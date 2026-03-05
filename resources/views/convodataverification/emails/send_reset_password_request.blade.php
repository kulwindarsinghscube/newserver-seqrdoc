<!DOCTYPE html>
<html>

<head>
    <title>Password Reset Request</title>
</head>

<body>
    <h1>Password Reset Request</h1>
    <p>Dear Student,</p>
    <p>We received a request to reset your password. </p>
    <p>Click the button <a href="{{ @$resetLink }}"
    target="_blank" class="btn" style="background-color: #337ab7; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Your Password</a></p>
    <p>Or</p>

    <p>You can reset your password using the following link:
        <a href="{{ @$resetLink }}" target="_blank">{{ @$resetLink }}</a>
    </p>

    <p>Thank you!</p>
    @include('convodataverification.emails.email_footer')

</body>

</html>
