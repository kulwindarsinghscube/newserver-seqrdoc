@extends('admin.layout.layout')
@section('content')
	<div class="container" style="width: 1350px !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-file"></i> SGRSA - Compliance Certificates Allotment</h1>	
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
													<label class="control-label">Issue Date</label>
													<input type="text" class="form-control installation_date" id="issue_date" name="issue_date" autocomplete="off">
												</div>
												<div class="col-sm-6">
													<label class="control-label" for="temp">Agents</label>
													<br />
													<select class="form-control select2" name="agent" id="agent" style="width:100%;">
													<option value="">Select</option>
													@foreach($agents as $key => $value)
													<option value="{{$key}}" <?php if($supplier_id==$key){ echo 'Selected';} ?>>{{$value}}</option>
													@endforeach		
													</select>
												</div>
                                            </div>								
											
											<div class="col-xs-12">
												<div class="col-sm-3">
													<label class="control-label">HC Number</label><br />
													From <input type="text" class="form-control" id="hc_from" name="hc_from" autocomplete="off">
												</div>
												<div class="col-sm-3">
													<label class="control-label">&nbsp;</label><br />
													To <input type="text" class="form-control" id="hc_to" name="hc_to" autocomplete="off">
												</div>
												<div class="col-sm-3">
													<label class="control-label">&nbsp;</label><br />
													Quantity <input type="text" class="form-control" id="quantity" name="quantity" readonly>
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
                                </div>                                
                                </div>                                
                            </div> 
                        </div> 
						<?php } ?>
						<div class="">							
							<div class="col-xs-12">
								<table id="example" class="table table-hover table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>#</th>
											<th>Created</th>
											<th>Issue Date</th>
											<th>HC From</th>
											<th>HC To</th>
											<th>Qty</th>
											<th>Reply</th>
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

	$('.installation_date').datetimepicker({
		format: 'DD-MM-YYYY'
	});
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
        [1, "desc"]
        ],
        //index page url calls
        "sAjaxSource":"<?= URL::route('supplier-allot.RecallList',['status'=>1])?>",        
        //columns that displaying
        "aoColumns":[
		{mData: "rownum", sWidth: "5%", bSortable:false,},
		{mData: "created_date",sWidth: "10%",bSortable: true,
			mRender: function(v, t, o) {
            	var date = moment(v).format('DD-MM-YYYY'); 
				return date;
            },
		},
		{mData: "issue_date",sWidth: "10%",bSortable: true,
			mRender: function(v, t, o) {
            	var date = moment(v).format('DD-MM-YYYY'); 
				return date;
            },
		},
        {mData: "hc_from",bSortable: true,},        
		{mData: "hc_to",bSortable: true,},        
		{mData: "quantity",bSortable: true,}, 
		{mData: "supplier_reply",bSortable: true,}, 
		{mData: 'id',
            bSortable: false,
            sWidth: "10%",
            //sClass: "text-center",
            mRender: function(v, t, o) {
            	supplier_reply=o['supplier_reply'];
				var buttons = ''; 
				if(supplier_reply == 'Pending'){
                buttons += '<input type="radio" name="ai'+o['id']+'" recordid="'+o['id']+'" value="Approve" class="le-checkbox" /> Approve<br /><input type="radio" name="ai'+o['id']+'" recordid="'+o['id']+'" value="Reject" class="le-checkbox" /> Reject'; 
                }
				if(supplier_reply == 'Approve'){
                buttons += '<a href="javascript:void(0);" class="allot-control button" id="'+o['id']+'" hc_from="'+o['hc_from']+'" hc_to="'+o['hc_to']+'" title="Allot To Agent"><i class="fa fa-share-alt fa-2" style="font-size: 1.5em;"  aria-hidden="true"></i></a>&nbsp;'; 
                buttons += '<a href="javascript:void(0);" class="details-control button" id="'+o['id']+'" title="View Alloted Numbers"><i class="fa fa-file" style="font-size: 1.5em;"  aria-hidden="true"></i></a>'; 
                
				}
				return buttons;
            },   	
        },
	],

	});

	oTable.on('draw.dt', function () {
	    //$(".loader").addClass('hidden');  
	}); 
	
   // Handle click on radio
   $('#example tbody').on('click', 'input[type="radio"]', function(e){
        var reply = $(this).val();        
        var id=$(this).attr('recordid');	
        var token="{{ csrf_token() }}";
        bootbox.confirm({
            message : "Do you want to "+reply+" a allotment?",
            size: 'small',
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
                        $.post('<?=route('supplier-allot.ApproveRejectRecord')?>',{'_token':token, 'id':id, 'reply':reply},function(Result){
                            var data = JSON.parse(Result);
                            if(data.rstatus=='Success') {
                                if(data.mode=='activated') {
                                    toastr["success"](data.message); 
                                }else{
                                    toastr["error"](data.message); 
                                }
                                oTable.ajax.reload();
                            }
                            else {                    
                                toastr["error"]('Record not found.');
                            }                 
                        });
                    }                    
                }
        });
		
		return false;            
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
	//$.validator.setDefaults({ ignore: [] }); $('select').change(function(){ $('select').valid(); }) 
    $('.select2').change(function(){
        $(this).valid()
    });   

	$("#hc_from").keyup(function(){		 
		var count=getQtyCount();
		$("#quantity").val(count);		
	});	
	$("#hc_to").keyup(function(){		 
		var count=getQtyCount();
		$("#quantity").val(count);	
	});
	
	function getQtyCount(){
		var hc_from=parseInt($("#hc_from").val()); 
		var hc_to=parseInt($("#hc_to").val());
		if(hc_from!="" && hc_to!=""){
		    if(hc_to >= hc_from){
			count=0;
		    for (i = hc_from; i <= hc_to; i++){			  
			  count++;
		    }	
			return count;
			}
		}		
	}
	
	$('#example tbody').on('click', 'a.allot-control', function () {
		$("html, body").animate({ scrollTop: 0 }, "slow");
		$('#agent_div').show();
		var tr = $(this).closest('tr');
		var row = oTable.row( tr );
		var id=$(this).attr('id');	
		var hc_from=$(this).attr('hc_from');	
		var hc_to=$(this).attr('hc_to');	
		$("#show_header").html("HC "+hc_from+" To "+hc_to);
		$('#record_id').val(id);
		$('#chk_from').val(hc_from);
		$('#chk_to').val(hc_to);
        	
	} );	
	
	$("#hc_from").on("change", function() {
		var hc_from=parseInt($("#chk_from").val());
		var hc_to=parseInt($("#chk_to").val());
		var val = parseInt(this.value);
		this.value = val > hc_to ? '' : val;
		if(isNaN(val)||val > hc_to || val < hc_from)
		{
			this.value ='';        
		}
	});	
	$("#hc_to").on("change", function() {
		var hc_from=parseInt($("#chk_from").val());
		var hc_to=parseInt($("#chk_to").val());
		var val = parseInt(this.value);
		this.value = val > hc_to ? '' : val;
		if(isNaN(val)||val > hc_to || val < hc_from)
		{
			this.value ='';        
		}
	});
	
	$.validator.addMethod('le', function(value, element, param) {
		  return this.optional(element) || parseInt(value) <= parseInt($(param).val());
	}, 'Invalid value');
	$.validator.addMethod('ge', function(value, element, param) {
		  return this.optional(element) || parseInt(value) >= parseInt($(param).val());
	}, 'Invalid value');		
	
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
            issue_date:{required: true},  
            agent:{required: true},    
            hc_from:{required: true, digits: true, le: '#hc_to'},    
            hc_to:{required: true, digits: true, ge: '#hc_from'},
        },
        messages:{            
            issue_date:{required:'Select Issue Date'},
            agent:{required:'Select Agent'},
			hc_from:{required:'Enter From Number', digits: 'Enter Only Digits', le: 'Must be less than or equal to field To'},
			hc_to:{required:'Enter To Number', digits: 'Enter Only Digits', ge: 'Must be greater than or equal to field From'},
        },
        submitHandler: function(form){            
            $('#show_msg').hide();
            $('#show_msg2').hide();
            var token = "<?= csrf_token()?>"; 
            $('#processApiForm').ajaxSubmit({
                url:'<?= route('supplier-allot.allotData')?>',
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