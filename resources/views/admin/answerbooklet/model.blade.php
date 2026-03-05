	<div class="modal fade clear_model" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Generate Answer Booklet</h4>
					</div>
					<div class="modal-body">
						<form method="post" id="UserData">
							<div class="form-group">
								<label for="name">Prefix Word</label>
								<input type="text" class="form-control" id="prefix_word" name="prefix_word" data-rule-required="true" >
								<span id="prefix_word_error" class="help-inline text-danger"><?=$errors->first('prefix_word')?></span>
							</div>
							<div class="form-group">
								<label for="name">Booklet Size</label>
								<input type="text" class="form-control allow_number" id="booklet_size" name="booklet_size" data-rule-required="true" >
								<span id="booklet_size_error" class="help-inline text-danger"><?=$errors->first('booklet_size')?></span>
							</div>
							<div class="form-group">
								<label for="name">Starting Serial Number</label>
								<input type="text" class="form-control allow_number" id="start_serial_no" name="start_serial_no" data-rule-required="true" >
								<span id="start_serial_no_error" class="help-inline text-danger"><?=$errors->first('start_serial_no')?></span>
							</div>
							<div class="form-group">
								<label for="name">Quantity</label>
								<input type="text" class="form-control allow_number" id="quantity" name="quantity" data-rule-required="true" >
								<span id="quantity_error" class="help-inline text-danger"><?=$errors->first('quantity')?></span>
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

