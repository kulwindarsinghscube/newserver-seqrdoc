<?php
$certificate_no=$record->certificate_no;
$vehicle_reg_no=$record->vehicle_reg_no;
$chassis_no=$record->chassis_no;
$date_of_inspection=date('d-m-Y', strtotime($record->date_of_inspection));
$date_of_expiry=date('d-m-Y', strtotime($record->date_of_expiry));
$town=$record->town;
$unit_sr_no=$record->unit_sr_no;
$date_of_installation=date('d-m-Y', strtotime($record->date_of_installation));
$agent_name=$record->agent_name;
$tele_no=$record->tele_no;
$date_of_issue=date('d-m-Y', strtotime($record->date_of_issue));
$model=$record->model;
$make=$record->make;
$vehicle_owner=$record->vehicle_owner;
$business_reg=$record->business_reg;
$pin_no=$record->pin_no;
$vat_no=$record->vat_no;
$company_address=$record->company_address;
$certify_by=$record->certify_by;
$engine_no=$record->engine_no;
$po_box=$record->po_box;
$code=$record->code;
$email=$record->email;
?>
	<div class="container" style="width: 100% !important;">
		<div class="col-xs-12">
			<table class='table table-bordered'>
				<tr><th>Certificate No.</th><td><?php echo $certificate_no; ?></td>
				<th>Vehicle Reg. No.</th><td><?php echo $vehicle_reg_no; ?></td>
				<th>Chassis No.</th><td><?php echo $chassis_no; ?></td>
				<th>Inspection Date</th><td><?php echo $date_of_inspection; ?></td>
				</tr>
				<tr><th>Expiry Date</th><td><?php echo $date_of_expiry; ?></td>
				<th>Town</th><td><?php echo $town; ?></td>
				<th>Unit Serial No.</th><td><?php echo $unit_sr_no; ?></td>
				<th>Installation Date</th><td><?php echo $date_of_installation; ?></td>
				</tr>
				<tr><th>Agent</th><td><?php echo $agent_name; ?></td>
				<th>Phone</th><td><?php echo $tele_no; ?></td>
				<th>Issue date</th><td><?php echo $date_of_issue; ?></td>
				<th>Model</th><td><?php echo $model; ?></td>
				</tr>
				<tr><th>Make</th><td><?php echo $make; ?></td>
				<th>Vehicle Owner</th><td><?php echo $vehicle_owner; ?></td>
				<th>Business Reg.</th><td><?php echo $business_reg; ?></td>
				<th>PIN</th><td><?php echo $pin_no; ?></td>
				</tr>
				<tr><th>VAT</th><td><?php echo $vat_no; ?></td>
				<th>Company Address</th><td><?php echo $company_address; ?></td>
				<th>Certify By</th><td><?php echo $certify_by; ?></td>
				<th>Engine No.</th><td><?php echo $engine_no; ?></td>
				</tr>
				<tr><th>PO Box</th><td><?php echo $po_box; ?></td>
				<th>Code</th><td><?php echo $code; ?></td>
				<th>Email</th><td colspan="3"><?php echo $email; ?></td>
				</tr>
			</table>		
		</div>
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
										
						<?php if ($role_id==3){ ?>
                                      
                            <div class="card" style="margin: 0 0;">                                
                                <div class="card-body" style="padding:10px;">
                                
                                <div class="row">
                                    <form method="post" id="processRenewForm" class="form-horizontal" novalidate="novalidate">
                                    <input type="hidden" value="{{$record_id}}" name="record_id">                                    
                                    <input type="hidden" value="<?= csrf_token()?>" name="_token">                                    
                                    <div class="col-xs-12">  
                                        <div class="form-group">
                                            <div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">Certificate Number</label><br />
													<input type="text" class="form-control" id="certificate_no" name="certificate_no" value="<?php echo $certificate_no; ?>" style="width:100%;">
												</div>
												<div class="col-sm-4">
													<label class="control-label">Vehicle Registration Number</label><br />
													<input type="text" class="form-control" id="vehicle_reg_no" name="vehicle_reg_no" value="<?php echo $vehicle_reg_no; ?>" style="width:100%;">
												</div>
												<div class="col-sm-4">
													<label class="control-label">Chassis Number</label><br />
													<input type="text" class="form-control" id="chassis_no" name="chassis_no" value="<?php echo $chassis_no; ?>" style="width:100%;">
												</div>
                                            </div>											
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">Type of Governor</label>
													<br />
													<select class="form-control select2" name="type_of_governor" id="type_of_governor" style="width:100%;">
													<option value="">Select</option>
													@foreach($governors as $key => $value)
													<option value="{{$key}}">{{$value}}</option>
													@endforeach		
													</select>													
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Unit Serial Number</label><br />							
													<select class="form-control select2" name="unit_sr_no" id="unit_sr_no" value="<?php echo $unit_sr_no; ?>" style="width:100%;">
													</select>				
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Agent</label>
													<br />
													<select class="form-control select2" name="agent" id="agent" style="width:100%;">
													<option value="">Select</option>
													@foreach($agents as $key => $value)
													<option value="{{$key}}" <?php if($supplier_id==$key){ echo 'Selected';} ?>>{{$value}}</option>
													@endforeach		
													</select>
													<input type="hidden" class="form-control" id="supplier" name="supplier" value="{{$supplier_id}}">
													
												</div>
											</div>
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">Make</label><br />
													<input type="text" class="form-control" id="make" name="make" value="<?php echo $make; ?>" style="width:100%;">
												</div>
												<div class="col-sm-4">
													<label class="control-label">Model</label><br />
													<input type="text" class="form-control" id="model" name="model" value="<?php echo $model; ?>" style="width:100%;">
												</div>
												<div class="col-sm-4">
													<label class="control-label">HC Number</label><br />
													<input type="text" class="form-control" id="hc_no" name="hc_no" style="width:100%;">
												</div>
                                            </div>
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label" for="temp">Date of Installation</label><br />
													<input type="text" class="form-control installation_date" id="date_of_installation" name="date_of_installation" value="<?php echo $date_of_installation; ?>" style="width:100%;">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Date of Expiry</label><br />
													<input type="text" class="form-control expiry_date" id="date_of_expiry" name="date_of_expiry" value="<?php echo $date_of_expiry; ?>" style="width:100%;">
												</div>
                                            </div>
                                        </div>         
                                    </div>
                                    <div class="col-xs-12"><p></p></div>
                                    <div class="col-xs-12 text-center"> 
                                    <button type="submit" class="btn btn-primary" id="btn_renew">Submit</button> 
                                    </div>
                                    </form>
                                    
                                    <div class="col-xs-12"><div id="show_msg3"></div></div>
                                    <div class="col-xs-12"><p><div id="show_msg4" class="text-center"></p></div></div>
                                </div>                               
                                </div>                                
                            </div> 
                        
						<?php } ?>		
						
					
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
<link rel="stylesheet" type="text/css" href="{{asset('backend/css/select2.min.css')}}">
<style type="text/css">
.bootstrap-datetimepicker-widget.dropdown-menu {
  margin: 2px 0;
  padding: 4px;
  width: 27em;
}
.bootstrap-datetimepicker-widget table th {
	font-size: .8em;
	border: none !important;
	box-shadow: none;
	outline: none;  
}
.bootstrap-datetimepicker-widget .datepicker-decades .decade {
  font-size: .9em;
  width:6em;
}
#example_length label{
  display:none;
}
.help-inline{
  color:red;
  font-weight:normal;
}

</style>
<script src="{{asset('backend/js/moment.min.js')}}"></script>
  <script src="{{asset('backend/js/select2.full.min.js')}}"></script>
  <script src="{{asset('backend/js/select2.min.js')}}"></script>
  <script src="{{asset('backend/js/select2-data.js')}}"></script>
<script type="text/javascript">
	$('.installation_date').datetimepicker({
		format: 'DD-MM-YYYY'
	});
	$('.expiry_date').datetimepicker({
		format: 'DD-MM-YYYY'
	});	
	roleid=<?=$role_id?>;
	$token = '<?= csrf_token()?>';

	$('#example tbody').on('click', '#btn_renew', function (ep) {		
		$('#processRenewForm').submit(function(e) {
			e.preventDefault();
		}).validate({
		errorElement: 'span',
		errorClass: 'help-inline',
		focusInvalid: false,
		invalidHandler: function (event, validator) { //display error alert on form submit
			$('.alert-error', $('#processRenewForm')).show();
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
            agent:{required: true},    
            supplier:{required: true},    
            make:{required: true},    
            model:{required: true},    
            hc_no:{required: true},    
            date_of_installation:{required: true},    
            date_of_expiry:{required: true},     
        },
        messages:{            
            certificate_no:{required:'Enter Certificate Number'},
            vehicle_reg_no:{required:'Enter Registration Number'},
            chassis_no:{required:'Enter Chassis Number'},
            type_of_governor:{required:'Enter Type of Governor'},
            unit_sr_no:{required:'Select Unit Serial Number'},
            agent:{required:'Select Agent'},
            supplier:{required:'Select Supplier'},
			make:{required:'Enter Make'},
			model:{required:'Enter Model'},
			hc_no:{required:'Enter HC Number'},
            date_of_installation:{required:'Select Installation Date'},
            date_of_expiry:{required:'Select Expiry Date'},
        },
        submitHandler: function(form){            
            $('#show_msg3').hide();
            $('#show_msg4').hide();
            var token = "<?= csrf_token()?>"; 
            $('#processRenewForm').ajaxSubmit({
                url:'<?= route('supplier-uploadexcel.recallData')?>',
                type: "POST",
                dataType: "JSON",
                data:$("#processRenewForm").serializeArray(),
                //processData: false,
                //contentType:false,                
                beforeSubmit:function(formData,jqform, options){
                    $('#show_msg3').show();
					$('#show_msg4').show();
					$("#show_msg4").html("Please wait...");
                    //$('#btn_renew').hide();
                },clearForm:false,dataType:'json',success:function(resObj){
                    if(resObj.success == true){
                        $('#show_msg4').show();
                        $("#show_msg4").html(resObj.message);
                        $('#btn_renew').show()
                        //$("#processRenewForm")[0].reset();
						$("select.select2").select2('data', {}); // clear out values selected
						$("select.select2").select2({ allowClear: true }); // re-init to show default status
						//document.getElementById("vehicle_reg_no").selectedIndex = 0;
						//document.getElementById("supplier").selectedIndex = 0;
						//oTable.ajax.reload();
                    }else{
                        $('#btn_renew').show();
                        $('#show_msg4').show();
                        $("#show_msg4").html(resObj.message);
						//alert(resObj.message);
                        //window.location.reload();
                    }
                }

            });
        }
		})
	});	
	
	$('.select2').change(function(){
        $(this).valid()
    });
	
	$(document).ready(function () {
		$('#type_of_governor').on('change', function () {
			var typeId = this.value;
			$('#unit_sr_no').html('<option value="">Loading...</option>');
			$.ajax({
				url: '{{ route('sgrsa-certificate.getUnitsrno') }}?type_id='+typeId,
				type: 'get',
				success: function (res) {
					$('#unit_sr_no').html('<option value="">Select</option>');
					$.each(res, function (key, value) {
						$('#unit_sr_no').append('<option value="' + value
							.reg_number + '">' + value.reg_number + '</option>');
					});
				}
			});
		});	
	});		
</script>

