@props(['id' => 'galleryDropzone'])

<div x-data="galleryDropzone()" x-init="init()" id="{{ $id }}" class="relative flex flex-col p-4 text-gray-400 border border-gray-200 rounded bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
    {{-- Drop Area --}}
    <div x-ref="dnd"
        class="relative flex flex-col text-gray-400 border border-gray-200 border-dashed rounded cursor-pointer dark:border-gray-500"
        :class="{ 'border-blue-400 ring-4 ring-inset ring-blue-200': dragOver }">
        <input accept="image/*,video/*" type="file" multiple
            class="absolute inset-0 z-50 w-full h-full p-0 m-0 outline-none opacity-0 cursor-pointer"
            @change="addFiles($event)"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="dragOver = false; addFiles($event)"
            title="" />

        <div class="flex flex-col items-center justify-center py-10 text-center">
            <svg class="w-10 h-10 mb-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="m-0 text-sm text-gray-500 dark:text-gray-300">Arrastra archivos aquí o haz clic para seleccionar</p>
            <p class="text-xs text-gray-400 mt-1">Imágenes (JPEG, PNG, WEBP) y videos (MP4, MOV, AVI)</p>
        </div>
    </div>

    {{-- File Previews Grid --}}
    <template x-if="files.length > 0">
        <div class="grid grid-cols-2 gap-4 mt-4 md:grid-cols-4 lg:grid-cols-5"
            x-ref="previewGrid"
            @dragover.prevent="$event.dataTransfer.dropEffect = 'move'">
            <template x-for="(item, index) in files" :key="item.id">
                <div class="relative flex flex-col items-center overflow-hidden text-center bg-gray-100 border rounded cursor-move select-none dark:bg-gray-800 dark:border-gray-600"
                    style="padding-top: 100%;"
                    @dragstart="dragStart($event, index)"
                    @dragend="fileDragging = null"
                    @drop.prevent="handleDrop($event, index)"
                    :class="{ 'border-blue-600 ring-2 ring-blue-400': fileDragging === index }"
                    draggable="true"
                    :data-index="index">

                    {{-- Remove button --}}
                    <button type="button" @click="remove(index)"
                        class="absolute top-0 right-0 z-50 p-1 bg-white dark:bg-gray-900 rounded-bl shadow hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none">
                        <svg class="w-4 h-4 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- Audio placeholder --}}
                    <template x-if="item.type && item.type.includes('audio/')">
                        <svg class="absolute w-12 h-12 text-gray-400 transform top-1/2 -translate-y-2/3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </template>

                    {{-- Generic file placeholder --}}
                    <template x-if="item.type && (item.type.includes('application/') || item.type === '')">
                        <svg class="absolute w-12 h-12 text-gray-400 transform top-1/2 -translate-y-2/3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </template>

                    {{-- Image preview --}}
                    <template x-if="item.type && item.type.includes('image/')">
                        <img class="absolute inset-0 z-0 object-cover w-full h-full border-4 border-white dark:border-gray-800 preview"
                            :src="item.previewUrl" draggable="false" />
                    </template>

                    {{-- Video preview --}}
                    <template x-if="item.type && item.type.includes('video/')">
                        <video class="absolute inset-0 object-cover w-full h-full border-4 border-white dark:border-gray-800 pointer-events-none preview"
                            :src="item.previewUrl" muted preload="metadata" draggable="false"></video>
                    </template>

                    {{-- File info --}}
                    <div class="absolute bottom-0 left-0 right-0 flex flex-col p-2 text-xs bg-white bg-opacity-90 dark:bg-gray-900 dark:bg-opacity-90">
                        <span class="w-full font-bold text-gray-900 dark:text-gray-100 truncate" x-text="item.file.name">Loading</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400" x-text="humanFileSize(item.file.size)">...</span>
                    </div>

                    {{-- Drop target overlay --}}
                    <div class="absolute inset-0 z-40 transition-colors duration-300"
                        @dragenter.prevent="fileDropping = index"
                        @dragleave.prevent="fileDropping = null"
                        :class="{ 'bg-blue-200 bg-opacity-80 dark:bg-blue-900 dark:bg-opacity-60': fileDropping === index && fileDragging !== index }">
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- Hidden file list for form submission --}}
    <div class="hidden">
        <template x-for="(item, index) in files" :key="item.id">
            <input type="file" :name="'files[]'" :data-index="index" :ref="'fileInput' + index" />
        </template>
    </div>

    <script>
        function galleryDropzone() {
            return {
                files: [],
                fileDragging: null,
                fileDropping: null,
                dragOver: false,
                nextId: 1,

                init() {
                    // nothing special needed for Alpine v3
                },

                humanFileSize(size) {
                    if (!size) return '0 B';
                    const i = Math.floor(Math.log(size) / Math.log(1024));
                    return (
                        (size / Math.pow(1024, i)).toFixed(2) * 1 +
                        ' ' +
                        ['B', 'kB', 'MB', 'GB', 'TB'][i]
                    );
                },

                addFiles(e) {
                    const fileList = e.target.files || e.dataTransfer?.files;
                    if (!fileList || fileList.length === 0) return;

                    Array.from(fileList).forEach(file => {
                        const id = this.nextId++;
                        const previewUrl = file.type.startsWith('image/') || file.type.startsWith('video/')
                            ? URL.createObjectURL(file)
                            : null;

                        this.files.push({
                            id: id,
                            file: file,
                            type: file.type,
                            previewUrl: previewUrl,
                        });
                    });

                    // Clear input so same files can be selected again
                    if (e.target.value) e.target.value = '';
                },

                remove(index) {
                    if (this.files[index] && this.files[index].previewUrl) {
                        URL.revokeObjectURL(this.files[index].previewUrl);
                    }
                    this.files.splice(index, 1);
                },

                dragStart(e, index) {
                    this.fileDragging = index;
                    e.dataTransfer.effectAllowed = 'move';
                },

                // Drop handled via dragover on individual items
                // But we need a global drop handler on the grid
                handleDrop(e, targetIndex) {
                    if (this.fileDragging === null || this.fileDragging === targetIndex) {
                        this.fileDragging = null;
                        this.fileDropping = null;
                        return;
                    }

                    const removed = this.files.splice(this.fileDragging, 1);
                    this.files.splice(targetIndex, 0, ...removed);

                    this.fileDropping = null;
                    this.fileDragging = null;
                },

                getFiles() {
                    return this.files.map(f => f.file);
                },

                clear() {
                    this.files.forEach(item => {
                        if (item.previewUrl) URL.revokeObjectURL(item.previewUrl);
                    });
                    this.files = [];
                    this.fileDragging = null;
                    this.fileDropping = null;
                }
            };
        }
    </script>
</div>
