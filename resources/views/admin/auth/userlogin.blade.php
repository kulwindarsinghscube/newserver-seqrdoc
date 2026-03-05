<!-- <?php 
session_set_cookie_params(0,'/WebApp/');
session_start();

if(isset($_GET['logout'])){
	if($_GET['logout'] == 'true'){
		session_destroy();
		session_set_cookie_params(0,'/WebApp/');
	}
}
if(isset($_SESSION['is_logged'])){
	if($_SESSION['is_logged'] === 1){
		header('Location: index.php');
		exit();
	}
}

function get_browser_name($user_agent)
{
    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
    elseif (strpos($user_agent, 'Edge')) return 'Edge';
    elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
    elseif (strpos($user_agent, 'Safari')) return 'Safari';
    elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
    elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
    
    return 'Other';
}

$get_browser = get_browser_name($_SERVER['HTTP_USER_AGENT']);
?> -->
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SeQR WebApp</title>
	<link rel="icon" type="image/png" href="assets/images/fav.png">
    <!-- Bootstrap Core CSS -->
    <link href="{{asset('/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />

    <!-- MetisMenu CSS -->
    <link href="{{asset('/backend/css/metisMenu.min.css')}}" rel="stylesheet" type="text/css" />
   
   <!-- Animate CSS -->
    <link href="{{asset('/backend/css/animate.css')}}" rel="stylesheet" type="text/css" />

    <!-- Custom CSS -->
    <link href="{{asset('/backend/css/sb-admin-2.css')}}" rel="stylesheet" type="text/css" />

    <!-- Custom Fonts -->
    <link href="{{asset('/backend/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css" />
	
 
    <link href="{{asset('/backend/css/AbelRoboto.css')}}" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="pull-left">
				<h2 style="font-family: 'Abel', sans-serif; color:#fff;">SeQR Web App</h2>
			</div>
			<div class="pull-right">
			<h2>
				<a href="https://play.google.com/store/apps/details?id=seqrprintscan.scube.com.seqrprintscan&hl=en" target="_blank"><img src="assets/images/gplay.png"/></a>
				<a href="https://appsto.re/in/PNGplb.i" target="_blank"><img src="assets/images/store.png" /></a>
			</h2>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="login-bg clearfix">
			<div class="title-hdg">
				<div class="row">
					<div class="col-xs-9">
						<h3>Login / SignUp</h3>
						<h6>Dont have an account? Sign up for free!</h6>
					</div>
					<div class="col-xs-3">
						<h3><i class="fa fa-fw fa-pencil fa-2x"></i><h3>
					</div>
				</div>
			</div>
			<ul class="nav nav-pills">
			  <li class="active"><a data-toggle="pill" href="#login-holder" id="login-pill">Login</a></li>
			  <li><a data-toggle="pill" href="#signup-holder">Sign Up</a></li>
			</ul>
			<div class="tab-content">
			  <div id="login-holder" class="tab-pane fade in active">
				<p>
					<form method="POST" action="{{ route('webapp.login') }}">
                        @csrf
						<fieldset>
							<div class="form-group">
								<label>Username</label>
								<input class="form-control input-lg" id="username" placeholder="john" name="username" type="text" autofocus required>
							</div>
							<div class="form-group">
								<label>Password</label>
								<input class="form-control input-lg" id="password" placeholder="**********" name="password" type="password" value="" required>
								<i class="fa fa-eye viewpass fa-lg" style="bottom:16px"></i>
							</div>
							<!-- Change this to a button or input when using this as a form -->
						  <button type="submit" id="login" class="btn btn-sm btn-block"><i class="fa fa-unlock"></i> Login</button>
							
						</fieldset>
					</form>
				</p>
			  </div>
			  <div id="signup-holder" class="tab-pane fade">
					<p>
						<form method="post" class="signupform">
						<fieldset>
							<div class="form-group">
								<label>Fullname<sup>*</sup></label>
								<input class="form-control" id="name" placeholder="John Doe" name="name" type="text" data-rule-required="true">			
							</div>
							<div class="form-group">
								<label>Email<sup>*</sup></label>
								<input class="form-control" id="email_id" placeholder="john@example.com" name="email_id" type="email" data-rule-required="true">
							</div>
							<div class="form-group">
								<label>Mobile<sup>*</sup></label>
								<input class="form-control" id="mobile_no" placeholder="9001008001" name="mobile_no" type="text" data-rule-required="true">
							</div>
							<div class="form-group">
								<label>Username<sup>*</sup></label>
								<input class="form-control" id="username" placeholder="john" name="username" type="text" data-rule-required="true">
							</div>
							<div class="form-group">
								<label>Password<sup>*</sup></label>
								<input class="form-control" id="password" placeholder="********" name="password" type="password" data-rule-required="true">
								<i class="fa fa-eye viewpass"></i>
							</div>
							<div class="form-group">
								<label>Account Verification<sup>*</sup> <i class="fa fa-info-circle theme" data-placement="right" data-toggle="tooltip" title="An email or OTP will be sent to you based on the method you select for verification."></i></label>
								<select class="form-control" name="verify_by" id="verify_by">
									<option value="1">Verify By SMS</option>
									<option value="2">Verify By Email</option>
								</select>
							</div>
							<!-- Change this to a button or input when using this as a form -->
							<button type="submit" id="signup" class="btn btn-sm btn-block"><i class="fa fa-check-circle"></i> SignUp</button>
							
						</fieldset>
					</form>
					</p>
			  </div>
			</div>
		</div>
	</div>
</div>
<!-- jQuery -->
<script src="{{asset('/backend/js/jquery.min.js')}}"></script>

<!-- Bootstrap Core JavaScript -->
<script src="{{asset('/backend/js/bootstrap.min.js')}}"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="{{asset('/backend/js/metisMenu.min.js')}}"></script>

<!-- Custom Theme JavaScript -->
<script src="{{asset('/backend/js/sb-admin2.js')}}"></script>
<!-- ToastrJS -->

<link rel="stylesheet" href="{{asset('/backend/css/toastr.min.css')}}" rel="stylesheet" type="text/css" />
<script src="{{asset('/backend/js/toastr.min.js')}}"></script> 
<script src="{{asset('/backend/js/jquery.mockjax.js')}}"></script>
<script src="{{asset('/backend/js/jquery.form.js')}}"></script>
<script src="{{asset('/backend/js/jquery.validate.js')}}"></script>
</body>

</html>
<script>
$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip(); 
	//login ajax
	$("#login").click(function(e){
		e.preventDefault();
		var password = $("#password_l").val();
		var username = $("#username_l").val();
		var ajaxURL = "../services/login_webApp.php";
		$.post(	
			ajaxURL,
			{	
				'username' : username,
				'password' : password
			},
			function(data){
				
				 if(data.status == true){
					toastr["success"](data.message);
					setTimeout(function(){window.location.replace('index.php')},400);
				}
				else{
					toastr["error"](data.message);
					if(data.code == '300'){
						setTimeout(function(){window.location.replace('verification.php?uid='+data.UID)},400);
					}else{
						return false;
					}
				} 
			},'json'
			
		);
		
	});
	
	//signup ajax
	$("#signup").click(function(e){
		e.preventDefault();
		if (!$('.signupform').valid()) 
		{
            return false;
		} 
		else{
			var password = $("#password").val();
			var username = $("#username").val();
			var fullname = $("#name").val();
			var mobile = $("#mobile_no").val();
			var email = $("#email_id").val();
			var verify_by = $("#verify_by").val();
			var ajaxURL = "../services/registration.php";
			$.post(	
				ajaxURL,
				{		
					'action':'register',
					'username' : username,
					'password' : password,
					'name':fullname,
					'mobile_no':mobile,
					'email_id':email,
					'verify_by':verify_by,
					'device_type':'web',
				},
				function(data){
					 if(data.status == true){
						toastr["success"](data.message);
						$('#login-pill').trigger('click');
						$('.signupform').reset();
					}
					else{
						toastr["error"](data.message);
					} 
				},'json'
				
			);
		}
	});
	
	$('.viewpass').click(function(){
		$type = $(this).prev('input').attr('type');
		if($type=="password"){
			$(this).prev('input').attr('type','text');
			$(this).addClass('fa-eye-slash red');
			$(this).removeClass('fa-eye');
		}else{
			$(this).prev('input').attr('type','password');
			$(this).addClass('fa-eye');
			$(this).removeClass('fa-eye-slash red');
		}
	});
	
});
</script>
<?php if(isset($_GET['logout'])):?>
		<script>
		toastr["success"]("Logged Out Successfully");
		</script>
<?php endif;?>
<style>
html,body{
	height:100%;
	width:100%;
}

body{
	background: #0052CC;
	color:#222;
	background-size:cover;
	background-attachment:fixed;
}

#login,#signup{
	background:#0052CC !important;
	border:1px solid #0052CC;
	border-radius:4px;
	margin-top:30px !important;
	color:#fff;
	text-transform:uppercase;
	font-size:14px;
	font-weight:700;
	padding:15px 0;
}

#login:hover, #signup:hover{
	border-color:#0747A6;
	background:#0747A6 !important;
}

*{
	transition:all ease 0.5s;
}

.footer-links{
	color:#fff;
	position:fixed;
	bottom:5px;
	left:0;
	right:0;
	text-align:center;
	font-size:11px;
}

.login-bg{
	background:#fff;
	padding:0 25px 25px 25px;
	border-radius:4px;
	width:390px;
}

.title-hdg{
	margin-top:10px;
	margin-bottom:10px;
	border-bottom:1px solid #ececec;
	padding:0px 5px ;
}

.login-bg .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover, .nav>li>a
.login-bg .nav-pills>li>a, .nav-pills>li>a:focus, .nav-pills>li>a:hover, .nav>li>a
{
	background:#fff;
	border:0px solid #fff;
	color:#0052CC;
	border-bottom:2px solid #0052CC;
	border-radius:0;
	padding:5px 15px;
}

.nav>li>a{
	border:0px solid #fff;
	border-bottom:2px solid #fff;	
}

.signupform label,
.loginform label{
	color:#5c5c5c;
}

.help-inline{
	color: #F44336;
    display: inline-block;
    font-size: 12px;
    position: absolute;
    top: 4px;
    right: 0;
    border-radius: 4px
}

.form-group{
	position:relative;
}

.signupform sup{
	color:red;
}

.viewpass{
	position:absolute;
	bottom:10px;
	right:10px;
	cursor:pointer;
}
</style>
<script>
$(document).ready(function(){
	alignMiddle();

	$('.nav-pills li a').click(function(){
		setTimeout(function(){alignMiddle();},500);
	});

});

function alignMiddle(){
	$('.login-bg').css({
        'position' : 'absolute',
        'left' : '50%',
        'top' : '50%',
        'margin-left' : -$('.login-bg').outerWidth()/2,
        'margin-top' : -$('.login-bg').outerHeight()/2
    });
}

$('.signupform').validate({
	errorElement: 'div',
	errorClass: 'help-inline',
	focusInvalid: false,
	rules: {
	mobile_no: {
		minlength: 10,
		maxlength: 10,
		number:true
	},
	password: {
		minlength: 8
	},
},
	messages: {
		name: "Fullname is required",
		email_id: "Valid Email is required",
		mobile_no: "10 Digit Mobile number is required",
		username: "Username is required",
		password: "Min. 8 character Password is required"
	},
	invalidHandler: function (event, validator) {},
	highlight: function (e) {},
	success: function (e) {},
	errorPlacement: function (error, element) {
		error.insertBefore(element);
	},
	submitHandler: function (form) {},
	invalidHandler: function (form) {}
});
</script>