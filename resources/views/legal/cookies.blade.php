@extends('layouts.app')

@php
    $empresa = $companyData->company_name ?? 'Servispin';
    $email = $companyData->email ?? 'info@servispin.net';
@endphp

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="mb-6">
            <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:underline">&larr; Volver al inicio</a>
        </div>

        <article class="prose max-w-none">
            <h1>Política de Cookies</h1>
            <p class="text-sm text-gray-500">Última actualización: {{ date('d/m/Y') }}</p>

            <p>
                Una cookie es un pequeño archivo que un sitio web guarda en tu navegador. En
                {{ $empresa }} usamos cookies y tecnologías similares (como el almacenamiento local
                del navegador) para que la web funcione y, con tu permiso, para medir su uso.
            </p>

            <h2>1. Cookies necesarias (no requieren consentimiento)</h2>
            <p>
                Son imprescindibles para que el sitio funcione y no se pueden desactivar. Incluyen la
                cookie de sesión y el token de seguridad (CSRF) que protege los formularios. Sin ellas,
                no podrías reservar una cita ni iniciar sesión.
            </p>

            <h2>2. Cookies funcionales</h2>
            <p>
                Recuerdan pequeñas preferencias en tu propio navegador, como que ya has visto el aviso
                de un servicio o que has decidido sobre las cookies. No te identifican ni se comparten.
            </p>

            <h2>3. Cookies de analítica (requieren tu consentimiento)</h2>
            <p>
                Usamos <strong>Google Analytics</strong> para entender de forma agregada cómo se usa la
                web y mejorarla. Estas cookies <strong>solo se activan si las aceptas</strong> en el
                aviso que aparece al entrar. Si las rechazas, no se cargan.
            </p>

            <h2>4. Cómo gestionar tu decisión</h2>
            <p>
                Puedes aceptar o rechazar las cookies de analítica en el aviso que aparece al visitar la
                web. Si quieres cambiar tu decisión más adelante, borra los datos del sitio en tu
                navegador y volverá a aparecer el aviso. También puedes bloquear o eliminar cookies
                desde la configuración de tu navegador (Chrome, Firefox, Safari, Edge…).
            </p>

            <h2>5. Más información</h2>
            <p>
                Para cualquier duda sobre esta política, escríbenos a
                <a href="mailto:{{ $email }}">{{ $email }}</a>. El tratamiento de los datos personales
                se detalla en nuestra
                <a href="{{ route('legal.privacidad') }}">política de privacidad</a>.
            </p>
        </article>
    </div>
@endsection
