 <!-- Desktop sidebar -->
 <aside class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0">
     <div class="py-4 text-gray-500 dark:text-gray-400">
         <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="{{ route('dashboard') }}">
             <!-- Logo -->


             <x-application-mark class="block h-10 w-auto" />

         </a>
         <h1
             class="text-center text-base font-bold leading-none tracking-tighter text-indigo-500 md:text-base lg:text-sm">
             Welcome,
             {{ Auth::user()->username }} </h1>
         <ul class="mt-6">
             <li class="relative px-6 py-3">
                 <span class="absolute inset-y-0 left-0 w-1 bg-blue-600 rounded-tr-lg rounded-br-lg"
                     aria-hidden="true"></span>
                 <a class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                     href="{{ route('dashboard') }}">
                     <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                         <path
                             d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                         </path>
                     </svg>
                     <span class="ml-4">Dashboard</span>
                 </a>
             </li>
         </ul>
         <ul>
             <!--<li class="relative px-6 py-3">
                 <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                     href="n="true" fill="none" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                         <path
                             d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                         </path>
                     </svg>
                     <span class="ml-4">Categories</span>
                 </a>
             </li>-->




             <li class="relative px-6 py-3">
                 <button
                     class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                     @click="togglePagesMenu2" aria-haspopup="true">
                     <span class="inline-flex items-center">
                         <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                             stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                             <path
                                 d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122">
                             </path>
                         </svg>
                         <span class="ml-4">Management</span>
                     </span>
                     <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                         <path fill-rule="evenodd"
                             d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                             clip-rule="evenodd"></path>
                     </svg>
                 </button>
                 <template x-if="isPagesMenuOpen2">
                     <ul x-transition:enter="transition-all ease-in-out duration-300"
                         x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl"
                         x-transition:leave="transition-all ease-in-out duration-300"
                         x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0"
                         class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
                         aria-label="submenu">

                         <li
                             class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
                             <a class="w-full" href="{{ route('posts') }}">
                                 Blog
                             </a>
                         </li>


                     </ul>
                 </template>
             </li>

             {{-- Appointment Calendar Link --}}
             <li class="relative px-6 py-3">
                 <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('admin.appointment.calendar.index') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                     href="{{ route('admin.appointment.calendar.index') }}">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                         </path>
                     </svg>
                     <span class="ml-4">Calendario Citas</span>
                 </a>
             </li>

             <!-- Holidays/Exceptions Management Link -->
             <li class="relative px-6 py-3">
                 <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('admin.availability-exceptions.index') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                     href="{{ route('admin.availability-exceptions.index') }}">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M9 3v2m6-2v2M9 19h6m-6 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                         </path>
                     </svg>
                     <span class="ml-4">Días Festivos</span>
                 </a>
             </li>

             @can('manage admin')
                 <li class="relative px-6 py-3">
                     <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('users') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                         href="{{ route('users') }}">


                         <svg class="w-5 h-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" height="1em"
                             viewBox="0 0 640 512">
                             <path
                                 d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z" />
                         </svg>
                         <span class="ml-4">Users</span>
                     </a>
                 </li>
                 <li class="relative px-6 py-3">
                     <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('brands.index') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                         href="{{ route('brands.index') }}">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 8V5a2 2 0 012-2z">
                             </path>
                         </svg>
                         <span class="ml-4">Brands</span>
                     </a>
                 </li>
                 <li class="relative px-6 py-3">
                     <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('services.*') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                         href="{{ route('services.index') }}">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M13 10V3L4 14h7v7l9-11h-7z">
                             </path>
                         </svg>
                         <span class="ml-4">Services</span>
                     </a>
                 </li>
                 <li class="relative px-6 py-3">
                     <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('company-data.index') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                         href="{{ route('company-data.index') }}">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                             </path>
                         </svg>
                         <span class="ml-4">Company Data</span>
                     </a>
                 </li>
             @endcan
         </ul>

     </div>
 </aside>
 <!-- Mobile sidebar -->
 <!-- Backdrop -->
 <div x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"></div>
 <aside class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"
     x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"
     x-transition:enter-start="opacity-0 transform -translate-x-20" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0 transform -translate-x-20" @click.away="closeSideMenu"
     @keydown.escape="closeSideMenu">
     <div class="py-4 text-gray-500 dark:text-gray-400">
         <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="{{ route('dashboard') }}">
             <x-application-mark class="block h-10 w-auto" />
         </a>
         <h1
             class="text-center text-base font-bold leading-none tracking-tighter text-indigo-500 md:text-7xl lg:text-sm">
             Welcome,
             {{ Auth::user()->username }} </h1>
         <ul class="mt-6">
             <li class="relative px-6 py-3">
                 <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                     aria-hidden="true"></span>
                 <a class="inline-flex items-center w-full text-sm font-semibold text-gray-800 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-100"
                     href="{{ route('dashboard') }}">
                     <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                         <path
                             d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                         </path>
                     </svg>
                     <span class="ml-4">Dashboard</span>
                 </a>
             </li>
         </ul>
         <ul>
             <!-- <li class="relative px-6 py-3">
                                 <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                                     href="">
                                     <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                         <path
                                             d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                         </path>
                                     </svg>
                                     <span class="ml-4">Categories</span>
                                 </a>
                             </li>-->


             <li class="relative px-6 py-3">
                 <button
                     class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                     @click="togglePagesMenu" aria-haspopup="true">
                     <span class="inline-flex items-center">


                         <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke-linecap="round"
                             stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                             <path
                                 d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122">
                             </path>
                         </svg>
                         <span class="ml-4">Management</span>
                     </span>
                     <svg class="w-4 h-4" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                         <path fill-rule="evenodd"
                             d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                             clip-rule="evenodd"></path>
                     </svg>
                 </button>
                 <template x-if="isPagesMenuOpen">
                     <ul x-transition:enter="transition-all ease-in-out duration-300"
                         x-transition:enter-start="opacity-25 max-h-0" x-transition:enter-end="opacity-100 max-h-xl"
                         x-transition:leave="transition-all ease-in-out duration-300"
                         x-transition:leave-start="opacity-100 max-h-xl" x-transition:leave-end="opacity-0 max-h-0"
                         class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
                         aria-label="submenu">
                         <li
                             class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
                             <a class="w-full" href="{{ route('posts') }}">Blog</a>
                         </li>



                     </ul>
                 </template>
             </li>

             {{-- Appointment Calendar Link (Mobile) --}}
             <li class="relative px-6 py-3">
                 <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('admin.appointment.calendar.index') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                     href="{{ route('admin.appointment.calendar.index') }}">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                         </path>
                     </svg>
                     <span class="ml-4">Calendario Citas</span>
                 </a>
             </li>

             <!-- Holidays/Exceptions Management Link -->
             <li class="relative px-6 py-3">
                 <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('admin.availability-exceptions.index') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                     href="{{ route('admin.availability-exceptions.index') }}">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M9 3v2m6-2v2M9 19h6m-6 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                         </path>
                     </svg>
                     <span class="ml-4">Días Festivos</span>
                 </a>
             </li>

             @can('manage admin')
                 <li class="relative px-6 py-3">
                     <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('users') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                         href="{{ route('users') }}">
                         {{-- Existing Users Icon (Mobile) --}}
                         <svg class="w-5 h-5" fill="currentColor" xmlns="http://www.w3.org/2000/svg" height="1em"
                             viewBox="0 0 640 512">
                             <path
                                 d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z" />
                         </svg>
                         <span class="ml-4">Users</span>
                     </a>
                 </li>
                 <li class="relative px-6 py-3">
                     <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('brands.index') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                         href="{{ route('brands.index') }}">
                         {{-- Heroicon: tag (Mobile) --}}
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 8V5a2 2 0 012-2z">
                             </path>
                         </svg>
                         <span class="ml-4">Brands</span>
                     </a>
                 </li>
                 <li class="relative px-6 py-3">
                     <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('company-data.index') ? 'dark:text-gray-100 text-gray-800' : '' }}"
                         href="{{ route('company-data.index') }}">
                         {{-- Existing Company Data Icon (Mobile) --}}
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                             </path>
                         </svg>
                         <span class="ml-4">Company Data</span>
                     </a>
                 </li>
             @endcan
         </ul>

     </div>
 </aside>
