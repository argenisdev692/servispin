  <!--====== CONTACT PART START ======-->
  <section id="contact" class="py-24 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center mb-16">
              <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">No dude en contactarnos</h2>
              <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                  ¡Estamos aquí para ayudarte! No dudes en contactarnos para cualquier servicio de reparación
                  de lavadoras, secadoras y electrodomésticos en general. Tu satisfacción es nuestra prioridad
              </p>
          </div>

          <div class="max-w-3xl mx-auto">
              <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
                  <form id="contact-form" autocomplete="off">
                      @csrf
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                          <div>
                              <input type="text" id="name" name="name" placeholder="Nombre"
                                  class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                  data-error="Nombre es obligatorio." required="required" />
                              <div class="help-block with-errors text-red-500 text-sm mt-2 error-name"></div>
                          </div>

                          <div>
                              <input type="email" id="email" name="email" placeholder="Email"
                                  class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                  data-error="Email es obligatorio." required="required" />
                              <div class="help-block with-errors text-red-500 text-sm mt-2 error-email"></div>
                          </div>

                          <div>
                              <input type="text" id="subject" name="subject" placeholder="Asunto"
                                  class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                  data-error="Asunto es obligatorio" required="required" />
                              <div class="help-block with-errors text-red-500 text-sm mt-2 error-subject"></div>
                          </div>

                          <div>
                              <input type="text" id="phone" name="phone" placeholder="Teléfono"
                                  class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                  data-error="Teléfono es obligatorio" required="required" />
                              <div class="help-block with-errors text-red-500 text-sm mt-2 error-phone"></div>
                          </div>

                          <div class="md:col-span-2">
                              <textarea rows="5" id="message" placeholder="Mensaje" name="message"
                                  class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all resize-none"
                                  data-error="Please, leave us a message." required="required"></textarea>
                              <div class="help-block with-errors text-red-500 text-sm mt-2 error-message"></div>
                          </div>

                          <div class="md:col-span-2 form-message-container" style="display: none;">
                              <div class="alert-message"></div>
                          </div>

                          <div class="md:col-span-2 text-center">
                              <button type="submit" id="submit-btn"
                                  class="bg-gradient-to-r from-cyan-500 to-blue-700 text-white px-12 py-4 rounded-full font-semibold hover:shadow-lg hover:scale-105 transition-all focus:outline-none cursor-pointer">
                                  <span class="normal-text">{{ __('Enviar Mensaje') }}</span>
                                  <span class="loading-spinner hidden" style="display: none;">
                                      <i class="fa fa-spinner" id="spinner-icon"></i> Enviando...
                                  </span>
                              </button>
                          </div>
                      </div>
                  </form>
              </div>
          </div>
      </div>
  </section>
  <!--====== CONTACT PART ENDS ======-->

  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>

  <script>
      document.addEventListener('DOMContentLoaded', function() {
          const form = document.getElementById('contact-form');
          const submitBtn = document.getElementById('submit-btn');
          const spinner = document.querySelector('.loading-spinner');
          const normalText = document.querySelector('.normal-text');
          const spinnerIcon = document.getElementById('spinner-icon');
          const formMessageContainer = document.querySelector('.form-message-container');
          const alertMessage = document.querySelector('.alert-message');

          // Double ensure spinner is hidden on page load
          spinner.style.display = 'none';
          spinner.classList.add('hidden');

          // Real-time validation
          const nameInput = document.getElementById('name');
          const emailInput = document.getElementById('email');
          const phoneInput = document.getElementById('phone');
          const subjectInput = document.getElementById('subject');
          const messageInput = document.getElementById('message');

          // Initialize intl-tel-input
          let iti = null;
          if (phoneInput) {
              iti = window.intlTelInput(phoneInput, {
                  initialCountry: "es",
                  preferredCountries: ["es"],
                  separateDialCode: true,
                  utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
                  autoPlaceholder: "aggressive"
              });

              // Add validation and formatting for Spanish phone numbers
              phoneInput.addEventListener('input', function(e) {
                  // Detect if we're working with a Spanish number
                  const isSpanish = iti.getSelectedCountryData().iso2 === 'es';

                  // Save cursor position before modification
                  const cursorPos = this.selectionStart;
                  // Count spaces before cursor
                  const spacesBefore = (this.value.substring(0, cursorPos).match(/ /g) || []).length;

                  // Get only digits from current value
                  const digits = this.value.replace(/\D/g, '');

                  // If Spain (+34), limit to maximum 9 digits (not counting country code)
                  if (isSpanish) {
                      // Automatic formatting XXX XX XX XX for Spanish numbers
                      let formattedValue = '';
                      for (let i = 0; i < Math.min(digits.length, 9); i++) {
                          // Add space after positions 3, 5, and 7
                          if (i === 3 || i === 5 || i === 7) {
                              formattedValue += ' ';
                          }
                          formattedValue += digits[i];
                      }

                      // Update value only if it has changed
                      if (this.value !== formattedValue) {
                          this.value = formattedValue;

                          // Calculate spaces after formatting
                          const spacesAfter = (formattedValue.substring(0, cursorPos).match(/ /g) || [])
                              .length;
                          // Adjust cursor position accounting for added spaces
                          const newCursorPos = cursorPos + (spacesAfter - spacesBefore);

                          // Reset cursor position
                          this.setSelectionRange(newCursorPos, newCursorPos);
                      }
                  } else {
                      // For other countries, limit to a reasonable maximum (15 digits is international standard)
                      if (digits.length > 15) {
                          this.value = this.value.substring(0, this.value.length - 1);
                      }
                  }
              });

              // Validate number when country changes
              phoneInput.addEventListener('countrychange', function() {
                  // Get only digits from current value
                  const digits = this.value.replace(/\D/g, '');

                  // If new country is Spain, apply Spanish format
                  if (iti.getSelectedCountryData().iso2 === 'es') {
                      if (digits.length > 9) {
                          // Truncate to 9 digits
                          const truncatedDigits = digits.substring(0, 9);

                          // Apply XXX XX XX XX format
                          let formattedValue = '';
                          for (let i = 0; i < truncatedDigits.length; i++) {
                              if (i === 3 || i === 5 || i === 7) {
                                  formattedValue += ' ';
                              }
                              formattedValue += truncatedDigits[i];
                          }

                          this.value = formattedValue;
                      } else if (digits.length > 0) {
                          // If already has digits but fewer than 9, format what's there
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

          // Function to validate email format
          function isValidEmail(email) {
              const re =
                  /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
              return re.test(String(email).toLowerCase());
          }

          // Real-time validation for name
          nameInput.addEventListener('blur', function() {
              validateNameField();
          });

          nameInput.addEventListener('input', function() {
              // Get cursor position
              const cursorPos = this.selectionStart;
              // Auto-capitalize name
              this.value = this.value.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
              // Restore cursor position
              this.setSelectionRange(cursorPos, cursorPos);

              // Clear validation message when typing
              if (this.value.trim()) {
                  const errorElement = document.querySelector('.error-name');
                  errorElement.textContent = '';
                  this.classList.remove('is-invalid');
              }
          });

          // Real-time validation for email
          emailInput.addEventListener('blur', function() {
              validateEmailField();
          });

          emailInput.addEventListener('input', function() {
              // Clear validation message when typing
              const errorElement = document.querySelector('.error-email');
              if (this.value.trim()) {
                  if (isValidEmail(this.value)) {
                      errorElement.textContent = '';
                      this.classList.remove('is-invalid');
                  }
              }
          });

          // Real-time validation for phone
          phoneInput.addEventListener('blur', function() {
              validatePhoneField();
          });

          phoneInput.addEventListener('input', function() {
              // Clear validation message when typing
              const errorElement = document.querySelector('.error-phone');
              if (this.value.trim() && iti && iti.isValidNumber()) {
                  errorElement.textContent = '';
                  this.classList.remove('is-invalid');
              }
          });

          // Real-time validation for subject
          subjectInput.addEventListener('blur', function() {
              validateSubjectField();
          });

          subjectInput.addEventListener('input', function() {
              // Clear validation message when typing
              if (this.value.trim()) {
                  const errorElement = document.querySelector('.error-subject');
                  errorElement.textContent = '';
                  this.classList.remove('is-invalid');
              }
          });

          // Real-time validation for message
          messageInput.addEventListener('blur', function() {
              validateMessageField();
          });

          messageInput.addEventListener('input', function() {
              // Clear validation message when typing
              if (this.value.trim()) {
                  const errorElement = document.querySelector('.error-message');
                  errorElement.textContent = '';
                  this.classList.remove('is-invalid');
              }
          });

          // Validation functions
          function validateNameField() {
              const errorElement = document.querySelector('.error-name');
              if (!nameInput.value.trim()) {
                  errorElement.textContent = 'El nombre es requerido';
                  nameInput.classList.add('is-invalid');
                  return false;
              } else {
                  errorElement.textContent = '';
                  nameInput.classList.remove('is-invalid');
                  return true;
              }
          }

          function validateEmailField() {
              const errorElement = document.querySelector('.error-email');
              if (!emailInput.value.trim()) {
                  errorElement.textContent = 'El correo electrónico es requerido';
                  emailInput.classList.add('is-invalid');
                  return false;
              } else if (!isValidEmail(emailInput.value)) {
                  errorElement.textContent = 'Ingrese un correo electrónico válido';
                  emailInput.classList.add('is-invalid');
                  return false;
              } else {
                  errorElement.textContent = '';
                  emailInput.classList.remove('is-invalid');
                  return true;
              }
          }

          function validatePhoneField() {
              const errorElement = document.querySelector('.error-phone');
              if (!phoneInput.value.trim()) {
                  errorElement.textContent = 'El teléfono es requerido';
                  phoneInput.classList.add('is-invalid');
                  return false;
              } else if (iti && !iti.isValidNumber()) {
                  errorElement.textContent = 'Ingrese un número de teléfono válido';
                  phoneInput.classList.add('is-invalid');
                  return false;
              } else {
                  errorElement.textContent = '';
                  phoneInput.classList.remove('is-invalid');
                  return true;
              }
          }

          function validateSubjectField() {
              const errorElement = document.querySelector('.error-subject');
              if (!subjectInput.value.trim()) {
                  errorElement.textContent = 'El asunto es requerido';
                  subjectInput.classList.add('is-invalid');
                  return false;
              } else {
                  errorElement.textContent = '';
                  subjectInput.classList.remove('is-invalid');
                  return true;
              }
          }

          function validateMessageField() {
              const errorElement = document.querySelector('.error-message');
              if (!messageInput.value.trim()) {
                  errorElement.textContent = 'El mensaje es requerido';
                  messageInput.classList.add('is-invalid');
                  return false;
              } else {
                  errorElement.textContent = '';
                  messageInput.classList.remove('is-invalid');
                  return true;
              }
          }

          // Form submission with AJAX
          form.addEventListener('submit', function(e) {
              e.preventDefault();

              // Validate all fields one more time
              const isNameValid = validateNameField();
              const isEmailValid = validateEmailField();
              const isPhoneValid = validatePhoneField();
              const isSubjectValid = validateSubjectField();
              const isMessageValid = validateMessageField();

              // Check if all validations passed
              const isValid = isNameValid && isEmailValid && isPhoneValid && isSubjectValid &&
                  isMessageValid;

              if (!isValid) {
                  return false;
              }

              // Show loading spinner
              submitBtn.disabled = true;
              normalText.classList.add('hidden');
              normalText.style.display = 'none';
              spinner.classList.remove('hidden');
              spinner.style.display = 'inline-block';
              spinnerIcon.classList.add('fa-spin'); // Add spin class only when submitting

              // Get form data
              const formData = new FormData(form);

              // Use the international phone number format for submission
              if (iti && iti.isValidNumber()) {
                  formData.set('phone', iti.getNumber());
              }

              // Send AJAX request
              fetch('{{ route('contact.submit') }}', {
                      method: 'POST',
                      body: formData,
                      headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                      }
                  })
                  .then(response => response.json())
                  .then(data => {
                      // Hide spinner
                      submitBtn.disabled = false;
                      spinner.classList.add('hidden');
                      spinner.style.display = 'none';
                      normalText.classList.remove('hidden');
                      normalText.style.display = 'inline-block';
                      spinnerIcon.classList.remove('fa-spin');

                      if (data.success) {
                          // Success message with SweetAlert
                          Swal.fire({
                              icon: 'success',
                              title: '¡Mensaje enviado!',
                              text: data.message,
                              confirmButtonColor: '#10b981'
                          });

                          // Reset form
                          form.reset();
                          if (iti) iti.setNumber('');
                      } else {
                          // Error message with SweetAlert
                          if (data.errors) {
                              let errorMessage = '';
                              for (const field in data.errors) {
                                  errorMessage += data.errors[field][0] + '<br>';
                                  document.querySelector('.error-' + field).textContent = data.errors[
                                      field][0];
                              }

                              Swal.fire({
                                  icon: 'error',
                                  title: 'Error',
                                  html: errorMessage,
                                  confirmButtonColor: '#ef4444'
                              });
                          } else {
                              Swal.fire({
                                  icon: 'error',
                                  title: 'Error',
                                  text: data.message ||
                                      'Ha ocurrido un error. Inténtelo de nuevo.',
                                  confirmButtonColor: '#ef4444'
                              });
                          }
                      }
                  })
                  .catch(error => {
                      // Hide spinner
                      submitBtn.disabled = false;
                      spinner.classList.add('hidden');
                      spinner.style.display = 'none';
                      normalText.classList.remove('hidden');
                      normalText.style.display = 'inline-block';
                      spinnerIcon.classList.remove('fa-spin');

                      // Display error message with SweetAlert
                      Swal.fire({
                          icon: 'error',
                          title: 'Error de conexión',
                          text: 'Ha ocurrido un error con la conexión. Inténtelo de nuevo.',
                          confirmButtonColor: '#ef4444'
                      });

                      console.error('Error:', error);
                  });
          });
      });
  </script>
  <style>
      .is-invalid {
          border-color: #ef4444 !important;
          background-color: #fee2e2 !important;
      }

      .help-block {
          color: #ef4444;
          font-size: 0.875rem;
          margin-top: 0.25rem;
      }

      .loading-spinner {
          display: inline-block;
      }

      .iti {
          width: 100%;
          display: block;
      }

      .iti__flag-container {
          z-index: 10;
      }

      .iti input {
          width: 100%;
          padding: 1rem 1.5rem;
          padding-left: 80px;
          background-color: #f8fafc;
          border: 1px solid #e2e8f0;
          border-radius: 0.75rem;
          transition: all 0.3s ease;
      }

      .iti input:focus {
          outline: none;
          ring: 2px;
          ring-color: #06b6d4;
          border-color: transparent;
      }
  </style>
