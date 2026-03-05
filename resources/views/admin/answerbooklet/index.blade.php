@extends('admin.layout.layout')
@section('content')
    <div class="container">
    	<div class="container-fluid">
    		<div class="row">
    			<div class="col-lg-12">
    				<h1 class="page-header"><i class="fa fa-fw fa-money"></i> Answer Booklet
    				<!-- <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('paymentgateway') }}</ol> -->
    				</h1>
    			</div>
    		</div>
    	<div class="">
    			<ul class="nav nav-pills" id="pills-filter">
    		  	<!-- <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active PG </a></li>
    			  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive PG</a></li>
   -->
    				<li style="float: right;">
    					<button class="btn btn-theme" id="addUser" onclick="AddAnswerBooklet_data()"><i class="fa fa-plus"></i> Create Answer Booklet</button>	
    				</li>
         
    			</ul>
    			<table id="example" class="table table-hover display" cellspacing="0" width="100%">
    				<thead>
    					<tr>
                 <th>#</th>
    						 <th>Batch No</th>
                 <th>Prefix Word</th>
                 <th>Booklet Size</th>
                 <th>Starting Serial No</th>
                 <th>Ending Serial No</th>
                 <th>Quantity</th>
                 <th>Created At</th>
    						 <th>Action</th>
                 <!-- <th></th> -->
    					</tr>
    				</thead>
    				<tfoot>
    				</tfoot>
    			</table>
    		</div>
    	</div>
    </div>

 @include('admin.answerbooklet.model')

@stop
@section('script')
<script type="text/javascript">
  
function exportExcel(batchId){


  var token="{{ csrf_token() }}";
     var newForm = jQuery('<form>', {
       'action': '<?=route('answerbooklet.excelreport')?>',
       'method':'POST',
       'target': '_top'
      }).append(jQuery('<input>', {
       'name': 'batchId',
       'value': batchId,
       'type': 'hidden'
      })).append(jQuery('<input>', {
       'name': '_token',
       'value': token,
       'type': 'hidden'
      }));
     $(document.body).append(newForm);
      //console.log(newForm);
      newForm.submit();



}

  function AddAnswerBooklet_data()
  {
      $(".update_save").hide();
      $('.save').show();
      $('#addUsr').modal('show');
      $('.save').click(function(event) {

         $('.save').attr('disabled','disabled');  
         event.preventDefault();
         var create_path="<?= URL::route('answerbooklet.store') ?>";
         var token="{{ csrf_token() }}";
         var method_type="post";

         $("#UserData").ajaxSubmit({
               url:create_path,
               type:method_type,
               data:{'_token':token},
               beforeSubmit:function(){
                 $("#addUsr").find('span').text('').end();
                 $(".loadsave").addClass('fa fa-spinner fa-spin');
               }, 
               success:function(data)
               {
                 if(data.success==true)
                 {
                    console.log(data);
                    // generateBatches(data.batch_id);
                    generateBatches(data.batch_id, data.startSerialNo, data.quantity, batchSize = 100)
                    // $('#addUsr').modal('hide');
                    // $('#UserData').trigger("reset");
                    // toastr.success(data.message);
                    // oTable.ajax.reload();
                    // $('.save').removeAttr('disabled');
                    // $(".loadsave").removeClass('fa fa-spinner fa-spin');
                 }else{
                  toastr.error(data.message);
                    $('.save').removeAttr('disabled');
                    $(".loadsave").removeClass('fa fa-spinner fa-spin');
                 }
               },
               error:function(resobj)
               {
                 $('.save').removeAttr('disabled');  
                 $.each(resobj.responseJSON.errors, function(k,v){
                     $('#'+k+'_error').text(v);
                  });
                 $(".loadsave").removeClass('fa fa-spinner fa-spin');
               },
         });      
      });
  }


  function generateBatches(batch_id, startSerialNo, quantity, batchSize = 50) {
    var actionUrl="<?= URL::route('answerbooklet.storeBatchWise') ?>";
    let token = "<?= csrf_token() ?>";

    let totalBatches = Math.ceil(quantity / batchSize); // Calculate total batches
    let currentBatch = 1;
    console.log(batch_id)
    console.log(startSerialNo)
    console.log(quantity)
    console.log(totalBatches)
    function processNextBatch() {
      if (currentBatch > totalBatches) {
            $("#customLoader").hide();
            $("#couponModal").modal("hide");
            // notification("success", "All batches generated successfully!");
            toastr.success("All batches generated successfully!");
            oTable.ajax.reload();
            $('#addUsr').modal('hide');
            $('#UserData').trigger("reset");
            $('.save').removeAttr('disabled');
            $(".loadsave").removeClass('fa fa-spinner fa-spin');
            return;
          }
          
          let batchStart = parseInt(startSerialNo)  + (parseInt(currentBatch) - 1) * parseInt(batchSize);
          let batchQuantity = Math.min(parseInt(batchSize), parseInt(quantity) - (parseInt(currentBatch) - 1) * parseInt(batchSize));
          
        console.log(totalBatches)
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                _token: token,
                batch_id: batch_id,
                startSerialNo: batchStart,
                quantity: batchQuantity
            },
            beforeSend: function () {
                $(".customLoader-text").html(`Processing batch ${currentBatch} of ${totalBatches}...`);
                $("#customLoader").show();
                $(".loadsave").addClass('fa fa-spinner fa-spin');
            },
            success: function (data) {
                // console.log(data);
                let response = data;
                // let response = JSON.parse(data);
                if (response.status === "1") {
                    currentBatch++;
                    processNextBatch(); // Call next batch
                } else {
                  toastr.error(response.message);
                    // notification("error", response.message);
                }
            },
            error: function () {
                toastr.error("Batch processing error.");
                // notification("error", "Batch processing error.");
            }
        });
    }

    processNextBatch(); // Start first batch
}



     // datatable	 
   var oTable = $('#example').DataTable( {
       'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
       "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [7, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('answerbooklet.index',['status'=>1])?>",
        "aoColumns":[
          {mData: "rownum", bSortable:false},
          {mData: "id",bSortable:true},
          {mData: "prefix",bSortable:true},
          {mData: "booklet_size",bSortable:true},
          {mData: "start_serial_no",bSortable:true},          
          {mData: "end_serial_no",bSortable:true},
          {mData: "quantity",bSortable:true},
          {mData: "created_at",bSortable:true},
          {
            mData:"id",
            bSortable:false,

            mRender:function(v, t, o){
                var act_html;
        
                     act_html ='<a onclick="exportExcel('+o['id']+')"><i class="fa fa-download fa-lg green"></i></a>';
                     
                     return act_html;
            },
          },
          // {mData: "created_at",bSortable:false,bVisible:false},  
      ],
    });

   oTable.on('draw', function(event) {
    $(".loader").addClass('hidden');
   });

 

// clear Admin model data
$('.clear_model').on('hidden.bs.modal', function () {
    $(this).find("input,textarea,select").val('').end();
    $(this).find('span').text('').end();
    $(".save").removeAttr('disabled');
});
// clear Admin model data 


  $('#booklet_size').on('input', function () {
        this.value = this.value.match(/^\d+\.?\d{0,2}/);
  });

  $('#start_serial_no').on('input', function () {
      this.value = this.value.match(/^\d+\.?\d{0,2}/);
  });

  $('#quantity').on('input', function () {
        this.value = this.value.match(/^\d+\.?\d{0,2}/);
  });


</script>

@stop
@section('style')

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
@stop