<?php $__env->startSection('content'); ?>
 <div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-fw fa-print"></i> Printing Details
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('printingdetail')); ?></ol>
				<i class="fa fa-info-circle iconModalCss" title="User Manual" id="printingDetailsSandboxingClick"></i>
				</h1>
				
			</div>
		</div>
<!-- 
		<div class="modal fade" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Printing Details</h4>
					</div>
					<div class="modal-body">
					
						<div class="alert alert-danger">
						  <strong>Error!</strong> <span class="message"></span>
						</div>
						
						<div class="alert alert-success">
						  <strong>Success!</strong> <span class="message"></span>
						</div>
						
						<form method="post" id="UserData">
							<div class="form-group">
								<label>Username</label>
								<select class="form-control" id="username" name="username" data-rule-required="true" data-live-search="true"></select>	
							</div>
							<div class="form-group">
								<label>Print Datetime</label>
								<input type="text" class="form-control" id="print_datetime" name="print_datetime" data-rule-required="true" >
							</div>
							<div class="form-group">
								<label>Printer name</label>
								<input type="text" class="form-control" id="printer_name" name="printer_name" data-rule-required="true" >
							</div>
							<div class="form-group">
								<label>Print count</label>
								<input type="text" class="form-control" id="print_count" name="print_count" data-rule-required="true" >
							</div>
							<div class="form-group">
								<label>Print serial no</label>
								<input type="text" class="form-control" id="print_serial_no" name="print_serial_no" data-rule-required="true">
							</div>
							<div class="form-group">
								<label>Serial no</label>
								<input type="text" class="form-control" id="serial_no" name="serial_no" data-rule-required="true">
							</div>
							<div class="form-group">
								<label for="opt_status">Status :</label>
								<select name="opt_status" id="opt_status" class="form-control" data-rule-required="true">
									<option value="">Select</option>
									<option value="1">Active</option>
									<option value="0">Inactive</option>
								</select>
							</div>
							<div class="form-group clearfix">
							<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="UserSave"><i class="fa fa-save"></i> Save</button>
							<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="UserEdit"><i class="fa fa-save"></i> Update</button>
							
							</div>
						</form>
					</div>
				</div>
			</div>
		</div> -->
		<div class="">
			<ul class="nav nav-pills" id="pills-filter">
			  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active </a></li>
			  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive </a></li>
			</ul>
			<div class="col-xs-12">
				<table id="example" class="table table-hover" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Print sr. no.</th>
							<th>Certificate sr. no.</th>
							<th>Print count</th>
							<th>Printer name</th>
							<th>Print Datetime</th>
							<th>Username</th>
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
</div>
<div id="myModal" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Printing Details Information</h4>
      </div>
      <div class="modal-body" id="ajaxResponse">
      	      	<div class="row">
                   <div class="col-xs-3"><label>Username</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="username_info"></span></div>
				</div>
				<div class="row">
					<div class="col-xs-3"><label>Print Datetime</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="print_datetime_info"></span></div>
				</div>
				<div class="row">
					<div class="col-xs-3"><label>Printer name</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="printer_name_info"></span></div>
				</div>
				<div class="row">
					<div class="col-xs-3"><label>Print count</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="print_count_info"></span></div>
				</div>
				<div class="row">
					<div class="col-xs-3"><label>Print serial no</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="print_serial_no_info"></span></div>
				</div>
				<div class="row">
					<div class="col-xs-3"><label>Serial no</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="sr_no_info"></span></div>
				</div>
				<div class="row">
					<div class="col-xs-3"><label>Reprinted</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="reprint_info"></span></div>
				</div>
				<div class="row">
					<div class="col-xs-3"><label>Status</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="status_info"></span></div>
				</div>
				<div class="row">
					<div class="col-xs-3"><label>Created at</label></div>
					<div class="col-xs-1"><label>:</label></div>
					<div class="col-xs-8"><span id="created_at_info"></span></div>
				</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-theme" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">

   function print_info(print_id)
   {
   	  var url="<?php echo e(URL::route('sand-box.printingDetails.getdata')); ?>";
   	  var token="<?php echo e(csrf_token()); ?>";
   	  $.get(url,{'_token':token,'print_id':print_id},function(data) {
          $("#myModal").modal('show');
          $("#username_info").text(data.username);
          $("#print_datetime_info").text(data.print_datetime);
          $("#printer_name_info").text(data.printer_name);
          $("#print_count_info").text(data.print_count);
          $("#print_serial_no_info").text(data.print_serial_no);
          $("#sr_no_info").text(data.sr_no);
          if(data.reprint==0)
          {
            $("#reprint_info").text('no'); 
          }
          else
          {
          	$("#reprint_info").text('yes'); 
          }
         
          if(data.status==1)
          {
             $("#status_info").text("active");
          }
          else
          {
          	$("#status_info").text("inactive");
          }
        
          $("#created_at_info").text(data.created_at);
   	  });

   }

	     // datatable	 
   var oTable = $('#example').DataTable( {
     'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
      "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [8, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('sandboxing.printingDetails.index',['status'=>1])?>",
    "aoColumns":[
          {mData: "rownum", bSortable:false,"sClass": "text-center"},
          {mData: "print_serial_no",bSortable:true},
          {mData: "sr_no",bSortable:true},
          {mData: "print_count",bSortable:true},
          {mData: "printer_name",bSortable:true},
          {mData: "print_datetime",bSortable:true},
          {mData: "username",bSortable:true},
          {
            mData:"id",
            bSortable:false,

            mRender:function(v, t, o){
                    var act_html;

                     act_html ='<?php if(App\Helpers\SitePermissionCheck::isPermitted('printing-detail.getdetail')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('printing-detail.getdetail')): ?><a onclick="print_info('+v+')"><i class="fa fa-info-circle fa-lg blue"></i></a><?php endif; ?> <?php endif; ?>';

                     return act_html;
            },
          },
         {mData: "updated_at",bSortable:false,bVisible:false},  
      ],
    });

   oTable.on('draw.dt',function(event) {
     $(".loader").addClass('hidden');
   });

  // get data active PaymentGateway       
  $('#success-pill').click(function(){

    var url="<?= URL::route('sandboxing.printingDetails.index',['status'=>1])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
    $('.loader').removeClass('hidden');
  });
  // get data Inactive PaymentGateway
  $('#fail-pill').click(function(){

    var url="<?= URL::route('sandboxing.printingDetails.index',['status'=>0])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
    $('.loader').removeClass('hidden');
  });

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
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/sandboxing/printingDetails.blade.php ENDPATH**/ ?>