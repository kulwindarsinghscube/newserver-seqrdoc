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
		<div class="col-xs-12">
			<div class="clearfix">
				<form method="post" id="templfrm">
				<!-- <form method="post" id="templfrm"  action="add_template.php"> -->
					<input type="hidden" name="edit" id="edit_id">
				</form>
				<div class="modal fade" id="uploadFile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="myModalLabel">Upload excel</h4>
							</div>
							<div class="modal-body">

								<h4 id="sandboxing_message" class="text-center" style="color: red;"><b>Under Sandboxing environment</b></h5>						
								<form method="post" action="<?=route('template-master.index')?>" enctype="multipart/form-data" id="updfilefrm">	
									<div class="form-group">
										<label class="control-label col-sm-3" for="previewPdf">Generate Preview PDF :</label>
										<label class="switch">  
											<input type="checkbox" class="form-control" id="previewPdf" name="previewPdf" value="1" checked="">

											<span class="slider round"></span>
										</label>
										<label id="previewPdfCheckbox" style="position: absolute;margin: 3px 30px;color: #000000bd;"><input type="checkbox"  name="previewWithoutBg" id="previewWithoutBg" style="height: 20px;width: 20px;vertical-align: bottom;" /> <span style="font-size: 12px;">Select this to preview without Background</span></label>
										<input type="hidden" class="form-control" id="previewPdfValue" name="previewPdfValue" value="1">
									</div> 
									<div class="form-group">
										<label>Upload File</label>
										<input type="hidden" name="_token" value="{{csrf_token()}}">
										<input type="file" class="form-control" id="field_file" name="field_file">
										<span id="field_file_error" class="help-inline text-danger"></span>
										<input type="hidden" name="id" id="template_id">
										<input type="hidden" name="func" id="func" value="generateUploadfile">
										<input type="hidden" name="print_type" value="pdf">
									</div>
									<div id="downloadLink">
									</div>
									<div class="form-group clearfix">
										<div id="duplicate_row_count" class=""></div>
										<div id="upload_btn">
											<a href="#" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-7" id="btn_updfile_back" onclick="closeModel();" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
											<button type="button" class="btn btn-theme" value="pdf_generate" id="pdf_generate" name="pdf_generate" style="display: none"><i class="fa fa-upload" ></i>PDF Generate</button>
											<button type="button" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="btn_updfile"><i class="fa fa-upload"></i> Upload</button>
										</div>
									</div>
								</form>

								<div class="progress">
								    <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
								      <span class="sr-only progress_bar_text"></span>
								    </div>
								</div>
								<p class="pdf_progress">Pdf Process Count : <span class="pdf_count text-danger"></span></p>
								<p class="time_details">Generation start time : <span class="start_time text-danger"></span><br/>Generation end time : <span class="end_time text-danger"></span><br/>Generation total time : <span class="total_time text-danger"></span><br/>Average Speed : <span class="avg_time text-danger"></span></p>
								<input type="hidden" name="pdf_generation_start_time" class="pdf_gen_start_time" value="">
								<input type="hidden" name="pdf_generation_start_timestamp" class="pdf_gen_start_timestamp" value="">
								<input type="hidden" name="pdf_generation_end_time" class="pdf_gen_end_time" value="">


								<div class="form-group">
									<div>Note:-</div>
									<ol>
									<!-- 	<li>Ensure that all fields are mapped.</li>
										<li>Ensure that all fonts used in templates master are available.</li>
										<li>Ensure that all columns mapped in the template master are available with the same name in your current uploading excel sheet.</li> -->
										<li>Ensure that the serial no in excel file is unique across all data.</li>
										<li>If the unique serial number is repeated or found duplicate in the excel data, then it will replace the already existing data (making them inactive), with the current excel data.</li>
										<li>Accepted file format XLS or XLSX.</li>
										<li>Max file size 10 MB</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i> Template Master
								<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('customtemplatemaster') }}</ol>
								</h1>
							</div>
						</div>
						<div class="">
							<ul class="nav nav-pills" id="pills-filter" style="display: none;">
							  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Templates </a></li>
							  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Templates</a></li>
							  
							 
							  	<li style="float: right;">
									<!-- <button class="btn btn-theme" id="report"><i class="fa fa-file"></i> Generate Report</button>	 -->
									<a style="background: #0052CC;padding-top:5px; color: #fff;border-radius: 2px;box-shadow: 2px 2px 5px #999;border: 1px solid transparent;height: 35px;" href="<?=route('template-master.excelreport')?>"  id="report" ><i class="fa fa-file"></i> Generate Report</a>	
								</li>
							   @if(App\Helpers\SitePermissionCheck::isPermitted('template-master.create'))
							   @if(App\Helpers\RolePermissionCheck::isPermitted('template-master.create'))	
								<li style="float: right;">
									<button class="btn btn-theme" id="addtemplate"><i class="fa fa-plus"></i> Add Template</button>	
								</li>
								@endif
								@endif
							</ul>
							<?php
		        		$domain = \Request::getHost();
        				$subdomain = explode('.', $domain);
        				 $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        				$path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
		        	?>
							<table id="example" class="table table-hover display" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>#</th>
										<th>Template Name</th>
										<th>Action</th>
									</tr>
								</thead>
								 <tbody> 
									<tr>
										<td>1</td>
										<td>UG</td>
										<td><span style="cursor:pointer"><i title="Generate PDF" data-id="UG" class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i>&nbsp;&nbsp;<a href="<?php echo $path.'/'.$subdomain[0].'/backend/sample_excel/UASB UG.xlsx';?>" download><i title="Download Sample Excel" data-id="UG_Excel" class="fa fa-download fa-lg blue"></i></a></span></td>
									</tr>
									<tr>
										<td>2</td>
										<td>PG</td>
										<td><span style="cursor:pointer"><i title="Generate PDF" data-id="PG" class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i>&nbsp;&nbsp;<a href="<?php echo $path.'/'.$subdomain[0].'/backend/sample_excel/UASB PG.xlsx';?>" download><i title="Download Sample Excel" data-id="PG_Excel" class="fa fa-download fa-lg blue"></i></a></span></td>
									</tr>
									<tr>
										<td>3</td>
										<td>Gold</td>
										<td><span style="cursor:pointer"><i title="Generate PDF" data-id="Gold" class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i>&nbsp;&nbsp;<a href="<?php echo $path.'/'.$subdomain[0].'/backend/sample_excel/UASB GOLD.xlsx';?>" download><i title="Download Sample Excel" data-id="GOLD_Excel" class="fa fa-download fa-lg blue"></i></a></span></td>
									</tr>
								 </tbody> 
								<tfoot>
								</tfoot>
							</table>
						</div>
						
					</div>
				</div>
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

 /*   function copyTemplate(id){

    	var url="{{ URL::route('template-master.copyTemplate.copy') }}";
    	var token="{{ csrf_token() }}";
    	var method_type="post";
        bootbox.confirm("Are you sure you want to copy?",function(result){	
          if(result){
    	      $.post(url,{'_token':token,'template_id':id}, function(data) {
    	      	if(data.success==true){
                  toastr.success(data.msg);
                  oTable.ajax.reload();
    	      	}
    	    });
          }
        });
    }*/
	var oTable = $('#example').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        /*"bProcessing": false,
        "bServerSide": true,*/
        "autoWidth": true,

        "aaSorting": [
        [0, "desc"]
        ],
      // "sAjaxSource":"<?= URL::route('uasb-certificate.index',['status'=>1])?>",
       /* "aoColumns":[
		{mData: "rownum", bSortable:false},
		{mData: "actual_template_name"},
		{mData: 'id',
            bSortable: false,
            sWidth: "30%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            	var edit_url = "{{route('template-master.edit',':id')}}"
            	edit_url = edit_url.replace(':id',o['id']);

            	var map_url =  "{{route('template-master.template-map.edit',':id')}}"
            	map_url = map_url.replace(':id',o['id']);
            	var buttons = '';
            	
            	buttons = '@if(App\Helpers\SitePermissionCheck::isPermitted('template-master.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('template-master.edit')) <a href="'+edit_url+'"><span style="cursor:pointer"><i title="Edit" data-id="'+o['id']+'" class="editrow fa fa-edit fa-lg green"></i> </span></a> @endif @endif';

                buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('template-master.copyTemplate.copy')) @if(App\Helpers\RolePermissionCheck::isPermitted('template-master.copyTemplate.copy')) <span style="cursor:pointer" onclick="copyTemplate('+o['id']+')" ><i title="Copy Template" class="copyrow fa fa-copy fa-lg yellow"></i></span> @endif @endif';

                buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('template-master.template-map.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('template-master.template-map.edit')) <a href="'+map_url+'"> <span style="cursor:pointer"><i title="Map Template" data-id="'+o['id']+'" class="maprow fa fa-map-marker fa-lg black"></i></span></a> @endif @endif';
                 buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('template-master.uplodafile')) @if(App\Helpers\RolePermissionCheck::isPermitted('template-master.uplodafile')) <span style="cursor:pointer"><i title="Generate PDF" data-id="'+o['id']+'" class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i></span> @endif @endif';   
				return buttons;
            },
         		   	
        },
	],*/
	"createdRow": function( row, data, dataIndex ) {

		/*if(data['status'] == 'Active'){
			$(row).addClass( 'active-student' );
		}else{
			$(row).addClass( 'inactive-student' );
		}*/
	}
});
oTable.on('draw', function () {
	$('[title]').tooltip(); 
});
/*$('#success-pill').click(function(){

	var url="<?= URL::route('template-master.index',['status'=>1])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	$('.loader').addClass('hidden');
});

$('#fail-pill').click(function(){

	var url="<?= URL::route('template-master.index',['status'=>0])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	$('.loader').addClass('hidden');
});
$('#addtemplate').click(function(){

	$.ajax({
		type:'get',
		url : "<?=route('templateMaster.checkLimit')?>",
		dataType:'json',
		success:function(response){
			if(response.type == 'success'){
				window.location.href = '<?=route('template-master.create')?>'
			}else{
				
			}
		}
	})
});*/
$('#field_file').change(function(){

	$('#func').val('checkExcel');	
	$('#btn_updfile_back').hide();
	$('#pdf_generate').hide();
	$('#btn_updfile').show();
});

$('.progress').hide();
$('.pdf_progress').hide();
$('.time_details').hide();

//on generate pdf click
oTable.on('click','.pdfGenerate',function(e){
	$template_id = $(this).data('id');
	var user_id = "<?= Auth::guard('admin')->user()->id?>"
	$.ajax({
		url:'<?= route('templateMaster.maxcerti')?>',
		type:'GET',
		data:{'id':user_id},
		success:function(response){
			if(response == 'success'){

				$('#template_id').val($template_id);

				if($('#previewPdfValue').val()==0){
					$('#previewPdf').click();
				}
				/*$('#previewPdf').val(1);
				$('#previewPdfValue').val(1);*/
						$('#uploadFile').modal('show');
				/*$.get('<?= route('templateMaster.map')?>',
				{'template_id':$template_id},
				function(data){
					if(data.is_mapped == 'excel'){
						$('#template_id').val($template_id);
						$('#uploadFile').modal('show');
					}
					else if(data.is_mapped == 'database') 
					{
						$temp = window.btoa($template_id);
						var url = '{{ route("template-map.index", ":template_id") }}';
						url = url.replace(':template_id', $template_id);
						window.location.href=url;
					}
					else
					{
						toastr["error"](data.message);
					}
				},'json');*/
			}else{
				bootbox.alert("Your printing limit is expired. Please contact Admin!");
			}
		}
	})
});
//on upload button click
$('#btn_updfile').click(function (event) {
	validateUpload();
});

function validateUpload() {
	var fd = new FormData();
	var files = $('[name="field_file"]')[0].files[0];
	var token = "<?= csrf_token()?>";
	fd.append('id',$('#template_id').val());
	fd.append('field_file',files);
	fd.append('_token',token);
	$.ajax({
		url:'<?= route('uasb-certificate.validateExcel')?>',
	    type: "POST",
		dataType: "JSON",
		data:fd,
		processData: false,
		contentType:false,
		async:false,
		success:function(resp){
			
			$('#field_file_error').text('')
			if(resp.success == false){
				if(resp.type == 'toaster'){
					toastr["error"](resp.message);
				}
				else{
					$('#field_file_error').text(resp.message)
				}
			}
			else
			{
				var previewPdf=$('#previewPdfValue').val();
				if(resp.old_rows == 0||previewPdf==1){
					uploadfile(is_progress = 'no',excel_row = 0);
					$('#btn_updfile_back').hide();
							$('#pdf_generate').hide();
							$('#btn_updfile').hide();
				}
				else{
					$('#duplicate_row_count').html('you have '+ resp.old_rows +' old records and '+resp.new_rows+' new records');
					$('#btn_updfile_back').show();
					$('#pdf_generate').show();
					$('#btn_updfile').hide();
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

function uploadfile(is_progress = 'no',excel_row = 0){
	$('#func').val('generateUploadfile');
	$('#pdf_generate').attr('disabled',true);
	$('#duplicate_row_count').empty();

	$('#downloadLink').html('<b>Please Wait your download will ready<b> <img src="/backend/images/loading.gif">');
	$('.close').attr('disabled',true);
	$('#btn_updfile_back').attr('disabled',true);
	$('#btn_updfile_back').hide();
	$('#pdf_generate').hide();
	$('#btn_updfile').hide();
	var formData = new FormData($("form#updfilefrm")[0]);
	
	var previewPdf=$('#previewPdfValue').val();
	if($("#previewWithoutBg").prop('checked') == true){
    var previewWithoutBg=1;
	}else{
	var previewWithoutBg=0;	
	}
	formData.append('previewPdf',previewPdf);
	formData.append('previewWithoutBg',previewWithoutBg);
	$.ajax({
		url: '<?= route('uasb-certificate.uploadfile')?>',
		data: formData, 		  // Data sent to server, a set of key/value pairs (i.e. form fields and values)
		contentType: false,       // The content type used when sending data to the server.
		cache: false,             // To unable request pages to be cached
		processData:false, 
		type: "POST",
		dataType:'json',
		success: function(response) {
			if(response.success == false){
				toastr["error"](response.message);
				setTimeout(function(){
					window.location = '<?= route('uasb-certificate.index')?>';
				},500)
			}
			else{
				$('#downloadLink').empty();
				$('#downloadLink').html("<b>Click <a href='"+response.link+"' class='downloadpdf' download target='_blank'>Here</a> to download file<b>");
						$('#downloadLink').removeAttr('style');
						$('#downloadLink').click(function(){
							$('#uploadFile').modal('hide');
							// form.reset();
							$('#downloadLink').empty();
							$('#duplicate_row_count').empty();
							$('#pdf_generate').removeAttr('disabled');
							$('#downloadLink').empty();
							$('.close').removeAttr('disabled');
							$('#btn_updfile_back').removeAttr('disabled');
							$('#btn_updfile_back').hide();
							$('#pdf_generate').hide();
							$('#btn_updfile').show();
							$('#field_file').val('');

							$('.pdf_progress').hide();
							$('.time_details').hide();
							$('.progress-bar').text('0% Complete');
							$('.progress-bar').css('width','0%')

						})

				/*if(response.type == 'formula'){
					var toString = response.cell.toString();	
					var columns = toString.split(',').join(' , ');
					$('#downloadLink').html(response.message+' '+columns);
					$('#downloadLink').removeAttr('style');
				}else if(response.type == 'fieldNotMatch'){
					$('#downloadLink').html(response.message);
					$('#field_file').val('');
					$('#downloadLink').css('color','red');
					$('#duplicate_row_count').empty();
					$('#pdf_generate').removeAttr('disabled');
					$('#btn_updfile_back').removeAttr('disabled');
					$('#btn_updfile_back').hide();
					$('#pdf_generate').hide();
					$('#btn_updfile').show();
				}else{	
					// console.log(response)
					// console.log('dasddsadasdsadsd');
					if(response.is_progress == 'yes'){
						var excelRow = parseInt(response.excel_row);
						var highestRow = parseInt(response.highestRow);
						console.log(highestRow);
						var per = 100/(highestRow -1)//get percentage calculation according to excel row divide by 100 
						var percentage_inner = per.toFixed(3);
						if(sessionStorage.getItem('percentage') != null){
							var per = parseFloat(sessionStorage.getItem('percentage')) + parseFloat(percentage_inner);
							// console.log(per)
						}
						console.log(per)
						var percentage = per.toFixed(3);
						var per = per.toFixed(2);
						$('.progress-bar').text(per+'% Complete');
						$('.progress-bar').css('width',per+'%')
						var current_count = (excelRow)-2;
						var total_count = highestRow-1;
							var display_count = current_count+'/'+total_count;
						$('.pdf_progress').show();
						//display count 
						$('.pdf_count').text(display_count)
						// sessionStorage.removeItem('percentage')

						sessionStorage.setItem('percentage',percentage);
						// console.log(excelRow)
						uploadfile(response.is_progress,excelRow)
					}
					else{

						console.log('dasd');
						var highestRow = parseInt(response.highestRow);
						$('.progress-bar').text('100% Complete');
						$('.progress-bar').css('width','100%')
						sessionStorage.removeItem('percentage');
						$('.progress').hide();

						//get generation start time
						var pdf_gen_start_date = $('.pdf_gen_start_time').val()
						var pdf_gen_start_timestamp = parseInt($('.pdf_gen_start_timestamp').val())
						var date_split = pdf_gen_start_date.split(' ')
						var pdf_generation_start_time = date_split[4];


						//get generation end time
						var pdf_end_date = new Date();
						$('.pdf_gen_end_time').val(pdf_end_date)
						var pdf_gen_end_time = $('.pdf_gen_end_time').val()
						var end_date_split = pdf_gen_end_time.split(' ')
						var pdf_generation_end_time = end_date_split[4];

						//display time
						$('.time_details').show();
						$('.start_time').text(pdf_generation_start_time)
						$('.end_time').text(pdf_generation_end_time)
						var time_diff =(pdf_end_date.getTime() - pdf_gen_start_timestamp) / 1000;
  				
	  					var get_time_diff = convertTime(time_diff)
  						var hours = get_time_diff.hour;
  						var mins = get_time_diff.minute;
  						var sec = get_time_diff.seconds;
  						$('.total_time').text(hours+' hrs '+mins+' mins '+sec+' sec')

  						// var avg_speed = time_diff/(highestRow-1);
  						// var get_total_time_diff = convertTime(avg_speed)
  						// var avg_hours = get_total_time_diff.hour;
  						// var avg_mins = get_total_time_diff.minute;
  						var total_time_sec = (hours*60*60)+(mins*60)+(sec)
  						console.log(total_time_sec)
  						var avg_sec = (total_time_sec)/(highestRow-1);
  						console.log(avg_sec)
  						var avg_time = avg_sec.toFixed(3);
  						$('.avg_time').text(avg_time+' sec')



  						// console.log(mins*60+sec)


						$('#downloadLink').html(response.msg);
						$('#downloadLink').removeAttr('style');
						$('#downloadLink').click(function(){
							$('#uploadFile').modal('hide');
							// form.reset();
							$('#downloadLink').empty();
							$('#duplicate_row_count').empty();
							$('#pdf_generate').removeAttr('disabled');
							$('#downloadLink').empty();
							$('.close').removeAttr('disabled');
							$('#btn_updfile_back').removeAttr('disabled');
							$('#btn_updfile_back').hide();
							$('#pdf_generate').hide();
							$('#btn_updfile').show();
							$('#field_file').val('');

							$('.pdf_progress').hide();
							$('.time_details').hide();
							$('.progress-bar').text('0% Complete');
							$('.progress-bar').css('width','0%')

						})
					}
				}*/
			}
		}
	}); 
}

function convertTime( milliseconds ) {
    var day, hour, minute, seconds;
    seconds = Math.floor(milliseconds);
    minute = Math.floor(seconds / 60);
    seconds = seconds % 60;
    hour = Math.floor(minute / 60);
    minute = minute % 60;
    day = Math.floor(hour / 24);
    hour = hour % 24;
    return {
        day: day,
        hour: hour,
        minute: minute,
        seconds: seconds
    };
}

$('#pdf_generate').click(function(){
	sessionStorage.removeItem('percentage')
	uploadfile(is_progress = 'no',excel_row = 0)
})



//for destroying session that is created during save in create
sessionStorage.removeItem('template_id')
</script>	
@stop
