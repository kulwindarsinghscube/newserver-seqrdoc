<div class="modal fade clear_model" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Font&nbsp;&nbsp;&nbsp;&nbsp;<span class="loading" style="display:none;" >Uploading ... <img src="../assets/images/loading.gif" /></span></h4>
					</div>
					<div class="modal-body">
						
						<form method="post" name="UserData" id="UserData" enctype="multipart/form-data">
							<div class="form-group">
								<input type="hidden" class="form-control" id="font_id" name="font_id">
							</div>
							<div class="form-group">
								<label for="name">Font Name</label>
								<input type="text" class="form-control" id="font_name" name="font_name" data-rule-required="true" autofocus="autofocus">
								<span id="font_name_error" class="help-inline text-danger"><?=$errors->first('font_name')?></span>
							</div>
							<div class="form-group">
								<label for="upload_font_N" style="font-weight: normal;">Upload Font Normal</label><span class="pull-right" id="upload_font_N" style="margin-bottom: 2px;"></span>
								<input type="file" class="form-control" name="upload_font_N" />
                               <span id="upload_font_N_error" class="help-inline text-danger"><?=$errors->first('upload_font_N')?></span> 
							</div>
							<div class="form-group">
								<label for="upload_font_B">Upload Font Bold</label><span class="pull-right" id="upload_font_B" style="margin-bottom: 2px;"></span>
								<input type="file" class="form-control" name="upload_font_B" />
								   <span id="upload_font_B_error" class="help-inline text-danger"><?=$errors->first('upload_font_B')?></span> 
							</div>
							<div class="form-group">
								<label for="upload_font_I" style="font-weight: normal;"><i>Upload Font Italic</i></label><span class="pull-right" id="upload_font_I" style="margin-bottom: 2px;"></span>
								<input type="file" class="form-control" name="upload_font_I" />
								   <span id="upload_font_I_error" class="help-inline text-danger"><?=$errors->first('upload_font_I')?></span> 
							</div>
							<div class="form-group">
								<label for="upload_font_BI"><i>Upload Font Bold Italic</i></label><span class="pull-right" id="upload_font_BI" style="margin-bottom: 2px;"></span>
								<input type="file" class="form-control" name="upload_font_BI" />
								   <span id="upload_font_BI_error" class="help-inline text-danger"><?=$errors->first('upload_font_BI')?></span> 
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
							<div class="form-group clearfix" id="button">
								
								<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 save" id="UserSave"><i class="loadsave"> </i>  Save</button> 	
								<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 update_save" id="updateSave"><i class="loadupdate"> </i> Update</button> 
							</div>
						</form>
					</div>
				</div>
			</div>
		</div><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/fontMaster/model.blade.php ENDPATH**/ ?>