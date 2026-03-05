<?php $__env->startSection('content'); ?>


<?php if($ResultCode == '0'): ?>
<div class="row">
  <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
    <h2>Transaction Successful</h2>
    <h5>Your transaction has completed successfully and details are provided below.</h5>
    <table class='table table-bordered table-hover'>
      <tr>
        <th>Transaction ID</th>
        <td class="text-left"><?php echo e($CheckoutRequestID); ?></td>
      </tr>
      <tr>
        <th>Date</th>
        <td class="text-left"><?php echo e(date('d-m-Y')); ?></td>
      </tr>
      <tr>
        <th>Mode</th>
        <td class="text-left">Mpesa</td>
      </tr>
      <tr>
        <th>Amount</th>
        <td class="text-left">1 KES</td>
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
    
    var ajaxURL = "<?= route('mpesa.transactionUpdate')?>";
    var token = "<?php echo e(csrf_token()); ?>";
    var txnid = "<?php echo e($CheckoutRequestID); ?>";
    
    $.ajax({
        url : ajaxURL,
        dataType: "json",
        type: "POST",
        data:{
          '_token':token,
          'trans_id_ref' : txnid,
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
        <td class="text-left"><?php echo e($CheckoutRequestID); ?></td>
      </tr>
      <tr>
        <th>Date</th>
        <td class="text-left"><?php echo e(date('d-m-Y')); ?></td>
      </tr>
      <tr>
        <th>Mode</th>
        <td class="text-left">mPesa</td>
      </tr>
      <tr>
        <th>Amount</th>
        <td class="text-left">1 KES</td>
      </tr>
    </table>
    <p class="text-center">You can close this tab and return to previous page to try again.</p>
    <p class="text-center"><a href="#" onclick="window.close()" class="btn btn-theme" style="color:#fff"> Close</a></p>
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
    
  var ajaxURL = "<?= route('mpesa.transactionUpdate')?>";
  var token = "<?php echo e(csrf_token()); ?>";
  var txnid = "<?php echo e($CheckoutRequestID); ?>";
  
  $.ajax({
      url : ajaxURL,
      dataType: "json",
      type: "POST",
      data:{
        '_token':token,
        'trans_id_ref' : txnid,
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
<?php echo $__env->make('webapp.layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/webapp/payment/mPesaStatus.blade.php ENDPATH**/ ?>