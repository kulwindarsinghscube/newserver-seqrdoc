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
use App\models\StudentTableMerge;
use App\models\SbStudentTable;
use App\models\PrintingDetail;
use App\models\SbPrintingDetail;
use App\models\ExcelUploadHistory;
use App\models\SbExceUploadHistory;
use App\models\StudentTable;

use App\Jobs\SendMailJob;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use Illuminate\Support\Facades\Redis;
use App\models\ThirdPartyRequests;
use App\Helpers\CoreHelper;
use Helper;

class PdfGenerateSaiuGrade7Job
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
        $merging_type=$pdf_data['dropdown_merging_type'];
        $verification_bg=$pdf_data['dropdown_verification_bg'];
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];
        
        // echo "<pre>";
        // print_r($studentDataOrg);
        // echo "</pre>";

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
        
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.ttf', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.ttf', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.ttf', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);



        $myriadArabic = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MyriadProRegular.ttf', 'TrueTypeUnicode', '', 96);
        $myriadArabicB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Myriad_Arabic_Bold.ttf', 'TrueTypeUnicode', '', 96);
        

        
        

        $preview_serial_no=1;
		//$card_serial_no="";
        $log_serial_no = 1;
        $cardDetails=$this->getNextCardNo('GradeCard');
        $card_serial_no=$cardDetails->next_serial_no;
        $generated_documents=0;  //for custom loader
        foreach ($studentDataOrg as $studentData) {
         
			if($card_serial_no>999999&&$previewPdf!=1){
				echo "<h5>Your card series ended...!</h5>";
				exit;
			}
			//For Custom Loader
            $startTimeLoader =  date('Y-m-d H:i:s');    
			$high_res_bg=$verification_bg; 
			$low_res_bg=$verification_bg;
			$pdfBig->AddPage();
			$pdfBig->SetFont($arialNarrowB, '', 8, '', false);
			//set background image
			$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;   

			if($previewPdf==1){
				if($previewWithoutBg!=1){
					$pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
				}
				// $date_font_size = '11';
				// $date_nox = 13;
				// $date_noy = 40;
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
			$pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false); // 420.116 296.926
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('TCPDF');
			$pdf->SetTitle('Certificate');
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
                if($merging_type=="Pre"){
                    $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
                }
			}
			
			$pdf->setPageMark();
            //if($previewPdf!=1){            
                $x= 173;
                $y = 39.1;
                $font_size=12;
                if($previewPdf!=1){
					$card_serial_no =$str =  str_pad($card_serial_no, 7, '0', STR_PAD_LEFT);
				}else{
					$card_serial_no = $str= str_pad($preview_serial_no, 7, '0', STR_PAD_LEFT);	
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
			
                
            //end pdf
            
            //Table's Titles
            $pdfBig->SetXY(16.7, 79.5);
            $pdfBig->SetFont($arial, '', 11, '', false); 
            $pdfBig->SetFillColor(255, 255, 255);
            $pdfBig->SetTextColor(0, 0, 0);  
            			
            $unique_id = trim($studentData[0]);
            $TransIssueDate = trim($studentData[1]);
            $candidate_name = trim($studentData[2]);
            $prnNo = trim($studentData[3]);
            $programme = trim($studentData[4]);
            $cumulativeGpa = trim($studentData[5]);
            $earnedCredit = trim($studentData[6]);

            $I_SEMESTER = trim($studentData[7]);
            $COURSE_CODE_11 = trim($studentData[8]);
            $COURSE_11 = trim($studentData[9]);
            $CREDIT_11 = trim($studentData[10]);
            $GRADE_11 = trim($studentData[11]);
            $COURSE_CODE_12 = trim($studentData[12]);
            $COURSE_12 = trim($studentData[13]);
            $CREDIT_12 = trim($studentData[14]);
            $GRADE_12 = trim($studentData[15]);
            $COURSE_CODE_13 = trim($studentData[16]);
            $COURSE_13 = trim($studentData[17]);
            $CREDIT_13 = trim($studentData[18]);
            $GRADE_13 = trim($studentData[19]);
            $COURSE_CODE_14 = trim($studentData[20]);
            $COURSE_14 = trim($studentData[21]);
            $CREDIT_14 = trim($studentData[22]);
            $GRADE_14 = trim($studentData[23]);
            $COURSE_CODE_15 = trim($studentData[24]);
            $COURSE_15 = trim($studentData[25]);
            $CREDIT_15 = trim($studentData[26]);
            $GRADE_15 = trim($studentData[27]);
            $COURSE_CODE_16 = trim($studentData[28]);
            $COURSE_16 = trim($studentData[29]);
            $CREDIT_16 = trim($studentData[30]);
            $GRADE_16 = trim($studentData[31]);
            $COURSE_CODE_17 = trim($studentData[32]);
            $COURSE_17 = trim($studentData[33]);
            $CREDIT_17 = trim($studentData[34]);
            $GRADE_17 = trim($studentData[35]);
            $COURSE_CODE_18 = trim($studentData[36]);
            $COURSE_18 = trim($studentData[37]);
            $CREDIT_18 = trim($studentData[38]);
            $GRADE_18 = trim($studentData[39]);
            $COURSE_CODE_19 = trim($studentData[40]);
            $COURSE_19 = trim($studentData[41]);
            $CREDIT_19 = trim($studentData[42]);
            $GRADE_19 = trim($studentData[43]);
            $SGPA_1 = trim($studentData[44]);

            $II_SEMESTER = trim($studentData[45]);
            $COURSE_CODE_21 = trim($studentData[46]);
            $COURSE_21 = trim($studentData[47]);
            $CREDIT_21 = trim($studentData[48]);
            $GRADE_21 = trim($studentData[49]);
            $COURSE_CODE_22 = trim($studentData[50]);
            $COURSE_22 = trim($studentData[51]);
            $CREDIT_22 = trim($studentData[52]);
            $GRADE_22 = trim($studentData[53]);
            $COURSE_CODE_23 = trim($studentData[54]);
            $COURSE_23 = trim($studentData[55]);
            $CREDIT_23 = trim($studentData[56]);
            $GRADE_23 = trim($studentData[57]);
            $COURSE_CODE_24 = trim($studentData[58]);
            $COURSE_24 = trim($studentData[59]);
            $CREDIT_24 = trim($studentData[60]);
            $GRADE_24 = trim($studentData[61]);
            $COURSE_CODE_25 = trim($studentData[62]);
            $COURSE_25 = trim($studentData[63]);
            $CREDIT_25 = trim($studentData[64]);
            $GRADE_25 = trim($studentData[65]);
            $COURSE_CODE_26 = trim($studentData[66]);
            $COURSE_26 = trim($studentData[67]);
            $CREDIT_26 = trim($studentData[68]);
            $GRADE_26 = trim($studentData[69]);
            $COURSE_CODE_27 = trim($studentData[70]);
            $COURSE_27 = trim($studentData[71]);
            $CREDIT_27 = trim($studentData[72]);
            $GRADE_27 = trim($studentData[73]);
            $COURSE_CODE_28 = trim($studentData[74]);
            $COURSE_28 = trim($studentData[75]);
            $CREDIT_28 = trim($studentData[76]);
            $GRADE_28 = trim($studentData[77]);
            $COURSE_CODE_29 = trim($studentData[78]);
            $COURSE_29 = trim($studentData[79]);
            $CREDIT_29 = trim($studentData[80]);
            $GRADE_29 = trim($studentData[81]);
            $SGPA_2 = trim($studentData[82]);
                    $III_SEMESTER = trim($studentData[83]);
            $COURSE_CODE_31 = trim($studentData[84]);
            $COURSE_31 = trim($studentData[85]);
            $CREDIT_31 = trim($studentData[86]);
            $GRADE_31 = trim($studentData[87]);
            $COURSE_CODE_32 = trim($studentData[88]);
            $COURSE_32 = trim($studentData[89]);
            $CREDIT_32 = trim($studentData[90]);
            $GRADE_32 = trim($studentData[91]);
            $COURSE_CODE_33 = trim($studentData[92]);
            $COURSE_33 = trim($studentData[93]);
            $CREDIT_33 = trim($studentData[94]);
            $GRADE_33 = trim($studentData[95]);
            $COURSE_CODE_34 = trim($studentData[96]);
            $COURSE_34 = trim($studentData[97]);
            $CREDIT_34 = trim($studentData[98]);
            $GRADE_34 = trim($studentData[99]);
            $COURSE_CODE_35 = trim($studentData[100]);
            $COURSE_35 = trim($studentData[101]);
            $CREDIT_35 = trim($studentData[102]);
            $GRADE_35 = trim($studentData[103]);
            $COURSE_CODE_36 = trim($studentData[104]);
            $COURSE_36 = trim($studentData[105]);
            $CREDIT_36 = trim($studentData[106]);
            $GRADE_36 = trim($studentData[107]);
            $COURSE_CODE_37 = trim($studentData[108]);
            $COURSE_37 = trim($studentData[109]);
            $CREDIT_37 = trim($studentData[110]);
            $GRADE_37 = trim($studentData[111]);
            $COURSE_CODE_38 = trim($studentData[112]);
            $COURSE_38 = trim($studentData[113]);
            $CREDIT_38 = trim($studentData[114]);
            $GRADE_38 = trim($studentData[115]);
            $COURSE_CODE_39 = trim($studentData[116]);
            $COURSE_39 = trim($studentData[117]);
            $CREDIT_39 = trim($studentData[118]);
            $GRADE_39 = trim($studentData[119]);
            $SGPA_3 = trim($studentData[120]);

                    $IV_SEMESTER = trim($studentData[121]);
            $COURSE_CODE_41 = trim($studentData[122]);
            $COURSE_41 = trim($studentData[123]);
            $CREDIT_41 = trim($studentData[124]);
            $GRADE_41 = trim($studentData[125]);
            $COURSE_CODE_42 = trim($studentData[126]);
            $COURSE_42 = trim($studentData[127]);
            $CREDIT_42 = trim($studentData[128]);
            $GRADE_42 = trim($studentData[129]);
            $COURSE_CODE_43 = trim($studentData[130]);
            $COURSE_43 = trim($studentData[131]);
            $CREDIT_43 = trim($studentData[132]);
            $GRADE_43 = trim($studentData[133]);
            $COURSE_CODE_44 = trim($studentData[134]);
            $COURSE_44 = trim($studentData[135]);
            $CREDIT_44 = trim($studentData[136]);
            $GRADE_44 = trim($studentData[137]);
            $COURSE_CODE_45 = trim($studentData[138]);
            $COURSE_45 = trim($studentData[139]);
            $CREDIT_45 = trim($studentData[140]);
            $GRADE_45 = trim($studentData[141]);
            $COURSE_CODE_46 = trim($studentData[142]);
            $COURSE_46 = trim($studentData[143]);
            $CREDIT_46 = trim($studentData[144]);
            $GRADE_46 = trim($studentData[145]);
            $COURSE_CODE_47 = trim($studentData[146]);
            $COURSE_47 = trim($studentData[147]);
            $CREDIT_47 = trim($studentData[148]);
            $GRADE_47 = trim($studentData[149]);
            $COURSE_CODE_48 = trim($studentData[150]);
            $COURSE_48 = trim($studentData[151]);
            $CREDIT_48 = trim($studentData[152]);
            $GRADE_48 = trim($studentData[153]);
            $COURSE_CODE_49 = trim($studentData[154]);
            $COURSE_49 = trim($studentData[155]);
            $CREDIT_49 = trim($studentData[156]);
            $GRADE_49 = trim($studentData[157]);
            $SGPA_4 = trim($studentData[158]);

            $V_SEMESTER = trim($studentData[159]);
            $COURSE_CODE_51 = trim($studentData[160]);
            $COURSE_51 = trim($studentData[161]);
            $CREDIT_51 = trim($studentData[162]);
            $GRADE_51 = trim($studentData[163]);
            $COURSE_CODE_52 = trim($studentData[164]);
            $COURSE_52 = trim($studentData[165]);
            $CREDIT_52 = trim($studentData[166]);
            $GRADE_52 = trim($studentData[167]);
            $COURSE_CODE_53 = trim($studentData[168]);
            $COURSE_53 = trim($studentData[169]);
            $CREDIT_53 = trim($studentData[170]);
            $GRADE_53 = trim($studentData[171]);
            $COURSE_CODE_54 = trim($studentData[172]);
            $COURSE_54 = trim($studentData[173]);
            $CREDIT_54 = trim($studentData[174]);
            $GRADE_54 = trim($studentData[175]);
            $COURSE_CODE_55 = trim($studentData[176]);
            $COURSE_55 = trim($studentData[177]);
            $CREDIT_55 = trim($studentData[178]);
            $GRADE_55 = trim($studentData[179]);
            $COURSE_CODE_56 = trim($studentData[180]);
            $COURSE_56 = trim($studentData[181]);
            $CREDIT_56 = trim($studentData[182]);
            $GRADE_56 = trim($studentData[183]);
            $COURSE_CODE_57 = trim($studentData[184]);
            $COURSE_57 = trim($studentData[185]);
            $CREDIT_57 = trim($studentData[186]);
            $GRADE_57 = trim($studentData[187]);
            $COURSE_CODE_58 = trim($studentData[188]);
            $COURSE_58 = trim($studentData[189]);
            $CREDIT_58 = trim($studentData[190]);
            $GRADE_58 = trim($studentData[191]);
            $COURSE_CODE_59 = trim($studentData[192]);
            $COURSE_59 = trim($studentData[193]);
            $CREDIT_59 = trim($studentData[194]);
            $GRADE_59 = trim($studentData[195]);
            $SGPA_5 = trim($studentData[196]);

            $VI_SEMESTER = trim($studentData[197]);
            $COURSE_CODE_61 = trim($studentData[198]);
            $COURSE_61 = trim($studentData[199]);
            $CREDIT_61 = trim($studentData[200]);
            $GRADE_61 = trim($studentData[201]);
            $COURSE_CODE_62 = trim($studentData[202]);
            $COURSE_62 = trim($studentData[203]);
            $CREDIT_62 = trim($studentData[204]);
            $GRADE_62 = trim($studentData[205]);
            $COURSE_CODE_63 = trim($studentData[206]);
            $COURSE_63 = trim($studentData[207]);
            $CREDIT_63 = trim($studentData[208]);
            $GRADE_63 = trim($studentData[209]);
            $COURSE_CODE_64 = trim($studentData[210]);
            $COURSE_64 = trim($studentData[211]);
            $CREDIT_64 = trim($studentData[212]);
            $GRADE_64 = trim($studentData[213]);
            $COURSE_CODE_65 = trim($studentData[214]);
            $COURSE_65 = trim($studentData[215]);
            $CREDIT_65 = trim($studentData[216]);
            $GRADE_65 = trim($studentData[217]);
            $COURSE_CODE_66 = trim($studentData[218]);
            $COURSE_66 = trim($studentData[219]);
            $CREDIT_66 = trim($studentData[220]);
            $GRADE_66 = trim($studentData[221]);
            $COURSE_CODE_67 = trim($studentData[222]);
            $COURSE_67 = trim($studentData[223]);
            $CREDIT_67 = trim($studentData[224]);
            $GRADE_67 = trim($studentData[225]);
            $COURSE_CODE_68 = trim($studentData[226]);
            $COURSE_68 = trim($studentData[227]);
            $CREDIT_68 = trim($studentData[228]);
            $GRADE_68 = trim($studentData[229]);
            $COURSE_CODE_69 = trim($studentData[230]);
            $COURSE_69 = trim($studentData[231]);
            $CREDIT_69 = trim($studentData[232]);
            $GRADE_69 = trim($studentData[233]);
            $SGPA_6 = trim($studentData[234]);
            $Academic_year_1 = trim($studentData[235]);
            $Academic_year_2 = trim($studentData[236]);
            $Academic_year_3 = trim($studentData[237]);


            $subjectData = [];
            $index = 7; // Start from index 7 in $studentData (1st subject of I Semester)

            for ($sem = 1; $sem <= 6; $sem++) {
                $semesterName = trim($studentData[$index++]); // 7 for I_SEMESTER, 45 for II_SEMESTER
                $subjects = [];

                for ($i = 1; $i <= 9; $i++) {
                    $courseCode = trim($studentData[$index++]);
                    $courseName = trim($studentData[$index++]);
                    $credit     = trim($studentData[$index++]);
                    $grade      = trim($studentData[$index++]);

                    // if ($courseCode != "") {
                    if ($courseCode !== '' || $courseName !== '' || $credit !== '' || $grade !== '') {
                        $subjects[] = [
                            'course_code' => $courseCode,
                            'course_name' => $courseName,
                            'credit'      => $credit,
                            'grade'       => $grade,
                        ];
                    }
                }

                $sgpa = trim($studentData[$index++]);

                $subjectData[] = [
                    'semester' => $semesterName,
                    'subjects' => $subjects,
                    'sgpa'     => $sgpa,
                ];
            }

            //Start pdfBig
            $pdfBig->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdfBig->setFontSpacing(1);
            $pdfBig->SetFont($arialb, '', 12, '', false);
            $pdfBig->SetXY(0, 42);
            $pdfBig->SetTextColor(46,109,164);
            $pdfBig->Cell(210, 0, 'TRANSCRIPT', 0, false, 'C');

            // Header Box  
            // $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            // $pdfBig->setFontSpacing(0);
            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($arialb, '',10, '', false);
            // $pdfBig->SetXY(11, 55);
            // $pdfBig->MultiCell(188, 7, 'Name : '.$candidate_name, 1, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetFont($arialb, '',10, '', false);
            // $pdfBig->SetXY(11, 62);
            // $pdfBig->MultiCell(188, 7, 'PRN : '.$prnNo, 1, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetFont($arialb, '',10, '', false);
            // $pdfBig->SetXY(11, 69);
            // $pdfBig->MultiCell(188, 7, 'Transcript Issue Date : '.$TransIssueDate, 1, "L", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetFont($arialb, '',10, '', false);
            // $pdfBig->SetXY(11, 76);
            // $pdfBig->MultiCell(188, 7, 'Program : '.$programme, 1, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->setFontSpacing(0);
            // start invisible data
            $pdfBig->SetFont($arialb, '', 8, '', false); 
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->SetXY(76, 53);
            $pdfBig->Cell(0, 0, $candidate_name, 0, false, 'L');
            $pdfBig->SetTextColor(0, 0, 0);
            // end invisible data
            // left first box
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            
            $pdfBig->SetTextColor(0,0,0);
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(11, 58);
            $pdfBig->MultiCell(94, 7, "", 'LTB', "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(15, 59);
            $pdfBig->MultiCell(27, 6, 'Student Name', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(39, 59);
            $pdfBig->MultiCell(5, 6, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(44, 59);
            $pdfBig->MultiCell(56, 6, $candidate_name, 0, "L", 0, 0, '', '', true, 0, true);


            // right first box
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(105, 58);
            $pdfBig->MultiCell(94, 7, "", 'LRTB', "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(109, 59);
            $pdfBig->MultiCell(26, 6, 'PRN', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(135, 59);
            $pdfBig->MultiCell(5, 6, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(140, 59);
            $pdfBig->MultiCell(34, 6, $prnNo, 0, "L", 0, 0, '', '', true, 0, true);

            // start prn no invisible data
            $pdfBig->SetFont($arialb, '', 8, '', false); 
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->SetXY(165, 59.5);
            $pdfBig->Cell(0, 0, $prnNo, 0, false, 'L');
            $pdfBig->SetTextColor(0, 0, 0);
            // end prn no invisible data

            // left second box
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(11, 65);

            $pdfBig->MultiCell(94, 9, "", 'LB', "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(15, 67);
            $pdfBig->MultiCell(39, 8, "Transcript Issue Date", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(54, 67);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(59, 67);
            $pdfBig->MultiCell(50, 8, $TransIssueDate, 0, "L", 0, 0, '', '', true, 0, true);


            // --- Existing Cumulative GPA Block ---
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(105, 65);
            $pdfBig->MultiCell(94, 9, "", 'LRB', "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(109, 67);
            $pdfBig->MultiCell(32, 8, "Cumulative GPA", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(141, 67);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(146, 67);
            $pdfBig->MultiCell(20, 8, $cumulativeGpa, 0, "L", 0, 0, '', '', true, 0, true);


            // --- New Earned Credit Block (to the right) ---
            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(158, 67);  // Adjust X as per width of previous cell
            $pdfBig->MultiCell(32, 8, "Earned Credit", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(185, 67);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(190, 67);
            $pdfBig->MultiCell(20, 8, $earnedCredit, 0, "L", 0, 0, '', '', true, 0, true);


            // third box
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(11, 74);
            $pdfBig->MultiCell(188, 8, "", 'LRB', "L", 0, 0, '', '', true, 0, true);

            
            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(15, 75.5);
            $pdfBig->MultiCell(24, 8, "Programme", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(39, 75.5);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(44, 75.5);
            $pdfBig->MultiCell(121, 8, $programme, 0, "L", 0, 0, '', '', true, 0, true);

            // End Header Box 
            
            $pdfBig->ln();
            

            $tableY = 90;
            $tableLeftY = 90;
            $tableRightY = 90;

            $tableLeftY = $tableRightY = 90;

            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = ''); 
            $pdfBig->SetFont($arialNarrowB, '', 9, '', false);
            $pdfBig->SetXY(11, $tableLeftY);
            $pdfBig->MultiCell(20, 8, 'Course Code', 1, "C", 0, 0);
            $pdfBig->SetXY(31, $tableLeftY);
            $pdfBig->MultiCell(51, 8, 'Course Title', 1, "C", 0, 0);
            $pdfBig->SetXY(82, $tableLeftY);
            $pdfBig->MultiCell(12, 8, 'Credits', 1, "C", 0, 0);
            $pdfBig->SetXY(94, $tableLeftY);
            $pdfBig->MultiCell(11, 8, 'Grade', 1, "C", 0, 0);

            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = ''); 
            $pdfBig->SetFont($arialNarrowB, '', 9, '', false);
            $pdfBig->SetXY(105, $tableRightY);
            $pdfBig->MultiCell(20, 8, 'Course Code', 1, "C", 0, 0);
            $pdfBig->SetXY(125, $tableRightY);
            $pdfBig->MultiCell(51, 8, 'Course Title', 1, "C", 0, 0);
            $pdfBig->SetXY(176, $tableRightY);
            $pdfBig->MultiCell(12, 8, 'Credits', 1, "C", 0, 0);
            $pdfBig->SetXY(188, $tableRightY);
            $pdfBig->MultiCell(11, 8, 'Grade', 1, "C", 0, 0);
            

            $pdfBig->ln();
            
            
            $tableLeftY = $pdfBig->GetY();
            $tableRightY = $pdfBig->GetY();
            

            foreach ($subjectData as $index => $sem) {
                if ($index % 2 == 0) {
                    // LEFT COLUMN
                    $pdfBig->SetFont($arialNarrowB, '', 9, '', false);
                    $pdfBig->SetXY(11, $tableLeftY);
                    $pdfBig->setCellPaddings(1, 0.5, '', '');
                    $pdfBig->MultiCell(94, 5, $sem['semester'], 'LRB', "C", 0, 0);
                    $pdfBig->ln();
                    $tableLeftY = $pdfBig->GetY();
                    $leftBorderY = $tableLeftY;

                    $leftHeight = 0;
                    foreach ($sem['subjects'] as $subject) {
                        $pdfBig->SetFont($arialNarrow, '', 8, '', false);
                        $pdfBig->setCellPaddings(1, 0.8, '', '');

                        $lineHeight = 4;
                        $pdfBig->startTransaction();
                        $lines = $pdfBig->MultiCell(51, 0, trim($subject['course_name']), 0, 'L', 0, 0, '', '', true, 0, false, true, 0);
                        $pdfBig = $pdfBig->rollbackTransaction();
                        if ($lines > 1) $lineHeight = 8;

                        $pdfBig->SetXY(11, $tableLeftY);
                        $pdfBig->MultiCell(20, $lineHeight, $subject['course_code'], 0, "C", 0, 0);
                        $pdfBig->SetXY(31, $tableLeftY);
                        $pdfBig->MultiCell(51, $lineHeight, $subject['course_name'], 0, "L", 0, 0);
                        $pdfBig->SetXY(82, $tableLeftY);
                        $pdfBig->MultiCell(12, $lineHeight, $subject['credit'], 0, "C", 0, 0);
                        $pdfBig->SetXY(94, $tableLeftY);
                        $pdfBig->MultiCell(11, $lineHeight, $subject['grade'], 0, "C", 0, 0);

                        $pdfBig->ln();
                        $tableLeftY = $pdfBig->GetY();
                        $leftHeight += $lineHeight;
                    }
                    $semester1 = $sem['sgpa']; 
                    
                } else {
                    // RIGHT COLUMN
                    $pdfBig->SetFont($arialNarrowB, '', 9, '', false);
                    $pdfBig->SetXY(105, $tableRightY);
                    $pdfBig->setCellPaddings(1, 0.5, '', '');
                    $pdfBig->MultiCell(94, 5, $sem['semester'], 'LRB', "C", 0, 0);
                    $pdfBig->ln();
                    $tableRightY = $pdfBig->GetY();
                    $rightBorderY = $tableRightY;

                    $rightHeight = 0;
                    foreach ($sem['subjects'] as $subject) {
                        $pdfBig->SetFont($arialNarrow, '', 8, '', false);
                        $pdfBig->setCellPaddings(1, 0.8, '', '');

                        $lineHeight = 4;
                        $pdfBig->startTransaction();
                        $lines = $pdfBig->MultiCell(51, 0, trim($subject['course_name']), 0, 'L', 0, 0, '', '', true, 0, false, true, 0);
                        $pdfBig = $pdfBig->rollbackTransaction();
                        if ($lines > 1) $lineHeight = 8;

                        $pdfBig->SetXY(105, $tableRightY);
                        $pdfBig->MultiCell(20, $lineHeight, $subject['course_code'], 0, "C", 0, 0);
                        $pdfBig->SetXY(125, $tableRightY);
                        $pdfBig->MultiCell(51, $lineHeight, $subject['course_name'], 0, "L", 0, 0);
                        $pdfBig->SetXY(176, $tableRightY);
                        $pdfBig->MultiCell(12, $lineHeight, $subject['credit'], 0, "C", 0, 0);
                        $pdfBig->SetXY(188, $tableRightY);
                        $pdfBig->MultiCell(11, $lineHeight, $subject['grade'], 0, "C", 0, 0);

                        $pdfBig->ln();
                        $tableRightY = $pdfBig->GetY();
                        $rightHeight += $lineHeight;
                    }

                     // Sync Y positions for next row
                    $maxY = max($tableLeftY, $tableRightY);
                    $tableLeftY = $maxY +1;
                    $tableRightY = $maxY +1;

                    // Draw right table border
                    
                    // Draw left table border
                    $BorderHeight = $tableLeftY - $leftBorderY;
                    
                    if($BorderHeight < 38) {
                        $tableLeftY = $tableLeftY +(38 -$BorderHeight);
                        $tableRightY = $tableRightY +(38 -$BorderHeight);
                        $BorderHeight = 38;

                    }
                    $semester2 = $sem['sgpa'];
                    

                    
                    
                    
                    $pdfBig->SetXY(11, $leftBorderY);
                    $pdfBig->MultiCell(20, $BorderHeight-4, '', '1', "C", 0, 0);
                    $pdfBig->SetXY(31, $leftBorderY);
                    $pdfBig->MultiCell(51, $BorderHeight-4, '', 1, "C", 0, 0);
                    $pdfBig->SetXY(82, $leftBorderY);
                    $pdfBig->MultiCell(12, $BorderHeight-4, '', 1, "C", 0, 0);
                    $pdfBig->SetXY(94, $leftBorderY);
                    $pdfBig->MultiCell(11, $BorderHeight-4, '', 1, "C", 0, 0);


                    $pdfBig->SetXY(105, $rightBorderY);
                    $pdfBig->MultiCell(20, $BorderHeight-4, '', '1', "C", 0, 0);
                    $pdfBig->SetXY(125, $rightBorderY);
                    $pdfBig->MultiCell(51, $BorderHeight-4, '', 1, "C", 0, 0);
                    $pdfBig->SetXY(176, $rightBorderY);
                    $pdfBig->MultiCell(12, $BorderHeight-4, '', 1, "C", 0, 0);
                    $pdfBig->SetXY(188, $rightBorderY);
                    $pdfBig->MultiCell(11, $BorderHeight-4, '', 1, "C", 0, 0);
                    

                    $pdfBig->setCellPaddings( $left = '1', $top = '0.4', $right = '', $bottom = ''); 
                    $pdfBig->SetFont($arialNarrowB, '',8, '', false);
                    $pdfBig->SetXY(11, $tableLeftY-4);
                    if($semester1) {
                        $pdfBig->MultiCell(94, $lineHeight, 'SGPA : '.$semester1, 'LRTB', "L", 0, 0, '', '', true, 0, true);
                    } else {
                        $pdfBig->MultiCell(94, $lineHeight, '', 'LRBT', "L", 0, 0, '', '', true, 0, true);
                    }

                    $pdfBig->setCellPaddings( $left = '1', $top = '0.4', $right = '', $bottom = ''); 
                    $pdfBig->SetFont($arialNarrowB, '',8, '', false);
                    $pdfBig->SetXY(105, $tableLeftY-4);
                    if($semester2) {
                        $pdfBig->MultiCell(94, $lineHeight, 'SGPA : '.$semester2, 'LRTB', "L", 0, 0, '', '', true, 0, true);
                    } else {
                        $pdfBig->MultiCell(94, $lineHeight, '', 'LRBT', "L", 0, 0, '', '', true, 0, true);
                    }

                   
                }
            }


           
            // $pdfBig->SetFont($arialNarrowB, '',10, '', false);
            // $pdfBig->SetXY(11, $tableLeftY);
            // $pdfBig->MultiCell(150, 0, 'Semester GPA :'.$cumulativeGpa, 0, "L", 0, 0, '', '', true, 0, true);

            //End pdfBig
			
			

            /////////////////////////////////////////////////////////////
			//start pdf
            $pdf->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdf->setFontSpacing(1);
            $pdf->SetFont($arialb, '', 12, '', false);
            $pdf->SetXY(0, 42);
            $pdf->SetTextColor(46,109,164);
            $pdf->Cell(210, 0, 'TRANSCRIPT', 0, false, 'C');

            // Header Box  
            // $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            // $pdf->setFontSpacing(0);
            // $pdf->SetTextColor(0,0,0);
            // $pdf->SetFont($arialb, '',10, '', false);
            // $pdf->SetXY(11, 55);
            // $pdf->MultiCell(188, 7, 'Name : '.$candidate_name, 1, "L", 0, 0, '', '', true, 0, true);

            // $pdf->SetFont($arialb, '',10, '', false);
            // $pdf->SetXY(11, 62);
            // $pdf->MultiCell(188, 7, 'PRN : '.$prnNo, 1, "L", 0, 0, '', '', true, 0, true);

            // $pdf->SetFont($arialb, '',10, '', false);
            // $pdf->SetXY(11, 69);
            // $pdf->MultiCell(188, 7, 'Transcript Issue Date : '.$TransIssueDate, 1, "L", 0, 0, '', '', true, 0, true);


            // $pdf->SetFont($arialb, '',10, '', false);
            // $pdf->SetXY(11, 76);
            // $pdf->MultiCell(188, 7, 'Program : '.$programme, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setFontSpacing(0);
            // start invisible data
            $pdf->SetFont($arialb, '', 8, '', false); 
            $pdf->SetTextColor(255, 255, 0);
            $pdf->SetXY(76, 53);
            $pdf->Cell(0, 0, $candidate_name, 0, false, 'L');
            $pdf->SetTextColor(0, 0, 0);
            // end invisible data
            // left first box
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(11, 58);
            $pdf->MultiCell(94, 7, "", 'LTB', "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(15, 59);
            $pdf->MultiCell(27, 6, 'Student Name', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(39, 59);
            $pdf->MultiCell(5, 6, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(44, 59);
            $pdf->MultiCell(56, 6, $candidate_name, 0, "L", 0, 0, '', '', true, 0, true);


            // right first box
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(105, 58);
            $pdf->MultiCell(94, 7, "", 'LRTB', "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(109, 59);
            $pdf->MultiCell(26, 6, 'PRN', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(135, 59);
            $pdf->MultiCell(5, 6, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(140, 59);
            $pdf->MultiCell(34, 6, $prnNo, 0, "L", 0, 0, '', '', true, 0, true);

            // start prn no invisible data
            $pdf->SetFont($arialb, '', 8, '', false); 
            $pdf->SetTextColor(255, 255, 0);
            $pdf->SetXY(165, 59.5);
            $pdf->Cell(0, 0, $prnNo, 0, false, 'L');
            $pdf->SetTextColor(0, 0, 0);
            // end prn no invisible data

            // left second box
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(11, 65);

            $pdf->MultiCell(94, 9, "", 'LB', "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(15, 67);
            $pdf->MultiCell(39, 8, "Transcript Issue Date", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(54, 67);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(59, 67);
            $pdf->MultiCell(50, 8, $TransIssueDate, 0, "L", 0, 0, '', '', true, 0, true);


            // --- Existing Cumulative GPA Block ---
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(105, 65);
            $pdf->MultiCell(94, 9, "", 'LRB', "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(109, 67);
            $pdf->MultiCell(32, 8, "Cumulative GPA", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(141, 67);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(146, 67);
            $pdf->MultiCell(20, 8, $cumulativeGpa, 0, "L", 0, 0, '', '', true, 0, true);


            // --- New Earned Credit Block (to the right) ---
            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(158, 67);  // Adjust X as per width of previous cell
            $pdf->MultiCell(32, 8, "Earned Credit", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(185, 67);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(190, 67);
            $pdf->MultiCell(20, 8, $earnedCredit, 0, "L", 0, 0, '', '', true, 0, true);

            // third box
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(11, 74);
            $pdf->MultiCell(188, 8, "", 'LRB', "L", 0, 0, '', '', true, 0, true);

            
            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(15, 75.5);
            $pdf->MultiCell(24, 8, "Programme", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(39, 75.5);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(44, 75.5);
            $pdf->MultiCell(121, 8, $programme, 0, "L", 0, 0, '', '', true, 0, true);
            // End Header Box 
            
            $pdf->ln();
            

            $tableY = 90;
            $tableLeftY = 90;
            $tableRightY = 90;

            $tableLeftY = $tableRightY = 90;

            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = ''); 
            $pdf->SetFont($arialNarrowB, '', 9, '', false);
            $pdf->SetXY(11, $tableLeftY);
            $pdf->MultiCell(20, 8, 'Course Code', 1, "C", 0, 0);
            $pdf->SetXY(31, $tableLeftY);
            $pdf->MultiCell(51, 8, 'Course Title', 1, "C", 0, 0);
            $pdf->SetXY(82, $tableLeftY);
            $pdf->MultiCell(12, 8, 'Credits', 1, "C", 0, 0);
            $pdf->SetXY(94, $tableLeftY);
            $pdf->MultiCell(11, 8, 'Grade', 1, "C", 0, 0);

            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = ''); 
            $pdf->SetFont($arialNarrowB, '', 9, '', false);
            $pdf->SetXY(105, $tableRightY);
            $pdf->MultiCell(20, 8, 'Course Code', 1, "C", 0, 0);
            $pdf->SetXY(125, $tableRightY);
            $pdf->MultiCell(51, 8, 'Course Title', 1, "C", 0, 0);
            $pdf->SetXY(176, $tableRightY);
            $pdf->MultiCell(12, 8, 'Credits', 1, "C", 0, 0);
            $pdf->SetXY(188, $tableRightY);
            $pdf->MultiCell(11, 8, 'Grade', 1, "C", 0, 0);
            

            $pdf->ln();
            
            
            $tableLeftY = $pdf->GetY();
            $tableRightY = $pdf->GetY();
            

            foreach ($subjectData as $index => $sem) {
                if ($index % 2 == 0) {
                    // LEFT COLUMN
                    $pdf->SetFont($arialNarrowB, '', 9, '', false);
                    $pdf->SetXY(11, $tableLeftY);
                    $pdf->setCellPaddings(1, 0.5, '', '');
                    $pdf->MultiCell(94, 5, $sem['semester'], 'LRB', "C", 0, 0);
                    $pdf->ln();
                    $tableLeftY = $pdf->GetY();
                    $leftBorderY = $tableLeftY;

                    $leftHeight = 0;
                    foreach ($sem['subjects'] as $subject) {
                        $pdf->SetFont($arialNarrow, '', 8, '', false);
                        $pdf->setCellPaddings(1, 0.8, '', '');

                        $lineHeight = 4;
                        $pdf->startTransaction();
                        $lines = $pdf->MultiCell(51, 0, trim($subject['course_name']), 0, 'L', 0, 0, '', '', true, 0, false, true, 0);
                        $pdf = $pdf->rollbackTransaction();
                        if ($lines > 1) $lineHeight = 8;

                        $pdf->SetXY(11, $tableLeftY);
                        $pdf->MultiCell(20, $lineHeight, $subject['course_code'], 0, "C", 0, 0);
                        $pdf->SetXY(31, $tableLeftY);
                        $pdf->MultiCell(51, $lineHeight, $subject['course_name'], 0, "L", 0, 0);
                        $pdf->SetXY(82, $tableLeftY);
                        $pdf->MultiCell(12, $lineHeight, $subject['credit'], 0, "C", 0, 0);
                        $pdf->SetXY(94, $tableLeftY);
                        $pdf->MultiCell(11, $lineHeight, $subject['grade'], 0, "C", 0, 0);

                        $pdf->ln();
                        $tableLeftY = $pdf->GetY();
                        $leftHeight += $lineHeight;
                    }
                    $semester1 = $sem['sgpa'];
                    
                } else {
                    // RIGHT COLUMN
                    $pdf->SetFont($arialNarrowB, '', 9, '', false);
                    $pdf->SetXY(105, $tableRightY);
                    $pdf->setCellPaddings(1, 0.5, '', '');
                    $pdf->MultiCell(94, 5, $sem['semester'], 'LRB', "C", 0, 0);
                    $pdf->ln();
                    $tableRightY = $pdf->GetY();
                    $rightBorderY = $tableRightY;

                    $rightHeight = 0;
                    foreach ($sem['subjects'] as $subject) {
                        $pdf->SetFont($arialNarrow, '', 8, '', false);
                        $pdf->setCellPaddings(1, 0.8, '', '');

                        $lineHeight = 4;
                        $pdf->startTransaction();
                        $lines = $pdf->MultiCell(51, 0, trim($subject['course_name']), 0, 'L', 0, 0, '', '', true, 0, false, true, 0);
                        $pdf = $pdf->rollbackTransaction();
                        if ($lines > 1) $lineHeight = 8;

                        $pdf->SetXY(105, $tableRightY);
                        $pdf->MultiCell(20, $lineHeight, $subject['course_code'], 0, "C", 0, 0);
                        $pdf->SetXY(125, $tableRightY);
                        $pdf->MultiCell(51, $lineHeight, $subject['course_name'], 0, "L", 0, 0);
                        $pdf->SetXY(176, $tableRightY);
                        $pdf->MultiCell(12, $lineHeight, $subject['credit'], 0, "C", 0, 0);
                        $pdf->SetXY(188, $tableRightY);
                        $pdf->MultiCell(11, $lineHeight, $subject['grade'], 0, "C", 0, 0);

                        $pdf->ln();
                        $tableRightY = $pdf->GetY();
                        $rightHeight += $lineHeight;
                    }

                     // Sync Y positions for next row
                    $maxY = max($tableLeftY, $tableRightY);
                    $tableLeftY = $maxY +0.5;
                    $tableRightY = $maxY +0.5;

                    // Draw right table border
                    
                    // Draw left table border
                    $BorderHeight = $tableLeftY - $leftBorderY;
                    
                    if($BorderHeight < 38) {
                        $tableLeftY = $tableLeftY +(38 -$BorderHeight);
                        $tableRightY = $tableRightY +(38 -$BorderHeight);
                        $BorderHeight = 38;

                    }
                    $semester2 = $sem['sgpa'];
                    

                    
                    
                    
                    $pdf->SetXY(11, $leftBorderY);
                    $pdf->MultiCell(20, $BorderHeight-4, '', '1', "C", 0, 0);
                    $pdf->SetXY(31, $leftBorderY);
                    $pdf->MultiCell(51, $BorderHeight-4, '', 1, "C", 0, 0);
                    $pdf->SetXY(82, $leftBorderY);
                    $pdf->MultiCell(12, $BorderHeight-4, '', 1, "C", 0, 0);
                    $pdf->SetXY(94, $leftBorderY);
                    $pdf->MultiCell(11, $BorderHeight-4, '', 1, "C", 0, 0);


                    $pdf->SetXY(105, $rightBorderY);
                    $pdf->MultiCell(20, $BorderHeight-4, '', '1', "C", 0, 0);
                    $pdf->SetXY(125, $rightBorderY);
                    $pdf->MultiCell(51, $BorderHeight-4, '', 1, "C", 0, 0);
                    $pdf->SetXY(176, $rightBorderY);
                    $pdf->MultiCell(12, $BorderHeight-4, '', 1, "C", 0, 0);
                    $pdf->SetXY(188, $rightBorderY);
                    $pdf->MultiCell(11, $BorderHeight-4, '', 1, "C", 0, 0);
                    

                    $pdf->setCellPaddings( $left = '1', $top = '0.4', $right = '', $bottom = ''); 
                    $pdf->SetFont($arialNarrowB, '',8, '', false);
                    $pdf->SetXY(11, $tableLeftY-4);
                    if($semester1) {
                        $pdf->MultiCell(94, $lineHeight, 'SGPA : '.$semester1, 'LRTB', "L", 0, 0, '', '', true, 0, true);
                    } else {
                        $pdf->MultiCell(94, $lineHeight, '', 'LRBT', "L", 0, 0, '', '', true, 0, true);
                    }

                    $pdf->setCellPaddings( $left = '1', $top = '0.4', $right = '', $bottom = ''); 
                    $pdf->SetFont($arialNarrowB, '',8, '', false);
                    $pdf->SetXY(105, $tableLeftY-4);
                    if($semester2) {
                        $pdf->MultiCell(94, $lineHeight, 'SGPA : '.$semester2, 'LRTB', "L", 0, 0, '', '', true, 0, true);
                    } else {
                        $pdf->MultiCell(94, $lineHeight, '', 'LRBT', "L", 0, 0, '', '', true, 0, true);
                    }

                    // $pdf->SetXY(11, $leftBorderY);
                    // $pdf->MultiCell(20, $BorderHeight, '', '1', "C", 0, 0);
                    // $pdf->SetXY(31, $leftBorderY);
                    // $pdf->MultiCell(51, $BorderHeight, '', 1, "C", 0, 0);
                    // $pdf->SetXY(82, $leftBorderY);
                    // $pdf->MultiCell(12, $BorderHeight, '', 1, "C", 0, 0);
                    // $pdf->SetXY(94, $leftBorderY);
                    // $pdf->MultiCell(11, $BorderHeight, '', 1, "C", 0, 0);


                    // $pdf->SetXY(105, $rightBorderY);
                    // $pdf->MultiCell(20, $BorderHeight, '', '1', "C", 0, 0);
                    // $pdf->SetXY(125, $rightBorderY);
                    // $pdf->MultiCell(51, $BorderHeight, '', 1, "C", 0, 0);
                    // $pdf->SetXY(176, $rightBorderY);
                    // $pdf->MultiCell(12, $BorderHeight, '', 1, "C", 0, 0);
                    // $pdf->SetXY(188, $rightBorderY);
                    // $pdf->MultiCell(11, $BorderHeight, '', 1, "C", 0, 0);

                   
                }
            }


           
            // $pdf->SetFont($arialNarrowB, '',10, '', false);
            // $pdf->SetXY(11, $tableLeftY);
            // $pdf->MultiCell(150, 0, 'Semester GPA :'.$cumulativeGpa, 0, "L", 0, 0, '', '', true, 0, true);

			//end pdf			
			
			// Ghost image
			$nameOrg=$candidate_name;
			// $ghost_font_size = '13';
			// $ghostImagex = 77;
			// $ghostImagey = 269;
			// $ghostImageWidth = 55;
			// $ghostImageHeight = 9.8;	
			

            $ghost_font_size = '11';//13
            $ghostImagex = 77;
            $ghostImagey =272;//85
            $ghostImageWidth = 91.9;
            $ghostImageHeight = 7;//9.8


			/*$ghost_font_size = '12';
            $ghostImagex = 146;
            $ghostImagey = 262;
            $ghostImageWidth = 39.405983333;
            $ghostImageHeight = 8;*/
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
			
            $codeContents = '';
            $codeContents .= 'Name: '.$candidate_name;
            $codeContents .="\n";
            $codeContents .= 'PRN: '.$prnNo;
            $codeContents .="\n";
            $codeContents .= 'Transcript Issue Date: '.$TransIssueDate;
            $codeContents .="\n";
            $codeContents .= 'Programme: '.$programme;
            $codeContents .="\n";
            
            $codeContents .="\n\n".strtoupper(md5($str));
            
            $encryptedString = strtoupper(md5($str));
			$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
			$qrCodex = 90; 
			$qrCodey = 251;
			$qrCodeWidth =18;
			$qrCodeHeight = 18;
			$ecc = 'L';
			$pixel_Size = 1;
			$frame_Size = 1;   
			\PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
			// \QrCode::backgroundColor(255, 255, 0)            
            // ->format('png')        
            // ->size(500)    
            // ->generate($codeContents, $qr_code_path);

            $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);   
			$pdf->setPageMark(); 
			$pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
			$pdfBig->setPageMark(); 			
	
			
			//1D Barcode
			// $style1Da = array(
			// 	'position' => '',
			// 	'align' => 'C',
			// 	'stretch' => true,
			// 	'fitwidth' => true,
			// 	'cellfitalign' => '',
			// 	'border' => false,
			// 	'hpadding' => 'auto',
			// 	'vpadding' => 'auto',
			// 	'fgcolor' => array(0,0,0),
			// 	'bgcolor' => false, //array(255,255,255),
			// 	'text' => true,
			// 	'font' => 'helvetica',
			// 	'fontsize' => 9,
			// 	'stretchtext' => 7
			// ); 
			
			// $barcodex = 8;
			// $barcodey = 9;
			// $barcodeWidth = 50;
			// $barodeHeight = 15;
			// $pdf->SetAlpha(1);
			// $pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			// $pdfBig->SetAlpha(1);
			// $pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			// // $pdf->SetFont($arial, '', 9, '', false);
			// // $pdf->SetXY(8, 18);
            // // $pdf->MultiCell(50, 0, trim($print_serial_no), 0, 'C', 0, 0);
			
			$str = $nameOrg;
			$str = strtoupper(preg_replace('/\s+/', '', $str)); 
			
			$microlinestr=$str;
			$pdf->SetFont($arialb, '', 2, '', false);
			$pdf->SetTextColor(0, 0, 0);
			//$pdf->StartTransform();
			$pdf->SetXY(90, 269);        
			$pdf->Cell(18, 0, $microlinestr, 0, false, 'C');     
			
			$pdfBig->SetFont($arialb, '', 2, '', false);
			$pdfBig->SetTextColor(0, 0, 0);
			//$pdf->StartTransform();
			$pdfBig->SetXY(90, 269);        
			$pdfBig->Cell(18, 0, $microlinestr, 0, false, 'C'); 
 



            //Microline
            $fullStr =$candidate_name.' '.$prnNo.' '.$programme;
			$microlineEnrollment1=$fullStr;
			//$microlineEnrollment1 = preg_replace('/\s+/', '', $microlineEnrollment1);
			$textArrayEnrollment1 = imagettfbbox(1.2, 0, public_path().'/'.$subdomain[0].'/backend/canvas/fonts/Arialb.TTF', $microlineEnrollment1);
			$strWidthEnrollment1 = ($textArrayEnrollment1[2] - $textArrayEnrollment1[0]);
			$strHeightEnrollment1 = $textArrayEnrollment1[6] - $textArrayEnrollment1[1] / 1.2;
			$latestWidthEnrollment1 = 1200;
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
			$pdfBig->SetFont($arial, '', 8, '', false);
			$pdfBig->SetTextColor(0, 0, 0, 10);
            $pdfBig->StartTransform();

            $pdf->SetFont($arial, '', 8, '', false);
			$pdf->SetTextColor(0, 0, 0, 10);
            $pdf->StartTransform();

            
			$xClip=5;
			$yClip=278;
			$wClip=198.9;
			$hClip=6;
			$pdfBig->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');
			$pdf->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');

			$pdfBig->SetXY(5, 278.5);
			$pdfBig->Cell(198.9, 0, $arrayEnrollment1, 0, false, 'L');
			$pdfBig->StopTransform();

            $pdf->SetXY(5, 278.5);
			$pdf->Cell(198.9, 0, $arrayEnrollment1, 0, false, 'L');
			$pdf->StopTransform();

            //Microline

			if($previewPdf!=1){

				$certName = str_replace("/", "_", $GUID) .".pdf";
				
				$myPath = public_path().'/backend/temp_pdf_file';

				$fileVerificationPath=$myPath . DIRECTORY_SEPARATOR . $certName;

				$pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');

				 $this->addCertificate($serial_no, $certName, $dt,$template_id,$admin_id,$merging_type,$verification_bg);

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
            $this->updateCardNo('GradeCard',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
        }
        $msg = '';
        
        $file_name =  str_replace("/", "_",'SaiuProvisionalTran'.date("Ymdhms")).'.pdf';
        
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();


        $filename = public_path().'/backend/tcpdf/examples/'.$file_name;
        
        $pdfBig->output($filename,'F');

        if($previewPdf!=1){
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name="SaiuProvisionalTran";
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

    public function addCertificate($serial_no, $certName, $dt,$template_id,$admin_id,$merging_type,$verification_bg)
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
        echo $merging_type.", ".$verification_bg;
        $result = SbStudentTable::create(['serial_no'=>'T-'.$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'merging_type'=>$merging_type,'verification_bg'=>$verification_bg]);
        }else{
        $resultu = StudentTableMerge::where('serial_no',''.$serial_no)->update(['status'=>'0']);
        // Insert the new record
        
        $result = StudentTableMerge::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2,'merging_type'=>$merging_type,'verification_bg'=>$verification_bg]);
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
        $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>$sr_no,'template_name'=>$template_name,'card_serial_no'=>$card_serial_no,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);    
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
