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
    <div id="sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm md:hidden"></div>

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

        const sidebar = document.getElementById('app-sidebar');
        const sidebarBackdrop = document.getElementById('sidebar-backdrop');
        const sidebarToggles = document.querySelectorAll('[data-sidebar-toggle]');
        const mobileViewport = window.matchMedia('(max-width: 767px)');

        function setSidebarState(isOpen) {
            const isMobile = mobileViewport.matches;

            if (!sidebar || !sidebarBackdrop) {
                return;
            }

            const shouldShowSidebar = isMobile && isOpen;
            sidebar.classList.toggle('-translate-x-full', isMobile && !isOpen);
            sidebarBackdrop.classList.toggle('hidden', !shouldShowSidebar);
            sidebar.toggleAttribute('inert', isMobile && !isOpen);
            sidebar.setAttribute('aria-hidden', String(isMobile && !isOpen));
            document.body.classList.toggle('overflow-hidden', shouldShowSidebar);

            sidebarToggles.forEach((toggle) => {
                toggle.setAttribute('aria-expanded', String(isMobile ? isOpen : true));
            });
        }

        window.toggleSidebar = function() {
            setSidebarState(sidebar?.classList.contains('-translate-x-full'));
        };

        sidebarToggles.forEach((toggle) => {
            toggle.addEventListener('click', window.toggleSidebar);
        });

        sidebarBackdrop?.addEventListener('click', () => setSidebarState(false));

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileViewport.matches) {
                setSidebarState(false);
            }
        });

        mobileViewport.addEventListener('change', () => setSidebarState(false));

        setSidebarState(false);
    </script>
    @stack('scripts')
</body>

</html>