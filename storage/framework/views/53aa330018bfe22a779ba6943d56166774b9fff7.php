<!DOCTYPE html>
<html>

<head>
    <title>Approved PDF</title>
</head>

<body>
    <h1>Dear Student,</h1>
    <p>We appreciate your approval of the PDF.</p>
    <p>We are pleased to announce that your registration process has been successfully completed.</p> 
    <p>Thank you!</p>
     <?php echo $__env->make('convodataverification.emails.email_footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
</body>

</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/emails/approved_pdf.blade.php ENDPATH**/ ?>