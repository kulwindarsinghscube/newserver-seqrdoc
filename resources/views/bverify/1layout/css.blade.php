
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
<!-- <link href="vendor/font/AbelRoboto.css" rel="stylesheet"> -->
<link rel="stylesheet" href="{{asset('backend/css/AbelRoboto.css')}}">  
<link rel="stylesheet" href="{{asset('backend/css/toastr.min.css')}}">   
<style type="text/css">
    body{
    background-image: linear-gradient(to bottom, rgba(34, 143, 176, 0.3), rgba(111, 128, 206, 0.4)), url("assets/images/bg2.jpg");
    background:#fff;
    color:#172B4D;
    background-size:cover;
    background-attachment:fixed;
}
.card{
    background:#fff;
    box-shadow:1px 1px 5px #444;
    border-radius:4px;
    margin:20px 0;
}

.cardpadding{
padding:0px;
}

.navbar{
    background:#0052CC;
    border:0px;
    border-radius:0px;
    box-shadow:0px 1px 3px #555;
}

.navbar-default .navbar-nav>li>a{
    color:#DEEBFF;
    font-family:roboto;
    font-weight:500;
    font-size:15px;
    padding:10px;
    margin:10px;
}
.navbar-default .navbar-nav>li>a:focus, .navbar-default .navbar-nav>li>a:hover{
    color:#fff;
    background:#0747A6;
    padding:10px;
    margin:10px;
    border-radius:4px;
}

.navbar-brand{
    margin-top:5px;
    font-family:abel;
    font-weight:500;

}

.navbar-default .navbar-nav>.active>a, .navbar-default .navbar-nav>.active>a:focus, .navbar-default .navbar-nav>.active>a:hover{
    color:#fff;
    background:#0747A6;
    padding:10px;
    margin:10px;
    border-radius:4px;
}

.navbar-default .navbar-nav>.open>a, .navbar-default .navbar-nav>.open>a:focus, .navbar-default .navbar-nav>.open>a:hover{
    color:#fff;
    background:#0747A6;
    padding:10px;
    margin:10px;
    border-radius:4px;
}

.dropdown-menu>li>a{
    padding:10px;
}

.dropdown-menu>.active>a, .dropdown-menu>.active>a:focus, .dropdown-menu>.active>a:hover{
    color:#fff;
    background:#0747A6;
    padding:10px;
}

.data-table-cust-btn {cursor: pointer;}
.help-inline{
    color:red;
    font-weight:normal;
}
.loader{
    display: table;
    background: rgba(0,0,0,0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    text-align: center;
    vertical-align: middle;
}

.loader-content{
    display:table-cell;
    vertical-align: middle;
    color:#fff;
}
</style>