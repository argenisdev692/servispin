# Technical plan: Asistencia Técnica Remota

> Phase 3 · PLAN — Define CÓMO se construye, verificado contra `research.md`.

**Feature ID:** 001-asistencia-remota
**Based on:** spec.md, research.md

## 1. Technical summary

El módulo se construye **extendiendo el módulo de citas existente**, no creando uno paralelo. Una
asistencia remota es una `Appointment` con `modality = 'remote'`: gana declaración de pago y enlace
de reunión, y pierde la dirección postal. La razón es directa: el calendario de administración, la
comprobación de solapamientos, los estados Pending/Confirmed/Cancelled y los emails ya existen y ya
funcionan. Duplicarlos en una tabla aparte significaría mantener dos agendas que pueden
contradecirse — y la agenda del técnico es una sola.

La pieza que decide la arquitectura es el enlace de la videollamada. Se genera automáticamente con
`spatie/laravel-google-calendar` y su método `addMeetLink()`, autenticando **por OAuth como usuario**
y no con service account: esa es la diferencia que permite usar un Gmail gratuito (research.md #6b),
porque la restricción de `Invalid conference type value` afecta solo a las service accounts. Se usa
un **único perfil OAuth** —la cuenta de Servispin— porque hay un solo técnico y un solo calendario
corporativo; es justo el caso que Spatie soporta de forma nativa, y evita la complejidad de tokens
por usuario, que el paquete no permite sin sobreescribir su factory (research.md #6c-d).

Aun así el enlace se modela detrás de una interfaz `MeetingLinkProvider`, y no por purismo: el
refresh token de Google es frágil (research.md #7), y si muere o la API falla, **una cita ya pagada
no puede perderse**. El proveedor manual es el fallback que garantiza FR-15, no una opción de
segunda. La interfaz también deja la puerta abierta a JaaS sin tocar el flujo.

> **Revisión 17/07/2026:** el borrador inicial usaba enlace manual *por defecto*, asumiendo que
> automatizar exigía Workspace. Argenis señaló la vía OAuth + `addMeetLink()`, que desmonta esa
> premisa. La automatización pasa al alcance principal y D-1 deja de bloquear.

## 2. Technology stack (verificado con investigación real)

> ⚠️ **CORREGIDO 17/07/2026 (segunda revisión):** esta tabla daba Laravel 10 EOL y PHP 8.2. **Falso
> a día de hoy**: Argenis subió el stack mientras se escribía este SSD. Ver research.md #1, segunda
> corrección. Las filas de abajo ya reflejan `composer.json` real.

| Componente | Elección | Versión verificada | Fuente / justificación |
|---|---|---|---|
| Framework | Laravel (existente) | **13.x** — con soporte | research.md #1 (2ª corrección). **R-1 resuelto.** |
| Lenguaje | PHP (existente) | **^8.4** | research.md #1 (2ª corrección) |
| Tests | PHPUnit | **^12.5.12** — ⚠️ `@test` **eliminado**, usar `#[Test]` | research.md #1. Costó un `No tests found`. |
| Cliente Google | `google/apiclient` (vía Spatie) | **v2.19.4** — usa `php-jwt ^7.0` | research.md #8. Instalar con `-W`. |
| UI formulario | Blade + JS (como `book.blade.php`) | Livewire **3.8** disponible | Coherencia con `appointments/book.blade.php` |
| Permisos | `spatie/laravel-permission` | **^8.3** | ⚠️ roles sembrados en guard `sanctum`, `auth.defaults` = `web` |
| Base de datos | La existente (migraciones Laravel) | — | Sin cambio de motor |
| Email | Resend (existente) | `resend/resend-laravel` ^1.4 | Ya en uso en los 5 Mailables actuales |
| Pago | **Ninguna integración** — QR SumUp + verificación manual | — | research.md #2 + decisión usuario 16/07 |
| Enlace de vídeo (**por defecto**) | `spatie/laravel-google-calendar` + `addMeetLink()` | **3.8.4** (`php ^7.2\|^8.0`, `laravel ^6.0…^12.0`) | research.md #6a — compatible con L10/PHP8.1 |
| Autenticación Google | **Perfil `oauth`** de Spatie, cuenta única | — | research.md #6b-d: OAuth funciona con Gmail gratis; service account **no** |
| Enlace de vídeo (**fallback**) | `ManualMeetingLinkProvider` | — | research.md #7 + FR-15: una cita pagada no se pierde |
| Enlace de vídeo (alternativa futura) | JaaS (8x8) | Tier Dev: 25 MAU gratis | research.md #4 — descartada por ahora |
| Almacenamiento de fotos | Supabase (existente) | — | `ImageHelper` + disco `supabase` ya en uso |

**Descartado explícitamente — Laravel Socialite** (`^5.8`, ya presente en `composer.json`): sirve
para que *cada* profesional conecte su cuenta. Servispin tiene un técnico y un calendario, y el
OAuth de Spatie es por aplicación, no por usuario (research.md #6c-d). Introducirlo aquí sería
construir para un problema que este negocio no tiene. Se reevalúa si entra un segundo técnico.

⚠️ No hay elecciones `[UNVERIFIED]` en esta tabla: todas las versiones salen de research.md.

## 3. Architecture

Se respeta la estructura actual (controlador → `TransactionService` → modelo), sin introducir capa
de repositorio: el proyecto no la tiene y añadirla solo aquí crearía dos estilos conviviendo.

```
Cliente (móvil/web)
  │
  ├─ GET  /asistencia-remota          → RemoteAssistanceController@landing   (US-4)
  ├─ POST /asistencia-remota/slots    → AvailabilityController (reutilizado)  (US-1)
  └─ POST /asistencia-remota/store    → RemoteAssistanceController@store      (US-1)
        │
        └─ TransactionService::run(
             db:        crea Appointment{modality:remote, status:Pending, payment_status:claimed}
             onCommit:  Mail → "solicitud recibida" (SIN enlace)   ← FR-3
             onError:   limpia foto subida a Supabase
           )

Cesar (admin, autenticado)
  │
  ├─ PATCH /admin/appointments/{id}/verify-payment → RemoteAssistanceAdminController@verifyPayment
  │     │
  │     └─ TransactionService::run(
  │          db:        payment_status=verified, verified_by/at, status=Confirmed,
  │                     meeting_url = MeetingLinkProvider::linkFor($appointment)
  │          onCommit:  Mail → cliente + técnico CON enlace          ← US-2
  │        )
  │
  └─ POST /admin/appointments/remote  → RemoteAssistanceAdminController@store    ← US-6 / FR-13
        │  (desde el hueco del FullCalendar; datos del cliente escritos a mano)
        │
        └─ TransactionService::run(
             db:        crea Appointment{modality:remote} + si Cesar marca "ya pagado":
                        payment_status=verified, verified_by=Cesar, status=Confirmed,
                        meeting_url = MeetingLinkProvider::linkFor($appointment)
             onCommit:  Mail CON enlace (solo si verificado; si no, sin enlace ← FR-3)
           )

Scheduler
  ├─ 09:00 diario        → appointments:send-reminders (existente, extendido)     (US-3)
  └─ cada 5 min          → appointments:send-imminent-reminders (nuevo, T+30min)  (US-3)
```

**`MeetingLinkProvider`** (contrato):
```php
interface MeetingLinkProvider {
    public function linkFor(Appointment $appointment): ?string; // null ⇒ se pega a mano
    public function isAutomatic(): bool;
}
```
- **`GoogleMeetLinkProvider` (por defecto):** crea el evento con Spatie y llama a `addMeetLink()`,
  recuperando la URL con `$event->googleEvent->getHangoutLink()`. Persiste `google_event_id`,
  `google_calendar_id` y `meeting_url` (FR-14). `isAutomatic() = true`.
- **`ManualMeetingLinkProvider` (fallback):** devuelve el enlace que Cesar escribe a mano.
  `isAutomatic() = false`. Es también el modo de degradación automática (ver abajo).
- `JaasLinkProvider`: no se implementa. La interfaz deja la puerta abierta (research.md #4).

Se enlaza en un `ServiceProvider` leyendo `config('remote_assistance.meeting_provider')`.

**Punto de diseño deliberado (FR-15, research.md #7):** si el provider automático falla —token
revocado a los 7 días, API caída, cuota— se captura la excepción, se registra, y **la cita se
confirma igualmente** con `meeting_url = null`, marcándola para que Cesar añada el enlace a mano y
avisándole. El fallo de Google nunca puede costar una cita ya cobrada. Este es el motivo real de que
exista la interfaz; la extensibilidad es un efecto secundario.

## 4. Data model (esquema físico)

**Migración A — `appointments` (extensión; nada destructivo):**
```
appointments (existente)
+ modality: enum('onsite','remote') default 'onsite'  NOT NULL   ← index
+ client_timezone: string(64) nullable                            (FR-6)
+ meeting_url: text nullable                                      (FR-8)
+ meeting_provider: string(32) nullable                           ('manual'|'google_meet'|'jaas')
+ google_event_id: string(255) nullable                           (FR-14) ← permite editar/borrar el evento
+ google_calendar_id: string(255) nullable                        (FR-14) ← de qué calendario cuelga
+ meeting_link_failed_at: timestamp nullable                      (FR-15) ← marca "pega el enlace a mano"
+ payment_status: enum('unpaid','claimed','verified','rejected','refund_pending') default 'unpaid' ← index
+ payment_reference: string(128) nullable                         (FR-2)
+ payment_amount: decimal(8,2) nullable
+ payment_currency: char(3) nullable default 'EUR'
+ payer_name: string(255) nullable
+ payment_claimed_at: timestamp nullable
+ payment_verified_at: timestamp nullable                         (FR-5)
+ payment_verified_by: foreignId nullable → users.id (nullOnDelete) (FR-5)
```
`address` ya es `nullable` en la migración actual, así que FR-11 no necesita cambio de esquema:
solo que la validación de la ruta remota no lo exija.

**Índices y porqué:**
- `(modality, status)` — el calendario y el listado de admin filtran por ambos.
- `(payment_status)` — la bandeja "pendientes de verificar" de Cesar es la consulta más frecuente.
- `(start_time, end_time)` — ya lo usa la comprobación de solapamiento (`AppointmentController:149`),
  que hoy hace un scan; con volumen bajo no duele, pero el índice es gratis.

**Migración B — `services` (arregla research.md #5):**
```
services
+ active: boolean default true NOT NULL   ← desbloquea getServices(), hoy roto
+ is_remote: boolean default false NOT NULL
```
`price` y `duration` ya existen: el precio y la duración de la sesión remota son un `Service` con
`is_remote = true`. No hace falta entidad nueva.

**Por qué enum y no tabla de estados:** el proyecto ya usa strings con constantes de clase para
`status` (`Appointment::STATUS_*`). Se sigue ese patrón para no introducir dos convenciones.

**Por qué todo va en `appointments` y no en una tabla aparte (pregunta de Argenis, 17/07/2026):**
la disponibilidad se calcula consultando `appointments` (`AvailabilityController:163`), igual que el
calendario de admin y los recordatorios. Una tabla separada sería **invisible** para esa consulta: se
ofrecería como libre un hueco con videollamada y se aceptaría una presencial encima — **FR-7 roto en
silencio** (research #9a). La agenda del técnico es una sola y debe vivir en una sola tabla.
El coste asumido es real y conviene decirlo: **una cita presencial arrastra 14 columnas a NULL** que
no le significan nada. Se aceptó (decisión de Argenis, 17/07) frente a la alternativa híbrida
(`remote_assistance_details` 1:1), por coherencia con un repo que no tiene capa de repositorio y por
el volumen previsto (unidades a decenas de citas/mes). Si el módulo creciera, extraer esas columnas a
una tabla 1:1 es un refactor mecánico que **no** tocaría la agenda ni FR-7.

## 4b. Buffer de desplazamiento según modalidad (research #9b)

`AvailabilityController:169` aplica un **buffer de 240 min antes y después de cada cita** porque el
técnico tiene que conducir. Heredarlo en las remotas haría que **una videollamada de 30 min bloquease
8,5 h de agenda** — justo el *"tiempo muerto entre desplazamientos"* que spec §2 quiere monetizar. El
módulo anularía su propio objetivo de negocio.

El buffer pasa a depender del **par de modalidades**:

| Existente → nueva | Buffer | Razón |
|---|---|---|
| presencial → presencial | **240 min** — sin cambio | Hay que conducir. El flujo actual no se toca. |
| remota → remota | **0 min** | Nadie se mueve. Llamadas seguidas son posibles. |
| mixto | **60 min**, configurable | El técnico debe llegar a un sitio con conexión. |

Configurable en `config/remote_assistance.php` (`buffer.mixed_minutes`). El caso
presencial↔presencial **no se modifica**: es código en producción que hoy funciona y no tiene tests.

## 5. API contracts

### GET /asistencia-remota
- **Story:** US-4 · **Auth:** no · **Response:** vista landing (GIF, precio, duración).

### POST /asistencia-remota/slots
- **Story:** US-1 · **Auth:** no · Reutiliza `AvailabilityController@getTimeSlots`.
- **Request:** `{ date, service_id, timezone }`
- **Response 200:** `{ success, data: [{ start_utc, start_local, label }] }`
- Añade `timezone` para FR-6; si falta, cae al huso de Servispin y lo indica.

### POST /asistencia-remota/store
- **Story:** US-1 · **Auth:** no · **Throttle:** `throttle:appointments` (ya existe) + honeypot.
- **Request (multipart):**
  `service_id` req · `brand_id` req · `client_first_name` req · `client_last_name` req ·
  `client_email` req·email · `client_phone` req · `issue_description` req · `start_time` req ·
  `client_timezone` req·timezone · `payment_reference` req·string(128) ·
  `payment_amount` req·numeric·min:0 · `payer_name` req · `equipment_photo` opt·image·max:10240
- **Prohibido:** cualquier campo de tarjeta. FR-4 / research.md #2.
- **Response 201:** `{ success, message, data: { uuid, start_time, status } }`
  → **nunca** incluye `meeting_url` (FR-3).
- **Errores:** `422` validación / hueco ocupado (FR-7) · `429` rate limit · `500` fallo interno.

### PATCH /admin/appointments/{id}/verify-payment
- **Story:** US-2 · **Auth:** sí, sesión admin (`auth` + permiso, como el resto de `/admin`).
- **Request:** `{ decision: 'verify'|'reject', meeting_url?: url, reason?: string }`
  (`meeting_url` obligatorio si el provider es manual y `decision = verify`)
- **Response 200:** `{ success, data: { status, payment_status, meeting_url } }`
- **Errores:** `404` no existe · `422` ya verificada / no es remota / falta `meeting_url` · `403`.

### POST /admin/appointments/remote
- **Story:** US-6 / FR-13 · **Auth:** sí, sesión admin. Alta desde el hueco del FullCalendar.
- **Request:** los mismos datos del cliente que el formulario público (escritos a mano, sin select)
  + `start_time` + `client_timezone` + `payment_verified: bool` + `payment_reference?` +
  `payment_amount?` + `meeting_url?` (si el provider es manual y `payment_verified = true`).
- **Response 201:** `{ success, data: { uuid, status, payment_status, meeting_url } }`
- **Comportamiento:** con `payment_verified = true` → Confirmed + enlace + email con enlace, en un
  solo paso. Con `false` → Pending, sin enlace (**FR-3 aplica igual que en el flujo público**: el
  atajo del admin no puede ser una puerta trasera que se salte la regla del pago).
- **Errores:** `422` hueco ocupado (FR-7) / falta `meeting_url` con provider manual · `403`.

### GET /admin/appointments?modality=remote&payment_status=claimed
- **Story:** US-2 · Bandeja de verificación. Extiende el filtrado que ya tiene `index()`.

## 6. Proposed folder structure

Sigue la organización actual del repo (`api/` para lo que responde JSON, `Admin/` para gestión).

> **CORRECCIÓN (17/07/2026):** la carpeta real del repo es `app/Http/Controllers/api/` **en
> minúscula** (con namespace `App\Http\Controllers\Api`). Crear `Api/` funcionaría en Windows pero
> daría **dos carpetas distintas en Linux** (producción/Docker). Se usa la carpeta existente.
>
> **CORRECCIÓN (17/07/2026):** este árbol marcaba `GoogleMeetLinkProvider` como *"fase 2 — solo
> Workspace"* y `ManualMeetingLinkProvider` como *"por defecto"*. Es el borrador **anterior** a la
> revisión del 17/07 y **contradice a §2 y §3**, ya revisados: Google Meet vía OAuth es la
> implementación **por defecto** y la manual es el **fallback** (research #6b).

```
app/
├── Http/Controllers/
│   ├── api/RemoteAssistanceController.php          (landing, slots, store)
│   └── Admin/RemoteAssistanceAdminController.php   (verifyPayment, store)
├── Http/Requests/
│   ├── StoreRemoteAssistanceRequest.php            (saca la validación del controlador)
│   └── VerifyPaymentRequest.php
├── Services/MeetingLink/
│   ├── MeetingLinkProvider.php                     (interfaz)
│   ├── ManualMeetingLinkProvider.php               (fallback — FR-15)
│   ├── GoogleMeetLinkProvider.php                  (por defecto — OAuth, research #6b)
│   └── JaasLinkProvider.php                        (no se implementa — alternativa futura)
├── Providers/MeetingLinkServiceProvider.php
├── Mail/
│   ├── RemoteAssistanceRequested.php               (SIN enlace)
│   ├── RemoteAssistanceConfirmed.php               (CON enlace)
│   └── RemoteAssistanceRejected.php
├── Console/Commands/SendImminentReminders.php      (30 min)
└── Models/Appointment.php                          (+scopes remote/pendingVerification)
config/remote_assistance.php
database/migrations/
├── 2026_07_XX_add_remote_fields_to_appointments_table.php
└── 2026_07_XX_add_active_and_is_remote_to_services_table.php
resources/views/
├── remote-assistance/{landing,book}.blade.php
└── emails/remote-assistance/{requested,confirmed,rejected}.blade.php
tests/Feature/RemoteAssistance/{BookingTest,PaymentVerificationTest,ReminderTest}.php
tests/Unit/MeetingLink/ManualMeetingLinkProviderTest.php
```

## 7. Testing strategy

El repo tiene `phpunit.xml` y `tests/`, pero el módulo de citas actual no tiene tests. No se va a
retrofitear el módulo viejo, pero **el nuevo sí nace con tests**, centrados en lo que duele si falla:
que alguien reciba una videollamada sin pagar.

- **Unit:** `ManualMeetingLinkProvider`; cálculo de conversión de husos; scopes del modelo.
- **Feature (críticos):**
  - `store` crea la cita con `payment_status=claimed` y `status=Pending`.
  - **El email de solicitud NO contiene `meeting_url`** (FR-3, el test más importante del módulo).
  - `store` rechaza si falta `payment_reference` (FR-2).
  - `store` rechaza un hueco ya ocupado por una cita presencial (FR-7).
  - `verifyPayment` con `decision=verify` → Confirmed + enlace + email con enlace.
  - `verifyPayment` con `decision=reject` → Cancelled + hueco liberado + email sin enlace.
  - `verifyPayment` es inaccesible sin autenticación (FR-5).
  - Un provider automático que lanza excepción **no impide** confirmar la cita (§3).
  - Los recordatorios no se envían a citas canceladas (US-3).
- **Cobertura:** no se fija umbral global (sería mentira con el resto del repo sin tests). Sí:
  **el 100% de FR-2, FR-3, FR-4 y FR-5 debe tener un test que falle si se rompen.**
- Emails con `Mail::fake()`; nada de envíos reales en la suite.

## 8. Security and compliance

- **PCI-DSS — fuera de alcance, y así debe seguir (FR-4, research.md #2):** el formulario no pide
  datos de tarjeta. Se añade un test que falla si aparece un campo `card`/`pan`/`cvv` en la request
  validada, para que un cambio futuro no meta a Servispin en alcance regulatorio por descuido.
- **Formulario público y anónimo:** `throttle:appointments` (ya configurado en `routes/web.php:105`)
  + honeypot. Sin captcha de terceros por ahora: el pago previo ya es el mejor filtro anti-spam que
  existe.
- **El enlace de reunión es un secreto de facto:** viaja solo por email al cliente y al técnico,
  nunca en respuestas de endpoints públicos, y las vistas de asistencia remota se marcan `noindex`.
- **Autorización:** `verifyPayment` va bajo el grupo `auth` de `/admin` y usa
  `spatie/laravel-permission`, ya presente. Ningún endpoint público puede alterar `payment_status`.
- **Trazabilidad de dinero:** `payment_verified_by` + `payment_verified_at` responden "¿quién dejó
  pasar esta llamada sin pago?" (FR-5).
- **Subida de fotos:** se reutiliza `ImageHelper` + limpieza en `onError`, ya probado en producción.
- **RGPD:** los datos son los mismos que ya recoge el formulario de citas; misma base legal.

## 9. Risks and open decisions

- ~~**R-1 · Laravel 10 está EOL; PHP 8.2 caduca el 31/12/2026**~~ **RIESGO RESUELTO (17/07/2026).**
  No por una mitigación de este plan, sino porque **Argenis subió el stack mientras se escribía el
  SSD**: `composer.json` declara hoy `laravel/framework ^13.8` y `php ^8.4` (research.md #1, 2ª
  corrección). Lo que queda no es un riesgo sino una consecuencia operativa: **PHPUnit 12 eliminó la
  anotación `/** @test */`** y los tests deben usar el atributo `#[Test]` — se descubrió por las
  malas, con `No tests found` sobre una suite entera ya escrita.
- **R-2 · Los emails se envían síncronos, sin colas** (research.md #1) → **Mitigación:** el envío ya
  ocurre en `onCommit`, así que un fallo de Resend no revierte la cita. Si la latencia molesta
  (NFR < 2 s), pasar los Mailables a `ShouldQueue`; requiere un worker corriendo. Aceptable de
  momento por el volumen previsto.
- **R-3 · El recordatorio de 30 min exige que el cron del servidor funcione de verdad.** Hoy solo
  hay una tarea diaria; si el cron no está activo en producción, nadie lo ha notado porque un
  recordatorio diario que no llega es invisible. **Mitigación:** verificar el cron **antes** de
  prometer el recordatorio de 30 min (T-C1), y que el comando sea idempotente para no duplicar
  emails si se ejecuta dos veces.
- **R-4 · La verificación manual es un cuello de botella humano** → **Mitigación:** FR-12 (liberar
  el hueco si no se verifica) + aviso a Cesar. Si el volumen crece, migrar a Hosted Checkout
  (research.md #2, la puerta ya está prevista).
- **R-5 · Huso horario mal gestionado = cliente que se pierde la cita pagada.** Es el fallo más
  probable del módulo y el más caro en reputación.
  > **CORRECCIÓN (17/07/2026).** Este apartado decía *"guardar siempre UTC en BD"*. **Es incorrecto
  > para este codebase, y aplicarlo rompería FR-7.** `config/app.php:73` declara
  > `'timezone' => 'Atlantic/Canary'` (no UTC), y `AvailabilityController:99` lo hardcodea: Eloquent
  > persiste en el huso de la app, así que `start_time` **ya contiene hora local de Canarias** en
  > todas las citas existentes. Guardar las remotas en UTC dejaría **dos convenciones en la misma
  > columna**; en verano (WEST = UTC+1) el desfase es de **1 hora — el tamaño de una cita**, y la
  > comprobación de solapamiento, que compara ambas, dejaría pasar solapes reales o inventaría
  > conflictos falsos **en silencio**. La mitigación propuesta causaba el fallo que pretendía evitar.
  > Misma lección que research #1: leer el runtime, no el requisito ideal.

  **Mitigación (corregida):** mantener la convención existente — persistir en el huso de la
  aplicación (`Atlantic/Canary`) igual que las citas presenciales —, guardar `client_timezone` en su
  propia columna, y **convertir solo en presentación** (slots, emails), con el huso explícito.
  El instante es inequívoco si la zona de almacenamiento es conocida y consistente, que es lo que
  FR-6 exige. Migrar todo el histórico a UTC es preferible en abstracto, pero es un refactor del
  módulo presencial en producción **sin tests**: trabajo propio, fuera de este alcance (research #1).
- **R-6 · El refresh token de Google se revoca a los 7 días si la app está en "Testing"**
  (research.md #7b-c). Es el riesgo más traicionero del módulo: **funciona durante la primera
  semana** y se rompe después, en producción, cuando ya nadie está mirando. **Mitigación
  obligatoria:** publicar la pantalla de consentimiento a **"In Production"** antes de dar el módulo
  por terminado (T036b), y alertar a Cesar cuando el provider falle (T024) en vez de fallar en
  silencio.
- **R-7 · El token OAuth vive en un fichero** (`oauth-token.json`, research.md #7d). Un despliegue
  que no lo persista mata la integración. **Mitigación:** ruta fuera del árbol desplegable,
  documentada en el README, y **fuera del repo** (contiene credenciales). Verificar que sobrevive a
  un deploy antes de cerrar la fase E.
- **R-8 · La cuenta de Google es de una persona, no de la empresa** (spec §9).
  **RIESGO ACEPTADO (17/07/2026, decisión de Argenis):** se usa la **cuenta personal de Cesar**. Es
  una decisión consciente, no un descuido: con un solo técnico, la cuenta de Cesar *es* la agenda del
  negocio. Consecuencia asumida: si Cesar cambia la contraseña, revoca el acceso en su cuenta de
  Google o deja la empresa, **se caen todos los Meet automáticos**. **Mitigación:** el
  `ManualMeetingLinkProvider` (FR-15) absorbe la caída sin perder ninguna cita pagada — Cesar pega el
  enlace a mano y el módulo sigue funcionando. Migrar a cuenta de empresa es la salida si entra un
  segundo técnico o si el negocio deja de depender de una sola persona.

- **R-9 · `config/google-calendar.php` está publicada con `default_auth_profile = 'service_account'`**
  (verificado en el repo, 17/07). Es **exactamente** el caso que research #3c dice que devuelve
  `Invalid conference type value` con un Gmail gratuito. **Mitigación:** `GOOGLE_CALENDAR_AUTH_PROFILE=oauth`
  en `.env` **antes** de T011d; si no, la prueba de humo falla y el error no menciona la causa por
  ningún lado.

**Decisiones pendientes (bloquean tareas concretas, no el arranque):**
- ~~**D-1** ¿Workspace o Gmail?~~ **RESUELTA (17/07/2026):** irrelevante. OAuth como usuario genera
  Meet en Gmail gratuito (research.md #6b). La fase E ya no está bloqueada.
- ~~**D-6** ¿Qué cuenta de Google se conecta?~~ **RESUELTA (17/07/2026):** la **cuenta personal de
  Cesar**, con el riesgo R-8 explícitamente aceptado. Desbloquea T011b.
- ~~**D-2** Campos del formulario de pago~~ **RESUELTA (17/07/2026):** se implementa la propuesta del
  spec — `payment_reference` + `payment_amount` + `payer_name`. Nada de tarjeta (FR-4).
- ~~**D-3** Precio y duración~~ **RESUELTA (17/07/2026):** **45 minutos, 30 €** (decisión de Argenis).
  En `config/remote_assistance.php` y sembrado vía `updateOrCreate`; Cesar los edita desde el CRUD de
  servicios que ya existe (`Admin/ServiceController`).
- **D-4** Plazo de liberación del hueco sin verificar (FR-12). Propuesta: 24 h o 2 h antes de la
  cita, lo que ocurra antes.
- **D-5** Política de "no show" (spec §9).

## 10. Traceability

| Requisito (spec.md) | Cubierto por |
|---|---|
| FR-1 (sin cuenta) | §5 `POST /asistencia-remota/store`, sin auth |
| FR-2 (referencia obligatoria) | §5 validación · §7 test |
| FR-3 (sin enlace hasta verificar) | §3 flujo `onCommit` · §5 respuesta 201 · §7 test crítico |
| FR-4 (sin datos de tarjeta) | §8 PCI · §7 test de guardarraíl |
| FR-5 (quién verificó) | §4 `payment_verified_by/at` · §8 |
| FR-6 (husos horarios) | §4 `client_timezone` · §5 slots · R-5 |
| FR-7 (sin solapamiento) | §3 reutiliza la comprobación existente · §7 test |
| FR-8 (enlace único) | §3 `MeetingLinkProvider` · §4 `meeting_url` |
| FR-9 (recordatorios 24 h / 30 min) | §3 scheduler · R-3 |
| FR-10 (visible en el calendario) | §4 `modality` + índice · reutiliza `AppointmentCalendarController` |
| FR-11 (sin dirección) | §4 (`address` ya nullable) · §5 validación |
| FR-12 (liberar hueco) | D-4 pendiente · §9 R-4 |
| FR-13 (alta desde el admin) | §3 `POST /admin/appointments/remote` · §5 |
| FR-14 (evento en Google Calendar) | §3 `GoogleMeetLinkProvider` · §4 `google_event_id`/`google_calendar_id` |
| FR-15 (el fallo no pierde la cita) | §3 degradación · §4 `meeting_link_failed_at` · §7 test |
| NFR-Seguridad | §8 |
| NFR-Rendimiento | §9 R-2 |
| NFR-Disponibilidad | §3 (fallo de provider no pierde la cita) · §9 R-6 |
