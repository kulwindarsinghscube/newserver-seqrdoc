<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>

    {{-- ✅ Open Graph Meta Tags --}}
    <meta property="og:title" content="{{$url }}">
    <meta property="og:description" content="Click to view or download your verified certificate from SeQRDoc.">
    <meta property="og:image" content="{{$url }}">
    <meta property="og:url" content="{{$url }}">
    <meta property="og:type" content="website">
    
    {{-- ✅ Twitter Card (optional) --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{$url }}">
    <meta name="twitter:description" content="Download your official certificate.">
    <meta name="twitter:image" content="{{$url }}">

    <style>
        body { font-family: Arial; text-align: center; padding: 40px; }
        a.button {
            background: #0073b1; color: white; padding: 10px 20px;
            text-decoration: none; border-radius: 5px;
        }
    </style>
</head>
<body>

    <iframe src="{{$url}}"></iframe>

</body>
</html>
