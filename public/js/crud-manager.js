/**
 * CRUD Manager Class
 * A reusable JavaScript class for managing CRUD operations
 */
class CrudManager {
    /**
     * Constructor
     * @param {Object} options - Configuration options
     */
    constructor(options) {
        // Required options
        this.entityName = options.entityName || "Item";
        this.entityNamePlural =
            options.entityNamePlural || this.entityName + "s";
        this.routes = options.routes || {};

        // Selectors
        this.tableSelector = options.tableSelector || "#dataTable";
        this.modalSelector = options.modalSelector || "#entityModal";
        this.formSelector = options.formSelector || "#entityForm";
        this.searchSelector = options.searchSelector || "#searchInput";
        this.perPageSelector = options.perPageSelector || "#perPage";
        this.showDeletedSelector =
            options.showDeletedSelector || "#showDeleted";
        this.paginationSelector = options.paginationSelector || "#pagination";
        this.alertSelector = options.alertSelector || "#alertMessage";
        this.addButtonSelector = options.addButtonSelector || "#addEntityBtn";

        // Modal elements
        this.modalHeaderSelector =
            options.modalHeaderSelector || "#modalHeader";
        this.modalTitleSelector = options.modalTitleSelector || "#modalTitle";
        this.saveBtnSelector = options.saveBtnSelector || "#saveBtn";
        this.cancelBtnSelector = options.cancelBtnSelector || "#cancelBtn";
        this.closeModalSelector = options.closeModalSelector || "#closeModal";

        // Id field
        this.idField = options.idField || "uuid";
        this.idInputSelector = options.idInputSelector || "#entityUuid";

        // Table headers
        this.tableHeaders = options.tableHeaders || [];

        // Validation fields
        this.validationFields = options.validationFields || [];

        // Display configurations
        this.colors = options.colors || {
            add: {
                header: "bg-green-500 dark:bg-green-600",
                hover: "hover:bg-green-600 dark:hover:bg-green-700",
                button: "bg-green-600 hover:bg-green-700",
                focus: "focus:ring-green-500",
            },
            edit: {
                header: "bg-blue-500 dark:bg-blue-600",
                hover: "hover:bg-blue-600 dark:hover:bg-blue-700",
                button: "bg-blue-600 hover:bg-blue-700",
                focus: "focus:ring-blue-500",
            },
        };

        // State
        this.currentPage = 1;
        this.perPage = $(this.perPageSelector).val() || 10;
        this.sortField = options.defaultSortField || "created_at";
        this.sortDirection = options.defaultSortDirection || "desc";
        this.searchTerm = "";
        this.showDeleted = false;

        // For real-time validation
        this.validationTimeouts = {};
        this.currentValidationChecks = {};

        // Initialize the manager
        this.init();
    }

    /**
     * Initialize the CRUD manager
     */
    init() {
        this.loadEntities();
        this.setupEventHandlers();
        this.setupValidation();
    }

    /**
     * Load entities with current filters and pagination
     */
    loadEntities() {
        const self = this;

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
            },
            beforeSend: function () {
                $(self.tableSelector + " #loadingRow").show();
                console.log(`Loading ${self.entityNamePlural}...`);
            },
            success: function (response) {
                console.log(
                    `${self.entityNamePlural} loaded successfully:`,
                    response
                );
                self.renderTable(response);
                self.renderPagination(response);
            },
            error: function (xhr, status, error) {
                console.error(
                    `Error loading ${self.entityNamePlural}:`,
                    xhr.responseText
                );
                console.error("Status:", status);
                console.error("Error:", error);
                self.showAlert(
                    `Error loading ${self.entityNamePlural}. Please check the console for details.`,
                    "error"
                );

                // Show error message in table
                $(self.tableSelector).html(`
                    <tr>
                        <td colspan="${self.tableHeaders.length}" class="px-6 py-4 text-center text-sm text-red-500">
                            Error loading ${self.entityNamePlural}. Please check the console for details.
                        </td>
                    </tr>
                `);
            },
            complete: function () {
                $(self.tableSelector + " #loadingRow").hide();
            },
        });
    }

    /**
     * Render data table
     */
    renderTable(data) {
        const self = this;
        const entities = data.data;
        let html = "";

        if (entities.length === 0) {
            html = `<tr><td colspan="${
                this.tableHeaders.length
            }" class="px-6 py-4 text-center text-sm text-gray-500">No ${this.entityNamePlural.toLowerCase()} found matching your search criteria.</td></tr>`;
        } else {
            entities.forEach((entity) => {
                const isDeleted = entity.deleted_at !== null;
                const rowClass = isDeleted
                    ? "bg-red-50 dark:bg-red-900 opacity-60"
                    : "";

                html += `<tr class="${rowClass}">`;

                // Generate cells based on table headers
                this.tableHeaders.forEach((header) => {
                    if (header.field === "actions") {
                        // Actions column
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">`;
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
                            html += `<button class="edit-btn text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3" data-id="${
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
                        let dateStr = entity[header.field]
                            ? new Date(entity[header.field]).toLocaleString()
                            : "N/A";
                        html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">${dateStr}</td>`;
                    } else {
                        // Standard data columns
                        let value = header.getter
                            ? header.getter(entity)
                            : entity[header.field];
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
        $(this.tableSelector + " .edit-btn").on("click", function () {
            const id = $(this).data("id");
            self.editEntity(id);
        });

        $(this.tableSelector + " .delete-btn").on("click", function () {
            const id = $(this).data("id");
            self.deleteEntity(id);
        });

        $(this.tableSelector + " .restore-btn").on("click", function () {
            const id = $(this).data("id");
            self.restoreEntity(id);
        });
    }

    /**
     * Render pagination controls
     */
    renderPagination(data) {
        const self = this;
        const { current_page, last_page, from, to, total } = data;

        let html = `
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Showing <span class="font-medium">${
                from || 0
            }</span> to <span class="font-medium">${
            to || 0
        }</span> of <span class="font-medium">${total}</span> results
        </div>
        <div class="flex space-x-1">`;

        // Previous button
        html += `<button class="px-3 py-1 rounded-md ${
            current_page === 1
                ? "opacity-50 cursor-not-allowed bg-gray-200 dark:bg-gray-700"
                : "bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600"
        }" ${
            current_page === 1
                ? "disabled"
                : 'data-page="' + (current_page - 1) + '"'
        }>
            Previous
        </button>`;

        // Next button
        html += `<button class="px-3 py-1 rounded-md ${
            current_page === last_page
                ? "opacity-50 cursor-not-allowed bg-gray-200 dark:bg-gray-700"
                : "bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600"
        }" ${
            current_page === last_page
                ? "disabled"
                : 'data-page="' + (current_page + 1) + '"'
        }>
            Next
        </button>`;

        html += `</div>`;

        $(this.paginationSelector).html(html);

        // Attach click handlers
        $(this.paginationSelector + " button:not([disabled])").on(
            "click",
            function () {
                self.currentPage = $(this).data("page");
                self.loadEntities();
            }
        );
    }

    /**
     * Set up event handlers
     */
    setupEventHandlers() {
        const self = this;

        // Search input
        $(this.searchSelector).on("keyup", function (e) {
            self.searchTerm = $(this).val();
            self.currentPage = 1; // Reset to first page when searching
            self.loadEntities();
        });

        // Per page dropdown
        $(this.perPageSelector).on("change", function () {
            self.perPage = $(this).val();
            self.currentPage = 1; // Reset to first page when changing per page
            self.loadEntities();
        });

        // Show deleted toggle
        $(this.showDeletedSelector).on("change", function () {
            self.showDeleted = $(this).is(":checked");
            self.currentPage = 1; // Reset to first page when changing filter
            self.loadEntities();
        });

        // Column sorting
        $(".sort-header").on("click", function () {
            const field = $(this).data("field");

            // Toggle direction if clicking on same field
            if (self.sortField === field) {
                self.sortDirection =
                    self.sortDirection === "asc" ? "desc" : "asc";
            } else {
                self.sortField = field;
                self.sortDirection = "asc";
            }

            // Update UI sort indicators
            $(".sort-header .sort-icon").html("");
            const icon = self.sortDirection === "asc" ? "↑" : "↓";
            $(this).find(".sort-icon").html(icon);

            self.loadEntities();
        });

        // Add entity button
        $(this.addButtonSelector).on("click", function () {
            self.showAddModal();
        });

        // Close modal buttons - use a more thorough approach
        $(this.closeModalSelector).on("click", function () {
            self.closeModal();
        });

        $(this.cancelBtnSelector).on("click", function () {
            self.closeModal();
        });

        // Form submission
        $(this.formSelector).on("submit", function (e) {
            e.preventDefault();
            self.submitForm();
        });
    }

    /**
     * Show modal for adding a new entity
     */
    showAddModal() {
        // First, detach validation handlers to prevent unwanted side effects
        this.validationFields.forEach((field) => {
            if (field.validation && field.validation.url) {
                $(`#${field.name}`).off("input blur");
            }
        });

        // Reset the form
        this.resetForm();

        // Make sure validation messages are completely cleared
        $(`${this.formSelector} .validation-message`)
            .addClass("hidden")
            .removeClass("text-green-500 text-red-500 text-gray-500")
            .text("");

        // Update modal text
        $(this.modalTitleSelector).text(`Create New ${this.entityName}`);
        $(this.saveBtnSelector)
            .find(".button-text")
            .text(`Create ${this.entityName}`);

        // Set modal header color for add mode
        this.setModalColor("add");

        // Reattach validation handlers after form is cleared
        this.setupValidation();

        // Show the modal
        $(this.modalSelector).removeClass("hidden").addClass("flex");
    }

    /**
     * Close the modal
     */
    closeModal() {
        const self = this;

        // First, unbind validation handlers to prevent any unwanted side effects
        self.validationFields.forEach((field) => {
            if (field.validation && field.validation.url) {
                $(`#${field.name}`).off("input blur");

                // Immediately clear any validation messages
                $(`#${field.name}ValidationMessage`)
                    .addClass("hidden")
                    .removeClass("text-green-500 text-red-500 text-gray-500")
                    .text("");

                // Clear any error messages
                $(`#${field.name}Error`).addClass("hidden").text("");
            }
        });

        // Reset the form
        self.resetForm();

        // Hide the modal
        $(this.modalSelector).addClass("hidden").removeClass("flex");

        // Reset state data
        $(this.idInputSelector).val("");
        this.currentValidationChecks = {};

        // Clear any timeouts
        Object.keys(this.validationTimeouts).forEach((field) => {
            if (this.validationTimeouts[field]) {
                clearTimeout(this.validationTimeouts[field]);
                this.validationTimeouts[field] = null;
            }
        });
    }

    /**
     * Reset the form
     */
    resetForm() {
        $(this.formSelector)[0].reset();
        $(this.idInputSelector).val("");

        // Clear validation errors and messages
        $(`${this.formSelector} .error-message`).addClass("hidden").text("");
        $(`${this.formSelector} .validation-message`)
            .addClass("hidden")
            .removeClass("text-green-500 text-red-500 text-gray-500")
            .text("");

        // Clear validation state
        this.currentValidationChecks = {};
        Object.keys(this.validationTimeouts).forEach((field) => {
            clearTimeout(this.validationTimeouts[field]);
            this.validationTimeouts[field] = null;
        });
    }

    /**
     * Set modal color scheme (add or edit)
     */
    setModalColor(mode) {
        const headerClasses = Object.values(this.colors)
            .map((c) => c.header)
            .join(" ");
        const hoverClasses = Object.values(this.colors)
            .map((c) => c.hover)
            .join(" ");
        const buttonClasses = Object.values(this.colors)
            .map((c) => c.button)
            .join(" ");
        const focusClasses = Object.values(this.colors)
            .map((c) => c.focus)
            .join(" ");

        $(this.modalHeaderSelector)
            .removeClass(headerClasses)
            .addClass(this.colors[mode].header);
        $(this.closeModalSelector)
            .removeClass(hoverClasses)
            .addClass(this.colors[mode].hover);
        $(this.saveBtnSelector)
            .removeClass(buttonClasses + " " + focusClasses)
            .addClass(this.colors[mode].button + " " + this.colors[mode].focus);
    }

    /**
     * Submit the form (create or update)
     */
    submitForm() {
        const self = this;
        const entityId = $(this.idInputSelector).val();
        const isEdit = !!entityId;

        // Check if we have any active validation errors
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

        // Collect form data
        const formData = {};
        this.validationFields.forEach((field) => {
            formData[field.name] = $(`#${field.name}`).val();
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
            url: isEdit
                ? self.formatRoute(self.routes.update, { id: entityId })
                : self.routes.store,
            type: isEdit ? "PUT" : "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                self.closeModal();

                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: response.message,
                    confirmButtonColor: "#3B82F6",
                });

                self.loadEntities();
            },
            error: function (xhr) {
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
            complete: function () {
                // Restore original button state
                saveBtn.prop("disabled", false).html(originalButtonContent);
            },
        });
    }

    /**
     * Show edit modal for an entity
     */
    editEntity(id) {
        const self = this;

        $.ajax({
            url: self.formatRoute(self.routes.edit, { id: id }),
            type: "GET",
            success: function (response) {
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

                    // Make sure validation messages are cleared after form reset
                    $(`${self.formSelector} .validation-message`)
                        .addClass("hidden")
                        .removeClass(
                            "text-green-500 text-red-500 text-gray-500"
                        )
                        .text("");

                    // Set ID first to ensure exclude_uuid works properly
                    $(self.idInputSelector).val(entity[self.idField]);

                    // Set field values
                    self.validationFields.forEach((field) => {
                        $(`#${field.name}`).val(entity[field.name]);
                    });

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
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: `Failed to load ${self.entityName.toLowerCase()} data.`,
                    confirmButtonColor: "#3B82F6",
                });
            },
        });
    }

    /**
     * Delete an entity
     */
    deleteEntity(id) {
        const self = this;

        // Get the entity name from the table row before deletion
        const row = $(`.delete-btn[data-id="${id}"]`).closest("tr");
        const nameCell = row.find("td:first");
        // Get text without any additional elements like "(Inactive)"
        let entityName = nameCell
            .clone()
            .children()
            .remove()
            .end()
            .text()
            .trim();

        const confirmMessage = entityName
            ? `Are you sure you want to delete the ${self.entityName.toLowerCase()} "<strong>${entityName}</strong>"?`
            : `This ${self.entityName.toLowerCase()} will be moved to trash.`;

        Swal.fire({
            title: `Delete ${self.entityName}?`,
            html: confirmMessage,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#EF4444",
            cancelButtonColor: "#6B7280",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: self.formatRoute(self.routes.destroy, { id: id }),
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        if (response.success) {
                            const successMessage = entityName
                                ? `${self.entityName} "<strong>${entityName}</strong>" moved to trash successfully!`
                                : response.message;

                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                html: successMessage,
                                confirmButtonColor: "#3B82F6",
                            });
                            self.loadEntities();
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: `Failed to delete ${self.entityName.toLowerCase()}.`,
                            confirmButtonColor: "#3B82F6",
                        });
                    },
                });
            }
        });
    }

    /**
     * Restore a deleted entity
     */
    restoreEntity(id) {
        const self = this;

        // Get the entity name from the table row before restoration
        const row = $(`.restore-btn[data-id="${id}"]`).closest("tr");
        const nameCell = row.find("td:first");
        // Get text without any additional elements like "(Inactive)"
        let entityName = nameCell
            .clone()
            .children()
            .remove()
            .end()
            .text()
            .trim();

        const confirmMessage = entityName
            ? `Are you sure you want to restore the ${self.entityName.toLowerCase()} "<strong>${entityName}</strong>"?`
            : `This will make the ${self.entityName.toLowerCase()} active again.`;

        Swal.fire({
            title: `Restore ${self.entityName}?`,
            html: confirmMessage,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#10B981",
            cancelButtonColor: "#6B7280",
            confirmButtonText: "Yes, restore it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: self.formatRoute(self.routes.restore, { id: id }),
                    type: "PATCH",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        if (response.success) {
                            const successMessage = entityName
                                ? `${self.entityName} "<strong>${entityName}</strong>" restored successfully!`
                                : response.message;

                            Swal.fire({
                                icon: "success",
                                title: "Restored!",
                                html: successMessage,
                                confirmButtonColor: "#3B82F6",
                            });
                            self.loadEntities();
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: `Failed to restore ${self.entityName.toLowerCase()}.`,
                            confirmButtonColor: "#3B82F6",
                        });
                    },
                });
            }
        });
    }

    /**
     * Setup real-time validation for form fields
     */
    setupValidation() {
        const self = this;

        this.validationFields.forEach((field) => {
            // Skip fields that don't need real-time validation
            if (!field.validation || !field.validation.url) {
                return;
            }

            // Remove any existing event handlers to prevent duplicates
            $(`#${field.name}`).off("input blur");

            $(`#${field.name}`).on("input", function () {
                const value = $(this).val().trim();
                const entityId = $(self.idInputSelector).val();

                // Clear previous validation message and timeout
                $(`#${field.name}ValidationMessage`)
                    .addClass("hidden")
                    .removeClass("text-green-500 text-red-500 text-gray-500")
                    .text("");

                if (self.validationTimeouts[field.name]) {
                    clearTimeout(self.validationTimeouts[field.name]);
                }

                // Don't validate if empty or too short
                if (
                    !value ||
                    value.length < (field.validation.minLength || 2)
                ) {
                    return;
                }

                // Set a timeout to prevent too many requests
                self.validationTimeouts[field.name] = setTimeout(() => {
                    self.currentValidationChecks[field.name] = value;

                    // Show checking message
                    $(`#${field.name}ValidationMessage`)
                        .removeClass("hidden text-green-500 text-red-500")
                        .addClass("text-gray-500")
                        .text("Checking availability...");

                    // Make AJAX request
                    $.ajax({
                        url: field.validation.url,
                        type: "POST",
                        data: {
                            [field.name]: value,
                            exclude_uuid: entityId,
                        },
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                        success: function (response) {
                            // Only update if this is still the current check
                            if (
                                self.currentValidationChecks[field.name] !==
                                value
                            )
                                return;

                            if (response.exists) {
                                $(`#${field.name}ValidationMessage`)
                                    .removeClass(
                                        "hidden text-gray-500 text-green-500"
                                    )
                                    .addClass("text-red-500")
                                    .text(
                                        field.validation.errorMessage ||
                                            `This ${field.name} is already taken.`
                                    );
                            } else {
                                $(`#${field.name}ValidationMessage`)
                                    .removeClass(
                                        "hidden text-gray-500 text-red-500"
                                    )
                                    .addClass("text-green-500")
                                    .text(
                                        field.validation.successMessage ||
                                            `${self.capitalize(
                                                field.name
                                            )} is available.`
                                    );
                            }
                        },
                        error: function () {
                            // Only update if this is still the current check
                            if (
                                self.currentValidationChecks[field.name] !==
                                value
                            )
                                return;

                            // Hide the message on error
                            $(`#${field.name}ValidationMessage`).addClass(
                                "hidden"
                            );
                        },
                    });
                }, field.validation.delay || 500);
            });

            // Add blur event handler to hide validation message when field is empty
            $(`#${field.name}`).on("blur", function () {
                const value = $(this).val().trim();
                if (!value) {
                    $(`#${field.name}ValidationMessage`)
                        .addClass("hidden")
                        .removeClass(
                            "text-green-500 text-red-500 text-gray-500"
                        )
                        .text("");
                }
            });
        });
    }

    /**
     * Show an alert message
     */
    showAlert(message, type = "success") {
        $(this.alertSelector).removeClass(
            "bg-green-100 text-green-700 bg-red-100 text-red-700"
        );

        if (type === "success") {
            $(this.alertSelector).addClass(
                "bg-green-100 border border-green-400 text-green-700"
            );
        } else {
            $(this.alertSelector).addClass(
                "bg-red-100 border border-red-400 text-red-700"
            );
        }

        $(this.alertSelector + " span").text(message);
        $(this.alertSelector).fadeIn().delay(3000).fadeOut();
    }

    /**
     * Format route URL with parameters
     */
    formatRoute(route, params) {
        let url = route;
        if (params) {
            Object.keys(params).forEach((key) => {
                url = url.replace(`:${key}`, params[key]);
            });
        }
        return url;
    }

    /**
     * Get entity variable name (for API responses)
     */
    getEntityVarName() {
        return this.entityName.toLowerCase();
    }

    /**
     * Capitalize first letter of a string
     */
    capitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
}
