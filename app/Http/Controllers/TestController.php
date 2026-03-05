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

//use Illuminate\Support\Facades\Mail;
use App\Exports\TemplateMasterExport;
use App\Jobs\SendMailJob;

use App\models\IdCardStatus;
use App\models\TemplateMaster;
use App\models\BackgroundTemplateMaster;
use App\models\FieldMaster;
use App\models\FontMaster;
use App\models\SystemConfig;
use App\models\Config;
use App\models\StudentTable;

use App\models\PrintingDetail;
use App\models\SbStudentTable;
use App\models\SbPrintingDetail;
use App\models\Site;

use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use Illuminate\Support\Facades\Log;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
use App\models\Demo\Site as DemoSite;
use App\Utility\BlockChain;
use App\Mail\SendSummaryBlockChainV1;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\CoreHelper;
use Illuminate\Support\Facades\File;

class TestController extends Controller
{
    public function anuCopyPdfs()
    {
        // Source base path
        $sourceBasePath = public_path('anu/backend/pdf_file'); 
        // OR storage_path('app') if stored in storage

        // Destination folder
        $destinationPath = public_path('anu/backend/anu_pdf_file');

        // Create destination folder if not exists
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        $records = DB::table('student_table')
            ->whereYear('created_at', 2025)
            ->whereNotNull('bc_txn_hash')
            ->where('status', 1)
            ->where('publish', 1)
            ->where('site_id', 284)
            ->get();
            dd($records);
        foreach ($records as $row) {

            // Full source file path
            $sourceFile = $sourceBasePath . '/' . $row->certificate_filename;

            // Only process PDFs
            if (File::exists($sourceFile) && strtolower(File::extension($sourceFile)) === 'pdf') {

                // Keep original filename
                $fileName = basename($sourceFile);

                // Destination file path
                $destinationFile = $destinationPath . '/' . $fileName;

                // Copy file
                File::copy($sourceFile, $destinationFile);
                echo $row->certificate_filename ."<br>";
            }
        }

        
    }
    public function todayRnd(){
        
        
        // $ftpHost = \Config::get('constant.monad_ftp_host');
        // $ftpPort = \Config::get('constant.monad_ftp_port');
        // $ftpUsername = \Config::get('constant.monad_ftp_username');
        // $ftpPassword = \Config::get('constant.monad_ftp_pass');        
       
        // // open an FTP connection
        // $connId = ftp_connect($ftpHost,$ftpPort) or die("Couldn't connect to $ftpHost");
        // // login to FTP server
        // $ftpLogin = ftp_login($connId, $ftpUsername, $ftpPassword);
        // if (!$ftpLogin) {
        //   echo 'Login Failed';
        // } else {
        //   echo 'Login Success';
        //   echo "<br>";
        // }
        ini_set('upload_max_filesize', '1024M');
        ini_set('post_max_size', '1024M');
        ini_set('max_execution_time', '900');
        ini_set('max_input_time', '900');
        ini_set('memory_limit', -1);
        error_reporting(E_ALL ^ E_NOTICE);
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        // echo $subdomain[0].'04';
        
        $admin_id = \Auth::guard('admin')->user()->toArray();
        $targetPath = public_path() . '/' . $subdomain[0];
        $inputFile = $targetPath . '/blockchain/import/2025/unikbp_excel.xlsx';

        // $inputFile = "C:\Users\ABC\Downloads\database_01_12_2025.xlsx";
        $objReader = IOFactory::createReader('Xlsx');
        $spreadsheetInput = $objReader->load($inputFile);
        $sheetInput = $spreadsheetInput->getSheet(0);

        $highestColumn = $sheetInput->getHighestColumn();
        $highestRow = $sheetInput->getHighestDataRow();
        $rowData = $sheetInput->rangeToArray("A1:{$highestColumn}{$highestRow}", null, true, false);
        $headers = array_shift($rowData);

        // New Excel creation
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers in new sheet
        $sheet->setCellValue('A1', 'Serial No');
        $sheet->setCellValue('B1', 'Status');

        // Write data rows
        // echo count($rowData);
        // echo "<br>";
        $rowIndex = 2;
        foreach ($rowData as $row) {

            // echo "<pre>";
            // print_r($row);
            // echo "<br>";
            // die();

            $serial_no = $row[1];
        

            echo $serial_no;
            echo "<br>";
            $certName = $serial_no.'.pdf';

            // $path = 'D:/unikbp/output_pdfs_unikbp/';

            // $fullPath = $path.$pdfName;

            $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;
            if (file_exists($pdf_path)) {
                echo "File exists";
            } else {
                echo "File does not exist";
            }
            echo "<br>";


            // $student = StudentTable::where('serial_no', $serial_no)->where('status', 1)->first();
            // if ($student) {
            //     $status = $student->status == 1 ? 'Active' : 'Inactive';
            //     echo "Student found for Serial No: " . $serial_no;
            //     echo "<br>";


                

            // } else {
            //     echo "Student not found for Serial No: " . $serial_no;
            //     echo "<br>";
            //     $status = 'Not Found';
            //     // $certName = $serial_no . '.pdf';
            //     // $sts = '1';
            //     // $datetime = date("Y-m-d H:i:s");
            //     // $ses_id = $admin_id["id"];
            //     // $certName = str_replace("/", "_", $certName);
            //     // $template_id = 100;
            //     // $key = $row[7];
            //     // $fileName = $key . '.png';
            //     // $urlRelativeFilePath = 'qr/' . $fileName;
            //     // $auth_site_id = '284';
                
            //     // $result = StudentTable::create(['serial_no' => $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id,'template_type'=>2,'certificate_type'=>'Certificate']);


            //     // echo "Data Inserted for Serial No: " . $serial_no;

            // }


            // $resultu = StudentTable::where('serial_no',$serial_no)->update(['status'=>'0']);

            // $certName = $serial_no . '.pdf';
            // $sts = '1';
            // $datetime = date("Y-m-d H:i:s");
            // $ses_id = $admin_id["id"];
            // $certName = str_replace("/", "_", $certName);
            // $template_id = 1;
            // $key = strtoupper($row[20]);

            // $fileName = $key . '.png';
            // $urlRelativeFilePath = 'qr/' . $fileName;
            // $auth_site_id = '357';
            
            // $result = StudentTable::create(['serial_no' => $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id,'template_type'=>0,'certificate_type'=>'Certificate']);
            
            // echo "Done Inserted";


            // $sheet->setCellValue('A'.$rowIndex, $serial_no);
            // $sheet->setCellValue('B'.$rowIndex, $status);

            // break;


           
            $rowIndex++;
        }

        // // Save new Excel file
        // $outputFile = $targetPath . "/unikbp_excel_updated_data_" . time() . ".xlsx";
        // $writer = new Xlsx($spreadsheet);
        // $writer->save($outputFile);

        // echo "File created: " . $outputFile;
        // echo count($rowData);
        // echo "<br>";
        // echo "<pre>";
        // print_r($rowData);
        die();




        // $students = StudentTable::where('status', 1)
        // ->whereNotNull('bc_txn_hash')
        // ->whereNull('bc_file_hash')
        // ->orderBy('id', 'DESC')
        // ->get();

        // foreach ($students as $student) {

        //     $certName = $student->certificate_filename;   // CHANGE: use your correct column
        //     $serial_no = $student->serial_no;
        //     echo $serial_no;
        //     echo "<br>";

        //     // Correct PDF path
        //     $pdf_path = public_path($subdomain[0] . '/backend/pdf_file/' . $certName);

        //     if (!file_exists($pdf_path)) {
        //         // Log error or skip
        //         echo "PDF not found for Serial No: " . $serial_no;
        //         echo "<br>";
        //         continue;
        //     }

        //     // Generate hash
        //     $bc_file_hash = CoreHelper::generateFileHash($pdf_path);

        //     // Update student record
        //     StudentTable::where('serial_no', (string) $serial_no)
        //         ->where('status', 1)
        //         ->update([
        //             'bc_file_hash' => $bc_file_hash,
        //         ]);
        // }

        // StudentTable::where('status', 1)
        // ->whereNotNull('bc_txn_hash')
        // ->whereNull('bc_file_hash')
        // ->orderBy('id', 'DESC')
        // ->chunkById(200, function ($students) use ($subdomain) {

        //     foreach ($students as $student) {

        //         $certName  = $student->certificate_filename;
        //         $serial_no = $student->serial_no;

        //         echo $serial_no . "<br>";

        //         // Build correct path
        //         // https://securedoc.s3.ap-south-1.amazonaws.com/public/mitwpu/backend/pdf_file/1032212068.pdf

        //         $pdf_path = public_path($subdomain[0] . '/backend/pdf_file/' . $certName);
        //         // $pdf_path = 'https://securedoc.s3.ap-south-1.amazonaws.com/public/mitwpu/backend/pdf_file/' . $certName;

        //         // $headers = @get_headers($pdf_path);

        //         // if (!$headers || strpos($headers[0], '404') !== false) {
        //         //     echo "PDF not found for Serial No: " . $serial_no . "<br>";
        //         //     continue;
        //         // }

        //         if (!file_exists($pdf_path)) {
        //             echo "PDF not found for Serial No: " . $serial_no . "<br>";
        //             continue;
        //         }


        //         // Generate hash
        //         $bc_file_hash = CoreHelper::generateFileHash($pdf_path);

        //         // $s3 = \Storage::disk('s3');

        //         // $key = 'public/mitwpu/backend/pdf_file/' . $certName;

        //         // // 1. Check file exists in S3
        //         // if (!$s3->exists($key)) {
        //         //     echo "PDF not found in S3 for Serial No: $serial_no<br>";
        //         //     continue;
        //         // }

        //         // // 2. Read file content from S3
        //         // $fileContent = $s3->get($key);

        //         // $bc_file_hash = hash_hmac('sha3-256', $fileContent, \Config::get('constant.EStamp_Salt'));

        //         // Update single record
        //         StudentTable::where('id', $student->id)->update([
        //             'bc_file_hash' => $bc_file_hash
        //         ]);
        //     }

        // });

        
        die();


        // $pdf_path=public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;
        // $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;
           
        // $bc_file_hash=CoreHelper::generateFileHash($pdf_path);

        // $serial_no = "";

        // $resultu = StudentTable::where('serial_no', (string)  $serial_no)
        //     ->where('status', 1)
        //     ->update([ 
        //         'bc_file_hash' => $bc_file_hash,
        //     ]);



        // $targetPath = public_path() . '/' . $subdomain[0];
        // $inputFile = $targetPath . '/image_check_medal.xlsx';
        // $objReader = IOFactory::createReader('Xlsx');
        // $spreadsheetInput = $objReader->load($inputFile);
        // $sheetInput = $spreadsheetInput->getSheet(0);

        // $highestColumn = $sheetInput->getHighestColumn();
        // $highestRow = $sheetInput->getHighestDataRow();
        // $rowData = $sheetInput->rangeToArray("A1:{$highestColumn}{$highestRow}", null, true, false);
        // $headers = array_shift($rowData);

        // // print_r(count($rowData));

        // $basePath = public_path('mitwpu/backend/students/');
        // $extensions = ['jpg', 'png', 'jpeg'];
        
        // foreach ($rowData as $index => $row) {
        //     if ($index == 0) continue; // Skip header row

        //     $prn = trim($row[0]);

        //     $existingImage = null;

        //     foreach ($extensions as $ext) {
        //         $filePath = $basePath . $prn . '.' . $ext;
        //         if (file_exists($filePath)) {
        //             $existingImage = $prn . '.' . $ext;
        //             break;
        //         }
        //     }

        //     echo $prn;
        //     echo "<br>";
        //     echo $existingImage;
        //     echo "<br>";
        //     echo "<br>";


        // }





        // die();

        // dd(DB::connection()->getDatabaseName());

        // $siteData = Site::on('mysql')
        //     // ->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'), $subdomain[0])
        //     ->get();
        // $siteData = DB::connection('mysql_old')
        //     ->table('seqr_demo.sites')
        //     ->where(DB::raw("SUBSTRING_INDEX(site_url, '.', 1)"), $subdomain[0])
        //     ->first();

        // if($siteData){
        //     $instance=explode('.', $siteData->site_url);
        //     if($instance[0]!='demo'&&$instance[0] != 'master'){
        //         $dbName = 'seqr_d_'.$instance[0];
        //     }else{
        //         $dbName = 'seqr_demo';
        //     }
        //     $dbCred = '';
        //     if($siteData->new_server == 0) {
        //         $dbCred = 'mysql_old';
        //     } else {
        //         $dbCred = 'mysql_new';
        //     }

        //     // Define paths
        //     $inputFileType = 'Xlsx';
        //     $targetPath = public_path() . '/' . $subdomain[0] . '/backend/excel_file';
        //     // $targetPath = public_path() . '/' . $subdomain[0] . '/blockchain';
        //     $inputFile = $targetPath . '/Mangalyatan_2909_QR Data.xlsx';

        //     // Load existing Excel
        //     $objReader = IOFactory::createReader($inputFileType);
        //     $spreadsheetInput = $objReader->load($inputFile);
        //     $sheetInput = $spreadsheetInput->getSheet(0);

        //     $highestColumn = $sheetInput->getHighestColumn();
        //     $highestRow = $sheetInput->getHighestDataRow();

        //     // Read data
        //     $rowData = $sheetInput->rangeToArray("A1:{$highestColumn}{$highestRow}", null, true, false);
        //     $headers = array_shift($rowData); // Get headers from first row (A1)

        //     // New Excel creation
        //     // $spreadsheet = new Spreadsheet();
        //     // $sheet = $spreadsheet->getActiveSheet();

        //     // // Set headers in new sheet
        //     // $sheet->setCellValue('A1', 'GUID');
        //     // $sheet->setCellValue('B1', 'Status');

        //     // Write data rows
        //     $rowIndex = 2;

        //     // $batchSize = 10; // adjust based on memory and performance
        //     // $totalRows = count($rowData);
        //     // $batches = ceil($totalRows / $batchSize);
        //     // for ($batch = 0; $batch < $batches; $batch++) {

        //     //     $offset = $batch * $batchSize;
        //     //     $batchData = array_slice($rowData, $offset, $batchSize);

        //         // foreach ($batchData as $row) {
        //         $admin_id = \Auth::guard('admin')->user()->toArray();
        //         $auth_site_id=\Auth::guard('admin')->user()->site_id;
        //         $template_id = 100;

        //         $start_row = 3;
        //         $highestRow = 2910; // 

        //         for ($excel_row = $start_row; $excel_row <= $highestRow; $excel_row++) {
        //             // foreach ($rowData as $row) {
                    
        //             // $studentData = StudentTable::where('serial_no',''.$row[1])->first();
        //             // Get the entire row data as an array
        //             $rowData = $sheetInput->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);
        //             $row = $rowData[0]; // rangeToArray returns a nested array
                    
        //             $studentData = DB::connection($dbCred)->table($dbName.".student_table")->where('serial_no',$row[0])->first('status');
        //             if($studentData){
        //                 $status = $studentData->status == 1 ? 'Active' : 'Inactive';
        //             } else {
        //                 $status = 'Not Found';
        //             }

        //             // $sheet->setCellValue("A{$rowIndex}", $row[2] ?? '');
        //             // $sheet->setCellValue("A{$rowIndex}", $status ?? '');
        //             // $student_data = DB::connection($dbCred)->table($dbName.".student_table")->where('serial_no',$row[0])->where('status',1)->first('status');
        //             // // // $student_data  = StudentTable::where('serial_no',''.$row[0])->where('status',1)->first();

        //             // if($student_data) {
        //             //     echo "Data Exist Already";
        //             //     echo "<br>";
        //             //     break;
        //             // }

        //             echo $row[0];
        //             echo " - ";
        //             echo $status;
        //             echo "<br>";

        //             // echo "<pre>";
        //             // print_r($row);
        //             // die();

        //             $serial_no = $row[0];

        //             $certName = $row[0].'.pdf';
                    
        //             $key = $row[1];
                    
        //             $fileName = $key.'.png'; 
            
        //             $urlRelativeFilePath = 'qr/'.$fileName; 

        //             // $auth_site_id = '289';
        //             $sts = '1';
        //             $datetime  = date("Y-m-d H:i:s");
        //             $ses_id  = $admin_id["id"];

                    

        //             $certName = str_replace("/", "_", $certName);
                    

        //             // $pdfActualPath=public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;
        //             // $pdf_path=public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;
        //             // $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;


        //             // if(!file_exists($pdf_path)){
        //             //     echo "PDF Path is not found";
        //             //     echo "<br>";
        //             //     break;
        //             // }

        //             // $full_name = $row[3];
        //             // $faculty_name = $row[5];
        //             // $specialization = $row[7];
        //             // $cgpa = $row[8];
        //             // $completion_date = $row[14];

        //             // $mintData=array();
        //             // $mintData['documentType']="Educational Document";
        //             // $mintData['description']="Student ID :".$serial_no;
        //             // $mintData['metadata1']=["label"=> "Student Name", "value"=> $full_name];
        //             // $mintData['metadata2']=["label"=> "Competency Level", "value"=> $faculty_name];
        //             // $mintData['metadata3']=["label"=> "Specialization", "value"=> $specialization];
        //             // $mintData['metadata4']=["label"=> "CGPA", "value"=> $cgpa];
        //             // $mintData['metadata5']=["label"=> "Completion date", "value"=> $completion_date];

        //             // $mintData['template_id']=$key;
        //             // $mintData['uniqueHash']=$key;

        //             // echo "<pre>";
        //             // print_r($mintData);
        //             // die();

        //             // $template_type = 2;
        //             // $blockchain_type = 1;
        //             // $response=CoreHelper::customMintPDF($mintData,$blockchain_type,$template_type);
                    
                    
                   
        //             // $bc_file_hash=CoreHelper::generateFileHash($pdf_path);

        //             // if($response['status']==200){
                        

        //                 // $bc_txn_hash=$response['txnHash'];
        //                 // $bc_sc_id=$response['bc_sc_id'];
        //                 // $metadata_ipfs_hash = $response['metadata_ipfs_hash'];
        //                 // $tokenId = $response['token_id'];
        //                 // if(isset($response['ipfsHash'])){
        //                 //     $bc_ipfs_hash=$response['ipfsHash'];
        //                 //     $pinata_ipfs_hash=$response['pinataIpfsHash'];
        //                 // }else{
        //                 //     $bc_ipfs_hash=null;
        //                 //     $pinata_ipfs_hash=null;
        //                 //     // $bc_sc_id=null;
        //                 // }


        //                 $sts = '1';
        //                 $datetime  = date("Y-m-d H:i:s");
        //                 $ses_id  = $admin_id["id"];
        //                 $certName = str_replace("/", "_", $certName);
        //                 $fileName = $key.'.png'; 
        //                 $urlRelativeFilePath = 'qr/'.$fileName; 

        //                 $resultu = StudentTable::where('serial_no',''.$serial_no)->update(['status'=>'0']);
        //                 // Insert the new record
        //                 $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>0,'certificate_type'=>'Certificate']);
                        
        //                 // $resultu = StudentTable::where('serial_no', (string)  $serial_no)
        //                 //     ->where('status', 1)
        //                 //     ->update([
        //                 //         'bc_txn_hash' => (string) $bc_txn_hash,
        //                 //         'bc_ipfs_hash' => (string) $bc_ipfs_hash,
        //                 //         'pinata_ipfs_hash' => $pinata_ipfs_hash,
        //                 //         'bc_sc_id' => $bc_sc_id,
        //                 //         'bc_file_hash' => $bc_file_hash,
        //                 //         'updated_at'=>$datetime
        //                 //     ]);

        //                 // vendor identifier
        //                 // $studentData = StudentTable::where('serial_no',''.$serial_no)->where('status',1)->where('key',''.$key)->first();
                        
        //                 // $result = DB::table('blockchain_other_data')->updateOrInsert(
        //                 //     ['student_table_id' => $studentData['id']], // search condition
        //                 //     [                                           // values to update/insert
        //                 //         'bc_md_ipfs_hash'   => $metadata_ipfs_hash,
        //                 //         'token_id'   => $tokenId,
        //                 //         'vendor_identifier' => $blockchain_type
        //                 //     ]
        //                 // );


        //                 // $student_data  = StudentTable::where('serial_no',''.$serial_no)->where('status',1)->where('key',''.$encryptedKey)->first();
                
        //                 // if($bc_sc_id && !empty($student_data)){
        //                 //     CoreHelper::updateContractCount($bc_sc_id,$student_data['id']);
        //                 // }

        //             // }
                    

                    
                    
                    
                    


        //             // // vendor identifier
        //             // $studentData = StudentTable::where('serial_no', $serial_no)->where('status', 1)->first();
                    
        //             // $result = DB::table('blockchain_other_data')->updateOrInsert(
        //             //     ['student_table_id' => $studentData['id']], // search condition
        //             //     [                                           // values to update/insert
        //             //         'bc_md_ipfs_hash'   => $metadata_ipfs_hash,
        //             //         'vendor_identifier' => $blockchain_type
        //             //     ]
        //             // );


        //             // $student_data  = StudentTable::where('serial_no',''.$serial_no)->where('status',1)->where('key',''.$key)->first();
            
        //             // if($bc_sc_id && !empty($student_data)){
        //             //     CoreHelper::updateContractCount($bc_sc_id,$student_data['id']);
        //             // }
                        

        //             echo $row[0]." - Data Inserted";
        //             echo "<br>";
        //             // $sheet->setCellValue("B{$rowIndex}", $status );
        //             // $rowIndex++;
        //         }

        //     //     // Optional: Free memory after each batch
        //     //     unset($batchData);
        //     //     gc_collect_cycles();

        //     //     // Optional: log progress
        //     //     echo "Processed batch " . ($batch + 1) . " of $batches" . PHP_EOL;
        //     // }
            
        //     // Save new Excel file
        //     // $outputFile = $targetPath . "/Mangalyatan_2909_QR Data_output_" . time() . ".xlsx";
        //     // $writer = new Xlsx($spreadsheet);
        //     // $writer->save($outputFile);
            
            
        // }
        // die();
        

        



        // Save new Excel file
        // $outputFile = $targetPath . "/KAJIADO_EAST_BATCH_4_updated_data_" . time() . ".xlsx";
        // $writer = new Xlsx($spreadsheet);
        // $writer->save($outputFile);



        // die();
        // $blockchainInstance = 'mitwpu';
        // $sitesData = DB::connection('mysql')->table("seqr_demo.sites")->whereNotNull('bc_wallet_address')->where('status',1)->get()->toArray();
        // $siteData = Site::on('mysql')->select('new_server')->where(DB::raw('SUBSTRING_INDEX(site_url_, ".", 1)'),$blockchainInstance)->first();
        
        
        
        // $blockchainInstance = 'mitwpu,anu';
        $blockchainInstance = 'mitwpu';
        
        // // Convert string to array
        $instances = explode(',', $blockchainInstance);

        // $sitesData = Site::on('mysql')
        //     ->select('new_server','site_url')
        //     ->whereIn(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'), $instances)
        //     ->get();

        $sitesData = DB::connection('mysql')->table("seqr_demo.sites")->select('new_server','site_url')->whereIn(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'), $instances)->get();
        //     echo "<pre>";
        //     print_r($siteData);
        // die();
        if($sitesData){
            
            foreach ($sitesData as $readData) {
                
                // $domain = \Request::getHost();
                // $subdomain = explode('.', $domain);

                $instance=explode('.', $readData->site_url);
                if($instance[0]!='demo'&&$instance[0] != 'master'){
                    $dbName = 'seqr_d_'.$instance[0];
                }else{
                    $dbName = 'seqr_demo';
                }

                $dbCred = '';
                if($readData->new_server == 0) {
                    $dbCred = 'mysql';
                } else {
                    $dbCred = 'mysql_new';
                }
                
                
                $studentData = DB::connection($dbCred)
                    ->table($dbName . ".student_table")
                    ->where('created_at', '>=', '2025-07-17');

                if ($instance[0] == 'mitwpu') {
                    $studentData->where('created_at', '<=', '2025-09-23');
                }

                $studentData = $studentData
                    ->where('status', 1)
                    ->whereNotNull('bc_txn_hash')
                    ->get()
                    ->toArray();

                // echo "<pre>";
                // print_r($studentData);
                // die();

                if($studentData){
                    $i= 1;
                    
                    foreach ($studentData as $eachRow) {
                        # code...
                        $filePath = public_path() . "\\" . $instance[0] . "\\backend\\pdf_file\\" . $eachRow->certificate_filename;

                        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                        $path = $protocol.'://'.$instance[0].'.'.$instance[1].'.com/';
                        $pdf_url=$path.$instance[0]."/backend/pdf_file/".$eachRow->certificate_filename;

                        echo $filePath;
                        echo "<br>";
                        echo $pdf_url;
                        echo "<br>";
                        echo "Total Processed Records ".$i;
                        echo "<br>";
                        if(file_exists($filePath)){

                            // $response2=CoreHelper::generateFileHash($filePath);
                            // DB::connection($dbCred)->statement("UPDATE `".$dbName."`.`student_table` 
                            //     SET `bc_file_hash`='".$response2."' WHERE `id`=".$eachRow->id);
                        }
                        $i++;
                        
                    }
                }
            }
        } else {
            echo "No data found";
        }



        die();
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        $get_template_data = TemplateMaster::select('template_name','id','actual_template_name')->where('status',1)->orderBy('template_name','asc')->where('site_id',$auth_site_id)->get()->toArray();
        
        // $get_template_data = DB::table('uploaded_pdfs')
        // ->select('template_name', 'id')
        // ->where('map_type','=',1)
        // ->orderBy('template_name','asc')
        // ->get()->toArray();

        // foreach ($get_template_data as $template_key => $template_value) {
            
        //     // Folder path
        //     // echo $template_value->template_name;
        //     // die();
        //     $template_value['id'] = '237';
        //     $folderPath = public_path($subdomain[0].'/backend/templates/'.$template_value['id']);

        //     // echo $template_value['id'];
        //     // die();
        //     // Get all image files (case-insensitive)
        //     $images = File::glob($folderPath.'/*.{jpg,png,JPG,PNG,JPEG,jpeg}', GLOB_BRACE);

        //     if (!empty($images)) {
        //         foreach ($images as $image) {
        //             $fileName = basename($image);

        //             // Insert into DB (example table: dynamic_image_managemant)
        //             DB::table('dynamic_image_managemant')->insert([
        //                 'template_id' => $template_value['id'],
        //                 'filename'   => $fileName,
        //                 'map_type' => 0,
        //                 'created_at'  => now(),
        //                 'updated_at'  => now(),
        //             ]);
        //         }
        //     }
            
        // }


        $get_template_data = DB::table('uploaded_pdfs')
            ->select('template_name', 'id')
            ->where('map_type', '=', 1)
            ->orderBy('template_name', 'asc')
            ->get()
            ->toArray();

        foreach ($get_template_data as $template_value) {

            // Example override for testing
            // $template_value->id = 237;

            $folderPath = public_path($subdomain[0] . '/backend/templates/excel2pdf/' . $template_value->id);

            // Get all image files
            $images = File::glob($folderPath . '/*.{jpg,png,JPG,PNG,JPEG,jpeg}', GLOB_BRACE);

            if (!empty($images)) {
                foreach ($images as $image) {
                    $fileName = basename($image);

                    // Check if already exists
                    $exists = DB::table('dynamic_image_managemant')
                        ->where('template_id', $template_value->id)
                        ->where('filename', $fileName)
                        ->exists();

                    if (!$exists) {
                        DB::table('dynamic_image_managemant')->insert([
                            'template_id' => $template_value->id,
                            'filename'    => $fileName,
                            'map_type'    => 1,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }
                }
            }
        }

        die();

        // Martin Mathias Dome
        // BE 21001786
        // Bachelor of Engineering
        // Information Technology
        // 4.06

        // $codeContents = 'Martin Mathias Dome';
        // $codeContents .="\n";
        // $codeContents .= 'BE 2505012';
        // $codeContents .="\n";
        // $codeContents .= 'Bachelor of Engineering';
        // $codeContents .="\n";
        // $codeContents .= 'Information Technology';
        // $codeContents .="\n";
        // $codeContents .= '4.06';
        // $codeContents .="\n\n";
        // $codeContents .= 'https://demo.seqrdoc.com/bverify/OEQyQTcwMzg5QTIyNjQxMTI4MkUyMzJFNkJCQ0QzOEY=';
        // $codeContents .="\n\n";
        // $codeContents .= '8D2A70389A226411282E232E6BBCD38F';


        // $qr_code_path = public_path().'\\'.$subdomain[0].'\sample_qr.png';

        // $ecc = 'L';
        // $pixel_Size = 1;
        // $frame_Size = 1;  
        // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
        die();
        
        $pyscript = "C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\excel2pdf\\Python_files\\apply_pantone_overlay_pdf_oldenv.py";
        $cmd = "$pyscript 2>&1";

        echo "Python ".$cmd;
        echo "<br>";
        //exec($cmd, $output, $return);
        exec('C:/Inetpub/vhosts/seqrdoc.com/httpdocs/pdf2pdf/pdf_env/Scripts/python.exe '.$cmd, $output, $return);

        print_r($output);
        die();
        // \DB::disconnect('mysql');

        // \Config::set("database.connections.mysql", [
        //     'driver'   => 'mysql',
        //     'host'     => 'seqrdoc.com',
        //     'port'     => '3306',
        //     'database' => 'seqr_d_kajido',
        //     'username' => 'developer',
        //     'password' => 'developer',
        //     'unix_socket' => '',
        //     'charset' => 'utf8mb4',
        //     'collation' => 'utf8mb4_unicode_ci',
        //     'prefix' => '',
        //     'prefix_indexes' => true,
        //     'strict' => true,
        //     'engine' => null,
        //     'options' => [],
        // ]);

        // \DB::reconnect();

        // $connection = \DB::connection();
        // $connectionName = $connection->getName();             // Should return 'mysql'
        // $databaseName = $connection->getDatabaseName();       // Should return 'seqr_d_kajido'

        // echo $connectionName . ' connection is successfully established with database: ' . $databaseName;

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        // Define paths
        $inputFileType = 'Xlsx';
        $targetPath = public_path() . '/' . $subdomain[0] . '/backend/excel_file';
        $inputFile = $targetPath . '/KAJIADO_EAST_BATCH_4.xlsx';

        // Load existing Excel
        $objReader = IOFactory::createReader($inputFileType);
        $spreadsheetInput = $objReader->load($inputFile);
        $sheetInput = $spreadsheetInput->getSheet(0);

        $highestColumn = $sheetInput->getHighestColumn();
        $highestRow = $sheetInput->getHighestDataRow();

        // Read data
        $rowData = $sheetInput->rangeToArray("A1:{$highestColumn}{$highestRow}", null, true, false);
        $headers = array_shift($rowData); // Get headers from first row (A1)

        // New Excel creation
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers in new sheet
        $sheet->setCellValue('A1', $headers[0] ?? 'Column 1');
        $sheet->setCellValue('B1', 'Updated ' . ($headers[1] ?? 'Column 2'));

        // Write data rows
        $rowIndex = 2;
        foreach ($rowData as $row) {
            $sheet->setCellValue("A{$rowIndex}", $row[0] ?? '');


            // $studentData = StudentTable::where('serial_no',''.$row[1])->first();
            $studentData = DB::connection('mysql')->table("seqr_d_kajido.student_table")->where('serial_no',$row[1])->first('status');
            if($studentData){
                $status = $studentData->status == 1 ? 'Active' : 'Inactive';
            } else {
                $status = 'Not Found';
            }

            $sheet->setCellValue("B{$rowIndex}", $status );
            $rowIndex++;
        }

        // Save new Excel file
        $outputFile = $targetPath . "/KAJIADO_EAST_BATCH_4_updated_data_" . time() . ".xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFile);

        
        
    }


    public function columInsert() {
        // file_records
        // upload_pdfs

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);


        // $data = DemoSite::select('site_url','new_server')->where('site_url',$domain)->get()->toArray();
        $data=DB::connection('mysql')->table("seqr_demo.sites")->select('site_url','new_server','site_id')->where('site_url',$domain)->get()->toArray();

        // print_r($data);
        // die();
        foreach($data as $key => $value){

            // print_r($value->site_id);
            // die();
            $site_id = $value->site_id;

            $dbName = explode('.', $value->site_url)[0];

            if($dbName == 'demo')
            {
                $dbName = 'seqr_'.$dbName;
            }else{

                $dbName = 'seqr_d_'.$dbName;
            }
            echo $value->new_server;

            echo "<br>";
            \DB::disconnect('mysql'); 

            if($value->new_server == 1) {

                \Config::set("database.connections.mysql", [
                    'driver'   => 'mysql',
                    'host'     => 'localhost',
                    "port" => '3306',
                    'database' => $dbName,
                    'username' => 'developer',
                    'password' => 'developer',
                    "unix_socket" => "",
                    "charset" => "utf8mb4",
                    "collation" => "utf8mb4_unicode_ci",
                    "prefix" => "",
                    "prefix_indexes" => true,
                    "strict" => true,
                    "engine" => null,
                    "options" => []
                ]);

            } else {
                \Config::set("database.connections.mysql", [
                  'driver'   => 'mysql',
                  // 'host'     => 'localhost',
                  'host'     => 'seqrdoc.com',
                  "port" => "3306",
                  'database' => $dbName,
                  'username' => 'developer',
                  'password' => 'developer',
                  "unix_socket" => "",
                  "charset" => "utf8mb4",
                  "collation" => "utf8mb4_unicode_ci",
                  "prefix" => "",
                  "prefix_indexes" => true,
                  "strict" => true,
                  "engine" => null,
                  "options" => []
                ]);
            
            }
            \DB::reconnect();

            
            echo $dbName;
            echo "<br>";

            // $tables = ['uploaded_pdfs', 'file_records'];
            $database = $dbName; // e.g., 'seqr_d_galgotias'

            try {
                // Check if database exists
                $dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$database]);

                if (!empty($dbExists)) {
                    // foreach ($tables as $table) {
                        // Check if table exists
                        $table = 'uploaded_pdfs';
                        $checkTable = DB::select("
                            SELECT TABLE_NAME 
                            FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                        ", [$dbName, $table]);

                        if (empty($checkTable)) {
                            DB::statement("
                                CREATE TABLE `uploaded_pdfs` (
                                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                    `file_name` varchar(191) DEFAULT NULL,
                                    `extractor_details` longtext DEFAULT NULL,
                                    `placer_details` longtext DEFAULT NULL,
                                    `ep_details` longtext DEFAULT NULL,
                                    `template_title` varchar(500) DEFAULT NULL,
                                    `template_name` varchar(191) DEFAULT NULL,
                                    `pdf_page` varchar(191) DEFAULT NULL,
                                    `print_bg_file` int(11) DEFAULT 0,
                                    `print_bg_status` varchar(191) DEFAULT 'No',
                                    `verification_bg_file` int(11) DEFAULT 0,
                                    `verification_bg_status` varchar(191) DEFAULT 'No',
                                    `generated_by` int(11) NOT NULL,
                                    `is_block_chain` int(11) NOT NULL DEFAULT 0,
                                    `bc_document_description` varchar(256) DEFAULT NULL,
                                    `bc_document_type` varchar(256) DEFAULT NULL,
                                    `bc_contract_address` varchar(256) DEFAULT NULL,
                                    `publish` int(11) DEFAULT 1,
                                    `map_type` tinyint(4) NOT NULL DEFAULT 0,
                                    `created_at` timestamp NULL DEFAULT NULL,
                                    `updated_at` timestamp NULL DEFAULT NULL,
                                    PRIMARY KEY (`id`)
                                )
                            ");
                            echo "Table `$table` does NOT exist in database `$dbName`.";
                        } else {
                            echo "Table `$table` exists in database `$dbName`.";
                        }

                        echo "<br>";


                        /*File Records table*/
                        $table1 = 'file_records';
                        $checkTable1 = DB::select("
                            SELECT TABLE_NAME 
                            FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                        ", [$dbName, $table1]);

                        if (empty($checkTable1)) {
                            DB::statement("
                                CREATE TABLE `file_records` (
                                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                    `template_id` int(11) DEFAULT NULL,
                                    `template_name` varchar(191) DEFAULT NULL,
                                    `pdf_page` varchar(191) DEFAULT NULL,
                                    `total_records` int(11) DEFAULT NULL,
                                    `pages_in_pdf` int(11) DEFAULT NULL,
                                    `source_file` text DEFAULT NULL,
                                    `userid` int(11) DEFAULT NULL,
                                    `record_unique_id` varchar(191) DEFAULT NULL,
                                    `created_at` timestamp NULL DEFAULT NULL,
                                    `updated_at` timestamp NULL DEFAULT NULL,
                                    `map_type` int(1) DEFAULT 0,
                                    PRIMARY KEY (`id`)
                                )
                            ");
                            echo "Table `$table1` does NOT exist in database `$dbName`.";
                        } else {
                            echo "Table `$table1` exists in database `$dbName`.";
                        }

                        
                } else {
                    echo "Database `{$database}` does not exist.<br>";
                }
            } catch (\Exception $e) {
                echo "Error: " . $e->getMessage();
            }

            // //Check if column already exists to avoid duplicate error
            // $columnExists = DB::select("SHOW COLUMNS FROM `".$dbName."`.`uploaded_pdfs` LIKE 'map_type'");
            // if (empty($columnExists)) {
            //     DB::statement("ALTER TABLE `".$dbName."`.`uploaded_pdfs` ADD `map_type` TINYINT(4) DEFAULT 0");
            // } else {
            //     echo "uploaded_pdfs Column 'status' already exists in table '$table'<br>";
            // }

            // $columnExists1 = DB::select("SHOW COLUMNS FROM `".$dbName."`.`file_records` LIKE 'map_type'");
            // if (empty($columnExists1)) {
            //     DB::statement("ALTER TABLE `".$dbName."`.`file_records` ADD `map_type` TINYINT(1) DEFAULT 0");
            // } else {
            //     echo "file _records Column 'status' already exists in table '$table'<br>";
            // }
            // echo "<br>";
            // die();

            // DB::statement("ALTER TABLE `".$dbName."`.`student_table` 
            //         ADD COLUMN `pinata_ipfs_hash` TEXT NULL 
            //         AFTER `bc_ipfs_hash`");



        }
        

        

    }
    
    public function columInsert_old() {
        // file_records
        // upload_pdfs

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);


        // $data = DemoSite::select('site_url','new_server')->where('site_url',$domain)->get()->toArray();
        $data=DB::connection('mysql')->table("seqr_demo.sites")->select('site_url','new_server')->where('site_url',$domain)->get()->toArray();
        foreach($data as $key => $value){

            $site_id = $value->site_id;

            $dbName = explode('.', $value->site_url)[0];

            if($dbName == 'demo')
            {
                $dbName = 'seqr_'.$dbName;
            }else{

                $dbName = 'seqr_d_'.$dbName;
            }
            echo $value->new_server;

            echo "<br>";
            \DB::disconnect('mysql'); 

            if($value->new_server == 1) {

                \Config::set("database.connections.mysql", [
                    'driver'   => 'mysql',
                    'host'     => 'localhost',
                    "port" => '3306',
                    'database' => $dbName,
                    'username' => 'developer',
                    'password' => 'developer',
                    "unix_socket" => "",
                    "charset" => "utf8mb4",
                    "collation" => "utf8mb4_unicode_ci",
                    "prefix" => "",
                    "prefix_indexes" => true,
                    "strict" => true,
                    "engine" => null,
                    "options" => []
                ]);

            } else {
                \Config::set("database.connections.mysql", [
                  'driver'   => 'mysql',
                  // 'host'     => 'localhost',
                  'host'     => 'seqrdoc.com',
                  "port" => "3306",
                  'database' => $dbName,
                  'username' => 'developer',
                  'password' => 'developer',
                  "unix_socket" => "",
                  "charset" => "utf8mb4",
                  "collation" => "utf8mb4_unicode_ci",
                  "prefix" => "",
                  "prefix_indexes" => true,
                  "strict" => true,
                  "engine" => null,
                  "options" => []
                ]);
            
            }
            \DB::reconnect();

            
            echo $dbName;
            echo "<br>";

            // $tables = ['uploaded_pdfs', 'file_records'];
            $database = $dbName; // e.g., 'seqr_d_galgotias'

            try {
                // Check if database exists
                $dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$database]);

                if (!empty($dbExists)) {
                    // foreach ($tables as $table) {
                        // Check if table exists
                        if (!Schema::hasTable('uploaded_pdfs')) {
                            // // Check if column exists
                            // $columnExists = DB::select("SHOW COLUMNS FROM `{$database}`.`{$table}` LIKE 'map_type'");
                            DB::statement("
                                CREATE TABLE `uploaded_pdfs` (
                                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                    `file_name` varchar(191) DEFAULT NULL,
                                    `extractor_details` longtext DEFAULT NULL,
                                    `placer_details` longtext DEFAULT NULL,
                                    `ep_details` longtext DEFAULT NULL,
                                    `template_title` varchar(500) DEFAULT NULL,
                                    `template_name` varchar(191) DEFAULT NULL,
                                    `pdf_page` varchar(191) DEFAULT NULL,
                                    `print_bg_file` int(11) DEFAULT 0,
                                    `print_bg_status` varchar(191) DEFAULT 'No',
                                    `verification_bg_file` int(11) DEFAULT 0,
                                    `verification_bg_status` varchar(191) DEFAULT 'No',
                                    `generated_by` int(11) NOT NULL,
                                    `is_block_chain` int(11) NOT NULL DEFAULT 0,
                                    `bc_document_description` varchar(256) DEFAULT NULL,
                                    `bc_document_type` varchar(256) DEFAULT NULL,
                                    `bc_contract_address` varchar(256) DEFAULT NULL,
                                    `publish` int(11) DEFAULT 1,
                                    `map_type` tinyint(4) NOT NULL DEFAULT 0,
                                    `created_at` timestamp NULL DEFAULT NULL,
                                    `updated_at` timestamp NULL DEFAULT NULL,
                                    PRIMARY KEY (`id`)
                                )
                            ");
                            echo "Table `uploaded_pdfs` is now exist in database `{$database}`<br>";
                        } else {
                            echo "Table `uploaded_pdfs` already exists.";
                            echo "<br>";
                        }

                        if (!Schema::hasTable('file_records')) {
                            DB::statement("
                                CREATE TABLE `file_records` (
                                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                    `template_id` int(11) DEFAULT NULL,
                                    `template_name` varchar(191) DEFAULT NULL,
                                    `pdf_page` varchar(191) DEFAULT NULL,
                                    `total_records` int(11) DEFAULT NULL,
                                    `pages_in_pdf` int(11) DEFAULT NULL,
                                    `source_file` text DEFAULT NULL,
                                    `userid` int(11) DEFAULT NULL,
                                    `record_unique_id` varchar(191) DEFAULT NULL,
                                    `created_at` timestamp NULL DEFAULT NULL,
                                    `updated_at` timestamp NULL DEFAULT NULL,
                                    `map_type` int(1) DEFAULT 0,
                                    PRIMARY KEY (`id`)
                                )
                            ");
                        } else {
                            echo "Table `file_records` already exists.";
                        }

                    // }
                } else {
                    echo "Database `{$database}` does not exist.<br>";
                }
            } catch (\Exception $e) {
                echo "Error: " . $e->getMessage();
            }

            // //Check if column already exists to avoid duplicate error
            // $columnExists = DB::select("SHOW COLUMNS FROM `".$dbName."`.`uploaded_pdfs` LIKE 'map_type'");
            // if (empty($columnExists)) {
            //     DB::statement("ALTER TABLE `".$dbName."`.`uploaded_pdfs` ADD `map_type` TINYINT(4) DEFAULT 0");
            // } else {
            //     echo "uploaded_pdfs Column 'status' already exists in table '$table'<br>";
            // }

            // $columnExists1 = DB::select("SHOW COLUMNS FROM `".$dbName."`.`file_records` LIKE 'map_type'");
            // if (empty($columnExists1)) {
            //     DB::statement("ALTER TABLE `".$dbName."`.`file_records` ADD `map_type` TINYINT(1) DEFAULT 0");
            // } else {
            //     echo "file _records Column 'status' already exists in table '$table'<br>";
            // }
            // echo "<br>";
            // die();

            // DB::statement("ALTER TABLE `".$dbName."`.`student_table` 
            //         ADD COLUMN `pinata_ipfs_hash` TEXT NULL 
            //         AFTER `bc_ipfs_hash`");



        }
        

        

    }

    public function mailcheck()
    {
        $hostUrl = explode('.', $_SERVER['HTTP_HOST']);
        $iname = ucwords($hostUrl[0]);
        $columns = ['id','request_url','client_ip','created','request_method','header_parameters','response_parameters','status'];
        $date = date('Y-m-d') . ' 17:59:00';
        $previousDate = date('Y-m-d', strtotime("-1 days")) . ' 18:00:00';

 

        $apiData = ApiTracker::select($columns)->where("status","failed")->get()->toArray();
       

        if(count($apiData))
        {
            try{
                
                $contents = Excel::raw(new ApiTrakerExport(), \Maatwebsite\Excel\Excel::XLSX);

                Mail::send('mail.apitracker', ['today' => $date, 'previousDate' => $previousDate, 'instance' => $iname], function ($m) use ($contents, $apiData, $iname, $date){
                    $m->from('info@seqrdoc.com', 'SeQR');
                    $m->to('dev12@scube.net.in', 'Mandar')->subject($iname.' SeQR Docs //'.count($apiData).' Failed API Log // '.date('Y-m-d').'.')->cc('deve12@scube.net.in');
                    $m->attachData($contents, $iname.'_SeQR_Failed_API_'.date('Y-m-d').'.xlsx');
                });
                
            }catch(\Exception $e){
               echo 'Message: ' .$e->getMessage();
            }
        }else{
            echo "no record";
        }
    



    }

    public function curl_test(){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://seqronline.com/iitj/easypaywebapp.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,"key=8354A18176EA05B14FB479DE0F73D2FC&device_type=webapp&user_id=1&scan_id=0");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        dd($server_output);
    }


    public function testAWS()
    {
        $disk = \Storage::disk('s3');
        $list = $disk->allFiles('bmcc');
        //$list = $disk->allFiles('public/demo/backend/pdf_file/Inactive_PDF');
        $size = 0;
        //foreach ($list as $file) {/
           //  $size+= $disk->size($file);
        //}
        //echo number_format($size / 1048576,2)." MB";
        //echo "<br>";

        // $list = $disk->allFiles('');

        echo "<pre>";
        print_r($list);
        echo "</pre>";

        die();

        // $aws_directory_pdf_file = 'public/demo/backend/pdf_file/inactive_PDF';
        // $size = array_sum(array_map(function($file) {
        //     return (int)$file['size'];
        // }, array_filter($disk->listContents($aws_directory_pdf_file, true /*<- recursive*/), function($file) {
        //     return $file['type'] == 'file';
        // })));
        // echo $size;
        // die();
        /*27/07/2023*/

        dd(\Storage::disk('s3')->allFiles(''));

        $s3=\Storage::disk('s3');    

        $testFile = "public/test/backend/pdf_file/test123.pdf";
        // $test_directory1 = 'test/backend/canvas/images/qr';
        $test_directory1 = 'public/test/backend/pdf_file/testRohit1';
        $test_directory2 = 'public/test/backend/pdf_file/testRohit2';
        if(!$s3->exists($test_directory1)) {
           // $s3->makeDirectory($test_directory1, 0777);  
            echo "not exist";
            if($test_directory1.'/MA010.pdf') {
                 echo "file name is exits";
                if(!$s3->exists($test_directory2)){
                    echo "create directory";
                    $s3->makeDirectory($test_directory2, 0777);
                    $s3 = \Storage::disk('s3')->move($test_directory1.'/MA010.pdf', $test_directory2.'/MA010.pdf');    
                } else {
                    echo "not create directory but file moved";
                    $s3 = \Storage::disk('s3')->move('public/test/backend/pdf_file/MA010.pdf', 'public/test/backend/pdf_file/testRohit1/MA010.pdf');  
                }

            }
        } else {
            echo "exist";
            if($test_directory1.'/MA010.pdf') {

                if(!$s3->exists($test_directory2)){
                    $s3->makeDirectory($test_directory2, 0777);
                    $s3 = \Storage::disk('s3')->move($test_directory1.'/MA010.pdf', $test_directory2.'/MA010.pdf');  
                } else {
                    $s3 = \Storage::disk('s3')->move('public/test/backend/pdf_file/MA010.pdf', 'public/test/backend/pdf_file/testRohit1/MA010.pdf');   
                }
                
            }

        }

        // if(!$s3->exists($
    //))
        // {
        //  $s3->makeDirectory($tcpdf_directory, 0777);  
        // }if(!$s3->exists($examples_directory))
        // {
        //  $s3->makeDirectory($examples_directory, 0777);  
        // }if(!$s3->exists($sandbox_directory))
        // {
        //  $s3->makeDirectory($sandbox_directory, 0777);  
        // }

        //echo 'tesyt';

        //$s3 = \Storage::disk('s3')->copy('public/test/backend/pdf_file/MA010.pdf', 'public/test/backend/pdf_file/testRohit1/MA010.pdf');
        

    }
    public function checkfiles()
    {
        $filePath = public_path('nita/backend/test/checkfiles.xlsx');

        // Check if the file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }else{
            $foldername = public_path('nita/backend/templates/4/');
            $data = Excel::toArray([], $filePath);
            // dd($data );
            unset($data[0][0]);
            $notfoundimage= [];
           foreach($data[0] as $value){
            $image_name = $value[15];
            // dd($image_name);
            $imagefilepath = $foldername.$image_name;
            
            if (!file_exists($imagefilepath)) {
                // $notfoundimage[] = $image_name;
                echo "<pre>";print_r($image_name);
            }


           }
           echo "<pre>";print_r($notfoundimage);
            // return    response()->json(['success' => 'File found'], 200);
        }
        

        // Display the data (for debugging)
       
    }

    public function imageCheck(){

        $result = $this->generatePdfZip('R/25/1365','7');


        die();
        $inputFileType = 'Xlsx';
        $target_path = public_path();
        $fullpath = $target_path.'/demo/TPSDI_Job_image.xlsx';
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        // Load the existing Excel file
        $objReader = IOFactory::createReader($inputFileType);
        $objPHPExcel1 = $objReader->load($fullpath);
        $sheet1 = $objPHPExcel1->getSheet(0);
        $highestColumn1 = $sheet1->getHighestColumn();
        $highestRow1 = $sheet1->getHighestDataRow();
        $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
        unset($rowData1[0]);
        print_r(count($rowData1));


        $newSpreadsheet = new Spreadsheet();
        $newSheet = $newSpreadsheet->getActiveSheet();
        $newSheet->SetCellValueByColumnAndRow(0, 1, 'Serial No');
        $newSheet->SetCellValueByColumnAndRow(1, 1, 'Result');
        $i = 2;
        $template_id = 7;
        foreach($rowData1 as $data) {
            $newSheet->SetCellValueByColumnAndRow(0, $i, $data[0]);

            // echo public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$data[2].'.jpg';
            // echo "<br>";
            $file_location_jpg= public_path().'/'.$subdomain[0].'/backend/templates/'.$template_id.'/'.$data[2].'.jpg';
            $file_location_png = public_path().'/'.$subdomain[0].'/backend/templates/'.$template_id.'/'.$data[2].'.png';
                
            if(file_exists($file_location_jpg) || file_exists($file_location_png) ) {
                $newSheet->SetCellValueByColumnAndRow(1, $i, 'image found.');
            } else {
                $newSheet->SetCellValueByColumnAndRow(1, $i, 'no image found.');
            }
            
            $i++;
        }

        // Define the output path
        $newFilePath = $target_path.'/demo/TPSDI_Job_image_result.xlsx';

        // Save the new Excel file
        $writer = new Xlsx($newSpreadsheet);
        $writer->save($newFilePath);

        echo "File copied successfully: " . $newFilePath;


    }
    


    public function generatePdfZip($request_number, $template_id)
    {
        $checkUploadedFileOnAwsOrLocal = new CheckUploadedFileOnAwsORLocalService();
        // $this->findMissedSerialNo();
        // exit;
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        // $request_number = "R/24/1278";

        //$request_number = $request['req_number'];
        $IdCardStatus = IdCardStatus::where('request_number', $request_number)
            ->first();

        //print_r($IdCardStatus);

        //$IdCardStatus['template_name']="Training participation 1 sided";
        $template_name = $IdCardStatus['template_name'];
        $excelfile = $IdCardStatus['excel_sheet'];
        //$excelfile = "TPSDI_Job_D245_20240217145502__.xlsx";
        $highestRow = $IdCardStatus['rows'] + 1;
        $status = $IdCardStatus['status'];



        //$excelfile="-SHE_TPSDI_Job_B146_20211217122041_processed.xlsx";
        // $target_path=public_path().'/'.$subdomain[0].'/backend/'.$excelfile;
        $target_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile;

        // echo $excelfile;

        $extension = pathinfo($excelfile, PATHINFO_EXTENSION);

        $inputType = 'Xls';
        if ($extension == 'xlsx' || $extension == 'XLSX') {

            $inputType = 'Xlsx';
        }
        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputType);
        $objPHPExcel = $objReader->load($target_path);
        $sheet = $objPHPExcel->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();



        if ($template_id == 7) {

            //$sheet->setCellValue('L1','SeQR code');
            $sheet->setCellValue('M1', 'SeQR code');
        } else {
            //$sheet->setCellValue('J1','SeQR code');
            $sheet->setCellValue('K1', 'SeQR code');
        }

        $file_not_exists = [];
        $enrollImage = [];
        $i = 1;
        // $highestRow = 500;
        for ($excel_row = 2; $excel_row <= $highestRow; $excel_row++) {

            $rowData = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);

            $rowData = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);

            // print_r($rowData);
            // exit;
            $excel_column_row = $sheet->getCellByColumnAndRow(3, $excel_row);

            $enroll_value = $excel_column_row->getValue();



            $dt = date("_ymdHis");

            $str = $rowData[0][0]; //updated by mandar


            //exit;
            if (!empty($str)) {

                if ($template_id == 7) {

                    $studentData = StudentTable::where('serial_no', $str)
                        ->orderBy('id', 'desc')->first();
                    // print_r($studentData);
                    // exit;
                    $codeContents = $studentData->key;
                    // exit;
                    //echo "a";
                    //$sheet->setCellValue('L'.$excel_row,$codeContents);
                    $sheet->setCellValue('M' . $excel_row, $codeContents);
                } else {
                    $studentData = StudentTable::where('serial_no', $str)
                        ->orderBy('id', 'desc')->first();
                    $codeContents = $studentData->key;
                    //echo "b";
                    // $sheet->setCellValue('J'.$excel_row,$codeContents);
                    $sheet->setCellValue('K' . $excel_row, $codeContents);
                }
                // echo "<pre>";print_r($excel_row);
                // echo "<pre>";print_r($codeContents);
                // exit;
            }


            $imageName = $enroll_value . '.jpg';


            $imageNamePng = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.png';
            $imageNameJpg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.jpg';
            $imageNameJpeg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.jpeg';
            //echo'<br>';   
            if (file_exists($imageNamePng)) {

                $imageName = $enroll_value . '.png';

            } elseif (file_exists($imageNameJpg)) {
                $imageName = $enroll_value . '.jpg';
            } elseif (file_exists($imageNameJpeg)) {
                $imageName = $enroll_value . '.jpeg';
            }

            array_push($enrollImage, $imageName);


            $i++;

        }




        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, "Xlsx");
        $pathinfo = pathinfo($excelfile);

        $excel_filename = $pathinfo['filename'];
        $excel_extension = $pathinfo['extension'];

        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
            $save_path = '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id;
        } else {
            $save_path = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id;
        }

        $objWriter->save(public_path() . $save_path . '/' . $template_name . '_' . $excel_filename . '_processed_new.' . $excel_extension);

        //print_r($file_not_exists);
        $pathinfo = pathinfo($excelfile);
        $excel_filename = $pathinfo['filename'];
        $excel_extension = $pathinfo['extension'];

        $excel_request_no = explode('/', $request_number);
        $zip_file_name = $excel_request_no[0] . '_' . $excel_request_no[1] . '_' . $excel_request_no[2] . '_new.zip';


        \Log::info('Enroll Image count: ' . count($enrollImage));

        $zip = new \ZipArchive;

        if ($zip->open(public_path() . $save_path . '/' . $zip_file_name, \ZipArchive::CREATE) === TRUE) {

            $zip->addFile(public_path() . $save_path . '/' . $template_name . '_' . $excel_filename . '_processed_new.' . $excel_extension, $template_name . '_' . $excel_filename . '_processed_new.' . $excel_extension);

            foreach ($enrollImage as $key => $value) {

                echo public_path() . $save_path . '/' . $value;
                echo "<br>";
                $zip->addfile(public_path() . $save_path . '/' . $value, $value);
            }
            $zip->close();
        }
        if ($get_file_aws_local_flag->file_aws_local == '1') {
            \Storage::disk('s3')->put($save_path . '/' . $zip_file_name, file_get_contents(public_path() . $save_path . '/' . $zip_file_name), 'public');
            \Storage::disk('s3')->put($save_path . '/' . $withoutExt . ".txt", file_get_contents($file_path . '/' . $withoutExt . ".txt"), 'public');
        }
        $created_on = date('Y-m-d H:i:s');
        $highestRecord = $highestRow - 1;

        return true;

    }





    public function excelRecord(){
        $inputFileType = 'Xlsx';
        $target_path = public_path();
        // $fullpath = $target_path.'/aiimsnagpur/Convo_MBBS.xlsx';
        $fullpath = $target_path.'/aiimsnagpur/MDMS_MASTERSHEET_TEST.xlsx';
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        // Load the existing Excel file
        $objReader = IOFactory::createReader($inputFileType);
        $objPHPExcel1 = $objReader->load($fullpath);
        $sheet1 = $objPHPExcel1->getSheet(0);
        $highestColumn1 = $sheet1->getHighestColumn();
        $highestRow1 = $sheet1->getHighestDataRow();
        $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
        unset($rowData1[0]);
        // echo "<pre>";
        // print_r($rowData1);
        // die();

        foreach($rowData1 as $data) {

            // echo "<pre>";
            // print($data[13]);
            // die();
            if(empty($data[13])) {
                break;
            }
            $studentData = StudentTable::where('serial_no', $data[13])->where('status', 1)->first();

            
            if($studentData) {
                // echo $studentData['id'];
                // echo "<br>";
                StudentTable::where('serial_no',$data[0])->update(["bc_txn_hash"=>$data[26] ]);

                

                $result = DB::table('blockchain_other_data')->updateOrInsert(
                    ['student_table_id' => $studentData['id']],
                    ['certificate_id' => $data[25]],
                    ['vendor_identifier' => 2]

                );


                echo $studentData['id']." Record Update. Certifcate Id - ". $data[10];
                echo "<br>";



            }


            // echo "<pre>";
            // echo 'Txn Hash '. $data[9];
            // echo "<br>";
            // echo 'Certificate ID '. $data[10];
            // echo "<br>";
            // print_r($studentData);
            // echo "<br>";
            // echo "<br>";
            
        }



    }



    public function blockchain_rnd() {

        $subdomain[0] = 'anu';
        $certName = 'JOHN19042.pdf';
        $filePath = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;

        //$filePath = 'C:\/inetpub\/vhosts\/seqrdoc.com\/httpdocs\/demo\/public\/mitwpu\/backend\/pdf_file\/1062212356.pdf';
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        // $filePath = str_replace("\\", '/', $filePath);
        $mode = 1;
        $requestUrl = ($mode == 0)
                ? 'http://localhost:9090/testnet/mint'
                : 'http://localhost:9090/mainnet/mint';

        $curl = curl_init();
            
        curl_setopt_array($curl, array(
          CURLOPT_URL => $requestUrl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array(
                'metadata1' => '{"label":"Student Name","value":"NILSHA"}',
                'metadata2' => '{"label":"Programme","value":"Bechelor of Design"}',
                'metadata3' => '',
                'metadata4' => '',
                'metadata5' => '',
                'walletID' => '0x4a8c8e4D5D255f95253FC93E99b68c05aa869c4F',
                'documentType' => 'Transcript',
                'smartContractAddress' => '0x0685eFf7B1D2217F466831d7c78Db3edC0d464F1',
                'file' => new \CURLFile($filePath),
                'description' => 'Student ID :JOHN19042',
                'uniqueHash' => '00F3CBB66F1CED1B276CF4A25A2A9443'
            ),
          CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'x-api-key: 123456789'
          ),
        ));

        $response = curl_exec($curl);

        print_r($response);
        // curl_close($curl);

        die();
        // $curl = curl_init();

        // // $url = 'https://api.opensea.io/api/v2/chain/matic/contract/0x5a11B22881A850E4df265bF42b0f74808E9B02cd/nfts/3';
        // $url = 'https://api.opensea.io/api/v2/chain/matic/contract/0x9d187c7c331301fb8cd383be6d4358552b0e223e/nfts/5954';
        // curl_setopt_array($curl, [
        //   CURLOPT_URL => $url,
        //   CURLOPT_RETURNTRANSFER => true,
        //   CURLOPT_ENCODING => "",
        //   CURLOPT_MAXREDIRS => 10,
        //   CURLOPT_TIMEOUT => 30,
        //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //   CURLOPT_CUSTOMREQUEST => "GET",
        //   CURLOPT_HTTPHEADER => [
        //     "accept: application/json",
        //     "x-api-key: eb9d18bbe6bf48648ed2c12300f2f4c2"
        //   ],
        // ]);

        // $response = curl_exec($curl);
        // $err = curl_error($curl);

        // curl_close($curl);

        // echo "<pre>";
        // if ($err) {
        //   echo "cURL Error #:" . $err;
        // } else {
        //   echo $response;
        // }

        // die();

        // Example: Run a command without options
        // Artisan::call('blockchain:verify-documents');
        // die();

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

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

        // $blockchain_documents = \DB::table('blockchain_documents_for_demo')->where('instance_name','ANU')->get();
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
                $temp_data['message'] =  'Invalid Document';
                \Log::error("$e"); 
                
            }
            if($temp_data['status'] == "failed"){
               
                $is_failed++;
            }
            $final_data[] = $temp_data;
        }

        // echo "<pre>";
        // print_r($final_data);
        // if(!empty( $final_data)){ 
        //     $email_id = 'dev7@scube.net.in';  
        //     Mail::to($email_id )->send(new SendSummaryBlockChainV1($final_data,$is_failed));
        //     // 'software@scube.net.in'
        //     Log::info('Something Wrong in Blockchain email Sent.');
        // }else{
        //     Log::error('All Blockchain working fine.');

        // }
      
         

        Log::info('Ended verify blockchain document Command');

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

        

        // $responData = $this->refreshBlockchainData($contractAddress,$token_id);
        // echo "<prE>";
        // print_r($responData);
        // die();
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
        // echo $tokenId;
        // die();
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
        
        echo "<pre>";
        print_r($decodedResponse);
        echo "<br>";
        echo "<br>";
        // die();

        if ($err) {
            $status = 'failed';

            return  "cURL Error #:" . $err;
        } else {
            return  $response;
        }

    }

    public function refreshBlockchainData($contract, $tokenId) {

        $requestParameters['contract']=$contract;
        $requestParameters['tokenId']=$tokenId;


        $url = "https://api.opensea.io/api/v2/chain/polygon/contract/".$contract."/nfts//".$tokenId."/refresh";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "X-API-KEY: eb9d18bbe6bf48648ed2c12300f2f4c2"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        $decodedResponse = json_decode($response, true);
        if ($err) {
            // echo "cURL Error #: " . $err;
            $status = 'failed';

            $responseTime = microtime(true) - LARAVEL_START;
            $api_tracker_id = Self::insertTracker($url,'POST',$requestParameters,$decodedResponse,$status,$responseTime,'refreshBlockchainData');
        } else {
            // echo $response;
            $status = 'success';

            $responseTime = microtime(true) - LARAVEL_START;
            $api_tracker_id = Self::insertTracker($url,'POST',$requestParameters,$decodedResponse,$status,$responseTime,'refreshBlockchainData');
        }


    }


    public function excelRead(){

       

        $inputFileType = 'Xlsx';    
        $target_path = public_path();
        $fullpath = $target_path.'/konkankrishi/blockchain/truescholar/4-Without Sub degree and 11 Line - Blockchain output.xlsx';
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        // Load the existing Excel file
        $objReader = IOFactory::createReader($inputFileType);
        $objPHPExcel1 = $objReader->load($fullpath);
        $sheet1 = $objPHPExcel1->getSheet(0);
        $highestColumn1 = $sheet1->getHighestColumn();
        $highestRow1 = $sheet1->getHighestDataRow();
        $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
        unset($rowData1[0]);
        print_r(count($rowData1));

        echo "<br>";
        // die();
        
        // $newSpreadsheet = new Spreadsheet();
        // $newSheet = $newSpreadsheet->getActiveSheet();
        // $newSheet->SetCellValueByColumnAndRow(1, 1, 'Certificate Number');
        // $newSheet->SetCellValueByColumnAndRow(2, 1, 'Name');
        // $newSheet->SetCellValueByColumnAndRow(3, 1, 'Passing Year');
        // $newSheet->SetCellValueByColumnAndRow(4, 1, 'Grade');
        // $newSheet->SetCellValueByColumnAndRow(5, 1, 'Degree');
        // $newSheet->SetCellValueByColumnAndRow(6, 1, 'Adhar Number');
        // $newSheet->SetCellValueByColumnAndRow(7, 1, 'Unique Hash');
        // $newSheet->SetCellValueByColumnAndRow(8, 1, 'PDF URL');
        $i = 2;
        $template_id = 7;
        foreach($rowData1 as $data) {
            // $newSheet->SetCellValueByColumnAndRow(1, $i, $data[0]);
            // $newSheet->SetCellValueByColumnAndRow(2, $i, $data[1]);
            // $newSheet->SetCellValueByColumnAndRow(3, $i, $data[2]);
            // $newSheet->SetCellValueByColumnAndRow(4, $i, $data[3]);
            // $newSheet->SetCellValueByColumnAndRow(5, $i, $data[4]);
            // $newSheet->SetCellValueByColumnAndRow(6, $i, $data[5]);
            

            $studentData = StudentTable::select('id', 'key' ,'certificate_filename')
            ->where('serial_no', $data[0])
            ->where('template_id', 7)
            ->whereDate('created_at', '>=', '2025-05-07')
            ->where('status', 1)
            ->where('publish', 1)
            ->first();

            $unique_hash = '';
            $filePath = '';
            if($studentData) {
                // $unique_hash = $studentData['key'];


                DB::table('student_table')
                    ->where('id', $studentData['id'])
                    ->update(['bc_txn_hash' => $data[8]]);





                DB::table('blockchain_other_data')->updateOrInsert(
                    ['student_table_id' => $studentData['id']], // Match condition
                    [
                        'certificate_id' => $data[9],
                        'vendor_identifier' => 2 // optional if you use timestamps
                    ]
                );


                echo $studentData['id'];
                echo "<br>";

            }

            // $newSheet->SetCellValueByColumnAndRow(7, $i, $unique_hash);
            // $newSheet->SetCellValueByColumnAndRow(8, $i, $filePath);
            
            $i++;
        }

        // Define the output path
        // $newFilePath = $target_path.'/konkankrishi/blockchain/export/1-With sub degree and 11 line - Blockchain output output.xlsx';

        // // Save the new Excel file
        // $writer = new Xlsx($newSpreadsheet);
        // $writer->save($newFilePath);

        // echo "File copied successfully: " . $newFilePath;


    }


    public function sftpTesting(){
        // $source
        $source=\Config::get('constant.directoryPathBackward')."\\kmtc\\backend\\pdf_file\\ATEST1996.pdf";//$file1
        // if(file_exists($source)) {

        //     CoreHelper::SFTPUploadKMTC($source);
        // } else {
        //     echo "file not found.";
        // }

        
        $response = CoreHelper::listFilesKMTC($source);
        echo $response;
        die();
    }




    
}
