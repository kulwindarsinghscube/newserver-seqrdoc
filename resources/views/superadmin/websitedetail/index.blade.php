@extends('superadmin.layout.layout')

@section('content')
    <div class="container">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header"><i class="fa fa-fw fa-users"></i> Website Detail
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
					</h1>		
				</div>
			</div>
				<ul class="nav nav-pills" id="pills-filter">
				  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Users </a></li>
				  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Users</a></li>
					<li style="float: right;">
							<button class="btn btn-theme" id="show_model"><i class="fa fa-plus"></i> Create WebsiteDetail </button>
					</li>
				</ul>
				<div class="col-xs-12">
					<table id="example" class="table table-hover" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>#</th>
								<th>WebsiteURl</th>
								<th>DatabaseName</th>
								<th>HostName</th>
								<th>Username</th>
								<th>Password</th>
								<th>Action</th>
                <th></th>
							</tr>
						</thead>
						<tfoot>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
		@include('superadmin.websitedetail.model');   
@stop

@section('script')
 <script type="text/javascript">
 	   // datatable
   var oTable = $('#example').DataTable( {
	      'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
	      "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [7, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('website-detail.index',['status'=>1]) ?>",
		    "aoColumns":[
          {mData: "rownum", bSortable:false},
          {mData: "website_url",bSortable:true,"sClass": "text-center"},
          {mData: "db_name",bSortable:true,"sClass": "text-center"},
          {mData: "db_host_address",bSortable:true,"sClass": "text-center"},
          {mData: "username",bSortable:true,"sClass": "text-center"},
          {mData: "password",bSortable:true,"sClass": "text-center"},	
    			{
    				mData:"id",
    				bSortable:false,

    				mRender:function(v, t, o){
                        var act_html;
                     
                     act_html ='&nbsp;&nbsp;<a id="db_update" data-id="'+o['id']+'" data-name="'+o['website_url']+'" data-dbname="'+o['db_name']+'" data-db_host="'+o['db_host_address']+'" data-username="'+o['username']+'" data-port="'+o['port']+'" data-table_name="'+o['table_name']+'" data-status="'+o['status']+'" data-password="'+o['password']+'"><i class="fa fa-edit fa-lg green"></i></a>&nbsp'
                     act_html +='&nbsp;&nbsp;<a onclick="deletepath('+o['id']+')"><i class="fa fa-trash fa-lg red"></i></a>';

                     return act_html;
    				},
    			},
          {mData: "updated_at",bSortable:false,bVisible:false},	
			],
    });

    oTable.on('click', '#db_update', function(event) {
    	  $web_id=$(this).data('id');
          $(".add_user").hide();
          $("#UserEdit").show();
    	  $("#website_url").val($(this).data('name'));
    	  $("#db_name").val($(this).data('dbname'));
    	  $("#db_host_address").val($(this).data('db_host'));
    	  $("#username").val($(this).data('username'));
    	  $("#password").val($(this).data('password'));
    	  $("#port").val($(this).data('port'));
    	  $("#table_name").val($(this).data('table_name'));
    	  if($(this).data('status')==1)
    	  {
             $("#opt_status").val($(this).data('status'));
    	  }
    	  else if($(this).data('status')==0)
    	  { 
    	  	 $("#opt_status").val($(this).data('status'));;
    	  }
    	  $("#addUsr").modal('show');
    }); 

   // Send Ajax Request to delete User Record      
    function deletepath(user_id)
    {
       console.log(user_id);	 
	   var delete_path="<?=URL::route('website-detail.destroy',array(':id')) ?>";
	   delete_path=delete_path.replace(':id',user_id);
	   var token="{{ csrf_token() }}";
	   console.log(delete_path);
	   var method_type="delete";

	    bootbox.confirm("Are you sure you want to delete?",function(result){	
          if(result)
           {
            $.ajax({
               url  : delete_path,
               type : method_type,
               data : {'_token':token},
               success:function(data){  
                 if(data.success==true)
                 {
                     toastr.success('Deleted successfully');
                     oTable.ajax.reload();
                 }
               },
            });
           } 

        });
     }

   $("#UserSave").click(function(event) {
   	  event.preventDefault();
      
      var url_path="<?= URL::route('website-detail.store') ?>";
      var method_type="post";
      var token="{{ csrf_token() }}";

      $("#UserData").ajaxSubmit({
          
              url  : url_path,
              type : method_type,
              data:{'_token':token},
              beforeSubmit:function(){
                $("#UserData").find("span").text('');
                $(".loadsave").addClass('fa fa-spinner fa-spin');
                $(".add_user").attr('disabled','disabled');
              },
              success:function(data){
                
                if(data.success==true)
                {
                	toastr.success(data.msg);
                	$("#addUsr").modal('hide');
                	oTable.ajax.reload();
                	$(".loadsave").removeClass('fa fa-spinner fa-spin');
                	$(".add_user").removeAttr('disabled','disabled');
                }
              },
              error:function(respone){
                 $(".loadsave").removeClass('fa fa-spinner fa-spin');
                  $(".add_user").removeAttr('disabled','disabled');
                 $.each(respone.responseJSON.errors,function(k,v) {
                 	$("#"+k+"_error").text(v);
                 });
              }
      });
   });
     $("#UserEdit").click(function(event) {
   	  event.preventDefault();

      var url_path="<?= URL::route('website-detail.update',array(':id')) ?>";
      url_path=url_path.replace(':id',$web_id);
      var method_type="patch";
      var token="{{ csrf_token() }}";

      $("#UserData").ajaxSubmit({
          
              url  : url_path,
              type : method_type,
              data:{'_token':token},
              beforeSubmit:function(){
                $("#UserData").find("span").text('');
                $(".loadupdate").addClass('fa fa-spinner fa-spin');
                $(".update_user").attr('disabled','disabled');
              },
              success:function(data){
                
                if(data.success==true)
                {
                	toastr.success(data.msg);
                	$("#addUsr").modal('hide');
                	oTable.ajax.reload();
                	$(".loadupdate").removeClass('fa fa-spinner fa-spin');
                	$(".update_user").removeAttr('disabled','disabled');
                }
              },
              error:function(respone){
                 $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                 $(".update_user").removeAttr('disabled','disabled');
                 $.each(respone.responseJSON.errors,function(k,v) {
                 	$("#"+k+"_error").text(v);
                 });
              }
      });
   });
   $("#show_model").click(function(event) {
         $(".add_user").show();
   	   $("#addUsr").modal('show');
   	   $(".update_user").hide();
   });

  $("#fail-pill").click(function(event) {
  	 var url_path="<?= URL::route('website-detail.index',['status'=>0]) ?>";
  	 oTable.ajax.url(url_path);
  	 oTable.ajax.reload();
  }); 
  $("#success-pill").click(function(event) {
  	var url_path="<?= URL::route('website-detail.index',['status'=>1]) ?>";
  	oTable.ajax.url(url_path);
  	oTable.ajax.reload();
  });
  $("#addUsr").on('hidden.bs.modal', function(event) {
     
     $("#UserData").find('input').val('');
     $("#UserData").find('span').text('');
  });
 </script>
@stop