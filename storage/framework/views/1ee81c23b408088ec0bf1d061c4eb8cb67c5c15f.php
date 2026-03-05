	<div class="modal fade clear_model" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Payment Gateway</h4>
					</div>
					<div class="modal-body">
						<form method="post" id="UserData">
							<div class="form-group">
								<input type="hidden" class="form-control" id="pg_id" name="pg_id">
								<label for="name">Payment Gateway Name</label>
								<input type="text" class="form-control" id="pg_name" name="pg_name" data-rule-required="true" >
								<span id="pg_name_error" class="help-inline text-danger"><?=$errors->first('pg_name')?></span>
							</div>
							<div class="form-group">
								<label for="opt_status">Status :</label>
								<select name="opt_status" id="opt_status" class="form-control" data-rule-required="true">
									<option value="">Select</option>
									<option value="1">Active</option>
									<option value="0">Inactive</option>
								</select>
								<span id="opt_status_error" class="help-inline text-danger"><?=$errors->first('opt_status')?></span>
							</div>
							<div class="form-group clearfix">
						   		
							<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 save" id="UserSave"><i class="loadsave"></i> Save</button>
						
							<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 update_save" id="UserEdit"><i class="loadupdate"></i> Update</button>
							
							</div>
						</form>
					</div>
				</div>
			</div>
	</div><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/paymentGateway/model.blade.php ENDPATH**/ ?>