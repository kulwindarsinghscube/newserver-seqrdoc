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
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="pull-left">
                    <h2 style="font-family: 'Abel', sans-serif; color:#fff;">{{ $sitename }} SeQR Web App</h2>
                </div>
                
           
            </div>
        </div>
         <hr>

        <div class="row">
            <div class="col-xs-12">
                <div class="pull-left">
                    <h5 style="font-family: 'Abel', sans-serif; color:#fff;">The website is down for maintenance. Email on support@scube.net.in for any help.</h2>
                </div>
            </div> 
        </div>
    </div>
</body>
