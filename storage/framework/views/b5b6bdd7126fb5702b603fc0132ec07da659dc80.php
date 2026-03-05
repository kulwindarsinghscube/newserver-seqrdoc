<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div id="">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header">
                                <i class="fa fa-file-powerpoint-o"></i></i>Printer Report
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('printingreport')); ?></ol>
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
    //datatable for background master
    var oTable = $('#example').dataTable({
        'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "sAjaxSource": "<?php echo e(URL::route('sandboxing.printingReport.index')); ?>",
        "aaSorting": [
            [5, "desc"]
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
                        var path =  "<?= Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.sandbox')?>"+"/"
                    }
                }
                else{
                    if(flag == '1'){
                        var path =  "<?= Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.template')?>"+"/"
                    }
                    else{
                        var path =  "<?= Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.template')?>"+"/"
                    }
                }

                var path = path+'sandbox/'+o.template_id+'/'+ o.excel_sheet_name;
                return '<a data-toggle="tooltip" data-placement="right" target="_blank" download title="Please click to download excel file" class="btn btn-success" href="'+ path +'">' + o.excel_sheet_name + '</a>';
                
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/sandboxing/printingReport.blade.php ENDPATH**/ ?>