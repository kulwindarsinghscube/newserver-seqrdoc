<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Paystack...</title>
</head>
<body>
    <h3 style="text-align:center;margin-top:40px;">Redirecting to Paystack, please wait...</h3>

    <form id="paystackForm" action="{{ $authorizationUrl }}" method="GET">
    </form>

    <script>
        document.getElementById('paystackForm').submit();
    </script>
</body>
</html>




