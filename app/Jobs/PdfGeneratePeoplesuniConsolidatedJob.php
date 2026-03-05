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

class PdfGeneratePeoplesuniConsolidatedJob
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
        $subjectDataOrg=$pdf_data['subjectDataOrg'];
        $template_id=$pdf_data['template_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
 
        $first_sheet=$pdf_data['studentDataOrg']; // get first worksheet rows
        

		$total_unique_records=count($first_sheet);
        $last_row=$total_unique_records+1;


        $photo_col=1;
        // dd($pdf_data);
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
        $pdfBig = new TCPDF('P', 'mm', "A4", true, 'UTF-8', false);
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
        
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);


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
        $pdf->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        //set fonts
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\times_B.ttf', 'TrueTypeUnicode', '', 96);
        
        
        // dd( $times);

        $preview_serial_no=1;
		//$card_serial_no="";
        $log_serial_no = 1;
        $cardDetails=$this->getNextCardNo('PeopleConsolidatedMarksheet');
        $card_serial_no=$cardDetails->next_serial_no;
        $generated_documents=0;  //for custom loader


        // Subject Hour
        $subjectsArr = array();
        foreach ($subjectDataOrg as $element) {
            $subjectsArr[$element[0]][] = $element;
        }


        foreach ($studentDataOrg as $studentData) {
         
			if($card_serial_no>999999&&$previewPdf!=1){
				echo "<h5>Your card series ended...!</h5>";
				exit;
			}
            //For Custom Loader
            $startTimeLoader =  date('Y-m-d H:i:s');        
			$high_res_bg="PEOPLES_UNIVERSITY_GRADE CARD_BG.jpg"; // 
			$low_res_bg="PEOPLES_UNIVERSITY_GRADE CARD_BG.jpg";
			$pdfBig->AddPage();
			$pdfBig->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');
			// $pdfBig->SetFont($arialNarrowB, '', 8, '', false);
			//set background image
			$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;   

			if($previewPdf==1){
				if($previewWithoutBg!=1){
					$pdfBig->Image($template_img_generate, 0, 0,  '210', '297', "JPG", '', 'R', true);
				}
				$date_font_size = '11';
				$date_nox = 13;
				$date_noy = 35;
				$date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
				// $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
				$pdfBig->SetTextColor(192,192,192);
				$pdfBig->SetXY($date_nox, $date_noy);
				//$pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
				$pdfBig->SetTextColor(0,0,0,100,false,'');
                
				// $pdfBig->SetFont($arialNarrowB, '', 9, '', false);

                $pdf->SetTextColor(192,192,192);
				$pdf->SetXY($date_nox, $date_noy);
				//$pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
				$pdf->SetTextColor(0,0,0,100,false,'');
			}else{
                // $pdfBig->Image($template_img_generate, 0, 0,  '210', '297', "JPG", '', 'R', true);
                $pdf->Image($template_img_generate, 0, 0,  '210', '297', "JPG", '', 'R', true);

            }
			$pdfBig->setPageMark();
            $pdf->setPageMark();
			$ghostImgArr = array();

            // $pdf->SetCreator(PDF_CREATOR);
			// $pdf->SetAuthor('TCPDF');
			// $pdf->SetTitle('Certificate');
			// $pdf->SetSubject('');

			// // remove default header/footer
			// $pdf->setPrintHeader(false);
			// $pdf->setPrintFooter(false);
			// $pdf->SetAutoPageBreak(false, 0);


			// add spot colors
			//$pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
			//$pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

			$pdf->AddPage();        
			$print_serial_no = $this->nextPrintSerial();
			//set background image
			$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$low_res_bg;

			if($previewPdf!=1){
			    $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
			}
			$pdf->setPageMark();
			
            
			$title1_x = 14; 
            $left_str_x = 15; 
			
			$unique_id = trim($studentData[1]);
            $serial_no = trim($studentData[1]);
			$EnrollmentNumber = trim($studentData[1]);
			$candidate_name = trim($studentData[2]);
			$father_name = trim($studentData[3]);
			$mother_name = trim($studentData[4]);
			$course_name = trim($studentData[5]);
			$institute_name = trim($studentData[6]);
			$specialization = trim($studentData[7]);
            $EnrolledSession = trim($studentData[8]);
            $Exam_Result_MonthYear = trim($studentData[9]);
            
            
            $MarksheetNo = trim($studentData[10]);
            

            $final_cgpa = trim($studentData[35]);
            $final_result = trim($studentData[36]);
            $division = trim($studentData[37]);
            $place = trim($studentData[38]);
            $issue_date = trim($studentData[39]);


            $subjectsData=$subjectsArr[$EnrollmentNumber];
            

            //Separate semesters 
            $subjects = array();
            if($subjectsData) {
                foreach ($subjectsData as $element) {
                    $subjects[$element[1]][] = $element;
                }
            }
            ksort($subjects);


            $path=public_path().'\\'.$subdomain[0].'\backend\templates\100\\';
            $file = $studentData[1];  
            if(file_exists($path.$file.".jpg")){
                $profile_path =$path.$file.".jpg";
            }elseif(file_exists($path.$file.".jpeg")){
                $profile_path =$path.$file.".jpeg";
            }
            elseif(file_exists($path.$file.".png")){
                $profile_path =$path.$file.".png";
            }else{
                $profile_path ='';
                
            }
            if(file_exists($profile_path)){
               
                $profilex = 175;
                $profiley = 29;
                $profileWidth = 16;
                $profileHeight = 16;
                $pdfBig->image($profile_path,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
                $pdfBig->setPageMark();

                $pdf->image($profile_path,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
                $pdf->setPageMark();
            }

            // pdfbig start
            
            // Left Side Header
            $headerHeight = 3.5;
            $pdfBig->setFontStretching(90);
            
            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY(17, 63.5);
            $pdfBig->MultiCell(30, $headerHeight, 'ENROLLMENT NO.', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY(46, 63.5);
            $pdfBig->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 8, '', false);
            $pdfBig->SetXY(48, 63.5);
            $pdfBig->MultiCell(55, $headerHeight, $EnrollmentNumber, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->ln();

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY(17, $pdfBig->getY() );
            $pdfBig->MultiCell(30, $headerHeight, 'NAME OF STUDENT', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY(46, $pdfBig->getY());
            $pdfBig->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 8, '', false);
            $pdfBig->SetXY(48, $pdfBig->getY());
            $pdfBig->MultiCell(55, $headerHeight, $candidate_name, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->ln();
            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY(17, $pdfBig->getY());
            $pdfBig->MultiCell(30, $headerHeight, "FATHER'S NAME", 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY(46, $pdfBig->getY());
            $pdfBig->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 8, '', false);
            $pdfBig->SetXY(48, $pdfBig->getY());
            $pdfBig->MultiCell(55, $headerHeight, $father_name, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->ln();
            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY(17, $pdfBig->getY());
            $pdfBig->MultiCell(30, $headerHeight, "MOTHER'S NAME", 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY(46, $pdfBig->getY());
            $pdfBig->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 8, '', false);
            $pdfBig->SetXY(48, $pdfBig->getY());
            $pdfBig->MultiCell(55, $headerHeight, $mother_name, 0, 'L', 0, 0, '', '', true, 0, true);


            // Right Side Header
            $rightX1 = 102;
            $rightX2 = 140;
            $rightX3 = 142;
            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY($rightX1, 63.5);
            $pdfBig->MultiCell(44, $headerHeight, 'PROGRAM', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY($rightX2, 63.5);
            $pdfBig->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 8, '', false);
            $pdfBig->SetXY($rightX3, 63.5);
            $pdfBig->MultiCell(55, $headerHeight, $course_name, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->ln();

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY($rightX1, $pdfBig->getY() );
            $pdfBig->MultiCell(44, $headerHeight, 'SPECIALIZATION', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY($rightX2, $pdfBig->getY() );
            $pdfBig->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 8, '', false);
            $pdfBig->SetXY($rightX3, $pdfBig->getY() );
            $pdfBig->MultiCell(55, $headerHeight, $specialization, 0, 'L', 0, 0, '', '', true, 0, true);


            $pdfBig->ln();

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY($rightX1, $pdfBig->getY() );
            $pdfBig->MultiCell(44, $headerHeight, 'PASSOUT MONTH & YEAR', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY($rightX2, $pdfBig->getY() );
            $pdfBig->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 8, '', false);
            $pdfBig->SetXY($rightX3, $pdfBig->getY() );
            $pdfBig->MultiCell(55, $headerHeight, $Exam_Result_MonthYear, 0, 'L', 0, 0, '', '', true, 0, true);


            
            $pdfBig->ln();
            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY($rightX1, $pdfBig->getY() );
            $pdfBig->MultiCell(44, $headerHeight, 'INSTITUTE', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 8, '', false);
            $pdfBig->SetXY($rightX2 - 15, $pdfBig->getY() );
            $pdfBig->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 8, '', false);
            $pdfBig->SetXY($rightX3 - 15, $pdfBig->getY() );
            $pdfBig->MultiCell(67, $headerHeight, $institute_name, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->Ln();

            $IndividualDataIndex = 11; 
            $tableY = $pdfBig->GetY(); 
            // $tableY = $pdfBig->GetY() +1; 
            $tableYV1 = $pdfBig->GetY(); 
            // $tableYV1 = $pdfBig->GetY() +1; 


            $tableTermI = 0;
            $tableTermII = 0;
            $tableTermIII = 0;
            $tableTermIV = 0;
            $tableTermV = 0;
            $tableTermVI = 0;


            foreach($subjects as $term => $term_array){
            
                if($term == 'I' || $term == 'III' || $term == 'V') {

                    if($term == 'III') {
                        if($tableTermI > $tableTermII) {
                            $tableYV1 =  $tableTermI;
                        }else if($tableTermI < $tableTermII) {
                            $tableYV1 =  $tableTermII;
                        }
                    } else if($term == 'V') {
                        if($tableTermIII > $tableTermIV) {
                            $tableYV1 =  $tableTermIII;
                        }else if($tableTermIII < $tableTermIV) {
                            $tableYV1 =  $tableTermIV;
                        }
                    }
                    $pdfBig->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
                    $pdfBig->SetFont($timesb, '', 7, '', false);
                    $pdfBig->SetXY(17, $tableY);
                    $pdfBig->MultiCell(29, 3, 'SEMESTER-'.$term, 'LT', 'L', 0, 0, '', '', true, 0, true);
    
                    $pdfBig->SetFont($timesb, '', 7, '', false);
                    $pdfBig->SetXY(46, $tableY);
                    $pdfBig->MultiCell(29, 3, 'MYOP : '.trim($studentData[$IndividualDataIndex]), 'LT', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
    
                    $pdfBig->SetFont($timesb, '', 7, '', false);
                    $pdfBig->SetXY(75, $tableY);
                    $pdfBig->MultiCell(30, 3, 'STATUS : '.trim($studentData[$IndividualDataIndex]), 'LTR', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
                    $pdfBig->Ln();
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY(17, $pdfBig->GetY());
                    $pdfBig->setCellHeightRatio(0.9);
                    $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
                    $pdfBig->MultiCell(13, 6, 'PAPER CODE', 'LTR', 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->setCellHeightRatio(1);
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
                    $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                    $pdfBig->MultiCell(39, 6, 'NAME OF PAPER', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($allotedCrX = $pdfBig->GetX(), $gradeY = $pdfBig->GetY());
                    $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                    $pdfBig->MultiCell(25, 3, 'CREDITS', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($allotedCrX, $pdfBig->GetY() + 3);
                    $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                    $pdfBig->MultiCell(13, 3, 'ALLOTED', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
                    $pdfBig->MultiCell(12, 3, 'EARNED', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($pdfBig->GetX(), $gradeY);
                    $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                    $pdfBig->MultiCell(11, 6, 'GRADE', 'LTR', 'C', 0, 0, '', '', true, 0, true);

                    $pdfBig->Ln();
    
                    $pdfBig->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
    
    
                    $SecondTableY = $pdfBig->GetY(); 
                    
                    foreach($term_array as $key => $val){
    
                        
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY(17, $SecondTableY);
                        $pdfBig->setCellHeightRatio(0.9);
                        $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        $pdfBig->MultiCell(13, 6, $val[2], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
                        $pdfBig->setCellHeightRatio(1);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY);

                        // store current object
                        $pdfBig->startTransaction();
                        // get the number of lines
                        $lines = $pdfBig->MultiCell(35, 0, $val[3], 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                        $pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

                        if($lines>1){
                            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
                        }else{
                            $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        }

                        
                        $pdfBig->MultiCell(35, 6, $val[3], 'LTRB', 'L', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($belowtableX = $pdfBig->GetX(), $SecondTableY);
                        $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdfBig->MultiCell(4, 3,$val[4], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY);
                        $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdfBig->MultiCell(13, 3, $val[5], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY);
                        $pdfBig->MultiCell(12, 3, $val[6], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY);
                        // $pdfBig->setCellPaddings($left = '', $top = '1.8', $right = '', $bottom = '');
                        $pdfBig->MultiCell(11, 3, $val[7], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($belowtableX, $SecondTableY+3);
                        $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdfBig->MultiCell(4, 3,$val[8], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY+3);
                        $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdfBig->MultiCell(13, 3, $val[9], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY+3);
                        $pdfBig->MultiCell(12, 3, $val[10], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY+3);
                        // $pdfBig->setCellPaddings($left = '', $top = '1.8', $right = '', $bottom = '');
                        $pdfBig->MultiCell(11, 3, $val[11], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
    
                        $pdfBig->Ln();
                        $SecondTableY = $pdfBig->GetY(); 
                        // $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                        
    
                    }

                    $pdfBig->setCellPaddings($left = '', $top = '0.3', $right = '', $bottom = '');
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY(17, $pdfBig->GetY());
                    $pdfBig->MultiCell(60, 3, 'TEC : '.trim($studentData[$IndividualDataIndex]), 'LTB', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 

                    $pdfBig->setCellPaddings($left = '', $top = '0.3', $right = '', $bottom = '');
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
                    $pdfBig->MultiCell(28, 3, 'SGPA :'.trim($studentData[$IndividualDataIndex]), 'TRB', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
                    $pdfBig->Ln();
                    $tableY = $pdfBig->GetY();
                    $pdfBig->setCellPaddings($left = '', $top = '0.1', $right = '', $bottom = '');
                    if($term == 'I') {
                        $tableTermI = $tableY;
                    } else if($term == 'III') {
                        $tableTermIII = $tableY;
                    } else if($term == 'V') {
                        $tableTermV = $tableY;
                    }
                } else if($term == 'II' || $term == 'IV' || $term == 'VI') {

                    if($term == 'IV') {
                        if($tableTermI > $tableTermII) {
                            $tableYV1 =  $tableTermI;
                        }else if($tableTermI < $tableTermII) {
                            $tableYV1 =  $tableTermII;
                        }
                    } else if($term == 'VI') {
                        if($tableTermIII > $tableTermIV) {
                            $tableYV1 =  $tableTermIII;
                        }else if($tableTermIII < $tableTermIV) {
                            $tableYV1 =  $tableTermIV;
                        }
                    }
                    if($term == 'II') {
                        $pdfBig->setCellPaddings($left = '', $top = '0.4', $right = '', $bottom = '0.3');    
                    } else {
                        $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                    }
                    $pdfBig->SetFont($timesb, '', 7, '', false);
                    $pdfBig->SetXY(105, $tableYV1);
                    $pdfBig->MultiCell(29, 3, 'SEMESTER-'.$term, 'LT', 'L', 0, 0, '', '', true, 0, true);
    
                    $pdfBig->SetFont($timesb, '', 7, '', false);
                    $pdfBig->SetXY(134, $tableYV1);
                    $pdfBig->MultiCell(29, 3, 'MYOP : '.trim($studentData[$IndividualDataIndex]), 'LT', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
    
                    $pdfBig->SetFont($timesb, '', 7, '', false);
                    $pdfBig->SetXY(163, $tableYV1);
                    $pdfBig->MultiCell(30, 3, 'STATUS : '.trim($studentData[$IndividualDataIndex]), 'LTR', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
                    $pdfBig->Ln();
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY(105, $pdfBig->GetY());
                    $pdfBig->setCellHeightRatio(0.9);
                    $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
                    $pdfBig->MultiCell(13, 6, 'PAPER CODE', 'LTR', 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->setCellHeightRatio(1);
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
                    $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                    $pdfBig->MultiCell(39, 6, 'NAME OF PAPER', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($allotedCrX = $pdfBig->GetX(), $gradeY = $pdfBig->GetY());
                    $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                    $pdfBig->MultiCell(25, 3, 'CREDITS', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($allotedCrX, $pdfBig->GetY() + 3);
                    $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                    $pdfBig->MultiCell(13, 3, 'ALLOTED', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
                    $pdfBig->MultiCell(12, 3, 'EARNED', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($pdfBig->GetX(), $gradeY);
                    $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                    $pdfBig->MultiCell(11, 6, 'GRADE', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
    
                    $pdfBig->Ln();
    
                    $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '');
    
    
                    $SecondTableY = $pdfBig->GetY(); 
                    
                    foreach($term_array as $key => $val){
    
                        
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY(105, $SecondTableY);
                        $pdfBig->setCellHeightRatio(0.9);
                        $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        $pdfBig->MultiCell(13, 6, $val[2], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
                        $pdfBig->setCellHeightRatio(1);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY);

                        // store current object
                        $pdfBig->startTransaction();
                        // get the number of lines
                        $lines = $pdfBig->MultiCell(35, 0, $val[3], 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                        $pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

                        if($lines>1){
                            $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
                        }else{
                            $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        }

                        // $pdfBig->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        $pdfBig->MultiCell(35, 6, $val[3], 'LTRB', 'L', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($belowtableX = $pdfBig->GetX(), $SecondTableY);
                        $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdfBig->MultiCell(4, 3,$val[4], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY);
                        $pdfBig->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdfBig->MultiCell(13, 3, $val[5], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY);
                        $pdfBig->MultiCell(12, 3, $val[6], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY);
                        // $pdfBig->setCellPaddings($left = '', $top = '1.8', $right = '', $bottom = '');
                        $pdfBig->MultiCell(11, 3, $val[7], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($belowtableX, $SecondTableY+3);
                        $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdfBig->MultiCell(4, 3,$val[8], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY+3);
                        $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdfBig->MultiCell(13, 3, $val[9], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY+3);
                        $pdfBig->MultiCell(12, 3, $val[10], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        
                        $pdfBig->SetFont($times, '', 7, '', false);
                        $pdfBig->SetXY($pdfBig->GetX(), $SecondTableY+3);
                        // $pdfBig->setCellPaddings($left = '', $top = '1.8', $right = '', $bottom = '');
                        $pdfBig->MultiCell(11, 3, $val[11], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
    
                        $pdfBig->Ln();
                        $SecondTableY = $pdfBig->GetY(); 
                        // $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                        
    
                    }

                    $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY(105, $pdfBig->GetY());
                    $pdfBig->MultiCell(60, 3, 'TEC : '.trim($studentData[$IndividualDataIndex]), 'LTB', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 

                    $pdfBig->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                    $pdfBig->SetFont($times, '', 7, '', false);
                    $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
                    $pdfBig->MultiCell(28, 3, 'SGPA :'.trim($studentData[$IndividualDataIndex]), 'TRB', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
                    $pdfBig->Ln();

                    $tableYV1 = $pdfBig->GetY();

                    if($term == 'II') {
                        $tableTermII = $tableYV1;
                    } else if($term == 'IV') {
                        $tableTermIV = $tableYV1;
                    } else if($term == 'VI') {
                        $tableTermVI = $tableYV1;
                    }

                }

                

            }

            
            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(17, 244);
            $pdfBig->MultiCell(160, 3, 'ABBREVIATIONS :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; TEC  =  Total Earned Credit &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MYOP = Month & Year of Passing', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(17, 247);
            $pdfBig->MultiCell(160, 3, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SGPA  =  Semester Grade Point Average &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CGPA = Cumulative Grade Point Average', 0, 'L', 0, 0, '', '', true, 0, true);



            
            $pdfBig->setCellPaddings($left = '', $top = '0.4', $right = '', $bottom = '');
            $pdfBig->SetFont($times, '', 7, '', false);
            $pdfBig->SetXY(153, 244);
            $pdfBig->MultiCell(40, 3, 'OVERALL', 1, 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->Ln();
            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(153, $pdfBig->GetY());
            $pdfBig->MultiCell(20, 3.5, 'CGPA', 1, 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 7, '', false);
            $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
            $pdfBig->MultiCell(20, 3.5, trim($studentData[35]), 1, 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->Ln();
            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(153, $pdfBig->GetY());
            $pdfBig->MultiCell(20, 3.5, 'RESULT', 1, 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 7, '', false);
            $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
            $pdfBig->MultiCell(20, 3.5, trim($studentData[36]), 1, 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->Ln();

            // store current object
            $pdfBig->startTransaction();
            $pdfBig->SetFont($timesb, '', 7, '', false);  
            // get the number of lines
            $lines = $pdfBig->MultiCell(20, 0, trim($division), 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
            $pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

            if($lines>1){
                $height = 5.5;
            }else{
                $height = 3.5;
            }

            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(153, $pdfBig->GetY());
            $pdfBig->MultiCell(20, $height, 'DIVISION', 1, 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($times, '', 7, '', false);
            $pdfBig->SetXY($pdfBig->GetX(), $pdfBig->GetY());
            $pdfBig->MultiCell(20, $height, trim($studentData[37]), 1, 'C', 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings($left = '', $top = '0.5', $right = '', $bottom = '');


            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(17, 253);
            $pdfBig->MultiCell(20, 0, 'PREPARED BY:', 0, 'L', 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(58, 253);
            $pdfBig->MultiCell(20, 0, 'CHECKED BY:', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(74, 256);
            $pdfBig->MultiCell(40, 0, '1.', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(74, 259);
            $pdfBig->MultiCell(40, 0, '2.', 0, 'L', 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(105, 258.5);
            $pdfBig->MultiCell(40, 0, 'CONTROLLER OF EXAMINATIONS', 0, 'L', 0, 0, '', '', true, 0, true);



            $pdfBig->SetFont($timesb, '', 7, '', false);
            $pdfBig->SetXY(17, 264.5);
            $pdfBig->MultiCell(70, 0, 'ISSUE DATE : '.$issue_date.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; PLACE : '.$place, 0, 'L', 0, 0, '', '', true, 0, true);


            // pdfbig end



            // Live generation pdf code
            // pdf start

            // Left Side Header
            $headerHeight = 3.5;
            $pdf->setFontStretching(90);
            
            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY(17, 64);
            $pdf->MultiCell(30, $headerHeight, 'ENROLLMENT NO.', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY(46, 64);
            $pdf->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 8, '', false);
            $pdf->SetXY(48, 64);
            $pdf->MultiCell(55, $headerHeight, $EnrollmentNumber, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->ln();

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY(17, $pdf->getY() );
            $pdf->MultiCell(30, $headerHeight, 'NAME OF STUDENT', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY(46, $pdf->getY());
            $pdf->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 8, '', false);
            $pdf->SetXY(48, $pdf->getY());
            $pdf->MultiCell(55, $headerHeight, $candidate_name, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->ln();
            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY(17, $pdf->getY());
            $pdf->MultiCell(30, $headerHeight, "FATHER'S NAME", 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY(46, $pdf->getY());
            $pdf->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 8, '', false);
            $pdf->SetXY(48, $pdf->getY());
            $pdf->MultiCell(55, $headerHeight, $father_name, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->ln();
            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY(17, $pdf->getY());
            $pdf->MultiCell(30, $headerHeight, "MOTHER'S NAME", 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY(46, $pdf->getY());
            $pdf->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 8, '', false);
            $pdf->SetXY(48, $pdf->getY());
            $pdf->MultiCell(55, $headerHeight, $mother_name, 0, 'L', 0, 0, '', '', true, 0, true);


            // Right Side Header
            $rightX1 = 102;
            $rightX2 = 140;
            $rightX3 = 142;
            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY($rightX1, 64);
            $pdf->MultiCell(44, $headerHeight, 'PROGRAM', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY($rightX2, 64);
            $pdf->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 8, '', false);
            $pdf->SetXY($rightX3, 64);
            $pdf->MultiCell(55, $headerHeight, $course_name, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->ln();

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY($rightX1, $pdf->getY() );
            $pdf->MultiCell(44, $headerHeight, 'SPECIALIZATION', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY($rightX2, $pdf->getY() );
            $pdf->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 8, '', false);
            $pdf->SetXY($rightX3, $pdf->getY() );
            $pdf->MultiCell(55, $headerHeight, $specialization, 0, 'L', 0, 0, '', '', true, 0, true);


            $pdf->ln();

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY($rightX1, $pdf->getY() );
            $pdf->MultiCell(44, $headerHeight, 'PASSOUT MONTH & YEAR', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY($rightX2, $pdf->getY() );
            $pdf->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 8, '', false);
            $pdf->SetXY($rightX3, $pdf->getY() );
            $pdf->MultiCell(55, $headerHeight, $Exam_Result_MonthYear, 0, 'L', 0, 0, '', '', true, 0, true);


            
            $pdf->ln();
            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY($rightX1, $pdf->getY() );
            $pdf->MultiCell(44, $headerHeight, 'INSTITUTE', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 8, '', false);
            $pdf->SetXY($rightX2 - 15, $pdf->getY() );
            $pdf->MultiCell(4, $headerHeight, ':', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 8, '', false);
            $pdf->SetXY($rightX3 - 15, $pdf->getY() );
            $pdf->MultiCell(67, $headerHeight, $institute_name, 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->Ln();

            $IndividualDataIndex = 11; 
            $tableY = $pdf->GetY(); 
            // $tableY = $pdf->GetY() +1; 
            // $tableYV1 = $pdf->GetY() +1; 
            $tableYV1 = $pdf->GetY(); 


            $tableTermI = 0;
            $tableTermII = 0;
            $tableTermIII = 0;
            $tableTermIV = 0;
            $tableTermV = 0;
            $tableTermVI = 0;


            foreach($subjects as $term => $term_array){
            
                if($term == 'I' || $term == 'III' || $term == 'V') {

                    if($term == 'III') {
                        if($tableTermI > $tableTermII) {
                            $tableYV1 =  $tableTermI;
                        }else if($tableTermI < $tableTermII) {
                            $tableYV1 =  $tableTermII;
                        }
                    } else if($term == 'V') {
                        if($tableTermIII > $tableTermIV) {
                            $tableYV1 =  $tableTermIII;
                        }else if($tableTermIII < $tableTermIV) {
                            $tableYV1 =  $tableTermIV;
                        }
                    }
                    $pdf->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(17, $tableY);
                    $pdf->MultiCell(29, 3, 'SEMESTER-'.$term, 'LT', 'L', 0, 0, '', '', true, 0, true);
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(46, $tableY);
                    $pdf->MultiCell(29, 3, 'MYOP : '.trim($studentData[$IndividualDataIndex]), 'LT', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(75, $tableY);
                    $pdf->MultiCell(30, 3, 'STATUS : '.trim($studentData[$IndividualDataIndex]), 'LTR', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
                    $pdf->Ln();
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(17, $pdf->GetY());
                    $pdf->setCellHeightRatio(0.9);
                    $pdf->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
                    $pdf->MultiCell(13, 6, 'PAPER CODE', 'LTR', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->setCellHeightRatio(1);
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($pdf->GetX(), $pdf->GetY());
                    $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                    $pdf->MultiCell(39, 6, 'NAME OF PAPER', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($allotedCrX = $pdf->GetX(), $gradeY = $pdf->GetY());
                    $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                    $pdf->MultiCell(25, 3, 'CREDITS', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($allotedCrX, $pdf->GetY() + 3);
                    $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                    $pdf->MultiCell(13, 3, 'ALLOTED', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($pdf->GetX(), $pdf->GetY());
                    $pdf->MultiCell(12, 3, 'EARNED', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($pdf->GetX(), $gradeY);
                    $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                    $pdf->MultiCell(11, 6, 'GRADE', 'LTR', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->Ln();
    
                    $pdf->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
    
    
                    $SecondTableY = $pdf->GetY(); 
                    
                    foreach($term_array as $key => $val){
    
                        
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY(17, $SecondTableY);
                        $pdf->setCellHeightRatio(0.9);
                        $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        $pdf->MultiCell(13, 6, $val[2], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
                        $pdf->setCellHeightRatio(1);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY);

                        // store current object
                        $pdf->startTransaction();
                        // get the number of lines
                        $lines = $pdf->MultiCell(35, 0, $val[3], 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                        $pdf=$pdf->rollbackTransaction(); // restore previous object

                        if($lines>1){
                            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
                        }else{
                            $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        }

                        
                        $pdf->MultiCell(35, 6, $val[3], 'LTRB', 'L', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($belowtableX = $pdf->GetX(), $SecondTableY);
                        $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdf->MultiCell(4, 3,$val[4], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY);
                        $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdf->MultiCell(13, 3, $val[5], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY);
                        $pdf->MultiCell(12, 3, $val[6], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY);
                        // $pdf->setCellPaddings($left = '', $top = '1.8', $right = '', $bottom = '');
                        $pdf->MultiCell(11, 3, $val[7], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($belowtableX, $SecondTableY+3);
                        $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdf->MultiCell(4, 3,$val[8], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY+3);
                        $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdf->MultiCell(13, 3, $val[9], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY+3);
                        $pdf->MultiCell(12, 3, $val[10], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY+3);
                        // $pdf->setCellPaddings($left = '', $top = '1.8', $right = '', $bottom = '');
                        $pdf->MultiCell(11, 3, $val[11], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
    
                        $pdf->Ln();
                        $SecondTableY = $pdf->GetY(); 
                        // $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                        
    
                    }

                    $pdf->setCellPaddings($left = '', $top = '0.3', $right = '', $bottom = '');
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(17, $pdf->GetY());
                    $pdf->MultiCell(60, 3, 'TEC : '.trim($studentData[$IndividualDataIndex]), 'LTB', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 

                    $pdf->setCellPaddings($left = '', $top = '0.3', $right = '', $bottom = '');
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($pdf->GetX(), $pdf->GetY());
                    $pdf->MultiCell(28, 3, 'SGPA :'.trim($studentData[$IndividualDataIndex]), 'TRB', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
                    $pdf->Ln();
                    $tableY = $pdf->GetY();
                    $pdf->setCellPaddings($left = '', $top = '0.1', $right = '', $bottom = '');
                    if($term == 'I') {
                        $tableTermI = $tableY;
                    } else if($term == 'III') {
                        $tableTermIII = $tableY;
                    } else if($term == 'V') {
                        $tableTermV = $tableY;
                    }
                } else if($term == 'II' || $term == 'IV' || $term == 'VI') {

                    if($term == 'IV') {
                        if($tableTermI > $tableTermII) {
                            $tableYV1 =  $tableTermI;
                        }else if($tableTermI < $tableTermII) {
                            $tableYV1 =  $tableTermII;
                        }
                    } else if($term == 'VI') {
                        if($tableTermIII > $tableTermIV) {
                            $tableYV1 =  $tableTermIII;
                        }else if($tableTermIII < $tableTermIV) {
                            $tableYV1 =  $tableTermIV;
                        }
                    }
                    if($term == 'II') {
                        $pdf->setCellPaddings($left = '', $top = '0.4', $right = '', $bottom = '0.3');    
                    } else {
                        $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                    }
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(105, $tableYV1);
                    $pdf->MultiCell(29, 3, 'SEMESTER-'.$term, 'LT', 'L', 0, 0, '', '', true, 0, true);
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(134, $tableYV1);
                    $pdf->MultiCell(29, 3, 'MYOP : '.trim($studentData[$IndividualDataIndex]), 'LT', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(163, $tableYV1);
                    $pdf->MultiCell(30, 3, 'STATUS : '.trim($studentData[$IndividualDataIndex]), 'LTR', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
                    $pdf->Ln();
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(105, $pdf->GetY());
                    $pdf->setCellHeightRatio(0.9);
                    $pdf->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
                    $pdf->MultiCell(13, 6, 'PAPER CODE', 'LTR', 'C', 0, 0, '', '', true, 0, true);
                    $pdf->setCellHeightRatio(1);
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($pdf->GetX(), $pdf->GetY());
                    $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                    $pdf->MultiCell(39, 6, 'NAME OF PAPER', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($allotedCrX = $pdf->GetX(), $gradeY = $pdf->GetY());
                    $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                    $pdf->MultiCell(25, 3, 'CREDITS', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($allotedCrX, $pdf->GetY() + 3);
                    $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                    $pdf->MultiCell(13, 3, 'ALLOTED', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($pdf->GetX(), $pdf->GetY());
                    $pdf->MultiCell(12, 3, 'EARNED', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
                    
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($pdf->GetX(), $gradeY);
                    $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                    $pdf->MultiCell(11, 6, 'GRADE', 'LTR', 'C', 0, 0, '', '', true, 0, true);
    
    
                    $pdf->Ln();
    
                    $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '');
    
    
                    $SecondTableY = $pdf->GetY(); 
                    
                    foreach($term_array as $key => $val){
    
                        
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY(105, $SecondTableY);
                        $pdf->setCellHeightRatio(0.9);
                        $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        $pdf->MultiCell(13, 6, $val[2], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
                        $pdf->setCellHeightRatio(1);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY);

                        // store current object
                        $pdf->startTransaction();
                        // get the number of lines
                        $lines = $pdf->MultiCell(35, 0, $val[3], 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                        $pdf=$pdf->rollbackTransaction(); // restore previous object

                        if($lines>1){
                            $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
                        }else{
                            $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        }

                        // $pdf->setCellPaddings( $left = '', $top = '1.5', $right = '', $bottom = '');
                        $pdf->MultiCell(35, 6, $val[3], 'LTRB', 'L', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($belowtableX = $pdf->GetX(), $SecondTableY);
                        $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdf->MultiCell(4, 3,$val[4], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY);
                        $pdf->setCellPaddings( $left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdf->MultiCell(13, 3, $val[5], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY);
                        $pdf->MultiCell(12, 3, $val[6], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY);
                        // $pdf->setCellPaddings($left = '', $top = '1.8', $right = '', $bottom = '');
                        $pdf->MultiCell(11, 3, $val[7], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($belowtableX, $SecondTableY+3);
                        $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdf->MultiCell(4, 3,$val[8], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY+3);
                        $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '0');
                        $pdf->MultiCell(13, 3, $val[9], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY+3);
                        $pdf->MultiCell(12, 3, $val[10], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
                        
                        $pdf->SetFont($times, '', 7, '', false);
                        $pdf->SetXY($pdf->GetX(), $SecondTableY+3);
                        // $pdf->setCellPaddings($left = '', $top = '1.8', $right = '', $bottom = '');
                        $pdf->MultiCell(11, 3, $val[11], 'LTRB', 'C', 0, 0, '', '', true, 0, true);
    
    
                        $pdf->Ln();
                        $SecondTableY = $pdf->GetY(); 
                        // $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                        
    
                    }

                    $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY(105, $pdf->GetY());
                    $pdf->MultiCell(60, 3, 'TEC : '.trim($studentData[$IndividualDataIndex]), 'LTB', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 

                    $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
                    $pdf->SetFont($times, '', 7, '', false);
                    $pdf->SetXY($pdf->GetX(), $pdf->GetY());
                    $pdf->MultiCell(28, 3, 'SGPA :'.trim($studentData[$IndividualDataIndex]), 'TRB', 'L', 0, 0, '', '', true, 0, true);
                    $IndividualDataIndex++; 
                    $pdf->Ln();

                    $tableYV1 = $pdf->GetY();

                    if($term == 'II') {
                        $tableTermII = $tableYV1;
                    } else if($term == 'IV') {
                        $tableTermIV = $tableYV1;
                    } else if($term == 'VI') {
                        $tableTermVI = $tableYV1;
                    }

                }

                

            }


            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(17, 244);
            $pdf->MultiCell(160, 3, 'ABBREVIATIONS :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; TEC  =  Total Earned Credit &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MYOP = Month & Year of Passing', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(17, 247);
            $pdf->MultiCell(160, 3, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SGPA  =  Semester Grade Point Average &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CGPA = Cumulative Grade Point Average', 0, 'L', 0, 0, '', '', true, 0, true);

            
            $pdf->setCellPaddings($left = '', $top = '0.4', $right = '', $bottom = '');
            $pdf->SetFont($times, '', 7, '', false);
            $pdf->SetXY(153, 244);
            $pdf->MultiCell(40, 3, 'OVERALL', 1, 'C', 0, 0, '', '', true, 0, true);
            $pdf->Ln();
            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(153, $pdf->GetY());
            $pdf->MultiCell(20, 3.5, 'CGPA', 1, 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 7, '', false);
            $pdf->SetXY($pdf->GetX(), $pdf->GetY());
            $pdf->MultiCell(20, 3.5, trim($studentData[35]), 1, 'C', 0, 0, '', '', true, 0, true);

            $pdf->Ln();
            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(153, $pdf->GetY());
            $pdf->MultiCell(20, 3.5, 'RESULT', 1, 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 7, '', false);
            $pdf->SetXY($pdf->GetX(), $pdf->GetY());
            $pdf->MultiCell(20, 3.5, trim($studentData[36]), 1, 'C', 0, 0, '', '', true, 0, true);

            $pdf->Ln();

            // store current object
            $pdf->startTransaction();
            $pdf->SetFont($timesb, '', 7, '', false);  
            // get the number of lines
            $lines = $pdf->MultiCell(20, 0, trim($division), 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
            $pdf=$pdf->rollbackTransaction(); // restore previous object

            if($lines>1){
                $height = 5.5;
            }else{
                $height = 3.5;
            }

            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(153, $pdf->GetY());
            $pdf->MultiCell(20, $height, 'DIVISION', 1, 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($times, '', 7, '', false);
            $pdf->SetXY($pdf->GetX(), $pdf->GetY());
            $pdf->MultiCell(20, $height, trim($studentData[37]), 1, 'C', 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings($left = '', $top = '0.5', $right = '', $bottom = '');


            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(17, 253);
            $pdf->MultiCell(20, 0, 'PREPARED BY:', 0, 'L', 0, 0, '', '', true, 0, true);


            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(58, 253);
            $pdf->MultiCell(20, 0, 'CHECKED BY:', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(74, 256);
            $pdf->MultiCell(40, 0, '1.', 0, 'L', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(74, 259);
            $pdf->MultiCell(40, 0, '2.', 0, 'L', 0, 0, '', '', true, 0, true);


            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(105, 258.5);
            $pdf->MultiCell(40, 0, 'CONTROLLER OF EXAMINATIONS', 0, 'L', 0, 0, '', '', true, 0, true);



            $pdf->SetFont($timesb, '', 7, '', false);
            $pdf->SetXY(17, 264.5);
            $pdf->MultiCell(70, 0, 'ISSUE DATE : '.$issue_date.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; PLACE : '.$place, 0, 'L', 0, 0, '', '', true, 0, true);


            // pdf end
            $pdf->setCellPaddings($left = '', $top = '0.2', $right = '', $bottom = '');
			
            
            // Ghost image
			$nameOrg=$candidate_name;
            $ghost_font_size = '13';
            $ghostImagex = 75;
            $ghostImagey = 268;
            //$ghostImageWidth = 55;//68
            //$ghostImageHeight = 8;
            $ghostImageWidth =55;
            $ghostImageHeight = 9.8;
            // $ghostImageWidth = 39.405983333;
            // $ghostImageHeight = 10;

            // Ghost IMAGE
            $name = substr(str_replace(' ', '', strtoupper($nameOrg)) , 0, 6);
            $tmpDir = $this->createTemp(public_path() . '/backend/images/ghosttemp/temp');

            $w = $this->CreateMessage($tmpDir, $name, $ghost_font_size, '');

            $pdfBig->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
            $pdf->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);



            $serial_no=$GUID=$studentData[1];

            //QR Code    
			$dt = date("_ymdHis");
            $GUID=$unique_id;
			$str=$GUID.$dt;
			$codeContents = strtoupper(md5($str));
			$encryptedString = strtoupper(md5($str));
			$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
			$qrCodex = 20; 
			$qrCodey =27;
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
			
            //1D Barcode
			$style1Da = array(
				'position' => '',
				'align' => 'C',
				'stretch' => true,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'hpadding' => 'auto',
				'vpadding' => 'auto',
				'fgcolor' => array(0,0,0),
				'bgcolor' => false, //array(255,255,255),
				'text' => true,
				'font' => 'helvetica',
				'fontsize' => 9,
				'stretchtext' => 7
			); 			
			$barcodex = 20;
			$barcodey = 268;
 
			$barcodeWidth = 52;
			$barodeHeight = 13;
			$pdf->SetAlpha(1);
			$pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			$pdfBig->SetAlpha(1);
			$pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			
			
            
          

			$str = $nameOrg;
			$str = strtoupper(preg_replace('/\s+/', '', $str)); 			
			$microlinestr=$str;
			// $pdf->SetFont($times, '', 1.3, '', false);
            $pdf->SetFont($times, '', 1.3, '', false);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY(20, 49);      
			$pdf->Cell(20, 0, $microlinestr, 0, false, 'C');    
           
			$pdfBig->SetFont($times, '', 1.3, '', false);
			$pdfBig->SetTextColor(0, 0, 0);
			$pdfBig->SetXY(20, 49);        
			$pdfBig->Cell(20, 0, $microlinestr, 0, false, 'C'); 
			
			if($previewPdf!=1){
				$certName = str_replace("/", "_", $GUID) .".pdf";				
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

				$this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'PeopleConsolidatedMarksheet',$admin_id,$card_serial_no);
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
			$this->updateCardNo('PeopleConsolidatedMarksheet',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
        }
        $msg = '';
        
        $file_name =  str_replace("/", "_",'PeopleConsolidatedMarksheet'.date("Ymdhms")).'.pdf';        
		// $file_name = 'test.pdf';
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $filename = public_path().'/backend/tcpdf/examples/'.$file_name;        
        $pdfBig->output($filename,'F');
        if($previewPdf!=1){
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name="PeopleConsolidatedMarksheet";
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

        /* Server Storage check for already generated pdf and move to inactive folder */
        $storagePath=public_path();
        $file_existes = $storagePath.'/'.$subdomain[0].'/backend/pdf_file/'.$certName;
        if(file_exists($file_existes)){    
        
            if(!is_dir($storagePath.'/'.$subdomain[0].'/backend/pdf_file/Inactive_PDF')){
                mkdir($storagePath.'/'.$subdomain[0].'/backend/pdf_file/Inactive_PDF');
            }
            $student = StudentTable::where('status',1)->where('serial_no',$serial_no)->value('id');
            $inactivePdf = $storagePath.'/'.$subdomain[0].'/backend/pdf_file/Inactive_PDF/'.$student.'_'.$certName;
            
            copy($file_existes, $inactivePdf);
           
        }
        
        /*copy($file1, $file2);        
        $aws_qr = \File::copy($file2,$pdfActualPath); 
        @unlink($file2);*/
		$source=\Config::get('constant.directoryPathBackward')."\\backend\\temp_pdf_file\\".$certName;
		$output=\Config::get('constant.directoryPathBackward').$subdomain[0]."\\backend\\pdf_file\\".$certName; 
		CoreHelper::compressPdfFile($source,$output);	
        @unlink($file1);

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
        
        $result = SbStudentTable::create(['serial_no'=>'T-'.$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'certificate_type'=>'Grade Card']);
        }else{
        $resultu = StudentTable::where('serial_no',''.$serial_no)->update(['status'=>'0']);
        // Insert the new record
        
        $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2,'certificate_type'=>'Grade Card']);
        }
        
    }
    
    public function testUpload($certName, $pdfActualPath)
    {
        // FTP server details
        $ftpHost = \Config::get('constant.monad_ftp_host');
        $ftpPort = \Config::get('constant.monad_ftp_port');
        $ftpUsername = \Config::get('constant.monad_ftp_username');
        $ftpPassword = \Config::get('constant.monad_ftp_pass');        
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

    
  
}
