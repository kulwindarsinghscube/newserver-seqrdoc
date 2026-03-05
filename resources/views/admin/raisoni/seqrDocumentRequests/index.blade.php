@extends('admin.layout.layout')
@section('content')

<div id="">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-list-ul" aria-hidden="true"></i> SEQR Documents
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
				<i class="fa fa-info-circle iconModalCss" title="User Manual" id="seqrDocumentsClick"></i>
				</h1>

			</div>
		</div>

		<div class="">
			<ul class="nav nav-pills" id="pills-filter">
				<li style="float: right;">
					
					<button class="btn btn-theme" id="generateQRReport" style="background-color: #e91e63;border: 1px solid #e91e63;"><i class="fa fa-file-excel-o"></i> Transactional Report</button>
						

					
					<button class="btn btn-theme" id="generateReportSummury" style="background-color: #009688;border: 1px solid #009688;"><i class="fa fa-file-excel-o"></i> Summury Report</button>
						


				</li>

			</ul>
			<br>
			<div class="col-xs-12">
				<div class="row">
					<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
					<div class="input-group date" data-provide="datepicker">
					    <input type="text" class="form-control datetimepicker" name="fromDate" id="fromDate" placeholder="Date From"  title="Date From" data-toggle="tooltip">
					    <div class="input-group-addon">
					        <span class="glyphicon glyphicon-th"></span>
					    </div>
					</div>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="input-group date" data-provide="datepicker" value="">
					    <input type="text" class="form-control datetimepicker" name="toDate" id="toDate" placeholder="Date To"  title="Date To" data-toggle="tooltip">
					    <div class="input-group-addon">
					        <span class="glyphicon glyphicon-th"></span>
					    </div>
					</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
						 <button type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #2196f3;background-color: #03a9f4 !important;color:#fff;max-width: 250px;    margin: auto; " id="submitFilter"><i class="fa fa-filter"></i> Filter</button>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
						 <button type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #FF9800 ;background-color: #FF9800  !important;color:#fff;max-width: 250px;    margin: auto; " id="clearFilter">Clear Filter</button>
					</div>
				</div>
				<hr>
				<table id="example" class="table table-hover" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Request No</th>
							<th>Requesting Person</th>
							<th>Device</th>
							<th>Documents Verified</th>
							<th>Date & Time</th>
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


<style type="text/css">
.modal-body .row{ border-bottom: 1px solid #e5e5e5;
    padding-bottom: 10px;
    padding-top: 10px;
}
</style>


<div id="viewDetailsModel" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Scanned Details</h4>
      </div>
      <div class="modal-body">
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Submitted By</div><div class="col-lg-8">:&nbsp;<span id="submittedByView"></span></div>
      	</div>
      		<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Device Type</div><div class="col-lg-8">:&nbsp;<span id="deviceTypeView"></span></div>
      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Request No.</div><div class="col-lg-8">:&nbsp;<span id="requestNoView"></span></div>
      	</div>
      	<div class="row" style="border-bottom: 2px solid #2196F3;">
      	   	<div class="col-lg-3" style="font-weight: 600;">Submission Date & Time</div><div class="col-lg-8">:&nbsp;<span id="submissionDateTimeView"></span></div>
      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Payment Transaction Id</div><div class="col-lg-8">:&nbsp;<span id="paymentTransactionIdView"></span></div>
      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Payment Gateway Id</div><div class="col-lg-8">:&nbsp;<span id="paymentGatewayIdView"></span></div>
      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Mode</div><div class="col-lg-8">:&nbsp;<span id="modeView"></span></div>
      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Amount</div><div class="col-lg-8">:&nbsp;<span id="amountView"></span></div>
      	</div>
      	<div class="row" style="border-bottom: 2px solid #2196F3;">
      	   	<div class="col-lg-3" style="font-weight: 600;">Payment Date & Time</div><div class="col-lg-8">:&nbsp;<span id="paymentDateTimeView"></span></div>
      	</div>
      	<style type="text/css">
      		.files-table{

			font-size: 13px;
			font-weight: 600;
			text-align: center;
			border: 1px solid #dbdbdb;
			border-collapse: collapse;
		}
		.files-table th{
			text-align: center;
			background-color: #d8e6fa;
			border-bottom: 0;
		}
		.files-table td {

			border: 1px solid #dbdbdb !important;
		}

		.file-head {

			border: 1px solid #dbdbdb !important;
		}
      	</style>
      	<div class="row" style="border: 0;">
      	   	<div class="col-lg-12">Document Details :</div>

      	   	<div style="margin: 20px;margin-top: 40px;">
      	   		<table class="table files-table" >
      	   			<tr>
	      	   			<th class="file-head">Unique Document No</th>
	      	   			<th class="file-head">Template</th>
	      	   			<th class="file-head">Status</th>
	      	   			<th class="file-head">Amount</th>
      	   			</tr>
      	   			<tbody id="tableDocumentBody">
      	   			</tbody>
      	   		</table>

      	   	</div>

      	</div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-theme" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

@stop

@section('script')


<script type="text/javascript">
	$(document).ready(function(){
	$('.datetimepicker').datetimepicker( {
    maxDate: moment(),
    allowInputToggle: true,
    enabledHours : false,
    locale: moment().local('en'),
    format: 'DD-MM-YYYY',
    defaultDate: ''
}).val('');
});
</script>

<script>

$(document).ready(function(){
	
	var url = "<?=route('request_testing')?>";     
     		
    var oTable = $('#example').DataTable({
    	'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "sAjaxSource": url,
        "aaSorting": [
            [1, "desc"]
        ],
        "oLanguage": {
          "sLengthMenu": "Display _MENU_ records"
        },
        "aoColumns":[

           /* { "mData":"id",bSortable : true,bVisible:true,sWidth:"10%"},*/
        	 
        	{ 
	            "mData": "serial_no",
	            sWidth: "2%",
	            mRender: function(data, type, row, meta) {
	                return meta.row + meta.settings._iDisplayStart + 1;
	            }
	        },

            { "mData": "request_number",sWidth: "15%",bSortable: true,bVisible:true},
            { "mData": "fullname",sWidth: "20%",bSortable: true,bVisible:true},
            { "mData": "device_type",sWidth: "15%",bSortable: true,bVisible:true},
            { "mData": "no_of_documents",sWidth: "20%",bSortable: true,bVisible:true},
            { "mData": "created_date_time",sWidth: "20%",bSortable: true,bVisible:true},
            {"mData" : "id",bSortable : false,sWidth:"15%",
                                        
            	mRender: function(v,t,o){
                    //console.log(v,"v",t,"t",o,"o");
                    var id= o['id'];
                   
                    var buttons = "";

					buttons += '<span data-toggle="tooltip" title="info" id="infoData" class="" data-id="'+id+'"><i class="fa fa-info-circle fa-lg blue"></i></span> &nbsp;&nbsp;';
					return buttons;
                }
            },  
        ],
      
    });

    $("#submitFilter").click(function(){

		var fromDate=$('#fromDate').val();
		var toDate=$('#toDate').val();
		if(fromDate!=''&&toDate!=''){
		oTable.ajax.url("/admin/seqr_document_requests?fromDate="+fromDate+"&toDate="+toDate);
		oTable.ajax.reload();
		}else{
			toastr["error"]("Please select from & to date!");
		}
	});

	$("#clearFilter").click(function(){

		$('.datetimepicker').datetimepicker( {
		    maxDate: moment(),
		    allowInputToggle: true,
		    enabledHours : false,
		    locale: moment().local('en'),
		    format: 'DD-MM-YYYY',
		    defaultDate: ''
		}).val('');
	oTable.ajax.url("/admin/seqr_document_requests");
	oTable.ajax.reload();
	});
	$("#generateQRReport").click(function(){

		// console.log('hi');
		var url = "<?=route('seqr-document-requests.report.transaction')?>";    
		var token = "{{ csrf_token() }}";
	 	var newForm = jQuery('<form>', {
            'action': url,
            'method':'POST',
            'target': '_top'
        }).append(jQuery('<input>', {
            'name': 'type',
            'value': 'generateQRReport',
            'type': 'hidden'
        })).append(jQuery('<input>', {
            'name': 'fromDate',
            'value': $('#fromDate').val(),
            'type': 'hidden'
        })).append(jQuery('<input>', {
            'name': 'toDate',
            'value': $('#toDate').val(),
            'type': 'hidden'
        })).append(jQuery('<input>', {
            'name': '_token',
            'value': token,
            'type': 'hidden'
        }));

       $(document.body).append(newForm);
       newForm.submit();

	});
	$("#generateReportSummury").click(function(){

		var url = "<?=route('seqr-document-requests.report.summary')?>";    
		var token = "{{ csrf_token() }}";

	 	var newForm = jQuery('<form>', {
            'action': url,
            'method':'POST',
            'target': '_top'
        }).append(jQuery('<input>', {
            'name': 'type',
            'value': 'generateReportQRSummury',
            'type': 'hidden'
        })).append(jQuery('<input>', {
            'name': 'fromDate',
            'value': $('#fromDate').val(),
            'type': 'hidden'
        })).append(jQuery('<input>', {
            'name': 'toDate',
            'value': $('#toDate').val(),
            'type': 'hidden'
        })).append(jQuery('<input>', {
            'name': '_token',
            'value': token,
            'type': 'hidden'
        }));

       $(document.body).append(newForm);
       newForm.submit();

	});
	oTable.on('click','#infoData',function(e){
		$id = $(this).data('id');
		var token = "{{ csrf_token() }}";
		var url = "{{ URL::route('document-request-data') }}"
		$.post(url,
		{'type':'details_seqr','id':$id,'_token':token},
		function(data){

			if(data.type == 'success'){
							if(data.requestData.l_name!=''&&data.requestData.l_name!=null){
							$('#submittedByView').html(data.requestData.fullname+' '+data.requestData.l_name);
							}else{
							$('#submittedByView').html(data.requestData.fullname);
							}
							$('#requestNoView').html(data.requestData.request_number);
							$('#deviceTypeView').html(data.requestData.device_type);

							$('#submissionDateTimeView').html(data.requestData.created_date_time);
							$('#paymentTransactionIdView').html(data.requestData.payment_transaction_id);
							$('#paymentGatewayIdView').html(data.requestData.payment_gateway_id);
							$('#modeView').html(data.requestData.payment_mode);
							$('#amountView').html(data.requestData.amount);
							$('#paymentDateTimeView').html(data.requestData.payment_date_time);


							$('#tableDocumentBody').empty();
							var documentLength=data.requestDetails.length;

							if(documentLength>0){
								for(var i=0;i<documentLength;i++){


									if(data.requestDetails[i].status==0){
										var status="<span style='color:red;'>Disabled</span>";
									}else{
										var status="<span style='color:green;'>Active</span>";

									}
									if(data.requestDetails[i].amount==0){
										var amount="<span style='color:orange;'>Already Paid</span>";
									}else{
										var amount=data.requestDetails[i].amount;

									}
									//console.log(data.requestDetails[i].template_type);
									if(data.requestDetails[i].template_type==2 || data.requestDetails[i].template_id==100){
										var template_name="Custom Template";
									}else if(data.requestDetails[i].template_type==1){
										var template_name="PDF2PDF Template";
									}else{
										var template_name=data.requestDetails[i].template_name;

									}

									$('#tableDocumentBody').append('<tr>'+
																 	'<td>'+data.requestDetails[i].serial_no+'</td>'+
																 	'<td>'+template_name+'</td>'+
																 	'<td>'+status+'</td>'+
																 	'<td>'+amount+'</td>'+
																 	'</tr>');
								}
							}else{
								$('#tableDocumentBody').append('<tr><td colspan="6">Data Not Found</td></tr>');
							}

							$('#viewDetailsModel').modal('show');
						}
						else{

							toastr["error"](data.message);
						}



		},'json');
	});
});

 // send Ajax Request to featch data and show model for SEQR document request		
function preview(id)
{
 	console.log(id);

 	var token = "{{ csrf_token() }}";
	var id = id;
    $.ajax({
        url: "{{ URL::route('document-request-data') }}",
        type: 'get',
        dataType:'json',
        data:{
            id:id,
            _token:token
        },
        success: function(data) {
        	
        	console.log(data.scan_request_data[0].fullname);
        	if(data.type == 'success'){
        		
            	if(data.scan_request_data[0].fullname!=''&&data.scan_request_data[0].fullname!=null){
						$('#submittedByView').html(data.scan_request_data[0].fullname);
						}else{
						$('#submittedByView').html(data.scan_request_data[0].fullname);
						}
						$('#requestNoView').html(data.scan_request_data[0].request_number);
						$('#deviceTypeView').html(data.scan_request_data[0].device_type);

						$('#submissionDateTimeView').html(data.scan_request_data[0].created_date_time);
						$('#paymentTransactionIdView').html(data.scan_request_data[0].trans_id_ref);
						$('#paymentGatewayIdView').html(data.scan_request_data[0].trans_id_gateway);
						$('#modeView').html(data.scan_request_data[0].payment_mode);
						$('#amountView').html(data.scan_request_data[0].amount);
						$('#paymentDateTimeView').html(data.scan_request_data[0].created_at);

				$('#viewDetailsModel').modal('show');	
				}else{

				toastr["error"](data.message);
				}

		}
	});
}




	
</script>
@stop
@section('style')
<style>
.help-inline{
	color:red;
	font-weight:normal;
}

#example td{
	vertical-align:middle !important;
	padding:15px 10px;
}
#example tr.sys-admin{
	background:#b3e5fc !important;
		font-weight:bold;
}

#example tr.sys-admin .green, #example tr.sys-admin .red, #example tr.sys-admin .yellow, #example tr.sys-admin .blue, #example tr.sys-admin .grey{
	color:#283593;
}

#example .green, #example  .red, #example .yellow, #example .blue{
	cursor:pointer;
}

.grey{
	color:#444;
}

.active-student{
	border-left:3px solid #5CB85C !important;
}
.inactive-student{
	border-left:3px solid #D9534F !important;
}

.nav-pills>li.active>a, .nav-pills>li.active>a:focus{
	background:#0052CC;
	color:#fff;
	border:1px solid #0052CC;
}


.nav-pills>li.active>a:hover, .nav-pills>li>a:focus, .nav-pills>li>a:hover
{
	background:#fff;
	background:#ddd;
	border-radius:0;
	padding:10px 20px;
	color:#333;
	border-radius:2px;
	border:1px solid #ddd;
}

.nav-pills>li>a, .nav-pills>li>a
{
	background:#fff;
	color:#aaa;
	border-radius:0;
	padding:10px 20px;
	border-radius:2px;
	margin-bottom:20px;
	border:1px solid #ddd;
}

#example_length label{
	display:none;
}

.active .success{
	background:#5CB85C !important;
	border:1px solid #5CB85C !important;
	color:#fff !important;
}

.active .failed{
	background:#D9534F !important;
	border:1px solid #D9534F !important;
	color:#fff !important;
}

</style>
@stop