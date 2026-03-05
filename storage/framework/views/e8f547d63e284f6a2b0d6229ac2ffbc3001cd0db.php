<?php $__env->startSection('content'); ?>
<style type="text/css">
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
  margin-left: 20px;
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
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i>Generate Machakos
					
				</h1>
				
			</div>
		</div>
       
       
		<!-- <form method="post" id="processJsonForm" class="form-horizontal" enctype="multipart/form-data" action="#">
			<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>"> 
		  	<div class="form-group">
		    	<label class="control-label col-sm-2" for="json">Upload Json File:</label>
		    	<div class="col-sm-10">
		      		<input type="file" class="form-control" id="pdf_file" name="pdf_file">
		    	</div>
		  	</div>
		  
              <div class="form-group"> 
		    	<div class="col-sm-offset-2 col-sm-10">
		      		<button type="button" class="btn btn-primary" id="btn_updfile">Submit</button>
		    	</div>
		  	</div>
		  
		</form> -->

        <form method="post" id="processJsonForm" class="form-horizontal" enctype="multipart/form-data" action="#">
    <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
    <div class="form-group">
        <label class="control-label col-sm-2" for="json">Upload Json File:</label>
        <div class="col-sm-10">
            <input type="file" class="form-control" id="pdf_file" name="pdf_file">
            <div id="pdf_file_error" class="text-danger"></div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
      
		    		 <div align="center"><b>Please Click <a href="/machakos/backend/sample_json/license.json" download>HERE</a> To Download Sample Json File</b></div>
            <button type="button" class="btn btn-primary" id="btn_updfile">Submit</button>
        </div>
    </div>
</form>

	
        <table id="machakos-certificate-table" class="table table-hover display" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Business Name</th>
                        <th>Created Date</th>
                        <th>PDF</th> 
                        <th>Mode</th> 
                    </tr>
                </thead>
                <tfoot>
                </tfoot>
        </table>
		
	</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>


<script>


    $(document).ready(function() {
        $('#btn_updfile').on('click', function() {
            var formData = new FormData($('#processJsonForm')[0]);

            $.ajax({
                url: '<?php echo e(route('upload.json')); ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.success);
                        $('#processJsonForm')[0].reset();
                    
                        oTable.ajax.reload();
                    } else {
                        alert('File upload failed');
                    }
                },
                error: function(response) {
                    // Clear previous error messages
                    $('#pdf_file_error').text('');

                    // Display the error message
                    if (response.responseJSON && response.responseJSON.errors) {
                        if (response.responseJSON.errors.pdf_file) {
                            $('#pdf_file_error').text(response.responseJSON.errors.pdf_file[0]);
                        }
                    } else {
                        alert('An error occurred');
                    }
                }
            });
        });

        var oTable = $('#machakos-certificate-table').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [0, "desc"]
        ],
      
        "sAjaxSource":"<?= URL::route('machakos.index')?>",
       
        "aoColumns":[

		{   mData: "rownum", 
            bSortable:false
        },
        {   mData: "business_name",
            sWidth: "30%",
            bSortable: true
        },
        
		{   mData: "created_at",
            sWidth: "15%",
            bSortable: true,
			mRender: function(v, t, o) {
            	// var date = moment(v).format('DD-MM-YYYY');
              var date = moment(v).format('DD-MM-YYYY HH:mm:ss');

				return date;
            },
		},
		
		
        {mData: "pdf_file",
			mRender: function(v, t, o) {
				<?php

        			$pdf_directory ='/backend/tcpdf/examples/';

				?>
				var pdf_path = "<?=$pdf_directory?>"+o.pdf_file;
				
				return "<a data-toggle='tooltip' target='_blank' data-placement='right' title='Please click to download pdf file' class='btn btn-success' href='"+pdf_path+"'>"+o.pdf_file+"</a>";
			}
		},
        {   mData: "request_mode",
            sWidth: "10%",
            bSortable: true
        },

	]
		
	});
    });



    
</script>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/machakos/index.blade.php ENDPATH**/ ?>