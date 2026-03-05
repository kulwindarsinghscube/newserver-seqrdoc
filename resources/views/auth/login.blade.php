<!-- 
<?php session_start();
if(isset($_GET['logout'])){
    if($_GET['logout'] == 'true'){
        session_destroy();
    }
}
if(isset($_SESSION['is_logged'])){
    if($_SESSION['is_logged'] === 1){
        // if($_SESSION['is_admin'] == 1)
            header('Location: indexFile/dashboard.php');
        // else
        //  header('Location: WebApp/login.php');
        exit();
    }
}
?> -->
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SeQR Admin</title>
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
</head>

<body>
<div class="container-fluid">
    <div class="row">
    
        <div class="col-xs-12">
            <div class="pull-left">
                <h2 style="font-family: 'Abel', sans-serif; color:#fff;">SeQR Admin</h2>
            </div>
            <div class="pull-right">
            <h2>
                <a href="https://play.google.com/store/apps/details?id=seqrprintscan.scube.com.seqrprintscan&hl=en" target="_blank"><img src="webapp/assets/images/gplay.png"/></a>
                <a href="https://appsto.re/in/PNGplb.i" target="_blank"><img src="webapp/assets/images/store.png" /></a>
            </h2>
            </div>
        </div>
    </div>
    <?php if(isset($_GET['inactivity'])):?>
    <div class="row">
        <div class="col-xs-4"></div>
        <div class="col-xs-5">
            
            <div class="alert alert-warning alert-dismissible">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>Auto Logged out due to inactivity</strong> 
            </div>      
        </div>
    </div>  
    <?php endif;?>
    <div class="row">
        <div class="login-bg clearfix">
            <div class="title-hdg">
                <div class="row">
                    <div class="col-xs-9">
                        <h3>Admin Login</h3>
                        <h6>Dont have an admin account ? <a href="{{ url('webapp/login') }}">click here</a></h6>
                    </div>
                    <div class="col-xs-3">
                        <h3><i class="fa fa-fw fa-user-secret fa-2x"></i><h3>
                    </div>
                </div>
            </div>
            <div class="tab-content">
                <div id="login-holder" class="tab-pane fade in active">
                    <p>
                        <form method="POST" action="{{ URL::route('admin.login') }}">
                        @csrf
                            <fieldset>
                                <div class="form-group">
                                    <label>Username</label>
                                    <input class="form-control input-lg" id="username" placeholder="john" name="email" type="email" value="{{old('username')}}" autofocus>
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('email')}}</strong>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input class="form-control input-lg" id="password" placeholder="**********" name="password" type="password" value="">
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('password')}}</strong>
                                    </span>
                                </div>
                                @if(!@empty($success))
                                      <strong class="text-danger">{{ $success}}</strong> 
                                @endempty
                                <!-- Change this to a button or input when using this as a form -->
                              <button type="submit" id="login" class="btn btn-sm btn-block"><i class="fa fa-unlock"></i> Login</button>
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
<!-- JQuery Validation -->
<script src="{{asset('/backend/js/jquery.mockjax.js')}}"></script>
<script src="{{asset('/backend/js/jquery.form.js')}}"></script>
<script src="{{asset('/backend/js/jquery.validate.js')}}"></script>
</body>

</html>
<script>
$(document).ready(function(){
    
    // var ajaxURL = "functions/loginModel.php";
    
    // $("#login").click(function(e){
    //     e.preventDefault();
    //     var password = $("#password_l").val();
    //     var username = $("#username_l").val();

    //     $.post( 
    //         ajaxURL,
    //         {   'type' : 'login',
    //             'username' : username,
    //             'password' : password
    //         },
    //         function(data){
    //             console.log(data);
    //             if(data.type == 'success'){
    //                 toastr["success"](data.message);
    //                 setTimeout(function(){window.location.replace('indexFile/dashboard.php')},400);
    //             }
    //             else{
    //                 toastr["error"](data.message);
    //             }
                
    //         },'json'
            
    //     );
        
    // });
    
    alignMiddle();
    
    function alignMiddle(){
        $('.login-bg').css({
            'position' : 'absolute',
            'left' : '50%',
            'top' : '50%',
            'margin-left' : -$('.login-bg').outerWidth()/2,
            'margin-top' : -$('.login-bg').outerHeight()/2
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
</style>