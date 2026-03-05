@extends('admin.layout.layout')
@section('content')

<div class="container">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header"><i class="fa fa-list-ul" aria-hidden="true"></i> Branch
        <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;">{{ Breadcrumbs::render('branch') }}</ol>
        <i class="fa fa-info-circle iconModalCss" title="User Manual" id="branchMasterClick"></i>
        </h1>

      </div>
    </div>

    <div class="">

      <div class="col-xs-12">
          <div class="row">
          <div class="col-lg-12" style="text-align: right;">
            @if(App\Helpers\SitePermissionCheck::isPermitted('branch.store'))
            @if(App\Helpers\RolePermissionCheck::isPermitted('branch.store'))
              <button class="btn btn-theme" onclick="AddBranch()"><i class="fa fa-plus"></i> Add Branch</button>
            @endif
            @endif
          </div>
        </div>
        <table id="example" class="table table-hover" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th>#</th>

              <th>Branch Full Name</th>
              <th>Branch Short Name</th>
              <th>Degree</th>
              <th>Created Date Time</th>
              <th>Updated Date Time</th>
              <th>Action</th>
            </tr>
          </thead>
          <tfoot>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
 <!--   // End User information model -->	

<!--  // User Create Model -->	
@include('admin.raisoni.branch.model')
<!--  // End User Create Model -->	

@stop
@section('script')
<script type="text/javascript">
  
  // get Degree Name 
       var ajaxUrl="{{ URL::route('raisoniMaster.get.degree') }}";
       var token="{{ csrf_token() }}";
       $.get(ajaxUrl,{'_token':token},function(data) {
       
         $.each(data,function(index, el) {
          $("#degree_id").append('<option value='+el.id+'>'+el.degree_name+'</option>');
         });
       });      

  // add semester record
  function AddBranch()
  { 
      $('.update_branch').hide();
     $('#addBranch').modal('show');
     $(".add_branch").show();
     $('.add_branch').click(function(event) {
       
       $(".add_branch").attr('disabled','disabled'); 
       event.preventDefault();
       var create_path="<?= URL::route('branch.store') ?>";
       var token="{{ csrf_token() }}";
       var method_type="post";

       $("#branchData").ajaxSubmit({
             url  : create_path,
             type : method_type,
             data : {'_token':token},
             beforeSubmit:function()
             {
               $(".clear_model").find('span').text('').end();
               $(".branchsave").addClass('fa fa-spinner fa-spin');
             },
             success:function(data)
             {
              // console.log(data)
               if(data.success==true)
               {
                  $('#addBranch').modal('hide');
                  toastr.success('Branch successfully added');
                  oTable.ajax.reload();
                  $(".add_branch").removeAttr('disabled');
                  $(".branchsave").removeClass('fa fa-spinner fa-spin');  

               }
             },
             error:function(resobj)
             {
               $.each(resobj.responseJSON.errors, function(k,v){
                   $('#'+k+'_error').text(v);
                });
               $(".add_branch").removeAttr('disabled'); 
               $(".branchsave").removeClass('fa fa-spinner fa-spin');  
             },
       });      
    });

  } 
    // send Ajax Request to featch User data and show model 
    function branch_edit(branch_id)
     {

         $(".clear_model").find('span').text('').end();
         $('#addBranch').modal('show');
         $(".add_branch").hide();
         $('.update_branch').show();
          var edit_path="<?=URL::route('branch.edit',array(':id')) ?>";
          edit_path=edit_path.replace(':id',branch_id);
          var method_type="GET";
          var token="{{ csrf_token() }}";
          $.ajax({
              
               url : edit_path,
              type : method_type,
              data : {'_token':token},

              success:function(data)
              {
                console.log(data)
                 // $('#addBranch').modal('addBranch');
                 $("#branch_id").val(data.id);
                 $("#degree_id").val(data.degree_id);
                 $("#branch_name_long").val(data.branch_name_long);
                 $("#branch_name_short").val(data.branch_name_short);
              },
        });  
     }
 // send Ajax Request to update User data 
  $(".update_branch").click(function(event) {
      // console.log('pdate')
       $(".update_save").attr('disabled'); 
       var branch_id=$("#branch_id").val();
       event.preventDefault();
       var update_path="<?= URL::route('branch.update',array(':id')) ?>";
       update_path=update_path.replace(':id',branch_id);
       var token="{{ csrf_token() }}";
       var type="post";

       $("#branchData").ajaxSubmit({
             url  : update_path,
             type : type,
             data : {'_token':token},
            beforeSubmit:function()
             {
               $(".clear_model").find('span').text('').end();
               $(".branchupdate").addClass('fa fa-spinner fa-spin');
               $(".update_branch").attr('disabled','disabled'); 
             },
             success:function(data)
             {
               if(data.success==true)
               {
                 $('#addBranch').modal('hide');
                 toastr.success('Branch Updated successfully');
                 oTable.ajax.reload();
                 $(".update_branch").removeAttr('disabled'); 
                 $(".branchupdate").removeClass('fa fa-spinner fa-spin');  
               }
             },
             error:function(resobj)
             {
               $.each(resobj.responseJSON.errors, function(k,v){
                   $('#'+k+'_error').text(v);
                });
               $(".update_branch").removeAttr('disabled'); 
               $(".branchupdate").removeClass('fa fa-spinner fa-spin');  
             },
       });      
  });
         
     // datatable
   var oTable = $('#example').DataTable( {
	      'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
	      "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [6, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('branch.index') ?>",
		    "aoColumns":[
          {mData: "rownum", bSortable:false},
          {mData: "branch_name_long",bSortable:true,"sClass": "text-center"},
          {mData: "branch_name_short",bSortable:true,"sClass": "text-center"},
          {mData: "degree_name",bSortable:true,"sClass": "text-center"},
          {mData: "created_at",bSortable:true,"sClass": "text-center"},
          {mData: "updated_at",bSortable:true,"sClass": "text-center"},		
    			{
    				mData:"id",
    				bSortable:false,

    				mRender:function(v, t, o){
                    var act_html = '';
          
                     act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('branch.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('branch.edit')) &nbsp;&nbsp;<a onclick="branch_edit('+o['id']+')" title="Edit"><i class="fa fa-edit fa-lg green"></i></a>&nbsp @endif @endif';

                     return act_html;
    				},
    			},
			],
    }); 

  oTable.on('draw',function(){
    
    $(".loader").addClass('hidden');
  });

// clear model data
$('.clear_model').on('hidden.bs.modal', function () {
    $(this).find("input,textarea,select").val('').end();
    $(this).find('span').text('').end();
});

</script>
@stop
@section('style')
<style type="text/css">
.modal-body .row{ 
  border-bottom: 1px solid #e5e5e5;
  padding-bottom: 10px;
  padding-top: 10px;
}
.help-inline{
  color:red;
  font-weight:normal;
}

#example td{
  vertical-align:middle !important;
  padding:15px 10px;
}
#example tr.sys-admin{
  background:#b3e5fc !important;
    font-weight:bold;
}

#example tr.sys-admin .green, #example tr.sys-admin .red, #example tr.sys-admin .yellow, #example tr.sys-admin .blue, #example tr.sys-admin .grey{
  color:#283593;
}

#example .green, #example  .red, #example .yellow, #example .blue{
  cursor:pointer;
}

.grey{
  color:#444;
}

.active-student{
  border-left:3px solid #5CB85C !important;
}
.inactive-student{
  border-left:3px solid #D9534F !important;
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
.iconModalCss {
    margin-top: 20px !important;
}
</style>

@stop