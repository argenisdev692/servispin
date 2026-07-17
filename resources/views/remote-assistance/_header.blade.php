{{--
    Cabecera común de las páginas públicas de asistencia remota.
    Logo de Servispin + breadcrumb para volver a Inicio + hora de Canarias en vivo.

    Parámetro opcional $current: texto del último nivel del breadcrumb
    (p. ej. "Solicitar"). Si no se pasa, el breadcrumb acaba en "Asistencia remota".
--}}
<div class="mb-8">
    {{-- Logo --}}
    <div class="flex justify-center mb-4">
        <a href="{{ url('/') }}" aria-label="Ir al inicio de Servispin">
            <x-application-mark class="block h-12 w-auto" />
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500" aria-label="Migas de pan">
            <a href="{{ url('/') }}" class="hover:text-blue-600 hover:underline">Inicio</a>
            <span class="mx-1">/</span>
            @isset($current)
                <a href="{{ route('remote-assistance.landing') }}" class="hover:text-blue-600 hover:underline">Asistencia remota</a>
                <span class="mx-1">/</span>
                <span class="text-gray-700 font-medium">{{ $current }}</span>
            @else
                <span class="text-gray-700 font-medium">Asistencia remota</span>
            @endisset
        </nav>

        {{-- Hora de Canarias en vivo: un cliente en otro huso ve qué hora es aquí --}}
        <p class="text-sm text-gray-500">
            🇮🇨 Hora en Canarias:
            <span id="canary-clock" class="font-medium text-gray-700">--:--</span>
        </p>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const el = document.getElementById('canary-clock');
                if (!el) return;

                function tick() {
                    el.textContent = new Intl.DateTimeFormat('es-ES', {
                        timeZone: 'Atlantic/Canary',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    }).format(new Date());
                }

                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endpush
@endonce
