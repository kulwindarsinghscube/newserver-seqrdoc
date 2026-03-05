<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to CCAvenue...</title>
</head>
<body onload="document.forms['ccavenue_form'].submit();">
    <form id="ccavenue_form" method="POST" action="{{ $paymentUrl }}">
        <input type="hidden" name="encRequest" value="{{ $encryptedData }}">
        <input type="hidden" name="access_code" value="{{ $accessCode }}">
        <noscript>
            <button type="submit">Click here if not redirected...</button>
        </noscript>
    </form>
</body>
</html>
