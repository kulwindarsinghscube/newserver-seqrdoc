
		<div class="loader">
			<div class="loader-content">
				<i class="fa fa-fw fa-5x fa-spinner fa-pulse fa-spin"></i>
			</div>
		</div>
	</body>
</html>
<script type="text/javascript">
	document.title = "<?php echo e(Session::get('site_name')); ?> SeQR";
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
	        $.post("<?php echo e(route('admin.autologout')); ?>",
		  	{
			    '_token': "<?php echo e(csrf_token()); ?>"
		  	},
			function(resp){
			   console.log(resp);
			});
	        window.location.href = "/admin/login";     //after automatically redirect to login page
	        
	    }

	   function resetTimer() {
	        clearTimeout(t);
	        t = setTimeout(logout, 43200000);
	        //t = setTimeout(logout, 4800000);
	        // t = setTimeout(logout, 100000);  // time is in milliseconds (1000 is 1 second)
	    }
	}
	idleTimer();
</script>
<?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/layout/footer.blade.php ENDPATH**/ ?>