@extends('verify.layout.layout')
@section('content')

<div class="row">
	<div class="col-xs-12 col-md-6 col-md-offset-3">
		<h2 style="color:#000;font-family:roboto;">Session Manager</h2>
		<h5 style="color:#000;font-family:roboto;">Manage your logged in sessions for all devices from here.<br><br></h5>
		<div id="ajaxResponse"></div>
	</div>
</div>
</div>
@stop
@section('script')
<script>
	$(document).ready(function(){
		load();
	});

	$('a[href^="sessionmanager"]').parent().addClass('active');
	$('[data-toggle="tooltip"]').tooltip();

	setInterval(load,20000);

	function load(){
		$('.loader').removeClass('hidden');
		var token = "{{ csrf_token() }}";
		var type = 'getSessions';

		$.ajax({

			url: "{{ URL::route('raisoni.sessiondata') }}",
	        type: 'post',
	        data:{
	        	type:type,
	            
	            _token:token,
	          
	        },
	        
	        success: function(data) {

	        	$('#ajaxResponse').html(data);
				$('[data-toggle="tooltip"]').tooltip();
				$('.loader').addClass('hidden');
				load2();
				
	        }
		})

	}

	function load2(){

		$('.logout_device').click(function(){
			var sesskey = $(this).data('sesskey');	
			bootbox.confirm('Are you sure to logout from this device?',function(result){
				if(result){

					$('.loader').removeClass('hidden');
					var type = 'logoutSingle';
					var token = "{{ csrf_token() }}";
					$.ajax({

						url: "{{ URL::route('raisoni.sessiondata') }}",
				        type: 'post',
				        data:{
				        	type:type,
				            sesskey:sesskey,
				            _token:token
				          
				        },				       
				        success: function(data) {
				  	    	load();
				        	$('.loader').addClass('hidden');
							toastr["success"]('Logged out from selected device successfully');
							if(data == 0){
								setTimeout(function(){window.location.replace()},400);
							}	
				        }
					});
				}
			});
		});

		$('.logout_all').click(function(){
			bootbox.confirm('Are you sure to logout from all devices?',function(result){
				if(result){
					var token = "{{ csrf_token() }}";
					var type = 'logoutAll';

					$.ajax({

						url: "{{ URL::route('raisoni.sessiondata') }}",
				        type: 'post',
				        data:{
				        	type:type,				            
				            _token:token,
				          
				        },
				        
				        success: function(data) {

				        	load();
							toastr["success"]('Logged out from all devices successfully');
							setTimeout(function(){window.location.replace('home')},400);	
				        }
					});
				}
			});
		});
	}
</script>
@stop