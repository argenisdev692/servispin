<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proveedor del enlace de videollamada
    |--------------------------------------------------------------------------
    |
    | Implementación de App\Services\MeetingLink\MeetingLinkProvider que se usa
    | al confirmar una cita remota. Valores: 'manual' | 'google_meet'.
    |
    | El default del fichero es 'manual' a propósito: un clon del repo sin
    | credenciales de Google tiene que funcionar. Producción pone 'google_meet'
    | vía .env. Si el provider automático falla, la cita se confirma igualmente
    | sin enlace y Cesar lo pega a mano (FR-15) — no se pierde una cita pagada.
    |
    */
    'meeting_provider' => env('REMOTE_ASSISTANCE_MEETING_PROVIDER', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Valores por defecto del servicio remoto
    |--------------------------------------------------------------------------
    |
    | Provisionales (D-3 se cerró con "valores editables"). La fuente de verdad
    | es el registro Service con is_remote = true, que Cesar edita desde el CRUD
    | de servicios. Esto solo alimenta el seeder.
    |
    */
    'default_price' => env('REMOTE_ASSISTANCE_PRICE', 35.00),
    'default_duration' => env('REMOTE_ASSISTANCE_DURATION', 20), // minutos

    /*
    |--------------------------------------------------------------------------
    | Liberación del hueco sin verificar (FR-12)
    |--------------------------------------------------------------------------
    |
    | D-4 sigue abierta. Estos son los valores de la propuesta del spec: se
    | libera a las 24 h de declarar el pago, o 2 h antes de la cita, lo que
    | ocurra antes. No se consumen hasta la fase G (T043).
    |
    */
    'hold_hours' => env('REMOTE_ASSISTANCE_HOLD_HOURS', 24),
    'hold_cutoff_hours_before' => env('REMOTE_ASSISTANCE_HOLD_CUTOFF_HOURS', 2),

    /*
    |--------------------------------------------------------------------------
    | QR de cobro de SumUp
    |--------------------------------------------------------------------------
    |
    | URL de la imagen del QR estático que se muestra en el formulario. El cobro
    | lo gestiona SumUp de principio a fin: aquí no se captura ningún dato de
    | tarjeta, y esa es la única razón por la que Servispin está fuera del
    | alcance de PCI-DSS (FR-4, research #2).
    |
    */
    'sumup_qr_url' => env(
        'REMOTE_ASSISTANCE_SUMUP_QR_URL',
        'https://docs.google.com/viewerng/viewer?url=https://api.sumup.com/v1/mobile-payments/tokens/QHTT4CB0/pdf'
    ),

    // El banner de la landing se sirve directamente desde
    // public/files/images/asistencia-online.webp (ver remote-assistance/landing.blade.php).

    /*
    |--------------------------------------------------------------------------
    | Buffer de desplazamiento según modalidad (research #9b, plan §4b)
    |--------------------------------------------------------------------------
    |
    | AvailabilityController aplica 240 min de buffer alrededor de cada cita
    | porque el técnico tiene que conducir. Para una videollamada eso no aplica:
    | heredarlo haría que una sesión de 30 min bloquease 8,5 h de agenda, justo
    | el tiempo muerto entre desplazamientos que este módulo quiere monetizar
    | (spec §2). El buffer depende del par de modalidades:
    |
    |   presencial → presencial : 240 min  (en AvailabilityController, sin tocar)
    |   remota     → remota     : 0 min    (nadie se mueve)
    |   mixto                   : el valor de aquí abajo
    |
    | 'mixed_minutes' es el margen para que el técnico llegue a un sitio con
    | conexión entre una visita presencial y una videollamada. 60 min es un punto
    | de partida: ajústalo con la práctica.
    |
    */
    'buffer' => [
        'remote_to_remote_minutes' => env('REMOTE_ASSISTANCE_BUFFER_REMOTE', 0),
        'mixed_minutes' => env('REMOTE_ASSISTANCE_BUFFER_MIXED', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Huso horario de referencia del negocio
    |--------------------------------------------------------------------------
    |
    | Las citas se persisten en el huso de la aplicación (config/app.php →
    | Atlantic/Canary), igual que las presenciales. NO se guarda en UTC: mezclar
    | convenciones en la columna start_time rompería la detección de solapamiento
    | (plan §9 R-5). Esto es solo para mostrar el huso explícito al cliente.
    |
    */
    'business_timezone' => env('REMOTE_ASSISTANCE_BUSINESS_TZ', 'Atlantic/Canary'),

];
