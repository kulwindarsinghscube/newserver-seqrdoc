@extends('superadmin.layout.layout')
@section('content')
<!-- // use -->
 <link rel="stylesheet" type="text/css" href="/backend/css/style.css">
   <?= Form::model($role_details,['role'=>'form','class'=>'m-0','id'=>'save_form']) ?>
    @include('superadmin.sitePermission.form')
    <?= Form::close(); ?>
@stop
<?php 
//print_r($role_current_permissions);

?>
@section('script')
    <script type="text/javascript">
          $('#country').change (function () {
            if($('select#country').val()=="India"){
                $('#stateDiv').show();
            }else{
                $('#stateDiv').hide();
            }
        });

          var country='<?php echo $role_details["country"]; ?>';
          $('#country').val(country);
          if(country=="India"){
            $('#stateDiv').show();
            var state='<?php echo $role_details["state"]; ?>';
            $('#state').val(state);
          }else{
                $('#stateDiv').hide();
            }

          function isNumberKey(evt)
          {
              var charCode = (evt.which) ? evt.which : event.keyCode
              if (charCode > 31 && (charCode < 48 || charCode > 57))
                  return false;

              return true;
          }
          $('#datepicker').datepicker({
            
            dateFormat: "dd-mm-yy",
            minDate: '0'
          });


          $('#datepicker2').datepicker({
            dateFormat: "dd-mm-yy",
            minDate: '0'
          });


      var url="{{ URL::route('superadmin.getfilesdetails') }}";
      var token="{{ csrf_token() }}";
      var method_type="post";
      var site_url='<?php echo $role_details->site_url; ?>';
      if(site_url!=""&&site_url!=null){
        
            $.post(url,{'_token':token,'site_url':site_url,'type':'pdf_file'}, function(data) {
              if(data.success==true){

                  //console.log(data.data.file_count_pdf_file);
                  $('#file_count_pdf_file').html(data.data.file_count_pdf_file);
                  $('#file_size_pdf_file').html(data.data.file_size_pdf_file);/*
                  $('#file_count_pdf_file_inactive').html(data.data.file_count_pdf_file_inactive);
                  $('#file_size_pdf_file_inactive').html(data.data.file_size_pdf_file_inactive);
                  $('#file_count_pdf_file_live').html(data.data.file_count_pdf_file_live);
                  $('#file_size_pdf_file_live').html(data.data.file_size_pdf_file_live);
                  $('#file_count_pdf_file_preview').html(data.data.file_count_pdf_file_preview);
                  $('#file_size_pdf_file_preview').html(data.data.file_size_pdf_file_preview);
                  $('#file_count_pdf_file_templates').html(data.data.file_count_pdf_file_templates);
                  $('#file_size_pdf_file_templates').html(data.data.file_size_pdf_file_templates);*/

                  $('#aws_file_count_pdf_file').html(data.data.aws_file_count_pdf_file);
                  $('#aws_file_size_pdf_file').html(data.data.aws_file_size_pdf_file);


              //   $('#fileCountLoader').hide();
              //     $('#fileDetails').show();

              // }else{
              //   $('#fileDetails').show();
              //   $('#fileCountLoader').hide();
                //toastr.error(data.msg);
              }
          });


            $.post(url,{'_token':token,'site_url':site_url,'type':'pdf_inactive'}, function(data) {
              if(data.success==true){

                  //console.log(data.data.file_count_pdf_file);
                 /* $('#file_count_pdf_file').html(data.data.file_count_pdf_file);
                  $('#file_size_pdf_file').html(data.data.file_size_pdf_file);*/
                  $('#file_count_pdf_file_inactive').html(data.data.file_count_pdf_file_inactive);
                  $('#file_size_pdf_file_inactive').html(data.data.file_size_pdf_file_inactive);/*
                  $('#file_count_pdf_file_live').html(data.data.file_count_pdf_file_live);
                  $('#file_size_pdf_file_live').html(data.data.file_size_pdf_file_live);
                  $('#file_count_pdf_file_preview').html(data.data.file_count_pdf_file_preview);
                  $('#file_size_pdf_file_preview').html(data.data.file_size_pdf_file_preview);
                  $('#file_count_pdf_file_templates').html(data.data.file_count_pdf_file_templates);
                  $('#file_size_pdf_file_templates').html(data.data.file_size_pdf_file_templates);*/

                  $('#aws_file_count_pdf_file_inactive').html(data.data.aws_file_count_pdf_file_inactive);
                  $('#aws_file_size_pdf_file_inactive').html(data.data.aws_file_size_pdf_file_inactive);
              //   $('#fileCountLoader').hide();
              //     $('#fileDetails').show();

              // }else{
              //   $('#fileDetails').show();
              //   $('#fileCountLoader').hide();
                //toastr.error(data.msg);
              }
          });


            $.post(url,{'_token':token,'site_url':site_url,'type':'pdf_live'}, function(data) {
              if(data.success==true){

                  //console.log(data.data.file_count_pdf_file);
                /*  $('#file_count_pdf_file').html(data.data.file_count_pdf_file);
                  $('#file_size_pdf_file').html(data.data.file_size_pdf_file);
                  $('#file_count_pdf_file_inactive').html(data.data.file_count_pdf_file_inactive);
                  $('#file_size_pdf_file_inactive').html(data.data.file_size_pdf_file_inactive);*/
                  $('#file_count_pdf_file_live').html(data.data.file_count_pdf_file_live);
                  $('#file_size_pdf_file_live').html(data.data.file_size_pdf_file_live);/*
                  $('#file_count_pdf_file_preview').html(data.data.file_count_pdf_file_preview);
                  $('#file_size_pdf_file_preview').html(data.data.file_size_pdf_file_preview);
                  $('#file_count_pdf_file_templates').html(data.data.file_count_pdf_file_templates);
                  $('#file_size_pdf_file_templates').html(data.data.file_size_pdf_file_templates);*/

                  $('#aws_file_count_pdf_file_live').html(data.data.aws_file_count_pdf_file_live);
                  $('#aws_file_size_pdf_file_live').html(data.data.aws_file_size_pdf_file_live);

              //   $('#fileCountLoader').hide();
              //     $('#fileDetails').show();

              // }else{
              //   $('#fileDetails').show();
              //   $('#fileCountLoader').hide();
                //toastr.error(data.msg);
              }
          });

            $.post(url,{'_token':token,'site_url':site_url,'type':'pdf_preview'}, function(data) {
              if(data.success==true){

                  //console.log(data.data.file_count_pdf_file);
                  /*$('#file_count_pdf_file').html(data.data.file_count_pdf_file);
                  $('#file_size_pdf_file').html(data.data.file_size_pdf_file);
                  $('#file_count_pdf_file_inactive').html(data.data.file_count_pdf_file_inactive);
                  $('#file_size_pdf_file_inactive').html(data.data.file_size_pdf_file_inactive);
                  $('#file_count_pdf_file_live').html(data.data.file_count_pdf_file_live);
                  $('#file_size_pdf_file_live').html(data.data.file_size_pdf_file_live);*/
                  $('#file_count_pdf_file_preview').html(data.data.file_count_pdf_file_preview);
                  $('#file_size_pdf_file_preview').html(data.data.file_size_pdf_file_preview);
                  /*$('#file_count_pdf_file_templates').html(data.data.file_count_pdf_file_templates);
                  $('#file_size_pdf_file_templates').html(data.data.file_size_pdf_file_templates);*/

                  $('#aws_file_count_pdf_file_preview').html(data.data.aws_file_count_pdf_file_preview);
                  $('#aws_file_size_pdf_file_preview').html(data.data.aws_file_size_pdf_file_preview);

                /*$('#fileCountLoader').hide();
                  $('#fileDetails').show();*/

              //}else{
               /* $('#fileDetails').show();
                $('#fileCountLoader').hide();*/
                //toastr.error(data.msg);
              }
          });


            $.post(url,{'_token':token,'site_url':site_url,'type':'templates'}, function(data) {
              if(data.success==true){

                  //console.log(data.data.file_count_pdf_file);
                 /* $('#file_count_pdf_file').html(data.data.file_count_pdf_file);
                  $('#file_size_pdf_file').html(data.data.file_size_pdf_file);
                  $('#file_count_pdf_file_inactive').html(data.data.file_count_pdf_file_inactive);
                  $('#file_size_pdf_file_inactive').html(data.data.file_size_pdf_file_inactive);
                  $('#file_count_pdf_file_live').html(data.data.file_count_pdf_file_live);
                  $('#file_size_pdf_file_live').html(data.data.file_size_pdf_file_live);
                  $('#file_count_pdf_file_preview').html(data.data.file_count_pdf_file_preview);
                  $('#file_size_pdf_file_preview').html(data.data.file_size_pdf_file_preview);*/
                  $('#file_count_pdf_file_templates').html(data.data.file_count_pdf_file_templates);
                  $('#file_size_pdf_file_templates').html(data.data.file_size_pdf_file_templates);

                  $('#aws_file_count_pdf_file_templates').html(data.data.aws_file_count_pdf_file_templates);
                  $('#aws_file_size_pdf_file_templates').html(data.data.aws_file_size_pdf_file_templates);


              //   $('#fileCountLoader').hide();
              //     $('#fileDetails').show();

              // }else{
              //   $('#fileDetails').show();
              //   $('#fileCountLoader').hide();
                //toastr.error(data.msg);
              }
          });

            $.post(url,{'_token':token,'site_url':site_url,'type':'storage_log'}, function(data) {
                if(data.success==true){
                    // console.log(data.data);
                    $('#storageSize').html(data.data.storage_log_size);
                    $('#storageLatestFileName').html("Latest log file: "+data.data.storage_log_latest_file+" (For All Instance)");
                }
            });
            
            $.post(url,{'_token':token,'site_url':site_url,'type':'database_log'}, function(data) {
                if(data.success==true){
                    // console.log(data.data);
                    $('#database_size').html("Size: "+data.data.database_size);
                    $('#database_table_count').html("Total Table Count: "+data.data.database_table_count);
                    
                }
            });


        }




         
     
        // console.log('hello');

       /* $('.user_permission_multi').multiSelect();

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
        })*/

        $('.save').click(function(){
            $('.overlay').show();
        });

        // role create
        $(".save").click(function(e){
            // console.log("gg");
            var btn_name = $(this).attr('title');
            e.preventDefault();
            var id = '<?= $id ?>';
            var update_url = "<?= URL::route('website-permission.update',array(':id')) ?>";
            update_url = update_url.replace(':id',id);
            var method_type ="post";
            var token = "<?=csrf_token()?>";
             var selectedElmsIds = [];
        var selectedElms = $('#jstree').jstree('get_selected', true);

        $.each(selectedElms, function() {
            selectedElmsIds.push(this.id);
        });
            $('#save_form').ajaxSubmit({
                url: update_url,
                type: method_type,
                data: { "_token" : token,"id":id,"permissions":selectedElmsIds},
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
                        toastr.success('Site Permission '+action);
 
                        window.location.href = "<?=route('website-permission.index')?>";
                    }else{
                        
                        toastr.error(resp.message);
                        $('#save_new_1').attr('disabled',false);
                        $('#save_new_1').html('Save & New');
                        $('#save_new_2').attr('disabled',false);
                        $('#save_new_2').html('Save & New');   
                    }
                   
                },
                error : function(resobj){    
                   
                    toastr.error('Something are wrong');
                    $.each(resobj.responseJSON.errors, function(k,v){
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
    
    //console.log(permissions+' Array');
  
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
//console.log(branch);
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

@section('script')
<script type="text/javascript">


</script>
@stop