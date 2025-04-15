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
                                        <button type="button" id="closeEventModalBtn"
                                            class="absolute top-2 right-2 rounded-full bg-red-600 p-2 inline-flex items-center justify-center text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span class="sr-only">Cerrar</span>
                                        </button>
                                        <div class="sm:flex sm:items-start">
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                                    id="modalEventTitle"></h3>
                                                <div class="mt-2 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <p><strong>Cliente:</strong> <span id="modalEventClient"></span></p>
                                                    <p><strong>Email:</strong> <span id="modalEventEmail"></span></p>
                                                    <p><strong>Teléfono:</strong> <span id="modalEventPhone"></span></p>
                                                    <p><strong>Servicio:</strong> <span id="modalEventService"></span></p>
                                                    <p><strong>Estado:</strong> <span id="modalEventStatus"
                                                            class="px-2 py-1 text-xs font-bold rounded-full"></span></p>
                                                    <p><strong>Dirección:</strong> <span id="modalEventAddress"
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
                                        class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row justify-center">
                                        {{-- Add status change buttons for non-new appointments --}}
                                        <div id="statusActionButtons" class="flex space-x-4">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- End Modal --}}

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
            let currentAppointmentId = null;

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
                    selectable: false, // You might enable this later to *create* new appointments by clicking/selecting
                    dayMaxEvents: true, // allow "more" link when too many events
                    nowIndicator: true, // Show current time line

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

                            // Mostrar la foto si está disponible
                            const photoContainer = document.getElementById('photoContainer');
                            const modalEventPhoto = document.getElementById('modalEventPhoto');

                            // Siempre mostrar el contenedor de fotos, independientemente de si hay foto o no
                            photoContainer.classList.remove('hidden');

                            if (props.equipmentPhotoPath) {
                                // Usar una URL absoluta para la imagen con la ruta correcta
                                modalEventPhoto.src = '{{ url('/') }}' + '/storage/app/public/' +
                                    props.equipmentPhotoPath;
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

                            // Show/hide status change buttons based on current status
                            if (props.status === 'New') {
                                statusActionButtons.classList.add('hidden');
                            } else {
                                statusActionButtons.classList.remove('hidden');

                                // Disable buttons based on current status
                                if (props.status === 'Confirmed') {
                                    confirmAppointmentBtn.disabled = true;
                                    confirmAppointmentBtn.classList.add('opacity-50',
                                        'cursor-not-allowed');
                                } else {
                                    confirmAppointmentBtn.disabled = false;
                                    confirmAppointmentBtn.classList.remove('opacity-50',
                                        'cursor-not-allowed');
                                }

                                if (props.status === 'Cancelled') {
                                    declineAppointmentBtn.disabled = true;
                                    declineAppointmentBtn.classList.add('opacity-50',
                                        'cursor-not-allowed');
                                } else {
                                    declineAppointmentBtn.disabled = false;
                                    declineAppointmentBtn.classList.remove('opacity-50',
                                        'cursor-not-allowed');
                                }
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
