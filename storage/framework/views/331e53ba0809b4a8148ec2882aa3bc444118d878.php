<?php $__env->startSection('style'); ?>
    <style>
    
        .panel { border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .left-panel, .right-panel { min-height: 600px; }
        .document-details p, .mint-details p { margin: 5px 0; }
        .btn-download { margin-top: 10px; }
        .pdf-container iframe { width: 100%; height: 600px; border: 1px solid #ddd; border-radius: 10px; }
        #pdf-contents {display: none;}
        #pdf-canvas {border: 1px solid rgba(0,0,0,0.2);box-sizing: border-box;max-width: 100%;}
        #page-loader {
            height: 100px;
            line-height: 100px;
            text-align: center;
            display: none;
            color: #999999;
            font-size: 13px;
        }
        .panel-body p {
            margin: 0;
          word-wrap: break-word; /* For older browsers */
          word-break: break-all; /* Ensures long strings break */
        }
        .mb-5 {
            margin-bottom: 5px;
        }
        /* Timeline Styles */
        .timeline {
          position: relative;
          margin: 20px auto;
          padding-left: 30px;
          border-left: 3px solid #ddd;
          width: 90%;
          max-width: 400px;
        }

        .progress-line {
          position: absolute;
          left: -1.5px;
          top: 0;
          width: 3px;
          height: 0;
          background-color: #4caf50;
          animation: fillProgress 2s forwards ease-in-out;
        }

        @keyframes  fillProgress {
          to { height: 100%; }
        }

        .step {
          position: relative;
          margin-bottom: 25px;
          opacity: 0;
          transform: translateY(15px);
          animation: fadeInUp 0.8s forwards ease-in-out;
        }

        /* Staggered Fade-In */
        .step:nth-child(1) { animation-delay: 0.4s; }
        .step:nth-child(2) { animation-delay: 0.8s; }
        .step:nth-child(3) { animation-delay: 1.2s; }
        .step:nth-child(4) { animation-delay: 1.6s; }
        .step:nth-child(5) { animation-delay: 2s; }

        @keyframes  fadeInUp {
          from { opacity: 0; transform: translateY(15px); }
          to { opacity: 1; transform: translateY(0); }
        }

        .step::before {
          content: '';
          position: absolute;
          left: -22px;
          top: 0;
          width: 16px;
          height: 16px;
          background-color: #ddd;
          border-radius: 50%;
          border: 2px solid #fff;
          box-shadow: 0 0 0 1px #ddd;
          transition: background-color 0.4s, box-shadow 0.4s;
        }

        .step.verified::before {
          background-color: #4caf50;
          content: '✔';
          font-size: 12px;
          color: #fff;
          text-align: center;
          line-height: 16px;
          box-shadow: 0 0 0 1px #4caf50;
          animation: popIn 0.4s ease;
        }

        @keyframes  popIn {
          0% { transform: scale(0); opacity: 0; }
          60% { transform: scale(1.2); opacity: 1; }
          100% { transform: scale(1); }
        }

        .content {
          padding: 6px 10px;
          background-color: #fff;
          border-radius: 6px;
          box-shadow: 0 1px 3px rgba(0,0,0,0.1);
          font-size: 12px;
        }

        .step .content h4 {
          margin: 0 0 4px;
          font-size: 14px;
          color: #333;
        }

        .step .content p {
          margin: 0;
          font-size: 11px;
          color: #666;
        }

        /* Modal Customization */
        .modal-content {
          border-radius: 8px;
          padding: 15px;
        }

        .modal-header {
          border-bottom: none;
        }

        .modal-title {
          font-size: 16px;
          font-weight: bold;
        }

        /* Verified Message */
        #verified-message {
          display: none;
          font-size: 14px;
          color: #4caf50;
          font-weight: bold;
          /*text-align: center;*/
          /*margin-top: 15px;*/
          animation: fadeIn 1s ease-in-out;
        }

        @keyframes  fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        /* #pdf-loader, #page-loader {
            text-align: center;
            font-weight: bold;
            padding: 10px;
        } */

        .pdf-page-canvas {
            width: 100% !important;
            height: auto !important;
            display: block;
            margin-bottom: 20px;
        }

        .modal-dialog {
            width: 100%;
        }

        @media (max-width: 414px) {
            .panel-title {
                font-size: 15px;
            }
            .viewPDFButton {
                margin-top: 6px;
                font-size: 13px;
            }
        }


        @media (max-width: 380px) {
            .panel-title {
                font-size: 14px;
            }
            .viewPDFButton {
                margin-top: 6px !important;
            }
        }


        @media (max-width: 360px) {
            .panel-title {
                font-size: 13px;
            }
            .viewPDFButton {
                font-size: 12px;
                margin-top: 7px !important;
            }
        }

        @media (max-width: 340px) {
            .panel-title {
                font-size: 12px;
            }
            .viewPDFButton {
                font-size: 11px;
                margin-top: 7.5px !important;
            }
        }



        


        
    </style>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php 
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        if($domain == 'verification.anu.edu.in') {
            $domain = 'anu.seqrdoc.com';
            $subdomain[0] = 'anu';
        }
    ?>

    <div class="container-fluid">
       <!--  <div class="row">
            <div class="col-md-12">
                <h2 class="text-center">Blockchain Document & Mint Details</h2>
            </div>
        </div> -->

        <div class="row">
            <!-- Left Column: Data Details -->
            <div class="col-md-5">
                <div class="panel panel-success  mint-details">
                    <div class="panel-heading">
                        <h3 class="panel-title"><img src="https://demo.seqrdoc.com/backend/images/green_tick.png" style="height: 30px;width: 30px;" /> Proof Verification</h3>
                    </div>
                    <div class="panel-body">
                        
                        <div class="row mb-5">
                            <div class="col-md-12 col-sm-6 col-xs-12 ">
                                <p id="name" style="display:none;"><i class="fa fa-angle-double-right"></i> <strong>Document Type:</strong></p>
                            </div>
                            <div class="col-md-12 col-sm-6 col-xs-12">
                                <p id="name_data" style="display:none;">&nbsp;&nbsp;&nbsp;<?php echo $data['name'];?></p>

                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-12 col-sm-6 col-xs-12 ">
                                 <p class="description" style="display:none;"><i class="fa fa-angle-double-right"></i><strong> Description:</strong></p>
                            </div>
                            <div class="col-md-12 col-sm-6 col-xs-12">
                                 <p class="description" style="display:none;">&nbsp;&nbsp;&nbsp;<?php echo $data['description'];?></p>

                            </div>
                        </div>


                       
                        
                        <?php if($data['data']) { ?>

                            <?php foreach ($data['data'] as $key => $trait) { ?>
                                

                                <div class="row mb-5">
                                    <div class="col-md-12 col-sm-6 col-xs-12 ">
                                         <p style="display:none;" class="metadata_<?php echo $key; ?>"><i class="fa fa-angle-double-right"></i> <strong><?php echo $trait['trait_type'] ?> : </strong></p>
                                    </div>
                                    <div class="col-md-12 col-sm-6 col-xs-12">
                                         <p style="display:none;" class="metadata_<?php echo $key; ?>">&nbsp;&nbsp;&nbsp;<?php echo $trait['value'] ?></p>

                                    </div>
                                </div>

                            <?php } ?>
                        <?php } ?>

                        
                        <!-- <p><strong>Faculty Name:</strong><br>MASTER OF SCIENCE</p>
                        <p><strong>Subject:</strong><br>Analytical Chemistry</p>
                        <p><strong>Grade:</strong><br>E</p>
                        <p><strong>Passing Month & Yea:</strong><br>May 2024</p> -->

                        <div class="row mb-5">
                            <div class="col-md-12 col-sm-6 col-xs-12 ">
                                 <p class="cardDownload" style="display: none"><strong>&nbsp;&nbsp;&nbsp;Download PDF:</strong></p>
                            </div>
                            <div class="col-md-12 col-sm-6 col-xs-12">
                                <p class="cardDownload" style="display: none">
                                    &nbsp;&nbsp;&nbsp;<a href="<?php echo $data['pdfUrl'];?>" target="_blank">Click here to download.
                                    </a>
                                </p>

                            </div>
                        </div>



                        <div class="row mb-5" style="margin-top: 15px;">
                            <div class="col-md-12 col-sm-6 col-xs-12 ">
                                 <p class="cardDownload" style="display: none"><strong>&nbsp;&nbsp;&nbsp;Verify Document:</strong></p>
                            </div>
                            <div class="col-md-12 col-sm-6 col-xs-12">
                                <p class="cardDownload" style="display: none">
                                    &nbsp;&nbsp;&nbsp;
                                    
                                    <a id="verified-button" style="text-decoration: none;cursor: pointer;" data-toggle="modal" data-target="#timelineModal" >Click here to Verify.
                                    </a>
                                    
                                    <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#timelineModal">
                                      Show Timeline
                                    </button> -->

                                </p>
                                <p id="verified-message">✔ Process Verified Successfully!</p>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-primary mint-details" style="display: none">
                    <div class="panel-heading">
                        <h3 class="panel-title" id="showMint">Issuer's Information</h3>
                    </div>
                    <div class="panel-body mint-details">
                        <?php if( !empty($data['walletID']) ) { ?>
                          <p ><strong>Wallet Address:</strong><br><?php echo $data['walletID'];?></p>
                        <?php } ?>
                        <p><strong>Polygon Transaction URL:</strong><br>
                            <a href="<?php echo $data['polygonTxnUrl'];?>" target="_blank">
                                View Transaction
                            </a>
                        </p>
                        <p><strong>Smart Contract Address:</strong><br><?php echo $data['contractAddress'];?></p>
                        <p><strong>Transaction Hash:</strong><br><?php echo $data['txnHash'];?></p>
                    </div>
                </div>
            </div>

            <!-- Right Column: PDF Viewer -->
            <div class="col-md-7">
                <div class="panel panel-info right-panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><img src="https://demo.seqrdoc.com/backend/images/green_tick_1.gif" style="border-radius: 30px;height: 30px;width: 30px;" /> Verified Document 
                        <?php if($subdomain[0] == 'anu') { ?>    
                            <a href="javascript:void(0);" style="float: right;margin-top: 5px;" class="viewPDFButton" onclick="renderSecurePDF()">Click to view full PDF</a>
                            <!--<a href="#" style="float: right;margin-top: 5px;" data-toggle="modal" data-target="#pdfModal">Click for full view PDF</a>-->

                            <!-- <button onclick="renderSecurePDF()">View PDF</button> -->
                            <?php }?>
                        </h3>
                    </div>
                    <div class="panel-body pdf-container">

                        <div id="pdf-main-container">
                            <div id="pdf-loader">Loading document ...</div>
                            <div id="pdf-contents">
                                
                                
                                <div id="page-loader">Loading page ...</div>
                            </div>
                        </div>

                        <!-- <iframe src="https://unikbp.seqrdoc.com/unikbp/backend/pdf_file/202202150012.pdf#toolbar=0&navpanes=0&scrollbar=0" title="PDF Preview"></iframe> -->
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Verified Message -->

<!-- Modal -->
<!-- PDF Modal -->
<div id="pdfModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" style="max-width: 90vw;">
    <div class="modal-content" style="background-color: #f4f4f4;">
      <div class="modal-header">
        
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <div id="pdf-viewer" style="width: 100%;"></div>
      </div>
    </div>
  </div>
</div>





<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1" role="dialog" aria-labelledby="timelineModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="timelineModalLabel">Verify Process</h4>
      </div>

      <div class="modal-body">
        <div class="timeline">
          <div class="progress-line"></div>

          <div class="step verified">
            <div class="content">
              <h4>Step 1: Initialized</h4>
              <p>Process started successfully.</p>
            </div>
          </div>

          <div class="step verified">
            <div class="content">
              <h4>Step 2: Processing</h4>
              <p>Data processed.</p>
            </div>
          </div>

          <div class="step verified">
            <div class="content">
              <h4>Step 3: Verified</h4>
              <p>Verification completed.</p>
            </div>
          </div>

        </div>
        
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>


<script>
    

    $("#name").delay(1000).fadeIn(100);
    $("#name_data").delay(1000).fadeIn(100);

    $(".description").delay(1500).fadeIn(100);
    $(".metadata_0").delay(2000).fadeIn(100);
    $(".metadata_1").delay(2500).fadeIn(100);
    $(".metadata_2").delay(3000).fadeIn(100);
    $(".metadata_3").delay(3500).fadeIn(100);
    $(".metadata_4").delay(4000).fadeIn(100);
    $(".metadata_5").delay(4500).fadeIn(100);
    $(".metadata_6").delay(5000).fadeIn(100);
    // $(".metadata_7").delay(5200).fadeIn(100);
    // $(".metadata_8").delay(5400).fadeIn(100);
    // $(".metadata_9").delay(5600).fadeIn(100);
    // $(".metadata_10").delay(5800).fadeIn(100);
    // $(".metadata_11").delay(6000).fadeIn(100);
    // $(".metadata_12").delay(6200).fadeIn(100);
    // $(".metadata_13").delay(6400).fadeIn(100);
    // $(".metadata_14").delay(6600).fadeIn(100);
    // $(".metadata_15").delay(6800).fadeIn(100);
    // $(".metadata_16").delay(7000).fadeIn(100);
    // $(".metadata_17").delay(7200).fadeIn(100);
    // $(".metadata_18").delay(7400).fadeIn(100);
    //$(".cardDownload").delay(4000).fadeIn(100);

    $(".mint-details").delay(4500).fadeIn(100);

    // $("#name").fadeIn(1000);
    // $("#name").delay(1000).fadeIn(1000);
    // $("#name_data").delay(1000).fadeIn(1000);

    // $(".description").delay(1500).fadeIn(1500);
    // $(".metadata_0").delay(2000).fadeIn(2000);
    // $(".metadata_1").delay(2500).fadeIn(2500);
    // $(".metadata_2").delay(3000).fadeIn(3000);
    // $(".metadata_3").delay(3500).fadeIn(3500);
    // $(".metadata_4").delay(4000).fadeIn(4000);
    // $(".cardDownload").delay(4000).fadeIn(4000);
    // //$("#pdfDiv").slideUp(1500).slideDown(2000);

    // $(".mint-details").delay(4500).fadeIn(4500);
    
    
    // $("#cardDownload").fadeIn(4500);

    $(document).contextmenu(function () {
        return false;
    });
    
    



</script>


<script>
    var fileUrl = '<?php echo $data['pdfUrl']; ?>'; 
    var pdfUrlStr = '<?php echo $data['pdfUrl']; ?>';
    console.log(pdfUrlStr);
    showPDF(pdfUrlStr);

    var _PDF_DOC,
        _CURRENT_PAGE,
        _TOTAL_PAGES,
        _PAGE_RENDERING_IN_PROGRESS = 0,
        _CANVAS = document.querySelector('#pdf-canvas');

    // initialize and load the PDF
    async function showPDF_old(pdf_url) {
        document.querySelector("#pdf-loader").style.display = 'block';

        // get handle of pdf document
        try {
            _PDF_DOC = await pdfjsLib.getDocument({ url: pdf_url });
        }
        catch(error) {
            console.log(error.message);
            var pdfUrlStr = '<?php echo $data['dirPdfUrl']; ?>';
            showPDF(pdfUrlStr);
            // alert(error.message);
        }

        // total pages in pdf
        _TOTAL_PAGES = _PDF_DOC.numPages;
        
        // Hide the pdf loader and show pdf container
        document.querySelector("#pdf-loader").style.display = 'none';
        document.querySelector("#pdf-contents").style.display = 'block';
        // document.querySelector("#pdf-total-pages").innerHTML = _TOTAL_PAGES;

        // show the first page
        showPage(1);
    }
    
    // load and render specific page of the PDF
    async function showPage_old(page_no) {
        _PAGE_RENDERING_IN_PROGRESS = 1;
        _CURRENT_PAGE = page_no;

        // disable Previous & Next buttons while page is being loaded
        // document.querySelector("#pdf-next").disabled = true;
        // document.querySelector("#pdf-prev").disabled = true;

        // while page is being rendered hide the canvas and show a loading message
        document.querySelector("#pdf-canvas").style.display = 'none';
        document.querySelector("#page-loader").style.display = 'block';

        // update current page
        // document.querySelector("#pdf-current-page").innerHTML = page_no;
        
        // get handle of page
        try {
            var page = await _PDF_DOC.getPage(page_no);
        }
        catch(error) {
            alert(error.message);
        }

        // original width of the pdf page at scale 1
        var pdf_original_width = page.getViewport(1).width;
        
        // as the canvas is of a fixed width we need to adjust the scale of the viewport where page is rendered
        var scale_required = _CANVAS.width / pdf_original_width;

        // get viewport to render the page at required scale
        var viewport = page.getViewport(scale_required);

        // set canvas height same as viewport height
        _CANVAS.height = viewport.height;

        // setting page loader height for smooth experience
        document.querySelector("#page-loader").style.height =  _CANVAS.height + 'px';
        document.querySelector("#page-loader").style.lineHeight = _CANVAS.height + 'px';

        // page is rendered on <canvas> element
        var render_context = {
            canvasContext: _CANVAS.getContext('2d'),
            viewport: viewport
        };
            
        // render the page contents in the canvas
        try {
            await page.render(render_context);
        }
        catch(error) {
            alert(error.message);
        }

        _PAGE_RENDERING_IN_PROGRESS = 0;

        // re-enable Previous & Next buttons
        // document.querySelector("#pdf-next").disabled = true;
        // document.querySelector("#pdf-prev").disabled = true;

        // show the canvas and hide the page loader
        document.querySelector("#pdf-canvas").style.display = 'block';
        document.querySelector("#page-loader").style.display = 'none';
        // document.querySelector("#pdf-buttons").style.display = 'none';
        
    }

    


    async function showPDF(pdf_url) {
        document.querySelector("#pdf-loader").style.display = 'block';
        document.querySelector("#pdf-contents").style.display = 'none';


        try {
            _PDF_DOC = await pdfjsLib.getDocument({ url: pdf_url });
        }
        catch(error) {
            console.log(error.message);
            var pdfUrlStr = '<?php echo $data['dirPdfUrl']; ?>';
            showPDF(pdfUrlStr);
            // alert(error.message);
        }

        // try {
        //     _PDF_DOC = await pdfjsLib.getDocument({ url: pdf_url });
        // } catch(error) {
        //     console.log(error.message);
        //     return;
        // }

        _TOTAL_PAGES = _PDF_DOC.numPages;

        document.querySelector("#pdf-loader").style.display = 'none';
        document.querySelector("#pdf-contents").style.display = 'block';

        // Clear previous contents if any
        document.querySelector("#pdf-contents").innerHTML = '<div id="page-loader">Loading page...</div>';

        // Loop through and show all pages
        for (let i = 1; i <= _TOTAL_PAGES; i++) {
            await showPage(i);
        }

        // Hide final loader after rendering all pages
        document.querySelector("#page-loader").style.display = 'none';
    }

    async function showPage(page_no) {
        let page;
        try {
            page = await _PDF_DOC.getPage(page_no);
        } catch(error) {
            console.log(error.message);
            return;
        }

        const container = document.querySelector("#pdf-contents");
        const containerWidth = container.clientWidth;

        // Get device pixel ratio for high DPI displays
        const dpiRatio = window.devicePixelRatio || 1;

        // Calculate scale based on container width * DPI ratio
        const viewport = page.getViewport({ scale: 1 });
        const scale = (containerWidth * dpiRatio) / viewport.width;
        const scaledViewport = page.getViewport({ scale: scale });

        // Create canvas
        const canvas = document.createElement("canvas");
        canvas.className = 'pdf-page-canvas';
        canvas.style.border = "1px solid #333"; // Add border here
        // Set canvas actual pixel dimensions (scaled)
        canvas.width = scaledViewport.width;
        canvas.height = scaledViewport.height;

        const context = canvas.getContext('2d');

        // Scale the context for high DPI
        context.setTransform(1, 0, 0, 1, 0, 0); // reset any existing transform

        // Append canvas before loader
        const loaderDiv = document.querySelector("#page-loader");
        loaderDiv.parentNode.insertBefore(canvas, loaderDiv);

        // Render page
        try {
            await page.render({
                canvasContext: context,
                viewport: scaledViewport
            }).promise;
        } catch(error) {
            console.log(error.message);
        }

        // Finally, set canvas CSS width to containerWidth for display
        canvas.style.width = containerWidth + "px";
        canvas.style.height = (scaledViewport.height / dpiRatio) + "px";
    }



    function checkFileExists(url, callback) {
        if (!url) {
            console.log('URL is null or empty');
            callback(false);
            return;
        }

        //console.log('URL:', url);
        $.ajax({
            url: url,
            type: 'GET',
            cache: false, // Prevent caching
            success: function() {
                console.log('true');
                callback(true); // File exists
            },
            error: function() {
                console.log('false');
                callback(false); // File does not exist
            }
        });
    }

</script>

<script>
  $('#timelineModal').on('shown.bs.modal', function () {
    $('.progress-line').css('height', '0').animate({ height: '100%' }, 2000);
    $('.step').css({ opacity: 0, transform: 'translateY(15px)' }).each(function (index) {
      $(this).delay(400 * index).animate({ opacity: 1, top: 0 }, 500);
    });

    $('.cardDownload').hide();

    // Show Verified Message after last step appears
    setTimeout(function () {
      $('#verified-message').fadeIn();
    }, 3000);
  });

  // Disable right click on the canvas
    document.addEventListener("contextmenu", function(e){
        e.preventDefault();
    });


    document.addEventListener('keyup', function (e) {
        if (e.key === "PrintScreen") {
            navigator.clipboard.writeText('');
        }
    });


    document.addEventListener('keydown', function (e) {
        // Disable F12
        if (e.keyCode === 123) {
            e.preventDefault();
            return false;
        }
        // Disable Ctrl+Shift+I, Ctrl+Shift+J
        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J')) {
            e.preventDefault();
            return false;
        }
        // Disable Ctrl+U (view source)
        if (e.ctrlKey && e.key === 'u') {
            e.preventDefault();
            return false;
        }
    });


    // Disable right-click inside modal
    $('#pdfModal').on('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Optional: Block common shortcuts
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey && ['s', 'p', 'u'].includes(e.key.toLowerCase())) || e.keyCode === 123) {
            e.preventDefault();
            return false;
        }
    });
</script>

<script>
    const gif = document.getElementById('myGif');
    const gifSrc = gif.src;

    gif.addEventListener('ended', function() {
        // Create a canvas element
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        // Create a new image element to load the GIF
        const tempImage = new Image();
        tempImage.src = gifSrc;

        tempImage.onload = function() {
            // Set canvas dimensions to the GIF's dimensions
            canvas.width = tempImage.width;
            canvas.height = tempImage.height;

            // Draw the first frame of the GIF onto the canvas
            context.drawImage(tempImage, 0, 0);

            // Convert the canvas content to a data URL (PNG format)
            const staticImage = canvas.toDataURL('image/png');

            // Replace the GIF's src with the static image data URL
            gif.src = staticImage;
        };

    });

</script>


<script>
    const pdfUrl = "<?php echo $data['pdfUrl']; ?>"; // your PDF file path
    function addWatermark(ctx, text) {
        ctx.font = '20px Arial';
        ctx.fillStyle = 'rgba(200, 0, 0, 0.3)';
        ctx.rotate(-0.4);
        ctx.fillText(text, 100, 100);
        ctx.rotate(0.4);
    }

    function renderSecurePDF() {
      
        $('#pdf-viewer').html('<p>Loading...</p>');

        pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
            $('#pdf-viewer').html('');
            for (let i = 1; i <= pdf.numPages; i++) {
                pdf.getPage(i).then(page => {
                    const scale = window.innerWidth < 768 ? 0.8 : 1.5;
                    const viewport = page.getViewport({ scale });
                    

                    // const canvas = document.createElement('canvas');
                    // canvas.style.display = 'block';
                    // canvas.style.marginBottom = '15px';
                    // canvas.width = viewport.width;
                    // canvas.height = viewport.height;

                    const canvas = document.createElement('canvas');
                    canvas.style.display = 'block';
                    canvas.style.marginBottom = '15px';
                    canvas.style.width = '100%';     // responsive width
                    canvas.style.height = 'auto';    // maintain aspect ratio
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const ctx = canvas.getContext('2d');
                    page.render({ canvasContext: ctx, viewport });
                    // page.render({ canvasContext: ctx, viewport }).promise.then(() => {
                    //     addWatermark(ctx, "User: rohit@example.com");
                    // });
                    document.getElementById('pdf-viewer').appendChild(canvas);
                });
            }
        });

        $('#pdfModal').modal('show');
  }

  // Block right-click and print shortcuts
  $(document).on('contextmenu', e => e.preventDefault());
    $(document).on('keydown', function(e) {
        if ((e.ctrlKey && ['s', 'p', 'u'].includes(e.key.toLowerCase())) || e.keyCode === 123) {
            e.preventDefault();
            return false;
        }
  });
  // document.addEventListener('keyup', function(e) {
  //     if (e.key === 'PrintScreen') {
  //         alert("Screenshotting is not allowed.");
  //     }
  // });

  document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
          document.getElementById('pdf-viewer').style.display = 'none';
      } else {
          document.getElementById('pdf-viewer').style.display = 'block';
      }
  });

  
</script>


<script>
  const pdfViewer = document.getElementById('pdf-viewer');

  // Blur before print
  window.addEventListener('beforeprint', function () {
      alert("test.");
      if (pdfViewer) {
          pdfViewer.style.filter = 'blur(10px)';
          alert("Printing is not allowed.");
      }
  });

  $(document).on('keyup', function(e) {
      if (e.key === 'PrintScreen') {
          $('#pdf-viewer').css('filter', 'blur(8px)');
          alert("Screenshotting is not allowed.");

          // Optional: remove blur after a short time
          setTimeout(function () {
              $('#pdf-viewer').css('filter', 'none');
          }, 3000); // 3 seconds
      }
  });
</script>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('bverify_new.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/bverify_new/index.blade.php ENDPATH**/ ?>