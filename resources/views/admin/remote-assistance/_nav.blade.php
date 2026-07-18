{{-- Navegación secundaria entre bandeja, historial y calendario --}}
<div class="flex flex-wrap gap-3 text-sm mb-8">
    <a href="{{ route('admin.remote-assistance.index') }}"
       class="px-4 py-2 rounded-md font-semibold {{ request()->routeIs('admin.remote-assistance.index') ? 'bg-violet-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-100' }}">
        Bandeja
        @if (($pendingCount ?? 0) > 0)
            <span class="ml-1 {{ request()->routeIs('admin.remote-assistance.index') ? 'bg-amber-400 text-violet-900' : 'bg-amber-500 text-white' }} text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.remote-assistance.payments') }}"
       class="px-4 py-2 rounded-md font-semibold {{ request()->routeIs('admin.remote-assistance.payments') ? 'bg-violet-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-100' }}">
        Historial de pagos
    </a>
    <a href="{{ route('admin.appointment.calendar.index') }}"
       class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">
        Calendario
    </a>
</div>
