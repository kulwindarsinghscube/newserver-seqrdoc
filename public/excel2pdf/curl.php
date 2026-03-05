
<?php

function callAPI($method, $url, $data){
   $curl = curl_init();
   switch ($method){
      case "POST":
         curl_setopt($curl, CURLOPT_POST, 1);
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
         break;
      case "PUT":
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
         break;
      default:
         if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
   }
   // OPTIONS:
   curl_setopt($curl, CURLOPT_URL, $url);
   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/x-www-form-urlencoded',
      'Accept: application/json',
   ));
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_HEADER, 1);
   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   // EXECUTE:
   $result = curl_exec($curl);
    //$info = curl_getinfo($curl);
   // print_r($info);
   if(!$result){die("Connection Failure");}
   curl_close($curl);
   return $result;
}

/*
curl -v "https://api.aspose.cloud/oauth2/token" \
-X POST \
-d 'grant_type=client_credentials&client_id=8ee71aa0-d2b1-4ba7-8bb9-6458a0dc206a&client_secret=ab784aefcefa090bc794441ffc51d84b' \
-H "Content-Type: application/x-www-form-urlencoded" \
-H "Accept: application/json" */


$data_array =  array(
    "grant_type"        => "client_credentials",
    "client_id"         =>"8ee71aa0-d2b1-4ba7-8bb9-6458a0dc206a",
    "client_secret"         =>"ab784aefcefa090bc794441ffc51d84b"
);
//echo urlencode($data_array);
//exit
//https://api.aspose.cloud/connect/token
$data='grant_type=client_credentials&client_id=8ee71aa0-d2b1-4ba7-8bb9-6458a0dc206a&client_secret=ab784aefcefa090bc794441ffc51d84b';
$make_call = callAPI('POST', 'https://api.aspose.cloud/oauth2/token',$data);//json_encode($data_array));////
print_r($make_call);
$response = json_decode($make_call, true);
$errors   = $response['response']['errors'];
$data     = $response['response']['data'][0];

///print_r($data);
//print_r($errors);
//print_r($response);