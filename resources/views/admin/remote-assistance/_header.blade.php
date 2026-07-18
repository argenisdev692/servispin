{{--
    Cabecera admin de asistencia remota: logo + breadcrumb al dashboard.

    @param string|null $current  Último nivel del breadcrumb (p. ej. "Bandeja", "Historial de pagos")
--}}
<div class="mb-8">
    <div class="flex justify-center mb-5">
        <a href="{{ route('dashboard') }}" aria-label="Volver al panel de Servispin">
            <img src="{{ asset('files/images/logo.png') }}" class="h-14 w-auto" alt="Servispin">
        </a>
    </div>

    <nav class="text-sm text-gray-500 dark:text-gray-400" aria-label="Migas de pan">
        <a href="{{ route('dashboard') }}" class="hover:text-violet-600 hover:underline dark:hover:text-violet-400">
            Dashboard
        </a>
        <span class="mx-1.5 text-gray-400">/</span>
        @isset($current)
            <a href="{{ route('admin.remote-assistance.index') }}"
               class="hover:text-violet-600 hover:underline dark:hover:text-violet-400">
                Asistencia remota
            </a>
            <span class="mx-1.5 text-gray-400">/</span>
            <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $current }}</span>
        @else
            <span class="text-gray-800 dark:text-gray-200 font-medium">Asistencia remota</span>
        @endisset
    </nav>
</div>
