<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="col-xs-12">
            <div class="clearfix">
                <div id="">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"><i class="fa fa-certificate"></i>Intifacc Mordern Technology
                                    <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('imtcertificate')); ?></ol>
                                </h1>
                            </div>
                        </div>
                        <ul class="nav nav-pills" id="pills-filter">
                            <li style="float: right;">
                                <a class="btn btn-theme" href="<?php echo e(route('imt.create')); ?>" style="background-color: #0052cc;color:white;"><i class="fa fa-plus"></i> Create New Certificate</a>
                            </li>
                        </ul>
                        <table id="imt-certificate-table" class="table table-hover display" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Date of Birth</th>
                                    <th>Place of Birth </th>
                                    <th>ID Card Number</th>
                                    <th>Sex</th>
                                    <th>Marital Status</th>
                                    <th>Place of Residence</th>
                                    <th>Name of Mother</th>
                                    
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset('backend/js/moment.min.js')); ?>"></script>
<script type="text/javascript">
	$token = '<?= csrf_token()?>';
	//datatable for index page 
	var oTable = $('#imt-certificate-table').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [0, "desc"]
        ],
        //index page url calls
        "sAjaxSource":"<?= URL::route('imt.index')?>",
        //columns that displaying
        "aoColumns":[

		{   mData: "rownum", 
            bSortable:false
        },
        {   mData: "name",
            sWidth: "20%",
            bSortable: true
        },
		{   mData: "dob",
            sWidth: "20%",
            bSortable: true,
			mRender: function(v, t, o) {
            	var date = moment(v).format('DD-MM-YYYY');
				return date;
            },
		},
		{   mData: "birth_place",
            sWidth: "20%",
            bSortable: true
        },
		{   mData: "id_number",
            sWidth: "20%",
            bSortable: true
        },
		{   mData: "sex",
            sWidth: "20%",
            bSortable: true
        },
		{   mData: "marital_status",
            sWidth: "20%",
            bSortable: true
        },
		{   mData: "address",
            sWidth: "20%",
            bSortable: true
        },
		{   mData: "mother_name",
            sWidth: "20%",
            bSortable: true
        },
		// { 
        //     mData: 'id',
        //     bSortable: false,
        //     sWidth: "30%",
        //     sClass: "text-center",
        //     mRender: function(v, t, o) {

        //     	var buttons = '';
        //     	// buttons += '<span data-toggle="tooltip" title="Edit" id="editData" class="" data-id="'+o['id']+'"><i class="fa fa-edit fa-lg green"></i></span> &nbsp;&nbsp;';
        //     	buttons += '<span data-toggle="tooltip" title="Delete" id="delData" class="" data-id="'+o['id']+'"><i class="fa fa-trash fa-lg red"></i></span> &nbsp;&nbsp;';
		// 		return buttons;
        //     },
         		   	
        // },
	]
		// "createdRow": function( row, data, dataIndex ) {

		// 	if(data['status'] == 'Active'){
		// 		$(row).addClass( 'active-student' );
		// 	}else{
		// 		$(row).addClass( 'inactive-student' );
		// 	}
		// }
	});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/imt/index.blade.php ENDPATH**/ ?>