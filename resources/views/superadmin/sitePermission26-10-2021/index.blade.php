@extends('superadmin.layout.layout')
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
@section('content')
   <div class="container">
       <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header"><i class="fa fa-fw fa-building"></i> Site Management
                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
                </h1>   
            </div>
        </div>
        <div class="">
            <ul class="nav nav-pills" id="pills-filter">
              <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Site </a></li>
               <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Site</a></li>
             
                <li style="float: right;">
                        <a href="<?= route('website-permission.create') ?>" class="btn btn-theme" title="Create site" style="background-color: #0052CC;color:white"><i class="fa fa-plus"></i> Add site
                        </a>
                </li>
               
            </ul>
            <div class="col-xs-12">
                <table id="master_table" class="table table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Site_Url</th>
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
@stop
@section('script')

    <!-- AJAX FOR DATA TABLE  START-->
<script type="text/javascript">
          // Send Ajax Request to delete Record      
function deletepath(fm_id)
{
  var delete_path="<?=URL::route('website-permission.destroy',array(':id')) ?>";
  delete_path=delete_path.replace(':id',fm_id);
  var token="{{ csrf_token() }}";
  var method_type="get";

  bootbox.confirm("<div style='color:grey;font-size:14px;font-weight:8px;'>1) Removes entry from site table. <br>2) Removes storage space from <span style='color:red;'>AWS</span> for particular instance. <br>3) Deletes public folder.<br> 4) Deletes database. </div><br><h4>Are you sure you want to delete? </h4>",function(result){  
       if(result)
       {
            $.ajax({
               url:delete_path,
               type:method_type,
               data:{'_token':token},
               success:function(data){  
                 if(data.success==true)
                 {
                     toastr.success('Site Deleted Successfully');
                     oTable.ajax.reload();
                 }
               },
            });
       }

  });
}
  var url = "<?=route('website-permission.index',['status'=>'1'])?>";     

  var oTable = $('#master_table').DataTable({
      "bProcessing": false,
      "bServerSide": true,
      "autoWidth": true,
      "aaSorting": [
          [2, "desc"]
      ],
      lengthMenu: [
          [ 50, 100,200,500],
          [ '50','100','200','500']
      ],
      "sAjaxSource": url,
      "aoColumns": [
          {mData: "rownum", bSortable:false,sWidth: "5%"},
          { "mData": "site_url",sWidth: "10%",bSortable: true,bVisible:true},
          {
              mData:'site_id',
              bSortable: false,
              sWidth: "10%",
              sClass: "text-center",
              mRender: function(v, t, o) {

                  var role_id= o['site_id'];
                  var edit_path = "<?=URL::route('website-permission.edit',array(':role_id'))?>";
                  edit_path = edit_path.replace(':role_id',role_id);
                
                  var act_html = '<a href="'+edit_path+'" title="Edit Role"><i class="fa fa-edit fa-lg green"></i></a>';
                  act_html+='&nbsp;&nbsp;&nbsp;<a onclick="deletepath('+o['site_id']+')"><i class="fa fa-trash fa-lg red"></i></a>';
                  return act_html;
              }
          },
           { "mData": "updated_at",sWidth: "15%",bSortable: false,bVisible:false},
      ],
    
  });
  oTable.on('draw',function(){
    
    $(".loader").addClass('hidden');
  });             
          
$('#fail-pill').click(function(){
   var url="<?= URL::route('website-permission.index',['status'=>0])?>";
   oTable.ajax.url(url);
   oTable.ajax.reload();
   $('.loader').removeClass('hidden');
});
// read  active user
// read  active user
$("#success-pill").click(function(event) {

    var url="<?= URl::route('website-permission.index',['status'=>1])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
    $('.loader').removeClass('hidden');
});   

    </script>
    <!-- AJAX FOR DATA TABLE  END--
@stop



