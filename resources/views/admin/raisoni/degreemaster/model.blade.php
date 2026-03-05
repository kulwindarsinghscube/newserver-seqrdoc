	<div class="modal fade clear_model" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Degree Master</h4>
					</div>
					<div class="modal-body">
						<form method="post" id="UserData">
							<div class="form-group">
								<input type="hidden" class="form-control" id="degree_id" name="degree_id">
								<label for="name">Degree Name</label>
								<input type="text" class="form-control" id="degree_name" name="degree_name" data-rule-required="true" >
								<span id="degree_name_error" class="help-inline text-danger"><?=$errors->first('degree_name')?></span>
							</div>
							
							<div class="form-group clearfix">
						   		
							<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 save" id="UserSave"><i class="loadsave"></i> Save</button>
						
							<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 update_save" id="UserEdit"><i class="loadupdate"></i> Update</button>
							
							</div>
						</form>
					</div>
				</div>
			</div>
	</div>