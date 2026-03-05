<?php $__env->startSection('content'); ?>
	<div class="container">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-building"></i> Institute Management
								<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('institutemanagement')); ?></ol>
								<i class="fa fa-info-circle iconModalCss" title="User Manual" id="instituteMasterClick"></i>
								</h1>	
							</div>
						</div>
						
						<div class="modal fade" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="myModalLabel">Institute</h4>
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
												<input type="text" class="form-control" id="institute_username" name="institute_username" maxlength="100">
												<span id="institute_username_error" class="help-inline text-danger"></span>
											</div>
											<div class="form-group">
												<label>Fullname</label>
												<input type="text" class="form-control only_character" id="username" name="username" maxlength="100">
												<span id="username_error" class="help-inline text-danger"></span>
											</div>
											<div class="form-group" id="psswrd">
												<label>Password</label>
												<input type="password" class="form-control" id="password" name="password">												
											</div>
                                            <div class="form-group" id="psswrd">
												<label>Confirm Password</label>
												<input type="password" class="form-control" id="password" name="password_confirmation">
                                                <span id="password_error" class="help-inline text-danger"></span>
                                                <span id="password_confirmation_error" class="help-inline text-danger"></span>
											</div>
											<!--for sending old password during edit take password hidden field-->
											<input type="hidden" name="passwordedit" id="pwd">
											<div class="form-group">
												<label for="opt_status">Status</label>
												<select name="status" id="opt_status" class="form-control">
													<option value="">Select</option>
													<option value="1">Active</option>
													<option value="0">Inactive</option>
												</select>
												<span id="status_error" class="help-inline text-danger"></span>
											</div>
											<input type="hidden" name="id" id="institute_id">
											<div class="form-group clearfix">
									

										  <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="UserSave"><i class="fa fa-save"></i> Save</button>
											<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="UserEdit"><i class="fa fa-save"></i> Update</button>
										
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
						<div class="">
							<ul class="nav nav-pills" id="pills-filter">
							  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Institute Users </a></li>
							  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Institute Users</a></li>
							  <?php if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.store')): ?>
							  <?php if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.store')): ?>
							  <li style="float: right;">
								<button class="btn btn-theme" id="addUser" data-toggle="modal" data-target="#addUsr"><i class="fa fa-plus"></i> Create Institute User</button>
							   </li>
							  <?php endif; ?>
							  <?php endif; ?>
							</ul>
							<div class="col-xs-12">
								<table id="example" class="table table-hover" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>#</th>
											<th>Date created</th>
											<th>Username</th>
											<th>Full Name</th>
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
	</div>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset('backend/js/moment.min.js')); ?>"></script>
<script type="text/javascript">
	$('a[href^="institutemaster.php"]').parent().addClass('active');
	$('a[href^="institutemaster.php"]').parent().parent().parent().addClass('active');

	//hide alert message in add-edit form
	$(".alert-danger").hide();
	$(".alert-success").hide();

	$token = '<?= csrf_token()?>';
	//datatable for index page 
	var oTable = $('#example').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [0, "desc"]
        ],
        //index page url calls
        "sAjaxSource":"<?= URL::route('institutemaster.index',['status'=>1])?>",
        //columns that displaying
        "aoColumns":[

		{mData: "rownum", bSortable:false,
		},
		{mData: "created_at",sWidth: "20%",bSortable: true,
			mRender: function(v, t, o) {
            	var date = moment(v).format('DD-MM-YYYY h:mm:ss');
				return date;
            },
		},
		{mData: "institute_username",sWidth: "20%",bSortable: true,},
		{mData: "username",sWidth: "20%",bSortable: true,},
		{mData: 'id',
            bSortable: false,
            sWidth: "30%",
            sClass: "text-center",
            mRender: function(v, t, o) {

            	var buttons = '';
            	
            	buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.update')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.update')): ?><span data-toggle="tooltip" title="Edit" id="editData" class="" data-id="'+o['id']+'" data-institute_username="'+o['institute_username']+'" data-username="'+o['username']+'" data-status="'+o['status']+'" data-password="'+o['password']+'" data-role="'+o['role_id']+'"><i class="fa fa-edit fa-lg green"></i></span> &nbsp;&nbsp;<?php endif; ?> <?php endif; ?>';
            	buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('institutemaster.delete')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.delete')): ?><span data-toggle="tooltip" title="Delete" id="delData" class="" data-id="'+o['id']+'"><i class="fa fa-trash fa-lg red"></i></span> &nbsp;&nbsp;<?php endif; ?> <?php endif; ?>';
				return buttons;
            },
         		   	
        },
	],
		"createdRow": function( row, data, dataIndex ) {

			if(data['status'] == 'Active'){
				$(row).addClass( 'active-student' );
			}else{
				$(row).addClass( 'inactive-student' );
			}
		}
	});

	oTable.on('draw.dt', function () {
	    $(".loader").addClass('hidden');  
	});

	//for displaying activate user(status = 1)
	$('#success-pill').click(function(){

		var url="<?= URL::route('institutemaster.index',['status'=>1])?>";
		oTable.ajax.url(url);
		oTable.ajax.reload();
		$('.loader').removeClass('hidden');
	});

	//for displaying inactivate user(status = 0)
	$('#fail-pill').click(function(){

		var url="<?= URL::route('institutemaster.index',['status'=>0])?>";
		oTable.ajax.url(url);
		oTable.ajax.reload();
		$('.loader').removeClass('hidden');
	});
<?php if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.delete')): ?>
	//delete User data
	oTable.on('click', '#delData', function (e) {
		$id = $(this).data('id');
		bootbox.confirm("Are you sure you want to delete?",function(result){
			if(result){
				$.post('<?=route('institutemaster.delete')?>',
					{'type':'delete','id':$id,'_token':$token},
					function(data){
						if(data.type == 'success'){
							toastr["success"](data.message);
							oTable.ajax.reload();
						}
						else toastr["error"](data.message);
					},'json');
			}
		});
	});
<?php endif; ?>
	//on add user button click show save button and hide update button
	$('#addUser').click(function(){
		$("#UserEdit").hide();
		$("#UserSave").show();
		$('#UserData')[0].reset();
		$("#psswrd").show();
		$('#institute_id').val('');
	});
<?php if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.store')): ?>
	//save data
	$('#UserSave').click(function(e){
		var token = '<?= csrf_token()?>';
		$("#UserData").ajaxSubmit({
		 	url: "<?=route('institutemaster.store')?>",
            type: 'POST',
            data: { "_token" : token},
			beforeSubmit:function()
			{
				$(".alert-danger").fadeOut();
				$(".alert-success").fadeOut();
				$("#UserSave i").removeClass('fa-save');
				$("#UserSave i").addClass('fa-spinner');
				$("#UserSave i").addClass('fa-spin');
			},
			complete: function(){
				$("#UserSave i").addClass('fa-save');
				$("#UserSave i").removeClass('fa-spinner');
				$("#UserSave i").removeClass('fa-spin');
			},
			success:function(data)
			{	
				$('#addUsr').modal('hide');
				toastr.success('User successfully added');
				oTable.ajax.reload(); 
				$('.help-inline').text('');

			},
			error:function(resobj)
			{
				$.each(resobj.responseJSON.errors, function(k,v){
				   $('#'+k+'_error').css('display','block')
				   $('#'+k+'_error').text(v);
				});
			},
		}); 	
	});
<?php endif; ?>
	//on edit click
	oTable.on('click', '#editData', function (e) {
	    $('#addUsr').modal('show');
		$("#UserEdit").show();
		$("#UserSave").hide();
		$('#UserData')[0].reset();
		
		$('#institute_username').val($(this).data('institute_username'));
		$('#username').val($(this).data('username'));
		$('#opt_status').val($(this).data('status'));
		$("#psswrd").show();
		$('#pwd').val($(this).data('password'));
		$id = $(this).data('id');
		$('#institute_id').val($id);
	});
<?php if(App\Helpers\RolePermissionCheck::isPermitted('institutemaster.update')): ?>
	//edit data
	$('#UserEdit').click(function(e){
		e.preventDefault();
		var token = '<?= csrf_token()?>';
		var id = $('#institute_id').val();
		var update_url = "<?=route('institutemaster.update',':id')?>";
		update_url = update_url.replace(':id',id)
        $('#UserData').ajaxSubmit({
            url: update_url,
            type: 'post',
            data: { "_token" : token},
            dataType: 'json',
            beforeSubmit: function (){
				$(".alert-danger").fadeOut();
				$(".alert-success").fadeOut();
				$("#UserEdit i").removeClass('fa-save');
				$("#UserEdit i").addClass('fa-spinner');
				$("#UserEdit i").addClass('fa-spin');
			},
			success:function(data)
			{	
				$('#addUsr').modal('hide');
				toastr.success('User successfully added');
				oTable.ajax.reload(); 
				$('.help-inline').text('');

			},
			complete: function(){
				$("#UserEdit i").addClass('fa-save');
				$("#UserEdit i").removeClass('fa-spinner');
				$("#UserEdit i").removeClass('fa-spin');
			},
			error:function(resobj)
			{
				$.each(resobj.responseJSON.errors, function(k,v){
				   $('#'+k+'_error').css('display','block')
				   $('#'+k+'_error').text(v);
				});
			},

        });	
	});
<?php endif; ?>
	//validate institute form data	
	$('#UserData').validate({
		errorElement: 'span',
		errorClass: 'help-inline',
		focusInvalid: false,
		invalidHandler: function (event, validator) { //display error alert on form submit
			$('.alert-error', $('#UserData')).show();
		},
		highlight: function (e) {
			$(e).closest('.control-group').removeClass('info').addClass('error');
		},
		success: function (e) {
			$(e).closest('.control-group').removeClass('error').addClass('info');
			$(e).remove();
		},
		errorPlacement: function (error, element) {
			if (element.is(':checkbox') || element.is(':radio')) {
				var controls = element.closest('.controls');
				if (controls.find(':checkbox,:radio').length > 1)
					controls.append(error);
				else
					error.insertAfter(element.nextAll('.lbl:eq(0)').eq(0));
			}
			else if (element.is('.select2')) {
				error.insertAfter(element.siblings('[class*="select2-container"]:eq(0)'));
			}
			else if (element.is('.chzn-select')) {
				error.insertAfter(element.siblings('[class*="chzn-container"]:eq(0)'));
			}
			else
				error.insertAfter(element);
		},
		submitHandler: function (form) {
		},
		invalidHandler: function (form) {
		}
	});


	// allow only character
	$(".only_character").keypress(function(h) {
	  
	    var keyCode=h.which ? h.which :h.keyCode;
	    if(!(keyCode>=97 && keyCode <=122) && !(keyCode>=65 && keyCode <=90) && !(keyCode>=32 && keyCode <=32))
	    {
	        return !1;
	    } 
	    
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
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/institutemaster/index.blade.php ENDPATH**/ ?>