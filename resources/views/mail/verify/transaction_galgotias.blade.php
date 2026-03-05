Dear sir/madam,<br/><br/>
Request for educational details verification of <b>{{$user_data['student_name']}}</b> is received to us. University will convey you the verification details within 24 hours.<br>
Your request number is <b>{{$user_data['key']}}</b>. Remember this number for our future communication references.
<br><br/>
Following is the details submitted by you:<br/>
<table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>University</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['student_institute']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Name of the Student</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['student_name']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Degree</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['student_degree']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Branch</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['student_branch']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Registration Number</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['student_reg_no']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Passing Year</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['passout_year']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Name Of Recruiter</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['name_of_recruiter']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Offer letter / Joining letter</td>
	<td style='border: 1px solid black;padding: 15px;'><?php echo $user_data['offer_letter']; ?></td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Date & Time</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['date_time_registraion']}}</td>
	</tr>
</table><br/><br/>
Document Details:<br/>
<table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Grade Cards</td>
	<td style='border: 1px solid black;padding: 15px;'><?php echo $user_data['grade_card_files']; ?></td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['grade_card_amount']}} RS</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Provisional Degree</td>
	<td style='border: 1px solid black;padding: 15px;'><?php echo $user_data['provisional_degree_files']; ?></td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['provisional_degree_amount']}} RS</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Original Certificate</td>
	<td style='border: 1px solid black;padding: 15px;'><?php echo $user_data['original_degree_files'];  ?></td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['original_degree_amount']}} RS</td>
	</tr>
</table><br/><br/>
Following is the payment details:<br/>
<table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Payment Transaction ID</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['trans_id']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Total Amount</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['amount']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Date & Time</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['date']}}</td>
	</tr>
</table>
<br/><br/>In case response not received within 24 hours, you can contact us on coe@galgotiasuniversity.edu.in <br/><br/>