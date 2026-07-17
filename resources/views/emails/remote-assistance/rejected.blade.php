{{--
    Rechazo por pago no localizado (US-2). Sin enlace, con el motivo y con una
    salida clara: lo más probable no es un intento de colarse, sino una
    referencia mal copiada.
--}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No hemos podido confirmar tu cita</title>
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
            background-color: #6b7280;
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

        .reason {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .company-info {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>No hemos podido confirmar tu cita</h1>
    </div>

    <div class="content">
        <p>Hola {{ $appointment->client_first_name }},</p>

        <p>Hemos revisado los datos del pago que nos diste para tu sesión de asistencia remota,
           pero <strong>no hemos conseguido localizar el cobro</strong>. Por eso no podemos
           confirmar la cita todavía, y el hueco ha quedado libre.</p>

        <div class="reason">
            <p><strong>Referencia que nos diste:</strong> {{ $appointment->payment_reference }}</p>
            @if ($appointment->payment_amount)
                <p><strong>Importe declarado:</strong> {{ number_format($appointment->payment_amount, 2) }} {{ $appointment->payment_currency }}</p>
            @endif
            @if ($reason)
                <p><strong>Motivo:</strong> {{ $reason }}</p>
            @endif
        </div>

        <p>Lo más habitual es que la referencia se haya copiado con algún carácter de más o de menos.
           Si estás seguro de que el pago salió de tu cuenta, respóndenos a este email con el recibo
           que te envió SumUp y lo resolvemos enseguida.</p>

        <p>Si el pago no llegó a completarse, puedes volver a intentarlo cuando quieras: no se te ha
           cobrado nada.</p>

        <div class="company-info">
            <p><strong>{{ $companyData->company_name }}</strong></p>
            @if ($companyData->phone)
                <p>Teléfono: {{ $companyData->phone }}</p>
            @endif
        </div>
    </div>
</body>

</html>
