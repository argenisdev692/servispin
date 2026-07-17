<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb" />
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon/favicon-16x16.png">
    <link rel="manifest" href="img/favicon/site.webmanifest">
    <title>Welcome</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Scripts -->
    {{-- Tailwind CSS v4 (browser CDN), coherente con layouts/app.blade.php.
         La base de formularios completa vive en app.blade.php; aquí basta el
         borde de los inputs para las pantallas de login/registro. --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
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

            [type='text'], [type='email'], [type='url'], [type='password'],
            [type='number'], [type='search'], [type='tel'], textarea, select {
                appearance: none;
                background-color: #fff;
                border: 1px solid #6b7280;
                padding: 0.5rem 0.75rem;
            }
            [type='checkbox'], [type='radio'] {
                appearance: none;
                height: 1rem;
                width: 1rem;
                border: 1px solid #6b7280;
                color: #2563eb;
            }
            [type='checkbox']:checked, [type='radio']:checked {
                background-color: currentColor;
                border-color: transparent;
            }
            [type='checkbox'] { border-radius: 0.25rem; }
            [type='radio'] { border-radius: 100%; }
        }
    </style>
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
</head>

<body>
    <div class="font-sans text-gray-900 antialiased">
        {{ $slot }}
    </div>
</body>

</html>
