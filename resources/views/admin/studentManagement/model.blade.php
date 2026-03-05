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
							
							<button type="button" style="float: left;border: 1px dotted red;background: green;color: #fff;font-size:12px;" class="btn btn-theme" id="btn_updfile" name="btn_updfile" onclick="Download()"> Download Sample</button>
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
</div>