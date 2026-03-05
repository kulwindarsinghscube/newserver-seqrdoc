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
blockquote {
    padding: 2px 10px;
    margin: 0 0 6px;
    font-size: 14px;
    border-left: 5px solid #0052CC;
    background-color: #e8eff9;
}
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
#progress-file {
    width: 100%;
    border: 1px solid #aaa;
    height: 20px;
}
#message-file {
    color: green;
}
#progress-file .bar {
    background-color: #684791;
    color: white;
    height: 18px;
    font-size: 14px;
    text-align: center;
} 
</style>
<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa fa-file-pdf-o"></i> Add Serial Number
					<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('generatekessccertificates')); ?></ol>
				</h1>
				
			</div>
		</div>
        
        <div class="panel panel-default">
        <div class="row">
            <div class="col-lg-7"> <br />
            <form method="post" id="processExcelForm" class="form-horizontal" enctype="multipart/form-data" action="<?=route('kessc-certificate.uploadfile')?>">
				<input type="hidden" name="func" id="func" value="uploadFile"> 
				<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">           
                    <div class="form-group">
                        <label class="control-label col-sm-5" for="excel">Enter Starting Serial Number:</label>
                        <div class="col-sm-7">
                            <input type="number" name="sr_no" id="sr_no" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="6" class="form-control"> 
                        </div>
                    </div>
					<div class="form-group">
                        <label class="control-label col-sm-5" for="excel">Upload PDF:</label>
                        <div class="col-sm-7">
                            <input type="file" class="form-control" id="field_file" name="field_file">
                        </div>
                    </div>                  
                    <div class="form-group">                         
                        <div class="col-sm-offset-5 col-sm-12">
                            <button type="button" class="btn btn-primary" id="btn_updfile">Submit</button>
                        </div>
                    </div>
            </form>
            </div>
            <div class="col-lg-7" id="downloadLink2" style="margin-bottom: 10px; margin-left: 10px;"></div>
			<div class="col-lg-7" style="margin-bottom: 10px; margin-left: 10px;">
			<div id="progress-file" style="display:none;"></div>
			<div id="message-file"></div>   
			<div id="single-file"></div> 
			</div>	
		</div>
		</div>            		
		
	</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">

//on upload button click
$('#btn_updfile').click(function (event) {
	$('#progress-file').html('');
	$('#progress-file').hide();
	$('#downloadLink2').html('');
	$("#message-file").html('');
	$("#single-file").html('');
	
	var fd = new FormData();
	var files = $('[name="field_file"]')[0].files[0];
	var sr_no = $('#sr_no').val();
	var progress_file=Math.floor(Math.random() * Math.floor(Math.random() * Date.now()))+".txt"; 
	var token = "<?= csrf_token()?>";    
	fd.append('field_file',files);
	fd.append('_token',token);
	fd.append('loader_token',$('#loader_token').val());
	fd.append('sr_no',sr_no);
	fd.append('progress_file',progress_file);
	$.ajax({
		url:'<?= route('kessc-addserial.uploadfile')?>',
	    type: "POST",
		dataType: "JSON",
		data:fd,
		processData: false,
		contentType:false,
		beforeSend: function (resp) {
			$('#progress-file').show();
			$('#downloadLink2').html('<b>Preparing for download link.</b>');
		},
		success:function(resp){
			if(resp.success == false){
				$('#downloadLink2').html('');
				toastr["error"](resp.message);
			}
			else
			{				
				$('#downloadLink2').html('<a href="'+resp.link+'" id="downloadLink" download>Click here</a> to download pdf.');
				toastr["success"](resp.message);	
			}
		},
		error:function(resp){
			if(resp.responseJSON != undefined){
				$('#field_file_error').text(resp.responseJSON.errors.field_file[0])
			}
		}
	});
	// Refresh the progress bar every 1 second.
	timer = window.setInterval(function(){refreshProgress(progress_file);}, 1000);	
});

function readTextFile(file,callback)
{
	var rawFile = new XMLHttpRequest();
	rawFile.open("GET", file, false);
	rawFile.onreadystatechange = function ()
	{
		if(rawFile.readyState === 4)
		{
			if(rawFile.status === 200 || rawFile.status == 0)
			{
				var allText = rawFile.responseText;
				if(callback) callback(allText);
			}
		}
	}
	rawFile.send(null);
}    

function refreshProgress(filename='') {        
	src='../../../kessc/backend/serial_no/'+filename;
	console.log(src);
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function () {
		if (this.readyState === this.DONE) {
			if (xhr.status === 200) {
				readTextFile(src,function(allText){
					if(allText){
						const obj = JSON.parse(allText);
						var percent=obj.percent;
						var msg=obj.message;
						$("#progress-file").html('<div class="bar" style="width:' + percent + '%">' + percent + '%</div>');
						$("#message-file").html(msg);
						if (percent == 100) {       
							window.clearInterval(timer);
							var beginning_time=obj.beginning_time;
							var ending_time=obj.ending_time;
							var exec_time=obj.exec_time;
							var hms_time=obj.hms_time;
							var page_time=obj.page_time;
							var pages_processed=obj.pages_processed;
							time_msg= "<div class='py-1'></div><table class='table table-bordered'><tbody><tr><td width='50%'>Pages Processed</td><td>"+pages_processed+"</td></tr><tr><td>Start Time</td><td>"+beginning_time+"</td></tr><tr><td>End Time</td><td>"+ending_time+"</td></tr><tr><td>Execution Time</td><td>"+hms_time+"</td></tr><tr><td>Time Per Page</td><td>"+page_time+"</td></tr></tbody></table>"; 
							$("#single-file").html(time_msg);                                
							timer = window.setInterval(completed(filename), 1000);
						}  
					}    
				});  
				
			} else {
				return false;
			}
		}
	}
	xhr.timeout = 5000;           // TIMEOUT SET TO PREFERENCE (5 SEC)
	xhr.open('HEAD', src, true);
	xhr.send(null);               // VERY IMPORTANT        
}

function completed(progress_file) {
	/*$.ajax({
		url: "create_textfile.php?file="+progress_file+"&status=end",
		success:function(data){}
	}); */ 	
	window.clearInterval(timer);
}

</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/kessc/add_serial_index.blade.php ENDPATH**/ ?>