<body>
<nav class="navbar navbar-default">
  <div class="container" style="width: 98%;">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span> 
      </button>
      <a class="navbar-brand" href="{{ URL::route('admin.dashboard') }}"><i class="fa fa-user-secret fa-fw"></i> SeQR Web Admin</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
		<ul class="nav navbar-nav">


            @if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.index') || (App\Helpers\SitePermissionCheck::isPermitted('background-master.index')) || (App\Helpers\SitePermissionCheck::isPermitted('template-master.index')) || (App\Helpers\SitePermissionCheck::isPermitted('processExcel.index')) || (App\Helpers\SitePermissionCheck::isPermitted('dynamic-image-management.index')) || (App\Helpers\SitePermissionCheck::isPermitted('idcards.index'))) 
                
			@if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.index') || (App\Helpers\RolePermissionCheck::isPermitted('background-master.index')) || (App\Helpers\RolePermissionCheck::isPermitted('template-master.index')) || (App\Helpers\RolePermissionCheck::isPermitted('processExcel.index')) || (App\Helpers\RolePermissionCheck::isPermitted('dynamic-image-management.index')) || (App\Helpers\RolePermissionCheck::isPermitted('idcards.index')))  

			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-book fa-fw"></i> Document Setup<span class="caret"></span></a>
				<ul class="dropdown-menu">
			        
			        @if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.index'))
					<li><a href="{{ URL::route('fontmaster.index') }}"><i class="fa fa-foursquare fa-fw"></i> Font Master</a></li>
					@endif
					@endif
					 
					@if(App\Helpers\SitePermissionCheck::isPermitted('background-master.index')) 
					@if(App\Helpers\RolePermissionCheck::isPermitted('background-master.index'))
					<li><a href="{{ URL::route('background-master.index') }}"><i class="fa fa-file-o"></i> Background Template Management</a></li>
					@endif
					@endif
                    
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('template-master.index'))
                    @if(App\Helpers\RolePermissionCheck::isPermitted('template-master.index'))
					<li><a href="<?= URL::route('template-master.index')?>"><i class="fa fa-file-o"></i> Template Management</a></li>
					@endif
					@endif
					
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('processExcel.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('processExcel.index'))
					<li><a href="{{ URL::route('processExcel.index') }}"><i class="fa fa-file-excel-o"></i> Process Excel</a></li>
                    @endif
                    @endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('dynamic-image-management.index'))
                    @if(App\Helpers\RolePermissionCheck::isPermitted('dynamic-image-management.index'))
					<li><a href="<?= URL::route('dynamic-image-management.index')?>"><i class="fa fa-image"></i> Dynamic Image Management</a></li>
                    @endif
                    @endif
                   
                    @if(App\Helpers\SitePermissionCheck::isPermitted('idcards.index'))
                    @if(App\Helpers\RolePermissionCheck::isPermitted('idcards.index'))
					<li><a href="{{ route('idcards.index') }}"><i class="fa fa-credit-card fa-fw"></i>Generate ID cards</a></li>
					@endif
					@endif
					
					@if(App\Helpers\SitePermissionCheck::isPermitted('idcard-status.index'))
                    @if(App\Helpers\RolePermissionCheck::isPermitted('idcard-status.index'))
					<li><a href="<?= URL::route('idcard-status.index')?>"><i class="fa fa-credit-card fa-fw"></i>ID cards status</a></li>
					@endif
					@endif	

				</ul>
			</li>
			@endif
			@endif


		
        <?php 
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
       if($subdomain[0]=="localhost" || $subdomain[0]=="bmcc"){ ?>

			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-file-o fa-fw"></i>Customs<span class="caret"></span></a>
				<ul class="dropdown-menu">
			        
			 
					<li><a href="{{ URL::route('bmcc-certificate.uploadpage') }}"><i class="fa fa-file-excel-o fa-fw"></i> BMCC SOM</a></li>
					<li><a href="{{ URL::route('bmcc-certificate.uploadpagePassing') }}"><i class="fa fa-file-excel-o fa-fw"></i> BMCC Passing Certificate</a></li>

					<li><a href="{{ URL::route('fergusson-certificate.uploadpage') }}"><i class="fa fa-file-excel-o fa-fw"></i> Fergusson SOM</a></li>
			
					 <li><a href="{{ URL::route('degree-certifiate.dbUploadfile') }}"><i class="fa fa-file-excel-o fa-fw"></i> Galgotias Degree Certificate</a></li>
					 <li><a href="{{ URL::route('uasb-certifiate.dbUploadfileugpg') }}"><i class="fa fa-file-excel-o fa-fw"></i> UASB UGPG Certificate</a></li>
					 <li><a href="{{ URL::route('uasb-certifiate.dbUploadfilegold') }}"><i class="fa fa-file-excel-o fa-fw"></i> UASB GOLD Certificate</a></li>
					 <li><a href="{{ URL::route('uasb-certificate.index') }}"><i class="fa fa-list fa-fw"></i> Templates</a></li>
					 <li><a href="{{ URL::route('kessc-certificate.uploadpage') }}"><i class="fa fa-file-excel-o fa-fw"></i> KESSC Certificate</a></li>
				</ul>
			</li>
			<?php } ?>
			
            @if(App\Helpers\SitePermissionCheck::isPermitted('pgconfig.index') || (App\Helpers\SitePermissionCheck::isPermitted('pgmaster.index')))

			@if(App\Helpers\RolePermissionCheck::isPermitted('pgconfig.index') || (App\Helpers\RolePermissionCheck::isPermitted('pgmaster.index')))
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-credit-card fa-fw"></i> Payment Setup<span class="caret"></span></a>
				<ul class="dropdown-menu">
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('pgmaster.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('pgmaster.index'))
					<li><a href="{{ URL::route('pgmaster.index') }}"><i class="fa fa-money fa-fw"></i>Payment Gateway</a></li>
					@endif
					@endif
					
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('pgconfig.index'))
                    @if(App\Helpers\RolePermissionCheck::isPermitted('pgconfig.index'))
					<li><a href="{{ URL::route('pgconfig.index') }}"><i class="fa fa-gears fa-fw"></i> PG Configuration</a></li>
					@endif
					@endif
					

				</ul>
			</li>
			@endif
		    @endif

            @if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.index') || (App\Helpers\SitePermissionCheck::isPermitted('printing-detail.index')))

			@if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.index') || (App\Helpers\RolePermissionCheck::isPermitted('printing-detail.index')))
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-newspaper-o fa-fw"></i> Document Management<span class="caret"></span></a>
				<ul class="dropdown-menu">

					@if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.index'))
					<li><a href="{{ route('certificateManagement.index') }}"><i class="fa fa-file-text-o fa-fw"></i> Certificate Management</a></li>
					@endif
					@endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('printing-detail.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('printing-detail.index')) 
					<li><a href="{{ URL::route('printing-detail.index') }}"><i class="fa fa-print fa-fw"></i> Printing Details</a></li>
					@endif
					@endif
					
				</ul>
			</li>
			@endif
            @endif

            @if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.index') || (App\Helpers\SitePermissionCheck::isPermitted('usermaster.index')) ||(App\Helpers\SitePermissionCheck::isPermitted('student.index')) ||(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.index')) || (App\Helpers\SitePermissionCheck::isPermitted('roles.index')) ||(App\Helpers\SitePermissionCheck::isPermitted('systemconfig.index')))
            
			@if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.index') || (App\Helpers\RolePermissionCheck::isPermitted('usermaster.index')) ||(App\Helpers\RolePermissionCheck::isPermitted('student.index')) ||(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.index')) || (App\Helpers\RolePermissionCheck::isPermitted('roles.index')) ||(App\Helpers\RolePermissionCheck::isPermitted('systemconfig.index')))

			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-user fa-fw"></i>  System Config<span class="caret"></span></a>
				<ul class="dropdown-menu">
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.index')) 
					@if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.index')) 
					<li><a href="<?= URL::route('institutemaster.index')?>"><i class="fa fa-building fa-fw"></i> Institute Management</a></li>
					@endif
					@endif
					
                    @if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.index')) 
                    @if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.index')) 
					<li><a href="{{ URL::route('usermaster.index') }}"><i class="fa fa-users fa-fw"></i> User Management</a></li>
					@endif
					@endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('student.index')) 
					@if(App\Helpers\RolePermissionCheck::isPermitted('student.index')) 
					<li><a href="{{ URL::route('student.index') }}"><i class="fa fa-users fa-fw"></i> Student Management</a></li>
					@endif
					@endif

                    @if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.index'))
					<li><a href="{{ URL::route('adminmaster.index') }}"><i class="fa fa-users fa-fw"></i> Admin Management</a></li>
                    @endif
                    @endif

                    @if(App\Helpers\SitePermissionCheck::isPermitted('roles.index'))
                    @if(App\Helpers\RolePermissionCheck::isPermitted('roles.index'))
					<li><a href="{{ URL::route('roles.index') }}"><i class="fa fa-users fa-fw"></i> Roles Management</a></li>
                    @endif
                    @endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('systemconfig.index'))
                    @if(App\Helpers\RolePermissionCheck::isPermitted('systemconfig.index'))
					<li><a href="{{ URL::route('systemconfig.index') }}"><i class="fa fa-cog fa-fw"></i>  Settings</a></li>
					@endif
					@endif
					
				</ul>
			</li>       
			@endif
			@endif
            
            @if(App\Helpers\SitePermissionCheck::isPermitted('template-data.index') ||(App\Helpers\SitePermissionCheck::isPermitted('printer-report.index')) || (App\Helpers\SitePermissionCheck::isPermitted('scanHistory.index')) || (App\Helpers\SitePermissionCheck::isPermitted('transaction.index')) || (App\Helpers\SitePermissionCheck::isPermitted('session-manager.index')))

			@if(App\Helpers\RolePermissionCheck::isPermitted('template-data.index') ||(App\Helpers\RolePermissionCheck::isPermitted('printer-report.index')) || (App\Helpers\RolePermissionCheck::isPermitted('scanHistory.index')) || (App\Helpers\RolePermissionCheck::isPermitted('transaction.index')) || (App\Helpers\RolePermissionCheck::isPermitted('session-manager.index')))
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-file-excel-o fa-fw"></i> Reports
				<span class="caret"></span></a>
				<ul class="dropdown-menu">

				    @if(App\Helpers\SitePermissionCheck::isPermitted('template-data.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('template-data.index'))
					<li><a href="<?= URL::route('template-data.index')?>"><i class="fa fa-file-pdf-o fa-fw"></i> Template Data</a></li>
					@endif
					@endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('printer-report.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('printer-report.index'))
					<li><a href="{{ route('printer-report.index') }}"><i class="fa fa-file-powerpoint-o fa-fw"></i> Printing Report</a></li>
					@endif
					@endif
                   
                  
                    @if(App\Helpers\SitePermissionCheck::isPermitted('scanHistory.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('scanHistory.index'))
					<li><a href="{{ URL::route('scanHistory.index') }}"><i class="fa fa-envira fa-fw"></i> Scan History</a></li>
					@endif
					@endif
 
                    @if(App\Helpers\SitePermissionCheck::isPermitted('transaction.index')) 
					@if(App\Helpers\RolePermissionCheck::isPermitted('transaction.index')) 
					<li><a href="{{ URL::route('transaction.index') }}"><i class="fa fa-cc-visa fa-fw"></i> Payment Transactions</a></li>
					@endif
					@endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('session-manager.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('session-manager.index')) 
					<li><a href="{{route('session-manager.index')}}"><i class="fa fa-lock fa-fw"></i> User Session Manager</a></li>
                    @endif
                    @endif
                    
				</ul>
			</li>
			@endif
			@endif


			@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.index') ||(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingDetails.index')) || (App\Helpers\SitePermissionCheck::isPermitted('sandboxing.scanHistory.index')))

			@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.index') ||(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingDetails.index')) || (App\Helpers\RolePermissionCheck::isPermitted('sandboxing.scanHistory.index')))
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-file-excel-o fa-fw"></i> Sandboxing
				<span class="caret"></span></a>
				<ul class="dropdown-menu">

				    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.index'))
					<li><a href="<?= URL::route('sandboxing.certificate.index')?>"><i class="fa fa-file-pdf-o fa-fw"></i> Certificate management</a></li>
					@endif
					@endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingDetails.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingDetails.index'))
					<li><a href="{{ route('sandboxing.printingDetails.index') }}"><i class="fa fa-file-powerpoint-o fa-fw"></i> Printing details</a></li>
					@endif
					@endif
                   
                  
                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.templateData.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.templateData.index'))
					<li><a href="{{ URL::route('sandboxing.templateData.index') }}"><i class="fa fa-envira fa-fw"></i> Template data</a></li>
					@endif
					@endif
 
                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingReport.index')) 
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingReport.index')) 
					<li><a href="{{ URL::route('sandboxing.printingReport.index') }}"><i class="fa fa-file-powerpoint-o fa-fw"></i> Printing report</a></li>
					@endif
					@endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.scanHistory.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.scanHistory.index')) 
					<li><a href="{{route('sandboxing.scanHistory.index')}}"><i class="fa fa-lock fa-fw"></i> Scan history</a></li>
                    @endif
                    @endif

                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.paymentTransaction.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.paymentTransaction.index')) 
					<li><a href="{{route('sandboxing.paymentTransaction.index')}}"><i class="fa fa-lock fa-fw"></i> Payment transactions</a></li>
                    @endif
                    @endif
                    
				</ul>
			</li>
			@endif
			@endif
			
			@if(App\Helpers\SitePermissionCheck::isPermitted('customs.certificate.index') ||(App\Helpers\SitePermissionCheck::isPermitted('customs.printingDetails.index')))

			@if(App\Helpers\RolePermissionCheck::isPermitted('customs.certificate.index') ||(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingDetails.index')) || (App\Helpers\RolePermissionCheck::isPermitted('sandboxing.scanHistory.index')))
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-file-excel-o fa-fw"></i> Sandboxing
				<span class="caret"></span></a>
				<ul class="dropdown-menu">

				    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.index'))
					<li><a href="<?= URL::route('sandboxing.certificate.index')?>"><i class="fa fa-file-pdf-o fa-fw"></i> Certificate management</a></li>
					@endif
					@endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingDetails.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingDetails.index'))
					<li><a href="{{ route('sandboxing.printingDetails.index') }}"><i class="fa fa-file-powerpoint-o fa-fw"></i> Printing details</a></li>
					@endif
					@endif
                   
                  
                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.templateData.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.templateData.index'))
					<li><a href="{{ URL::route('sandboxing.templateData.index') }}"><i class="fa fa-envira fa-fw"></i> Template data</a></li>
					@endif
					@endif
 
                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingReport.index')) 
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingReport.index')) 
					<li><a href="{{ URL::route('sandboxing.printingReport.index') }}"><i class="fa fa-file-powerpoint-o fa-fw"></i> Printing report</a></li>
					@endif
					@endif
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.scanHistory.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.scanHistory.index')) 
					<li><a href="{{route('sandboxing.scanHistory.index')}}"><i class="fa fa-lock fa-fw"></i> Scan history</a></li>
                    @endif
                    @endif

                    @if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.paymentTransaction.index'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.paymentTransaction.index')) 
					<li><a href="{{route('sandboxing.paymentTransaction.index')}}"><i class="fa fa-lock fa-fw"></i> Payment transactions</a></li>
                    @endif
                    @endif
                    
				</ul>
			</li>
			@endif
			@endif
		</ul>

      @if(App\Helpers\SitePermissionCheck::isPermitted('admin.profile.showprofile') || (App\Helpers\SitePermissionCheck::isPermitted('admin.logout')))	

		@if(App\Helpers\RolePermissionCheck::isPermitted('admin.profile.showprofile') || (App\Helpers\RolePermissionCheck::isPermitted('admin.logout')))	
		<ul class="nav navbar-nav navbar-right">
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative">
					<img src="/backend/seqr_scan.png" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> SeQR Admin
				<span class="caret"></span></a>
				<ul class="dropdown-menu">	
					<li><a href="/webapp/dashboard"><i class="fa fa-video-camera fa-fw"></i> WebApp</a></li>
                    
                    @if(App\Helpers\SitePermissionCheck::isPermitted('admin.profile.showprofile'))
					@if(App\Helpers\RolePermissionCheck::isPermitted('admin.profile.showprofile'))	
					<li><a href="{{ URL::route('admin.profile.showprofile') }}"><i class="fa fa-user fa-fw"></i> My Profile</a></li>
					@endif
					@endif
		            
		            @if(App\Helpers\SitePermissionCheck::isPermitted('admin.logout'))
		            @if(App\Helpers\RolePermissionCheck::isPermitted('admin.logout'))
		            <li><a href="<?=route('admin.logout')?>">&nbsp;<span class="fa fa-fw fa-sign-out">&nbsp;&nbsp;Logout</span></a></li>
					@endif
					@endif
				</ul>
			</li>
		</ul>
	   @else
	     <ul class="nav navbar-nav navbar-right">
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative">
					<img src="/backend/login1.png" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> 
				<span class="caret"></span></a>
				<ul class="dropdown-menu">	
	     <li><a href="<?=route('admin.logout')?>">&nbsp;<span class="fa fa-fw fa-sign-out">&nbsp;&nbsp; Logout</span></a></li>  
         </ul>

	  @endif

	@else
	     <ul class="nav navbar-nav navbar-right">
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative">
					<img src="/backend/login1.png" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> 
				<span class="caret"></span></a>
				<ul class="dropdown-menu">	
	     <li><a href="<?=route('admin.logout')?>">&nbsp;<span class="fa fa-fw fa-sign-out">&nbsp;&nbsp;Logout</span></a></li>  
         </ul>
   @endif 
    </div>
  </div>
</nav>