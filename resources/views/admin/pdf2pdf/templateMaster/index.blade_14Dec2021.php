@extends('admin.layout.layout')
@section('content')

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
</style>
<?php 
$domain = \Request::getHost();
$subdomain = explode('.', $domain);
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
                          Folder :
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


				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i> Template Master (PDF2PDF)
								<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('templatemangement') }}</ol>
								</h1>
							</div>
						</div>
						<div class="">
                            <ul class="nav nav-pills" id="pills-filter">
                                <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Template </a></li>
                                <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Template</a></li>						
							   @if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.templatemaker'))
							   @if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.templatemaker'))	
								<li style="float: right;">
									<span  id="addtemplate" class="btn btn-theme" style="background: rgb(0 82 204); color: #FFF;"><i class="fa fa-plus"></i> Add Template</span>	
								</li>
								@endif
								@endif
							</ul> 
							<table id="example" class="table table-hover display" cellspacing="0" width="100%">
								<thead>
									<tr>
										<th>#</th>
										<th>Template Name</th>
										<th>Pdf Page</th>
                                        <?php if($subdomain[0]=="icat"){ ?>
										<th>Lab Assigned</th>
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
@stop



@section('script')

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

    	var url="{{ URL::route('template-master.copyTemplate.copy') }}";
    	var token="{{ csrf_token() }}";
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
        <?php if($subdomain[0]=="icat"){ ?>
        "aaSorting": [[4, "desc"] ],
        <?php } else{ ?>
        "aaSorting": [[3, "desc"] ],
        <?php } ?>
        "sAjaxSource":"<?= URL::route('pdf2pdf.templatelist',['status'=>1])?>",
        "aoColumns":[
		{mData: "rownum", bSortable:false, sWidth: "5%"},
		{mData: "template_name", sWidth: "50%"},
		{mData: "pdf_page", sWidth: "10%"},
        <?php if($subdomain[0]=="icat"){ ?>
		{mData: "LabCount", bSortable: false, sWidth: "15%", sClass: "text-center"},
        <?php } ?>
		{mData: 'id',
            bSortable: false,
            sWidth: "20%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            	var edit_url = "{{route('template-master.edit',':id')}}"
            	edit_url = edit_url.replace(':id',o['id']);

            	var map_url =  "{{route('template-master.template-map.edit',':id')}}"
            	map_url = map_url.replace(':id',o['id']);
            	var buttons = '';
            	
            	buttons = '@if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.templatemakeredit')) @if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.templatemakeredit')) <span style="cursor:pointer"><i title="Edit" id="editData" data-id="'+o['id']+'" data-temp="'+o['template_name']+'" data-page="'+o['pdf_page']+'" data-file="'+o['file_name']+'" data-toggle="tooltip" class="editrow fa fa-edit fa-lg green"></i> </span> @endif @endif';

                buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.duplicatetemplate')) @if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.duplicatetemplate')) <span style="cursor:pointer" data-id="'+o['id']+'" id="dupData" class="duplicateData" ><i title="Copy Template" class="copyrow fa fa-copy fa-lg yellow"></i></span> @endif @endif';
                
                buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.processpdf')) @if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.processpdf')) <span style="cursor:pointer" id="processData" data-id="'+o['id']+'" data-temp="'+o['template_name']+'" data-pdf_page="'+o['pdf_page']+'" data-file="'+o['file_name']+'" data-template_name = "'+o['template_name']+'"><i title="Generate PDF"  class="pdfGenerate fa fa-file-pdf-o fa-lg blue"></i></span> @endif @endif';   
                
                if(o['publish']==1){ rcheck="checked"; title_str="Inactivate Template"; }else{ rcheck=""; title_str="Activate Template";}        
                buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.templatemakeredit')) @if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.templatemakeredit')) &nbsp;<span title="'+title_str+'" style="cursor:pointer"><input name="ai'+o['id']+'" value="'+o['id']+'" type="checkbox" '+rcheck+'></span> @endif @endif';
                <?php if($subdomain[0]=="icat"){ ?>
                var assign_url = "{{route('pdf2pdf.AssignLab',':id')}}"
            	assign_url = assign_url.replace(':id',o['id']);
                buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('pdf2pdf.templatemakeredit')) @if(App\Helpers\RolePermissionCheck::isPermitted('pdf2pdf.templatemakeredit')) &nbsp;<span title="Assign Lab" style="cursor:pointer"><a href="'+assign_url+'"><i class="pdfGenerate fa fa-code-fork fa-lg blue"></i></a></span> @endif @endif';
                <?php } ?>
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

    var url="<?= URL::route('pdf2pdf.templatelist',['status'=>1])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
    $('.loader').removeClass('hidden');
});

//for displaying inactivate user(status = 0)
$('#fail-pill').click(function(){

    var url="<?= URL::route('pdf2pdf.templatelist',['status'=>0])?>";
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
    $(form).attr("action", "<?= URL::route('pdf2pdf.templatemakeredit')?>");
    $(form).attr("method", "POST");

    var input = $("<input>").attr("type", "hidden").attr("name", "id").val(id);
    $(form).append($(input));
    var input = $("<input>").attr("type", "hidden").attr("name", "_token").val(token);
    $(form).append($(input));
    $(document.body).append(form);
    $(form).submit();
        
    });

//duplicate
	oTable.on('click', '.duplicateData', function (e) {
       	$id = $(this).data('id');
		
var token="{{ csrf_token() }}";
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

			$.post('<?=route('pdf2pdf.duplicatetemplate')?>',{'_token':token,'id':$id},function(Result){			
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
				window.location.href = '<?=route('pdf2pdf.templatemaker')?>'
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
            url: "<?=route('pdf2pdf.createtextfile')?>"+subdomain+"/create_textfile.php?file="+progress_file+"&status=end",
            success:function(data){}
        });*/
        $.ajax({
            url: '<?=route('pdf2pdf.createtextfile')?>',
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
        var progress_file=Math.floor(Math.random() * Math.floor(Math.random() * Date.now()))+".txt"; 
        var files = $('#file_process')[0].files[0];
        if(typeof files === 'undefined'){ alert("Please select PDF."); return false;}
        /*$.ajax({
            url: "../"+subdomain+"/create_textfile.php?file="+progress_file+"&status=start",
            success:function(data){}
        }); */
        $.ajax({
            url: '<?=route('pdf2pdf.createtextfile')?>',
            type:"POST",
            data:{'_token':'<?= csrf_token()?>',"file":progress_file,"status":"start"},
            success:function(data){}
        });         
        wait(1000);  //1 second in milliseconds
        $('#progress-file').show();
        var fd = new FormData();
        fd.append('file',files);
        fd.append('template_id',template_id);
        fd.append('pdf_page',pdf_page);
        fd.append('progress_file',progress_file);
        fd.append('_token','<?= csrf_token()?>');
        $.ajax({
            url: '<?=route('pdf2pdf.processpdf')?>',
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
            url: '<?=route('pdf2pdf.processpdfagain')?>',
            type: 'post',
            data: {'_token':'<?= csrf_token()?>',file:file, template_id:template_id, pdf_page:pdf_page, unids:unids, progressfile:progress_file},
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
            url: '<?=route('pdf2pdf.createtextfile')?>',
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
            url: '<?=route('pdf2pdf.processpdf')?>',
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
            url: '<?=route('pdf2pdf.processpdf')?>',
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
            url: "../pdf2pdf/setBG.php",
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
        var token="{{ csrf_token() }}";
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
                        $.post('<?=route('pdf2pdf.ActiveInactiveTemplate')?>',{'_token':token, 'id':id, 'publish':publish, 'mode':mode},function(Result){
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
    
</script>	
@stop
