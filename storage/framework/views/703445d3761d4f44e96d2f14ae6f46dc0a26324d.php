<?php $__env->startSection('content'); ?>
<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

if(isset($inputInfo)){
	$response = $inputInfo;
	
	/* It is very important to calculate the hash using the returned value and compare it against the hash that was sent while payment request, to make sure the response is legitimate */
	$salt = \Config::get('constant.omniware_salt'); /* put your salt provided by Omniware here */
	if(isset($salt) && !empty($salt)){
		$response['calculated_hash']=hashCalculate($salt, $response);
		$response['valid_hash'] = ($response['hash']==$response['calculated_hash'])?'Yes':'No';
	} else {
		$response['valid_hash']='Set your salt in return_page.php to do a hash check on receiving response from Omniware';
	}
}

function hashCalculate($salt,$input){
	/* Remove hash key if it is present */
	unset($input['hash']);
	/*Sort the array before hashing*/
	ksort($input);
	
	/*first value of hash data will be salt*/
	$hash_data = $salt;
	
	/*Create a | (pipe) separated string of all the $input values which are available in $hash_columns*/
	foreach ($input as $key=>$value) {
		if (strlen($value) > 0) {
			$hash_data .= '|' . $value;
		}
	}

	$hash = null;
	if (strlen($hash_data) > 0) {
		$hash = strtoupper(hash("sha512", $hash_data));
	}
		
	return $hash;
}


?>

<?php if($response['response_code'] == 0 || $response['response_code'] == '0'): ?>
<div class="row">
  <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
    <h2>Transaction Successful</h2>
    <h5>Your transaction has completed successfully and details are provided below.</h5>
    <table class='table table-bordered table-hover'>
      <tr>
        
        <th>Transaction ID</th>
        <td class="text-left"><?php echo e($response['order_id']); ?></td>
      </tr>
      <tr>
        <th>Gateway ID</th>
        <td class="text-left"><?php echo e($response['transaction_id']); ?></td>
      </tr>
      <tr>
        <th>Date</th>
        <td class="text-left"><?php echo e(date('d-m-Y')); ?></td>
      </tr>
      <tr>
        <th>Mode</th>
        <td class="text-left"><?php echo e($response['payment_mode']); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td class="text-left"><?php echo e($response['amount']); ?><i class="fa fa-rupee"></i></td>
      </tr>
    </table>
    <p class="text-center">You can close this tab and return to previous page to try again.</p>
    <p class="text-center"><a href="#" onclick="pdfView()" class="btn btn-theme" style="color:#fff"> Close</a></p>
  </div>
</div>	

<?php $__env->startSection('script'); ?>
<script>
  function pdfView(){
    var session_key = '<?= $session_key?>';
    sessionStorage.setItem('qrCodeKey',session_key)
    var ajaxURL = "<?php echo e(URL::route('webapp.dashboard')); ?>";
    // ajaxURL = ajaxURL.replace(':id',session_key)
    window.location.href = ajaxURL;
  }


  function update(){
    
    var ajaxURL = "<?= route('rohitPayment.transaction')?>";
    var token = "<?php echo e(csrf_token()); ?>";
    var txnid = "<?php echo e($response['order_id']); ?>";
    var mihpayid = "<?php echo e($response['transaction_id']); ?>";
    var mode = "<?php echo e($response['payment_mode']); ?>";
    var amount = "<?php echo e($response['amount']); ?>";
    var user_id = "<?php echo e($user_id); ?>";
    var productinfo = "<?php echo e($session_key); ?>";
    
    $.ajax({
        url : ajaxURL,
        dataType: "json",
        type: "POST",
        data:{
          
          '_token':token,
          'trans_id_ref' : txnid,
          'trans_id_gateway' : mihpayid,
          'payment_mode' : mode,
          'amount' : amount,
          'additional' : '0',
          'user_id' : user_id,
          'student_key' : productinfo,
          'trans_status' : '1'
        },
        success: function (data) {        
         
        }
      });
  }
$(document).ready(function(){
  update();
});
</script>
<?php $__env->stopSection(); ?>

<?php else: ?>
  
  <div class="row">
  <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
    <h2>Transaction Failed</h2>
    <h5>Your transaction has failed and details are provided below.</h5>
    <table class='table table-bordered table-hover'>
      <tr>
       
        <th>Transaction ID</th>
        <td class="text-left"><?php echo e($response['order_id']); ?></td>
      </tr>
      <tr>
        <th>Gateway ID</th>
        <td class="text-left"><?php echo e($response['transaction_id']); ?></td>
      </tr>
      <tr>
        <th>Date</th>
        <td class="text-left"><?php echo e(date('d-m-Y')); ?></td>
      </tr>
      <tr>
        <th>Mode</th>
        <td class="text-left"><?php echo e($response['payment_mode']); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td class="text-left"><?php echo e($response['amount']); ?><i class="fa fa-rupee"></i></td>
      </tr>
    </table>
    <p class="text-center">You can close this tab and return to previous page to try again.</p>
    <p class="text-center"><a href="javascript:void(0)" onclick="window.close()" class="btn btn-theme" style="color:#fff"> Close</a></p>
  </div>
</div>  

<?php $__env->startSection('script'); ?>
<script>

  function update(){
    
    var ajaxURL = "<?= route('omniware.transaction')?>";
    var token = "<?php echo e(csrf_token()); ?>";
    var txnid = "<?php echo e($response['order_id']); ?>";
    var mihpayid = "<?php echo e($response['transaction_id']); ?>";
    var mode = "<?php echo e($response['payment_mode']); ?>";
    var amount = "<?php echo e($response['amount']); ?>";
    var user_id = "<?php echo e($user_id); ?>";
    var productinfo = "<?php echo e($session_key); ?>";
    
    $.ajax({
        url : ajaxURL,
        dataType: "json",
        type: "POST",
        data:{
          '_token':token,
          'trans_id_ref' : txnid,
          'trans_id_gateway' : mihpayid,
          'payment_mode' : mode,
          'amount' : amount,
          'additional' : '0',
          'user_id' : user_id,
          'student_key' : productinfo,
          'trans_status' : '0'
        },
        success: function (data) {        
         
        }
      });
  }
$(document).ready(function(){
  update();
});
</script>
<?php $__env->stopSection(); ?>

<?php endif; ?>  


<?php $__env->stopSection(); ?>
<?php echo $__env->make('webapp.layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/webapp/rohit_payment/omniwareStatus.blade.php ENDPATH**/ ?>