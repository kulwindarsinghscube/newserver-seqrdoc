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
					<h1 class="page-header"><i class="fa fa-fw fa-users"></i> User Management
					 <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('usermanagement') }}</ol>
                     <i class="fa fa-info-circle iconModalCss" title="User Manual" id="userMasterClick"></i>
					</h1>
					
				</div>
			</div>
				<ul class="nav nav-pills" id="pills-filter">
				  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Users </a></li>
				  <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Users</a></li>
          @if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.store'))
				  @if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.store'))
					<li style="float: right;">
							<button class="btn btn-theme" id="addUser" onclick="AddUser()"><i class="fa fa-plus"></i> Create User</button>
					</li>
          @endif
          @endif
				</ul>
				<div class="col-xs-12">
					<table id="example" class="table table-hover" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>#</th>
								<th>Username</th>
								<th>Fullname</th>
            <?php 
        if (isset($subdomain[0]) && $subdomain[0] == 'mallareddyuniversity') { 
            echo '<th>Organization</th>';
        } 
        ?>
              
								<th>Email</th>
								<th>Mobile</th>
								<th>Device</th>
								<th>Date/Time</th>
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
 <!--   // show User information model -->
	 <div id="myModal" class="modal fade clear_user_data" role="dialog" tabindex="-1">
	  <div class="modal-dialog">

	    <!-- Modal content-->
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	        <h4 class="modal-title">User Information</h4>
	      </div>
	      <div class="modal-body cleardiv" id="ajaxResponse">
        <div class="list-group">
          <div class="list-group-item" id="userType" style="display: none;"><b>User Type:</b> <span id="data-usertype"> </span></div>
          <div class="list-group-item"><b>Username:</b> <span id="data-username"> </span></div>
          <div class="list-group-item"><b>Fullname:</b> <span id="data-fullname"> </span></div>
          <div class="list-group-item"><b>Email:</b> <span id="data-email"> </span></div>
          <div class="list-group-item"><b>Mobile:</b> <span id="data-mobile"> </span></div>
          <div class="list-group-item" id="registrationNo" style="display: none;"><b>Registration No:</b> <span id="data-registration_no"> </span></div>
          <div class="list-group-item" id="workingSector" style="display: none;"><b>Working Sector:</b> <span id="data-working_sector"> </span></div>
          <div class="list-group-item" id="addressView" style="display: none;"><b>Address:</b> <span id="data-address"> </span></div>
          <div class="list-group-item" id="institute" style="display: none;"><b>Institute:</b> <span id="data-institute"> </span></div>
          <div class="list-group-item" id="degree" style="display: none;"><b>Degree:</b> <span id="data-degree"> </span></div>
          <div class="list-group-item" id="branch" style="display: none;"><b>Branch:</b> <span id="data-branch"> </span></div>
          <div class="list-group-item" id="passoutYear" style="display: none;"><b>Passout Year:</b> <span id="data-passout_year"> </span></div>
          <div class="list-group-item"><b>Registration Device:</b> <span id="data-type"> </span></div>
          <div class="list-group-item"><b>Registered On:</b> <span id="data-created"> </span></div>
          <div class="list-group-item"><b>Last Login:</b> <span id="data-lastlogin"> </span></div>
        </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-theme" data-dismiss="modal">Close</button>
	      </div>
	    </div>

	  </div>
	</div>
 <!--   // End User information model -->	

<!--  // User Create Model -->	
@include('admin.userManagement.model')
<!--  // End User Create Model -->	

@stop
@section('script')

   <script type="text/javascript">
      
  // add user record
  function AddUser()
  { 
     $(".psswrd").show();
     $('.update_user').hide();
     $('#addUsr').modal('show');
     $(".add_user").show();
     $('.add_user').click(function(event) {
       
       $(".add_user").attr('disabled','disabled'); 
       event.preventDefault();
       var create_path="<?= URL::route('usermaster.store') ?>";
       var token="{{ csrf_token() }}";
       var method_type="post";

       $("#UserData").ajaxSubmit({
             url  : create_path,
             type : method_type,
             data : {'_token':token},
             beforeSubmit:function()
             {
               $(".clear_model").find('span').text('').end();
               $(".loadsave").addClass('fa fa-spinner fa-spin');
             },
             success:function(data)
             {
               if(data.success==true)
               {
               	  $('#addUsr').modal('hide');
               	  toastr.success('User successfully added');
                  oTable.ajax.reload();
                  $(".add_user").removeAttr('disabled');
                  $(".loadsave").removeClass('fa fa-spinner fa-spin');  

               }
             },
             error:function(resobj)
             {
               $.each(resobj.responseJSON.errors, function(k,v){
                   $('#'+k+'_error').text(v);
                });
               $(".add_user").removeAttr('disabled'); 
               $(".loadsave").removeClass('fa fa-spinner fa-spin');  
             },
       });     	
    });

  } 
    // send Ajax Request to featch User data and show model 
    function user_edit(user_id)
     {

         $(".clear_model").find('span').text('').end();
         $(".psswrd,.add_user").hide();
         $('.update_user').show();
          var edit_path="<?=URL::route('usermaster.edit',array(':id')) ?>";
          edit_path=edit_path.replace(':id',user_id);
          var method_type="GET";
          var token="{{ csrf_token() }}";
          $.ajax({
              
               url : edit_path,
              type : method_type,
              data : {'_token':token},

              success:function(data)
              {
                 $('#addUsr').modal('show');
                 $("#user_id").val(data.id);
                 $("#username").val(data.username);
                 $("#fullname").val(data.fullname);
                 $("#email_id").val(data.email_id);
                 $("#mobile_no").val(data.mobile_no);
                 if(data.status)
                 {
                    $("#opt_status").val(data.status);
                 }
                 else
                 {
                    $("#opt_status").val(data.status);
                 }
                 if(data.role_id)
                 {
                    $('#roleId').val(data.role_id)
                 } 
                 else
                 {
                   $('#roleId').val(0);
                 }  

                  if(typeof(data.user_type) != "undefined" && data.user_type !== null) {

                    if(data.user_type==1||data.user_type==2){

                      if(data.user_type==1){
                         $( "#student_type").prop('checked', false);
                            $( "#agency_type").prop('checked', false);
                             $( "#employer_type").prop('checked', true);
                      }else{
                         $( "#employer_type").prop('checked', false);
                            $( "#student_type").prop('checked', false);
                             $( "#agency_type").prop('checked', true);
                      }
                      $(".emp_agency_holder").show();
                          $(".student_hoder").hide();
                    }else{
                       $(".emp_agency_holder").hide();
                          $(".student_hoder").show();
                           $( "#employer_type").prop('checked', false);
                            $( "#agency_type").prop('checked', false);
                             $( "#student_type").prop('checked', true);
                    }
                    //$("#data-usertype").text(getUserType(parseInt(data.user_type)));
                    //$('#userType').show();
                    //$("input[name=registration_type][value='"+data.user_type+"']").prop("checked",true);
                    //$("input[name=registration_type][value='"+data.user_type+"']").attr('checked','checked')
                  }else{
                     $(".emp_agency_holder").hide();
                          $(".student_hoder").hide();
                  }

                   if(typeof(data.registration_no) != "undefined" && data.registration_no !== null) {
                    $('#reg_no').val(data.registration_no);
                    $('#student_reg_no').val(data.registration_no);
                  }
                   if(typeof(data.working_sector) != "undefined" && data.working_sector !== null) {
                    $('#working_sector').val(data.working_sector);
                  }

                  if(typeof(data.address) != "undefined" && data.address !== null) {
                    $('#address').val(data.address);
                  }

                  if(typeof(data.institute) != "undefined" && data.institute !== null) {
                    $('#student_institute').val(data.institute);
                  }
                  if(typeof(data.degree) != "undefined" && data.degree !== null) {
                    $('#student_degree').val(data.degree);

                    getBranches(data.degree,data.branch);
                  }
                  if(typeof(data.passout_year) != "undefined" && data.passout_year !== null) {
                    $('#passout_year').val(data.passout_year);
                  }

                  


              },
        });  
     }


      $("#employer_type").click(function() {
      $(".emp_agency_holder").show();
      $(".student_hoder").hide();

    });

  $("#agency_type").click(function() {
     $(".emp_agency_holder").show();
      $(".student_hoder").hide();
    });

    $("#student_type").click(function() {
      $(".emp_agency_holder").hide();
      $(".student_hoder").show();
    });


     <?php 
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        ?>
     

        var subdomain='<?php echo $subdomain[0];?>';
if(subdomain=="demo"||subdomain=="raisoni"||subdomain == "galgotias"){
 var token="{{ csrf_token() }}";
    /* Fetching Dropdown Values */
  $.ajax({
        url: '<?= URL::route('degreemaster-dropdown') ?>',
        type: 'POST',
        data :{type:'degree','_token':token},
        success: function (data) {
          if(data.type=="success"){
            $('#student_degree').html(data.data);
          }
        },
        dataType:'JSON'
    });
}

    $("#student_degree").change(function(){
       getBranches(this.value,0);

});


function getBranches(degree_id,branch_id){
   var token="{{ csrf_token() }}";
  $.ajax({
        url: '<?= URL::route('branchmaster-dropdown') ?>',
        type: 'POST',
        data :{type:'branch',degree_id:degree_id,'_token':token },
        success: function (data) {
          if(data.type=="success"){
            $('#student_branch').html(data.data);
            if(branch_id!=0){
              $('#student_branch').val(branch_id);
            }
          }
        },
        dataType:'JSON'
    });
}




 // send Ajax Request to update User data 
  $(".update_user").click(function(event) {

       $(".update_save").attr('disabled'); 
       var user_id=$("#user_id").val();
       event.preventDefault();
       var update_path="<?= URL::route('usermaster.update',array(':id')) ?>";
       update_path=update_path.replace(':id',user_id);
       var token="{{ csrf_token() }}";
       var type="post";

       $("#UserData").ajaxSubmit({
             url  : update_path,
             type : type,
             data : {'_token':token},
            beforeSubmit:function()
             {
               $(".clear_model").find('span').text('').end();
               $(".loadupdate").addClass('fa fa-spinner fa-spin');
               $(".update_user").attr('disabled','disabled'); 
             },
             success:function(data)
             {
               if(data.success==true)
               {
                 $('#addUsr').modal('hide');
                 $('#fontMatser_data').trigger("reset");
                 toastr.success('User Updated successfully');
                 oTable.ajax.reload();
                 $(".update_user").removeAttr('disabled'); 
                 $(".loadupdate").removeClass('fa fa-spinner fa-spin');  
               }
             },
             error:function(resobj)
             {
               $.each(resobj.responseJSON.errors, function(k,v){
                   $('#'+k+'_error').text(v);
                });
               $(".update_user").removeAttr('disabled'); 
               $(".loadupdate").removeClass('fa fa-spinner fa-spin');  
             },
       });      
  });
// Send Ajax Request to delete Record      
function deletepath(fm_id)
{
  var delete_path="<?=URL::route('usermaster.delete',array(':id')) ?>";
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
                     toastr.success('Deleted successfully');
                     oTable.ajax.reload();
                 }
               },
            });
       }

  });
}
function logout(user_id)
    {
     var logout_path="<?=URL::route('usermaster.logout',array(':id')) ?>";
     logout_path=logout_path.replace(':id',user_id);
     var token="{{ csrf_token() }}";
     // var token="{{ csrf_token() }}";
     var method_type="post";

      bootbox.confirm("Are you sure you want to logout?",function(result){  
          if(result)
           {
            $.ajax({
               url  : logout_path,
               type : 'POST',
               data : {'_token':token,'user_id':user_id},
               success:function(data){
                 if(data.success==true)
                 {
                     toastr.success('User logged out successfully');
                     oTable.ajax.reload();
                 }
               },
            });
           } 

        });
     }


     function getUserType(userType) {
//console.log(userType);
  switch (userType) {
  case 1:
   var userType = "Employer";
    break;
  case 2:
   var userType = "Agency";
    break;
  default:
  var  userType = "Student";
    break;
  }
  return userType;
}
 // get user information 
  function user_info(user_id)
  {
      var show_info="{{ URL::route('usermaster.show',array(':id')) }}";
      show_info=show_info.replace(':id',user_id);
      var token="{{ csrf_token() }}";
      $.get(show_info,{ '_token':token} ,function(data) {
         $('#myModal').modal('show'); 

         if(typeof(data.user_type) != "undefined" && data.user_type !== null) {
            $("#data-usertype").text(getUserType(parseInt(data.user_type)));
            $('#userType').show();
          }else{
            $('#userType').hide();
          }

         $("#data-username").text(data.username);
         if(typeof(data.l_name) != "undefined" && data.l_name !== null) {
            $("#data-fullname").text(data.fullname+' '+data.l_name);
          }else{
            $("#data-fullname").text(data.fullname);
          }
         $("#data-email").text(data.email_id);
         $("#data-mobile").text(data.mobile_no);
         $("#data-type").text(data.device_type);

         if(typeof(data.registration_no) != "undefined" && data.registration_no !== null) {
            $("#data-registration_no").text(data.registration_no);
            $('#registrationNo').show();
          }else{
            $('#registrationNo').hide();
          }

          if(typeof(data.working_sector) != "undefined" && data.working_sector !== null) {
            $("#data-working_sector").text(data.working_sector);
            $('#workingSector').show();
          }else{
            $('#workingSector').hide();
          }

          if(typeof(data.address) != "undefined" && data.address !== null) {
            $("#data-address").text(data.address);
            $('#addressView').show();
          }else{
            $('#addressView').hide();
          }

          if(typeof(data.institute) != "undefined" && data.institute !== null) {
            $("#data-institute").text(data.institute);
            $('#institute').show();
          }else{
            $('#institute').hide();
          }

          if(typeof(data.degree) != "undefined" && data.degree !== null) {
            $("#data-degree").text(data.degree);
            $('#degree').show();
          }else{
            $('#degree').hide();
          }

          if(typeof(data.branch) != "undefined" && data.branch !== null) {
            $("#data-branch").text(data.branch);
            $('#branch').show();
          }else{
            $('#branch').hide();
          }

          if(typeof(data.passout_year) != "undefined" && data.passout_year !== null) {
            $("#data-passout_year").text(data.passout_year);
            $('#passoutYear').show();
          }else{
            $('#passoutYear').hide();
          }

         $("#data-created").text(moment(data.created_at).format('DD-MM-YY h:mm a'));
         if(data.login_time)
         {
          $("#data-lastlogin").text(moment(data.login_time).format('DD-MM-YY h:mm a'));
         }
         else
         {
           $("#data-lastlogin").text('Never');
         }
         
       
      });
  }
       // get Role Name 
       var ajaxUrl="{{ URl::route('user-master.role.get') }}";
       var token="{{ csrf_token() }}";
       $.get(ajaxUrl,{'_token':token},function(data) {
       
       	 $.each(data,function(index, el) {
       	 	$("#roleId").append('<option value='+el.id+'>'+el.name+'</option>');
       	 });
       });
     
     // datatable
   var oTable = $('#example').DataTable( {
	      'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
	      "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [7, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('usermaster.index',['status'=>1]) ?>",
		    "aoColumns":[
          {mData: "rownum", bSortable:false},
          {
            mData: "username",
            bSortable:true,
            "sClass": "text-center",
           mRender:function(v,t,o){
          
             var act_html;
             var title=null;
             var icon=null;
            /* if(o['verify_by']==1)
             {
                title="Admin verified";
                icon='check-circle yellow';      
             }
             else*/
             if(o['is_verified']==1)
             {
                if(o['verify_by']==2){
                  title="Email verified";
                }else if(o['verify_by']==3){
                  title="Admin verified";
                }else{
                  title="SMS verified";
                }
                
                icon='check-circle green';      
             }
             else
             {
                title="Not verified";
                icon='close red';     
             }    
           return  act_html='<i class="fa-lg fa fa-'+icon+' green" data-toggle="tooltip" title="'+title+'" data-original-title="Email Verified"></i> '+o['username'];
              
           }
          
          },
          {mData: "fullname",bSortable:true,"sClass": "text-center"},
          <?php if($subdomain[0]=="mallareddyuniversity"){ ?>
          {mData:"organization_name",bSortable:true},
          <?php } ?>
          {mData: "email_id",bSortable:true,"sClass": "text-center"},
          {mData: "mobile_no",bSortable:true,"sClass": "text-center"},
          {mData: "device_type",bSortable:true,"sClass": "text-center"},
          {mData: "created_at",
              bSortable:true,
              "sClass": "text-center",  
              mRender:function(v)
              {
                  return moment(v).format('DD-MM-YY');
              } ,  
          },		
    			{
    				mData:"id",
    				bSortable:false,

    				mRender:function(v, t, o){
                        var act_html;
          
                     act_html ='@if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.show')) @if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.show'))<a onclick="user_info('+o['id']+')"><i class="fa fa-info-circle fa-lg blue"></i></a>&nbsp @endif @endif';
                     act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.edit')) &nbsp;&nbsp;<a onclick="user_edit('+o['id']+')"><i class="fa fa-edit fa-lg green"></i></a>&nbsp @endif @endif';
                     
                     act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.logout')) @if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.logout'))  &nbsp;&nbsp;<a onclick="logout('+o['id']+')" title="logout"><i class="fa fa-sign-out fa-lg green"></i></a> @endif @endif';

                     act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.destroy')) @if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.destroy'))  &nbsp;&nbsp;<a onclick="deletepath('+o['id']+')"><i class="fa fa-trash fa-lg red"></i></a> @endif @endif';

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

   var url="<?= URL::route('usermaster.index',['status'=>0])?>";
   oTable.ajax.url(url);
   oTable.ajax.reload();
   $('.loader').removeClass('hidden');
});
// read  active user
$("#success-pill").click(function(event) {

	var url="<?= URl::route('usermaster.index',['status'=>1])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	$('.loader').removeClass('hidden');
});	

// clear model data
$('.clear_model').on('hidden.bs.modal', function () {
    $(this).find("input,textarea,select").val('').end();
    $(this).find('span').text('').end();
});

// allow only number
$(".allow_number").keypress(function(h){
    var keyCode =h.which ? h.which : h.keyCode
       if (!(keyCode >= 48 && keyCode <= 57)) {
             return !1;
           }
 });

// allow only character
$(".allow_character").keypress(function(h) {
  
    var keyCode=h.which ? h.which :h.keyCode;
    if(!(keyCode>=97 && keyCode <=122) && !(keyCode>=65 && keyCode <=90) && !(keyCode>=32 && keyCode <=32))
    {
        return !1;
    } 
    
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