<div id="addBranch" class="modal fade clear_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Add Branch</h4>
                  </div>
                  <div class="modal-body">
                    
                    <form method="post" id="branchData">
                      <input type="hidden" name="branch_id" id="branch_id">
                      <div class="form-group">
                        <label for="opt_status">Select Degree :</label>
                        <select name="degree_id" id="degree_id" class="form-control" data-rule-required="true">
                          <option value="" disabled selected>Select Degree</option>

                        </select>
                        <span id="degree_id_error" class="help-inline text-danger"><?=$errors->first('degree_id')?></span>
                      </div>
                      <div class="form-group psswrd" id="psswrd">
                       <label for="name">Branch Full Name</label>
                        <input type="text" class="form-control" id="branch_name_long" name="branch_name_long" data-rule-required="true" autofocus="autofocus" maxlength="256">
                         <span id="branch_name_long_error" class="help-inline text-danger"><?=$errors->first('branch_name_long')?></span>
                      </div>
                      <div class="form-group psswrd" id="psswrd">
                        <label for="name">Branch Short Name</label>
                        <input type="text" class="form-control" id="branch_name_short" name="branch_name_short" data-rule-required="true" autofocus="autofocus" maxlength="32">
                         <span id="branch_name_short_error" class="help-inline text-danger"><?=$errors->first('branch_name_short')?></span>
                      </div>
                      <div class="form-group clearfix">
                      <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 add_branch" id="BranchSave"><i class="branchsave"></i> Save</button>
                      <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 update_branch" id="BranchEdit"><i class="branchupdate"></i> Update</button>
                      
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>