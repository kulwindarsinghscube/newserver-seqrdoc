@extends('admin.layout.layout')
@section('content')
	<div class="container" style="width: 1350px !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-file"></i> Old Record</h1>	
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
						<?php if ($role_id==2){ ?>
                        <div class="col-xs-12">                
                            <div class="card">                                
                                <div class="card-body" style="padding:20px;">
                                
                                <div class="row">
                                    <form method="post" id="processApiForm" class="form-horizontal" novalidate="novalidate">
                                    <input type="hidden" value="<?= csrf_token()?>" name="_token">                                    
                                    <input type="hidden" name="record_id" id="record_id">                                    
                                    <div class="col-xs-12">  
                                        <div class="form-group">
                                            <div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">Certificate Number</label>
													<input type="text" class="form-control" id="certificate_no" name="certificate_no" readonly>
												</div>
												<div class="col-sm-4">
													<label class="control-label">Vehicle Registration Number</label>
													<input type="text" class="form-control" id="vehicle_reg_no" name="vehicle_reg_no">
												</div>
												<div class="col-sm-4">
													<label class="control-label">Chassis Number</label>
													<input type="text" class="form-control" id="chassis_no" name="chassis_no" readonly>
												</div>
                                            </div>											
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">Type of Governor</label>
													<!--{!! Form::select('type_of_governor', $governors,[], array('class' => 'form-control select2', 'id' => 'type_of_governor')) !!}-->
													<select class="form-control select2" name="type_of_governor" id="type_of_governor">
													<option value="">Select</option>
													@foreach($governors as $key => $value)
													<option value="{{$key}}">{{$value}}</option>
													@endforeach		
													</select>														
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Unit Serial Number</label>									
													<input type="text" class="form-control" id="unit_sr_no" name="unit_sr_no" readonly>
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Supplier</label>
													<br />
													{{$supplier_name}} 
													<input type="hidden" class="form-control" id="supplier" name="supplier" value="{{$supplier_id}}">
												</div>
											</div>
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">Make</label>
													<input type="text" class="form-control" id="make" name="make" readonly>
												</div>
												<div class="col-sm-4">
													<label class="control-label">Model</label>
													<input type="text" class="form-control" id="model" name="model" readonly>
												</div>
												<div class="col-sm-4">
													<label class="control-label">HC Number</label>
													<input type="text" class="form-control" id="hc_no" name="hc_no">
												</div>
                                            </div>
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label" for="temp">Date of Installation</label>
													<input type="text" class="form-control installation_date" id="date_of_installation" name="date_of_installation" readonly>
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Date of Expiry</label>
													<input type="text" class="form-control expiry_date" id="date_of_expiry" name="date_of_expiry" readonly>
												</div>
                                            </div>
                                        </div>         
                                    </div>
                                    <div class="col-xs-12"><p></p></div>
                                    <div class="col-xs-12 text-center"> 
                                    <button type="submit" class="btn btn-primary" id="btn_import">Submit</button> 
                                    </div>
                                    </form>
                                    
                                    <div class="col-xs-12"><div id="progress" style="border:1px solid gray; border-radius: 4px; display:none;"></div></div>
                                    <div class="col-xs-12"><div id="show_msg"></div></div>
                                    <div class="col-xs-12"><div id="show_records"></div></div>
                                    <div class="col-xs-12"><p><div id="show_msg2" class="text-center"></p></div></div>
                                </div>                                
                                </div>                                
                            </div> 
                        </div> 
						<?php } ?>
						
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
	$domain = \Request::getHost();
	$subdomain = explode('.', $domain);
	$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
	$path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
	$print_filepath=$path.$subdomain[0]."/backend/tcpdf/examples/";
	$verify_filepath=$path.$subdomain[0]."/backend/pdf_file/"; 
?>    
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
<script type="text/javascript">
	$('.installation_date').datetimepicker({
		format: 'DD-MM-YYYY'
	});	
	$('.expiry_date').datetimepicker({
		format: 'DD-MM-YYYY'
	});	
	roleid=<?=$role_id?>;
	$token = '<?= csrf_token()?>';
    
	//$.validator.setDefaults({ ignore: [] }); $('select').change(function(){ $('select').valid(); }) 
    $('.select2').change(function(){
        $(this).valid()
    });   
	
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
            certificate_no: { required: true },
            vehicle_reg_no:{required: true},    
            chassis_no:{required: true},    
            type_of_governor:{required: true},    
            unit_sr_no:{required: true},    
            supplier:{required: true},    
            make:{required: true},    
            model:{required: true},    
            hc_no:{required: true},    
            date_of_installation:{required: true},    
            date_of_expiry:{required: true},    
        },
        messages:{            
            certificate_no:{required:'Certificate Number is required'},
            vehicle_reg_no:{required:'Enter Registration Number'},
            chassis_no:{required:'Chassis Number is required'},
            type_of_governor:{required:'Select Type of Governor'},
            unit_sr_no:{required:'Unit Serial Number is required'},
            supplier:{required:'Supplier is required'},
			make:{required:'Make is required'},
			model:{required:'Model is required'},
			hc_no:{required:'HC Number is required'},
            date_of_installation:{required:'Installation Date is required'},
            date_of_expiry:{required:'Expiry Date is required'},
        },
        submitHandler: function(form){            
            $('#show_msg').hide();
            $('#show_msg2').hide();
            var token = "<?= csrf_token()?>"; 
            $('#processApiForm').ajaxSubmit({
                url:'<?= route('supplier-uploadexcel.recallData')?>',
                type: "POST",
                dataType: "JSON",
                data:$("#processApiForm").serializeArray(),
                //processData: false,
                //contentType:false,                
                beforeSubmit:function(formData,jqform, options){
                    $('#show_msg').show();
					$('#show_msg2').show();
					$("#show_msg2").html("Please wait...");
                    //$('#btn_import').hide();
                },clearForm:false,dataType:'json',success:function(resObj){
                    if(resObj.success == true){
                        $('#show_msg2').show();
                        $("#show_msg2").html(resObj.message);
                        $('#btn_import').show()
                        //$("#processApiForm")[0].reset();
						$("select.select2").select2('data', {}); // clear out values selected
						$("select.select2").select2({ allowClear: true }); // re-init to show default status
						//document.getElementById("vehicle_reg_no").selectedIndex = 0;
						//document.getElementById("supplier").selectedIndex = 0;
						oTable.ajax.reload();
                    }else{
                        $('#btn_import').show();
                        $('#show_msg2').show();
                        $("#show_msg2").html(resObj.message);
						//alert(resObj.message);
                        //window.location.reload();
                    }
                }

            });
        }
    })    
    
	
	$(document).ready(function () {
		$('#vehicle_reg_no').on('input', function () {
			$('#record_id').val('');
			$('#certificate_no').val('');
			$('#chassis_no').val('');
			$('#unit_sr_no').val('');
			$('#make').val('');
			$('#model').val('');
			$('#hc_no').val('');
			$('#date_of_installation').val('');
			$('#date_of_expiry').val('');
			var supplier_id = $('#supplier').val();
			var vno = this.value;
			//console.log(vno.length);
			$.ajax({
				url: '{{ route('sgrsa-previouscertificate.searchVehicleNo') }}?vno='+vno+'&supplier_id='+supplier_id,
				type: 'get',
				success: function (res) {
					var count = Object.keys(res).length;
					console.log(count);
					if(count>0){
						$('#record_id').val(res.id);
						$('#certificate_no').val(res.certificate_no);
						$('#chassis_no').val(res.chassis_no);
						$('#unit_sr_no').val(res.unit_sr_no);
						$('#make').val(res.make);
						$('#model').val(res.model);
						$('#hc_no').val(res.hc_no);
						var date_of_installation = moment(res.date_of_installation).format('DD-MM-YYYY');
						var date_of_expiry = moment(res.date_of_expiry).format('DD-MM-YYYY');
						$('#date_of_installation').val(date_of_installation);
						$('#date_of_expiry').val(date_of_expiry);
					}else{
						//alert("Record not found.");
						toastr.error("Record not found.");
					}
				}
			});
		});	
	});	
	
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
.select2-container .select2-selection--single {
    height: 34px !important;
}
</style>
@stop