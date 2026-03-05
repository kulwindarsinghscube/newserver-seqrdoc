@extends('admin.layout.layout')
@section('content')
	<div class="container" style="width: 1350px !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-file"></i> Supplier Form</h1>	
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
                        <div class="col-xs-12" id="div_add">                
                            <div class="card">                                
                                <div class="card-body" style="padding:20px;">
                                
                                <div class="row">
                                    <form method="post" id="processApiForm" class="form-horizontal" novalidate="novalidate">
                                    <input type="hidden" value="<?= csrf_token()?>" name="_token">                                    
                                    <div class="col-xs-12">  
                                        <div class="form-group">
                                            <div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">Business Name</label>
													<input type="text" class="form-control" id="company_name" name="company_name">
												</div>
												<div class="col-sm-4">
													<label class="control-label">Registration Number</label>
													<input type="text" class="form-control" id="registration_no" name="registration_no">
												</div>
												<div class="col-sm-4">
													<label class="control-label">PIN Number</label>
													<input type="text" class="form-control" id="pin_no" name="pin_no">
												</div>
                                            </div>											
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">VAT Number</label>
													<input type="text" class="form-control" id="vat_no" name="vat_no">													
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">PO Box</label>									
													<input type="text" class="form-control" id="po_box" name="po_box">				
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Code</label>
													<input type="text" class="form-control" id="code" name="code">
												</div>
											</div>
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label" for="temp">Town</label>
													<input type="text" class="form-control" id="town" name="town">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Telephone</label>
													<input type="text" class="form-control" id="tel_no" name="tel_no">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Email</label>
													<input type="text" class="form-control" id="email" name="email">
												</div>
                                            </div>
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label" for="temp">Username</label>
													<input type="text" class="form-control" id="username" name="username">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Password</label>
													<input type="password" class="form-control" id="password" name="password">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Confirm Password</label>
													<input type="password" class="form-control" id="cpassword" name="cpassword">
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
<!--Edit Form-->
						<div class="col-xs-12" id="div_edit" style="display:none;">                
                            <div class="card">                                
                                <div class="card-body" style="padding:20px;">
                                
                                <div class="row">
                                    <form method="post" id="editForm" class="form-horizontal" novalidate="novalidate">
                                    <input type="hidden" value="<?= csrf_token()?>" name="_token">                                    
                                    <div class="col-xs-12">  
                                        <div class="form-group">
                                            <div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">Business Name</label>
													<input type="text" class="form-control" id="company_name_e" name="company_name_e">
													<input type="hidden" class="form-control" id="company_name_chk" name="company_name_chk">
													<input type="hidden" class="form-control" id="supplier_id" name="supplier_id">
												</div>
												<div class="col-sm-4">
													<label class="control-label">Registration Number</label>
													<input type="text" class="form-control" id="registration_no_e" name="registration_no_e">
												</div>
												<div class="col-sm-4">
													<label class="control-label">PIN Number</label>
													<input type="text" class="form-control" id="pin_no_e" name="pin_no_e">
												</div>
                                            </div>											
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label">VAT Number</label>
													<input type="text" class="form-control" id="vat_no_e" name="vat_no_e">													
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">PO Box</label>									
													<input type="text" class="form-control" id="po_box_e" name="po_box_e">				
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Code</label>
													<input type="text" class="form-control" id="code_e" name="code_e">
												</div>
											</div>
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label" for="temp">Town</label>
													<input type="text" class="form-control" id="town_e" name="town_e">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Telephone</label>
													<input type="text" class="form-control" id="tel_no_e" name="tel_no_e">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Email</label>
													<input type="text" class="form-control" id="email_e" name="email_e">
												</div>
                                            </div>
											<div class="col-xs-12">
												<div class="col-sm-4">
													<label class="control-label" for="temp">Username</label>
													<input type="text" class="form-control" id="username_e" name="username_e">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Password</label>
													<input type="password" class="form-control" id="password_e" name="password_e">
												</div>
												<div class="col-sm-4">
													<label class="control-label" for="temp">Confirm Password</label>
													<input type="password" class="form-control" id="cpassword_e" name="cpassword_e">
												</div>
                                            </div>
                                        </div>         
                                    </div>
                                    <div class="col-xs-12"><p></p></div>
                                    <div class="col-xs-12 text-center"> 
                                    <button type="submit" class="btn btn-primary" id="btn_import_e">Edit</button> 
                                    </div>
                                    </form>
                                    
                                    <div class="col-xs-12"><div id="progress_e" style="border:1px solid gray; border-radius: 4px; display:none;"></div></div>
                                    <div class="col-xs-12"><div id="show_msg_e"></div></div>
                                    <div class="col-xs-12"><div id="show_records_e"></div></div>
                                    <div class="col-xs-12"><p><div id="show_msg2_e" class="text-center"></p></div></div>
                                </div>                                
                                </div>                                
                            </div> 
                        </div>
						
						
						<div class="">							
							<div class="col-xs-12">
								<table id="example" class="table table-hover table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>#</th>
											<th>Date</th>
											<th>Business Name</th>
											<th>Registration No</th>
											<th>Pin No</th>
											<th>VAT No</th>
											<th>PO Box</th>
											<th>Code</th>
											<th>Town</th>
											<th>tel_no</th>
											<th></th>											
										</tr>
									</thead>
									<tfoot>
									</tfoot>
								</table>
							</div> 
                            <div class="col-sm-12" style="height:30px;"></div>    
                            
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

<script type="text/javascript">

	$('.installation_date').datetimepicker({
		format: 'DD-MM-YYYY'
	});	
	$('.expiry_date').datetimepicker({
		format: 'DD-MM-YYYY'
	});	
	
	$token = '<?= csrf_token()?>';
	//datatable for index page 
	var oTable = $('#example').DataTable({
		//'dom':  "<'row'<'col-sm-3'i><'col-sm-5' p><'col-sm-1' ><'col-sm-3'f>>",
        //"bProcessing": false,
        "bProcessing": "<span class='fa-stack fa-lg'>\n\<i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\</span>&nbsp;&nbsp;&nbsp;&nbsp;Processing ...",
        "bServerSide": true,
        "autoWidth": true,
        "oLanguage": {"infoFiltered": ""},        
        "aaSorting": [
        [1, "desc"]
        ],
        //index page url calls
        "sAjaxSource":"<?= URL::route('sgrsa-supplier.SupplierList',['status'=>1])?>",        
        //columns that displaying
        "aoColumns":[
		{mData: "rownum", sWidth: "5%", bSortable:false,},
		{mData: "created_date",sWidth: "10%",bSortable: true,
			mRender: function(v, t, o) {
            	var date = moment(v).format('DD-MM-YYYY');
				return date;
            },
		},
        {mData: "company_name",bSortable: true,},        
		{mData: "registration_no",bSortable: true,},        
		{mData: "pin_no",bSortable: true,},        
		{mData: "vat_no",bSortable: true,},        
		{mData: "po_box",bSortable: true,},  
		{mData: "code",bSortable: true,},  
		{mData: "town",bSortable: true,},  
		{mData: "tel_no",bSortable: true,},  
		
		{mData: 'id',
            bSortable: false,
            sWidth: "5%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            	var buttons = '';  
                //buttons += '<button title="View Details" class="details-control" id="'+o['id']+'"><i class="fa fa-eye fa-lg blue"></i></button>';  
                //buttons += "<a href='<?= $print_filepath; ?>"+o['file_name']+"' target='_blank' class='details-control' title='PDF to Print'><i class='fa fa-file-pdf-o fa-lg red'></i></a> ";  
                //buttons += "<a href='<?= $verify_filepath; ?>"+o['vehicle_reg_no'].replace(/\s/g, '')+".pdf' target='_blank' class='details-control' title='Verification PDF'><i class='fa fa-file-pdf-o fa-lg blue'></i></a>";  
                buttons += '<a href="javascript:void(0);" title="Edit" class="edit-control button" id="'+o['id']+'"><i class="fa fa-edit fa-lg green"></i></a>';    
				return buttons;
            },   	
        },
	],

	});

	oTable.on('draw.dt', function () {
	    //$(".loader").addClass('hidden');  
	}); 
    /*
	oTable.on('click', '#delData', function (e) {
		$id = $(this).data('id');
		bootbox.confirm("Are you sure you want to delete?",function(result){
			if(result){
				$.post('<?=route('labmaster.delete')?>',
					{'type':'delete','id':$id,'_token':$token},
					function(data){
						if(data.type == 'success'){
							toastr["success"](data.message);
							oTable.ajax.reload();
						}
						else toastr["error"](data.message);
					},'json');
			}
		});
	});
    */
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
            company_name: { required: true },
            registration_no:{required: true},    
            pin_no:{required: true},    
            vat_no:{required: true},    
            po_box:{required: true},    
            code:{required: true},    
            town:{required: true},    
            tel_no:{required: true},    
            email:{required: true},    
            username:{required: true, minlength : 5},    
            password:{required: true, minlength : 6},    
            cpassword:{required: true, minlength : 6, equalTo : "#password"},    
        },
        messages:{            
            company_name:{required:'Enter Business Name'},
            registration_no:{required:'Enter Registration Number'},
            pin_no:{required:'Enter PIN Number'},
            vat_no:{required:'Enter VAT Number'},
            po_box:{required:'Enter PO Box'},
            code:{required:'Enter Code'},
            town:{required:'Enter Town'},
            tel_no:{required:'Enter Telephone'},
            email:{required:'Enter Email'},
			username:{required: 'Enter Username', minlength:'Enter minimum 5 charactrs'},    
            password:{required: 'Enter Password', minlength:'Enter minimum 6 charactrs'},    
            cpassword:{required: 'Enter Confirm Password', minlength:'Enter minimum 6 charactrs', equalTo:"Doesn't match with Password"},  
        },
        submitHandler: function(form){            
            $('#show_msg').hide();
            $('#show_msg2').hide();
            var token = "<?= csrf_token()?>"; 
            $('#processApiForm').ajaxSubmit({
                url:'<?= route('sgrsa-supplier.supplierData')?>',
                type: "GET",
                dataType: "JSON",
                data:$("#processApiForm").serializeArray(),
                processData: false,
                contentType:false,                
                beforeSubmit:function(formData,jqform, options){
                    $('#show_msg').show();
					$('#show_msg2').show();
					$("#show_msg2").html("Please wait...");
                    $('#btn_import').hide();
                },clearForm:false,dataType:'json',success:function(resObj){
                    if(resObj.success == true){
                        $('#show_msg2').show();
                        $("#show_msg2").html(resObj.message);
                        $('#btn_import').show()
                        //document.getElementById("processApiForm").reset();
						$("#processApiForm")[0].reset();
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
    
	/*$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $token
		}
	});*/

	$('#example tbody').on('click', 'a.edit-control', function () {
		$("html, body").animate({ scrollTop: 0 }, "slow");
		var tr = $(this).closest('tr');
		var row = oTable.row( tr );
		var id=$(this).attr('id');	
		$("input#username_e").attr('disabled','disabled');
		$("#show_msg2_e").html("Please wait...");
		$('#company_name_e').val('');
		$('#company_name_chk').val('');
		$('#supplier_id').val('');
		$('#registration_no_e').val('');
		$('#pin_no_e').val('');
		$('#vat_no_e').val('');
		$('#po_box_e').val('');
		$('#code_e').val('');
		$('#town_e').val('');
		$('#tel_no_e').val('');
		$('#email_e').val('');
		$('#username_e').val('');		
		$('#password_e').val('');		
		$('#cpassword_e').val('');		
        //if (confirm("Do you want to edit a record?")) {
			$.ajax( {
				url: "<?=route('sgrsa-supplier.EditRecord')?>",
				type: "GET",
				data: {
					'id':id
				},
				//dataType: 'json',
				success: function (resObj) {
					if(resObj.success == true){
						$("#div_add").hide();
						$("#div_edit").show();
						$('#company_name_e').val(resObj.res.company_name);
						$('#company_name_chk').val(resObj.res.company_name);
						$('#supplier_id').val(resObj.supplier_id);
						$('#registration_no_e').val(resObj.res.registration_no);
						$('#pin_no_e').val(resObj.res.pin_no);
						$('#vat_no_e').val(resObj.res.vat_no);
						$('#po_box_e').val(resObj.res.po_box);
						$('#code_e').val(resObj.res.code);
						$('#town_e').val(resObj.res.town);
						$('#tel_no_e').val(resObj.res.tel_no);
						$('#email_e').val(resObj.res.email);
						$('#username_e').val(resObj.username);
						$("#show_msg2_e").html("");
					}
				}
			} );
		//}		
	} );
	
    
	$('#editForm').validate({
		errorElement: 'span',
		errorClass: 'help-inline',
		focusInvalid: false,
		invalidHandler: function (event, validator) { //display error alert on form submit
			$('.alert-error', $('#editForm')).show();
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
            company_name_e: { required: true },
            registration_no_e:{required: true},    
            pin_no_e:{required: true},    
            vat_no_e:{required: true},    
            po_box_e:{required: true},    
            code_e:{required: true},    
            town_e:{required: true},    
            tel_no_e:{required: true},    
            email_e:{required: true},    
            username_e:{required: true, minlength : 5},    
            password_e:{minlength : 6},    
            cpassword_e:{
				required: function(element){
				return $("#password_e").val()!="";
				},
				minlength : 6, equalTo : "#password_e"
			},    
        },
        messages:{            
            company_name_e:{required:'Enter Business Name'},
            registration_no_e:{required:'Enter Registration Number'},
            pin_no_e:{required:'Enter PIN Number'},
            vat_no_e:{required:'Enter VAT Number'},
            po_box_e:{required:'Enter PO Box'},
            code_e:{required:'Enter Code'},
            town_e:{required:'Enter Town'},
            tel_no_e:{required:'Enter Telephone'},
            email_e:{required:'Enter Email'},
			username_e:{required: 'Enter Username', minlength:'Enter minimum 5 charactrs'},    
            password_e:{required: 'Enter Password', minlength:'Enter minimum 6 charactrs'},    
            cpassword_e:{required: 'Enter Confirm Password', minlength:'Enter minimum 6 charactrs', equalTo:"Doesn't match with Password"},  
        },
        submitHandler: function(form){            
            $('#show_msg').hide();
            $('#show_msg2').hide();
            var token = "<?= csrf_token()?>"; 
            $('#editForm').ajaxSubmit({
                url:'<?= route('sgrsa-supplier.editData')?>',
                type: "GET",
                dataType: "JSON",
                data:$("#editForm").serializeArray(),
                processData: false,
                contentType:false,                
                beforeSubmit:function(formData,jqform, options){
                    $('#show_msg_e').show();
					$('#show_msg2_e').show();
					$("#show_msg2_e").html("Please wait...");
                    $('#btn_import_e').hide();
                },clearForm:false,dataType:'json',success:function(resObj){
                    if(resObj.success == true){
                        $('#show_msg2_e').show();
                        $("#show_msg2_e").html(resObj.message);
                        $('#btn_import_e').show()
                        //document.getElementById("editForm").reset();
						$("#editForm")[0].reset();
						$("select.select2").select2('data', {}); // clear out values selected
						$("select.select2").select2({ allowClear: true }); // re-init to show default status
						//oTable.ajax.reload();
						window.location.reload();
                    }else{
                        $('#btn_import_e').show();
                        $('#show_msg2_e').show();
                        $("#show_msg2_e").html(resObj.message);
						//alert(resObj.message);
                        //window.location.reload();
                    }
                }

            });
        }
    }) 	
	
	//view 
    $('.viewData').click(function (e) {
        $('#ajaxContent').html('Please wait...');
        $('#viewInfo').modal('show');
        var api_option=$(this).data('api');
        var district_selector=$("#district_selector").val();
        var upazila_selector=$("#upazila_selector").val();
        var category_selector=$("#category_selector").val();
        var per_page=$("#per_page").val();
        var page_no=$("#page_no").val();        
        var is_alive=$("#is_alive").val();        
        var token = "<?= csrf_token()?>";
        $.get("<?=route('molwa-certificate.ViewImportDetails')?>",{'api_option':api_option, 'per_page':per_page, 'page_no':page_no, 'is_alive':is_alive, 'district':district_selector, 'upazila':upazila_selector, 'category':category_selector, '_token':token},function(data){
            $('#ajaxContent').html(data);
        });
	});	    
	
	$('#example tbody').on('click', 'button.details-control', function () {
		var tr = $(this).closest('tr');
		var row = oTable.row( tr );
		var id=$(this).attr('id');	
		if ( row.child.isShown() ) {
			row.child.hide();
			tr.removeClass('shown');
		}
		else {
			row.child( format(row.data(),id) ).show();
			tr.addClass('shown');
		}
	} );

	function format ( rowData,id ) { //rowData[1] 
		var div = $('<div/>')
			.addClass( 'loading' )
			.text( 'Loading...' );
		var token = "<?= csrf_token()?>";
		$.ajax( {
			url: "<?=route('molwa-certificate.ViewImportFF')?>",
			type: "POST",
			data: {
				'id':id, '_token':token
			},
			//dataType: 'json',
			success: function ( data ) {
				div
					.html( data )
					.removeClass( 'loading' );
			}
		} );
	 
		return div;
	}    
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