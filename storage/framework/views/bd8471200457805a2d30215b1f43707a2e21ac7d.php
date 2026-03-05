<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>
<style>
    body {
        position: relative;
        overflow: hidden;
        min-height: 100vh;
        background-color: #f8f9fa;
    }

    .captcha {
        width: 100%;
        background: #f0f8ff;
        /* Light Alice Blue background */
        min-height: 63px;
        margin-bottom: 2%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #333;
        /* Dark Gray text */
        font-weight: bold;
        position: relative;
    }

    .captcha span {
        display: inline-block;
        margin: 0 5px;
        font-size: 30px;
        transition: transform 0.2s;
        color: #2c3e50;
        /* Dark Blue text color */
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo e(asset('backend/convodataverification/images/background_image.png')); ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        filter: blur(7px);
        z-index: -1;
    }

    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .login-form {
        width: 401px;
        max-width: 800px;
        padding: 2rem;
        background-color: #ffffff;
        border-radius: .5rem;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .login-form h1 {
        margin-bottom: 1rem;
    }

    .div {
        margin-bottom: 15px;
    }
</style>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="login-container">
            <div class="login-form">

                <h2 class="text-danger " style="text-align: center;">
                    <i class="fa fa-warning" style="font-size:48px;"></i><br>
                    Students are hereby notified that the registration process has been officially closed. </h2>

                
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('admin.layout.layoutnonheader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/student/auth/close.blade.php ENDPATH**/ ?>