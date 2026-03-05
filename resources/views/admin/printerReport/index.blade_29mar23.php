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
                                <i class="fa fa-file-powerpoint-o"></i></i>Printer Report
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('printingreport') }}</ol>
                            </h1>
                        </div>
                    </div>
                    <div class="">
                        <table id="example" class="table table-hover display" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Template Name</th>
                                    <th>Excel Filename</th>
                                    <th>Pdf Filename</th>
                                    <th>UserName</th>
                                    <th>No.of Records</th>
                                    <th>Datetime</th>
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
        "sAjaxSource": "{{URL::route('printer-report.index')}}",
        "aaSorting": [
            [0, "desc"]
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
        { "mData": "template_name",sWidth: "20%",bSortable: true,
             mRender: function(v, t, o) {
                if(o['template_name']==null){
                    if(o['template_name_excel']!=''&&o['template_name_excel']!=null){
                    var actual_template_name='Custom Template - '+o['template_name_excel'];
                    }else{
                        var actual_template_name='Custom Template';
                    }
                }else{
                     var actual_template_name=o['template_name'];
                }
               
                return actual_template_name;
            },
        },
        
        {  
            "mData":function (o,t,v){
                var flag = '<?= $file_aws_local?>'
                var systemConfig = '<?= $config?>'
                <?php
                    $domain = \Request::getHost();
                    $subdomain = explode('.', $domain);
                ?>
                if(systemConfig == '1'){
                    if(flag == '1'){
                        var path =  "<?= Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.sandbox')?>"+"/"
                    }
                    else{
                        var path =  "<?= 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/'.Config::get('constant.sandbox')?>"+"/"
                    }
                }
                else{
                    if(flag == '1'){
                        var path =  "<?= Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.template')?>"+"/"
                    }
                    else{
                        var path =  "<?= 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/'.Config::get('constant.template')?>"+"/"
                    }
                }

                console.log(o);
                var path = path+o.template_id+'/'+ o.excel_sheet_name;
                return '<a data-toggle="tooltip" data-placement="right" target="_blank" download title="Please click to download excel file" class="btn btn-success" href="'+ path +'">' + o.excel_sheet_name + '</a>';
                
            },
            sWidth: "20%",
            bSortable: false,
            
        },
        { 
            "mData":function (o,t,v){
                var flag = '<?= $file_aws_local?>'
                var systemConfig = '<?= $config?>'
                <?php
                    $domain = \Request::getHost();
                    $subdomain = explode('.', $domain);
                ?>
                if(systemConfig == '1'){
                    if(flag == '1'){
                        var path =  "<?= Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.sandbox')?>"+"/"
                    }
                    else{
                        var path =  "<?= 'http://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/'.Config::get('constant.sandbox')?>"+"/"
                    }
                }
                else{
                    if(flag == '1'){
                        var path =  "<?= Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.template')?>"+"/"
                    }
                    else{
                        var path =  "<?= 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/backend/tcpdf/examples/' ?>"
                    }
                }

                var path = path+ o.pdf_file;
                if(o.pdf_file != null){

                    return '<a data-toggle="tooltip" data-placement="right" target="_blank" download title="Please click to download excel file" class="btn btn-success" href="'+ path +'">' + o.pdf_file + '</a>';
                }else{
                    return '';
                }
                
            },
            sWidth: "20%",
            bSortable: false,
        },
        { "mData": "user",sWidth: "20%",bSortable: false},
        { "mData": "no_of_records",sWidth: "20%",bSortable: false},
        { "mData": "created_on",sWidth: "20%",bSortable: true},
        ],
        
        fnPreDrawCallback : function() { 
            $("#spin").show();
    
        },
        fnDrawCallback : function (oSettings) {
            $("#spin").hide();
        }
    }); 
</script>
@stop
