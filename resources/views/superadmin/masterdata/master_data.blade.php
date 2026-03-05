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
            <h1 class="page-header"><i class="fa fa-fw fa-building"></i> Sites Superdata
            <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
            </h1>   
        </div>
    </div>
    <div class="">
         <ul class="nav nav-pills" id="pills-filter">
        <!--  <li class="active"><a id="success-pill" data-toggle="pill" href="#success" class="success"><i class="fa fa-fw fa-lg fa-check"></i> Active Site </a></li>
           <li><a id="fail-pill"data-toggle="pill" href="#failed" class="failed"><i class="fa fa-fw fa-lg fa-times"></i> Inactive Site</a></li>
         
            <li style="float: right;">
                    <a href="" class="btn btn-theme" title="Create site" style="background-color: #0052CC;color:white"><i class="fa fa-plus"></i> Add site
                    </a>
            </li> -->
 
                            <li style="float: right;">
                                <a href="{{ route('masterData.excelExport') }}" style="background: #0052CC;padding-top:5px; color: #fff;border-radius: 2px;box-shadow: 2px 2px 5px #999;border: 1px solid transparent;height: 35px;" id="report" ><i class="fa fa-file"></i> Export Data</a> 
                            </li>
                                  </ul>

        <div class="col-xs-12">
            <table id="master_table" class="table table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Instance name</th>
                        <th>Total Templates</th>
                        <th>Active documents</th>
                        <th>Inactive documents</th>
                        <th>Total verifier</th>
                        <th>Total scanned</th>
                        <th>Last generation date</th>
                        
                        
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
<script>
	var url = "<?=route('superadmin.masterdata',['status'=>'1'])?>";     
	var oTable = $('#master_table').DataTable({
      "bProcessing": false,
      "bServerSide": true,
      "autoWidth": true,
      "aaSorting": [
          [7, "desc"]
      ],
      lengthMenu: [
          [ 25, 50,100,500],
          [ '25','50','100','500']
      ],
      "sAjaxSource": url,
      "aoColumns": [
          {mData: "rownum", bSortable:false,sWidth: "5%"},
          { "mData": "sites_name",sWidth: "10%",bSortable: true,bVisible:true},
        
           { "mData": "template_number",sWidth: "15%",bSortable: true,bVisible:true},
           { "mData": "active_documents",sWidth: "15%",bSortable: true,bVisible:true},
           { "mData": "inactive_documents",sWidth: "15%",bSortable: true,bVisible:true},
           { "mData": "total_verifier",sWidth: "15%",bSortable: true,bVisible:true},
           { "mData": "total_scanned",sWidth: "15%",bSortable: true,bVisible:true},
           { "mData": "last_genration_date",sWidth: "45%",bSortable: true,bVisible:true},
      ],
    
  });
  oTable.on('draw',function(){
    
    $(".loader").addClass('hidden');
  });  
</script>

@stop