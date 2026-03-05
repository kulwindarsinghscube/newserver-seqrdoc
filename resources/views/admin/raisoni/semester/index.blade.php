@extends('admin.layout.layout')
@section('content')
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header"><i class="fa fa-list-ul" aria-hidden="true"></i> Semester
        <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;">{{ Breadcrumbs::render('semester') }}</ol>
        <i class="fa fa-info-circle iconModalCss" title="User Manual" id="semesterMasterClick"></i>
	</h1>

      </div>
    </div>

    <div class="">

      <div class="col-xs-12">
          <div class="row">
          <div class="col-lg-12" style="text-align: right;">
            @if(App\Helpers\SitePermissionCheck::isPermitted('semester.store'))
            @if(App\Helpers\RolePermissionCheck::isPermitted('semester.store'))
              <button class="btn btn-theme" onclick="AddSemester()"><i class="fa fa-plus"></i> Add Semester</button>
            @endif
            @endif
          </div>
        </div>
        <table id="example" class="table table-hover" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Semester Name</th>
              <th>Full Name</th>
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
@include('admin.raisoni.semester.model')
<!--  // End User Create Model -->	

@stop
@section('script')

   <script type="text/javascript">
      
  // add semester record
  function AddSemester()
  { 
      $('.update_semester').hide();
     $('#addSemester').modal('show');
     $(".add_semester").show();
     $('.add_semester').click(function(event) {
       
       $(".add_semester").attr('disabled','disabled'); 
       event.preventDefault();
       var create_path="<?= URL::route('semester.store') ?>";
       var token="{{ csrf_token() }}";
       var method_type="post";

       $("#semesterData").ajaxSubmit({
             url  : create_path,
             type : method_type,
             data : {'_token':token},
             beforeSubmit:function()
             {
               $(".clear_model").find('span').text('').end();
               $(".semestersave").addClass('fa fa-spinner fa-spin');
             },
             success:function(data)
             {
               if(data.success==true)
               {
                  $('#addSemester').modal('hide');
                  toastr.success('Semester  successfully added');
                  oTable.ajax.reload();
                  $(".add_semester").removeAttr('disabled');
                  $(".semestersave").removeClass('fa fa-spinner fa-spin');  

               }
             },
             error:function(resobj)
             {
               $.each(resobj.responseJSON.errors, function(k,v){
                   $('#'+k+'_error').text(v);
                });
               $(".add_semester").removeAttr('disabled'); 
               $(".semestersave").removeClass('fa fa-spinner fa-spin');  
             },
       });      
    });

  } 
    // send Ajax Request to featch User data and show model 
    function semester_edit(semester_id)
     {

         $(".clear_model").find('span').text('').end();
         $('#addSemester').modal('show');
         $(".add_semester").hide();
         $('.update_semester').show();
          var edit_path="<?=URL::route('semester.edit',array(':id')) ?>";
          edit_path=edit_path.replace(':id',semester_id);
          var method_type="GET";
          var token="{{ csrf_token() }}";
          $.ajax({
              
               url : edit_path,
              type : method_type,
              data : {'_token':token},

              success:function(data)
              {
                 $('#addUsr').modal('addSemester');
                 $("#semester_id").val(data.id);
                 $("#semester_name").val(data.semester_name);
                 $("#semester_full_name").val(data.semester_full_name);
              },
        });  
     }
 // send Ajax Request to update User data 
  $(".update_semester").click(function(event) {

       $(".update_save").attr('disabled'); 
       var semester_id=$("#semester_id").val();
       event.preventDefault();
       var update_path="<?= URL::route('semester.update',array(':id')) ?>";
       update_path=update_path.replace(':id',semester_id);
       var token="{{ csrf_token() }}";
       var type="post";

       $("#semesterData").ajaxSubmit({
             url  : update_path,
             type : type,
             data : {'_token':token},
            beforeSubmit:function()
             {
               $(".clear_model").find('span').text('').end();
               $(".semesterupdate").addClass('fa fa-spinner fa-spin');
               $(".update_semester").attr('disabled','disabled'); 
             },
             success:function(data)
             {
               if(data.success==true)
               {
                 $('#addSemester').modal('hide');
                 toastr.success('Semester Updated successfully');
                 oTable.ajax.reload();
                 $(".update_semester").removeAttr('disabled'); 
                 $(".semesterupdate").removeClass('fa fa-spinner fa-spin');  
               }
             },
             error:function(resobj)
             {
               $.each(resobj.responseJSON.errors, function(k,v){
                   $('#'+k+'_error').text(v);
                });
               $(".update_semester").removeAttr('disabled'); 
               $(".semesterupdate").removeClass('fa fa-spinner fa-spin');  
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
        [5, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('semester.index') ?>",
		    "aoColumns":[
          {mData: "rownum", bSortable:false},
          {
            mData: "semester_name",
            bSortable:true,
            "sClass": "text-center",
          },
          {mData: "semester_full_name",bSortable:true,"sClass": "text-center"},
          {mData: "created_at",bSortable:true,"sClass": "text-center"},
          {mData: "updated_at",bSortable:true,"sClass": "text-center"},		
    			{
    				mData:"id",
    				bSortable:false,

    				mRender:function(v, t, o){
                    var act_html = '';
          
                     act_html +='@if(App\Helpers\SitePermissionCheck::isPermitted('semester.edit')) @if(App\Helpers\RolePermissionCheck::isPermitted('semester.edit')) &nbsp;&nbsp;<a onclick="semester_edit('+o['id']+')" title="Edit"><i class="fa fa-edit fa-lg green"></i></a>&nbsp @endif @endif';

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
    margin-top: 20px;
}
</style>

@stop