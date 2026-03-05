@extends('webapp.layouts.layout')
@section('content')
<!--- <form action="<?= route('payment.gateway.omniware') ?>" method="post" name="omniwareForm" >
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="student_name" value="{{$student_name}}">
      <input type="hidden" name="key" value="{{$key}}">
      <input type="hidden" name="amount" value="{{$amount}}">
</form> -->
@php
    $amount = 2;
    $order_id = uniqid();
@endphp

@php
$salt = \Config::get('constant.omniware_salt'); //Pass your SALT here
$_POST['api_key'] = \Config::get('constant.omniware_api_key'); //Pass your API KEY here
$_POST['amount'] = $amount;
$_POST['city'] = 'vikhroli';
$_POST['country'] = 'india';
$_POST['currency'] = 'INR';
$_POST['description'] = 'tets des';
$_POST['email'] = 'dev7@scube.net.in';
$_POST['mode'] = 'LIVE';
$_POST['name'] = 'rohit';
$_POST['order_id'] = $order_id;
$_POST['phone'] = '7083008499';
$_POST['return_url'] = 'http://demo.seqrdoclocal.com/webapp/payment-gateway/omniware/response';
$_POST['state'] = 'maharashtra';
$_POST['zip_code'] = '423104';
$hash = hashCalculate($salt, $_POST);

function hashCalculate($salt,$input){
	/* Columns used for hash calculation, Donot add or remove values from $hash_columns array */
	$hash_columns = ['amount', 'api_key', 'city', 'country', 'currency', 'description', 'email', 'mode', 'name', 'order_id', 'phone', 'return_url', 'state', 'zip_code','token'];
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

@endphp

<form action="https://pgbiz.omniware.in/v2/paymentrequest" method="post" name="omniwareForm" >

    <input type="hidden" value="<?php echo $hash; ?>"                   name="hash"/>
    <input type="hidden" value="<?php echo $_POST['api_key'];?>"        name="api_key"/>
    <input type="hidden" value="<?php echo $_POST['return_url']; ?>"    name="return_url"/>
    <input type="hidden" value="<?php echo $_POST['mode'];?>"           name="mode"/>
    <input type="hidden" value="<?php echo $_POST['order_id'];?>"       name="order_id"/>
    <input type="hidden" value="<?php echo $_POST['amount'];?>"         name="amount"/>
    <input type="hidden" value="<?php echo $_POST['currency'];?>"       name="currency"/>
    <input type="hidden" value="<?php echo $_POST['description'];?>"    name="description"/>
    <input type="hidden" value="<?php echo $_POST['name'];?>"           name="name"/>
    <input type="hidden" value="<?php echo $_POST['email'];?>"          name="email"/>
    <input type="hidden" value="<?php echo $_POST['phone'];?>"          name="phone"/>
    <input type="hidden" value="<?php echo $_POST['city'];?>"           name="city"/>
    <input type="hidden" value="<?php echo $_POST['state'];?>"          name="state"/>
    <input type="hidden" value="<?php echo $_POST['zip_code'];?>"       name="zip_code"/>
    <input type="hidden" value="<?php echo $_POST['country'];?>"        name="country"/>
    <!--<input type="submit" value="Submit"> -->
</form>

@stop
@section('script')

<script type="text/javascript">
      var omniwareForm = document.forms.omniwareForm;
      omniwareForm.submit();
      
    //   function formAutoSubmit () {
    //         var payform = document.getElementById("omniwareForm");
    //         payform.submit();
    //   }
    //   window.onload = formAutoSubmit;

      // $(document).payuForm.submit();
</script>
@stop