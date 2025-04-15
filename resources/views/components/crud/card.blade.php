@props([
    'title' => null,
    'subtitle' => null,
    'tools' => null,
    'footer' => null,
    'headerClass' => 'card-header d-flex justify-content-between align-items-center',
    'bodyClass' => 'card-body',
    'footerClass' => 'card-footer',
    'cardClass' => 'card mb-4',
])

<div class="{{ $cardClass }}">
    @if ($title || $tools)
        <div class="{{ $headerClass }}">
            <div>
                @if ($title)
                    <h5 class="card-title mb-0">{{ $title }}</h5>
                    @if ($subtitle)
                        <h6 class="card-subtitle text-muted mt-1">{{ $subtitle }}</h6>
                    @endif
                @endif
            </div>

            @if ($tools)
                <div class="card-tools">
                    {{ $tools }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="{{ $footerClass }}">
            {{ $footer }}
        </div>
    @endif
</div>
