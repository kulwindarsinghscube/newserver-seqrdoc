<?php $__env->startSection('content'); ?>
<div class="container">
  <div class="col-xs-12">
    <div class="clearfix">
    	<div id="">
      	<div class="container-fluid">
      		<div class="row">
      			<div class="col-lg-12">
      				<h1 class="page-header"><i class="fa fa-fw fa-lock"></i> Template Data
      				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('templatedata')); ?></ol>
              <i class="fa fa-info-circle iconModalCss" title="User Manual" id="templateDataSandboxingClick"></i>
      				</h1>
      			</div>
      		</div>
      		<div class="">
      			<table id="example" class="table table-hover display" cellspacing="0" width="100%">
      				<thead>
      					<tr>
                  <th>#</th>
                  <th>Template Name</th>
                  <th>Status</th>
      						<th>Active Documents</th>
      						<th>Inactive Documents</th>
                  <th>Data last used</th>
      						<th>Active Scanned</th>
      						<th>Inactive Scanned</th>
      					</tr>
      				</thead>
      				<tbody>
                  <?php $__currentLoopData = $template_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <tr>
                        <td><?php echo e($value['id']); ?></td>
                        <td><?php echo e($value['template_name']); ?></td>
                        <td><?php echo e($value['status']); ?></td>
                        <td><?php echo e($value['active_count']); ?></td>
                        <td><?php echo e($value['deactive_count']); ?></td>
                        <td><?php echo e($value['updated_on']); ?></td>
                        <td><?php echo e($value['active']); ?></td>
                        <td><?php echo e($value['deactive']); ?></td>
                      </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      				</tbody>
      			</table>
      		</div>
      	</div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script type="text/javascript">
var oTable = $('#example').DataTable( {
	'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
     "paging": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "processing": true,
      "bPaginate": true,
      "lengthMenu": [10,25,50,75,100],
      "aaSorting": [
      [0, "asc"],
      ],
      "aoColumnDefs":[
        {aTargets:[4],bSortable:false}
      ]
});
</script>	
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/sandboxing/templateData.blade.php ENDPATH**/ ?>