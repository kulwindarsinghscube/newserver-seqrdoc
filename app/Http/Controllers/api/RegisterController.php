<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\WebUserRegisterRequest;
use App\models\Site;
use Hash;
use App\Jobs\SendMailJob;
use App\models\User;
use App\models\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Utility\ApiSecurityLayer;
use App\models\ApiTracker;
class RegisterController extends Controller
{
    public function userRegister(Request $request)
    {
        // dd($request->all());

        
        
        $data = $request->post();

        
        if(ApiSecurityLayer::checkAuthorization())
        {

            $domain = \Request::getHost();
            $subdomain = explode('.', $domain);
            if($subdomain[0]=="IMT"&&$subdomain[0]=="imt"){
                $rules =[
                    'fullname'   => 'required',
                    'email_id'   => 'required|email|unique:user_table,email_id',
                    'mobile_no'   => 'required|numeric|min:9|unique:user_table,mobile_no',
                    'username'   => 'required|unique:user_table,username',
                    'password' => 'required|min:8',
                ];

                $messages = [
                    'fullname.required'=>'Fullname is required',
                    'email_id.required'=>'Email is required',
                    'email_id.email'=>'Valid Email is required',
                    'email_id.unique'=>'Email ID already exists',
                    'mobile_no.required'=>'9 Digit Mobile number is required',
                    'mobile_no.numeric'=>'9 Digit Mobile number is required',
                    'mobile_no.min'=>'9 Digit Mobile number is required',
                    'mobile_no.unique'=>'Mobile no. already exists',          
                    'username.required'=>'Username is required',
                    'username.unique'=>'Username already exists',              
                    'password.required'=>'Min. 8 character Password is required',            
                    'password.min'=>'Min. 8 character Password is required', 
                ];
            }
            elseif (strtolower($subdomain[0]) == "mallareddyuniversity") {
                $rules = [
                    'fullname'          => 'required',
                    'email_id'          => 'required|email|unique:user_table,email_id',
                    'mobile_no'         => 'required|numeric|digits:10|unique:user_table,mobile_no',
                    'username'          => 'required|unique:user_table,username',
                    'password'          => 'required|min:8',
                    'organization_name' => 'required',
                ];

                $messages = [
                    'fullname.required'          => 'Fullname is required',
                    'email_id.required'          => 'Email is required',
                    'email_id.email'             => 'Valid Email is required',
                    'email_id.unique'            => 'Email ID already exists',
                    'mobile_no.required'         => '10 Digit Mobile number is required',
                    'mobile_no.numeric'          => '10 Digit Mobile number is required',
                    'mobile_no.digits'           => '10 Digit Mobile number is required',
                    'mobile_no.unique'           => 'Mobile no. already exists',
                    'username.required'          => 'Username is required',
                    'username.unique'            => 'Username already exists',
                    'password.required'          => 'Min. 8 character Password is required',
                    'password.min'               => 'Min. 8 character Password is required',
                    'organization_name.required' => 'Organization Name is required',
                ];
            }
            else{
                $rules =[
                    'fullname'   => 'required',
                    'email_id'   => 'required|email|unique:user_table,email_id',
                    'mobile_no'   => 'required|numeric|min:10|unique:user_table,mobile_no',
                    'username'   => 'required|unique:user_table,username',
                    'password' => 'required|min:8',
                ];

                $messages = [
                    'fullname.required'=>'Fullname is required',
                    'email_id.required'=>'Email is required',
                    'email_id.email'=>'Valid Email is required',
                    'email_id.unique'=>'Email ID already exists',
                    'mobile_no.required'=>'10 Digit Mobile number is required',
                    'mobile_no.numeric'=>'10 Digit Mobile number is required',
                    'mobile_no.min'=>'10 Digit Mobile number is required',
                    'mobile_no.unique'=>'Mobile no. already exists',          
                    'username.required'=>'Username is required',
                    'username.unique'=>'Username already exists',              
                    'password.required'=>'Min. 8 character Password is required',            
                    'password.min'=>'Min. 8 character Password is required', 
                ];  
            }

            $validator = \Validator::make($request->post(),$rules,$messages);
            if ($validator->fails()) {
                $message = array('success' => false,'status'=>400, 'message' => ApiSecurityLayer::getMessage($validator->errors()));
                // return response()->json([false,'status'=>400,'message'=>ApiSecurityLayer::getMessage($validator->errors()),$validator->errors()],400);
                $requestUrl = \Request::Url();
                $requestMethod = \Request::method();
                $requestParameter = $data;
                if ($message['success']==true) {
                    $status = 'success';
                }
                else
                {
                    $status = 'failed';
                }

                $api_tracker_id_otp = ApiSecurityLayer::insertTracker($requestUrl,$requestMethod,$requestParameter,$message,$status);

                $response_time = microtime(true) - LARAVEL_START;
                ApiTracker::where('id',$api_tracker_id_otp)->update(['response_time'=>$response_time]);

                return $message;
            }
            
            $generate_token = Hash::make($request->username.$request->mobile_no);
            $token = str_replace('/','',$generate_token);
            $OTP = $this->generateOTP();
            $hostUrl = $_SERVER['HTTP_HOST'];        
            $site = Site::select('site_id')->where('site_url',$hostUrl)->first();
            $site_id = $site['site_id'];
            $webuserData = [
                'fullname'   => $request['fullname'],
                'username'   => $request['username'],
                'password'   => Hash::make($request['password']),
                'email_id'   => $request['email_id'],
                'mobile_no'  => $request['mobile_no'],
                'verify_by'  => $request['verify_by'],
                'status'     => '1',
                'device_type'=> 'mobile',
                'token'      => $token,
                'OTP'        => $OTP,
                'site_id'    => $site_id,
            ];

            // Add organization_name only for Mallareddy
            if (strtolower($subdomain[0]) == 'mallareddyuniversity') {
                $webuserData['organization_name'] = $request['organization_name'];
            }
        //    dd($webuserData);

            $webuser = User::create($webuserData);

            
            if($request['verify_by'] == 2){
                //sending mail
                $mail_view = 'mail.index';
                $user_email = $request['email_id'];
                
                $mail_subject = 'Activate your account for SeQR Mobile App';
                $user_data = ['name'=>$request['username'],'token'=>$token];

                $this->dispatch(new SendMailJob($mail_view,$user_email,$mail_subject,$user_data));
            }else{
                 $status = $this->sendSms($OTP,$request['mobile_no']);
            }
            $webuser['accesstoken'] ='';
           
                        
            $message = array('success'=>true,'status'=>200,'message'=>'New User Successfully Registered', 'data' => $webuser);
        }
        else
        {
            $message = array('success'=>false,'status'=>403, 'message' => 'Access forbidden.');
        } 

        $requestUrl = \Request::Url();
        $requestMethod = \Request::method();
        $requestParameter = $data;

        if ($message['success']==true) {
            $status = 'success';
        }
        else
        {
            $status = 'failed';
        }

        $api_tracker_id = ApiSecurityLayer::insertTracker($requestUrl,$requestMethod,$requestParameter,$message,$status);
        
        $response_time = microtime(true) - LARAVEL_START;
        ApiTracker::where('id',$api_tracker_id)->update(['response_time'=>$response_time]);
        return $message;   
    }


    public function mobileNoVerify(Request $request)
    {
        $data = $request->post();
        
        if (ApiSecurityLayer::checkAuthorization()) 
        {   
 
                $rules = [
                    'mobile_no' => 'required|numeric|min:10',
                    'otp' => 'required|numeric|min:5',
                ];

                $messages = [
                    'otp.required' => '5 Digit otp is required',
                     'otp.numeric' => '5 Digit otp is required',
                     'otp.numeric' => '5 Digit otp is required',
                     'mobile_no.required'=>'10 Digit Mobile number is required',
                'mobile_no.numeric'=>'10 Digit Mobile number is required',
                'mobile_no.min'=>'10 Digit Mobile number is required',
                ];

                $validator = \Validator::make($request->post(),$rules,$messages);

                if ($validator->fails()) {
                       $message = array('success' => false,'status'=>400, 'message' => ApiSecurityLayer::getMessage($validator->errors()));
               // return response()->json([false,'status'=>400,'message'=>ApiSecurityLayer::getMessage($validator->errors()),$validator->errors()],400);
                $requestUrl = \Request::Url();
                        $requestMethod = \Request::method();
                        $requestParameter = $data;
                if ($message['success']==true) {
                            $status = 'success';
                        }
                        else
                        {
                            $status = 'failed';
                        }

                        $api_tracker_id_otp = ApiSecurityLayer::insertTracker($requestUrl,$requestMethod,$requestParameter,$message,$status);

                        $response_time = microtime(true) - LARAVEL_START;
                        ApiTracker::where('id',$api_tracker_id_otp)->update(['response_time'=>$response_time]);

                        return $message;
                }

                
                    $result = \DB::table('user_table')->select('*')->where('OTP',$request['otp'])
                                                                         ->where('mobile_no',$request['mobile_no'])
                                                                          ->first();
                    if($result){

                    if($result->is_verified == 1){
                       $message = array('success'=>false,'status'=>400,'message'=>'User is already verified.');
                    }
                    else{
                       
                        \DB::table('user_table')->where('mobile_no',$request['mobile_no'])->update(['is_verified'=>'1']);

                        $session_id = ApiSecurityLayer::generateAccessToken();
                        $login_time = date('Y-m-d H:i:s');
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $id = $result->id;
                        //storing session details in db
                        $sessionData = new SessionManager();
                        $sessionData->user_id = $result->id;
                        $sessionData->session_id = $session_id;
                        $sessionData->login_time = $login_time;
                        $sessionData->is_logged = 1;
                        $sessionData->device_type = 'mobile';
                        $sessionData->ip = $ip;
                        $sessionData->save();
                   
                       // $webuser['accesstoken'] = $session_id;
                        header("accesstoken:" . $session_id);
                        $message = array('success'=>true,'status'=>200,'message'=>'success','data'=>$result);
                    }
                }else{
                    $message = array('success' => false,'status'=>403, 'message' => 'Wrong OTP or mobile no.');
                }
          
        }
        else
        {
            $message = array('success' => false,'status'=>403, 'message' => 'Access forbidden.');
        }

        $requestUrl = \Request::Url();
        $requestMethod = \Request::method();
        $requestParameter = $data;

        if ($message['success']==true) {
            $status = 'success';
        }
        else
        {
            $status = 'failed';
        }

       $api_tracker_id_mobile = ApiSecurityLayer::insertTracker($requestUrl,$requestMethod,$requestParameter,$message,$status);
        
        $response_time = microtime(true) - LARAVEL_START;
        ApiTracker::where('id',$api_tracker_id_mobile)->update(['response_time'=>$response_time]);
        return $message;
    }

    public function resendOtp(Request $request)
    {
         
        $data = $request->post();
        
        if (ApiSecurityLayer::checkAuthorization()) 
        {   
        

                   
                $rules = [
                    'mobile_no' => 'required|numeric|min:10'
                ];

                $messages = [
                     'mobile_no.required'=>'10 Digit Mobile number is required',
                    'mobile_no.numeric'=>'10 Digit Mobile number is required',
                    'mobile_no.min'=>'10 Digit Mobile number is required',
                ];
                 $validator = \Validator::make($request->post(),$rules,$messages);

                if ($validator->fails()) {
                      $message = array('success' => false,'status'=>400, 'message' => ApiSecurityLayer::getMessage($validator->errors()));
               // return response()->json([false,'status'=>400,'message'=>ApiSecurityLayer::getMessage($validator->errors()),$validator->errors()],400);
                $requestUrl = \Request::Url();
                        $requestMethod = \Request::method();
                        $requestParameter = $data;
                if ($message['success']==true) {
                            $status = 'success';
                        }
                        else
                        {
                            $status = 'failed';
                        }

                        $api_tracker_id_otp = ApiSecurityLayer::insertTracker($requestUrl,$requestMethod,$requestParameter,$message,$status);

                        $response_time = microtime(true) - LARAVEL_START;
                        ApiTracker::where('id',$api_tracker_id_otp)->update(['response_time'=>$response_time]);

                        return $message;
                }
                 
               
                    $result = \DB::table('user_table')->select('*')->where('mobile_no',$request['mobile_no'])->first();
                 if($result){

                    if($result->is_verified == 1){
                       $message = array('success'=>false,'status'=>400,'message'=>'User is already verified.');
                    }
                    else{
                       $OTP = $this->generateOTP();
                        \DB::table('user_table')->where('mobile_no',$request['mobile_no'])->update(['OTP'=>$OTP]);
                        $status = $this->sendSms($OTP,$request['mobile_no']);
                       
                        $message = array('success'=>true,'status'=>200,'message'=>'Otp sent successfully.');
                    }
                }else{
                    $message = array('success' => false,'status'=>403, 'message' => 'User not found with this mobile no.');
                }
               
           
        }
        else
        {
            $message = array('success' => false,'status'=>403, 'message' => 'Access forbidden.');
        }

        $requestUrl = \Request::Url();
        $requestMethod = \Request::method();
        $requestParameter = $data;

        if ($message['success']==true) {
            $status = 'success';
        }
        else
        {
            $status = 'failed';
        }

       $api_tracker_id_otp = ApiSecurityLayer::insertTracker($requestUrl,$requestMethod,$requestParameter,$message,$status);
        
        $response_time = microtime(true) - LARAVEL_START;
        ApiTracker::where('id',$api_tracker_id_otp)->update(['response_time'=>$response_time]);
        return $message;
    }


    public function generateOTP(){
        $digits = 5;
        $OTP = rand(pow(10, $digits-1), pow(10, $digits)-1); //5 Digit OTP
        return $OTP;
    }

    private function sendSmsV1($OTP,$mobile_no){
        $apiKey = "A32ba2b0a6770c225411fd95ff86401c4";
        $message = urlencode("Your OTP for SeQR App Verification is ". $OTP .""."- Team SSSL");
        $sender_id = "SEQRDC";
        
        $url = "https://alerts.solutionsinfini.com/api/v4/?api_key=". $apiKey ."&method=sms&message=". $message ."&to=". $mobile_no ."&sender=". $sender_id ."";
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        $result=curl_exec($ch);
        curl_close($ch);
        
        $data = (json_decode($result, true));
        
        if($data['status'] == "OK"){
            return 1;
        }
        else return 0;
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
    
        // $type = "TXN"; 	//"TXN", “TXND”, “MKT”
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