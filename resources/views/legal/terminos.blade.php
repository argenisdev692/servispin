@extends('layouts.app')

@php
    $empresa = $companyData->company_name ?? 'Servispin';
    $titular = $companyData->name ?? 'Cesar González';
    $direccion = $companyData->address ?? 'Las Palmas de Gran Canaria, España';
    $email = $companyData->email ?? 'info@servispin.net';
    $web = $companyData->website ?? 'https://servispin.net';
@endphp

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="mb-6">
            <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:underline">&larr; Volver al inicio</a>
        </div>

        <article class="prose max-w-none">
            <h1>Aviso Legal y Condiciones de Uso</h1>
            <p class="text-sm text-gray-500">Última actualización: {{ date('d/m/Y') }}</p>

            <h2>1. Titular del sitio</h2>
            <p>
                Este sitio web es titularidad de <strong>{{ $titular }}</strong> ({{ $empresa }}), con
                domicilio en {{ $direccion }}. Puedes contactar en
                <a href="mailto:{{ $email }}">{{ $email }}</a>. Sitio web:
                <a href="{{ $web }}" target="_blank" rel="noopener">{{ $web }}</a>.
            </p>

            <h2>2. Servicios</h2>
            <p>{{ $empresa }} ofrece:</p>
            <ul>
                <li><strong>Reparación a domicilio</strong> de electrodomésticos en Gran Canaria.</li>
                <li><strong>Asistencia técnica remota</strong> por videollamada, disponible desde
                    cualquier parte del mundo, en la que un técnico te guía para diagnosticar o reparar
                    tu aparato. La asistencia remota es un servicio de orientación por vídeo; su
                    resultado depende de la información que facilites y de tu colaboración durante la
                    llamada.</li>
            </ul>

            <h2>3. Pagos</h2>
            <p>
                La sesión de asistencia remota se abona por adelantado a través de SumUp. Tras el pago,
                verificamos manualmente que el cobro se ha realizado antes de confirmar la cita y enviar
                el enlace de la videollamada. En ningún momento tratamos datos de tu tarjeta.
            </p>

            <h2>4. Cancelaciones y reembolsos</h2>
            <p>
                Si cancelamos una cita ya pagada, gestionaremos la devolución del importe a través de
                SumUp. Para reprogramar o consultar el estado de un pago, escríbenos a
                <a href="mailto:{{ $email }}">{{ $email }}</a>.
            </p>

            <h2>5. Uso del sitio</h2>
            <p>
                Te comprometes a usar el sitio y sus formularios de forma lícita y a facilitar
                información veraz. No está permitido usar la web para fines fraudulentos ni introducir
                datos de terceros sin su autorización.
            </p>

            <h2>6. Protección de datos</h2>
            <p>
                El tratamiento de los datos personales que nos facilites se rige por nuestra
                <a href="{{ route('legal.privacidad') }}">política de privacidad</a> y nuestra
                <a href="{{ route('legal.cookies') }}">política de cookies</a>.
            </p>

            <h2>7. Legislación aplicable</h2>
            <p>
                Estas condiciones se rigen por la legislación española. Para cualquier controversia,
                las partes se someten a los juzgados y tribunales que correspondan conforme a la ley.
            </p>
        </article>
    </div>
@endsection
