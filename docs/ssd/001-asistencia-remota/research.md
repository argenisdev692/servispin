# Research: Asistencia Técnica Remota

> Phase 2 · RESEARCH — Hallazgos verificados con búsqueda real (julio 2026). Cada decisión técnica
> del plan.md debe poder trazarse a un hallazgo de aquí.

**Feature ID:** 001-asistencia-remota
**Fecha de la investigación:** 2026-07-16

---

## #1 — Estado del stack actual del proyecto

**Hallazgo (leído del repo, no de la web):**
- `composer.json`: `laravel/framework: ^10.10`, `php: ^8.1`, `livewire/livewire: ^2.11`,
  `laravel/jetstream: ^3.2`, `resend/resend-laravel: ^1.4`, `spatie/laravel-permission: ^5.10`.
- No existe `app/Jobs/` ni uso de colas: los emails se envían de forma síncrona dentro del request
  (`AppointmentController::sendConfirmationEmail`).
- `app/Console/Kernel.php` solo programa una tarea: `appointments:send-reminders` diaria a las 09:00.

**Verificado en la web:** Laravel 10 está **fuera de soporte** (el soporte de seguridad terminó a
principios de 2025).

> **CORRECCIÓN (17/07/2026):** este apartado afirmaba que el proyecto corría sobre **PHP 8.1 EOL**.
> **Es falso.** `composer.json` declara `^8.1` como *suelo mínimo*, no como versión instalada. La
> salida real de Composer en el entorno Sail dice `your php version (8.2.32)`: el runtime es
> **PHP 8.2.32**, que **sigue con soporte de seguridad hasta el 31 de diciembre de 2026** (soporte
> activo terminado, solo parches de CVEs críticos). Lección: leer el runtime, no el constraint.

> **SEGUNDA CORRECCIÓN (17/07/2026) — TODO ESTE APARTADO ESTABA OBSOLETO.**
> Leído de `composer.json` **hoy**, el stack real es:
>
> | Paquete | Lo que decía este research | **Lo que hay de verdad** |
> |---|---|---|
> | `laravel/framework` | `^10.10` (EOL) | **`^13.8`** |
> | `php` | `^8.1` → runtime 8.2.32 | **`^8.4`** |
> | `livewire/livewire` | `^2.11` | **`^3.8.2`** |
> | `laravel/jetstream` | `^3.2` | **`^5.5`** |
> | `spatie/laravel-permission` | `^5.10` | **`^8.3`** |
> | `laravel/sanctum` | — | **`^4.3`** |
> | `phpunit/phpunit` | (asumido ^10) | **`^12.5.12`** |
>
> **Argenis subió el stack entre el 16 y el 17 de julio**, mientras se escribía este SSD. `git status`
> lo delataba y nadie lo miró: `M composer.json`, `M composer.lock`, y el movimiento
> `app/Http/Livewire/` → `app/Livewire/`, que es exactamente la convención de Livewire 3.
>
> **R-1 queda RESUELTO**, no mitigado: ya no hay framework EOL ni PHP a punto de caducar.
>
> **Es la tercera vez que este SSD comete el mismo error**, y ya no es casualidad:
> 1. Leer `php: ^8.1` (constraint) en vez del runtime 8.2.32.
> 2. Proponer "guardar en UTC" sin leer `config/app.php` (R-5).
> 3. Reutilizar "la comprobación de solapamiento" sin leer que aplicaba un buffer de 4 h (#9).
> 4. Y ahora: dar por bueno un `composer.json` **que ya había cambiado en el working tree**.
>
> La lección se amplía: **leer el estado actual del repo, no la foto que se tomó al empezar.** Un SSD
> escrito en dos días puede quedar obsoleto por el trabajo del propio equipo mientras se escribe.
>
> **Consecuencia práctica ya encontrada:** los tests del módulo se escribieron con `/** @test */` y
> **PHPUnit 12 eliminó el soporte de esa anotación**. `php artisan test` respondía `No tests found`
> con 60+ tests escritos y en verde a nivel de código. Se migraron al atributo `#[Test]`.

**Implicación para el plan (revisada):** ya no hay riesgo de stack EOL. El módulo se construye sobre
Laravel 13 / PHP 8.4 / PHPUnit 12, con las consecuencias concretas de la tabla de arriba (atributos
en vez de anotaciones, Livewire 3, Spatie Permission 8).

⚠️ **Lo que NO se ha verificado:** que el esqueleto siga siendo el de Laravel 10 (`app/Http/Kernel.php`
existe y no hay `bootstrap/providers.php`) es compatible con el core de Laravel 13 — funciona, y por
eso `config/app.php` sigue siendo el sitio donde registrar providers, pero es una configuración
híbrida que conviene tener presente si algo se comporta de forma rara.

Fuentes: [endoflife.date/laravel](https://endoflife.date/laravel) ·
[PHP: Supported Versions](https://www.php.net/supported-versions.php) ·
[PHP 8.2 EOL](https://tuxcare.com/blog/php-8-2-eol/)

---

## #2 — SumUp: por qué el flujo por QR nos saca de PCI-DSS

**Contexto:** decisión del usuario (16/07/2026) — no se integra la API de SumUp. El cliente paga con
el QR que Servispin ya tiene y luego declara el pago en un formulario.

**Hallazgo:** la alternativa descartada (Hosted Checkout) sería `POST https://api.sumup.com/v0.1/checkouts`
con `hosted_checkout.enabled: true`, devolviendo `hosted_checkout_url`; la documentación advierte
explícitamente: *"Treat the hosted page as the customer interface, but use the checkout status and
webhook events as the source of truth for your backend order state"*, y la sesión caduca a los 30
minutos. Requiere `merchant_code` y API key.

**Implicación para el plan:**
1. Con el flujo por QR, **el sistema no puede verificar el pago por sí mismo**. La verificación
   humana no es una simplificación provisional: es el único control que existe. Por eso FR-3 es
   innegociable.
2. Como el dato de tarjeta lo captura SumUp y nunca toca nuestro servidor, Servispin **no entra en
   alcance PCI-DSS** — siempre que el formulario no pida número de tarjeta (FR-4). Si algún día se
   pide el PAN "para verificar", el alcance regulatorio cambia por completo.
3. Puerta de salida: si el volumen crece y la verificación manual molesta, se migra a Hosted
   Checkout + webhook sin rehacer el módulo, siempre que el estado de pago viva en su propio campo
   desde el día 1.

Fuentes: [SumUp Hosted Checkout](https://developer.sumup.com/online-payments/checkouts/hosted-checkout) ·
[SumUp API Reference](https://developer.sumup.com/api)

---

## #3 — Google Meet: la restricción que decide la arquitectura del enlace

**Pregunta investigada:** ¿puede el sistema generar automáticamente un enlace de Meet?

**Hallazgos:**

a) **`spatie/laravel-google-calendar` v3.8.4** es compatible con Laravel 10 y PHP 8.1
   (`php: ^7.2|^8.0`, `laravel: ^6.0|...|^12.0`). Tiene método `addMeetLink()`. La librería **no es
   el problema**.

b) **La librería es irrelevante para esta restricción.** `spatie/laravel-google-calendar` es un
   envoltorio sobre `google/apiclient`; el error lo devuelve el servidor de Google. Cambiar de
   paquete no cambia el resultado.

c) **Una service account sobre un calendario de Gmail personal NO puede crear conferencias.** Está
   ampliamente reportado el error **`Invalid conference type value`** al enviar
   `conferenceData.createRequest` con `conferenceSolutionKey.type = hangoutsMeet` y
   `conferenceDataVersion: 1`. El "workaround" que circula (omitir `conferenceDataVersion`) no crea
   la reunión: elimina el síntoma, no el problema.

d) En la discusión del propio repo de Spatie, **la única confirmación de que funciona viene de un
   usuario probando con GSUITE (Workspace)**: *"I am testing it with a GSUITE using a service
   account, it works correctly"*. Crear Meet vía service account exige Workspace + **domain-wide
   delegation**, que se configura en el Admin Console (Security → API Controls) y requiere
   credenciales de administrador de Workspace — es decir, cuenta de pago.

e) **La vía OAuth (no service account) sí funciona con un Gmail normal**, pero tiene su propia
   trampa: si la pantalla de consentimiento está en estado **"Testing"** con tipo **"External"**,
   Google **revoca el refresh token a los 7 días**. El scope de Calendar es "sensible", así que
   entra en esa regla.

f) **Incertidumbre no resuelta (declarada como tal):** las fuentes **se contradicen** sobre el caso
   "Producción + sin verificar + scope sensible". Unas afirman que el límite de 7 días aplica solo
   al estado Testing; otras hablan de *"refresh tokens issued by unverified apps"* en general.
   Solo hay dos certezas: en Testing caduca a los 7 días, y verificado en Producción no caduca.
   **El plan no se apoya en el punto intermedio.**

**Implicación para el plan (revisada 17/07/2026):** ver #6. La vía OAuth (e) elimina la dependencia
de Workspace, así que la generación automática **sí entra en el alcance principal**. El enlace se
sigue modelando **detrás de una interfaz**, pero ahora con Google Meet vía OAuth como implementación
por defecto y la manual como red de seguridad (no como opción principal).

Fuentes: [spatie/laravel-google-calendar](https://github.com/spatie/laravel-google-calendar) ·
[Discusión #203](https://github.com/spatie/laravel-google-calendar/discussions/203) ·
[Invalid conference type value](https://github.com/googleapis/google-api-nodejs-client/issues/3272) ·
[Google issue tracker 254292386](https://issuetracker.google.com/issues/254292386) ·
[Automating Google Meet Creation](https://dev.to/himanshusinghtomar/automating-google-meet-creation-14mo) ·
[Google OAuth refresh token](https://www.unipile.com/google-oauth-refresh-token/) ·
[OAuth 2.0 Policies](https://developers.google.com/identity/protocols/oauth2/policies)

---

## #4 — Jitsi: el "100% gratis" del doc no es exacto

**Hallazgo:** desde el **24 de agosto de 2023**, `meet.jit.si` **ya no permite crear salas de forma
anónima**: quien abre la sala debe autenticarse (Google, GitHub o Facebook). Los invitados sí pueden
entrar sin cuenta. Además, **la autenticación JWT no funciona contra el servidor público** de
`meet.jit.si`: requiere instancia propia o JaaS.

**Alternativa:** **JaaS (8x8)** — tier Dev gratuito con **25 usuarios activos mensuales** y minutos
ilimitados hasta 25 endpoints. Las reuniones se sirven en el dominio `8x8.vc`, no en `meet.jit.si`.
Cada participante requiere un JWT firmado con la clave privada de la app.

**Implicación para el plan:** Jitsi sigue siendo una alternativa válida y sin coste para el volumen
esperado (spec §6: unidades a decenas de citas/mes, muy por debajo de 25 MAU si solo el técnico
cuenta como endpoint autenticado), pero **no es "pegar una URL aleatoria y ya"** como sugería el
doc. Requiere alta en 8x8 y firma de JWT. Queda como la implementación automática preferente **si**
Servispin no tiene Workspace.

Fuentes: [Authentication on meet.jit.si](https://jitsi.org/blog/authentication-on-meet-jit-si/) ·
[JaaS FAQ](https://developer.8x8.com/jaas/docs/faq/) ·
[lib-jitsi-meet tokens](https://github.com/jitsi/lib-jitsi-meet/blob/master/doc/tokens.md)

---

## #5 — Bug encontrado en el código actual (no relacionado con el módulo, pero bloqueante)

**Hallazgo (leído del repo):** `AppointmentController::getServices()` ejecuta
`Service::where('active', true)->get()`, pero la tabla `services` **no tiene columna `active`**
(migración `2025_04_10_212535_create_services_table.php`) y el modelo `Service` no la declara en
`$fillable`. Ninguna migración posterior la añade.

**Implicación:** la ruta `GET /appointments/services` (`routes/web.php:107`) debería reventar con un
error SQL de columna inexistente. Como el módulo remoto necesita listar servicios, hay que decidir:
añadir la columna `active` (útil: permite despublicar servicios) o quitar el filtro. Se recomienda
**añadir la columna**, porque además hace falta para marcar qué servicios son remotos.

---

## #6 — OAuth de Spatie: por aplicación, no por usuario (aportación de Argenis, 17/07/2026)

**Contexto:** propuesta de usar `spatie/laravel-google-calendar` + `addMeetLink()` + Laravel
Socialite para que cada profesional conecte su propia cuenta de Google.

**Hallazgos:**

a) **`addMeetLink()` está documentado en el README oficial**, confirmado textualmente:
   `$event->addMeetLink(); // optionally add a google meet link to the event`. No hace falta
   construir `conferenceData` a mano. **Corrige el enfoque manual que sugería #3c.**

b) **El punto clave: OAuth como usuario evita la restricción de #3c-d.** El bloqueo de
   `Invalid conference type value` es específico de **service accounts** sobre calendarios de Gmail
   personal. Autenticando como el propio usuario de Google, un Gmail gratuito **sí** crea enlaces de
   Meet. **Esto elimina D-1 como bloqueante**: ya no importa si Servispin tiene Workspace.

c) **Pero el OAuth de Spatie es de una sola cuenta, por aplicación.** El README describe un flujo que
   se configura una vez, guardando `oauth-credentials.json` y `oauth-token.json` en rutas estáticas
   del proyecto. `GoogleCalendarFactory::createOAuthClient` hace
   `$client->setAccessToken(file_get_contents($authProfile['token_json']))` — siempre el mismo
   cliente. Existe una discusión abierta titulada literalmente *"Impossible to define dynamic user
   token"*. Tokens por usuario exigirían sobreescribir la factory o hacks con `Config::set()`.

d) **Socialite no aporta nada aquí, y por una razón de negocio, no técnica.** El modelo "cada
   profesional conecta su cuenta" es correcto para un SaaS multiprofesional (médicos, profesores).
   Servispin tiene **un único técnico** (spec §9, asunción): un solo calendario corporativo, el de
   Cesar. Ese es justo el caso que el perfil OAuth único de Spatie cubre de forma nativa. Añadir
   Socialite + tokens por usuario sería construir infraestructura para un problema que Servispin no
   tiene.

**Implicación para el plan:** perfil `oauth` de Spatie con **una sola conexión** (la cuenta de
Servispin). Sin Socialite en este módulo. Si algún día hay un segundo técnico, se reevalúa — y ahí
sí habrá que sobreescribir la factory.

Fuentes: [README de spatie/laravel-google-calendar](https://github.com/spatie/laravel-google-calendar/blob/main/README.md) ·
[Discusión #179 "Impossible to define dynamic user token"](https://github.com/spatie/laravel-google-calendar/discussions/179) ·
[Discusión #177](https://github.com/spatie/laravel-google-calendar/discussions/177)

---

## #7 — El refresh token es el punto frágil de la vía OAuth

**Hallazgos:**

a) **Laravel Socialite devuelve `refreshToken` null de forma intermitente** si no se pide
   `access_type => 'offline'` (issue #715 del repo de Socialite). Sin refresh token, la conexión
   muere cuando caduca el access token (~1 h). Como #6d descarta Socialite, esto solo aplica si se
   reintrodujera; se registra para que nadie lo redescubra por las malas.

b) **La regla de los 7 días de #3e aplica igual al token de Spatie.** Da igual cómo se genere el
   refresh token (Socialite, quickstart de Google, o a mano): si la pantalla de consentimiento está
   en **Testing + External**, Google lo revoca a los 7 días. El scope de Calendar es sensible.

c) **Consecuencia operativa:** la app **debe publicarse a "In Production"** en Google Cloud Console.
   Si no, el módulo funciona una semana y deja de funcionar sin previo aviso — el peor modo de fallo
   posible, porque aparece en producción días después de dar el trabajo por terminado.

d) **Riesgo de infraestructura:** el token vive en un fichero (`oauth-token.json`). Si el despliegue
   sobreescribe o no persiste ese fichero, la conexión se pierde. No debe commitearse al repo
   (contiene credenciales).

**Implicación para el plan:** riesgo R-6. Se mantiene el proveedor manual como **fallback**
(no como opción por defecto): si Google falla o el token muere, la cita pagada se confirma igual y
Cesar pega el enlace. Ver plan §3.

Fuentes: [Socialite issue #715](https://github.com/laravel/socialite/issues/715) ·
[Google OAuth refresh token](https://www.unipile.com/google-oauth-refresh-token/) ·
[OAuth 2.0 Policies](https://developers.google.com/identity/protocols/oauth2/policies)

---

## #8 — Instalación: el advisory de `php-jwt` es un síntoma, no la causa (17/07/2026)

**Contexto:** `composer require spatie/laravel-google-calendar` falla con un muro de advisories de
seguridad sobre `firebase/php-jwt`. Parece un bloqueo de seguridad. **No lo es.**

**Diagnóstico (leído con cuidado de la salida de Composer):**

```
google/apiclient[v2.17.0…v2.18.4] require firebase/php-jwt ^6.0 → affected by security advisories
google/apiclient[v2.19.0…v2.19.4] require guzzlehttp/psr7 ^2.6  → but the package is fixed to 2.5.0
                                                                   (lock file version) by a partial update
```

La v2.19 **no se rechaza por el advisory**, sino por el pin de `psr7`. Verificado en Packagist:

| Versión de `google/apiclient` | `firebase/php-jwt` | `guzzlehttp/psr7` | `php` |
|---|---|---|---|
| v2.18.4 | `^6.0` | `^2.6` | `^8.1` |
| **v2.19.0 – v2.19.4** | **`^6.0\|\|^7.0`** | `^2.6` | `^8.1` |

**Cadena causal real:** el lock tiene `guzzlehttp/psr7: 2.5.0` → `composer require` es una
actualización *parcial* y no lo sube → sin `psr7 ^2.6` no se puede llegar a apiclient v2.19 →
Composer cae a v2.18, que solo admite `php-jwt ^6.0` → **ahí** salta el advisory. El mensaje de
seguridad es el último eslabón, no el primero.

**Solución — sin ignorar ningún advisory:**
```bash
./vendor/bin/sail composer require spatie/laravel-google-calendar -W
```
`-W` permite subir `psr7` → Composer elige apiclient v2.19.4 → resuelve `php-jwt ^7.0` → sin
advisory. **No** usar `policy.advisories.ignore-id` ni `policy.advisories.block: false`.

**Los dos advisories, para constancia:**
- **PKSA-2kqm-ps5x-s4f5** = CVE-2021-46743: confusión de algoritmos (RS256/HS256) vía cabecera
  `kid`. Real. Afecta a `< 6.0.0`, corregido en 6.0.
- **PKSA-y2cr-5h3j-g3ys** = CVE-2025-45769: claves HMAC cortas aceptadas sin validar. Afecta a todo
  el 6.x, corregido en **7.0**. **Disputado por MITRE/NVD** (17/08/2025): argumentan que la longitud
  de clave la fija la aplicación, no la librería, y NVD no le asignó CVSS. GitHub lo mantiene como
  High (CISA-ADP 7.3), lo que está bloqueando a medio ecosistema PHP (Drupal, MediaWiki, Salesforce,
  PayPal). Irrelevante para nosotros: acabamos en v7.

**Precaución:** `-W` puede mover otros paquetes del lock. Revisar el diff de `composer.lock` y correr
la suite antes de commitear.

Fuentes: [Packagist google/apiclient](https://repo.packagist.org/p2/google/apiclient.json) ·
[php-jwt issue #620 (disputa del CVE)](https://github.com/firebase/php-jwt/issues/620) ·
[CVE-2021-46743](https://github.com/advisories/GHSA-8xf4-w7qw-pjjw) ·
[GitLab Advisory DB](https://advisories.gitlab.com/pkg/composer/firebase/php-jwt/)

---

## #9 — El buffer de 4 horas: la lógica compartida que asume que toda cita lleva coche (17/07/2026)

**Contexto:** pregunta de Argenis — *"¿no debería ser otra tabla? ¿no interfiere con el flujo
actual?"*. Al verificar por qué la agenda debe ser compartida, apareció algo que **ningún documento
de este SSD había leído**: `AvailabilityController` no solo comprueba solapamientos, aplica un
**buffer de desplazamiento**.

**Hallazgo (leído del repo):**
```php
// AvailabilityController:163 — la disponibilidad se calcula SOBRE la tabla appointments
$appointments = Appointment::where('start_time', '>=', $dayStart) ...

// AvailabilityController:169
$bufferMinutes = 240;   // 4 horas ANTES y DESPUÉS de cada cita
```

Ese buffer existe por una razón física: **el técnico tiene que conducir** hasta casa del cliente.

**Doble implicación, y las dos importan:**

a) **Confirma que la agenda debe ser compartida.** La disponibilidad se calcula consultando
   `appointments` (línea 163). Una tabla separada para lo remoto **sería invisible** para esta
   consulta: el sistema ofrecería como libre un hueco con videollamada y aceptaría una presencial
   encima. **FR-7 roto en silencio**, sin error en ningún log. Lo mismo con el calendario de admin
   (FR-10) y los recordatorios (FR-9): todos leen `appointments`.

b) **Pero heredar el buffer tal cual destruiría el objetivo de negocio del módulo.** Una videollamada
   de 30 min bloquearía `4h + 0,5h + 4h = 8,5 horas` — el día entero — por una sesión en la que nadie
   se mueve de su sitio. Y **spec §2 dice literalmente que el módulo existe para monetizar *"el
   tiempo muerto entre desplazamientos"***: con el buffer heredado, cada asistencia remota
   **consumiría** ese tiempo muerto en vez de aprovecharlo. El módulo trabajaría en contra de su
   propia razón de existir.

**La lección se repite por tercera vez en este SSD:** research #1 leyó el constraint en vez del
runtime; R-5 propuso UTC sin mirar `config/app.php`; y aquí el plan reutilizó "la comprobación de
solapamiento existente" (T015) sin leer que esa función hace algo más que comprobar solapamientos.
**Reutilizar código exige leerlo, no solo nombrarlo.**

**Implicación para el plan:** el buffer pasa a depender del **par de modalidades**, no de una
constante:

| Cita existente → cita nueva | Buffer | Por qué |
|---|---|---|
| presencial → presencial | **240 min** (sin cambio) | Hay que conducir. Comportamiento actual intacto. |
| remota → remota | **0 min** | Nadie se mueve; dos llamadas seguidas son perfectamente posibles. |
| mixto (presencial ↔ remota) | **60 min** (configurable) | El técnico necesita llegar a un sitio con conexión. |

**Decisión (Argenis, 17/07/2026):** remota↔remota sin buffer, mixto reducido y configurable
(`config/remote_assistance.php` → `buffer.mixed_minutes`, default 60, a ajustar con la práctica).
El caso presencial↔presencial **no se toca**: es el flujo que hoy funciona.

---

## Resumen de decisiones que salen de esta investigación

| Decisión | Basada en | Resultado |
|---|---|---|
| Sin API de SumUp; verificación manual | #2 + decisión usuario | FR-3 es el único control de pago real |
| Prohibido pedir datos de tarjeta | #2 | Mantiene a Servispin fuera de PCI-DSS |
| Enlace de reunión tras una interfaz | #3, #4, #7 | Permite fallback si Google falla |
| **Google Meet vía OAuth como opción por defecto** | **#6a-b** | **Automático, sin necesidad de Workspace** |
| **Perfil OAuth único, sin Socialite** | **#6c-d** | **Un solo técnico ⇒ un solo calendario** |
| Enlace manual como *fallback*, no por defecto | #7 | Una cita pagada nunca se pierde |
| **Publicar la app a "In Production"** | **#7b-c** | **Sin esto, se rompe a los 7 días** |
| JaaS descartado por ahora | #4, #6b | Innecesario: OAuth ya resuelve Gmail |
| No subir Laravel/PHP en este módulo | #1 | Riesgo registrado, alcance aislado |
| Añadir `services.active` | #5 | Desbloquea el listado de servicios |
| **Agenda compartida en `appointments`** | **#9a** | **Una tabla aparte rompería FR-7 en silencio** |
| **Buffer según par de modalidades** | **#9b** | **Heredar las 4h anularía el objetivo del módulo** |

> **Nota de revisión (17/07/2026):** los hallazgos #6 y #7 nacen de una corrección de Argenis sobre
> el borrador inicial. El plan original usaba enlace manual por defecto por asumir que la
> automatización dependía de Workspace (#3). La vía OAuth desmonta esa premisa. Se conserva #3 sin
> editar: la restricción de la service account sigue siendo real y es la razón de no usarla.
