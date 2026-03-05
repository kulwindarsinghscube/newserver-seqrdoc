@extends('admin.layout.layout')
@section('content')
<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div class="modal fade" id="backgroundTemplatePreview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Background Preview</h4>
                        </div>
                        <div class="modal-body" id="modalBody">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="backgroundTemplateImage" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog-lg">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Background Image</h4>
                        </div>
                        <div class="modal-body" id="modalBodyImage">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header">
                                <i class="fa fa-fw fa fa-file-o"></i>Background Template Master
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('backgroundmaster') }}</ol>
                                <i class="fa fa-info-circle iconModalCss" title="User Manual" id="backgroundTemplateManagementClick"></i>
                            </h1>
                        </div>
                    </div>
                    <div class="">
                        <ul class="nav nav-pills" id="pills-filter">
                            <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Templates </a></li>
                            <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Templates</a></li>

                            @if(App\Helpers\SitePermissionCheck::isPermitted('background-master.excelExport'))
                            @if(App\Helpers\RolePermissionCheck::isPermitted('background-master.excelExport'))
                            <li style="float: right;">
                                <a href="{{ route('background-master.excelExport') }}" style="background: #0052CC;padding-top:5px; color: #fff;border-radius: 2px;box-shadow: 2px 2px 5px #999;border: 1px solid transparent;height: 35px;" id="report" ><i class="fa fa-file"></i> Generate Report</a> 
                            </li>
                            @endif
                            @endif

                            @if(App\Helpers\SitePermissionCheck::isPermitted('background-master.create'))
                            @if(App\Helpers\RolePermissionCheck::isPermitted('background-master.create'))
                            <li style="float: right;">
                                <button onclick="location.href = '{{ route('background-master.create') }}'" class="btn btn-theme" type="button" id="addtemplate"><i class="fa fa-plus"></i> Add Background Template</button>    
                            </li>
                            @endif
                            @endif
                            
                        </ul>
                        <table id="example" class="table table-hover display" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Background Template Name</th>
                                    <th>Width (mm)</th>
                                    <th>Height (mm)</th>
                                    <th>Action</th>
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
    var bg_template = $('#example').dataTable({
        'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "sAjaxSource": "{{URL::route('background-master.index',['status'=>1])}}",
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
        { "mData": "background_name",sWidth: "20%",bSortable: true},
        
        { 
            "mData": "width",
            sWidth: "10%",
            mRender: function(v, t, o) {
                return o['width'];
            }
        },

        { 
            "mData": "height",
            sWidth: "10%",
            mRender: function(v, t, o) {
                return o['height'];
            }
        },

        {
            mData: null,
            bSortable: false,
            sWidth: "20%",
            sClass: "text-center",
            mRender: function(v, t, o) {
    
                var editurl = '{{ route("background-master.edit", ":id") }}';
                editurl = editurl.replace(':id',o['id']);

                 var id = o['id'];
    
                var act_html = "<div class='btn-group'>"
                                
                                +"@if(App\Helpers\SitePermissionCheck::isPermitted('background-master.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('background-master.edit'))<a href='"+editurl+"' data-toggle='tooltip' title='Edit background Template' data-placement='top' class='editrow'><i class='fa fa-edit fa-lg green'></i></a> @endif @endif"

                                +"@if(App\Helpers\SitePermissionCheck::isPermitted('background-master.showDetail')) @if(App\Helpers\RolePermissionCheck::isPermitted('background-master.showDetail'))<a href='javascript:void(0);' onclick='openImage("+id+")' data-toggle='tooltip' title='Edit background Template' data-placement='top' class='editrow'><i class='fa fa-image fa-lg pink'></i></a>@endif @endif"

                                // +"@if(App\Helpers\SitePermissionCheck::isPermitted('background-master.showDetail')) @if(App\Helpers\RolePermissionCheck::isPermitted('background-master.showDetail'))<a href='"+showDetailurl+"' data-toggle='tooltip' title='Show background Template' data-placement='top' class='editrow'><i class='fa fa-image fa-lg pink'></i></a> @endif @endif"
    
                                +"@if(App\Helpers\SitePermissionCheck::isPermitted('background-master.show')) @if(App\Helpers\RolePermissionCheck::isPermitted('background-master.show'))<a href='javascript:void(0);' onclick='preview("+ o['id'] +")' data-toggle='tooltip' title='Preview' data-placement='top' class='editrow bgTempletePreview'><i class='fa fa-fw fa-eye blue'></i></a>@endif @endif"
    
                                +"</div>"
                return act_html;
            },
            
                "createdRow": function( row, data, dataIndex ) {
                    if(data['status'] == 'Active'){
                        $(row).addClass( 'active-student' );
                    }else{
                        $(row).addClass( 'inactive-student' );
                    }
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
    
    // send Ajax Request to featch data and show model for background template preview
    function preview(id) {
        var token = "{{ csrf_token() }}";
        var id = id;
            $.ajax({
                url: "{{ URL::route('background-master.show') }}",
                type: 'get',
                dataType:'html',
                data:{
                    id:id,
                    _token:token
                },
                success: function(data) {
                    var resp = JSON.parse(data);
                    var previewData = resp.resp;
                    <?php
                        $domain = \Request::getHost();
                        $subdomain = explode('.', $domain);
                    ?>
                    $('#modalBody').empty();
                    if(resp.get_file_aws_local_flag['file_aws_local'] == '1'){
                        $('#modalBody').append($('<img />').attr('src', "<?= Config::get('constant.amazone_path').$subdomain[0]?>"+'/backend/canvas/bg_images/'+ previewData.image_path));
                    }
                    else{
                        $('#modalBody').append($('<img />').attr('src', "<?= Config::get('constant.local_base_path').$subdomain[0]?>"+'/backend/canvas/bg_images/'+ previewData.image_path));
                    }
                    $("#modalBody img").css({
                        width: previewData.width,
                        height: previewData.height
                    });
                    $('#backgroundTemplatePreview').find('.modal-body').css({
                          width:'auto', //probably not needed
                          height:'auto', //probably not needed 
                          'max-height':'100%'
                   });
                    
                    $('#backgroundTemplatePreview').modal('show');
                }
            });    
    }
    
    
    //for displaying activate user(status = 1)
    $('#success-pill').click(function(){
    
      var url="<?= URL::route('background-master.index',['status'=>1])?>";
      
      bg_template.DataTable().ajax.url(url);
      bg_template.DataTable().ajax.reload();
      $('.loader').addClass('hidden');
    });
    
    //for displaying Inactivate user(status = 1)
    $('#fail-pill').click(function(){
      var url="<?= URL::route('background-master.index',['status'=>0])?>";
      bg_template.DataTable().ajax.url(url);
      bg_template.DataTable().ajax.reload();
      $('.loader').addClass('hidden');
    }); 
    
</script>

<script type="text/javascript">
    function openImage(id) {
        var token = "{{ csrf_token() }}";
        var id = id;
            $.ajax({
                url: "{{ URL::route('background-master.showDetail') }}",
                type: 'get',
                dataType:'html',
                data:{
                    id:id,
                    _token:token
                },
                success: function(data) {
                    var resp = JSON.parse(data);
                    var previewData = resp.resp;
                    <?php
                        $domain = \Request::getHost();
                        $subdomain = explode('.', $domain);
                    ?>
                    $('#modalBodyImage').empty();
                    if(resp.get_file_aws_local_flag['file_aws_local'] == '1'){
                        $('#modalBodyImage').append($('<img />').attr('src', "<?= Config::get('constant.amazone_path').$subdomain[0]?>"+'/backend/canvas/bg_images/'+ previewData.image_path));
                    }
                    else{
                        $('#modalBodyImage').append($('<img />').attr('src', "<?= Config::get('constant.local_base_path').$subdomain[0]?>"+'/backend/canvas/bg_images/'+ previewData.image_path));
                    }
                    $("#modalBodyImage img").css({
                        width: '100%',
                        height: '100%',
                    });
                    $('#backgroundTemplateImage').find('.modal-body').css({
                          width:'auto', //probably not needed
                          height:'auto', //probably not needed 
                          'max-height':'100%'
                   });
                    
                    $('#backgroundTemplateImage').modal('show');
                }
            });

    }

</script>

@include('partials.alert')
@stop
@section('style')
    <style type="text/css">
    .modal-dialog{
    position: relative;
    display: table; /* This is important */ 
    overflow-y: auto;    
    overflow-x: auto;
    width: auto;
    min-width: 300px;   
}
    </style>
@stop
