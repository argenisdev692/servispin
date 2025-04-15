@props([
    'id' => 'entityModal',
    'title' => 'Entity',
    'colorType' => 'green',
    'formId' => 'entityForm',
    'entityIdField' => 'entityUuid',
])

@php
    $colors = [
        'green' => [
            'header' => 'bg-green-500 dark:bg-green-600',
            'hover' => 'hover:bg-green-600 dark:hover:bg-green-700',
            'button' => 'bg-green-600 hover:bg-green-700',
            'focus' => 'focus:ring-green-500',
        ],
        'blue' => [
            'header' => 'bg-blue-500 dark:bg-blue-600',
            'hover' => 'hover:bg-blue-600 dark:hover:bg-blue-700',
            'button' => 'bg-blue-600 hover:bg-blue-700',
            'focus' => 'focus:ring-blue-500',
        ],
    ];

    $headerClass = $colors[$colorType]['header'] ?? $colors['green']['header'];
    $hoverClass = $colors[$colorType]['hover'] ?? $colors['green']['hover'];
    $buttonClass = $colors[$colorType]['button'] ?? $colors['green']['button'];
    $focusClass = $colors[$colorType]['focus'] ?? $colors['green']['focus'];
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
        <div id="modalHeader"
            class="px-4 py-3 flex justify-between items-center border-b border-gray-200 dark:border-gray-600 {{ $headerClass }}">
            <h3 id="modalTitle" class="w-full text-lg leading-6 font-medium text-white text-center">
                {{ $title }}
            </h3>
            <button type="button" id="closeModal"
                class="text-white bg-transparent {{ $hoverClass }} rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="{{ $formId }}">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <input type="hidden" id="{{ $entityIdField }}">
                {{ $slot }}
            </div>
            <div
                class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-600">
                <button type="submit" id="saveBtn"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 {{ $buttonClass }} text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $focusClass }} sm:ml-3 sm:w-auto sm:text-sm">
                    <span class="button-text">Save</span>
                </button>
                <button type="button" id="cancelBtn"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
