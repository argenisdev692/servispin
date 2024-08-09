<div :class="{ 'theme-dark': dark }" x-data="data()" lang="en">



    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
        <!-- MENU SIDEBAR -->
        <x-menu-sidebar />
        <!-- END MENU SIDEBAR -->
        <div class="flex flex-col flex-1 w-full">

            <!-- HEADER -->
            <x-header-dashboard />
            <!-- END HEADER -->

            <!-- PANEL MAIN CATEGORIES -->
            <!--INCLUDE ALERTS MESSAGES-->

            <x-message-success />


            <!-- END INCLUDE ALERTS MESSAGES-->

            <main class="h-full overflow-y-auto">
                <div class="container px-6 mx-auto grid">

                    <!-- CTA -->
                    <div
                        class="mt-5 flex items-center justify-between p-4 mb-8 text-sm font-semibold text-white bg-blue-500 rounded-lg shadow-md focus:outline-none focus:shadow-outline-purple">
                        <div class="flex items-center">
                            <i class="fa-solid fa-users mr-3"></i>

                            <x-slot name="title">
                                {{ __('User data') }}
                            </x-slot>
                            <a href="{{ route('users') }}">
                                <span>User data</span></a>
                        </div>

                    </div>
                    @can('manage admin')
                        <div class=" my-7 flex justify-between space-x-2">
                            <x-button wire:click="create()"><span class="font-semibold"> Create New <i
                                        class="fa-solid fa-user"></i> </span>
                            </x-button>
                            <x-input id="name" type="text" wire:model="search" placeholder="Search..." autofocus
                                autocomplete="off" />
                        </div>
                    @endcan
                    <!-- Tables -->
                    <div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
                        <div class="w-full overflow-x-auto">
                            <table class="w-full whitespace-no-wrap">
                                <thead>
                                    <tr
                                        class="text-xs font-semibold tracking-wide text-center text-white uppercase border-b dark:border-gray-700 bg-blue-600 dark:text-gray-400 dark:bg-gray-800">
                                        <th class="px-4 py-3">Nro</th>
                                        <th class="px-4 py-3">Name</th>
                                        <th class="px-4 py-3">Username</th>
                                        <th class="px-4 py-3">Email</th>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3">Role</th>
                                        @can('manage admin')
                                            <th class="px-4 py-3">Action</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                                    @forelse($data as $item)
                                        <tr class="text-gray-700 text-center  dark:text-gray-400">
                                            <td class="px-4 py-3 text-center">

                                                {{ $loop->iteration }}

                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ $item->name }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ $item->username }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ $item->email }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if ($item->role_name === 'Admin')
                                                    <span
                                                        class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                                        {{ $item->role_name }}
                                                    </span>
                                                @elseif ($item->role_name === 'User')
                                                    <span
                                                        class="px-2 py-1 font-semibold leading-tight text-purple-700 bg-purple-100 rounded-full dark:bg-purple-700 dark:text-purple-100">
                                                        {{ $item->role_name }}
                                                    </span>
                                                @else
                                                    <!-- Otro caso por defecto si no coincide con 'admin' ni 'user' -->
                                                    <span
                                                        class="px-2 py-1 font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-100">
                                                        {{ $item->role_name }}
                                                    </span>
                                                @endif


                                            </td>
                                            @can('manage admin')
                                                <td class="px-4 py-3 text-sm">

                                                    <button wire:click="edit({{ $item->id }})"
                                                        class="bg-blue-600 duration-500 ease-in-out hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"><i
                                                            class="fa-solid fa-pen-to-square"></i></button>
                                                    <button wire:click="$emit('deleteData',{{ $item->id }})"
                                                        class="bg-red-600 duration-500 ease-in-out hover:bg-red-700 text-white font-bold py-2 px-4 rounded"><i
                                                            class="fa-solid fa-trash"></i></button>




                                                </td>
                                            @endcan
                                        </tr>

                                    @empty
                                        <tr class="text-center">
                                            <td colspan="6">
                                                <div class="grid justify-items-center w-full mt-5">
                                                    <div class="text-center bg-red-100 rounded-lg py-5 w-full px-6 mb-4 text-base text-red-700 "
                                                        role="alert">
                                                        No Data Records
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="m-2 p-2">{{ $data->links() }}</div>
                        </div>
                        <!-- MODAL -->
                        @if ($isOpen)
                            <div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
                                <div
                                    class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 transition-opacity">
                                        <div class="absolute inset-0 bg-gray-700 opacity-75"></div>
                                    </div>
                                    <!-- This element is to trick the browser into centering the modal contents. -->
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

                                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full sm:max-w-lg sm:w-full"
                                        role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                                        <div
                                            class="flex flex-shrink-0 items-center justify-between rounded-t-md border-b-2 border-neutral-100 border-opacity-100 p-4 dark:border-opacity-50">
                                            <!--Modal title-->
                                            <div class="text-center"></div>
                                            <h5 class="text-xl font-medium leading-normal text-neutral-800 dark:text-neutral-200"
                                                id="exampleModalLabel">
                                                Users Managament
                                            </h5>
                                            <!--Close button-->
                                            <button type="button" wire:click="closeModal()"
                                                class="p-0.5 bg-red-600 duration-500 ease-in-out hover:bg-red-700 text-white rounded-full box-content  border-none hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none"
                                                data-te-modal-dismiss aria-label="Close">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="h-6 w-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>


                                        </div>
                                        <form autocomplete="off">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="">
                                                    <div class="mb-4">
                                                        <label for="exampleFormControlInput1"
                                                            class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                                                        <input type="text" id="name" autocomplete="off"
                                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                            id="exampleFormControlInput1" required maxlength="20"
                                                            placeholder="Enter Name" wire:model="name">
                                                        @error('name')
                                                            <span class="text-red-500">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="exampleFormControlInput1"
                                                            class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                                                        <input type="text" id="username" autocomplete="off"
                                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                            id="exampleFormControlInput1" required maxlength="20"
                                                            placeholder="Enter Username" wire:model="username">
                                                        @error('username')
                                                            <span class="text-red-500">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div class="mb-4">
                                                        <label for="exampleFormControlInput2"
                                                            class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                                                        <input type="email" id="email" autocomplete="off"
                                                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                            id="exampleFormControlInput1" maxlength="50"
                                                            placeholder="Enter Email" wire:model="email">
                                                        @error('email')
                                                            <span class="text-red-500">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="exampleFormControlInput1"
                                                            class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                                                        <div class="relative mb-2">
                                                            <div class="input-container">
                                                                <div
                                                                    class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">


                                                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                                                        aria-hidden="true"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        fill="currentColor"
                                                                        viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                                                        <path
                                                                            d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                                                                    </svg>

                                                                </div>

                                                                <input type="password" id="password"
                                                                    wire:model="password" required autocomplete="off"
                                                                    autofocus autocomplete="off" id="input-group-1"
                                                                    class="bg-gray-50 border border-gray-300 px-5 py-3 mt-2 mb-2 text-gray-900 text-sm rounded-lg focus:ring-indigo-500
                                 focus:border-indigo-500 block w-full pl-10  p-2.5  dark:border-gray-700  dark:focus:border-indigo-400 focus:outline-none focus:ring focus:ring-opacity-40"
                                                                    placeholder="Password">
                                                                <span id="toggle-password" class="password-toggle"
                                                                    onclick="togglePasswordVisibility()">
                                                                    <i class=" text-gray-500 fa-regular fa-eye"></i>
                                                                </span>
                                                            </div>
                                                            @error('password')
                                                                <span class="text-red-500">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="exampleFormControlInput2"
                                                            class="block text-gray-700 text-sm font-bold mb-2">Role
                                                        </label>
                                                        <select wire:model="role"
                                                            class="block w-full mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-white form-select focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:focus:shadow-outline-gray">
                                                            <option value="">Select Role</option>
                                                            @foreach ($rolesRender as $item)
                                                                <option value="{{ $item->id }}"
                                                                    @if ($item->id === $role) selected @endif>
                                                                    {{ $item->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        @error('role')
                                                            <span class="text-red-500">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                                                    <button type="button" wire:click.prevent="store()"
                                                        wire:loading.attr="disabled" wire:target="store"
                                                        class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                                        Register
                                                    </button>
                                                </span>
                                                <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
                                                    <button wire:click="closeModal()" type="button"
                                                        class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                                        Cancel
                                                    </button>
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <!-- MODAL -->
                    </div>


                </div>
            </main>


            <!-- END PANEL MAIN CATEGORIES -->

        </div>
    </div>


</div>

<style>
    .relative {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: 10px;
        /* Ajusta esto según tu diseño */
        cursor: pointer;
    }
</style>
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.querySelector('#password');
        const toggleButton = document.querySelector('#toggle-password');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.innerHTML = '<i class="far fa-eye-slash"></i>'; // Cambia el icono a ojo tachado
        } else {
            passwordInput.type = 'password';
            toggleButton.innerHTML = '<i class="far fa-eye"></i>'; // Cambia el icono a ojo
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('deleteData', function(id) {
            Swal.fire({
                title: 'Are you sure you want to delete all of this user records?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emitTo('users-crud', 'delete',
                        id); // Envía el Id al método delete
                    Swal.fire(
                        'Deleted!',
                        'Your Data has been deleted.',
                        'success'
                    );
                }
            });
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('deleteUserOperations', function(id) {
            Swal.fire({
                title: 'Are you sure you want to delete this item?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emitTo('users-crud', 'deleteOperations',
                        id); // Envía el Id al método delete
                    Swal.fire(
                        'Deleted!',
                        'Your Data has been deleted.',
                        'success'
                    );
                }
            });
        });
    });
</script>

<style>
    .password-input-container {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        cursor: pointer;
    }
</style>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.querySelector('#password');
        const toggleButton = document.querySelector('#toggle-password');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.innerHTML = '<i class="fa-regular fa-eye-slash"></i>'; // Cambia el icono a ojo tachado
        } else {
            passwordInput.type = 'password';
            toggleButton.innerHTML = '<i class="fa-regular fa-eye"></i>'; // Cambia el icono a ojo
        }
    }
</script>
