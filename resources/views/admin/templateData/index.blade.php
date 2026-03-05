@extends('admin.layout.layout')
@section('content')
<div class="container">
  <div class="col-xs-12">
    <div class="clearfix">
    	<div id="">
      	<div class="container-fluid">
      		<div class="row">
      			<div class="col-lg-12">
      				<h1 class="page-header"><i class="fa fa-fw fa-lock"></i> Template Data
      				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('templatedata') }}</ol>
              <i class="fa fa-info-circle iconModalCss" title="User Manual" id="templateDataReportClick"></i>
      				</h1>
      			</div>
      		</div>
      		<div class="">
      			<table id="example" class="table table-hover display" cellspacing="0" width="100%">
      				<thead>
      					<tr>
                  <th>#</th>
                  <th>Template Name</th>
                  <th>Status</th>
      						<th>Active Documents</th>
      						<th>Inactive Documents</th>
                  <th>Data last used</th>
      						<th>Active Scanned</th>
      						<th>Inactive Scanned</th>
      					</tr>
      				</thead>
      				<tbody>
                  @foreach($template_data as $key=>$value)
                      <tr>
                        <td>{{$value['id']}}</td>
                        <td>{{$value['template_name']}}</td>
                        <td>{{$value['status']}}</td>
                        <td>{{$value['active_count']}}</td>
                        <td>{{$value['deactive_count']}}</td>
                        <td>{{$value['updated_on']}}</td>
                        <td>{{$value['active']}}</td>
                        <td>{{$value['deactive']}}</td>
                      </tr>
                  @endforeach
      				</tbody>
      			</table>
      		</div>
      	</div>
      </div>
    </div>
  </div>
</div>
@stop

@section('script')
<script type="text/javascript">
var oTable = $('#example').DataTable( {
	'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
     "paging": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "processing": true,
      "bPaginate": true,
      "lengthMenu": [10,25,50,75,100],
      "aaSorting": [
      [0, "asc"],
      ],
      "aoColumnDefs":[
        {aTargets:[4],bSortable:false}
      ]
});
</script>	
@stop

