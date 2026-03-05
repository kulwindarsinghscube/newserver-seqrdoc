<?php $__env->startSection('content'); ?>
  	<div class="container">
  	<div class="col-xs-12">
    	<div class="container-fluid">
  		<div class="row">
  			<div class="col-lg-12">
  				<h1 class="page-header"><i class="fa fa-fw fa-users"></i> Student Management
  				  <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('studentmanagement')); ?></ol>
            <i class="fa fa-info-circle iconModalCss" title="User Manual" id="studentMasterClick"></i>
  				</h1>	
  			</div>
  		</div>
  		<div class="">
  			<ul class="nav nav-pills" id="pills-filter">
  			  <!--<li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Users </a></li>
  			  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Users</a></li> -->
        <?php if(App\Helpers\SitePermissionCheck::isPermitted('student.upload')): ?>
        <?php if(App\Helpers\RolePermissionCheck::isPermitted('student.upload')): ?>
  				<li style="float: right;">
  						<button class="btn btn-theme" id="importUser" data-toggle="modal" data-target="#uploadFile"><i class="fa fa-plus"></i> Import Students</button>
  				</li>
        <?php endif; ?>
        <?php endif; ?>
  			</ul>
  			<div class="col-xs-12">
  				<table id="example" class="table table-hover" cellspacing="0" width="100%">
  					<thead>
  						<tr>
  						  <th>#</th>
  							<th>Document Id</th>
  							<th>Enroll No</th>
  							<th>Date OF Birth</th>
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
<iframe id="my_iframe" style="display:none;"></iframe>
 <!--  Model studentManagement -->
  <?php echo $__env->make('admin.studentManagement.model', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
 <!-- End Model studentManagement -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
 <script type="text/javascript">
 	
  // downlaod sample file
   function Download()
   {    
      document.getElementById('my_iframe').src = "<?= \Config::get('constant.download_path') ?>/CEDPImportData.xlsx";
   }

 	      // datatable
   var oTable = $('#example').DataTable( {
	      'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [4, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('student.index') ?>",
	      "aoColumns":[
          {mData: "rownum", bSortable:false,"sClass": "text-center"},
          {mData: "doc_id",bSortable:true,"sClass": "text-center"},
          {mData: "enrollment_no",bSortable:true,"sClass": "text-center"},
          {mData: "date_of_birth",bSortable:true,"sClass": "text-center"},
          {mData: "updated_at",bSortable:false,bVisible:false},
         ],
    });
   <?php if(App\Helpers\RolePermissionCheck::isPermitted('student.upload')): ?>
     // Send ajax to Import student file
    $(".save").click(function(event){
       event.preventDefault();
       var url="<?php echo e(URL::route('student.upload')); ?>";
       var token="<?php echo e(csrf_token()); ?>";
       var method_type="post";
       $("#student_doc").ajaxSubmit({
               
                url  : url,
                data : {'_token':token},
                type : method_type,
                beforeSubmit:function(){
                  $("#student_doc").find('span').text('').end();
                  $(".loadsave").addClass('fa fa-spinner fa-spin');
                  $(".save").attr('disabled','disabled');
                },
                success:function(data){
                  if(data.success==false)
                  {
                     if(data.NoLine)
                     {
                       $("#field_file_error").text(data.NoLine);
                     }
                     if(data.InvalidData)
                     {
                       $("#field_file_error").text(data.InvalidData);
                     }
                     if(data.ExcelInvalid)
                     {
                       $("#field_file_error").text(data.ExcelInvalid);
                     }
                    $(".loadsave").removeClass('fa fa-spinner fa-spin');
                    $(".save").removeAttr('disabled');
                  }
                  if(data.success==true)
                  {

                  	$("#uploadFile").modal('hide');
                  	toastr.success('Succeessfully Imported file');
                  	oTable.ajax.reload();
                    $(".loadsave").removeClass('fa fa-spinner fa-spin');
                    $(".save").removeAttr('disabled');
                  }
                },
                error:function(resobj){
                   $(".save").removeAttr('disabled');
                  $.each(resobj.responseJSON.errors,function(k,v) {
                  	 $("#"+k+'_error').text(v);
                  });
                  $(".loadsave").removeClass('fa fa-spinner fa-spin');
                }
       });
      
    });
<?php endif; ?>    

   // clear Student model data    
    $(".clear_model").on('hidden.bs.modal', function(){
                   
       $(this).find('input').val('').end();
       $(this).find('span').text('');
    });
   // clear Student model data 
 
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
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/studentManagement/index.blade.php ENDPATH**/ ?>