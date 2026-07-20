@extends('layouts.app')

@section('content')
    <x-admin-shell lang="es">
        <div class="container px-6 mx-auto py-6 max-w-4xl">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Detalle del Backup</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $details->filename }}</p>
                </div>
                <a
                    href="{{ route('admin.backup-history.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    title="Volver"
                >
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Nombre del archivo</dt>
                    <dd class="sm:col-span-2 text-sm text-slate-900 dark:text-slate-100">{{ $details->filename }}</dd>

                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Fecha</dt>
                    <dd class="sm:col-span-2 text-sm text-slate-900 dark:text-slate-100">{{ $details->formattedDate }}</dd>

                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Tamaño</dt>
                    <dd class="sm:col-span-2 text-sm text-slate-900 dark:text-slate-100">{{ $details->formattedSize }}</dd>

                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Disco</dt>
                    <dd class="sm:col-span-2 text-sm text-slate-900 dark:text-slate-100">{{ $details->disk }}</dd>

                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Ruta</dt>
                    <dd class="sm:col-span-2 text-sm text-slate-900 dark:text-slate-100"><code class="rounded bg-slate-100 px-2 py-1 text-xs dark:bg-slate-900">{{ $details->path }}</code></dd>

                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Estado</dt>
                    <dd class="sm:col-span-2">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $details->statusBadgeClass }}">{{ $details->status }}</span>
                    </dd>

                    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Creado hace...</dt>
                    <dd class="sm:col-span-2 text-sm text-slate-900 dark:text-slate-100">{{ $details->createdAgo }}</dd>
                </dl>

                <div class="mt-6 flex gap-2">
                    @can('download', $backupFile)
                        @if ($details->exists)
                            <a
                                href="{{ route('admin.backup-history.download', ['backupFile' => $backupFile->getRouteKey()]) }}"
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"
                                title="Descargar"
                            >
                                <i class="fa-solid fa-download" aria-hidden="true"></i>
                            </a>
                        @endif
                    @endcan

                    @can('delete', $backupFile)
                        <button
                            type="button"
                            class="backup-delete-button inline-flex items-center justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700"
                            title="Eliminar"
                            data-destroy-url="{{ route('admin.backup-history.destroy', ['backupFile' => $backupFile->getRouteKey()]) }}"
                            data-filename="{{ $details->filename }}"
                        >
                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </x-admin-shell>
@endsection

@push('scripts')
    <script src="{{ asset('js/backup-history-manager.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.initBackupHistoryDeleteButtons({
                csrfToken: @json(csrf_token()),
                redirectUrl: @json(route('admin.backup-history.index')),
            });
        });
    </script>
@endpush
