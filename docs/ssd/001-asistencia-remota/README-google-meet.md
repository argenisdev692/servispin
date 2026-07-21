# Google Calendar + Meet — guía desde cero (ServiSpin)

Instrucciones operativas para conectar **Google Calendar API** y generar
enlaces **Google Meet** en el módulo de asistencia remota.

> **Regla de oro:** todo (Calendar API, OAuth, Service Account) debe vivir en
> **el mismo proyecto** de Google Cloud. Si mezclas proyectos, verás errores
> tipo `API has not been used in project XXX` o tokens que no cuadran.

---

## Qué necesitas al final

| Archivo / variable | Para qué |
|---|---|
| `storage/app/google-calendar/oauth-credentials.json` | Cliente OAuth (Desktop o Web) |
| `storage/app/google-calendar/oauth-token.json` | Token tras login OAuth (genera Meet) |
| `storage/app/google-calendar/service-account-credentials.json` | Service Account (eventos sin Meet en Gmail personal) |
| `GOOGLE_CALENDAR_AUTH_PROFILE=oauth` | **Obligatorio para Meet** con Gmail personal |
| `GOOGLE_CALENDAR_ID=tu-email@gmail.com` | ID del calendario |
| `REMOTE_ASSISTANCE_MEETING_PROVIDER=google_meet` | Activar Meet en el módulo |

### ¿OAuth o Service Account?

| Objetivo | Perfil | Resultado con Gmail personal |
|---|---|---|
| Crear eventos de calendario | `service_account` | OK (si compartes el calendario) |
| Generar enlace **Meet** | `oauth` | OK |
| Service Account + Meet | `service_account` | Falla (`Invalid conference type value`) |

**Para Meet en producción: usa OAuth.** Service Account es opcional/complementario.

---

## 0. Requisitos previos

- Cuenta Google del calendario (ej. `servispin19@gmail.com`)
- Acceso a [Google Cloud Console](https://console.cloud.google.com/)
- Laravel + Sail (o `php artisan`) con el proyecto ServiSpin
- Carpeta: `storage/app/google-calendar/` (créala si no existe)

Los JSON de credenciales **no se suben a Git** (están en `.gitignore`).

---

## 1. Crear o seleccionar el proyecto (uno solo)

1. Entra en <https://console.cloud.google.com/>
2. Arriba: **Select a project** → crea uno (ej. `Servispin Asistencia Remota`) o elige el existente
3. Anota el **Project number** (ej. `722311583782`): la API y las credenciales deben ser de **ese** proyecto

---

## 2. Habilitar Google Calendar API (mismo proyecto)

1. **APIs & Services → Library** (o *Enable APIs and Services*)
2. Busca **Google Calendar API**
3. Pulsa **Enable**

Enlace directo (sustituye `PROJECT_ID` por tu número de proyecto):

```
https://console.developers.google.com/apis/api/calendar-json.googleapis.com/overview?project=PROJECT_ID
```

Si la API no está habilitada, el error típico es:

> Google Calendar API has not been used in project … before or it is disabled

Tras habilitarla, espera 1–2 minutos y reintenta.

---

## 3. Pantalla de consentimiento OAuth (mismo proyecto)

1. **APIs & Services → OAuth consent screen** (o *Google Auth Platform → Branding*)
2. Tipo de usuario: **External**
3. Completa:
   - Nombre de la app (ej. ServiSpin)
   - Email de soporte
   - Dominios autorizados: `servispin.net` (sin `https://`)
   - Homepage / Privacy: `https://servispin.net` y páginas legales
4. Scopes: añade `https://www.googleapis.com/auth/calendar`
5. **Publica a "In Production" / En producción**

> ⚠️ Si dejas la app en **Testing**, Google **revoca el refresh token a los 7 días**.
> Meet funcionaría una semana y se rompería solo.

---

## 4. Credenciales OAuth — Desktop (recomendado) o Web

Ve a **APIs & Services → Credentials → Create Credentials → OAuth client ID**.

### Opción A — Aplicación de escritorio (**por defecto / recomendada**)

| Campo | Valor |
|---|---|
| Application type | **Desktop app** / Aplicación de escritorio |
| Name | ej. `ServiSpin Calendar Desktop` |

1. Crea el cliente y **descarga el JSON**
2. Guárdalo como:
   ```
   storage/app/google-calendar/oauth-credentials.json
   ```
3. El JSON debe tener la clave **`"installed"`** (no `"web"`)

Redirect URI por defecto en este proyecto:

```
http://localhost
```

Eso coincide con `GOOGLE_CALENDAR_OAUTH_REDIRECT_URI` (default en `config/google-calendar.php`).

Flujo de conexión (Sail / localhost):

1. Inicia sesión en la app como **admin**
2. Abre: **http://localhost/admin/google-calendar/oauth/connect**
3. Autoriza con la cuenta del calendario (ej. `servispin19@gmail.com`)
4. Google redirige a `http://localhost/?code=...&scope=...`
5. La app guarda: `storage/app/google-calendar/oauth-token.json`

> La ruta `/admin/google-calendar/oauth/connect` exige login. Si te manda a `/login`,
> autentícate primero y vuelve a abrir el enlace.

### Opción B — Aplicación web (alternativa)

Úsala solo si prefieres un cliente tipo Web.

| Campo | Valor |
|---|---|
| Application type | **Web application** |
| Authorized JavaScript origins | `http://localhost` y/o `https://servispin.net` |
| Authorized redirect URIs | `http://localhost` (Sail) y, si aplica, la URI de producción |

1. Descarga el JSON → mismo path: `oauth-credentials.json`
2. El JSON tendrá clave **`"web"`**
3. El `redirect_uri` del `.env` / config **debe coincidir exactamente** con el de Google Console

```env
GOOGLE_CALENDAR_OAUTH_REDIRECT_URI=http://localhost
```

Si no coinciden → `redirect_uri_mismatch`.

### Comparación rápida

| | Desktop (A) | Web (B) |
|---|---|---|
| Recomendado en ServiSpin | Sí | Alternativa |
| Redirect típico | `http://localhost` | Debes declararlo tú |
| Clave en JSON | `"installed"` | `"web"` |
| Login OAuth en app | `/admin/google-calendar/oauth/connect` | Igual, si URI = `http://localhost` |

---

## 5. Service Account (opcional; mismo proyecto)

Útil para pruebas de eventos **sin** Meet, o entornos Workspace con delegación.
**No genera Meet fiable en Gmail personal.**

1. **APIs & Services → Credentials → Create Credentials → Service account**
2. Nombre (ej. `servispin-calendar`)
3. En la service account: **Keys → Add key → Create new key → JSON**
4. Guarda el archivo como:
   ```
   storage/app/google-calendar/service-account-credentials.json
   ```
5. Copia el email de la SA (`xxx@PROJECT.iam.gserviceaccount.com`)

### Compartir el calendario con la Service Account

1. Abre [Google Calendar](https://calendar.google.com) con la cuenta dueña del calendario
2. Calendario → ⋮ → **Settings and sharing**
3. **Share with specific people** → **Add people**
4. Pega el email `xxx@....iam.gserviceaccount.com`
5. Permiso: **Make changes to events**
6. Guarda

### Calendar ID

En los mismos ajustes → **Integrate calendar** → copia el **Calendar ID**
(suele ser tu email, ej. `servispin19@gmail.com`).

---

## 6. Configurar Laravel (`.env`)

```env
# Perfil: oauth = Meet | service_account = solo eventos (sin Meet en Gmail personal)
GOOGLE_CALENDAR_AUTH_PROFILE=oauth
GOOGLE_CALENDAR_ID=servispin19@gmail.com
GOOGLE_CALENDAR_OAUTH_REDIRECT_URI=http://localhost

# Módulo asistencia remota
REMOTE_ASSISTANCE_MEETING_PROVIDER=google_meet
```

Luego:

```bash
php artisan config:clear
```

Estructura de archivos esperada:

```
storage/app/google-calendar/
├── oauth-credentials.json              # OAuth Desktop o Web
├── oauth-token.json                    # Se crea tras el login OAuth
└── service-account-credentials.json    # Opcional
```

---

## 7. Login OAuth (crear `oauth-token.json`)

### Opción recomendada — navegador (Sail)

1. `http://localhost/login` → admin (`servispin19@gmail.com` / seeder)
2. `http://localhost/admin/google-calendar/oauth/connect`
3. Autoriza con la cuenta del calendario
4. Verifica que exista `storage/app/google-calendar/oauth-token.json`

También puedes usar el aviso del panel:

`/admin/remote-assistance` → enlace “conecta la cuenta de Google”.

### Opción alternativa — Artisan

```bash
php artisan google:oauth-token --manual
```

1. Abre la URL que imprime
2. Autoriza
3. Pega la URL completa de la barra (aunque diga “conexión rechazada”)
4. Se escribe `oauth-token.json`

> No uses scripts sueltos tipo `generate-token.php` fuera de Laravel: el flujo
> soportado es el de la app o `google:oauth-token`.

### Si Google no da `refresh_token`

1. Ve a <https://myaccount.google.com/permissions>
2. Revoca el acceso de la app
3. Vuelve a conectar (`oauth/connect` o el comando)

---

## 8. Invitación al cliente (Gmail, Outlook, etc.)

Al confirmar una cita remota, el sistema crea el evento en **tu** calendario e
**invita al email del cliente** como asistente (`sendUpdates=all`).

- Funciona con **cualquier email** (Gmail, Outlook/Hotmail, Yahoo, corporativo…).
- El cliente recibe la invitación de Google Calendar (ICS) además del email de ServiSpin.
- **No necesita cuenta Google** para entrar al Meet: abre el enlace en el navegador como invitado.
- Los recordatorios de 24 h / 30 min de ServiSpin siguen enviándose aparte (cron).

## 9. Prueba de humo

Con OAuth y token ya creados:

```bash
# Solo evento
php artisan google:calendar-test --keep

# Evento + Meet (lo que importa para producción)
php artisan google:calendar-test --meet --keep
```

Éxito esperado:

```
Evento creado correctamente.
Event ID:  ...
Meet OK:   https://meet.google.com/...
```

Prueba de negocio (opcional): confirma una cita remota de prueba y comprueba que
el email / ficha llevan un Meet real.

---

## 10. Checklist final (mismo proyecto)

- [ ] Un solo proyecto Google Cloud
- [ ] **Google Calendar API** habilitada
- [ ] Consent screen en **Production**
- [ ] Cliente OAuth **Desktop** (recomendado) o **Web**, con redirect `http://localhost`
- [ ] `oauth-credentials.json` en `storage/app/google-calendar/`
- [ ] Login OAuth → `oauth-token.json` creado
- [ ] (Opcional) Service Account + calendario compartido + `service-account-credentials.json`
- [ ] `.env`: `AUTH_PROFILE=oauth`, `GOOGLE_CALENDAR_ID`, `MEETING_PROVIDER=google_meet`
- [ ] `php artisan config:clear`
- [ ] `php artisan google:calendar-test --meet --keep` → Meet OK

---

## 11. Errores frecuentes

| Error | Causa | Qué hacer |
|---|---|---|
| `API has not been used… or it is disabled` | Calendar API apagada en ese project number | Enable API en **el mismo** proyecto del OAuth |
| `redirect_uri_mismatch` | URI distinta a la del cliente | Desktop + `http://localhost`, o alinear Web + `.env` |
| Te manda a `/login` en `oauth/connect` | Ruta protegida | Login admin primero |
| Evento OK pero sin Meet / `Invalid conference type` | Perfil `service_account` | `GOOGLE_CALENDAR_AUTH_PROFILE=oauth` + token |
| Token muere a los ~7 días | Consent en Testing | Publicar a **Production** |
| Tras deploy deja de funcionar | Se perdió `oauth-token.json` | Restaurar el fichero en el servidor (no está en Git) |
| `403` / sin permiso en calendario (SA) | Calendario no compartido con la SA | Share → Make changes to events |

---

## Referencias en el código

| Pieza | Ruta |
|---|---|
| Config Spatie | `config/google-calendar.php` |
| Provider Meet | `app/Services/MeetingLink/GoogleMeetLinkProvider.php` |
| OAuth connect / callback | `GoogleCalendarOAuthController` + rutas admin |
| Generar token CLI | `php artisan google:oauth-token` |
| Prueba de humo | `php artisan google:calendar-test --meet` |
| Provider del módulo | `REMOTE_ASSISTANCE_MEETING_PROVIDER` en `config/remote_assistance.php` |

Mientras Meet no esté listo, deja:

```env
REMOTE_ASSISTANCE_MEETING_PROVIDER=manual
```

El módulo sigue funcionando: Cesar pega el enlace a mano.
