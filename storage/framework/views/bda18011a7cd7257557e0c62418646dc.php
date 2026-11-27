<!DOCTYPE html>
<html lang="tk" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'ŞypaTap - Dolandyryş paneli'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100">
    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm md:hidden"></div>

    <div class="admin-shell flex min-h-screen w-full">
        <?php echo $__env->make('layouts.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="admin-main flex min-h-screen min-w-0 flex-1 flex-col">
            <div class="sticky top-0 z-30 px-3 pt-3 sm:px-5 lg:px-7">
                <?php echo $__env->make('layouts.partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <main class="w-full min-w-0 flex-1 px-3 py-5 sm:px-5 lg:px-7 lg:py-7">
                <div class="mx-auto w-full max-w-[1600px]">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>

            <div class="px-3 pb-3 sm:px-5 lg:px-7">
                <?php echo $__env->make('layouts.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html><?php /**PATH /Users/macbookair/Desktop/ShypaTap-backend/resources/views/layouts/app.blade.php ENDPATH**/ ?>