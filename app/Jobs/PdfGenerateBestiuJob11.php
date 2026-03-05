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
class PdfGenerateBestiuJob11
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
		
        $first_sheet=$pdf_data['studentDataOrg']; // get first worksheet rows
        // dd($first_sheet);
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
		}else{ 
			 if(Session::get('pdf_data_obj') != null){
			$pdfBig = Session::get('pdf_data_obj');   
			}
		}
        // $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        // $pdfBig->SetCreator(PDF_CREATOR);
        // $pdfBig->SetAuthor('TCPDF');
        // $pdfBig->SetTitle('Transcript');
        // $pdfBig->SetSubject('');

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);
        $pdfBig->setCellPaddings( $left = '', $top = '', $right = '', $bottom = '');

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
        $oef = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\old-english-five.ttf', 'TrueTypeUnicode', '', 96);

        $preview_serial_no=1;
		//$card_serial_no="";
        $log_serial_no = 1;
        $cardDetails=$this->getNextCardNo('BestiuT');
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
				$high_res_bg="bestiu_pdc_bg.jpg"; // bestiu_pdc_bg, TranscriptData.jpg
				$low_res_bg="bestiu_pdc_bg.jpg";
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
					$pdfBig->SetFont($arialb, '', $date_font_size, '', false);
					$pdfBig->SetTextColor(192,192,192);
					$pdfBig->SetXY($date_nox, $date_noy);
					$pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
					$pdfBig->SetTextColor(0,0,0,100,false,'');
					$pdfBig->SetFont($arialNarrowB, '', 9, '', false);
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
			//  echo"<pre>";print_r($studentData);    die();                			
				$unique_id=trim($studentData[0]);
				$REG_No=trim($studentData[1]);
				$AADHAR_NO=trim($studentData[2]);
				$STUDENT_NAME=trim($studentData[3]);
				$PROGRAMME=trim($studentData[4]);
				$College=trim($studentData[5]);
				$Department=trim($studentData[6]);
				$Specialization=trim($studentData[7]);
				$FATHERS_NAME=trim($studentData[8]);
				$Year_of_admission=trim($studentData[9]);
				$Date_of_successful_completion=trim($studentData[10]);
				$Class_Awarded=trim($studentData[11]);
				// $SEM=trim($studentData[12]);
				// $MOTHER_NAME=trim($studentData[13]);
				// $EXAMINATION=trim($studentData[14]);
				// $PROGRAMME=trim($studentData[15]);
				// $REGULATION=trim($studentData[16]);
				// $Date_of_Declaration_of_Result=trim($studentData[17]);

				$I_SEM=trim($studentData[12]);
				$SEM1_COURSE_CODE_1=trim($studentData[13]);
				$SEM1_COURSE_TITLE_1=trim($studentData[14]);
				$SEM1_CREDIT_HOURS_1=trim($studentData[15]);
				$SEM1_GRADE_POINT_1=trim($studentData[16]);
				$SEM1_CREDIT_POINTS_1=trim($studentData[17]);

				$SEM1_COURSE_CODE_2=trim($studentData[18]);
				$SEM1_COURSE_TITLE_2=trim($studentData[19]);
				$SEM1_CREDIT_HOURS_2=trim($studentData[20]);
				$SEM1_GRADE_POINT_2=trim($studentData[21]);
				$SEM1_CREDIT_POINTS_2=trim($studentData[22]);

				$SEM1_COURSE_CODE_3=trim($studentData[23]);
				$SEM1_COURSE_TITLE_3=trim($studentData[24]);
				$SEM1_CREDIT_HOURS_3=trim($studentData[25]);
				$SEM1_GRADE_POINT_3=trim($studentData[26]);
				$SEM1_CREDIT_POINTS_3=trim($studentData[27]);

				$SEM1_COURSE_CODE_4=trim($studentData[28]);
				$SEM1_COURSE_TITLE_4=trim($studentData[29]);
				$SEM1_CREDIT_HOURS_4=trim($studentData[30]);
				$SEM1_GRADE_POINT_4=trim($studentData[31]);
				$SEM1_CREDIT_POINTS_4=trim($studentData[32]);

				$SEM1_COURSE_CODE_5=trim($studentData[33]);
				$SEM1_COURSE_TITLE_5=trim($studentData[34]);
				$SEM1_CREDIT_HOURS_5=trim($studentData[35]);
				$SEM1_GRADE_POINT_5=trim($studentData[36]);
				$SEM1_CREDIT_POINTS_5=trim($studentData[37]);

				$SEM1_COURSE_CODE_6=trim($studentData[38]);
				$SEM1_COURSE_TITLE_6=trim($studentData[39]);
				$SEM1_CREDIT_HOURS_6=trim($studentData[40]);
				$SEM1_GRADE_POINT_6=trim($studentData[41]);
				$SEM1_CREDIT_POINTS_6=trim($studentData[42]);

				$SEM1_COURSE_CODE_7=trim($studentData[43]);
				$SEM1_COURSE_TITLE_7=trim($studentData[44]);
				$SEM1_CREDIT_HOURS_7=trim($studentData[45]);
				$SEM1_GRADE_POINT_7=trim($studentData[46]);
				$SEM1_CREDIT_POINTS_7=trim($studentData[47]);

				$SEM1_COURSE_CODE_8=trim($studentData[48]);
				$SEM1_COURSE_TITLE_8=trim($studentData[49]);
				$SEM1_CREDIT_HOURS_8=trim($studentData[50]);
				$SEM1_GRADE_POINT_8=trim($studentData[51]);
				$SEM1_CREDIT_POINTS_8=trim($studentData[52]);
				$SEM1_SGPA=trim($studentData[53]);

				
				$II_SEM=trim($studentData[54]);
				$SEM2_COURSE_CODE_1=trim($studentData[55]);
				$SEM2_COURSE_TITLE_1=trim($studentData[56]);
				$SEM2_CREDIT_HOURS_1=trim($studentData[57]);
				$SEM2_GRADE_POINT_1=trim($studentData[58]);
				$SEM2_CREDIT_POINTS_1=trim($studentData[59]);

				$SEM2_COURSE_CODE_2=trim($studentData[60]);
				$SEM2_COURSE_TITLE_2=trim($studentData[61]);
				$SEM2_CREDIT_HOURS_2=trim($studentData[62]);
				$SEM2_GRADE_POINT_2=trim($studentData[63]);
				$SEM2_CREDIT_POINTS_2=trim($studentData[64]);

				$SEM2_COURSE_CODE_3=trim($studentData[65]);
				$SEM2_COURSE_TITLE_3=trim($studentData[66]);
				$SEM2_CREDIT_HOURS_3=trim($studentData[67]);
				$SEM2_GRADE_POINT_3=trim($studentData[68]);
				$SEM2_CREDIT_POINTS_3=trim($studentData[69]);

				$SEM2_COURSE_CODE_4=trim($studentData[70]);
				$SEM2_COURSE_TITLE_4=trim($studentData[71]);
				$SEM2_CREDIT_HOURS_4=trim($studentData[72]);
				$SEM2_GRADE_POINT_4=trim($studentData[73]);
				$SEM2_CREDIT_POINTS_4=trim($studentData[74]);

				$SEM2_COURSE_CODE_5=trim($studentData[75]);
				$SEM2_COURSE_TITLE_5=trim($studentData[76]);
				$SEM2_CREDIT_HOURS_5=trim($studentData[77]);
				$SEM2_GRADE_POINT_5=trim($studentData[78]);
				$SEM2_CREDIT_POINTS_5=trim($studentData[79]);

				$SEM2_COURSE_CODE_6=trim($studentData[80]);
				$SEM2_COURSE_TITLE_6=trim($studentData[81]);
				$SEM2_CREDIT_HOURS_6=trim($studentData[82]);
				$SEM2_GRADE_POINT_6=trim($studentData[83]);
				$SEM2_CREDIT_POINTS_6=trim($studentData[84]);

				$SEM2_COURSE_CODE_7=trim($studentData[85]);
				$SEM2_COURSE_TITLE_7=trim($studentData[86]);
				$SEM2_CREDIT_HOURS_7=trim($studentData[87]);
				$SEM2_GRADE_POINT_7=trim($studentData[88]);
				$SEM2_CREDIT_POINTS_7=trim($studentData[89]);

				$SEM2_COURSE_CODE_8=trim($studentData[90]);
				$SEM2_COURSE_TITLE_8=trim($studentData[91]);
				$SEM2_CREDIT_HOURS_8=trim($studentData[92]);
				$SEM2_GRADE_POINT_8=trim($studentData[93]);
				$SEM2_CREDIT_POINTS_8=trim($studentData[94]);

				$SEM2_COURSE_CODE_9=trim($studentData[95]);
				$SEM2_COURSE_TITLE_9=trim($studentData[96]);
				$SEM2_CREDIT_HOURS_9=trim($studentData[97]);
				$SEM2_GRADE_POINT_9=trim($studentData[98]);
				$SEM2_CREDIT_POINTS_9=trim($studentData[99]);

				$SEM2_COURSE_CODE_10=trim($studentData[100]);
				$SEM2_COURSE_TITLE_10=trim($studentData[101]);
				$SEM2_CREDIT_HOURS_10=trim($studentData[102]);
				$SEM2_GRADE_POINT_10=trim($studentData[103]);
				$SEM2_CREDIT_POINTS_10=trim($studentData[104]);	

				$SEM2_COURSE_CODE_11=trim($studentData[105]);
				$SEM2_COURSE_TITLE_11=trim($studentData[106]);
				$SEM2_CREDIT_HOURS_11=trim($studentData[107]);
				$SEM2_GRADE_POINT_11=trim($studentData[108]);
				$SEM2_CREDIT_POINTS_11=trim($studentData[109]);	
				$SEM2_SGPA=trim($studentData[110]);	

				$III_SEM=trim($studentData[111]);
				$SEM3_COURSE_CODE_1=trim($studentData[112]);
				$SEM3_COURSE_TITLE_1=trim($studentData[113]);
				$SEM3_CREDIT_HOURS_1=trim($studentData[114]);
				$SEM3_GRADE_POINT_1=trim($studentData[115]);
				$SEM3_CREDIT_POINTS_1=trim($studentData[116]);

				$SEM3_COURSE_CODE_2=trim($studentData[117]);
				$SEM3_COURSE_TITLE_2=trim($studentData[118]);
				$SEM3_CREDIT_HOURS_2=trim($studentData[119]);
				$SEM3_GRADE_POINT_2=trim($studentData[120]);
				$SEM3_CREDIT_POINTS_2=trim($studentData[121]);

				$SEM3_COURSE_CODE_3=trim($studentData[122]);
				$SEM3_COURSE_TITLE_3=trim($studentData[123]);
				$SEM3_CREDIT_HOURS_3=trim($studentData[124]);
				$SEM3_GRADE_POINT_3=trim($studentData[125]);
				$SEM3_CREDIT_POINTS_3=trim($studentData[126]);

				$SEM3_COURSE_CODE_4=trim($studentData[127]);
				$SEM3_COURSE_TITLE_4=trim($studentData[128]);
				$SEM3_CREDIT_HOURS_4=trim($studentData[129]);
				$SEM3_GRADE_POINT_4=trim($studentData[130]);
				$SEM3_CREDIT_POINTS_4=trim($studentData[131]);

				$SEM3_COURSE_CODE_5=trim($studentData[132]);
				$SEM3_COURSE_TITLE_5=trim($studentData[133]);
				$SEM3_CREDIT_HOURS_5=trim($studentData[134]);
				$SEM3_GRADE_POINT_5=trim($studentData[135]);
				$SEM3_CREDIT_POINTS_5=trim($studentData[136]);

				$SEM3_COURSE_CODE_6=trim($studentData[137]);
				$SEM3_COURSE_TITLE_6=trim($studentData[138]);
				$SEM3_CREDIT_HOURS_6=trim($studentData[139]);
				$SEM3_GRADE_POINT_6=trim($studentData[140]);
				$SEM3_CREDIT_POINTS_6=trim($studentData[141]);

				$SEM3_COURSE_CODE_7=trim($studentData[142]);
				$SEM3_COURSE_TITLE_7=trim($studentData[143]);
				$SEM3_CREDIT_HOURS_7=trim($studentData[144]);
				$SEM3_GRADE_POINT_7=trim($studentData[145]);
				$SEM3_CREDIT_POINTS_7=trim($studentData[146]);

				$SEM3_COURSE_CODE_8=trim($studentData[147]);
				$SEM3_COURSE_TITLE_8=trim($studentData[148]);
				$SEM3_CREDIT_HOURS_8=trim($studentData[149]);
				$SEM3_GRADE_POINT_8=trim($studentData[150]);
				$SEM3_CREDIT_POINTS_8=trim($studentData[151]);
				$SEM3_SGPA=trim($studentData[152]);

				$IV_SEM=trim($studentData[153]);
				$SEM4_COURSE_CODE_1=trim($studentData[154]);
				$SEM4_COURSE_TITLE_1=trim($studentData[155]);
				$SEM4_CREDIT_HOURS_1=trim($studentData[156]);
				$SEM4_GRADE_POINT_1=trim($studentData[157]);
				$SEM4_CREDIT_POINTS_1=trim($studentData[158]);

				$SEM4_COURSE_CODE_2=trim($studentData[159]);
				$SEM4_COURSE_TITLE_2=trim($studentData[160]);
				$SEM4_CREDIT_HOURS_2=trim($studentData[161]);
				$SEM4_GRADE_POINT_2=trim($studentData[162]);
				$SEM4_CREDIT_POINTS_2=trim($studentData[163]);

				$SEM4_COURSE_CODE_3=trim($studentData[164]);
				$SEM4_COURSE_TITLE_3=trim($studentData[165]);
				$SEM4_CREDIT_HOURS_3=trim($studentData[166]);
				$SEM4_GRADE_POINT_3=trim($studentData[167]);
				$SEM4_CREDIT_POINTS_3=trim($studentData[168]);

				$SEM4_COURSE_CODE_4=trim($studentData[169]);
				$SEM4_COURSE_TITLE_4=trim($studentData[170]);
				$SEM4_CREDIT_HOURS_4=trim($studentData[171]);
				$SEM4_GRADE_POINT_4=trim($studentData[172]);
				$SEM4_CREDIT_POINTS_4=trim($studentData[173]);

				$SEM4_COURSE_CODE_5=trim($studentData[174]);
				$SEM4_COURSE_TITLE_5=trim($studentData[175]);
				$SEM4_CREDIT_HOURS_5=trim($studentData[176]);
				$SEM4_GRADE_POINT_5=trim($studentData[177]);
				$SEM4_CREDIT_POINTS_5=trim($studentData[178]);

				$SEM4_COURSE_CODE_6=trim($studentData[179]);
				$SEM4_COURSE_TITLE_6=trim($studentData[180]);
				$SEM4_CREDIT_HOURS_6=trim($studentData[181]);
				$SEM4_GRADE_POINT_6=trim($studentData[182]);
				$SEM4_CREDIT_POINTS_6=trim($studentData[183]);

				$SEM4_COURSE_CODE_7=trim($studentData[184]);
				$SEM4_COURSE_TITLE_7=trim($studentData[185]);
				$SEM4_CREDIT_HOURS_7=trim($studentData[186]);
				$SEM4_GRADE_POINT_7=trim($studentData[187]);
				$SEM4_CREDIT_POINTS_7=trim($studentData[188]);

				$SEM4_COURSE_CODE_8=trim($studentData[189]);
				$SEM4_COURSE_TITLE_8=trim($studentData[190]);
				$SEM4_CREDIT_HOURS_8=trim($studentData[191]);
				$SEM4_GRADE_POINT_8=trim($studentData[192]);
				$SEM4_CREDIT_POINTS_8=trim($studentData[193]);
				$SEM4_SGPA=trim($studentData[194]);

				$V_SEM=trim($studentData[195]);
				$SEM5_COURSE_CODE_1=trim($studentData[196]);
				$SEM5_COURSE_TITLE_1=trim($studentData[197]);
				$SEM5_CREDIT_HOURS_1=trim($studentData[198]);
				$SEM5_GRADE_POINT_1=trim($studentData[199]);
				$SEM5_CREDIT_POINTS_1=trim($studentData[200]);

				$SEM5_COURSE_CODE_2=trim($studentData[201]);
				$SEM5_COURSE_TITLE_2=trim($studentData[202]);
				$SEM5_CREDIT_HOURS_2=trim($studentData[203]);
				$SEM5_GRADE_POINT_2=trim($studentData[204]);
				$SEM5_CREDIT_POINTS_2=trim($studentData[205]);

				$SEM5_COURSE_CODE_3=trim($studentData[206]);
				$SEM5_COURSE_TITLE_3=trim($studentData[207]);
				$SEM5_CREDIT_HOURS_3=trim($studentData[208]);
				$SEM5_GRADE_POINT_3=trim($studentData[209]);
				$SEM5_CREDIT_POINTS_3=trim($studentData[210]);

				$SEM5_COURSE_CODE_4=trim($studentData[211]);
				$SEM5_COURSE_TITLE_4=trim($studentData[212]);
				$SEM5_CREDIT_HOURS_4=trim($studentData[213]);
				$SEM5_GRADE_POINT_4=trim($studentData[214]);
				$SEM5_CREDIT_POINTS_4=trim($studentData[215]);

				$SEM5_COURSE_CODE_5=trim($studentData[216]);
				$SEM5_COURSE_TITLE_5=trim($studentData[217]);
				$SEM5_CREDIT_HOURS_5=trim($studentData[218]);
				$SEM5_GRADE_POINT_5=trim($studentData[219]);
				$SEM5_CREDIT_POINTS_5=trim($studentData[220]);

				$SEM5_COURSE_CODE_6=trim($studentData[221]);
				$SEM5_COURSE_TITLE_6=trim($studentData[222]);
				$SEM5_CREDIT_HOURS_6=trim($studentData[223]);
				$SEM5_GRADE_POINT_6=trim($studentData[224]);
				$SEM5_CREDIT_POINTS_6=trim($studentData[225]);

				$SEM5_COURSE_CODE_7=trim($studentData[226]);
				$SEM5_COURSE_TITLE_7=trim($studentData[227]);
				$SEM5_CREDIT_HOURS_7=trim($studentData[228]);
				$SEM5_GRADE_POINT_7=trim($studentData[229]);
				$SEM5_CREDIT_POINTS_7=trim($studentData[230]);

				$SEM5_COURSE_CODE_8=trim($studentData[231]);
				$SEM5_COURSE_TITLE_8=trim($studentData[232]);
				$SEM5_CREDIT_HOURS_8=trim($studentData[233]);
				$SEM5_GRADE_POINT_8=trim($studentData[234]);
				$SEM5_CREDIT_POINTS_8=trim($studentData[235]);
				$SEM5_SGPA=trim($studentData[236]);


				$VI_SEM=trim($studentData[237]);
				$SEM6_COURSE_CODE_1=trim($studentData[238]);
				$SEM6_COURSE_TITLE_1=trim($studentData[239]);
				$SEM6_CREDIT_HOURS_1=trim($studentData[240]);
				$SEM6_GRADE_POINT_1=trim($studentData[241]);
				$SEM6_CREDIT_POINTS_1=trim($studentData[242]);
				$SEM6_COURSE_CODE_2=trim($studentData[243]);
				$SEM6_COURSE_TITLE_2=trim($studentData[244]);
				$SEM6_CREDIT_HOURS_2=trim($studentData[245]);
				$SEM6_GRADE_POINT_2=trim($studentData[246]);
				$SEM6_CREDIT_POINTS_2=trim($studentData[247]);
				$SEM6_COURSE_CODE_3=trim($studentData[248]);
				$SEM6_COURSE_TITLE_3=trim($studentData[249]);
				$SEM6_CREDIT_HOURS_3=trim($studentData[250]);
				$SEM6_GRADE_POINT_3=trim($studentData[251]);
				$SEM6_CREDIT_POINTS_3=trim($studentData[252]);
				$SEM6_COURSE_CODE_4=trim($studentData[253]);
				$SEM6_COURSE_TITLE_4=trim($studentData[254]);
				$SEM6_CREDIT_HOURS_4=trim($studentData[255]);
				$SEM6_GRADE_POINT_4=trim($studentData[256]);
				$SEM6_CREDIT_POINTS_4=trim($studentData[257]);
				$SEM6_COURSE_CODE_5=trim($studentData[258]);
				$SEM6_COURSE_TITLE_5=trim($studentData[259]);
				$SEM6_CREDIT_HOURS_5=trim($studentData[260]);
				$SEM6_GRADE_POINT_5=trim($studentData[251]);
				$SEM6_CREDIT_POINTS_5=trim($studentData[262]);
				$SEM6_COURSE_CODE_6=trim($studentData[263]);
				$SEM6_COURSE_TITLE_6=trim($studentData[264]);
				$SEM6_CREDIT_HOURS_6=trim($studentData[265]);
				$SEM6_GRADE_POINT_6=trim($studentData[266]);
				$SEM6_CREDIT_POINTS_6=trim($studentData[267]);
				$SEM6_COURSE_CODE_7=trim($studentData[268]);
				$SEM6_COURSE_TITLE_7=trim($studentData[269]);
				$SEM6_CREDIT_HOURS_7=trim($studentData[270]);
				$SEM6_GRADE_POINT_7=trim($studentData[271]);
				$SEM6_CREDIT_POINTS_7=trim($studentData[272]);
				$SEM6_COURSE_CODE_8=trim($studentData[273]);
				$SEM6_COURSE_TITLE_8=trim($studentData[274]);
				$SEM6_CREDIT_HOURS_8=trim($studentData[275]);
				$SEM6_GRADE_POINT_8=trim($studentData[276]);
				$SEM6_CREDIT_POINTS_8=trim($studentData[277]);
				$SEM6_SGPA=trim($studentData[278]);

				$VII_SEM=trim($studentData[279]);
				$SEM7_COURSE_CODE_1=trim($studentData[280]);
				$SEM7_COURSE_TITLE_1=trim($studentData[281]);
				$SEM7_CREDIT_HOURS_1=trim($studentData[282]);
				$SEM7_GRADE_POINT_1=trim($studentData[283]);
				$SEM7_CREDIT_POINTS_1=trim($studentData[284]);
				$SEM7_COURSE_CODE_2=trim($studentData[285]);
				$SEM7_COURSE_TITLE_2=trim($studentData[286]);
				$SEM7_CREDIT_HOURS_2=trim($studentData[287]);
				$SEM7_GRADE_POINT_2=trim($studentData[288]);
				$SEM7_CREDIT_POINTS_2=trim($studentData[289]);
				$SEM7_COURSE_CODE_3=trim($studentData[290]);
				$SEM7_COURSE_TITLE_3=trim($studentData[291]);
				$SEM7_CREDIT_HOURS_3=trim($studentData[292]);
				$SEM7_GRADE_POINT_3=trim($studentData[293]);
				$SEM7_CREDIT_POINTS_3=trim($studentData[294]);
				$SEM7_COURSE_CODE_4=trim($studentData[295]);
				$SEM7_COURSE_TITLE_4=trim($studentData[296]);
				$SEM7_CREDIT_HOURS_4=trim($studentData[297]);
				$SEM7_GRADE_POINT_4=trim($studentData[298]);
				$SEM7_CREDIT_POINTS_4=trim($studentData[299]);
				$SEM7_COURSE_CODE_5=trim($studentData[300]);
				$SEM7_COURSE_TITLE_5=trim($studentData[301]);
				$SEM7_CREDIT_HOURS_5=trim($studentData[302]);
				$SEM7_GRADE_POINT_5=trim($studentData[303]);
				$SEM7_CREDIT_POINTS_5=trim($studentData[304]);
				$SEM7_COURSE_CODE_6=trim($studentData[305]);
				$SEM7_COURSE_TITLE_6=trim($studentData[306]);
				$SEM7_CREDIT_HOURS_6=trim($studentData[307]);
				$SEM7_GRADE_POINT_6=trim($studentData[308]);
				$SEM7_CREDIT_POINTS_6=trim($studentData[309]);
				

				$VIII_SEM=trim($studentData[310]);
				$SEM8_COURSE_CODE_1=trim($studentData[311]);
				$SEM8_COURSE_TITLE_1=trim($studentData[312]);
				$SEM8_CREDIT_HOURS_1=trim($studentData[313]);
				$SEM8_GRADE_POINT_1=trim($studentData[314]);
				$SEM8_CREDIT_POINTS_1=trim($studentData[315]);
				$SEM8_COURSE_CODE_2=trim($studentData[316]);
				$SEM8_COURSE_TITLE_2=trim($studentData[317]);
				$SEM8_CREDIT_HOURS_2=trim($studentData[318]);
				$SEM8_GRADE_POINT_2=trim($studentData[319]);
				$SEM8_CREDIT_POINTS_2=trim($studentData[320]);
				$SEM8_COURSE_CODE_3=trim($studentData[321]);
				$SEM8_COURSE_TITLE_3=trim($studentData[322]);
				$SEM8_CREDIT_HOURS_3=trim($studentData[323]);
				$SEM8_GRADE_POINT_3=trim($studentData[324]);
				$SEM8_CREDIT_POINTS_3=trim($studentData[325]);

				$TOTAL_CREDITS_CREDIT_HOURS=trim($studentData[326]);
				$TOTAL_CREDITS_CREDIT_POINTS=trim($studentData[327]);
				$OVERALL_GRADE_POINT_AVERAGE=trim($studentData[328]);
				$PERCENTAGE_OF_MARKS=trim($studentData[329]);
				$Month_of_Declaration_of_Results=trim($studentData[330]);
				$DATE=trim($studentData[331]);
				$Photo=trim($studentData[332]);
				$year1=trim($studentData[333]);
				$year2=trim($studentData[334]);
				$year3=trim($studentData[335]);
				$year4=trim($studentData[336]);
				$mother_name=trim($studentData[337]);
				$SEM7_SGPA=trim($studentData[338]);
				$SEM8_SGPA=trim($studentData[339]);
				
				if($Photo!=''){
					//path of photos
					$profile_path_org = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$Photo;
					//set profile image   
					
					$profilex = 12.5;
					$profiley = 16;
					$profileWidth = 20;
					$profileHeight = 20;
					$pdfBig->Image($profile_path_org, $profilex,$profiley,$profileWidth,$profileHeight, 'jpg', '', true, false);
					$pdf->Image($profile_path_org, $profilex,$profiley,$profileWidth,$profileHeight, 'jpg', '', true, false);
				}
				$left_pos=10.5;
				$left_pos_two=112;
				//Start pdfBig  
				$pdfBig->SetFont($arial, '', 8, '', false); 
				$pdfBig->SetTextColor(0, 0, 0);    
				$pdfBig->SetXY($left_pos, 11.5);
				$pdfBig->MultiCell(0, 0, 'ID No.: <b>'.$REG_No.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);
				if(!empty($AADHAR_NO)){
				$pdfBig->SetFont($arial, '', 8, '', false); 
				$pdfBig->SetTextColor(0, 0, 0); 
				$pdfBig->SetXY($left_pos, 36.5);
				$pdfBig->MultiCell(0, 0, 'Aadhar No: <b>'.$AADHAR_NO.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);
				}
				
				$pdfBig->SetFont($arialb, '', 12, '', false); 
				$pdfBig->SetXY(12, 42.7);
				$pdfBig->Cell(186, 0, 'CONSOLIDATED MARKS MEMO/GRADE SHEET/CREDIT SHEET', 0, false, 'C');
				
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos, 51);		
				$pdfBig->Cell(10, 0, 'Name :', 0, false, 'L');			
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(20.5, 51);		
				$pdfBig->Cell(91, 0, $STUDENT_NAME, 0, false, 'L');
				
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos_two, 51);		
				$pdfBig->Cell(0, 0, "Father's Name :", 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(132.5, 51);		
				$pdfBig->Cell(0, 0, $FATHERS_NAME, 0, false, 'L');


				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos, 55.5);		
				$pdfBig->Cell(0, 0, 'Programme :', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(28, 55.5);		
				$pdfBig->Cell(83, 0, $PROGRAMME, 0, false, 'L');
					
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos_two, 55.5);		
				$pdfBig->Cell(64, 0, "Mother's Name :", 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(133.5, 55.5);		
				$pdfBig->Cell(64, 0, $mother_name, 0, false, 'L');


				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos, 60);		
				$pdfBig->Cell(0, 0, 'Name of the College :', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(39, 60);
				$pdfBig->Cell(74, 0, $College, 0, false, 'L');
				
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos_two, 60);		
				$pdfBig->Cell(0, 0, 'Year of Admission :', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(137, 60);		
				$pdfBig->Cell(0, 0, $Year_of_admission, 0, false, 'L');


				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos, 64.5);		
				$pdfBig->Cell(101, 0, 'Department :', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(28, 64.5);		
				$pdfBig->Cell(83, 0, $Department, 0, false, 'L');
				
				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos_two, 64.5);		
				$pdfBig->Cell(0, 0, 'Year of Completion :', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(138, 64.5);		
				$pdfBig->Cell(0, 0, $Date_of_successful_completion, 0, false, 'L');


				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos, 69);		
				$pdfBig->Cell(0, 0, 'Specialization :', 0, false, 'L');	
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(31, 69);	
				$pdfBig->Cell(80.5, 0, $Specialization, 0, false, 'L');
				

				$pdfBig->SetFont($arial, '', 8, '', false);
				$pdfBig->SetXY($left_pos_two, 69);		
				$pdfBig->Cell(0, 0, 'Class Awarded :', 0, false, 'L');
				$pdfBig->SetFont($arialb, '', 8, '', false);
				$pdfBig->SetXY(133, 69);		
				$pdfBig->Cell(0, 0, $Class_Awarded, 0, false, 'L');
				
				
				/*Left Part Start*/
				$pdfBig->SetXY(11.5, 75);	
				$pdfBig->MultiCell(15, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(10.2, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(15, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.2, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				
				$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
				$pdfBig->SetXY(11.5, 74.9);	
				$pdfBig->setCellPaddings( $left = '', $top = '0.7', $right = '', $bottom = '');
				$pdfBig->MultiCell(15, 7, 'Course Code', 0, 'C', 0, 0, '', '', true, 0, true);			
				$pdfBig->MultiCell(45.2, 7, "Course Title", 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdfBig->MultiCell(45.2, 7, '<span style="margin-top:5pt;">\nCourse Title</span>', 0, "L", 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(10.2, 7, 'Credit', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, 7, 'Letter Grade', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, 7, 'Credit Points', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(15, 7, 'Course Code', 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '1.4', $right = '', $bottom = '');
				$pdfBig->MultiCell(45.2, 7, 'Course Title', 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(11.2, 7, 'Credit', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, 7, 'Letter Grade', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, 7, 'Credit Points', 0, 'C', 0, 0, '', '', true, 0, true);

				
				// $pdfBig->SetXY(11.5, 77.3);	
				// $pdfBig->MultiCell(9.5, 5.5, 'Code', 0, 'C', 0, 0, '', '', true, 0, true);
				// $pdfBig->MultiCell(52.5, 5.5, '', 0, 'C', 0, 0, '', '', true, 0, true);
				// $pdfBig->MultiCell(10.2, 5.5, 'Hours', 0, 'C', 0, 0, '', '', true, 0, true);
				// $pdfBig->MultiCell(10.3, 5.5, 'Points', 0, 'C', 0, 0, '', '', true, 0, true);
				// $pdfBig->MultiCell(10.3, 5.5, 'Points', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
				$pdfBig->SetXY(11.5, 82.3);
				$pdfBig->Cell(72.3, 4.2, $I_SEM, 'LB', false, 'C');
				$pdfBig->Cell(31.5, 4.2, $year1, 'B', false, 'C');
				$pdfBig->Cell(83.3, 4.2, $II_SEM, 'RB', false, 'C');
				

				// 1st year start
				$y_axis=86.6;
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				for ($x = 1; $x <= 9; $x++) {
					
				// store current object
				$pdfBig->startTransaction();
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines = $pdfBig->MultiCell(45.2, 0, ${"SEM1_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

				/*first sem Start*/
				if($lines>1){
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=6;	
				}else{
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=3.4;	
							
				}
				
				$pdfBig->MultiCell(15, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(10.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
						

				if($lines>1){
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=6;
							
				}else{
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=3.4;	
							
				}
				
				$pdfBig->MultiCell(15, $chight, ${"SEM1_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				
				$pdfBig->MultiCell(45.2, $chight, ${"SEM1_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(10.2, $chight, ${"SEM1_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, ${"SEM1_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, ${"SEM1_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				$y_axis=$y_axis+$chight;
				
			}
			/*1st sem end*/
			/*2nd sem Start*/
			$x_axis=104.5;
			$pdfBig->SetXY($x_axis, 75);
			$y_axis=86.6;
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
			for ($x = 1; $x <= 11; $x++) {
					
				// store current object
				$pdfBig->startTransaction();
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines2 = $pdfBig->MultiCell(45.2, 0, ${"SEM2_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

				/*Left Part Start*/
				
				if($lines2>1){
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=4.8;	
				}else{
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
				}	

				$pdfBig->MultiCell(15, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);


				if($lines2>1){
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=4.8;
							
				}else{
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
						
				}
				/////

				$pdfBig->MultiCell(15, $chight2, ${"SEM2_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				$pdfBig->MultiCell(45.2, $chight2, ${"SEM2_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(11.2, $chight2, ${"SEM2_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight2, ${"SEM2_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, $chight2, ${"SEM2_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				
					$y_axis=$y_axis+$chight2;
				
			}
			/*2nd sem end*/
			$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
			$pdfBig->SetXY(11.5, 122.5);
			//$pdfBig->setCellPaddings( $left = '5.5', $top = '', $right = '', $bottom = '');
			$pdfBig->Cell(93, 4.2, 'SGPA: '.$SEM1_SGPA, 'LRTB', false, 'L');
			$pdfBig->Cell(94.1, 4.2,'SGPA: '. $SEM2_SGPA, 'LRTB', false, 'L');
	// 1st year end
	/*2nd year start*/
			$y_axis=$pdfBig->GetY();
			$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
				$pdfBig->SetXY(11.5, $y_axis+4);
				$pdfBig->Cell(72.3, 4.2, $III_SEM, 'LB', false, 'C');
				$pdfBig->Cell(31.5, 4.2, $year2, 'B', false, 'C');
				$pdfBig->Cell(83.3, 4.2, $IV_SEM, 'RB', false, 'C');
				

	/*3rd sem Start*/
				$y_axis=$pdfBig->GetY()+4.5;
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				for ($x = 1; $x <= 8; $x++) {
					
				// store current object
				$pdfBig->startTransaction();
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines = $pdfBig->MultiCell(45.2, 0, ${"SEM3_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

				
				if($lines>1){
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=7.8;	
				}else{
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdfBig->MultiCell(15, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(10.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
						

				if($lines>1){
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=5.8;
							
				}else{
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdfBig->MultiCell(15, $chight, ${"SEM3_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				// //$pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				
				$pdfBig->MultiCell(45.2, $chight, ${"SEM3_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				// //$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(10.2, $chight, ${"SEM3_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, ${"SEM3_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, ${"SEM3_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				$y_axis=$y_axis+$chight;
				
			}
	/*3rd sem end*/
	/*4th sem Start*/
			$x_axis=104.5;
			$pdfBig->SetXY($x_axis, 75);
			$y_axis=$pdfBig->GetY()+55.9;
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
			for ($x = 1; $x <= 11; $x++) {
					
				// store current object
				$pdfBig->startTransaction();
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines2 = $pdfBig->MultiCell(45.2, 0, ${"SEM4_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

				
				
				if($lines2>1){
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=5.7;	
				}else{
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=5.7;	
				}	

				$pdfBig->MultiCell(15, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);


				if($lines2>1){
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=5.2;
							
				}else{
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=3;	
				}
				/////

				$pdfBig->MultiCell(15, $chight2, ${"SEM4_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				$pdfBig->MultiCell(45.2, $chight2, ${"SEM4_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(11.2, $chight2, ${"SEM4_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight2, ${"SEM4_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, $chight2, ${"SEM4_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				
					$y_axis=$y_axis+$chight2;
				
			}
	/*4th sem end*/
			$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
			$pdfBig->SetXY(11.5, $pdfBig->GetY()+6);
			//$pdfBig->setCellPaddings( $left = '5.5', $top = '', $right = '', $bottom = '');
			$pdfBig->Cell(93, 4.2, 'SGPA: '.$SEM3_SGPA, 'LRTB', false, 'L');
			$pdfBig->Cell(94.1, 4.2,'SGPA: '. $SEM4_SGPA, 'LRTB', false, 'L');
	/* 2nd year end*/
	/*3rd year start*/
			$y_axis=$pdfBig->GetY();
			$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
				$pdfBig->SetXY(11.5, $y_axis+4);
				$pdfBig->Cell(72.3, 4.2, $V_SEM, 'LB', false, 'C');
				$pdfBig->Cell(31.5, 4.2, $year3, 'B', false, 'C');
				$pdfBig->Cell(83.3, 4.2, $VI_SEM, 'RB', false, 'C');
				

	/*5th sem Start*/
				$y_axis=$pdfBig->GetY()+4.5;
				$pdfBig->SetFont($arialNarrow, '', 7, '', false);
				for ($x = 1; $x <= 10; $x++) {
					
				// store current object
				$pdfBig->startTransaction();
				$pdfBig->SetFont($arialNarrow, '', 7, '', false);
				// get the number of lines
				$lines = $pdfBig->MultiCell(45.2, 0, ${"SEM5_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

				
				if($lines>1){
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=7.7;	
				}else{
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=8;	
							
				}
				
				$pdfBig->MultiCell(15, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(10.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
						

				if($lines>1){
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=5.8;
							
				}else{
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdfBig->MultiCell(15, $chight, ${"SEM5_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				// //$pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				
				$pdfBig->MultiCell(45.2, $chight, ${"SEM5_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				// //$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(10.2, $chight, ${"SEM5_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, ${"SEM5_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, ${"SEM5_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				$y_axis=$y_axis+$chight;
				
			}
	/*5th sem end*/
	/*6th sem Start*/
			$x_axis=104.5;
			$pdfBig->SetXY($x_axis, 75);
			$y_axis=$pdfBig->GetY()+102.5;
				$pdfBig->SetFont($arialNarrow, '', 7, '', false);
			for ($x = 1; $x <= 8; $x++) {
					
				// store current object
				$pdfBig->startTransaction();
				$pdfBig->SetFont($arialNarrow, '', 7, '', false);
				// get the number of lines
				$lines2 = $pdfBig->MultiCell(45.2, 0, ${"SEM6_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

				
				
				if($lines2>1){
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=5.7;	
				}else{
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
				}	

				$pdfBig->MultiCell(15, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);


				if($lines2>1){
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=5.8;
							
				}else{
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
						
				}
				/////

				$pdfBig->MultiCell(15, $chight2, ${"SEM6_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				$pdfBig->MultiCell(45.2, $chight2, ${"SEM6_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(11.2, $chight2, ${"SEM6_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight2, ${"SEM6_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, $chight2, ${"SEM6_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				
					$y_axis=$y_axis+$chight2;
				
			}
	/*6th sem end*/
			$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
			$pdfBig->SetXY(11.5, $pdfBig->GetY()+4.2);
			//$pdfBig->setCellPaddings( $left = '5.5', $top = '', $right = '', $bottom = '');
			$pdfBig->Cell(93, 4.2, 'SGPA: '.$SEM5_SGPA, 'LRTB', false, 'L');
			$pdfBig->Cell(94.1, 4.2,'SGPA: '. $SEM6_SGPA, 'LRTB', false, 'L');
	/*3rd year end*/
	/*4th year start*/
			$y_axis=$pdfBig->GetY();
			$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
				$pdfBig->SetXY(11.5, $y_axis+4);
				$pdfBig->Cell(72.3, 4.2, $VII_SEM, 'LB', false, 'C');
				$pdfBig->Cell(31.5, 4.2, $year4, 'B', false, 'C');
				$pdfBig->Cell(83.3, 4.2, $VIII_SEM, 'RB', false, 'C');
				

	/*7th sem Start*/
				$y_axis=$pdfBig->GetY()+4.5;
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				for ($x = 1; $x <= 6; $x++) {
					
				// store current object
				$pdfBig->startTransaction();
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines = $pdfBig->MultiCell(45.2, 0, ${"SEM7_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

				
				if($lines>1){
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=9.5;	
				}else{
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdfBig->MultiCell(15, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(10.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
						

				if($lines>1){
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=9;
							
				}else{
						$pdfBig->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdfBig->MultiCell(15, $chight, ${"SEM7_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				// //$pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				
				$pdfBig->MultiCell(45.2, $chight, ${"SEM7_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				// //$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(10.2, $chight, ${"SEM7_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, ${"SEM7_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight, ${"SEM7_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				$y_axis=$y_axis+$chight;
				
			}
	/*7th sem end*/
	/*8th sem Start*/
			$x_axis=104.5;
			$y_axis=$pdfBig->GetY()-22;
			$pdfBig->SetXY($x_axis, $y_axis);
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
			for ($x = 1; $x <= 8; $x++) {
					
				// store current object
				$pdfBig->startTransaction();
				$pdfBig->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines2 = $pdfBig->MultiCell(45.2, 0, ${"SEM8_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdfBig=$pdfBig->rollbackTransaction(); // restore previous object

				
				
				if($lines2>1){
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=5;	
				}else{
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=3;	
				}	

				$pdfBig->MultiCell(15, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(45.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);


				if($lines2>1){
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=5;
							
				}else{
						$pdfBig->SetXY($x_axis, $y_axis);
						$chight2=3.1;	
						
				}
				/////

				$pdfBig->MultiCell(15, $chight2, ${"SEM8_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				$pdfBig->MultiCell(45.2, $chight2, ${"SEM8_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdfBig->MultiCell(11.2, $chight2, ${"SEM8_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.3, $chight2, ${"SEM8_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdfBig->MultiCell(11.4, $chight2, ${"SEM8_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				
					$y_axis=$y_axis+$chight2;
				
			}
			$pdfBig->SetXY(11.5, $y_axis+0.8);
			$pdfBig->MultiCell(187, 1, '', 'T', 'L', 0, 0, '', '', true, 0, true);
	/*8th sem end*/
			$pdfBig->SetFont($arialNarrowB, '', 7, '', false);
			$pdfBig->SetXY(11.5, $pdfBig->GetY()+0.2);
			//$pdfBig->setCellPaddings( $left = '5.5', $top = '', $right = '', $bottom = '');
			$pdfBig->Cell(93, 4.2, 'SGPA: '.$SEM7_SGPA, 'LRB', false, 'L');
			$pdfBig->Cell(94.1, 4.2,'SGPA: '. $SEM8_SGPA, 'LRB', false, 'L');		
	/*4th year end*/						
				/*Right Part End*/
				$pdfBig->SetFont($arialNarrow, '', 7, '', false); 
				$pdfBig->SetXY(10.5, 253);
				$pdfBig->MultiCell(0, 0, 'Total Credit Hours: <b>'.$TOTAL_CREDITS_CREDIT_HOURS.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(35.5, 253);
				$pdfBig->MultiCell(0, 0, 'Total Credit points: <b>'.$TOTAL_CREDITS_CREDIT_POINTS.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(82.5, 253);
				$pdfBig->MultiCell(0, 0, 'OVERALL GRADE POINT AVERAGE:&nbsp;&nbsp;<b>'.$OVERALL_GRADE_POINT_AVERAGE.'</b>&nbsp;&nbsp;(10.00 basis)', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdfBig->SetXY(140.5, 253);
				$pdfBig->MultiCell(0, 0, '<b> PERCENTAGE OF MARKS: '.$PERCENTAGE_OF_MARKS.'% </b>', 0, 'L', 0, 0, '', '', true, 0, true);

				$pdfBig->SetXY(10.5, 257);
				$pdfBig->MultiCell(0, 0, '<b>Month of Declaration of Results: '.$Month_of_Declaration_of_Results.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);

				$pdfBig->SetFont($arial, '', 10, '', false); 
				$pdfBig->SetTextColor(0, 0, 0);    
				$pdfBig->SetXY(13.5, 262);
				$pdfBig->Cell(0, 0, 'Dated: '.$DATE, 0, false, 'L');
				$pdfBig->SetFont($arial, '', 11, '', false); 
				$pdfBig->SetXY(143, 261.5);
				$pdfBig->Cell(0, 0, 'Controller of Examinations', 0, false, 'L');  
				//End pdfBig 
				
				//Start pdf
				$pdf->SetFont($arial, '', 8, '', false); 
				$pdf->SetTextColor(0, 0, 0);    
				$pdf->SetXY($left_pos, 11.5);
				$pdf->MultiCell(0, 0, 'ID No.: <b>'.$REG_No.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);
				if(!empty($AADHAR_NO)){
					$pdf->SetFont($arial, '', 8, '', false); 
					$pdf->SetTextColor(0, 0, 0); 
					$pdf->SetXY($left_pos, 36.5);
					$pdf->MultiCell(0, 0, 'Aadhar No: <b>'.$AADHAR_NO.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);
				}
				
				$pdf->SetFont($arialb, '', 12, '', false); 
				$pdf->SetXY(12, 42.7);
				$pdf->Cell(186, 0, 'CONSOLIDATED MARKS MEMO/GRADE SHEET/CREDIT SHEET', 0, false, 'C');
				
				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos, 51);		
				$pdf->Cell(10, 0, 'Name :', 0, false, 'L');			
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(20.5, 51);		
				$pdf->Cell(91, 0, $STUDENT_NAME, 0, false, 'L');
				
				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos_two, 51);		
				$pdf->Cell(0, 0, "Father's Name :", 0, false, 'L');
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(132.5, 51);		
				$pdf->Cell(0, 0, $FATHERS_NAME, 0, false, 'L');


				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos, 55.5);		
				$pdf->Cell(0, 0, 'Programme :', 0, false, 'L');
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(28, 55.5);		
				$pdf->Cell(83, 0, $PROGRAMME, 0, false, 'L');
					
				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos_two, 55.5);		
				$pdf->Cell(64, 0, "Mother's Name :", 0, false, 'L');
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(133.5, 55.5);		
				$pdf->Cell(64, 0, $mother_name, 0, false, 'L');


				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos, 60);		
				$pdf->Cell(0, 0, 'Name of the College :', 0, false, 'L');
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(39, 60);
				$pdf->Cell(74, 0, $College, 0, false, 'L');
				
				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos_two, 60);		
				$pdf->Cell(0, 0, 'Year of Admission :', 0, false, 'L');
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(137, 60);		
				$pdf->Cell(0, 0, $Year_of_admission, 0, false, 'L');


				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos, 64.5);		
				$pdf->Cell(101, 0, 'Department :', 0, false, 'L');
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(28, 64.5);		
				$pdf->Cell(83, 0, $Department, 0, false, 'L');
				
				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos_two, 64.5);		
				$pdf->Cell(0, 0, 'Year of Completion :', 0, false, 'L');
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(138, 64.5);		
				$pdf->Cell(0, 0, $Date_of_successful_completion, 0, false, 'L');


				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos, 69);		
				$pdf->Cell(0, 0, 'Specialization :', 0, false, 'L');	
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(31, 69);	
				$pdf->Cell(80.5, 0, $Specialization, 0, false, 'L');
				

				$pdf->SetFont($arial, '', 8, '', false);
				$pdf->SetXY($left_pos_two, 69);		
				$pdf->Cell(0, 0, 'Class Awarded :', 0, false, 'L');
				$pdf->SetFont($arialb, '', 8, '', false);
				$pdf->SetXY(133, 69);		
				$pdf->Cell(0, 0, $Class_Awarded, 0, false, 'L');
				
				
				/*Left Part Start*/
				$pdf->SetXY(11.5, 75);	
				$pdf->MultiCell(15, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(10.2, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(15, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.2, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, 7, '', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
				
				$pdf->SetFont($arialNarrowB, '', 7, '', false);
				$pdf->SetXY(11.5, 74.9);	
				$pdf->setCellPaddings( $left = '', $top = '0.7', $right = '', $bottom = '');
				$pdf->MultiCell(15, 7, 'Course Code', 0, 'C', 0, 0, '', '', true, 0, true);			
				$pdf->MultiCell(45.2, 7, "Course Title", 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdf->MultiCell(45.2, 7, '<span style="margin-top:5pt;">\nCourse Title</span>', 0, "L", 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(10.2, 7, 'Credit', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, 7, 'Letter Grade', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, 7, 'Credit Points', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(15, 7, 'Course Code', 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '1.4', $right = '', $bottom = '');
				$pdf->MultiCell(45.2, 7, 'Course Title', 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(11.2, 7, 'Credit', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, 7, 'Letter Grade', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, 7, 'Credit Points', 0, 'C', 0, 0, '', '', true, 0, true);

				
				// $pdf->SetXY(11.5, 77.3);	
				// $pdf->MultiCell(9.5, 5.5, 'Code', 0, 'C', 0, 0, '', '', true, 0, true);
				// $pdf->MultiCell(52.5, 5.5, '', 0, 'C', 0, 0, '', '', true, 0, true);
				// $pdf->MultiCell(10.2, 5.5, 'Hours', 0, 'C', 0, 0, '', '', true, 0, true);
				// $pdf->MultiCell(10.3, 5.5, 'Points', 0, 'C', 0, 0, '', '', true, 0, true);
				// $pdf->MultiCell(10.3, 5.5, 'Points', 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->SetFont($arialNarrowB, '', 7, '', false);
				$pdf->SetXY(11.5, 82.3);
				$pdf->Cell(72.3, 4.2, $I_SEM, 'LB', false, 'C');
				$pdf->Cell(31.5, 4.2, $year1, 'B', false, 'C');
				$pdf->Cell(83.3, 4.2, $II_SEM, 'RB', false, 'C');
				

				// 1st year start
				$y_axis=86.6;
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				for ($x = 1; $x <= 9; $x++) {
					
				// store current object
				$pdf->startTransaction();
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines = $pdf->MultiCell(45.2, 0, ${"SEM1_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdf=$pdf->rollbackTransaction(); // restore previous object

				/*first sem Start*/
				if($lines>1){
						$pdf->SetXY(11.5, $y_axis);
						$chight=6;	
				}else{
						$pdf->SetXY(11.5, $y_axis);
						$chight=3.4;	
							
				}
				
				$pdf->MultiCell(15, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(10.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
						

				if($lines>1){
						$pdf->SetXY(11.5, $y_axis);
						$chight=6;
							
				}else{
						$pdf->SetXY(11.5, $y_axis);
						$chight=3.4;	
							
				}
				
				$pdf->MultiCell(15, $chight, ${"SEM1_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				
				$pdf->MultiCell(45.2, $chight, ${"SEM1_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(10.2, $chight, ${"SEM1_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, ${"SEM1_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, ${"SEM1_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				$y_axis=$y_axis+$chight;
				
			}
			/*1st sem end*/
			/*2nd sem Start*/
			$x_axis=104.5;
			$pdf->SetXY($x_axis, 75);
			$y_axis=86.6;
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
			for ($x = 1; $x <= 11; $x++) {
					
				// store current object
				$pdf->startTransaction();
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines2 = $pdf->MultiCell(45.2, 0, ${"SEM2_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdf=$pdf->rollbackTransaction(); // restore previous object

				/*Left Part Start*/
				
				if($lines2>1){
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=4.8;	
				}else{
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
				}	

				$pdf->MultiCell(15, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);


				if($lines2>1){
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=4.8;
							
				}else{
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
						
				}
				/////

				$pdf->MultiCell(15, $chight2, ${"SEM2_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				$pdf->MultiCell(45.2, $chight2, ${"SEM2_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(11.2, $chight2, ${"SEM2_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight2, ${"SEM2_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, $chight2, ${"SEM2_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				
					$y_axis=$y_axis+$chight2;
				
			}
			/*2nd sem end*/
			$pdf->SetFont($arialNarrowB, '', 7, '', false);
			$pdf->SetXY(11.5, 122.5);
			//$pdf->setCellPaddings( $left = '5.5', $top = '', $right = '', $bottom = '');
			$pdf->Cell(93, 4.2, 'SGPA: '.$SEM1_SGPA, 'LRTB', false, 'L');
			$pdf->Cell(94.1, 4.2,'SGPA: '. $SEM2_SGPA, 'LRTB', false, 'L');
	// 1st year end
	/*2nd year start*/
			$y_axis=$pdf->GetY();
			$pdf->SetFont($arialNarrowB, '', 7, '', false);
				$pdf->SetXY(11.5, $y_axis+4);
				$pdf->Cell(72.3, 4.2, $III_SEM, 'LB', false, 'C');
				$pdf->Cell(31.5, 4.2, $year2, 'B', false, 'C');
				$pdf->Cell(83.3, 4.2, $IV_SEM, 'RB', false, 'C');
				

	/*3rd sem Start*/
				$y_axis=$pdf->GetY()+4.5;
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				for ($x = 1; $x <= 8; $x++) {
					
				// store current object
				$pdf->startTransaction();
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines = $pdf->MultiCell(45.2, 0, ${"SEM3_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdf=$pdf->rollbackTransaction(); // restore previous object

				
				if($lines>1){
						$pdf->SetXY(11.5, $y_axis);
						$chight=7.8;	
				}else{
						$pdf->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdf->MultiCell(15, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(10.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
						

				if($lines>1){
						$pdf->SetXY(11.5, $y_axis);
						$chight=5.8;
							
				}else{
						$pdf->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdf->MultiCell(15, $chight, ${"SEM3_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				// //$pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				
				$pdf->MultiCell(45.2, $chight, ${"SEM3_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				// //$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(10.2, $chight, ${"SEM3_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, ${"SEM3_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, ${"SEM3_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				$y_axis=$y_axis+$chight;
				
			}
	/*3rd sem end*/
	/*4th sem Start*/
			$x_axis=104.5;
			$pdf->SetXY($x_axis, 75);
			$y_axis=$pdf->GetY()+55.9;
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
			for ($x = 1; $x <= 11; $x++) {
					
				// store current object
				$pdf->startTransaction();
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines2 = $pdf->MultiCell(45.2, 0, ${"SEM4_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdf=$pdf->rollbackTransaction(); // restore previous object

				
				
				if($lines2>1){
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=5.7;	
				}else{
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=5.7;	
				}	

				$pdf->MultiCell(15, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);


				if($lines2>1){
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=5.7;
							
				}else{
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
						
				}
				/////

				$pdf->MultiCell(15, $chight2, ${"SEM4_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				$pdf->MultiCell(45.2, $chight2, ${"SEM4_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(11.2, $chight2, ${"SEM4_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight2, ${"SEM4_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, $chight2, ${"SEM4_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				
					$y_axis=$y_axis+$chight2;
				
			}
	/*4th sem end*/
			$pdf->SetFont($arialNarrowB, '', 7, '', false);
			$pdf->SetXY(11.5, $pdf->GetY()+6);
			//$pdf->setCellPaddings( $left = '5.5', $top = '', $right = '', $bottom = '');
			$pdf->Cell(93, 4.2, 'SGPA: '.$SEM3_SGPA, 'LRTB', false, 'L');
			$pdf->Cell(94.1, 4.2,'SGPA: '. $SEM4_SGPA, 'LRTB', false, 'L');
	/* 2nd year end*/
	/*3rd year start*/
			$y_axis=$pdf->GetY();
			$pdf->SetFont($arialNarrowB, '', 7, '', false);
				$pdf->SetXY(11.5, $y_axis+4);
				$pdf->Cell(72.3, 4.2, $V_SEM, 'LB', false, 'C');
				$pdf->Cell(31.5, 4.2, $year3, 'B', false, 'C');
				$pdf->Cell(83.3, 4.2, $VI_SEM, 'RB', false, 'C');
				

	/*5th sem Start*/
				$y_axis=$pdf->GetY()+4.5;
				$pdf->SetFont($arialNarrow, '', 7, '', false);
				for ($x = 1; $x <= 10; $x++) {
					
				// store current object
				$pdf->startTransaction();
				$pdf->SetFont($arialNarrow, '', 7, '', false);
				// get the number of lines
				$lines = $pdf->MultiCell(45.2, 0, ${"SEM5_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdf=$pdf->rollbackTransaction(); // restore previous object

				
				if($lines>1){
						$pdf->SetXY(11.5, $y_axis);
						$chight=7.7;	
				}else{
						$pdf->SetXY(11.5, $y_axis);
						$chight=8;	
							
				}
				
				$pdf->MultiCell(15, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(10.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
						

				if($lines>1){
						$pdf->SetXY(11.5, $y_axis);
						$chight=5.8;
							
				}else{
						$pdf->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdf->MultiCell(15, $chight, ${"SEM5_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				// //$pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				
				$pdf->MultiCell(45.2, $chight, ${"SEM5_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				// //$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(10.2, $chight, ${"SEM5_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, ${"SEM5_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, ${"SEM5_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				$y_axis=$y_axis+$chight;
				
			}
	/*5th sem end*/
	/*6th sem Start*/
			$x_axis=104.5;
			$pdf->SetXY($x_axis, 75);
			$y_axis=$pdf->GetY()+102.5;
				$pdf->SetFont($arialNarrow, '', 7, '', false);
			for ($x = 1; $x <= 8; $x++) {
					
				// store current object
				$pdf->startTransaction();
				$pdf->SetFont($arialNarrow, '', 7, '', false);
				// get the number of lines
				$lines2 = $pdf->MultiCell(45.2, 0, ${"SEM6_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdf=$pdf->rollbackTransaction(); // restore previous object

				
				
				if($lines2>1){
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=5.7;	
				}else{
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
				}	

				$pdf->MultiCell(15, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);


				if($lines2>1){
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=5.8;
							
				}else{
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=3.2;	
						
				}
				/////

				$pdf->MultiCell(15, $chight2, ${"SEM6_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				$pdf->MultiCell(45.2, $chight2, ${"SEM6_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(11.2, $chight2, ${"SEM6_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight2, ${"SEM6_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, $chight2, ${"SEM6_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				
					$y_axis=$y_axis+$chight2;
				
			}
	/*6th sem end*/
			$pdf->SetFont($arialNarrowB, '', 7, '', false);
			$pdf->SetXY(11.5, $pdf->GetY()+4.2);
			//$pdf->setCellPaddings( $left = '5.5', $top = '', $right = '', $bottom = '');
			$pdf->Cell(93, 4.2, 'SGPA: '.$SEM5_SGPA, 'LRTB', false, 'L');
			$pdf->Cell(94.1, 4.2,'SGPA: '. $SEM6_SGPA, 'LRTB', false, 'L');
	/*3rd year end*/
	/*4th year start*/
			$y_axis=$pdf->GetY();
			$pdf->SetFont($arialNarrowB, '', 7, '', false);
				$pdf->SetXY(11.5, $y_axis+4);
				$pdf->Cell(72.3, 4.2, $VII_SEM, 'LB', false, 'C');
				$pdf->Cell(31.5, 4.2, $year4, 'B', false, 'C');
				$pdf->Cell(83.3, 4.2, $VIII_SEM, 'RB', false, 'C');
				

	/*7th sem Start*/
				$y_axis=$pdf->GetY()+4.5;
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				for ($x = 1; $x <= 6; $x++) {
					
				// store current object
				$pdf->startTransaction();
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines = $pdf->MultiCell(45.2, 0, ${"SEM7_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdf=$pdf->rollbackTransaction(); // restore previous object

				
				if($lines>1){
						$pdf->SetXY(11.5, $y_axis);
						$chight=9.5;	
				}else{
						$pdf->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdf->MultiCell(15, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(10.2, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
						

				if($lines>1){
						$pdf->SetXY(11.5, $y_axis);
						$chight=9;
							
				}else{
						$pdf->SetXY(11.5, $y_axis);
						$chight=3.2;	
							
				}
				
				$pdf->MultiCell(15, $chight, ${"SEM7_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				// //$pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				
				$pdf->MultiCell(45.2, $chight, ${"SEM7_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				// //$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(10.2, $chight, ${"SEM7_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, ${"SEM7_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight, ${"SEM7_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				$y_axis=$y_axis+$chight;
				
			}
	/*7th sem end*/
	/*8th sem Start*/
			$x_axis=104.5;
			$y_axis=$pdf->GetY()-22;
			$pdf->SetXY($x_axis, $y_axis);
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
			for ($x = 1; $x <= 8; $x++) {
					
				// store current object
				$pdf->startTransaction();
				$pdf->SetFont($arialNarrow, '', 6.5, '', false);
				// get the number of lines
				$lines2 = $pdf->MultiCell(45.2, 0, ${"SEM8_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
				$pdf=$pdf->rollbackTransaction(); // restore previous object

				
				
				if($lines2>1){
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=5;	
				}else{
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=3;	
				}	

				$pdf->MultiCell(15, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(45.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.2, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, $chight2, '', 'LR', 'L', 0, 0, '', '', true, 0, true);


				if($lines2>1){
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=5;
							
				}else{
						$pdf->SetXY($x_axis, $y_axis);
						$chight2=3.1;	
						
				}
				/////

				$pdf->MultiCell(15, $chight2, ${"SEM8_COURSE_CODE_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0.4', $right = '', $bottom = '');
				$pdf->MultiCell(45.2, $chight2, ${"SEM8_COURSE_TITLE_" . $x}, 0, 'L', 0, 0, '', '', true, 0, true);
				//$pdf->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
				$pdf->MultiCell(11.2, $chight2, ${"SEM8_CREDIT_HOURS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.3, $chight2, ${"SEM8_GRADE_POINT_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);
				$pdf->MultiCell(11.4, $chight2, ${"SEM8_CREDIT_POINTS_" . $x}, 0, 'C', 0, 0, '', '', true, 0, true);

				
					$y_axis=$y_axis+$chight2;
				
			}
			$pdf->SetXY(11.5, $y_axis+0.8);
			$pdf->MultiCell(187, 1, '', 'T', 'L', 0, 0, '', '', true, 0, true);
	/*8th sem end*/
			
	/*4th year end*/						
				/*Right Part End*/
				$pdf->SetFont($arialNarrow, '', 7, '', false); 
				$pdf->SetXY(10.5, 250);
				$pdf->MultiCell(0, 0, 'Total Credit Hours: <b>'.$TOTAL_CREDITS_CREDIT_HOURS.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdf->SetXY(35.5, 250);
				$pdf->MultiCell(0, 0, 'Total Credit points: <b>'.$TOTAL_CREDITS_CREDIT_POINTS.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdf->SetXY(82.5, 250);
				$pdf->MultiCell(0, 0, 'OVERALL GRADE POINT AVERAGE:&nbsp;&nbsp;<b>'.$OVERALL_GRADE_POINT_AVERAGE.'</b>&nbsp;&nbsp;(10.00 basis)', 0, 'L', 0, 0, '', '', true, 0, true);
				$pdf->SetXY(140.5, 250);
				$pdf->MultiCell(0, 0, '<b> PERCENTAGE OF MARKS: '.$PERCENTAGE_OF_MARKS.'% </b>', 0, 'L', 0, 0, '', '', true, 0, true);

				$pdf->SetXY(10.5, 255);
				$pdf->MultiCell(0, 0, '<b>Month of Declaration of Results: '.$Month_of_Declaration_of_Results.'</b>', 0, 'L', 0, 0, '', '', true, 0, true);

				$pdf->SetFont($arial, '', 10, '', false); 
				$pdf->SetTextColor(0, 0, 0);    
				$pdf->SetXY(13.5, 262);
				$pdf->Cell(0, 0, 'Dated: '.$DATE, 0, false, 'L');
				$pdf->SetFont($arial, '', 11, '', false); 
				$pdf->SetXY(143, 261.5);
				$pdf->Cell(0, 0, 'Controller of Examinations', 0, false, 'L'); 					           
				//End pdf			
				
				// Ghost image
				$nameOrg=$STUDENT_NAME;
				
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
				$encryptedString = strtoupper(md5($str));
				$codeContents = "Student Name: " . $STUDENT_NAME . "\nID No: " . $REG_No  . "\nBatch/Academic Year: " . $Year_of_admission . "\n\n" . $encryptedString;
				$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
				
				$qrCodex = 177; 
				$qrCodey = 13.5;
				$qrCodeWidth =21;
				$qrCodeHeight = 21;
				$ecc = 'L';
				$pixel_Size = 1;
				$frame_Size = 1;  
				// \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
				// set style for barcode
				$style = array(
					// 'border' => 2,
					// 'vpadding' => 'auto',
					// 'hpadding' => 'auto',
					'fgcolor' => array(0,0,0),
					'bgcolor' => false, //array(255,255,255)
					'module_width' => 1, // width of a single module in points
					'module_height' => 1 // height of a single module in points
				);

				
				
				// $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);  
				$pdf->write2DBarcode($codeContents, 'QRCODE,L', $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, $style, 'N'); 
				$pdf->setPageMark(); 
				// $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
				$pdfBig->write2DBarcode($codeContents, 'QRCODE,L', $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, $style, 'N');
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
				$pdf->SetAlpha(1);
				$pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
				$pdfBig->SetAlpha(1);
				$pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
				
				// micro line
							
				$str = $nameOrg;
				$str = strtoupper(preg_replace('/\s+/', '', $str)); 
				$microlinestr=$str;
				$pdf->SetFont($arialb, '', 2, '', false);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY(178, 35); 
				$pdf->Cell(21, 0, $microlinestr, 0, false, 'C');     
				
				$pdfBig->SetFont($arialb, '', 2, '', false);
				$pdfBig->SetTextColor(0, 0, 0);
				$pdfBig->SetXY(178, 35);      
				$pdfBig->Cell(21, 0, $microlinestr, 0, false, 'C'); 

				if($previewPdf!=1){

					// $certName = str_replace("/", "_", $GUID) .".pdf";
					$certName = str_replace(array('/', '.'), '_', $GUID).'.pdf';//str_replace(".", "_", $GUID) .".pdf";
					
					$myPath = public_path().'/backend/temp_pdf_file';

					$fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;
					// echo $fileVerificationPath;die();
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
			$this->updateCardNo('BestiuT',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
        }
        $msg = '';
        
        $file_name =  str_replace("/", "_",'BestiuT'.date("Ymdhms")).'.pdf';
        
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
            $template_name="BestiuT";
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
