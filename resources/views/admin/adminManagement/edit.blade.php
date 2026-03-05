@extends('admin.layout.layout')
@section('content')
<!-- // use -->
 <link rel="stylesheet" type="text/css" href="/backend/css/style.css">
     <?= Form::model($user,['role'=>'form','class'=>'m-0','id'=>'save_form']) ?>
     @include('admin.adminManagement.form')
     <?= Form::close(); ?>
@stop

@section('script')
    <script type="text/javascript">
        $('.select2').select2();

        $('.user_permission_multi').multiSelect();

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
            $('.overlay').show();
        });

        // role create
        $(".save").click(function(e){
            var btn_name = $(this).attr('title');
            e.preventDefault();
            var id = '<?= $id?>';
            var update_url = "<?= URL::route('adminmaster.update',array(':id')) ?>";
            update_url = update_url.replace(':id',id);
            var method_type = 'post';
            var token = "<?=csrf_token()?>";
              var selectedElmsIds = [];
        var selectedElms = $('#jstree').jstree('get_selected', true);

        $.each(selectedElms, function() {
            selectedElmsIds.push(this.id);
        });


            $('#save_form').ajaxSubmit({
                url: update_url,
                type: method_type,
                data: { "_token" : token,"id":id,"permissions":selectedElmsIds  },
                dataType: 'json',
                
                beforeSubmit : function()
                {
                    if(btn_name == 'Save & Close')
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
                        toastr.success('Admin successfully '+action);

                        if(btn_name == 'Save & Close')
                        {
                            $('#save_exit_1').attr('disabled',false);
                            $('#save_exit_1').html('Save & Close');
                            $('#save_exit_2').attr('disabled',false);
                            $('#save_exit_2').html('Save & Close');
                            window.location.href = "<?=route('adminmaster.index')?>";
                        }
                    }
                   
                },
                error : function(respObj){    

                    toastr.error('Something are wrong');
                    $.each(respObj.responseJSON.errors, function(k,v){
                        $('#'+k+'_error').text(v);
                    });

                    if(btn_name == 'Save & Close')
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

        $('#role').change(function(){
            var val = $(this).val();
            var url = "<?= URL::route('admin-master.role.get')?>";
            var method_type = 'GET';
            var token = "<?=csrf_token()?>";
            $.ajax({
                type:method_type,
                url:url,
                data:{'id': val,'_token': token},
                dataType:'json',
                success: function(respObj) {
                    if(respObj.success == true)
                    {
                       $("#jstree").jstree("deselect_all");
                      //var branch= Object.values(JSON.parse(permissions));
                     // var branch= JSON.parse(respObj.data);

                        var branch= Object.values(respObj.data);
                       // var branch=branch.join(", ");
                       // var branch= Object.values(JSON.parse(respObj.data));
                      //console.log(branch);
                        $('#jstree').jstree("select_node", branch, true);
                          $("#jstree").jstree("close_all");
                          $("#jstree").jstree("open_node", $('#j_alltree'));
                          $('#jstree_q').show();
                        //$('#permission_add').html(respObj.html);
                    }
                }
            });
        });
  // clear Admin model data   
$(".allow_number").keypress(function(h){
    var keyCode =h.which ? h.which : h.keyCode
       if (!(keyCode >= 48 && keyCode <= 57)) {
             return !1;
           }
 });
// allow only character
$(".allow_character").keypress(function(h) {
  
    var keyCode=h.which ? h.which :h.keyCode;
    if(!(keyCode>=97 && keyCode <=122) && !(keyCode>=65 && keyCode <=90) && !(keyCode==32))
    {
        return !1;
    } 
    
});       
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

 

     var permissions='<?php  echo json_encode($role_current_permissions); ?>';
    //var permissions='<?php  //echo $role_current_permissions_str; ?>';
    
   // console.log(permissions+' Array');
  
  $(document).ready(function(){
     
              if(document.readyState === 'ready' || document.readyState === 'complete') {
  //doSomething();
 selectBranch(permissions);
  //console.log('Loaded1');
} else {
  document.onreadystatechange = function () {
    if (document.readyState == "complete") {
      //doSomething();
      selectBranch(permissions)
      //console.log('Loaded2');
      //selectBranch(["admin.dashboard","admin.profile.changepassword","adminmaster.index","adminmaster.create","adminmaster.edit","adminmaster.destroy"]);
      /*setTimeout(function () {
       
            selectBranch(permissions)
        
    }, 5000);*/
    }
  }
}




  });
  
 function selectBranch(permissions){
  
   // var branch=[permissions];
   //var branch=permissions.toString();
    //var branch=permissions.join(", ");
    //console.log(permissions);
    //var array = ["123", "456", "789"],
    //result = branch.map(a => JSON.stringify(a)).join();
    
//console.log(result);
//var branch= JSON.parse(permissions);
var branch= Object.values(JSON.parse(permissions));
//console.log(branch.length);
  $('#jstree').jstree("select_node", branch, true);
                      $("#jstree").jstree("close_all");
    $("#jstree").jstree("open_node", $('#j_alltree'));
    $('#jstree_q').show();
   // console.log('selecteBranch11');
/*for (var i = 0; i < branch.length; i++) {
  console.log(i+' '+branch[i]);
                     $('#jstree').jstree("select_node", branch[i], true);
                      $("#jstree").jstree("close_all");
    $("#jstree").jstree("open_node", $('#j_alltree'));
    $('#jstree_q').show();
    console.log('selecteBranch11');
                }
*/
   
   
}
</script>
@stop
@section('style')
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
@stop