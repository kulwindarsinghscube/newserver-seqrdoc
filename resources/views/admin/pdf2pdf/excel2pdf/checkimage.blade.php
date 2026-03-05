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


table, td, th {
  border: 2px solid #dbdbdb;
  padding: 5px;
 
}

#table1 {
  border-collapse: collapse;
}

#table1 td,th{
	 color: red;
}
#table2 td,th{
	color: green;
}
#table3 td,th{
	color: orange;
}
</style>
<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-image"></i>Check Galgotias Images 
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('generategalgotiascertificates') }}</ol>
				</h1>
				
			</div>
		</div>
		<form method="post" id="processExcelForm" class="form-horizontal" enctype="multipart/form-data" action="<?=route('galgotias-certificate.checkimageexist')?>">
			<!-- <input type="hidden" name="func" id="func" value="uploadFile">  -->
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
		    	<label class="control-label col-sm-2" for="excel">Upload Excel:</label>
		    	<div class="col-sm-6">
		      		<input type="file" class="form-control" id="field_file" name="field_file">
		      		<span id="excel_data_error" class="help-inline text-danger"><?=$errors->first('excel_data')?></span>
		    	</div>
		  	</div>
		  	<!-- <div class="form-group">
		  		<label class="control-label col-sm-2" for="excel"></label>
		    	<div class="col-sm-10">
				  	<a href="" download>Download Sample Excel</a>
				</div>
		  	</div> -->
		  	<label class="control-label col-sm-2" for="excel" id="toggleText">Non-Existed Images:</label>
		  			<label class="switch">  
						<input type="checkbox" class="form-control" id="toggleImages" name="toggleImages" value="1" checked="">
						<span class="slider round"></span>
					</label>
						
						<input type="hidden" class="form-control" id="imagesType" name="imagesType" value="1">
		  	<div class="form-group"> 
		    	<div class="col-sm-offset-2 col-sm-10">
		    	
		      		<button type="submit" class="btn btn-primary" id="btn_updfile">Submit</button>

		    	</div>
		  	</div>
		  	<div class="form-group"> 
		    	<div class="col-sm-offset-2 col-sm-10">
		  	<h4 class="modal-title" id="divLoading" style="display: none;">Excel is processing... Please wait ..... <img src="/backend/images/loading.gif"></h4>
		  	</div>
		  </div>
		  	<div class="form-group" id="imagesNonExisted"> 
		    	<div class="col-sm-offset-2 col-sm-10">
		  			<table id="table1">
					  <tbody id="imageResp">
					  	
					  </tbody>
					</table>
				</div>
			</div>
			<div class="form-group" id="imagesExisted" style="display: none;"> 
		    	<div class="col-sm-offset-2 col-sm-4">
		  			<table id="table2">
					  <tbody id="imageRespExist">
					  	
					  </tbody>
					</table>
				</div>
				<div class="col-sm-offset-1 col-sm-4">
		  			<table id="table3">
					  <tbody id="imageRespExistErr">
					  	
					  </tbody>
					</table>
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

$('#toggleImages').on('change',function(){
	 var value = $('#imagesType').val();

	 if(value==1){
	 	
	 	$('#toggleText').html('Existed Images:');
	 	$('#imagesType').val(0);
	 	$('#imagesExisted').show();
	 	//$('#imagesExistedErr').show();
	 	$('#imagesNonExisted').hide();
	 }else{
	 	$('#toggleText').html('Non-Existed Images:');
	 	$('#imagesType').val(1);
	 	$('#imagesExisted').hide();
	 	//$('#imagesExistedErr').hide();
	 	$('#imagesNonExisted').show();
	 }
//  console.log(value);
});
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
			//console.log(resObj);
				$('#imageResp').empty();
				if(resObj.type == 1){
					
					toastr["success"](resObj.message);
					
					$('#divLoading').hide();

					if(resObj.strTd==''){
					$('#imageResp').html('<tr><td>No data Found!</td></tr>');
					}else{
					$('#imageResp').html(resObj.strTd);
					}
					if(resObj.strTdExist==''){
					$('#imageRespExist').html('<tr><td>No data Found!</td></tr>');
					}else{
					$('#imageRespExist').html(resObj.strTdExist);
					}
					if(resObj.strTdExistErr==''){
					$('#imageRespExistErr').html('<tr><td>No data Found!</td></tr>');
					}else{
					$('#imageRespExistErr').html(resObj.strTdExistErr);
					}
					// $('#download_link').empty();
				}else if(resObj.type == 2){
					if(resObj.strTd==''){
					$('#imageResp').html('<tr><td>No data Found!</td></tr>');
					}else{
					$('#imageResp').html(resObj.strTd);
					}
					if(resObj.strTdExist==''){
					$('#imageRespExist').html('<tr><td>No data Found!</td></tr>');
					}else{
					$('#imageRespExist').html(resObj.strTdExist);
					}
					if(resObj.strTdExistErr==''){
					$('#imageRespExistErr').html('<tr><td>No data Found!</td></tr>');
					}else{
					$('#imageRespExistErr').html(resObj.strTdExistErr);
					}
					$('#divLoading').hide();
					toastr["error"](resObj.message);
				}else{
					
					$('#divLoading').hide();
					toastr["error"](resObj.message);
				}
			}

		});
	}
})

</script>

@stop
