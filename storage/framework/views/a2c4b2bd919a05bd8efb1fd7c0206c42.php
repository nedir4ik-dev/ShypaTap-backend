<?php
$user = auth()->user();
$initial = mb_strtoupper(mb_substr($user?->name ?? 'A', 0, 1));
?>

<header class="flex min-h-16 w-full items-center justify-between gap-4 rounded-4xl border border-slate-200/80 bg-white/30 px-4 py-2.5 shadow-sm backdrop-blur-sm dark:border-slate-300/30 dark:bg-slate-900/30 sm:px-5">
    <div class="flex min-w-0 items-center gap-3">
        <button type="button" onclick="toggleSidebar()" aria-label="Menýuny aç"
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-3xl border border-slate-200 text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 md:hidden">
            <i class="ti ti-menu-2 text-xl leading-none"></i>
        </button>
        <div class="truncate text-base font-bold text-slate-900 dark:text-white sm:text-lg">
            <?php echo $__env->yieldContent('header', 'Dolandyryş paneli'); ?>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        <button type="button" onclick="toggleDarkMode()" title="Režimi üýtget" aria-label="Režimi üýtget"
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            <i id="theme-icon" class="ti ti-moon text-xl leading-none"></i>
        </button>

        <a href="<?php echo e(route('admin.dashboard')); ?>"
            class="flex max-w-48 items-center gap-2 rounded-4xl border border-slate-200 px-1.5 py-1.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">
            <?php if($user?->avatar_url): ?>
            <img src="<?php echo e($user->avatar_url); ?>" alt="Profil" class="h-8 w-8 shrink-0 rounded-full object-cover">
            <?php else: ?>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold leading-none text-blue-700 dark:bg-blue-500/45 dark:text-blue-300">
                <?php echo e($initial); ?>

            </span>
            <?php endif; ?>
            <span class="hidden truncate sm:inline"><?php echo e($user?->name ?? 'Admin'); ?></span>
        </a>
    </div>
</header><?php /**PATH /Users/macbookair/Desktop/ShypaTap-backend/resources/views/layouts/partials/navbar.blade.php ENDPATH**/ ?>