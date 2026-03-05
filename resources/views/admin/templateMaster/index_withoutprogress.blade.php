@extends('admin.layout.layout')
@section('content')
	<div class="container">
		<div class="col-xs-12">
			<div class="clearfix">
				<form method="post" id="templfrm">
				<!-- <form method="post" id="templfrm"  action="add_template.php"> -->
					<input type="hidden" name="edit" id="edit_id">
				</form>
				<div class="modal fade" id="uploadFile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="myModalLabel">Upload excel</h4>
							</div>
							<div class="modal-body">						
								<form method="post" action="<?=route('template-master.index')?>" enctype="multipart/form-data" id="updfilefrm">	
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
								<h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i> Template Master
								<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
								</h1>
							</div>
						</div>
						<div class="">
							<ul class="nav nav-pills" id="pills-filter">
							  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Templates </a></li>
							  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Templates</a></li>
							  
							 
							  	<li style="float: right;">
									<!-- <button class="btn btn-theme" id="report"><i class="fa fa-file"></i> Generate Report</button>	 -->
									<a style="background: #0052CC;padding-top:5px; color: #fff;border-radius: 2px;box-shadow: 2px 2px 5px #999;border: 1px solid transparent;height: 35px;" href="../functions/excelReport.php"  id="report" ><i class="fa fa-file"></i> Generate Report</a>	
								</li>
								<li style="float: right;">
									<button class="btn btn-theme" id="addtemplate"><i class="fa fa-plus"></i> Add Template</button>	
								</li>
							</ul>
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
@stop



@section('script')
<script type="text/javascript">
	
    function copyTemplate(id){

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
            	
            	buttons = '<a href="'+edit_url+'"><span style="cursor:pointer"><i title="Edit" data-id="'+o['id']+'" class="editrow fa fa-edit fa-lg green"></i> </span></a>';

                buttons += ' <span style="cursor:pointer" onclick="copyTemplate('+o['id']+')" ><i title="Copy Template" class="copyrow fa fa-copy fa-lg yellow"></i></span>';

                buttons += '<a href="'+map_url+'"> <span style="cursor:pointer"><i title="Map Template" data-id="'+o['id']+'" class="maprow fa fa-map-marker fa-lg black"></i></span></a>';
                 buttons += ' <span style="cursor:pointer"><i title="Generate PDF" data-id="'+o['id']+'" class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i></span>';   
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
						var url = '{{ route("template-map.index", ":template_id") }}';
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
								uploadfile(flag = 0)
							}
							else{
								$('#duplicate_row_count').html('you have '+ data.old_rows +' old records');
								$('#btn_updfile_back').show();
								$('#pdf_generate').show();
								$('#btn_updfile').hide();
							}
						}
						else{
							uploadfile(flag = 0)
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

function uploadfile(flag = 0){
	$('#pdf_generate').attr('disabled',true);
	$('#duplicate_row_count').empty();

	$('#downloadLink').html('<b>Please Wait your download will ready<b>');
	$('.close').attr('disabled',true);
	$('#btn_updfile_back').attr('disabled',true);
	var formData = new FormData($("form#updfilefrm")[0]);
	if(flag == 1){
		formData.append('flag',1);
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
					if(response.flag == 1){
						uploadfile(response.flag)
						toastr["success"](response.message);
						setTimeout(function(){
							window.location = '<?= route('template-master.index')?>';
						},500)
					}

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

					})
				}
			}
		}
	}); 
}

$('#pdf_generate').click(function(){
	uploadfile(flag = 0)
})

//for destroying session that is created during save in create
sessionStorage.removeItem('template_id')
</script>	
@stop
