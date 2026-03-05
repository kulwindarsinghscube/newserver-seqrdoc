@extends('admin.layout.layout')
@section('content')
	<div class="container" style="width: 1350px !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-file"></i> Import & Print</h1>	
							</div>
						</div>						
						<div class="modal fade" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="myModalLabel"></h4>
									</div>
									<div class="modal-body">									
										
									</div>
								</div>
							</div>
						</div>
                        <div class="col-xs-12">                
                            <div class="card">                                
                                <div class="card-body" style="padding:20px;">
                                <div class="row">
                                    <div class="col-xs-12 bg-primary text-center" style="padding:2px;">
                                        Total Records Available: <?php echo $total_record ?? ''; ?>
                                    </div>
                                    <div class="col-xs-12 text-center">
                                        <h4>Either select "Option 1" OR "Option 2" to import data</h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <form method="post" id="processApiForm" class="form-horizontal" novalidate="novalidate">
                                    <input type="hidden" value="<?= csrf_token()?>" name="_token">
                                    <div class="col-xs-3" style="border-right: 2px solid green;">
                                        <div class="form-group">                                            
                                            <div class="col-sm-12">
                                                <input type="radio" name="api_option" id="api_option" value="1" style="width:10%;height:16px;"> Option 1
                                            </div>
                                            <div class="col-sm-12">
                                                <label class="control-label" for="temp">District</label>
                                                <select class="form-control" id="district" name="district">
                                                    <option value="" data-tempid="">Select</option>
                                                    <option value="1" data-tempid="1"></option>
                                                    <option value="2" data-tempid="2"></option>               
                                                </select>
                                            </div>
                                        </div>                                
                                    </div>  
                                    
                                    <div class="col-xs-9">  
                                        <div class="form-group">                                            
                                            <div class="col-sm-12">
                                                <input type="radio" name="api_option" id="api_option" value="2" style="width:2%;height:16px;" checked> Option 2
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="control-label">Log Name</label>
                                                <input type="text" class="form-control" id="log_name" name="log_name">
                                            </div>                                            
                                            <div class="col-sm-3">
                                                <label class="control-label">Per Page Records</label>
                                                <input type="text" class="form-control" id="per_page" name="per_page">
                                            </div>                                            
                                            <div class="col-sm-3">
                                                <label class="control-label">Page Number</label>
                                                <input type="text" class="form-control" id="page_no" name="page_no">
                                            </div>
                                        </div>         
                                    </div>
                                    <div class="col-xs-12"><p></p></div>
                                    <div class="col-xs-12 text-center"> 
                                    <button type="submit" class="btn btn-primary" id="btn_import">Import</button> 
                                    </div>
                                    </form>
                                    <div class="col-xs-12"><p></p></div>
                                    <div class="col-xs-12"><div id="progress" style="border:1px solid gray; border-radius: 4px; display:none;"></div></div>
                                    <div class="col-xs-12"><div id="show_msg"></div></div>
                                    <div class="col-xs-12"><div id="show_records"></div></div>
                                </div>                                
                                </div>                                
                            </div>                        
                        </div>                
						<div class="">							
							<div class="col-xs-12">
								<table id="example" class="table table-hover table-bordered" cellspacing="0" width="100%">
									<caption>
                                    Total Imported: <?php echo $imported_count ?? ''; ?>&nbsp;&nbsp;
                                    Total Generated: <?php echo $generated_count ?? ''; ?>&nbsp;&nbsp;
                                    Total Completed: <?php echo $completed_count ?? ''; ?>&nbsp;&nbsp;
                                    </caption>
                                    <thead>
										<tr>
											<th>#</th>
											<th>Created</th>
											<th>Name</th>
											<th>Per Page</th>
											<th>Page No.</th>
											<th>Quantity</th>
											<th>ID Card</th>
											<th>Status</th>
											<th>Certificate</th>
											<th>Status</th>
											<th>Action</th>
										</tr>
									</thead>
									<tfoot>
									</tfoot>
								</table>
							</div>
                            
                            <div class="col-sm-12">
                                <h5>Notes:</h5>
                                <blockquote>Quantity column: count of fresh records imported. <i class="fa fa-file-excel-o fa-lg green"></i> icon to download the imported data in excel format.</blockquote>
                                <blockquote><i class="fa fa-cog fa-lg blue"></i> icon: generate secured PDF. Show progress bar. Change status from IMPORTED to GENERATED.</blockquote>
                                <blockquote><i class="fa fa-file-pdf-o fa-sm red"></i> icon: is shown when the PDF file is ready for download</blockquote>
                                <blockquote><i class="fa fa-refresh fa-lg blue"></i> icon: will be available when status = GENERATED. on click, change the status to COMPLETED.</blockquote>
                                <blockquote><i class="fa fa-eye fa-lg blue"></i> icon: will display details about the lot ie. all the currently displayed fields + user names and timestamp for
each activity.</blockquote>                                
                            </div>      
                            <div class="col-sm-12" style="height:30px;"></div>    
                            
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
    
<div class="modal fade" id="viewInfo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">			
        <div class="modal-content" style="width: 1200px;left: -440px;">
            <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="z-index:99999;opacity: 1;position: absolute;right: 0px;top: -4px;">X</button>-->
            
            <div class="modal-body" id="ajaxContent"></div>
            <div class="modal-footer">
            	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>             
        </div>
    </div>
</div>    
@stop



@section('script')
<script src="{{asset('backend/js/moment.min.js')}}"></script>
<script type="text/javascript">
	$token = '<?= csrf_token()?>';
	//datatable for index page 
	var oTable = $('#example').DataTable({
		//'dom':  "<'row'<'col-sm-3'i><'col-sm-5' p><'col-sm-1' ><'col-sm-3'f>>",
        //"bProcessing": false,
        "bProcessing": "<span class='fa-stack fa-lg'>\n\<i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\</span>&nbsp;&nbsp;&nbsp;&nbsp;Processing ...",
        "bServerSide": true,
        "autoWidth": true,
        "oLanguage": {"infoFiltered": ""},        
        "aaSorting": [
        [1, "desc"]
        ],
        //index page url calls
        "sAjaxSource":"<?= URL::route('molwa-certificate.ImportPrint',['status'=>1])?>",
        
        //columns that displaying
        "aoColumns":[

		{mData: "rownum", sWidth: "5%", bSortable:false,},
		{mData: "created_at",sWidth: "10%",bSortable: true,
			mRender: function(v, t, o) {
            	var date = moment(v).format('DD-MM-YYYY');
				return date;
            },
		},
        {mData: "name",bSortable: true,},        
		{mData: "per_page",bSortable: true,},        
		{mData: "page_no",bSortable: true,},        
		//Quantity
        {mData: 'fresh_records',
            bSortable: true,
            mRender: function(v, t, o) {
            	var buttons = o['fresh_records'];            	
            	if(o['fresh_records'] > 0){
                buttons += '&nbsp;<a data-toggle="tooltip" title="Export" href="ExportToExcel/'+o['record_unique_id']+'" target="_blank"><i class="fa fa-file-excel-o fa-lg green" style="font-size:23px;"></i></a>';
                }
				return buttons;
            },         		   
        }, 
        //ID Card        
        {mData: 'fresh_records',
            bSortable: true, sWidth: "81px",
            mRender: function(v, t, o) {
            	var buttons = '';            	
            	if(o['fresh_records'] > 0){
                if(o['idcard_status'] != "COMPLETED"){
                    buttons += '<button data-toggle="tooltip" title="Generate ID Card" class="btn_generate_id" data-id="'+o['record_unique_id']+'" data-msg="msg_'+o['record_unique_id']+'" data-rid="id_'+o['record_unique_id']+'"><i class="fa fa-cog fa-lg blue"></i></button>';
                }
                if(o['idcard_status'] == "GENERATED" || o['idcard_status'] == "COMPLETED"){
                    buttons += '<a href="{{ url::asset("/") }}secura/molwa_pdfs/ID/'+o['idcard_file']+'" class="btn" role="button" download><i class="fa fa-file-pdf-o fa-sm red" style="font-size:23px;"></i></a>';
                }
                buttons += '<div id="id_'+o['record_unique_id']+'" style="margin-top:3px; border:1px solid gray; border-radius: 2px; display:none; width:100%;"></div><div id="msg_'+o['record_unique_id']+'"></div>';
                }
				return buttons;
            },         		   
        },
		{mData: "idcard_status",bSortable: true,
            mRender: function(v, t, o) {
            	var buttons = o['idcard_status'];
                if(o['idcard_status'] == "GENERATED"){
                    buttons += '&nbsp;<button data-toggle="tooltip" title="Completed" class="btn_status" data-id="'+o['record_unique_id']+'" data-col="idcard_status" data-rid="idstatus_'+o['record_unique_id']+'"><i class="fa fa-refresh fa-lg blue" style="font-size:12px;"></i></button>';
                }
                buttons += '<div id="idstatus_'+o['record_unique_id']+'" style="display:none; width:100%;"></div>';
				return buttons;
            }, 
        },        
		//Certificate
        {mData: 'fresh_records',
            bSortable: true,
            mRender: function(v, t, o) {
            	var buttons = '';            	
            	if(o['fresh_records'] > 0){
                if(o['certificate_status'] != "COMPLETED"){
                    buttons += '<button data-toggle="tooltip" title="Generate Certificate" class="btn_generate_certificate" data-id="'+o['record_unique_id']+'" data-msg="cmsg_'+o['record_unique_id']+'" data-rid="c_'+o['record_unique_id']+'"><i class="fa fa-cog fa-lg blue"></i></button>';
                }
                if(o['certificate_status'] == "GENERATED" || o['certificate_status'] == "COMPLETED"){
                    buttons += '<a href="{{ url::asset("/") }}secura/molwa_pdfs/certificate/'+o['certificate_file']+'" class="btn" role="button" download><i class="fa fa-file-pdf-o fa-sm red" style="font-size:23px;"></i></a>';
                }
                buttons += '<div id="c_'+o['record_unique_id']+'" style="margin-top:3px; border:1px solid gray; border-radius: 2px; display:none; width:100%;"></div><div id="cmsg_'+o['record_unique_id']+'"></div>';
                }
				return buttons;
            },         		   
        },
        {mData: "certificate_status",bSortable: true,
            mRender: function(v, t, o) {
            	var buttons = o['certificate_status']; 
                if(o['certificate_status'] == "GENERATED"){
                    buttons += '&nbsp;<button data-toggle="tooltip" title="Completed" class="btn_status" data-id="'+o['record_unique_id']+'" data-col="certificate_status" data-rid="cstatus_'+o['record_unique_id']+'"><i class="fa fa-refresh fa-lg blue" style="font-size:12px;"></i></button>';
                }
                buttons += '<div id="cstatus_'+o['record_unique_id']+'" style="display:none; width:100%;"></div>';
				return buttons;
            }, 
        },        
		{mData: 'id',
            bSortable: false,
            sWidth: "5%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            	var buttons = '';  
                //buttons += '<button title="View Details" class="viewData" data-id="'+o['id']+'"><i class="fa fa-eye fa-lg blue"></i></button>';  
                buttons += '<button title="View Details" class="details-control" id="'+o['id']+'"><i class="fa fa-eye fa-lg blue"></i></button>';  
                //buttons += "<a href='javascript:void(0);' id='"+o['id']+"' class='details-control'>View</a>";  
                    
				return buttons;
            },
         		   	
        },
	],

	});

	oTable.on('draw.dt', function () {
	    //$(".loader").addClass('hidden');  
	}); 
    /*
	oTable.on('click', '#delData', function (e) {
		$id = $(this).data('id');
		bootbox.confirm("Are you sure you want to delete?",function(result){
			if(result){
				$.post('<?=route('labmaster.delete')?>',
					{'type':'delete','id':$id,'_token':$token},
					function(data){
						if(data.type == 'success'){
							toastr["success"](data.message);
							oTable.ajax.reload();
						}
						else toastr["error"](data.message);
					},'json');
			}
		});
	});
    */
    
    $('#processApiForm').validate({
		errorElement: 'span',
		errorClass: 'help-inline',
		focusInvalid: false,
		invalidHandler: function (event, validator) { //display error alert on form submit
			$('.alert-error', $('#processApiForm')).show();
		},
		highlight: function (e) {
			$(e).closest('.control-group').removeClass('info').addClass('error');
		},
		success: function (e) {
			$(e).closest('.control-group').removeClass('error').addClass('info');
			$(e).remove();
		},
		errorPlacement: function (error, element) {
			if (element.is(':checkbox') || element.is(':radio')) {
				var controls = element.closest('.controls');
				if (controls.find(':checkbox,:radio').length > 1)
					controls.append(error);
				else
					error.insertAfter(element.nextAll('.lbl:eq(0)').eq(0));
			}
			else if (element.is('.select2')) {
				error.insertAfter(element.siblings('[class*="select2-container"]:eq(0)'));
			}
			else if (element.is('.chzn-select')) {
				error.insertAfter(element.siblings('[class*="chzn-container"]:eq(0)'));
			}
			else
				error.insertAfter(element);
		},        
        rules:{            
            district: { 
             required: '#api_option[value="1"]:checked'
            },
            log_name:{required: '#api_option[value="2"]:checked'},    
            per_page:{required: '#api_option[value="2"]:checked', digits: true},    
            page_no:{required: '#api_option[value="2"]:checked', digits: true},    
        },
        messages:{            
            district:{required:'Select District'},
            log_name:{required:'Enter Log Name'},
            per_page:{required:'Enter Per Page Records', digits:'Enter only digits'},
            page_no:{required:'Enter Page Number', digits:'Enter only digits'},
        },
        submitHandler: function(form){            
            $('#progress').hide();
            $('#show_msg').hide();
            $('#show_records').hide(); 
            var token = "<?= csrf_token()?>";
            var interval = null;
            //var fd = new FormData();
            //fd.append('_token',token);
            $('#processApiForm').ajaxSubmit({
                url:'<?= route('molwa-certificate.importFfData')?>',
                type: "GET",
                dataType: "JSON",
                data:$("#processApiForm").serialize(),
                processData: false,
                contentType:false,                
                beforeSubmit:function(formData,jqform, options){
                    $('#progress').show(); 
                    $('#show_msg').show();                    
                    $('#btn_import').hide();
                    interval = setInterval(function(){
                        $.getJSON('<?=route('molwa-certificate.getProgess')?>', function(data) {
                            $("#progress").html('<div class="bar" style="width:' + data.percent + '%">' + data.percent + '%</div>');
                            $("#show_msg").html(data.show_msg);
                            if(data.percent==100){clearInterval(interval); $('#btn_import').show(); }
                        });
                    }, 1000);                    
                },clearForm:false,dataType:'json',success:function(resObj){
                    
                    if(resObj.success == true){ 
                        $('#show_records').html("Fresh Records: "+resObj.fresh_records+" | Repeat Records:"+resObj.repeat_records); 
                        $('#show_records').show(); 
                        oTable.ajax.reload();
                    }else{
                        //$('#progress').hide();                        
                    }
                }

            });
        }
    })    
    
    oTable.on('click', '.btn_generate_id', function (e) {     
        var id=$(this).data('id');    //record_unique_id
        var rid=$(this).data('rid');    
        var msg=$(this).data('msg');    
        $('#'+rid).hide();
        $('#'+msg).hide();   
        
        //var id=1;
        var token = "<?= csrf_token()?>";
        var interval = null;
        if (confirm('Are you sure?')) {
            $.ajax({
                type:'GET',
                //url:"{{ route('molwa-certificate.IdGenerate') }}" + '/' + id,
                url : "<?=route('molwa-certificate.IdGenerate')?>",
                dataType:'json',
                data:{'id':id, '_token':token},            
                beforeSend : function() {
                    $('#'+rid).show();
                    $('#'+msg).show();                   
                    interval = setInterval(function(){
                        $.getJSON('<?=route('molwa-certificate.getProgess')?>', function(data) {
                            $('#'+rid).html('<div class="ibar" style="width:' + data.percent + '%">' + data.percent + '%</div>');
                            $('#'+msg).html(data.show_msg);
                            if(data.percent==100){clearInterval(interval);}
                        });
                    }, 1000);
                },
                success:function(response){
                    if(response.success==true){                    
                        //alert(response.message);
                        oTable.ajax.reload();
                    }else{
                        
                    }
                }
            }); 
        }
    });    
    
    oTable.on('click', '.btn_generate_certificate', function (e) {     
        var id=$(this).data('id');    //record_unique_id
        var rid=$(this).data('rid');    
        var msg=$(this).data('msg');    
        $('#'+rid).hide();
        $('#'+msg).hide();   
        
        //var id=1;
        var token = "<?= csrf_token()?>";
        var interval = null;
        if (confirm('Are you sure?')) {
            $.ajax({
                type:'GET',
                url : "<?=route('molwa-certificate.CertificateGenerate')?>",
                dataType:'json',
                data:{'id':id, '_token':token},            
                beforeSend : function() {
                    $('#'+rid).show();
                    $('#'+msg).show();                   
                    interval = setInterval(function(){
                        $.getJSON('<?=route('molwa-certificate.getProgess')?>', function(data) {
                            $('#'+rid).html('<div class="cbar" style="width:' + data.percent + '%">' + data.percent + '%</div>');
                            $('#'+msg).html(data.show_msg);
                            if(data.percent==100){clearInterval(interval);}
                        });
                    }, 1000);
                },
                success:function(response){
                    if(response.success==true){
                        //alert(response.message);
                        oTable.ajax.reload();
                    }else{
                        
                    }
                }
            }); 
        }    
    });
    
    oTable.on('click', '.btn_status', function (e) {     
        var id=$(this).data('id');    //record_unique_id
        var rid=$(this).data('rid'); 
        var col=$(this).data('col');
        var token = "<?= csrf_token()?>";
        var interval = null;
        if (confirm('If you have printed all the Pages of this lot then confirm.')) {
            $.ajax({
                type:'GET',
                url : "<?=route('molwa-certificate.StatusComplete')?>",
                dataType:'json',
                data:{'id':id, 'col':col, '_token':token},            
                beforeSend : function() {
                    $('#'+rid).html('Wait...');
                },
                success:function(response){
                    if(response.success==true){
                        //alert(response.message);
                        oTable.ajax.reload();
                    }else{
                        
                    }
                }
            });
        }        
    });

	//view 
	oTable.on('click', '.viewData', function (e) {
       $('#viewInfo').modal('show');
		$id = $(this).data('id');
        var token = "<?= csrf_token()?>";
		$.post("<?=route('molwa-certificate.ViewImportFF')?>",{'view':'1','id':id, '_token':token},function(data){
			$('#ajaxContent').html(data);
		});
	});	    
$('#example tbody').on('click', 'button.details-control', function () {
    var tr = $(this).closest('tr');
    var row = oTable.row( tr );
	var id=$(this).attr('id');	
    if ( row.child.isShown() ) {
        row.child.hide();
        tr.removeClass('shown');
    }
    else {
        row.child( format(row.data(),id) ).show();
		tr.addClass('shown');
    }
} );

function format ( rowData,id ) { //rowData[1] 
    var div = $('<div/>')
        .addClass( 'loading' )
        .text( 'Loading...' );
    var token = "<?= csrf_token()?>";
    $.ajax( {
        url: "<?=route('molwa-certificate.ViewImportFF')?>",
		type: "POST",
        data: {
            'id':id, '_token':token
        },
        //dataType: 'json',
        success: function ( data ) {
            div
                .html( data )
                .removeClass( 'loading' );
        }
    } );
 
    return div;
}    
</script>
@stop
@section('style')
<style type="text/css">
#example th{
border: 1px solid #dee3ed;
}
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
#progress .bar {
    background-color: #0052CC;
    color: white;
    height: 18px;
    font-size: 14px;
    text-align: center;
} 
.ibar, .cbar {
    background-color: #0052CC;
    color: white;
    height: 18px;
    font-size: 14px;
    text-align: center;
} 
blockquote {
    padding: 2px 10px;
    margin: 0 0 6px;
    font-size: 14px;
    border-left: 5px solid #0052CC;
    background-color: #e8eff9;
}
</style>
@stop