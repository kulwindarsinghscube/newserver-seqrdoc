<div class="modal fade clear_model" id="assignFont" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						 <h4 class="modal-title" id="myModalLabel">Font Name : <span id="font_name_title"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="loading" style="display:none;" >Uploading ... <img src="../assets/images/loading.gif" /></span></h4>
					</div>
					<div class="modal-body">
						
						<form method="post" name="fontData" id="fontData" enctype="multipart/form-data">
							<div class="form-group">
								<input type="hidden" class="form-control" id="font_id_assign" name="font_id_assign">
								<input type="hidden" class="form-control" id="font_name_assign" name="font_name_assign">
							</div>
							<!-- <div class="form-group">
								<label for="name">Font Name : <span id="font_name_assign"></span></label>
								
							</div> -->
							<div class="form-group">
									<label for="dest_instance">Select Instance :</label><br>
				                  	<!-- <select name="dest_instance" id="dest_instance" class="form-control" data-rule-required="true">
				                     --><select class="selectpicker" id="dest_instance" name="dest_instance[]" multiple data-live-search="true" style="width: 100%;" autocomplete="off" data-size="10"  data-selected-text-format="count" data-count-selected-text=" ({0} items selected)" data-actions-box='true'>
				                    <?php foreach ($instancesListArray as $readInstance) {
				                        echo '<option value="'.$readInstance['site_url'].'">'.$readInstance['site_url'].'</option>';
				                    } ?>
				                    
				                  	</select>
				                  	  <span id="dest_instance_error" class="help-inline text-danger"><?=$errors->first('dest_instance')?></span> 
							</div>
							<div class="form-group">
								<label for="normalFont">Select Font Style :</label><br>
								<div class="form-check" style="padding-left: 45px;" id="normalFontDiv">
								  <input class="form-check-input" type="checkbox" value="1" id="normalFont" name="normalFont">
								  <label class="form-check-label" for="normalFont">
								    Normal
								  </label>
								 </div>
								 <div class="form-check" style="padding-left: 45px;" id="boldFontDiv">
								  <input class="form-check-input" type="checkbox" value="1" id="boldFont" name="boldFont">
								  <label class="form-check-label" for="boldFont">
								    Bold
								  </label>
								 </div>
								 <div class="form-check" style="padding-left: 45px;" id="italicFontDiv">
								  <input class="form-check-input" type="checkbox" value="1" id="italicFont" name="italicFont">
								  <label class="form-check-label" for="italicFont">
								    Italic
								  </label>
								 </div>
								 <div class="form-check" style="padding-left: 45px;" id="boldItalicFontDiv">
								  <input class="form-check-input" type="checkbox" value="1" id="boldItalicFont" name="boldItalicFont">
								  <label class="form-check-label" for="boldItalicFont">
								    Bold-Italic
								  </label>
								 </div>
								  <span id="font_style_error" class="help-inline text-danger"><?=$errors->first('font_style')?></span> 
							</div>
							<div class="form-group clearfix" id="assignbutton">
								
								<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="assignSave"><i class="loadsave"> </i>  Submit</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
