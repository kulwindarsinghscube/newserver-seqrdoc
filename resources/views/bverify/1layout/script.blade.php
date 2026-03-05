	<!-- jQuery -->
    <script src="{{asset('backend/js/jquery.min.js')}}"></script>

	<!-- datepicker -->
	<script src="{{asset('backend/js/moment.js')}}"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="{{asset('backend/js/bootstrap.min.js')}}"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="{{asset('backend/js/metisMenu.min.js')}}"></script>

	<!-- JQuery Validation -->
	<script src="{{asset('backend/js/jquery.mockjax.js')}}"></script>
	<script src="{{asset('backend/js/jquery.form.js')}}"></script>
	<script src="{{asset('backend/js/jquery.validate.js')}}"></script>

	<!-- Data Tables -->
	<script src="{{asset('backend/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('backend/js/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{asset('backend/js/dataTables.responsive.js')}}"></script>
	<!-- bootbox -->
	<script src="{{asset('backend/js/bootbox.min.js')}}"></script>

	<!-- datepicker -->
	<script src="{{asset('backend/js/bootstrap-datetimepicker.min.js')}}"></script>

	<!-- animateNumber -->
	<script src="{{asset('backend/js/jquery.animateNumber.js')}}"></script>

	<!-- selectpicker -->
	<script src="{{asset('backend/js/bootstrap-select.js')}}"></script>


	<!-- ToastrJS -->
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js" type="text/javascript"></script> -->
	<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"  type="text/css"> -->
	<script src="{{asset('backend/js/toastr.min.js')}}"></script>    <!-- Custom Theme JavaScript -->
    <script src="{{asset('backend/js/sb-admin-2.js')}}"></script>

	<script>
$(document).ready(function(){
	$('.loader').addClass('hidden');
	 $('[data-toggle="tooltip"]').tooltip();

	 $("#logout").click(function(e){
		e.preventDefault();

		var ajaxURL = "/verify/logout";
		var token = "{{csrf_token()}}";
		$.post(
			ajaxURL,{'type':"logout",'_token':token},
			function(data){
					console.log(data)
				if(data.type == 'success'){
					window.location.href = "/verify/login";
				}
			},'json'
		);
	});

	function ajaxSession() {
		
		$ajaxURL = '';

		$.post($ajaxURL,{
			/*'sesskey':$sesskey,
			'user_id':$user_id*/
		},function(data){
			if(data.is_logged == '1'){
				//console.log('is logged');
			}else{
				$("#logout").trigger('click');
			}
		},'JSON');

	}
	//setInterval(ajaxSession, 20000);
});

$(function () {
    /* START OF DEMO JS - NOT NEEDED */
    if (window.location == window.parent.location) {
        $('#fullscreen').html('<span class="glyphicon glyphicon-resize-small"></span>');
        $('#fullscreen').attr('href', 'http://bootsnipp.com/mouse0270/snippets/PbDb5');
        $('#fullscreen').attr('title', 'Back To Bootsnipp');
    }
    $('#fullscreen').on('click', function(event) {
        event.preventDefault();
        window.parent.location =  $('#fullscreen').attr('href');
    });
    $('#fullscreen').tooltip();
    /* END DEMO OF JS */

    $('.navbar-toggler').on('click', function(event) {
		event.preventDefault();
		$(this).closest('.navbar-minimal').toggleClass('open');
	})
});
	</script>