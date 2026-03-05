<?php

      // Set your PayUbiz credentials
      if( strtolower($paymentGateway->payment_mode) == 'test') {
            $payuKey = $paymentGateway->test_merchant_key;
            $payuSalt = $paymentGateway->test_salt;
            $url = 'https://test.payu.in/_payment';
      } else {
            $payuKey = $paymentGateway->merchant_key;
            $payuSalt = $paymentGateway->salt;
            $url = 'https://secure.payu.in/_payment';
      }
      
      // Check if the form is submitted

      // Retrieve payment details from the form
      $amount = $amount;
      $firstName = (!isset($student_name)) ? $student_name : 'scube';
      $email = 'dev7@scube.net.in';
      $phone = '7083008499';
      $productInfo = (!isset($key)) ? $key : 'scube';
      $paymentId = 'SeQR_PM_'.strtotime("now");
      // Generate hash
      $hashString = $payuKey . '|' . $paymentId . '|' . $amount .'|' . $productInfo . '|' . $firstName . '|' . $email . '|||||||||||' . $payuSalt;
      $hash = strtolower(hash('sha512', $hashString));

      // Prepare data for API call
      $data = [
            'key' => $payuKey,
            'txnid' => $paymentId, // Generate unique payment ID
            'amount' => $amount,
            'productinfo' => $productInfo,
            'firstname' => $firstName,
            'email' => $email,
            'phone' => $phone,
            'surl' => url('webapp/payment-gateway/rohit-payumoney/response'),
            'furl' => url('webapp/payment-gateway/rohit-payumoney/response'),
            'hash' => $hash,
            'service_provider' => 'payu_paisa',
      ];
      // echo "<pre>";
      // print_r($paymentGateway);
      // echo "</pre>";
      // die();
      // //Redirect to PayUbiz payment page
      //$url = 'https://test.payu.in/_payment'; // For test environment
      // $url = 'https://secure.payu.in/_payment'; // For live environment
?>
<html>
    <body>
      <form action="<?php echo $url; ?>" method="post" name="payuForm">
            <?php foreach ($data as $key => $value) {
                  echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';
            } ?>
      </form>
      <script>
            document.payuForm.submit();
      </script>
      </body>
</html>
<?php
    exit; // Stop further execution
?>