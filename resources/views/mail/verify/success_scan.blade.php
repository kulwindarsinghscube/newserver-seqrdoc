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
Your payment of <b>Rs.{{$user_data['amount']}}</b> for verification of SeQR certificate has been successful.<br><br>
Below is the details of the SeQR scan payment for verification.<br><br>

<b>Payment Receipt number:</b> {{$user_data['trans_id']}}<br><br>
<b>Payment Gateway Transaction ID:</b> {{$user_data['gateway_id']}}<br><br>
<b>Date & Time of transaction:</b> {{$user_data['date']}}<br><br>

<table width='100%' style='border:1px solid #ddebf7;border-collapse: collapse;font-family:arial;font-size:12px;'>
	<tr class='odd'>
		<th style='background:#1E90FF;color:#fff;font-size:13px;'  colspan='2'>
			<b>Transaction Details</b>
		</th>
		<th style='background:#1E90FF;color:#fff;font-size:13px;'  colspan='2'>
			<b>Verification Details</b>
		</th>
	</tr>
	<tr>
		<td><b>Payment Gateway</b></td>
		<td>{{$user_data['gateway']}}</td>
		<td><b>Serial No.</b></td>
		<td>{{$user_data['key']}}</td>
	<tr>
	<tr class='odd' style='background:#ddebf7;'>
		<td><b>Payment Method</b></td>
		<td>{{$user_data['mode']}}</td>
		<td><b>User Name</b></td>
		<td>{{$user_data['name']}}</td>
	<tr>
	<tr>
		<td><b>Amount</b></td>
		<td>Rs.{{$user_data['amount']}}</td>
		<td></td>
		<td></td>
	<tr>
</table><br>
<br><br>For more details, visit the website or use the mobile application.<br>
					For further assistance, you can contact us on deanstudents.ghrce@raisoni.net, registrar_ghrce@raisoni.net, principal.ghrce@raisoni.net or call us on +91-9604787184, +91-9689903286.