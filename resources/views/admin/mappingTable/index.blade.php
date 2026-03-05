@extends('admin.layout.layout')
@section('content')
<div id="">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-6">
				<h3 class="page-header">Database Name:- @if($db_details){{ $db_details['db_name'] }}@endif</h3>
			</div>
			<div class="col-lg-6">
				<h3 class="page-header">Table Name:- @if($db_details){{ $db_details['table_name'] }}@endif</h3>
			</div>
				
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i> Table Of Mapped Database
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;"></ol>
				</h1>
			</div>
			<div id="iframe">
				
			</div>
		</div>
		<div class="">
			
			<!-- table view  -->
			<table id="example" class="table table-hover display" cellspacing="0" width="100%" data-page-length='10'>
				<thead>
                    <tr>
                    	<th><input type="checkbox" id="selectall" name="all"/></th>
                    	
                        @foreach($columns as $value)
                        	<th>{{ ucwords(str_replace('_', ' ', $value)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody>
			</table>
		</div>
		<div class="row">
			
			<button class="btn btn-theme" id="print_above"> Print All Above</button>
			<button class="btn btn-theme" id="print_selected"> Print Selected</button>&nbsp;&nbsp;&nbsp;<button class="btn btn-danger" id="count">0</button>
			<br>
		</div>
	</div>
</div>
@stop
@section('script')
<script type="text/javascript">
   
    var template_id = <?= $template_id ?>;
    var token = "{{ csrf_token() }}";
    // url for datatable
    var url = '{{ route("template-map.index", ":template_id") }}';
	url = url.replace(':template_id', template_id);

	// php columns array converting into jquery variable
	var columns_js = new Array();
	columns_js.push('id');
	<?php foreach($columns as $key => $val){ ?>
        columns_js.push('{{ $val }}');
    <?php } ?>

	//generating dynamic cloumns 
	var dynamicColumns = [];
    var i = 0;
    
    // console.log(obj);
    $.each(columns_js, function (key, value) {
        obj = { "mData": value,bSortable: false };
        
        dynamicColumns[i] = obj;
        i++;
    });
    
    // Datatable for mapping database
    var oTable = $('#example').dataTable({
        'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "sAjaxSource": url,
        "oLanguage": {
          "sLengthMenu": "Display _MENU_ records"
        },
        'columnDefs': [{
         'targets': 0,
         'searchable':false,
         'orderable':false,
         'className': 'dt-body-center',
         'render': function (data, type, full, meta){
             return '<input type="checkbox" id="chk_' + data + '" name="id[]" class="checkbox" value="' + data + '"/>';
         }
      }],
    	"aoColumns": dynamicColumns,
    	
        fnPreDrawCallback : function() { 
            $("#spin").show();
    
        },
        fnDrawCallback : function (oSettings) {
            $("#spin").hide();
        }
    }); 

     var allPages = oTable.fnGetNodes();

    
	$('#selectall').click(function(event) {  //on click 
        if(this.checked) { // check select status
            $('.checkbox').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"               
            });
        }else{
            $('.checkbox').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"                       
            });         
        }
    });

    function checkbox()
    {
    	var check_ele = $('#example tbody input[type=checkbox]:checked');
        var check_id = [];
        $.each(check_ele, function(i, ele) {
            check_id.push($(ele).val());
        });
        $("#count").html(check_id.length);
    }
	
    $('body').on('click', 'table tr #selectall',function(){
    	checkbox();
    });

    $('body').on('click', 'table tbody .checkbox',function(){
    	checkbox()
    });

    $('#print_above').click(function(){
    	$.ajax({
	        url: "{{ URL::route('template-map.print') }}",
	        type: 'get',
	        dataType:'json',
	        data:{
	            template_id:template_id,
                print_type:'pdf',
	            status:'print_all',
	            _token:token
	        },
	        success: function(resp) {
	        	if (resp.success==true) {
                    toastr.success(resp.message);
                }
                else
                {
                    toastr.error(resp.message);
                }
	        }
	    });    
    });

    $('#print_selected').click(function(){

    	var check_ele = $('#example tbody input[type=checkbox]:checked');
        var check_id = [];
        $.each(check_ele, function(i, ele) {
            check_id.push($(ele).val());
        });

        if (check_id.length == 0) {
        	toastr.error('Please Select Checkbox');
        }
        else
        {
        	$.ajax({
	        url: "{{ URL::route('template-map.print') }}",
	        type: 'get',
	        dataType:'json',
	        data:{
	            template_id:template_id,
                print_type:'pdf',
	            status:'print_selected',
	            id:check_id,
	            _token:token
	        },
            success: function(resp) {
                if (resp.success==true) {
                    toastr.success(resp.message);
                }
                else
                {
                    toastr.error(resp.message);
                }
            }
	       
	    	});  
        }
    });

</script>
@stop


