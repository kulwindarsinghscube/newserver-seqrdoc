<?php $__env->startSection('style'); ?>
<?php
        
    $domain = \Request::getHost();

    if($domain=='certificate.kmtc.ac.ke'){ 
        $domain = 'kmtc.seqrdoc.com';
    }

    $subdomain = explode('.', $domain);
?>
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

    </style>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <div class="container-fluid">
       <!--  <div class="row">
            <div class="col-md-12">
                <h2 class="text-center">Blockchain Document & Mint Details</h2>
            </div>
        </div> -->

        <div class="row">
            
        		<div class="col-md-3"></div>
            <!-- Right Column: PDF Viewer -->
            <div class="col-md-6">
                <div class="panel panel-info right-panel">
                    <div class="panel-heading">
                        <?php if($subdomain[0]=='kmtc'){?>
                        <h3>“This certificate is issued on the basis of information available with the institution at the certificate date mentioned. The institution reserves the right to withdraw the certificate if new evidence materially alters graduation status”.</h3>
                        <?php }else{ ?>
                        <h3 class="panel-title"><img src="https://demo.seqrdoc.com/backend/images/green_tick_1.gif" style="border-radius: 30px;height: 30px;width: 30px;" /> Verified Document</h3>
                    <?php } ?>
                    </div>
                    <div class="panel-body pdf-container">
                        <!-- Watermark -->
                        <div style="
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%) rotate(-30deg);
                            font-size: 60px;
                            color: rgba(0,0,0,0.3);
                            font-weight: bold;
                            z-index: 10;
                            pointer-events: none;
                        ">
                            Provisional
                        </div>
                        <div id="pdf-main-container">
                            <div id="pdf-loader">Loading document ...</div>
                            <div id="pdf-contents">
                                
                                <canvas id="pdf-canvas" width="1200px" height="1122px"></canvas>
                                <div id="page-loader">Loading page ...</div>
                            </div>
                        </div>

                        <!-- <iframe src="https://unikbp.seqrdoc.com/unikbp/backend/pdf_file/202202150012.pdf#toolbar=0&navpanes=0&scrollbar=0" title="PDF Preview"></iframe> -->
                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
    </div>

<!-- Verified Message -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.2.228/pdf.min.js"></script>
<script>
    document.addEventListener('keyup', function(e) {
    if (e.key === "PrintScreen") {
        // alert("Print screen is disabled on this page.");
        document.body.style.display = 'none';
    }
    });
    $(document).ready(function() {
    // Disable right-click
    $(document).on("contextmenu", function(e) {
        e.preventDefault();
    });

    // Disable certain key combinations
    $(document).on("keydown", function(e) {
        // Block F12, Ctrl+U, Ctrl+Shift+I, PrintScreen
        if (
            e.key === "F12" ||
            (e.ctrlKey && e.key.toLowerCase() === "u") || // Ctrl+U
            (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === "i") || // Ctrl+Shift+I
            e.key === "PrintScreen"
        ) {
            e.preventDefault();
        }
    });
});

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

    // $(document).contextmenu(function () {
    //     return false;
    // });
    

    </script>


    <script>
        var fileUrl = '<?php echo $pdfUrl; ?>'; 
        // console.log(fileUrl);
        var pdfUrlStr = '<?php echo $pdfUrl; ?>';
        showPDF(pdfUrlStr);

        var _PDF_DOC,
            _CURRENT_PAGE,
            _TOTAL_PAGES,
            _PAGE_RENDERING_IN_PROGRESS = 0,
            _CANVAS = document.querySelector('#pdf-canvas');

        // initialize and load the PDF
        async function showPDF(pdf_url) {
            console.log(pdf_url);
            document.querySelector("#pdf-loader").style.display = 'block';

            // get handle of pdf document
            try {
                _PDF_DOC = await pdfjsLib.getDocument({ url: pdf_url });
            }
            catch(error) {
                console.log(error.message);
                var pdfUrlStr = '<?php echo $pdfUrl; ?>';
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
        async function showPage(page_no) {
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


        function checkFileExists(url, callback) {
            if (!url) {
                console.log('URL is null or empty');
                callback(false);
                return;
            }

            console.log('URL:', url);
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('InstituteStudentWallet.layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/InstituteStudentWallet/documents/show.blade.php ENDPATH**/ ?>