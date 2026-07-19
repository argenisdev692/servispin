@extends('layouts.app')

{{--
    Bandeja de verificación de pagos remotos (US-2, T028).

    Objetivo de diseño medible (spec §10): Cesar debe tardar menos de 1 minuto por
    cita. Por eso la referencia y el importe van en grande y juntos: es lo único
    que tiene que cotejar contra la app de SumUp.
--}}

@section('content')
    <x-admin-shell lang="es">
                    <div class="container px-6 mx-auto py-6 max-w-6xl">

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Asistencia remota</h1>
                <p class="text-gray-600">Pagos declarados pendientes de cotejar en SumUp.</p>
            </div>

            @include('admin.remote-assistance._nav', ['pendingCount' => $pendingCount])

            {{-- FR-15: citas pagadas y confirmadas que se quedaron sin enlace porque
                 el provider automático falló. Son las urgentes: el cliente ya pagó y
                 tiene una cita a la que no puede entrar. --}}
            @if ($awaitingLink->isNotEmpty())
                <div class="bg-red-50 border border-red-300 rounded-lg p-5 mb-8">
                    <h2 class="text-lg font-semibold text-red-900 mb-2">
                        ⚠️ {{ $awaitingLink->count() }} cita(s) confirmada(s) sin enlace
                    </h2>
                    <p class="text-sm text-red-800 mb-4">
                        Estos clientes han pagado y su cita está confirmada, pero no se pudo generar el
                        enlace automáticamente. Añádelo a mano cuanto antes.
                    </p>
                    <ul class="space-y-4">
                        @foreach ($awaitingLink as $appointment)
                            <li class="text-sm text-red-900 border border-red-200 rounded p-3 bg-white">
                                <p class="mb-2">
                                    <strong>{{ $appointment->client_first_name }} {{ $appointment->client_last_name }}</strong>
                                    — {{ $appointment->start_time->format('d/m/Y H:i') }}
                                    ({{ $appointment->client_email }})
                                </p>
                                <form class="link-form flex flex-col sm:flex-row gap-2" data-id="{{ $appointment->id }}">
                                    <input type="url" name="meeting_url" required
                                           placeholder="https://meet.google.com/…"
                                           class="flex-1 rounded-md border-gray-300 text-sm">
                                    <button type="submit"
                                            class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-md font-semibold text-sm whitespace-nowrap">
                                        Guardar y reenviar
                                    </button>
                                </form>
                                <p class="link-feedback text-xs mt-1 hidden"></p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (! $providerIsAutomatic)
                <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6 text-sm text-blue-900">
                    El generador de enlaces está en <strong>modo manual</strong>: tendrás que pegar el enlace
                    de la videollamada al confirmar cada cita.
                </div>
            @endif

            @forelse ($appointments as $appointment)
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4" data-appointment="{{ $appointment->id }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Lo que hay que cotejar con SumUp, primero y en grande --}}
                        <div class="md:col-span-1 bg-gray-50 rounded p-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Referencia SumUp</p>
                            <p class="text-lg font-mono font-bold text-gray-900 break-all mb-3">
                                {{ $appointment->payment_reference }}
                            </p>
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Importe declarado</p>
                            <p class="text-2xl font-bold text-gray-900 mb-3">
                                {{ number_format($appointment->payment_amount, 2) }} {{ $appointment->payment_currency }}
                            </p>
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Pagador</p>
                            <p class="text-sm text-gray-900">{{ $appointment->payer_name }}</p>
                            <p class="text-xs text-gray-500 mt-3">
                                Declarado {{ $appointment->payment_claimed_at?->diffForHumans() }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $appointment->client_first_name }} {{ $appointment->client_last_name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $appointment->client_email }} · {{ $appointment->client_phone }}
                                    </p>
                                </div>
                                <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded font-semibold uppercase">
                                    Pago sin verificar
                                </span>
                            </div>

                            {{-- FR-6: la hora, siempre con su huso. Y la del cliente,
                                 porque puede estar en cualquier parte del mundo. --}}
                            <p class="text-sm text-gray-700 mb-1">
                                <strong>{{ $appointment->start_time->format('d/m/Y H:i') }}</strong>
                                <span class="text-gray-500">({{ config('remote_assistance.business_timezone') }})</span>
                            </p>
                            @if ($appointment->client_timezone && $appointment->client_timezone !== config('remote_assistance.business_timezone'))
                                <p class="text-sm text-gray-500 mb-3">
                                    Para el cliente:
                                    {{ $appointment->start_time->copy()->setTimezone($appointment->client_timezone)->format('d/m/Y H:i') }}
                                    ({{ $appointment->client_timezone }})
                                </p>
                            @endif

                            <p class="text-sm text-gray-700 mb-1">
                                <strong>Servicio:</strong> {{ $appointment->service?->name }}
                                @if ($appointment->brand)
                                    · <strong>Marca:</strong> {{ $appointment->brand->name }}
                                @endif
                            </p>
                            <p class="text-sm text-gray-700 mb-4">
                                <strong>Avería:</strong> {{ $appointment->issue_description }}
                            </p>

                            @if ($appointment->equipment_photo_url)
                                <a href="{{ $appointment->equipment_photo_url }}" target="_blank"
                                   class="text-sm text-blue-600 hover:underline">Ver foto del aparato</a>
                            @endif

                            <form class="verify-form mt-4 space-y-3" data-id="{{ $appointment->id }}">
                                @if (! $providerIsAutomatic)
                                    <input type="url" name="meeting_url" placeholder="https://… enlace de la videollamada"
                                           class="w-full rounded-md border-gray-300 text-sm">
                                @endif
                                <input type="text" name="reason" placeholder="Motivo (solo si rechazas)"
                                       class="w-full rounded-md border-gray-300 text-sm">

                                <div class="flex gap-3">
                                    <button type="button" data-decision="verify"
                                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">
                                        Confirmar y enviar enlace
                                    </button>
                                    <button type="button" data-decision="reject"
                                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                                        Rechazar
                                    </button>
                                </div>
                                <p class="form-feedback text-sm hidden"></p>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                    <p class="text-gray-600">No hay pagos pendientes de verificar.</p>
                </div>
            @endforelse

            <div class="mt-6">
                {{ $appointments->links() }}
            </div>

                    </div>
    </x-admin-shell>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.verify-form').forEach(function (form) {
            const id = form.dataset.id;
            const feedback = form.querySelector('.form-feedback');

            form.querySelectorAll('button[data-decision]').forEach(function (btn) {
                btn.addEventListener('click', async function () {
                    const decision = btn.dataset.decision;

                    if (decision === 'reject' && !confirm('¿Rechazar esta solicitud? Se cancelará la cita y se avisará al cliente.')) {
                        return;
                    }

                    form.querySelectorAll('button').forEach(b => b.disabled = true);
                    feedback.classList.add('hidden');

                    const payload = { decision: decision };
                    const meetingUrl = form.querySelector('[name="meeting_url"]');
                    const reason = form.querySelector('[name="reason"]');
                    if (meetingUrl && meetingUrl.value) payload.meeting_url = meetingUrl.value;
                    if (reason && reason.value) payload.reason = reason.value;

                    try {
                        const res = await fetch('/admin/appointments/' + id + '/verify-payment', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        const json = await res.json();

                        if (res.ok) {
                            const card = document.querySelector('[data-appointment="' + id + '"]');
                            card.style.opacity = '0.5';
                            feedback.textContent = json.message;
                            feedback.className = 'form-feedback text-sm text-green-700';
                            setTimeout(() => window.location.reload(), 1500);
                            return;
                        }

                        const messages = json.errors ? Object.values(json.errors).flat().join(' ') : json.message;
                        feedback.textContent = messages;
                        feedback.className = 'form-feedback text-sm text-red-700';
                        form.querySelectorAll('button').forEach(b => b.disabled = false);
                    } catch (e) {
                        feedback.textContent = 'Error de conexión.';
                        feedback.className = 'form-feedback text-sm text-red-700';
                        form.querySelectorAll('button').forEach(b => b.disabled = false);
                    }
                });
            });
        });

        document.querySelectorAll('.link-form').forEach(function (form) {
            const id = form.dataset.id;
            const feedback = form.parentElement.querySelector('.link-feedback');

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const meetingUrl = form.querySelector('[name="meeting_url"]').value;
                form.querySelector('button').disabled = true;
                feedback.classList.add('hidden');

                try {
                    const res = await fetch('/admin/appointments/' + id + '/meeting-link', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ meeting_url: meetingUrl, resend_email: true })
                    });
                    const json = await res.json();

                    if (res.ok) {
                        feedback.textContent = json.message;
                        feedback.className = 'link-feedback text-xs mt-1 text-green-700';
                        form.style.opacity = '0.5';
                    } else {
                        const messages = json.errors ? Object.values(json.errors).flat().join(' ') : json.message;
                        feedback.textContent = messages;
                        feedback.className = 'link-feedback text-xs mt-1 text-red-700';
                        form.querySelector('button').disabled = false;
                    }
                } catch (err) {
                    feedback.textContent = 'Error de conexión.';
                    feedback.className = 'link-feedback text-xs mt-1 text-red-700';
                    form.querySelector('button').disabled = false;
                }
            });
        });
    </script>
@endpush
