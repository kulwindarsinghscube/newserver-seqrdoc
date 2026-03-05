<?php $__env->startSection('content'); ?>
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


.file-row{
  margin-top:10px !important;

}

</style>
<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i> Batch
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('Batch')); ?></ol>
				</h1>
				
			</div>
		</div>
      
        <div class="">
    <!-- panel panel-default -->
     <div>
          <!-- <?php if(App\Helpers\SitePermissionCheck::isPermitted('add-batch')): ?>
          <?php if(App\Helpers\RolePermissionCheck::isPermitted('add-batch')): ?> -->
          <a href="#" class="btn btn-primary" id="btn_add_batch" >Add Batch</a>
          <!-- <?php endif; ?>
          <?php endif; ?> -->
          <!-- <a href="#" class="btn btn-primary" id="btn_add_batch" >Add Batch</a>
          <a href="#" class="btn btn-primary" role="button" id="UploadExcel">Upload Excel </a> -->
          <!-- <?php if(App\Helpers\SitePermissionCheck::isPermitted('ksg.uploadfile')): ?>
          <?php if(App\Helpers\RolePermissionCheck::isPermitted('ksg.uploadfile')): ?> -->
          <a href="#" class="btn btn-primary" role="button" id="UploadExcel">Upload Excel </a>
          <!-- <?php endif; ?>
          <?php endif; ?> -->
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
                <?php echo csrf_field(); ?>
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
          <?php echo csrf_field(); ?>
          <!-- Dropdown for selecting batch -->
          <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
      

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
          <?php echo csrf_field(); ?>
      
          <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>" id="token">
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


 <!-- Batch Modal -->
 <div class="modal fade" id="BatchModal" tabindex="-1" role="dialog" aria-labelledby="batchModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="batchModalLabel">Add Batch</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="BatchForm" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>
          <input type="hidden" id="batchId" name="id">

          <!-- Batch Name -->
          <div class="form-group">
            <label for="batchName">Batch Name</label>
            <input type="text" class="form-control" id="batchName" name="name" required>
          </div>
          <div id="docList"></div>

          <!-- File Upload Section -->
          <div class="form-group mb-5">
              <label>Upload Files</label>
              <div id="fileUploadContainer">
                  <div class="row ">
                      <div class="col-sm-10">
                          <input type="file" class="form-control file-input" name="files[]">
                      </div>
                      <div class="col-sm-2 ">
                          <button type="button" class="btn btn-primary btn-sm" id="addFileButton">
                              <i class="fa fa-plus"></i>
                          </button>
                      </div>
                  </div>
              </div>
          </div>

  
          <div class=" mt-3 ml-5">
            <button type="submit" class="btn btn-success mt-3">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- File List Modal -->
<div class="modal fade" id="fileListModal" tabindex="-1" role="dialog" aria-labelledby="fileListModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileListModalLabel">Files List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="fileList">
                    <!-- Files will be loaded here dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

       <div class='mt-3'>
        <table class="table align-middle table-row-dashed fs-7 gy-5" id="tableYajra">
              <thead>
                  <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                      <th id="th">SR NO</th>
                      <th id="th">NAME</th>
                      <th id="th">By</th>
                      <th id="th">Date</th>
                      <th id="th">Count Details</th>
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
  $(document).ready(function() {
            var table = $('#tableYajra').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                url: "<?php echo e(route('ksg-batch.datatable')); ?>",
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
                        data: 'status_counts', 
                        name: 'status_counts', 
                        orderable: false, 
                        searchable: false,
                        render: function(data, type, row) {
                           
                          return data.replace(/,/g, '<br>'); 
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, full, meta) {

                          let badgeClass = '';
                            let badgeColor = '';
                            if (data === 'Open') {
                              
                                badgeClass = 'badge-secondary';
                                badgeColor = 'grey'; 
                            } else if (data === 'Rejected') {
                                badgeClass = 'badge-danger';
                                badgeColor = 'red';
                            } else if (data === 'Send For Approval') {
                                badgeClass = 'badge-warning';
                                badgeColor = 'orange';
                            } else {
                              badgeClass = 'badge-success';
                              badgeColor = 'green'; 
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
                    $(row).find('td:eq(0)').html(rowNum);
                }
            });

            setTimeout(function() {
                $("div.alert-success").remove();
            }, 3000);




        

    ///other data

//edit data
    $('#tableYajra').on('click', '.editBatch', function() {
     
        var id = $(this).data('id'); 
        // alert(id);
        $('#batchId').val();
        $('#batchName').val();
        $('#BatchForm').trigger("reset");

        $.ajax({
            url: "<?php echo e(route('edit-batch', '')); ?>/" + id,
            method: 'GET',
            success: function(response) {
                $('#batchId').val(response.id);
                $('#batchName').val(response.name);
                if (response.files && response.files.length > 0) {
                  let fileList = '<ul class="list-group">';
                  response.files.split(',').forEach(function(file) {

                    fileList += `
                          <li class="list-group-item downloadFile" data-file="${file}">
                              ${file}
                              <button type="button" class="btn-close" aria-label="Close" style="float: right;">x</button><input type ="hidden"  name="oldFiles[]" value="${file}">
                          </li>
                      `;
                  });
                  fileList += '</ul>';
                  $('#docList').html(fileList);
              } else {
                  // Show a message if no files are available
                  $('#docList').html('<p>No files available</p>');
              }

                $('#batchModalLabel').text('Edit Batch'); 
                $('#BatchModal').modal('show');
            },
            error: function(xhr) {
                toastr.error('An error occurred while fetching the batch details.');
            }
        });

    });

 
    function callTemplatematerCreate(batchName) {
      var template_desc="test";
      var height="148";
      var width="105";
       var lock_element="unloack";
     
      var token = '<?= csrf_token()?>';
			var get_template_id = sessionStorage.getItem('template_id');
      
    $.ajax({
        url: "<?php echo e(route('template-master.store')); ?>", 
        method: 'POST',
        data: { "_token" : token,'id':get_template_id,'template_name':batchName,'template_desc':template_desc,'height':height,'width':width,"template_status":1,"lock_element":lock_element,"background_template_status":1,'print_with_background':1},
	      dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.success); 
            } else if (response.error) {
                toastr.error(response.error); 
            }
        },
        error: function(xhr) {
            toastr.error('An error occurred while processing the second functionality.');
        }
    });
}



    $('#BatchForm').on('submit', function(event) {
        event.preventDefault();  
        var formData = new FormData(this); 
        batchName=$("#batchName").val();    
        var url = $('#batchId').val() ? "<?php echo e(route('update-batch')); ?>" : "<?php echo e(route('add-batch')); ?>";
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false, 
            contentType: false,
            success: function(data) {
              if(data.success) {
                    toastr.success(data.success); 
                    $('#BatchModal').modal('hide');
                    $('#BatchForm').trigger("reset");
                    $('#batchModalLabel').text('Add Batch'); 
                    $('#tableYajra').DataTable().ajax.reload();
                    callTemplatematerCreate(batchName);
                    
                  }
                  else if(data.error){
                    toastr.error(data.error); 
                  }
                  else {
                        toastr.error('An error occurred while deleting the batch.');
                    }
                 
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    for (let key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            toastr.error(errors[key][0]); 
                        }
                    }
                } else {
                    toastr.error('An unexpected error occurred.');
                }
            }
        });
    });
   

    // delete batch
    $('body').on('click', '.DeleteBatch', function () {
     
     var id = $(this).data("id");
     var userConfirmed = confirm("Are you sure you want to delete this Batch!");
     var url = "<?php echo e(url('delete-batch')); ?>/" + id;
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
                        toastr.error('An error occurred while deleting the batch.');
                    }
                     $('#tableYajra').DataTable().ajax.reload();
                  
                     
                 },
                 error: function (data) {
                     console.log('Error:', data);
                 }
             });
         }
       });



    $('#UploadExcel').on('click', function(event) {
      $.ajax({
            url: "<?php echo e(route('get-batch')); ?>",
            method: 'GET',
            success: function(response) {

              let options = '<option value="">Select Batch</option>'; 
              response.forEach(function(batch) {
                options += `<option value="${batch.id}">${batch.name}</option>`;
              });
              $('#batchSelect').html(options);
                $('#batchModalLabel').text('Upload Excel File'); 
                $('#fileModal').modal('show'); 
            },
            error: function(xhr) {
              toastr.error('An error occurred while fetching the batch details.');
            }
        });

    });


   
   //send for print 

   $('body').on('click', '.printBatch', function () {
    var batchId = $(this).data("id");
    $('#batchIdtogenerate').val(batchId);
   });


   $('#pdfForm').on('submit', function(event) {
    event.preventDefault(); 
    
        $.ajax({
          url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
              if(resp.success==true){
                $('#loader_token').val(resp.loader_token);
				        $('#loaderFile').val(resp.loaderFile);
                  GeneratePDf();

                //console.log("test");
              }
            
              

            }

        });



   
   });

   function GeneratePDf(){
    var fd = new FormData();
    var token = "<?= csrf_token() ?>";
    var  batchIdtogenerate=$("#batchIdtogenerate").val();
    var selecttype=$("#selecttype").val();
    var token=$("#token").val();
    var loader_token= $('#loader_token').val();
   
    fd.append('loader_token',loader_token);
    fd.append('_token',token);
    fd.append('batchIdtogenerate',batchIdtogenerate);
	  fd.append('selecttype',selecttype);
    var url = "<?php echo e(route('PrintBatchRecord')); ?>";
        $.ajax({
            url: url,
            method: 'POST',
            data: fd,
            contentType: false, // Important for FormData
            processData: false, // Important for FormData
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

function clearTimeoutFunction(id,isGenerationCompleted,recordsToGenerate){
	 clearTimeout(id);
	 /*if(isGenerationCompleted==1){
		
	 var loader_token= $('#loader_token').val();
	 var token = "<?= csrf_token()?>";
	 $.ajax({
            url: '<?= route('deleteloaderjson.delete')?>',
            type: "POST",
            data:{'_token':token,'loader_token':loader_token},
            dataType: 'json',  
            success: function (result) {
            	$('#loader_token').val('');
            	$('#loaderFile').val('');
            }
        });
	}*/
}

    $('#fileForm').on('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(this); 
        var url = "<?php echo e(route('ksg.uploadfile')); ?>";
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,  
            contentType: false,  
            success: function(response) {
                // console.log(response);
                if (!response.duplicate_uniqueno) {
                  toastr.success(response.message); 
                  } else {
                      toastr.success(response.message + " Duplicate Unique no is " + response.duplicate_uniqueno);
 
                  }
              
              
                $('#fileModal').modal('hide');
                $('#tableYajra').DataTable().ajax.reload();
                
            },
            error: function(xhr) {
                toastr.error("An error occurred while processing the batch.");
            }
        });
    });


    
    $(document).on('click', '#btn_close', function() {
    location.reload();
    });


    $(document).on('click', '#btn_add_batch', function() {


      $('#BatchForm').trigger("reset");
      $("#BatchModal").modal("show");
      });
  
    
    $(document).on('click', '.approveRejectBatch', function() {
        var recordId = $(this).data('id');
        $('#statusUpdateForm')[0].reset();

      
        $("#updateRecordId").val(recordId);

         var url = "<?php echo e(url('edit-batch')); ?>/" + recordId;
           $.ajax({
            url: url,
            method: 'GET',
            data: $(this).serialize(),
            success: function(data) {
              $('#approveRejectModal').modal('show');
              $('#statusList').empty();
               $('#statusList').append('<li class="list-group-item border-0"><strong>Batch Name:</strong> ' + data.name + '</li>');
              
                   
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
        var url = "<?php echo e(route('update-batch-status')); ?>";
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



    //new code 
    $('#addFileButton').on('click', function() {

      let fileCount = $('#fileUploadContainer .file-row').length;
    
      // Only allow up to 5 file inputs
      if (fileCount < 5) {
        let fileInputGroup = `
            <div class="row mb-2 file-row">
                <div class="col-sm-10">
                    <input type="file" class="form-control file-input" name="files[]">
                </div>
                <div class="col-sm-2">
                    <button type="button" class="btn btn-danger btn-sm removeFileButton">
                        <i class="fa fa-minus"></i>
                    </button> 
                </div>
            </div>
        `;
        $('#fileUploadContainer').append(fileInputGroup);
      }
      else
       {
    
        alert('You can only upload up to 5 files.');
      }
});

// Remove file input row on minus button click
$(document).on('click', '.removeFileButton', function() {
    $(this).closest('.file-row').remove();
});


$(document).on('click', '.downloadFile', function(e) {
  if ($(e.target).is('button')) {return;}
    e.preventDefault();
    let fileName = $(this).data('file').trim();
    $.ajax({
        url: `/download-document`, 
        method: 'GET',
        data: { fileName: fileName },
        xhrFields: { responseType: 'blob' }, // Handle binary data
        success: function(response, status, xhr) {
            let disposition = xhr.getResponseHeader('Content-Disposition');
            let matches = /filename="(.+)"/.exec(disposition);
            let fileName = matches != null && matches[1] ? matches[1] : 'downloaded_file';

            let blob = new Blob([response], { type: xhr.getResponseHeader('Content-Type') });
            let link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        error: function() {
            alert('Failed to download the file.');
        }
    });
});



$(document).on('click', '.btn-close', function(e) {
        e.stopPropagation(); 
        e.preventDefault();
        $(this).closest('li').remove();
    });



    $('#tableYajra').on('click', '.ViewDocuments', function() {
      alert(2);
      var id = $(this).data('id'); 

      $.ajax({
            url: "<?php echo e(route('edit-batch', '')); ?>/" + id,
            method: 'GET',
            success: function(response) {
                if (response.files && response.files.length > 0) {
                  let fileList = '<ul class="list-group">';
                  response.files.split(',').forEach(function(file) {

                    fileList += `
                          <li class="list-group-item downloadFile" data-file="${file}">
                              ${file}
                              
                          </li>
                      `;
                  });
                  fileList += '</ul>';
                  $('#fileList').html(fileList);
              } else {
                  $('#fileList').html('<p>No files available</p>');
              }

               
                $('#fileListModal').modal('show');
            },
            error: function(xhr) {
                toastr.error('An error occurred while fetching the batch details.');
            }
        });
      
    });



 });



 
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/ksg/index.blade.php ENDPATH**/ ?>