<body>
	<nav class="navbar navbar-default">
		<div class="container" style="width: 99%;">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="{{ URL::route('admin.dashboard') }}"><i
						class="fa fa-user-secret fa-fw"></i>{{ Session::get('site_name') }} SeQR Web Admin</a>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav" style="max-width: 75%;">


					@if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.index') || (App\Helpers\SitePermissionCheck::isPermitted('background-master.index')) || (App\Helpers\SitePermissionCheck::isPermitted('template-master.index')) || (App\Helpers\SitePermissionCheck::isPermitted('processExcel.index')) || (App\Helpers\SitePermissionCheck::isPermitted('dynamic-image-management.index')) || (App\Helpers\SitePermissionCheck::isPermitted('idcards.index')))

						@if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.index') || (App\Helpers\RolePermissionCheck::isPermitted('background-master.index')) || (App\Helpers\RolePermissionCheck::isPermitted('template-master.index')) || (App\Helpers\RolePermissionCheck::isPermitted('processExcel.index')) || (App\Helpers\RolePermissionCheck::isPermitted('dynamic-image-management.index')) || (App\Helpers\RolePermissionCheck::isPermitted('idcards.index')))

							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-book fa-fw"></i>
									Document Setup<span class="caret"></span></a>
								<ul class="dropdown-menu">

									@if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.index'))
											<li><a href="{{ URL::route('fontmaster.index') }}"><i class="fa fa-foursquare fa-fw"></i>
													Font Master</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('background-master.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('background-master.index'))
											<li><a href="{{ URL::route('background-master.index') }}"><i class="fa fa-file-o"></i>
													Background Template Management</a></li>
										@endif
									@endif


									@if(App\Helpers\SitePermissionCheck::isPermitted('template-master.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('template-master.index'))
											<li><a href="<?= URL::route('template-master.index')?>"><i class="fa fa-file-o"></i>
													Template Management</a></li>
										@endif
									@endif


									@if(App\Helpers\SitePermissionCheck::isPermitted('processExcel.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('processExcel.index'))
											<li><a href="{{ URL::route('processExcel.index') }}"><i class="fa fa-file-excel-o"></i>
													Process Excel</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('dynamic-image-management.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('dynamic-image-management.index'))
											<li><a href="<?= URL::route('dynamic-image-management.index')?>"><i class="fa fa-image"></i>
													Dynamic Image Management</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('idcards.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('idcards.index'))
											<li><a href="{{ route('idcards.index') }}"><i class="fa fa-credit-card fa-fw"></i>Generate
													ID cards</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('idcard-status.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('idcard-status.index'))
											<li><a href="<?= URL::route('idcard-status.index')?>"><i
														class="fa fa-credit-card fa-fw"></i>ID cards status</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('functionalusers.cards_listing'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('functionalusers.cards_listing'))
											<li><a href="<?= URL::route('functionalusers.cards_listing')?>"><i
														class="fa fa-credit-card fa-fw"></i>ID cards Listing</a></li>
										@endif
									@endif

								</ul>
							</li>
						@endif
					@endif

					<?php $domain = \Request::getHost();
$subdomain = explode('.', $domain);


// if($subdomain[0]=="lnctbhopal"){
// 	echo App\Helpers\SitePermissionCheck::isPermitted('lnct-certificate.uploadpage');

// 	 App\Helpers\RolePermissionCheck::isPermitted('lnct-certificate.uploadpage');
// exit;
// }


if (
	$subdomain[0] == "demo" || $subdomain[0] == "ghrstu" || $subdomain[0] == "cscacs" || $subdomain[0] == "sjcit" || $subdomain[0] == "ghrusaikheda" || $subdomain[0] == "pjlcp" || $subdomain[0] == "uasb" || $subdomain[0] == "galgotias" || $subdomain[0] == "kessc" || $subdomain[0] == "iccs" || $subdomain[0] == "monad" || $subdomain[0] == "ghribmjal" || $subdomain[0] == "secura" || $subdomain[0] == "po" || $subdomain[0] == "auro" || $subdomain[0] == "woxsen" || $subdomain[0] == "test" || $subdomain[0] == "rrmu" || $subdomain[0] == "spit" || $subdomain[0] == "vbit" || $subdomain[0] == "cedp" || $subdomain[0] == "iscnagpur" || $subdomain[0] == "sgrsa" || $subdomain[0] == "anu" || $subdomain[0] == "imt" || $subdomain[0] == "sangamuni" || $subdomain[0] == "bnmit" || $subdomain[0] == "uneb" || $subdomain[0] == "estamp" || $subdomain[0] == "minoshacloud" || $subdomain[0] == "secdoc" || $subdomain[0] == "cu" || $subdomain[0] == "srwcnagpur" || $subdomain[0] == "lnctbhopal" || $subdomain[0] == "inct" || $subdomain[0] == "saiu" || $subdomain[0] == "machakos" || $subdomain[0] == "mitwpu" || $subdomain[0] == "chanakyauniversity" || $subdomain[0] == "peoplesuni" || $subdomain[0] == "ksg" || $subdomain[0] == "imcc" || $subdomain[0] == "jntuacek" || $subdomain[0] == "aiimsnagpur" || $subdomain[0] == "kswa" || $subdomain[0] == "mallareddyuniversity" || $subdomain[0] == "cambridgeit" || $subdomain[0] == "sce" || $subdomain[0] == "superflux" || $subdomain[0] == "kdkn" || $subdomain[0] == "mitaoe" || $subdomain[0] == "tpsdi" || $subdomain[0] == "eastpoint" || $subdomain[0] == "abyssinia" ||
	(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.templatelist') && App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.templatelist')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.templatelist') && App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatelist')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('bmcc-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('bmcc-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('bmcc-certificate.uploadpagePassing') && App\Helpers\RolePermissionCheck::isPermitted('bmcc-certificate.uploadpagePassing')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('fergusson-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('fergusson-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('galgotias-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('galgotias-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('kessc-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('kessc-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('iccs-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('iccs-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('monad-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('monad-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('ghribmjal-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('ghribmjal-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('uasb-certifiate.dbUploadfileugpg') && App\Helpers\RolePermissionCheck::isPermitted('uasb-certifiate.dbUploadfileugpg')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('secura-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('secura-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('kmtc-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('kmtc-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('auro-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('auro-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('woxsen-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('woxsen-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('rrmu-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('rrmu-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('cavendish-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('cavendish-certificate.uploadpage')) ||


	(App\Helpers\SitePermissionCheck::isPermitted('srwcnagpur-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('srwcnagpur-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('spit-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('spit-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('vbit-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('vbit-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('cedp-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('cedp-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('iscn-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('iscn-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('rawatpura-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('rawatpura-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('mmk-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('mmk-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('bestiu-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('bestiu-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('mvsr-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('mvsr-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('eastpoint-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('eastpoint-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('sdm-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('sdm-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-certificate.addform') && App\Helpers\RolePermissionCheck::isPermitted('sgrsa-certificate.addform')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-supplier.editform') && App\Helpers\RolePermissionCheck::isPermitted('sgrsa-supplier.editform')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('anu-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('anu-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('lnct-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('lnct-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('surana-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('surana-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('imt-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('imt-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('sangamui-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('sangamui-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('bnm-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('bnm-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('uneb-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('uneb-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('saiu-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('saiu-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('machakos.index') && App\Helpers\RolePermissionCheck::isPermitted('machakos.index')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('wordtopdf.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('wordtopdf.uploadpage')) ||


	(App\Helpers\SitePermissionCheck::isPermitted('mitwpu-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('mitwpu-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('peoplesuni-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('peoplesuni-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('chanakya-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('chanakya-certificate.uploadpage')) ||

	(App\Helpers\SitePermissionCheck::isPermitted('aiims-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('aiims-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('yuvaparivartan-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('yuvaparivartan-certificate.uploadpage')) ||

	(App\Helpers\SitePermissionCheck::isPermitted('mallareddy-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('mallareddy-certificate.uploadpage')) ||

	(App\Helpers\SitePermissionCheck::isPermitted('cambridge-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('cambridge-certificate.uploadpage')) ||

	(App\Helpers\SitePermissionCheck::isPermitted('kdkn-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('kdkn-certificate.uploadpage')) ||

	(App\Helpers\SitePermissionCheck::isPermitted('mitaoe-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('mitaoe-certificate.uploadpage')) ||


	(App\Helpers\SitePermissionCheck::isPermitted('tpsdi-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('tpsdi-certificate.uploadpage')) ||

	(App\Helpers\SitePermissionCheck::isPermitted('cscacs-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('cscacs-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('ghrstu-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('ghrstu-certificate.uploadpage')) ||


	(App\Helpers\SitePermissionCheck::isPermitted('ksg-batch.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('ksg-batch.uploadpage')) ||

	(App\Helpers\SitePermissionCheck::isPermitted('ksg-approval.page') && App\Helpers\RolePermissionCheck::isPermitted('ksg-approval.page')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('bihar-estamp-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('bihar-estamp-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('pjlcp-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('pjlcp-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('sjcit-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('sjcit-certificate.uploadpage')) ||
	(App\Helpers\SitePermissionCheck::isPermitted('ghrusaikheda-certificate.uploadpage') && App\Helpers\RolePermissionCheck::isPermitted('ghrusaikheda-certificate.uploadpage'))

) { ?>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
								class="fa fa-file-o fa-fw"></i>Customs<span class="caret"></span></a>
						<ul class="dropdown-menu">


							<!-- @if(App\Helpers\SitePermissionCheck::isPermitted('convocation.dashboard'))
                    @if(App\Helpers\RolePermissionCheck::isPermitted('convocation.dashboard'))

						<li><a href="<?= URL::route('convocation.dashboard')?>"><i class="fa fa-file-o fa-fw"></i> Convocation</a></li>
					@endif
					@endif -->

							@if($subdomain[0] == "mitwpu" || @$subdomain[1] == "mitwpu")
								<li><a href="<?= URL::route('convocation.dashboard')?>"><i class="fa fa-file-o fa-fw"></i>
										Convocation</a></li>
							@endif

							@if($subdomain[0] == "jntuacek" || @$subdomain[1] == "jntuacek")
								<li><a href="<?= URL::route('jntu-certificate.uploadpage')?>"><i
											class="fa fa-file-o fa-fw"></i> JNTU Templates</a></li>
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.templatelist'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.templatelist'))
									<li><a href="{{ URL::route('pdf2pdf.templatelist') }}"><i
												class="fa fa-list fa-fw"></i>Pdf2Pdf Templates</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.templatelist'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatelist'))
									<li><a href="{{ URL::route('excel2pdf.templatelist') }}"><i
												class="fa fa-list fa-fw"></i>Excel2Pdf Templates</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('wordtopdf.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('wordtopdf.uploadpage'))
									<li><a href="{{ URL::route('wordtopdf.uploadpage') }}"><i
												class="fa fa-file-pdf-o fa-fw"></i>Word To PDF Converter</a></li>
								@endif
							@endif



							@if($subdomain[0] == "demo" || $subdomain[0] == "test")
								<li><a href="{{ URL::route('utility.templates') }}"><i class="fa fa-file-excel-o fa-fw"></i>
										Utility - Ghost | Yellow Patch</a></li>
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('exceltopdf.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('exceltopdf.uploadpage'))
									<li><a href="{{ URL::route('exceltopdf.uploadpage') }}"><i
												class="fa fa-file-pdf-o fa-fw"></i>PDF Generate</a></li>

									<li><a href="{{ URL::route('exceltopdfnew.uploadpage') }}"><i
												class="fa fa-file-pdf-o fa-fw"></i>PDF Generate New Utility</a></li>
								@endif
							@endif

							@if($subdomain[0] == "demo")
								<li><a href="{{ URL::route('answerbooklet.index') }}"><i class="fa fa-money fa-fw"></i>
										Answer Booklet Generation</a></li>
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('bmcc-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('bmcc-certificate.uploadpage'))
									<li><a href="{{ URL::route('bmcc-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> BMCC SOM</a></li>
								@endif
							@endif




							@if(App\Helpers\SitePermissionCheck::isPermitted('woxsen-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('woxsen-certificate.uploadpage'))
									<li><a href="{{ URL::route('woxsen-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> WOXSEN</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('bmcc-certificate.uploadpagePassing'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('bmcc-certificate.uploadpagePassing'))
									<li><a href="{{ URL::route('bmcc-certificate.uploadpagePassing') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> BMCC Passing Certificate</a></li>
								@endif
							@endif



							@if(App\Helpers\SitePermissionCheck::isPermitted('fergusson-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('fergusson-certificate.uploadpage'))

									<li><a href="{{ URL::route('fergusson-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Fergusson SOM</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('galgotias-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('galgotias-certificate.uploadpage'))
									<li><a href="{{ URL::route('galgotias-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Galgotias Degree Certificate</a></li>
								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('uasb-certifiate.dbUploadfileugpg'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('uasb-certifiate.dbUploadfileugpg'))
											<!--  <li><a href="{{ URL::route('uasb-certifiate.dbUploadfileugpg') }}"><i class="fa fa-file-excel-o fa-fw"></i> UASB UGPG Certificate</a></li>
									 <li><a href="{{ URL::route('uasb-certifiate.dbUploadfilegold') }}"><i class="fa fa-file-excel-o fa-fw"></i> UASB GOLD Certificate</a></li> -->
											<li><a href="{{ URL::route('uasb-certificate.index') }}"><i
														class="fa fa-list fa-fw"></i>UASB Templates</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('auro-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('auro-certificate.uploadpage'))
									<li><a href="{{ URL::route('auro-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> AURO Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('rrmu-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('rrmu-certificate.uploadpage'))
									<li><a href="{{ URL::route('rrmu-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> RRMU Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('cavendish-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('cavendish-certificate.uploadpage'))
									<li><a href="{{ URL::route('cavendish-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> CAVENDISH Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('srwcnagpur-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('srwcnagpur-certificate.uploadpage'))
									<li><a href="{{ URL::route('srwcnagpur-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> SRWCNAGPUR Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('kessc-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('kessc-certificate.uploadpage'))
									<li><a href="{{ URL::route('kessc-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> KESSC Certificate</a></li>
									<li><a href="{{ URL::route('kessc-addserial.uploadpage') }}"><i
												class="fa fa-file-pdf-o fa-fw"></i> KESSC Add Serial No.</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('iccs-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('iccs-certificate.uploadpage'))
									<li><a href="{{ URL::route('iccs-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> ICCS Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('monad-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('monad-certificate.uploadpage'))
									<li><a href="{{ URL::route('monad-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> MONAD Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('ghribmjal-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ghribmjal-certificate.uploadpage'))
									<li><a href="{{ URL::route('ghribmjal-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Ghribmjal Certificate</a></li>
								@endif
							@endif
							<!--Start Molwa-->
							@if(App\Helpers\SitePermissionCheck::isPermitted('secura-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('secura-certificate.uploadpage'))
									<li><a href="{{ URL::route('secura-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Molwa Documents</a></li>
								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('molwa-certificate.ImportPrint'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('molwa-certificate.ImportPrint'))
									<li><a href="{{ URL::route('molwa-certificate.ImportPrint') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Import</a></li>
								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('molwa-certificate.index'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('molwa-certificate.index'))
									<li><a href="{{ URL::route('molwa-certificate.index') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Manage Records</a></li>
									<li><a href="{{ URL::route('molwa-certificate.molwaRemarks') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Molwa Remark Records</a></li>
								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('molwa-certificate.ReImportPrint'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('molwa-certificate.ReImportPrint'))
									<li><a href="{{ URL::route('molwa-certificate.ReImportPrint') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Re-Import</a></li>
									<li><a href="{{ URL::route('molwa-certificate.molwaReport') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Report</a></li>
								@endif
							@endif
							<!--End Molwa-->
							@if(App\Helpers\SitePermissionCheck::isPermitted('kmtc-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('kmtc-certificate.uploadpage'))
									<li><a href="{{ URL::route('kmtc-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Kmtc Documents</a></li>
								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('kmtc-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('kmtc-certificate.uploadpage'))
									<li><a href="{{ URL::route('convocationkmtc.dashboard') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Kmtc Convocation</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('spit-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('spit-certificate.uploadpage'))
									<li><a href="{{ URL::route('spit-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Grade Cards</a></li>
								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('vbit-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('vbit-certificate.uploadpage'))
									<li><a href="{{ URL::route('vbit-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Grade Cards</a></li>
								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('cedp-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('cedp-certificate.uploadpage'))
									<li><a href="{{ URL::route('cedp-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Grade Cards</a></li>
								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('iscn-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('iscn-certificate.uploadpage'))
									<li><a href="{{ URL::route('iscn-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> ISCN Grade Cards</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('rawatpura-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('rawatpura-certificate.uploadpage'))
									<li><a href="{{ URL::route('rawatpura-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> SRSU Raipur Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('bestiu-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('bestiu-certificate.uploadpage'))
									<li><a href="{{ URL::route('bestiu-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Bestiu Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('mvsr-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('mvsr-certificate.uploadpage'))
									<li><a href="{{ URL::route('mvsr-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> MVSR Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('eastpoint-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('eastpoint-certificate.uploadpage'))
									<li><a href="{{ URL::route('eastpoint-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> East Point Certificate</a></li>
								@endif
							@endif


							{{-- @if(App\Helpers\SitePermissionCheck::isPermitted('bestiu-certificate.uploadpage')) --}}
							{{-- @if(App\Helpers\RolePermissionCheck::isPermitted('bestiu-certificate.uploadpage')) --}}
							<?php $domain = \Request::getHost();
	$subdomain = explode('.', $domain);
	if ($subdomain[0] == "bestiu") { ?>
							<li><a href="{{ URL::route('bestiu-e-document.uploadpage') }}"><i
										class="fa fa-file-excel-o fa-fw"></i> BestIU E-Documents</a></li>
							<?php } ?>
							{{-- @endif --}}
							{{-- @endif --}}


							@if(App\Helpers\SitePermissionCheck::isPermitted('sdm-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sdm-certificate.uploadpage'))
									<li><a href="{{ URL::route('sdm-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> SDM Certificate</a></li>
									<li><a href="{{ URL::route('sdm-resit.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> SDM RESIT</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('pjlcp-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('pjlcp-certificate.uploadpage'))
									<li><a href="{{ URL::route('pjlcp-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> PJLCP Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('sjcit-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sjcit-certificate.uploadpage'))
									<li><a href="{{ route('sjcit-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> SJCIT Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('ghrusaikheda-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ghrusaikheda-certificate.uploadpage'))
									<li><a href="{{ route('ghrusaikheda-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Ghrusaikheda Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('surana-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('surana-certificate.uploadpage'))
									<li><a href="{{ URL::route('surana-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Surana Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('anu-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('anu-certificate.uploadpage'))
									<li><a href="{{ URL::route('anu-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> ANU Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('lnct-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('lnct-certificate.uploadpage'))
									<li><a href="{{ URL::route('lnct-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> LNCT Certificate</a></li>
								@endif
							@endif

							<!--Start sgrsa-->
							@if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-certificate.addform'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-certificate.addform'))
									<li><a href="{{ URL::route('sgrsa-certificate.addform') }}"><i
												class="fa fa-file-pdf-o fa-fw"></i> New Record</a></li>
									<li><a href="{{ URL::route('sgrsa-previouscertificate.addform') }}"><i
												class="fa fa-file-pdf-o fa-fw"></i> Old Record</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-supplier.addform'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-supplier.addform'))
									<li><a href="{{ URL::route('sgrsa-supplier.addform') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Suppliers</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-agent.addform'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-agent.addform'))
									<li><a href="{{ URL::route('sgrsa-agent.addform') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Sub Agents</a></li>
									<li><a href="{{ URL::route('sgrsa-governor.addform') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Type of Governor</a></li>
									<li><a href="{{ URL::route('sgrsa-governor.adduform') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Unit Serial Numbers</a></li>
									<li><a href="{{ URL::route('sgrsa-certificate.addform') }}"><i
												class="fa fa-file-pdf-o fa-fw"></i> Recall</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('agent-allot.RecallList'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('agent-allot.RecallList'))
									<li><a href="{{ URL::route('agent-allot.RecallList') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Agent Allotment</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('supplier-allot.addform'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('supplier-allot.addform'))
									<li><a href="{{ URL::route('supplier-allot.addform') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Allot HC to Agent</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('allot-edit.editform'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('allot-edit.editform'))
									<li><a href="{{ URL::route('allot-edit.editform') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Damaged/Replaced HC</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('supplier-uploadexcel.uploadExcelForm'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('supplier-uploadexcel.uploadExcelForm'))
									<li><a href="{{ URL::route('supplier-uploadexcel.uploadExcelForm') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Upload Previous Data</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-supplier.editform'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-supplier.editform'))
									<li><a href="{{ URL::route('sgrsa-supplier.editform') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Supplier Profile</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-allot.addform'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-allot.addform'))
									<li><a href="{{ URL::route('sgrsa-allot.addform') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Allot HC to Supplier</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('sgrsa-previouscertificate.indexPC'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sgrsa-previouscertificate.indexPC'))
									<li><a href="{{ URL::route('sgrsa-previouscertificate.indexPC') }}"><i
												class="fa fa-file-text-o fa-fw"></i> Notification</a></li>
								@endif
							@endif

							<!--End sgrsa-->

							@if(App\Helpers\SitePermissionCheck::isPermitted('sangamui-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('sangamui-certificate.uploadpage'))
									<li><a href="{{ URL::route('sangamui-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Sangam Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('imt-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('imt-certificate.uploadpage'))
									<li><a href="{{ route('imt.index') }}"><i class="fa fa-file-excel-o fa-fw"></i> IMT
											Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('ttd-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ttd-certificate.uploadpage'))
									<li><a href="{{ route('ttd.index') }}"><i class="fa fa-file-excel-o fa-fw"></i> TTD
											Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('bnm-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('bnm-certificate.uploadpage'))
									<li><a href="{{ route('bnm-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> BNMI Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('uneb-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('uneb-certificate.uploadpage'))
									<li><a href="{{ route('uneb-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> UNEB Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('saiu-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('saiu-certificate.uploadpage'))
									<li><a href="{{ route('saiu-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> SAIU Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('mitwpu-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('mitwpu-certificate.uploadpage'))
									<li><a href="{{ route('mitwpu-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> MITWPU Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('peoplesuni-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('peoplesuni-certificate.uploadpage'))
									<li><a href="{{ route('peoplesuni-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Peoplesuni Certificate</a></li>
								@endif
							@endif





							@if(App\Helpers\SitePermissionCheck::isPermitted('chanakya-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('chanakya-certificate.uploadpage'))
									<li><a href="{{ route('chanakya-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Chanakya Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('aiims-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('aiims-certificate.uploadpage'))
									<li><a href="{{ route('aiims-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Aiims Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('yuvaparivartan-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('yuvaparivartan-certificate.uploadpage'))
									<li><a href="{{ route('yuvaparivartan-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i>
											Yuvaparivartan Certificate</a></li>
									<li><a href="{{ URL::route('yuvaparivartan-records.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Verify E-Certificate Details</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('mallareddy-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('mallareddy-certificate.uploadpage'))
									<li><a href="{{ route('mallareddy-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Malla Reddy Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('cambridge-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('cambridge-certificate.uploadpage'))
									<li><a href="{{ route('cambridge-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Cambridge Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('kdkn-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('kdkn-certificate.uploadpage'))
									<li><a href="{{ route('kdkn-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> KDKN Certificate</a></li>
								@endif
							@endif



							@if(App\Helpers\SitePermissionCheck::isPermitted('mitaoe-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('mitaoe-certificate.uploadpage'))
									<li><a href="{{ route('mitaoe-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Mitaoe Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('tpsdi-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('tpsdi-certificate.uploadpage'))
									<li><a href="{{ route('tpsdi-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> TPSDI Certificate</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('cscacs-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('cscacs-certificate.uploadpage'))
									<li><a href="{{ route('cscacs-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> CSCACS Certificate</a></li>
								@endif
							@endif


							@if(App\Helpers\SitePermissionCheck::isPermitted('ghrstu-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ghrstu-certificate.uploadpage'))
									<li><a href="{{ route('ghrstu-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> GHRSTU Certificate</a></li>
								@endif
							@endif






							<!--ksg -->
							@if(App\Helpers\SitePermissionCheck::isPermitted('ksg-branch.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ksg-branch.uploadpage'))
									<li><a href="{{ URL::route('ksg-branch.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Branch</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('ksg-branch.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ksg-branch.uploadpage'))
									<li><a href="{{ URL::route('ksg-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> KSG Custom</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('ksg-batch.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ksg-batch.uploadpage'))
									<li><a href="{{ URL::route('ksg-batch.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Batch</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('ksg-approval.page'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ksg-approval.page'))
									<li><a href="{{ URL::route('ksg-approval.page') }}"><i class="fa fa-file-excel-o fa-fw"></i>
											Approval page</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('ksg-print.page'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('ksg-print.page'))
									<li><a href="{{ URL::route('ksg-print.page') }}"><i class="fa fa-file-excel-o fa-fw"></i>
											Printer page</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('machakos.index'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('machakos.index'))
									<li><a href="/admin/machakos-certificate/machakos"><i class="fa fa-file-excel-o fa-fw"></i>
											Generation</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('bihar-estamp-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('bihar-estamp-certificate.uploadpage'))
									<li><a href="{{ URL::route('bihar-estamp-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Certificate</a></li>
									<li><a href="{{ URL::route('bihar-estamp-certificate.uploadpageutility') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Certificate - Utility</a></li>
									<li><a href="{{ URL::route('bihar-estamp-certificate.uploadpageprinterutility') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Certificate - Printer Utility</a></li>
									<li><a href="{{ URL::route('bihar-estamp-certificate.uploadpageapiutility') }}"><i
												class="fa fa-file-excel-o fa-fw"></i>Certificate - API Printer Utility</a></li>
									<li><a href="{{ URL::route('bihar-estamp-certificate.uploadpageprinter') }}"><i
												class="fa fa-file-excel-o fa-fw"></i>Old Certificate - Printer</a></li>

								@endif
							@endif
							@if(App\Helpers\SitePermissionCheck::isPermitted('minosha-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('minosha-certificate.uploadpage'))
									<li><a href="{{ route('minosha-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> Minosha Custom Templates</a></li>
								@endif
							@endif

							@if(App\Helpers\SitePermissionCheck::isPermitted('suryodaya-certificate.uploadpage'))
								@if(App\Helpers\RolePermissionCheck::isPermitted('suryodaya-certificate.uploadpage'))
									<li><a href="{{ URL::route('suryodaya-certificate.uploadpage') }}"><i
												class="fa fa-file-excel-o fa-fw"></i> SURYODAYA COLLEGE </a></li>

								@endif
							@endif

						</ul>
					</li>
					<?php } ?>
					<?php $domain = \Request::getHost();
$subdomain = explode('.', $domain);
if ($subdomain[0] == "mitadt" || $subdomain[0] == "localhost") { ?>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
								class="fa fa-file-o fa-fw"></i>Customs<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{ URL::route('passing-certificate.uploadpage') }}"><i
										class="fa fa-file-excel-o fa-fw"></i>Passing Certificate</a></li>
						</ul>
					</li>
					<?php } ?>
					@if(App\Helpers\SitePermissionCheck::isPermitted('degree-certifiate.index'))

						@if(App\Helpers\RolePermissionCheck::isPermitted('degree-certifiate.index'))
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
										class="fa fa-newspaper-o fa-fw"></i> Galgotias Degree Certificate<span
										class="caret"></span></a>
								<ul class="dropdown-menu">

									<li><a href="{{ route('degree-certifiate.index') }}"><i class="fa fa-file-text-o fa-fw"></i>
											Generate Degree Certificate</a></li>

								</ul>
							</li>
						@endif
					@endif

					@if(App\Helpers\SitePermissionCheck::isPermitted('pgconfig.index') || (App\Helpers\SitePermissionCheck::isPermitted('pgmaster.index')) || (App\Helpers\SitePermissionCheck::isPermitted('documentsratemaster.index')))
						@if(App\Helpers\RolePermissionCheck::isPermitted('pgconfig.index') || (App\Helpers\RolePermissionCheck::isPermitted('pgmaster.index')) || (App\Helpers\RolePermissionCheck::isPermitted('documentsratemaster.index')))
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
										class="fa fa-credit-card fa-fw"></i> Payment Setup<span class="caret"></span></a>
								<ul class="dropdown-menu">
									@if($subdomain[0] == "raisoni")

										@if(App\Helpers\SitePermissionCheck::isPermitted('pgmaster.index'))
											@if(App\Helpers\RolePermissionCheck::isPermitted('pgmaster.index'))
												<li>
													<a href="{{ URL::route('pgmaster.index') }}"><i class="fa fa-money fa-fw"></i>Payment
														Gateway</a>
												</li>
											@endif
										@endif

										@if(App\Helpers\SitePermissionCheck::isPermitted('pgconfig.index'))
											@if(App\Helpers\RolePermissionCheck::isPermitted('pgconfig.index'))
												<li>
													<a href="{{ URL::route('pgconfig.index') }}"><i class="fa fa-gears fa-fw"></i> PG
														Configuration</a>

												</li>
											@endif
										@endif

									@else
										@if(App\Helpers\SitePermissionCheck::isPermitted('pgmaster_new.index'))
											@if(App\Helpers\RolePermissionCheck::isPermitted('pgmaster_new.index'))
												<li>
													<a href="{{ URL::route('pgmaster_new.index') }}"><i
															class="fa fa-money fa-fw"></i>Payment Gateway</a>
												</li>
											@endif
										@endif

										@if(App\Helpers\SitePermissionCheck::isPermitted('pg_newconfig.index'))
											@if(App\Helpers\RolePermissionCheck::isPermitted('pg_newconfig.index'))
												<li>
													<a href="{{ URL::route('pg_newconfig.index') }}"><i class="fa fa-gears fa-fw"></i> PG
														Configuration</a>

												</li>
											@endif
										@endif



									@endif


									@if(App\Helpers\SitePermissionCheck::isPermitted('documentsratemaster.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('documentsratemaster.index'))
											<li>
												<a href="{{ URL::route('documentsratemaster.index') }}"><i
														class="fa fa-gears fa-fw"></i> Document Rate Master</a>

											</li>
										@endif
									@endif

								</ul>
							</li>
						@endif
					@endif

					@if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.index') || (App\Helpers\SitePermissionCheck::isPermitted('printing-detail.index')) || (App\Helpers\SitePermissionCheck::isPermitted('vgujaipur.create')))

						@if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.index') || (App\Helpers\RolePermissionCheck::isPermitted('printing-detail.index')) || (App\Helpers\RolePermissionCheck::isPermitted('vgujaipur.create')))
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
										class="fa fa-newspaper-o fa-fw"></i> Document Management<span class="caret"></span></a>
								<ul class="dropdown-menu">

									@if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.index'))
											<li><a href="{{ route('certificateManagement.index') }}"><i
														class="fa fa-file-text-o fa-fw"></i> Certificate Management</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('vgujaipur.create'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('vgujaipur.create'))
											<li><a href="{{ route('vgujaipur.create') }}"><i class="fa fa-file-text-o fa-fw"></i> Print
													Serial Number</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('printing-detail.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('printing-detail.index'))
														<li><a href="{{ URL::route('printing-detail.index') }}"><i class="fa fa-print fa-fw"></i>
																<?php if ($subdomain[0] == "sales") {
												echo "Generation details";
											} else {
												echo "Printing details";
											} ?></a>
														</li>
										@endif
									@endif

								</ul>
							</li>
						@endif
					@endif


					<?php
$domain = \Request::getHost();
$subdomain = explode('.', $domain);
if ($subdomain[0] == \Config('constant.raisoni_subdomain') || $subdomain[0] == 'demo' || $subdomain[0] == 'galgotias' || $subdomain[0] == 'monad') {
			?>
					@if(App\Helpers\SitePermissionCheck::isPermitted('semester.index') || App\Helpers\SitePermissionCheck::isPermitted('branch.index') || App\Helpers\SitePermissionCheck::isPermitted('degreemaster.index') || App\Helpers\SitePermissionCheck::isPermitted('sessionsmaster.index'))
						@if(App\Helpers\RolePermissionCheck::isPermitted('semester.index') || App\Helpers\RolePermissionCheck::isPermitted('branch.index') || App\Helpers\RolePermissionCheck::isPermitted('degreemaster.index') || App\Helpers\RolePermissionCheck::isPermitted('sessionsmaster.index'))
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-list-alt fa-fw"></i>
									Masters<span class="caret"></span></a>
								<ul class="dropdown-menu">
									@if(App\Helpers\SitePermissionCheck::isPermitted('sessionsmaster.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('sessionsmaster.index'))
											<li><a href="{{ URL::route('sessionsmaster.index') }}"><i class="fa fa-list fa-fw"></i>
													Session</a></li>
										@endif
									@endif
									@if(App\Helpers\SitePermissionCheck::isPermitted('degreemaster.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('degreemaster.index'))
											<li><a href="{{ URL::route('degreemaster.index') }}"><i class="fa fa-list fa-fw"></i>
													Degree</a></li>
										@endif
									@endif
									@if(App\Helpers\SitePermissionCheck::isPermitted('branch.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('branch.index'))
											<li><a href="{{ URL::route('branch.index') }}"><i class="fa fa-list fa-fw"></i> Branch</a>
											</li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('semester.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('semester.index'))
											<li><a href="{{ URL::route('semester.index') }}"><i class="fa fa-list fa-fw"></i>
													Semester</a></li>
										@endif
									@endif
								</ul>
							</li>
						@endif
					@endif

					@if(App\Helpers\SitePermissionCheck::isPermitted('request_testing') || App\Helpers\SitePermissionCheck::isPermitted('oldverification.index'))
						@if(App\Helpers\RolePermissionCheck::isPermitted('request_testing') || App\Helpers\RolePermissionCheck::isPermitted('oldverification.index'))
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
										class="fa fa-certificate fa-fw"></i> Document Verification<span
										class="caret"></span></a>
								<ul class="dropdown-menu">

									@if(App\Helpers\SitePermissionCheck::isPermitted('oldverification.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('oldverification.index'))
											<li><a href="{{ URL::route('oldverification.index') }}"><i class="fa fa-list-ul"></i> Old
													Documents</a></li>
										@endif
									@endif
									@if(App\Helpers\SitePermissionCheck::isPermitted('request_testing'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('request_testing'))
											<li><a href="{{ URL::route('request_testing') }}"><i class="fa fa-list-ul"></i> SeQR
													Documents</a></li>
										@endif
									@endif

								</ul>
							</li>
						@endif
					@endif

					@if(App\Helpers\SitePermissionCheck::isPermitted('stationarystock.index') || App\Helpers\SitePermissionCheck::isPermitted('damagedstock.index') || App\Helpers\SitePermissionCheck::isPermitted('consumptionreport.index') || App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.index'))
						@if(App\Helpers\RolePermissionCheck::isPermitted('stationarystock.index') || App\Helpers\RolePermissionCheck::isPermitted('damagedstock.index') || App\Helpers\RolePermissionCheck::isPermitted('consumptionreport.index') || App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.index'))
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-hdd-o fa-fw"></i>
									Stock
									<span class="caret"></span></a>
								<ul class="dropdown-menu">
									@if(App\Helpers\SitePermissionCheck::isPermitted('stationarystock.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('stationarystock.index'))
											<li><a href="{{ URL::route('stationarystock.index') }}"><i class="fa fa-th-large fa-fw"></i>
													Stationary Stock</a></li>
										@endif
									@endif
									@if(App\Helpers\SitePermissionCheck::isPermitted('damagedstock.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('damagedstock.index'))
											<li><a href="{{ URL::route('damagedstock.index') }}"><i
														class="fa fa-chain-broken fa-fw"></i> Damaged Stock</a></li>
										@endif
									@endif
									@if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreport.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreport.index'))
											<li><a href="{{ URL::route('consumptionreport.index') }}"><i
														class="fa fa-align-justify fa-fw"></i> Consumption Report</a></li>
										@endif
									@endif
									@if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.index'))
											<li><a href="{{ URL::route('consumptionreportexport.index') }}"><i
														class="fa fa-file-excel-o fa-fw"></i>Download Consumption Report</a></li>
										@endif
									@endif
								</ul>
							</li>
						@endif
					@endif
					<?php
}
			?>

					@if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.index') || (App\Helpers\SitePermissionCheck::isPermitted('usermaster.index')) || (App\Helpers\SitePermissionCheck::isPermitted('student.index')) || (App\Helpers\SitePermissionCheck::isPermitted('adminmaster.index')) || (App\Helpers\SitePermissionCheck::isPermitted('roles.index')) || (App\Helpers\SitePermissionCheck::isPermitted('systemconfig.index')))

						@if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.index') || (App\Helpers\RolePermissionCheck::isPermitted('usermaster.index')) || (App\Helpers\RolePermissionCheck::isPermitted('student.index')) || (App\Helpers\RolePermissionCheck::isPermitted('adminmaster.index')) || (App\Helpers\RolePermissionCheck::isPermitted('roles.index')) || (App\Helpers\RolePermissionCheck::isPermitted('systemconfig.index')))

							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-user fa-fw"></i>
									System Config<span class="caret"></span></a>
								<ul class="dropdown-menu">

									@if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.index'))
											<li><a href="<?= URL::route('institutemaster.index')?>"><i class="fa fa-building fa-fw"></i>
													Institute Management</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('labmaster.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('labmaster.index'))
											<li><a href="<?= URL::route('labmaster.index')?>"><i class="fa fa-file fa-fw"></i> Lab
													Master</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.index'))
											<li><a href="{{ URL::route('usermaster.index') }}"><i class="fa fa-users fa-fw"></i> User
													Management</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('functionalusers.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('functionalusers.index'))
											<li><a href="{{ URL::route('functionalusers.index') }}"><i class="fa fa-users fa-fw"></i>
													Functional Users</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('student.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('student.index'))
											<li><a href="{{ URL::route('student.index') }}"><i class="fa fa-users fa-fw"></i> Student
													Management </a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.index'))
											<li><a href="{{ URL::route('adminmaster.index') }}"><i class="fa fa-users fa-fw"></i> Admin
													Management</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('roles.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('roles.index'))
											<li><a href="{{ URL::route('roles.index') }}"><i class="fa fa-users fa-fw"></i> Roles
													Management</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('systemconfig.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('systemconfig.index'))
											<li><a href="{{ URL::route('systemconfig.index') }}"><i class="fa fa-cog fa-fw"></i>
													Settings</a></li>
										@endif
									@endif

								</ul>
							</li>
						@endif
					@endif

					@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.index') || (App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingDetails.index')) || (App\Helpers\SitePermissionCheck::isPermitted('sandboxing.scanHistory.index')))

						@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.index') || (App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingDetails.index')) || (App\Helpers\RolePermissionCheck::isPermitted('sandboxing.scanHistory.index')))
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
										class="fa fa-file-excel-o fa-fw"></i> Sandboxing
									<span class="caret"></span></a>
								<ul class="dropdown-menu">

									@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.index'))
											<li><a href="<?= URL::route('sandboxing.certificate.index')?>"><i
														class="fa fa-file-pdf-o fa-fw"></i> Certificate management</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingDetails.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingDetails.index'))
														<li><a href="{{ route('sandboxing.printingDetails.index') }}"><i
																	class="fa fa-file-powerpoint-o fa-fw"></i><?php if ($subdomain[0] == "sales") {
												echo "Generation details";
											} else {
												echo "Printing details";
											} ?>
															</a></li>
										@endif
									@endif


									@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.templateData.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.templateData.index'))
											<li><a href="{{ URL::route('sandboxing.templateData.index') }}"><i
														class="fa fa-envira fa-fw"></i> Template data</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.printingReport.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.printingReport.index'))
											<li><a href="{{ URL::route('sandboxing.printingReport.index') }}"><i
														class="fa fa-file-powerpoint-o fa-fw"></i> Printing report</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.scanHistory.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.scanHistory.index'))
											<li><a href="{{route('sandboxing.scanHistory.index')}}"><i class="fa fa-lock fa-fw"></i>
													Scan history</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.paymentTransaction.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.paymentTransaction.index'))
											<li><a href="{{route('sandboxing.paymentTransaction.index')}}"><i
														class="fa fa-lock fa-fw"></i> Payment transactions</a></li>
										@endif
									@endif

								</ul>
							</li>
						@endif
					@endif

					@if(App\Helpers\SitePermissionCheck::isPermitted('template-data.index') || (App\Helpers\SitePermissionCheck::isPermitted('printer-report.index')) || (App\Helpers\SitePermissionCheck::isPermitted('scanHistory.index')) || (App\Helpers\SitePermissionCheck::isPermitted('transaction.index')) || (App\Helpers\SitePermissionCheck::isPermitted('session-manager.index')))

						@if(App\Helpers\RolePermissionCheck::isPermitted('template-data.index') || (App\Helpers\RolePermissionCheck::isPermitted('printer-report.index')) || (App\Helpers\RolePermissionCheck::isPermitted('scanHistory.index')) || (App\Helpers\RolePermissionCheck::isPermitted('transaction.index')) || (App\Helpers\RolePermissionCheck::isPermitted('session-manager.index')))
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
										class="fa fa-file-excel-o fa-fw"></i> Reports
									<span class="caret"></span></a>
								<ul class="dropdown-menu">

									@if(App\Helpers\SitePermissionCheck::isPermitted('template-data.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('template-data.index'))
											<li><a href="<?= URL::route('template-data.index')?>"><i class="fa fa-file-pdf-o fa-fw"></i>
													Template Data</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('printer-report.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('printer-report.index'))
											<li><a href="{{ route('printer-report.index') }}"><i
														class="fa fa-file-powerpoint-o fa-fw"></i> Printing Report</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf-report.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf-report.index'))
											<li><a href="{{ route('pdf2pdf-report.index') }}"><i
														class="fa fa-file-powerpoint-o fa-fw"></i> Pdf2pdf Report</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('functionalusers.idcard_report'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('functionalusers.idcard_report'))
											<li><a href="{{ route('functionalusers.idcard_report') }}"><i
														class="fa fa-file-powerpoint-o fa-fw"></i> ID Cards Report</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('scanHistory.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('scanHistory.index'))
											<li><a href="{{ URL::route('scanHistory.index') }}"><i class="fa fa-envira fa-fw"></i> Scan
													History</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('transaction.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('transaction.index'))
											<li><a href="{{ URL::route('transaction.index') }}"><i class="fa fa-cc-visa fa-fw"></i>
													Payment Transactions</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('transactionVerification.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('transactionVerification.index'))
											<li><a href="{{ URL::route('transactionVerification.index') }}"><i
														class="fa fa-cc-visa fa-fw"></i> Payment Verification Transactions</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('session-manager.index'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('session-manager.index'))
											<li><a href="{{route('session-manager.index')}}"><i class="fa fa-lock fa-fw"></i> User
													Session Manager</a></li>
										@endif
									@endif

								</ul>
							</li>
						@endif
					@endif



					<?php if ($subdomain[0] == 'tpsdi') {?>
					<li><a href="{{route('functionalusers.datamapping')}}"><i class="fa fa-file-pdf-o fa-fw"></i> Map
							Old Data</a></li>
					<?php } ?>
					<?php if ($subdomain[0] == 'mitwpu' || $subdomain[0] == 'konkankrishi' || $subdomain[0] == 'anu') {?>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-link fa-fw"></i>
							Blockchain Authorization<span class="caret"></span></a>
						<ul class="dropdown-menu">


							<li><a href="{{ route('BlockchainAuthorization.live') }}"><i class="fa fa-link fa-fw"></i>
									Live Blockchain Authorization</a></li>

							<li><a href="{{ route('BlockchainAuthorization.test') }}"><i class="fa fa-link fa-fw"></i>
									Test Blockchain Authorization</a></li>


						</ul>
					</li>

					<?php } ?>


				</ul>

				@if(App\Helpers\SitePermissionCheck::isPermitted('admin.profile.showprofile') || (App\Helpers\SitePermissionCheck::isPermitted('admin.logout')))

					@if(App\Helpers\RolePermissionCheck::isPermitted('admin.profile.showprofile') || (App\Helpers\RolePermissionCheck::isPermitted('admin.logout')))
						<ul class="nav navbar-nav navbar-right">
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"
									style="display:block;padding-left:40px;position:relative">
									<img src="/backend/seqr_scan.png" class=""
										style="height:30px;width:30px;position:absolute;top:5px;left:5px;"> SeQR Admin
									<span class="caret"></span></a>
								<ul class="dropdown-menu">
									<!--<li><a href="/webapp/dashboard"><i class="fa fa-video-camera fa-fw"></i> WebApp</a></li> -->

									@if(App\Helpers\SitePermissionCheck::isPermitted('admin.profile.showprofile'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('admin.profile.showprofile'))
											<li><a href="{{ URL::route('admin.profile.showprofile') }}"><i class="fa fa-user fa-fw"></i>
													My Profile</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('admin.manual.showmanual'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('admin.manual.showmanual'))
											<li><a href="{{ URL::route('admin.manual.showmanual') }}" target="_blank"><i
														class="fa fa-leanpub fa-fw"></i> Manual</a></li>
										@endif
									@endif

									@if(App\Helpers\SitePermissionCheck::isPermitted('admin.logout'))
										@if(App\Helpers\RolePermissionCheck::isPermitted('admin.logout'))
											<li><a href="<?=route('admin.logout')?>">&nbsp;<span
														class="fa fa-fw fa-sign-out">&nbsp;&nbsp;Logout</span></a></li>
										@endif
									@endif
								</ul>
							</li>
						</ul>
					@else
						<ul class="nav navbar-nav navbar-right">
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#"
									style="display:block;padding-left:40px;position:relative">
									<img src="/backend/login1.png" class=""
										style="height:30px;width:30px;position:absolute;top:5px;left:5px;">
									<span class="caret"></span></a>
								<ul class="dropdown-menu">
									<li><a href="<?=route('admin.logout')?>">&nbsp;<span
												class="fa fa-fw fa-sign-out">&nbsp;&nbsp; Logout</span></a></li>
								</ul>

					@endif

				@else
							<ul class="nav navbar-nav navbar-right">
								<li class="dropdown">
									<a class="dropdown-toggle" data-toggle="dropdown" href="#"
										style="display:block;padding-left:40px;position:relative">
										<img src="/backend/login1.png" class=""
											style="height:30px;width:30px;position:absolute;top:5px;left:5px;">
										<span class="caret"></span></a>
									<ul class="dropdown-menu">
										<li><a href="<?=route('admin.logout')?>">&nbsp;<span
													class="fa fa-fw fa-sign-out">&nbsp;&nbsp;Logout</span></a></li>
									</ul>
						@endif
			</div>
		</div>
	</nav>