<?php $__env->startSection('content'); ?>

<style type="text/css">
    #progress-file {
    width: 100%;
    border: 1px solid #aaa;
    height: 20px;
}
#message-file {
    color: green;
}
#progress-file .bar {
    background-color: #684791;
    color: white;
    height: 18px;
    font-size: 14px;
    text-align: center;
} 
#progress-folder {
    width: 612px;
    border: 1px solid #aaa;
    height: 20px;
}
#message-folder {
    color: green;
}
#progress-folder .bar {
    background-color: #684791;
    color: white;
    height: 18px;
    font-size: 14px;
    text-align: center;
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
#toast-container > .toast {
background-image: none !important;
}

.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}


.blockchain_switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
  margin-left: 20px;
}

.blockchain_switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
<?php 
$domain = \Request::getHost();
$subdomain = explode('.', $domain);
$bg_file_directory='http://'.$domain.'/'.$subdomain[0].'/backend/canvas/bg_images/';
?>
	<div class="container">
		<div class="col-xs-12">
			<div class="clearfix">
				<form method="post" id="templfrm">				
					<input type="hidden" name="edit" id="edit_id">
				</form>
				<div class="modal fade" id="viewInfo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content" style="width: 1200px;left: -440px;">       
                    <div class="modal-body" id="ajaxContent"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>             
                </div>
            </div>
        </div>	
        <!-- Process Modal -->
        <div id="uploadModal" class="modal fade" role="dialog">
            <div class="modal-dialog" style="max-width: 650px;">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="process-title">Process</h6>
                        <button type="button" class="close" id="process_close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>        
                    </div>
                    <div class="modal-body">
                        <input id="template_id" type="hidden" name="template_id" value="0">
                        <input id="pdf_page" type="hidden" name="pdf_page" value="0">      
                        <input id="pdf_flag" type="hidden" name="pdf_flag" value="">  
                        <!-- Form -->
                        <form id="form_process" method='post' action='' enctype="multipart/form-data">
                    
                            <?php if($subdomain[0] == 'demo'||$subdomain[0] == 'newserver'|| $subdomain[0] == 'mmk') { ?> 
                            <div class="form-group">
                                <label class="control-label col-sm-3" id="generate_title" for="previewPdf">PDF Preview:</label>
                                <label class="switch">  
                                    <input type="checkbox" class="form-control" id="previewPdf" name="previewPdf" value="1" checked="">
                                    <span class="slider round"></span>
                                </label>
                                <input type="hidden" class="form-control" id="previewPdfValue" name="previewPdfValue" value="1">
                            </div>

                            

                            

                            


                            <?php } ?>

                            Select file : <input type='file' name='file' id='file_process' accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class='form-control'><br />
                            <input type='button' class='btn btn-info' value='Upload' id='btn_upload'>
                        </form>
                        <div class="py-1"></div>
                        <div id="progress-file" style="display:none;"></div>
                        <div id="message-file"></div>   
                        <div id="single-file"></div>   
                        <!-- Preview-->
                        <div id='preview'></div>
                    </div> 
                </div>
            </div>
        </div>

        <div id="folderModal" class="modal fade" role="dialog">
          <div class="modal-dialog" style="max-width: 650px;">
            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-header">
                <h6 class="modal-title" id="process-title2">Process</h6>
                <button type="button" class="close" id="process_close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>        
              </div>
              <div class="modal-body">
                <!-- Form -->
                <form id="folder_process" method='post' action='' enctype="multipart/form-data">
                  Folder :
                    <select id="folder-process" name="folder" class="form-control">
                    <option value = "">Select</option>
                    <!--<option value = "multi_pages">Default</option>-->
                    <option value = "Auto Create">Auto Create</option>
                    </select>                
                  <br>
                  <input type="file" name="files[]" id="folderprocess" accept="application/pdf" style="display:none;" multiple /><br /><br />
                  <input type='button' class='btn btn-info' value='Submit' id='btn_folder'>
                </form>
                <div id="progress-folder" style="display:none;"></div>
                <div id="message-folder"></div>   
                <div id="multi-file"></div>     
                <!-- Preview-->
                <div id='preview2'></div>
              </div> 
            </div>
          </div>
        </div>
                
        <div class="modal fade" id="editTtitle" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							
                            <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="modal-title" id="myModalLabel">Edit Title</h4>
							</div>
							<div class="modal-body">
								<form method="post" action='' id="UserData">
									<div class="form-group">
										<label>Title</label>
										<input type="text" class="form-control" id="template_title" name="template_title" maxlength="100">
										<span id="template_title_error" class="help-inline text-danger"></span>
									</div>
                                    <?php if($subdomain[0]=="demo"||$subdomain[0]=="konkankrishi"){ ?>    
                                    <div class="form-group bc_toggle">
                                        <b>Upload Data To Blockchain</b> <br />
                                        <small class="text-danger">Once it's enabled, you can't disable it.</small>
                                        <br />
                                        <label class="switch">
                                            <input type="checkbox" id="bccheck">
                                            <span class="slider round"></span>
                                        </label> 
                                    </div>
                                    <div class="form-group bc_input" style="display:none;">
                                        <label>Document Description</label>
                                        <input type="text" class="form-control" id="bc_document_description" name="bc_document_description" maxlength="200">
                                        <span id="template_desc_error" class="help-inline text-danger"></span>
                                        <label>Document Type</label>
                                        <input type="text" class="form-control" id="bc_document_type" name="bc_document_type" maxlength="200">
                                        <span id="template_type_error" class="help-inline text-danger"></span>
                                    </div> 
                                    <?php } ?> 
									<input type="hidden" name="bc_flag" id="bc_flag" value="0">
									<input type="hidden" name="id" id="tem_id">
									<div class="form-group clearfix">
                                        <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="UserSave"><i class="fa fa-save"></i> Save</button>
									    <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="UserEdit"><i class="fa fa-save"></i> Update</button>										
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

        <div class="modal fade" id="modalBG" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  aria-hidden="true">
          <?php 
          $results = DB::select( DB::raw("SELECT * FROM background_template_master where status='1'")); 
          //print_r($results);
          ?>         
              
          <div class="modal-dialog" role="document" style="max-width: 530px;">
            <div class="modal-content">                                  
              <div class="modal-header text-center">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="modal-title w-100" id="bg-title">Assign Background</h6> 
                
              </div>
              <div class="modal-body mx-3">
                <div class="form-group mb-2">
                    <label><strong>Print Background</strong></label>              
                    <select id="print_bg_id" name="print_bg_id" class="form-control">
                    <option value="0" data-file="0">Select BG</option>
                    <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $got): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($got->id); ?>" data-file="<?php echo e($got->image_path); ?>"><?php echo e($got->background_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                                             
                    <input type="checkbox" id="print_bg_status" name="print_bg_status" style="width:15px;height:15px;"> Use as background
                </div>
                <div><img id="image_print" src='' width="30%"></div>
                <div class="form-group">
                  <label><strong>Verification Background</strong></label>
                  <select id="verification_bg_id" name="verification_bg_id" class="form-control">
                  <option value="0" data-file="0">Select BG</option>
                    <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $got): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($got->id); ?>" data-file="<?php echo e($got->image_path); ?>"><?php echo e($got->background_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                  </select>      
                    <input type="checkbox" id="verification_bg_status" name="verification_bg_status" style="width:15px;height:15px;"> Use as background    
                </div>
                <div><img id="image_verification" src='' width="30%"></div>
              </div>
              <div class="modal-footer d-flex justify-content-center">
                <button type="submit" class="btn btn-success" id="btn_bg">Save</button>
                <input type="hidden" id="record_id" name="record_id" class="form-control">
              </div>
                
            </div>
          </div>
        </div>                        
                        
                        

				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i> Template Master (EXCEL2PDF)
								  <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('templatemangement')); ?></ol>
                                    <i class="fa fa-info-circle iconModalCss" title="User Manual" id="pdf2pdfClick"></i>								
                                </h1>
							</div>
						</div>
						<div class="">
                            <ul class="nav nav-pills" id="pills-filter">
                                <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Template </a></li>
                                <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Template</a></li>						
                                <?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.templatemaker')): ?>
                                <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatemaker')): ?>	
								<li style="float: right;">
									<span  id="addtemplate" class="btn btn-theme" style="background: rgb(0 82 204); color: #FFF;"><i class="fa fa-plus"></i> Add Template</span>	
								</li>
								<?php endif; ?>
								<?php endif; ?>
							</ul> 
							<table id="example" class="table table-hover display" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>#</th>
										<th>Template Title</th>
										<th>Template Name</th>
										<th>Pdf Page</th>

                    <?php if($subdomain[0]=="icat" || $subdomain[0]=="demo"||$subdomain[0]=="konkankrishi"){ ?>
                        <th>Contract</th>
                        <?php } ?>
										<th>Action</th>
									</tr>
								</thead>
								<tfoot>
								</tfoot>
							</table>
						</div>
						
					</div>
				</div>

			</div>
		</div>
	</div>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>

<script type="text/javascript">
	
    var subdomain='<?php echo $subdomain[0];?>';
    console.log('<?php echo $subdomain[0];?>');
	  var timer;
  	$.ajax({
  		type:'get',
  		url : "<?=route('templateMaster.check-sandbox')?>",
  		dataType:'json',
  		success:function(response){
  			if(response.sandboxing == 1){
  				
  	        }else{

  				$('#sandboxing_message').text('');
  	        }
  		}
  	});
    function copyTemplate(id){

    	var url="<?php echo e(URL::route('template-master.copyTemplate.copy')); ?>";
    	var token="<?php echo e(csrf_token()); ?>";
    	var method_type="post";
        bootbox.confirm("Are you sure you want to copy?",function(result){	
          if(result){
    	      $.post(url,{'_token':token,'template_id':id}, function(data) {
    	      	if(data.success==true){
                  toastr.success(data.msg);
                  oTable.ajax.reload();
    	      	}
    	    });
          }
        });
    }
	   
    var oTable = $('#example').DataTable({
		  'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
      "bProcessing": false,
      "bServerSide": true,
      "autoWidth": true,
      <?php if($subdomain[0]=="icat" || $subdomain[0]=="demo"||$subdomain[0]=="konkankrishi"){ ?>
      "aaSorting": [[4, "desc"] ],
      <?php } else{ ?>
      "aaSorting": [[3, "desc"] ],
      <?php } ?>
      "sAjaxSource":"<?= URL::route('excel2pdf.templatelist',['status'=>1])?>",
      "aoColumns":[
    		{mData: "rownum", bSortable:false, sWidth: "5%"},
    		{mData: "template_title", sWidth: "20%"},
    		{mData: "template_name", sWidth: "25%"},
    		{mData: "pdf_page", sWidth: "10%"},

         <?php if($subdomain[0]=="icat" || $subdomain[0]=="demo"||$subdomain[0]=="konkankrishi"){ ?>
            {mData: "bc_contract_address", bSortable: false, sWidth: "10%", sClass: "text-center"},
        <?php } ?>
    		{mData: 'id',
            bSortable: false,
            sWidth: "20%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            	var edit_url = "<?php echo e(route('template-master.edit',':id')); ?>"
            	edit_url = edit_url.replace(':id',o['id']);

            	var map_url =  "<?php echo e(route('template-master.template-map.edit',':id')); ?>"
            	map_url = map_url.replace(':id',o['id']);
            	var buttons = '';
            	
            	buttons = '<?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> <span style="cursor:pointer"><i title="Edit Template" id="editData" data-id="'+o['id']+'" data-temp="'+o['template_name']+'" data-page="'+o['pdf_page']+'" data-file="'+o['file_name']+'" data-toggle="tooltip" class="editrow fa fa-edit fa-lg green"></i> </span> <?php endif; ?> <?php endif; ?>';

                buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.duplicatetemplate')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.duplicatetemplate')): ?> <span style="cursor:pointer" data-id="'+o['id']+'" id="dupData" class="duplicateData" ><i title="Copy Template" class="copyrow fa fa-copy fa-lg yellow"></i></span> <?php endif; ?> <?php endif; ?>';
                
                buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.processpdf')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.processpdf')): ?> <span style="cursor:pointer" id="processData" data-id="'+o['id']+'" data-temp="'+o['template_name']+'" data-pdf_page="'+o['pdf_page']+'" data-file="'+o['file_name']+'" data-template_name = "'+o['template_name']+'" data-flag=""><i title="Generate PDF"  class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i></span> <?php endif; ?> <?php endif; ?>';   
                
                if(o['publish']==1){ rcheck="checked"; title_str="Inactivate Template"; }else{ rcheck=""; title_str="Activate Template";}        
                buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> &nbsp;<span title="'+title_str+'" style="cursor:pointer"><input name="ai'+o['id']+'" value="'+o['id']+'" type="checkbox" '+rcheck+'></span> <?php endif; ?> <?php endif; ?>';
                <?php if($subdomain[0]=="icat"){ ?>
                    var assign_url = "<?php echo e(route('excel2pdf.AssignLab',':id')); ?>"
                    assign_url = assign_url.replace(':id',o['id']);
                    buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> &nbsp;<span title="Assign Lab" style="cursor:pointer"><a href="'+assign_url+'"><i class="pdfGenerate fa fa-code-fork fa-lg blue"></i></a></span> <?php endif; ?> <?php endif; ?>';
                <?php } ?>
                buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> <span style="cursor:pointer"><i title="Edit Template Title" id="editTitle" data-id="'+o['id']+'" data-temp="'+o['template_name']+'" data-title="'+o['template_title']+'" data-isblockchain="'+o['is_block_chain']+'" data-toggle="tooltip" class="editrow fa fa-edit fa-lg"></i> </span> <?php endif; ?> <?php endif; ?>';
                <?php //if($subdomain[0]=="demo"){ ?>
                buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?> <span style="cursor:pointer"><i title="Assign Background" id="assignBG" data-id="'+o['id']+'" data-title="'+o['template_title']+'" data-pbg = "'+o['print_bg_file']+'" data-pbgs = "'+o['print_bg_status']+'" data-vbg = "'+o['verification_bg_file']+'" data-vbgs = "'+o['verification_bg_status']+'" data-toggle="tooltip" class="editrow fa fa-image fa-lg green"></i> </span> <?php endif; ?> <?php endif; ?>';
                <?php //} ?>
                buttons += '<?php if(App\Helpers\SitePermissionCheck::isPermitted('excel2pdf.processpdf')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.processpdf')): ?> <span style="cursor:pointer" id="processData" data-id="'+o['id']+'" data-temp="'+o['template_name']+'" data-pdf_page="'+o['pdf_page']+'" data-file="'+o['file_name']+'" data-template_name="'+o['template_name']+'" data-flag="invisible"><i title="Generate Invisible PDF"  class="pdfGenerate fa fa-file-pdf-o fa-lg yellow"></i></span> <?php endif; ?> <?php endif; ?>';
                return buttons;
            
            },   		   	
        },
	    ],

	    "createdRow": function( row, data, dataIndex ) {
    		if(data['status'] == 'Active'){
    			$(row).addClass( 'active-student' );
    		}else{
    			$(row).addClass( 'inactive-student' );
    		}
	    }
    });

    //for displaying activate user(status = 1)
    $('#success-pill').click(function(){

        var url="<?= URL::route('excel2pdf.templatelist',['status'=>1])?>";
        oTable.ajax.url(url);
        oTable.ajax.reload();
        $('.loader').removeClass('hidden');
    });


    // preview and generate
    $('#previewPdf').on('change',function(){
       var value = $('#previewPdfValue').val();

       if(value==1){
            $('#previewPdfValue').val(0);
            $('#previewPdfCheckbox').hide();
            $('#generate_title').html('Generate Live PDF:');

            //$('#blockchain_generate_title').show();
		    $('.blockchain_div').show();

       }else{
            $('#previewPdfValue').val(1);
            $('#previewPdfCheckbox').show();
            $('#generate_title').html('PDF Preview:');
            //$('#blockchain_generate_title').hide();
			$('.blockchain_div').hide();

            $('#blockchainPdfValue').val(0);
            $('#blockchainPdfCheckbox').hide();
            $('#blockchainPdf').prop('checked', true);
            $('#blockchain_generate_title').html('<span style="color: red;margin-left: 5px;    margin-top: 10px;">Generating Test Blockchain Documents.</span>');

       }

    });


    // preview and generate
    $('#blockchainPdf').on('change',function(){
       var value = $('#blockchainPdfValue').val();

       if(value==1){
            $('#blockchainPdfValue').val(0);
            $('#blockchainPdfCheckbox').hide();
            $('#blockchain_generate_title').html('<span style="color: red;margin-left: 5px;    margin-top: 10px;">Generating Test Blockchain Documents.</span>');

            

       }else{
            $('#blockchainPdfValue').val(1);
            $('#blockchainPdfCheckbox').show();
            $('#blockchain_generate_title').html('<span style="color: green;margin-left: 5px;    margin-top: 10px;">Generating Live Blockchain Documents.</span>');
            
       }

    });
    


    // $('#blockchain_pdf_option').on('change',function(){
	// 	var value = $(this).val();
	// 	var option = 0;
	// 	if(value == 0){
	// 		$(this).val(1);
	// 		$('#blockchainToggleText').html('<span style="color: green;margin-left: 5px;margin-top: 10px;">Generating Live Blockchain Documents.</span>');
	// 	}else{
	// 		$(this).val(0);
	// 		$('#blockchainToggleText').html('<span style="color: red;margin-left: 5px;margin-top: 10px;">Generating Test Blockchain Documents.</span>');
	// 	}
	// 	var val = $('#blockchain_pdf_option').val();
	// 	$('#blockchain_pdf_local_option').val(val);
	// });


    


    //for displaying inactivate user(status = 0)
    $('#fail-pill').click(function(){

        var url="<?= URL::route('excel2pdf.templatelist',['status'=>0])?>";
        oTable.ajax.url(url);
        oTable.ajax.reload();
        $('.loader').removeClass('hidden');
    });

    oTable.on('draw', function () {
    	$('[title]').tooltip(); 
        $(".loader").addClass('hidden');
    });
    
    oTable.on('click','#editData',function(e){
      var id = $(this).attr('data-id');
      var token = "<?= csrf_token()?>"; 

      var form = $(document.createElement('form'));
      $(form).attr("action", "<?= URL::route('excel2pdf.templatemakeredit')?>");
      $(form).attr("method", "POST");

      var input = $("<input>").attr("type", "hidden").attr("name", "id").val(id);
      $(form).append($(input));
      var input = $("<input>").attr("type", "hidden").attr("name", "_token").val(token);
      $(form).append($(input));
      $(document.body).append(form);
      $(form).submit();
        
    });
    
    oTable.on('click', '#editTitle', function (e) { 
	    $('#editTtitle').modal('show');
        $("#UserEdit").show();
        $("#UserSave").hide();
        $('#UserData')[0].reset();	
        template_title=$(this).attr('data-title');
        $('#template_title').val(template_title);		
        $id = $(this).data('id'); 
        $('#tem_id').val($id); 
        isblockchain = $(this).data('isblockchain'); 
		if(isblockchain==1){$('.bc_input').hide();$('.bc_toggle').hide();}else{$('.bc_toggle').show();}
	 });

  <?php if(App\Helpers\RolePermissionCheck::isPermitted('excel2pdf.templatemakeredit')): ?>
  	//edit data
  	$('#UserEdit').click(function(e){
  		e.preventDefault();
  		var token = '<?= csrf_token()?>';
  		var id = $('#tem_id').val(); 
        var template_title = $('#template_title').val(); 
        var bc_flag = $('#bc_flag').val(); 
  		var bc_document_description = $('#bc_document_description').val(); 
  		var bc_document_type = $('#bc_document_type').val(); 
  		var update_url = "<?=route('excel2pdf.templatemasterupdate',':id')?>";
  		update_url = update_url.replace(':id',id)
  		//console.log(update_url);
          if(template_title==''){
              alert("Please enter Title.");
              return false;
          }
  		if(bc_flag=='1'){
              if(bc_document_description==''){
                  alert("Please enter Document Description.");
                  return false;
              }
              if(bc_document_type==''){
                  alert("Please enter Document Type.");
                  return false;
              }
          }
          $('#UserData').ajaxSubmit({
              url: update_url,
              type: 'post',
              data: { "_token" : token, id:id, template_title:template_title, bc_flag:bc_flag, bc_document_description:bc_document_description, bc_document_type:bc_document_type},
              dataType: 'json',
              beforeSubmit: function (){				
  				$("#UserEdit i").removeClass('fa-save');
  				$("#UserEdit i").addClass('fa-spinner');
  				$("#UserEdit i").addClass('fa-spin');
  			},			
  			complete: function(){
  				$("#UserEdit i").addClass('fa-save');
  				$("#UserEdit i").removeClass('fa-spinner');
  				$("#UserEdit i").removeClass('fa-spin');
  				//console.log('test');
  			},
              success:function(data)
  			{	

  				$('#editTtitle').modal('hide');
  				toastr.success('Title successfully edited.');
  				oTable.ajax.reload(); 
  				$('.help-inline').text('');

  			},
  			error:function(resobj)
  			{
  				
  				/*$.each(resobj.responseJSON.errors, function(k,v){
  				   $('#'+k+'_error').css('display','block')
  				   $('#'+k+'_error').text(v);
  				});*/
  			},

          });	
  	});
  <?php endif; ?>    

  //duplicate
	oTable.on('click', '.duplicateData', function (e) {
    $id = $(this).data('id');
		
    var token="<?php echo e(csrf_token()); ?>";
    bootbox.confirm({
      message : "Do you want to create duplicate template?",
      size: 'large',
      buttons : {
          confirm: {
              label: "Yes",
              className: 'btn-success'
          },
          cancel : {
              label: "No",
              className: 'btn-danger'
          }
      },
      callback: 
      function(result) {
          if(result) {
      			$.post('<?=route('excel2pdf.duplicatetemplate')?>',{'_token':token,'id':$id},function(Result){			
      				var data = JSON.parse(Result);
              if(data.rstatus=='invalid') {
                  alert('Invalid File');
              }                 
              else if(data.rstatus=='Success') {
              	toastr["success"](data.template_name+" created.");                    
                  oTable.ajax.reload();
              }
              else {                    
                  toastr["error"]('Record not found.');
              }                 
      			});
          }
          
      }
    });
		//}
		return false;		

	});	
      
    



  $('#addtemplate').click(function(){
  	$.ajax({
  		type:'get',
  		url : "<?=route('templateMaster.checkLimit')?>",
  		dataType:'json',
  		success:function(response){
  			if(response.type == 'success'){
  				window.location.href = '<?=route('excel2pdf.templatemaker')?>'
  			}else{
  				
  			}
  		}
  	})
  });

  $('.progress').hide();
  $('.pdf_progress').hide();
  $('.time_details').hide();

  //process
	oTable.on('click', '#processData', function (e) {       
  		$('.error').hide();          
        $('#preview').html('');
        $('#preview2').html('');
  		$('#btn_upload').show();
        $('#btn_folder').show();        
        template_name = "Generate PDFs &raquo; "+$(this).data('template_name');
        id = $(this).data('id');
  		pdf_page = $(this).data('pdf_page');
  		pdf_flag = $(this).data('flag');
        $("#process-title").html(template_name);        
        $("#template_id").val(id);
        $("#pdf_page").val(pdf_page);        
        $("#pdf_flag").val(pdf_flag);       
        if(pdf_page=="Single"){
            $('#progress-file').hide('');
            $('#progress-file').html('');
            $('#message-file').html('');
            $('#single-file').html('');      
            $('#form_process')[0].reset();
            $('#uploadModal').modal({backdrop: 'static', keyboard: false, show: true});
        }
        if(pdf_page=="Multi"){
            $('#progress-folder').hide('');
            $('#progress-folder').html('');
            $('#message-folder').html('');
            $('#multi-file').html('');                
            $("#folderprocess").hide();
            $('#folder_process')[0].reset();
            $("#process-title2").html(template_name);
            $('#folderModal').modal({backdrop: 'static', keyboard: false, show: true});
        }        
        
	});		

  function readTextFile(file,callback)
  {
      var rawFile = new XMLHttpRequest();
      rawFile.open("GET", file, false);
      rawFile.onreadystatechange = function ()
      {
          if(rawFile.readyState === 4)
          {
              if(rawFile.status === 200 || rawFile.status == 0)
              {
                  var allText = rawFile.responseText;
                  if(callback) callback(allText);
              }
          }
      }
      rawFile.send(null);
  }    
    
  function refreshProgress(filename='') {        
      src='../'+subdomain+'/processed_pdfs/'+filename        
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
          if (this.readyState === this.DONE) {
              if (xhr.status === 200) {
                  readTextFile(src,function(allText){
                      if(allText){
                          const obj = JSON.parse(allText);
                          var percent=obj.percent;
                          var msg=obj.message;
                          $("#progress-file").html('<div class="bar" style="width:' + percent + '%">' + percent + '%</div>');
                          $("#message-file").html(msg);
                          if (percent == 100) {       
                              window.clearInterval(timer);
                              var beginning_time=obj.beginning_time;
                              var ending_time=obj.ending_time;
                              var exec_time=obj.exec_time;
                              var hms_time=obj.hms_time;
                              var page_time=obj.page_time;
                              var pages_processed=obj.pages_processed;
                              time_msg= "<div class='py-1'></div><table class='table table-bordered'><tbody><tr><td width='50%'>Pages Processed</td><td>"+pages_processed+"</td></tr><tr><td>Start Time</td><td>"+beginning_time+"</td></tr><tr><td>End Time</td><td>"+ending_time+"</td></tr><tr><td>Execution Time</td><td>"+hms_time+"</td></tr><tr><td>Time Per Page</td><td>"+page_time+"</td></tr></tbody></table>"; 
                              $("#single-file").html(time_msg);                                
                              timer = window.setInterval(completed(filename), 1000);
                          }  
                      }    
                  });  
                  
              } else {
                  return false;
              }
          }
      }
      xhr.timeout = 5000;           // TIMEOUT SET TO PREFERENCE (5 SEC)
      xhr.open('HEAD', src, true);
      xhr.send(null);               // VERY IMPORTANT        
  }

  function refreshProgressMulti(filename='') {        
      src='../'+subdomain+'/processed_pdfs/'+filename        
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
          if (this.readyState === this.DONE) {
              if (xhr.status === 200) {
                  readTextFile(src,function(allText){
                      if(allText){
                          const obj = JSON.parse(allText);
                          var percent=obj.percent;
                          var msg=obj.message;
                          $("#progress-folder").html('<div class="bar" style="width:' + percent + '%">' + percent + '%</div>');
                          $("#message-folder").html(msg);
                          if (percent == 100) {       
                              window.clearInterval(timer);
                              var beginning_time=obj.beginning_time;
                              var ending_time=obj.ending_time;
                              var exec_time=obj.exec_time;
                              var hms_time=obj.hms_time;
                              var page_time=obj.page_time;
                              var pages_processed=obj.pages_processed;
                              time_msg= "<div class='py-1'></div><table class='table table-bordered'><tbody><tr><td width='25%'>Pages Processed</td><td>"+pages_processed+"</td></tr><tr><td>Start Time</td><td>"+beginning_time+"</td></tr><tr><td>End Time</td><td>"+ending_time+"</td></tr><tr><td>Execution Time</td><td>"+hms_time+"</td></tr><tr><td>Time Per Page</td><td>"+page_time+"</td></tr></tbody></table>"; 
                              $("#multi-file").html(time_msg);                                
                              timer = window.setInterval(completed(filename), 1000);
                          }  
                      }    
                  });  
                  
              } else {
                  return false;
              }
          }
      }
      xhr.timeout = 5000;           // TIMEOUT SET TO PREFERENCE (5 SEC)
      xhr.open('HEAD', src, true);
      xhr.send(null);               // VERY IMPORTANT        
  }
  
  function completed(progress_file) {
      
     /* $.ajax({
          url: "<?=route('excel2pdf.createtextfile')?>"+subdomain+"/create_textfile.php?file="+progress_file+"&status=end",
          success:function(data){}
      });*/
      $.ajax({
          url: '<?=route('excel2pdf.createtextfile')?>',
          type:"POST",
          data:{'_token':'<?= csrf_token()?>',"file":progress_file,"status":"end"},
          success:function(data){}
      });           
      window.clearInterval(timer);
  }
    
  function wait(ms){
    var start = new Date().getTime();
    var end = start;
    while(end < start + ms) {
      end = new Date().getTime();
    }
  }
  
  $('#btn_upload').click(function(){
      
      var template_id=$('#template_id').val(); 
      var pdf_page=$('#pdf_page').val();
      var pdf_flag=$('#pdf_flag').val();
      var previewPdf=$('#previewPdfValue').val();
      var blockchainPdf=$('#blockchainPdfValue').val();
      var progress_file=Math.floor(Math.random() * Math.floor(Math.random() * Date.now()))+".txt"; 
      var files = $('#file_process')[0].files[0];
      if(typeof files === 'undefined'){ alert("Please select PDF."); return false;}
      /*$.ajax({
          url: "../"+subdomain+"/create_textfile.php?file="+progress_file+"&status=start",
          success:function(data){}
      }); */
      $.ajax({
          url: '<?=route('excel2pdf.createtextfile')?>',
          type:"POST",
          data:{'_token':'<?= csrf_token()?>',"file":progress_file,"status":"start"},
          success:function(data){}
      });         
      wait(1000);  //1 second in milliseconds
      $('#progress-file').show();
      $('#message-file').show();
      $('#single-file').show();
      $('#preview').show();
      
      var fd = new FormData();
      fd.append('file',files);
      fd.append('template_id',template_id);
      fd.append('pdf_page',pdf_page);
      fd.append('pdf_flag',pdf_flag);
      fd.append('previewPdf',previewPdf);
      fd.append('blockchainPdf',blockchainPdf);
      fd.append('progress_file',progress_file);
      console.log(progress_file);
      fd.append('_token','<?= csrf_token()?>');
      $.ajax({
          url: '<?=route('excel2pdf.processpdf')?>',
          type: 'post',
          data: fd,
          contentType: false,
          processData: false,
          beforeSend : function() {
              $('#btn_upload').hide();
              $('#preview').html('Processing...');                
          },      
          xhr: function() {                
              var xhr = $.ajaxSettings.xhr();
              xhr.upload.onprogress = function(e) {
                  console.log(Math.floor(e.loaded / e.total *100) + '%');
              };
              return xhr;
          },
          success: function(response){
                var res = eval('('+response+')');                
                if(res.type == 'Success'){                    
                    $('#preview').fadeIn().html('Completed');
                    $('#preview').html("<a href='../"+subdomain+"/"+res.dlink+"' target='_blank' style='color: #0056b3;'>Download</a>");
                }
                else if(res.type == 'Duplicates'){  
                    $('#preview').html(res.msg+"<br /><input type='button' class='btn btn-primary progress-status' data-file='"+res.filename+"' data-templateid='"+res.template_id+"' data-pdfpage='"+res.pdf_page+"' data-unids='"+res.unids+"' data-progressfile='"+res.progressfile+"' data-previewPdf='"+previewPdf+"' data-blockchainPdf='"+blockchainPdf+"' value='Proceed' id='btn_proceed'>");
                    $('#btn_upload').hide();
                    $('#progress-file').hide();
                    $('#message-file').hide();
                    $('#progress-file').html('');
                    $('#message-file').html('');
                    $('#previewPdfValue').val(previewPdf);
                    if(previewPdf == 1) {
                        $('#generate_title').html('PDF Preview:');
                    } else if(previewPdf == 0) {
                        $('#generate_title').html('Generate Live PDF:');
                    }


                    $('#blockchainPdfValue').val(blockchainPdf);
                    if(blockchainPdf == 1) {
                        $('#blockchainPdf').prop('checked', false);
                        $('#blockchain_generate_title').html('<span style="color: green;margin-left: 5px;    margin-top: 10px;">Generating Live Blockchain Documents.</span>');
                    } else if(blockchainPdf == 0) {
                        $('#blockchainPdf').prop('checked', true);
                        $('#blockchain_generate_title').html('<span style="color: red;margin-left: 5px;    margin-top: 10px;">Generating Test Blockchain Documents.</span>');
                    }


                    // $('#blockchain_pdf_local_option').val(blockchainPdf);
                    // alert(blockchainPdf);
                    // if(blockchainPdf == 1) {
                    //     $('#blockchain_pdf_option').prop('checked', false);
                    //     // $('#blockchain_pdf_option').attr('checked');
                    //     $('#blockchainToggleText').html('<span style="color: green;margin-left: 5px;margin-top: 10px;">Generating Live Blockchain Documents.</span>');
                    // } else if(blockchainPdf == 0) {
                    //     $('#blockchain_pdf_option').prop('checked', true);
                    //     // $('#blockchain_pdf_option').attr('checked');
                    //     $('#blockchainToggleText').html('<span style="color: red;margin-left: 5px;margin-top: 10px;">Generating Test Blockchain Documents.</span>');
                    // }


                    //   $('#blockchain_pdf_local_option').val(blockchainPdf);
                    //   if(blockchainPdf == 1) {
                    //     $('#blockchainToggleText').html('Generating Live Blockchain Documents.');
                    //   } else if(blockchainPdf == 0) {
                    //     $('#blockchainToggleText').html('Generating Test Blockchain Documents.');
                    //   }


                    return false;
                }
              else if(res.type == 'Over Limit'){                    
                  $('#preview').html(res.msg);
                  $('#btn_upload').hide();
                  $('#progress-file').hide();
                  $('#message-file').hide();
                  $('#progress-file').html('');
                  $('#message-file').html('');
                  completed(progress_file);
                  return false;
              }
        			else if(res.type == 'Empty Extractor'){                    
                  $('#preview').html(res.msg);
                  $('#btn_upload').hide();
                  $('#progress-file').hide();
                  $('#message-file').hide();
                  $('#progress-file').html('');
                  $('#message-file').html('');
                  completed(progress_file);
                  return false;
              }
              else{
                alert('file not uploaded');
              }
          },
          complete: function (data) {
                $('#form_process')[0].reset(); // this will reset the form fields
                $('#previewPdfValue').val(previewPdf);
                if(previewPdf == 1) {
                    $('#previewPdf').prop('checked', true);
                    $('#generate_title').html('PDF Preview:');
                } else if(previewPdf == 0) {
                    $('#previewPdf').prop('checked', false);
                    $('#generate_title').html('Generate Live PDF:');
                }


                $('#blockchainPdfValue').val(blockchainPdf);
                if(blockchainPdf == 1) {
                    $('#blockchainPdf').prop('checked', false);
                    $('#blockchain_generate_title').html('<span style="color: green;margin-left: 5px;    margin-top: 10px;">Generating Live Blockchain Documents.</span>');
                } else if(blockchainPdf == 0) {
                    $('#blockchainPdf').prop('checked', true);
                    $('#blockchain_generate_title').html('<span style="color: red;margin-left: 5px;    margin-top: 10px;">Generating Test Blockchain Documents.</span>');
                }


                // $('#blockchain_pdf_local_option').val(blockchainPdf);
                // if(blockchainPdf == 1) {
                //     $('#blockchain_pdf_local_option').prop('checked', true);
                //     $('#blockchainToggleText').html('Generating Live Blockchain Documents.');
                // } else if(blockchainPdf == 0) {
                //     $('#blockchain_pdf_local_option').prop('checked', true);
                //     $('#blockchainToggleText').html('Generating Test Blockchain Documents.');
                // }
            


              // $('#generate_title').html('PDF Preview:');
          }
      });
      // Refresh the progress bar every 1 second.
      timer = window.setInterval(function(){refreshProgress(progress_file);}, 1000); 
  });


  // preview and generate
  $('#previewPdf').on('change',function(){
    var value = $('#previewPdfValue').val();

    $('#progress-file').hide();
    $('#message-file').hide();
    $('#single-file').hide();
    $('#single-file').html('');
    $('#preview').hide();
    $('#btn_upload').show();

  });

    
  $(document.body).on('click', "#btn_proceed", function(){    
      var template_id=$(this).data('templateid');
      var pdf_page=$(this).data('pdfpage');      
      var file=$(this).data('file'); 
      var unids=$(this).data('unids'); 
      var progress_file=$(this).data('progressfile');
      var previewPdf=$(this).data('previewpdf');
      var blockchainPdf=$(this).data('blockchainpdf');
      $('#progress-file').show();
      $('#message-file').show();
      $('#single-file').show();
      $('#preview').show();
      $.ajax({
          url: '<?=route('excel2pdf.processpdfagain')?>',
          type: 'post',
          data: {'_token':'<?= csrf_token()?>',file:file, template_id:template_id, pdf_page:pdf_page, unids:unids, progressfile:progress_file,previewPdf:previewPdf,blockchainPdf:blockchainPdf},
          beforeSend : function() {
              $('#preview').html('Processing...');    
          },
          success: function(response){                
              var res = eval('('+response+')');                
              if(res.type == 'Success'){                    
                  $('#preview').fadeIn().html('Completed');
                  $('#preview').html("<a href='../"+subdomain+"/"+res.dlink+"' target='_blank' style='color: #0056b3;' >Download</a>");
              }
              else{
                alert('Error');
              }
          }
      }); 
      // Refresh the progress bar every 1 second.
       
  });    

  
  $('#btn_folder').click(function(){ 
      var template_id=$('#template_id').val();
      var pdf_page=$('#pdf_page').val(); 
      var folder_name=$('#folder-process').val(); 
      var progress_file=Math.floor(Math.random() * Math.floor(Math.random() * Date.now()))+".txt";
      var fd = new FormData(); 
      var totalfiles = document.getElementById('folderprocess').files.length; 
      if(folder_name == ''){ alert("Please select folder."); return false;}
      if(folder_name == 'Auto Create'){
          if(totalfiles == 0){ alert("Please select PDF."); return false;}
          for (var index = 0; index < totalfiles; index++) {
            fd.append("files[]", document.getElementById('folderprocess').files[index]);
          }
      }
      $.ajax({
          url: '<?=route('excel2pdf.createtextfile')?>',
          type:"POST",
          data:{'_token':'<?= csrf_token()?>',"file":progress_file,"status":"start"},
          success:function(data){}
      });              
      wait(1000);  //1 second in milliseconds
      $('#progress-folder').show();
      fd.append('template_id',template_id);
      fd.append('pdf_page',pdf_page);
      fd.append('folder_name',folder_name);
      fd.append('progress_file',progress_file);
      fd.append('_token','<?= csrf_token()?>');
      $.ajax({
          url: '<?=route('excel2pdf.processpdf')?>',
          type: 'post',
          data: fd,
          contentType: false,
          processData: false,
          beforeSend : function() {
              $('#btn_folder').hide();
              $('#preview2').html('Processing...');
          },      
          xhr: function() {
              var xhr = $.ajaxSettings.xhr();
              xhr.upload.onprogress = function(e) {
                  console.log(Math.floor(e.loaded / e.total *100) + '%');
              };
              return xhr;
          },
          success: function(response){
              var res = eval('('+response+')');                
              if(res.type == 'Success'){                    
                  $('#preview2').fadeIn().html('Completed');
                  $('#preview2').html("<a href='../"+subdomain+"/"+res.dlink+"' target='_blank' style='color: #0056b3;' >Download</a>");
              }
              else if(res.type == 'Duplicates'){                    
                  $('#preview2').html(res.msg+"<br /><input type='button' class='btn btn-primary' data-folder='"+res.folder+"' data-file='"+res.filename+"' data-templateid='"+res.template_id+"' data-pdfpage='"+res.pdf_page+"' data-unids='"+res.unids+"' data-progressfile='"+res.progressfile+"' value='Proceed' id='folder_btn_proceed'>");
                  $('#btn_folder').hide();
                  $('#progress-folder').hide();
                  $('#message-folder').hide();
                  $('#progress-folder').html('');
                  $('#message-folder').html('');
              }
              else if(res.type == 'Over Limit'){                    
                  $('#preview').html(res.msg);
                  $('#btn_folder').hide();
                  $('#progress-folder').hide();
                  $('#message-folder').hide();
                  $('#progress-folder').html('');
                  $('#message-folder').html('');
                  completed(progress_file);
                  return false;
              } 
              else{
                alert('Error');
              }
          },
          complete: function (data) {
              $('#folder_process')[0].reset(); // this will reset the form fields
          }
      });   
      // Refresh the progress bar every 1 second.
      timer = window.setInterval(function(){refreshProgressMulti(progress_file);}, 1000);
  });     

  $(document.body).on('click', "#folder_btn_proceed", function(){    
      var template_id=$(this).data('templateid');
      var pdf_page=$(this).data('pdfpage');        
      var folder=$(this).data('folder'); 
      var file=$(this).data('file'); 
      var unids=$(this).data('unids'); 
      var progressfile=$(this).data('progressfile'); 
      $('#progress-folder').show();
      $('#message-folder').show();
      $.ajax({
          url: '<?=route('excel2pdf.processpdf')?>',
          type: 'post',
          data: {'_token':'<?= csrf_token()?>',folder:folder, file:file, template_id:template_id, pdf_page:pdf_page, unids:unids, progressfile:progressfile},
          beforeSend : function() {
              $('#preview2').html('Processing...');
          }, 
          success: function(response){
              var res = eval('('+response+')');                
              if(res.type == 'Success'){                    
                  $('#preview2').fadeIn().html('Completed');
                  $('#preview2').html("<a href='../"+subdomain+"/"+res.dlink+"' target='_blank' style='color: #0056b3;' >Download</a>");
              }
              else{
                alert('Error');
              }
          }
      }); 
      // Refresh the progress bar every 1 second.
      
  }); 
  
  $('#process_close').click(function(){
      
  });  
  
  $(document.body).on('change', "#folder-process", function(){    
      var name = $(this).val();
      if(name == "Auto Create"){$("#folderprocess").show();}else{$("#folderprocess").hide();}
  });  

	//assign bg
	oTable.on('click', '#assignBG', function (e) {       
		id = $(this).data('id');
		pbg = $(this).data('pbg'); //print bg id
		pbgs = $(this).data('pbgs'); //print bg status
    vbg = $(this).data('vbg'); //verification bg id
		vbgs = $(this).data('vbgs'); //verification bg status
		template_name = "Assign Background &raquo; "+$(this).data('title');
        $("#bg-title").html(template_name);   
        $("#print_bg_id").val(pbg);
        if(pbgs=='Yes'){
            document.getElementById("print_bg_status").checked = true;
        }else{
            document.getElementById("print_bg_status").checked = false;
        }
        $("#verification_bg_id").val(vbg);
        if(vbgs=='Yes'){
            document.getElementById("verification_bg_status").checked = true;
        }else{
            document.getElementById("verification_bg_status").checked = false;
        }
        $("#record_id").val(id);
        $('#modalBG').modal({backdrop: 'static', keyboard: false, show: true});
        if(pbg != "0"){
            file = $('#print_bg_id').find(':selected').data('file');
            $('#image_print')[0].src = "<?php  echo $bg_file_directory; ?>"+file;
        }else{ $('#image_print')[0].src = ""; 
        }
        if(vbg != "0"){
            file = $('#verification_bg_id').find(':selected').data('file');
            $('#image_verification')[0].src = "<?php  echo $bg_file_directory; ?>"+file;
        }else{ $('#image_verification')[0].src = ""; 
        }
	});	

  $('#print_bg_id').change(function(){
      file = $(this).find(':selected').data('file');
      if(file=="0"){
          $('#image_print')[0].src = "";
      }else{
          $('#image_print')[0].src = "<?php  echo $bg_file_directory; ?>"+file;
      }
  });

  $('#verification_bg_id').change(function(){
      file = $(this).find(':selected').data('file');
      if(file=="0"){
          $('#image_verification')[0].src = "";
      }else{
          $('#image_verification')[0].src = "<?php  echo $bg_file_directory; ?>"+file;
      }
      
  });  
    
  $(document.body).on('click', "#btn_bg", function(){ 
      var record_id = $("#record_id").val();
      var pbg_id = $("#print_bg_id").val();
      var vbg_id = $("#verification_bg_id").val();
      if(document.getElementById("print_bg_status").checked == true && document.getElementById("print_bg_id").value == "0"){
          alert("Please select print background.");
          return false;
      }
      if(document.getElementById("verification_bg_status").checked == true && document.getElementById("verification_bg_id").value == "0"){
          alert("Please select verification background.");
          return false;
      }
      if(document.getElementById("print_bg_status").checked == true){ pbg_status="Yes"; }else{ pbg_status="No"; }
      if(document.getElementById("verification_bg_status").checked == true){ vbg_status="Yes"; }else{ vbg_status="No"; }
      $.ajax({
          url: "<?=route('excel2pdf.templatebgupdate')?>",
          type: "POST",
          data: {'_token':'<?= csrf_token()?>',id:record_id, pbg_id:pbg_id, pbg_status:pbg_status, vbg_id:vbg_id, vbg_status:vbg_status},
          success: function(Result) { 
              var data = JSON.parse(Result);
              if(data.type=='success') {
                  $('#modalBG').modal('hide'); 
                  alert('Succefully saved');
                  oTable.ajax.reload();
              }  
              else {
                  alert('Error');
              }            
          },
          error: function(e) {
              console.error(e);
          }
          
      });   
          return false;
  });
    
   // Handle click on checkbox
  $('#example tbody').on('click', 'input[type="checkbox"]', function(e){
    id = $(this).val();        
    if(this.checked){
        publish=1;
        mode="active";
        title_str="activate"
        //alert('active '+id+' '+publish);            
    } else {
        publish=0;
        mode="inactive";
        title_str="inactivate"
        //alert('inactive '+id+' '+publish);            
    }
    var token="<?php echo e(csrf_token()); ?>";
    bootbox.confirm({
        message : "Do you want to "+title_str+" a template?",
        size: 'small',
        buttons : {
            confirm: {
                label: "Yes",
                className: 'btn-success'
            },
            cancel : {
                label: "No",
                className: 'btn-danger'
            }
        },
        callback: 
            function(result) {
                if(result) {
                    $.post('<?=route('excel2pdf.ActiveInactiveTemplate')?>',{'_token':token, 'id':id, 'publish':publish, 'mode':mode},function(Result){
                        var data = JSON.parse(Result);
                        if(data.rstatus=='Success') {
                            if(data.mode=='activated') {
                                toastr["success"](data.message); 
                            }else{
                                toastr["error"](data.message); 
                            }
                            oTable.ajax.reload();
                        }
                        else {                    
                            toastr["error"]('Record not found.');
                        }                 
                    });
                }                    
            }
    });

    return false;            
  });

  $(function () {
      $("#bccheck").click(function () {
          if ($(this).is(":checked")) {
              $("#bc_flag").val(1);
              $(".bc_input").show();
          } else {
              $("#bc_flag").val(0);
              $(".bc_input").hide();
          }
      });
  });    
</script>	
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/excel2pdf/templateMaster/index.blade.php ENDPATH**/ ?>