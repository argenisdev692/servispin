@extends('layouts.app')

@section('content')
    <x-admin-shell lang="es">
        <div class="container-fluid px-4 py-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1">Historial de Backups</h1>
                    <p class="text-muted mb-0">Backups generados por Spatie Laravel Backup.</p>
                </div>
                <a href="{{ route('admin.backup-history.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="backup-history-table" class="table table-striped table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Nombre del archivo</th>
                                    <th>Tamaño</th>
                                    <th>Disco</th>
                                    <th>Estado</th>
                                    <th>Creado hace...</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </x-admin-shell>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="{{ asset('js/backup-history-datatable.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initBackupHistoryDatatable({
                tableSelector: '#backup-history-table',
                datatableUrl: @json(route('admin.backup-history.datatable')),
                csrfToken: @json(csrf_token()),
            });
        });
    </script>
@endpush
