
		<div class="loader">
			<div class="loader-content">
				<i class="fa fa-fw fa-5x fa-spinner fa-pulse fa-spin"></i>
			</div>
		</div>
	</body>
</html>
<script type="text/javascript">
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
	        $.post("{{route('admin.autologout')}}",
		  	{
			    '_token': "{{csrf_token()}}"
		  	},
			function(resp){
			   console.log(resp);
			});
	        window.location.href = "/admin/login";     //after automatically redirect to login page
	        
	    }

	   function resetTimer() {
	        clearTimeout(t);
	        t = setTimeout(logout, 4800000);
	        // t = setTimeout(logout, 100000);  // time is in milliseconds (1000 is 1 second)
	    }
	}
	idleTimer();
</script>
