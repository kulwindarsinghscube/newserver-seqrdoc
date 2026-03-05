
@extends('admin.layout.layout')
@section('content')
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
</style>
<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i>Generate PDF
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('generatepdf') }}</ol>
				</h1>
				
			</div>
		</div>

		
       
		<form method="post" id="processExcelForm" class="form-horizontal" enctype="multipart/form-data" action="<?=route('kessc-certificate.uploadfile')?>">
			<input type="hidden" name="func" id="func" value="uploadFile"> 
			<input type="hidden" name="_token" value="{{csrf_token()}}"> 
			<!-- <div class="form-group">
						<label class="control-label col-sm-2" for="previewPdf">Generate Preview PDF :</label>
						<label class="switch">  
							<input type="checkbox" class="form-control" id="previewPdf" name="previewPdf" value="1" checked="">

							<span class="slider round"></span>
						</label>
						<label id="previewPdfCheckbox" style="position: absolute;margin: 3px 30px;color: #000000bd;"><input type="checkbox"  name="previewWithoutBg" id="previewWithoutBg" style="height: 20px;width: 20px;vertical-align: bottom;" /> <span>Select this to preview without Background</span></label>
						<input type="hidden" class="form-control" id="previewPdfValue" name="previewPdfValue" value="1">
			</div>  -->
			<div class="form-group">
                        <label class="control-label col-sm-2" for="temp">Page Type:</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="pageType" name="pageType" required>
                                <option value="">Select Page Type</option>
                                <option value="Single">Single</option>
                                <option value="Multiple">Multiple</option>                
                            </select>
                        </div>
                    </div>
		  	<div class="form-group">
		    	<label class="control-label col-sm-2" for="excel">Upload Excel:</label>
		    	<div class="col-sm-10">
		      		<input type="file" class="form-control" id="field_file" name="field_file">
		      		<span id="excel_data_error" class="help-inline text-danger"><?=$errors->first('excel_data')?></span>
		    	</div>
		  	</div>
		  
		  	<div class="form-group"> 
		    	<div class="col-sm-offset-2 col-sm-10">
		    		<?php
						$domain = \Request::getHost();
	        			$subdomain = explode('.', $domain);
	        			$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
     					$path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
     					$excel_url=$path.$subdomain[0]."/backend/sample_excel/KESSC Sample Excel.xlsx";
        			?>
		    		 <!-- <div align="center"><b>Please Click <a href="{{$excel_url}}" download>HERE</a> To Download Sample Excel</b></div> -->
		      		<button type="button" class="btn btn-primary" id="btn_updfile">Submit</button>

		    	</div>
		  	</div>
			  <div class="form-group"> 
			  <div class="col-sm-offset-2 col-sm-10">
			  <h4 class="modal-title" id="loader" style="color:crimson;display:none;padding: 5px 0px 5px 10px;background-color: lightgoldenrodyellow; border: 1px solid crimson;">Excel is processing... Please wait ..... <img src="/backend/images/loading.gif"></h4>
			  <h4 class="modal-title" id="successMessage" style="color:green;display:none;padding: 5px 0px 5px 10px;background-color: lightgoldenrodyellow; border: 1px solid green;">PDF is ready to download.<span id="downloadLink"></span></h4>
			  <h4 class="modal-title" id="errorMessage" style="color:red;display:none;padding: 5px 0px 5px 10px;background-color: lightgoldenrodyellow; border: 1px solid red;">Error while generating PDF.</h4>
			</div>
			</div>
			  
		</form>
	
		
		<div id="download_link">
			
		</div>
		
		
	</div>
</div>
@stop
@section('script')
<script type="text/javascript">

//on upload button click
$('#btn_updfile').click(function (event) {

	validateUpload();
});


function validateUpload() {

	$('#downloadLink').html('');
	var fd = new FormData();
	var files = $('[name="field_file"]')[0].files[0];
	var token = "<?= csrf_token()?>";

	fd.append('field_file',files);
	fd.append('_token',token);
    fd.append('api','convert');
	fd.append('pageType',$('#pageType').val());


	$.ajax({
		url:'https://aspose.seqrdoc.com/excel2pdf_api.php',
	    type: "POST",
		dataType: "JSON",
		data:fd,
		processData: false,
		contentType:false,
		//async:false,
		beforeSend: function (resp) {
			$('#successMessage').hide();
			$('#errorMessage').hide();
			$('#loader').show();
		},
		success:function(resp){
			$('#loader').hide();
			if(resp.status == 0){
				$('#errorMessage').html(resp.message);
                $('#errorMessage').show();
                //toastr["error"](resp.message);
            }
            else
            {
                $('#successMessage').show();
                //$("#downloadLink").attr("href", resp.link);
                $("#downloadLink").html('<a href="'+resp.link+'" target="_blank" download>Click here</a> to download pdf.');
               //toastr["success"](resp.message); 
            }
		},
		error:function(resp){
			if(resp.responseJSON != undefined){
				$('#field_file_error').text(resp.responseJSON.errors.field_file[0])
			}
		}
	});
} 


function uploadfile(){

	 
	var fd = new FormData();
	var files = $('[name="field_file"]')[0].files[0];
	var token = "<?= csrf_token()?>";
	var previewPdf=$('#previewPdfValue').val();
	if($("#previewWithoutBg").prop('checked') == true){
    var previewWithoutBg=1;
	}else{
	var previewWithoutBg=0;	
	}
	fd.append('previewPdf',previewPdf);
	fd.append('previewWithoutBg',previewWithoutBg);
	fd.append('field_file',files);
	fd.append('_token',token);
	fd.append('loader_token',$('#loader_token').val());
	fd.append('pageType',$('#pageType').val());

	$.ajax({
		url:'<?= route('kessc-certificate.uploadfile')?>',
	    type: "POST",
		dataType: "JSON",
		data:fd,
		processData: false,
		contentType:false,
		//async:false,
		beforeSend: function (resp) {
		$('#process').css('display', 'block');
	    $('.progress-bar').css('width','1%');
	    $('.progress-bar').text('1%');
	    $('#predictedTimeDiv').hide();
	    $('#totalTimeGenerationDiv').hide();
	    $('#downloadLink').html('<b>Preparing for download link.</b>');
	    $('#predictedTimeText').text('Approx. Completion Time ');
		load($('#loaderFile').val());
		},
		success:function(resp){
			$('#myModalLabel3').hide();
            $('#myModalLabel').hide();
            $('#myModalLabel1').hide();
            $('#myModalLabel2').show();
             $('#process').css('display', 'none');
			if(resp.success == false){
				toastr["error"](resp.message);
			}
			else
			{
				$('#downloadLink').html(resp.link);
				toastr["success"](resp.message);	
			}
		},
		error:function(resp){
			if(resp.responseJSON != undefined){
				$('#field_file_error').text(resp.responseJSON.errors.field_file[0])
			}
		}
	});
}


$('#processExcelForm').validate({

	rules:{
		pageType:{'required':true},
		excel_data:{'required':true, extension:'xls|xlsx'}
	},
	messages:{
		pageType:{'required':'Please select page type.'},
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
				// console.log(resObj.data[0].type);
				if(resObj.data[0].type == 'success'){
					
					$('#download_link').empty();
					toastr["success"](resObj.data[0].message);
					$('#download_link').html('<a href="'+resObj.data[0].link+'">Download </a>')
					$('#divLoading').hide();
					// $('#download_link').empty();

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
            	//console.log(result);
            	var isGenerationCompleted=result.isGenerationCompleted;

            	if(result.timePerCertificate!=0&&result.generatedCertificates!=0){
            		$('#predictedTimeDiv').show();
            		$('#predictedTime').text(result.predictedTime);
            		if(result.pendingCertificates==0){
            			$('#predictedTimeText').text('Completion Time ');

            			$('#totalTimeGenerationDiv').show();
            			$('#totalTimeGenerationTime').text(result.totalTimeForGeneration);
            		}
            	}

            	if(result.percentageCompleted!=0){
            	$('.progress-bar').css('width', result.percentageCompleted + '%');
            	$('.progress-bar').text(result.percentageCompleted + '%');

            	}
            	if(isGenerationCompleted==0){
            	
            	load(jsonFileUrl);

            	}else{
            		/*$('#process').css('display', 'none');
            		$('.progress-bar').css('width','1%');
            		$('.progress-bar').text('1%');*/
            		clearTimeoutFunction(loaderid,isGenerationCompleted,result.recordsToGenerate);
            	}
                /*$("#clog").empty();
                $.each(result, function (rowKey, row) {
                    $("#clog").append('<p ><h4>' + row.username + ':</h4>' + row.message_content + '</p>'); 
                }); */
            },
            error: function (error) {
			alert('error; Something Went Wrong!');
			}
            //complete: load(jsonFileUrl,1,loaderid) 
        });
    }, 1101);
  
}

function clearTimeoutFunction(id,isGenerationCompleted,recordsToGenerate){
	 clearTimeout(id);
	 if(isGenerationCompleted==1){

	 	/*var totalSeconds=Math.round((parseInt(recordsToGenerate)/100)*3);

		var timeout=(parseInt(totalSeconds)*1000)+2000;	 	
*/
	//$('#downloadLink').html('<b>Preparing for download link.</b>');
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
            //complete: load(jsonFileUrl,1,loaderid) 
        });
	 /*setTimeout(function() {
	    $('#process').css('display', 'none');
	  }, timeout);*/
	 
	}
}
</script>

@stop
