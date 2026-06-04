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
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">Gallery Images</h2>

                    <x-crud.filter-bar searchPlaceholder="Search images..." :perPageOptions="[5,10,25,50]" :showInactiveToggle="false" entityName="Gallery Image" addButtonId="addGalleryImageBtn" />

                    <x-crud.alert type="success" id="alertMessage" />

                    <div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
                        <div class="w-full overflow-x-auto">
                            <table class="w-full whitespace-no-wrap">
                                <thead>
                                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                        <th class="px-4 py-3 text-center">Preview</th>
                                        <th class="px-4 py-3 text-center cursor-pointer sort-header" data-field="type">Type <span class="sort-icon"></span></th>
                                        <th class="px-4 py-3 text-center cursor-pointer sort-header" data-field="sort_order">Order <span class="sort-icon"></span></th>
                                        <th class="px-4 py-3 text-center cursor-pointer sort-header" data-field="created_at">Created At <span class="sort-icon"></span></th>
                                        <th class="px-4 py-3 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="galleryImagesTable" class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                                    <tr id="loadingRow" style="display: none;">
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading gallery images...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="pagination" class="px-4 py-3 flex items-center justify-between border-t bg-gray-50 dark:bg-gray-800 dark:border-gray-700"></div>
                    </div>

                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md flex items-start">
                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            <strong>Información:</strong> Puede reordenar los archivos arrastrando las filas de esta tabla o las vistas previas dentro del modal de carga. El orden se guarda automáticamente al soltar el elemento en la tabla.
                        </p>
                    </div>

                </div>
            </main>

            {{-- Add Modal --}}
            <x-crud.modal id="galleryImageModal" title="Gallery Image" formId="galleryImageForm"
                entityIdField="galleryImageUuid">
                <x-gallery-dropzone id="galleryDropzone" />
                <span id="filesError" class="error-message hidden text-red-500 text-xs italic mt-1"></span>
            </x-crud.modal>

            {{-- Replace Modal --}}
            <div id="replaceModal" x-data="replaceModalData()" x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black bg-opacity-50" @click="close()"></div>
                <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full relative z-10">
                    <div class="px-4 py-3 flex justify-between items-center border-b border-gray-200 dark:border-gray-600 bg-blue-500 dark:bg-blue-600">
                        <h3 class="w-full text-lg leading-6 font-medium text-white text-center">Replace File</h3>
                        <button type="button" @click="close()"
                            class="text-white bg-transparent hover:bg-blue-600 dark:hover:bg-blue-700 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submit()">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <input type="hidden" x-model="uuid">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select New File</label>
                                <input type="file" accept="image/*,video/*" @change="handleFile($event)"
                                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-100 dark:bg-gray-700">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Images (JPEG, PNG, WEBP) and videos (MP4, MOV, AVI). Max 50MB.</p>
                                <span x-show="error.length > 0" x-text="error" class="text-red-500 text-xs italic mt-1 block"></span>
                            </div>
                            <div x-show="preview" class="flex justify-center mt-4">
                                <img x-show="previewType === 'image'" :src="preview" class="h-40 rounded object-cover border dark:border-gray-600">
                                <video x-show="previewType === 'video'" :src="preview" class="h-40 rounded object-cover border dark:border-gray-600" controls muted preload="metadata"></video>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-600">
                            <button type="submit" :disabled="loading || !file"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-show="!loading">Replace File</span>
                                <span x-show="loading">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Uploading...
                                </span>
                            </button>
                            <button type="button" @click="close()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist/css/glightbox.min.css" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist/js/glightbox.min.js"></script>
    <script>
        function openGalleryPreview(src, type) {
            const lightbox = GLightbox({
                elements: [{
                    href: src,
                    type: type
                }],
                touchNavigation: true,
                loop: false,
                zoomable: true
            });
            lightbox.open();
        }

        function replaceModalData() {
            return {
                open: false,
                uuid: null,
                file: null,
                preview: null,
                previewType: null,
                error: '',
                loading: false,

                init() {
                    this.$watch('open', value => {
                        document.body.style.overflow = value ? 'hidden' : '';
                    });
                },

                show(uuid) {
                    this.uuid = uuid;
                    this.file = null;
                    this.preview = null;
                    this.previewType = null;
                    this.error = '';
                    this.loading = false;
                    this.open = true;
                },

                close() {
                    this.open = false;
                    setTimeout(() => {
                        this.uuid = null;
                        this.file = null;
                        this.preview = null;
                        this.previewType = null;
                        this.error = '';
                    }, 200);
                },

                handleFile(e) {
                    const f = e.target.files[0];
                    if (!f) return;
                    this.file = f;
                    this.error = '';

                    if (f.type.startsWith('image/')) {
                        this.preview = URL.createObjectURL(f);
                        this.previewType = 'image';
                    } else if (f.type.startsWith('video/')) {
                        this.preview = URL.createObjectURL(f);
                        this.previewType = 'video';
                    } else {
                        this.preview = null;
                        this.previewType = null;
                    }
                },

                submit() {
                    if (!this.file || !this.uuid) return;
                    this.loading = true;
                    this.error = '';

                    const formData = new FormData();
                    formData.append('files[]', this.file);
                    formData.append('_method', 'PUT');

                    fetch(`/gallery-images/${this.uuid}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.loading = false;
                        if (data.success) {
                            this.close();
                            Swal.fire({ icon: 'success', title: 'Success!', text: data.message, confirmButtonColor: '#3B82F6' });
                            if (typeof galleryManager !== 'undefined') galleryManager.loadEntities();
                        } else {
                            this.error = data.message || 'Error replacing file.';
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.error = 'Network error. Please try again.';
                    });
                }
            };
        }

        $(document).ready(function() {
            // Helper to show alerts
            function showAlert(elementId, message, type = 'success') {
                const $alert = $('#' + elementId);
                $alert.removeClass('hidden bg-green-100 text-green-700 bg-red-100 text-red-700');

                if (type === 'success') {
                    $alert.addClass('bg-green-100 text-green-700');
                } else {
                    $alert.addClass('bg-red-100 text-red-700');
                }

                $alert.text(message).removeClass('hidden');
                setTimeout(() => { $alert.addClass('hidden'); }, 5000);
            }

            // Initialize Sortable for reordering rows
            let sortableInstance = null;

            window.galleryManager = new CrudManager({
                entityName: 'Gallery Image',
                entityNamePlural: 'Gallery Images',
                routes: {
                    index: '{{ route('gallery-images.index') }}',
                    store: '{{ route('gallery-images.store') }}',
                    edit: '{{ route('gallery-images.edit', ['uuid' => ':id']) }}',
                    update: '{{ route('gallery-images.update', ['uuid' => ':id']) }}',
                    destroy: '{{ route('gallery-images.destroy', ['uuid' => ':id']) }}',
                    reorder: '{{ route('gallery-images.reorder') }}',
                },
                tableSelector: '#galleryImagesTable',
                modalSelector: '#galleryImageModal',
                formSelector: '#galleryImageForm',
                searchSelector: '#searchInput',
                perPageSelector: '#perPage',
                showDeletedSelector: '#showDeleted',
                paginationSelector: '#pagination',
                alertSelector: '#alertMessage',
                addButtonSelector: '#addGalleryImageBtn',
                modalHeaderSelector: '#modalHeader',
                modalTitleSelector: '#modalTitle',
                saveBtnSelector: '#saveBtn',
                cancelBtnSelector: '#cancelBtn',
                closeModalSelector: '#closeModal',
                idField: 'uuid',
                idInputSelector: '#galleryImageUuid',
                defaultSortField: 'sort_order',
                defaultSortDirection: 'asc',
                tableHeaders: [
                    {
                        field: 'file_path',
                        label: 'Preview',
                        getter: function(entity) {
                            const src = '/storage-gallery/' + entity.file_path;
                            const type = entity.type === 'video' ? 'video' : 'image';
                            if (entity.type === 'video') {
                                return `<div class="flex justify-center cursor-pointer hover:opacity-80 transition" onclick="openGalleryPreview('${src}', '${type}')"><video src="${src}" class="h-16 rounded object-cover pointer-events-none" muted preload="metadata"></video></div>`;
                            } else {
                                return `<div class="flex justify-center cursor-pointer hover:opacity-80 transition" onclick="openGalleryPreview('${src}', '${type}')"><img src="${src}" class="h-16 rounded object-cover pointer-events-none" alt="Gallery image"></div>`;
                            }
                        }
                    },
                    { field: 'type', label: 'Type' },
                    { field: 'sort_order', label: 'Order' },
                    { field: 'created_at', label: 'Created At' },
                    { field: 'actions', label: 'Actions' },
                ],
                validationFields: []
            });

            // Override submitForm to use Alpine dropzone for ADD mode
            const originalSubmit = galleryManager.submitForm.bind(galleryManager);
            galleryManager.submitForm = function() {
                const self = this;
                const entityId = $(this.idInputSelector).val();
                const isEdit = !!entityId;

                if (!isEdit) {
                    // ADD MODE - get files from Alpine dropzone
                    const dropzoneEl = document.getElementById('galleryDropzone');
                    if (!dropzoneEl || !window.Alpine) {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Uploader not initialized.', confirmButtonColor: '#3B82F6' });
                        return;
                    }

                    let alpineData;
                    try {
                        alpineData = window.Alpine.$data ? window.Alpine.$data(dropzoneEl) : dropzoneEl._x_dataStack[0];
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Unable to access uploader.', confirmButtonColor: '#3B82F6' });
                        return;
                    }

                    const files = alpineData.getFiles ? alpineData.getFiles() : [];
                    if (!files || files.length === 0) {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Please select at least one file to upload.', confirmButtonColor: '#3B82F6' });
                        return;
                    }

                    const formData = new FormData();
                    files.forEach(file => formData.append('files[]', file));

                    const saveBtn = $(this.saveBtnSelector);
                    const originalHtml = saveBtn.html();
                    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><span>Uploading...</span>');

                    $.ajax({
                        url: self.routes.store,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function(response) {
                            self.closeModal();
                            Swal.fire({ icon: 'success', title: 'Success!', text: response.message, confirmButtonColor: '#3B82F6' });
                            self.loadEntities();
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                Object.keys(errors).forEach(field => {
                                    $(`#${field}Error`).removeClass('hidden').text(errors[field][0]);
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'Upload failed.', confirmButtonColor: '#3B82F6' });
                            }
                        },
                        complete: function() {
                            saveBtn.prop('disabled', false).html(originalHtml);
                        }
                    });
                } else {
                    // EDIT MODE fallback (should not be used now that replace modal exists)
                    originalSubmit();
                }
            };

            // Override closeModal to clear dropzone
            const originalCloseModal = galleryManager.closeModal.bind(galleryManager);
            galleryManager.closeModal = function() {
                originalCloseModal();
                const dropzoneEl = document.getElementById('galleryDropzone');
                if (dropzoneEl && window.Alpine) {
                    try {
                        const alpineData = window.Alpine.$data ? window.Alpine.$data(dropzoneEl) : dropzoneEl._x_dataStack[0];
                        if (alpineData && alpineData.clear) alpineData.clear();
                    } catch (e) { /* ignore */ }
                }
            };

            // Override editEntity to open REPLACE modal instead
            galleryManager.editEntity = function(id) {
                const replaceModal = document.getElementById('replaceModal');
                if (replaceModal && window.Alpine) {
                    try {
                        const data = window.Alpine.$data ? window.Alpine.$data(replaceModal) : replaceModal._x_dataStack[0];
                        if (data && data.show) data.show(id);
                    } catch (e) {
                        console.error('Error opening replace modal:', e);
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to open replace modal.', confirmButtonColor: '#3B82F6' });
                    }
                }
            };

            // Override deleteEntity to show proper SweetAlert for gallery
            const originalDelete = galleryManager.deleteEntity.bind(galleryManager);
            galleryManager.deleteEntity = function(id) {
                const self = this;
                Swal.fire({
                    title: 'Delete Gallery Image?',
                    text: 'This image will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Yes, delete it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: self.formatRoute(self.routes.destroy, { id: id }),
                            type: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, confirmButtonColor: '#3B82F6' });
                                    self.loadEntities();
                                }
                            },
                            error: function() {
                                Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to delete gallery image.', confirmButtonColor: '#3B82F6' });
                            }
                        });
                    }
                });
            };

            // Override renderTable to attach Sortable after rendering
            const originalRenderTable = galleryManager.renderTable.bind(galleryManager);
            galleryManager.renderTable = function(data) {
                originalRenderTable(data);

                if (sortableInstance) sortableInstance.destroy();

                const entities = data.data;
                if (!entities || entities.length === 0) return;

                const tbody = document.querySelector(this.tableSelector);
                if (!tbody) return;

                // Attach data-uuid to rows for reorder
                $(this.tableSelector + ' tr').each(function(index) {
                    if (entities[index]) {
                        $(this).attr('data-uuid', entities[index].uuid);
                    }
                });

                sortableInstance = new Sortable(tbody, {
                    animation: 150,
                    ghostClass: 'bg-blue-100',
                    onEnd: function(evt) {
                        const rows = tbody.querySelectorAll('tr');
                        const uuids = Array.from(rows).map(row => row.getAttribute('data-uuid')).filter(Boolean);

                        if (uuids.length === 0) return;

                        $.ajax({
                            url: '{{ route('gallery-images.reorder') }}',
                            type: 'POST',
                            data: { items: uuids },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            },
                            success: function(response) {
                                if (response.success) {
                                    showAlert('alertMessage', response.message, 'success');
                                }
                            },
                            error: function() {
                                showAlert('alertMessage', 'Error saving order.', 'error');
                            }
                        });
                    }
                });
            };
        });
    </script>
@endpush
