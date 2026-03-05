<?php $__env->startSection('content'); ?>
<?php 
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        ?>
<div class="container">
  <div class="col-xs-12">
    <div class="clearfix">
     <div id="">
       <div class="container-fluid">
        <div class="row">
         <div class="col-lg-12">
          <h1 class="page-header"><i class="fa fa-fw fa-foursquare"></i> Font master
             <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('fontmaster')); ?></ol>
		<i class="fa fa-info-circle iconModalCss" title="User Manual" id="fontModalClick"></i>
          </h1>
        </div>
      </div>
      <div class="hello">
       <ul class="nav nav-pills" id="pills-filter">
         <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Font </a></li>
         <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Font</a></li>
         <?php if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.store')): ?>
         <?php if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.store')): ?>
         <li style="float: right;">
           <button class="btn btn-theme" id="addUser" onclick="fontMaster_data()"><i class="fa fa-plus"></i> Add font file</button>	
         </li>
         <?php endif; ?>
         <?php endif; ?>
      
       </ul>
       <table id="example" class="table table-hover display" cellspacing="0" width="100%">
        <thead>
         <tr>
          <th>#</th>
          <th>Font Name</th>
          <th>Font Normal</th>
          <th>Font Bold</th>
          <th>Font Italic</th>
          <th>Font Bold Italic</th>
          
          <?php
          //font changes
          if(!in_array($subdomain[0], $fontMasterExceptions)){ ?>
          <th>Action</th>
          <?php }
          ?>
          <th></th>
        </tr>
      </thead>
      <tfoot>
      </tfoot>
    </table>
  </div>
</div>
</div>
</div>
</div>
</div>
<!-- Model fontMaster -->
<?php echo Form::open(array('id'=>'fontMatser_data','files'=>true)); ?> 
  <?php echo $__env->make('admin.fontMaster.model', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo Form::close(); ?>

<!-- End Model fontMaster -->
<div id="edit_form">
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script type="text/javascript">
           
  	// Send Ajax Request to delete Record Soft Delete     
  function deletepath(fm_id)
  {
  	var delete_path="<?=URL::route('fontmaster.destroy',array(':id')) ?>";
  	delete_path=delete_path.replace(':id',fm_id);
  	var token="<?php echo e(csrf_token()); ?>";
  	var method_type="get";

  	bootbox.confirm("Are you sure you want to delete?",function(result){	
         if(result)
         {
              $.ajax({
                 url  : delete_path,
                 type : method_type,
                 data : {'_token':token},
                 success:function(data){  
                   if(data.success==true)
                   {
                       toastr.success('Font Deleted successfully');
                       oTable.ajax.reload();
                   }
                 },
              });
         }

    });
  }
  
     // send Ajax Request to featch data and show model 
      function edit(id)
       {
          $('#UserSave').hide();
          $('#updateSave').show();
          var edit_path="<?=URL::route('fontmaster.edit',array(':id')) ?>";
          edit_path=edit_path.replace(':id',id);
          var method_type="GET";
          var token="<?php echo e(csrf_token()); ?>";
          $.ajax({
                
                url  : edit_path,
                type : method_type,
                data : {'_token':token},
                beforeSend:function(){
                 
                },
                success:function(data)
                {
                   $('#addUsr').modal('show');
                   $("#font_id").val(data.id);
                   $('#font_name').val(data.font_name); 
                   $("#upload_font_N").text(data.font_filename_N);
                   $("#upload_font_B").text(data.font_filename_B);
                   $("#upload_font_I").text(data.font_filename_I);
                   $("#upload_font_BI").text(data.font_filename_BI);

                   if(data.status==1)
                   {
                      $("#opt_status").prop("selectedIndex", 1);
                   }
                   else
                   {
                      $("#opt_status").prop("selectedIndex", 2);
                   }
                },
          
          });  
       }
      
    // Update FontMaster 
    $(".update_save").click(function(event) {
          
         var font_id=$("#font_id").val();
         event.preventDefault();
         var update_path="<?= URL::route('fontmaster.updates',array(':id')) ?>";
         update_path=update_path.replace(':id',font_id);
         var token="<?php echo e(csrf_token()); ?>";
         var type="post";

         $("#fontMatser_data").ajaxSubmit({
               url  : update_path,
               type : type,
               data : {'_token':token},
               beforeSubmit:function(){
                 $("#addUsr").find('span').text('').end();
                 $(".loadupdate").addClass('fa fa-spinner fa-spin');
                 $(".update_save").attr('disabled','disabled');
               },
               success:function(data)
               {
                 if(data.validEx)
                 {
                     $(".save").removeAttr('disabled');
                     $.each(data.validEx,function(k,v) {
                        console.log(k);
                        $("#"+k+'_error').text(v);
                     });
                     $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                     $(".update_save").removeAttr('disabled');
                 }
                 if(data.success==true)
                 {
                   $('#addUsr').modal('hide');
                   $('#fontMatser_data').trigger("reset");
                   toastr.success('Font successfully added');
                   oTable.ajax.reload();
                   $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                   $(".update_save").removeAttr('disabled');
                   
                 }
               },
               error:function(resobj)
               {
                 $(".update_save").removeAttr('disabled');
                 $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                 $.each(resobj.responseJSON.errors, function(k,v){
                     $('#'+k+'_error').text(v);
                  });
               },
         });      
    });
    

    //Send Ajax Request to Create FontMaster
    function fontMaster_data()
    {
       $('#UserSave').show();
       $('#updateSave').hide();
       $('#addUsr').modal('show');
       $('.save').click(function(event) {
       $(".save").attr('disabled','disabled');   
         event.preventDefault();
         var create_path="<?= URL::route('fontmaster.store') ?>";
         var token="<?php echo e(csrf_token()); ?>";
         var method_type="post";

         $("#fontMatser_data").ajaxSubmit({
               url  : create_path,
               type : method_type,
               data : {'_token':token},
               beforeSubmit:function(){
                 $("#addUsr").find('span').text('').end();
                 $(".loadsave").addClass('fa fa-spinner fa-spin');
               },
               success:function(data)
               {
                if(data.success==false)
                {
                    if(data.fontFile){
                     $(".save").removeAttr('disabled');
                      toastr.error(data.fontFile);
                      $(".loadsave").removeClass('fa fa-spinner fa-spin');
                    }  
                   if(data.validEx)
                    {
                      $(".save").removeAttr('disabled');
                      $.each(data.validEx,function(k,v) {
                        console.log(k);
                        $("#"+k+'_error').text(v);
                      });
                      $(".loadsave").removeClass('fa fa-spinner fa-spin');
                    }
                }
                 if(data.success==true)
                 {
                 	 $('#addUsr').modal('hide');
                 	 $('#fontMatser_data').trigger("reset");
                    $(".save").removeAttr('disabled');
                 	 toastr.success('Font successfully added');
                   oTable.ajax.reload();
                   $(".loadsave").removeClass('fa fa-spinner fa-spin');
                 }
               },
               error:function(resobj)
               {
                $(".loadsave").removeClass('fa fa-spinner fa-spin');
                $(".save").removeAttr('disabled');
                 $.each(resobj.responseJSON.errors, function(k,v){
                     $('#'+k+'_error').text(v);
                  });
               },
         });     	
      });

  }

   // datatable
     var oTable = $('#example').DataTable( {
       'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
       "bProcessing": false,
       "bServerSide": true,
       "autoWidth": true,

       "aaSorting": [
          [6, "desc"]
          ],
       "sAjaxSource":"<?= route('fontmaster.index',['status'=>1]) ?>",
       "aoColumns":[
            {mData: "rownum", bSortable:false,"sClass": "text-center"},
            {mData: "font_name",bSortable:true,"sClass": "text-center"},
            {mData: "font_filename_N",bSortable:true,"sClass": "text-center"},
            {mData: "font_filename_B",bSortable:true,"sClass": "text-center"},
            {mData: "font_filename_I",bSortable:true,"sClass": "text-center"},
            {mData: "font_filename_BI",bSortable:true,"sClass": "text-center"},
            <?php
          //font changes
          if(!in_array($subdomain[0], $fontMasterExceptions)){ ?>
            {
              mData:"id",
              bSortable:false,

              mRender:function(v, t, o){
                var act_html;
            
                
                       act_html =  
                        '<?php if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.edit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.edit')): ?><a onclick="edit('+o['id']+')"><i class="fa fa-edit fa-lg green"></i></a><?php endif; ?> <?php endif; ?>';
                       
                        act_html +='<?php if(App\Helpers\SitePermissionCheck::isPermitted('fontmaster.destroy')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('fontmaster.destroy')): ?>&nbsp;&nbsp;<a onclick="deletepath('+o['id']+')"><i class="fa fa-trash fa-lg red"></i></a><?php endif; ?> <?php endif; ?>';
                       
                       return act_html;
              },

            },
             <?php } ?>
           {mData: "updated_at",bSortable:false,bVisible:false},  
        ],
      } );
  oTable.on('draw', function(event) {
    $vary = $('#addUser');
    $('#example_length').prepend($vary);
    $('#addUser').css('margin-right','20px');
    $(".loader").addClass('hidden');
  });
  // show active FontMater
  $('#success-pill').click(function(){

    var url="<?= URL::route('fontmaster.index',['status'=>1])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
    $('.loader').removeClass('hidden');
  }); 
  // show Inactive FontMater
  $('#fail-pill').click(function(){

    var url="<?= URL::route('fontmaster.index',['status'=>0])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
    $('.loader').removeClass('hidden');
  });

  // clear model data
  $('.clear_model').on('hidden.bs.modal', function () {
      $(this).find("input,textarea,select").val('').end();
      $(this).find('span').text('').end();
      $('.save').removeAttr('disabled');
  });
  // clear model data
 
  </script>	
<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>

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
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/fontMaster/index.blade.php ENDPATH**/ ?>