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
                      <form id="contact-form" action="files/contact.php" method="post" data-toggle="validator"
                          autocomplete="off">
                          <div class="row">
                              <div class="w-full md:w-1/2">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <input type="text" name="name" placeholder="Nombre"
                                          data-error="Nombre es obligatorio." required="required" />
                                      <div class="help-block with-errors text-red"></div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <div class="w-full md:w-1/2">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <input type="email" name="email" placeholder=" Email"
                                          data-error="Valid email is required." required="required" />
                                      <div class="help-block with-errors"></div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <div class="w-full md:w-1/2">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <input type="text" name="subject" placeholder="Asunto"
                                          data-error="Asunto es obligatorio" required="required" />
                                      <div class="help-block with-errors"></div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <div class="w-full md:w-1/2">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <input type="text" name="phone" placeholder="Teléfono "
                                          data-error="Teléfono es obligatorio" required="required" />
                                      <div class="help-block with-errors"></div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <div class="w-full">
                                  <div class="mx-4 mb-6 single-form form-group">
                                      <textarea rows="5" placeholder="Mensaje" name="message" data-error="Please, leave us a message."
                                          required="required"></textarea>
                                      <div class="help-block with-errors"></div>
                                  </div>
                                  <!-- single form -->
                              </div>
                              <p class="mx-4 form-message"></p>
                              <div class="w-full">
                                  <div class="mx-4 mt-2 text-center single-form form-group">
                                      <button wire:click.prevent="submit()"
                                          class="main-btn gradient-btn focus:outline-none" wire:loading.attr="disabled"
                                          wire:target="submit,password">

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
