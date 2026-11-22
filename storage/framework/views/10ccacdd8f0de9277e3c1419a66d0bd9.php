<?php $__env->startSection('title', 'Ulanyjylar Sanawy'); ?>

<?php $__env->startSection('header', 'Ulanyjylar Dolandyryşy'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <?php if(session('success')): ?>
    <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-400 px-4 py-3 rounded-2xl text-sm flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <i class="ti ti-circle-check text-xl"></i>
            <span><?php echo e(session('success')); ?></span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">
            <i class="ti ti-x text-lg"></i>
        </button>
    </div>
    <?php endif; ?>

    <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-2xl shadow-sm flex flex-col md:flex-row items-center gap-4">
        <div class="relative flex-1 w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <i class="ti ti-search text-lg"></i>
            </span>
            <input
                type="text"
                name="search"
                value="<?php echo e(request('search')); ?>"
                placeholder="Ady, email ýa-da telefon boýunça gözleg..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div class="w-full md:w-56">
            <select name="role" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Ähli rollar</option>
                <option value="patient" <?php echo e(request('role') == 'patient' ? 'selected' : ''); ?>>Näsag (Patient)</option>
                <option value="doctor" <?php echo e(request('role') == 'doctor' ? 'selected' : ''); ?>>Lukman (Doctor)</option>
                <option value="receptionist" <?php echo e(request('role') == 'receptionist' ? 'selected' : ''); ?>>Registratura</option>
                <option value="admin" <?php echo e(request('role') == 'admin' ? 'selected' : ''); ?>>Admin</option>
            </select>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <button type="submit" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="ti ti-filter text-lg"></i> Filtrle
            </button>
            <?php if(request('search') || request('role')): ?>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Arassala">
                <i class="ti ti-refresh text-lg"></i>
            </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden transition-colors">

        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h5 class="font-bold text-gray-900 dark:text-white text-lg flex items-center gap-2">
                <i class="ti ti-users text-blue-600 dark:text-blue-400"></i>
                Ulanyjylaryň Sanawy (Jemi: <?php echo e($users->total()); ?>)
            </h5>
            <?php if(Route::has('admin.users.create')): ?>
            <a href="<?php echo e(route('admin.users.create')); ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="ti ti-user-plus text-lg"></i>
                Täze Ulanyjy Goşmak
            </a>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                        <th class="py-3.5 px-6 font-semibold">#ID</th>
                        <th class="py-3.5 px-6 font-semibold">Ady we Familiýasy</th>
                        <th class="py-3.5 px-6 font-semibold">Email Salgysy</th>
                        <th class="py-3.5 px-6 font-semibold">Telefon</th>
                        <th class="py-3.5 px-6 font-semibold">Roly / Derejesi</th>
                        <th class="py-3.5 px-6 font-semibold text-end">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition">
                        <td class="py-4 px-6 text-gray-500 dark:text-gray-400 font-medium">#<?php echo e($user->id); ?></td>
                        <td class="py-4 px-6 font-semibold text-gray-900 dark:text-white">
                        <div class="flex items-center gap-3">
                            <?php if($user->avatar_url): ?>
                                <img src="<?php echo e($user->avatar_url); ?>" alt="<?php echo e($user->name); ?>" class="h-9 w-9 rounded-full object-cover">
                            <?php else: ?>
                                <span class="h-9 w-9 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300 flex items-center justify-center font-bold"><?php echo e(mb_strtoupper(mb_substr($user->name, 0, 1))); ?></span>
                            <?php endif; ?>
                            <?php echo e($user->name); ?>

                        </div>
                        </td>
                        <td class="py-4 px-6 text-gray-600 dark:text-gray-300"><?php echo e($user->email); ?></td>
                        <td class="py-4 px-6 text-gray-600 dark:text-gray-300"><?php echo e($user->phone ?? 'Görkezilmedik'); ?></td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                                <?php echo e(ucfirst($user->role) ?? 'User'); ?>

                            </span>
                        </td>
                        <td class="py-4 px-6 text-end">
                            <div class="inline-flex items-center gap-1.5">
                                <?php if(Route::has('admin.users.edit')): ?>
                                <a href="<?php echo e(route('admin.users.edit', $user->id)); ?>" class="p-2 text-amber-600 hover:text-white hover:bg-amber-600 dark:text-amber-400 dark:hover:bg-amber-600 rounded-xl transition shadow-xs" title="Üýtgetmek">
                                    <i class="ti ti-edit text-lg"></i>
                                </a>
                                <?php endif; ?>

                                <?php if(auth()->id() !== $user->id && Route::has('admin.users.destroy')): ?>
                                <form action="<?php echo e(route('admin.users.destroy', $user->id)); ?>" method="POST" class="inline" onsubmit="return confirm('Hakykatdan hem bu ulanyjyny pozmak isleýärsiňizmi?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-2 text-red-600 hover:text-white hover:bg-red-600 dark:text-red-400 dark:hover:bg-red-600 rounded-xl transition shadow-xs" title="Pozmak">
                                        <i class="ti ti-trash text-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 dark:text-gray-400 py-12 italic">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="ti ti-database-off text-4xl text-gray-400"></i>
                                <span class="font-medium">Gözlegiňize görä hiç hili ulanyjy tapylmady.</span>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(method_exists($users, 'links') && $users->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <?php echo e($users->links()); ?>

        </div>
        <?php endif; ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbookair/Desktop/ShypaTap-backend/resources/views/admin/users/index.blade.php ENDPATH**/ ?>