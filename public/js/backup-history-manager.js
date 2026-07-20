(function (window, $) {
    'use strict';

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderStatusBadge(status, badgeClass) {
        return '<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ' + badgeClass + '">' + escapeHtml(status) + '</span>';
    }

    function renderActions(row) {
        var html = '<div class="flex justify-end gap-2">';

        html += '<a href="' + escapeHtml(row.show_url) + '" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200" title="Ver detalle">';
        html += '<i class="fa-solid fa-eye" aria-hidden="true"></i></a>';

        if (row.can_download && row.exists) {
            html += '<a href="' + escapeHtml(row.download_url) + '" class="inline-flex items-center justify-center rounded-lg border border-blue-300 bg-blue-50 px-2.5 py-1.5 text-blue-700 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-300" title="Descargar">';
            html += '<i class="fa-solid fa-download" aria-hidden="true"></i></a>';
        }

        if (row.can_delete) {
            html += '<button type="button" class="backup-delete-button inline-flex items-center justify-center rounded-lg border border-red-300 bg-red-50 px-2.5 py-1.5 text-red-700 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300" title="Eliminar" ';
            html += 'data-destroy-url="' + escapeHtml(row.destroy_url) + '" data-filename="' + escapeHtml(row.filename) + '">';
            html += '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>';
        }

        html += '</div>';

        return html;
    }

    function renderRows(rows) {
        if (!rows.length) {
            return '<tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No hay backups.</td></tr>';
        }

        return rows.map(function (row) {
            return '<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">' +
                '<td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">' + escapeHtml(row.formatted_date) + '</td>' +
                '<td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">' + escapeHtml(row.filename) + '</td>' +
                '<td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">' + escapeHtml(row.formatted_size) + '</td>' +
                '<td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">' + escapeHtml(row.disk) + '</td>' +
                '<td class="px-4 py-3 text-sm">' + renderStatusBadge(row.status, row.status_badge_class) + '</td>' +
                '<td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">' + escapeHtml(row.created_ago) + '</td>' +
                '<td class="px-4 py-3 text-sm">' + renderActions(row) + '</td>' +
                '</tr>';
        }).join('');
    }

    function confirmDelete(filename) {
        return Swal.fire({
            title: '¿Eliminar backup?',
            html: 'Se eliminará permanentemente <strong>' + escapeHtml(filename) + '</strong>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        });
    }

    function deleteBackup(destroyUrl, csrfToken) {
        return $.ajax({
            url: destroyUrl,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    }

    window.initBackupHistoryDeleteButtons = function (options) {
        var csrfToken = options.csrfToken;
        var redirectUrl = options.redirectUrl || null;
        var reloadCallback = options.reloadCallback || null;

        $(document).off('click', '.backup-delete-button').on('click', '.backup-delete-button', function (event) {
            event.preventDefault();

            var button = $(this);
            var destroyUrl = button.data('destroy-url');
            var filename = button.data('filename');

            confirmDelete(filename).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                deleteBackup(destroyUrl, csrfToken)
                    .done(function () {
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'Backup eliminado correctamente.',
                            icon: 'success',
                            confirmButtonText: 'OK',
                        }).then(function () {
                            if (typeof reloadCallback === 'function') {
                                reloadCallback();
                                return;
                            }

                            if (redirectUrl !== null) {
                                window.location.href = redirectUrl;
                            }
                        });
                    })
                    .fail(function (xhr) {
                        var message = 'No se pudo eliminar el backup.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            title: 'Error',
                            text: message,
                            icon: 'error',
                            confirmButtonText: 'OK',
                        });
                    });
            });
        });
    };

    window.initBackupHistoryManager = function (options) {
        var state = {
            draw: 1,
            start: 0,
            length: parseInt($(options.lengthSelector).val(), 10) || 10,
            search: '',
            orderColumn: 0,
            orderDir: 'desc',
        };

        function renderPagination(response) {
            var total = response.recordsFiltered || 0;
            var from = total === 0 ? 0 : state.start + 1;
            var to = Math.min(state.start + state.length, total);
            var prevDisabled = state.start <= 0;
            var nextDisabled = state.start + state.length >= total;

            var html = '<div>Mostrando ' + from + '–' + to + ' de ' + total + '</div>';
            html += '<div class="flex gap-2">';
            html += '<button type="button" id="backup-history-prev" class="rounded-lg border border-slate-300 px-3 py-1.5 disabled:opacity-50 dark:border-slate-600"' + (prevDisabled ? ' disabled' : '') + '>Anterior</button>';
            html += '<button type="button" id="backup-history-next" class="rounded-lg border border-slate-300 px-3 py-1.5 disabled:opacity-50 dark:border-slate-600"' + (nextDisabled ? ' disabled' : '') + '>Siguiente</button>';
            html += '</div>';

            $(options.paginationSelector).html(html);
        }

        function loadTable() {
            $(options.tableBodySelector).html(
                '<tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">Cargando backups...</td></tr>'
            );

            $.ajax({
                url: options.datatableUrl,
                method: 'GET',
                dataType: 'json',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                data: {
                    draw: state.draw,
                    start: state.start,
                    length: state.length,
                    'search[value]': state.search,
                    'order[0][column]': state.orderColumn,
                    'order[0][dir]': state.orderDir,
                },
            })
                .done(function (response) {
                    $(options.tableBodySelector).html(renderRows(response.data || []));
                    renderPagination(response);
                    initBackupHistoryDeleteButtons({
                        csrfToken: options.csrfToken,
                        reloadCallback: loadTable,
                    });
                })
                .fail(function (xhr) {
                    var message = 'Error al cargar los backups.';

                    if (xhr.status === 403) {
                        message = 'No tienes permiso para ver el historial de backups.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    $(options.tableBodySelector).html(
                        '<tr><td colspan="7" class="px-4 py-8 text-center text-sm text-red-600 dark:text-red-400">' + escapeHtml(message) + '</td></tr>'
                    );
                });
        }

        var searchTimeout = null;

        $(options.searchSelector).on('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                state.search = $(options.searchSelector).val();
                state.start = 0;
                state.draw += 1;
                loadTable();
            }, 300);
        });

        $(options.lengthSelector).on('change', function () {
            state.length = parseInt($(this).val(), 10) || 10;
            state.start = 0;
            state.draw += 1;
            loadTable();
        });

        $(options.refreshSelector).on('click', function () {
            state.draw += 1;
            loadTable();
        });

        $(document).on('click', '#backup-history-prev', function () {
            state.start = Math.max(0, state.start - state.length);
            state.draw += 1;
            loadTable();
        });

        $(document).on('click', '#backup-history-next', function () {
            state.start += state.length;
            state.draw += 1;
            loadTable();
        });

        $(options.sortSelector).on('click', function () {
            var column = parseInt($(this).data('column'), 10);

            if (state.orderColumn === column) {
                state.orderDir = state.orderDir === 'asc' ? 'desc' : 'asc';
            } else {
                state.orderColumn = column;
                state.orderDir = 'asc';
            }

            state.draw += 1;
            loadTable();
        });

        loadTable();
    };
})(window, jQuery);
