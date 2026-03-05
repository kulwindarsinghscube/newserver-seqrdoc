<?php
    $domain = \Request::getHost();
    $subdomain = explode('.', $domain);
?>
<div class="container">
          <div class="float-right" style="margin-left: 72%;padding-bottom: 10px">
                @if(Request::segment(3) == 'create')
                    <button type="button" id="save_new_1" class="btn btn-sm btn-primary save" title="Save & New">Save & New</button>
                @endif
                <button type="button" id="save_exit_1" class="btn btn-sm btn-info  save" title="Save & Close"> Save & Close</button>
                <a href="<?= route('adminmaster.index') ?>" role="button" class="btn btn-sm btn-light cancel" title="Cancel">Cancel</a>
            </div>
    <div class="row">
        <div class="col-xl-12">
            <section class="hk-sec-wrapper">
                <h5 class="hk-sec-title">Admin Information</h5>
                <hr>
                <div class="row">
                    <div class="col-sm">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">User Name<sup class="text-danger">*</sup></label>
                                <?= Form::text('username',old('username'), ['class' => 'form-control', 'placeholder' => 'Enter username']); ?>
                                <span id="username_error" class="help-inline text-danger"><?= $errors->first('username') ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email">Email<sup class="text-danger">*</sup></label>
                                <?= Form::text('email',old('email'), ['class' => 'form-control', 'placeholder' => 'Enter Email']); ?>
                                <span id="email_error" class="help-inline text-danger"><?= $errors->first('email') ?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">Full Name<sup class="text-danger">*</sup></label>
                                <?= Form::text('fullname',old('fullname'), ['class' => 'form-control allow_character', 'placeholder' => 'Enter fullname']); ?>
                                <span id="fullname_error" class="help-inline text-danger"><?= $errors->first('fullname') ?></span>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email">Phone<sup class="text-danger">*</sup></label>
                                <?= Form::text('mobile_no',old('mobile_no'), ['class' => 'form-control allow_number', 'placeholder' => 'Enter mobile no','maxlength' =>'10']); ?>
                                <span id="mobile_no_error" class="help-inline text-danger"><?= $errors->first('mobile_no') ?></span>
                            </div>

                        </div>
                        @if(Request::segment(3) == 'create')
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="email">Password<sup class="text-danger">*</sup></label>
                                <?= Form::text('password',old('password'), ['class' => 'form-control', 'placeholder' => 'Enter Password']); ?>
                                <span id="password_error" class="help-inline text-danger"><?= $errors->first('password') ?></span>
                            </div>
                                   <div class="col-md-6 form-group">
                                <label for="role">Role<sup class="text-danger">*</sup></label>
                                <?= Form::select('role',$roles,isset($user) ? $user->role_id : null,  ['class' => 'form-control select2','placeholder'=>'Select Role','id'=>'role' ]); ?>
                                <span id="role_error" class="help-inline text-danger"><?=$errors->first('roles')?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="is_active" class="w-100 mt-4">Is Active<sup class="text-danger">*</sup></label>
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
                        @else
                        <div class="row">
                            @if($subdomain[0] =='demo')
                            @if (in_array("admin.profile.changeotheradminpassword", $auth_current_permissions))
                            <div class="col-md-6 form-group">
                                <label for="role">Password</label>
                                <input id="newpassword" type="password" class="form-control" placeholder="New Password" name="password"   />
                                <span id="password_error" class="help-inline text-danger"><?= $errors->first('password') ?></span>
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label for="role">Confirm Password</label>
                                <input id="confirm_password" type="password" class="form-control" placeholder="Confirm New Password" name="confirm_password"  />
                                <span id="confirm_password_error" class="help-inline text-danger"><?= $errors->first('confirm_password') ?></span>
                                
                            </div>
                            @endif
                            @endif
                            
                            <div class="col-md-6 form-group">
                                <label for="role">Role<sup class="text-danger">*</sup></label>
                                <?= Form::select('role',$roles,isset($user) ? $user->role_id : null,  ['class' => 'form-control select2','placeholder'=>'Select Role','id'=>'role' ]); ?>
                                <span id="role_error" class="help-inline text-danger"><?=$errors->first('roles')?></span>
                            </div>
                             <div class="col-md-6 form-group">
                                <label for="is_active" class="w-100 mt-4">Is Active<sup class="text-danger">*</sup></label>
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
                        
                        @endif
                    </div>
                </div>
            </section>
            <div id="permission_add">
                    <section>
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
                                                               
                                                                <li id="<?php echo $single_user_perms->main_route; ?>" data-jstree='{"icon":"fa fa-caret-right blue"}'><?php echo $single_user_perms->route_name; ?></li>
                                                             <?php 
                                                                //if($previousModule!=$single_user_perms->group_name){  

                                                                    //$previousModule=$single_user_perms->group_name;
                                                                    ?> 
                                                                
                                                                <?php 
                                                                
                                                                    } ?>
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
             
            </div>
            <div class="float-right" style="margin-left: 72%">
                @if(Request::segment(3) == 'create')
                    <button type="button" id="save_new_2" class="btn btn-sm btn-primary save" title="Save & New"> Save & New</button>
                @endif
                <button type="button" id="save_exit_2" class="btn btn-sm btn-info save"title="Save & Close"> Save & Close</button>
                <a href="<?= route('adminmaster.index') ?>" role="button" class="btn btn-sm btn-light cancel" title="Cancel">Cancel</a>
            </div>
        </div>
    </div>
</div>