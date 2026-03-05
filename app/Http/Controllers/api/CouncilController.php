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
use Mail;
class CouncilController extends Controller
{

    public function VerifyDocument(Request $request)
    {  
     
        $data = $request->post();
        $hostUrl = \Request::getHttpHost();
        $subdomain = explode('.', $hostUrl);
        $awsS3Instances = \Config::get('constant.awsS3Instances');

        if (ApiSecurityLayer::checkAuthorization() || 1==1) 
        {   

            $rules = [
                    'API_KEY' => 'required'
                ];

            $messages = [
                'API_KEY.required' => 'Key is required'
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
                
                $key = $data['API_KEY'];
                $scan_data = [];
                
                $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();
                if(!$studentData){
                    $key = $data['QRCODE'];
                    $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();
                } 


                
                if (!empty($studentData)) {
                    if($studentData['status']=='1'){


                        $scan_result = $studentData['status'];
                       
                            //if(in_array($subdomain[0], $awsS3Instances)){ 
                                $certificateFilename = \Config::get('constant.s3bucket_base_url').$subdomain[0]."/varify/".$studentData['certificate_filename']; 
                            // }else{ 
                                
                            //         $certificateFilename = $path.$subdomain[0]."/backend/pdf_file/".$studentData['certificate_filename'];
                                
                                
                            // }

                        if(!file_exists($certificateFilename)){

                            $certificateFilename = "https://".$subdomain[0].".seqrdoc.com/".$subdomain[0]."/backend/pdf_file/".$studentData['certificate_filename'];

                        }        
                        
                        $studentData['fileUrl'] = $certificateFilename;
                        $studentData['scan_result'] = $scan_result;
                        $scan_data = $studentData;

                       
                        $payment_status = false;
                        
                        $studentData['payment_status'] = $payment_status;
                    }else{
                        $scan_result = $studentData['status'];
                        $gotData = [];
                        $gotData['status'] = $studentData['status'];
                        $gotData['message'] ="The document scanned in not Active.";
                        $scan_data = $gotData;
                    }


                    $date = date('Y-m-d H:i:s');
                
                
                    //$students = StudentTable::where(['key'=>$key])->first();
                   
                    

                    $document_id = $studentData['serial_no'];
                    $document_status = $studentData['status'];
                    
                    $scanndHistory = new ScannedHistory();
                    $scanndHistory['date_time'] = $date;
                    $scanndHistory['device_type'] = '';
                    $scanndHistory['scanned_data'] = $key;
                    $scanndHistory['scan_by'] = 0;
                    $scanndHistory['scan_result'] = $scan_result;
                    $scanndHistory['site_id'] = $site_id;
                    $scanndHistory['document_id'] = $document_id;
                    $scanndHistory['document_status'] = $document_status;
                    $scanndHistory->save();
                    
                   
                    $updateStudentData = StudentTable::where('key',$key)
                                                            ->update(['scan_count' => \DB::raw('scan_count + 1')]);

                }
                else
                {
                    
                    $scan_result = 2;
                    $gotData = [];
                    $gotData['status'] = 2;
                    $gotData['message'] ="Certificate not found!.";
                    $scan_data = $gotData;
                    
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
            $headers = apache_request_headers();
            $message = array('success' => false,'status'=>403, 'message' => 'Access forbidden.',"header"=>str_replace("\\", "", $headers['Apikey']));
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

    public function sendMail(Request $request){
        $deaultApiKey = '7B6E7CB2A0B5BCFCCE15B17A810F3E3A';

        $request_data = $request->all();
        if(empty($request_data['Phone']) || empty($request_data['Details']) || empty($request_data['Document_Name']) || empty($request_data['Name']) || empty($request_data['Time'])){
             return response()->json(['Message'=>'The required parameters for processing are missing.','Status'=>203]);
        }
        if (ApiSecurityLayer::checkAuthorization()){
        //if($deaultApiKey == $request_data['API_KEY']){
            $random = '#'.mt_rand(111111, 999999);
            // $subject = $random.' Mismatch report for SeQR Docs.';
            $request_data['subject'] = $random.' Mismatch report for SeQR Docs.';
            Mail::send([],  [], function($message) use ($request_data)
            {   
               
                $email = ['software@scube.net.in']; //software
                $message->from('info@seqrloyalty.com', 'SeQR Loyalty');
                $message->to($email)->subject($request_data['subject']);
                // $message->to('rushik9994@gmail.com') ->subject('Enquiry');
               
                $message->setBody("Dear Team,
<br><br> A mismatch was reported today at <b>".$request_data['Time']."</b> by <b>".$request_data['Name']."</b> for document name <b>".$request_data['Document_Name']."</b>.
<br><br> <b>Mismatch Details: </b>".$request_data['Details']." .
<br><br> ".$request_data['Name']."'s Contact No.: ".$request_data['Phone']."
<br><br> Regards,
<br>SeQR Docs App.", 'text/html');
                // $message->to($emails)->subject('This is test e-mail');    
            });
            // Details submitted sucessfully','Thank you for mismatch details. We will contact you further, if required
            return response()->json(['Message'=>' Details submitted sucessfully, Thank you for mismatch details. We will contact you further, if required.','Status'=>200]);
        }else{

            return response()->json(['Message'=>'Invalid API KEY.','Status'=>202]);
        }
    }
    
}
