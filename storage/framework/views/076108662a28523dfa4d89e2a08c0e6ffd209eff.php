<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="icon" type="image/png" href="<?php echo e(asset('backend/images/fav.png')); ?>">
        <!--  Css files  -->
        <?php echo $__env->make('admin.layout.css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->yieldContent('style'); ?>
    </head>
    
    <!-- <?php echo $__env->yieldContent('start_form'); ?> -->
        <!-- Header file -->

        <!-- Side-Bar-->
        
        <!-- Content Start here -->
       
            <?php echo $__env->yieldContent('content'); ?>
        <!-- Content End here -->
        <!-- footer contant -->
        <!-- <?php echo $__env->yieldContent('end_form'); ?> -->
    <!-- Js file -->
        <?php echo $__env->make('admin.layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('admin.layout.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->yieldContent('script'); ?>
    
</html><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/layout/layoutnonheader.blade.php ENDPATH**/ ?>