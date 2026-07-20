@extends('layouts.app')

@section('content')
    <x-admin-shell lang="es">
        <div class="container-fluid px-4 py-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1">Detalle del Backup</h1>
                    <p class="text-muted mb-0">{{ $details->filename }}</p>
                </div>
                <a href="{{ route('admin.backup-history.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Nombre del archivo</dt>
                        <dd class="col-sm-9">{{ $details->filename }}</dd>

                        <dt class="col-sm-3">Fecha</dt>
                        <dd class="col-sm-9">{{ $details->formattedDate }}</dd>

                        <dt class="col-sm-3">Tamaño</dt>
                        <dd class="col-sm-9">{{ $details->formattedSize }}</dd>

                        <dt class="col-sm-3">Disco</dt>
                        <dd class="col-sm-9">{{ $details->disk }}</dd>

                        <dt class="col-sm-3">Ruta</dt>
                        <dd class="col-sm-9"><code>{{ $details->path }}</code></dd>

                        <dt class="col-sm-3">Estado</dt>
                        <dd class="col-sm-9">
                            <span class="badge {{ $details->statusBadgeClass }}">{{ $details->status }}</span>
                        </dd>

                        <dt class="col-sm-3">Creado hace...</dt>
                        <dd class="col-sm-9">{{ $details->createdAgo }}</dd>
                    </dl>

                    <div class="d-flex gap-2 mt-4">
                        @can('download', $backupFile)
                            @if ($details->exists)
                                <a href="{{ route('admin.backup-history.download', $backupFile) }}" class="btn btn-primary btn-sm" title="Download">
                                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                                </a>
                            @endif
                        @endcan

                        @can('delete', $backupFile)
                            <button
                                type="button"
                                class="btn btn-danger btn-sm backup-delete-button"
                                title="Delete"
                                data-destroy-url="{{ route('admin.backup-history.destroy', $backupFile) }}"
                                data-filename="{{ $details->filename }}"
                            >
                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </x-admin-shell>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/backup-history-datatable.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initBackupHistoryDeleteButtons({
                csrfToken: @json(csrf_token()),
                redirectUrl: @json(route('admin.backup-history.index')),
            });
        });
    </script>
@endpush
