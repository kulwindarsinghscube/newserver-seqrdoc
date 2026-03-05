<!DOCTYPE html>
<html>

<head>
    <title>Payment Confirmation</title>
</head>

<body>
    <h1>Dear Student,</h1>
    <p>Thank you for your payment of <?php echo e(@$paymentDetails['txn_amount']); ?>.</p>
    <p>Your transaction ID is: <?php echo e(@$paymentDetails['txn_id']); ?>.</p>
    <p>Your bank transaction ID is: <?php echo e(@$paymentDetails['bank_txn_id']); ?>.</p>
    <p>Transaction Date: <?php echo e(\Carbon\Carbon::parse(@$paymentDetails['txn_date'])->format('d-m-Y h:i A')); ?>.</p>
    <p>Status: <?php echo e(@$paymentDetails['status'] == 'TXN_SUCCESS' ? 'SUCCESS' : ''); ?>.</p>
    <p>Payment Mode: <?php echo e(@$paymentDetails['payment_mode']); ?>.</p>
    <p>Thank you for your payment!</p>

    <p style="color:red">Note : Your registration process is currently incomplete. It will be finalized once you verify all details and approve the PDF.</p>
    <?php echo $__env->make('convodataverification.emails.email_footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
</body>

</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/emails/payment_confirmation.blade.php ENDPATH**/ ?>