<?php $__env->startSection('content'); ?>
<form action="<?= route('payment.gateway.paystack') ?>" method="post" name="paystackForm" >
      <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
      <input type="hidden" name="student_name" value="<?php echo e($student_name); ?>">
      <input type="hidden" name="key" value="<?php echo e($key); ?>">
      <input type="hidden" name="amount" value="<?php echo e($amount); ?>">
</form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

<script type="text/javascript">
      var paystackForm = document.forms.paystackForm;
      paystackForm.submit();  
      // $(document).payuForm.submit();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('webapp.layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/webapp/payment/paystackPost.blade.php ENDPATH**/ ?>