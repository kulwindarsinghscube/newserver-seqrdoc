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
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="pull-left">
				<h2 style="font-family: 'Abel', sans-serif; color:#fff;"><?php echo e($sitename); ?> SeQR Web App</h2>
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
				<li><a data-toggle="pill" href="#forgot-password-holder">Forgot Password</a></li>
				
			</ul>
			<div class="tab-content">
			  <div id="login-holder" class="tab-pane fade in active">
				<p>
					<form method="POST" action="#" id="web_login">
                        <?php echo csrf_field(); ?>
						<fieldset>
							<div class="form-group">
								<label>Username</label>
								<input class="form-control input-lg" id="username_l" placeholder="john" name="username" type="text" autofocus required>
								<span id="username_errors" class="help-inline text-danger"><?=$errors->first('username')?></span>
							</div>
							<div class="form-group">
								<label>Password</label>
								<input class="form-control input-lg" id="password_l" placeholder="**********" name="password" type="password" value="" required>
								<i class="fa fa-eye viewpass fa-lg" style="bottom:16px"></i>
								<span id="password_errors" class="help-inline text-danger"><?=$errors->first('password')?></span>
							</div>
							<span id="credential" class="text-danger"></span>
							<!-- Change this to a button or input when using this as a form -->
						  <button type="submit" id="login" class="btn btn-sm btn-block"><i class="fa fa-unlock"></i> Login</button>
						</fieldset>
					</form>
				</p>
			  </div>
		
			  <div id="signup-holder" class="tab-pane fade">
					<p>
						<form method="post" class="signupform" id="web_register">
						<fieldset>
							<input type="hidden" name="device_type" value="WebApp">
							<div class="form-group">
								<label>Fullname<sup>*</sup></label>
								<input class="form-control" id="name" placeholder="John Doe" name="fullname" type="text" data-rule-required="true">	
								<span id="fullname_error" class="help-inline text-danger"><?=$errors->first('fullname')?></span>		
							</div>
							<div class="form-group">
								<label>Email<sup>*</sup></label>
								<input class="form-control" id="email_id" placeholder="john@example.com" name="email_id" type="email" data-rule-required="true">
								<span id="email_id_error" class="help-inline text-danger"><?=$errors->first('email_id')?></span>
							</div>
							<div class="form-group">
								<label>Mobile<sup>*</sup></label>
								<input class="form-control" id="mobile_no" placeholder="9001008001" name="mobile_no" type="text" data-rule-required="true">
								<span id="mobile_no_error" class="help-inline text-danger"><?=$errors->first('mobile_no')?></span>
							</div>
							<div class="form-group">
								<label>Username<sup>*</sup></label>
								<input class="form-control" id="username" placeholder="john" name="username" type="text" data-rule-required="true">
								<span id="username_error" class="help-inline text-danger"><?=$errors->first('username')?></span>
							</div>
									
				<?php if(isset($subdomain[0]) && strtolower($subdomain[0]) == 'mallareddyuniversity'): ?>
    <div class="form-group">
        <label>Organization<sup>*</sup></label>
        <input class="form-control" id="organization_name" placeholder="scube" name="organization_name" type="text" data-rule-required="true">
    </div>
<?php endif; ?>

							<div class="form-group">
								<label>Password<sup>*</sup></label>
								<input class="form-control" id="password" placeholder="********" name="password" type="password" data-rule-required="true">
								<i class="fa fa-eye viewpass"></i>
								<span id="password_error" class="help-inline text-danger"><?=$errors->first('password')?></span>
							</div>
							<!-- <div class="form-group" style="visibility: hidden;">
								<label>Account Verification<sup>*</sup> <i class="fa fa-info-circle theme" data-placement="right" data-toggle="tooltip" title="An email or OTP will be sent to you based on the method you select for verification."></i></label>
								<select class="form-control" name="verify_by" id="verify_by">
									<option value="1">Verify By SMS</option>
									<option value="2" selected>Verify By Email</option>
								</select>
							</div> -->
							<!-- Change this to a button or input when using this as a form -->
							<button type="submit" id="signup" class="btn btn-sm btn-block"><i class="fa fa-check-circle"></i> SignUp</button>
							
						</fieldset>
					</form>
					</p>
			  </div>

			  
			  <div id="forgot-password-holder" class="tab-pane fade">
			  	
				<p>
					<form method="post" class="forgotpasswordform" id="forgotpasswordform">
						<fieldset>
							<!-- <div class="form-group">
								<div class="row">
									<div class="col-xs-12 col-md-12 col-lg-12"  >
										<h4>Please enter your registered email id :</h4>
									</div>
								</div>
							</div> -->
							<div class="form-group">
								<div class="row">
									<div class="col-xs-12 col-md-12 col-lg-12">
									<label>Please enter your registered email id :</label>
									<input type="hidden" id="_token" name="_token" value="<?php echo e(csrf_token()); ?>">
									<input class="form-control input-lg" id="forgot_pwd_email" placeholder="abc@example.com" name="email_id" type="email" value="" data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="row">
		      					<div class="col-xs-12 col-md-12 col-lg-12">
		      						<button type="submit" id="reset-pwd-btn" class="btn btn-sm btn-block" > <i class="fa fa-key"></i>  Reset Password</button>
		      					</div>
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
	$("#login").click(function(e){

		e.preventDefault();		
		var login_url="<?php echo e(URL::route('webapp.login')); ?>";
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
                   window.location.href="<?php echo e(URL::route('webapp.dashboard')); ?>";
                }else if(data.success==200)
                {
                  	toastr.success(data.msg); 	
                   window.location.href=data.link;
                } 
                else if(data.success==false)
                {
                   toastr.error(data.msg);  	 
                   $("#credential").text(data.msg); 
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

body{
	background: #0052CC;
	color:#222;
	background-size:cover;
	background-attachment:fixed;
}

#login,#signup,#reset-pwd-btn{
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

#login:hover, #signup, #reset-pwd-btn:hover{
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
</script><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/auth/userlogin.blade.php ENDPATH**/ ?>