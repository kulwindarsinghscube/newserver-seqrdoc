<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Code Scanner</title>
  <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
  <style>
    #reader {
      width: 100%;
      max-width: 500px;
      margin: 20px auto;
      display: none; /* Initially hidden */
    }
    #output {
      margin-top: 20px;
      font-size: 1.2em;
      color: green;
    }
    button {
      margin: 10px;
      padding: 10px 20px;
      font-size: 1em;
    }
    #qr-reader .scanapp-branding {
	    display: none !important;
	}
  </style>
</head>
<body>
  	<div id="qr-reader" style="width:400px"></div> 
	
	


	<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

	<script>
	    // function onScanSuccess(qrCodeMessage) { alert('text'); }

	    // Success callback
        function onScanSuccess(decodedText, decodedResult) {
            alert("QR Code detected: " + decodedText);
        }

		var html5QrcodeScanner = new Html5QrcodeScanner(
		     "qr-reader", { fps: 60, qrbox: 250,videoConstraints: {
		            facingMode: "environment",  // Use back camera for better focus
		            width: { ideal: 1920 },  // Set higher resolution for better scanning
		            height: { ideal: 1080 }
		        } 
		    });
		html5QrcodeScanner.render(onScanSuccess);

		function onScanError(errorMessage) {
		    alert("Error scanning QR code: ", errorMessage);
		}

	</script>
</body>
</html>
