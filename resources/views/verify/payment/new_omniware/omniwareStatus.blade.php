<!DOCTYPE html>
<html>
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Payment Success</title>
  </head>
<body>

@php

ini_set('display_errors',1);
error_reporting(E_ALL);

if(isset($_POST)){
	$response = $_POST;
	
	/* It is very important to calculate the hash using the returned value and compare it against the hash that was sent while payment request, to make sure the response is legitimate */
	$salt = \Config::get('constant.omniware_salt'); /* put your salt provided by Omniware here */
	if(isset($salt) && !empty($salt)){
		$response['calculated_hash']=hashCalculate($salt, $response);
		$response['valid_hash'] = ($response['hash']==$response['calculated_hash'])?'Yes':'No';
	} else {
		$response['valid_hash']='Set your salt in return_page.php to do a hash check on receiving response from Omniware';
	}
}

function hashCalculate($salt,$input){
	/* Remove hash key if it is present */
	unset($input['hash']);
	/*Sort the array before hashing*/
	ksort($input);
	
	/*first value of hash data will be salt*/
	$hash_data = $salt;
	
	/*Create a | (pipe) separated string of all the $input values which are available in $hash_columns*/
	foreach ($input as $key=>$value) {
		if (strlen($value) > 0) {
			$hash_data .= '|' . $value;
		}
	}

	$hash = null;
	if (strlen($hash_data) > 0) {
		$hash = strtoupper(hash("sha512", $hash_data));
	}
		
	return $hash;
}


@endphp
<div class="container">
  @if($response['response_code'] == 0 || $response['response_code'] == '0')
  <div class="row">
    <div class="col-xs-12 col-md-12 col-md-offset-3 text-center">
      <h2>Transaction Successful</h2>
      <h5>Your transaction has completed successfully and details are provided below.</h5>
      <table class='table table-bordered table-hover'>
        <tr>
          
          <th>Transaction ID</th>
          <td class="text-left">{{$response['order_id']}}</td>
        </tr>
        <tr>
          <th>Gateway ID</th>
          <td class="text-left">{{$response['transaction_id']}}</td>
        </tr>
        <tr>
          <th>Date</th>
          <td class="text-left">{{date('d-m-Y')}}</td>
        </tr>
        <tr>
          <th>Mode</th>
          <td class="text-left">{{$response['payment_mode']}}</td>
        </tr>
        <tr>
          <th>Amount</th>
          <td class="text-left">{{$response['amount']}}<i class="fa fa-rupee"></i></td>
        </tr>
      </table>
      <p class="text-center">You can close this tab and return to previous page to try again.</p>
      <p class="text-center"><a href="javascript:void(0)" onclick="window.close()" class="btn btn-theme" style="color:#fff"> Close</a></p>
    </div>
  </div>	
  @else
    
  <div class="row">
    <div class="col-xs-12 col-md-12 col-md-offset-3 text-center">
      <h2>Transaction Failed</h2>
      <h5>Your transaction has failed and details are provided below.</h5>
      <table class='table table-bordered table-hover'>
        <tr>
          {{ $session_key }}
          <th>Transaction ID</th>
          <td class="text-left">{{$response['order_id']}}</td>
        </tr>
        <tr>
          <th>Gateway ID</th>
          <td class="text-left">{{$response['transaction_id']}}</td>
        </tr>
        <tr>
          <th>Date</th>
          <td class="text-left">{{date('d-m-Y')}}</td>
        </tr>
        <tr>
          <th>Mode</th>
          <td class="text-left">{{$response['payment_mode']}}</td>
        </tr>
        <tr>
          <th>Amount</th>
          <td class="text-left">{{$response['amount']}}<i class="fa fa-rupee"></i></td>
        </tr>
      </table>
      <p class="text-center">You can close this tab and return to previous page to try again.</p>
      <p class="text-center"><a href="javascript:void(0)" onclick="window.close()" class="btn btn-theme" style="color:#fff"> Close</a></p>
    </div>
  </div>  

  @endif  
</div>
</body>
</html>