<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiTracker;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use App\Exports\ApiTrakerExport;
use Mail;
use Session;
// use Excel;

use App\models\StudentTable;
//use Illuminate\Support\Facades\Mail;
use App\Exports\TemplateMasterExport;
use App\Jobs\SendMailJob;
use DB;
use App\Utility\BlockChainV1;
use App\Helpers\CoreHelper;
use Illuminate\Support\Facades\Storage;

class BlockchainRNDController extends Controller
{


    public function pinataToLighthouseV1_old()
    {
        ini_set('max_execution_time', 600);
        ini_set('max_input_time', 300);
        ini_set('memory_limit', '4096M');

        $domain = request()->getHost();
        $subdomain = explode('.', $domain);
        $pdfBasePath = public_path("{$subdomain[0]}/backend/pdf_file");

        // -----------------------------------------------
        // 1. Fetch student rows
        // -----------------------------------------------
        $students = DB::table('student_table')
            ->leftJoin('blockchain_other_data', 'student_table.id', '=', 'blockchain_other_data.student_table_id')
            ->join('bc_smart_contracts as bsc', 'student_table.bc_sc_id', '=', 'bsc.id')
            ->where('student_table.status', 1)
            ->whereNotNull('student_table.bc_txn_hash')
            ->whereBetween(DB::raw('DATE(student_table.created_at)'), ['2025-07-01', '2025-11-13'])
            ->orderByDesc('student_table.id')
            ->get();

        foreach ($students as $row) {

            echo "<pre>Student Serial: {$row->serial_no}<br>";

            $contractAddress = $row->smart_contract_address;
            $tokenId         = $row->token_id ?: $this->fetchTokenSingle($row->bc_txn_hash, $row->key);

            // Skip if still not available
            if (!$tokenId) {
                echo "Token ID Missing<br></pre>";
                continue;
            }
            

            echo "Contract: $contractAddress<br>Token ID: $tokenId<br>";

            // -----------------------------------------------
            // 2. Fetch NFT metadata from blockchain
            // -----------------------------------------------
            $nftResponse = json_decode($this->fetchNFTWithCurl($contractAddress, $tokenId), true);

            if (!isset($nftResponse['nft'])) {
                echo "NFT NOT FOUND or Invalid Response<br></pre>";
                continue;
            }

            $nft = $nftResponse['nft'];

            // Extract metadata CID
            $metadataUrl = $nft['metadata_url'] ?? null;
            if (!$metadataUrl) {
                echo "Metadata URL Missing<br></pre>";
                continue;
            }

            $cid = $this->getCidFromIpfsUri($metadataUrl);
            echo "CID: $cid<br>";

            // -----------------------------------------------
            // 3. Check if CID exists on Pinata
            // -----------------------------------------------
            if (!$this->checkPinataCidExists($cid)) {
                echo "CID NOT FOUND on Pinata<br></pre>";
                continue;
            }

            echo "CID FOUND on Pinata<br>";

            // -----------------------------------------------
            // 4. Prepare Mint Data
            // -----------------------------------------------
            $mintData = $this->prepareMintData($row, $nft, $cid, $tokenId, $pdfBasePath);

            // -----------------------------------------------
            // 5. Call Lighthouse Mint API
            // -----------------------------------------------
            $responseBC = CoreHelper::migrationMintPDF($mintData);

            if (!isset($responseBC['status']) || $responseBC['status'] != 200) {
                echo "Lighthouse Mint Failed<br></pre>";
                continue;
            }

            // -----------------------------------------------
            // 6. Update Database
            // -----------------------------------------------
            $this->updateBlockchainData($row, $responseBC);

            echo "Updated Student ID: {$row->id}<br>";
            print_r($responseBC);
            echo "</pre><br>";
        }
    }

    private function prepareMintData($row, $nft, $cid, $tokenId, $pdfBasePath)
    {
        $filePath = $pdfBasePath . '/' . $row->certificate_filename;

        // Get API tracker details
        $apiTrack = DB::table('bc_api_tracker')
            ->where('api_name', 'mintDataV1')
            ->where('response', 'like', "%{$row->bc_txn_hash}%")
            ->where('status', 'success')
            ->first();

        $mintData = [
            "walletID"            => $row->wallet_address,
            "bc_contract_address" => $row->smart_contract_address,
            "uniqueHash"          => $row->key,
            "pdf_file"            => $filePath,
            "template_id"         => $row->template_id,
            "tokenId"             => $tokenId,
        ];

        // Case 1: tracker exists → use old metadata
        if (!empty($apiTrack)) {
            $req = json_decode($apiTrack->request_parameters, true);
            $mintData["documentType"] = $req["documentType"] ?? "";
            $mintData["description"]  = $req["description"] ?? "";

            for ($i = 1; $i <= 10; $i++) {
                $key = "metadata$i";
                if (!empty($req[$key])) {
                    $data = is_string($req[$key]) ? json_decode($req[$key], true) : $req[$key];
                    if (!empty($data['label']) || !empty($data['value'])) {
                        $mintData[$key] = $data;
                    }
                }
            }

            return $mintData;
        }

        // Case 2: No tracker → extract from NFT traits
        $mintData["documentType"] = $nft['name'] ?? "";
        $mintData["description"]  = $nft['description'] ?? "";

        $traits = $nft['traits'] ?? [];
        $i = 1;
        foreach ($traits as $trait) {
            if ($i > 4) break;
            $mintData["metadata$i"] = [
                "label" => $trait["trait_type"] ?? "",
                "value" => $trait["value"] ?? ""
            ];
            $i++;
        }

        return $mintData;
    }


    private function updateBlockchainData($row, $responseBC)
    {
        StudentTable::where('id', $row->id)->update([
            'bc_txn_hash'      => $responseBC['txnHash'],
            'bc_ipfs_hash'     => $responseBC['ipfsHash'] ?? null,
            'pinata_ipfs_hash' => $responseBC['pinataIpfsHash'] ?? null,
            'bc_sc_id'         => $row->bc_sc_id,
            'updated_at'       => now()
        ]);

        DB::table('blockchain_other_data')
            ->where('student_table_id', $row->id)
            ->update([
                'token_id'         => $responseBC['token_id'] ?? null,
                'bc_md_ipfs_hash'  => $responseBC['metadata_ipfs_hash'] ?? null,
            ]);
    }



    public function pinataToLighthouseV1(){
        
        

        ini_set('max_execution_time', '600');
        ini_set('max_input_time', '300');
        ini_set('memory_limit', '4096M');


        // $row =DB::table('student_table as st,')
        //     ->join('blockchain_other_data as bod', 'st.id', '=', 'bod.student_table_id')
        //     ->join('bc_smart_contracts as bsc', 'st.bc_sc_id', '=', 'bsc.id')
        //     ->where('st.status', '1')
        //     ->whereNotNull('bsc.smart_contract_address')
        //     ->whereNotNull('bod.token_id')
        //     ->where('bod.token_id',$api_response->tokenID)
        //     ->where('st.bc_txn_hash', $api_response->txnHash)
        //     ->select('bod.id as bod_id','bod.token_id', 'bsc.smart_contract_address','st.template_id','st.certificate_filename','bsc.wallet_address','st.key','st.serial_no','st.id as student_table_id')
        //     // ->orderBy('st.id');
        //     ->first();

        // dd($students);

        $students = DB::table('student_table')
        ->leftJoin('blockchain_other_data', 'student_table.id', '=', 'blockchain_other_data.student_table_id')
        ->join('bc_smart_contracts as bsc', 'student_table.bc_sc_id', '=', 'bsc.id')
        ->where('status', 1)
        ->whereNotNull('bc_txn_hash')
        ->whereDate('student_table.created_at', '=', '2025-11-07')
        // ->whereDate('student_table.created_at', '>=', '2025-09-13')
        // ->whereDate('student_table.created_at', '<=', '2025-10-07') // split date
        // ->whereDate('student_table.created_at', '<=', '2025-11-13') // MITWPU Last date
        // ->whereDate('student_table.created_at', '=', '2025-08-01')
        // ->whereDate('student_table.created_at', '<=', '2025-10-15')
        // ->where('student_table.serial_no', '=', 'TESTA4U19042')
        ->orderBy('student_table.id', 'desc')
        ->limit(30)
        ->get();


		// ->chunk(10, function ($students) {	        
	        foreach($students as $row){

                $domain = \Request::getHost();
                $subdomain = explode('.', $domain);

	            echo "<pre>";
	            echo "Student Table Row =>";
	            // echo "<br>";
	            echo $row->serial_no;
	            echo "<br>";
	            // print_r($row);
	            // echo "<br><br>";
	            // die(); 

	            $contractAddress = $row->smart_contract_address;
	            $token_id = $row->token_id;
	            $txnHash = $row->bc_txn_hash;
	            $barcode = $row->key;
	            if($token_id==null || $token_id=='' || empty($token_id) ){
	                $token_id = $this->fetchTokenSingle($txnHash,$barcode);
	            }
	            // $pinata_result=BlockChainV1::pinataRetreiveDetails($row);
	            
	            // $contractAddress ='0x0685eFf7B1D2217F466831d7c78Db3edC0d464F1';
	            // $token_id='462';

	            $response = $this->fetchNFTWithCurl($contractAddress,$token_id);

	            $decodedResponse = json_decode($response, true);

	            echo "Blockchain Response =>";
	            echo "<br>";
	            echo "Contract => ".$contractAddress;
	            echo "<br>";
	            echo "Token ID => ".$token_id;
	            echo "<br>";

	            if (!isset($decodedResponse['errors']) || !is_array($decodedResponse['errors'])) {

	                // print_r($pinata_result);
	                // echo "<br><br>";

	                $nftData = $decodedResponse['nft'] ?? null;
	                $url = $nftData['metadata_url'];

	                if($url) {
	                    $metadataHash =$this->getCidFromIpfsUri($url);
	                } else{
	                    echo "Metadata URL not found";
	                    continue;
	                }
	                echo $metadataHash;
	                echo "<br>";
	                $walletId = $row->wallet_address;
	                $certificateFilename = $row->certificate_filename;
	                $serialNo = $row->serial_no;
	                
	                $currentDateTime = date("Y-m-d H:i:s");
	                $bc_sc_id = $row->bc_sc_id;
	                // $filePath = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certificateFilename;

	                $filePath = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certificateFilename;

                    $s3Disk = Storage::disk('s3');
                    $s3Key  = 'public/' . $subdomain[0] . '/backend/pdf_file/' . $certificateFilename;


                    $s3Flag = '0';
                    if (!file_exists($filePath)) {

                        if ($s3Disk->exists($s3Key)) {

                            // Download file from S3
                            $fileContent = $s3Disk->get($s3Key);

                            // Save locally
                            file_put_contents($filePath, $fileContent);
                            $s3Flag = 1;

                        } else {
                            throw new \Exception('PDF not found in S3: ' . $s3Key);
                        }
                    }

	                $templateId = $row->template_id;
	                if ($this->checkPinataCidExists($metadataHash)) {
	                    echo "CID FOUND on Pinata Gateway\n";
	                    // Remint Data on Lighthouse
	                    // $decodedResponse['nft']['traits'] = array_values($traits); // Reindex the array
	                    
	                    $api_track_result = DB::table('bc_api_tracker')
	                    ->where('api_name', 'mintDataV1')
	                    ->where('response', 'like', '%'.$txnHash.'%')
	                    ->where('status', 'success')
	                    ->first();

	                    // print_r($api_track_result); 
	                    // echo "<br><br>";
	                    $mintData = [];


	                    if(!empty($api_track_result)) {
	                        $reqParams = json_decode($api_track_result->request_parameters, true);
	                        // Document fields
	                        $mintData["documentType"] = $reqParams["documentType"] ?? "";
	                        $mintData["description"]  = $reqParams["description"]  ?? "";

	                        $mintData["walletID"]            = $walletId;
	                        $mintData["bc_contract_address"] = $row->smart_contract_address;
	                        $mintData["uniqueHash"]          = $barcode;
	                        $mintData["pdf_file"]            = $filePath;
	                        $mintData["template_id"]         = $templateId;
	                        $mintData["tokenId"]            = $token_id;

	                        // Metadata restructuring
	                        $metadataCount = 1;

	                        // Loop through metadata1 to metadata 10 if present
	                        for ($i = 1; $i <= 10; $i++) {

	                            $metaKey = "metadata" . $i;

	                            if (!empty($reqParams[$metaKey])) {

	                                $metaValue = $reqParams[$metaKey];

	                                // If it's a string, decode JSON
	                                if (is_string($metaValue)) {
	                                    $metaDecoded = json_decode($metaValue, true);
	                                } else {
	                                    // already array
	                                    $metaDecoded = $metaValue;
	                                }

	                                // skip empty {}
	                                if (!empty($metaDecoded["label"]) || !empty($metaDecoded["value"])) {

	                                    $mintData[$metaKey] = [
	                                        "label" => $metaDecoded["label"] ?? "",
	                                        "value" => $metaDecoded["value"] ?? "",
	                                    ];

	                                    $metadataCount++;
	                                }
	                            }
	                        }
	                    } else{
	                        $traits = $decodedResponse['nft']['traits'] ?? [];
	                        $mintData = [
	                            "documentType" =>$nftData['name'] ? $nftData['name'] : '',
	                            "description" => $nftData['description'] ? $nftData['description'] : '',
	                        ];
	                        $mintData["walletID"]            = $walletId;
	                        $mintData["bc_contract_address"] = $row->smart_contract_address;
	                        $mintData["uniqueHash"]          = $barcode;
	                        $mintData["pdf_file"]            = $filePath;
	                        $mintData["template_id"]         = $templateId;
	                        $mintData["token_id"]            = $token_id;
	                        
	                        
	                        $traits = $decodedResponse['nft']['traits'] ?? [];
	                        $metadataCount = 1;
	                        $useCount = 0;
	                        foreach ($traits as $Mkey => $box) {
	                            if($Mkey<=4){
	                                $metaLabel = $box['trait_type'];
	                                $metaValue = $box['value']; // Extract actual value from PDF or assign here

	                                $mintData["metadata{$metadataCount}"] = [
	                                    'label' => $metaLabel,
	                                    'value' => $metaValue,
	                                ];

	                                $metadataCount++;
	                                $useCount++;
	                            }
	                        }
	                    }
	                    
	                    $responseBC=CoreHelper::migrationMintPDF($mintData);
	                    // $responseBC = [];
	                    if($responseBC['status']==200){
	                        $bc_txn_hash=$responseBC['txnHash'];
	                        $bc_sc_id=$bc_sc_id;
	                        if(isset($responseBC['ipfsHash'])){
	                            $bc_ipfs_hash=$responseBC['ipfsHash'];
	                            $pinata_ipfs_hash=$responseBC['pinataIpfsHash'];
	                        }else{
	                            $bc_ipfs_hash=null;
	                            $pinata_ipfs_hash=null;
	                            // $bc_sc_id=null;
	                        }

	                        // $resultu = StudentTable::where('key',$barcode)->where('status','1')->update(['bc_txn_hash'=>$bc_txn_hash,'bc_ipfs_hash'=>$bc_ipfs_hash,'pinata_ipfs_hash'=>$pinata_ipfs_hash,'bc_sc_id'=>$bc_sc_id,'updated_at'=>$currentDateTime]);

	                        $resultu = StudentTable::where('key', $barcode)
							    ->where('status', '1')
							    ->update([
							        'bc_txn_hash'       => $bc_txn_hash,
							        'bc_ipfs_hash'      => $bc_ipfs_hash,
							        'pinata_ipfs_hash'  => $pinata_ipfs_hash,
							        'bc_sc_id'          => $bc_sc_id,
							        'updated_at'        => $currentDateTime
							    ]);


	                        $studentData = StudentTable::where('key',$barcode)->where('status','1')->first();
	                        echo "End Student ID: ".$studentData->id;
	                        echo "<br>";
	                        // if ($token_id && $studentData) {
	                            // DB::table('blockchain_other_data')
	                            //     ->updateOrInsert(
	                            //     ['student_table_id' => $studentData->id], // Condition
	                            //     [
	                            //         'token_id' => $responseBC['tokenID'],
	                            //         'bc_md_ipfs_hash' => $responseBC['metadata_ipfs_hash']
	                            //     ]
	                            // );

	                            DB::table('blockchain_other_data')
	                                ->where('student_table_id', $studentData->id)
	                                ->update([
	                                    'token_id' => $responseBC['token_id'],
	                                    'bc_md_ipfs_hash' => $responseBC['metadata_ipfs_hash'],
	                                ]);
	                                
	                        // }
	                           


	                    } else {
	                        echo "Lighthouse Reminting Failed\n";
	                    }
	                    print_r($responseBC);
	                    echo "<br><br>";
	                        
                        if($s3Flag == '1') {
                            unlink($filePath);
                       }
	                } else {
	                    echo "CID NOT FOUND on Pinata Gateway\n";
	                }



	            }
	            echo "</pre><br>";
	            
	            // $pinata_result=BlockChainV1::pinataRetreiveDetails($row);
	            // echo "<pre>";
	            // print_r($pinata_result);
	            // echo "</pre><br><br>";

	            // if($pinata_result['status']===200){
	                
	            //     $templateId = $row->template_id;
	            //     $extractorDetails = $pinata_result['meta_data']['attributes'];//json_decode($template->extractor_details, true);

	            //     $pdfUrl = $pinata_result['meta_data']['image'];
	                
	            //     $walletId = $row->wallet_address;//'0x8fA7E9EcF3DBdF6A5B6Dfbac2bEe5B151b373819';
	            //     $subdomain[0] = 'anu'; // e.g. "demo"
	            //     $certificateFilename = $row->certificate_filename;
	            //     $serialNo = $row->serial_no;
	            //     $barcode = $row->key;
	            //     $currentDateTime = date("Y-m-d H:i:s");
	            //     $filePath = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certificateFilename;
	                
	            // }
	        }
	    // });


    }

    

    public function pinataToLighthouse() {
        // $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";

        //  $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";
        //  $output = '';
        //     $txnHash = '0x9b272639fa1bc55a6200ac21a7bc58ae3a5aa9bd93a1c7666395ec908fbd9bd6';
        //  //$txnHash = $api_response->txnHash;
        //  $cmd =  "$pyscript $txnHash 2>&1";


        //  exec('C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe '.$cmd, $output, $return);

        //     print_r($output);die();
        $api_track_results = DB::table('bc_api_tracker')
        ->where('api_name', 'mintDataV1')
        ->where('request_parameters', 'like', '%pinata%')
        ->where('status', 'success')
        ->get();
        dd($api_track_results);
        foreach ($api_track_results as $key => $res) {
            $api_response=json_decode($res->response);
            $student_tbl_data = DB::table('student_table')
            ->where('bc_txn_hash', $api_response->txnHash)->where('status', '1')
            ->first();

        // dd($student_tbl_data->id);
        //  $exists = DB::table('blockchain_other_data')
        //     ->where('token_id', $api_response->tokenID)
        //     ->exists();

        // if (!$exists) {

        //update token_id in block chain other data start
            $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";

            $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";
            $output = '';
            // $txnHash = '0xdf942312510ff0ed8c631b0e4f165115ff4d3b242f3d2e8ebdf93215c6d51886';
            $txnHash = $api_response->txnHash;
            $cmd =  "$pyscript $txnHash 2>&1";


            exec('C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe '.$cmd, $output, $return);

            // print_r($output);
            $jsonString = str_replace("'", '"', $output[1]);
            $data = json_decode($jsonString, true);

            // Step 3: Access the token_id
            $token_id = $data['token_id'] ?? null;

            $result = DB::table('blockchain_other_data')->updateOrInsert(
                ['student_table_id' => $student_tbl_data->id], // Condition
                ['token_id' => $token_id]                   // Data to insert or update
            );
        //update token_id in block chain other data end
        // }

            $row =DB::table('student_table as st')
            ->join('blockchain_other_data as bod', 'st.id', '=', 'bod.student_table_id')
            ->join('bc_smart_contracts as bsc', 'st.bc_sc_id', '=', 'bsc.id')
            ->where('st.status', '1')
            ->whereNotNull('bsc.smart_contract_address')
            ->whereNotNull('bod.token_id')
            ->where('bod.token_id',$api_response->tokenID)
            ->where('st.bc_txn_hash', $api_response->txnHash)
            ->select('bod.id as bod_id','bod.token_id', 'bsc.smart_contract_address','st.template_id','st.certificate_filename','bsc.wallet_address','st.key','st.serial_no','st.id as student_table_id')
            // ->orderBy('st.id');
            ->first();
            // dd($row);
        // Get SQL and bindings
        // $sql = $row->toSql();
        // $bindings = $row->getBindings();

        // // Merge bindings into SQL for display
        // $fullSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

        //dd($fullSql); // dump the complete query with values
        // foreach ($data as $row) {
            $pinata_result=BlockChainV1::pinataRetreiveDetails($row);
        // dd($pinata_result); 
            if($pinata_result['status']===200){
        ////////
            $templateId = $row->template_id;
            // $template = DB::table('uploaded_pdfs')
            // ->select([
            //     'ep_details',
            //     'id',
            //     'extractor_details',
            //     'template_name',
            //     'pdf_page',
            //     'print_bg_file',
            //     'print_bg_status',
            //     'verification_bg_file',
            //     'verification_bg_status',
            //     DB::raw("IFNULL(bc_contract_address, '') as bc_contract_address"),
            //     'bc_document_description',
            //     'bc_document_type'
            // ])
            // ->where('id', $templateId)
            // ->first();

            // if (!$template) {
            //     logger("Template not found with template id: $templateId");
            // continue;//  skip current loop iteration
            // }
        // echo "<pre>";
        // print_r($template);
        // die();
        // $boxes = json_decode($template->ep_details, true);
        $extractorDetails = $pinata_result['meta_data']['attributes'];//json_decode($template->extractor_details, true);
        $walletId = $row->wallet_address;//'0x8fA7E9EcF3DBdF6A5B6Dfbac2bEe5B151b373819';
        $subdomain[0] = 'anu'; // e.g. "demo"
        $certificateFilename = $row->certificate_filename;
        $serialNo = $row->serial_no;
        $barcode = $row->key;
        $currentDateTime = date("Y-m-d H:i:s");
        $filePath = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certificateFilename;
        if (!file_exists($filePath)) {
            // logger("File not found: $filePath");
                logger("Local file not found: $filePath, using IPFS fallback.");

                $pdfUrl = $pinata_result['meta_data']['image'];//'https://ipfs.io/ipfs/bafybeih4gqjjhub7bckwythjcuakkks3rr4bo5ksvw7f5ojpbwvbruqcpy/1736770952637_17001A0591.pdf';
                $pdfContent = @file_get_contents($pdfUrl);
                if (file_put_contents($filePath, $pdfContent)) {
                logger("PDF saved successfully at: $filePath");
                }
            //continue; //  skip current loop iteration
        }
        $mintData = [
            "documentType" =>$pinata_result['meta_data']['name'],
            "description" => $pinata_result['meta_data']['description'],
        ];
        $metadataCount = 1;
        $useCount = 0;

        foreach ($extractorDetails as $Mkey => $box) {
                if($Mkey<=4){
                $metaLabel = $box['trait_type'];
                $metaValue = $box['value']; // Extract actual value from PDF or assign here

                    $mintData["metadata{$metadataCount}"] = [
                        'label' => $metaLabel,
                        'value' => $metaValue,
                    ];

                    $metadataCount++;
                    $useCount++;
                }
            }
            if ($useCount > 0 && $row->smart_contract_address) {

                $mintData["walletID"] = $walletId;
                $mintData["bc_contract_address"] = $row->smart_contract_address;
                $mintData["uniqueHash"] = $barcode;
                // $mintData["pdf_file"] = "https://{$subdomain}.seqrdoc.com/{$subdomain}/backend/pdf_file/{$certificateFilename}";
                $mintData['pdf_file']=$filePath;
                $mintData['template_id']=$templateId;

                echo "mintData => <pre>";
                print_r($mintData);echo "<br><br>";
                // die();
                $responseData=CoreHelper::mintPDF($mintData);
                echo "responseData=> <pre>";
                print_r($responseData);
                echo "<br>";
                if($responseData['status']==200){

                    DB::table('student_table')
                    ->where('serial_no', $serialNo)
                    ->where('status', 1)
                    ->update([
                        'bc_txn_hash' => $responseData['txnHash'],
                        'bc_ipfs_hash' => $responseData['data']['doc_ipfs_hash'],
                    ]);
                    DB::table('blockchain_other_data')
                    ->where('student_table_id', $row->student_table_id)
                    ->where('id', $row->bod_id)
                    ->update([
                        'token_id' => $responseData['data']['tokenID'],
                        'bc_md_ipfs_hash' => $responseData['data']['metadata_ipfs_hash'],
                    ]);

                    // Log API call
                    // DB::table('bc_api_tracker')->insert([
                    //     'api_name' => 'pinata_to_lighthouse_mintData',
                    //     'request_method' => 'POST',
                    //     'request_url' => 'https://mainnet-apis.herokuapp.com/v1/mainnet/mint',
                    //     'request_parameters' => json_encode($mintData),
                    //     'response' => json_encode($responseData['data']),
                    //     'status' => ($responseData['status']==200)?'success' : 'failed',
                    //     'created_at' => $currentDateTime,
                    // ]);

                    DB::table('bc_mint_data')->insert([
                        'txn_hash' => $responseData['txnHash'],
                        'gas_fees' => $responseData['data']['gasPrice'],
                        'token_id' => $responseData['data']['tokenID'],
                        'key' => $barcode,
                        'created_at' => $currentDateTime,
                    ]);
                }
            }
        ////////
        }
        // }
        }
        
        dd("hi");
    }
    public function anumintData(Request $request)
    {
        // dd("hi");
        ini_set('max_execution_time', '600');
        ini_set('max_input_time', '300');
        ini_set('memory_limit', '4096M');
       $domain = \Request::getHost();
        $subdomain = explode('.', $domain); 
         $filename = "database_01_12_2025.xlsx";
        $pathImport = public_path() . '/' . $subdomain[0] . '/backend/blockchain/import/';
        $import_filename_import = $pathImport . $filename;
        // dd($import_filename_import);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($import_filename_import);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($import_filename_import);
        $sheet = $spreadsheet->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
        $currentDateTime = date("Y-m-d H:i:s");
         echo"<pre>";
        for ($excel_row = 288; $excel_row <= 289; $excel_row++)//$highestRow upto 104 is correct 252 is done
        {
            $rowData1 = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);

            // print_r($rowData1[0]);
            // exit;
            $studentID = $rowData1[0][9];
            $encryptedKey = $rowData1[0][9];
            $check_data = StudentTable::where('serial_no', $studentID)->where('status', 1)->first();//->where('updated_at', '<','2025-12-05')->first();
//             $sql = $check_data->toSql();
// $bindings = $check_data->getBindings();

// dd($sql, $bindings);
           // dd($check_data);
             // $check_data=false;
                if($check_data){

                    // if(!empty($check_data->bc_txn_hash)){
                    //      $check_dataFlag=true;
                    //      $encryptedKey="";
                    // }else{
                         $check_dataFlag=false;
                         $encryptedKey=$check_data->key;
                         $certName=$check_data->certificate_filename;
                    // }

                }else{
                    $check_dataFlag=true;
                    $encryptedKey="";
                }
                 $encryptedKey=$check_data->key;
                 $certName=$check_data->certificate_filename;
                // $check_dataFlag=false;
                if(!$check_dataFlag&&!empty($encryptedKey)&&!empty($certName)){
                   echo $certName = str_replace("/", "_", $studentID) . ".pdf";
                    $pdf_path = public_path() . '\\' . $subdomain[0] . '\backend\pdf_file\\' . $certName;
                    //exit;
                    
                    if (file_exists($pdf_path)) {
                        ////////////////////
                        //  $source=$pdf_path;
                        // $destination=public_path()."\\anu\\backend\\pdf_file\\anupink_backup\\".$certName;

                        // if(file_exists($destination)){
                        //     continue;
                        // }else if (file_exists($source)) {


                        //  \File::copy($source,$destination);
                        // }else{
                        //      Log::info("No Data found on : ".$readData['certificate_filename']);
                        // }
                        ////////////////////
                        $studentIDArr = explode('-', $rowData1[0][6]);
                        $studentIDMeta = substr($studentIDArr[0], 1);
                        $mintData = array();
                        $mintData["walletID"] = '0x06c87829C80924E355F61C1b535533770553FD37';
                        $mintData['documentType'] = "Certificate";
                        $mintData['description'] = "Educational Document";
                        $mintData['metadata1'] = ["label" => "Student ID", "value" => $studentIDMeta];
                        $mintData['metadata2'] = ["label" => "Student Name", "value" => $rowData1[0][0]];
                        $mintData['metadata3'] = ["label" => "Programme", "value" => $rowData1[0][1]];
                        $mintData['metadata4'] = ["label" => "Degree Certificate Sr. No.", "value" => $rowData1[0][9]];
                        $mintData['metadata5'] = ["label" => "University", "value" => "Anant National University"];

                        $mintData['uniqueHash'] = $encryptedKey;

                        $mintData['pdf_file'] = $pdf_path;
                        $mintData['template_id'] = 100;
                        $mintData['bc_contract_address'] = "0xaA31348eE47deF0A76a023a157d50035b0C9A204";


                    //      echo "<br>";
                    // print_r($mintData);
                    // echo "</pre>";die;
                        // dd($mintData);
                        $template_type = 2;
                    $blockchain_type = 1;
                    $response=CoreHelper::customMintPDF($mintData,$blockchain_type,$template_type);
                    $bc_file_hash=CoreHelper::generateFileHash($pdf_path);
                    // dd($response);
                      //  $response=CoreHelper::mintPDF($mintData);
                        if($response['status']==200){
                            
                            echo $excel_row."is done<br>";
                            $bc_txn_hash=$response['txnHash'];
                            $bc_sc_id=$response['bc_sc_id'];
                            $metadata_ipfs_hash = $response['metadata_ipfs_hash'];
                            $tokenId = $response['tokenID'];
                            if(isset($response['ipfsHash'])){
                                $bc_ipfs_hash=$response['ipfsHash'];
                                $pinata_ipfs_hash=$response['pinataIpfsHash'];
                            }else{
                                $bc_ipfs_hash=null;
                                $pinata_ipfs_hash=null;
                                // $bc_sc_id=null;
                            }
                            if(isset($response['gasPrice'])){
                                $gasPrice=$response['gasPrice'];
                            }else{
                                $gasPrice=null;
                                // $bc_sc_id=null;
                            }
                            
                            DB::table('student_table')
                            ->where('serial_no', $rowData1[0][9])
                            ->where('status', 1)
                            ->update([
                            'bc_txn_hash' => (string) $bc_txn_hash,
                            'bc_ipfs_hash' => (string) $bc_ipfs_hash,
                            'bc_file_hash' => $bc_file_hash,
                            'bc_sc_id' => $bc_sc_id,
                            'updated_at' => $currentDateTime
                            ]);

                            DB::table('blockchain_other_data')
                            ->where('student_table_id', $check_data->id)
                            ->update([
                            'token_id' => $tokenId,
                            'bc_md_ipfs_hash' => $metadata_ipfs_hash,
                            ]);
                            
                            DB::table('bc_mint_data')->insert([
                            'txn_hash' => (string) $bc_txn_hash,
                            'gas_fees' => $gasPrice,
                            'token_id' => $tokenId,
                            'key' => $encryptedKey,
                            'created_at' => $currentDateTime,
                            ]);
                        }else{//file check
                             echo "File Not Found";
                        }
                    }else{//non empty data
                    echo "Data Not Found";
                    }
                    
                }
        }
    }
    public function fetchToken() {
        ini_set('max_execution_time', '600');
        ini_set('max_input_time', '300');
        ini_set('memory_limit', '4096M');

        // Running code
        
        // $studentDataOrg = StudentTable::where('status',1)->orderBy('id','asc')->limit(5)->get();
        // $studentDataOrg = StudentTable::where('status',1)->orderBy('id','asc')->get();
        
        $studentDataOrg = StudentTable::where('status', 1)
        ->leftJoin('blockchain_other_data', 'student_table.id', '=', 'blockchain_other_data.student_table_id')
        ->whereNull('blockchain_other_data.token_id')
        // ->whereNull('blockchain_other_data.student_table_id')
        ->whereNotNull('student_table.bc_txn_hash')
        
        ->orderBy('student_table.id', 'asc')
        ->select('student_table.id','student_table.bc_txn_hash') // Select only fields from StudentTable
        ->limit(10)
        ->get();
        // $studentDataOrg = StudentTable::where('status', 1)
        // ->whereNotNull('student_table.bc_txn_hash')
        // ->whereIn('student_table.id', function ($query) {
        //     $query->select('blockchain_other_data.student_table_id')
        //           ->from('blockchain_other_data')
        //           ->whereNull('blockchain_other_data.token_id');
        // })
        // ->orderBy('student_table.id', 'desc')
        // ->select('student_table.*') // Select only fields from StudentTable
        // ->limit(80)
        // ->get();



        echo "<pre>";
        print_r(count($studentDataOrg));
        echo "</pre>";
        die();

        $directoryUrlForward="C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
        $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";

        $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";            

        foreach($studentDataOrg as $studentData) {

            $output = '';
            // $txnHash = '0xdf942312510ff0ed8c631b0e4f165115ff4d3b242f3d2e8ebdf93215c6d51886';
            $txnHash = $studentData['bc_txn_hash'];
            $cmd =  "$pyscript $txnHash 2>&1";
            
            
            exec($cmd, $output, $return);

            // print_r($output);
            $jsonString = str_replace("'", '"', $output[1]);
            $data = json_decode($jsonString, true);

            // Step 3: Access the token_id
            $token_id = $data['token_id'] ?? null;
            // print_r($output);
            // echo $token_id;
            // echo "<br>";
            // // echo 'Python '.$cmd;
            // die();

            $result = DB::table('blockchain_other_data')->updateOrInsert(
                ['student_table_id' => $studentData['id']], // Condition
                ['token_id' => $token_id]                   // Data to insert or update
            );
            if ($result) {
                echo $studentData['id'].' - Record updated or inserted successfully.';
            } else {
                echo $studentData['id'].' - Failed to update or insert the record';
            }
            echo "<br>";



        }

        // die();


        // $directoryUrlForward="C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
        // $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";


        // $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";
        
        // $txnHash = '0xdf942312510ff0ed8c631b0e4f165115ff4d3b242f3d2e8ebdf93215c6d51886';
        // // $txnHash = $studentData['bc_txn_hash'];
        // $cmd =  "$pyscript $txnHash 2>&1";
            
        // exec($cmd, $output, $return);
        // $jsonString = str_replace("'", '"', $output[1]);
        // $data = json_decode($jsonString, true);

        // // Step 3: Access the token_id
        // $token_id = $data['token_id'] ?? null;
        // // print_r($output);
        // // // echo 'Python '.$cmd;
        // // die();

        // DB::table('blockchain_other_data')->updateOrInsert(
        //     ['student_table_id' => $studentData['id']], // Condition
        //     ['token_id' => $token_id]                   // Data to insert or update
        // );

        
    }



    public function fetchTokenOptimize() {

        $directoryUrlForward="C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
        $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";

        $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";   

        $studentDataOrg = StudentTable::where('status', 1)
        ->whereNotNull('bc_txn_hash')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('blockchain_other_data')
                  ->whereRaw('blockchain_other_data.student_table_id = student_table.id');
        })
        ->orderBy('id', 'asc')
        ->chunk(20, function ($students) use ($pyscript) {

            foreach ($students as $studentData) {
                // $txnHash = escapeshellarg($studentData->bc_txn_hash);
                // $cmd = escapeshellcmd("$pyscript $txnHash 2>&1");
                $output = '';

                $txnHash = $studentData['bc_txn_hash'];
                $cmd =  "$pyscript $txnHash 2>&1";

                exec($cmd, $output, $return);
                $jsonString = str_replace("'", '"', $output[1] ?? '');
                $data = json_decode($jsonString, true);
                $token_id = $data['token_id'] ?? null;

                DB::table('blockchain_other_data')->updateOrInsert(
                    ['student_table_id' => $studentData->id],
                    ['token_id' => $token_id]
                );

                echo $studentData->id . ' - Processed<br>';
            }
        });

    }



    public function fetchTokenPython() {
        $directoryUrlForward="C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
        $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";

        $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script_v1.py";  
        $cmd =  "$pyscript $txnHash 2>&1";
        exec('C:/Users/Administrator/AppData/Local/Programs/Python/Python38/python.exe '.$cmd, $output, $return);

        print_r($output);
        die();
    }



    public function fetchNFTWithCurl($contract, $tokenId)
    {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

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
         
        // if ($err) {
        //     $status = 'failed';

        //     $responseTime = microtime(true) - LARAVEL_START;
        //     // $api_tracker_id = Self::insertTracker($requestUrl,$requestMethod,$requestParameters,$decodedResponse,$status,$responseTime,'retreiveDetailsV1');
        //     return  "cURL Error #:" . $err;
        // } else {
        //     $status = 'success';
        //     $responseTime = microtime(true) - LARAVEL_START;

        //     //$response['status']=200;
        //     $nftData = $decodedResponse['nft'] ?? null;
           
        //     $url = $nftData['metadata_url'];

        //     if($url)  {
        //         $original_url = $url;
        //         $url = $this->convertToW3sLink($url);


        //         $pdfUrl = $this->fetchMetadataImage($url, $decodedResponse['nft']['image_url'] ?? null,$original_url);

        //     } else {
        //         $metadata_url = '';
        //     }
            

        //     $dataR['success'] = 200;
        //     $dataR['message'] = "Valid Document";
        //     $dataR['name'] = $nftData['name'] ? $nftData['name'] : '';
        //     $dataR['description'] = $nftData['description'] ? $nftData['description'] : '';

        //     $traits = $decodedResponse['nft']['traits'] ?? [];
            
        //     // Loop through the traits and remove the one with trait_type = 'UniqueHash'
        //     foreach ($traits as $key => $trait) {
        //         if (isset($trait['trait_type']) && $trait['trait_type'] === 'UniqueHash') {
        //             unset($traits[$key]);
        //             break; // Stop after removing it
        //         }
        //     }
        //     // Update the original array if needed
        //     $decodedResponse['nft']['traits'] = array_values($traits); // Reindex the array
        //     $dataR['data'] = array_reverse($decodedResponse['nft']['traits']);


        //     $dataR['contractAddress']=$contractAddress;  

            

        //     $owners = $decodedResponse['nft']['owners'] ?? [];

        //     if (!empty($owners) && isset($owners[0]['address'])) {
        //         $dataR['walletID']=$owners[0]['address'];
        //     } else{ 
        //         $dataR['walletID'] = '-';
        //     }

        //     $dataR['IPFS_URL'] =  $pdfUrl;
            
        //     if (strpos($dataR['IPFS_URL'], 'https') !== 0) {
        //         // Add 'https://' prefix if not already present
        //         $dataR['pdfUrl'] = "https://ipfs.io/ipfs/" . substr($dataR['IPFS_URL'], 7);
        //     } else {
        //         // Use IPFS_URL as-is if it already starts with 'https'
        //         //$dataR['pdfUrl'] = $dataR['IPFS_URL'];


        //         $url = $dataR['IPFS_URL'];

        //         // Remove the 'https://ipfs.io/ipfs/' part
        //         $trimmed = str_replace("https://ipfs.io/ipfs/", "", $url);

        //         // Split into CID and the rest
        //         $parts = explode("/", $trimmed, 2);

        //         // Build new URL
        //         $dataR['pdfUrl'] = "https://" . $parts[0] . ".ipfs.w3s.link/" . $parts[1];

        //     }

        //     $dataR['polygonTxnUrl']="https://polygonscan.com/tx/".$studentData['bc_txn_hash'];
  

        //     $dataR['txnHash']=$studentData['bc_txn_hash'];

        //     // $api_tracker_id = Self::insertTracker($requestUrl,$requestMethod,$requestParameters,$dataR,$status,$responseTime,'retreiveDetailsV1');

        //     return  $response;
        // }
        return $response;

    }

    
    function convertToW3sLink($url) {
        if (!$url) return $url;

        // 1. ipfs:// scheme
        if (strpos($url, 'ipfs://') === 0) {
            $withoutPrefix = substr($url, 7);
            $parts = explode('/', $withoutPrefix, 2);
            $hash = $parts[0];
            $path = $parts[1] ?? '';
            return "https://{$hash}.ipfs.w3s.link" . ($path ? "/{$path}" : "");
        }

        // 2. ipfs.io URLs
        if (preg_match("#https?://ipfs\.io/ipfs/([^/]+)(/.*)?#", $url, $matches)) {
            $hash = $matches[1];
            $path = isset($matches[2]) ? ltrim($matches[2], '/') : '';
            return "https://{$hash}.ipfs.w3s.link/{$path}";
        }

        // 3. Pinata (opensea-private) URLs
        if (preg_match("#https?://opensea-private\.mypinata\.cloud/ipfs/([^/]+)/(.*)#", $url, $matches)) {
            $hash = $matches[1];
            $path = $matches[2];
            return "https://{$hash}.ipfs.w3s.link/{$path}";
        }

        // 4. Plain hash without protocol
        if (!preg_match("#^https?://#", $url)) {
            $parts = explode('/', $url, 2);
            $hash = $parts[0];
            $path = $parts[1] ?? '';
            return "https://{$hash}.ipfs.w3s.link" . ($path ? "/{$path}" : "");
        }

        // 5. If URL already formatted or none matched, return original
        return $url;
    }

    function fetchMetadataImage(string $url, ?string $fallback = null,$original_url): ?string {

        



        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // echo $url;
        // die();

        if ($response === false || $httpCode !== 200) {
            if (strpos($original_url, 'ipfs://') === 0) {
                $url = str_replace('ipfs://', 'https://gateway.lighthouse.storage/ipfs/', $original_url);

                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => $timeout,
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            }
        }
        
        if ($response !== false && $httpCode === 200) {


            $metadata = json_decode($response, true);
            return $metadata['image'] ?? $fallback;
        }

        return $fallback;
    }


    function fetchTokenSingle($txnHash,$barcode) {
        $directoryUrlForward="C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
        $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";
        $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";
        // $cmd =  escapeshellarg($pyscript) ." 2>&1";
        $txnHash = $txnHash;
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
        

        $studentData = StudentTable::select('id')->where('key',$barcode)->first();

        if($token_id) {
            // DB::table('blockchain_other_data')->insert([
            //     'student_table_id' => $studentData['id'],
            //     'token_id' => $token_id,
            // ]);
            DB::table('blockchain_other_data')->updateOrInsert(
                ['student_table_id' => $studentDataID], // Condition
                ['token_id' => $token_id]                   // Data to insert or update
            );
        }
        
        return $token_id;
    }

    function getCidFromIpfsUri(string $input) {
        $input = trim($input);

        // If it already looks like a raw CID, return it (quick path)
        if (preg_match('/^(Qm[1-9A-HJ-NP-Za-km-z]{44}|bafy[0-9a-z]{40,})$/i', $input, $m)) {
            return $m[1];
        }

        // Try to find CID inside a more complex string/URI
        if (preg_match('/(Qm[1-9A-HJ-NP-Za-km-z]{44}|bafy[0-9a-z]{40,})/i', $input, $m)) {
            return $m[1];
        }

        return false;
    }

    function checkPinataCidExists(string $cid): bool
    {
        // Build gateway URL
        $url = "https://amethyst-tiny-kangaroo-972.mypinata.cloud/ipfs/" . $cid;

        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,              // HEAD request (faster)
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
        ]);

        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 200 = file found
        // 404 = not found
        return ($httpCode === 200);
    }

    
}
