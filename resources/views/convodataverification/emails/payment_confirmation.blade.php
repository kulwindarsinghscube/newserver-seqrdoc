<!DOCTYPE html>
<html>

<head>
    <title>Payment Confirmation</title>
</head>

<body>
    <h1>Dear Student,</h1>
    <p>Thank you for your payment of {{ @$paymentDetails['txn_amount'] }}.</p>
    <p>Your transaction ID is: {{ @$paymentDetails['txn_id'] }}.</p>
    <p>Your bank transaction ID is: {{ @$paymentDetails['bank_txn_id'] }}.</p>
    <p>Transaction Date: {{ \Carbon\Carbon::parse(@$paymentDetails['txn_date'])->format('d-m-Y h:i A') }}.</p>
    <p>Status: {{ @$paymentDetails['status'] == 'TXN_SUCCESS' ? 'SUCCESS' : '' }}.</p>
    <p>Payment Mode: {{ @$paymentDetails['payment_mode'] }}.</p>
    <p>Thank you for your payment!</p>

    <p style="color:red">Note : Your registration process is currently incomplete. It will be finalized once you verify all details and approve the PDF.</p>
    @include('convodataverification.emails.email_footer')
    
</body>

</html>
