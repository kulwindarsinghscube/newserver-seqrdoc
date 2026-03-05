<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
require('login_session.php');
require('connection.php'); 
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<meta http-equiv='cache-control' content='no-cache'> 
<meta http-equiv='expires' content='0'> 
<meta http-equiv='pragma' content='no-cache'> 
<title>Secure Docs::Templates</title>
<?php require('included_files.php'); ?>
</head>
<body>
<?php require('side_nav.php'); ?>
<div class="container-fluid"> 
    <div class="row">
<?php require('logo.php'); ?>
        <div class="col-md-4 text-center py-2"><h5>Templates</h5></div>    
        <div class="col-md-4 text-right py-2">    
<?php if(checkPermissions('add-template')){ ?>         
            <a href="index.php?action=add" class="btn btn-info btn-md pull-right">New Template</a>
<?php } ?>            
        </div>  
        <?php include('divider.php'); ?>
<?php 
if(1==1){
?>        
        <div class="col-md-12 py-2">
            <table id="example" class="table table-striped table-bordered table-hover display card0" width="100%">
                <thead>
                    <tr class="bg-white">
                        <th width="5%">Sr.</th>
                        <th>Name</th>
                        <th width="10%">Page</th>
                        <th width="5%">ID</th>
                        <th width="10%">Date</th>                                
                        <th width="30%">Action</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div> 
<?php 
} 
else{
    echo '<div class="col-md-12 py-2"><div class="alert alert-box">'.alert_msg[0].'</div></div>';
}
?>          
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
        <!-- Form -->
        <form id="form_process" method='post' action='' enctype="multipart/form-data">
          Select file : <input type='file' name='file' id='file_process' accept="application/pdf" class='form-control'><br />
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
          Folder : <!--<input type='text' name='folder' id='folder-process' class='form-control' value="multi_pages">-->
            <select id="folder-process" name="folder" class="form-control">
            <option value = "">Select</option>
            <option value = "multi_pages">Default</option>
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

<div class="modal fade" id="modalBG" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"  aria-hidden="true">
  <div class="modal-dialog" role="document" style="max-width: 530px;">
    <div class="modal-content">
          
          <div class="modal-header text-center">
            <h6 class="modal-title w-100" id="bg-title">Assign Background</h6> 
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body mx-3">
            <div class="form-group mb-2">
              <label><strong>Print Background</strong></label>              
              <select id="print_bg_id" name="print_bg_id" class="form-control">
              <option value="0">Select BG</option>
                <?php
                $stmt = $conn->prepare("SELECT id,name FROM bg_data where publish=1 order by name asc");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
              </select>        
                <input type="checkbox" id="print_bg_status" name="print_bg_status" style="width:15px;height:15px;"> Use as background
            </div>
            <div class="form-group">
              <label><strong>Verification Background</strong></label>
              <select id="verification_bg_id" name="print_bg_id" class="form-control">
              <option value="0">Select BG</option>
                <?php
                $stmt = $conn->prepare("SELECT id,name FROM bg_data where publish=1 order by name asc");
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
              </select>      
                <input type="checkbox" id="verification_bg_status" name="verification_bg_status" style="width:15px;height:15px;"> Use as background    
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-center">
            <button type="submit" class="btn btn-success" id="btn_bg">Save</button>
            <input type="hidden" id="record_id" name="record_id" class="form-control">
          </div>
        
    </div>
  </div>
</div>

<script>
var timer;
$(document).ready(function(){

	var oTable =  $('#example').DataTable({        
		/*"bStateSave": true,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem( 'DataTable', JSON.stringify(oData) );
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse( localStorage.getItem('DataTable') );
        }, */ 		
        "pageLength": 10,
		"processing": true,
		"serverSide": true,
		"autoWidth": false,
		"language": {"infoFiltered": "","info": "_START_ to _END_ of _TOTAL_ entries (Page _PAGE_ of _PAGES_)","sLengthMenu":"_MENU_ entries"},
		"aaSorting": [],
        "ajax": {
            "url": "getRecords.php",
            //"type": "POST", /* Uncomment the syntax while uploading on server */
        }, 
		"order": [[ 4, 'desc' ]],		
		"aoColumnDefs": [
			//{'bSort' : false,   'aTargets' : [ 0 ] },		
            //{"targets": [8],"visible": false},
			//{"targets": [4],"orderData": [3,8] },
            {"targets": [6,7,8,9],"orderable": false,"visible": false},
            {"targets": [0,5],"orderable": false},
			{"sClass": "text-center", "aTargets": [5]},
			{
				"mRender": function (oObj) {
					//console.log(oObj);
					var buttons = "";		
                    <?php if(checkPermissions('edit-template')){ ?>
					buttons += '<span id="editData" class="btn btn-sm btn-success" data-id = "'+oObj.id+'" data-temp = "'+oObj.template_name+'" data-page = "'+oObj.pdf_page+'" data-file = "'+oObj.file_name+'" data-toggle="tooltip">Edit</span>'; 
                    <?php } ?>
                    <?php if(checkPermissions('duplicate-template')){ ?>
                    buttons += '&nbsp;<span id="dupData" class="btn btn-sm btn-primary duplicateData" data-id = "'+oObj.id+'" data-toggle="tooltip" >Duplicate</span>';
                    <?php } ?>
                    <?php if(checkPermissions('generate-pdfs')){ ?>
                    buttons += '&nbsp;<span id="processData" class="btn btn-sm btn-warning text-white" data-id = "'+oObj.id+'" data-temp = "'+oObj.template_name+'" data-pdf_page = "'+oObj.pdf_page+'" data-template_name = "'+oObj.template_name+'" data-toggle="tooltip">Generate PDFs</span>'; 
                    <?php } ?>
                    <?php if(checkPermissions('assign-bg')){ ?>
                    buttons += '&nbsp;<span id="assignBG" class="btn btn-sm btn-info text-white" data-id = "'+oObj.id+'" data-temp = "'+oObj.template_name+'" data-pdf_page = "'+oObj.pdf_page+'" data-pbg = "'+oObj.print_bg_file+'" data-pbgs = "'+oObj.print_bg_status+'" data-vbg = "'+oObj.verification_bg_file+'" data-vbgs = "'+oObj.verification_bg_status+'" data-toggle="tooltip">Assign BG</span>'; 
                    <?php } ?>
                    //buttons += '<span id="delData" class="btn btn-sm btn-danger deleteData" data-id = "'+oObj.id+'" data-toggle="tooltip" title="Delete">X</span>';
					return buttons;
				},
				"aTargets": [5]
			},
		],	
	   	
	} );	
	
	//edit
	oTable.on('click', '#editData', function (e) {       
		id = $(this).data('id');
		temp = $(this).data('temp');
		pdf_page = $(this).data('page');
		file = $(this).data('file');
        
		var pageUrl = 'index.php?action=edit&id='+id+'&temp='+temp+'&file='+file+'&pdf_page='+pdf_page;
		//window.location.replace(pageUrl);
        window.location.href = pageUrl;
        //window.open(pageUrl, '_blank');
	});		
	//duplicate
	oTable.on('click', '.duplicateData', function (e) {
       	$id = $(this).data('id');
		if (confirm("Do you want to create duplicate template?")) {
			$.post('duplicate.php',{'id':$id},function(Result){			
				var data = JSON.parse(Result);
                if(data.rstatus=='invalid') {
                    alert('Invalid File');
                }                 
                else if(data.rstatus=='Success') {
                    alert(data.template_name+" created.");
                    location.reload();
                }
                else {
                    alert('Record not found.');
                }                 
			});
		}
		return false;		

	});	
    //delete
	oTable.on('click', '.deleteData', function (e) {
       	$id = $(this).data('id');
		if (confirm("Are you sure?")) {
			$.post('delete.php',{'view':'1','id':$id},function(data){			
				alert(data);
				location.reload();						
			});
		}
		return false;		

	});		    
    
	$("#export").click(function(){		
			$href='ExportToExcel.php';
			window.open($href, '_blank');		
	})
    
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
        $("#process-title").html(template_name);        
        $("#template_id").val(id);
        $("#pdf_page").val(pdf_page);        
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
        src='processed_pdfs/'+filename        
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
                                time_msg= "<div class='py-1'></div><table class='table table-bordered'><tbody><tr><td width='25%'>Pages Processed</td><td>"+pages_processed+"</td></tr><tr><td>Start Time</td><td>"+beginning_time+"</td></tr><tr><td>End Time</td><td>"+ending_time+"</td></tr><tr><td>Execution Time</td><td>"+hms_time+"</td></tr><tr><td>Time Per Page</td><td>"+page_time+"</td></tr></tbody></table>"; 
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
        src='processed_pdfs/'+filename        
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
        //$("#message-file").html("Completed");
        $.ajax({
            url: "create_textfile.php?file="+progress_file+"&status=end",
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
        var progress_file=Math.floor(Math.random() * Math.floor(Math.random() * Date.now()))+".txt"; 
        var files = $('#file_process')[0].files[0];
        if(typeof files === 'undefined'){ alert("Please select PDF."); return false;}
        $.ajax({
            url: "create_textfile.php?file="+progress_file+"&status=start",
            success:function(data){}
        });         
        wait(1000);  //1 second in milliseconds
        $('#progress-file').show();
        var fd = new FormData();
        fd.append('file',files);
        fd.append('template_id',template_id);
        fd.append('pdf_page',pdf_page);
        fd.append('progress_file',progress_file);
        
        $.ajax({
            url: 'pdfprocess.php',
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
                    $('#preview').html("<a href='"+res.dlink+"' target='_blank' style='color: #0056b3;'>Download</a>");
                }
                else if(res.type == 'Duplicates'){                    
                    $('#preview').html(res.msg+"<br /><input type='button' class='btn btn-primary progress-status' data-file='"+res.filename+"' data-templateid='"+res.template_id+"' data-pdfpage='"+res.pdf_page+"' data-unids='"+res.unids+"' data-progressfile='"+res.progressfile+"' value='Proceed' id='btn_proceed'>");
                    $('#btn_upload').hide();
                    $('#progress-file').hide();
                    $('#message-file').hide();
                    $('#progress-file').html('');
                    $('#message-file').html('');
                    return false;
                }
                else{
                  alert('file not uploaded');
                }
            },
            complete: function (data) {
                $('#form_process')[0].reset(); // this will reset the form fields
            }
        });
        // Refresh the progress bar every 1 second.
        timer = window.setInterval(function(){refreshProgress(progress_file);}, 1000); 
    });
    
    $(document.body).on('click', "#btn_proceed", function(){    
        var template_id=$(this).data('templateid');
        var pdf_page=$(this).data('pdfpage');      
        var file=$(this).data('file'); 
        var unids=$(this).data('unids'); 
        var progress_file=$(this).data('progressfile');              
        $('#progress-file').show();
        $('#message-file').show();
        $.ajax({
            url: 'pdfprocess_again.php',
            type: 'post',
            data: {file:file, template_id:template_id, pdf_page:pdf_page, unids:unids, progressfile:progress_file},
            beforeSend : function() {
                $('#preview').html('Processing...');    
            },
            success: function(response){                
                var res = eval('('+response+')');                
                if(res.type == 'Success'){                    
                    $('#preview').fadeIn().html('Completed');
                    $('#preview').html("<a href='"+res.dlink+"' target='_blank' style='color: #0056b3;'>Download</a>");
                }
                else{
                  alert('Error');
                }
            }
        }); 
        // Refresh the progress bar every 1 second.
        //timer = window.setInterval(function(){refreshProgress(progress_file);}, 1000); 
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
            url: "create_textfile.php?file="+progress_file+"&status=start",
            success:function(data){}
        });         
        wait(1000);  //1 second in milliseconds
        $('#progress-folder').show();
        fd.append('template_id',template_id);
        fd.append('pdf_page',pdf_page);
        fd.append('folder_name',folder_name);
        fd.append('progress_file',progress_file);
        $.ajax({
            url: 'pdfprocess.php',
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
                    $('#preview2').html("<a href='"+res.dlink+"' target='_blank' style='color: #0056b3;'>Download</a>");
                }
                else if(res.type == 'Duplicates'){                    
                    $('#preview2').html(res.msg+"<br /><input type='button' class='btn btn-primary' data-folder='"+res.folder+"' data-file='"+res.filename+"' data-templateid='"+res.template_id+"' data-pdfpage='"+res.pdf_page+"' data-unids='"+res.unids+"' data-progressfile='"+res.progressfile+"' value='Proceed' id='folder_btn_proceed'>");
                    $('#btn_folder').hide();
                    $('#progress-folder').hide();
                    $('#message-folder').hide();
                    $('#progress-folder').html('');
                    $('#message-folder').html('');
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
            url: 'pdfprocess_again.php',
            type: 'post',
            data: {folder:folder, file:file, template_id:template_id, pdf_page:pdf_page, unids:unids, progressfile:progressfile},
            beforeSend : function() {
                $('#preview2').html('Processing...');
            }, 
            success: function(response){
                var res = eval('('+response+')');                
                if(res.type == 'Success'){                    
                    $('#preview2').fadeIn().html('Completed');
                    $('#preview2').html("<a href='"+res.dlink+"' target='_blank' style='color: #0056b3;'>Download</a>");
                }
                else{
                  alert('Error');
                }
            }
        }); 
        // Refresh the progress bar every 1 second.
        //timer = window.setInterval(function(){refreshProgressMulti(progressfile);}, 1000); 
    }); 
    
    $('#process_close').click(function(){
        //$('#preview').html('');
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
		template_name = "Assign Background &raquo; "+$(this).data('temp');
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
            url: "setBG.php",
            type: "POST",
            data: {id:record_id, pbg_id:pbg_id, pbg_status:pbg_status, vbg_id:vbg_id, vbg_status:vbg_status},
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
});
</script>
</body>
</html>