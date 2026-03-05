<?php $__env->startSection('content'); ?>
	<div class="container">
		<div class="col-xs-12">
			<div class="clearfix">
				<form method="post" id="templfrm">
				
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

				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i> Generate Degree Certificate
								<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('templatemangement')); ?></ol>
								</h1>
							</div>
						</div>
						<div class="">	
							<table id="example" class="table table-hover display" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>#</th>
										<th>Template Name</th>
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

	var oTable = $('#example').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [2, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('degree-certifiate.index',['status'=>1])?>",
        "aoColumns":[
		{mData: "rownum", bSortable:false},
		{mData: "actual_template_name"},
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
                 buttons = '<?php if(App\Helpers\SitePermissionCheck::isPermitted('template-master.uplodafile')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.uplodafile')): ?> <span style="cursor:pointer"><i title="Generate PDF" data-id="'+o['id']+'" class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i></span> <?php endif; ?> <?php endif; ?>';   
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
		url:'<?= route('degreeCertificate.maxcerti')?>',
		type:'GET',
		data:{'id':user_id},
		success:function(response){
			if(response == 'success'){
				$('#template_id').val($template_id);
				$('#uploadFile').modal('show');
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

	fd.append('field_file',files);
	fd.append('_token',token);
	$.ajax({
		url:'<?= route('degreeCertificate.validation')?>',
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
					url:'<?= route('degreeCertificate.check')?>',
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
		url: '<?= route('degree-certifiate.uploadfile')?>',
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
					window.location = '<?= route('degree-certifiate.index')?>';
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
					
					if(response.is_progress == 'yes'){
						var excelRow = parseInt(response.excel_row);
						var highestRow = parseInt(response.highestRow);
						console.log(excelRow-2);
						var per = 100/(highestRow -1)//get percentage calculation according to excel row divide by 100 
						
						var per = (per) * (excelRow - 2);
						
						console.log(per)
						
						var per = per.toFixed(2);
						$('.progress-bar').text(per+'% Complete');
						$('.progress-bar').css('width',per+'%')
						var current_count = (excelRow)-2;
						var total_count = highestRow-1;
							var display_count = current_count+'/'+total_count;
						$('.pdf_progress').show();
						//display count 
						$('.pdf_count').text(display_count)
					
						uploadfile(response.is_progress,excelRow)
					}
					else{

						console.log('dasd');
						var highestRow = parseInt(response.highestRow);
						$('.progress-bar').text('100% Complete');
						$('.progress-bar').css('width','100%')
						
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

  						var total_time_sec = (hours*60*60)+(mins*60)+(sec)
  						console.log(total_time_sec)
  						console.log(highestRow)
  						var avg_sec = (total_time_sec)/(highestRow-1);
  						console.log(avg_sec)
  						var avg_time = avg_sec.toFixed(3);
  						$('.avg_time').text(avg_time+' sec')


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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/degreecertificate/index.blade.php ENDPATH**/ ?>