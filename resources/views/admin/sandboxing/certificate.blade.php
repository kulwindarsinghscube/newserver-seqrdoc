@extends('admin.layout.layout')
@section('content')
<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div class="modal fade" id="generateQRCode" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Student Info</h4>
                        </div>
                        <div class="modal-body" id="modalBody">

                        </div>
                        <div class="modal-footer">
                        
                            <a href="" class="btn btn-primary" id="QrCode" download style="margin:auto;display:block;"> Download QR Code</a>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div id="">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header">
                                <i class="fa fa-fw fa fa-file-o"></i>Certificate Management
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px">{{ Breadcrumbs::render('certificatemanagement') }}</ol>
                                <i class="fa fa-info-circle iconModalCss" title="User Manual" id="certificateManagementSandboxingClick"></i>
                            </h1>
                        </div>
                    </div>
                    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                        
                          <!-- Modal content-->
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title">Download pdf</h4>
                            </div>
                            <div class="modal-body" id="downloadPdf">
                              
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          
                        </div>
                    </div>
                    <div class="">
                        <ul class="nav nav-pills" id="pills-filter">
                            <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Certificate </a></li>
                            <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Certificate</a></li>
                            
                        </ul>

                        <table id="example" class="table table-hover display" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Serial No.</th>
                                    <th>Certificate Filename</th>
                                    <th>Template Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <button class="btn btn-theme" id="RePrintSelected" style="display: none;" > RePrint Selected</button>   
            </div>
        </div>
    </div>
</div>
@stop
@section('script')
<script type="text/javascript">
    //datatable for Certificate Management
    var token = "{{ csrf_token() }}";
    var oTable = $('#example').dataTable({
        'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "sAjaxSource": "{{URL::route('sandboxing.certificate.index',['status'=>1])}}",
        "aaSorting": [
            [4, "desc"]
        ],
        "oLanguage": {
          "sLengthMenu": "Display _MENU_ records"
        },
    
        "aoColumns": [
        
        { 
            "mData": "#",
            sWidth: "2%",
            mRender: function(v,t,o) {
                // console.log(data);
                // console.log(type);
                return '<div class="checkbox purple">'
                    +'<input type="checkbox" id="chk_'+o['id']+'" name="id[]" class="check text-center" value="'+o['id']+'"/>';
                    +'<label for="chk_'+o['id']+'" style="min-height: 14px; padding-left: 17px"></label>';
                    +'</div>';
                // console.log(row);
                // return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { "mData": "serial_no",sWidth: "20%",bSortable: true},
        { "mData": "certificate_filename",sWidth: "20%",bSortable: true},
        { "mData": "template_name",sWidth: "20%",bSortable: true},
        { 
            "mData": "status",
            sWidth: "20%",
            bSortable: true,
            
        },
        
        {
            mData: null,
            bSortable: false,
            sWidth: "20%",
            sClass: "text-center",
            mRender: function(v, t, o) {
                // console.log(o.id);
                var act_html = "<div class='btn-group'>"
                                
                                +"@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.generateQR')) @if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.generateQR'))<a href='javascript:void(0);' onclick='generateQRCode("+ o['id'] +")' data-toggle='tooltip' title='Generate QR Code' data-placement='top' class='editrow'><i class='fa fa-info-circle fa-lg blue'></i></a>@endif @endif"

                                +"@if(App\Helpers\SitePermissionCheck::isPermitted('sandboxing.certificate.update')) @if(App\Helpers\RolePermissionCheck::isPermitted('sandboxing.certificate.update')) <label class='switch'><input type='checkbox' id ='action' data-id = '"+o.id+"' data-status='"+ o.status +"' ><span class='slider round'></label>@endif @endif"
    
                                +"</div>"
                return act_html;
            },

        },
        { "mData": "created_at",sWidth: "20%",bSortable: true,bVisible:false},
    
        ],

        "createdRow": function( row, data, dataIndex ) 
        {
            // console.log(data['status']);
            if ( data['status'] == "Active" ) 
            {
                $(row).find('#action').attr('checked',true);
                $(row).addClass('active-student' );
            }else{
                $(row).find('#action').removeAttr('checked');
                $(row).addClass('inactive-student');
            }
        },
    
        fnPreDrawCallback : function() { 
            $("#spin").show();
    
        },
        fnDrawCallback : function (oSettings) {
            $("#spin").hide();
        }
    }); 
    oTable.on('click', '.check', function (e) {
        // alert('hi');
        var id = $('#example tbody .check:checked');
        if(id.length > 0){

            $('#RePrintSelected').css('display','');
        }else{
            $('#RePrintSelected').css('display','none');
        }
    });
    oTable.on('click','#action',function(e){
        var id = $(this).attr('data-id');
        var status = $(this).attr('data-status');

        var message = "Are you sure you want to enable certificate?";

        if (status == 'Active') {
            var message = "Are you sure you want to disable certificate?";
        }
        bootbox.confirm({
            message : message,
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
                        $.ajax({
                            url: "{{ URL::route('sandboxing.certificate.update') }}",
                            type: 'post',
                            dataType:'html',
                            data:{
                                id:id,
                                status:status,
                                _token:token
                            },
                            success: function(resp) {
                                oTable.DataTable().ajax.reload();
                            }

                        });
                    }
                    else
                    {
                        oTable.DataTable().ajax.reload();
                    }
                }
        });
    });
    
    // send Ajax Request to generate qr code and download qr code
    function generateQRCode(id) {
        
        var id = id;
        // console.log(id);
            $.ajax({
                url: "{{ URL::route('sandboxing.certificate.generateQR') }}",
                type: 'post',
                dataType:'html',
                data:{
                    id:id,
                    _token:token
                },
                success: function(data) {

                    console.log(data);
                var resp = JSON.parse(data);
                var key = resp.data.key;
                var path = resp.data.path
                $('#modalBody').empty();
                $('#modalBody').append($('<img />').attr('src',path));
                $("#QrCode").attr('href',path);
                $('#generateQRCode').modal('show');
                }
            });    
    }
    
    $('#RePrintSelected').on('click',function(){
        var id = $('#example tbody .check:checked');
        var printId = [];
        $.each(id,function(i,ele){
            printId.push($(ele).val());
        })
        $('#downloadPdf').empty();

        $('#RePrintSelected').attr('disabled','true');
        $('#RePrintSelected').html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
        // $.post(ajaxURL,
        //  {'type':'reprint','data':printId,cache: false,async:true},
        //  function(data){
        //      // console.log(data);
        //      if(data.type = 'success'){
        //          console.log('success');
        //          $('#myModal').modal('show');

        //          $('#downloadPdf').append(data.link);
        //          $('#downloadPdf').click(function(){
        //              $('#myModal').modal('hide');
        //              $('#RePrintSelected').attr('disabled','false');
        //              $('#RePrintSelected').html(' RePrint Selected');
        //          })
        //      }else{
        //          window.location.reload();
        //      }
                
        // },'json');
        var token="{{ csrf_token() }}";
        url="<?= URL::route('sandboxing.certificate.reprint')?>";
        $.ajax({
            'url':url,
            'type':'POST',
            'data':{data:printId,type:'reprint','_token':token},
            'async':true,
            'cache':false,
            'dataType':'json',
            success:function(data){
                console.log(data);
                var filePath = data.filePath;
                var dataPrint = [];
                $.each(data.eData,function(k,v){
                    dataPrint[k] = v;
                })
                    
                $('#myModal').modal('show');
                $('#RePrintSelected').removeAttr('disabled');
                // $('#RePrintSelected').html('RePrint Selected');
                
                $('#downloadPdf').append(data.link);
                $('#downloadPdf').click(function(){
                    $('#myModal').modal('hide');
                    
                    $('#RePrintSelected').html(' RePrint Selected');
                    var unlinkUrl = "<?= URL::route('sandboxing.certificate.unlink')?>";
                    $.ajax({
                        'url':unlinkUrl,
                        'type':'POST',
                        'data':{data:filePath,dataPrint:dataPrint,type:'unlink','_token':token},
                        'dataType':'json',
                        success:function(response){
                            // console.log(response);

                        }
                    })
                    
                })
            }
        })
    });
    //for displaying activate user(status = 1)
    $('#success-pill').click(function(){
    
      var url="<?= URL::route('sandboxing.certificate.index',['status'=>1])?>";
      
      oTable.DataTable().ajax.url(url);
      oTable.DataTable().ajax.reload();
      $('.loader').addClass('hidden');
    });
    
    //for displaying Inactivate user(status = 1)
    $('#fail-pill').click(function(){
      var url="<?= URL::route('sandboxing.certificate.index',['status'=>0])?>";
      oTable.DataTable().ajax.url(url);
      oTable.DataTable().ajax.reload();
      $('.loader').addClass('hidden');
    }); 
    
</script>
@stop
@section('style')
<style type="text/css">
    .modal-dialog
    {
        position: relative;
        display: table; /* This is important */ 
        overflow-y: auto;    
        overflow-x: auto;
        width: auto;
        min-width: 300px;   
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
</style>
@stop
