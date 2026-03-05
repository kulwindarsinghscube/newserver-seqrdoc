<div>
	Dear Sir/Madam,<br/><br/>
	Kindly click <a href="{{$mail_data['file_path']}}" target="_blank">here</a> to download the file containing the Tata ID Cards for the Request No. listed below:<br/><br/>
	<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>Request Number</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
		 @foreach($mail_data['records'] as $key => $value)
		
		<tr>
			<td>{{ $value->request_number }}</td>
			<td>{{ $value->rows }}</td>
		</tr>
	@endforeach
		<tr>
			
			<td><b>Total Quantity</b></td>
			<td>{{ $mail_data['total_quantity'] }}</td>
		</tr>
	</tbody>
    </table>
	<br/><br/>
	Regards,<br/><br/>
	TPSDI SeQR Docs
</div>