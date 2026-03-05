@extends('admin.layout.layout')
@section('content')

<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div id="">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header">
                                <i class="fa fa-fw fa fa-file-o"></i>ID Cards
                               <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('generateidcard') }}</ol>
                               <i class="fa fa-info-circle iconModalCss" title="User Manual" id="generateIdCardsClick"></i>
                            </h1>
                        </div>
                    </div>

                    <!-- Generate pdf modal start -->

                    <div class="modal fade" id="uploadFile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabel">Upload excel</h4>
                                </div>
                                <div class="modal-body">
                                    
                                        <div class="form-group">
                                            <label>Upload File</label>
                                            <input type="file" class="form-control" id="field_file" name="field_file">
                                            <input type="hidden" name="id" id="template_id">
                                            <input type="hidden" name="varified" id="varified">
                                            <input type="hidden" name="func" id="func" value="ManageIdCards">
                                            <input type="hidden" name="print_type" value="pdf">
                                        </div>
                                        <div id="downloadLink">
                                        </div>
                                        <div class="form-group clearfix">
                                            <div id="duplicate_row_count" class=""></div>
                                            <div id="upload_btn" style="float:inline-end;">
                                                <a href="#" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-7" id="btn_updfile_back" onclick="closeModel();" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
                                                <button type="submit" class="btn btn-theme" value="pdf_generate" id="pdf_generate" name="pdf_generate" style="display: none"><i class="fa fa-upload" ></i>PDF Generate</button>
                                                <button type="submit" class="btn btn-theme" id="btn_updfile"><i class="fa fa-upload"></i> Upload</button>
                                                <button type="button" class="btn btn-theme " id="excel_url"><i class="fa fa-download"></i> Download Sample Excel</button>
                                            </div>
                                        </div>
                                    
                                    <div class="form-group">
                                        <div>Note:-</div>
                                        <ol>
                                            <li>Ensure that all fields are mapped.</li>
                                            <li>Ensure that all fonts used in templates master are available.</li>
                                            <li>Ensure that all columns mapped in the template master are available with the same name in your current uploading excel sheet.</li>
                                            <li>Ensure that the serial no in excel file is unique across all data.</li>
                                            <li>Name are case sensitive, column sequence insensitive, extra columns are ignored.</li>
                                            <li>If the unique serial number is repeated or found duplicate in the excel data, then it will replace the already existing data (making them inactive), with the current excel data.</li>
                                            <li>Accepted file format XLS or XLSX.</li>
                                            <li>Max file size 10 MB</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="uploadFileExcel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabel">Upload excel</h4>
                                </div>
                                <div class="modal-body">

                                                            
                                    <form method="post"  enctype="multipart/form-data" id="updfilefrmExcel"> 
                                        <div class="form-group">
                                            <label>Upload File</label>
                                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                                            <input type="file" class="form-control" id="field_file_excel" name="field_file_excel">
                                            <span id="field_file_error" class="help-inline text-danger"></span>
                                            <input type="hidden" name="id" id="template_id_excel">
                                            <input type="hidden" name="func" id="func_excel" value="generateUploadfile">
                                            <input type="hidden" name="print_type" value="pdf">
                                        </div>
                                        <div id="downloadLinkExcel">
                                        </div>
                                        <div class="form-group clearfix">
                                            <div id="duplicate_row_count" class=""></div>
                                            <div id="upload_btn" style="float:inline-end;">
                                                <a href="#" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-7" id="btn_updfile_back_excel" onclick="closeModel();" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
                                                <button type="button" class="btn btn-theme" value="pdf_generate" id="pdf_generate_excel" name="pdf_generate" style="display: none"><i class="fa fa-upload" ></i>PDF Generate</button>
                                                <button type="button" class="btn btn-theme " id="btn_updfile_excel"><i class="fa fa-upload"></i> Upload</button>
                                                <button type="button" class="btn btn-theme " id="excel_url_soft"><i class="fa fa-download"></i> Download Sample Excel</button>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                          <span class="sr-only progress_bar_text"></span>
                                        </div>
                                    </div>
                                    <p class="pdf_progress">Pdf Process Count : <span class="pdf_count text-danger"></span></p>
                                    <p class="time_details">Generation start time : <span class="start_time text-danger"></span><br/>Generation end time : <span class="end_time text-danger"></span><br/>Generation total time : <span class="total_time text-danger"></span><br/>Average Speed : <span class="avg_time text-danger"></span></p>
                                    <input type="hidden" name="pdf_generation_start_time" class="pdf_gen_start_time" value="">
                                    <input type="hidden" name="pdf_generation_start_timestamp" class="pdf_gen_start_timestamp" value="">
                                    <input type="hidden" name="pdf_generation_end_time" class="pdf_gen_end_time" value="">


                                    <div class="form-group">
                                        <div>Note:-</div>
                                        <ol>
                                            <li>Ensure that all fields are mapped.</li>
                                            <li>Ensure that all fonts used in templates master are available.</li>
                                            <li>Ensure that all columns mapped in the template master are available with the same name in your current uploading excel sheet.</li>
                                            <li>Ensure that the serial no in excel file is unique across all data.</li>
                                            <li>Name are case sensitive, column sequence insensitive, extra columns are ignored.</li>
                                            <li>If the unique serial number is repeated or found duplicate in the excel data, then it will replace the already existing data (making them inactive), with the current excel data.</li>
                                            <li>Accepted file format XLS or XLSX.</li>
                                            <li>Max file size 10 MB</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- digital id card modals start -->
                     <div class="modal fade" id="uploadFileDIC" tabindex="-1" role="dialog" aria-labelledby="myModalLabeldic" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button onclick="closeModel();" type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabeldic">Upload excel</h4>
                                </div>
                                <div class="modal-body">
                                    
                                        <div class="form-group">
                                            <label>Upload File</label>
                                            <input type="file" class="form-control" id="field_filedic" name="field_file">
                                            <input type="hidden" name="id" id="template_iddic">
                                            <input type="hidden" name="varified" id="varifieddic">
                                            <input type="hidden" name="func" id="funcdic" value="ManageIdCards">
                                            <input type="hidden" name="print_type" value="pdf">
                                        </div>
                                        <div id="downloadLinkdic">
                                        </div>
                                        <div class="form-group clearfix">
                                            <div id="duplicate_row_countdic" class=""></div>
                                            <div id="upload_btndic" style="float: inline-end;">
                                                <a href="#" class="btn btn-theme " id="btn_updfile_backdic" onclick="closeModel();" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
                                                <button type="submit" class="btn btn-theme" value="pdf_generate" id="pdf_generatedic" name="pdf_generate" style="display: none"><i class="fa fa-upload" ></i>PDF Generate</button>
                                                <button type="button" class="btn btn-theme" value="pdf_generate_duplicate" id="pdf_generate_duplicatedic" name="pdf_generate_duplicate" data-file="" style="display: none"><i class="fa fa-upload" ></i>PDF Generate</button>
                                                <button type="submit" class="btn btn-theme " id="btn_updfiledic" onclick="this.innerHTML='Please wait...'; this.disabled=true;"><i class="fa fa-upload"></i> Upload</button>
                                            </div>
                                        </div>
                                    
                                    <div class="form-group">
                                        <div>Note:-</div>
                                        <ol>
                                            <li>Ensure that all fields are mapped.</li>
                                            <li>Ensure that all fonts used in templates master are available.</li>
                                            <li>Ensure that all columns mapped in the template master are available with the same name in your current uploading excel sheet.</li>
                                            <li>Ensure that the serial no in excel file is unique across all data.</li>
                                            <li>Name are case sensitive, column sequence insensitive, extra columns are ignored.</li>
                                            <li>If the unique serial number is repeated or found duplicate in the excel data, then it will replace the already existing data (making them inactive), with the current excel data.</li>
                                            <li>Accepted file format XLS or XLSX.</li>
                                            <li>Max file size 10 MB</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="modal fade" id="uploadFileExcelDIC" tabindex="-1" role="dialog" aria-labelledby="myModalLabeldic" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id="myModalLabeldic">Upload excel</h4>
                                </div>
                                <div class="modal-body">

                                                            
                                    <form method="post"  enctype="multipart/form-data" id="updfilefrmExceldic"> 
                                        <div class="form-group">
                                            <label>Upload File</label>
                                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                                            <input type="file" class="form-control" id="field_file_exceldic" name="field_file_exceldic">
                                            <span id="field_file_error" class="help-inline text-danger"></span>
                                            <input type="hidden" name="id" id="template_id_exceldic">
                                            <input type="hidden" name="func" id="func_exceldic" value="generateUploadfile">
                                            <input type="hidden" name="print_type" value="pdf">
                                        </div>
                                        <div id="downloadLinkExceldic">
                                        </div>
                                        <div class="form-group clearfix">
                                            <div id="duplicate_row_countdic" class=""></div>
                                            <div id="duplicate_row_count_softdic" class="" style="color: red;"></div>
                                            <div id="upload_btndic" style="float: inline-end;">
                                                <a href="#" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-7" id="btn_updfile_back_exceldic" onclick="closeModel();" style="display: none;"><i class="fa fa-arrow-left"></i>Back</a>  
                                                <button type="button" class="btn btn-theme" value="pdf_generate" id="pdf_generate_exceldic" name="pdf_generate" style="display: none"><i class="fa fa-upload" ></i>PDF Generate</button>
                                                <button type="button" class="btn btn-theme" value="pdf_generate_duplicatesoft" id="pdf_generate_duplicatesoftdic" name="pdf_generate_duplicatesoft" style="display: none"><i class="fa fa-upload" ></i>PDF Generate</button> 
                                                <button type="button" class="btn btn-theme " id="btn_updfile_exceldic"><i class="fa fa-upload"></i> Upload</button>
                                                <button type="button" class="btn btn-theme " id="excel_url_digital"><i class="fa fa-download"></i> Download Sample Excel</button>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                          <span class="sr-only progress_bar_text"></span>
                                        </div>
                                    </div>

                                    <p class="pdf_progress">Pdf Process Count : <span class="pdf_count text-danger"></span></p>
                                    <p class="time_details">Generation start time : <span class="start_time text-danger"></span><br/>Generation end time : <span class="end_time text-danger"></span><br/>Generation total time : <span class="total_time text-danger"></span><br/>Average Speed : <span class="avg_time text-danger"></span></p>
                                    <input type="hidden" name="pdf_generation_start_time" class="pdf_gen_start_time" value="">
                                    <input type="hidden" name="pdf_generation_start_timestamp" class="pdf_gen_start_timestamp" value="">
                                    <input type="hidden" name="pdf_generation_end_time" class="pdf_gen_end_time" value="">


                                    <div class="form-group">
                                        <div>Note:-</div>
                                        <ol>
                                            <li>Ensure that all fields are mapped.</li>
                                            <li>Ensure that all fonts used in templates master are available.</li>
                                            <li>Ensure that all columns mapped in the template master are available with the same name in your current uploading excel sheet.</li>
                                            <li>Ensure that the serial no in excel file is unique across all data.</li>
                                            <li>Name are case sensitive, column sequence insensitive, extra columns are ignored.</li>
                                            <li>If the unique serial number is repeated or found duplicate in the excel data, then it will replace the already existing data (making them inactive), with the current excel data.</li>
                                            <li>Accepted file format XLS or XLSX.</li>
                                            <li>Max file size 10 MB</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- digital id card modal end -->
                    <!-- modal close -->
                    <div class="">
                        <table id="example" class="table table-hover display" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Template Name</th>
                                    <th>Printing ID Card</th>
                                    <th>Softcopy ID Card</th>
                                    <th>Digital ID Card</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
    //datatable for background master
    var oTable = $('#example').dataTable({
        'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "sAjaxSource": "{{URL::route('idcards.index')}}",
        "aaSorting": [
            [1, "asc"]
        ],
        "oLanguage": {
          "sLengthMenu": "Display _MENU_ records"
        },
    
        "aoColumns": [
        
        { 
            "mData": "serial_no",
            sWidth: "2%",
            mRender: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { "mData": "template_name",sWidth: "20%",bSortable: true},
        
        {
            mData: null,
            bSortable: false,
            sWidth: "20%",
            sClass: "text-center",
            mRender: function(v, t, o) {
                
                var buttons = '';
                
                buttons += '@if(App\Helpers\SitePermissionCheck::isPermitted('idcards.manageExcel')) @if(App\Helpers\RolePermissionCheck::isPermitted('idcards.manageExcel')) <span style="cursor:pointer;margin-right:6px;"><i title="Generate PDF" data-id="'+o['id']+'" class="pdfGenerate fa fa-file-excel-o fa-lg blue"></i></span>' @endif  @endif;

                

                return buttons;
            }
        },
        {
            mData: null,
            bSortable: false,
            sWidth: "20%",
            sClass: "text-center",
            mRender: function(v, t, o) {
                var buttons = '';

                buttons += '<span style="cursor:pointer"><i title="Softcopy ID Card" data-id="'+o['id']+'" class="SoftcopyGenerate fa fa-file-pdf-o fa-lg green"></i></span>';

                return buttons;
            }
        },
        {
            mData: null,
            bSortable: false,
            sWidth: "20%",
            sClass: "text-center",
            mRender: function(v, t, o) {
                var buttons = '';

                buttons += '<span style="cursor:pointer"><i title="Digital ID Card" data-id="'+o['id']+'" class="SoftcopyGeneratedic fa fa-file-pdf-o fa-lg red"></i></span>';

                return buttons;
            }
        },
        ],
        
        fnPreDrawCallback : function() { 
            $("#spin").show();
    
        },
        fnDrawCallback : function (oSettings) {
            $("#spin").hide();
        }
    }); 

    $('#field_file').change(function(){
        $('#func').val('checkExcel');   
        $('#btn_updfile_back').hide();
        $('#pdf_generate').hide();
        $('#btn_updfile').show();
    });
    $('.progress').hide();
    $('.pdf_progress').hide();
    $('.time_details').hide();

    $('#field_file_excel').change(function(){
        $('#func_excel').val('checkExcel');   
        $('#btn_updfile_back').hide();
        $('#pdf_generate_excel').hide();
        $('#btn_updfile_excel').show();
    });
    $('#field_file_exceldic').change(function(){
    $('#func_exceldic').val('checkExcel');   
    $('#btn_updfile_backdic').hide();
    $('#pdf_generate_exceldic').hide();
    $('#btn_updfile_exceldic').show();
    });
    oTable.on('click','.pdfGenerate',function(e){
        var id = $(this).attr('data-id');
        $('#template_id').val(id);
        $('#uploadFile').modal('show');
    });
    oTable.on('click','.SoftcopyGenerate',function(e){
        var id = $(this).attr('data-id');
        $('#template_id_excel').val(id);
        $('#uploadFileExcel').modal('show');
    });
    oTable.on('click','.SoftcopyGeneratedic',function(e){
        var id = $(this).attr('data-id');
        $('#template_id_exceldic').val(id);
        $('#uploadFileExcelDIC').modal('show');
    });
    $('#btn_updfile_excel').click(function (event) {
        var fd = new FormData();
        var id = $('#template_id_excel').val();
        var files = $('[name="field_file_excel"]')[0].files[0];
        var token = "<?= csrf_token()?>";
        $('#downloadLinkExcel').html('<b>Please Wait your download will ready<b>');
        fd.append('field_file',files);
        fd.append('_token',token);
        fd.append('id',id);

        $.ajax({
            url:'<?= route('idcards_validation.excelvalidation')?>',
            type: "POST",
            dataType: "JSON",
            data:fd,
            processData: false,
            contentType:false,
            async:false,
            success: function(response) 
            {
                if(response.success == false){
                    toastr["error"](response.message);
                }
                else{
                    checkExcelSoftCopy(fd);
                }
            }
        });
    });

    $('#btn_updfile_exceldic').click(function (event) {
        var fd = new FormData();
        var id = $('#template_id_exceldic').val();
        var files = $('[name="field_file_exceldic"]')[0].files[0];
        var token = "<?= csrf_token()?>";
        
        fd.append('field_file',files);
        fd.append('_token',token);
        fd.append('id',id);

        $(this).html('Please wait...').prop('disabled', true);
        $.ajax({
            url:'<?= route('dicidcards_validation.excelvalidation')?>',
            type: "POST",
            dataType: "JSON",
            data:fd,
            processData: false,
            contentType:false,
            // async:false,
            beforeSend: function() {
             $('#downloadLinkExceldic').html('<b>Please Wait your download will ready <b>');   
            },
            success: function(response) 
            {
                $('#downloadLinkExceldic').html('');
                if(response.success == false){
                    toastr["error"](response.message);
                }
                else{
                    checkExcelSoftCopydic(fd);
                }
            }
        });
    });

    $('#btn_updfile').click(function (event) {
        var fd = new FormData();
        var id = $('#template_id').val();
        var files = $('[name="field_file"]')[0].files[0];
        var token = "<?= csrf_token()?>";
        
        fd.append('field_file',files);
        fd.append('_token',token);
        fd.append('id',id);

        $.ajax({
            url:'<?= route('idcards_validation.excelvalidation')?>',
            type: "POST",
            dataType: "JSON",
            data:fd,
            processData: false,
            contentType:false,
            async:false,
            success: function(response) 
            {
                if(response.success == false){
                    toastr["error"](response.message);
                }
                else{
                    checkExcel(fd);
                }
            }
        });
    });
    function checkExcelSoftCopy(file){

        var fd = new FormData();
        var id = $('#template_id_excel').val();
        var files = $('[name="field_file_excel"]')[0].files[0];
        var token = "<?= csrf_token()?>";
        
        fd.append('field_file',files);
        fd.append('_token',token);
        fd.append('id',id);

        $.ajax({
            url:'<?= route('idcards_validation.excelvalidation')?>',
            type: "POST",
            dataType: "JSON",
            data:fd,
            processData: false,
            contentType:false,
            async:false,
            success: function(response) 
            {
               if(response.type == 'duplicate'){
                    
                    $('#varified').val(1);
                    
                }
                else{
                    $('#varified').val(0);
                    
                }
                manageExcelSoftCopy()
            }
        });
    }
    function checkExcelSoftCopydic(file){

        var fd = new FormData();
        var id = $('#template_id_exceldic').val();
        var files = $('[name="field_file_exceldic"]')[0].files[0];
        var token = "<?= csrf_token()?>";
        
        fd.append('field_file',files);
        fd.append('_token',token);
        fd.append('id',id);

        $.ajax({
            url:'<?= route('dicidcards_validation.excelvalidation')?>',
            type: "POST",
            dataType: "JSON",
            data:fd,
            processData: false,
            contentType:false,
            // async:false,
            success: function(response) 
            {
               if(response.type == 'duplicate'){
                    
                    $('#varifieddic').val(1);
                    
                }
                else{
                    $('#varifieddic').val(0);
                    
                }
                manageExcelSoftCopydic()
            }
        });
    }
    function manageExcelSoftCopy(){


        var formData = new FormData($("form#updfilefrmExcel")[0]);

        $.ajax({
            url: '<?= route('idcards.generate.softcopy')?>',
            data: formData,           // Data sent to server, a set of key/value pairs (i.e. form fields and values)
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false, 
            type: "POST",
            dataType:'json',
            success: function(response) {

                if(response.success == false){
                    $('#downloadLinkExcel').html('<b>Some images are missing in folder</b><br>');
                    $('#downloadLinkExcel').append(response.msg)
                    toastr["error"](response.message);
                    
                }
                else{
                    
                    uploadfile(is_progress = 'no',excel_row = 0);
                }
            }
        });
    }
function manageExcelSoftCopydic(){


        var formData = new FormData($("form#updfilefrmExceldic")[0]);

        $.ajax({
            url: '<?= route('dicidcards.generate.softcopy')?>',
            data: formData,           // Data sent to server, a set of key/value pairs (i.e. form fields and values)
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false, 
            type: "POST",
            dataType:'json',
            success: function(response) {
                 $('#btn_updfile_exceldic').html('Upload').prop('disabled', false);
                console.log(response);
                if(response.success == false){
                    $('#downloadLinkExceldic').html('<b>Some images are missing in folder</b><br>');
                    $('#downloadLinkExceldic').append(response.msg)
                    toastr["error"](response.message);
                    
                }else if(response.success == '2'){
                    $('#pdf_generate_duplicatesoftdic').show();
                    $('#btn_updfile_backdic').show();
                    $('#btn_updfiledic').hide();
                    $('#duplicate_row_count_softdic').html('<b>'+response.message+'</b>');
                    // $('#duplicate_row_count_soft').css('color','red !important');
                     // $('#downloadLink').html('<b>Some images are missing in folder</b><br>');
                    $('#downloadLinkExceldic').append(response.msg);
                    // $('#pdf_generate_duplicatesoft').data('file',file);
                    toastr["error"](response.message);
                }else{
                    uploadfiledic(is_progress = 'no',excel_row = 0);
                }
            }
        });
    }
    $('#pdf_generate_duplicatesoftdic').click(function (event) {
        uploadfiledic(is_progress = 'no',excel_row = 0);
    });
    function uploadfile(is_progress = 'no',excel_row = 0){

        $('#pdf_generate_excel').attr('disabled',true);
        $('#duplicate_row_count').empty();

        $('.close').attr('disabled',true);
        $('#btn_updfile_back_excel').attr('disabled',true);
        var formData = new FormData($("form#updfilefrmExcel")[0]);
        if(is_progress == 'yes'){
            formData.append('is_progress','yes');
            formData.append('excel_row',excel_row);
        }
        else{
            $('.progress').show();
            $('.progress-bar').text('1% Complete');
            $('.progress-bar').css('width','1%')
            //store generation start time
            var current_date = new Date();
            var current_time = current_date.getTime();
            $('.pdf_gen_start_time').val(current_date)
            $('.pdf_gen_start_timestamp').val(current_time)
        }
        $.ajax({
            url: '<?= route('idcards.generate.softcopygenerate')?>',
            data: formData,           // Data sent to server, a set of key/value pairs (i.e. form fields and values)
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false, 
            type: "POST",
            dataType:'json',
            success: function(response) {
                if(response.success == false){
                    toastr["error"](response.message);
                    setTimeout(function(){
                        window.location = '<?= route('idcards.index')?>';
                    },500)
                }
                else{
                    $('#downloadLinkExcel').empty();
                    if(response.type == 'formula'){
                        var toString = response.cell.toString();    
                        var columns = toString.split(',').join(' , ');
                        $('#downloadLinkExcel').html(response.message+' '+columns);
                        $('#downloadLinkExcel').removeAttr('style');
                    }else if(response.type == 'fieldNotMatch'){
                        $('#downloadLinkExcel').html(response.message);
                        $('#field_file').val('');
                        $('#downloadLinkExcel').css('color','red');
                        $('#duplicate_row_count').empty();
                        $('#pdf_generate').removeAttr('disabled');
                        $('#btn_updfile_back').removeAttr('disabled');
                        $('#btn_updfile_back').hide();
                        $('#pdf_generate').hide();
                        $('#btn_updfile').show();
                    }else{  
                        
                        if(response.is_progress == 'yes'){
                            var excelRow = parseInt(response.excel_row);
                            var highestRow = parseInt(response.highestRow);
                            var per = 100/(highestRow -1)//get percentage calculation according to excel row divide by 100 
                            var per = (per) * (excelRow - 2);
                            var per = per.toFixed(2);
                            $('.progress-bar').text(per+'% Complete');
                            $('.progress-bar').css('width',per+'%')
                            var current_count = (excelRow)-2;
                            var total_count = highestRow-1;
                                var display_count = current_count+'/'+total_count;
                            $('.pdf_progress').show();
                            //display count 
                            $('.pdf_count').text(display_count)
                            uploadfile(response.is_progress,excelRow)
                        }
                        else{

                            
                            var highestRow = parseInt(response.highestRow);
                            $('.progress-bar').text('100% Complete');
                            $('.progress-bar').css('width','100%')
                            $('.progress').hide();

                            //get generation start time
                            var pdf_gen_start_date = $('.pdf_gen_start_time').val()
                            var pdf_gen_start_timestamp = parseInt($('.pdf_gen_start_timestamp').val())
                            var date_split = pdf_gen_start_date.split(' ')
                            var pdf_generation_start_time = date_split[4];


                            //get generation end time
                            var pdf_end_date = new Date();
                            $('.pdf_gen_end_time').val(pdf_end_date)
                            var pdf_gen_end_time = $('.pdf_gen_end_time').val()
                            var end_date_split = pdf_gen_end_time.split(' ')
                            var pdf_generation_end_time = end_date_split[4];

                            //display time
                            $('.time_details').show();
                            $('.start_time').text(pdf_generation_start_time)
                            $('.end_time').text(pdf_generation_end_time)
                            var time_diff =(pdf_end_date.getTime() - pdf_gen_start_timestamp) / 1000;
                    
                            var get_time_diff = convertTime(time_diff)
                            var hours = get_time_diff.hour;
                            var mins = get_time_diff.minute;
                            var sec = get_time_diff.seconds;
                            $('.total_time').text(hours+' hrs '+mins+' mins '+sec+' sec')

                            var total_time_sec = (hours*60*60)+(mins*60)+(sec)
                            console.log(total_time_sec)
                            var avg_sec = (total_time_sec)/(highestRow-1);
                            console.log(avg_sec)
                            var avg_time = avg_sec.toFixed(3);
                            $('.avg_time').text(avg_time+' sec')

                            $('#downloadLinkExcel').html(response.msg);
                            $('#downloadLinkExcel').removeAttr('style');
                            $('#downloadLinkExcel').click(function(){
                                $('#uploadFile').modal('hide');
                                // form.reset();
                                $('#downloadLinkExcel').empty();
                                $('#duplicate_row_count').empty();
                                $('#pdf_generate').removeAttr('disabled');
                                $('#downloadLinkExcel').empty();
                                $('.close').removeAttr('disabled');
                                $('#btn_updfile_back').removeAttr('disabled');
                                $('#btn_updfile_back').hide();
                                $('#pdf_generate').hide();
                                $('#btn_updfile').show();
                                $('#field_file').val('');

                                $('.pdf_progress').hide();
                                $('.time_details').hide();
                                $('.progress-bar').text('0% Complete');
                                $('.progress-bar').css('width','0%')

                            })
                        }
                    }
                }
            }
        }); 
    }
    function uploadfiledic(is_progress = 'no',excel_row = 0){

        $('#pdf_generate_exceldic').attr('disabled',true);
        $('#duplicate_row_countdic').empty();

        $('.close').attr('disabled',true);
        $('#btn_updfile_back_exceldic').attr('disabled',true);
        var formData = new FormData($("form#updfilefrmExceldic")[0]);
        if(is_progress == 'yes'){
            formData.append('is_progress','yes');
            formData.append('excel_row',excel_row);
        }
        else{
            $('.progress').show();
            $('.progress-bar').text('1% Complete');
            $('.progress-bar').css('width','1%')
            //store generation start time
            var current_date = new Date();
            var current_time = current_date.getTime();
            $('.pdf_gen_start_time').val(current_date)
            $('.pdf_gen_start_timestamp').val(current_time)
        }
        $.ajax({
            url: '<?= route('dicidcards.generate.softcopygenerate')?>',
            data: formData,           // Data sent to server, a set of key/value pairs (i.e. form fields and values)
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false, 
            type: "POST",
            dataType:'json',
            success: function(response) {
                if(response.success == false){
                    toastr["error"](response.message);
                    setTimeout(function(){
                        window.location = '<?= route('idcards.index')?>';
                    },500)
                }
                else{
                    $('#downloadLinkExceldic').empty();
                    if(response.type == 'formula'){
                        var toString = response.cell.toString();    
                        var columns = toString.split(',').join(' , ');
                        $('#downloadLinkExceldic').html(response.message+' '+columns);
                        $('#downloadLinkExceldic').removeAttr('style');
                    }else if(response.type == 'fieldNotMatch'){
                        $('#downloadLinkExceldic').html(response.message);
                        $('#field_filedic').val('');
                        $('#downloadLinkExceldic').css('color','red');
                        $('#duplicate_row_countdic').empty();
                        $('#pdf_generatedic').removeAttr('disabled');
                        $('#btn_updfile_backdic').removeAttr('disabled');
                        $('#btn_updfile_backdic').hide();
                        $('#pdf_generatedic').hide();
                        $('#btn_updfiledic').show();
                    }else{  
                        
                        if(response.is_progress == 'yes'){
                            var excelRow = parseInt(response.excel_row);
                            var highestRow = parseInt(response.highestRow);
                            var per = 100/(highestRow -1)//get percentage calculation according to excel row divide by 100 
                            var per = (per) * (excelRow - 2);
                            var per = per.toFixed(2);
                            $('.progress-bar').text(per+'% Complete');
                            $('.progress-bar').css('width',per+'%')
                            var current_count = (excelRow)-2;
                            var total_count = highestRow-1;
                                var display_count = current_count+'/'+total_count;
                            $('.pdf_progress').show();
                            //display count 
                            $('.pdf_count').text(display_count)
                            uploadfiledic(response.is_progress,excelRow)
                        }
                        else{

                            
                            var highestRow = parseInt(response.highestRow);
                            $('.progress-bar').text('100% Complete');
                            $('.progress-bar').css('width','100%')
                            $('.progress').hide();

                            //get generation start time
                            var pdf_gen_start_date = $('.pdf_gen_start_time').val()
                            var pdf_gen_start_timestamp = parseInt($('.pdf_gen_start_timestamp').val())
                            var date_split = pdf_gen_start_date.split(' ')
                            var pdf_generation_start_time = date_split[4];


                            //get generation end time
                            var pdf_end_date = new Date();
                            $('.pdf_gen_end_time').val(pdf_end_date)
                            var pdf_gen_end_time = $('.pdf_gen_end_time').val()
                            var end_date_split = pdf_gen_end_time.split(' ')
                            var pdf_generation_end_time = end_date_split[4];

                            //display time
                            $('.time_details').show();
                            $('.start_time').text(pdf_generation_start_time)
                            $('.end_time').text(pdf_generation_end_time)
                            var time_diff =(pdf_end_date.getTime() - pdf_gen_start_timestamp) / 1000;
                    
                            var get_time_diff = convertTime(time_diff)
                            var hours = get_time_diff.hour;
                            var mins = get_time_diff.minute;
                            var sec = get_time_diff.seconds;
                            $('.total_time').text(hours+' hrs '+mins+' mins '+sec+' sec')

                            var total_time_sec = (hours*60*60)+(mins*60)+(sec)
                            console.log(total_time_sec)
                            var avg_sec = (total_time_sec)/(highestRow-1);
                            console.log(avg_sec)
                            var avg_time = avg_sec.toFixed(3);
                            $('.avg_time').text(avg_time+' sec')

                            $('#downloadLinkExceldic').html(response.msg);
                            $('#downloadLinkExceldic').removeAttr('style');
                            $('#downloadLinkExceldic').click(function(){
                                $('#uploadFileDIC').modal('hide');
                                // form.reset();
                                $('#downloadLinkExceldic').empty();
                                $('#duplicate_row_countdic').empty();
                                $('#pdf_generatedic').removeAttr('disabled');
                                $('#downloadLinkExceldic').empty();
                                $('.close').removeAttr('disabled');
                                $('#btn_updfile_backdic').removeAttr('disabled');
                                $('#btn_updfile_backdic').hide();
                                $('#pdf_generatedic').hide();
                                $('#btn_updfiledic').show();
                                $('#field_filedic').val('');

                                $('.pdf_progress').hide();
                                $('.time_details').hide();
                                $('.progress-bar').text('0% Complete');
                                $('.progress-bar').css('width','0%')

                            })
                        }
                    }
                }
            }
        }); 
    }
    function convertTime( milliseconds ) {
        var day, hour, minute, seconds;
        seconds = Math.floor(milliseconds);
        minute = Math.floor(seconds / 60);
        seconds = seconds % 60;
        hour = Math.floor(minute / 60);
        minute = minute % 60;
        day = Math.floor(hour / 24);
        hour = hour % 24;
        return {
            day: day,
            hour: hour,
            minute: minute,
            seconds: seconds
        };
    }
    function checkExcel(file)
    {
        $.ajax({
            url:'<?= route('idcards_validation.excelcheck')?>',
            type: "POST",
            dataType: "JSON",
            data:file,
            processData: false,
            contentType:false,
            async:false,
            success: function(response) 
            {
                console.log(response);
                if(response.type == 'duplicate'){
                    
                    $('#varified').val(1);
                     manageExcel(file);
                }
                else{
                    $('#varified').val(0);
                    manageExcel(file);
                }
            }
        });
    }
    function manageExcel(file)
    {
        $.ajax({
            url:'<?= route('idcards.manageExcel')?>',
            type: "POST",
            dataType: "JSON",
            data:file,
            processData: false,
            contentType:false,
            async:false,
            success: function(response) 
            {
              
                if(response.success == false){
                    $('#downloadLink').html('<b>Some images are missing in folder</b><br>');
                    $('#downloadLink').append(response.msg);
                    toastr["error"](response.message);
                }
                else{
                    $('#downloadLink').html('');
                    toastr["success"](response.message);
                    setTimeout(function(){
                            window.location = '{{ route("idcards.index") }}';
                        },3000)
                }
            }
        });
    }
    $("#excel_url").click(function() {

    let tmpl_id = $("#template_id").val(); // get value from input

    let domain = window.location.hostname;
    let subdomain = domain.split('.');
    let protocol = window.location.protocol;

    let path = protocol + "//" + subdomain[0] + "." + subdomain[1] + ".com/";
    
    let excel_url = path + subdomain[0] + "/backend/templates/sampleexcel/soft/" + tmpl_id + "/sampleexcel.xlsx";

    window.open(excel_url, "_blank"); // open in new tab
});
    $("#excel_url_soft").click(function() {

    let tmpl_id = $("#template_id_excel").val(); // get value from input

    let domain = window.location.hostname;
    let subdomain = domain.split('.');
    let protocol = window.location.protocol;

    let path = protocol + "//" + subdomain[0] + "." + subdomain[1] + ".com/";
    
    let excel_url = path + subdomain[0] + "/backend/templates/sampleexcel/soft/" + tmpl_id + "/sampleexcel.xlsx";

    window.open(excel_url, "_blank"); // open in new tab
});
    $("#excel_url_digital").click(function() {

    let tmpl_id = $("#template_id_exceldic").val(); // get value from input

    let domain = window.location.hostname;
    let subdomain = domain.split('.');
    let protocol = window.location.protocol;

    let path = protocol + "//" + subdomain[0] + "." + subdomain[1] + ".com/";
    
    let excel_url = path + subdomain[0] + "/backend/templates/sampleexcel/digital/" + tmpl_id + "/sampleexcel.xlsx";

    window.open(excel_url, "_blank"); // open in new tab
});
</script>
@stop
