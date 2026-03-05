@extends('admin.layout.layout')
@section('content')
    <div class="container">
    	<div class="container-fluid">
    		<div class="row">
    			<div class="col-lg-12">
    				<h1 class="page-header"><i class="fa fa-fw fa-money"></i> Degree Master
    				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('degreemasterpage') }}</ol>
    				<i class="fa fa-info-circle iconModalCss" title="User Manual" id="degreeMasterClick"></i>
				</h1>
    			</div>
    		</div>
    	<div class="">
    			<ul class="nav nav-pills" id="pills-filter">
    		  	
            @if(App\Helpers\SitePermissionCheck::isPermitted('degreemaster.store'))
            @if(App\Helpers\RolePermissionCheck::isPermitted('degreemaster.store'))
    				<li style="float: right;">
    					<button class="btn btn-theme" id="addUser" onclick="AddPgMaster_data()"><i class="fa fa-plus"></i> Add Degree</button>	
    				</li>
            @endif
            @endif
    			</ul>
    			<table id="example" class="table table-hover display" cellspacing="0" width="100%">
    				<thead>
    				<tr>
                 	<th>#</th>	
                 	<th>Degree Name</th>
					<th>Created Date Time</th>
					<th>Updated Date Time</th>
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

 @include('admin.raisoni.degreemaster.model')

@stop
@section('script')
<script type="text/javascript">
  
  // Send Ajax Request to delete Record      
function deletepath(fm_id)
{
  var delete_path="<?=URL::route('sessionsmaster.destroy',array(':session_no')) ?>";
  delete_path=delete_path.replace(':session_no',fm_id);
  var token="{{ csrf_token() }}";
  var method_type="get";

  bootbox.confirm("Are you sure you want to delete?",function(result){  
       if(result)
       {
            $.ajax({
               url:delete_path,
               type:method_type,
               data:{'_token':token},
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

  function AddPgMaster_data()
  {
      $(".update_save").hide();
      $('.save').show();
      $('#addUsr').modal('show');
      $('.save').click(function(event) {

         $('.save').attr('disabled','disabled');  
         event.preventDefault();
         var create_path="<?= URL::route('degreemaster.store') ?>";
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
                   $('#addUsr').modal('hide');
                   $('#UserData').trigger("reset");
                   toastr.success('Degree successfully added');
                   oTable.ajax.reload();
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



    // send Ajax Request to featch data and show model 
      function edit(id)
       {
          $(".save").hide();
          $('.update_save').show();
          var edit_path="<?=URL::route('degreemaster.edit',array(':id')) ?>";
          edit_path=edit_path.replace(':id',id);
          var method_type="GET";
          var token="{{ csrf_token() }}";
          $.ajax({
                
                url  : edit_path,
                type : method_type,
                data : {'_token':token},

                success:function(data)
                {
                  $('#addUsr').modal('show');
                  $('#degree_name').val(data.degree_name);
                  $("#degree_id").val(data.id);
                   
                },
          });  
       }


      
    // Update data PaymentGateway
     $(".update_save").click(function(event) {
         $(".update_save").attr('disabled'); 
         var degree_id=$("#degree_id").val();
         event.preventDefault();
         var update_path="<?= URL::route('degreemaster.update',array(':id')) ?>";
         update_path=update_path.replace(':id',degree_id);
         var token="{{ csrf_token() }}";
         var type="post";

         $("#UserData").ajaxSubmit({
               url  : update_path,
               type : type,
               data : {'_token':token},
               beforeSubmit:function(){
                  $("#addUsr").find('span').text('').end();
                  $(".loadupdate").addClass('fa fa-spinner fa-spin');
                  $(".update_save").attr('disabled','disabled');
               },
               success:function(data)
               {
                 if(data.success==true)
                 {
                   $('#addUsr').modal('hide');
                   $('#fontMatser_data').trigger("reset");
                   toastr.success('Degree updated successfully');
                   oTable.ajax.reload();
                   $(".update_save").removeAttr('disabled'); 
                   $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                 }
               },
               error:function(resobj)
               {
                 $.each(resobj.responseJSON.errors, function(k,v){
                     $('#'+k+'_error').text(v);
                  });
                 $(".update_save").removeAttr('disabled');
                 $(".loadupdate").removeClass('fa fa-spinner fa-spin'); 
               },
         });      
    });
 
     // datatable	 
   var oTable = $('#example').DataTable( {
       'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
       "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [1, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('degreemaster.index')?>",
        "aoColumns":[
          {mData: "rownum", bSortable:false},
          {mData: "degree_name",bSortable:true},
          {mData: "created_at",bSortable:true,
              "sClass": "text-center",  
              mRender:function(v)
              {
                  return moment(v).format('DD MMM YYYY hh:mm A');
              }
          },
          {mData: "updated_at",bSortable:true,
              "sClass": "text-center",  
              mRender:function(v)
              {
              	if(v!=null){
              		return moment(v).format('DD MMM YYYY hh:mm A');
              	}else{
              		return '-';
              	}
                  
              } 
          },
          {
            mData:"id",
            bSortable:false,

            mRender:function(v, t, o){
                var act_html;
        
                     act_html ='@if(App\Helpers\SitePermissionCheck::isPermitted('degreemaster.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('degreemaster.edit'))<a onclick="edit('+o['id']+')"><i class="fa fa-edit fa-lg green"></i></a>@endif @endif';
                    /*   act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('sessionsmaster.destroy')) @if(App\Helpers\RolePermissionCheck::isPermitted('sessionsmaster.destroy'))&nbsp;&nbsp;<a onclick="deletepath('+o['session_no']+')"><i class="fa fa-trash fa-lg red"></i></a>@endif @endif';*/

                     return act_html;
            },
          },  
      ],
    });

   oTable.on('draw', function(event) {
    $(".loader").addClass('hidden');
   });

  // get data active PaymentGateway       


// clear Admin model data
$('.clear_model').on('hidden.bs.modal', function () {
    $(this).find("input,textarea,select").val('').end();
    $(this).find('span').text('').end();
    $(".save").removeAttr('disabled');
});
// clear Admin model data 
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