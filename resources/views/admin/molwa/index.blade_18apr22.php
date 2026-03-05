@extends('admin.layout.layout')
@section('content')
	<div class="container" style="width: 1350px !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-file"></i> Manage Records
								<!--<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('institutemanagement') }}</ol>-->
								</h1>	
							</div>
						</div>
						
						<div class="modal fade" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h4 class="modal-title" id="myModalLabel">Lab</h4>
									</div>
									<div class="modal-body">
									
										<div class="alert alert-danger">
										  <strong>Error!</strong> <span class="message"></span>
										</div>
										
										<div class="alert alert-success">
										  <strong>Success!</strong> <span class="message"></span>
										</div>
										
										<form method="post" id="UserData">
											<div class="form-group">
												<label>Title</label>
												<input type="text" class="form-control" id="lab_title" name="lab_title" maxlength="100">
												<span id="lab_title_error" class="help-inline text-danger"></span>
											</div>
											<div class="form-group">
												<label for="opt_status">Status :</label>
												<select name="status" id="opt_status" class="form-control">
													<option value="">Select</option>
													<option value="1">Active</option>
													<option value="0">Inactive</option>
												</select>
												<span id="status_error" class="help-inline text-danger"></span>
											</div>
											<input type="hidden" name="id" id="lab_id">
											<div class="form-group clearfix">
									

										  <button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="UserSave"><i class="fa fa-save"></i> Save</button>
											<button type="submit" class="btn btn-theme col-lg-2 col-md-2 col-sm-12 col-xs-12 col-md-offset-10" id="UserEdit"><i class="fa fa-save"></i> Update</button>
										
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
						<div class="">
							<!--<ul class="nav nav-pills" id="pills-filter">
							  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Lab </a></li>
							  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Lab</a></li>
							  @if(App\Helpers\SitePermissionCheck::isPermitted('labmaster.store'))
							  @if(App\Helpers\RolePermissionCheck::isPermitted('labmaster.store'))
							  <li style="float: right;">
								<button class="btn btn-theme" id="addUser" data-toggle="modal" data-target="#addUsr"><i class="fa fa-plus"></i> Create Lab</button>
							   </li>
							  @endif
							  @endif
							</ul>-->
							<div class="col-xs-12">
								<table id="example" class="table table-hover table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>#</th>
											<th>Created</th>
											<!--<th>Photo</th>-->
											<th>FF ID</th>
											<th>Name</th>
											<th>District</th>
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
                                <blockquote>Only the records, that are imported (from Import & Print module) will be available here. If record is not yet imported, it will not be available here.</blockquote>
                                <blockquote><i class="fa fa-eye fa-sm blue"></i> icon: the page will display all the most-recently-imported details of the record. Like: FF number, Name, District, Thana, Mother Name, Father Name, Village.</blockquote>
                                <blockquote><i class="fa fa-tasks fa-sm blue"></i> icon: a log will be displayed with details.</blockquote>
                                <blockquote><i class="fa fa-times-circle red"></i> On click, present a confirmation box to disable the FF. Scanning QR codes on active documents of a disabled FF will now show the certificate but a message as "This document is deactivated" Once disabled, the same icon (now as a <i class="fa fa-check-circle green"></i> tick mark) will enable the FF.</blockquote>
                                                           
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
	//$('a[href^="labmaster.php"]').parent().addClass('active');
	//$('a[href^="labmaster.php"]').parent().parent().parent().addClass('active');

	//hide alert message in add-edit form
	$(".alert-danger").hide();
	$(".alert-success").hide();

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
        "sAjaxSource":"<?= URL::route('molwa-certificate.index',['status'=>1])?>",
        //columns that displaying
        "aoColumns":[

		{mData: "rownum", sWidth: "7%", bSortable:false,
		},
		{mData: "created_at",sWidth: "10%",bSortable: true,
			mRender: function(v, t, o) {
            	//var date = moment(v).format('DD-MM-YYYY h:mm:ss');
            	var date = moment(v).format('DD-MM-YYYY');
				return date;
            },
		},
        /*{mData: 'ff_photo',
            bSortable: false,
            sWidth: "5%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            	var buttons = '<img src="'+o['ff_photo']+'" class="img-thumbnail" />';            	
				return buttons;
            },
         		   	
        },*/
        {mData: "ff_id",bSortable: true,},
		{mData: "ff_name",bSortable: true,},
		{mData: "district",bSortable: true,},        
		//status
        {mData: "id",bSortable: true,
            bSortable: false,
            mRender: function(v, t, o) {
                generated_at=o['generated_at']; 
                c_generated_at=o['c_generated_at'];
                completed_at=o['completed_at'];
                c_completed_at=o['c_completed_at'];
                import_flag=o['import_flag'];
                var str = '';
                if(generated_at == ''){
                    if(import_flag == 'IMPORTED'){ str = 'IMPORTED'; }else{ str = 'RE-IMPORTED'; }
                }
                if((generated_at == '' && c_generated_at != '') || (generated_at != '' && c_generated_at == '')){
                    if(import_flag == 'IMPORTED'){ str = 'PROGRESS'; }else{ str = 'RE-PROGRESS'; }
                }
                if((generated_at != '' && c_generated_at != '') && (completed_at == '' && c_completed_at == '') ){
                    if(import_flag == 'IMPORTED'){ str = 'GENERATED'; }else{ str = 'RE-GENERATED'; }
                }
                if((generated_at != '' && c_generated_at != '') && (completed_at != '' || c_completed_at != '') ){
                    if(import_flag == 'IMPORTED'){ str = 'GENERATED'; }else{ str = 'RE-GENERATED'; }
                }
                if(completed_at != '' && c_completed_at != ''){
                    if(import_flag == 'IMPORTED'){ str = 'COMPLETED'; }else{ str = 'RE-COMPLETED'; }
                }
                return str;
            },
        },
		//{mData: "ghost_image_code",bSortable: true,},        
		{mData: 'id',
            bSortable: false,
            sWidth: "12%",
            sClass: "text-center",
            mRender: function(v, t, o) {
                generated_at=o['generated_at']; 
                c_generated_at=o['c_generated_at'];
                completed_at=o['completed_at'];
                c_completed_at=o['c_completed_at'];
                import_flag=o['import_flag'];
                var str = '';
                if(generated_at == ''){
                    if(import_flag == 'IMPORTED'){ str = 'IMPORTED'; }else{ str = 'RE-IMPORTED'; }
                }
                if((generated_at == '' && c_generated_at != '') || (generated_at != '' && c_generated_at == '')){
                    if(import_flag == 'IMPORTED'){ str = 'PROGRESS'; }else{ str = 'RE-PROGRESS'; }
                }
                if((generated_at != '' && c_generated_at != '') && (completed_at == '' && c_completed_at == '') ){
                    if(import_flag == 'IMPORTED'){ str = 'GENERATED'; }else{ str = 'RE-GENERATED'; }
                }
                if((generated_at != '' && c_generated_at != '') && (completed_at != '' || c_completed_at != '') ){
                    if(import_flag == 'IMPORTED'){ str = 'GENERATED'; }else{ str = 'RE-GENERATED'; }
                }
                if(completed_at != '' && c_completed_at != ''){
                    if(import_flag == 'IMPORTED'){ str = 'COMPLETED'; }else{ str = 'RE-COMPLETED'; }
                }            	
                var buttons = '';
            	buttons += '<button title="View Details" class="details-control" id="'+o['id']+'" ff_id="'+o['ff_id']+'" flag="ff_details"><i class="fa fa-eye fa-lg blue"></i></button>';  
            	buttons += '&nbsp;<button title="View Process" class="details-control" id="'+o['id']+'" ff_id="'+o['ff_id']+'" flag="Process"><i class="fa fa-tasks fa-lg blue"></i></button>';  
                /*
                if(o['active']=='Yes'){ rcheck="checked"; title_str="Inactivate"; }else{ rcheck=""; title_str="Activate";} 
                buttons += '&nbsp;<span title="'+title_str+'" style="cursor:pointer"><input name="ai'+o['id']+'" value="'+o['id']+'" ffid="'+o['ff_id']+'" type="checkbox" class="form-control" style="width:20px !important;height:20px !important;" '+rcheck+'></span>';
                */
                if(o['active']=='Yes'){ rcheck="checked"; title_str="Click to Deactivate"; }else{ rcheck=""; title_str="Click to Activate";}                 
                if(str == 'COMPLETED' || str == 'RE-COMPLETED'){
                buttons += '&nbsp;<div class="checkdiv" title="'+title_str+'"><input type="checkbox" name="ai'+o['id']+'" value="'+o['id']+'" ffid="'+o['ff_id']+'" class="le-checkbox" '+rcheck+' /></div>'; 
                }
				return buttons;
            },
         		   	
        },
	],
		/*"createdRow": function( row, data, dataIndex ) {

			if(data['status'] == 'Active'){
				$(row).addClass( 'active-student' );
			}else{
				$(row).addClass( 'inactive-student' );
			}
		}*/
	});

	oTable.on('draw.dt', function () {
	    $(".loader").addClass('hidden');  
	});
    
    $('#example tbody').on('click', 'button.details-control', function () {
        var tr = $(this).closest('tr');
        var row = oTable.row( tr );
        var id=$(this).attr('id');	
        var ff_id=$(this).attr('ff_id');	
        var flag=$(this).attr('flag');	
        if ( row.child.isShown() ) {
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            row.child( format(row.data(),id,ff_id,flag) ).show();
            tr.addClass('shown');
        }
    } );

    function format ( rowData,id,ff_id,flag ) { //rowData[1] 
        var div = $('<div/>')
            .addClass( 'loading' )
            .text( 'Loading...' );
        var token = "<?= csrf_token()?>";
        $.ajax( {
            url: "<?=route('molwa-certificate.ViewFF')?>",
            type: "POST",
            data: {
                'id':id, 'ff_id':ff_id, 'flag':flag, '_token':token
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
    
   // Handle click on checkbox
   $('#example tbody').on('click', 'input[type="checkbox"]', function(e){
        id = $(this).val();        
        var ffid=$(this).attr('ffid');	
        if(this.checked){
            publish='Yes';
            mode="active";
            title_str="activate"
            //alert('active '+id+' '+publish);            
        } else {
            publish='No';
            mode="deactive";
            title_str="deactivate"
            //alert('inactive '+id+' '+publish);            
        }
        var token="{{ csrf_token() }}";
        bootbox.confirm({
            message : ffid+"<br>Do you want to "+title_str+" a document?",
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
                        $.post('<?=route('molwa-certificate.ActiveInactiveRecord')?>',{'_token':token, 'id':id, 'ffid':ffid, 'publish':publish, 'mode':mode},function(Result){
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
    

	//for displaying activate user(status = 1)
	$('#success-pill').click(function(){

		var url="<?= URL::route('labmaster.index',['status'=>1])?>";
		oTable.ajax.url(url);
		oTable.ajax.reload();
		$('.loader').removeClass('hidden');
	});

	//for displaying inactivate user(status = 0)
	$('#fail-pill').click(function(){

		var url="<?= URL::route('labmaster.index',['status'=>0])?>";
		oTable.ajax.url(url);
		oTable.ajax.reload();
		$('.loader').removeClass('hidden');
	});
    
    $('#example tbody').on('click', '.viewData', function () {
        $('#ajaxContent').html('Please wait...');
        $('#viewInfo').modal('show');
        var id=$(this).attr('id');             
        var ff_id=$(this).attr('ff_id');             
        var flag=$(this).attr('flag');             
        var token = "<?= csrf_token()?>";
        $.post("<?=route('molwa-certificate.ViewFF')?>",{'id':id, 'ff_id':ff_id, 'flag':flag, '_token':token},function(data){
            $('#ajaxContent').html(data);
        });
	});	        



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
/*active inactive chcekbox*/
.checkdiv {
  position: relative;
  /*padding: 4px 8px;*/
  border-radius:40px;
  margin-bottom:4px;
  min-height:20px;
  padding-left:51px;
  display: flex;
  align-items: center;
  float:right;
}
.checkdiv:last-child {
  margin-bottom:0px;
}
.checkdiv span {
  position: relative;
  vertical-align: middle;
  line-height: normal;
}
.le-checkbox {
  appearance: none;
  position: absolute;
  top:50%;
  left:5px;
  transform:translateY(-50%);
  background-color: #4CAF50;
  width:30px;
  height:30px;
  border-radius:40px;
  margin:0px;
  outline: none; 
  transition:background-color .5s;
}
.le-checkbox:before {
  content:'';
  position: absolute;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%) translate(-9px,1px) rotate(45deg);
  background-color:#ffffff;
  width:12px;
  height:5px;
  border-radius:40px;  
  /*
  transform:translate(-50%,-50%) rotate(45deg);
  background-color:#ffffff;
  width:20px;
  height:5px;
  border-radius:40px;
  transition:all .5s;
  */
}

.le-checkbox:after {
  content:'';
  position: absolute;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%) rotate(-45deg);
  background-color:#ffffff;
  width:20px;
  height:5px;
  border-radius:40px;
  transition:all .5s;  
  /*
  transform:translate(-50%,-50%) rotate(-45deg);
  background-color:#ffffff;
  width:20px;
  height:5px;
  border-radius:40px;
  transition:all .5s;
  */
}
.le-checkbox:checked {
  background-color:#F44336;
}
.le-checkbox:checked:before {
  content:'';
  position: absolute;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%) rotate(45deg);
  background-color:#ffffff;
  width:20px;
  height:5px;
  border-radius:40px;
  transition:all .5s;  
  /*
  transform:translate(-50%,-50%) translate(-4px,3px) rotate(45deg);
  background-color:#ffffff;
  width:12px;
  height:5px;
  border-radius:40px;
  */
}

.le-checkbox:checked:after {
  content:'';
  position: absolute;
  top:50%;
  left:50%;
  /*
  transform:translate(-50%,-50%) translate(3px,2px) rotate(-45deg);
  background-color:#ffffff;
  width:16px;
  height:5px;
  border-radius:40px;
  */
}
blockquote {
    padding: 2px 10px;
    margin: 0 0 6px;
    font-size: 14px;
    border-left: 5px solid #0052CC;
    background-color: #e8eff9;
}
.tdtext{color:white !important}
</style>
@stop