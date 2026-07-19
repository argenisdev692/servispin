@props([
    'variant' => 'default',
])

@php
    $wrapperClass = match ($variant) {
        'sidebar' => 'app-logo app-logo--sidebar',
        'remote' => 'app-logo app-logo--remote',
        'appointment' => 'app-logo app-logo--appointment',
        default => 'app-logo',
    };
@endphp

<span {{ $attributes->merge(['class' => $wrapperClass]) }}>
    <img
        src="{{ asset('files/images/logo.png') }}"
        alt="Servispin"
        class="app-logo-img app-logo-img--light"
    />
    <img
        src="{{ asset('files/images/logo-white.png') }}"
        alt="Servispin"
        class="app-logo-img app-logo-img--dark"
    />
</span>
