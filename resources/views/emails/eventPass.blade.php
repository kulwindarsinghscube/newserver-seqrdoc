<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Pass</title>
</head>
<body>
    <h3>Hi {{ $vc['recipientName'] }}</h3>
    <p>You are invited to the {{ $vc['eventName'] }} event!</p>
    <p>Click here to get your event pass:</p>
    <a href="{{ url('/event/pass/?jwt=' . $token) }}" target="_blank">Download Event Pass</a>
    <br />
    <p>Thanks & Regards, <br />{{ $vc['issuerName'] }}</p>
</body>
</html>
