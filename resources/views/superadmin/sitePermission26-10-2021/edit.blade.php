@extends('superadmin.layout.layout')
@section('content')
<!-- // use -->
 <link rel="stylesheet" type="text/css" href="/backend/css/style.css">
   <?= Form::model($role_details,['role'=>'form','class'=>'m-0','id'=>'save_form']) ?>
    @include('superadmin.sitePermission.form')
    <?= Form::close(); ?>
@stop

@section('script')
    <script type="text/javascript">

          function isNumberKey(evt)
          {
              var charCode = (evt.which) ? evt.which : event.keyCode
              if (charCode > 31 && (charCode < 48 || charCode > 57))
                  return false;

              return true;
          }
          $('#datepicker').datepicker({
            
            dateFormat: "yy-mm-dd",
            minDate: '0'
          });


          $('#datepicker2').datepicker({
            dateFormat: "yy-mm-dd",
            minDate: '0'
          });
        // console.log('hello');

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
            // console.log("gg");
            var btn_name = $(this).attr('title');
            e.preventDefault();
            var id = '<?= $id ?>';
            var update_url = "<?= URL::route('website-permission.update',array(':id')) ?>";
            update_url = update_url.replace(':id',id);
            var method_type ="post";
            var token = "<?=csrf_token()?>";

            $('#save_form').ajaxSubmit({
                url: update_url,
                type: method_type,
                data: { "_token" : token,"id":id },
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