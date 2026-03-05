<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Session;

use App\models\StudentTable;
use App\models\SbStudentTable;
use App\models\Site;
use App\models\TemplateMaster;
use App\Helpers\CoreHelper;
use DB;
use Storage;
use App\models\Demo\Site as DemoSite;
use App\Utility\BlockChain;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class VerifyV1Controller extends Controller
{

    public function bverify() {
        return view('bverify_v1');
    }



    public function callPdfData(Request $request) {


        // $client = new \GuzzleHttp\Client();

        $envValue = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkaWQ6ZXRocjoweEZiNmI4NzhmNmUwMEY3MDdhMGZkM0JGNjIyN0M1MzI3ZDA3OWUzYzUiLCJpc3MiOiJuZnQtc3RvcmFnZSIsImlhdCI6MTY5ODMwMzczNDIxMSwibmFtZSI6Imhvc3Rpbmcgc2VjdXJlIGRvY3MgZmlsZXMifQ.9Lbc93JLoNSw3LKnV8aLKEMPasq0fPwKnpoU_7iBKr0';

        // header("Access-Control-Allow-Origin: *");
        // $url = 'https://mainnet-apis.herokuapp.com/v1/mainnet/getFileHash';
        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     'Authorization: Bearer '.$envValue,
        //     'Content-Type: application/pdf',
        // ]);
        // $response = curl_exec($ch);
        // curl_close($ch);
        // echo $response;

        // print_r($response);
        // echo "tets";
        // // $message = array('success' => true,'status'=>200, 'resArr' => $response); 

        // // return $message;
        
        // Validate the incoming request to ensure it has a file
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf',
        ]);

        $file = $request->file('file');
        
        // print_r($file);

        // $client = new Client();

        // // $file_path = public_path().'/blockchaintest.pdf';

        // $fileStream = fopen($file->getPathname(), 'r');


        // // if ($file->isValid()) {
        // //     // Add debugging
        // //     \Log::info('File is valid: ' . $file->getClientOriginalName());
        // // } else {
        // //     \Log::error('File is invalid.');
        // // }

        // // print_r($file->getPathname());
        // try {
        //     $response = $client->request('POST', 'https://mainnet-apis.herokuapp.com/v1/mainnet/getFileHash', [
        //         'headers' => [
        //             'Authorization' => $envValue,
        //             // 'Content-Type' => 'multipart/form-data',
        //             'Content-Type'=> 'application/pdf',
        //         ],
        //         'multipart' => [
        //             [
        //                 'name'     => 'file',
        //                 'contents' => $fileStream,
        //                 'filename' => $file->getClientOriginalName(),
        //             ]
        //         ],
        //     ]);

        //     fclose($fileStream);
        //     $responseData = json_decode($response->getBody()->getContents(), true);

        //     return response()->json([
        //         'message' => 'File uploaded successfully!',
        //         'data'    => $responseData,
        //     ]);

        // } catch (RequestException $e) {
        //     fclose($fileStream);
        //     // $errorMessage = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
        //     // return response()->json([
        //     //     'message' => 'Failed to upload file.',
        //     //     'error'   => $errorMessage,
        //     // ], 500);

        //     $response = $e->getResponse();
        //     $statusCode = $response ? $response->getStatusCode() : 'No response';
        //     $errorBody = $response ? $response->getBody()->getContents() : 'No body';

        //     \Log::error("File upload failed with status: $statusCode, body: $errorBody");

        //     return response()->json([
        //         'message' => 'Failed to upload file.',
        //         'error'   => $statusCode . ': ' . $errorBody,
        //     ], 500);

        // }
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);


        $file_path = public_path().'/blockchaintest.pdf';
            
        // $file_path ='https://mitwpu.seqrdoc.com/blockchaintest.pdf';

        // echo $file_path;


        $filePath = $file; // Replace with the path to your file
        // $filePath = $file_path; // Replace with the path to your file

        // if (!file_exists($filePath)) {
        //     die('File does not exist.');
        // }

        $apiToken = $envValue; // Replace with your API token


        // Initialize cURL
       $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://mainnet-apis.herokuapp.com/v1/mainnet/getFileHash',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('document'=> new \CURLFile($filePath)),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        
        // Decode the JSON response
        $responseData = json_decode($response, true);

        // Check for any JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Failed to decode JSON response: ' . json_last_error_msg(),
                'response' => $response
            ];
        }

        // Return or print the response
        $result = [
            'success' => true,
            'response' => $responseData
        ];

        return $result;
        // print_r($result); // To output the response in a readable format

        // // You can also access specific fields, like:
        // echo "Pinata IPFS Hash: " . $responseData['pinataIpfsHash'];
    


    }
    

    public function verifyPdf(Request $request)
    {  
        $data = $request->post();
        $hostUrl = \Request::getHttpHost();
        $subdomain = explode('.', $hostUrl);

        if(isset($data['pdfData'])){

             $site = Site::select('site_id')->where('site_url',$hostUrl)->first();
          
            $site_id = $site['site_id'];
            $studentData = StudentTable::where('pinata_ipfs_hash',$data['pdfData'])
                                                ->where('publish',1)
                                                ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();

         
            if($studentData){
                $key = $studentData['key'];
                $siteData = DemoSite::select('bc_wallet_address')->where('site_url',$hostUrl)->first();
                //print_r($siteData);
                if(isset($siteData['bc_wallet_address'])&&!empty($siteData['bc_wallet_address'])){
                    $data['walletID']=$siteData['bc_wallet_address'];
                    $data['uniqueHash']=$key;
                    $data['template_id']=$studentData['template_id'];
                    
                     // print_r($studentData);
                     // exit;
                    if(($subdomain[0]=="demo" && ($studentData['template_id']==2||$studentData['template_id']==698||$studentData['template_id']==718||$studentData['template_id']==719||$studentData['template_id']==744||$studentData['template_id']==137||$studentData['template_id']==144||$studentData['template_id']==145||$studentData['template_id']==162))||$subdomain[0]=="mitwpu"||$subdomain[0]=="anu"||$subdomain[0]=="mpkv"||$subdomain[0]=="ksu"||$subdomain[0]=="konkankrishi"){
                    $mode=1;
                    }else{
                    $mode=0;
                    }
                    // print_r($data);
                    // exit;
                    $response=CoreHelper::retreiveDetails($data);


                    
                    if($response['status']==200){

                        $dataR=$response['data'];
                        $dataR['walletID']=$data['walletID'];

                        if($subdomain[0] =='anu') {
                            $dataR['pdfUrl']='https://verification.anu.edu.in/anu/backend/pdf_file/'.$studentData['certificate_filename'];
                        } else {
                            $dataR['pdfUrl']=$dataR['IPFS_URL'];
                        }

                        if(($subdomain[0]=="demo" && ($studentData['template_id']==2||$studentData['template_id']==698||$studentData['template_id']==718||$studentData['template_id']==719||$studentData['template_id']==744||$studentData['template_id']==137||$studentData['template_id']==144||$studentData['template_id']==145||$studentData['template_id']==162))||$subdomain[0]=="mitwpu"||$subdomain[0]=="anu"||$subdomain[0]=="mpkv"||$subdomain[0]=="ksu"||$subdomain[0]=="konkankrishi"){
                        $dataR['polygonTxnUrl']="https://polygonscan.com/tx/".$studentData['bc_txn_hash'];
                        }else{
                        $dataR['polygonTxnUrl']="https://mumbai.polygonscan.com/tx/".$studentData['bc_txn_hash'];    
                        }

                        if($studentData['template_type']==1){
                           // $checkContract = TemplateMaster::select('bc_contract_address')->where('id',$studentData['template_id'])->first();

                             $checkContract = DB::table("uploaded_pdfs")
                               ->select('bc_contract_address')
                               ->where('id',$studentData['template_id'])
                               ->get();
                               
                            if($checkContract&&!empty($checkContract[0]->bc_contract_address)){
                               
                                $dataR['contractAddress']=$checkContract[0]->bc_contract_address;
                            }else{
                                $dataR['contractAddress']="";  
                            }
                            
                        }else{

                            $checkContract = TemplateMaster::select('bc_contract_address')->where('id',$studentData['template_id'])->first();

                            if($checkContract&&!empty($checkContract['bc_contract_address'])){
                               
                                $dataR['contractAddress']=$checkContract['bc_contract_address'];
                            }else{
                                $dataR['contractAddress']="";  
                            }

                        }
                        
                       
                        $dataR['txnHash']=$studentData['bc_txn_hash'];
                        
                        $data=$dataR;
                        $data['status']=200;  
                        $data['success']=true; 
                        $data['message']="success";

                        /*print_r($data);
                        exit;*/
                        //return view('bverify.index',compact('data'));
                    }else{

                      /*  $response['status']=400;  
                        $response['message']="Details not found."; */
                        $data['status']=400; 
                        $data['success']=false;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Please try after sometime."; 
                         //return view('bverify.failed',compact('data'));
                           
                    }
                }else{
                    // $response['status']=400;
                    // $response['message']="Wallet address not found.";
                        $data['status']=400;  
                        $data['success']=false;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Something went wrong."; 
                       //  return view('bverify.failed',compact('data'));
                }


                $message=$data;
            // $message = array('success' => true,'status'=>200, 'message' => 'Pdf verified sucessfully.');
            }else{
               $message = array('success' => false,'status'=>400, 'message' => 'The Uploaded pdf not found in our system.');  
            }
        }else{
             $message = array('success' => false,'status'=>403, 'message' => 'Access forbidden.');
        }

       return $message;


    }

    
}
