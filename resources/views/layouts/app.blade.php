<!DOCTYPE html>
<html lang="tk" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ŞypaTap - Lukman Bronlamak Ulgamy')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="h-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200 overflow-x-hidden">

    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm hidden md:hidden"></div>

    <div class="flex h-screen overflow-hidden">

        @include('layouts.partials.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            @include('layouts.partials.navbar')

            <main class="flex-1 p-6">
                @yield('content')
            </main>

            @include('layouts.partials.footer')

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const storedTheme = localStorage.getItem('theme') || 'light';
            if (storedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
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
            if (icon) {
                icon.className = theme === 'dark' ? 'ti ti-sun text-yellow-500 text-xl' : 'ti ti-moon text-xl';
            }
        }

        window.toggleSidebar = function() {
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
            if (backdrop) {
                backdrop.classList.toggle('hidden');
            }
        }
    </script>

    @stack('scripts')
</body>

</html>
