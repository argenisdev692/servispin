<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Su cita ha sido cancelada</title>
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
            background-color: #ef4444;
            /* Rojo para cancelación */
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

        .status-tag {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #ef4444;
            /* Rojo para cancelación */
            color: white;
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

        .rebook-button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #3490dc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
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
        <h1>Su cita ha sido cancelada</h1>
    </div>

    <div class="content">
        <p>Hola <strong>{{ $appointment->client_first_name }} {{ $appointment->client_last_name }}</strong>,</p>

        <p>Lamentamos informarle que su cita con <strong>{{ $companyData->company_name }}</strong> ha sido cancelada.
        </p>

        <div class="appointment-details">
            <h3>Detalles de la Cita Cancelada:</h3>
            @if ($appointment->service)
                <p><strong>Servicio:</strong> {{ $appointment->service->name }}</p>
            @endif
            <p><strong>Estado:</strong> <span class="status-tag">Cancelada</span></p>
            <p><strong>Fecha y hora:</strong>
                {{ \Carbon\Carbon::parse($appointment->start_time)->formatLocalized('%A %d de %B de %Y a las %H:%M') }}
            </p>
            <p><strong>Dirección:</strong> {{ $appointment->address }}</p>
        </div>

        <p>Si desea reprogramar su cita o tiene alguna pregunta, por favor contáctenos a la brevedad posible.</p>

        @if ($companyData->website)
            <p>
                <a href="{{ $companyData->website }}/appointments/book" style="color: white;"
                    class="rebook-button">Agendar Nueva Cita</a>
            </p>
        @endif

        <p>Contactos para asistencia:</p>

        @if ($companyData->phone)
            <p><strong>Teléfono:</strong> {{ formatSpanishPhone($companyData->phone) }}</p>
        @endif
        @if ($companyData->email)
            <p><strong>Email:</strong> {{ $companyData->email }}</p>
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
