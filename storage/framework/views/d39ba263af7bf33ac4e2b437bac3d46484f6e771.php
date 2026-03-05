<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div id="">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="page-header">
                                <i class="fa fa-file-powerpoint-o"></i></i> Pdf2pdf Report
                                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('printingreport')); ?></ol>
                            </h1>
                        </div>
                    </div>
                    <div class="">
                        <table id="example" class="table table-hover display" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Template</th>
                                    <th>Original PDF</th>
                                    <th>No. of Pages</th>
                                    <th>Processed PDF</th>
                                    <th>Page</th>
                                    <th>Date</th>
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
        "sAjaxSource": "<?php echo e(URL::route('pdf2pdf-report.index')); ?>",
        "aaSorting": [
            [0, "desc"]
        ],
        "oLanguage": {
          "sLengthMenu": "Display _MENU_ records"
        },
    
        "aoColumns": [
        
        { 
            "mData": "id",
            sWidth: "2%",
            mRender: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { "mData": "template_name",sWidth: "20%",bSortable: true},
        
        {  
            "mData":function (o,t,v){
                <?php
                    $domain = \Request::getHost();
                    $subdomain = explode('.', $domain);
                ?>
				var source_file = o.source_file;	
				var nameArr = source_file.split(',');    
				var count_source_file=nameArr.length
				var record_unique_id = o.record_unique_id;
				var pdf_page = o.pdf_page;	                    
				if(pdf_page == 'Multi'){
					original='';
					for (q = 0; q < count_source_file; q++) {
						original +="<a href='https://<?=$subdomain[0];?>.seqrdoc.com/<?=$subdomain[0];?>/uploads/data/multi/"+record_unique_id+"/"+nameArr[q]+"' target='_blank' title='Click to download' class='btn btn-success'>"+nameArr[q]+"</a><br />";
					}
				}else{ 
					original= "<a href='https://<?=$subdomain[0];?>.seqrdoc.com/<?=$subdomain[0];?>/uploads/data/"+source_file+"' target='_blank' title='Click to download' class='btn btn-success'>"+source_file+"</a>";
				}
				return original;
                //console.log(o);
                //var path = path+o.template_id+'/'+ o.excel_sheet_name;
                //return '<a data-toggle="tooltip" data-placement="right" target="_blank" download title="Please click to download excel file" class="btn btn-success" href="'+ path +'">' + o.excel_sheet_name + '</a>';
                
            },
            sWidth: "20%",
            bSortable: false,
            
        },
		{ "mData": "total_records",sWidth: "20%",bSortable: false},
        { 
            "mData":function (o,t,v){
                <?php
                    $domain = \Request::getHost();
                    $subdomain = explode('.', $domain);
                ?>
				var template_name = o.template_name;	
				var source_file = o.source_file;	
				var nameArr = source_file.split(',');    
				var count_source_file=nameArr.length
				var record_unique_id = o.record_unique_id;
				var pdf_page = o.pdf_page;	                    
				if(pdf_page == 'Multi'){
					original='';
					for (q = 0; q < count_source_file; q++) {
						original +="<a href='https://<?=$subdomain[0];?>.seqrdoc.com/<?=$subdomain[0];?>/documents/"+template_name+"/"+record_unique_id+"/"+nameArr[q]+"' target='_blank' title='Click to download' class='btn btn-success'>"+nameArr[q]+"</a><br />";
					}
				}else{ 
					original= "<a href='https://<?=$subdomain[0];?>.seqrdoc.com/<?=$subdomain[0];?>/documents/"+template_name+"/"+record_unique_id+"/"+source_file+"' target='_blank' title='Click to download' class='btn btn-success'>"+source_file+"</a>";
				}
				return original;                
            },
            sWidth: "20%",
            bSortable: false,
        },
        { "mData": "pdf_page",sWidth: "20%",bSortable: false},
		{"mData": "created_at",sWidth: "20%",bSortable: true,
			mRender: function(v, t, o) { 
            	if(o.created_at != null){
					var date = moment(v).format('DD-MM-YYYY h:mm:ss');
					//var date = moment(v).format('DD-MM-YYYY');
					return date;
				}else{
					return '';
				}
            },
		},
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

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/pdf2pdfReport/index.blade.php ENDPATH**/ ?>