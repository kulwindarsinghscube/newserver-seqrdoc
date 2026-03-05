    <section>
        <div class="animated-checkbox">
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
                            @if(Request::segment(3) == 'create')
                                <?= Form::select('permission[]',$single_user_perms,old('permission',$role_current_permissions), array('multiple'=>true,'class' => 'multi-select user_permission_multi '.$keys)) ?>
                            @else
                                <?= Form::select('permission[]',$single_user_perms,old('permission',$role_current_permissions), array('multiple'=>true,'class' => 'multi-select user_permission_multi '.$keys)) ?>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            <center><span id="permission_error" class="text-danger text-center"><?= $errors->first('permission') ?></span></center>
        </div>
    </section>
<script type="text/javascript">
    
  $('.user_permission_multi').multiSelect();

        $('form').submit(function(){
            $('.overlay').show();
        });

        $("#selectall").click(function(){
            var is_checked = $(this).is(':checked');
            $(".sub_selection").prop('checked',is_checked);
            if (is_checked == true) {
                $('.user_permission_multi').multiSelect('select_all');
            }else{
                $('.user_permission_multi').multiSelect('deselect_all');
            }
        });

        $('.sub_selection').click(function(){
            var is_checked = $(this).is(':checked');
            var sub_selection_value = $(this).val();
            console.log(sub_selection_value);

            if (is_checked == true) {
                $('.'+sub_selection_value).multiSelect('select_all');
            }else{
                $('.'+sub_selection_value).multiSelect('deselect_all');
            }
        })

        $("input[type='checkbox'][class*='select_']").click(function(){
            var getClass = $(this).attr('class');
            var getSection = getClass.split(' ');
            var getSection = getSection[1].slice(7);
            var is_checked = $(this).is(':checked');
            $("."+getSection).prop('checked',is_checked);
        });

</script>