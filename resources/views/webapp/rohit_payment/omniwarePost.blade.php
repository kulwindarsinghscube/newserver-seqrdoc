@extends('webapp.layouts.layout')
@section('content')
<?php

      if( strtolower($paymentGateway->payment_mode) == 'test') {
            $salt = $paymentGateway->test_salt; //Pass your SALT here
            $param_data['api_key'] = $paymentGateway->test_merchant_key; //Pass your API KEY here
            $param_data['mode'] = 'TEST';

      } else {
            $salt = $paymentGateway->salt; //Pass your SALT here
            $param_data['api_key'] = $paymentGateway->merchant_key; //Pass your API KEY here
            $param_data['mode'] = 'LIVE';

      }
      

      $param_data['amount'] = $amount+1;
      $param_data['city'] = 'vikhroli';
      $param_data['country'] = 'india';
      $param_data['currency'] = 'INR';
      $param_data['description'] =  (!isset($key)) ? $key : 'scube';
      $param_data['email'] = 'dev7@scube.net.in';
      $param_data['name'] = (!isset($student_name)) ? $student_name : 'scube';
      $param_data['order_id'] = 'SeQR_OW_'.strtotime("now");
      $param_data['phone'] = '7083008499';
      $param_data['return_url'] = route('paymentResponse.rohitOmniware');
      $param_data['zip_code'] = '400079';
      $hash = hashCalculate($salt, $param_data);

      function hashCalculate($salt,$input){
            /* Columns used for hash calculation, Donot add or remove values from $hash_columns array */
            $hash_columns = ['amount', 'api_key', 'city', 'country', 'currency', 'description', 'email', 'mode', 'name', 'order_id', 'phone', 'return_url', 'zip_code',];
            /*Sort the array before hashing*/
            sort($hash_columns);

            /*Create a | (pipe) separated string of all the $input values which are available in $hash_columns*/
            $hash_data = $salt;
            foreach ($hash_columns as $column) {
                  if (isset($input[$column])) {
                        if (strlen($input[$column]) > 0) {
                              $hash_data .= '|' . trim($input[$column]);
                        }
                  }
            }
            $hash = strtoupper(hash("sha512", $hash_data));
            
            return $hash;
      }

?>

<form action="https://pgbiz.omniware.in/v2/paymentrequest" method="post" name="omniwareForm" >
    <input type="hidden" value="<?php echo $hash; ?>"                        name="hash"/>
    <input type="hidden" value="<?php echo $param_data['api_key'];?>"        name="api_key"/>
    <input type="hidden" value="<?php echo $param_data['return_url']; ?>"    name="return_url"/>
    <input type="hidden" value="<?php echo $param_data['mode'];?>"           name="mode"/>
    <input type="hidden" value="<?php echo $param_data['order_id'];?>"       name="order_id"/>
    <input type="hidden" value="<?php echo $param_data['amount'];?>"         name="amount"/>
    <input type="hidden" value="<?php echo $param_data['currency'];?>"       name="currency"/>
    <input type="hidden" value="<?php echo $param_data['description'];?>"    name="description"/>
    <input type="hidden" value="<?php echo $param_data['name'];?>"           name="name"/>
    <input type="hidden" value="<?php echo $param_data['email'];?>"          name="email"/>
    <input type="hidden" value="<?php echo $param_data['phone'];?>"          name="phone"/>
    <input type="hidden" value="<?php echo $param_data['city'];?>"           name="city"/>
    <input type="hidden" value="<?php echo $param_data['zip_code'];?>"       name="zip_code"/>
    <input type="hidden" value="<?php echo $param_data['country'];?>"        name="country"/>
    <!--<input type="submit" value="Submit"> -->
</form>

@stop
@section('script')

<script type="text/javascript">
      var omniwareForm = document.forms.omniwareForm;
      omniwareForm.submit();
</script>
@stop