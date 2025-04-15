@props([
    'id' => null,
    'showRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'confirmMessage' => 'Are you sure you want to delete this item?',
    'buttonSize' => 'sm',
    'showTooltip' => 'View details',
    'editTooltip' => 'Edit item',
    'deleteTooltip' => 'Delete item',
    'showPermission' => null,
    'editPermission' => null,
    'deletePermission' => null,
])

<div class="flex space-x-1">
    @if ($showRoute && (empty($showPermission) || auth()->user()->can($showPermission)))
        <a href="{{ $showRoute }}" class="btn btn-primary btn-{{ $buttonSize }} rounded" data-bs-toggle="tooltip"
            data-bs-placement="top" title="{{ $showTooltip }}">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if ($editRoute && (empty($editPermission) || auth()->user()->can($editPermission)))
        <a href="{{ $editRoute }}" class="btn btn-warning btn-{{ $buttonSize }} rounded" data-bs-toggle="tooltip"
            data-bs-placement="top" title="{{ $editTooltip }}">
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if ($deleteRoute && (empty($deletePermission) || auth()->user()->can($deletePermission)))
        <form action="{{ $deleteRoute }}" method="POST" class="inline-block delete-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-{{ $buttonSize }} rounded delete-btn"
                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $deleteTooltip }}"
                data-confirm="{{ $confirmMessage }}">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const message = this.getAttribute('data-confirm');

                        if (confirm(message)) {
                            this.closest('form').submit();
                        }
                    });
                });

                // Initialize tooltips if Bootstrap is available
                if (typeof bootstrap !== 'undefined') {
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });
        </script>
    @endpush
@endonce
