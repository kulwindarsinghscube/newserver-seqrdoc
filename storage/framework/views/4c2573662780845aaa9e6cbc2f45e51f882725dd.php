<!DOCTYPE html>
<html>

<head>
    <title>Registration Correction</title>
</head>

<body>
    <h1>Dear Student,</h1>
    <p>The admin has made corrections to your details.</p>
    <p>Please <a href="https://convocation.mitwpu.edu.in/convo_student/login">log in here</a> to verify the changes and
        ensure everything is
        correct.</p>
    <p>If you prefer, you can also visit the login page directly at: <a
            href="https://convocation.mitwpu.edu.in/convo_student/login"><strong>https://convocation.mitwpu.edu.in/convo_student/login</strong></a>
    </p>

    <p>Thank you !</p>

    <p style="color:red">Note : Your registration process is currently incomplete. It will be finalized once you verify all details, complete the payment and approve the PDF.</p>

    

    <?php echo $__env->make('convodataverification.emails.email_footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>

</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/emails/admin_correction.blade.php ENDPATH**/ ?>