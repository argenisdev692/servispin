(function (window, $) {
    'use strict';

    function renderStatusBadge(status, badgeClass) {
        return '<span class="badge ' + badgeClass + '">' + status + '</span>';
    }

    function renderActions(row) {
        var html = '<div class="d-flex justify-content-end gap-2">';

        html += '<a href="' + row.show_url + '" class="btn btn-outline-secondary btn-sm" title="View details">';
        html += '<i class="fa-solid fa-eye" aria-hidden="true"></i></a>';

        if (row.can_download && row.exists) {
            html += '<a href="' + row.download_url + '" class="btn btn-outline-primary btn-sm" title="Download">';
            html += '<i class="fa-solid fa-download" aria-hidden="true"></i></a>';
        }

        if (row.can_delete) {
            html += '<button type="button" class="btn btn-outline-danger btn-sm backup-delete-button" title="Delete" ';
            html += 'data-destroy-url="' + row.destroy_url + '" data-filename="' + row.filename + '">';
            html += '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>';
        }

        html += '</div>';

        return html;
    }

    function confirmDelete(filename) {
        return Swal.fire({
            title: 'Delete backup?',
            html: 'This will permanently delete <strong>' + filename + '</strong>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
        });
    }

    function deleteBackup(destroyUrl, csrfToken) {
        return $.ajax({
            url: destroyUrl,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
        });
    }

    window.initBackupHistoryDeleteButtons = function (options) {
        var csrfToken = options.csrfToken;
        var redirectUrl = options.redirectUrl || null;
        var tableSelector = options.tableSelector || null;
        var table = options.table || null;

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
                            title: 'Deleted',
                            text: 'Backup deleted successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK',
                        }).then(function () {
                            if (table !== null) {
                                table.ajax.reload(null, false);
                                return;
                            }

                            if (redirectUrl !== null) {
                                window.location.href = redirectUrl;
                            }
                        });
                    })
                    .fail(function (xhr) {
                        var message = 'Unable to delete backup.';

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

        if (tableSelector !== null && table === null) {
            return;
        }
    };

    window.initBackupHistoryDatatable = function (options) {
        var table = $(options.tableSelector).DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searching: true,
            ordering: true,
            ajax: {
                url: options.datatableUrl,
                type: 'GET',
                data: function (data) {
                    return data;
                },
            },
            columns: [
                { data: 'formatted_date', name: 'formatted_date' },
                { data: 'filename', name: 'filename' },
                { data: 'formatted_size', name: 'formatted_size' },
                { data: 'disk', name: 'disk' },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: true,
                    render: function (data, type, row) {
                        return renderStatusBadge(row.status, row.status_badge_class);
                    },
                },
                { data: 'created_ago', name: 'created_ago' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return renderActions(row);
                    },
                },
            ],
            order: [[0, 'desc']],
            language: {
                emptyTable: 'No backups found.',
                processing: 'Loading backups...',
            },
        });

        initBackupHistoryDeleteButtons({
            csrfToken: options.csrfToken,
            table: table,
        });
    };
})(window, jQuery);
