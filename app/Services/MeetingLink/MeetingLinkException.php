<?php

namespace App\Services\MeetingLink;

use RuntimeException;

/**
 * Un proveedor automático no pudo generar el enlace.
 *
 * Esta excepción NO debe abortar la confirmación de una cita: el dinero ya
 * entró. Quien la captura registra meeting_link_failed_at, confirma igual y
 * avisa a Cesar para que pegue el enlace a mano (FR-15, plan §3).
 */
class MeetingLinkException extends RuntimeException {}
