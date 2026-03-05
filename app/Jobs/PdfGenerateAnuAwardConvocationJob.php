<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\models\BackgroundTemplateMaster;
use Session, TCPDF, TCPDF_FONTS, Auth, DB;
use App\models\FontMaster;
use App\models\SystemConfig;
use QrCode;
use App\models\Config;
use App\models\StudentTable;
use App\models\SbStudentTable;
use App\models\PrintingDetail;
use App\models\SbPrintingDetail;
use App\models\ExcelUploadHistory;
use App\models\SbExceUploadHistory;
use App\Jobs\SendMailJob;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use Illuminate\Support\Facades\Redis;
use App\models\ThirdPartyRequests;
use App\Helpers\CoreHelper;
use Helper;

class PdfGenerateAnuAwardConvocationJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 180000;
    protected $pdf_data;


    public function __construct($pdf_data)
    {
        $this->pdf_data = $pdf_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    // public function handleold(CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    // {
    //     $pdf_data = $this->pdf_data;        

    //     $studentDataOrg=$pdf_data['studentDataOrg'];
    //     $subjectDataOrg=$pdf_data['subjectDataOrg'];
    //     $subjectDataOrg1=$pdf_data['subjectDataOrg'];
    //     $subjectDataOrg2=$pdf_data['subjectDataOrg'];
    //     $template_id=$pdf_data['template_id'];
    //     $dropdown_template_id=$pdf_data['dropdown_template_id'];
    //     $points=$pdf_data['points'];
    //     $previewPdf=$pdf_data['previewPdf'];
    //     $excelfile=$pdf_data['excelfile'];
    //     $auth_site_id=$pdf_data['auth_site_id'];
    //     $previewWithoutBg=$previewPdf[1];
    //     $previewPdf=$previewPdf[0];
    //     $photo_col=23;

    //     $first_sheet=$pdf_data['studentDataOrg']; // get first worksheet rows
    //     //print_r($second_sheet); exit;

    //     // echo "<pre>";
    //     // print_r($studentDataOrg);
    //     // echo "</pre>";

    //     if(isset($pdf_data['generation_from'])&&$pdf_data['generation_from']=='API'){        
    // 		$admin_id=$pdf_data['admin_id'];
    //     }else{
    // 		$admin_id = \Auth::guard('admin')->user()->toArray();  
    //     }
    //     $domain = \Request::getHost();
    //     $subdomain = explode('.', $domain);

    //     $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
    //     $printer_name = $systemConfig['printer_name'];


    //     $ghostImgArr = array();
    //      if(!empty($loader_data) && isset($loader_data['generatedCertificates'])){

    // 		$generated_documents=$loader_data['generatedCertificates'];  

    // 	}else{
    // 		$generated_documents=0;  
    // 	}


    //     if($generated_documents == 0){
    // 		Session::forget('pdf_data_obj');
    // 		$pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
    // 		$pdfBig->SetCreator(PDF_CREATOR);
    // 		$pdfBig->SetAuthor('TCPDF');
    // 		$pdfBig->SetTitle('Certificate');
    // 		$pdfBig->SetSubject('');
    // 		// Session::put('pdf_obj', $pdfBig);


    // 	}else{ 
    // 		 if(Session::get('pdf_data_obj') != null){
    // 		$pdfBig = Session::get('pdf_data_obj');   
    // 		}
    // 	}

    //     // remove default header/footer
    //     $pdfBig->setPrintHeader(false);
    //     $pdfBig->setPrintFooter(false);
    //     $pdfBig->SetAutoPageBreak(false, 0);


    //     // add spot colors
    //     $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
    //     $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
    //     $pdfBig->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
    //     //set fonts
    //     $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
    //     $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
    //      $EBGaramondExtraBold = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\EBGaramond-ExtraBold_0.TTF', 'TrueTypeUnicode', '', 96);
    //     $EBGaramondRegular = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\EBGaramond-Regular_0.TTF', 'TrueTypeUnicode', '', 96);
    //     $georgia = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\georgia.TTF', 'TrueTypeUnicode', '', 96);
    //     $MontserratBold = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Montserrat-Bold.TTF', 'TrueTypeUnicode', '', 96);
    //     $MontserratRegular = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Montserrat-Regular.TTF', 'TrueTypeUnicode', '', 96);
    //     $MontserratSemiBold = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Montserrat-SemiBold.TTF', 'TrueTypeUnicode', '', 96);

    //     $preview_serial_no=1;
    // 	//$card_serial_no="";
    //     $log_serial_no = 1;
    //     $cardDetails=$this->getNextCardNo('anuA');
    //     $card_serial_no=$cardDetails->next_serial_no;
    //     // $generated_documents=0;  //for custom loader






    //     $sftpData = [];
    //     if($studentDataOrg&&!empty($studentDataOrg)){
    //         foreach ($studentDataOrg as $studentData) {

    //             if($card_serial_no>999999&&$previewPdf!=1){
    //                 echo "<h5>Your card series ended...!</h5>";
    //                 exit;
    //             }
    //             //For Custom Loader
    //             $startTimeLoader =  date('Y-m-d H:i:s');    
    //             $high_res_bg="Award_Certificate_Nlank.jpg"; // anu_gradecard_front
    //             $low_res_bg="Award_Certificate_Nlank.jpg";
    //             $pdfBig->AddPage();
    //             $pdfBig->SetFont($arialNarrowB, '', 8, '', false);
    //             //set background image
    //             $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;   

    //             if($previewPdf==1){
    //                 if($previewWithoutBg!=1){
    //                     $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
    //                 }

    //                 $date_font_size = '11';
    //                 $date_nox = 13;
    //                 $date_noy = 27;
    //                 $date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
    //                 $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
    //                 $pdfBig->SetTextColor(192,192,192);
    //                 $pdfBig->SetXY($date_nox, $date_noy);
    //                 // $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
    //                 $pdfBig->SetTextColor(0,0,0,100,false,'');
    //                 $pdfBig->SetFont($arialNarrowB, '', 9, '', false);
    //             }
    //             $pdfBig->setPageMark();

    //             $ghostImgArr = array();
    //             $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
    //             $pdf->SetCreator(PDF_CREATOR);
    //             $pdf->SetAuthor('TCPDF');
    //             $pdf->SetTitle('A4 TRANSCRIPT');
    //             $pdf->SetSubject('');

    //             // remove default header/footer
    //             $pdf->setPrintHeader(false);
    //             $pdf->setPrintFooter(false);
    //             $pdf->SetAutoPageBreak(false, 0);


    //             // add spot colors
    //             //$pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
    //             //$pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

    //             $pdf->AddPage();        
    //             $print_serial_no = $this->nextPrintSerial();
    //             //set background image
    //             $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$low_res_bg;

    //             if($previewPdf!=1){
    //             $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
    //             }
    //             //$pdf->setPageMark();
    //             $pdf->setPageMark();
    //             //$pdfBig->setPageMark();
    //             //if($previewPdf!=1){            
    //                 $x= 173;
    //                 $y = 39.1;
    //                 $font_size=12;
    //                 if($previewPdf!=1){
    //                     $str = str_pad($card_serial_no, 7, '0', STR_PAD_LEFT);
    //                 }else{
    //                     $str = str_pad($preview_serial_no, 7, '0', STR_PAD_LEFT);	
    //                 }
    //                 $strArr = str_split($str);
    //                 $x_org=$x;
    //                 $y_org=$y;
    //                 $font_size_org=$font_size;
    //                 $i =0;
    //                 $j=0;
    //                 $y=$y+4.5;
    //                 $z=0;
    //                 foreach ($strArr as $character) {
    //                     $pdf->SetFont($arialNarrow,0, $font_size, '', false);
    //                     $pdf->SetXY($x, $y+$z);

    //                     $pdfBig->SetFont($arialNarrow,0, $font_size, '', false);
    //                     $pdfBig->SetXY($x, $y+$z);

    //                     if($i==3){
    //                         $j=$j+0.2;
    //                     }else if($i>1){
    //                         $j=$j+0.1;   
    //                     }

    //                 if($i>1){
    //                     $z=$z+0.1;
    //                     }
    //                     if($i>3){
    //                     $x=$x+0.4;  
    //                     }else if($i>2){
    //                     $x=$x+0.2;
    //                     } 
    //                 //$pdf->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
    //                 //$pdfBig->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
    //                     $i++;
    //                     $x=$x+2.2+$j; 
    //                     if($i>2){
    //                     $font_size=$font_size+1.7;   
    //                     }
    //                 }         
    //             //}


    //             $serial_no=$GUID=$studentData[0];
    //             //start Bigpdf
    //                 $X= 18;
    //                 $Y= 70;
    //                 $pdfBig->SetFont($EBGaramondRegular, '', 18, '', false); 
    //                 $pdfBig->setCellPaddings( $left = '', $top = '', $right = '', $bottom = '');
    //                 $pdfBig->SetXY($X, $Y);  
    //                 $pdfBig->MultiCell(174, 10, $studentData[2], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdfBig->Ln();
    //                 $pdfBig->SetFont($EBGaramondRegular, '', 28, '', false); 
    //                 $pdfBig->SetXY($X, $pdfBig->GetY());  
    //                 $pdfBig->MultiCell(174, 10, $studentData[3], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdfBig->Ln();
    //                 $pdfBig->SetFont($EBGaramondRegular, '', 18, '', false); 
    //                 $pdfBig->SetXY($X, $pdfBig->GetY());  
    //                 $pdfBig->MultiCell(174, 10, $studentData[4], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdfBig->Ln();
    //                 $pdfBig->SetFont($EBGaramondExtraBold, '', 26, '', false); 
    //                 $pdfBig->SetXY($X, $pdfBig->GetY());  
    //                 $pdfBig->MultiCell(174, 10, $studentData[5], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdfBig->Ln();
    //                 $pdfBig->SetFont($EBGaramondRegular, '', 18, '', false); 
    //                 $pdfBig->SetXY($X, $pdfBig->GetY());  
    //                 $pdfBig->MultiCell(174, 10, $studentData[6], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdfBig->Ln();
    //                 $pdfBig->SetXY($X, $pdfBig->GetY());  
    //                 $pdfBig->MultiCell(174, 10, $studentData[7].' '. $studentData[8], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdfBig->Ln();
    //                 $pdfBig->SetXY($X, $pdfBig->GetY());  
    //                 $pdfBig->MultiCell(174, 10, $studentData[9], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdfBig->Ln();
    //                 $pdfBig->SetXY($X, $pdfBig->GetY());  
    //                 $pdfBig->MultiCell(174, 10, $studentData[10], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdfBig->Ln();
    //                 $pdfBig->SetXY($X, $pdfBig->GetY());  
    //                 $pdfBig->MultiCell(174, 10, $studentData[11], 0, 'C', 0, 0, '', '', true, 0, true);

    //                 // $pdfBig->SetFont($georgia, '', 17, '', false); 
    //                 //  $pdfBig->SetXY(30, 215);  
    //                 // $pdfBig->MultiCell(174, 10, $studentData[1], 0, 'L', 0, 0, '', '', true, 0, true);


    //             //    $number = (string)$studentData[1]; // ensure it's a string
    //             //     $x = 30;   // starting X position
    //             //     $y = 215;  // Y position (same line)
    //             //     $baseSize = 10;        // base font size
    //             //     $spacingFactor = 0.20; // adjust spacing between digits

    //             //     foreach (str_split($number) as $digit) {
    //             //         // Convert digit to integer
    //             //         $d = (int)$digit;

    //             //         // Calculate font size (e.g. base + digit value)
    //             //         $fontSize = $baseSize + $d;

    //             //         // Set font for this digit
    //             //         $pdfBig->SetFont($georgia, '', $fontSize, '', false);

    //             //         // Print this digit
    //             //         $pdfBig->SetXY($x, $y);
    //             //         $pdfBig->Cell(0, 0, $digit, 0, 0, 'L', 0, '', 0, false, 'T', 'M');
    //             //         $x += $fontSize * $spacingFactor;
    //             //     }


    //             // $number = (string)$studentData[1]; // e.g. 356744357
    //             // $x = 30;   // starting X
    //             // $y = 215;  // baseline Y
    //             // $baseSize = 10;
    //             // $spacingFactor = 0.20;

    //             // // baseline adjustment reference
    //             // $baselineFontSize = 10; // smallest font size baseline reference

    //             // foreach (str_split($number) as $digit) {
    //             //     $d = (int)$digit;
    //             //     $fontSize = $baseSize + $d;

    //             //     // dd($d );
    //             //     // Set font size
    //             //     $pdfBig->SetFont($georgia, '', $fontSize, '', false);

    //             //     // Adjust Y so the text baseline aligns (larger font → move Y up slightly)
    //             //     $adjustedY = $y - ($fontSize - $baselineFontSize) * 0.35;

    //             //     // Print digit
    //             //     $pdfBig->SetXY($x, $adjustedY);
    //             //     $pdfBig->Cell(0, 0, $digit, 0, 0, 'L', 0, '', 0, false, 'T', 'M');

    //             //     // Move next X
    //             //     $x += $fontSize * $spacingFactor;
    //             // }


    //             $number = (string)$studentData[1]; // e.g. 92345678537
    //             $x = 30;   // starting X position
    //             $y = 215;  // base Y position

    //             $startFontSize = 11;   // starting font size
    //             $spacingFactor = 0.20; // horizontal spacing factor
    //             $fontSize = $startFontSize;

    //             foreach (str_split($number) as $digit) {
    //                 $pdfBig->SetFont($georgia, '', $fontSize, '', false);
    //                 $adjustedY = $y; 
    //                 $pdfBig->SetXY($x, $adjustedY);
    //                 $pdfBig->Cell(0, 0, $digit, 0, 0, 'L', 0, '', 0, false, 'L', 'M');
    //                 $x += $fontSize * $spacingFactor;
    //                 $fontSize++;
    //             }

    //             $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');

    //                 if(!empty($studentData[12])){
    //                     $pdfBig->SetFont($arial, '',10, '', false);
    //                     $pdfBig->SetXY(25, 256);

    //                     $pdfBig->Cell(40, 0, '____________________', 0, false, 'C');
    //                     $pdfBig->SetFont($MontserratRegular, '', 9.51, '', false);
    //                     $pdfBig->SetTextColor(0, 0, 255);
    //                     $pdfBig->SetXY(25, 261);
    //                     $pdfBig->Cell(40, 0, $studentData[12], 0, false, 'C');
    //                     $pdfBig->SetFont($MontserratRegular, '', 7.78, '', false);
    //                     $pdfBig->SetXY(25, 267);
    //                     $pdfBig->Cell(40, 0, $studentData[13], 0, false, 'C');
    //                 }

    //                 if(!empty($studentData[14])){
    //                     $pdfBig->SetTextColor(0, 0, 0);
    //                         $pdfBig->SetFont($arial, '',10, '', false);
    //                         $pdfBig->SetXY(146, 256);
    //                         $pdfBig->Cell(40, 0, '____________________', 0, false, 'C');
    //                         $pdfBig->SetFont($MontserratRegular, '', 9.51, '', false);
    //                         $pdfBig->SetTextColor(0, 0, 255);
    //                         $pdfBig->SetXY(146, 261);
    //                         $pdfBig->Cell(40, 0, $studentData[14], 0, false, 'C');
    //                         $pdfBig->SetFont($MontserratRegular, '', 7.78, '', false);
    //                         $pdfBig->SetXY(146, 267);
    //                         $pdfBig->Cell(40, 0, $studentData[15], 0, false, 'C');
    //                 }


    //                     if(!empty($studentData[16])){
    //                         $pdfBig->SetTextColor(0, 0, 0);
    //                         $pdfBig->SetFont($arial, '',10, '', false);
    //                         $pdfBig->SetXY(85.3, 256);
    //                         $pdfBig->Cell(40, 0, '____________________', 0, false, 'C');
    //                         $pdfBig->SetFont($MontserratRegular, '', 9.51, '', false);
    //                         $pdfBig->SetTextColor(0, 0, 255);
    //                         $pdfBig->SetXY(85.3, 261);
    //                         $pdfBig->Cell(40, 0, $studentData[16], 0, false, 'C');
    //                         $pdfBig->SetFont($MontserratRegular, '', 7.78, '', false);
    //                         $pdfBig->SetXY(85.3, 267);
    //                         $pdfBig->Cell(40, 0, $studentData[17], 0, false, 'C');
    //                 }




    //                 //end Bigpdf

    //                 //start pdf
    //                 $X= 18;
    //                 $Y= 70;
    //                 $pdf->SetFont($EBGaramondRegular, '', 18, '', false); 
    //                 $pdf->setCellPaddings( $left = '', $top = '', $right = '', $bottom = '');
    //                 $pdf->SetXY($X, $Y);  
    //                 $pdf->MultiCell(174, 10, $studentData[2], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdf->Ln();
    //                 $pdf->SetFont($EBGaramondRegular, '', 28, '', false); 
    //                 $pdf->SetXY($X, $pdf->GetY());  
    //                 $pdf->MultiCell(174, 10, $studentData[3], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdf->Ln();
    //                 $pdf->SetFont($EBGaramondRegular, '', 18, '', false); 
    //                 $pdf->SetXY($X, $pdf->GetY());  
    //                 $pdf->MultiCell(174, 10, $studentData[4], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdf->Ln();
    //                 $pdf->SetFont($EBGaramondExtraBold, '', 26, '', false); 
    //                 $pdf->SetXY($X, $pdf->GetY());  
    //                 $pdf->MultiCell(174, 10, $studentData[5], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdf->Ln();
    //                 $pdf->SetFont($EBGaramondRegular, '', 18, '', false); 
    //                 $pdf->SetXY($X, $pdf->GetY());  
    //                 $pdf->MultiCell(174, 10, $studentData[6], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdf->Ln();
    //                 $pdf->SetXY($X, $pdf->GetY());  
    //                 $pdf->MultiCell(174, 10, $studentData[7].' '. $studentData[8], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdf->Ln();
    //                 $pdf->SetXY($X, $pdf->GetY());  
    //                 $pdf->MultiCell(174, 10, $studentData[9], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdf->Ln();
    //                 $pdf->SetXY($X, $pdf->GetY());  
    //                 $pdf->MultiCell(174, 10, $studentData[10], 0, 'C', 0, 0, '', '', true, 0, true);
    //                 $pdf->Ln();
    //                 $pdf->SetXY($X, $pdf->GetY());  
    //                 $pdf->MultiCell(174, 10, $studentData[11], 0, 'C', 0, 0, '', '', true, 0, true);

    //                 // $pdf->SetFont($georgia, '', 17, '', false); 
    //                 //  $pdf->SetXY(30, 215);  
    //                 // $pdf->MultiCell(174, 10, $studentData[1], 0, 'L', 0, 0, '', '', true, 0, true);


    //             //    $number = (string)$studentData[1]; // ensure it's a string
    //             //     $x = 30;   // starting X position
    //             //     $y = 215;  // Y position (same line)
    //             //     $baseSize = 10;        // base font size
    //             //     $spacingFactor = 0.20; // adjust spacing between digits

    //             //     foreach (str_split($number) as $digit) {
    //             //         // Convert digit to integer
    //             //         $d = (int)$digit;

    //             //         // Calculate font size (e.g. base + digit value)
    //             //         $fontSize = $baseSize + $d;

    //             //         // Set font for this digit
    //             //         $pdf->SetFont($georgia, '', $fontSize, '', false);

    //             //         // Print this digit
    //             //         $pdf->SetXY($x, $y);
    //             //         $pdf->Cell(0, 0, $digit, 0, 0, 'L', 0, '', 0, false, 'T', 'M');
    //             //         $x += $fontSize * $spacingFactor;
    //             //     }


    //             // $number = (string)$studentData[1]; // e.g. 356744357
    //             // $x = 30;   // starting X
    //             // $y = 215;  // baseline Y
    //             // $baseSize = 10;
    //             // $spacingFactor = 0.20;

    //             // // baseline adjustment reference
    //             // $baselineFontSize = 10; // smallest font size baseline reference

    //             // foreach (str_split($number) as $digit) {
    //             //     $d = (int)$digit;
    //             //     $fontSize = $baseSize + $d;

    //             //     // dd($d );
    //             //     // Set font size
    //             //     $pdf->SetFont($georgia, '', $fontSize, '', false);

    //             //     // Adjust Y so the text baseline aligns (larger font → move Y up slightly)
    //             //     $adjustedY = $y - ($fontSize - $baselineFontSize) * 0.35;

    //             //     // Print digit
    //             //     $pdf->SetXY($x, $adjustedY);
    //             //     $pdf->Cell(0, 0, $digit, 0, 0, 'L', 0, '', 0, false, 'T', 'M');

    //             //     // Move next X
    //             //     $x += $fontSize * $spacingFactor;
    //             // }


    //             $number = (string)$studentData[1]; // e.g. 92345678537
    //             $x = 30;   // starting X position
    //             $y = 215;  // base Y position

    //             $startFontSize = 11;   // starting font size
    //             $spacingFactor = 0.20; // horizontal spacing factor
    //             $fontSize = $startFontSize;

    //             foreach (str_split($number) as $digit) {
    //                 $pdf->SetFont($georgia, '', $fontSize, '', false);
    //                 $adjustedY = $y; 
    //                 $pdf->SetXY($x, $adjustedY);
    //                 $pdf->Cell(0, 0, $digit, 0, 0, 'L', 0, '', 0, false, 'L', 'M');
    //                 $x += $fontSize * $spacingFactor;
    //                 $fontSize++;
    //             }

    //             $pdf->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');

    //                 if(!empty($studentData[12])){
    //                     $pdf->SetFont($arial, '',10, '', false);
    //                     $pdf->SetXY(25, 256);

    //                     $pdf->Cell(40, 0, '____________________', 0, false, 'C');
    //                     $pdf->SetFont($MontserratRegular, '', 9.51, '', false);
    //                     $pdf->SetTextColor(0, 0, 255);
    //                     $pdf->SetXY(25, 261);
    //                     $pdf->Cell(40, 0, $studentData[12], 0, false, 'C');
    //                     $pdf->SetFont($MontserratRegular, '', 7.78, '', false);
    //                     $pdf->SetXY(25, 267);
    //                     $pdf->Cell(40, 0, $studentData[13], 0, false, 'C');
    //                 }

    //                 if(!empty($studentData[14])){
    //                     $pdf->SetTextColor(0, 0, 0);
    //                         $pdf->SetFont($arial, '',10, '', false);
    //                         $pdf->SetXY(146, 256);
    //                         $pdf->Cell(40, 0, '____________________', 0, false, 'C');
    //                         $pdf->SetFont($MontserratRegular, '', 9.51, '', false);
    //                         $pdf->SetTextColor(0, 0, 255);
    //                         $pdf->SetXY(146, 261);
    //                         $pdf->Cell(40, 0, $studentData[14], 0, false, 'C');
    //                         $pdf->SetFont($MontserratRegular, '', 7.78, '', false);
    //                         $pdf->SetXY(146, 267);
    //                         $pdf->Cell(40, 0, $studentData[15], 0, false, 'C');
    //                 }


    //                     if(!empty($studentData[16])){
    //                         $pdf->SetTextColor(0, 0, 0);
    //                         $pdf->SetFont($arial, '',10, '', false);
    //                         $pdf->SetXY(85.3, 256);
    //                         $pdf->Cell(40, 0, '____________________', 0, false, 'C');
    //                         $pdf->SetFont($MontserratRegular, '', 9.51, '', false);
    //                         $pdf->SetTextColor(0, 0, 255);
    //                         $pdf->SetXY(85.3, 261);
    //                         $pdf->Cell(40, 0, $studentData[16], 0, false, 'C');
    //                         $pdf->SetFont($MontserratRegular, '', 7.78, '', false);
    //                         $pdf->SetXY(85.3, 267);
    //                         $pdf->Cell(40, 0, $studentData[17], 0, false, 'C');
    //                 }




    //                 /*end pdf*/ 


    //                 if(!empty($studentData[12])){
    //                 $name1=$studentData[12];
    //             if(strpos($name1,'Jasmine Gohil') !== FALSE) {
    //                 //if($name1=="Dr. Sridhar B Reddy"){
    //                 $sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\JasmineGohil_new.png';
    //                 } elseif(strpos($name1,'Jigisha Patel') !== FALSE) {
    //                 $sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Jigisha_Patel_Sign_new.png';
    //                 } elseif(strpos($name1,'Suhas Toshniwal') !== FALSE) {
    //                 $sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
    //                 } elseif(strpos($name1,'Hrishikesh Trivedi') !== FALSE) {
    //                 $sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
    //                 } elseif(strpos($name1,'Sanjay Bhatnagar') !== FALSE) {
    //                 $sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sanjay_sign.png';

    //                 } 
    //                 elseif(strpos($name1,'Dr Sanjeev Vidyarthi') !== FALSE) {
    //                 $sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sanjay_sign.png';

    //                 }else {
    //                 $sign1 = "";
    //                 } 


    //                 $sign1_x = 30;
    //                 $sign1_y = 249;
    //                 $sign1_Width = 31.75;
    //                 $sign1_Height = 9.79;
    //                     //kamini changes 20-05-2025
    //                 $upload_sign2_org =  $sign1;
    //                 $pathInfo = pathinfo( $sign1);
    //                 $sign1 = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$serial_no.".$pathInfo['extension'];
    //                     \File::copy($upload_sign2_org, $sign1); 

    //                 $pdf->image($sign1,$sign1_x,$sign1_y,$sign1_Width,$sign1_Height,"",'','L',true,3600);
    //                 $pdf->setPageMark();
    //                 $pdfBig->image($sign1,$sign1_x,$sign1_y,$sign1_Width,$sign1_Height,"",'','L',true,3600);
    //                 $pdfBig->setPageMark();
    //             }


    //             if(!empty($studentData[14])){
    //                 $name2=$studentData[14];
    //                 $sign2_x = 150;
    //                 $sign2_y = 249;
    //                 $sign2_Width = 31.75;
    //                 $sign2_Height = 9.79;
    //             if(strpos($name2,'Jasmine Gohil') !== FALSE) {
    //                 //if($name2=="Dr. Sridhar B Reddy"){
    //                 $sign2 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\JasmineGohil_new.png';
    //                 } elseif(strpos($name2,'Jigisha Patel') !== FALSE) {
    //                 $sign2 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Jigisha_Patel_Sign_new.png';
    //                 } elseif(strpos($name2,'Suhas Toshniwal') !== FALSE) {
    //                 $sign2 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
    //                 } elseif(strpos($name2,'Hrishikesh Trivedi') !== FALSE) {
    //                 $sign2 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
    //                 } elseif(strpos($name2,'Sanjay Bhatnagar') !== FALSE) {
    //                 $sign2 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sanjay_sign.png';

    //                 } 
    //                 elseif(strpos($name2,'Dr Sanjeev Vidyarthi') !== FALSE) {
    //                 $sign2 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sanjay_sign.png';

    //                 }else {
    //                 $sign2 = "";
    //                 }                
    //                 $upload_sign2_org =  $sign2;
    //                 $pathInfo = pathinfo( $sign2);
    //                 $sign2 = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$serial_no.".$pathInfo['extension'];
    //                     \File::copy($upload_sign2_org, $sign2); 

    //                 $pdf->image($sign2,$sign2_x,$sign2_y,$sign2_Width,$sign2_Height,"",'','L',true,3600);
    //                 $pdf->setPageMark();

    //                 $pdfBig->image($sign2,$sign2_x,$sign2_y,$sign2_Width,$sign2_Height,"",'','L',true,3600);
    //                 $pdfBig->setPageMark();
    //             }




    //             if(!empty($studentData[16])){
    //                 $name3=$studentData[16];
    //                 $sign3_x =90;
    //                 $sign3_y = 249;
    //                 $sign3_Width = 31.75;
    //                 $sign3_Height = 9.79;
    //                 if(strpos($name3,'Jasmine Gohil') !== FALSE) {
    //                 //if($name3=="Dr. Sridhar B Reddy"){
    //                 $sign3 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\JasmineGohil_new.png';
    //                 } elseif(strpos($name3,'Jigisha Patel') !== FALSE) {
    //                 $sign3 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Jigisha_Patel_Sign_new.png';
    //                 } elseif(strpos($name3,'Suhas Toshniwal') !== FALSE) {
    //                 $sign3 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
    //                 } elseif(strpos($name3,'Hrishikesh Trivedi') !== FALSE) {
    //                 $sign3 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
    //                 } elseif(strpos($name3,'Sanjay Bhatnagar') !== FALSE) {
    //                 $sign3 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sanjay_sign.png';

    //                 } 
    //                 elseif(strpos($name3,'Dr Sanjeev Vidyarthi') !== FALSE) {
    //                 $sign3 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sanjay_sign.png';

    //                 }else {
    //                 $sign3 = "";
    //                 }               
    //                 $upload_sign2_org =  $sign3;
    //                 $pathInfo = pathinfo( $sign3);
    //                 $sign3 = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$serial_no.".$pathInfo['extension'];
    //                     \File::copy($upload_sign2_org, $sign3); 

    //                 $pdf->image($sign3,$sign3_x,$sign3_y,$sign3_Width,$sign3_Height,"",'','L',true,3600);
    //                 $pdf->setPageMark();

    //                 $pdfBig->image($sign3,$sign3_x,$sign3_y,$sign3_Width,$sign3_Height,"",'','L',true,3600);
    //                 $pdfBig->setPageMark();
    //             }


    //                 //block chain data 
    //             $cellValue=trim($studentData[18]);
    //                 $parts = preg_split('/,\s+(?=[A-Z][A-Za-z0-9_ ]*:\s*)/', $cellValue);

    //                 $data = [];
    //                 foreach ($parts as $part) {
    //                     $pair = explode(':', $part, 2);
    //                     if (count($pair) === 2) {
    //                         $key = trim($pair[0]);
    //                         $value = trim($pair[1]);
    //                         $data[$key] = $value;
    //                     }
    //                 }

    //                 // dd($data);


    //             //qr code  
    //             // $new_PercNew = trim($last[7]);  
    //             $dt = date("_ymdHis");
    //             $str=$GUID.$dt;
    //             //// $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
    //             //// $codeContents = "[".$student_id." - ". $candidate_name ."]"."\n\n" ."\n\n".strtoupper(md5($str));


    //             // $codeContents = "[".$studentData[1]." - ". $studentData[3] ."]";
    //             // $codeContents .="\n";
    //             $codeContents .= "[". $studentData[18] ."]";

    //             $codeContents .="\n"; 
    //             $codeContents .="\n";




    //             $codeData =CoreHelper::getBCVerificationUrl(base64_encode(strtoupper(md5($str))));

    //             $codeContents .=$codeData;


    //             $codeContents .="\n\n".strtoupper(md5($str));





    //             // $codeData=CoreHelper::getBCVerificationUrl(base64_encode($codeContents));

    //             $encryptedString = strtoupper(md5($str));
    //             $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
    //             $qrCodex = 150; 
    //             $qrCodey = 203;
    //             $qrCodeWidth =29;
    //             $qrCodeHeight = 27;
    //             $ecc = 'L';
    //             $pixel_Size = 1;
    //             $frame_Size = 1;  
    //             //\PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);



    //             \QrCode::backgroundColor(255, 255, 0)            
    //                 ->format('png')        
    //                 ->size(500)    
    //                 ->generate($codeContents, $qr_code_path);

    //                 // Step 2: Convert transparent PNG to white background
    //             // $im = imagecreatefrompng($qr_code_path);
    //             // $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
    //             // $white = imagecolorallocate($bg, 255, 255, 255);
    //             // imagefill($bg, 0, 0, $white);
    //             // imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
    //             // imagepng($bg, $qr_code_path);
    //             // imagedestroy($im);
    //             // imagedestroy($bg);

    //             $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
    //             $pdf->setPageMark(); 
    //             $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
    //             $pdfBig->setPageMark(); 



    //             // Bottom Blockchain URL Link in image By rohit 21-07-2025
    //             $verifyLink = $codeData;
    //             $logo_path_org = public_path().'\\'.$subdomain[0].'\backend\canvas\images\logo.png'; 
    //             // set profile image   
    //             $logox = 75;
    //             $logoy = 281;
    //             $logoWidth = 8;
    //             $logoHeight = 8;

    //             // pass $verifyLink in the link parameter
    //             $pdf->image($logo_path_org, $logox, $logoy, $logoWidth, $logoHeight, '', $verifyLink, '', true, 3600);  
    //             $pdf->setPageMark();

    //             $pdf->SetFont($arial, '', 9, '', false);
    //             $pdf->SetXY(84, 282);
    //             $pdf->MultiCell(0, 0, 'Click here to verify on Blockchain', 0, 'L', 0, 0, '', '', true, 0, true);
    //             // Bottom Blockchain URL Link in image By rohit 21-07-2025






    //             // Ghost image
    //             $nameOrg=$candidate_name;






    //             $student_name = $candidate_name;
    //             if($previewPdf!=1){

    //                 $certName = str_replace("/", "_", $GUID) .".pdf";
    //                 // $certName = str_replace(" ", "_", $certName) .".pdf";

    //                 array_push($sftpData,$certName);

    //                 $myPath = public_path().'/backend/temp_pdf_file';

    //                 $fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;

    //                 $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');

    //                 $dateOfIssue = date('d/m/Y');
    //                 $mintData=array();
    //                 $mintData['documentType']="Certificate";
    //                 $mintData['description']="Anant National University";
    //                 $mintData['metadata1']=["label"=> "Certificate Sr. No.", "value"=> $studentData[0]];
    //                 $mintData['metadata2']=["label"=> "Student ID", "value"=> $studentData[1]];
    //                 $mintData['metadata3']=["label"=> "Student Name", "value"=> $studentData[3]];
    //                 $mintData['metadata4']=["label"=> "Programme", "value"=> $studentData[7]];
    //                 $mintData['metadata5']=["label"=> "Title of Award", "value"=> $studentData[5]];

    //                 // $index = 1; // next metadata number
    //                 // foreach ($data as $key => $value) {
    //                 //     $mintData["metadata{$index}"] = [
    //                 //         "label" => $key,
    //                 //         "value" => $value
    //                 //     ];
    //                 //     $index++;
    //                 // }

    //                 $mintData['uniqueHash']=$encryptedString;

    //                 $this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id,$student_name,$mintData);

    //                 $username = $admin_id['username'];
    //                 date_default_timezone_set('Asia/Kolkata');

    //                 $content = "#".$log_serial_no." serial No :".$serial_no.PHP_EOL;
    //                 $date = date('Y-m-d H:i:s').PHP_EOL;
    //                 $print_datetime = date("Y-m-d H:i:s");


    //                 $print_count = $this->getPrintCount($serial_no);
    //                 $printer_name = /*'HP 1020';*/$printer_name;

    //                 $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'Memorandum',$admin_id,$card_serial_no);

    //                 $card_serial_no=$card_serial_no+1;
    //             }else{
    //                 $preview_serial_no=$preview_serial_no+1;
    //             }

    //             $generated_documents++;

    //             if(isset($pdf_data['generation_from'])&&$pdf_data['generation_from']=='API'){
    //                 $updated=date('Y-m-d H:i:s');
    //                 ThirdPartyRequests::where('id',$pdf_data['request_id'])->update(['generated_documents'=>$generated_documents,"updated_at"=>$updated]);
    //             }else{
    //             //For Custom loader calculation
    //                 //echo $generated_documents;
    //             $endTimeLoader = date('Y-m-d H:i:s');
    //             $time1 = new \DateTime($startTimeLoader);
    //             $time2 = new \DateTime($endTimeLoader);
    //             $interval = $time1->diff($time2);
    //             $interval = $interval->format('%s');

    //             $jsonArr=array();
    //             $jsonArr['token'] = $pdf_data['loader_token'];
    //             $jsonArr['generatedCertificates'] =$generated_documents;
    //             $jsonArr['timePerCertificate'] =$interval;

    //             $loaderData=CoreHelper::createLoaderJson($jsonArr,0);
    //             }
    //             //delete temp dir 26-04-2022 
    //             CoreHelper::rrmdir($tmpDir);
    //             $pdf_data_obj = $pdfBig; // Get the PDF data as a string

    //                 // Store the PDF data in the session
    //                 Session::put('pdf_data_obj', $pdf_data_obj);

    //                 // Update code for batchwise genration
    //                 return "Will be generated soon!";	
    //         } 
    //     }


    //     if($previewPdf!=1){
    //         $this->updateCardNo('anuAward',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
    //     }
    //     $msg = '';

    //     $file_name =  str_replace("/", "_",'anuAward'.date("Ymdhms")).'.pdf';

    //     $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();


    //     $filename = public_path().'/backend/tcpdf/examples/'.$file_name;

    //     $pdfBig->output($filename,'F');

    //     if($previewPdf!=1){

    //         // Upload on SFTP Server
    //         CoreHelper::SFTPUploadAnu($sftpData);

    //         $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
    //         @unlink($filename);
    //         $no_of_records = count($studentDataOrg);
    //         $user = $admin_id['username'];
    //         $template_name="anuAward";
    //         if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
    //             // with sandbox

    //             $result = SbExceUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
    //         }else{
    //             // without sandbox
    //             $result = ExcelUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
    //         }
    // 		$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
    // 		$path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
    // 		$pdf_url=$path.$subdomain[0]."/backend/tcpdf/examples/".$file_name;
    // 		$msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/".$file_name."'class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
    // 	}else{
    //         $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/preview/'.$file_name);
    //         @unlink($filename);
    //         $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
    //         $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
    //         $pdf_url=$path.$subdomain[0]."/backend/tcpdf/examples/preview/".$file_name;
    //         $msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/preview/".$file_name."' class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
    //     }
    //     //API changes
    //     if(isset($pdf_data['generation_from'])&&$pdf_data['generation_from']=='API'){
    //         $updated=date('Y-m-d H:i:s');

    //         ThirdPartyRequests::where('id',$pdf_data['request_id'])->update(['status'=>'Completed','printable_pdf_link'=>$pdf_url,"updated_at"=>$updated]);
    //         //Sending data to call back url
    //         $reaquestParameters = array
    //         (
    //             'request_id'=>$pdf_data['request_id'],
    //             'printable_pdf_link' => $pdf_url,
    //         );
    //         $url = $pdf_data['call_back_url'];
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $url);
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reaquestParameters));
    //         $result = curl_exec($ch);

    //         $updated=date('Y-m-d H:i:s');
    //         ThirdPartyRequests::where('id',$pdf_data['request_id'])->update(['call_back_response'=>json_encode($result),"updated_at"=>$updated]);

    //         curl_close($ch);
    //     }

    //     return $msg;



    // }


    public function handle(CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {
        $pdf_data = $this->pdf_data;
        // dd($pdf_data);    
        $studentDataOrg = $pdf_data['studentDataOrg'];
        $subjectDataOrg = $pdf_data['subjectDataOrg'];

        $template_id = $pdf_data['template_id'];
        $dropdown_template_id = $pdf_data['dropdown_template_id'];
        $previewPdf = $pdf_data['previewPdf'];
        $excelfile = $pdf_data['excelfile'];
        $auth_site_id = $pdf_data['auth_site_id'];
        $previewWithoutBg = $previewPdf[1];
        $previewPdf = $previewPdf[0];

        $first_sheet = $pdf_data['studentDataOrg']; // get first worksheet rows

        $total_unique_records = count($pdf_data['studentDataOrg']);
        $last_row = $total_unique_records + 1;

        if (isset($pdf_data['generation_from']) && $pdf_data['generation_from'] == 'API') {
            $admin_id = $pdf_data['admin_id'];
        } else {
            $admin_id = \Auth::guard('admin')->user()->toArray();
        }
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $systemConfig = SystemConfig::select('sandboxing', 'printer_name')->where('site_id', $auth_site_id)->first();
        $printer_name = $systemConfig['printer_name'];


        $loader_data = CoreHelper::getLoaderJson($pdf_data['loader_token']);


        // Log an error
        //\Log::info('loader error', ['loader_data' => $loader_data]);

        if (!empty($loader_data) && isset($loader_data['generatedCertificates'])) {

            $generated_documents = $loader_data['generatedCertificates'];
        } else {
            $generated_documents = 0;
        }


        //\Log::info('generated_documents error', ['generated_documents' => $generated_documents]);

        if ($generated_documents == 0) {
            Session::forget('pdf_data_obj');
            $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
            $pdfBig->SetCreator(PDF_CREATOR);
            $pdfBig->SetAuthor('TCPDF');
            $pdfBig->SetTitle('Certificate');
            $pdfBig->SetSubject('');
            // Session::put('pdf_obj', $pdfBig);


        } else {
            if (Session::get('pdf_data_obj') != null) {
                $pdfBig = Session::get('pdf_data_obj');
            }
        }

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
        $pdfBig->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $EBGaramondExtraBold = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\EBGaramond-ExtraBold_0.TTF', 'TrueTypeUnicode', '', 96);
        $EBGaramondRegular = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\EBGaramond-Regular_0.TTF', 'TrueTypeUnicode', '', 96);
        $georgia = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\georgia.TTF', 'TrueTypeUnicode', '', 96);
        $MontserratBold = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Montserrat-Bold.TTF', 'TrueTypeUnicode', '', 96);
        $MontserratRegular = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Montserrat-Regular.TTF', 'TrueTypeUnicode', '', 96);
        $MontserratSemiBold = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Montserrat-SemiBold.TTF', 'TrueTypeUnicode', '', 96);

        $preview_serial_no = 1;
        //$card_serial_no="";
        $log_serial_no = 1;
        // $cardDetails=$this->getNextCardNo('EastPoint');
        // $card_serial_no=$cardDetails->next_serial_no;
        //$generated_documents=0;  //for custom loader


        // $subjectsArr = array();
        // if($subjectDataOrg) {
        //     foreach ($subjectDataOrg as $element) {
        //         $subjectsArr[$element[0]][] = $element;
        //     }
        // }
        $sftpData = [];
        if ($studentDataOrg && !empty($studentDataOrg)) {
            foreach ($studentDataOrg as $studentData) {

                // if($card_serial_no>999999&&$previewPdf!=1){
                // 	echo "<h5>Your card series ended...!</h5>";
                // 	exit;
                // }
                //For Custom Loader
                $startTimeLoader =  date('Y-m-d H:i:s');
                $high_res_bg = "Award_Certificate_Nlank.jpg"; // bestiu_pdc_bg, TranscriptData.jpg
                $low_res_bg = "Award_Certificate_Nlank.jpg";
                $pdfBig->AddPage();
                $pdfBig->SetFont($arialb, '', 8, '', false);
                //set background image
                $template_img_generate = public_path() . '\\' . $subdomain[0] . '\backend\canvas\bg_images\\' . $high_res_bg;

                if ($previewPdf == 1) {
                    if ($previewWithoutBg != 1) {
                        $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
                    }
                    $date_font_size = '11';
                    $date_nox = 13;
                    $date_noy = 40;
                    $date_nostr = 'DRAFT ' . date('d-m-Y H:i:s');
                    $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
                    $pdfBig->SetTextColor(192, 192, 192);
                    $pdfBig->SetXY($date_nox, $date_noy);
                    // $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
                    $pdfBig->SetTextColor(0, 0, 0, 100, false, '');
                    $pdfBig->SetFont($arialb, '', 9, '', false);
                } else {
                    $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
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

                $pdf->AddPage();
                $print_serial_no = $this->nextPrintSerial();
                //set background image
                $template_img_generate = public_path() . '\\' . $subdomain[0] . '\backend\canvas\bg_images\\' . $low_res_bg;
                if ($previewPdf != 1) {
                    $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
                }
                //$pdf->setPageMark();
                $pdf->setPageMark();
                //$pdfBig->setPageMark();
                //Table's Titles  
                $unique_id = trim($studentData[0]);




                $serial_no = $GUID = $studentData[0];
                // dd($GUID,'yes');
                //start Bigpdf
                $X = 18;
                $Y = 70;
                $pdfBig->SetTextColor(0, 0, 0);
                $pdfBig->SetFont($EBGaramondRegular, '', 18, '', false);
                $pdfBig->setCellPaddings($left = '', $top = '', $right = '', $bottom = '');
                $pdfBig->SetXY($X, $Y);
                $pdfBig->MultiCell(174, 10, $studentData[2], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->Ln();
                $pdfBig->SetFont($EBGaramondRegular, '', 28, '', false);
                $pdfBig->SetXY($X, $pdfBig->GetY());
                $pdfBig->MultiCell(174, 10, $studentData[3], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->Ln();
                $pdfBig->SetFont($EBGaramondRegular, '', 18, '', false);
                $pdfBig->SetXY($X, $pdfBig->GetY());
                $pdfBig->MultiCell(174, 10, $studentData[4], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->Ln();
                // $pdfBig->SetFont($EBGaramondExtraBold, '', 26, '', false); 
                // $pdfBig->SetXY($X, $pdfBig->GetY());  
                // $pdfBig->MultiCell(174, 10, $studentData[5], 0, 'C', 0, 0, '', '', true, 0, true);
                // $pdfBig->Ln();

                $text = str_replace(["\r\n", "\r", "\n"], "\n", $studentData[5]);

                $pdfBig->SetFont($EBGaramondExtraBold, '', 26, '', false);
                $pdfBig->SetXY($X, $pdfBig->GetY());

                $pdfBig->MultiCell(174, 10, $text, 0, 'C', 0, 1, '', '', true);
                $pdfBig->SetFont($EBGaramondRegular, '', 18, '', false);
                $pdfBig->SetXY($X, $pdfBig->GetY());
                $pdfBig->MultiCell(174, 10, $studentData[6], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->Ln();
                $pdfBig->SetXY($X, $pdfBig->GetY());
                $pdfBig->MultiCell(174, 10, $studentData[7] . ' ' . $studentData[8], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->Ln();
                $pdfBig->SetXY($X, $pdfBig->GetY());
                $pdfBig->MultiCell(174, 10, $studentData[9], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->Ln();
                $pdfBig->SetXY($X, $pdfBig->GetY());
                $pdfBig->MultiCell(174, 10, $studentData[10], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->Ln();
                $pdfBig->SetXY($X, $pdfBig->GetY());
                $pdfBig->MultiCell(174, 10, $studentData[11], 0, 'C', 0, 0, '', '', true, 0, true);



                $number = (string)$studentData[0]; // e.g. 92345678537
                $x = 30;   // starting X position
                $y = 215;  // base Y position

                $startFontSize = 11;   // starting font size
                $spacingFactor = 0.26; // horizontal spacing factor
                $fontSize = $startFontSize;

                foreach (str_split($number) as $digit) {
                    $pdfBig->SetFont($georgia, '', $fontSize, '', false);
                    $adjustedY = $y;
                    $pdfBig->SetXY($x, $adjustedY);
                    $pdfBig->Cell(0, 0, $digit, 0, 0, 'L', 0, '', 0, false, 'L', 'M');
                    $x += $fontSize * $spacingFactor;
                    $fontSize++;
                }

                $pdfBig->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');

                if (!empty($studentData[12])) {
                    $pdfBig->SetFont($arial, '', 10, '', false);
                    $pdfBig->SetXY(25, 256);

                    $pdfBig->Cell(40, 0, '____________________', 0, false, 'C');
                    $pdfBig->SetFont($MontserratRegular, '', 9.51, '', false);
                    $pdfBig->SetTextColor(0, 0, 255);
                    $pdfBig->SetXY(25, 261);
                    $pdfBig->Cell(40, 0, $studentData[12], 0, false, 'C');
                    $pdfBig->SetFont($MontserratRegular, '', 7.78, '', false);
                    $pdfBig->SetXY(25, 267);
                    $pdfBig->Cell(40, 0, $studentData[13], 0, false, 'C');
                }

                if (!empty($studentData[14])) {
                    $pdfBig->SetTextColor(0, 0, 0);
                    $pdfBig->SetFont($arial, '', 10, '', false);
                    $pdfBig->SetXY(146, 256);
                    $pdfBig->Cell(40, 0, '____________________', 0, false, 'C');
                    $pdfBig->SetFont($MontserratRegular, '', 9.51, '', false);
                    $pdfBig->SetTextColor(0, 0, 255);
                    $pdfBig->SetXY(146, 261);
                    $pdfBig->Cell(40, 0, $studentData[14], 0, false, 'C');
                    $pdfBig->SetFont($MontserratRegular, '', 7.78, '', false);
                    $pdfBig->SetXY(146, 267);
                    $pdfBig->Cell(40, 0, $studentData[15], 0, false, 'C');
                }


                if (!empty($studentData[16])) {
                    $pdfBig->SetTextColor(0, 0, 0);
                    $pdfBig->SetFont($arial, '', 10, '', false);
                    $pdfBig->SetXY(85.3, 256);
                    $pdfBig->Cell(40, 0, '____________________', 0, false, 'C');
                    $pdfBig->SetFont($MontserratRegular, '', 9.51, '', false);
                    $pdfBig->SetTextColor(0, 0, 255);
                    $pdfBig->SetXY(85.3, 261);
                    $pdfBig->Cell(40, 0, $studentData[16], 0, false, 'C');
                    $pdfBig->SetFont($MontserratRegular, '', 7.78, '', false);
                    $pdfBig->SetXY(85.3, 267);
                    $pdfBig->Cell(40, 0, $studentData[17], 0, false, 'C');
                }
                //ENd Big Pdf

                //Start pdf
                $X = 18;
                $Y = 70;
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont($EBGaramondRegular, '', 18, '', false);
                $pdf->setCellPaddings($left = '', $top = '', $right = '', $bottom = '');
                $pdf->SetXY($X, $Y);
                $pdf->MultiCell(174, 10, $studentData[2], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdf->Ln();
                $pdf->SetFont($EBGaramondRegular, '', 28, '', false);
                $pdf->SetXY($X, $pdf->GetY());
                $pdf->MultiCell(174, 10, $studentData[3], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdf->Ln();
                $pdf->SetFont($EBGaramondRegular, '', 18, '', false);
                $pdf->SetXY($X, $pdf->GetY());
                $pdf->MultiCell(174, 10, $studentData[4], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdf->Ln();
                // $pdf->SetFont($EBGaramondExtraBold, '', 26, '', false); 
                // $pdf->SetXY($X, $pdf->GetY());  
                // $pdf->MultiCell(174, 10, $studentData[5], 0, 'C', 0, 0, '', '', true, 0, true);

                $text = str_replace(["\r\n", "\r", "\n"], "\n", $studentData[5]);

                $pdf->SetFont($EBGaramondExtraBold, '', 26, '', false);
                $pdf->SetXY($X, $pdf->GetY());

                $pdf->MultiCell(174, 10, $text, 0, 'C', 0, 1, '', '', true);
                $pdf->Ln();
                $pdf->SetFont($EBGaramondRegular, '', 18, '', false);
                $pdf->SetXY($X, $pdf->GetY());
                $pdf->MultiCell(174, 10, $studentData[6], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdf->Ln();
                $pdf->SetXY($X, $pdf->GetY());
                $pdf->MultiCell(174, 10, $studentData[7] . ' ' . $studentData[8], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdf->Ln();
                $pdf->SetXY($X, $pdf->GetY());
                $pdf->MultiCell(174, 10, $studentData[9], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdf->Ln();
                $pdf->SetXY($X, $pdf->GetY());
                $pdf->MultiCell(174, 10, $studentData[10], 0, 'C', 0, 0, '', '', true, 0, true);
                $pdf->Ln();
                $pdf->SetXY($X, $pdf->GetY());
                $pdf->MultiCell(174, 10, $studentData[11], 0, 'C', 0, 0, '', '', true, 0, true);



                $number = (string)$studentData[0]; // e.g. 92345678537
                $x = 30;   // starting X position
                $y = 215;  // base Y position

                $startFontSize = 11;   // starting font size
                // $spacingFactor = 0.28; // horizontal spacing factor
                $spacingFactor = 0.26;
                $fontSize = $startFontSize;

                foreach (str_split($number) as $digit) {
                    $pdf->SetFont($georgia, '', $fontSize, '', false);
                    $adjustedY = $y;
                    $pdf->SetXY($x, $adjustedY);
                    $pdf->Cell(0, 0, $digit, 0, 0, 'L', 0, '', 0, false, 'L', 'M');
                    $x += $fontSize * $spacingFactor;
                    $fontSize++;
                }

                $pdf->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');

                if (!empty($studentData[12])) {
                    $pdf->SetFont($arial, '', 10, '', false);
                    $pdf->SetXY(25, 256);

                    $pdf->Cell(40, 0, '____________________', 0, false, 'C');
                    $pdf->SetFont($MontserratRegular, '', 9.51, '', false);
                    $pdf->SetTextColor(0, 0, 255);
                    $pdf->SetXY(25, 261);
                    $pdf->Cell(40, 0, $studentData[12], 0, false, 'C');
                    $pdf->SetFont($MontserratRegular, '', 7.78, '', false);
                    $pdf->SetXY(25, 267);
                    $pdf->Cell(40, 0, $studentData[13], 0, false, 'C');
                }

                if (!empty($studentData[14])) {
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont($arial, '', 10, '', false);
                    $pdf->SetXY(146, 256);
                    $pdf->Cell(40, 0, '____________________', 0, false, 'C');
                    $pdf->SetFont($MontserratRegular, '', 9.51, '', false);
                    $pdf->SetTextColor(0, 0, 255);
                    $pdf->SetXY(146, 261);
                    $pdf->Cell(40, 0, $studentData[14], 0, false, 'C');
                    $pdf->SetFont($MontserratRegular, '', 7.78, '', false);
                    $pdf->SetXY(146, 267);
                    $pdf->Cell(40, 0, $studentData[15], 0, false, 'C');
                }


                if (!empty($studentData[16])) {
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont($arial, '', 10, '', false);
                    $pdf->SetXY(85.3, 256);
                    $pdf->Cell(40, 0, '____________________', 0, false, 'C');
                    $pdf->SetFont($MontserratRegular, '', 9.51, '', false);
                    $pdf->SetTextColor(0, 0, 255);
                    $pdf->SetXY(85.3, 261);
                    $pdf->Cell(40, 0, $studentData[16], 0, false, 'C');
                    $pdf->SetFont($MontserratRegular, '', 7.78, '', false);
                    $pdf->SetXY(85.3, 267);
                    $pdf->Cell(40, 0, $studentData[17], 0, false, 'C');
                }




                //End pdf	
                // Ghost image
                $nameOrg = $STUDENT_NAME;


                //qr code  
                $dt = date("_ymdHis");
                $str = $GUID . $dt;
                //// $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
                //// $codeContents = "[".$student_id." - ". $candidate_name ."]"."\n\n" ."\n\n".strtoupper(md5($str));

                // $codeContents = "[".$studentData[1]." - ". $studentData[3] ."]";
                // $codeContents .="\n";
                $codeContents =  $studentData[18];

                $codeContents .= "\n";
                $codeContents .= "\n";

                $codeData = CoreHelper::getBCVerificationUrl(base64_encode(strtoupper(md5($str))));
                $codeContents .= $codeData;
                $codeContents .= "\n\n" . strtoupper(md5($str));

                // $codeData=CoreHelper::getBCVerificationUrl(base64_encode($codeContents));

                $encryptedString = strtoupper(md5($str));
                $qr_code_path = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\qr\/' . $encryptedString . '.png';
                $qrCodex = 150;
                $qrCodey = 203;
                $qrCodeWidth = 29;
                $qrCodeHeight = 27;
                $ecc = 'M';
                $pixel_Size = 2;
                $frame_Size = 1;
                // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);



                \QrCode::backgroundColor(255, 255, 0)
                    ->format('png')
                    ->size(500)
                    ->margin(0)
                    ->generate($codeContents, $qr_code_path);




                // Step 2: Convert transparent PNG to white background
                // $im = imagecreatefrompng($qr_code_path);
                // $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
                // $white = imagecolorallocate($bg, 255, 255, 255);
                // imagefill($bg, 0, 0, $white);
                // imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
                // imagepng($bg, $qr_code_path);
                // imagedestroy($im);
                // imagedestroy($bg);

                $inputPath = $qr_code_path;
                $qr_code_path_temp = public_path($subdomain[0] . '/backend/canvas/images/qr/' . $encryptedString . '_temp.png');

                $img = imagecreatefrompng($inputPath);

                // Enable alpha
                imagealphablending($img, false);
                imagesavealpha($img, true);

                $width = imagesx($img);
                $height = imagesy($img);

                // Loop through each pixel
                for ($x = 0; $x < $width; $x++) {
                    for ($y = 0; $y < $height; $y++) {
                        $rgb = imagecolorat($img, $x, $y);
                        $colors = imagecolorsforindex($img, $rgb);

                        // If pixel is white (or near-white), make it transparent
                        if ($colors['red'] > 240 && $colors['green'] > 240 && $colors['blue'] > 240) {
                            $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
                            imagesetpixel($img, $x, $y, $transparent);
                        }
                    }
                }

                // Save PNG with transparency
                imagepng($img, $qr_code_path_temp);
                imagedestroy($img);






                $pdf->Image($qr_code_path_temp, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);
                $pdf->setPageMark();
                $pdfBig->Image($qr_code_path_temp, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);
                $pdfBig->setPageMark();


                // Bottom Blockchain URL Link in image By rohit 21-07-2025
                $verifyLink = $codeData;
                $logo_path_org = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\logo.png';
                // set profile image   
                $logox = 75;
                $logoy = 285;
                $logoWidth = 8;
                $logoHeight = 8;

                // pass $verifyLink in the link parameter
                $pdf->image($logo_path_org, $logox, $logoy, $logoWidth, $logoHeight, '', $verifyLink, '', true, 3600);
                $pdf->setPageMark();

                $pdf->SetFont($arial, '', 9, '', false);
                $pdf->SetXY(84, 286);
                $pdf->MultiCell(0, 0, 'Click here to verify on Blockchain', 0, 'L', 0, 0, '', '', true, 0, true);
                // Bottom Blockchain URL Link in image By rohit 21-07-2025

                if (!empty($studentData[12])) {
                    $name1 = $studentData[12];
                    $sign1_x = 30;
                    $sign1_y = 249;
                    $sign1_Width = 31.75;
                    $sign1_Height = 9.79;
                    if (strpos($name1, 'Jasmine Gohil') !== FALSE) {
                        //if($name1=="Dr. Sridhar B Reddy"){
                        $sign1 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\JasmineGohil_new.png';
                    } elseif (strpos($name1, 'Jigisha Patel') !== FALSE) {
                        $sign1 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Jigisha_Patel_Sign_new.png';
                    } elseif (strpos($name1, 'Suhas Toshniwal') !== FALSE) {
                        $sign1 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
                    } elseif (strpos($name1, 'Hrishikesh Trivedi') !== FALSE) {
                        $sign1 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
                    } elseif (strpos($name1, 'Sanjay Bhatnagar') !== FALSE) {
                        //$sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sanjay_sign.png';
                        $sign1 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\sanjay_new.png';
                        $sign1_Width = 15;
                        $sign1_Height = 17;
                        $sign1_x = 35;
                        $sign1_y = 242;
                    } elseif (strpos($name1, 'Brijesh Kumar Singh') !== FALSE) {
                        $sign1 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Brijesh_Kumar_Singh.png';
                        $sign1_Width = 25;
                        $sign1_y = 237;
                        $sign1_Height = 25;
                        $sign1_x = 33;
                    } elseif (strpos($name1, 'Dr Sanjeev Vidyarthi') !== FALSE) {
                        $sign1 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Sanjeev_new.png';
                        $sign1_Width = 27;
                        $sign1_Height = 13.79;
                        $sign1_y = 246;
                    } else {
                        $sign1 = "";
                    }



                    //kamini changes 20-05-2025
                    $upload_sign2_org =  $sign1;
                    $pathInfo = pathinfo($sign1);
                    $sign1 = public_path() . '\\' . $subdomain[0] . '\backend\\templates\\100\\' . $pathInfo['filename'] . "_$serial_no." . $pathInfo['extension'];
                    \File::copy($upload_sign2_org, $sign1);

                    $pdf->image($sign1, $sign1_x, $sign1_y, $sign1_Width, $sign1_Height, "", '', 'L', true, 3600);
                    $pdf->setPageMark();
                    $pdfBig->image($sign1, $sign1_x, $sign1_y, $sign1_Width, $sign1_Height, "", '', 'L', true, 3600);
                    $pdfBig->setPageMark();
                }


                if (!empty($studentData[14])) {
                    $name2 = $studentData[14];
                    $sign2_x = 150;
                    $sign2_y = 249;
                    $sign2_Width = 31.75;
                    $sign2_Height = 9.79;
                    if (strpos($name2, 'Jasmine Gohil') !== FALSE) {
                        //if($name2=="Dr. Sridhar B Reddy"){
                        $sign2 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\JasmineGohil_new.png';
                    } elseif (strpos($name2, 'Jigisha Patel') !== FALSE) {
                        $sign2 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Jigisha_Patel_Sign_new.png';
                    } elseif (strpos($name2, 'Suhas Toshniwal') !== FALSE) {
                        $sign2 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
                    } elseif (strpos($name2, 'Hrishikesh Trivedi') !== FALSE) {
                        $sign2 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
                    } elseif (strpos($name2, 'Sanjay Bhatnagar') !== FALSE) {
                        $sign2 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\sanjay_new.png';
                        $sign2_Width = 15;
                        $sign2_Height = 17;
                        $sign2_x = 155;
                        $sign2_y = 242;
                    } elseif (strpos($name2, 'Brijesh Kumar Singh') !== FALSE) {
                        $sign2 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Brijesh_Kumar_Singh.png';
                        $sign2_Width = 25;
                        $sign2_Height = 25;
                        $sign2_x = 153;
                        $sign2_y = 237;
                    } elseif (strpos($name2, 'Dr Sanjeev Vidyarthi') !== FALSE) {

                        $sign2 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Sanjeev_new.png';
                        $sign2_Width = 27;
                        $sign2_Height = 13.79;
                        $sign2_y = 246;
                        $sign2_x = 153;
                    } else {
                        $sign2 = "";
                    }
                    $upload_sign2_org =  $sign2;
                    $pathInfo = pathinfo($sign2);
                    $sign2 = public_path() . '\\' . $subdomain[0] . '\backend\\templates\\100\\' . $pathInfo['filename'] . "_$serial_no." . $pathInfo['extension'];
                    \File::copy($upload_sign2_org, $sign2);

                    $pdf->image($sign2, $sign2_x, $sign2_y, $sign2_Width, $sign2_Height, "", '', 'L', true, 3600);
                    $pdf->setPageMark();

                    $pdfBig->image($sign2, $sign2_x, $sign2_y, $sign2_Width, $sign2_Height, "", '', 'L', true, 3600);
                    $pdfBig->setPageMark();
                }




                if (!empty($studentData[16])) {
                    $name3 = $studentData[16];
                    $sign3_x = 90;
                    $sign3_y = 249;
                    $sign3_Width = 31.75;
                    $sign3_Height = 9.79;
                    if (strpos($name3, 'Jasmine Gohil') !== FALSE) {
                        //if($name3=="Dr. Sridhar B Reddy"){
                        $sign3 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\JasmineGohil_new.png';
                    } elseif (strpos($name3, 'Jigisha Patel') !== FALSE) {
                        $sign3 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Jigisha_Patel_Sign_new.png';
                    } elseif (strpos($name3, 'Suhas Toshniwal') !== FALSE) {
                        $sign3 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
                    } elseif (strpos($name3, 'Hrishikesh Trivedi') !== FALSE) {
                        $sign3 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
                    } elseif (strpos($name3, 'Sanjay Bhatnagar') !== FALSE) {
                        // $sign3 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sanjay_sign.png';
                        $sign3 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\sanjay_new.png';
                        $sign3_Width = 15;
                        $sign3_Height = 17;
                        $sign3_x = 95;
                        $sign3_y = 242;
                    } elseif (strpos($name3, 'Brijesh Kumar Singh') !== FALSE) {
                        $sign3 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Brijesh_Kumar_Singh.png';
                        $sign3_Width = 25;
                        $sign3_y = 237;
                        $sign3_Height = 25;
                        $sign3_x = 93;
                    } elseif (strpos($name3, 'Dr Sanjeev Vidyarthi') !== FALSE) {
                        $sign3 = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Sanjeev_new.png';
                        $sign3_Width = 27;
                        $sign3_Height = 13.79;
                        $sign3_y = 246;
                        $sign3_x = 94;
                    } else {
                        $sign3 = "";
                    }
                    $upload_sign2_org =  $sign3;
                    $pathInfo = pathinfo($sign3);
                    $sign3 = public_path() . '\\' . $subdomain[0] . '\backend\\templates\\100\\' . $pathInfo['filename'] . "_$serial_no." . $pathInfo['extension'];
                    \File::copy($upload_sign2_org, $sign3);

                    $pdf->image($sign3, $sign3_x, $sign3_y, $sign3_Width, $sign3_Height, "", '', 'L', true, 3600);
                    $pdf->setPageMark();

                    $pdfBig->image($sign3, $sign3_x, $sign3_y, $sign3_Width, $sign3_Height, "", '', 'L', true, 3600);
                    $pdfBig->setPageMark();
                }


                //block chain data 
                $cellValue = trim($studentData[18]);
                $parts = preg_split('/,\s+(?=[A-Z][A-Za-z0-9_ ]*:\s*)/', $cellValue);

                $data = [];
                foreach ($parts as $part) {
                    $pair = explode(':', $part, 2);
                    if (count($pair) === 2) {
                        $key = trim($pair[0]);
                        $value = trim($pair[1]);
                        $data[$key] = $value;
                    }
                }

                // dd($data);









                $student_name = $studentData[3];
                if ($previewPdf != 1) {

                    $certName = str_replace("/", "_", $GUID) . ".pdf";
                    // $certName = str_replace(" ", "_", $certName) .".pdf";
                    // dd($certName);
                    array_push($sftpData, $certName);

                    $myPath = public_path() . '/backend/temp_pdf_file';

                    $fileVerificationPath = $myPath . DIRECTORY_SEPARATOR . $certName;

                    $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');

                    $dateOfIssue = date('d/m/Y');
                    $mintData = array();
                    $mintData['documentType'] = "Certificate";
                    $mintData['description'] = "Anant National University";
                    $mintData['metadata1'] = ["label" => "Certificate Sr. No.", "value" => $studentData[0]];
                    $mintData['metadata2'] = ["label" => "Student ID", "value" => $studentData[1]];
                    $mintData['metadata3'] = ["label" => "Student Name", "value" => $studentData[3]];
                    $mintData['metadata4'] = ["label" => "Programme", "value" => $studentData[7]];
                    $mintData['metadata5'] = ["label" => "Title of Award", "value" => $studentData[5]];

                    $mintData['uniqueHash'] = $encryptedString;
                    $serial_no = $studentData[0];

                    $this->addCertificate($serial_no, $certName, $dt, $template_id, $admin_id, $student_name, $mintData);

                    $username = $admin_id['username'];
                    date_default_timezone_set('Asia/Kolkata');

                    $content = "#" . $log_serial_no . " serial No :" . $serial_no . PHP_EOL;
                    $date = date('Y-m-d H:i:s') . PHP_EOL;
                    $print_datetime = date("Y-m-d H:i:s");


                    $print_count = $this->getPrintCount($serial_no);
                    $printer_name = /*'HP 1020';*/ $printer_name;

                    $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no, 'Memorandum', $admin_id, $card_serial_no);

                    $card_serial_no = $card_serial_no + 1;
                } else {
                    $preview_serial_no = $preview_serial_no + 1;
                }

                $generated_documents++;

                if (isset($pdf_data['generation_from']) && $pdf_data['generation_from'] == 'API') {
                    $updated = date('Y-m-d H:i:s');
                    ThirdPartyRequests::where('id', $pdf_data['request_id'])->update(['generated_documents' => $generated_documents, "updated_at" => $updated]);
                } else {
                    //For Custom loader calculation
                    //echo $generated_documents;
                    $endTimeLoader = date('Y-m-d H:i:s');
                    $time1 = new \DateTime($startTimeLoader);
                    $time2 = new \DateTime($endTimeLoader);
                    $interval = $time1->diff($time2);
                    $interval = $interval->format('%s');

                    $jsonArr = array();
                    $jsonArr['token'] = $pdf_data['loader_token'];
                    $jsonArr['generatedCertificates'] = $generated_documents;
                    $jsonArr['timePerCertificate'] = $interval;

                    $loaderData = CoreHelper::createLoaderJson($jsonArr, 0);
                }
                //delete temp dir 26-04-2022 
                CoreHelper::rrmdir($tmpDir);
                $pdf_data_obj = $pdfBig; // Get the PDF data as a string

                // Store the PDF data in the session
                Session::put('pdf_data_obj', $pdf_data_obj);

                // Update code for batchwise genration
                return "Will be generated soon!";
            }
        } else {
        }

        //  if($previewPdf!=1){
        //     $this->updateCardNo('anuAward',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
        // }
        $msg = '';

        $file_name =  str_replace("/", "_", 'anuAward' . date("Ymdhms")) . '.pdf';

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        $filename = public_path() . '/backend/tcpdf/examples/' . $file_name;

        $pdfBig->output($filename, 'F');
        // 

        if ($previewPdf != 1) {
            if (!empty($sftpData)) {
                // Upload on SFTP Server
                // CoreHelper::SFTPUploadAnu($sftpData);
            }
            $aws_qr = \File::copy($filename, public_path() . '/' . $subdomain[0] . '/backend/tcpdf/examples/' . $file_name);
            @unlink($filename);
            // $no_of_records = count($studentDataOrg);
            $no_of_records = $pdf_data['highestrow'];
            $user = $admin_id['username'];
            $template_name = "anuAward";
            if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                // with sandbox

                $result = SbExceUploadHistory::create(['template_name' => $template_name, 'excel_sheet_name' => $excelfile, 'pdf_file' => $file_name, 'user' => $user, 'no_of_records' => $no_of_records, 'site_id' => $auth_site_id]);
            } else {
                // without sandbox
                $result = ExcelUploadHistory::create(['template_name' => $template_name, 'excel_sheet_name' => $excelfile, 'pdf_file' => $file_name, 'user' => $user, 'no_of_records' => $no_of_records, 'site_id' => $auth_site_id]);
            }
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/';
            $pdf_url = $path . $subdomain[0] . "/backend/tcpdf/examples/" . $file_name;
            $msg = "<b>Click <a href='" . $path . $subdomain[0] . "/backend/tcpdf/examples/" . $file_name . "'class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
        } else {
            $aws_qr = \File::copy($filename, public_path() . '/' . $subdomain[0] . '/backend/tcpdf/examples/preview/' . $file_name);
            @unlink($filename);
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/';
            $pdf_url = $path . $subdomain[0] . "/backend/tcpdf/examples/preview/" . $file_name;
            $msg = "<b>Click <a href='" . $path . $subdomain[0] . "/backend/tcpdf/examples/preview/" . $file_name . "' class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
        }
        //API changes
        if (isset($pdf_data['generation_from']) && $pdf_data['generation_from'] == 'API') {
            $updated = date('Y-m-d H:i:s');
            ThirdPartyRequests::where('id', $pdf_data['request_id'])->update(['status' => 'Completed', 'printable_pdf_link' => $pdf_url, "updated_at" => $updated]);
            //Sending data to call back url
            $reaquestParameters = array(
                'request_id' => $pdf_data['request_id'],
                'printable_pdf_link' => $pdf_url,
            );
            $url = $pdf_data['call_back_url'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reaquestParameters));
            $result = curl_exec($ch);

            $updated = date('Y-m-d H:i:s');
            ThirdPartyRequests::where('id', $pdf_data['request_id'])->update(['call_back_response' => json_encode($result), "updated_at" => $updated]);

            curl_close($ch);
        }
        return $msg;
    }

    public function uploadPdfsToServer()
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $certName = "abc.pdf";

        $files = $this->getDirContents(public_path() . '/' . $subdomain[0] . '/backend/pdf_file/');

        foreach ($files as $filename) {
            echo $filename . "<br>";
        }
    }

    public function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            }
        }

        return $results;
    }

    public function downloadPdfsFromServer()
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $accesskey = "Tz7IOmG/9+tyxZpRTAam+Ll3eqA9jezqHdqSdgi+BjHsje0+VM+pKC6USBuR/K0nkw5E7Psw/4IJY3KMgBMLrA==";
        $storageAccount = 'seqrdocpdf';
        $containerName = 'pdffile';

        $files = $this->getDirContents(public_path() . '/' . $subdomain[0] . '/backend/pdf_file/');

        foreach ($files as $filename) {
            $myFile = pathinfo($filename);
            $blobName = 'BMCC\PC\\' . $myFile['basename'];
            echo $destinationURL = "https://$storageAccount.blob.core.windows.net/$containerName/$blobName";

            $local_server_file_path = public_path() . '/' . $subdomain[0] . '/backend/pdf_file_downloaded/' . $blobName;
            if (file_exists($destinationURL)) {
                file_put_contents($local_server_file_path, file_get_contents($destinationURL));
            }
        }
    }


    public function addCertificate($serial_no, $certName, $dt, $template_id, $admin_id, $student_name, $mintData)
    {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $file1 = public_path() . '/backend/temp_pdf_file/' . $certName;
        $file2 = public_path() . '/backend/pdf_file/' . $certName;

        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        $pdfActualPath = public_path() . '/' . $subdomain[0] . '/backend/pdf_file/' . $certName;

        // copy($file1, $file2);        
        // $aws_qr = \File::copy($file2,$pdfActualPath);
        // @unlink($file2);
        /* Server Storage check for already generated pdf and move to inactive folder */
        $storagePath = public_path();
        $file_existes = $storagePath . '/' . $subdomain[0] . '/backend/pdf_file/' . $certName;
        if (file_exists($file_existes)) {

            if (!is_dir($storagePath . '/' . $subdomain[0] . '/backend/pdf_file/Inactive_PDF')) {
                mkdir($storagePath . '/' . $subdomain[0] . '/backend/pdf_file/Inactive_PDF');
            }
            $student = StudentTable::where('status', 1)->where('serial_no', $serial_no)->value('id');
            $inactivePdf = $storagePath . '/' . $subdomain[0] . '/backend/pdf_file/Inactive_PDF/' . $student . '_' . $certName;

            copy($file_existes, $inactivePdf);
        }

        $source = \Config::get('constant.directoryPathBackward') . "\\backend\\temp_pdf_file\\" . $certName;
        $output = \Config::get('constant.directoryPathBackward') . $subdomain[0] . "\\backend\\pdf_file\\" . $certName;
        CoreHelper::compressPdfFile($source, $output);

        // Upload on SFTP Server
        // CoreHelper::SFTPUpload($output);

        @unlink($file1);


        // customMintPDF
        // blockchain code 
        // $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;
        // $pdf_path = "https://verify.anu.edu.in/verify/seqrdoc/pdf_file/".$certName;
        $pdf_path = "https://verify.anu.edu.in/verify/seqrdoc/pdf_file/" . $certName;
        // $mintData['pdf_file']=$pdf_path;
        $mintData['pdf_file'] = $output;
        $mintData['template_id'] = $template_id;
        $mintData['bc_contract_address'] = 'adasdas';

        // [0: Template Maker, 1 : PDF2PDF, 2:Custom, 3: Excel2PDF]
        $template_type = 2;
        $blockchain_type = 1;

        $url = 'http://localhost:9090/docs';
        if ($this->isUrlWorking($url)) {
            $response = CoreHelper::customMintPDF($mintData, $blockchain_type, $template_type);
        } else {
            $response = [];
        }


        // $response = [];
        if ($response['status'] == 200) {
            $bc_txn_hash = $response['txnHash'];
            $bc_sc_id = $response['bc_sc_id'];
            if (isset($response['ipfsHash'])) {
                $bc_ipfs_hash = $response['ipfsHash'];
                $pinata_ipfs_hash = $response['pinataIpfsHash'];
            } else {
                $bc_ipfs_hash = null;
                $pinata_ipfs_hash = null;
            }
        } else {
            $bc_txn_hash = null;
            $bc_ipfs_hash = null;
            $pinata_ipfs_hash = null;
            $bc_sc_id = null;
        }

        // Blockchain code
        //Sore file on azure server

        $sts = '1';
        $datetime  = date("Y-m-d H:i:s");
        $ses_id  = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();

        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path() . '/backend/qr';
        $key = strtoupper(md5($serial_no . $dt));
        $codeContents = $key;
        $fileName = $key . '.png';

        $urlRelativeFilePath = 'qr/' . $fileName;

        if ($systemConfig['sandboxing'] == 1) {
            $resultu = SbStudentTable::where('serial_no', 'T-' . $serial_no)->update(['status' => '0']);
            // Insert the new record

            $result = SbStudentTable::create(['serial_no' => 'T-' . $serial_no, 'student_name' => $student_name, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        } else {
            $resultu = StudentTable::where('serial_no', '' . $serial_no)->update(['status' => '0']);
            // Insert the new record


            // $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2,'certificate_type'=>'Degree Certificate' ,'bc_txn_hash'=>$bc_txn_hash,'bc_ipfs_hash'=>$bc_ipfs_hash,'pinata_ipfs_hash'=>$pinata_ipfs_hash]);
            $bc_file_hash = '';
            $bc_file_hash = CoreHelper::generateFileHash($output);

            $result = StudentTable::create(['serial_no' => $serial_no, 'student_name' => $student_name, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'template_type' => 2, 'certificate_type' => 'Transcript', 'bc_txn_hash' => $bc_txn_hash, 'bc_ipfs_hash' => $bc_ipfs_hash, 'pinata_ipfs_hash' => $pinata_ipfs_hash, 'bc_sc_id' => $bc_sc_id, 'bc_file_hash' => $bc_file_hash]);


            // vendor identifier
            $studentData = StudentTable::where('serial_no', $serial_no)->where('status', 1)->first();

            $result = DB::table('blockchain_other_data')->updateOrInsert(
                ['student_table_id' => $studentData['id']],
                ['vendor_identifier' => 1]

            );
        }

        // blockchain process
        if ($subdomain[0] == 'demo') {

            $mintData['documentType'] = 'pdf';
            $mintData['description'] = 'certificate';
            $mintData['metadata1'] = ["label" => "Student Name", "value" => $student_name];

            $mintData['bc_contract_address'] = '0x9344C4E2cC7b5f2E99959488a1BE900B921B2ba0';
            $mintData['uniqueHash'] = $key;
            $mintData['pdf_file'] = $pdfActualPath;
            $mintData['template_id'] = $template_id;

            $response = CoreHelper::customMintPDF($mintData);

            if ($response['status'] == 200) {
                $resultu = StudentTable::where('serial_no', '' . $serial_no)->where('key', '' . $key)->where('status', 1)->update(['bc_txn_hash' => $response['txnHash']]);
            }
        }
    }

    public function isUrlWorking($url)
    {
        $headers = @get_headers($url);

        if ($headers && strpos($headers[0], '200') !== false) {
            return true; // URL is valid and working
        }
        return false; // Not working
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
        $localFilePath  = $pdfActualPath;
        $remoteFilePath = $certName;
        // try to upload file
        if (ftp_put($connId, $remoteFilePath, $localFilePath, FTP_BINARY)) {
            //echo "File transfer successful - $localFilePath";
        } else {
            //echo "There was an error while uploading $localFilePath";
        }
        // close the connection
        ftp_close($connId);
    }

    public function getPrintCount($serial_no)
    {
        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }
        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        $numCount = PrintingDetail::select('id')->where('sr_no', $serial_no)->count();

        return $numCount + 1;
    }
    public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no, $template_name, $admin_id, $card_serial_no)
    {

        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];

        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }

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

        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        $result = \DB::select("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num "
            . "FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '$current_year'");
        //get next num
        $maxNum = $result[0]->next_num + 1;

        return $current_year . $maxNum;
    }

    public function getNextCardNo($template_name)
    {
        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }
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
        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }

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
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
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
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
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
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
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
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
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

            $filename = 'http://' . $_SERVER['HTTP_HOST'] . "/backend/canvas/ghost_images/F13_H10_W360.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename = 'http://' . $_SERVER['HTTP_HOST'] . "/backend/canvas/ghost_images/alpha_GHOST.png";
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
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
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
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
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
