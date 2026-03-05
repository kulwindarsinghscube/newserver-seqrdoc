@extends('admin.layout.layout')

@section('style')
    <link href="{{asset('backend/canvas/css/all.css')}}" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="{{asset('backend/canvas/css/custom.css')}}">
	<style>
		
		


	</style>
	<!-- <style type="text/css">
	
	

		<?php
		
		$domain = \Request::getHost();
        $subdomain = explode('.', $domain);
		
			if(isset($FONTS)){
				foreach ($FONTS as $key => $font) {
					$fname = $font['font_filename'];
					echo "@font-face {" ;
					echo "font-family: '{$fname}';";
					echo "src: url('../../backend/fonts/{$font['font_filename_N']}') format('truetype')";
					echo "}";
					if($font['font_filename_B'] != '') {
						/*
						 * Style Bold
						 */
						$fname = str_replace(' ', '_', $font['font_name']) . '_B';
						echo "@font-face {";
						echo "font-family: '{$fname}';";
						echo "src: url('../../backend/fonts/{$font['font_filename_B']}') format('truetype')";
						echo "font-weight: bold;";
						echo "}";
					}
					if($font['font_filename_I'] != '') {
						/*
						 * Style Italic
						 */
						$fname = str_replace(' ', '_', $font['font_name']) . '_I';
						echo "@font-face {";
						echo "font-family: '{$fname}';";
						echo "src: url('../../backend/fonts/{$font['font_filename_I']}') format('truetype')";
						echo "font-weight: bold;";
						echo "}";
					}
					if($font['font_filename_BI'] != '') {
						/*
						 * Style Italic
						 */
						$fname = str_replace(' ', '_', $font['font_name']) . '_BI';
						echo "@font-face {";
						echo "font-family: '{$fname}';";
						echo "src: url('../../backend/fonts/{$font['font_filename_BI']}') format('truetype')";
						echo "font-weight: bold;";
						echo "}";
					}
				}
			}
		?>
	</style> -->
@stop

@section('content')
	<div id="wrapper">
		<ul class="sidebar navbar-nav custom_res_1">
			<li class="nav-item dropdown">
				<a class="nav-link " href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  data-toggle="tooltip" title="Text">
					<i class="fas fa-file-alt custom_side_icon"></i>
				</a>
				<div class="dropdown-menu" aria-labelledby="pagesDropdown" style="top:50px!important;">
					<table class="table table-dark">
						<thead style="background:#333;">
							<tr>
								<th colspan="2">Text</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="padding: 0.75rem" class="custom_table" id="dynamic_text">
									<i class="fas fa-file-alt custom_icon"></i><br>Dynamic Text
								</td>
								<td style="padding: 0.75rem" class="custom_table" id="static_text">
									<i class="far fa-file-alt custom_icon"></i><br>Static Text
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</li>
			<li class="nav-item dropdown">
				<a class="nav-link " href="#" id="pagesDropdown0" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  data-toggle="tooltip" title="Image">
					<i class="far fa-images custom_side_icon"></i>
				</a>
				<div class="dropdown-menu" aria-labelledby="pagesDropdown0">
					<table class="table table-dark">
						<thead style="background:#333;">
							<tr>
								<th colspan="2">Image</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="padding: 1.75rem" class="custom_table" id="dynamic_image">
									<i class="fas fa-file-image custom_icon"></i><br>Dynamic Image
								</td>
								<td style="padding: 1.75rem" class="custom_table" id="static_image">
									<i class="far fa-file-image custom_icon"></i><br>Static Image
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</li>
			<li class="nav-item dropdown">
				<a class="nav-link " href="#" id="pagesDropdown1" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  data-toggle="tooltip" title="Security">
					<i class="fas fa-plus-square custom_side_icon"></i>
				</a>
				<div class="dropdown-menu" aria-labelledby="pagesDropdown1">
					<table class="table table-dark">
						<thead style="background:#333;">
							<tr>
								<th colspan="2">Security</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="padding: 1.75rem" class="custom_table" id="microtext_border">
									<i class="fas fa-tag custom_icon"></i><br>Microtext Border
								</td>
								<td style="padding: 1.75rem" class="custom_table" id="microtext">
									<i class="fas fa-fingerprint custom_icon"></i><br>MicroText
								</td>
							</tr>

							<tr>
								<td style="padding: 1.75rem" class="custom_table" id="ghost_image"><i class="fas fa-ghost custom_icon"></i><br>Ghost Image</td>
								<td style="padding: 1.75rem" class="custom_table" id="void_pantograph"><i class="fas fa-signal custom_icon"></i><br>Void Pantograph</td>
							</tr>

							<tr>
								<td style="padding: 1.75rem" class="custom_table" id="security_line"><i class="far fa-id-badge custom_icon"></i><br>Security Line</td>
								<td style="padding: 1.75rem" class="custom_table" id="uv_repeat_line"><i class="fas fa-redo custom_icon"></i><br>UV Repeat Line</td>
							</tr>
							<tr>
								<td style="padding: 1.75rem" class="custom_table" id="invisible"><i class="far fa-id-badge custom_icon"></i><br>Invisible</td>
								<td style="padding: 1.75rem" class="custom_table" id="uv_repeat_fullpage"><i class="fas fa-redo custom_icon"></i><br>UV Repeat Fullpage</td>
							</tr>
						</tbody>
					</table>
				</div>
			</li>
			<li class="nav-item dropdown">
				<a class="nav-link " href="#" id="pagesDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  data-toggle="tooltip" title="Digital Security Elements">
					<i class="fas fa-qrcode custom_side_icon"></i>
				</a>
				<div class="dropdown-menu" aria-labelledby="pagesDropdown2">
					<table class="table table-dark">
						<thead style="background:#333;">
							<tr>
								<th colspan="2">Digital Security Elements</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="padding: 1.75rem" class="custom_table" id="qr_code"><i class="fas fa-qrcode custom_icon"></i><br>QR Code</td>
								<td style="padding: 1.75rem" class="custom_table" id="1d_barcode"><i class="fas fa-barcode custom_icon"></i><br>1D Barcode</td>
							</tr>
							<tr>
								<td style="padding: 1.75rem" class="custom_table" id="2d_barcode"><i class="fas fa-barcode custom_icon"></i><br>2D Barcode</td>
							</tr>
						</tbody>
					</table>
				</div>
			</li>
		</ul>
		<?= Form::open(['id'=>'template_frm','files'=>true])?>
			<div id="content-wrapper" class="custom_res_2" style="margin-top: -20px;">
				<div class="custom_header_bg">
					<div class="container">
						<div class="row">
							<div class="col-md-1"></div>
							<div class="col-md-2 custom_header_2" style="display: inline-flex;margin-bottom: 10px;">
								<input type="hidden" id="bSaveAndClose" value="0"/>
								<button type="submit" class="btn btn-success custom_header_btn" id="btnSave" style="margin-right: 50px;">Save</button>
								<button type="submit" class="btn btn-primary custom_header_btn" id="btnSaveAndClose"  style="margin-right: 50px;"> Save and Close</button>
								<button type="submit" class="btn btn-warning custom_header_btn" id="btnPreview"  style="margin-right: 50px;"> Preview</button>
								<button type="button" class="btn btn-danger custom_header_btn" id="btnCancel"  style="margin-right: 50px;"> Cancel</button>
							</div>
						</div>
					</div>
				</div>
				<div class="sidebar_1" style="float: right; margin-top: -61px;">
					<div id="main_tab">
						<input type="hidden" name="func" id="func" >  
						<input type="hidden" name="template_id" id="template_id" value='<?php if(isset($TEMPLATE['id'])) echo $TEMPLATE['id']; ?>'>
						<div id="main_tab_sidebar">
							<span><i class="fas fa-cog custom_icon"></i></span>
							<span><i class="fas fa-layer-group custom_icon"></i></span>       
							<span style="float: right;" id="deleteCurrent"><i class="fa fa-trash custom_icon"></i></span>       
							<span style="float: right;" id="copyCurrent"><i class="far fa-copy custom_icon"></i></span>       
						</div>
						<div id="para" >
							<div id="first" class="tab_p" >
								<table class="table table-responsive" >
									<thead>
										<tr>
											<th colspan="4">Attributes</th>
										</tr>
									</thead>
									<div id="response"></div>
									<tbody id="field_dialog">
									</tbody>          
								</table>
							</div>
							<div class="tab_p" id="second_tab">
								<table class="table table-condensed">  
									<thead>
										<tr>
											<th colspan="4">Typography</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td colspan="2">
												<button type="button" class="btn btn-primary">Edit Text Content</button>
											</td>                         
										</tr>
										<tr>
											<td>
												Template Name 
											</td>
											<td>
												@if(isset($TEMPLATE["actual_template_name"]))
													<input type="text" class="custom_text" id="actual_template_name" name="actual_template_name" value="<?php if(isset($TEMPLATE["actual_template_name"])) echo $TEMPLATE["actual_template_name"]; ?>">

													<input type="hidden" class="custom_text" id="template_name" name="template_name" value="<?php if(isset($TEMPLATE["template_name"])) echo $TEMPLATE["template_name"]; ?>">
												@else
													<input type="text" class="custom_text" id="template_name" name="template_name" value="<?php if(isset($TEMPLATE["template_name"])) echo $TEMPLATE["template_name"]; ?>">
												@endif
												<span class="help-inline text-danger" id="template_name_error"><?= $errors->first('template_name')?></span>
												<span class="help-inline text-danger" id="actual_template_name_error"><?= $errors->first('actual_template_name')?></span>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												Template Description
												<br>
												<textarea cols="40" class="custom_text custom_textarea" id="template_desc" name="template_desc" ><?php if(isset($TEMPLATE["template_desc"])) echo $TEMPLATE["template_desc"]; ?></textarea>
												<span class="help-inline text-danger" id="template_desc_error"><?= $errors->first('template_desc')?></span>
											</td>                      
										</tr>
										<tr>
											<td>Background Template</td>
											<td>
												<select class="custom_select"  name="bg_template_id" id="bg_template_id" class="form-control">

													<option value="0" <?php if(isset($TEMPLATE["bg_template_id"]) && $TEMPLATE["bg_template_id"] == 0) echo "selected"; ?>>Blank Template</option>
													<?php
					                                    $status = 1; 
					                                    if(isset($BGTEMPLATE))
					                                    {
					                                       foreach ($BGTEMPLATE as $key => $br_row) {

					                                          if(isset($TEMPLATE["bg_template_id"]) && $TEMPLATE["bg_template_id"] == $br_row['id'])
					                                             echo "<option value='{$br_row['id']}' selected>{$br_row['background_name']}</option>";
					                                          else
					                                             echo "<option value='{$br_row['id']}'>{$br_row['background_name']}</option>";
					                                       }
					                                    }
					                                 ?>
												</select>
											</td>
										</tr>
										<tr>
											<td>Print with Background</td>
											<td>  
												<label class="switch">  
												<input type="checkbox" class="form-control" id="background_print" value="<?php 
				                                    if(isset($TEMPLATE["background_template_status"]))
				                                     {
				                                       echo $TEMPLATE["background_template_status"];

				                                     }else{
				                                       echo "1";
				                                     }?>"
				                                     <?php
				                                     if(isset($TEMPLATE["background_template_status"])){
				                                       if($TEMPLATE["background_template_status"] == 0){
				                                       ?>
				                                        unchecked
				                                        <?php      
				                                          }else{
				                                        ?>
				                                        checked
				                                        <?php      
				                                          }
				                                        }else{
				                                       ?>
				                                       checked
				                                       <?php
				                                     }
				                                     ?> 
				                                     >
													<span class="slider round"></span>
												</label>
											</td>
										</tr>

										<tr>
											<td>Template Size</td>
											<td>
												<select name="template_size" id="template_size" class="custom_select" >
													<?php
					                                $status = 1; 
					                                if(isset($TEMPLATE["template_size"])){
					                                  $template_size = $TEMPLATE["template_size"];
					                                }else{
					                                  $template_size = '';
					                                }
					                                ?>
					                                <option value="A4" <?php echo $template_size == 'A4'? "selected" : ""; ?>>A4</option>
					                                <option value="A3" <?php echo $template_size == 'A3'? "selected" : ""; ?>>A3</option>
					                                <option value="A5" <?php echo $template_size == 'A5'? "selected" : ""; ?>>A5</option>
					                                <option value="Custom" <?php echo $template_size == 'Custom'? "selected" : ""; ?>>Custom</option>
												</select>
											</td>
										</tr>

										<tr id="tmp_height">
											<td>
												Height
											</td>
											<td>
												
												<input type="text" class="custom_text" id="template_height" onkeypress="return isNumberKey(event)" name="height" value="<?php if(isset($TEMPLATE["height"])) echo $TEMPLATE["height"]; ?>">
												<span class="help-inline text-danger" id="height_error"><?= $errors->first('height')?></span>
											</td>
										</tr>
										<tr id="tmp_width">
											<td>
												Width
											</td>
											<td>
												
												<input type="text" class="custom_text" id="template_width" onkeypress="return isNumberKey(event)" name="width" value="<?php if(isset($TEMPLATE["width"])) echo $TEMPLATE["width"]; ?>">
												<span class="help-inline text-danger" id="width_error"><?= $errors->first('width')?></span>
											</td>
										</tr>

										<tr>
											<td>Template Status</td>
											<td>
												<select name="template_status" id="template_status" class="custom_select" >
													<?php
						                            	$status = 1; 
						                            	if(isset($TEMPLATE["status"]))
						                                	$status = $TEMPLATE["status"];
						                           ?>
													<option value="1" <?php echo $status == '1'? "selected" : ""; ?>>Enable</option>
													<option value="0" <?php echo $status == '0'? "selected" : ""; ?>>Disable</option>
												</select>
											</td>
										</tr>
										<!--Blockchain-->
										<?php if($subdomain[0]=="demo"){ ?>
										<tr>
											<td>Upload Data To Blockchain <br><small style="color:#ff0000;">(If enabled blockchain then you can't disable it.)</small></td>
											<td>  
												<label class="switch">  
												<input type="checkbox" class="form-control" id="is_block_chain_template" value="<?php 
				                                    if(isset($TEMPLATE["is_block_chain"])&&!empty($TEMPLATE["is_block_chain"]))
				                                     {
				                                       echo $TEMPLATE["is_block_chain"];

				                                     }else{
				                                       echo "0";
				                                     }?>"
				                                     <?php
				                                     if(isset($TEMPLATE["is_block_chain"])){
				                                       if($TEMPLATE["is_block_chain"] == 0){
				                                       ?>
				                                        unchecked
				                                        <?php      
				                                          }else{
				                                        ?>
				                                        checked disabled
				                                        <?php      
				                                          }
				                                        }else{
				                                       ?>
				                                       unchecked
				                                       <?php
				                                     }
				                                     ?> 
				                                     >
													<span class="slider round"></span>
												</label>
											</td>
										</tr>
										<tr id="bcDocumentDescriptionDiv" style="display: none;">
											<td>
												Document Description
											</td>
											<td>
												@if(isset($TEMPLATE["bc_document_description"]))
													<input type="text" class="custom_text" id="bc_document_description" name="bc_document_description" value="<?php if(isset($TEMPLATE["bc_document_description"])) echo $TEMPLATE["bc_document_description"]; ?>">

													
												@else
													<input type="text" class="custom_text" id="bc_document_description" name="bc_document_description" value="<?php if(isset($TEMPLATE["bc_document_description"])) echo $TEMPLATE["bc_document_description"]; ?>">
												@endif
												<span class="help-inline text-danger" id="bc_document_description_error"><?= $errors->first('bc_document_description')?></span>
											
											</td>
										</tr>
										<tr id="bcDocumentTypeDiv" style="display: none;">
											<td>
												Document Type
											</td>
											<td>
												@if(isset($TEMPLATE["bc_document_type"]))
													<input type="text" class="custom_text" id="bc_document_type" name="bc_document_type" value="<?php if(isset($TEMPLATE["bc_document_type"])) echo $TEMPLATE["bc_document_type"]; ?>">

													
												@else
													<input type="text" class="custom_text" id="bc_document_type" name="bc_document_type" value="<?php if(isset($TEMPLATE["bc_document_type"])) echo $TEMPLATE["bc_document_type"]; ?>" >
												@endif
												<span class="help-inline text-danger" id="bc_document_type_error"><?= $errors->first('bc_document_type')?></span>
											
											</td>
										</tr>
										
										<?php } ?>

										<!--End Blockchain-->

										<input type="hidden" id = "lock_index" name="lock_element" value="<?php if(isset($TEMPLATE["lock_element"])) echo $TEMPLATE['lock_element']; else echo 'unlock' ?>">
										<input type="hidden" id="print_with_background" name="print_with_background" value="<?php 
			                                  if(isset($TEMPLATE["background_template_status"]))
			                                  {
			                                    echo $TEMPLATE["background_template_status"];

			                                  }else{
			                                    echo "1";
			                                  }?>">

			                            <!--Blockchain-->
										<?php if($subdomain[0]=="demo"){ ?>
			                            <input type="hidden" id="is_block_chain" name="is_block_chain" value="<?php 
			                                  if(isset($TEMPLATE["is_block_chain"])&&!empty($TEMPLATE["is_block_chain"]))
			                                  {
			                                    echo $TEMPLATE["is_block_chain"];

			                                  }else{
			                                    echo "0";
			                                  }?>">
			                            <?php } ?>

										<!--End Blockchain-->

										<!-- hidden fields for QR Code -->
										<input type="hidden" name="field_qr_mapped" value="<?php if(isset($FIELDS[0]["mapped_name"])) echo $FIELDS[0]["mapped_name"]; ?>">
									     <input type="hidden" class="form-control" name="is_mapped_qr" value="<?php if(isset($FIELDS[0]["is_mapped"])) echo $FIELDS[0]["is_mapped"]; ?>">
										<input type="hidden" id="field_qr_x" name="field_qr_x" value="<?php if(isset($FIELDS[0]["x_pos"])) echo $FIELDS[0]["x_pos"]; else echo '31'; ?>">
										<input type="hidden" id="field_qr_y" name="field_qr_y" value="<?php if(isset($FIELDS[0]["y_pos"])) echo $FIELDS[0]["y_pos"]; else echo '35';?>">
										<input type="hidden" id="field_qr_width" name="field_qr_width" value="<?php if(isset($FIELDS[0]["width"])) echo $FIELDS[0]["width"]; else echo '32';?>">
										<input type="hidden" id="field_qr_height" name="field_qr_height" value="<?php if(isset($FIELDS[0]["height"])) echo $FIELDS[0]["height"]; else echo '32';?>">
										<input type="hidden" id="field_qr_sample_text" name="field_qr_sample_text" value="Dummy text">
										
										<input type="hidden" id="field_qr_image" name="field_qr_image"  value="<?php if(isset($FIELDS[0]["sample_image"])) echo $FIELDS[0]["sample_image"]; ?>">

										<input type="hidden" id="field_qr_image_chk" name="field_qr_image_chk" value="<?php if(isset($FIELDS[0]["include_image"])) echo $FIELDS[0]["include_image"]; else echo 0; ?>" >
										<input type="hidden" id="field_qr_lockIndex" name="field_qr_lockIndex" value="<?php if(isset($FIELDS[0]["lock_index"])) echo $FIELDS[0]["lock_index"]; else echo "unlock"?>">
										<!-- hidden fields for ID Barcode -->
										<input type="hidden" name="field_id_mapped" value="<?php if(isset($FIELDS[1]["mapped_name"])) echo $FIELDS[1]["mapped_name"]; ?>">
									    <input type="hidden" class="form-control" name="is_mapped_id" value="<?php if(isset($FIELDS[1]["is_mapped"])) echo $FIELDS[1]["is_mapped"]; ?>">
										<input type="hidden" class="form-control" id="field_id_x" name="field_id_x" value="<?php if(isset($FIELDS[1]["x_pos"])) echo $FIELDS[1]["x_pos"]; else echo "143"; ?>">
										<input type="hidden" class="form-control" id="field_id_y" name="field_id_y" value="<?php if(isset($FIELDS[1]["y_pos"])) echo $FIELDS[1]["y_pos"]; else echo "35"; ?>">
										<input type="hidden" class="form-control" id="field_id_width" name="field_id_width" value="<?php if(isset($FIELDS[1]["width"])) echo $FIELDS[1]["width"];else echo "55"; ?>">
										<input type="hidden" class="form-control" id="field_id_height" name="field_id_height" value="<?php if(isset($FIELDS[1]["height"])) echo $FIELDS[1]["height"];else echo "12"; ?>">
										<input type="hidden" id="field_id_text" name="field_id_text" value="Dummy text">
										<input type="hidden" id="field_id_lockIndex" name="field_id_lockIndex" value="<?php if(isset($FIELDS[1]["lock_index"])) echo $FIELDS[1]["lock_index"]; else echo "unlock"?>">
										<input type="hidden" id="field_id_visible" name="field_id_visible" value="<?php if(isset($FIELDS[1]["visible"])) echo $FIELDS[1]["visible"]; else echo 0;?>">
										<input type="hidden" id="field_id_varification" name="field_id_varification" value="<?php if(isset($FIELDS[1]["visible_varification"])) echo $FIELDS[1]["visible_varification"]; else echo 0;?>">
										<input type="hidden" id="field_qr_combo_qr_text" name="field_qr_combo_qr_text" value="<?php if(isset($FIELDS[0]["combo_qr_text"])) echo $FIELDS[0]["combo_qr_text"]; else echo '{{QR Code}}';?>">
										
										<!-- Rohit Changes -->
										<?php if($subdomain[0]=="demo"){ ?>
										@if(\Route::currentRouteName() == "template-master.edit")
											<input type="hidden" name="is_encrypted_qr_hidden" id="is_encrypted_qrHidden" class="form-control" value="<?php if(isset($FIELDS[0]["is_encrypted_qr"])) echo $FIELDS[0]["is_encrypted_qr"]; else echo "0"; ?>">

											<input type="hidden" name="encrypted_qr_text_hidden" id="encrypted_qr_textHidden" class="form-control" value="<?php if(isset($FIELDS[0]["encrypted_qr_text"])) echo $FIELDS[0]["encrypted_qr_text"]; else echo 'GUID : {{QR Code}}'; ?>">
										@endif

										<?php } ?>
										<!-- Rohit Changes -->

										<div class="form-group clearfix" id="additional_field">
										<?php 
			                                $fc = 2; 
			                                if($field_count > 2) 
			                                    for($fc = 2; $fc < $field_count; $fc++) 
			                                    {
			                            ?>
											<div class="extrafields">
												<input type="hidden" name="field_extra_mapped[]" value="<?php if(isset($FIELDS[$fc]["mapped_name"])) echo $FIELDS[$fc]["mapped_name"]; ?>">
												<input type="hidden" class="form-control" name="field_extra_name[]" value="<?php if(isset($FIELDS[$fc]["name"])) echo $FIELDS[$fc]["name"]; ?>">
												<input type="hidden" class="form-control" name="field_extra_security_type[]" value="<?php if(isset($FIELDS[$fc]["security_type"])) echo $FIELDS[$fc]["security_type"]; ?>">
												<input type="hidden" class="form-control" name="field_extra_font_id[]" value="<?php if(isset($FIELDS[$fc]["font_id"])) echo $FIELDS[$fc]["font_id"]; ?>">
												<input type="hidden" class="talign" name="field_extra_text_align[]" value="<?php if(isset($FIELDS[$fc]["text_justification"])) echo $FIELDS[$fc]["text_justification"]; ?>">
												<input type="hidden" class="fstyle" name="field_extra_font_style[]" value="<?php if(isset($FIELDS[$fc]["font_style"])) echo $FIELDS[$fc]["font_style"]; ?>">
												<input type="hidden" class="fstyle" name="field_extra_font_size[]" value="<?php if(isset($FIELDS[$fc]["font_size"])) echo $FIELDS[$fc]["font_size"]; ?>">
												<input type="hidden" class="fstyle" name="field_extra_font_case[]" value="<?php if(isset($FIELDS[$fc]["is_font_case"])) echo $FIELDS[$fc]["is_font_case"]; ?>">
												<input type="hidden" class="form-control"  name="field_extra_font_color[]" value="<?php if(isset($FIELDS[$fc]["font_color"])) echo $FIELDS[$fc]["font_color"]; ?>">
												<input type="hidden" class="form-control" id="chosen-value" name="font_color_extra[]" value="<?php if(isset($FIELDS[$fc]["font_color_extra"])) echo $FIELDS[$fc]["font_color_extra"]; ?>">
												<input type="hidden" class="form-control" name="field_extra_x[]" value="<?php if(isset($FIELDS[$fc]["x_pos"])) echo $FIELDS[$fc]["x_pos"]; ?>">
												<input type="hidden" class="form-control" name="field_extra_y[]" value="<?php if(isset($FIELDS[$fc]["y_pos"])) echo $FIELDS[$fc]["y_pos"]; ?>">
												<input type="hidden" class="form-control" name="field_extra_width[]" value="<?php if(isset($FIELDS[$fc]["width"])) echo $FIELDS[$fc]["width"]; ?>">
												<input type="hidden" class="form-control" name="field_extra_height[]" value="<?php if(isset($FIELDS[$fc]["height"])) echo $FIELDS[$fc]["height"]; ?>">
												<input type="hidden" name="field_sample_text[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["sample_text"])) echo htmlspecialchars($FIELDS[$fc]["sample_text"]); ?>">
												<!-- Add fields -->
												<input type="hidden" name="field_sample_text_width[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["field_sample_text_width"])) echo $FIELDS[$fc]["field_sample_text_width"]; ?>">
									            <input type="hidden" name="field_sample_text_vertical_width[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["field_sample_text_vertical_width"])) echo $FIELDS[$fc]["field_sample_text_vertical_width"]; ?>">
									            <input type="hidden" name="field_sample_text_horizontal_width[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["field_sample_text_horizontal_width"])) echo $FIELDS[$fc]["field_sample_text_horizontal_width"]; ?>">
												<input type="hidden" name="microline_width[]" class="form-control" value="">
												@if($FIELDS[$fc]["security_type"] == 'Qr Code')
													<input type="hidden" name="field_image1[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["sample_image"])) echo $canvas_upload_path.'/canvas/images/'.$FIELDS[$fc]["sample_image"]; ?>" multiple="true">

												@else
													<input type="hidden" name="field_image1[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["sample_image"])) echo $FIELDS[$fc]["sample_image"]; ?>" multiple="true">
												@endif
												<input type="hidden" name="angle[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["angle"])) echo $FIELDS[$fc]["angle"]; ?>">
												<input type="hidden" name="line_gap[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["line_gap"])) echo $FIELDS[$fc]["line_gap"]; ?>">
												<input type="hidden"  name="field_lockIndex[]" class="form-control"  value="<?php if(isset($FIELDS[$fc]["lock_index"])) echo $FIELDS[$fc]["lock_index"]; else echo "unlock"; ?>">

												<input type="hidden" name="length[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["length"])) echo $FIELDS[$fc]["length"]; ?>">
												<input type="hidden" name="uv_percentage[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["uv_percentage"])) echo $FIELDS[$fc]["uv_percentage"]; ?>">
									            <input type="hidden" name="is_repeat[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["is_repeat"])) echo $FIELDS[$fc]["is_repeat"]; ?>">
									            <input type="hidden" name="infinite_height[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["is_repeat"])) echo $FIELDS[$fc]["infinite_height"]; ?>">
									            <input type="hidden" name="include_image[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["include_image"])) echo $FIELDS[$fc]["include_image"]; ?>">
									            <input type="hidden" name="grey_scale[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["grey_scale"])) echo $FIELDS[$fc]["grey_scale"]; ?>">
									            <input type="hidden" name="water_mark[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["water_mark"])) echo $FIELDS[$fc]["water_mark"]; ?>">
									            <input type="hidden" name="is_uv_image[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["is_uv_image"])) echo $FIELDS[$fc]["is_uv_image"]; ?>">
									            <input type="hidden" name="is_transparent_image[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["is_transparent_image"])) echo $FIELDS[$fc]["is_transparent_image"]; ?>">
									            <input type="hidden" name="text_opicity[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["text_opicity"])) echo $FIELDS[$fc]["text_opicity"]; ?>">
									            <input type="hidden" name="visible[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["visible"])) echo $FIELDS[$fc]["visible"]; else echo 0; ?>">
									            <input type="hidden" name="visible_varification[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["visible_varification"])) echo $FIELDS[$fc]["visible_varification"]; else echo 0; ?>">
									            <input type="hidden" name="combo_qr_text[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["combo_qr_text"])) echo $FIELDS[$fc]["combo_qr_text"]; else echo '{{Dummy Text}}'; ?>">
									            <!--code for hidden value pass for preview pdf -->
									            <input type="hidden" class="form-control" name="is_mapped[]" value="<?php if(isset($FIELDS[$fc]["is_mapped"])) echo $FIELDS[$fc]["is_mapped"]; ?>">
												<!-- end code -->
												
												<?php if($subdomain[0]=="demo"){ ?>
												<!--For Block Chain -->
												<input type="hidden" name="bc_document_description[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["bc_document_description"])) echo $FIELDS[$fc]["bc_document_description"]; else echo ""; ?>">
												<input type="hidden" name="bc_document_type[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["bc_document_type"])) echo $FIELDS[$fc]["bc_document_type"]; else echo ""; ?>">

												<input type="hidden" name="is_meta_data[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["is_meta_data"])) echo $FIELDS[$fc]["is_meta_data"]; else echo "0"; ?>">
												<input type="hidden" name="field_metadata_label[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["meta_data_label"])) echo htmlspecialchars($FIELDS[$fc]["meta_data_label"]); ?>">
												<input type="hidden" name="field_metadata_value[]" class="form-control" value="<?php if(isset($FIELDS[$fc]["meta_data_value"])) echo htmlspecialchars($FIELDS[$fc]["meta_data_value"]); ?>">
												<!--Block Chain End -->

												
												<?php } ?>
											</div>
											<?php } ?>
										</div>
									</tbody>
								</table>
							</div>

						</div>
					<?=Form::close()?>
				</div>
			</div>
			<div class="container">
				<ol class="breadcrumb" style="width: 84%;">
				</ol>
				<iframe id="myframe" src="{{URL::route('canvasmaker.create')}}" style="width :710px;"></iframe>
			</div>
		</div> 
	</div>
@stop
@section('script')


	<script type="text/javascript">

		$(document).ready(function(){
			setTimeout(function () {resetFields(); }, 2500);
		})
		
		
		var default_image = '<?= $default_image?>';
		//canvas uplaod path 
		var aws_canvas_upload_path = '<?=$aws_canvas_upload_path?>'
		var canvas_upload_path = '<?=$canvas_upload_path?>'
		
	</script>
	<!-- Blockchain -->
	<?php if($subdomain[0]=="demo"){ ?>
	
	<script type="text/javascript" src="{{asset('backend/canvas/blockchainjs/canvas.js')}}"></script>
	<script type="text/javascript" src="{{asset('backend/canvas/blockchainjs/templatestyle.js')}}"></script>
	<script type="text/javascript" src="{{asset('backend/canvas/blockchainjs/extraField.js')}}"></script>
	<?php }else{ ?>
	<script type="text/javascript" src="{{asset('backend/canvas/js/canvas.js')}}"></script>
	<script type="text/javascript" src="{{asset('backend/canvas/js/templatestyle.js')}}"></script>
	<script type="text/javascript" src="{{asset('backend/canvas/js/extraField.js')}}"></script>
	<?php } ?>
	<!-- End Blockchain -->

	<script type="text/javascript" src="{{asset('backend/canvas/js/colorpicker.js')}}"></script>

	<script type="text/javascript">


		//get font style for dynamic /static text for dynamic /static text
		var font_style = '<?= $font_style?>';
		//get font size for dynamic /static text for dynamic /static text
		var font_size = '<?= $font_size?>';
		//get all fonts
		var fonts = '<?= $FONTS?>'
		var fontList = JSON.parse(fonts)
		//console.log(fontList);
		var opacity = '<?=$text_opacity?>'
		var uv_percentage_value = '<?= $uv_percentage_value?>'
		//call fromcanvas.js
		function barcodeImageChange(){
			$("[name='field_qr_image1']").on('change',function(){
				var fd = new FormData();
				var files = $('[name="field_qr_image1"]')[0].files[0];
				var template_name = $('#template_name').val();
				var token = "<?= csrf_token()?>";
				var imageType = 'Qr';

				fd.append('field_qr_image1',files);
				fd.append('template_name',template_name);
				fd.append('imageType',imageType);
				fd.append('_token',token);
				var url = "<?= route('templateMaster.barcodeImageChange')?>";
				$.ajax({

					url:url,
				    type: "POST",
					dataType: "JSON",
					data:fd,
					processData: false,
					contentType:false,
					async:false,
					success:function(imageResponse){
						if(imageResponse.data[0].type == 'error'){
							$('#field_image-error').hide();
			        		$('#error_message').removeAttr('style');
			        		$('#btnModalSave').attr('disabled', 'true');
			 				$('#qr_image_error').text(imageResponse.data[0].message);
						}
						else{
							$('.qr_image_src').show();
				        	$('.qr_image_src').attr('src',imageResponse.data[0].filename);
				        	var field_sample_text = $('#field_sample_text').val();
				        	var field_chk_val = $('#field_qr_image_chk1').val();
				        	var field_qr_width1 = $('#field_qr_width1').val();
				        	var field_qr_height1 = $('#field_qr_height1').val();
				        	var field_qr_x1 = $('#field_qr_x1').val();
				        	var field_qr_y1 = $('#field_qr_y1').val();
				        	var field_qr_lockIndex1 = $('#field_qr_lockIndex1').val();

				        	myframe.contentWindow.postFrameMessage('update',1,field_qr_x1,field_qr_y1,field_qr_width1,field_qr_height1,field_qr_lockIndex1,'','','','',field_sample_text,'','','',imageResponse.data[0].filename,'','','','','','','',field_chk_val);
				        	$('#qr_image_error').empty();
				        	$('#field_qr_image').attr('value',imageResponse.data[0].filename);
				        	$('#btnModalSave').removeAttr('disabled', 'true');
				        }
					}
				});
			});
		}

		//call from canvas.js
		function dynamicStaticImageChange(i,$id,$security_type){
			$("[name='field_image']").on('change',function(){
				var fd = new FormData();
				var files = $('[name="field_image"]')[0].files[0];
				var template_name = $('#template_name').val();
				var template_id = $('#template_id').val();
				var security_type = $('#field_extra_security_type').val();
				var grey_scale = $('#grey_scale').val();
				var is_uv_image = $('#is_uv_image').val();
				var is_transparent_image = $('#is_transparent_image').val();
				var token = "<?= csrf_token()?>";
				var imageType = 'field_image';

				fd.append('field_image',files);
				fd.append('template_name',template_name);
				fd.append('security_type',security_type);
				fd.append('grey_scale',grey_scale);
				fd.append('is_uv_image',is_uv_image);
				fd.append('is_transparent_image',is_transparent_image);
				fd.append('imageType',imageType);
				fd.append('id',template_id);
				fd.append('_token',token);
				var url = "<?= route('templateMaster.barcodeImageChange')?>";
				$.ajax({

					url:url,
				    type: "POST",
					dataType: "JSON",
					data:fd,
					processData: false,
					contentType:false,
					async:false,
					success:function(imageResponse){
						if(imageResponse.data[0].type == 'error'){
							$('#field_image-error').hide();
			        		$('#error_message').removeAttr('style');
			        		$('#btnModalSave').attr('disabled', 'true');
		     				$('#error_message').text(imageResponse.data[0].message);
						}
						else{
							if(imageResponse.data[0].security_type == 'Qr Code'){
			        			var field_extra_width = $('#field_extra_width').val();
								var field_extra_height = $('#field_extra_height').val();
								var field_sample_text = $('#field_sample_text').val();
								var include_image = $('#include_image').val();
								console.log(field_sample_text);
			        			$('.uploaded_image').attr('src',imageResponse.data[0].filename);
					        	myframe.contentWindow.postFrameMessage('update',$id,'','',field_extra_width,field_extra_height,'',imageResponse.data[0].security_type,'','','',field_sample_text,'','','',imageResponse.data[0].filename,'','','','','','','',include_image);

				        	}else{

				        		// code updated by rohit 14/01/2024
								var imageWidth = 100;
								
								ratio = imageResponse.data[0].width / imageResponse.data[0].height;
								
								height = imageWidth / ratio;

								var imageHeight = Math.floor(height);								
								// code updated by rohit
								
				        		// console.log(imageResponse);
				        		$('[name="field_extra_width[]"]')[i].setAttribute('value',imageWidth);
				        		$('[name="field_extra_height[]"]')[i].setAttribute('value',imageHeight);
				        		$('#field_extra_width').val(imageWidth)
				        		$('#field_extra_height').val(imageHeight)
				        
					        	var field_sample_text = $('#field_sample_text').val();
								if(imageResponse.data[0].template_name){
					        		// template name available
					        		var src = canvas_upload_path+'/templates/'+imageResponse.data[0].template_id+'/'+imageResponse.data[0].filename+'';

					        		// console.log(src);

					        		$('.uploaded_image').attr('src',src);
					        		myframe.contentWindow.postFrameMessage('update',$id,'','',imageWidth,imageHeight,'',$security_type,'','','',field_sample_text,'','','','',imageResponse.data[0].filename,imageResponse.data[0].template_id,'','','','','','','',imageResponse.data[0].grey_scale,'',imageResponse.data[0].is_uv_image,imageResponse.data[0].is_transparent_image);
					        		// template name not available
					        	}else{
					        		var src = canvas_upload_path+'/templates/customImages/'+imageResponse.data[0].filename+''
					        		console.log(src);
					        		$('.uploaded_image').attr('src',src);
					        		myframe.contentWindow.postFrameMessage('update',$id,'','',imageWidth,imageHeight,'',$security_type,'','','',field_sample_text,'','','','',imageResponse.data[0].filename,'','','','','','','','',imageResponse.data[0].grey_scale,'',imageResponse.data[0].is_uv_image,imageResponse.data[0].is_transparent_image);
					        	}
				        	}

				        	$('[name="field_image1[]"]')[i].setAttribute('value',imageResponse.data[0].filename);
				        	$('#error_message').hide();
				        	$('#btnModalSave').removeAttr('disabled', 'true');
				        }
					}
				});
			});
		}

		//on submit 

		$('#btnSave, #btnSaveAndClose').click(function(e){
			//set value for save and save n close button
			var bSaveAndClose = "1";
			if($(this).attr('id') == 'btnSave')
				bSaveAndClose = "0";
				$('#bSaveAndClose').val(bSaveAndClose);

			//if second tab is not click
			if(!$('#second_tab').is(':visible')){
				if($('#template_name').val() == '' || $('#template_desc').val() == '')
				{
					toastr["error"]('Empty template parameters');
				}
				if($('#field_qr_x').val() == '' || $('#field_qr_y').val() == '' || $('#field_qr_width').val() == '' || $('#field_qr_height').val() == ''){
					toastr["error"]('Empty QR code parameters');
				}
				if($('#field_id_x').val() == '' || $('#field_id_y').val() == '' || $('#field_id_width').val() == '' || $('#field_id_height').val() == ''){
					toastr["error"]('Empty barcode parameters');
				}
			}


			//validate institute form data	
			$('#template_frm').validate({
				errorElement: 'span',
				errorClass: 'help-inline',
				focusInvalid: false,
				rules: {
						template_name: {"required":true,},
						template_desc: "required",
						template_status: "required",
						height: {"required": true, min: 148, max: 841, number: true},
						width: {"required": true, min: 105, max: 594, number: true},
				},
				messages: {
					template_name: "Template name cannot be empty", 
					template_desc: "Template description cannot be empty",
					template_status: "Template status cannot be empty",
					height: "Template height cannot be empty",
					width: "Template width cannot be empty",
				},
				invalidHandler: function (event, validator) { //display error alert on form submit
					
				},
				highlight: function (e) {
					
				},
				success: function (e) {
					
				},
				errorPlacement: function (error, element) {
				},
				submitHandler: function (form) {
					$('#btnSave').attr('disabled', 'true');
					$('#btnSaveAndClose').attr('disabled', 'true');
					$('#btnPreview').attr('disabled', 'true');
					$('#btnCancel').attr('disabled', 'true');

					if(!checkFieldsinside($('#template_width').val(), $('#template_height').val()))
					{
						$('#btnSave').removeAttr('disabled');
						$('#btnSaveAndClose').removeAttr('disabled');
						$('#btnPreview').removeAttr('disabled');
						$('#btnCancel').removeAttr('disabled');
						
					}
				},
				invalidHandler: function (form) {
				}
			});


			var token = '<?= csrf_token()?>';
			var get_template_id = sessionStorage.getItem('template_id');
			var url = '<?=route('template-master.store')?>';
			var type = 'POST'; 
			console.log(get_template_id);
			$('#template_frm').attr('method', type);
			if(get_template_id == null){
				get_template_id = '';
			}
			else{
				// var url = '<?=route('template-master.update',':id')?>'
				// url = url.replace(':id',get_template_id)
				// var type = 'PATCH';
				// $('#template_frm').attr('method', type);
			}
			//alert(get_template_id);
	        $('#template_frm').ajaxSubmit({
	            url: url,
	            type: type,
	            data: { "_token" : token,'id':get_template_id},
	            dataType: 'json',
	           	beforeSubmit: function (formData, jqForm, options) {

					//check if any field is out of the page
				
				}, clearForm: false, dataType: 'json', success: function (resObj) {
					
					$('#btnSave').removeAttr('disabled');
					$('#btnSaveAndClose').removeAttr('disabled');
					$('#btnPreview').removeAttr('disabled');
					$('#btnCancel').removeAttr('disabled');
					if(resObj.data[0].type == 'success') {
						toastr["success"](resObj.data[0].message);
						if($('#bSaveAndClose').val() == 1)

							setTimeout(function(){ 
								window.location.href = "<?=route('template-master.index')?>"; 
							}, 800);
						else {


							$('#template_id').val(resObj.data[0].last_id);
							//store id in session during create
							<?php
								if(!isset($TEMPLATE['id'])){
							?>
							sessionStorage.setItem('template_id',resObj.data[0].last_id)
							<?php
								}
							?>

							if($('#is_block_chain_template').val()==1){
								$('#is_block_chain_template').prop('disabled', true);
							}
						}
					}
					else{
						toastr["error"](resObj.data[0].message);
					}
				},
				error:function(resObj){
					console.log(resObj);
					if(resObj.responseJSON.errors != undefined && $('#template_name').val() != '' && $('#template_desc').val() != ''){
						toastr["error"](resObj.responseJSON.errors.template_name);
					}
					$('#btnSave').removeAttr('disabled');
					$('#btnSaveAndClose').removeAttr('disabled');
					$('#btnPreview').removeAttr('disabled');
					$('#btnCancel').removeAttr('disabled');
					$.each(resObj.responseJSON.errors,function(k,v){
						$('#'+k+'_error').css('display','block');
						$('#'+k+'_error').text(v);
					})
				}

	        });	

		});

		//on cancel button click
		$('#btnCancel').click(function(){
			window.location = '<?= route('template-master.index')?>'
		})

		function checkFieldsinside(width, height)
		{
			width = parseInt(width);
			height = parseInt(height);
			var count = $('[name="field_extra_name[]"]').length,
			areFieldsIn = true;

			if(parseInt($('#field_qr_x1').val()) >= width || parseInt($('#field_qr_y1').val()) >= height)
			{
				areFieldsIn = false;
			}
			if(parseInt($('#field_id_x1').val()) >= width || parseInt($('#field_id_y1').val()) >= height)
			{
				areFieldsIn = false;
			}

			for(var i = 0; i < count; i++)
			{
				if( $('[name="field_extra_x[]"]')[i].getAttribute('value') >= width ||
				$('[name="field_extra_y[]"]')[i].getAttribute('value') >= height)
				{
					areFieldsIn = false;        
				}
			}
			return areFieldsIn;
		}


		//on background template change
		$('#bg_template_id').change(function (event) {
			var myframe = document.getElementById('myframe');
			if($('#bg_template_id').val() != 0) {
				// get width height of selected background
				var url = '<?= route('templateMaster.bgtemplatechanges')?>',
				data = {id: $('#bg_template_id').val()};
				$.get(url, data, function (resp) {
					if(!checkFieldsinside(resp.width, resp.height))
					{
						toastr["error"]("Can't change the template as some fields will be outside the boundary!");
						$('#bg_template_id').val(previousBgId);
						return false;
					}
					// set background
					myframe.contentWindow.setBackground($('#bg_template_id').val(), $('#template_width').val(), $('#template_height').val());
					// set iframe
					var h = (resp.height * 700) / resp.width;
					myframe.height = h + 15;
					for(var i = 0; i < 5000; i++);
					myframe.contentWindow.refreshCanvas();

					updateAllFields();
				}, 'json');
			}
			else {
				myframe.contentWindow.setBackground($('#bg_template_id').val(), $('#template_width').val(), $('#template_height').val());
				// set iframe
				var h = ($('#template_height').val() * 700) / $('#template_width').val();
				myframe.height = h + 15;
				for(var i = 0; i < 5000; i++);
				myframe.contentWindow.refreshCanvas();
			}
		});

		//check if backgroung is present then take its width and width comes from background template master db else take default width
		function showHeightWidth() {
		    if($('#bg_template_id').val() == 0){
		    }
		    else {
		      	// get width height
		      	var url = '<?= route('templateMaster.bgtemplatechanges')?>',
		      	data = {id: $('#bg_template_id').val()};
		      	$.get(url, data, function (resp) {
			    }, 'json');
		    }
		    previousBgId = $('#bg_template_id').val();
		}

		//on font style change
		function fontStyleChange(font_id){
			var url="<?= route('templateMaster.fontChange')?>";
			var token = '{{csrf_token()}}';
			var data = {'id':font_id,'_token':token}
			$.post(url, data, function (resp) {
				var filename = resp.font_filename
				console.log(filename,'sewak');
				var dirPath ='C:\\wamp64\\www\\uneb\\public\\';//'<?= public_path()?>';
				//dirPath.replace("\", "//");
				var font_path = dirPath+'backend\\fonts\\'+filename+'.ttf';
				console.log($('head'))
				$('head').prepend("<style type=\"text/css\">" + 
                                "@font-face {\n" +
                                    "\tfont-family: '"+filename+".ttf'\n" + 
                                    "\tsrc: url('"+font_path+"') format('truetype');\n" + 
                                "}\n" + 
                            "</style>");
			}, 'json');

		}

		//on preview button click
		$('#btnPreview').click(function(event){
			/*
			*  PDF Preview is shown
			*  Process: 
			*  1. Data is posted to PHP
			*  2. PHP saves data as in User Session
			*  3. In new Window Preview request is sent 
			*/


			event.preventDefault();
			/* get text default width,vertical Width and Horizontal width from localStorage  */ 
			if(localStorage.getItem('defaultvalue') != null){
				var defaultvalue = localStorage.getItem('defaultvalue');


				defaultvalue_json = JSON.parse(defaultvalue);
				str_default = defaultvalue_json.toString();
				$('[name="field_sample_text_width[]"]').val(str_default);

				var vertical_value = localStorage.getItem('vertical_value')

				vertical_value_json = JSON.parse(vertical_value);

				str_vertical = vertical_value_json.toString();
				$('[name="field_sample_text_vertical_width[]"]').val(str_vertical);

				var horizontal_value = localStorage.getItem('horizontal_value')

				horizontal_value_json = JSON.parse(horizontal_value);

				str_horizontal = horizontal_value_json.toString();

				$('[name="field_sample_text_horizontal_width[]"]').val(str_horizontal);
			}
			if(localStorage.getItem('microline_width') != null){

				var microline_width = localStorage.getItem('microline_width');

				microlineWidth_json = JSON.parse(microline_width);
				str_microline_width = microlineWidth_json.toString();
				$('[name="microline_width[]"]').val(str_microline_width);
			}
			var url = '<?=route('preview.store')?>',
			data = $('#template_frm').serialize();
			
			$.post(url, data, function (resp) {
				if(window.newwinow)
				window.newwinow.close();
				window.newwinow = window.open('<?=route('template-master.preview.pdf')?>', 'mywindow','width=600,height=800');
			});
		})
		/**Blockchain**/
		var isBlockChain="<?php if(isset($TEMPLATE["is_block_chain"])){ echo $TEMPLATE["is_block_chain"];}else{echo "0";}?>";
		if(isBlockChain==1){
			$('#bcDocumentDescriptionDiv').show();
			$('#bcDocumentTypeDiv').show();
		}
		/**End Blockchain**/

	</script>
	


	

@stop