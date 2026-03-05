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
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i>Generate Demo A3 Transcript
					<!-- <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('generatebmcccertificates') }}</ol> -->
				</h1>
				
			</div>
		</div>
    
		<!-- modal start -->
    <div class="modal fade" id="uploadFile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Excel is processing... Please wait ..... <img src="/backend/images/loading.gif"></h4>
						<h4 class="modal-title" id="myModalLabel1" style="display: none;">Generate Certificate</h4>
						<h4 class="modal-title" id="myModalLabel2" style="display: none;">PDF is ready to download.</h4>
						<h4 class="modal-title" id="myModalLabel3" style="display: none;">Cetificates are processing... Please wait ..... <img src="/backend/images/loading.gif"> </h4>
					</div>
					<div class="modal-body">
						<h4 id="sandboxing_message" class="text-center" style="color: red;"><b>Under Sandboxing environment</b></h5>	
						<h4 style="color: red;display: none;margin-bottom: 10px;" id="previewPdfTitle">Generating Preview PDF</h4>							
						<div id="downloadLink"></div>
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
		<!-- modal end -->
       
		<!-- <form method="post" id="processExcelForm" class="form-horizontal" enctype="multipart/form-data" action="<?=route('demo-certificate.uploadfile')?>"> -->
		<form class="form-horizontal" action="{{route('testUploadFile')}}" method="POST" target="_blank" enctype="multipart/form-data" >
			@csrf
			<input type="hidden" name="func" id="func" value="uploadFile"> 
			<input type="hidden" name="_token" value="{{csrf_token()}}"> 
			<div class="form-group">
				<label class="control-label col-sm-2" for="previewPdf">Generate Preview PDF :</label>
				<label class="switch">  
					<input type="checkbox" class="form-control" id="previewPdf" name="previewPdf" value="1" checked="">
					<span class="slider round"></span>
				</label>
				
				<label id="previewPdfCheckbox" style="position: absolute;margin: 3px 30px;color: #000000bd;">
					<input type="checkbox"  name="previewWithoutBg" id="previewWithoutBg" style="height: 20px;width: 20px;vertical-align: bottom;" /> <span>Select this to preview without Background</span>
				</label>
				<input type="hidden" class="form-control" id="previewPdfValue" name="previewPdfValue" value="1">
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
      		?>
	    		<!-- <div align="center"><b>Please Click <a href="{{asset('backend/processExcel/sampleExcel.xlsx')}}" download>HERE</a> To Download Sample Excel</b></div> -->
	      	<button type="submit" class="btn btn-primary" >Submit</button>
	      	<!-- <button type="button" class="btn btn-primary" id="btn_updfile">Submit</button> -->
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
	$.ajax({
		type:'get',
		url : "<?=route('templateMaster.check-sandbox')?>",
		dataType:'json',
		success:function(response){
			if(response.sandboxing == 1){
				// $('#sandboxing_message').text('');
	    }else{
				$('#sandboxing_message').text('');
	    }
		}
	});

	$('#previewPdf').on('change',function(){
	 	var value = $('#previewPdfValue').val();
	 	if(value==1){
	 		$('#previewPdfValue').val(0);
	 		$('#previewPdfCheckbox').hide();
	 	}else{
	 		$('#previewPdfValue').val(1);
	 		$('#previewPdfCheckbox').show();
	 	}
		//  console.log(value);
	});

	//on upload button click
	$('#btn_updfile').click(function (event) {
		validateUpload();
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
		uploadfile();
	});

	function validateUpload() {
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
		$.ajax({
			url:'<?= route('demo-certificate.validateexcel')?>',
	    type: "POST",
			dataType: "JSON",
			data:fd,
			processData: false,
			contentType:false,
			//async:false,
			beforeSend: function (resp) {
				$('#myModalLabel').show();
			},
			success:function(resp){
				$('#field_file_error').text('')
				if(resp.success == false){
					toastr["error"](resp.message);
				} else{
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
		$.ajax({
			url:'<?= route('demo-certificate.uploadfile')?>',
		  type: "POST",
			dataType: "JSON",
			data:fd,
			processData: false,
			contentType:false,
			//async:false,
			success:function(resp){
				$('#myModalLabel3').hide();
        $('#myModalLabel').hide();
        $('#myModalLabel1').hide();
        $('#myModalLabel2').show();
				if(resp.success == false){
					toastr["error"](resp.message);
				} else {
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
</script>

@stop
