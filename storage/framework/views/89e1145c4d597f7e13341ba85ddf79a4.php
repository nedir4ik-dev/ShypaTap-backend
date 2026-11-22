<?php $__env->startSection('title', 'Bahalandyrmalar'); ?>
<?php $__env->startSection('header', 'Lukmanlara berlen bahalar'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-5">
    <?php if(session('success')): ?><div class="rounded-xl bg-emerald-50 p-3 text-emerald-700"><?php echo e(session('success')); ?></div><?php endif; ?>
    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700">
                    <th class="p-3">Näsag</th>
                    <th class="p-3">Lukman</th>
                    <th class="p-3">Bron</th>
                    <th class="p-3">Baha</th>
                    <th class="p-3">Teswir</th>
                    <th class="p-3">Hereket</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t dark:border-gray-700">
                    <td class="p-3"><?php echo e($review->patient?->name); ?></td>
                    <td class="p-3"><?php echo e($review->doctor?->user?->name); ?></td>
                    <td class="p-3"><?php echo e($review->appointment?->booking_code); ?></td>
                    <td class="p-3 font-bold text-amber-600">★ <?php echo e(number_format((float)$review->rating, 1)); ?>/5</td>
                    <td class="p-3 max-w-sm"><?php echo e($review->comment ?: '—'); ?></td>
                    <td class="p-3">
                        <form method="POST" action="<?php echo e(route('admin.reviews.destroy', $review)); ?>" onsubmit="return confirm('Bu bahalandyrmany pozmalymy?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white">Poz</button></form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">Bahalandyrma ýok.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo e($reviews->links()); ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbookair/Desktop/ShypaTap-backend/resources/views/admin/reviews/index.blade.php ENDPATH**/ ?>