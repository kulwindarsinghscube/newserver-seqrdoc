<!-- <?php 
session_set_cookie_params(0,'/WebApp/');
session_start();
// echo "<pre>";
// print_r($_SERVER);
// exit();
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


<?php

	$domain = \Request::getHost();
	$subdomain = explode('.', $domain);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo e($sitename); ?> SeQR</title>
	<link rel="icon" type="image/png" href="assets/images/fav.png">
    <!-- Bootstrap Core CSS -->
    <link href="<?php echo e(asset('/backend/css/bootstrap.min.css')); ?>" rel="stylesheet" type="text/css" />

    <!-- MetisMenu CSS -->
    <link href="<?php echo e(asset('/backend/css/metisMenu.min.css')); ?>" rel="stylesheet" type="text/css" />
   
   <!-- Animate CSS -->
    <link href="<?php echo e(asset('/backend/css/animate.css')); ?>" rel="stylesheet" type="text/css" />

    <!-- Custom CSS -->
    <link href="<?php echo e(asset('/backend/css/sb-admin-2.css')); ?>" rel="stylesheet" type="text/css" />

    <!-- Custom Fonts -->
    <link href="<?php echo e(asset('/backend/css/font-awesome.min.css')); ?>" rel="stylesheet" type="text/css" />
	
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<!-- <link href="https://fonts.googleapis.com/css?family=Abel" rel="stylesheet"> -->
    <link href="<?php echo e(asset('/backend/css/AbelRoboto.css')); ?>" rel="stylesheet" type="text/css" />
</head>

<body>
	<style>body { background-image: url('assets/media/auth/bg4.jpg'); } [data-bs-theme="dark"] body { background-image: url('assets/media/auth/bg4-dark.jpg'); }</style>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="pull-left" style="margin-top: 15%;margin-left: 10%;">
				<h2 style="font-family: 'Abel', sans-serif; color:#fff;">Welcome to <?php echo e($sitename); ?> SeQR Web App</h2>
				<h3 style="font-family: 'Abel', sans-serif; color:#fff;">Secure Documents Management System</h3>
			</div>
			<div class="pull-right">
			<h2>
				<?php
            	if(isset($site_data['apple_app_url'])){
            	?>
                <a href="<?php echo e($site_data['apple_app_url']); ?>" target="_blank"><img src="<?php echo e(asset('/webapp/images/store.png')); ?>" /></a>
            	<?php } ?>

            	<?php
            	if(isset($site_data['android_app_url'])){
            	?>
                <a href="<?php echo e($site_data['android_app_url']); ?>" target="_blank"><img src="<?php echo e(asset('/webapp/images/gplay.png')); ?>"/></a>
                <?php } ?>
			</h2>
			</div>
		</div>
	</div>
	<!-- <?php if($get_browser == 'Chrome') { ?>
	<div class="row" style="margin: 0px auto; width: 1200px;">
		<div class="col-xs-12">
			<div class='alert alert-danger alert-dismissable'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a><b> Scaning by web-cam will work only on Mozilla Firefox currently. So open
the website on Firefox browser. You can download it <a target="_blank" href="https://www.mozilla.org/en-US/firefox/new/">here</a> if you don't have the browser.</b></div>
		</div>
	</div>
	<?php } ?> -->
	<div class="row">
		<div class="login-bg clearfix">
			<div class="title-hdg">
				<div class="row ">
					<div class="col-xs-9">
						<h3 id="title" style="text-align: center;margin-left: 30%;">Sign In</h3>
					</div>
				</div>
			</div>
			<ul class="nav nav-pills hide">
			  	<li><a data-toggle="pill" href="#login-holder" id="login-pill">Login</a></li>
			  	<!-- <li><a id="signup_tab" data-toggle="pill" href="#signup-holder">Sign Up</a></li>
				<li><a id="forget_tab" data-toggle="pill" href="#forgot-password-holder">Forgot Password</a></li> -->
				
			</ul>
			<div class="tab-content">
			  <div id="login-holder" class="tab-pane fade in active">
				<p>
					<form method="POST" action="#" id="web_login">
                        <?php echo csrf_field(); ?>
						<fieldset>
							<div id="login_block">
							<div class="form-group">
								<label>Username</label>
								<input class="form-control input-lg" value="sewakdeshmukh19@gmail.com" id="username_l" placeholder="john" name="username" type="text" autofocus required>
								<span id="username_errors" class="help-inline text-danger"><?=$errors->first('username')?></span>
							</div>
							<div class="form-group">
								<label>Password</label>
								<input class="form-control input-lg" id="password_l" placeholder="**********96220" name="password" type="password" value="" required>
								<i class="fa fa-eye viewpass fa-lg" style="bottom:16px"></i>
								<span id="password_errors" class="help-inline text-danger"><?=$errors->first('password')?></span>
							</div>
							
							<span id="credential" class="text-danger"></span>
							
						  <button type="submit" id="login" class="btn btn-lg btn-primary btn-block"><i class="fa fa-unlock"></i> Login</button>
						  </div>
						  <div id="verify_block"  style="display: none;">
							
							<div class="form-group">
								<label>OTP</label>
								<input class="form-control input-lg" id="otp_l" placeholder="OTP" name="otp" type="text" autofocus>
								<span id="otp_errors" class="help-inline text-danger"><?=$errors->first('otp')?></span>
							</div>
							<span id="credential2" class="text-danger"></span>
							
						  <button type="submit" id="verify" class="btn btn-lg btn-primary btn-block"><i class="fa fa-unlock"></i> Verify</button>
						  </div>
						  <div id="setpassword_block"  style="display: none;">
							
							<div class="form-group">
								<label>Password</label>
								<input class="form-control input-lg" id="new_password_l" placeholder="Enter your password" name="new_password" type="password" autofocus>
								<span id="otp_errors" class="help-inline text-danger"><?=$errors->first('new_password')?></span>
							</div>
							
						  <button type="submit" id="set_password" class="btn btn-lg btn-primary btn-block"><i class="fa fa-unlock"></i> Set Password</button>
						  </div>
						</fieldset>
					</form>
				</p>
			  </div> 

			</div>
		</div>
	</div>
</div>
<!-- jQuery -->
<script src="<?php echo e(asset('/backend/js/jquery.min.js')); ?>"></script>

<!-- Bootstrap Core JavaScript -->
<script src="<?php echo e(asset('/backend/js/bootstrap.min.js')); ?>"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="<?php echo e(asset('/backend/js/metisMenu.min.js')); ?>"></script>

<!-- Custom Theme JavaScript -->
<script src="<?php echo e(asset('/backend/js/sb-admin2.js')); ?>"></script>
<!-- ToastrJS -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js" type="text/javascript"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"  type="text/css"> -->
<!-- JQuery Validation -->
<link rel="stylesheet" href="<?php echo e(asset('/backend/css/toastr.min.css')); ?>" rel="stylesheet" type="text/css" />
<script src="<?php echo e(asset('/backend/js/toastr.min.js')); ?>"></script> 
<script src="<?php echo e(asset('/backend/js/jquery.mockjax.js')); ?>"></script>
<script src="<?php echo e(asset('/backend/js/jquery.form.js')); ?>"></script>
<script src="<?php echo e(asset('/backend/js/jquery.validate.js')); ?>"></script>

<style>
    #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        background: rgba(0,0,0,0.75) url("/output.gif") no-repeat center center;
        z-index: 99999;
        background-repeat: no-repeat;
        background-size: 70px 70px;
        opacity: .8;
    }
</style>
<div id='loader'></div>

</body>

</html>
<script>
$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip(); 
	//login ajax
	
	$(".signin_clk").click(function(e){
		$("#login-pill").click();
		$("#title").text('Sign In');
	});
	
	$("#login,#verify,#set_password").click(function(e){

		e.preventDefault();		
		var login_url="<?php echo e(URL::route('gswebapp.login')); ?>";
		var method_type="post";
		var token="<?php echo e(csrf_token()); ?>";
		
		$("#web_login").ajaxSubmit({
              url:login_url,
              type:method_type,
              data:{'_token':token},

              success:function(data)
              {
                if(data.success==true)
                {
                	
                   toastr.success('Login Successfully'); 	
                   window.location.href="<?php echo e(URL::route('gsdocuments')); ?>";
                }else if(data.success==200)
                {
                  	toastr.success(data.msg); 	
                   window.location.href="<?php echo e(URL::route('gsdocuments')); ?>";
                } 
                else if(data.success==false)
                {
                   toastr.error(data.msg);  	 
                   $("#credential").text(data.msg); 
                }
                else if(data.success=='verify')
                {
                	$("#login_block").hide();
                	$("#verify_block").show();
                   toastr.error(data.msg);  	 
                   $("#credential2").text(data.msg); 
                }
                else if(data.success=='vfail')
                {
                   toastr.error(data.msg);  	 
                   $("#credential2").text(data.msg); 
                }
                else if(data.success=='verified')
                {
                   $("#login_block").hide();
                	$("#verify_block").hide();
                	$("#setpassword_block").show();
                   toastr.success(data.msg);  	 
                }
                else if(data.success==405)
                {
                   $("#login_block").hide();
                	$("#verify_block").hide();
                	$("#setpassword_block").show();
                   toastr.success(data.msg);  	 
                }
              },
              error:function(resobj)
              {

              	toastr.error('Something are wrong');
                 $.each(resobj.responseJSON.errors,function(k,v){
                   
                   $('#'+k+'_errors').text(v);
                 });
              }            
		});		
	});
	
	//signup ajax
	$("#signup").click(function(e){
		e.preventDefault();

		if (!$('.signupform').valid()) 
		{
            return false;
		} 
		else{	
		    
			var web_register="<?php echo e(URL::route('webapp.register')); ?>";
			var method_type="post";
			var token="<?php echo e(csrf_token()); ?>";
            
			$('#web_register').ajaxSubmit({
                  url:web_register,
                  type:method_type,
                  data:{'_token':token},
                  success:function(data){
                   if(data.status==true)
                   {
                    toastr.success('Registered Successfully'); 	
                    window.location.href=data.link; 
                   }   
                  },
                  error:function(resobj){
                   toastr.error('Something are wrong');
                    $.each(resobj.responseJSON.errors,function(k,v){
                   
                      $('#'+k+'_error').text(v);
                    });
                  }
			});	
		}
	});

	// view passowrd textbox and hide
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



<script>
	//forgot password ajax
	$("form#forgotpasswordform").submit(function(e){
		e.preventDefault();
		var token = "<?php echo e(csrf_token()); ?>";
		if (!$('.forgotpasswordform').valid())
		{
			return false;
		}else{
			var formData = new FormData($(this)[0]);
			$.ajax({
				url: "<?php echo e(route('authVerificationLink')); ?>",
				type: 'POST',
				data: formData,
				beforeSend:function() {
                    $('#loader').show();
				},	
				success: function (data) {
					if(data.status == true){
						toastr["success"](data.message);
						$('#forgotpasswordform')[0].reset();
						$('#loader').hide();
						//$('#login-holder-btn').click();
					}else{
						toastr["success"](data.message);
						$('#forgotpasswordform')[0].reset();
						$('#loader').hide();
					}
				},
				cache: false,
				contentType: false,
				processData: false,
				dataType:'JSON'
			});
		}
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
.semi-bold {
    font-weight: 600 !important;
}
body{
	/*background: #0052CC;
	color:#222;
	background-size:cover;
	background-attachment:fixed;*/

    background-color: var(--bs-app-blank-bg-color);
    background-attachment: fixed;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;

}

#login,#signup,#reset-pwd-btn{
	/*background:#0052CC !important;
	border:1px solid #0052CC;
	border-radius:4px;
	margin-top:30px !important;
	color:#fff;
	text-transform:uppercase;
	font-size:14px;
	font-weight:700;
	padding:15px 0;*/
}

#login:hover, #signup, #reset-pwd-btn:hover{
	/*border-color:#0747A6;
	background:#0747A6 !important;*/
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
	border-radius:30px;
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
        'left' : '65%',
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
</script><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/auth/studentlogin.blade.php ENDPATH**/ ?>