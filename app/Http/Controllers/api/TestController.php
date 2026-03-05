<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemConfig;
use App\models\Transactions;
use App\Utility\SuperApiSecurityLayer;
use App\Utility\ApiSecurityLayer;
use Illuminate\Support\Facades\DB;


class TestController extends Controller
{

    public function test1(Request $request){
        echo 'Hello';

          $status = $this->sendSms('9999','9892630464');

    }
	
	public function test(Request $request)
    {

		$data = $request->post();  
      	if (ApiSecurityLayer::checkAuthorization())
        {
        	$rules = [
                'key' => 'required',
                'device_type' => 'required',
                'user_id' => 'required',
            ];

            $messages = [
                'key.required' => 'Key is required',
                'device_type.required' => 'Device type is required',
                'user_id.required' => 'User id is required',
            ];

            $validator = \Validator::make($request->post(),$rules,$messages);

            if ($validator->fails()) {
                return response()->json([false,'status'=>400,'message'=>ApiSecurityLayer::getMessage($validator->errors()),$validator->errors()],400);
            }
  
            $user_id = ApiSecurityLayer::fetchUserId();

            if (!empty($data['key'])) {
               
                if (1)
                {
                    $HTTP_HOST = $_SERVER['HTTP_HOST'];
                    $PHP_SELF = $_SERVER['PHP_SELF'];

                    if(array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on"){
                        $server_type = 'https';
                    } else {
                        $server_type = 'http';
                    }

                    $domain = \Request::getHost();
                    $subdomain = explode('.', $domain);

                    $payment = DB::table('payment_gateway')
                                ->select('*')
                                ->join('payment_gateway_config', 'payment_gateway.id', '=', 'payment_gateway_config.pg_id')
                                ->where('payment_gateway.pg_name', '=', 'instaMojo')
                                ->get();

                    $user = DB::table('user_table')
                                ->select('*')
                                ->where('user_table.id', '=', $data['user_id'])
                                ->get();            

                    $amount = $payment[0]->amount;
                    $fullname = $user[0]->fullname;
                    $email_id = $user[0]->email_id;
                    $mobile_no = $user[0]->mobile_no;

                    if($payment[0]->crendential)
                    {
                        $X_Api_Key = $payment[0]->merchant_key;
                        $X_Auth_Token = $payment[0]->salt;
                        $endpoint = 'https://www.instamojo.com/api/1.1/';
                    }else{
                        $X_Api_Key = $payment[0]->test_merchant_key;
                        $X_Auth_Token = $payment[0]->test_salt;
                        $endpoint = 'https://test.instamojo.com/api/1.1/payment-requests/';
                    }           

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $endpoint);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                                array("X-Api-Key:".$X_Api_Key,
                                      "X-Auth-Token:".$X_Auth_Token));

                    $payload = Array(
                        'purpose' => 'Certificate Request',
                        'amount' => $amount,
                        'phone' => $mobile_no,
                        'buyer_name' => $fullname,
                        'redirect_url' => $server_type.'://'.$HTTP_HOST.'/api/instamojoResponse?key='.$data['key']."&id=".$data['user_id'],
                        'email' => $email_id,
                        'allow_repeated_payments' => false
                    );
                    

                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
                    $response = curl_exec($ch);
                    curl_close($ch); 
                    $response = json_decode($response);
                    
                    $message = array('status' => 200, 'message' => "success", "URL" => $response->payment_request->longurl);
                }else{
                    $message = array('status'=>403, 'message' => 'User id is missing or You dont have access to this api.');
                }
            }else{
                $message = array('status'=>422, 'message' => 'Required parameters not found.');
            }

        }else{
            $message = array('status'=>403, 'message' => 'Access forbidden.');
        }


        $requestUrl = \Request::Url();
        $requestMethod = \Request::method();
        $requestParameter = $data;


        if ($message['status']==200) {
            $status = 'success';
        }
        else
        {
            $status = 'failed';
        }

        ApiSecurityLayer::insertTracker($requestUrl,$requestMethod,$requestParameter,$message,$status);
        
        return $message;
    }


    public function pdf(Request $request){
        

        //ini_set('sys_temp_dir ','none');
       // echo "abc";
        $file= public_path().'/demo/livetest.pdf';
       //exit;
       /* exit;
        $file='livetest.pdf';*/
       
        /* header('Content-type: application/pdf');
        header('Content-Disposition:inline; filename="'.$filename.'"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges:bytes');
        @readfile($file);*/
        
        $path = $file; //storage_path($file);
        $filename='pdf.pdf';
        /*return response::make(file_get_contents($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Content-Transfer-Encoding'=> 'binary',
            'Accept-Ranges'=>'bytes'
        ]);*/

        return response()->file($file, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Content-Transfer-Encoding'=> 'binary',
            'Accept-Ranges'=>'bytes'
        ]);
    }


      function sendSms($OTP,$mobileNo) {
        // dd(1);
        $text = "Your OTP for SeQR App Verification is ". $OTP .""."- Team SSSL";

        // \Log::debug("message:$text");

        if (strpos($text, 'OTP') !== false && (strlen($mobileNo) === 10)){ 
        
        $base_url="https://api.kaleyra.io/v1/";
        $api_key="A25dd681d149b289d3a4ce30b4ba67917";
        $sid="HXAP1649996084IN";
        // $call_back_url="https://seqrloyalty.com/demo/api/test2";
        // $sender="SQRLYT";
    
        // $type = "OTP";
        // $template_id="1007161113321208125";
    
        // $type = "TXN";   //"TXN", “TXND”, “MKT”
        // $template_id="1007161113228955876";
    
        $sender="SEQRDC";
        $type = "OTP";
        $template_id="1007161113135607610";
    
        // Set the URL
        $url = $base_url.$sid."/messages";
    
        // Set the headers
        $headers = array(
            "Content-Type: application/json",
            "api-key: ".$api_key
        );
    
        // Set the POST data
        $data = json_encode([
            "type" => "OTP",
            "sender"=>$sender,
            "to"=>"+91".$mobileNo,
          //  "callback"=>$call_back_url,
            "template_id"=>$template_id,
            "body"=>$text
        ]);
    
        // Initialize cURL session
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  
         $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,  
         1);
    
        // Execute the request
        $response = curl_exec($ch);
    
        // Check for errors
        if (curl_errno($ch)) {
            $resp= 'Error:' . curl_error($ch). ' | ' . date('Y-m-d H:i:s');
            // \Log::debug($resp);
            //   writeLog('SMSLogger.txt', $resp);
        } else {
            $resp= $response. ' | ' . date('Y-m-d H:i:s');
    
            // \Log::debug($resp);
        }
    
        // Close the cURL session
        curl_close($ch);
        }
    
    }
}
