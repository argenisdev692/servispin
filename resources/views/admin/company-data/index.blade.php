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

                <!--INCLUDE ALERTS MESSAGES (Optional, if you want success messages here too)-->
                {{-- <x-message-success /> --}}
                <!-- END INCLUDE ALERTS MESSAGES-->

                <main class="h-full overflow-y-auto">
                    <div class="container px-6 mx-auto grid">

                        {{-- Page Title / Breadcrumb Area --}}
                        <div
                            class="mt-5 flex items-center justify-between p-4 mb-8 text-sm font-semibold text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:shadow-outline-purple">
                            <div class="flex items-center">
                                {{-- Heroicon: office-building --}}
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                <a href="{{ route('company-data.index') }}">
                                    <span>Company Data Management</span>
                                </a>
                            </div>
                            {{-- Optional: Add breadcrumbs or other actions here --}}
                        </div>

                        {{-- Button to Open Edit Modal --}}
                        <div class="mb-4 text-right">
                            <button id="editCompanyDataBtn"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Edit Company Data
                            </button>
                        </div>

                        {{-- Display Area for Company Data --}}
                        <div id="companyDataDisplay"
                            class="bg-white dark:bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4">
                            <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Current Data</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700 dark:text-gray-400">
                                {{-- Data will be loaded here by JavaScript --}}
                                <p><strong>Company Name:</strong> <span id="display_company_name">Loading...</span></p>
                                <p><strong>Contact Name:</strong> <span id="display_name">Loading...</span></p>
                                <p><strong>Email:</strong> <span id="display_email">Loading...</span></p>
                                <p><strong>Phone:</strong> <span id="display_phone">Loading...</span></p>
                                <p><strong>Website:</strong> <span id="display_website">Loading...</span></p>
                                <p><strong>Address:</strong> <span id="display_address">Loading...</span></p>
                                <p class="hidden"><strong>Latitude:</strong> <span id="display_latitude">Loading...</span>
                                </p>
                                <p class="hidden"><strong>Longitude:</strong> <span id="display_longitude">Loading...</span>
                                </p>
                                <p><strong>Facebook:</strong> <span id="display_social_media_facebook">Loading...</span></p>
                                <p><strong>Instagram:</strong> <span id="display_social_media_instagram">Loading...</span>
                                </p>
                                <p><strong>Twitter:</strong> <span id="display_social_media_twitter">Loading...</span></p>
                                <p><strong>Google Map URL:</strong> <span id="display_address_google_map"
                                        class="break-all">Loading...</span></p>
                                {{-- Add display for signature_path if needed --}}
                            </div>
                        </div>

                        {{-- Google Map Preview --}}
                        <div id="mapPreview" class="bg-white dark:bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4">
                            <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Location Map</h2>
                            <div id="map-container" class="w-full h-80 rounded overflow-hidden">
                                <div id="google-map-display" class="w-full h-full">
                                    {{-- Map will be loaded here by JavaScript --}}
                                </div>
                            </div>
                        </div>

                        {{-- Modal for Editing Company Data - Styled similar to users-crud modal --}}
                        <div id="companyDataModal" class="fixed z-50 inset-0 overflow-y-auto hidden"
                            aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div
                                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                {{-- Background overlay --}}
                                <div class="fixed inset-0 transition-opacity">
                                    <div class="absolute inset-0 bg-gray-700 opacity-75"></div>
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                    aria-hidden="true">&#8203;</span>

                                {{-- Modal panel --}}
                                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
                                    role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                                    <form id="companyDataForm" autocomplete="off">
                                        @csrf {{-- Add CSRF token --}}
                                        <input type="hidden" name="_method" id="form_method" value="POST">
                                        {{-- Method spoofing for PUT --}}
                                        <input type="hidden" name="company_data_id" id="company_data_id">

                                        {{-- Modal Header --}}
                                        <div
                                            class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 border-opacity-100 p-4 dark:border-opacity-50">
                                            <h5 class="text-xl font-medium leading-normal text-neutral-800 dark:text-neutral-200"
                                                id="modal-title">
                                                Edit Company Data
                                            </h5>
                                            <button type="button" id="cancelBtnHeader" {{-- Give header cancel button a unique ID --}}
                                                class="p-0.5 bg-red-600 duration-500 ease-in-out hover:bg-red-700 text-white rounded-full box-content border-none hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none"
                                                data-te-modal-dismiss aria-label="Close">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Modal Body --}}
                                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {{-- Form Fields (Adapted labels/inputs for dark mode) --}}
                                                <div>
                                                    <label for="company_name"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Company
                                                        Name *</label>
                                                    <input type="text" name="company_name" id="company_name" required
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_company_name"></span>
                                                </div>
                                                <div>
                                                    <label for="name"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Contact
                                                        Name</label>
                                                    <input type="text" name="name" id="name"
                                                        class="capitalize block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_name"></span>
                                                </div>
                                                <div>
                                                    <label for="email"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                                                    <input type="email" name="email" id="email"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_email"></span>
                                                </div>
                                                <div>
                                                    <label for="phone"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Phone</label>
                                                    <input type="tel" name="phone" id="phone"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_phone"></span>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="address"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Address</label>
                                                    <textarea name="address" id="address" rows="3"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-textarea"></textarea>
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_address"></span>
                                                </div>
                                                <div class="hidden">
                                                    <label for="latitude"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Latitude</label>
                                                    <input type="text" name="latitude" id="latitude"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_latitude"></span>
                                                </div>
                                                <div class="hidden">
                                                    <label for="longitude"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Longitude</label>
                                                    <input type="text" name="longitude" id="longitude"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_longitude"></span>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="website"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Website
                                                        URL</label>
                                                    <input type="url" name="website" id="website"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_website"></span>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label for="address_google_map"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Google
                                                        Map Embed URL</label>
                                                    <textarea name="address_google_map" id="address_google_map" rows="3"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-textarea"></textarea>
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_address_google_map"></span>
                                                </div>
                                                <div>
                                                    <label for="social_media_facebook"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Facebook
                                                        URL</label>
                                                    <input type="url" name="social_media_facebook"
                                                        id="social_media_facebook"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_social_media_facebook"></span>
                                                </div>
                                                <div>
                                                    <label for="social_media_instagram"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Instagram
                                                        URL</label>
                                                    <input type="url" name="social_media_instagram"
                                                        id="social_media_instagram"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_social_media_instagram"></span>
                                                </div>
                                                <div>
                                                    <label for="social_media_twitter"
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-400">Twitter/X
                                                        URL</label>
                                                    <input type="url" name="social_media_twitter"
                                                        id="social_media_twitter"
                                                        class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input">
                                                    <span class="text-red-500 text-xs italic error-text"
                                                        id="error_social_media_twitter"></span>
                                                </div>
                                                {{-- Add signature_path field if needed (might require file input handling) --}}
                                            </div>
                                        </div>

                                        {{-- Modal Footer --}}
                                        <div
                                            class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t-2 border-neutral-100 dark:border-neutral-500">
                                            <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                                                {{-- Adjusted save button style --}}
                                                <button type="submit" id="saveBtn"
                                                    class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                                    Save Changes
                                                </button>
                                            </span>
                                            <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
                                                {{-- Adjusted cancel button style --}}
                                                <button id="cancelBtnFooter" {{-- Give footer cancel button a unique ID --}} type="button"
                                                    class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                                    Cancel
                                                </button>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        {{-- End Modal --}}

                    </div> {{-- End container --}}
                </main>

            </div> {{-- End flex flex-col --}}
        </div> {{-- End flex h-screen --}}
    </div>

    @push('scripts')
        {{-- International Telephone Input Library --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>

        {{-- Ensure jQuery and SweetAlert are loaded, either here or in the main layout --}}
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                    // Add other Alpine states if needed from your layout
                }
            }
        </script>

        <script>
            $(document).ready(function() {
                // Initialize intl-tel-input
                const phoneInput = document.querySelector("#phone");
                let iti;

                if (phoneInput) {
                    iti = window.intlTelInput(phoneInput, {
                        initialCountry: "es", // Set initial country to Spain
                        preferredCountries: ["es"], // Preferred countries list
                        separateDialCode: true, // Show the dial code separately
                        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
                        formatOnDisplay: true,
                        autoPlaceholder: "aggressive"
                    });

                    // Custom style fixes for dark mode compatibility
                    $('.iti').addClass('w-full');

                    // Handle form submission - ensure full international number is submitted
                    $('#companyDataForm').on('submit', function() {
                        if (iti) {
                            const phoneNumber = iti.getNumber();
                            if (phoneNumber) {
                                $('#phone').val(phoneNumber);
                            }
                        }
                    });
                }

                // --- CSRF Token Setup ---
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let currentCompanyDataId = null; // To store the ID of the data being edited

                // --- Load Initial Data ---
                function loadCompanyData() {
                    $.ajax({
                        url: '{{ route('company-data.index') }}', // Use GET /company-data
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            const data = response.companyData;
                            if (data) {
                                currentCompanyDataId = data.id; // Store the ID
                                $('#display_company_name').text(data.company_name || 'N/A');
                                $('#display_name').text(data.name || 'N/A');
                                $('#display_email').html(data.email ?
                                    `<a href="mailto:${data.email}" class="text-blue-600 hover:underline">${data.email}</a>` :
                                    'N/A');

                                // Format phone number display with Spanish formatting if possible
                                if (data.phone) {
                                    let formattedPhone = data.phone;
                                    // If it's just a Spanish number without country code, add +34
                                    if (data.phone.startsWith('6') || data.phone.startsWith('7') ||
                                        data.phone.startsWith('8') || data.phone.startsWith('9')) {
                                        formattedPhone = '+34 ' + data.phone;
                                    }
                                    $('#display_phone').html(
                                        `<a href="tel:${data.phone}" class="text-blue-600 hover:underline">${formattedPhone}</a>`
                                    );
                                } else {
                                    $('#display_phone').text('N/A');
                                }

                                $('#display_address').text(data.address || 'N/A');
                                $('#display_latitude').text(data.latitude || 'N/A');
                                $('#display_longitude').text(data.longitude || 'N/A');
                                $('#display_website').html(data.website ?
                                    `<a href="${data.website}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">${data.website}</a>` :
                                    'N/A');
                                $('#display_address_google_map').html(data.address_google_map ?
                                    `<a href="${data.address_google_map}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">View Map</a>` :
                                    'N/A');
                                $('#display_social_media_facebook').html(data.social_media_facebook ?
                                    `<a href="${data.social_media_facebook}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Facebook</a>` :
                                    'N/A');
                                $('#display_social_media_instagram').html(data.social_media_instagram ?
                                    `<a href="${data.social_media_instagram}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Instagram</a>` :
                                    'N/A');
                                $('#display_social_media_twitter').html(data.social_media_twitter ?
                                    `<a href="${data.social_media_twitter}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">Twitter/X</a>` :
                                    'N/A');

                                // Update Google Map iframe
                                if (data.address_google_map) {
                                    $('#google-map-display').html(`
                                        <iframe src="${data.address_google_map}" width="100%" height="100%" style="border:0;"
                                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                    `);
                                    $('#mapPreview').removeClass('hidden');
                                } else {
                                    $('#google-map-display').html(
                                        '<p class="text-center py-10 text-gray-500">No map location available</p>'
                                    );
                                    $('#mapPreview').removeClass('hidden');
                                }

                                // Enable edit button if data exists
                                $('#editCompanyDataBtn').prop('disabled', false).text('Edit Company Data');
                            } else {
                                // Handle case where no data exists yet
                                $('#companyDataDisplay').find('span[id^="display_"]').text('N/A');
                                $('#editCompanyDataBtn').text('Add Company Data').prop('disabled',
                                    false); // Change button text
                                currentCompanyDataId = null;
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading company data:", error);
                            $('#companyDataDisplay').find('span[id^="display_"]').text(
                                'Error loading data');
                            $('#editCompanyDataBtn').prop('disabled', true).text('Error Loading');
                            Swal.fire('Error', 'Could not load company data.', 'error');
                        }
                    });
                }

                loadCompanyData(); // Load data on page load

                // --- Open Edit Modal ---
                $('#editCompanyDataBtn').click(function() {
                    $('.error-text').text(''); // Clear previous errors
                    if (currentCompanyDataId) {
                        // Fetch existing data for editing
                        $('#modal-title').text('Edit Company Data');
                        //$('#form_method').val('PUT'); // Keep using POST for simplicity with controller
                        $('#company_data_id').val(currentCompanyDataId); // Set the hidden ID

                        $.ajax({
                            url: `/company-data/${currentCompanyDataId}/edit`, // Use edit route
                            type: 'GET',
                            success: function(data) {
                                // Populate form fields
                                $('#company_name').val(data.company_name);
                                $('#name').val(data.name);
                                $('#email').val(data.email);

                                // Set the phone number in the international input
                                if (data.phone && iti) {
                                    // First clear any existing value
                                    phoneInput.value = '';

                                    // Check if phone already has country code
                                    if (data.phone.startsWith('+')) {
                                        iti.setNumber(data.phone);
                                    } else {
                                        // If it looks like a Spanish mobile or landline without country code
                                        if (data.phone.startsWith('6') || data.phone.startsWith(
                                                '7') ||
                                            data.phone.startsWith('8') || data.phone.startsWith('9')
                                        ) {
                                            iti.setNumber('+34' + data.phone);
                                        } else {
                                            iti.setNumber(data.phone);
                                        }
                                    }
                                }

                                $('#address').val(data.address);
                                $('#latitude').val(data.latitude);
                                $('#longitude').val(data.longitude);
                                $('#website').val(data.website);
                                $('#address_google_map').val(data.address_google_map);
                                $('#social_media_facebook').val(data.social_media_facebook);
                                $('#social_media_instagram').val(data.social_media_instagram);
                                $('#social_media_twitter').val(data.social_media_twitter);
                                $('#companyDataModal').removeClass('hidden');
                            },
                            error: function(xhr) {
                                console.error("Error fetching data for edit:", xhr.responseText);
                                Swal.fire('Error', 'Could not fetch data for editing.', 'error');
                            }
                        });

                    } else {
                        // Prepare modal for creating new data
                        $('#modal-title').text('Add Company Data');
                        $('#companyDataForm')[0].reset(); // Clear form

                        // Reset telephone input
                        if (iti) {
                            iti.setCountry('es');
                        }

                        // $('#form_method').val('POST'); // Already default
                        $('#company_data_id').val(''); // Clear hidden ID
                        $('#companyDataModal').removeClass('hidden');
                    }
                });

                // --- Generic Close Modal Function ---
                function closeModal() {
                    $('#companyDataModal').addClass('hidden');
                    $('#companyDataForm')[0].reset(); // Reset form
                    $('.error-text').text(''); // Clear errors
                }

                // --- Close Modal Button Handlers ---
                $('#cancelBtnFooter').click(closeModal); // Use footer button ID
                $('#cancelBtnHeader').click(closeModal); // Use header button ID


                // --- Handle Form Submission (Create/Update) ---
                $('#companyDataForm').submit(function(e) {
                    e.preventDefault(); // Prevent default browser submission
                    $('#saveBtn').prop('disabled', true).text('Saving...'); // Disable button
                    $('.error-text').text(''); // Clear previous errors

                    const formData = $(this).serialize(); // Get form data
                    // const method = $('#form_method').val(); // Not strictly needed if always POSTing to store
                    // const id = $('#company_data_id').val();

                    // Use POST to the store route for both create and update
                    let ajaxUrl = '{{ route('company-data.store') }}';
                    let ajaxMethod = 'POST';

                    $.ajax({
                        url: ajaxUrl,
                        type: ajaxMethod,
                        data: formData,
                        success: function(response) {
                            closeModal(); // Use the close function
                            Swal.fire('Success!', response.message, 'success');
                            loadCompanyData(); // Refresh displayed data
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) { // Validation errors
                                const errors = xhr.responseJSON.errors;
                                $.each(errors, function(key, value) {
                                    $('#error_' + key).text(value[
                                        0
                                    ]); // Display the first error message for each field
                                });
                                Swal.fire('Validation Error', 'Please check the form fields.',
                                    'error');
                            } else {
                                console.error("Error saving data:", xhr.responseText);
                                Swal.fire('Error', 'Could not save company data.', 'error');
                            }
                        },
                        complete: function() {
                            $('#saveBtn').prop('disabled', false).text(
                                'Save Changes'); // Re-enable button
                        }
                    });
                });

                // --- Optional: Delete Handler --- (Keep commented as before)
            });
        </script>

        <style>
            /* Fixes for intl-tel-input in dark mode */
            .iti {
                width: 100%;
            }

            .dark .iti__country-list {
                background-color: #4a5568;
                color: #e2e8f0;
                border-color: #2d3748;
            }

            .dark .iti__country.iti__highlight {
                background-color: #2d3748;
            }
        </style>
    @endpush
@endsection
