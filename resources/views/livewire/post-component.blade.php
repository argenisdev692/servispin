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
                            <i class="fa-solid fa-blog mr-3"></i>

                            <x-slot name="title">
                                {{ __('Posts data') }}
                            </x-slot>
                            <a href="{{ route('posts') }}">
                                <span>Manage Posts</span></a>
                        </div>

                    </div>
                    @can('manage admin')
                        <div class=" my-7 flex justify-between space-x-2">
                            <x-button wire:click="showDataModal">+ Create New </x-button>
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
                                        <th class="px-4 py-2 w-20">Id.</th>
                                        <th class="px-4 py-2">Image</th>
                                        <th class="px-4 py-2">Title</th>
                                        <th class="px-4 py-2">Content</th>

                                        <th class="px-4 py-2">Status</th>
                                        @can('manage admin')
                                            <th class="px-4 py-3">Action</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-center">
                                    @forelse($posts as $post)
                                        <tr class="text-gray-700  uppercase dark:text-gray-400">
                                            <td class="px-4 py-3 text-center">

                                                {{ $loop->iteration }}

                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if (strpos($post->post_image, 'videos') !== false)
                                                    {{-- Si es un video, mostrar el reproductor de video --}}
                                                    <video class="w-32 h-32  object-cover rounded border" controls>
                                                        <source src="{{ Storage::url($post->post_image) }}"
                                                            type="video/mp4">
                                                        Tu navegador no soporta el elemento de video.
                                                    </video>
                                                @else
                                                    {{-- Si es una imagen, mostrar la imagen --}}
                                                    <img class="w-24 h-24  object-cover rounded border"
                                                        src="{{ Storage::url($post->post_image) }}" />
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 text-xs">
                                                {{ Str::words($post->post_title, 6, '...') }}
                                            </td>
                                            <td class="px-4 py-3 text-xs">
                                                {{ Str::words($post->post_content, 6, '...') }}
                                            </td>
                                            <td class="px-4 py-3 text-xs">
                                                {{ $post->post_status }}

                                            </td>
                                            @can('manage admin')
                                                <td class="px-4 py-3 text-sm">
                                                    <a href="{{ route('posts.show', ['postId' => $post->post_title_slug]) }}"
                                                        class="bg-purple-600 transition duration-500 ease-in-out hover:bg-purple-700 text-white font-bold inline-flex items-center p-3 px-4 py-2.5 mr-0.5  rounded text-base">
                                                        <i class="fa-solid fa-eye "></i>
                                                    </a>



                                                    <button wire:click="showEditDataModal({{ $post->id }})"
                                                        class="bg-blue-600 duration-500 ease-in-out hover:bg-blue-700 text-white font-bold p-3 py-2 px-4 rounded"><i
                                                            class="fa-solid fa-pen-to-square"></i></button>
                                                    <button wire:click="$emit('deleteData',{{ $post->id }})"
                                                        class="bg-red-600 duration-500 ease-in-out hover:bg-red-700 text-white font-bold p-3 py-2 px-4 rounded"><i
                                                            class="fa-solid fa-trash"></i></button>

                                                </td>
                                            @endcan
                                        </tr>

                                    @empty
                                        <tr class="text-center">
                                            <td colspan="7">
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
                            <div class="m-2 p-2">{{ $posts->links() }}</div>
                        </div>
                        <!-- MODAL -->
                        <div>
                            <x-dialog-modal wire:model="showingDataModal">

                                @if ($isEditMode)
                                    <x-slot name="title">Update Post</x-slot>
                                @else
                                    <x-slot name="title">Create Post</x-slot>
                                @endif
                                <x-slot name="content">
                                    <div class="space-y-8 divide-y divide-gray-200 w-full mt-10">
                                        <form enctype="multipart/form-data" autocomplete="off">
                                            <div class="sm:col-span-6">
                                                <label for="exampleFormControlInput1"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                                                <div class="mt-1">
                                                    <input type="text" id="post_title" wire:model.lazy="post_title"
                                                        name="post_title"
                                                        class="block w-full 
                                     appearance-none bg-white border
                                      border-gray-400 rounded-md py-2 px-3 text-base leading-normal transition duration-150 ease-in-out sm:text-sm sm:leading-5 mb-2" />
                                                </div>
                                                @error('post_title')
                                                    <span class="text-red-400">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="sm:col-span-6 ">

                                                <label for="exampleFormControlInput2"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Image:</label>

                                                @if ($oldImage)
                                                    <div class="py-2">
                                                        <label
                                                            class="text-red-700
                                        font-bold">
                                                            Current Image: </label>

                                                        <img src="{{ Storage::url($oldImage) }}">
                                                    </div>
                                                @endif
                                                @if ($newImage)
                                                    <div class="py-2 ml-6">


                                                        @if ($post_image)
                                                            @if (strpos($post_image->getMimeType(), 'image') !== false)
                                                                <label class="text-green-700 font-bold">Photo
                                                                    Preview:</label>
                                                                {{-- If it's an image, show the image preview --}}
                                                                <img src="{{ $post_image }}"
                                                                    class="w-32 h-32 object-cover" alt="Image Preview">
                                                            @elseif (strpos($post_image->getMimeType(), 'video') !== false)
                                                                {{-- If it's a video, show a video icon and add a link to view the video --}}
                                                                <div>
                                                                    <span class="text-gray-500">Video Preview:</span>
                                                                    <i class="fas fa-video mx-2"></i>
                                                                    <a href="{{ $post_image }}" target="_blank"
                                                                        rel="noopener noreferrer">View Video</a>
                                                                </div>
                                                            @endif
                                                        @endif

                                                    </div>
                                                @endif


                                                <div class="mt-1">
                                                    <input type="file" id="image" wire:model="newImage"
                                                        name="newImage" accept="image/*,video/mp4"
                                                        class="block w-full appearance-none bg-white border border-gray-400 rounded-md py-2 px-3 text-base leading-normal transition duration-150 ease-in-out sm:text-sm sm:leading-5" />

                                                </div>
                                                @error('newImage')
                                                    <span class="text-red-400">{{ $message }}</span>
                                                @enderror

                                            </div>
                                            <div class="sm:col-span-6 pt-5">
                                                <label for="exampleFormControlInput2"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Content:</label>
                                                <div class="mt-1">

                                                    <textarea id="post_content" rows="3" wire:model.lazy="post_content" name="post_content"
                                                        class="shadow-sm focus:ring-indigo-500 appearance-none bg-white border
                                     py-2 px-3 text-base leading-normal transition duration-150 ease-in-out focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                                </div>
                                                @error('post_content')
                                                    <span class="text-red-400">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="sm:col-span-6 py-2 ">
                                                <label for="exampleFormControlInput1"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                                                <div class="mt-1">

                                                    <select data-te-select-init id="category_id"
                                                        wire:model="category_id" name="category_id"
                                                        class="block w-full 
                                     appearance-none bg-white border
                                      border-gray-400 rounded-md py-2 px-3 text-base leading-normal transition duration-150 ease-in-out sm:text-sm sm:leading-5 mb-2">
                                                        <option value=""></option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}">
                                                                {{ $category->category_name }}</option>
                                                        @endforeach

                                                    </select>

                                                </div>
                                                @error('category_id')
                                                    <span class="text-red-400">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="sm:col-span-6 mb-3">
                                                <label for="exampleFormControlInput1"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                                                <div class="mt-1">

                                                    <select data-te-select-init id="post_status"
                                                        wire:model="post_status" name="post_status"
                                                        class="block w-full 
                                     appearance-none bg-white border
                                      border-gray-400 rounded-md py-2 px-3 text-base leading-normal transition duration-150 ease-in-out sm:text-sm sm:leading-5 mb-2">
                                                        <option value=""></option>
                                                        <option value="ACTIVE">ACTIVE</option>
                                                        <option value="INACTIVE">INACTIVE</option>


                                                    </select>


                                                </div>
                                                @error('post_status')
                                                    <span class="text-red-400">{{ $message }}</span>
                                                @enderror
                                            </div>


                                            <div class="sm:col-span-6">
                                                <label for="exampleFormControlInput1"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Meta
                                                    Title:</label>
                                                <div class="mt-1">
                                                    <input type="text" id="meta_title"
                                                        wire:model.lazy="meta_title"
                                                        class="block w-full 
                                     appearance-none bg-white border
                                      border-gray-400 rounded-md py-2 px-3 text-base leading-normal transition duration-150 ease-in-out sm:text-sm sm:leading-5 mb-2" />
                                                </div>
                                                @error('meta_title')
                                                    <span class="text-red-400">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="sm:col-span-6 pt-5">
                                                <label for="exampleFormControlInput2"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Meta
                                                    Description:</label>
                                                <div class="mt-1">
                                                    <textarea rows="3" wire:model.lazy="meta_description"
                                                        class="shadow-sm focus:ring-indigo-500 appearance-none bg-white border
                                     py-2 px-3 text-base leading-normal transition duration-150 ease-in-out focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                                </div>
                                                @error('meta_description')
                                                    <span class="text-red-400">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="sm:col-span-6 pt-5">
                                                <label for="exampleFormControlInput2"
                                                    class="block text-gray-700 text-sm font-bold mb-2">Meta
                                                    Keywords:</label>
                                                <div class="mt-1">
                                                    <textarea rows="3" wire:model.lazy="meta_keywords"
                                                        class="shadow-sm focus:ring-indigo-500 appearance-none bg-white border
                                     py-2 px-3 text-base leading-normal transition duration-150 ease-in-out focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                                </div>
                                                @error('meta_keywords')
                                                    <span class="text-red-400">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </form>
                                    </div>

                                </x-slot>
                                <x-slot name="footer">
                                    @if ($isEditMode)
                                        <button wire:click="closeModal()" type="button"
                                            class="inline-flex justify-center  rounded-md border border-gray-300 px-4 py-2 mr-3
                         bg-white text-base leading-6 font-medium
                          text-gray-700 shadow-sm hover:text-gray-500 
                          focus:outline-none focus:border-blue-300
                           focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                            Cancel
                                        </button>
                                        <x-button wire:click.prevent="updateData()" wire:loading.attr="disabled"
                                            wire:target="updateData,newImage">
                                            <svg wire:loading wire:target="updateData"
                                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            Update
                                        </x-button>
                                    @else
                                        <button wire:click="closeModal()" type="button"
                                            class="inline-flex justify-center  rounded-md border border-gray-300 px-4 py-2 mr-3
                         bg-white text-base leading-6 font-medium
                          text-gray-700 shadow-sm hover:text-gray-500 
                          focus:outline-none focus:border-blue-300
                           focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                            Cancel
                                        </button>
                                        <x-button wire:click.prevent="storeData()" wire:loading.attr="disabled"
                                            wire:target="storeData,newImage">
                                            <svg wire:loading wire:target="storeData"
                                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            {{ __('Save') }}
                                        </x-button>
                                    @endif
                                </x-slot>
                            </x-dialog-modal>
                        </div>
                        <!-- MODAL -->
                    </div>


                </div>
            </main>


            <!-- END PANEL MAIN CATEGORIES -->

        </div>
    </div>


</div>





@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        Livewire.on('deleteData', catId => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emitTo('posts', 'delete', catId)
                    Swal.fire(
                        'Deleted!',
                        'Your Data has been deleted.',
                        'success'
                    )
                }
            })
        })
    </script>
@endpush
