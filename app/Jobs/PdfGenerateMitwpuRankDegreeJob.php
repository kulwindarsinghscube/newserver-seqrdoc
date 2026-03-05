<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\models\BackgroundTemplateMaster;
use App\models\pdf2pdf\TemplateMaster;
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

class PdfGenerateMitwpuRankDegreeJob
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
		
        $template_id=$pdf_data['template_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
        
        $year=$pdf_data['year'];

        $exceptionGeneration=$pdf_data['exceptionGeneration'];

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

        $template_id=2;
        $template_data = TemplateMaster::select('id','template_name','is_block_chain','bc_document_description','bc_document_type','bc_contract_address')->where('id',$template_id)->first();

      
        $ghostImgArr = array();
        // $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        // $pdfBig->SetCreator(PDF_CREATOR);
        // $pdfBig->SetAuthor('TCPDF');
        // $pdfBig->SetTitle('Certificate');
        // $pdfBig->SetSubject('');

        $loader_data =CoreHelper::getLoaderJson($pdf_data['loader_token']);
        
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
        $KRDEV010 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KRDEV010.ttf', 'TrueTypeUnicode', '', 96);
        $KRDEV100 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KrutiDev100Regular.ttf', 'TrueTypeUnicode', '', 96);
        $KRDEV100Thin = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100_thin.TTF', 'TrueTypeUnicode', '', 96);
        $KRDEV100R = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100.TTF', 'TrueTypeUnicode', '', 96);
        
        $KRDEV100B = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100_bold.ttf', 'TrueTypeUnicode', '', 96);
        
        $MTCORSVA = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MTCORSVA_5.ttf', 'TrueTypeUnicode', '', 96);

        $calibri = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Calibri.TTF', 'TrueTypeUnicode', '', 96);
        $calibrib = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\CalibriBold.TTF', 'TrueTypeUnicode', '', 96);
        
        $AkrutiDevPriya_B = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\AkrutiDevPriya_B.ttf', 'TrueTypeUnicode', '', 96);

        // AlgerianRegular.ttf
        $preview_serial_no=1;
		$card_serial_no="";
        $log_serial_no = 1;
        // $cardDetails=$this->getNextCardNo('MitwpuRank');
        // $card_serial_no=$cardDetails->next_serial_no;
        // $generated_documents=0;  //for custom loader
        if($studentDataOrg&&!empty($studentDataOrg)){
            foreach ($studentDataOrg as $studentData) {
            
                // if($card_serial_no>999999&&$previewPdf!=1){
                // 	echo "<h5>Your card series ended...!</h5>";
                // 	exit;
                // }
                //For Custom Loader
                $startTimeLoader =  date('Y-m-d H:i:s');    
                $high_res_bg="MIT_WPU_Certificate_BG.jpg"; // bestiu_pdc_bg, GradeCard.jpg
                $low_res_bg="MIT_WPU_Certificate_BG.jpg";
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
                    // $date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
                    // $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
                    // $pdfBig->SetTextColor(192,192,192);
                    // $pdfBig->SetXY($date_nox, $date_noy);
                    // $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
                    // $pdfBig->SetTextColor(0,0,0,100,false,'');
                    // $pdfBig->SetFont($arialNarrowB, '', 9, '', false);
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
            
                // $student_photo = trim($studentData[16]);
                // $full_name_krutidev = trim($studentData[3]);
                // $course_name_krutidev = trim($studentData[12]);
                // $rank_krutidev = trim($studentData[15]);
                // $full_name = trim($studentData[1]);
                // $course_name = trim($studentData[10]);
                // $rank = trim($studentData[13]);

                ///////////////////
                $PRN = trim($studentData[0]);
                $full_name = trim($studentData[1]);
                $full_name_hindi = trim($studentData[2]);
                $full_name_krutidev = trim($studentData[3]);
                $full_name_krutidev = htmlspecialchars($full_name_krutidev);

                $mother_name = trim($studentData[4]);
                $mother_name_hindi = trim($studentData[5]);
                $mother_name_krutidev = trim($studentData[6]);
                $mother_name_krutidev = htmlspecialchars($mother_name_krutidev);
                
                $father_name = trim($studentData[7]);
                $father_name_hindi = trim($studentData[8]);
                $father_name_krutidev = trim($studentData[9]);
                $father_name_krutidev = htmlspecialchars($father_name_krutidev);
                
                $course_name = trim($studentData[10]);
                $course_name_hindi = trim($studentData[11]);
                $course_name_krutidev = trim($studentData[12]);
                $course_name_krutidev = htmlspecialchars($course_name_krutidev);
            
                $rank = trim($studentData[13]);
                $rank_hindi = trim($studentData[14]);
                $rank_krutidev = trim($studentData[15]);
                $rank_krutidev = htmlspecialchars($rank_krutidev);

                $student_photo = trim($studentData[16]);
                
                $faculty_name = trim($studentData[17]);
                $faculty_name_hindi = trim($studentData[18]);
                $faculty_name_krutidev = trim($studentData[19]);
                $faculty_name_krutidev = htmlspecialchars($faculty_name_krutidev);

                $certificate_id = trim($studentData[20]);
                $completion_date = trim($studentData[21]);
                $completion_date_krutidev = trim($studentData[22]);
                $completion_date_krutidev = htmlspecialchars($completion_date_krutidev);
                
                // $issue_date= '2024-10-19';
                // $issue_date_krutidev= '19 vDVwcj „å„†';

                // $issue_date= $year.'-10-19';
                // $issue_date_krutidev= '19 vDVwcj '.$year;

                if($year == 2024) {
                    $issue_date= $year.'-10-19';
                    $issue_date_krutidev= '19 vDVwcj '.$year;
                } else {
                    $issue_date= $year.'-10-11';
                    $issue_date_krutidev= '11 vDVwcj '.$year;
                }
                
                // $issue_date = trim($studentData[23]);
                // $issue_date_krutidev = trim($studentData[24]);


                $specialization = trim($studentData[25]);
                $specialization_hindi = trim($studentData[26]);
                $specialization_krutidev = trim($studentData[27]);
                $specialization_krutidev = htmlspecialchars($specialization_krutidev);


                $serialNo = trim($studentData[28]);

                
                //Start pdfBig  
               // $profile_path_org = public_path().'\\'.$subdomain[0].'\backend\students\\'.$student_photo;  

                $extensions = ['jpeg','jpg', 'png'];
                $nameOnly = pathinfo($student_photo, PATHINFO_FILENAME);
                $student_photo = null;
                $profile_path_org = '';
                foreach ($extensions as $ext) {
                    $newFilename = $nameOnly . '.' . $ext;

                    $path = public_path() . DIRECTORY_SEPARATOR . $subdomain[0] .
                            DIRECTORY_SEPARATOR . 'backend' .
                            DIRECTORY_SEPARATOR . 'students' .
                            DIRECTORY_SEPARATOR . $newFilename;

                    if (file_exists($path)) {
                        $student_photo = $newFilename;
                        $profile_path_org = $path;
                        break;
                    }
                } 

                if(file_exists($profile_path_org)) {


                    $profilex = 165;
                    $profiley = 15;
                    $profileWidth = 25;
                    $profileHeight = 25;
                    
                    $pdfBig->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
                    $pdfBig->setPageMark();

                }

                $pdfBig->SetTextColor(50,50,154);
                $pdfBig->SetFont($KRDEV100, '', 16.02, '', false);
                $pdfBig->SetXY(15, 51);
                $pdfBig->MultiCell(180, 0, "laLFkkid v/;{k rFkk dk;Zdkjh v/;{k] iz'kkldh; vf/kdkjh ,oa ".$faculty_name_krutidev." dh flQkfj'k dks vuqeksfnr djrs gq,]", 0, 'C', 0, 0, '', '', true, 0, true);


                $pdfBig->SetTextColor(255,0,0);
                $pdfBig->SetXY(15, 68);
                // $pdfBig->MultiCell(180, 0, $full_name_krutidev.' '.$mother_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);

                $headerPart = '';
                $headerPart1 = '';
                if($exceptionGeneration == 1){
                    
                    $headerPart = $full_name_krutidev;
                    
                    if($mother_name_krutidev) {
                        //$headerPart .= '<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span>
                        $headerPart .= ' <span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span> <span style="color:#32329a;font-family:'.$KRDEV100.';font-size:16.02px;">ek¡ dk uke</span> '.$mother_name_krutidev;
                    }

                    $pdfBig->SetFont($AkrutiDevPriya_B, '', 19.98, '', false);
                    $pdfBig->MultiCell(180, 0, $headerPart , 0, 'C', 0, 0, '', '', true, 0, true);
                    
                } else {
                    $headerPart1 = $full_name_krutidev;

                    if($mother_name_krutidev) {
                        $headerPart1 .= '<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span> <span style="color:#32329a;font-family:'.$KRDEV100.';font-size:16.02px;">ek¡ dk uke</span> '.$mother_name_krutidev;
                    }

                    $pdfBig->SetFont($KRDEV100B, '', 19.98, '', false);
                    $pdfBig->MultiCell(180, 0, $headerPart1 , 0, 'C', 0, 0, '', '', true, 0, true);
                }

                // $pdfBig->SetFont($KRDEV100B, '', 19.98, '', false);
                // $pdfBig->MultiCell(180, 0, $full_name_krutidev .'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span> <span style="color:#32329a;font-family:'.$KRDEV100.';font-size:16.02px;">ek¡ dk uke</span> '.$mother_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);


                $pdfBig->SetTextColor(50,50,154);
                $pdfBig->SetFont($KRDEV100, '', 16.02, '', false);
                $pdfBig->SetXY(15, 78);
                $pdfBig->MultiCell(180, 0, "dks", 0, 'C', 0, 0, '', '', true, 0, true);            

                // if(stripos($course_name_krutidev, 'fMIyksek') !== FALSE){


                //     if(stripos($specialization_krutidev, 'ix fMIyksek bu') !== FALSE){
                //         $specialization_krutidev = str_replace('ix fMIyksek bu','',$specialization_krutidev);
                //     }

                //     $pdfBig->SetTextColor(255,0,0);

                //     if ($course_name== "Doctor of Philosophy (Electronics and Communication Engineering)") {
                //         $pdfBig->SetFont($KRDEV100B, '', 19.5, '', false);
                //     } else {
                //         $pdfBig->SetFont($KRDEV100B, '', 19.98, '', false);
                //     }
                //     // $pdfBig->SetFont($KRDEV100B, '', 19.98, '', false);
                    
                //     $pdfBig->SetXY(15, 86);
                //     if($specialization_krutidev){
                //         $pdfBig->MultiCell(180, 0, $course_name_krutidev.' bu '.$specialization_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);
                //     } else {
                //         $pdfBig->MultiCell(180, 0, $course_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);
                //     }

                //     $pdfBig->Ln();

                    

                // } else {
                    $pdfBig->SetTextColor(255,0,0);

                    if ($course_name== "Doctor of Philosophy (Electronics and Communication Engineering)") {
                        $pdfBig->SetFont($KRDEV100B, '', 19.5, '', false);
                    } else {
                        $pdfBig->SetFont($KRDEV100B, '', 19.98, '', false);
                    }

                    // $pdfBig->SetFont($KRDEV100B, '', 19.98, '', false);
                    $pdfBig->SetXY(15, 86);
                    $pdfBig->MultiCell(180, 0, $course_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);

                    $pdfBig->Ln();
                    
                // }


                $pdfBig->SetTextColor(50,50,154);
                $pdfBig->SetFont($KRDEV100, '', 16.02, '', false);
                $pdfBig->SetXY(15, $pdfBig->GetY()+2);

                // if(stripos($course_name_krutidev, 'fMIyksek') !== FALSE){
                    
                //     $pdfBig->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".$completion_date_krutidev." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd mUgksausa ".$rank_krutidev." Js.kh izkIr dh gSA", 0, 'C', 0, 0, '', '', true, 0, true);
                    

                // } else {

                    if($specialization_krutidev) {
                        $pdfBig->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".$completion_date_krutidev." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd ".$specialization_krutidev." Lis'kykbts'ku esa mUgksausa ".$rank_krutidev." Js.kh izkIr dh gSA", 0, 'C', 0, 0, '', '', true, 0, true);
                    } else {
                        $pdfBig->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".$completion_date_krutidev." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd mUgksausa ".$rank_krutidev." Js.kh izkIr dh gSA", 0, 'C', 0, 0, '', '', true, 0, true);
                    }

                // }
                    
                

                $pdfBig->Ln();
                $pdfBig->SetTextColor(50,50,154);
                $pdfBig->SetFont($KRDEV100, '', 16.02, '', false);
                // $pdfBig->SetXY(15, 113);
                $pdfBig->SetXY(15, $pdfBig->GetY()+2);
                $pdfBig->MultiCell(180, 0, "bls ekU;rk nsrs gq, vkt ".$issue_date_krutidev." ds volj ij ge vius uke rFkk fo'ofo|ky; dh eksgj ds vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);


                $pdfBig->SetTextColor(50,50,154);
                $pdfBig->SetFont($MTCORSVA, '', 16.02, '', false);
                $pdfBig->SetXY(10, 141);
                $pdfBig->MultiCell(190, 0, "The Founder President and the Executive President approbating the recommendations of the Governing Body and ".$faculty_name." hereby confer upon", 0, 'C', 0, 0, '', '', true, 0, true);


                $pdfBig->SetTextColor(255,0,0);
                $pdfBig->SetFont($timesb, '', 19.98, '', false);
                $pdfBig->SetXY(10, 158);
                // $pdfBig->MultiCell(190, 0, $full_name.' '.$mother_name, 0, 'C', 0, 0, '', '', true, 0, true);

                // $pdfBig->MultiCell(190, 0, trim(ucwords(strtolower($full_name))).' <span style="font-size:16;display: flex; align-items: center;">'.  trim(ucwords(strtolower($mother_name))).'</span>', 0, 'C', 0, 0, '', '', true, 0, true);
                
                $nameInEnglish = '';

                $nameInEnglish = trim(ucwords(strtolower($full_name))).'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px; ">';
                if(!empty($mother_name)){
                    $nameInEnglish .= ', Mother\'s Name</span> '.trim(ucwords(strtolower($mother_name)));
                }
                $pdfBig->MultiCell(190, 0, $nameInEnglish , 0, 'C', 0, 0, '', '', true, 0, true);
                

                // $pdfBig->SetTextColor(50,50,154);
                // $pdfBig->SetFont($MTCORSVA, '', 16.02, '', false);
                // $pdfBig->SetXY(10, 167.5);
                // $pdfBig->MultiCell(190, 0, "the Degree of", 0, 'C', 0, 0, '', '', true, 0, true);


                // $pdfBig->SetTextColor(255,0,0);
                // $pdfBig->SetFont($timesb, '', 19.98, '', false);
                // $pdfBig->SetXY(10, 174);
                // $pdfBig->MultiCell(190, 0, $course_name, 0, 'C', 0, 0, '', '', true, 0, true);


                // if(stripos($course_name, 'Diploma') !== FALSE){


                //     if(stripos($specialization, 'PG Diploma in') !== FALSE){
                //         $specialization = str_replace('PG Diploma in','',$specialization);
                //     }
                    

                //     $pdfBig->SetTextColor(50,50,154);
                //     $pdfBig->SetFont($MTCORSVA, '', 16.02, '', false);
                //     $pdfBig->SetXY(10, 167.5);
                //     $pdfBig->MultiCell(190, 0, "the", 0, 'C', 0, 0, '', '', true, 0, true);

                //     $pdfBig->SetTextColor(255,0,0);
                //     $pdfBig->SetFont($timesb, '', 19.98, '', false);
                //     $pdfBig->SetXY(10, 174);
                //     if($specialization) {
                //         $pdfBig->MultiCell(190, 0, $course_name .' in '.$specialization , 0, 'C', 0, 0, '', '', true, 0, true);
                //     } else {
                //         $pdfBig->MultiCell(190, 0, $course_name , 0, 'C', 0, 0, '', '', true, 0, true);
                //     }

                //     $pdfBig->Ln();
                // } else {
                    

                    $pdfBig->SetTextColor(50,50,154);
                    $pdfBig->SetFont($MTCORSVA, '', 16.02, '', false);
                    $pdfBig->SetXY(10, 167.5);
                    $pdfBig->MultiCell(190, 0, "the Degree of", 0, 'C', 0, 0, '', '', true, 0, true);

                    $pdfBig->SetTextColor(255,0,0);
                    $pdfBig->SetFont($timesb, '', 19.98, '', false);
                    $pdfBig->SetXY(10, 174);
                    $pdfBig->MultiCell(190, 0, $course_name, 0, 'C', 0, 0, '', '', true, 0, true);
                    
                    $pdfBig->Ln();
                // }

            // $completion_date_new = date("F Y", strtotime($completion_date));
                $completion_date_new =$completion_date;

                $specilaText = '';
                if($specialization) {
                    $specilaText = "in ".$specialization." Specialisation";
                }

                $issue_formatted_date = '';
                if($issue_date) {
                    $issue_formatted_date = date("jS", strtotime($issue_date))  . " day of " . date("F", strtotime($issue_date)) . " in the year " . date("Y", strtotime($issue_date));
                }

                $pdfBig->SetTextColor(50,50,154);
                $pdfBig->SetFont($MTCORSVA, '', 16.02, '', false);
                $pdfBig->SetXY(10, $pdfBig->GetY()+2);

                // if(stripos($course_name, 'Diploma') !== FALSE){
                //     $pdfBig->MultiCell(190, 0, " with ".$rank." Rank secured 
                //         in the examination held in ".$completion_date_new.".
                //         <br>In recognition we have hereunder placed our names and
                //         <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);
                // } else {
                    if($specialization) {

                        $pdfBig->MultiCell(190, 0, $specilaText." with ".$rank." Rank secured 
                        <br>in the examination held in ".$completion_date_new.".
                        <br>In recognition we have hereunder placed our names and
                        <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);
        
                    } else {
        
                        $pdfBig->MultiCell(190, 0, " with ".$rank." Rank secured 
                        in the examination held in ".$completion_date_new.".
                        <br>In recognition we have hereunder placed our names and
                        <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);
                    }

                // }

                

                


                // $pdfBig->SetTextColor(0,0,0);
                // $pdfBig->SetFont($times, '', 11, '', false);
                // $pdfBig->SetXY(10, 245);
                // $pdfBig->MultiCell(190, 0, 'This is a review purpose preview pdf. Please confirm the correctness of the data in this PDF, on the convocation portal.', 0, 'L', 0, 0, '', '', true, 0, true);

                // $pdfBig->SetFont($times, '', 11, '', false);
                // $pdfBig->SetXY(10, 250);
                // $pdfBig->MultiCell(190, 0, 'Do not print it.', 0, 'L', 0, 0, '', '', true, 0, true);

                //End pdfBig 
                
                //Start pdf
                

                //$profile_path_org = public_path().'\\'.$subdomain[0].'\backend\students\\'.$student_photo;   

                if(file_exists($profile_path_org)) {


                    $profilex = 165;
                    $profiley = 15;
                    $profileWidth = 25;
                    $profileHeight = 25;
                    
                    $pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
                    $pdf->setPageMark();

                }

                $pdf->SetTextColor(50,50,154);
                $pdf->SetFont($KRDEV100, '', 16.02, '', false);
                $pdf->SetXY(15, 51);
                $pdf->MultiCell(180, 0, "laLFkkid v/;{k rFkk dk;Zdkjh v/;{k] iz'kkldh; vf/kdkjh ,oa ".$faculty_name_krutidev." dh flQkfj'k dks vuqeksfnr djrs gq,]", 0, 'C', 0, 0, '', '', true, 0, true);


                $pdf->SetTextColor(255,0,0);
                $pdf->SetXY(15, 68);
                //$pdf->MultiCell(180, 0, $full_name_krutidev.' '.$mother_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);
                
                if($exceptionGeneration == 1){

                    
                    $pdf->SetFont($AkrutiDevPriya_B, '', 19.98, '', false);
                    $pdf->MultiCell(180, 0, $full_name_krutidev .'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span> <span style="color:#32329a;font-family:'.$KRDEV100.';font-size:16.02px;">ek¡ dk uke</span> '.$mother_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);
                    
                } else {
                
                    $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
                    $pdf->MultiCell(180, 0, $full_name_krutidev .'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span> <span style="color:#32329a;font-family:'.$KRDEV100.';font-size:16.02px;">ek¡ dk uke</span> '.$mother_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);
                }

                // $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
                // $pdf->MultiCell(180, 0, $full_name_krutidev .'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span> <span style="color:#32329a;font-family:'.$KRDEV100.';font-size:16.02px;">ek¡ dk uke</span> '.$mother_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);

                $pdf->SetTextColor(50,50,154);
                $pdf->SetFont($KRDEV100, '', 16.02, '', false);
                $pdf->SetXY(15, 78);
                $pdf->MultiCell(180, 0, "dks", 0, 'C', 0, 0, '', '', true, 0, true);


                // $pdf->SetTextColor(255,0,0);
                // $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
                // $pdf->SetXY(15, 86);
                // $pdf->MultiCell(180, 0, $course_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);

                // if(stripos($course_name_krutidev, 'fMIyksek') !== FALSE){


                //     if(stripos($specialization_krutidev, 'ix fMIyksek bu') !== FALSE){
                //         $specialization_krutidev = str_replace('ix fMIyksek bu','',$specialization_krutidev);
                //     }

                //     $pdf->SetTextColor(255,0,0);
                //     if ($course_name== "Doctor of Philosophy (Electronics and Communication Engineering)") {
                //         $pdf->SetFont($KRDEV100B, '', 19.5, '', false);
                //     } else {
                //         $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
                //     }

                //     // $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
                    
                //     $pdf->SetXY(15, 86);
                //     if($specialization_krutidev){
                //         $pdf->MultiCell(180, 0, $course_name_krutidev.' bu '.$specialization_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);
                //     } else {
                //         $pdf->MultiCell(180, 0, $course_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);
                //     }

                //     $pdf->Ln();

                    

                // } else {
                    $pdf->SetTextColor(255,0,0);
                    //$pdf->SetFont($KRDEV100B, '', 19.98, '', false);
                    if ($course_name== "Doctor of Philosophy (Electronics and Communication Engineering)") {
                        $pdf->SetFont($KRDEV100B, '', 19.5, '', false);
                    } else {
                        $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
                    }
                    $pdf->SetXY(15, 86);

                    $pdf->MultiCell(180, 0, $course_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);

                    $pdf->Ln();
                    
                // }


                $pdf->SetTextColor(50,50,154);
                $pdf->SetFont($KRDEV100, '', 16.02, '', false);
                $pdf->SetXY(15, $pdf->GetY()+2);

                // if(stripos($course_name_krutidev, 'fMIyksek') !== FALSE){
                    
                //     $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".$completion_date_krutidev." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd mUgksausa ".$rank_krutidev." Js.kh izkIr dh gSA", 0, 'C', 0, 0, '', '', true, 0, true);
                

                // } else {

                    if($specialization_krutidev) {
                        $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".$completion_date_krutidev." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd ".$specialization_krutidev." Lis'kykbts'ku esa mUgksausa ".$rank_krutidev." Js.kh izkIr dh gSA", 0, 'C', 0, 0, '', '', true, 0, true);
                    } else {
                        $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".$completion_date_krutidev." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd mUgksausa ".$rank_krutidev." Js.kh izkIr dh gSA", 0, 'C', 0, 0, '', '', true, 0, true);
                    }
                    
                // }

                
                // $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".$completion_date_krutidev." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd ".$specialization_krutidev." Lis'kykbts'ku esa mUgksausa ".$rank_krutidev." Js.kh izkIr dh gSA", 0, 'C', 0, 0, '', '', true, 0, true);

            

                $pdf->Ln();
                $pdf->SetTextColor(50,50,154);
                $pdf->SetFont($KRDEV100, '', 16.02, '', false);
                // $pdf->SetXY(15, 113);
                $pdf->SetXY(15, $pdf->GetY()+2);
                $pdf->MultiCell(180, 0, "bls ekU;rk nsrs gq, vkt ".$issue_date_krutidev." ds volj ij ge vius uke rFkk fo'ofo|ky; dh eksgj ds vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);


                $pdf->SetTextColor(50,50,154);
                $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
                $pdf->SetXY(10, 141);
                $pdf->MultiCell(190, 0, "The Founder President and the Executive President approbating the recommendations of the Governing Body and ".$faculty_name." hereby confer upon", 0, 'C', 0, 0, '', '', true, 0, true);


                $pdf->SetTextColor(255,0,0);
                $pdf->SetFont($timesb, '', 19.98, '', false);
                $pdf->SetXY(10, 158);
                // $pdf->MultiCell(190, 0, $full_name.' '.$mother_name , 0, 'C', 0, 0, '', '', true, 0, true);
                // $pdf->MultiCell(190, 0, trim(ucwords(strtolower($full_name))).' <span style="font-size:16;display: flex; align-items: center;">'.  trim(ucwords(strtolower($mother_name))).'</span>', 0, 'C', 0, 0, '', '', true, 0, true);
                $pdf->MultiCell(190, 0, trim(ucwords(strtolower($full_name))).'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px; ">, Mother\'s Name</span> '.trim(ucwords(strtolower($mother_name))), 0, 'C', 0, 0, '', '', true, 0, true);


                // $pdf->SetTextColor(50,50,154);
                // $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
                // $pdf->SetXY(10, 167.5);
                // $pdf->MultiCell(190, 0, "the Degree of", 0, 'C', 0, 0, '', '', true, 0, true);


                // $pdf->SetTextColor(255,0,0);
                // $pdf->SetFont($timesb, '', 19.98, '', false);
                // $pdf->SetXY(10, 174);
                // $pdf->MultiCell(190, 0, $course_name, 0, 'C', 0, 0, '', '', true, 0, true);

                // if(stripos($course_name, 'Diploma') !== FALSE){


                //     if(stripos($specialization, 'PG Diploma in') !== FALSE){
                //         $specialization = str_replace('PG Diploma in','',$specialization);
                //     }
                    

                //     $pdf->SetTextColor(50,50,154);
                //     $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
                //     $pdf->SetXY(10, 167.5);
                //     $pdf->MultiCell(190, 0, "the", 0, 'C', 0, 0, '', '', true, 0, true);

                //     $pdf->SetTextColor(255,0,0);
                //     $pdf->SetFont($timesb, '', 19.98, '', false);
                //     $pdf->SetXY(10, 174);
                //     if($specialization) {
                //         $pdf->MultiCell(190, 0, $course_name .' in '.$specialization , 0, 'C', 0, 0, '', '', true, 0, true);
                //     } else {
                //         $pdf->MultiCell(190, 0, $course_name , 0, 'C', 0, 0, '', '', true, 0, true);
                //     }

                //     $pdf->Ln();
                // } else {
                    

                    $pdf->SetTextColor(50,50,154);
                    $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
                    $pdf->SetXY(10, 167.5);
                    $pdf->MultiCell(190, 0, "the Degree of", 0, 'C', 0, 0, '', '', true, 0, true);

                    $pdf->SetTextColor(255,0,0);
                    $pdf->SetFont($timesb, '', 19.98, '', false);
                    $pdf->SetXY(10, 174);
                    $pdf->MultiCell(190, 0, $course_name, 0, 'C', 0, 0, '', '', true, 0, true);
                    
                    $pdf->Ln();
                // }



                


                
                $pdf->SetTextColor(50,50,154);
                $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
                $pdf->SetXY(10, $pdf->GetY()+2);

                // if(stripos($course_name, 'Diploma') !== FALSE){
                //     $pdf->MultiCell(190, 0, " with ".$rank." Rank secured 
                //         in the examination held in ".$completion_date_new.".
                //         <br>In recognition we have hereunder placed our names and
                //         <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);
                // } else {

                    if($specialization) {
        
                        $pdf->MultiCell(190, 0, $specilaText." with ".$rank." Rank secured 
                        <br>in the examination held in ".$completion_date_new.".
                        <br>In recognition we have hereunder placed our names and
                        <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);
        
                    } else {
        
                        $pdf->MultiCell(190, 0, " with ".$rank." Rank secured 
                        in the examination held in ".$completion_date_new.".
                        <br>In recognition we have hereunder placed our names and
                        <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);
                    }
                // }

                                    
                //End pdf			
                
                $sepratorImage = public_path().'\\'.$subdomain[0].'\backend\canvas\images\certificateseparator.png';
                // $sepratorImage = public_path().'\\'.$subdomain[0].'\backend\canvas\images\chitnis.png';
                $upload_sepratorImage_org = $sepratorImage;
                $pathInfo = pathinfo($sepratorImage);
                $sepratorImage = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$PRN.".$pathInfo['extension'];
                \File::copy($upload_sepratorImage_org,$sepratorImage);

                $seprator_x = 72;
                $seprator_y = 136;
                $seprator_Width = 66;
                $seprator_Height = 5;
                $pdfBig->Image($sepratorImage, $seprator_x,$seprator_y,$seprator_Width,$seprator_Height, "png", '', 'C', true, 3600);
                $pdf->Image($sepratorImage, $seprator_x,$seprator_y,$seprator_Width,$seprator_Height, "png", '', 'C', true, 3600);
                $pdfBig->setPageMark();
                $pdf->setPageMark();

                // Sign 1 Image
                // $sign1Image = public_path().'\\'.$subdomain[0].'\backend\canvas\images\chitnis.png';

                $sign1Image = public_path().'\\'.$subdomain[0].'\backend\canvas\images\chitnis.png';
                $upload_sign1Image_org = $sign1Image;
                $pathInfo = pathinfo($sign1Image);
                $sign1Image = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$PRN.".$pathInfo['extension'];
                \File::copy($upload_sign1Image_org,$sign1Image);


                $sign1_x = 28;
                $sign1_y = 226;
                $sign1_Width = 26;
                $sign1_Height = 16;
                $pdfBig->Image($sign1Image, $sign1_x,$sign1_y,$sign1_Width,$sign1_Height, "png", '', 'C', true, 3600);
                $pdf->Image($sign1Image, $sign1_x,$sign1_y,$sign1_Width,$sign1_Height, "png", '', 'C', true, 3600);
                $pdfBig->setPageMark();
                $pdf->setPageMark();


                $pdf->SetTextColor(0,0,0);
                $pdf->SetFont($calibrib, '', 12.48, '', false);
                $pdf->SetXY(28, 240);
                $pdf->MultiCell(50, 0, 'Dr. R. M. Chitnis', 0, 'L', 0, 0, '', '', true, 0, true);
                
                $pdf->SetFont($calibri, '', 10.98, '', false);
                $pdf->SetXY(30, 246);
                $pdf->MultiCell(50, 0, 'Vice Chancellor', 0, 'L', 0, 0, '', '', true, 0, true);
                    
                    
                $pdfBig->SetTextColor(0,0,0);
                $pdfBig->SetFont($calibrib, '', 12.48, '', false);
                $pdfBig->SetXY(28, 240);
                $pdfBig->MultiCell(50, 0, 'Dr. R. M. Chitnis', 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetFont($calibri, '', 10.98, '', false);
                $pdfBig->SetXY(30, 246);
                $pdfBig->MultiCell(50, 0, 'Vice Chancellor', 0, 'L', 0, 0, '', '', true, 0, true);



                // Sign 2 Image
                $sign2Image = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Vishwanath_Karad.png';
                $upload_sign2Image_org = $sign2Image;
                $pathInfo = pathinfo($sign2Image);
                $sign2Image = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$PRN.".$pathInfo['extension'];
                \File::copy($upload_sign2Image_org,$sign2Image);

                $sign2_x = 91;
                $sign2_y = 221;
                $sign2_Width = 30;
                $sign2_Height = 12;
                $pdfBig->Image($sign2Image, $sign2_x,$sign2_y,$sign2_Width,$sign2_Height, "png", '', 'C', true, 3600);
                $pdf->Image($sign2Image, $sign2_x,$sign2_y,$sign2_Width,$sign2_Height, "png", '', 'C', true, 3600);
                $pdfBig->setPageMark();
                $pdf->setPageMark();


                $pdf->SetTextColor(0,0,0);
                $pdf->SetFont($calibrib, '', 12.48, '', false);
                $pdf->SetXY(82, 233.5);
                $pdf->MultiCell(70, 0, 'Prof. Dr. Vishwanath D. Karad', 0, 'L', 0, 0, '', '', true, 0, true);
                    
                    
                $pdf->SetFont($calibri, '', 10.98, '', false);
                $pdf->SetXY(92, 239);
                $pdf->MultiCell(70, 0, 'Founder President', 0, 'L', 0, 0, '', '', true, 0, true);
                    
                    
                $pdfBig->SetTextColor(0,0,0);
                $pdfBig->SetFont($calibrib, '', 12.48, '', false);
                $pdfBig->SetXY(82, 233.5);
                $pdfBig->MultiCell(70, 0, 'Prof. Dr. Vishwanath D. Karad', 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetFont($calibri, '', 10.98, '', false);
                $pdfBig->SetXY(92, 239);
                $pdfBig->MultiCell(70, 0, 'Founder President', 0, 'L', 0, 0, '', '', true, 0, true);


                // Sign 3 Image
                $sign3Image = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Rahul_Karad.png';

                $upload_sign3Image_org = $sign3Image;
                $pathInfo = pathinfo($sign3Image);
                $sign3Image = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$PRN.".$pathInfo['extension'];
                \File::copy($upload_sign3Image_org,$sign3Image);
                
                $sign3_x = 166;
                $sign3_y = 226;
                $sign3_Width = 17;
                $sign3_Height = 15;
                $pdfBig->Image($sign3Image, $sign3_x,$sign3_y,$sign3_Width,$sign3_Height, "png", '', 'C', true, 3600);
                $pdf->Image($sign3Image, $sign3_x,$sign3_y,$sign3_Width,$sign3_Height, "png", '', 'C', true, 3600);
                $pdfBig->setPageMark();
                $pdf->setPageMark();


                if($year =='2024') {
                    $executivePresidentName = 'Rahul V. Karad';
                    $setX = 161;
                } else {
                    $executivePresidentName = 'Dr. Rahul V. Karad';
                    $setX = 159;
                }


                $pdf->SetTextColor(0,0,0);
                $pdf->SetFont($calibrib, '', 12.48, '', false);
                $pdf->SetXY($setX, 240);
                $pdf->MultiCell(70, 0, $executivePresidentName, 0, 'L', 0, 0, '', '', true, 0, true);
                
                $pdf->SetFont($calibri, '', 10.98, '', false);
                $pdf->SetXY(160, 246);
                $pdf->MultiCell(70, 0, 'Executive President', 0, 'L', 0, 0, '', '', true, 0, true);
                    
                    
                $pdfBig->SetTextColor(0,0,0);
                $pdfBig->SetFont($calibrib, '', 12.48, '', false);
                $pdfBig->SetXY($setX, 240);
                $pdfBig->MultiCell(70, 0, $executivePresidentName, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetFont($calibri, '', 10.98, '', false);
                $pdfBig->SetXY(160, 246);
                $pdfBig->MultiCell(70, 0, 'Executive President', 0, 'L', 0, 0, '', '', true, 0, true);


                // $pdfBig->SetTextColor(50,50,154);
                // $pdfBig->SetFont($calibrib, '', 10.98, '', false);
                // $pdfBig->SetXY(95, 261);
                // $pdfBig->MultiCell(100, 0, 'CO/O24/'.$PRN, 0, 'R', 0, 0, '', '', true, 0, true);

                // $pdf->SetTextColor(50,50,154);
                // $pdf->SetFont($calibrib, '', 10.98, '', false);
                // $pdf->SetXY(95, 261);
                // $pdf->MultiCell(100, 0, 'CO/O24/'.$PRN, 0, 'R', 0, 0, '', '', true, 0, true);
                
                $last_prn = str_replace('RANK_'.$year.'_', '', $PRN);
                $pdfBig->SetTextColor(50,50,154);
                $pdfBig->SetFont($calibrib, '', 10.98, '', false);
                $pdfBig->SetXY(147, 259);
                $pdfBig->MultiCell(60, 0, 'PRN No. - '.$last_prn, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetTextColor(50,50,154);
                $pdfBig->SetFont($calibrib, '', 10.98, '', false);
                $pdfBig->SetXY(147, 264);
                $pdfBig->MultiCell(60, 0, 'Sr.No. - '.$serialNo, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetTextColor(50,50,154);
                $pdf->SetFont($calibrib, '', 10.98, '', false);
                $pdf->SetXY(147, 259);
                $pdf->MultiCell(60, 0, 'PRN No. - '.$last_prn, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetTextColor(50,50,154);
                $pdf->SetFont($calibrib, '', 10.98, '', false);
                $pdf->SetXY(147, 264);
                $pdf->MultiCell(60, 0, 'Sr.No. - '.$serialNo, 0, 'L', 0, 0, '', '', true, 0, true);

                // Ghost image
                $nameOrg=$candidate_name;
                
                /*$ghost_font_size = '13';
                $ghostImagex = 132;
                $ghostImagey = 267;
                $ghostImageWidth = 55;
                $ghostImageHeight = 9.8;*/	
                
                $ghost_font_size = '12';
                $ghostImagex = 144;
                $ghostImagey = 268;
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

                // $encryptedKey = strtoupper(md5($studentID.$date));

                $encryptedString = strtoupper(md5($str));
                // $codeContents =$encryptedString = strtoupper(md5($str));

                $codeContents = $full_name.','.$faculty_name.','.$course_name.','.$rank.','.$completion_date_new.','.$PRN;
                
                // if($subdomain[0] =='demo') {
                    $codeContents .="\n\n";
                    $codeData =CoreHelper::getBCVerificationUrl(base64_encode(strtoupper(md5($str))));
                    $codeContents .=$codeData;
                // }

                $codeContents .="\n\n".strtoupper(md5($str));


                $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
                $qrCodex = 31; 
                $qrCodey = 252;
                $qrCodeWidth =22;
                $qrCodeHeight = 22;
                $ecc = 'L';
                $pixel_Size = 1;
                $frame_Size = 1;  
                // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
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
                
                $barcodex = 12;
                $barcodey = 267;
                $barcodeWidth = 56;
                $barodeHeight = 13;
                // $pdf->SetAlpha(1);
                // $pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
                // $pdfBig->SetAlpha(1);
                // $pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
                            
                $str = $nameOrg;
                $str = strtoupper(preg_replace('/\s+/', '', $str)); 
                
                $microlinestr=$str;
                $pdf->SetFont($arialb, '', 2, '', false);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetXY(177, 39);        
                $pdf->Cell(21, 0, $microlinestr, 0, false, 'C');     
                
                $pdfBig->SetFont($arialb, '', 2, '', false);
                $pdfBig->SetTextColor(0, 0, 0);
                $pdfBig->SetXY(177, 39);      
                $pdfBig->Cell(21, 0, $microlinestr, 0, false, 'C'); 

                if($previewPdf!=1){

                    $certName = str_replace("/", "_", $GUID) .".pdf";
                    
                    $myPath = public_path().'/backend/temp_pdf_file';

                    $fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;

                    $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');




                    
                    $mintData=array();
                    $mintData['documentType']="Educational Document";
                    $mintData['description']="Student ID :".$PRN;
                    $mintData['metadata1']=["label"=> "Student Name", "value"=> $full_name];
                    $mintData['metadata2']=["label"=> "Competency Level", "value"=> $faculty_name];
                    $mintData['metadata3']=["label"=> "Specialization", "value"=> $course_name];
                    $mintData['metadata4']=["label"=> "Rank", "value"=> $rank];
                    $mintData['metadata5']=["label"=> "Completion date", "value"=> $completion_date];

                    $mintData['uniqueHash']=$encryptedString;

                    
                    $this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id,$mintData);

                    
                    $username = $admin_id['username'];
                    date_default_timezone_set('Asia/Kolkata');

                    $content = "#".$log_serial_no." serial No :".$serial_no.PHP_EOL;
                    $date = date('Y-m-d H:i:s').PHP_EOL;
                    $print_datetime = date("Y-m-d H:i:s");
                    

                    $print_count = $this->getPrintCount($serial_no);
                    $printer_name = /*'HP 1020';*/$printer_name;

                    $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'Memorandum',$admin_id,$card_serial_no);

                    // $card_serial_no=$card_serial_no+1;
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
                // dd($studentDataOrg);
                // \Log::info($studentDataOrg);
                // Update code for batchwise genration
                return "Will be generated soon!";	

            } 
        }
        
        //if($previewPdf!=1){
            // $this->updateCardNo('MitwpuRank',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
        //}
        $msg = '';
        
        $file_name =  str_replace("/", "_",'MitwpuRank'.date("Ymdhms")).'.pdf';
        
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        $filename = public_path().'/backend/tcpdf/examples/'.$file_name;
        
        $pdfBig->output($filename,'F');

        if($previewPdf!=1){

           


            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            // $no_of_records = count($studentDataOrg);
            $no_of_records = $pdf_data['highestrow'];
            $user = $admin_id['username'];
            $template_name="MitwpuRank";
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

    public function addCertificate($serial_no, $certName, $dt,$template_id,$admin_id,$mintData)
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
        

         // Blockchain
        

        
        $template_id=6;
        $template_data = TemplateMaster::select('id','template_name','is_block_chain','bc_document_description','bc_document_type','bc_contract_address')->where('id',$template_id)->first();

        $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;
        
        $mintData['pdf_file']=$pdf_path;
        $mintData['template_id']=$template_id;
        $mintData['bc_contract_address']=$template_data['bc_contract_address'];
        // $response=CoreHelper::mintPDF($mintData);
        

        $template_type = 2;
        $blockchain_type = 1;

        // $response = [];
        // $response['status']=500;
        $response=CoreHelper::customMintPDF($mintData,$blockchain_type,$template_type);
        $bc_file_hash=CoreHelper::generateFileHash($pdf_path);
        
        


        // print_r($response);

        // if($response['status']==200){
        //     $bc_txn_hash=$response['txnHash'];
        //     if(isset($response['ipfsHash'])){
        //         $bc_ipfs_hash=$response['ipfsHash'];
        //         $pinata_ipfs_hash=$response['pinataIpfsHash'];
        //     }else{
        //         $bc_ipfs_hash=null;
        //         $pinata_ipfs_hash=null;
        //     }
        // } else {
        //     $bc_txn_hash = null;
        //     $bc_ipfs_hash=null;
        //     $pinata_ipfs_hash=null;
        // }

        if($response['status']==200){
            $bc_txn_hash=$response['txnHash'];
            $bc_sc_id=$response['bc_sc_id'];
            $metadata_ipfs_hash = $response['metadata_ipfs_hash'];
            if(isset($response['ipfsHash'])){
                $bc_ipfs_hash=$response['ipfsHash'];
                $pinata_ipfs_hash=$response['pinataIpfsHash'];
            }else{
                $bc_ipfs_hash=null;
                $pinata_ipfs_hash=null;
                // $bc_sc_id=null;
            }
        } else {
            $bc_txn_hash = null;
            $bc_ipfs_hash=null;
            $pinata_ipfs_hash=null;
            $bc_sc_id=null;
            $metadata_ipfs_hash = null;
        }

        // blockchain


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
            
            $result = SbStudentTable::create(['serial_no'=>'T-'.$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'certificate_type'=>'Rank Degree Certificate' ,'bc_txn_hash'=>$bc_txn_hash,'bc_ipfs_hash'=>$bc_ipfs_hash,'pinata_ipfs_hash'=>$pinata_ipfs_hash ,'bc_sc_id' => $bc_sc_id, 'bc_file_hash'=>$bc_file_hash]);
        }else{
            

            $resultu = StudentTable::where('serial_no',''.$serial_no)->update(['status'=>'0']);
            // Insert the new record
            
            $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2,'certificate_type'=>'Rank Degree Certificate' ,'bc_txn_hash'=>$bc_txn_hash,'bc_ipfs_hash'=>$bc_ipfs_hash,'pinata_ipfs_hash'=>$pinata_ipfs_hash ,'bc_sc_id' => $bc_sc_id, 'bc_file_hash'=>$bc_file_hash]);

            // vendor identifier
            $studentData = StudentTable::where('serial_no', $serial_no)->where('status', 1)->first();
            
            // $result = DB::table('blockchain_other_data')->updateOrInsert(
            //     ['student_table_id' => $studentData['id']],
            //     ['bc_md_ipfs_hash' => $metadata_ipfs_hash],
            //     ['vendor_identifier' => $blockchain_type]

            // );
            $result = DB::table('blockchain_other_data')->updateOrInsert(
                ['student_table_id' => $studentData['id']], // search condition
                [                                           // values to update/insert
                    'bc_md_ipfs_hash'   => $metadata_ipfs_hash,
                    'vendor_identifier' => $blockchain_type
                ]
            );


            $student_data  = StudentTable::where('serial_no',''.$serial_no)->where('status',1)->where('key',''.$key)->first();
    
            if($bc_sc_id && !empty($student_data)){
                CoreHelper::updateContractCount($bc_sc_id,$student_data['id']);
            }
            
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
