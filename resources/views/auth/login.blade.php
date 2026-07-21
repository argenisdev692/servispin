<x-guest-layout>


    <div class="flex bg-white  overflow-hidden ">


        <div class="hidden lg:block lg:w-1/2  bg-cover bg-center 
        " style="background-image:url('img/image.jpg')">
            <div class="w-full h-full flex  justify-center items-center backdrop-brightness-50 bg-blue-600/60">

                <div class="text-white text-center">

                    <h1 class=" text-gray-300 text-4xl  text-center font-semibold mb-3">Login</h1>
                    <p class="text-gray-200 text-base text-center">Please log in to access your account.</p>
                </div>
            </div>

        </div>

        <div class="w-full p-10 lg:w-1/2">

            <a href="/">
                <x-logo />
            </a>
            <p class="text-xl font-bold text-blue-500 text-center ">
                Welcome back!</p>

            <form method="POST" id="form" action="{{ route('login') }}" class=" " autocomplete="off">
                @csrf
                <x-validation-errors class="mb-1 mx-auto ml-7 mt-2" />

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-center text-indigo-600">
                        {{ session('status') }}
                    </div>
                @endif


                <div class="flex flex-col gap-4 p-4 md:p-8">
                    <div>
                        <a href="/google-auth/redirect"
                            class="flex items-center my-5 justify-center gap-2  rounded-lg shadow-md border border-gray-300 bg-white px-8 py-3 text-center text-sm font-semibold text-gray-800 outline-none ring-gray-300 transition duration-100 hover:bg-gray-100 focus-visible:ring active:bg-gray-200 md:text-base">
                            <svg class="h-5 w-5 shrink-0" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M23.7449 12.27C23.7449 11.48 23.6749 10.73 23.5549 10H12.2549V14.51H18.7249C18.4349 15.99 17.5849 17.24 16.3249 18.09V21.09H20.1849C22.4449 19 23.7449 15.92 23.7449 12.27Z"
                                    fill="#4285F4" />
                                <path
                                    d="M12.2549 24C15.4949 24 18.2049 22.92 20.1849 21.09L16.3249 18.09C15.2449 18.81 13.8749 19.25 12.2549 19.25C9.12492 19.25 6.47492 17.14 5.52492 14.29H1.54492V17.38C3.51492 21.3 7.56492 24 12.2549 24Z"
                                    fill="#34A853" />
                                <path
                                    d="M5.52488 14.29C5.27488 13.57 5.14488 12.8 5.14488 12C5.14488 11.2 5.28488 10.43 5.52488 9.71V6.62H1.54488C0.724882 8.24 0.254883 10.06 0.254883 12C0.254883 13.94 0.724882 15.76 1.54488 17.38L5.52488 14.29Z"
                                    fill="#FBBC05" />
                                <path
                                    d="M12.2549 4.75C14.0249 4.75 15.6049 5.36 16.8549 6.55L20.2749 3.13C18.2049 1.19 15.4949 0 12.2549 0C7.56492 0 3.51492 2.7 1.54492 6.62L5.52492 9.71C6.47492 6.86 9.12492 4.75 12.2549 4.75Z"
                                    fill="#EA4335" />
                            </svg>

                            Continue with Google
                        </a>
                        <div class="relative flex items-center justify-center my-8">
                            <span class="absolute inset-x-0 h-px bg-gray-300"></span>
                            <span class="relative bg-white px-4 text-sm text-gray-400">Or Login With</span>
                        </div>

                        <div class="relative mb-9">
                            <div class="input-container">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">

                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                        viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                        <path
                                            d="M399 384.2C376.9 345.8 335.4 320 288 320H224c-47.4 0-88.9 25.8-111 64.2c35.2 39.2 86.2 63.8 143 63.8s107.8-24.7 143-63.8zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256 16a72 72 0 1 0 0-144 72 72 0 1 0 0 144z" />
                                    </svg>

                                </div>
                                <input type="text" name="identity" id="identity" :value="old('email')" required
                                    autofocus
                                    class="bg-gray-50 border border-gray-300 px-5 py-3 mt-2 mb-2 text-gray-900 text-sm rounded-lg focus:ring-indigo-500
                                 focus:border-indigo-500 block w-full pl-10  p-2.5  dark:border-gray-700  dark:focus:border-indigo-400 focus:outline-none focus:ring focus:ring-opacity-40"
                                    placeholder="Email or Username">
                            </div>
                        </div>

                        <div class="relative mb-2">
                            <div class="input-container">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">


                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                        viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                        <path
                                            d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                                    </svg>

                                </div>

                                <input type="password" id="password" name="password" required
                                    autocomplete="current-password" autofocus autocomplete="off" id="input-group-1"
                                    class="bg-gray-50 border border-gray-300 px-5 py-3 mt-2 mb-2 text-gray-900 text-sm rounded-lg focus:ring-indigo-500
                                 focus:border-indigo-500 block w-full pl-10  p-2.5  dark:border-gray-700  dark:focus:border-indigo-400 focus:outline-none focus:ring focus:ring-opacity-40"
                                    placeholder="Password">
                                <span id="toggle-password" class="password-toggle" onclick="togglePasswordVisibility()">
                                    <i class=" text-gray-500 fa-regular fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>



                    <div class="flex justify-between items-center mb-8">
                        <div class="block">
                            <label for="remember_me" class="flex items-center">
                                <x-checkbox id="remember_me" name="remember" />
                                <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                            </label>
                        </div>
                        <div class="flex items-center justify-end ">
                            @if (Route::has('password.request'))
                                <a class="no-underline text-sm text-gray-600 hover:text-indigo-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    href="{{ route('password.request') }}">
                                    {{ __('Forgot Password?') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <button type="submit" id="SubmitForm"
                        class="block rounded-lg bg-blue-600 px-8 py-3 text-center text-sm font-semibold text-white outline-none ring-indigo-300 transition duration-500 ease-in-out hover:bg-blue-800 focus-visible:ring active:bg-blue-600 md:text-base">
                        {{ __('Login') }}</button>





                </div>

                <div class="flex items-center justify-center  ">
                    <p class="text-center text-sm text-gray-500">¿No tienes cuenta?
                        <button type="button" id="members-only-alert"
                            class="text-indigo-500 transition duration-100 hover:text-indigo-600 active:text-indigo-700 bg-transparent border-0 p-0 font-inherit">
                            Registrarse
                        </button>
                    </p>
                </div>
            </form>

        </div>
    </div>


    <style>
        .password-input-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>

    <script>
        document.getElementById('members-only-alert')?.addEventListener('click', function() {
            Swal.fire({
                icon: 'info',
                title: 'Acceso solo para miembros',
                text: 'El registro no está disponible. Si eres del equipo, inicia sesión con tu cuenta existente o contacta al administrador.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#2563eb',
            });
        });

        function togglePasswordVisibility() {
            const passwordInput = document.querySelector('#password');
            const toggleButton = document.querySelector('#toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.innerHTML = '<i class="fa-regular fa-eye-slash"></i>'; // Cambia el icono a ojo tachado
            } else {
                passwordInput.type = 'password';
                toggleButton.innerHTML = '<i class="fa-regular fa-eye"></i>'; // Cambia el icono a ojo
            }
        }
    </script>


    <!-- START JQUERY VALIDATE  -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script>
        $(document).ready(function() {
            // Extiende el plugin jQuery Validation con una regla personalizada
            $.validator.addMethod("customPassword", function(value, element) {
                    // Utiliza una expresión regular para validar la contraseña
                    return /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#$%^&+=!]).*$/.test(value);
                },
                "The password must contain at least one uppercase, one lowercase, one number, and one special character"
            );

            $("#form").validate({
                // Agrega la opción "errorClass" para especificar la clase CSS para los mensajes de error
                errorClass: "error-message",
                rules: {
                    identity: {
                        required: true,
                    },
                    password: {
                        required: true,
                    },
                },
                messages: {
                    password: {
                        required: "Please provide a password",
                    },
                    identity: {
                        required: "Please provide an Email or Username",
                    },
                },
                submitHandler: function(form) {
                    // Deshabilita el botón, cambia la opacidad y muestra "Initializing..."
                    $("#SubmitForm").prop("disabled", true);
                    $("#SubmitForm").css("opacity", "0.5");
                    $("#SubmitForm").html("Initializing...");

                    // Continúa con el envío del formulario
                    form.submit();
                }
            });
        });
    </script>

    <style>
        input.invalid,
        select.invalid,
        textarea.invalid {
            border-color: red;


        }

        input.valid,
        select.valid,
        textarea.valid {
            border-color: green;
            /* Cambia el color de fondo a verde */
        }

        .error-message {
            color: red;
            font-size: 14px;
            padding-bottom: 5px;
            margin-bottom: 5px;

        }



        .input-container {
            position: relative;
            height: 48px;
            /* Ajusta la altura según sea necesario */
        }
    </style>
    <!--END JQUERY VALIDATE -->

</x-guest-layout>
