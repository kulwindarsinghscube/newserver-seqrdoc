@extends('admin.layout.layout')
@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-6 col-md-offset-3 col-xs-12 ">
				<h2>Bmcc Upload PDFs To Azure </h2>	
				
				<form id="save_form"  method="">
				
					<div class="form-group">
						<label for="upload_file_type">Upload Files :</label>
						<select name="status" id="upload_file_type" class="form-control" data-rule-required="true">
							<option value="">Select Limit</option>
							<option value="all">All</option>
							<option value="0">Limit Document</option>
						</select>
						<span id="upload_file_type_error" class="help-inline text-danger"><?=$errors->first('upload_file_type')?></span>
					</div>

					<div class="limitDocumentDiv" style="position:relative;margin-bottom:10px;display: none">
						<label for="limit_number">Enter Limit to upload document :</label>
						<input id="limit_number" type="text" class="form-control" placeholder="Enter Document Limit to upload" name="limit_number" />
						<span id="limit_number_error" class="help-inline text-danger"><?=$errors->first('limit_number')?></span>
					</div>

					<center>
						<button type="submit" id="upload" class="btn btn-theme" style="color:#fff">Upload</button>
					</center>
				</form>
				<br>
				<p style="font-size:18px;font-weight:bold;" class='response_msg'></p>
				<p style="font-size:18px;font-weight:bold;" class='not_uploaded_response_msg'></p>
				
			</div>

	    </div>
	</div>
@stop
@section('script')

<script type="text/javascript">


$('#upload_file_type').on('change',function(){
	var val =  $('#upload_file_type').val();
	console.log('value :'+ val)
	if (val === 'all') {
		$('.limitDocumentDiv').hide();
		$('#limit_number').val('')
	} else if (val === '' || val === null) { 
		$('.limitDocumentDiv').hide();
		$('#limit_number').val('')
	} else {
		$('.limitDocumentDiv').show();
	}
});


$("#upload").click(function(e) {
    e.preventDefault(); // Prevent default form submission

    var url = "<?= route('bmcc.upload-file-azure') ?>";
    var token = "<?= csrf_token() ?>";
    var upload_file_type = $('#upload_file_type').val();
    var limit_number = $('#limit_number').val();

    $.ajax({
        url: url,
        type: 'POST',
        data: { "_token": token, "upload_file_type": upload_file_type, "limit_number": limit_number },
        dataType: 'json',
        
        beforeSend: function() {
			$('.response_msg').text('');
            $('#not_uploaded_response_msg').text('');
			$('#upload_file_type_error').text('');
            $('#limit_number_error').text('');
			
            $('#upload').text('Uploading...');

			
        },

        success: function(resp) {

			$('#save_form')[0].reset();
			$('.limitDocumentDiv').hide();
            if (resp.success) {
                toastr.success('File uploaded successfully.');
				$('.response_msg').text('File successfully Uploaded Count - '+ resp.count);
				if(resp.notUploadCount > 0) {
					$('.not_uploaded_response_msg').text('File Not Fount - '+ resp.notUploadCount);
				}
            }
			$('#upload').text('Upload');
        },

        error: function(respObj) {
            toastr.error('Something went wrong.');
            $('#upload').text('Upload');
			console.log(respObj.responseJSON);
			if (respObj.responseJSON && respObj.responseJSON.errors) {
                $.each(respObj.responseJSON.errors, function(k, v) {
                    $('#' + k + '_error').text(v);
                });
            }
        }
    });
});


</script> 

@stop  

@section('style')

<style>
	
</style>

@stop	