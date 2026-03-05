<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Download</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
    <div class="header bg-gradient-success py-lg-7">
        <div class="container text-center">
            <div class="cred-card-body" style="padding: 12px; color: rgb(80, 54, 101); font-size: small;">
                <div align="center">
                    <table class="cert" id="credCardBody" style="border: 5px solid; min-height: 30rem; border-right: 5px solid #b2bbc1; border-left: 5px solid #b2bbc1; width: 50%; font-family: arial; color: rgb(80, 54, 101); background-color: white;">
                        <tr>
                            <td align="center" class="crt_logo">
                                <img src="{{ asset('assets/hypersign.jpg') }}" class="mt-2" width="100px" height="100px" alt="logo">
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <h1 class="crt_title" style="letter-spacing: 1px; color: rgb(80, 54, 101) !important;">{{ $vcDetails['degreeName'] }}</h1>
                                <h2 style="font-size: larger; color: rgb(80, 54, 101);">CERTIFICATE</h2>
                                <p style="margin-bottom: 0;">This Certificate is awarded to</p>
                                <h1 class="crt_user" style="font-family: 'Satisfy', cursive; font-size: 40px; margin-top: 0; margin-bottom: 0;">{{ $vcDetails['recipientFullName'] }}</h1>
                                <p style="margin-bottom: 0;">for successfully completing {{ $vcDetails['degreeType'] }} in {{ $vcDetails['degreeName'] }}</p>
                                <div style="display: flex;">
                                    <div style="text-align: center; flex: 1;">
                                        <h4 class="mt-4">Issued By</h4>
                                        <span>{{ $vcDetails['issuerName'] }}</span>
                                    </div>
                                    <div style="text-align: center; flex: 1;">
                                        <h4 class="mt-4">Date</h4>
                                        <span>{{ \Carbon\Carbon::parse($vcDetails['issuedDate'])->format('F j, Y') }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary" id="downloadPdf">Download as PDF</button>
                    <button class="btn btn-primary" id="downloadPng">Download as PNG</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            function downloadBlob(data, filename, type) {
                const blob = new Blob([data], { type });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                link.click();
            }

            $('#downloadPdf').click(async function () {


                const url = "{{route('certificate.createdid')}}";
                
                $.ajax({
                    url: url,
                    type: "GET",
                    contentType: "application/json",
                    success: function(response) {
                        console.log("Success:", response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", status, error);
                    }
                });

                // https://ent-7e2e968.api.entity.hypersign.id/api/v1/did/create

                // const element = document.getElementById('credCardBody');
                // const canvas = await html2canvas(element);
                // const imageDataURL = canvas.toDataURL('image/png');

                // const response = await fetch('/generate-pdf', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json',
                //         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                //     },
                //     body: JSON.stringify({ image: imageDataURL })
                // });

                // const pdfBlob = await response.blob();
                // downloadBlob(pdfBlob, 'certificate.pdf', 'application/pdf');
            });

            $('#downloadPng').click(async function () {
                const element = document.getElementById('credCardBody');
                const canvas = await html2canvas(element);
                const imageDataURL = canvas.toDataURL('image/png');

                const link = document.createElement('a');
                link.href = imageDataURL;
                link.download = 'certificate.png';
                link.click();
            });
        });
    </script>
</body>
</html>
