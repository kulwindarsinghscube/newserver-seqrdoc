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
                <h1 class="page-header"><i class="fa fa-fw fa-files-o"></i> Copy Templates
                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
                </h1>   
            </div>
        </div>
        <div class="">
          
            <div class="col-xs-12">
                <table id="master_table" class="table table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Instance</th>
                            <th>Template Maker</th>
                            <th>PDF2PDF</th>
                            <th>Custom</th>
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

  var url = "<?=route('copy-template.index',['status'=>'1'])?>";     

  var oTable = $('#master_table').DataTable({
      "bProcessing": false,
      "bServerSide": true,
      "autoWidth": true,
      "aaSorting": [
          [5, "desc"]
      ],
      lengthMenu: [
          [ 50, 100,200,500],
          [ '50','100','200','500']
      ],
      "sAjaxSource": url,
      "aoColumns": [
          { "mData": "rownum", bSortable:false,sWidth: "5%"},
          { "mData": "site_url",sWidth: "10%",bSortable: true,bVisible:true},
          { "mData": "template_count",sWidth: "10%",bSortable: true,bVisible:true},
          { "mData": "pdf2pdf_template_count",sWidth: "10%",bSortable: true,bVisible:true},
          { "mData": "custom_templates",sWidth: "10%",bSortable: true,bVisible:true},
          {
              mData:'site_id',
              bSortable: false,
              sWidth: "10%",
              sClass: "text-center",
              mRender: function(v, t, o) {

                  var site_url= o['site_url'];
                  var view_path_canvas = "<?=URL::route('copy-template.viewtemplates',array(':site_url'))?>";
                  view_path_canvas = view_path_canvas.replace(':site_url',site_url);

                  var view_path_canvas_pdf = "<?=URL::route('copy-template.viewtemplatespdf2pdf',array(':site_url'))?>";
                  view_path_canvas_pdf = view_path_canvas_pdf.replace(':site_url',site_url);

                 /* var view_path_pdf2pdf = "<?=URL::route('copy-template.viewtemplatespdf2pdf',array(':site_url'))?>";
                  view_path_pdf2pdf = view_path_pdf2pdf.replace(':site_url',site_url);
                &nbsp;&nbsp;<a href="'+view_path_pdf2pdf+'" title="View Templates PDF2PDF"><i class="fa fa-file-pdf-o fa-lg red"></i></a>
                */
                var act_html =''; 
                if(o['template_count']>0){
                   act_html = '<a href="'+view_path_canvas+'" title="View Template Maker Templates"><i class="fa fa-clipboard fa-lg green"></i></a>';
                }

                if(o['pdf2pdf_template_count']>0){
                   act_html += '&nbsp;&nbsp;<a href="'+view_path_canvas_pdf+'" title="View PDF2PDF Templates"><i class="fa fa-file-pdf-o fa-lg red"></i></a>';
                }
                 /* var act_html = '<a href="'+view_path_canvas+'" title="View Template Maker Templates"><i class="fa fa-list-ul fa-lg green"></i></a>&nbsp;&nbsp;<a href="'+view_path_canvas_pdf+'" title="View PDF2PDF Templates"><i class="fa fa-file-pdf-o fa-lg green"></i></a>';*/
                 if(act_html==""){
                  act_html = '<i class="fa fa-ban fa-lg blue"></i>';
                 }

                  return act_html;
              }
          },
           { "mData": "site_id",sWidth: "15%",bSortable: false,bVisible:false},
      ],
    
  });
  oTable.on('draw',function(){
    
    $(".loader").addClass('hidden');
  });             
          

    </script>
    <!-- AJAX FOR DATA TABLE  END--
@stop



