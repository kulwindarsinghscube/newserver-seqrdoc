<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="col-xs-12">
            <div class="clearfix">
                <div id="">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"><i class="fa fa-certificate"></i>Intifacc Mordern Technology
                                    <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"></ol>
                                </h1>
                            </div>
                        </div>
                             
                        <form action="<?php echo e(route('imt.store')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php if($errors->any()): ?>
                                <div class="alert alert-danger">
                                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                    <ul>
                                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($error); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="exampleFormControlInputName" class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" id="exampleFormControlInputName" placeholder="Enter Name" value="<?php echo e(old('name')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputDOB" class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" id="exampleFormControlInputDOB" value="<?php echo e(old('dob')); ?>" >
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputPlaceofBirth" class="form-label">Place of Birth</label>
                                <input type="text" name="birth_place" class="form-control" id="exampleFormControlInputPlaceofBirth" placeholder="Enter Place of Birth" value="<?php echo e(old('birth_place')); ?>" >
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputIDCardNumber" class="form-label">ID Card Number</label>
                                <input type="text" name="card_number" class="form-control" id="exampleFormControlInputIDCardNumber" placeholder="Enter ID Card Number" value="<?php echo e(old('card_number')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputDOB" class="form-label">Sex</label>
                                <input type="text" name="sex" class="form-control" id="exampleFormControlInputDOB" placeholder="Enter Sex" value="<?php echo e(old('sex')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputMaritalStatus" class="form-label">Marital Status</label>
                                <input type="text" name="marital_status" class="form-control" id="exampleFormControlInputMaritalStatus" placeholder="Enter Marital Status" value="<?php echo e(old('marital_status')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextareaPlaceofResidence" class="form-label">Place of Residence</label>
                                <textarea class="form-control" name="address" id="exampleFormControlTextareaPlaceofResidence" rows="3"><?php echo e(old('address')); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputMotherName" class="form-label">Mother Name</label>
                                <input type="text" name="mother_name" class="form-control" id="exampleFormControlInputMotherName" placeholder="Enter Mother Name" value="<?php echo e(old('mother_name')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputDOI" class="form-label">Date of Issue</label>
                                <input type="date" name="doi" class="form-control" id="exampleFormControlInputDOI" value="<?php echo e(old('doi')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputOccupation" class="form-label">Occupation</label>
                                <input type="text" name="occupation" class="form-control" id="exampleFormControlInputOccupation" placeholder="Enter Occupation" value="<?php echo e(old('occupation')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputImage" class="form-label">Image</label>
                                <input type="file" name="photo" class="form-control" id="exampleFormControlInputImage" value="<?php echo e(old('photo')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInputfingerprint" class="form-label">Finger Print </label>
                                <input type="file" name="fingerprint" class="form-control" id="exampleFormControlInputfingerprint" value="<?php echo e(old('fingerprint')); ?>">
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary" style="margin: 0.5%">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/imt/create.blade.php ENDPATH**/ ?>