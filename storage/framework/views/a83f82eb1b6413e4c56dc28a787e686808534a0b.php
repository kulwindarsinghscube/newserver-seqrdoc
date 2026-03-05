<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Paystack...</title>
</head>
<body>
    <h3 style="text-align:center;margin-top:40px;">Redirecting to Paystack, please wait...</h3>

    <form id="paystackForm" action="<?php echo e($authorizationUrl); ?>" method="GET">
    </form>

    <script>
        document.getElementById('paystackForm').submit();
    </script>
</body>
</html>




<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/payment/paystack/paystackPost.blade.php ENDPATH**/ ?>