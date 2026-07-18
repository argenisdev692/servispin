@extends('layouts.app')

{{--
    Formulario de solicitud de asistencia remota (US-1).

    Deliberadamente más simple que appointments/book.blade.php: aquí no hay
    dirección (FR-11) ni desplazamiento, y el pago ya se ha hecho por QR antes de
    llegar a esta pantalla.

    ⚠️ NUNCA añadir campos de tarjeta (FR-4). El dato de tarjeta lo captura SumUp
    y no puede tocar este servidor: es lo único que mantiene a Servispin fuera
    del alcance de PCI-DSS. El backend los rechaza con 422 (StoreRemoteAssistanceRequest).
--}}

@push('styles')
    {{-- El enlace de la videollamada es un secreto de facto: estas páginas no se
         indexan (plan §8). Va en el stack 'styles' porque es el único que el
         layout renderiza dentro de <head>. --}}
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
    <style>
        .honeypot-field {
            position: absolute !important;
            left: -9999px !important;
            opacity: 0;
            height: 0;
            width: 0;
        }

        .slot-btn.selected {
            background-color: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .required-asterisk {
            color: red;
            margin-left: 2px;
        }

        .iti {
            width: 100%;
            display: block;
        }

        .iti__flag-container {
            z-index: 10;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8">
        @include('remote-assistance._header', ['current' => 'Solicitar'])

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Asistencia técnica por videollamada</h1>
        <p class="text-gray-600 mb-8">
            {{ $service->name }} · {{ $service->duration }} minutos
            @if ($service->price)
                · <strong>{{ number_format($service->price, 2) }} €</strong>
            @endif
        </p>

        {{-- Paso previo: el pago. Va primero a propósito: sin referencia no hay solicitud (FR-2) --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-blue-900 mb-2">1. Paga la sesión con el código QR</h2>
            <p class="text-blue-800 text-sm mb-4">
                Escanea el QR con tu móvil y paga el importe de la sesión. Al terminar, SumUp te dará una
                <strong>referencia de recibo</strong>: la necesitas para rellenar el formulario.
            </p>
            {{-- El enlace de SumUp es un visor de PDF, no una imagen: se abre en
                 una pestaña nueva en vez de incrustarlo (en un <img> saldría roto). --}}
            @if (config('remote_assistance.sumup_qr_url'))
                <div class="text-center">
                    <a href="{{ config('remote_assistance.sumup_qr_url') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-qrcode mr-2"></i> Abrir el código QR para pagar
                    </a>
                    <p class="text-xs text-blue-700 mt-2">Se abre en una pestaña nueva.</p>
                </div>
            @else
                <p class="text-sm text-blue-700 italic">
                    Escríbenos y te enviamos el QR de pago.
                </p>
            @endif
            <p class="text-xs text-blue-700 mt-4">
                No te pedimos —ni guardamos— el número de tu tarjeta en ningún momento. El cobro lo gestiona
                SumUp íntegramente.
            </p>
        </div>

        <form id="remote-assistance-form" class="space-y-6" novalidate>
            @csrf
            <input type="hidden" name="client_timezone" id="client_timezone">

            {{-- Honeypot: invisible para una persona, irresistible para un bot --}}
            <div class="honeypot-field" aria-hidden="true">
                <label for="website_url">No rellenes este campo</label>
                <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">2. Cuéntanos qué te pasa</h2>

                <input type="hidden" name="service_id" value="{{ $service->id }}">

                <div class="mb-4">
                    <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Marca del aparato <span class="required-asterisk">*</span>
                    </label>
                    <select name="brand_id" id="brand_id" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecciona una marca</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="issue_description" class="block text-sm font-medium text-gray-700 mb-1">
                        Describe la avería <span class="required-asterisk">*</span>
                    </label>
                    <textarea name="issue_description" id="issue_description" rows="4" required
                              placeholder="Cuanto más detalle nos des, mejor podremos ayudarte en la llamada."
                              class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div class="mt-4">
                    <label for="equipment_photo" class="block text-sm font-medium text-gray-700 mb-1">
                        Foto del aparato <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <input type="file" name="equipment_photo" id="equipment_photo"
                           accept="image/jpeg,image/png,image/jpg,image/gif"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">Solo imágenes (JPG, PNG, GIF). Tamaño máximo: 10 MB.</p>
                    <p id="photo-size-error" class="text-red-500 text-xs mt-1 hidden">El archivo excede el límite de 10 MB.</p>
                    <img id="photo-preview" src="#" alt="Vista previa" class="mt-2 max-h-40 rounded-lg shadow-md hidden object-contain">
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">3. Elige el día y la hora</h2>

                {{-- FR-6: el huso se muestra siempre, explícito. R-5 dice que este
                     es el fallo más caro del módulo: un cliente que se pierde la
                     cita que ya pagó. --}}
                <p class="text-sm text-gray-500 mb-4">
                    Las horas se muestran en tu zona horaria:
                    <strong id="tz-label">detectando…</strong>.
                    Nuestro horario local es {{ $businessTimezone }}.
                </p>

                <div class="mb-4">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha <span class="required-asterisk">*</span>
                    </label>
                    <input type="text" id="date" required placeholder="Selecciona una fecha"
                           class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div id="slots-container" class="hidden">
                    <span class="block text-sm font-medium text-gray-700 mb-2">
                        Hueco disponible <span class="required-asterisk">*</span>
                    </span>
                    <div id="slots" class="grid grid-cols-3 sm:grid-cols-4 gap-2"></div>
                    <p id="no-slots" class="hidden text-sm text-gray-500">No quedan huecos ese día. Prueba con otra fecha.</p>
                </div>

                <input type="hidden" name="start_time" id="start_time" required>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">4. Tus datos</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="client_first_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="client_first_name" id="client_first_name" required
                               minlength="3" maxlength="15" autocomplete="given-name"
                               placeholder="Primer nombre"
                               class="capitalize mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Solo letras, sin espacios (3–15 caracteres).</p>
                    </div>
                    <div>
                        <label for="client_last_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Apellido <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="client_last_name" id="client_last_name" required
                               minlength="3" maxlength="15" autocomplete="family-name"
                               placeholder="Primer apellido"
                               class="capitalize mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Solo letras, sin espacios (3–15 caracteres).</p>
                    </div>
                    <div>
                        <label for="client_email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="required-asterisk">*</span>
                        </label>
                        <input type="email" name="client_email" id="client_email" required autocomplete="email"
                               class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Aquí te enviaremos el enlace de la videollamada.</p>
                    </div>
                    <div>
                        <label for="client_phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Teléfono <span class="required-asterisk">*</span>
                        </label>
                        <input type="tel" name="client_phone" id="client_phone" required autocomplete="tel"
                               class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">5. Datos del pago que acabas de hacer</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="payment_reference" class="block text-sm font-medium text-gray-700 mb-1">
                            Referencia del recibo de SumUp <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="payment_reference" id="payment_reference" required maxlength="128"
                               class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Aparece en el recibo que te envía SumUp por email o SMS.</p>
                    </div>
                    <div>
                        <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Importe pagado (€) <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="payment_amount" id="payment_amount" required readonly
                               value="{{ number_format($service->price, 2, '.', '') }}"
                               class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-700 cursor-not-allowed focus:outline-none">
                    </div>
                    <div>
                        <label for="payer_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del pagador <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" name="payer_name" id="payer_name" required
                               minlength="3" maxlength="20" autocomplete="name"
                               placeholder="Nombre y apellido"
                               class="capitalize mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Como aparece en SumUp (3–20 letras, puede incluir espacio).</p>
                    </div>
                </div>
            </div>

            {{-- FR-3, dicho en voz alta: el cliente tiene que saber que esto no es
                 una reserva confirmada hasta que Cesar coteje el pago. --}}
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
                <p class="text-sm text-amber-900">
                    <strong>Tu cita no queda confirmada al enviar este formulario.</strong>
                    Comprobamos que el pago ha entrado y te enviamos el enlace de la videollamada por email.
                    Ese email es el que confirma tu cita.
                </p>
            </div>

            <div id="form-errors" class="hidden bg-red-50 border border-red-200 text-red-800 rounded p-4 text-sm"></div>

            <button type="submit" id="submit-btn"
                    class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition">
                Enviar solicitud
            </button>

            {{-- Nota de privacidad en el punto de recogida de datos (GDPR). --}}
            <p class="text-xs text-gray-500 text-center mt-3">
                Al enviar esta solicitud aceptas nuestra
                <a href="{{ route('legal.privacidad') }}" target="_blank" class="underline hover:text-blue-600">política de privacidad</a>.
                Tratamos tus datos para gestionar tu cita. Nunca pedimos datos de tu tarjeta.
            </p>
        </form>

        <div id="success-message" class="hidden bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <h2 class="text-xl font-semibold text-green-900 mb-2">Solicitud recibida</h2>
            <p class="text-green-800" id="success-text"></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
    <script>
        (function () {
            const form = document.getElementById('remote-assistance-form');
            const slotsContainer = document.getElementById('slots-container');
            const slotsEl = document.getElementById('slots');
            const noSlotsEl = document.getElementById('no-slots');
            const startTimeEl = document.getElementById('start_time');
            const errorsEl = document.getElementById('form-errors');
            const submitBtn = document.getElementById('submit-btn');
            const clientFirstNameInput = document.getElementById('client_first_name');
            const clientLastNameInput = document.getElementById('client_last_name');
            const clientPhoneInput = document.getElementById('client_phone');
            const payerNameInput = document.getElementById('payer_name');
            const equipmentPhotoInput = document.getElementById('equipment_photo');
            const photoSizeError = document.getElementById('photo-size-error');
            const photoPreview = document.getElementById('photo-preview');
            const namePattern = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+$/u;
            const payerNamePattern = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?: [A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*$/u;

            const clientTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '{{ $businessTimezone }}';
            document.getElementById('client_timezone').value = clientTimezone;
            document.getElementById('tz-label').textContent = clientTimezone;

            let selectedDate = null;
            let iti = null;

            function sanitizeNameInput(input) {
                input.addEventListener('input', function () {
                    this.value = this.value
                        .replace(/\s+/g, '')
                        .replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ]/g, '')
                        .slice(0, 15);
                });
            }

            sanitizeNameInput(clientFirstNameInput);
            sanitizeNameInput(clientLastNameInput);

            payerNameInput.addEventListener('input', function () {
                this.value = this.value
                    .replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s]/g, '')
                    .replace(/\s{2,}/g, ' ')
                    .slice(0, 20);
            });

            payerNameInput.addEventListener('blur', function () {
                this.value = this.value.trim();
            });

            if (clientPhoneInput) {
                iti = window.intlTelInput(clientPhoneInput, {
                    initialCountry: 'es',
                    preferredCountries: ['es'],
                    separateDialCode: true,
                    utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js',
                    autoPlaceholder: 'aggressive',
                });

                clientPhoneInput.addEventListener('input', function () {
                    const isSpanish = iti.getSelectedCountryData().iso2 === 'es';
                    const cursorPos = this.selectionStart;
                    const spacesBefore = (this.value.substring(0, cursorPos).match(/ /g) || []).length;
                    const digits = this.value.replace(/\D/g, '');

                    if (isSpanish) {
                        let formattedValue = '';
                        for (let i = 0; i < Math.min(digits.length, 9); i++) {
                            if (i === 3 || i === 5 || i === 7) formattedValue += ' ';
                            formattedValue += digits[i];
                        }
                        if (this.value !== formattedValue) {
                            this.value = formattedValue;
                            const spacesAfter = (formattedValue.substring(0, cursorPos).match(/ /g) || []).length;
                            this.setSelectionRange(cursorPos + (spacesAfter - spacesBefore), cursorPos + (spacesAfter - spacesBefore));
                        }
                    } else if (digits.length > 15) {
                        this.value = this.value.substring(0, this.value.length - 1);
                    }
                });

                clientPhoneInput.addEventListener('countrychange', function () {
                    const digits = this.value.replace(/\D/g, '');
                    if (iti.getSelectedCountryData().iso2 === 'es' && digits.length > 0) {
                        let formattedValue = '';
                        const limited = digits.substring(0, 9);
                        for (let i = 0; i < limited.length; i++) {
                            if (i === 3 || i === 5 || i === 7) formattedValue += ' ';
                            formattedValue += limited[i];
                        }
                        this.value = formattedValue;
                    }
                });
            }

            if (equipmentPhotoInput) {
                equipmentPhotoInput.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    photoSizeError.classList.add('hidden');
                    photoPreview.classList.add('hidden');
                    photoPreview.src = '#';

                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        showErrors(['La foto debe ser una imagen (JPG, PNG o GIF).']);
                        equipmentPhotoInput.value = '';
                        return;
                    }

                    const maxSize = 10 * 1024 * 1024;
                    if (file.size > maxSize) {
                        photoSizeError.classList.remove('hidden');
                        equipmentPhotoInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        photoPreview.src = e.target.result;
                        photoPreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                });
            }

            flatpickr('#date', {
                locale: 'es',
                minDate: new Date().fp_incr(1),
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                onChange: function (dates, dateStr) {
                    selectedDate = dateStr;
                    loadSlots(dateStr);
                }
            });

            async function loadSlots(date) {
                slotsEl.innerHTML = '<p class="col-span-full text-sm text-gray-500">Cargando huecos…</p>';
                slotsContainer.classList.remove('hidden');
                noSlotsEl.classList.add('hidden');
                startTimeEl.value = '';

                try {
                    const res = await fetch('{{ route('remote-assistance.slots') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            service_id: {{ $service->id }},
                            date: date,
                            timezone: clientTimezone
                        })
                    });

                    const json = await res.json();
                    slotsEl.innerHTML = '';

                    if (!json.success || !json.data.length) {
                        noSlotsEl.classList.remove('hidden');
                        return;
                    }

                    json.data.forEach(function (slot) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'slot-btn bg-white border border-gray-300 rounded-md py-2 px-1 text-sm text-gray-800 hover:border-blue-500 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500';
                        // Se muestra la hora del cliente...
                        btn.textContent = slot.client_formatted_time;
                        // ...pero se envía la del negocio, que es como se persiste.
                        btn.dataset.time = slot.time;
                        btn.addEventListener('click', function () {
                            document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                            btn.classList.add('selected');
                            startTimeEl.value = slot.time;
                        });
                        slotsEl.appendChild(btn);
                    });
                } catch (e) {
                    slotsEl.innerHTML = '<p class="col-span-full text-sm text-red-600">No se han podido cargar los huecos.</p>';
                }
            }

            function showErrors(messages) {
                errorsEl.innerHTML = messages.map(m => '<div>• ' + m + '</div>').join('');
                errorsEl.classList.remove('hidden');
                errorsEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function validateForm() {
                const messages = [];
                const requiredFields = form.querySelectorAll('input[required]:not([type="hidden"]):not([readonly]), select[required], textarea[required]');

                requiredFields.forEach(function (field) {
                    field.classList.remove('border-red-500');
                    const label = form.querySelector('label[for="' + field.id + '"]');
                    if (label) label.classList.remove('text-red-500');

                    if (!String(field.value || '').trim()) {
                        messages.push('Completa todos los campos obligatorios (*).');
                        field.classList.add('border-red-500');
                        if (label) label.classList.add('text-red-500');
                    }
                });

                const firstName = clientFirstNameInput.value.trim();
                const lastName = clientLastNameInput.value.trim();

                if (firstName.length < 3 || firstName.length > 15 || !namePattern.test(firstName)) {
                    messages.push('El nombre debe tener entre 3 y 15 letras, sin espacios.');
                    clientFirstNameInput.classList.add('border-red-500');
                }
                if (lastName.length < 3 || lastName.length > 15 || !namePattern.test(lastName)) {
                    messages.push('El apellido debe tener entre 3 y 15 letras, sin espacios.');
                    clientLastNameInput.classList.add('border-red-500');
                }

                const payerName = payerNameInput.value.trim();
                if (payerName.length < 3 || payerName.length > 20 || !payerNamePattern.test(payerName)) {
                    messages.push('El nombre del pagador debe tener entre 3 y 20 letras (puede incluir un espacio).');
                    payerNameInput.classList.add('border-red-500');
                }

                if (iti && (!clientPhoneInput.value.trim() || !iti.isValidNumber())) {
                    messages.push('Introduce un teléfono válido.');
                    clientPhoneInput.classList.add('border-red-500');
                }

                if (!startTimeEl.value) {
                    messages.push('Selecciona un hueco disponible para tu cita.');
                }

                return [...new Set(messages)];
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                errorsEl.classList.add('hidden');

                const validationErrors = validateForm();
                if (validationErrors.length) {
                    showErrors(validationErrors);
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Enviando…';

                const formData = new FormData(form);
                if (iti && iti.isValidNumber()) {
                    formData.set('client_phone', iti.getNumber());
                }

                try {
                    const res = await fetch('{{ route('remote-assistance.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });

                    const json = await res.json();

                    if (res.status === 201) {
                        form.classList.add('hidden');
                        document.getElementById('success-text').textContent = json.message;
                        document.getElementById('success-message').classList.remove('hidden');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (res.status === 429) {
                        showErrors(['Demasiados intentos. Espera un momento e inténtalo de nuevo.']);
                        return;
                    }

                    const messages = json.errors
                        ? Object.values(json.errors).flat()
                        : [json.message || 'No se ha podido enviar la solicitud.'];
                    showErrors(messages);

                    // El hueco pudo ocuparse mientras rellenaba el formulario (FR-7).
                    if (selectedDate) {
                        loadSlots(selectedDate);
                    }
                } catch (err) {
                    showErrors(['Error de conexión. Inténtalo de nuevo.']);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar solicitud';
                }
            });
        })();
    </script>
@endpush
