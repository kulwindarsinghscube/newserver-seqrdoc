<div id="addSemester" class="modal fade clear_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Add Semester</h4>
                  </div>
                  <div class="modal-body">
                    
                    <form method="post" id="semesterData">
                      <input type="hidden" name="semester_id" id="semester_id">
                      <div class="form-group">
                        <label for="name">Semester Name</label>
                        <input type="text" class="form-control" id="semester_name" name="semester_name" data-rule-required="true" autofocus="autofocus" maxlength="191">
                        <span id="semester_name_error" class="help-inline text-danger"><?=$errors->first('semester_name')?></span>
                      </div>
                      <div class="form-group psswrd" id="psswrd">
                        <label for="name">Semester Full Name</label>
                        <input type="text" class="form-control" id="semester_full_name" name="semester_full_name" data-rule-required="true" autofocus="autofocus" maxlength="256">
                         <span id="semester_full_name_error" class="help-inline text-danger"><?=$errors->first('semester_full_name')?></span>
                      </div>
                      <div class="form-group clearfix">
                      <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 add_semester" id="SemesterSave"><i class="semestersave"></i> Save</button>
                      <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 update_semester" id="SemesterEdit"><i class="semesterupdate"></i> Update</button>
                      
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>