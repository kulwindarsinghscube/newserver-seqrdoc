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




</style>
<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i>  Print
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('Print')); ?></ol>
				</h1>
				
			</div>
		</div>
      
        <div class="">
    <!-- panel panel-default -->
  


   




    


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
                url: "<?php echo e(route('ksg-printView')); ?>",
                method: 'POST',
                data: {
                        approve_flag: 2,
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




    //send for print 

   $('body').on('click', '.printBatch', function () {
    var batchId = $(this).data("id");
    var flag=$(this).data("flag");
    $('#batchIdtogenerate').val(batchId);
   
    $('#BatchFlag').val(flag);
   });


   $('#pdfForm').on('submit', function(event) {
    event.preventDefault(); 
    var flag=$("#BatchFlag").val();
    let url = "<?php echo e(url('/loderfile')); ?>/" + flag;
    //alert(url);
        $.ajax({
          url: url,
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
              if(resp.success==true){
                $('#loader_token').val(resp.loader_token);
				        $('#loaderFile').val(resp.loaderFile);
                  GeneratePDf(1);

                //console.log("test");
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

    var url = "<?php echo e(route('PrintBatchRecord')); ?>";

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

    $(document).on('click', '#btn_close', function() {
    location.reload();
    });

        
 });


 
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/ksg/print.blade.php ENDPATH**/ ?>