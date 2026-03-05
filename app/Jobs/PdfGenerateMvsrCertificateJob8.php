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
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Helper;
class PdfGenerateMvsrCertificateJob8
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
        // dd($pdf_data );   
        $studentDataOrg=$pdf_data['studentDataOrg'];
        $subjectDataOrg=$pdf_data['subjectDataOrg'];
		
        $subjectsMark=$pdf_data['subjectsMark'];
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

		// Start Update code for batchwise genration
		$loader_data =CoreHelper::getLoaderJson($pdf_data['loader_token']);
        

		// Log an error
		//\Log::info('loader error', ['loader_data' => $loader_data]);

		if(!empty($loader_data) && isset($loader_data['generatedCertificates'])){

			$generated_documents=$loader_data['generatedCertificates'];  

		}else{
			$generated_documents=0;  
		}
	
		
		//\Log::info('generated_documents error', ['generated_documents' => $generated_documents]);

		if($generated_documents == 0){
			Session::forget('pdf_data_obj');
			$pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
			$pdfBig->SetCreator(PDF_CREATOR);
			$pdfBig->SetAuthor('TCPDF');
			$pdfBig->SetTitle('Certificate');
			$pdfBig->SetSubject('');
			// Session::put('pdf_obj', $pdfBig);


		}else{ 
			 if(Session::get('pdf_data_obj') != null){
			$pdfBig = Session::get('pdf_data_obj');   
			}
		}
        // $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        // $pdfBig->SetCreator(PDF_CREATOR);
        // $pdfBig->SetAuthor('TCPDF');
        // $pdfBig->SetTitle('Certificate');
        // $pdfBig->SetSubject('');

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
         $Cambria_Bold_11 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\cambriab.ttf', 'TrueTypeUnicode', '', 96);
        // $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        // $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        // $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        // $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        // $oef = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\old-english-five.ttf', 'TrueTypeUnicode', '', 96);

        $preview_serial_no=1;
		//$card_serial_no="";
        $log_serial_no = 1;
        // $cardDetails=$this->getNextCardNo('MVSR');
        // $card_serial_no=$cardDetails->next_serial_no;
        // $generated_documents=0;  //for custom loader

		$subjectsArr = array();
        if($subjectDataOrg) {
            foreach ($subjectDataOrg as $element) {
                $subjectsArr[$element[0]][] = $element;
            }
        }

		if($studentDataOrg&&!empty($studentDataOrg)){
			foreach ($studentDataOrg as $studentData) {
			
				// if($card_serial_no>999999&&$previewPdf!=1){
				// 	echo "<h5>Your card series ended...!</h5>";
				// 	exit;
				// }
				//For Custom Loader
				$startTimeLoader =  date('Y-m-d H:i:s');    
				$high_res_bg="Transcript_bg.jpg"; // bestiu_pdc_bg, TranscriptData.jpg
				$low_res_bg="Transcript_bg.jpg";
				$pdfBig->AddPage();
				$pdfBig->SetFont($arialb, '', 8, '', false);
				//set background image
				$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;   

				if($previewPdf==1){
					if($previewWithoutBg!=1){
						$pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
					}
					$date_font_size = '11';
					$date_nox = 13;
					$date_noy = 40;
					$date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
					$pdfBig->SetFont($arialb, '', $date_font_size, '', false);
					$pdfBig->SetTextColor(192,192,192);
					$pdfBig->SetXY($date_nox, $date_noy);
					$pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
					$pdfBig->SetTextColor(0,0,0,100,false,'');
					$pdfBig->SetFont($arialb, '', 9, '', false);
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
				$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$low_res_bg;
				if($previewPdf!=1){
					$pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
				}
				//$pdf->setPageMark();
				$pdf->setPageMark();
				//$pdfBig->setPageMark();
				//Table's Titles                        			
				$unique_id=trim($studentData[0]);
				$Name=trim($studentData[1]);
				$Fathers_Name=trim($studentData[2]);
				$monthYearExam=trim($studentData[3]);
				$ref_no=trim($studentData[4]);
				$course_name=trim($studentData[5]);
				$branch=trim($studentData[6]);
				$gender=trim($studentData[7]);
				$Roll_No=trim($studentData[8]);
				$hall_ticket_no=trim($studentData[8]);
				$collegeCode=trim($studentData[9]);
				$classAwarded=trim($studentData[10]);
				$serial=trim($studentData[11]);
				$CGPASecured=trim($studentData[12]);
				$CGPARegister=trim($studentData[13]);
				$CreditSecured=trim($studentData[14]);
				$place=trim($studentData[15]);
				$Result_DATE=trim($studentData[16]);
				$Result_DATE=$this->convertExcelDate($studentData[16]);

				$Photo=trim($studentData[17]);
				$marksheet=trim($studentData[18]);
				$note=trim($studentData[19]);
				$course_year=trim($studentData[20]);
				$Mother_Name=trim($studentData[21]);
				$Mother_Name=trim($studentData[21]);
				$CGPA_Word=trim($studentData[22]);
				$Percentage_marks=trim($studentData[23]);
				$division=trim($studentData[24]);
				
			
				//Start pdfBig
				$left_pos=15.5;
				$left_pos_two=125;

                $pdfBig->SetFont($Cambria_Bold_11, 'B', 10, '', false); 
				$pdfBig->SetXY(13, 37);             
                $pdfBig->MultiCell(175, 2, $studentData[25], 0, 'C', 0, 1, '', '', true);       
                $pdfBig->SetXY(13, $pdfBig->GetY());             
                $pdfBig->MultiCell(175, 2, $studentData[26], 0, 'C', 0, 1, '', '', true);
                 $X=12;
                $pdfBig->SetXY($X, $pdfBig->GetY()); 
				$pdfBig->SetFont($arialb, '', 7, '', false); 
				// $pdfBig->SetXY(12, 42.7);
			
                 $pdfBig->MultiCell(175, 2,  $course_name.' ['.$course_year.']', 0, 'C', 0, 1, '', '', true);
			    $pdfBig->SetXY($X, $pdfBig->GetY());
				// $pdfBig->Cell(186, 5, 'Examination : '.$monthYearExam, 0, false, 'C');
                $pdfBig->MultiCell(175, 5, 'Examination : '.$monthYearExam, 0, 'C', 0, 1, '', '', true);
				
                $left_pos=15.5;
				$left_pos_two=125;
                $firstLineY=$pdfBig->GetY();

				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos, $firstLineY);
                $pdfBig->MultiCell(0, 4, 'Ref No. ', 0, 'L', 0, 1, '', '', true);
				$pdfBig->SetXY(36,  $firstLineY);			
                $pdfBig->MultiCell(0, 4, ': ', 0, 'L', 0, 1, '', '', true);	
				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(39,  $firstLineY);		
                $pdfBig->MultiCell(0, 4, $ref_no, 0, 'L', 0, 1, '', '', true);



			    $secondLineY=$pdfBig->GetY();
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos,$secondLineY);		
                $pdfBig->MultiCell(0, 4, 'Roll No ', 0, 'L', 0, 1, '', '', true);
				$pdfBig->SetXY(36, $secondLineY);		
				$pdfBig->MultiCell(0, 4, ': ', 0, 'L', 0, 1, '', '', true);	
				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(39, $secondLineY);		
                $pdfBig->MultiCell(0, 4, $Roll_No, 0, 'L', 0, 1, '', '', true);


				$ThirdLineY=$pdfBig->GetY();			
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos, $ThirdLineY);		
			
                $pdfBig->MultiCell(0, 4, 'Name', 0, 'L', 0, 1, '', '', true);
				$pdfBig->SetXY(36, $ThirdLineY);		
			    $pdfBig->MultiCell(0, 4, ': ', 0, 'L', 0, 1, '', '', true);	
				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(39, $ThirdLineY);
                $pdfBig->MultiCell(0, 4, $Name, 0, 'L', 0, 1, '', '', true);
				
                $ForthLineY=$pdfBig->GetY();	
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos,   $ForthLineY);		
                $pdfBig->MultiCell(0, 4, 'Father`s Name', 0, 'L', 0, 1, '', '', true);
				$pdfBig->SetXY(36,   $ForthLineY);		
				$pdfBig->MultiCell(0, 4, ': ', 0, 'L', 0, 1, '', '', true);	
				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(39,   $ForthLineY);		
                 $pdfBig->MultiCell(0, 4, $Fathers_Name, 0, 'L', 0, 1, '', '', true);



							
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos_two, $firstLineY);		
				$pdfBig->Cell(0, 4, "Mother's Name", 0, false, 'L');
				$pdfBig->SetXY(145, $firstLineY);		
				$pdfBig->Cell(0, 4, ': ', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(147, $firstLineY);		
				$pdfBig->Cell(0, 4, $Mother_Name, 0, false, 'L');
				
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos_two,  $secondLineY);		
				$pdfBig->Cell(0, 4, 'Course', 0, false, 'L');
				$pdfBig->SetXY(145,  $secondLineY);		
				$pdfBig->Cell(0, 4, ': ', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(147,  $secondLineY);		
				$pdfBig->Cell(0, 4, $course_name, 0, false, 'L');
				
				$pdfBig->SetFont($arial, '', 7, '', false);
				$pdfBig->SetXY($left_pos_two, $ThirdLineY);		
				$pdfBig->Cell(0, 0, 'Branch', 0, false, 'L');
				$pdfBig->SetXY(145, $ThirdLineY);		
				$pdfBig->Cell(0, 0, ': ', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(147, $ThirdLineY);		
				// $pdfBig->Cell(0, 0, $branch, 0, false, 'L');
				$pdfBig->MultiCell(45, 7, $branch, 0, 'L', 0, 0, '', '', true, 0, true);

				$pdfBig->Ln();			
				$tableY = $ForthLineY+5;
				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(15.5, $tableY);	
				$pdfBig->MultiCell(8, 7, 'SL.<br>NO.', 1, 'C', 0, 0, '', '', true, 0, true);

				$pdfBig->SetXY($pdfBig->getX(), $tableY);
				$pdfBig->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
				
				$pdfBig->SetXY($pdfBig->getX(), $tableY);
				$pdfBig->MultiCell(54, 7, 'COURSE TITLE', 1, 'C', 0, 0, '', '', true, 0, true);
				
				$pdfBig->SetXY($pdfBig->getX(), $tableY);
				$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(13.2, 7, 'GRADE<br>LETTER', 1, 'C', 0, 0, '', '', true, 0, true);
				
				$pdfBig->SetXY($pdfBig->getX(), $tableY);
				$pdfBig->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
				$pdfBig->MultiCell(13.3, 7, 'CREDITS', 1, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');


				$pdfBig->SetFont($arialb, '', 7, '', false);
				$pdfBig->SetXY(106, $tableY);	
				$pdfBig->MultiCell(8, 7, 'SL.<br>NO.', 1, 'C', 0, 0, '', '', true, 0, true);

				$pdfBig->SetXY($pdfBig->getX(), $tableY);
				$pdfBig->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
				
				$pdfBig->SetXY($pdfBig->getX(), $tableY);
				$pdfBig->MultiCell(54, 7, 'COURSE TITLE', 1, 'C', 0, 0, '', '', true, 0, true);
				
				$pdfBig->SetXY($pdfBig->getX(), $tableY);
				$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(13.2, 7, 'GRADE<br>LETTER', 1, 'C', 0, 0, '', '', true, 0, true);
				
				$pdfBig->SetXY($pdfBig->getX(), $tableY);
				$pdfBig->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
				$pdfBig->MultiCell(13.3, 7, 'CREDITS', 1, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');

				$pdfBig->ln();
				$subjectsData=$subjectsArr[$hall_ticket_no];
				

				//Separate semesters 
				$subjects = array();
				foreach ($subjectsData as $element) {
					$subjects[$element[1]][] = $element;
				}
				ksort($subjects);
				// $tableStart = 78; 
				// $tableSecondStart = 78; 
              $tableSecondStart=  $pdfBig->GetY();
				$tableStart = $pdfBig->GetY();
				// $IndividualDataIndex = 9; 
				// $tableY = 80;

				$leftCurrentY = $tableStart; // track left column
				$rightCurrentY = $tableSecondStart; // track right column

				foreach($subjects as $term => $term_array){
					// echo "<pre>";
					// print_r($term_array);

					

					$totalHeight = 0;
					// echo "<br>Term: ".$term;
					if ($term == "I SEMESTER" || $term == "III SEMESTER" || $term == "V SEMESTER" || $term == "VII SEMESTER" ) {
						$pdfBig->SetFont($arialb, '', 5, '', false);
						$pdfBig->SetXY(15.5, $leftCurrentY);
						$pdfBig->MultiCell(88.5, 3, $term .' (SGPA : '.$term_array[0][7].')', 'TB', 'C', 0, 0, '', '', true, 0, true);
						// $pdfBig->Cell(88.4, 3.7, $term.' (SGPA : '.$SEM1_SGPA.')', 'B', false, 'C');
						$pdfBig->SetFont($arial, '', 5, '', false);
						$pdfBig->ln();

						$subjectTableHeight = 2.7;

						$blankCount = 10- count($term_array);
					
						// $count = count($term_array);
						// if ($count < 10) {
						//     for ($i = $count + 1; $i <= 10; $i++) {
						//         // Adjust structure of blank array as needed
						//         $term_array[] = [$i]; // here adding array with only increment number
						//     }
						// }
						
						$tableBorderStartY = $pdfBig->getY(); 
						foreach($term_array as $key => $val){
							
							$pdfBig->startTransaction();
							$pdfBig->SetFont($arial, '', 6, '', false);  
							// get the number of lines
							$lines = $pdfBig->MultiCell(54, 0, trim($val[3]), 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
							$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object
							if($lines>1){
								$subjectTableHeight = 5.4;
							} else {
								$subjectTableHeight = 2.7;
							}
							
							$pdfBig->SetFont($arial, '', 6, '', false);
							$pdfBig->SetXY(15.5, $pdfBig->getY());	
							$pdfBig->MultiCell(8, $subjectTableHeight, $val[2], '', 'C', 0, 0, '', '', true, 0, true);
							$pdfBig->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');
							$pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY());
							$pdfBig->MultiCell(54, $subjectTableHeight,trim($val[3]), '', 'L', 0, 0, '', '', true, 0, true);
							
							$pdfBig->setCellPaddings( $left = '5.6', $top = '', $right = '', $bottom = '');
							$pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY());
							$pdfBig->MultiCell(13.2, $subjectTableHeight, $val[5], '', 'L', 0, 0, '', '', true, 0, true);
							$pdfBig->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');

							// $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY());
							// $pdfBig->MultiCell(13.2, $subjectTableHeight, $val[5], '', 'C', 0, 0, '', '', true, 0, true);
							
							$pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY());
							$pdfBig->MultiCell(13.3, $subjectTableHeight, $val[6], '', 'C', 0, 0, '', '', true, 0, true);
							$totalHeight += $subjectTableHeight;
							$pdfBig->ln();
						}

						
						
						$leftCurrentY = $pdfBig->GetY();
						// $tableStart = $pdfBig->GetY();
						
					} else if ($term == "II SEMESTER" || $term == "IV SEMESTER" || $term == "VI SEMESTER" || $term == "VIII SEMESTER" ) {
						// $tableSecondStart
						$pdfBig->SetFont($arialb, '', 5, '', false);
						$pdfBig->SetXY(106, $rightCurrentY);
						$pdfBig->MultiCell(88.5, 3, $term .' (SGPA : '.$term_array[0][7].')', 'TB', 'C', 0, 0, '', '', true, 0, true);
						// $pdfBig->Cell(88.4, 3.7, $term.' (SGPA : '.$SEM1_SGPA.')', 'B', false, 'C');
						$pdfBig->SetFont($arial, '', 5, '', false);
						$pdfBig->ln();

						$subjectTableHeight = 2.7;

						$blankCount = 10- count($term_array);
					
						// $count = count($term_array);
						// if ($count < 10) {
						//     for ($i = $count + 1; $i <= 10; $i++) {
						//         // Adjust structure of blank array as needed
						//         $term_array[] = [$i]; // here adding array with only increment number
						//     }
						// }

						$tableBorderStart = $pdfBig->getY(); 
						foreach($term_array as $key => $val){
							
							$pdfBig->startTransaction();
							$pdfBig->SetFont($arial, '', 6, '', false);  
							// get the number of lines
							$lines = $pdfBig->MultiCell(54, 0, trim($val[3]), 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
							$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object
							if($lines>1){
								$subjectTableHeight = 5.4;
							} else {
								$subjectTableHeight = 2.7;
							}
							
							
							$pdfBig->SetFont($arial, '', 6, '', false);
							$pdfBig->SetXY(106, $pdfBig->getY());	
							$pdfBig->MultiCell(8, $subjectTableHeight, $val[2], '0', 'C', 0, 0, '', '', true, 0, true);

							$pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY());
							$pdfBig->MultiCell(54, $subjectTableHeight, trim($val[3]), '0', 'L', 0, 0, '', '', true, 0, true);
							
							// $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY());
							// $pdfBig->MultiCell(13.2, $subjectTableHeight, $val[5], '0', 'C', 0, 0, '', '', true, 0, true);
							$pdfBig->setCellPaddings( $left = '5.6', $top = '', $right = '', $bottom = '');
							$pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY());
							$pdfBig->MultiCell(13.2, $subjectTableHeight, $val[5], '', 'L', 0, 0, '', '', true, 0, true);
							$pdfBig->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');

							$pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY());
							$pdfBig->MultiCell(13.3, $subjectTableHeight, $val[6], '0', 'C', 0, 0, '', '', true, 0, true);
							
							$totalHeight += $subjectTableHeight;
							$pdfBig->ln();
						
						}
						
						$rightCurrentY = $pdfBig->GetY();
						// $tableSecondStart = $pdfBig->GetY();
					}   

					if($term == "II SEMESTER" || $term == "IV SEMESTER" || $term == "VI SEMESTER" || $term == "VIII SEMESTER" ) {
						if($leftCurrentY > $rightCurrentY) {
							$leftCurrentY = $leftCurrentY;
							$rightCurrentY = $leftCurrentY;
						} else {
							$leftCurrentY = $rightCurrentY; 
							$rightCurrentY = $rightCurrentY;
						}
					}

					// echo $totalHeight;
					// echo "<br>";
					// echo $leftCurrentY;
					// echo "<br>";
					
					$totalHeight = $totalHeight;
					// if($term == "VIII SEMESTER" ) {
					//     $totalHeight = $totalHeight+0.5;
					// }
					// echo $totalHeight;

					$pdfBig->SetFont($arial, '', 6, '', false);
					$pdfBig->SetXY(15.5, $tableBorderStartY);	
					$pdfBig->MultiCell(8, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);

					$pdfBig->SetXY($pdfBig->getX(), $tableBorderStartY);
					$pdfBig->MultiCell(54, $totalHeight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
					
					$pdfBig->SetXY($pdfBig->getX(), $tableBorderStartY);
					$pdfBig->MultiCell(13.2, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);
					
					$pdfBig->SetXY($pdfBig->getX(), $tableBorderStartY);
					$pdfBig->MultiCell(13.3, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);
					// $tableBorderStartY = 0;
					// $totalHeight = 0;

					$pdfBig->SetFont($arial, '', 6, '', false);
					$pdfBig->SetXY(106, $tableBorderStartY);	
					$pdfBig->MultiCell(8, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);

					$pdfBig->SetXY($pdfBig->getX(), $tableBorderStartY);
					$pdfBig->MultiCell(54, $totalHeight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
					
					$pdfBig->SetXY($pdfBig->getX(), $tableBorderStartY);
					$pdfBig->MultiCell(13.2, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);
					
					$pdfBig->SetXY($pdfBig->getX(), $tableBorderStartY);
					$pdfBig->MultiCell(13.3, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);


					

					
					

					
					

				
				}
				$bottomY = $leftCurrentY-3;
				
				// $y_axis=226.5;
				$y_axis=$bottomY;
				$pdfBig->SetXY(15.5, $y_axis+3);	
				$pdfBig->MultiCell(179, 15, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				

				$pdfBig->SetXY(16.5, $y_axis+5);
				$pdfBig->MultiCell(0, 0, 'Cumulative Grade Point Average (CGPA) ', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(70.5, $y_axis+5);
				$pdfBig->MultiCell(0, 0, ': '.$CGPASecured, 0, 'L', 0, 0, '', '', true, 0, true);

				$pdfBig->SetXY(16.5, $y_axis+9);
				$pdfBig->MultiCell(0, 0, 'Percentage of Marks ', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(70.5, $y_axis+9);
				$pdfBig->MultiCell(0, 0, ': '.$Percentage_marks, 0, 'L', 0, 0, '', '', true, 0, true);

				$pdfBig->SetXY(16.5, $y_axis+13);
				$pdfBig->MultiCell(0, 0, 'Division ', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(70.5, $y_axis+13);
				$pdfBig->MultiCell(0, 0, ': '.$division, 0, 'L', 0, 0, '', '', true, 0, true);

				$pdfBig->SetXY(125.5, $y_axis+5);
				$pdfBig->MultiCell(0, 0, 'CGPA in Words ', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(145.5, $y_axis+5);
				$pdfBig->MultiCell(0, 0, ': '.$CGPA_Word, 0, 'L', 0, 0, '', '', true, 0, true);

				$pdfBig->SetXY(125.5, $y_axis+9);
				$pdfBig->MultiCell(0, 0, 'The date of declaration of result ', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(162, $y_axis+9);
				$pdfBig->MultiCell(0, 0, ': '.$Result_DATE, 0, 'L', 0, 0, '', '', true, 0, true);

				$pdfBig->SetFont($arialb, '', 10, '', false); 
				$pdfBig->SetTextColor(0, 0, 0);    
				$pdfBig->SetXY(85, 268);
				$pdfBig->Cell(0, 0, 'Section-Incharge', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 10, '', false); 
				$pdfBig->SetXY(145, 261.5);
				$pdfBig->Cell(0, 0, 'Controller of Examinations', 0, false, 'L');  
				
				//End pdfBig 
				
				//Start pdf
				$left_pos=15.5;
                $left_pos_two=125;

                $pdf->SetFont($Cambria_Bold_11, 'B', 10, '', false); 
                $pdf->SetXY(13, 37);             
                $pdf->MultiCell(175, 2, $studentData[25], 0, 'C', 0, 1, '', '', true);       
                $pdf->SetXY(13, $pdf->GetY());             
                $pdf->MultiCell(175, 2, $studentData[26], 0, 'C', 0, 1, '', '', true);
                 $X=12;
                $pdf->SetXY($X, $pdf->GetY()); 
                $pdf->SetFont($arialb, '', 7, '', false); 
                // $pdf->SetXY(12, 42.7);
            
                 $pdf->MultiCell(175, 2,  $course_name.' ['.$course_year.']', 0, 'C', 0, 1, '', '', true);
                $pdf->SetXY($X, $pdf->GetY());
                // $pdf->Cell(186, 5, 'Examination : '.$monthYearExam, 0, false, 'C');
                $pdf->MultiCell(175, 5, 'Examination : '.$monthYearExam, 0, 'C', 0, 1, '', '', true);
                
                $left_pos=15.5;
                $left_pos_two=125;
                $firstLineY=$pdf->GetY();

                $pdf->SetFont($arial, '', 8, '', false);
                $pdf->SetXY($left_pos, $firstLineY);
                $pdf->MultiCell(0, 4, 'Ref No. ', 0, 'L', 0, 1, '', '', true);
                $pdf->SetXY(36,  $firstLineY);           
                $pdf->MultiCell(0, 4, ': ', 0, 'L', 0, 1, '', '', true); 
                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(39,  $firstLineY);       
                $pdf->MultiCell(0, 4, $ref_no, 0, 'L', 0, 1, '', '', true);



                $secondLineY=$pdf->GetY();
                $pdf->SetFont($arial, '', 8, '', false);
                $pdf->SetXY($left_pos,$secondLineY);     
                $pdf->MultiCell(0, 4, 'Roll No ', 0, 'L', 0, 1, '', '', true);
                $pdf->SetXY(36, $secondLineY);       
                $pdf->MultiCell(0, 4, ': ', 0, 'L', 0, 1, '', '', true); 
                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(39, $secondLineY);       
                $pdf->MultiCell(0, 4, $Roll_No, 0, 'L', 0, 1, '', '', true);


                $ThirdLineY=$pdf->GetY();            
                $pdf->SetFont($arial, '', 8, '', false);
                $pdf->SetXY($left_pos, $ThirdLineY);     
            
                $pdf->MultiCell(0, 4, 'Name', 0, 'L', 0, 1, '', '', true);
                $pdf->SetXY(36, $ThirdLineY);        
                $pdf->MultiCell(0, 4, ': ', 0, 'L', 0, 1, '', '', true); 
                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(39, $ThirdLineY);
                $pdf->MultiCell(0, 4, $Name, 0, 'L', 0, 1, '', '', true);
                
                $ForthLineY=$pdf->GetY();    
                $pdf->SetFont($arial, '', 8, '', false);
                $pdf->SetXY($left_pos,   $ForthLineY);       
                $pdf->MultiCell(0, 4, 'Father`s Name', 0, 'L', 0, 1, '', '', true);
                $pdf->SetXY(36,   $ForthLineY);      
                $pdf->MultiCell(0, 4, ': ', 0, 'L', 0, 1, '', '', true); 
                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(39,   $ForthLineY);      
                 $pdf->MultiCell(0, 4, $Fathers_Name, 0, 'L', 0, 1, '', '', true);



                            
                $pdf->SetFont($arial, '', 8, '', false);
                $pdf->SetXY($left_pos_two, $firstLineY);     
                $pdf->Cell(0, 4, "Mother's Name", 0, false, 'L');
                $pdf->SetXY(145, $firstLineY);       
                $pdf->Cell(0, 4, ': ', 0, false, 'L');
                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(147, $firstLineY);       
                $pdf->Cell(0, 4, $Mother_Name, 0, false, 'L');
                
                $pdf->SetFont($arial, '', 8, '', false);
                $pdf->SetXY($left_pos_two,  $secondLineY);       
                $pdf->Cell(0, 4, 'Course', 0, false, 'L');
                $pdf->SetXY(145,  $secondLineY);     
                $pdf->Cell(0, 4, ': ', 0, false, 'L');
                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(147,  $secondLineY);     
                $pdf->Cell(0, 4, $course_name, 0, false, 'L');
                
                $pdf->SetFont($arial, '', 7, '', false);
                $pdf->SetXY($left_pos_two, $ThirdLineY);     
                $pdf->Cell(0, 0, 'Branch', 0, false, 'L');
                $pdf->SetXY(145, $ThirdLineY);       
                $pdf->Cell(0, 0, ': ', 0, false, 'L');
                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(147, $ThirdLineY);       
                // $pdf->Cell(0, 0, $branch, 0, false, 'L');
                $pdf->MultiCell(45, 7, $branch, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->Ln();          
                $tableY = $ForthLineY+5;
                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(15.5, $tableY);  
                $pdf->MultiCell(8, 7, 'SL.<br>NO.', 1, 'C', 0, 0, '', '', true, 0, true);

                $pdf->SetXY($pdf->getX(), $tableY);
                $pdf->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
                
                $pdf->SetXY($pdf->getX(), $tableY);
                $pdf->MultiCell(54, 7, 'COURSE TITLE', 1, 'C', 0, 0, '', '', true, 0, true);
                
                $pdf->SetXY($pdf->getX(), $tableY);
                $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
                $pdf->MultiCell(13.2, 7, 'GRADE<br>LETTER', 1, 'C', 0, 0, '', '', true, 0, true);
                
                $pdf->SetXY($pdf->getX(), $tableY);
                $pdf->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
                $pdf->MultiCell(13.3, 7, 'CREDITS', 1, 'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');


                $pdf->SetFont($arialb, '', 7, '', false);
                $pdf->SetXY(106, $tableY);   
                $pdf->MultiCell(8, 7, 'SL.<br>NO.', 1, 'C', 0, 0, '', '', true, 0, true);

                $pdf->SetXY($pdf->getX(), $tableY);
                $pdf->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
                
                $pdf->SetXY($pdf->getX(), $tableY);
                $pdf->MultiCell(54, 7, 'COURSE TITLE', 1, 'C', 0, 0, '', '', true, 0, true);
                
                $pdf->SetXY($pdf->getX(), $tableY);
                $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
                $pdf->MultiCell(13.2, 7, 'GRADE<br>LETTER', 1, 'C', 0, 0, '', '', true, 0, true);
                
                $pdf->SetXY($pdf->getX(), $tableY);
                $pdf->setCellPaddings( $left = '', $top = '2', $right = '', $bottom = '');
                $pdf->MultiCell(13.3, 7, 'CREDITS', 1, 'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');

                $pdf->ln();
                $subjectsData=$subjectsArr[$hall_ticket_no];
                

                //Separate semesters 
                $subjects = array();
                foreach ($subjectsData as $element) {
                    $subjects[$element[1]][] = $element;
                }
                ksort($subjects);
                // $tableStart = 78; 
                // $tableSecondStart = 78; 
              $tableSecondStart=  $pdf->GetY();
                $tableStart = $pdf->GetY();
                // $IndividualDataIndex = 9; 
                // $tableY = 80;

                $leftCurrentY = $tableStart; // track left column
                $rightCurrentY = $tableSecondStart; // track right column

                foreach($subjects as $term => $term_array){
                    // echo "<pre>";
                    // print_r($term_array);

                    

                    $totalHeight = 0;
                    // echo "<br>Term: ".$term;
                    if ($term == "I SEMESTER" || $term == "III SEMESTER" || $term == "V SEMESTER" || $term == "VII SEMESTER" ) {
                        $pdf->SetFont($arialb, '', 5, '', false);
                        $pdf->SetXY(15.5, $leftCurrentY);
                        $pdf->MultiCell(88.5, 3, $term .' (SGPA : '.$term_array[0][7].')', 'TB', 'C', 0, 0, '', '', true, 0, true);
                        // $pdf->Cell(88.4, 3.7, $term.' (SGPA : '.$SEM1_SGPA.')', 'B', false, 'C');
                        $pdf->SetFont($arial, '', 5, '', false);
                        $pdf->ln();

                        $subjectTableHeight = 2.7;

                        $blankCount = 10- count($term_array);
                    
                        // $count = count($term_array);
                        // if ($count < 10) {
                        //     for ($i = $count + 1; $i <= 10; $i++) {
                        //         // Adjust structure of blank array as needed
                        //         $term_array[] = [$i]; // here adding array with only increment number
                        //     }
                        // }
                        
                        $tableBorderStartY = $pdf->getY(); 
                        foreach($term_array as $key => $val){
                            
                            $pdf->startTransaction();
                            $pdf->SetFont($arial, '', 6, '', false);  
                            // get the number of lines
                            $lines = $pdf->MultiCell(54, 0, trim($val[3]), 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                            $pdf=$pdf->rollbackTransaction(); // restore previous object
                            if($lines>1){
                                $subjectTableHeight = 5.4;
                            } else {
                                $subjectTableHeight = 2.7;
                            }
                            
                            $pdf->SetFont($arial, '', 6, '', false);
                            $pdf->SetXY(15.5, $pdf->getY());  
                            $pdf->MultiCell(8, $subjectTableHeight, $val[2], '', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');
                            $pdf->SetXY($pdf->getX(), $pdf->getY());
                            $pdf->MultiCell(54, $subjectTableHeight,trim($val[3]), '', 'L', 0, 0, '', '', true, 0, true);
                            
                            $pdf->setCellPaddings( $left = '5.6', $top = '', $right = '', $bottom = '');
                            $pdf->SetXY($pdf->getX(), $pdf->getY());
                            $pdf->MultiCell(13.2, $subjectTableHeight, $val[5], '', 'L', 0, 0, '', '', true, 0, true);
                            $pdf->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');

                            // $pdf->SetXY($pdf->getX(), $pdf->getY());
                            // $pdf->MultiCell(13.2, $subjectTableHeight, $val[5], '', 'C', 0, 0, '', '', true, 0, true);
                            
                            $pdf->SetXY($pdf->getX(), $pdf->getY());
                            $pdf->MultiCell(13.3, $subjectTableHeight, $val[6], '', 'C', 0, 0, '', '', true, 0, true);
                            $totalHeight += $subjectTableHeight;
                            $pdf->ln();
                        }

                        
                        
                        $leftCurrentY = $pdf->GetY();
                        // $tableStart = $pdf->GetY();
                        
                    } else if ($term == "II SEMESTER" || $term == "IV SEMESTER" || $term == "VI SEMESTER" || $term == "VIII SEMESTER" ) {
                        // $tableSecondStart
                        $pdf->SetFont($arialb, '', 5, '', false);
                        $pdf->SetXY(106, $rightCurrentY);
                        $pdf->MultiCell(88.5, 3, $term .' (SGPA : '.$term_array[0][7].')', 'TB', 'C', 0, 0, '', '', true, 0, true);
                        // $pdf->Cell(88.4, 3.7, $term.' (SGPA : '.$SEM1_SGPA.')', 'B', false, 'C');
                        $pdf->SetFont($arial, '', 5, '', false);
                        $pdf->ln();

                        $subjectTableHeight = 2.7;

                        $blankCount = 10- count($term_array);
                    
                        // $count = count($term_array);
                        // if ($count < 10) {
                        //     for ($i = $count + 1; $i <= 10; $i++) {
                        //         // Adjust structure of blank array as needed
                        //         $term_array[] = [$i]; // here adding array with only increment number
                        //     }
                        // }

                        $tableBorderStart = $pdf->getY(); 
                        foreach($term_array as $key => $val){
                            
                            $pdf->startTransaction();
                            $pdf->SetFont($arial, '', 6, '', false);  
                            // get the number of lines
                            $lines = $pdf->MultiCell(54, 0, trim($val[3]), 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                            $pdf=$pdf->rollbackTransaction(); // restore previous object
                            if($lines>1){
                                $subjectTableHeight = 5.4;
                            } else {
                                $subjectTableHeight = 2.7;
                            }
                            
                            
                            $pdf->SetFont($arial, '', 6, '', false);
                            $pdf->SetXY(106, $pdf->getY());   
                            $pdf->MultiCell(8, $subjectTableHeight, $val[2], '0', 'C', 0, 0, '', '', true, 0, true);

                            $pdf->SetXY($pdf->getX(), $pdf->getY());
                            $pdf->MultiCell(54, $subjectTableHeight, trim($val[3]), '0', 'L', 0, 0, '', '', true, 0, true);
                            
                            // $pdf->SetXY($pdf->getX(), $pdf->getY());
                            // $pdf->MultiCell(13.2, $subjectTableHeight, $val[5], '0', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->setCellPaddings( $left = '5.6', $top = '', $right = '', $bottom = '');
                            $pdf->SetXY($pdf->getX(), $pdf->getY());
                            $pdf->MultiCell(13.2, $subjectTableHeight, $val[5], '', 'L', 0, 0, '', '', true, 0, true);
                            $pdf->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');

                            $pdf->SetXY($pdf->getX(), $pdf->getY());
                            $pdf->MultiCell(13.3, $subjectTableHeight, $val[6], '0', 'C', 0, 0, '', '', true, 0, true);
                            
                            $totalHeight += $subjectTableHeight;
                            $pdf->ln();
                        
                        }
                        
                        $rightCurrentY = $pdf->GetY();
                        // $tableSecondStart = $pdf->GetY();
                    }   

                    if($term == "II SEMESTER" || $term == "IV SEMESTER" || $term == "VI SEMESTER" || $term == "VIII SEMESTER" ) {
                        if($leftCurrentY > $rightCurrentY) {
                            $leftCurrentY = $leftCurrentY;
                            $rightCurrentY = $leftCurrentY;
                        } else {
                            $leftCurrentY = $rightCurrentY; 
                            $rightCurrentY = $rightCurrentY;
                        }
                    }

                    // echo $totalHeight;
                    // echo "<br>";
                    // echo $leftCurrentY;
                    // echo "<br>";
                    
                    $totalHeight = $totalHeight;
                    // if($term == "VIII SEMESTER" ) {
                    //     $totalHeight = $totalHeight+0.5;
                    // }
                    // echo $totalHeight;

                    $pdf->SetFont($arial, '', 6, '', false);
                    $pdf->SetXY(15.5, $tableBorderStartY);   
                    $pdf->MultiCell(8, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetXY($pdf->getX(), $tableBorderStartY);
                    $pdf->MultiCell(54, $totalHeight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
                    
                    $pdf->SetXY($pdf->getX(), $tableBorderStartY);
                    $pdf->MultiCell(13.2, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);
                    
                    $pdf->SetXY($pdf->getX(), $tableBorderStartY);
                    $pdf->MultiCell(13.3, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);
                    // $tableBorderStartY = 0;
                    // $totalHeight = 0;

                    $pdf->SetFont($arial, '', 6, '', false);
                    $pdf->SetXY(106, $tableBorderStartY);    
                    $pdf->MultiCell(8, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetXY($pdf->getX(), $tableBorderStartY);
                    $pdf->MultiCell(54, $totalHeight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
                    
                    $pdf->SetXY($pdf->getX(), $tableBorderStartY);
                    $pdf->MultiCell(13.2, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);
                    
                    $pdf->SetXY($pdf->getX(), $tableBorderStartY);
                    $pdf->MultiCell(13.3, $totalHeight, '', 'LR', 'C', 0, 0, '', '', true, 0, true);


                    

                    
                    

                    
                    

                
                }
                $bottomY = $leftCurrentY-3;
                
                // $y_axis=226.5;
                $y_axis=$bottomY;
                $pdf->SetXY(15.5, $y_axis+3);    
                $pdf->MultiCell(179, 15, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
                

                $pdf->SetXY(16.5, $y_axis+5);
                $pdf->MultiCell(0, 0, 'Cumulative Grade Point Average (CGPA) ', 0, 'L', 0, 0, '', '', true, 0, true);
                $pdf->SetXY(70.5, $y_axis+5);
                $pdf->MultiCell(0, 0, ': '.$CGPASecured, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(16.5, $y_axis+9);
                $pdf->MultiCell(0, 0, 'Percentage of Marks ', 0, 'L', 0, 0, '', '', true, 0, true);
                $pdf->SetXY(70.5, $y_axis+9);
                $pdf->MultiCell(0, 0, ': '.$Percentage_marks, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(16.5, $y_axis+13);
                $pdf->MultiCell(0, 0, 'Division ', 0, 'L', 0, 0, '', '', true, 0, true);
                $pdf->SetXY(70.5, $y_axis+13);
                $pdf->MultiCell(0, 0, ': '.$division, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(125.5, $y_axis+5);
                $pdf->MultiCell(0, 0, 'CGPA in Words ', 0, 'L', 0, 0, '', '', true, 0, true);
                $pdf->SetXY(145.5, $y_axis+5);
                $pdf->MultiCell(0, 0, ': '.$CGPA_Word, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(125.5, $y_axis+9);
                $pdf->MultiCell(0, 0, 'The date of declaration of result ', 0, 'L', 0, 0, '', '', true, 0, true);
                $pdf->SetXY(162, $y_axis+9);
                $pdf->MultiCell(0, 0, ': '.$Result_DATE, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetFont($arialb, '', 10, '', false); 
                $pdf->SetTextColor(0, 0, 0);    
                $pdf->SetXY(85, 268);
                $pdf->Cell(0, 0, 'Section-Incharge', 0, false, 'L');
                $pdf->SetFont($arialb, '', 10, '', false); 
                $pdf->SetXY(145, 261.5);
                $pdf->Cell(0, 0, 'Controller of Examinations', 0, false, 'L');  
                
                    					           
				//End pdf	
                
                
                	if($Photo!=''){
					//path of photos
					$profile_path_org = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$Photo;
					//set profile image   
					$profilex = 105;
					// $profiley = 52;
                    $profiley =$firstLineY;
					$profileWidth = 15;
					$profileHeight = 15;
					$pdfBig->Image($profile_path_org, $profilex,$profiley,$profileWidth,$profileHeight, 'jpg', '', true, false);
					$pdf->Image($profile_path_org, $profilex,$profiley,$profileWidth,$profileHeight, 'jpg', '', true, false);
				}
				
				// Ghost image
				$nameOrg=$Name;
				
				/*$ghost_font_size = '13';
				$ghostImagex = 132;
				$ghostImagey = 267;
				$ghostImageWidth = 55;
				$ghostImageHeight = 9.8;*/	
				
				$ghost_font_size = '12';
				$ghostImagex = 78;
				$ghostImagey = 273;
				$ghostImageWidth = 39.405983333;
				$ghostImageHeight = 8;
				$name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
				$tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
				$w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');
				$pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
				$pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
				$pdf->setPageMark();
				$pdfBig->setPageMark();
				$serial_no=$GUID=$studentData[0];
				//qr code    
				$dt = date("_ymdHis");
				$str=$GUID.$dt;
				
				$codeContents = "";
				$codeContents .= $Name;
				$codeContents .=",\n";
				$codeContents .= $course_name;
				$codeContents .=",\n";
				$codeContents .= $branch;
				$codeContents .=",\n";
				$codeContents .= $monthYearExam;
				$codeContents .="\n\n".strtoupper(md5($str));

				$encryptedString = strtoupper(md5($str));

				//$codeContents =$encryptedString = strtoupper(md5($str));
				$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
				$qrCodex = 47; 
				$qrCodey = 250;
				$qrCodeWidth =21;
				$qrCodeHeight = 21;
				$ecc = 'L';
				$pixel_Size = 1;
				$frame_Size = 1;  
				 \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
				// \QrCode::size(80)
				// 	->backgroundColor(255, 255, 0)
				// 	->format('png')
				// 	->generate($codeContents, $qr_code_path);
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
				
				$barcodex = 140;
				$barcodey = 267;
				$barcodeWidth = 56;
				$barodeHeight = 13;
				$pdf->SetAlpha(1);
				$pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
				$pdfBig->SetAlpha(1);
				$pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
							
				$str = $nameOrg;
				$str = strtoupper(preg_replace('/\s+/', '', $str)); 
				
				$microlinestr=$str;
				$pdf->SetFont($arialb, '', 2, '', false);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY(47, 249);        
				$pdf->Cell(21, 0, $microlinestr, 0, false, 'C');     
				
				$pdfBig->SetFont($arialb, '',2, '', false);
				$pdfBig->SetTextColor(0, 0, 0);
				$pdfBig->SetXY(47, 249);      
				$pdfBig->Cell(21, 0, $microlinestr, 0, false, 'C'); 

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

					$this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'Memorandum',$admin_id,$card_serial_no);

					$card_serial_no=$card_serial_no+1;
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
				$pdf_data_obj = $pdfBig; // Get the PDF data as a string

				// Store the PDF data in the session
				Session::put('pdf_data_obj', $pdf_data_obj);
	
				// Update code for batchwise genration
				return "Will be generated soon!";
			} 
		}
        
        if($previewPdf!=1){
			//$this->updateCardNo('MvsrGC',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
        }
        $msg = '';
        
        $file_name =  str_replace("/", "_",'MvsrGC'.date("Ymdhms")).'.pdf';
        
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        $filename = public_path().'/backend/tcpdf/examples/'.$file_name;
        
        $pdfBig->output($filename,'F');

        if($previewPdf!=1){
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            //$no_of_records = count($studentDataOrg);
            //Update code for batchwise genration
            $no_of_records =$pdf_data['highestrow'];
            $user = $admin_id['username'];
            $template_name="MvsrGC";
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
        // copy($file1, $file2);        
        // $aws_qr = \File::copy($file2,$pdfActualPath);
        // @unlink($file2);
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

            $filename = public_path()."/backend/canvas/ghost_images/F13_H10_W360.png";
             $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
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

	  public function convertExcelDate($dateFromExcel)
    {
        if (is_numeric($dateFromExcel)) {
            // Handle Excel date format
            try {
                $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateFromExcel);
                return Carbon::instance($excelDate)->format('d/M/Y'); // Returns DD-MM-YYYY
            } catch (\Throwable $th) {
                Log::error("Excel Date Conversion Error: " . $th->getMessage() . " for value: " . $dateFromExcel);
                return null;
            }
        } else {
            // Handle normal string date formats
            $formats = ['d/m/Y', 'd-m-Y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateFromExcel)->format('d/M/Y');
                } catch (\Throwable $th) {
                    // Try next format
                }
            }
    
            // Log error if none of the formats matched
            Log::error("String Date Parsing Error: Unable to parse date: " . $dateFromExcel);
            return null;
        }
    }

	public function convertExcelDatenew($dateFromExcel)
{
    if (is_numeric($dateFromExcel)) {
        try {
            // Handle Excel numeric date
            if (Date::isDateTimeFormatCode($dateFromExcel)) {
                $excelDate = Date::excelToDateTimeObject($dateFromExcel);
                return Carbon::instance($excelDate)->format('d/m/Y');
            } else {
                // Assume numeric input is Excel date serial number
                $excelDate = Date::excelToDateTimeObject($dateFromExcel);
                return Carbon::instance($excelDate)->format('d/m/Y');
            }
        } catch (\Throwable $th) {
            Log::error("Excel Date Conversion Error: " . $th->getMessage() . " for value: " . $dateFromExcel);
            return null;
        }
    } else {
        // Handle string formats
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y']; // Add more if needed
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateFromExcel)->format('d/m/Y');
            } catch (\Throwable $th) {
                continue;
            }
        }
        Log::error("String Date Parsing Error: Unable to parse date: " . $dateFromExcel);
        return null;
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
