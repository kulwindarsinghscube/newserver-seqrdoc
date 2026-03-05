@extends('StudentWallet.layouts.layout')
@section('content')
 <!-- Bootstrap Select and Table -->
<div class="container">
    <h3>Select Institute</h3>
    <div class="row">
    	<div class="col-md-4">
    <select class="form-control" id="institute-select">
        <option value="">-- Select Institute --</option>
        @foreach($institutes as $institute)
            <option value="{{ $institute->site_id }}">{{ $institute->site_url }}</option>
        @endforeach
    </select>
    </div>
    </div>
<br><br>
    <table id="courses-table" class="table table-bordered">
        <thead>
            <tr>
                <th>Serial No.</th>
                <th>Created Date</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>

@stop
@section('script')
<script>
var table;

$('#institute-select').on('change', function () {
    var instituteId = $(this).val();
    var fullDomain = $('#institute-select option:selected').text(); // e.g. zeal.seqrlocal1.com
    var subdomain = fullDomain.split('.')[0];
    console.log(subdomain);
    // if (!instituteId) return; // prevent if no selection
    if (!instituteId) {
        // If empty selection, clear the table
        $('#courses-table').DataTable().clear().draw();
        return;
    }

    if ($.fn.DataTable.isDataTable('#courses-table')) {
        // If already initialized, just reload with new param
        table.ajax.reload();
    } else {
        // Initialize first time
        table = $('#courses-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("get.institute.certificates") }}',
                data: function (d) {
                    d.institute_id = $('#institute-select').val();
                    d.subdomain = subdomain;
                    d.fullDomain = fullDomain;
                }
            },
            columns: [
                { data: 'serial_no', name: 'serial_no' },
                { data: 'created_at', name: 'created_at' },
                {
		            data: 'action',
		            name: 'action',
		            orderable: false,
		            searchable: false
		        }
            ]
        });
    }
});
</script>

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
/*	.viewpass{
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
}*/	
</style>

@stop	  