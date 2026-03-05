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

class PdfGenerateCSCACSJob1
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
        // dd($pdf_data );
       
        $credit_register_key=$pdf_data['credit_register_key'];
        $total_credit_hours_key=$pdf_data['total_credit_hours_key'];
        $total_credit_points_key=$pdf_data['total_credit_points_key'];
		$note_key=$pdf_data['note_key']; 
		$cgpa_key=$pdf_data['cgpa_key']; 
		$sgpa_key=$pdf_data['sgpa_key'];
		$subj_col=$pdf_data['subj_col']; 

		$subj_start=$pdf_data['subj_start'];
        $subj_end=$pdf_data['subj_end'];

        $template_id=$pdf_data['template_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
		
        // $first_sheet=$pdf_data['studentDataOrg']; // get first worksheet rows
        
        // echo "<pre>";
        // print_r($pdf_data);
        // echo "</pre>";
        // Log an error
        // \Log::info('pdf data error', ['pdf_data' => $pdf_data]);



		// $total_unique_records=count($first_sheet);
        // $last_row=$total_unique_records+1;
                
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
            $pdfBig = new TCPDF('L', 'mm', array('297', '210'), true, 'UTF-8', false);
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
        $verdanab = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\verdanab.ttf', 'TrueTypeUnicode', '', 96);
        $verdana = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\verdana.ttf', 'TrueTypeUnicode', '', 96);

        $preview_serial_no=1;
		//$card_serial_no="";
        $log_serial_no = 1;
        $cardDetails= array();
        $card_serial_no=0;

        // $generated_documents=0;  //for custom loader
        if($studentDataOrg&&!empty($studentDataOrg)){

            foreach ($studentDataOrg as $studentData) {
            //  dd($studentData);
    			if($card_serial_no>999999&&$previewPdf!=1){
    				echo "<h5>Your card series ended...!</h5>";
    				exit;
    			}
    			//For Custom Loader
                $startTimeLoader =  date('Y-m-d H:i:s');    
    			$high_res_bg="cscacs_BG.jpg"; // bestiu_grade_btech_bg, GradeCard.jpg
    			$low_res_bg="cscacs_BG.jpg";
    			$pdfBig->AddPage();
    			$pdfBig->SetFont($arialNarrowB, '', 8, '', false);
    			//set background image
    			$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;   

                // \Log::info('PDF parameters', [
                //     'template_img' => $template_img_generate,
                //     'pageWidth' => $pdfBig->getPageWidth(),
                //     'pageHeight ' => $pdfBig->getPageHeight(),
                //     'current_page' => $pdfBig->getPage()
                // ]);
    			if($previewPdf==1){
    				if($previewWithoutBg!=1){
    					$pdfBig->Image($template_img_generate, 0, 0, '297', '210', "JPG", '', 'R', false);
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
    				$pdfBig->SetFont($arialNarrowB, '', 9, '', false);
    			}
    			$pdfBig->setPageMark();

    			$ghostImgArr = array();
    			$pdf = new TCPDF('L', 'mm', array('297', '210'), true, 'UTF-8', false); 
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
    				$pdf->Image($template_img_generate, 0, 0, '297', '210', "JPG", '', 'R', false);
    			}
    			//$pdf->setPageMark();
    			$pdf->setPageMark();
    			//$pdfBig->setPageMark();
                //Table's Titles                        			
    			$unique_id=trim($studentData[0]);
    			$programme=trim($studentData[4]);
                $semester=trim($studentData[36]);
                $academic_year=trim($studentData[3]);
    			$student_id=trim($studentData[9]);
    			$prn_no=trim($studentData[10]);
    			$seat_no=trim($studentData[12]);
                $candidate_name=trim($studentData[13]);
    			$examination=trim($studentData[8]);

                $sgpi=trim($studentData[48]);
                $result=trim($studentData[30]);
                $totalce=trim($studentData[41]);
                $totalse=trim($studentData[38]);
                $ec=trim($studentData[41]);
                $cg=trim($studentData[43]);
                $place=trim($studentData[1]);
                $print_date=trim($studentData[343]);
                $result_date=trim($studentData[342]);
    			$photo=trim($studentData[27]);
                $totalMO = trim($studentData[40]);
    			
    			// $DATE=trim($studentData[11]);
                

                $credit_reg=trim($studentData[$credit_register_key]);
                $total_credit_hours=trim($studentData[$total_credit_hours_key]);
    			$total_credit_points=trim($studentData[$total_credit_points_key]);
    			$sgpa=trim($studentData[50]);
    			$cgpa=trim($studentData[48]);
    			$note=trim($studentData[$note_key]);

                

                $left_pos=12.5;
                $left_pos_colan_data=35;

    			$left_pos_two=105;
                $left_pos_two_colan_data=127.5;


                $imageName =$photo;
                
                $image_path = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$imageName;

                $basename = basename($image_path);
                // $template_img = str_replace(' ', '_', $image_path);
                // $template_img = $image_path;    
                $ext = pathinfo($image_path, PATHINFO_EXTENSION);
            
                $image_extension = 'JPG';
                if ($ext == 'PNG' || 'png') {
                    $image_extension = 'PNG';
                }
                if ($ext == 'JPG' || $ext == 'jpg') {
                    $image_extension = 'jpg';
                }
                
                //Mandar Update
                $template_img_new = public_path().'\\'.$subdomain[0].'\\backend\templates\100\\'.$basename;

                
                $imagex = 263; 
                $imagey = 51;
                $imageWidth =20;
                $imageHeight = 20;
                
                if(file_exists($image_path)) {

                    $pdfBig->Image($template_img_new, $imagex, $imagey, $imageWidth,  $imageHeight, $image_extension, '', true, false);   
                    $pdfBig->setPageMark(); 
                    $pdf->Image($template_img_new, $imagex, $imagey, $imageWidth,  $imageHeight, $image_extension, '', true, false);   
                    $pdf->setPageMark(); 
                }

                
    			//Start pdfBig  
                // start invisible data
                $pdfBig->SetFont($arialb, '', 10, '', false); 
                $pdfBig->SetTextColor(255, 255, 0);        
                $pdfBig->SetXY(13, 45);
                $pdfBig->Cell(0, 0, $candidate_name, 0, false, 'L');
                // end invisible data		
                $pdfBig->SetTextColor(0, 0, 0); 
               $pdfBig->SetFont($verdanab, '', 15, '', false); 
               $pdfBig->SetXY(50, 44);		
               $pdfBig->MultiCell(180, 7, 'GRADE CARD', 0, 'C');
    			
                $startX = 12;
                $startY = 52;
                $h = 8;

                $wProgramme = 151;
                $wSemester  = 30;
                $wYear      = 36;
                $wStudentId = 30;

                $pdfBig->SetFont($verdanab, '', 8, '', false); 
                
                // PROGRAMME
                $pdfBig->setCellPadding(1.6);

                $pdfBig->SetXY($startX, $startY);
                $pdfBig->MultiCell(
                    $wProgramme,
                    $h,
                    'PROGRAMME : '.$programme,
                    1,
                    'L',
                    false
                );

                // SEMESTER
                $pdfBig->SetXY($startX + $wProgramme, $startY);
                $pdfBig->MultiCell($wSemester, $h, "SEMESTER : ".$semester, 1, 'C', false);

                $pdfBig->setCellPadding(0);

                // ACADEMIC YEAR
                $pdfBig->SetXY($startX + $wProgramme + $wSemester, $startY);
                $pdfBig->MultiCell($wYear, $h, "ACADEMIC YEAR\n".$academic_year,  1, 'C', false);

                // STUDENT ID
                $pdfBig->SetXY($startX + $wProgramme + $wSemester + $wYear, $startY);
                $pdfBig->MultiCell($wStudentId, $h, "STUDENT ID\n".$student_id, 1, 'C', false);

                $startY += $h;
                $wPRN   = 50;
                $wSeat  = 61;
                $wName  = 83;
                $wMonth = 53;

                $labelH = 6;      // label height
                $valueH = 6;      // value height
                $totalH = $labelH + $valueH;

                $x = $startX;
                $y = $startY;

                // Outer box
                $pdfBig->Rect($x, $y, $wPRN, $totalH);
                $pdfBig->setCellPadding(1.2);

                // Label
                $pdfBig->SetXY($x, $y);
                $pdfBig->MultiCell($wPRN, $labelH, "PRN No.", 0, 'C');

                // Horizontal separator line
                $pdfBig->Line(
                    $x,
                    $y + $labelH,
                    $x + $wPRN,
                    $y + $labelH
                );

                // Value
                $pdfBig->SetXY($x, $y + $labelH);
                $pdfBig->MultiCell($wPRN, $valueH, $prn_no, 0, 'C');
                $x = $startX + $wPRN;

                $pdfBig->Rect($x, $y, $wSeat, $totalH);

                $pdfBig->SetXY($x, $y);
                $pdfBig->MultiCell($wSeat, $labelH, "Examination Seat No.", 0, 'C');

                $pdfBig->Line($x, $y + $labelH, $x + $wSeat, $y + $labelH);

                $pdfBig->SetXY($x, $y + $labelH);
                $pdfBig->MultiCell($wSeat, $valueH, $seat_no, 0, 'C');
                $x = $startX + $wPRN + $wSeat;

                $pdfBig->Rect($x, $y, $wName, $totalH);

                $pdfBig->SetXY($x, $y);
                $pdfBig->MultiCell($wName, $labelH, "Name of the Learner", 0, 'C');

                $pdfBig->Line($x, $y + $labelH, $x + $wName, $y + $labelH);

                $pdfBig->SetXY($x, $y + $labelH);
                $pdfBig->MultiCell($wName, $valueH, $candidate_name, 0, 'C');
                $x = $startX + $wPRN + $wSeat + $wName;

                $pdfBig->Rect($x, $y, $wMonth, $totalH);

                $pdfBig->SetXY($x, $y);
                $pdfBig->MultiCell($wMonth, $labelH, "Month & Year of Examination", 0, 'C');

                $pdfBig->Line($x, $y + $labelH, $x + $wMonth, $y + $labelH);

                $pdfBig->SetXY($x, $y + $labelH);
                $pdfBig->MultiCell($wMonth, $valueH, $examination, 0, 'C');
                $pdfBig->setCellPadding(0);
                
                // heading  data end

                // subject table data start
                
                $left = 12;
                $top  = 73;

                $wSl     = 10;
                $wCode   = 28;
                $wTitle  = 59;
                $wCredit = 13;

                // CIA (3 cols)
                $wCia = 12;
                $wSem = 12;
                $wTot = 12;

                $wGrade  = 12;
                $wGP     = 12;
                $wCE     = 13;
                $wCG     = 12;
                $wsgpi   = 15;

                $h1 = 7; // header row 1
                $h2 = 7; // header row 2
                $rowH = 7;

                $pdfBig->SetFont($verdanab, '', 6, '', false); 
                $pdfBig->SetXY($left, $top);

               

            $x = $left;
            $y = $top;
            $pdfBig->SetXY(12,   $y);
            $pdfBig->MultiCell(28,  $h1 + $h2, "Course Code", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wTitle,  $h1 + $h2, "Course Title", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wCredit,  $h1 + $h2, "Course Credit", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wCia * 3,  $h1 , "Continuous Internal Assessment", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wCia * 3,  $h1 , "Semester End Examination", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wCia * 3,  $h1 , "Total Marks", "TLB", 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->MultiCell($wGrade, $h1 + $h2, "Grade", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wGP, $h1 + $h2, "Grade Points", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wCE, $h1 + $h2, "Credits Earned", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wCG, $h1 + $h2, "CG = C × G","TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->MultiCell($wsgpi, $h1 + $h2, "SGPI=\nΣCG /\nΣC", "TLBR",'C', 0, 0, '', '', true, 0, true);

            
           

            $x = $left + $wCode + $wTitle + $wCredit;
            $y = $top + $h1;

            $pdfBig->SetXY($x,   $y);
            foreach (['Max Marks','Min Marks','Marks Obt','Max Marks','Min Marks','Marks Obt','Max Marks','Min Marks','Marks Obt'] as $t) {
            $pdfBig->MultiCell($wCia,  $h2,$t, "LB",'C', 0, 0, '', '', true, 0, true);
            }

            

                $y = $top + $h1 + $h2;
                $sr = 1;

                $subjectData = array_slice($studentData, $subj_start, $subj_end);
                $subjectsArr=array_chunk($subjectData, $subj_col); 
                // dd(  $subj_end);
                $pdfBig->setCellPadding(1);
                $pdfBig->SetFont($verdanab, '', 5, '', false); 
                $rowH1=5.45;
                $count = 0;
                $totalHeight = 0;
                foreach ($subjectsArr as $row) {

                   if (empty($row[0])) {
                        continue;
                    }

                    	$pdfBig->startTransaction();
							 $pdfBig->SetFont($verdanab, '', 5, '', false);
							// get the number of lines
							$lines = $pdfBig->MultiCell($wTitle, 0, trim($row[0]), 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
							$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object
							if($lines>1){
								$rowH1 = 7;
							} else {
								$rowH1 =4.8;
							}

                    $pdfBig->SetXY(12,   $y);
                    $pdfBig->MultiCell(28, $rowH1, $row[1], "LRB", 'C', 0, 0, '', '', true, 0, true); 
                    $pdfBig->MultiCell($wTitle, $rowH1, $row[0], "LRB", 'L', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wCredit, $rowH1, $row[17], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wCia, $rowH1, $row[6], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wCia, $rowH1, $row[7], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wCia, $rowH1, $row[10], "LRB", 'C', 0, 0, '', '', true, 0, true);

                    $pdfBig->MultiCell($wSem, $rowH1, $row[4], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wSem, $rowH1, $row[5], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wSem, $rowH1, $row[8], "LRB", 'C', 0, 0, '', '', true, 0, true);

                    $pdfBig->MultiCell($wTot, $rowH1, $row[2], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wTot, $rowH1, $row[3], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wTot, $rowH1, $row[11], "LRB", 'C', 0, 0, '', '', true, 0, true);


                    $pdfBig->MultiCell($wGrade, $rowH1, $row[15], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    
                    $pdfBig->MultiCell($wGP, $rowH1, $row[16], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->MultiCell($wCE, $rowH1, $row[17], "LRB", 'C', 0, 0, '', '', true, 0, true);
                     $pdfBig->MultiCell($wCG, $rowH1, $row[18], "LRB", 'C', 0, 0, '', '', true, 0, true);
                     $y += $rowH1;
                      $count++;
                        $totalHeight += $rowH1;
                }

           

                    // $totalHeight  =  $count  * $rowH1;
                    $sgpiX = 269;
                    $sgpiY = $top + $h1 + $h2;
                    $pdfBig->setCellPaddings( $left = '', $top = $totalHeight /2, $right = '', $bottom = '');
                    $pdfBig->SetXY($sgpiX,   $sgpiY);
                    $pdfBig->MultiCell($wsgpi, $totalHeight, $sgpi, "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');


                $pdfBig->SetFont($verdanab, '', 6.5, '', false); 
                $pdfBig->SetXY(12,  $y);
                $pdfBig->Cell(60, 6, "Result: ".$result, 'L', 0, 'L');
                $pdfBig->Cell(76, 6, "Credits Earned : ".$totalce, 'R', 0, 'R');
                $pdfBig->Cell(36, 6, "Total", 'R', 0, 'C');
                $pdfBig->Cell(12, 6, $totalse, 'R', 0, 'C');
                $pdfBig->Cell(12, 6, " ", 'R', 0, 'C');
                $pdfBig->Cell(12, 6, $totalMO, 'R', 0, 'C');
                $pdfBig->Cell(24, 6, " ", 'R', 0, 'C');
                $pdfBig->Cell(13, 6, "ΣC=".$ec, 'R', 0, 'C');
                $pdfBig->Cell(12, 6, "ΣCG=".$cg, 'R', 0, 'C');
                $pdfBig->Cell(15, 6, "SGPI=".$sgpi, 'R', 1, 'C');

            $semesterFooter = [
                ['label' => 'Sem I',   'credits' => 16, 'sgpi' => 'NA', 'result' => 'F(1)'],
                ['label' => 'Sem II',  'credits' => 18, 'sgpi' => 'NA', 'result' => 'F(1)'],
                ['label' => 'Sem III', 'credits' => 16, 'sgpi' => 'NA', 'result' => 'F(1)'],
                // ['label' => 'Sem IV',  'credits' => 14, 'sgpi' => 'NA', 'result' => 'F(2)'],
                // ['label' => 'Sem V',   'credits' => 'NA', 'sgpi' => 'NA', 'result' => ''],
                // ['label' => 'Sem VI',  'credits' => 'NA', 'sgpi' => 'NA', 'result' => ''],
            ];

            $footerX =$startFooterX= 12;
            $footerY = $gradeY=$y+6;
            $blockW  = 80;
            $blockH  = 5;

            $totalW = $blockW * 3;   // 3 columns
            $totalH = $blockH ;   // 2 rows

           $pdfBig->SetFont($verdanab, '', 7.3, '', false); 
            $i = 0;



                 $pdfBig->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
                $text = 'Sem '.$semester. " : Credits Earned = " . $ec . "   SGPI = " . $sgpi;
                $pdfBig->setXY($footerX, $footerY);
                $pdfBig->MultiCell($totalW, $blockH, $text, 'TLB', 'L', 0, 0, '', '', true, 0, true);
            
              // $pdfBig->SetY( $gradeY);
                
                $pdfBig->MultiCell(32, $totalH, trim($studentData[53]), 1, 'C', 0, 0, '', '', true, 0, true);

                $pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
    			$pdfBig->SetTextColor(0, 0, 0);
    			$pdfBig->SetXY(12, $gradeY+$totalH);
    			$pdfBig->SetFont($verdanab, '', 8, '', false); 
    			//$pdfBig->Cell(0, 6, $note, 0, false, 'L');
    			$pdfBig->MultiCell(185, 0, 'Place:- '.$place, 0, 'L', 0, 0, '', '', true, 0, true);
                $pdfBig->ln();
    			$pdfBig->SetXY(12, $pdfBig->GetY());
                $pdfBig->Cell(0, 0, 'Print Date:- '.$print_date, 0, false, 'L'); 
                $pdfBig->ln();
                $pdfBig->SetXY(12, $pdfBig->GetY());
                $pdfBig->Cell(0, 0, 'Result Date:- '.$result_date, 0, false, 'L'); 

    			$pdfBig->SetFont($verdanab, '', 7.3, '', false); 
    			$pdfBig->SetXY(50, 187);
                $pdfBig->Cell(0, 0, 'Controller of Examinations', 0, false, 'L'); 
                $pdfBig->SetXY(12, 187);
                $pdfBig->Cell(0, 0, 'Checked By:', 0, false, 'L'); 

                $pdfBig->SetXY(162, 187);
                $pdfBig->Cell(0, 0, 'Seal of the College', 0, false, 'L'); 

                $pdfBig->SetXY(220, 187);
                $pdfBig->Cell(0, 0, 'Principal', 0, false, 'L'); 

                $pdfBig->SetFont($verdana, '', 6.5, '', false); 
                $pdfBig->SetXY(12, 192);
                $pdfBig->MultiCell(185, 6,
                'F: Failure, AB: Absent, --- : Not Applicable, @: 0.5042A/5043A/5044A, *: 5045A, &: ADDITIONAL,
                CC: COPY OPY CASE, NA: NULL AND VOID, #: O.229, $: Carried Forward, /: Female, : LD Grace, DA: Disallow', 0,'L', false);



                //End pdfBig 
    			
    			//Start pdf
            
                $pdf->SetFont($arialb, '', 10, '', false); 
                $pdf->SetTextColor(255, 255, 0);        
                $pdf->SetXY(13, 45);
                $pdf->Cell(0, 0, $candidate_name, 0, false, 'L');
                // end invisible data   
                $pdf->SetTextColor(0, 0, 0); 
               $pdf->SetFont($verdanab, '', 15, '', false); 
               $pdf->SetXY(50, 44);    
               $pdf->MultiCell(180, 7, 'GRADE CARD', 0, 'C');
          
                $startX = 12;
                $startY = 52;
                $h = 8;

                $wProgramme = 151;
                $wSemester  = 30;
                $wYear      = 36;
                $wStudentId = 30;

                $pdf->SetFont($verdanab, '', 8, '', false); 
                
                // PROGRAMME
                $pdf->setCellPadding(1.6);

                $pdf->SetXY($startX, $startY);
                $pdf->MultiCell(
                    $wProgramme,
                    $h,
                    'PROGRAMME : '.$programme,
                    1,
                    'L',
                    false
                );

                // SEMESTER
                $pdf->SetXY($startX + $wProgramme, $startY);
                $pdf->MultiCell($wSemester, $h, "SEMESTER : ".$semester, 1, 'C', false);

                $pdf->setCellPadding(0);

                // ACADEMIC YEAR
                $pdf->SetXY($startX + $wProgramme + $wSemester, $startY);
                $pdf->MultiCell($wYear, $h, "ACADEMIC YEAR\n".$academic_year,  1, 'C', false);

                // STUDENT ID
                $pdf->SetXY($startX + $wProgramme + $wSemester + $wYear, $startY);
                $pdf->MultiCell($wStudentId, $h, "STUDENT ID\n".$student_id, 1, 'C', false);

                $startY += $h;
                $wPRN   = 50;
                $wSeat  = 61;
                $wName  = 83;
                $wMonth = 53;

                $labelH = 6;      // label height
                $valueH = 6;      // value height
                $totalH = $labelH + $valueH;

                $x = $startX;
                $y = $startY;

                // Outer box
                $pdf->Rect($x, $y, $wPRN, $totalH);
                $pdf->setCellPadding(1.2);

                // Label
                $pdf->SetXY($x, $y);
                $pdf->MultiCell($wPRN, $labelH, "PRN No.", 0, 'C');

                // Horizontal separator line
                $pdf->Line(
                    $x,
                    $y + $labelH,
                    $x + $wPRN,
                    $y + $labelH
                );

                // Value
                $pdf->SetXY($x, $y + $labelH);
                $pdf->MultiCell($wPRN, $valueH, $prn_no, 0, 'C');
                $x = $startX + $wPRN;

                $pdf->Rect($x, $y, $wSeat, $totalH);

                $pdf->SetXY($x, $y);
                $pdf->MultiCell($wSeat, $labelH, "Examination Seat No.", 0, 'C');

                $pdf->Line($x, $y + $labelH, $x + $wSeat, $y + $labelH);

                $pdf->SetXY($x, $y + $labelH);
                $pdf->MultiCell($wSeat, $valueH, $seat_no, 0, 'C');
                $x = $startX + $wPRN + $wSeat;

                $pdf->Rect($x, $y, $wName, $totalH);

                $pdf->SetXY($x, $y);
                $pdf->MultiCell($wName, $labelH, "Name of the Learner", 0, 'C');

                $pdf->Line($x, $y + $labelH, $x + $wName, $y + $labelH);

                $pdf->SetXY($x, $y + $labelH);
                $pdf->MultiCell($wName, $valueH, $candidate_name, 0, 'C');
                $x = $startX + $wPRN + $wSeat + $wName;

                $pdf->Rect($x, $y, $wMonth, $totalH);

                $pdf->SetXY($x, $y);
                $pdf->MultiCell($wMonth, $labelH, "Month & Year of Examination", 0, 'C');

                $pdf->Line($x, $y + $labelH, $x + $wMonth, $y + $labelH);

                $pdf->SetXY($x, $y + $labelH);
                $pdf->MultiCell($wMonth, $valueH, $examination, 0, 'C');
                $pdf->setCellPadding(0);
                
                // heading  data end

                // subject table data start
                
                $left = 12;
                $top  = 73;

                $wSl     = 10;
                $wCode   = 28;
                $wTitle  = 59;
                $wCredit = 13;

                // CIA (3 cols)
                $wCia = 12;
                $wSem = 12;
                $wTot = 12;

                $wGrade  = 12;
                $wGP     = 12;
                $wCE     = 13;
                $wCG     = 12;
                $wsgpi   = 15;

                $h1 = 7; // header row 1
                $h2 = 7; // header row 2
                $rowH = 7;

                $pdf->SetFont($verdanab, '', 6, '', false); 
                $pdf->SetXY($left, $top);

               

            $x = $left;
            $y = $top;
            $pdf->SetXY(12,   $y);
            $pdf->MultiCell(28,  $h1 + $h2, "Course Code", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wTitle,  $h1 + $h2, "Course Title", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wCredit,  $h1 + $h2, "Course Credit", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wCia * 3,  $h1 , "Continuous Internal Assessment", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wCia * 3,  $h1 , "Semester End Examination", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wCia * 3,  $h1 , "Total Marks", "TLB", 'C', 0, 0, '', '', true, 0, true);

            $pdf->MultiCell($wGrade, $h1 + $h2, "Grade", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wGP, $h1 + $h2, "Grade Points", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wCE, $h1 + $h2, "Credits Earned", "TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wCG, $h1 + $h2, "CG = C × G","TLB", 'C', 0, 0, '', '', true, 0, true);
            $pdf->MultiCell($wsgpi, $h1 + $h2, "SGPI=\nΣCG /\nΣC", "TLBR",'C', 0, 0, '', '', true, 0, true);

            
           

            $x = $left + $wCode + $wTitle + $wCredit;
            $y = $top + $h1;

            $pdf->SetXY($x,   $y);
            foreach (['Max Marks','Min Marks','Marks Obt','Max Marks','Min Marks','Marks Obt','Max Marks','Min Marks','Marks Obt'] as $t) {
            $pdf->MultiCell($wCia,  $h2,$t, "LB",'C', 0, 0, '', '', true, 0, true);
            }

            

                $y = $top + $h1 + $h2;
                $sr = 1;

                $subjectData = array_slice($studentData, $subj_start, $subj_end);
                $subjectsArr=array_chunk($subjectData, $subj_col); 
                // dd(  $subj_end);
                $pdf->setCellPadding(1);
                $pdf->SetFont($verdanab, '', 5, '', false); 
                $rowH1=5.45;
                $count = 0;
                $totalHeight = 0;
                foreach ($subjectsArr as $row) {

                   if (empty($row[0])) {
                        continue;
                    }

                      $pdf->startTransaction();
               $pdf->SetFont($verdanab, '', 5, '', false);
              // get the number of lines
              $lines = $pdf->MultiCell($wTitle, 0, trim($row[0]), 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
              $pdf=$pdf->rollbackTransaction(); // restore previous object
              if($lines>1){
                $rowH1 = 7;
              } else {
                $rowH1 =4.8;
              }

                    $pdf->SetXY(12,   $y);
                    $pdf->MultiCell(28, $rowH1, $row[1], "LRB", 'C', 0, 0, '', '', true, 0, true); 
                    $pdf->MultiCell($wTitle, $rowH1, $row[0], "LRB", 'L', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wCredit, $rowH1, $row[17], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wCia, $rowH1, $row[6], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wCia, $rowH1, $row[7], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wCia, $rowH1, $row[10], "LRB", 'C', 0, 0, '', '', true, 0, true);

                    $pdf->MultiCell($wSem, $rowH1, $row[4], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wSem, $rowH1, $row[5], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wSem, $rowH1, $row[8], "LRB", 'C', 0, 0, '', '', true, 0, true);

                    $pdf->MultiCell($wTot, $rowH1, $row[2], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wTot, $rowH1, $row[3], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wTot, $rowH1, $row[11], "LRB", 'C', 0, 0, '', '', true, 0, true);


                    $pdf->MultiCell($wGrade, $rowH1, $row[15], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    
                    $pdf->MultiCell($wGP, $rowH1, $row[16], "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->MultiCell($wCE, $rowH1, $row[17], "LRB", 'C', 0, 0, '', '', true, 0, true);
                     $pdf->MultiCell($wCG, $rowH1, $row[18], "LRB", 'C', 0, 0, '', '', true, 0, true);
                     $y += $rowH1;
                      $count++;
                        $totalHeight += $rowH1;
                }

           

                    // $totalHeight  =  $count  * $rowH1;
                    $sgpiX = 269;
                    $sgpiY = $top + $h1 + $h2;
                    $pdf->setCellPaddings( $left = '', $top = $totalHeight /2, $right = '', $bottom = '');
                    $pdf->SetXY($sgpiX,   $sgpiY);
                    $pdf->MultiCell($wsgpi, $totalHeight, $sgpi, "LRB", 'C', 0, 0, '', '', true, 0, true);
                    $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');


                $pdf->SetFont($verdanab, '', 6.5, '', false); 
                $pdf->SetXY(12,  $y);
                $pdf->Cell(60, 6, "Result: ".$result, 'L', 0, 'L');
                $pdf->Cell(76, 6, "Credits Earned : ".$totalce, 'R', 0, 'R');
                $pdf->Cell(36, 6, "Total", 'R', 0, 'C');
                $pdf->Cell(12, 6, $totalse, 'R', 0, 'C');
                $pdf->Cell(12, 6, " ", 'R', 0, 'C');
                $pdf->Cell(12, 6, $totalMO, 'R', 0, 'C');
                $pdf->Cell(24, 6, " ", 'R', 0, 'C');
                $pdf->Cell(13, 6, "ΣC=".$ec, 'R', 0, 'C');
                $pdf->Cell(12, 6, "ΣCG=".$cg, 'R', 0, 'C');
                $pdf->Cell(15, 6, "SGPI=".$sgpi, 'R', 1, 'C');

            $semesterFooter = [
                ['label' => 'Sem I',   'credits' => 16, 'sgpi' => 'NA', 'result' => 'F(1)'],
                ['label' => 'Sem II',  'credits' => 18, 'sgpi' => 'NA', 'result' => 'F(1)'],
                ['label' => 'Sem III', 'credits' => 16, 'sgpi' => 'NA', 'result' => 'F(1)'],
                // ['label' => 'Sem IV',  'credits' => 14, 'sgpi' => 'NA', 'result' => 'F(2)'],
                // ['label' => 'Sem V',   'credits' => 'NA', 'sgpi' => 'NA', 'result' => ''],
                // ['label' => 'Sem VI',  'credits' => 'NA', 'sgpi' => 'NA', 'result' => ''],
            ];

            $footerX =$startFooterX= 12;
            $footerY = $gradeY=$y+6;
            $blockW  = 80;
            $blockH  = 5;

            $totalW = $blockW * 3;   // 3 columns
            $totalH = $blockH ;   // 2 rows

           $pdf->SetFont($verdanab, '', 7.3, '', false); 
            $i = 0;



                 $pdf->setCellPaddings( $left = '', $top = '0.5', $right = '', $bottom = '');
                $text = 'Sem '.$semester. " : Credits Earned = " . $ec . "   SGPI = " . $sgpi;
                $pdf->setXY($footerX, $footerY);
                $pdf->MultiCell($totalW, $blockH, $text, 'TLB', 'L', 0, 0, '', '', true, 0, true);
            
              // $pdf->SetY( $gradeY);
                
                $pdf->MultiCell(32, $totalH, trim($studentData[53]), 1, 'C', 0, 0, '', '', true, 0, true);

                $pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetXY(12, $gradeY+$totalH);
                $pdf->SetFont($verdanab, '', 8, '', false); 
                //$pdf->Cell(0, 6, $note, 0, false, 'L');
                 $pdf->MultiCell(185, 0, 'Place:- '.$place, 0, 'L', 0, 0, '', '', true, 0, true);
                $pdf->ln();
                $pdf->SetXY(12, $pdf->GetY());
                $pdf->Cell(0, 0, 'Print Date:- '.$print_date, 0, false, 'L'); 
                $pdf->ln();
                $pdf->SetXY(12, $pdf->GetY());
                $pdf->Cell(0, 0, 'Result Date:- '.$result_date, 0, false, 'L'); 

                $pdf->SetFont($verdanab, '', 7.3, '', false); 
                $pdf->SetXY(50, 187);
                $pdf->Cell(0, 0, 'Controller of Examinations', 0, false, 'L'); 
                $pdf->SetXY(12, 187);
                $pdf->Cell(0, 0, 'Checked By:', 0, false, 'L'); 

                $pdf->SetXY(162, 187);
                $pdf->Cell(0, 0, 'Seal of the College', 0, false, 'L'); 

                $pdf->SetXY(220, 187);
                $pdf->Cell(0, 0, 'Principal', 0, false, 'L'); 

                $pdf->SetFont($verdana, '', 6.5, '', false); 
                $pdf->SetXY(12, 192);
                $pdf->MultiCell(185, 6,
                'F: Failure, AB: Absent, --- : Not Applicable, @: 0.5042A/5043A/5044A, *: 5045A, &: ADDITIONAL,
                CC: COPY OPY CASE, NA: NULL AND VOID, #: O.229, $: Carried Forward, /: Female, : LD Grace, DA: Disallow', 0,'L', false);

    			//End pdf			
    			
    			// Ghost image
    			$nameOrg=$candidate_name;
    			
    			/*$ghost_font_size = '13';
    			$ghostImagex = 132;
    			$ghostImagey = 267;
    			$ghostImageWidth = 55;
    			$ghostImageHeight = 9.8;*/	
    			
    			$ghost_font_size = '12';
                $ghostImagex = 237;
                $ghostImagey = 162;
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
                $codeContents .= $candidate_name;
                $codeContents .="\n";
                $codeContents .= $student_id;
                $codeContents .="\n\n".strtoupper(md5($str));

                $encryptedString = strtoupper(md5($str));

                
    			// $codeContents =$encryptedString = strtoupper(md5($str));
    			$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
    			$qrCodex = 125; 
    			$qrCodey = 168;
    			$qrCodeWidth =21;
    			$qrCodeHeight = 21;
    			$ecc = 'L';
    			$pixel_Size = 1;
    			$frame_Size = 1;  
    			//\PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
                \QrCode::size(80)
                    ->backgroundColor(255, 255, 0)
                    ->format('png')
                    ->generate($codeContents, $qr_code_path);
                    
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
    			
    			$barcodex = 165;
    			$barcodey = 162;
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
    			$pdf->SetXY(125, 189);        
    			$pdf->Cell(21, 0, $microlinestr, 0, false, 'C');     
    			
    			$pdfBig->SetFont($arialb, '', 2, '', false);
    			$pdfBig->SetTextColor(0, 0, 0);
    			$pdfBig->SetXY(125, 189);      
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
            if($previewPdf!=1){
            $this->updateCardNo('Cscacs',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
            }
        }
        
        $msg = '';
        
        $file_name =  str_replace("/", "_",'Cscacs'.date("Ymdhms")).'.pdf';
        
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
            $template_name="Cscacs";
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
