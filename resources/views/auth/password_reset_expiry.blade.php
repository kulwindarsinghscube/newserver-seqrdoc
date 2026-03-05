
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ $sitename }} SeQR</title>
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
	
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<!-- <link href="https://fonts.googleapis.com/css?family=Abel" rel="stylesheet"> -->
    <link href="{{asset('/backend/css/AbelRoboto.css')}}" rel="stylesheet" type="text/css" />

    <link href="https://cdn.jsdelivr.net/npm/icomoon@1.0.0/style.min.css" rel="stylesheet">


</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="pull-left">
                    <h2 style="font-family: 'Abel', sans-serif; color:#fff;">{{ $sitename }} SeQR Web App</h2>
                </div>
                <div class="pull-right">
                <h2>
                    <?php
                    if(isset($site_data['apple_app_url'])){
                    ?>
                    <a href="{{ $site_data['apple_app_url'] }}" target="_blank"><img src="{{asset('/webapp/images/store.png')}}" /></a>
                    <?php } ?>

                    <?php
                    if(isset($site_data['android_app_url'])){
                    ?>
                    <a href="{{ $site_data['android_app_url'] }}" target="_blank"><img src="{{asset('/webapp/images/gplay.png')}}"/></a>
                    <?php } ?>
                </h2>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="login-bg clearfix">
                <!-- <div class="title-hdg">
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <h3><i class="fa fa-fw fa-frown-o fa-2x"></i><h3>
                            <h3>{{ __('Reset Password') }}</h3>
                        </div>
                    </div>
                </div> -->
                
                <div class="tab-content">
                    <div id="" class="tab-pane fade in active text-center">
                        <h3><i class="fa fa-fw fa-frown-o fa-2x"></i><h3>
                        <h3>{{ __('Reset Password') }}</h3>
                        <p>Either the link had already expired on this link was already used to change the password</p>
                        <a href="{{url('/')}}" class="btn btn-danger">Resend Password Reset Link</a>
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
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js" type="text/javascript"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"  type="text/css"> -->
    <!-- JQuery Validation -->
    <link rel="stylesheet" href="{{asset('/backend/css/toastr.min.css')}}" rel="stylesheet" type="text/css" />
    <script src="{{asset('/backend/js/toastr.min.js')}}"></script> 
    <script src="{{asset('/backend/js/jquery.mockjax.js')}}"></script>
    <script src="{{asset('/backend/js/jquery.form.js')}}"></script>
    <script src="{{asset('/backend/js/jquery.validate.js')}}"></script>

</body>
</html>

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

#password_reset{
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

#password_reset:hover{
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
	width:500px;
}

.title-hdg{
	margin-top:10px;
	margin-bottom:10px;
	border-bottom:1px solid #ececec;
	padding:0px 5px ;
}

.loginform label{
	color:#5c5c5c;
}

.help-inline{
	color: #F44336;
    display: inline-block;
    font-size: 12px;
    position: absolute;
    /* top: 4px;
    right: 0; */
    border-radius: 4px
}

.form-group{
	position:relative;
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

</script>