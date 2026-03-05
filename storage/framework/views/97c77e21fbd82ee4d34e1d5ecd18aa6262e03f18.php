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
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>"> 
        <!--  Css files  -->
        <?php echo $__env->make('admin.layout.css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->yieldContent('style'); ?>
        <?php
            $domain = \Request::getHost();
            $subdomain = explode('.', $domain);
            if($subdomain[0] == \Config('constant.raisoni_subdomain'))
            {
        ?>
        <style type="text/css">
            .navbar-default .navbar-nav>li>a {
                color: #DEEBFF !important;
                font-family: roboto;
                font-weight: 500;
                font-size: 11px;
                padding: 8px;
                margin: 10px 0;
            }    
        </style>
        <?php
        }
        ?>

        <?php 
        if($subdomain[0] == 'ksg' ){ ?>

            <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <?php } ?>

    </head>
    
    <!-- <?php echo $__env->yieldContent('start_form'); ?> -->
        <!-- Header file -->
        <?php echo $__env->make('admin.layout.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <!-- Side-Bar-->
        
        <!-- Content Start here -->
       
            <?php echo $__env->yieldContent('content'); ?>
        <!-- Content End here -->
        <!-- footer contant -->
        <!-- <?php echo $__env->yieldContent('end_form'); ?> -->
    <!-- Js file -->
        <?php echo $__env->make('admin.layout.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <!-- rohit code 25/04/2023 -->
        <?php echo $__env->make('admin.layout.manual_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- rohit code 25/04/2023 -->

        <?php echo $__env->make('admin.layout.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->yieldContent('script'); ?>

        <!-- rohit code 25/04/2023 -->
        <?php echo $__env->make('admin.layout.modal_script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- rohit code 25/04/2023 -->
    
</html><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/layout/layout.blade.php ENDPATH**/ ?>