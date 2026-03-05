<!DOCTYPE html>
<html>
<head>
	<title>Hypersign Certificate</title>

	<!-- Fonts -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .parseData pre {
      background-color: #f5f5f5;
      padding: 10px;
      border-radius: 5px;
      white-space: pre-wrap; /* Ensure wrapping for long lines */
    }
  </style>

</head>
<body>

  <div class="container">
    <!-- Button to open preview -->
    <button id="previewBtn" class="btn btn-primary">Preview</button>
    {{-- <button class="btn btn-success">Save</button> --}}
    <button id="saveBtn" class="btn btn-success">Save</button>
  
    <!-- Preview Container -->
    <div id="certPreview" class="container" style="display: none;">
      <div class="b-card-header p-1 border-0 accordion-header-theme" role="tab">
        <!-- Close Button -->
        <button id="closeBtn" class="btn btn-primary" style="float: right;">Close</button>
      </div>
  
      <div class="cred-card-body" style="padding: 12px; color: rgb(80, 54, 101); font-size: small;">
        <div class="container" align="center">
          <table class="cert" style="border: 5px solid #b2bbc1; width: 90%; font-family: Arial, sans-serif; color: rgb(80, 54, 101);">
            <tr>
              <td align="center" class="crt_logo">
                <img src="{{ asset('assets/hypersign.jpg') }}" class="mt-2" width="60px" height="60px" alt="Certificate Logo">
              </td>
            </tr>
            <tr>
              <td align="center">
                <h1 id="certName" class="crt_title" style="margin-top: 5px; letter-spacing: 1px; color: rgb(80, 54, 101);">
                  { Certificate Name }
                </h1>
                <h2 style="font-size: larger; color: rgb(80, 54, 101);">CERTIFICATE</h2>
                <p style="margin-bottom: 0;">This Certificate is awarded to</p>
                <h3 id="subName">{ Subject Name }</h3>
                <h1 id="subNameLarge" class="crt_user" style="font-family: 'Satisfy', cursive; font-size: 40px; margin-top: 0; margin-bottom: 0;">
                  { Subject Name }
                </h1>
                <p style="margin-bottom: 0;">for successfully participating in</p>
                <div style="display: flex; justify-content: center;">
                  <div style="text-align: center; margin-right: 20px;">
                    <h4>Issued By</h4>
                    <span id="issuerName">{ Issuer Name }</span>
                  </div>
                  <div style="text-align: center;">
                    <h4>Date</h4>
                    <span id="issuedDate">{ Issued Date }</span>
                  </div>
                </div>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Include the JavaScript -->
  <script src="{{ asset('js/cert-preview.js') }}"></script>
  
  
<div class="container">
	<div class="row">
		<div class="card">
			<div class="card-body">
				<div class="col-sm-6">
          <input class="form-control" type="file" id="csvInput" accept=".csv">
          <button id="parseBtn">Parse CSV</button>
          <h3>Parsed JSON Data</h3>
          <div class="parseData">
            <pre>Upload and parse a CSV to view JSON</pre>
          </div>
        </div>

			</div>
		</div>
	</div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>

<!-- Include the JavaScript -->
<script src="{{ asset('js/cert-preview.js') }}"></script>


<script>
$(document).ready(function () {
  const csvParser = {
    csvFile: null,
    jsonData: null,

    parseCSV() {
      if (this.csvFile) {
        Papa.parse(this.csvFile, {
          header: true,
          skipEmptyLines: true,
          complete: (results) => {
            this.jsonData = results.data
              .filter(row => Object.values(row).some(value => value.trim() !== ""))
              .map(row => {
                if (row.dateOfBirth) row.dateOfBirth = this.convertToISO(row.dateOfBirth);
                if (row.degreeEarnedDate) row.degreeEarnedDate = this.convertToISO(row.degreeEarnedDate);
                return row;
              });
            $('.parseData pre').text(JSON.stringify(this.jsonData, null, 2));
          },
          error: (err) => {
            console.error('Error parsing CSV:', err);
            alert('Error parsing the CSV file. Please try again.');
          }
        });
      } else {
        alert('Please upload a CSV file');
      }
    },

    convertToISO(dateString) {
      try {
        const date = new Date(dateString);
        return !isNaN(date) ? date.toISOString().split('T')[0] : dateString;
      } catch (error) {
        console.error('Invalid date format:', dateString);
        return dateString;
      }
    }
  };

  $('#csvInput').on('change', function (e) {
    const file = e.target.files[0];
    if (file) csvParser.csvFile = file;
  });

  $('#parseBtn').on('click', function () {
    csvParser.parseCSV();
  });


  // Save

  function saveCertificate() {
    // Prepare the data payload
    const certDetails = {
      schemaId: "sch:hid:testnet:z6MkpHD47Fcg7xKh6WUL8qSJQTVxri5nPXmDVXJqNVNRTnob:1.0",
      recipientDetails: jsonData, // Assumes `jsonData` is globally available or passed here
    };

    var myUrl = "{{route('certificate.sendMail')}}";
    var csrfToken = "{{ csrf_token() }}"; // Get the CSRF token
    // Make the AJAX call
    $.ajax({
      url: myUrl, // Replace with your API endpoint
      type: 'POST',
      contentType: 'application/json',
      headers: {
        'X-CSRF-TOKEN': csrfToken // Add CSRF token to the request headers
      },
      data: JSON.stringify(certDetails),
      success: function (response) {
        console.log(response);
        notifyUser('success', 'Successfully sent certificate');
      },
      error: function (xhr, status, error) {
        console.error('Error:', xhr.responseText || error);
        notifyUser('error', 'Failed to send certificate. Please try again.');
      },
    });
  }

  // Notification function
  function notifyUser(type, message) {
    // Example notification handling
    if (type === 'success') {
      alert(message); // Replace with your custom notification logic
    } else if (type === 'error') {
      alert(message); // Replace with your custom notification logic
    }
  }


  // Example `jsonData` (ensure this is defined)
  const jsonData = [
    {
      recipientFullName: "Raj Vijaykumar Patil",
      recipientEmail: "raj.v.patil108@gmail.com",
      degreeType: "Bachelor",
      degreeName: "Engineering",
      dateOfBirth: "1999-11-23T18:30:00.000Z",
      degreeEarnedDate: "1999-03-26T18:30:00.000Z",
      issuerName: "WIT Solapur",
      enrollmentNumber: "20181231231",
    },
  ];

  // Bind click event to the Save button
  $('#saveBtn').on('click', function () {
    saveCertificate(jsonData);
  });

  




});






</script>


</body>
</html>
