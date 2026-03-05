<?php $__env->startSection('content'); ?>
<!-- // use -->
<link rel="stylesheet" type="text/css" href="/backend/css/style.css">
    <?= Form::open(['id'=>'save_form']) ?> 
    <?php echo $__env->make('admin.role.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?= Form::close(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script type="text/javascript">
        $('.user_permission_multi').multiSelect();
        $('.user_permission_multi').multiSelect('deselect_all');

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

            if (is_checked == true) {
                $('.'+sub_selection_value).multiSelect('select_all');
            }else{
                $('.'+sub_selection_value).multiSelect('deselect_all');
            }
        })

        $('.save').click(function(){
            $('.preloader-it').show();
        });

        // role create
        $(".save").click(function(e){
            var btn_name = $(this).attr('title');
            var url = "<?= URL::route('roles.store') ?>";
            var method_type = 'POST';
            var token = "<?=csrf_token()?>";
              var selectedElmsIds = [];
            var selectedElms = $('#jstree').jstree('get_selected', true);

            $.each(selectedElms, function() {
                selectedElmsIds.push(this.id);
            });
            $('#save_form').ajaxSubmit({

                url: url,
                type: method_type,
                data: { "_token" : token,"permissions":selectedElmsIds },
                dataType: 'json',
                
                beforeSubmit : function()
                {
                     if(btn_name == 'Save & New')
                    {
                        $('#save_new_1').attr('disabled',true);
                        $('#save_new_1').html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                        $('#save_new_2').attr('disabled',true);
                        $('#save_new_2').html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                    }
                    else
                    {
                        $('#save_exit_1').attr('disabled',true);
                        $('#save_exit_1').html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                        $('#save_exit_2').attr('disabled',true);
                        $('#save_exit_2').html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                    }
                    $("[id$='_error']").empty();
                },
                
                success : function(resp)
                {             
                    $(".overlay").hide();
                    
                    if (resp.success == true) {
                        var action = resp.action;
                        toastr.success('Role successfully '+action);

                        if(btn_name == 'Save & New')
                        {
                            $('#save_new_1').attr('disabled',false);
                            $('#save_new_1').html('Save & New');
                            $('#save_new_2').attr('disabled',false);
                            $('#save_new_2').html('Save & New');
                            window.location.href = "<?=route('roles.create')?>";
                        }
                        else
                        {
                            $('#save_exit_1').attr('disabled',false);
                            $('#save_exit_1').html('Save & Close');
                            $('#save_exit_2').attr('disabled',false);
                            $('#save_exit_2').html('Save & Close');
                            window.location.href = "<?=route('roles.index')?>";
                        }
                    }
                   
                },
                error : function(respObj){    
                    toastr.error('Something are wrong');
                    $.each(respObj.responseJSON.errors, function(k,v){
                        $('#'+k+'_error').text(v);
                    });
                     if(btn_name == 'Save & New')
                    {
                        $('#save_new_1').attr('disabled',false);
                        $('#save_new_1').html('Save & New');
                        $('#save_new_2').attr('disabled',false);
                        $('#save_new_2').html('Save & New');
                    }
                    else
                    {
                        $('#save_exit_1').attr('disabled',false);
                        $('#save_exit_1').html('Save & Close');
                        $('#save_exit_2').attr('disabled',false);
                        $('#save_exit_2').html('Save & Close');
                    }
                    $(".overlay").hide();
                   
                    
                }
            });
        });
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
    </script> 
    <script  type="text/javascript">
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        /*$('#perms').slideUp();
        $('#jstree_q').hide();*/
        $('#perms').slideUp();
            $('#perms').slideDown();
            $("#jstree").jstree("deselect_all");
            $("#jstree").jstree("open_all");
            $("#jstree").jstree("open_node", $('#j_alltree'));
            $('#jstree_q').show();

    //create JSTREE
    $('#jstree').jstree({ "plugins" : [ "search","checkbox" ]});
    $('#jstree').on("changed.jstree", function (e, data) {
      $perms = data.selected;
  });



    //search filter
    var to = false;
    $('#jstree_q').keyup(function () {
        if(to) { clearTimeout(to); }
        to = setTimeout(function () {
            var v = $('#jstree_q').val();
        //$("#jstree").jstree("close_all");
        //$("#jstree").jstree("open_node", $('#j_alltree'));
        $('#jstree').jstree(true).search(v);
    }, 250);
    });

    //load permissions
   /* $('#roleId').on('change',function(){
        $("#jstree").jstree("close_all");
        var role_id = $(this).val();
        // alert(role_id);
        if(role_id !== ''){
            $('#perms').slideUp();
            $('#perms').slideDown();
            $("#jstree").jstree("deselect_all");
            $("#jstree").jstree("open_all");

            $.post('',
            {
                'role_id':role_id
            },function(response){
                for (var i = 0; i < response.length; i++) {
                    $('#jstree').jstree("select_node", response[i], true);
                    $("#jstree").jstree("close_all");
                    $("#jstree").jstree("open_node", $('#j_alltree'));
                    $('#jstree_q').show();
                }
            },'JSON');
        }
        else{
            $('#perms').slideUp();
            $('#jstree_q').hide();
        }
    });*/


    //save permission
   /* $('#updatePerms').click(function(){
        var roleId = $('#roleId').val();
        var selectedElmsIds = [];
        var selectedElms = $('#jstree').jstree('get_selected', true);

        $.each(selectedElms, function() {
            selectedElmsIds.push(this.id);
        });

      //  console.log(selectedElmsIds);
      $.post('',
      {
        'type': 'update',
        'data':selectedElmsIds,
        'role_id':roleId
    },function(data){
        if(data.status == '1')
           notification('success',data.message);
       else
           notification('error',data.message);
   },'JSON');
  });
*/

    //toggle tree
    $('#toggle-tree').click(function(){
        if($(this).hasClass('open_me')){
            $('#jstree').jstree('open_all');
            $(this).removeClass('open_me');
            $(this).addClass('close_me');
            $('#toggle-tree-button').addClass('fa-caret-up');
            $('#toggle-tree-button').removeClass('fa-caret-down');
        }else{
            $('#jstree').jstree('close_all');
            $(this).removeClass('close_me');
            $(this).addClass('open_me');
            $('#toggle-tree-button').addClass('fa-caret-down');
            $('#toggle-tree-button').removeClass('fa-caret-up');
            $("#jstree").jstree("open_node", $('#j_alltree'));
        }
    });
    

});
</script>  
<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>
<style>
/* The container */
.container_radio {
  display: block;
  position: relative;
  padding-left: 35px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 15px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Hide the browser's default radio button */
.container_radio input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

/* Create a custom radio button */
.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #eee;
  border-radius: 50%;
}

/* On mouse-over, add a grey background color */
.container_radio:hover input ~ .checkmark {
  background-color: #ccc;
}

/* When the radio button is checked, add a blue background */
.container_radio input:checked ~ .checkmark {
  background-color: #2196F3;
}

/* Create the indicator (the dot/circle - hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the indicator (dot/circle) when checked */
.container_radio input:checked ~ .checkmark:after {
  display: block;
}

/* Style the indicator (dot/circle) */
.container_radio .checkmark:after {
    top: 6px;
    left: 6px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: white;
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/role/create.blade.php ENDPATH**/ ?>