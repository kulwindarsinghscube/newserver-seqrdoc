<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate</title>
</head>
<body>
    <h3>Hi {{ $vc['recipientFullName'] }}</h3>
    <p>Congratulations! This is your {{ $vc['degreeType'] }} {{ $vc['degreeName'] }} Certificate!</p>
    <p>Click here to download:</p>
    <a href="{{ url('/credential/issue/?jwt=' . $token) }}" target="_blank">Download Certificate</a>
    <br />
    <p>Thanks & Regards, <br />{{ $vc['issuerName'] }}</p>
</body>
</html>
