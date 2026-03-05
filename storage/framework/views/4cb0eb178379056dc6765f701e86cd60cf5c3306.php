<html>
<head>
	<title>Account Verification</title>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Roboto+Slab" rel="stylesheet">
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	
	<style>
		.center-div{	
			background:#fff;
			border:1px solid #1976d2;
			box-shadow:1px 1px 5px #1976d2;
			padding:35px 30px;
			font-family:'roboto slab';
			box-sizing:border-box;
		}
		
		.green{
			color:#8ebf1d;
		}
		
		.red{
			color:#f25444
		}
		
		.center-div i{
			font-size:60px;
		}
		body{
			background:#f2f2f2 url('../assets/images/scanbg.png') no-repeat;
			background-size:cover;
			background-position:right center;
		}
	</style>
	
</head>
<body>

<div class="">
	<div class="container">
		<div class="col-xs-12 col-md-4 col-md-offset-4">
			<img src="<?php echo e(asset('backend/images/SeQR.png')); ?>" class="center-block" style="height:90px;width:90px;">
		</div>
		<div class="col-xs-12 col-md-4 col-md-offset-4">
			<br><br><br>
			<div class="center-div">
				<center>
					<?php if($status == 1): ?>
						<i class="fa fa-check-circle green"></i><h2>Success!</h2>
						Congrats! Your account has been verified successfuly.<br> <a href="<?php echo e(URL::route('webapp.index')); ?>">Click here</a> to login with your username and password.
					<?php elseif($status == 2): ?>
						<i class="fa fa-times-circle red"></i><h2>Error!</h2>
						Invalid verification token or account is already verified. Please contact administrator.
					<?php else: ?>
						<i class="fa fa-times-circle red"></i><h2>Error!</h2>
						Invalid verification token
					<?php endif; ?>
				</center>
			</div>
		</div>
	</div>
</div>
</body>
</html><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/auth/verified.blade.php ENDPATH**/ ?>