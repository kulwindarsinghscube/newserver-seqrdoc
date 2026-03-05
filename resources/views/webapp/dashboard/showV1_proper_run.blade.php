<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Code Scanner</title>
  <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.18.6/umd/index.min.js"></script>
</head>
<body>
  <h1>QR Code Scanner</h1>
  <video id="preview" style=" max-height: 300px;"></video>
  <p id="output">QR Code Result will be displayed here</p>
  <select id="cameraSelect"></select>
  <input type="file" id="fileInput" accept="image/*" />
  
  <script type="text/javascript">
    const videoElem = document.getElementById("preview");
    const output = document.getElementById("output");
    const fileInput = document.getElementById("fileInput");
    const cameraSelect = document.getElementById("cameraSelect");

    // Create ZXing QR Code reader instance
    const codeReader = new ZXing.BrowserQRCodeReader();

    // Populate camera options
    navigator.mediaDevices.getUserMedia({ video: true })
    .then(() => {
      codeReader.listVideoInputDevices().then((devices) => {
        devices.forEach((device) => {
          const option = document.createElement("option");
          option.value = device.deviceId;
          option.text = device.label || `Camera ${cameraSelect.length + 1}`;
          cameraSelect.appendChild(option);
        });

        // Automatically start scanning with the first camera
        if (devices.length > 0) {
          startScanning(devices[0].deviceId);
        }
      });
    })
    .catch((err) => {
      output.textContent = `Error accessing camera: ${err}`;
    });

    // Start scanning from the selected camera
    function startScanning(deviceId) {
      const constraints = {
        video: {
          deviceId: { exact: deviceId },
          width: { ideal: 1920 }, // High resolution
          height: { ideal: 1080 },
        },
      };
      codeReader.decodeFromVideoDevice(deviceId, videoElem, (result, err) => {
        if (result) {
          output.textContent = `QR Code detected: ${result.text}`;
          alert(`QR Code detected: ${result.text}`);
        } else if (err && !(err instanceof ZXing.NotFoundException)) {
          output.textContent = `Error: ${err}`;
        }
      }, constraints);
    }

    // Handle camera selection change
    cameraSelect.addEventListener("change", () => {
      const selectedDeviceId = cameraSelect.value;
      if (selectedDeviceId) {
        codeReader.reset(); // Stop any ongoing scanning
        startScanning(selectedDeviceId);
      }
    });

    // Handle image file input
    fileInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function () {
          const img = new Image();
          img.onload = function () {
            codeReader.decodeFromImage(img).then((result) => {
              output.textContent = `QR Code detected from image: ${result.text}`;
              alert(`QR Code detected from image: ${result.text}`);
            }).catch((err) => {
              output.textContent = `Error: ${err}`;
            });
          };
          img.src = reader.result;
        };
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>
</html>
