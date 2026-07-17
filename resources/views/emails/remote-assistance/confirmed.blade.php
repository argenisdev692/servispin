{{--
    Confirmación con enlace (US-2). El único email del módulo que lo lleva, y
    solo llega aquí después de que un humano cotejara el pago en SumUp (FR-3).
--}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita confirmada</title>
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
            background-color: #16a34a;
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

        .no-link {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            border-radius: 4px;
        }

        .company-info {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    @php
        $businessTz = config('remote_assistance.business_timezone', 'Atlantic/Canary');
        $clientTz = $appointment->client_timezone ?: $businessTz;
        $startBusiness = $appointment->start_time->copy();
        $startClient = $appointment->start_time->copy()->setTimezone($clientTz);
        $sameTz = $clientTz === $businessTz;
    @endphp

    <div class="header">
        <h1>{{ $isForTechnician ? 'Videollamada confirmada' : '¡Tu cita está confirmada!' }}</h1>
    </div>

    <div class="content">
        @if ($isForTechnician)
            <p>Pago verificado. Tienes una videollamada con
               <strong>{{ $appointment->client_first_name }} {{ $appointment->client_last_name }}</strong>.</p>
        @else
            <p>Hola {{ $appointment->client_first_name }},</p>
            <p>Hemos comprobado tu pago y tu cita queda confirmada. Aquí tienes todo lo que necesitas.</p>
        @endif

        <div class="appointment-details">
            <p><strong>Servicio:</strong> {{ $appointment->service->name }}</p>
            <p><strong>Duración:</strong> {{ $appointment->service->duration }} minutos</p>

            {{-- FR-6 / R-5: nunca una hora sin decir de qué huso es. Un cliente
                 que se pierde la cita que ya pagó es el fallo más caro del módulo. --}}
            @if ($isForTechnician)
                <p><strong>Cuándo:</strong> {{ $startBusiness->format('d/m/Y H:i') }}
                    <span class="timezone">({{ $businessTz }})</span>
                </p>
                @unless ($sameTz)
                    <p class="timezone">Para el cliente son las {{ $startClient->format('H:i') }} en {{ $clientTz }}.</p>
                @endunless
                <p><strong>Email del cliente:</strong> {{ $appointment->client_email }}</p>
                <p><strong>Teléfono:</strong> {{ $appointment->client_phone }}</p>
                @if ($appointment->brand)
                    <p><strong>Marca:</strong> {{ $appointment->brand->name }}</p>
                @endif
                <p><strong>Avería:</strong> {{ $appointment->issue_description }}</p>
                @if ($appointment->equipment_photo_url)
                    <p><strong>Foto:</strong> <a href="{{ $appointment->equipment_photo_url }}">ver imagen</a></p>
                @endif
                <p><strong>Referencia del pago:</strong> {{ $appointment->payment_reference }}</p>
            @else
                <p><strong>Cuándo:</strong> {{ $startClient->format('d/m/Y') }} a las
                    <strong>{{ $startClient->format('H:i') }}</strong>
                    <span class="timezone">(hora de {{ $clientTz }})</span>
                </p>
                @unless ($sameTz)
                    <p class="timezone">Equivale a las {{ $startBusiness->format('H:i') }} en {{ $businessTz }}, nuestro horario local.</p>
                @endunless
            @endif
        </div>

        @if ($appointment->meeting_url)
            <div class="join-wrapper">
                <a href="{{ $appointment->meeting_url }}" class="join-button">Entrar a la videollamada</a>
            </div>
            <p style="text-align:center; font-size:0.85em; color:#6c757d;">
                Si el botón no funciona, copia este enlace:<br>
                <span style="word-break:break-all;">{{ $appointment->meeting_url }}</span>
            </p>
            <p style="text-align:center; font-size:0.85em; color:#6c757d;">
                Se abre en el navegador del móvil. No necesitas instalar nada.
            </p>
        @elseif ($isForTechnician)
            {{-- FR-15 / T035: al técnico se le grita, porque es quien tiene que
                 arreglarlo. La cita está pagada y confirmada, pero sin enlace. --}}
            <div class="no-link">
                <strong>⚠️ Esta cita NO tiene enlace: genéralo a mano.</strong>
                La generación automática de Google Meet falló. La cita está pagada y confirmada, así
                que <strong>no se puede perder</strong>: crea un enlace de videollamada, añádelo a la
                cita desde el panel y reenvía la confirmación al cliente antes de la hora.
            </div>
        @else
            {{-- FR-15: al cliente se le tranquiliza. Su cita está firme; el enlace llega aparte. --}}
            <div class="no-link">
                <strong>Te enviaremos el enlace en un momento.</strong>
                Tu cita y tu pago están confirmados; solo nos falta generar el enlace de la
                videollamada y te llegará en un email aparte antes de la cita.
            </div>
        @endif

        @unless ($isForTechnician)
            <div class="safety">
                <strong>Antes de la llamada:</strong> busca un sitio con buena cobertura y, si vas conduciendo,
                detén el coche en un lugar seguro. Ten el aparato a mano y, si puedes, alguien que sujete el móvil.
            </div>
        @endunless

        <div class="company-info">
            <p><strong>{{ $companyData->company_name }}</strong></p>
            @if ($companyData->phone)
                <p>Teléfono: {{ $companyData->phone }}</p>
            @endif
        </div>
    </div>
</body>

</html>
