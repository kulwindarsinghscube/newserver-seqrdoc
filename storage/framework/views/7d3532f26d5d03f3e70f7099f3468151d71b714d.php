<?php $__env->startSection('content'); ?>
    <?php 
      $domain = \Request::getHost();
      $subdomain = explode('.', $domain);
    ?>
    <div class="container">
    	<div class="container-fluid">
    		<div class="row">
    			<div class="col-lg-12">
    				<h1 class="page-header"><i class="fa fa-fw fa-money"></i> Payment Gateway
    				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('paymentgateway')); ?></ol>
              <?php if($subdomain[0] == 'demo') {?>
              <i class="fa fa-info-circle iconModalCss" title="User Manual" id="paymentGatewayClick"></i>
              <?php } ?>
    				</h1>
    			</div>
    		</div>
    	<div class="">
    			<ul class="nav nav-pills" id="pills-filter">
    		  	<li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active PG </a></li>
    			  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive PG</a></li>
            <?php if(App\Helpers\SitePermissionCheck::isPermitted('pgmaster.store')): ?>
            <?php if(App\Helpers\RolePermissionCheck::isPermitted('pgmaster.store')): ?>
    				<li style="float: right;">
    					<button class="btn btn-theme" id="addUser" onclick="AddPgMaster_data()"><i class="fa fa-plus"></i> Add Payment Gateway</button>	
    				</li>
            <?php endif; ?>
            <?php endif; ?>
    			</ul>
    			<table id="example" class="table table-hover display" cellspacing="0" width="100%">
    				<thead>
    					<tr>
                 <th>#</th>
    						 <th>Payment Gateway Name</th>
    						 <th>Action</th>
                 <th></th>
    					</tr>
    				</thead>
    				<tfoot>
    				</tfoot>
    			</table>
    		</div>
    	</div>
    </div>

 <?php echo $__env->make('admin.paymentGateway.model', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
  
  // Send Ajax Request to delete Record      
function deletepath(fm_id)
{
  var delete_path="<?=URL::route('pgmaster.destroy',array(':id')) ?>";
  delete_path=delete_path.replace(':id',fm_id);
  var token="<?php echo e(csrf_token()); ?>";
  var method_type="get";

  bootbox.confirm("Are you sure you want to delete?",function(result){  
       if(result)
       {
            $.ajax({
               url:delete_path,
               type:method_type,
               data:{'_token':token},
               success:function(data){  
                 if(data.success==true)
                 {
                     toastr.success('Deleted successfully');
                     oTable.ajax.reload();
                 }
               },
            });
       }

  });
}

  function AddPgMaster_data()
  {
      $(".update_save").hide();
      $('.save').show();
      $('#addUsr').modal('show');
      $('.save').click(function(event) {

         $('.save').attr('disabled','disabled');  
         event.preventDefault();
         var create_path="<?= URL::route('pgmaster.store') ?>";
         var token="<?php echo e(csrf_token()); ?>";
         var method_type="post";

         $("#UserData").ajaxSubmit({
               url:create_path,
               type:method_type,
               data:{'_token':token},
               beforeSubmit:function(){
                 $("#addUsr").find('span').text('').end();
                 $(".loadsave").addClass('fa fa-spinner fa-spin');
               }, 
               success:function(data)
               {
                 if(data.success==true)
                 {
                   $('#addUsr').modal('hide');
                   $('#UserData').trigger("reset");
                   toastr.success('Payment Gateway successfully added');
                   oTable.ajax.reload();
                  $('.save').removeAttr('disabled');
                  $(".loadsave").removeClass('fa fa-spinner fa-spin');
                 }
               },
               error:function(resobj)
               {
                 $('.save').removeAttr('disabled');  
                 $.each(resobj.responseJSON.errors, function(k,v){
                     $('#'+k+'_error').text(v);
                  });
                 $(".loadsave").removeClass('fa fa-spinner fa-spin');
               },
         });      
      });
  }



    // send Ajax Request to featch data and show model 
      function edit(id)
       {
          $(".save").hide();
          $('.update_save').show();
          var edit_path="<?=URL::route('pgmaster.edit',array(':id')) ?>";
          edit_path=edit_path.replace(':id',id);
          var method_type="GET";
          var token="<?php echo e(csrf_token()); ?>";
          $.ajax({
                
                url  : edit_path,
                type : method_type,
                data : {'_token':token},

                success:function(data)
                {
                  $('#addUsr').modal('show');
                  $('#pg_name').val(data.pg_name);
                  $("#pg_id").val(data.id);
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


      
    // Update data PaymentGateway
     $(".update_save").click(function(event) {
         $(".update_save").attr('disabled'); 
         var pg_id=$("#pg_id").val();
         event.preventDefault();
         var update_path="<?= URL::route('pgmaster.update',array(':id')) ?>";
         update_path=update_path.replace(':id',pg_id);
         var token="<?php echo e(csrf_token()); ?>";
         var type="post";

         $("#UserData").ajaxSubmit({
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
                 if(data.success==true)
                 {
                   $('#addUsr').modal('hide');
                   $('#fontMatser_data').trigger("reset");
                   toastr.success('Payment Gateway Deleted successfully');
                   oTable.ajax.reload();
                   $(".update_save").removeAttr('disabled'); 
                   $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                 }
               },
               error:function(resobj)
               {
                 $.each(resobj.responseJSON.errors, function(k,v){
                     $('#'+k+'_error').text(v);
                  });
                 $(".update_save").removeAttr('disabled');
                 $(".loadupdate").removeClass('fa fa-spinner fa-spin'); 
               },
         });      
    });
 
     // datatable	 
   var oTable = $('#example').DataTable( {
       'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
       "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [3, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('pgmaster.index',['status'=>1])?>",
        "aoColumns":[
          {mData: "rownum", bSortable:false},
          {mData: "pg_name",bSortable:true},
          {
            mData:"id",
            bSortable:false,

            mRender:function(v, t, o){
                var act_html;
        
                     act_html ='<?php if(App\Helpers\SitePermissionCheck::isPermitted('pgmaster.edit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('pgmaster.edit')): ?><a onclick="edit('+o['id']+')"><i class="fa fa-edit fa-lg green"></i></a><?php endif; ?> <?php endif; ?>';
                       act_html +='<?php if(App\Helpers\SitePermissionCheck::isPermitted('pgmaster.destroy')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('pgmaster.destroy')): ?>&nbsp;&nbsp;<a onclick="deletepath('+o['id']+')"><i class="fa fa-trash fa-lg red"></i></a><?php endif; ?> <?php endif; ?>';

                     return act_html;
            },
          },
          {mData: "updated_at",bSortable:false,bVisible:false},  
      ],
    });

   oTable.on('draw', function(event) {
    $(".loader").addClass('hidden');
   });

  // get data active PaymentGateway       
  $('#success-pill').click(function(){

    var url="<?= URL::route('pgmaster.index',['status'=>1])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
    $('.loader').removeClass('hidden');
  });
  // get data Inactive PaymentGateway
  $('#fail-pill').click(function(){

    var url="<?= URL::route('pgmaster.index',['status'=>0])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
    $('.loader').removeClass('hidden');
  });

// clear Admin model data
$('.clear_model').on('hidden.bs.modal', function () {
    $(this).find("input,textarea,select").val('').end();
    $(this).find('span').text('').end();
    $(".save").removeAttr('disabled');
});
// clear Admin model data 
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
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/paymentGateway/index.blade.php ENDPATH**/ ?>