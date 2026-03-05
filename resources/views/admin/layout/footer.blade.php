
		<div class="loader">
			<div class="loader-content">
				<i class="fa fa-fw fa-5x fa-spinner fa-pulse fa-spin"></i>
			</div>
		</div>
	</body>
</html>
<script type="text/javascript">
	document.title = "{{ Session::get('site_name') }} SeQR";
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
	        t = setTimeout(logout, 43200000);
	        //t = setTimeout(logout, 4800000);
	        // t = setTimeout(logout, 100000);  // time is in milliseconds (1000 is 1 second)
	    }
	}
	idleTimer();
</script>
<?php
$domain = \Request::getHost();
$subdomain = explode('.', $domain);
if ($subdomain[0]=="ksg"){
?>
<style>
.navbar {
    background: #7f632c !important;
    border: none !important;
    border-radius: 0px;
    box-shadow: 0px 1px 3px #555 !important;
}
.dashboard-card {
    background: #fff;
    box-shadow: 0px 3px 6px #ccc;
    padding: 20px 25px 0 25px;
    border: 1px solid #ccc;
    border-bottom: 2px solid #CBD300;
    margin: 15px 0;
}
.link-url a {
    color: #CBD300;
}
.btn-primary {
    color: #fff;
    background-color: #CBD300 !important;
    border-color: #7f632c !important;
}
.navbar-default .navbar-nav>li>a:focus{
	color:#fff;
	background:#CBD300 !important;
	padding:7px;
	margin:10px;
	border-radius:4px;
}
.navbar-default .navbar-nav>li>a:hover{
	color:#fff;
	background:#CBD300 !important;
	border-radius:4px;
}

.dropdown-menu>.active>a, .dropdown-menu>.active>a:focus{
	color:#fff;
	background:#CBD300 !important;
	padding:10px;
}

.dropdown-menu>.active>a:hover{
	color:#fff;
	background:#CBD300 !important;
}

.navbar-default .navbar-nav>.open>a, .navbar-default .navbar-nav>.open>a:focus{
	color:#fff;
	background:#CBD300 !important;
	padding:7px;
	margin:10px;
	border-radius:4px;
}

.navbar-default .navbar-nav>.open>a:hover{
	color:#fff;
	background:#CBD300 !important;
	border-radius:4px;
}


</style>
<?php
}
?>