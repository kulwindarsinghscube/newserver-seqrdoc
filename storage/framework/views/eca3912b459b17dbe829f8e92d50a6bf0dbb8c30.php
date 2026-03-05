<script src="<?php echo e(asset('backend/js/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/jquery.validate.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/additional-methods.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/jquery.timepicker.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/readmore.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/modernizr-custom.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/moment.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/metisMenu.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/jquery.mockjax.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/jquery.form.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/jquery.dataTables.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/dataTables.bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/dataTables.responsive.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/bootbox.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/bootstrap-datetimepicker.min.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/jquery.animateNumber.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/bootstrap-select.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/sb-admin-2.js')); ?>"></script>
<script src="<?php echo e(asset('backend/js/toastr.min.js')); ?>"></script>
 
 <!--  // acl permission js -->
  <script src="<?php echo e(asset('backend/js/jquery.form.min.js')); ?>"></script>
  <script src="<?php echo e(asset('backend/js/select2.full.min.js')); ?>"></script>
  <script src="<?php echo e(asset('backend/js/select2.min.js')); ?>"></script>
  <script src="<?php echo e(asset('backend/js/select2-data.js')); ?>"></script>
  <script src="<?php echo e(asset('backend/js/jquery.multi-select.js')); ?>"></script>
  <script src="<?php echo e(asset('backend/jstree/dist/jstree.min.js')); ?>"></script>
<script>
$(document).ready(function(){
	$('.loader').addClass('hidden');
	 $('[data-toggle="tooltip"]').tooltip(); 

	 $("#logout").click(function(e){
		e.preventDefault();
		
		var ajaxURL = "../functions/loginModel.php";
		
		$.post(	
			ajaxURL,{'type':"logout"},
			function(data){
				if(data.type == 'success'){
					window.location.replace('../login.php?logout=true');
				}
			},'json'	
		);  
	});
	
	$('#newpass').click(function(){
		$(".alert-danger").hide();
		$(".alert-success").hide();
		$('#psswrddata')[0].reset();
		
	});
	
	$('#changePass').click(function(){
		var ajaxURL = "../functions/UserManagementModel.php";
		
		if (!$('#psswrddata').valid()) 
		{
            return false;
		} 
		var $data = $('#psswrddata').serialize();
		$.post(ajaxURL,
					{'type':'changepass','data':$data},
					function(data){
						if(data.type == 'success'){
							$(".alert-success .message").html(data.message);
							$(".alert-success").fadeIn();
							$('#psswrddata')[0].reset();
						}
						else{
							$(".alert-danger .message").html(data.message);
							$(".alert-danger").fadeIn();
						}
					},'json');
	});
	
	$('#psswrddata').validate({
		errorElement: 'div',
		errorClass: 'help-inline',
		focusInvalid: false,
		rules: {
		},
		messages: {
			//field_region_name: "Subject is required"
		},
		invalidHandler: function (event, validator) { //display error alert on form submit
			$('.alert-error', $('#psswrddata')).show();
		},
		highlight: function (e) {
			$(e).closest('.control-group').removeClass('info').addClass('error');
		},
		success: function (e) {
			$(e).closest('.control-group').removeClass('error').addClass('info');
			$(e).remove();
		},
		errorPlacement: function (error, element) {
			if (element.is(':checkbox') || element.is(':radio')) {
				var controls = element.closest('.controls');
				if (controls.find(':checkbox,:radio').length > 1)
					controls.append(error);
				else
					error.insertAfter(element.nextAll('.lbl:eq(0)').eq(0));
			}
			else if (element.is('.select2')) {
				error.insertAfter(element.siblings('[class*="select2-container"]:eq(0)'));
			}
			else if (element.is('.chzn-select')) {
				error.insertAfter(element.siblings('[class*="chzn-container"]:eq(0)'));
			}
			else
				error.insertAfter('#showError');
		},
		submitHandler: function (form) {
		},
		invalidHandler: function (form) {
		}
	});
	
	//breadcrumbs
	/*generateBreadCrumb();*/
	
	function generateBreadCrumb(){
		var url = location.pathname.substring(location.pathname.lastIndexOf("/") + 1);
		if(url == 'add_template.php'){
			url = 'template_master.php';  
		}else if(url == 'add_background_template.php'){
			url = 'background_template_master.php';
		}
		var currentItem = $(".nav").find("[href='" + url + "']");
		var path = '<li><a href="<?php echo e(URL::route('admin.dashboard')); ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>';
		$(currentItem.parents("li").get().reverse()).each(function () {
			var text = $(this).children("a").html();
			var href = $(this).children("a").attr('href');
			var link = '';
			if(href == '#')
				link = '<li style="color:#666">'+text+'</li>';
			else
				link = '<li><a href="'+href+'">'+text+'</a></li>';
			path += link; 
		});
		$('.breadcrumb').append(path);
		$('.breadcrumb .caret').css('display','none');
	}
	$('#page-wrapper').css('margin','0 0 0 0px');
	//hide sidebar
	$('#toggle-sidebar').click(function(e){
		e.preventDefault();
		$('.sidebar').toggleClass('active');
		
		if($('.sidebar').hasClass('active')){
			$('#page-wrapper').css('margin','0 0 0 0');
			$('.sidebar').addClass('animated slideOutLeft');
			$("#toggle-sidebar .glyphicon").removeClass('glyphicon-menu-hamburger');
			$("#toggle-sidebar .glyphicon").addClass('glyphicon-align-justify');
		}
		else {
			$('#page-wrapper').css('margin','0 0 0 250px');
			$('.sidebar').removeClass('slideOutLeft');
			$('.sidebar').addClass('slideInLeft');
			$("#toggle-sidebar .glyphicon").addClass('glyphicon-menu-hamburger');
			$("#toggle-sidebar .glyphicon").removeClass('glyphicon-align-justify');
		}
		
	});
	
});
</script><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/layout/script.blade.php ENDPATH**/ ?>