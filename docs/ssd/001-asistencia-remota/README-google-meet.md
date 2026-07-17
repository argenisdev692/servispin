# Asistencia remota — puesta en marcha de Google Meet

Guía operativa para pasar del **enlace manual** (lo que funciona hoy) al **enlace
automático** con Google Meet. Complementa a `plan.md` §3 y `research.md` #6–#8.

> **Regla de oro:** mientras Google no esté configurado, el módulo funciona con
> `meeting_provider = manual` y Cesar pega el enlace a mano. Nada de lo de aquí es
> urgente ni bloquea el uso del módulo. Pero **T033 (publicar a Producción) no es
> opcional**: sin él, la automatización funciona una semana y se rompe sola.

---

## Qué hace falta, de un vistazo

| Paso | Quién | Bloquea |
|---|---|---|
| Crear proyecto y credenciales OAuth en Google Cloud | Argenis | Todo |
| **Publicar la pantalla de consentimiento a "In Production"** | Argenis | Que dure más de 7 días |
| Generar el token OAuth con la cuenta de Cesar | Argenis (una vez) | La generación automática |
| Poner las variables en `.env` | Argenis | La generación automática |
| Cambiar `meeting_provider` a `google_meet` | Argenis | — |

El código (provider, blindaje FR-15, persistencia del evento) **ya está hecho y
probado**. Lo que queda son credenciales y un flujo interactivo que no puede
automatizarse.

---

## 1. Proyecto y credenciales en Google Cloud

1. Entra en <https://console.cloud.google.com/> **con la cuenta de Cesar** (la que
   será dueña de los eventos y los Meet — decisión D-6, riesgo R-8 aceptado).
2. Crea un proyecto (p. ej. "Servispin Asistencia Remota").
3. **APIs y servicios → Biblioteca →** activa **Google Calendar API**.
4. **APIs y servicios → Credenciales → Crear credenciales → ID de cliente de OAuth**
   - Tipo de aplicación: **Aplicación de escritorio** (es lo que espera el flujo de
     token de Spatie).
   - Descarga el JSON y guárdalo como:
     `storage/app/google-calendar/oauth-credentials.json`

## 2. Pantalla de consentimiento → **Producción** (esto es lo crítico)

**APIs y servicios → Pantalla de consentimiento de OAuth:**

- Tipo de usuario: **Externo**.
- Rellena los datos obligatorios (nombre de la app, email de soporte, dominio).
- Añade el scope `https://www.googleapis.com/auth/calendar`.
- **Publica la app: estado "En producción" (In Production), NO "En pruebas".**

> ⚠️ **Por qué no puede quedarse en "En pruebas" (research #7b-c, R-6):** con la app
> en estado *Testing* + tipo *External*, Google **revoca el refresh token a los 7
> días**. El módulo funcionaría la primera semana y dejaría de generar enlaces
> después, en producción, cuando ya nadie está mirando. Es el peor modo de fallo
> del módulo. Publicar a Producción lo elimina.
>
> Google puede pedir verificación para apps con scopes sensibles. Para un único
> usuario (la propia cuenta de Cesar) normalmente basta con publicar; si pide
> verificación completa, se puede añadir a Cesar como usuario y aun así publicar.

## 3. Generar el token OAuth (una vez)

Con `oauth-credentials.json` ya en su sitio y `GOOGLE_CALENDAR_AUTH_PROFILE=oauth`
en el `.env`:

```bash
./vendor/bin/sail artisan google:oauth-token
```

> Este comando es **propio del proyecto** (`app/Console/Commands/GenerateGoogleOAuthToken.php`):
> el paquete de Spatie no trae ninguno, da por hecho que el token ya existe.

Se abre un flujo interactivo: copia la URL, ábrela **en sesión de la cuenta de
Cesar**, acepta los permisos, pega el código de vuelta. Esto crea:

`storage/app/google-calendar/oauth-token.json`

> Ese fichero **contiene credenciales y está en `.gitignore`** (T036a). No lo
> commitees jamás. Y verifica que **sobrevive a un despliegue** (R-7, T034): si el
> deploy sobreescribe `storage/app/`, la conexión muere. Ubícalo fuera del árbol
> desplegable o cópialo tras cada deploy.

## 4. Variables de entorno

```dotenv
GOOGLE_CALENDAR_AUTH_PROFILE=oauth          # NO 'service_account' (research #3c, R-9)
GOOGLE_CALENDAR_ID=la-cuenta-de-cesar@gmail.com
REMOTE_ASSISTANCE_MEETING_PROVIDER=google_meet
```

> ⚠️ **`config/google-calendar.php` viene con `default_auth_profile = 'service_account'`.**
> Es exactamente el perfil que da `Invalid conference type value` con un Gmail
> personal (research #3c). La variable de arriba lo corrige. El provider además
> deja un aviso en el log si detecta que sigue en `service_account`.

## 5. Prueba de humo — **no te la saltes** (T011d)

Antes de dar Google por bueno, confirma una cita remota de prueba y comprueba que
llega un enlace `https://meet.google.com/...` de verdad.

- Si sale **`Invalid conference type value`**: el perfil sigue en `service_account`.
  Revisa el paso 4.
- Si el evento se crea **pero sin enlace de Meet**: mismo diagnóstico (el provider
  lanza una `MeetingLinkException` con ese mensaje).

## Qué pasa si Google falla (ya está resuelto, FR-15)

No hay que hacer nada especial: está blindado por diseño.

1. El provider lanza `MeetingLinkException`.
2. El controlador la captura, **confirma la cita igualmente** con `meeting_url = null`
   y marca `meeting_link_failed_at`.
3. El email al **técnico** grita "⚠️ genera el enlace a mano" (T035).
4. La cita aparece en la bandeja `/admin/remote-assistance` bajo "citas confirmadas
   sin enlace".

Una cita pagada **nunca se pierde** por un fallo de Google. Ese es el motivo de que
todo el enlace viva detrás de una interfaz.
