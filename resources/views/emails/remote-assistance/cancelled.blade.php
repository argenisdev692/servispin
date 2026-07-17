{{-- Cancelación de cita remota (US-5). Con aviso de reembolso si estaba pagada. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita cancelada</title>
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

        .refund {
            background-color: #ecfdf5;
            border-left: 4px solid #16a34a;
            padding: 15px;
            border-radius: 4px;
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
        <h1>Tu cita ha sido cancelada</h1>
    </div>

    <div class="content">
        <p>Hola {{ $appointment->client_first_name }},</p>

        <p>Te confirmamos que tu sesión de asistencia remota ha sido cancelada.</p>

        @if ($refundPending)
            <div class="refund">
                <strong>Vamos a devolverte el importe que pagaste.</strong>
                Estamos tramitando el reembolso de {{ number_format($appointment->payment_amount, 2) }}
                {{ $appointment->payment_currency }} a través de SumUp. Puede tardar unos días en
                reflejarse en tu cuenta, según tu banco.
            </div>
        @endif

        <p>Si quieres reprogramar la sesión, respóndenos a este email y buscamos un nuevo hueco.</p>

        <div class="company-info">
            <p><strong>{{ $companyData->company_name }}</strong></p>
            @if ($companyData->phone)
                <p>Teléfono: {{ $companyData->phone }}</p>
            @endif
        </div>
    </div>
</body>

</html>
