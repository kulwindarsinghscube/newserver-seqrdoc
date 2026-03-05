@extends('webapp.layouts.layout')
@section('content')
<div class="">
	<div class="row">
		<div class="col-xs-12 col-md-6 col-md-offset-3">
			<h2 style="color:#fff;font-family:roboto;">Session Manager</h2>
			<h5 style="color:#fff;font-family:roboto;">Manage your logged in sessions for all devices from here.<br><br></h5>
			<div id="ajaxResponse"></div>
		</div>
	</div>
</div>

@stop

@section('style')
<style>
body{
	background-image: linear-gradient(to bottom, rgba(34, 143, 176, 0.3), rgba(111, 128, 206, 0.4)), url(images/bg2.jpg);
}
</style>
@stop
@section('script')
<script type="text/javascript">
	$(document).ready(function(){
		load();
	});
	function load(){
		$('.loader').removeClass('hidden');
		ajaxURL = "<?= URL::route('webapp.getSessions') ?>";
		var token="{{ csrf_token() }}";

		// $.post(ajaxURL,function(data){
		// 	$('#ajaxResponse').html(data);
		// 	$('[data-toggle="tooltip"]').tooltip(); 
		// 	$('.loader').addClass('hidden');
		// 	// load2();
		// });
		$.ajax({
			url : ajaxURL,
			type : 'POST',
			data : {'_token':token},
            success:function(data){  
            	
            	$('#ajaxResponse').html(data);
				$('[data-toggle="tooltip"]').tooltip(); 
				$('.loader').addClass('hidden');	
				load2();
            }
		});
	}
	function load2(){
	$('.logout_device').click(function(){
		$sesskey = $(this).data('sesskey');
		bootbox.confirm('Are you sure to logout from this device?',function(result){
			if(result){
				$('.loader').removeClass('hidden');
				ajaxURL = "<?= URL::route('webapp.logoutsingle') ?>";
				var token="{{ csrf_token() }}";
				$.ajax({

					url : ajaxURL,
					type : 'POST',
					data : {'_token':token,'sesskey':$sesskey},
		            success:function(data){  
		            	
		            	load();
						$('.loader').addClass('hidden');
						toastr["success"]('Logged out from selected device successfully');
						if(data == 0){
							setTimeout(function(){window.location.replace('index.php')},400);
						}
		            }
				});
			}
		});
	});
	
	$('.logout_all').click(function(){
		bootbox.confirm('Are you sure to logout from all devices?',function(result){
			if(result){

				ajaxURL = "<?= URL::route('webapp.logoutAll') ?>";				
				$.ajax({
					url : ajaxURL,
					type : 'POST',
					data : {'_token':token},
		            success:function(data){  
		            	
		            	load();
						toastr["success"]('Logged out from all devices successfully');
						setTimeout(function(){window.location.replace('index.php')},400);
		            }
				});
			}
		});
	});
}
</script>
@stop