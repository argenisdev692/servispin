@extends('layouts.app')

@php
    $empresa = $companyData->company_name ?? 'Servispin';
    $titular = $companyData->name ?? 'Cesar González';
    $direccion = $companyData->address ?? 'Las Palmas de Gran Canaria, España';
    $email = $companyData->email ?? 'info@servispin.net';
@endphp

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="mb-6">
            <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:underline">&larr; Volver al inicio</a>
        </div>

        <article class="prose max-w-none">
            <h1>Política de Privacidad</h1>
            <p class="text-sm text-gray-500">Última actualización: {{ date('d/m/Y') }}</p>

            <h2>1. Responsable del tratamiento</h2>
            <p>
                El responsable del tratamiento de tus datos es <strong>{{ $titular }}</strong>
                ({{ $empresa }}), con domicilio en {{ $direccion }} y correo de contacto
                <a href="mailto:{{ $email }}">{{ $email }}</a>.
            </p>

            <h2>2. Qué datos recogemos</h2>
            <p>Cuando reservas una cita (presencial o remota) o nos contactas, tratamos:</p>
            <ul>
                <li>Datos de contacto: nombre, apellidos, correo electrónico y teléfono.</li>
                <li>Datos del servicio: descripción de la avería, marca del aparato y, opcionalmente, fotografías.</li>
                <li>En citas presenciales: dirección donde se presta el servicio.</li>
                <li>En asistencia remota: tu zona horaria y los datos de la declaración de pago
                    (referencia del recibo de SumUp, importe y nombre del pagador).</li>
            </ul>
            <p>
                <strong>No solicitamos ni almacenamos datos de tu tarjeta</strong> (número, CVV ni
                caducidad). El cobro lo gestiona íntegramente SumUp; nosotros solo recibimos una
                referencia del recibo para poder verificar que el pago se ha realizado.
            </p>

            <h2>3. Para qué usamos tus datos y base legal</h2>
            <ul>
                <li><strong>Gestionar tu cita y prestarte el servicio</strong> (reparación presencial o
                    asistencia por videollamada), incluida la verificación del pago y el envío de
                    confirmaciones y recordatorios. Base legal: ejecución de un contrato
                    (art. 6.1.b RGPD).</li>
                <li><strong>Atender tus consultas</strong> a través del formulario de contacto. Base
                    legal: tu consentimiento y nuestro interés legítimo en responderte.</li>
                <li><strong>Analítica web</strong> (solo si aceptas las cookies). Base legal: tu
                    consentimiento (ver la <a href="{{ route('legal.cookies') }}">política de cookies</a>).</li>
            </ul>

            <h2>4. Con quién compartimos tus datos</h2>
            <p>Solo con proveedores que nos prestan servicios técnicos, como encargados del tratamiento:</p>
            <ul>
                <li><strong>SumUp</strong> — procesamiento del pago.</li>
                <li><strong>Supabase</strong> — almacenamiento de las fotografías que subes.</li>
                <li><strong>Resend</strong> — envío de los correos electrónicos.</li>
                <li><strong>Google</strong> — generación del enlace de videollamada (Google Meet) en la
                    asistencia remota.</li>
            </ul>
            <p>
                Algunos de estos proveedores pueden tratar datos fuera del Espacio Económico Europeo.
                En esos casos, la transferencia se ampara en las garantías previstas por el RGPD
                (por ejemplo, cláusulas contractuales tipo de la Comisión Europea). No vendemos tus
                datos ni los cedemos a terceros con fines comerciales.
            </p>

            <h2>5. Cuánto tiempo los conservamos</h2>
            <p>
                Conservamos tus datos mientras dure la relación y, después, durante los plazos
                legalmente exigibles (por ejemplo, obligaciones fiscales y contables). Cuando dejan de
                ser necesarios, se eliminan o se anonimizan.
            </p>

            <h2>6. Tus derechos</h2>
            <p>
                Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición, limitación
                y portabilidad escribiéndonos a <a href="mailto:{{ $email }}">{{ $email }}</a>.
                También tienes derecho a presentar una reclamación ante la Agencia Española de
                Protección de Datos (<a href="https://www.aepd.es" target="_blank" rel="noopener">www.aepd.es</a>)
                si consideras que no hemos tratado tus datos correctamente.
            </p>

            <h2>7. Personas fuera de la Unión Europea</h2>
            <p>
                La asistencia remota puede contratarse desde cualquier país. Si nos facilitas tus datos
                desde fuera de la UE, el tratamiento que hacemos de ellos es el mismo descrito aquí.
            </p>
        </article>
    </div>
@endsection
