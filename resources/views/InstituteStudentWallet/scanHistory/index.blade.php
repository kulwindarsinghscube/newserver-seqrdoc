@extends('webapp.layouts.layout')
@section('content')
	<h1 class="page-header"><i class="fa fa-envira"></i> Scan History
<span style="font-family:roboto;font-weight:500;font-size:14px;color:#777;display:block;margin:10px 0;">
A consolidated list of scans made from various devices.
</span>
</h1>
<div class="">
	<div class="">
		<ul class="nav nav-pills" id="addUser">
		  <li class="active"><a id="web-pill" data-toggle="pill" href="#webapp"><i class="fa fa-fw fa-lg fa-desktop"></i> WebApp </a></li>
		  <li><a id="android-pill" data-toggle="pill" href="#android"><i class="fa fa-fw fa-lg fa-android"></i> Android</a></li>
		  <li><a id="iphone-pill" data-toggle="pill" href="#iphone"><i class="fa fa-fw fa-lg fa-apple"></i> iPhone</a></li>
		</ul>

		<table id="example" class="table table-hover display" cellspacing="0" width="100%">
			<thead>
				<tr class="text-center">
					<th>#</th>
					<th>Date</th>
					<!-- <th>Scanner</th> -->
					<th>Scanned Data</th>
					<th>Result</th>
				</tr>
			</thead>
			<tbody></tbody>
			<tfoot></tfoot>
		</table>
	</div>
</div>
<div class="modal zoomIn animated" id="info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel" style="display: inline-block;">Student's info</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-9">
					  	<div class="row">
							<div class="col-xs-5"><label for="info1">Document UID</label></div>
							<div class="col-xs-1"><label for="info1">:</label></div>
							<div class="col-xs-6" id="info1"></div>
						</div>
						
						<!-- <div class="row">
							<div class="col-xs-5"><label for="info3">Document Link</label></div>
							<div class="col-xs-1"><label for="info3">:</label></div>
							<div class="col-xs-6"><a target="_blank" id="info3" href=""></a></div>
						</div> -->
						<div class="row">
							<div class="col-xs-5"><label for="info4">Status</label></div>
							<div class="col-xs-1"><label for="info4">:</label></div>
							<div class="col-xs-6" id="info4"></div>
						</div>
					</div>
					<div class="col-xs-3">
					<div class="col-xs-12" id="info5"></div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>
@stop

@section('script')
<script type="text/javascript">
    //datatable for scan history
    var oTable = $('#example').dataTable({
        'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "sAjaxSource": "{{URL::route('scan-history.index',['device_type'=>'WebApp'])}}",
        "aaSorting": [
            [0, "desc"]
        ],
        "oLanguage": {
          "sLengthMenu": "Display _MENU_ records"
        },
    
        "aoColumns": [
        
        { 
            "mData": "serial_no",
            sWidth: "6%",
            bSortable: false,
            mRender: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { "mData": "date_time",sWidth: "25%",bSortable: false},
        
        // {  
        //     "mData":function (o,t,v){
        //         if (o.device_type=="WebApp") {
        //         	return '<i class="fa fa-fw fa-2x fa-desktop"></i>';
        //         }
        //         else if(o.device_type=="android")
        //         {
        //         	return '<i class="fa fa-fw fa-2x fa-android green"></i>';
        //         }
        //         else if(o.device_type=="iOS")
        //         {
        //         	return '<i class="fa fa-fw fa-2x fa-apple"></i>';
        //         }
                
        //     },
        //     sWidth: "20%",
        //     bSortable: false,
            
        // },
        { "mData": "scanned_data",sWidth: "20%",bSortable: false},
        {  
            "mData":function (o,t,v){
                if (o.scan_result==0) {
                	return '<span style="cursor:not-allowed;color: #fff;" class="btn btn-theme2">Inactive QR</span>';;
                }
                else if(o.scan_result==1)
                {
                	return '<span style="cursor: pointer; color: #fff;" class="btn btn-theme" id="infoData">Success</span>';
                }
                else if(o.scan_result==2)
                {
                	return '<span style="cursor:not-allowed;color: #fff;" class="btn btn-theme2">Regular QR</span>';
                }
                
            },
            sWidth: "20%",
            bSortable: false,
            
        },
        ],
        
        fnPreDrawCallback : function() { 
            $("#spin").show();
    
        },
        fnDrawCallback : function (oSettings) {
            $("#spin").hide();
        }
    }); 

    $("#web-pill").click(function(){
    	var url="<?= URL::route('scan-history.index',['device_type'=>'WebApp'])?>";
		oTable.DataTable().ajax.url(url);
      	oTable.DataTable().ajax.reload();
      	$('.loader').addClass('hidden');
	});
	
	$("#android-pill").click(function(){
		var url="<?= URL::route('scan-history.index',['device_type'=>'android'])?>";
		oTable.DataTable().ajax.url(url);
      	oTable.DataTable().ajax.reload();
      	$('.loader').addClass('hidden');
	});
	
	$("#iphone-pill").click(function(){
		var url="<?= URL::route('scan-history.index',['device_type'=>'iOS'])?>";
		oTable.DataTable().ajax.url(url);
      	oTable.DataTable().ajax.reload();
      	$('.loader').addClass('hidden');
	});

	oTable.on('click', '#infoData', function () {


		 var token = "{{ csrf_token() }}";
		var key = $(this).closest("tr").find('td:eq(2)').text();
		$.ajax({
                url: "{{ URL::route('scanningHistory.show') }}",
                type: 'get',
                dataType:'json',
                data:{
                    key:key,
                    _token:token
                },
                success: function(data) {

                	
					$('#info').modal('show');
					resp = data.resp;
                	if(resp != null){


						var status = resp['status'] ? 'Active' : 'Inactive';
						var path   = resp['path'];
						// var src ="{{ asset('backend/canvas/images')}} "+'/'+resp['0']['path'];

						$('#info1').html(resp['serial_no']);
						$('#info2').html(resp['student_name']);
						$('#info4').html(status);
						// $('#info5').html("<img class='img-responsive img-thumbnail'");
						$('#info5').html('<img src="'+data.qr_path+'/'+path+'" class= "img-responsive img-thumbnail" "/>');
					
						$('#info6').html(resp['key']);

						var row = $('#info3').parent().parent();
						if (resp['status'] != "1") {

							
							row.hide();
						}else{

							row.show();
							$("a#info3").attr("href",data.pdf_path+'/'+resp['certificate_filename']);
							$('#info3').html(resp['certificate_filename']);
						}
                	}
                }
                
            });    
	});

    
</script>
@stop

@section('style')
<style type="text/css">
#example td{
	word-break: break-all;
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
</style>
@stop