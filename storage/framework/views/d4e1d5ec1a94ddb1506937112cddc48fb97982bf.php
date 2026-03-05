<?php $__env->startSection('content'); ?>
<h1 class="page-header"><i class="fa fa-envira"></i> Scan History
<span style="font-family:roboto;font-weight:500;font-size:14px;color:#777;display:block;margin:10px 0;">
A consolidated list of scans made from various devices.
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
					<th>No. Of Documents </th>
					<th>Device Type </th>
					<th>Submitted Date </th>
					<th>Paid Amount </th>
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
      	   	<div class="col-lg-12">Scanned Documents :</div>

      	   	<div style="margin: 20px;margin-top: 40px;">
      	   		<table class="table files-table" >
      	   			<tr>
	      	   			<th class="file-head" id="th1">Pdf File</th>
	      	   			<th class="file-head" id="th2">QR Code</th>
      	   			</tr>
      	   			<tbody id="tableDocumentBody">
      	   			<tr>
	      	   			<td>File Url</td>
	      	   			<td>View QR</td>

      	   			</tr>
      	   			</tbody>
      	   		</table>

      	   	</div>

      	</div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-theme" data-dismiss="modal" style="color: #fff;">Close</button>
      </div>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
	var oTable = $('#example').DataTable( {
        "processing": true,
        "serverSide": true,
         "sAjaxSource": "<?php echo e(URL::route('verify.scan.history')); ?>",
        "order": [[2, 'desc']],
            "bInfo":false,
            "aoColumns": [
        
              { "mData": "rownum",sWidth: "7%"},
              { "mData": "request_number",sWidth: "15%",bSortable: true},
              { "mData": "no_of_documents",sWidth: "15%",bSortable: true},
              { "mData": "device_type",sWidth: "20%",bSortable: true},
              { "mData": "created_date_time",sWidth: "20%",bSortable: true},
              { "mData": "total_amount",sWidth: "13%",bSortable: true},
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
	
	oTable.on('click','#infoData',function(e){
		$id = $(this).data('id');
		var token="<?php echo e(csrf_token()); ?>";
		$.post("<?php echo e(URL::route('verify.scan.history.info')); ?>",
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


				$('#submissionDateTimeView').html(data.requestData.created_date_time);
				$('#paymentTransactionIdView').html(data.requestData.payment_transaction_id);
				$('#paymentGatewayIdView').html(data.requestData.payment_gateway_id);
				$('#modeView').html(data.requestData.payment_mode);
				$('#amountView').html(data.requestData.amount);
				$('#paymentDateTimeView').html(data.requestData.payment_date_time);
				


				$('#tableDocumentBody').empty();
				var documentLength=data.requestDetails.length;

				if(documentLength>0){
					var subdomain="<?php echo $subdomain[0];?>";
					if(subdomain=="monad"||subdomain=="demo"){
						$('#th2').hide();
						if(data.requestData.data_type=="qr"){
							$('#th1').html('QR Code');
							for(var i=0;i<documentLength;i++){
								if(data.requestDetails[i].qr_code_path!=null){
								$('#tableDocumentBody').append('<tr>'+'<td><img src="<?= Config::get('constant.local_base_path').$subdomain[0]?>/backend/canvas/images/'+data.requestDetails[i].qr_code_path+'" style="height:100px;width:100px;object-fit:contain;border: 1px solid #ddd;border-radius: 4px;margin-bottom: 10px;" /></td>'+
								 	'</tr>');
								}else{
									$('#tableDocumentBody').append('<tr>'+'<td>QR Text : [ '+data.requestDetails[i].document_key+' ]</td>'+
							 	'</tr>');
								}
							}

						}else{
							$('#th1').html('Serial No');
							for(var i=0;i<documentLength;i++){

							$('#tableDocumentBody').append('<tr>'+'<td>'+data.requestDetails[i].document_key+'</td>'+
							 	'</tr>');
							}
						}
						

					}else{
						for(var i=0;i<documentLength;i++){

							$('#tableDocumentBody').append('<tr>'+
							 	'<td><img src="<?= Config::get('constant.local_base_path')?>/backend/images/pdf.png" style="height:75px;width:75px;object-fit:contain;border: 1px solid #ddd;border-radius: 4px;margin-bottom: 10px;" /><br><button style="color: #337ab7;background-color: #fff;border: 1px solid #fff;" class="showFile">View File</button></td>'+
							 	'<td><img src="<?= Config::get('constant.local_base_path').$subdomain[0]?>/backend/canvas/images/'+data.requestDetails[i].qr_code_path+'" style="height:100px;width:100px;object-fit:contain;border: 1px solid #ddd;border-radius: 4px;margin-bottom: 10px;" /></td>'+
							 	'</tr>'+
							 	'<tr style="display:none;">'+
							 	'<td  colspan="2"><iframe src="<?= Config::get('constant.local_base_path').$subdomain[0]?>/backend/pdf_file/'+data.requestDetails[i].pdf_path+'?page=hsn#toolbar=0" width="810" height="780"></iframe><button style="margin-top: -10px;position: absolute;right: 20px;color: indianred;background-color: #fff;border: 1px solid indianred;" onclick="this.parentElement.parentElement.style.display=\'none\';">Close</button></td>'+
							 	'</tr>');
						}

					}
				}else{
					$('#tableDocumentBody').append('<tr><td colspan="6">Data Not Found</td></tr>');
				}

					$('#viewDetailsModel').modal('show');
				}
				else{
					
					toastr["error"](data.message);
				}

				$("button.showFile").click(function() {

			  		$(this).closest("tr").next().show();
				});

		},'json');
	});
$("#submitFilter").click(function(){
		var fromDate=$('#fromDate').val();
		var toDate=$('#toDate').val();
		if(fromDate!=''&&toDate!=''){
		oTable.ajax.url("/verify/scan-history?type=read&fromDate="+fromDate+"&toDate="+toDate);
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
	oTable.ajax.url("/verify/scan-history");
	oTable.ajax.reload();
	});



$('a[data-url^="scanhistory"]').parent().addClass('active');
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('verify.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/scan_history.blade.php ENDPATH**/ ?>