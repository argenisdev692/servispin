@props([
    'columns' => [],
    'items' => [],
    'actionColumn' => true,
    'tableClass' => 'table table-striped table-hover',
    'theadClass' => 'table-light',
    'id' => 'crud-table',
    'responsive' => true,
    'searchable' => false,
    'pagination' => null,
    'noDataText' => 'No data available',
    'showDeleteModal' => false,
])

<div class="{{ $responsive ? 'table-responsive' : '' }}">
    <table id="{{ $id }}" class="{{ $tableClass }}">
        <thead class="{{ $theadClass }}">
            <tr>
                @foreach ($columns as $column)
                    <th scope="col">{{ $column['label'] ?? ucfirst($column['key']) }}</th>
                @endforeach

                @if ($actionColumn)
                    <th scope="col" class="text-end">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    @foreach ($columns as $column)
                        <td>
                            @if (isset($column['format']) && is_callable($column['format']))
                                {!! $column['format']($item, $column['key']) !!}
                            @elseif(isset($column['raw']) && $column['raw'])
                                {!! data_get($item, $column['key']) !!}
                            @else
                                {{ data_get($item, $column['key']) }}
                            @endif
                        </td>
                    @endforeach

                    @if ($actionColumn)
                        <td class="text-end">
                            {{ $slot }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + ($actionColumn ? 1 : 0) }}" class="text-center">
                        {{ $noDataText }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($searchable)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize DataTables if available
                if (typeof $.fn.DataTable !== 'undefined') {
                    $('#{{ $id }}').DataTable({
                        responsive: true,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "Showing 0 to 0 of 0 entries",
                            infoFiltered: "(filtered from _MAX_ total entries)"
                        }
                    });
                }
            });
        </script>
    @endpush
@endif

@if ($pagination && !$searchable)
    <div class="mt-4">
        {{ $pagination->links() }}
    </div>
@endif
