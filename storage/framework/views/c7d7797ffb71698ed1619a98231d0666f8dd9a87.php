<?php $__env->startSection('style'); ?>
    <style>
        body { 
            /* padding-top: 20px; */
            font-family: "Montserrat", serif;
            /*background-image: url(https://wallpapers.com/images/hd/blockchain-dark-blue-points-vmzovkeeoz7u0fr4.jpg);*/
            background-position: center;
            background-size: cover;
        }

        .navbar {
            background: #0052CC;
            border: 0px;
            border-radius: 0px;
            box-shadow: 0px 1px 3px #555;
        }

        .navbar-brand {
            margin-top: 5px;
            font-family: abel;
            font-weight: 500;
            color: #fff !important;
        }
        
        /* Center loading box */
        #loading-box {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-content {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 30px 50px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .loading-content .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes  spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #data-container {
            display: none;
            margin-top: 50px;
        }

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
        <!-- Left Column: Data Details -->
        <div class="col-md-5">
            <div class="panel panel-success  mint-details">
                <div class="panel-heading">
                    <h3 class="panel-title"><img src="https://demo.seqrdoc.com/backend/images/green_tick.png" style="height: 30px;width: 30px;" /> Proof Verification</h3>
                </div>
                <div class="panel-body">
                    
                    

                    <div class="row mb-5">
                        <div class="col-md-12 col-sm-6 col-xs-12">
                            <p id="name"><i class="fa fa-angle-double-right"></i> <strong>Document Type:</strong></p>
                        </div>
                        <div class="col-md-12 col-sm-6 col-xs-12">
                            <p id="name_data"></p>
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-12 col-sm-6 col-xs-12">
                            <p class="description"><i class="fa fa-angle-double-right"></i><strong> Description:</strong></p>
                        </div>
                        <div class="col-md-12 col-sm-6 col-xs-12">
                            <p id="description"></p>
                        </div>
                    </div>

                    <div class="metadata-details">

                    </div>
                    
                    
                    <!-- Data Of Metdata -->

                    

                    
                </div>
            </div>
            

            <div class="panel panel-primary mint-details">
                <div class="panel-heading">
                    <h3 class="panel-title" id="showMint">Issuer's Information</h3>
                </div>
                <div class="panel-body mint-details">
                    <p><strong>Wallet Address:</strong><br><span class="walletID"></span></p>
                    <p><strong>Polygon Transaction URL:</strong><br><span class="polyTransactionUrl"></span></p>
                    <p><strong>Smart Contract Address:</strong><br><span class="smartContractAddress"></span></p>
                    <p><strong>Transaction Hash:</strong><br><span class="txn_hash"></span></p>
                </div>
            </div>

        </div>

        <!-- Right Column: PDF Viewer -->
        <div class="col-md-7">
            <div class="panel panel-info right-panel">
                <div class="panel-heading">
                    <h3 class="panel-title"><img src="https://demo.seqrdoc.com/backend/images/green_tick_1.gif" style="border-radius: 30px;height: 30px;width: 30px;" /> Verified Document</h3>
                </div>
                <div class="panel-body pdf-container">

                    <div id="pdf-main-container">
                        <div id="pdf-loader">Loading document ...</div>
                        <div id="pdf-contents">
                            
                            <canvas id="pdf-canvas" width="794px" height="1122px"></canvas>
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
$(document).ready(function() {

    // // Simulate AJAX data fetching
    // $.ajax({
    //     url: "fetch_data.php", // your PHP file or API URL
    //     method: "GET",
    //     dataType: "html",
    //     success: function(response) {
    //         $("#data-container").html(response).fadeIn(400);
    //     },
    //     error: function() {
    //         $("#data-container").html('<div class="alert alert-danger">Failed to load data.</div>').fadeIn(400);
    //     },
    //     complete: function() {
    //         // Hide loading box when done
    //         $("#loading-box").fadeOut(500);
    //     }
    // });

    $("#name_data, #description").show().html('<i class="fa fa-spinner fa-spin"></i> Loading...');

    $(".walletID, .polyTransactionUrl, .smartContractAddress, .txn_hash").html('<i class="fa fa-spinner fa-spin"></i> Loading...');

    $(".metadata-details").html('<i class="fa fa-spinner fa-spin"></i> Metadata Loading...');
     

    $.ajax({
        url: "/bverify-new/show_details/<?php echo e($token); ?>",
        type: "GET",
        dataType: "json",
        success: function(response) {
            console.log(response);
            // Hide loading message
            
            // console.log(response);

            // alert(response.status);
            
            if(response.status == "200") {
                let data = response;

                // alert(data.name);
                $("#loading-box").fadeOut(400);

           
                $("#name").delay(1000).fadeIn(100);
                $("#name_data").delay(1000).fadeIn(100);

                $(".description").delay(1500).fadeIn(100);
                

                $(".mint-details").delay(4500).fadeIn(100);

                
                // Show all hidden data blocks
                // $("#name, #name_data, #description,.description, .cardDownload").show();
                // $("#name,.description, .cardDownload").show();
                $(".mint-details").show();

                // Fill document name and description
                $("#name_data").html("&nbsp;&nbsp;&nbsp;" + data.name);
                $("#description").html("&nbsp;&nbsp;&nbsp;" + data.description);

                $(".metadata-details").empty();
                // Generate metadata fields dynamically
                if (data.data && data.data.length > 0) {
                    let metaHtml = "";
                    data.data.forEach(function (trait, index) {
                        metaHtml += `
                            <div class="row mb-5">
                                <div class="col-md-12 col-sm-6 col-xs-12">
                                    <p class="metadata_${index}">
                                        <i class="fa fa-angle-double-right"></i> 
                                        <strong>${trait.trait_type} :</strong>
                                    </p>
                                </div>
                                <div class="col-md-12 col-sm-6 col-xs-12">
                                    <p class="metadata_${index}">
                                        &nbsp;&nbsp;&nbsp;${trait.value}
                                    </p>
                                </div>
                            </div>
                        `;
                    });
                    // Append metadata inside panel-body
                    $(".metadata-details").append(metaHtml);
                }

                $(".metadata_0").delay(2000).fadeIn(100);
                $(".metadata_1").delay(2500).fadeIn(100);
                $(".metadata_2").delay(3000).fadeIn(100);
                $(".metadata_3").delay(3500).fadeIn(100);
                $(".metadata_4").delay(4000).fadeIn(100);

                
                
                // Update PDF Download Link
                $(".cardDownload a").attr("href", data.pdfUrl);

                // Update Mint Details panel

                // Replace data in mint details panel
                $(".walletID").html(data.walletID || "Not available");
                $(".polyTransactionUrl").html(
                    data.polygonTxnUrl
                    ? `<a href="${data.polygonTxnUrl}" target="_blank">View Transaction</a>`
                    : "Not available"
                );
                $(".smartContractAddress").html(data.contractAddress || "Not available");
                $(".txn_hash").html(data.txnHash || "Not available");
                
                // $(".panel-primary .panel-body").html(`
                //     <p><strong>Wallet Address:</strong><br>${data.walletID}</p>
                //     <p><strong>Polygon Transaction URL:</strong><br>
                //         <a href="${data.polygonTxnUrl}" target="_blank">View Transaction</a>
                //     </p>
                //     <p><strong>Smart Contract Address:</strong><br>${data.contractAddress}</p>
                //     <p><strong>Transaction Hash:</strong><br>${data.txnHash}</p>
                // `);

                // Show panels smoothly
                $(".mint-details").fadeIn(500);
                
                
                // // Show traits dynamically
                // if(data.name){
                //     $("#name").show();
                //     $("#name_data").show().text(data.name);
                // }

                // if(data.description){
                //     $(".description").show().last().text(data.description);
                // }

                // if(data.data && data.data.length){
                //     data.data.forEach(function(trait, i){
                //         $(".metadata_" + i).show().last().text(trait.value);
                //     });
                // }

                // PDF load
                showPDF(data.pdfUrl,data.dirPdfUrl);
            } else {
                // alert("Failed to load details.");
                
                $(".container-fluid").empty();
                
                let metaHtml1 = "";

                metaHtml1 += `
                    <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                            <div style="color: red;
                                font-size: 23px;
                                max-width: 600px;
                                margin: auto;
                                text-align: center;
                                background-color: #fff;
                                padding: 10px;
                                border: 1px solid #dbdbdb;
                                border-radius: 5px;">
                                <img src="../backend/images/error.png" style="max-width: 100px;" />
                                <br>
                                <span>${response.message}</span>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    </div>
                    `;

                $(".container-fluid").append(metaHtml1);
                $("#loading-box").html(
                    "<div class='alert alert-danger text-center'>Error loading data. Please try again.</div>"
                );
            }
        },
        error: function() {
            alert("Server error while loading details.");
        },
        complete: function() {
            // Hide loading box when done
            $("#loading-box").fadeOut(500);
        }
    });



    var _PDF_DOC,
        _CURRENT_PAGE,
        _TOTAL_PAGES,
        _PAGE_RENDERING_IN_PROGRESS = 0,
        _CANVAS = document.querySelector('#pdf-canvas');

    // initialize and load the PDF
    async function showPDF(pdf_url,dirPdfUrl) {
        console.log(pdf_url);
        document.querySelector("#pdf-loader").style.display = 'block';

        // get handle of pdf document
        try {
            _PDF_DOC = await pdfjsLib.getDocument({ url: pdf_url });
        }
        catch(error) {
            console.log(error.message);
            var pdfUrlStr = dirPdfUrl;
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


});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('bverify_new.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/bverify_new/index_v1.blade.php ENDPATH**/ ?>