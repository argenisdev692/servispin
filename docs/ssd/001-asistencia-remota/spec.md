# Specification: Asistencia Técnica Remota

> Phase 1 · SPECIFY — Defines WHAT is built and WHY. No technical stack here.

**Feature ID:** 001-asistencia-remota
**Date:** 2026-07-16
**Status:** Draft — pendiente de revisión por Argenis

## 1. Summary

Módulo que permite a un cliente de cualquier parte del mundo contratar una sesión de asistencia
técnica por videollamada para reparar su electrodoméstico. El cliente paga por adelantado mediante
el QR de SumUp que Servispin ya tiene, declara el pago en un formulario, y una vez Servispin
verifica que el cobro entró, se le envía la cita con el enlace de la videollamada.

Resuelve dos problemas: monetiza el conocimiento técnico de Servispin sin desplazamiento, y abre
el negocio a un mercado que hoy no puede atender (fuera de Las Palmas).

## 2. Motivation / Business context

Hoy Servispin solo factura si el técnico se desplaza, lo que limita los ingresos al radio
geográfico de Gran Canaria y a las horas de coche. Existe demanda observada de gente que se inicia
en reparación y de particulares con averías simples que se resolverían guiando por vídeo.

Si no se hace: se sigue rechazando (o no captando) a todo cliente fuera de la isla, y el tiempo
muerto entre desplazamientos sigue sin monetizarse.

## 3. Actors

- **Cliente remoto**: particular o aficionado con una avería. Puede estar en cualquier huso horario.
  No tiene cuenta en el sistema; interactúa una sola vez de forma anónima.
- **Cesar (administrador)**: verifica que el pago entró en SumUp, confirma la cita y atiende la
  videollamada. Es el único que puede convertir una solicitud en cita confirmada.
- **Técnico**: quien atiende la llamada. En la práctica hoy coincide con Cesar; el diseño no debe
  asumir que siempre será la misma persona.

## 4. User stories

### US-1: Solicitar asistencia remota (Priority: Alta)
**Como** cliente remoto, **quiero** describir mi avería y reservar un hueco tras pagar por QR,
**para** que un técnico me guíe por videollamada.

**Acceptance criteria:**
- [ ] Dado que estoy en la página de asistencia remota, cuando elijo un hueco libre y envío el
      formulario con descripción, marca y referencia de pago, entonces se registra la solicitud
      en estado "pago declarado" y recibo un email de "solicitud recibida".
- [ ] Dado que envío el formulario, cuando no incluyo referencia de pago, entonces se rechaza
      indicando que el pago es obligatorio.
- [ ] Dado que estoy en otro huso horario, cuando veo los huecos disponibles, entonces se
      muestran en mi hora local y se indica explícitamente el huso.
- [ ] Dado que el email de "solicitud recibida" llega, entonces NO contiene enlace de
      videollamada y advierte que la cita no es firme hasta verificar el pago.

### US-2: Verificar el pago y confirmar la cita (Priority: Alta)
**Como** Cesar, **quiero** ver las solicitudes con su referencia de pago y confirmarlas de una
en una, **para** que solo quien pagó de verdad reciba la videollamada.

**Acceptance criteria:**
- [ ] Dado que hay una solicitud con pago declarado, cuando la abro, entonces veo referencia,
      importe declarado, fecha/hora y datos de la avería para poder cotejarlo con la app SumUp.
- [ ] Dado que coteje el pago y sea correcto, cuando pulso "Confirmar", entonces la cita pasa a
      Confirmada, se le asocia el enlace de videollamada y el cliente recibe email con el enlace.
- [ ] Dado que el pago no aparezca en SumUp, cuando pulso "Rechazar", entonces la cita se cancela,
      el hueco se libera y el cliente recibe email explicando el motivo.
- [ ] Dado que confirmo una cita, entonces queda registrado quién la verificó y cuándo.

### US-3: Recibir recordatorios (Priority: Media)
**Como** cliente remoto, **quiero** que me recuerden la cita, **para** no perder lo que he pagado.

**Acceptance criteria:**
- [ ] Dado que tengo cita confirmada para mañana, cuando son las 09:00 hora de Servispin,
      entonces recibo un recordatorio con el enlace.
- [ ] Dado que mi cita empieza en 30 minutos, entonces recibo un segundo recordatorio con el enlace.
- [ ] Dado que un recordatorio se envía, entonces el técnico también lo recibe.
- [ ] Dado que la cita fue cancelada, entonces no se envía ningún recordatorio.

### US-4: Descubrir el servicio (Priority: Media)
**Como** visitante de la web, **quiero** entender en 5 segundos que puedo arreglar mi lavadora por
videollamada, **para** decidir si me interesa.

**Acceptance criteria:**
- [ ] Dado que entro en la landing, entonces veo el anuncio (GIF), el precio y la duración de la
      sesión antes de tener que rellenar nada.
- [ ] Dado que el anuncio carga, entonces no degrada el rendimiento de la página de inicio actual.

### US-5: Cancelar una cita remota (Priority: Media)
**Como** Cesar, **quiero** cancelar una cita remota, **para** gestionar imprevistos.

**Acceptance criteria:**
- [ ] Dado que cancelo una cita ya pagada, entonces el sistema registra que hay un reembolso
      pendiente y notifica al cliente.
- [ ] Dado que se cancela, entonces el hueco vuelve a estar disponible.

### US-6: Crear una cita remota desde el calendario de administración (Priority: Alta)
**Como** Cesar, **quiero** dar de alta una cita remota yo mismo desde el calendario, **para** poder
atender al cliente que me llama por teléfono y paga por QR sin pasar por la web.

**Acceptance criteria:**
- [ ] Dado que estoy en el calendario de administración, cuando pulso sobre un hueco libre,
      entonces puedo crear una cita remota indicando si el cliente ya pagó.
- [ ] Dado que el cliente no existe en el sistema, cuando creo la cita, entonces puedo escribir sus
      datos a mano sin tener que seleccionarlo de una lista.
- [ ] Dado que marco la cita como ya pagada y verificada por mí, cuando la guardo, entonces se crea
      confirmada, se genera el enlace y se envía el email al cliente en un solo paso.
- [ ] Dado que creo la cita sin marcarla como pagada, entonces queda pendiente de verificación y NO
      se envía enlace (FR-3 aplica igual que en el formulario público).
- [ ] Dado que guardo la cita, entonces queda registrado que fui yo quien verificó el pago (FR-5).
- [ ] Dado que elijo un hueco ocupado, entonces el sistema lo impide (FR-7).

## 5. Functional requirements

- **FR-1**: El sistema DEBE permitir crear una solicitud de asistencia remota sin cuenta de usuario.
- **FR-2**: El sistema DEBE exigir una referencia de pago y un importe declarado para aceptar la
  solicitud.
- **FR-3**: El sistema NO DEBE enviar el enlace de la videollamada hasta que un administrador
  verifique el pago manualmente.
- **FR-4**: El sistema NO DEBE solicitar, almacenar ni transmitir datos de tarjeta (PAN, CVV,
  caducidad). Solo referencia del recibo, importe y nombre del pagador.
- **FR-5**: El sistema DEBE registrar qué administrador verificó cada pago y en qué momento.
- **FR-6**: El sistema DEBE mostrar y almacenar la franja horaria de la cita de forma no ambigua
  respecto al huso horario del cliente.
- **FR-7**: El sistema DEBE impedir que dos citas (remotas o presenciales) ocupen el mismo hueco.
- **FR-8**: El sistema DEBE asociar a cada cita remota confirmada un enlace de videollamada único.
- **FR-9**: El sistema DEBE enviar recordatorios 24 horas antes y 30 minutos antes de cada cita
  remota confirmada.
- **FR-10**: El sistema DEBE mostrar las citas remotas en el calendario de administración
  existente, distinguibles de las presenciales.
- **FR-11**: El sistema NO DEBE exigir dirección postal en una solicitud remota.
- **FR-12**: El sistema DEBE tratar como no pagada cualquier solicitud cuya referencia no haya
  sido verificada, y liberar el hueco si no se verifica en un plazo determinado.
- **FR-13**: El sistema DEBE permitir a un administrador crear una cita remota directamente,
  introduciendo los datos del cliente a mano, sin pasar por el formulario público.
- **FR-14**: El sistema DEBE registrar la cita remota en el calendario de Google de Servispin.
- **FR-15**: Si la generación automática del enlace falla, el sistema NO DEBE perder la cita: debe
  confirmarla igualmente y permitir añadir el enlace manualmente.

## 6. Non-functional requirements

- **Rendimiento**: el envío del formulario debe responder en < 2 s p95. El envío de emails no debe
  bloquear la respuesta al cliente.
- **Seguridad**: sin datos de tarjeta (ver FR-4), por tanto fuera del alcance de PCI-DSS. El
  formulario es público y anónimo, por lo que necesita rate limiting y protección anti-spam. Los
  enlaces de videollamada no deben ser adivinables ni indexables.
- **Privacidad (RGPD)**: se recogen nombre, email, teléfono, descripción de avería y opcionalmente
  fotos; aplica la política existente. Base legal: ejecución de contrato. Cliente fuera de la UE:
  no cambia el tratamiento.
- **Disponibilidad**: si falla la generación del enlace, la cita NO debe perderse; debe poder
  completarse a mano.
- **Escalabilidad**: volumen esperado bajo (unidades a decenas de citas/mes en 6-12 meses). No
  justifica infraestructura nueva.
- **Usabilidad móvil**: el cliente y el técnico entrarán desde el móvil; el enlace debe abrirse sin
  instalar nada.

## 7. Data entities (conceptual)

- **Solicitud de asistencia remota**: es una Cita (entidad existente) con modalidad "remota". Añade
  declaración de pago y enlace de reunión. No tiene dirección postal.
- **Declaración de pago**: referencia del recibo SumUp, importe declarado, moneda, momento de la
  declaración, estado (declarado / verificado / rechazado), quién y cuándo lo verificó.
- **Enlace de reunión**: URL, proveedor que lo generó, y si fue introducido a mano o automáticamente.
- **Servicio** (existente): gana el atributo de si es prestable en remoto, con su precio y duración.

## 8. Out of scope

- Cobro automático y conciliación vía API de SumUp (webhooks). Se hace verificación manual.
- Reembolsos automáticos. Cesar los tramita en SumUp a mano.
- Grabación de las videollamadas.
- Notificaciones por WhatsApp (el doc las menciona; hoy no hay integración de WhatsApp en el
  proyecto y añadirla es un módulo aparte).
- Sala de espera, chat, o compartir pantalla propios: se delega en el proveedor de vídeo.
- Cuentas de cliente / histórico de sesiones del cliente.
- Multi-idioma de la landing.

## 9. Assumptions and open decisions

- **Asunción**: hay un único técnico atendiendo, por lo que el solapamiento de citas se comprueba
  globalmente (como ya hace el código actual), sin agenda por técnico.
- **Asunción**: el QR de SumUp es estático y el cliente introduce el importe; por eso se pide
  importe declarado además de referencia.
- **Asunción**: el precio de la sesión remota es fijo y se define como un Servicio.
- **[NEEDS CLARIFICATION]** ¿Qué campos exactos se piden como "datos del pago"? La propuesta es
  referencia/ID del recibo + importe + nombre del pagador. **Bajo ninguna circunstancia el
  número de tarjeta** — eso metería a Servispin en PCI-DSS.
- **[NEEDS CLARIFICATION]** ¿Cuánto tiempo se reserva el hueco antes de liberarlo si Cesar no
  verifica el pago? (propuesta: 24 h, o hasta 2 h antes de la cita, lo que ocurra antes).
- **[NEEDS CLARIFICATION]** ¿Cuál es la política si el cliente no aparece a la llamada? ¿Se
  reembolsa, se reprograma una vez, se pierde?
- **[NEEDS CLARIFICATION]** ¿Precio y duración de la sesión? (el doc no lo dice).
- ~~**[NEEDS CLARIFICATION]** Tipo de cuenta Google de Servispin (Workspace vs Gmail gratuito).~~
  **RESUELTO (17/07/2026):** deja de importar. Autenticando por OAuth como usuario (en vez de con
  service account), un Gmail gratuito genera enlaces de Meet igual. Ver research.md #6b.
- **[NEEDS CLARIFICATION]** ¿Quién es el titular de la cuenta de Google que se conectará, y quién
  custodia sus credenciales? Los eventos y los Meet nacerán en ese calendario. Ver research.md #6d.

## 10. Success criteria (measurable)

- Una solicitud pagada se convierte en cita confirmada con enlace en < 24 h desde su envío.
- 0 videollamadas atendidas sin pago verificado.
- 0 citas remotas perdidas por fallo en la generación del enlace.
- Cesar dedica < 1 minuto por cita a la verificación del pago.
