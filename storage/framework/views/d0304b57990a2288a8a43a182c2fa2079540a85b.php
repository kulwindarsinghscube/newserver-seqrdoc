<div class="container">
     <div class="float-right" style="margin-left: 72%;padding-bottom: 10px">
                <?php if(Request::segment(3) == 'create'): ?>
                    <button type="button" id="save_new_1" class="btn btn-sm btn-primary save" title="Save & New">Save & New</button>
                <?php endif; ?>
                <button type="button" id="save_exit_1" class="btn btn-sm btn-info save"title="Save & Close">Save & Close</button>
                <a href="<?= route('roles.index') ?>" role="button" class="btn btn-light cancel" title="Cancel">Cancel</a>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <section class="hk-sec-wrapper">
                <h5 class="hk-sec-title">Role Information</h5>
                <hr>
                <div class="row">
                    <div class="col-sm">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">Name of Role<sup class="text-danger">*</sup></label>
                                <?= Form::text('name',old('name'), ['class' => 'form-control', 'placeholder' => 'Enter Role']); ?>
                                <span id="name_error" class="help-inline text-danger"><?= $errors->first('name') ?></span>
                                <br>
                                <label for="status" class="w-100 mt-4">Status<sup class="text-danger">*</sup></label>
                                <div class="row">
                                    <div class="custom-control custom-radio radio-primary col-md-2 ml-15">
                                       <label class="container_radio">Active    
                                        <?php echo Form::radio('status',1, true,["class"=>"custom-control-input",'id'=>'customRadio1']); ?>

                                        <span class="checkmark" for=""></span>
                                    </label>
                                    </div>
                                    <div class="custom-control custom-radio radio-primary col-md-2 ml-15">
                                        <label class="container_radio">InActive  
                                        <?php echo Form::radio('status',0,null,["class"=>"custom-control-input",'id'=>"customRadio2"]); ?>

                                        <span class="checkmark" for=""></span>
                                    </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="description">Description of Role<sup class="text-danger">*</sup></label>
                                <?= Form::text('description',old('description'), ['class' => 'form-control', 'placeholder' => 'Enter Description']); ?>
                                <span id="description_error" class="help-inline text-danger"><?= $errors->first('description') ?></span>
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
                                                        <?php $__currentLoopData = $user_permission; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keys => $single_user_perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            

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
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
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
                <?php if(Request::segment(3) == 'create'): ?>
                    <button type="button" id="save_new_2" class="btn btn-sm btn-primary save" title="Save & New">Save & New</button>
                <?php endif; ?>
                <button type="button" id="save_exit_2" class="btn btn-sm btn-info save"title="Save & Close">Save & Close</button>
                <a href="<?= route('roles.index') ?>" role="button" class="btn btn-light cancel" title="Cancel">Cancel</a>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/role/form.blade.php ENDPATH**/ ?>