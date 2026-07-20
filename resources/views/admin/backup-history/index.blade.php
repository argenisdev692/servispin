@extends('layouts.app')

@section('content')
    <x-admin-shell lang="es">
        <div class="container px-6 mx-auto py-6 max-w-7xl">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Historial de Backups</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Backups generados por Spatie Laravel Backup.</p>
                </div>
                <button
                    type="button"
                    id="backup-history-refresh"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    title="Actualizar"
                >
                    <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
                </button>
            </div>

            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative w-full sm:max-w-xs">
                    <input
                        type="search"
                        id="backup-history-search"
                        placeholder="Buscar por archivo, disco o estado..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                    >
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <label for="backup-history-length">Mostrar</label>
                    <select
                        id="backup-history-length"
                        class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="backup-history-sort px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300" data-column="0">Fecha</th>
                                <th class="backup-history-sort px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300" data-column="1">Nombre del archivo</th>
                                <th class="backup-history-sort px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300" data-column="2">Tamaño</th>
                                <th class="backup-history-sort px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300" data-column="3">Disco</th>
                                <th class="backup-history-sort px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300" data-column="4">Estado</th>
                                <th class="backup-history-sort px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300" data-column="5">Creado hace...</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="backup-history-body" class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Cargando backups...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="backup-history-pagination" class="mt-4 flex flex-col gap-3 text-sm text-slate-600 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between"></div>
        </div>
    </x-admin-shell>
@endsection

@push('scripts')
    <script src="{{ asset('js/backup-history-manager.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initBackupHistoryManager({
                tableBodySelector: '#backup-history-body',
                paginationSelector: '#backup-history-pagination',
                searchSelector: '#backup-history-search',
                lengthSelector: '#backup-history-length',
                refreshSelector: '#backup-history-refresh',
                sortSelector: '.backup-history-sort',
                datatableUrl: @json(route('admin.backup-history.datatable')),
                csrfToken: @json(csrf_token()),
            });
        });
    </script>
@endpush
