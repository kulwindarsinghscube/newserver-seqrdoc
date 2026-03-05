 Dear sir/madam,<br/><br/>
 <style>
		table{border:1px solid #ddebf7;border-collapse: collapse;font-family:arial;font-size:12px;}
		th{background:#1E90FF;color:#fff;font-size:13px;}
		td{padding:5px 10px;}
		td.left{text-align:center;}
		tr{border:1px solid #ddebf7;}
		tr.head{background:#5b9bd5;color:#ffffff;}
		tr.even{}
		tr.odd{background:#ddebf7;}
</style>
The educational verification details of <b>{{$user_data['student_name']}}</b> are <b>verified</b>. <br><br/>
Following is the details submitted by you:<br/>


<table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Student's Institute</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['institute']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Name of the Student</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['student_name']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Degree</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['degree_name']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Branch</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['branch_name_long']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Registration Number</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['registration_no']}}</td>
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
	@if(!empty($user_data['offer_letter']))
	<td style='border: 1px solid black;padding: 15px;'><a href="{{$user_data['offer_letter']}}" target="_blank">Link</a></td>
	@else
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['offer_letter']}}</td>
	@endif
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Date & Time</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['created_date_time']}}</td>
	</tr>
	</table><br/><br/>

	Following is the payment details:<br/><br/>
	<table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Payment Transaction ID</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['payment_transaction_id']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Total Amount</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['amount']}}</td>
	</tr>
	<tr>
	<td style='border: 1px solid black;padding: 15px;'>Date & Time</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$user_data['payment_date_time']}}</td>
	</tr>
	</table><br/><br/>

	The documents you uploaded have following results founds.<br/><br/>
	<table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
	<tr>
				<th style='border: 1px solid black;padding: 15px;'>Document Type</th>
				<th style='border: 1px solid black;padding: 15px;'>Device Type</th>
				<th style='border: 1px solid black;padding: 15px;'>Uploaded File</th>
				<th style='border: 1px solid black;padding: 15px;'>Results Found</th>
				<th style='border: 1px solid black;padding: 15px;'>Remark</th>
				<th style='border: 1px solid black;padding: 15px;'>Exam Name</th>
				<th style='border: 1px solid black;padding: 15px;'>Semester</th>
				<th style='border: 1px solid black;padding: 15px;'>Document Year</th>
				</tr>
	@foreach ($user_data['requestDetailData'] as $document_data)

	<tr>
	<td style='border: 1px solid black;padding: 15px;'>{{$document_data['document_type']}}</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$document_data['device_type']}}</td>
	<td style='border: 1px solid black;padding: 15px;'> <a href="{{$document_data['document_path']}}" target="_blank">Link</a> </td>
	<td style='border: 1px solid black;padding: 15px;'>{{$document_data['result']}}</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$document_data['remark']}}</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$document_data['exam']}}</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$document_data['semester']}}</td>
	<td style='border: 1px solid black;padding: 15px;'>{{$document_data['doc_year']}}</td>
	</tr>
	@endforeach

</table><br/><br/>
<br><br>For more details, visit the website or use the mobile application.<br>
					For further assistance, you can contact us on coe@galgotiasuniversity.edu.in.


