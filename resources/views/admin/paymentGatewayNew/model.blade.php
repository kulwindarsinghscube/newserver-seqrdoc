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
								<label for="pg_name">Payment Gateway Name</label>
								<select name="pg_name" id="pg_name" class="form-control" data-rule-required="true">
									<option value="">Select</option>
									<option value="paytm">Paytm</option>
									<option value="payubiz">PayuBiz</option>
									<option value="payumoney">Payumoney</option>
									<option value="instamojo">Instamojo</option>
									<option value="omniware">Omniware</option>
									<option value="eazypay">Eazypay</option>
									<option value="mpesa">M Pesa</option>
									<option value="ccavenue">CCAvenue</option>
									<option value="paystack">Paystack</option>

									

								</select>
								<span id="pg_name_error" class="help-inline text-danger"><?=$errors->first('pg_name')?></span>
							</div>


							<div class="form-group">
								<input type="hidden" class="form-control" id="pg_id" name="pg_id">
								<label for="name">Title</label>
								<input type="text" class="form-control" id="pg_title" name="pg_title" data-rule-required="true" >
								<span id="pg_title_error" class="help-inline text-danger"><?=$errors->first('pg_title')?></span>
							</div>

							<div class="form-group">
								<label for="pg_mode">Status :</label>
								<select name="pg_mode" id="pg_mode" class="form-control" data-rule-required="true">
									<option value="">Select</option>
									<option value="live">Live</option>
									<option value="test">Test</option>
								</select>
								<span id="pg_mode_error" class="help-inline text-danger"><?=$errors->first('pg_mode')?></span>
							</div>

							<div class="form-group">
								<label for="name">Merchant Key</label>
								<input type="text" class="form-control" id="merchant_key" name="merchant_key" data-rule-required="true" >
								<span id="merchant_key_error" class="help-inline text-danger"><?=$errors->first('merchant_key')?></span>
							</div>

							<div class="form-group">
								<label for="name">Merchant Salt</label>
								<input type="text" class="form-control" id="merchant_salt" name="merchant_salt" data-rule-required="true" >
								<span id="merchant_salt_error" class="help-inline text-danger"><?=$errors->first('merchant_salt')?></span>
							</div>

							<div class="form-group">
								<label for="name">Test Merchant Key</label>
								<input type="text" class="form-control" id="test_merchant_key" name="test_merchant_key" data-rule-required="true" >
								<span id="test_merchant_key_error" class="help-inline text-danger"><?=$errors->first('test_merchant_key')?></span>
							</div>
							
							<div class="form-group">
								<label for="name">Test Merchant Salt</label>
								<input type="text" class="form-control" id="test_merchant_salt" name="test_merchant_salt" data-rule-required="true" >
								<span id="test_merchant_salt_error" class="help-inline text-danger"><?=$errors->first('test_merchant_salt')?></span>
							</div>

							<div class="form-group">
								<label for="name">Website</label>
								<input type="text" class="form-control" id="website" name="website" data-rule-required="true" >
								<span id="website_error" class="help-inline text-danger"><?=$errors->first('website')?></span>
							</div>

							<div class="form-group">								
								<label for="name">Channel</label>
								<input type="text" class="form-control" id="channel" name="channel" data-rule-required="true" >
								<span id="channel_error" class="help-inline text-danger"><?=$errors->first('channel')?></span>
							</div>

							<div class="form-group">								
								<label for="name">Industry Type</label>
								<input type="text" class="form-control" id="industry_type" name="industry_type" data-rule-required="true" >
								<span id="industry_type_error" class="help-inline text-danger"><?=$errors->first('industry_type')?></span>
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
	</div>