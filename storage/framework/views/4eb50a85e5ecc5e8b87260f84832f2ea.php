<?php $__env->startSection('title', 'Bronlar Sanawy'); ?>

<?php $__env->startSection('header', 'Bron Dolandyryşy'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="ti ti-calendar-event text-blue-600 dark:text-blue-400"></i>
            Ähli Bron Edilmeler
        </h1>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition">
            <i class="ti ti-arrow-left text-lg"></i>
            Dashboard
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">
                        <th class="py-3.5 px-4">Bron Kody</th>
                        <th class="py-3.5 px-4">Näsag</th>
                        <th class="py-3.5 px-4">Lukman</th>
                        <th class="py-3.5 px-4">Klinika</th>
                        <th class="py-3.5 px-4">Sene / Wagt</th>
                        <th class="py-3.5 px-4">Ýagdaýy</th>
                        <th class="py-3.5 px-4 text-right">Amal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                        <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-white">
                            <a href="<?php echo e(route('admin.appointments.show', $app->id)); ?>" class="text-blue-600 dark:text-blue-400 hover:underline">
                                <?php echo e($app->booking_code ?? 'ST-'.$app->id); ?>

                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-gray-800 dark:text-gray-200"><?php echo e($app->patient->name ?? 'ID: '.$app->patient_id); ?></td>
                        <td class="py-3.5 px-4 text-gray-800 dark:text-gray-200"><?php echo e($app->doctor->user->name ?? 'N/A'); ?></td>
                        <td class="py-3.5 px-4 text-gray-600 dark:text-gray-400"><?php echo e($app->clinic->name ?? 'N/A'); ?></td>
                        <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300">
                            <?php echo e($app->appointment_date?->format('d.m.Y')); ?> <?php echo e(\Illuminate\Support\Str::substr($app->start_time, 0, 5)); ?>

                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/30">
                                <?php echo e($app->status); ?>

                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="<?php echo e(route('admin.appointments.show', $app->id)); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20 text-xs font-semibold transition">
                                    <i class="ti ti-eye"></i> Gör
                                </a>
                                <form method="POST" action="<?php echo e(route('admin.appointments.destroy', $app->id)); ?>" onsubmit="return confirm('Bu bron ýazgysyny Trash bölümine geçirmek isleýärsiňizmi?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 dark:bg-red-500/10 dark:text-red-400">Trash</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500 dark:text-gray-400">Bron maglumaty tapylmady.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($appointments->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <?php echo e($appointments->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbookair/Desktop/ShypaTap-backend/resources/views/admin/appointments/index.blade.php ENDPATH**/ ?>