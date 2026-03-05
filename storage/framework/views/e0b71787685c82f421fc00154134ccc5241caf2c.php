<?php $__env->startSection('content'); ?>
<?php
		
		$domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $profile_photo=asset($subdomain[0].'/backend/templates/profile_photos/' . Auth::guard('inswallet')->user()->photo);
   	?>
 <div class="row">
	<div class="col-xs-12 col-md-6 col-md-offset-3">
		<h2 style="color:#fff;font-family:roboto;">My Profile</h2>	
		<div class="list-group">
			<form class="change_profile_form" action="" method="post" enctype="multipart/form-data">
			<li class="list-group-item"><label>Student Name: </label> <input class="form-control" type="text" name="Student_Name" id="Student_Name" value="<?php echo e(Auth::guard('inswallet')->user()->Student_Name); ?>"> </li>
			<?php if($subdomain[0]!='kmtc'){ ?>
			<li class="list-group-item"><label>Father’s Name: </label> <input class="form-control" type="text" name="Father_Name" id="Father_Name" value="<?php echo e(Auth::guard('inswallet')->user()->Father_Name); ?>"> </li>
			<li class="list-group-item"><label>Mother’s Name: </label><input class="form-control" type="text" name="Mother_name" id="Mother_name" value="<?php echo e(Auth::guard('inswallet')->user()->Mother_name); ?>"> </li>
			<?php } ?>
			<li class="list-group-item"><label>Institute Email: </label><input readonly class="form-control" type="text" name="institute_email" id="institute_email" value="<?php echo e(Auth::guard('inswallet')->user()->institute_email); ?>"></li>
			<li class="list-group-item"><label>Mobile Number: </label><input class="form-control" type="text" name="mobile_no" id="mobile_no" value="<?php echo e(Auth::guard('inswallet')->user()->mobile_no); ?>"></li>
			<?php if($subdomain[0]=='kmtc'){ ?>
			<li class="list-group-item"><label>National ID: </label><input readonly class="form-control" type="text" name="enrol_roll_number" id="enrol_roll_number" value="<?php echo e(Auth::guard('inswallet')->user()->enrol_roll_number); ?>"></li>
			<?php } ?>
			<!-- <li class="list-group-item"><label>Institute Provided Email: </label> <?php echo e(Auth::guard('inswallet')->user()->username); ?> </li> -->
			<li class="list-group-item"><label>Admission Year: </label> <input class="form-control" type="text" name="admission_year" id="admission_year" value="<?php echo e(Auth::guard('inswallet')->user()->admission_year); ?>"></li>
			<li class="list-group-item"><label>Graduation Year: </label><input class="form-control" type="text" name="graduation_year" id="graduation_year" value="<?php echo e(Auth::guard('inswallet')->user()->graduation_year); ?>"></li>
			<?php if($subdomain[0]!='kmtc'){ ?>
			<li class="list-group-item"><label>Enrolment/Roll Number: </label><input class="form-control" type="text" name="enrol_roll_number" id="enrol_roll_number" value="<?php echo e(Auth::guard('inswallet')->user()->enrol_roll_number); ?>"></li>
			<li class="list-group-item"><label>Aadhaar Number: </label> <input class="form-control" type="text" name="adhar_no" id="adhar_no" value="<?php echo e(Auth::guard('inswallet')->user()->adhar_no); ?>"></li>
			<li class="list-group-item"><label>ABC ID: </label><input class="form-control" type="text" name="abc_id" id="abc_id" value="<?php echo e(Auth::guard('inswallet')->user()->abc_id); ?>"></li>
			<li class="list-group-item"><label>Local Address: </label><input class="form-control" type="text" name="local_address" id="local_address" value="<?php echo e(Auth::guard('inswallet')->user()->local_address); ?>"></li>
			<li class="list-group-item"><label>Blood Group: </label> <input class="form-control" type="text" name="blood_group" id="blood_group" value="<?php echo e(Auth::guard('inswallet')->user()->blood_group); ?>"> </li>
			<?php } ?>
			<li class="list-group-item"><label>Permanent Address: </label> <input class="form-control" type="text" name="permanent_address" id="permanent_address" value="<?php echo e(Auth::guard('inswallet')->user()->permanent_address); ?>"></li>
			<li class="list-group-item"><label>Date of Birth: </label><input class="form-control" type="text" name="dob" id="dob" value="<?php echo e(Auth::guard('inswallet')->user()->dob); ?>"> </li>
			<li class="list-group-item"><label>Gender: </label> <input class="form-control" type="text" name="gender" id="gender" value="<?php echo e(Auth::guard('inswallet')->user()->gender); ?>"> </li>
			<li class="list-group-item"><label>Photo: </label> <img width="100" height="100" src="<?php echo e($profile_photo); ?>" alt="Student Photo"><input class="form-control" type="file" name="photo" id="photo"> </li>
			<li class="list-group-item clearfix">
				<label>Password: </label> 
				<i class="fa fa-circle"></i><i class="fa fa-circle"></i><i class="fa fa-circle"></i><i class="fa fa-circle"></i><i class="fa fa-circle"></i>
				<span class="pull-right">
					<button id="change_profile" class="change_profile btn btn-theme" style="color:#fff">Update Profile</button>
					<button type="button" class="change_pass btn btn-theme" style="color:#fff">Change Password</button>
				</span>
			</li>
		</form>
			<li id="change_pass_holder" class="list-group-item" style="display:none;">
			<label>Change Password: </label>
				<form class="change_pass_form" action="" method="post">
					<div class="" style="position:relative;margin-bottom:10px;">
						<input id="newpassword" type="password" class="form-control" placeholder="New Password" name="password" data-rule-required="true" autofocus />
						<i class="fa fa-eye viewpass"></i>
					</div>
					
					<div class="" style="position:relative;margin-bottom:10px;">
						<input id="confirm_password" type="password" class="form-control" placeholder="Confirm New Password" name="confirm_password" data-rule-required="true" />
						<i class="fa fa-eye viewpass"></i>
					</div>
					
					<center>
						<button type="submit" id="update_pass" class="btn btn-theme" style="color:#fff">Update</button>
						<button type="button" id="cancel_change" class="btn btn-theme2 change_pass" style="color:#fff">Cancel</button>
					</center>
				</form>
			</li>
			<!-- <li class="list-group-item"><label>Fullname:</label> <?php echo e(Auth::guard('inswallet')->user()->fullname); ?> </li>
			<li class="list-group-item"><label>Email ID: </label> <?php echo e(Auth::guard('inswallet')->user()->email_id); ?></li>
			<li class="list-group-item"><label>Phone No: </label> <?php echo e(Auth::guard('inswallet')->user()->mobile_no); ?></li> -->
		</div>
	</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
  <script type="text/javascript">
       // Changes password slide toggle
	    $(".change_pass").click(function(event) {
	        	$("#change_pass_holder").slideToggle();
	     });
	        // password show and hide
        $('.viewpass').click(function(){
	       	$type = $(this).prev('input').attr('type');
	         if($type=="password"){
	        		$(this).prev('input').attr('type','text');
	        		$(this).addClass('fa-eye-slash red');
	        		$(this).removeClass('fa-eye');
	       	 }else{
	        		$(this).prev('input').attr('type','password');
	        		$(this).addClass('fa-eye');
	        		$(this).removeClass('fa-eye-slash red');
	        	  }
	     });
  
        $("#update_pass").click(function(event) {
            event.preventDefault();
               
		  if (!$('.change_pass_form').valid()) 
		   {
             return false;
		   }
		  else
		   {
       	    var url="<?php echo e(URL::route('inswebapp.profile.changepassword')); ?>";
       	    var token="<?php echo e(csrf_token()); ?>";
       	    var method_type="post";
       	    bootbox.confirm("Are you sure to update password?",function(result){
       	     if(result){
                  $(".change_pass_form").ajaxSubmit({
                 
                      url  : url,
                      type : method_type,
                      data : {'_token':token},

                      success:function(data){
                        if(data.success==true){
                          toastr.success('Successfully Change password');
                          setTimeout(function() {

                          	window.location.assign('/institute-student');
                          }, 2000);
                        }
                      },
                      error:function(resobj){
                       toastr.error('Something are wrong');
                      }
                  });
       	         } 
             });
            }
        });
$('.change_pass_form').validate({
		errorElement: 'div',
		errorClass: 'help-inline',
		focusInvalid: false,
		rules: {
		password: {
			minlength: 8
		},
		confirm_password: {
	      equalTo: "#newpassword"
	    }
	},
		messages: {
			password: "<span style='color:white'>Min. 8 character password is required</span>",
			confirm_password:"<span style='color:white'>Please enter the same value again.</span>",
		},
		invalidHandler: function (event, validator) {},
		highlight: function (e) {},
		success: function (e) {},
		errorPlacement: function (error, element) {
			error.insertBefore(element);
		},
		submitHandler: function (form) {},
		invalidHandler: function (form) {}
});

$("#change_profile").click(function(event) {
	event.preventDefault();
            var url="<?php echo e(URL::route('inswebapp.updateprofile')); ?>";
       	    var token="<?php echo e(csrf_token()); ?>";
       	    var method_type="post";
       	    bootbox.confirm("Are you sure to update profile details?",function(result){
       	     if(result){
                  $(".change_profile_form").ajaxSubmit({
                 
                      url  : url,
                      type : method_type,
                      data : {'_token':token},

                      success:function(data){
                        if(data.success==true){
                          toastr.success('Profile has been updated successfully');
                          setTimeout(function() {

                          	window.location.assign('/institute-student');
                          }, 2000);
                        }
                      },
                      error:function(resobj){
                       toastr.error('Something are wrong');
                      }
                  });
       	         } 
             });
            
        });

  </script> 
<?php $__env->stopSection(); ?>  

<?php $__env->startSection('style'); ?>
 <style>
	.viewpass{
		position:absolute;
		bottom:10px;
		right:10px;
		cursor:pointer;
	}

	.help-inline{
		font-weight: normal;
	    position: absolute;
	    top: -15px;
	    right: 4px;
	    color: #fff;
	    background: rgba(203,47,47,0.9);
	    padding: 0px 5px;
	    font-size: 11px;
	}
body{
	background-image: linear-gradient(to bottom, rgba(34, 143, 176, 0.3), rgba(111, 128, 206, 0.4)), url(/webapp/images/bg2.jpg);
}	
</style>

<?php $__env->stopSection(); ?>	  
<?php echo $__env->make('InstituteStudentWallet.layouts.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/InstituteStudentWallet/profile/index.blade.php ENDPATH**/ ?>