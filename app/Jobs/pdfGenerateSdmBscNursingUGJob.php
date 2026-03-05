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
class pdfGenerateSdmBscNursingUGJob{
    
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
        // dd($pdf_data);
        $studentDataOrg=$pdf_data['studentDataOrg'];
        $SGPA = $pdf_data['SGPA'];
        $CGPA = $pdf_data['CGPA'];
        $Total_Credits = $pdf_data['Total_Credits'];
        $Total_Credit_Point = $pdf_data['Total_Credit_Point'];
        $Credit_Ex_Subj = $pdf_data['Credit_Ex_Subj'];
        $Total_Ex_Credit = $pdf_data['Total_Ex_Credit'];
        $Total_Cp_Earned = $pdf_data['Total_Cp_Earned'];
        $Exam_Result = $pdf_data['Exam_Result'];
        $CGPA = $pdf_data['CGPA'];
        $classGrade = $pdf_data['class_grade'];
		$Date_key=$pdf_data['Date_key'];
		$Student_image_key=$pdf_data['Student_image_key'];
		$Batch_of_key=$pdf_data['Batch_of_key'];
		$Aadhar_No_key=$pdf_data['Aadhar_No_key'];
		$DOB_key=$pdf_data['DOB_key'];
		$course_key=$pdf_data['course_key'];
		$subj_col= $pdf_data['subj_col'];
		$subj_start=$pdf_data['subj_start'];
		$subj_end=$pdf_data['subj_end'];
        $template_id=$pdf_data['template_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
        $photo_col=$Student_image_key;
        
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
        $pdfBig->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        
        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
		$times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $verdana = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\verdana.ttf', 'TrueTypeUnicode', '', 96);

        $MyriadProRegular = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MyriadProRegular.ttf', 'TrueTypeUnicode', '', 96);
        $MyriadProBold = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MyriadProBold.ttf', 'TrueTypeUnicode', '', 96);
        $MyriadProItalic = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MyriadProItalic.ttf', 'TrueTypeUnicode', '', 96);
        $MyriadProBoldItalic = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MyriadProBoldItalic.ttf', 'TrueTypeUnicode', '', 96);
        $MinionProRegular = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MinionProRegular.ttf', 'TrueTypeUnicode', '', 96);
        $MinionProBold = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MinionProBold.ttf', 'TrueTypeUnicode', '', 96);

        $preview_serial_no=1;
		//$card_serial_no="";
        $log_serial_no = 1;
        $cardDetails=$this->getNextCardNo('sdmBSCNursing');
        $card_serial_no=$cardDetails->next_serial_no;
        $generated_documents=0;  //for custom loader
        foreach ($studentDataOrg as $studentData) {
         
			if($card_serial_no>999999&&$previewPdf!=1){
				echo "<h5>Your card series ended...!</h5>";
				exit;
			}
            //For Custom Loader
            $startTimeLoader =  date('Y-m-d H:i:s');        
			$high_res_bg="SDM_University_BG.jpg"; // SDM_University_BG.jpg, SDM_University.jpg
			$low_res_bg="SDM_University_BG.jpg";

			$pdfBig->AddPage();
			$pdfBig->SetFont($arialNarrowB, '', 8, '', false);            
            
            // Start of PDF
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
            $pdf->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);

            $pdf->AddPage();
            $pdf->SetFont($arialNarrowB, '', 8, '', false);

			//set background image
			$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;   

			if($previewPdf==1){
				if($previewWithoutBg!=1){
					$pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
				}
				$date_font_size = '11';
				$date_nox = 13;
				$date_noy = 35;
				$date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
				$pdfBig->SetFont($arialb, '', $date_font_size, '', false);
				$pdfBig->SetTextColor(192,192,192);
				$pdfBig->SetXY($date_nox, $date_noy);

                $pdf->SetFont($arialb, '', $date_font_size, '', false);
				$pdf->SetTextColor(192,192,192);
				$pdf->SetXY($date_nox, $date_noy);
				//$pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
				$pdfBig->SetTextColor(0,0,0,100,false,'');
				$pdfBig->SetFont($arialNarrowB, '', 9, '', false);
                $pdf->SetTextColor(0,0,0,100,false,'');
				$pdf->SetFont($arialNarrowB, '', 9, '', false);
			}
			$pdfBig->setPageMark();
            $pdf->setPageMark();

			$ghostImgArr = array();
			$print_serial_no = $this->nextPrintSerial();
			//set background image
			$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$low_res_bg;
			if($previewPdf!=1){
			    $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
			}

            
			 
			//images
			if($studentData[$photo_col]!=''){
				//path of photos
				$profile_path_org = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$studentData[$photo_col];  
				//set profile image   
				$profilex = 15; //169.3
				$profiley = 42.3;
				$profileWidth = 20.3;
				$profileHeight = 25.3;
				$pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);
				$pdfBig->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
				$pdf->setPageMark();
				$pdfBig->setPageMark();
			}
			$title1_x = 14; 
            $left_str_x = 15;
            
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdf->SetTextColor(0, 0, 0);   
			/******Start pdfBig ******/ 
			$pdfBig->SetFont($arialNarrow, '', 2, '', false);			
			$pdfBig->SetXY($left_str_x-3, 85);	
			$pdfBig->MultiCell(185.5, 22, '', 'LRT', 'C', 0, 0);	//Entire Table
			$pdfBig->SetXY(84.9, 96);	
			$pdfBig->MultiCell(57.7, 0, '', 'T', 'L', 0, 0);	//UIT HR Line
			// $pdfBig->SetXY($left_str_x-3, 85);	
			// $pdfBig->MultiCell(180.6, 0, '', 'T', 'C', 0, 0);	//SSUITR HR Line

            // vertical line
			$pdfBig->SetXY(21.5, 85);	
            $pdfBig->MultiCell(15.1, 22, '', 'LR', 'C', 0, 0);  //COURSE CODE
            $pdfBig->MultiCell(35, 22, '', 'LR', 'C', 0, 0);  //TITLE OF THE COURSE           
			$pdfBig->MultiCell(13.1, 22, '', 'LR', 'C', 0, 0); //CREDITS
			$pdfBig->MultiCell(30, 22, '', 'LR', 'C', 0, 0); //INTERNAL ASSESSMENT
            $pdfBig->MultiCell(28, 22, '', 'LR', 'C', 0, 0); //END SEMESTER COLLEGE/UNIVERSITY EXAM
			$pdfBig->MultiCell(10, 22, '', 'LR', 'C', 0, 0); //FINAL MARKS%            
			$pdfBig->MultiCell(11, 22, '', 'LR', 'C', 0, 0); //FINAL MARKS%            
			$pdfBig->MultiCell(10, 22, '', 'LR', 'C', 0, 0); //FINAL MARKS%            
			$pdfBig->MultiCell(10.4, 22, '', 'R', 'C', 0, 0); //LETTER GRADE
            $pdfBig->setCellPaddings( $left = '', $top = '', $right = '', $bottom = '');

			
			$pdfBig->SetFont($MyriadProRegular, '', 7.5, '', false);
			$pdfBig->SetXY(11.5, 90);
			$pdfBig->MultiCell(12, 7, 'SL. NO.', 0, 'L', 0, 0);
            $pdfBig->SetXY(22, 90);
            $pdfBig->MultiCell(15, 7, 'COURSE CODE', 0, 'C', 0, 0);
			$pdfBig->SetXY(36, 90);
			$pdfBig->MultiCell(35, 7, 'TITLE OF THE COURSE', 0, 'C', 0, 0);
			$pdfBig->SetXY(67, 90);
			$pdfBig->MultiCell(20, 7, 'CREDITS', 0, 'C', 0, 0);
			$pdfBig->SetXY(87, 86);
			$pdfBig->MultiCell(27, 7, 'INTERNAL ASSESSMENT', 0, 'C', 0, 0);
            $pdfBig->SetXY(116.3,  86);
			$pdfBig->MultiCell(27, 7, 'END SEMESTER COLLEGE/UNIVERSITY EXAM', 0, 'C', 0, 0);
            $pdfBig->SetXY(142, 90);
			$pdfBig->MultiCell(11, 7, 'FINAL MARKS (%)', 0, 'C', 0, 0);
            $pdfBig->SetXY(153, 90);
			$pdfBig->MultiCell(11, 7, 'LETTER GRADE', 0, 'C', 0, 0);
			$pdfBig->SetXY(164, 90);
			$pdfBig->MultiCell(10, 7, 'GRADE POINT', 0, 'C', 0, 0);
			$pdfBig->SetXY(173, 90);
			$pdfBig->MultiCell(11, 7, 'CREDIT POINT', 0, 'C', 0, 0);
			$pdfBig->SetXY(184, 90);
			$pdfBig->MultiCell(13, 7, 'REMARKS', 0, 'C', 0, 0);

            // vertical line between Marks OBTAINED PASS MARKS OUT OF 
			$pdfBig->SetXY(85, 96);
			$pdfBig->MultiCell(14.8, 11, '', 'R', 'C', 0, 0);
			$pdfBig->MultiCell(30, 11, '', 'R', 'C', 0, 0);

            //fdf
            $pdf->SetFont($arialNarrow, '', 2, '', false);			
			$pdf->SetXY($left_str_x-3, 85);	
			$pdf->MultiCell(185.5, 22, '', 'LRT', 'C', 0, 0);	//Entire Table
			$pdf->SetXY(84.9, 96);	
			$pdf->MultiCell(57.7, 0, '', 'T', 'L', 0, 0);	//UIT HR Line

            // vertical line
			$pdf->SetXY(21.5, 85);	
            $pdf->MultiCell(15.1, 22, '', 'LR', 'C', 0, 0);  //COURSE CODE
            $pdf->MultiCell(35, 22, '', 'LR', 'C', 0, 0);  //TITLE OF THE COURSE           
			$pdf->MultiCell(13.1, 22, '', 'LR', 'C', 0, 0); //CREDITS
			$pdf->MultiCell(30, 22, '', 'LR', 'C', 0, 0); //INTERNAL ASSESSMENT
            $pdf->MultiCell(28, 22, '', 'LR', 'C', 0, 0); //END SEMESTER COLLEGE/UNIVERSITY EXAM
			$pdf->MultiCell(10, 22, '', 'LR', 'C', 0, 0); //FINAL MARKS%            
			$pdf->MultiCell(11, 22, '', 'LR', 'C', 0, 0); //FINAL MARKS%            
			$pdf->MultiCell(10, 22, '', 'LR', 'C', 0, 0); //FINAL MARKS%            
			$pdf->MultiCell(10.4, 22, '', 'R', 'C', 0, 0); //LETTER GRADE
            $pdf->setCellPaddings( $left = '', $top = '', $right = '', $bottom = '');

			
			$pdf->SetFont($MyriadProRegular, '', 7.5, '', false);
			$pdf->SetXY(11.5, 90);
			$pdf->MultiCell(12, 7, 'SL. NO.', 0, 'L', 0, 0);
            $pdf->SetXY(22, 90);
            $pdf->MultiCell(15, 7, 'COURSE CODE', 0, 'C', 0, 0);
			$pdf->SetXY(36, 90);
			$pdf->MultiCell(35, 7, 'TITLE OF THE COURSE', 0, 'C', 0, 0);
			$pdf->SetXY(67, 90);
			$pdf->MultiCell(20, 7, 'CREDITS', 0, 'C', 0, 0);
			$pdf->SetXY(87, 86);
			$pdf->MultiCell(27, 7, 'INTERNAL ASSESSMENT', 0, 'C', 0, 0);
            $pdf->SetXY(116.3,  86);
			$pdf->MultiCell(27, 7, 'END SEMESTER COLLEGE/UNIVERSITY EXAM', 0, 'C', 0, 0);
            $pdf->SetXY(142, 90);
			$pdf->MultiCell(11, 7, 'FINAL MARKS (%)', 0, 'C', 0, 0);
            $pdf->SetXY(153, 90);
			$pdf->MultiCell(11, 7, 'LETTER GRADE', 0, 'C', 0, 0);
			$pdf->SetXY(164, 90);
			$pdf->MultiCell(10, 7, 'GRADE POINT', 0, 'C', 0, 0);
			$pdf->SetXY(173, 90);
			$pdf->MultiCell(11, 7, 'CREDIT POINT', 0, 'C', 0, 0);
			$pdf->SetXY(184, 90);
			$pdf->MultiCell(13, 7, 'REMARKS', 0, 'C', 0, 0);

            // vertical line between Marks OBTAINED PASS MARKS OUT OF 
			$pdf->SetXY(85, 96);
			$pdf->MultiCell(14.8, 11, '', 'R', 'C', 0, 0);
			$pdf->MultiCell(30, 11, '', 'R', 'C', 0, 0);

			$unique_id = trim($studentData[0]);
			$mc_no = trim($studentData[0]);
			$year = trim($studentData[3]);
			$year_examination = strtoupper(trim($studentData[5]));
			$candidate_name = strtoupper(trim($studentData[1]));
			$ureg_no = strtoupper(trim($studentData[2]));
			$specilisation = trim($studentData[4]);
			$total_credits = trim($studentData[$Total_Credits]);
			$total_credit_point = trim($studentData[$Total_Credit_Point]);
			$credit_ex_subj = trim($studentData[$Credit_Ex_Subj]);
			$total_ex_credit = trim($studentData[$Total_Ex_Credit]);
			$total_cp_earned = trim($studentData[$Total_Cp_Earned]);
			$exam_result = trim($studentData[$Exam_Result]);
            $sgpa = trim($studentData[$SGPA]); 
            $cgpa = trim($studentData[$CGPA]); 
            $class_grade = trim($studentData[$classGrade]); 
			$Date = trim($studentData[$Date_key]);
			$batch_of_qr = trim($studentData[$Batch_of_key]);
			$aadhar_number_qr = trim($studentData[$Aadhar_No_key]);
			$DOB = trim($studentData[$DOB_key]);			
			$course = trim($studentData[$course_key]);
            $subjectData = array_slice($studentData, $subj_start, $subj_end);
            $subjectsArr=array_chunk($subjectData, $subj_col); 
            $pdfBig->SetFont($arial, '', 10, '', false); 
            $pdf->SetFont($arial, '', 10, '', false); 
			$QR_Output="Batch Of: $batch_of_qr\nName of the Student: $candidate_name\nApaar Id: $aadhar_number_qr\nCourse: $course\nYear: $year\nDate Of Birth: $DOB";            
			
			$pdfBig->SetFont($MyriadProRegular, '', 7.5, '', false);
			$pdfBig->SetXY(85, 97);
			$pdfBig->MultiCell(14, 0, 'MARKS OBTAINED', 0, 'C', 0, 0); //UA
			$pdfBig->MultiCell(14, 0, 'PASS MARKS OUT OF', 0, 'C', 0, 0);
			$pdfBig->MultiCell(19, 0, 'MARKS OBTAINED', 0, 'C', 0, 0); //IN
			$pdfBig->MultiCell(11, 0, 'PASS MARKS OUT OF', 0, 'C', 0, 0);	

			$pdfBig->SetXY(153, 25); //160, 37
			$pdfBig->SetTextColor(0, 0, 0);
			$pdfBig->SetFont($MyriadProBold, 'B', 9, '', false);	
			$pdfBig->MultiCell(52, 0, 'MC No: ', 0, 'L', 0, 0);
			$pdfBig->SetXY(165, 25);
			$pdfBig->SetFont($MyriadProRegular, '', 9, '', false);	
			$pdfBig->MultiCell(52, 0, $mc_no, 0, 'L', 0, 0);

			$pdfBig->SetXY(153, 30); //160, 37
			$pdfBig->SetTextColor(0, 0, 0);
			$pdfBig->SetFont($MyriadProBold, 'B', 9, '', false);	
			$pdfBig->MultiCell(52, 0, 'APAAR ID: ', 0, 'L', 0, 0);
			$pdfBig->SetXY(169, 30);
			$pdfBig->SetFont($MyriadProRegular, '', 9, '', false);	
			$pdfBig->MultiCell(50, 0, $aadhar_number_qr, 0, 'L', 0, 0);

			$pdfBig->SetFont($MyriadProBold, 'B', 15, '', false);
			$pdfBig->SetTextColor(0, 0, 0);
			$pdfBig->SetXY($title1_x, 45);
			$pdfBig->Cell(188, 0, "STATEMENT OF MARKS", 0, false, 'C');            

						
			$pdfBig->SetFont($MinionProBold, '', 12, '', false);			
			$pdfBig->SetXY($title1_x, 52.5);
			$pdfBig->Cell(188, 0, $specilisation." (BASIC)", 0, false, 'C');				
			$pdfBig->SetFont($MyriadProRegular, '', 12, '', false);
			// $pdfBig->SetXY($title1_x, 58.8);
			// $pdfBig->Cell(188, 0,"(CBCS SCHEME)", 0, false, 'C');				
			$pdfBig->SetXY($title1_x, 58.8);
			$pdfBig->Cell(188, 0, $year, 0, false, 'C');
			$pdfBig->SetFont($MinionProBold, '', 12, '', false);			
			$pdfBig->SetXY($title1_x, 64.8);
			$pdfBig->Cell(188, 0, $year_examination, 0, false, 'C');



			$pdfBig->SetFont($MyriadProItalic, 'I', 12, '', false);
			$pdfBig->SetXY($title1_x, 72);
			$pdfBig->Cell(0, 0, 'Name of the Student:', 0, false, 'L');
			$pdfBig->SetXY($title1_x, 78);
			$pdfBig->Cell(0, 0, "University Reg. No.:", 0, false, 'L');
			$pdfBig->SetXY($title1_x, $left_title3_y);

			$pdfBig->SetFont($MyriadProBoldItalic, 'BI', 12, '', false);
			$pdfBig->SetXY(51, 72);
			$pdfBig->Cell(0, 0, $candidate_name, 0, false, 'L');
			$pdfBig->SetXY(48, 78);
			$pdfBig->Cell(0, 0, $ureg_no, 0, false, 'L');   
            
            //dsfs
            $pdf->SetFont($MyriadProRegular, '', 7.5, '', false);
			$pdf->SetXY(85, 97);
			$pdf->MultiCell(14, 0, 'MARKS OBTAINED', 0, 'C', 0, 0); //UA
			$pdf->MultiCell(14, 0, 'PASS MARKS OUT OF', 0, 'C', 0, 0);
			$pdf->MultiCell(19, 0, 'MARKS OBTAINED', 0, 'C', 0, 0); //IN
			$pdf->MultiCell(11, 0, 'PASS MARKS OUT OF', 0, 'C', 0, 0);	

			$pdf->SetXY(153, 25); //160, 37
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont($MyriadProBold, 'B', 9, '', false);	
			$pdf->MultiCell(52, 0, 'MC No: ', 0, 'L', 0, 0);
			$pdf->SetXY(165, 25);
			$pdf->SetFont($MyriadProRegular, '', 9, '', false);	
			$pdf->MultiCell(52, 0, $mc_no, 0, 'L', 0, 0);

			$pdf->SetXY(153, 30); //160, 37
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont($MyriadProBold, 'B', 9, '', false);	
			$pdf->MultiCell(52, 0, 'APAAR ID: ', 0, 'L', 0, 0);
			$pdf->SetXY(169, 30);
			$pdf->SetFont($MyriadProRegular, '', 9, '', false);	
			$pdf->MultiCell(52, 0, $aadhar_number_qr, 0, 'L', 0, 0);

			$pdf->SetFont($MyriadProBold, 'B', 15, '', false);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($title1_x, 45);
			$pdf->Cell(188, 0, "STATEMENT OF MARKS", 0, false, 'C');            

						
			$pdf->SetFont($MinionProBold, '', 12, '', false);			
			$pdf->SetXY($title1_x, 52.5);
			$pdf->Cell(188, 0, $specilisation." (BASIC)", 0, false, 'C');				
			$pdf->SetFont($MyriadProRegular, '', 12, '', false);
			$pdf->SetXY($title1_x, 58.8);
			$pdf->Cell(188, 0, $year, 0, false, 'C');
			$pdf->SetFont($MinionProBold, '', 12, '', false);			
			$pdf->SetXY($title1_x, 64.8);
			$pdf->Cell(188, 0, $year_examination, 0, false, 'C');



			$pdf->SetFont($MyriadProItalic, 'I', 12, '', false);
			$pdf->SetXY($title1_x, 72);
			$pdf->Cell(0, 0, 'Name of the Student:', 0, false, 'L');
			$pdf->SetXY($title1_x, 78);
			$pdf->Cell(0, 0, "University Reg. No.:", 0, false, 'L');
			$pdf->SetXY($title1_x, $left_title3_y);

			$pdf->SetFont($MyriadProBoldItalic, 'BI', 12, '', false);
			$pdf->SetXY(51, 72);
			$pdf->Cell(0, 0, $candidate_name, 0, false, 'L');
			$pdf->SetXY(48, 78);
			$pdf->Cell(0, 0, $ureg_no, 0, false, 'L'); 
            
			// dd($subjectsArr);
			/*Subject*/
			$subj_y=107;
            foreach ($subjectsArr as $subjectDatas){
                $th_pr_val = strtoupper(trim($subjectDatas[0])); //TH|PR, TH, PR	
                $Sr_no = trim($subjectDatas[1]);	
                $Subject_Code = strtoupper(trim($subjectDatas[2]));		
                $Subject_Name = strtoupper(trim($subjectDatas[3]));
                $credits_TH = strtoupper(trim($subjectDatas[4]));
                $IA_OBT_TH = trim($subjectDatas[5]);	
                $IA_OUT_TH = trim($subjectDatas[6]);
                $UE_OBT_TH = trim($subjectDatas[7]);	
                $UE_OUT_TH = trim($subjectDatas[8]);	
                $final_marks_TH = trim($subjectDatas[9]);
                $letter_grade_TH = trim($subjectDatas[10]);
                $grade_point_TH = trim($subjectDatas[11]);
                $credit_point_TH = trim($subjectDatas[12]);
                $remarks_TH = trim($subjectDatas[13]);
                $credits_PR = strtoupper(trim($subjectDatas[14]));
                $IA_OBT_PR = trim($subjectDatas[15]);	
                $IA_OUT_PR = trim($subjectDatas[16]);
                $UE_OBT_PR = trim($subjectDatas[17]);	
                $UE_OUT_PR = trim($subjectDatas[18]);	
                $final_marks_PR = trim($subjectDatas[19]);
                $letter_grade_PR = trim($subjectDatas[20]);
                $grade_point_PR = trim($subjectDatas[21]);
                $credit_point_PR = trim($subjectDatas[22]);
                $remarks_PR = trim($subjectDatas[23]);
            
                /*start pdfbig line*/
                // store current object
                $pdfBig->startTransaction();
                $pdfBig->SetFont($MyriadProRegular, '', 7.5, '', false);
                $pdf->startTransaction();
                $pdf->SetFont($MyriadProRegular, '', 7.5, '', false);	 				
                // get the number of lines
                $Subject_Name2=str_replace("<br>", "", $Subject_Name);
                $Subject_Name=str_replace("@", "•", $Subject_Name2);
                $lines1 = $pdfBig->MultiCell(35, 0, $Subject_Name2, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                // dd($lines1);
                $lines2 = $pdfBig->MultiCell(15, 0, $Subject_Code, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                 $lines3 = $pdfBig->MultiCell(15, 0, $IA_OUT_TH, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                  $lines4 = $pdfBig->MultiCell(13, 0, $UE_OUT_TH, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);


                $lines = max($lines1, $lines2, $lines3, $lines4);
                // dd($lines1,$lines2,$lines3,$lines4);
                // if($lines1>=$lines2){
                // 	$lines=$lines1;
                // }if($lines1<=$lines2){
                // 	$lines=$lines2;
                // }
                
                // restore previous object
                $pdfBig=$pdfBig->rollbackTransaction();	
                $pdf=$pdf->rollbackTransaction();
                if($lines == 2){
                	$th_height = 8;
                	$th_height_half = 4;
                } else if($lines == 3){
                	$th_height = 13;
                	$th_height_half = 6.5;
                }else if($lines == 4){
                	$th_height = 15;
                	$th_height_half = 7.5;
                }else if($lines == 5){
                	$th_height = 18;
                	$th_height_half = 9;
                }else if($lines == 6){
                	$th_height = 23;
                	$th_height_half = 11.5;
                }else if($lines == 7){
                	$th_height = 26;
                	$th_height_half = 13;
                }
                else if($lines >7){
                	$th_height = 24;
                	$th_height_half = 12;
                }
                else{
                	$th_height = 5;
                	$th_height_half = 2.5;
                }	
                $pdfBig->SetXY($left_str_x-3, $subj_y);
                $pdfBig->SetFont($MyriadProRegular, '', 7.5, '', false);
                $pdf->SetXY($left_str_x, $subj_y);
                $pdf->SetFont($MyriadProRegular, '', 7.5, '', false);	 
               
                if ($th_pr_val == 'TH|PR') {
                    $pdfBig->MultiCell(9.6, $th_height, $Sr_no, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15,$th_height, $Subject_Code, 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->MultiCell(35,$th_height, $Subject_Name,1, "L", 0, 0, '', '', true, 0, true);			
                    $pdfBig->SetXY(71.7, $subj_y);					
                    $pdfBig->MultiCell(13, $th_height_half, $credits_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15, $th_height_half, $IA_OBT_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15, $th_height_half, $IA_OUT_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15, $th_height_half, $UE_OBT_TH, 1, 'C', 0, 0);               
                    $pdfBig->MultiCell(13, $th_height_half, $UE_OUT_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(10, $th_height_half, $final_marks_TH, 1, 'C', 0, 0);	
                    $pdfBig->MultiCell(11, $th_height_half, $letter_grade_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(10, $th_height_half, $grade_point_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(10.4, $th_height_half, $credit_point_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(13.4, $th_height_half, $remarks_TH, 1, 'C', 0, 0);
                    $pdfBig->SetXY(71.7, $subj_y+$th_height_half);
                    $pdfBig->MultiCell(13, $th_height_half, $credits_PR, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15, $th_height_half, $IA_OBT_PR, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15, $th_height_half, $IA_OUT_PR, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15, $th_height_half, $UE_OBT_PR, 1, 'C', 0, 0);               
                    $pdfBig->MultiCell(13, $th_height_half, $UE_OUT_PR, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(10, $th_height_half, $final_marks_PR, 1, 'C', 0, 0);	
                    $pdfBig->MultiCell(11, $th_height_half, $letter_grade_PR, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(10, $th_height_half, $grade_point_PR, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(10.4, $th_height_half, $credit_point_PR, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(13.4, $th_height_half, $remarks_PR, 1, 'C', 0, 0);
                    //drfew
                    $pdf->MultiCell(9.6, $th_height, $Sr_no, 1, 'C', 0, 0);
                    $pdf->MultiCell(15,$th_height, $Subject_Code, 1, "C", 0, 0, '', '', true, 0, true);

                    $pdf->MultiCell(35,$th_height, $Subject_Name,1, "L", 0, 0, '', '', true, 0, true);			
                    $pdf->SetXY(71.7, $subj_y);					
                    $pdf->MultiCell(13, $th_height_half, $credits_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height_half, $IA_OBT_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height_half, $IA_OUT_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height_half, $UE_OBT_TH, 1, 'C', 0, 0);               
                    $pdf->MultiCell(13, $th_height_half, $UE_OUT_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(10, $th_height_half, $final_marks_TH, 1, 'C', 0, 0);	
                    $pdf->MultiCell(11, $th_height_half, $letter_grade_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(10, $th_height_half, $grade_point_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(10.4, $th_height_half, $credit_point_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(13.4, $th_height_half, $remarks_TH, 1, 'C', 0, 0);
                    $pdf->SetXY(71.7, $subj_y+$th_height_half);
                    $pdf->MultiCell(13, $th_height_half, $credits_PR, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height_half, $IA_OBT_PR, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height_half, $IA_OUT_PR, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height_half, $UE_OBT_PR, 1, 'C', 0, 0);               
                    $pdf->MultiCell(13, $th_height_half, $UE_OUT_PR, 1, 'C', 0, 0);
                    $pdf->MultiCell(10, $th_height_half, $final_marks_PR, 1, 'C', 0, 0);	
                    $pdf->MultiCell(11, $th_height_half, $letter_grade_PR, 1, 'C', 0, 0);
                    $pdf->MultiCell(10, $th_height_half, $grade_point_PR, 1, 'C', 0, 0);
                    $pdf->MultiCell(10.4, $th_height_half, $credit_point_PR, 1, 'C', 0, 0);
                    $pdf->MultiCell(13.4, $th_height_half, $remarks_PR, 1, 'C', 0, 0);
                }else{	
                    $pdfBig->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');	
                    $pdfBig->MultiCell(9.6, $th_height, $Sr_no, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15,$th_height, $Subject_Code, 1, "C", 0, 0, '', '', true, 0, true);
                    
                    $pdfBig->MultiCell(35,$th_height, $Subject_Name,1, "L", 0, 0, '', '', true, 0, true);	
                    // $pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');		
                    $pdfBig->SetXY(71.7, $subj_y);                    					
                    $pdfBig->MultiCell(13, $th_height, $credits_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(15, $th_height, $IA_OBT_TH, 1, 'C', 0, 0);
                    //  $pdfBig->SetFont($MyriadProRegular, '', 6.5, '', false);
                    $pdfBig->MultiCell(15, $th_height, $IA_OUT_TH, 1, 'C', 0, 0);
                    //  $pdfBig->SetFont($MyriadProRegular, '', 7.5, '', false);
                    $pdfBig->MultiCell(15, $th_height, $UE_OBT_TH, 1, 'C', 0, 0); 
                    //  $pdfBig->SetFont($MyriadProRegular, '', 6.5, '', false);              
                    $pdfBig->MultiCell(13, $th_height, $UE_OUT_TH, 1, 'C', 0, 0);
                    //  $pdfBig->SetFont($MyriadProRegular, '', 7.5, '', false);
                    $pdfBig->MultiCell(10, $th_height, $final_marks_TH, 1, 'C', 0, 0);	
                    $pdfBig->MultiCell(11, $th_height, $letter_grade_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(10, $th_height, $grade_point_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(10.4, $th_height, $credit_point_TH, 1, 'C', 0, 0);
                    $pdfBig->MultiCell(13.4, $th_height, $remarks_TH, 1, 'C', 0, 0);

                    //fasfsaf
                    $pdf->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');	
                    $pdf->MultiCell(9.6, $th_height, $Sr_no, 1, 'C', 0, 0);
                    $pdf->MultiCell(15,$th_height, $Subject_Code, 1, "C", 0, 0, '', '', true, 0, true);
                    
                    $pdf->MultiCell(35,$th_height, $Subject_Name,1, "L", 0, 0, '', '', true, 0, true);	
                    // $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');		
                    $pdf->SetXY(71.7, $subj_y);                    					
                    $pdf->MultiCell(13, $th_height, $credits_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height, $IA_OBT_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height, $IA_OUT_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(15, $th_height, $UE_OBT_TH, 1, 'C', 0, 0);               
                    $pdf->MultiCell(13, $th_height, $UE_OUT_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(10, $th_height, $final_marks_TH, 1, 'C', 0, 0);	
                    $pdf->MultiCell(11, $th_height, $letter_grade_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(10, $th_height, $grade_point_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(10.4, $th_height, $credit_point_TH, 1, 'C', 0, 0);
                    $pdf->MultiCell(13.4, $th_height, $remarks_TH, 1, 'C', 0, 0);
                }
                $subj_y=$subj_y+$th_height;
            }

			/*Grand Total*/
			$pdfBig->SetFont($MyriadProBold, 'B', 8, '', false);
            $pdf->SetFont($MyriadProBold, 'B', 8, '', false);

            
            if($year == trim("I SEMESTER")){
            	$pdfBig->SetXY($left_str_x-3, $subj_y);	
				$pdfBig->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdfBig->SetXY(71.7, $subj_y);	
                $pdfBig->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdfBig->SetXY(173.7, $subj_y); 
                $pdfBig->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $pdf->SetXY($left_str_x-3, $subj_y);	
				$pdf->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdf->SetXY(71.7, $subj_y);	
                $pdf->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdf->SetXY(173.7, $subj_y); 
                $pdf->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $notes= "<b>*ENGL 101-College Exam, Marks are not added for calculating SGPA, Pass marks-40%</b>";
            } elseif($year == trim("II SEMESTER")){
                $pdfBig->SetXY($left_str_x-3, $subj_y);	
				$pdfBig->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdfBig->SetXY(71.7, $subj_y);	
                $pdfBig->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdfBig->SetXY(173.7, $subj_y); 
                $pdfBig->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $pdf->SetXY($left_str_x-3, $subj_y);	
				$pdf->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdf->SetXY(71.7, $subj_y);	
                $pdf->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdf->SetXY(173.7, $subj_y); 
                $pdf->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $notes= "<b>* T-Theory, P-Practicum</b> (Skill lab & Clinical)<br>*HNIT 145-College Exam";
            }elseif($year == trim("III SEMESTER")){ 
                $pdfBig->SetXY($left_str_x-3, $subj_y);	
				$pdfBig->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdfBig->SetXY(71.7, $subj_y);	
                $pdfBig->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdfBig->SetXY(173.7, $subj_y); 
                $pdfBig->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $pdf->SetXY($left_str_x-3, $subj_y);	
				$pdf->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdf->SetXY(71.7, $subj_y);	
                $pdf->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdf->SetXY(173.7, $subj_y); 
                $pdf->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $notes= "<b>* T-Theory, P-Practical</b>";
            }elseif($year == trim("IV SEMESTER")){
                $pdfBig->SetXY($left_str_x-3, $subj_y);	
				$pdfBig->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdfBig->SetXY(71.7, $subj_y);	
                $pdfBig->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdfBig->SetXY(173.7, $subj_y); 
                $pdfBig->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $pdf->SetXY($left_str_x-3, $subj_y);	
				$pdf->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdf->SetXY(71.7, $subj_y);	
                $pdf->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdf->SetXY(173.7, $subj_y); 
                $pdf->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $notes= "<b>* T-Theory, P-Practical</b><br>*PROF 230-College Exam; *Elective 1-College Exam, Marks are not added for calculating SGPA, Pass marks-40%";
            }elseif($year == trim("V SEMESTER")){
                $pdfBig->SetXY($left_str_x-3, $subj_y);	
				$pdfBig->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdfBig->SetXY(71.7, $subj_y);	
                $pdfBig->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdfBig->SetXY(173.7, $subj_y); 
                $pdfBig->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $pdf->SetXY($left_str_x-3, $subj_y);	
				$pdf->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdf->SetXY(71.7, $subj_y);	
                $pdf->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdf->SetXY(173.7, $subj_y); 
                $pdf->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $notes= "<b>* T-Theory, P-Practical</b><br>*N-FORN 320-College Exam";
            }elseif($year == trim("VI SEMESTER")){
                $pdfBig->SetXY($left_str_x-3, $subj_y);	
				$pdfBig->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdfBig->SetXY(71.7, $subj_y);	
                $pdfBig->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdfBig->SetXY(173.7, $subj_y); 
                $pdfBig->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $pdf->SetXY($left_str_x-3, $subj_y);	
				$pdf->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdf->SetXY(71.7, $subj_y);	
                $pdf->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdf->SetXY(173.7, $subj_y); 
                $pdf->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $notes= "<b>* T-Theory, P-Practical</b><br>*Elective-2-College Exam, Marks are not added for calculating SGPA, Pass marks-40%";
            }elseif($year == trim("VII SEMESTER")){
                $pdfBig->SetXY($left_str_x-3, $subj_y);	
				$pdfBig->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdfBig->SetXY(71.7, $subj_y);	
                $pdfBig->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdfBig->SetXY(173.7, $subj_y); 
                $pdfBig->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $pdf->SetXY($left_str_x-3, $subj_y);	
				$pdf->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdf->SetXY(71.7, $subj_y);	
                $pdf->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdf->SetXY(173.7, $subj_y); 
                $pdf->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $notes= "<b>T-Theory, P-Practical</b>";
            }elseif($year == trim("VIII SEMESTER")){
                $pdfBig->SetXY($left_str_x-3, $subj_y);	
				$pdfBig->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdfBig->SetXY(71.7, $subj_y);	
                $pdfBig->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdfBig->SetXY(173.7, $subj_y); 
                $pdfBig->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);

                $pdf->SetXY($left_str_x-3, $subj_y);	
				$pdf->MultiCell(185.5, 5, '', 'LRTB', 'C', 0, 0);	//Entire Table
                $pdf->SetXY(71.7, $subj_y);	
                $pdf->MultiCell(13, 5, $total_credits, 'LR', 'C', 0, 0);
                $pdf->SetXY(173.7, $subj_y); 
                $pdf->MultiCell(10.4, 5,$total_credit_point, 'RL', 'C', 0, 0);
                $notes= "<b>P-Practical</b><br>*Elective 3-Marks are not added for calculating SGPA";
            }else{
                $notes= "";
            }        
			 

			$subj_y=$pdfBig->GetY()+5.5;
			$pdfBig->SetFont($MyriadProRegular, '', 9, '', false);
			$pdfBig->SetXY($left_str_x, $subj_y);			
			$pdfBig->MultiCell(0, 0, $notes, 0, 'L', 0, 0, '', '', true, 0, true);

            $subj_y=$pdf->GetY()+5.5;
			$pdf->SetFont($MyriadProRegular, '', 9, '', false);
			$pdf->SetXY($left_str_x, $subj_y);			
			$pdf->MultiCell(0, 0, $notes, 0, 'L', 0, 0, '', '', true, 0, true);
            //Mark Statement start
            $pdfBig->SetFont($MinionProBold, '', 11, '', false);			
            $pdfBig->SetXY($left_str_x, $subj_y+8);
			$pdfBig->Cell(188, 0,'-End of Mark Statement-', 0, false, 'C');
			$pdfBig->SetFont($MinionProBold, '', 10, '', false); 
			$exclude=(!empty($credit_ex_subj))?('excluding '.$credit_ex_subj):null;
			if($year == trim("I SEMESTER")||$year == trim("III SEMESTER")||$year == trim("VII SEMESTER")){
				switch ($year) {
					case 'I SEMESTER':
						$sem='I';
						break;
					case 'III SEMESTER':
					$sem='III';
					break;
					
					default:
						$sem='VII';
						break;
				}
                $pdfBig->SetXY($left_str_x, $subj_y+13);	
                $pdfBig->MultiCell(120,7, 'Semester', 1, 'C', 0, 0);
                $pdfBig->MultiCell(30, 7,$sem, 1, 'C', 0, 0);
                $pdfBig->MultiCell(30, 7,'SGPA', 1, 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+20);	
                $pdfBig->MultiCell(120,7, 'Total Credit Points Earned', 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(30, 7,$total_cp_earned, 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(30, 7,'', 'LR', 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+27);
                $pdfBig->MultiCell(120,7, 'Applicable Credits '.$exclude, 'LR', 'C', 0, 0);
                $pdfBig->MultiCell(30, 7,$total_ex_credit, 'LR', 'C', 0, 0);
                // $pdfBig->SetXY($left_str_x+150, $subj_y+25);
                $pdfBig->MultiCell(30, 7,$sgpa, 'LR', 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+34);	
                $pdfBig->MultiCell(150,7, 'Result', 1, 'C', 0, 0);
                // $pdfBig->MultiCell(30, 5,'I', 0, 'C', 0, 0);
                $pdfBig->MultiCell(30, 7,$exam_result, 1, 'C', 0, 0);
                $increase=10;
			}else if($year == trim("II SEMESTER")||$year == trim("IV SEMESTER")||$year == trim("V SEMESTER")||$year == trim("VI SEMESTER")){
				switch ($year) {
					case 'II SEMESTER':
					$sem='II';
						break;
					case 'IV SEMESTER':
					$sem='IV';
						break;
					case 'V SEMESTER':
					$sem='V';
						break;					
					default:
						$sem='VI';
						break;
				}
                $pdfBig->SetXY($left_str_x, $subj_y+13);	
                $pdfBig->MultiCell(120,5, 'Semester', 1, 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$sem, 1, 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,'SGPA', 1, 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+18.5);	
                $pdfBig->MultiCell(120,5, 'Total Credit Points Earned', 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$total_cp_earned, 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,'', 'LR', 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+23.5);
                $pdfBig->MultiCell(120,5, 'Applicable Credits '.$exclude, 'LR', 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$total_ex_credit, 'LR', 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$sgpa, 'LR', 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+29);	
                $pdfBig->MultiCell(150,5, 'Result', 1, 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$exam_result, 1, 'C', 0, 0);
                $increase=2;
			}else if($year == trim("VIII SEMESTER")){
				//2nd table
                $pdfBig->SetXY($left_str_x, $subj_y+13);	
                $pdfBig->MultiCell(120,5, 'Semester', 1, 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,'VIII', 1, 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,'SGPA', 1, 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+18.5);	
                $pdfBig->MultiCell(120,5, 'Total Credit Points Earned', 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$total_cp_earned, 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,'', 'LR', 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+23.5);
                $pdfBig->MultiCell(120,5, 'Applicable Credits '.$exclude, 'LR', 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$total_ex_credit, 'LR', 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$sgpa, 'LR', 'C', 0, 0);
                $pdfBig->SetXY($left_str_x, $subj_y+29);	
                $pdfBig->MultiCell(150,5, 'Result', 1, 'C', 0, 0);
                $pdfBig->MultiCell(30, 5,$exam_result, 1, 'C', 0, 0);
                $increase=20;

                //3rd table
                $tbl_3rd_y=$subj_y+36;
                $pdfBig->SetXY($left_str_x-3, $tbl_3rd_y);	
                $pdfBig->MultiCell(30, 5, 'Semester', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'I', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'II', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'III', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'IV', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'V', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'VI', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'VII', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'VIII', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'Total', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'CGPA', 1, 'C', 0, 0);
                $pdfBig->SetXY($left_str_x-3, $tbl_3rd_y+5.5);	
                $pdfBig->MultiCell(30,5, 'Applicable Credits', 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'12', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'31', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'17', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'21', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'11', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'21', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'26', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'12', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'151', 1, 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,'', 'LR', 'C', 0, 0);
                $pdfBig->SetXY($left_str_x-3, $tbl_3rd_y+11);
                $pdfBig->MultiCell(30,5, 'SGPA', 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(140.4, 5,$sgpa, 'LRB', 'C', 0, 0);
                $pdfBig->MultiCell(15.6, 5,$cgpa, 'LRB', 'C', 0, 0);
			}
			//Mark Statement end
            $percentageNote = "The grade points are out of 10, based on UGC 10-point grading system modified with Pass grade as follows.";
            $pdfBig->SetFont($MyriadProRegular, '', 9, '', false); 
            $pdfBig->SetXY($left_str_x, $subj_y+32.5+$increase);
            $pdfBig->MultiCell(0, 0, $percentageNote, 0, 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
            $pdfBig->SetFont($MyriadProRegular, '', 8, '', false);    
            $pdfBig->SetXY($left_str_x-3, $subj_y+51.4+$increase);//3rd
			$pdfBig->MultiCell(20, 5.2, '% OF MARKS', 1, 'L', 0, 0);
			$pdfBig->MultiCell(19, 5.2, '85% & Above', 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 5.2, '80-84.99%', 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 5.2, '75-79.99%', 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 5.2, '65-74.99%', 1, 'C', 0, 0);
            $pdfBig->MultiCell(25, 5.2, '60-64.99%', 1, 'C', 0, 0);
            $pdfBig->MultiCell(20, 5.2, '50-59.99%', 1, 'C', 0, 0);
            $pdfBig->MultiCell(27, 5.2, '50% and above', 1, 'C', 0, 0);
            $pdfBig->MultiCell(15, 5.2, '< 50%', 1, 'C', 0, 0);
            $pdfBig->MultiCell(12, 5.2, '0', 1, 'C', 0, 0);

            $pdfBig->SetXY($left_str_x-3, $subj_y+38+$increase);//1st
			$pdfBig->MultiCell(20, 8.2, 'LETTER GRADE', 1, 'L', 0, 0);
			$pdfBig->MultiCell(19, 8.2, "O\n(Outstanding)", 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 8.2, "A+\n(Excellent)", 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 8.2, "A\n(Very Good)", 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 8.2, "B+\n(Good)", 1, 'C', 0, 0);
            $pdfBig->MultiCell(25, 8.2, "B\n(Above Average)", 1, 'C', 0, 0);
            $pdfBig->MultiCell(20, 8.2, "C\n(Average)", 1, 'C', 0, 0);
            $pdfBig->MultiCell(27, 8.2, "P\n(Pass)", 1, 'C', 0, 0);
            $pdfBig->MultiCell(15, 8.2, "F\n(Fail)", 1, 'C', 0, 0);
            $pdfBig->MultiCell(12, 8.2, "Ab\n(Absent)", 1, 'C', 0, 0);

            
			$pdfBig->SetXY($left_str_x-3, $subj_y+46+$increase);//2nd
			$pdfBig->MultiCell(20, 5.2, 'GRADE POINT', 1, 'L', 0, 0);
			$pdfBig->MultiCell(19, 5.2, '10', 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 5.2, '9', 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 5.2, '8', 1, 'C', 0, 0);
			$pdfBig->MultiCell(16, 5.2, '7', 1, 'C', 0, 0);
            $pdfBig->MultiCell(25, 5.2, '6', 1, 'C', 0, 0);
            $pdfBig->MultiCell(20, 5.2, '5', 1, 'C', 0, 0);
            $pdfBig->MultiCell(27, 5.2, '-', 1, 'C', 0, 0);
            $pdfBig->MultiCell(15, 5.2, '0', 1, 'C', 0, 0);
            $pdfBig->MultiCell(12, 5.2, '0', 1, 'C', 0, 0);

            $pdfBig->SetFont($MyriadProRegular, '', 9, '', false);
			$pdfBig->SetXY($left_str_x-3, $subj_y+56.7+$increase);//4th
			$pdfBig->MultiCell(20, 9, 'Pass Criteria', 'LRT', 'L', 0, 0);
			$pdfBig->MultiCell(166, 8.2, "For Nursing Courses and all other courses: - Pass is at C Grade (5 grade point) 50% and above\nFor Communicative English and Electives: - Pass is at (4 grade point) 40% and above", 'LRT', 'L', 0, 0);

			$pdfBig->SetFont($MyriadProRegular, 'B', 9, '', false);//5th
			$pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
			$pdfBig->SetXY($left_str_x-3, $subj_y+66+$increase);//5th
			$pdfBig->MultiCell(93, 13, 'SGPA', 1, 'C', 0, 0);
			$pdfBig->MultiCell(93, 13, "Σ (No. of Credits x Grade Points)
……………………………..…..…..…
Σ (No. of Credits)", 1, 'C', 0, 0);
			if($year == trim("VIII SEMESTER")){
			$pdfBig->SetFont($MyriadProRegular, 'B', 9, '', false);//5th
			$pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
			$pdfBig->SetXY($left_str_x-3, $subj_y+79+$increase);//5th
			$pdfBig->MultiCell(93, 13, 'CGPA', 1, 'C', 0, 0);
			$pdfBig->MultiCell(93, 13, "Σ (No. of Credits x SGPA)
……………………………..…..…..…
Σ (No. of Credits)", 1, 'C', 0, 0);
			}

            $pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
            //ffdf
            $pdf->SetFont($MinionProBold, '', 11, '', false);			
            $pdf->SetXY($left_str_x, $subj_y+8);
			$pdf->Cell(188, 0,'-End of Mark Statement-', 0, false, 'C');
			$pdf->SetFont($MinionProBold, '', 10, '', false); 
			if($year == trim("I SEMESTER")||$year == trim("III SEMESTER")||$year == trim("VII SEMESTER")){
				switch ($year) {
					case 'I SEMESTER':
						$sem='I';
						break;
					case 'III SEMESTER':
					$sem='III';
					break;
					
					default:
						$sem='VII';
						break;
				}
                $pdf->SetXY($left_str_x, $subj_y+13);	
                $pdf->MultiCell(120,7, 'Semester', 1, 'C', 0, 0);
                $pdf->MultiCell(30, 7,$sem, 1, 'C', 0, 0);
                $pdf->MultiCell(30, 7,'SGPA', 1, 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+20);	
                $pdf->MultiCell(120,7, 'Total Credit Points Earned', 'LRB', 'C', 0, 0);
                $pdf->MultiCell(30, 7,$total_cp_earned, 'LRB', 'C', 0, 0);
                $pdf->MultiCell(30, 7,'', 'LR', 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+27);
                $pdf->MultiCell(120,7, 'Applicable Credits '.$exclude, 'LR', 'C', 0, 0);
                $pdf->MultiCell(30, 7,$total_ex_credit, 'LR', 'C', 0, 0);
                // $pdf->SetXY($left_str_x+150, $subj_y+25);
                $pdf->MultiCell(30, 7,$sgpa, 'LR', 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+34);	
                $pdf->MultiCell(150,7, 'Result', 1, 'C', 0, 0);
                // $pdf->MultiCell(30, 5,'I', 0, 'C', 0, 0);
                $pdf->MultiCell(30, 7,$exam_result, 1, 'C', 0, 0);
                $increase=10;
			}else if($year == trim("II SEMESTER")||$year == trim("IV SEMESTER")||$year == trim("V SEMESTER")||$year == trim("VI SEMESTER")){
				switch ($year) {
					case 'II SEMESTER':
					$sem='II';
						break;
					case 'IV SEMESTER':
					$sem='IV';
						break;
					case 'V SEMESTER':
					$sem='V';
						break;					
					default:
						$sem='VI';
						break;
				}
                $pdf->SetXY($left_str_x, $subj_y+13);	
                $pdf->MultiCell(120,5, 'Semester', 1, 'C', 0, 0);
                $pdf->MultiCell(30, 5,$sem, 1, 'C', 0, 0);
                $pdf->MultiCell(30, 5,'SGPA', 1, 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+18.5);	
                $pdf->MultiCell(120,5, 'Total Credit Points Earned', 'LRB', 'C', 0, 0);
                $pdf->MultiCell(30, 5,$total_cp_earned, 'LRB', 'C', 0, 0);
                $pdf->MultiCell(30, 5,'', 'LR', 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+23.5);
                $pdf->MultiCell(120,5, 'Applicable Credits '.$exclude, 'LR', 'C', 0, 0);
                $pdf->MultiCell(30, 5,$total_ex_credit, 'LR', 'C', 0, 0);
                $pdf->MultiCell(30, 5,$sgpa, 'LR', 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+29);	
                $pdf->MultiCell(150,5, 'Result', 1, 'C', 0, 0);
                $pdf->MultiCell(30, 5,$exam_result, 1, 'C', 0, 0);
                $increase=0;
			}else if($year == trim("VIII SEMESTER")){
				//2nd table
                $pdf->SetXY($left_str_x, $subj_y+13);	
                $pdf->MultiCell(120,5, 'Semester', 1, 'C', 0, 0);
                $pdf->MultiCell(30, 5,'VIII', 1, 'C', 0, 0);
                $pdf->MultiCell(30, 5,'SGPA', 1, 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+18.5);	
                $pdf->MultiCell(120,5, 'Total Credit Points Earned', 'LRB', 'C', 0, 0);
                $pdf->MultiCell(30, 5,$total_cp_earned, 'LRB', 'C', 0, 0);
                $pdf->MultiCell(30, 5,'', 'LR', 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+23.5);
                $pdf->MultiCell(120,5, 'Applicable Credits '.$exclude, 'LR', 'C', 0, 0);
                $pdf->MultiCell(30, 5,$total_ex_credit, 'LR', 'C', 0, 0);
                $pdf->MultiCell(30, 5,$sgpa, 'LR', 'C', 0, 0);
                $pdf->SetXY($left_str_x, $subj_y+29);	
                $pdf->MultiCell(150,5, 'Result', 1, 'C', 0, 0);
                $pdf->MultiCell(30, 5,$exam_result, 1, 'C', 0, 0);
                $increase=20;

                //3rd table
                $tbl_3rd_y=$subj_y+38;
                $pdf->SetXY($left_str_x-3, $tbl_3rd_y);	
                $pdf->MultiCell(30, 5, 'Semester', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'I', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'II', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'III', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'IV', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'V', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'VI', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'VII', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'VIII', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'Total', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'CGPA', 1, 'C', 0, 0);
                $pdf->SetXY($left_str_x-3, $tbl_3rd_y+5.5);	
                $pdf->MultiCell(30,5, 'Applicable Credits', 'LRB', 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'12', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'31', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'17', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'21', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'11', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'21', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'26', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'12', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'151', 1, 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,'', 'LR', 'C', 0, 0);
                $pdf->SetXY($left_str_x-3, $tbl_3rd_y+11);
                $pdf->MultiCell(30,5, 'SGPA', 'LRB', 'C', 0, 0);
                $pdf->MultiCell(140.4, 5,$sgpa, 'LRB', 'C', 0, 0);
                $pdf->MultiCell(15.6, 5,$cgpa, 'LRB', 'C', 0, 0);
			}
			//Mark Statement end
            $percentageNote = "The grade points are out of 10, based on UGC 10-point grading system modified with Pass grade as follows.";
            $pdf->SetFont($MyriadProRegular, '', 9, '', false); 
            $pdf->SetXY($left_str_x, $subj_y+34.5+$increase);
            $pdf->MultiCell(0, 0, $percentageNote, 0, 'C', 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
            $pdf->SetFont($MyriadProRegular, '', 8, '', false);    
            $pdf->SetXY($left_str_x-3, $subj_y+54.4+$increase);//3rd
			$pdf->MultiCell(20, 5.2, '% OF MARKS', 1, 'L', 0, 0);
			$pdf->MultiCell(19, 5.2, '85% & Above', 1, 'C', 0, 0);
			$pdf->MultiCell(16, 5.2, '80-84.99%', 1, 'C', 0, 0);
			$pdf->MultiCell(16, 5.2, '75-79.99%', 1, 'C', 0, 0);
			$pdf->MultiCell(16, 5.2, '65-74.99%', 1, 'C', 0, 0);
            $pdf->MultiCell(25, 5.2, '60-64.99%', 1, 'C', 0, 0);
            $pdf->MultiCell(20, 5.2, '50-59.99%', 1, 'C', 0, 0);
            $pdf->MultiCell(27, 5.2, '50% and above', 1, 'C', 0, 0);
            $pdf->MultiCell(15, 5.2, '< 50%', 1, 'C', 0, 0);
            $pdf->MultiCell(12, 5.2, '0', 1, 'C', 0, 0);

            $pdf->SetXY($left_str_x-3, $subj_y+41+$increase);//1st
			$pdf->MultiCell(20, 8.2, 'LETTER GRADE', 1, 'L', 0, 0);
			$pdf->MultiCell(19, 8.2, "O\n(Outstanding)", 1, 'C', 0, 0);
			$pdf->MultiCell(16, 8.2, "A+\n(Excellent)", 1, 'C', 0, 0);
			$pdf->MultiCell(16, 8.2, "A\n(Very Good)", 1, 'C', 0, 0);
			$pdf->MultiCell(16, 8.2, "B+\n(Good)", 1, 'C', 0, 0);
            $pdf->MultiCell(25, 8.2, "B\n(Above Average)", 1, 'C', 0, 0);
            $pdf->MultiCell(20, 8.2, "C\n(Average)", 1, 'C', 0, 0);
            $pdf->MultiCell(27, 8.2, "P\n(Pass)", 1, 'C', 0, 0);
            $pdf->MultiCell(15, 8.2, "F\n(Fail)", 1, 'C', 0, 0);
            $pdf->MultiCell(12, 8.2, "Ab\n(Absent)", 1, 'C', 0, 0);

            
			$pdf->SetXY($left_str_x-3, $subj_y+49+$increase);//2nd
			$pdf->MultiCell(20, 5.2, 'GRADE POINT', 1, 'L', 0, 0);
			$pdf->MultiCell(19, 5.2, '10', 1, 'C', 0, 0);
			$pdf->MultiCell(16, 5.2, '9', 1, 'C', 0, 0);
			$pdf->MultiCell(16, 5.2, '8', 1, 'C', 0, 0);
			$pdf->MultiCell(16, 5.2, '7', 1, 'C', 0, 0);
            $pdf->MultiCell(25, 5.2, '6', 1, 'C', 0, 0);
            $pdf->MultiCell(20, 5.2, '5', 1, 'C', 0, 0);
            $pdf->MultiCell(27, 5.2, '-', 1, 'C', 0, 0);
            $pdf->MultiCell(15, 5.2, '0', 1, 'C', 0, 0);
            $pdf->MultiCell(12, 5.2, '0', 1, 'C', 0, 0);

            $pdf->SetFont($MyriadProRegular, '', 9, '', false);
			$pdf->SetXY($left_str_x-3, $subj_y+59.7+$increase);//4th
			$pdf->MultiCell(20, 9, 'Pass Criteria', 'LRT', 'L', 0, 0);
			$pdf->MultiCell(166, 8.2, "For Nursing Courses and all other courses: - Pass is at C Grade (5 grade point) 50% and above\nFor Communicative English and Electives: - Pass is at (4 grade point) 40% and above", 'LRT', 'L', 0, 0);

			$pdf->SetFont($MyriadProRegular, 'B', 9, '', false);//5th
			$pdf->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
			$pdf->SetXY($left_str_x-3, $subj_y+69+$increase);//5th
			$pdf->MultiCell(93, 13, 'SGPA', 1, 'C', 0, 0);
			$pdf->MultiCell(93, 13, "Σ (No. of Credits x Grade Points)
……………………………..…..…..…
Σ (No. of Credits)", 1, 'C', 0, 0);
			if($year == trim("VIII SEMESTER")){
			$pdf->SetFont($MyriadProRegular, 'B', 9, '', false);//5th
			$pdf->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
			$pdf->SetXY($left_str_x-3, $subj_y+82+$increase);//5th
			$pdf->MultiCell(93, 13, 'CGPA', 1, 'C', 0, 0);
			$pdf->MultiCell(93, 13, "Σ (No. of Credits x SGPA)
……………………………..…..…..…
Σ (No. of Credits)", 1, 'C', 0, 0);
			}
			
            $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');


			// /*3rd box end*/
			$pdfBig->SetXY(144, 270.5);
			$pdfBig->SetFont($arialNarrow, '', 1, '', false);
			$pdfBig->MultiCell(47.5, 1, '', 'T', 'C', 0, 0);
			$pdfBig->SetFont($MyriadProRegular, '', 10, '', false);
			$pdfBig->SetXY(135, 271);
			$pdfBig->MultiCell(0, 0, 'Controller of Examinations', 0, 'C', 0, 0);
			
			$pdfBig->SetXY(12.3, 269.2);
			$pdfBig->SetFont($MyriadProRegular, '', 10, '', false);	
			$pdfBig->MultiCell(0, 0, 'Date: ',0, 'L', 0, 0);
			$pdfBig->SetXY(21, 269.2);
			$pdfBig->SetFont($MyriadProRegular, '', 10, '', false);
			$pdfBig->MultiCell(0, 0, $Date,0, 'L', 0, 0);
			
			$pdfBig->SetFont($MyriadProRegular, '',7.5, '', false);
			$pdfBig->SetXY(50, 283);
			$pdfBig->MultiCell(92, 0, "SDM Institute of Nursing  Sciences, Sattur, Dharwad – 580009, Karnataka, India",0, 'C', 0, 0);

            $pdf->SetXY(144, 270.5);
			$pdf->SetFont($arialNarrow, '', 1, '', false);
			$pdf->MultiCell(47.5, 1, '', 'T', 'C', 0, 0);
			$pdf->SetFont($MyriadProRegular, '', 10, '', false);
			$pdf->SetXY(135, 271);
			$pdf->MultiCell(0, 0, 'Controller of Examinations', 0, 'C', 0, 0);
			
			$pdf->SetXY(12.3, 269.2);
			$pdf->SetFont($MyriadProRegular, '', 10, '', false);	
			$pdf->MultiCell(0, 0, 'Date: ',0, 'L', 0, 0);
			$pdf->SetXY(21, 269.2);
			$pdf->SetFont($MyriadProRegular, '', 10, '', false);
			$pdf->MultiCell(0, 0, $Date,0, 'L', 0, 0);
			
			$pdf->SetFont($MyriadProRegular, '',7.5, '', false);
			$pdf->SetXY(50, 283);
			$pdf->MultiCell(92, 0, "SDM Institute of Nursing  Sciences, Sattur, Dharwad – 580009, Karnataka, India",0, 'C', 0, 0);
			/******End pdfBig ******/
            
			
			// Ghost image
			$nameOrg=$candidate_name;
			// $ghost_font_size = '13';
			// $ghostImagex = 140;
			// $ghostImagey = 276;
			// $ghostImageWidth = 55;//68
			// $ghostImageHeight = 9.8;
			$name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
			$tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
			// $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');
			// $pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);			
			// $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
			$ghost_font_size = '12';
            $ghostImagex = 144;
            $ghostImagey = 276.3;
            $ghostImageWidth = 39.405983333;
            $ghostImageHeight = 8;
            $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
            $tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
            $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');			
            $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
			$pdfBig->setPageMark();
			$pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
			$pdf->setPageMark();
			
			$serial_no=$GUID=$studentData[0];
			//qr code    
			$dt = date("_ymdHis");
			$str=$GUID.$dt;
			$codeContents = $QR_Output."\n\n".strtoupper(md5($str));
			$encryptedString = strtoupper(md5($str));
			$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
			$qrCodex = 176; //
			$qrCodey = 46; //
			$qrCodeWidth =21;
			$qrCodeHeight = 21;
			$ecc = 'L';
			$pixel_Size = 1;
			$frame_Size = 1;  
            
			\PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
			$pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
			$pdfBig->setPageMark();			
			$pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
			$pdf->setPageMark(); 						
			
            $COE = public_path().'\\'.$subdomain[0].'\backend\canvas\images\sdm_coe.png';
            // $COE = public_path().'\minosha\backend\canvas\bg_images\sdm_coe.png';
            $COE_x = 150;
            $COE_y = 253;
            $COE_Width = 26.458333333;
            $COE_Height = 18.785416667;
            $pdfBig->image($COE,$COE_x,$COE_y,$COE_Width,$COE_Height,"",'','L',true,3600);
            $pdfBig->setPageMark();
			$pdf->image($COE,$COE_x,$COE_y,$COE_Width,$COE_Height,"",'','L',true,3600);
            $pdf->setPageMark();
			
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
			$barcodex = 145;
			$barcodey = 65;
			$barcodeWidth = 52;
			$barodeHeight = 13;
			//$pdf->SetAlpha(1);
			//$pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			//$pdfBig->SetAlpha(1);
			//$pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			
			
			$str = $nameOrg;
			$str = strtoupper(preg_replace('/\s+/', '', $str)); 			
			$microlinestr=$str;
			// $pdf->SetFont($timesb, '', 1.3, '', false);
			// $pdf->SetTextColor(0, 0, 0);
			// $pdf->SetXY(176.5, 73);        
			// $pdf->Cell(20, 0, $microlinestr, 0, false, 'C');    
			
			$pdfBig->SetFont($timesb, '', 1.3, '', false);
			$pdfBig->SetTextColor(0, 0, 0);
			$pdfBig->SetXY(176.5, 63);        
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

				$this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'sdmGC',$admin_id,$card_serial_no);
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
			$this->updateCardNo('sdmGC',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
        }
        $msg = '';
        
        $file_name =  str_replace("/", "_",'sdmGC'.date("Ymdhms")).'.pdf';        
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $filename = public_path().'/backend/tcpdf/examples/'.$file_name;        
        $pdfBig->output($filename,'F');
        if($previewPdf!=1){
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name="sdmGC";
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
