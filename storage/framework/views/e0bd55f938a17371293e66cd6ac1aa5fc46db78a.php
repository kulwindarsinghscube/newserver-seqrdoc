<?php $__env->startSection('content'); ?>

<style type="text/css">
	
	.monad-alert{
		text-align: center;
    padding: 15px;
    font-size: 19px;
    color: #fff;
    background-color: brown;
    font-weight: 600;
    max-width: 1090px;
    border-radius: 10px;
    margin: auto;
    margin-bottom: 10px;
}

    @media  only screen and  (max-width: 600px) {
    	.monad-alert{
    		font-size: 13px;
    	}
    	}
</style>
<br>
  <div id="pdfDiv" style="background-color: #fff;padding: 15px;text-align: center; display: none;">
<!--  <embed src="http://localhost:8080/raisoni/pdf_file/623021.pdf" width="600" height="500" alt="pdf" pluginspage="http://www.adobe.com/products/acrobat/readstep2.html"> -->
    </div>
<div class="" id="QR-Code">
	<div class="col-xs-12">
		<div class="monad-alert">
	Marksheet & Degree Certificates of only those students who have passed since 2020 can be verified using this app.
	</div>
	</div>

	<div class="col-xs-12">
		<div class="col-md-0 helper">
			 <div class="form-group">
				<div class="row">
					<div class="col-md-5 text-center col-md-offset-0">
						<div style="    margin: auto;
    max-width: 350px;
    background-color: #337ab7;
    padding: 10px;
    border: 1px solid #fff;
    border-radius: 5px;">
						<label style="color: #ffff;font-size: 18px;font-size: 18px;
    min-height: 40px;
">Select Verification Type <sup>*</sup> </label>
						<select class="form-control" name="verification_type" id="verification_type" data-rule-required="true" style="max-width: 300px;margin: auto;">
							<option value="" disabled selected>Select Verification Type</option>
							<option value="degree">Degree</option>
							<option value="marksheet">Marksheet</option>
						</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-5 text-center phonebg col-md-offset-0 scanner-div">
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
						<img width="320" height="240" id="scanned-img" src="assets/images/blank.png">
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
		<div class="col-md-6 col-md-offset-1 " id="stepsContainer">
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
		<!-- <div class="col-md-6 col-md-offset-1 thumbnail hidden">
			<div class="cardpadding">

				<div class="ajaxResponse">
					Scan a QR code to begin
				</div>
			</div>
		</div> -->
		<style type="text/css">
      		.files-table{

			font-size: 13px;
			font-weight: 600;
			text-align: center;
			border: 1px solid #fff ;
			border-collapse: collapse;
			width: 100%;
    height: 70px;
		}
		.files-table th{
			text-align: center;
			background-color: #d8e6fa;
			border-bottom: 0;
			border: 1px solid #fff  !important;
		}
		.files-table td {
			background-color: #f0f0f0;
			border: 1px solid #fff  !important;
		}

		.file-head {

			border: 1px solid #fff  !important;
		}
      	</style>
		<div class="col-md-6 col-md-offset-1 thumbnail" id="resultContainer" style="margin-top: 10%;display: none;">
			<div class="cardpadding" style="background-color: #f0f0f0;">
				<table class="files-table">
					<tr>
						<th>Total Scans</th>
						<th>Total Amount</th>
					</tr>
					<tr>
						<td><span id="totalFileScans" style="background-color: brown;color: #fff;padding: 5px;padding-left: 10px; padding-right: 10px; border-radius: 5px;">0</span></td>
						<td><span style="background-color: brown;color: #fff;padding: 5px;padding-left: 10px; padding-right: 10px; border-radius: 5px;">RS. <span id="amountToBePaid">0</span></span></td>
					</tr>
				</table>
				<h4 style="padding-left: 20px;  margin-top: 20px;    font-size: 15px; font-weight: 600; ">Scanning Result :</h4>
				<h4 style="text-align: center;padding: 15px;width: 100%;background-color: #d8e6fa;border-radius: 30px;" id="responseMessage">Your Certificate is active.</h4>
				<div class="row" style="    background-color: #fff; margin: 0px;">
					<div class="col-md-6" style="text-align: center;   padding: 15px;">
						<!-- <button type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #2196f3;background-color: #03a9f4 !important;color:#fff;margin: auto; " > Scan one more QR click on start button</button> -->
						<span type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #0052CC;background-color: #0052CC !important;color:#fff;margin: auto; " id="scan-more-btn"> Scan one more QR click on start button</span>
					</div>
					<div class="col-md-6" style="text-align: center;   padding: 15px;">
						<button type="button"  class="btn btn-sm btn-block form-btn" style="border:1px solid #4caf50; background-color: #8bc34a !important; color:#fff;max-width: 200px;    margin: auto;"  id="proceed-to-payment-btn"> View Certificate</button>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12" >
						<h4 style="margin-top: 30px;     margin-bottom: 20px; text-align: center;    width: 100%; color: red;font-size: 15px;"><span style="color: brown;">Note :</span> Maximum <span id="maxDocuments">10</span> scans allowed for single request.</h4>
					</div>
				</div>
				<!-- <div class="ajaxResponse">
					Scan a QR code to begin
				</div> -->
			</div>
		</div>
	</div>
</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>

<link rel="stylesheet" href="<?php echo e(asset('backend/css/webcamjs_style.css')); ?>">
<?php 
$domain =$_SERVER['HTTP_HOST'];
            $subdomain = explode('.', $domain);
	if($subdomain[0] == "galgotias"){

	}else if($subdomain[0] == "monad"||$subdomain[0] == "demo"){
 	?>
 		<style>
			body{
			padding:0;
			background-image:  url("../backend/images/monad-university_bg1.jpg");
			}
		</style>

 	<?php }else{ ?>
		<style>
		body{
			padding:0;
			background-image:  url("../backend/images/home.png");
		}
		</style>
<?php }  ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset('webapp/webcamjs/js/filereader.js')); ?>"></script>
<script src="<?php echo e(asset('webapp/webcamjs/js/qrcodelib.js')); ?>"></script>
<script src="<?php echo e(asset('webapp/webcamjs/js/webcodecamjs.js')); ?>"></script>
<script src="<?php echo e(asset('webapp/webcamjs/js/main.js')); ?>"></script>
<script>
var key = '';
var pay = '';
var totalFilesScanned=0;
var totalAmount=0;
var qrCodes = [];
var verificationAmount=0;
var maxVerificationCount=0;
$(window).focusin(function(){
	if(key != '' && pay !=''){
		ajaxCall(key,'1');
	}else{
		return false;
	}
});

$(window).focusout(function(){
	pay = '1';
});

function load(){
	// console.log('load');
	$('.payment-url').click(function(e){
		e.preventDefault();
		$url = $(this).attr('href');
		window.open($url,'_blank');
		pay = '1';
	});
}

$('#scan-more-btn').click(function(){
	if(totalFilesScanned<maxVerificationCount){
		/*$('#play').click();*/
		$('#responseMessage').html('<span style="padding: 10px;color: brown;border-radius: 5px;font-size: 17px;">Scanner started for scanning QR code.</span>');
	}else{
		/*$('#scan-more-btn').hide();*/
		$('#responseMessage').html('<span style="color:orange;">You can scan only '+maxVerificationCount+' QR codes for single request.</span>');
	}
});

$('#play,#stop').click(function(e){
	e.preventDefault();
	if($('#verification_type').val()=="degree"||$('#verification_type').val()=="marksheet"){
		if (navigator.userAgent.match(/chrome/i))
		{
			// bootbox.alert('Scaning by web-cam will work only on Mozilla Firefox currently. So open the website on Firefox browser. You can download it <a target="_blank" href="https://www.mozilla.org/en-US/firefox/new/">here</a> if you dont have the browser.');
		}
		else
		{
		$('#qrcode').fadeOut();
		$('.phonebg').addClass('col-md-offset-0');
		$('.thumbnail').addClass('hidden');
		$('.helper').removeClass('hidden');
		key ='';
		pay = '';
		}

	}else{
		bootbox.alert('Please select verification type.');
		//toastr["error"]('Please select verification type.');
	}
});


$("#proceed-to-payment-btn").click(function(e){
		e.preventDefault();

		if($('#verification_type').val()=="degree"||$('#verification_type').val()=="marksheet"){
		if (totalFilesScanned!=0)
		{
		var token = "<?php echo e(csrf_token()); ?>";
		var formData = new FormData();
		formData.append("action", "saveRequestMonad");
		formData.append("total_files_count", totalFilesScanned);
		formData.append("totalAmount", totalAmount);
		formData.append("qrCodes", qrCodes);
		formData.append("verification_type", $('#verification_type').val());
		formData.append("data_type", "qr");
		$.ajax({
            url: "<?php echo e(URL::route('raisoni.store')); ?>",
            headers: {'X-CSRF-TOKEN': token},
            type: 'POST',
            data: formData,
            success: function (data) {
                if(data.status == true){
                	//toastr["success"](data.message);
                	if(data.amount!=0){
                		
                			
                		window.location.href = "/verify/payment/paytm?key_payment="+data.request_number; 
                			
                	}else{
                		$('#QR-Code').hide();
                		$('#pdfDiv').show();
                		$('#pdfDiv').empty();
                		toastr["success"](data.message);
                		if(data.showPdf==true){

					      var length=data.dataPdf.length;
					      var hostname = window.location.hostname;
					      var subdomain = hostname.split('.');
					     // console.log(hostname);
					      for (var i=0; i<length; i++){
					      /*	console.log('https://'+hostname+'/'+subdomain[0]+'/backend/pdf_file/'+data.dataPdf[i]['certificate_filename']+'');*/
					        $('#pdfDiv').append('<embed src="https://'+hostname+'/'+subdomain[0]+'/backend/pdf_file/'+data.dataPdf[i]['certificate_filename']+'#toolbar=0" width="600" height="500" alt="pdf" pluginspage="http://www.adobe.com/products/acrobat/readstep2.html"><hr>');

					      }
					    }
                	}

                }else{
                	toastr["error"](data.message);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
            dataType:'JSON'
        });
		}else{
			toastr["error"]('Please scan some files.');
		}
		}else{
		bootbox.alert('Please select verification type.');
		//toastr["error"]('Please select verification type.');
	}

});


$('#verification_type').change (function () {
  var verification_type = this.value;
  if($('#verification_type').val()=="marksheet"){
  	maxVerificationCount=10;
  	verificationAmount = 300;
  	$('#maxDocuments').html(10);

  }else{
  	maxVerificationCount=1;
  	verificationAmount = 10000;
  	$('#maxDocuments').html(1);
  }
  	totalFilesScanned=0;
	totalAmount=0;
	qrCodes = [];
	$('#totalFileScans').html(0);
	$('#amountToBePaid').html(0);
});

function ajaxCall(val,update='0'){
	if($('#verification_type').val()=="degree"||$('#verification_type').val()=="marksheet"){

		if((totalFilesScanned<10&&$('#verification_type').val()=="marksheet")||(totalFilesScanned<1&&$('#verification_type').val()=="degree")){
			key = val;
			$('#scanned-QR').text('success');
			$('#response-QR').text(key);
			//$('#qrcode').removeClass('hidden');
			$('#qrcode').fadeIn();
			$('#resultContainer').show();

			if(qrCodes.includes(val)){
			$('#responseMessage').html('<span style="color:red;">You have already scanned this QR for same request.</span>');
			}else{
				
				$('#stepsContainer').hide();
				//

				qrCodes.push(key);
				totalFilesScanned=totalFilesScanned+1;
				
				if(totalFilesScanned==10&&$('#verification_type').val()=="marksheet"){
					$('#scan-more-btn').hide();
					//$('.scanner-div').hide();
				}else if(totalFilesScanned==1&&$('#verification_type').val()=="degree"){
					$('#scan-more-btn').hide();
				}else{
					$('#scan-more-btn').show();
				}
				totalAmount= parseFloat(totalAmount)+ parseFloat(verificationAmount);
				//$('#responseMessage').html(resMsg).show(100);
				$('#totalFileScans').html(totalFilesScanned);
				$('#amountToBePaid').html(totalAmount);
				$('#proceed-to-payment-btn').html('Procceed To Payment');
				$('#responseMessage').html('<span>You have successfully scanned QR. Total - '+totalFilesScanned+' .</span>');
			
			}
			return ;
		}else{

			$('#responseMessage').html('<span style="color:red;">You have reached maximum scan limit.</span>').show(100);
		}
	}else{
		bootbox.alert('Please select verification type.');
		//toastr["error"]('Please select verification type.');
	}
}
    




$('a[data-url^="dashboard"]').parent().addClass('active');

</script>
<?php $__env->stopSection(); ?>
<style>
.phonebg{
	background:url("../webapp/images/phonebg2.png");
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
<?php echo $__env->make('verify.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/dashboard_monad.blade.php ENDPATH**/ ?>