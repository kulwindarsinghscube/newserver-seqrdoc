<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\TemplateMaster;
use App\models\SuperAdmin;
use Session, TCPDF, TCPDF_FONTS, Auth, DB;
use App\Http\Requests\ExcelValidationRequest;
use App\Http\Requests\MappingDatabaseRequest;
use App\Http\Requests\TemplateMapRequest;
use App\Http\Requests\TemplateMasterRequest;
use App\Imports\TemplateMapImport
;
use App\Imports\TemplateMasterImport;
use App\Jobs\PDFGenerateJob;
use App\models\BackgroundTemplateMaster;
use App\Events\BarcodeImageEvent;
use App\Events\TemplateEvent;
use App\models\FontMaster;
use App\models\FieldMaster;
use App\models\User;
//use App\models\StudentTable;
use App\models\blockchain\StudentTable;
use App\models\SbStudentTable;
use Maatwebsite\Excel\Facades\Excel;
use App\models\SystemConfig;
use App\Jobs\PreviewPDFGenerateJob;
use App\Exports\TemplateMasterExport;
use Storage;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use App\models\Config;
use App\models\PrintingDetail;
use App\models\ExcelUploadHistory;
use App\models\SbExceUploadHistory;

use App\Helpers\CoreHelper;
use Helper;
use App\Jobs\ValidateExcelAnuJob;
use App\Jobs\ValidateExcelAnuGradeCard2022Job;

use App\Jobs\ValidateExcelAnuA4TranscriptJob;
use App\Jobs\ValidateExcelAnuA3TranscriptJob;
use App\Jobs\PdfGenerateAnuJob;
use App\Jobs\PdfGenerateAnuGradeCard2022Job;
use App\Jobs\PdfGenerateAnuA4TranscriptJob;
use App\Jobs\PdfGenerateAnuA3TranscriptJob;
use App\Jobs\PdfGenerateAnuAwardConvocationJob;
use App\Jobs\ValidateExcelAnuAwardConvocationJob;
use App\Jobs\PdfGenerateAnuFinalAFCACertificateJob;
use App\Jobs\ValidateExcelAnuBTCCJob;
use App\Jobs\PdfGenerateAnuBTCCJob;
use Illuminate\Support\Facades\Log;

class AnuCertificateController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.anu.index');
    }

    public function uploadpage()
    {
        // $response=CoreHelper::SFTPConnect();        
        $ftp_flag = $response['ftp_flag'];
        $ftpHost = $response['ftpHost'];
        return view('admin.anu.index', compact(['ftp_flag', 'ftpHost']));
    }

    public function testUpload($certName, $pdfActualPath)
    {
        // FTP server details
        $ftpHost = \Config::get('constant.anu_ftp_host');
        $ftpPort = \Config::get('constant.anu_ftp_port');
        $ftpUsername = \Config::get('constant.anu_ftp_username');
        $ftpPassword = \Config::get('constant.anu_ftp_pass');
        // open an FTP connection
        $connId = ftp_connect($ftpHost, $ftpPort) or die("Couldn't connect to $ftpHost");
        // login to FTP server
        $ftpLogin = ftp_login($connId, $ftpUsername, $ftpPassword);
        // local & server file path
        $localFilePath = $pdfActualPath;
        $remoteFilePath = $certName;
        // try to upload file
        if (ftp_put($connId, $remoteFilePath, $localFilePath, FTP_BINARY)) {
            echo "File transfer successful - $localFilePath";
        } else {
            echo "There was an error while uploading $localFilePath";
        }
        // close the connection
        ftp_close($connId);
    }

    public function pdfGenerate()
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $response = CoreHelper::checkAnuFtpStatus();
        $ftp_flag = $response['ftp_flag'];
        $file_name = "anuGC20221101111135.pdf";
        $this->testUpload($file_name, public_path() . '/' . $subdomain[0] . '/backend/tcpdf/examples/' . $file_name);
    }

    public function validateExcel(Request $request, CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {
        $template_id = 100;
        $dropdown_template_id = $request['template_id'];
        $points = $request['points'];
        /* 1=Basic, 2=B.Ed. 1st Year, 3=B.Ed. Final, 4=Grade Final, 5=Pharma */
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        //check file is uploaded or not
        if ($request->hasFile('field_file')) {
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile = date("YmdHis") . "_" . $file_name;
            $target_path = public_path() . '/backend/canvas/dummy_images/' . $template_id;
            $fullpath = $target_path . '/' . $excelfile;

            if (!is_dir($target_path)) {
                mkdir($target_path, 0777);
            }

            if ($request['field_file']->move($target_path, $excelfile)) {
                //get excel file data

                if ($ext == 'xlsx' || $ext == 'Xlsx') {
                    $inputFileType = 'Xlsx';
                } else {
                    $inputFileType = 'Xls';
                }
                $auth_site_id = Auth::guard('admin')->user()->site_id;

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
                if ($get_file_aws_local_flag->file_aws_local == '1') {
                    if ($systemConfig['sandboxing'] == 1) {
                        $sandbox_directory = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/';
                        //if directory not exist make directory
                        if (!is_dir($sandbox_directory)) {

                            mkdir($sandbox_directory, 0777);
                        }

                        $aws_excel = \Storage::disk('s3')->put($subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $excelfile, file_get_contents($fullpath), 'public');
                        $filename1 = \Storage::disk('s3')->url($excelfile);

                    } else {

                        $aws_excel = \Storage::disk('s3')->put($subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile, file_get_contents($fullpath), 'public');
                        $filename1 = \Storage::disk('s3')->url($excelfile);
                    }
                } else {

                    $sandbox_directory = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/';
                    //if directory not exist make directory
                    if (!is_dir($sandbox_directory)) {

                        mkdir($sandbox_directory, 0777);
                    }

                    if ($systemConfig['sandboxing'] == 1) {
                        $aws_excel = \File::copy($fullpath, public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $excelfile);

                    } else {
                        $aws_excel = \File::copy($fullpath, public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile);

                    }
                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);
                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();
                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                //For checking certificate limit updated by Mandar
                $recordToGenerate = $highestRow1 - 1;
                $checkStatus = CoreHelper::checkMaxCertificateLimit($recordToGenerate);
                if (!$checkStatus['status']) {
                    return response()->json($checkStatus);
                }
                $excelData = array('rowData1' => $rowData1, 'auth_site_id' => $auth_site_id, 'dropdown_template_id' => $dropdown_template_id, 'points' => $points);


                if ($dropdown_template_id == 2 || $dropdown_template_id == 3 ) {
                    $studentData = $this->fetchArrayData($sheet1, $highestRow1);
                    $recordToGenerate = count($studentData[0]);
                }

                // echo "<pre>";
                // print_r($studentData[1]);
                // echo "</pre>";
                // die();
                // $excelData1=array('studentDataOrg'=>$studentData[0],'subjectDataOrg'=>$studentData[1],'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'dropdown_template_id'=>$dropdown_template_id,'points'=>$points,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,'loader_token'=>$loader_token);

                $excelData1 = array('rowData1' => $rowData1, 'studentData' => $studentData[0], 'subjectData' => $studentData[1], 'auth_site_id' => $auth_site_id, 'dropdown_template_id' => $dropdown_template_id, 'points' => $points);

                if ($dropdown_template_id == 1) {
                    $response = $this->dispatch(new ValidateExcelAnuJob($excelData));
                } elseif ($dropdown_template_id == 2) {
                    $response = $this->dispatch(new ValidateExcelAnuA4TranscriptJob($excelData1));
                } elseif ($dropdown_template_id == 3) {
                    $response = $this->dispatch(new ValidateExcelAnuA3TranscriptJob($excelData1));
                } elseif ($dropdown_template_id == 4) {
                    $response = $this->dispatch(new ValidateExcelAnuGradeCard2022Job($excelData));
                }
                elseif ($dropdown_template_id == 5) {
                    $response = $this->dispatch(new ValidateExcelAnuAwardConvocationJob($excelData));
                }
                 elseif ($dropdown_template_id == 6) {
                    $response = $this->dispatch(new ValidateExcelAnuAwardConvocationJob($excelData));
                }
                elseif ($dropdown_template_id == 7) {
                    $response = $this->dispatch(new ValidateExcelAnuBTCCJob($excelData));
                }
                //print_r($response);
                $responseData = $response->getData();

                if ($responseData->success) {
                    $old_rows = $responseData->old_rows;
                    $new_rows = $responseData->new_rows;
                } else {
                    return $response;
                }
            }

            if (file_exists($fullpath)) {
                unlink($fullpath);
            }
            //For custom Loader
            $randstr = CoreHelper::genRandomStr(5);
            $jsonArr = array();
            $jsonArr['token'] = $randstr . '_' . time();
            $jsonArr['status'] = '200';
            $jsonArr['message'] = 'Pdf generation started...';
            $jsonArr['recordsToGenerate'] = $recordToGenerate;
            $jsonArr['generatedCertificates'] = 0;
            $jsonArr['pendingCertificates'] = $recordToGenerate;
            $jsonArr['timePerCertificate'] = 0;
            $jsonArr['isGenerationCompleted'] = 0;
            $jsonArr['totalSecondsForGeneration'] = 0;
            $loaderData = CoreHelper::createLoaderJson($jsonArr, 1);
            return response()->json(['success' => true, 'type' => 'success', 'message' => 'success', 'old_rows' => $old_rows, 'new_rows' => $new_rows, 'loaderFile' => $loaderData['fileName'], 'loader_token' => $loaderData['loader_token']]);

        } else {
            return response()->json(['success' => false, 'message' => 'File not found!']);
        }

    }

    public function uploadfile(Request $request, CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
      
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        //For custom loader
        $loader_token = $request['loader_token'];
        $template_id = 100;
        $dropdown_template_id = $request['template_id'];
        $points = $request['points'];
        /* 1=Basic */
        $previewPdf = array($request['previewPdf'], $request['previewWithoutBg']);

             // Start Update code for batchwise genration
        $batchSize = 1; // Rows per batch
        $startRow = $request->get('startRow', 2); // Default to row 1 if not provided
        $endRow = $startRow + $batchSize - 1;
        // End Update code for batchwise genration

        //check file is uploaded or not
        if ($request->hasFile('field_file')) {
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile = date("YmdHis") . "_" . $file_name;
            $target_path = public_path() . '/backend/canvas/dummy_images/' . $template_id;
            $fullpath = $target_path . '/' . $excelfile;

            if (!is_dir($target_path)) {
                mkdir($target_path, 0777);
            }

            if ($request['field_file']->move($target_path, $excelfile)) {
                //get excel file data

                if ($ext == 'xlsx' || $ext == 'Xlsx') {
                    $inputFileType = 'Xlsx';
                } else {
                    $inputFileType = 'Xls';
                }
                $auth_site_id = Auth::guard('admin')->user()->site_id;

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
                if ($get_file_aws_local_flag->file_aws_local == '1') {
                    if ($systemConfig['sandboxing'] == 1) {
                        $sandbox_directory = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/';
                        //if directory not exist make directory
                        if (!is_dir($sandbox_directory)) {

                            mkdir($sandbox_directory, 0777);
                        }

                        $aws_excel = \Storage::disk('s3')->put($subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $excelfile, file_get_contents($fullpath), 'public');
                        $filename1 = \Storage::disk('s3')->url($excelfile);

                    } else {

                        $aws_excel = \Storage::disk('s3')->put($subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile, file_get_contents($fullpath), 'public');
                        $filename1 = \Storage::disk('s3')->url($excelfile);
                    }
                } else {

                    $sandbox_directory = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/';
                    //if directory not exist make directory
                    if (!is_dir($sandbox_directory)) {
                        mkdir($sandbox_directory, 0777);
                    }

                    if ($systemConfig['sandboxing'] == 1) {
                        $aws_excel = \File::copy($fullpath, public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $excelfile);
                    } else {
                        $aws_excel = \File::copy($fullpath, public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile);

                    }
                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);
                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();


                

                if ($dropdown_template_id == 2 || $dropdown_template_id == 3) {
                    $studentData = $this->fetchArrayData($sheet1, $highestRow1);

                }


                
                /*for ($row = 2; $row <= $highestRow1; $row++) {
                                $date_value = $sheet1->getCellByColumnAndRow(6, $row)->getValue(); 
                                $timestamp =  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($date_value); 
                                $dob = gmdate( 'd F Y', $timestamp ); //$dob = gmdate( 'd-m-Y', $timestamp );  
                                $sheet1->setCellValue('F'.$row,$dob);
                                $issue_date_value = $sheet1->getCellByColumnAndRow(131, $row)->getValue(); 
                                $idtimestamp =  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($issue_date_value); 
                                $issue_date = gmdate( 'd F Y', $idtimestamp ); //$dob = gmdate( 'F-Y', $timestamp );  
                                $sheet1->setCellValue('EA'.$row,$issue_date);
                                
                            }*/
                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                unset($rowData1[0]);





                // foreach($rowData1 as $row) {
                //     $previousColumn = $objWorksheet->getCellByColumnAndRow(0, $column - 1)->getValue();
                //     $cat = $objWorksheet->getCellByColumnAndRow(0, $column)->getValue();

                //     if ($cat == $previousColumn) { 
                //         continue; 
                //     } else { 
                //         echo '<li><a href="'.$cat.'">'.$cat.'</a></li>'; 
                //     } 
                // }
                // die();
                // $rowData1

                $rowDataLoop = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                if($dropdown_template_id == 7){
                $headerRow = $rowDataLoop[0];
                $uniqueIdIndex = array_search('Unique_ID', $headerRow);
                }
                // dd($uniqueIdIndex);
                unset($rowDataLoop[0]);
                // dd($rowDataLoop);
                if($dropdown_template_id == 4||$dropdown_template_id == 1||$dropdown_template_id == 5||$dropdown_template_id == 6||$dropdown_template_id == 7){
                 
                       $objPHPExcel1 = $objReader->load($fullpath);
                    $sheet1 = $objPHPExcel1->getSheet(0);
                    $highestColumn1 = $sheet1->getHighestColumn();
                    $highestRow1 = $sheet1->getHighestDataRow();
                    
                    $range = 'A' . $startRow . ':' . $highestColumn1 . $endRow;
                    //\Log::info("$range");


                    
                    
                    //Update code for batchwise genration
                    if($highestRow1>=$startRow){
                        // Extract data for the defined range
                        $rowData1 = $sheet1->rangeToArray($range, NULL, TRUE, FALSE);
                        // $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                    
                    }else{
                        $rowData1=array(); 
                        $last_col=0;

                      
                    }
                  
                }

            }
        } else {
            return response()->json(['success' => false, 'message' => 'File not found!']);
        }

        //store ghost image 
        //$tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
        $admin_id = \Auth::guard('admin')->user()->toArray();

        $pdfData = array('studentDataOrg' => $rowData1, 'auth_site_id' => $auth_site_id, 'template_id' => $template_id, 'dropdown_template_id' => $dropdown_template_id, 'points' => $points, 'previewPdf' => $previewPdf, 'excelfile' => $excelfile, 'loader_token' => $loader_token); //For Custom Loader

        $pdfData1 = array('studentDataOrg' => $studentData[0], 'subjectDataOrg' => $studentData[1], 'auth_site_id' => $auth_site_id, 'template_id' => $template_id, 'dropdown_template_id' => $dropdown_template_id, 'points' => $points, 'previewPdf' => $previewPdf, 'excelfile' => $excelfile, 'loader_token' => $loader_token); //For Custom Loader

        if ($dropdown_template_id == 1) {
             $pdfData=array('studentDataOrg'=>$rowData1,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'dropdown_template_id'=>$dropdown_template_id,'points' => $points,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,'loader_token'=>$loader_token,'highestrow'=>(($highestRow1?$highestRow1:$highestRow)-1));
            $link = $this->dispatch(new PdfGenerateAnuJob($pdfData));
        } elseif ($dropdown_template_id == 2) {
            $link = $this->dispatch(new PdfGenerateAnuA4TranscriptJob($pdfData1));
            // Zip file creation
            if($request['previewPdf'] == 0 ) {
                if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
                }else{
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
                }
                $filenameWithoutExt = pathinfo($excelfile, PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '', $filenameWithoutExt); // Remove special chars
                $zip_file_name = $cleanName . '.zip';

                $pdfActualPath = public_path().'/'.$subdomain[0].'/backend/pdf_file';
                $zip = new \ZipArchive;
                
                if ($zip->open(public_path().$save_path.'/'.$zip_file_name,\ZipArchive::CREATE) === TRUE) {
                    foreach ($studentData[0] as $value) {
                        // echo public_path().$save_path.'/'.$value;
                        // echo "<br>";
                        $certName = str_replace("/", "_", $value[6]) .".pdf";

                        $zip->addfile($pdfActualPath.'/'.$certName,$certName);
                    }
                    $zip->close();
                }
                $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
                $zip_link = "<b>Click <a href='".$path.$save_path.'/'.$zip_file_name."' class='downloadpdf download'>Here</a> to download the zip file that includes Digital Blockchain Documents.<b>";

                // $zip_link = public_path().$save_path.'/'.$zip_file_name;
            } else {
                $zip_link = '';
            }
            return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link,'zip_link'=>$zip_link,'startRow'=> $startRow,'endRow'=> $endRow,'highestRow'=> ($highestRow??$highestRow1)]);

        } elseif ($dropdown_template_id == 3) {
            // dd($studentData[0]);
            $link = $this->dispatch(new PdfGenerateAnuA3TranscriptJob($pdfData1));


            // Zip file creation
            if($request['previewPdf'] == 0 ) {
                if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
                }else{
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
                }
                $filenameWithoutExt = pathinfo($excelfile, PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '', $filenameWithoutExt); // Remove special chars
                $zip_file_name = $cleanName . '.zip';

                $pdfActualPath = public_path().'/'.$subdomain[0].'/backend/pdf_file';
                $zip = new \ZipArchive;
                
                if ($zip->open(public_path().$save_path.'/'.$zip_file_name,\ZipArchive::CREATE) === TRUE) {
                    // dd($studentData[0]);
                    foreach ($studentData[0] as $value) {
                        // echo public_path().$save_path.'/'.$value;
                        // echo "<br>";
                        $certName = str_replace("/", "_", $value[6]) .".pdf";

                        $zip->addfile($pdfActualPath.'/'.$certName,$certName);
                    }
                    $zip->close();
                }
                $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
                $zip_link = "<b>Click <a href='".$path.$save_path.'/'.$zip_file_name."' class='downloadpdf download'>Here</a> to download the zip file that includes Digital Blockchain Documents.<b>";

                // $zip_link = public_path().$save_path.'/'.$zip_file_name;
            } else {
                $zip_link = '';
            }
            return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link,'zip_link'=>$zip_link,'startRow'=> $startRow,'endRow'=> $endRow,'highestRow'=> ($highestRow??$highestRow1)]);

        } elseif ($dropdown_template_id == 4) {
              $pdfData=array('studentDataOrg'=>$rowData1,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'dropdown_template_id'=>$dropdown_template_id,'points' => $points,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,'loader_token'=>$loader_token,'highestrow'=>(($highestRow1?$highestRow1:$highestRow)-1));
            //   dd($pdfData);

            //  if($startRow==3){
            //         dd($rowData1);
            //     }
            $link = $this->dispatch(new PdfGenerateAnuGradeCard2022Job($pdfData));
        }
         elseif ($dropdown_template_id == 5) {
              $pdfData=array('studentDataOrg'=>$rowData1,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'dropdown_template_id'=>$dropdown_template_id,'points' => $points,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,'loader_token'=>$loader_token,'highestrow'=>(($highestRow1?$highestRow1:$highestRow)-1));

            //$pdfData1 = array('studentDataOrg' => $rowData1, 'auth_site_id' => $auth_site_id, 'template_id' => $template_id, 'dropdown_template_id' => $dropdown_template_id, 'points' => $points, 'previewPdf' => $previewPdf, 'excelfile' => $excelfile, 'loader_token' => $loader_token);
                $pdfData1 = array('studentDataOrg' => $studentData[0], 'subjectDataOrg' => $studentData[1], 'auth_site_id' => $auth_site_id, 'template_id' => $template_id, 'dropdown_template_id' => $dropdown_template_id, 'points' => $points, 'previewPdf' => $previewPdf, 'excelfile' => $excelfile, 'loader_token' => $loader_token);
      

            $link = $this->dispatch(new PdfGenerateAnuAwardConvocationJob($pdfData));


            
            // Zip file creation
            if($request['previewPdf'] == 0 && $link!="Will be generated soon!") {
               
                if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
                }else{
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
                }
                $filenameWithoutExt = pathinfo($excelfile, PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '', $filenameWithoutExt); // Remove special chars
                $zip_file_name = $cleanName . '.zip';

                $pdfActualPath = public_path().'/'.$subdomain[0].'/backend/pdf_file';
                $zip = new \ZipArchive;
                
                if ($zip->open(public_path().$save_path.'/'.$zip_file_name,\ZipArchive::CREATE) === TRUE) {
                 
                   foreach ($rowDataLoop as $key => $value) {          // $value1 => array:1
                    
                        // echo public_path().$save_path.'/'.$value;
                        // echo "<br>";
                        $certName = str_replace("/", "_", $value[0]) .".pdf";

                        $zip->addfile($pdfActualPath.'/'.$certName,$certName);
                    
                }

                    $zip->close();
                }
                $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
                $zip_link = "<b>Click <a href='".$path.$save_path.'/'.$zip_file_name."' class='downloadpdf download'>Here</a> to download the zip file that includes Digital Blockchain Documents.<b>";

                // $zip_link = public_path().$save_path.'/'.$zip_file_name;
            } else {
                $zip_link = '';
            }
            return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link,'zip_link'=>$zip_link,'startRow'=> $startRow,'endRow'=> $endRow,'highestRow'=> ($highestRow??$highestRow1)]);



        }
        elseif ($dropdown_template_id == 6) {
              $pdfData=array('studentDataOrg'=>$rowData1,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'dropdown_template_id'=>$dropdown_template_id,'points' => $points,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,'loader_token'=>$loader_token,'highestrow'=>(($highestRow1?$highestRow1:$highestRow)-1));

            $pdfData1 = array('studentDataOrg' => $rowData1, 'auth_site_id' => $auth_site_id, 'template_id' => $template_id, 'dropdown_template_id' => $dropdown_template_id, 'points' => $points, 'previewPdf' => $previewPdf, 'excelfile' => $excelfile, 'loader_token' => $loader_token);
            //   dd($pdfData);

            $link = $this->dispatch(new PdfGenerateAnuFinalAFCACertificateJob($pdfData));
                //zip creation
             if($request['previewPdf'] == 0 && $link!="Will be generated soon!") {
               
                if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
                }else{
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
                }
                $filenameWithoutExt = pathinfo($excelfile, PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '', $filenameWithoutExt); // Remove special chars
                $zip_file_name = $cleanName . '.zip';

                $pdfActualPath = public_path().'/'.$subdomain[0].'/backend/pdf_file';
                $zip = new \ZipArchive;
                
                if ($zip->open(public_path().$save_path.'/'.$zip_file_name,\ZipArchive::CREATE) === TRUE) {
                    // dd($studentData[0]);
                   foreach ($rowDataLoop as $key => $value) {          // $value1 => array:1
                    
                        // echo public_path().$save_path.'/'.$value;
                        // echo "<br>";
                        $certName = str_replace("/", "_", $value[0]) .".pdf";

                        $zip->addfile($pdfActualPath.'/'.$certName,$certName);
                    
                }

                    $zip->close();
                }
                $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
                $zip_link = "<b>Click <a href='".$path.$save_path.'/'.$zip_file_name."' class='downloadpdf download'>Here</a> to download the zip file that includes Digital Blockchain Documents.<b>";

                // $zip_link = public_path().$save_path.'/'.$zip_file_name;
            } else {
                $zip_link = '';
            }
            return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link,'zip_link'=>$zip_link,'startRow'=> $startRow,'endRow'=> $endRow,'highestRow'=> ($highestRow??$highestRow1)]);
        }

        elseif($dropdown_template_id == 7){
        //$rowData1
           $headerRange = 'A1:' . $highestColumn1 . '1';
             $headerRow = $sheet1->rangeToArray($headerRange, NULL, TRUE, FALSE);

             $headers = array_map('trim', $headerRow[0]);
                // dd($rowData1);
             $finalData = [];

                foreach ($rowData1 as $row) {
                    // Make row length equal to header length
                    $row = array_pad($row, count($headers), null);

                    $finalData[] = array_combine($headers, $row);
                }
                // dd($finalData);
              $pdfData=array('studentDataOrg'=>$finalData,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'dropdown_template_id'=>$dropdown_template_id,'points' => $points,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,'loader_token'=>$loader_token,'highestrow'=>(($highestRow1?$highestRow1:$highestRow)-1));
            //   dd($pdfData);

            $link = $this->dispatch(new PdfGenerateAnuBTCCJob($pdfData));
              if($request['previewPdf'] == 0 && $link!="Will be generated soon!") {
               
                if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
                }else{
                    $save_path = '/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
                }
                $filenameWithoutExt = pathinfo($excelfile, PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '', $filenameWithoutExt); // Remove special chars
                $zip_file_name = $cleanName . '.zip';

                $pdfActualPath = public_path().'/'.$subdomain[0].'/backend/pdf_file';
                $zip = new \ZipArchive;
                
                if ($zip->open(public_path().$save_path.'/'.$zip_file_name,\ZipArchive::CREATE) === TRUE) {
                      $i=0;
                   foreach ($rowDataLoop as $key => $value) {          // $value1 => array:1
                    
                        // echo public_path().$save_path.'/'.$value;
                        // echo "<br>";
                        $certName = str_replace("/", "_", $value[$uniqueIdIndex]) .".pdf";

                        $zip->addfile($pdfActualPath.'/'.$certName,$certName);
                    //    $i++;
                      Log::info('Certificate added to ZIP', [
                            'file_name' => $certName,
                            'row_key'   => $key
                        ]);
                    }

                    $zip->close();
                    //    dd($i);
                }
                $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
                $zip_link = "<b>Click <a href='".$path.$save_path.'/'.$zip_file_name."' class='downloadpdf download'>Here</a> to download the zip file that includes Digital Blockchain Documents.<b>";

                // $zip_link = public_path().$save_path.'/'.$zip_file_name;
            } else {

                $zip_link = '';
            }
         
             return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link,'zip_link'=>$zip_link,'startRow'=> $startRow,'endRow'=> $endRow,'highestRow'=> ($highestRow??$highestRow1)]);
        }

        //return response()->json(['success' => true, 'message' => 'Certificates generated successfully.', 'link' => $link]);
          return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link,'startRow'=> $startRow,'endRow'=> $endRow,'highestRow'=> ($highestRow??$highestRow1),"rowData1"=>$rowData1]);
    }


    public function fetchArrayData($sheet1, $highestRow1)
    {
        $recordData = array();
        $subRecordData = array();
        $rowIndex = 0;
        $subIndex = 0;
        for ($row = 2; $row <= $highestRow1; $row++) {

            $previousColumn = $sheet1->getCellByColumnAndRow(6, $row - 1)->getValue();
            $rollNo = $sheet1->getCellByColumnAndRow(6, $row)->getValue();
            $sem = $sheet1->getCellByColumnAndRow(19, $row)->getValue();
            $yearMonth = $sheet1->getCellByColumnAndRow(16, $row)->getValue() . ' - ' . $sheet1->getCellByColumnAndRow(15, $row)->getValue();
            $semRef = $sheet1->getCellByColumnAndRow(230, $row)->getValue();
            $cgpa = $sheet1->getCellByColumnAndRow(28, $row)->getValue();
            $cgpaDescription = $sheet1->getCellByColumnAndRow(247, $row)->getValue();
            $ecp = $sheet1->getCellByColumnAndRow(27, $row)->getValue();
            $tec = $sheet1->getCellByColumnAndRow(26, $row)->getValue();
            $minor = $sheet1->getCellByColumnAndRow(246, $row)->getValue();
            $perctage = $sheet1->getCellByColumnAndRow(17, $row)->getValue();

            if ($rollNo == $previousColumn) {
                // after first line duplicate data

                // sub 1
                $incremNu = 10;
                for ($x11 = 33; $x11 <= 45; $x11++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x11, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 2
                $incremNu = 10;
                for ($x12 = 47; $x12 <= 59; $x12++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x12, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 3
                $incremNu = 10;
                for ($x13 = 60; $x13 <= 72; $x13++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x13, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 4
                $incremNu = 10;
                for ($x14 = 73; $x14 <= 85; $x14++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x14, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 5
                $incremNu = 10;
                for ($x15 = 86; $x15 <= 98; $x15++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x15, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 6
                $incremNu = 10;
                for ($x16 = 99; $x16 <= 111; $x16++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x16, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 7
                $incremNu = 10;
                for ($x17 = 112; $x17 <= 124; $x17++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x17, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 8
                $incremNu = 10;
                for ($x18 = 125; $x18 <= 137; $x18++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x18, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 9
                $incremNu = 10;
                for ($x19 = 138; $x19 <= 150; $x19++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x19, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 10
                $incremNu = 10;
                for ($x20 = 151; $x20 <= 163; $x20++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x20, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 11
                $incremNu = 10;
                for ($x21 = 164; $x21 <= 176; $x21++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x21, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 12
                $incremNu = 10;
                for ($x22 = 177; $x22 <= 189; $x22++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x22, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 13
                $incremNu = 10;
                for ($x23 = 190; $x23 <= 202; $x23++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x23, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 14
                $incremNu = 10;
                for ($x24 = 203; $x24 <= 215; $x24++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x24, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 15
                $incremNu = 10;
                for ($x25 = 216; $x25 <= 228; $x25++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x25, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                continue;
            } else {

                for ($x1 = 1; $x1 <= 247; $x1++) {
                    $recordData[$rowIndex][$x1] = $sheet1->getCellByColumnAndRow($x1, $row)->getValue();
                }
                $rowIndex++;

                $rollNo = $sheet1->getCellByColumnAndRow(6, $row)->getValue();
                $sem = $sheet1->getCellByColumnAndRow(19, $row)->getValue();
                $yearMonth = $sheet1->getCellByColumnAndRow(16, $row)->getValue() . ' - ' . $sheet1->getCellByColumnAndRow(15, $row)->getValue();
                $semRef = $sheet1->getCellByColumnAndRow(230, $row)->getValue();
                $cgpa = $sheet1->getCellByColumnAndRow(28, $row)->getValue();
                $cgpaDescription = $sheet1->getCellByColumnAndRow(247, $row)->getValue();
                $ecp = $sheet1->getCellByColumnAndRow(27, $row)->getValue();
                $tec = $sheet1->getCellByColumnAndRow(26, $row)->getValue();
                $minor = $sheet1->getCellByColumnAndRow(246, $row)->getValue();
                $perctage = $sheet1->getCellByColumnAndRow(17, $row)->getValue();
                // first line data
                // sub 1
                $incremNu = 10;
                for ($x11 = 33; $x11 <= 45; $x11++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x11, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 2
                $incremNu = 10;
                for ($x12 = 47; $x12 <= 59; $x12++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x12, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 3
                $incremNu = 10;
                for ($x13 = 60; $x13 <= 72; $x13++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x13, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 4
                $incremNu = 10;
                for ($x14 = 73; $x14 <= 85; $x14++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x14, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 5
                $incremNu = 10;
                for ($x15 = 86; $x15 <= 98; $x15++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x15, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 6
                $incremNu = 10;
                for ($x16 = 99; $x16 <= 111; $x16++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x16, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 7
                $incremNu = 10;
                for ($x17 = 112; $x17 <= 124; $x17++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x17, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 8
                $incremNu = 10;
                for ($x18 = 125; $x18 <= 137; $x18++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x18, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 9
                $incremNu = 10;
                for ($x19 = 138; $x19 <= 150; $x19++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x19, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 10
                $incremNu = 10;
                for ($x20 = 151; $x20 <= 163; $x20++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x20, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 11
                $incremNu = 10;
                for ($x21 = 164; $x21 <= 176; $x21++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x21, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 12
                $incremNu = 10;
                for ($x22 = 177; $x22 <= 189; $x22++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x22, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;

                // sub 13
                $incremNu = 10;
                for ($x23 = 190; $x23 <= 202; $x23++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x23, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 14
                $incremNu = 10;
                for ($x24 = 203; $x24 <= 215; $x24++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x24, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


                // sub 15
                $incremNu = 10;
                for ($x25 = 216; $x25 <= 228; $x25++) {
                    $subRecordData[$subIndex][0] = $rollNo;
                    $subRecordData[$subIndex][1] = $sem;
                    $subRecordData[$subIndex][2] = $yearMonth;
                    $subRecordData[$subIndex][3] = $semRef;
                    $subRecordData[$subIndex][4] = $cgpa;
                    $subRecordData[$subIndex][5] = $cgpaDescription;
                    $subRecordData[$subIndex][6] = $ecp;
                    $subRecordData[$subIndex][7] = $tec;
                    $subRecordData[$subIndex][8] = $minor;
                    $subRecordData[$subIndex][9] = $perctage;
                    $subRecordData[$subIndex][$incremNu] = $sheet1->getCellByColumnAndRow($x25, $row)->getValue();
                    $incremNu++;
                }
                $subIndex++;


            }

        }

        return array($recordData, $subRecordData);


    }

    public function updateData(){
        $students = StudentTable::select( 'id','serial_no', 'key')
        ->distinct()
        ->whereNotNull('bc_txn_hash')
        ->whereDate('created_at', '2024-11-14')
        ->where('template_id', 1)
        ->whereNull('pinata_ipfs_hash')
        ->get();
        // dd($students);
       foreach($students as $student){
            
            $apiResponse = DB::table('bc_api_tracker')
            ->where('request_parameters', 'like', "%{$student->key}%")
            ->where('status', 'success')
            ->limit(1)
            ->pluck('response') // This will return the response value directly
            ->first(); // Get the first (and only) result
          
            $json_response = json_decode( $apiResponse); 
            
            if(isset($json_response->pinataIpfsHash) && !empty($json_response->pinataIpfsHash)){
             
                $student->pinata_ipfs_hash = $json_response->pinataIpfsHash;
                //  $student->save();
                //  dd($student);
            }
            
       }
    }
    

    public function mintData(Request $request)
    {

        //   $encryptedKey="D089B186DD9C4AD2FFB30960DCE70793";
        //  echo $blockchainUrl = CoreHelper::getBCVerificationUrl(base64_encode($encryptedKey));

        // exit;
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        /**Deploy Contract**/
        // CoreHelper::checkContactAddress(1,$templateType='NORMALTEMPLATE');
        //  exit;
        /**End Deploy Contract**/

        $filename = "database_01_12_2025.xlsx";
        $pathImport = public_path() . '/' . $subdomain[0] . '/backend/blockchain/import/';
        $import_filename_import = $pathImport . $filename;
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
        $result = array();

        $template_id = 1;
        $admin_id = \Auth::guard('admin')->user()->toArray();
        $auth_site_id = Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing', 'printer_name')->where('site_id', $auth_site_id)->first();
        $template_data = TemplateMaster::select('id', 'template_name', 'is_block_chain', 'bc_document_description', 'bc_document_type', 'bc_contract_address')->where('id', $template_id)->first();


        // print_r($template_data);
        // exit;
        $template_type = 0;
        $certificate_type = "Degree Certificate";
        $log_serial_no = 1;
        $withoutExt = "Blockchain_" . date('Y_m_d_h_i_s');


        // exit;


        echo"<pre>";
        // print_r(1);
        // for ($excel_row = 578; $excel_row <= 583; $excel_row++)//$highestRow
        for ($excel_row = 2; $excel_row <= $highestRow; $excel_row++)//$highestRow
        {
            $rowData1 = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);

            print_r($rowData1[0]);
            exit;
            // dd(1);
            // exit;
            $studentID = $rowData1[0][9];
            // exit;
            // echo date('dmYHis');
            // exit;
            $dt = "20251119101200";//date('dmYHis');//"05032025132339"//"10102023121802" //"21112023124500" //
            //  $encryptedKey = strtoupper(md5($studentID . $dt));
             $encryptedKey = $rowData1[0][9];

            //$encryptedKey="D089B186DD9C4AD2FFB30960DCE70793";
            // echo $encryptedKey;

            // die();
            // $encryptedKey = $rowData1[0][7];
            // $blockchainUrl=CoreHelper::getBCVerificationUrl(encrypt($encryptedKey));
            $blockchainUrl = CoreHelper::getBCVerificationUrl(base64_encode($encryptedKey));
            //   $blockchainUrl=
            echo "<tr><td>".$studentID." </td><td>".$encryptedKey."</td><td>  ".$blockchainUrl."</td></tr>";
        // echo "Yes";
        //            if($excel_row==5){
        //             die();
        //            }

             echo "Yes";
           //  $serial_no = $studentID.'_N';
           //  $certName = $studentID.'_N.pdf';
           //  $urlRelativeFilePath = 'qr/'.$encryptedKey.'.png';
           //  $sts = '1';
           //  $datetime  = date("Y-m-d H:i:s");
           //  $ses_id  = 1;

           // $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$encryptedKey,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>'284','template_type'=>$template_type]);





            //  exit;

            //$result[] = array("student_id"=>$studentID,"encrypted_key"=>$encryptedKey,"blockchain_url"=>$blockchainUrl);
            //Blockchain fields
            // echo "<br>";
            //  echo "<br>";
            // $studentID = $studentID.'_N';
            $studentID = $studentID;
            $check_data = StudentTable::where('serial_no', $studentID)->where('status', 1)->first();
            // $check_data = StudentTable::select('id')->where('serial_no', $studentID)->where('status', 1)->first();
            
              $check_data=false;
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

            //if ($check_data) {
            if(!$check_dataFlag&&!empty($encryptedKey)&&!empty($certName)){
                echo "Yes";
                exit;
                $certName = str_replace("/", "_", $studentID) . ".pdf";
                $pdf_path = public_path() . '\\' . $subdomain[0] . '\backend\pdf_file\\' . $certName;
                
                if (file_exists($pdf_path)) {

                    $studentIDArr = explode('-', $rowData1[0][6]);
                    // print_r($studentIDArr);
                    // die();
                    // $str = "geeks"; 
                    $studentIDMeta = substr($studentIDArr[0], 1);

                    // echo "<br>";
                    // echo "<br>";

                    $mintData = array();
                    $mintData['documentType'] = "Certificate";
                    $mintData['description'] = "Educational Document";
                    $mintData['metadata1'] = ["label" => "Student ID", "value" => $studentIDMeta];
                    $mintData['metadata2'] = ["label" => "Student Name", "value" => $rowData1[0][0]];
                    $mintData['metadata3'] = ["label" => "Programme", "value" => $rowData1[0][1]];
                    $mintData['metadata4'] = ["label" => "Degree Certificate Sr. No.", "value" => $rowData1[0][9]];
                    $mintData['metadata5'] = ["label" => "University", "value" => "Anant National University"];

                    $mintData['uniqueHash'] = $encryptedKey;

                    $mintData['pdf_file'] = $pdf_path;
                    $mintData['template_id'] = $template_id;
                    $mintData['bc_contract_address'] = "0xaA31348eE47deF0A76a023a157d50035b0C9A204";
                    // $template_data['bc_contract_address'];



                        //  echo"<pre>";print_r($mintData);
                        // exit;
                    $response=CoreHelper::mintPDF($mintData);
                    
                    // echo "<pre>";
                    // print_r($response);

                    if ($response['status'] == 200) {
                        $bc_txn_hash = $response['txnHash'];

                        if (isset($response['ipfsHash'])) {
                            $bc_ipfs_hash = $response['ipfsHash'];
                        } else {
                            $bc_ipfs_hash = null;
                        }

                        if (isset($response['pinataIpfsHash'])) {
                            $pinataIpfsHash = $response['pinataIpfsHash'];
                        } else {
                            $pinataIpfsHash = null;
                        }
                        // echo"<pre>";print_r($pinataIpfsHash);
                        /*Add data to student table and printing details*/
                        $student_table_id = $this->addCertificateBlokchain($studentID, $certName, $template_id, $admin_id, $studentID, $template_type, $certificate_type, $bc_txn_hash, $bc_ipfs_hash, $encryptedKey,$pinataIpfsHash);
                        $username = $admin_id['username'];
                        date_default_timezone_set('Asia/Kolkata');
                        $print_datetime = date("Y-m-d H:i:s");
                        $print_count = $this->getPrintCount($studentID);
                        $printer_name = $systemConfig['printer_name'];
                        $print_serial_no = $this->nextPrintSerial();
                        $template_name = $template_data['template_name'];
                        $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $studentID, $template_name, $admin_id, $student_table_id, $studentID);
                        /*End Add data to student table and printing details*/

                        $content = "#" . $log_serial_no . " serial No :" . $studentID . " | " . date('Y-m-d H:i:s') . " | Success" . PHP_EOL;

                    } else {
                        $content = "#" . $log_serial_no . " serial No :" . $studentID . " | " . date('Y-m-d H:i:s') . " | Not deployed on blockchain network." . PHP_EOL;
                    }
                } else {
                    $content = "#" . $log_serial_no . " serial No :" . $studentID . " | " . date('Y-m-d H:i:s') . " | Pdf not found." . PHP_EOL;
                }

            } else {
                $content = "#" . $log_serial_no . " serial No :" . $studentID . " | " . date('Y-m-d H:i:s') . " | Data found in student table." . PHP_EOL;
            }


            /***Start Log Data***/
            if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                $file_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id;
            } else {
                $file_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id;
            }
            $fp = fopen($file_path . '/' . $withoutExt . ".txt", "a");
            fwrite($fp, $content);
            $log_serial_no++;
            /***End Log Data***/
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
      
        // $pathImport = public_path().'/'.$subdomain[0].'/blockchain/import/2024/';
        $filename = "database_01_12_2025.xlsx";
        $pathImport = public_path() . '/' . $subdomain[0] . '/backend/blockchain/import/';
        $import_filename_import = $pathImport . $filename;
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
        $template_id=100; //100 : Custom
        $admin_id = \Auth::guard('admin')->user()->toArray();
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        $template_data = TemplateMaster::select('id','template_name','is_block_chain','bc_document_description','bc_document_type','bc_contract_address')->where('id',$template_id)->first();
        // print_r($template_data);
        // exit;
        $template_type=2;
        //$certificate_type="Degree Certificate";
        $certificate_type="Certificate";
        $log_serial_no=1;
        $withoutExt="Blockchain_".date('Y_m_d_h_i_s');
        // $date=date('dmYHis');

        // $date="27102023130610";//Rank //Medal
        //$date="27102023134610";//PHD
        //$date="05072024172610";//Diploma
        $date="12032024183000";//Diploma
        $start_row = 122;
        $highestRow = 131; // 121
        $startTime = microtime(true);
        for($excel_row =$start_row; $excel_row <=$highestRow; $excel_row++)//$highestRow
        {

            
            // echo $highestRow;
            $rowData1 = $sheet->rangeToArray('A'. $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);
            //Student ID for php and other
            $studentID = $rowData1[0][9];
            $studentIDMeta = $rowData1[0][9];
            $serial_no = $rowData1[0][9];
            //For Rank / Medal with date
            
            // echo "<pre>";
            // print_r($rowData1);
            // echo "<br>";
            // die();

            // $encryptedKey

            // Get Student Data Using Sr No;
            $student_data  = StudentTable::where('serial_no',''.$serial_no)->where('status',1)->first();

            if(!$student_data) {
                break;
            }
            $encryptedKey = $student_data['key'];//for Diploma
            // $encryptedKey = $rowData1[0][21];//for Diploma
            
            $blockchainUrl=CoreHelper::getBCVerificationUrl(base64_encode(strtoupper(md5($encryptedKey))));
            
            
            $certName = $studentIDMeta . ".pdf";

            if(1==1){
            // if(!$check_dataFlag&&!empty($encryptedKey)&&!empty($certName)){
                //$certName = str_replace("/", "_", $studentID) .".pdf";
                $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;

                

                $s3Flag = 0;
                if (file_exists($pdf_path)) {
                    $pdf_path = $pdf_path;

                    echo "File in local server";
                    echo "<br>";
                }

                if(file_exists($pdf_path)){
                    
                    $mintData = array();
                    $mintData['documentType'] = "Certificate";
                    $mintData['description'] = "Educational Document";
                    $mintData['metadata1'] = ["label" => "Student ID", "value" => $studentIDMeta];
                    $mintData['metadata2'] = ["label" => "Student Name", "value" => $rowData1[0][0]];
                    $mintData['metadata3'] = ["label" => "Programme", "value" => $rowData1[0][1]];
                    $mintData['metadata4'] = ["label" => "Degree Certificate Sr. No.", "value" => $rowData1[0][9]];
                    $mintData['metadata5'] = ["label" => "University", "value" => "Anant National University"];

                    $mintData['uniqueHash'] = $encryptedKey;

                    $mintData['pdf_file'] = $pdf_path;
                    $mintData['template_id'] = $template_id;
                    $mintData['bc_contract_address'] = "0x0685eFf7B1D2217F466831d7c78Db3edC0d464F1";
                    // echo "<pre>";
                    // print_r($rowData1[0]);
                    // echo "<br>";
                    // print_r($mintData);
                    // echo "</pre>";die;
                    // echo "<br>";
                    // exit;
                    // $template_type = 0;
                    // $blockchain_type = 1;
                    $template_type = 2;
                    $blockchain_type = 1;
                    $response=CoreHelper::customMintPDF($mintData,$blockchain_type,$template_type);

                    $bc_file_hash=CoreHelper::generateFileHash($pdf_path);

                    // $response=CoreHelper::mintPDF($mintData);
                    // print_r($response);
                    // echo "<br>";
                    //    $response['status']=201;
                    
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
                        // echo $studentIDMeta;
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
                        
                        if($s3Flag == 1 || $s3Flag == '1') {
                            unlink($pdf_path);
                        }
                        
                        // $content = "#" . $log_serial_no . 
                        // " serial No :" . $studentIDMeta . 
                        // " | " . date('Y-m-d H:i:s') . 
                        // " | Success" . 
                        // " | Blockchain URL: " . $blockchainUrl . 
                        // PHP_EOL;

                        // $content = "#".$log_serial_no." serial No :".$studentIDMeta." | ".date('Y-m-d H:i:s')." | Success".PHP_EOL;
                        $content = "#" . $log_serial_no . 
                            " Serial No: " . $studentIDMeta . 
                            " | " . date('Y-m-d H:i:s') . 
                            " | Success" .
                            " | Blockchain URL: " . $blockchainUrl .
                            PHP_EOL;
                    }else{
                        $content = "#".$log_serial_no." serial No :".$studentIDMeta." | ".date('Y-m-d H:i:s')." | Not deployed on blockchain network.".PHP_EOL;
                    }
                }else{
                    $content = "#".$log_serial_no." serial No :".$studentIDMeta." | ".date('Y-m-d H:i:s')." | Pdf not found.".PHP_EOL;
                }

                
            }else{
                    $content = "#".$log_serial_no." serial No :".$studentIDMeta." | ".date('Y-m-d H:i:s')." | Data found in student table.".PHP_EOL;
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

        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        echo "Execution Time: " . $executionTime . " seconds";
        echo "</table>";
        //$sheet_name = 'MITWPUData_'. date('Y_m_d_H_i_s').'.xlsx'; 
    
        //return Excel::download(new MITWPUDataExport($result),$sheet_name,'Xlsx');
            
    }

    public function addCertificateBlokchain($serial_no, $certName, $template_id, $admin_id, $unique_no, $template_type, $certificate_type, $bc_txn_hash, $bc_ipfs_hash, $encryptedKey,$pinataIpfsHash)
    {


        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);


        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();

        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path() . '/backend/qr';
        //$key = strtoupper(md5($unique_no)); 
        $key = $encryptedKey;
        $codeContents = $key;
        $fileName = $key . '.png';

        $urlRelativeFilePath = 'qr/' . $fileName;
        $auth_site_id = \Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
        // Mark all previous records of same serial no to inactive if any
        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
            $resultu = SbStudentTable::where('serial_no', $unique_no)->update(['status' => '0']);
            // Insert the new record

            $result = SbStudentTable::create(['serial_no' => $unique_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        } else {

            $check_data = StudentTable::select('id')->where('serial_no', $studentID)->first();
            if ($check_data) {
                $resultu = StudentTable::where('serial_no', $unique_no)->update(['status' => '0']);
            }

            // Insert the new record
            // echo"<pre> 2";print_r($pinataIpfsHash);
            $result = StudentTable::create(['serial_no' => $unique_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'template_type' => $template_type, 'certificate_type' => $certificate_type, 'bc_txn_hash' => $bc_txn_hash, 'bc_ipfs_hash' => $bc_ipfs_hash,'pinata_ipfs_hash'=>$pinataIpfsHash,'bc_sc_id'=>3]);
        }

        return $result['id'];
    }

    public function addCertificate($serial_no, $certName, $dt, $template_id, $admin_id, $blob)
    {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $file1 = public_path() . '/backend/temp_pdf_file/' . $certName;
        $file2 = public_path() . '/backend/pdf_file/' . $certName;

        $auth_site_id = Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        $pdfActualPath = public_path() . '/' . $subdomain[0] . '/backend/pdf_file/' . $certName;


        copy($file1, $file2);

        $aws_qr = \File::copy($file2, $pdfActualPath);


        @unlink($file2);

        @unlink($file1);



        $sts = '1';
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();

        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path() . '/backend/qr';
        $key = strtoupper(md5($serial_no));
        $codeContents = $key;
        $fileName = $key . '.png';

        $urlRelativeFilePath = 'qr/' . $fileName;

        if ($systemConfig['sandboxing'] == 1) {
            $resultu = SbStudentTable::where('serial_no', 'T-' . $serial_no)->update(['status' => '0']);
            // Insert the new record

            $result = SbStudentTable::create(['serial_no' => 'T-' . $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        } else {
            $resultu = StudentTable::where('serial_no', $serial_no)->update(['status' => '0']);
            // Insert the new record

            $result = StudentTable::create(['serial_no' => $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        }

    }

    public function getPrintCount($serial_no)
    {
        $auth_site_id = Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        $numCount = PrintingDetail::select('id')->where('sr_no', $serial_no)->count();

        return $numCount + 1;
    }

    public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no, $template_name, $admin_id, $card_serial_no)
    {
        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];

        $auth_site_id = Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        if ($systemConfig['sandboxing'] == 1) {
            $result = PrintingDetail::create(['username' => $username, 'print_datetime' => $print_datetime, 'printer_name' => $printer_name, 'print_count' => $printer_count, 'print_serial_no' => $print_serial_no, 'sr_no' => 'T-' . $sr_no, 'template_name' => $template_name, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'publish' => 1]);
        } else {
            $result = PrintingDetail::create(['username' => $username, 'print_datetime' => $print_datetime, 'printer_name' => $printer_name, 'print_count' => $printer_count, 'print_serial_no' => $print_serial_no, 'sr_no' => $sr_no, 'template_name' => $template_name, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'publish' => 1]);
        }
    }

    public function nextPrintSerial()
    {
        $current_year = 'PN/' . $this->getFinancialyear() . '/';
        // find max
        $maxNum = 0;

        $auth_site_id = Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        $result = \DB::select("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num "
            . "FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '$current_year'");
        //get next num
        $maxNum = $result[0]->next_num + 1;

        return $current_year . $maxNum;
    }

    public function getNextCardNo($template_name)
    {
        $auth_site_id = Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        if ($systemConfig['sandboxing'] == 1) {
            $result = \DB::select("SELECT * FROM sb_card_serial_numbers WHERE template_name = '$template_name'");
        } else {
            $result = \DB::select("SELECT * FROM card_serial_numbers WHERE template_name = '$template_name'");
        }

        return $result[0];
    }

    public function updateCardNo($template_name, $count, $next_serial_no)
    {
        $auth_site_id = Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        if ($systemConfig['sandboxing'] == 1) {
            $result = \DB::select("UPDATE sb_card_serial_numbers SET card_count='$count',next_serial_no='$next_serial_no' WHERE template_name = '$template_name'");
        } else {
            $result = \DB::select("UPDATE card_serial_numbers SET card_count='$count',next_serial_no='$next_serial_no' WHERE template_name = '$template_name'");
        }

        return $result;
    }


    public function getFinancialyear()
    {
        $yy = date('y');
        $mm = date('m');
        $fy = str_pad($yy, 2, "0", STR_PAD_LEFT);
        if ($mm > 3)
            $fy = $fy . "-" . ($yy + 1);
        else
            $fy = str_pad($yy - 1, 2, "0", STR_PAD_LEFT) . "-" . $fy;
        return $fy;
    }

    public function createTemp($path)
    {
        //create ghost image folder
        $tmp = date("ymdHis");

        $tmpname = tempnam($path, $tmp);
        //unlink($tmpname);
        //mkdir($tmpname);
        if (file_exists($tmpname)) {
            unlink($tmpname);
        }
        mkdir($tmpname, 0777);
        return $tmpname;
    }

    public function CreateMessage($tmpDir, $name = "", $font_size, $print_color)
    {
        if ($name == "")
            return;
        $name = strtoupper($name);
        // Create character image
        if ($font_size == 15 || $font_size == "15") {


            $AlphaPosArray = array(
                "A" => array(0, 825),
                "B" => array(825, 840),
                "C" => array(1665, 824),
                "D" => array(2489, 856),
                "E" => array(3345, 872),
                "F" => array(4217, 760),
                "G" => array(4977, 848),
                "H" => array(5825, 896),
                "I" => array(6721, 728),
                "J" => array(7449, 864),
                "K" => array(8313, 840),
                "L" => array(9153, 817),
                "M" => array(9970, 920),
                "N" => array(10890, 728),
                "O" => array(11618, 944),
                "P" => array(12562, 736),
                "Q" => array(13298, 920),
                "R" => array(14218, 840),
                "S" => array(15058, 824),
                "T" => array(15882, 816),
                "U" => array(16698, 800),
                "V" => array(17498, 841),
                "W" => array(18339, 864),
                "X" => array(19203, 800),
                "Y" => array(20003, 824),
                "Z" => array(20827, 876)
            );

            $filename = public_path() . "/backend/canvas/ghost_images/F15_H14_W504.png";

            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((14 * $currentX) / $size[1]);

        } else if ($font_size == 12) {

            $AlphaPosArray = array(
                "A" => array(0, 849),
                "B" => array(849, 864),
                "C" => array(1713, 840),
                "D" => array(2553, 792),
                "E" => array(3345, 872),
                "F" => array(4217, 776),
                "G" => array(4993, 832),
                "H" => array(5825, 880),
                "I" => array(6705, 744),
                "J" => array(7449, 804),
                "K" => array(8273, 928),
                "L" => array(9201, 776),
                "M" => array(9977, 920),
                "N" => array(10897, 744),
                "O" => array(11641, 864),
                "P" => array(12505, 808),
                "Q" => array(13313, 804),
                "R" => array(14117, 904),
                "S" => array(15021, 832),
                "T" => array(15853, 816),
                "U" => array(16669, 824),
                "V" => array(17493, 800),
                "W" => array(18293, 909),
                "X" => array(19202, 800),
                "Y" => array(20002, 840),
                "Z" => array(20842, 792)

            );

            $filename = public_path() . "/backend/canvas/ghost_images/F12_H8_W288.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((8 * $currentX) / $size[1]);

        } else if ($font_size == "10" || $font_size == 10) {
            $AlphaPosArray = array(
                "A" => array(0, 700),
                "B" => array(700, 757),
                "C" => array(1457, 704),
                "D" => array(2161, 712),
                "E" => array(2873, 672),
                "F" => array(3545, 664),
                "G" => array(4209, 752),
                "H" => array(4961, 744),
                "I" => array(5705, 616),
                "J" => array(6321, 736),
                "K" => array(7057, 784),
                "L" => array(7841, 673),
                "M" => array(8514, 752),
                "N" => array(9266, 640),
                "O" => array(9906, 760),
                "P" => array(10666, 664),
                "Q" => array(11330, 736),
                "R" => array(12066, 712),
                "S" => array(12778, 664),
                "T" => array(13442, 723),
                "U" => array(14165, 696),
                "V" => array(14861, 696),
                "W" => array(15557, 745),
                "X" => array(16302, 680),
                "Y" => array(16982, 728),
                "Z" => array(17710, 680)

            );

            $filename = public_path() . "/backend/canvas/ghost_images/F10_H5_W180.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((5 * $currentX) / $size[1]);

        } else if ($font_size == 11) {

            $AlphaPosArray = array(
                "A" => array(0, 833),
                "B" => array(833, 872),
                "C" => array(1705, 800),
                "D" => array(2505, 888),
                "E" => array(3393, 856),
                "F" => array(4249, 760),
                "G" => array(5009, 856),
                "H" => array(5865, 896),
                "I" => array(6761, 744),
                "J" => array(7505, 832),
                "K" => array(8337, 887),
                "L" => array(9224, 760),
                "M" => array(9984, 920),
                "N" => array(10904, 789),
                "O" => array(11693, 896),
                "P" => array(12589, 776),
                "Q" => array(13365, 904),
                "R" => array(14269, 784),
                "S" => array(15053, 872),
                "T" => array(15925, 776),
                "U" => array(16701, 832),
                "V" => array(17533, 824),
                "W" => array(18357, 872),
                "X" => array(19229, 806),
                "Y" => array(20035, 832),
                "Z" => array(20867, 848)

            );

            $filename = public_path() . "/backend/canvas/ghost_images/F11_H7_W250.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);


            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((7 * $currentX) / $size[1]);

        } else if ($font_size == "13" || $font_size == 13) {

            $AlphaPosArray = array(
                "A" => array(0, 865),
                "B" => array(865, 792),
                "C" => array(1657, 856),
                "D" => array(2513, 888),
                "E" => array(3401, 768),
                "F" => array(4169, 864),
                "G" => array(5033, 824),
                "H" => array(5857, 896),
                "I" => array(6753, 784),
                "J" => array(7537, 808),
                "K" => array(8345, 877),
                "L" => array(9222, 664),
                "M" => array(9886, 976),
                "N" => array(10862, 832),
                "O" => array(11694, 856),
                "P" => array(12550, 776),
                "Q" => array(13326, 896),
                "R" => array(14222, 816),
                "S" => array(15038, 784),
                "T" => array(15822, 816),
                "U" => array(16638, 840),
                "V" => array(17478, 794),
                "W" => array(18272, 920),
                "X" => array(19192, 808),
                "Y" => array(20000, 880),
                "Z" => array(20880, 800)

            );


            $filename = public_path() . "/backend/canvas/ghost_images/F13_H10_W360.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);

            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX) / $size[1]);

        } else if ($font_size == "14" || $font_size == 14) {

            $AlphaPosArray = array(
                "A" => array(0, 833),
                "B" => array(833, 872),
                "C" => array(1705, 856),
                "D" => array(2561, 832),
                "E" => array(3393, 832),
                "F" => array(4225, 736),
                "G" => array(4961, 892),
                "H" => array(5853, 940),
                "I" => array(6793, 736),
                "J" => array(7529, 792),
                "K" => array(8321, 848),
                "L" => array(9169, 746),
                "M" => array(9915, 1024),
                "N" => array(10939, 744),
                "O" => array(11683, 864),
                "P" => array(12547, 792),
                "Q" => array(13339, 848),
                "R" => array(14187, 872),
                "S" => array(15059, 808),
                "T" => array(15867, 824),
                "U" => array(16691, 872),
                "V" => array(17563, 736),
                "W" => array(18299, 897),
                "X" => array(19196, 808),
                "Y" => array(20004, 880),
                "Z" => array(80884, 808)

            );

            $filename = public_path() . "/backend/canvas/ghost_images/F14_H12_W432.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((12 * $currentX) / $size[1]);

        } else {
            $AlphaPosArray = array(
                "A" => array(0, 944),
                "B" => array(943, 944),
                "C" => array(1980, 944),
                "D" => array(2923, 944),
                "E" => array(3897, 944),
                "F" => array(4840, 753),
                "G" => array(5657, 943),
                "H" => array(6694, 881),
                "I" => array(7668, 504),
                "J" => array(8265, 692),
                "K" => array(9020, 881),
                "L" => array(9899, 944),
                "M" => array(10842, 944),
                "N" => array(11974, 724),
                "O" => array(12916, 850),
                "P" => array(13859, 850),
                "Q" => array(14802, 880),
                "R" => array(15776, 944),
                "S" => array(16719, 880),
                "T" => array(17599, 880),
                "U" => array(18479, 880),
                "V" => array(19485, 880),
                "W" => array(20396, 1038),
                "X" => array(21465, 944),
                "Y" => array(22407, 880),
                "Z" => array(23287, 880)
            );

            $filename = public_path() . "/backend/canvas/ghost_images/ALPHA_GHOST.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);

            // Create Backgoround image
            $filename = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX) / $size[1]);
        }
    }

    function GetStringPositions($strings, $pdf)
    {
        $len = count($strings);
        $w = array();
        $sum = 0;
        foreach ($strings as $key => $str) {
            $width = $pdf->GetStringWidth($str[0], $str[1], $str[2], $str[3], false);
            $w[] = $width;
            $sum += intval($width);

        }

        $ret = array();
        $ret[0] = (205 - $sum) / 2;
        for ($i = 1; $i < $len; $i++) {
            $ret[$i] = $ret[$i - 1] + $w[$i - 1];

        }

        return $ret;
    }

    function sanitizeQrString($content)
    {
        $find = array('â€œ', 'â€™', 'â€¦', 'â€”', 'â€“', 'â€˜', 'Ã©', 'Â', 'â€¢', 'Ëœ', 'â€'); // en dash
        $replace = array('“', '’', '…', '—', '–', '‘', 'é', '', '•', '˜', '”');
        return $content = str_replace($find, $replace, $content);
    }
}
