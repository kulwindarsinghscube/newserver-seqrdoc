<body>
<nav class="navbar navbar-default">
  <div class="container"  style="width: 100%;">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span> 
      </button>
      <a class="navbar-brand" href="{{ URL::route('superadmin.dashboard') }}"><i class="fa fa-user-secret fa-fw"></i> SeQR Web Admin</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
    <ul class="nav navbar-nav">
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-credit-card fa-fw"></i>  Website Management<span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="<?= URL::route('website-permission.index') ?>"><i class="fa fa-money fa-fw"></i>  Website RolePermission</a></li>
        </ul>
      </li>
       <li  class="dropdown">
        <a  href="<?= URL::route('copy-template.index') ?>"><i class="fa fa-files-o fa-fw"></i> Copy Templates</a>
        
      </li>
      <li  class="dropdown">
        <a  href="<?= URL::route('superadmin.masterdata') ?>"><i class="fa fa-list fa-fw"></i> Master Data</a>
        
      </li>
       <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-foursquare fa-fw"></i>  Fonts<span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="<?= URL::route('superfontmaster.index') ?>"><i class="fa fa-book fa-fw"></i>  Fonts Master</a></li>

        </ul>
      </li>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-bar-chart fa-fw"></i>  InfoGraphy<span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="<?= URL::route('superadmin.infography') ?>"><i class="fa fa-book fa-fw"></i> Overall Consumption</a></li>
          <li><a href="<?= URL::route('superadmin.monthlyconsumption') ?>"><i class="fa fa-area-chart fa-fw"></i> Monthly Consumption</a></li>
          <li><a href="<?= URL::route('superadmin.consumptioncomparison') ?>"><i class="fa fa-line-chart fa-fw"></i> Consumption Comparison</a></li>

        </ul>
      </li> 
     
      <ul class="nav navbar-nav navbar-right" style="margin-left: 320px;">
      <li class="dropdown" >
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative" style="">
          <img src="/backend/seqr_scan.png" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> SeQR Admin
        <span class="caret"></span></a>
        <ul class="dropdown-menu">  
                <li><a href="<?=route('superadmin.logout')?>"><span class="fa fa-fw fa-sign-out">Logout</span></a></li>
        </ul>
      </li>
    </ul>
    </div>
  </div>
</nav>