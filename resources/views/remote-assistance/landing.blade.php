@extends('layouts.app')

{{--
    Landing del servicio (US-4): en 5 segundos el visitante tiene que entender
    que puede arreglar su lavadora por videollamada, con precio y duración a la
    vista antes de rellenar nada.
--}}

@push('styles')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-12">
        @include('remote-assistance._header')

        <div class="text-center mb-10">
            {{-- Distintivo bien visible: esto NO es una visita a domicilio. Es la
                 preocupación de Cesar — que no se confunda con la reparación
                 presencial de siempre. --}}
            <span class="inline-block mb-4 px-4 py-1 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold uppercase tracking-wide">
                100 % por videollamada · No vamos a tu casa
            </span>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Arregla tu electrodoméstico por videollamada
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Un técnico te guía paso a paso por vídeo desde donde estés, en cualquier parte del
                mundo. Tú tienes el aparato delante; nosotros te decimos qué hacer. Sin esperar a que
                nadie se desplace.
            </p>
        </div>

        {{-- Aclara la diferencia con el servicio presencial para que nadie reserve
             lo que no es (US-4 / preocupación de Cesar). --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-10 max-w-3xl mx-auto">
            <div class="border border-blue-200 bg-blue-50 rounded-lg p-4">
                <p class="font-semibold text-blue-900 mb-1">📹 Esto es asistencia remota</p>
                <p class="text-sm text-blue-800">
                    Te guiamos por videollamada para que tú mismo lo diagnostiques o repares.
                    Ideal para averías sencillas o si te estás iniciando en la reparación.
                </p>
            </div>
            <div class="border border-gray-200 bg-gray-50 rounded-lg p-4">
                <p class="font-semibold text-gray-900 mb-1">🔧 ¿Prefieres que vayamos a tu casa?</p>
                <p class="text-sm text-gray-700">
                    Si estás en Gran Canaria y quieres una visita presencial para reparación o
                    inspección, eso se pide por otro lado.
                    <a href="{{ route('appointments.book') }}" class="text-blue-600 underline">Reservar visita a domicilio</a>.
                </p>
            </div>
        </div>

        {{-- Banner del servicio (US-4). El eslogan va por ENCIMA en HTML, no
             quemado en la imagen: así es nítido, editable, traducible y legible
             por buscadores/lectores de pantalla. Un scrim en degradado garantiza
             el contraste del texto sobre cualquier imagen. --}}
        <div class="relative mb-10 rounded-lg overflow-hidden shadow-lg">
            <img src="{{ asset('files/images/asistencia-online.webp') }}"
                 alt="Asistencia técnica remota por videollamada"
                 loading="lazy" decoding="async"
                 class="w-full block">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-5 sm:p-8">
                <p class="text-white text-xl sm:text-3xl font-bold drop-shadow mb-1">
                    ¿Tu electrodoméstico falló? Te ayudamos por videollamada.
                </p>
                <p class="text-white/90 text-sm sm:text-lg drop-shadow">
                    Estés donde estés, en cualquier parte del mundo. ¿Te inicias en la reparación?
                    Te guiamos paso a paso.
                </p>
            </div>
        </div>

        @if ($service)
            <div class="bg-white border border-gray-200 rounded-lg p-8 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">Precio</p>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ $service->price ? number_format($service->price, 2) . ' €' : 'Consúltanos' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">Duración</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $service->duration }} min</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">Dónde</p>
                        <p class="text-3xl font-bold text-gray-900">Tu móvil</p>
                    </div>
                </div>
            </div>

            <div class="text-center mb-12">
                <a href="{{ route('remote-assistance.book') }}"
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-10 rounded-lg text-lg transition">
                    Reservar mi sesión
                </a>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 text-center mb-12">
                <p class="text-amber-900">
                    El servicio de asistencia remota no está disponible en este momento.
                    @if ($companyData && $companyData->phone)
                        Llámanos al <strong>{{ $companyData->phone }}</strong> y te ayudamos.
                    @endif
                </p>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-3xl mb-2">1️⃣</div>
                <h3 class="font-semibold text-gray-900 mb-1">Pagas la sesión</h3>
                <p class="text-sm text-gray-600">Escaneas nuestro QR y pagas con tu móvil. Nosotros nunca vemos tu tarjeta.</p>
            </div>
            <div class="text-center">
                <div class="text-3xl mb-2">2️⃣</div>
                <h3 class="font-semibold text-gray-900 mb-1">Eliges tu hora</h3>
                <p class="text-sm text-gray-600">Ves los huecos en tu zona horaria, estés donde estés.</p>
            </div>
            <div class="text-center">
                <div class="text-3xl mb-2">3️⃣</div>
                <h3 class="font-semibold text-gray-900 mb-1">Te llamamos</h3>
                <p class="text-sm text-gray-600">Recibes el enlace por email. Se abre en el navegador, sin instalar nada.</p>
            </div>
        </div>
    </div>
@endsection
