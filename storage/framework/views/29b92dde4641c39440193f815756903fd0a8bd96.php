<?php $__env->startSection('content'); ?>

<?php $domain = \Request::getHost();
$subdomain = explode('.', $domain);
//print_r($subdomain);
//echo session()->get('user_id');

if(!isset($inputInfo['PAYMENTMODE'])){
$inputInfo['PAYMENTMODE']='';
}
//print_r($verification_requests);

//print_r($inputInfo);
?>

<?php if($inputInfo['STATUS'] == 'TXN_SUCCESS'): ?>
	
	<div class="row">
        <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
           <h2>Transaction Successful</h2>
         	<p style="text-align: justify;">Request for educational details verification of <b><?php echo e($verification_requests['student_name']); ?></b> is received to us. Institute will convey you the verification details within 24 hours.<br><br>
            Your request number is <b><?php echo e($request_number); ?></b> Remember this number for our future communication references.<br><br>
            Further details are sent to your submitted email id and can be found in <b>“Request Status”</b> tab.<br>
           	<h5>Your transaction has completed successfully and details are provided below.</h5>
            <table class="table table-bordered table-hover">
              	<tr>
                    <th>Transaction ID</th>
                    <td class="text-left"><?php echo e($inputInfo['ORDERID']); ?></td>
              	</tr>
             	<tr>
                    <th>Gateway ID</th>
                    <td class="text-left"><?php echo e($inputInfo['TXNID']); ?></td>
              	</tr>
             	<tr>
                    <th>Date</th>
                    <td class="text-left"><?php echo e(date('Y-m-Y')); ?></td>
              	</tr>
             	<tr>
                    <th>Mode</th>
                    <td class="text-left"><?php echo e($inputInfo['PAYMENTMODE']); ?></td>
              	</tr>
                <?php if($subdomain[0]!="demo"&&$subdomain[0]!="monad"){?>
              	<tr>
                    <th>Email</th>
                    <td class="text-left"><?php echo e($email_id); ?></td>
              	</tr>
              	<tr>
                    <th>Phone</th>
                    <td class="text-left"><?php echo e($mobile_no); ?></td>
              	</tr>
                <?php }?>
              	<tr>
                    <th>Amount</th>
                    <td class="text-left"><i class="fa fa-rupee"></i><?php echo e($inputInfo['TXNAMOUNT']); ?></td>
              	</tr>
            </table>
             <div id="countTable" style="margin: auto;text-align: center;"></div>
        </div>
        </div>
       
        <div id="pdfDiv" style="text-align: center;">
          <div id="loader" style="position: absolute;
    font-size: 25px;
    left: 30%;
    margin-top: -10%;
    border: 2px solid darkred;
    padding: 5px;
    color: darkred;
    border-radius: 5px;    background-color: #f0f0f0;">Please wait... Fetching details...</div>
    </div>
<?php else: ?>



<div class="row">
   <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
   <h2>Transaction Failed</h2>
      <p style="text-align: justify;">Request for educational details verification of <b><?php echo e($verification_requests['student_name']); ?></b> is received to us. Institute will convey you the verification details within 24 hours.<br><br>


        Your request number is <b><?php echo e($request_number); ?></b> Remember this number for our future communication references.<br><br>


        <span style="color:red;">Your transaction has been failed. You can do the payment by switching to <b>“Pending Payment”</b> tab.</span><br>
        </p>
        <h5>Your transaction failed and details are provided below.</h5>
        <table class="table table-bordered table-hover">
          	<tr>
            	<th>Transaction ID</th>
            	<td class="text-left"><?php echo e($inputInfo['ORDERID']); ?></td>
          	</tr>
          	<tr>
	            <th>Gateway ID</th>
	            <td class="text-left"><?php echo e($inputInfo['TXNID']); ?></td>
          	</tr>
          	<tr>
	            <th>Date</th>
	            <td class="text-left"><?php echo e(date('Y-m-Y')); ?></td>
          	</tr>
          	<tr>
	            <th>Mode</th>
	            <td class="text-left"><?php echo e($inputInfo['PAYMENTMODE']); ?></td>
          	</tr>
        	<tr>
                <th>Email</th>
                <td class="text-left"><?php echo e($email_id); ?></td>
          	</tr>
          	<tr>
                <th>Phone</th>
                <td class="text-left"><?php echo e($mobile_no); ?></td>
          	</tr>
        </table>
    </div>
</div>         
              
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>



<script>
  function update(){
   var ajaxURL = '<?php echo e(URL::route("request.verification.add.transaction")); ?>';
    var token = '<?php echo e(csrf_token()); ?>';
    $.post(ajaxURL,{
      'action':'create',
      '_token':token,
      'trans_id_ref':'<?php echo $inputInfo['ORDERID'] ?>',
      'trans_id_gateway':'<?php echo $inputInfo['TXNID'] ?>',
      'payment_mode':'<?php echo $inputInfo['PAYMENTMODE'] ?>',
      'amount':'<?php echo $inputInfo['TXNAMOUNT'] ?>',
      'additional':'0',
      'user_id':'<?php echo $user_id; ?>',
      'student_key':'<?php echo $request_number; ?>',
      'trans_status': '<?php echo $inputInfo["STATUS"] == "TXN_SUCCESS" ? 1 : 0;  ?>'
    },function(data){
      $('#loader').hide();
      //console.log(data);
      if(data.status==true){
          let invalidCount=0;
          let validCount=0;
            if(data.showPdf==true){
            var length=data.dataPdf.length;
              for (var i=0; i<length; i++){

                if(data.dataPdf[i]['certificate_filename']==null){
                  invalidCount=invalidCount+1;
                   $('#pdfDiv').append('<div style="color: red;padding: 5px;border: 1px solid red;background-color: #f0f0f0;">Certificate not verified.</div><hr>');
                }else{
                  validCount=validCount+1;
                let link='<?php  echo Config::get('constant.local_base_path').$subdomain[0]; ?>/backend/pdf_file/'+data.dataPdf[i]['certificate_filename'];
                $('#pdfDiv').append('<iframe src="'+link+'?page=hsn#toolbar=0" width="810" height="780"></iframe><hr>');
                }
              }
          }

          $('#countTable').html('<table class="table table-bordered table-hover"><tr><th style="text-align: center;">Valid Documents</th><th style="text-align: center;">Invalid Documents</th></tr><tr><td>'+validCount+'</td><td>'+invalidCount+'</td></tr></table>');
      }
    },'JSON');
  }
$(document).ready(function(){
  update();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('verify.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/payment/paytm_success_qr.blade.php ENDPATH**/ ?>