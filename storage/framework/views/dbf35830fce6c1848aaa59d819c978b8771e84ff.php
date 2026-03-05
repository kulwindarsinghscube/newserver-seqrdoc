<?php $__env->startSection('content'); ?>
<?php
$domain =$_SERVER['HTTP_HOST'];
$subdomain = explode('.', $domain);
 ?>

	<?php if($inputInfo['STATUS'] == 'TXN_SUCCESS'): ?>

		<div class="row">
           <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
           <h2>Transaction Successful</h2>
              <p style="text-align: justify;">Request for educational details verification of <b><?php echo e($verification_requests['student_name']); ?></b> is received to us. <?php if($subdomain[0] == "galgotias"||$subdomain[0] == "demo"||$subdomain[0] == "monad"){ ?>University <?php }else{ ?>Institute<?php } ?> will convey you the verification details within 24 hours.<br><br>


                Your request number is <b><?php echo e($request_number); ?></b> Remember this number for our future communication references.<br><br>


                Further details are sent to your submitted email id and can be found in <b>“Request Status”</b> tab.<br>

            <p class="text-center"><a href="/verify/verification-status" class="btn btn-theme" style="color:#fff"> Check Request Status</a></p>
            <input type="button"
               name="backButton"
               value="Close"
               style="background-color:#245f91; color:#fff;font-weight:bold;"
               onclick="window.close();">
          </div>
        </div> 
	<?php else: ?>

		<div class="row">
           <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
           <h2>Transaction Failed</h2>
              <p style="text-align: justify;">Request for educational details verification of <b><?php echo e($verification_requests['student_name']); ?></b> is received to us. <?php if($subdomain[0] == "galgotias"||$subdomain[0] == "demo"||$subdomain[0] == "monad"){ ?>University <?php }else{ ?>Institute<?php } ?> will convey you the verification details within 24 hours.<br><br>


                Your request number is <b><?php echo e($request_number); ?></b> Remember this number for our future communication references.<br><br>


                <span style="color:red;">Your transaction has been failed. You can do the payment by switching to <b>“Pending Payment”</b> tab.</span><br>
                </p>
                <input type="button"
                 name="backButton"
                 value="Close"
                 style="background-color:#245f91; color:#fff;font-weight:bold;"
                 onclick="window.close();">
            </div>
        </div>
	<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>
<?php $__env->stopSection(); ?>

<?php 
if(!isset($inputInfo['PAYMENTMODE'])){
	$inputInfo['PAYMENTMODE']='';
}

?>
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
    },function(){

    });
  }
$(document).ready(function(){
  update();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('verify.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/payment/paytm_success.blade.php ENDPATH**/ ?>