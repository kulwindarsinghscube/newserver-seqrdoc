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
									<div class="form-group">
										<label>Student Name</label>
										<input type="text" class="form-control" id="sname" name="sname" data-rule-required="true" >
									</select>
									<span id="sname_error" class="help-inline text-danger"><?=$errors->first('sname')?></span>
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
								<!-- End Rushik Code -->

								<div class="form-group clearfix">
									<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 update_user" id="UserEdit"><i class="loadupdate"></i> Update</button>

								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade clear_model" id="uploadFile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close"  data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="myModalLabel">Upload excel</h4>
						</div>
						<div class="modal-body">						
							<form method="POST" enctype="multipart/form-data" id="student_doc">	
								<div class="form-group">
									<label>Upload File</label>
									<input type="file" class="form-control" id="field_file" name="field_file">
									<input type="hidden" name="import" id="import" value="parsexcel">
									<span id="field_file_error" class="help-inline text-danger"><?=$errors->first('field_file')?></span>
								</div>
								<div id="studentlink">
								</div>
								<div class="form-group clearfix">
									<div id="upload_btn">
										<a href="#" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-7" id="btn_updfile_back" onclick="closeModel();" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
										<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 save" id="btn_updfile" name="btn_updfile" style="margin-left: 318px;margin-right: 10px;"><i class="loadsave"></i> Upload</button>
										<button type="submit" class="btn btn-theme" value="importstudent" id="importstudent" name="importstudent" style="display: none"><i class="fa fa-upload" ></i>Confirm</button>
										
										
										<a style="float: left;border: 1px dotted red;background: green;color: #fff;font-size:12px;" class="btn btn-theme" href="<?php echo e(url('demo/backend/sample_excel/import_users_sample.xlsx')); ?>">Download Sample</a>
									</div>
								</div>
							</form>
							<div class="form-group">
								<div>Note:-</div>
								<ol>
									<li>Unique Document Number Expected</li>
									<li>Accepted file format XLS or XLSX.</li>
									<li>Max file size 10 MB</li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/functionalusers/model.blade.php ENDPATH**/ ?>