@extends('admin.layout.layout')
@section('content')
<style type="text/css">
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
  margin-left: 20px;
}

.list-group-item.border-0 {
    border: none;
    padding-left: 0; 
    padding-right: 0; 
    padding: 5px 10px;
}
.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
 /*range css*/
$range-width: 100% !default;

.range-slider {
  width: $range-width;
}
.range-slider__range {
  -webkit-appearance: none;
  width: calc(100% - (#{$range-label-width + 13px}));
  height: $range-track-height;
  border-radius: 5px;
  background: $range-track-color;
  outline: none;
  padding: 0;
  margin: 0;

  &::-webkit-slider-thumb {
    appearance: none;
    width: $range-handle-size;
    height: $range-handle-size;
    border-radius: 50%;
    background: $range-handle-color;
    cursor: pointer;
    transition: background .15s ease-in-out;

    &:hover {
      background: $range-handle-color-hover;
    }
  }

  &:active::-webkit-slider-thumb {
    background: $range-handle-color-hover;
  }

  &::-moz-range-thumb {
    width: $range-handle-size;
    height: $range-handle-size;
    border: 0;
    border-radius: 50%;
    background: $range-handle-color;
    cursor: pointer;
    transition: background .15s ease-in-out;

    &:hover {
      background: $range-handle-color-hover;
    }
  }

  &:active::-moz-range-thumb {
    background: $range-handle-color-hover;
  }

  &:focus {
    
    &::-webkit-slider-thumb {
      box-shadow: 0 0 0 3px $shade-0,
                  0 0 0 6px $teal;
    }
  }
}
.range-slider__value {
  display: inline-block;
  position: relative;
  width: $range-label-width;
  color: $shade-0;
  line-height: 20px;
  text-align: center;
  border-radius: 3px;
  background: $range-label-color;
  padding: 5px 10px;
  margin-left: 8px;

  &:after {
    position: absolute;
    top: 8px;
    left: -7px;
    width: 0;
    height: 0;
    border-top: 7px solid transparent;
    border-right: 7px solid $range-label-color;
    border-bottom: 7px solid transparent;
    content: '';
  }
}
blockquote {
    padding: 2px 10px;
    margin: 0 0 6px;
    font-size: 14px;
    border-left: 5px solid #0052CC;
    background-color: #e8eff9;
}
</style>
<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i>  Branch
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('Branch') }}</ol>
				</h1>
				
			</div>
		</div>
      
        <div class="">
    <!-- panel panel-default -->
     <div>
        @if(App\Helpers\SitePermissionCheck::isPermitted('add-branch'))
        @if(App\Helpers\RolePermissionCheck::isPermitted('add-branch')) 
        <a href="#" class="btn btn-primary" id="btn_add_batch" >Add Branch</a>
        @endif
        @endif 

          <!-- <a href="#" class="btn btn-primary" id="btn_add_batch" >Add Branch</a>
          <a href="#" class="btn btn-primary" role="button" id="UploadExcel">Upload Excel </a> -->

        
     </div>


     <!-- Approve/Reject Modal -->
     <div class="modal fade" id="approveRejectModal" tabindex="-1" role="dialog" aria-labelledby="approveRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="approveRejectModalLabel">Update status </h2>
              <button type="button" class="close " data-dismiss="modal" aria-label="Close" >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <ul id="statusList" class="list-group">
              </ul>
              <form id="statusUpdateForm">
                @csrf
                <input type ="hidden" id="updateRecordId" name="updateRecordId">
                <div  class="form-group">
                    <label for="statusSelect">Update Status</label>
                    <select id="statusSelect" class="form-control mb-3" name="newStatus" id="newStatus">
                      <option value="">Select Status</option>
                      <option value="Send For Approval">Send For Approval</option>
                      <!-- <option value="Approved">Approved</option>
                      <option value="Rejected">Rejected</option>
                      <option value="Correction">Correction</option> -->
                    </select>
                </div>
                <div id="contentBox" class="form-group" style="display: none;">
                  <label for="statusComments">Comments</label>
                  <textarea id="statusComments" class="form-control" rows="3" name="comment" placeholder="Enter comments here..."></textarea>
                </div>
                  <button type="submit" class="btn btn-success" id='approveBtn'> Update Status</button>
              </form>
             
            </div>
          </div>
        </div>
      </div>

     <!-- Upload file modal -->
     <div class="modal fade" id="fileModal" tabindex="-1" role="dialog" aria-labelledby="fileModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="fileModalLabel">Upload  File</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="fileForm" enctype="multipart/form-data">
          @csrf
          <!-- Dropdown for selecting batch -->
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
      

          <div class="form-group">
            <label for="batchSelect">Select Batch</label>
            <select id="batchSelect" name="batch_id" class="form-control" >
             
            </select>
          </div>
          
         
          <div class="form-group">
            <label for="excelFile"> File</label>
            <input type="file" id="excelFile" name="excel_file" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-success" id="btn_updfile">Upload</button>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- pdf generation modal -->
 <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLable" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="pdfModalLable1">Generate  File</h3>
        <h4 class="modal-title" style="display: none;"id="pdfModalLable2">Processing... Please wait ..... <img src="/backend/images/loading.gif"></h4>
        <h4 class="modal-title" id="pdfModalLable3" style="display: none;">PDF is ready to download. </h4>
        <button type="button" class="close " data-dismiss="modal" aria-label="Close" id="btn_close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <!--Custom Loader Start-->
          <input type="hidden" name="loader_token" id="loader_token" value="0"/>
          <input type="hidden" name="loaderFile" id="loaderFile" value="0"/>
          <div class="form-group" id="process" style="display:none;">
              <div class="progress">
              <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 1%;">1%
              </div>
            </div>
              </div>
      <div id="downloadLink" style="margin-bottom: 10px;">
      </div>
          <div class="form-group clearfix" id="loaderDiv" style="display: none;">
									
									<table class="table" style="max-width: 500px;border: 1px solid rgb(219 219 219) !important;">
										<tr><td style="width: 60%;">Generated Certificates Count</td><td id="generatedCertificates" style="width: 40%;padding-left: 10px;"></td></tr>
										<tr><td style="width: 60%;">Pending Certificates Count</td><td id="pendingCertificates" style="width: 40%;padding-left: 10px;"></td></tr>
										<tr><td style="width: 60%;">Total Certificates To Generate Count</td><td id="recordsToGenerate" style="width: 40%;padding-left: 10px;"></td></tr>

										<tr id="predictedTimeDiv" style="display: none;"><td id="predictedTimeText" style="width: 60%;">Approx. Completion Time </td><td id="predictedTime" style="width: 40%;padding-left: 10px;"></td></tr>
										<tr id="totalTimeGenerationDiv" style="display: none;"><td id="totalTimeGenerationText" style="width: 60%;">Total Time For Generation </td><td id="totalTimeGenerationTime" style="width: 40%;padding-left: 10px;"></td></tr>
									</table>
				   </div>
        <div id="pdfModalForm">
        <form id="pdfForm" enctype="multipart/form-data">
          @csrf
      
          <input type="hidden" name="_token" value="{{ csrf_token() }}" id="token">
          <input type="hidden" name ="batchIdtogenerate" id="batchIdtogenerate" value="">
          <div class="form-group">
            <label for="selecttype">Select type</label>
            <select id="selecttype" name="pdf_type" class="form-control" >
             <option value="1">Preview</option>
             <option value="0">Live</option>
            </select>
          </div>
          <button type="submit" class="btn btn-success" id="generate_pdf">Generate</button>
        </form>

        
        </div>
      </div>
    </div>
  </div>
</div>


     <!--  Branch Modal -->
<div class="modal fade" id="BranchModal" tabindex="-1" role="dialog" aria-labelledby="batchModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="batchModalLabel">Add Branch</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="BranchForm">
          @csrf
          <input type="hidden" id="batchId" name="id">
          <div class="form-group">
            <label for="BranchName">Branch Name</label>
            <input type="text" class="form-control" id="BranchName" name="name" required>
          </div>
      
          <button type="submit" class="btn btn-success">Save</button>
        </form>
      </div>
    </div>
  </div>
</div>


@if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

       <div class='mt-3'>
        <table class="table align-middle table-row-dashed fs-7 gy-5" id="tableYajra">
              <thead>
                  <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                      <th id="th">SR NO</th>
                      <th id="th">NAME</th>
                      <th id="th">By</th>
                      <th id="th">Date</th>
                      <th id="th">ACTIONS</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
       </div>
        
		    </div>

 		
		
	</div>
</div>
@stop
@section('script')
<script type="text/javascript">
 
 $(document).ready(function() {
            var table = $('#tableYajra').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                url: "{{ route('ksg-branch.datatable') }}",
                method: 'POST',
                data: {
                        approve_flag: 0,
                    },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
               },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                  
                 
                    {
                        data: 'admin_name',
                        name: 'admin_name'
                    },
                    {
                        data: 'formatted_date',
                        name: 'formatted_date'
                    },
                 
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true,

                    }
                ],
                rowCallback: function(row, data, index) {
                    var api = this.api();
                    var startIndex = api.page() * api.page.len();
                    var rowNum = startIndex + index + 1;
                    $(row).find('td:eq(0)').html(rowNum);
                }
            });

            setTimeout(function() {
                $("div.alert-success").remove();
            }, 3000);









    $(document).on('click', '#btn_add_batch', function() {
        $('#BranchForm').trigger("reset");
        $("#BranchModal").modal("show");
    })


$('#BranchForm').on('submit', function(event) {
        event.preventDefault();        
        var url = $('#batchId').val() ? "{{ route('update-branch') }}" : "{{ route('add-branch') }}";
        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(data) {
              if(data.success) {
                    toastr.success(data.success); 
                    $('#BranchModal').modal('hide');
                    $('#BranchForm').trigger("reset");
                    $('#batchModalLabel').text('Add Batch'); 
                    $('#tableYajra').DataTable().ajax.reload();
                  }
                  else if(data.error){
                    toastr.error(data.error); 
                  }
                  else {
                        toastr.error('An error occurred while deleting the branch.');
                    }
                 
            },
            error: function(xhr) {
                toastr.error('An error occurred while processing the branch.');
            }
        });
    });
   

    //edit data
    $('#tableYajra').on('click', '.editBranch', function() {
        var id = $(this).data('id'); 
        // alert(id);
        $('#batchId').val();
        $('#BranchName').val();

        $.ajax({
            url: "{{ route('edit-branch', '') }}/" + id,
            method: 'GET',
            success: function(response) {
                $('#batchId').val(response.id);
                $('#BranchName').val(response.name);
                $('#batchModalLabel').text('Edit Batch'); 
                $('#BranchModal').modal('show');
            },
            error: function(xhr) {
                toastr.error('An error occurred while fetching the branch details.');
            }
        });

    });

    $('body').on('click', '.DeleteBranch', function () {
     
     var id = $(this).data("id");
     var userConfirmed = confirm("Are you sure you want to delete this Batch!");
     var url = "{{ url('delete-branch') }}/" + id;
     if(userConfirmed){
             $.ajax({
                 type: "get",
                 url: url,
                 success: function (data) {
                  if(data.success) {
                    toastr.success(data.success); 
                  }
                  else if(data.error){
                    toastr.error(data.error); 
                  }
                  else {
                        toastr.error('An error occurred while deleting the branch.');
                    }
                     $('#tableYajra').DataTable().ajax.reload();
                  
                     
                 },
                 error: function (data) {
                     console.log('Error:', data);
                 }
             });
         }
       });





});
</script>

@stop
