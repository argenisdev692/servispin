# Módulo: Asistencia Técnica Remota

Permite a un cliente de cualquier parte del mundo contratar una sesión de
asistencia técnica por videollamada, pagando por adelantado con el QR de SumUp.

- **Spec / plan / research / tasks:** ver los ficheros de esta carpeta.
- **Puesta en marcha de Google Meet (Spatie + OAuth):** [`README-google-meet.md`](README-google-meet.md).

## El flujo, de un vistazo

```
Cliente                          Cesar (admin)                 Sistema
  │                                                              │
  ├─ Paga con el QR de SumUp                                     │
  ├─ Rellena el formulario  ───────────────────────────────────►│ crea cita
  │   (referencia + importe + nombre)                           │ status=Pending
  │                                                             │ payment=claimed
  │◄─ email "solicitud recibida" (SIN enlace) ──────────────────┤
  │                              │                               │
  │                              ├─ ve el pago en /admin/remote-assistance
  │                              ├─ lo coteja en la app de SumUp │
  │                              ├─ pulsa "Confirmar" ──────────►│ status=Confirmed
  │                              │                               │ genera enlace
  │◄─ email CON el enlace ───────┴───────────────────────────────┤
  │
  ├─ recordatorio 24 h antes (con enlace)
  ├─ recordatorio 30 min antes (con enlace)
  └─ entra a la videollamada
```

## Flujo Google Calendar API + Spatie (Meet)

Al confirmar el pago, el sistema intenta generar el enlace automáticamente:

```
verify-payment (decision=verify)
        │
        ▼
 MeetingLinkProvider  ←── REMOTE_ASSISTANCE_MEETING_PROVIDER
        │                    (manual | google_meet)
        ▼
 GoogleMeetLinkProvider
        │
        ├─ spatie/laravel-google-calendar
        │     Event::create(...) + addMeetLink()
        │     auth: GOOGLE_CALENDAR_AUTH_PROFILE=oauth
        │
        ├─ OK  → meeting_url = hangoutLink
        │        google_event_id / google_calendar_id guardados
        │        invita al cliente (sendUpdates=all)
        │        email de confirmación CON enlace
        │
        └─ FAIL → MeetingLinkException (FR-15)
                  cita se confirma IGUAL
                  meeting_link_failed_at marcado
                  bandeja admin: pegar Meet a mano
```

| Pieza | Ruta / config |
|-------|----------------|
| Config Spatie | `config/google-calendar.php` |
| Provider Meet | `app/Services/MeetingLink/GoogleMeetLinkProvider.php` |
| Interfaz | `app/Services/MeetingLink/MeetingLinkProvider.php` |
| OAuth connect | `/admin/google-calendar/oauth/connect` |
| Credenciales | `storage/app/google-calendar/oauth-*.json` (fuera de Git) |
| Provider del módulo | `REMOTE_ASSISTANCE_MEETING_PROVIDER` en `config/remote_assistance.php` |

**Importante:** en Gmail personal, Meet solo funciona con perfil **OAuth** (cuenta
del técnico). Service Account crea eventos pero suele fallar con
`Invalid conference type value`. Guía paso a paso: [`README-google-meet.md`](README-google-meet.md).

## Por qué la verificación del pago es MANUAL

Es la decisión que define el módulo, y es deliberada (research #2):

Servispin cobra con un **QR estático de SumUp**. No se integra la API de SumUp, así
que **el sistema no puede saber por sí mismo si un pago entró**. Lo único que tiene
es lo que el cliente declara (referencia + importe). Por eso **un humano — Cesar —
coteja cada pago en la app de SumUp antes de que se envíe el enlace** (FR-3).

Esto tiene dos consecuencias que no se deben "optimizar" sin entenderlas:

1. **El email de solicitud NO puede llevar enlace.** Si lo llevara, cualquiera
   podría inventarse una referencia y colarse en una videollamada gratis. Hay un
   test que falla si ese email llega a contener un enlace. No lo quites.

2. **Nunca se piden datos de tarjeta** (FR-4). El dato de tarjeta lo captura SumUp
   y no toca este servidor: es lo único que mantiene a Servispin **fuera del
   alcance de PCI-DSS**. Un guardarraíl rechaza con 422 cualquier campo tipo
   `card_number`/`cvv`/`pan`. El día que se acepte uno, el negocio entra en
   alcance regulatorio.

## Rutas

| Ruta | Auth | Qué es |
|---|---|---|
| `GET /asistencia-remota` | no | Landing (US-4) |
| `GET /asistencia-remota/solicitar` | no | Formulario del cliente (US-1) |
| `POST /asistencia-remota/store` | no | Registrar solicitud |
| `POST /asistencia-remota/slots` | no | Huecos disponibles (con huso del cliente) |
| `GET /admin/remote-assistance` | admin | Bandeja de verificación (US-2) |
| `PATCH /admin/appointments/{id}/verify-payment` | admin | Confirmar / rechazar (US-2) |
| `POST /admin/appointments/remote` | admin | Alta desde el calendario (US-6) |

## Configuración (`config/remote_assistance.php`)

| Clave | `.env` | Por defecto | Para qué |
|---|---|---|---|
| `meeting_provider` | `REMOTE_ASSISTANCE_MEETING_PROVIDER` | `manual` | `manual` o `google_meet` |
| `default_price` | `REMOTE_ASSISTANCE_PRICE` | `30.00` | Precio del seeder |
| `default_duration` | `REMOTE_ASSISTANCE_DURATION` | `20` | Duración en min |
| `sumup_qr_url` | `REMOTE_ASSISTANCE_SUMUP_QR_URL` | (URL de SumUp) | QR del formulario |
| `buffer.mixed_minutes` | `REMOTE_ASSISTANCE_BUFFER_MIXED` | `60` | Margen entre presencial y remota |
| `hold_hours` | `REMOTE_ASSISTANCE_HOLD_HOURS` | `24` | Plazo para verificar (FR-12) |

## Decisiones de diseño que conviene no deshacer

- **Todo vive en la tabla `appointments`, no en una tabla aparte.** La agenda del
  técnico es una sola: la disponibilidad, el calendario y los recordatorios
  consultan `appointments`. Una tabla separada sería invisible para ellos y se
  reservarían huecos ocupados (FR-7 roto en silencio). Ver research #9a.
- **Las fechas se guardan en el huso del negocio (`Atlantic/Canary`), no en UTC.**
  Es como ya lo hace el flujo presencial. Mezclar UTC y hora local en la misma
  columna rompería la detección de solapamiento. Ver plan §9 R-5.
- **El buffer de desplazamiento depende de la modalidad.** Una videollamada no
  bloquea 4 h de agenda como una visita presencial. Ver research #9b.
- **El enlace vive detrás de una interfaz (`MeetingLinkProvider`).** No por
  purismo: si Google falla, la cita pagada se confirma igual y Cesar pega el
  enlace a mano (FR-15). Una cita cobrada nunca se pierde.

## Autorización

Los endpoints de admin comprueban el rol con el **guard `sanctum` explícito**
(`hasRole('Admin', 'sanctum')`), porque los roles se siembran en ese guard mientras
`config/auth.php` resuelve `web` por defecto. Por el mismo motivo **no se usa el
middleware `role:`/`permission:`** en las rutas: daría 403 a todo el mundo, Cesar
incluido.

## Comandos programados

| Comando | Cuándo | Para qué |
|---|---|---|
| `appointments:send-reminders` | diario 09:00 | Recordatorio 24 h (US-3) |
| `appointments:send-imminent-reminders` | cada 5 min | Recordatorio 30 min (US-3) |
| `appointments:release-unverified` | cada hora | Liberar huecos no verificados (FR-12) |
| `google:oauth-token` | manual, una vez | Generar el token de Google (ver README-google-meet.md) |

> ⚠️ Los recordatorios y la liberación dependen de que **el cron del servidor corra
> de verdad** (R-3). Verifícalo antes de prometer el recordatorio de 30 min (T037).
