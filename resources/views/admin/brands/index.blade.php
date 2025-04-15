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
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 8V5a2 2 0 012-2z">
                                    </path>
                                </svg>
                                <a href="{{ route('brands.index') }}">
                                    <span>Brands Management</span>
                                </a>
                            </div>
                        </div>

                        <!-- Filter Bar Component -->
                        <x-crud.filter-bar entityName="Brand" :showSearchBar="true" :showInactiveToggle="true" :showPerPage="true"
                            :perPageOptions="[5, 10, 15, 25, 50]" :defaultPerPage="10" addButtonId="addBrandBtn" />

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
                                <tbody id="brandsTable"
                                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <!-- Table content will be loaded here by JavaScript -->
                                    <tr id="loadingRow">
                                        <td colspan="3" class="px-6 py-4 text-center">
                                            <svg class="animate-spin h-5 w-5 mr-3 text-blue-500 inline-block"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            Loading brands...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div id="pagination" class="mt-4 flex justify-between items-center">
                            <!-- Pagination info will be inserted here by JavaScript -->
                        </div>

                        <!-- Brand Modal Component -->
                        <x-crud.modal id="brandModal" title="Add Brand" colorType="green" formId="brandForm"
                            entityIdField="brandUuid">
                            <x-forms.input label="Brand Name" name="name" id="name" type="text" required="true"
                                style="text-transform: capitalize;" errorId="nameError"
                                validationId="nameValidationMessage" />
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
            const brandManager = new CrudManager({
                entityName: 'Brand',
                entityNamePlural: 'Brands',
                routes: {
                    index: "{{ route('brands.index') }}",
                    store: "{{ route('brands.store') }}",
                    edit: "/brands/:id/edit",
                    update: "/brands/:id",
                    destroy: "/brands/:id",
                    restore: "/brands/:id/restore",
                    checkName: "{{ route('brands.check-name') }}"
                },
                // UI Selectors
                tableSelector: '#brandsTable',
                modalSelector: '#brandModal',
                formSelector: '#brandForm',
                searchSelector: '#searchInput',
                perPageSelector: '#perPage',
                showDeletedSelector: '#showDeleted',
                paginationSelector: '#pagination',
                alertSelector: '#alertMessage',
                addButtonSelector: '#addBrandBtn',

                // Modal elements
                modalHeaderSelector: '#modalHeader',
                modalTitleSelector: '#modalTitle',
                saveBtnSelector: '#saveBtn',
                cancelBtnSelector: '#cancelBtn',
                closeModalSelector: '#closeModal',

                // ID field
                idField: 'uuid',
                idInputSelector: '#brandUuid',

                // Table headers
                tableHeaders: [{
                        field: 'name',
                        name: 'Name',
                        sortable: true
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
                        url: "{{ route('brands.check-name') }}",
                        delay: 500,
                        minLength: 2,
                        errorMessage: 'This brand name is already taken.',
                        successMessage: 'Brand name is available.'
                    },
                    errorMessage: 'Please choose a different brand name.'
                }],

                // Default sorting
                defaultSortField: 'created_at',
                defaultSortDirection: 'desc'
            });

            // Function to show alert message
            function showAlertMessage(message, type = 'success') {
                showAlert('alertMessage', message, type);
            }

            // Function to render brands table
            function renderBrandsTable(data) {
                const brands = data.data;
                let html = '';

                if (brands.length === 0) {
                    html =
                        `<tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No brands found matching your search criteria.</td></tr>`;
                } else {
                    brands.forEach(brand => {
                        const isDeleted = brand.deleted_at !== null;
                        const rowClass = isDeleted ? 'bg-red-50 dark:bg-red-900 opacity-60' : '';
                        const uuid = brand.uuid || ''; // Ensure uuid is always a string

                        // Skip rendering action buttons if UUID is missing or invalid
                        const hasValidUuid = uuid && uuid !== 'undefined' && uuid.trim() !== '';

                        html += `
                            <tr class="${rowClass}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 text-center">
                                    ${brand.name}
                                    ${isDeleted ? '<span class="ml-2 text-xs text-red-500 dark:text-red-400">(Inactive)</span>' : ''}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    ${new Date(brand.created_at).toLocaleString()}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    ${isDeleted ? 
                                        `${hasValidUuid ? `<button class="restore-btn text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" data-uuid="${uuid}" title="Restore">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m-15.357-2a8.001 8.001 0 0015.357 2M15 15h-5" />
                                                    </svg>
                                                </button>` : `<span class="text-gray-400 italic">No actions available</span>`}` 
                                        : 
                                        `${hasValidUuid ? `<button class="edit-btn text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3" data-uuid="${uuid}" title="Edit">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <button class="delete-btn text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" data-uuid="${uuid}" title="Delete">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>` : `<span class="text-gray-400 italic">No actions available</span>`}`
                                    }
                                </td>
                            </tr>`;
                    });
                }

                // Replace table content
                $('#brandsTable').html(html);

                // Attach event handlers to buttons
                $('.edit-btn').on('click', function() {
                    const uuid = $(this).data('uuid'); // Use .data() if render function is correct
                    editBrand(uuid);
                });

                // Add event handler for delete buttons
                $('.delete-btn').on('click', function() {
                    const uuid = $(this).data('uuid');
                    // More robust way to get the brand name
                    const row = $(this).closest('tr');
                    const brandNameCell = row.find('td:first');
                    // Get text without the "(Inactive)" part
                    let brandName = brandNameCell.clone().children().remove().end().text().trim();

                    console.log("Brand to delete:", brandName); // For debugging

                    // Validate UUID before proceeding
                    if (!uuid || uuid === 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Unable to identify the brand to delete. Please refresh the page and try again.',
                            confirmButtonColor: '#3B82F6'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Delete Brand?',
                        text: `Are you sure you want to delete the brand "${brandName}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/brands/${uuid}`,
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted!',
                                            text: `Brand "${brandName}" has been moved to trash.`,
                                            confirmButtonColor: '#3B82F6'
                                        });
                                        brandManager.loadItems();
                                    }
                                },
                                error: function(xhr) {
                                    const errorMsg = xhr.responseJSON && xhr
                                        .responseJSON.message ?
                                        xhr.responseJSON.message :
                                        `Failed to delete brand "${brandName}".`;

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: errorMsg,
                                        confirmButtonColor: '#3B82F6'
                                    });
                                }
                            });
                        }
                    });
                });

                // Add event handler for restore buttons
                $('.restore-btn').on('click', function() {
                    const uuid = $(this).data('uuid');
                    // More robust way to get the brand name
                    const row = $(this).closest('tr');
                    const brandNameCell = row.find('td:first');
                    // Get text without the "(Inactive)" part
                    let brandName = brandNameCell.clone().children().remove().end().text().trim();

                    console.log("Brand to restore:", brandName); // For debugging

                    // Validate UUID before proceeding
                    if (!uuid || uuid === 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Unable to identify the brand to restore. Please refresh the page and try again.',
                            confirmButtonColor: '#3B82F6'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Restore Brand?',
                        text: `Are you sure you want to restore the brand "${brandName}"?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Yes, restore it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/brands/${uuid}/restore`,
                                type: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Restored!',
                                            text: `Brand "${brandName}" has been restored.`,
                                            confirmButtonColor: '#3B82F6'
                                        });
                                        brandManager.loadItems();
                                    }
                                },
                                error: function(xhr) {
                                    const errorMsg = xhr.responseJSON && xhr
                                        .responseJSON.message ?
                                        xhr.responseJSON.message :
                                        `Failed to restore brand "${brandName}".`;

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: errorMsg,
                                        confirmButtonColor: '#3B82F6'
                                    });
                                }
                            });
                        }
                    });
                });
            }

            // Function to edit brand
            function editBrand(uuid) {
                $.ajax({
                    url: `/brands/${uuid}/edit`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const brand = response.brand;

                            // Fill form
                            $('#brandUuid').val(brand.uuid);
                            $('#name').val(brand.name);

                            // Update modal
                            $('#modalTitle').text('Edit Brand');
                            $('#saveBtn').find('.button-text').text('Update Brand');

                            // Update colors for edit mode
                            $('#modalHeader').removeClass('bg-green-500 dark:bg-green-600').addClass(
                                'bg-blue-500 dark:bg-blue-600');
                            $('#closeModal').removeClass('hover:bg-green-600 dark:hover:bg-green-700')
                                .addClass('hover:bg-blue-600 dark:hover:bg-blue-700');
                            $('#saveBtn').removeClass(
                                    'bg-green-600 hover:bg-green-700 focus:ring-green-500')
                                .addClass('bg-blue-600 hover:bg-blue-700 focus:ring-blue-500');

                            $('#brandModal').removeClass('hidden').addClass('flex');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load brand data.',
                            confirmButtonColor: '#3B82F6'
                        });
                    }
                });
            }
        });
    </script>
@endpush
