
	<div class="container" style="width: 100% !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h4><i class="fa fa-refresh fa-lg green"></i> Renew</h4>	
							</div>
						</div>
						<?php if ($role_id==3){ ?>
                        <div class="col-xs-12">                
                            <div class="card" style="margin: 0 0;">                                
                                <div class="card-body" style="padding:20px;">
                                
                                <div class="row">
                                    <form method="post" id="processRenewForm" class="form-horizontal" novalidate="novalidate">
                                    <input type="hidden" value="{{$record_id}}" name="record_id">                                    
                                    <input type="hidden" value="<?= csrf_token()?>" name="_token">                                    
                                    <div class="col-xs-12">  
                                        <div class="form-group" style="display: block;">
                                            											
											<div class="col-xs-12">
												<div class="col-sm-12">
													<label class="control-label" for="temp">Supplier</label>
													<br />
													{{$supplier_name}}
												</div>
											</div>
											
											<div class="col-xs-12">
												<div class="col-sm-6">
													<label class="control-label" for="temp">HC Number</label>
													<input type="text" class="form-control" id="renew_hc_no" name="renew_hc_no" style="width:100%;">
												</div>
												<div class="col-sm-6">
													<label class="control-label" for="temp">Date of Expiry</label>
													<input type="text" class="form-control renew_expiry_date" id="renew_date_of_expiry" name="renew_date_of_expiry" style="width:100%;">
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

<script type="text/javascript">

	$('.renew_expiry_date').datetimepicker({
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
            renew_hc_no: { required: true },
            renew_date_of_expiry:{required: true},    
        },
        messages:{            
            renew_hc_no:{required:'Enter HC Number'},
            renew_date_of_expiry:{required:'Select Expiry Date'},
        },
        submitHandler: function(form){            
            $('#show_msg3').hide();
            $('#show_msg4').hide();
            var token = "<?= csrf_token()?>"; 
            $('#processRenewForm').ajaxSubmit({
                url:'<?= route('sgrsa-certificate.RenewRecall')?>',
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
</script>

