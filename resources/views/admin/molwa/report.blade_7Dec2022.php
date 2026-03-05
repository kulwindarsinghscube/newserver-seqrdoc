@extends('admin.layout.layout')
@section('content')
	<div class="container" style="width: 1350px !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-file"></i> Molwa Report</h1>	
							</div>
						</div>						
						<div class="modal fade" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="myModalLabel"></h4>
									</div>
									<div class="modal-body">									
										
									</div>
								</div>
							</div>
						</div>
                        <div class="col-xs-12">                
                            <div class="card">                                
                                <div class="card-body" style="padding:20px;">                                
                                <div class="row">
                                    <form method="post" id="processApiForm" class="form-horizontal" novalidate="novalidate">
                                    <input type="hidden" value="<?= csrf_token()?>" name="_token"  id="token_val">
                                    <div class="col-xs-12">  
                                        <div class="form-group">
                                            <div class="col-sm-4">
                                                <label class="control-label" for="temp">District</label>
                                                <select class="form-control" id="R_district_selector" name="district">
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="control-label" for="temp">Upazila</label>
                                                <select class="form-control" id="R_upazila_selector" name="upazila">
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="control-label" for="temp">Alive/Dead</label>
                                                <select class="form-control" id="is_alive" name="is_alive">
                                                    <option value="হ্যাঁ" data-tempid="yes">Alive</option>
                                                    <option value="না" data-tempid="no">Dead</option>
                                                </select>                                            
                                            </div>
                                        </div>         
                                    </div>
                                    <div class="col-xs-12">  
                                        <div class="form-group"> 
                                            <div class="col-sm-8">
                                                <label class="control-label">Report Name</label>
                                                <select class="form-control" id="report_name" name="report_name">
                                                    <option value="">Select</option>
                                                    <option value="1">Register Book of Smart ID Card</option>
                                                    <option value="2">Register Book of Digital Certificate</option>
                                                    <option value="3">Delivery Challan (Digital Certificate)</option>
                                                    <option value="4">Delivery Challan (Smart ID Card)</option>
                                                    <option value="5">Digital Certificate</option>
                                                    <option value="6">Digital Smart ID Card</option>
                                                </select>
                                            </div>                                            
                                            <div class="col-sm-4" style="margin-top:25px;">
                                                <button type="submit" class="btn btn-primary" id="btn_export">Export</button>                                        
                                            </div>
                                        </div>         
                                    </div>
                                    </form>
                                    <div class="col-xs-12"><p></p></div>
                                    <div class="col-xs-12"><div id="show_msg"></div></div>
                                    <div class="col-xs-12"><div id="show_records"></div></div>
                                </div>                                
                                </div>                                
                            </div> 
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
    
<div class="modal fade" id="viewInfo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">			
        <div class="modal-content" style="width: 1200px;left: -440px;">
            <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="z-index:99999;opacity: 1;position: absolute;right: 0px;top: -4px;">X</button>-->
            
            <div class="modal-body" id="ajaxContent"></div>
            <div class="modal-footer">
            	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>             
        </div>
    </div>
</div>    
@stop
@section('script')
<script src="{{asset('backend/js/moment.min.js')}}"></script>
<script src="{{asset('backend/js/district_report.js')}}"></script>
<script type="text/javascript">
	//$token = '<?= csrf_token()?>';
    $('#processApiForm').validate({
		errorElement: 'span',
		errorClass: 'help-inline',
		focusInvalid: false,
		invalidHandler: function (event, validator) { //display error alert on form submit
			$('.alert-error', $('#processApiForm')).show();
		},
		highlight: function (e) {
			$(e).closest('.control-group').removeClass('info').addClass('error');
		},
		success: function (e) {
			$(e).closest('.control-group').removeClass('error').addClass('info');
			$(e).remove();
		},
		errorPlacement: function (error, element) {
			if (element.is(':checkbox') || element.is(':radio')) {
				var controls = element.closest('.controls');
				if (controls.find(':checkbox,:radio').length > 1)
					controls.append(error);
				else
					error.insertAfter(element.nextAll('.lbl:eq(0)').eq(0));
			}
			else if (element.is('.select2')) {
				error.insertAfter(element.siblings('[class*="select2-container"]:eq(0)'));
			}
			else if (element.is('.chzn-select')) {
				error.insertAfter(element.siblings('[class*="chzn-container"]:eq(0)'));
			}
			else
				error.insertAfter(element);
		},        
        rules:{            
            district: { required: true },
            upazila: { required: true },
            report_name:{required: true}, 
        },
        messages:{            
            district:{required:'Select District'},
            upazila:{required:'Select Upazila'},
            report_name:{required:'Select Report Name'},
        },
        submitHandler: function(form){            
            event.preventDefault();
			$('#show_msg').hide(); 
            var interval = null;            
			$('#processApiForm').ajaxSubmit({
                url:'<?= route('molwa-certificate.molwaReportGet')?>',
                type: "POST",                
                data:$("#processApiForm").serializeArray(),                           
                beforeSubmit:function(formData,jqform, options){
                    $('#show_msg').show();
					$("#show_msg").html("Please wait...");
                    //$('#btn_export').hide();   
                },clearForm:false,dataType:'json',success:function(resObj){
                    if(resObj.success == true){
                        $('#show_msg').show();
                        $("#show_msg").html(resObj.message);
                        $('#btn_export').show()
                    }else{
                        $('#btn_export').show();
                        alert(resObj.message);
                    }
                }
            });			
        }
    })    
	

</script>
@stop
@section('style')
<style type="text/css">
#example th{
border: 1px solid #dee3ed;
}
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
#progress .bar {
    background-color: #0052CC;
    color: white;
    height: 18px;
    font-size: 14px;
    text-align: center;
} 
.ibar, .cbar {
    background-color: #0052CC;
    color: white;
    height: 18px;
    font-size: 14px;
    text-align: center;
} 
blockquote {
    padding: 2px 10px;
    margin: 0 0 6px;
    font-size: 14px;
    border-left: 5px solid #0052CC;
    background-color: #e8eff9;
}
</style>
@stop