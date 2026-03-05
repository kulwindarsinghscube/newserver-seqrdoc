<?php 
$domain =$_SERVER['HTTP_HOST'];
$subdomain = explode('.', $domain);
 ?>
@extends('admin.layout.layout')
@section('content')
<div id="">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-list-ul" aria-hidden="true"></i> Old Documents
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
<i class="fa fa-info-circle iconModalCss" title="User Manual" id="oldDocumentsClick"></i>
				</h1>

			</div>
		</div>

		<div class="">
			<ul class="nav nav-pills" id="pills-filter">
			  <li class="active nav-pill-class" id="nav-pill-class-pending"><a id="success-pill" data-toggle="pill" href="#success" class="primary"> Pending </a></li>
			  <li class="nav-pill-class" id="nav-pill-class-completed"><a id="fail-pill"data-toggle="pill" href="#failed" class="success"> Completed </a></li>

				<li style="float: right;">
					
					<button class="btn btn-theme" id="generateReport" style="background-color: #e91e63;border: 1px solid #e91e63;"><i class="fa fa-file-excel-o"></i> Generate Non-QR Report</button>
						
					<button class="btn btn-theme" id="generateReportSummury" style="background-color: #009688;border: 1px solid #009688;"><i class="fa fa-file-excel-o"></i> Generate Report Summury</button>
						

				</li>

			</ul>
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
					<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" id="refund_chk_filter_container" style="display: none;">

					    <input type="checkbox" class="" name="refund" id="refund_chk_filter" style="height: 17px;width: 17px;vertical-align: -webkit-baseline-middle;"> <span style="vertical-align: bottom;color: darkgreen;font-weight: 600;font-size: 13px;">Refund Transactions</span>


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
							<th>Student Name</th>
							<th>Registration No</th>
							<th>Verified Documents</th>
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
<div id="updateModel" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg" style="width: 1150px;">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Update Request</h4>
      </div>
      <div class="modal-body">
      	<form id="updateForm">

      	<input type="hidden" name="request_id" id="request_id" value="0" />
      	<input type="hidden" name="_token" id="token" value="{{csrf_token()}}" />
      	<div class="row" style="border: 0;">
      	   	<div class="col-lg-12" style="font-weight: 600;">Document Verification Details :</div>

      	   	<div style="margin: 20px;margin-top: 40px;">
      	   		<table class="table files-table" >
      	   			<tr>
	      	   			<th class="file-head">Document Type</th>
	      	   			<th class="file-head">Uploaded File</th>
	      	   			<th class="file-head">Device Type</th>
	      	   			<th class="file-head">Results Found</th>
	      	   			<th class="file-head">Remark</th>
	      	   			<th class="file-head">Exam Name</th>
	      	   			<th class="file-head">Semester</th>
	      	   			<th class="file-head">Year</th>
      	   			</tr>
      	   			<tbody id="tableDocumentBodyUpdate">
      	   			<!-- <tr>
	      	   			<td>Document Type</td>
	      	   			<td>Uploaded File</td>
	      	   			<td>Results Found</td>
	      	   			<td>Remark</td>
	      	   			<td>Exam Name</td>
	      	   			<td>Semester</td>
      	   			</tr> -->
      	   			</tbody>
      	   		</table>
      	   		<div class="col-xs-12 col-md-12 col-lg-12">
					<button type="submit" id="updateBtn" class="btn btn-sm btn-block" style="margin-bottom: 10px;max-width: 150px;margin: auto;
    color: #fff;background-color: green;">Submit</button>
				</div>
      	   	</div>
      	</div>

			</form>
      	</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-theme" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

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
      	   	<div class="col-lg-3" style="font-weight: 600;">Device Type</div><div class="col-lg-8">:&nbsp;<span id="deviceTypeView"></span></div>
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
      	   				<?php if($subdomain[0] == "galgotias"){ ?> University <?php }else{ ?>Institute <?php } ?>
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
      	<div class="row">
      	   	<div class="col-lg-12">Document Verification Details :</div>

      	   	<div style="margin: 20px;margin-top: 40px;">
      	   		<table class="table files-table" >
      	   			<tr>
	      	   			<th class="file-head">Document Type</th>
	      	   			<th class="file-head">Uploaded File</th>
	      	   			<th class="file-head">Device Type</th>
	      	   			<th class="file-head">Results Found</th>
	      	   			<th class="file-head">Remark</th>
	      	   			<th class="file-head">Exam Name</th>
	      	   			<th class="file-head">Semester</th>
	      	   			<th class="file-head">Year</th>
      	   			</tr>
      	   			<tbody id="tableDocumentBody">
      	   			<!-- <tr>
	      	   			<td>Document Type</td>
	      	   			<td>Uploaded File</td>
	      	   			<td>Results Found</td>
	      	   			<td>Remark</td>
	      	   			<td>Exam Name</td>
	      	   			<td>Semester</td>
      	   			</tr> -->
      	   			</tbody>
      	   		</table>

      	   	</div>

      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Current Status</div><div class="col-lg-8">:&nbsp;<span id="currentStatusView"></span></div>
      	</div>
      	<div class="row">
      	   	<div class="col-lg-3" style="font-weight: 600;">Status Last Updated Date & Time</div><div class="col-lg-8">:&nbsp;<span id="statusLastDateTimeView"></span></div>
      	</div>
      	<div class="row" style="border: 0;">
      	   	<div class="col-lg-3" style="font-weight: 600;">Status Last Updated By</div><div class="col-lg-8">:&nbsp;<span id="statusLastUpdatedByView"></span></div>
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
<script type="text/javascript">
var oTable = $('#example').DataTable( {
    'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
    "bProcessing": false,
    "bServerSide": true,
    "autoWidth": true,

    "aaSorting": [
    [1, "desc"]
    ],
    "sAjaxSource":"<?= URL::route('oldverification.index',['payment_status'=>'Paid','verification_status'=>'Pending']) ?>",
        "aoColumns":[
        {mData: "rownum", bSortable:false},
        {
            mData: "request_number",
            bSortable:true,
            "sClass": "text-center",
        },
        {mData: "fullname",bSortable:true,"sClass": "text-center"},
        {mData: "student_name",bSortable:true,"sClass": "text-center"},
        {mData: "registration_no",bSortable:true,"sClass": "text-center"},		
		{
			mData:"verification_count",
			bSortable:false,
			mRender:function (v,t,o) {
				// console.log(v);
				// console.log(t);
				// console.log(o);
				// return getDocumentVerificationCount(v);
				
			    return "<span class='badge'>"+v+"</span>";
			    // return data;

			},
		
		},
		// {mData: "verification_status",bSortable:true,"sClass": "text-center"},
		{
			mData:"id",
            "mRender": function (oObj,t,o) {
            	
                var buttons = "";

				buttons += '<span data-toggle="tooltip" title="info" id="infoData" class="" data-id="'+oObj+'"><i class="fa fa-info-circle fa-lg blue"></i></span> &nbsp;&nbsp;';
				
				if(o['verification_status']=="Pending"){

				buttons += '<span data-toggle="tooltip" title="update" id="editData" class="" data-id="'+oObj+'"><i class="fa fa-pencil-square-o fa-lg blue" aria-hidden="true"></i></span> &nbsp;&nbsp;';
				}

				return buttons;
            },
            
        },
	],
}); 

oTable.on('draw',function(){
    
    $(".loader").addClass('hidden');
});
	var semesterDropdown;
	var token = "<?=csrf_token()?>";
$.post('/admin/old-verification/semester',
	{'_token':token},
	function(data){
		if(data.type == 'success'){
			semesterDropdown=data.data;
		}
		else{
			toastr["error"](data.message);
		}
	},
'json');
	var examDropdown;
	$.post('/admin/old-verification/exam',
	{'_token':token},
	function(data){
		if(data.type == 'success'){
			examDropdown=data.data;
		}
		else{
			toastr["error"](data.message);
		}
	},
'json');
	var ajaxURL = "/admin/old-verification/info-data";

oTable.on('click','#infoData',function(e){
	$id = $(this).data('id');
	var token = "<?=csrf_token()?>";
	$.post(ajaxURL,
	{'id':$id,'_token': token},
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
						$('#deviceTypeView').html(data.requestData.device_type);
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
							$('#offerLetterView').html('<a href="<?= Config::get('constant.local_base_path').$subdomain[0]."/backend/"?>'+data.requestData.offer_letter+'" target="_blank">View File</a>');
						}else{  
							$('#offerLetterView').html('Not Found');
						}

						$('#submissionDateTimeView').html(data.requestData.created_date_time);
						$('#paymentTransactionIdView').html(data.requestData.payment_transaction_id);
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
						}

						$('#tableDocumentBody').empty();
						var documentLength=data.requestDetails.length;

						if(documentLength>0){
							for(var i=0;i<documentLength;i++){
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
								if(data.requestDetails[i].doc_year!=''&&data.requestDetails[i].doc_year!=null){
								var doc_year=data.requestDetails[i].doc_year;
								}else{
								var doc_year='-';
								}
								switch(data.requestDetails[i].result_found_status){
									case 'Correct':
									var statusClr="green";
									break;
									case 'Incorrect':
									var statusClr="red";
									break;
									case 'Return':
									var statusClr="orange";
									break;
									default :
									var statusClr="black";
								}

								if(data.requestDetails[i].refund_updated_date_time!=''&&data.requestDetails[i].refund_updated_date_time!=null){
									var refund_updated_date_time=data.requestDetails[i].refund_updated_date_time;
								}else{
									var refund_updated_date_time='-';
								}
								if(data.requestDetails[i].is_refunded==1){
									var refundDiv='<span data-toggle="collapse" data-target="#col'+data.requestDetails[i].id+'"><i class="fa fa-info-circle" aria-hidden="true" style="color: #2196F3;font-size: 18px;cursor:pointer;" title="View Refund Details"></i></span>'+
										'<div id="col'+data.requestDetails[i].id+'" class="collapse" style="text-align:left;">'+
										'<div><span style="color:#767676;">Transaction Ref. ID : </span>'+data.requestDetails[i].transaction_ref_id+'</div>'+
									    '<div><span style="color:#767676;">Transaction ID : </span>'+data.requestDetails[i].transaction_id_payu+'</div>'+
									    '<div><span style="color:#767676;">Request ID : </span>'+data.requestDetails[i].refund_request_id+'</div>'+
									    '<div><span style="color:#767676;">Status : </span>'+data.requestDetails[i].status+'</div>'+
									    '<div><span style="color:#767676;">Refund Date Time </span>: '+data.requestDetails[i].refund_date_time+'</div>'+
									    '<div><span style="color:#767676;">Updated Date Time </span>: '+refund_updated_date_time+'</div>'+
									  '</div>';
								}else{
									var refundDiv='';
								}




								$('#tableDocumentBody').append('<tr>'+
															 	'<td>'+data.requestDetails[i].document_type+'</td>'+
															 	'<td><a href="'+document_path+'" target="_blank">View File</a></td>'+
															 	'<td>'+data.requestData.device_type+'</td>'+
															 	'<td><span  style="color:'+statusClr+'">'+data.requestDetails[i].result_found_status+'</span><br>'+refundDiv+'</td>'+
															 	'<td>'+remark+'</td>'+
															 	'<td>'+exam_name+'</td>'+
															 	'<td>'+semester+'</td>'+
															 	'<td>'+doc_year+'</td>'+
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
var ajaxURL = "/admin/old-verification/edit-data";
oTable.on('click', '#editData', function (e) {
	//console.log("entered");
	var token = "<?=csrf_token()?>";
	$id = $(this).data('id');
	$.post(ajaxURL,
	{'id':$id,'_token':token},
	function(data){
		//console.log('here');
		console.log(data);
		if(data.type == 'success'){
			// console.log('here1');
					$('#request_id').val(data.requestData.id);
						$('#updateBtn').show();
						$('#tableDocumentBodyUpdate').empty();
						var documentLength=data.requestDetails.length;
			// console.log(data.requestDetails);

						if(documentLength>0){
							for(var i=0;i<documentLength;i++){
								var document_path='<?= Config::get('constant.local_base_path').$subdomain[0]."/backend/"?>'+data.requestDetails[i].document_path;
								if(data.requestDetails[i].remark!=''&&data.requestDetails[i].remark!=null){
								var remark=data.requestDetails[i].remark;
								}else{
								var remark='';
								}

								if(data.requestDetails[i].semester!=''&&data.requestDetails[i].semester!=null){
								var semester=data.requestDetails[i].semester;
								}else{
								var semester='-';
								}

								if(data.requestDetails[i].exam_name!=''&&data.requestDetails[i].exam_name!=null){
								var exam_name=data.requestDetails[i].exam_name;
								}else{
								var exam_name='-';
								}

								var resultFoundDropdown='<select class="form-control resultFound" name="resultFound[]" id="resultFound'+data.requestDetails[i].id+'" data-id="'+data.requestDetails[i].id+'">'+
															'<option value="" disabled>Select Result Status</option>'+
															'<option value="Pending" selected>Pending</option>'+
															'<option value="Correct">Correct</option>'+
															'<option value="Incorrect">Incorrect</option>'+
															/*'<option value="Return">Return</option>'+*/
														'</select>';

								var semesterDropdownAppend='<select class="form-control" name="semester[]" id="semester'+data.requestDetails[i].id+'">'+semesterDropdown+
														'</select>';

								var examDropdownAppend='<select class="form-control" name="exam[]" id="exam'+data.requestDetails[i].id+'">'+examDropdown+
														'</select>';

								var resultYearDropdownAppend='<select class="form-control" name="resultYear[]" id="resultYear'+data.requestDetails[i].id+'" data-id="'+data.requestDetails[i].id+'">'+
															'<option value="">Select Year</option>'+
															'<option value="1">1</option>'+
															'<option value="2">2</option>'+
															'<option value="3">3</option>'+
															'<option value="4">4</option>'+
															'<option value="5">5</option>'+
															'<option value="6">6</option>'+
															'<option value="7">7</option>'+
															'<option value="8">8</option>'+
														'</select>';

								$('#tableDocumentBodyUpdate').append('<tr>'+
															 	'<td>'+data.requestDetails[i].document_type+'<input type="hidden" name="document_id[]" value="'+data.requestDetails[i].id+'" id="document_id'+data.requestDetails[i].id+'"></td>'+
															 	'<td><a href="'+document_path+'" target="_blank">View File</a></td>'+
															 	'<td>'+data.requestData.device_type+'</td>'+
															 	'<td>'+resultFoundDropdown+'</td>'+
															 	'<td><textarea class="form-control input-lg" id="remark'+data.requestDetails[i].id+'" name="remark[]" type="text" autofocus>'+remark+'</textarea></td>'+
															 	'<td>'+examDropdownAppend+'</td>'+
															 	'<td>'+semesterDropdownAppend+'</td>'+
															 	'<td>'+resultYearDropdownAppend+'</td>'+
															 	'</tr>');

								$("#resultFound"+data.requestDetails[i].id).val(data.requestDetails[i].result_found_status);
								$("#semester"+data.requestDetails[i].id).val(data.requestDetails[i].semester);
								$("#exam"+data.requestDetails[i].id).val(data.requestDetails[i].exam_name);
								$("#resultYear"+data.requestDetails[i].id).val(data.requestDetails[i].doc_year);

								if(data.requestDetails[i].result_found_status=="Return"){
									$("#resultFound"+data.requestDetails[i].id).attr("disabled", true);
									$("#semester"+data.requestDetails[i].id).attr("disabled", true);
									$("#exam"+data.requestDetails[i].id).attr("disabled", true);
									$("#resultYear"+data.requestDetails[i].id).attr("disabled", true);
									$("#remark"+data.requestDetails[i].id).attr("disabled", true);
									$("#document_id"+data.requestDetails[i].id).attr("disabled", true);

									if(documentLength==1){
										$('#updateBtn').hide();
									}
								}
							}
						}else{
							$('#tableDocumentBodyUpdate').append('<tr><td colspan="6">Data Not Found</td></tr>');
						}
						$('.resultFound').on('change', function() {
						  //console.log( this.value );
						  // console.log($(this).data('id'));
						  $('#chk_box_holder'+$(this).data('id')).remove();
						  if(this.value=="Return"){

						  $('<div id="chk_box_holder'+$(this).data('id')+'" style="margin-top:5px;"><input type="checkbox" name="refund[]" id="refund_chk'+$(this).data('id')+'" value="'+$(this).data('id')+'" /><label style="color:#e91e63;">Refund Verification Fee</label></div>').insertAfter('#resultFound'+$(this).data('id'));
							}
						});
						$('#updateModel').modal('show');
					}
					else{

						toastr["error"](data.message);
					}



	},'json');
	//console.log('here2');

});
$("form#updateForm").submit(function(e){
	e.preventDefault();
	if (!$('#updateForm').valid())
	{
        return false;
	}
	else{
		var checkIfRetunIsSelectedFlag = checkIfRetunIsSelected();

		if(checkIfRetunIsSelectedFlag==2){
			toastr["error"]("For Return result remark is compulsory.");
		}else if(checkIfRetunIsSelectedFlag==1){
			var formData = new FormData($(this)[0]);
			formData.append("type", "updateRequest");
			bootbox.confirm("Are you sure you want to return document ?",function(result){
				if(result){
					$.ajax({
			            url: '/admin/old-verification/update-form',
			            type: 'POST',
			            data: formData,
			            success: function (data) {
			                if(data.type == 'success'){
			                            toastr["success"](data.message);

			                            $('#updateForm')[0].reset();
			                            oTable.ajax.reload();
			                            $('#updateModel').modal('hide');
			                          /*  setTimeout(function() {

			                            	 $("#resendBtnMsg").hide();
									        $("#resend-otp-btn").show();
									    }, 60000);*/
			                        }else{
			                        	toastr["error"](data.message);
			                        }
			            },
			            cache: false,
			            contentType: false,
			            processData: false,
			            dataType:'JSON'
			        });
				}

			});
	}else{
			var formData = new FormData($(this)[0]);
			formData.append("type", "updateRequest");
			$.ajax({
	            url: '/admin/old-verification/update-form',
	            type: 'POST',
	            data: formData,
	            success: function (data) {
	                if(data.type == 'success'){
	                            toastr["success"](data.message);

	                            $('#updateForm')[0].reset();
	                            oTable.ajax.reload();
	                            $('#updateModel').modal('hide');
	                          /*  setTimeout(function() {

	                            	 $("#resendBtnMsg").hide();
							        $("#resend-otp-btn").show();
							    }, 60000);*/
	                        }else{
	                        	toastr["error"](data.message);
	                        }
	            },
	            cache: false,
	            contentType: false,
	            processData: false,
	            dataType:'JSON'
	        });
	}

	}
});


$('#updateForm').validate({
	errorElement: 'div',
	errorClass: 'help-inline',
	focusInvalid: false,
	rules: {


		request_id: {minlength: 1,maxlength:255,required:true},
		student_institute:{required:true},
		student_degree:{required:true},
		student_branch:{required:true},
		passout_year:{required:true},
		student_reg_no:{minlength: 15,maxlength:15,required:true},
	},
	messages: {

		request_id:{required:"Request number is required"},
		student_institute:{required:"Institute is required"},
		student_degree:{required:"Student degree is required"},
		student_branch:{required:"Student branch is required"},
		passout_year:{required:"Passout year is required"},
		student_reg_no: {required:"Student registration no is required",minlength:"Student registration no should be of 15 characters",maxlength:"Student registration no should be of 15 characters"},

	},
	invalidHandler: function (event, validator) {},
	highlight: function (e) {},
	success: function (e) {},
	errorPlacement: function (error, element) {
		error.insertBefore(element);
	},
	submitHandler: function (form) {},
	invalidHandler: function (form) {}
});
$("#success-pill").click(function(){
	$('#refund_chk_filter_container').hide();
		oTable.ajax.url("<?= URL::route('oldverification.index',['payment_status'=>'Paid','verification_status'=>'Pending']) ?>");
		oTable.ajax.reload();
		//$('.loader').removeClass('hidden');
		 $('#refund_chk_filter'). prop("checked",false);
		$('.datetimepicker').datetimepicker( {
	    maxDate: moment(),
	    allowInputToggle: true,
	    enabledHours : false,
	    locale: moment().local('en'),
	    format: 'DD-MM-YYYY',
	    defaultDate: ''
	}).val('');
});
$("#fail-pill").click(function(){

	$('#refund_chk_filter_container').hide();

	oTable.ajax.url("<?= URL::route('oldverification.index',['payment_status'=>'Paid','verification_status'=>'Completed']) ?>");
	
	oTable.ajax.reload();
	//$('.loader').removeClass('hidden');
	 $('#refund_chk_filter'). prop("checked",false);
	$('.datetimepicker').datetimepicker( {
	    maxDate: moment(),
	    allowInputToggle: true,
	    enabledHours : false,
	    locale: moment().local('en'),
	    format: 'DD-MM-YYYY',
	    defaultDate: ''
	}).val('');
});
$("#submitFilter").click(function(){

	var refund_transactions=0;
 	var $parent = $('#nav-pill-class-pending');
    if (!$parent.hasClass('active')) {
      var status="Completed";
      if($('#refund_chk_filter'). prop("checked") == true){
		var refund_transactions=1;
	}
    }else{
    	 var status="Pending";
    }

	var fromDate=$('#fromDate').val();
	var toDate=$('#toDate').val();
	if((fromDate!=''&&toDate!='')||refund_transactions==1){

		oTable.ajax.url("/admin/old-verification?type=read&payment_status=Paid&verification_status="+status+"&fromDate="+fromDate+"&toDate="+toDate+"&refund_transactions="+refund_transactions);
		
	
	
	oTable.ajax.reload();
	}else{
		toastr["error"]("Please select from & to date or refund checkbox.");
	}
});
$("#clearFilter").click(function(){

	var $parent = $('#nav-pill-class-pending');
        if (!$parent.hasClass('active')) {
          var status="Completed";
        }else{
        	 var status="Pending";
        }
    $('#refund_chk_filter'). prop("checked",false);
	$('.datetimepicker').datetimepicker( {
	    maxDate: moment(),
	    allowInputToggle: true,
	    enabledHours : false,
	    locale: moment().local('en'),
	    format: 'DD-MM-YYYY',
	    defaultDate: ''
	}).val('');
	oTable.ajax.url("/admin/old-verification?payment_status=Paid&verification_status="+status);
	oTable.ajax.reload();
});
$("#generateReport").click(function(){
	var $parent = $('#nav-pill-class-pending');
	var token = "<?=csrf_token()?>";
        if (!$parent.hasClass('active')) {
          var status="Completed";
           if($('#refund_chk_filter'). prop("checked") == true){
			var refund_transactions=1;
		}else{
          var refund_transactions=0;
		}
        }else{
        	 var status="Pending";
        	  var refund_transactions=0;
        }
	 var newForm = jQuery('<form>', {
            'action': '<?=URL::route("oldverification.report.nonqr") ?>',
            'method':'POST',
            'target': '_top'
        }).append(jQuery('<input>', {
            'name': 'type',
            'value': 'generateReport',
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
            'name': 'status',
            'value': status,
            'type': 'hidden'
        })).append(jQuery('<input>', {
            'name': 'refund_transactions',
            'value': refund_transactions,
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

  var token = "<?=csrf_token()?>";
  var $parent = $('#nav-pill-class-pending');
      if (!$parent.hasClass('active')) {
        var status="Completed";
        if($('#refund_chk_filter'). prop("checked") == true){
    var refund_transactions=1;
  }else{
        var refund_transactions=0;
  }
      }else{
         var status="Pending";
         var refund_transactions=0;
      }
      var newForm = jQuery('<form>', {
          'action': '<?=URL::route("oldverification.report.summary") ?>',
          'method':'POST',
          'target': '_top'
      }).append(jQuery('<input>', {
          'name': 'type',
          'value': 'generateReportSummury',
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
          'name': 'status',
          'value': status,
          'type': 'hidden'
      })).append(jQuery('<input>', {
          'name': 'refund_transactions',
          'value': refund_transactions,
          'type': 'hidden'
      })).append(jQuery('<input>', {
            'name': '_token',
            'value': token,
            'type': 'hidden'
        }));

     $(document.body).append(newForm);
     newForm.submit();

});
function getDocumentVerificationCount(request_id){

    var url = "<?= URL::route('oldverification.document.count')?>";
    var method_type = 'POST';
    var token = "<?=csrf_token()?>";
    console.log('hoi');
  	$.ajax({
        type:method_type,
        url:url,
        data:{'request_id': request_id,'_token': token},
        dataType:'json',
        success: function(respObj) {
        	console.log(respObj);
        	// return respObj;
            /*if(respObj.success == true)
            {
                $('#permission_add').html(respObj.html);
            }*/
        }
    });
}
function checkIfRetunIsSelected(){

	var resultFoundInput = document.getElementsByName('resultFound[]');

	for (i=0; i<resultFoundInput.length; i++)
	{
		console.log(resultFoundInput[i].dataset.id);
	 if (resultFoundInput[i].value == "Return")
	    {
	    	var remark=$('#remark'+resultFoundInput[i].dataset.id).val();
	   if(remark==''){
	   	return 2;
	   }
	     return 1;
	    }


	}
	return 0;
}
</script>
@stop
@section('style')
<style type="text/css">
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