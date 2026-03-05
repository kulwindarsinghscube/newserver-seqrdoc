<?php

$domain = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $domain);

function get_browser_name($user_agent)
{
    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) {
        return 'Opera';
    } elseif (strpos($user_agent, 'Edge')) {
        return 'Edge';
    } elseif (strpos($user_agent, 'Chrome')) {
        return 'Chrome';
    } elseif (strpos($user_agent, 'Safari')) {
        return 'Safari';
    } elseif (strpos($user_agent, 'Firefox')) {
        return 'Firefox';
    } elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) {
        return 'Internet Explorer';
    }

    return 'Other';
}

$get_browser = get_browser_name($_SERVER['HTTP_USER_AGENT']);
?>
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
    <link rel="stylesheet" href="{{asset('backend/css/bootstrap.min.css')}}">

    <!-- MetisMenu CSS -->
    <link rel="stylesheet" href="{{asset('backend/css/metisMenu.min.css')}}">

   <!-- Animate CSS -->
    <link rel="stylesheet" href="{{asset('backend/css/animate.css')}}">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{asset('backend/css/sb-admin-2.css')}}">

    <!-- Custom Fonts -->
    <link rel="stylesheet" href="{{asset('backend/css/font-awesome.min.css')}}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<!-- <link href="https://fonts.googleapis.com/css?family=Abel" rel="stylesheet"> -->
	<link href="vendor/font/AbelRoboto.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="pull-left">
				<h2 style="font-family: 'Abel', sans-serif; color:#fff;">
				<?php if ($subdomain[0] == "monad") {
    echo "Welcome to " . ucwords($subdomain[0]) . " University";
} else {
    echo "SeQR Verification App";
}
?>


				</h2>
			</div>
			<div class="pull-right">
			<h2>

				<?php if ($subdomain[0] == "monad") {?>
				<a href="https://play.google.com/store/apps/details?id=com.monad_seqr" target="_blank"><img src="{{asset('backend/gplay.png')}}"/></a>
				<a href="https://apps.apple.com/in/app/monad-seqr-scan/id1597741035" target="_blank"><img src="{{asset('backend/store.png')}}" /></a>
			<?php } else {?>
				<a href="https://play.google.com/store/apps/details?id=com.raisoni" target="_blank"><img src="{{asset('backend/gplay.png')}}"/></a>
				<a href="https://apps.apple.com/us/app/raisoni-group/id1468248125?ls=1" target="_blank"><img src="{{asset('backend/store.png')}}" /></a>
			<?php }?>
			</h2>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="login-bg clearfix">
			<ul class="nav nav-pills">
			<div class="tab-content">
			  <div id="login-holder" class="tab-pane fade in active" style="min-height: 425px;">
			  	<div class="title-hdg">
					<div class="row">
						<div class="col-xs-9">
							<h3>Login</h3>
						</div>
					</div>
				</div>
				<p>
					<form method="post" class="loginform">
						<fieldset>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" style="    text-align: center;">
										<label>Username</label>
									</div>
									<div class="col-xs-9 col-md-5 col-lg-5">

										<input class="form-control input-lg" id="username_l" placeholder="john" name="username_l" type="email" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
								<div class="col-xs-3 col-md-3 col-lg-4"  style="    text-align: center;">
								<label>Password</label>
								</div>
								<div class="col-xs-9 col-md-5 col-lg-5">
								<input class="form-control input-lg" id="password_l" placeholder="**********" name="password_l" type="password" value="" data-rule-required="true">
								<i class="fa fa-eye viewpass fa-lg" style="bottom:10px"></i>
								</div>
							</div>
							</div>

							<div class="row">
								<div class="col-xs-3 col-md-3 col-lg-4"></div>
								<div class="col-xs-4 col-md-4 col-lg-4">
										<!-- Change this to a button or input when using this as a form -->
									  <button type="submit" id="login" class="btn btn-sm btn-block form-btn"><i class="fa fa-unlock"></i> Login</button>
									</div>
								</div>

							<div class="row" style="    margin-top: 30px;">
								<div class="col-xs-6 col-md-4 col-lg-4">
									<button type="button" data-toggle="pill" href="#signup-holder" class="btn btn-sm btn-block form-btn" style="border:1px solid #05a705; background-color: #54b954 !important;"  id="signup-holder-btn"> New Registration</button>
								</div>
								<div class="col-xs-6 col-md-4 col-lg-4">
									  <button type="button" data-toggle="pill" href="#forgot-password-holder" class="btn btn-sm btn-block form-btn" style="border:1px solid #e62121;background-color: #f75b5b !important; " id="forgot-password-holder-btn"> Forgot Password</button>
									</div>
								</div>
						</fieldset>
					</form>
				</p>
			  </div>
			  <div id="signup-holder" class="tab-pane fade">
			  	<div class="title-hdg">
					<div class="row">
						<div class="col-xs-9">
							<h3>New Registration</h3>
						</div>
					</div>
				</div>
					<p>
						<form method="post" class="signupform" id="signupform">
						<fieldset>
							<input type="hidden" id="_token" name="_token" value="{{csrf_token()}}">
							<div class="form-group">
								<div class="row">
									<div class="col-xs-4 col-md-4 col-lg-4" style="    text-align: center;">
										<input  id="employer_type"  name="registration_type" type="radio" value="1" checked  data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Employer</label>
									</div>
									<div class="col-xs-4 col-md-4 col-lg-4" style="    text-align: center;">
										<input  id="agency_type"  name="registration_type" type="radio"  value="2" data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Third Party Agency</label>
									</div>
									<div class="col-xs-4 col-md-4 col-lg-4" style="    text-align: center;">
										<input  id="student_type"  name="registration_type" type="radio"  value="0" data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Student</label>
									</div>
								</div>
							</div>
							<!--Employer Section-->
							<div id="employerSection">
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Employer Name <sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">

										<input class="form-control input-lg" id="employer_name"  name="employer_name" type="text" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-6 col-md-6 col-lg-5" >
										<label>Employer CIIN / Registration Number <sup>*</sup></label>
									</div>
									<div class="col-xs-6 col-md-6 col-lg-7">

										<input class="form-control input-lg" id="employer_reg_no" name="employer_reg_no" type="text" onkeypress="return isAlphaNumeric(event)" minlength="1" maxlength="25" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
								<label>Working Sector <sup>*</sup> </label>
								</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
								<select class="form-control" name="employer_working_sector" id="employer_working_sector" data-rule-required="true">
									<option value="" disabled selected>Select Working Sector</option>
									<option value="Public sector">Public sector</option>
									<option value="Private sector">Private sector</option>
									<option value="Government Body">Government Body</option>
									<option value="Public Sector Unit">Public Sector Unit</option>
								</select>
								</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Address
											<?php if ($subdomain[0] != "monad") {?>
											<sup>*</sup>
										<?php }?>
										</label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
										<?php if ($subdomain[0] == "monad") {?>
										<textarea class="form-control input-lg" id="employer_address" placeholder="Mumbai" name="employer_address" type="text" autofocus></textarea>
										<?php } else {?>
										<textarea class="form-control input-lg" id="employer_address" placeholder="Mumbai" name="employer_address" type="text" autofocus data-rule-required="true"></textarea>
										<?php }?>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Mobile No <sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">

										<input class="form-control input-lg" id="employer_mob_no" placeholder="9xxxxxxxxx" name="employer_mob_no" type="text" onkeypress="return isNumberKey(event)" minlength="10" maxlength="10" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Email Id<sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">

										<input class="form-control input-lg" id="employer_email" placeholder="xx@example.com" name="employer_email" type="email" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Verification Mode:<sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
										<input  id="emp_verify_type"  name="emp_verify_type" type="radio" value="m" checked  data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Mobile</label>
										<input  id="emp_verify_type"  name="emp_verify_type" type="radio" value="e" checked  data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Email</label>
<h5><i>For verification, users outside of India are requested to choose the email mode.</i></h5>
									</div>
								</div>
							</div>

						</div>

						<!--Third Party Agency-->
						<div id="agencySection" style="display: none;">
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Agency Name <sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">

										<input class="form-control input-lg" id="agency_name" name="agency_name" type="text" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-6 col-md-6 col-lg-5" >
										<label>Agency CIIN / Registration Number <sup>*</sup></label>
									</div>
									<div class="col-xs-6 col-md-6 col-lg-7">

										<input class="form-control input-lg" id="agency_reg_no" name="agency_reg_no" type="text" onkeypress="return isAlphaNumeric(event)" minlength="1" maxlength="25" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
								<label>Working Sector <sup>*</sup> </label>
								</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
								<select class="form-control" name="agency_working_sector" id="agency_working_sector" data-rule-required="true">
									<option value="" disabled selected>Select Working Sector</option>
									<option value="Public sector">Public sector</option>
									<option value="Private sector">Private sector</option>
									<option value="Government Body">Government Body</option>
									<option value="Public Sector Unit">Public Sector Unit</option>
								</select>
								</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Address
											<?php if ($subdomain[0] != "monad") {?>
											<sup>*</sup>
											<?php }?>
										</label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
										<?php if ($subdomain[0] == "monad") {?>
										<textarea class="form-control input-lg" id="agency_address" placeholder="Mumbai" name="agency_address" type="text" autofocus></textarea>
										<?php } else {?>
										<textarea class="form-control input-lg" id="agency_address" placeholder="Mumbai" name="agency_address" type="text" autofocus data-rule-required="true"></textarea>
										<?php }?>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Mobile No <sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">

										<input class="form-control input-lg" id="agency_mob_no" placeholder="9xxxxxxxxx" name="agency_mob_no" type="text" onkeypress="return isNumberKey(event)" minlength="10" maxlength="10" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Email Id<sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">

										<input class="form-control input-lg" id="agency_email" placeholder="a@example.com" name="agency_email" type="email" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Verification Mode:<sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
										<input  id="agency_verify_type"  name="agency_verify_type" type="radio" value="1" checked  data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Mobile</label>
										<input  id="agency_verify_type"  name="agency_verify_type" type="radio" value="1" checked  data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Email</label>
<h5><i>For verification, users outside of India are requested to choose the email mode.</i></h5>
									</div>
								</div>
							</div>

						</div>

						<!--Student Section-->
						<div id="studentSection" style="display: none;">
							<div class="form-group">
								<div class="row">
									<div class="col-xs-23 col-md-3 col-lg-4" >
										<label>Student Name <sup>*</sup></label>
									</div>
									<div class="col-xs-6 col-md-4 col-lg-4">

										<input class="form-control input-lg" id="student_f_name" placeholder="First Name" name="student_f_name" type="text" autofocus data-rule-required="true">
									</div>

									<div class="col-xs-6 col-md-4 col-lg-4">

										<input class="form-control input-lg" id="student_l_name" placeholder="Last Name" name="student_l_name" type="text" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<?php
if ($subdomain[0] == "galgotias") {?>
							<input type="hidden" name="student_institute" id="student_institute" value="Galgotias University">
							<?php } else if ($subdomain[0] == "monad") {?>
							<input type="hidden" name="student_institute" id="student_institute" value="Monad University">
							<?}else{ ?>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
								<label>Institute <sup>*</sup> </label>
								</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
								<select class="form-control" name="student_institute" id="student_institute" data-rule-required="true">
									<option value="" disabled selected>Select Institute</option>

									<?php if ($subdomain[0] == "monad"){ ?>
										<option value="Monad University">Monad University</option>
								     
								     <?php }else{ ?>
								     	<option value="G H Raisoni College of Engineering, Nagpur">G H Raisoni College of Engineering, Nagpur</option>

								     <?php }?>
								</select>
								</div>
								</div>
							</div>
							<?php }?>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" style="margin-bottom: 10px;">
									<label>Degree <sup>*</sup> </label>
									</div>
									<div class="col-xs-9 col-md-4 col-lg-3" style="margin-bottom: 10px;">
									<select class="form-control" name="student_degree" id="student_degree" data-rule-required="true">
										<option value="" disabled selected>Select Degree</option>

									</select>
									</div>
									<div class="col-xs-3 col-md-2 col-lg-2" >
									<label>Branch <sup>*</sup> </label>
									</div>
									<div class="col-xs-9 col-md-3 col-lg-3">
									<select class="form-control" name="student_branch" id="student_branch" data-rule-required="true">
										<option value="" disabled selected>Select Branch</option>

									</select>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
								<label>Passout Year <sup>*</sup> </label>
								</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
								<select class="form-control" name="passout_year" id="passout_year" data-rule-required="true">
									<option value="" disabled selected>Select Year</option>
									<?php
$currently_selected = date('Y');
$earliest_year = 2000;
$latest_year = date('Y');

foreach (range($latest_year, $earliest_year) as $i) {
    echo '<option value="' . $i . '"' . ($i === $currently_selected ? ' selected="selected"' : '') . '>' . $i . '</option>';
}

?>
								</select>
								</div>
								</div>
							</div>

							<?php
if ($subdomain[0] == "galgotias") {?>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Student Institute Admission number / Enrolment number <sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-6 col-lg-8">

										<input class="form-control input-lg" id="student_reg_no" name="student_reg_no" type="text" onkeypress="return isAlphaNumeric(event)" placeholder="2015ACSC1234567" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<?php } else {?>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Student Institute Registration Number / Enrollment Number <sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-6 col-lg-8">

										<input class="form-control input-lg" id="student_reg_no" name="student_reg_no" type="text" onkeypress="return isAlphaNumeric(event)" placeholder="2015ACSC1234567" minlength="10" maxlength="20" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<?php }?>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Mobile No <sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">

										<input class="form-control input-lg" id="student_mob_no" placeholder="9xxxxxxxxx" name="student_mob_no" type="text" onkeypress="return isNumberKey(event)" minlength="10" maxlength="10" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Email Id<sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">

										<input class="form-control input-lg" id="student_email" placeholder="a@example.com" name="student_email" type="email" autofocus data-rule-required="true">
									</div>
								</div>
							</div>
<div class="form-group">
								<div class="row">
									<div class="col-xs-3 col-md-3 col-lg-4" >
										<label>Verification Mode:<sup>*</sup></label>
									</div>
									<div class="col-xs-9 col-md-9 col-lg-8">
										<input  id="student_verify_type"  name="student_verify_type" type="radio" value="1" checked  style="vertical-align: top;">&nbsp;&nbsp;<label>Mobile</label>
										<input  id="student_verify_type"  name="student_verify_type" type="radio" value="1" checked  style="vertical-align: top;">&nbsp;&nbsp;<label>Email</label>
<h5><i>For verification, users outside of India are requested to choose the email mode.</i></h5>
									</div>
								</div>
							</div>
							<input type="hidden" name="device_type" id="device_type" value="web"/>
						</div>
						<div class="form-group">
							<div class="row">

								<div class="col-xs-12 col-md-12 col-lg-4" >
								</div>
								<div class="col-xs-12 col-md-6 col-lg-4">
									<button type="submit" id="signup" class="btn btn-sm btn-block" style="margin-bottom: 10px;"><i class="fa fa-check-circle"></i> SignUp</button>
								</div>
								<div class="col-xs-12 col-md-6 col-lg-4" >
									  <button type="button" data-toggle="pill" href="#login-holder" class="btn btn-sm btn-block form-btn" style="border:1px solid #e62121;background-color: #f75b5b !important; " id="login-holder-btn">Login Page</button>

								</div>
							</div>
						</div>
						</fieldset>
					</form>
					</p>
			  </div>

			  <button type="button" data-toggle="pill" href="#verification-holder" class="btn btn-sm btn-block form-btn" style="border:1px solid #e62121;background-color: #f75b5b !important;display: none; " id="showVerification"></button>

			  <div id="verification-holder" class="tab-pane fade">
			  	<div class="title-hdg">
					<div class="row">
						<div class="col-xs-9">
							<h3>Verification </h3>
						</div>
					</div>
				</div>
				<p>
			    <form method="post" class="verificationform" id="verificationform">
						<fieldset>
						<div class="form-group" id="verification-section" style="text-align: center;">

								<div class="row" >
								<div class="col-xs-12 col-md-12 col-lg-12"  >
								<h4>Please enter the 6-digit verification code we sent via <span id="sms">SMS</span> on : <span style="color: #85049c;" id="mobile-no-verification-display">9892630464</span></h4>
								<input type="hidden" name="mobile_no_verification" id="mobile_no_verification" value="0">
<input type="hidden" name="verification_type" id="verification_type" value="">

								<input type="hidden" id="_token" name="_token" value="{{csrf_token()}}">
								</div>

								<div class="col-xs-12 col-md-12 col-lg-12">
									<input type="text" name="otp[]" class="verification-input" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" onkeypress="return isNumberKey(event)" />
		      						<input type="text" name="otp[]" class="verification-input" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" onkeypress="return isNumberKey(event)" />
		      						<input type="text" name="otp[]" class="verification-input" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" onkeypress="return isNumberKey(event)" />
		      						<input type="text" name="otp[]" class="verification-input" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" onkeypress="return isNumberKey(event)" />
		      						<input type="text" name="otp[]" class="verification-input" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" onkeypress="return isNumberKey(event)" />
		      						<input type="text" name="otp[]" class="verification-input" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" onkeypress="return isNumberKey(event)" />
		      						<br>
		      						<span id="otpError" style="color: red;display: none;">Please enter valid otp.</span>
		      					</div>
		      					</div>
		      					<div class="row" style="margin-top: 15px;">
		      					<div class="col-xs-12 col-md-12 col-lg-12">
		      						<button type="submit" id="verify-btn" class="btn btn-sm btn-block form-btn" style="max-width: 200px;margin: auto;"><i class="fa fa-unlock"></i> Verify</button>
		      					</div>
					  			</div>
					  			<div class="row" style="margin-top: 15px;">
		      					<div class="col-xs-12 col-md-12 col-lg-12">
		      						<button type="button" id="resend-otp-btn" class="btn btn-sm btn-block form-btn" style="max-width: 200px;margin: auto;background-color: #85049c !important;border-color: #85049c;display: none;"> Resend Otp</button>
		      						<span style="color: orange;" id="resendBtnMsg">Resend otp button will be enable shortly.</span>
		      					</div>
					  			</div>
						</div>
						</fieldset>

					</form>
				</p>
			  </div>
			  <div id="forgot-password-holder" class="tab-pane fade">
			  	<div class="title-hdg">
					<div class="row">
						<div class="col-xs-9">
							<h3>Forgot Password</h3>
						</div>
					</div>
				</div>
				<p>
					<form method="post" class="forgotpasswordform" id="forgotpasswordform">
						<fieldset>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-12 col-md-12 col-lg-12"  >
									<h4>Please enter your registered email id :</h4>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-xs-12 col-md-12 col-lg-12">
									<input type="hidden" id="_token" name="_token" value="{{csrf_token()}}">
									<input class="form-control input-lg" id="forgot_pwd_email" placeholder="abc@example.com" name="forgot_pwd_email" type="email" value="" data-rule-required="true">
									</div>
								</div>
							</div>
							<div class="row" style="margin-top: 15px;">
		      					<div class="col-xs-12 col-md-12 col-lg-12">
		      						<button type="submit" id="reset-pwd-btn" class="btn btn-sm btn-block form-btn" style="max-width: 200px;margin: auto;">Reset Password</button>
		      					</div>
		      					<div class="col-xs-12 col-md-12 col-lg-12" >
									  <button type="button" data-toggle="pill" href="#login-holder" class="btn btn-sm btn-block form-btn" style="border:1px solid #e62121;background-color: #f75b5b !important;max-width: 200px;margin: auto;margin-top: 15px; " >Login Page</button>

								</div>
					  		</div>
						</fieldset>
					</form>
				</p>
			  </div>
			  <div class="row" id="verificationSuccessMsg" style="display: none;">
								<div class="col-xs-12 col-md-12 col-lg-12"  >
								<h4>Your Mobile No has been verified. Thanks! Please login using credentials sent on email.</h4><br>
								<h5 style="color: blue;">Redirecting to login page...</h5>
								</div>
							</div>
			</div>
		</div>
	</div>
</div>
<!-- jQuery -->
<script src="{{asset('backend/js/jquery.min.js')}}"></script>

<!-- Bootstrap Core JavaScript -->
<script src="{{asset('backend/js/bootstrap.min.js')}}"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="{{asset('backend/js/metisMenu.min.js')}}"></script>

<!-- Custom Theme JavaScript -->
<script src="{{asset('backend/js/sb-admin-2.js')}}"></script>
<!-- ToastrJS -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js" type="text/javascript"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"  type="text/css"> -->
<!-- JQuery Validation -->
<link rel="stylesheet" href="{{asset('backend/css/toastr.min.css')}}">
<script src="{{asset('backend/js/toastr.min.js')}}"></script>
<script src="{{asset('backend/js/jquery.mockjax.js')}}"></script>
  <script src="{{asset('backend/js/jquery.form.min.js')}}"></script>
<script src="{{asset('backend/js/jquery.validate.js')}}"></script>
<script src="{{asset('backend/canvas/js/canvas.js')}}"></script>

<!-- Custom Js-->
<script src="{{asset('backend/js/custom.js')}}"></script>

</body>

</html>

<script>
$(document).ready(function(){

	/* Fetching Dropdown Values */
	var token = "{{ csrf_token() }}";

	$.ajax({
        url: "{{url('/verify/raisoni-degree-master')}}",
        type: 'POST',
        dataType:'json',
        data :{_token:token},
        success: function (data) {
        	if(data.type=="success"){
        		$('#student_degree').html(data.data);
        	}
        },
    });



    $("#student_degree").change(function(){
    	var degree_id = this.value;
    	console.log(degree_id);
		$.ajax({
	        url: "{{url('/verify/raisoni-branch-master')}}",
	        type: 'POST',
	        data :{_token:token,degree_id:this.value},
	        dataType:'json',
	        success: function (data) {
	        	if(data.type=="success"){
	        		$('#student_branch').html(data.data);
	        	}
	        },
	    });
	});

	$("#employer_type").click(function() {
      $("#employerSection").show();
      $("#agencySection").hide();
      $("#studentSection").hide();

  	});

	$("#agency_type").click(function() {
      $("#employerSection").hide();
      $("#agencySection").show();
      $("#studentSection").hide();
  	});

  	$("#student_type").click(function() {
      $("#employerSection").hide();
      $("#agencySection").hide();
      $("#studentSection").show();
  	});


	$('[data-toggle="tooltip"]').tooltip();

	//login ajax
	$("#login").click(function(e){
		e.preventDefault();
		if (!$('.loginform').valid())
		{
            return false;
		}
		else{

			var password = $("#password_l").val();
			var username = $("#username_l").val();

			$.ajax({
	            url: "{{url('/verify/login')}}",
	            type: 'POST',
	            data: {
	            		'username' : username,
						'password' : password,
						"_token": "{{ csrf_token() }}",},
	            dataType:'JSON',
	            success: function (data) {
	            	if(data.status == true){
						toastr["success"](data.message);
						setTimeout(function(){window.location.replace('home')},400);
					}else{
						toastr["error"](data.message);
						if(data.code == '300'){
							//setTimeout(function(){window.location.replace('verify.login')},400);
							$('#mobile-no-verification-display').html(data.mobile_no);
	                        $('#mobile_no_verification').val(data.mobile_no);
	                        $('#showVerification').click();
                            $('#signupform')[0].reset();
                            setTimeout(function() {

                            	 $("#resendBtnMsg").hide();
						        $("#resend-otp-btn").show();
						    }, 10000);
						}else{
							return false;
						}
					}
	            }
	        });
		}

	});

	//signup ajax
	$("form#signupform").submit(function(e){

		var token = "{{ csrf_token() }}";
		e.preventDefault();
		if (!$('.signupform').valid())
		{
            return false;
		}
		else{
			// console.log($(this));
			var formData = new FormData($(this)[0]);

			$.ajax({
	            url: "{{url('/verify/raisoni-registration')}}",
	            type: 'POST',
	            data: formData,
	            dataType:'JSON',
	            cache: false,
	            contentType: false,
	            processData: false,
	            success: function (data) {
	                if(data.status == true){
if(data.verification_type=="m"){
								$('#sms').html('SMS');
	                	$('#mobile-no-verification-display').html(data.mobile_no);
}else{
								$('#sms').html('Email');
								$('#mobile-no-verification-display').html(data.email_id);
							}
	                	
                        $('#mobile_no_verification').val(data.mobile_no);
$('#verification_type').val(data.verification_type);
						//$('#mobile_no_verification').val(data.mobile_no);
                        $('#showVerification').click();
                    	$('#signupform')[0].reset();
	                    setTimeout(function() {

	                    	 $("#resendBtnMsg").hide();
					        $("#resend-otp-btn").show();
					    }, 10000);

	                }else{
	                	toastr["error"](data.message);
	                }
	            },

	        });
		}
	});

	$("form#verificationform").submit(function(e){

		var token = "{{ csrf_token() }}";
		e.preventDefault();
		if (!$('.verificationform').valid())
		{
            return false;
		}else if(!validateOtp()){
	      $('#otpError').show();
	    }else{
			var formData = new FormData($(this)[0]);
			$.ajax({
	            url: "{{url('/verify/verification')}}",
	            type: 'POST',
	            data: formData,
	            dataType:'JSON',
	            cache: false,
	            contentType: false,
	            processData: false,
	            success: function (data) {
	                 if(data.status == true){
                            toastr["success"](data.message);
                            $('#verificationform')[0].reset();
                            $('#verification-section').hide();
                            $('#verificationSuccessMsg').show();
                            setTimeout(function() {
                            	$('#verificationSuccessMsg').hide();
                        	 $('#login-holder-btn').click();
					    }, 10000);


                        }else{
                        	toastr["error"](data.message);
                        }
	            },

	        });
		}
	});

	$("#resend-otp-btn").click(function() {
		 var token = "{{ csrf_token() }}";
	      $.ajax({
		        url: "{{url('/verify/resend-otp')}}",
		        type: 'POST',
		        dataType:'json',
		        data :{_token:token,"mobile_no":$('#mobile_no_verification').val(),'verification_type':$('#verification_type').val()},
		        success: function (data) {
		        	if(data.status == true){
                            toastr["success"](data.message);
                    }else{
							toastr["error"](data.message);
                    }
		        },
		    });
		   return false;
	});

	function validateOtp(){

        var otpInput = document.getElementsByName('otp[]');
        for (i=0; i<otpInput.length; i++)
            {
             if (otpInput[i].value == "")
                {

                 return false;
                }
            }
             return true;
    }


	$("form#forgotpasswordform").submit(function(e){
		e.preventDefault();
		var token = "{{ csrf_token() }}";
		if (!$('.forgotpasswordform').valid())
		{
            return false;
		}else{
			var formData = new FormData($(this)[0]);
			$.ajax({
	            url: "{{url('/verify/forgot-password')}}",
	            type: 'POST',
	            data: formData,
	            success: function (data) {
	                if(data.status == true){
	                            toastr["success"](data.message);
	                            $('#forgotpasswordform')[0].reset();
	                            $('#login-holder-btn').click();

	                        }else{

	                        	toastr["error"](data.message);
	                        }
	            },
	            cache: false,
	            contentType: false,
	            processData: false,
	            dataType:'JSON'
	        });
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

	$(".verification-input").keyup(function () {
    if (this.value.length == this.maxLength) {
      $(this).next('.verification-input').focus();
    }
});

});

</script>

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

#login,#signup, .form-btn{
	background:#0052CC !important;
	border:1px solid #0052CC;
	border-radius:4px;
	/*margin-top:15px !important;*/
	color:#fff;
	text-transform:uppercase;
	font-size:14px;
	font-weight:700;
	padding:7px 0;
}

#login:hover, #signup:hover{
	border-color:#0747A6;
	background:#0747A6 !important;
}

#signup-holder-btn:hover{
	border-color:#05a705 ;
	background:#54b954   !important;
	color: #fff !important;
}

#forgot-password-holder-btn:hover{
	border-color:#e62121;
	background:#f75b5b  !important;
	color: #fff !important;
}

#login-holder-btn{
	color: #fff !important;
}

#resend-otp-btn{
	color: #fff !important;
}

#reset-pwd-btn {
	color: #fff !important;
}
#verify-btn{
	color: #fff !important;
}
.form-btn{
	color: #fff !important;
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
	max-width:750px;
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
    top: -15px;
    right: 20px;
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
	right:25px;
	cursor:pointer;
}
.verification-input {
        margin: 0 5px;
        text-align: center;
        line-height: 30px;
        font-size: 30px;
        border: solid 1px #ccc;
        box-shadow: 0 0 5px #ccc inset;
        outline: none;
        width: 50px;
        transition: all .2s ease-in-out;
        border-radius: 3px;

        &:focus {
          border-color: purple;
          box-shadow: 0 0 5px purple inset;
        }

        &::selection {
          background: transparent;
        }
      }

@media screen and (max-width: 600px) {
.btn{font-size: 13px!important;}

}
@media screen and (max-width: 450px) {

#captcha_image{
    width: 210px !important;
}
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
        'position' : 'relative',
         'margin' : 'auto',
       /* 'left' : '50%',
        'top' : '50%',
        'margin-left' : -$('.login-bg').outerWidth()/2,
        'margin-top' : -$('.login-bg').outerHeight()/2*/
    });
}

$('.signupform').validate({
	errorElement: 'div',
	errorClass: 'help-inline',
	focusInvalid: false,
	rules: {
		employer_name: {minlength: 1,maxlength:255,required:true},
		employer_reg_no:{minlength: 1,maxlength:25,required:true},
		employer_working_sector:{required:true},
		<?php	if ($subdomain[0] == "monad") {?>
			employer_address:{maxlength:1024},
		<?php } else {?>
			employer_address:{minlength: 1,maxlength:1024,required:true},
		<?php }?>
		employer_mob_no: {minlength: 10,maxlength: 10,number:true},
		employer_email: {minlength: 1,maxlength:255,required:true},
		agency_name: {minlength: 1,maxlength:255,required:true},
		agency_reg_no:{minlength: 1,maxlength:25,required:true},
		agency_working_sector:{required:true},
		<?php	if ($subdomain[0] == "monad") {?>
		agency_address:{maxlength:1024},
		<?php } else {?>
		agency_address:{minlength: 1,maxlength:1024,required:true},
		<?php }?>
		agency_mob_no: {minlength: 10,maxlength: 10,number:true	},
		agency_email: {minlength: 1,maxlength:255,required:true},
		student_f_name: {minlength: 1,maxlength:255,required:true},
		student_l_name: {minlength: 1,maxlength:255,required:true},
		student_institute:{required:true},
		student_degree:{required:true},
		student_branch:{required:true},
		passout_year:{required:true},
		<?php
if ($subdomain[0] == "galgotias") {?>
		student_reg_no:{required:true},
		<?php } else {?>
		student_reg_no:{minlength: 10,maxlength:20,required:true},
		<?php }?>
		student_mob_no: {minlength: 10,maxlength: 10,number:true},
		student_email: {minlength: 1,maxlength:255,required:true},
},
	messages: {
		employer_name: {required:"Employer name is required",maxlength:"Employer name with maximum 256 characters allowed"},
		employer_reg_no: {required:"Employer registration no is required",minlength:"Employer registration no is required",maxlength:"Employer registration no is required"},
		employer_working_sector:{required:"Working sector is required"},
		employer_address: {required:"Address is required",maxlength:"Address with maximum 1024 characters allowed"},
		employer_mob_no: {required:"Employer mobile no is required",minlength:"Employer mobile no should be of 10 digit",maxlength:"Employer mobile no should be of 10 digit"},
		employer_email:{required:"Employer email is required"},
		agency_name: {required:"Agency name is required",maxlength:"Agency name with maximum 256 characters allowed"},
		agency_reg_no: {required:"Agency registration no is required",minlength:"Agency registration no is required",maxlength:"Agency registration no is required"},
		agency_working_sector:{required:"Working sector is required"},
		agency_address: {required:"Address is required",maxlength:"Address with maximum 1024 characters allowed"},
		agency_mob_no: {required:"Agency mobile no is required",minlength:"Agency mobile no should be of 10 digit",maxlength:"Agency mobile no should be of 10 digit"},
		agency_email:{required:"Agency email is required"},
		student_f_name:{required:"First name is required"},
		student_l_name:{required:"Last name is required"},
		student_institute:{required:"Institute is required"},
		student_degree:{required:"Student degree is required"},
		student_branch:{required:"Student branch is required"},
		passout_year:{required:"Passout year is required"},
		student_reg_no: {required:"Student registration no is required",minlength:"Student registration no should be of 10 to 20 characters",maxlength:"Student registration no should be of 10 to 20 characters"},
		student_mob_no: {required:"Student mobile no is required",minlength:"Student mobile no should be of 10 digit",maxlength:"Student mobile no should be of 10 digit"},
		student_email:{required:"Student email is required"},
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
$('.loginform').validate({
	errorElement: 'div',
	errorClass: 'help-inline',
	focusInvalid: false,
	rules: {
	username_l: {
		required:true
	},
	password_l: {
		required:true
	},
	/*ct_captcha: {
		required:true
	},*/
},
	messages: {
		username_l: "Username is required",
		password_l: "Password is required",
		/*ct_captcha: "Enter valid captcha",*/
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

$('.forgotpasswordform').validate({
	errorElement: 'div',
	errorClass: 'help-inline',
	focusInvalid: false,
	rules: {
	forgot_pwd_email: {
		required:true
	}
},
	messages: {
		forgot_pwd_email: "Email is required"
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
<script type="text/javascript">
    $(function() {
  'use strict';

  var body = $('.verification-input');

  function goToNextInput(e) {
    var key = e.which,
      t = $(e.target),
      sib = t.next('input');

    if (key != 9 && (key < 48 || key > 57)) {
      e.preventDefault();
      return false;
    }

    if (key === 9) {
      return true;
    }

    if (!sib || !sib.length) {
      sib = body.find('input').eq(0);
    }
    sib.select().focus();
  }

  function onKeyDown(e) {
    var key = e.which;

    if (key === 9 || (key >= 48 && key <= 57)) {
      return true;
    }

    e.preventDefault();
    return false;
  }

  function onFocus(e) {
    $(e.target).select();
  }

  $(".verification-input").keyup(function(e){

 goToNextInput(e);
});



})
</script>