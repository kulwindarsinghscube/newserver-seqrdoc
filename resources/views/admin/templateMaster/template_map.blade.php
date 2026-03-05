@extends('admin.layout.layout')
@section('content')
	<div class="container">
		<div class="col-xs-12">
			<div class="clearfix">
				<style type="text/css">
					label.error { color:red; }
					.frow {
						/*border-bottom: 2px solid black;*/
						/*border-top: 1px solid black;*/
					}
					.tmpl .btn {
						margin-left: 65px;
					}


				</style>
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-map-marker"></i> Map Template
								<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
								</h1>
							</div>
						</div>
						<div class="row">
							<a href="{{ URL::route('template-master.index') }}" class="btn btn-theme"><i class="fa fa-arrow-left"></i> Back</a>	
						</div>
						<form id="savefrm" class="savefrm"  action="" method="post">
								<input type="hidden" name="id" value="{{$template_data['id']}}">		
								<!-- start pratik code   -->
								<!-- using hidden value save flag for excel or database -->
								<input type="hidden" name="is_mapped" class="is_mapped" value="">
								<!-- end pratik code -->
								<div class="row">
									<div class="panel panel-primary" style="margin: 15px auto; width: 800px;">
										<div class="panel-heading"><i class="fa fa-file-o"></i> Map </div>
										<div class="panel-body">
											<div class="form-group">
												<h2>Template : {{$template_data['actual_template_name']}}</h2>
											</div>
											<div class="form-group clearfix" style="">
												<div class="col-md-6"><h3>Template columns</h3></div>
												<div class="col-md-6"><h3>Excelsheet columns</h3></div>
											</div>
											<div class="frow form-group clearfix" style="">
												<div class="col-md-6">	
												Unique serial no</div>
												<div class="col-md-6">
														<?php if(isset($template_data["unique_serial_no"]) && $template_data["unique_serial_no"] != '') { 
								       						 echo '<select name="excel_serial_no" class="form-control excelfields">';
															echo '<option value="'.$template_data["unique_serial_no"].'">'.$template_data["unique_serial_no"].'</option>';
														 } else {
														 	echo '<select name="excel_serial_no" class="form-control excelfields">';
															echo '<option value="">None</option>';
														 } ?>
													</select>
												</div>
											</div>

											<?php
												$disabled = "";
												$field_arr = config('Constant.field_skip_mapping');

												foreach ($fields as $fkey => $fvalue) {
													if($fkey == 1)
														continue;
								
													if(in_array($fvalue['security_type'],config('constant.field_skip_mapping'))){
													}else
													{
														echo '<div class="frow form-group clearfix" style="">';
														echo '<div class="col-md-6">'.$fvalue['name'].' </div><div class="col-md-6">';
														if($fvalue['mapped_name'] == '') {
															echo '<input type="hidden" name="f_id[]" value='.$fvalue['id'].'>';
															echo '<select name="f_value[]" class="form-control excelfields">';
															echo '<option value="">None</option>';
															$disabled = "";
														}
														else {
															echo '<input type="hidden" name="f_id[]" value='.$fvalue['id'].'>';
															echo '<select name="f_value[]" class="form-control excelfields">';
															echo '<option value="'. $fvalue['mapped_name'] .'">'.$fvalue['mapped_name'].'</option>';
														}

														echo '</select>';
														echo '</div>';
														echo '</div>';
														
													}
													// end pratik code
												}
											?>	
										</div>
									</div>
								</div>
								<div class="row">
									<div class="form-group clearfix tmpl">
										
										<button <?= $disabled ?>  type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 savecolumns" id="btnSave"><i class="fa fa-save "></i> Save</button>
										<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 exl_btn" id="btnMap"><i class="fa fa-file-o"></i> Map from file </button>
										 <!-- start pratik code -->
										<button  type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 db_btn" id="btnDb"><i class="fa fa-file-o"></i> Map from Database </button>
										<!-- end pratik code  -->
									</div>
								</div>
					
				          </form>
				      </div>
				  </div>

                      <!-- // Excel Modal -->
						<div class="modal fade" id="uploadFile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="myModalLabel">Map Fields</h4>
									</div>
									<div class="modal-body">						
										<form method="post" enctype="multipart/form-data" id="updfilefrm">
											<div class="form-group">
												<label>Upload File</label>
												<input type="file" class="form-control" id="field_file" name="field_file">
												<span id="field_file_error" class="text-danger"></span>
											</div>
											<input type="hidden" name="id" value="{{$template_data['id']}}">
											<div class="form-group clearfix">
												<button type="button" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 dbtn" id="btn_updfile"><i class="loadmap"></i> Map </button>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div> 
				<!-- // Excel Modal -->
						<!-- start pratik code 
						 database details form-->
							<div class="modal fade db_modal" id="connectDB" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h4 class="modal-title" id="myModalLabel">Database Connection Fields</h4>
										</div>
										<div class="modal-body">						
											<form method="post" action="../functions/TemplateModel.php" enctype="multipart/form-data" id="DBconnection">
												<input type="hidden" name="id" value="{{ $template_data['id']}}">
												
												<div class="form-group">
													<label>Database Name:-</label>
											<input type="text" class="form-control" id="db_name" name="db_name">
											 <span id="db_name_error" class="help-inline text-danger"></span>
												</div>
												<div class="form-group">
													<label>Database Host:-</label>
													<input type="text" class="form-control" id="host" name="host_address">
													 <span id="host_address_error" class="help-inline text-danger"></span>
												</div>
												<div class="form-group">
													<label>User Name:-</label>
													<input type="text" class="form-control" id="username" name="username">
													 <span id="username_error" class="help-inline text-danger"></span>
												</div>
												<div class="form-group">
													<label>Password:-</label>
													<input type="text" class="form-control" id="password" name="password">
													 <span id="password_error" class="help-inline text-danger"></span>
												</div>
												<div class="form-group">
													<label>Port:-</label>
													<input type="text" class="form-control allow_number" id="port" name="port" maxlength="6">
													 <span id="port_error" class="help-inline text-danger"></span>
												</div>
												<div class="form-group">
													<label>Table name:-</label>
													<input type="text" class="form-control" id="table_name" name="table_name">
													 <span id="table_name_error" class="help-inline text-danger"></span>
												</div>

												<div class="form-group clearfix">
													<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10 btn_connection " id="btn_connection"><i class="loadDBcon"></i> Connect </button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
						<!-- end database details form  -->
					</div>
				</div>	
			</div>
		</div>
	</div>	
@stop
@section('script')
<script type="text/javascript">

$(document).ready(function(){
   // disabled button	
   $(".exl_btn,.db_btn,.savecolumns").attr('disabled','disabled');

   var is_mapped="<?php if(!empty($fields[0]['is_mapped'])) echo $fields[0]['is_mapped'];  ?>";
   
   if(is_mapped=="excel")
   {
     $(".exl_btn").removeAttr('disabled','disabled');
   } 
   else if(is_mapped=="database")
   {
   	 $(".db_btn").removeAttr('disabled','disabled');
   }
   else
   {
   	 $(".db_btn,.exl_btn").removeAttr('disabled','disabled');
   }

	$('.savefrm').validate({
	  rules: {
	    gg: 'required',
	    "f_value[]": 'required',
	    'excel_serial_no':'required',
	  },
	  messages: {
	    gg: 'This field is required',
	    "f_value[]": "This field is required",
	    'excel_serial_no':'This field is required',
	  },
	  submitHandler: function(form) {
	  	// save mapping columns in database
	     var index_page="{{ URL::route('template-master.index') }}";
		         var url = " {{ URL::route('templateMaster.template-map.uploadcolumns') }} ";
		         var token = " {{ csrf_token() }} ";
		         var method_type = "post";

		         $("#savefrm").ajaxSubmit({
		               url:url,
		               data:{'_token':token},
		               type:method_type,

		               success:function(data){
		               	if(data.error)
	                     {
	                     	toastr.error(data.error);
	                     }
	                     else if(data.success==true)
	                     {
	                     	toastr.success('fields successfully mapped');
	                     	window.location.href=index_page;
	                     	$(".savecolumns").attr('disabled','disabled');
	                     }

		               },
		               error:function(){

		               },

		         });
	       }
	});
		// show model mapping
		$('#btnMap').click(function (event) {
			event.preventDefault();
			$('#uploadFile').modal('show');
		});
		//get mapping columns form Excel sheet
		 $("#btn_updfile").click(function(e){
            e.preventDefault();
       
            var val = $(this).val();
            var url = " {{ URL::route('templateMaster.template-map.uploadmap')}} ";
            var method_type = 'post';
        
            $('#updfilefrm').ajaxSubmit({
                url: url,
                type: method_type,
                data:{"_token":"{{csrf_token()}}"},
                beforeSubmit:function(){
                	$(".loadmap").addClass('fa fa-spinner fa-spin');
                	$("#updfilefrm").find('span').text('').end();
                    $(".dbtn").attr('disabled', 'disabled');
                },
                success : function(resp)
                {
                  if(resp.success==true)
                  {
                  	toastr.success('file successfully uploaded');
                  	$('#uploadFile').modal('hide');
                    $(".savecolumns").removeAttr('disabled');
                     var option=$(".savefrm").find("option");
                     option.remove().end();
                     var select=$('.savefrm').find('select');
                     $(".is_mapped").val('excel');
                     select.append('<option value>===Select===</option>');
                     $.each(resp.fields,function(k,v) {
                      
                      select.append("<option value='"+v+"'>"+v+"</option>");
                     });
                     $(".loadmap").removeClass('fa fa-spinner fa-spin');
                     $("#field_file").val('');
                     $(".dbtn").removeAttr('disabled', 'disabled');
                  }
                },
                error : function(respObj){
                	$(".dbtn").removeAttr('disabled', 'disabled');
					$.each(respObj.responseJSON.errors, function(k,v){
						$('#'+k+'_error').text(v);
					});
					$(".loadmap").removeClass('fa fa-spinner fa-spin');
				}
            });
        });
       // modal show database details
		$(".db_btn").click(function(event) {
		   event.preventDefault();	
		   $("#DBconnection").find('input').val('').end(); 
		    $("#DBconnection").find('span').text('').end();
		   $(".db_modal").modal('show');	
		}); 
      // database connection request
		$(".btn_connection").click(function(event) {	
			event.preventDefault();
            
            var url="{{ URL::route('templateMaster.template-map.mapdatabase') }}";
            var token="{{ csrf_token() }}";
            var method_type="post";
            $("#DBconnection").ajaxSubmit({
                  
                  url:url,
                  type:method_type,
                  data:{'_token':token},
                  beforeSubmit:function(){
                  	$("#DBconnection").find('span').text('').end();
                  	$(".loadDBcon").addClass('fa fa-spinner fa-spin');
                  	$(".btn_connection").attr('disabled', 'disabled'); 
                  },
                  success:function(data){
                   
                   if(data.connection_error)
                   {
                   	 toastr.error(data.connection_error);
                   	 $(".loadDBcon").removeClass('fa fa-spinner fa-spin');
                   	 $(".btn_connection").removeAttr('disabled'); 
                   }
                   else if(data.table_error)
                   {
                   	  toastr.error('Something are wrong');
                   	  $("#table_name_error").text(data.table_error);
                   	  $(".loadDBcon").removeClass('fa fa-spinner fa-spin'); 
                   	  $(".btn_connection").removeAttr('disabled'); 
                   }
                   else if(data.success==true)
                   {
                   	 toastr.success('fields successfully mapped');
                   	 $('.db_modal').modal('hide');
                     $(".savecolumns").removeAttr('disabled');
                     $(".is_mapped").val('database');
                   	 var option=$(".savefrm").find("option");
                     option.remove().end();
                     var select=$('.savefrm').find('select');
                     select.append('<option value>===Select===</option>');
                     $.each(data.table_columns,function(k,v) {
                      
                      select.append("<option value='"+v+"'>"+v+"</option>");
                     });
                     $(".loadDBcon").removeClass('fa fa-spinner fa-spin');
                     $(".btn_connection").removeAttr('disabled');  
                   } 
                  },
                  error:function(respObj){
                   $(".btn_connection").removeAttr('disabled'); 
                   $.each(respObj.responseJSON.errors, function(k,v){
						$('#'+k+'_error').text(v);
					});
                   $(".loadDBcon").removeClass('fa fa-spinner fa-spin'); 
                  }

            });
			
		});	 	 

	});
 
 $(".allow_number").keypress(function(h){
    var keyCode =h.which ? h.which : h.keyCode
       if (!(keyCode >= 48 && keyCode <= 57)) {
             return !1;
           }
 }); 
</script>
@stop