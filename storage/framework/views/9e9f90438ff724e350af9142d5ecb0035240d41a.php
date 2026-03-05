<body>
<nav class="navbar navbar-default">
  <div class="container" style="width: 99%;">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span> 
      </button>
      <a class="navbar-brand" href="<?php echo e(URL::route('admin.dashboard')); ?>"><i class="fa fa-user-secret fa-fw"></i><?php echo e(Session::get('site_name')); ?> SeQR Web Admin</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
		<ul class="nav navbar-nav" style="max-width: 75%;">


            <?php if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.index') || (App\Helpers\SitePermissionCheck::isPermitted('background-master.index')) || (App\Helpers\SitePermissionCheck::isPermitted('template-master.index')) || (App\Helpers\SitePermissionCheck::isPermitted('processExcel.index')) || (App\Helpers\SitePermissionCheck::isPermitted('dynamic-image-management.index')) || (App\Helpers\SitePermissionCheck::isPermitted('idcards.index'))): ?> 
                
			<?php if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.index') || (App\Helpers\RolePermissionCheck::isPermitted('background-master.index')) || (App\Helpers\RolePermissionCheck::isPermitted('template-master.index')) || (App\Helpers\RolePermissionCheck::isPermitted('processExcel.index')) || (App\Helpers\RolePermissionCheck::isPermitted('dynamic-image-management.index')) || (App\Helpers\RolePermissionCheck::isPermitted('idcards.index'))): ?>  

			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-book fa-fw"></i> Document Setup<span class="caret"></span></a>
				<ul class="dropdown-menu">
			        
			        <?php if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.index')): ?>
					<li><a href="<?php echo e(URL::route('fontmaster.index')); ?>"><i class="fa fa-foursquare fa-fw"></i> Font Master</a></li>
					<?php endif; ?>
					<?php endif; ?>
					 
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('background-master.index')): ?> 
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('background-master.index')): ?>
					<li><a href="<?php echo e(URL::route('background-master.index')); ?>"><i class="fa fa-file-o"></i> Background Template Management</a></li>
					<?php endif; ?>
					<?php endif; ?>
                    
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('template-master.index')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('template-master.index')): ?>
					<li><a href="<?= URL::route('template-master.index')?>"><i class="fa fa-file-o"></i> Template Management</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('processExcel.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('processExcel.index')): ?>
					<li><a href="<?php echo e(URL::route('processExcel.index')); ?>"><i class="fa fa-file-excel-o"></i> Process Excel</a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('dynamic-image-management.index')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('dynamic-image-management.index')): ?>
					<li><a href="<?= URL::route('dynamic-image-management.index')?>"><i class="fa fa-image"></i> Dynamic Image Management</a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                   
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('idcards.index')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('idcards.index')): ?>
					<li><a href="<?php echo e(route('idcards.index')); ?>"><i class="fa fa-credit-card fa-fw"></i>Generate ID cards</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('idcard-status.index')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('idcard-status.index')): ?>
					<li><a href="<?= URL::route('idcard-status.index')?>"><i class="fa fa-credit-card fa-fw"></i>ID cards status</a></li>
					<?php endif; ?>
					<?php endif; ?>	

				</ul>
			</li>
			<?php endif; ?>
			<?php endif; ?>

			 <?php $domain = \Request::getHost();
        $subdomain = explode('.', $domain);


        /*if($subdomain[0]=="secura"){
        	echo App\Helpers\SitePermissionCheck::isPermitted('secura-certificate.uploadpage');

        	echo App\Helpers\RolePermissionCheck::isPermitted('secura-certificate.uploadpage');
        exit;
        }*/


       if($subdomain[0]=="demo" ||$subdomain[0]=="uasb" || $subdomain[0]=="galgotias" || $subdomain[0]=="kessc" || $subdomain[0]=="iccs" || $subdomain[0]=="monad" || $subdomain[0]=="ghribmjal" || $subdomain[0]=="secura" ||$subdomain[0]=="po"|| $subdomain[0]=="auro" || $subdomain[0]=="woxsen"|| $subdomain[0]=="test" || $subdomain[0]=="rrmu" || $subdomain[0]=="spit" || $subdomain[0]=="vbit"|| $subdomain[0]=="cedp"|| $subdomain[0]=="iscnagpur" || $subdomain[0]=="sgrsa" || $subdomain[0]=="anu" || $subdomain[0]=="imt" || $subdomain[0]=="sangamuni" || $subdomain[0]=="bnmit" || $subdomain[0]=="uneb" || $subdomain[0]=="estamp" || $subdomain[0]=="minoshacloud" || $subdomain[0]=="secdoc" || $subdomain[0]=="cu" || $subdomain[0]=="srwcnagpur" ||
       	(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.templatelist')&&App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.templatelist'))||
       	(App\Helpers\SitePermissionCheck::isPermitted('bmcc-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('bmcc-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('bmcc-certificate.uploadpagePassing')&&App\Helpers\RolePermissionCheck::isPermitted('bmcc-certificate.uploadpagePassing'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('fergusson-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('fergusson-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('galgotias-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('galgotias-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('kessc-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('kessc-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('iccs-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('iccs-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('monad-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('monad-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('ghribmjal-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('ghribmjal-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('uasb-certifiate.dbUploadfileugpg')&&App\Helpers\RolePermissionCheck::isPermitted('uasb-certifiate.dbUploadfileugpg'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('secura-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('secura-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('kmtc-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('kmtc-certificate.uploadpage')) ||
   		(App\Helpers\SitePermissionCheck::isPermitted('auro-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('auro-certificate.uploadpage')) ||
   		(App\Helpers\SitePermissionCheck::isPermitted('woxsen-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('woxsen-certificate.uploadpage')) ||
   		(App\Helpers\SitePermissionCheck::isPermitted('rrmu-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('rrmu-certificate.uploadpage'))||
		  (App\Helpers\SitePermissionCheck::isPermitted('cavendish-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('cavendish-certificate.uploadpage'))||

		  (App\Helpers\SitePermissionCheck::isPermitted('srwcnagpur-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('srwcnagpur-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('spit-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('spit-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('vbit-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('vbit-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('cedp-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('cedp-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('iscn-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('iscn-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('rawatpura-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('rawatpura-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('mmk-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('mmk-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('bestiu-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('bestiu-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('sdm-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('sdm-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-certificate.addform')&&App\Helpers\RolePermissionCheck::isPermitted('sgrsa-certificate.addform'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-supplier.editform')&&App\Helpers\RolePermissionCheck::isPermitted('sgrsa-supplier.editform'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('anu-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('anu-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('surana-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('surana-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('imt-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('imt-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('sangamui-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('sangamui-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('bnm-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('bnm-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('uneb-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('uneb-certificate.uploadpage'))||
   		(App\Helpers\SitePermissionCheck::isPermitted('bihar-estamp-certificate.uploadpage')&&App\Helpers\RolePermissionCheck::isPermitted('bihar-estamp-certificate.uploadpage'))
   		){ ?>
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-file-o fa-fw"></i>Customs<span class="caret"></span></a>
				<ul class="dropdown-menu">
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.templatelist')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.templatelist')): ?>
					<li><a href="<?php echo e(URL::route('pdf2pdf.templatelist')); ?>"><i class="fa fa-list fa-fw"></i>Pdf2Pdf Templates</a></li>
					<?php endif; ?>
					<?php endif; ?>

					<?php if($subdomain[0]=="demo" ||$subdomain[0]=="test"): ?>
          <li><a href="<?php echo e(URL::route('utility.templates')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Utility - Ghost | Yellow Patch</a></li>
					<?php endif; ?>

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('exceltopdf.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('exceltopdf.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('exceltopdf.uploadpage')); ?>"><i class="fa fa-file-pdf-o fa-fw"></i>PDF Generate</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('bmcc-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('bmcc-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('bmcc-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> BMCC SOM</a></li>
					<?php endif; ?>
					<?php endif; ?>

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('woxsen-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('woxsen-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('woxsen-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> WOXSEN</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('bmcc-certificate.uploadpagePassing')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('bmcc-certificate.uploadpagePassing')): ?>
					<li><a href="<?php echo e(URL::route('bmcc-certificate.uploadpagePassing')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> BMCC Passing Certificate</a></li>
					<?php endif; ?>
					<?php endif; ?>


					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('fergusson-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('fergusson-certificate.uploadpage')): ?>
				
					<li><a href="<?php echo e(URL::route('fergusson-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Fergusson SOM</a></li>
					<?php endif; ?>
					<?php endif; ?>

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('galgotias-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('galgotias-certificate.uploadpage')): ?>
					 <li><a href="<?php echo e(URL::route('galgotias-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Galgotias Degree Certificate</a></li>
					<?php endif; ?>
					<?php endif; ?>
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('uasb-certifiate.dbUploadfileugpg')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('uasb-certifiate.dbUploadfileugpg')): ?>
						<!--  <li><a href="<?php echo e(URL::route('uasb-certifiate.dbUploadfileugpg')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> UASB UGPG Certificate</a></li>
					 <li><a href="<?php echo e(URL::route('uasb-certifiate.dbUploadfilegold')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> UASB GOLD Certificate</a></li> -->
					 <li><a href="<?php echo e(URL::route('uasb-certificate.index')); ?>"><i class="fa fa-list fa-fw"></i>UASB Templates</a></li>
					<?php endif; ?>
					<?php endif; ?>


					<?php if(App\Helpers\SitePermissionCheck::isPermitted('auro-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('auro-certificate.uploadpage')): ?>
					 <li><a href="<?php echo e(URL::route('auro-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> AURO Certificate</a></li>
					<?php endif; ?>
					<?php endif; ?>

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('rrmu-certificate.uploadpage')): ?>
                    			  <?php if(App\Helpers\RolePermissionCheck::isPermitted('rrmu-certificate.uploadpage')): ?>
					    <li><a href="<?php echo e(URL::route('rrmu-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> RRMU Certificate</a></li>
					  <?php endif; ?>
					<?php endif; ?>

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('cavendish-certificate.uploadpage')): ?>
             				  <?php if(App\Helpers\RolePermissionCheck::isPermitted('cavendish-certificate.uploadpage')): ?>
					    <li><a href="<?php echo e(URL::route('cavendish-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> CAVENDISH Certificate</a></li>
					  <?php endif; ?>
					<?php endif; ?>


					<?php if(App\Helpers\SitePermissionCheck::isPermitted('srwcnagpur-certificate.uploadpage')): ?>
             				  <?php if(App\Helpers\RolePermissionCheck::isPermitted('srwcnagpur-certificate.uploadpage')): ?>
					    <li><a href="<?php echo e(URL::route('srwcnagpur-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> SRWCNAGPUR Certificate</a></li>
					  <?php endif; ?>
					<?php endif; ?>

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('kessc-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('kessc-certificate.uploadpage')): ?>
					 <li><a href="<?php echo e(URL::route('kessc-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> KESSC Certificate</a></li>
					 <li><a href="<?php echo e(URL::route('kessc-addserial.uploadpage')); ?>"><i class="fa fa-file-pdf-o fa-fw"></i> KESSC Add Serial No.</a></li>
					<?php endif; ?>
					<?php endif; ?>

					 <?php if(App\Helpers\SitePermissionCheck::isPermitted('iccs-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('iccs-certificate.uploadpage')): ?>
					 <li><a href="<?php echo e(URL::route('iccs-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> ICCS Certificate</a></li>
					 <?php endif; ?>
					<?php endif; ?>

					 <?php if(App\Helpers\SitePermissionCheck::isPermitted('monad-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('monad-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('monad-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> MONAD Certificate</a></li>
					 <?php endif; ?>
					<?php endif; ?>

					 <?php if(App\Helpers\SitePermissionCheck::isPermitted('ghribmjal-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('ghribmjal-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('ghribmjal-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Ghribmjal Certificate</a></li>
					 <?php endif; ?>
					<?php endif; ?>
					<!--Start Molwa-->
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('secura-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('secura-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('secura-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Molwa Documents</a></li>
                    <?php endif; ?>
					<?php endif; ?>
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('molwa-certificate.ImportPrint')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('molwa-certificate.ImportPrint')): ?>
					<li><a href="<?php echo e(URL::route('molwa-certificate.ImportPrint')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Import</a></li>
                    <?php endif; ?>
					<?php endif; ?>
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('molwa-certificate.index')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('molwa-certificate.index')): ?>
					<li><a href="<?php echo e(URL::route('molwa-certificate.index')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Manage Records</a></li>
					<li><a href="<?php echo e(URL::route('molwa-certificate.molwaRemarks')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Molwa Remark Records</a></li>
                    <?php endif; ?>
					<?php endif; ?>
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('molwa-certificate.ReImportPrint')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('molwa-certificate.ReImportPrint')): ?>
					<li><a href="<?php echo e(URL::route('molwa-certificate.ReImportPrint')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Re-Import</a></li>
					<li><a href="<?php echo e(URL::route('molwa-certificate.molwaReport')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Report</a></li>
                    <?php endif; ?>
					<?php endif; ?>
					<!--End Molwa-->
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('kmtc-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('kmtc-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('kmtc-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Kmtc Documents</a></li>
                     <?php endif; ?>
					<?php endif; ?>
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('spit-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('spit-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('spit-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Grade Cards</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('vbit-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('vbit-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('vbit-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Grade Cards</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('cedp-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('cedp-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('cedp-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Grade Cards</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('iscn-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('iscn-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('iscn-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> ISCN Grade Cards</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					
					 <?php if(App\Helpers\SitePermissionCheck::isPermitted('rawatpura-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('rawatpura-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('rawatpura-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> SRSU Raipur Certificate</a></li>
					 <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('bestiu-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('bestiu-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('bestiu-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Bestiu Certificate</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('sdm-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('sdm-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('sdm-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> SDM Certificate</a></li>
					<li><a href="<?php echo e(URL::route('sdm-resit.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> SDM RESIT</a></li>
                     <?php endif; ?>
					<?php endif; ?>


					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('surana-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('surana-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('surana-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Surana Certificate</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('anu-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('anu-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('anu-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> ANU Certificate</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					
					<!--Start sgrsa-->
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-certificate.addform')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-certificate.addform')): ?>
					<li><a href="<?php echo e(URL::route('sgrsa-certificate.addform')); ?>"><i class="fa fa-file-pdf-o fa-fw"></i> New Record</a></li>
					<li><a href="<?php echo e(URL::route('sgrsa-previouscertificate.addform')); ?>"><i class="fa fa-file-pdf-o fa-fw"></i> Old Record</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-supplier.addform')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-supplier.addform')): ?>
					<li><a href="<?php echo e(URL::route('sgrsa-supplier.addform')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Suppliers</a></li>					
                    <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-agent.addform')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-agent.addform')): ?>
					<li><a href="<?php echo e(URL::route('sgrsa-agent.addform')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Sub Agents</a></li>
				    <li><a href="<?php echo e(URL::route('sgrsa-governor.addform')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Type of Governor</a></li>
					<li><a href="<?php echo e(URL::route('sgrsa-governor.adduform')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Unit Serial Numbers</a></li>
					<li><a href="<?php echo e(URL::route('sgrsa-certificate.addform')); ?>"><i class="fa fa-file-pdf-o fa-fw"></i> Recall</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('agent-allot.RecallList')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('agent-allot.RecallList')): ?>
					<li><a href="<?php echo e(URL::route('agent-allot.RecallList')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Agent Allotment</a></li>					
                    <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('supplier-allot.addform')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('supplier-allot.addform')): ?>
					<li><a href="<?php echo e(URL::route('supplier-allot.addform')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Allot HC to Agent</a></li>					
                    <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('allot-edit.editform')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('allot-edit.editform')): ?>
					<li><a href="<?php echo e(URL::route('allot-edit.editform')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Damaged/Replaced HC</a></li>					
                    <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('supplier-uploadexcel.uploadExcelForm')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('supplier-uploadexcel.uploadExcelForm')): ?>
					<li><a href="<?php echo e(URL::route('supplier-uploadexcel.uploadExcelForm')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Upload Previous Data</a></li>					
                    <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-supplier.editform')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-supplier.editform')): ?>
					<li><a href="<?php echo e(URL::route('sgrsa-supplier.editform')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Supplier Profile</a></li>					
                    <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-allot.addform')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-allot.addform')): ?>
					<li><a href="<?php echo e(URL::route('sgrsa-allot.addform')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Allot HC to Supplier</a></li>					
                    <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-previouscertificate.indexPC')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-previouscertificate.indexPC')): ?>
					<li><a href="<?php echo e(URL::route('sgrsa-previouscertificate.indexPC')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Notification</a></li>					
                    <?php endif; ?>
					<?php endif; ?>
					
					<!--End sgrsa-->
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('sangamui-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('sangamui-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(URL::route('sangamui-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Sangam Certificate</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('imt-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('imt-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(route('imt.index')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> IMT Certificate</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('bnm-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('bnm-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(route('bnm-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> BNMI Certificate</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('uneb-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('uneb-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(route('uneb-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> UNEB Certificate</a></li>
                     <?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('bihar-estamp-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('bihar-estamp-certificate.uploadpage')): ?>
					 <li><a href="<?php echo e(URL::route('bihar-estamp-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Certificate</a></li>
					  <li><a href="<?php echo e(URL::route('bihar-estamp-certificate.uploadpageutility')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Certificate - Utility</a></li>
					  <li><a href="<?php echo e(URL::route('bihar-estamp-certificate.uploadpageprinterutility')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Certificate - Printer Utility</a></li>
					 <li><a href="<?php echo e(URL::route('bihar-estamp-certificate.uploadpageapiutility')); ?>"><i class="fa fa-file-excel-o fa-fw"></i>Certificate - API Printer Utility</a></li>
					 <li><a href="<?php echo e(URL::route('bihar-estamp-certificate.uploadpageprinter')); ?>"><i class="fa fa-file-excel-o fa-fw"></i>Old Certificate - Printer</a></li>

					<?php endif; ?>
					<?php endif; ?>
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('minosha-certificate.uploadpage')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('minosha-certificate.uploadpage')): ?>
					<li><a href="<?php echo e(route('minosha-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i> Minosha Custom Templates</a></li>
          <?php endif; ?>
           <?php endif; ?>
				</ul>
			</li>
			<?php } ?>
			<?php $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
       if($subdomain[0]=="mitadt" || $subdomain[0] == "localhost"){ ?>
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-file-o fa-fw"></i>Customs<span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><a href="<?php echo e(URL::route('passing-certificate.uploadpage')); ?>"><i class="fa fa-file-excel-o fa-fw"></i>Passing Certificate</a></li>
				</ul>
			</li>
			<?php } ?>
			<?php if(App\Helpers\SitePermissionCheck::isPermitted('degree-certifiate.index')): ?>

			<?php if(App\Helpers\RolePermissionCheck::isPermitted('degree-certifiate.index')): ?>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-newspaper-o fa-fw"></i> Galgotias Degree Certificate<span class="caret"></span></a>
					<ul class="dropdown-menu">

						<li><a href="<?php echo e(route('degree-certifiate.index')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Generate Degree Certificate</a></li>
						
					</ul>
				</li>
			<?php endif; ?>
			<?php endif; ?>
			
      <?php if(App\Helpers\SitePermissionCheck::isPermitted('pgconfig.index') || (App\Helpers\SitePermissionCheck::isPermitted('pgmaster.index')) || (App\Helpers\SitePermissionCheck::isPermitted('documentsratemaster.index'))): ?>
			<?php if(App\Helpers\RolePermissionCheck::isPermitted('pgconfig.index') || (App\Helpers\RolePermissionCheck::isPermitted('pgmaster.index')) || (App\Helpers\RolePermissionCheck::isPermitted('documentsratemaster.index'))): ?>
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-credit-card fa-fw"></i> Payment Setup<span class="caret"></span></a>
				<ul class="dropdown-menu">          
          <?php if(App\Helpers\SitePermissionCheck::isPermitted('pgmaster.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('pgmaster.index')): ?>
						<li>
							<a href="<?php echo e(URL::route('pgmaster.index')); ?>"><i class="fa fa-money fa-fw"></i>Payment Gateway</a>
						</li>
					<?php endif; ?>
					<?php endif; ?>
					
                    
          <?php if(App\Helpers\SitePermissionCheck::isPermitted('pgconfig.index')): ?>
          <?php if(App\Helpers\RolePermissionCheck::isPermitted('pgconfig.index')): ?>
						<li>
							<a href="<?php echo e(URL::route('pgconfig.index')); ?>"><i class="fa fa-gears fa-fw"></i> PG Configuration</a>
							
						</li>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('documentsratemaster.index')): ?>
          <?php if(App\Helpers\RolePermissionCheck::isPermitted('documentsratemaster.index')): ?>
						<li>
							<a href="<?php echo e(URL::route('documentsratemaster.index')); ?>"><i class="fa fa-gears fa-fw"></i> Document Rate Master</a>
							
						</li>
					<?php endif; ?>
					<?php endif; ?>

				</ul>
			</li>
			<?php endif; ?>
		    <?php endif; ?>

            <?php if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.index') || (App\Helpers\SitePermissionCheck::isPermitted('printing-detail.index')) || (App\Helpers\SitePermissionCheck::isPermitted('vgujaipur.create'))): ?>

			<?php if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.index') || (App\Helpers\RolePermissionCheck::isPermitted('printing-detail.index')) || (App\Helpers\RolePermissionCheck::isPermitted('vgujaipur.create'))): ?>
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-newspaper-o fa-fw"></i> Document Management<span class="caret"></span></a>
				<ul class="dropdown-menu">

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.index')): ?>
					<li><a href="<?php echo e(route('certificateManagement.index')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Certificate Management</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('vgujaipur.create')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('vgujaipur.create')): ?>
					<li><a href="<?php echo e(route('vgujaipur.create')); ?>"><i class="fa fa-file-text-o fa-fw"></i> Print Serial Number</a></li>
					<?php endif; ?>
					<?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('printing-detail.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('printing-detail.index')): ?> 
					<li><a href="<?php echo e(URL::route('printing-detail.index')); ?>"><i class="fa fa-print fa-fw"></i> <?php if($subdomain[0]=="sales"){ echo "Generation details"; }else{ echo "Printing details"; } ?></a></li>
					<?php endif; ?>
					<?php endif; ?>
					
				</ul>
			</li>
			<?php endif; ?>
            <?php endif; ?>


            <?php
	            $domain = \Request::getHost();
				$subdomain = explode('.', $domain);
				if($subdomain[0] == \Config('constant.raisoni_subdomain') || $subdomain[0] == 'demo'|| $subdomain[0] == 'galgotias'|| $subdomain[0] == 'monad')
				{
			?>
			<?php if(App\Helpers\SitePermissionCheck::isPermitted('semester.index') || App\Helpers\SitePermissionCheck::isPermitted('branch.index') || App\Helpers\SitePermissionCheck::isPermitted('degreemaster.index') || App\Helpers\SitePermissionCheck::isPermitted('sessionsmaster.index')): ?>
			<?php if(App\Helpers\RolePermissionCheck::isPermitted('semester.index') || App\Helpers\RolePermissionCheck::isPermitted('branch.index') || App\Helpers\RolePermissionCheck::isPermitted('degreemaster.index') || App\Helpers\RolePermissionCheck::isPermitted('sessionsmaster.index')): ?>
	            <li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-list-alt fa-fw"></i> Masters<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('sessionsmaster.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('sessionsmaster.index')): ?>
							<li><a href="<?php echo e(URL::route('sessionsmaster.index')); ?>"><i class="fa fa-list fa-fw"></i> Session</a></li>
						<?php endif; ?>
						<?php endif; ?>
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('degreemaster.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('degreemaster.index')): ?>
							<li><a href="<?php echo e(URL::route('degreemaster.index')); ?>"><i class="fa fa-list fa-fw"></i> Degree</a></li>
						<?php endif; ?>
						<?php endif; ?>
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('branch.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('branch.index')): ?>
							<li><a href="<?php echo e(URL::route('branch.index')); ?>"><i class="fa fa-list fa-fw"></i> Branch</a></li>
						<?php endif; ?>
						<?php endif; ?>
						
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('semester.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('semester.index')): ?>
							<li><a href="<?php echo e(URL::route('semester.index')); ?>"><i class="fa fa-list fa-fw"></i> Semester</a></li>
						<?php endif; ?>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
			<?php endif; ?>

			<?php if(App\Helpers\SitePermissionCheck::isPermitted('request_testing') || App\Helpers\SitePermissionCheck::isPermitted('oldverification.index')): ?>
			<?php if(App\Helpers\RolePermissionCheck::isPermitted('request_testing') || App\Helpers\RolePermissionCheck::isPermitted('oldverification.index')): ?>
	            <li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-certificate fa-fw"></i> Document Verification<span class="caret"></span></a>
					<ul class="dropdown-menu">
						
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('oldverification.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('oldverification.index')): ?>
							<li><a href="<?php echo e(URL::route('oldverification.index')); ?>"><i class="fa fa-list-ul"></i> Old Documents</a></li>
						<?php endif; ?>
						<?php endif; ?>
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('request_testing')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('request_testing')): ?>
							<li><a href="<?php echo e(URL::route('request_testing')); ?>"><i class="fa fa-list-ul"></i> SeQR Documents</a></li>
						<?php endif; ?>
						<?php endif; ?>
						
					</ul>
				</li>
			<?php endif; ?>
			<?php endif; ?>

			<?php if(App\Helpers\SitePermissionCheck::isPermitted('stationarystock.index') || App\Helpers\SitePermissionCheck::isPermitted('damagedstock.index') || App\Helpers\SitePermissionCheck::isPermitted('consumptionreport.index') || App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.index')): ?>
			<?php if(App\Helpers\RolePermissionCheck::isPermitted('stationarystock.index') || App\Helpers\RolePermissionCheck::isPermitted('damagedstock.index') || App\Helpers\RolePermissionCheck::isPermitted('consumptionreport.index') || App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.index')): ?>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-hdd-o fa-fw"></i> Stock
					<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('stationarystock.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('stationarystock.index')): ?>
							<li><a href="<?php echo e(URL::route('stationarystock.index')); ?>"><i class="fa fa-th-large fa-fw"></i> Stationary Stock</a></li>
						<?php endif; ?>
						<?php endif; ?>
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('damagedstock.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('damagedstock.index')): ?>
							<li><a href="<?php echo e(URL::route('damagedstock.index')); ?>"><i class="fa fa-chain-broken fa-fw"></i> Damaged Stock</a></li>
						<?php endif; ?>
						<?php endif; ?>
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreport.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreport.index')): ?>
							<li><a href="<?php echo e(URL::route('consumptionreport.index')); ?>"><i class="fa fa-align-justify fa-fw"></i> Consumption Report</a></li>
						<?php endif; ?>
						<?php endif; ?>
						<?php if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.index')): ?>
						<?php if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.index')): ?>
							<li><a href="<?php echo e(URL::route('consumptionreportexport.index')); ?>"><i class="fa fa-file-excel-o fa-fw"></i>Download Consumption Report</a></li>
						<?php endif; ?>
						<?php endif; ?>
					</ul>
				</li>
			<?php endif; ?>
			<?php endif; ?>
			<?php
				}
			?>

            <?php if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.index') || (App\Helpers\SitePermissionCheck::isPermitted('usermaster.index')) ||(App\Helpers\SitePermissionCheck::isPermitted('student.index')) ||(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.index')) || (App\Helpers\SitePermissionCheck::isPermitted('roles.index')) ||(App\Helpers\SitePermissionCheck::isPermitted('systemconfig.index'))): ?>
            
			<?php if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.index') || (App\Helpers\RolePermissionCheck::isPermitted('usermaster.index')) ||(App\Helpers\RolePermissionCheck::isPermitted('student.index')) ||(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.index')) || (App\Helpers\RolePermissionCheck::isPermitted('roles.index')) ||(App\Helpers\RolePermissionCheck::isPermitted('systemconfig.index'))): ?>

			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-user fa-fw"></i>  System Config<span class="caret"></span></a>
				<ul class="dropdown-menu">
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.index')): ?> 
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.index')): ?> 
					<li><a href="<?= URL::route('institutemaster.index')?>"><i class="fa fa-building fa-fw"></i> Institute Management</a></li>
					<?php endif; ?>
					<?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('labmaster.index')): ?> 
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('labmaster.index')): ?> 
					<li><a href="<?= URL::route('labmaster.index')?>"><i class="fa fa-file fa-fw"></i> Lab Master</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.index')): ?> 
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.index')): ?> 
					<li><a href="<?php echo e(URL::route('usermaster.index')); ?>"><i class="fa fa-users fa-fw"></i> User Management</a></li>
					<?php endif; ?>
					<?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('student.index')): ?> 
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('student.index')): ?> 
					<li><a href="<?php echo e(URL::route('student.index')); ?>"><i class="fa fa-users fa-fw"></i> Student Management </a></li>
					<?php endif; ?>
					<?php endif; ?>

                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.index')): ?>
					<li><a href="<?php echo e(URL::route('adminmaster.index')); ?>"><i class="fa fa-users fa-fw"></i> Admin Management</a></li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('roles.index')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('roles.index')): ?>
					<li><a href="<?php echo e(URL::route('roles.index')); ?>"><i class="fa fa-users fa-fw"></i> Roles Management</a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('systemconfig.index')): ?>
                    <?php if(App\Helpers\RolePermissionCheck::isPermitted('systemconfig.index')): ?>
					<li><a href="<?php echo e(URL::route('systemconfig.index')); ?>"><i class="fa fa-cog fa-fw"></i>  Settings</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
				</ul>
			</li>       
			<?php endif; ?>
			<?php endif; ?>
            
            <?php if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.index') ||(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingDetails.index')) || (App\Helpers\SitePermissionCheck::isPermitted('sandboxing.scanHistory.index'))): ?>

			<?php if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.index') ||(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingDetails.index')) || (App\Helpers\RolePermissionCheck::isPermitted('sandboxing.scanHistory.index'))): ?>
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-file-excel-o fa-fw"></i> Sandboxing
				<span class="caret"></span></a>
				<ul class="dropdown-menu">

				    <?php if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.index')): ?>
					<li><a href="<?= URL::route('sandboxing.certificate.index')?>"><i class="fa fa-file-pdf-o fa-fw"></i> Certificate management</a></li>
					<?php endif; ?>
					<?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingDetails.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingDetails.index')): ?>
					<li><a href="<?php echo e(route('sandboxing.printingDetails.index')); ?>"><i class="fa fa-file-powerpoint-o fa-fw"></i><?php if($subdomain[0]=="sales"){ echo "Generation details"; }else{ echo "Printing details"; } ?> </a></li>
					<?php endif; ?>
					<?php endif; ?>
                   
                  
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.templateData.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.templateData.index')): ?>
					<li><a href="<?php echo e(URL::route('sandboxing.templateData.index')); ?>"><i class="fa fa-envira fa-fw"></i> Template data</a></li>
					<?php endif; ?>
					<?php endif; ?>
 
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingReport.index')): ?> 
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingReport.index')): ?> 
					<li><a href="<?php echo e(URL::route('sandboxing.printingReport.index')); ?>"><i class="fa fa-file-powerpoint-o fa-fw"></i> Printing report</a></li>
					<?php endif; ?>
					<?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.scanHistory.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.scanHistory.index')): ?> 
					<li><a href="<?php echo e(route('sandboxing.scanHistory.index')); ?>"><i class="fa fa-lock fa-fw"></i> Scan history</a></li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.paymentTransaction.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.paymentTransaction.index')): ?> 
					<li><a href="<?php echo e(route('sandboxing.paymentTransaction.index')); ?>"><i class="fa fa-lock fa-fw"></i> Payment transactions</a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    
				</ul>
			</li>
			<?php endif; ?>
			<?php endif; ?>
			
            <?php if(App\Helpers\SitePermissionCheck::isPermitted('template-data.index') ||(App\Helpers\SitePermissionCheck::isPermitted('printer-report.index')) || (App\Helpers\SitePermissionCheck::isPermitted('scanHistory.index')) || (App\Helpers\SitePermissionCheck::isPermitted('transaction.index')) || (App\Helpers\SitePermissionCheck::isPermitted('session-manager.index'))): ?>

			<?php if(App\Helpers\RolePermissionCheck::isPermitted('template-data.index') ||(App\Helpers\RolePermissionCheck::isPermitted('printer-report.index')) || (App\Helpers\RolePermissionCheck::isPermitted('scanHistory.index')) || (App\Helpers\RolePermissionCheck::isPermitted('transaction.index')) || (App\Helpers\RolePermissionCheck::isPermitted('session-manager.index'))): ?>
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-file-excel-o fa-fw"></i> Reports
				<span class="caret"></span></a>
				<ul class="dropdown-menu">

				    <?php if(App\Helpers\SitePermissionCheck::isPermitted('template-data.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('template-data.index')): ?>
					<li><a href="<?= URL::route('template-data.index')?>"><i class="fa fa-file-pdf-o fa-fw"></i> Template Data</a></li>
					<?php endif; ?>
					<?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('printer-report.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('printer-report.index')): ?>
					<li><a href="<?php echo e(route('printer-report.index')); ?>"><i class="fa fa-file-powerpoint-o fa-fw"></i> Printing Report</a></li>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf-report.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf-report.index')): ?>
					<li><a href="<?php echo e(route('pdf2pdf-report.index')); ?>"><i class="fa fa-file-powerpoint-o fa-fw"></i> Pdf2pdf Report</a></li>
					<?php endif; ?>
					<?php endif; ?>
                   
                  
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('scanHistory.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('scanHistory.index')): ?>
					<li><a href="<?php echo e(URL::route('scanHistory.index')); ?>"><i class="fa fa-envira fa-fw"></i> Scan History</a></li>
					<?php endif; ?>
					<?php endif; ?>
 
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('transaction.index')): ?> 
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('transaction.index')): ?> 
					<li><a href="<?php echo e(URL::route('transaction.index')); ?>"><i class="fa fa-cc-visa fa-fw"></i> Payment Transactions</a></li>
					<?php endif; ?>
					<?php endif; ?>

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('transactionVerification.index')): ?> 
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('transactionVerification.index')): ?> 
					<li><a href="<?php echo e(URL::route('transactionVerification.index')); ?>"><i class="fa fa-cc-visa fa-fw"></i> Payment Verification Transactions</a></li>
					<?php endif; ?>
					<?php endif; ?>
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('session-manager.index')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('session-manager.index')): ?> 
					<li><a href="<?php echo e(route('session-manager.index')); ?>"><i class="fa fa-lock fa-fw"></i> User Session Manager</a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                    
				</ul>
			</li>
			<?php endif; ?>
			<?php endif; ?>


			
			
		</ul>

      <?php if(App\Helpers\SitePermissionCheck::isPermitted('admin.profile.showprofile') || (App\Helpers\SitePermissionCheck::isPermitted('admin.logout'))): ?>	

		<?php if(App\Helpers\RolePermissionCheck::isPermitted('admin.profile.showprofile') || (App\Helpers\RolePermissionCheck::isPermitted('admin.logout'))): ?>	
		<ul class="nav navbar-nav navbar-right">
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative">
					<img src="/backend/seqr_scan.png" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> SeQR Admin
				<span class="caret"></span></a>
				<ul class="dropdown-menu">	
					<!--<li><a href="/webapp/dashboard"><i class="fa fa-video-camera fa-fw"></i> WebApp</a></li> -->
                    
                    <?php if(App\Helpers\SitePermissionCheck::isPermitted('admin.profile.showprofile')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('admin.profile.showprofile')): ?>	
					<li><a href="<?php echo e(URL::route('admin.profile.showprofile')); ?>"><i class="fa fa-user fa-fw"></i> My Profile</a></li>
					<?php endif; ?>
					<?php endif; ?>

					<?php if(App\Helpers\SitePermissionCheck::isPermitted('admin.manual.showmanual')): ?>
					<?php if(App\Helpers\RolePermissionCheck::isPermitted('admin.manual.showmanual')): ?>	
					<li><a href="<?php echo e(URL::route('admin.manual.showmanual')); ?>" target="_blank"><i class="fa fa-leanpub fa-fw"></i> Manual</a></li>
					<?php endif; ?>
					<?php endif; ?>
		            
		            <?php if(App\Helpers\SitePermissionCheck::isPermitted('admin.logout')): ?>
		            <?php if(App\Helpers\RolePermissionCheck::isPermitted('admin.logout')): ?>
		            <li><a href="<?=route('admin.logout')?>">&nbsp;<span class="fa fa-fw fa-sign-out">&nbsp;&nbsp;Logout</span></a></li>
					<?php endif; ?>
					<?php endif; ?>
				</ul>
			</li>
		</ul>
	   <?php else: ?>
	     <ul class="nav navbar-nav navbar-right">
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative">
					<img src="/backend/login1.png" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> 
				<span class="caret"></span></a>
				<ul class="dropdown-menu">	
	     <li><a href="<?=route('admin.logout')?>">&nbsp;<span class="fa fa-fw fa-sign-out">&nbsp;&nbsp; Logout</span></a></li>  
         </ul>

	  <?php endif; ?>

	<?php else: ?>
	     <ul class="nav navbar-nav navbar-right">
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" style="display:block;padding-left:40px;position:relative">
					<img src="/backend/login1.png" class="" style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> 
				<span class="caret"></span></a>
				<ul class="dropdown-menu">	
	     <li><a href="<?=route('admin.logout')?>">&nbsp;<span class="fa fa-fw fa-sign-out">&nbsp;&nbsp;Logout</span></a></li>  
         </ul>
   <?php endif; ?> 
    </div>
  </div>
</nav><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/layout/header.blade.php ENDPATH**/ ?>