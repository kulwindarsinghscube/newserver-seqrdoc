<?php

// dd(@$student->studentAckLogs->fn_hi_status == 1 || empty(@$student->studentAckLogs));
?>


<?php $__env->startSection('content'); ?>
   
        <div class="row">
        
            <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
                <?php if($inputInfo['STATUS'] == 'TXN_SUCCESS'): ?>
                    <h2>Transaction Successful</h2>
                    <h5>Your transaction has completed successfully and details are provided below.</h5>
                <?php elseif($inputInfo['STATUS'] == 'TXN_FAILURE'): ?>
                    <h2>Transaction Failed</h2>
                    <h5>Your transaction has failed and details are provided below.</h5>
                <?php else: ?>
                    <h2>Transaction Pending</h2>
                    <h5>Your transaction is currently being processed.</h5>
                    <p>Your transaction is under review and is expected to be completed within 24 to 48 hours. We are working diligently to ensure everything is processed smoothly.</p>
                
                <?php endif; ?>
                <table class='table table-bordered table-hover'>
                    <tr>
                        <th>Order ID</th>
                        <td class="text-left"><?php echo e(@$inputInfo['ORDERID']); ?></td>
                    </tr>
                    <tr>
                        <th>Transaction ID</th>
                        <td class="text-left"><?php echo e(@$inputInfo['TXNID']); ?></td>
                    </tr>
                    <tr>
                        <th>Bank Transaction ID</th>
                        <td class="text-left"><?php echo e(@$inputInfo['BANKTXNID']); ?></td>
                    </tr>
                    <?php if(@$inputInfo['TXNDATE']): ?>
                    <tr>
                        <th>Date</th>
                        <td class="text-left">
                            <?php echo e(date('d-m-Y h:i A', strtotime($inputInfo['TXNDATE']))); ?>

                        </td>
                    </tr> 
                    <?php endif; ?>
                    <tr>
                        <th>Mode</th>
                        <td class="text-left"><?php echo e(@$inputInfo['PAYMENTMODE']); ?></td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td class="text-left"><i class="fa fa-rupee"></i> <?php echo e(@$inputInfo['TXNAMOUNT']); ?></td>
                    </tr>
                </table>
                <p class="text-center">
                    <a href="<?php echo e(url('/convo_student/dashboard')); ?>"   class="btn btn-theme" style="color:#fff">
                        <?php if($inputInfo['STATUS'] == 'TXN_SUCCESS'): ?>
                            View Details To Approve PDF
                        <?php else: ?>
                            View Details
                        <?php endif; ?>
                    </a>
                    <a onclick="window.print();" class="btn btn-theme" style="color:#fff">
                        Print
                    </a>
                </p>
            </div>
        </div>

        <?php $__env->startSection('script'); ?>
            <script></script>
        <?php $__env->stopSection(); ?>
    
<?php $__env->stopSection(); ?>

<?php echo $__env->make('convodataverification.student.pages.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/convodataverification/student/pages/payment_status.blade.php ENDPATH**/ ?>