<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Cita</title>
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

        .social-links {
            margin-top: 15px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #3490dc;
            text-decoration: none;
        }

        .button {
            background-color: #3490dc;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    @php
        // Función para formatear números de teléfono españoles
        if (!function_exists('formatSpanishPhone')) {
            function formatSpanishPhone($phoneNumber)
            {
                // Eliminar cualquier espacio existente
                $cleaned = preg_replace('/\s+/', '', $phoneNumber);

                // Si el número comienza con +34, formatearlo correctamente
                if (str_starts_with($cleaned, '+34')) {
                    // Quitar el código de país para trabajar solo con los 9 dígitos
                    $nationalNumber = substr($cleaned, 3);

                    // Aplicar formato +34 XXX XX XX XX
                    if (strlen($nationalNumber) === 9) {
                        return '+34 ' .
                            substr($nationalNumber, 0, 3) .
                            ' ' .
                            substr($nationalNumber, 3, 2) .
                            ' ' .
                            substr($nationalNumber, 5, 2) .
                            ' ' .
                            substr($nationalNumber, 7, 2);
                    }
                }

                // Si no es un número español o no tiene el formato esperado, devolverlo sin cambios
                return $phoneNumber;
            }
        }
    @endphp

    <div class="header">
        <h1>Recordatorio de Cita</h1>
    </div>

    <div class="content">
        @if ($isForCompany)
            <p>Hola <strong>{{ $companyData->name }}</strong>,</p>
            <p>Este es un recordatorio de que tiene una cita programada para mañana con el cliente
                <strong>{{ $appointment->client_name }}</strong>.
            </p>
        @else
            <p>Hola <strong>{{ $appointment->client_name }}</strong>,</p>
            <p>Este es un recordatorio de su cita programada para mañana con
                <strong>{{ $companyData->company_name }}</strong>.
            </p>
        @endif

        <div class="appointment-details">
            <h3>Detalles de la Cita:</h3>
            <p><strong>Servicio:</strong> {{ $appointment->service }}</p>
            <p><strong>Fecha y hora:</strong>
                {{ \Carbon\Carbon::parse($appointment->start_time)->formatLocalized('%A %d de %B de %Y a las %H:%M') }}
            </p>
            <p><strong>Dirección:</strong> {{ $appointment->address }}</p>
            @if (!empty($appointment->issue))
                <p><strong>Problema/Asunto:</strong> {{ $appointment->issue }}</p>
            @endif
            @if (!empty($appointment->notes))
                <p><strong>Notas adicionales:</strong> {{ $appointment->notes }}</p>
            @endif
        </div>

        @if (!$isForCompany)
            <p>Si necesita cambiar o cancelar su cita, por favor contáctenos lo antes posible.</p>
            @if ($companyData->phone)
                <p><strong>Teléfono:</strong> {{ formatSpanishPhone($companyData->phone) }}</p>
            @endif
            @if ($companyData->email)
                <p><strong>Email:</strong> {{ $companyData->email }}</p>
            @endif
        @endif

        <div class="company-info">
            <p><strong>{{ $companyData->company_name }}</strong></p>
            @if ($companyData->address)
                <p>{{ $companyData->address }}</p>
            @endif

            <div class="social-links">
                @if ($companyData->social_media_facebook)
                    <a href="{{ $companyData->social_media_facebook }}" target="_blank">Facebook</a>
                @endif
                @if ($companyData->social_media_instagram)
                    <a href="{{ $companyData->social_media_instagram }}" target="_blank">Instagram</a>
                @endif
                @if ($companyData->social_media_twitter)
                    <a href="{{ $companyData->social_media_twitter }}" target="_blank">Twitter</a>
                @endif
            </div>

            @if ($companyData->website)
                <p><a href="{{ $companyData->website }}" target="_blank">{{ $companyData->website }}</a></p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} {{ $companyData->company_name }}. Todos los derechos reservados.</p>
    </div>
</body>

</html>
