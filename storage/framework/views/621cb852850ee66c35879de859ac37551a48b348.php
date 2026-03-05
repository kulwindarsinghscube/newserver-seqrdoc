<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-fw fa-gears"></i>PGC For Old Certificates
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('pgconfig')); ?></ol>
				<i class="fa fa-info-circle iconModalCss" title="User Manual" id="documentRateMasterClick"></i>			
				</h1>
			</div>
		</div>
<div class="panel panel-primary" style="margin: 0 auto; width: 100%;max-width:900px;">
			<!--<div class="panel-heading"><i class="fa fa-gears fa-fw"></i> Payment Gateway Configuration</div>-->
			<div class="panel-body">
				<form  id="document_rate_data">
					<table id="document_rate_table" class="table table-hover display" cellspacing="0" width="100%">
								<thead style="background-color: #337ab7;color: #fff;">
									<tr>
										<th>Document Type</th>
										<th>Rate Per Document (RS.)</th>
										<th>Last Updated On</th>
									</tr>
								</thead>
								<tbody id="documentRateMastertbody">

								</tbody>
								<tfoot>
								</tfoot>
							</table>
				
					
					<div class="form-group clearfix">
						
         <?php if(App\Helpers\SitePermissionCheck::isPermitted('documentsratemaster.update')): ?>
            <?php if(App\Helpers\RolePermissionCheck::isPermitted('documentsratemaster.update')): ?>
			<button type="button" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="update_save_document_rate"><i class="loadsave"></i> Update</button>
          <?php endif; ?>
            <?php endif; ?>
					</div>
				</form>	
			</div>
		</div>	<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/raisoni/paymentGatewayConfig/documentRateMaster.blade.php ENDPATH**/ ?>