@props([
    'id' => 'alertMessage',
    'type' => 'success',
    'dismissible' => true,
    'show' => false,
    'autoHide' => true,
    'autoHideDelay' => 3000,
    'position' => 'top', // top, bottom
    'icon' => true,
])

@php
    $alertClasses = [
        'success' => 'bg-green-100 border border-green-400 text-green-700',
        'error' => 'bg-red-100 border border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border border-blue-400 text-blue-700',
    ];

    $iconPaths = [
        'success' =>
            'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
        'error' =>
            'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
        'warning' =>
            'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
        'info' =>
            'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
    ];

    $display = $show ? 'block' : 'none';
    $alertClass = $alertClasses[$type] ?? $alertClasses['info'];
    $iconPath = $iconPaths[$type] ?? $iconPaths['info'];
@endphp

<div id="{{ $id }}" style="display: {{ $display }}" class="mb-4 p-4 rounded relative {{ $alertClass }}"
    role="alert">

    @if ($icon)
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="{{ $iconPath }}" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span id="{{ $id }}Text" class="block sm:inline">{{ $slot }}</span>
        </div>
    @else
        <span id="{{ $id }}Text" class="block sm:inline">{{ $slot }}</span>
    @endif

    @if ($dismissible)
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3"
            onclick="dismissAlert('{{ $id }}')">
            <svg class="fill-current h-6 w-6" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path
                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
            </svg>
        </button>
    @endif
</div>

@once
    @push('scripts')
        <script>
            function dismissAlert(alertId) {
                document.getElementById(alertId).style.display = 'none';
            }

            function showAlert(alertId, message, type = 'success', autoHide = true, delay = 3000) {
                const alertElement = document.getElementById(alertId);
                const alertTextElement = document.getElementById(alertId + 'Text');

                // Remove existing alert classes
                alertElement.classList.remove(
                    'bg-green-100', 'border-green-400', 'text-green-700',
                    'bg-red-100', 'border-red-400', 'text-red-700',
                    'bg-yellow-100', 'border-yellow-400', 'text-yellow-700',
                    'bg-blue-100', 'border-blue-400', 'text-blue-700'
                );

                // Set message
                alertTextElement.textContent = message;

                // Add appropriate class based on type
                const alertClasses = {
                    success: 'bg-green-100 border border-green-400 text-green-700',
                    error: 'bg-red-100 border border-red-400 text-red-700',
                    warning: 'bg-yellow-100 border border-yellow-400 text-yellow-700',
                    info: 'bg-blue-100 border border-blue-400 text-blue-700',
                };

                const classesToAdd = alertClasses[type] ? alertClasses[type].split(' ') : alertClasses['info'].split(' ');
                alertElement.classList.add(...classesToAdd);

                // Show alert
                alertElement.style.display = 'block';

                // Auto-hide if enabled
                if (autoHide) {
                    setTimeout(() => {
                        alertElement.style.display = 'none';
                    }, delay);
                }
            }
        </script>
    @endpush
@endonce
