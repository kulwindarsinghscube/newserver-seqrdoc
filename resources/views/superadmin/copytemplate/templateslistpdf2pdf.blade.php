@extends('superadmin.layout.layout')
@section('style')
<style type="text/css">

#example_length label{
  display:none;
}
.help-inline{
  color:red;
  font-weight:normal;
}

.breadcrumb{
  background:#fff;
}

.breadcrumb a{
  color:#666;
}

.breadcrumb a:hover{
  text-decoration:none;
  color:#222;
}

.loader{
  display: table;
    background: rgba(0,0,0,0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    text-align: center;
    vertical-align: middle;
}

.loader-content{
  display:table-cell;
  vertical-align: middle;
  color:#fff;
}
.success2{
  border-left:3px solid #5CB85C;
}
.danger2{
  border-left:3px solid #D9534F;
}

#example td{
  word-break: break-all;
  padding:10px;
}

.nav-pills>li.active>a, .nav-pills>li.active>a:focus{
  background:#0052CC;
  color:#fff;
  border:1px solid #0052CC;
}

.nav-pills>li.active>a:hover, .nav-pills>li>a:focus, .nav-pills>li>a:hover
{
  background:#fff;
  background:#ddd;
  border-radius:0;
  padding:10px 20px;
  color:#333;
  border-radius:2px;
  border:1px solid #ddd;
}

.nav-pills>li>a, .nav-pills>li>a
{
  background:#fff;
  color:#aaa;
  border-radius:0;
  padding:10px 20px;
  border-radius:2px;
  margin-bottom:20px;
  border:1px solid #ddd;
}

#example_length label{
  display:none;
}

.active .success{
  background:#5CB85C !important;
  border:1px solid #5CB85C !important;
  color:#fff !important;
}

.active .failed{
  background:#D9534F !important;
  border:1px solid #D9534F !important;
  color:#fff !important;
}
</style>
@stop
@section('content')
   <div class="container">
       <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header"><i class="fa fa-fw fa-list-ul"></i>Templates : <?php echo $instance; ?>
                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
                </h1>   
            </div>
        </div>
        <div class="">
           
            <div class="col-xs-12">
                <table id="master_table" class="table table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Template Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="modal fade" id="copyTemplateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Copy Template : <span id="copyTemplateName"></span></h4>
              </div>
              <div class="modal-body">
           
                <form method="post" action="<?=route('copy-template.copy-templatepdf2pdf')?>" enctype="multipart/form-data" id="updfilefrm"> 
                  <div class="form-group">
                   
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <input type="hidden" name="template_id" id="template_id">
                 
                  </div>
                  <div class="form-group" id="opt1">
                  <label for="opt_pg">Select Instance </label>
                  <select name="dest_instance" id="dest_instance" class="form-control" data-rule-required="true">
                    <option value="" selected>Select</option>
                    <?php foreach ($instancesListArray as $readInstance) {
                       if($instance!=$readInstance['site_url']){

                        echo '<option value="'.$readInstance['site_url'].'">'.$readInstance['site_url'].'</option>';
                      }
                    } ?>
                    
                  </select>
                  <span id="opt_pg_error" class="help-inline text-danger"><?=$errors->first('opt_pg')?></span>
                </div>
                  <div class="form-group clearfix">
                    <div id="upload_btn">
                      <a href="#" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-7" id="btn_copy_back" onclick="closeModel();" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
                     
                      <button type="button" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="btn_copy_template"><i class="fa fa-files-o"></i> Copy</button>
                    </div>
                  </div>
                </form>

                
              </div>
            </div>
          </div>
        </div>
    </div>
@stop
@section('script')

    <!-- AJAX FOR DATA TABLE  START-->
<script type="text/javascript">
          // Send Ajax Request to delete Record      
function deletepath(fm_id)
{
  var delete_path="<?=URL::route('website-permission.destroy',array(':id')) ?>";
  delete_path=delete_path.replace(':id',fm_id);
  var token="{{ csrf_token() }}";
  var method_type="get";

  bootbox.confirm("<div style='color:grey;font-size:14px;font-weight:8px;'>1) Removes entry from site table. <br>2) Removes storage space from <span style='color:red;'>AWS</span> for particular instance. <br>3) Deletes public folder.<br> 4) Deletes database. </div><br><h4>Are you sure you want to delete? </h4>",function(result){  
       if(result)
       {
            $.ajax({
               url:delete_path,
               type:method_type,
               data:{'_token':token},
               success:function(data){  
                 if(data.success==true)
                 {
                     toastr.success('Site Deleted Successfully');
                     oTable.ajax.reload();
                 }
               },
            });
       }

  });
}


  var url = "<?=route('copy-template.viewtemplateslistpdf2pdf',['instance'=>$instance])?>";     

  var oTable = $('#master_table').DataTable({
      "bProcessing": false,
      "bServerSide": true,
      "autoWidth": true,
      "aaSorting": [
          [3, "desc"]
      ],
      lengthMenu: [
          [ 50, 100,200,500],
          [ '50','100','200','500']
      ],
      "sAjaxSource": url,
      "aoColumns": [
          {mData: "rownum", bSortable:false,sWidth: "5%"},
          { "mData": "template_name",sWidth: "10%",bSortable: true,bVisible:true},
          { "mData": "publish",sWidth: "10%",bSortable: true,bVisible:true,
            mRender: function(v, t, o) {

                
                  if(o['publish']==1){
                    return 'Active';
                  }else{
                    return 'In-Active';
                  }
              }
          },
          {
              mData:'id',
              bSortable: false,
              sWidth: "10%",
              sClass: "text-center",
              mRender: function(v, t, o) {

                  var template_id= o['id'];
                  var actual_template_name= o['template_name'];
                  var act_html = '<span class="btn copy-template" data-template_id="'+template_id+'" data-template_name="'+actual_template_name+'" datatitle="Copy Template"><i class="fa fa-files-o fa-lg green"></i></span>';
                  return act_html;
              }
          }
      ],
    
  });
  oTable.on('draw',function(){
    
    $(".loader").addClass('hidden');
  });             
          

oTable.on('click','.copy-template',function(e){
  $template_id = $(this).data('template_id');
  $template_name = $(this).data('template_name');
 $('#template_id').val($template_id);
  $('#copyTemplateName').html($template_name);
  $('#instance').val('');
  $('#copyTemplateModal').modal('show');
  
});


$('#btn_copy_template').click(function(){
 var url="{{ URL::route('copy-template.copy-templatepdf2pdf') }}";
      var token="{{ csrf_token() }}";
      var method_type="post";
      var id=$('#template_id').val();
      var instance=$('#dest_instance :selected').val();
      var source_instance='<?php echo $instance; ?>';
      if(instance!=""&&instance!=null){
        bootbox.confirm("Are you sure you want to copy?",function(result){  
          if(result){
            $.post(url,{'_token':token,'template_id':id,'instance':instance,"source_instance":source_instance}, function(data) {
              if(data.success==true){
                  toastr.success(data.msg);
                  $('#copyTemplateModal').modal('hide');
                 oTable.ajax.reload();
              }else{
                toastr.error(data.msg);
              }
          });
          }
        });
      }else{
        toastr.error('Please select instance.');
      }
});

    </script>
    <!-- AJAX FOR DATA TABLE  END--
@stop



