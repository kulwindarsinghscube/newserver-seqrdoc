@extends('StudentWallet.layouts.layout')
@section('content')
 <div class="row">
	<div class="col-xs-12 col-md-6 col-md-offset-3">
		<h2 style="color:#fff;font-family:roboto;">My Profile</h2>	
		<div class="list-group">
			<li class="list-group-item"><label>Student Name: </label> {{ Auth::guard('gswallet')->user()->Student_Name }} </li>
			<li class="list-group-item"><label>Father’s Name: </label> {{ Auth::guard('gswallet')->user()->Father_Name }} </li>
			<li class="list-group-item"><label>Mother’s Name: </label> {{ Auth::guard('gswallet')->user()->Mother_name }} </li>
			<li class="list-group-item"><label>Personal Email: </label> {{ Auth::guard('gswallet')->user()->personal_email }} </li>
			<li class="list-group-item"><label>Mobile Number: </label> {{ Auth::guard('gswallet')->user()->mobile_no }} </li>
			<li class="list-group-item"><label>Enrolment/Roll Number: </label> {{ Auth::guard('gswallet')->user()->enrol_roll_number }} </li>
			<!-- <li class="list-group-item"><label>Institute Provided Email: </label> {{ Auth::guard('gswallet')->user()->username }} </li> -->
			<li class="list-group-item"><label>Admission Year: </label> {{ Auth::guard('gswallet')->user()->admission_year }} </li>
			<li class="list-group-item"><label>Graduation Year: </label> {{ Auth::guard('gswallet')->user()->graduation_year }} </li>
			<li class="list-group-item"><label>Aadhaar Number: </label> {{ Auth::guard('gswallet')->user()->adhar_no }} </li>
			<li class="list-group-item"><label>ABC ID: </label> {{ Auth::guard('gswallet')->user()->abc_id }} </li>
			<li class="list-group-item"><label>Local Address: </label> {{ Auth::guard('gswallet')->user()->local_address }} </li>
			<li class="list-group-item"><label>Permanent Address: </label> {{ Auth::guard('gswallet')->user()->permanent_address }} </li>
			<li class="list-group-item"><label>Blood Group: </label> {{ Auth::guard('gswallet')->user()->blood_group }} </li>
			<li class="list-group-item"><label>Date of Birth: </label> {{ Auth::guard('gswallet')->user()->dob }} </li>
			<li class="list-group-item"><label>Gender: </label> {{ Auth::guard('gswallet')->user()->gender }} </li>
			<li class="list-group-item"><label>Photo: </label> <img width="100" height="100" src="{{ asset('demo/backend/templates/100/' . Auth::guard('gswallet')->user()->photo) }}" alt="Student Photo"> </li>
			<li class="list-group-item clearfix">
				<label>Password: </label> 
				<i class="fa fa-circle"></i><i class="fa fa-circle"></i><i class="fa fa-circle"></i><i class="fa fa-circle"></i><i class="fa fa-circle"></i>
				<span class="pull-right"><button id="" class="change_pass btn btn-theme" style="color:#fff">Change Password</button></span>
			</li>
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
						<button type="reset" id="cancel_change" class="btn btn-theme2 change_pass" style="color:#fff">Cancel</button>
					</center>
				</form>
			</li>
			<!-- <li class="list-group-item"><label>Fullname:</label> {{ Auth::guard('gswallet')->user()->fullname }} </li>
			<li class="list-group-item"><label>Email ID: </label> {{ Auth::guard('gswallet')->user()->email_id }}</li>
			<li class="list-group-item"><label>Phone No: </label> {{ Auth::guard('gswallet')->user()->mobile_no }}</li> -->
		</div>
	</div>
</div>
@stop
@section('script')
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
       	    var url="{{ URL::route('gswebapp.profile.changepassword') }}";
       	    var token="{{ csrf_token() }}";
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

                          	window.location.assign('/global-student');
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

  </script> 
@stop  

@section('style')
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

@stop	  