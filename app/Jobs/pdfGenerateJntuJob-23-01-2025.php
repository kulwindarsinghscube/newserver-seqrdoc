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
class pdfGenerateJntuJob
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
        $points=$pdf_data['points'];
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
        $photo_col=23;

        $first_sheet=$pdf_data['studentDataOrg']; // get first worksheet rows
        //print_r($second_sheet); exit;
		       
        
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

        $loader_data =CoreHelper::getLoaderJson($pdf_data['loader_token']);
        

        // Log an error
        //\Log::info('loader error', ['loader_data' => $loader_data]);

        if(!empty($loader_data) && isset($loader_data['generatedCertificates'])){

            $generated_documents=$loader_data['generatedCertificates'];  

        }else{
            $generated_documents=0;  
        }
    
        if($generated_documents == 0){
            Session::forget('pdf_data_obj');
            $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
            $pdfBig->SetCreator(PDF_CREATOR);
            $pdfBig->SetAuthor('TCPDF');
            $pdfBig->SetTitle('Certificate');
            $pdfBig->SetSubject('');
    
        }else{ 
             if(Session::get('pdf_data_obj') != null){
            $pdfBig = Session::get('pdf_data_obj');   
            }
        }
     
        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);

 
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
        $cardDetails=$this->getNextCardNo('jntuacekEGC');
        $card_serial_no=$cardDetails->next_serial_no;
        // $generated_documents=0;  //for custom loader
        if($studentDataOrg&&!empty($studentDataOrg)){
        foreach ($studentDataOrg as $studentData) {
         
			if($card_serial_no>999999&&$previewPdf!=1){
				echo "<h5>Your card series ended...!</h5>";
				exit;
			}
			//For Custom Loader
            $startTimeLoader =  date('Y-m-d H:i:s');    
			$high_res_bg="Engg_grade_card_bg.jpg"; // anu_gradecard_front
			$low_res_bg="Engg_grade_card_bg.jpg";
			$pdfBig->AddPage();
			$pdfBig->SetFont($arialNarrowB, '', 8, '', false);
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
				 
			}
			$pdfBig->setPageMark();

			$ghostImgArr = array();
			$pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
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
			$pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
			}
			//$pdf->setPageMark();
			$pdf->setPageMark();
			 
			if($studentData[1]!=''){
				//path of photos
				$profile_path_org = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$studentData[1].'.jpg'; 
				//set profile image   
				$profilex = 170;
				$profiley = 46;
				$profileWidth = 27.18;
				$profileHeight = 35;
				// $profilex = 175.5;
				// $profiley = 62;
				// $profileWidth = 16;
				// $profileHeight = 22;
				$pdfBig->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
				$pdfBig->setPageMark();
				$pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);
				$pdf->setPageMark();
			}
            

            $unique_id = trim($studentData[0]);
            $student_id = trim($studentData[1]);
            $hall_ticket = trim($studentData[1]);
            $memo_no = trim($studentData[2]);
            $appar_id = trim($studentData[3]);
            $serial_no = trim($studentData[4]);
			$examination = trim($studentData[5]);
            $month_year = trim($studentData[6]);
            $branch = trim($studentData[7]); 
            $candidate_name = trim($studentData[8]);
            $father_name =  trim($studentData[9]);
            $mother_name =  trim($studentData[10]);

            $total_internal_marks = trim($studentData[107]);
            $total_external_marks = trim($studentData[108]);
            $total_marks_100 = trim($studentData[109]);
            $total_credits = trim($studentData[110]);

            $total_subject = trim($studentData[111]);
            $total_appered = trim($studentData[112]);
            $total_passed = trim($studentData[113]);
          
            $agreegate_in_word =  trim($studentData[114]);
            $sgpa =  trim($studentData[115]);
            $cgpa =  trim($studentData[116]);
            $date_of_issue =  trim($studentData[117]);

            
            $str_font_size = '10';       
			$content_font_size = 9;

            $pdfBig->SetFont($timesb, '', $content_font_size, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $x_axis_main_info = 12;
            $x_axis_main_info_second = 120; 
            $y_axis_main_info =43;
            $main_info_height = 7;
            $pdfBig->SetXY($x_axis_main_info, $y_axis_main_info);
			$pdfBig->MultiCell(41,  $main_info_height, "MEMO NO", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$memo_no.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetXY(154, $y_axis_main_info-1);
            $pdfBig->MultiCell(32,  $main_info_height, "", 1, "L", 0, 0, '', '', true, 0, true);
 

            $pdfBig->SetXY($x_axis_main_info_second, $y_axis_main_info);
            $pdfBig->MultiCell(32,  $main_info_height, "APAAR ID", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$appar_id.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->ln();
            $pdfBig->SetXY($x_axis_main_info, $pdfBig->getY());
			$pdfBig->MultiCell(41,  $main_info_height, "SERIAL NO", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$serial_no.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->ln();
            
            $pdfBig->SetXY($x_axis_main_info, $pdfBig->getY());
			$pdfBig->MultiCell(41,  $main_info_height, "EXAMINATION", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$examination.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
           
            $pdfBig->SetXY($x_axis_main_info_second, $pdfBig->getY());
            $pdfBig->MultiCell(32,  $main_info_height, "MONTH & YEAR", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$month_year.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->ln();
            
            $pdfBig->SetXY($x_axis_main_info, $pdfBig->getY());
			$pdfBig->MultiCell(41,  $main_info_height, "BRANCH", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$branch.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->ln();
            $pdfBig->SetXY($x_axis_main_info, $pdfBig->getY());
			$pdfBig->MultiCell(41,  $main_info_height, "NAME OF THE STUDENT", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$candidate_name.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetTextColor(255,255, 0);
            $old_y=$pdfBig->getY();
            $pdfBig->SetXY(55, 74);
            $pdfBig->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">'.$candidate_name.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0);   
            $pdfBig->SetXY($x_axis_main_info, $old_y);


            $old_y = $pdfBig->getY();

            $pdfBig->SetXY(154, $pdfBig->getY()-1);
            $pdfBig->MultiCell(32,  $main_info_height, "", 1, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY($x_axis_main_info, $old_y);
            $pdfBig->SetXY($x_axis_main_info_second, $pdfBig->getY());
            $pdfBig->MultiCell(32,  $main_info_height, "HALL TICKET NO", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(40,  $main_info_height, ' <span style="font-family:'.$times.';">: '.$hall_ticket.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            

            $pdfBig->ln();

            $pdfBig->SetXY($x_axis_main_info, $pdfBig->getY());
			$pdfBig->MultiCell(41,  $main_info_height, "FATHER'S NAME", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$father_name.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetXY($x_axis_main_info_second, $pdfBig->getY());
            $pdfBig->MultiCell(32,  $main_info_height, "MOTHER'S NAME", 0, "L", 0, 0, '', '', true, 0, true);
			$pdfBig->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$mother_name.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
			
            $pdfBig->ln();


            $pdfBig->setFontStretching(85);
            $table_heading_y_axis =$pdfBig->getY() +5;
            $pdfBig->SetFont($timesb, '', 9, '', false);            
			$pdfBig->SetTextColor(0, 0, 0);   
            $pdfBig->SetXY(12, $table_heading_y_axis);
            $pdfBig->setCellPaddings($left = '', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(7, 12.5, 'S.<br>NO.', 1, 'C', 0, 0, '', '', true, 0, true);

			$pdfBig->SetXY($pdfBig->getX(), $table_heading_y_axis);
            $pdfBig->MultiCell(17, 12.5, 'SUBJECT CODE', 1, 'C', 0, 0, '', '', true, 0, true);    
			
            $pdfBig->setCellPaddings($left = '', $top = '4', $right = '', $bottom = '');
            $pdfBig->SetXY($pdfBig->getX(), $table_heading_y_axis);
			$pdfBig->MultiCell(71, 12.5, 'SUBJECT TITLE', 1, 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '0');
            $pdfBig->SetXY($pdfBig->getX(), $table_heading_y_axis);
            $pdfBig->MultiCell(17, 12.5, 'INTERNAL MARKS<br>(30 M)', 1, 'C', 0, 0, '', '', true, 0, true);
			
            $pdfBig->SetXY($pdfBig->getX(), $table_heading_y_axis);
            $pdfBig->MultiCell(17, 12.5, 'EXTERNAL MARKS<br>(70 M)', 1, 'C', 0, 0, '', '', true, 0, true);  
			
            $pdfBig->SetXY($pdfBig->getX(), $table_heading_y_axis);
            $pdfBig->MultiCell(15, 12.5, 'TOTAL MARKS<br>(100 M)', 1, 'C', 0, 0, '', '', true, 0, true);    
			
            $pdfBig->setCellPaddings($left = '', $top = '4', $right = '', $bottom = '0');
            $pdfBig->SetXY($pdfBig->getX(), $table_heading_y_axis);
            $pdfBig->MultiCell(15, 12.5, 'RESULT', 1, 'C', 0, 0, '', '', true, 0, true);    
			
            $pdfBig->SetXY($pdfBig->getX(), $table_heading_y_axis);
            $pdfBig->MultiCell(14, 12.5, 'CREDITS', 1, 'C', 0, 0, '', '', true, 0, true);    
			
            
            $pdfBig->SetXY($pdfBig->getX(), $table_heading_y_axis);
			$pdfBig->MultiCell(13, 12.5, 'GRADE', 1, 'C', 0, 0, '', '', true, 0, true);    

            $pdfBig->setCellPaddings($left = '', $top = '0.5', $right = '', $bottom = '0');
            $pdfBig->ln();

            $pdfBig->setFontStretching(100);
        
            $pdfBig->SetFont($times, '', $content_font_size, '', false); 
            $table_content_y =$pdfBig->getY();

            $newBelowHeigh = '';
			for ($s=0; $s < 12; $s++) {
                
                
                $sr_no=$s+1;
                $next = ($s*8);
				$SubjectCode=trim($studentData[11+$next]);
				$SubjectTitle=trim($studentData[12+$next]);
				$internalMarks=trim($studentData[13+$next]);
				$ExternalMarks=trim($studentData[14+$next]);
				$TotalMarks=trim($studentData[15+$next]);
				$Result=trim($studentData[16+$next]);
                $Credit=trim($studentData[17+$next]);		
                $Grade=trim($studentData[18+$next]);
                
                if($SubjectTitle == '' || !$SubjectTitle) {
                    $newBelowHeigh = $table_content_height1 ? $table_content_height1 : 4;
                    break;


                }
                
                $table_content_height = 5;
                $pdfBig->startTransaction();
                $pdfBig->SetFont($times, '', $content_font_size, '', false);
                // get the number of lines
                $lines = $pdfBig->MultiCell(75, $table_content_height, $SubjectTitle, 0, 'C', 0, 0, '', '', true, 0, false,true, 0);
                // restore previous object
                $pdfBig=$pdfBig->rollbackTransaction();	
                
                if($lines>1){
                    $table_content_height1 = 9;
                }else{
                    $table_content_height1 = $table_content_height;
                }


                
                $pdfBig->SetXY(12, $table_content_y);
                $pdfBig->MultiCell(7, $table_content_height1, $sr_no, "RL", 'C', 0, 0, '', '', true, 0, true);    
                $pdfBig->SetXY($pdfBig->getX(), $table_content_y);
                $pdfBig->MultiCell(17, $table_content_height1, $SubjectCode, "RL", 'C', 0, 0, '', '', true, 0, true);    
                $pdfBig->SetXY($pdfBig->getX(), $table_content_y);
                $pdfBig->MultiCell(71, $table_content_height1, $SubjectTitle, "RL", 'L', 0, 0, '', '', true, 0, true);
                $pdfBig->SetXY($pdfBig->getX(), $table_content_y);
                $pdfBig->MultiCell(17, $table_content_height1, $internalMarks, "RL", 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->SetXY($pdfBig->getX(), $table_content_y);
                $pdfBig->MultiCell(17, $table_content_height1, $ExternalMarks, "RL", 'C', 0, 0, '', '', true, 0, true);  
                $pdfBig->SetXY($pdfBig->getX(), $table_content_y);
                $pdfBig->MultiCell(15, $table_content_height1, $TotalMarks, "RL", 'C', 0, 0, '', '', true, 0, true);  
                
                $old_x=$pdfBig->getX();
                $pdfBig->SetXY($pdfBig->getX()-50, $table_content_y);  
                $pdfBig->SetTextColor(255, 255, 0);
                $pdfBig->MultiCell(0, $table_content_height1, $TotalMarks, "0", 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->SetTextColor(0, 0, 0); 
                $pdfBig->SetXY($old_x, $table_content_y);

                $pdfBig->SetXY($pdfBig->getX(), $table_content_y);
                $pdfBig->MultiCell(15, $table_content_height1, $Result, "RL", 'C', 0, 0, '', '', true, 0, true);  
                $pdfBig->SetXY($pdfBig->getX(), $table_content_y);
                $pdfBig->MultiCell(14, $table_content_height1, $Credit, "RL", 'C', 0, 0, '', '', true, 0, true);    
                $pdfBig->SetXY($pdfBig->getX(), $table_content_y);
                $pdfBig->MultiCell(13, $table_content_height1,  $Grade, "RL", 'C', 0, 0, '', '', true, 0, true); 
                

                $pdfBig->ln();
                $table_content_y =  $pdfBig->getY();

                 
                
                
                
            }
            if(empty($newBelowHeigh)){
                $newBelowHeigh = $table_content_height1 ? $table_content_height1 : 4;
            }
            
            $pdfBig->ln();

            
            // dd($newBelowHeigh);
            $bottomY = $pdfBig->getY() -$newBelowHeigh;
            
            $pdfBig->SetXY(12, $bottomY);   
            $pdfBig->Cell(95, 0, "SUBJECTS REGISTERED:  $total_subject        APPEARED: $total_appered        PASSED: $total_passed", 'LTB', false, 'L');  
            
            $old_x=$pdfBig->getX();
            $pdfBig->SetXY(9, $bottomY-0.3);  
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->MultiCell(95, $table_content_height1, $total_subject, "", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdfBig->SetXY($old_x, $bottomY);

 
            $pdfBig->SetXY(75, $bottomY-0.3);  
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->MultiCell(18, $table_content_height1, $total_appered, "", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdfBig->SetXY($old_x, $bottomY);

            $pdfBig->SetXY(94, $bottomY+4);  
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->MultiCell(18, $table_content_height1, $total_passed, "", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdfBig->SetXY($old_x, $bottomY);


            
            $pdfBig->SetXY($old_x, $bottomY);

            
            $old_x1=$pdfBig->getX();
            $pdfBig->SetXY($pdfBig->getX(), $bottomY);   
            $pdfBig->Cell(17, 0, $total_internal_marks, 'LTB', false, 'C'); 

            $old_x=$pdfBig->getX();
            $pdfBig->SetXY($old_x1, $bottomY+4);  
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->MultiCell(17, $table_content_height1, $total_internal_marks, "", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdfBig->SetXY($old_x, $bottomY);


            $old_x1=$pdfBig->getX();

            $pdfBig->SetXY($pdfBig->getX(), $bottomY);   
            $pdfBig->Cell(17, 0, $total_external_marks, 'LTB', false, 'C');  

            $old_x=$pdfBig->getX();
            $pdfBig->SetXY($old_x1, $bottomY+4);  
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->MultiCell(17, $table_content_height1, $total_external_marks, "", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdfBig->SetXY($old_x, $bottomY);

            $old_x1=$pdfBig->getX();
            $pdfBig->SetXY($pdfBig->getX(), $bottomY);   
            $pdfBig->Cell(15, 0,  "$total_marks_100",'LTB', false, 'C');  

            $old_x=$pdfBig->getX();
            $pdfBig->SetXY($old_x1, $bottomY+4);  
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->MultiCell(17, $table_content_height1, $total_marks_100, "", 'C', 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdfBig->SetXY($old_x, $bottomY);

            $pdfBig->SetXY($pdfBig->getX(), $bottomY);   
            $pdfBig->Cell(15, 0, '', 'LTBR', false, 'C');  
            $pdfBig->SetXY($pdfBig->getX(), $bottomY);   
            $pdfBig->Cell(14, 0, "$total_credits", 'LTBR', false, 'C');  
            $pdfBig->SetXY($pdfBig->getX(), $bottomY);   
            $pdfBig->Cell(13, 0, "", 'LTBR', false, 'C');  


            $pdfBig->ln();
            
            $pdfBig->SetXY(12, $pdfBig->getY());        
            $pdfBig->Cell(180, 7, "AGGREGATE (IN WORDS) : $agreegate_in_word", 0, false, 'L');   
            $pdfBig->ln();

            $pdfBig->SetXY(12, $pdfBig->getY());   
            $pdfBig->Cell(180, 7, "SEMESTER GRADE POINT AVERAGE (SGPA) : $sgpa", 0, false, 'L');  
            $old_x=$pdfBig->getX();
            $pdfBig->SetXY(88,$pdfBig->getY()+1);  
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->MultiCell(180, 7, $sgpa, "", 'L', 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdfBig->SetXY($old_x, $pdfBig->getY());
            $pdfBig->ln();

            $pdfBig->SetXY(12, $pdfBig->getY());   
            $pdfBig->Cell(180, 7, "CUMULATIVE GRADE POINT AVERAGE (CGPA) : $cgpa", 0, false, 'L'); 
           
            $old_x=$pdfBig->getX();
            $pdfBig->SetXY(94,$pdfBig->getY()+1);  
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->MultiCell(180, 7, $cgpa, "", 'L', 0, 0, '', '', true, 0, true);
            $pdfBig->SetTextColor(0, 0, 0); 
            $pdfBig->SetXY($old_x, $pdfBig->getY());

            $pdfBig->ln();


            $pdfBig->SetXY(12, 245);   
            $pdfBig->Cell(50, 0, "VERIFIED BY", 0, false, 'C');  
            $pdfBig->SetXY(90, 245);   
            $pdfBig->Cell(50, 0, "CONTROLLER OF EXAMINATION", 0, false, 'C'); 
            $pdfBig->SetXY(150, 245);   
            $pdfBig->Cell(50, 0, "PRINCIPAL", 0, false, 'C');  


            $pdfBig->SetXY(12, 253);   
            $pdfBig->Cell(50, 0, "DATE OF ISSUE  : $date_of_issue", 0, false, 'C');  

          
            // $pdfBig->SetXY(10, 283);   
            // $pdfBig->Cell(100, 0, "Note:Any discrepancy must be reprensented within 15 days from the date of issue. ", 0, false, 'L');  
            // $pdfBig->SetXY(120, 283);   
            // $pdfBig->Cell(100, 0, "MP:MALPRACTICE", 0, false, 'L');  
            // $pdfBig->SetXY(180, 283);   
            // $pdfBig->Cell(100, 0, "AB:ABSENT", 0, false, 'L');  
			/*end pdf*/		
            
            // Start PDF
            $pdf->SetFont($timesb, '', $content_font_size, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $x_axis_main_info = 12;
            $x_axis_main_info_second = 120; 
            $y_axis_main_info =43;
            $main_info_height = 7;
            $pdf->SetXY($x_axis_main_info, $y_axis_main_info);
			$pdf->MultiCell(41,  $main_info_height, "MEMO NO", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$memo_no.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            

            $pdf->SetXY(154, $y_axis_main_info-1);
            $pdf->MultiCell(32,  $main_info_height, "", 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($x_axis_main_info_second, $y_axis_main_info);
            $pdf->MultiCell(32,  $main_info_height, "APAAR ID", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$appar_id.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->ln();
            $pdf->SetXY($x_axis_main_info, $pdf->getY());
			$pdf->MultiCell(41,  $main_info_height, "SERIAL NO", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$serial_no.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            $pdf->ln();
            
            $pdf->SetXY($x_axis_main_info, $pdf->getY());
			$pdf->MultiCell(41,  $main_info_height, "EXAMINATION", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$examination.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
           
            $pdf->SetXY($x_axis_main_info_second, $pdf->getY());
            $pdf->MultiCell(32,  $main_info_height, "MONTH & YEAR", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$month_year.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            $pdf->ln();
            
            $pdf->SetXY($x_axis_main_info, $pdf->getY());
			$pdf->MultiCell(41,  $main_info_height, "BRANCH", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$branch.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            $pdf->ln();
            $pdf->SetXY($x_axis_main_info, $pdf->getY());
			$pdf->MultiCell(41,  $main_info_height, "NAME OF THE STUDENT", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$candidate_name.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            

            $old_y = $pdf->getY();

            $pdf->SetXY(154, $pdf->getY()-1);
            $pdf->MultiCell(32,  $main_info_height, "", 1, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetXY($x_axis_main_info, $old_y);
            $pdf->SetXY($x_axis_main_info_second, $pdf->getY());
            $pdf->MultiCell(32,  $main_info_height, "HALL TICKET NO", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$hall_ticket.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            $pdf->ln();
            
            $pdf->SetXY($x_axis_main_info, $pdf->getY());
			$pdf->MultiCell(41,  $main_info_height, "FATHER'S NAME", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(125.5,  $main_info_height, '<span style="font-family:'.$times.';">: '.$father_name.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetXY($x_axis_main_info_second, $pdf->getY());
            $pdf->MultiCell(32,  $main_info_height, "MOTHER'S NAME", 0, "L", 0, 0, '', '', true, 0, true);
			$pdf->MultiCell(80,  $main_info_height, '<span style="font-family:'.$times.';">: '.$mother_name.'</span>', 0, "L", 0, 0, '', '', true, 0, true);
			
            $pdf->ln();


            $pdf->setFontStretching(85);
            $table_heading_y_axis =$pdf->getY() +5;
            $pdf->SetFont($timesb, '', 9, '', false);            
			$pdf->SetTextColor(0, 0, 0);   
            $pdf->SetXY(12, $table_heading_y_axis);
            $pdf->setCellPaddings($left = '', $top = '2.5', $right = '', $bottom = '');
            $pdf->MultiCell(7, 12.5, 'S.<br>NO.', 1, 'C', 0, 0, '', '', true, 0, true);

			$pdf->SetXY($pdf->getX(), $table_heading_y_axis);
            $pdf->MultiCell(17, 12.5, 'SUBJECT CODE', 1, 'C', 0, 0, '', '', true, 0, true);    
			
            $pdf->setCellPaddings($left = '', $top = '4', $right = '', $bottom = '');
            $pdf->SetXY($pdf->getX(), $table_heading_y_axis);
			$pdf->MultiCell(71, 12.5, 'SUBJECT TITLE', 1, 'C', 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '0');
            $pdf->SetXY($pdf->getX(), $table_heading_y_axis);
            $pdf->MultiCell(17, 12.5, 'INTERNAL MARKS<br>(30 M)', 1, 'C', 0, 0, '', '', true, 0, true);
			
            $pdf->SetXY($pdf->getX(), $table_heading_y_axis);
            $pdf->MultiCell(17, 12.5, 'EXTERNAL MARKS<br>(70 M)', 1, 'C', 0, 0, '', '', true, 0, true);  
			
            $pdf->SetXY($pdf->getX(), $table_heading_y_axis);
            $pdf->MultiCell(15, 12.5, 'TOTAL MARKS<br>(100 M)', 1, 'C', 0, 0, '', '', true, 0, true);    
			
            $pdf->setCellPaddings($left = '', $top = '4', $right = '', $bottom = '0');
            $pdf->SetXY($pdf->getX(), $table_heading_y_axis);
            $pdf->MultiCell(15, 12.5, 'RESULT', 1, 'C', 0, 0, '', '', true, 0, true);    
			
            $pdf->SetXY($pdf->getX(), $table_heading_y_axis);
            $pdf->MultiCell(14, 12.5, 'CREDITS', 1, 'C', 0, 0, '', '', true, 0, true);    
			
            
            $pdf->SetXY($pdf->getX(), $table_heading_y_axis);
			$pdf->MultiCell(13, 12.5, 'GRADE', 1, 'C', 0, 0, '', '', true, 0, true);    

            $pdf->setCellPaddings($left = '', $top = '0.5', $right = '', $bottom = '0');
            $pdf->ln();

            $pdf->setFontStretching(100);
        
            $pdf->SetFont($times, '', $content_font_size, '', false); 
            $table_content_y =$pdf->getY();

            $newBelowHeigh = '';
			for ($s=0; $s < 12; $s++) {
                
                
                $sr_no=$s+1;
                $next = ($s*8);
				$SubjectCode=trim($studentData[11+$next]);
				$SubjectTitle=trim($studentData[12+$next]);
				$internalMarks=trim($studentData[13+$next]);
				$ExternalMarks=trim($studentData[14+$next]);
				$TotalMarks=trim($studentData[15+$next]);
				$Result=trim($studentData[16+$next]);
                $Credit=trim($studentData[17+$next]);		
                $Grade=trim($studentData[18+$next]);
                
                if($SubjectTitle == '' || !$SubjectTitle) {
                    $newBelowHeigh = $table_content_height1 ? $table_content_height1 : 4;
                    break;


                }
                $table_content_height = 5;
                $pdf->startTransaction();
                $pdf->SetFont($times, '', $content_font_size, '', false);
                // get the number of lines
                $lines = $pdf->MultiCell(75, $table_content_height, $SubjectTitle, 0, 'C', 0, 0, '', '', true, 0, false,true, 0);
                // restore previous object
                $pdf=$pdf->rollbackTransaction();	
                
                if($lines>1){
                    $table_content_height1 = 9;
                }else{
                    $table_content_height1 = $table_content_height;
                }


                
                $pdf->SetXY(12, $table_content_y);
                $pdf->MultiCell(7, $table_content_height1, $sr_no, "RL", 'C', 0, 0, '', '', true, 0, true);    
                $pdf->SetXY($pdf->getX(), $table_content_y);
                $pdf->MultiCell(17, $table_content_height1, $SubjectCode, "RL", 'C', 0, 0, '', '', true, 0, true);    
                $pdf->SetXY($pdf->getX(), $table_content_y);
                $pdf->MultiCell(71, $table_content_height1, $SubjectTitle, "RL", 'L', 0, 0, '', '', true, 0, true);
                $pdf->SetXY($pdf->getX(), $table_content_y);
                $pdf->MultiCell(17, $table_content_height1, $internalMarks, "RL", 'C', 0, 0, '', '', true, 0, true);
                $pdf->SetXY($pdf->getX(), $table_content_y);
                $pdf->MultiCell(17, $table_content_height1, $ExternalMarks, "RL", 'C', 0, 0, '', '', true, 0, true);  
                $pdf->SetXY($pdf->getX(), $table_content_y);
                $pdf->MultiCell(15, $table_content_height1, $TotalMarks, "RL", 'C', 0, 0, '', '', true, 0, true);    
                $pdf->SetXY($pdf->getX(), $table_content_y);
                $pdf->MultiCell(15, $table_content_height1, $Result, "RL", 'C', 0, 0, '', '', true, 0, true);  
                $pdf->SetXY($pdf->getX(), $table_content_y);
                $pdf->MultiCell(14, $table_content_height1, $Credit, "RL", 'C', 0, 0, '', '', true, 0, true);    
                $pdf->SetXY($pdf->getX(), $table_content_y);
                $pdf->MultiCell(13, $table_content_height1,  $Grade, "RL", 'C', 0, 0, '', '', true, 0, true); 
                

                $pdf->ln();
                $table_content_y =  $pdf->getY();

                 
                
                
                
            }

            
            $pdf->ln();
            if(empty($newBelowHeigh)){
                $newBelowHeigh = $table_content_height1 ? $table_content_height1 : 4;
            }
            

            $bottomY = $pdf->getY() -$newBelowHeigh;
            
            $pdf->SetXY(12, $bottomY);   
            $pdf->Cell(95, 0, "SUBJECTS REGISTERED:  $total_subject        APPEARED: $total_appered        PASSED: $total_passed", 'LTB', false, 'L');  
            $pdf->SetXY($pdf->getX(), $bottomY);   
            $pdf->Cell(17, 0, $total_internal_marks, 'LTB', false, 'C');  
            $pdf->SetXY($pdf->getX(), $bottomY);   
            $pdf->Cell(17, 0, $total_external_marks, 'LTB', false, 'C');  
            $pdf->SetXY($pdf->getX(), $bottomY);   
            $pdf->Cell(15, 0,  "$total_marks_100",'LTB', false, 'C');  
            $pdf->SetXY($pdf->getX(), $bottomY);   
            $pdf->Cell(15, 0, '', 'LTBR', false, 'C');  
            $pdf->SetXY($pdf->getX(), $bottomY);   
            $pdf->Cell(14, 0, "$total_credits", 'LTBR', false, 'C');  
            $pdf->SetXY($pdf->getX(), $bottomY);   
            $pdf->Cell(13, 0, "", 'LTBR', false, 'C');  


            $pdf->ln();
            
            $pdf->SetXY(12, $pdf->getY());        
            $pdf->Cell(180, 7, "AGGREGATE (IN WORDS) : $agreegate_in_word", 0, false, 'L');  
            $pdf->ln();

            $pdf->SetXY(12, $pdf->getY());   
            $pdf->Cell(180, 7, "SEMESTER GRADE POINT AVERAGE (SGPA) : $sgpa", 0, false, 'L');  
            $pdf->ln();

            $pdf->SetXY(12, $pdf->getY());   
            $pdf->Cell(180, 7, "CUMULATIVE GRADE POINT AVERAGE (CGPA) : $cgpa", 0, false, 'L'); 
            $pdf->ln();


            $pdf->SetXY(12, 245);   
            $pdf->Cell(50, 0, "VERIFIED BY", 0, false, 'C');  
            $pdf->SetXY(90, 245);   
            $pdf->Cell(50, 0, "CONTROLLER OF EXAMINATION", 0, false, 'C'); 
            $pdf->SetXY(150, 245);   
            $pdf->Cell(50, 0, "PRINCIPAL", 0, false, 'C');  


            $pdf->SetXY(12, 253);   
            $pdf->Cell(50, 0, "DATE OF ISSUE  : $date_of_issue", 0, false, 'C');  

          
            // $pdf->SetXY(10, 283);   
            // $pdf->Cell(100, 0, "Note:Any discrepancy must be reprensented within 15 days from the date of issue. ", 0, false, 'L');  
            // $pdf->SetXY(120, 283);   
            // $pdf->Cell(100, 0, "MP:MALPRACTICE", 0, false, 'L');  
            // $pdf->SetXY(180, 283);   
            // $pdf->Cell(100, 0, "AB:ABSENT", 0, false, 'L');  
            
			
			// Ghost image
			$nameOrg=$candidate_name;
			/*$ghost_font_size = '13';
			$ghostImagex = 70;
			$ghostImagey = 269.5;
			$ghostImageWidth = 55;//68
			$ghostImageHeight = 9.8;
			$name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
			$tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
			$pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);			
			$pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);*/
			 $ghost_font_size = '12';
            $ghostImagex = 92;
            $ghostImagey = 255;
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
            \Log::info($GUID);
			//qr code    
			$dt = date("_ymdHis");
			$str=$GUID.$dt;
			

            $encryptedString = $studentData[1].','. $studentData[8];
            $QR_Output = $studentData[1].','. $studentData[8];

            $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
			$encryptedString = strtoupper(md5($str));
			$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
			$qrCodex = 173; 
			$qrCodey = 190;
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
			 
            $microline_str = $studentData[8];
            $microline_str = strtoupper(preg_replace('/\s+/', '', $microline_str));
            $microlinestr = $microline_str;
            $pdf->SetFont($ArialB, '', 1.2, '', false);
            $pdf->SetTextColor(0, 0, 0);


            $pdf->StartTransform();

            $pdf->SetXY(180, 189);
            $pdf->Cell(30, 0, $microlinestr, 0, false, 'L');

            $pdfBig->SetFont($ArialB, '', 1.2, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->StartTransform();
            $pdfBig->SetXY(180, 189);
            $pdfBig->Cell(30, 0, $microlinestr, 0, false, 'L');

            //left side
			$sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\COE_Sign_new.png';
            $sign1_x = 95;
            $sign1_y = 235;
            $sign1_Width = 31.75;
            $sign1_Height = 9.79;

            $upload_sign1_org = $sign1;
            $pathInfo = pathinfo($sign1);
            $sign1 = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$serial_no.".$pathInfo['extension'];
            \File::copy($upload_sign1_org,$sign1); 

            $pdfBig->image($sign1,$sign1_x,$sign1_y,$sign1_Width,$sign1_Height,"",'','L',true,3600);
            $pdfBig->setPageMark();
            $pdf->image($sign1,$sign1_x,$sign1_y,$sign1_Width,$sign1_Height,"",'','L',true,3600);
            $pdf->setPageMark();
			//right side
           
            $sign2 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Principal_Sign_new.png';
            $upload_sign2_org = $sign2;
            $pathInfo = pathinfo($sign2);
            $sign2 = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$serial_no.".$pathInfo['extension'];
            \File::copy($upload_sign2_org,$sign2);
            $sign2_x = 162;
            $sign2_y = 235;
            $sign2_Width = 31.75;
            $sign2_Height = 9.79;
            $pdfBig->image($sign2,$sign2_x,$sign2_y,$sign2_Width,$sign2_Height,"",'','L',true,3600);
            $pdfBig->setPageMark();
            $pdf->image($sign2,$sign2_x,$sign2_y,$sign2_Width,$sign2_Height,"",'','L',true,3600);
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
			$barcodey = 255;
             
			$barcodeWidth = 54;
			$barodeHeight = 13;
			$pdf->SetAlpha(1);
			$pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			$pdfBig->SetAlpha(1);
			$pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			//$pdfBig->SetFont($arial, '', 9, '', false);
			//$pdfBig->SetXY(142, 275);
            //$pdfBig->MultiCell(0, 0, trim($print_serial_no), 0, 'L', 0, 0);
			 
			$str = $nameOrg;
			$str = strtoupper(preg_replace('/\s+/', '', $str)); 
						
			$pdfBig->SetFont($graduateR, '', 8, '', false);
			$pdfBig->SetTextColor(0, 0, 0);
			$pdfBig->SetXY(12, 275.5);        
			$pdfBig->Cell(21, 0, $QR_Code_No, 0, false, 'C'); 
			
			
			/*Point Page Start*/
			// $pdfBig->AddPage();
			$back_img = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\Engg_grade_card_bg';   

           

			if($previewPdf==1){
				if($previewWithoutBg!=1){
					$pdfBig->Image($back_img, 0, 0, '210', '297', "JPG", '', 'R', true);
				}
			}
			 
			/*
			$pdfBig->SetFont($poppinsM, '', 13, '', false);
			$pdfBig->SetXY(9, 275);
			$pdfBig->MultiCell(0, 180, $university_name, 0, 'C', 0, 0, '', '', true, 0, true);
			$pdfBig->SetFont($poppinsR, '', 10, '', false);
			$pdfBig->SetXY(9, 281);
			$pdfBig->MultiCell(0, 180, $line2, 0, 'C', 0, 0, '', '', true, 0, true);
			$pdfBig->SetXY(9, 286);
			$pdfBig->MultiCell(0, 180, $line3, 0, 'C', 0, 0, '', '', true, 0, true);
			*/
			
			/*Point Page End*/			
			
			if($previewPdf!=1){

                $certName = str_replace("/", "_", $GUID) .".pdf";
				
				$myPath = public_path().'/backend/temp_pdf_file';

				$fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;

    //             $filename = public_path().'/backend/tcpdf/examples/'.$GUID.".pdf";
				// $pdf->output($filename, 'F');

                $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');
                

				$this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id);

				//  $this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id);

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
            @unlink($sign1);
            @unlink($sign2);
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
        $this->updateCardNo('jntuacekEGC',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
       }
       $msg = '';
        
        $file_name =  str_replace("/", "_",'jntuacekEGC'.date("Ymdhms")).'.pdf';
        // $file_name = 'test.pdf';
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();


       $filename = public_path().'/backend/tcpdf/examples/'.$file_name;
        
        $pdfBig->output($filename,'F');

        if($previewPdf!=1){
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            $no_of_records = $pdf_data['highestrow'];
            $user = $admin_id['username'];
            $template_name="jntuacekEGC";
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
  //       @unlink($file1);

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

  
}