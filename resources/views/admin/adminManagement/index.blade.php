@extends('admin.layout.layout')
@section('content')
<?php 
$domain = \Request::getHost();
$subdomain = explode('.', $domain);
?>
	<div class="container">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header"><i class="fa fa-fw fa-foursquare"></i> Admin Management
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('adminmanagement') }}</ol>
          <i class="fa fa-info-circle iconModalCss" title="User Manual" id="adminMasterClick"></i>
					</h1>
				</div>
			</div>
				<ul class="nav nav-pills" id="pills-filter">
				  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Admin </a></li>
				  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Admin</a></li>
          @if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.create'))
				  @if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.create'))
					<li style="float: right;">
					  <a href="<?= URL::route('adminmaster.create') ?>" class="btn btn-theme" id="addUser"  data-target="#addUsr" style="background-color: #0052CC;color:white"><i class="fa fa-plus"></i> Add Admin</a>
					</li>
			    @endif
          @endif
				</ul>
				<table id="example" class="table table-hover display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Username</th>
							<th>Full Name</th>
							<th>Email</th>
							<th>Mobile No</th>
							<th>Status</th>
							<th>Action</th>
              <th></th>
						</tr>
					</thead>
					<tfoot>
					</tfoot>
				</table>
			</div>
	</div> 
	<!-- // Admin Model  -->
	@include('admin.adminManagement.model');
  <!-- // End Admin Model  -->

  <!-- branch assign model  -->

  <div class="modal fade" id="branchModal" tabindex="-1" role="dialog" aria-labelledby="branchModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="branchModalLabel">Assign Branch</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="assignBranchForm" enctype="multipart/form-data">
        @csrf
          <input type="hidden" name="userId" id="userId">
          <!-- Add your form fields here -->
          <!-- <div class="form-group">
            <label for="branch-name">Branch Name</label>
            <input type="text" class="form-control" id="branch-name" name="branch_name" required>
          </div> -->
          <div class="form-group">
            <label for="batchSelect">Select Branch</label>
            <select id="batchSelect" name="assign_branch" class="form-control" >
             
            </select>
          </div>
          <div class="form-group signdiv" style="display: none;">
          <label for="sign_file">Approvar sign</label>
              <input type="file" class="form-control" id="sign_file" name="sign_file">
          </div>

          <button type="submit" class="btn btn-primary">Assign</button>
        </form>
      </div>
    </div>
  </div>
</div>

   <!-- end branch assign model -->


@stop
@section('script')
<script type="text/javascript"> 
var subdomain='<?php echo $subdomain[0];?>';   
function deletepath(fm_id)
{
  var delete_path="<?=URL::route('adminmaster.destroy',array(':id')) ?>";
  delete_path=delete_path.replace(':id',fm_id);
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
                     toastr.success('User Deleted successfully');
                     oTable.ajax.reload();
                 }
               },
            });
       }

  });
}

     // datatable
   var oTable = $('#example').DataTable( {
	    'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
	    "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [7, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('adminmaster.index',['status'=>1]) ?>",
		     "aoColumns":[
          {mData: "rownum", bSortable:false},
          {
            mData: "username",
            bSortable:true,
            "sClass": "text-center",
          },
          {mData: "fullname",bSortable:true,"sClass": "text-center"},
          {mData: "email",bSortable:true,"sClass": "text-center"},
          {mData: "mobile_no",bSortable:true,"sClass": "text-center"},
          {
          	mData: "status",
          	bSortable:true,
          	"sClass": "text-center",
          	mRender:function(v,t,o)
          	{
          		var status=null;
          		if(o['status']==1)
          		{
          			status="Active";
          		}
          		else
          		{
          			status="inactive";
          		}
          		return status;
          	}
           },	
    		{
    		  mData:"[{data: 'id'},{data: 'role_id'}]",
    		  bSortable:false,

    		  mRender:function(v, t, o){ 
                 var act_html;
                 var role_id= o['id'];
                 var edit_path = "<?=URL::route('adminmaster.edit',array(':role_id'))?>";
                 edit_path = edit_path.replace(':role_id',role_id);
                     act_html ='@if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.edit'))<a href="'+edit_path+'"><i class="fa fa-edit fa-lg green"></i></a>&nbsp @endif @endif';
                    if(subdomain=="icat"){
                        var roleid= o['role_id'];
                        if(roleid != "1"){
                        var assign_url = "{{route('adminmaster.AssignLab',':id')}}";
                        assign_url = assign_url.replace(':id',o['id']);                    
                        act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.edit'))&nbsp;&nbsp;<span title="Assign Lab" style="cursor:pointer"><a href="'+assign_url+'"><i class="fa fa-code-fork fa-lg blue"></i></a></span>&nbsp @endif @endif';
                        }
                    }
					
                    if(subdomain == "ksg") {
                        var roleid = o['role_id'];
                        // if(roleid != "1"){
                        act_html += '@if(App\Helpers\SitePermissionCheck::isPermitted("adminmaster.edit")) @if(App\Helpers\RolePermissionCheck::isPermitted("adminmaster.edit")) &nbsp;&nbsp;' +
                                    '<span title="Assign Branch" style="cursor:pointer" class="assign-branch" data-id="'+o['id']+'">' +
                                    '<i class="fa fa-sitemap fa-lg blue"></i></span>&nbsp @endif @endif';
                        // }
                    }

					if(subdomain=="sgrsa"){
                        //1 Admin, 2 Sub Agent, 3 Supplier
						var roleid= o['role_id'];
                        if(roleid == "3"){
                        var assign_url = "{{route('adminmaster.AssignSupplier',':id')}}";
                        assign_url = assign_url.replace(':id',o['id']);                    
                        act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.edit'))&nbsp;&nbsp;<span title="Assign Supplier" style="cursor:pointer"><a href="'+assign_url+'"><i class="fa fa-user fa-lg blue"></i></a></span>&nbsp @endif @endif';
                        }
						if(roleid == "2"){
                        var assign_url = "{{route('adminmaster.AssignAgent',':id')}}";
                        assign_url = assign_url.replace(':id',o['id']);                    
                        act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.edit'))&nbsp;&nbsp;<span title="Assign Agent & Supplier" style="cursor:pointer"><a href="'+assign_url+'"><i class="fa fa-users fa-lg green"></i></a></span>&nbsp @endif @endif';
                        }
                    }
					
                     act_html+='@if(App\Helpers\SitePermissionCheck::isPermitted('adminmaster.destroy')) @if(App\Helpers\RolePermissionCheck::isPermitted('adminmaster.destroy'))&nbsp;&nbsp;<a onclick="deletepath('+o['id']+')"><i class="fa fa-trash fa-lg red"></i></a>@endif @endif';
                     return act_html;
    				},
    			},
        {mData: "updated_at",bSortable:false,bVisible:false},	
			],
    });

  oTable.on('draw',function(){
    
    $(".loader").addClass('hidden');
  });
// read  inactive user
$('#fail-pill').click(function(){
   var url="<?= URL::route('adminmaster.index',['status'=>0])?>";
   oTable.ajax.url(url);
   oTable.ajax.reload();
   $('.loader').removeClass('hidden');
});
// read  active user
$("#success-pill").click(function(event) {

	var url="<?= URl::route('adminmaster.index',['status'=>1])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	 $('.loader').removeClass('hidden');
});

// clear Admin model data
$('.clear_model').on('hidden.bs.modal', function () {
    $(this).find("input,textarea,select").val('').end();
    $(this).find('span').text('').end();
});


//new Ksg changes
$(document).on('click', '.assign-branch', function() {
    var id = $(this).data('id');
    $("#userId").val(id);
    var url = "{{ url('get-branch') }}/" + id;
    $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
              console.log(response);
              // let options = '<option value="">Select Branch</option>'; 
              // response.branches.forEach(function(branch) {
              //   // options += `<option value="${batch.id}">${batch.name}</option>`;
              //   options += `<option value="${branch.id}">${branch.name}</option>`;
              // });
              let options = '<option value="">Select Branch</option>'; 
                response.branches.forEach(function(branch) {
                    // Check if the branch id matches the assign-branch value
                    let isSelected = response["assign-branch"] && response["assign-branch"] === branch.id ? 'selected' : '';
                    options += `<option value="${branch.id}" ${isSelected}>${branch.name}</option>`;
                });
              $('#batchSelect').html(options);
              if(response.role_name=="Cheker"){
                $('.signdiv').show();

              }
                $('#branchModal').modal('show'); 
            },
            error: function(xhr) {
              toastr.error('An error occurred while fetching the batch details.');
            }
        });
});

$('#assignBranchForm').on('submit', function(event) {
    event.preventDefault(); 
    var formData = new FormData(this); 
    var url = "{{ route('assign-branch') }}";
        $.ajax({
          url: url,
            method: 'POST',
            data: formData ,
            processData: false,  
            contentType: false,
            success: function(data) {
              if (data.success === true) {
                toastr.success(data.message); 
              }
              else{
                toastr.error(data.message);  
              } 
              $('#branchModal').modal('hide'); 

            },
            error: function(xhr) {
            if (xhr.status === 422) {  // Laravel validation error status code
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(key, value) {
                    toastr.error(value[0]);  // Display each error using Toastr
                });
            } else {
                toastr.error('An error occurred while updating the branch assignment.');
            }
        }

        });



   
   });

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
