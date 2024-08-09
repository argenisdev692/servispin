<div :class="{ 'theme-dark': dark }" x-data="data()" lang="en" id="theme">


    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
        <!-- MENU SIDEBAR -->
        <x-menu-sidebar />
        <!-- END MENU SIDEBAR -->
        <div class="flex flex-col flex-1 w-full">

            <!-- HEADER -->
            <x-header-dashboard />
            <!-- END HEADER -->

            <!-- PANEL DASHBOARD WELCOME -->
            <main class="h-full overflow-y-auto">
                <div class="container px-6 mx-auto grid">

                    <!-- CTA -->
                    <div
                        class="mt-5 flex items-center justify-between p-4 mb-8 text-sm font-semibold text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:shadow-outline-purple">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>

                            <x-slot name="title">
                                {{ __('Dashboard') }}
                            </x-slot>
                            <a href="{{ route('dashboard') }}">
                                <span>Dashboard</span></a>
                        </div>

                    </div>
                    <!-- Cards -->


                    <section
                        class="h-96 relative flex flex-1 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-100 py-16 shadow-lg md:py-20 xl:py-48">
                        <!-- image - start -->
                        <img src="https://images.unsplash.com/photo-1618004652321-13a63e576b80?auto=format&q=75&fit=crop&w=1500"
                            loading="lazy" alt="dashboard"
                            class="absolute inset-0 h-full w-full object-cover object-center" />
                        <!-- image - end -->

                        <!-- overlay - start -->
                        <div class="absolute inset-0 bg-sky-600 mix-blend-multiply"></div>
                        <!-- overlay - end -->

                        <!-- text start -->
                        <div class="relative flex flex-col items-center p-4 sm:max-w-xl">
                            <p class="mb-4 text-center text-lg text-indigo-200 sm:text-xl md:mb-8"> Bienvenido</p>
                            <h1 class="mb-8 text-center text-4xl font-bold text-white sm:text-5xl md:mb-12 md:text-6xl">
                                Panel de control Servispin</h1>


                        </div>
                        <!-- text end -->
                    </section>

                    <!-- New Table -->



                    <!-- Charts -->

                </div>
            </main>
            <!-- END PANEL DASHBOARD WELCOME -->

        </div>
    </div>


</div>
