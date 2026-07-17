{{--
    Email de "solicitud recibida" (US-1).

    ⚠️ FR-3: esta vista NO PUEDE contener $appointment->meeting_url, ni el enlace
    de la videollamada bajo ninguna forma. La verificación manual del pago es el
    único control que existe (research #2): si el enlace saliera de aquí,
    bastaría con inventarse una referencia de pago para colarse en una llamada.
    Hay un test que falla si este email llega a contener un enlace de reunión.
--}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isForCompany ? 'Pago por verificar' : 'Solicitud recibida' }}</title>
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
            background-color: #3490dc;
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

        .notice {
            background-color: #fff3cd;
            border-left: 4px solid #f0ad4e;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .status-tag {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f0ad4e;
            color: white;
        }

        .timezone {
            font-size: 0.9em;
            color: #6c757d;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9em;
            color: #6c757d;
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
        // La cita se guarda en el huso del negocio; aquí se muestra también en el
        // huso del cliente. Nunca una hora sin decir de qué huso es (FR-6, R-5).
        $businessTz = config('remote_assistance.business_timezone', 'Atlantic/Canary');
        $clientTz = $appointment->client_timezone ?: $businessTz;
        $startBusiness = $appointment->start_time->copy();
        $startClient = $appointment->start_time->copy()->setTimezone($clientTz);
        $sameTz = $clientTz === $businessTz;
    @endphp

    <div class="header">
        <h1>{{ $isForCompany ? 'Pago por verificar' : 'Solicitud recibida' }}</h1>
    </div>

    <div class="content">
        @if ($isForCompany)
            <p>Hay una nueva solicitud de <strong>asistencia remota</strong> con un pago declarado
               pendiente de cotejar en SumUp.</p>

            <div class="appointment-details">
                <p><strong>Cliente:</strong> {{ $appointment->client_first_name }} {{ $appointment->client_last_name }}</p>
                <p><strong>Email:</strong> {{ $appointment->client_email }}</p>
                <p><strong>Teléfono:</strong> {{ $appointment->client_phone }}</p>
                <p><strong>Referencia del pago:</strong> {{ $appointment->payment_reference }}</p>
                <p><strong>Importe declarado:</strong> {{ number_format($appointment->payment_amount, 2) }} {{ $appointment->payment_currency }}</p>
                <p><strong>Pagador:</strong> {{ $appointment->payer_name }}</p>
                <p><strong>Fecha y hora:</strong> {{ $startBusiness->format('d/m/Y H:i') }}
                    <span class="timezone">({{ $businessTz }})</span>
                </p>
                @unless ($sameTz)
                    <p><strong>Hora del cliente:</strong> {{ $startClient->format('d/m/Y H:i') }}
                        <span class="timezone">({{ $clientTz }})</span>
                    </p>
                @endunless
                @if ($appointment->brand)
                    <p><strong>Marca:</strong> {{ $appointment->brand->name }}</p>
                @endif
                <p><strong>Avería:</strong> {{ $appointment->issue_description }}</p>
            </div>

            <div class="notice">
                <strong>El cliente todavía NO tiene el enlace.</strong> Comprueba en SumUp que el cobro
                entró y confirma la cita desde el panel: solo entonces se le envía el enlace de la
                videollamada.
            </div>
        @else
            <p>Hola {{ $appointment->client_first_name }},</p>

            <p>Hemos recibido tu solicitud de asistencia técnica remota y los datos de tu pago.
               Vamos a comprobar que el cobro ha entrado correctamente.</p>

            <div class="appointment-details">
                <p><strong>Estado:</strong> <span class="status-tag">Pendiente de verificar</span></p>
                <p><strong>Servicio:</strong> {{ $appointment->service->name }}</p>
                <p><strong>Duración:</strong> {{ $appointment->service->duration }} minutos</p>
                <p><strong>Tu cita sería el:</strong> {{ $startClient->format('d/m/Y') }} a las
                    {{ $startClient->format('H:i') }}
                    <span class="timezone">(hora de {{ $clientTz }})</span>
                </p>
                @unless ($sameTz)
                    <p class="timezone">Equivale a las {{ $startBusiness->format('H:i') }} en {{ $businessTz }},
                       que es nuestro horario local.</p>
                @endunless
                <p><strong>Referencia de pago que nos diste:</strong> {{ $appointment->payment_reference }}</p>
                <p><strong>Importe declarado:</strong> {{ number_format($appointment->payment_amount, 2) }} {{ $appointment->payment_currency }}</p>
            </div>

            <div class="notice">
                <strong>Tu cita todavía no es firme.</strong> En cuanto comprobemos el pago te enviaremos
                un segundo email con el enlace de la videollamada. Ese email es el que confirma tu cita:
                hasta que llegue, el hueco no está garantizado.
            </div>

            <p>Si no recibes esa confirmación en 24 horas, escríbenos respondiendo a este email y lo
               revisamos.</p>
        @endif

        <div class="company-info">
            <p><strong>{{ $companyData->company_name }}</strong></p>
            @if ($companyData->phone)
                <p>Teléfono: {{ $companyData->phone }}</p>
            @endif
            @if ($companyData->website)
                <p>Web: <a href="{{ $companyData->website }}">{{ $companyData->website }}</a></p>
            @endif
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, pero puedes responderlo si necesitas algo.</p>
        </div>
    </div>
</body>

</html>
