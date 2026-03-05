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
                            </div>
                            
                        </div>
                    </div>
                </div>
            </section>
            <section>
               

                <div class="row clearfix">
                            <!-- Visitors -->
                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="body">
                                       
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
                                                    <ul>
                                                         <?php $previousModule=''; ?>    
                                                        @foreach($user_permission as $keys => $single_user_perms)
                                                            
                                                            <?php if($previousModule!=$single_user_perms->group_name){

                                                                    if(!empty($previousModule)){
                                                                        echo '</ul></li>';
                                                                        }  
                                                                    //if(empty($previousModule)){
                                                                        $previousModule=$single_user_perms->group_name;
                                                                   // }else{
                                                                        
                                                                    
                                                                    
                                                                
                                                                ?> 
                                                                
                                                                <li data-jstree='{"icon":"fa fa-certificate fa-fw theme"}'><?php echo $single_user_perms->group_name; ?>
                                                                <ul>
                                                                <?php } ?>     
                                                               
                                                                <li id="<?php echo $single_user_perms->main_route; ?>" data-jstree='{"icon":"fa fa-caret-right blue"}'><?php echo $single_user_perms->route_name; ?></li>
                                                             <?php 
                                                                //if($previousModule!=$single_user_perms->group_name){  

                                                                    //$previousModule=$single_user_perms->group_name;
                                                                    ?> 
                                                                
                                                                <?php 
                                                                
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

