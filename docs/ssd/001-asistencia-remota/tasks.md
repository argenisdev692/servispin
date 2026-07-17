# Tasks: Asistencia Técnica Remota

> Phase 4 · BREAK DOWN TASKS — Cada tarea es ejecutable en aislamiento y deja el sistema funcional.
> `[P]` = paralelizable (sin ficheros ni dependencias compartidas).

**Feature ID:** 001-asistencia-remota
**Based on:** plan.md

---

## Estado de ejecución (17/07/2026)

| Fase | Estado | Notas |
|---|---|---|
| **A** — Fundamentos y esquema | ✅ **Hecho y verificado** | Migraciones **aditivas** (decisión de Argenis: no tocar `create_appointments_table`). |
| **B1** — Interfaz + provider manual | ✅ **Hecho y verificado** | `MeetingLinkProvider`, `ManualMeetingLinkProvider`, `MeetingLinkServiceProvider`. |
| **C** — US-1 Solicitar | ✅ **Hecho y verificado** | Incluye T015a (buffer por modalidad, research #9b). |
| **D** — US-2 Verificar y confirmar | ✅ **Hecho y verificado** | Autorización con guard `sanctum` explícito (ver abajo). |
| **D2** — US-6 Alta desde admin | ✅ **Hecho** | Backend en verde (8 tests). T031d hecho con `dateClick` (sin tocar `selectable`) + T031g (selector de huso). ⚠️ El frontend **no tiene test**: no hay Dusk ni Vitest en el repo; requiere prueba manual en el navegador. |
| **B2** — Google Meet | 🟡 **Código hecho** | `GoogleMeetLinkProvider` + test (T011c/e) + comando `google:oauth-token`. **Falta lo que solo puede hacer Argenis**: credenciales, token y prueba de humo real (T011b/d). |
| **E** — Endurecer Google | 🟡 **Lo automatizable, hecho** | `.gitignore` (T036a), aviso al técnico si falla (T035), README (T036). **Falta acción de Argenis**: publicar a Producción (T033) y verificar que el token sobrevive al deploy (T034). |
| **F** — Recordatorios | 🟡 **Código hecho** | 24 h (T038, arregla un bug latente: la remota recibía el email presencial sin enlace) + 30 min idempotente (T039) + tests (T040). **Falta T037**: verificar que el cron corre en producción — sin eso el de 30 min no llega, y es acción de Argenis, no código. |
| **G** — Cierre | 🟡 **Casi** | Landing (T041), cancelación con reembolso (T042/US-5), liberación FR-12 (T043) y READMEs (T046) hechos, con tests. **Falta T044** (`pint` + suite completa en verde, acción de Argenis) y T045 (repaso de trazabilidad). |

> ✅ **Suite en verde el 17/07/2026: 49 tests, 0 fallos** (`AppointmentRemoteScopesTest`,
> `ManualMeetingLinkProviderTest`, `BookingTest`, `BufferTest`, `PaymentVerificationTest`,
> `AdminBookingTest`). **El módulo es entregable con el proveedor manual**: solicitud → verificación
> del pago → confirmación → email con enlace funciona de punta a punta sin depender de Google.
>
> Cobertura de los FR que duelen si se rompen (plan §7): FR-2, FR-3, FR-4, FR-5, FR-7, FR-11 y FR-15
> tienen cada uno un test que falla si se rompen. Incluye un test de **no-regresión** que salta si el
> buffer de 4 h entre citas presenciales cambia de comportamiento.

**Dos tropiezos durante la puesta en verde, por si vuelven a aparecer:**
1. **`No tests found` con la suite entera escrita.** PHPUnit 12 **eliminó** la anotación
   `/** @test */`; con nombres de método en español (que no empiezan por `test`) no se detectaba
   ninguno. Se usa el atributo `#[Test]`. Ver research #1, 2ª corrección.
2. **`RoleDoesNotExist: no role named 'Admin' for guard 'web'`.** `assignRole('Admin')` con un
   *string* resuelve el nombre contra el guard por defecto del modelo (`web`) y los roles están
   sembrados en `sanctum`. Hay que pasarle el **objeto** `Role`, como ya hace `DatabaseSeeder`.
   Es el mismo desajuste de guards de abajo, entrando por otra puerta.

**Hallazgo de seguridad durante la fase D (verificado en el repo):** los roles y permisos se siembran
con `guard_name => 'sanctum'` (`DatabaseSeeder`), pero `config/auth.php:17` declara
`'defaults.guard' => 'web'`. Spatie resuelve el guard por defecto del modelo `User` —que sale `web`—
y buscaría un rol `Admin` con guard `web` que no existe: el middleware `role:`/`permission:` habría
devuelto **403 a todo el mundo, Cesar incluido**. Por eso la autorización se comprueba en las
FormRequest con el guard explícito: `hasRole('Admin', 'sanctum')`. Es una solución quirúrgica que no
toca la autenticación del resto de la app; arreglar el desajuste de guards de raíz es trabajo aparte.

---

## Phase A — Fundamentos y esquema

- [ ] **T001** Crear `config/remote_assistance.php` (`meeting_provider` = `manual` por defecto,
      precio/duración por defecto, plazo de liberación de hueco D-4).
- [ ] **T002** Migración: añadir campos remotos y de pago a `appointments` (plan §4, migración A),
      con índices `(modality,status)` y `(payment_status)`. Migración puramente aditiva:
      `modality` default `'onsite'` ⇒ las citas existentes no cambian de comportamiento.
- [ ] **T003** [P] Migración: añadir `active` e `is_remote` a `services` (plan §4, migración B).
      **Arregla de paso el bug de research.md #5**: `getServices()` filtra por una columna que no
      existe y hoy revienta.
- [ ] **T004** [P] Actualizar `Appointment`: `$fillable`, casts de fechas de pago, constantes
      `MODALITY_*` y `PAYMENT_*`, scopes `remote()` y `pendingPaymentVerification()`.
- [ ] **T005** [P] Actualizar `Service`: `$fillable` con `active`/`is_remote`, scopes `active()` y
      `remote()`.
- [ ] **T006** Seeder: servicio "Asistencia Técnica Remota" con `is_remote=true`, precio y duración
      (⚠️ requiere **D-3**; usar valores provisionales y marcarlo si D-3 sigue abierta).
- [ ] **T007** [P] Tests unitarios del modelo: scopes y casts (`tests/Unit/`).

**Verificación de fase:** `php artisan migrate` corre limpio y el módulo de citas presencial sigue
funcionando exactamente igual que antes.

---

## Phase B — Enlace de reunión (interfaz + manual + Google Meet)

> Revisión 17/07: la generación automática entra aquí, ya no en una fase 2 bloqueada.
> OAuth funciona con Gmail gratuito (research.md #6b).

- [ ] **T008** Crear la interfaz `MeetingLinkProvider` (plan §3).
- [ ] **T009** Implementar `ManualMeetingLinkProvider` (`isAutomatic() = false`). Sigue siendo el
      **fallback** que garantiza FR-15.
- [ ] **T010** Crear `MeetingLinkServiceProvider` que resuelve la implementación desde config y
      registrarlo en `config/app.php`.
- [ ] **T011** [P] Test unitario de `ManualMeetingLinkProvider` y de la resolución vía config.
- [ ] **T011a** Instalar el paquete y publicar su config. **Usar `-W`** (research.md #8):
      ```bash
      ./vendor/bin/sail composer require spatie/laravel-google-calendar -W
      ```
      Sin `-W` falla con un muro de advisories de `firebase/php-jwt` que **no** es un problema de
      seguridad: es el pin de `guzzlehttp/psr7: 2.5.0` del lock impidiendo llegar a
      `google/apiclient` v2.19 (la única rama que admite `php-jwt ^7.0`, ya sin advisory).
      ⚠️ **No** ignorar advisories con `policy.advisories.*`. Revisar el diff de `composer.lock`:
      `-W` puede mover más paquetes de la cuenta.
- [ ] **T011b** Generar el token OAuth de la **cuenta personal de Cesar** (D-6, R-8 aceptado):
      `oauth-credentials.json` + `oauth-token.json`, con perfil `oauth`, **no** `service_account`
      (research.md #6b). Fuera del repo (R-7).
      ⚠️ **`config/google-calendar.php` ya está publicada con `default_auth_profile = 'service_account'`**
      (R-9, verificado en el repo). Poner **`GOOGLE_CALENDAR_AUTH_PROFILE=oauth`** en `.env` **antes**
      de T011d, o la prueba de humo falla con `Invalid conference type value` sin decir por qué.
      Requiere un paso humano: el flujo OAuth es interactivo y lo tiene que autorizar Cesar con su
      cuenta.
- [ ] **T011c** `GoogleMeetLinkProvider`: crear evento, `addMeetLink()`, leer
      `$event->googleEvent->getHangoutLink()`, persistir `google_event_id` y `google_calendar_id`.
- [ ] **T011d** **Prueba de humo antes de seguir:** crear un evento real y comprobar que devuelve un
      enlace de Meet válido. Si aquí sale `Invalid conference type value`, el perfil está en
      `service_account` en vez de `oauth` (research.md #3c vs #6b). *No construir la fase D encima
      sin haber pasado esta prueba.*
- [ ] **T011e** Test de integración del provider con la API de Google mockeada.

**Verificación de fase:** cambiar `meeting_provider` en config intercambia la implementación sin
tocar el resto del código, y el modo `google_meet` devuelve un enlace real.

---

## Phase C — US-1: Solicitar asistencia remota

- [ ] **T012** `StoreRemoteAssistanceRequest`: validación completa (plan §5), **sin dirección**
      (FR-11), con `payment_reference`/`payment_amount`/`payer_name` obligatorios (FR-2) y
      `client_timezone` válido (FR-6). ⚠️ **D-2**.
- [ ] **T013** Test de guardarraíl PCI: la request rechaza/ignora cualquier campo tipo
      `card_number`, `pan`, `cvv` (FR-4, plan §8). *Escribir este test antes que el controlador.*
- [ ] **T014** `RemoteAssistanceController@store`: reutiliza `TransactionService` y `ImageHelper`
      siguiendo el patrón de `AppointmentController@store`, creando la cita con
      `modality=remote`, `status=Pending`, `payment_status=claimed`.
- [ ] **T015** Reutilizar la comprobación de solapamiento existente para citas remotas (FR-7).
      Extraerla de `AppointmentController` a un sitio compartido para no duplicarla.
      ⚠️ El módulo presencial **no tiene tests** (plan §7). Extraer y que el flujo presencial pase a
      usar el código movido es refactorizar producción sin red. Hacerlo a comportamiento idéntico y
      cubrirlo con los tests nuevos antes de tocar el presencial.
- [ ] **T015a** **Buffer según modalidad (research #9b, plan §4b).** `AvailabilityController:169`
      aplica 240 min de buffer alrededor de CADA cita porque el técnico conduce. Si se hereda tal
      cual, una videollamada de 30 min bloquea **8,5 h** — el tiempo muerto que spec §2 quiere
      monetizar. El buffer pasa a depender del par de modalidades:
      presencial→presencial **240** (sin tocar) · remota→remota **0** ·
      mixto **`config('remote_assistance.buffer.mixed_minutes')`** (default 60).
      *Sin esta tarea, cada cita remota vacía la agenda del día.*
- [ ] **T015b** Tests del buffer: dos remotas seguidas caben en el mismo día · una remota **no**
      bloquea 8,5 h · una presencial sigue bloqueando 4h+4h **exactamente igual que antes**
      (test de no-regresión del flujo que hoy funciona).
- [ ] **T016** Soporte de husos en los slots: aceptar `timezone` y devolver hora local + huso
      explícito (FR-6, R-5).
      ⚠️ **CORREGIDO 17/07: NO guardar en UTC.** El plan decía "guardar siempre UTC" y eso
      **rompería FR-7**: `config/app.php:73` es `Atlantic/Canary`, así que `start_time` ya contiene
      hora local canaria en todas las citas existentes. Guardar las remotas en UTC dejaría dos
      convenciones en la misma columna y el solapamiento fallaría en silencio (1 h de desfase en
      verano). **Persistir en el huso de la app, igual que las presenciales**, guardar
      `client_timezone` aparte, y convertir **solo en presentación**. Ver plan §9 R-5.
- [ ] **T017** `RemoteAssistanceRequested` mailable + vista, **sin enlace de videollamada** (FR-3).
- [ ] **T018** Rutas públicas `/asistencia-remota/{,slots,store}` con `throttle:appointments`.
- [ ] **T019** Vista del formulario (`remote-assistance/book.blade.php`) con honeypot, instrucciones
      del QR y campos de declaración de pago. `noindex`.
- [ ] **T020** Tests feature: creación correcta · falta referencia → 422 · hueco ocupado → 422 ·
      **el email enviado NO contiene enlace** (test crítico de FR-3).
- [ ] **T021** Verificar contra los criterios de aceptación de US-1 en spec.md.

**Verificación de fase:** un cliente puede solicitar y recibe email sin enlace. Nadie puede obtener
un enlace todavía — porque aún no existe forma de generarlo.

---

## Phase D — US-2: Verificar el pago y confirmar

- [ ] **T022** `VerifyPaymentRequest`: `decision` in `verify,reject`; `meeting_url` obligatorio y
      URL válida si `decision=verify` y el provider es manual.
- [ ] **T023** `RemoteAssistanceAdminController@verifyPayment` dentro de `TransactionService`:
      sella `payment_verified_by/at`, pasa a Confirmed y resuelve el enlace vía provider.
- [ ] **T024** Blindaje de disponibilidad (plan §3): si un provider automático lanza excepción, se
      registra, la cita **se confirma igual** con `meeting_url=null` y se avisa a Cesar.
- [ ] **T025** [P] `RemoteAssistanceConfirmed` mailable + vista, **con enlace**, huso explícito y
      aviso de "detén el coche en lugar seguro".
- [ ] **T026** [P] `RemoteAssistanceRejected` mailable + vista, sin enlace, con motivo.
- [ ] **T027** Ruta `PATCH /admin/appointments/{id}/verify-payment` bajo `auth` + permiso.
- [ ] **T028** Bandeja de verificación en admin: listado filtrado por
      `modality=remote & payment_status=claimed`, mostrando referencia, importe y datos de la avería
      para cotejar con SumUp. Botones Confirmar / Rechazar.
- [ ] **T029** Distinguir visualmente las citas remotas en el calendario existente (FR-10).
- [ ] **T030** Tests feature: verify → Confirmed + enlace + email con enlace · reject → Cancelled +
      hueco liberado · **sin auth → 403/401** (FR-5) · provider que falla no impide confirmar.
- [ ] **T031** Verificar contra los criterios de aceptación de US-2 en spec.md.

**Verificación de fase:** el circuito completo funciona de punta a punta. **El módulo ya es
entregable aquí** — todo lo que sigue es mejora.

---

## Phase D2 — US-6: Alta de cita remota desde el calendario del admin

> Idea de Argenis (17/07): el cliente llama por teléfono, paga por QR, y Cesar la da de alta él
> mismo desde el hueco del FullCalendar sin pasar por la web.

- [ ] **T031a** `StoreAdminRemoteAppointmentRequest`: datos del cliente escritos a mano (sin select),
      `payment_verified` booleano, `meeting_url` obligatorio si provider manual + verificado.
- [ ] **T031b** `RemoteAssistanceAdminController@store` (plan §5, `POST /admin/appointments/remote`):
      con `payment_verified=true` → Confirmed + enlace + email en un paso, sellando
      `payment_verified_by` = usuario actual (FR-5).
- [ ] **T031c** **FR-3 también aplica aquí:** con `payment_verified=false` la cita queda Pending y
      **sin enlace**. El atajo del admin no puede ser una puerta trasera al control de pago.
- [x] **T031d** Frontend: al pulsar un hueco libre del FullCalendar existente, abrir el formulario de
      alta remota. Reutilizar `AppointmentCalendarController` y la vista de calendario actuales.
      **Hecho (17/07).** Decisiones tomadas al implementarlo:
      - Se usa **`dateClick`**, no `select` + `selectable: true`. US-6 dice "cuando **pulso** sobre un
        hueco libre", y `dateClick` no exige tocar `selectable` ⇒ **cero cambios de comportamiento**
        en un fichero de 745 líneas, en producción y sin tests. El comentario de la línea 310
        (*"You might enable this later to create new appointments by clicking"*) anticipaba
        exactamente esta feature.
      - **`start_time` no se convierte de huso.** `info.dateStr` ya viene en el `timeZone` del
        calendario (`Atlantic/Canary`), que es como se persiste. Un `toISOString()` habría mandado
        UTC y creado la cita **una hora antes** de la pulsada, en verano: R-5 por la puerta de atrás.
      - Fecha y hora quedan **editables** en el modal: en la vista de mes el clic no trae hora
        (`allDay`), y así funciona en las cuatro vistas sin obligar a cambiar de vista.
      - **El solapamiento no se duplica en JS.** FR-7 lo decide el backend (`SchedulingService`, ya
        con test en verde); el frontend solo muestra el 422. Duplicar la regla daría dos verdades que
        pueden contradecirse.
- [x] **T031g** [NUEVO — hallazgo de diseño, 17/07] **Selector de huso horario del cliente en el alta
      del admin.** No estaba en el plan y es necesario: en el formulario público el huso lo detecta el
      navegador **del cliente**, pero aquí el navegador es el **de Cesar** (Atlantic/Canary) mientras
      el cliente está al teléfono desde cualquier parte del mundo. Autodetectar habría guardado un
      `client_timezone` **falso**, y ese dato es justo el que usan los emails para decirle al cliente
      su hora local ⇒ le diríamos una hora equivocada a alguien que ya pagó. Es **R-5 colándose por
      US-6**. Se añade un `<select>` con la lista completa de husos, por defecto el del negocio (el
      caso habitual: cliente local que llama por teléfono).
- [ ] **T031e** Tests: alta verificada → Confirmed + email con enlace · alta sin verificar → Pending
      sin enlace · hueco ocupado → 422 (FR-7) · sin auth → 403.
- [ ] **T031f** Verificar contra los criterios de aceptación de US-6 en spec.md.

---

## Phase E — Endurecer la integración con Google

> Ya no está bloqueada: D-1 quedó resuelta el 17/07 (research.md #6b). El provider se construye en
> la fase B; aquí se hace que **sobreviva en producción**.

- [ ] **T032** ~~Resolver D-1~~ — **RESUELTA**: OAuth funciona con Gmail gratuito. Tarea eliminada.
- [ ] **T033** **Publicar la pantalla de consentimiento a "In Production"** en Google Cloud Console.
      **Sin esto el módulo se rompe a los 7 días** (R-6, research.md #7b-c). No es opcional ni
      cosmético: es la diferencia entre funcionar y funcionar *una semana*.
- [ ] **T034** Verificar que `oauth-token.json` **sobrevive a un despliegue real** (R-7). Probarlo
      desplegando, no razonándolo.
- [ ] **T035** Alerta a Cesar cuando el provider falle (email o marca visible en el calendario), para
      que `meeting_link_failed_at` no pase inadvertido y una cita llegue sin enlace (FR-15).
- [ ] **T036** Documentar en el README: alta de credenciales, perfil `oauth` (**no**
      `service_account`, y por qué), publicación a Producción, y ubicación del token.
      ⚠️ Nunca commitear `oauth-token.json` ni `oauth-credentials.json`.
- [ ] **T036a** Añadir ambos ficheros a `.gitignore` **antes** de generarlos.

---

## Phase F — US-3: Recordatorios

- [ ] **T037** **Verificar primero que el cron corre en producción** (R-3). Si no corre, ni el
      recordatorio diario actual está funcionando — y eso es un hallazgo por sí solo.
- [ ] **T038** Extender `SendAppointmentReminders` para incluir citas remotas con su enlace y huso.
- [ ] **T039** `SendImminentReminders` (T-30 min), idempotente (marca de "recordatorio enviado" para
      no duplicar), programado cada 5 min en `Kernel.php`.
- [ ] **T040** Tests: se envía a confirmadas · **no** se envía a canceladas · no se duplica al
      ejecutar dos veces.

---

## Phase G — US-4/US-5 y cierre

- [ ] **T041** [P] Landing con el GIF publicitario, precio y duración (US-4), `noindex` fuera.
      Vigilar que no degrade la home (spec US-4).
- [ ] **T042** [P] Cancelación de cita remota: marca `refund_pending` y notifica (US-5).
- [ ] **T043** Liberación automática del hueco sin verificar (FR-12) — ⚠️ requiere **D-4**.
- [ ] **T044** Pasar `php artisan pint` y la suite completa.
- [ ] **T045** Revisión de trazabilidad: cada FR de spec.md tiene código **y** test (plan §10).
- [ ] **T046** Documentar el módulo en el README: flujo, config del provider, y **el hecho de que la
      verificación del pago es manual y por qué**.

---

## Orden recomendado y puntos de corte

> **REVISIÓN (17/07/2026): la fase B se parte en dos.** B mezclaba trabajo sin dependencias externas
> (interfaz + provider manual) con trabajo que exige credenciales de Google (T011a-e). Tal cual
> estaba, un tropiezo con Google bloqueaba la fase C, que **no depende de Google para nada**.
>
> - **B1** = T008, T009, T010, T011 — interfaz, `ManualMeetingLinkProvider`, ServiceProvider, tests.
> - **B2** = T011a…T011e — Google Meet vía OAuth. Requiere las credenciales de Cesar (D-6).

```
A → B1 → C → D → D2  ◄── MÓDULO COMPLETO Y ENTREGABLE con provider manual.
                ↓        No depende de Google. Cortar aquí si aprieta el tiempo.
              B2 + E     ◄── Google automático + endurecerlo
                ↓           (T011d y T033 NO son opcionales)
                F        ◄── recordatorios (T037 antes de prometer nada)
                ↓
                G        ◄── landing, cancelación, cierre
```

Esto **no contradice** el punto de corte original: lo hace ejecutable sin esperar a Google. El
`ManualMeetingLinkProvider` existe justo para esto (plan §3), así que el circuito completo —
solicitud, verificación de pago, confirmación, email con enlace — funciona de punta a punta antes de
que Google entre en escena.

**Dos tareas que no se saltan aunque haya prisa:**
- **T011d** — prueba de humo del Meet real. Si el perfil quedó en `service_account`, todo lo
  construido encima se cae, y el error (`Invalid conference type value`) no dice eso por ningún lado.
- **T033** — publicar a Producción. Es el único fallo del módulo que aparece **después** de darlo
  por terminado.

**Decisiones que hay que cerrar antes de tocar su tarea:**
| Decisión | Bloquea | Estado |
|---|---|---|
| ~~D-1 Workspace vs Gmail~~ | ~~E~~ | **RESUELTA 17/07** — OAuth funciona con Gmail |
| ~~D-2 Campos de pago~~ | T012 | **RESUELTA 17/07** — referencia + importe + nombre (FR-4: nada de tarjeta) |
| ~~D-3 Precio y duración~~ | T006 | **RESUELTA 17/07** — **45 min, 30 €**; Cesar los edita en el CRUD de servicios |
| ~~D-6 Cuenta Google~~ | T011b | **RESUELTA 17/07** — cuenta personal de Cesar; **R-8 aceptado** |
| D-4 Plazo de liberación | T043 | **Implementada con la propuesta** (24 h / 2 h antes), configurable en `remote_assistance.php`. Ajústala si hace falta. |
| D-5 Política de no-show | — (solo texto legal) | Abierta — no bloquea |

---
**Convención de commits:** `feat(remote-assistance): T0XX descripción corta`
