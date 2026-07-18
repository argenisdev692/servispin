@extends('layouts.app')

{{-- FullCalendar CSS --}}
@push('styles')
    {{-- Use a specific version of FullCalendar --}}
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css' rel='stylesheet' />
    {{-- Add meta CSRF token if not in main layout --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Optional: Customize calendar appearance */
        #calendar {
            max-width: 1100px;
            margin: 20px auto;
            padding: 0 10px;
        }

        /* Style for event tooltips (using tippy.js) */
        .tippy-box[data-theme~='light-border'] {
            font-size: 0.85rem;
        }

        .tippy-box[data-theme~='light-border'] .tippy-content {
            padding: 0.5rem;
        }

        /* Mejoras para la visualización de eventos */
        .fc-event {
            font-size: 0.75rem !important;
            /* Reduce tamaño de fuente */
            line-height: 1.2 !important;
            /* Reduce espacio entre líneas */
        }

        /* Estilo para el contenido personalizado de eventos */
        .fc-event-content-custom {
            width: 100%;
            padding: 1px 2px !important;
        }

        /* Servicio (primera línea) */
        .service-title {
            font-weight: bold;
            font-size: 0.8rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        /* Horario (segunda línea) */
        .event-time {
            font-size: 0.7rem;
            opacity: 0.85;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        /* Cliente (última línea) */
        .client-name {
            font-size: 0.7rem;
            opacity: 0.9;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Ocultamos el título nativo para que no se duplique */
        .fc-event-title-container,
        .fc-event-time {
            display: none !important;
        }

        /* Aseguramos que la hora aparezca entre servicio y cliente */
        .fc-event-time {
            font-size: 0.7rem !important;
            padding: 1px 2px !important;
            order: 2;
            opacity: 0.9;
        }

        /* Mejora del layout de celdas */
        .fc-timegrid-event-harness {
            margin-left: 1px !important;
            margin-right: 1px !important;
        }

        .fc-timegrid-event {
            padding: 1px 2px !important;
        }
    </style>
@endpush

@section('content')
    {{-- Use standard admin layout structure --}}
    <div :class="{ 'theme-dark': dark }" x-data="data()" lang="es"> {{-- Assuming data() provides dark mode toggle etc. --}}
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">

            <!-- MENU SIDEBAR -->
            <x-menu-sidebar />
            <!-- END MENU SIDEBAR -->

            <div class="flex flex-col flex-1 w-full">

                <!-- HEADER -->
                <x-header-dashboard />
                <!-- END HEADER -->

                <main class="h-full overflow-y-auto">
                    <div class="container px-6 mx-auto grid">

                        {{-- Page Title --}}
                        <div
                            class="mt-5 flex items-center justify-between p-4 mb-8 text-sm font-semibold text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:shadow-outline-purple">
                            <div class="flex items-center">
                                {{-- Heroicon: calendar --}}
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span>Calendario de Citas</span>
                            </div>
                        </div>

                        {{-- Calendar Container --}}
                        <div class="bg-white dark:bg-gray-800 shadow-md rounded p-4 mb-4">
                            <div id='calendar'></div>
                        </div>

                        {{-- Simple Modal for Event Details (using basic HTML/Tailwind) --}}
                        <div id="eventDetailModal" class="fixed z-50 inset-0 overflow-y-auto hidden"
                            aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div
                                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true">
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                    aria-hidden="true">&#8203;</span>
                                <div
                                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 relative">
                                        {{-- Close (X) button in top right corner --}}
                                        <button type="button" id="closeEventModalBtn" aria-label="Cerrar"
                                            class="absolute top-3 right-3 z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-600 p-0 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg class="block h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full pr-8">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                                    id="modalEventTitle"></h3>
                                                <div class="mt-2 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <p><strong>Cliente:</strong> <span id="modalEventClient"></span></p>
                                                    <p><strong>Email:</strong> <span id="modalEventEmail"></span></p>
                                                    <p><strong>Teléfono:</strong> <span id="modalEventPhone"></span></p>
                                                    <p><strong>Servicio:</strong> <span id="modalEventService"></span></p>
                                                    <p><strong>Estado:</strong> <span id="modalEventStatus"
                                                            class="px-2 py-1 text-xs font-bold rounded-full"></span></p>

                                                    {{-- Sección remota: pago, enlace, acciones --}}
                                                    <div id="remoteSection" class="hidden mt-3 p-3 rounded-lg border border-violet-200 bg-violet-50 dark:bg-violet-900/20 dark:border-violet-700 space-y-2">
                                                        <p class="text-xs font-bold uppercase tracking-wide text-violet-800 dark:text-violet-200">📹 Asistencia remota</p>
                                                        <p><strong>Pago:</strong> <span id="modalPaymentStatus"></span></p>
                                                        <p><strong>Referencia SumUp:</strong> <span id="modalPaymentReference" class="font-mono"></span></p>
                                                        <p><strong>Importe:</strong> <span id="modalPaymentAmount"></span></p>
                                                        <p><strong>Pagador:</strong> <span id="modalPayerName"></span></p>
                                                        <p id="modalClientTimezoneRow" class="hidden"><strong>Huso cliente:</strong> <span id="modalClientTimezone"></span></p>
                                                        <p id="modalMeetingUrlRow" class="hidden"><strong>Enlace:</strong>
                                                            <a id="modalMeetingUrl" href="#" target="_blank" class="text-blue-600 underline break-all"></a>
                                                        </p>
                                                        <p id="modalMeetingFailed" class="hidden text-red-700 text-xs font-semibold">⚠️ Confirmada sin enlace — pégalo abajo.</p>

                                                        <div id="remoteVerifyActions" class="hidden space-y-2 pt-2">
                                                            @unless ($providerIsAutomatic)
                                                                <input type="url" id="remoteMeetingUrlInput" placeholder="https://… enlace de videollamada"
                                                                    class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600">
                                                            @endunless
                                                            <input type="text" id="remoteRejectReason" placeholder="Motivo (solo si rechazas el pago)"
                                                                class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600">
                                                        </div>

                                                        <div id="remoteLinkActions" class="hidden space-y-2 pt-2">
                                                            <input type="url" id="addMeetingUrlInput" placeholder="https://meet.google.com/…"
                                                                class="w-full rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600">
                                                        </div>
                                                    </div>

                                                    <p id="modalAddressRow"><strong>Dirección:</strong> <span id="modalEventAddress"
                                                            class="whitespace-pre-wrap"></span></p>
                                                    <p><strong>Problema:</strong> <span id="modalEventIssue"
                                                            class="whitespace-pre-wrap"></span></p>
                                                    <p><strong>Notas:</strong> <span id="modalEventNotes"
                                                            class="whitespace-pre-wrap"></span></p>

                                                    <!-- Sección para la imagen del equipo -->
                                                    <div id="photoContainer" class="mt-4 hidden">
                                                        <p><strong>Foto del equipo:</strong></p>
                                                        <div class="mt-2 flex justify-center">
                                                            <img id="modalEventPhoto" src="" alt="Foto del equipo"
                                                                class="max-w-full max-h-64 rounded-lg shadow-md object-contain" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-col gap-3 justify-center">
                                        {{-- Botones presenciales (Confirmar / Rechazar cita) --}}
                                        <div id="statusActionButtons" class="flex space-x-4 justify-center">
                                            <button type="button" id="confirmAppointmentBtn"
                                                class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                                                <span class="normal-btn-text">Confirmar Cita</span>
                                                <span class="processing-btn-text hidden">
                                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    Procesando...
                                                </span>
                                            </button>
                                            <button type="button" id="declineAppointmentBtn"
                                                class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                                                <span class="normal-btn-text">Rechazar Cita</span>
                                                <span class="processing-btn-text hidden">
                                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    Procesando...
                                                </span>
                                            </button>
                                        </div>

                                        {{-- Botones remotos: verificar pago --}}
                                        <div id="remotePaymentButtons" class="hidden flex flex-wrap gap-3 justify-center">
                                            <button type="button" id="verifyPaymentBtn"
                                                class="inline-flex justify-center rounded-md shadow-sm px-4 py-2 bg-green-600 text-sm font-medium text-white hover:bg-green-700">
                                                Confirmar pago y enviar enlace
                                            </button>
                                            <button type="button" id="rejectPaymentBtn"
                                                class="inline-flex justify-center rounded-md shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700">
                                                Rechazar pago
                                            </button>
                                        </div>

                                        {{-- Botones remotos: enlace manual / reenvío --}}
                                        <div id="remoteLinkButtons" class="hidden flex flex-wrap gap-3 justify-center">
                                            <button type="button" id="saveMeetingLinkBtn"
                                                class="inline-flex justify-center rounded-md shadow-sm px-4 py-2 bg-violet-600 text-sm font-medium text-white hover:bg-violet-700">
                                                Guardar enlace y reenviar email
                                            </button>
                                            <button type="button" id="resendConfirmationBtn"
                                                class="inline-flex justify-center rounded-md shadow-sm px-4 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700">
                                                Reenviar confirmación
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- End Modal --}}

                        {{-- Modal de alta de cita remota (US-6 / T031d).
                             El cliente llama por teléfono, paga por QR y Cesar la da
                             de alta desde el hueco, sin pasar por la web.
                             Se AÑADE junto al modal de detalles; no se toca aquél. --}}
                        <div id="remoteAppointmentModal" class="fixed z-50 inset-0 overflow-y-auto hidden"
                            aria-labelledby="remote-modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900" id="remote-modal-title">
                                            Nueva cita remota
                                        </h3>
                                        <button type="button" id="closeRemoteModalBtn" aria-label="Cerrar"
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                                            <svg class="block h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <form id="remoteAppointmentForm" class="space-y-4 text-left">
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Fecha</label>
                                                <input type="date" name="date" id="remoteDate" required
                                                    class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                            </div>
                                            <div>
                                                {{-- La hora viene del hueco pulsado, pero es editable: en la
                                                     vista de mes el clic no trae hora, y así Cesar puede
                                                     corregir sin cerrar y volver a abrir. --}}
                                                <label class="block text-sm font-medium text-gray-700">Hora</label>
                                                <input type="time" name="time" id="remoteTime" required step="900"
                                                    class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Servicio remoto</label>
                                            <select name="service_id" id="remoteServiceId" required
                                                class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                                @foreach ($remoteServices as $remoteService)
                                                    <option value="{{ $remoteService->id }}" data-duration="{{ $remoteService->duration }}">
                                                        {{ $remoteService->name }} ({{ $remoteService->duration }} min)
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($remoteServices->isEmpty())
                                                <p class="mt-1 text-sm text-red-600">
                                                    No hay ningún servicio marcado como remoto. Crea uno en Servicios.
                                                </p>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                                <input type="text" name="client_first_name" required
                                                    class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Apellidos</label>
                                                <input type="text" name="client_last_name" required
                                                    class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                                <input type="email" name="client_email" required
                                                    class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                                                <input type="tel" name="client_phone"
                                                    class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                            </div>
                                        </div>

                                        {{-- FR-6 / R-5: el huso del CLIENTE, no el del navegador de Cesar.
                                             Si se cogiera el del navegador, guardaríamos Atlantic/Canary
                                             como huso de un cliente que puede estar en Argentina, y los
                                             emails le dirían una hora equivocada. Por defecto va el del
                                             negocio (cliente local, que es el caso habitual al teléfono). --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">
                                                Zona horaria del cliente
                                            </label>
                                            <select name="client_timezone" id="remoteClientTimezone" required
                                                class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                                @foreach ($timezones as $tz)
                                                    <option value="{{ $tz }}" @selected($tz === $businessTimezone)>{{ $tz }}</option>
                                                @endforeach
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Se usa para decirle su hora local en los emails. Si el cliente está
                                                fuera de Canarias, cámbialo.
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Marca</label>
                                                <select name="brand_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                                    <option value="">Sin especificar</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Avería</label>
                                            <textarea name="issue_description" rows="2"
                                                class="w-full mt-1 border-gray-300 rounded-md shadow-sm"></textarea>
                                        </div>

                                        <div class="p-4 rounded-md bg-gray-50">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="payment_verified" id="remotePaymentVerified"
                                                    class="border-gray-300 rounded">
                                                <span class="ml-2 text-sm font-medium text-gray-900">
                                                    Ya he comprobado el pago en SumUp
                                                </span>
                                            </label>
                                            {{-- FR-3: sin marcar esto, la cita queda pendiente y SIN enlace,
                                                 igual que en el formulario público. El atajo del admin no es
                                                 una puerta trasera al control de pago. --}}
                                            <p class="mt-1 text-xs text-gray-600">
                                                Si no lo marcas, la cita queda pendiente de verificar y
                                                <strong>no se envía ningún enlace</strong> al cliente.
                                            </p>

                                            <div id="remotePaymentFields" class="hidden mt-3 space-y-3">
                                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                                    <input type="text" name="payment_reference" placeholder="Referencia SumUp"
                                                        class="border-gray-300 rounded-md shadow-sm">
                                                    <input type="number" step="0.01" min="0" name="payment_amount" placeholder="Importe €"
                                                        class="border-gray-300 rounded-md shadow-sm">
                                                    <input type="text" name="payer_name" placeholder="Nombre del pagador"
                                                        class="border-gray-300 rounded-md shadow-sm">
                                                </div>
                                                @unless ($providerIsAutomatic)
                                                    {{-- Con proveedor manual nadie genera el enlace: sin él, el
                                                         cliente recibiría un "confirmada" sin forma de entrar. --}}
                                                    <input type="url" name="meeting_url" id="remoteMeetingUrl"
                                                        placeholder="https://… enlace de la videollamada"
                                                        class="w-full border-gray-300 rounded-md shadow-sm">
                                                @endunless
                                            </div>
                                        </div>

                                        <div id="remoteFormErrors" class="hidden p-3 text-sm text-red-800 border border-red-200 rounded bg-red-50"></div>

                                        <div class="flex gap-3 pt-2">
                                            <button type="submit" id="remoteSubmitBtn"
                                                class="flex-1 px-4 py-2 font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                                                Crear cita remota
                                            </button>
                                            <button type="button" id="cancelRemoteBtn"
                                                class="px-4 py-2 font-semibold text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                                Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        {{-- End Remote Modal --}}

                    </div>
                </main>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- FullCalendar JS --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/es.global.js'></script>

    {{-- Tooltip library (Tippy.js) --}}
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    {{-- Ensure jQuery and SweetAlert are loaded (usually in app.blade.php or here) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Función para formatear números de teléfono españoles
        function formatSpanishPhone(phoneNumber) {
            // Eliminar cualquier espacio existente
            let cleaned = phoneNumber.replace(/\s+/g, '');

            // Si el número comienza con +34, formatearlo correctamente
            if (cleaned.startsWith('+34')) {
                // Quitar el código de país para trabajar solo con los 9 dígitos
                const nationalNumber = cleaned.substring(3);

                // Aplicar formato +34 XXX XX XX XX
                if (nationalNumber.length === 9) {
                    return `+34 ${nationalNumber.substring(0, 3)} ${nationalNumber.substring(3, 5)} ${nationalNumber.substring(5, 7)} ${nationalNumber.substring(7, 9)}`;
                }
            }

            // Si no es un número español o no tiene el formato esperado, devolverlo sin cambios
            return phoneNumber;
        }

        // ============================================================
        // US-6 / T031d — Alta de cita remota desde un hueco del calendario
        // ============================================================

        // Guarda la API del calendario para poder refrescar tras crear la cita.
        let remoteCalendarApi = null;

        /**
         * Abre el modal de alta remota con la fecha/hora del hueco pulsado.
         *
         * ⚠️ El instante NO se convierte de huso. `info.dateStr` ya viene en el
         * timeZone configurado en el calendario (Atlantic/Canary), que es
         * exactamente como se persiste start_time. Si aquí se hiciera
         * `new Date(...).toISOString()` se mandaría UTC y en verano la cita se
         * crearía una hora antes de la que Cesar pulsó: el bug de R-5.
         */
        function openRemoteModal(info) {
            const modal = document.getElementById('remoteAppointmentModal');
            if (!modal) return;

            remoteCalendarApi = info.view.calendar;

            // "2026-07-20T10:00:00+01:00" → fecha y hora tal cual, sin convertir.
            // En la vista de mes el clic no trae hora (allDay) y se deja vacía
            // para que Cesar la escriba.
            document.getElementById('remoteDate').value = info.dateStr.slice(0, 10);
            document.getElementById('remoteTime').value =
                (!info.allDay && info.dateStr.length >= 16) ? info.dateStr.slice(11, 16) : '';

            document.getElementById('remoteFormErrors').classList.add('hidden');
            modal.classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const remoteModal = document.getElementById('remoteAppointmentModal');
            const remoteForm = document.getElementById('remoteAppointmentForm');

            if (remoteModal && remoteForm) {
                const errorsEl = document.getElementById('remoteFormErrors');
                const submitBtn = document.getElementById('remoteSubmitBtn');
                const paidCheckbox = document.getElementById('remotePaymentVerified');
                const paymentFields = document.getElementById('remotePaymentFields');

                function closeRemoteModal() {
                    remoteModal.classList.add('hidden');
                    remoteForm.reset();
                    paymentFields.classList.add('hidden');
                    errorsEl.classList.add('hidden');
                }

                document.getElementById('closeRemoteModalBtn').addEventListener('click', closeRemoteModal);
                document.getElementById('cancelRemoteBtn').addEventListener('click', closeRemoteModal);

                // Los datos del pago solo tienen sentido si Cesar ya lo cobró.
                paidCheckbox.addEventListener('change', function() {
                    paymentFields.classList.toggle('hidden', !paidCheckbox.checked);
                });

                function showRemoteErrors(messages) {
                    errorsEl.innerHTML = messages.map(m => '<div>• ' + m + '</div>').join('');
                    errorsEl.classList.remove('hidden');
                }

                remoteForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    errorsEl.classList.add('hidden');

                    const date = document.getElementById('remoteDate').value;
                    const time = document.getElementById('remoteTime').value;

                    if (!date || !time) {
                        showRemoteErrors(['Indica la fecha y la hora de la cita.']);
                        return;
                    }

                    const fd = new FormData(remoteForm);
                    const payload = {
                        service_id: fd.get('service_id'),
                        brand_id: fd.get('brand_id') || null,
                        client_first_name: fd.get('client_first_name'),
                        client_last_name: fd.get('client_last_name'),
                        client_email: fd.get('client_email'),
                        client_phone: fd.get('client_phone') || null,
                        issue_description: fd.get('issue_description') || null,
                        client_timezone: fd.get('client_timezone'),
                        // El backend exige Y-m-d H:i:s en huso del negocio. Ya lo está.
                        start_time: date + ' ' + time + ':00',
                        // Se manda explícito: un checkbox sin marcar no viaja en el
                        // FormData y la regla es `required|boolean`.
                        payment_verified: paidCheckbox.checked,
                    };

                    if (paidCheckbox.checked) {
                        payload.payment_reference = fd.get('payment_reference') || null;
                        payload.payment_amount = fd.get('payment_amount') || null;
                        payload.payer_name = fd.get('payer_name') || null;
                        if (fd.get('meeting_url')) {
                            payload.meeting_url = fd.get('meeting_url');
                        }
                    }

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Creando…';

                    try {
                        const res = await fetch('{{ route('admin.appointments.remote.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        const json = await res.json();

                        if (res.status === 201) {
                            closeRemoteModal();
                            if (remoteCalendarApi) remoteCalendarApi.refetchEvents();
                            Swal.fire('Cita creada', json.message, 'success');
                            return;
                        }

                        // El solapamiento (FR-7) lo decide el backend, no el JS:
                        // duplicar esa regla aquí sería tener dos verdades que
                        // pueden contradecirse. Aquí solo se muestra el 422.
                        const messages = json.errors
                            ? Object.values(json.errors).flat()
                            : [json.message || 'No se pudo crear la cita.'];
                        showRemoteErrors(messages);
                    } catch (err) {
                        showRemoteErrors(['Error de conexión. Inténtalo de nuevo.']);
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Crear cita remota';
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM loaded, initializing calendar");
            var calendarEl = document.getElementById('calendar');

            if (!calendarEl) {
                console.error("Calendar element not found! Check your HTML.");
                return;
            }

            console.log("Calendar element found:", calendarEl);
            const eventDetailModal = document.getElementById('eventDetailModal');
            const closeEventModalBtn = document.getElementById('closeEventModalBtn');
            const confirmAppointmentBtn = document.getElementById('confirmAppointmentBtn');
            const declineAppointmentBtn = document.getElementById('declineAppointmentBtn');
            const statusActionButtons = document.getElementById('statusActionButtons');
            const remoteSection = document.getElementById('remoteSection');
            const remotePaymentButtons = document.getElementById('remotePaymentButtons');
            const remoteLinkButtons = document.getElementById('remoteLinkButtons');
            const verifyPaymentBtn = document.getElementById('verifyPaymentBtn');
            const rejectPaymentBtn = document.getElementById('rejectPaymentBtn');
            const saveMeetingLinkBtn = document.getElementById('saveMeetingLinkBtn');
            const resendConfirmationBtn = document.getElementById('resendConfirmationBtn');
            const providerIsAutomatic = @json($providerIsAutomatic);
            let currentAppointmentId = null;
            let currentEventProps = null;

            // --- CSRF Token for AJAX --- 
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Log CSRF token for debugging
            console.log("CSRF token found:", $('meta[name="csrf-token"]').attr('content') ? "Yes" : "No");

            try {
                console.log("Creating calendar with options");
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    // Core options
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' // Views
                    },
                    initialView: 'timeGridWeek', // Default view
                    locale: 'es', // Spanish locale
                    timeZone: 'Atlantic/Canary', // Your specific timezone
                    navLinks: true, // allows users to click day/week names to navigate
                    editable: true, // enable drag and drop
                    // Se mantiene en false a propósito: US-6 dice "cuando PULSO sobre un
                    // hueco libre", y eso es dateClick (abajo), que no necesita selectable.
                    // Dejarlo en false evita cambiar el comportamiento del calendario actual.
                    selectable: false,
                    dayMaxEvents: true, // allow "more" link when too many events
                    nowIndicator: true, // Show current time line

                    // US-6 / T031d: pulsar un hueco abre el alta de cita remota.
                    dateClick: function(info) {
                        openRemoteModal(info);
                    },

                    // Time grid options
                    slotDuration: '00:30:00', // Set slot duration to 30 mins for grid lines
                    slotMinTime: '08:00:00', // Optional: Start time for the grid
                    slotMaxTime: '20:00:00', // Optional: End time for the grid
                    // businessHours: { // Optional: Highlight business hours
                    //     daysOfWeek: [ 1, 2, 3, 4, 5 ], // Monday - Friday
                    //     startTime: '08:00',
                    //     endTime: '18:00',
                    // },

                    // Renderizado personalizado de eventos
                    eventContent: function(arg) {
                        let content = document.createElement('div');
                        content.classList.add('fc-event-content-custom');
                        content.style.cursor = 'pointer'; // Add pointer cursor to indicate clickability
                        content.style.width = '100%';
                        content.style.height = '100%';

                        // 1. Título de servicio (primera línea, más grande)
                        let serviceTitle = document.createElement('div');
                        serviceTitle.classList.add('service-title');
                        serviceTitle.innerHTML = arg.event.title; // Ahora el título es el servicio

                        // 2. Horario (segunda línea)
                        let timeText = document.createElement('div');
                        timeText.classList.add('event-time');

                        // Formatear la hora en formato 24h (HH:MM - HH:MM)
                        const start = arg.event.start;
                        const end = arg.event.end;
                        const startTime = start.toLocaleTimeString('es-ES', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        });
                        const endTime = end ? end.toLocaleTimeString('es-ES', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        }) : '';

                        timeText.innerHTML = startTime + (endTime ? ' - ' + endTime : '');

                        // 3. Nombre del cliente (última línea, más pequeña)
                        let clientName = document.createElement('div');
                        clientName.classList.add('client-name');
                        clientName.innerHTML = arg.event.extendedProps.clientName ||
                            'Cliente'; // Usamos clientName de extendedProps

                        // Agregamos todo al contenedor
                        content.appendChild(serviceTitle);
                        content.appendChild(timeText);
                        content.appendChild(clientName);

                        return {
                            domNodes: [content]
                        };
                    },

                    // Event Data Source
                    events: {
                        url: '{{ url('/admin/appointment-calendar/events') }}',
                        failure: function(err) {
                            console.error("Failed to load events:", err);
                        },
                        success: function(events) {
                            console.log("Events loaded successfully:", events);
                        }
                    },
                    eventTimeFormat: { // Format time display on events
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false // Use 24-hour format
                    },

                    // --- Event Handlers ---

                    // Handle event dragging
                    eventDrop: function(info) {
                        const event = info.event;
                        const newStart = event.start.toISOString();
                        const newEnd = event.end ? event.end.toISOString() :
                            null; // End might be null if duration based

                        Swal.fire({
                            title: 'Reagendar Cita',
                            text: `Mover cita de "${event.title}" a ${event.start.toLocaleString('es-ES', {dateStyle: 'short', timeStyle: 'short'})}?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sí, mover!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Send AJAX request to update backend
                                $.ajax({
                                    url: `{{ url('admin/appointment-calendar/events') }}/${event.id}`,
                                    type: 'PATCH',
                                    data: {
                                        start: newStart,
                                        end: newEnd
                                    },
                                    success: function(response) {
                                        Swal.fire('¡Movida!', response.message,
                                            'success');
                                        // Calendar automatically keeps the event in the new position on success
                                    },
                                    error: function(xhr) {
                                        console.error("Error updating event:", xhr
                                            .responseText);
                                        let errorMessage =
                                            'No se pudo actualizar la cita.';
                                        if (xhr.responseJSON && xhr.responseJSON
                                            .message) {
                                            errorMessage +=
                                                ` ${xhr.responseJSON.message}`;
                                        }
                                        Swal.fire('Error', errorMessage, 'error');
                                        info
                                            .revert(); // Revert event to original position on error
                                    }
                                });
                            } else {
                                info.revert(); // Revert if user cancels confirmation
                            }
                        });
                    },

                    // Handle clicking on an event
                    eventClick: function(info) {
                        console.log("Event clicked:", info.event.title);
                        info.jsEvent
                            .preventDefault(); // Prevent browser navigation if the event has a URL

                        try {
                            const props = info.event.extendedProps;
                            console.log("Event props:", props);
                            currentAppointmentId = info.event.id; // Store the current appointment ID
                            console.log("Current appointment ID:", currentAppointmentId);

                            // Populate modal with event data
                            document.getElementById('modalEventTitle').textContent = info.event.title;
                            document.getElementById('modalEventClient').textContent = props
                                .clientName || 'N/A';
                            document.getElementById('modalEventService').textContent = props.service ||
                                'N/A';
                            document.getElementById('modalEventEmail').textContent = props
                                .clientEmail || 'N/A';

                            // Formatear teléfono de España
                            let phoneDisplay = props.clientPhone || 'N/A';
                            if (phoneDisplay && phoneDisplay.startsWith('+34')) {
                                phoneDisplay = formatSpanishPhone(phoneDisplay);
                            }
                            document.getElementById('modalEventPhone').textContent = phoneDisplay;

                            // Formatear el estado como badge con color
                            const statusElement = document.getElementById('modalEventStatus');
                            statusElement.textContent = props.status || 'N/A';

                            // Aplicar estilos al badge según el estado
                            statusElement.className =
                                'px-2 py-1 text-xs font-bold rounded-full text-white';
                            switch (props.status) {
                                case 'Cancelled':
                                    statusElement.classList.add('bg-red-600');
                                    break;
                                case 'Confirmed':
                                    statusElement.classList.add('bg-green-600');
                                    break;
                                case 'Pending':
                                    statusElement.classList.add('bg-blue-600');
                                    break;
                                default:
                                    statusElement.classList.add('bg-gray-600');
                                    break;
                            }

                            document.getElementById('modalEventAddress').textContent = props.address ||
                                'N/A';
                            document.getElementById('modalEventIssue').textContent = props.issue ||
                                'N/A';
                            document.getElementById('modalEventNotes').textContent = props.notes ||
                                'N/A';

                            currentEventProps = props;

                            // Sección remota
                            const isRemote = props.isRemote === true;
                            const addressRow = document.getElementById('modalAddressRow');
                            remoteSection.classList.toggle('hidden', !isRemote);
                            addressRow.classList.toggle('hidden', isRemote);

                            if (isRemote) {
                                const paymentLabels = {
                                    claimed: 'Declarado (sin verificar)',
                                    verified: 'Verificado',
                                    rejected: 'Rechazado',
                                    refund_pending: 'Reembolso pendiente',
                                    unpaid: 'Sin pago',
                                };
                                document.getElementById('modalPaymentStatus').textContent =
                                    paymentLabels[props.paymentStatus] || props.paymentStatus || 'N/A';
                                document.getElementById('modalPaymentReference').textContent =
                                    props.paymentReference || '—';
                                document.getElementById('modalPaymentAmount').textContent =
                                    props.paymentAmount != null
                                        ? `${props.paymentAmount} ${props.paymentCurrency || 'EUR'}`
                                        : '—';
                                document.getElementById('modalPayerName').textContent = props.payerName || '—';

                                const tzRow = document.getElementById('modalClientTimezoneRow');
                                if (props.clientTimezone) {
                                    tzRow.classList.remove('hidden');
                                    document.getElementById('modalClientTimezone').textContent = props.clientTimezone;
                                } else {
                                    tzRow.classList.add('hidden');
                                }

                                const urlRow = document.getElementById('modalMeetingUrlRow');
                                const meetingUrlEl = document.getElementById('modalMeetingUrl');
                                if (props.meetingUrl) {
                                    urlRow.classList.remove('hidden');
                                    meetingUrlEl.href = props.meetingUrl;
                                    meetingUrlEl.textContent = props.meetingUrl;
                                } else {
                                    urlRow.classList.add('hidden');
                                }

                                document.getElementById('modalMeetingFailed').classList.toggle(
                                    'hidden', !(props.meetingLinkFailed && !props.meetingUrl)
                                );

                                document.getElementById('remoteVerifyActions').classList.toggle(
                                    'hidden', !(props.status === 'Pending' && props.paymentStatus === 'claimed')
                                );
                                document.getElementById('remoteLinkActions').classList.toggle(
                                    'hidden', !(props.status === 'Confirmed' && (!props.meetingUrl || props.meetingLinkFailed))
                                );

                                const remoteUrlInput = document.getElementById('remoteMeetingUrlInput');
                                if (remoteUrlInput) remoteUrlInput.value = '';
                                document.getElementById('remoteRejectReason').value = '';
                                const addLinkInput = document.getElementById('addMeetingUrlInput');
                                if (addLinkInput) addLinkInput.value = props.meetingUrl || '';
                            }

                            configureModalActions(props);

                            // Mostrar la foto si está disponible
                            const photoContainer = document.getElementById('photoContainer');
                            const modalEventPhoto = document.getElementById('modalEventPhoto');

                            // Siempre mostrar el contenedor de fotos, independientemente de si hay foto o no
                            photoContainer.classList.remove('hidden');

                            if (props.equipmentPhotoUrl) {
                                modalEventPhoto.src = props.equipmentPhotoUrl;
                                console.log('Loading photo from:', modalEventPhoto.src);

                                // Manejar errores de carga de imagen sin ocultar
                                modalEventPhoto.onerror = function() {
                                    console.error('Error loading equipment photo:', modalEventPhoto
                                        .src);
                                    // Ya no ocultamos el contenedor, solo mostramos un mensaje de error
                                    modalEventPhoto.alt = "Error al cargar la imagen";

                                    // Agregar un texto de error visible
                                    const errorMsg = document.createElement('p');
                                    errorMsg.className = 'text-red-500 text-xs mt-1';
                                    errorMsg.textContent = 'No se pudo cargar la imagen. URL: ' +
                                        modalEventPhoto.src;
                                    modalEventPhoto.parentNode.appendChild(errorMsg);
                                };
                            } else {
                                modalEventPhoto.src = '';
                                modalEventPhoto.alt = "No hay imagen disponible";
                                console.log('No equipment photo available for this appointment');
                            }

                            // Show modal with explicit style update
                            eventDetailModal.classList.remove('hidden');
                            eventDetailModal.style.display = 'block';
                            console.log("Modal should be visible now");
                        } catch (error) {
                            console.error("Error in eventClick handler:", error);
                        }
                    },

                    // --- Tooltips on Hover (using Tippy.js) ---
                    eventMouseEnter: function(info) {
                        tippy(info.el, {
                            content: `<strong>${info.event.title}</strong><br>Status: ${info.event.extendedProps.status}`,
                            allowHTML: true,
                            theme: 'light-border', // Example theme
                            placement: 'top',
                            arrow: true
                        });
                    }

                });

                function configureModalActions(props) {
                    const isRemote = props.isRemote === true;
                    const pendingPayment = props.status === 'Pending' && props.paymentStatus === 'claimed';
                    const needsLink = props.status === 'Confirmed' && (!props.meetingUrl || props.meetingLinkFailed);
                    const canResend = props.status === 'Confirmed' && props.meetingUrl;

                    remotePaymentButtons.classList.toggle('hidden', !isRemote || !pendingPayment);
                    remoteLinkButtons.classList.toggle('hidden', !isRemote || (!needsLink && !canResend));

                    saveMeetingLinkBtn.classList.toggle('hidden', !needsLink);
                    resendConfirmationBtn.classList.toggle('hidden', !canResend);

                    if (props.status === 'New') {
                        statusActionButtons.classList.add('hidden');
                        return;
                    }

                    if (isRemote && pendingPayment) {
                        statusActionButtons.classList.add('hidden');
                        return;
                    }

                    statusActionButtons.classList.remove('hidden');

                    if (props.status === 'Confirmed' || (isRemote && props.paymentStatus !== 'verified')) {
                        confirmAppointmentBtn.disabled = true;
                        confirmAppointmentBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    } else if (!isRemote) {
                        confirmAppointmentBtn.disabled = false;
                        confirmAppointmentBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }

                    if (props.status === 'Cancelled' || (isRemote && pendingPayment)) {
                        declineAppointmentBtn.disabled = true;
                        declineAppointmentBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        declineAppointmentBtn.disabled = false;
                        declineAppointmentBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }

                function closeEventModal() {
                    eventDetailModal.classList.add('hidden');
                    eventDetailModal.style.display = 'none';
                }

                async function patchJson(url, payload) {
                    const res = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        body: JSON.stringify(payload),
                    });
                    const json = await res.json().catch(() => ({}));
                    return { res, json };
                }

                async function postJson(url, payload = {}) {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        body: JSON.stringify(payload),
                    });
                    const json = await res.json().catch(() => ({}));
                    return { res, json };
                }

                verifyPaymentBtn.addEventListener('click', async function () {
                    if (!currentAppointmentId) return;

                    const payload = { decision: 'verify' };
                    const urlInput = document.getElementById('remoteMeetingUrlInput');
                    if (urlInput && urlInput.value) payload.meeting_url = urlInput.value;

                    const result = await Swal.fire({
                        title: 'Confirmar pago',
                        text: '¿El cobro aparece en SumUp? Se confirmará la cita y se enviará el enlace.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, confirmar',
                        cancelButtonText: 'Cancelar',
                    });

                    if (!result.isConfirmed) return;

                    verifyPaymentBtn.disabled = true;
                    const { res, json } = await patchJson(
                        `{{ url('admin/appointments') }}/${currentAppointmentId}/verify-payment`,
                        payload
                    );
                    verifyPaymentBtn.disabled = false;

                    if (res.ok) {
                        Swal.fire('¡Listo!', json.message, 'success');
                        calendar.refetchEvents();
                        closeEventModal();
                    } else {
                        const msg = json.errors ? Object.values(json.errors).flat().join(' ') : (json.message || 'Error');
                        Swal.fire('Error', msg, 'error');
                    }
                });

                rejectPaymentBtn.addEventListener('click', async function () {
                    if (!currentAppointmentId) return;

                    const reason = document.getElementById('remoteRejectReason').value;
                    const result = await Swal.fire({
                        title: 'Rechazar pago',
                        text: '¿Rechazar esta solicitud? Se cancelará la cita y se avisará al cliente.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, rechazar',
                        cancelButtonText: 'Cancelar',
                    });

                    if (!result.isConfirmed) return;

                    rejectPaymentBtn.disabled = true;
                    const { res, json } = await patchJson(
                        `{{ url('admin/appointments') }}/${currentAppointmentId}/verify-payment`,
                        { decision: 'reject', reason: reason || undefined }
                    );
                    rejectPaymentBtn.disabled = false;

                    if (res.ok) {
                        Swal.fire('Rechazada', json.message, 'success');
                        calendar.refetchEvents();
                        closeEventModal();
                    } else {
                        Swal.fire('Error', json.message || 'No se pudo rechazar.', 'error');
                    }
                });

                saveMeetingLinkBtn.addEventListener('click', async function () {
                    if (!currentAppointmentId) return;

                    const meetingUrl = document.getElementById('addMeetingUrlInput').value;
                    if (!meetingUrl) {
                        Swal.fire('Falta el enlace', 'Pega la URL de la videollamada.', 'warning');
                        return;
                    }

                    saveMeetingLinkBtn.disabled = true;
                    const { res, json } = await patchJson(
                        `{{ url('admin/appointments') }}/${currentAppointmentId}/meeting-link`,
                        { meeting_url: meetingUrl, resend_email: true }
                    );
                    saveMeetingLinkBtn.disabled = false;

                    if (res.ok) {
                        Swal.fire('Guardado', json.message, 'success');
                        calendar.refetchEvents();
                        closeEventModal();
                    } else {
                        const msg = json.errors ? Object.values(json.errors).flat().join(' ') : (json.message || 'Error');
                        Swal.fire('Error', msg, 'error');
                    }
                });

                resendConfirmationBtn.addEventListener('click', async function () {
                    if (!currentAppointmentId) return;

                    resendConfirmationBtn.disabled = true;
                    const { res, json } = await postJson(
                        `{{ url('admin/appointments') }}/${currentAppointmentId}/resend-confirmation`
                    );
                    resendConfirmationBtn.disabled = false;

                    if (res.ok) {
                        Swal.fire('Enviado', json.message, 'success');
                    } else {
                        Swal.fire('Error', json.message || 'No se pudo reenviar.', 'error');
                    }
                });

                console.log("Rendering calendar...");
                try {
                    calendar.render();
                    console.log("Calendar rendered successfully!");
                } catch (err) {
                    console.error("Error rendering calendar:", err);
                }

                // Close modal button with explicit style update
                closeEventModalBtn.addEventListener('click', () => {
                    eventDetailModal.classList.add('hidden');
                    eventDetailModal.style.display = 'none';
                    console.log("Modal closed by button");
                });

                // Close modal on clicking outside with explicit style update
                eventDetailModal.addEventListener('click', function(event) {
                    if (event.target === eventDetailModal) { // Check if click is on the backdrop
                        eventDetailModal.classList.add('hidden');
                        eventDetailModal.style.display = 'none';
                        console.log("Modal closed by clicking outside");
                    }
                });

                // Handle confirm appointment button
                confirmAppointmentBtn.addEventListener('click', function() {
                    if (!currentAppointmentId) return;

                    Swal.fire({
                        title: 'Confirmar Cita',
                        text: '¿Estás seguro de que quieres confirmar esta cita? Se enviará un correo de confirmación al cliente.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, confirmar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Mostrar estado de procesamiento
                            const btnText = confirmAppointmentBtn.querySelector('.normal-btn-text');
                            const processingText = confirmAppointmentBtn.querySelector(
                                '.processing-btn-text');
                            btnText.classList.add('hidden');
                            processingText.classList.remove('hidden');
                            confirmAppointmentBtn.disabled = true;
                            confirmAppointmentBtn.classList.add('opacity-70', 'cursor-not-allowed');

                            // Send AJAX request to update appointment status
                            $.ajax({
                                url: `{{ url('admin/appointment-calendar/status') }}/${currentAppointmentId}`,
                                type: 'PATCH',
                                data: {
                                    status: 'Confirmed'
                                },
                                success: function(response) {
                                    // Restaurar estado del botón
                                    btnText.classList.remove('hidden');
                                    processingText.classList.add('hidden');
                                    confirmAppointmentBtn.disabled = false;
                                    confirmAppointmentBtn.classList.remove('opacity-70',
                                        'cursor-not-allowed');

                                    Swal.fire('¡Confirmada!', response.message,
                                        'success');
                                    calendar.refetchEvents(); // Refresh calendar events
                                    eventDetailModal.classList.add(
                                        'hidden'); // Close modal
                                    eventDetailModal.style.display = 'none';
                                },
                                error: function(xhr) {
                                    // Restaurar estado del botón
                                    btnText.classList.remove('hidden');
                                    processingText.classList.add('hidden');
                                    confirmAppointmentBtn.disabled = false;
                                    confirmAppointmentBtn.classList.remove('opacity-70',
                                        'cursor-not-allowed');

                                    console.error("Error updating appointment status:",
                                        xhr.responseText);
                                    let errorMessage = 'No se pudo confirmar la cita.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage += ` ${xhr.responseJSON.message}`;
                                    }
                                    Swal.fire('Error', errorMessage, 'error');
                                }
                            });
                        }
                    });
                });

                // Handle decline appointment button
                declineAppointmentBtn.addEventListener('click', function() {
                    if (!currentAppointmentId) return;

                    Swal.fire({
                        title: 'Rechazar Cita',
                        text: '¿Estás seguro de que quieres rechazar esta cita? Se enviará un correo de rechazo al cliente.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, rechazar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Mostrar estado de procesamiento
                            const btnText = declineAppointmentBtn.querySelector('.normal-btn-text');
                            const processingText = declineAppointmentBtn.querySelector(
                                '.processing-btn-text');
                            btnText.classList.add('hidden');
                            processingText.classList.remove('hidden');
                            declineAppointmentBtn.disabled = true;
                            declineAppointmentBtn.classList.add('opacity-70', 'cursor-not-allowed');

                            // Send AJAX request to update appointment status
                            $.ajax({
                                url: `{{ url('admin/appointment-calendar/status') }}/${currentAppointmentId}`,
                                type: 'PATCH',
                                data: {
                                    status: 'Cancelled'
                                },
                                success: function(response) {
                                    // Restaurar estado del botón
                                    btnText.classList.remove('hidden');
                                    processingText.classList.add('hidden');
                                    declineAppointmentBtn.disabled = false;
                                    declineAppointmentBtn.classList.remove('opacity-70',
                                        'cursor-not-allowed');

                                    Swal.fire('¡Rechazada!', response.message,
                                        'success');
                                    calendar.refetchEvents(); // Refresh calendar events
                                    eventDetailModal.classList.add(
                                        'hidden'); // Close modal
                                    eventDetailModal.style.display = 'none';
                                },
                                error: function(xhr) {
                                    // Restaurar estado del botón
                                    btnText.classList.remove('hidden');
                                    processingText.classList.add('hidden');
                                    declineAppointmentBtn.disabled = false;
                                    declineAppointmentBtn.classList.remove('opacity-70',
                                        'cursor-not-allowed');

                                    console.error("Error updating appointment status:",
                                        xhr.responseText);
                                    let errorMessage = 'No se pudo rechazar la cita.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage += ` ${xhr.responseJSON.message}`;
                                    }
                                    Swal.fire('Error', errorMessage, 'error');
                                }
                            });
                        }
                    });
                });
            } catch (err) {
                console.error("Error creating calendar:", err);
            }
        });
    </script>
@endpush
