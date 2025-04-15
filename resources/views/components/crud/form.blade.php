@props([
    'action' => '#',
    'method' => 'POST',
    'formId' => 'crud-form',
    'submitButtonText' => 'Save',
    'cancelButtonText' => 'Cancel',
    'cancelUrl' => '#',
    'enctype' => null,
    'hasFiles' => false,
    'submitButtonClass' => 'bg-blue-600 hover:bg-blue-700 text-white',
])

<form id="{{ $formId }}" action="{{ $action }}" method="POST"
    {{ $hasFiles ? 'enctype=multipart/form-data' : '' }} {{ $enctype ? 'enctype=' . $enctype : '' }}
    class="bg-white dark:bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4">

    @csrf

    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="space-y-4">
        {{ $slot }}
    </div>

    <div class="flex items-center justify-end mt-6 space-x-3">
        <a href="{{ $cancelUrl }}"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            {{ $cancelButtonText }}
        </a>

        <button type="submit"
            class="px-4 py-2 text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $submitButtonClass }}">
            {{ $submitButtonText }}
        </button>
    </div>
</form>
