				<div class="modal fade clear_model" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="myModalLabel">User</h4>
									</div>
									<div class="modal-body">
										
										<form method="post" id="UserData">
											<input type="hidden" name="user_id" id="user_id">
										<?php	$domain =$_SERVER['HTTP_HOST'];
            $subdomain = explode('.', $domain);
            if($subdomain[0] == "demo"||$subdomain[0] == "raisoni"||$subdomain[0] == "galgotias"){ ?>
											<div class="form-group">
												<div class="row">
													<div class="col-xs-4 col-md-4 col-lg-4" style="text-align: center;">
														<input  id="employer_type"  name="registration_type" type="radio" value="1" data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Employer</label>
													</div>
													<div class="col-xs-4 col-md-4 col-lg-4" style="text-align: center;">
														<input  id="agency_type"  name="registration_type" type="radio"  value="2" data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Third Party Agency</label>
													</div>
													<div class="col-xs-4 col-md-4 col-lg-4" style="text-align: center;">
														<input  id="student_type"  name="registration_type" type="radio"  value="0" data-rule-required="true" style="vertical-align: top;">&nbsp;&nbsp;<label>Student</label>
													</div>
												</div>
											</div>

											<?php } ?>

											<div class="form-group">
												<label>Username</label>
												<input type="text" class="form-control" id="username" name="username" data-rule-required="true" >
													</select>
								                <span id="username_error" class="help-inline text-danger"><?=$errors->first('username')?></span>
											</div>
											<div class="form-group psswrd" id="psswrd">
												<label>Password</label>
												<input type="text" class="form-control" id="password" name="user_password" data-rule-required="true" >
												 <span id="user_password_error" class="help-inline text-danger"><?=$errors->first('user_password')?></span>
											</div>
											<div class="form-group">
												<label>Fullname</label>
												<input type="text" class="form-control allow_character" id="fullname" name="fullname" data-rule-required="true" >
												 <span id="fullname_error" class="help-inline text-danger"><?=$errors->first('fullname')?></span>
											</div>
											<div class="form-group">
												<label>Email</label>
												<input type="email" class="form-control" id="email_id" name="email_id" data-rule-required="true" >
												 <span id="email_id_error" class="help-inline text-danger"><?=$errors->first('email_id')?></span>
											</div>
											<div class="form-group">
												<label>Mobile</label>
												<input type="text" class="form-control allow_number" id="mobile_no" name="mobile_no"  maxlength="10">
												 <span id="mobile_no_error" class="help-inline text-danger"><?=$errors->first('mobile_no')?></span>
											</div>


   <?php if($subdomain[0] == "demo"||$subdomain[0] == "raisoni"||$subdomain[0] == "galgotias"){ ?>
											<div class="emp_agency_holder">
							<div class="form-group">
								<label>CIIN / Registration Number <sup>*</sup></label>
								<input class="form-control" id="reg_no" name="reg_no" type="text" onkeypress="return isAlphaNumeric(event)" minlength="1" maxlength="25" autofocus data-rule-required="true">
							</div>
							<div class="form-group">

								<label>Working Sector <sup>*</sup> </label>

								<select class="form-control" name="working_sector" id="working_sector" data-rule-required="true">
									<option value="" disabled selected>Select Working Sector</option>
									<option value="Public sector">Public sector</option>
									<option value="Private sector">Private sector</option>
									<option value="Government Body">Government Body</option>
									<option value="Public Sector Unit">Public Sector Unit</option>
								</select>

							</div>
							<div class="form-group">
									<label>Address <sup>*</sup></label>
									<textarea class="form-control" id="address" placeholder="Mumbai" name="address" type="text" autofocus data-rule-required="true"></textarea>
							</div>
							</div>
							<div class="student_hoder">
								<div class="form-group">

								<label>Institute <sup>*</sup> </label>

								<select class="form-control" name="student_institute" id="student_institute" data-rule-required="true">
									<option value="" disabled selected>Select Institute</option>
									<option value="G H Raisoni College of Engineering, Nagpur">G H Raisoni College of Engineering, Nagpur</option>
								</select>

							</div>

							<div class="form-group">

									<label>Degree <sup>*</sup> </label>

									<select class="form-control" name="student_degree" id="student_degree" data-rule-required="true">
										<option value="" disabled selected>Select Degree</option>

									</select>
									</div>
									<div class="form-group">
									<label>Branch <sup>*</sup> </label>

									<select class="form-control" name="student_branch" id="student_branch" data-rule-required="true">
										<option value="" disabled selected>Select Branch</option>

									</select>
									</div>


							<div class="form-group">
								<label>Passout Year <sup>*</sup> </label>
								<select class="form-control" name="passout_year" id="passout_year" data-rule-required="true">
									<option value="" disabled selected>Select Year</option>
									<?php
$currently_selected = date('Y');
$earliest_year = 2000;
$latest_year = date('Y');

foreach (range($latest_year, $earliest_year) as $i) {
	echo '<option value="' . $i . '"' . ($i === $currently_selected ? ' selected="selected"' : '') . '>' . $i . '</option>';
}

?>								</select>

							</div>
							<div class="form-group">
								<label>Registration Number <sup>*</sup></label>
								<input class="form-control" id="student_reg_no" name="student_reg_no" type="text" onkeypress="return isAlphaNumeric(event)" placeholder="2015ACSC1234567" minlength="14" maxlength="15" autofocus data-rule-required="true">
							</div>


							</div>

							<?php } ?>

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
												<select name="role" id="roleId" class="form-control"><!--data-rule-required="true"-->
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
						</div>