@extends('admin.layout.layout')
@section('content')
<div class="container">
	<div class="loaders"></div>
	<div class="container-fluid">

		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i> ID Card Status
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('idcardstatus') }}</ol>
				<i class="fa fa-info-circle iconModalCss" title="User Manual" id="idCardsStatusClick"></i>
				</h1>
			</div>
		</div>
		
		<div class="">
			<ul class="nav nav-pills" id="pills-filter">
			  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Pending</a></li>
			  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Acknowledged</a></li>
			  
			</ul>
			<table id="example" class="table table-hover display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>#</th>
						<th>Request Number</th>
						<th>Template Name</th>
						<th> Input Excel </th>
						<th>Rows</th>
						<th>Status</th>
						<th>Uploaded By</th>
						<th>Updated On</th>
						<th>Action</th>
						<th>Id</th>
					</tr>
				</thead>
				<tfoot>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Some images are not in folder</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="link" id="link"></div>
      </div>
      
    </div>
  </div>
</div>
@stop
@section('script')
<script type="text/javascript">
var oTable = $('#example').DataTable({
		'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
	    "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [1, "desc"],
        ],
        "sAjaxSource":"<?= URL::route('idcard-status.index',['status'=>1])?>",
        "aoColumns":[
		{ 
            "mData": "serial_no",
            sWidth: "2%",
            mRender: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
		{mData: "request_number"},
		{mData: "template_name"},
		{mData: "excel_sheet",

			mRender: function(v,t,o){
				console.log(o)
				var file_aws_local = '<?= $file_aws_local?>';
				var config = '<?= $config?>';
				<?php
					$domain = \Request::getHost();
        			$subdomain = explode('.', $domain);
				?>
				if(config == '1')
				{
					if(file_aws_local == '1'){
						var excel_path = "<?= \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox')?>"+"/"+o.id+"/"+o.excel_sheet;
					}
					else{
						var excel_path = "<?= 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/'.\Config::get('constant.sandbox')?>"+"/"+o.id+"/"+o.excel_sheet;
					}
				}
				else{
					if(file_aws_local == '1'){
						var excel_path = "<?= \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.template')?>"+"/"+o.id+"/"+o.excel_sheet;
					}
					else{
						var excel_path = "<?= 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/'.\Config::get('constant.template')?>"+"/"+o.id+"/"+o.excel_sheet;
					}

				}

				return "<a data-toggle='tooltip' data-placement='right' title='Please click to download excel file' class='btn btn-success' href='"+excel_path+"'>"+o.excel_sheet+"</a>";
				
			}

		},
		{mData: "rows"},
		{mData: "status"},
		{mData: "username"},
		{mData: "updated_on"},
		{mData: "created_on",

			mRender: function(v, t, o) {
				var buttons = "";
				if(o.status == 'Received'){
					buttons += '<span style="cursor:pointer"><i title="process PDF files" data-request_number="'+o.request_number+'" class="processPdfFiles fa fa-spinner fa-lg blue "></i> </span>';

					buttons += '<span style="cursor:pointer"><i title="revoke request" data-request_number="'+o.request_number+'" class="revokeRequest fa fa-close fa-lg red"></i> </span>';
				}else if(o.status == 'Inprogress'){

					var req_number = o.request_number;
                    var update_req_number = req_number.split('/').join('_');

                    
					buttons += '<a href="<?= 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/'.\Config::get('constant.template')?>/'+o.id+'/'+update_req_number+'.zip"><i title="download excel & photos" data-request_number="'+o.request_number+'" class="download_excel_photos fa fa-download fa-lg"></i></a>';

					buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('idcard-status.update.complete')) @if(App\Helpers\RolePermissionCheck::isPermitted('idcard-status.update.complete'))<span style="cursor:pointer"><i title="update status" data-request_number="'+o.request_number+'" class="update_status fa fa-pencil-square fa-lg green"></i> </span> @endif @endif';

				}else if (o.status == 'Complete'){
					buttons += '<span style="cursor:pointer"><i title="acknowledge delivery " data-request_number="'+o.request_number+'" class="ack_delivery fa fa-check-square fa-lg green"></i> </span>';
				}else{
	
            	}
            	buttons += '<i class="fa fa-eye view_image" data-request_number="'+o.request_number+'"></i>';
            	return buttons;
			}
		},

		{mData: "id",bVisible:false},
	],
	"createdRow": function( row, data, dataIndex ) {
		
		if(data.status == 'Acknowledged'){
			$(row).addClass( 'active-student' );
		}else{
			$(row).addClass( 'inactive-student' );
		}
	}
});
$('#success-pill').click(function(){

	var url="<?= URL::route('idcard-status.index',['status'=>1])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	$('.loader').addClass('hidden');
});

$('#fail-pill').click(function(){

	var url="<?= URL::route('idcard-status.index',['status'=>0])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	$('.loader').addClass('hidden');
});
oTable.on('click', '.revokeRequest', function (e) {

	var req_number = $(this).data('request_number');
	bootbox.confirm('Are you sure you want to revoke request?',function(result){

		if (result) {
			
			var token="{{ csrf_token() }}";
			$.ajax({
					
				url: "<?= URL::route('idcard-status.revoke')?>",
				type: "POST",
				data:{'req_number' : req_number,'_token':token},
				dataType: "json",
				beforeSend:function(aa){
					
					$('.loader').removeClass('hidden');
				},
				success:function(response){
					$('.loader').addClass('hidden');
					toastr["success"](response.message);
					oTable.ajax.reload(null, false);
					
				}
			});

		}
	});	
})
oTable.on('click', '.ack_delivery', function (e) {
		
	var req_number = $(this).data('request_number');
	bootbox.confirm('Are you sure you want to update status from Completed to Acknowledge?',function(result){
		if (result) {
			var token="{{ csrf_token() }}";
			$.ajax({
				
				url: "<?= URL::route('idcard-status.update.acknowledge')?>",
				type: "POST",
				data:{'req_number' : req_number,'_token':token},
				dataType: "json",
				beforeSend:function(aa){
					
					$('.loader').removeClass('hidden');
				},
				success:function(response){
					$('.loader').addClass('hidden');
					toastr["success"](response.message);
					oTable.ajax.reload(null, false);
					
				}
			});
		}
	})
})
oTable.on('click', '.update_status', function (e) {
		
	var req_number = $(this).data('request_number');
	bootbox.confirm('Are you sure you want to update status from In-progress to Completed?',function(result){
		if (result) {
			var token="{{ csrf_token() }}";
			$.ajax({
				
				url: "<?= URL::route('idcard-status.update.complete')?>",
				type: "POST",
				data:{'req_number' : req_number,'_token':token},
				dataType: "json",
				beforeSend:function(aa){
					
					$('.loader').removeClass('hidden');
				},
				success:function(response){
					$('.loader').addClass('hidden');
					toastr["success"](response.message);
					oTable.ajax.reload(null, false);
					
				}
			});
		}
	})
})
oTable.on('click', '.processPdfFiles1', function (e) {
		
	var req_number = $(this).data('request_number');
	bootbox.confirm('Are you sure you want to process pdf?',function(result){
		if (result) {
			var token="{{ csrf_token() }}";
			$.ajax({
				
				url: "<?= URL::route('idcard-status.processPdf')?>",
				type: "POST",
				data:{'req_number' : req_number,'_token':token},
				dataType: "json",
				beforeSend:function(aa){
					
					$('.loader').removeClass('hidden');
				},
				success:function(response){
					if(response.type == "success"){
						$('.loader').addClass('hidden');
						toastr["success"](response.message);
						oTable.ajax.reload(null, false);
					}else{
						alert(1);
						$('.processPdfFiles').trigger('click');
					}
					
				}
			});
		}
	})
})

oTable.on('click', '.processPdfFiles', function (e) {
    var req_number = $(this).data('request_number');

    function processPdfRequest(skipConfirmation = false) {
        if (!skipConfirmation) {
            // Show confirmation only on the first call
            bootbox.confirm('Are you sure you want to process pdf?', function (result) {
                if (result) {
                    processPdfRequest(true); // Skip confirmation in recursive calls
                }
            });
        } else {
            var token = "{{ csrf_token() }}";
            $.ajax({
                url: "<?= URL::route('idcard-status.processPdf') ?>",
                type: "POST",
                data: { 'req_number': req_number, '_token': token },
                dataType: "json",
                beforeSend: function () {
                    $('.loader').removeClass('hidden');
                },
                success: function (response) {
                    $('.loader').addClass('hidden');
                    if (response.type === "success") {
                        toastr["success"](response.message);
                        oTable.ajax.reload(null, false);
                    } else {
                        console.warn("Retrying...");
                        processPdfRequest(true); // Recursive call, skip confirmation
                    }
                },
                error: function () {
                    $('.loader').addClass('hidden');
                    toastr["error"]("An error occurred during the request. Retrying...");
                    processPdfRequest(true); // Recursive call, skip confirmation
                }
            });
        }
    }

    processPdfRequest(); // Initial call with confirmation
});


oTable.on('click','.view_image',function(){

	var req_number = $(this).data('request_number');
	
	var token="{{ csrf_token() }}";
	$.ajax({
				
		url: "<?= URL::route('idcard-status.viewImage')?>",
		type: "POST",
		data:{'req_number' : req_number,'_token':token},
		dataType: "json",
		beforeSend:function(aa){
			
			$('.loader').removeClass('hidden');
		},
		success:function(response){
			if(response.success == false){

				toastr["error"](response.message);
				$('#modal').modal('show');
				$('#link').html(response.msg);
				
			}else{
				toastr["success"](response.message);
				setTimeout(function(){
                            window.location = '{{ route("idcard-status.index") }}';
                        },2000)

			}
		}
	});
})

$('#link').click(function(){
	window.location = '{{ route("idcard-status.index") }}';
});

</script>

@stop