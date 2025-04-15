@extends('layouts.app')

@section('content')
    <div :class="{ 'theme-dark': dark }" x-data="data()" lang="en">
        <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
            <!-- MENU SIDEBAR -->
            <x-menu-sidebar />
            <!-- END MENU SIDEBAR -->

            <div class="flex flex-col flex-1 w-full">
                <!-- HEADER -->
                <x-header-dashboard />
                <!-- END HEADER -->

                <main class="h-full overflow-y-auto">
                    <div class="container px-6 mx-auto grid">
                        <!-- Page Title -->
                        <div
                            class="mt-5 flex items-center justify-between p-4 mb-8 text-sm font-semibold text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:shadow-outline-purple">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <a href="{{ route('services.index') }}">
                                    <span>Services Management</span>
                                </a>
                            </div>
                        </div>

                        <!-- Filter Bar Component -->
                        <x-crud.filter-bar entityName="Service" :showSearchBar="true" :showInactiveToggle="true" :showPerPage="true"
                            :perPageOptions="[5, 10, 15, 25, 50]" :defaultPerPage="10" addButtonId="addServiceBtn" />

                        <!-- Alert Messages -->
                        <x-crud.alert id="alertMessage" :show="false" />

                        <!-- Data Table -->
                        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <!-- Sortable Name Column -->
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header"
                                            data-field="name">
                                            Name
                                            <span class="sort-icon"></span>
                                        </th>
                                        <!-- Duration Column -->
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header"
                                            data-field="duration">
                                            Duration (mins)
                                            <span class="sort-icon"></span>
                                        </th>
                                        <!-- Price Column -->
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header"
                                            data-field="price">
                                            Price
                                            <span class="sort-icon"></span>
                                        </th>
                                        <!-- Sortable Created At Column -->
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header"
                                            data-field="created_at">
                                            Created At
                                            <span class="sort-icon"></span>
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="servicesTable"
                                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <!-- Table content will be loaded here by JavaScript -->
                                    <tr id="loadingRow">
                                        <td colspan="5" class="px-6 py-4 text-center">
                                            <svg class="animate-spin h-5 w-5 mr-3 text-blue-500 inline-block"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            Loading services...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div id="pagination" class="mt-4 flex justify-between items-center">
                            <!-- Pagination info will be inserted here by JavaScript -->
                        </div>

                        <!-- Service Modal Component -->
                        <x-crud.modal id="serviceModal" title="Add Service" colorType="green" formId="serviceForm"
                            entityIdField="serviceUuid">
                            <x-forms.input label="Service Name" name="name" id="name" type="text" required="true"
                                style="text-transform: capitalize;" errorId="nameError"
                                validationId="nameValidationMessage" />

                            <div class="mt-4">
                                <x-forms.input label="Duration (minutes)" name="duration" id="duration" type="number"
                                    required="true" min="5" errorId="durationError" />
                            </div>

                            <div class="mt-4">
                                <x-forms.input label="Price" name="price" id="price" type="number" required="true"
                                    min="0" step="0.01" errorId="priceError" />
                            </div>

                            <div class="mt-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <textarea id="description" name="description" rows="3"
                                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-100 dark:bg-gray-700"></textarea>
                                <span id="descriptionError" class="text-red-500 text-xs italic mt-1 hidden"></span>
                            </div>
                        </x-crud.modal>
                    </div>
                </main>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function data() {
            function getThemeFromLocalStorage() {
                // if user already changed the theme, use it
                if (window.localStorage.getItem('dark')) {
                    return JSON.parse(window.localStorage.getItem('dark'))
                }

                // else return their preferences
                return (
                    !!window.matchMedia &&
                    window.matchMedia('(prefers-color-scheme: dark)').matches
                )
            }

            function setThemeToLocalStorage(value) {
                window.localStorage.setItem('dark', value)
            }

            return {
                dark: getThemeFromLocalStorage(),
                toggleTheme() {
                    this.dark = !this.dark
                    setThemeToLocalStorage(this.dark)
                },
                isSideMenuOpen: false,
                toggleSideMenu() {
                    this.isSideMenuOpen = !this.isSideMenuOpen
                },
                closeSideMenu() {
                    this.isSideMenuOpen = false
                },
                isPagesMenuOpen: false, // For mobile menu dropdown
                togglePagesMenu() {
                    this.isPagesMenuOpen = !this.isPagesMenuOpen
                },
                isPagesMenuOpen2: false, // Separate state for desktop dropdown if needed
                togglePagesMenu2() {
                    this.isPagesMenuOpen2 = !this.isPagesMenuOpen2
                },
            }
        }

        $(document).ready(function() {
            // Initialize the CRUD manager
            const serviceManager = new CrudManager({
                entityName: 'Service',
                entityNamePlural: 'Services',
                routes: {
                    index: "{{ route('services.index') }}",
                    store: "{{ route('services.store') }}",
                    edit: "/admin/services/:id/edit",
                    update: "/admin/services/:id",
                    destroy: "/admin/services/:id",
                    restore: "/admin/services/:id/restore",
                    checkName: "{{ route('services.check-name') }}"
                },
                // UI Selectors
                tableSelector: '#servicesTable',
                modalSelector: '#serviceModal',
                formSelector: '#serviceForm',
                searchSelector: '#searchInput',
                perPageSelector: '#perPage',
                showDeletedSelector: '#showDeleted',
                paginationSelector: '#pagination',
                alertSelector: '#alertMessage',
                addButtonSelector: '#addServiceBtn',

                // Modal elements
                modalHeaderSelector: '#modalHeader',
                modalTitleSelector: '#modalTitle',
                saveBtnSelector: '#saveBtn',
                cancelBtnSelector: '#cancelBtn',
                closeModalSelector: '#closeModal',

                // ID field
                idField: 'uuid',
                idInputSelector: '#serviceUuid',

                // Table headers
                tableHeaders: [{
                        field: 'name',
                        name: 'Name',
                        sortable: true
                    },
                    {
                        field: 'duration',
                        name: 'Duration',
                        sortable: true
                    },
                    {
                        field: 'price',
                        name: 'Price',
                        sortable: true,
                        getter: function(service) {
                            return '€' + parseFloat(service.price).toFixed(2);
                        }
                    },
                    {
                        field: 'created_at',
                        name: 'Created At',
                        sortable: true
                    },
                    {
                        field: 'actions',
                        name: 'Actions',
                        sortable: false
                    }
                ],

                // Validation fields
                validationFields: [{
                        name: 'name',
                        validation: {
                            url: "{{ route('services.check-name') }}",
                            delay: 500,
                            minLength: 2,
                            errorMessage: 'This service name is already taken.',
                            successMessage: 'Service name is available.'
                        },
                        errorMessage: 'Please choose a different service name.'
                    },
                    {
                        name: 'duration'
                    },
                    {
                        name: 'price'
                    },
                    {
                        name: 'description'
                    }
                ],

                // Default sorting
                defaultSortField: 'created_at',
                defaultSortDirection: 'desc'
            });

            // Custom renderer for the services table to include the toggle button
            const originalRenderTable = serviceManager.renderTable;
            serviceManager.renderTable = function(data) {
                const self = this;
                const entities = data.data;
                let html = "";

                if (entities.length === 0) {
                    html =
                        `<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No services found matching your search criteria.</td></tr>`;
                } else {
                    entities.forEach((entity) => {
                        const isDeleted = entity.deleted_at !== null;
                        const rowClass = isDeleted ?
                            "bg-red-50 dark:bg-red-900 opacity-60" :
                            "";

                        html += `<tr class="${rowClass}">`;

                        // Generate cells based on table headers
                        this.tableHeaders.forEach((header) => {
                            if (header.field === "actions") {
                                // Actions column
                                html +=
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">`;
                                if (isDeleted) {
                                    // Restore button for deleted items
                                    html += `<button class="restore-btn text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" data-id="${
                                        entity[this.idField]
                                    }" title="Restore">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2a8.001 8.001 0 0015.357 2M15 15h-5" />
                                        </svg>
                                    </button>`;
                                } else {
                                    // Edit and delete buttons for active items
                                    html += `
                                    <button class="edit-btn text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3" data-id="${
                                        entity[this.idField]
                                    }" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="delete-btn text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" data-id="${
                                        entity[this.idField]
                                    }" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>`;
                                }
                                html += `</td>`;
                            } else if (
                                header.field === "created_at" ||
                                header.field === "updated_at" ||
                                header.field === "deleted_at"
                            ) {
                                // Date columns
                                let dateStr = entity[header.field] ?
                                    new Date(entity[header.field]).toLocaleString('en-GB', {
                                        day: 'numeric',
                                        month: 'numeric',
                                        year: 'numeric',
                                        hour: 'numeric',
                                        minute: 'numeric',
                                        second: 'numeric',
                                        hour12: true
                                    }) :
                                    "N/A";
                                html +=
                                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">${dateStr}</td>`;
                            } else if (header.getter) {
                                // Custom getter
                                let value = header.getter(entity);
                                html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 text-center">
                                    ${value}
                                </td>`;
                            } else {
                                // Standard data columns
                                let value = entity[header.field];
                                html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 text-center">
                                    ${value}
                                    ${
                                        header.field === "name" && isDeleted
                                            ? '<span class="ml-2 text-xs text-red-500 dark:text-red-400">(Inactive)</span>'
                                            : ""
                                    }
                                </td>`;
                            }
                        });

                        html += `</tr>`;
                    });
                }

                // Replace table content
                $(this.tableSelector).html(html);

                // Attach event handlers to buttons
                $(this.tableSelector + " .edit-btn").on("click", function() {
                    const id = $(this).data("id");
                    self.editEntity(id);
                });

                $(this.tableSelector + " .delete-btn").on("click", function() {
                    const id = $(this).data("id");
                    self.deleteEntity(id);
                });

                $(this.tableSelector + " .restore-btn").on("click", function() {
                    const id = $(this).data("id");
                    self.restoreEntity(id);
                });
            };

            // Function to show alert message
            function showAlert(alertId, message, type = 'success') {
                const $alert = $('#' + alertId);

                // Remove any existing alert classes
                $alert.removeClass('bg-green-100 border-green-400 text-green-700');
                $alert.removeClass('bg-red-100 border-red-400 text-red-700');
                $alert.removeClass('bg-yellow-100 border-yellow-400 text-yellow-700');
                $alert.removeClass('bg-blue-100 border-blue-400 text-blue-700');

                // Add appropriate classes based on alert type
                if (type === 'success') {
                    $alert.addClass('bg-green-100 border-green-400 text-green-700');
                } else if (type === 'error') {
                    $alert.addClass('bg-red-100 border-red-400 text-red-700');
                } else if (type === 'warning') {
                    $alert.addClass('bg-yellow-100 border-yellow-400 text-yellow-700');
                } else if (type === 'info') {
                    $alert.addClass('bg-blue-100 border-blue-400 text-blue-700');
                }

                $alert.find('.alert-message').text(message);
                $alert.show();

                // Auto-hide the alert after 5 seconds
                setTimeout(function() {
                    $alert.fadeOut('slow');
                }, 5000);
            }
        });
    </script>
@endpush
