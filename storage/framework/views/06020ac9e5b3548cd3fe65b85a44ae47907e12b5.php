<?php $__env->startSection('style'); ?>
    <style type="text/css">
        .box__dragndrop,
        .box__uploading,
        .box__success,
        .box__error {
            display: none;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('top_fixed_content'); ?>
<nav class="navbar navbar-static-top">
    <div class="title">
        <h4><i class="fa fa-user"></i>Users</h4>
    </div>
    <div class="top_filter"></div>
    
</nav>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-title-w-btn">
                <h4 class="title">Unauthrorized Access!</h4>
            </div>
            <hr>
            <div class="card-body">
                <div class="login-box">
                    <section class="error-wrapper">
                        <!-- <div style="text-align: center; ">
                            <img src="<?php echo e(asset('backend/images/access_denied.jpg')); ?>"/>
                        </div> -->
                        <h2 class="f_bold text-danger">Access Denied</h2>
                        <h5 class="f_bold text-danger">Your Request for this page has been denied because of access control<h5> 
                        
                            <a href="<?= route('admin.dashboard') ?>"><span class="text-primary"><b><u>Return to Dashboard</u></b></span></a>
                        
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/permission_denied.blade.php ENDPATH**/ ?>