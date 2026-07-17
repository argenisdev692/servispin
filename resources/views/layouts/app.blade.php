<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('img/favicon/site.webmanifest') }}">
    <title>ServiSpin - Web App

        @if (empty($title))
            Dashboard
        @else
            {{ $title }}
        @endif
    </title>

    <!-- Fonts -->


    <!-- Scripts -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- Tailwind CSS v4 (Play CDN de navegador). Sustituye al Play CDN v3 y al
         antiguo tailwind.output.css. Ver el bloque <style type="text/tailwindcss">
         más abajo para el tema y la base de formularios. --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Fonts: Plus Jakarta Sans — legible en formularios/admin y cercana en la web pública -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />


    <!-- Styles -->

    <style>
        ::-webkit-scrollbar {
            width: 12px;
        }

        ::-webkit-scrollbar-track {
            background-color: #e5e7eb;
            border-radius: 9px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #6b7280;
            border-radius: 7px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: #3b82f6;
            border-radius: 7px;
        }
    </style>

    {{-- Configuración de Tailwind v4 en línea (el browser build no lee tailwind.config.js).
         - @theme: fuente global Plus Jakarta Sans (tailwind.config.js).
         - @layer base: reproduce la base del plugin @tailwindcss/forms. NO se usa
           `@plugin "@tailwindcss/forms"` porque el browser build no garantiza
           cargar plugins de npm, y un @plugin que no resuelve rompería TODA la
           hoja. Las utilidades (rounded-md, border-gray-300, focus:ring-*) siguen
           ganando: en v4 el layer de utilidades va después del de base. --}}
    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        @layer base {
            button,
            [type='button'],
            [type='submit'],
            [type='reset'],
            a,
            nav,
            nav a,
            [role='button'],
            summary,
            label[for] {
                cursor: pointer;
            }

            button:disabled,
            [type='button']:disabled,
            [type='submit']:disabled,
            [aria-disabled='true'] {
                cursor: not-allowed;
            }

            [type='text'], [type='email'], [type='url'], [type='password'], [type='number'],
            [type='date'], [type='datetime-local'], [type='month'], [type='search'],
            [type='tel'], [type='time'], [type='week'], [multiple], textarea, select {
                appearance: none;
                background-color: #fff;
                border: 1px solid #6b7280;
                border-radius: 0;
                padding: 0.5rem 0.75rem;
                font-size: 1rem;
                line-height: 1.5rem;
            }

            [type='text']:focus, [type='email']:focus, [type='url']:focus, [type='password']:focus,
            [type='number']:focus, [type='date']:focus, [type='datetime-local']:focus,
            [type='month']:focus, [type='search']:focus, [type='tel']:focus, [type='time']:focus,
            [type='week']:focus, [multiple]:focus, textarea:focus, select:focus {
                outline: 2px solid transparent;
                outline-offset: 2px;
                border-color: #2563eb;
                box-shadow: 0 0 0 1px #2563eb;
            }

            [type='checkbox'], [type='radio'] {
                appearance: none;
                padding: 0;
                display: inline-block;
                vertical-align: middle;
                height: 1rem;
                width: 1rem;
                color: #2563eb;
                background-color: #fff;
                border: 1px solid #6b7280;
                flex-shrink: 0;
            }

            [type='checkbox'] { border-radius: 0.25rem; }
            [type='radio'] { border-radius: 100%; }

            [type='checkbox']:checked, [type='radio']:checked {
                border-color: transparent;
                background-color: currentColor;
                background-size: 100% 100%;
                background-position: center;
                background-repeat: no-repeat;
            }

            [type='checkbox']:checked {
                background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
            }

            [type='radio']:checked {
                background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
            }
        }
    </style>
    <script src="{{ asset('assets/js/init-alpine.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" defer></script>

    {{-- Stack for page-specific styles --}}
    @stack('styles')

    @livewireStyles
</head>

<body class="font-sans antialiased">
    <x-banner />

    <div class="min-h-screen bg-gray-100">


        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

    @stack('modals')
    <script src="{{ asset('js/crud-manager.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
    @livewireScripts

</body>

</html>
