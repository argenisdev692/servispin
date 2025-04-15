@props([
    'label',
    'name',
    'id',
    'type' => 'text', // Default type
    'required' => false,
    'style' => '',
    'class' =>
        'mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-100 dark:bg-gray-700', // Default classes
    'value' => '',
    'validationId' => null,
    'errorId' => null,
    'labelClass' => 'block text-sm font-medium text-gray-700 dark:text-gray-300', // Default label class
    'errorClass' => 'text-red-500 text-xs italic mt-1 hidden', // Default error class
    'validationClass' => 'text-xs mt-1 hidden', // Default validation message class
])

<div>
    <label for="{{ $id }}" class="{{ $labelClass }}">
        {{ $label }} @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
        @if ($required) required @endif style="{{ $style }}" class="{{ $class }}"
        value="{{ old($name, $value) }}" {{-- Add old() helper for form persistence --}} {{ $attributes->merge(['class' => $class]) }}
        {{-- Merge classes and allow passing extra attributes --}}>
    @if ($errorId)
        <span id="{{ $errorId }}" class="{{ $errorClass }}"></span>
    @endif
    @if ($validationId)
        <span id="{{ $validationId }}" class="{{ $validationClass }}"></span>
    @endif
</div>
