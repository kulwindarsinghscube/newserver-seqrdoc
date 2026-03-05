<?php $__env->startSection('content'); ?>
<?php 
$domain = \Request::getHost();
$subdomain = explode('.', $domain);
?>
	<div class="container">
    <?php if($message = Session::get('success')): ?>
        <div class="alert alert-success fade in alert-dismissible show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true" style="font-size:20px">×</span>
            </button>
            <?php echo e($message); ?>

        </div>
    <?php endif; ?>  
<?php if($id != 1){ ?>
<?php echo Form::open(['action' =>['admin\AdminManagementController@AssignLabSave',$id], 'method' => 'put']); ?>    
    <input name="id" type="hidden" value="<?php echo e($id); ?>">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><i class="fa fa-fw fa fa-code-fork"></i> Assign Lab
            <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('adminmanagement')); ?></ol>
            </h1>
        </div>
    </div>		
    <div class="row" style="margin-top:0 !important;">
        <div class="col-lg-11"><h4 class="htag">User: <?php echo e($fullname); ?></h4></div>
        <div class="col-lg-1 text-center"><h4 class="htag"><a href="<?= route('adminmaster.index') ?>">Back</a></h4></div>
    </div>
    <div class="row">
        <div class="col-lg-5 text-center text-primary">        
            <h4 class="text-success">Available Labs</h4>
            <select name="sbTwo" id="sbTwo" class="form-control" style="width: 100%;">
                <option value="0">Select Lab</option>
                <?php $__currentLoopData = $LabData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value->id); ?>"  <?php if($assigned_labs == $value->id) echo "selected"; ?>><?php echo e($value->lab_title); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>				
            </select>
        </div>
        <div class="clearfix"></div>
        <div class="col-lg-5 text-center"> <br />
            <button class="btn btn-success" title="Save" type="submit" id="select_all">Save</button>
        </div>
    </div>
<?php echo Form::close(); ?>    
<?php } ?>		
	</div> 
	
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript"> 
var subdomain='<?php echo $subdomain[0];?>';   

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

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/adminManagement/assignlab.blade.php ENDPATH**/ ?>