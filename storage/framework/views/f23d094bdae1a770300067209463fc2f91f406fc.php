<?php $__env->startSection('content'); ?>


<div class="container">
    <div class="col-xs-12">
      <div class="clearfix">
	<div id="">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-fw fa-lock"></i> Session Manager
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('usersession')); ?></ol>
        <i class="fa fa-info-circle iconModalCss" title="User Manual" id="userSessionManagerReportClick"></i>
								
				</h1>
			</div>
		</div>
		<div class="">
			<ul class="nav nav-pills" id="pills-filter">
			  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-lock"></i> Active Sessions </a></li>
			  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-globe"></i> Session History</a></li>
			</ul>
			<table id="example" class="table table-hover display" cellspacing="0" width="100%">
				<thead>
					<tr>
            <th>#</th>
            <th>User Name</th>
            <th>Full Name</th>
          
						<th>User type</th>
						<th>IP Address</th>
            <th>Login Time</th>
						<th>Logout Time</th>
						<th>Device Type</th>
						<th>Action</th>
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
<form>
  <input type="hidden" name="status" id="status_val">
</form>

<div id="edit_form">
  

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script type="text/javascript">
var status = $("#status_val").val();
	 var oTable = $('#example').DataTable( {
		'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
	   "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [4, "desc"]
        ],
        "sAjaxSource":"<?= route('session-manager.index',['status'=>1]) ?>",
		    "aoColumns":[

          {mData: "rownum", bSortable:false},
          {mData: "name",bSortable:true},
          {mData: "fullname",bSortable:true},
         
    			{mData: "in_name",bSortable:true},
    			{mData: "ip",bSortable:true},
          {mData: "login_time",bSortable:true},
          {mData: "logout_time",bSortable:true,bVisible:false},
    			{mData: "device_type",bSortable:true},
    			{
    				mData:"id",
    				bSortable:false,

    				mRender:function(v, t, o){
              var act_html;
          
              
             act_html ='<a><i class="fa fa-map-marker fa-lg green"></i></a>';
             act_html +='<?php if(App\Helpers\SitePermissionCheck::isPermitted('session-manager.destroy')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('session-manager.destroy')): ?><a onclick="deletepath('+o['id']+')"><i class="fa fa-trash fa-lg red"></i></a><?php endif; ?> <?php endif; ?>';

              return act_html;
    				},
    			},	
			],
    } );
oTable.on('draw', function () {
  $('[title]').tooltip(); 
});
$("#status_val").val(1); 
// visible false to logout time
oTable.column( 6 ).visible( false );
oTable.column( 8 ).visible( true );

  // Send Ajax Request to delete Record Soft Delete     
function deletepath(fm_id)
{
  var delete_path="<?=URL::route('session-manager.destroy',array(':id')) ?>";
  delete_path=delete_path.replace(':id',fm_id);
  var token="<?php echo e(csrf_token()); ?>";
  var method_type="delete";

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
                     toastr.success('User Logout Successfully');
                     oTable.ajax.reload();
                 }
               },
            });
       }

  });
} 

$('#success-pill').click(function(){
  $("#status_val").val(1);
  var url="<?= URL::route('session-manager.index',['status'=>1])?>";
  oTable.ajax.url(url);
  oTable.ajax.reload();
  oTable.column( 6 ).visible( false );
  oTable.column( 8 ).visible( true );
  $('.loader').addClass('hidden');
}); 
$('#fail-pill').click(function(){
  $("#status_val").val(0);

  var url="<?= URL::route('session-manager.index',['status'=>0])?>";
  oTable.ajax.url(url);
  oTable.ajax.reload();
  oTable.column( 6 ).visible( true );
  oTable.column( 8 ).visible( false );
  $('.loader').addClass('hidden');
});
</script>	
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/sessionmanager/index.blade.php ENDPATH**/ ?>