<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExcelValidationRequest;
use App\models\BackgroundTemplateMaster;
use App\models\FieldMaster;
use App\models\FontMaster;
use App\models\IDCardStatus;
use App\models\blockchain\StudentTable;
use App\models\SystemConfig;
use App\models\TemplateMaster;
use File;
use Illuminate\Http\Request;
use Auth,Storage;
use TCPDF;
use QrCode;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use App\models\SbStudentTable;
use App\models\Config;
use App\models\PrintingDetail;
use App\Helpers\CoreHelper;
use Maatwebsite\Excel\Facades\Excel;


use DB;

class UNIKBPCertificateController extends Controller
{

    
    public function mintData(Request $request){
     

        ini_set('max_execution_time', '600');
        ini_set('max_input_time', '300');
        ini_set('memory_limit', '4096M');
    
        // dd(1);
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        //  CoreHelper::checkContactAddress(2,$templateType='PDF2PDF');
        // exit;
        // $filename="mitwe_degree_new.xlsx";
        // $filename="Database_Part4.xlsx";
        $filename="unikbp_excel.xlsx";
        

        // 1 => Degree
        // 2 => PHD
        // 3 => Rank
        // 4 => Medal
        $template_type_format= '1';

        // $pathImport = public_path().'/'.$subdomain[0].'/blockchain/import/2024/';
        $pathImport = public_path().'/'.$subdomain[0].'/blockchain/import/2025/';
        $import_filename_import = $pathImport.$filename;
        // dd( $import_filename_import);
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($import_filename_import);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($import_filename_import);
        $sheet = $spreadsheet->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
        // dd($rowData);
        echo "<table>";
        $result=array();
        $template_id=1; //4 : Degree | 5 :Medal | 6:Rank | 7:PHD
        $admin_id = 1;//\Auth::guard('admin')->user()->toArray();
        $auth_site_id=357;//Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        $template_data = TemplateMaster::select('id','template_name','is_block_chain','bc_document_description','bc_document_type','bc_contract_address')->where('id',$template_id)->first();
        // print_r($template_data);
        //exit;
        $template_type=1;
        $certificate_type="Degree Certificate";
        $log_serial_no=1;
        $withoutExt="Blockchain_".date('Y_m_d_h_i_s');
        // $date=date('dmYHis');
       // dd($highestRow);
        $date="240120253000";
        $start_row = 2;
        $chunkSize = 5;
        $maxRows =$highestRow;//450- 480;//
        $last = DB::table('readexcel')->where('id', 1)->value('last');
        // for ($start = $last; $start <= $maxRows; $start += $chunkSize) {

        $end =$last+100;// min($last + $chunkSize - 1, $maxRows);
        for($excel_row =$last+1; $excel_row <=$end; $excel_row++)//$highestRow
        {
            // echo $highestRow;
            $rowData1 = $sheet->rangeToArray('A'. $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);
            //Student ID for php and other
            $studentID = $rowData1[0][1];
            $studentIDMetadata = $rowData1[0][1];
            //For Rank / Medal with date
            //  $studentID = $rowData1[0][49];
            // echo"<pre>"; print_r($rowData1);
            //    exit;
            // echo"<pre>";
            //  print_r($studentID);
            // exit;
            //For  PHD /Degree Certiticate without date
            // $encryptedKey = strtoupper(md5($studentID.$date));
            $encryptedKey = $rowData1[0][20];
            //echo "<br>";
            //For Rank / Medal with date
            //$encryptedKey = strtoupper(md5($studentID.$date));
            //exit;
           $blockchainUrl=$rowData1[0][21];
           // $blockchainUrl=CoreHelper::getBCVerificationUrl(base64_encode($encryptedKey));
           echo "<tr><td>".$studentID."</td><td>".$encryptedKey."</td><td>".$blockchainUrl."</td></tr>";

            //$result[] = array("student_id"=>$studentID,"encrypted_key"=>$encryptedKey,"blockchain_url"=>$blockchainUrl);
            //Blockchain fields
            //exit;
            //  $check_data = StudentTable::select('id')->where('serial_no',$studentID)->whereNotNull('bc_txn_hash')->first();
           // $check_data = StudentTable::select('id')->where('serial_no',$studentID)->where('status',1)->whereDate('created_at','2024-10-16')->whereNotNull('bc_txn_hash')->first();

            $check_data = StudentTable::where('serial_no',$studentID)->where('status',1)->where('template_id',$template_id)->whereDate('created_at', '>=','2024-10-16')->first();

            // dd($check_data);
             // exit;

           //echo $check_data->certificate_filename;
            if($check_data){

                if(!empty($check_data->bc_txn_hash)){
                     $check_dataFlag=true;
                     $encryptedKey="";
                }else{
                     $check_dataFlag=false;
                     $encryptedKey=$check_data->key;
                     $certName=$check_data->certificate_filename;
                }

            }else{
                $check_dataFlag=true;
                $encryptedKey="";
            }
           // $check_data=false;
            // echo "<br>";
            //   print_r($check_dataFlag);

            // echo  $check_data->id;
            
            // echo "<pre>";
          //   print_r($rowData1[0]);
            // echo "</pre>";
            // exit;
            //if(1==2){
            $contract = DB::table('bc_smart_contracts')->where('template_id', $template_id)->where('is_active', 1)->first();
            if(!$check_dataFlag&&!empty($encryptedKey)&&!empty($certName)){
                //$certName = str_replace("/", "_", $studentID) .".pdf";
                  $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;
                if(file_exists($pdf_path)){
                    
                    // For Degree
                    // if($template_type_format == 1) {
                        // $studentIDMetadata=str_replace("DC_2024_","",$studentIDMetadata);
                        $mintData=array();
                        $mintData['documentType']="Educational Document";
                        $mintData['description']="Description : PRN Number : ".$studentIDMetadata;
                        $mintData['metadata1']=["label"=> "Student Name", "value"=> $rowData1[0][3]];
                        $mintData['metadata2']=["label"=> "Faculty Name", "value"=> $rowData1[0][10]];
                        $mintData['metadata3']=["label"=> "Subject", "value"=> $rowData1[0][12]];
                        $mintData['metadata4']=["label"=> "Grade", "value"=> $rowData1[0][14]];
                        $mintData['metadata5']=["label"=> "Passing Month & Year", "value"=> $rowData1[0][16]];

                        $mintData['uniqueHash']=$encryptedKey;
                        
                        $mintData['pdf_file']=$pdf_path;
                        $mintData['template_id']=$template_id;

                        
                        $mintData['bc_contract_address']=$contract->smart_contract_address;
                        // $mintData['bc_contract_address']=$template_data['bc_contract_address'];


                       
                        //  : {May 2023}


                    // } else if($template_type_format == 2) { // For PHD

                    //     $mintData=array();
                    //     $mintData['documentType']="Educational Document";
                    //     $mintData['description']="Student ID :".$studentIDMetadata;
                    //     $mintData['metadata1']=["label"=> "Student Name", "value"=> $rowData1[0][1]];
                    //     $mintData['metadata2']=["label"=> "Competency Level", "value"=> $rowData1[0][10]];
                    //     $mintData['metadata3']=["label"=> "Specialization", "value"=> $rowData1[0][20]];
                    //     $mintData['metadata4']=["label"=> "Topic", "value"=> $rowData1[0][13]];
                    //     $mintData['metadata5']=["label"=> "Completion date", "value"=> $rowData1[0][23]];

                    //     $mintData['uniqueHash']=$encryptedKey;
                        
                    //     $mintData['pdf_file']=$pdf_path;
                    //     $mintData['template_id']=$template_id;
                    //     $mintData['bc_contract_address']=$template_data['bc_contract_address'];

                    // }else if($template_type_format == 3) { // For Rank
                    //     // echo"<pre>";print_r($rowData);
                    //     // exit;
                    //      $studentIDMetadata=str_replace("RANK_2024_","",$studentIDMetadata);
                    //     $mintData=array();
                    //     $mintData['documentType']="Educational Document";
                    //     $mintData['description']="Student ID :".$studentIDMetadata;
                    //     $mintData['metadata1']=["label"=> "Student Name", "value"=> $rowData1[0][1]];
                    //     $mintData['metadata2']=["label"=> "Competency Level", "value"=> $rowData1[0][10]];
                    //     $mintData['metadata3']=["label"=> "Specialization", "value"=> $rowData1[0][25]];
                    //     $mintData['metadata4']=["label"=> "Rank", "value"=> $rowData1[0][13]];
                    //     $mintData['metadata5']=["label"=> "Completion date", "value"=> $rowData1[0][21]];
                    //     $mintData['uniqueHash']=$encryptedKey;
                        
                    //     $mintData['pdf_file']=$pdf_path;
                    //     $mintData['template_id']=$template_id;
                    //     $mintData['bc_contract_address']=$template_data['bc_contract_address'];

                    // }else if($template_type_format == 4) { // For Medal
                    //     // echo"<pre>";print_r($rowData);
                    //     // exit;

                    //     $studentIDMetadata=str_replace("MEDAL_2024_","",$studentIDMetadata);

                    //     $mintData=array();
                    //     $mintData['documentType']="Educational Document";
                    //     $mintData['description']="Student ID :".$studentIDMetadata;
                    //     $mintData['metadata1']=["label"=> "Student Name", "value"=> $rowData1[0][1]];
                    //     $mintData['metadata2']=["label"=> "Competency Level", "value"=> $rowData1[0][10]];
                    //     $mintData['metadata3']=["label"=> "Specialization", "value"=> $rowData1[0][24]];
                    //     $mintData['metadata4']=["label"=> "Medal", "value"=> $rowData1[0][22]];
                    //     $mintData['metadata5']=["label"=> "Completion date", "value"=> $rowData1[0][18]];
                    //     $mintData['uniqueHash']=$encryptedKey;
                        
                    //     $mintData['pdf_file']=$pdf_path;
                    //     $mintData['template_id']=$template_id;
                    //     $mintData['bc_contract_address']=$template_data['bc_contract_address'];

                    // }
                    // echo "<pre>";
                    // print_r($mintData);
                    // echo "</pre>";
                    // die;
                    
                    $template_type = 2;
                    $blockchain_type = 1;
                    $response=CoreHelper::customMintPDF($mintData,$blockchain_type,$template_type);
                    $bc_file_hash=CoreHelper::generateFileHash($pdf_path);
                    // dd($response,$mintData,$blockchain_type,$template_type);
                    // echo"<pre>";print_r($response);
                      //  $response=CoreHelper::mintPDF($mintData);
                        if($response['status']==200){
                            $currentDateTime = date("Y-m-d H:i:s");
                            echo $excel_row." & sr.No.".$studentIDMetadata." is done<br>";

                            $bc_txn_hash=$response['txnHash'];
                            $bc_sc_id=$response['bc_sc_id'];
                            $metadata_ipfs_hash = $response['metadata_ipfs_hash'];
                            $tokenId = $response['token_id'];
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
                            ->where('serial_no', $studentIDMetadata)
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
                            
                          DB::table('readexcel')->where('id', 1)->update(['last' => $excel_row]);  
                        $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Success".PHP_EOL;
                
                    }else{
                        // echo ">tr>#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Not deployed on blockchain network.>/tr>";
                         $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Not deployed on blockchain network.".PHP_EOL;
                    }
                }else{
                     $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Pdf not found.".PHP_EOL;
                }

            }else{
                 DB::table('readexcel')->where('id', 1)->update(['last' => $excel_row]); 
                // echo "<tr>#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Data not found in student table.</tr>";
                $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Data  found in student table.".PHP_EOL;
            }
            
            // $date = date('Y-m-d H:i:s').PHP_EOL;
                

            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
            }
            else{
                $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
            }
            $fp = fopen($file_path.'/'.$withoutExt.".txt","a");
            fwrite($fp,$content);
            //  fwrite($fp,$date);
            $log_serial_no++;
        }
        
//     echo "Processed rows: $start to $end <br>";
//     flush();
// }
        echo "</table>";
        //$sheet_name = 'MITWPUData_'. date('Y_m_d_H_i_s').'.xlsx'; 
    
        //return Excel::download(new MITWPUDataExport($result),$sheet_name,'Xlsx');
            
    }
    

    public function addRecord(Request $request){
     

        ini_set('max_execution_time', '600');
        ini_set('max_input_time', '300');
        ini_set('memory_limit', '4096M');

        // dd(1);
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        //  CoreHelper::checkContactAddress(2,$templateType='PDF2PDF');
        // exit;
        // $filename="mitwe_degree_new.xlsx";
        $filename="Database_Part3.xlsx";
        

        // 1 => Degree
        // 2 => PHD
        // 3 => Rank
        // 4 => Medal
        $template_type_format= '1';

        // $pathImport = public_path().'/'.$subdomain[0].'/blockchain/import/2024/';
        $pathImport = public_path().'/'.$subdomain[0].'/blockchain/import/2025/';
        $import_filename_import = $pathImport.$filename;
        // dd( $import_filename_import);
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($import_filename_import);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($import_filename_import);
        $sheet = $spreadsheet->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
        // dd($rowData);
        echo "<table>";
        $result=array();
        $template_id=1; //4 : Degree | 5 :Medal | 6:Rank | 7:PHD
        $admin_id = \Auth::guard('admin')->user()->toArray();
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        $template_data = TemplateMaster::select('id','template_name','is_block_chain','bc_document_description','bc_document_type','bc_contract_address')->where('id',$template_id)->first();
        // print_r($template_data);
        //exit;
        $template_type=1;
        $certificate_type="Degree Certificate";
        $log_serial_no=1;
        $withoutExt="Blockchain_".date('Y_m_d_h_i_s');
        // $date=date('dmYHis');
        // dd($highestRow);
        $date="240120253000";
        $start_row = 2;
        for($excel_row =2; $excel_row <=507; $excel_row++)//$highestRow
        {
            // echo $highestRow;
            $rowData1 = $sheet->rangeToArray('A'. $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);
            //Student ID for php and other
            $studentID = $rowData1[0][1];
            $studentIDMetadata = $rowData1[0][1];
            //For Rank / Medal with date
            //  $studentID = $rowData1[0][49];
            // echo"<pre>"; print_r($rowData1);
            //    exit;
            // echo"<pre>";
            //  print_r($studentID);
            // exit;
            //For  PHD /Degree Certiticate without date
            // $encryptedKey = strtoupper(md5($studentID.$date));
            $encryptedKey = $rowData1[0][20];
            //echo "<br>";
            //For Rank / Medal with date
            //$encryptedKey = strtoupper(md5($studentID.$date));
            //exit;
            $blockchainUrl=$rowData1[0][21];
            // $blockchainUrl=CoreHelper::getBCVerificationUrl(base64_encode($encryptedKey));
            //echo "<tr><td>".$studentID."</td><td>".$encryptedKey."</td><td>".$blockchainUrl."</td></tr>";
            //$result[] = array("student_id"=>$studentID,"encrypted_key"=>$encryptedKey,"blockchain_url"=>$blockchainUrl);
            //Blockchain fields
            //exit;
            //  $check_data = StudentTable::select('id')->where('serial_no',$studentID)->whereNotNull('bc_txn_hash')->first();
            // $check_data = StudentTable::select('id')->where('serial_no',$studentID)->where('status',1)->whereDate('created_at','2024-10-16')->whereNotNull('bc_txn_hash')->first();

            $check_data = StudentTable::where('serial_no',$studentID)->where('status',1)->where('template_id',$template_id)->first();

            //echo $check_data->certificate_filename;
            if($check_data){

                $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;
                if(!file_exists($pdf_path)){
                    echo "<tr><td>".$studentID."</td><td>".$encryptedKey."</td><td>".$blockchainUrl."</td></tr>";
                    echo "<tr><td>PDF Not Found</td></tr>";
                }
                //echo "<tr><td>".$studentID."</td><td>".$encryptedKey."</td><td>".$blockchainUrl."</td></tr>";

            }else{
                $admin_id = \Auth::guard('admin')->user()->toArray();  
                $datetime  = date("Y-m-d H:i:s");
                $ses_id  = $admin_id["id"];
                $sts = 1;
                $auth_site_id = '357';


                // $result = StudentTable::create(['serial_no'=>$studentID,'certificate_filename'=>$studentID.'.pdf','template_id'=>$template_id,'key'=>$encryptedKey,'path'=>'qr/'.$encryptedKey.'.png','created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id]);

                echo "<tr><td>".$studentID."</td><td>".$encryptedKey."</td><td>".$blockchainUrl."</td></tr>";

            }
            
            
            // if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            //     $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
            // }
            // else{
            //     $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
            // }
            //$fp = fopen($file_path.'/'.$withoutExt.".txt","a");
            //fwrite($fp,$content);
            //  fwrite($fp,$date);
            $log_serial_no++;
        }


        echo "</table>";
        //$sheet_name = 'MITWPUData_'. date('Y_m_d_H_i_s').'.xlsx'; 

        //return Excel::download(new MITWPUDataExport($result),$sheet_name,'Xlsx');
            
    }

    public function mintDataVerified() {
        ini_set('max_execution_time', '600');
        ini_set('max_input_time', '300');
        ini_set('memory_limit', '4096M');

        // Running code

        // $studentDataOrg = StudentTable::where('status',1)->orderBy('id','asc')->limit(5)->get();
        // $studentDataOrg = StudentTable::where('status',1)->orderBy('id','asc')->get();
        
        $studentDataOrg = StudentTable::where('status', 1)
        ->leftJoin('blockchain_other_data', 'student_table.id', '=', 'blockchain_other_data.student_table_id')
        ->whereNull('blockchain_other_data.token_id')
        ->orderBy('student_table.id', 'asc')
        ->select('student_table.*') // Select only fields from StudentTable
        // ->limit(80)
        ->get();


        // echo count($studentDataOrg);
        // die();

        $directoryUrlForward="C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
        $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";

        $pyscript = $directoryUrlBackward."Python_files\\fetch_token_script.py";            

        foreach($studentDataOrg as $studentData) {

            $output = '';
            // $txnHash = '0xdf942312510ff0ed8c631b0e4f165115ff4d3b242f3d2e8ebdf93215c6d51886';
            $txnHash = $studentData['bc_txn_hash'];
            $cmd =  "$pyscript $txnHash 2>&1";
            
            // echo $txnHash;
            // echo "<br>";
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

    public function OldmintData(Request $request){
    
    		$domain = \Request::getHost();
        	$subdomain = explode('.', $domain);
           //  CoreHelper::checkContactAddress(2,$templateType='PDF2PDF');
          // exit;
        	$filename="database_diploma_2024_2024_07_05.xlsx";
    		$pathImport = public_path().'/'.$subdomain[0].'/blockchain/import/';
			$import_filename_import = $pathImport.$filename;
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($import_filename_import);
			$reader->setReadDataOnly(true);
			$spreadsheet = $reader->load($import_filename_import);
			$sheet = $spreadsheet->getSheet(0);
			$highestColumn = $sheet->getHighestColumn();
			$highestRow = $sheet->getHighestRow();
			$rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);


			//$pathExport = public_path().'/'.$subdomain[0].'/blockchain/export/';
			//$import_filename_export = $pathImport.$filename;
			// $reader2 = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($import_filename_export);
			// //$reader->setReadDataOnly(true);
			// $spreadsheet2 = $reader2->load($import_filename_export);
			// $sheet2 = $spreadsheet2->getSheet(0);
			echo "<table>";
            $result=array();
            $template_id=2;
            $admin_id = \Auth::guard('admin')->user()->toArray();
            $auth_site_id=Auth::guard('admin')->user()->site_id;
             $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
             $template_data = TemplateMaster::select('id','template_name','is_block_chain','bc_document_description','bc_document_type','bc_contract_address')->where('id',$template_id)->first();
            // print_r($template_data);
            //exit;
            $template_type=1;
            $certificate_type="Degree Certificate";
            $log_serial_no=1;
            $withoutExt="Blockchain_".date('Y_m_d_h_i_s');
           // $date=date('dmYHis');

         
          //   $date="27102023130610";//Rank //Medal
             //$date="27102023134610";//PHD

             $date="05072024172610";//Diploma
            for($excel_row =2; $excel_row <=228; $excel_row++)//$highestRow
            {
               // echo $highestRow;
                $rowData1 = $sheet->rangeToArray('A'. $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);
                //Student ID for php and other
                $studentID = $rowData1[0][0];
                $studentIDMetadata = $rowData1[0][0];
                //For Rank / Medal with date
                //  $studentID = $rowData1[0][49];
                // echo $studentID;
                //    exit;

                //   print_r($rowData1[0]);
                //  exit;
                //For  PHD /Degree Certiticate without date
                // $encryptedKey = strtoupper(md5($rowData1[0][0]));
                //echo "<br>";
                //For Rank / Medal with date
                $encryptedKey = strtoupper(md5($studentID.$date));
                //exit;
                $blockchainUrl=CoreHelper::getBCVerificationUrl(encrypt($encryptedKey));
                echo "<tr><td>".$studentID."</td><td>".$encryptedKey."</td><td>".$blockchainUrl."</td></tr>";

                //$result[] = array("student_id"=>$studentID,"encrypted_key"=>$encryptedKey,"blockchain_url"=>$blockchainUrl);
                //Blockchain fields
                //exit;
                //  $check_data = StudentTable::select('id')->where('serial_no',$studentID)->whereNotNull('bc_txn_hash')->first();
                //$check_data = StudentTable::select('id')->where('serial_no',$studentID)->where('status',1)->whereDate('created_at','2024-01-10')->whereNotNull('bc_txn_hash')->first();
                $check_data=false;

                //   print_r($check_data);

                // echo  $check_data->id;
               
                // exit;
                //if(1==2){
                if(!$check_data){
                $certName = str_replace("/", "_", $studentID) .".pdf";
                $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\blockchain\\'.$certName;
                if(file_exists($pdf_path)){
                $mintData=array();
                $mintData['documentType']="Educational Document";
                $mintData['description']="Student ID :".$studentIDMetadata;
                $mintData['metadata1']=["label"=> "Student Name", "value"=> $rowData1[0][3]];
                $mintData['metadata2']=["label"=> "Competency Level", "value"=> $rowData1[0][11]];
                $mintData['metadata3']=["label"=> "Specialization", "value"=> $rowData1[0][14]];
                $mintData['metadata4']=["label"=> "CGPA", "value"=> $rowData1[0][17]];
                $mintData['metadata5']=["label"=> "Completion date", "value"=> $rowData1[0][23]];

                $mintData['uniqueHash']=$encryptedKey;
                
                $mintData['pdf_file']=$pdf_path;
                $mintData['template_id']=$template_id;
                $mintData['bc_contract_address']=$template_data['bc_contract_address'];
                 // print_r($mintData);
             
                 // exit;
             //   $response=CoreHelper::mintPDF($mintData);
             //  print_r($response);
           // exit;
                $response['status']=201;
                
                if($response['status']==200){
                $bc_txn_hash=$response['txnHash'];
                if(isset($response['ipfsHash'])){
                    $bc_ipfs_hash=$response['ipfsHash'];
                }else{
                    $bc_ipfs_hash=null;
                }
              //         echo "generate";
            //echo $bc_ipfs_hash;
            //echo "end";
                    /*Add data to student table and printing details*/
                    $student_table_id = $this->addCertificate($studentID, $certName, $template_id,$admin_id,$studentID,$template_type,$certificate_type,$bc_txn_hash,$bc_ipfs_hash,$encryptedKey);
                    $username = $admin_id['username'];
                    date_default_timezone_set('Asia/Kolkata');
                    $print_datetime = date("Y-m-d H:i:s");
                    $print_count = $this->getPrintCount($studentID);
                    $printer_name = $systemConfig['printer_name'];
                    $print_serial_no = $this->nextPrintSerial();
                    $template_name=$template_data['template_name'];
                    $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $studentID,$template_name,$admin_id,$student_table_id,$studentID);
                    /*End Add data to student table and printing details*/

                    $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Success".PHP_EOL;
                    
                }else{
                    $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Not deployed on blockchain network.".PHP_EOL;
                }
                }else{
                    $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Pdf not found.".PHP_EOL;
                }

                 }else{
                     $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Data found in student table.".PHP_EOL;
                 }
                
                   // $date = date('Y-m-d H:i:s').PHP_EOL;
                    

                    if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                        $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
                    }
                    else{
                        $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
                    }
                    $fp = fopen($file_path.'/'.$withoutExt.".txt","a");
                    fwrite($fp,$content);
                 //  fwrite($fp,$date);
                    $log_serial_no++;
            }
            echo "</table>";
			//$sheet_name = 'MITWPUData_'. date('Y_m_d_H_i_s').'.xlsx'; 
        
        	//return Excel::download(new MITWPUDataExport($result),$sheet_name,'Xlsx');


			
    }

    

    public function mintDataV1(Request $request){
        
        // ignore_user_abort(false);
        // die();
        // Allow long execution time
        set_time_limit(0);
        ini_set('max_execution_time', 0); // 0 = unlimited
        ini_set('max_input_time', 0);
        ini_set('memory_limit', '2048M'); // increase if needed

        // Optional: if you use large file uploads
        ini_set('upload_max_filesize', '256M');
        ini_set('post_max_size', '256M');
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        //  CoreHelper::checkContactAddress(2,$templateType='PDF2PDF');
        // exit;
        // $filename="mitwe_degree_new.xlsx";
        $filename="unikbp_excel.xlsx";
        
       

        $template_type_format= '1';

        // $pathImport = public_path().'/'.$subdomain[0].'/blockchain/import/2024/';
        $pathImport = public_path().'/'.$subdomain[0].'/blockchain/import/2025/';
        $import_filename_import = $pathImport.$filename;
        // dd( $import_filename_import);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($import_filename_import);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($import_filename_import);
        $sheet = $spreadsheet->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);

        echo "<table>";
        $result=array();
        $template_id=1;
        $admin_id = \Auth::guard('admin')->user()->toArray();
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        $template_data = TemplateMaster::select('id','template_name','is_block_chain','bc_document_description','bc_document_type','bc_contract_address')->where('id',$template_id)->first();
        // print_r($template_data);
        // exit;
        $template_type=1;
        //$certificate_type="Degree Certificate";
        $certificate_type="Certificate";
        $log_serial_no=1;
        $withoutExt="Blockchain_".date('Y_m_d_h_i_s');
        // $date=date('dmYHis');

        // $date="27102023130610";//Rank //Medal
        //$date="27102023134610";//PHD
        //$date="05072024172610";//Diploma
        $date="12032024183000";//Diploma
        $start_row = 292;
        $highestRow = 301; // 121
        for($excel_row =$start_row; $excel_row <=$highestRow; $excel_row++)//$highestRow
        {

            
            // echo $highestRow;
            $rowData1 = $sheet->rangeToArray('A'. $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);
            //Student ID for php and other
            $studentID = $rowData1[0][1];
            $studentIDMetadata = $rowData1[0][1];
            $serial_no = $rowData1[0][1];
            //For Rank / Medal with date
            
            // echo "<pre>";
            // print_r($rowData1);
            // die();

            // $encryptedKey

            // Get Student Data Using Sr No;
            $student_data  = StudentTable::where('serial_no',''.$serial_no)->where('status',1)->first();

            if(!$student_data) {
                continue;
            }

            if($student_data['bc_txn_hash'] != '' || $student_data['bc_txn_hash'] != null ) {
                echo "Allready Minted.";
                echo "<br>";
                continue;
            }

            $encryptedKey = $student_data['key'];//for Diploma
            
            $blockchainUrl=CoreHelper::getBCVerificationUrl(base64_encode(strtoupper(md5($encryptedKey))));
            $blockchainUrl1=$rowData1[0][21];
            

            $certName = $studentIDMetadata . ".pdf";

            if(1==1){
            // if(!$check_dataFlag&&!empty($encryptedKey)&&!empty($certName)){
                //$certName = str_replace("/", "_", $studentID) .".pdf";
                $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;
               

                if(file_exists($pdf_path)){
                    
                    $mintData=array();
                    $mintData['documentType']="Educational Document";
                    $mintData['description']="Description : PRN Number : ".$studentIDMetadata;
                    $mintData['metadata1']=["label"=> "Student Name", "value"=> $rowData1[0][3]];
                    $mintData['metadata2']=["label"=> "Faculty Name", "value"=> $rowData1[0][10]];
                    $mintData['metadata3']=["label"=> "Subject", "value"=> $rowData1[0][12]];
                    $mintData['metadata4']=["label"=> "Grade", "value"=> $rowData1[0][14]];
                    $mintData['metadata5']=["label"=> "Passing Month & Year", "value"=> $rowData1[0][16]];

                    $mintData['uniqueHash']=$encryptedKey;
                    
                    $mintData['pdf_file']=$pdf_path;
                    $mintData['template_id']=$template_id;

                    
                    $mintData['bc_contract_address']=$template_data['bc_contract_address'];
                    
                    
                    // echo "<pre>";
                    // print_r($rowData1[0]);
                    // echo "<br>";
                    // print_r($mintData);
                    // echo "</pre>";die;
                    
                    // exit;
                    // $template_type = 0;
                    // $blockchain_type = 1;
                    $template_type = 1;
                    $blockchain_type = 1;
                    $response=CoreHelper::customMintPDF($mintData,$blockchain_type,$template_type);

                    $bc_file_hash=CoreHelper::generateFileHash($pdf_path);

                    // $response=CoreHelper::mintPDF($mintData);
                    // print_r($response);
                    // echo "<br>";
                   //$response['status']=201;
                    
                    if($response['status']==200){
                        

                        $bc_txn_hash=$response['txnHash'];
                        $bc_sc_id=$response['bc_sc_id'];
                        $metadata_ipfs_hash = $response['metadata_ipfs_hash'];
                        $tokenId = $response['token_id'];
                        if(isset($response['ipfsHash'])){
                            $bc_ipfs_hash=$response['ipfsHash'];
                            $pinata_ipfs_hash=$response['pinataIpfsHash'];
                        }else{
                            $bc_ipfs_hash=null;
                            $pinata_ipfs_hash=null;
                            // $bc_sc_id=null;
                        }
                        // echo $studentIDMetadata;
                        // echo "<br>";
                        // echo $bc_sc_id;
                        // echo "<br>";
                        // echo $bc_file_hash;
                        // echo "<br>";
                        // echo "<br>";
                        // echo "<br>";
                        
                        // $resultu = StudentTable::where('serial_no',''.$serial_no)->update(['status'=>'0']);
                        // Insert the new record
                        $sts = '1';
                        $datetime  = date("Y-m-d H:i:s");
                        $ses_id  = $admin_id["id"];
                        $certName = str_replace("/", "_", $certName);
                        $fileName = $encryptedKey.'.png'; 
                        $urlRelativeFilePath = 'qr/'.$fileName; 

                        // $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$encryptedKey,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2,'certificate_type'=>'Degree Certificate' ,'bc_txn_hash'=>$bc_txn_hash,'bc_ipfs_hash'=>$bc_ipfs_hash,'pinata_ipfs_hash'=>$pinata_ipfs_hash,'bc_sc_id' => $bc_sc_id, 'bc_file_hash'=>$bc_file_hash ]);

                        $resultu = StudentTable::where('serial_no', (string)  $serial_no)
                            ->where('status', 1)
                            ->update([
                                'bc_txn_hash' => (string) $bc_txn_hash,
                                'bc_ipfs_hash' => (string) $bc_ipfs_hash,
                                'pinata_ipfs_hash' => $pinata_ipfs_hash,
                                'bc_sc_id' => $bc_sc_id,
                                'bc_file_hash' => $bc_file_hash,
                                'updated_at'=>$datetime
                            ]);

                        /*End Add data to student table and printing details*/

                        // vendor identifier
                        $studentData = StudentTable::where('serial_no',''.$serial_no)->where('status',1)->where('key',''.$encryptedKey)->first();
                        
                        $result = DB::table('blockchain_other_data')->updateOrInsert(
                            ['student_table_id' => $studentData['id']], // search condition
                            [                                           // values to update/insert
                                'bc_md_ipfs_hash'   => $metadata_ipfs_hash,
                                'token_id'   => $tokenId,
                                'vendor_identifier' => $blockchain_type
                            ]
                        );


                        $student_data  = StudentTable::where('serial_no',''.$serial_no)->where('status',1)->where('key',''.$encryptedKey)->first();
                
                        if($bc_sc_id && !empty($student_data)){
                            CoreHelper::updateContractCount($bc_sc_id,$student_data['id']);
                        }
                        
                        // if($s3Flag == 1 || $s3Flag == '1') {
                        //     unlink($pdf_path);
                        // }
                        
                        $content = "#" . $log_serial_no . 
                        " serial No :" . $studentIDMetadata . 
                        " | " . date('Y-m-d H:i:s') . 
                        " | Success" . 
                        " | Blockchain URL: " . $blockchainUrl . 
                        " | Blockchain URL My: " . $blockchainUrl1 . 
                        PHP_EOL;

                        // $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Success".PHP_EOL;
                
                    }else{
                        $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Not deployed on blockchain network.".PHP_EOL;
                    }
                }else{
                    $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Pdf not found.".PHP_EOL;
                }

                
            }else{
                    $content = "#".$log_serial_no." serial No :".$studentIDMetadata." | ".date('Y-m-d H:i:s')." | Data found in student table.".PHP_EOL;
            }
            
            echo $content;
            echo "<br>";
            // $date = date('Y-m-d H:i:s').PHP_EOL;
                
            gc_collect_cycles(); // clear PHP memory
            flush(); // send output

            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
            }
            else{
                $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
            }
            $fp = fopen($file_path.'/'.$withoutExt.".txt","a");
            fwrite($fp,$content);
            //  fwrite($fp,$date);
            $log_serial_no++;
        }
        echo "</table>";
        //$sheet_name = 'UNIKBPData_'. date('Y_m_d_H_i_s').'.xlsx'; 
    
        //return Excel::download(new UNIKBPDataExport($result),$sheet_name,'Xlsx');
            
    }

 
	public function check_file_exist($url){
	    $handle = @fopen($url, 'r');
	    if(!$handle){
	        return false;
	    }else{
	        return true;
	    }
	}
	public function addCertificate($serial_no, $certName, $template_id,$admin_id,$unique_no,$template_type,$certificate_type,$bc_txn_hash,$bc_ipfs_hash,$encryptedKey)
    {

       
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        

        $sts = 1;
        $datetime  = date("Y-m-d H:i:s");
        $ses_id  = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();
     
        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path().'/backend/qr';
       // $key = strtoupper(md5($unique_no));
       $key=$encryptedKey; 
        
        $codeContents = $key;
        $fileName = $key.'.png'; 
        
        $urlRelativeFilePath = 'qr/'.$fileName; 
        $auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        // Mark all previous records of same serial no to inactive if any
        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            $resultu = SbStudentTable::where('serial_no',$unique_no)->update(['status'=>'0']);
            // Insert the new record

            $result = SbStudentTable::create(['serial_no'=>$unique_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id]);
        }
        else{

            $check_data = StudentTable::select('id')->where('serial_no',$unique_no)->where('status',1)->whereNotNull('bc_txn_hash')->first();
            // $check_data = StudentTable::select('id')->where('serial_no',$studentID)->first();
            if($check_data){
                $resultu = StudentTable::where('id',$check_data->id)->update(['status'=>'0']);
             }
            
            // Insert the new record

            $result = StudentTable::create(['serial_no'=>$unique_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>$template_type,'certificate_type'=>$certificate_type,'bc_txn_hash'=>$bc_txn_hash,'bc_ipfs_hash'=>$bc_ipfs_hash]);
          //  echo "addCertificate";
           // echo $bc_ipfs_hash;
        }

        return $result['id'];
    }
    public function getPrintCount($serial_no)
    {
        $numCount = PrintingDetail::select('id')->where('sr_no',$serial_no)->count();
        return $numCount + 1;
    }
    public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no,$template_name,$admin_id,$student_table_id,$unique_no)
    {
        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];
        $auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            // Insert the new record
            $result = SbPrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>$unique_no,'template_name'=>$template_name,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);
        }
        else{
            // Insert the new record
            $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>$unique_no,'template_name'=>$template_name,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1,'student_table_id'=>$student_table_id]);
        }
    }
    public function nextPrintSerial()
    {
        $current_year = 'PN/' . $this->getFinancialyear() . '/';
        // find max
        $maxNum = 0;

        $result = \DB::select("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num "
            . "FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '$current_year'");
        
        //get next num
        $maxNum = $result[0]->next_num + 1;

        return $current_year . $maxNum;
    }
    public function getFinancialyear()
    {
        $yy = date('y');
        $mm = date('m');
        $fy = str_pad($yy, 2, "0", STR_PAD_LEFT);
        if($mm > 3)
            $fy = $fy . "-" . ($yy + 1);
        else
            $fy = str_pad($yy - 1, 2, "0", STR_PAD_LEFT) . "-" . $fy;
        return $fy;
    }
}

