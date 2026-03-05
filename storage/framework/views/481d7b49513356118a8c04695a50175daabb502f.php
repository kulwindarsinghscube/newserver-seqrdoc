<?php $__env->startSection('content'); ?>
<?php
$domain =$_SERVER['HTTP_HOST'];
$subdomain = explode('.', $domain);
 ?>
<style>
body{
	padding:0;
}
</style>
<br><br>

<div class="row">
	<div class="col-xs-12 col-md-12 col-lg-8" >

		<form method="post" id="verificationRequestForm">
			<fieldset>

				<!--<div class="form-group">
					<div class="row">
						<div class="col-xs-3 col-md-3 col-lg-4" >
					<label>Institute <sup>*</sup> </label>
					</div>
						<div class="col-xs-9 col-md-9 col-lg-8">
					<select class="form-control" name="student_institute" id="student_institute" data-rule-required="true" <?php if (\Auth::guard('webuser')->user()->user_type == '0') {echo "disabled";}?>>
						<option value="" disabled selected>Select Institute</option>
						<?php	
          				if($subdomain[0] == "galgotias"){ ?>
          					<option value="Galgotias College">Galgotias College</option>
           				<?php }else{ ?>
							<option value="G H Raisoni College of Engineering, Nagpur">G H Raisoni College of Engineering, Nagpur</option>
					 	<?php } ?>
					</select>
					</div>
					</div>
				</div> -->

				<?php	
				if($subdomain[0] == "galgotias"){ ?>
				<input type="hidden" name="student_institute" id="student_institute" value="Galgotias University">
				<?php }else{ ?>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-3 col-md-3 col-lg-4" >
					<label>Institute <sup>*</sup> </label>
					</div>
						<div class="col-xs-9 col-md-9 col-lg-8">
					<select class="form-control" name="student_institute" id="student_institute" data-rule-required="true">
						<option value="" disabled selected>Select Institute</option>
					     <option value="G H Raisoni College of Engineering, Nagpur">G H Raisoni College of Engineering, Nagpur</option>
					</select>
					</div>
					</div>
				</div>
				<?php } ?>	

				<div class="form-group">
					<div class="row">
						<div class="col-xs-3 col-md-3 col-lg-4" style="margin-bottom: 10px;">
						<label>Degree <sup>*</sup> </label>
						</div>
						<div class="col-xs-9 col-md-4 col-lg-3" style="margin-bottom: 10px;">
						<select class="form-control" name="student_degree" id="student_degree" data-rule-required="true" <?php if (\Auth::guard('webuser')->user()->user_type == '0') {echo "disabled";}?>>
							<option value="" disabled selected>Select Degree</option>

						</select>
						</div>
						<div class="col-xs-3 col-md-2 col-lg-2" >
						<label>Branch <sup>*</sup> </label>
						</div>
						<div class="col-xs-9 col-md-3 col-lg-3">
						<select class="form-control" name="student_branch" id="student_branch" data-rule-required="true"<?php if (\Auth::guard('webuser')->user()->user_type == '0') {echo "disabled";}?>>
							<option value="" disabled selected>Select Branch</option>

						</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-3 col-md-3 col-lg-4" >
							<label>Student Name <sup>*</sup></label>
						</div>
						<div class="col-xs-9 col-md-6 col-lg-8">

							<input class="form-control input-lg" id="student_name" name="student_name" type="text"  minlength="1" maxlength="256" autofocus data-rule-required="true" <?php if (\Auth::guard('webuser')->user()->user_type == '0') {echo "readonly";}?>>
						</div>
					</div>
				</div>
				<!--<div class="form-group">
					<div class="row">
						<div class="col-xs-4 col-md-4 col-lg-5" >
							<label>Student Institute Registration Number / Enrollment Number <sup>*</sup></label>
						</div>
						<div class="col-xs-8 col-md-8 col-lg-7">

							<input class="form-control input-lg" id="student_reg_no" name="student_reg_no" type="text"  placeholder="2015ACSC1234567" minlength="10" maxlength="20" autofocus data-rule-required="true" <?php if (\Auth::guard('webuser')->user()->user_type == '0') {echo "readonly";}?>>
						</div>
					</div>
				</div>-->
				<?php	
				if($subdomain[0] == "galgotias"){ ?>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-4 col-md-4 col-lg-5" >
							<label>Student Institute Admission number / Enrolment number <sup>*</sup></label>
						</div>
						<div class="col-xs-8 col-md-8 col-lg-7">

							<input class="form-control input-lg" id="student_reg_no" name="student_reg_no" type="text"  placeholder="2015ACSC1234567" autofocus data-rule-required="true" <?php if (\Auth::guard('webuser')->user()->user_type == '0') {echo "readonly";}?>>
						</div>
					</div>
				</div>
				<?php }else{ ?>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-4 col-md-4 col-lg-5" >
							<label>Student Institute Registration Number / Enrollment Number <sup>*</sup></label>
						</div>
						<div class="col-xs-8 col-md-8 col-lg-7">

							<input class="form-control input-lg" id="student_reg_no" name="student_reg_no" type="text"  placeholder="2015ACSC1234567" minlength="10" maxlength="20" autofocus data-rule-required="true" <?php if (\Auth::guard('webuser')->user()->user_type == '0') {echo "readonly";}?>>
						</div>
					</div>
				</div>
				<?php } ?>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-3 col-md-3 col-lg-4" >
							<label>Passout Year <sup>*</sup> </label>
						</div>
						<div class="col-xs-9 col-md-9 col-lg-8">
							<select class="form-control" name="passout_year" id="passout_year" data-rule-required="true"<?php if (\Auth::guard('webuser')->user()->user_type == '0') {echo "disabled";}?> >
								<option value="" disabled selected>Select Year</option>
								<?php
									$currently_selected = date('Y');
									$earliest_year = 2000;
									$latest_year = date('Y');

									foreach (range($latest_year, $earliest_year) as $i) {
										echo '<option value="' . $i . '"' . ($i === $currently_selected ? ' selected="selected"' : '') . '>' . $i . '</option>';
									}

									?>

							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-3 col-md-3 col-lg-4" >
							<label>Name of Recruiter  <sup>*</sup></label>
						</div>
						<div class="col-xs-9 col-md-6 col-lg-8">

							<input class="form-control input-lg" id="name_of_recruiter" name="name_of_recruiter" type="text"  minlength="1" maxlength="256" autofocus data-rule-required="true">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="row">
						<div class="col-xs-4 col-md-4 col-lg-5" >
							<label>Upload offer letter/joining letter </label>
						</div>
						<div class="col-xs-8 col-md-8 col-lg-7">

							<input class="form-control" id="offer_letter" name="offer_letter" type="file"  accept="image/*,.pdf" >
						</div>
					</div>
				</div>
			</div>

			<?php	if($subdomain[0] == "galgotias"){ ?>
			<div class="col-xs-12 col-md-12 col-lg-4" style="font-size: 10px">
				<h5 style="color: #0288f2;font-weight: 600">Prices for verification : </h5>
				<table class="table price-table" >
					<tr>
						<th>Document Type</th>
						<th>Rate Per Document</th>
						<th>Maximum Uploads</th>
					</tr>
					<tr>
						<td>Grade Card</td>
						<td><span id="grade_card_amount_chart">500</span> RS</td>
						<td>10 Files</td>
					</tr>
					<tr>
						<td>Provisional Degree</td>
						<td><span id="provision_degree_amount_chart">300</span> RS</td>
						<td>1 Files</td>
					</tr>
					<tr>
						<td>Leaving Certificate</td>
						<td><span id="original_degree_amount_chart">300</span> RS</td>
						<td>1 Files</td>
					</tr>
				</table>
			</div>
		<?php }else{ ?>
			<div class="col-xs-12 col-md-12 col-lg-4" style="font-size: 10px">
				<h5 style="color: #0288f2;font-weight: 600">Prices for verification : </h5>
				<table class="table price-table" >
					<tr>
						<th>Document Type</th>
						<th>Rate Per Document</th>
						<th>Maximum Uploads</th>
					</tr>
					<tr>
						<td>Grade Card</td>
						<td><span id="grade_card_amount_chart">500</span> RS</td>
						<td>5 Files</td>
					</tr>
					<tr>
						<td>Provisional Degree</td>
						<td><span id="provision_degree_amount_chart">300</span> RS</td>
						<td>2 Files</td>
					</tr>
					<tr>
						<td>Leaving Certificate</td>
						<td><span id="original_degree_amount_chart">300</span> RS</td>
						<td>2 Files</td>
					</tr>
					<tr>
						<td>Marksheet</td>
						<td><span id="marksheet_amount_chart">600</span> RS</td>
						<td>2 Files</td>
					</tr>
				</table>
			</div>
		<?php } ?>

			<div div class="col-xs-12 col-md-12 col-lg-8" style="font-size: 10px;">
				<h5 style="color: #0288f2;font-weight: 600">Upload Documents : </h5>
				<table class="table file-table">
					<tr>
						<th>Document Type</th>
						<th>Upload Files</th>
						<th>Total Amount</th>
					</tr>
					<tr>
					<td>Grade Card</td>
					<td>

						  <label for="grade_card1" class="custom-file-upload">
						   File1
						  </label>
						  <input id="grade_card1" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card"  title="File1" style="display:none;">
						 <label for="grade_card2" class="custom-file-upload">
						   File2
						  </label>
						  <input id="grade_card2" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File2" style="display:none;">
						   <label for="grade_card3" class="custom-file-upload">
						    File3
						  </label>
						  <input id="grade_card3" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File3" style="display:none;">
						   <label for="grade_card4" class="custom-file-upload">
						    File4
						  </label>
						  <input id="grade_card4" name='grade_card[]' type="file" caccept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File4" style="display:none;">
						  <label for="grade_card5" class="custom-file-upload">
						    File5
						  </label>
						  <input id="grade_card5" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File5" style="display:none;">
						  <?php	if($subdomain[0] == "galgotias"){ ?>
						  <label for="grade_card6" class="custom-file-upload">
						    File6
						  </label>
						  <input id="grade_card6" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File6" style="display:none;">

						  <label for="grade_card7" class="custom-file-upload">
						    File7
						  </label>
						  <input id="grade_card7" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File7" style="display:none;">

						  <label for="grade_card8" class="custom-file-upload">
						    File8
						  </label>
						  <input id="grade_card8" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File8" style="display:none;">

						  <label for="grade_card9" class="custom-file-upload">
						    File9
						  </label>
						  <input id="grade_card9" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File9" style="display:none;">

						  <label for="grade_card10" class="custom-file-upload">
						    File10
						  </label>
						  <input id="grade_card10" name='grade_card[]' type="file" accept="image/*,.pdf" class="upload_document_file" data-type="grade_card" title="File10" style="display:none;">
						  <?php }?>
						  
					</td>
						<td><span id="grade_card_amount">0</span>&nbsp;RS</td>
					</tr>
					<tr>
						<td>Provisional Degree</td>
						<td>
							 <label for="provision_degree1" class="custom-file-upload">
							   File1
							  </label>
							  <input id="provision_degree1" name='provision_degree[]' type="file" accept="image/*,.pdf" data-type="provision_degree" title="File1" style="display:none;">
							<?php if($subdomain[0] != "galgotias"){ ?>  
							 <label for="provision_degree2" class="custom-file-upload">
							   File2
							  </label>
							  <input id="provision_degree2" name='provision_degree[]' type="file" accept="image/*,.pdf" data-type="provision_degree" title="File2" style="display:none;">
							<?php } ?>

						</td>
						<td><span id="provisinal_degree_amount">0</span>&nbsp;RS</td>
					</tr>
					<tr>
						<td>Leaving Certificate</td>
						<td>	<label for="original_degree1" class="custom-file-upload">
							   File1
							  </label>
							  <input id="original_degree1" name='original_degree[]' type="file" accept="image/*,.pdf" data-type="original_degree" title="File1" style="display:none;">
							<?php if($subdomain[0] != "galgotias"){ ?>  
							 <label for="original_degree2" class="custom-file-upload">
							   File2
							  </label>
							  <input id="original_degree2" name='original_degree[]' type="file" accept="image/*,.pdf" data-type="original_degree" title="File2" style="display:none;">
							<?php } ?>  
						</td>
						<td><span id="original_degree_amount">0</span>&nbsp;RS<span hidden id="marksheet_amount">0</span></td>
					</tr>
					<?php if($subdomain[0] != "galgotias"){ ?> 
					<tr>
						<td>Marksheet <br>RTMNU Students <br>(Non Autonomous)</td>
						<td>
							<label for="marksheet1" class="custom-file-upload">
							   File1
							  </label>
							  <input id="marksheet1" name='marksheet[]' type="file" accept="image/*,.pdf" data-type="marksheet" title="File1" style="display:none;">
							  <label for="marksheet2" class="custom-file-upload">
							   File2
							  </label>
							  <input id="marksheet2" name='marksheet[]' type="file" accept="image/*,.pdf" data-type="marksheet" title="File1" style="display:none;">
						</td>
						<td><span id="marksheet_amount">0</span>&nbsp;RS</td>
					</tr>
					<?php } ?>
					<tr>
						<td>Total</td>
						<td>
							<span id="total_files_count">0</span>&nbsp;Files
						</td>
						<td><span id="total_amount">0</span>&nbsp;RS</td>
					</tr>
				</table>
			</div>

			<div div class="col-xs-12 col-md-12 col-lg-8" >

				<div class="row" style="margin-top: 30px;margin-bottom: 30px;">
					<div class="col-xs-6 col-md-6 col-lg-6" style="text-align: center; ">
						<button type="button"  class="btn btn-sm btn-block form-btn" style="border:1px solid #4caf50; background-color: #8bc34a !important; color:#fff;max-width: 250px;    margin: auto;"  id="proceed-to-payment-btn"> Make Payment to Submit</button>
					</div>
					<div class="col-xs-6 col-md-6 col-lg-6" style="text-align: center; ">
						  <button type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #2196f3;background-color: #03a9f4 !important;color:#fff;max-width: 250px;    margin: auto; " id="save-request-btn"> Save & Pay Later on</button>
					</div>
				</div>
			</div>

	</fieldset>
</form>
</div>
</div>			
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
	var grade_card_counter=0;
	var provision_degree_counter=0;
	var original_degree_counter=0;
	var marksheet_counter=0;
	var totalFiles=0;
	var grade_card_amount_per_doc=500;
	var provision_degree_amount_per_doc=300;
	var original_degree_amount_per_doc=300;
	var marksheet_amount_per_doc=600;

	var key = '';
	var pay = '';

	$('a[data-url^="requestverification"]').parent().addClass('active');

	$(document).ready(function(){

		$('#student_reg_no').keypress(function(event){

			isAlphaNumeric(event);
		})
		function isAlphaNumeric(evt) {
			
		  	var charCode = (evt.which) ? evt.which : event.keyCode
		    if (!(charCode > 47 && charCode < 58) && // numeric (0-9)
		        !(charCode > 64 && charCode < 91) && // upper alpha (A-Z)
		        !(charCode > 96 && charCode < 123)) { // lower alpha (a-z)
		      return false;
		    }
		 
		  	return true;
		};
		var token="<?php echo e(csrf_token()); ?>";
		$.ajax({
	        url: '<?php echo e(URL::route("request.verification.type")); ?>',
	        type: 'POST',
	        data :{type:'degree','_token':token},
	        success: function (data) {
	        	if(data.type=="success"){
	        		$('#student_degree').html(data.data);
	        	}
	        },
	        dataType:'JSON'
	    });

	    $.ajax({
	        url: '<?php echo e(URL::route("request.verification.type")); ?>',
	        type: 'POST',
	        data :{type:'documents_rate_master','_token':token},
	        success: function (data) {
	        	if(data.type=="success"){
	        		// console.log(data);
	        		grade_card_amount_per_doc=data.data[0]['amount_per_document'];
	        		provision_degree_amount_per_doc=data.data[1]['amount_per_document'];
	        		original_degree_amount_per_doc=data.data[2]['amount_per_document'];
	        		marksheet_amount_per_doc=data.data[3]['amount_per_document'];
	        		$('#grade_card_amount_chart').html(grade_card_amount_per_doc);
	        		$('#provision_degree_amount_chart').html(provision_degree_amount_per_doc);
	        		$('#original_degree_amount_chart').html(original_degree_amount_per_doc);
	        		$('#marksheet_amount_chart').html(marksheet_amount_per_doc);
	        	}
	        },
	        dataType:'JSON'
	    });

	    $.ajax({
	        url: '<?php echo e(URL::route("request.verification.type")); ?>',
	        type: 'POST',
	        data :{type:'fetchStudentDetails','_token':token},
	        success: function (data) {
	        	if(data.type=="success"){
	        	//	console.log(data.data);
	        		$('#student_institute').val(data.data.institute);
	        		$('#student_degree').val(data.data.degree);
	        		getBranches(data.data.degree,data.data.branch);

	        		$('#student_reg_no').val(data.data.registration_no);
	        		$('#passout_year').val(data.data.passout_year);
	        		$('#student_name').val(data.data.fullname+' '+data.data.l_name);
	        	}
	        },
	        dataType:'JSON'
	    });

	    $("#student_degree").change(function(){
	    	getBranches(this.value,0);

		});
		function getBranches(degree_id,branch_id){

			$.ajax({
		        url: '<?php echo e(URL::route("request.verification.type")); ?>',
		        type: 'POST',
		        data :{type:'branch',degree_id:degree_id,'_token':token},
		        success: function (data) {
		        	if(data.type=="success"){
		        		$('#student_branch').html(data.data);
		        		if(branch_id!=0){
		        			$('#student_branch').val(branch_id);
		        		}
		        	}
		        },
		        dataType:'JSON'
		    });
		}
		$("#save-request-btn").click(function(e){
			
			e.preventDefault();

			if (!$('#verificationRequestForm').valid())
			{
				$("html, body").animate({ scrollTop: 0 }, "slow");
	            return false;
			}
			else{

				var formData = new FormData($('#verificationRequestForm')[0]);
				formData.append("type", "saveRequest");
				formData.append("payment_status", "0");
				formData.append("total_files", $('#total_files_count').html());
				formData.append("total_amount", $('#total_amount').html());
				formData.append("student_institute", $('#student_institute').val());
				formData.append("student_degree", $('#student_degree').val());
				formData.append("student_branch", $('#student_branch').val());
				formData.append("passout_year", $('#passout_year').val());
				formData.append("_token", token);



				$.ajax({
		            url: '<?php echo e(URL::route("request.verification.save.request")); ?>',
		            type: 'POST',
		            data: formData,
		            success: function (data) {
		                if(data.status == 'success'){
		                	console.log(data);
		                	toastr["success"](data.message);
		                	setTimeout(function() {
		                	//location.reload(true);
		                	//window.location.href = '/verify/request-verification/success-request?firstname='+data.firstname+'&request_number='+data.request_number;
		                	window.location.href = '/verify/pending-payments';
		                }, 2000);
		                        }else{
		                        	toastr["error"](data.message);
		                        }
		            },
		            cache: false,
		            contentType: false,
		            processData: false,
		            dataType:'JSON'
		        });

			}

		});
		$("#proceed-to-payment-btn").click(function(e){
			e.preventDefault();

			if (!$('#verificationRequestForm').valid())
			{
				$("html, body").animate({ scrollTop: 0 }, "slow");
	            return false;
			}
			else{
				$("#student_institute").removeAttr("disabled");
				$("#student_degree").removeAttr("disabled");
				$("#student_branch").removeAttr("disabled");
				$("#passout_year").removeAttr("disabled");

				var formData = new FormData($('#verificationRequestForm')[0]);
				formData.append("type", "saveRequest");
				formData.append("payment_status", "1");
				formData.append("total_files", $('#total_files_count').html());
				formData.append("total_amount", $('#total_amount').html());
				formData.append("_token", token);
				$.ajax({
		            url: '<?php echo e(URL::route("request.verification.save.request")); ?>',
		            type: 'POST',
		            data: formData,
		            success: function (data) {
		                if(data.status == 'success'){
		                	toastr["success"](data.message);
		                	
		                	window.location.href = "/verify/payment/paytm?key_payment="+data.request_number;

		                	setInterval( function () {
							    oTable.ajax.reload();
							}, 10000 );

		                        }else{
		                        	toastr["error"](data.message);
		                        }
		            },
		            cache: false,
		            contentType: false,
		            processData: false,
		            dataType:'JSON'
		        });

			}
		});

		<?php if($subdomain[0] == "galgotias"){ ?>
			$('#grade_card1,#grade_card2,#grade_card3,#grade_card4,#grade_card5,#grade_card6,#grade_card7,#grade_card8,#grade_card9,#grade_card10,#provision_degree1,#original_degree1,#original_degree2,#marksheet1,#marksheet2').change(function() {
			processFile(this);
			});
		<?php }else{ ?>
			$('#grade_card1,#grade_card2,#grade_card3,#grade_card4,#grade_card5,#provision_degree1,#provision_degree2,#original_degree1,#original_degree2,#marksheet1,#marksheet2').change(function() {
			processFile(this);
		});
		<?php } ?>
		function processFile($this){
			var i = $($this).prev('label').clone();
		 	if($($this)[0].files[0] != null && $($this)[0].files[0] != undefined){
		 		
		 		$($this).prev('label').html($($this)[0].files[0].name+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		  

		    	$($this).after('<span class="remove-file" data-id="'+$($this).attr('id')+'" data-type="'+$($this).attr('data-type')+'" data-title="'+$($this).attr('title')+'" title="Remove File" ><i class="fa fa-times-circle remove-file-icon" aria-hidden="true" ></i></span>');
			  	var data_type=$($this).attr('data-type');
			  	switch (data_type){
			  		case 'grade_card':
			  			grade_card_counter=grade_card_counter+1;
			  			$('#grade_card_amount').html(grade_card_counter*grade_card_amount_per_doc);
			  		break;
			  		case 'provision_degree':
			  			provision_degree_counter=provision_degree_counter+1;
			  			$('#provisinal_degree_amount').html(provision_degree_counter*provision_degree_amount_per_doc);
			  		break;
			  		case 'original_degree':
			  			original_degree_counter=original_degree_counter+1;
			  			$('#original_degree_amount').html(original_degree_counter*original_degree_amount_per_doc);
			  		break;
			  		case 'marksheet':
			  			marksheet_counter=marksheet_counter+1;
			  			$('#marksheet_amount').html(marksheet_counter*marksheet_amount_per_doc);
			  		break;

			  	}
		  		totalFiles=totalFiles+1;



			}else{
			  	var data_type=$($this).attr('data-type');
			  	switch (data_type){
			  		case 'grade_card':
			  		if(grade_card_counter!=0){
			  			grade_card_counter=grade_card_counter-1;
			  		}
			  		$('#grade_card_amount').html(grade_card_counter*grade_card_amount_per_doc);
			  		break;
			  		case 'provision_degree':
			  		if(provision_degree_counter!=0){
			  			provision_degree_counter=provision_degree_counter-1;
			  		}
			  		$('#provisinal_degree_amount').html(provision_degree_counter*provision_degree_amount_per_doc);
			  		break;
			  		case 'original_degree':
			  		if(original_degree_counter!=0){
			  			original_degree_counter=original_degree_counter-1;
			  		}
			  		$('#original_degree_amount').html(original_degree_counter*original_degree_amount_per_doc);
			  		break;
			  		case 'marksheet':
			  		if(marksheet_counter!=0){
			  			marksheet_counter=marksheet_counter-1;
			  		}
			  		$('#marksheet_amount').html(marksheet_counter*marksheet_amount_per_doc);
			  		break;

			  	}
			  	if(totalFiles!=0){
			  		totalFiles=totalFiles-1;
			  	}
			  	$($this).prev('label').text($($this).attr('title'));

			  	$("span[data-id='"+$($this).attr('id')+"']").remove();
			}

		  	$('#total_files_count').html(totalFiles);
		   	$('#total_amount').html(parseInt($('#grade_card_amount').html())+parseInt($('#provisinal_degree_amount').html())+parseInt($('#original_degree_amount').html())+parseInt($('#marksheet_amount').html()));
		  	
		}

		$(document).on('click', '.remove-file', function(e) {

			removeFile(this);
		});
		
		function removeFile($this){
		

			$("#"+$($this).attr('data-id')).val("");
		  		//$(this).prev('label').text($(this).attr('data-title'));
		  	$("label[for='"+$($this).attr('data-id')+"']").text($($this).attr('data-title'));
  			var data_type=$($this).attr('data-type');
  			var data_id=$($this).attr('data-id');
		  			//console.log(data_id);
		  	switch (data_type){
		  		case 'grade_card':
		  		
		  		if(grade_card_counter!=0){
		  			grade_card_counter=grade_card_counter-1;
		  		}
		  		$('#grade_card_amount').html(grade_card_counter*grade_card_amount_per_doc);
		  		break;
		  		case 'provision_degree':
		  		if(provision_degree_counter!=0){
		  			provision_degree_counter=provision_degree_counter-1;
		  		}
		  		$('#provisinal_degree_amount').html(provision_degree_counter*provision_degree_amount_per_doc);
		  		break;
		  		case 'original_degree':
		  		if(original_degree_counter!=0){
		  			original_degree_counter=original_degree_counter-1;
		  		}
		  		$('#original_degree_amount').html(original_degree_counter*original_degree_amount_per_doc);
		  		break;
		  		case 'marksheet':
		  		if(marksheet_counter!=0){
		  			marksheet_counter=marksheet_counter-1;
		  		}
		  		$('#marksheet_amount').html(marksheet_counter*marksheet_amount_per_doc);
		  		break;

		  	}
		  	if(totalFiles!=0){
		  		totalFiles=totalFiles-1;
		  	}

			$('#total_files_count').html(totalFiles);
		    $('#total_amount').html(parseInt($('#grade_card_amount').html())+parseInt($('#provisinal_degree_amount').html())+parseInt($('#original_degree_amount').html())+parseInt($('#marksheet_amount').html()));
		   $($this).remove();
		    
		}
		
	});
</script>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>
<style type="text/css">
		.price-table{
			background-color: #d8e6fa;
			font-size: 13px;
			border: 2px solid #ddd;
			text-align: center;
		}
		.price-table th{
			text-align: center;
		}

		.file-table{
			background-color: #d8e6fa;
			font-size: 13px;
			font-weight: 600;
			text-align: center;
			border: 1px solid #ccd7a0; border-collapse: collapse;
		}
		.file-table th{
			text-align: center;
			width: 33.33%;
			padding: 20px !important;
		}
		.file-table tr:first-child  {
		   background-color: #a7c941 !important;
		   color: #fff;

		}
		.file-table tr:nth-child(even) {background: #fff;}
		.file-table tr:nth-child(odd) {background: #ebf2d3;}
		.file-table td,th {
			border: 1px solid #ccd7a0 !important;
		}

		.custom-file-upload {
     border: 1px solid #2196F3;
    display: inline-block;
    padding: 6px 12px;
    cursor: pointer;
    background-color: #0288f2;
    color: #fff;
    border-radius: 5px;
}
.error{ color: red; }
.form-control{
	color: black !important;
}

	</style>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('verify.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/request_verification.blade.php ENDPATH**/ ?>