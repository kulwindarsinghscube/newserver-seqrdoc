<div class="container">
     <div class="float-right" style="margin-left: 72%">
                @if(Request::segment(2) == 'create')
                    <button type="button" id="save_new_1" class="btn btn-sm btn-primary save" title="Save & New">Save & New</button>
                @endif
                <button type="button" id="save_exit_1" class="btn btn-sm btn-info save"title="Save & Close">Save & Close</button>
                <a href="<?= route('website-permission.index') ?>" role="button" class="btn btn-light cancel" title="Cancel">Cancel</a>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <section class="hk-sec-wrapper">
                <h5 class="hk-sec-title">Site Information</h5>
                <hr>
                <div class="row">
                    <div class="col-sm">
                        <div class="row">
                            <div class="col-md-6 form-group">

                                <label for="name">website Url<sup class="text-danger">*</sup></label>
                                <?= Form::text('site_url',old('name'), ['class' => 'form-control', 'placeholder' => 'Enter site url']); ?>
                                <span id="site_url_error" class="help-inline text-danger"><?= $errors->first('name') ?></span>
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        
                                        <label for="start_date">Start Date<sup class="text-danger">*</sup></label>
                                        <?= Form::text('start_date',old('name'), ['class' => 'form-control', 'placeholder' => 'Enter Start date','id'=>'datepicker','width'=>270]); ?>
                                         <span id="start_date_error" class="help-inline text-danger"><?= $errors->first('name') ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="name">End Date<sup class="text-danger">*</sup></label>
                                        <!-- <input id="datepicker2" width="270" name="end_date" /> -->
                                        <?= Form::text('end_date',old('end_date'), ['class' => 'form-control', 'placeholder' => 'Enter End date','id'=>'datepicker2','width'=>270]); ?>
                                         <span id="end_date_error" class="help-inline text-danger"><?= $errors->first('end_date') ?></span>
                                    </div>
                                </div>
                                <br>
                                <label for="License">License key<sup class="text-danger">*</sup></label>
                                <?= Form::text('license_key',old('name'), ['class' => 'form-control', 'placeholder' => 'Enter License key','maxlength'=>16]); ?>
                                <span id="license_key_error" class="help-inline text-danger"><?= $errors->first('name') ?></span>
                                <br>
                                <label for="pdf_storage_path">Pdf Storage Path [Like -> E:\seqrdoc\public\]</label>
                                <?= Form::text('pdf_storage_path',old('pdf_storage_path'), ['class' => 'form-control', 'placeholder' => 'Enter Pdf Storage Path','maxlength'=>256]); ?>
                                <span id="pdf_storage_path_error" class="help-inline text-danger"><?= $errors->first('pdf_storage_path') ?></span>
                                <br>
                                <label for="apple_app_url">Apple App URL</label>
                                <?= Form::text('apple_app_url',old('apple_app_url'), ['class' => 'form-control', 'placeholder' => 'Apple App URL','maxlength'=>250]); ?>
                                <span id="apple_app_url_error" class="help-inline text-danger"><?= $errors->first('apple_app_url') ?></span>
                                <br>
                                <label for="android_app_url">Android App URL</label>
                                <?= Form::text('android_app_url',old('android_app_url'), ['class' => 'form-control', 'placeholder' => 'Android App URL','maxlength'=>250]); ?>
                                <span id="android_app_url_error" class="help-inline text-danger"><?= $errors->first('android_app_url') ?></span>
                                <br>
                                <label for="Quantity_of_prints">Quantity of prints<sup class="text-danger">*</sup></label>
                                @if(isset($total_print))
                                <?= Form::text('value' ,$total_print, ['class' => 'form-control', 'placeholder' => 'Enter Print quantity','maxlength'=>10,'onkeypress'=>"return isNumberKey(event)"]); ?>
                                @else
                                <?= Form::text('value' ,old('name'), ['class' => 'form-control', 'placeholder' => 'Enter Print quantity','maxlength'=>10,'onkeypress'=>"return isNumberKey(event)"]); ?>
                                @endif
                                <span id="value_error" class="help-inline text-danger"><?= $errors->first('name') ?></span>
                                <br>


                                @if(Request::segment(2) != 'create')
                                <div class="col-md-6">    
                                    <label for="Count of printed">Count of Printed Documents</label>
                                    
                                        <?= Form::text('printed_document',$current_print, ['class' => 'form-control col-md-6','disabled'=>true]); ?>
                                </div>
                                <div class="col-md-6">    
                                    <label for="Count of printed left">Printed Documents Left</label>
                                    
                                        <?= Form::text('printed_document_left',$print_left, ['class' => 'form-control col-md-6','disabled'=>true]); ?>
                                </div>
                                @endif
                                <label for="Country">Country</label>
                                <div class="form-group form-float">
                                    <div class="form-line select-container">
                                        <select class="form-control  select show-tick" name="country" id="country" required aria-required="true">
                                            <option value="">Select Country</option>
                                             <option value="India">India</option>
                                             <option value="Kenya">Kenya</option>
                                             <option value="Bangladesh">Bangladesh</option>
                                             <option value="Nigeria">Nigeria</option>
                                             
                                        </select>
                                    </div>
                                </div>
                                <div id="stateDiv" style="display: none;">
                                 <label for="State">State</label>
                                <div class="form-group form-float">
                                    <div class="form-line select-container">
                                        <select class="form-control  select show-tick" name="state" id="state" required aria-required="true">
                                            <option value="">Select State</option>
                                            <option value="Andhra Pradesh">Andhra Pradesh</option>
                                            <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                                            <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                            <option value="Assam">Assam</option>
                                            <option value="Bihar">Bihar</option>
                                            <option value="Chandigarh">Chandigarh</option>
                                            <option value="Chhattisgarh">Chhattisgarh</option>
                                            <option value="Dadar and Nagar Haveli">Dadar and Nagar Haveli</option>
                                            <option value="Daman and Diu">Daman and Diu</option>
                                            <option value="Delhi">Delhi</option>
                                            <option value="Lakshadweep">Lakshadweep</option>
                                            <option value="Puducherry">Puducherry</option>
                                            <option value="Goa">Goa</option>
                                            <option value="Gujarat">Gujarat</option>
                                            <option value="Haryana">Haryana</option>
                                            <option value="Himachal Pradesh">Himachal Pradesh</option>
                                            <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                            <option value="Jharkhand">Jharkhand</option>
                                            <option value="Karnataka">Karnataka</option>
                                            <option value="Kerala">Kerala</option>
                                            <option value="Madhya Pradesh">Madhya Pradesh</option>
                                            <option value="Maharashtra">Maharashtra</option>
                                            <option value="Manipur">Manipur</option>
                                            <option value="Meghalaya">Meghalaya</option>
                                            <option value="Mizoram">Mizoram</option>
                                            <option value="Nagaland">Nagaland</option>
                                            <option value="Odisha">Odisha</option>
                                            <option value="Punjab">Punjab</option>
                                            <option value="Rajasthan">Rajasthan</option>
                                            <option value="Sikkim">Sikkim</option>
                                            <option value="Tamil Nadu">Tamil Nadu</option>
                                            <option value="Telangana">Telangana</option>
                                            <option value="Tripura">Tripura</option>
                                            <option value="Uttar Pradesh">Uttar Pradesh</option>
                                            <option value="Uttarakhand">Uttarakhand</option>
                                            <option value="West Bengal">West Bengal</option>
                                        </select>
                                    </div>
                                </div>
                                </div>
                                 <label for="organization_category">Organization Category</label>
                                <?= Form::text('organization_category',old('organization_category'), ['class' => 'form-control', 'placeholder' => 'Organization Category','maxlength'=>250]); ?>
                                <span id="organization_category_error" class="help-inline text-danger"><?= $errors->first('organization_category') ?></span>
                                

                                <br><br>
                                <label for="status" class="w-100 mt-4">Status<sup class="text-danger">*</sup></label>
                                <div class="row">
                                    <div class="custom-control custom-radio radio-primary col-md-2 ml-15">
                                       <label class="container_radio">Active    
                                        {!! Form::radio('status',1, true,["class"=>"custom-control-input",'id'=>'customRadio1']) !!}
                                        <span class="checkmark" for=""></span>
                                    </label>
                                    </div>
                                    <div class="custom-control custom-radio radio-primary col-md-2 ml-15">
                                        <label class="container_radio">InActive  
                                        {!! Form::radio('status',0,null,["class"=>"custom-control-input",'id'=>"customRadio2"]) !!}
                                        <span class="checkmark" for=""></span>
                                    </label>
                                    </div>
                                </div>
                                @if(Request::segment(2) != 'create')

                                <label for="organization_category">Wallet Address</label>
                                <input type="text" class="form-control" value="<?php echo $role_details['bc_wallet_address'];?>" readonly>
                                <br>
                                <label for="organization_category">Private Key</label>
                                <input type="text" class="form-control" value="<?php echo $role_details['bc_private_key'];?>" readonly>
                                <br>
                                <!-- <label id="fileCountLoader">Please wait...We are counting numbers...</label> -->
                                <div id="fileDetails">
                                <label>Number of documents for verification : <b><span id="file_count_pdf_file">Counting...</span> / <span id="file_size_pdf_file">Counting...</span><?php //echo $fileDetailsArr["file_count_pdf_file"].' / '.$fileDetailsArr["file_size_pdf_file"]; ?></b></label>
                                <label>Number of documents on AWS for verification : <b><span id="aws_file_count_pdf_file">Counting...</span> / <span id="aws_file_size_pdf_file">Counting...</span> </b></label>
                                <br>
                                <label>Number of inactive documents : <b><span id="file_count_pdf_file_inactive">Counting...</span> / <span id="file_size_pdf_file_inactive">Counting...</span><?php //echo $fileDetailsArr["file_count_pdf_file_inactive"].' / '.$fileDetailsArr["file_size_pdf_file_inactive"]; ?></b></label>
                                <label>Number of inactive on AWS documents : <b><span id="aws_file_count_pdf_file_inactive">Counting...</span> / <span id="aws_file_size_pdf_file_inactive">Counting...</span></b></label>
                                <br>
                                <label>Number of live pdfs : <b><span id="file_count_pdf_file_live">Counting...</span> / <span id="file_size_pdf_file_live">Counting...</span><?php //echo $fileDetailsArr["file_count_pdf_file_live"].' / '.$fileDetailsArr["file_size_pdf_file_live"]; ?></b></label>
                                <label>Number of live on AWS pdfs : <b><span id="aws_file_count_pdf_file_live">Counting...</span> / <span id="aws_file_size_pdf_file_live">Counting...</span></b></label>
                                <br>
                                <label>Number of preview pdfs : <b><span id="file_count_pdf_file_preview">Counting...</span> / <span id="file_size_pdf_file_preview">Counting...</span><?php //echo $fileDetailsArr["file_count_pdf_file_preview"].' / '.$fileDetailsArr["file_size_pdf_file_preview"]; ?></b></label>
                                <label>Number of preview on AWS pdfs : <b><span id="aws_file_count_pdf_file_preview">Counting...</span> / <span id="aws_file_size_pdf_file_preview">Counting...</span></b></label>
                                <br>
                                <label>Number of template files : <b><span id="file_count_pdf_file_templates">Counting...</span> / <span id="file_size_pdf_file_templates">Counting...</span><?php //echo $fileDetailsArr["file_count_pdf_file_templates"].' / '.$fileDetailsArr["file_size_pdf_file_templates"]; ?></b></label>
                                <label>Number of template on AWS files : <b><span id="aws_file_count_pdf_file_templates">Counting...</span> / <span id="aws_file_size_pdf_file_templates">Counting...</span></b></label>
                                <br>
                                <label>Error log File : <b><span id="storageSize">Counting...</span> / <span id="storageLatestFileName">Processing...</span> <?php //echo $fileDetailsArr["file_count_pdf_file_templates"].' / '.$fileDetailsArr["file_size_pdf_file_templates"]; ?></b></label>
                                <br>
                                <label>Database Details: <b><span id="database_size">Counting...</span> / <span id="database_table_count">Processing...</span> </b></label>
                            	</div>
                                 @endif
                            </div>
                            
                        </div>
                    </div>
                </div>
            </section>
            <section>
               <!--  <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" id="selectall"><span class="label-text" style="color: #333 !important;font-weight: normal !important">Click Here To Select All Permission</span>
                    </label>
                </div>
                <div class="row">
                    @foreach($user_permission as $keys => $single_user_perms)
                        <div class="col-sm-12 col-md-4">
                            <div class="card padding-20">
                              
                                <h5 class="subtitle custom-multiple-header">
                                  <?php $word_one=explode('_', $keys) ?> 
                                       
                                     <?=  $word_one[0];  ?>
                                </h5>
                                <hr>
                                <div class="custom-multiple-section">
                                    <div class="animated-checkbox">
                                        <label>
                                            <input type="checkbox" class ="sub_selection" value="<?= $keys ?>"><span class="label-text" style="color: #333 !important;font-weight: normal !important">Select All</span>
                                        </label>
                                    </div>

                                    @if(Request::segment(2) == 'create')
                                        <?= Form::select('permission[]',$single_user_perms,null, array('multiple'=>true,'class' => 'multi-select user_permission_multi '.$keys)) ?>
                                    @else
                                        <?= Form::select('permission[]',$single_user_perms,old('permission',$role_current_permissions), array('multiple'=>true,'class' => 'multi-select user_permission_multi '.$keys)) ?>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                <center><span id="permission_error" class="text-danger text-center"><?= $errors->first('permission') ?></span></center>
                </div> -->

                <div class="row clearfix">
                            <!-- Visitors -->
                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="body">
                                        <!-- <div class="form-group form-float">
                                            <div class="form-line select-container">
                                                <select class="form-control  select show-tick" name="roleId" id="roleId" required aria-required="true">
                                                    <option value="">Select Role</option>
                                                     <option value="1">Test</option>
                                                    <?php 
                                                   /* foreach ($roles as $readRole) {

    if ($readRole->role_id != 1) {?>

                                                        <option value="<?php echo $readRole->role_id; ?>"><?php echo $readRole->role_name; ?></option>
                                                    <?php }}*/
                                                    ?>
                                                </select>
                                            </div>
                                        </div> -->
                                        <div class="form-group  form-float">
                                            <input type="text" id="jstree_q" class="form-control" placeholder="Search Permissions" style="border-bottom: 1px solid #dddddd;" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- #END# Visitors -->
                            <!-- Latest Social Trends -->
                            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9" id="perms">
                                <div class="card" style="padding: 10px;">
                                    <div class="body ">
                                  
                                           <div class="clearfix">
                                            <div class="pull-left"><i class="fa fa-th-large theme"></i> Permissions </div>
                                            <div class="pull-right">
                                                <span class="btn btn-secondary btn-sm open_me" data-placement="left" data-toggle="tooltip" title="Toggle Tree" id="toggle-tree">
                                                    <i class="fa fa-sitemap"></i>&nbsp;<i class="fa fa-caret-down" id="toggle-tree-button"></i>
                                                </span>
                                              <!--   <button id="updatePerms" class="btn-success btn btn-sm" data-placement="left" data-toggle="tooltip" title="Update Permissions"><i class="fa fa-check-circle fa-lg"></i></button> -->
                                            </div>
                                        </div>

                                        <div id="jstree">
                                            <ul>
                                                <li data-jstree='{"icon":"fa fa-user-secret fa-lg theme"}' id="j_alltree">All Permissions (Not recommended)

                                                   <!--  <ul>
                                                        <li>Document Setup
                                                            <ul>
                                                                <li>Font Master
                                                                    <ul>
                                                                        <li>Add font</li>
                                                                    </ul>
                                                                </li>
                                                                <li>Template Master
                                                                    <ul>
                                                                        <li>Add Template</li>
                                                                    </ul>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul> -->

                                                    <ul>
                                                         <?php $previousModule=''; 
                                                                $previousModuleMainMenu='';
                                                                $newMod=0;
                                                                /*print_r($user_permission);
                                                                exit;*/
                                                         ?>    
                                                        @foreach($user_permission as $keys => $single_user_perms)
                                                            
                                                            <?php

                                                           // print_r($single_user_perms);
                                                            if($single_user_perms->main_menu_no!=$previousModuleMainMenu&&$single_user_perms->step_id==0){

                                                                
                                                                if(!empty($previousModuleMainMenu)&&!empty($previousModule)){
                                                                    echo '</ul></li></ul></li>';
                                                                    $newMod=1;
                                                                }else if(!empty($previousModuleMainMenu)&&empty($previousModule)){
                                                                    echo '</ul></li>';
                                                                    $newMod=1;
                                                                }
                                                                $previousModuleMainMenu=$single_user_perms->main_menu_no;
                                                           


                                                           ?>

                                                           <li data-jstree='{"icon":"fa fa-certificate fa-fw theme"}'><?php echo $single_user_perms->group_name; ?>
                                                                <ul>
                                                           <?php   
                                                            
                                                          /* echo "exit";
                                                           exit;*/
                                                            }

                                                            else{

                                                            if($previousModule!=$single_user_perms->group_name){

                                                                    if(!empty($previousModule)&&$newMod==0){
                                                                        echo '</ul></li>';
                                                                        }  
                                                                        $newMod=0;
                                                                    //if(empty($previousModule)){
                                                                        $previousModule=$single_user_perms->group_name;
                                                                   // }else{
                                                                        
                                                                    
                                                                    
                                                                
                                                                ?> 
                                                                
                                                                <li data-jstree='{"icon":"fa fa-certificate fa-fw theme"}'><?php echo $single_user_perms->group_name; ?>
                                                                <ul>
                                                                <?php } ?>     
                                                               
                                                                <li id="<?php echo $single_user_perms->main_route; ?>" data-jstree='{"icon":"fa fa-caret-right blue"}'><?php echo $single_user_perms->route_name; ?>
                                                                  
                                                                </li>
                                                             <?php 
                                                                //if($previousModule!=$single_user_perms->group_name){  

                                                                    //$previousModule=$single_user_perms->group_name;
                                                                    ?> 
                                                                
                                                                <?php 
                                                                }
                                                                
                                                                    //} ?>
                                                    @endforeach 
                                                        </ul>
                                                                </li>
                                                       

                                                            </ul>
                                                        </li>
                                                         
                                                    </ul>
                                                </li>

                                            </ul>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- #END# Latest Social Trends -->

                        </div><!--End Row-->
            </section>
            <div class="float-right" style="margin-left: 72%">
                @if(Request::segment(2) == 'create')
                    <button type="button" id="save_new_2" class="btn btn-sm btn-primary save" title="Save & New">Save & New</button>
                @endif
                <button type="button" id="save_exit_2" class="btn btn-sm btn-info save"title="Save & Close">Save & Close</button>
                <a href="<?= route('website-permission.index') ?>" role="button" class="btn btn-light cancel" title="Cancel">Cancel</a>
            </div>
        </div>
    </div>
</div>

<?php //exit;?>