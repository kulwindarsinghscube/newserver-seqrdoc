@extends('verify.layout.layout')
@section('content')
<h1 class="page-header"><i class="fa fa-envira"></i> Pending Payments
<span style="font-family:roboto;font-weight:500;font-size:14px;color:#777;display:block;margin:10px 0;">
A consolidated list of verification request from various devices.
</span>
</h1>
<div class="">
	<div class="">
		<!-- <ul class="nav nav-pills" id="addUser">
		  <li class="active"><a id="web-pill" data-toggle="pill" href="#webapp"><i class="fa fa-fw fa-lg fa-desktop"></i> WebApp </a></li>
		  <li><a id="android-pill" data-toggle="pill" href="#android"><i class="fa fa-fw fa-lg fa-android"></i> Android</a></li>
		  <li><a id="iphone-pill" data-toggle="pill" href="#iphone"><i class="fa fa-fw fa-lg fa-apple"></i> iPhone</a></li>
		</ul> -->

		<table id="example" class="table table-hover display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>#</th>
					<th>Request No. </th>
					<th>Student Name </th>
					<th>No. Of Documents </th>
					<th>Submitted Date </th>
					<th>To Pay Amount </th>
					<th>Actions </th>
				</tr>
			</thead>
			<tbody></tbody>
			<tfoot></tfoot>
		</table>
	</div>
</div>
<div class="modal zoomIn animated" id="info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel" style="display: inline-block;">Student's info</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-9">
					  	<div class="row">
							<div class="col-xs-5"><label for="info1">Serial No.</label></div>
							<div class="col-xs-1"><label for="info1">:</label></div>
							<div class="col-xs-6" id="info1"></div>
						</div>
						<div class="row">
							<div class="col-xs-5"><label for="info2">Student Name</label></div>
							<div class="col-xs-1"><label for="info2">:</label></div>
							<div class="col-xs-6" id="info2"></div>
						</div>
						<div class="row">
							<div class="col-xs-5"><label for="info3">Certificate Filename</label></div>
							<div class="col-xs-1"><label for="info3">:</label></div>
							<div class="col-xs-6"><a target="_blank" id="info3" href=""></a></div>
						</div>
						<div class="row">
							<div class="col-xs-5"><label for="info4">Status</label></div>
							<div class="col-xs-1"><label for="info4">:</label></div>
							<div class="col-xs-6" id="info4"></div>
						</div>
					</div>
					<div class="col-xs-3">
					<div class="col-xs-12" id="info5"></div>
					</div>
				</div>

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
        <h4 class="modal-title">Request Details</h4>
      </div>
      <div class="modal-body">
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Submitted By</div><div class="col-lg-8">:&nbsp;<span id="submittedByView"></span></div>
      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Request No.</div><div class="col-lg-8">:&nbsp;<span id="requestNoView"></span></div>
      	</div>
      <!-- 	<div class="row" style="padding-bottom: 0;"> -->
      	   <!-- 	<div class="col-lg-12">Submitted Details :</div> -->
      	   <!-- 	<div style="border: 1px solid #dbdbdb; padding: 0px 14px 0px 14px; margin-top: 30px; border-bottom: 0;"> -->
      	   	<div class="row">
      	   			<div class="col-lg-3"  style="font-weight: 600;">
      	   				Student Name
      	   			</div>
      	   			<div class="col-lg-9">
      	   				:&nbsp;<span id="studentNameView"></span>
      	   			</div>
      	   	</div>
      	   		<div class="row">
      	   			<div class="col-lg-3"  style="font-weight: 600;">
      	   				Institute
      	   			</div>
      	   			<div class="col-lg-9">
      	   				:&nbsp;<span id="instituteView"></span>
      	   			</div>
      	   	</div>
      	   		<div class="row">
      	   			<div class="col-lg-3"  style="font-weight: 600;">
      	   				Degree
      	   			</div>
      	   			<div class="col-lg-9">
      	   				:&nbsp;<span id="degreeView"></span>
      	   			</div>
      	   	</div>
      	   		<div class="row">
      	   			<div class="col-lg-3"  style="font-weight: 600;">
      	   				Branch
      	   			</div>
      	   			<div class="col-lg-9">
      	   				:&nbsp;<span id="branchView"></span>
      	   			</div>
      	   		</div>
      	   		<div class="row">
      	   			<div class="col-lg-3"  style="font-weight: 600;">
      	   				Registration No
      	   			</div>
      	   			<div class="col-lg-9">
      	   				:&nbsp;<span id="registrationNoView"></span>
      	   			</div>
      	   		</div>
      	   		<div class="row">
      	   			<div class="col-lg-3"  style="font-weight: 600;">
      	   				Passout Year
      	   			</div>
      	   			<div class="col-lg-9">
      	   				:&nbsp;<span id="passoutYearView"></span>
      	   			</div>
      	   		</div>
      	   		<div class="row">
      	   			<div class="col-lg-3"  style="font-weight: 600;">
      	   				Name Of Recruiter
      	   			</div>
      	   			<div class="col-lg-9">
      	   				:&nbsp;<span id="nameOfRecruiterView"></span>
      	   			</div>
      	   		</div>
      	   		<div class="row" style="border: 0;">
      	   			<div class="col-lg-3"  style="font-weight: 600;">
      	   				Offer/Joining Letter
      	   			</div>
      	   			<div class="col-lg-9">
      	   				:&nbsp;<span id="offerLetterView"></span>
      	   			</div>
      	   		</div>
     <!--  	</div> -->
      <!-- 	</div> -->

      	<div class="row" style="border-bottom: 2px solid #2196F3;">
      	   	<div class="col-lg-3" style="font-weight: 600;">Submission Date & Time</div><div class="col-lg-8">:&nbsp;<span id="submissionDateTimeView"></span></div>
      	</div>
      <!-- 	<div class="row">
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
 -->      	<style type="text/css">
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
      	<div class="row" style="border-bottom: 0; ">
      	   	<div class="col-lg-12">Documents Uploaded :</div>

      	   	<div style="margin: 20px;margin-top: 40px;">
      	   		<table class="table files-table" >
      	   			<tr>
	      	   			<th class="file-head">Document Type</th>
	      	   			<th class="file-head">Uploaded File</th>
	      	   			<th class="file-head">Results Found</th>
	      	   			<th class="file-head">Remark</th>
	      	   			<th class="file-head">Exam Name</th>
	      	   			<th class="file-head">Semester</th>
      	   			</tr>
      	   			<tbody id="tableDocumentBody">
      	   			<tr>
	      	   			<td>Document Type</td>
	      	   			<td>Uploaded File</td>
	      	   			<td>Results Found</td>
	      	   			<td>Remark</td>
	      	   			<td>Exam Name</td>
	      	   			<td>Semester</td>
      	   			</tr>
      	   			</tbody>
      	   		</table>

      	   	</div>

      	</div>
      	<!-- <div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Current Status</div><div class="col-lg-8">:&nbsp;<span id="currentStatusView"></span></div>
      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Status Last Updated Date & Time</div><div class="col-lg-8">:&nbsp;<span id="statusLastDateTimeView"></span></div>
      	</div>
      	<div class="row" style="border: 0;">
      	   	<div class="col-lg-3" style="font-weight: 600;">Status Last Updated By</div><div class="col-lg-8">:&nbsp;<span id="statusLastUpdatedByView"></span></div>
      	</div> -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-theme" data-dismiss="modal" style="color: #fff;">Close</button>
      </div>
    </div>

  </div>
</div>
@stop
@section('script')
<script>
$(document).ready(function(){

	var oTable = $('#example').DataTable( {
        "processing": true,
        "serverSide": true,
        "sAjaxSource": "{{URL::route('verify.pending.payments')}}",
        "order": [[5, 'desc']],
		"bInfo":false,
		"aoColumns": [
        
	        { "mData": "rownum",sWidth: "5%"},
	        { "mData": "request_number",sWidth: "15%",bSortable: true},
	        { "mData": "student_name",sWidth: "20%",bSortable: true},
	        { "mData": "no_of_documents",sWidth: "10%",bSortable: true},
	        { "mData": "created_date_time",sWidth: "20%",bSortable: true},
	        { "mData": "total_amount",sWidth: "15%",bSortable: true},
	        { 
	        	"mData": "id",
	        	bSortable:false,
	        	sWidth: "15%",
              	mRender:function(v, t, o){
              		return '<span data-toggle="tooltip" title="info" id="infoData" class="" data-id="'+v+'"><i class="fa fa-info-circle fa-lg blue"></i></span> &nbsp;&nbsp;<a id="doPayment" class="data-table-cust-btn" data-request_number="'+o['request_number']+'" title="Make Payment"><i class="fa fa-credit-card" aria-hidden="true"></i></a>&nbsp;&nbsp<a style="color:red;" class="delete data-table-cust-btn" id="delete" data-id="'+v+'" title="Delete Permanently"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
              	}
	    	},
	    ],

    } );

    //info data
	oTable.on('click','#infoData',function(e){
		$id = $(this).data('id');
		var token="{{ csrf_token() }}";
		$.post("{{URL::route('verify.pending.payments.info')}}",
		{'type':'details','id':$id,'_token':token},
		function(data){
			<?php
	            $domain = \Request::getHost();
	            $subdomain = explode('.', $domain);
	        ?>
			if(data.type == 'success'){
							if(data.requestData.l_name!=''&&data.requestData.l_name!=null){
							$('#submittedByView').html(data.requestData.fullname+' '+data.requestData.l_name);
							}else{
							$('#submittedByView').html(data.requestData.fullname);
							}
							$('#requestNoView').html(data.requestData.request_number);

							/***submittedDetailsView****/
							$('#studentNameView').html(data.requestData.student_name);
							$('#instituteView').html(data.requestData.institute);
							$('#degreeView').html(data.requestData.degree_name);
							$('#branchView').html(data.requestData.branch_name_long);
							$('#registrationNoView').html(data.requestData.registration_no);
							$('#passoutYearView').html(data.requestData.passout_year);
							$('#nameOfRecruiterView').html(data.requestData.name_of_recruiter);
							if(data.requestData.offer_letter!=''&&data.requestData.offer_letter!=null){
								$('#offerLetterView').html('<a href="<?= Config::get('constant.local_base_path').$subdomain[0]?>/backend/'+data.requestData.offer_letter+'" target="_blank">View File</a>');
								// $('#offerLetterView').html('<a href="'+data.requestData.offer_letter+'" target="_blank">View File</a>');
							}else{
								$('#offerLetterView').html('Not Found');
							}

							$('#submissionDateTimeView').html(data.requestData.created_date_time);
						/*	$('#paymentTransactionIdView').html(data.requestData.payment_transaction_id);
							$('#paymentGatewayIdView').html(data.requestData.payment_gateway_id);
							$('#modeView').html(data.requestData.payment_mode);
							$('#amountView').html(data.requestData.amount);
							$('#paymentDateTimeView').html(data.requestData.payment_date_time);
							$('#currentStatusView').html(data.requestData.verification_status);
							if(data.requestData.updated_date_time!=''&&data.requestData.updated_date_time!=null){
							$('#statusLastDateTimeView').html(data.requestData.updated_date_time);
							}else{
							$('#statusLastDateTimeView').html('Not Updated Yet');
							}
							if(data.requestData.username!=''&&data.requestData.username!=null){
							$('#statusLastUpdatedByView').html(data.requestData.username);
							}else{
								$('#statusLastUpdatedByView').html('Not Updated Yet');
							}*/

							$('#tableDocumentBody').empty();
							var documentLength=data.requestDetails.length;

							if(documentLength>0){
								for(var i=0;i<documentLength;i++){
									// var document_path=''+data.requestDetails[i].document_path;
									var document_path='<?= Config::get('constant.local_base_path').$subdomain[0]?>/backend/'+data.requestDetails[i].document_path;
									if(data.requestDetails[i].remark!=''&&data.requestDetails[i].remark!=null){
									var remark=data.requestDetails[i].remark;
									}else{
									var remark='-';
									}
									if(data.requestDetails[i].exam_name!=''&&data.requestDetails[i].exam_name!=null){
									var exam_name=data.requestDetails[i].exam_name_display;
									}else{
									var exam_name='-';
									}
									if(data.requestDetails[i].semester!=''&&data.requestDetails[i].semester!=null){
									var semester=data.requestDetails[i].semester_name_display;
									}else{
									var semester='-';
									}
									if(data.requestDetails[i].result_found_status=="Correct"){
										var statusClr="green";
									}else if(data.requestDetails[i].result_found_status=="Incorrect"){
										var statusClr="red";
									}else{
										var statusClr="black";
									}
									$('#tableDocumentBody').append('<tr>'+
																 	'<td>'+data.requestDetails[i].document_type+'</td>'+
																 	'<td><a href="'+document_path+'" target="_blank">View File</a></td>'+
																 	'<td style="color:'+statusClr+'">'+data.requestDetails[i].result_found_status+'</td>'+
																 	'<td>'+remark+'</td>'+
																 	'<td>'+exam_name+'</td>'+
																 	'<td>'+semester+'</td>'+
																 	'</tr>');
								}
							}else{
								$('#tableDocumentBody').append('<tr><td colspan="6">Data Not Found</td></tr>');
							}

							$('#viewDetailsModel').modal('show');
						}
						else{
							/* $(".alert-danger .message").html(data.message);
							$(".alert-danger").fadeIn(); */
							toastr["error"](data.message);
						}



		},'json');
	});
    oTable.on('click', '#delete', function (e) {
    	var request_id=$(this).attr('data-id');
    	var token="{{ csrf_token() }}";
		bootbox.confirm('Are you sure to this request ?',function(result){
			if(result){
				$.ajax({
			            url: '{{URL::route("verify.payments.remove")}}',
			            type: 'POST',
			            data: {type:'delete_permanent',request_id:request_id,_token:token},
			            success: function (data) {
			                if(data.type == 'success'){
			                	toastr["success"](data.message);
			                	oTable.ajax.reload();
			                        }else{
			                        	toastr["error"](data.message);
			                        }
			            },

			            dataType:'JSON'
			        });
			}
		});

  });

     oTable.on('click', '#doPayment', function (e) {
 ///console.log($(this).attr('data-id'));window.location.href
 	var req_number = $(this).attr('data-request_number');
 	/*var link="<?=URL::route('verify.payments.Paytm',array(':key_payment')) ?>";
    link=link.replace(':key_payment',req_number);*/
 	var link = "/verify/payment/paytm?key_payment="+req_number;
 	 window.open(link,
                         'Paytm',
                         'width=900,height=450');
 	setInterval( function () {
	    oTable.ajax.reload();
	}, 10000 );
    return false;
  });



});
$('a[data-url^="pendingpayments"]').parent().addClass('active');
</script>
@stop
@section('style')
<style>
#example td{
	word-break: break-all;
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
.delete {

	color: red !important;
}
</style>

@stop