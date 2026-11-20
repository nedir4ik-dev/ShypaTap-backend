<!DOCTYPE html>
<html lang="tk" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ŞypaTap - Dolandyryş paneli')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100">
    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm md:hidden"></div>

    <div class="admin-shell flex min-h-screen w-full">
        @include('layouts.partials.sidebar')

        <div class="admin-main flex min-h-screen min-w-0 flex-1 flex-col">
            <div class="sticky top-0 z-30 px-3 pt-3 sm:px-5 lg:px-7">
                @include('layouts.partials.navbar')
            </div>

            <main class="w-full min-w-0 flex-1 px-3 py-5 sm:px-5 lg:px-7 lg:py-7">
                <div class="mx-auto w-full max-w-[1600px]">
                    @yield('content')
                </div>
            </main>

            <div class="px-3 pb-3 sm:px-5 lg:px-7">
                @include('layouts.partials.footer')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.classList.toggle('dark', storedTheme === 'dark');
            updateThemeIcon(storedTheme);
        });

        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            const newTheme = isDark ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (icon) icon.className = theme === 'dark' ?
                'ti ti-sun text-xl text-amber-400' :
                'ti ti-moon text-xl';
        }

        window.toggleSidebar = function() {
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar) sidebar.classList.toggle('-translate-x-full');
            if (backdrop) backdrop.classList.toggle('hidden');
        };
    </script>
    @stack('scripts')
</body>

</html>