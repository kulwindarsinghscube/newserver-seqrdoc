		</div>	
		</div>
		</div>
		 <div class="loader">
			<div class="loader-content">
				<i class="fa fa-fw fa-5x fa-spinner fa-pulse fa-spin"></i>
			</div>
		</div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="<?php echo e(asset('backend/js/jquery.min.js')); ?>"></script>
	
	<!-- datepicker -->
	<script src="<?php echo e(asset('backend/js/moment.js')); ?>"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="<?php echo e(asset('backend/js/bootstrap.min.js')); ?>"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="<?php echo e(asset('backend/js/metisMenu.min.js')); ?>"></script>

	<!-- JQuery Validation -->
	<script src="<?php echo e(asset('backend/js/jquery.mockjax.js')); ?>"></script>
	<script src="<?php echo e(asset('backend/js/jquery.form.js')); ?>"></script>
	<script src="<?php echo e(asset('backend/js/jquery.validate.js')); ?>"></script>
	
	<!-- Data Tables -->
	<script src="<?php echo e(asset('backend/js/jquery.dataTables.min.js')); ?>"></script>
    <script src="<?php echo e(asset('backend/js/dataTables.bootstrap.min.js')); ?>"></script>
    <script src="<?php echo e(asset('backend/js/dataTables.responsive.js')); ?>"></script>
   
	<!-- bootbox -->
	<script src="<?php echo e(asset('backend/js/bootbox.min.js')); ?>"></script>
	
	<!-- datepicker -->
	<script src="<?php echo e(asset('backend/js/bootstrap-datetimepicker.min.js')); ?>"></script>
	
	<!-- animateNumber -->
	<script src="<?php echo e(asset('backend/js/jquery.animateNumber.js')); ?>"></script>
	
	<!-- selectpicker -->
	<script src="<?php echo e(asset('backend/js/bootstrap-select.js')); ?>"></script>
	
	
	<!-- ToastrJS -->
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js" type="text/javascript"></script> -->
	<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"  type="text/css"> -->
    <script src="<?php echo e(asset('backend/js/toastr.min.js')); ?>"></script>
    <!-- Custom Theme JavaScript -->
    <script src="<?php echo e(asset('backend/js/sb-admin-2.js')); ?>"></script>
	
	<!-- JS Tree -->
	<!-- <link rel="stylesheet" href="vendor/jstree/dist/themes/default/style.min.css" />
	
	<script src="vendor/jstree/dist/jstree.min.js"></script> -->
	
</body>

</html>

<?php echo $__env->yieldContent('script'); ?>
<script>
document.title = "<?php echo e(Session::get('site_name')); ?> SeQR";	
$(document).ready(function(){
	$('.loader').addClass('hidden');
	 $('[data-toggle="tooltip"]').tooltip(); 
	 
	 /*$("#logout").click(function(e){
		e.preventDefault();
		
		var ajaxURL = "functions/loginModel.php";
		
		$.post(	
			ajaxURL,{'type':"logout"},
			function(data){
				if(data.type == 'success'){
					window.location.replace('login.php?logout=true');
				}
			},'json'	
		);  
	});*/
	
	// function ajaxSession() {
	// 	$sesskey = '<?php //echo session_id();?>';
	// 	$user_id = '<?php //echo $_SESSION['UID'];?>';
		
	// 	$ajaxURL = '../services/check_session.php';
		
	// 	$.post($ajaxURL,{
	// 		'sesskey':$sesskey,
	// 		'user_id':$user_id
	// 	},function(data){
	// 		if(data.is_logged == '1'){
	// 			//console.log('is logged');
	// 		}else{
	// 			$("#logout").trigger('click');
	// 		}
	// 	},'JSON');
		
	// }

	// setInterval(ajaxSession, 20000);
	
	
});

function idleTimer() {
	    var t;
	    window.onload = resetTimer;
	    window.onmousemove = resetTimer; // catches mouse movements
	    window.onmousedown = resetTimer; // catches mouse movements
	    window.onclick = resetTimer;     // catches mouse clicks
	    window.onscroll = resetTimer;    // catches scrolling
	    window.onkeypress = resetTimer;  //catches keyboard actions

	    function logout() {
	    	// call ajax for logout
	        $.post("<?php echo e(route('webapp.autologout')); ?>",
		  	{
			    '_token': "<?php echo e(csrf_token()); ?>"
		  	},
			function(resp){
			   console.log(resp);
			});
	        window.location.href = "/";     //after automatically redirect to login page
	        
	    }

	   function resetTimer() {
	        clearTimeout(t);
	        t = setTimeout(logout, 4800000); // time is in milliseconds (1000 is 1 second)
	        // t = setTimeout(logout, 100000);  // time is in milliseconds (1000 is 1 second)
	    }
	}
idleTimer();

$(function () {
    /* START OF DEMO JS - NOT NEEDED */
    if (window.location == window.parent.location) {
        $('#fullscreen').html('<span class="glyphicon glyphicon-resize-small"></span>');
        $('#fullscreen').attr('href', 'http://bootsnipp.com/mouse0270/snippets/PbDb5');
        $('#fullscreen').attr('title', 'Back To Bootsnipp');
    }    
    $('#fullscreen').on('click', function(event) {
        event.preventDefault();
        window.parent.location =  $('#fullscreen').attr('href');
    });
    $('#fullscreen').tooltip();
    /* END DEMO OF JS */
    
    $('.navbar-toggler').on('click', function(event) {
		event.preventDefault();
		$(this).closest('.navbar-minimal').toggleClass('open');
	})
});
</script>
<style>
.help-inline{
	color:red;
	font-weight:normal;
}
.loader{
	display: table;
    background: rgba(0,0,0,0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    text-align: center;
    vertical-align: middle;
}

.loader-content{
	display:table-cell;
	vertical-align: middle;
	color:#fff;
}
</style><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/StudentWallet/layouts/footer.blade.php ENDPATH**/ ?>