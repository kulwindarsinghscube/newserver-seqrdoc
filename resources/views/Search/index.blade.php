<html>
<head>
		<link rel="stylesheet" href="{{asset('backend/css/bootstrap.min.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/dataTables.bootstrap.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/dataTables.responsive.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/sb-admin-2.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/font-awesome.min.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/bootstrap-datetimepicker.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/animate.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/bootstrap-select.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/style.min.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/AbelRoboto.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/toastr.min.css')}}">
		<link rel="stylesheet" href="{{asset('backend/css/custom.css')}}">


		<!--  // acl permission css -->
		<link rel="stylesheet" type="text/css" href="{{asset('backend/css/multi-select.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('backend/css/select2.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('backend/css/binary.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('backend/css/owl.theme.default.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('backend/css/owl.carousel.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('backend/jstree/dist/themes/default/style.min.css')}}">
	</head>
 <body>

<div class="main-container" id="main-container">
  <div class="main-content">
        <div class="main-content-inner">
            <div id="main-page-data-holder" class="page-content">
<div class="row">
    <div class="col-xs-12">
        <div class="widget-box">

            <div class="widget-body">
                <div class="widget-main">
                        <fieldset>
                          <form id="form-result" class="form-horizontal" novalidate="novalidate">
                                
                                <div class="form-group">
                                    <div class="col-md-5">
                                    </div>
                                    <div class="col-md-5">
                                      
                                    </div>
                                    <div class="col-md-1">

                                    </div>
                                </div>

                                <div class="form-group" >
                                    <div class="col-md-3">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" data-rule-required="true" class="form-control textbox"
                                               placeholder="College Number"
                                               name="college_number"
                                               id="college_number" aria-required="true"
                                               value="" data-rule-required="true">
                                    </div> 
                                    <div class="col-md-3">
                                    </div>                              
                                </div>
                                <div class="form-group">
                                    <div class="col-md-5">
                                    </div>
                                    <div class="col-md-6" style="margin-left: 30px;">
                                      <select class="custom-select" id="course" name="course" data-rule-required="true">
																				  <option value="" selected>Select Course</option>
																				  <option value="MET">MET</option>
																				  <option value="CMS">CMS</option>
																				</select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-5">
                                    </div>
                                    <div class="col-md-6" style="margin-left: 30px;">
                                      
                                      <button class="btn btn-primary" id="btn-check">
                                        Check Result
                                      </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12 table-responsive" id="response" style="text-align: center;">
                                      
                                      
                                    </div>
                                </div>
                            </form>
                        </fieldset>
                   
                </div>
            </div>
        </div>
        
    </div>

    
</div>
</div>
</div>
</div>
</div>

<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
    <i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
</a>
</div>

</body>
<script src="{{asset('backend/js/jquery.min.js')}}"></script>
<script src="{{asset('backend/js/jquery.validate.js')}}"></script>
<script src="{{asset('backend/js/additional-methods.min.js')}}"></script>
<script src="{{asset('backend/js/jquery.timepicker.js')}}"></script>
<script src="{{asset('backend/js/readmore.js')}}"></script>
<script src="{{asset('backend/js/modernizr-custom.js')}}"></script>
<script src="{{asset('backend/js/moment.js')}}"></script>
<script src="{{asset('backend/js/bootstrap.min.js')}}"></script>
<script src="{{asset('backend/js/metisMenu.min.js')}}"></script>
<script src="{{asset('backend/js/jquery.mockjax.js')}}"></script>
<script src="{{asset('backend/js/jquery.form.js')}}"></script>
<script src="{{asset('backend/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backend/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{asset('backend/js/dataTables.responsive.js')}}"></script>
<script src="{{asset('backend/js/bootbox.min.js')}}"></script>
<script src="{{asset('backend/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{asset('backend/js/jquery.animateNumber.js')}}"></script>
<script src="{{asset('backend/js/bootstrap-select.js')}}"></script>
<script src="{{asset('backend/js/sb-admin-2.js')}}"></script>
<script src="{{asset('backend/js/toastr.min.js')}}"></script>
 
<script src="{{asset('backend/js/jquery.form.min.js')}}"></script>
<script src="{{asset('backend/js/select2.full.min.js')}}"></script>
<script src="{{asset('backend/js/select2.min.js')}}"></script>
<script src="{{asset('backend/js/select2-data.js')}}"></script>
<script src="{{asset('backend/js/jquery.multi-select.js')}}"></script>
<script src="{{asset('backend/jstree/dist/jstree.min.js')}}"></script>
<script>
$(document).ready(function(){


		var token="{{ csrf_token() }}";
		$("#btn-check").click(function(e){
			e.preventDefault();
			$('#response').html('');
				var course = $('#course').val();
				//var input = $('#college_number').val();
				if(course != '')
				{	
					if (!$('#form-result').valid())
					{
						$("html, body").animate({ scrollTop: 0 }, "slow");
			            return false;
					}
					else{
						var formData = new FormData($('#form-result')[0]);
						formData.append("_token", token);

								$.ajax({
				            url: '{{URL::route("search.result")}}',
				            type: 'POST',
				            data: formData,
				            success: function (data) {
				                if(data.type == 'success'){
				                	  $('#response').html('<H2>'+data.message+'</H2>');
				                }else{
				                    $('#response').html('<H2><span style="color:red;">'+data.message+'</span></H2>')
				                }
				            },
				            cache: false,
				            contentType: false,
				            processData: false,
				            dataType:'JSON'
				        });
					}
				}else{
					 toastr["error"]('Please Select Course');
				}
		});

	
});
</script>
</html>