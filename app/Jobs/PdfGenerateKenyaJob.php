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

class PdfGenerateKenyaJob
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
        ini_set('memory_limit', '4096M');

        $pdf_data = $this->pdf_data;
        $studentDataOrg=$pdf_data['studentDataOrg'];
        $template_id=$pdf_data['template_id'];
        $previewPdf=$pdf_data['previewPdf'];
        $excelfile=$pdf_data['excelfile'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $previewWithoutBg=$previewPdf[1];
        $previewPdf=$previewPdf[0];

        /*echo "<pre>";
        print_r($studentDataOrg);
        exit;*/

        
        if(isset($pdf_data['generation_from']) && $pdf_data['generation_from']=='API'){
        
            $admin_id=$pdf_data['admin_id'];
        }else{
            $admin_id = \Auth::guard('admin')->user()->toArray();  
        }
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $systemConfig = SystemConfig::select('sandboxing','printer_name')->where('site_id',$auth_site_id)->first();
        $printer_name = $systemConfig['printer_name'];
 
        $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdfBig->SetCreator(PDF_CREATOR);
        $pdfBig->SetAuthor('TCPDF');
        $pdfBig->SetTitle('Certificate');
        $pdfBig->SetSubject('');

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);


        //set fonts
        $Times_New_Roman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $K101 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\K101.ttf', 'TrueTypeUnicode', '', 96);
        $K100 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\K100.ttf', 'TrueTypeUnicode', '', 96);
        $Kruti_Dev_730k = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Kruti Dev 730k.ttf', 'TrueTypeUnicode', '', 96);
        $MICR_B10 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MICR-B10.ttf', 'TrueTypeUnicode', '', 96);
        $OLD_ENG1 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\OLD_ENG1.ttf', 'TrueTypeUnicode', '', 96);
        $OLD_ENGL = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\OLD_ENGL.ttf', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\times.ttf', 'TrueTypeUnicode', '', 96);
        $timesbd = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbd.ttf', 'TrueTypeUnicode', '', 96);
        $timesbi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbi.ttf', 'TrueTypeUnicode', '', 96);
        $timesi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesi.ttf', 'TrueTypeUnicode', '', 96);
        $Arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $ArialB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);

        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.ttf', 'TrueTypeUnicode', '', 96);


        $log_serial_no = 1;
        //$cardDetails=$this->getNextCardNo('KENYA-C');
        //$card_serial_no=$cardDetails->next_serial_no;
        
        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\Kenya_Education_Certificate_BG_1.jpg'; 
        $fontEBPath = public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\E-13B_0.php';
        $style1D = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );  

        $signature_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Vice Chancellor.png';

        $generated_documents=0;  
        foreach ($studentDataOrg as $studentData) 
        {
            $card_serial_no = $studentData[0];
            //For Custom Loader
             $startTimeLoader =  date('Y-m-d H:i:s');
         
             $pdfBig->AddPage();
            
             //set background image
               
             if($previewPdf==1){
                if($previewWithoutBg!=1){
                    $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
                }
             }
            $pdfBig->setPageMark();
         
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
            //set background image
            //$template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\Kenya_Education_Certificate_BG_1.jpg';
            if($previewPdf!=1){
                $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);

            }
            $pdf->setPageMark();

            $print_serial_no = $this->nextPrintSerial();
            // $print_serial_no = '';
            
            $pdfBig->SetTextColor(0,0,0);
            $pdf->SetTextColor(0,0,0);

            
            // //Serial NO
            // $x = 165;
            // $y = 11;   
            // $pdf->SetFont($times, '', 10, '', false);
            // $pdf->SetXY($x, $y);
            // $pdf->Cell(0, 0, "Serial No. :", 0, false, 'L');

            // $pdfBig->SetFont($times, '', 10, '', false);
            // $pdfBig->SetXY($x, $y);
            // $pdfBig->Cell(0, 0, "Serial No. :", 0, false, 'L');



            $serialNo = $studentData[0];
            $indexNo = $studentData[1];
            $sex = $studentData[2];
            $name = $studentData[3];
            $subCode1 = $studentData[4];
            $subGrade1 = $studentData[5];
            $subCode2 = $studentData[6];
            $subGrade2 = $studentData[7];
            $subCode3 = $studentData[8];
            $subGrade3 = $studentData[9];
            $subCode4 = $studentData[10];
            $subGrade4 = $studentData[11];
            $subCode5 = $studentData[12];
            $subGrade5 = $studentData[13];
            $subCode6 = $studentData[14];
            $subGrade6 = $studentData[15];
            $subCode7 = $studentData[16];
            $subGrade7 = $studentData[17];
            $subCode8 = $studentData[18];
            $subGrade8 = $studentData[19];
            $subCode9 = $studentData[20];
            $subGrade9 = $studentData[21];
            $mgDesc = $studentData[22];
            $aggregate = $studentData[23];
            $ent = $studentData[24];
            $code = $studentData[25];
            $school = $studentData[26];
            $resyear = $studentData[27];
            $status = $studentData[28];
            $photo = $studentData[29];

            
            $profile_image = public_path().'\\'.$subdomain[0].'\backend\images\kenya-certificate\\'.$photo;
            $profilex = 87;
            $profiley = 91;
            $profileWidth = 35;
            $profileHeight = 43;
            $pdf->image($profile_image,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);
            $pdfBig->image($profile_image,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);



            $strName = $name;
            $strName = str_replace(' ', '', $strName);

            // count of string
            $strNameLength = strlen($strName);

            // for each concat string in one line
            $oneLineLoopCount = 98 / $strNameLength;
            $oneLineLoopCount = ceil($oneLineLoopCount);
            // add string in multiple times
            $newString= "";
            for ($x = 0; $x <= $oneLineLoopCount; $x++) {
                $newString .= $strName;
            }
            
            $totalLopChar = 2842;
            // for Loop multiple lines count 
            $muliptleLineCount = $totalLopChar / $strNameLength;
            $muliptleLineCount = ceil($muliptleLineCount);

            $fullString= "";
            for ($x = 0; $x <= $muliptleLineCount; $x++) {
                $fullString .= $strName;
            }


            $fullStringNew = substr($fullString,0,2842);
            // echo $fullStringNew;
            
            $chunk_length = 98;
            $output = str_split($fullStringNew, $chunk_length);
            $strArry = [];
            foreach ($output as $x => $val) {
                $strArry[] =  $val;
                
            }

            //clipping
            $xClip=16.5;
            $yClip=137.8;
            $wClip=175;
            $hClip=83.5;
                    // Start clipping.      
            $pdf->StartTransform();
            $pdfBig->StartTransform();

            // // Draw clipping rectangle to match html cell.
            $pdf->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');
            $pdfBig->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');

            $k=0;
            for($q=0;$q<3;$q++){

                for($p=0;$p<10;$p++){
                    $str1 = substr($strArry[$p],0,40);
                    $str2 = substr($strArry[$p],40,40);
                    $str3 = substr($strArry[$p],80,18);
                    //ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567
                    // if($p==0){
                    //     $str1="ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCD";
                    //     $str2="EFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGH";
                    //     $str3="IJKLMNOPQRSTUVWXYZ";
                    //     // $str1="WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW";
                    //     // $str2="WWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW";
                    //     // $str3="WWWWWWWWWWWWWWWWWWWWWWWWW";
                    // }else if($p==1){
                    //     $str1="67890ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
                    //     $str2="0ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABC";
                    //     $str3="DEFGHIJKLMNOPQRST";
                    // }else if($p==2){
                    //     $str1="1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ1234";
                    //     $str2="567890ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678";
                    //     $str3="90ABCDEFGHIJKLMNOPQ";
                    // }else if($p==3){
                    //     $str1="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
                    //     $str2="Z1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ123";
                    //     $str3="4567890ABCDEFGHIJK";
                    // }else if($p==4){
                    //     $str1="QRSTUVWXZ1234567890ABCDEFGHIJKLMNOPQRSTU";
                    //     $str2="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
                    //     $str3="Z1234567890ABCDEFG";
                    // }else if($p==5){
                    //     $str1="ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABCD";
                    //     $str2="EFGHIJKLMNOPQRSTUVWXYZ1234567890ABCDEFGH";
                    //     $str3="IJKLMNOPQRSTUVWXYZ";
                    // }else if($p==6){
                    //     $str1="67890ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
                    //     $str2="0ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ABC";
                    //     $str3="DEFGHIJKLMNOPQRSTU";
                    // }else if($p==7){
                    //     $str1="1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ1234";
                    //     $str2="567890ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678";
                    //     $str3="90ABCDEFGHIJKLMNOP";
                    // }else if($p==8){
                    //     $str1="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
                    //     $str2="Z1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ123";
                    //     $str3="4567890ABCDEFGHIJK";
                    // }else if($p==9){
                    //     $str1="QRSTUVWXZ1234567890ABCDEFGHIJKLMNOPQRSTU";
                    //     $str2="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
                    //     $str3="Z1234567890ABCDEFG";
                    // }else if($p==10){
                    //     $str1="QRSTUVWXZ1234567890ABCDEFGHIJKLMNOPQRSTU";
                    //     $str2="VWXYZ1234567890ABCDEFGHIJKLMNOPQRSTUVWXY";
                    //     $str3="Z1234567890ABCDEF";
                    // }

                    //AAAAAAAAAAAAAAAAAWAARKGHADSAAZAZAZAZAZAZ
                    $text = strtoupper($str1);//KANROFATHHMUAAKWREKANROFATHHMUAAKWREKANROFATHHMUAAKWRE
                    
                    // Font size and color
                    $fontSize = 7;
                    // $fontColor = imagecolorallocate($image, 0, 0, 0);

                    // Font path (change to the path of your desired TTF font file)
                    $fontPath = public_path().'/demo/backend/canvas/fonts/Arial.ttf';
                    $imageHeight = 297;
                    // Horizontal and vertical offsets for the sine wave effect
                    $amplitude = 3; // Adjust this value to control the amplitude of the sine wave
                    $period = 150;//100   // Adjust this value to control the frequency of the sine wave
                    $offsetX = 16.5;
                    $offsetY = 134+$k;
                    $customX=0;
                    
                    $pdf->SetTextColor(255,255,255);
                    $pdf->SetFont($arialNarrowB, 'B', $fontSize, '', false);

                    $pdfBig->SetTextColor(255,255,255);
                    $pdfBig->SetFont($arialNarrowB, 'B', $fontSize, '', false);
                    // Loop through each character in the text
                    for ($i = 0; $i < strlen($text); $i++) {
                        $char = $text[$i];

                        // Calculate the X and Y position for the current character
                        $x = $i * $fontSize * 0.25;//1.5
                        $y = $offsetY + $amplitude * sin(6 * M_PI * $x / $period);

                        if($i==0){
                            $customX =0.5;
                        }
                        if($i==1){
                            $customX =0;
                            $y=$y-0.2;

                        }
                        
                        if($i==2){
                            $customX =-0.1;
                            $y=$y-0.7;

                        }

                        if($i==3){
                            $customX =-0.1;
                            $y=$y-1.3;

                        }
                        
                        if($i==4){
                            $customX =-0.1;
                            $y=$y-1.8;

                        }

                        if($i==5){
                            $customX =-0.8;
                            $y=$y-2.1;

                        }

                        if($i==6){
                            $customX =-1;
                            $y=$y-2.4;

                        }

                        if($i==7){
                            $customX =-1;
                            $y=$y-2.8;
                        }

                        if($i==8){
                            $customX =-1.4;
                            $y=$y-2.9;
                        }

                        if($i==9){
                            $customX =-1.6;
                            $y=$y-3;
                        }

                        if($i==10){
                            $customX =-1.8;
                            $y=$y-3;
                        }


                        if($i==11){
                            $customX =-2;
                            $y=$y-2.8;
                        }

                        if($i==12){
                            $customX =-2;
                            $y=$y-2.8;
                        }

                        if($i==13){
                            $customX =-2;
                            $y=$y-2.7;
                        }

                        if($i==14){
                            $customX =-1.8;
                            $y=$y-2.65;

                        }


                        if($i==15){
                            $customX =-1.7;
                            $y=$y-2.55;
                        }
                        if($i==16){
                            $customX =-0.9;
                            $y=$y-2.7;
                        }
                        if($i==17){
                            
                            $customX =-0.8;
                            $y=$y-2.4;
                        }

                        if($i==18){
                            $customX =-0.3;
                            $y=$y-2.4;
                        }

                        if($i==19){
                            $customX =0.2;
                            $y=$y-2.2;
                        }

                        if($i==20){
                            $customX =0.7;
                            
                            $y=$y-2.1;
                        }

                        if($i==21){
                            $customX =0.7;
                        
                            $y=$y-1.9;
                        }

                        if($i==22){
                            $customX =1.2;
                            $y=$y-1.9;
                        }

                        if($i==23){
                            
                            $customX =1.2;
                            $y=$y-1.9;
                        }


                        if($i==24){
                            $customX =1.4;
                            $y=$y-1.9;
                        }

                        if($i==25){
                            $customX =1.4;
                            $y=$y-2.1;
                        }

                        if($i==26){
                            //$pdfBig->SetTextColor(169,169,169);
                            $customX =2.2;
                            $y=$y-2.5;
                        }

                        if($i==27){
                            $customX =2.1;
                            $y=$y-2.8;
                        }

                        if($i==28){
                            $customX =1.9;
                            $y=$y-3;
                        }


                        if($i==29){
                            $customX =1.8;
                            $y=$y-3.3;
                        }

                        if($i==30){
                            $customX =1.5;
                            $y=$y-3.7;
                        }


                        if($i==31){
                            $customX =1.6;
                            $y=$y-3.8;
                        }

                        if($i==32){
                            $customX =1.2;
                            $y=$y-4;
                        }
                        

                        if($i==33){
                            $customX =1.3;
                            $y=$y-4.1;
                        }
                        

                        if($i==34){
                            $customX =1.2;
                            $y=$y-4.1;
                        }

                        if($i==35){
                            $customX =1.5;
                            $y=$y-3.9;
                        }
                        

                        if($i==36){
                            $customX =1.4;
                            $y=$y-3.7;
                        }
                        
                        if($i==37){
                            $customX =1.7;
                            $y=$y-3.3;
                        }

                        if($i==38){
                            $customX =1.6;
                            $y=$y-3;
                        }

                        if($i==39){
                            $customX =1.7;
                            $y=$y-2.4;
                        }

                        // if($i==40){
                        //     $customX =1.6;
                        //     $y=$y-1.8;
                        // }

                        //  if($i==41){
                        //     $customX =1.7;
                        //     $y=$y-2.4;
                        // }

                        // if($i==42){
                        //     $customX =1.6;
                        //     $y=$y-1.8;
                        // }
                        


                        $pdf->SetXY($x + $offsetX+ $customX,$y);
                        $pdfBig->SetXY($x + $offsetX+ $customX,$y);

                        $pdf->StartTransform();
                        $pdfBig->StartTransform();

                        
                        if($i>0){
                            if($i==5){
                                $rotateAngle =8;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }
                            if($i==6){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==7){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==8){
                                $rotateAngle =14;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==9){
                                $rotateAngle =16;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==10){
                                $rotateAngle =18;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==11){
                                $rotateAngle =20;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==12){
                                $rotateAngle =19;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==13){
                                $rotateAngle =20;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==14){
                                $rotateAngle =19;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==15){
                                $rotateAngle =18;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==16){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==17){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==18){
                                $rotateAngle =5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==19){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i>19){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i>21){
                                $rotateAngle =4;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==26){
                                $rotateAngle =9;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                            if($i==27){
                                $rotateAngle =8;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                            if($i==28){
                                $rotateAngle =6;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==29){
                                $rotateAngle =4;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==30){
                                $rotateAngle =3;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==31){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==32){
                                $rotateAngle =1;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==33){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==34){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==35){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==36){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==37){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==38){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==39){
                                $rotateAngle =0;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                        }else{
                            $rotateAngle =$i+5;
                            $pdf->Rotate(-$rotateAngle); 
                            $pdfBig->Rotate(-$rotateAngle); 
                        }
                        $pdf->Cell(210, 10, $char, 0, false, 'L');
                        $pdfBig->Cell(210, 10, $char, 0, false, 'L');
                        $pdf->StopTransform();
                        $pdfBig->StopTransform();
                    
                    }


                    $text = strtoupper($str2);//KANROFATHHMUAAKWREKANROFATHHMUAAKWREKANROFATHHMUAAKWRE

                    // Font size and color
                    $fontSize = 7;
                    // $fontColor = imagecolorallocate($image, 0, 0, 0);

                    // Font path (change to the path of your desired TTF font file)
                    $fontPath = public_path().'/demo/backend/canvas/fonts/Arial.ttf';
                    $imageHeight = 297;
                    // Horizontal and vertical offsets for the sine wave effect
                    $amplitude = 3; // Adjust this value to control the amplitude of the sine wave
                    $period = 150;//100   // Adjust this value to control the frequency of the sine wave
                    $offsetX = 88.3;
                    $offsetY = 133.8+$k;
                    $customX=0;
                    //$pdfBig->SetTextColor(0, 0, 0);
                    // $pdfBig->SetTextColor(255,255,255);
                    // $pdfBig->SetFont($arial, 'B', $fontSize, '', false);
                    // Loop through each character in the text
                    for ($i = 0; $i < strlen($text); $i++) {
                        $char = $text[$i];

                        // Calculate the X and Y position for the current character
                        $x = $i * $fontSize * 0.25;//1.5
                        $y = $offsetY + $amplitude * sin(6 * M_PI * $x / $period);

                        if($i==0){
                            $customX =0.5;
                        }
                        if($i==1){
                            $customX =-0.1;
                            $y=$y-0.2;

                        }
                        
                        if($i==2){
                            $customX =-0.1;
                            $y=$y-0.7;

                        }

                        if($i==3){
                            $customX =-0.1;
                            $y=$y-1.2;

                        }
                        
                        if($i==4){
                            $customX =-0.1;
                            $y=$y-1.9;

                        }

                        if($i==5){
                            $customX =-0.8;
                            $y=$y-2.1;

                        }

                        if($i==6){
                            $customX =-1;
                            $y=$y-2.6;

                        }

                        if($i==7){
                            $customX =-1;
                            $y=$y-2.8;
                        }

                        if($i==8){
                            $customX =-1.6;
                            $y=$y-2.9;
                        }

                        if($i==9){
                            $customX =-2;
                            $y=$y-3;
                        }

                        if($i==10){
                            $customX =-2;
                            $y=$y-3;
                        }


                        if($i==11){
                            $customX =-2;
                            $y=$y-2.8;
                        }

                        if($i==12){
                            $customX =-2;
                            $y=$y-3;
                        }

                        if($i==13){
                            $customX =-2;
                            $y=$y-2.9;
                        }

                        if($i==14){
                            $customX =-1.8;
                            $y=$y-2.8;
                        }


                        if($i==15){
                            $customX =-1.7;
                            $y=$y-2.6;
                        }
                        if($i==16){
                            $customX =-0.9;
                            $y=$y-2.6;
                        }
                        if($i==17){
                            $customX =-1;
                            $y=$y-2.4;
                        }

                        if($i==18){
                            $customX =0;
                            $y=$y-2.4;
                        }

                        if($i==19){
                            $customX =0.5;
                            $y=$y-2.2;
                        }

                        if($i==20){
                            $customX =1;
                            $y=$y-2;
                        }

                        if($i==21){
                            $customX =1;
                            $y=$y-1.9;
                        }

                        if($i==22){
                            $customX =1;
                            $y=$y-1.9;
                        }

                        if($i==23){
                            $customX =1.4;
                            $y=$y-1.9;
                        }


                        if($i==24){
                            $customX =1.4;
                            $y=$y-1.9;
                        }

                        if($i==25){
                            $customX =1.4;
                            $y=$y-2.1;
                        }

                        if($i==26){
                            $customX =2.2;
                            $y=$y-2.4;
                        }

                        if($i==27){
                            $customX =2;
                            $y=$y-2.8;
                        }

                        if($i==28){
                            $customX =1.9;
                            $y=$y-3;
                        }


                        if($i==29){
                            $customX =1.8;
                            $y=$y-3.3;
                        }

                        if($i==30){
                            $customX =1.5;
                            $y=$y-3.7;
                        }


                        if($i==31){
                            $customX =1.6;
                            $y=$y-3.8;
                        }

                        if($i==32){
                            $customX =1.2;
                            $y=$y-4;
                        }
                        

                        if($i==33){
                            $customX =1.3;
                            $y=$y-4.1;
                        }
                        

                        if($i==34){
                            $customX =1.2;
                            $y=$y-4.1;
                        }

                        if($i==35){
                            $customX =1.5;
                            $y=$y-3.9;
                        }
                        

                        if($i==36){
                            $customX =1.4;
                            $y=$y-3.7;
                        }
                        
                        if($i==37){
                            $customX =1.7;
                            $y=$y-3.3;
                        }

                        if($i==38){
                            $customX =1.6;
                            $y=$y-3;
                        }

                        if($i==39){
                            $customX =1.7;
                            $y=$y-2.4;
                        }

                        // if($i==40){
                        //     $customX =1.6;
                        //     $y=$y-1.8;
                        // }

                        //  if($i==41){
                        //     $customX =1.7;
                        //     $y=$y-2.4;
                        // }

                        // if($i==42){
                        //     $customX =1.6;
                        //     $y=$y-1.8;
                        // }
                        


                        $pdf->SetXY($x + $offsetX+ $customX,$y);
                        $pdfBig->SetXY($x + $offsetX+ $customX,$y);

                        $pdf->StartTransform();
                        $pdfBig->StartTransform();

                        if($i>0){

                            if($i==5){
                                $rotateAngle =8;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }
                            if($i==6){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==7){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==8){
                                $rotateAngle =14;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==9){
                                $rotateAngle =16;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==10){
                                $rotateAngle =18;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==11){
                                $rotateAngle =20;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==12){
                                $rotateAngle =19;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==13){
                                $rotateAngle =20;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==14){
                                $rotateAngle =19;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==15){
                                $rotateAngle =18;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==16){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==17){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==18){
                                $rotateAngle =5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==19){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i>19){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i>21){
                                $rotateAngle =4;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==26){
                                $rotateAngle =9;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                            if($i==27){
                                $rotateAngle =8;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                            if($i==28){
                                $rotateAngle =6;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==29){
                                $rotateAngle =4;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==30){
                                $rotateAngle =3;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==31){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==32){
                                $rotateAngle =1;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==33){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==34){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==35){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==36){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==37){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==38){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==39){
                                $rotateAngle =0;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                        }else{
                            $rotateAngle =$i+5;
                            $pdf->Rotate($rotateAngle);
                            $pdfBig->Rotate(-$rotateAngle); 
                        }
                        $pdf->Cell(210, 10, $char, 0, false, 'L');
                        $pdfBig->Cell(210, 10, $char, 0, false, 'L');
                        $pdf->StopTransform();
                        $pdfBig->StopTransform();
                    
                    }


                    $text = strtoupper($str3);//KANROFATHHMUAAKWREKANROFATHHMUAAKWREKANROFATHHMUAAKWRE

                    // Font size and color
                    $fontSize = 7;
                    // $fontColor = imagecolorallocate($image, 0, 0, 0);

                    // Font path (change to the path of your desired TTF font file)
                    $fontPath = public_path().'/demo/backend/canvas/fonts/Arial.ttf';
                    $imageHeight = 297;
                    // Horizontal and vertical offsets for the sine wave effect
                    $amplitude = 3; // Adjust this value to control the amplitude of the sine wave
                    $period = 150;//100   // Adjust this value to control the frequency of the sine wave
                    $offsetX = 160;
                    $offsetY = 133.8+$k;
                    $customX=0;
                    //$pdfBig->SetTextColor(0, 0, 0);
                    //$pdfBig->SetTextColor(255,255,255);
                    // $pdfBig->SetFont($arial, 'B', $fontSize, '', false);
                    // Loop through each character in the text
                    for ($i = 0; $i < strlen($text); $i++) {
                        $char = $text[$i];

                        // Calculate the X and Y position for the current character
                        $x = $i * $fontSize * 0.25;//1.5
                        $y = $offsetY + $amplitude * sin(6 * M_PI * $x / $period);

                        if($i==0){
                            $customX =0.5;
                        }
                        if($i==1){
                            $customX =-0.1;
                            $y=$y-0.2;

                        }
                        
                        if($i==2){
                            $customX =-0.1;
                            $y=$y-0.7;

                        }

                        if($i==3){
                            $customX =-0.1;
                            $y=$y-1.2;

                        }
                        
                        if($i==4){
                            $customX =-0.1;
                            $y=$y-1.9;

                        }

                        if($i==5){
                            $customX =-0.8;
                            $y=$y-2.1;

                        }

                        if($i==6){
                            $customX =-1;
                            $y=$y-2.6;

                        }

                        if($i==7){
                            $customX =-1;
                            $y=$y-2.8;
                        }

                        if($i==8){
                            $customX =-1.6;
                            $y=$y-2.9;
                        }

                        if($i==9){
                            $customX =-2;
                            $y=$y-3;
                        }

                        if($i==10){
                            $customX =-2;
                            $y=$y-3;
                        }


                        if($i==11){
                            $customX =-2;
                            $y=$y-2.8;
                        }

                        if($i==12){
                            $customX =-2;
                            $y=$y-3;
                        }

                        if($i==13){
                            $customX =-2;
                            $y=$y-2.9;
                        }

                        if($i==14){
                            $customX =-1.8;
                            $y=$y-2.8;
                        }


                        if($i==15){
                            $customX =-1.7;
                            $y=$y-2.6;
                        }
                        if($i==16){
                            $customX =-0.9;
                            $y=$y-2.6;
                        }
                        if($i==17){
                            $customX =-1;
                            $y=$y-2.4;
                        }

                        if($i==18){
                            $customX =0;
                            $y=$y-2.4;
                        }

                        if($i==19){
                            $customX =0.5;
                            $y=$y-2.2;
                        }

                        if($i==20){
                            $customX =1;
                            $y=$y-2;
                        }

                        if($i==21){
                            $customX =1;
                            $y=$y-1.9;
                        }

                        if($i==22){
                            $customX =1;
                            $y=$y-1.9;
                        }

                        if($i==23){
                            $customX =1.4;
                            $y=$y-1.9;
                        }


                        if($i==24){
                            $customX =1.4;
                            $y=$y-1.9;
                        }

                        if($i==25){
                            $customX =1.4;
                            $y=$y-2.1;
                        }

                        if($i==26){
                            $customX =2.2;
                            $y=$y-2.4;
                        }

                        if($i==27){
                            $customX =2;
                            $y=$y-2.8;
                        }

                        if($i==28){
                            $customX =1.9;
                            $y=$y-3;
                        }


                        if($i==29){
                            $customX =1.8;
                            $y=$y-3.3;
                        }

                        if($i==30){
                            $customX =1.5;
                            $y=$y-3.7;
                        }


                        if($i==31){
                            $customX =1.6;
                            $y=$y-3.8;
                        }

                        if($i==32){
                            $customX =1.2;
                            $y=$y-4;
                        }
                        

                        if($i==33){
                            $customX =1.3;
                            $y=$y-4.1;
                        }
                        

                        if($i==34){
                            $customX =1.2;
                            $y=$y-4.1;
                        }

                        if($i==35){
                            $customX =1.5;
                            $y=$y-3.9;
                        }
                        

                        if($i==36){
                            $customX =1.4;
                            $y=$y-3.7;
                        }
                        
                        if($i==37){
                            $customX =1.7;
                            $y=$y-3.3;
                        }

                        if($i==38){
                            $customX =1.6;
                            $y=$y-3;
                        }

                        if($i==39){
                            $customX =1.7;
                            $y=$y-2.4;
                        }

                        // if($i==40){
                        //     $customX =1.6;
                        //     $y=$y-1.8;
                        // }

                        //  if($i==41){
                        //     $customX =1.7;
                        //     $y=$y-2.4;
                        // }

                        // if($i==42){
                        //     $customX =1.6;
                        //     $y=$y-1.8;
                        // }
                        


                        $pdf->SetXY($x + $offsetX+ $customX,$y);
                        $pdfBig->SetXY($x + $offsetX+ $customX,$y);

                        $pdf->StartTransform();
                        $pdfBig->StartTransform();

                        
                        if($i>0){

                            if($i==5){
                                $rotateAngle =8;
                                $pdf->Rotate($rotateAngle); 
                                $pdfBig->Rotate($rotateAngle); 
                            }
                            if($i==6){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==7){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle);
                            }

                            if($i==8){
                                $rotateAngle =14;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==9){
                                $rotateAngle =16;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==10){
                                $rotateAngle =18;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==11){
                                $rotateAngle =20;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==12){
                                $rotateAngle =19;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==13){
                                $rotateAngle =20;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==14){
                                $rotateAngle =19;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==15){
                                $rotateAngle =18;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==16){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==17){
                                $rotateAngle =10;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==18){
                                $rotateAngle =5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i==19){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate($rotateAngle); 
                            }

                            if($i>19){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i>21){
                                $rotateAngle =4;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==26){
                                $rotateAngle =9;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                            if($i==27){
                                $rotateAngle =8;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                            if($i==28){
                                $rotateAngle =6;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==29){
                                $rotateAngle =4;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==30){
                                $rotateAngle =3;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==31){
                                $rotateAngle =2;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==32){
                                $rotateAngle =1;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==33){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==34){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==35){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==36){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==37){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==38){
                                $rotateAngle =0.5;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }

                            if($i==39){
                                $rotateAngle =0;
                                $pdf->Rotate($rotateAngle);
                                $pdfBig->Rotate(-$rotateAngle); 
                            }


                        }else{
                            $rotateAngle =$i+5;
                            $pdf->Rotate($rotateAngle);
                            $pdfBig->Rotate(-$rotateAngle); 
                        }

                        $pdf->Cell(210, 10, $char, 0, false, 'L');
                        $pdfBig->Cell(210, 10, $char, 0, false, 'L');
                        $pdf->StopTransform();
                        $pdfBig->StopTransform();
                    
                    }
                    $k=$k+3;
                }

            }
            // Stop clipping.
            $pdf->StopTransform();
            $pdfBig->StopTransform();

            $x = 22;
            $y = 146;
            $pdf->SetTextColor(0, 0, 0);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, 'NAME:', 0, false, 'L');

            $pdfBig->SetTextColor(0, 0, 0);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, "NAME:", 0, false, 'L');


            $x = 41;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, $name, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, $name, 0, false, 'L');


            $x = 152;   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, "*F1*", 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, "*F1*", 0, false, 'L');

            $x = 165;   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, $indexNo, 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, $indexNo, 0, false, 'L');

            
            $x = 41;
            $y = 150;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, $school, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, $school, 0, false, 'L');


            $x = 37;
            $y = 158;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($ArialB, 'B', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, 'SUBJECT', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($ArialB, 'B', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, 'SUBJECT', 0, false, 'L');

            $x = 155;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($ArialB, 'B', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, 'GRADE', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($ArialB, 'B', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, 'GRADE', 0, false, 'L');

            
            $subjectx1 = 27;
            $subjectx2 = 37;
            $subjectx3 = 153;

            $y = 162;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx1, $y);
            $pdf->Cell(0, 0, $subCode1, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx1, $y);
            $pdfBig->Cell(0, 0, $subCode1, 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx2, $y);
            $pdf->Cell(0, 0, 'ENGLISH', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx2, $y);
            $pdfBig->Cell(0, 0, 'ENGLISH', 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx3, $y);
            $pdf->Cell(0, 0, $subGrade1, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx3, $y);
            $pdfBig->Cell(0, 0, $subGrade1, 0, false, 'L');




            $y = $y+4;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx1, $y);
            $pdf->Cell(0, 0, $subCode2, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx1, $y);
            $pdfBig->Cell(0, 0, $subCode2, 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx2, $y);
            $pdf->Cell(0, 0, 'KISWAHILI', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx2, $y);
            $pdfBig->Cell(0, 0, 'KISWAHILI', 0, false, 'L');


            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx3, $y);
            $pdf->Cell(0, 0, $subGrade2, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx3, $y);
            $pdfBig->Cell(0, 0, $subGrade2, 0, false, 'L');



            $y = $y+4;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx1, $y);
            $pdf->Cell(0, 0, $subCode3, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx1, $y);
            $pdfBig->Cell(0, 0, $subCode3, 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx2, $y);
            $pdf->Cell(0, 0, 'MATHEMATICS', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx2, $y);
            $pdfBig->Cell(0, 0, 'MATHEMATICS', 0, false, 'L');


            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx3, $y);
            $pdf->Cell(0, 0, $subGrade3, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx3, $y);
            $pdfBig->Cell(0, 0, $subGrade3, 0, false, 'L');


            $y = $y+4;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx1, $y);
            $pdf->Cell(0, 0, $subCode4, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx1, $y);
            $pdfBig->Cell(0, 0, $subCode4, 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx2, $y);
            $pdf->Cell(0, 0, 'BIOLOGY', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx2, $y);
            $pdfBig->Cell(0, 0, 'BIOLOGY', 0, false, 'L');


            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx3, $y);
            $pdf->Cell(0, 0, $subGrade4, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx3, $y);
            $pdfBig->Cell(0, 0, $subGrade4, 0, false, 'L');




            $y = $y+4;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx1, $y);
            $pdf->Cell(0, 0, $subCode5, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx1, $y);
            $pdfBig->Cell(0, 0, $subCode5, 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx2, $y);
            $pdf->Cell(0, 0, 'CHEMISTRY', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx2, $y);
            $pdfBig->Cell(0, 0, 'CHEMISTRY', 0, false, 'L');


            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx3, $y);
            $pdf->Cell(0, 0, $subGrade5, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx3, $y);
            $pdfBig->Cell(0, 0, $subGrade5, 0, false, 'L');




            $y = $y+4;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx1, $y);
            $pdf->Cell(0, 0, $subCode6, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx1, $y);
            $pdfBig->Cell(0, 0, $subCode6, 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx2, $y);
            $pdf->Cell(0, 0, 'GEOGRAPHY', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx2, $y);
            $pdfBig->Cell(0, 0, 'GEOGRAPHY', 0, false, 'L');


            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx3, $y);
            $pdf->Cell(0, 0, $subGrade6, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx3, $y);
            $pdfBig->Cell(0, 0, $subGrade6, 0, false, 'L');



            $y = $y+4;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx1, $y);
            $pdf->Cell(0, 0, $subCode7, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx1, $y);
            $pdfBig->Cell(0, 0, $subCode7, 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx2, $y);
            $pdf->Cell(0, 0, 'CHRISTIAN RELIGIOUS EDUCATION', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx2, $y);
            $pdfBig->Cell(0, 0, 'CHRISTIAN RELIGIOUS EDUCATION', 0, false, 'L');


            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx3, $y);
            $pdf->Cell(0, 0, $subGrade7, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx3, $y);
            $pdfBig->Cell(0, 0, $subGrade7, 0, false, 'L');




            $y = $y+4;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx1, $y);
            $pdf->Cell(0, 0, $subCode8, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx1, $y);
            $pdfBig->Cell(0, 0, $subCode8, 0, false, 'L');

            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx2, $y);
            $pdf->Cell(0, 0, 'BUSINESS STUDIES', 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx2, $y);
            $pdfBig->Cell(0, 0, 'BUSINESS STUDIES', 0, false, 'L');


            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($subjectx3, $y);
            $pdf->Cell(0, 0, $subGrade8, 0, false, 'L');

            // $pdfBig->SetTextColor(237, 50, 55);   
            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($subjectx3, $y);
            $pdfBig->Cell(0, 0, $subGrade8, 0, false, 'L');



            $x = 60;
            $y = 202;
            // $pdf->SetTextColor(237, 50, 55);   
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, 'SUBJECT NAMED EIGHT', 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, 'SUBJECT NAMED EIGHT', 0, false, 'L');

            $x = 108;
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, 'MEAN GRADE '.$mgDesc, 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, 'MEAN GRADE '.$mgDesc, 0, false, 'L');


            $x = 80;
            $y= 206;
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, 'EXAMINATION OF YEAR '.$resyear, 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, 'EXAMINATION OF YEAR '.$resyear, 0, false, 'L');


            $x = 83;
            $y= 210;
            $pdf->SetFont($Arial, '', 9, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, 'PRINTED : '.$code, 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 9, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, 'PRINTED : '.$code, 0, false, 'L');




            $x = 86;
            $y= 263;
            $pdf->SetFont($Arial, '', 18, '', false);
            $pdf->SetXY($x, $y);
            $pdf->Cell(0, 0, 'KCSE/15', 0, false, 'L');

            $pdfBig->SetFont($Arial, '', 18, '', false);
            $pdfBig->SetXY($x, $y);
            $pdfBig->Cell(0, 0, 'KCSE/15', 0, false, 'L');



            
            

            
            //Signature
            //$signature_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Vice Chancellor.png';
            // $signaturex = 155;
            // $signaturey = 254;
            // $signatureWidth = 30;
            // $signatureHeight = 11;
            // $pdf->image($signature_path,$signaturex,$signaturey,$signatureWidth,$signatureHeight,"",'','L',true,3600);           
            // $pdfBig->image($signature_path,$signaturex,$signaturey,$signatureWidth,$signatureHeight,"",'','L',true,3600);

            
            $nameOrg = $name;
            $ghost_font_size = '12';
            $ghostImagex = 21;
            $ghostImagey = 277;
            $ghostImageWidth = 39.405983333;
            $ghostImageHeight = 8;
            // $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
            $tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
            // $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');

            // $pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);

            // $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);


            $serial_no=$GUID=$studentData[0];
            // $str= $name;
            $dt = date("_ymdHis");
            $str=$serial_no.$dt;
            // $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
            $codeContents = strtoupper(md5($str));
            $encryptedString = strtoupper(md5($str));

            $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
            $qrCodex = 20;
            $qrCodey = 203;
            $qrCodeWidth =17;
            $qrCodeHeight = 17;
            \QrCode::size(75)
                ->backgroundColor(255, 255, 0)
                ->format('png')
                ->generate($codeContents, $qr_code_path);

            // $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);

            // $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);

            // set style for barcode
            $style = array(
                'border' => false,
                'vpadding' => '2',
                'hpadding' => '2',
                'fgcolor' => array(0,0,0),
                'bgcolor' => array(255,255,255),
                'module_width' => 1, // width of a single module in points
                'module_height' => 1 // height of a single module in points
            );


            $strQr = "Name :".$name.', Index :'.$indexNo.', Mean Grade :'.$mgDesc.', Aggregate :'.$aggregate.', Year :'.$resyear."\r\n\r\n".$codeContents;
            
            $pdf->write2DBarcode($strQr, 'DATAMATRIX', $qrCodex, $qrCodey, $qrCodeWidth, $qrCodeHeight, $style, 'N');
            $pdfBig->write2DBarcode($strQr, 'DATAMATRIX', $qrCodex, $qrCodey, $qrCodeWidth, $qrCodeHeight, $style, 'N');

            // $x = 10;
            // $y = 272;
            // $microlinestr=$studentData[3];
            // $pdf->SetFont($Arial, '', 1, '', false);
            // $pdf->SetTextColor(0, 0, 0);
            // $pdf->SetXY($x, $y);        
            // $pdf->Cell(0, 0, $microlinestr, 0, false, 'C');

            // $pdfBig->SetFont($Arial, '', 1, '', false);
            // $pdfBig->SetTextColor(0, 0, 0);
            // $pdfBig->SetXY($x, $y);        
            // $pdfBig->Cell(0, 0, $microlinestr, 0, false, 'C');

           /* $pdf->AddSpotColor('Clear', 67.3, 31.2, 0, 20.8); 
            $pdfBig->AddSpotColor('Clear', 67.3, 31.2, 0, 20.8); */
            

            // 20-11-2023
            // $pdf->AddSpotColor('Clear',0, 0, 0, 20); 
            // $pdfBig->AddSpotColor('Clear', 0, 0, 0, 20); 

            // $x = 12;
            // $y = 273;
            // $namestr = $studentData[3];
            // $pdf->SetOverprint(true, true, 0);
            // $pdf->SetFont($ArialB, '', 10, '', false);
            // $pdf->SetTextSpotColor('Clear', 100);
            // $pdf->SetXY($x, $y);
            // $pdf->StartTransform();
            // $pdf->Rotate(90);
            // $pdf->Cell(0, 0, $namestr, 0, false, 'L');
            // $pdf->StopTransform();
            // $pdf->SetOverprint(false, false, 0);

            // $pdfBig->SetOverprint(true, true, 0);
            // $pdfBig->SetFont($ArialB, '', 10, '', false);
            // $pdfBig->SetTextSpotColor('Clear', 100);
            // $pdfBig->SetXY($x, $y);
            // $pdfBig->StartTransform();
            // $pdfBig->Rotate(90);
            // $pdfBig->Cell(0, 0, $namestr, 0, false, 'L');
            // $pdfBig->StopTransform();
            // $pdfBig->SetOverprint(false, false, 0);

            // $serial_no=$GUID=$studentData[0];
          
            if($previewPdf!=1){

                $certName = str_replace("/", "_", $GUID) .".pdf";
                $dt = date("_ymdHis");
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
                // Comment To Uncomment on live
                $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,'KENYA-C',$admin_id,$card_serial_no);
                // Comment To Uncomment on live
                //$card_serial_no=$card_serial_no+1;
            }
            //$card_serial_no=$card_serial_no+1;
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

        //if($previewPdf!=1){
            //$this->updateCardNo('KENYA-C',$card_serial_no-$cardDetails->starting_serial_no,$card_serial_no);
        //}
        $msg = '';
        $file_name =  str_replace("/", "_",'KENYA-C'.date("Ymdhms")).'.pdf';
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $filename = public_path().'/backend/tcpdf/examples/'.$file_name;

        //$file_name_inv='INV_'.$file_name;
        //$filenameInvisible = public_path().'/backend/tcpdf/examples/'.$file_name_inv;
        
        $pdfBig->output($filename,'F');

        if($previewPdf!=1){
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name);
            @unlink($filename);

            /*$aws_qr = \File::copy($filenameInvisible,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/'.$file_name_inv);
            @unlink($filenameInvisible);*/

            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name="KENYA-C";
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
            $msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/".$file_name."'class='downloadpdf' download target='_blank'>Here</a> to download visible data file.";
        }else{
            
            $aws_qr = \File::copy($filename,public_path().'/'.$subdomain[0].'/backend/tcpdf/examples/preview/'.$file_name);
            @unlink($filename);
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
            $pdf_url=$path.$subdomain[0]."/backend/tcpdf/examples/preview/".$file_name;
            $msg = "<b>Click <a href='".$path.$subdomain[0]."/backend/tcpdf/examples/preview/".$file_name."'class='downloadpdf' download target='_blank'>Here</a> to download file<b>";
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
		
        /*copy($file1, $file2);        
        $aws_qr = \File::copy($file2,$pdfActualPath);            
        @unlink($file2);*/
		$source=\Config::get('constant.directoryPathBackward')."\\backend\\temp_pdf_file\\".$certName;
		$output=\Config::get('constant.directoryPathBackward').$subdomain[0]."\\backend\\pdf_file\\".$certName;
		CoreHelper::compressPdfFile($source,$output);
        @unlink($file1);

        // Rohit Changes 18/05/2023
        $outputFile = 'public/'.$subdomain[0]."\\backend\\pdf_file\\".$certName;
        //$movedfolder = 'public/'.$subdomain[0]."/backend/pdf_file";

        // awsS3Instances
        $awsS3Instances = \Config::get('constant.awsS3Instances');
        
        if(in_array($subdomain[0], $awsS3Instances)) {
            CoreHelper::awsUpload($output,$outputFile,$serial_no,$certName);
        }
        // rohit changes 18/05/2023


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
        $resultu = StudentTable::where('serial_no',"".$serial_no)->update(['status'=>'0']);
        // Insert the new record
        
        $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2]);
        }
        
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
        $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>'T-'.$sr_no,'template_name'=>$template_name,'card_serial_no'=>$card_serial_no,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);
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
        if (file_exists($tmpname)) {
         unlink($tmpname);
        }
        mkdir($tmpname, 0777);
        return $tmpname;
    }

    /*public function CreateMessage($tmpDir, $name = "",$font_size,$print_color) // handled for font_size 13 only
    {
        if($name == "")
            return;
        $name = strtoupper($name);

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
      
            $filename = public_path()."/backend/canvas/ghost_images/green/F13_H10_W360.png";
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
    }*/

    public function CreateMessage($tmpDir, $name = "",$font_size,$print_color) // handled for font_size 12 only
    {
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
                
            $filename = public_path()."/backend/canvas/ghost_images/green/F12_H8_W288.png";
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
    }



    /*public function CreateMessage($tmpDir, $name = "",$font_size,$print_color)
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
    }*/

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
