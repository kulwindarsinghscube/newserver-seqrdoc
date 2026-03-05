<!DOCTYPE html>
<html>

<head>
    <title>Student Summary Report</title>
    
</head>

<body>
    <div class="container">
        <h1>Hello Admin,</h1>
        <p>Here is summary report till date:</p>
        <ul>
            <?php $__currentLoopData = $all_count; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><strong><?php echo e(ucfirst($status)); ?>:</strong> <?php echo e($count); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <?php 
        $total_count = array_sum($all_count);
        $total_registration_complete = (int) ($all_count['student re-acknowledged new data as correct, Payment is completed and preview pdf is approved'] ?? 0) +
                                       (int) ($all_count['student acknowledge all data as correct, Payment is completed and preview pdf is approved'] ?? 0);
        ?>
        <h2><strong>Total Student Count:</strong> <?php echo e($total_count); ?></h2>
        <h2><strong>Total Registration Completed :</strong> <?php echo e($total_registration_complete); ?></h2>

        <h3><strong>Collection Mode:</strong></h3>
        <ul>
            
            <li><strong>Collecting by Post (India):</strong> <?php echo e($collection_mode['by_post_india_registration_completed']); ?> (<?php echo e($collection_mode['by_post_india_all']); ?>)</li>
            <li><strong>Collecting by Post (International):</strong> <?php echo e($collection_mode['by_post_international_registration_completed']); ?> (<?php echo e($collection_mode['by_post_international_all']); ?>)</li>
        </ul>
        <?php echo $__env->make('convodataverification.emails.email_footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</body>

</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/emails/student_summary.blade.php ENDPATH**/ ?>