@extends('layouts.app')

@section('content')
    <div :class="{ 'theme-dark': dark }" x-data="data()" lang="es">
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">

            <x-menu-sidebar />

            <div class="flex flex-col flex-1 w-full">
                <x-header-dashboard />

                <main class="h-full overflow-y-auto">
                    <div class="container px-6 mx-auto py-6 max-w-6xl">

                        @include('admin.remote-assistance._header', ['current' => 'Historial de pagos'])

                        <div class="mb-6">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">Historial de pagos remotos</h1>
                            <p class="text-gray-600 dark:text-gray-400">Trazabilidad de referencias SumUp y acciones de verificación.</p>
                        </div>

                        @include('admin.remote-assistance._nav', ['pendingCount' => $pendingCount])

        <form method="GET" action="{{ route('admin.remote-assistance.payments') }}" class="mb-6 flex gap-3">
            <input type="search" name="reference" value="{{ $reference }}"
                   placeholder="Buscar por referencia SumUp…"
                   class="flex-1 rounded-md border-gray-300 text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-semibold">
                Buscar
            </button>
            @if ($reference !== '')
                <a href="{{ route('admin.remote-assistance.payments') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700">
                    Limpiar
                </a>
            @endif
        </form>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Evento</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Referencia</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Importe</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Cliente / Cita</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Registrado por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($events as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                {{ $event->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900">{{ $event->label() }}</span>
                                @if ($event->notes)
                                    <p class="text-xs text-gray-500 mt-0.5 max-w-xs truncate" title="{{ $event->notes }}">{{ $event->notes }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-900">{{ $event->reference ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-900">
                                @if ($event->amount !== null)
                                    {{ number_format($event->amount, 2) }} {{ $event->currency }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($event->appointment)
                                    <p class="font-medium text-gray-900">
                                        {{ $event->appointment->client_first_name }} {{ $event->appointment->client_last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $event->appointment->start_time?->format('d/m/Y H:i') }}
                                        · {{ $event->appointment->service?->name }}
                                    </p>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $event->recorder?->name ?? 'Sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                No hay eventos de pago{{ $reference !== '' ? ' con esa referencia' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $events->links() }}
        </div>

                    </div>
                </main>
            </div>
        </div>
    </div>
@endsection
