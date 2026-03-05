<div id="addStationaryStock" class="modal fade clear_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Add Stationary</h4>
                  </div>
                  <div class="modal-body">
                    
                    <form method="post" id="stockData">
                      <input type="hidden" name="stock_id" id="stock_id">
                      <div class="form-group">
                        <label for="card_category">Card Category <sup>*</sup></label>
                        <select class="form-control" id="card_category" name="card_category" data-rule-required="true">
                            <option value="" selected disabled>Select Card Category</option>
                            <option value="Grade Cards">Grade Cards</option>
                            <option value="Certificates">Certificates</option>
                        </select>
                        <span id="card_category_error" class="help-inline text-danger"><?=$errors->first('card_category')?></span>
                      </div>
                      <div class="form-group psswrd" id="psswrd">
                            <label>Academic Year <sup>*</sup> </label>
                            <select class="form-control" name="academic_year" id="academic_year" data-rule-required="true">
                                <option value="" disabled selected>Select Year</option>
                                <?php
                                    $currently_selected = date('Y') . ' - ' . date('Y', strtotime('+1 Year'));
                                    $earliest_year = 2018;
                                    $latest_year = date('Y');

                                    foreach (range($latest_year, $earliest_year) as $i) {
                                        echo '<option value="' . ($i) . ' - ' . ($i + 1) . '"' . (($i) . ' - ' . ($i + 1) === $currently_selected ? ' selected="selected"' : '') . '>' . ($i) . ' - ' . ($i + 1) . '</option>';
                                    }

                                    ?>
                            </select>
                            <span id="academic_year_error" class="help-inline text-danger"><?=$errors->first('academic_year')?></span>
                      </div>
                      <div class="form-group psswrd" id="psswrd">
                        <label for="name">Date Of Received <sup>*</sup></label>
                        <div class="input-group date" data-provide="datepicker" value="">
                            <input type="text" class="form-control datetimepicker" name="date_of_received" id="date_of_received" data-rule-required="true">
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-th"></span>
                            </div>
                        </div>
                        <span id="date_of_received_error" class="help-inline text-danger"><?=$errors->first('date_of_received')?></span>
                      </div>
                      <div class="form-group">
                            <label for="name">Serial No. From <sup>*</sup></label>
                            <input type="text" class="form-control allow_number" id="serial_no_from" name="serial_no_from" data-rule-required="true" >
                            <span id="serial_no_from_error" class="help-inline text-danger"><?=$errors->first('serial_no_from')?></span>
                        </div>
                        <div class="form-group">
                            <label for="name">Serial No. To <sup>*</sup></label>
                            <input type="text" class="form-control allow_number" id="serial_no_to" name="serial_no_to" data-rule-required="true" >
                            <span id="serial_no_to_error" class="help-inline text-danger"><?=$errors->first('serial_no_to')?></span>
                        </div>
                        <div class="form-group">
                            <label for="name">Quantity <sup>*</sup></label>
                            <input type="text" class="form-control allow_number" id="quantity" name="quantity" data-rule-required="true" readonly="">
                            <span id="quantity_error" class="help-inline text-danger"><?=$errors->first('quantity')?></span>
                        </div>
                      <div class="form-group clearfix">
                      <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 add_stock" id="StockSave"><i class="branchsave"></i> Save</button>
                      
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>