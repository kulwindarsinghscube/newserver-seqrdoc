@extends('admin.layout.layout')
@section('content')
<meta name="csrf-token" content="{{csrf_token()}}">
	<div class="container" style="width: 1350px !important;">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-fw fa-file"></i> Molwa Remark Records
								<!--<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('institutemanagement') }}</ol>-->
								</h1>	
							</div>
						</div>
						
						<div class="modal fade" id="addUsr" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									
								</div>
							</div>
						</div>
						<div class="">
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
											<th>Remark</th>
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
        [0, "asc"]
        ],
        //index page url calls
        "sAjaxSource":"<?= URL::route('molwa-certificate.molwaRemarks',['status'=>1])?>",
        //columns that displaying
        "aoColumns":[

		{mData: "rownum", sWidth: "7%", bSortable:false,},
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
		{mData: "ff_name",bSortable: false,},
		{mData: "district",bSortable: false,},        
		
		//{mData: "ghost_image_code",bSortable: true,},        
		{mData: 'remark', bSortable: true,},
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
			// Enumerate all rows
			oTable.rows().every(function(){
				// If row has details expanded
				if(this.child.isShown()){
					// Collapse row details
					this.child.hide();
					$(this.node()).removeClass('shown');
				}
			});			
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