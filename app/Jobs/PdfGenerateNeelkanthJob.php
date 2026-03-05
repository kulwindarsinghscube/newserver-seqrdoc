<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\models\BackgroundTemplateMaster;
use Session,TCPDF,TCPDF_FONTS,Auth,DB;
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
class PdfGenerateNeelkanthJob
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
    public function handle(CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {
        $pdf_data = $this->pdf_data;        
        $studentDataOrg=$pdf_data['studentDataOrg'];
        $subjectsMark=$pdf_data['subjectsMark'];
        $template_id=$pdf_data['template_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
       
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
        $photo_col=23;

        $first_sheet=$pdf_data['studentDataOrg']; // get first worksheet rows
       
        
		       
        
        if(isset($pdf_data['generation_from'])&&$pdf_data['generation_from']=='API'){        
			$admin_id=$pdf_data['admin_id'];
        }else{
			$admin_id = \Auth::guard('admin')->user()->toArray();  
        }
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        $printer_name = $systemConfig['printer_name'];

      
        $ghostImgArr = array();
        $pdfBig = new TCPDF('L', 'mm', array('210', '156'), true, 'UTF-8', false);
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
        $pdfBig->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $trebuc = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\trebuc.ttf', 'TrueTypeUnicode', '', 96);
        $trebucb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\TREBUCBD.ttf', 'TrueTypeUnicode', '', 96);
        $trebuci = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Trebuchet-MS-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $graduateR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\graduate-regular.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Regular 400.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsM = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Medium 500.ttf', 'TrueTypeUnicode', '', 96);

        $preview_serial_no=1;
		//$card_serial_no="";
        $log_serial_no = 1;
        $cardDetails=$this->getNextCardNo('NeelkanthFD');
        $card_serial_no=$cardDetails->next_serial_no;
        $generated_documents=0;  //for custom loader
        foreach ($studentDataOrg as $studentData) {
         
			if($card_serial_no>999999&&$previewPdf!=1){
				echo "<h5>Your card series ended...!</h5>";
				exit;
			}
			//For Custom Loader
            $startTimeLoader =  date('Y-m-d H:i:s');    
			$high_res_bg="neelkanth_co_op_bank_bg.jpg"; // neelkanth_co_op_bank_bg
			$low_res_bg="neelkanth_co_op_bank_bg.jpg";
			$pdfBig->AddPage();
			$pdfBig->SetFont($arialNarrowB, '', 8, '', false);
			//set background image
			$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;   

			if($previewPdf==1){
				if($previewWithoutBg!=1){
					$pdfBig->Image($template_img_generate, 0, 0, '210', '156', "JPG", '', 'R', true);
				}
				$date_font_size = '11';
				$date_nox = 13;
				$date_noy = 40;
				$date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
				
			}
			$pdfBig->setPageMark();

			$ghostImgArr = array();
			$pdf = new TCPDF('L', 'mm', array('210', '156'), true, 'UTF-8', false);
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('TCPDF');
			$pdf->SetTitle('Grade Card');
			$pdf->SetSubject('');

			// remove default header/footer
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetAutoPageBreak(false, 0);


			// add spot colors
			//$pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
			//$pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

			$pdf->AddPage();        
			$print_serial_no = $this->nextPrintSerial();
			//set background image
			$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$low_res_bg;

			if($previewPdf!=1){
			$pdf->Image($template_img_generate, 0, 0, '210', '156', "JPG", '', 'R', true);
			}
			//$pdf->setPageMark();
			$pdf->setPageMark();
			//$pdfBig->setPageMark();
            //if($previewPdf!=1){            
                $x= 173;
                $y = 39.1;
                $font_size=12;
                if($previewPdf!=1){
					$str = str_pad($card_serial_no, 7, '0', STR_PAD_LEFT);
				}else{
					$str = str_pad($preview_serial_no, 7, '0', STR_PAD_LEFT);	
				}
                $strArr = str_split($str);
                $x_org=$x;
                $y_org=$y;
                $font_size_org=$font_size;
                $i =0;
                $j=0;
                $y=$y+4.5;
                $z=0;
                foreach ($strArr as $character) {
                    $pdf->SetFont($arialNarrow,0, $font_size, '', false);
                    $pdf->SetXY($x, $y+$z);

                    $pdfBig->SetFont($arialNarrow,0, $font_size, '', false);
                    $pdfBig->SetXY($x, $y+$z);

                    if($i==3){
                        $j=$j+0.2;
                    }else if($i>1){
						$j=$j+0.1;   
                    }
                   
                   if($i>1){
                       $z=$z+0.1;
                    }
                    if($i>3){
                      $x=$x+0.4;  
                    }else if($i>2){
                      $x=$x+0.2;
                    } 
                   //$pdf->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
                   //$pdfBig->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
                    $i++;
                    $x=$x+2.2+$j; 
                    if($i>2){
                     $font_size=$font_size+1.7;   
                    }
                }         
            //}
			

            
			$branchName = 'MAIN Branch';
            $customerId = trim($studentData[13]);
            $name = trim($studentData[3]);
            $panNo = trim($studentData[14]);

            $fdSrNo = trim($studentData[4]);

            $accPrefix = trim($studentData[1]);
            $accNo = trim($studentData[2]);
            $recPrefix = trim($studentData[0]);
            $recNo = trim($studentData[5]);
            $nomineeName = trim($studentData[15]);
            
            $depositeAmt = trim($studentData[8]);
            $depositeDate = trim($studentData[6]);
            //$depositePeriod = trim($studentData[11]);
            $depositeMonthPeriod = trim($studentData[11]);
            $depositeDayPeriod = trim($studentData[12]);
            $MaturityDate = trim($studentData[7]);
            $intRate = trim($studentData[9]);
            $MaturityAmt = trim($studentData[10]);
            $depositeAmtInWords= ucwords($this->getIndianCurrency($depositeAmt))." Only";

            $depositeAmt = $this->formatAmount($depositeAmt);
            $MaturityAmt = $this->formatAmount($MaturityAmt);

            
            if(!empty($depositeMonthPeriod) && $depositeMonthPeriod != 'NULL' && !empty($depositeDayPeriod) && $depositeDayPeriod != 'NULL') {
                $depositePeriod = $depositeMonthPeriod.' Months <br>'.$depositeDayPeriod.' Days';
            } else if(!empty($depositeMonthPeriod) && $depositeMonthPeriod != 'NULL') {
                $depositePeriod = $depositeMonthPeriod.' Months';
            } else if(!empty($depositeDayPeriod) && $depositeDayPeriod != 'NULL') {
                $depositePeriod = $depositeDayPeriod.' Days';
            } else {
                $depositePeriod = '';
            }

            
                        

            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetTextColor(35, 31, 32);
            $pdfBig->SetXY(175, 50);
            $pdfBig->MultiCell(30, 0, "DEPOSIT<br>RECEIPT", 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetTextColor(35, 31, 32);
            $pdfBig->SetXY(38, 52);
            $pdfBig->MultiCell(60, 0, "Branch: &nbsp;&nbsp;<b>".$branchName."</b>", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(36, 57);
            $pdfBig->MultiCell(60, 0, "Customer ID: &nbsp;&nbsp;<b>".$customerId."</b>", 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetXY(22, 62);
            $pdfBig->MultiCell(78, 0, "Name :", 0, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->SetXY(22, 66);
            $pdfBig->MultiCell(78, 0,"<b>".$name."</b>", 0, "L", 0, 0, '', '', true, 0, true);
            

            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(104, 50);
            $pdfBig->MultiCell(20, 0, "FD Sr.No.:", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(124, 50);
            $pdfBig->MultiCell(40, 0, $fdSrNo, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(104, 54.5);
            $pdfBig->MultiCell(20, 0, "PAN.No:", 0, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(124, 54.5);
            $pdfBig->MultiCell(40, 0, $panNo, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(104, 59);
            $pdfBig->MultiCell(20, 0, "Deposit Type:", 0, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(124, 59);
            $pdfBig->MultiCell(40, 0, "DHANRASHI THAVE", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(104, 63.5);
            $pdfBig->MultiCell(20, 0, "Scheme No:", 0, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(124, 63.5);
            $pdfBig->MultiCell(40, 0, $accPrefix, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(104, 68);
            $pdfBig->MultiCell(20, 0, "Account No:", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(124, 68);
            $pdfBig->MultiCell(40, 0, $accNo, 0, "L", 0, 0, '', '', true, 0, true);
            // $pdfBig->MultiCell(40, 0, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$accNo, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(104, 72.5);
            $pdfBig->MultiCell(20, 0, "Receipt No:", 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(124, 72.5);
            $pdfBig->MultiCell(40, 0, $recNo, 0, "L", 0, 0, '', '', true, 0, true);
            // $pdfBig->MultiCell(40, 0, $recPrefix."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$recNo, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(22, 73);
            $pdfBig->MultiCell(20, 0, "Nominee:", 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(36, 73);
            $pdfBig->MultiCell(70, 0, $nomineeName, 0, "L", 0, 0, '', '', true, 0, true);

            // box details
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(22, 80);
            $pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdfBig->MultiCell(25, 8, "Deposit<br>Amount (In Rs.)", 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(22, 88);
            $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(25, 8, "<b>".$depositeAmt."</b>", 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(47, 80);
            $pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdfBig->MultiCell(23, 8, "Deposit<br>Date", 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(47, 88);
            $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(23, 8, "<b>".$depositeDate."</b>", 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->SetXY(70, 80);
            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(22, 8, "As On<br>Date", 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(70, 88);
            $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(22, 8, "<b>".$depositeDate."</b>", 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->SetXY(92, 80);
            $pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdfBig->MultiCell(23, 8, "Deposite<br>Period", 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(92, 88);
            if(!empty($depositeMonthPeriod) && $depositeMonthPeriod != 'NULL' && !empty($depositeDayPeriod) && $depositeDayPeriod != 'NULL') {
                $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            } else if(!empty($depositeMonthPeriod) && $depositeMonthPeriod != 'NULL') {
                $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            } else if(!empty($depositeDayPeriod) && $depositeDayPeriod != 'NULL') {
                $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            } else {
                $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            }

            $pdfBig->MultiCell(23, 8, "<b>".$depositePeriod."</b>", 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(115, 80);
            $pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdfBig->MultiCell(22, 8, "Maturity<br>Date", 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(115, 88);
            $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(22, 8, "<b>".$MaturityDate."</b>", 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(137, 80);
            $pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdfBig->MultiCell(23, 8, "Interest Rate<br>(% p.a)", 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(137, 88);
            $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(23, 8, "<b>".$intRate."</b>", 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->SetXY(160, 80);
            $pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdfBig->MultiCell(28, 8, "Maturity<br>Amount (in Rs.)", 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(160, 88);
            $pdfBig->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(28, 8, "<b>".$MaturityAmt."</b>", 1, "C", 0, 0, '', '', true, 0, true);


            //  $amountInword = "";
            $pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
            $pdfBig->SetXY(22, 101);
            $pdfBig->MultiCell(190, 0, "Deposit Amount In Words: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>".$depositeAmtInWords."</b>", 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetXY(22, 107);
            $pdfBig->MultiCell(190, 0, "Joint Account Holders:", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(22, 125);
            $pdfBig->MultiCell(190, 0, "Subject to TDS", 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(130, 109);
            $pdfBig->MultiCell(190, 0, "Neelkanth Co.Op.Bank Ltd., Solapur", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(127, 124);
            $pdfBig->MultiCell(190, 0, "Officer", 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(165, 124);
            $pdfBig->MultiCell(190, 0, "Br. Manager", 0, "L", 0, 0, '', '', true, 0, true);
            /*end pdfBig*/
            
            /*start pdf*/            
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetTextColor(35, 31, 32);
            $pdf->SetXY(175, 50);
            $pdf->MultiCell(30, 0, "DEPOSIT<br>RECEIPT", 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetTextColor(35, 31, 32);
            $pdf->SetXY(38, 52);
            $pdf->MultiCell(60, 0, "Branch: &nbsp;&nbsp;<b>".$branchName."</b>", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetXY(36, 57);
            $pdf->MultiCell(60, 0, "Customer ID: &nbsp;&nbsp;<b>".$customerId."</b>", 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetXY(22, 62);
            $pdf->MultiCell(78, 0, "Name :", 0, "L", 0, 0, '', '', true, 0, true);
            $pdf->SetXY(22, 66);
            $pdf->MultiCell(78, 0,"<b>".$name."</b>", 0, "L", 0, 0, '', '', true, 0, true);
            


            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(104, 50);
            $pdf->MultiCell(20, 0, "FD Sr.No.:", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(124, 50);
            $pdf->MultiCell(40, 0, $fdSrNo, 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(104, 54.5);
            $pdf->MultiCell(20, 0, "PAN.No:", 0, "L", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(124, 54.5);
            $pdf->MultiCell(40, 0, $panNo, 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(104, 59);
            $pdf->MultiCell(20, 0, "Deposit Type:", 0, "L", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(124, 59);
            $pdf->MultiCell(40, 0, "DHANRASHI THAVE", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(104, 63.5);
            $pdf->MultiCell(20, 0, "Scheme No:", 0, "L", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(124, 63.5);
            $pdf->MultiCell(40, 0, $accPrefix, 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(104, 68);
            $pdf->MultiCell(20, 0, "Account No:", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(124, 68);
            $pdf->MultiCell(40, 0, $accNo, 0, "L", 0, 0, '', '', true, 0, true);
            // $pdf->MultiCell(40, 0, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$accNo, 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(104, 72.5);
            $pdf->MultiCell(20, 0, "Receipt No:", 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(124, 72.5);
            $pdf->MultiCell(40, 0, $recNo, 0, "L", 0, 0, '', '', true, 0, true);
            // $pdf->MultiCell(40, 0, $recPrefix."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$recNo, 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(22, 73);
            $pdf->MultiCell(20, 0, "Nominee:", 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(36, 73);
            $pdf->MultiCell(70, 0, $nomineeName, 0, "L", 0, 0, '', '', true, 0, true);

            // box details
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(22, 80);
            $pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdf->MultiCell(25, 8, "Deposit<br>Amount (In Rs.)", 1, "C", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(22, 88);
            $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdf->MultiCell(25, 8, "<b>".$depositeAmt."</b>", 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetXY(47, 80);
            $pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdf->MultiCell(23, 8, "Deposit<br>Date", 1, "C", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(47, 88);
            $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdf->MultiCell(23, 8, "<b>".$depositeDate."</b>", 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->SetXY(70, 80);
            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            $pdf->MultiCell(22, 8, "As On<br>Date", 1, "C", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(70, 88);
            $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdf->MultiCell(22, 8, "<b>".$depositeDate."</b>", 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->SetXY(92, 80);
            $pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdf->MultiCell(23, 8, "Deposite<br>Period", 1, "C", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(92, 88);
            //$pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            if(!empty($depositeMonthPeriod) && $depositeMonthPeriod != 'NULL' && !empty($depositeDayPeriod) && $depositeDayPeriod != 'NULL') {
                $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
            } else if(!empty($depositeMonthPeriod) && $depositeMonthPeriod != 'NULL') {
                $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            } else if(!empty($depositeDayPeriod) && $depositeDayPeriod != 'NULL') {
                $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            } else {
                $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            }

            $pdf->MultiCell(23, 8, "<b>".$depositePeriod."</b>", 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetXY(115, 80);
            $pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdf->MultiCell(22, 8, "Maturity<br>Period", 1, "C", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(115, 88);
            $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdf->MultiCell(22, 8, "<b>".$MaturityDate."</b>", 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetXY(137, 80);
            $pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdf->MultiCell(23, 8, "Interest Rate<br>(% p.a)", 1, "C", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(137, 88);
            $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdf->MultiCell(23, 8, "<b>".$intRate."</b>", 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->SetXY(160, 80);
            $pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
            $pdf->MultiCell(28, 8, "Maturity<br>Amount (in Rs.)", 1, "C", 0, 0, '', '', true, 0, true);
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(160, 88);
            $pdf->setCellPaddings( $left = '', $top = '2.5', $right = '', $bottom = '');
            $pdf->MultiCell(28, 8, "<b>".$MaturityAmt."</b>", 1, "C", 0, 0, '', '', true, 0, true);


           // $amountInword = "Rupees Five Lakhs Only";
            $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
            $pdf->SetXY(22, 101);
            $pdf->MultiCell(190, 0, "Deposit Amount In Words: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>".$depositeAmtInWords."</b>", 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetXY(22, 107);
            $pdf->MultiCell(190, 0, "Joint Account Holders:", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(22, 125);
            $pdf->MultiCell(190, 0, "Subject to TDS", 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(130, 109);
            $pdf->MultiCell(190, 0, "Neelkanth Co.Op.Bank Ltd., Solapur", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(127, 124);
            $pdf->MultiCell(190, 0, "Officer", 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(165, 124);
            $pdf->MultiCell(190, 0, "Br. Manager", 0, "L", 0, 0, '', '', true, 0, true);


			/*end pdf*/			
			
			// Ghost image
			$nameOrg= $name;
			// $ghost_font_size = '13';
			// $ghostImagex = 77;
			// $ghostImagey = 269;
			// $ghostImageWidth = 55;
			// $ghostImageHeight = 9.8;	
			

            $ghost_font_size = '11';//13
            $ghostImagex = 128;
            $ghostImagey =133;//85
            $ghostImageWidth = 91.9;
            $ghostImageHeight = 7;//9.8


			/*$ghost_font_size = '12';
            $ghostImagex = 146;
            $ghostImagey = 262;
            $ghostImageWidth = 39.405983333;
            $ghostImageHeight = 8;*/
            $nameQr = $name;
            $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
            $tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
            $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');
			$pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
            $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
			$pdf->setPageMark();
			$pdfBig->setPageMark();
            
            // $pdfBig->SetFont('Arial', '', 1.6, '', false);//1.2
            // $pdfBig->setXY(95, 123);//258
            // $pdfBig->MultiCell(19, 0, trim($nameOrg), '', 'C', 0, 1);


            // $pdf->SetFont('Arial', '', 1.6, '', false);//1.2
            // $pdf->setXY(95, 123);//258
            // $pdf->MultiCell(19, 0, trim($nameOrg), '', 'C', 0, 1);
            // Microline
            $fullStr =trim($nameOrg);
			$microlineEnrollment1=$fullStr;
			//$microlineEnrollment1 = preg_replace('/\s+/', '', $microlineEnrollment1);
			$textArrayEnrollment1 = imagettfbbox(1.2, 0, public_path().'/'.$subdomain[0].'/backend/canvas/fonts/Arialb.TTF', $microlineEnrollment1);
			$strWidthEnrollment1 = ($textArrayEnrollment1[2] - $textArrayEnrollment1[0]);
			$strHeightEnrollment1 = $textArrayEnrollment1[6] - $textArrayEnrollment1[1] / 1.2;
			$latestWidthEnrollment1 = 500;
			$microlineEnrollmentstrLength1=strlen($microlineEnrollment1);
			//width per character
			$microlineEnrollmentcharacterWd1 =$strWidthEnrollment1/$microlineEnrollmentstrLength1;
			//Required no of characters required in string to match width
			$microlineEnrollmentCharReq1=$latestWidthEnrollment1/$microlineEnrollmentcharacterWd1;
			$microlineEnrollmentCharReq1=round($microlineEnrollmentCharReq1);
			//No of time string should repeated
			$repeatemicrolineEnrollmentCount1=$latestWidthEnrollment1/$strWidthEnrollment1;
			$repeatemicrolineEnrollmentCount1=round($repeatemicrolineEnrollmentCount1)+1;
			//Repeatation of string 
			$microlineEnrollmentstrRep1 = str_repeat($microlineEnrollment1, $repeatemicrolineEnrollmentCount1);                
			//Cut string in required characters (final string)
			$arrayEnrollment1 = substr($microlineEnrollmentstrRep1,0,$microlineEnrollmentCharReq1);
			$wdEnrollment1 = '';
			$last_widthEnrollment1 = 0;
			$messageEnrollment1 = array();                
			$pdfBig->SetFont($arial, '', 1.2, '', false);
			$pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->StartTransform();

            $pdf->SetFont($arial, '', 1.2, '', false);
			$pdf->SetTextColor(0, 0, 0);
            $pdf->StartTransform();

            

			$xClip=130;
			$yClip=112.4;
			$wClip=49.4;
			$hClip=6;
			$pdfBig->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');
			$pdf->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');

			$pdfBig->SetXY(130, 112.5);
			$pdfBig->Cell(50, 0, $arrayEnrollment1, 0, false, 'L');
			$pdfBig->StopTransform();

            $pdf->SetXY(130, 112.5);
			$pdf->Cell(50, 0, $arrayEnrollment1, 0, false, 'L');
			$pdf->StopTransform();

            // Microline
            
            
			$serial_no=$GUID=$recPrefix."_".$accPrefix."_".$accNo."_".$recNo;
			//qr code    
			$dt = date("_ymdHis");
			$str=$GUID.$dt;
            $QR_Output = "";
            $QR_Output .= "Name : ".$nameQr."\n";
            $QR_Output .= "FD Sr.No : ".$fdSrNo."\n";
            $QR_Output .= "Scheme No : ".$accPrefix."\n";
            $QR_Output .= "Account No : ".$accNo."\n";
            $QR_Output .= "Receipt No : ".$recNo."\n";
            $QR_Output .= "Maturity Date: ".$MaturityDate."\n";
            $QR_Output .= "Maturity Amount: ".$MaturityAmt."\n";
			$codeContents = $QR_Output."\n\n".strtoupper(md5($str));
			$encryptedString = strtoupper(md5($str));
			$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
			$qrCodex = 93; 
			$qrCodey = 121.5;
			$qrCodeWidth =21;
			$qrCodeHeight = 21;
			$ecc = 'L';
			$pixel_Size = 1;
			$frame_Size = 1;  
			\PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);

            // \QrCode::backgroundColor(255, 255, 0)            
            //     ->format('png')        
            //     ->size(500)    
            //     ->generate($codeContents, $qr_code_path);

			$pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
			$pdf->setPageMark(); 
			$pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
			$pdfBig->setPageMark(); 			
			
            
			
			
			
			
			
			

			
			if($previewPdf!=1){

				$certName = str_replace("/", "_", $GUID);
				$certName = str_replace(" ", "_", $certName) .".pdf";
				
				$myPath = public_path().'/backend/temp_pdf_file';

				$fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;

				$pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');

				 $this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id);

				$username = $admin_id['username'];
				date_default_timezone_set('Asia/Kolkata');

				$content = "#".$log_serial_no." serial No :".$serial_no.PHP_EOL;
				$date = date('Y-m-d H:i:s').PHP_EOL;
				$print_datetime = date("Y-m-d H:i:s");
				

				$print_count = $this->getPrintCount($serial_no);
				$printer_name = /*'HP 1020';*/$printer_name;

				$this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'Memorandum',$admin_id,$card_serial_no);

				$card_serial_no=$card_serial_no+1;
			}else{
				$preview_serial_no=$preview_serial_no+1;
			}

            $generated_documents++;

            if(isset($pdf_data['generation_from'])&&$pdf_data['generation_from']=='API'){
                $updated=date('Y-m-d H:i:s');
                ThirdPartyRequests::where('id',$pdf_data['request_id'])->update(['generated_documents'=>$generated_documents,"updated_at"=>$updated]);
            }else{
              //For Custom loader calculation
                //echo $generated_documents;
              $endTimeLoader = date('Y-m-d H:i:s');
              $time1 = new \DateTime($startTimeLoader);
              $time2 = new \DateTime($endTimeLoader);
              $interval = $time1->diff($time2);
              $interval = $interval->format('%s');

              $jsonArr=array();
              $jsonArr['token'] = $pdf_data['loader_token'];
              $jsonArr['generatedCertificates'] =$generated_documents;
              $jsonArr['timePerCertificate'] =$interval;
             
              $loaderData=CoreHelper::createLoaderJson($jsonArr,0);
            }
            //delete temp dir 26-04-2022 
        	CoreHelper::rrmdir($tmpDir);	
        } 
        
       if($previewPdf!=1){
        $this->updateCardNo('NeelkanthFD',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
       }
       $msg = '';
        
        $file_name =  str_replace("/", "_",'NeelkanthFD'.date("Ymdhms")).'.pdf';
        
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();


       $filename = public_path().'/backend/tcpdf/examples/'.$file_name;
        
        $pdfBig->output($filename,'F');

        if($previewPdf!=1){
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name="NeelkanthFD";
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                // with sandbox
                
                $result = SbExceUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
            }else{
                // without sandbox
                $result = ExcelUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
            }
			$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
			$path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
			$pdf_url=$path.$subdomain[0]."/backend/tcpdf/examples/".$file_name;
			$msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/".$file_name."'class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
		}else{
        $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/preview/'.$file_name);
        @unlink($filename);
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
        $pdf_url=$path.$subdomain[0]."/backend/tcpdf/examples/preview/".$file_name;
        $msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/preview/".$file_name."' class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
        }
        //API changes
        if(isset($pdf_data['generation_from'])&&$pdf_data['generation_from']=='API'){
         $updated=date('Y-m-d H:i:s');
        
        ThirdPartyRequests::where('id',$pdf_data['request_id'])->update(['status'=>'Completed','printable_pdf_link'=>$pdf_url,"updated_at"=>$updated]);
        //Sending data to call back url
        $reaquestParameters = array
        (
            'request_id'=>$pdf_data['request_id'],
            'printable_pdf_link' => $pdf_url,
        );
        $url = $pdf_data['call_back_url'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reaquestParameters));
        $result = curl_exec($ch);
        
        $updated=date('Y-m-d H:i:s');
        ThirdPartyRequests::where('id',$pdf_data['request_id'])->update(['call_back_response'=>json_encode($result),"updated_at"=>$updated]);

        curl_close($ch);
        }

        return $msg;



    }

    public function uploadPdfsToServer(){
         $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
            $certName="abc.pdf";
         
        $files=$this->getDirContents(public_path().'/'.$subdomain[0].'/backend/pdf_file/');

foreach ($files as $filename) {
echo $filename."<br>";
}
    }

public function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } 
    }

    return $results;
}

public function downloadPdfsFromServer(){
 $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
$accesskey = "Tz7IOmG/9+tyxZpRTAam+Ll3eqA9jezqHdqSdgi+BjHsje0+VM+pKC6USBuR/K0nkw5E7Psw/4IJY3KMgBMLrA==";
$storageAccount = 'seqrdocpdf';
$containerName = 'pdffile';

        $files=$this->getDirContents(public_path().'/'.$subdomain[0].'/backend/pdf_file/');

foreach ($files as $filename) {
$myFile = pathinfo($filename); 
$blobName = 'BMCC\PC\\'.$myFile['basename'];
echo $destinationURL = "https://$storageAccount.blob.core.windows.net/$containerName/$blobName";

$local_server_file_path= public_path().'/'.$subdomain[0].'/backend/pdf_file_downloaded/'.$blobName;
if(file_exists($destinationURL)){
file_put_contents($local_server_file_path, file_get_contents($destinationURL));
}
}

}

    public function addCertificate($serial_no, $certName, $dt,$template_id,$admin_id)
    {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $file1 = public_path().'/backend/temp_pdf_file/'.$certName;
        $file2 = public_path().'/backend/pdf_file/'.$certName;
        
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        } 

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        $pdfActualPath=public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;

        copy($file1, $file2);        
        $aws_qr = \File::copy($file2,$pdfActualPath);
        @unlink($file2);
		// $source=\Config::get('constant.directoryPathBackward')."\\backend\\temp_pdf_file\\".$certName;
		// $output=\Config::get('constant.directoryPathBackward').$subdomain[0]."\\backend\\pdf_file\\".$certName; 
		// CoreHelper::compressPdfFile($source,$output);
        // @unlink($file1);

        //Sore file on azure server

        $sts = '1';
        $datetime  = date("Y-m-d H:i:s");
        $ses_id  = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();
     
        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path().'/backend/qr';
        $key = strtoupper(md5($serial_no.$dt)); 
        $codeContents = $key;
        $fileName = $key.'.png'; 
        
        $urlRelativeFilePath = 'qr/'.$fileName; 

        if($systemConfig['sandboxing'] == 1){
        $resultu = SbStudentTable::where('serial_no','T-'.$serial_no)->update(['status'=>'0']);
        // Insert the new record
        
        $result = SbStudentTable::create(['serial_no'=>'T-'.$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id]);
        }else{
        $resultu = StudentTable::where('serial_no',''.$serial_no)->update(['status'=>'0']);
        // Insert the new record
        
        $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2]);
        }
        
    }
    
    public function testUpload($certName, $pdfActualPath)
    {
        // FTP server details
        $ftpHost = \Config::get('constant.anu_ftp_host');
        $ftpPort = \Config::get('constant.anu_ftp_port');
        $ftpUsername = \Config::get('constant.anu_ftp_username');
        $ftpPassword = \Config::get('constant.anu_ftp_pass');        
        // open an FTP connection
        $connId = ftp_connect($ftpHost,$ftpPort) or die("Couldn't connect to $ftpHost");
        // login to FTP server
        $ftpLogin = ftp_login($connId, $ftpUsername, $ftpPassword);
        // local & server file path
        $localFilePath  = $pdfActualPath;
        $remoteFilePath = $certName;
        // try to upload file
        if(ftp_put($connId, $remoteFilePath, $localFilePath, FTP_BINARY)){
            //echo "File transfer successful - $localFilePath";
        }else{
            //echo "There was an error while uploading $localFilePath";
        }
        // close the connection
        ftp_close($connId);
    }
    
    public function getPrintCount($serial_no)
    {
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        }
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $numCount = PrintingDetail::select('id')->where('sr_no',$serial_no)->count();
        
        return $numCount + 1;
    
}
public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no,$template_name,$admin_id,$card_serial_no)
    {
       
        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];

        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        if($systemConfig['sandboxing'] == 1){
        $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>'T-'.$sr_no,'template_name'=>$template_name,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);
        }else{
        $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>$sr_no,'template_name'=>$template_name,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);    
        }
    }

    public function nextPrintSerial()
    {
        $current_year = 'PN/' . $this->getFinancialyear() . '/';
        // find max
        $maxNum = 0;
        
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $result = \DB::select("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num "
                . "FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '$current_year'");
        //get next num
        $maxNum = $result[0]->next_num + 1;
       
        return $current_year . $maxNum;
    }

    public function getNextCardNo($template_name)
    { 
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        
        }
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
        //Updated by Mandar for api based pdf generation
        if(Auth::guard('admin')->user()){
            $auth_site_id=Auth::guard('admin')->user()->site_id;
        }else{
            $auth_site_id=$this->pdf_data['auth_site_id'];
        }

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

            $filename = public_path()."/backend/canvas/ghost_images/F15_H14_W504.png";

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
            
            $filename = public_path()."/backend/canvas/ghost_images/F10_H5_W180.png";
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

            $filename ='http://'.$_SERVER['HTTP_HOST']."/backend/canvas/ghost_images/F13_H10_W360.png";
             $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename ='http://'.$_SERVER['HTTP_HOST']."/backend/canvas/ghost_images/alpha_GHOST.png";
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
            
        }
        
        $ret = array();
        $ret[0] = (205 - $sum)/2;
        for($i = 1; $i < $len; $i++)
        {
            $ret[$i] = $ret[$i - 1] + $w[$i - 1] ;
            
        }
        
        return $ret;
    }

    function sanitizeQrString($content){
         $find = array('â€œ', 'â€™', 'â€¦', 'â€”', 'â€“', 'â€˜', 'Ã©', 'Â', 'â€¢', 'Ëœ', 'â€'); // en dash
         $replace = array('“', '’', '…', '—', '–', '‘', 'é', '', '•', '˜', '”');
        return $content = str_replace($find, $replace, $content);
    }


    function formatAmount($amount, $decimals = 2) {
      return number_format($amount, $decimals, '.', ',');
    }

    function convertINRToWords($amount) {

  // Define number to word conversion for units, tens, and hundreds
  $numberToWord = array(
    0 => "",
    1 => "One",
    2 => "Two",
    3 => "Three",
    4 => "Four",
    5 => "Five",
    6 => "Six",
    7 => "Seven",
    8 => "Eight",
    9 => "Nine",
    10 => "Ten",
    11 => "Eleven",
    12 => "Twelve",
    13 => "Thirteen",
    14 => "Fourteen",
    15 => "Fifteen",
    16 => "Sixteen",
    17 => "Seventeen",
    18 => "Eighteen",
    19 => "Nineteen",
    20 => "Twenty",
    30 => "Thirty",
    40 => "Forty",
    50 => "Fifty",
    60 => "Sixty",
    70 => "Seventy",
    80 => "Eighty",
    90 => "Ninety",
  );

  // Define word representation for larger denominations (thousands, lakhs, crores)
  $denominations = array(
    1000 => "Thousand",
    100000 => "Lakh",
    10000000 => "Crore",
  );

  // Handle negative numbers
  if ($amount < 0) {
    $amount = -$amount;
    $result = "Negative ";
  } else {
    $result = "";
  }

  // Separate whole number and decimal parts (if any)
  $number = floor($amount);
  $decimal = round($amount - $number, 2) * 100; // Convert decimal to paise

  // Convert whole number part
  $result = $this->convertNumberToWords($number, $denominations, $result);

  // Handle decimal part (paise)
  if ($decimal > 0) {
    $result .= " Rupees and ";
    $result .= $this->convertNumberToWords($decimal, $numberToWord);
    $result .= " Paise";
  } else {
    $result .= " Rupees Only";
  }

  return $result;
}

// Function to recursively convert numbers to words
    function convertNumberToWords($number, $dictionary, $result = "") {
      $remainder = 0;

      // Traverse denominations
      foreach (array_reverse($dictionary) as $key => $value) {

        if ($key > 0) {
        $units = (int) ($number / $key);
        $remainder = $number % $key;

        }else{
           $units =  0 ;
           $remainder =0; 
        }
        if ($units > 0) {
          $result .= ($result != "" ? " " : "") . $dictionary[$key] . " " . $this->convertNumberToWords($units, $numberToWord, "");
        }

        $number = $remainder;
      }

      // Handle teens (11-19) and two-digit numbers
      if ($number < 20 && $number > 0) {
        $result .= ($result != "" ? " " : "") . $dictionary[$number];
      } else if ($number > 0) {
        $tens = (int) ($number / 10) * 10;
        $ones = $number % 10;
        $result .= ($result != "" ? " " : "") . $dictionary[$tens];
        if ($ones > 0) {
          $result .= " " . $dictionary[$ones];
        }
      }

      return $result;
    }



    function getIndianCurrency(float $number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'one', 2 => 'two',
            3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
            7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve',
            13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
            19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
            40 => 'forty', 50 => 'fifty', 60 => 'sixty',
            70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
        $digits = array('', 'hundred','thousand','lakh', 'crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
    }




  
}
