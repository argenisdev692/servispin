@props([
    'entityName' => 'Item',
    'showSearchBar' => true,
    'showInactiveToggle' => true,
    'showPerPage' => true,
    'perPageOptions' => [5, 10, 15, 25, 50],
    'defaultPerPage' => 10,
    'addButtonId' => 'addEntityBtn',
])

<div class="mb-5 flex flex-col sm:flex-row justify-between items-center">
    @if ($showSearchBar)
        <!-- Search Input -->
        <div class="relative w-full sm:w-1/3 mb-3 sm:mb-0">
            <span class="absolute inset-y-0 left-0 flex items-center pl-2">
                <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    viewBox="0 0 24 24" class="w-6 h-6 text-gray-400">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="Search {{ $entityName }}s..."
                class="pl-10 pr-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full text-sm text-gray-700 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600">
        </div>
    @endif

    <!-- Controls: Show Deleted, Per Page, Add Button -->
    <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
        @if ($showInactiveToggle)
            <!-- Show Deleted Toggle -->
            <div class="flex items-center justify-end sm:justify-start w-full sm:w-auto">
                <label for="showDeleted" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show
                    Inactive</label>
                <label for="showDeleted" class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" id="showDeleted" class="sr-only">
                        <div class="block bg-gray-600 w-10 h-6 rounded-full"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition">
                        </div>
                    </div>
                </label>
                <style>
                    /* Toggle CSS */
                    input:checked~.dot {
                        transform: translateX(100%);
                        background-color: #48bb78;
                        /* Tailwind green-500 */
                    }

                    input:checked~.block {
                        background-color: #a0aec0;
                        /* Tailwind gray-500 */
                    }
                </style>
            </div>
        @endif

        @if ($showPerPage)
            <!-- Per Page Selector -->
            <div class="flex items-center justify-end sm:justify-start w-full sm:w-auto">
                <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Per
                    Page:</label>
                <select id="perPage"
                    class="border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm py-2 px-2 text-gray-700 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-600">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}" {{ $option == $defaultPerPage ? 'selected' : '' }}>
                            {{ $option }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Add Entity Button -->
        <button id="{{ $addButtonId }}"
            class="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 w-full sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Add {{ $entityName }}
        </button>
    </div>
</div>
