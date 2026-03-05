		<div class="modal fade clear_model" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="myModalLabel">Admin</h4>
							</div>
							<div class="modal-body">
								
								<form method="post" id="UserData">
									<input type="hidden" name="user_id" id="user_id">
									<div class="form-group">
										<label>Username</label>
										<input type="text" class="form-control" id="username" name="username" data-rule-required="true" >
											</select>
						                <span id="username_error" class="help-inline text-danger"><?=$errors->first('username')?></span>
									</div>
									<div class="form-group psswrd" id="psswrd">
										<label>Password</label>
										<input type="text" class="form-control" id="password" name="passwords" data-rule-required="true" >
										 <span id="passwords_error" class="help-inline text-danger"><?=$errors->first('password')?></span>
									</div>
									<div class="form-group">
										<label>Fullname</label>
										<input type="text" class="form-control allow_character" id="fullname" name="fullname" data-rule-required="true" >
										 <span id="fullname_error" class="help-inline text-danger"><?=$errors->first('fullname')?></span>
									</div>
									<div class="form-group">
										<label>Email</label>
										<input type="email" class="form-control" id="email_id" name="email" data-rule-required="true" >
										 <span id="email_error" class="help-inline text-danger"><?=$errors->first('email')?></span>
									</div>
									<div class="form-group">
										<label>Mobile</label>
										<input type="text" class="form-control allow_number" id="mobile_no" name="mobile_no" data-rule-required="true" maxlength="10" >
										 <span id="mobile_no_error" class="help-inline text-danger"><?=$errors->first('mobile_no')?></span>
									</div>
									<div class="form-group">
										<label for="opt_status">Status :</label>
										<select name="status" id="opt_status" class="form-control" data-rule-required="true">
											<option value="">Select</option>
											<option value="1">Active</option>
											<option value="0">Inactive</option>
										</select>
										 <span id="status_error" class="help-inline text-danger"><?=$errors->first('status')?></span>
									</div>
									<!-- Start Rushik Code -->
									<!-- add Role field -->
									<div class="form-group">
										<label for="role_id">Role </label>
										<select name="role" id="roleId" class="form-control" data-rule-required="true">
											<option value="">Select a Role</option>
										</select>
										 <span id="role_error" class="help-inline text-danger"><?=$errors->first('role')?></span>
									</div>
									<!-- End Rushik Code -->
									<div class="form-group clearfix">
									<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 add_user" id="UserSave"><i class="loadsave"></i> Save</button>
									<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 update_user" id="UserEdit"><i class="loadupdate"></i> Update</button>
									
									</div>
								</form>
							</div>
						</div>
					</div>
				</div><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/adminManagement/model.blade.php ENDPATH**/ ?>