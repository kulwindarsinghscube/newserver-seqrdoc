<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Config;  
use App\Utility\BlockChain;
use App\Mail\SendSummaryBlockChain;
use DB;

class VerifyBlockChainDocument extends Command
{
    protected $signature = 'blockchain:verify-documents';
    protected $description = 'Send email notifications regarding blockchain document';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('Started verify blockchain document Command');
        \DB::disconnect('mysql'); 
        \Config::set("database.connections.mysql", [
            'driver'   => 'mysql',
            'host'     => \Config::get('constant.DB_HOST'),
            "port" => \Config::get('constant.DB_PORT'),
            'database' =>'seqr_demo',
            'username' => \Config::get('constant.DB_UN'),
            'password' => \Config::get('constant.DB_PW'),
            "unix_socket" => "",
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => true,
            "engine" => null,
            "options" => []
        ]);
        \DB::reconnect();

        $blockchain_documents = \DB::table('blockchain_documents_for_demo')->get();
        $final_data = [];
        $is_failed = 0;
        foreach($blockchain_documents as $document){
            $mode = 1;
                $data = array(
                    'walletID'=>$document->wallet_id,
                    'uniqueHash'=>$document->unique_hash  
                );
            // if (!in_array($document->instance_name, ['MITWPU', 'DEMO'])) {
                $data['contractAddress'] = $document->contract_address;
               
            // }  
            
            $temp_data['instance'] = $document->instance_name;
            $temp_data['document_id'] = $document->document_id;
            $temp_data['url'] = $document->url;

            try{ 
                // $instance_name = $document->instance_name.'.seqrdoc.com';
                $instance_name = $document->instance_name;
                
                $siteData = DB::connection('mysql_old')->table("seqr_demo.sites")
                            ->select('new_server','site_url')
                            ->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'),$instance_name)
                            ->first();

                $instance=explode('.', $siteData->site_url);
                if($instance[0]!='demo'&&$instance[0] != 'master'){
                    $dbName = 'seqr_d_'.$instance[0];
                }else{
                    $dbName = 'seqr_demo';
                }

                $dbCred = '';
                if($siteData->new_server == 0) {
                    $dbCred = 'mysql_old';
                } else {
                    $dbCred = 'mysql_new';
                }
                
                $studentData = DB::connection($dbCred)->table($dbName.".student_table")
                               ->where('serial_no', $document->document_id)
                                ->where('publish', 1)
                                ->where('status', 1)
                                ->orderBy('id', 'DESC')
                                ->first();

                $bc_txn_hash = $studentData->bc_txn_hash; 
                $response= $this->retreiveDetailsPython($bc_txn_hash,$document->contract_address);
                $decodedResponse = json_decode($response, true);
                if (!isset($decodedResponse['errors']) || !is_array($decodedResponse['errors'])) {

                    $nftData = $decodedResponse['nft'] ?? null;
                    $url = $nftData['metadata_url']; // Replace with your JSON URL

                    $temp_data['status'] = "success";
                    $temp_data['message'] =  'Details fetched succesfully.';

                    if(empty($url)) {
                        $temp_data['status'] = "failed";
                        $temp_data['message'] =  'Invalid Document';
                    }
                } else {
                    $temp_data['status'] = "failed";
                    $temp_data['message'] =  'Invalid Document';
                }
                
            }catch(\Exception $e){
                $temp_data['status'] = "failed";
                $temp_data['message'] =  $response['message'];
                \Log::error("$e"); 
            }
            if($temp_data['status'] == "failed"){
               
                $is_failed++;
            }
            $final_data[] = $temp_data;
        }
        if(!empty( $final_data)){ 
            $email_id = 'software@scube.net.in';  
            Mail::to($email_id )->send(new SendSummaryBlockChain($final_data,$is_failed));
            // 'software@scube.net.in'
            Log::info('Something Wrong in Blockchain email Sent.');
        }else{
            Log::error('All Blockchain working fine.');

        }
      
         

        Log::info('Ended verify blockchain document Command');
        // $this->info('Emails sent successfully!');
    }

    
    public function retreiveDetailsPython($bc_txn_hash,$contractAddress){

        $directoryUrlForward="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
        $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";
        $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";
        // $cmd =  escapeshellarg($pyscript) ." 2>&1";
        $txnHash = $bc_txn_hash;
        $cmd =  "$pyscript $txnHash 2>&1";
        // $cmd = "$pyscript $template_id $fullpath $directoryUrlForward $servername $db_username $password $dbName $instance_name 2>&1"; // 
        // exec($cmd, $output, $return);
        exec('C:/Users/Administrator/AppData/Local/Programs/Python/Python38/python.exe '.$cmd, $output, $return);

        // exec('Python '.$cmd, $output, $return);
        // exec('D:/pdf2pdf_env/Scripts/python.exe '.$cmd, $output, $return);
        $jsonString = str_replace("'", '"', $output[1]);
        $data = json_decode($jsonString, true);
        // print_r($data);
        // die();
        // Step 3: Access the token_id
        $token_id = $data['token_id'] ?? null;

        
       
        $response = $this->fetchNFTWithCurl($contractAddress,$token_id);

        return $response;
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

}
