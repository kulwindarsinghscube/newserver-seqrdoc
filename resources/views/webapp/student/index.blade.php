@extends('webapp.layouts.layout')
@section('content')
<h1 class="page-header"><i class="fa fa-users"></i>  Students
<span style="font-family:roboto;font-weight:500;font-size:14px;color:#777;display:block;margin:10px 0;">
All students that you have made payment to acess hidden data.
</span>
</h1>
<div class="">
	<div class="">
		<table id="example" class="table table-hover display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>#</th>
					 <th></th>
					<th>Student</th>
					<th>Subscription Date/Time</th>
					<th>View Data</th>
          <th></th>
				</tr>
			</thead>
			<tbody></tbody>
			<tfoot>
			</tfoot>
		</table>
	</div>
</div>
	<div class="modal fade clear_model" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="width: 900px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel" style="display: inline-block;">Student's info</h4>
			</div>
			<div class="modal-body">
				<div class="ajaxResponse"><div class="panel panel-info"><div class="panel-heading"><b>Student information</b></div><div class="panel-body"><div class="col-xs-6">
									  	<div class="row">
											<div class="col-xs-5"><label for="info1">Serial No.</label></div>
											<div class="col-xs-1"><label for="info1">:</label></div>
											<span id="Serial_no"></span>

										</div>
										<div class="row">
											<div class="col-xs-5"><label for="info3">Certificate Filename</label></div>
											<div class="col-xs-1"><label for="info3">:</label></div>
											<span ><a target="_blank"  id="show_new_page"></a></span>
										</div><hr>
										<div class="row">
											<div class="col-xs-12">
												<iframe width="810" height="780" id="show_pdf"></iframe>
											</div>
										</div>
									</div></div></div></div>
				
			</div>
		</div>
	</div>
</div>
@stop
@section('script')
<script type="text/javascript">

  $(document).ready(function() {
  	
   var oTable = $('#example').DataTable( {
      "aaSorting": [
        [5, "desc"]
        ],
		"bInfo":false,
		"bServerSide": true,
		"sAjaxSource":"<?= URL::route('studentSubscribed.index',['status'=>1])?>",
		"language": {
				"emptyTable":     "<div class='alert alert-info alert-dismissable'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a><i class='fa fa-frown-o'></i> <b></b> You have not made payment for any student yet.</div>",
				"zeroRecords":    "<div class='alert alert-info alert-dismissable'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a><i class='fa fa-frown-o'></i> <b></b> You have not made payment for any student yet.</div>",
		},
	   "aoColumns":[
          {mData: "rownum", bSortable:false},
          { mData: "path",
            bSortable:false,
            mRender:function(v,t,o)
            {
               var qrimage=null;
               if(v){
                
                   qrimage='<img class="img-responsive img-thumbnail" width="70px" height="70px" alt="NotFound" src="<?= Config::get('constant.qrcode_show_webapp') ?>/'+v+'" >';
                }
                
               return qrimage;
            },
          },
          {mData: "fullname", bSortable:true},
          {
          	mData: "created_at",
          	bSortable:true,
          	mRender:function(v){

          		  return moment(v).format('DD MMM YYYY hh:mm a');
          	}
            
          },
          {
          	mData: "student_key",
          	bSortable:false,
          	mRender:function(v,t,o){
              var button=null;
              var button ='<button class="btn btn-theme infoData" data-key="'+v+'" style="color:#fff;"><i class="fa fa-fw fa-info-circle"></i> View Details</button>';

              return button;
          	}

          },
          {mData: "updated_at", bSortable:false,bVisible:false},

          ],
    });
   	oTable.on('click', '.infoData', function () {
      
	     	$key_id = $(this).data('key');
        var url_path="{{ URL::route('studentSubscribed.getdata') }}";
        var token="{{ csrf_token() }}";
        var method_type="post";
        $.ajax({
              url:url_path,
              data:{'_token':token,'key':$key_id},
              type:method_type,

              success:function(response){

                $("#Serial_no").text(response.serial_no);
                $("#show_new_page").text(response.certificate_filename);
                $("#show_new_page").attr('href', '<?= Config::get('constant.show_pdf') ?>/'+response.certificate_filename+' ');
                $("#show_pdf").attr('src','<?= Config::get('constant.show_pdf') ?>/'+response.certificate_filename+' ');

                $("#addUsr").modal('show');
              }
 
        });
	});
  });

</script>
@stop