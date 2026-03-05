<body>
<nav class="navbar navbar-default">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="{{ URL::route('verify.home') }}"><i class="fa fa-qrcode fa-fw"></i> SeQR WebApp</a>
    </div>
    <?php

    $domain =$_SERVER['HTTP_HOST'];
    $subdomain = explode('.', $domain);?>

    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
      	<li><a href="{{ URL::route('verify.home') }}" data-url="home">HOME</a></li>
        <?php if($subdomain[0]!="monad"){?>
      	<li><a href="{{ URL::route('request.verification.index') }}" data-url="requestverification">Request Verification</a></li>
      	<li><a href="{{ URL::route('verify.pending.payments') }}" data-url="pendingpayments">Pending Payments</a></li>
      	 <li><a href="{{ URL::route('verify.verification.status') }}" data-url="documentverificationrequests">Verification Status</a></li>
        <?php } ?>
        
        <?php if($subdomain[0]=="monad"){?>
      	<li><a href="{{ URL::route('verify.dashboard') }}" data-url="dashboard">Verification</a></li>
      	<li><a href="{{ URL::route('verify.scan.history') }}" data-url="scanhistory">SeQR History</a></li>
        <?php }else{ ?>
          <li><a href="{{ URL::route('verify.dashboard') }}" data-url="dashboard">Scan SeQR</a></li>
        <li><a href="{{ URL::route('verify.scan.history') }}" data-url="scanhistory">SeQR History</a></li>
        <?php
          }
        ?>

      </ul>
      <ul class="nav navbar-nav navbar-right">
		<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative">
					<img src="/backend/seqr_scan.png" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;">
					
				<span class="caret"></span></a>
				<ul class="dropdown-menu">

				  <!-- 
					<li><a href="{{ URL::route('verify.dashboard') }}" id=""><span class="fa fa-user-secret fa-fw"></span> Web Admin</a></li>
				
					<li><a href="{{ URL::route('sessionmanager') }}" id=""><span class="fa fa-lock fa-fw"></span> Session Manager</a></li> -->
					<li><a href="{{ URL::route('profile') }}" id=""><span class="fa fa-user fa-fw"></span> My Profile</a></li>
					<li><a href="{{ URL::route('verify.logout') }}" id="logout"><span class="fa fa-fw fa-sign-out"></span> Logout </a></li>
				</ul>
			</li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
	<div class="col-xs-12">
			<div class="cardpadding clearfix">