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
				<h1 class="page-header"><i class="fa fa fa-file-excel-o"></i> Verify E-Certificate
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('generateyuvaparivartanverification') }}</ol>
				</h1>
				
			</div>
	</div>

    <div class="panel panel-default">
        <div class="row">
            <div class="col-lg-7"> <br />
            <form id="processrecordForm" class="form-horizontal" enctype="multipart/form-data" action="">
            @csrf

                <div class="form-group">
                    <label class="control-label col-sm-4" for="certificate_no">Certificate No:</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="certificate_no" name="certificate_no" required>
                    </div>
                </div>

              

                <div class="form-group">
                    <label class="control-label col-sm-4" for="candidate_name">Name of the Candidate:</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="candidate_name" name="candidate_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-4" for="issue_date">Issue Date:</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="issue_date" name="issue_date" required>
                    </div>
                </div>

  

                <div class="form-group">
                    <label class="control-label col-sm-4">Select Option:</label>
                    <div class="col-sm-8">
                        <label class="radio-inline">
                            <input type="radio" name="option" value="verification" required> Data Verification
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="option" value="certificate" required> Download E-Certificate
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-9">
                        <button type="submit" class="btn btn-primary" id="btn_records">Submit</button>
                    </div>
                </div>
            </form>


            <div>
        </div>
     </div>    


    </div>
</div>


<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered " role="document"> <!-- Small modal -->
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title">
                    <span id="myModalLabel1">Processing</span>
                    <span id="myModalLabel2" style="display: none;">PDF is ready to download</span>
                    <span id="myModalLabel3" style="display: none;">Data Verified</span>
                </h5>
                <button type="button" class="close" id="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body text-center" id="modalContent">
                <h5 class="mb-3">Processing, Please Wait...</h5>
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>

        </div>
    </div>
</div>




@stop
@section('script')
<script type="text/javascript">

$(document).ready(function () {

    
   

  $('#processrecordForm').on('submit', function(event) {
    event.preventDefault();

  
        var formData = new FormData($("#processrecordForm")[0]);
        var selectedOption = $("input[name='option']:checked").val();
        $("#processingModal").modal("show");

        $.ajax({
            url: '<?= route('yuvaparivartan-records.verification')?>',
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function (resp) {
              $('#myModalLabel1').show();
              $('#myModalLabel2').hide();
              $('#myModalLabel3').hide();
              $("#modalContent").html(`
                     <div class="modal-body text-center" id="modalContent">
                <h5 class="mb-3">Processing, Please Wait...</h5>
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
                `);
             
            },
            success: function (response) {
                console.log(response);
                if (response.status === "yes" && selectedOption=='verification') {
                    
                    $('#myModalLabel1').hide();
                    $('#myModalLabel2').hide();
                    $('#myModalLabel3').show();
                    $("#modalContent").html(`
                        <div style= margin: 0 auto;" class="alert alert-success"><p class="mt-2">&#9989<strong>Data Verified Successfully!</strong></p></div>
                    `);

                    setTimeout(function () {
                        $("#processingModal").modal("hide");
                    }, 3000);
                   
                } 
                else if(response.status === "yes" && selectedOption == 'certificate') {
                    $('#myModalLabel1').hide();
                    $('#myModalLabel3').hide();
                    $('#myModalLabel2').show();
                    $("#modalContent").html(`
                    <div style="width:88% !important; margin: 0 auto;" class="alert alert-success text-center"> 
                        
                        <p class="mt-2">&#9989<strong>Data Verified Successfully!</strong></p>
                    </div>
                      <div style=" border: 2px solid #dbdbdb;width: 88%;margin: auto;margin-top: 10px;border-radius: 5px;
                      background-color: #a6e4ff;padding:5px;">
                      <a href="${response.pdf_url}" class=" mt-2" target="_blank" download>
                         <i class="fa fa-file-pdf-o" style="font-size:18px;color:red"></i> Click to download file</a></div>
                        
                `);

               
            }
            else {
              $('#myModalLabel1').show();
              $('#myModalLabel2').hide();
              $('#myModalLabel3').hide();
                    $("#modalContent").html(`
                   <div style=" margin: 0 auto;" class="alert alert-danger text-danger"> 
                    <p class="mt-2"><strong>❌ Verification Failed! Please check the details.</strong></p>
                    </div>

                    `);
                    setTimeout(function () {
                        $("#processingModal").modal("hide");
                    }, 3000);
                }

                $("#processrecordForm")[0].reset(); 
            },
            error: function (xhr, status, error) {
                $("#modalContent").html(`
                    <div style=" margin: 0 auto;" class="alert alert-danger text-danger"> 
                         <p class="mt-2"><strong> ❌Something went wrong!</strong></p>
                    </div>
                `);    
                setTimeout(function () {
                    $("#processingModal").modal("hide");
                }, 3000);

              
            }
        });
    });


    //     $("#close").on("click", function () {
    //     $("#processrecordForm")[0].reset(); 
    // });
    $("#processingModal").on("hidden.bs.modal", function () {
        $("#processrecordForm")[0].reset(); 
    });
});


</script>

@stop
