<?php $__env->startSection('title', 'Tölegler'); ?>
<?php $__env->startSection('header', 'Töleg dolandyryşy'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-5">
    <?php if(session('success')): ?>
    <div class="rounded-xl bg-emerald-50 p-3 text-emerald-700">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>
    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700">
                    <th class="p-3">Bron belgisi</th>
                    <th class="p-3">Näsag</th>
                    <th class="p-3">Lukman</th>
                    <th class="p-3">Klinika</th>
                    <th class="p-3">Mukdar</th>
                    <th class="p-3">Usul</th>
                    <th class="p-3">Ýagdaý</th>
                    <th class="p-3">Tölenen wagty</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t dark:border-gray-700">
                    <td class="p-3"><a class="text-blue-300 underline" href="<?php echo e(route('admin.payments.show',$payment)); ?>"><?php echo e($payment->appointment?->booking_code); ?></a></td>
                    <td class="p-3"><?php echo e($payment->appointment?->patient?->name); ?></td>
                    <td class="p-3"><?php echo e($payment->appointment?->doctor?->user?->name); ?></td>
                    <td class="p-3"><?php echo e($payment->appointment?->clinic?->name); ?></td>
                    <td class="p-3"><?php echo e(number_format((float)$payment->amount,2)); ?> <?php echo e($payment->currency); ?></td>
                    <td class="p-3"><?php echo e($payment->payment_method); ?></td>
                    <td class="p-3"><?php echo e($payment->status); ?></td>
                    <td class="p-3"><?php echo e($payment->paid_at?->format('Y-m-d H:i') ?? '—'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr>
                    <td colspan="8" class="p-8 text-center text-gray-500">Töleg ýazgysy ýok.</td>
                </tr><?php endif; ?>
            </tbody>
        </table>
    </div><?php echo e($payments->links()); ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbookair/Desktop/ShypaTap-backend/resources/views/admin/payments/index.blade.php ENDPATH**/ ?>