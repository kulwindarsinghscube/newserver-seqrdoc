				<div class="modal fade clear_model" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="myModalLabel">WebsiteDetail</h4>
									</div>
									<div class="modal-body">
										
										<form method="post" id="UserData">
											<input type="hidden" name="user_id" id="user_id">
											<div class="form-group">
												<label>WebsiteName</label>
												<input type="text" class="form-control" id="website_url"
												 name="website_url" data-rule-required="true" >
													</select>
								                <span id="website_url_error" class="help-inline text-danger"><?=$errors->first('username')?></span>
											</div>
											<div class="form-group psswrd" id="psswrd">
												<label>DatabaseName</label>
												<input type="text" class="form-control" id="db_name" name="db_name" data-rule-required="true" >
												 <span id="db_name_error" class="help-inline text-danger"><?=$errors->first('user_password')?></span>
											</div>
											<div class="form-group">
												<label>HostName</label>
												<input type="text" class="form-control allow_character" id="db_host_address" name="db_host_address" data-rule-required="true" >
												 <span id="db_host_address_error" class="help-inline text-danger"><?=$errors->first('fullname')?></span>
											</div>
											<div class="form-group">
												<label>Username</label>
												<input type="text" class="form-control" id="username" name="username" data-rule-required="true" >
												 <span id="username_error" class="help-inline text-danger"><?=$errors->first('email_id')?></span>
											</div>
											<div class="form-group">
												<label>Password</label>
												<input type="text" class="form-control" id="password" name="password" data-rule-required="true" >
												 <span id="password_error" class="help-inline text-danger"><?=$errors->first('email_id')?></span>
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
											<!-- End Rushik Code -->
											<div class="form-group clearfix">
											<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 add_user" id="UserSave"><i class="loadsave"></i> Save</button>
											<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 update_user" id="UserEdit"><i class="loadupdate"></i> Update</button>
											
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>