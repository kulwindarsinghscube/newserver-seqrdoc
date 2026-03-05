<!DOCTYPE html>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv='cache-control' content='no-cache'> 
    <meta http-equiv='expires' content='0'> 
    <meta http-equiv='pragma' content='no-cache'>
    <title>Secure Docs::Create Template</title>     
	<link rel="stylesheet" href="{{asset('pdf2pdf/bootstrap.css')}}">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.css">
	<link rel="stylesheet" href="{{asset('pdf2pdf/pdf_styles.css')}}">
	<link rel="stylesheet" href="{{asset('pdf2pdf/pdfannotate.css')}}">
</head>
<body>

<div id="show-msg" style="position: relative;width: 900px;height: 900px;margin-top: 100px; padding-left: 617px;color: white; display:none;"></div>
<div class="toolbar" style="text-align: center;">
        
        <input type="hidden" id="action_temp" value="<?php echo $template_data['action'];?>">	
		<?php 
        if ($template_data['action'] != "edit"){ 
            $style="visible";
        }else{
            $style="invisible";
            ?>

            
            <input type="hidden" id="edit_id" value="<?php echo $template_data['id'];?>">
            <input type="hidden" id="edit_temp" value="<?php echo $template_data['template_name'];?>">
            <input type="hidden" id="edit_pdf_page" value="<?php echo $template_data['pdf_page'];?>">
            <input type="hidden" id="edit_file" value="<?php echo $template_data['file_name'];?>">

            <?php 
        }
        ?>          
        <div class="tool"><span><img src="../pdf2pdf/images/logo.png" class="logo img-thumbnail" style="margin-top: -10px !important;margin-left: -190px !important;border-radius:0;heigh:auto;width:50%;"></span></div>
        <div class="tool"><span id="msg" style="color:white;font-size: 0.9rem;">&nbsp;</span></div>
        <div class="tool <?php echo $style; ?>">
			<span>PDF Upload</span>
		</div>
		<div class="tool <?php echo $style; ?>">
			<button class="tool-button"><i class="fa fa-file" title="Upload PDF" onclick="$('#pdf-upload').click();"></i></button>
			<form id="form" action="<?=route('pdf2pdf.createtemplate')?>" method="post" enctype="multipart/form-data">
				<input id="pdf-upload" type="file" name="pdf_upload" style="display: none;">
			</form>
		</div>
        
		<div class="tool">
			<button class="tool-button active"><i class="fa fa-hand-paper-o" title="Free Hand" onclick="enableSelector(event)"></i></button>
		</div>
		<div class="tool" title="Extractor">
			<button class="tool-button" style="color: #e75480 !important;"><i class="fa fa-minus" onclick="enableRectangle(event)"></i></button>
		</div>
        <div class="tool" title="Placer">
			<button class="tool-button text-primary"><i class="fa fa-plus" onclick="enablePlaceRectangle(event)"></i></button>
		</div>        
        <div class="tool dropdown">
			<button class="tool-button text-success"><i class="fa fa-snapchat-ghost"></i></button>
            <div class="dropdown-content">
                <a href="javascript:void(0);"><strong>Select Ghost Placer</strong></a> 
                <select id="print_words" name="print_words" class="form-control">
                <?php for($w=5;$w<=15;$w++){ ?>
                <option value = "<?php echo $w; ?>"><?php echo $w; ?> characters</option>
                <?php } ?>
                </select>    <div class="py-1"></div>             
                <a href="javascript:void(0);" onclick="enableGhostRectangle(event,10)">Font Size 10</a>
                <a href="javascript:void(0);" onclick="enableGhostRectangle(event,11)">Font Size 11</a>
                <a href="javascript:void(0);" onclick="enableGhostRectangle(event,12)">Font Size 12</a>
                <a href="javascript:void(0);" onclick="enableGhostRectangle(event,13)">Font Size 13</a>
                <a href="javascript:void(0);" onclick="enableGhostRectangle(event,14)">Font Size 14</a>
                <a href="javascript:void(0);" onclick="enableGhostRectangle(event,15)">Font Size 15</a>
            </div>            
		</div>
        <div class="tool clone-dropdown">
			<button class="tool-button text-primary"><i class="fa fa-copy"></i></button>
            <div class="clone-dropdown-content">
                <a href="javascript:void(0);"><strong>Create Clone</strong></a> 
                <div class="py-1"></div>
                <select id="clone_ep" class="form-control" title="Clone">
                <option value = "">Select Clone Type</option>
                <option value = "extractor">Extractor</option>
                <option value = "placer">Placer</option>
                </select>
                <div class="py-2"></div> 
                <button class="btn btn-primary btn-sm" onclick="createClone()">Clone Selected</button>    
            </div>
		</div>
		<div class="tool">
			<button class="btn btn-danger btn-sm" onclick="deleteSelectedObject(event)"><i class="fa fa-trash"></i></button>
		</div>
		<div class="tool <?php echo $style; ?>">
			<button class="btn btn-danger btn-sm" onclick="clearPage()">Clear Page</button>
		</div>
		<div class="tool">
			<button class="btn btn-light btn-sm" onclick="savePDFCoord()"><i class="fa fa-save"></i> Save</button>
		</div>
        <div class="tool">
			<input type="text" class="form-control" name="template-name" id="template-name" aria-describedby="template_name" placeholder="Template Name">
            <input id="template_id" type="hidden" name="template_id" value="0">
            <input id="pdf_file" type="hidden" name="pdf_file" value="0">
		</div>
        <div class="tool">
            <select id="pdf_page" name="pdf_page" class="form-control">
            <option value = "">Page</option>
            <option value = "Single">Single</option>
            <option value = "Multi">Multi</option>
            </select>            
        </div>

        <div class="tool">
            
            <button type="button" class="btn btn-success btn-sm" id="process" >
            <i class="fa fa-spinner"></i> Process</button>
        </div>
        
        <div class="tool">
            <a href="<?=route('pdf2pdf.templatelist')?>" class="btn btn-info btn-sm">Back</a> 
        </div>
		<div class="tool">
			<button class="btn btn-info btn-sm" onclick="showPdfData()">{}</button>		
		</div>
	
</div>
<div class="container-fluid" style="margin-top: 35px;min-height:4000px;">
<div class="row">    
    <div class="col-lg-9"><div id="pdf-container"></div></div>
    <div class="col-lg-3 mt-3 p-5"> 
        <div class="overflow-auto" style="position: fixed; left:75%; right: 35px;z-index:9999;overflow-x:hidden;height:85%;">
        <input type="text" id="unique-id" class="form-control" style="display:none;" />
        <input type="text" id="image_path" class="form-control" style="display:none;" />
        <input type="text" id="coord-list" class="form-control" onkeyup="changeRectBoxCoords()" placeholder="Coords" autocomplete="off" readonly="readonly" style="display:none;" />
        <div class="input-group">
        <input type="text" id="RectBoxId" class="form-control input-sm" placeholder="ID" autocomplete="off" readonly="readonly" title="ID" />
        <input type="text" id="nature" class="form-control input-sm" placeholder="Nature" autocomplete="off" readonly="readonly" style="width:10%;" title="Nature" />        
        </div>
        <div class="py-1"></div> 
        <input type="text" id="RectBox" class="form-control" onkeyup="changeRectBoxName()" placeholder="Name" autocomplete="off" title="Box Name" />        
        <div class="py-1"></div>
        <div class="input-group">
            <input type="text" id="rect-width" class="form-control input-sm" onkeyup="changeRectWidth()" placeholder="Width" autocomplete="off" title="Width"  />
            <input type="text" id="rect-height" class="form-control input-sm" onkeyup="changeRectHeight()" placeholder="Height" autocomplete="off" title="Height"  /> 
        </div>    
        <div class="py-1"></div>
        <!--Left,Top,Width,Height-->
        
        <!--<div class="row" style="margin-bottom: -10px !important;">
            <div class="col-lg-5">
                <button class="btn btn-primary btn-sm" onclick="createClone()">Clone Selected</button> &nbsp;
            </div>
            <div class="col-lg-7">    
                <select id="clone_ep" class="form-control" title="Clone">
                <option value = "">Select</option>
                <option value = "extractor">Extractor</option>
                <option value = "placer">Placer</option>
                </select>
            </div>
        </div>-->
        <div id="placer_elements" style="display:none;">
        <select id="source_selector" class="form-control" onchange="changeSourceName()" title="Source"><option value = "">Select a Source</option></select><div class="py-1"></div>
        <div class="input-group">
        <select id="placer_type" class="form-control" onchange="changePlacerType()" title="Placer Type">
            <option value = "">Select Placer Type</option>
            <option value = "QR Default">QR Default</option>
            <option value = "QR Dynamic">QR Dynamic</option>
            <option value = "QR Invisible Plain Text">QR Invisible Plain Text-on each page</option>
            <option value = "QR Plain Text">QR Plain Text-on each page</option>            
            <!--<option value = "Barcode">Barcode</option>-->
            <option value = "Micro Line">Micro Line</option>
            <option value = "Invisible">Invisible Text</option>
            <option value = "Invisible Image">Invisible Image</option>
            <option value = "Plain Text">Plain Text</option>
            <option value = "Static Text">Static Text</option>
            <option value = "Common Static Text">Static Text-on each page</option>
            <option value = "Image">Image</option>
            <option value = "Watermark Text">Watermark Text</option>
            <option value = "Watermark Multi Lines">Watermark Multi Lines Background</option>
            <option value = "Ghost Image" disabled>Ghost Image</option>
        </select>
        <select id="qr_position" class="form-control" onchange="changeQrPosition()" title="Position">
            <option value = "">Select Position</option>
            <option value = "On First Page">On First Page</option>
            <option value = "On Each Page">On Each Page</option>
        </select>
        <a href="javascript:void(0);" class="btn btn-sm btn-primary" id="select_image" style="border-radius:0;padding-top: 7px;">Select</a>
        <a href="javascript:void(0);" class="btn btn-sm btn-warning" id="addData" style="border-radius:0;padding-top: 7px;">Add</a>
        </div>
        <button class="btn btn-primary btn-small" id="source-link">Source</button>
        <textarea id="qr_details" class="form-control" placeholder="Add Combination of Sources {^source_name1^}   {^source_name2^}" style="display:none;clear:both;margin-top:8px;height: 70px;" onkeyup="changeQrDetails()" title="Combination of Sources"></textarea>
        <div class="py-1"></div>
        <div class="input-group text-white">
            <select id="placer_font" class="form-control" onchange="changePlacerFontName()" title="Font" style="width:70%;">
            <option value = "">Select Font</option>
            <option value = "arial.ttf">Arial</option>
            <option value = "arialbd.ttf">Arial Bold</option>
            <option value = "ariali.ttf">Arial Italic</option>
            <option value = "arialbi.ttf">Arial Bold Italic</option>
            <option value = "times.ttf">Times New Roman</option>
            <option value = "timesbd.ttf">Times Bold</option>
            <option value = "timesi.ttf">Times Italic</option>
            <option value = "timesbi.ttf">Times Bold Italic</option>
            <option value = "Kruti_Dev_100.ttf">Kruti Dev 100</option>
            <option value = "Kruti_Dev_100_Bold.ttf">Kruti Dev 100 Bold</option>
            <option value = "Kruti_Dev_100_Italic.ttf">Kruti Dev 100 Italic</option>
            <option value = "Kruti_Dev_100_Bold_Italic.ttf">Kruti Dev 100 Bold Italic</option>
            </select>   
            
            <input type="checkbox" class="form-control" id="placer_font_underline" value="underline" onclick="changePlacerFontUnderline()" style="width:1%;" /> <u class="form-control" id="text_underline">U</u>
        </div>
        <div class="py-1"></div>
        <div class="input-group">
            <input type="text" id="font-div" class="form-control input-sm" placeholder="Font Size" autocomplete="off" readonly="readonly" style="width:9%;font-size:15px;" />
            <input type="text" id="placer_font_size" class="form-control input-sm" autocomplete="off" onkeyup="changePlacerFont()" style="width:9%;" />   
            <select id="placer_display" class="form-control" onchange="changePlacerDisplay()" title="Align" style="width:25%;">
            <option value = "">Select Align</option>
            <option value = "0">Left</option>
            <option value = "1">Center</option>
            <option value = "2">Right</option>
            <option value = "3">Justify</option>
            </select>            
        </div>
        <div class="py-1"></div>
        <div class="input-group">
            <input type="text" id="ghost_chars" class="form-control input-sm" placeholder="Characters" autocomplete="off" readonly="readonly" />
            <input type="text" id="ghost_words" class="form-control input-sm" placeholder="Ghost Characters" autocomplete="off" readonly="readonly" />
        </div>
        <div class="input-group">
        <select id="color_selector" class="form-control" onchange="changePlacerColor()" title="Colour" style="width:30%;"></select>
        <input type="text" id="line_height" class="form-control input-sm" onkeyup="changeLineHeight()" placeholder="Line Height" autocomplete="off" title="Line Height"  /> 
        </div>
        <div class="py-1"></div>
        <div class="input-group">
            <select id="ghost_degree_angle" class="form-control" onchange="changeGhostPlacerAngle()" title="Degree Angle" style="width:25%;">
            <option value = "0">0 Degree Rotation</option>
            <option value = "90">90 Degree Rotation</option>
            </select> 
            <input type="text" id="degree_angle" class="form-control input-sm" placeholder="Degree Angle" autocomplete="off" style="display:none;" onkeyup="changePlacerAngle()" title="Degree Angle" />            
            <select id="opacity_val" name="opacity_val" class="form-control input-sm" onchange="changePlacerOpacity()" title="Opacity">
            <option value="">Select Opacity</option>
            <?php for($o=1;$o<=9;$o++){ ?>
            <option value = "<?php echo "0.".$o; ?>"><?php echo "0.".$o; ?></option>
            <?php } ?> 
            <option value="1.0">1.0</option>
            </select>
        </div> 
        <div class="py-1"></div>
        
        </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="dataModalLabel" aria-hidden="true"  style="z-index:9999">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dataModalLabel">PDF annotation data</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<pre class="prettyprint lang-json linenums">
				</pre>
			</div>
		</div>
	</div>
</div>

<!-- Process Modal -->
<div id="uploadModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Process</h5>
        <button type="button" class="close" id="process_close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>        
      </div>
      <div class="modal-body">
        <!-- Form -->
        <form id="form_process" method='post' action='' enctype="multipart/form-data">
          Select file : <input type='file' name='file' id='file_process' class='form-control'><br>
          <input type='button' class='btn btn-info' value='Upload' id='btn_upload'>
        </form>
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
        <h5 class="modal-title" id="process-title2">Process</h5>
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
          <input type="file" name="files[]" id="folderprocess" style="display:none;" multiple /><br /><br />
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

<!-- Image Modal -->
<div class="modal fade" id="imgModal" role="dialog" style="z-index: 10000;">
    <div class="modal-dialog" style="max-width: 95%;"> 
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Images</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document" style="max-width: 530px;">
    <div class="modal-content">
        <form id="RegForm" class="form-horizontal" enctype="multipart/form-data">  
          <div class="modal-header text-center">
            <h5 class="modal-title w-100" id="modal-head">Add New Image</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body mx-3">
            <div class="form-group mb-2">
              <label>Name</label>
              <input type="text" id="name" name="name" class="form-control">
            </div>
            <div class="form-group mb-2">
              <label>Image</label>
              <input type='file' name='file' id='file' accept="image/png, image/jpeg, image/gif" class='form-control'>        
            </div>            
            <div class="form-group" style="display:none;">
              <label>Active</label>
              <select id="publish_id" name="publish_id" class="form-control">
              <!--<option value="">Select</option>--> 
              <option value="1">Yes</option>
              <!--<option value="0">No</option>-->              
              </select>          
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-center">
            <button type="submit" class="btn btn-success submit">Save</button>
            <input type="hidden" id="record_id" name="record_id" class="form-control">
            <input type="hidden" id="name_check" name="name_check" class="form-control">  
            <input type="hidden" id="filename_check" name="filename_check" class="form-control">   
          </div>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" id="sourceModal" role="dialog" style="z-index: 10000;">
    <div class="modal-dialog" style="max-width: 50%;"> 
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Source</h4>                
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                
            </div>
            <div class="modal-body">
            <div style="overflow-x:hidden;max-height:500px;">
                <small>Note: Drag the row to up or down to put the row into proper sequence. The source name will be displayed in Source Text Box when checkbox checked.</small><br />
                <ul id="sortable"></ul> 
            </div>
            </div>
            
        </div>
    </div>
</div>

<div class="modal fade" id="instructions" role="dialog" style="z-index: 10000;">
    <div class="modal-dialog" style="max-width: 70%;"> 
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Instructions</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
            <div style="overflow-x:hidden;max-height:500px;"> 
                <span>Note:</span>
                <ol style="border:1px solid #ccc;">
                <li>Pink colour box (Extractor) should be placed first.</li>
                <li>After that, place blue colour box (Placer) and select placer type QR Default or QR Dynamic.</li>
                <li>"Watermark Multi Lines Background"  placer always should be at last. Means after putting all placers.</li>
                </ol> <hr />
                Click on <img src="../pdf2pdf/images/pdf_upload.png" /> "Page Icon" to upload PDF from your local machine.<br />
                Select a PDF and click the "Open" button.<br />
                PDF will be appeared on screen.  <br />
                Enter the Template name and select a page option, Single or Multi.<br /><img src="../pdf2pdf/images/add_template.png" /> <br /><br />
                <span class="text-danger"><strong>First extract all required data.</strong></span><br />
                <strong>How to extract text?</strong><br />
                There is a pink colour minus sign <img src="../pdf2pdf/images/extractor.png" /> image. We call it extractor.<br />
                Click it. One pink colour box will be opened at top left corner.<br />
                Drag and drop it on text which need to be extracted.<br />
                Give appropriate name for extractor. (Right side panel)<br /><br />
                <span class="text-danger"><strong>First placer should be QR Default or QR Dynamic.</strong></span><br />
                <strong>How to add security features like QR, Micro Line, Ghost Image, and Invisible Text?</strong><br />
                There is a blue colour plus sign <img src="../pdf2pdf/images/placer.png" /> image. We call it placer.<br />
                Click it. One blue colour box will be opened at top left corner.<br />
                Drag and drop it on specific place where extracted data to be placed.<br /><br />
                To format the placer, use the format panel on the right. <br />
                <img src="../pdf2pdf/images/placer_options.png" /> <br /><br />
                Select a placer type to get attributes of placer.<br />
                <img src="../pdf2pdf/images/placer_attributes.png" /> <br /><br />
                Click save button <img src="../pdf2pdf/images/save.png" /> to store the template.<br /><br />
                To apply these security features on already printed documents, click the process button <img src="../pdf2pdf/images/process.png" /><br />
                Pop-up will be opened. Here you have to choose PDF which contains documents and click the Upload button. <br />
                Once process is completed. Download link will be appeared.
            </div>
            </div>
            
        </div>
    </div>
</div>

<div class="feedbackform">
    <button class="feedbackBTN" data-toggle="modal" data-target="#instructions" data-backdrop="static" data-keyboard="false"><i class="fa fa-file" aria-hidden="true"></i> Instructions</button>
</div>
 

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.0.328/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.7.22/fabric.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.debug.js"></script>
<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js"></script>
<script>
    var domainName=window.location.hostname;
    var domainArr = domainName.split('.');
    
    // global app configuration object
    var config = {
        routes: {
            createtemplate: "<?=route('pdf2pdf.createtemplate')?>",
            createtextfile: "<?=route('pdf2pdf.createtextfile')?>",
            processpdf: "<?=route('pdf2pdf.processpdf')?>",
            processpdfagain: "<?=route('pdf2pdf.processpdfagain')?>",
            imagesave: "<?=route('pdf2pdf.imagesave')?>",
            imageedit: "<?=route('pdf2pdf.imageedit')?>",
            imagelist: "<?=route('pdf2pdf.imagelist')?>"
        },
        domain_name:domainName,
        subdomain:domainArr[0],
        csrf_token:"<?= csrf_token()?>", 
    };
</script>
<script src="{{asset('pdf2pdf/arrow.fabric.js')}}"></script>
<script src="{{asset('pdf2pdf/pdfannotate.js')}}"></script>
<script src="{{asset('pdf2pdf/script.js')}}"></script>
<script type="text/javascript" src="https://rawgithub.com/mark-rolich/Event.js/master/Event.js"></script>
<script type="text/javascript" src="https://rawgithub.com/mark-rolich/Dragdrop.js/master/Dragdrop.js"></script>
<script type="text/javascript" src="{{asset('pdf2pdf/RulersGuides/RulersGuides.js')}}"></script>
<script src="{{asset('pdf2pdf/fitz_colours.js')}}"></script>
<script type="text/javascript">
var evt     = new Event(),
dragdrop    = new Dragdrop(evt),
rg          = new RulersGuides(evt, dragdrop);
</script>
<script src="{{asset('pdf2pdf/bootstrap/jquery.validate.js')}}"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</body>
</html>
