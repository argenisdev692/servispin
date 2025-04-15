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
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <a href="{{ route('admin.availability-exceptions.index') }}">
                                    <span>Availability Exceptions Management</span>
                                </a>
                            </div>
                        </div>

                        <!-- Filter Bar Component -->
                        <x-crud.filter-bar entityName="Exception" :showSearchBar="true" :showInactiveToggle="true" :showPerPage="true"
                            :perPageOptions="[5, 10, 15, 25, 50]" :defaultPerPage="10" addButtonId="addExceptionBtn" />

                        <!-- Additional Filters -->
                        <div class="mb-4 flex flex-wrap items-end gap-4">
                            <!-- Date Range Filter -->
                            <div>
                                <label for="date_start"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From
                                    Date</label>
                                <input type="date" id="date_start" name="date_start"
                                    class="mt-1 block w-40 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300">
                            </div>
                            <div>
                                <label for="date_end"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                                <input type="date" id="date_end" name="date_end"
                                    class="mt-1 block w-40 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="is_available"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select id="is_available" name="is_available"
                                    class="mt-1 block w-40 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">Any status</option>
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>
                                </select>
                            </div>

                            <!-- Filter Button -->
                            <div>
                                <button id="applyFilters"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    Apply Filters
                                </button>
                                <button id="resetFilters"
                                    class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2a8.001 8.001 0 0015.357 2M15 15h-5" />
                                    </svg>
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Alert Messages -->
                        <x-crud.alert id="alertMessage" :show="false" />

                        <!-- Data Table -->
                        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <!-- Sortable Date Column -->
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header"
                                            data-field="date">
                                            Date
                                            <span class="sort-icon"></span>
                                        </th>
                                        <!-- Reason Column -->
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header"
                                            data-field="reason">
                                            Reason
                                            <span class="sort-icon"></span>
                                        </th>
                                        <!-- Status Column -->
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header"
                                            data-field="is_available">
                                            Status
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
                                <tbody id="exceptionsTable"
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
                                            Loading exceptions...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div id="pagination" class="mt-4 flex justify-between items-center">
                            <!-- Pagination info will be inserted here by JavaScript -->
                        </div>

                        <!-- Exception Modal Component -->
                        <x-crud.modal id="exceptionModal" title="Add Exception" colorType="green" formId="exceptionForm"
                            entityIdField="exceptionUuid">
                            <div class="mb-4">
                                <x-forms.input label="Date" name="date" id="date" type="date"
                                    required="true" errorId="dateError" validationId="dateValidationMessage" />
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Availability Status
                                </label>
                                <div class="mt-2 flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-blue-600" name="is_available"
                                            value="0" checked>
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Unavailable
                                            (Holiday/Exception)</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-green-600" name="is_available"
                                            value="1">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Available (Override)</span>
                                    </label>
                                </div>
                                <span id="is_availableError" class="text-red-500 text-xs italic mt-1 hidden"></span>
                            </div>

                            <div class="mb-4">
                                <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Reason
                                </label>
                                <textarea id="reason" name="reason" rows="3" required
                                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-100 dark:bg-gray-700"></textarea>
                                <span id="reasonError" class="text-red-500 text-xs italic mt-1 hidden"></span>
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
            // Function to format date in Month Day, Year format (Abril 12, 2025)
            function formatDate(dateString) {
                if (!dateString) return 'N/A';

                const date = new Date(dateString);
                const options = {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                };
                // Format as "25 de Diciembre de 2025"
                let formattedDate = date.toLocaleDateString('es-ES', options);

                // Capitalize the month
                formattedDate = formattedDate.replace(/\b\w/g, function(l) {
                    return l.toUpperCase();
                });

                return formattedDate;
            }

            // Function to truncate text with ellipsis
            function truncateText(text, maxLength = 50) {
                if (!text) return '';
                if (text.length <= maxLength) return text;
                return text.substring(0, maxLength) + '...';
            }

            // Initialize the CRUD manager
            const exceptionManager = new CrudManager({
                entityName: 'Exception',
                entityNamePlural: 'Exceptions',
                routes: {
                    index: "{{ route('admin.availability-exceptions.index') }}",
                    store: "{{ route('admin.availability-exceptions.store') }}",
                    edit: "/admin/availability-exceptions/:id/edit",
                    update: "/admin/availability-exceptions/:id",
                    destroy: "/admin/availability-exceptions/:id",
                    restore: "/admin/availability-exceptions/:id/restore",
                    checkName: "{{ route('admin.availability-exceptions.check-date') }}"
                },
                // UI Selectors
                tableSelector: '#exceptionsTable',
                modalSelector: '#exceptionModal',
                formSelector: '#exceptionForm',
                searchSelector: '#searchInput',
                perPageSelector: '#perPage',
                showDeletedSelector: '#showDeleted',
                paginationSelector: '#pagination',
                alertSelector: '#alertMessage',
                addButtonSelector: '#addExceptionBtn',

                // Modal elements
                modalHeaderSelector: '#modalHeader',
                modalTitleSelector: '#modalTitle',
                saveBtnSelector: '#saveBtn',
                cancelBtnSelector: '#cancelBtn',
                closeModalSelector: '#closeModal',

                // ID field
                idField: 'uuid',
                idInputSelector: '#exceptionUuid',

                // Table headers
                tableHeaders: [{
                        field: 'date',
                        name: 'Date',
                        sortable: true,
                        getter: function(exception) {
                            return formatDate(exception.date);
                        }
                    },
                    {
                        field: 'reason',
                        name: 'Reason',
                        sortable: true,
                        getter: function(exception) {
                            return truncateText(exception.reason, 50);
                        }
                    },
                    {
                        field: 'is_available',
                        name: 'Status',
                        sortable: true,
                        getter: function(exception) {
                            return exception.is_available ?
                                '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">Available</span>' :
                                '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">Unavailable</span>';
                        }
                    },
                    {
                        field: 'created_at',
                        name: 'Created At',
                        sortable: true,
                        getter: function(exception) {
                            if (!exception.created_at) return 'N/A';
                            return new Date(exception.created_at).toLocaleString('es-ES', {
                                day: 'numeric',
                                month: 'numeric',
                                year: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric',
                                second: 'numeric',
                                hour12: true
                            });
                        }
                    },
                    {
                        field: 'actions',
                        name: 'Actions',
                        sortable: false
                    }
                ],

                // Validation fields
                validationFields: [{
                        name: 'date',
                        validation: {
                            url: "{{ route('admin.availability-exceptions.check-date') }}",
                            delay: 500,
                            errorMessage: 'This date already has an exception defined.',
                            successMessage: 'This date is available.'
                        },
                        errorMessage: 'Please choose a different date.'
                    },
                    {
                        name: 'is_available'
                    },
                    {
                        name: 'reason'
                    }
                ],

                // Default sorting
                defaultSortField: 'date',
                defaultSortDirection: 'desc'
            });

            // Custom form population for radio buttons
            const originalEditEntity = exceptionManager.editEntity;
            exceptionManager.editEntity = function(id) {
                const self = this;
                console.log("Edit entity called with ID:", id);
                console.log("Edit URL:", self.formatRoute(self.routes.edit, {
                    id: id
                }));

                $.ajax({
                    url: self.formatRoute(self.routes.edit, {
                        id: id
                    }),
                    type: "GET",
                    success: function(response) {
                        console.log("Edit response:", response);
                        if (response.success) {
                            const entity =
                                response.entity || response[self.getEntityVarName()];

                            // First, detach validation to prevent it from firing
                            self.validationFields.forEach((field) => {
                                if (field.validation && field.validation.url) {
                                    $(`#${field.name}`).off("input blur");
                                }
                            });

                            // Reset form
                            self.resetForm();

                            // Set ID first to ensure exclude_uuid works properly
                            $(self.idInputSelector).val(entity[self.idField]);

                            // Set regular field values
                            self.validationFields.forEach((field) => {
                                if (field.name !== 'is_available') {
                                    $(`#${field.name}`).val(entity[field.name]);
                                }
                            });

                            // Set radio button value based on is_available
                            $(`input[name="is_available"][value="${entity.is_available ? '1' : '0'}"]`)
                                .prop('checked', true);

                            // Reattach validation after populating form
                            self.setupValidation();

                            // Update modal
                            $(self.modalTitleSelector).text(`Edit ${self.entityName}`);
                            $(self.saveBtnSelector)
                                .find(".button-text")
                                .text(`Update ${self.entityName}`);

                            // Set modal color for edit mode
                            self.setModalColor("edit");

                            // Show modal
                            $(self.modalSelector)
                                .removeClass("hidden")
                                .addClass("flex");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Edit AJAX error:", {
                            xhr,
                            status,
                            error
                        });
                        console.error("Response text:", xhr.responseText);
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: `Failed to load ${self.entityName.toLowerCase()} data.`,
                            confirmButtonColor: "#3B82F6",
                        });
                    },
                });
            };

            // Custom form submission to handle radio buttons
            const originalSubmitForm = exceptionManager.submitForm;
            exceptionManager.submitForm = function() {
                const self = this;
                const entityId = $(this.idInputSelector).val();
                const isEdit = !!entityId;

                // Create a copy of the original function but customize the formData collection
                let hasValidationErrors = false;
                this.validationFields.forEach((field) => {
                    const msgElement = $(`#${field.name}ValidationMessage`);
                    if (msgElement.hasClass("text-red-500")) {
                        $(`#${field.name}Error`)
                            .removeClass("hidden")
                            .text(
                                field.errorMessage ||
                                `Please choose a different ${field.name}.`
                            );
                        hasValidationErrors = true;
                    }
                });

                if (hasValidationErrors) {
                    return;
                }

                // Reset error messages
                $(`${this.formSelector} .error-message`).addClass("hidden").text("");

                // Collect form data with special handling for radio buttons
                const formData = {};
                this.validationFields.forEach((field) => {
                    if (field.name === 'is_available') {
                        formData[field.name] = $(`input[name="${field.name}"]:checked`).val();
                    } else {
                        formData[field.name] = $(`#${field.name}`).val();
                    }
                });

                // Save original button content
                const saveBtn = $(this.saveBtnSelector);
                const originalButtonContent = saveBtn.html();

                // Show loading spinner
                saveBtn
                    .prop("disabled", true)
                    .html(
                        '<i class="fas fa-spinner fa-spin mr-2"></i><span>Saving...</span>'
                    );

                // Send request
                $.ajax({
                    url: isEdit ?
                        self.formatRoute(self.routes.update, {
                            id: entityId
                        }) : self.routes.store,
                    type: isEdit ? "PUT" : "POST",
                    data: formData,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function(response) {
                        self.closeModal();

                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: response.message,
                            confirmButtonColor: "#3B82F6",
                        });

                        self.loadEntities();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;

                            Object.keys(errors).forEach((field) => {
                                $(`#${field}Error`)
                                    .removeClass("hidden")
                                    .text(errors[field][0]);
                            });
                        } else {
                            // Other error
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: "Something went wrong. Please try again.",
                                confirmButtonColor: "#3B82F6",
                            });
                        }
                    },
                    complete: function() {
                        // Restore original button state
                        saveBtn.prop("disabled", false).html(originalButtonContent);
                    },
                });
            };

            // Override loadEntities method to include additional filters
            const originalLoadEntities = exceptionManager.loadEntities;
            exceptionManager.loadEntities = function() {
                const dateStart = $('#date_start').val();
                const dateEnd = $('#date_end').val();
                const isAvailable = $('#is_available').val();

                // Make AJAX request with all filters
                $.ajax({
                    url: this.routes.index,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        Accept: "application/json",
                    },
                    data: {
                        page: this.currentPage,
                        per_page: this.perPage,
                        sort_field: this.sortField,
                        sort_direction: this.sortDirection,
                        search: this.searchTerm,
                        show_deleted: this.showDeleted ? "true" : "false",
                        date_start: dateStart,
                        date_end: dateEnd,
                        is_available: isAvailable
                    },
                    beforeSend: () => {
                        $(this.tableSelector + " #loadingRow").show();
                    },
                    success: (response) => {
                        this.renderTable(response);
                        this.renderPagination(response);
                    },
                    error: (xhr, status, error) => {
                        console.error("Load entities error:", error);
                        console.error("Response:", xhr.responseText);

                        // Show error message in table
                        $(this.tableSelector).html(`
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-red-500">
                                    Error loading exceptions. Please check the console for details.
                                </td>
                            </tr>
                        `);
                    },
                    complete: () => {
                        $(this.tableSelector + " #loadingRow").hide();
                    }
                });
            };

            // Handle additional filters
            $('#applyFilters').on('click', function() {
                // Validate dates before applying
                if (validateDates()) {
                    exceptionManager.currentPage = 1; // Reset to first page when applying filters
                    exceptionManager.loadEntities();
                }
            });

            $('#resetFilters').on('click', function() {
                // Reset date inputs and status select
                $('#date_start').val('');
                $('#date_end').val('');
                $('#is_available').val('');

                // Reset min/max attributes
                $('#date_start').removeAttr('max');
                $('#date_end').removeAttr('min');

                exceptionManager.currentPage = 1; // Reset to first page
                exceptionManager.loadEntities();
            });

            // Setup date constraints
            setupDateConstraints();

            // Function to set up date input constraints
            function setupDateConstraints() {
                // Handle start date changes
                $('#date_start').on('change', function() {
                    const startDate = $(this).val();
                    if (startDate) {
                        // Set the min value of end date to the selected start date
                        $('#date_end').attr('min', startDate);

                        // If end date is before start date, clear it
                        const endDate = $('#date_end').val();
                        if (endDate && endDate < startDate) {
                            $('#date_end').val('');
                        }
                    } else {
                        // If start date is cleared, remove the min constraint
                        $('#date_end').removeAttr('min');
                    }
                });

                // Handle end date changes
                $('#date_end').on('change', function() {
                    const endDate = $(this).val();
                    if (endDate) {
                        // Set the max value of start date to the selected end date
                        $('#date_start').attr('max', endDate);

                        // If start date is after end date, clear it
                        const startDate = $('#date_start').val();
                        if (startDate && startDate > endDate) {
                            $('#date_start').val('');
                        }
                    } else {
                        // If end date is cleared, remove the max constraint
                        $('#date_start').removeAttr('max');
                    }
                });
            }

            // Function to validate dates before submitting
            function validateDates() {
                const startDate = $('#date_start').val();
                const endDate = $('#date_end').val();

                // If both dates are set, ensure end date is not before start date
                if (startDate && endDate && endDate < startDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Date Error',
                        text: 'The end date cannot be earlier than the start date.',
                        confirmButtonColor: '#3B82F6'
                    });
                    return false;
                }

                return true;
            }
        });
    </script>
@endpush
