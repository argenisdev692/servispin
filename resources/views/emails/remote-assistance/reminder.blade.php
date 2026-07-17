{{--
    Recordatorio de cita remota (US-3). 24 h antes o 30 min antes.
    Lleva SIEMPRE el enlace y el huso explícito: sin enlace no sirve, y con la
    hora en el huso equivocado el cliente se pierde la cita que ya pagó (R-5).
--}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de tu videollamada</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }

        .appointment-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .join-button {
            display: inline-block;
            background-color: #16a34a;
            color: #ffffff !important;
            padding: 14px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
        }

        .join-wrapper {
            text-align: center;
            margin: 28px 0;
        }

        .safety {
            background-color: #fff3cd;
            border-left: 4px solid #f0ad4e;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .timezone {
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>

<body>
    @php
        $isImminent = $when === \App\Mail\RemoteAssistanceReminder::WHEN_IMMINENT;
        $businessTz = config('remote_assistance.business_timezone', 'Atlantic/Canary');
        $clientTz = $appointment->client_timezone ?: $businessTz;
        $startClient = $appointment->start_time->copy()->setTimezone($clientTz);
        $startBusiness = $appointment->start_time->copy();
        $sameTz = $clientTz === $businessTz;
    @endphp

    <div class="header">
        <h1>{{ $isImminent ? 'Tu videollamada empieza pronto' : 'Recordatorio de tu videollamada' }}</h1>
    </div>

    <div class="content">
        @if ($isForTechnician)
            <p>Recordatorio: videollamada con
               <strong>{{ $appointment->client_first_name }} {{ $appointment->client_last_name }}</strong>
               {{ $isImminent ? 'en unos minutos' : 'mañana' }}.</p>
        @else
            <p>Hola {{ $appointment->client_first_name }},</p>
            <p>
                @if ($isImminent)
                    Tu sesión de asistencia remota empieza en unos minutos. Este es tu enlace:
                @else
                    Te recordamos que mañana tienes tu sesión de asistencia remota. Aquí tienes el enlace:
                @endif
            </p>
        @endif

        <div class="appointment-details">
            <p><strong>Cuándo:</strong> {{ $startClient->format('d/m/Y') }} a las
                <strong>{{ $startClient->format('H:i') }}</strong>
                <span class="timezone">(hora de {{ $clientTz }})</span>
            </p>
            @unless ($sameTz)
                <p class="timezone">Equivale a las {{ $startBusiness->format('H:i') }} en {{ $businessTz }}.</p>
            @endunless
            <p><strong>Servicio:</strong> {{ $appointment->service->name ?? 'Asistencia remota' }}
               ({{ $appointment->service->duration ?? '' }} min)</p>
        </div>

        @if ($appointment->meeting_url)
            <div class="join-wrapper">
                <a href="{{ $appointment->meeting_url }}" class="join-button">Entrar a la videollamada</a>
            </div>
            <p style="text-align:center; font-size:0.85em; color:#6c757d; word-break:break-all;">
                {{ $appointment->meeting_url }}
            </p>
        @else
            {{-- Sin enlace (fallo de generación, FR-15): al técnico se le avisa,
                 al cliente no se le alarma. --}}
            @if ($isForTechnician)
                <div class="safety"><strong>⚠️ Esta cita aún no tiene enlace: genéralo a mano ya.</strong></div>
            @endif
        @endif

        @unless ($isForTechnician)
            <div class="safety">
                Busca un sitio con buena cobertura. Si vas conduciendo, detén el coche en un lugar seguro
                antes de la llamada. Ten el aparato a mano.
            </div>
        @endunless
    </div>
</body>

</html>
