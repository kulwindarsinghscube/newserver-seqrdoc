<?php

namespace App\Http\Controllers\api\blockchain;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


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
use App\models\BcSmartContract;

class PdfController extends Controller
{
    private function isValid64base($str){
    if (base64_decode($str, true) !== false){
        return true;
    } else {
        return false;
    }
    }

    public function callPdfDataV1(Request $request) {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $fileHashInstances = \Config::get('constant.fileHashInstances');
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


        // $file_path = public_path().'/blockchaintest.pdf';
            
        // $file_path ='https://mitwpu.seqrdoc.com/blockchaintest.pdf';

        // echo $file_path;


        $filePath = $file; // Replace with the path to your file
        // $filePath = $file_path; // Replace with the path to your file

        // if (!file_exists($filePath)) {
        //     die('File does not exist.');
        // }
        // if($subdomain[0] =='anu') { 
        if(in_array($subdomain[0], $fileHashInstances)){
            $response1=CoreHelper::generateFileHash($filePath);
            $result = [
                'success' => true,
                'response' => $response1
            ];
            return $result;
        }

        $apiToken = $envValue; // Replace with your API token


        // Initialize cURL
       $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://mainnet-apis.herokuapp.com/v1/mainnet/getFileHash',
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

   public function showDetails(Request $request,$token){
        


        $valid=true;
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        if($domain == 'verification.anu.edu.in') {
            $domain = 'anu.seqrdoc.com';
            $subdomain[0] = 'anu';
        }

        $fileHashInstances = \Config::get('constant.fileHashInstances');

        $site = Site::select('site_id')->where('site_url',$domain)->first();
        $site_id = $site['site_id'];
        // $token="76DFF20704DDD5B0BD2AD04AF30AB792";
        // echo  $key = encrypt($token);
        // exit;
        //echo $token;

        
        if(!empty($token)){

          
            try {
               // echo "s";
                 //$key= base64_decode($token);
                //FOR PHP
                
                 $key = decrypt($token);
                
                //  $base64_string = "SGVsbG8gd29ybGQ=";
                // $decoded_data = base64_decode($token);

                // if ($decoded_data === false) {
                //     $key = decrypt($token);
                // } else {
                //    $key =  $decoded_data;
                // }
                
            } catch (DecryptException $e) {

                $key="";
                if($this->isValid64base($token)){
                    $key = base64_decode($token);


                    

                    //for PDF2PDF
                    $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();

                
                                             
                    
                    if($studentData){
                        $key=$key;
                    }else{
                        $result = json_decode($key);
                        if ($result === FALSE) {
                            // JSON is invalid
                            $key=$key;
                        }else{
                            //FOR exceptional case
                            $key =$this->decodeCustomLogic($token);
                        }
                    }
                 
                }else{
                    //FOR exceptional case
                    
                    $key =$this->decodeCustomLogic($token);
                  
                }
             
                // echo "BB";

                //print_r($e->getMessage());
            }
             //echo $key;
            // exit;
            
            // dd($key);
            if(!empty($key)){
            
            
         
            

                if($subdomain[0]=="mitwpu"){

                        $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                 ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->whereNotNull('bc_txn_hash')
                                                ->orderBy('id','DESC')
                                                ->first();
                        // print_r($studentData);
                   
                }else{
                    $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();
                }
            
                
            if($studentData){

                $siteData = DemoSite::select('bc_wallet_address')->where('site_url',$domain)->first();
                //print_r($siteData);
                if(isset($siteData['bc_wallet_address'])&&!empty($siteData['bc_wallet_address'])){

                    if($subdomain[0]=="mitwpu" || $subdomain[0]=="anu"|| $subdomain[0]=="verification" || $subdomain[0]=="unikbp"){

                        $checkContract = TemplateMaster::select('bc_contract_address')->where('id',$studentData['template_id'])->first();


                        
                        
                        if((isset($checkContract['bc_contract_address']) && empty($checkContract['bc_contract_address'])) || !isset($checkContract['bc_contract_address']) ){
                            $bc_contract_address  ="0x9d187c7C331301FB8CD383be6d4358552B0E223e";
                          
                        }else{
                            $bc_contract_address =   $checkContract['bc_contract_address'];
                        }
                       
                        $data['contractAddress']=$bc_contract_address;

                        if(isset($studentData->bc_sc_id)&&!empty($studentData->bc_sc_id)){
                            $contractData = DB::table('bc_smart_contracts')
                        ->select('wallet_address','smart_contract_address')
                        ->where('id','=',$studentData->bc_sc_id)
                        ->first();  

                        // dd($contractData);
                        if(!empty($contractData)){
                            $data['walletID']=$contractData->wallet_address;
                            $data['contractAddress']=$contractData->smart_contract_address;
                        }else{
                            $data['walletID']="";
                        }

                        }else{
                           $contractData = DB::table('bc_smart_contracts')
                        ->select('wallet_address')
                        ->where('smart_contract_address','=',$bc_contract_address)
                        ->first(); 

                        // dd($contractData);
                        if(!empty($contractData)){
                            $data['walletID']=$contractData->wallet_address;
                        }else{
                            $data['walletID']="";
                        }

                        }
                        
                        
                        
                       

                    } else {

                        $data['walletID']=$siteData['bc_wallet_address'];
                    }

                    //$data['walletID']=$siteData['bc_wallet_address'];
                    $data['uniqueHash']=$key;
                    $data['template_id']=$studentData['template_id'];
                    
                     // print_r($studentData);
                     // exit;
                    if(($subdomain[0]=="demo" && ($studentData['template_id']==2||$studentData['template_id']==698||$studentData['template_id']==718||$studentData['template_id']==719||$studentData['template_id']==744||$studentData['template_id']==137||$studentData['template_id']==144||$studentData['template_id']==145||$studentData['template_id']==162))||$subdomain[0]=="mitwpu"||$subdomain[0]=="anu"||$subdomain[0]=="mpkv"||$subdomain[0]=="ksu"||$subdomain[0]=="konkankrishi"){
                    $mode=1;
                    }else{
                    $mode=0;
                    }


                    // if($key=="1CDE726256C2CAD7FF3003CA76DCA260"){
                    //          dd($data);
                    //     }
                    // print_r($data);
                    // exit;
                    // if($subdomain[0]=="anu"){
                    //     dd($data);
                    // }
                    // if($subdomain[0]=="anu"){

                    //     echo"<pre>";print_r($data);
                    //     exit;
                    //     }

                    // if($subdomain[0] == "anu"){
                    //     $smart_contracts = BcSmartContract::where('wallet_address',"0xB509AF6532Af95eE59286A8235f2A290c26b5730")->first();
                       
                    //     if(!empty($smart_contracts)){
                    //         $data['contractAddress'] = $smart_contracts->smart_contract_address;
                    //     }
                    // }
                //     if($key=="17D41FE160F3B48BB2A9E43F876F32F0"){
                //         dd($data,$contractData);
                //    }
                // if($subdomain[0]=="mitwpu"){
                //     dd($key);
                //   }
                    $response=CoreHelper::retreiveDetails($data);
                    // dd($response);
                //     if($key=="17D41FE160F3B48BB2A9E43F876F32F0"){
                //         dd($response);
                //    }
                    if($response['status']==200){

                        //$response['status']=200;
                        $dataR=$response['data'];
                        $dataR['walletID']=$data['walletID'];
                        if (strpos($dataR['IPFS_URL'], 'https') !== 0) {
                            // Add 'https://' prefix if not already present
                            $dataR['pdfUrl'] = "https://ipfs.io/ipfs/" . substr($dataR['IPFS_URL'], 7);
                        } else {
                            // Use IPFS_URL as-is if it already starts with 'https'
                            $dataR['pdfUrl'] = $dataR['IPFS_URL'];
                        }
                        
                        //$dataR['pdfUrl']="https://ipfs.io/ipfs/".substr($dataR['IPFS_URL'], 7);
                        //https://mumbai.polygonscan.com/
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

                        /*print_r($data);
                        exit;*/
                        return view('bverify.index',compact('data'));
                    }else{
                        
                      /*  $response['status']=400;  
                        $response['message']="Details not found."; */
                        $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Please try after sometime."; 
                         return view('bverify.failed',compact('data'));
                           
                    }
                }else{
                    // $response['status']=400;
                    // $response['message']="Wallet address not found.";
                    $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Something went wrong."; 
                         return view('bverify.failed',compact('data'));
                }

            }else{
                // $response['status']=400;
                // $response['message']="Data not found.";
                $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Something went wrong."; 
                         return view('bverify.failed',compact('data'));
            }


            }else{
                // $response['status']=400;   
                // $response['message']="Key not found."; 
                $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently2.<br> Something went wrong."; 
                         return view('bverify.failed',compact('data'));
            }

        }else{
            // $response['status']=400;
            // $response['message']="Key not found.";
            $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Something went wrong."; 
                         return view('bverify.failed',compact('data'));
        }
        
       // $response['message']="Details not found.";
        return $response;
    }


    public function PreviewDetails(Request $request,$token){
        


        $valid=true;
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
         if($domain == 'verification.anu.edu.in') {
            $domain = 'anu.seqrdoc.com';
            $subdomain[0] = 'anu';
        }
        $site = Site::select('site_id')->where('site_url',$domain)->first();
        $site_id = $site['site_id'];
        // $token="76DFF20704DDD5B0BD2AD04AF30AB792";
        // echo  $key = encrypt($token);
        // exit;
        //echo $token;

        
        if(!empty($token)){

          
            try {
               // echo "s";
                 //$key= base64_decode($token);
                //FOR PHP
                  $key = decrypt($token);
            } catch (DecryptException $e) {

                $key="";
                if($this->isValid64base($token)){
                 $key = base64_decode($token);

                 //for PDF2PDF
                 $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();

                // if($subdomain[0]=="demo"){
                //     echo $key;
                //     echo "<br>";
                //     echo $site_id;
                //     echo "<br>";
                //     print_r($studentData);
                //     exit;
                //  }


                 if($studentData){
                     $key=$key;
                 }else{
                    $result = json_decode($key);
                    if ($result === FALSE) {
                        // JSON is invalid
                        $key=$key;
                    }else{
                        //FOR exceptional case
                        $key =$this->decodeCustomLogic($token);
                    }
                 }
                 
                }else{
                    //FOR exceptional case
                $key =$this->decodeCustomLogic($token);
                }
                
                // echo "BB";

                //print_r($e->getMessage());
            }
            //  echo $key;
            // exit;
            if(!empty($key)){
            
            
          
            

            if($subdomain[0]=="mitwpu"){

                        $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                 ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->whereNotNull('bc_txn_hash')
                                                ->orderBy('id','DESC')
                                                ->first();
                        // print_r($studentData);
                   
                }else{
                    $studentData = StudentTable::where('key',$key)
                                                ->where('publish',1)
                                                ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();
                }
            

            if($studentData){

                $siteData = DemoSite::select('bc_wallet_address')->where('site_url',$domain)->first();
                //print_r($siteData);
                if(isset($siteData['bc_wallet_address'])&&!empty($siteData['bc_wallet_address'])){


                    if($data['template_id']==2||$data['template_id']==698||$data['template_id']==718||$data['template_id']==719||$subdomain[0]=="mitwpu"||$subdomain[0]=="anu"||$subdomain[0]=="verification"||($subdomain[0]=="demo"&&$data['template_id']==137)||($subdomain[0]=="demo"&&$data['template_id']==144)||($subdomain[0]=="demo"&&$data['template_id']==145)){
                        $data['walletID']="0xB509AF6532Af95eE59286A8235f2A290c26b5730";
                    }else if($subdomain[0]=="demo"&&($data['template_id']==162||$studentData['template_id']==744)){
                         $data['walletID']="0x2A13685119CBfa6B780c191b59eA97d6BD572f14";
                     }else{   
                        $data['walletID']=$siteData['bc_wallet_address'];
                    }
                    if($subdomain[0]=="mitwpu" || $subdomain[0]=="anu"|| $subdomain[0]=="verification"){

                        $checkContract = TemplateMaster::select('bc_contract_address')->where('id',$studentData['template_id'])->first();


                        
                        
                        if((isset($checkContract['bc_contract_address']) && empty($checkContract['bc_contract_address'])) || !isset($checkContract['bc_contract_address']) ){
                            $bc_contract_address  ="0x9d187c7C331301FB8CD383be6d4358552B0E223e";
                          
                        }else{
                            $bc_contract_address =   $checkContract['bc_contract_address'];
                        }
                       
                        $data['contractAddress']=$bc_contract_address;
                        $contractData = DB::table('bc_smart_contracts')
                        ->select('wallet_address')
                        ->where('smart_contract_address','=',$bc_contract_address)
                        ->first();  
                        // dd($contractData);
                        if(!empty($contractData)){
                            $data['walletID']=$contractData->wallet_address;
                        }else{
                            $data['walletID']="";
                        }
                        
                        // if($key=="1CDE726256C2CAD7FF3003CA76DCA260"){
                        //      dd($data['walletID']);
                        // }

                    } else {

                        $data['walletID']=$siteData['bc_wallet_address'];
                    }

                    
                    $data['uniqueHash']=$key;
                    $data['template_id']=$studentData['template_id'];
                    
                    //  print_r($studentData);
                    //  exit;
                    // if(($subdomain[0]=="demo" && ($studentData['template_id']==2||$studentData['template_id']==698||$studentData['template_id']==718||$studentData['template_id']==719||$studentData['template_id']==137))||$subdomain[0]=="mitwpu"||$subdomain[0]=="anu"||$subdomain[0]=="mpkv"||$subdomain[0]=="ksu"||$subdomain[0]=="konkankrishi"){
                    // $mode=1;
                    // }else{
                    // $mode=0;
                    // }
                    // $mode = $studentData['bc_generation_type']; 
                    $data['blockchain_mode'] = $studentData['bc_generation_type'];
                    // print_r($data);
                    // exit;
                    // if($subdomain[0] == "anu"){
                    //     $smart_contracts = BcSmartContract::where('wallet_address','0xB509AF6532Af95eE59286A8235f2A290c26b5730')->first();
                    //     if(!empty($smart_contracts)){
                    //         $data['contractAddress'] = $smart_contracts->smart_contract_address;
                    //     }
                    // }
                    $response=CoreHelper::retreiveDetails($data);
                    
                    if($response['status']==200){

                        //$response['status']=200;
                        $dataR=$response['data'];
                        $dataR['walletID']=$data['walletID'];
                        $dataR['pdfUrl']=$dataR['IPFS_URL'];
                        //$dataR['pdfUrl']="https://ipfs.io/ipfs/".substr($dataR['IPFS_URL'], 7);
                        //https://mumbai.polygonscan.com/
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

                        /*print_r($data);
                        exit;*/
                        return view('bverify.preview_index',compact('data'));
                    }else{

                      /*  $response['status']=400;  
                        $response['message']="Details not found."; */
                        $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Please try after sometime."; 
                         return view('bverify.failed',compact('data'));
                           
                    }
                }else{
                    // $response['status']=400;
                    // $response['message']="Wallet address not found.";
                    $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Something went wrong."; 
                         return view('bverify.failed',compact('data'));
                }

            }else{
                // $response['status']=400;
                // $response['message']="Data not found.";
                $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Something went wrong."; 
                         return view('bverify.failed',compact('data'));
            }


            }else{
                // $response['status']=400;   
                // $response['message']="Key not found."; 
                $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Something went wrong."; 
                         return view('bverify.failed',compact('data'));
            }

        }else{
            // $response['status']=400;
            // $response['message']="Key not found.";
            $data['status']=400;  
                        $data['message']="Opps! We are not able to fetch details currently.<br> Something went wrong."; 
                         return view('bverify.failed',compact('data'));
        }
        
       // $response['message']="Details not found.";
        return $response;
    }
    


    public function verifyPdf(Request $request)
    {  
     
        $data = $request->post();
        $hostUrl = \Request::getHttpHost();
        $subdomain = explode('.', $hostUrl);
        if($domain == 'verification.anu.edu.in') {
            $domain = 'anu.seqrdoc.com';
            $subdomain[0] = 'anu';
        }
        $fileHashInstances = \Config::get('constant.fileHashInstances');
        if(isset($data['pdfData'])){
            
             $site = Site::select('site_id')->where('site_url',$hostUrl)->first();
          
            $site_id = $site['site_id'];
            // $studentData = StudentTable::where('pinata_ipfs_hash',$data['pdfData'])
                                                // ->where('publish',1)
                                                // ->where('status',1)
                                                // ->where('site_id',$site_id)
                                                // ->orderBy('id','DESC')
                                                // ->first();


            // if($subdomain[0] =='anu') {
            if(in_array($subdomain[0], $fileHashInstances)){
                $studentData = StudentTable::where('bc_file_hash',$data['pdfData'])
                                                ->where('publish',1)
                                                ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();
            } else {
                $studentData = StudentTable::where('pinata_ipfs_hash',$data['pdfData'])
                                                ->where('publish',1)
                                                ->where('status',1)
                                                ->where('site_id',$site_id)
                                                ->orderBy('id','DESC')
                                                ->first();
            }

         
            if($studentData){
                $key = $studentData['key'];
                $siteData = DemoSite::select('bc_wallet_address')->where('site_url',$hostUrl)->first();
                //print_r($siteData);
                if(isset($siteData['bc_wallet_address'])&&!empty($siteData['bc_wallet_address'])){
                    if($subdomain[0]=="mitwpu" || $subdomain[0]=="anu"|| $subdomain[0]=="verification"){

                        $checkContract = TemplateMaster::select('bc_contract_address')->where('id',$studentData['template_id'])->first();


                        
                        
                        if((isset($checkContract['bc_contract_address']) && empty($checkContract['bc_contract_address'])) || !isset($checkContract['bc_contract_address']) ){
                            $bc_contract_address  ="0x9d187c7C331301FB8CD383be6d4358552B0E223e";
                          
                        }else{
                            $bc_contract_address =   $checkContract['bc_contract_address'];
                        }
                       
                        $data['contractAddress']=$bc_contract_address;
                        $contractData = DB::table('bc_smart_contracts')
                        ->select('wallet_address')
                        ->where('smart_contract_address','=',$bc_contract_address)
                        ->first();  
                        // dd($contractData);
                        if(!empty($contractData)){
                            $data['walletID']=$contractData->wallet_address;
                        }else{
                            $data['walletID']="";
                        }
                        
                        // if($key=="1CDE726256C2CAD7FF3003CA76DCA260"){
                        //      dd($data['walletID']);
                        // }

                    } else {

                        $data['walletID']=$siteData['bc_wallet_address'];
                    }
                        
                    // $data['walletID']=$siteData['bc_wallet_address'];
                    $data['uniqueHash']=$key;
                    $data['template_id']=$studentData['template_id'];
                    
                     // print_r($studentData);
                     // exit;
                    if(($subdomain[0]=="demo" && ($studentData['template_id']==2||$studentData['template_id']==698||$studentData['template_id']==718||$studentData['template_id']==719||$studentData['template_id']==744||$studentData['template_id']==137||$studentData['template_id']==144||$studentData['template_id']==145||$studentData['template_id']==162))||$subdomain[0]=="mitwpu"||$subdomain[0]=="anu"||$subdomain[0]=="verification"||$subdomain[0]=="mpkv"||$subdomain[0]=="ksu"||$subdomain[0]=="konkankrishi"){
                    $mode=1;
                    }else{
                    $mode=0;
                    }
                    // print_r($data);
                    // exit;
                    // if($subdomain[0] == "anu"){
                    //     $smart_contracts = BcSmartContract::where('wallet_address','0xB509AF6532Af95eE59286A8235f2A290c26b5730')->first();
                    //     if(!empty($smart_contracts)){
                    //         $data['contractAddress'] = $smart_contracts->smart_contract_address;
                    //     }
                    // }
                    // if($subdomain[0] == 'anu') {
                    if(in_array($subdomain[0], $fileHashInstances)){


                        $tokenData = DB::table('blockchain_other_data')
                            ->select('student_table_id','token_id')
                            ->where('student_table_id','=',$studentData['id'])
                            ->whereNotNull('token_id')
                            ->first();  
                        if($tokenData) {
                            //$token_id = $tokenData->token_id;

                            $directoryUrlForward="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
                            $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";
                            $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";
                            // $cmd =  escapeshellarg($pyscript) ." 2>&1";
                            $txnHash = $studentData['bc_txn_hash'];
                            $cmd =  "$pyscript $txnHash 2>&1";
                            // $cmd = "$pyscript $template_id $fullpath $directoryUrlForward $servername $db_username $password $dbName $instance_name 2>&1"; // 
                            // exec($cmd, $output, $return);
                            exec('C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe '.$cmd, $output, $return);
                            // exec('Python '.$cmd, $output, $return);
                            // exec('D:/pdf2pdf_env/Scripts/python.exe '.$cmd, $output, $return);
                            $jsonString = str_replace("'", '"', $output[1]);
                            $data = json_decode($jsonString, true);

                            // Step 3: Access the token_id
                            $token_id = $data['token_id'] ?? null;

                            if($token_id) {
                                // DB::table('blockchain_other_data')->insert([
                                //     'student_table_id' => $studentData['id'],
                                //     'token_id' => $token_id,
                                // ]);
                                DB::table('blockchain_other_data')->updateOrInsert(
                                    ['student_table_id' => $studentData['id']], // Condition
                                    ['token_id' => $token_id]                   // Data to insert or update
                                );
                            }
                            

                        } else {

                            $directoryUrlForward="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
                            $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";
                            $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";
                            // $cmd =  escapeshellarg($pyscript) ." 2>&1";
                            $txnHash = $studentData['bc_txn_hash'];
                            $cmd =  "$pyscript $txnHash 2>&1";
                            // $cmd = "$pyscript $template_id $fullpath $directoryUrlForward $servername $db_username $password $dbName $instance_name 2>&1"; // 
                            exec('C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe '.$cmd, $output, $return);
                            // exec($cmd, $output, $return);

                            // exec('Python '.$cmd, $output, $return);
                            // exec('D:/pdf2pdf_env/Scripts/python.exe '.$cmd, $output, $return);
                            $jsonString = str_replace("'", '"', $output[1]);
                            $data = json_decode($jsonString, true);

                            // Step 3: Access the token_id
                            $token_id = $data['token_id'] ?? null;

                            if($token_id) {
                                // DB::table('blockchain_other_data')->insert([
                                //     'student_table_id' => $studentData['id'],
                                //     'token_id' => $token_id,
                                // ]);
                                DB::table('blockchain_other_data')->updateOrInsert(
                                    ['student_table_id' => $studentData['id']], // Condition
                                    ['token_id' => $token_id]                   // Data to insert or update
                                );
                            }

                        }
                    
                        $contractData = DB::table('bc_smart_contracts')
                                ->select('wallet_address','smart_contract_address')
                                ->where('id','=',$studentData['bc_sc_id'])
                                ->first();  

                        // dd($contractData);
                        if(!empty($contractData)){
                            $walletID = $contractData->wallet_address;
                            $contractAddress = $contractData->smart_contract_address;
                        } else {
                            if($studentData['template_type'] == 0 ) {

                                $checkContact = TemplateMaster::select('bc_contract_address')->where('id',$studentData['template_id'])->first();

                                $contractAddress = $checkContact['bc_contract_address'];


                            } elseif ($studentData['template_type'] == 1) {
                                $checkContract = DB::table("uploaded_pdfs")
                                   ->select('bc_contract_address')
                                   ->where('id',$studentData['template_id'])
                                   ->get();
                                   
                                if($checkContract&&!empty($checkContract[0]->bc_contract_address)){  
                                    $contractAddress = $checkContract[0]->bc_contract_address;
                                } else {
                                    $contractAddress = "";
                                }
                            } elseif ($studentData['template_type'] == 2) {
                                // $checkContract = DB::table("uploaded_pdfs")
                                //    ->select('bc_contract_address')
                                //    ->where('id',$studentData['template_id'])
                                //    ->get();
                                   
                                // if($checkContract&&!empty($checkContract[0]->bc_contract_address)){  
                                //     $contractAddress = $checkContract[0]->bc_contract_address;
                                // } else {
                                //     $contractAddress = "";
                                // }
                                $checkContact = TemplateMaster::select('bc_contract_address')->where('id',$studentData['template_id'])->first();

                                $contractAddress = $checkContact['bc_contract_address'];

                            } 
                            //0:Template Maker, 2:Custom Template, 1: PDF2PDF

                        }
                        
                        $response = $this->fetchNFTWithCurl($contractAddress,$token_id);



                        $decodedResponse = json_decode($response, true);

                        // print_r($decodedResponse);
                        if (!isset($decodedResponse['errors']) || !is_array($decodedResponse['errors'])) {

                            //$response['status']=200;
                            $nftData = $decodedResponse['nft'] ?? null;


                            $url = $nftData['metadata_url']; // Replace with your JSON URL


                            if(empty($url )) {
                                $data['status']=400;  
                                $data['message']="Opps! We are not able to fetch details currently.<br> Please try after sometime."; 
                                //return view('bverify_new.failed',compact('data'));
                                    
                            }


                            // Remove the 'https://ipfs.io/ipfs/' part
                            // $trimmed = str_replace("https://ipfs.io/ipfs/", "", $url);

                            // // Split into CID and the rest
                            // $parts = explode("/", $trimmed, 2);

                            // // Build new URL
                            // $url = "https://" . $parts[0] . ".ipfs.w3s.link/" . $parts[1];
                             // Uddated on 21-07-2025 by Rohit
                            $url = $this->convertToW3sLink($url);

                            // Download and save the JSON file

                            $metadata_url = file_get_contents($url);
                            
                            $metadata_data = json_decode($metadata_url, true);

                            // $pdfUrl = $metadata_data['image'] ?? null;
                            // $pdfUrl = $metadata_data['image_url'] ?? null;
                            // $pdfUrl = $metadata_data['image'] ?? null;

                            $pdfUrl = $metadata_data['image'] ?? null;
                            if($subdomain[0] == 'mitwpu') {
                                $pdfUrl = $nftData['image_url'] ?? null;
                            }   

                            

                            $dataR['name'] = $nftData['name'] ? $nftData['name'] : '';
                            $dataR['description'] = $nftData['description'] ? $nftData['description'] : '';

                            $traits = $decodedResponse['nft']['traits'] ?? [];

                            // Loop through the traits and remove the one with trait_type = 'UniqueHash'
                            foreach ($traits as $key => $trait) {
                                if (isset($trait['trait_type']) && $trait['trait_type'] === 'UniqueHash') {
                                    unset($traits[$key]);
                                    break; // Stop after removing it
                                }
                            }
                            // Update the original array if needed
                            $decodedResponse['nft']['traits'] = array_values($traits); // Reindex the array
                            // $dataR['metadata'] = array_reverse($decodedResponse['nft']['traits']);
                            $dataR['metadata'] = $decodedResponse['nft']['traits'];


                            
                            $dataR['contractAddress']=$contractAddress;  
                            $owners = $decodedResponse['nft']['owners'] ?? [];

                            if (!empty($owners) && isset($owners[0]['address'])) {
                                $dataR['walletID']=$owners[0]['address'];
                            } else{ 
                                $dataR['walletID'] = '-';
                            }

                            $dataR['IPFS_URL'] =  $pdfUrl;
                            if($subdomain[0] == 'demo'){
                                // Build new URL
                                $dataR['pdfUrl'] = $nftData['image_url'] ? $nftData['image_url'] : '';
                            }else{
                                // if (strpos($dataR['IPFS_URL'], 'https') !== 0) {
                                //     // Add 'https://' prefix if not already present
                                //     $dataR['pdfUrl'] = "https://ipfs.io/ipfs/" . substr($dataR['IPFS_URL'], 7);
                                //     $url = $dataR['pdfUrl']; // Replace with your JSON URL

                                //     // Remove the 'https://ipfs.io/ipfs/' part
                                //     $trimmed = str_replace("https://ipfs.io/ipfs/", "", $url);

                                //     // Split into CID and the rest
                                //     $parts = explode("/", $trimmed, 2);

                                //     $url = $this->convertToW3sLink($url);
                                //     // Build new URL
                                //     // $dataR['pdfUrl'] = "https://" . $parts[0] . ".ipfs.w3s.link/" . $parts[1];
                                //     $dataR['pdfUrl'] = $url;
                                // } else {
                                //     // Use IPFS_URL as-is if it already starts with 'https'
                                //     $dataR['pdfUrl'] = $dataR['IPFS_URL'];

                                //      // Remove the 'https://ipfs.io/ipfs/' part
                                //     // $trimmed = str_replace("https://ipfs.io/ipfs/", "", $url);

                                //     // // Split into CID and the rest
                                //     // $parts = explode("/", $trimmed, 2);
                                //     $url = $this->convertToW3sLink($url);
                                //     // Build new URL
                                //     $dataR['pdfUrl'] = url;
                                //     $dataR['pdfUrl'] = "https://" . $parts[0] . ".ipfs.w3s.link/" . $parts[1];
                                // }

                                // $dataR['pdfUrl'] = $nftData['image_url'] ? $nftData['image_url'] : '';
                                $dataR['pdfUrl'] = $pdfUrl;
                                


                            }

                            // Local path of pdf
                            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                            $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
                            $pdf_url=$path.$subdomain[0]."/backend/pdf_file/".$studentData['certificate_filename'];

                            $dataR['dirPdfUrl'] = $pdf_url;

                            // if($subdomain[0] =='anu') {
                            //     $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                            //     $domain = \Request::getHost();
                            //     $path = $protocol.'://'.$domain.'/';

                            //     // echo $domain;
                            //     // die();
                            //     $pdf_url="https://verify.anu.edu.in/verify/seqrdoc/pdf_file/".$studentData['certificate_filename'];

                            //     $dataR['pdfUrl'] = $pdf_url;
                            //     $dataR['dirPdfUrl'] = $pdf_url;

                            // }


                            // if($subdomain[0]=="demo"){
                                $dataR['polygonTxnUrl']="https://polygonscan.com/tx/".$studentData['bc_txn_hash'];
                            // }else{
                            //     $dataR['polygonTxnUrl']="https://mumbai.polygonscan.com/tx/".$studentData['bc_txn_hash'];    
                            // }


                            $dataR['txnHash']=$studentData['bc_txn_hash'];
                            
                            
                            $data=$dataR;
                            $data['status']=200;  
                            $data['success']=true; 
                            $data['message']="success";
                            //return view('bverify_new.index',compact('data'));
                        }else{
                            $data['status']=400; 
                            $data['success']=false;   
                            $data['message']="Opps! We are not able to fetch details currently.<br> Please try after sometime."; 
                            //return view('bverify_new.failed',compact('data'));
                                
                        }


                    } else {
                        $response=CoreHelper::retreiveDetails($data);
                       

                        //print_r($response);
                        // exit;
                        if($response['status']==200){

                            $dataR=$response['data'];
                            $dataR['walletID']=$data['walletID'];
                            if($subdomain[0] =='anu') {
                                $dataR['pdfUrl']='https://verification.anu.edu.in/anu/backend/pdf_file/'.$studentData['certificate_filename'];
                            } else {
                                $dataR['pdfUrl']=$dataR['IPFS_URL'];
                            }
                            //$dataR['pdfUrl']=$dataR['IPFS_URL'];
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


    public function fetchNFTWithCurl($contract, $tokenId)
    {
        $requestMethod='GET';
        $requestParameters['contract']=$contract;
        $requestParameters['tokenId']=$tokenId;

        $requestUrl = "https://api.opensea.io/api/v2/chain/matic/contract/".$contract."/nfts/".$tokenId;     
        $curl = curl_init();
        
        curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.opensea.io/api/v2/chain/matic/contract/".$contract."/nfts/".$tokenId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "x-api-key: eb9d18bbe6bf48648ed2c12300f2f4c2"
        ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);     
        $decodedResponse = json_decode($response, true);
        
        if ($err) {
            $status = 'failed';

            return  "cURL Error #:" . $err;
        } else {
            return  $response;
        }

    }

    function convertToW3sLink($url) {
        // If URL starts with ipfs://
        if (strpos($url, 'ipfs://') === 0) {
            // Remove ipfs:// prefix
            // $url = substr($url, 7);
            $hash = substr($url, 7);
            return "https://ipfs.io/ipfs/{$hash}";
        }

        // If URL is from ipfs.io gateway
        if (preg_match("#https://ipfs\.io/ipfs/([^/]+)(/.*)?#", $url, $matches)) {
            $hash = $matches[1];
            $path = isset($matches[2]) ? ltrim($matches[2], '/') : '';
            return "https://{$hash}.ipfs.w3s.link/{$path}";
        }

        // If URL is Pinata opensea-private
        if (preg_match("#https://opensea-private\.mypinata\.cloud/ipfs/([^/]+)/(.*)#", $url, $matches)) {
            $hash = $matches[1];
            $path = $matches[2];
            return "https://{$hash}.ipfs.w3s.link/{$path}";
        }

        // If plain hash without protocol
        if (!preg_match("#^https?://#", $url) && preg_match("#^([^/]+)(/.*)?#", $url, $matches)) {
            $hash = $matches[1];
            $path = isset($matches[2]) ? ltrim($matches[2], '/') : '';
            return "https://{$hash}.ipfs.w3s.link/{$path}";
        }

        // If already formatted or none matched, return original
        return $url;
    }



    public function decodeCustomLogic($token){


$key="";

             $APIKey="57r3edp5MkLzIbCsA4iPJW8I3yeojqaPK+pd3gRPiZ4=";

              $fromKey = base64_decode($APIKey);

//exit;
//$toKey = base64_decode("to_key_as_a_base_64_encoded_string");
$cipher = "AES-256-CBC"; //or AES-128-CBC if you prefer

//Create two encrypters using different keys for each
$encrypterFrom = new Encrypter($fromKey, $cipher);
//$encrypterTo = new Encrypter($toKey, $cipher);

//Decrypt a string that was encrypted using the "from" key
try{
    $decryptedFromString = $encrypterFrom->decryptString($token);
}catch(DecryptException $e){
    $decryptedFromString ='';
}
$string = $decryptedFromString;

 $string = str_replace(';','',$string);

 //echo $string = "s:32:\"1EA6F8CBE03B40E5EE406234CD0FF091\"";

$pattern = '/^s:(?<length>\d+):\"(?<data>.*?)\"$/';

if (preg_match($pattern, $string, $matches)) {
    $dataLength = intval($matches['length']);
    $data = $matches['data'];

    if (strlen($data) === $dataLength) {
        // echo "The string is in the format s:<length>:\"<data>\".\n";
        // echo "Length: $dataLength\n";
        // echo "Data: $data\n";

        $key = $data;
    } else {
      //  echo "The string's length doesn't match the specified length.\n";
    }
} else {
   // echo "Invalid string format.\n";
}
return $key;
    }

     public function testPdf(Request $request,$token){


 try {
               // echo "s";
                 //$key= base64_decode($token);
                  $key = decrypt($token);
            } catch (DecryptException $e) {

                $key="";
                if($this->isValid64base($token)){
                //    echo "1";
                 $key = base64_decode($token);

                $result = json_decode($key);
                if ($result === FALSE) {
                    // JSON is invalid
                    $key=$key;
                }else{
                    $key =$this->decodeCustomLogic($token);
                }
                //  if(is_string($key)){
                //     echo "3";
                //  }
                }else{
                $key =$this->decodeCustomLogic($token);
                }
                
                // echo "BB";

                //print_r($e->getMessage());
            }
echo $key;

//echo $this->decodeCustomLogic($token);
exit;
          // try {
          //      // echo "s";
          //        //$key= base64_decode($token);
          //         $key = decrypt($token);
          //   } catch (DecryptException $e) {

          //       $key="";
          //       if($this->isValid64base($token)){
          //        $key = base64_decode($token);
          //       }
                
          //       // echo "BB";

          //       //print_r($e->getMessage());
          //   }
        // echo $token;

        // exit;

            // $key="";
            // if($this->isValid64base($token)){
            //  $key = base64_decode($token);
            // }
                
                $APIKey="base64:57r3edp5MkLzIbCsA4iPJW8I3yeojqaPK+pd3gRPiZ4=";

                $str = 'eyJpdiI6IndwZURPbjlMWUc1TnFXN2xYakpiTGc9PSIsInZhbHVlIjoidjg2TDgwQmNQd1p0a3lwcndDQ3ZCY0xiS0VTYmlQVVVUV0REV3AxTVE1VTRXNW1ZYzdpREVSQ3JONXNDUWRRRyIsIm1hYyI6ImE5MzMyODU1M2JmZjI5NWJlNGUxYjg1MTgzNTMwMTQxMjYwM2Q5Y2NlZmI0MjJkMmM0NTBjOGNjNzcyMmNiMGYifQ='; 


// // see if the key starts with 'base64:'
// if (Str::startsWith($key = $APIKey, 'base64:')) {
//     // decode the key
//     $key = base64_decode(substr($key, 7));
// }

// echo Encrypter($key, $config['cipher']);

// APP_KEY=base64:dlIYiNJqqtamt/d/iN0s/AJ7iUX6Sv+ocY+8BXN4/iI=
// #APP_KEY=base64:57r3edp5MkLzIbCsA4iPJW8I3yeojqaPK+pd3gRPiZ4=


//Keys and cipher used by encrypter(s)

 $fromKey = base64_decode("57r3edp5MkLzIbCsA4iPJW8I3yeojqaPK+pd3gRPiZ4=");

//exit;
//$toKey = base64_decode("to_key_as_a_base_64_encoded_string");
$cipher = "AES-256-CBC"; //or AES-128-CBC if you prefer

//Create two encrypters using different keys for each
$encrypterFrom = new Encrypter($fromKey, $cipher);
//$encrypterTo = new Encrypter($toKey, $cipher);

//Decrypt a string that was encrypted using the "from" key
 $decryptedFromString = $encrypterFrom->decryptString($str);

$string = $decryptedFromString;

 $string = str_replace(';','',$string);

 //echo $string = "s:32:\"1EA6F8CBE03B40E5EE406234CD0FF091\"";
echo "<br>";
$pattern = '/^s:(?<length>\d+):\"(?<data>.*?)\"$/';

if (preg_match($pattern, $string, $matches)) {
    $dataLength = intval($matches['length']);
    $data = $matches['data'];

    if (strlen($data) === $dataLength) {
        // echo "The string is in the format s:<length>:\"<data>\".\n";
        // echo "Length: $dataLength\n";
        // echo "Data: $data\n";

      echo  $key = $data;
    } else {
        echo "The string's length doesn't match the specified length.\n";
    }
} else {
    echo "Invalid string format.\n";
}



// exit;

//              echo      $key = decrypt($token);
//                    exit;
//              // $key;
//             echo encrypt("A29C4F7EF50129C431DA73320E944B95");
//           //   echo $token;
            
//             exit;
//             echo base64_encode($token);

//             exit;
//             echo $key = decrypt(base64_encode($token));
//             exit;

     }

}

