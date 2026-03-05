<?php if(Session::has('message')): ?>
    <?php if( 'danger' == Session::get('message_type') ): ?>
    <script>
        $(document).ready(function(){
            toastr.error
            ("<?php echo e(Session::get('message')); ?>");
        });
    </script>
    <?php elseif( 'success' == Session::get('message_type') ): ?>
    <script>
        $(document).ready(function(){
            toastr.success
            ("<?php echo e(Session::get('message')); ?>");
        });
    </script>
    <?php elseif( 'warning' == Session::get('message_type') ): ?>
    <script>
        $(document).ready(function(){
            toastr.warning
            ("<?php echo e(Session::get('message')); ?>");
        });
        </script>
        <?php elseif( 'info' == Session::get('message_type') ): ?>
    <script>
        $(document).ready(function(){
            toastr.info
            ("<?php echo e(Session::get('message')); ?>");
        });
    </script>
    <?php endif; ?>
<?php endif; ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/partials/alert.blade.php ENDPATH**/ ?>