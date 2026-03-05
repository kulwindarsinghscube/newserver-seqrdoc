<?php $__env->startSection('content'); ?>
<style type="text/css">
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
  margin-left: 20px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
 /*range css*/
$range-width: 100% !default;

.range-slider {
  width: $range-width;
}
.range-slider__range {
  -webkit-appearance: none;
  width: calc(100% - (#{$range-label-width + 13px}));
  height: $range-track-height;
  border-radius: 5px;
  background: $range-track-color;
  outline: none;
  padding: 0;
  margin: 0;

  &::-webkit-slider-thumb {
    appearance: none;
    width: $range-handle-size;
    height: $range-handle-size;
    border-radius: 50%;
    background: $range-handle-color;
    cursor: pointer;
    transition: background .15s ease-in-out;

    &:hover {
      background: $range-handle-color-hover;
    }
  }

  &:active::-webkit-slider-thumb {
    background: $range-handle-color-hover;
  }

  &::-moz-range-thumb {
    width: $range-handle-size;
    height: $range-handle-size;
    border: 0;
    border-radius: 50%;
    background: $range-handle-color;
    cursor: pointer;
    transition: background .15s ease-in-out;

    &:hover {
      background: $range-handle-color-hover;
    }
  }

  &:active::-moz-range-thumb {
    background: $range-handle-color-hover;
  }

  &:focus {
    
    &::-webkit-slider-thumb {
      box-shadow: 0 0 0 3px $shade-0,
                  0 0 0 6px $teal;
    }
  }
}
.range-slider__value {
  display: inline-block;
  position: relative;
  width: $range-label-width;
  color: $shade-0;
  line-height: 20px;
  text-align: center;
  border-radius: 3px;
  background: $range-label-color;
  padding: 5px 10px;
  margin-left: 8px;

  &:after {
    position: absolute;
    top: 8px;
    left: -7px;
    width: 0;
    height: 0;
    border-top: 7px solid transparent;
    border-right: 7px solid $range-label-color;
    border-bottom: 7px solid transparent;
    content: '';
  }
}
blockquote {
    padding: 2px 10px;
    margin: 0 0 6px;
    font-size: 14px;
    border-left: 5px solid #0052CC;
    background-color: #e8eff9;
}
</style>
<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i> GHRSTU
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('generatespitcertificates')); ?></ol>
				</h1>
				
			</div>
		</div>
        <?php 
        if($ftp_flag=="Not Connected"){ 
            //echo "<h4 class='text-danger'>Failed to connect FTP ($ftpHost). Please Check. Otherwise PDF won't be saved to $ftpHost</h4>";
        } 
        ?>
        <div class="modal fade" id="uploadFile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								
								<h4 class="modal-title" id="myModalLabel">Excel is processing... Please wait ..... <img src="/backend/images/loading.gif"></h4>
							<h4 class="modal-title" id="myModalLabel1" style="display: none;">Generate Certificate</h4>
							<!--
							<h4 class="modal-title" id="myModalLabel2" style="display: none;">PDF is ready to download.</h4>
							<h4 class="modal-title" id="myModalLabel3" style="display: none;">Cetificates are processing... Please wait ..... <img src="/backend/images/loading.gif"> </h4>
							-->
							<h4 class="modal-title" id="myModalLabel2" style="display: none;">PDF is ready to download. <button type="button" class="btn btn-primary" id="btn_refresh1" style="margin-left: 45px;">Check Status</button></h4>
							<h4 class="modal-title" id="myModalLabel3" style="display: none;">Cetificates are processing... Please wait ..... <img src="/backend/images/loading.gif"> <button type="button" class="btn btn-primary" id="btn_refresh" style="margin-left: 45px;">Check Status</button></h4>
							</div>
							<div class="modal-body">
							<h4 id="sandboxing_message" class="text-center" style="color: red;"><b>Under Sandboxing environment</b></h5>	
							<h4 style="color: red;display: none;margin-bottom: 10px;" id="previewPdfTitle">Generating Preview PDF</h4>							
								<div id="downloadLink">
								</div>
						    <!--Custom Loader Start-->
								<input type="hidden" name="loader_token" id="loader_token" value="0"/>
								<input type="hidden" name="loaderFile" id="loaderFile" value="0"/>
								<div class="form-group" id="process" style="display:none;">
						        <div class="progress">
						       <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 1%;">1%
						       </div>
						      </div>
						       </div>
								<div class="form-group clearfix" id="loaderDiv" style="display: none;">
									<style type="text/css">td{border: 1px solid #dbdbdb !important;}</style>
									<table class="table" style="max-width: 500px;border: 1px solid rgb(219 219 219) !important;">
										<tr><td style="width: 60%;">Generated Certificates Count</td><td id="generatedCertificates" style="width: 40%;padding-left: 10px;"></td></tr>
										<tr><td style="width: 60%;">Pending Certificates Count</td><td id="pendingCertificates" style="width: 40%;padding-left: 10px;"></td></tr>
										<tr><td style="width: 60%;">Total Certificates To Generate Count</td><td id="recordsToGenerate" style="width: 40%;padding-left: 10px;"></td></tr>

										<tr id="predictedTimeDiv" style="display: none;"><td id="predictedTimeText" style="width: 60%;">Approx. Completion Time </td><td id="predictedTime" style="width: 40%;padding-left: 10px;"></td></tr>
										<tr id="totalTimeGenerationDiv" style="display: none;"><td id="totalTimeGenerationText" style="width: 60%;">Total Time For Generation </td><td id="totalTimeGenerationTime" style="width: 40%;padding-left: 10px;"></td></tr>
									</table>
								</div>
								<!--Custom Loader End-->								
								<div class="form-group clearfix">
									<div id="duplicate_row_count" class=""></div>
									<div id="upload_btn">
										<a href="#" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-7" id="btn_updfile_back" class="close" data-dismiss="modal" aria-hidden="true" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
										<button type="button" class="btn btn-theme" value="pdf_generate" id="pdf_generate" name="pdf_generate" style="display: none;" ><i class="fa fa-upload" ></i>PDF Generate</button>
									</div>
								</div>			
							</div>
						</div>
					</div>
				</div>
        
        <div class="panel panel-default">
        <div class="row">
            <div class="col-lg-7"> <br />
                <form method="post" id="processExcelForm" class="form-horizontal" enctype="multipart/form-data" action="<?=route('ghrstu-certificate.uploadfile')?>">
                    <input type="hidden" name="func" id="func" value="uploadFile"> 
                    <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>"> 
                  
                    <div class="form-group">
                                <label class="control-label col-sm-3" id="generate_title" for="previewPdf">PDF Preview:</label>
                                <label class="switch">  
                                    <input type="checkbox" class="form-control" id="previewPdf" name="previewPdf" value="1" checked="">

                                    <span class="slider round"></span>
                                </label>
                                <label id="previewPdfCheckbox" style="position: absolute;margin: 3px 30px;color: #000000bd;"><input type="checkbox"  name="previewWithoutBg" id="previewWithoutBg" style="height: 20px;width: 20px;vertical-align: bottom;" /> <span>Select this to preview without Background</span></label>
                                <input type="hidden" class="form-control" id="previewPdfValue" name="previewPdfValue" value="1">
                            </div> 
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="temp">Template:</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="template_id" name="template_id">
                                <option value="0" data-tempid="0">Select Template</option>
                                <option value="1" data-tempid="1">Grade Card</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="excel">Upload Excel:</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" id="field_file" name="field_file">
                            <span id="excel_data_error" class="help-inline text-danger"><?=$errors->first('excel_data')?></span>
                        </div>
                    </div>
                  
                    <div class="form-group">                        
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="button" class="btn btn-primary" id="btn_updfile">Submit</button>
                        </div>
                    </div>
                   
                 
                </form>
            </div> 
            <div class="col-lg-5" style="padding: 83px 1em;"> 
                <?php
                    $domain = \Request::getHost();
                    $subdomain = explode('.', $domain);
                    $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                    $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
                    $excel_url_1=$path.$subdomain[0]."/backend/sample_excel/ghrstu_sample.xlsx";
                     $excel_url_2=$path.$subdomain[0]."/backend/sample_excel/ghrstu_sample_certificate.xlsx";
                ?>
                <span style="display:none;" id="excel_url_1"><b>Please Click <a href="<?php echo e($excel_url_1); ?>" download>HERE</a> To Download Sample Excel</b></span>
                <span style="display:none;" id="excel_url_2"><b>Please Click <a href="<?php echo e($excel_url_2); ?>" download>HERE</a> To Download Sample Excel</b></span>

                
			</div>
        </div> 
        </div> 
		
		<div id="download_link">
			
		</div>
            <div class="col-sm-12">
                <h5>Notes</h5>
                <blockquote>Ensure that the serial number in excel file is unique across all data.</blockquote>
                <blockquote>Name are case sensitive, column sequence insensitive.</blockquote>
                <blockquote>If the unique serial number is repeated or found duplicate in the excel data, then it will replace the already existing data (making them inactive), with the current excel data.</blockquote>
                <blockquote>Accepted file format XLS or XLSX.</blockquote>
                <blockquote>Max file size 10 MB</blockquote>
                <blockquote>Keep "Text" format for date column.</blockquote>
                <blockquote>The cell value is printed exactly as entered.</blockquote>
                <br />
            </div>   		
		
	</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

<script type="text/javascript">
$.ajax({
		type:'get',
		url : "<?=route('templateMaster.check-sandbox')?>",
		dataType:'json',
		success:function(response){
			if(response.sandboxing == 1){
				
	        }else{

				$('#sandboxing_message').text('');
	        }
		}
	});

$('#previewPdf').on('change',function(){
	 var value = $('#previewPdfValue').val();
	 if(value==1){
	 	$('#generate_title').html('Generating Live PDF:');
	 	$('#previewPdfValue').val(0);
	 	$('#previewPdfCheckbox').hide();
	 }else{
	 	$('#previewPdfValue').val(1);
	 	$('#previewPdfCheckbox').show();
        $('#generate_title').html('Generating Preview PDF:');
	 }

});

//on upload button click
$('#btn_updfile').click(function (event) {
	var template_id=$('#template_id').val();
	if(template_id == 0){
		//alert("Please select template.");
		toastr["error"]("Please select template.");
		return false;
	}
	else{
		validateUpload();
	}
});

$('#pdf_generate').click(function (event) {
		$('#downloadLink').html('<b>Pdf download link will appear here.</b>');
	    $('#myModalLabel').hide();
	    $('#myModalLabel1').hide();
	    $('#myModalLabel2').hide();	
	    $('#myModalLabel3').show();	
	    $('#duplicate_row_count').html('');
		$('#btn_updfile_back').hide();
		$('#pdf_generate').hide(); 
		/*For custom loader*/
		$('#loaderDiv').hide();
		$('#predictedTimeDiv').hide();
		/*End For custom loader*/
	uploadfile();
});

function validateUpload() {
	var template_id=$('#template_id').val();
    var previewPdf=$('#previewPdfValue').val();
	if(previewPdf==1){
		$('#previewPdfTitle').show();
	}else{
		$('#previewPdfTitle').hide();
	}
	$('#downloadLink').html('');
	var fd = new FormData();
	var files = $('[name="field_file"]')[0].files[0];
	var token = "<?= csrf_token()?>";

	fd.append('field_file',files);
	fd.append('_token',token);
    fd.append('template_id',template_id);
	$.ajax({
		url:'<?= route('ghrstu-certificate.validateexcel')?>',
	    type: "POST",
		dataType: "JSON",
		data:fd,
		processData: false,
		contentType:false,
		
		beforeSend: function (resp) {
			$('#myModalLabel').show();
		},
		success:function(resp){
			
			$('#field_file_error').text('')
			if(resp.success == false){
				toastr["error"](resp.message);
			}
			else
			{
				//For Custom Loader
				$('#loader_token').val(resp.loader_token);
				$('#loaderFile').val(resp.loaderFile);
				
				if(resp.old_rows==0 || previewPdf==1){
					$('#uploadFile').modal('show');
					$('#duplicate_row_count').html('');
					$('#downloadLink').html('<b>Pdf download link will appear here.</b>');
					$('#btn_updfile_back').hide();
					$('#pdf_generate').hide();
					$('#myModalLabel1').hide();
					$('#myModalLabel2').hide();
					$('#myModalLabel').hide();
					$('#myModalLabel3').show();
					uploadfile();
				}else{
					$('#downloadLink').html('');
					$('#uploadFile').modal('show');
					$('#duplicate_row_count').html('You have '+ resp.old_rows +' old records and '+resp.new_rows+' new records');
					$('#btn_updfile_back').show();
					$('#pdf_generate').show();
					$('#myModalLabel1').show();
					$('#myModalLabel2').hide();
					$('#myModalLabel').hide();
					$('#myModalLabel3').hide();	
				}
				
			}
		},
		error:function(resp){
			if(resp.responseJSON != undefined){
				$('#field_file_error').text(resp.responseJSON.errors.field_file[0])
			}
		}
	});
} 



// Start Update code for batchwise genration
var isLoading = false;
function uploadfile(startRow = 2, endRow = 2, highestRow = null) {
    var fd = new FormData();
    var files = $('[name="field_file"]')[0].files[0];
    var token = "<?= csrf_token()?>";
    var previewPdf = $('#previewPdfValue').val();
    var previewWithoutBg = $("#previewWithoutBg").prop('checked') ? 1 : 0;
    var template_id = $('#template_id').val();

    fd.append('previewPdf', previewPdf);
    fd.append('previewWithoutBg', previewWithoutBg);
    fd.append('template_id', template_id);
    fd.append('field_file', files);
    fd.append('startRow', startRow);
    fd.append('endRow', endRow);
    fd.append('_token', token);
    fd.append('loader_token', $('#loader_token').val());

    $.ajax({
        url: '<?= route('ghrstu-certificate.uploadfile') ?>',
        type: "POST",
        dataType: "JSON",
        data: fd,
        processData: false,
        contentType: false,
        beforeSend: function () {
          console.log(`Processing rows ${startRow} to ${endRow}`);
          if(startRow==2){
            $('#process').css('display', 'block');
            $('.progress-bar').css('width', '1%');
            $('.progress-bar').text('1%');
            $('#predictedTimeDiv').hide();
            $('#totalTimeGenerationDiv').hide();
            $('#predictedTimeText').text('Approx. Completion Time ');
            load($('#loaderFile').val());
          }
          if(startRow==highestRow){
            $('#downloadLink').html('<b>Preparing for download link.</b>');
          }
        },
        success: function (resp) {
            $('#myModalLabel3').hide();
            $('#myModalLabel').hide();
            $('#myModalLabel1').hide();
            $('#myModalLabel2').show();

            if (resp.success === false) {
                toastr["error"](resp.message);
            } else {
                

                // Set highestRow dynamically from the response on the first call
                if (highestRow === null) {
                    highestRow = resp.highestRow;
                }

                var highestRowCheck= resp.highestRow+1;

                // Trigger next API call if rows are left
                if (parseInt(resp.endRow) < parseInt(highestRowCheck)) {

                  if(resp.link=="Will be generated soon!"){


                    uploadfile(endRow + 1, Math.min(endRow + 1, highestRow), resp.highestRow);
                  }else{
                      $('#downloadLink').html(resp.link);
                     toastr["success"](resp.message);
                    console.log('All rows processed successfully.');
                  }
                } else {
                  $('#downloadLink').html(resp.link);
                 toastr["success"](resp.message);
                    console.log('All rows processed successfully.');
                }
            }
        },
        error: function (resp) {
            if (resp.responseJSON !== undefined) {
                $('#field_file_error').text(resp.responseJSON.errors.field_file[0]);
            }
        }
    });
}
// End Update code for batchwise genration


$('#processExcelForm').validate({

	rules:{
		excel_data:{'required':true, extension:'xls|xlsx'}
	},
	messages:{
		excel_data:{
			required:'please choose file',
			extension:'please select only excel file',
		}
	},
	submitHandler: function(form){
		
		$('#processExcelForm').ajaxSubmit({
			target:'#response',
			beforeSubmit:function(formData,jqform, options){
				$('#divLoading').show();
			},clearForm:false,dataType:'json',success:function(resObj){
				
				if(resObj.data[0].type == 'success'){
					
					$('#download_link').empty();
					toastr["success"](resObj.data[0].message);
					$('#download_link').html('<a href="'+resObj.data[0].link+'">Download </a>')
					$('#divLoading').hide();
					

				}else{

					$('#divLoading').hide();
					toastr["error"](resObj.data[0].message);
				}
			}

		});
	}
})
$('#download_link').click(function(){
	setTimeout(function(){ window.location.reload(); }, 800);
});
$('#template_id').change(function(){
    tempid = $(this).find(':selected').data('tempid');
    if(tempid=="0"){
        for (let i = 2; i <= 19; i++) {
          $("#excel_url_"+i).hide();
        }             
    }else{
        $("#excel_url_"+tempid).show();
        for (let i = 2; i <= 19; i++) {
          if(i != tempid){
            $("#excel_url_"+i).hide();
          }
        }        
    }
});    

function load(jsonFileUrl) {
    var loaderid =setTimeout(function () {
        $.ajax({
            url: jsonFileUrl,
            type: "GET",
            dataType: 'json',  
            success: function (result) {
            	$('#loaderDiv').show();
            	$('#pendingCertificates').text(result.pendingCertificates);
            	$('#recordsToGenerate').text(result.recordsToGenerate);
            	$('#generatedCertificates').text(result.generatedCertificates);
            	console.log(result);
            	var isGenerationCompleted=result.isGenerationCompleted;

            	if(result.timePerCertificate!=0&&result.generatedCertificates!=0){
            		$('#predictedTimeDiv').show();
            		$('#predictedTime').text(result.predictedTime);
            		if(result.pendingCertificates==0){
            			$('#predictedTimeText').text('Completion Time ');
            			$('#totalTimeGenerationDiv').show();
            			$('#totalTimeGenerationTime').text(result.totalTimeForGeneration);

                  // Update code for batchwise genration
                  isLoading = false;

            		}
            	}
  
            	if(result.percentageCompleted!=0){
            	$('.progress-bar').css('width', result.percentageCompleted + '%');
            	$('.progress-bar').text(result.percentageCompleted + '%');

            	}
            	if(isGenerationCompleted==0){
            		load(jsonFileUrl);
            	}else{
            		clearTimeoutFunction(loaderid,isGenerationCompleted,result.recordsToGenerate);
            	}
                
            },
            error: function (error) {
								alert('error; Something Went Wrong!');
						}
            
       });
    }, 1101);
  
}

$('#btn_refresh1').click(function (event) {

	load($('#loaderFile').val());
});


$('#btn_refresh').click(function (event) {

	load($('#loaderFile').val());
});



$('.close').click(function (event) {

	location.reload();
});

function clearTimeoutFunction(id,isGenerationCompleted,recordsToGenerate){
	 clearTimeout(id);
	 /*if(isGenerationCompleted==1){
		
	 var loader_token= $('#loader_token').val();
	 var token = "<?= csrf_token()?>";
	 $.ajax({
            url: '<?= route('deleteloaderjson.delete')?>',
            type: "POST",
            data:{'_token':token,'loader_token':loader_token},
            dataType: 'json',  
            success: function (result) {
            	$('#loader_token').val('');
            	$('#loaderFile').val('');
            }
        });
	}*/
} 
</script>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/ghrstu/index.blade.php ENDPATH**/ ?>