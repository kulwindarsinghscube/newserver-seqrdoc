<?php $__env->startSection('style'); ?>
<link rel="stylesheet" href="<?php echo e(asset('webapp/webcamjs/css/style.css')); ?>">

<style>

	<?php
		
		$domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $awsS3Instances = \Config::get('constant.awsS3Instances');
   	?>
body{
	padding:0;
background-image: linear-gradient(to bottom, rgba(34, 143, 176, 0.3), rgba(111, 128, 206, 0.4)), url("<?php echo e(asset('webapp/images/bg2.jpg')); ?>");
}
.phonebg{
	background:url("<?php echo e(asset('webapp/images/phonebg2.png')); ?>");
	padding:25px 25px;
	background-repeat:no-repeat;
	background-position:center center;
	height:766px;
	padding-top:105px;
}

.alert-default{
	background:linear-gradient(to bottom, rgba(255, 255, 255, 0.8), rgba(255,255,255, 0.8));
	border:0px;
}

.thumbnail{
	border:0;
	background:transparent;
}
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
	
<div class="" id="QR-Code">		
	<div class="col-xs-12">
		<div class="col-md-0 helper">
		</div>
		<div class="col-md-5 text-center phonebg col-md-offset-0">
			<div class="cam-selecter">
				<select class="form-control" id="camera-select"></select>
			</div>
			<div style="position:relative;height:554px;">
				<div class="controls">
					<button title="Upload Image" class="btn btn-secondary hide" id="decode-img" type="button" data-toggle="tooltip"><span class="fa fa-upload"></span></button>
					<button title="Capture Image" class="btn btn-secondary disabled hide" id="grab-img" type="button" data-toggle="tooltip"><span class="fa fa-camera"></span></button>
					
					<button title="Pause" class="btn btn-secondary" id="pause" type="button" data-toggle="tooltip"><span class="fa fa-pause"></span></button>

					<button title="Start" class="btn btn-secondary" id="play" type="button" data-toggle="tooltip"><span class="fa fa-play fa-2x"></span></button>
				
					<button title="Stop streams" class="btn btn-secondary" id="stop" type="button" data-toggle="tooltip"><span class="fa fa-stop"></span></button>
				</div>
				<div class="" style="position: relative;display: inline-block;margin-top:28px">
					<canvas width="320" height="288" id="webcodecam-canvas"></canvas>
					<div class="scanner-laser laser-rightBottom" style="opacity: 0.5;"></div>
					<div class="scanner-laser laser-rightTop" style="opacity: 0.5;"></div>
					<div class="scanner-laser laser-leftBottom" style="opacity: 0.5;"></div>
					<div class="scanner-laser laser-leftTop" style="opacity: 0.5;"></div>
				</div>
			
				<div class="" id="result">
					<div class="hidden">
						<img width="320" height="240" id="scanned-img" src="<?php echo e(asset('webapp/images/blank.png')); ?>">
					</div>
					<div class="caption ">
						<h3></h3>
						
						<div class="list-group text-left" style="width:320px;margin:0 auto;">
							<li class="list-group-item">
								<b>Status:</b>
								<span id="scanned-QR">Press <i class="fa fa-play fa-fw"></i> button to begin </span>
							</li>
							<li class="list-group-item hidden" id="qrcode">
							<b>QR Code:</b>
								<span id="response-QR">Press the <i class="fa fa-play fa-fw"></i> button to start </span>
							</li>
						</div>
						
						
						
					</div>
					 <div class="hidden" style="width: 100%;">
						<label id="zoom-value" width="100">Zoom: 2</label>
						<input id="zoom" onchange="Page.changeZoom();" type="range" min="10" max="30" value="20">
						<label id="brightness-value" width="100">Brightness: 0</label>
						<input id="brightness" onchange="Page.changeBrightness();" type="range" min="0" max="128" value="0">
						<label id="contrast-value" width="100">Contrast: 0</label>
						<input id="contrast" onchange="Page.changeContrast();" type="range" min="-128" max="128" value="0">
						<label id="threshold-value" width="100">Threshold: 0</label>
						<input id="threshold" onchange="Page.changeThreshold();" type="range" min="0" max="512" value="0">
						<label id="sharpness-value" width="100">Sharpness: off</label>
						<input id="sharpness" onchange="Page.changeSharpness();" type="checkbox">
						<label id="grayscale-value" width="100">grayscale: off</label>
						<input id="grayscale" onchange="Page.changeGrayscale();" type="checkbox">
						<br>
						<label id="flipVertical-value" width="100">Flip Vertical: off</label>
						<input id="flipVertical" onchange="Page.changeVertical();" type="checkbox">
						<label id="flipHorizontal-value" width="100">Flip Horizontal: off</label>
						<input id="flipHorizontal" onchange="Page.changeHorizontal();" type="checkbox">
					</div>
				</div>
			</div>
			<br>
		</div>
		<div class="col-md-6 col-md-offset-1" class="helper">
			<div class="helper">
			<br><br><br><br>
			<h1 style="color:#fff;font-family:roboto;">Start Scanning</h1>
			<h5 style="color:#fff">Follow these simple steps to scan your QR Code.</h5><br>
				<div class="alert alert-default">
				  <strong>Step 1: </strong><i class="fa fa-plug fa-fw theme"></i> Plugin Webcam. 
				</div>
				<div class="alert alert-default">
				  <strong>Step 2: </strong> Press <i class="fa fa-play fa-fw theme"></i> button to begin. 
				</div>
				<div class="alert alert-default">
				  <strong>Step 3: </strong> Allow the browser to access camera <i class="fa fa-video-camera fa-fw theme"></i>.
				</div>
				<div class="alert alert-default">
				  <strong>Step 4: </strong> Hold the <i class="fa fa-qrcode fa-fw theme"></i> QR Code infront of the webcam.
				</div>
				<div class="alert alert-default">
				  <strong>Step 5: </strong> Wait for the scanner to scan the <i class="fa fa-qrcode fa-fw theme"></i> QR Code and display the data.
				</div>
			</div>
		</div>
		<div class="col-md-6 col-md-offset-1 thumbnail hidden">
			<div class="cardpadding">
				
				<div class="ajaxResponse">
					Scan a QR code to begin
				</div>

				<?php if(in_array($subdomain[0], $awsS3Instances)){ ?>

					<div claass="row">
						
						<div id="pdf-main-container">
							<div id="pdf-loader"></div>
							<div id="pdf-contents">
								
								<canvas id="pdf-canvas" width="794px" height="1122px"></canvas>
								<div id="page-loader"></div>
							</div>
						</div>
					</div>

				<?php } ?>
			</div>
		</div>
	</div> 
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset('webapp/webcamjs/js/filereader.js')); ?>"></script>
<script src="<?php echo e(asset('webapp/webcamjs/js/qrcodelib.js')); ?>"></script>
<script src="<?php echo e(asset('webapp/webcamjs/js/webcodecamjs.js')); ?>"></script>
<script src="<?php echo e(asset('webapp/webcamjs/js/main.js')); ?>"></script>
<script>
var key = '';
var pay = '';

// console.log(56)
if(sessionStorage.getItem('qrCodeKey') != null && sessionStorage.getItem('qrCodeKey') != 'null'){
	$('#QR-Code').hide()
	var qrCode = sessionStorage.getItem('qrCodeKey');
	ajaxCall(qrCode,'1');
}

$(window).focusin(function(){
	if(key != '' && pay !=''){
		console.log('focusin');
		ajaxCall(key,'1');
	}else{
		return false;
	}
}); 

$(window).focusout(function(){
	console.log('focusout');
	pay = '1';
}); 

function load(){
	console.log('load');
	$('.payment-url').click(function(e){

		e.preventDefault();
		$url = $(this).attr('href');
		window.open($url,'_blank');
		pay = '1';
	});
}

$('#play,#stop').click(function(){
	
	$('#qrcode').fadeOut();
	$('.phonebg').addClass('col-md-offset-0');
	$('.thumbnail').addClass('hidden');
	$('.helper').removeClass('hidden');
	key ='';
	pay = '';
});

function ajaxCall(val,update='0'){
	if(sessionStorage.getItem('qrCodeKey') != null && sessionStorage.getItem('qrCodeKey') != 'null'){
		sessionStorage.removeItem('qrCodeKey')
	}

	key = val;
	$('#scanned-QR').text('success');
	$('#response-QR').text(key);
	$('#qrcode').removeClass('hidden');
	$('#qrcode').fadeIn();
	console.log('ajaxCall');
	var qrcode = key;
	var token = "<?php echo e(csrf_token()); ?>";
	$.ajax({
        url: "<?php echo e(URL::route('dashboard.store')); ?>",
        type: 'post',
        data:{
            qrcode:qrcode,
            _token:token,
            update_scan : update
        },
        success: function(data) {
			$('#QR-Code').show()
        	console.log(data);
        	$('.ajaxResponse').html(data);
			$('.phonebg').removeClass('col-md-offset-0');
			//$('.loader').removeClass('hidden');
			$('.thumbnail').removeClass('hidden');
			$('.thumbnail').removeClass('col-md-6');
			$('.thumbnail').removeClass('col-md-offset-1');
			$('.thumbnail').addClass('col-md-12');
			$('.phonebg').addClass('hidden');
			$('.helper').addClass('hidden');
			pay ='';
			load();
			<?php if(in_array($subdomain[0], $awsS3Instances)){ ?>
				showPDF();
			<?php } ?>
        }
    });
    return;
}

// $('#play').focusout(function(){
// 	if (('#scanned-QR')) {
// 		var qrcode = $('#scanned-QR').text();
// 		ajaxCall(qrcode);
// 	}
// 	// $('#scanned-QR').text('Success');
// 	// if ($('#scanned-QR').text('Success') == 'Success') {
// 	// }
// });

// ajaxCall('5363085FC2B5AE778FAB5FA77FCB7AA5','1');
// function ajaxCall(val,update='0'){
// 	key = val;
// 	$('#scanned-QR').text('Success');
// 	$('#response-QR').text(val);
// 	$('#qrcode').removeClass('hidden');
// 	$('#qrcode').fadeIn();
	
// 	var ajaxURL = 'functions/scanModel.php';
	
// 	$.post(ajaxURL,{
// 		'action':'scanData',
// 		'key':key,
// 		'update_scan':update
// 	},function(data){
// 		$('.ajaxResponse').html(data);
// 		$('.phonebg').removeClass('col-md-offset-0');
// 		//$('.loader').removeClass('hidden');
// 		$('.thumbnail').removeClass('hidden');
// 		$('.thumbnail').removeClass('col-md-6');
// 		$('.thumbnail').removeClass('col-md-offset-1');
// 		$('.thumbnail').addClass('col-md-12');
// 		$('.phonebg').addClass('hidden');
// 		$('.helper').addClass('hidden');
// 		pay ='';
// 		load();
// 	});
// 	return;
// 	//$('.loader').addClass('hidden');
// }
document.addEventListener('contextmenu', function(e) {
  e.preventDefault();
});
</script>




<?php if(in_array($subdomain[0], $awsS3Instances)){ ?>


	<!-- add pdf view js added -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.2.228/pdf.min.js"></script>

	<script>

	var pdfUrlStr = $('#pdfUrl').val();
	console.log(pdfUrlStr);

	// var pdfUrlStr = '<?php echo $data['pdfUrl']; ?>';
	//showPDF(pdfUrlStr);

	var _PDF_DOC,
	    _CURRENT_PAGE,
	    _TOTAL_PAGES,
	    _PAGE_RENDERING_IN_PROGRESS = 0,
	    _CANVAS = document.querySelector('#pdf-canvas');

	// initialize and load the PDF
	async function showPDF(pdf_url) {
		var pdf_url = $('#pdfUrl').val();
		//alert(pdf_url);
	    document.querySelector("#pdf-loader").style.display = 'block';

	    // get handle of pdf document
	    try {
	        _PDF_DOC = await pdfjsLib.getDocument({ url: pdf_url });
	    }
	    catch(error) {
	        alert(error.message);
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

	</script>

	<!-- add pdf view js added-->
<?php } ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('webapp.layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/webapp/dashboard/show.blade.php ENDPATH**/ ?>