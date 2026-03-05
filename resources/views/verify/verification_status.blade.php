<?php 
$domain =$_SERVER['HTTP_HOST'];
$subdomain = explode('.', $domain);
 ?>
@extends('verify.layout.layout')
@section('content')
<h1 class="page-header"><i class="fa fa-envira"></i> Verification Status
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
		<div class="row">
			<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
			<div class="input-group date" data-provide="datepicker">
			    <input type="text" class="form-control datetimepicker" name="fromDate" id="fromDate" >
			    <div class="input-group-addon">
			        <span class="glyphicon glyphicon-th"></span>
			    </div>
			</div>
			</div>
			<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
				<div class="input-group date" data-provide="datepicker" value="">
			    <input type="text" class="form-control datetimepicker" name="toDate" id="toDate" >
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

		<table id="example" class="table table-hover display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>#</th>
					<th>Request No. </th>
					<th>Student Name </th>
					<th>No. Of Documents </th>
					<th>Submitted Date </th>
					<th>Paid Amount </th>
					<th>Verification Status </th>
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
      	   				<?php if($subdomain[0] == "galgotias"||$subdomain[0] == "monad"){ ?> University <?php }else{ ?>Institute <?php } ?>
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
<script>
$(document).ready(function(){

      var oTable = $('#example').DataTable( {
        "processing": true,
        "serverSide": true,
         "sAjaxSource": "{{URL::route('verify.verification.status')}}",
        "order": [[6, 'desc']],
            "bInfo":false,
            "aoColumns": [
        
              { "mData": "rownum",sWidth: "7%"},
              { "mData": "request_number",sWidth: "15%",bSortable: true},
              { "mData": "student_name",sWidth: "20%",bSortable: true},
              { "mData": "no_of_documents",sWidth: "10%",bSortable: true},
              { "mData": "created_date_time",sWidth: "20%",bSortable: true},
              { "mData": "total_amount",sWidth: "13%",bSortable: true},
              { "mData": "verification_status",sWidth: "15%",bSortable: true},
              { 
                  "mData": "id",
                  bSortable:false,
                  sWidth: "15%",
                  mRender:function(v, t, o){
                       var buttons = '<span data-toggle="tooltip" title="info" id="infoData" class="" data-id="'+v+'"><i class="fa fa-info-circle fa-lg blue"></i></span> &nbsp;&nbsp;';


                        return buttons;
                  }
            },
          ],

      });


      //info data
      oTable.on('click','#infoData',function(e){

            $id = $(this).data('id');
            var token="{{ csrf_token() }}";
            $.post("{{URL::route('verify.verification.status.info')}}",
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
                              // $('#offerLetterView').html('<a href="'+data.requestData.offer_letter+'" target="_blank">View File</a>');
                              $('#offerLetterView').html('<a href="<?= Config::get('constant.local_base_path').$subdomain[0]?>/backend/'+data.requestData.offer_letter+'" target="_blank">View File</a>');
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
                                    // var document_path=''+data.requestDetails[i].document_path;
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
                                   $('#tableDocumentBody').append('<tr>'+'<td>'+data.requestDetails[i].document_type+'</td>'+'<td><a href="'+document_path+'" target="_blank">View File</a></td>'+'<td style="color:'+statusClr+'">'+data.requestDetails[i].result_found_status+'</td>'+'<td>'+remark+'</td>'+'<td>'+exam_name+'</td>'+'<td>'+semester+'</td>'+'</tr>');
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


$("#submitFilter").click(function(){
            var fromDate=$('#fromDate').val();
            var toDate=$('#toDate').val();
            if(fromDate!=''&&toDate!=''){

                  
            oTable.ajax.url("/verify/verification-status?fromDate="+fromDate+"&toDate="+toDate);
            
            
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
      oTable.ajax.url("");
            oTable.ajax.reload();
      });
});
$('a[data-url^="documentverificationrequests"]').parent().addClass('active');
</script>
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
</style>

@stop