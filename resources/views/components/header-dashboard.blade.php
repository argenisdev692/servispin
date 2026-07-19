 <header class="z-10 py-3 bg-white/90 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shadow-sm">
     <div class="container flex items-center justify-between h-full px-6 mx-auto text-blue-600 dark:text-blue-400">
         <!-- Mobile hamburger -->
         <button class="p-2 -ml-1 rounded-lg md:hidden text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
             @click="toggleSideMenu" aria-label="Menu">
             <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                 <path fill-rule="evenodd"
                     d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                     clip-rule="evenodd"></path>
             </svg>
         </button>

         <div class="flex justify-center flex-1 lg:mr-32">
             <div class="relative w-full max-w-xl mr-6"></div>
         </div>

         <ul class="flex items-center flex-shrink-0 gap-2 sm:gap-4">
             <!-- Theme toggler -->
             <li class="flex">
                 <button type="button"
                     class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 transition-colors"
                     @click="toggleTheme"
                     :aria-label="dark ? 'Activar modo claro' : 'Activar modo oscuro'"
                     :title="dark ? 'Modo claro' : 'Modo oscuro'">
                     <template x-if="!dark">
                         <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                             <path d="M17.293 13.293A8 8 0 016.707 2.707a8 8 0 1010.586 10.586z"></path>
                         </svg>
                     </template>
                     <template x-if="dark">
                         <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                             <path fill-rule="evenodd"
                                 d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                 clip-rule="evenodd"></path>
                         </svg>
                     </template>
                 </button>
             </li>

             <!-- Profile menu -->
             <x-dropdown align="right" width="48">
                 <x-slot name="trigger">
                     @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                         <button
                             class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500/40 transition">
                             <img class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700" src="{{ Auth::user()->profile_photo_url }}"
                                 alt="{{ Auth::user()->name }}" />
                         </button>
                     @else
                         <span class="inline-flex rounded-md">
                             <button type="button"
                                 class="inline-flex items-center px-3 py-2 border border-slate-200 dark:border-slate-700 text-sm leading-4 font-medium rounded-lg text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/40 transition ease-in-out duration-150">
                                 {{ Auth::user()->name }}

                                 <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                     <path stroke-linecap="round" stroke-linejoin="round"
                                         d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                 </svg>
                             </button>
                         </span>
                     @endif
                 </x-slot>

                 <x-slot name="content">
                     <div class="block px-4 py-2 text-xs text-gray-400">
                         {{ __('Manage Account') }}
                     </div>

                     <x-dropdown-link href="{{ route('profile.show') }}">
                         {{ __('Profile') }}
                     </x-dropdown-link>

                     @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                         <x-dropdown-link href="{{ route('api-tokens.index') }}">
                             {{ __('API Tokens') }}
                         </x-dropdown-link>
                     @endif

                     <div class="border-t border-gray-200 dark:border-slate-700"></div>

                     <form method="POST" action="{{ route('logout') }}">
                         @csrf

                         <button type="submit"
                             class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-slate-700 transition duration-150 ease-in-out">
                             {{ __('Log Out') }}
                         </button>
                     </form>
                 </x-slot>
             </x-dropdown>

         </ul>
     </div>
 </header>
