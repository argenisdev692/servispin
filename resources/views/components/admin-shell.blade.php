{{--
    Shell del panel admin: sidebar, header y toggle dark/light.
    El modo oscuro solo aplica dentro de .admin-shell.dark (no sigue prefers-color-scheme).
--}}
@props(['lang' => 'es'])

<div
    class="admin-shell min-h-screen text-slate-900 dark:text-slate-100"
    :class="{ 'dark': dark }"
    x-data="data()"
    lang="{{ $lang }}"
    x-init="
        const applyScheme = (value) => { document.documentElement.style.colorScheme = value ? 'dark' : 'light'; };
        applyScheme(dark);
        $watch('dark', applyScheme);
    "
>
    <div class="flex h-screen bg-slate-50 dark:bg-slate-950" :class="{ 'overflow-hidden': isSideMenuOpen }">
        <x-menu-sidebar />

        <div class="flex flex-col flex-1 w-full min-w-0">
            <x-header-dashboard />

            <main class="h-full overflow-y-auto bg-slate-50 dark:bg-slate-950">
                {{ $slot }}
            </main>
        </div>
    </div>
</div>
