<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cita Confirmada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #4a90e2;
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

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }

        .appointment-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Su cita ha sido confirmada</h1>
    </div>

    <div class="content">
        <p>Estimado/a {{ $appointment->client->name }},</p>

        <p>Nos complace informarle que su cita en ServiSpin ha sido <strong>confirmada</strong>.</p>

        <div class="appointment-details">
            <p><strong>Servicio:</strong> {{ $appointment->service->name }}</p>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($appointment->start_time)->format('d/m/Y') }}</p>
            <p><strong>Hora:</strong> {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</p>
            @if ($appointment->comments)
                <p><strong>Comentarios adicionales:</strong> {{ $appointment->comments }}</p>
            @endif
        </div>

        <p>Por favor, llegue 10 minutos antes de su cita. Si necesita hacer algún cambio, contáctenos lo antes posible.
        </p>

        <p>Si tiene alguna pregunta adicional, no dude en ponerse en contacto con nosotros.</p>

        <p>¡Gracias por elegir ServiSpin!</p>

        <p>Atentamente,<br>El equipo de ServiSpin</p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} ServiSpin. Todos los derechos reservados.</p>
        <p>Este es un correo electrónico automático. Por favor, no responda a este mensaje.</p>
    </div>
</body>

</html>
