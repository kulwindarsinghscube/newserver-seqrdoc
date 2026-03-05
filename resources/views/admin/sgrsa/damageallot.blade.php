@extends('admin.layout.layout')
@section('content')
	<div class="container" style="width: 1350px !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-file"></i> Damaged/Replaced - Compliance Certificate</h1>	
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
						<?php if ($role_id==3){ ?>
                        <div class="col-xs-12" id="agent_div" style="display:none;">                
                            <div class="card">                                
                                <div class="card-body" style="padding:20px;">
                                <span id="show_header"></span>
                                <div class="row">
                                    <form method="post" id="processApiForm" class="form-horizontal" novalidate="novalidate">
                                    <input type="hidden" value="<?= csrf_token()?>" name="_token"> 
									<input type="hidden" class="form-control" id="record_id" name="record_id">	
									<input type="hidden" class="form-control" id="chk_from" name="chk_from">	
									<input type="hidden" class="form-control" id="chk_to" name="chk_to">	
                                    <div class="col-xs-12">  
                                        <div class="form-group">
                                            <div class="col-xs-12">												
												<div class="col-sm-3">
													<label class="control-label" for="temp">Condition</label>
													<br />
													<select class="form-control" name="condition" id="condition" style="width:100%;">
													<option value="">Select</option>
													<option value="Damaged">Damaged</option>
													<option value="Replaced">Replaced</option>
													</select>
												</div>
												<div class="col-sm-3">
													<label class="control-label">Enter HC Number</label><br />
													<input type="text" class="form-control" id="hc_no_enter" name="hc_no_enter" autocomplete="off">
												</div>
												<div class="col-sm-3">
													<label class="control-label">Original HC Number</label><br />
													<input type="text" class="form-control" id="hc_no_original" name="hc_no_original" autocomplete="off" readonly>
												</div>
                                            </div>
                                        </div>         
                                    </div>
                                    <div class="col-xs-12"><p></p></div>
                                    <div class="col-xs-12 text-center"> 
                                    <button type="submit" class="btn btn-primary" id="btn_import">Submit</button> 
                                    </div>
                                    </form>
                                    
                                    <div class="col-xs-12"><div id="progress" style="border:1px solid gray; border-radius: 4px; display:none;"></div></div>
                                    <div class="col-xs-12"><div id="show_msg"></div></div>
                                    <div class="col-xs-12"><div id="show_records"></div></div>
                                    <div class="col-xs-12"><p><div id="show_msg2" class="text-center"></p></div></div>
									<div class="col-xs-12">
									<small>
									<p>
									Note:<br />
									<span style='font-size:18;'>•</span> Select "Damaged" option if certificate paper is wasted due to paper jam in printer.<br />
									<span style='font-size:18;'>•</span> Select "Replaced" option if entered HC number and certificate HC number is mismatched.
									</p>
									</small>
									</div>
                                </div>
                                </div>                                
                            </div>
															
                        </div> 
						<?php }?>
						<div class="">							
							<div class="col-xs-12">
								<table id="example" class="table table-hover table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>#</th>
											<th>HC Number</th>
											<th>Agent</th>
											<th>Status</th>
											<th></th>
										</tr>
									</thead>
									<tfoot>
									</tfoot>
								</table>
							</div> 
                            <div class="col-sm-12" style="height:30px;"></div>    
                            
						</div>						
						
					</div>
				</div>
			</div>
		</div>
	</div>
<?php 
	$domain = \Request::getHost();
	$subdomain = explode('.', $domain);
	$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
	$path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
	$print_filepath=$path.$subdomain[0]."/backend/tcpdf/examples/";
	$verify_filepath=$path.$subdomain[0]."/backend/pdf_file/"; 
?>    
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

	/*$('.installation_date').datetimepicker({
		format: 'DD-MM-YYYY'
	});*/
	roleid=<?=$role_id?>;
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
        [1, "asc"]
        ],
        //index page url calls
        "sAjaxSource":"<?= URL::route('allot-edit.RecallList',['status'=>1])?>",        
        //columns that displaying
        "aoColumns":[
		{mData: "rownum", sWidth: "5%", bSortable:false,},
		/*{mData: "created_date",sWidth: "10%",bSortable: true,
			mRender: function(v, t, o) {
            	var date = moment(v).format('DD-MM-YYYY'); 
				return date;
            },
		},*/
		
        {mData: "hc_number",bSortable: true,},  
		{mData: "name",bSortable: true,},	
		{mData: "status",bSortable: true,}, 		 
		{mData: 'id',
            bSortable: false,
            sWidth: "10%",
            //sClass: "text-center",
            mRender: function(v, t, o) {
            	//supplier_reply=o['supplier_reply'];
				var buttons = ''; 
				
                buttons += '<a href="javascript:void(0);" class="allot-control button" id="'+o['id']+'" hc_number="'+o['hc_number']+'" title="Edit"><i class="fa fa-edit fa-2" style="font-size: 1.5em;"  aria-hidden="true"></i></a>'; 
                //buttons += '<a href="javascript:void(0);" class="details-control button" id="'+o['id']+'" title="View Alloted Numbers"><i class="fa fa-file" style="font-size: 1.5em;"  aria-hidden="true"></i></a>'; 
                
				return buttons;
            },   	
        },
		],

	});

	oTable.on('draw.dt', function () {
	    //$(".loader").addClass('hidden');  
	}); 
	
	$('#example tbody').on('click', 'a.allot-control', function () {
		$("html, body").animate({ scrollTop: 0 }, "slow");
		$('#agent_div').show();
		var tr = $(this).closest('tr');
		var row = oTable.row( tr );
		var id=$(this).attr('id');	
		var hc_number=$(this).attr('hc_number');
		//$("#show_header").html("HC "+hc_number);
		$('#record_id').val(id);
		$('#hc_no_original').val(hc_number);
        	
	} );	
	

	
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
            condition:{required: true}, 
            hc_no_enter:{required: true, digits: true},    
            hc_no_original:{required: true, digits: true},
        },
        messages:{            
            condition:{required:'Select Codition'},
			hc_no_enter:{required:'Enter HC Number', digits: 'Enter Only Digits'},
			hc_no_original:{required:'HC Number Required', digits: 'Enter Only Digits'},
        },
        submitHandler: function(form){            
            $('#show_msg').hide();
            $('#show_msg2').hide();
            var token = "<?= csrf_token()?>"; 
            $('#processApiForm').ajaxSubmit({
                url:'<?= route('allot-edit.editData')?>',
                type: "POST",
                dataType: "JSON",
                data:$("#processApiForm").serializeArray(),
                //processData: false,
                //contentType:false,                
                beforeSubmit:function(formData,jqform, options){
                    $('#show_msg').show();
					$('#show_msg2').show();
					$("#show_msg2").html("Please wait...");
                    $('#btn_import').hide();
                },clearForm:false,dataType:'json',success:function(resObj){
                    if(resObj.success == true){
                        $('#show_msg2').show();
                        $("#show_msg2").html(resObj.message);
                        $('#btn_import').show()
                        $("#processApiForm")[0].reset();
						$("select.select2").select2('data', {}); // clear out values selected
						$("select.select2").select2({ 
							allowClear: true, 
						    //placeholder: "Select",
							//initSelection: function(element, callback) { }
						}); 
						$('#agent_div').hide();
						oTable.ajax.reload();
						alert(resObj.message);
                    }else{
                        $('#btn_import').show();
                        $('#show_msg2').show();
                        $("#show_msg2").html(resObj.message);
						//alert(resObj.message);
                        //window.location.reload();
                    }
                }

            });
        }
    })    
    
	/*$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $token
		}
	});*/
	
	
	$('#example tbody').on('click', 'a.rdetails-control', function () {
		var tr = $(this).closest('tr');
		var row = oTable.row( tr );
		var id=$(this).attr('id');	
        if (confirm("Do you really want to delete a record?")) {
			$.ajax( {
				url: "",
				type: "GET",
				data: {
					'id':id
				},
				//dataType: 'json',
				success: function (resObj) {
					if(resObj.success == true){
						alert(resObj.message);
						oTable.ajax.reload();
					}
				}
			} );
		}		
	} );	
	
	//view 
    $('.viewData').click(function (e) {
        $('#ajaxContent').html('Please wait...');
        $('#viewInfo').modal('show');
        var api_option=$(this).data('api');
        var district_selector=$("#district_selector").val();
        var upazila_selector=$("#upazila_selector").val();
        var category_selector=$("#category_selector").val();
        var per_page=$("#per_page").val();
        var page_no=$("#page_no").val();        
        var is_alive=$("#is_alive").val();        
        var token = "<?= csrf_token()?>";
        $.get("<?=route('molwa-certificate.ViewImportDetails')?>",{'api_option':api_option, 'per_page':per_page, 'page_no':page_no, 'is_alive':is_alive, 'district':district_selector, 'upazila':upazila_selector, 'category':category_selector, '_token':token},function(data){
            $('#ajaxContent').html(data);
        });
	});	    
	
	$('#example tbody').on('click', 'a.details-control', function () {
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
			url: "<?=route('supplier-viewallotments.ViewAllotments')?>",
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
.select2-container .select2-selection--single {
    height: 34px !important;
}
</style>
@stop