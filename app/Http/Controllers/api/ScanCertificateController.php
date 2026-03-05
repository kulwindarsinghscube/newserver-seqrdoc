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
class ScanCertificateController extends Controller
{
    public function ScanViewCertificate(Request $request){        
        $data = $request->post(); 
        $hostUrl = \Request::getHttpHost();
        $subdomain = explode('.', $hostUrl);
        
        if (ApiSecurityLayer::checkAuthorization()) 
        {
            $rules = [
                    'key' => 'required',
                ];

            $messages = [
                'key.required' => 'Key is required',
            ];

            $validator = \Validator::make($request->post(),$rules,$messages);
                
            if ($validator->fails()) {
                $message = array('success' => false,'status'=>400, 'message' => ApiSecurityLayer::getMessage($validator->errors()));
               
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
                        

                        return $message;
            }

            // to fetch user id
                        
            $hostUrl = \Request::getHttpHost();
            $subdomain = explode('.', $hostUrl);

            $site = Site::select('site_id')->where('site_url',$hostUrl)->first();
            $site_id = $site['site_id'];
            $key = $data['key'];
            $scan_data = [];
            $studentData = StudentTable::where('key',$key)
                                        ->where('publish',1)
                                        ->where('site_id',$site_id)
                                        ->orderBy('id','DESC')
                                        ->first();   
             
            if (!empty($studentData)) {                
                $scan_result = $studentData['status'];
                $path = 'https://'.$subdomain[0].'.seqrdoc.com/';
                $certificateFilename = $path.$subdomain[0]."/backend/pdf_file/".$studentData['certificate_filename'];   
                $verifyFilename = $path.$subdomain[0]."/backend/verification_output/".$studentData['certificate_filename'];   
                $template_id = $studentData['template_id'];
                $studentData['fileUrlold'] = $certificateFilename;
                $studentData['fileUrl'] = $verifyFilename;
                $studentData['scan_result'] = $scan_result;
                $scan_data = $studentData;
                $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\pdf2pdf\\";
                #0:Template Maker, 1: PDF2PDF, 2:Custom Template
                if($studentData['template_type']==1){
                    $pyscript = $directoryUrlBackward."Python_files\\pdf2pdf_verification_pdf.py";
                    $cmd = "$pyscript $subdomain[0] $template_id $key 2>&1";
                }
                elseif($studentData['template_type']==2){
                    $pyscript = $directoryUrlBackward."Python_files\\custom_verification_pdf.py";
                    $cmd = "$pyscript $subdomain[0] $template_id $key 2>&1";
                }else{
                    $pyscript = $directoryUrlBackward."Python_files\\template_verification_pdf.py";
                    $cmd = "$pyscript $subdomain[0] $template_id $key 2>&1";
                }
                exec($cmd, $output, $return);
                //print_r($output);
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
            if(!empty($studentData)){                   
                $message = array('success' => true,'status'=>200, 'message' => 'Success','data' => $scan_data);                    
            }
            else
            {
                $message = array('success' => false,'status'=>400, 'message' => 'Unsuccess','data' => $key);
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
        return $message;
    }
    public function scanData(Request $request){
        dd($request->all());
    }
    
    function CallAPI($method,$url,$data)
    {

        $curl = curl_init();
        
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }




      public function crashApi(Request $request)
    {  

        return   $message = array('success' => false,'status'=>400, 'message' => 'No data found.');
    }
}
