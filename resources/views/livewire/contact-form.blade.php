  <!--====== CONTACT PART START ======-->
  <section id="contact" class="contact-area py-120">
      <div class="container">
          <div class="justify-center row">
              <div class="w-full mx-4 lg:w-1/2">
                  <div class="pb-10 text-center section-title">
                      <h4 class="title" id="titleResponsive">No dude en contactarnos</h4>
                      <p class="text">
                          ¡Estamos aquí para ayudarte! No dudes en contactarnos para cualquier servicio de reparación
                          de lavadoras, secadoras y electrodomésticos en general. Tu satisfacción es nuestra prioridad
                      </p>
                  </div>
                  <!-- section title -->
              </div>
          </div>
          <!-- row -->
          <div class="justify-center row">
              <div class="w-full lg:w-2/3">
                  <div class="contact-form">
                      <form wire:submit.prevent="submit" id="contact-form" autocomplete="off">
                          <div class="row">
                              <div class="w-full md:w-1/2">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <input type="text" wire:model="name" placeholder="Nombre"
                                          data-error="Nombre es obligatorio." required="required" />
                                      <div class="help-block with-errors text-red">
                                          @error('name')
                                              {{ $message }}
                                          @enderror
                                      </div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <div class="w-full md:w-1/2">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <input type="email" wire:model="email" placeholder=" Email"
                                          data-error="Valid email is required." required="required" />
                                      <div class="help-block with-errors">
                                          @error('email')
                                              {{ $message }}
                                          @enderror
                                      </div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <div class="w-full md:w-1/2">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <input type="text" wire:model="subject" placeholder="Asunto"
                                          data-error="Asunto es obligatorio" required="required" />
                                      <div class="help-block with-errors">
                                          @error('subject')
                                              {{ $message }}
                                          @enderror
                                      </div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <div class="w-full md:w-1/2">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <input type="text" wire:model="phone" placeholder="Teléfono "
                                          data-error="Teléfono es obligatorio" required="required" />
                                      <div class="help-block with-errors">
                                          @error('phone')
                                              {{ $message }}
                                          @enderror
                                      </div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <div class="w-full">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <textarea rows="5" placeholder="Mensaje" wire:model="message" data-error="Please, leave us a message."
                                          required="required"></textarea>
                                      <div class="help-block with-errors">
                                          @error('message')
                                              {{ $message }}
                                          @enderror
                                      </div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              @if (session()->has('success'))
                                  <div class="w-full">
                                      <div class="mx-4 p-3 bg-green-100 text-green-800 rounded">
                                          {{ session('success') }}
                                      </div>
                                  </div>
                              @endif
                              @if (session()->has('error'))
                                  <div class="w-full">
                                      <div class="mx-4 p-3 bg-red-100 text-red-800 rounded">
                                          {{ session('error') }}
                                      </div>
                                  </div>
                              @endif
                              <div class="w-full">
                                  <div class="mx-4 mt-2 text-center single-form form-group">
                                      <button type="submit" class="main-btn gradient-btn focus:outline-none"
                                          wire:loading.attr="disabled" wire:target="submit">
                                          {{ __('Enviar Mensaje') }}
                                      </button>
                                  </div>
                                  <!-- single form -->
                              </div>
                          </div>
                          <!-- row -->
                      </form>
                  </div>
                  <!-- row -->
              </div>
          </div>
          <!-- row -->
      </div>
      <!-- container -->
  </section>
  <!--====== CONTACT PART ENDS ======-->
