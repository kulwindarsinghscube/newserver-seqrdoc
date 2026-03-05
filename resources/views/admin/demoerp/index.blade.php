@extends('admin.layout.layoutnonheader')
@section('content')
	<div class="container">
		<div class="col-xs-12">
			<div class="clearfix">
			
				<div class="modal fade" id="apiResponse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="myModalLabel">API Call # <span id="heading_request_id"></span></h4>
							</div>
							<div class="modal-body" style="    word-break: break-all;">
								<ul style="list-style-type:none;">
									<li style="padding-bottom: 10px;"><strong>Request Url</strong> : <div id="request_url"></div></li>
									<li style="padding-bottom: 10px;"><strong>Request Method</strong> : <div id="request_method"></div></li>
									<li style="padding-bottom: 10px;"><strong>Header Parameters</strong> : <pre id="header_parameters"></pre></li>
									<li style="padding-bottom: 10px;"><strong>Request Parameters</strong> : <pre id="request_parameters"></pre></li>
									<li style="padding-bottom: 10px;"><strong>Response</strong> : <pre id="response_parameters"></pre></li>	
									<li style="padding-bottom: 10px;"><strong>Status</strong> : <div id="status"></div></li>	
									<li style="padding-bottom: 10px;"><strong>Request Timestamp</strong> : <div id="created"></div></li>
									<li style="padding-bottom: 10px;"><strong>Response Timestamp</strong> : <div id="response_date_time"></div></li>
									<li style="padding-bottom: 10px;"><strong>Response Time</strong> : <div id="response_time"></div></li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-laptop fa fa-file-o"></i> Demo ERP
								
								</h1>
								<h4 class="refresh-table" style="max-width:100px;top: 45px;color: rgb(0 0 205);right: 30px;cursor: pointer;"  title="Refresh Table"><i class="fa fa-refresh fa fa-file-o"></i> Refresh</h4>
							</div>
							
						</div>
						<div class="">
							
							<table id="example" class="table table-hover display" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>Unique Id</th>
										<th>Student Name</th>
										<th>Mother Name</th>
										<th>Degree Type</th>
										<th>Degree Name</th>
										<th>Passing Year</th>
										<th>CGPA</th>
										<th>Status</th>	
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
	

  
	var oTable = $('#example').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [0, "asc"]
        ],
        "sAjaxSource":"<?= URL::route('demo-erp.index')?>",
        "aoColumns":[
		{mData: "unique_id"},
		{mData: "student_name"},
		{mData: "mother_name"},
		{mData: "degree_type"},
		{mData: "degree_name"},
		{mData: "passing_year"},
		{mData: "cgpa"},
		{mData: 'status',
            sClass: "text-center",
            mRender: function(v, t, o) {
            var status = '';
           
            if(o['status']=="Generate"){

            	status='-';
            }else if(o['status']=="Awaiting"){
            	status='Pending';
            }else if(o['status']=="Error"){
            	status='<span class="red">Error</span>';
            }else{
            	status='Generated';
            }
				return status;
            },
         		   	
        },
		{mData: 'unique_id',
            bSortable: false,
            sWidth: "30%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            var buttons = '';
           
            if(o['status']=="Generate"){

            	buttons = '<span  style="cursor:pointer"><i title="Generate Certificate" data-id="'+o['id']+'" class="generatepdf fa fa-paper-plane fa-lg green"></i> </span>';
            }else if(o['status']=="Error"){
            	buttons = '<span  style="cursor:pointer"><i title="Generate Api Call Details" data-id="'+o['id']+'" data-api_type="generate_api" class="apicall fa fa-info-circle fa-lg blue"></i>&nbsp;&nbsp;<span class="generatepdf" title="Regenerate Certificate" data-id="'+o['id']+'" style="cursor:pointer"><i  class="fa fa-repeat fa-lg green"></i></span>';
            }else if(o['status']=="Awaiting"||o['status']=="Error"){
            	buttons = '<span style="cursor:pointer; color: rgb(165 42 42);font-weight: 600;">Awaiting</span>&nbsp;&nbsp;<i title="Generate Api Call Details" data-id="'+o['id']+'" data-api_type="generate_api" class="apicall fa fa-info-circle fa-lg blue"></i>';
            }else{
            	buttons = '<span style="cursor:pointer"><a href="'+o['printable_pdf_link']+'" target="_blank" download><i class="fa fa-download" aria-hidden="true"></i></a>&nbsp;&nbsp;<i title="Generate Api Call Details" data-id="'+o['id']+'" data-api_type="generate_api" class="apicall fa fa-info-circle fa-lg blue"></i>&nbsp;&nbsp;<i title="Call Back Api Call Details" data-id="'+o['id']+'" data-api_type="call_back_api" class="apicall fa fa-history fa-lg blue"></i>&nbsp;&nbsp;<span class="generatepdf" title="Regenerate Certificate" data-id="'+o['id']+'" style="cursor:pointer"><i  class="fa fa-repeat fa-lg green"></i> </span>';

            	
            }
            	
            	
        
				return buttons;
            },
         		   	
        },
	]
});
oTable.on('draw', function () {
	$('[title]').tooltip(); 
});


$('.refresh-table').click(function(){
oTable.ajax.reload();
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
oTable.on('click','.generatepdf',function(e){

	
	$(this).parent().html('<span style="cursor:pointer; color: rgb(165 42 42);font-weight: 600;">Awaiting</span>');
	$unique_id = $(this).data('id');
	var token = "<?= csrf_token()?>";
	$.ajax({
		url:'<?= route('demo-erp.generate')?>',
		type:'POST',
		data:{'id':$unique_id,'_token':token},
		success:function(response){
			if(response.status == true){

				toastr["success"](response.message);

			}else{
				toastr["error"](response.message);
			}
			oTable.ajax.reload();
		}
	})
});

//on generate pdf click
oTable.on('click','.apicall',function(e){
	$unique_id = $(this).data('id');
	$api_type = $(this).data('api_type');
	var token = "<?= csrf_token()?>";
	$.ajax({
		url:'<?= route('demo-erp.apicall')?>',
		type:'POST',
		data:{'id':$unique_id,'api_type':$api_type,'_token':token},
		success:function(response){
			if(response.status == 200){
				$('#apiResponse').modal('show');
				$('#request_url').html(response.apiData.request_url);
				$('#request_method').html(response.apiData.request_method);

				
				if($api_type=="generate_api"){
					var resp=JSON.parse(response.apiData.response_parameters);
					if(resp.status==200){
						
						var request_id =resp.data.request_id;
					}else{
						var request_id ="ERROR";
					}
					
				}else{
				var resp=JSON.parse(response.apiData.request_parameters);
				var request_id =resp.request_id;	
				}
				
				$('#heading_request_id').html(request_id);
				$('#header_parameters').html(JSON.stringify(JSON.parse(response.apiData.header_parameters), null, '\t'));
				$('#request_parameters').html(JSON.stringify(JSON.parse(response.apiData.request_parameters), null, '\t'));
				$('#response_parameters').html(JSON.stringify(JSON.parse(response.apiData.response_parameters), null, '\t'));

				$('#status').html(response.apiData.status);
				$('#created').html(response.apiData.created);
				$('#response_date_time').html(response.apiData.response_date_time);
				$('#response_time').html(response.apiData.response_time+' Sec');
			}else{
				toastr["error"](response.message);
			}
		}
	})
});


</script>	
@stop
