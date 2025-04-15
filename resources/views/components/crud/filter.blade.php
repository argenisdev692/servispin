@props([
    'action' => '',
    'method' => 'GET',
    'resetUrl' => null,
    'filters' => [],
    'formClass' => 'card mb-4',
    'formBodyClass' => 'card-body',
    'formFooterClass' => 'card-footer d-flex justify-content-between',
])

<form action="{{ $action }}" method="{{ $method }}" class="{{ $formClass }}">
    <div class="{{ $formBodyClass }}">
        <div class="row g-3">
            {{ $slot }}

            @foreach ($filters as $name => $filter)
                <div class="col-md-{{ $filter['width'] ?? '3' }}">
                    <div class="form-group">
                        <label for="filter_{{ $name }}" class="form-label">
                            {{ $filter['label'] ?? ucfirst($name) }}
                        </label>

                        @if ($filter['type'] == 'select')
                            <select name="{{ $name }}" id="filter_{{ $name }}" class="form-select">
                                <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                                @foreach ($filter['options'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ request($name) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($filter['type'] == 'date')
                            <input type="date" name="{{ $name }}" id="filter_{{ $name }}"
                                class="form-control" value="{{ request($name) }}"
                                placeholder="{{ $filter['placeholder'] ?? '' }}">
                        @elseif($filter['type'] == 'daterange')
                            <div class="input-group">
                                <input type="date" name="{{ $name }}_from" class="form-control"
                                    value="{{ request($name . '_from') }}" placeholder="From">
                                <span class="input-group-text">to</span>
                                <input type="date" name="{{ $name }}_to" class="form-control"
                                    value="{{ request($name . '_to') }}" placeholder="To">
                            </div>
                        @else
                            <input type="{{ $filter['type'] ?? 'text' }}" name="{{ $name }}"
                                id="filter_{{ $name }}" class="form-control" value="{{ request($name) }}"
                                placeholder="{{ $filter['placeholder'] ?? '' }}">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="{{ $formFooterClass }}">
        <div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search me-1"></i> Filter
            </button>

            @if ($resetUrl)
                <a href="{{ $resetUrl }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            @endif
        </div>

        @if (isset($actions))
            <div class="filter-actions">
                {{ $actions }}
            </div>
        @endif
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any select2 dropdowns if available
            if (typeof $.fn.select2 !== 'undefined') {
                $('select.form-select').select2({
                    theme: 'bootstrap-5'
                });
            }
        });
    </script>
@endpush
