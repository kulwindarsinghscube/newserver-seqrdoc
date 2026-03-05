<?php $__env->startSection('content'); ?>
  <div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-fw fa-gears"></i> Payment Gateway Configuration New
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('pg_newconfig')); ?></ol>
				<i class="fa fa-info-circle iconModalCss" title="User Manual" id="pgConfigurationClick"></i>
				</h1>
			</div>
		</div>
		<div class="panel panel-primary" style="margin: 0 auto; width: 560px;">
			<div class="panel-heading"><i class="fa fa-gears fa-fw"></i> Payment Gateway Configuration</div>
			<div class="panel-body">
				<form  id="update_data">
					<div class="form-group">
						<label for="opt_wl">Payment Gateway</label>
						<select class="form-control" id="opt_wl" name="opt_wl" data-rule-required="true" data-live-search="true">
							<option value="">select</option>
						</select>
						<span id="opt_wl_error" class="help-inline text-danger"><?=$errors->first('opt_wl')?></span>
					</div>
					<div class="form-group" id="opt1">
						<label for="opt_pg">Payment status</label>
						<select name="opt_pg" id="opt_pg" class="form-control" data-rule-required="true">
							<option value="">Select</option>
							<option value="1">Enable</option>
							<option value="0">Disable</option>
						</select>
						<span id="opt_pg_error" class="help-inline text-danger"><?=$errors->first('opt_pg')?></span>
					</div>
					<div class="form-group" id="opt2">
						<label for="amt_charge">Amount to charge</label>
						<input type="text" class="form-control allow_number" id="amt_charge" name="amt_charge" data-rule-required="true" value="">
						<span id="amt_charge_error" class="help-inline text-danger"><?=$errors->first('amt_charge')?></span>
					</div>
					<div class="form-group" id="opt3">
						<label for="opt_crenden">Crendentials</label>
						<select name="opt_crenden" id="opt_crenden" class="form-control" data-rule-required="true">
							<option value="">Select</option>
							<option value="0">Test</option>
							<option value="1">Live</option>
						</select>
						<span id="opt_crenden_error" class="help-inline text-danger"><?=$errors->first('opt_crenden')?></span>
					</div>
					<div class="form-group clearfix">
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('pg_newconfig.update')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('pg_newconfig.update')): ?>
									<button type="button" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="update_save"><i class="loadsave"></i> Update</button>
						<?php endif; ?>
						<?php endif; ?>
					</div>
				</form>	
			</div>
		</div>			
	</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
$(document).ready(function() {	

	// show payment method on select input
     var ajaxURL="<?php echo e(URL::route('pg_newconfig.index')); ?>";
     var token="<?php echo e(csrf_token()); ?>";
	 $.get(ajaxURL,
	 { 
		 _token:token
	 },function(data){
	 	$.each(data,function(index, el) {
	 		 $("#opt_wl").append('<option value="'+el.id+'">'+el.pg_name+'</option');
	 	});
		
	 });
   // End show payment method on select input

   // on change payment method call featch data
	 $("#opt_wl").change(function(event) {
	 	var pg_id=$(this).val();
        var show_path="<?php echo e(URL::route('pg_newconfig_fetch_dropdown_value.show')); ?>";
        var token="<?php echo e(csrf_token()); ?>";
	 	$.ajax({
             url:show_path,
             data:{'_token':token,'pg_id':pg_id},
             success:function(data){
               if(data.pg_status==1)
               {
               	 $("#opt_pg").prop('selectedIndex', 1);
               }
               else if(data.pg_status==0)
               {
               	 $("#opt_pg").prop('selectedIndex',2);
               }
               else
               {
               	 $("#opt_pg").prop('selectedIndex',0);
               }
               if(data.amount)
               {
                 $("#amt_charge").val(data.amount);
               }
               else
               {
               	 $("#amt_charge").val('');
               }

               if(data.crendential==0)
               {
                  $("#opt_crenden").prop('selectedIndex', 1); 
               }
               else if(data.crendential==1)
               {
               	  $("#opt_crenden").prop('selectedIndex', 2); 
               } 
               else
               {
               	  $("#opt_crenden").prop('selectedIndex', 0); 
               }   
              
             }
	 	});   
	 });

   $("#update_save").click(function(event) {
   	 event.preventDefault();
      var update_path="<?php echo e(URL::route('pg_newconfig.update')); ?>";
      var token="<?php echo e(csrf_token()); ?>";
      var method_type="post";
      $("#update_data").ajaxSubmit({
            url:update_path,
            data:{'_token':token},
            type:method_type,
            beforeSubmit:function(){
             $("#update_data").find('span').text('').end();
             $(".loadsave").addClass('fa fa-spinner fa-spin');
            },
            success:function(data){
             if(data.success==true)
             {
             	toastr.success('Updated successfully');
             	window.location.reload();
              $(".loadsave").removeClass('fa fa-spinner fa-spin');
             }
            },
            error:function(resobj){
            
              $.each(resobj.responseJSON.errors,function(k,v){
 
                $("#"+k+'_error').text(v); 
              });
              $(".loadsave").removeClass('fa fa-spinner fa-spin');
            }
           
      });

   });
 
    $('#amt_charge').on('input', function () {
        this.value = this.value.match(/^\d+\.?\d{0,2}/);
    });
});


</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/paymentGatewayNewConfig/index.blade.php ENDPATH**/ ?>