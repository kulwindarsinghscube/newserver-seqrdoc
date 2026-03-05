<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\StudentTable;
use App\models\SbStudentTable;
use App\models\ScannedHistory;
use App\models\SbScannedHistory;
use App\models\SystemConfig;
use App\models\Site;
use App\models\Transactions;
use App\models\SbTransactions;
use App\models\PrintingDetail;
use App\models\SbPrintingDetail;
use Illuminate\Support\Facades\Auth;
use App\Utility\ApiSecurityLayer;
use Illuminate\Support\Facades\DB;
use App\models\ApiTracker;
use App\Helpers\CoreHelper;

class ScanNewController extends Controller
{
    
    public function scan(Request $request)
    {  
     
        $data = $request->post();
        $hostUrl = \Request::getHttpHost();
        $subdomain = explode('.', $hostUrl);
        $awsS3Instances = \Config::get('constant.awsS3Instances');

        // if (ApiSecurityLayer::checkAuthorization()) 
        if (1 == 1) 
        {   


            if($subdomain[0]=="monad"){
                $response=CoreHelper::checkMonadFtpStatus();
            
        
                if(!$response['status']){
                    $scan_result = 2;
                    $gotData = [];
                    $gotData['status'] = 2;
                    $gotData['message'] =$response['message'];
                    $scan_data = $gotData;
                    $message = array('success' => false,'status'=>400, 'message' =>$response['message'],"data"=>$gotData);
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

            }

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
            
            // to fetch user id
            $user_id = ApiSecurityLayer::fetchUserId();

            // if (!empty($user_id) /*&& ApiSecurityLayer::checkAccessToken($user_id)*/&& $user_id==$data['user_id']) 
            if (1==1) 
            {
                
                /* $hostUrl = \Request::getHttpHost();
                $subdomain = explode('.', $hostUrl);*/

                $site = Site::select('site_id')->where('site_url',$hostUrl)->first();
                $site_id = $site['site_id'];
               

                $systemConfig = SystemConfig::select('varification_sandboxing')->where('site_id',$site_id)->first();
                
                if($systemConfig['varification_sandboxing'] == 1){
                 
                    $sandbox =  $this->scanSandboxing($request,$site_id);
                    
                    $message = array('success' => true,'status'=>200, 'message' => 'Success','data' => $sandbox['data']);
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
                
                $key = base64_decode($data['key']);
                $scan_data = [];
                if($subdomain[0]=="mpkv"){
                    $studentData = StudentTable::where('key',$key)
                                                ->where('status',1)
                                                ->where('publish',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();
                
                }else{
                    $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();
                }
                if (!empty($studentData)) {
                    if($studentData['status']=='1'){
                        $scan_result = $studentData['status'];
                        $path = 'https://'.$subdomain[0].'.seqrdoc.com/';
                        if($subdomain[0]=='kmtc'&&$studentData['template_id']=='13'){
                            $certificateFilename = $path.$subdomain[0]."/backend/pdf_file/Original.pdf";
                        }elseif($subdomain[0]=='imt' && $studentData['template_id']=='101'){
                           $certificateFilename = \Config::get('constant.lrmis_base_url').$studentData['certificate_filename'];
                        }elseif($subdomain[0]=='monad'){
                           $certificateFilename = \Config::get('constant.monad_base_url')."pdf_file/".$studentData['certificate_filename'];
                        }else{
                            if(in_array($subdomain[0], $awsS3Instances)){ 
                                $certificateFilename = \Config::get('constant.s3bucket_base_url').$subdomain[0]."/backend/pdf_file/".$studentData['certificate_filename']; 
                            }else if($subdomain[0]=='test'||($subdomain[0]=='demo')){
                                //$certificateFilename = 'https://'.$subdomain[0].'.seqrdoc.com/api/pdf/'.$studentData['serial_no'].'/1/1';
                                $certificateFilename = 'https://'.$subdomain[0].'.seqrdoc.com/api/displayPdf/'.$data['key'];
                               //echo $filename1 = \Storage::disk('s3')->url($studentData['serial_no'].'.png');
                               /* echo $image_path = \Storage::disk('s3')->temporaryUrl(
                                                                                    'public/'.$subdomain[0].'/backend/pdf_file/'.$studentData['serial_no'].'.pdf',
                                                                                    Carbon::now()->addMinutes(5)
                                                                                );*/
                               // $certificateFilename = \Config::get('constant.s3bucket_base_url').$subdomain[0]."/backend/pdf_file/".$studentData['certificate_filename'];
                            }else{
                                //$cer = https://demo.seqrdoc.com/api/generateDecryptedPDF/;
                                $certificateFilename = $path.$subdomain[0]."/backend/pdf_file/".$studentData['certificate_filename'];
                                
                            }
                        }
                        $studentData['fileUrl'] = $certificateFilename;
                        $studentData['scan_result'] = $scan_result;
                        $scan_data = $studentData;

                        $transaction = Transactions::where('student_key',$key)
                                                        ->where('user_id',$data['user_id'])
                                                        ->where('trans_status','1')
                                                        ->where('publish','1')
                                                        ->where('site_id',$site_id)
                                                        ->get()->toArray();
                        if (count($transaction)>=1) {
                            $payment_status = true;
                        }
                        else
                        {
                            $payment_status = false;
                        }
                        $studentData['payment_status'] = $payment_status;
                    }else{
                        $scan_result = $studentData['status'];
                        $gotData = [];
                        $gotData['status'] = $studentData['status'];
                        $gotData['message'] ="The document scanned in not Active.";
                        $scan_data = $gotData;
                    }
                }
                else
                {
                    
                    $scan_result = 2;
                    $gotData = [];
                    $gotData['status'] = 2;
                    $gotData['message'] ="Certificate not found!.";
                    $scan_data = $gotData;
                    
                }

                $date = date('Y-m-d H:i:s');
                
                $students = StudentTable::where(['key'=>$key])->first();

                $document_id = $students['serial_no'];
                $document_status = $students['status'];
                
                $scanndHistory = new ScannedHistory();
                $scanndHistory['date_time'] = $date;
                $scanndHistory['device_type'] = $data['device_type'];
                $scanndHistory['scanned_data'] = $key;
                $scanndHistory['scan_by'] = $data['user_id'];
                $scanndHistory['scan_result'] = $scan_result;
                $scanndHistory['site_id'] = $site_id;
                $scanndHistory['document_id'] = $document_id;
                $scanndHistory['document_status'] = $document_status;
                $scanndHistory->save();
                
                if($subdomain[0]=="mpkv"){
                    $updateStudentData = StudentTable::where('key',$key)->where('status',1)
                                                        ->update(['scan_count' => \DB::raw('scan_count + 1')]);
                }else{
                    $updateStudentData = StudentTable::where('key',$key)
                                                        ->update(['scan_count' => \DB::raw('scan_count + 1')]);

                }
                if(!empty($studentData)){
                    $message = array('success' => true,'status'=>200, 'message' => 'Success','data' => $scan_data);
                }
                else
                {
                    $message = array('success' => false,'status'=>400, 'message' => 'Unsuccess','data' => $scan_data);
                }   
            }
            else
            {
                $message = array('success' => false,'status'=>400, 'message' => 'User id is missing or You dont have access to this api.');
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

        $api_tracker_id = ApiSecurityLayer::insertTracker($requestUrl,$requestMethod,$requestParameter,$message,$status);
        
        $response_time = microtime(true) - LARAVEL_START;
        ApiTracker::where('id',$api_tracker_id)->update(['response_time'=>$response_time]);
        return $message;
    }
    
   
}
