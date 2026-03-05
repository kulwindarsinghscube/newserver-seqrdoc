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
use App\Jobs\SendMailJob;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use Illuminate\Support\Facades\Redis;
use App\models\ThirdPartyRequests;
use App\Helpers\CoreHelper;
use Helper;

class PdfGenerateSaiuGradeJob
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
			$candidate_name = trim($studentData[1]);
			$prnNo = trim($studentData[2]);
			$monthYearOfPassing = trim($studentData[3]);
			$doi = trim($studentData[4]);
			$programme = trim($studentData[5]);
            $Code1 = trim($studentData[6]);
            $CourseTitle1 = trim($studentData[7]);
            $Grade1 = trim($studentData[8]);
            $Credits1 = trim($studentData[9]);
            $Code2 = trim($studentData[10]);
            $CourseTitle2 = trim($studentData[11]);
            $Grade2 = trim($studentData[12]);
            $Credits2 = trim($studentData[13]);
            $Code3 = trim($studentData[14]);
            $CourseTitle3 = trim($studentData[15]);
            $Grade3 = trim($studentData[16]);
            $Credits3 = trim($studentData[17]);
            $Code4 = trim($studentData[18]);
            $CourseTitle4 = trim($studentData[19]);
            $Grade4 = trim($studentData[20]);
            $Credits4 = trim($studentData[21]);
            $Code5 = trim($studentData[22]);
            $CourseTitle5 = trim($studentData[23]);
            $Grade5 = trim($studentData[24]);
            $Credits5 = trim($studentData[25]);

            $Code21 = trim($studentData[26]);
            $CourseTitle21 = trim($studentData[27]);
            $Grade21 = trim($studentData[28]);
            $Credits21 = trim($studentData[29]);
            $Code22 = trim($studentData[30]);
            $CourseTitle22 = trim($studentData[31]);
            $Grade22 = trim($studentData[32]);
            $Credits22 = trim($studentData[33]);
            $Code23 = trim($studentData[34]);
            $CourseTitle23 = trim($studentData[35]);
            $Grade23 = trim($studentData[36]);
            $Credits23 = trim($studentData[37]);
            $Code24 = trim($studentData[38]);
            $CourseTitle24 = trim($studentData[39]);
            $Grade24 = trim($studentData[40]);
            $Credits24 = trim($studentData[41]);
            $Code25 = trim($studentData[42]);
            $CourseTitle25 = trim($studentData[43]);
            $Grade25 = trim($studentData[44]);
            $Credits25 = trim($studentData[45]);

            
            $Code31 = trim($studentData[46]);
            $CourseTitle31 = trim($studentData[47]);
            $Grade31 = trim($studentData[48]);
            $Credits31 = trim($studentData[49]);
            $Code32 = trim($studentData[50]);
            $CourseTitle32 = trim($studentData[51]);
            $Grade32 = trim($studentData[52]);
            $Credits32 = trim($studentData[53]);
            $Code33 = trim($studentData[54]);
            $CourseTitle33 = trim($studentData[55]);
            $Grade33 = trim($studentData[56]);
            $Credits33 = trim($studentData[57]);
            $Code34 = trim($studentData[58]);
            $CourseTitle34 = trim($studentData[59]);
            $Grade34 = trim($studentData[60]);
            $Credits34 = trim($studentData[61]);
            $Code35 = trim($studentData[62]);
            $CourseTitle35 = trim($studentData[63]);
            $Grade35 = trim($studentData[64]);
            $Credits35 = trim($studentData[65]);


            $COMMCode1 = trim($studentData[66]);
            $COMMCourseTitle1 = trim($studentData[67]);
            $COMMGrade1 = trim($studentData[68]);
            $COMMCode2 = trim($studentData[69]);
            $COMMCourseTitle2 = trim($studentData[70]);
            $COMMGrade2 = trim($studentData[71]);
            $COMMCode3 = trim($studentData[72]);
            $COMMCourseTitle3 = trim($studentData[73]);
            $COMMGrade3 = trim($studentData[74]);
            $Grade3STATUS = trim($studentData[75]);

            $WWBLCODE1 = trim($studentData[76]);
            $WWBLCourseTitle1 = trim($studentData[77]);
            $WWBLGrade1 = trim($studentData[78]);

            $WWBLCode2 = trim($studentData[79]);
            $WWBLCourseTitle2 = trim($studentData[80]);
            $WWBLGrade2 = trim($studentData[81]);

            $WWBLCode3 = trim($studentData[82]);
            $WWBLCourseTitle3 = trim($studentData[83]);
            $WWBLGrade3 = trim($studentData[84]);
            $WWBLGradeSTATUS = trim($studentData[85]);


            $TGPA1 = sprintf('%0.2f', trim($studentData[86]));
            $CreditsEarned1 =trim($studentData[87]);
            $TGPA2 = sprintf('%0.2f', trim($studentData[88]));
            $CreditsEarned2 = trim($studentData[89]);
            $TGPA3 = sprintf('%0.2f', trim($studentData[90]));
            $CreditsEarned3 = trim($studentData[91]);
            $TotalCreditsEarned = trim($studentData[92]);
            $CGPA = sprintf('%0.2f', trim($studentData[93]))."/10";
            $Result = trim($studentData[95]);
            $DeanSignature = trim($studentData[95]);
            $COESignature = trim($studentData[96]);
            $Lab1Title = trim($studentData[97]);
            $Lab2Title = trim($studentData[98]);
            $serial_no_card = trim($studentData[99]);

			
            
            //Start pdfBig
            $pdfBig->setCellPaddings( $left = '1', $top = '0', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '', 11, '', false); 
            $pdfBig->SetTextColor(0, 0, 0);    
            $pdfBig->SetXY(0, 7);
            $pdfBig->Cell(204, 0, $serial_no_card, 0, false, 'R');//unique_id

            $pdfBig->SetFont($arialb, '', 12, '', false);
            $pdfBig->SetXY(0, 42);
            $pdfBig->Cell(210, 0, 'SCHOOL OF LAW', 0, false, 'C');

            $pdfBig->SetFont($arialb, '', 12, '', false);
            $pdfBig->SetXY(0, 48);
            $pdfBig->Cell(210, 0, 'CUMULATIVE GRADE REPORT', 0, false, 'C');
            
            // start invisible data
            $pdfBig->SetFont($arialb, '', 8, '', false); 
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->SetXY(76, 53);
            $pdfBig->Cell(0, 0, $candidate_name, 0, false, 'L');
            $pdfBig->SetTextColor(0, 0, 0);
            // end invisible data

            // left first box
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(17, 58);
            $pdfBig->MultiCell(88, 7, "", 'LTB', "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(21, 59);
            $pdfBig->MultiCell(29, 8, 'Name', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(50, 59);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(55, 59);
            $pdfBig->MultiCell(50, 8, $candidate_name, 0, "L", 0, 0, '', '', true, 0, true);


            // right first box
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(105, 58);
            $pdfBig->MultiCell(88, 7, "", 'LRTB', "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(109, 59);
            $pdfBig->MultiCell(26, 8, 'PRN', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(135, 59);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(140, 59);
            $pdfBig->MultiCell(30, 8, $prnNo, 0, "L", 0, 0, '', '', true, 0, true);

            // start prn no invisible data
            $pdfBig->SetFont($arialb, '', 8, '', false); 
            $pdfBig->SetTextColor(255, 255, 0);
            $pdfBig->SetXY(165, 59.5);
            $pdfBig->Cell(0, 0, $prnNo, 0, false, 'L');
            $pdfBig->SetTextColor(0, 0, 0);
            // end prn no invisible data

            // left second box
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(17, 65);

            $pdfBig->MultiCell(88, 9, "", 'LB', "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(21, 65);
            $pdfBig->MultiCell(29, 8, "Month & <br>Year of Passing", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(50, 67);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(55, 67);
            $pdfBig->MultiCell(50, 8, $monthYearOfPassing, 0, "L", 0, 0, '', '', true, 0, true);


            // right second box
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(105, 65);
            $pdfBig->MultiCell(88, 9, "", 'LRB', "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(109, 67);
            $pdfBig->MultiCell(26, 8, "Date Of Issue", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(135, 67);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(140, 67);
            $pdfBig->MultiCell(50, 8, $doi, 0, "L", 0, 0, '', '', true, 0, true);

            // third box
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(17, 74);
            $pdfBig->MultiCell(176, 8, "", 'LRB', "L", 0, 0, '', '', true, 0, true);

            
            $pdfBig->SetFont($arialb, '',10, '', false);
            $pdfBig->SetXY(21, 75.5);
            $pdfBig->MultiCell(29, 8, "Programme", 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(50, 75.5);
            $pdfBig->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdfBig->SetFont($arial, '',10, '', false);
            $pdfBig->SetXY(55, 75.5);
            $pdfBig->MultiCell(115, 8, $programme, 0, "L", 0, 0, '', '', true, 0, true);

            //  Term I
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',7, '', false);
            $pdfBig->SetXY(11, 91);
            $pdfBig->MultiCell(90, 4, 'TERM - I', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 95);
            $pdfBig->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 95);
            $pdfBig->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 95);
            $pdfBig->MultiCell(9, 4, 'Grade', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 95);
            $pdfBig->MultiCell(10, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 99);
            $pdfBig->MultiCell(17, 6, $Code1, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 99);
            $pdfBig->MultiCell(54, 6, $CourseTitle1, 1, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 99);
            $pdfBig->MultiCell(9, 6, $Grade1, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 99);
            $pdfBig->MultiCell(10, 6, $Credits1, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 105);
            $pdfBig->MultiCell(17, 6, $Code2, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 105);
            $pdfBig->MultiCell(54, 6, $CourseTitle2, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '1.5', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 105);
            $pdfBig->MultiCell(9, 6, $Grade2, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 105);
            $pdfBig->MultiCell(10, 6, $Credits2, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 111);
            $pdfBig->MultiCell(17, 6, $Code3, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 111);
            $pdfBig->MultiCell(54, 6, $CourseTitle3, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '1', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 111);
            $pdfBig->MultiCell(9, 6, $Grade3, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 111);
            $pdfBig->MultiCell(10, 6, $Credits3, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 117);
            $pdfBig->MultiCell(17, 6, $Code4, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 117);
            $pdfBig->MultiCell(54, 6, $CourseTitle4, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '1', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 117);
            $pdfBig->MultiCell(9, 6, $Grade4, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 117);
            $pdfBig->MultiCell(10, 6, $Credits4, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 123);
            $pdfBig->MultiCell(17, 6, $Code5, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 123);
            $pdfBig->MultiCell(54, 6, $CourseTitle5, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '1.5', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 123);
            $pdfBig->MultiCell(9, 6, $Grade5, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 123);
            $pdfBig->MultiCell(10, 6, $Credits5, 1, "C", 0, 0, '', '', true, 0, true);



            //  Term II
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',7, '', false);
            $pdfBig->SetXY(109, 91);
            $pdfBig->MultiCell(90, 4, 'TERM - II', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 95);
            $pdfBig->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 95);
            $pdfBig->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 95);
            $pdfBig->MultiCell(9, 4, 'Grade', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(189, 95);
            $pdfBig->MultiCell(10, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 99);
            $pdfBig->MultiCell(17, 6, $Code21, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 99);
            $pdfBig->MultiCell(54, 6, $CourseTitle21, 1, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 99);
            $pdfBig->MultiCell(9, 6, $Grade21, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(189, 99);
            $pdfBig->MultiCell(10, 6, $Credits21, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 105);
            $pdfBig->MultiCell(17, 6, $Code22, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 105);
            $pdfBig->MultiCell(54, 6, $CourseTitle22, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 105);
            $pdfBig->MultiCell(9, 6, $Grade22, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(189, 105);
            $pdfBig->MultiCell(10, 6, $Credits22, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 111);
            $pdfBig->MultiCell(17, 6, $Code23, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 111);
            $pdfBig->MultiCell(54, 6, $CourseTitle23, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 111);
            $pdfBig->MultiCell(9, 6, $Grade23, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(189, 111);
            $pdfBig->MultiCell(10, 6, $Credits23, 1, "C", 0, 0, '', '', true, 0, true);

           $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 117);
            $pdfBig->MultiCell(17, 6, $Code24, 1, "C", 0, 0, '', '', true, 0, true);

           $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 117);
            $pdfBig->MultiCell(54, 6, $CourseTitle24, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 117);
            $pdfBig->MultiCell(9, 6, $Grade24, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(189, 117);
            $pdfBig->MultiCell(10, 6, $Credits24, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 123);
            $pdfBig->MultiCell(17, 6, $Code25, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 123);
            $pdfBig->MultiCell(54, 6, $CourseTitle25, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 123);
            $pdfBig->MultiCell(9, 6, $Grade25, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(189, 123);
            $pdfBig->MultiCell(10, 6, $Credits25, 1, "C", 0, 0, '', '', true, 0, true);




            //  Term III
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',7, '', false);
            $pdfBig->SetXY(11, 133);
            $pdfBig->MultiCell(90, 4, 'TERM - III', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 137);
            $pdfBig->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 137);
            $pdfBig->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 137);
            $pdfBig->MultiCell(9, 4, 'Grade', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 137);
            $pdfBig->MultiCell(10, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 141);
            $pdfBig->MultiCell(17, 6, $Code31, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 141);
            $pdfBig->MultiCell(54, 6, $CourseTitle31, 1, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 141);
            $pdfBig->MultiCell(9, 6, $Grade31, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 141);
            $pdfBig->MultiCell(10, 6, $Credits31, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 147);
            $pdfBig->MultiCell(17, 6, $Code32, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 147);
            $pdfBig->MultiCell(54, 6, $CourseTitle32, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 147);
            $pdfBig->MultiCell(9, 6, $Grade32, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 147);
            $pdfBig->MultiCell(10, 6, $Credits32, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 153);
            $pdfBig->MultiCell(17, 6, $Code33, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 153);
            $pdfBig->MultiCell(54, 6, $CourseTitle33, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 153);
            $pdfBig->MultiCell(9, 6, $Grade33, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 153);
            $pdfBig->MultiCell(10, 6, $Credits33, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 159);
            $pdfBig->MultiCell(17, 6, $Code34, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 159);
            $pdfBig->MultiCell(54, 6, $CourseTitle34, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 159);
            $pdfBig->MultiCell(9, 6, $Grade34, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 159);
            $pdfBig->MultiCell(10, 6, $Credits34, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 165);
            $pdfBig->MultiCell(17, 6, $Code35, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 165);
            $pdfBig->MultiCell(54, 6, $CourseTitle35, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 165);
            $pdfBig->MultiCell(9, 6, $Grade35, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(91, 165);
            $pdfBig->MultiCell(10, 6, $Credits35, 1, "C", 0, 0, '', '', true, 0, true);


            
            

            //  LAB 1
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',7, '', false);
            $pdfBig->SetXY(109, 133);
            $pdfBig->MultiCell(90, 4, $Lab1Title, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 137);
            $pdfBig->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 137);
            $pdfBig->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');


            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 137);
            $pdfBig->MultiCell(19, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);

            

            if(!empty($COMMCourseTitle1)){

            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 141);
            $pdfBig->MultiCell(17, 7, $COMMCode1, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 141);
            $pdfBig->MultiCell(54, 7, $COMMCourseTitle1, 1, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 141);
            $pdfBig->MultiCell(19, 7, $COMMGrade1, 1, "C", 0, 0, '', '', true, 0, true);

                $lab1Y=148;
            }else{
                
                $lab1Y=141;
            
            }


            if(!empty($COMMCourseTitle2)){

            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 148);
            $pdfBig->MultiCell(17, 7, $COMMCode2, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 148);
            $pdfBig->MultiCell(54, 7, $COMMCourseTitle2, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 148);
            $pdfBig->MultiCell(19, 7, $COMMGrade2, 1, "C", 0, 0, '', '', true, 0, true);

                $lab1Y=155;
            }

            if(!empty($COMMCourseTitle3)){

            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(109, 155);
            $pdfBig->MultiCell(17, 7, $COMMCode3, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(126, 155);
            $pdfBig->MultiCell(54, 7, $COMMCourseTitle3, 1, "L", 0, 0, '', '', true, 0, true);
            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(180, 155);
            $pdfBig->MultiCell(19, 7, $COMMGrade3, 1, "C", 0, 0, '', '', true, 0, true);
        
             $lab1Y=162;
            }

            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',7, '', false);
            $pdfBig->SetXY(180, $lab1Y);
            $pdfBig->MultiCell(19, 7, $Grade3STATUS, 1, "C", 0, 0, '', '', true, 0, true);



            //  LAB 2
            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',7, '', false);
            $pdfBig->SetXY(11, 175);
            $pdfBig->MultiCell(90, 4, $Lab2Title, 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(11, 179);
            $pdfBig->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(28, 179);
            $pdfBig->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($arial, '',7, '', false);
            $pdfBig->SetXY(82, 179);
            $pdfBig->MultiCell(19, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);


            if(!empty($WWBLCourseTitle1)){

                $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(11, 183);
                $pdfBig->MultiCell(17, 7, $WWBLCODE1, 1, "C", 0, 0, '', '', true, 0, true);

                $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(28, 183);
                $pdfBig->MultiCell(54, 7, $WWBLCourseTitle1, 1, "L", 0, 0, '', '', true, 0, true);

                $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(82, 183);
                $pdfBig->MultiCell(19, 7, $WWBLGrade1, 1, "C", 0, 0, '', '', true, 0, true);
            
                $lab2Y=190;
            }else{
                $lab2Y=183;
            }
            
            if(!empty($WWBLCourseTitle2)){
                $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(11, 190);
                $pdfBig->MultiCell(17, 7, $WWBLCode2, 1, "C", 0, 0, '', '', true, 0, true);

                $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(28, 190);
                $pdfBig->MultiCell(54, 7, $WWBLCourseTitle2, 1, "L", 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(82, 190);
                $pdfBig->MultiCell(19, 7, $WWBLGrade2, 1, "C", 0, 0, '', '', true, 0, true);

                $lab2Y=197;
            }


            if(!empty($WWBLCourseTitle3)){
                $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(11, 197);
                $pdfBig->MultiCell(17, 7, $WWBLCode3, 1, "C", 0, 0, '', '', true, 0, true);

                $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(28, 197);
                $pdfBig->MultiCell(54, 7, $WWBLCourseTitle3, 1, "L", 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

                $pdfBig->SetFont($arial, '',7, '', false);
                $pdfBig->SetXY(82, 197);
                $pdfBig->MultiCell(19, 7, $WWBLGrade3, 1, "C", 0, 0, '', '', true, 0, true);
                    
                $lab2Y=204;
            }

            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',7, '', false);
            $pdfBig->SetXY(82, $lab2Y);
            $pdfBig->MultiCell(19, 7, $WWBLGradeSTATUS, 1, "C", 0, 0, '', '', true, 0, true);

            
            
            // bottom box 
            $pdfBig->setCellPaddings( $left = '2', $top = '3', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(31, 223);
            $pdfBig->MultiCell(27, 9, 'TERM - I', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(31, 232);
            $pdfBig->MultiCell(27, 8, 'TGPA', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(31, 240);
            $pdfBig->MultiCell(27, 7, $TGPA1, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '2', $top = '5.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(58, 223);
            $pdfBig->MultiCell(13, 17, 'Credits<br>Earned', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(58, 240);
            $pdfBig->MultiCell(13, 7, $CreditsEarned1, 1, "C", 0, 0, '', '', true, 0, true);



            $pdfBig->setCellPaddings( $left = '2', $top = '3', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(71, 223);
            $pdfBig->MultiCell(27, 9, 'TERM - II', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(71, 232);
            $pdfBig->MultiCell(27, 8, 'TGPA', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(71, 240);
            $pdfBig->MultiCell(27, 7, $TGPA2, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '2', $top = '5.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(98, 223);
            $pdfBig->MultiCell(13, 17, 'Credits<br>Earned', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(98, 240);
            $pdfBig->MultiCell(13, 7, $CreditsEarned2, 1, "C", 0, 0, '', '', true, 0, true);



            $pdfBig->setCellPaddings( $left = '2', $top = '3', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(111, 223);
            $pdfBig->MultiCell(27, 9, 'TERM - III', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(111, 232);
            $pdfBig->MultiCell(27, 8, 'TGPA', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(111, 240);
            $pdfBig->MultiCell(27, 7, $TGPA3, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '2', $top = '5.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(138, 223);
            $pdfBig->MultiCell(13, 17, 'Credits<br>Earned', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(138, 240);
            $pdfBig->MultiCell(13, 7, $CreditsEarned3, 1, "C", 0, 0, '', '', true, 0, true);



            $pdfBig->setCellPaddings( $left = '2', $top = '2.5', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(151, 223);
            $pdfBig->MultiCell(14, 17, 'Total<br>Credits<br>Earned', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(151, 240);
            $pdfBig->MultiCell(14, 7, $TotalCreditsEarned, 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '2', $top = '7', $right = '', $bottom = '');
            $pdfBig->SetFont($arialb, '',8, '', false);
            $pdfBig->SetXY(165, 223);
            $pdfBig->MultiCell(16, 17, 'CGPA', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdfBig->SetFont($arial, '',8, '', false);
            $pdfBig->SetXY(165, 240);
            $pdfBig->MultiCell(16, 7, $CGPA, 1, "C", 0, 0, '', '', true, 0, true);
            //End pdfBig
			
			
			//start pdf
            $pdf->setCellPaddings( $left = '1', $top = '0', $right = '', $bottom = '');
            $pdf->SetFont($arial, '', 11, '', false); 
            $pdf->SetTextColor(0, 0, 0);    
            $pdf->SetXY(0, 7);
            $pdf->Cell(204, 0, $serial_no_card, 0, false, 'R');//unique_id

            $pdf->SetFont($arialb, '', 12, '', false);
            $pdf->SetXY(0, 42);
            $pdf->Cell(210, 0, 'SCHOOL OF LAW', 0, false, 'C');

            $pdf->SetFont($arialb, '', 12, '', false);
            $pdf->SetXY(0, 48);
            $pdf->Cell(210, 0, 'CUMULATIVE GRADE REPORT', 0, false, 'C');
            
            // start invisible data
            $pdf->SetFont($arialb, '', 8, '', false); 
            $pdf->SetTextColor(255, 255, 0);
            $pdf->SetXY(76, 53);
            $pdf->Cell(0, 0, $candidate_name, 0, false, 'L');
            $pdf->SetTextColor(0, 0, 0);
            // end invisible data

            // left first box
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(17, 58);
            $pdf->MultiCell(88, 7, "", 'LTB', "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(21, 59);
            $pdf->MultiCell(29, 8, 'Name', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(50, 59);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(55, 59);
            $pdf->MultiCell(50, 8, $candidate_name, 0, "L", 0, 0, '', '', true, 0, true);


            // right first box
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(105, 58);
            $pdf->MultiCell(88, 7, "", 'LRTB', "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(109, 59);
            $pdf->MultiCell(26, 8, 'PRN', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(135, 59);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(140, 59);
            $pdf->MultiCell(30, 8, $prnNo, 0, "L", 0, 0, '', '', true, 0, true);

            // start prn no invisible data
            $pdf->SetFont($arialb, '', 8, '', false); 
            $pdf->SetTextColor(255, 255, 0);
            $pdf->SetXY(165, 59.5);
            $pdf->Cell(0, 0, $prnNo, 0, false, 'L');
            $pdf->SetTextColor(0, 0, 0);
            // end prn no invisible data

            // left second box
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(17, 65);

            $pdf->MultiCell(88, 9, "", 'LB', "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(21, 65);
            $pdf->MultiCell(29, 8, "Month & <br>Year of Passing", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(50, 67);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(55, 67);
            $pdf->MultiCell(50, 8, $monthYearOfPassing, 0, "L", 0, 0, '', '', true, 0, true);


            // right second box
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(105, 65);
            $pdf->MultiCell(88, 9, "", 'LRB', "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(109, 67);
            $pdf->MultiCell(26, 8, "Date Of Issue", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(135, 67);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(140, 67);
            $pdf->MultiCell(50, 8, $doi, 0, "L", 0, 0, '', '', true, 0, true);

            // third box
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(17, 74);
            $pdf->MultiCell(176, 8, "", 'LRB', "L", 0, 0, '', '', true, 0, true);

            
            $pdf->SetFont($arialb, '',10, '', false);
            $pdf->SetXY(21, 75.5);
            $pdf->MultiCell(29, 8, "Programme", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(50, 75.5);
            $pdf->MultiCell(5, 8, ':', 0, "L", 0, 0, '', '', true, 0, true);
            
            $pdf->SetFont($arial, '',10, '', false);
            $pdf->SetXY(55, 75.5);
            $pdf->MultiCell(115, 8, $programme, 0, "L", 0, 0, '', '', true, 0, true);

            //  Term I
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',7, '', false);
            $pdf->SetXY(11, 91);
            $pdf->MultiCell(90, 4, 'TERM - I', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 95);
            $pdf->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 95);
            $pdf->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 95);
            $pdf->MultiCell(9, 4, 'Grade', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 95);
            $pdf->MultiCell(10, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 99);
            $pdf->MultiCell(17, 6, $Code1, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 99);
            $pdf->MultiCell(54, 6, $CourseTitle1, 1, "L", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 99);
            $pdf->MultiCell(9, 6, $Grade1, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 99);
            $pdf->MultiCell(10, 6, $Credits1, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 105);
            $pdf->MultiCell(17, 6, $Code2, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 105);
            $pdf->MultiCell(54, 6, $CourseTitle2, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '1.5', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 105);
            $pdf->MultiCell(9, 6, $Grade2, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 105);
            $pdf->MultiCell(10, 6, $Credits2, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 111);
            $pdf->MultiCell(17, 6, $Code3, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 111);
            $pdf->MultiCell(54, 6, $CourseTitle3, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '1', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 111);
            $pdf->MultiCell(9, 6, $Grade3, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 111);
            $pdf->MultiCell(10, 6, $Credits3, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 117);
            $pdf->MultiCell(17, 6, $Code4, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 117);
            $pdf->MultiCell(54, 6, $CourseTitle4, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '1', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 117);
            $pdf->MultiCell(9, 6, $Grade4, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 117);
            $pdf->MultiCell(10, 6, $Credits4, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 123);
            $pdf->MultiCell(17, 6, $Code5, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 123);
            $pdf->MultiCell(54, 6, $CourseTitle5, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '1.5', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 123);
            $pdf->MultiCell(9, 6, $Grade5, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 123);
            $pdf->MultiCell(10, 6, $Credits5, 1, "C", 0, 0, '', '', true, 0, true);



            //  Term II
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',7, '', false);
            $pdf->SetXY(109, 91);
            $pdf->MultiCell(90, 4, 'TERM - II', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 95);
            $pdf->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 95);
            $pdf->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 95);
            $pdf->MultiCell(9, 4, 'Grade', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(189, 95);
            $pdf->MultiCell(10, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 99);
            $pdf->MultiCell(17, 6, $Code21, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 99);
            $pdf->MultiCell(54, 6, $CourseTitle21, 1, "L", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 99);
            $pdf->MultiCell(9, 6, $Grade21, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(189, 99);
            $pdf->MultiCell(10, 6, $Credits21, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 105);
            $pdf->MultiCell(17, 6, $Code22, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 105);
            $pdf->MultiCell(54, 6, $CourseTitle22, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 105);
            $pdf->MultiCell(9, 6, $Grade22, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(189, 105);
            $pdf->MultiCell(10, 6, $Credits22, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 111);
            $pdf->MultiCell(17, 6, $Code23, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 111);
            $pdf->MultiCell(54, 6, $CourseTitle23, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 111);
            $pdf->MultiCell(9, 6, $Grade23, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(189, 111);
            $pdf->MultiCell(10, 6, $Credits23, 1, "C", 0, 0, '', '', true, 0, true);

           $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 117);
            $pdf->MultiCell(17, 6, $Code24, 1, "C", 0, 0, '', '', true, 0, true);

           $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 117);
            $pdf->MultiCell(54, 6, $CourseTitle24, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 117);
            $pdf->MultiCell(9, 6, $Grade24, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(189, 117);
            $pdf->MultiCell(10, 6, $Credits24, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 123);
            $pdf->MultiCell(17, 6, $Code25, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 123);
            $pdf->MultiCell(54, 6, $CourseTitle25, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 123);
            $pdf->MultiCell(9, 6, $Grade25, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(189, 123);
            $pdf->MultiCell(10, 6, $Credits25, 1, "C", 0, 0, '', '', true, 0, true);




            //  Term III
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',7, '', false);
            $pdf->SetXY(11, 133);
            $pdf->MultiCell(90, 4, 'TERM - III', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 137);
            $pdf->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 137);
            $pdf->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 137);
            $pdf->MultiCell(9, 4, 'Grade', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 137);
            $pdf->MultiCell(10, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 141);
            $pdf->MultiCell(17, 6, $Code31, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 141);
            $pdf->MultiCell(54, 6, $CourseTitle31, 1, "L", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 141);
            $pdf->MultiCell(9, 6, $Grade31, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 141);
            $pdf->MultiCell(10, 6, $Credits31, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 147);
            $pdf->MultiCell(17, 6, $Code32, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 147);
            $pdf->MultiCell(54, 6, $CourseTitle32, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 147);
            $pdf->MultiCell(9, 6, $Grade32, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 147);
            $pdf->MultiCell(10, 6, $Credits32, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 153);
            $pdf->MultiCell(17, 6, $Code33, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 153);
            $pdf->MultiCell(54, 6, $CourseTitle33, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 153);
            $pdf->MultiCell(9, 6, $Grade33, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 153);
            $pdf->MultiCell(10, 6, $Credits33, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 159);
            $pdf->MultiCell(17, 6, $Code34, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 159);
            $pdf->MultiCell(54, 6, $CourseTitle34, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 159);
            $pdf->MultiCell(9, 6, $Grade34, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 159);
            $pdf->MultiCell(10, 6, $Credits34, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 165);
            $pdf->MultiCell(17, 6, $Code35, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '1.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 165);
            $pdf->MultiCell(54, 6, $CourseTitle35, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 165);
            $pdf->MultiCell(9, 6, $Grade35, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(91, 165);
            $pdf->MultiCell(10, 6, $Credits35, 1, "C", 0, 0, '', '', true, 0, true);


            
            

            //  LAB 1
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',7, '', false);
            $pdf->SetXY(109, 133);
            $pdf->MultiCell(90, 4, $Lab1Title, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 137);
            $pdf->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 137);
            $pdf->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');


            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 137);
            $pdf->MultiCell(19, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);

            

            if(!empty($COMMCourseTitle1)){

            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 141);
            $pdf->MultiCell(17, 7, $COMMCode1, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 141);
            $pdf->MultiCell(54, 7, $COMMCourseTitle1, 1, "L", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 141);
            $pdf->MultiCell(19, 7, $COMMGrade1, 1, "C", 0, 0, '', '', true, 0, true);

                $lab1Y=148;
            }else{
                
                $lab1Y=141;
            
            }


            if(!empty($COMMCourseTitle2)){

            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 148);
            $pdf->MultiCell(17, 7, $COMMCode2, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 148);
            $pdf->MultiCell(54, 7, $COMMCourseTitle2, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 148);
            $pdf->MultiCell(19, 7, $COMMGrade2, 1, "C", 0, 0, '', '', true, 0, true);

                $lab1Y=155;
            }

            if(!empty($COMMCourseTitle3)){

            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(109, 155);
            $pdf->MultiCell(17, 7, $COMMCode3, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(126, 155);
            $pdf->MultiCell(54, 7, $COMMCourseTitle3, 1, "L", 0, 0, '', '', true, 0, true);
            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(180, 155);
            $pdf->MultiCell(19, 7, $COMMGrade3, 1, "C", 0, 0, '', '', true, 0, true);
        
             $lab1Y=162;
            }

            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',7, '', false);
            $pdf->SetXY(180, $lab1Y);
            $pdf->MultiCell(19, 7, $Grade3STATUS, 1, "C", 0, 0, '', '', true, 0, true);



            //  LAB 2
            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',7, '', false);
            $pdf->SetXY(11, 175);
            $pdf->MultiCell(90, 4, $Lab2Title, 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(11, 179);
            $pdf->MultiCell(17, 4, 'Code', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '1', $top = '0.5', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(28, 179);
            $pdf->MultiCell(54, 4, 'COURSE TITLE', 1, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($arial, '',7, '', false);
            $pdf->SetXY(82, 179);
            $pdf->MultiCell(19, 4, 'Credits', 1, "C", 0, 0, '', '', true, 0, true);


            if(!empty($WWBLCourseTitle1)){

                $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(11, 183);
                $pdf->MultiCell(17, 7, $WWBLCODE1, 1, "C", 0, 0, '', '', true, 0, true);

                $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(28, 183);
                $pdf->MultiCell(54, 7, $WWBLCourseTitle1, 1, "L", 0, 0, '', '', true, 0, true);

                $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(82, 183);
                $pdf->MultiCell(19, 7, $WWBLGrade1, 1, "C", 0, 0, '', '', true, 0, true);
            
                $lab2Y=190;
            }else{
                $lab2Y=183;
            }
            
            if(!empty($WWBLCourseTitle2)){
                $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(11, 190);
                $pdf->MultiCell(17, 7, $WWBLCode2, 1, "C", 0, 0, '', '', true, 0, true);

                $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(28, 190);
                $pdf->MultiCell(54, 7, $WWBLCourseTitle2, 1, "L", 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(82, 190);
                $pdf->MultiCell(19, 7, $WWBLGrade2, 1, "C", 0, 0, '', '', true, 0, true);

                $lab2Y=197;
            }


            if(!empty($WWBLCourseTitle3)){
                $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(11, 197);
                $pdf->MultiCell(17, 7, $WWBLCode3, 1, "C", 0, 0, '', '', true, 0, true);

                $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(28, 197);
                $pdf->MultiCell(54, 7, $WWBLCourseTitle3, 1, "L", 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');

                $pdf->SetFont($arial, '',7, '', false);
                $pdf->SetXY(82, 197);
                $pdf->MultiCell(19, 7, $WWBLGrade3, 1, "C", 0, 0, '', '', true, 0, true);
                    
                $lab2Y=204;
            }

            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',7, '', false);
            $pdf->SetXY(82, $lab2Y);
            $pdf->MultiCell(19, 7, $WWBLGradeSTATUS, 1, "C", 0, 0, '', '', true, 0, true);

            
            
            // bottom box 
            $pdf->setCellPaddings( $left = '2', $top = '3', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(31, 223);
            $pdf->MultiCell(27, 9, 'TERM - I', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(31, 232);
            $pdf->MultiCell(27, 8, 'TGPA', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(31, 240);
            $pdf->MultiCell(27, 7, $TGPA1, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '2', $top = '5.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(58, 223);
            $pdf->MultiCell(13, 17, 'Credits<br>Earned', 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(58, 240);
            $pdf->MultiCell(13, 7, $CreditsEarned1, 1, "C", 0, 0, '', '', true, 0, true);



            $pdf->setCellPaddings( $left = '2', $top = '3', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(71, 223);
            $pdf->MultiCell(27, 9, 'TERM - II', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(71, 232);
            $pdf->MultiCell(27, 8, 'TGPA', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(71, 240);
            $pdf->MultiCell(27, 7, $TGPA2, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '2', $top = '5.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(98, 223);
            $pdf->MultiCell(13, 17, 'Credits<br>Earned', 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(98, 240);
            $pdf->MultiCell(13, 7, $CreditsEarned2, 1, "C", 0, 0, '', '', true, 0, true);



            $pdf->setCellPaddings( $left = '2', $top = '3', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(111, 223);
            $pdf->MultiCell(27, 9, 'TERM - III', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(111, 232);
            $pdf->MultiCell(27, 8, 'TGPA', 1, "C", 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings( $left = '2', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(111, 240);
            $pdf->MultiCell(27, 7, $TGPA3, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '2', $top = '5.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(138, 223);
            $pdf->MultiCell(13, 17, 'Credits<br>Earned', 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(138, 240);
            $pdf->MultiCell(13, 7, $CreditsEarned3, 1, "C", 0, 0, '', '', true, 0, true);



            $pdf->setCellPaddings( $left = '2', $top = '2.5', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(151, 223);
            $pdf->MultiCell(14, 17, 'Total<br>Credits<br>Earned', 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(151, 240);
            $pdf->MultiCell(14, 7, $TotalCreditsEarned, 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '2', $top = '7', $right = '', $bottom = '');
            $pdf->SetFont($arialb, '',8, '', false);
            $pdf->SetXY(165, 223);
            $pdf->MultiCell(16, 17, 'CGPA', 1, "C", 0, 0, '', '', true, 0, true);


            $pdf->setCellPaddings( $left = '1', $top = '2', $right = '', $bottom = '');
            $pdf->SetFont($arial, '',8, '', false);
            $pdf->SetXY(165, 240);
            $pdf->MultiCell(16, 7, $CGPA, 1, "C", 0, 0, '', '', true, 0, true);
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
            $ghostImagey =271;//85
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
			// $codeContents =$encryptedString = strtoupper(md5($str));
            $codeContents = '';
            $codeContents .= 'Name: '.$candidate_name;
            $codeContents .="\n";
            $codeContents .= 'PRN: '.$prnNo;
            $codeContents .="\n";
            $codeContents .= 'Month & Year of Passing: '.$monthYearOfPassing;
            $codeContents .="\n";
            $codeContents .= 'Date Of Issue: '.$doi;
            $codeContents .="\n";
            $codeContents .= 'Programme: '.$programme;
            $codeContents .="\n";
            
            $codeContents .="\n\n".strtoupper(md5($str));

            $encryptedString = strtoupper(md5($str));
            
			$qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
			$qrCodex = 185; 
			$qrCodey = 16;
			$qrCodeWidth =18;
			$qrCodeHeight = 18;
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
			
			$barcodex = 8;
			$barcodey = 9;
			$barcodeWidth = 50;
			$barodeHeight = 15;
			$pdf->SetAlpha(1);
			$pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			$pdfBig->SetAlpha(1);
			$pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
			// $pdfBig->SetFont($arial, '', 9, '', false);
			// $pdfBig->SetXY(8, 18);
            // $pdfBig->MultiCell(50, 0, trim($print_serial_no), 0, 'C', 0, 0);
			
			$str = $nameOrg;
			$str = strtoupper(preg_replace('/\s+/', '', $str)); 
			
			$microlinestr=$str;
			$pdf->SetFont($arialb, '', 2, '', false);
			$pdf->SetTextColor(0, 0, 0);
			//$pdf->StartTransform();
			$pdf->SetXY(185, 34);        
			$pdf->Cell(20, 0, $microlinestr, 0, false, 'C');     
			
			$pdfBig->SetFont($arialb, '', 2, '', false);
			$pdfBig->SetTextColor(0, 0, 0);
			//$pdfBig->StartTransform();
			$pdfBig->SetXY(185, 34);        
			$pdfBig->Cell(20, 0, $microlinestr, 0, false, 'C'); 



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
        
        $file_name =  str_replace("/", "_",'SaiuDegree'.date("Ymdhms")).'.pdf';
        
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();


        $filename = public_path().'/backend/tcpdf/examples/'.$file_name;
        
        $pdfBig->output($filename,'F');

        if($previewPdf!=1){
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);
            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name="SaiuDegree";
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
