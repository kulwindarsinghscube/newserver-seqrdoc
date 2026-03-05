@extends('admin.layout.layout')
@section('content')
<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i> Process Excel
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('processexcel') }}</ol>
					<i class="fa fa-info-circle iconModalCss" title="User Manual" id="processExcelClick"></i>
				</h1>
				
			</div>
		</div>
        
         @if(App\Helpers\SitePermissionCheck::isPermitted('processExcel.merge'))
         @if(App\Helpers\RolePermissionCheck::isPermitted('processExcel.merge'))
		<form method="post" id="processExcelForm" class="form-horizontal" enctype="multipart/form-data" action="<?=route('processExcel.merge')?>">
			<input type="hidden" name="func" id="func" value="processExcel"> 
			<input type="hidden" name="_token" value="{{csrf_token()}}"> 
		  	<div class="form-group">
		    	<label class="control-label col-sm-2" for="excel">Upload Excel:</label>
		    	<div class="col-sm-10">
		      		<input type="file" class="form-control" id="excel" name="excel_data">
		      		<span id="excel_data_error" class="help-inline text-danger"><?=$errors->first('excel_data')?></span>
		    	</div>
		  	</div>
		  
		  	<div class="form-group"> 
		    	<div class="col-sm-offset-2 col-sm-10">
		    		<?php
						$domain = \Request::getHost();
	        			$subdomain = explode('.', $domain);
        			?>
		    		<div align="center"><b>Please Click <a href="{{asset('backend/processExcel/sampleExcel.xlsx')}}" download>HERE</a> To Download Sample Excel</b></div>
		      		<button type="submit" class="btn btn-primary">Submit</button>

		    	</div>
		  	</div>
		</form>
		@endif
		@endif
		
		<div id="download_link">
			
		</div>
		<div id="divLoading" style="margin: 0px; padding: 0px; position: fixed; right: 0px; top: 0px; width: 100%; height: 100%; background-color: rgb(102, 102, 102); z-index: 30001; opacity: 0.8;display: none;">
			<p style="position: absolute; color: White; top: 20%; left: 30%;">
			Excel merge are in process please wait...
					<img src="https://thumbs.gfycat.com/ImprobablePertinentGraysquirrel-max-1mb.gif" height="150" width="150">
			</p>
		</div>
		<div class="">
			<table id="example" class="table table-hover display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>#</th>
						<th>Raw Excel</th>
						<th>Processed Excel</th>
						<th>Total Unique Records</th>
						<th>Date Time</th>
						<th>Status</th>
						
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
<script type="text/javascript">
var oTable = $('#example').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [4, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('processExcel.index')?>",
        "aoColumns":[
		{mData: "rownum", bSortable:false},
		{mData: "raw_excel",
			mRender: function(v, t, o) {
				<?php
					$domain = \Request::getHost();
        			$subdomain = explode('.', $domain);
        			if($get_file_aws_local_flag->file_aws_local == '1'){
        				$excel_directory = \Config::get('constant.amazone_path').$subdomain[0].'/backend/processExcel/';
        			}
        			else{
        				$excel_directory = 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/backend/processExcel/';
        			}

				?>
				var excel_path = "<?=$excel_directory?>"+o.raw_excel;
				
				return "<a data-toggle='tooltip' data-placement='right' title='Please click to download excel file' class='btn btn-success' href='"+excel_path+"'>"+o.raw_excel+"</a>";
			}	
		},
		{mData: "processed_excel",
			mRender: function(v, t, o) {
				<?php
					$domain = \Request::getHost();
        			$subdomain = explode('.', $domain);
        			if($get_file_aws_local_flag->file_aws_local == '1'){
        				$excel_directory = \Config::get('constant.amazone_path').$subdomain[0].'/backend/processExcel/';
        			}
        			else{
        				$excel_directory = 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/backend/processExcel/';
        			}

				?>
				var excel_path = "<?=$excel_directory?>"+o.processed_excel;
				
				return "<a data-toggle='tooltip' data-placement='right' title='Please click to download excel file' class='btn btn-success' href='"+excel_path+"'>"+o.processed_excel+"</a>";
			}
		},
		{mData: "total_unique_records"},
		{mData: "date_time"},
		{mData: "status"},

	],
	"createdRow": function( row, data, dataIndex ) {

		if(data['status'] == 'Active'){
			$(row).addClass( 'active-student' );
		}else{
			$(row).addClass( 'inactive-student' );
		}
	}

});
$('#processExcelForm').validate({

	rules:{
		excel_data:{'required':true, extension:'xls|xlsx'}
	},
	messages:{
		excel_data:{
			required:'please choose file',
			extension:'please select only excel file',
		}
	},
	submitHandler: function(form){
		
		$('#processExcelForm').ajaxSubmit({
			target:'#response',
			beforeSubmit:function(formData,jqform, options){
				$('#divLoading').show();
			},clearForm:false,dataType:'json',success:function(resObj){
				// console.log(resObj.data[0].type);
				if(resObj.data[0].type == 'success'){
					
					$('#download_link').empty();
					toastr["success"](resObj.data[0].message);
					$('#download_link').html('<a href="'+resObj.data[0].link+'">Download </a>')
					$('#divLoading').hide();
					

				}else{

					$('#divLoading').hide();
					toastr["error"](resObj.data[0].message);
				}
			}

		});
	}
})
$('#download_link').click(function(){
	setTimeout(function(){ window.location.reload(); }, 800);
});
</script>

@stop
