<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemConfig;
use App\models\Transactions;
use App\Utility\SuperApiSecurityLayer;
use App\Utility\ApiSecurityLayer;
use Illuminate\Support\Facades\DB;


class StatementController extends Controller
{
	
	public function fetchStatement(Request $request)
    {

		$data = $request->post();  
      	if (ApiSecurityLayer::checkAuthorization())
        {
        	$rules = [
                'account_number' => 'required',
                'start_date'     => 'required|date',
                'end_date'       => 'required|date|after_or_equal:start_date',
            ];

            $messages = [
                'account_number.required' => 'Account Number is required',
                'start_date.required'     => 'Start Date is required',
                'start_date.date'         => 'Start Date must be a valid date',
                'end_date.required'       => 'End Date is required',
                'end_date.date'           => 'End Date must be a valid date',
                'end_date.after_or_equal' => 'End Date must be after or equal to Start Date',
            ];

            $validator = \Validator::make($request->post(),$rules,$messages);

            if ($validator->fails()) {
                return response()->json([false,'status'=>400,'message'=>ApiSecurityLayer::getMessage($validator->errors()),$validator->errors()],400);
            }
  
            $user_id = ApiSecurityLayer::fetchUserId();

            if (!empty($data['account_number'])) {
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
                    // $domain = 'abyssinia.seqrdoc.com';
                    $subdomain = explode('.', $domain);


                    $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                    $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
                    $pdf_file = $path.''.$subdomain[0].'/backend/pdf_file/723019.pdf';
                    $message = array('status' => 200, 'message' => "success", "file_url" => $pdf_file);
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

}
