<?php 
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");
// following files need to be included
use App\Utility\Payment\Paytm\PaytmEncdec;

// dynamic process for payment gateway 
$PAYTM_ENVIRONMENT = "TEST";	// For Staging / TEST
// if(strtolower($paymentGateway->payment_mode) != "test") {
//       $PAYTM_ENVIRONMENT = 'PROD';     
// }

// For LIVE
if ($PAYTM_ENVIRONMENT == 'PROD') {
	//===================================================
	//	For Production or LIVE Credentials
	//===================================================
	$PAYTM_STATUS_QUERY_NEW_URL='https://securegw.paytm.in/merchant-status/getTxnStatus';
	$PAYTM_TXN_URL='https://securegw.paytm.in/theia/processTransaction';

	//Change this constant's value with Merchant key received from Paytm.
	$PAYTM_MERCHANT_MID 		= '';
	$PAYTM_MERCHANT_KEY 		= '';

	$PAYTM_CHANNEL_ID 	      = "WEB";
	$PAYTM_INDUSTRY_TYPE_ID       = "";
	$PAYTM_MERCHANT_WEBSITE       = "DEFAULT";
	$PAYTM_CALLBACK_URL 	      = url('convo_student/payment_response');	
}else{
	//===================================================
	//	For Staging or TEST Credentials
	//===================================================
	$PAYTM_STATUS_QUERY_NEW_URL='https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
	$PAYTM_TXN_URL='https://securegw-stage.paytm.in/theia/processTransaction';

	//Change this constant's value with Merchant key received from Paytm.
	$PAYTM_MERCHANT_MID 		= 'SCubeP10227761293550';
	$PAYTM_MERCHANT_KEY 		= 'C&PCFG2EsGHFqohG';
	$PAYTM_CHANNEL_ID 	      = "WEB";
	$PAYTM_INDUSTRY_TYPE_ID       = "Retail";
	$PAYTM_MERCHANT_WEBSITE       = "DEFAULT";
	$PAYTM_CALLBACK_URL 	      = url('convo_student/payment_response');
	
}
      // dynamic process for payment gateway

      $checkSum = "";
      $paramList = array();

      // $orderId = time();
      // $CUST_ID = $_POST["CUST_ID"];
    //   $CUST_ID = 'adity123';

      // Create an array having all required parameters for creating checksum.
      $paramList["MID"] = $PAYTM_MERCHANT_MID;
      $paramList["ORDER_ID"] = $orderId;
      $paramList["CUST_ID"] = $prn;
      $paramList["INDUSTRY_TYPE_ID"] = $PAYTM_INDUSTRY_TYPE_ID;
      $paramList["CHANNEL_ID"] = $PAYTM_CHANNEL_ID;
      $paramList["TXN_AMOUNT"] = $amount;
      $paramList["WEBSITE"] = $PAYTM_MERCHANT_WEBSITE;
      $paramList["CALLBACK_URL"] = $PAYTM_CALLBACK_URL;
      $paramList["MOBILE_NO"] = $student->student_mobile_no;
        $paramList["EMAIL"] = $student->wpu_email_id;
    //   $paramList["TXN_DESC"] = '123';
      //Here checksum string will return by getChecksumFromArray() function.
      $checkSum = PaytmEncdec::getChecksumFromArray($paramList,$PAYTM_MERCHANT_KEY);
 

?>
<html>

<head>
    <title>Merchant Check Out Page</title>
</head>

<body>
    <center>
        <h1>Please do not refresh this page...</h1>
    </center>
    <form method="post" action="<?php echo $PAYTM_TXN_URL ?>" name="f1">
 
    <table border="1">
            <tbody>
                <?php
			foreach($paramList as $name => $value) {
				echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
			}
			?>
                <input type="hidden" name="CHECKSUMHASH" value="<?php echo $checkSum ?>">
            </tbody>
        </table>
        <script type="text/javascript">
        document.f1.submit();
        </script>
    </form>
</body>

</html>