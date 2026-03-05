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
                <h2 style="font-family: 'Abel', sans-serif; color:#fff;">SeQR Super Admin</h2>
            </div>
            <div class="pull-right">
            <h2>
                <!--<a href="https://play.google.com/store/apps/details?id=seqrprintscan.scube.com.seqrprintscan&hl=en" target="_blank"><img src="webapp/assets/images/gplay.png"/></a>
                <a href="https://appsto.re/in/PNGplb.i" target="_blank"><img src="webapp/assets/images/store.png" /></a> -->
            </h2>
            </div>
        </div>
    </div> 
    <div class="row">
        <div class="login-bg clearfix">
            <div class="title-hdg">
                <div class="row">
                    <div class="col-xs-9">
                        <h3>Super Admin Login</h3>
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
                        <form method="POST" id="superadmin" >
                           @csrf
                            <fieldset>
                                <div class="form-group">
                                    <label>Username</label>
                                    <input class="form-control input-lg" id="username" placeholder="john" name="username" type="text" value="{{old('username')}}" autofocus>
                                    <span class="text-danger" id="username_errors"></span>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input class="form-control input-lg" id="password" placeholder="**********" name="password" type="password" value="">
                                    <span class="text-danger" id="password_errors"></span>
                                </div>
                                 <span class="text-danger" id="credential_not"></span>
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
    
    $("#login").click(function(event) {
        event.preventDefault();
        var redirect="<?= URL::route('superadmin.dashboard') ?>"
        var url_path="<?= URL::route('superadmin.login') ?>";
        var token="{{ csrf_token() }}";
        var method_type="post";
        $("#superadmin").ajaxSubmit({

              url:url_path,
              type:method_type,
              data:{'_token':token},
              beforeSubmit:function(){
                 $("#superadmin").find('span').text(''); 
              },
              success:function(data){
                if(data.success==false)
                {
                    toastr.error(data.msg);
                    $("#credential_not").text(data.msg);
                }
                else if(data.success==true)
                {
                    toastr.success(data.msg);
                    window.location.href=redirect;
                }
              },
              error:function(resobj){
               toastr.error('Something are wrong'); 
               $.each(resobj.responseJSON.errors,function(k,v){
                   
                   $("#"+k+'_errors').text(v);
                });
              }
        });
    });
  
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