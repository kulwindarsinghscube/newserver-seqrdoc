<?php $__env->startSection('style'); ?>

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
nput:checked + .slider {
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
<?php 
$domain = \Request::getHost();
$subdomain = explode('.', $domain);
?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
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
									<p>Select option: <label id="toggleText"> <span style="color: red;margin-left: 5px;    margin-top: 10px;">Generating preview pdf.</span></label></p>
									<label class="switch">  
									<input type="checkbox" class="form-control" id="pdf_option" name="pdf_option" value="1" checked data-toggle="toggle" data-on="Live" data-off="Preview Only" data-onstyle="success" data-offstyle="danger" data-width="100" data-height="75">
	                            <span class="slider round"></span>
	           					</label>
	           					
								<input type="hidden" name="pdf_local_option" value="1" id="pdf_local_option">
									<div class="form-group">
										<label>Upload File</label>
										<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
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
										<li>Ensure that all fields are mapped.</li>
										<li>Ensure that all fonts used in templates master are available.</li>
										<li>Ensure that all columns mapped in the template master are available with the same name in your current uploading excel sheet.</li>
										<li>Ensure that the serial no in excel file is unique across all data.</li>
										<li>Name are case sensitive, column sequence insensitive, extra columns are ignored.</li>
										<li>If the unique serial number is repeated or found duplicate in the excel data, then it will replace the already existing data (making them inactive), with the current excel data.</li>
										<li>Accepted file format XLS or XLSX.</li>
										<li>Max file size 10 MB</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- dic modal start -->
				<div class="modal fade" id="dic_uploadFile" tabindex="-1" role="dialog" aria-labelledby="dic_myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="dic_myModalLabel">Upload excel</h4>
							</div>
							<div class="modal-body">

								<h4 id="dic_sandboxing_message" class="text-center" style="color: red;"><b>Under Sandboxing environment</b></h5>						
								<form method="post" action="<?=route('template-master.index')?>" enctype="multipart/form-data" id="dicupdfilefrm">	
									<p>Select option: <label id="dic_toggleText"> <span style="color: red;margin-left: 5px;    margin-top: 10px;">Generating preview pdf.</span></label></p>
									<label class="switch">  
									<input type="checkbox" class="form-control" id="dic_pdf_option" name="dic_pdf_option" value="1" checked data-toggle="toggle" data-on="Live" data-off="Preview Only" data-onstyle="success" data-offstyle="danger" data-width="100" data-height="75">
	                            <span class="slider round"></span>
	           					</label>
	           					
								<input type="hidden" name="dic_pdf_local_option" value="1" id="dic_pdf_local_option">
									<div class="form-group">
										<label>Upload File</label>
										<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
										<input type="file" class="form-control" id="dic_field_file" name="dic_field_file">
										<span id="dic_field_file_error" class="help-inline text-danger"></span>
										<input type="hidden" name="id" id="dic_template_id">
										<input type="hidden" name="func" id="dic_func" value="generateUploadfile">
										<input type="hidden" name="print_type" value="pdf">
									</div>
									<div id="dic_downloadLink">
									</div>
									<div class="form-group clearfix">
										<div id="dic_duplicate_row_count" class=""></div>
										<div id="dic_upload_btn">
											<a href="#" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-7" id="dic_btn_updfile_back" onclick="closeModel();" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
											<button type="button" class="btn btn-theme" value="pdf_generate" id="dic_pdf_generate" name="pdf_generate" style="display: none"><i class="fa fa-upload" ></i>PDF Generate</button>
											<button type="button" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="dic_btn_updfile"><i class="fa fa-upload"></i> Upload</button>
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
										<li>Ensure that all fields are mapped.</li>
										<li>Ensure that all fonts used in templates master are available.</li>
										<li>Ensure that all columns mapped in the template master are available with the same name in your current uploading excel sheet.</li>
										<li>Ensure that the serial no in excel file is unique across all data.</li>
										<li>Name are case sensitive, column sequence insensitive, extra columns are ignored.</li>
										<li>If the unique serial number is repeated or found duplicate in the excel data, then it will replace the already existing data (making them inactive), with the current excel data.</li>
										<li>Accepted file format XLS or XLSX.</li>
										<li>Max file size 10 MB</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- dic modal end -->
				<div class="modal fade" id="uploadSamplePdf" tabindex="-1" role="dialog" aria-labelledby="uploadSamplePdfLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="uploadSamplePdfLabel">Upload Sample/Design PDF</h4>
							</div>
							<div class="modal-body">
								<form method="post" action="" enctype="multipart/form-data" id="uploadSamplePdfForm">
									<div class="form-group">
										<label>Upload PDF File</label>
										<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
										<input type="hidden" name="template_id" id="sample_pdf_template_id" value="">
										<input type="file" class="form-control" id="sample_pdf" name="sample_pdf" accept=".pdf">
										<span id="pdf_file_error" class="help-inline text-danger"></span>
									</div>
				
									<div class="form-group">
										<div class="row align-items-center">
											<!-- Column for download link -->
											<div class="col-md-9 col-sm-12 mb-2 mb-md-0" id="downloadLinkSample">
												<!-- This will be populated dynamically -->
											</div>
											<!-- Column for upload button -->
											<div class="col-md-3 col-sm-12 text-right">
												<button type="button" class="btn btn-theme" id="btn_upload_sample_pdf">
													<i class="fa fa-upload"></i> Upload
												</button>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>


				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i> Template Master
								<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('templatemangement')); ?></ol>
								<i class="fa fa-info-circle iconModalCss" title="User Manual" id="templateManagementClick"></i>
								</h1>
							</div>
						</div>
						<div class="">
							<ul class="nav nav-pills" id="pills-filter">
							  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Templates </a></li>
							  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Templates</a></li>
							  
							 
							  	<li style="float: right;">
									<!-- <button class="btn btn-theme" id="report"><i class="fa fa-file"></i> Generate Report</button>	 -->
									<a style="background: #0052CC;padding-top:5px; color: #fff;border-radius: 2px;box-shadow: 2px 2px 5px #999;border: 1px solid transparent;height: 35px;" href="<?=route('template-master.excelreport')?>"  id="report" ><i class="fa fa-file"></i> Generate Report</a>	
								</li>
							   <?php if(App\Helpers\SitePermissionCheck::isPermitted('template-master.create')): ?>
							   <?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.create')): ?>	
							   <?php if($is_disable): ?>	
								<li style="float: right;">
									<button class="btn btn-theme" id="addtemplate"><i class="fa fa-plus"></i> Add Template</button>	
								</li>
								<?php endif; ?>
								<?php endif; ?>
								<?php endif; ?>
							</ul>
							<?php
							$domain =$_SERVER['HTTP_HOST'];
							$subdomain = explode('.', $domain);
							?>
								<table id="example" class="table table-hover display" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>#</th>
										<th>Template Name</th>
										<?php if($subdomain[0]=="demo"){ ?>
										<th>Contract Address</th>
										<?php } ?>
										<th>Action</th>
									</tr>
								</thead>
								<tfoot>
								</tfoot>
							</table>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
<script>
    var subdomain='<?php echo $subdomain[0];?>';
    console.log('<?php echo $subdomain[0];?>');	
	<?php if($subdomain[0]=="mock"){ ?>
		$('#pdf_local_option').val(0);
		$('#pdf_option').val(0);
		$("#pdf_option").removeAttr('checked');
		$('#toggleText').html('<span style="color: green;margin-left: 5px;    margin-top: 10px;">Generating live pdf.</span>');
		$('.switch').hide();	
	<?php } ?>	
	$('#pdf_option').on('change',function(){

		var value = $(this).val();
		var option = 0;
		if(value == 1){
			 $(this).val(0);
			 $('#toggleText').html('<span style="color: green;margin-left: 5px;    margin-top: 10px;">Generating live pdf.</span>');
		}else{
			$(this).val(1);
			$('#toggleText').html('<span style="color: red;margin-left: 5px;    margin-top: 10px;">Generating preview pdf.</span>');
		}
		var val = $('#pdf_option').val();
		$('#pdf_local_option').val(val);
	});
		$('#dic_pdf_option').on('change',function(){

		var value = $(this).val();
		var option = 0;
		if(value == 1){
			 $(this).val(0);
			 $('#dic_toggleText').html('<span style="color: green;margin-left: 5px;    margin-top: 10px;">Generating live pdf.</span>');
		}else{
			$(this).val(1);
			$('#dic_toggleText').html('<span style="color: red;margin-left: 5px;    margin-top: 10px;">Generating preview pdf.</span>');
		}
		var val = $('#dic_pdf_option').val();
		$('#dic_pdf_local_option').val(val);
	});
</script>
<script type="text/javascript">
	
	var subdomain='<?php echo $subdomain[0];?>';
    

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
	$.ajax({
		type:'get',
		url : "<?=route('dictemplateMaster.check-sandbox')?>",
		dataType:'json',
		success:function(response){
			if(response.sandboxing == 1){
				// $('#sandboxing_message').text('');
	        }else{

				$('#dic_sandboxing_message').text('');
	        }
		}
	});
    function copyTemplate(id){

    	var url="<?php echo e(URL::route('template-master.copyTemplate.copy')); ?>";
    	var token="<?php echo e(csrf_token()); ?>";
    	var method_type="post";
        bootbox.confirm("Are you sure you want to copy?",function(result){	
          if(result){
    	      $.post(url,{'_token':token,'template_id':id}, function(data) {
    	      	if(data.success==true){
                  toastr.success(data.msg);
                  oTable.ajax.reload();
    	      	}else{
                  toastr.error(data.msg);

    	      	}
    	    });
          }
        });
    }
	var oTable = $('#example').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [2, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('template-master.index',['status'=>1])?>",
        "aoColumns":[
		{mData: "rownum", bSortable:false},
		{mData: "actual_template_name"},
		<?php if($subdomain[0]=="demo"){ ?> 
		{mData: "bc_contract_address"},
		<?php } ?>
		{mData: 'id',
            bSortable: false,
            sWidth: "30%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            	var edit_url = "<?php echo e(route('template-master.edit',':id')); ?>"
            	edit_url = edit_url.replace(':id',o['id']);

            	var map_url =  "<?php echo e(route('template-master.template-map.edit',':id')); ?>"
            	map_url = map_url.replace(':id',o['id']);
            	var buttons = '';
            	
            	buttons = '<?php if(App\Helpers\SitePermissionCheck::isPermitted('template-master.edit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.edit')): ?> <a href="'+edit_url+'"><span style="cursor:pointer"><i title="Edit" data-id="'+o['id']+'" class="editrow fa fa-edit fa-lg green"></i> </span></a> <?php endif; ?> <?php endif; ?>';
            	//console.log("<?php echo App\Helpers\RolePermissionCheck::isPermitted('template-master.copyTemplate.copy') ?>");
                buttons += '<?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.copyTemplate.copy')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.copyTemplate.copy')): ?> <span style="cursor:pointer" onclick="copyTemplate('+o['id']+')" ><i title="Copy Template" class="copyrow fa fa-copy fa-lg yellow"></i></span> <?php endif; ?> <?php endif; ?>';

                buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('template-master.template-map.edit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.template-map.edit')): ?> <a href="'+map_url+'"> <span style="cursor:pointer"><i title="Map Template" data-id="'+o['id']+'" class="maprow fa fa-map-marker fa-lg black"></i></span></a> <?php endif; ?> <?php endif; ?>';
                 buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('template-master.uplodafile')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.uplodafile')): ?> <span style="cursor:pointer"><i title="Generate PDF" data-id="'+o['id']+'" class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i></span> <?php endif; ?> <?php endif; ?>';
				
				 if (subdomain == "tpsdi") {
					buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('template-master.uplodafile')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.uplodafile')): ?> <span style="cursor:pointer"><i title="Generate Certificate With Data Mapping" data-id="'+o['id']+'" class="dicpdfGenerate fa fa-file-pdf-o fa-lg red"></i></span> <?php endif; ?> <?php endif; ?>';
				}
   
				return buttons;
            },
         		   	
        },
	],
	"createdRow": function( row, data, dataIndex ) {

		if(data['status'] == 'Active'){
			$(row).addClass( 'active-student' );
		}else{
			$(row).addClass( 'inactive-student' );
		}
	}
});
oTable.on('draw', function () {
	$('[title]').tooltip(); 
});
$('#success-pill').click(function(){

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
});
$('#field_file').change(function(){

	$('#func').val('checkExcel');	
	$('#btn_updfile_back').hide();
	$('#pdf_generate').hide();
	$('#btn_updfile').show();
});
$('#dic_field_file') . change(function () {

    $('#dic_func') . val('checkExcel');
    $('#dic_btn_updfile_back') . hide();
    $('#dic_pdf_generate') . hide();
    $('#dic_btn_updfile') . show();
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

				$.get('<?= route('templateMaster.map')?>',
				{'template_id':$template_id},
				function(data){
					if(data.is_mapped == 'excel'){
						$('#template_id').val($template_id);
						$('#uploadFile').modal('show');
					}
					else if(data.is_mapped == 'database') 
					{
						$temp = window.btoa($template_id);
						var url = '<?php echo e(route("template-map.index", ":template_id")); ?>';
						url = url.replace(':template_id', $template_id);
						window.location.href=url;
					}
					else
					{
						toastr["error"](data.message);
					}
				},'json');
			}else{
				bootbox.alert("Your printing limit is expired. Please contact Admin!");
			}
		}
	})
});

		//on Digital id card generate pdf click
	oTable.on('click','.dicpdfGenerate',function(e){
		$template_id = $(this).data('id');
		var user_id = "<?= Auth::guard('admin')->user()->id?>"
		$.ajax({
			url:'<?= route('dictemplateMaster.maxcerti')?>',
			type:'GET',
			data:{'id':user_id},
			success:function(response){
				if(response == 'success'){

					$.get('<?= route('dictemplateMaster.map')?>',
					{'template_id':$template_id},
					function(data){
						if(data.is_mapped == 'excel'){
							$('#dic_template_id').val($template_id);
							$('#dic_uploadFile').modal('show');
						}
						else if(data.is_mapped == 'database') 
						{
							$temp = window.btoa($template_id);
							var url = '<?php echo e(route("dictemplate-map.index", ":template_id")); ?>';
							url = url.replace(':template_id', $template_id);
							window.location.href=url;
						}
						else
						{
							toastr["error"](data.message);
						}
					},'json');
				}else{
					bootbox.alert("Your printing limit is expired. Please contact Admin!");
				}
			}
		})
	});
	$('#dic_btn_updfile').click(function (event) {
		validateDICUpload();
	});
	//on upload button click
	$('#btn_updfile').click(function (event) {
		validateUpload();
	});

function validateUpload() {
	var fd = new FormData();
	var files = $('[name="field_file"]')[0].files[0];
	var token = "<?= csrf_token()?>";

		fd.append('field_file',files);
		fd.append('_token',token);
		$.ajax({
			url:'<?= route('excel.validation')?>',
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
					var formData = new FormData($("form#updfilefrm")[0]);
					$.ajax({
						url:'<?= route('excel.check')?>',
						data:formData,
						type: "POST",
						contentType: false,       // The content type used when sending data to the server.
						cache: false,             // To unable request pages to be cached
						processData:false,
						dataType:'json', 
						success:function(data){
							if(data.type == 'duplicate'){
								if(data.old_rows == 0){
									uploadfile(is_progress = 'no',excel_row = 0)
								}
								else{
									$('#duplicate_row_count').html('you have '+ data.old_rows +' old records and '+data.new_rows+' new records');
									$('#btn_updfile_back').show();
									$('#pdf_generate').show();
									$('#btn_updfile').hide();
								}
							}else if(data.type=='error'){
								bootbox.alert(data.message);
							}
							else{
								uploadfile(is_progress = 'no',excel_row = 0)
							}
						}
					})
				}
			},
			error:function(resp){
				if(resp.responseJSON != undefined){
					$('#field_file_error').text(resp.responseJSON.errors.field_file[0])
				}
			}
		});
	} 
	function validateDICUpload() {
		var fd = new FormData();
		var files = $('[name="dic_field_file"]')[0].files[0];
		var token = "<?= csrf_token()?>";

		fd.append('field_file',files);
		fd.append('_token',token);
		$.ajax({
			url:'<?= route('dicexcel.validation')?>',
			type: "POST",
			dataType: "JSON",
			data:fd,
			processData: false,
			contentType:false,
			async:false,
			success:function(resp){
				
				$('#dic_field_file_error').text('')
				if(resp.success == false){
					if(resp.type == 'toaster'){
						toastr["error"](resp.message);
					}
					else{
						$('#dic_field_file_error').text(resp.message)
					}
				}
				else
				{
					var formData = new FormData($("form#dicupdfilefrm")[0]);
					$.ajax({
						url:'<?= route('dicexcel.check')?>',
						data:formData,
						type: "POST",
						contentType: false,       // The content type used when sending data to the server.
						cache: false,             // To unable request pages to be cached
						processData:false,
						dataType:'json', 
						success:function(data){
							if(data.type == 'duplicate'){
								if(data.old_rows == 0){
									uploadfiledic(is_progress = 'no',excel_row = 0)
								}
								else{
									$('#dic_duplicate_row_count').html('you have '+ data.old_rows +' old records and '+data.new_rows+' new records');
									$('#dic_btn_updfile_back').show();
									$('#dic_pdf_generate').show();
									$('#dic_btn_updfile').hide();
								}
							}else if(data.type=='error'){
								bootbox.alert(data.message);
							}
							else{
								uploadfiledic(is_progress = 'no',excel_row = 0)
							}
						}
					})
				}
			},
			error:function(resp){
				if(resp.responseJSON != undefined){
					$('#dic_field_file_error').text(resp.responseJSON.errors.field_file[0])
				}
			}
		});
	} 

function uploadfile(is_progress = 'no',excel_row = 0){
	$('#pdf_generate').attr('disabled',true);
	$('#duplicate_row_count').empty();

	$('#downloadLink').html('<b>Please Wait your download will ready<b>');
	$('.close').attr('disabled',true);
	$('#btn_updfile_back').attr('disabled',true);
	var formData = new FormData($("form#updfilefrm")[0]);
	if(is_progress == 'yes'){
		formData.append('is_progress','yes');
		formData.append('excel_row',excel_row);
	}
	else{
		$('.progress').show();
		$('.progress-bar').text('1% Complete');
		$('.progress-bar').css('width','1%')
		//store generation start time
		var current_date = new Date();
		var current_time = current_date.getTime();
		$('.pdf_gen_start_time').val(current_date)
		$('.pdf_gen_start_timestamp').val(current_time)
	}
	$.ajax({
		url: '<?= route('template-master.uplodafile')?>',
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
					window.location = '<?= route('template-master.index')?>';
				},500)
			}
			else{
				$('#downloadLink').empty();
				if(response.type == 'formula'){
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
						console.log(excelRow-2);
						var per = 100/(highestRow -1)//get percentage calculation according to excel row divide by 100 
						console.log(per);
						// var percentage_inner = per.toFixed(3);//old
						// if(sessionStorage.getItem('percentage') != null){//old
						var per = (per) * (excelRow - 2);
						// var per = parseFloat(sessionStorage.getItem('percentage')) + parseFloat(per);//old
							// console.log(per)
						// }
						console.log(per)
						// var percentage = per.toFixed(3);//old
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

						// sessionStorage.setItem('percentage',percentage);//old
						// console.log(excelRow)
						uploadfile(response.is_progress,excelRow)
					}
					else{

						console.log('dasd');
						var highestRow = parseInt(response.highestRow);
						$('.progress-bar').text('100% Complete');
						$('.progress-bar').css('width','100%')
						// sessionStorage.removeItem('percentage');//old
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
				}
			}
		}
	}); 
}

	function uploadfiledic(is_progress = 'no',excel_row = 0){
		$('#dic_pdf_generate').attr('disabled',true);
		$('#dic_duplicate_row_count').empty();

		$('#dic_downloadLink').html('<b>Please Wait your download will ready<b>');
		$('.close').attr('disabled',true);
		$('#dic_btn_updfile_back').attr('disabled',true);
		var formData = new FormData($("form#dicupdfilefrm")[0]);
		// Get the existing file
		var file = formData.get('dic_field_file');

		// Remove old key
		formData.delete('dic_field_file');

		// Add new key with same file
		formData.append('field_file', file);

		if(is_progress == 'yes'){
			formData.append('is_progress','yes');
			formData.append('excel_row',excel_row);
		}
		else{
			$('.progress').show();
			$('.progress-bar').text('1% Complete');
			$('.progress-bar').css('width','1%')
			//store generation start time
			var current_date = new Date();
			var current_time = current_date.getTime();
			$('.pdf_gen_start_time').val(current_date)
			$('.pdf_gen_start_timestamp').val(current_time)
		}
		$.ajax({
			url: '<?= route('dictemplate-master.uplodafile')?>',
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
						//window.location = '<?= route('template-master.index')?>';
					},500)
				}
				else{
					$('#dic_downloadLink').empty();
					if(response.type == 'formula'){
						var toString = response.cell.toString();	
						var columns = toString.split(',').join(' , ');
						$('#dic_downloadLink').html(response.message+' '+columns);
						$('#dic_downloadLink').removeAttr('style');
					}else if(response.type == 'fieldNotMatch'){
						$('#dic_downloadLink').html(response.message);
						$('#dic_field_file').val('');
						$('#dic_downloadLink').css('color','red');
						$('#dic_duplicate_row_count').empty();
						$('#dic_pdf_generate').removeAttr('disabled');
						$('#dic_btn_updfile_back').removeAttr('disabled');
						$('#dic_btn_updfile_back').hide();
						$('#dic_pdf_generate').hide();
						$('#dic_btn_updfile').show();
					}else{	
						// console.log(response)
						// console.log('dasddsadasdsadsd');
						if(response.is_progress == 'yes'){
							var excelRow = parseInt(response.excel_row);
							var highestRow = parseInt(response.highestRow);
							console.log(excelRow-2);
							var per = 100/(highestRow -1)//get percentage calculation according to excel row divide by 100 
							console.log(per);
							// var percentage_inner = per.toFixed(3);//old
							// if(sessionStorage.getItem('percentage') != null){//old
							var per = (per) * (excelRow - 2);
							// var per = parseFloat(sessionStorage.getItem('percentage')) + parseFloat(per);//old
								// console.log(per)
							// }
							console.log(per)
							// var percentage = per.toFixed(3);//old
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

							// sessionStorage.setItem('percentage',percentage);//old
							// console.log(excelRow)
							uploadfiledic(response.is_progress,excelRow)
						}
						else{

							console.log('dasd');
							var highestRow = parseInt(response.highestRow);
							$('.progress-bar').text('100% Complete');
							$('.progress-bar').css('width','100%')
							// sessionStorage.removeItem('percentage');//old
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


							$('#dic_downloadLink').html(response.msg);
							$('#dic_downloadLink').removeAttr('style');
							$('#dic_downloadLink').click(function(){
								$('#dic_uploadFile').modal('hide');
								// form.reset();
								$('#dic_downloadLink').empty();
								$('#dic_duplicate_row_count').empty();
								$('#dic_pdf_generate').removeAttr('disabled');
								$('#dic_downloadLink').empty();
								$('.close').removeAttr('disabled');
								$('#dic_btn_updfile_back').removeAttr('disabled');
								$('#dic_btn_updfile_back').hide();
								$('#dic_pdf_generate').hide();
								$('#dic_btn_updfile').show();
								$('#dic_field_file').val('');

								$('.pdf_progress').hide();
								$('.time_details').hide();
								$('.progress-bar').text('0% Complete');
								$('.progress-bar').css('width','0%')

							})
						}
					}
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
	});
	$('#dic_pdf_generate').click(function(){
		sessionStorage . removeItem('percentage');
		uploadfiledic(is_progress = 'no', excel_row = 0);
	});




//for destroying session that is created during save in create
sessionStorage.removeItem('template_id')
</script>	
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/templateMaster/index.blade.php ENDPATH**/ ?>