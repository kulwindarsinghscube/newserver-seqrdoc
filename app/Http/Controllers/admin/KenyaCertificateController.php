<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\TemplateMaster;
use App\models\SuperAdmin;
use Session,TCPDF,TCPDF_FONTS,Auth,DB;
use App\Http\Requests\ExcelValidationRequest;
use App\Http\Requests\MappingDatabaseRequest;
use App\Http\Requests\TemplateMapRequest;
use App\Http\Requests\TemplateMasterRequest;
use App\Imports\TemplateMapImport
;use App\Imports\TemplateMasterImport;
use App\Jobs\PDFGenerateJob;
use App\models\BackgroundTemplateMaster;
use App\Events\BarcodeImageEvent;
use App\Events\TemplateEvent;
use App\models\FontMaster;
use App\models\FieldMaster;
use App\models\User;
use App\models\StudentTable;
use App\models\SbStudentTable;
use Maatwebsite\Excel\Facades\Excel;
use App\models\SystemConfig;
use App\Jobs\PreviewPDFGenerateJob;
use App\Exports\TemplateMasterExport;
use Storage;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use App\models\Config;
use App\models\SbPrintingDetail;
use App\models\PrintingDetail;
use App\models\ExcelUploadHistory;
use App\models\SbExceUploadHistory;
//use Illuminate\Support\Facades\Storage;
use App\Helpers\CoreHelper;
use Helper;
use App\Jobs\ValidateExcelKenyaJob;
use App\Jobs\PdfGenerateKenyaJob;
use App\Jobs\ValidateExcelKenyaPassingJob;
use App\Jobs\PdfGenerateKenyaPassingJob;

class KenyaCertificateController extends Controller
{
    public function index(Request $request)
    {
       return view('admin.kenya.index');
    }

    public function uploadpage(){

      return view('admin.kenya.index');
    }

    public function validateExcel(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        $template_id=100;
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
         //check file is uploaded or not
        if($request->hasFile('field_file')){
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile =  date("YmdHis") . "_" . $file_name;
            $target_path = public_path().'/backend/canvas/dummy_images/'.$template_id;
            $fullpath = $target_path.'/'.$excelfile;

            if(!is_dir($target_path)){
                mkdir($target_path, 0777);
            }

            if($request['field_file']->move($target_path,$excelfile)){
                //get excel file data
                // dd('hi');
                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
                }
                $auth_site_id=Auth::guard('admin')->user()->site_id;

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                if($get_file_aws_local_flag->file_aws_local == '1'){
                    // add sandboxing funcationlity
                    CoreHelper::sandboxingFileAWS($systemConfig,$fullpath,$template_id,$excelfile);
                    
                }
                else{
                    // add sandboxing funcationlity
                    CoreHelper::sandboxingFile($systemConfig,$fullpath,$template_id,$excelfile);

                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);
                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();
                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                // $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
                
                //For checking certificate limit updated by Mandar
                $recordToGenerate=$highestRow1-1;
                $checkStatus = CoreHelper::checkMaxCertificateLimit($recordToGenerate);
                if(!$checkStatus['status']){
                  return response()->json($checkStatus);
                }

                // $objPHPExcel2 = $objReader->load($fullpath);
                // $sheet2 = $objPHPExcel2->getSheet(1);
                // $highestColumn2 = $sheet2->getHighestColumn();
                // $highestRow2 = $sheet2->getHighestDataRow();
                // $rowData2 = $sheet2->rangeToArray('A1:' . $highestColumn2 . $highestRow2, NULL, TRUE, FALSE);
               
                $excelData=array('rowData1'=>$rowData1,'rowData2'=>$rowData1,'auth_site_id'=>$auth_site_id);
                $response = $this->dispatch(new ValidateExcelKenyaJob($excelData));

                $responseData =$response->getData();
                //print_r($responseData);
                if($responseData->success){
                    $old_rows=$responseData->old_rows;
                    $new_rows=$responseData->new_rows;
                }else{
                   return $response;
                }
              
            }

            //echo $fullpath;
            if (file_exists($fullpath)) {
                unlink($fullpath);
            }
            
        return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);

        }
        else{
            return response()->json(['success'=>false,'message'=>'File not found!']);
        }


    }
    
    public function uploadfile(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
       // $start_time = microtime(true); 
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        //Blockchain data
        //Generate id once and define to contractAddress variable
        //CoreHelper::checkContactAddress(100,$templateType='CUSTOMTEMPLATE');
        $contractAddress="0x9b2bBB33CB0C72d9A1Cb7c375851Da5da1b0591F";
        $isBlockChain=1;
       
        $template_id = 100;
        
        $previewPdf = array($request['previewPdf'],$request['previewWithoutBg']);
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        //check file is uploaded or not
        if($request->hasFile('field_file')){
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile =  date("YmdHis") . "_" . $file_name;
            $target_path = public_path().'/backend/canvas/dummy_images/'.$template_id;
            $fullpath = $target_path.'/'.$excelfile;

            if(!is_dir($target_path)){
                
                mkdir($target_path, 0777);
            }

            if($request['field_file']->move($target_path,$excelfile)){
                //get excel file data
                // dd('hi');
                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
                }
                

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                if($get_file_aws_local_flag->file_aws_local == '1'){
                    CoreHelper::sandboxingFileAWS($systemConfig,$fullpath,$template_id,$excelfile);
                    
                }
                else{
                    // add sandboxing funcationlity
                    CoreHelper::sandboxingFile($systemConfig,$fullpath,$template_id,$excelfile);

                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);
                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();
                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                // $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
                
                // $objPHPExcel2 = $objReader->load($fullpath);
                // $sheet2 = $objPHPExcel2->getSheet(1);
                // $highestColumn2 = $sheet2->getHighestColumn();
                // $highestRow2 = $sheet2->getHighestDataRow();
                // $rowData2 = $sheet2->rangeToArray('A1:' . $highestColumn2 . $highestRow2, NULL, TRUE, FALSE);
                // $rowData2 = $sheet2->rangeToArray('A1:' . $highestColumn2 . '1', NULL, TRUE, FALSE);
                // foreach ($rowData[0] as $key => $value) {

            }
               
            
                       
        }
        else{
            return response()->json(['success'=>false,'message'=>'File not found!']);
        }

     
        unset($rowData1[0]);
        unset($rowData1[0]);
        $rowData1=array_values($rowData1);
        $rowData2=array_values($rowData1);
        //store ghost image
        //$tmpDir = $this->createTemp(public_path().'/backend/images/ghosttemp/temp');
        $admin_id = \Auth::guard('admin')->user()->toArray();
 
        
        $pdfData=array('studentDataOrg'=>$rowData1,'subjectsDataOrg'=>$rowData2,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile,"contractAddress"=>$contractAddress,"isBlockChain"=>$isBlockChain);
        $link = $this->dispatch(new PdfGenerateKenyaJob($pdfData));
        /*// End clock time in seconds 
        $end_time = microtime(true);
        // Calculate script execution time 
        $execution_time = ($end_time - $start_time); 
  
        echo " Execution time of script = ".$execution_time." sec";*/
        return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link]);
    }


    public function certificateGenerate($studentDataOrg,$subjectsDataOrg,$template_id,$previewPdf,$excelfile){
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
        $admin_id = \Auth::guard('admin')->user()->toArray();
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $auth_site_id=Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        // dd($systemConfig);
        $printer_name = $systemConfig['printer_name'];

        //Separate students subjects
        $subjectsArr = array();
        foreach ($subjectsDataOrg as $element) {
            $subjectsArr[$element[0]][] = $element;
        }


        $ghostImgArr = array();
        $pdfBig = new TCPDF('L', 'mm', array('297', '420'), true, 'UTF-8', false);
        $pdfBig->SetCreator(PDF_CREATOR);
        $pdfBig->SetAuthor('TCPDF');
        $pdfBig->SetTitle('Certificate');
        $pdfBig->SetSubject('');

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
       // $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $log_serial_no = 1;
        $cardDetails=$this->getNextCardNo('Kenya-C');
        $card_serial_no=$cardDetails->next_serial_no;
          
        foreach ($studentDataOrg as $studentData) {
         
         if($card_serial_no<9999&&$previewPdf!=1){
            echo "<h5>Your card series ended...!</h5>";
            exit;
         }

         $pdfBig->AddPage();
         $pdfBig->SetFont($arial, '', 8, '', false);
        //set background image
        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\Kenya statment of marks_BG.jpg';
        // $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\DO_Background Light.jpg';
        // dd($template_img_generate);


        

        if($previewPdf==1){
            if($previewWithoutBg!=1){
                $pdfBig->Image($template_img_generate, 0, 0, '420', '297', "JPG", '', 'R', true);
            }

            $date_font_size = '11';
        $date_nox = 13;
        $date_noy = 37;
        $date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
        $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
        $pdfBig->SetTextColor(192,192,192);
        $pdfBig->SetXY($date_nox, $date_noy);
        $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetFont($arial, '', 8, '', false);
        }
        $pdfBig->setPageMark();


            $subjectsData=$subjectsArr[$studentData[3]];
            //Separate semesters 
            $subjects = array();
            foreach ($subjectsData as $element) {
                $subjects[$element[1]][] = $element;
            }
            ksort($subjects);
 

        $ghostImgArr = array();
        $pdf = new TCPDF('L', 'mm', array('297', '420'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

        //set fonts
        /*$arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);*/

        $pdf->AddPage();
        
         $print_serial_no = $this->nextPrintSerial();
        //set background image
        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\Kenya statment of marks_BG.jpg';
        // $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\DO_Background Light.jpg';
        // dd($template_img_generate);
        if($previewPdf!=1){
        $pdf->Image($template_img_generate, 0, 0, '420', '297', "JPG", '', 'R', true);
        }
        $pdf->setPageMark();

        if($previewPdf!=1){
        $fontEBPath=public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\E-13B_0.php';
        $pdf->AddFont('E-13B_0', '', $fontEBPath);
        $pdfBig->AddFont('E-13B_0', '', $fontEBPath);
         //set enrollment no
        $card_serial_no_font_size = '13';
        $card_serial_nox= 172.5;//172.5
        $card_serial_noy = 35.5;
        $pdf->SetFont('E-13B_0', '', $card_serial_no_font_size, '', false);
        $pdf->SetXY($card_serial_nox, $card_serial_noy);
        $pdf->Cell(23.5, 0, $card_serial_no, 0, false, 'R');


        $pdfBig->SetFont('E-13B_0', '', $card_serial_no_font_size, '', false);
        $pdfBig->SetXY($card_serial_nox, $card_serial_noy);
        $pdfBig->Cell(23.5, 0, $card_serial_no, 0, false, 'R');

        

        $card_serial_noxx= 375.5;
        $card_serial_noyy = 35.5;
        $pdf->SetFont('E-13B_0', '', $card_serial_no_font_size, '', false);
        $pdf->SetXY($card_serial_noxx, $card_serial_noyy);
        $pdf->Cell(0, 0, $card_serial_no, 0, false, 'L');


        $pdfBig->SetFont('E-13B_0', '', $card_serial_no_font_size, '', false);
        $pdfBig->SetXY($card_serial_noxx, $card_serial_noyy);
        $pdfBig->Cell(0, 0, $card_serial_no, 0, false, 'L');

        }
        $pdf->SetFont($arial, '', 8, '', false);
        $pdfBig->SetFont($arial, '', 8, '', false);

        $semRomanArr=array(1=>"I",2=>"II",3=>"III",4=>"IV",5=>"V",6=>"VI");

        if($studentData[6]=="MASTER OF COMMERCE"){
            $fontSizeCource=11;
        }else{
            $fontSizeCource=8;
        }

        $html = <<<EOD
<style>
 td{
 
  font-size:8px; 
 border-top: 0px solid black;
    border-bottom: 0px solid black;
}
#table1 {
    
  border-collapse: collapse;
}
</style>
<table id="table1" cellspacing="0" cellpadding="2" border="0.1" width="46.4%" rules="rows">
   <tr>
    <td colspan="2" style="width:52%;"> Student Name :  <b>{$studentData[2]} </b></td>
    <td style="width:48%;"> Mother's Name :  <b>{$studentData[5]} </b></td>
   </tr>
   <tr>
    <td style="width:19.1%;"> Examination Programme :</td>
    <td style="width:47.57%;"><b>{$studentData[6]}</b></td>
    <td style="width:33.33%;"> Year : <b>{$studentData[7]}</b></td>
   </tr>
   <tr>
    <td style="width:33.34%;"> Permanent Reg . No : <b>{$studentData[3]}</b></td>
    <td style="text-align:center;width:33.33%;"> Seat Number : <b>{$studentData[0]}</b></td>
    <td style="width:33.33%;"> UID. No : <b>{$studentData[4]}</b></td>
   </tr>
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='12', $y='46', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

$pdfBig->writeHTMLCell($w=0, $h=0, $x='12', $y='46', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
$linesLoopMaxL=42;
$linesLoopMaxR=77;
if(strlen($studentData[6])>=38){
    $offset=3; 
    $lineOffset=1;
    $linesLoopMaxR=76;
}else{
    $offset=0;
    $lineOffset=0;
}

$html = <<<EOD
<style>
 td{
 
  font-size:8px; 

}
#table111 {
    
  border-collapse: collapse;
}
</style>
<table id="table111" cellspacing="0" cellpadding="2" border="0" width="46.4%">
  <tr>
    <td>Special Subject : <b>{$studentData[8]}</b></td>
    </tr>
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='12', $y=61.7+$offset, $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

$pdfBig->writeHTMLCell($w=0, $h=0, $x='12', $y=61.7+$offset, $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

        $html = <<<EOD
<style>
 td{
 
  font-size:8px; 
 border-top: 0px solid black;
    border-bottom: 0px solid black;
}
#table11 {
    
  border-collapse: collapse;
}
#block_container
{
    text-align:center;
}
#bloc1, #bloc2
{
    display:inline;
}
</style>
<table id="table11" cellspacing="0" cellpadding="2" border="0.1" width="98.5%" rules="rows">
 <tr>
    <td colspan="2" style="width:52%;"> Student Name :  <b>{$studentData[2]} </b></td>
    <td style="width:48%;"> Mother's Name :  <b>{$studentData[5]} </b></td>
   </tr>
   <tr>
    <td style="width:19.1%;"> Examination Programme :</td>
    <td style="width:47.57%;"><b>{$studentData[6]}</b></td>
    <td style="width:33.33%;"> Year : <b>{$studentData[7]}</b></td>
   </tr>
   <tr>
    <td style="width:33.34%;"> Permanent Reg . No : <b>{$studentData[3]}</b></td>
    <td style="text-align:center;width:33.33%;"> Seat Number : <b>{$studentData[0]}</b></td>
    <td style="width:33.33%;"> UID. No : <b>{$studentData[4]}</b></td>
   </tr>
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='222', $y='46', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
$pdfBig->writeHTMLCell($w=0, $h=0, $x='222', $y='46', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);


$lineCount=0;
$strtdL="";
$strtdR="";
$specialSubArr=array();
foreach ($subjects as $key => $semesterData) {

    if($studentData[6]=="MASTER OF COMMERCE"&&$key==4){

        $lineCountPrev=$lineCount;
        
       
            for ($p=$lineCountPrev; $p < 31; $p++) {  //40 35 32
                 $strtdL .= '<tr>
                            <td style="width:11%;text-align:center;"></td>
                            <td style="width:39%;text-align:left;"></td>
                            <td style="width:5%;text-align:center;"></td>
                            <td style="width:5%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:5%;text-align:center;"></td>
                            </tr>';
            }
        

        $lineCount=56; //47 52 56


    }elseif($studentData[6]!="MASTER OF COMMERCE"&&$key==5){

        $lineCountPrev=$lineCount;
        
       
            for ($p=$lineCountPrev; $p < 42; $p++) { //44 
                 $strtdL .= '<tr>
                            <td style="width:11%;text-align:center;"></td>
                            <td style="width:39%;text-align:left;"></td>
                            <td style="width:5%;text-align:center;"></td>
                            <td style="width:5%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:7%;text-align:center;"></td>
                            <td style="width:5%;text-align:center;"></td>
                            </tr>';
            }
        

        $lineCount=43;

    }


    if($lineCount<($linesLoopMaxL)){    
      $strtdL .= '<tr>
    <td style="width:11%;text-align:right;"><b>SEM '.$semRomanArr[$key].'</b></td>
    <td style="width:39%;text-align:left;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    </tr>';
    }else{
       $strtdR .= '<tr>
    <td style="width:11%;text-align:right;"><b>SEM '.$semRomanArr[$key].'</b></td>
    <td style="width:39%;text-align:left;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    </tr>'; 
    }
    $lineCount=$lineCount+1;

    foreach ($semesterData as $readSubject) {


        if($readSubject[4]==="-"&&$readSubject[5]==="-"&&$readSubject[6]==="-"&&$readSubject[10]==="-"){
            array_push($specialSubArr, $readSubject);
            
        }else{
        if(strlen($readSubject[3])>34){
            $lineCount=$lineCount+1.64;
        }else{
            $lineCount=$lineCount+1;
        }

        if($lineCount<($linesLoopMaxL)){
         $strtdL .= '<tr>
                    <td style="width:11%;text-align:right;"><b>'.$readSubject[2].'</b></td>
                    <td style="width:39%;text-align:left;"><b>'.$readSubject[3].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[4].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[5].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[6].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[7].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[8].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[9].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[10].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[11].'</b></td>
                   </tr>';
        }else{

         $strtdR .= '<tr>
                    <td style="width:11%;text-align:right;"><b>'.$readSubject[2].'</b></td>
                    <td style="width:39%;text-align:left;"><b>'.$readSubject[3].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[4].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[5].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[6].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[7].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[8].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[9].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[10].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[11].'</b></td>
                   </tr>';
        }

    }

    }
}

foreach ($specialSubArr as $readSubject) {
    if(strlen($readSubject[3])>34){
            $lineCount=$lineCount+1.64;
        }else{
            $lineCount=$lineCount+1;
        }
   $strtdR .= '<tr>
                    <td style="width:11%;text-align:right;"><b>'.$readSubject[2].'</b></td>
                    <td style="width:39%;text-align:left;"><b>'.$readSubject[3].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[4].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[5].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[6].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[7].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[8].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[9].'</b></td>
                    <td style="width:7%;text-align:center;"><b>'.$readSubject[10].'</b></td>
                    <td style="width:5%;text-align:center;"><b>'.$readSubject[11].'</b></td>
                   </tr>';
}
 
 
for ($z=$lineCount; $z < 90; $z++) { 
 
 if($lineCount<($linesLoopMaxL)){
     $strtdL .= '<tr>
    <td style="width:11%;text-align:center;"></td>
    <td style="width:39%;text-align:left;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    </tr>';
    
 }elseif($lineCount>($linesLoopMaxL-1)&&$lineCount<$linesLoopMaxR){
     $strtdR .= '<tr>
    <td style="width:11%;text-align:center;"></td>
    <td style="width:39%;text-align:left;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    </tr>';
 }
$lineCount=$lineCount+1;
}
     $html = <<<EOD
<style>
 th{

  font-size:7px; 
}
 td{

  font-size:{$fontSizeCource}px;
   border-left: 0px solid black;
    border-right: 0px solid black; 
}
#table2 {
  
    height: 400px;
  
  border-collapse: collapse;
}
#table2 td:empty {
  height:400px;
  border-left: 0;
  border-right: 0;
}
.borderspace{
  border-spacing: -1.5px;
    border-collapse: separate;
    width:99%;
   display: block;
   padding-left:2px;

}

</style>
<table id="table2" class="t-table borderspace" cellspacing="0" cellpadding="2" border="0.1" width="46.4%">
  <tr>
    <th style="width:11%;text-align:center;">Subject Code</th>
    <th style="width:39%;text-align:center;">Subject Name</th>
    <th style="width:5%;text-align:center;">INT</th>
    <th style="width:5%;text-align:center;">EXT</th>
    <th style="width:7%;text-align:center;">TOT 40/100</th>
    <th style="width:7%;text-align:center;">Subject Credits</th>
    <th style="width:7%;text-align:center;">Earn Credits</th>
    <th style="width:7%;text-align:center;">Grade Point</th>
    <th style="width:7%;text-align:center;">Credit Points</th>
    <th style="width:5%;text-align:center;">Grade</th>
   </tr>
    {$strtdL}
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='12', $y=67.5+$offset, $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
$pdfBig->writeHTMLCell($w=0, $h=0, $x='12', $y=67.5+$offset, $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

 

     $html = <<<EOD
<style>
 th{

  font-size:7px; 
}
 td{

  font-size:{$fontSizeCource}px;
   border-left: 0px solid black;
    border-right: 0px solid black; 
}
#table3 {
  
    height: 400px;
  
  border-collapse: collapse;
}

</style>
<table id="table3" cellspacing="0" cellpadding="2" border="0.1" width="98.5%">
  <tr>
    <th style="width:11%;text-align:center;">Subject Code</th>
    <th style="width:39%;text-align:center;">Subject Name</th>
    <th style="width:5%;text-align:center;">INT</th>
    <th style="width:5%;text-align:center;">EXT</th>
    <th style="width:7%;text-align:center;">TOT 40/100</th>
    <th style="width:7%;text-align:center;">Subject Credits</th>
    <th style="width:7%;text-align:center;">Earn Credits</th>
    <th style="width:7%;text-align:center;">Grade Point</th>
    <th style="width:7%;text-align:center;">Credit Points</th>
    <th style="width:5%;text-align:center;">Grade</th>
   </tr>
    {$strtdR}
  </table>
EOD;
if(empty($offset)){
$offsetR=2;
}else{
$offsetR=0;    
}
$pdf->writeHTMLCell($w=0, $h=0, $x='222', $y=66-$offsetR, $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
$pdfBig->writeHTMLCell($w=0, $h=0, $x='222', $y=66-$offsetR, $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

 

     $html = <<<EOD
<style>
 th{

  font-size:9px; 
}
 td{

  font-size:7.5px;
   
}
#table4 {
  
    height: 400px;
  
  border-collapse: collapse;
}


</style>
<table id="table4" cellspacing="0" cellpadding="2" border="0.1" width="98.5%">
    <tr>
    <td style="width:12%;text-align:center;">SGPA (I) : <b>{$studentData[9]}</b></td>
    <td style="width:12%;text-align:left;">SGPA (II) : <b>{$studentData[10]}</b></td>
    <td style="width:13%;text-align:center;">SGPA (III) : <b>{$studentData[11]}</b></td>
    <td style="width:13%;text-align:center;">SGPA (IV) : <b>{$studentData[12]}</b></td>
    <td style="width:13%;text-align:center;">SGPA (V) : <b>{$studentData[13]}</b></td>
    <td style="width:13%;text-align:center;">SGPA (VI) : <b>{$studentData[14]}</b></td>
    <td style="width:12%;text-align:center;">CGPA : <b>{$studentData[15]}</b></td>
   <td style="width:12%;text-align:center;">Grade : <b>{$studentData[16]}</b></td>
    </tr>
    <tr>
     <td style="width:35%;text-align:left;" colspan="3">Academic Earned Credits : <b>{$studentData[18]}</b></td>
    <td style="width:37%;text-align:left;" colspan="3">Environment and Skill Course Earned Credits : <b>{$studentData[19]}</b></td>
    <td style="width:28%;text-align:left;" colspan="2">Total Earned Credits : <b>{$studentData[20]}</b></td>
    </tr>
    <tr>
     <td style="width:30%;text-align:left;" colspan="2">Marks : <b>{$studentData[22]}</b></td>
    <td style="width:35%;text-align:left;" colspan="3">Result : <b>{$studentData[17]}</b></td>
    <td style="width:35%;text-align:left;" colspan="3">Date : <b>{$studentData[21]}</b></td>
    </tr>
  </table>
EOD;

/*print_r($studentData);
exit;*/
$pdf->writeHTMLCell($w=0, $h=0, $x='222', $y='248.5', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
$pdfBig->writeHTMLCell($w=0, $h=0, $x='222', $y='248.5', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
     
     /*   $strFont = '13';
        $strX = 55;
        $strY = 219;
        $str = "th";
        $pdf->SetFont($timesNewRomanI, '', $strFont, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY(92.7, $strY);
        $pdf->Cell(0, 0, $str, 0, false, 'L');*/
        $nameOrg=$studentData[2];
// Ghost image
                $ghost_font_size = '13';
                $ghostImagex = 222.2;
                $ghostImagey = 267;
                $ghostImageWidth = 55;//68
                $ghostImageHeight = 9.8;
                $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
                // dd($name);

                $tmpDir = $this->createTemp(public_path().'/backend/images/ghosttemp/temp');
                // if(!array_key_exists($name, $ghostImgArr))
                // {
                    $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');
                    // $ghostImgArr[$name] = $w;   
                // }
                // else{
                //     $w = $ghostImgArr[$name];
                // }

                $pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);
                $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);
        //qr code    
        $dt = date("_ymdHis");
        $blobFile = pathinfo($studentData[23]);

        $serial_no=$GUID=$blobFile['filename'];
        $str= $GUID;
        $encryptedString = strtoupper(md5($str));
        $codeContents = $studentData[24];

        $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
        $qrCodex = 305;
        $qrCodey = 267;
        $qrCodeWidth =19.3;
        $qrCodeHeight = 19.3;
                
        \QrCode::size(75.6)
            //->backgroundColor(255, 255, 0)
            ->format('png')
            ->generate($codeContents, $qr_code_path);

        $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
        $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
        

        $str = $nameOrg;
        $str = strtoupper(preg_replace('/\s+/', '', $str)); //added by Mandar
        $textArray = imagettfbbox(1.4, 0, public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', $str);
        $strWidth = ($textArray[2] - $textArray[0]);
        $strHeight = $textArray[6] - $textArray[1] / 1.4;
        
        $width=2;
        $latestWidth = round($width*3.7795280352161);

         //Updated by Mandar
        $microlinestr=$str;
        $wd = '';
        $last_width = 0;
        $pdf->SetFont($arialb, '', 1.2, '', false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->StartTransform();
        $pdf->SetXY(220.6, 285);
        $pdf->Cell(0, 0, $microlinestr, 0, false, 'C');

        $pdfBig->SetFont($arialb, '', 1.2, '', false);
        $pdfBig->SetTextColor(0, 0, 0);
        $pdfBig->StartTransform();
        $pdfBig->SetXY(220.6, 285);
        $pdfBig->Cell(0, 0, $microlinestr, 0, false, 'C');

          //Signature
        $signature_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Bmcc coe sign.png';
        $signaturex = 365;
        $signaturey = 270.3;
        $signatureWidth = 30;
        $signatureHeight = 11;
        $pdf->image($signature_path,$signaturex,$signaturey,$signatureWidth,$signatureHeight,"",'','L',true,3600);           
        $pdfBig->image($signature_path,$signaturex,$signaturey,$signatureWidth,$signatureHeight,"",'','L',true,3600);           

        if($previewPdf!=1){

         $certName = str_replace("/", "_", $GUID) .".pdf";
            // $myPath =    ().'/backend/temp_pdf_file';
            //$myPath = public_path().'/backend/temp_pdf_file';
            $myPath = public_path().'/backend/temp_pdf_file';
            $dt = date("_ymdHis");

            $fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;


            // print_r($pdf);
            // print_r("$tmpDir/" . $name."".$ghost_font_size.".png");
            $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');
       // $pdf->Output('sample.pdf', 'F');

             $this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id,'Kenya\GC\\');

            $username = $admin_id['username'];
            date_default_timezone_set('Asia/Kolkata');

            $content = "#".$log_serial_no." serial No :".$serial_no.PHP_EOL;
            $date = date('Y-m-d H:i:s').PHP_EOL;
            $print_datetime = date("Y-m-d H:i:s");
            

            $print_count = $this->getPrintCount($serial_no);
            $printer_name = /*'HP 1020';*/$printer_name;

            $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'Kenya-C',$admin_id,$card_serial_no);

            $card_serial_no=$card_serial_no+1;
            }
            //delete temp dir 26-04-2022 
            CoreHelper::rrmdir($tmpDir);
       } 

       if($previewPdf!=1){
        $this->updateCardNo('Kenya-C',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
       }
       $msg = '';
        // if(is_dir($tmpDir)){
        //     rmdir($tmpDir);
        // }   
       // $file_name = $template_data['template_name'].'_'.date("Ymdhms").'.pdf';
        //print_r($fetch_degree_array);
      //  exit;
        $file_name =  str_replace("/", "_",'Kenya-C'.date("Ymdhms")).'.pdf';
        
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();


       $filename = public_path().'/backend/tcpdf/examples/'.$file_name;
        // $filename = 'C:\xampp\htdocs\seqr\public\backend\tcpdf\exmples\/'.$file_name;
        $pdfBig->output($filename,'F');

        if($previewPdf!=1){

             

            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name="Kenya-C";

            // add sandboxing funcationlity
            CoreHelper::sandboxingDB($systemConfig,$template_name,$excelfile,$file_name,$user,$no_of_records,$auth_site_id);

            // if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            //     // with sandbox    
            //     $result = SbExceUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
            // }else{
            //     // without sandbox
            //     $result = ExcelUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
            // } 

            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';

            $msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/".$file_name."'class='downloadpdf' download target='_blank'>Here</a> to download file<b>";
        }else{
          

            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/preview/'.$file_name);
            @unlink($filename);
            // add sandboxing funcationlity
            CoreHelper::sandboxingDB($systemConfig,$template_name,$excelfile,$file_name,$user,$no_of_records,$auth_site_id);
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';

            $msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/preview/".$file_name."'class='downloadpdf' download target='_blank'>Here</a> to download file<b>";
        }
         
         //               }
                    //}
        //echo $msg;
        return $msg;

    }

    public function pdfGenerate(){


        $domain = \Request::getHost();
        
        $subdomain = explode('.', $domain);
        $ghostImgArr = array();
        $pdf = new TCPDF('L', 'mm', array('297', '420'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
       // $pdf->SetCreator('TCPDF');
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);

        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);

       $timesNewRoman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.TTF', 'TrueTypeUnicode', '', 96);
        $timesNewRomanB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.TTF', 'TrueTypeUnicode', '', 96);
        $timesNewRomanBI = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.TTF', 'TrueTypeUnicode', '', 96);
        $timesNewRomanI = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times New Roman_I.TTF', 'TrueTypeUnicode', '', 96);



        $pdf->AddPage();
        
        //set background image
        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\Kenya statment of marks_BG.jpg';
        // $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\DO_Background Light.jpg';
        // dd($template_img_generate);
        $pdf->Image($template_img_generate, 0, 0, '420', '297', "JPG", '', 'R', true);
        $pdf->setPageMark();
        $fontEBPath=public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\E-13B_0.php';
        $pdf->AddFont('E-13B_0', '', $fontEBPath);
         //set enrollment no
        $enrollment_font_size = '13';
        $enrollmentx= 172.5;
        $enrollmenty = 35.5;
        $enrollmentstr = '012345';
        $pdf->SetFont('E-13B_0', '', $enrollment_font_size, '', false);
        $pdf->SetXY($enrollmentx, $enrollmenty);
        $pdf->Cell(23.5, 0, $enrollmentstr, 0, false, 'R');

        $enrollment_font_size = '13';
        $enrollmentx= 380.5;
        $enrollmenty = 35.5;
        $enrollmentstr = '012345';
        $pdf->SetFont('E-13B_0', '', $enrollment_font_size, '', false);
        $pdf->SetXY($enrollmentx, $enrollmenty);
        $pdf->Cell(23.5, 0, $enrollmentstr, 0, false, 'R');

        /*$enrollment_font_size = '15';
        $enrollmentx= 190.2;
        $enrollmenty = 34.5;
        $enrollmentstr = '/1';
        $pdf->SetFont($arial, '', $enrollment_font_size, '', false);
        $pdf->SetXY($enrollmentx, $enrollmenty);
        $pdf->Cell(0, 0, $enrollmentstr, 0, false, '');*/
        

        $date_font_size = '11';
        $date_nox = 13;
        $date_noy = 37;
        $date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
        $pdf->SetFont($arialb, '', $date_font_size, '', false);
        $pdf->SetTextColor(192,192,192);
        $pdf->SetXY($date_nox, $date_noy);
        $pdf->Cell(0, 0, $date_nostr, 0, false, 'L');
        $pdf->SetTextColor(0,0,0,100,false,'');

        $pdf->SetFont($arial, '', 8, '', false);
        $html = <<<EOD
<style>
 td{
 
  font-size:8px; 
 border-top: 0px solid black;
    border-bottom: 0px solid black;
}
#table1 {
    
  border-collapse: collapse;
}
</style>
<table id="table1" cellspacing="0" cellpadding="2" border="0.1" width="46.4%" rules="rows">
  <tr>
    <td  colspan="2" style="width:52%;">  Student Name :  <b>Bhavendra Manoj Kumar </b></td>
    <td style="width:48%;">  Mother's Name :  <b>Bhavika </b></td>
   </tr>
   <tr>
    <td colspan="2" style="width:66.67%;">  Examination Programme : <b>BACHELOR OF COMMERCE</b></td>
    <td style="width:33.33%;">  Year : <b>2020</b></td>
   </tr>
   <tr>
    <td style="width:33.33%;">  Permanent Reg . No : <b>1700020001</b></td>
    <td style="text-align:center;width:33.34%;">  Seat Number : <b>00001</b></td>
    <td style="width:33.33%;"> UID. No : <b>202012345</b></td>
   </tr>
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='12', $y='46', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

$html = <<<EOD
<style>
 td{
 
  font-size:9px; 

}
#table111 {
    
  border-collapse: collapse;
}
</style>
<table id="table111" cellspacing="0" cellpadding="2" border="0" width="46.4%">
  <tr>
    <td>Special Subject : <b>BUSINESS COMMUNICATION - I</b></td>
    </tr>
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='12', $y='63', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

        $html = <<<EOD
<style>
 td{
 
  font-size:9px; 
 border-top: 0px solid black;
    border-bottom: 0px solid black;
}
#table11 {
    
  border-collapse: collapse;
}
</style>
<table id="table11" cellspacing="0" cellpadding="2" border="0.1" width="98.5%" rules="rows">
  <tr>
    <td colspan="2" style="width:52%;">  Student Name :  <b>Bhavendra Manoj Kumar </b></td>
    <td style="width:48%;">  Mother's Name :  <b>Bhavika </b></td>
   </tr>
   <tr>
    <td colspan="2" style="width:66.67%;">  Examination Programme : <b>BACHELOR OF COMMERCE</b></td>
    <td style="width:33.33%;">  Year : <b>2020</b></td>
   </tr>
   <tr>
    <td style="width:33.33%;">  Permanent Reg . No : <b>1700020001</b></td>
    <td style="text-align:center;width:33.34%;">  Seat Number : <b>00001</b></td>
    <td style="width:33.33%;"> UID. No : <b>202012345</b></td>
   </tr>
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='222', $y='46', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);



$strtd="";
for ($i=0; $i <42 ; $i++) { 
  if($i!=6&&$i!=12&&$i!=17){
    $strtd .= '<tr>
    <td style="width:11%;text-align:center;"><b>2406 (D)</b></td>
    <td style="width:39%;text-align:left;"><b>SERVICES OPERATIONS MANAGEMENT</b></td>
    <td style="width:5%;text-align:center;"><b>31</b></td>
    <td style="width:5%;text-align:center;"><b>43</b></td>
    <td style="width:7%;text-align:center;"><b>74</b></td>
    <td style="width:7%;text-align:center;"><b>3</b></td>
    <td style="width:7%;text-align:center;"><b>3</b></td>
    <td style="width:7%;text-align:center;"><b>7</b></td>
    <td style="width:7%;text-align:center;"><b>24</b></td>
    <td style="width:5%;text-align:center;"><b>A</b></td>
   </tr>';

 }else{
    
    if($i==6){
      $sem="II";
    }elseif($i==12){
      $sem="III";
    }elseif($i==17){
      $sem="IV";
    }
    
 
  $strtd .= '<tr>
    <td style="width:11%;text-align:center;"><b>SEM '.$sem.'</b></td>
    <td style="width:39%;text-align:left;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    </tr>';
 }
}

     $html = <<<EOD
<style>
 th{

  font-size:7px; 
}
 td{

  font-size:8px;
   border-left: 0px solid black;
    border-right: 0px solid black; 
}
#table2 {
  
    height: 400px;
  
  border-collapse: collapse;
}
#table2 td:empty {
    height:400px;
  border-left: 0;
  border-right: 0;
}
.borderspace{
    border-spacing: -1.5px;
    border-collapse: separate;
    width:99%;
   display: block;
   padding-left:2px;

}

</style>
<table id="table2" class="t-table borderspace" cellspacing="0" cellpadding="2" border="0.1" width="46.4%">
  <tr>
    <th style="width:11%;text-align:center;">Subject Code</th>
    <th style="width:39%;text-align:center;">Subject Name</th>
    <th style="width:5%;text-align:center;">INT</th>
    <th style="width:5%;text-align:center;">EXT</th>
    <th style="width:7%;text-align:center;">TOT 40/100</th>
    <th style="width:7%;text-align:center;">Subject Credits</th>
    <th style="width:7%;text-align:center;">Earn Credits</th>
    <th style="width:7%;text-align:center;">Grade Point</th>
    <th style="width:7%;text-align:center;">Credit Points</th>
    <th style="width:5%;text-align:center;">Grade</th>
   </tr>
   
    {$strtd}
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='12', $y='70', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

$strtd="";
for ($i=0; $i <35 ; $i++) { 
  if($i!=8){
    $strtd .= '<tr>
    <td style="width:11%;text-align:center;"><b>2406 (D)</b></td>
    <td style="width:39%;text-align:left;"><b>SERVICES OPERATIONS MANAGEMENT </b></td>
    <td style="width:5%;text-align:center;"><b>31</b></td>
    <td style="width:5%;text-align:center;"><b>43</b></td>
    <td style="width:7%;text-align:center;"><b>74</b></td>
    <td style="width:7%;text-align:center;"><b>3</b></td>
    <td style="width:7%;text-align:center;"><b>3</b></td>
    <td style="width:7%;text-align:center;"><b>7</b></td>
    <td style="width:7%;text-align:center;"><b>24</b></td>
    <td style="width:5%;text-align:center;"><b>A</b></td>
   </tr>';

 }else{
 
    $sem="VI";
 
  $strtd .= '<tr>
    <td style="width:11%;text-align:center;"><b>SEM '.$sem.'</b></td>
    <td style="width:39%;text-align:left;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:7%;text-align:center;"></td>
    <td style="width:5%;text-align:center;"></td>
    </tr>';
 }
} 

     $html = <<<EOD
<style>
 th{

  font-size:7px; 
}
 td{

  font-size:8px;
   border-left: 0px solid black;
    border-right: 0px solid black; 
}
#table3 {
  
    height: 400px;
  
  border-collapse: collapse;
}

</style>
<table id="table3" cellspacing="0" cellpadding="2" border="0.1" width="98.5%">
  <tr>
    <th style="width:11%;text-align:center;">Subject Code</th>
    <th style="width:39%;text-align:center;">Subject Name</th>
    <th style="width:5%;text-align:center;">INT</th>
    <th style="width:5%;text-align:center;">EXT</th>
    <th style="width:7%;text-align:center;">TOT 40/100</th>
    <th style="width:7%;text-align:center;">Subject Credits</th>
    <th style="width:7%;text-align:center;">Earn Credits</th>
    <th style="width:7%;text-align:center;">Grade Point</th>
    <th style="width:7%;text-align:center;">Credit Points</th>
    <th style="width:5%;text-align:center;">Grade</th>
   </tr>
    {$strtd}
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='222', $y='64', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

 

     $html = <<<EOD
<style>
 th{

  font-size:9px; 
}
 td{

  font-size:7.5px;
   
}
#table4 {
  
    height: 400px;
  
  border-collapse: collapse;
}


</style>
<table id="table4" cellspacing="0" cellpadding="2" border="0.1" width="98.5%">
    <tr>
    <td style="width:12%;text-align:center;">SGPA (I) : <b>31.00</b></td>
    <td style="width:12%;text-align:left;">SGPA (II) : <b>31.00</b></td>
    <td style="width:13%;text-align:center;">SGPA (III) : <b>31.00</b></td>
    <td style="width:13%;text-align:center;">SGPA (IV) : <b>31.00</b></td>
    <td style="width:13%;text-align:center;">SGPA (V) : <b>31.00</b></td>
    <td style="width:13%;text-align:center;">SGPA (VI) : <b>31.00</b></td>
    <td style="width:12%;text-align:center;">CGPA : <b>31.00</b></td>
   <td style="width:12%;text-align:center;">Grade : <b>31.00</b></td>
    </tr>
    <tr>
     <td style="width:35%;text-align:left;" colspan="3">Academic Earned Credits : <b>31.00</b></td>
    <td style="width:37%;text-align:left;" colspan="3">Environment and Skill Course Earned Credits : <b>31.00</b></td>
    <td style="width:28%;text-align:left;" colspan="2">Total Earned Credits : <b>31.00</b></td>
    </tr>
    <tr>
     <td style="width:30%;text-align:left;" colspan="2">Marks : <b>31.00</b></td>
    <td style="width:35%;text-align:left;" colspan="3">Result : <b>Pass</b></td>
    <td style="width:35%;text-align:left;" colspan="3">Date : <b>17 Feb 2021</b></td>
    </tr>
  </table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='222', $y='248.5', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
     
     /*   $strFont = '13';
        $strX = 55;
        $strY = 219;
        $str = "th";
        $pdf->SetFont($timesNewRomanI, '', $strFont, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY(92.7, $strY);
        $pdf->Cell(0, 0, $str, 0, false, 'L');*/
        $nameOrg="Bhavendra Manoj Kumar";
// Ghost image
                $ghost_font_size = '13';
                $ghostImagex = 222.2;
                $ghostImagey = 267;
                $ghostImageWidth = 55;//68
                $ghostImageHeight = 9.8;
                $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
                // dd($name);

                $tmpDir = $this->createTemp(public_path().'/backend/images/ghosttemp/temp');
                // if(!array_key_exists($name, $ghostImgArr))
                // {
                    $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');
                    // $ghostImgArr[$name] = $w;   
                // }
                // else{
                //     $w = $ghostImgArr[$name];
                // }

                $pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);
        //qr code    
        $dt = date("_ymdHis");
        $str="TEST0001";
        $encryptedString = strtoupper(md5($str));
        $codeContents = "https://doc.deccansociety.org/Doc/GetDoc?filePath=7GAvqnwkJgMqlDQ0T/qRWpojRJgrMNTO8F0NxN4/Q1g=";

        $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
        $qrCodex = 305;
        $qrCodey = 267;
        $qrCodeWidth =19.3;
        $qrCodeHeight = 19.3;
                
        \QrCode::size(75.6)
            //->backgroundColor(255, 255, 0)
            ->format('png')
            ->generate($codeContents, $qr_code_path);

        $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
        //delete temp dir 26-04-2022 
            CoreHelper::rrmdir($tmpDir);

                            $str = $nameOrg;
                            $str = strtoupper(preg_replace('/\s+/', '', $str)); //added by Mandar
                            $textArray = imagettfbbox(1.4, 0, public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', $str);
                            $strWidth = ($textArray[2] - $textArray[0]);
                            $strHeight = $textArray[6] - $textArray[1] / 1.4;
                            
                             $width=2;
                            $latestWidth = round($width*3.7795280352161);

                             //Updated by Mandar
                            $microlinestr=$str;
                           /* $microlinestrLength=strlen($microlinestr);

                            //width per character
                            $microLinecharacterWd =$strWidth/$microlinestrLength;

                            //Required no of characters required in string to match width
                             $microlinestrCharReq=$latestWidth/$microLinecharacterWd;
                            $microlinestrCharReq=round($microlinestrCharReq);
                           // echo '<br>';
                            //No of time string should repeated
                             $repeateMicrolineStrCount=$latestWidth/$strWidth;
                             $repeateMicrolineStrCount=round($repeateMicrolineStrCount)+1;

                            //Repeatation of string 
                             $microlinestrRep = str_repeat($microlinestr, $repeateMicrolineStrCount);
                           // echo strlen($microlinestrRep);
                            //Cut string in required characters (final string)
                            $array = substr($microlinestrRep,0,$microlinestrCharReq);*/

                            $wd = '';
                            $last_width = 0;
                            $pdf->SetFont($arialb, '', 1.2, '', false);
                            $pdf->SetTextColor(0, 0, 0);
                            $pdf->StartTransform();
                            // $pdf->SetXY(36.8, 146.6);
                            $pdf->SetXY(220.6, 285);
                            
                            $pdf->Cell(0, 0, $microlinestr, 0, false, 'C');

          //profile photo
        $signature_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Bmcc coe sign.png';
         $signaturex = 365;
        $signaturey = 270.3;
        $signatureWidth = 30;
        $signatureHeight = 11;
        $pdf->image($signature_path,$signaturex,$signaturey,$signatureWidth,$signatureHeight,"",'','L',true,3600);                 
      
        $pdf->Output('sample.pdf', 'I');  
    }

   
    

    

    public function pdfSample(){

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $ghostImgArr = array();
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);

        $pdf->SetCreator('TCPDF');
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);
        
        
        // add spot colors
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial Bold.TTF', 'TrueTypeUnicode', '', 96);

        $krutidev100 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\K100.TTF', 'TrueTypeUnicode', '', 96); 
        $krutidev101 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\K101.TTF', 'TrueTypeUnicode', '', 96);
        $HindiDegreeBold = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KRUTI_DEV_100__BOLD.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALN.TTF', 'TrueTypeUnicode', '', 96); 
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $timesNewRomanB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.TTF', 'TrueTypeUnicode', '', 96);
        $timesNewRomanBI = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.TTF', 'TrueTypeUnicode', '', 96);
        $timesNewRomanI = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times New Roman_I.TTF', 'TrueTypeUnicode', '', 96);
        $timesNewRoman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\times new roman_N.TTF', 'TrueTypeUnicode', '', 96);
        $style1Da = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 3
        );


        $pdf->AddPage();
        

        // $template_img_generate = public_path().'\\'.$subdomain[0].'\RND\curv\Rainbow Background.jpg';
        // $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\Kenya_Education_Certificate_BG_1.jpg'; 
        // $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
        
        // cheque Kesara
        $template_img_generate = public_path().'\\Kesara.png';
        // dd($template_img_generate);
        
        //3.2mm 1.6 mm 2.7 mm 2.8 mm 3.2 mm 2.8 mm 2.9 mm 2.6 mm 2.9 mm 2.9 mm
        $numberOffset=[0=>3.2,1=>1.6,2=>2.7,3=>2.8,4=>3.2,5=>2.8,6=>2.9,7=>2.6,8=>2.9,9=>2.9];
        $numberToWord=[0=>"ZERO",1=>"ONE",2=>"TWO",3=>"THREE",4=>"FOUR",5=>"FIVE",6=>"SIX",7=>"SEVEN",8=>"EIGHT",9=>"NINE"];
        // $stringArr=['KESRA/234567','KESRA/123456','KESRA/123156','KESRA/129486'];
        $stringArr=[];
        // for($z=0;$z<=0;$z++){
        //     $number= rand(000000, 999999); 
        //     $stringArr[]=$number;
        // }
            $value = 7740001040200206;
        //print_r($stringArr);
        //exit;

        //$microlinestr='KESRA/123456';
        $tempY=0;
        // foreach($stringArr as $value){

            $pdf->Image($template_img_generate, 10, 10+$tempY, '70', '10', "PNG", '', 'R', true);

            $microlinestr=$value;

            $pdf->SetFont($gilroyMedium, '', 17.5, '', false);
            $pdf->setFontSpacing(0.5);
            $pdf->SetTextColor(256,256,246);
            $pdf->SetXY(12.5, 11 + $tempY);
            $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.1, 'depth_h'=>0.1, 'color'=>array(0,0,0), 'opacity'=>5, 'blend_mode'=>'Normal'));
            $pdf->Cell(0, 0, $microlinestr, 0, false, 'L');   
            $pdf->setTextShadow(array('enabled'=>false, 'depth_w'=>0.1, 'depth_h'=>0.1, 'color'=>array(0,0,0), 'opacity'=>5, 'blend_mode'=>'Normal'));
        

            $pdf->SetFont($gilroyMedium, '', 2.5, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->setFontSpacing(0);
            //Bottom Characters Section 
            $yCharacter=17.6 + $tempY;      
            // $pdf->SetXY(14, $yCharacter);
            // $pdf->Cell(0, 0, 'K', 0, false, 'L');

            // $pdf->SetXY(17.9, $yCharacter);
            // $pdf->Cell(0, 0, 'E', 0, false, 'L');

            // $pdf->SetXY(21.9, $yCharacter);
            // $pdf->Cell(0, 0, 'S', 0, false, 'L');

            // $pdf->SetXY(25.6, $yCharacter);
            // $pdf->Cell(0, 0, 'R', 0, false, 'L');

            // $pdf->SetXY(30.1, $yCharacter);
            // $pdf->Cell(0, 0, 'A', 0, false, 'L');
            //Bottom Numbers Section 
            // $strArr=explode('/',$microlinestr);
            //$numericArr=$strArr[1];
            $numericArr="".$value;

            $yWordNumber=17.6 + $tempY;      
            if($numericArr[0]==1){
                $xBottom=14;
            }else{
                $xBottom=12;
            }
            $spacing=0.5;
        
            for($i=0;$i<strlen($numericArr);$i++){
                //$arr[] = $numericArr[$i];
                if($i==0){
                    $xBottom = $xBottom+$spacing;
                }else{

                    if($numberOffset[$numericArr[$i-1]]>2&&$numberOffset[$numericArr[$i]]>2){
                        $xBottom = $xBottom+$spacing+$numberOffset[$numericArr[$i]]+($numberOffset[$numericArr[$i-1]]/6);    
                    }else if($numberOffset[$numericArr[$i-1]]>2&&$numberOffset[$numericArr[$i]]!=1.6){
                        $xBottom = $xBottom+$spacing+$numberOffset[$numericArr[$i]]+($numberOffset[$numericArr[$i-1]]/8);    
                    }else{

                        if($numericArr[$i-1]==1){
                            $xBottom = $xBottom+$spacing+$numberOffset[$numericArr[$i]]+($numberOffset[$numericArr[$i-1]]/8);
                        }else{    
                            $xBottom = $xBottom+$spacing+$numberOffset[$numericArr[$i]]+($numberOffset[$numericArr[$i-1]]/3);
                        }
                    }    
                    
                }

                $pdf->SetXY($xBottom, $yWordNumber);
                $pdf->Cell(0, 0, $numberToWord[$numericArr[$i]], 0, false, 'L');
            }

            $tempY=$tempY+10;
        // }


        // // Parameters for the sine wave
        // $amplitude = 30;
        // $frequency = 0.05;
        // $phase = 0;
        // $characterSpacing = 10;

        // // Text to be repeated along the sine wave
        // $text = "SINE WAVE";

        // // X and Y positions
        // $x = 10;
        // $y = 100;

        // // Loop to place characters along the sine wave with rotation
        // for ($i = 0; $i < strlen($text); $i++) {
        //     $char = $text[$i];
        //     $yOffset = $amplitude * sin($frequency * $i + $phase);

        //     // Calculate the rotation angle based on the slope of the sine wave
        //     $angle = atan($amplitude * $frequency * cos($frequency * $i + $phase));
        //     $angle = rad2deg($angle);

        //     // Place the character with rotation
        //     $pdf->StartTransform();
        //     $pdf->Rotate($angle);
        //     $pdf->Text($x + $i * $characterSpacing, $y + $yOffset, $char);
        //     $pdf->StopTransform();
        // }

        // cheque Kesara



        // kenya Certificate 
        // $strName = 'KANARIO FAITH MURUKI';
        // $strName = str_replace(' ', '', $strName);

        // // count of string
        // $strNameLength = strlen($strName);

        // // for each concat string in one line
        // $oneLineLoopCount = 98 / $strNameLength;
        // $oneLineLoopCount = ceil($oneLineLoopCount);
        // // add string in multiple times
        // $newString= "";
        // for ($x = 0; $x <= $oneLineLoopCount; $x++) {
        //     $newString .= $strName;
        // }
        
        // $totalLopChar = 2842;
        // // for Loop multiple lines count 
        // $muliptleLineCount = $totalLopChar / $strNameLength;
        // $muliptleLineCount = ceil($muliptleLineCount);

        // $fullString= "";
        // for ($x = 0; $x <= $muliptleLineCount; $x++) {
        //     $fullString .= $strName;
        // }


        // $fullStringNew = substr($fullString,0,2842);
        // // echo $fullStringNew;
        
        // $chunk_length = 98;
        // $output = str_split($fullStringNew, $chunk_length);
        // $strArry = [];
        // foreach ($output as $x => $val) {
        //     $strArry[] =  $val;
            
        // }
        // // echo "<pre>";
        // // print_r($strArry[0]);
        // // echo "</pre>";
        // // die();
        
        // //clipping
        // $xClip=16.5;
        // $yClip=137.7;
        // $wClip=175;
        // $hClip=83.5;
        
        // // Start clipping.      
        // $pdf->StartTransform();

        // // Draw clipping rectangle to match html cell.
        // $pdf->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');

        

        // $k=0;
        // for($q=0;$q<3;$q++){

        //     for($p=0;$p<10;$p++){

        //         //ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567
        //         // if($p==0){
        //             // $str1="ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCD";
                    
        //             $str1 = substr($strArry[$p],0,40);
        //             $str2 = substr($strArry[$p],40,40);
        //             $str3 = substr($strArry[$p],80,18);

                    
        //             // echo $str1;
        //             // echo "<br>";
        //             // echo $str2;
        //             // echo "<br>";
        //             // echo $str3;
        //             // echo "<br>";

        //             // echo $str1;
        //             // echo "<br>";
        //             // echo $str2;
        //             // echo "<br>";
        //             // echo $str3;
        //             // echo "<br>";
        //             // echo "<br>";

        //             // $str1="WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW";
        //             // $str2="WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW";
        //             // $str3="WWWWWWWWWWWWWWWWWWWWWWWWW";
        //         // }else if($p==1){
        //         //     $str1="67890ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
        //         //     $str2="0ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABC";
        //         //     $str3="DEFGHIJKLMNOPQRST";
        //         // }else if($p==2){
        //         //     $str1="1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ1234";
        //         //     $str2="567890ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678";
        //         //     $str3="90ABCDEFGHIJKLMNOP";
        //         // }else if($p==3){
        //         //     $str1="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
        //         //     $str2="Z1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ123";
        //         //     $str3="4567890ABCDEFGHIJK";
        //         // }else if($p==4){
        //         //     $str1="QRSTUVWXZ1234567890ABCDEFGHIJKLMNOPQRSTU";
        //         //     $str2="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
        //         //     $str3="Z1234567890ABCDEFG";
        //         // }else if($p==5){
        //         //     $str1="ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCD";
        //         //     $str2="EFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGH";
        //         //     $str3="IJKLMNOPQRSTUVWXYZ";
        //         // }else if($p==6){
        //         //     $str1="67890ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
        //         //     $str2="0ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABC";
        //         //     $str3="DEFGHIJKLMNOPQRSTU";
        //         // }else if($p==7){
        //         //     $str1="1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ1234";
        //         //     $str2="567890ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678";
        //         //     $str3="90ABCDEFGHIJKLMNOP";
        //         // }else if($p==8){
        //         //     $str1="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
        //         //     $str2="Z1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ123";
        //         //     $str3="4567890ABCDEFGHIJK";
        //         // }else if($p==9){
        //         //     $str1="QRSTUVWXZ1234567890ABCDEFGHIJKLMNOPQRSTU";
        //         //     $str2="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
        //         //     $str3="Z1234567890ABCDEFG";
        //         // }else if($p==10){
        //         //     $str1="QRSTUVWXZ1234567890ABCDEFGHIJKLMNOPQRSTU";
        //         //     $str2="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
        //         //     $str3="Z1234567890ABCDEF";
        //         // }

        //         //AAAAAAAAAAAAAAAAAWAARKGHADSAAZAZAZAZAZAZ
        //         $text = strtoupper($str1);//KANROFATHHMUAAKWREKANROFATHHMUAAKWREKANROFATHHMUAAKWRE
                
        //         // Font size and color
        //         $fontSize = 7;
        //         // $fontColor = imagecolorallocate($image, 0, 0, 0);

        //         // Font path (change to the path of your desired TTF font file)
        //         $fontPath = public_path().'/demo/backend/canvas/fonts/Arial.ttf';
        //         $imageHeight = 297;
        //         // Horizontal and vertical offsets for the sine wave effect
        //         $amplitude = 3; // Adjust this value to control the amplitude of the sine wave
        //         $period = 150;//100   // Adjust this value to control the frequency of the sine wave
        //         $offsetX = 16.5;
        //         $offsetY = 133.5+$k;
        //         $customX=0;
        //         $pdf->SetTextColor(255,255,255);
        //         $pdf->SetFont($arialNarrowB, 'B', $fontSize, '', false);
        //         // Loop through each character in the text
        //         for ($i = 0; $i < strlen($text); $i++) {
        //             $char = $text[$i];

        //             // Calculate the X and Y position for the current character
        //             $x = $i * $fontSize * 0.25;//1.5
        //             $y = $offsetY + $amplitude * sin(6 * M_PI * $x / $period);

        //             if($i==0){
        //                 $customX =0.5;
        //             }
        //             if($i==1){
        //                 $customX =0;
        //                 $y=$y-0.2;

        //             }
                    
        //             if($i==2){
        //                 $customX =-0.1;
        //                 $y=$y-0.7;

        //             }

        //             if($i==3){
        //                 $customX =-0.1;
        //                 $y=$y-1.3;

        //             }
                    
        //             if($i==4){
        //                 $customX =-0.1;
        //                 $y=$y-1.8;

        //             }

        //             if($i==5){
        //                 $customX =-0.8;
        //                 $y=$y-2.1;

        //             }

        //             if($i==6){
        //                 $customX =-1;
        //                 $y=$y-2.4;

        //             }

        //             if($i==7){
        //                 $customX =-1;
        //                 $y=$y-2.8;
        //             }

        //             if($i==8){
        //                 $customX =-1.4;
        //                 $y=$y-2.9;
        //             }

        //             if($i==9){
        //                 $customX =-1.6;
        //                 $y=$y-3;
        //             }

        //             if($i==10){
        //                 $customX =-1.8;
        //                 $y=$y-3;
        //             }


        //             if($i==11){
        //                 $customX =-2;
        //                 $y=$y-2.8;
        //             }

        //             if($i==12){
        //                 $customX =-2;
        //                 $y=$y-2.8;
        //             }

        //             if($i==13){
        //                 $customX =-2;
        //                 $y=$y-2.7;
        //             }

        //             if($i==14){
        //                 $customX =-1.8;
        //                 $y=$y-2.65;

        //             }


        //             if($i==15){
        //                 $customX =-1.7;
        //                 $y=$y-2.55;
        //             }
        //             if($i==16){
        //                 $customX =-0.9;
        //                 $y=$y-2.7;
        //             }
        //             if($i==17){
                        
        //                 $customX =-0.8;
        //                 $y=$y-2.4;
        //             }

        //             if($i==18){
        //                 $customX =-0.3;
        //                 $y=$y-2.4;
        //             }

        //             if($i==19){
        //                 $customX =0.2;
        //                 $y=$y-2.2;
        //             }

        //             if($i==20){
        //                 $customX =0.7;
                        
        //                 $y=$y-2.1;
        //             }

        //             if($i==21){
        //                 $customX =0.7;
                    
        //                 $y=$y-1.9;
        //             }

        //             if($i==22){
        //                 $customX =1.2;
        //                 $y=$y-1.9;
        //             }

        //             if($i==23){
                        
        //                 $customX =1.2;
        //                 $y=$y-1.9;
        //             }


        //             if($i==24){
        //                 $customX =1.4;
        //                 $y=$y-1.9;
        //             }

        //             if($i==25){
        //                 $customX =1.4;
        //                 $y=$y-2.1;
        //             }

        //             if($i==26){
        //                 //$pdf->SetTextColor(169,169,169);
        //                 $customX =2.2;
        //                 $y=$y-2.5;
        //             }

        //             if($i==27){
        //                 $customX =2.1;
        //                 $y=$y-2.8;
        //             }

        //             if($i==28){
        //                 $customX =1.9;
        //                 $y=$y-3;
        //             }


        //             if($i==29){
        //                 $customX =1.8;
        //                 $y=$y-3.3;
        //             }

        //             if($i==30){
        //                 $customX =1.5;
        //                 $y=$y-3.7;
        //             }


        //             if($i==31){
        //                 $customX =1.6;
        //                 $y=$y-3.8;
        //             }

        //             if($i==32){
        //                 $customX =1.2;
        //                 $y=$y-4;
        //             }
                    

        //             if($i==33){
        //                 $customX =1.3;
        //                 $y=$y-4.1;
        //             }
                    

        //             if($i==34){
        //                 $customX =1.2;
        //                 $y=$y-4.1;
        //             }

        //             if($i==35){
        //                 $customX =1.5;
        //                 $y=$y-3.9;
        //             }
                    

        //             if($i==36){
        //                 $customX =1.4;
        //                 $y=$y-3.7;
        //             }
                    
        //             if($i==37){
        //                 $customX =1.7;
        //                 $y=$y-3.3;
        //             }

        //             if($i==38){
        //                 $customX =1.6;
        //                 $y=$y-3;
        //             }

        //             if($i==39){
        //                 $customX =1.7;
        //                 $y=$y-2.4;
        //             }

        //             // if($i==40){
        //             //     $customX =1.6;
        //             //     $y=$y-1.8;
        //             // }

        //             //  if($i==41){
        //             //     $customX =1.7;
        //             //     $y=$y-2.4;
        //             // }

        //             // if($i==42){
        //             //     $customX =1.6;
        //             //     $y=$y-1.8;
        //             // }
                    


        //             $pdf->SetXY($x + $offsetX+ $customX,$y);

        //             $pdf->StartTransform();

                    
        //             if($i>0){
        //                 if($i==5){
        //                     $rotateAngle =8;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }
        //                 if($i==6){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==7){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==8){
        //                     $rotateAngle =14;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==9){
        //                     $rotateAngle =16;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==10){
        //                     $rotateAngle =18;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==11){
        //                     $rotateAngle =20;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==12){
        //                     $rotateAngle =19;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==13){
        //                     $rotateAngle =20;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==14){
        //                     $rotateAngle =19;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==15){
        //                     $rotateAngle =18;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==16){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==17){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==18){
        //                     $rotateAngle =5;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==19){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i>19){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i>21){
        //                     $rotateAngle =4;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==26){
        //                     $rotateAngle =9;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //                 if($i==27){
        //                     $rotateAngle =8;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //                 if($i==28){
        //                     $rotateAngle =6;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==29){
        //                     $rotateAngle =4;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==30){
        //                     $rotateAngle =3;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==31){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==32){
        //                     $rotateAngle =1;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==33){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==34){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==35){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==36){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==37){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==38){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==39){
        //                     $rotateAngle =0;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //             }else{
        //                 $rotateAngle =$i+5;
        //                 $pdf->Rotate(-$rotateAngle); 
        //             }
        //             // if($i == 8) {
        //             //     // $pdf->SetAlpha(1);
        //             //     $pdf->SetAlpha(0.5);
        //             //     $pdf->SetTextColor(0,0,0,100,false,'');
        //             // }
        //             $pdf->Cell(210, 10, $char, 0, false, 'L');
                    
        //             // $pdf->SetTextColor(255,255,255);
        //             $pdf->StopTransform();
                    
        //         }
                

        //         $text = strtoupper($str2);//KANROFATHHMUAAKWREKANROFATHHMUAAKWREKANROFATHHMUAAKWRE

        //         // Font size and color
        //         $fontSize = 7;
        //         // $fontColor = imagecolorallocate($image, 0, 0, 0);

        //         // Font path (change to the path of your desired TTF font file)
        //         $fontPath = public_path().'/demo/backend/canvas/fonts/Arial.ttf';
        //         $imageHeight = 297;
        //         // Horizontal and vertical offsets for the sine wave effect
        //         $amplitude = 3; // Adjust this value to control the amplitude of the sine wave
        //         $period = 150;//100   // Adjust this value to control the frequency of the sine wave
        //         $offsetX = 88.3;
        //         $offsetY = 133.8+$k;
        //         $customX=0;
        //         //$pdf->SetTextColor(0, 0, 0);
        //         // $pdf->SetTextColor(255,255,255);
        //         // $pdf->SetFont($arial, 'B', $fontSize, '', false);
        //         // Loop through each character in the text
        //         for ($i = 0; $i < strlen($text); $i++) {
        //             $char = $text[$i];

        //             // Calculate the X and Y position for the current character
        //             $x = $i * $fontSize * 0.25;//1.5
        //             $y = $offsetY + $amplitude * sin(6 * M_PI * $x / $period);

        //             if($i==0){
        //                 $customX =0.5;
        //             }
        //             if($i==1){
        //                 $customX =-0.1;
        //                 $y=$y-0.2;

        //             }
                    
        //             if($i==2){
        //                 $customX =-0.1;
        //                 $y=$y-0.7;

        //             }

        //             if($i==3){
        //                 $customX =-0.1;
        //                 $y=$y-1.2;

        //             }
                    
        //             if($i==4){
        //                 $customX =-0.1;
        //                 $y=$y-1.9;

        //             }

        //             if($i==5){
        //                 $customX =-0.8;
        //                 $y=$y-2.1;

        //             }

        //             if($i==6){
        //                 $customX =-1;
        //                 $y=$y-2.6;

        //             }

        //             if($i==7){
        //                 $customX =-1;
        //                 $y=$y-2.8;
        //             }

        //             if($i==8){
        //                 $customX =-1.6;
        //                 $y=$y-2.9;
        //             }

        //             if($i==9){
        //                 $customX =-2;
        //                 $y=$y-3;
        //             }

        //             if($i==10){
        //                 $customX =-2;
        //                 $y=$y-3;
        //             }


        //             if($i==11){
        //                 $customX =-2;
        //                 $y=$y-2.8;
        //             }

        //             if($i==12){
        //                 $customX =-2;
        //                 $y=$y-3;
        //             }

        //             if($i==13){
        //                 $customX =-2;
        //                 $y=$y-2.9;
        //             }

        //             if($i==14){
        //                 $customX =-1.8;
        //                 $y=$y-2.8;
        //             }


        //             if($i==15){
        //                 $customX =-1.7;
        //                 $y=$y-2.6;
        //             }
        //             if($i==16){
        //                 $customX =-0.9;
        //                 $y=$y-2.6;
        //             }
        //             if($i==17){
        //                 $customX =-1;
        //                 $y=$y-2.4;
        //             }

        //             if($i==18){
        //                 $customX =0;
        //                 $y=$y-2.4;
        //             }

        //             if($i==19){
        //                 $customX =0.5;
        //                 $y=$y-2.2;
        //             }

        //             if($i==20){
        //                 $customX =1;
        //                 $y=$y-2;
        //             }

        //             if($i==21){
        //                 $customX =1;
        //                 $y=$y-1.9;
        //             }

        //             if($i==22){
        //                 $customX =1;
        //                 $y=$y-1.9;
        //             }

        //             if($i==23){
        //                 $customX =1.4;
        //                 $y=$y-1.9;
        //             }


        //             if($i==24){
        //                 $customX =1.4;
        //                 $y=$y-1.9;
        //             }

        //             if($i==25){
        //                 $customX =1.4;
        //                 $y=$y-2.1;
        //             }

        //             if($i==26){
        //                 $customX =2.2;
        //                 $y=$y-2.4;
        //             }

        //             if($i==27){
        //                 $customX =2;
        //                 $y=$y-2.8;
        //             }

        //             if($i==28){
        //                 $customX =1.9;
        //                 $y=$y-3;
        //             }


        //             if($i==29){
        //                 $customX =1.8;
        //                 $y=$y-3.3;
        //             }

        //             if($i==30){
        //                 $customX =1.5;
        //                 $y=$y-3.7;
        //             }


        //             if($i==31){
        //                 $customX =1.6;
        //                 $y=$y-3.8;
        //             }

        //             if($i==32){
        //                 $customX =1.2;
        //                 $y=$y-4;
        //             }
                    

        //             if($i==33){
        //                 $customX =1.3;
        //                 $y=$y-4.1;
        //             }
                    

        //             if($i==34){
        //                 $customX =1.2;
        //                 $y=$y-4.1;
        //             }

        //             if($i==35){
        //                 $customX =1.5;
        //                 $y=$y-3.9;
        //             }
                    

        //             if($i==36){
        //                 $customX =1.4;
        //                 $y=$y-3.7;
        //             }
                    
        //             if($i==37){
        //                 $customX =1.7;
        //                 $y=$y-3.3;
        //             }

        //             if($i==38){
        //                 $customX =1.6;
        //                 $y=$y-3;
        //             }

        //             if($i==39){
        //                 $customX =1.7;
        //                 $y=$y-2.4;
        //             }

        //             // if($i==40){
        //             //     $customX =1.6;
        //             //     $y=$y-1.8;
        //             // }

        //             //  if($i==41){
        //             //     $customX =1.7;
        //             //     $y=$y-2.4;
        //             // }

        //             // if($i==42){
        //             //     $customX =1.6;
        //             //     $y=$y-1.8;
        //             // }
                    


        //             $pdf->SetXY($x + $offsetX+ $customX,$y);

        //             $pdf->StartTransform();

                    
        //             if($i>0){

        //                 if($i==5){
        //                     $rotateAngle =8;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }
        //                 if($i==6){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==7){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==8){
        //                     $rotateAngle =14;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==9){
        //                     $rotateAngle =16;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==10){
        //                     $rotateAngle =18;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==11){
        //                     $rotateAngle =20;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==12){
        //                     $rotateAngle =19;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==13){
        //                     $rotateAngle =20;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==14){
        //                     $rotateAngle =19;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==15){
        //                     $rotateAngle =18;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==16){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==17){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==18){
        //                     $rotateAngle =5;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==19){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i>19){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i>21){
        //                     $rotateAngle =4;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==26){
        //                     $rotateAngle =9;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //                 if($i==27){
        //                     $rotateAngle =8;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //                 if($i==28){
        //                     $rotateAngle =6;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==29){
        //                     $rotateAngle =4;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==30){
        //                     $rotateAngle =3;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==31){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==32){
        //                     $rotateAngle =1;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==33){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==34){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==35){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==36){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==37){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==38){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==39){
        //                     $rotateAngle =0;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //             }else{
        //                 $rotateAngle =$i+5;
        //                 $pdf->Rotate(-$rotateAngle); 
        //             }

                    
        //             $pdf->Cell(210, 10, $char, 0, false, 'L');
        //             $pdf->StopTransform();
        //         }


        //         $text = strtoupper($str3);//KANROFATHHMUAAKWREKANROFATHHMUAAKWREKANROFATHHMUAAKWRE

        //         // Font size and color
        //         $fontSize = 7;
        //         // $fontColor = imagecolorallocate($image, 0, 0, 0);

        //         // Font path (change to the path of your desired TTF font file)
        //         $fontPath = public_path().'/demo/backend/canvas/fonts/Arial.ttf';
        //         $imageHeight = 297;
        //         // Horizontal and vertical offsets for the sine wave effect
        //         $amplitude = 3; // Adjust this value to control the amplitude of the sine wave
        //         $period = 150;//100   // Adjust this value to control the frequency of the sine wave
        //         $offsetX = 160;
        //         $offsetY = 133.8+$k;
        //         $customX=0;
        //         //$pdf->SetTextColor(0, 0, 0);
        //         //$pdf->SetTextColor(255,255,255);
        //         // $pdf->SetFont($arial, 'B', $fontSize, '', false);
        //         // Loop through each character in the text
        //         for ($i = 0; $i < strlen($text); $i++) {
        //             $char = $text[$i];

        //             // Calculate the X and Y position for the current character
        //             $x = $i * $fontSize * 0.25;//1.5
        //             $y = $offsetY + $amplitude * sin(6 * M_PI * $x / $period);

        //             if($i==0){
        //                 $customX =0.5;
        //             }
        //             if($i==1){
        //                 $customX =-0.1;
        //                 $y=$y-0.2;

        //             }
                    
        //             if($i==2){
        //                 $customX =-0.1;
        //                 $y=$y-0.7;

        //             }

        //             if($i==3){
        //                 $customX =-0.1;
        //                 $y=$y-1.2;

        //             }
                    
        //             if($i==4){
        //                 $customX =-0.1;
        //                 $y=$y-1.9;

        //             }

        //             if($i==5){
        //                 $customX =-0.8;
        //                 $y=$y-2.1;

        //             }

        //             if($i==6){
        //                 $customX =-1;
        //                 $y=$y-2.6;

        //             }

        //             if($i==7){
        //                 $customX =-1;
        //                 $y=$y-2.8;
        //             }

        //             if($i==8){
        //                 $customX =-1.6;
        //                 $y=$y-2.9;
        //             }

        //             if($i==9){
        //                 $customX =-2;
        //                 $y=$y-3;
        //             }

        //             if($i==10){
        //                 $customX =-2;
        //                 $y=$y-3;
        //             }


        //             if($i==11){
        //                 $customX =-2;
        //                 $y=$y-2.8;
        //             }

        //             if($i==12){
        //                 $customX =-2;
        //                 $y=$y-3;
        //             }

        //             if($i==13){
        //                 $customX =-2;
        //                 $y=$y-2.9;
        //             }

        //             if($i==14){
        //                 $customX =-1.8;
        //                 $y=$y-2.8;
        //             }


        //             if($i==15){
        //                 $customX =-1.7;
        //                 $y=$y-2.6;
        //             }
        //             if($i==16){
        //                 $customX =-0.9;
        //                 $y=$y-2.6;
        //             }
        //             if($i==17){
        //                 $customX =-1;
        //                 $y=$y-2.4;
        //             }

        //             if($i==18){
        //                 $customX =0;
        //                 $y=$y-2.4;
        //             }

        //             if($i==19){
        //                 $customX =0.5;
        //                 $y=$y-2.2;
        //             }

        //             if($i==20){
        //                 $customX =1;
        //                 $y=$y-2;
        //             }

        //             if($i==21){
        //                 $customX =1;
        //                 $y=$y-1.9;
        //             }

        //             if($i==22){
        //                 $customX =1;
        //                 $y=$y-1.9;
        //             }

        //             if($i==23){
        //                 $customX =1.4;
        //                 $y=$y-1.9;
        //             }


        //             if($i==24){
        //                 $customX =1.4;
        //                 $y=$y-1.9;
        //             }

        //             if($i==25){
        //                 $customX =1.4;
        //                 $y=$y-2.1;
        //             }

        //             if($i==26){
        //                 $customX =2.2;
        //                 $y=$y-2.4;
        //             }

        //             if($i==27){
        //                 $customX =2;
        //                 $y=$y-2.8;
        //             }

        //             if($i==28){
        //                 $customX =1.9;
        //                 $y=$y-3;
        //             }


        //             if($i==29){
        //                 $customX =1.8;
        //                 $y=$y-3.3;
        //             }

        //             if($i==30){
        //                 $customX =1.5;
        //                 $y=$y-3.7;
        //             }


        //             if($i==31){
        //                 $customX =1.6;
        //                 $y=$y-3.8;
        //             }

        //             if($i==32){
        //                 $customX =1.2;
        //                 $y=$y-4;
        //             }
                    

        //             if($i==33){
        //                 $customX =1.3;
        //                 $y=$y-4.1;
        //             }
                    

        //             if($i==34){
        //                 $customX =1.2;
        //                 $y=$y-4.1;
        //             }

        //             if($i==35){
        //                 $customX =1.5;
        //                 $y=$y-3.9;
        //             }
                    

        //             if($i==36){
        //                 $customX =1.4;
        //                 $y=$y-3.7;
        //             }
                    
        //             if($i==37){
        //                 $customX =1.7;
        //                 $y=$y-3.3;
        //             }

        //             if($i==38){
        //                 $customX =1.6;
        //                 $y=$y-3;
        //             }

        //             if($i==39){
        //                 $customX =1.7;
        //                 $y=$y-2.4;
        //             }

        //             // if($i==40){
        //             //     $customX =1.6;
        //             //     $y=$y-1.8;
        //             // }

        //             //  if($i==41){
        //             //     $customX =1.7;
        //             //     $y=$y-2.4;
        //             // }

        //             // if($i==42){
        //             //     $customX =1.6;
        //             //     $y=$y-1.8;
        //             // }
                    


        //             $pdf->SetXY($x + $offsetX+ $customX,$y);

        //             $pdf->StartTransform();

                    
        //             if($i>0){

        //                 if($i==5){
        //                     $rotateAngle =8;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }
        //                 if($i==6){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==7){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==8){
        //                     $rotateAngle =14;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==9){
        //                     $rotateAngle =16;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==10){
        //                     $rotateAngle =18;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==11){
        //                     $rotateAngle =20;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==12){
        //                     $rotateAngle =19;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==13){
        //                     $rotateAngle =20;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==14){
        //                     $rotateAngle =19;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==15){
        //                     $rotateAngle =18;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==16){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==17){
        //                     $rotateAngle =10;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==18){
        //                     $rotateAngle =5;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i==19){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate($rotateAngle); 
        //                 }

        //                 if($i>19){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i>21){
        //                     $rotateAngle =4;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==26){
        //                     $rotateAngle =9;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //                 if($i==27){
        //                     $rotateAngle =8;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //                 if($i==28){
        //                     $rotateAngle =6;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==29){
        //                     $rotateAngle =4;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==30){
        //                     $rotateAngle =3;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==31){
        //                     $rotateAngle =2;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==32){
        //                     $rotateAngle =1;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==33){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==34){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==35){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==36){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==37){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==38){
        //                     $rotateAngle =0.5;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }

        //                 if($i==39){
        //                     $rotateAngle =0;
        //                     $pdf->Rotate(-$rotateAngle); 
        //                 }


        //             }else{
        //                 $rotateAngle =$i+5;
        //                 $pdf->Rotate(-$rotateAngle); 
        //             }
        //             $pdf->Cell(210, 10, $char, 0, false, 'L');
        //             $pdf->StopTransform();
                
        //         }
        //         $k=$k+3;
        //     }

        // }
        // // die();
        // // Stop clipping.
        // $pdf->StopTransform();
        
        
        // $date_font_size = '11';
        // $date_nox = 50;
        // $date_noy = 85;
        // $date_nostr = 'Name :   KANARIO FAITH MURUKI';
        // $pdf->SetFont($arial, '', $date_font_size, '', false);
        // $pdf->SetTextColor(0,0,0,100,false,'');
        // $pdf->SetXY($date_nox, $date_noy);
        // $pdf->Cell(12, 0, $date_nostr, 0, false, 'C');
        
        
        // $date_font_size = '11';
        // $date_nox = 68.7;
        // $date_noy = 90;
        // $date_nostr = 'ATHIRU GATI SECONDARY SCHOOL';
        // $pdf->SetFont($arial, '', $date_font_size, '', false);
        // $pdf->SetTextColor(0,0,0,100,false,'');
        // $pdf->SetXY($date_nox, $date_noy);
        // $pdf->Cell(12, 0, $date_nostr, 0, false, 'C');
        
        
        // $date_font_size = '11';
        // $date_nox = 50;
        // $date_noy = 105;
        // $date_nostr = 'SUBJECT';
        // $pdf->SetFont($arial, 'B', $date_font_size, '', false);
        // $pdf->SetTextColor(0,0,0,100,false,'');
        // $pdf->SetXY($date_nox, $date_noy);
        // $pdf->Cell(12, 0, $date_nostr, 0, false, 'C');
        
        
        // $date_font_size = '11';
        // $date_nox = 50;
        // $date_noy = 115;
        // $date_nostr = '101   ENGLISH';
        // $pdf->SetFont($arial, '', $date_font_size, '', false);
        // $pdf->SetTextColor(0,0,0,100,false,'');
        // $pdf->SetXY($date_nox, $date_noy);
        // $pdf->Cell(12, 0, $date_nostr, 0, false, 'C');
        
        
        // $date_font_size = '11';
        // $date_nox = 51.7;
        // $date_noy = 120;
        // $date_nostr = '102   KISWAHILI';
        // $pdf->SetFont($arial, '', $date_font_size, '', false);
        // $pdf->SetTextColor(0,0,0,100,false,'');
        // $pdf->SetXY($date_nox, $date_noy);
        // $pdf->Cell(12, 0, $date_nostr, 0, false, 'C');
        
        
        // $date_font_size = '11';
        // $date_nox = 56;
        // $date_noy = 125;
        // $date_nostr = '121   MATHEMATICS';
        // $pdf->SetFont($arial, '', $date_font_size, '', false);
        // $pdf->SetTextColor(0,0,0,100,false,'');
        // $pdf->SetXY($date_nox, $date_noy);
        // $pdf->Cell(12, 0, $date_nostr, 0, false, 'C');
        
        
        
        // $date_font_size = '11';
        // $date_nox = 51.3;
        // $date_noy = 130;
        // $date_nostr = '231   BIOLOGY';
        // $pdf->SetFont($arial, '', $date_font_size, '', false);
        // $pdf->SetTextColor(0,0,0,100,false,'');
        // $pdf->SetXY($date_nox, $date_noy);
        // $pdf->Cell(12, 0, $date_nostr, 0, false, 'C');
        
        // // set style for barcode
        // $style = array(
        //     'border' => false,
        //     'vpadding' => '2',
        //     'hpadding' => '2',
        //     'fgcolor' => array(0,0,0),
        //     'bgcolor' => array(255,255,255),
        //     'module_width' => 1, // width of a single module in points
        //     'module_height' => 1 // height of a single module in points
        // );

        // $pdf->write2DBarcode('tkNDMvC2b58TqaEckaS0RN2BrFha9GdpX4pbAytEHUioudasiodtkNDMvC2b58TqaEckaS0RN2BrFha9GdpX4pbAytEHUioudasiod', 'DATAMATRIX', 20, 203, 17, 17, $style, 'N');

        //set background image
        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\BMCC PASSING CERTIFICATE Bg Plain.jpg';
        
        $fontEBPath=public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\E-13B_0.php';
        

        $resultsQR = "tkN#DMvC2b5$8@TqaEck@aS0RN2BrFh*=a9GdpX4p**bAytEH&U/+MAsdasd ajbujfasdgasudgsahdasbi idasidsahidaso ioudasiod";
        $qrJson=$resultsQR;


        $pdf->Output('sample.pdf', 'I');  
        

    }

    
    public function uploadpagePassing(){
      //return view('admin.statementsofmarks.index');
    
      return view('admin.statementsofmarks.passing');
    }

     public function validateExcelPassing(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        $template_id=100;
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
         //check file is uploaded or not
        if($request->hasFile('field_file')){
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile =  date("YmdHis") . "_" . $file_name;
            $target_path = public_path().'/backend/canvas/dummy_images/'.$template_id;
            $fullpath = $target_path.'/'.$excelfile;

            if(!is_dir($target_path)){
                
                            mkdir($target_path, 0777);
                        }

            if($request['field_file']->move($target_path,$excelfile)){
                //get excel file data
                // dd('hi');
                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
                }
                $auth_site_id=Auth::guard('admin')->user()->site_id;

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                if($get_file_aws_local_flag->file_aws_local == '1'){
                    // add sandboxing funcationlity
                    CoreHelper::sandboxingFileAWS($systemConfig,$fullpath,$template_id,$excelfile);

                    // if($systemConfig['sandboxing'] == 1){
                    //     $sandbox_directory = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/';
                    //     //if directory not exist make directory
                    //     if(!is_dir($sandbox_directory)){
                
                    //         mkdir($sandbox_directory, 0777);
                    //     }

                    //     $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
                    //     $filename1 = \Storage::disk('s3')->url($excelfile);
                    //     // dd($aws_excel);
                    // }else{
                        
                    //     $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
                    //     $filename1 = \Storage::disk('s3')->url($excelfile);
                    // }
                }
                else{

                    // add sandboxing funcationlity
                    CoreHelper::sandboxingFile($systemConfig,$fullpath,$template_id,$excelfile);
                    // $sandbox_directory = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/';
                    // //if directory not exist make directory
                    // if(!is_dir($sandbox_directory)){
                    //     mkdir($sandbox_directory, 0777);
                    // }
                    // if($systemConfig['sandboxing'] == 1){
                    //     $aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile);
                    //     // $filename1 = \Storage::disk('s3')->url($excelfile);
                    //     // dd($aws_excel);
                    // }else{
                    //     $aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile);
                    //     // $filename1 = \Storage::disk('s3')->url($excelfile);
                    // }
                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);
                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();
                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);

                 $excelData=array('rowData1'=>$rowData1,'auth_site_id'=>$auth_site_id);
                $response = $this->dispatch(new ValidateExcelKenyaPassingJob($excelData));

                $responseData =$response->getData();
               
                if($responseData->success){
                    $old_rows=$responseData->old_rows;
                    $new_rows=$responseData->new_rows;
                }else{
                   return $response;
                }
               // $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
                
                
               /* $rowData1[0] = array_filter($rowData1[0]);
                // dd($rowData);
                $columnsCount1=count($rowData1[0]);
                
                
                if($columnsCount1==12){

                    $columnArr=array('Name',"Mother's Name",'Branch','Month-Year','Grade','Programme','Seat No','P.R.No','College Code','Date','BlobFileName','QRCodeUrl');
                        $mismatchColArr=array_diff($rowData1[0], $columnArr);
                        //print_r($mismatchColArr);
                        if(count($mismatchColArr)>0){
                            
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1 : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                        }

                }else{
                    return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of sheet1 do not matched!']);
                }

                $ab = array_count_values($rowData1[0]);

                $duplicate_columns = '';
                foreach ($ab as $key => $value) {
                    
                    if($value > 1){

                        if($duplicate_columns != ''){

                            $duplicate_columns .= ", ".$key;
                        }else{
                            
                            $duplicate_columns .= $key;
                        }
                    }
                }

                if($duplicate_columns != ''){
                    // Excel has more than 1 column having same name. i.e. <column name>
                    $message = Array('success'=>false,'type' => 'error', 'message' => 'Excel has more than 1 column having same name. i.e. : '.$duplicate_columns);
                    return json_encode($message);
                }
               
                unset($rowData1[0]);
                $rowData1=array_values($rowData1);
                $blobArr=array();
                foreach ($rowData1 as $readblob) {
                    array_push($blobArr, $readblob[23]);
                }

                $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                $old_rows = 0;
                $new_rows = 0;
                foreach ($rowData1 as $key1 => $value1) {
                    $blobFile = pathinfo($value1[10]);
                    $serial_no=$blobFile['filename'];
                    array_push($blobArr, $serial_no);
                    if($sandboxCheck['sandboxing'] == 1){
                       
                        $studentTableCounts = SbStudentTable::where('serial_no',$serial_no)->where('site_id',$auth_site_id)->count();
                        
                        $studentTablePrefixCounts = SbStudentTable::where('serial_no','T-'.$serial_no)->where('site_id',$auth_site_id)->count();
                        if($studentTableCounts > 0){
                            $old_rows += 1;
                        }else if($studentTablePrefixCounts){
                            
                            $old_rows += 1;
                        }else{
                            $new_rows += 1;
                        }

                    }else{
                        $studentTableCounts = StudentTable::where('serial_no',$serial_no)->where('site_id',$auth_site_id)->count();
                        if($studentTableCounts > 0){
                            $old_rows += 1;
                        }else{
                            $new_rows += 1;
                        }
                    }   
                }
               
                $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));
                      
                    if(count($mismatchArr)>0){
                    return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : BlobFileName contains following duplicate values : '.implode(',', $mismatchArr)]);
                    }*/
            }

            //echo $fullpath;
            if (file_exists($fullpath)) {
                unlink($fullpath);
            }
            
        return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);

        }
        else{
            return response()->json(['success'=>false,'message'=>'File not found!']);
        }


    }

    public function uploadfilePassing(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $template_id = 100;
       $previewPdf = array($request['previewPdf'],$request['previewWithoutBg']);

        //check file is uploaded or not
        if($request->hasFile('field_file')){
            //check extension
            $file_name = $request['field_file']->getClientOriginalName();
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            //excel file name
            $excelfile =  date("YmdHis") . "_" . $file_name;
            $target_path = public_path().'/backend/canvas/dummy_images/'.$template_id;
            $fullpath = $target_path.'/'.$excelfile;

            if(!is_dir($target_path)){
                
                            mkdir($target_path, 0777);
                        }

            if($request['field_file']->move($target_path,$excelfile)){
                //get excel file data
                // dd('hi');
                if($ext == 'xlsx' || $ext == 'Xlsx'){
                    $inputFileType = 'Xlsx';
                }
                else{
                    $inputFileType = 'Xls';
                }
                $auth_site_id=Auth::guard('admin')->user()->site_id;

                $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                if($get_file_aws_local_flag->file_aws_local == '1'){
                    // add sandboxing funcationlity
                    CoreHelper::sandboxingFileAWS($systemConfig,$fullpath,$template_id,$excelfile);

                    // if($systemConfig['sandboxing'] == 1){
                    //     $sandbox_directory = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/';
                    //     //if directory not exist make directory
                    //     if(!is_dir($sandbox_directory)){
                
                    //         mkdir($sandbox_directory, 0777);
                    //     }

                    //     $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
                    //     $filename1 = \Storage::disk('s3')->url($excelfile);
                    //     // dd($aws_excel);
                    // }else{
                        
                    //     $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
                    //     $filename1 = \Storage::disk('s3')->url($excelfile);
                    // }
                }
                else{
                    CoreHelper::sandboxingFile($systemConfig,$fullpath,$template_id,$excelfile);
                    // $sandbox_directory = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/';
                    // //if directory not exist make directory
                    // if(!is_dir($sandbox_directory)){
                    //     mkdir($sandbox_directory, 0777);
                    // }

                    // if($systemConfig['sandboxing'] == 1){
                    //     $aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile);
                    //     // $filename1 = \Storage::disk('s3')->url($excelfile);
                    //     // dd($aws_excel);
                    // }else{
                    //     $aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile);
                    //     // $filename1 = \Storage::disk('s3')->url($excelfile);
                    // }
                }
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $objPHPExcel1 = $objReader->load($fullpath);
                $sheet1 = $objPHPExcel1->getSheet(0);
                $highestColumn1 = $sheet1->getHighestColumn();
                $highestRow1 = $sheet1->getHighestDataRow();
                $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
               // $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
                
                
                 }
               
            
                       
        }
        else{
            return response()->json(['success'=>false,'message'=>'File not found!']);
        }

     
        unset($rowData1[0]);
        $rowData1=array_values($rowData1);
        //store ghost image
      //  $tmpDir = $this->createTemp(public_path().'/backend/images/ghosttemp/temp');
        $admin_id = \Auth::guard('admin')->user()->toArray();

        
        $pdfData=array('studentDataOrg'=>$rowData1,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'previewPdf'=>$previewPdf,'excelfile'=>$excelfile);
                $link = $this->dispatch(new PdfGenerateKenyaPassingJob($pdfData));
        //$link=$this->certificateGeneratePassing($rowData1,$template_id,$previewPdf,$excelfile);
        return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link]);
    }
    
    public function certificateGeneratePassing($studentDataOrg,$template_id,$previewPdf,$excelfile){

        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];

        $admin_id = \Auth::guard('admin')->user()->toArray();
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $auth_site_id=Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        // dd($systemConfig);
        $printer_name = $systemConfig['printer_name'];

    

        $ghostImgArr = array();
        $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdfBig->SetCreator(PDF_CREATOR);
        $pdfBig->SetAuthor('TCPDF');
        $pdfBig->SetTitle('Certificate');
        $pdfBig->SetSubject('');

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
       // $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);
       // $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $timesNewRomanB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.TTF', 'TrueTypeUnicode', '', 96);
        $log_serial_no = 1;
        $cardDetails=$this->getNextCardNo('Kenya-PC');
        $card_serial_no=$cardDetails->next_serial_no;
        foreach ($studentDataOrg as $studentData) {
         

         $pdfBig->AddPage();
        
        //set background image
        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\Kenya PASSING CERTIFICATE Bg Plain.jpg';
       
        if($previewPdf==1){
            if($previewWithoutBg!=1){
            $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);

        }

        $date_font_size = '11';
        $date_nox = 8;
        $date_noy = 47;
        $date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
        $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
       // $pdf->SetTextColor(189, 189, 189,7,false,'');
        $pdfBig->SetTextColor(192,192,192);
        $pdfBig->SetXY($date_nox, $date_noy);
        $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'C');

        }
        $pdfBig->setPageMark();
 

        $ghostImgArr = array();
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo


        $pdf->AddPage();
         $print_serial_no = $this->nextPrintSerial();
        //set background image
        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\Kenya PASSING CERTIFICATE Bg Plain.jpg';
        // dd($template_img_generate);
        
        if($previewPdf!=1){
        if($previewWithoutBg!=1){
        $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
        }
        }
        $pdf->setPageMark();

        if($previewPdf!=1){
        $fontEBPath=public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\E-13B_0.php';
        $pdf->AddFont('E-13B_0', '', $fontEBPath);
        $pdfBig->AddFont('E-13B_0', '', $fontEBPath);
         //set enrollment no
        $card_serial_no_font_size = '13';
        $card_serial_nox= 171.5;
        $card_serial_noy = 35.5;
        $pdf->SetFont('E-13B_0', '', $card_serial_no_font_size, '', false);
        $pdf->SetXY($card_serial_nox, $card_serial_noy);
        $pdf->Cell(23.5, 0, $card_serial_no, 0, false, 'R');


        $pdfBig->SetFont('E-13B_0', '', $card_serial_no_font_size, '', false);
        $pdfBig->SetXY($card_serial_nox, $card_serial_noy);
        $pdfBig->Cell(23.5, 0, $card_serial_no, 0, false, 'R');
        }
        //Date
        $date_font_size = '14';
        $date_nox = 166;
        $date_noy = 45;
        $date_nostr = 'Date : '.$studentData[9];
        $pdf->SetFont($arial, '', $date_font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($date_nox, $date_noy);
        $pdf->Cell(12, 0, $date_nostr, 0, false, 'C');

        $pdfBig->SetFont($arial, '', $date_font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($date_nox, $date_noy);
        $pdfBig->Cell(12, 0, $date_nostr, 0, false, 'C');
                
        $font_size = '16';
        $x = 8;
        $y = 68;
        $str = 'This is to certify that';
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'C');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'C');

        $font_size = '18';
        $x = 8;
        $y = 78;
        $name = $studentData[0];
        $pdf->SetFont($timesNewRomanB, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $name, 0, false, 'C');

        $pdfBig->SetFont($timesNewRomanB, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $name, 0, false, 'C');

        $font_size =  '16';
        $str = "Mother's name : ";
        $mother_str = $studentData[1];
        $stry = 88;

        $result = $this->GetStringPositions(
            array(
                array($str, $arial, '', $font_size), 
                array($mother_str, $arialb, '', $font_size)
            ),$pdf
        );

    
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetXY($result[0], $stry);
        $pdf->Cell(0, 0, $str, 0, false, 'L');

        $pdf->SetFont($arialb, '', $font_size, '', false);
        $pdf->SetXY($result[1], $stry);
        $pdf->Cell(0, 0, $mother_str, 0, false, 'L');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetXY($result[0], $stry);
        $pdfBig->Cell(0, 0, $str, 0, false, 'L');

        $pdfBig->SetFont($arialb, '', $font_size, '', false);
        $pdfBig->SetXY($result[1], $stry);
        $pdfBig->Cell(0, 0, $mother_str, 0, false, 'L');

        $font_size = '16';
        $x = 8;
        $y = 98;
        $str = 'has appeared for the';
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'C');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'C');
        
        $font_size = '18';
        $x = 8;
        $y = 110;
        $str = $studentData[2];
        $pdf->SetFont($timesNewRomanB, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(196, 0, $str, 0, 'C', 0, 0, '7', '', true); 

        $pdfBig->SetFont($timesNewRomanB, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->MultiCell(196, 0, $str, 0, 'C', 0, 0, '7', '', true);    

        if(strlen($str)<49){
            $offset=6;
        }else{
            $offset=0;
        }

        $font_size =  '16';
        $str = "examination held in month of ";
        $str2 = $studentData[3];
        $stry = 130-$offset;

        $result = $this->GetStringPositions(
            array(
                array($str, $arial, '', $font_size), 
                array($str2, $arialb, '', $font_size)
            ),$pdf
        );

    
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetXY($result[0], $stry);
        $pdf->Cell(0, 0, $str, 0, false, 'L');

        $pdf->SetFont($arialb, '', $font_size, '', false);
        $pdf->SetXY($result[1], $stry);
        $pdf->Cell(0, 0, $str2, 0, false, 'L');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetXY($result[0], $stry);
        $pdfBig->Cell(0, 0, $str, 0, false, 'L');

        $pdfBig->SetFont($arialb, '', $font_size, '', false);
        $pdfBig->SetXY($result[1], $stry);
        $pdfBig->Cell(0, 0, $str2, 0, false, 'L');

        $font_size =  '16';
        $str = "and declare to have passed the examination with ";
        $str2 = $studentData[4];
        $str3 = ' grade.';
        $stry = 140-$offset;

        $result = $this->GetStringPositions(
            array(
                array($str, $arial, '', $font_size), 
                array($str2, $arialb, '', $font_size),
                array($str3, $arial, '', $font_size),
            ),$pdf
        );

    
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetXY($result[0], $stry);
        $pdf->Cell(0, 0, $str, 0, false, 'L');

        $pdf->SetFont($arialb, '', $font_size, '', false);
        $pdf->SetXY($result[1], $stry);
        $pdf->Cell(0, 0, $str2, 0, false, 'L');

        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetXY($result[2], $stry);
        $pdf->Cell(0, 0, $str3, 0, false, 'L');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetXY($result[0], $stry);
        $pdfBig->Cell(0, 0, $str, 0, false, 'L');

        $pdfBig->SetFont($arialb, '', $font_size, '', false);
        $pdfBig->SetXY($result[1], $stry);
        $pdfBig->Cell(0, 0, $str2, 0, false, 'L');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetXY($result[2], $stry);
        $pdfBig->Cell(0, 0, $str3, 0, false, 'L');

        $programme = $studentData[5];
        if(!empty($programme)){

        $font_size = '16';
        $x = 8;
        $y = 150-$offset;
        $str = 'This is further to certify that her/his special subject at the said examination is';
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'C');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'C');

        
        $font_size = '18';
        $x = 8;
        $y = 160-$offset;
        $pdf->SetFont($timesNewRomanB, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $programme, 0, false, 'C');

        $pdfBig->SetFont($timesNewRomanB, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $programme, 0, false, 'C');

        $font_size = '16';
        $x = 8;
        $y = 170-$offset;
        $str = 'She/He is eligible for the aforesaid Degree Certificate,';
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'C');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'C');

        
        $font_size = '16';
        $x = 8;
        $y = 180-$offset;
        $str = 'whenever she/he applies for the same at University Convocation.';
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'C');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'C');


        }else{

        $font_size = '16';
        $x = 9.5;
        $y = 150-$offset;
        $str = 'This is further to certify that she/he is eligible for the aforesaid Degree';
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'C'); 

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'C');    

        $offset=$offset+20;
        

        $font_size = '16';
        $x = 10.5;
        $y = 180-$offset;
        $str = 'Certificate, whenever she/he applies for the same at University Convocation.';
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'C');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'C');

        }

        


        $font_size = '14';
        $x = 22;
        $y = 215;
        $str = 'Seat No: '.$studentData[6];
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'L');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'L');

        $font_size = '14';
        $x = 22;
        $y = 222;
        $str = 'P.R.NO.: '.$studentData[7];
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'L');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'L');

        $font_size = '14';
        $x = 22;
        $y = 229;
        $str = 'College Code: '.$studentData[8];
        $pdf->SetFont($arial, '', $font_size, '', false);
        $pdf->SetTextColor(0,0,0,100,false,'');
        $pdf->SetXY($x, $y);
        $pdf->Cell(0, 0, $str, 0, false, 'L');

        $pdfBig->SetFont($arial, '', $font_size, '', false);
        $pdfBig->SetTextColor(0,0,0,100,false,'');
        $pdfBig->SetXY($x, $y);
        $pdfBig->Cell(0, 0, $str, 0, false, 'L');

        $dt = date("_ymdHis");
        $blobFile = pathinfo($studentData[10]);

        $serial_no=$GUID=$blobFile['filename'];
        //qr code    
        $codeContents =$studentData[11];
        $codeContentsPath = strtoupper(md5($GUID));

        $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$codeContentsPath.'.png';
        $qrCodex = 170;
        $qrCodey = 210;
        $qrCodeWidth =20;
        $qrCodeHeight = 20;
                
        \QrCode::size(75.6)
            ->format('png')
            ->generate($codeContents, $qr_code_path);

        $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
        $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
        
        $microlinestr=$name;
        $pdf->SetFont($arialb, '', 2, '', false);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(160, 229);
        $pdf->Cell(0, 0, $microlinestr, 0, false, 'C');

        $pdfBig->SetFont($arialb, '', 2, '', false);
        $pdfBig->SetTextColor(0, 0, 0);
        $pdfBig->SetXY(160, 229);
        $pdfBig->Cell(0, 0, $microlinestr, 0, false, 'C');
        

        // Ghost image
        $ghost_font_size = '13';
        $ghostImagex = 23;
        $ghostImagey = 275.5;
        $ghostImageWidth = 44;
        $ghostImageHeight = 8;
        $name = str_replace(' ','',substr($name, 0, 6));

        $tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
        if(!array_key_exists($name, $ghostImgArr))
        {
            $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');
            $ghostImgArr[$name] = $w;   
        }
        else{
            $w = $ghostImgArr[$name];
        }

        $pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);
        $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);

         $signature_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Bmcc Principal sign.png';
        $signaturex = 150;
        $signaturey = 258;
        $signatureWidth = 30;
        $signatureHeight = 11;
        $pdf->image($signature_path,$signaturex,$signaturey,$signatureWidth,$signatureHeight,"",'','L',true,3600);           
        $pdfBig->image($signature_path,$signaturex,$signaturey,$signatureWidth,$signatureHeight,"",'','L',true,3600);  

        //delete temp dir 26-04-2022 
        CoreHelper::rrmdir($tmpDir);
        if($previewPdf!=1){

         $certName = str_replace("/", "_", $GUID) .".pdf";
            // $myPath =    ().'/backend/temp_pdf_file';
            //$myPath = public_path().'/backend/temp_pdf_file';
            $myPath = public_path().'/backend/temp_pdf_file';
            $dt = date("_ymdHis");

            $fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;


            // print_r($pdf);
            // print_r("$tmpDir/" . $name."".$ghost_font_size.".png");
            $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');
       // $pdf->Output('sample.pdf', 'F');

             $this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id,'Kenya\PC\\');

            $username = $admin_id['username'];
            date_default_timezone_set('Asia/Kolkata');

            $content = "#".$log_serial_no." serial No :".$serial_no.PHP_EOL;
            $date = date('Y-m-d H:i:s').PHP_EOL;
            $print_datetime = date("Y-m-d H:i:s");
            

            $print_count = $this->getPrintCount($serial_no);
            $printer_name = /*'HP 1020';*/$printer_name;
            $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'Kenya-PC',$admin_id,$card_serial_no);

            $card_serial_no=$card_serial_no+1;
            }
       } 
       if($previewPdf!=1){
        $this->updateCardNo('Kenya-PC',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
       }
       $msg = '';
        // if(is_dir($tmpDir)){
        //     rmdir($tmpDir);
        // }   card_serial_no
       // $file_name = $template_data['template_name'].'_'.date("Ymdhms").'.pdf';
        //print_r($fetch_degree_array);
      //  exit;
        $file_name =  str_replace("/", "_",'Kenya-PC'.date("Ymdhms")).'.pdf';
        
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();


        $filename = public_path().'/backend/tcpdf/examples/'.$file_name;
        // $filename = 'C:\xampp\htdocs\seqr\public\backend\tcpdf\exmples\/'.$file_name;
        $pdfBig->output($filename,'F');

       
       

        if($previewPdf!=1){
             $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
             @unlink($filename);
             
            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name="Kenya-PC";
            // add sandboxing code
            CoreHelper::sandboxingDB($systemConfig,$template_name,$excelfile,$file_name,$user,$no_of_records,$auth_site_id);
            // if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            //     // with sandbox
            //     $result = SbExceUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
            // }else{
            //     // without sandbox
            //     $result = ExcelUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
            // } 


            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
            $msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/".$file_name."'class='downloadpdf' download target='_blank'>Here</a> to download file<b>";

        }else{
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/preview/'.$file_name);
            @unlink($filename);
            // add sandboxing code
            CoreHelper::sandboxingDB($systemConfig,$template_name,$excelfile,$file_name,$user,$no_of_records,$auth_site_id);
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
            $msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/preview/".$file_name."'class='downloadpdf' download target='_blank'>Here</a> to download file<b>";
        }
         
         //               }
                    //}
        //echo $msg;
        return $msg;

    }


 public function uploadPdfsToServer(){
         $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
            $certName="abc.pdf";
         //  echo $pdfActualPath=public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;

        $files=$this->getDirContents(public_path().'/'.$subdomain[0].'/backend/pdf_file/');

foreach ($files as $filename) {
echo $filename."<br>";
}



        //Sore file on azure server
        //CoreHelper::uploadBlob($pdfActualPath,$blob.$certName);
    }

public function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } /*else if ($value != "." && $value != "..") {
            $this->getDirContents($path, $results);
            $results[] = $path;
        }*/
    }

    return $results;
}

public function downloadPdfsFromServer(){
 $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
$accesskey = "Tz7IOmG/9+tyxZpRTAam+Ll3eqA9jezqHdqSdgi+BjHsje0+VM+pKC6USBuR/K0nkw5E7Psw/4IJY3KMgBMLrA==";
$storageAccount = 'seqrdocpdf';

//$filetoUpload = realpath('./692671.pdf');
//$containerName = 'desdocument';
$containerName = 'pdffile';
//$containerPrefix="/desdocument";


        $files=$this->getDirContents(public_path().'/'.$subdomain[0].'/backend/pdf_file/');

foreach ($files as $filename) {
$myFile = pathinfo($filename); 
$blobName = 'Kenya\PC\\'.$myFile['basename'];
echo $destinationURL = "https://$storageAccount.blob.core.windows.net/$containerName/$blobName";


//$file_url = "https://mysite.blob.core.windows.net/container-name/" . $blob_name;   
$local_server_file_path= public_path().'/'.$subdomain[0].'/backend/pdf_file_downloaded/'.$blobName;
if(file_exists($destinationURL)){
file_put_contents($local_server_file_path, file_get_contents($destinationURL));
}
}

}

    public function addCertificate($serial_no, $certName, $dt,$template_id,$admin_id,$blob)
    {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $file1 = public_path().'/backend/temp_pdf_file/'.$certName;
        $file2 = public_path().'/backend/pdf_file/'.$certName;
        
        $auth_site_id=Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        $pdfActualPath=public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;

        // dd($file1);
        copy($file1, $file2);
        
        $aws_qr = \File::copy($file2,$pdfActualPath);
                // $msg = "<b>PDF will be sent in mail<b>";
            
        @unlink($file2);

        @unlink($file1);

        //Sore file on azure server
        //Storage::disk('azure')->put('GC\\'.$certName, fopen($pdfActualPath, 'r+'));
        //CoreHelper::uploadBlob($pdfActualPath,$blob.$certName);


        $sts = '1';
        $datetime  = date("Y-m-d H:i:s");
        $ses_id  = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();
     
        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path().'/backend/qr';
        $key = strtoupper(md5($serial_no)); 
        $codeContents = $key;
        $fileName = $key.'.png'; 
        
        $urlRelativeFilePath = 'qr/'.$fileName; 

        if($systemConfig['sandboxing'] == 1){
            $resultu = SbStudentTable::where('serial_no','T-'.$serial_no)->update(['status'=>'0']);
            // Insert the new record
            $result = SbStudentTable::create(['serial_no'=>'T-'.$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id]);
        }else{
            $resultu = StudentTable::where('serial_no',$serial_no)->update(['status'=>'0']);
            // Insert the new record
            $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id]);
        }
        
    }

    public function getPrintCount($serial_no)
    {
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $numCount = PrintingDetail::select('id')->where('sr_no',$serial_no)->count();
        
        return $numCount + 1;
    }

    public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no,$template_name,$admin_id,$card_serial_no)
    {
        // dd($sr_no);
        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];

        $auth_site_id=Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        if($systemConfig['sandboxing'] == 1){
            $result = SbPrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>'T-'.$sr_no,'template_name'=>$template_name,'card_serial_no'=>$card_serial_no,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);
        }else{
            $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>$sr_no,'template_name'=>$template_name,'card_serial_no'=>$card_serial_no,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);    
        }
    }

    public function nextPrintSerial()
    {
        $current_year = 'PN/' . $this->getFinancialyear() . '/';
        // find max
        $maxNum = 0;
        
        $auth_site_id=Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $result = \DB::select("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num "
                . "FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '$current_year'");
        //get next num
        $maxNum = $result[0]->next_num + 1;
        // dd($current_year . $maxNum);
        return $current_year . $maxNum;
    }

    public function getNextCardNo($template_name)
    { 
        $auth_site_id=Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        if($systemConfig['sandboxing'] == 1){
            $result = \DB::select("SELECT * FROM sb_card_serial_numbers WHERE template_name = '$template_name'");
        }else{
            $result = \DB::select("SELECT * FROM card_serial_numbers WHERE template_name = '$template_name'");
        }
          
        return $result[0];
    }

    public function updateCardNo($template_name,$count,$next_serial_no)
    { 
        $auth_site_id=Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        if($systemConfig['sandboxing'] == 1){
            $result = \DB::select("UPDATE sb_card_serial_numbers SET card_count='$count',next_serial_no='$next_serial_no' WHERE template_name = '$template_name'");
        }else{
            $result = \DB::select("UPDATE card_serial_numbers SET card_count='$count',next_serial_no='$next_serial_no' WHERE template_name = '$template_name'");
        }
        
        return $result;
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

    public function createTemp($path){
        //create ghost image folder
        $tmp = date("ymdHis");
        // print_r($path);
        // dd($tmp);
        $tmpname = tempnam($path, $tmp);
        //unlink($tmpname);
        //mkdir($tmpname);
        if (file_exists($tmpname)) {
         unlink($tmpname);
        }
        mkdir($tmpname, 0777);
        return $tmpname;
    }

    public function CreateMessage($tmpDir, $name = "",$font_size,$print_color)
    {
        if($name == "")
            return;
        $name = strtoupper($name);
        // Create character image
        if($font_size == 15 || $font_size == "15"){


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

            $filename = public_path()."/backend/canvas/ghost_images/F10_RND_latest.png";//F15_H14_W504

            $charsImage = imagecreatefrompng($filename);
            imagealphablending($charsImage, false);
            imagesavealpha($charsImage, true);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((14 * $currentX)/ $size[1]);

        }else if($font_size == 12){

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
                
                $filename = public_path()."/backend/canvas/ghost_images/F12_H8_W288.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((8 * $currentX)/ $size[1]);

        }else if($font_size == "10" || $font_size == 10){
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
            

            $filename = public_path()."/backend/canvas/ghost_images/F10_H5_W180_TWO_IMG.png";//F10_H5_W180//F10_RND_latest
            $charsImage = imagecreatefrompng($filename);
            
           // imagealphablending($charsImage, false);
           // imagesavealpha($charsImage, true);
            //$filename = public_path()."/backend/canvas/ghost_images/F10_RND_latest.png";//F10_H5_W180
            //$charsImage2 = imagecreatefrompng($filename);
            //imagealphablending($charsImage2, false);
            //imagesavealpha($charsImage2, true);
            
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";//alpha_GHOST
            $bgImage = imagecreatefrompng($filename);

            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
               // imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 70);
                 imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }
            
           

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
             $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((5 * $currentX)/ $size[1]);

        }else if($font_size == 11){

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
            
                $filename = public_path()."/backend/canvas/ghost_images/F11_H7_W250.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            

            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((7 * $currentX)/ $size[1]);

        }else if($font_size == "13" || $font_size == 13){

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

                
            $filename = public_path()."/backend/canvas/ghost_images/F13_H10_W360.png";
            $charsImage = imagecreatefrompng($filename);
            //$filename = public_path()."/backend/canvas/ghost_images/F10_RND_latest.png";//F15_H14_W504

            //$charsImage = imagecreatefrompng($filename);
            //imagealphablending($charsImage, false);
            //imagesavealpha($charsImage, true);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            // dd($rect);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX)/ $size[1]);

        }else if($font_size == "14" || $font_size == 14){

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
                
                $filename = public_path()."/backend/canvas/ghost_images/F14_H12_W432.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((12 * $currentX)/ $size[1]);

        }else{
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

            $filename = public_path()."/backend/canvas/ghost_images/ALPHA_GHOST.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);

            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX)/ $size[1]);
        }
    }

    function GetStringPositions($strings,$pdf)
    {
        $len = count($strings);
        $w = array();
        $sum = 0;
        foreach ($strings as $key => $str) {
            $width = $pdf->GetStringWidth($str[0], $str[1], $str[2], $str[3], false);
            $w[] = $width;
            $sum += intval($width);
            // print_r($w);echo "<pre>";
        }
        // print_r($w);
        // dd($sum);
        // exit();
        $ret = array();
        $ret[0] = (205 - $sum)/2;
        for($i = 1; $i < $len; $i++)
        {
            $ret[$i] = $ret[$i - 1] + $w[$i - 1] ;
            // print_r($ret);echo "<pre>";
        }
        // exit();
        return $ret;
    }

    function sanitizeQrString($content){
         $find = array('â€œ', 'â€™', 'â€¦', 'â€”', 'â€“', 'â€˜', 'Ã©', 'Â', 'â€¢', 'Ëœ', 'â€'); // en dash
         $replace = array('“', '’', '…', '—', '–', '‘', 'é', '', '•', '˜', '”');
        return $content = str_replace($find, $replace, $content);
    }
}
