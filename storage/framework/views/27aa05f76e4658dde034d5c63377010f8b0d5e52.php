<?php $__env->startSection('content'); ?>
<?php
    $domain = \Request::getHost();
    $subdomain = explode('.', $domain);
?>
<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div class="modal fade" id="generateQRCode" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            
                            <?php if($subdomain[0] == 'imt') { ?>
                                <h4 class="modal-title">Document Info</h4>
                            <?php } else { ?>
                            <h4 class="modal-title">Student Info</h4>
                            <?php }  ?>
                            
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
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px"><?php echo e(Breadcrumbs::render('certificatemanagement')); ?></ol>
				<i class="fa fa-info-circle iconModalCss" title="User Manual" id="certificateManagementClick"></i>
                            </h1>
                        </div>
                    </div>
                    <div class="">
                        <ul class="nav nav-pills" id="pills-filter">
                            <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Certificate </a></li>
                            <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Certificate</a></li>
                            <?php if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.excelExport')): ?>
                            <?php if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.excelExport')): ?>
                            <li style="float: right;">
                                <a href="<?php echo e(route('certificateManagement.excelExport')); ?>" style="background: #0052CC;padding-top:5px; color: #fff;border-radius: 2px;box-shadow: 2px 2px 5px #999;border: 1px solid transparent;height: 35px;" id="report" ><i class="fa fa-file"></i> Generate Report</a> 
                            </li>
                            <?php endif; ?>
                            <?php endif; ?>
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
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
    //datatable for Certificate Management
    var token = "<?php echo e(csrf_token()); ?>";
    var oTable = $('#example').dataTable({
        'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "sAjaxSource": "<?php echo e(URL::route('certificateManagement.index',['status'=>1])); ?>",
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
            mRender: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { "mData": "serial_no",sWidth: "20%",bSortable: true},
        { "mData": "certificate_filename",sWidth: "20%",bSortable: true},
        { "mData": "actual_template_name",sWidth: "20%",bSortable: true,
            mRender: function(v, t, o) {
                if(o['template_id']==100){
                    var actual_template_name='Custom Template';
                }else{
                     var actual_template_name=o['actual_template_name'];
                }
               
                return actual_template_name;
            },
        },
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
                                
                                +"<?php if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.generateQRCode')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.generateQRCode')): ?><a href='javascript:void(0);' onclick='generateQRCode("+ o['id'] +")' data-toggle='tooltip' title='Generate QR Code' data-placement='top' class='editrow'><i class='fa fa-info-circle fa-lg blue'></i></a><?php endif; ?> <?php endif; ?>"

                                +"<?php if(App\Helpers\SitePermissionCheck::isPermitted('certificateManagement.update')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('certificateManagement.update')): ?> <label class='switch'><input type='checkbox' id ='action' data-id = '"+o.id+"' data-status='"+ o.status +"' ><span class='slider round'></label><?php endif; ?> <?php endif; ?>"
    
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
                            url: "<?php echo e(URL::route('certificateManagement.update')); ?>",
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
        
            $.ajax({
                url: "<?php echo e(URL::route('certificateManagement.generateQRCode')); ?>",
                type: 'post',
                dataType:'html',
                data:{
                    id:id,
                    _token:token
                },
                success: function(data) {
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
    
    
    //for displaying activate user(status = 1)
    $('#success-pill').click(function(){
    
      var url="<?= URL::route('certificateManagement.index',['status'=>1])?>";
      
      oTable.DataTable().ajax.url(url);
      oTable.DataTable().ajax.reload();
      $('.loader').addClass('hidden');
    });
    
    //for displaying Inactivate user(status = 1)
    $('#fail-pill').click(function(){
      var url="<?= URL::route('certificateManagement.index',['status'=>0])?>";
      oTable.DataTable().ajax.url(url);
      oTable.DataTable().ajax.reload();
      $('.loader').addClass('hidden');
    }); 
    
</script>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/certificateManagement/index.blade.php ENDPATH**/ ?>