 	<link rel="stylesheet" href="{{asset('backend/css/bootstrap.min.css')}}">
    <!-- MetisMenu CSS 
    <link href="vendor/metisMenu/metisMenu.min.css" rel="stylesheet">-->
	
	<!-- DataTables CSS 
    <link href="vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">

    <!-- DataTables Responsive CSS
    <link href="vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">-->
    
	<!-- Custom CSS -->
    <link rel="stylesheet" href="{{asset('backend/css/sb-admin-2.css')}}">

    <!-- Custom Fonts -->
    <link rel="stylesheet" href="{{asset('backend/css/font-awesome.min.css')}}">
	
	<!-- DatePicker -->
    <link rel="stylesheet" href="{{asset('backend/css/bootstrap-datetimepicker.css')}}">
	
	<!-- Animate CSS -->
	<link rel="stylesheet" href="{{asset('backend/css/animate.css')}}">
	
	<!-- Selectpicker -->
	<link rel="stylesheet" href="{{asset('backend/css/bootstrap-select.css')}}">
	
	
	
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<link rel="stylesheet" href="{{asset('backend/css/AbelRoboto.css')}}">
	<link rel="stylesheet" href="{{asset('backend/css/toastr.min.css')}}">
	<style>
body{
	background-image: linear-gradient(to bottom, rgba(34, 143, 176, 0.3), rgba(111, 128, 206, 0.4)), url("{{asset('webapp/images/bg2.jpg')}}");
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
</style>
 @yield('style')
</head>
<body>
<nav class="navbar navbar-default">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span> 
      </button>
      <a class="navbar-brand" href="dashboard"><i class="fa fa-qrcode fa-fw"></i>{{ Session::get('site_name') }} SeQR WebApp</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li><a href="dashboard"><i class="fa fa-camera"></i> Scanner</a></li>
       
		<li><a href="{{ URL::route('studentSubscribed.index') }}"><i class="fa fa-users"></i> Students</a></li> 
		 <li><a href="{{ route('scan-history.index') }}"><i class="fa fa-envira"></i> Scan History</a></li> 
        <!--li><a href="transactions.php"><i class="fa fa-cc-visa"></i> Transactions</a></li--> 
      </ul>
      <ul class="nav navbar-nav navbar-right">
		<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative">
					<img src="{{asset('webapp/images/login1.png')}}" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> 
					
				<span class="caret"></span></a>
				<ul class="dropdown-menu">	
					<!--<li><a href="/seqr_doc/gen/dashboard.php" id=""><span class="fa fa-user-secret fa-fw"></span> Web Admin</a></li> -->
					<li><a href="sessionManager.php" id=""><span class="fa fa-lock fa-fw"></span> Session Manager</a></li>
					<li><a href="{{ URL::route('webapp.profile.showprofile') }}" id=""><span class="fa fa-user fa-fw"></span> My Profile</a></li>
					<li><a href="{{ URL::route('webapp.logout') }}" id="logout"><span class="fa fa-fw fa-sign-out"></span>Logout </a></li>
				</ul>
			</li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
	<div class="col-xs-12">
			<div class="cardpadding clearfix">