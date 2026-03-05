<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 text-center"> 
            <h1 class="page-header d-inline-block">
                <i class="fa fa-file-pdf-o"></i> Word to PDF Converter
            </h1>
        </div>
    </div>

    <form id="wordToPdfForm" enctype="multipart/form-data" class="mt-4">
        <?php echo csrf_field(); ?>
<div class="form-group row justify-content-center">
                <label class="col-sm-5 col-form-label text-right">Upload Word File:</label>
            <div class="col-sm-4"> 
                <input type="file" class="form-control" name="field_file" id="field_file" required>
            </div>
        </div>

        <div class="form-group row justify-content-center">
            <div class="col-sm-10 text-center">
                <button type="button" class="btn btn-primary" id="btnConvert">Convert to PDF</button>
            </div>
        </div>
    </form>

    <!-- Messages -->
    <div class="mt-3 text-center">
        <div id="loader" class="alert alert-warning" style="display:none;">
            Converting... Please wait <img src="/backend/images/loading.gif" height="20">
        </div>
        <div id="successMessage" class="alert alert-success" style="display:none;"></div>
        <div id="errorMessage" class="alert alert-danger" style="display:none;"></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
$('#btnConvert').click(function(){
    let formData = new FormData($('#wordToPdfForm')[0]);

    $('#loader').show();
    $('#successMessage').hide();
    $('#errorMessage').hide();

    $.ajax({
        url: "<?php echo e(route('wordtopdf.uploadfile')); ?>",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(resp){
            $('#loader').hide();
            if(resp.success){
                $('#successMessage').html(resp.message + '<br><a href="'+resp.link+'" target="_blank" download class="btn btn-success mt-2">Download PDF</a>').show();
            } else {
                $('#errorMessage').text(resp.message).show();
            }
        },
        error: function(xhr){
            $('#loader').hide();
            $('#errorMessage').text("Upload failed. Please try again.").show();
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/wordtopdf/index.blade.php ENDPATH**/ ?>