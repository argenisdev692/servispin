@extends('layouts.app')

{{-- Add Flatpickr CSS & intl-tel-input CSS --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
    <style>
        .iti {
            width: 100%;
            display: block;
        }

        .iti__flag-container {
            z-index: 10;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        /* Review Step Styling */
        .review-section {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .review-section h3 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            color: #4b5563;
        }

        .review-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .review-item span:first-child {
            font-weight: 500;
            color: #374151;
            margin-right: 1rem;
        }

        .review-item span:last-child {
            text-align: right;
        }

        /* No aplicar color gris si ya tiene clase text-orange-600 */
        .review-item span:last-child:not(.text-green-600) {
            color: #6b7280;
        }

        /* Added for red asterisks */
        .required-asterisk {
            color: red;
            margin-left: 2px;
            /* Optional: add a little space */
        }
    </style>
@endpush

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Logo --}}
                    <div class="flex justify-center mb-4">
                        <x-application-mark class="block h-12 w-auto" />
                    </div>

                    <h1 class="text-3xl font-semibold mb-4 text-center text-gray-800">Agendar una cita</h1>
                    <p class="text-center text-gray-600 text-sm mb-4">Zona horaria: Islas Canarias (GMT+01:00)</p>

                    {{-- Wizard Container --}}
                    <div id="booking-form-container">
                        <!-- Progress Bar -->
                        <div class="mb-8">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span class="w-1/3 text-center">Servicio y Hora</span>
                                <span class="w-1/3 text-center">Datos y Detalles</span>
                                <span class="w-1/3 text-center">Revisión</span>
                            </div>
                            <div class="relative pt-1">
                                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                                    <div id="progress-bar" style="width: 33.33%"
                                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alerts -->
                        <div id="alert-success" class="hidden mb-4 p-4 bg-green-100 text-green-700 rounded"></div>
                        <div id="alert-error" class="hidden mb-4 p-4 bg-red-100 text-red-700 rounded"></div>
                        <div id="alert-validation" class="hidden mb-4 p-4 bg-yellow-100 text-yellow-700 rounded"></div>

                        <form id="booking-form" enctype="multipart/form-data">
                            @csrf

                            {{-- Step 1: Service & Time --}}
                            <div id="step-1" class="form-step active space-y-6">
                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Paso 1: Seleccione Servicio y
                                    Fecha/Hora</h2>
                                {{-- Service Selection --}}
                                <div>
                                    <label for="service_id" class="block text-sm font-medium text-gray-700">Servicio
                                        <span class="required-asterisk">*</span></label>
                                    <select id="service_id" name="service_id" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Seleccione un servicio</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}" data-duration="{{ $service->duration }}">
                                                {{ $service->name }}
                                                @if ($service->price > 0)
                                                    ({{ $service->duration }} minutos -
                                                    €{{ number_format($service->price, 2) }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Date and Time Selection --}}
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="appointment_date" class="block text-sm font-medium text-gray-700">Fecha
                                            <span class="required-asterisk">*</span></label>
                                        <input type="text" id="appointment_date" name="appointment_date" required
                                            placeholder="Seleccione una fecha"
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="time_slots" class="block text-sm font-medium text-gray-700">Hora
                                            disponible <span class="required-asterisk">*</span></label>
                                        <select id="time_slots" name="start_time" required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            disabled>
                                            <option value="">Seleccione servicio y fecha</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- Navigation --}}
                                <div class="flex justify-end mt-6">
                                    <button type="button" id="next-step-1"
                                        class="px-6 py-2 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 inline-flex items-center">
                                        Siguiente <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Step 2: Client & Equipment Details --}}
                            <div id="step-2" class="form-step space-y-6">
                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Paso 2: Sus Datos y Detalles del Equipo
                                </h2>
                                {{-- Client Info Section --}}
                                <fieldset class="border border-gray-300 p-4 rounded-md">
                                    <legend class="text-lg font-medium text-gray-900 px-2">Datos de Contacto</legend>
                                    {{-- First Name / Last Name --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                        <div>
                                            <label for="client_first_name"
                                                class="block text-sm font-medium text-gray-700">Nombre <span
                                                    class="required-asterisk">*</span></label>
                                            <input type="text" id="client_first_name" name="client_first_name" required
                                                placeholder="Primer Nombre"
                                                class="capitalize mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="client_last_name"
                                                class="block text-sm font-medium text-gray-700">Apellido <span
                                                    class="required-asterisk">*</span></label>
                                            <input type="text" id="client_last_name" name="client_last_name" required
                                                placeholder="Primer Apellido"
                                                class="capitalize mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    {{-- Email / Phone --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                        <div>
                                            <label for="client_email" class="block text-sm font-medium text-gray-700">Correo
                                                electrónico <span class="required-asterisk">*</span></label>
                                            <input type="email" id="client_email" name="client_email" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="client_phone"
                                                class="block text-sm font-medium text-gray-700">Teléfono
                                                <span class="required-asterisk">*</span></label>
                                            <input type="tel" id="client_phone" name="client_phone" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- Equipment Info Section --}}
                                <fieldset class="border border-gray-300 p-4 rounded-md">
                                    <legend class="text-lg font-medium text-gray-900 px-2">Detalles del Equipo y Dirección
                                    </legend>
                                    <div class="grid grid-cols-1 gap-6 mt-4">
                                        <div>
                                            <label for="brand_id" class="block text-sm font-medium text-gray-700">Marca
                                                del
                                                Equipo <span class="required-asterisk">*</span></label>
                                            <select id="brand_id" name="brand_id" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Seleccione una marca</option>
                                                @foreach ($brands as $brand)
                                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="issue_description"
                                                class="block text-sm font-medium text-gray-700">Explique brevemente que
                                                falla o ruido presenta su equipo <span
                                                    class="required-asterisk">*</span></label>
                                            <textarea id="issue_description" name="issue_description" rows="3" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                        </div>
                                        <div>
                                            <label for="address"
                                                class="block text-sm font-medium text-gray-700">Dirección Exacta <span
                                                    class="required-asterisk">*</span></label>
                                            <textarea id="address" name="address" rows="2" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                        </div>
                                        <div>
                                            <label for="equipment_photo"
                                                class="block text-sm font-medium text-gray-700">Foto del equipo
                                                (Opcional)</label>
                                            <input type="file" id="equipment_photo" name="equipment_photo"
                                                accept="image/*"
                                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                            <p class="mt-1 text-xs text-gray-500">Tamaño máximo: 10MB</p>
                                            <p id="photo-size-error" class="text-red-500 text-xs mt-1 hidden">El archivo
                                                excede el límite de 10MB.</p>
                                            <img id="photo-preview-step2" src="#" alt="Vista previa"
                                                class="mt-2 max-h-40 hidden" />
                                        </div>
                                        <div>
                                            <label for="notes" class="block text-sm font-medium text-gray-700">Notas o
                                                comentarios adicionales (Opcional)</label>
                                            <textarea id="notes" name="notes" rows="3"
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- Navigation --}}
                                <div class="flex justify-between mt-6">
                                    <button type="button" id="prev-step-2"
                                        class="px-6 py-2 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 inline-flex items-center">
                                        <i class="fas fa-arrow-left mr-2"></i> Anterior
                                    </button>
                                    <button type="button" id="next-step-2"
                                        class="px-6 py-2 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 inline-flex items-center">
                                        Revisar <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Step 3: Review & Confirm --}}
                            <div id="step-3" class="form-step space-y-6">
                                <h2 class="text-xl font-semibold text-gray-700 mb-4">Paso 3: Revisión y Confirmación</h2>
                                <p class="text-sm text-gray-600 mb-4">Por favor, revise la información de su cita antes de
                                    confirmar.</p>

                                {{-- Review Section 1: Service & Time --}}
                                <div class="review-section">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-lg">Servicio y Hora</h3>
                                        <button type="button" class="edit-step text-sm text-blue-600 hover:underline"
                                            data-step="0"><i class="fas fa-edit mr-1"></i> Editar</button>
                                    </div>
                                    <div class="review-item"><span>Servicio:</span> <span id="review_service"></span>
                                    </div>
                                    <div class="review-item"><span>Fecha:</span> <span id="review_date"></span></div>
                                    <div class="review-item"><span>Hora:</span> <span id="review_time"></span></div>
                                </div>

                                {{-- Review Section 2: Contact & Equipment --}}
                                <div class="review-section">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-lg">Sus Datos y Detalles del Equipo</h3>
                                        <button type="button" class="edit-step text-sm text-blue-600 hover:underline"
                                            data-step="1"><i class="fas fa-edit mr-1"></i> Editar</button>
                                    </div>
                                    <div class="review-item"><span>Nombre:</span> <span id="review_client_name"></span>
                                    </div>
                                    <div class="review-item"><span>Email:</span> <span id="review_client_email"></span>
                                    </div>
                                    <div class="review-item"><span>Teléfono:</span> <span id="review_client_phone"></span>
                                    </div>
                                    <div class="review-item"><span>Marca Equipo:</span> <span
                                            id="review_equipment_brand"></span></div>
                                    <div class="review-item"><span>Fallo/Ruido:</span> <span id="review_issue_description"
                                            class="whitespace-pre-wrap"></span></div>
                                    <div class="review-item"><span>Dirección:</span> <span id="review_address"
                                            class="whitespace-pre-wrap"></span></div>
                                    <div class="review-item">
                                        <span>Foto Adjunta:</span>
                                        <span id="review_equipment_photo_container">
                                            <img id="photo-preview-step3" src="#" alt="Vista previa"
                                                class="mt-1 max-h-20 hidden" />
                                            <span id="review_equipment_photo_text">No adjuntada</span>
                                        </span>
                                    </div>
                                    <div class="review-item"><span>Notas Adicionales:</span> <span id="review_notes"
                                            class="whitespace-pre-wrap"></span></div>
                                </div>

                                {{-- Navigation / Submit --}}
                                <div class="flex justify-between mt-8">
                                    <button type="button" id="prev-step-3"
                                        class="px-6 py-2 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 inline-flex items-center">
                                        <i class="fas fa-arrow-left mr-2"></i> Anterior
                                    </button>
                                    <button type="submit" id="submit-booking"
                                        class="px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 inline-flex items-center justify-center">
                                        <span class="button-text">Confirmar Cita</span> <i
                                            class="fas fa-check ml-2 button-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- JS Libraries --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form & Step Elements
            const bookingForm = document.getElementById('booking-form');
            const steps = Array.from(document.querySelectorAll('.form-step'));
            const progressBar = document.getElementById('progress-bar');
            let currentStep = 0;

            // Alert Elements
            const alertSuccess = document.getElementById('alert-success');
            const alertError = document.getElementById('alert-error');
            const alertValidation = document.getElementById('alert-validation');

            // Input Elements 
            const serviceSelect = document.getElementById('service_id');
            const dateInput = document.getElementById('appointment_date');
            const timeSelect = document.getElementById('time_slots');
            const clientFirstNameInput = document.getElementById('client_first_name');
            const clientLastNameInput = document.getElementById('client_last_name');
            const clientEmailInput = document.getElementById('client_email');
            const clientPhoneInput = document.getElementById('client_phone');
            const brandSelect = document.getElementById('brand_id');
            const issueDescriptionInput = document.getElementById('issue_description');
            const addressInput = document.getElementById('address');
            const equipmentPhotoInput = document.getElementById('equipment_photo');
            const notesInput = document.getElementById('notes');
            const photoSizeError = document.getElementById('photo-size-error');
            const photoPreviewStep2 = document.getElementById('photo-preview-step2');

            // Review Span Elements
            const reviewServiceName = document.getElementById('review_service');
            const reviewDate = document.getElementById('review_date');
            const reviewTime = document.getElementById('review_time');
            const reviewClientName = document.getElementById('review_client_name');
            const reviewClientEmail = document.getElementById('review_client_email');
            const reviewClientPhone = document.getElementById('review_client_phone');
            const reviewEquipmentBrand = document.getElementById('review_equipment_brand');
            const reviewIssueDescription = document.getElementById('review_issue_description');
            const reviewAddress = document.getElementById('review_address');
            const reviewEquipmentPhoto = document.getElementById('review_equipment_photo');
            const reviewNotes = document.getElementById('review_notes');
            const photoPreviewStep3 = document.getElementById('photo-preview-step3');
            const reviewEquipmentPhotoText = document.getElementById('review_equipment_photo_text');

            // --- Library Initializations ---
            const fp = flatpickr(dateInput, {
                locale: "es",
                dateFormat: "d/m/Y",
                altInput: true,
                altFormat: "F j, Y",
                minDate: new Date().fp_incr(1),
                onChange: function() {
                    checkDateAndServiceAvailability();
                }
            });

            let iti = null;
            if (clientPhoneInput) {
                iti = window.intlTelInput(clientPhoneInput, {
                    initialCountry: "es",
                    preferredCountries: ["es"],
                    separateDialCode: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
                    autoPlaceholder: "aggressive"
                });

                // Agregar validación de longitud y formato para números españoles
                clientPhoneInput.addEventListener('input', function(e) {
                    // Detectar si estamos trabajando con un número español
                    const isSpanish = iti.getSelectedCountryData().iso2 === 'es';

                    // Guardar posición del cursor antes de la modificación
                    const cursorPos = this.selectionStart;
                    // Número de espacios antes del cursor
                    const spacesBefore = (this.value.substring(0, cursorPos).match(/ /g) || []).length;

                    // Obtener solo los dígitos del valor actual
                    const digits = this.value.replace(/\D/g, '');

                    // Si es España (+34), limitar a máximo 9 dígitos (sin contar código país)
                    if (isSpanish) {
                        // Formateo automático XXX XX XX XX para números españoles
                        let formattedValue = '';
                        for (let i = 0; i < Math.min(digits.length, 9); i++) {
                            // Agregar espacio después de la posición 3, 5 y 7
                            if (i === 3 || i === 5 || i === 7) {
                                formattedValue += ' ';
                            }
                            formattedValue += digits[i];
                        }

                        // Actualizar el valor solo si ha cambiado
                        if (this.value !== formattedValue) {
                            this.value = formattedValue;

                            // Calcular espacios después del formateo
                            const spacesAfter = (formattedValue.substring(0, cursorPos).match(/ /g) || [])
                                .length;
                            // Ajustar la posición del cursor teniendo en cuenta espacios agregados
                            const newCursorPos = cursorPos + (spacesAfter - spacesBefore);

                            // Restablecer la posición del cursor
                            this.setSelectionRange(newCursorPos, newCursorPos);
                        }
                    } else {
                        // Para otros países, limitar a un máximo razonable (15 dígitos es el estándar internacional)
                        if (digits.length > 15) {
                            this.value = this.value.substring(0, this.value.length - 1);
                        }
                    }
                });

                // Validar número al cambiar de país
                clientPhoneInput.addEventListener('countrychange', function() {
                    // Obtener solo los dígitos del valor actual
                    const digits = this.value.replace(/\D/g, '');

                    // Si el nuevo país es España, aplicar formato español
                    if (iti.getSelectedCountryData().iso2 === 'es') {
                        if (digits.length > 9) {
                            // Truncar a 9 dígitos
                            const truncatedDigits = digits.substring(0, 9);

                            // Aplicar formato XXX XX XX XX
                            let formattedValue = '';
                            for (let i = 0; i < truncatedDigits.length; i++) {
                                if (i === 3 || i === 5 || i === 7) {
                                    formattedValue += ' ';
                                }
                                formattedValue += truncatedDigits[i];
                            }

                            this.value = formattedValue;
                        } else if (digits.length > 0) {
                            // Si ya tiene dígitos pero menos de 9, formatear lo que hay
                            let formattedValue = '';
                            for (let i = 0; i < digits.length; i++) {
                                if (i === 3 || i === 5 || i === 7) {
                                    formattedValue += ' ';
                                }
                                formattedValue += digits[i];
                            }

                            this.value = formattedValue;
                        }
                    }
                });
            }

            // --- Prevent spaces in name fields ---
            function preventSpaces(inputElement) {
                inputElement.addEventListener('input', function() {
                    this.value = this.value.replace(/\s+/g, '');
                    // Optionally, trigger validation after removing space
                    // validateField(this);
                });
            }
            preventSpaces(clientFirstNameInput);
            preventSpaces(clientLastNameInput);
            // -------------------------------------

            // --- Wizard Navigation ---            
            function showStep(stepIndex) {
                // Ensure index is within bounds
                stepIndex = Math.max(0, Math.min(stepIndex, steps.length - 1));

                steps.forEach((step, index) => step.classList.toggle('active', index === stepIndex));
                const progress = ((stepIndex + 1) / steps.length) * 100;
                progressBar.style.width = `${Math.min(progress, 100)}%`; // Cap progress at 100%
                currentStep = stepIndex;
                document.getElementById('booking-form-container').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                hideAlert(alertValidation);

                // Populate review step if navigating to it
                if (stepIndex === 2) { // Step 3 is index 2
                    populateReviewStep();
                }
            }

            function nextStep() {
                if (validateStep(currentStep)) {
                    if (currentStep < steps.length - 1) {
                        showStep(currentStep + 1);
                    }
                }
            }

            function prevStep() {
                if (currentStep > 0) {
                    showStep(currentStep - 1);
                }
            }

            // Navigation Button Listeners
            document.getElementById('next-step-1').addEventListener('click', nextStep);
            document.getElementById('prev-step-2').addEventListener('click', prevStep);
            document.getElementById('next-step-2').addEventListener('click', nextStep);
            document.getElementById('prev-step-3').addEventListener('click', prevStep);

            // Edit Button Listeners
            document.querySelectorAll('.edit-step').forEach(button => {
                button.addEventListener('click', function() {
                    const stepToGo = parseInt(this.getAttribute('data-step'), 10);
                    showStep(stepToGo);
                });
            });

            // --- Step Validation --- 
            function validateStep(stepIndex) {
                hideAlert(alertValidation);
                let isValid = true;
                const stepElement = steps[stepIndex];
                // Validate only inputs within the *current* step that are required
                const inputsToValidate = stepElement.querySelectorAll(
                    'input[required], select[required], textarea[required]');

                inputsToValidate.forEach(input => {
                    let fieldValid = true;
                    // Clear previous error state
                    input.classList.remove('border-red-500');
                    const label = bookingForm.querySelector(`label[for="${input.id}"]`);
                    if (label) label.classList.remove('text-red-500');

                    // Perform validation
                    if (input.tagName === 'SELECT') {
                        if (!input.value) fieldValid = false;
                    } else if (input.id === 'client_phone') {
                        // Phone requires value AND validity check
                        if (!input.value.trim() || (iti && !iti.isValidNumber())) {
                            fieldValid = false;
                        }
                    } else { // General text, email, textarea, etc.
                        if (!input.value.trim()) fieldValid = false;
                    }

                    if (!fieldValid) {
                        isValid = false;
                        input.classList.add('border-red-500');
                        if (label) label.classList.add('text-red-500');
                    }
                });

                if (!isValid) {
                    showAlert(alertValidation, 'Por favor, complete todos los campos requeridos (*) en este paso.');
                }
                return isValid;
            }

            // Remove red border on input/change
            steps.forEach(step => {
                step.querySelectorAll('input[required], select[required], textarea[required]').forEach(
                    input => {
                        input.addEventListener('input', () => {
                            if (input.value.trim()) {
                                input.classList.remove('border-red-500');
                                const label = bookingForm.querySelector(
                                    `label[for="${input.id}"]`);
                                if (label) label.classList.remove('text-red-500');
                            }
                            // Specific check for phone validity on input
                            if (input.id === 'client_phone' && iti && iti.isValidNumber()) {
                                input.classList.remove('border-red-500');
                                const label = bookingForm.querySelector(
                                    `label[for="${input.id}"]`);
                                if (label) label.classList.remove('text-red-500');
                            }
                            hideAlert(alertValidation);
                        });
                        input.addEventListener('change', () => { // For selects
                            if (input.value.trim()) {
                                input.classList.remove('border-red-500');
                                const label = bookingForm.querySelector(
                                    `label[for="${input.id}"]`);
                                if (label) label.classList.remove('text-red-500');
                            }
                            hideAlert(alertValidation);
                        });
                    });
            });

            // --- Populate Review Step ---
            function populateReviewStep() {
                // Service & Time
                reviewServiceName.textContent = serviceSelect.options[serviceSelect.selectedIndex]?.text.split(
                    ' (')[0] || 'N/A';
                reviewServiceName.classList.add('font-semibold', 'text-green-600');
                if (fp.selectedDates.length > 0) {
                    reviewDate.textContent = fp.formatDate(fp.selectedDates[0], "F j, Y");
                    reviewDate.classList.add('font-semibold', 'text-green-600');
                } else {
                    reviewDate.textContent = 'N/A';
                }
                const selectedTimeValue = timeSelect.value; // Value is YYYY-MM-DD HH:MM:SS
                if (selectedTimeValue) {
                    try {
                        const timeString = selectedTimeValue.split(' ')[1]; // Extract HH:MM:SS
                        const [hours, minutes] = timeString.split(':');

                        // Convert to 12-hour format with AM/PM
                        const hour = parseInt(hours, 10);
                        const ampm = hour >= 12 ? 'PM' : 'AM';
                        const hour12 = hour % 12 || 12; // Convert 0 to 12 for 12 AM
                        const formattedTime = `${hour12}:${minutes} ${ampm}`;

                        // Add font-semibold class to reviewTime
                        reviewTime.textContent = formattedTime;
                        reviewTime.classList.add('font-semibold', 'text-green-600');
                    } catch (e) {
                        console.error("Error formatting time:", e);
                        reviewTime.textContent = timeSelect.options[timeSelect.selectedIndex]?.text || 'Error';
                    }
                } else {
                    reviewTime.textContent = 'N/A';
                }

                // Client & Equipment
                const firstName = clientFirstNameInput.value.trim();
                const lastName = clientLastNameInput.value.trim();
                const formattedFirstName = firstName ? firstName.charAt(0).toUpperCase() + firstName.slice(1)
                    .toLowerCase() : '';
                const formattedLastName = lastName ? lastName.charAt(0).toUpperCase() + lastName.slice(1)
                    .toLowerCase() : '';
                reviewClientName.textContent = (formattedFirstName + ' ' + formattedLastName).trim() || 'N/A';
                reviewClientName.classList.add('font-semibold', 'text-green-600');

                reviewClientEmail.textContent = clientEmailInput.value || 'N/A';
                reviewClientEmail.classList.add('font-semibold', 'text-green-600');

                // Formato de teléfono español
                if (iti && iti.isValidNumber()) {
                    // Obtener número con formato español
                    const phoneNumber = iti.getNumber();

                    // Si es un número español, aplicar formato específico
                    if (phoneNumber.startsWith('+34')) {
                        // Quitar todos los no dígitos y luego el prefijo +34
                        const cleaned = phoneNumber.replace(/\D/g, '');
                        // Asegurar que estamos procesando el número sin el código de país
                        const numberWithoutCode = cleaned.startsWith('34') ? cleaned.substring(2) : cleaned;

                        // Aplicar formato +34 XXX XX XX XX
                        if (numberWithoutCode.length === 9) {
                            const formatted =
                                `+34 ${numberWithoutCode.substring(0, 3)} ${numberWithoutCode.substring(3, 5)} ${numberWithoutCode.substring(5, 7)} ${numberWithoutCode.substring(7, 9)}`;
                            reviewClientPhone.textContent = formatted;
                        } else {
                            // Si no tiene 9 dígitos, mostrar con formato simple
                            reviewClientPhone.textContent = phoneNumber;
                        }
                    } else {
                        reviewClientPhone.textContent = phoneNumber;
                    }
                } else {
                    reviewClientPhone.textContent = clientPhoneInput.value || 'N/A';
                }
                reviewClientPhone.classList.add('font-semibold', 'text-green-600');

                reviewEquipmentBrand.textContent = brandSelect.options[brandSelect.selectedIndex]?.text || 'N/A';
                reviewEquipmentBrand.classList.add('font-semibold', 'text-green-600');

                reviewIssueDescription.textContent = issueDescriptionInput.value || 'N/A';
                reviewIssueDescription.classList.add('font-semibold', 'text-green-600');

                reviewAddress.textContent = addressInput.value || 'N/A';
                reviewAddress.classList.add('font-semibold', 'text-green-600');

                reviewNotes.textContent = notesInput.value || 'Ninguna';
                reviewNotes.classList.add('font-semibold', 'text-green-600');

                // Update photo preview in review step
                if (equipmentPhotoInput.files && equipmentPhotoInput.files.length > 0) {
                    // Get the file name for display
                    const fileName = equipmentPhotoInput.files[0].name;
                    reviewEquipmentPhotoText.textContent = fileName || 'Archivo adjunto';
                    reviewEquipmentPhotoText.classList.add('font-semibold', 'text-green-600');

                    // Get the stored image source from the data attribute
                    const imageData = photoPreviewStep2.getAttribute('data-image-src');

                    if (imageData) {
                        photoPreviewStep3.src = imageData;
                        photoPreviewStep3.classList.remove('hidden');
                        reviewEquipmentPhotoText.classList.remove('hidden');
                    } else {
                        // If no data attribute but we have a file, try reading it again
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            photoPreviewStep3.src = e.target.result;
                            photoPreviewStep3.classList.remove('hidden');
                        }
                        reader.readAsDataURL(equipmentPhotoInput.files[0]);
                    }
                } else {
                    photoPreviewStep3.src = '#';
                    photoPreviewStep3.classList.add('hidden');
                    reviewEquipmentPhotoText.textContent = 'No adjuntada';
                    reviewEquipmentPhotoText.classList.add('font-semibold', 'text-green-600');
                    reviewEquipmentPhotoText.classList.remove('hidden');
                }
            }

            // --- Availability Check --- 
            function checkDateAndServiceAvailability() {
                const selectedDate = fp.selectedDates.length > 0 ? fp.formatDate(fp.selectedDates[0], "Y-m-d") :
                    null;
                if (serviceSelect.value && selectedDate) {
                    loadAvailableTimeSlots(selectedDate);
                    timeSelect.disabled = false;
                } else {
                    timeSelect.disabled = true;
                    timeSelect.innerHTML = '<option value="">Seleccione servicio y fecha</option>';
                }
            }
            serviceSelect.addEventListener('change', checkDateAndServiceAvailability);

            function loadAvailableTimeSlots(date) {
                const serviceId = serviceSelect.value;
                timeSelect.innerHTML = '<option value="">Cargando...</option>';
                fetch(`/appointments/availability/slots`, {
                        /* ... fetch options ... */
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            service_id: serviceId,
                            date: date
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        timeSelect.innerHTML = '';
                        if (data.success && data.data.length > 0) {
                            timeSelect.innerHTML = '<option value="">Seleccione un horario</option>';
                            data.data.forEach(slot => {
                                const option = document.createElement('option');
                                option.value = slot.time;
                                option.textContent = slot.formatted_time;
                                timeSelect.appendChild(option);
                            });
                        } else {
                            const message = data.message || 'No hay horarios disponibles';
                            timeSelect.innerHTML = `<option value="">${message}</option>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading time slots:', error);
                        timeSelect.innerHTML = '<option value="">Error al cargar</option>';
                        showAlert(alertError, 'No se pudieron cargar los horarios disponibles.');
                    });
            }

            // --- Alert Handling --- 
            function showAlert(alertElement, message) {
                alertElement.textContent = message;
                alertElement.classList.remove('hidden');
            }

            function hideAlert(alertElement) {
                alertElement.classList.add('hidden');
                alertElement.textContent = '';
            }

            // --- Form Submission --- 
            bookingForm.addEventListener('submit', function(e) {
                e.preventDefault();
                hideAlert(alertSuccess);
                hideAlert(alertError);
                hideAlert(alertValidation);

                // No need for final validation check here, it happens before navigating to review step
                // if (!validateStep(currentStep)) { return; }

                const submitButton = document.getElementById('submit-booking');
                const originalButtonContent = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...`;

                const formData = new FormData(bookingForm);

                // Ensure phone number and start time are correctly set on FormData
                if (iti && iti.isValidNumber()) formData.set('client_phone', iti.getNumber());
                else formData.delete('client_phone'); // Remove if invalid or empty

                if (timeSelect.value) formData.set('start_time', timeSelect.value);
                else {
                    /* Should not happen if validation works */
                    formData.delete('start_time');
                }

                formData.delete('appointment_date'); // Remove display date

                console.log('Datos FormData a enviar:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }

                fetch('/appointments/store', {
                        /* ... fetch options ... */
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        /* ... response handling ... */
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            return response.json().then(data => ({
                                ok: response.ok,
                                status: response.status,
                                body: data
                            }));
                        } else {
                            return response.text().then(text => {
                                throw new Error('Server response was not JSON: ' + text);
                            });
                        }
                    })
                    .then(({
                        ok,
                        status,
                        body
                    }) => {
                        /* ... success/error handling ... */
                        if (ok) {
                            showAlert(alertSuccess, body.message ||
                                'Cita creada correctamente. Será contactado para confirmar la cita y/o solicitar más detalles.'
                            );
                            bookingForm.reset();
                            fp.clear();
                            if (iti) iti.setNumber('');
                            timeSelect.innerHTML =
                                '<option value="">Seleccione servicio y fecha</option>';
                            timeSelect.disabled = true;
                            // Reset photo preview and error on success
                            photoSizeError.classList.add('hidden');
                            photoPreviewStep2.classList.add('hidden');
                            photoPreviewStep2.src = '#';
                            photoPreviewStep3.classList.add('hidden');
                            photoPreviewStep3.src = '#';
                            reviewEquipmentPhotoText.classList.remove('hidden');
                            showStep(0);
                        } else {
                            let errorMessage = 'Ocurrió un error.';
                            if (body && body.message) {
                                errorMessage = body.message;
                            }
                            if (body && body.errors) {
                                let errorMessages = [errorMessage];
                                let firstErrorFieldKey = null;
                                for (const key in body.errors) {
                                    if (!firstErrorFieldKey) firstErrorFieldKey =
                                        key; // Store first error key
                                    const fieldId = key.replace(/_\w/g, m => m[1].toUpperCase());
                                    const labelElement = bookingForm.querySelector(
                                        `label[for="${fieldId}"]`);
                                    const fieldName = labelElement ? labelElement.innerText.replace('*',
                                        '').trim() : key;
                                    errorMessages.push(`${fieldName}: ${body.errors[key].join(' ')}`);
                                }
                                errorMessage = errorMessages.join('\n');
                            }
                            showAlert(alertError, errorMessage);
                            // Go back to the step with the first validation error
                            if (firstErrorFieldKey) {
                                for (let i = 0; i < steps.length - 1; i++) { // Check steps 0 and 1
                                    if (steps[i].querySelector(`[name="${firstErrorFieldKey}"]`)) {
                                        showStep(i);
                                        break;
                                    }
                                }
                            }
                        }
                    })
                    .catch(error => {
                        /* ... catch error ... */
                        console.error('Fetch Error:', error);
                        showAlert(alertError, 'Error de conexión o respuesta inesperada del servidor.');
                    })
                    .finally(() => {
                        /* ... finally block ... */
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonContent;
                    });
            });

            // --- Equipment Photo Validation & Preview ---
            if (equipmentPhotoInput) {
                equipmentPhotoInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    photoSizeError.classList.add('hidden'); // Hide error initially
                    photoPreviewStep2.classList.add('hidden'); // Hide preview initially
                    photoPreviewStep2.src = '#'; // Reset preview src

                    if (file) {
                        const maxSize = 10 * 1024 * 1024; // 10 MB in bytes
                        if (file.size > maxSize) {
                            photoSizeError.classList.remove('hidden');
                            equipmentPhotoInput.value = ''; // Clear the invalid file selection
                        } else {
                            // File is valid, show preview
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                photoPreviewStep2.src = e.target.result;
                                photoPreviewStep2.classList.remove('hidden');
                                // Store the image data URL as a data attribute for later use
                                photoPreviewStep2.setAttribute('data-image-src', e.target.result);
                            }
                            reader.readAsDataURL(file);
                        }
                    }
                });
            }

            // Show first step initially
            showStep(0);
        });
    </script>
@endpush
