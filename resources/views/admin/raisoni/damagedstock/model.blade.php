<div id="addCard" class="modal fade clear_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Add Damaged Serial Number</h4>
      </div>
      <div class="modal-body">
        
        <form method="post" id="stockData">
            <div class="form-group">
                <label for="card_category_filter">Card Category <sup>*</sup></label>
                <select class="form-control" id="card_category" name="card_category" data-rule-required="true">
                    <option value="" selected disabled>Select Card Category</option>
                    <option value="Grade Cards">Grade Cards</option>
                    <option value="Certificates">Certificates</option>
                </select>
                <span id="card_category_error" class="help-inline text-danger"><?=$errors->first('card_category')?></span>
            </div>
            <div class="form-group">
                <label for="name">Serial No. of Card <sup>*</sup></label>
                <input type="text" class="form-control allow_number" id="serial_no" name="serial_no" data-rule-required="true" maxlength="255">
                <span id="serial_no_error" class="help-inline text-danger"><?=$errors->first('serial_no')?></span>
            </div>
            <div class="form-group psswrd" id="psswrd">
                <label>Type <sup>*</sup> </label>
                <select class="form-control" name="type_damaged" id="type_damaged" data-rule-required="true" >
                    <option value="" disabled selected>Select Type</option>
                    <option value="Cancel">Cancel</option>
                    <option value="Corrections">Corrections</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Duplicate">Duplicate</option>
                </select>
                <span id="type_damaged_error" class="help-inline text-danger"><?=$errors->first('type_damaged')?></span>
            </div>
            <div class="form-group">
                <label for="name">Remark <sup>*</sup></label>
                <textarea class="form-control input-lg" id="remark" name="remark" type="text" minlength="1" maxlength="512"   data-rule-required="true"></textarea>
                <span id="remark_error" class="help-inline text-danger"><?=$errors->first('remark')?></span>
            </div>
            <div class="form-group">
                <label>Exam Name </label>
                <select class="form-control" name="exam" id="exam" >
                    <option value="All" disabled selected>Select Exam</option>
                </select>
                <span id="exam_error" class="help-inline text-danger"><?=$errors->first('exam')?></span>
            </div>
            <div class="form-group">
                <label>Degree </label>
                <select class="form-control" name="degree" id="degree" >
                    <option value="All" disabled selected>Select Degree</option>
                </select>
                <span id="degree_error" class="help-inline text-danger"><?=$errors->first('degree')?></span>
            </div>
            <div class="form-group">
                <label>Branch </label>
                <select class="form-control" name="branch" id="branch" >
                    <option value="All" disabled selected>Select Branch</option>
                </select>
                <span id="branch_error" class="help-inline text-danger"><?=$errors->first('branch')?></span>
            </div>
            <div class="form-group">
                <label>Semester </label>
                <select class="form-control" name="semester" id="semester" >
                    <option value="All" disabled selected>Select Semester</option>
                </select>
                <span id="semester_error" class="help-inline text-danger"><?=$errors->first('semester')?></span>
            </div>
            <div class="form-group">
                <label>Student Reg. Number </label>
                <input class="form-control allow_character_number" id="reg_no" name="reg_no" type="text" placeholder="2015ACSC1234567" minlength="14" maxlength="15" >
                <span id="reg_no_error" class="help-inline text-danger"><?=$errors->first('reg_no')?></span>
            </div>
          <div class="form-group clearfix">
          <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 add_stock" id="StockSave"><i class="branchsave"></i> Save</button>
          
          </div>
        </form>
      </div>
    </div>
    </div>
</div>