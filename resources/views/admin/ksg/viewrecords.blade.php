@extends('admin.layout.layout')
@section('content')
<style type="text/css">

.list-group-item.border-0 {
    border: none;
    padding-left: 0; 
    padding-right: 0; 
    padding: 5px 10px;
}
.batch-info {
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 16px;
    color: #333;
    padding: 8px 12px;
    /* background-color: #f8f9fa; */
    /* border: 1px solid #ddd; */
    border-radius: 5px;
    margin-bottom: 10px;
    gap: 20px;
    text-align: center;
}


.batch-info strong {
    font-weight: bold;
}

.batch-info br {
    margin-bottom: 5px;
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
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i> Records
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render($breadcrums ) }}</ol>
				</h1>
				
			</div>
		</div>
      
        <div class="">
    <!-- panel panel-default -->
    <label class="batch-info">
    <span>Batch Name: {{ $batch->name }}</span> 
    <span>Date: {{ $batch->created_date }}</span>
    </label>
    <input type="hidden" id="BatchId" value="{{$id}}">


        <div class="mt-5">
         <input type="hidden" id="approver_flag" value="{{$flag }}" >
         @if(App\Helpers\SitePermissionCheck::isPermitted('add-records'))
        @if(App\Helpers\RolePermissionCheck::isPermitted('add-records'))
     
        @endif
					@endif
         
          @if($flag == 0)
          <a href="#" class="btn btn-primary" id="btn_add_records"role="button" >Add Records</a>
          @endif


        <!-- @if(App\Helpers\SitePermissionCheck::isPermitted(' export.records'))
          @if(App\Helpers\RolePermissionCheck::isPermitted(' export.records'))
      
          @endif
				@endif -->
          <a href="#" class="btn btn-primary" id="btn_view_records"role="button" >Export Records</a>

          
     </div>


     <!-- Approve/Reject Modal -->
      <div class="modal fade" id="approveRejectModal" tabindex="-1" role="dialog" aria-labelledby="approveRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="approveRejectModalLabel">Update Status </h2>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                    <select id="statusSelect" class="form-control mb-3" name="newStatus">
                      <option value="">Select Status</option>
                      <option value="Approved">Approved</option>
                      <option value="Rejected">Rejected</option>
                      <option value="Correction">Correction</option>
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
          <input type="hidden" name ="BatchFlag" id="BatchFlag" value="">
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




     <!--  Batch Modal -->
<div class="modal fade" id="BatchModal" tabindex="-1" role="dialog" aria-labelledby="batchModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="batchModalLabel">Add Batch Records</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="BatchForm">
          @csrf
          <input type="hidden" id="recordId" name="id">
          <input type="hidden" name="batch_id" id="batch_id" value="{{$decryptedId }}">
          <div class="form-group">
            <label for="usn">Unique sr. no.</label>
            <input type="text" class="form-control" id="usn" name="usn" required>
          </div>
          <div class="form-group">
            <label for="studentName">Student Name</label>
            <input type="text" class="form-control" id="studentName" name="studentName" required>
          </div>
          <div class="form-group">
            <label for="course">course</label>
            <input type="text" class="form-control" id="course" name="course" required>
          </div>
          <div class="form-group">
            <label for="date">Date</label>
            <input type="text" class="form-control" id="date" name="date" required>
          </div>
          <div class="form-group">
            <label for="month">Month</label>
            <input type="text" class="form-control" id="month" name="month" required>
          </div>
          <div class="form-group">
            <label for="fees-status">Fees</label>
            <div>
              <label class="form-check-label">
                <input type="radio" class="form-check-input" name="fees_status" value="Paid" required> Paid
              </label>
              <label class="form-check-label ml-5">
                <input type="radio" class="form-check-input" name="fees_status" value="Pending" required> Pending
              </label>
            </div>
          </div>
          <div class="form-group">
            <label for="course">Unique ID No</label>
            <input type="text" class="form-control" id="unique_id_no" name="unique_id_no" required>
          </div>
           <div class="form-group">
            <label for="course">ID type</label>
            <input type="text" class="form-control" id="id_type" name="id_type" required>
          </div>
           <div class="form-group">
            <label for="course">credit</label>
            <input type="text" class="form-control" id="credit" name="credit" required>
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
        <input type="hidden" name="batch_id" id="batch_id" value="{{$decryptedId }}">
        <table class="table align-middle table-row-dashed fs-7 gy-5" id="tableYajra">
              <thead>
              @if($flag == 1)
              <tr><th colspan="2"><input type="checkbox" id="select-all-checkbox"> Select All</th>
              <th colspan="3"><button type="button" id="approve-all" class="btn btn-success">Approve Selected</button></th>
             </tr>
       
              @endif
             
              
                  <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                      <th id="th"></th>
                      <th id="th">SR NO</th>
                      <th id="th">USN</th>
                      <th id="th">NAME</th>
                      <th id="th">Course</th>
                      <th id="th">Date</th>
                      <th id="th">Month</th>
                      <th id="th">Fees Status</th>
                       <th id="th">Unique ID No</th>
                        <th id="th">ID type</th>
                      <th id="th">Status</th>
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



    var batch_id=$("#batch_id").val();
    var approver_flag=$("#approver_flag").val();
    // alert(approver_flag);
            var table = $('#tableYajra').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('ksg-batch-records.datatable') }}",
                    method: 'POST',
                    data: {
                        batch_id: batch_id ,
                        approve_flag: approver_flag,
                    
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [
                  {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                         render: function(data, type, row, meta) {
                            return data ;
                        }
                       
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                   
                    {
                        data: 'usn',
                        name: 'usn'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                 
                    {
                        data: 'course',
                        name: 'course'
                    },
                    {
                        data: 'course_date',
                        name: 'course_date'
                    },
                    {
                        data: 'course_month',
                        name: 'course_month'
                    },
                    {
                        data: 'fees',
                        name: 'fees'
                    },
                      {
                        data: 'unique_id_no',
                        name: 'unique_id_no'
                    },
                      {
                        data: 'id_type',
                        name: 'id_type'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, full, meta) {
                            let badgeClass = '';
                            let badgeColor = '';
                            if (data === 'Approved') {
                                badgeClass = 'badge-success';
                                badgeColor = 'green';
                            } else if (data === 'Rejected') {
                                badgeClass = 'badge-danger';
                                badgeColor = 'red';
                            } else if (data === 'Correction') {
                                badgeClass = 'badge-warning';
                                badgeColor = 'orange';
                            } else {
                                badgeClass = 'badge-secondary';
                                badgeColor = 'grey'; 
                            }

                            return '<span class="badge ' + badgeClass + '" style="background-color: ' + badgeColor + ' !important; margin-left: 6px; padding: 5px;">' +
                                data + '</span>';
                        }
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
                    // $(row).find('td:eq(0)').html(rowNum);
                    $(row).find('td:eq(1)').html(rowNum); 
                }
            });

            setTimeout(function() {
                $("div.alert-success").remove();
            }, 3000);






    $('#tableYajra').on('click', '.editBatch', function() {
        var id = $(this).data('id'); 
        $.ajax({
            url: "{{ route('edit-record', '') }}/" + id,
            method: 'GET',
            success: function(response) {
                $('#recordId').val(response.id);
                $('#studentName').val(response.name);
                $('#usn').val(response.usn);
                $('#course').val(response.course);
                $('#date').val(response.course_date);
                $('#month').val(response.course_month);
                 $('#unique_id_no').val(response.unique_id_no);
                  $('#id_type').val(response.id_type);
                  $('#credit').val(response.credit);
                $('#batchModalLabel').text('Edit Record'); 
                $('#BatchModal').modal('show');
                if (response.fees === 'Paid') {
                $('input[name="fees_status"][value="Paid"]').prop('checked', true);
                } else if (response.fees === 'Pending') {
                    $('input[name="fees_status"][value="Pending"]').prop('checked', true);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while fetching the batch details.');
            }
        });

    });

 
    $('#BatchForm').on('submit', function(event) {
        event.preventDefault();        
        var url = $('#recordId').val() ? "{{ route('update-record') }}" : "{{ route('add-record') }}";
        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(data) {
               
                if(data.success) {
                    toastr.success(data.success); 

                    $('#BatchModal').modal('hide');
                    $('#BatchForm').trigger("reset");
                    $('#batchModalLabel').text('Add Batch Record'); 
                     $('#tableYajra').DataTable().ajax.reload();
                  }
                  else if(data.error){
                    toastr.error(data.error);                      
                  }
                  else {
                        toastr.error('An error occurred while deleting the batch.');
                    }    
            },
            error: function(xhr) {
                toastr.error('An error occurred while processing the batch.');
            }
        });
    });


    


    // delete batch
    $('body').on('click', '.DeleteBatch', function () {
     
     var id = $(this).data("id");
     var userConfirmed = confirm("Are you sure you want to delete this Record!");
     var url = "{{ url('delete-record') }}/" + id;
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
                  else 
                  {
                        toastr.error('An error occurred while deleting the Record.');
                  }
                    $('#tableYajra').DataTable().ajax.reload(); 
                 },
                 error: function (data) {
                     console.log('Error:', data);
                 }
             });
         }
       });

       
       $(document).on('click', '#btn_add_records', function() {

        // alert(1);
        $('#batchModalLabel').text('Add Batch Record'); 
        $('#BatchForm').trigger("reset");
        $("#BatchModal").modal("show");
       });

       $(document).on('click', '.approveRejectBatch', function() {
        var recordId = $(this).data('id');
        $("#updateRecordId").val(recordId);
        $('#statusUpdateForm')[0].reset();

         var url = "{{ url('edit-record') }}/" + recordId;
           $.ajax({
            url: url,
            method: 'GET',
            data: $(this).serialize(),
            success: function(data) {
              $('#approveRejectModal').modal('show');
              $('#statusList').empty();
              $('#statusList').append('<li class="list-group-item border-0"><strong>Student Name:</strong> ' + data.name + '</li>');
              $('#statusList').append('<li class="list-group-item border-0"><strong>Course Name:</strong> ' + data.course + '</li>');
              $('#statusList').append('<li class="list-group-item border-0"><strong>Unique sr. no:</strong> ' + data.usn + '</li>');
              $('#statusList').append('<li class="list-group-item border-0"><strong>Date:</strong> ' + data.course_date + '</li>');
              $('#statusList').append('<li class="list-group-item border-0"><strong>Month:</strong> ' + data.course_month + '</li>');
              $('#statusList').append('<li class="list-group-item border-0"><strong>Fees:</strong> ' + data.fees + '</li>');
                   
            },
            error: function(xhr) {
               toastr.error('An error occurred while processing the batch.');
            }
        });  
        });

        $('#statusSelect').change(function() {
          var selectedValue = $(this).val();
          if (selectedValue === 'Rejected' || selectedValue === 'Correction') {
            $('#contentBox').show();
          } else {
            $('#contentBox').hide();
          }
        });
       
      
        // update status

        $('#statusUpdateForm').on('submit', function(event) {
        event.preventDefault();    
        var url = "{{ route('update-record-status') }}";
        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(data) {
                if(data.success) {
                    toastr.success(data.success); 
                    $("#approveRejectModal").modal('hide');
                    $('#tableYajra').DataTable().ajax.reload(); 
                  }
                  else if(data.error){
                    toastr.error(data.error);                      
                  }
                  else {
                        toastr.error('An error occurred while deleting the batch.');
                    }    
            },
            error: function(xhr) {
                toastr.error('An error occurred while processing the batch.');
            }
        });
    });

    //new

    $('#select-all-checkbox').on('click', function() {
      $('.row-checkbox').prop('checked', this.checked);
     });

     $('#approve-all').on('click', function() {
    // Get selected checkboxes
    var selectedIds = [];
    var selectedRowsData = [];
    
    $('input.row-checkbox:checked').each(function() {
        selectedIds.push($(this).val()); 
        // var rowData = $(this).closest('tr').find('td');  
        // var rowDataValues = {
        //     usn: rowData.eq(2).text(),  
        //     name: rowData.eq(3).text(),  
        //     course: rowData.eq(4).text() 
        // };
        // selectedRowsData.push(rowDataValues);
    });
    if (selectedIds.length === 0) {
        alert("Please select at least one checkbox.");
        return;
    }
    if (confirm("Are you sure you want to approve the selected items?")) {
        console.log("Selected IDs:", selectedIds);  
        $.ajax({
            url: "{{ route('approve-selected') }}",  
            method: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                selected_ids: selectedIds,
             
            },
            success: function(response) {
              if(response.success) {
                toastr.success(response.success); 
              }
              else{
                toastr.error(response.error); 
              }
              
                $('#tableYajra').DataTable().ajax.reload(); 
            },
            error: function(error) {
                toastr.error(error); 
            }
        });
    }
});


$('#btn_view_records').on('click', function(e) {
    e.preventDefault();
   var BatchId = $('#BatchId').val(); 
    // alert(BatchId);
    if (!BatchId) {
        toastr.error('Please select a record ID.');
        return;
    }
    var exportUrl = "{{ url('export-records') }}/" + BatchId;
    window.location.href = exportUrl;
});





function load(jsonFileUrl) {
    var loaderid =setTimeout(function () {
        $.ajax({
            url: jsonFileUrl,
            type: "GET",
            dataType: 'json',  
            success: function (result) {
            	$('#loaderDiv').show();
            	$('#pendingCertificates').text(result.pendingCertificates);
            	$('#recordsToGenerate').text(result.recordsToGenerate);
            	$('#generatedCertificates').text(result.generatedCertificates);
            	console.log(result);
            	var isGenerationCompleted=result.isGenerationCompleted;

            	if(result.timePerCertificate!=0&&result.generatedCertificates!=0){
            		$('#predictedTimeDiv').show();
            		$('#predictedTime').text(result.predictedTime);
            		if(result.pendingCertificates==0){
            			$('#predictedTimeText').text('Completion Time ');
            			$('#totalTimeGenerationDiv').show();
            			$('#totalTimeGenerationTime').text(result.totalTimeForGeneration);
            		}
            	}

            	if(result.percentageCompleted!=0){
            	$('.progress-bar').css('width', result.percentageCompleted + '%');
            	$('.progress-bar').text(result.percentageCompleted + '%');

            	}
            	if(isGenerationCompleted==0){
            		load(jsonFileUrl);
            	}else{
            		clearTimeoutFunction(loaderid,isGenerationCompleted,result.recordsToGenerate);
            	}
                
            },
            error: function (error) {
								alert('error; Something Went Wrong!');
						}
            
       });
    }, 1101);
  
}

$('body').on('click', '.printBatch', function () {
    var batchId = $(this).data("id");
    var flag = $(this).data("flag");
    // alert($flag );
    $('#batchIdtogenerate').val(batchId);
    $('#BatchFlag').val(flag);
   });


   $('#pdfForm').on('submit', function(event) {
    event.preventDefault(); 
    let flag = $("#BatchFlag").val();
    // alert(flag);
    
    let url = "{{ url('/loderfile') }}/" + flag;
    // alert(url);
        $.ajax({
          url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
              if(resp.success==true){
                $('#loader_token').val(resp.loader_token);
				        $('#loaderFile').val(resp.loaderFile);
                  GeneratePDf(1);
              }
            }
        });   
   });

function GeneratePDfold(){
    var fd = new FormData();
    var token = "<?= csrf_token() ?>";
    var  batchIdtogenerate=$("#batchIdtogenerate").val();
    var selecttype=$("#selecttype").val();
    var token=$("#token").val();
    var loader_token= $('#loader_token').val();
    let flag = $("#BatchFlag").val();
   
    fd.append('loader_token',loader_token);
    fd.append('_token',token);
    fd.append('batchIdtogenerate',batchIdtogenerate);
	  fd.append('selecttype',selecttype);
    fd.append('flag',flag);
    var url = "{{ route('PrintBatchRecord') }}";
        $.ajax({
            url: url,
            method: 'POST',
            data: fd,
            contentType: false, 
            processData: false, 
            beforeSend: function (data) {
            $("#pdfModalForm").css('display', 'none');
           
            $("#pdfModalLable1").css('display', 'none');
            $('#pdfModalLable2').css('display', 'block');
            $('#downloadLink').html('<b>Preparing for download link.</b>');
            $('#predictedTimeText').text('Approx. Completion Time ');
            $('#process').css('display', 'block');
              $('.progress-bar').css('width','1%');
              $('.progress-bar').text('1%');
              $('#predictedTimeDiv').hide();
              $('#totalTimeGenerationDiv').hide();
            load($('#loaderFile').val());

            

            },
            success: function(data) {
              if(data.success) {
                    toastr.success(data.message); 
                    // $('#pdfModal').modal('hide');
                    $('#downloadLink').html(data.link);
                    $('#pdfModalLable2').css('display', 'none');
                    $('#pdfModalLable3').css('display', 'block');
                    $('#pdfForm').trigger("reset");
                    $('#tableYajra').DataTable().ajax.reload();
                    $('#process').css('display', 'none');
                  }
                  else{
                    toastr.error(data.error); 
                  }
                
                 
            },
            error: function(xhr) {
                toastr.error('An error occurred while processing the batch.');
            }
        });
   }



   function GeneratePDf(startRow = 1, endRow = 2, highestRow = null) {
    var fd = new FormData();
    var token = "<?= csrf_token() ?>";
    var batchIdtogenerate = $("#batchIdtogenerate").val();
    var selecttype = $("#selecttype").val();
    var loader_token = $('#loader_token').val();
    let flag = $("#BatchFlag").val();

    fd.append('loader_token', loader_token);
    fd.append('_token', token);
    fd.append('batchIdtogenerate', batchIdtogenerate);
    fd.append('selecttype', selecttype);
    fd.append('flag', flag);
    fd.append('startRow', startRow);

    var url = "{{ route('PrintBatchRecord') }}";

    $.ajax({
        url: url,
        method: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        beforeSend: function () {
            if (startRow === 1) {
                $("#pdfModalForm").css('display', 'none');
           
            $("#pdfModalLable1").css('display', 'none');
            $('#pdfModalLable2').css('display', 'block');
            $('#downloadLink').html('<b>Preparing for download link.</b>');
            $('#predictedTimeText').text('Approx. Completion Time ');
            $('#process').css('display', 'block');
              $('.progress-bar').css('width','1%');
              $('.progress-bar').text('1%');
              $('#predictedTimeDiv').hide();
              $('#totalTimeGenerationDiv').hide();
              load($('#loaderFile').val());
            }
             if(startRow==highestRow){
            $('#downloadLink').html('<b>Preparing for download link.</b>');
          }
        },
        success: function(data) {
            $('#myModalLabel3').hide();
            $('#myModalLabel').hide();
            $('#myModalLabel1').hide();
            $('#myModalLabel2').show();
            if (data.success) {
                // Update progress
              
                if (highestRow === null) {
                    highestRow = data.highestRow;
                }
                var highestRowCheck= data.highestRow+1;
                if (parseInt(data.endRow) < parseInt(highestRowCheck)) {

                    if(data.link=="Will be generated soon!"){
                      GeneratePDf(data.endRow + 1, Math.min(data.endRow + 1, data.highestRow), data.highestRow);
                    }else{
                      $('#downloadLink').html(data.link);
                     toastr["success"](data.message);
                    console.log('All rows processed successfully.');
                  }

                   
                } else {
                    // All done
                console.log(data.link);
                    toastr.success(data.message);
                    $('#downloadLink').html(data.link);
                    $('#pdfModalLable2').hide();
                    $('#pdfModalLable3').show();
                    $('#process').hide();
                    $('#pdfForm').trigger("reset");
                    $('#tableYajra').DataTable().ajax.reload();
                }
            } else {
                toastr.error(data.message);
            }
        },
        error: function (data) {
            if (data.dataonseJSON !== undefined) {
                $('#field_file_error').text(data.responseJSON.errors.field_file[0]);
            }
        }
    });
}





  

   

 });





 
</script>

@stop
