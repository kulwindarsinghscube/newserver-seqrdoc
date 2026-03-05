<?php $__env->startSection('content'); ?>	
<style type="text/css">
  .iconModalCss {
    right:92px !important;
  }
</style>
	<div class="container">
		<h1 class="page-header"><i class="fa fa-cc-visa blue"></i>  Transactions
		<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('paymenttransaction')); ?></ol>
    <i class="fa fa-info-circle iconModalCss" title="User Manual" id="paymentTransactionsReportClick"></i>
		</h1>
	<div class="">
		<ul class="nav nav-pills" id="addUser">
		  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Success </a></li>
		  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Failed</a></li>
		</ul>
		<table id="example" class="table table-hover" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>#</th>
					<th>Transaction ID</th>
					<th>Gateway ID</th>
					<th>Mode</th>
					<th>Amount</th>
					<th>Charges</th>
					<th>User</th>
					<th>Document No</th>
					<th>Date</th>
					<th></th>
					
				</tr>
			</thead>
			<tfoot></tfoot>
		</table>
	</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
  <script type="text/javascript">
     
          // datatable
   var oTable = $('#example').DataTable( {
	    'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
	    "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [8, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('transaction.index',['status'=>1]) ?>",
		"aoColumns":[
		{
          	mData: "rownum",
          	bSortable:false,
          	"sClass": "text-center",
          	 mRender: function (oObj) {
				   return "<span class='badge'>"+oObj+"</span>";
			   },

		   },
          {mData: "trans_id_ref",bSortable:false,"sClass": "text-center"},
          {
          	mData: "trans_id_gateway",
          	bSortable:false,
          	"sClass": "text-center",
          	mRender:function(reobj,k,o)
          	{
              var badge;
              if(o['trans_status']==1)
              {
                 badge = "<span class='label label-success'>"+reobj+"</span>";
              }
              else
              {
              	badge ="<span class='label label-danger'>"+reobj+"</span>";
              }

               return badge;
          	},
          },
          {mData: "payment_mode",bSortable:false,"sClass": "text-center"},
          {mData: "amount",bSortable:true,"sClass": "text-center"},
          {mData: "additional",bSortable:true,"sClass": "text-center"},
          {mData: "username",bSortable:false,"sClass": "text-center"},
          {mData: "serial_no",bSortable:false,"sClass": "text-center"},
          { mData: "created_at",
            bSortable:true,
            "sClass": "text-center",
           mRender:function(v)
           {
           	  return moment(v).format('DD-MMM-YY h:mm a');
           },
         },
         {mData: "updated_at",bSortable:false,bVisible:false},
          
          ],
          "createdRow": function( row, data, dataIndex ) {
						if ( data[9] == "Success" ) {
							$(row).addClass( 'success2' );
						}else{
							$(row).addClass( 'danger2' );
						}
					},
			
    
    }); 
  	oTable.on('draw.dt', function () {
		$vary = $('#addUser');
		$('#example_length').prepend($vary);
		$('#addUser').css('margin-right','20px');
		$('.loader').addClass('hidden');
	});	


  // read  inactive transaction
$('#fail-pill').click(function(){

   var url="<?= URL::route('transaction.index',['status'=>0])?>";
   oTable.ajax.url(url);
   oTable.ajax.reload();
   $('.loader').removeClass('hidden');
});
// read  active transaction
$("#success-pill").click(function(event) {

	var url="<?= URl::route('transaction.index',['status'=>1])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	$('.loader').removeClass('hidden');
});
  </script>
<?php $__env->stopSection(); ?>  	
<?php $__env->startSection('style'); ?>
<style type="text/css">

#example_length label{
  display:none;
}
.help-inline{
  color:red;
  font-weight:normal;
}

.breadcrumb{
  background:#fff;
}

.breadcrumb a{
  color:#666;
}

.breadcrumb a:hover{
  text-decoration:none;
  color:#222;
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
.success2{
  border-left:3px solid #5CB85C;
}
.danger2{
  border-left:3px solid #D9534F;
}

#example td{
  word-break: break-all;
  padding:10px;
}

.nav-pills>li.active>a, .nav-pills>li.active>a:focus{
  background:#0052CC;
  color:#fff;
  border:1px solid #0052CC;
}

.nav-pills>li.active>a:hover, .nav-pills>li>a:focus, .nav-pills>li>a:hover
{
  background:#fff;
  background:#ddd;
  border-radius:0;
  padding:10px 20px;
  color:#333;
  border-radius:2px;
  border:1px solid #ddd;
}

.nav-pills>li>a, .nav-pills>li>a
{
  background:#fff;
  color:#aaa;
  border-radius:0;
  padding:10px 20px;
  border-radius:2px;
  margin-bottom:20px;
  border:1px solid #ddd;
}

#example_length label{
  display:none;
}

.active .success{
  background:#5CB85C !important;
  border:1px solid #5CB85C !important;
  color:#fff !important;
}

.active .failed{
  background:#D9534F !important;
  border:1px solid #D9534F !important;
  color:#fff !important;
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/transaction/index.blade.php ENDPATH**/ ?>