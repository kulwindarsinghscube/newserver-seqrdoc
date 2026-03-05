<!DOCTYPE html>
<html>

<head>
    <title>Password Reset Request</title>
</head>

<body>
    <h1>Password Reset Request</h1>
    <p>Dear Student,</p>
    <p>We received a request to reset your password. </p>
    <p>Click the button <a href="<?php echo e(@$resetLink); ?>"
    target="_blank" class="btn" style="background-color: #337ab7; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Your Password</a></p>
    <p>Or</p>

    <p>You can reset your password using the following link:
        <a href="<?php echo e(@$resetLink); ?>" target="_blank"><?php echo e(@$resetLink); ?></a>
    </p>

    <p>Thank you!</p>
    <?php echo $__env->make('convodataverification.emails.email_footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</body>

</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/emails/send_reset_password_request.blade.php ENDPATH**/ ?>