<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\models\BackgroundTemplateMaster;
use Session, TCPDF, TCPDF_FONTS, Auth, DB;
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

class PdfGenerateAnuA3TranscriptJob
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

        $studentDataOrg = $pdf_data['studentDataOrg'];
        $subjectDataOrg = $pdf_data['subjectDataOrg'];
        $subjectDataOrg1 = $pdf_data['subjectDataOrg'];
        $template_id = $pdf_data['template_id'];
        $dropdown_template_id = $pdf_data['dropdown_template_id'];
        $points = $pdf_data['points'];
        $previewPdf = $pdf_data['previewPdf'];
        $excelfile = $pdf_data['excelfile'];
        $auth_site_id = $pdf_data['auth_site_id'];
        $previewWithoutBg = $previewPdf[1];
        $previewPdf = $previewPdf[0];


        if (isset($pdf_data['generation_from']) && $pdf_data['generation_from'] == 'API') {
            $admin_id = $pdf_data['admin_id'];
        } else {
            $admin_id = \Auth::guard('admin')->user()->toArray();
        }
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $systemConfig = SystemConfig::select('sandboxing', 'printer_name')->where('site_id', $auth_site_id)->first();

        $printer_name = $systemConfig['printer_name'];

        //Separate students subjects

        $ghostImgArr = array();
        $pdfBig = new TCPDF('L', 'mm', array('297', '420'), true, 'UTF-8', false);
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

        //set fonts
        $timesNewRoman = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Times-New-Roman.TTF', 'TrueTypeUnicode', '', 96);
        $arial = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $trebuc = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\trebuc.ttf', 'TrueTypeUnicode', '', 96);
        $trebucb = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\TREBUCBD.ttf', 'TrueTypeUnicode', '', 96);
        $trebuci = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Trebuchet-MS-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $graduateR = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\graduate-regular.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsR = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Poppins Regular 400.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsM = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Poppins Medium 500.ttf', 'TrueTypeUnicode', '', 96);

        $log_serial_no = 1;
        // $cardDetails=$this->getNextCardNo('anuA3'); //not needed for this
        // $card_serial_no=$cardDetails->next_serial_no; //not needed for this
        $card_serial_no = '';
        $generated_documents = 0;

        $sftpData = [];

        foreach ($studentDataOrg as $studentData) {

            //For Custom Loader
            $startTimeLoader =  date('Y-m-d H:i:s');

            $pdfBig->AddPage();

            //set background image
            $template_img_generate = public_path() . '\\' . $subdomain[0] . '\backend\canvas\bg_images\anu_a3_transcript.jpg';

            if ($previewPdf == 1) {
                if ($previewWithoutBg != 1) {
                    $pdfBig->Image($template_img_generate, 0, 0, '420', '297', "JPG", '', 'R', true);
                }
                $date_font_size = '11';
                $date_nox = 180;
                $date_noy = 12;
                $date_nostr = 'DRAFT ' . date('d-m-Y H:i:s');
                $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
                $pdfBig->SetTextColor(192, 192, 192);
                $pdfBig->SetXY($date_nox, $date_noy);
                $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
                $pdfBig->SetTextColor(0, 0, 0, 100, false, '');
                $pdfBig->SetFont($arialNarrowB, '', 9, '', false);
            }
            $pdfBig->setPageMark();

            $pdf = new TCPDF('L', 'mm', array('297', '420'), true, 'UTF-8', false);
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
            $pdf->AddPage();
            //set background image
            $template_img_generate = public_path() . '\\' . $subdomain[0] . '\backend\canvas\bg_images\anu_a3_transcript.jpg';
            if ($previewPdf != 1) {
                $pdf->Image($template_img_generate, 0, 0, '420', '297', "JPG", '', 'R', true);
            }
            $pdf->setPageMark();


            $serial_no = trim($studentData[6]);
            $GUID = trim($studentData[6]);
            $unique_id = trim($studentData[6]);
            $student_id = trim($studentData[6]);

            $DOB = trim($studentData[10]);
            $candidate_name = trim($studentData[8]);
            $Batch = trim($studentData[5]);
            $Semester = trim($studentData[9]);
            $Programme = trim($studentData[3]);
            $major = trim($studentData[4]);
            $remarks = trim($studentData[229]);
            $remarksHu = trim($studentData[235]);
            $tec = trim($studentData[27]);
            $ecp = trim($studentData[26]);
            $Percentage = trim($studentData[17]);
            $doi = trim($studentData[18]);
            $photo = trim($studentData[236]);

            $sign_name1 = trim($studentData[237]);
            $designation1 = trim($studentData[238]);
            $role1 = trim($studentData[239]);

            $sign_name2 = trim($studentData[240]);
            $designation2 = trim($studentData[241]);
            $role2 = trim($studentData[242]);

            $sign_name3 = trim($studentData[243]);
            $designation3 = trim($studentData[244]);
            $role3 = trim($studentData[245]);
            $minor = trim($studentData[246]);
            $cpgaDescription = trim($studentData[247]);

            // $str=$studentData[6];
            // $codeContents = "[".$student_id." - ". $candidate_name ."]";
            // $codeContents .="\n";
            // $codeContents .= "[CGPA - ".$cgpa." (". $cgpaRemark .")]";
            // $codeContents .="\n";
            // $codeContents .= "[Percentage - ".$Percentage."%]";
            // $codeContents .="\n";
            // $codeContents .= $Programme."(".$major.")";
            // $codeContents .="\n\n".strtoupper(md5($str));
            // // $codeContents =$encryptedString = strtoupper(md5($str));
            // $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
            // $qrCodex = 176;
            // $qrCodey = 15;
            // $qrCodeWidth =20;
            // $qrCodeHeight = 20;
            // \QrCode::size(75.6)
            //     ->backgroundColor(255, 255, 0)
            //     ->format('png')
            //     ->generate($codeContents, $qr_code_path);

            // $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
            // $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);


            if ($photo != '') {
                //path of photos
                $profile_path_org = public_path() . '\\' . $subdomain[0] . '\backend\templates\100\\' . $photo . '.jpg';
                //set profile image   
                $profilex = 375;
                $profiley = 10;
                $profileWidth = 25;
                $profileHeight = 28;
                // $profilex = 175.5;
                // $profiley = 62;
                // $profileWidth = 16;
                // $profileHeight = 22;
                $pdfBig->image($profile_path_org, $profilex, $profiley, $profileWidth, $profileHeight, "", '', 'L', true, 3600);
                $pdfBig->setPageMark();
                $pdf->image($profile_path_org, $profilex, $profiley, $profileWidth, $profileHeight, "", '', 'L', true, 3600);
                $pdf->setPageMark();
            }

            $pdfBig->SetFont($trebucb, '', 14, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(90, 10);
            $pdfBig->MultiCell(0, 0, "TRANSCRIPT", 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 14, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(90, 10);
            $pdf->MultiCell(0, 0, "TRANSCRIPT", 0, "L", 0, 0, '', '', true, 0, true);



            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 18);
            $pdfBig->MultiCell(42, 0, "STUDENT NAME:", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(80, 18);
            $pdf->MultiCell(42, 0, "STUDENT NAME:", 0, "R", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 18);
            $pdfBig->MultiCell(140, 0, $candidate_name, 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(122, 18);
            $pdf->MultiCell(140, 0, $candidate_name, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 22);
            $pdfBig->MultiCell(42, 0, "STUDENT ID NUMBER: ", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(80, 22);
            $pdf->MultiCell(42, 0, "STUDENT ID NUMBER: ", 0, "R", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 22);
            $pdfBig->MultiCell(42, 0, $student_id, 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(122, 22);
            $pdf->MultiCell(42, 0, $student_id, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 26);
            $pdfBig->MultiCell(42, 0, "DATE OF BIRTH: ", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(80, 26);
            $pdf->MultiCell(42, 0, "DATE OF BIRTH: ", 0, "R", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 26);
            $pdfBig->MultiCell(42, 0, $DOB, 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(122, 26);
            $pdf->MultiCell(42, 0, $DOB, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 30);
            $pdfBig->MultiCell(42, 0, "BATCH: ", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(80, 30);
            $pdf->MultiCell(42, 0, "BATCH: ", 0, "R", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 30);
            $pdfBig->MultiCell(42, 0, $Batch, 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(122, 30);
            $pdf->MultiCell(42, 0, $Batch, 0, "L", 0, 0, '', '', true, 0, true);





            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(80, 34);
            $pdfBig->MultiCell(42, 0, "DURATION", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(80, 34);
            $pdf->MultiCell(42, 0, "DURATION", 0, "R", 0, 0, '', '', true, 0, true);


            // table second upper heading data
            $newArraySession = [];

            foreach ($subjectDataOrg1 as $row => $innerArray) {
                if ($innerArray[0] == $student_id) {
                    // if($innerArray[1] == 'V') {
                    //     break;
                    // }
                    $newArraySession[$row][0] = $innerArray[0];
                    $newArraySession[$row][1] = $innerArray[2];
                    $newArraySession[$row][2] = $innerArray[4];
                    $newArraySession[$row][3] = $innerArray[5];
                    $newArraySession[$row][4] = $innerArray[6];
                    $newArraySession[$row][5] = $innerArray[7];
                    $newArraySession[$row][6] = $innerArray[8];
                    $newArraySession[$row][7] = $innerArray[9];
                }
            }
            //print_r($newArraySession);
            $first = reset($newArraySession);
            $last = end($newArraySession);

            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(122, 34);
            $pdfBig->MultiCell(70, 0, $first[1] . ' To ' . $last[1], 0, "L", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(122, 34);
            $pdf->MultiCell(70, 0, $first[1] . ' To ' . $last[1], 0, "L", 0, 0, '', '', true, 0, true);




            // $pdfBig->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));
            // $pdfBig->SetFont($trebuc, '', 10, '', false);
            // $pdfBig->SetXY(210, 40);
            // $pdfBig->MultiCell(1, 207, '', 'L', "L", 0, 0, '', '', true, 0, true);

            // $pdf->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));
            // $pdf->SetFont($trebuc, '', 10, '', false);
            // $pdf->SetXY(210, 40);
            // $pdf->MultiCell(1, 207, '', 'L', "L", 0, 0, '', '', true, 0, true);



            $pdfBig->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(137, 142, 145)));
            $pdf->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(137, 142, 145)));
            $pdfBig->setCellPaddings($left = 0, $top = 0.8, $right = 0, $bottom = 0);
            $pdf->setCellPaddings($left = 0, $top = 0.8, $right = 0, $bottom = 0);

            $tableFont = 7;
            $pdfBig->SetFont($trebucb, '', $tableFont, '', false);
            $pdf->SetFont($trebucb, '', $tableFont, '', false);
            $tableX = 13;
            $tableY = 40;
            $tableHeight = 4.5;
            $tableSpa1 = 20;
            $tableSpa2 = 18;
            $tableSpa3 = 20;
            $tableSpa4 = 108;
            $tableSpa5 = 9;
            $tableSpa6 = 9;
            $tableSpa7 = 9;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa1, $tableHeight, 'SESSION', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell($tableSpa1, $tableHeight, 'SESSION', 'LTRB', "C", 0, 0, '', '', true, 0, true);

            $tableX = $tableX + $tableSpa1;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa2, $tableHeight, 'SEMESTER', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell($tableSpa2, $tableHeight, 'SEMESTER', 'LTRB', "C", 0, 0, '', '', true, 0, true);

            $tableX = $tableX + $tableSpa2;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa3, $tableHeight, 'CODE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell($tableSpa3, $tableHeight, 'CODE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX + $tableSpa3;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa4, $tableHeight, 'COURSE TITLE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell($tableSpa4, $tableHeight, 'COURSE TITLE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX + $tableSpa4;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa5, $tableHeight, 'CR', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell($tableSpa5, $tableHeight, 'CR', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX + $tableSpa5;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa6, $tableHeight, 'LG', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell($tableSpa6, $tableHeight, 'LG', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX = $tableX + $tableSpa6;

            $pdfBig->SetXY($tableX, $tableY);
            $pdfBig->MultiCell($tableSpa7, $tableHeight, 'GP', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX, $tableY);
            $pdf->MultiCell($tableSpa7, $tableHeight, 'GP', 'LTRB', "C", 0, 0, '', '', true, 0, true);



            // second table 
            $tableX1 = 214;
            $tableY1 = 40;
            $tableSpa11 = 20;
            $tableSpa22 = 18;
            $tableSpa33 = 20;
            $tableSpa44 = 108;
            $tableSpa55 = 9;
            $tableSpa66 = 9;
            $tableSpa77 = 9;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa11, $tableHeight, 'SESSION', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX1, $tableY1);
            $pdf->MultiCell($tableSpa11, $tableHeight, 'SESSION', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1 + $tableSpa11;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa22, $tableHeight, 'SEMESTER', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX1, $tableY1);
            $pdf->MultiCell($tableSpa22, $tableHeight, 'SEMESTER', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1 + $tableSpa22;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa33, $tableHeight, 'CODE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX1, $tableY1);
            $pdf->MultiCell($tableSpa33, $tableHeight, 'CODE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1 + $tableSpa33;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa44, $tableHeight, 'COURSE TITLE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX1, $tableY1);
            $pdf->MultiCell($tableSpa44, $tableHeight, 'COURSE TITLE', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1 + $tableSpa44;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa55, $tableHeight, 'CR', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX1, $tableY1);
            $pdf->MultiCell($tableSpa55, $tableHeight, 'CR', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1 + $tableSpa55;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa66, $tableHeight, 'LG', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX1, $tableY1);
            $pdf->MultiCell($tableSpa66, $tableHeight, 'LG', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $tableX1 = $tableX1 + $tableSpa66;

            $pdfBig->SetXY($tableX1, $tableY1);
            $pdfBig->MultiCell($tableSpa77, $tableHeight, 'GP', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            $pdf->SetXY($tableX1, $tableY1);
            $pdf->MultiCell($tableSpa77, $tableHeight, 'GP', 'LTRB', "C", 0, 0, '', '', true, 0, true);
            // second table 


            $pdfBig->SetFont($trebuc, '', $tableFont, '', false);
            $pdf->SetFont($trebuc, '', $tableFont, '', false);
            $pdfBig->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);
            $pdf->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);

            $subCount1 = 1;
            $subCount2 = 1;
            $tableY = $tableY1 + $tableHeight;

            $tableY1 = $tableY1 + $tableHeight;

            $romanArray = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX'];
            foreach ($subjectDataOrg as $row => $arrayValue) {

                if ($arrayValue[0] == $student_id) {
                    //if ($arrayValue[1] == "I" || $arrayValue[1] == "II" || $arrayValue[1] == "III" || $arrayValue[1] == "IV") {
                    if ($subCount1 <= 45) {
                        if (!empty($arrayValue[10])) {

                            $tableX = 13;
                            //$tableY = $tableY+$tableHeight;

                            $tableHeightOld = $tableHeight;
                            $pdfBig->startTransaction();
                            $pdfBig->SetFont($trebuc, '', $tableFont, '', false);
                            // get the number of lines
                            $lines = $pdfBig->MultiCell($tableSpa4, 0, $arrayValue[10], 0, 'L', 0, 0, '', '', true, 0, false, true, 0);
                            $pdfBig = $pdfBig->rollbackTransaction(); // restore previous object

                            if ($lines > 1) {
                                $tableHeight = $tableHeight + 4;
                            } else {
                                $tableHeight = $tableHeight;
                            }

                            // echo $tableHeight;
                            // echo "<br>";
                            //$pdfBig->SetXY($tableX, $tableY);
                            //$pdfBig->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
                            //$pdfBig->MultiCell($tableSpa1, $tableHeight, $arrayValue[3], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa1, $h = $tableHeight, $arrayValue[3], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');


                            //$pdf->SetXY($tableX, $tableY);
                            //$pdf->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
                            //$pdf->MultiCell($tableSpa1, $tableHeight, $arrayValue[3], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa1, $h = $tableHeight, $arrayValue[3], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            $tableX = $tableX + $tableSpa1;


                            //$pdfBig->SetXY($tableX, $tableY);
                            //$pdfBig->MultiCell($tableSpa2, $tableHeight, 'Semester-'.$arrayValue[1] , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            if (in_array($arrayValue[1], $romanArray)) {
                                $semesterValue = $arrayValue[1];
                            } else {
                                $semesterValue = '';
                            }
                            $pdfBig->MultiCell($w = $tableSpa2, $h = $tableHeight, $semesterValue, $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            //$pdf->SetXY($tableX, $tableY);
                            //$pdf->MultiCell($tableSpa2, $tableHeight, 'Semester-'.$arrayValue[1] , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa2, $h = $tableHeight, $semesterValue, $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');
                            $tableX = $tableX + $tableSpa2;

                            //$pdfBig->SetXY($tableX, $tableY);
                            //$pdfBig->MultiCell($tableSpa3, $tableHeight, $arrayValue[11], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa3, $h = $tableHeight,  $arrayValue[11], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            //$pdf->SetXY($tableX, $tableY);
                            //$pdf->MultiCell($tableSpa3, $tableHeight, $arrayValue[11], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa3, $h = $tableHeight,  $arrayValue[11], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            $tableX = $tableX + $tableSpa3;



                            //$pdfBig->SetXY($tableX, $tableY);
                            //$pdfBig->MultiCell($tableSpa4, $tableHeight, $arrayValue[10], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->setCellPaddings($left = 2, $top = 0, $right = 2, $bottom = 0);
                            $pdf->setCellPaddings($left = 2, $top = 0, $right = 2, $bottom = 0);
                            $pdfBig->MultiCell($w = $tableSpa4, $h = $tableHeight,  $arrayValue[10], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            //$pdf->SetXY($tableX, $tableY);
                            //$pdf->MultiCell($tableSpa4, $tableHeight, $arrayValue[10], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa4, $h = $tableHeight,  $arrayValue[10], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            $pdfBig->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);
                            $pdf->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);

                            $tableX = $tableX + $tableSpa4;

                            //$pdfBig->SetXY($tableX, $tableY);
                            $pdf->SetXY($tableX, $tableY);
                            if (is_numeric($arrayValue[20])) {
                                $crPoint = number_format($arrayValue[20], 2);
                            } else {
                                $crPoint = $arrayValue[20];
                            }
                            //$pdfBig->MultiCell($tableSpa5, $tableHeight, $crPoint , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa5, $h = $tableHeight,  $crPoint, $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            //$pdf->MultiCell($tableSpa5, $tableHeight, $crPoint , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa5, $h = $tableHeight,  $crPoint, $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');
                            $tableX = $tableX + $tableSpa5;

                            //$pdfBig->SetXY($tableX, $tableY);
                            //$pdfBig->MultiCell($tableSpa6, $tableHeight, $arrayValue[18], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa6, $h = $tableHeight,  $arrayValue[18], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            //$pdf->SetXY($tableX, $tableY);
                            //$pdf->MultiCell($tableSpa6, $tableHeight, $arrayValue[18], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa6, $h = $tableHeight,  $arrayValue[18], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            $tableX = $tableX + $tableSpa6;

                            if (is_numeric($arrayValue[19])) {
                                $gpPoint = number_format($arrayValue[19], 2);
                            } else {
                                $gpPoint = $arrayValue[19];
                            }

                            //$pdfBig->SetXY($tableX, $tableY);
                            //$pdfBig->MultiCell($tableSpa7, $tableHeight, $gpPoint, 'LTBR', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa7, $h = $tableHeight,  $gpPoint, $border = 'LTBR', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');


                            //$pdf->SetXY($tableX, $tableY);
                            //$pdf->MultiCell($tableSpa7, $tableHeight, $gpPoint, 'LTBR', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa7, $h = $tableHeight,  $gpPoint, $border = 'LTBR', $align = 'C', $fill = 0, 1, $x = $tableX, $y = $tableY, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            // echo $lines;
                            // echo "<br>";
                            // echo $tableHeight;
                            // echo "<br>";
                            // echo $tableHeight1;
                            // echo "<br>";
                            // echo "<br>";
                            // if($tableHeight1 != $tableHeight) {
                            //     $tableHeight = $tableHeight1;
                            // } else {
                            //     $tableHeight = $tableHeight;
                            // }
                            $tableY = $tableY + $tableHeight;
                            if ($lines > 1) {
                                $tableHeight = $tableHeight - 4;
                                $subCount1++;
                            }

                            $subCount1++;
                        }
                    }

                    //} else {
                    else {
                        if (!empty($arrayValue[10])) {

                            $tableX1 = 214;
                            //$tableY1 = $tableY1+$tableHeight;

                            $pdfBig->startTransaction();
                            $pdfBig->SetFont($trebuc, '', $tableFont, '', false);
                            // get the number of lines
                            $lines = $pdfBig->MultiCell($tableSpa44, 0, $arrayValue[10], 0, 'L', 0, 0, '', '', true, 0, false, true, 0);
                            $pdfBig = $pdfBig->rollbackTransaction(); // restore previous object

                            if ($lines > 1) {
                                $tableHeight = $tableHeight + 4;
                            } else {
                                $tableHeight = $tableHeight;
                            }

                            // $pdfBig->SetXY($tableX1, $tableY1);
                            // $pdfBig->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
                            // $pdfBig->MultiCell($tableSpa11, $tableHeight, $arrayValue[3], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa11, $h = $tableHeight, $arrayValue[3], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            // $pdf->SetXY($tableX1, $tableY1);
                            // $pdf->setCellPaddings( $left = 0, $top = 0.8, $right = 0, $bottom = 0);
                            // $pdf->MultiCell($tableSpa11, $tableHeight, $arrayValue[3], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa11, $h = $tableHeight, $arrayValue[3], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            $tableX1 = $tableX1 + $tableSpa11;

                            //$pdfBig->SetXY($tableX1, $tableY1);
                            //$pdfBig->MultiCell($tableSpa22, $tableHeight, 'Semester-'.$arrayValue[1], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            if (in_array($arrayValue[1], $romanArray)) {
                                $semesterValue1 = $arrayValue[1];
                            } else {
                                $semesterValue1 = '';
                            }
                            $pdfBig->MultiCell($w = $tableSpa22, $h = $tableHeight, $semesterValue1, $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            //$pdf->SetXY($tableX1, $tableY1);

                            //$pdf->MultiCell($tableSpa22, $tableHeight, 'Semester-'.$arrayValue[1], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa22, $h = $tableHeight, $semesterValue1, $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');
                            $tableX1 = $tableX1 + $tableSpa22;

                            //$pdfBig->SetXY($tableX1, $tableY1);
                            //$pdfBig->MultiCell($tableSpa33, $tableHeight, $arrayValue[11], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa33, $h = $tableHeight, $arrayValue[11], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            // $pdf->SetXY($tableX1, $tableY1);
                            // $pdf->MultiCell($tableSpa33, $tableHeight, $arrayValue[11], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa33, $h = $tableHeight, $arrayValue[11], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            $tableX1 = $tableX1 + $tableSpa33;

                            //$pdfBig->SetXY($tableX1, $tableY1);
                            //$pdfBig->MultiCell($tableSpa44, $tableHeight, $arrayValue[10], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->setCellPaddings($left = 1, $top = 0, $right = 1, $bottom = 0);
                            $pdf->setCellPaddings($left = 1, $top = 0, $right = 1, $bottom = 0);
                            $pdfBig->MultiCell($w = $tableSpa44, $h = $tableHeight, $arrayValue[10], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            // $pdf->SetXY($tableX1, $tableY1);
                            // $pdf->MultiCell($tableSpa44, $tableHeight, $arrayValue[10], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa44, $h = $tableHeight, $arrayValue[10], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');
                            $pdfBig->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);
                            $pdf->setCellPaddings($left = 0, $top = 0, $right = 0, $bottom = 0);

                            $tableX1 = $tableX1 + $tableSpa44;

                            //$pdfBig->SetXY($tableX1, $tableY1);
                            $pdf->SetXY($tableX1, $tableY1);
                            if (is_numeric($arrayValue[20])) {
                                $crPoint = number_format($arrayValue[20], 2);
                            } else {
                                $crPoint = $arrayValue[20];
                            }
                            //$pdfBig->MultiCell($tableSpa55, $tableHeight, $crPoint , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa55, $h = $tableHeight, $crPoint, $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            // $pdf->MultiCell($tableSpa55, $tableHeight, $crPoint , 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa55, $h = $tableHeight, $crPoint, $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');
                            $tableX1 = $tableX1 + $tableSpa55;

                            //$pdfBig->SetXY($tableX1, $tableY1);
                            //$pdfBig->MultiCell($tableSpa66, $tableHeight, $arrayValue[18], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa66, $h = $tableHeight, $arrayValue[18], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            // $pdf->SetXY($tableX1, $tableY1);
                            // $pdf->MultiCell($tableSpa66, $tableHeight, $arrayValue[18], 'LTB', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa66, $h = $tableHeight, $arrayValue[18], $border = 'LTB', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            $tableX1 = $tableX1 + $tableSpa66;

                            if (is_numeric($arrayValue[19])) {
                                $gpPoint = number_format($arrayValue[19], 2);
                            } else {
                                $gpPoint = $arrayValue[19];
                            }

                            //$pdfBig->SetXY($tableX1, $tableY1);
                            //$pdfBig->MultiCell($tableSpa77, $tableHeight, $gpPoint, 'LTBR', 'C', 0, 0, '', '', true, 0, true);
                            $pdfBig->MultiCell($w = $tableSpa77, $h = $tableHeight, $gpPoint, $border = 'LTBR', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            //$pdf->SetXY($tableX1, $tableY1);
                            //$pdf->MultiCell($tableSpa77, $tableHeight, $gpPoint, 'LTBR', 'C', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell($w = $tableSpa77, $h = $tableHeight, $gpPoint, $border = 'LTBR', $align = 'C', $fill = 0, 1, $x = $tableX1, $y = $tableY1, $reseth = true, $strech = 0, $ishtml = false, $autopadding = true, $maxh = $tableHeight, $valign = 'M');

                            $tableY1 = $tableY1 + $tableHeight;
                            if ($lines > 1) {
                                $tableHeight = $tableHeight - 4;
                                $subCount2++;
                            }

                            $subCount2++;
                        }
                    }

                    //}


                }
            }

            // if($subCount1 <= 45) {
            //     $emptyBoxRow1 = 45 - $subCount1;

            //     for ($emptyBoxRow = 0; $emptyBoxRow <= $emptyBoxRow1; $emptyBoxRow++) {
            //         $tableX = 13;
            //         $tableY = $tableY+$tableHeight;
            //         $pdfBig->SetXY($tableX, $tableY);
            //         $pdfBig->setCellPaddings( $left = 0, $top = 1, $right = 0, $bottom = 0);
            //         $pdfBig->MultiCell($tableSpa1, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $pdf->SetXY($tableX, $tableY);
            //         $pdf->setCellPaddings( $left = 0, $top = 1, $right = 0, $bottom = 0);
            //         $pdf->MultiCell($tableSpa1, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $tableX = $tableX+$tableSpa1;

            //         $pdfBig->SetXY($tableX, $tableY);
            //         $pdfBig->MultiCell($tableSpa2, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $pdf->SetXY($tableX, $tableY);
            //         $pdf->MultiCell($tableSpa2, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $tableX = $tableX+$tableSpa2;

            //         $pdfBig->SetXY($tableX, $tableY);
            //         $pdfBig->MultiCell($tableSpa3, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $pdf->SetXY($tableX, $tableY);
            //         $pdf->MultiCell($tableSpa3, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $tableX = $tableX+$tableSpa3;

            //         $pdfBig->SetXY($tableX, $tableY);
            //         $pdfBig->MultiCell($tableSpa4, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $pdf->SetXY($tableX, $tableY);
            //         $pdf->MultiCell($tableSpa4, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $tableX = $tableX+$tableSpa4;

            //         $pdfBig->SetXY($tableX, $tableY);
            //         $pdfBig->MultiCell($tableSpa5, $tableHeight, '' , 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $pdf->SetXY($tableX, $tableY);
            //         $pdf->MultiCell($tableSpa5, $tableHeight, '' , 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $tableX = $tableX+$tableSpa5;

            //         $pdfBig->SetXY($tableX, $tableY);
            //         $pdfBig->MultiCell($tableSpa6, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $pdf->SetXY($tableX, $tableY);
            //         $pdf->MultiCell($tableSpa6, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $tableX = $tableX+$tableSpa6;

            //         $pdfBig->SetXY($tableX, $tableY);
            //         $pdfBig->MultiCell($tableSpa7, $tableHeight, '', 'LTBR', 'C', 0, 0, '', '', true, 0, true);
            //         $pdf->SetXY($tableX, $tableY);
            //         $pdf->MultiCell($tableSpa7, $tableHeight, '', 'LTBR', 'C', 0, 0, '', '', true, 0, true);

            //     }
            // }


            // if($subCount2 <= 45) {
            //     $emptyBoxRow2 = 45 - $subCount2;

            //     for ($emptyBoxRow11 = 0; $emptyBoxRow11 <= $emptyBoxRow2; $emptyBoxRow11++) {
            //         $tableX1 = 214;
            //         $tableY1 = $tableY1+$tableHeight;
            //         $pdfBig->SetXY($tableX1, $tableY1);
            //         $pdfBig->setCellPaddings( $left = 0, $top = 1, $right = 0, $bottom = 0);
            //         $pdfBig->MultiCell($tableSpa11, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $pdf->SetXY($tableX1, $tableY1);
            //         $pdf->setCellPaddings( $left = 0, $top = 1, $right = 0, $bottom = 0);
            //         $pdf->MultiCell($tableSpa11, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $tableX1 = $tableX1+$tableSpa11;

            //         $pdfBig->SetXY($tableX1, $tableY1);
            //         $pdfBig->MultiCell($tableSpa22, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $pdf->SetXY($tableX1, $tableY1);
            //         $pdf->MultiCell($tableSpa22, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $tableX1 = $tableX1+$tableSpa22;

            //         $pdfBig->SetXY($tableX1, $tableY1);
            //         $pdfBig->MultiCell($tableSpa33, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $pdf->SetXY($tableX1, $tableY1);
            //         $pdf->MultiCell($tableSpa33, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $tableX1 = $tableX1+$tableSpa33;

            //         $pdfBig->SetXY($tableX1, $tableY1);
            //         $pdfBig->MultiCell($tableSpa44, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $pdf->SetXY($tableX1, $tableY1);
            //         $pdf->MultiCell($tableSpa44, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $tableX1 = $tableX1+$tableSpa44;

            //         $pdfBig->SetXY($tableX1, $tableY1);
            //         $pdfBig->MultiCell($tableSpa55, $tableHeight, '' , 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $pdf->SetXY($tableX1, $tableY1);
            //         $pdf->MultiCell($tableSpa55, $tableHeight, '' , 'LTB', 'C', 0, 0, '', '', true, 0, true);

            //         $tableX1 = $tableX1+$tableSpa55;

            //         $pdfBig->SetXY($tableX1, $tableY1);
            //         $pdfBig->MultiCell($tableSpa66, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $pdf->SetXY($tableX1, $tableY1);
            //         $pdf->MultiCell($tableSpa66, $tableHeight, '', 'LTB', 'C', 0, 0, '', '', true, 0, true);
            //         $tableX1 = $tableX1+$tableSpa66;

            //         $pdfBig->SetXY($tableX1, $tableY1);
            //         $pdfBig->MultiCell($tableSpa77, $tableHeight, '', 'LTBR', 'C', 0, 0, '', '', true, 0, true);
            //         $pdf->SetXY($tableX1, $tableY1);
            //         $pdf->MultiCell($tableSpa77, $tableHeight, '', 'LTBR', 'C', 0, 0, '', '', true, 0, true);

            //     }
            // }

            $pdfBig->SetFont($trebuc, '', 9, '', false);
            $pdfBig->SetXY(214, $tableY1 + 2);
            $pdfBig->MultiCell(200, 0, '* END OF THE STATEMENT *', 0, "C", 0, 0, '', '', true, 0, true);


            $pdf->SetFont($trebuc, '', 9, '', false);
            $pdf->SetXY(214, $tableY1 + 2);
            $pdf->MultiCell(200, 0, '* END OF THE STATEMENT *', 0, "C", 0, 0, '', '', true, 0, true);







            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(220, 22);
            $pdfBig->MultiCell(58, 0, "PROGRAMME:", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(220, 22);
            $pdf->MultiCell(58, 0, "PROGRAMME:", 0, "R", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(280, 22);
            $pdfBig->MultiCell(60, 0, $Programme, 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(280, 22);
            $pdf->MultiCell(60, 0, $Programme, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(220, 26);
            $pdfBig->MultiCell(58, 0, "MAJOR:", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(220, 26);
            $pdf->MultiCell(58, 0, "MAJOR:", 0, "R", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(280, 26);
            $pdfBig->MultiCell(95, 0, $major, 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(280, 26);
            $pdf->MultiCell(95, 0, $major, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(220, 30);
            $pdfBig->MultiCell(58, 0, "MINOR:", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(220, 30);
            $pdf->MultiCell(58, 0, "MINOR:", 0, "R", 0, 0, '', '', true, 0, true);


            $minorNew = trim($last[6]);

            if (!empty(trim($minorNew))) {
                $minorN = $minorNew;
            } else {
                $minorN = 'Not Applicable';
            }
            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(280, 30);
            $pdfBig->MultiCell(95, 0, $minorN, 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(280, 30);
            $pdf->MultiCell(95, 0, $minorN, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetXY(220, 34);
            $pdfBig->MultiCell(58, 0, "MEDIUM OF INSTRUCTION:   ", 0, "R", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetXY(220, 34);
            $pdf->MultiCell(58, 0, "MEDIUM OF INSTRUCTION:   ", 0, "R", 0, 0, '', '', true, 0, true);



            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(280, 34);
            $pdfBig->MultiCell(60, 0, 'English', 0, "L", 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(280, 34);
            $pdf->MultiCell(60, 0, 'English', 0, "L", 0, 0, '', '', true, 0, true);





            // table second upper heading data

            // table first under data
            $pdfBig->setCellPaddings($left = 0, $top = 0.8, $right = 0, $bottom = 0);
            $pdf->setCellPaddings($left = 0, $top = 0.8, $right = 0, $bottom = 0);

            $y2 = $tableY + 5.5;
            $secondY2 = $tableY + 5.5;

            $pdfBig->SetFont($trebucb, '', 7, '', false);


            if ($points == "10_2") {
                $pdfBig->setCellPaddings($left = 0, $top = 1.9, $right = 0, $bottom = 0);
                $pdfBig->SetFont($trebucb, '', 7, '', false);
                $pdfBig->SetXY(13, $y2);
                $pdfBig->MultiCell(45, 8, 'Total Credits', 'LT', 'C', 0, 0, '', '', true, 0, true);

                $pdf->setCellPaddings($left = 0, $top = 1.9, $right = 0, $bottom = 0);
                $pdf->SetFont($trebucb, '', 7, '', false);
                $pdf->SetXY(13, $y2);
                $pdf->MultiCell(45, 8, 'Total Credits  ', 'LT', 'C', 0, 0, '', '', true, 0, true);
            } else {
                $pdfBig->setCellPaddings($left = 0, $top = 1.9, $right = 0, $bottom = 0);
                $pdfBig->SetFont($trebucb, '', 7, '', false);
                $pdfBig->SetXY(13, $y2);
                $pdfBig->MultiCell(45, 8, 'Total Earned Credits', 'LT', 'C', 0, 0, '', '', true, 0, true);

                $pdf->setCellPaddings($left = 0, $top = 1.9, $right = 0, $bottom = 0);
                $pdf->SetFont($trebucb, '', 7, '', false);
                $pdf->SetXY(13, $y2);
                $pdf->MultiCell(45, 8, 'Total Earned Credits  ', 'LT', 'C', 0, 0, '', '', true, 0, true);
            }



            $pdfBig->setCellPaddings($left = 0, $top = 1.2, $right = 0, $bottom = 0);
            $pdf->setCellPaddings($left = 0, $top = 1.2, $right = 0, $bottom = 0);

            $pdfBig->SetXY(58, $y2);
            $pdfBig->MultiCell(54, 8, 'Earned Credit Points', 'LT', 'C', 0, 0, '', '', true, 0, true);


            $pdf->SetXY(58, $y2);
            $pdf->MultiCell(54, 8, 'Earned Credit Points', 'LT', 'C', 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($trebucb, '', 5, '', false);
            $pdfBig->SetXY(58, $y2 + 3);
            $pdfBig->MultiCell(54, 8, '∑(Credit X Grade Points)', 0, 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetFont($trebucb, '', 5, '', false);
            $pdf->SetXY(58, $y2 + 3);
            $pdf->MultiCell(54, 8, '∑(Credit X Grade Points)', 0, 'C', 0, 0, '', '', true, 0, true);







            $pdfBig->setCellPaddings($left = 0, $top = 1.9, $right = 0, $bottom = 0);
            $pdfBig->SetFont($trebucb, '', 7, '', false);
            $pdfBig->SetXY(112, $y2);
            $pdfBig->MultiCell(60, 8, 'Cumulative Grade Point Average (CGPA) ', 'LTR', 'C', 0, 0, '', '', true, 0, true);

            $pdf->setCellPaddings($left = 0, $top = 1.9, $right = 0, $bottom = 0);
            $pdf->SetFont($trebucb, '', 7, '', false);
            $pdf->SetXY(112, $y2);
            $pdf->MultiCell(60, 8, 'Cumulative Grade Point Average (CGPA) ', 'LTR', 'C', 0, 0, '', '', true, 0, true);


            $pdfBig->SetXY(13, $y2 = $y2 + 8);
            $pdfBig->MultiCell(45, 7, $last[4], 'LTB', 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(13, $y2);
            $pdf->MultiCell(45, 7, $last[4], 'LTB', 'C', 0, 0, '', '', true, 0, true);


            $pdfBig->SetXY(58, $y2);
            $pdfBig->MultiCell(54, 7, $last[5], 'LTB', 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(58, $y2);
            $pdf->MultiCell(54, 7, $last[5], 'LTB', 'C', 0, 0, '', '', true, 0, true);






            if (is_numeric($last[2])) {
                $cgpa = number_format($last[2], 2);
            } else {
                $cgpa = $last[2];
            }

            // if($cgpa >= 3.50) {
            //     $cgpaRemark = "Excellent";
            // } elseif (3.50 > $cgpa && $cgpa >= 3.00) {
            //     $cgpaRemark = "Very Good";
            // } elseif (3.00 > $cgpa && $cgpa >= 2.50) {
            //     $cgpaRemark = "Good";
            // } elseif (2.50 > $cgpa && $cgpa >= 2.00) {
            //     $cgpaRemark = "Above Average";
            // } elseif (2.00 > $cgpa) {
            //     $cgpaRemark = "Unsatisfactory";
            // } else {
            //     $cgpaRemark ="-";
            // }
            if ($last[3]) {
                $cgpaRemark = ' [' . $last[3] . ']';
            } else {
                $cgpaRemark = '';
            }

            // if($cpgaDescription) {
            //     $cgpaRemark = ' ['.$cpgaDescription .']';
            // } else {
            //     $cgpaRemark = '';
            // }
            //$pdfBig->setCellPaddings( $left = 0, $top = 0.5, $right = 0, $bottom = 0);
            $pdfBig->SetXY(112, $y2);
            $pdfBig->MultiCell(60, 7, $cgpa . $cgpaRemark, 'LTBR', 'C', 0, 0, '', '', true, 0, true);

            // $pdf->setCellPaddings( $left = 0, $top = 0.5, $right = 0, $bottom = 0);
            $pdf->SetXY(112, $y2);
            $pdf->MultiCell(60, 7, $cgpa . $cgpaRemark, 'LTBR', 'C', 0, 0, '', '', true, 0, true);

            // $pdfBig->SetFont($trebuc, '', 6, '', false);
            // $pdfBig->SetXY(14, $y2+6);
            // $pdfBig->MultiCell(60, 7,'CR = Credit | LG = Letter Grade | GP = Grade Point'  , 0, 'L', 0, 0, '', '', true, 0, true);


            // $pdf->SetXY(14, $y2+6);
            // $pdf->MultiCell(60, 7,'CR = Credit | LG = Letter Grade | GP = Grade Point'  , 0, 'L', 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($trebuc, '', 7, '', false);
            $pdfBig->SetXY(120, $y2 + 6);
            $pdfBig->MultiCell(60, 7, 'ISSUE DATE: ' . $doi, 0, 'C', 0, 0, '', '', true, 0, true);


            $pdf->SetXY(120, $y2 + 6);
            $pdf->MultiCell(60, 7, 'ISSUE DATE: ' . $doi, 0, 'C', 0, 0, '', '', true, 0, true);

            //qr code    
            $newPerc = trim($last[7]);
            $dt = date("_ymdHis");
            $str = $GUID . $dt;
            // $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
            // $codeContents = "[".$student_id." - ". $candidate_name ."]"."\n\n" ."\n\n".strtoupper(md5($str));
            $codeContents = "";
            $codeContents = "[" . $student_id . " - " . $candidate_name . "]";
            $codeContents .= "\n";
            $codeContents .= "[CGPA - " . $cgpa . " (" . trim($last[3]) . ")]";
            $codeContents .= "\n";
            $codeContents .= "[Batch - " . $Batch . "]";
            $codeContents .= "\n";
            if ($newPerc) {
                $codeContents .= "[Percentage - " . $newPerc . "%]";
                $codeContents .= "\n";
            } else {
                $codeContents .= "[Percentage - NA]";
                $codeContents .= "\n";
            }
            $codeContents .= $Programme . "(" . $major . ")";

            // Blockchain Code
            $codeContents .= "\n\n";
            $codeData = CoreHelper::getBCVerificationUrl(base64_encode(strtoupper(md5($str))));
            $codeContents .= $codeData;
            // Blockchain Code


            $codeContents .= "\n\n" . strtoupper(md5($str));
            $encryptedString = strtoupper(md5($str));
            $qr_code_path = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\qr\/' . $encryptedString . '.png';
            $qrCodex = 181;
            $qrCodey = 250.5;
            $qrCodeWidth = 25;
            $qrCodeHeight = 25;
            $ecc = 'L';
            $pixel_Size = 1;
            $frame_Size = 1;
            \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);

            // Step 2: Convert transparent PNG to white background
            $im = imagecreatefrompng($qr_code_path);
            $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefill($bg, 0, 0, $white);
            imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
            imagepng($bg, $qr_code_path);
            imagedestroy($im);
            imagedestroy($bg);

            // \QrCode::backgroundColor(255, 255, 0)            
            //     ->format('png')        
            //     ->size(500)    
            //     ->generate($codeContents, $qr_code_path);

            $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);
            $pdf->setPageMark();
            $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false);
            $pdfBig->setPageMark();




            // table first under data
            if ($points == "4") {
                $poi = $points;
            } else {
                $poi = "10";
            }
            if ($newPerc) {
                if ($points == "4") {
                    $newPerc = 'Percentage of marks scored = (CGPA Earned / ' . $poi . ') X 100 = ' . $newPerc . '%';
                } else {
                    $newPerc = 'Percentage of marks scored = (CGPA Earned X ' . $poi . ') = ' . $newPerc . '%';
                }
                // $newPerc = 'Percentage of marks scored = (CGPA / '.$poi.') X 100 = '.$newPerc.'%';
            } else {
                $newPerc = '';
            }
            $pdfBig->SetFont($trebuc, '', 7, '', false);


            $pdfBig->startTransaction();
            $pdfBig->SetFont($trebuc, '', 7, '', false);
            // get the number of lines
            $lines = $pdfBig->MultiCell(193, 0, $remarks, 0, 'J', 0, 0, '', '', true, 0, false, true, 0);
            $pdfBig = $pdfBig->rollbackTransaction(); // restore previous object
            //echo $lines;
            if ($lines == 1) {
                $remarkX = 244;
            } else if ($lines == 2) {
                $remarkX = 240;
            } else if ($lines == 3) {
                $remarkX = 236;
            } else if ($lines == 4) {
                $remarkX = 232;
            } else {
                $remarkX = 244;
            }

            $pdfBig->SetXY(214, $remarkX);
            $pdfBig->MultiCell(193, 0, $remarks . '<br/>' . $newPerc, 0, 'J', 0, 0, '', '', true, 0, true);


            // $pdfBig->SetFont($trebuc, '', 7, '', false);
            //$pdfBig->SetXY(215, 248);
            // if($points=="4") {
            //     $poi = $points;
            // } else {
            //     $poi = "10";
            // }



            // if($newPerc) {
            //     $pdfBig->MultiCell(200, 0,'Percentage of marks scored = (CGPA / '.$poi.') X 100 = '.$newPerc.'%' , 0, 'L', 0, 0, '', '', true, 0, true);
            // }

            $pdf->SetFont($trebuc, '', 7, '', false);
            $pdf->SetXY(214, $remarkX);
            $pdf->MultiCell(193, 0, $remarks . '<br/>' . $newPerc, 0, 'L', 0, 0, '', '', true, 0, true);


            // $pdf->SetFont($trebuc, '', 7, '', false);
            // $pdf->SetXY(215, 248);
            // if($newPerc) {
            //     $pdf->MultiCell(200, 0,'Percentage of marks scored = (CGPA / '.$poi.') X 100 = '.$newPerc.'%' , 0, 'L', 0, 0, '', '', true, 0, true);
            // }

            $newArraySession = [];


            $sign2_x = 220;
            $sign2_y = 253;
            $sign2_Width = 31.75;
            $sign2_Height = 18;
            if (strpos($sign_name2, 'Jasmine Gohil') !== FALSE) {
                $sign2Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\JasmineGohil.png';
            } elseif (strpos($sign_name2, 'Jigisha Patel') !== FALSE) {
                $sign2Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Jigisha_Patel_Sign_new.png';
            } elseif (strpos($sign_name2, 'Suhas Toshniwal') !== FALSE) {
                $sign2Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
            } elseif (strpos($sign_name2, 'Hrishikesh Trivedi') !== FALSE) {
                $sign2Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
            } elseif (strpos($sign_name2, 'Sanjay Bhatnagar') !== FALSE) {
                $sign2Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\sanjay_sign.png';
                $sign2_x = 212;
            } elseif (strpos($sign_name2, 'Brijesh Kumar Singh') !== FALSE) {
                  $sign2Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Brijesh_Kumar_Singh.png';
                $sign2_Width = 27;
                $sign2_y = 247;
                $sign2_Height = 25;
                // $sign1_x = 33;
            } else {
                $sign2Image = "";
            }




            //$pdfBig->image($sign2Image,$sign2_x,$sign2_y,$sign2_Width,$sign2_Height,"",'','L',true,3600);

            $pdfBig->Image($sign2Image, $sign2_x, $sign2_y, $sign2_Width, $sign2_Height, "PNG", '', 'L', true, 3600);
            $pdf->Image($sign2Image, $sign2_x, $sign2_y, $sign2_Width, $sign2_Height, "PNG", '', 'L', true, 3600);
            $pdfBig->setPageMark();
            $pdf->setPageMark();

            if ($sign_name2 == "Ms. Jigisha Patel" || $sign_name2 == "Prof. Suhas Toshniwal" || $sign_name2 == "Brijesh Kumar Singh" || $sign_name2 == "Prof. Jasmine Gohil" || $sign_name2 == "Jigisha Patel" || $sign_name2 == "Suhas Toshniwal" || $sign_name2 == "Jasmine Gohil" || $sign_name2 == "Hrishikesh Trivedi" || $sign_name2 == "Sanjay Bhatnagar") {
                $pdfBig->SetFont($trebuc, '', 8, '', false);

                $pdfBig->SetXY(220, 269);
                $pdfBig->MultiCell(0, 0, $sign_name2, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetXY(220, 272);
                $pdfBig->MultiCell(0, 0, $designation2, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetXY(220, 275);
                $pdfBig->MultiCell(0, 0, $role2, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetFont($trebuc, '', 8, '', false);

                $pdf->SetXY(220, 269);
                $pdf->MultiCell(0, 0, $sign_name2, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(220, 272);
                $pdf->MultiCell(0, 0, $designation2, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(220, 275);
                $pdf->MultiCell(0, 0, $role2, 0, 'L', 0, 0, '', '', true, 0, true);
            }

            $sign3_x = 280;
            $sign3_y = 253;
            $sign3_Width = 31.75;
            $sign3_Height = 18;
            if (strpos($sign_name3, 'Jasmine Gohil') !== FALSE) {
                $sign3Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\JasmineGohil.png';
            } elseif (strpos($sign_name3, 'Jigisha Patel') !== FALSE) {
                $sign3Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Jigisha_Patel_Sign_new.png';
            } elseif (strpos($sign_name3, 'Suhas Toshniwal') !== FALSE) {
                $sign3Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
            } elseif (strpos($sign_name3, 'Hrishikesh Trivedi') !== FALSE) {
                $sign3Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
            } elseif (strpos($sign_name3, 'Sanjay Bhatnagar') !== FALSE) {
                $sign3Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\sanjay_sign.png';
                $sign3_x = 272;
            } elseif (strpos($sign_name3, 'Brijesh Kumar Singh') !== FALSE) {
                 $sign3Image= public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Brijesh_Kumar_Singh.png';
                $sign3_Width = 27;
                $sign3_Height = 25;
                 $sign3_y = 247;
                // $sign3_x = 33;
            } else {
                $sign3Image = "";
            }

            //$pdfBig->image($sign3Image,$sign3_x,$sign3_y,$sign3_Width,$sign3_Height,"",'','L',true,3600);

            $pdfBig->Image($sign3Image, $sign3_x, $sign3_y, $sign3_Width, $sign3_Height, "PNG", '', 'L', true, 3600);
            $pdf->Image($sign3Image, $sign3_x, $sign3_y, $sign3_Width, $sign3_Height, "PNG", '', 'L', true, 3600);
            $pdfBig->setPageMark();
            $pdf->setPageMark();

            if ($sign_name3 == "Ms. Jigisha Patel" || $sign_name3 == "Prof. Suhas Toshniwal" || $sign_name3 == "Brijesh Kumar Singh" || $sign_name3 == "Prof. Jasmine Gohil" || $sign_name3 == "Jigisha Patel" || $sign_name3 == "Suhas Toshniwal" || $sign_name3 == "Jasmine Gohil" || $sign_name3 == "Hrishikesh Trivedi" || $sign_name3 == "Sanjay Bhatnagar") {

                $pdfBig->SetXY(280, 269);
                $pdfBig->MultiCell(0, 0, $sign_name3, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetXY(280, 272);
                $pdfBig->MultiCell(0, 0, $designation3, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetXY(280, 275);
                $pdfBig->MultiCell(0, 0, $role3, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(280, 269);
                $pdf->MultiCell(0, 0, $sign_name3, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(280, 272);
                $pdf->MultiCell(0, 0, $designation3, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(280, 275);
                $pdf->MultiCell(0, 0, $role3, 0, 'L', 0, 0, '', '', true, 0, true);
            }

            $sign1_x = 340;
            $sign1_y = 253;
            $sign1_Width = 31.75;
            $sign1_Height = 18;
            if (strpos($sign_name1, 'Jasmine Gohil') !== FALSE) {
                $sign1Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\JasmineGohil.png';
            } elseif (strpos($sign_name1, 'Jigisha Patel') !== FALSE) {
                $sign1Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Jigisha_Patel_Sign_new.png';
            } elseif (strpos($sign_name1, 'Suhas Toshniwal') !== FALSE) {
                $sign1Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Suhas_Toshniwal_Sign_new.png';
            } elseif (strpos($sign_name1, 'Hrishikesh Trivedi') !== FALSE) {
                $sign1Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Hrishikesh_Trivedi_Sign_new.png';
            } elseif (strpos($sign_name1, 'Sanjay Bhatnagar') !== FALSE) {
                $sign1Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\sanjay_sign.png';
                $sign1_x = 332;
            } elseif (strpos($sign_name1, 'Brijesh Kumar Singh') !== FALSE) {
                // dd($sign_name1);
                $sign1Image = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\Brijesh_Kumar_Singh.png';
                // dd($sign1);
                // $sign1_x = 50;
                $sign1_y = 247;
                $sign1_Width = 27;
                $sign1_Height = 25;
            } else {
                $sign1Image = "";
            }

            //$pdfBig->image($sign1Image,$sign1_x,$sign1_y,$sign1_Width,$sign1_Height,"",'','L',true,3600);

            //$pdfBig->image($sign3, $sign3_x, $sign3_y, $sign3_Width, $sign3_Height, "", '', 'L', true, 3600);
            // $pdfBig->setPageMark();

             $pdfBig->Image($sign1Image, $sign1_x, $sign1_y, $sign1_Width, $sign1_Height, "PNG", '', 'L', true, 3600);
            $pdf->Image($sign1Image, $sign1_x, $sign1_y, $sign1_Width, $sign1_Height, "PNG", '', 'L', true, 3600);
            $pdfBig->setPageMark();
            $pdf->setPageMark();

            if ($sign_name1 == "Ms. Jigisha Patel" || $sign_name1 == "Prof. Suhas Toshniwal" || $sign_name1 == "Prof. Jasmine Gohil" || $sign_name1 == "Jigisha Patel" || $sign_name1 == "Suhas Toshniwal" || $sign_name1 == "Jasmine Gohil" || $sign_name1 == "Hrishikesh Trivedi" || $sign_name1 == "Sanjay Bhatnagar" || $sign_name1 == "Brijesh Kumar Singh") {

                $pdfBig->SetXY(340, 269);
                $pdfBig->MultiCell(0, 0, $sign_name1, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetXY(340, 272);
                $pdfBig->MultiCell(0, 0, $designation1, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetXY(340, 275);
                $pdfBig->MultiCell(0, 0, $role1, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(340, 269);
                $pdf->MultiCell(0, 0, $sign_name1, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(340, 272);
                $pdf->MultiCell(0, 0, $designation1, 0, 'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(340, 275);
                $pdf->MultiCell(0, 0, $role1, 0, 'L', 0, 0, '', '', true, 0, true);
            }


            $pdfBig->setCellPaddings($left = 1, $top = 1, $right = 1, $bottom = 0);
            $pdf->setCellPaddings($left = 1, $top = 1, $right = 1, $bottom = 0);

            // Bottom Blockchain URL Link in image By rohit 21-07-2025
            $verifyLink = $codeData;
            $logo_path_org = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\logo.png';
            // set profile image   
            $logox = 329;
            $logoy = 281;
            $logoWidth = 8;
            $logoHeight = 8;

            // pass $verifyLink in the link parameter
            $pdf->image($logo_path_org, $logox, $logoy, $logoWidth, $logoHeight, '', $verifyLink, '', true, 3600);
            $pdf->setPageMark();

            $pdf->SetFont($arial, '', 9, '', false);
            $pdf->SetXY(280, 282);
            $pdf->MultiCell(0, 0, 'Click here to verify on Blockchain', 0, 'L', 0, 0, '', '', true, 0, true);


            $nameOrg = $candidate_name;
            // Ghost image
            $ghost_font_size = '13';
            $ghostImagex = 13;
            $ghostImagey = 268.5;
            //$ghostImageWidth = 55; //68
            //$ghostImageHeight = 9.8;
            $ghostImageWidth = 39.405983333;
            $ghostImageHeight = 10;
            $name = substr(str_replace(' ', '', strtoupper($nameOrg)), 0, 6);
            $tmpDir = $this->createTemp(public_path() . '/backend/images/ghosttemp/temp');

            $w = $this->CreateMessage($tmpDir, $name, $ghost_font_size, '');

            //$pdfBig->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);
            //$pdf->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $ghostImageWidth, $ghostImageHeight, "PNG", '', 'L', true, 3600);

            //$pdfBig->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
            //$pdf->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);


            /*Point Page Start*/
            // Start Back pdfbig
            $pdfBig->AddPage();
            $back_img = public_path() . '\\' . $subdomain[0] . '\backend\canvas\bg_images\\anu_a3_transcript_back.jpg';

            if ($previewPdf == 1) {
                if ($previewWithoutBg != 1) {
                    $pdfBig->Image($back_img, 0, 0, '420', '297', "JPG", '', 'R', true);
                }
            }
            $pdfBig->setPageMark();

            //$pdfBig->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));
            $pdfBig->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => array(109, 113, 115)));
            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $pdfBig->SetXY(210, 20);
            $pdfBig->MultiCell(1, 225, '', 'L', "L", 0, 0, '', '', true, 0, true);
            $pdfBig->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));

            if ($points == "4") {
                $grade_title = 'Grade Points: <span style="font-size:10;font-family:' . $trebuc . ';">4 point grading system with corresponding numeric grade & letter grade as given below</span>';
                $grades_count = 13;
                $grades = array(
                    array("96-100", "A+", "4.00", "Distinguished"),
                    array("91-95", "A", "3.80", "Excellent"),
                    array("86-90", "A-", "3.60", "Very Good"),
                    array("81-85", "B+", "3.40", "Good"),
                    array("76-80", "B", "3.20", "High satisfactory"),
                    array("71-75", "B-", "3.00", "Above satisfactory"),
                    array("66-70", "C+", "2.80", "Satisfactory"),
                    array("61-65", "C", "2.60", "Less than satisfactory"),
                    array("56-60", "C-", "2.40", "Low satisfactory"),
                    array("50-55", "D", "2.00", "Poor"),
                    array("Below 50", "F", "0.00", "Fail"),
                    array("Non-Credit", "NC", "--", ""),
                    array("Pass", "P", "--", "")
                );
                $cgpa_count = 5;
                $cgpa_class = array(
                    array("GPA ≥ 3.50", "Excellent"),
                    array("3.50 > GPA ≥ 3.00", "Very Good"),
                    array("3.00 > GPA ≥ 2.50", "Good"),
                    array("2.50 > GPA ≥ 2.00", "Above Average"),
                    array("2.00 > GPA", "Unsatisfactory")
                );
                /*$list_count = 3;
				$list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (GPA/4) * 100"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;50% is passing criteria in each course."),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);	*/
                $list_count = 2;
                $list = array(
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (GPA/4) * 100"),
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
                );
            } elseif ($points == "10") {
                $grade_title = 'Grade Points: <span style="font-size:10;font-family:' . $trebuc . ';">10 point grading system with corresponding numeric grade & letter grade as given below</span>';
                $grades_count = 9;
                $grades = array(
                    array("91-100", "A+", "10.00"),
                    array("81-90", "A", "9.00"),
                    array("71-80", "B+", "8.00"),
                    array("61-70", "B", "7.00"),
                    array("56-60", "C+", "6.00"),
                    array("50-55", "C", "5.00"),
                    array("G(Grace)", "As per University norms", "5.00"),
                    array("Below 50", "F", "0.00"),
                    array("Absent (AB)", "AB", "0.00")
                );
                $cgpa_count = 4;
                $cgpa_class = array(
                    array("7.5 and above", "First class with honor"),
                    array("6.5 to 7.49", "First class"),
                    array("5.0 to 6.49", "Second class"),
                    array("Below 5.0", "Fail")
                );
                /*$list_count = 3;
				$list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;50% is passing criteria in each course."),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);*/
                $list_count = 2;
                $list = array(
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
                );
            } elseif ($points == "10_2") {
                $grade_title = 'Grade Points: <span style="font-size:10;font-family:' . $trebuc . ';">10 point grading system with corresponding numeric grade & letter grade as given below</span>';
                $grades_count = 10;
                $grades = array(
                    array("91-100", "A+", "10.00", "Distinguished"),
                    array("81-90", "A", "9.00", "Excellent"),
                    array("71-80", "B+", "8.00", "Very Good"),
                    array("61-70", "B", "7.00", "Good"),
                    array("56-60", "C+", "6.00", "High satisfactory"),
                    array("50-55", "C", "5.00", "Satisfactory"),
                    array("Below 50", "F", "0.00", "Fail"),
                    array("Absent (AB)", "AB", "0.00", "Absent"),
                    array("Non-Credit", "NC", "--", ""),
                    array("Pass", "P", "--", "Pass")
                );
                $cgpa_count = 5;
                $cgpa_class = array(
                    array("8.75 and above", "Excellent"),
                    array("8.75 to 7.50", "Very Good"),
                    array("7.50 to 6.25", "Good"),
                    array("6.25 to 5.00", "Above Average"),
                    array("Below 5.00", "Unsatisfactory")
                );
                /*$list_count = 3;
				$list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;50% is passing criteria in each course."),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);*/
                $list_count = 2;
                $list = array(
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
                );
            }
            $denote_count = 10;
            $denote = array(
                array("(*)", "= Repeat Course"),
                array("#", "= Audit Course"),
                array("(E)", "= Elective Course"),
                array("(EM)", "= Elective Under Minor Course"),
                array("P", "= Pass"),
                array("NC", "= Non-Credit Course"),
                array("CR", "= Credit"),
                array("LG", "= Letter Grade"),
                array("GP", "= Grade Point"),
                array("CGPA", "= Cumulative Grade Point Average")
            );
            // $note="Note - The grades obtained in an audit course are not considered in the calculation of SGPA or CGPA however they are counted towards the total number of credits earned.";
            $note = "Note: The grades obtained in an audit course are not considered in the calculation of SGPA or CGPA; however, they are counted towards the total number of credits earned.";

            $university_name = 'A N A N T&nbsp;&nbsp;&nbsp;N A T I O N A L&nbsp;&nbsp;&nbsp;U N I V E R S I T Y';
            $line2 = 'Sanskardham Campus, Bopal - Ghuma - Sanand Road, Ahmedabad - 382115, Gujarat';
            $line3 = 'Email: registrar@anu.edu.in | Website: www.anu.edu.in';

            $pdfBig->SetFont($trebucb, '', 10, '', false);
            $pdfBig->SetTextColor(0, 0, 0);
            $pdfBig->SetXY(12, 25);
            $pdfBig->MultiCell(0, 0, $grade_title, 0, 'L', 0, 0, '', '', true, 0, true);
            if ($points == "4") {
                $pdfBig->SetFont($trebucb, '', 10, '', false);
                $pdfBig->SetXY(55, 33);
                $pdfBig->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdfBig->MultiCell(22, 11, 'Numeric<br />Grade', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->MultiCell(17, 11, 'Letter<br />Grade', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '1.5', $right = '', $bottom = '');
                $pdfBig->MultiCell(25, 11, 'Grade Point<br><span style="font-size:9;font-family:' . $trebucb . ';">(4.0 pt scale)</span>', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdfBig->MultiCell(40, 11, 'Descriptive<br />Performance', 'LRTB',  'L', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
                $pdfBig->SetFont($trebuc, '', 10, '', false);
                $y_start = 44;
                for ($grow = 0; $grow < $grades_count; $grow++) {
                    $pdfBig->SetXY(55, $y_start);
                    $pdfBig->MultiCell(22, 5, $grades[$grow][0], 'LRTB', 'C', 0, 0);
                    $pdfBig->MultiCell(17, 5, $grades[$grow][1], 'LRTB', 'C', 0, 0);
                    $pdfBig->MultiCell(25, 5, $grades[$grow][2], 'LRTB', 'C', 0, 0);
                    $pdfBig->MultiCell(40, 5, $grades[$grow][3], 'LRTB', 'L', 0, 0);
                    $y_start = $y_start + 5;
                }

                $pdfBig->SetFont($trebucb, '', 10, '', false);
                $pdfBig->SetXY(55, $y_start + 3);
                $pdfBig->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0, '', '', true, 0, true);
                $pdfBig->MultiCell(54, 5, '', 'LRTB',  'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetXY(55, $y_start + 3);
                $pdfBig->MultiCell(50, 5, 'CGPA', 0, 'R', 0, 0, '', '', true, 0, true);
                $pdfBig->SetXY(106, $y_start + 3);
                $pdfBig->MultiCell(54, 5, 'Award of Class', 0,  'L', 0, 0, '', '', true, 0, true);

                $pdfBig->SetFont($trebuc, '', 10, '', false);
                $cy_start = $y_start + 8;
                for ($crow = 0; $crow < $cgpa_count; $crow++) {
                    $pdfBig->SetXY(55, $cy_start);
                    $pdfBig->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0);
                    $pdfBig->MultiCell(54, 5, '', 'LRTB', 'L', 0, 0);

                    $pdfBig->SetXY(55, $cy_start);
                    $pdfBig->MultiCell(49, 5, $cgpa_class[$crow][0], 0, 'R', 0, 0);
                    $pdfBig->SetXY(106, $cy_start);
                    $pdfBig->MultiCell(54, 5, $cgpa_class[$crow][1], 0, 'L', 0, 0);
                    $cy_start = $cy_start + 5;
                }
            } elseif ($points == "10") {
                $pdfBig->SetFont($trebucb, '', 10, '', false);
                $pdfBig->SetXY(55, 33);
                $pdfBig->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdfBig->MultiCell(29, 11, 'Numeric<br />Grade', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '2', $right = '', $bottom = '');
                $pdfBig->MultiCell(46, 11, 'Letter Grade', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '1.5', $right = '', $bottom = '');
                $pdfBig->MultiCell(29, 11, 'Grade Point<br><span style="font-size:9;font-family:' . $trebucb . ';">(10 pt scale)</span>', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
                $pdfBig->SetFont($trebuc, '', 10, '', false);
                $y_start = 44;
                for ($grow = 0; $grow < $grades_count; $grow++) {
                    $pdfBig->SetXY(55, $y_start);
                    $pdfBig->MultiCell(29, 5, $grades[$grow][0], 'LRTB', 'C', 0, 0);
                    $pdfBig->MultiCell(46, 5, $grades[$grow][1], 'LRTB', 'C', 0, 0);
                    $pdfBig->MultiCell(29, 5, $grades[$grow][2], 'LRTB', 'C', 0, 0);
                    $y_start = $y_start + 5;
                }

                $pdfBig->SetFont($trebucb, '', 10, '', false);
                $pdfBig->SetXY(55, $y_start + 3);
                $pdfBig->MultiCell(50, 5, 'CGPA', 'LRTB', 'R', 0, 0, '', '', true, 0, true);
                $pdfBig->MultiCell(54, 5, 'Award of Class', 'LRTB',  'L', 0, 0, '', '', true, 0, true);
                $pdfBig->SetFont($trebuc, '', 10, '', false);
                $cy_start = $y_start + 8;
                for ($crow = 0; $crow < $cgpa_count; $crow++) {
                    $pdfBig->SetXY(55, $cy_start);
                    $pdfBig->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0);
                    $pdfBig->MultiCell(54, 5, '', 'LRTB', 'L', 0, 0);

                    $pdfBig->SetXY(55, $cy_start);
                    $pdfBig->MultiCell(49, 5, $cgpa_class[$crow][0], 0, 'R', 0, 0);
                    $pdfBig->SetXY(106, $cy_start);
                    $pdfBig->MultiCell(54, 5, $cgpa_class[$crow][1], 0, 'L', 0, 0);
                    $cy_start = $cy_start + 5;
                }
            } elseif ($points == "10_2") {
                $pdfBig->SetFont($trebucb, '', 10, '', false);
                $pdfBig->SetXY(55, 33);
                $pdfBig->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdfBig->MultiCell(22, 11, 'Numeric<br />Grade', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '2', $right = '', $bottom = '');
                $pdfBig->MultiCell(17, 11, 'Letter Grade', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '1.5', $right = '', $bottom = '');
                $pdfBig->MultiCell(25, 11, 'Grade Point<br><span style="font-size:9;font-family:' . $trebucb . ';">(10 pt scale)</span>', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdfBig->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdfBig->MultiCell(40, 11, 'Descriptive<br />Performance', 'LRTB',  'L', 0, 0, '', '', true, 0, true);

                $pdfBig->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
                $pdfBig->SetFont($trebuc, '', 10, '', false);
                $y_start = 44;
                for ($grow = 0; $grow < $grades_count; $grow++) {
                    $pdfBig->SetXY(55, $y_start);
                    $pdfBig->MultiCell(22, 5, $grades[$grow][0], 'LRTB', 'C', 0, 0);
                    $pdfBig->MultiCell(17, 5, $grades[$grow][1], 'LRTB', 'C', 0, 0);
                    $pdfBig->MultiCell(25, 5, $grades[$grow][2], 'LRTB', 'C', 0, 0);
                    $pdfBig->MultiCell(40, 5, $grades[$grow][3], 'LRTB', 'C', 0, 0);
                    $y_start = $y_start + 5;
                }

                $pdfBig->SetFont($trebucb, '', 10, '', false);
                $pdfBig->SetXY(55, $y_start + 3);
                $pdfBig->MultiCell(50, 5, 'CGPA', 'LRTB', 'R', 0, 0, '', '', true, 0, true);
                $pdfBig->MultiCell(54, 5, 'Award of Class', 'LRTB',  'L', 0, 0, '', '', true, 0, true);
                $pdfBig->SetFont($trebuc, '', 10, '', false);
                $cy_start = $y_start + 8;
                for ($crow = 0; $crow < $cgpa_count; $crow++) {
                    $pdfBig->SetXY(55, $cy_start);
                    $pdfBig->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0);
                    $pdfBig->MultiCell(54, 5, '', 'LRTB', 'L', 0, 0);

                    $pdfBig->SetXY(55, $cy_start);
                    $pdfBig->MultiCell(49, 5, $cgpa_class[$crow][0], 0, 'R', 0, 0);
                    $pdfBig->SetXY(106, $cy_start);
                    $pdfBig->MultiCell(54, 5, $cgpa_class[$crow][1], 0, 'L', 0, 0);
                    $cy_start = $cy_start + 5;
                }
            }

            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $ly_start = $cy_start + 5;
            for ($lrow = 0; $lrow < $list_count; $lrow++) {
                $pdfBig->SetXY(13, $ly_start);
                $pdfBig->MultiCell(0, 7, $list[$lrow][0], 0, 'L', 0, 0, '', '', true, 0, true);
                $ly_start = $ly_start + 9;
            }
            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $dy_start = $ly_start + 3;
            for ($drow = 0; $drow < $denote_count; $drow++) {
                $pdfBig->SetXY(20.5, $dy_start);
                $pdfBig->MultiCell(15, 5, $denote[$drow][0], 0, 'L', 0, 0);
                $pdfBig->MultiCell(0, 5, $denote[$drow][1], 0, 'L', 0, 0);
                $dy_start = $dy_start + 5;
            }
            $pdfBig->SetFont($trebuc, '', 10, '', false);
            $n_start = $dy_start + 5;
            $pdfBig->SetXY(13, $n_start);
            $pdfBig->MultiCell(200, 0, $note, 0, 'L', 0, 0, '', '', true, 0, true);
            // back page
            // End Back pdfbig


            // Start Back pdf
            $pdf->AddPage();
            $back_img = public_path() . '\\' . $subdomain[0] . '\backend\canvas\bg_images\\anu_a3_transcript_back.jpg';

            if ($previewPdf != 1) {
                $pdf->Image($back_img, 0, 0, '420', '297', "JPG", '', 'R', true);
            }
            $pdf->setPageMark();

            //$pdf->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));
            $pdf->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => array(109, 113, 115)));
            $pdf->SetFont($trebuc, '', 10, '', false);
            $pdf->SetXY(210, 20);
            $pdf->MultiCell(1, 225, '', 'L', "L", 0, 0, '', '', true, 0, true);
            $pdf->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(109, 113, 115)));

            if ($points == "4") {
                $grade_title = 'Grade Points: <span style="font-size:10;font-family:' . $trebuc . ';">4 point grading system with corresponding numeric grade & letter grade as given below</span>';
                $grades_count = 13;
                $grades = array(
                    array("96-100", "A+", "4.00", "Distinguished"),
                    array("91-95", "A", "3.80", "Excellent"),
                    array("86-90", "A-", "3.60", "Very Good"),
                    array("81-85", "B+", "3.40", "Good"),
                    array("76-80", "B", "3.20", "High satisfactory"),
                    array("71-75", "B-", "3.00", "Above satisfactory"),
                    array("66-70", "C+", "2.80", "Satisfactory"),
                    array("61-65", "C", "2.60", "Less than satisfactory"),
                    array("56-60", "C-", "2.40", "Low satisfactory"),
                    array("50-55", "D", "2.00", "Poor"),
                    array("Below 50", "F", "0.00", "Fail"),
                    array("Non-Credit", "NC", "--", ""),
                    array("Pass", "P", "--", "")
                );
                $cgpa_count = 5;
                $cgpa_class = array(
                    array("GPA ≥ 3.50", "Excellent"),
                    array("3.50 > GPA ≥ 3.00", "Very Good"),
                    array("3.00 > GPA ≥ 2.50", "Good"),
                    array("2.50 > GPA ≥ 2.00", "Above Average"),
                    array("2.00 > GPA", "Unsatisfactory")
                );
                /*$list_count = 3;
				$list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (GPA/4) * 100"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;50% is passing criteria in each course."),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);	*/
                $list_count = 2;
                $list = array(
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (GPA/4) * 100"),
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
                );
            } elseif ($points == "10") {
                $grade_title = 'Grade Points: <span style="font-size:10;font-family:' . $trebuc . ';">10 point grading system with corresponding numeric grade & letter grade as given below</span>';
                $grades_count = 9;
                $grades = array(
                    array("91-100", "A+", "10.00"),
                    array("81-90", "A", "9.00"),
                    array("71-80", "B+", "8.00"),
                    array("61-70", "B", "7.00"),
                    array("56-60", "C+", "6.00"),
                    array("50-55", "C", "5.00"),
                    array("G(Grace)", "As per University norms", "5.00"),
                    array("Below 50", "F", "0.00"),
                    array("Absent (AB)", "AB", "0.00")
                );
                $cgpa_count = 4;
                $cgpa_class = array(
                    array("7.5 and above", "First class with honor"),
                    array("6.5 to 7.49", "First class"),
                    array("5.0 to 6.49", "Second class"),
                    array("Below 5.0", "Fail")
                );
                /*$list_count = 3;
				$list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;50% is passing criteria in each course."),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);*/
                $list_count = 2;
                $list = array(
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
                );
            } elseif ($points == "10_2") {
                $grade_title = 'Grade Points: <span style="font-size:10;font-family:' . $trebuc . ';">10 point grading system with corresponding numeric grade & letter grade as given below</span>';
                $grades_count = 10;
                $grades = array(
                    array("91-100", "A+", "10.00", "Distinguished"),
                    array("81-90", "A", "9.00", "Excellent"),
                    array("71-80", "B+", "8.00", "Very Good"),
                    array("61-70", "B", "7.00", "Good"),
                    array("56-60", "C+", "6.00", "High satisfactory"),
                    array("50-55", "C", "5.00", "Satisfactory"),
                    array("Below 50", "F", "0.00", "Fail"),
                    array("Absent (AB)", "AB", "0.00", "Absent"),
                    array("Non-Credit", "NC", "--", ""),
                    array("Pass", "P", "--", "Pass")
                );
                $cgpa_count = 5;
                $cgpa_class = array(
                    array("8.75 and above", "Excellent"),
                    array("8.75 to 7.50", "Very Good"),
                    array("7.50 to 6.25", "Good"),
                    array("6.25 to 5.00", "Above Average"),
                    array("Below 5.00", "Unsatisfactory")
                );
                /*$list_count = 3;
				$list = array (
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;50% is passing criteria in each course."),
					array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
				);*/
                $list_count = 2;
                $list = array(
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Percentage of marks scored = (CGPA Earned x 10)"),
                    array("●&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MEDIUM OF INSTRUCTIONS: ENGLISH")
                );
            }
            $denote_count = 10;
            $denote = array(
                array("(*)", "= Repeat Course"),
                array("#", "= Audit Course"),
                array("(E)", "= Elective Course"),
                array("(EM)", "= Elective Under Minor Course"),
                array("P", "= Pass"),
                array("NC", "= Non-Credit Course"),
                array("CR", "= Credit"),
                array("LG", "= Letter Grade"),
                array("GP", "= Grade Point"),
                array("CGPA", "= Cumulative Grade Point Average")
            );
            // $note="Note - The grades obtained in an audit course are not considered in the calculation of SGPA or CGPA however they are counted towards the total number of credits earned.";
            $note = "Note: The grades obtained in an audit course are not considered in the calculation of SGPA or CGPA; however, they are counted towards the total number of credits earned.";

            $university_name = 'A N A N T&nbsp;&nbsp;&nbsp;N A T I O N A L&nbsp;&nbsp;&nbsp;U N I V E R S I T Y';
            $line2 = 'Sanskardham Campus, Bopal - Ghuma - Sanand Road, Ahmedabad - 382115, Gujarat';
            $line3 = 'Email: registrar@anu.edu.in | Website: www.anu.edu.in';

            $pdf->SetFont($trebucb, '', 10, '', false);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(12, 25);
            $pdf->MultiCell(0, 0, $grade_title, 0, 'L', 0, 0, '', '', true, 0, true);
            if ($points == "4") {
                $pdf->SetFont($trebucb, '', 10, '', false);
                $pdf->SetXY(55, 33);
                $pdf->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdf->MultiCell(22, 11, 'Numeric<br />Grade', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
                $pdf->MultiCell(17, 11, 'Letter<br />Grade', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '1.5', $right = '', $bottom = '');
                $pdf->MultiCell(25, 11, 'Grade Point<br><span style="font-size:9;font-family:' . $trebucb . ';">(4.0 pt scale)</span>', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdf->MultiCell(40, 11, 'Descriptive<br />Performance', 'LRTB',  'L', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
                $pdf->SetFont($trebuc, '', 10, '', false);
                $y_start = 44;
                for ($grow = 0; $grow < $grades_count; $grow++) {
                    $pdf->SetXY(55, $y_start);
                    $pdf->MultiCell(22, 5, $grades[$grow][0], 'LRTB', 'C', 0, 0);
                    $pdf->MultiCell(17, 5, $grades[$grow][1], 'LRTB', 'C', 0, 0);
                    $pdf->MultiCell(25, 5, $grades[$grow][2], 'LRTB', 'C', 0, 0);
                    $pdf->MultiCell(40, 5, $grades[$grow][3], 'LRTB', 'L', 0, 0);
                    $y_start = $y_start + 5;
                }

                $pdf->SetFont($trebucb, '', 10, '', false);
                $pdf->SetXY(55, $y_start + 3);
                $pdf->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0, '', '', true, 0, true);
                $pdf->MultiCell(54, 5, '', 'LRTB',  'L', 0, 0, '', '', true, 0, true);

                $pdf->SetXY(55, $y_start + 3);
                $pdf->MultiCell(50, 5, 'CGPA', 0, 'R', 0, 0, '', '', true, 0, true);
                $pdf->SetXY(106, $y_start + 3);
                $pdf->MultiCell(54, 5, 'Award of Class', 0,  'L', 0, 0, '', '', true, 0, true);

                $pdf->SetFont($trebuc, '', 10, '', false);
                $cy_start = $y_start + 8;
                for ($crow = 0; $crow < $cgpa_count; $crow++) {
                    $pdf->SetXY(55, $cy_start);
                    $pdf->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0);
                    $pdf->MultiCell(54, 5, '', 'LRTB', 'L', 0, 0);

                    $pdf->SetXY(55, $cy_start);
                    $pdf->MultiCell(49, 5, $cgpa_class[$crow][0], 0, 'R', 0, 0);
                    $pdf->SetXY(106, $cy_start);
                    $pdf->MultiCell(54, 5, $cgpa_class[$crow][1], 0, 'L', 0, 0);
                    $cy_start = $cy_start + 5;
                }
            } elseif ($points == "10") {
                $pdf->SetFont($trebucb, '', 10, '', false);
                $pdf->SetXY(55, 33);
                $pdf->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdf->MultiCell(29, 11, 'Numeric<br />Grade', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '2', $right = '', $bottom = '');
                $pdf->MultiCell(46, 11, 'Letter Grade', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '1.5', $right = '', $bottom = '');
                $pdf->MultiCell(29, 11, 'Grade Point<br><span style="font-size:9;font-family:' . $trebucb . ';">(10 pt scale)</span>', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
                $pdf->SetFont($trebuc, '', 10, '', false);
                $y_start = 44;
                for ($grow = 0; $grow < $grades_count; $grow++) {
                    $pdf->SetXY(55, $y_start);
                    $pdf->MultiCell(29, 5, $grades[$grow][0], 'LRTB', 'C', 0, 0);
                    $pdf->MultiCell(46, 5, $grades[$grow][1], 'LRTB', 'C', 0, 0);
                    $pdf->MultiCell(29, 5, $grades[$grow][2], 'LRTB', 'C', 0, 0);
                    $y_start = $y_start + 5;
                }

                $pdf->SetFont($trebucb, '', 10, '', false);
                $pdf->SetXY(55, $y_start + 3);
                $pdf->MultiCell(50, 5, 'CGPA', 'LRTB', 'R', 0, 0, '', '', true, 0, true);
                $pdf->MultiCell(54, 5, 'Award of Class', 'LRTB',  'L', 0, 0, '', '', true, 0, true);
                $pdf->SetFont($trebuc, '', 10, '', false);
                $cy_start = $y_start + 8;
                for ($crow = 0; $crow < $cgpa_count; $crow++) {
                    $pdf->SetXY(55, $cy_start);
                    $pdf->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0);
                    $pdf->MultiCell(54, 5, '', 'LRTB', 'L', 0, 0);

                    $pdf->SetXY(55, $cy_start);
                    $pdf->MultiCell(49, 5, $cgpa_class[$crow][0], 0, 'R', 0, 0);
                    $pdf->SetXY(106, $cy_start);
                    $pdf->MultiCell(54, 5, $cgpa_class[$crow][1], 0, 'L', 0, 0);
                    $cy_start = $cy_start + 5;
                }
            } elseif ($points == "10_2") {
                $pdf->SetFont($trebucb, '', 10, '', false);
                $pdf->SetXY(55, 33);
                $pdf->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdf->MultiCell(22, 11, 'Numeric<br />Grade', 'LRTB', 'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '2', $right = '', $bottom = '');
                $pdf->MultiCell(17, 11, 'Letter Grade', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '1.5', $right = '', $bottom = '');
                $pdf->MultiCell(25, 11, 'Grade Point<br><span style="font-size:9;font-family:' . $trebucb . ';">(10 pt scale)</span>', 'LRTB',  'C', 0, 0, '', '', true, 0, true);
                $pdf->setCellPaddings($left = '', $top = '1', $right = '', $bottom = '');
                $pdf->MultiCell(40, 11, 'Descriptive<br />Performance', 'LRTB',  'L', 0, 0, '', '', true, 0, true);

                $pdf->setCellPaddings($left = '', $top = '0', $right = '', $bottom = '');
                $pdf->SetFont($trebuc, '', 10, '', false);
                $y_start = 44;
                for ($grow = 0; $grow < $grades_count; $grow++) {
                    $pdf->SetXY(55, $y_start);
                    $pdf->MultiCell(22, 5, $grades[$grow][0], 'LRTB', 'C', 0, 0);
                    $pdf->MultiCell(17, 5, $grades[$grow][1], 'LRTB', 'C', 0, 0);
                    $pdf->MultiCell(25, 5, $grades[$grow][2], 'LRTB', 'C', 0, 0);
                    $pdf->MultiCell(40, 5, $grades[$grow][3], 'LRTB', 'C', 0, 0);
                    $y_start = $y_start + 5;
                }

                $pdf->SetFont($trebucb, '', 10, '', false);
                $pdf->SetXY(55, $y_start + 3);
                $pdf->MultiCell(50, 5, 'CGPA', 'LRTB', 'R', 0, 0, '', '', true, 0, true);
                $pdf->MultiCell(54, 5, 'Award of Class', 'LRTB',  'L', 0, 0, '', '', true, 0, true);
                $pdf->SetFont($trebuc, '', 10, '', false);
                $cy_start = $y_start + 8;
                for ($crow = 0; $crow < $cgpa_count; $crow++) {
                    $pdf->SetXY(55, $cy_start);
                    $pdf->MultiCell(50, 5, '', 'LRTB', 'R', 0, 0);
                    $pdf->MultiCell(54, 5, '', 'LRTB', 'L', 0, 0);

                    $pdf->SetXY(55, $cy_start);
                    $pdf->MultiCell(49, 5, $cgpa_class[$crow][0], 0, 'R', 0, 0);
                    $pdf->SetXY(106, $cy_start);
                    $pdf->MultiCell(54, 5, $cgpa_class[$crow][1], 0, 'L', 0, 0);
                    $cy_start = $cy_start + 5;
                }
            }

            $pdf->SetFont($trebuc, '', 10, '', false);
            $ly_start = $cy_start + 5;
            for ($lrow = 0; $lrow < $list_count; $lrow++) {
                $pdf->SetXY(13, $ly_start);
                $pdf->MultiCell(0, 7, $list[$lrow][0], 0, 'L', 0, 0, '', '', true, 0, true);
                $ly_start = $ly_start + 9;
            }
            $pdf->SetFont($trebuc, '', 10, '', false);
            $dy_start = $ly_start + 3;
            for ($drow = 0; $drow < $denote_count; $drow++) {
                $pdf->SetXY(20.5, $dy_start);
                $pdf->MultiCell(15, 5, $denote[$drow][0], 0, 'L', 0, 0);
                $pdf->MultiCell(0, 5, $denote[$drow][1], 0, 'L', 0, 0);
                $dy_start = $dy_start + 5;
            }
            $pdf->SetFont($trebuc, '', 10, '', false);
            $n_start = $dy_start + 5;
            $pdf->SetXY(13, $n_start);
            $pdf->MultiCell(200, 0, $note, 0, 'L', 0, 0, '', '', true, 0, true);
            // End Back pdf


            //upper code change  







            $student_name = $candidate_name;
            if ($previewPdf != 1) {

                $certName = str_replace("/", "_", $GUID) . ".pdf";


                array_push($sftpData, $certName);

                $myPath = public_path() . '/backend/temp_pdf_file';

                $fileVerificationPath = $myPath . DIRECTORY_SEPARATOR . $certName;

                $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');

                $dateOfIssue = date('d/m/Y');
                $mintData = array();
                $mintData['documentType'] = "Transcript";
                $mintData['description'] = "Student ID Number :" . $student_id;
                $mintData['metadata1'] = ["label" => "Student Name", "value" => $candidate_name];
                $mintData['metadata2'] = ["label" => "Programme", "value" => $Programme];
                $mintData['metadata3'] = ["label" => "Batch", "value" => $Batch];
                $mintData['metadata4'] = ["label" => "CGPA", "value" => $cgpa];
                $mintData['metadata5'] = ["label" => "Date Of Issue", "value" => $doi];

                $mintData['uniqueHash'] = $encryptedString;

                $this->addCertificate($serial_no, $certName, $dt, $template_id, $admin_id, $student_name, $mintData);

                $username = $admin_id['username'];
                date_default_timezone_set('Asia/Kolkata');

                $content = "#" . $log_serial_no . " serial No :" . $serial_no . PHP_EOL;
                $date = date('Y-m-d H:i:s') . PHP_EOL;
                $print_datetime = date("Y-m-d H:i:s");


                $print_count = $this->getPrintCount($serial_no);
                $printer_name = /*'HP 1020';*/ $printer_name;
                $print_serial_no = '';

                $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no, 'ANUA3-T', $admin_id, $card_serial_no);
                //$card_serial_no=$card_serial_no+1;
            }

            //TRSNCRIPT RIGHT SIDE PAGE DATA

            $generated_documents++;

            if (isset($pdf_data['generation_from']) && $pdf_data['generation_from'] == 'API') {
                $updated = date('Y-m-d H:i:s');
                ThirdPartyRequests::where('id', $pdf_data['request_id'])->update(['generated_documents' => $generated_documents, "updated_at" => $updated]);
            } else {
                //For Custom loader calculation
                $endTimeLoader = date('Y-m-d H:i:s');
                $time1 = new \DateTime($startTimeLoader);
                $time2 = new \DateTime($endTimeLoader);
                $interval = $time1->diff($time2);
                $interval = $interval->format('%s');

                $jsonArr = array();
                $jsonArr['token'] = $pdf_data['loader_token'];
                $jsonArr['generatedCertificates'] = $generated_documents;
                $jsonArr['timePerCertificate'] = $interval;

                $loaderData = CoreHelper::createLoaderJson($jsonArr, 0);
            }
            //loop through all terms of student 
            //delete temp dir 26-04-2022 
            CoreHelper::rrmdir($tmpDir);
        } //foreach indiviual student


        $msg = '';

        $file_name =  str_replace("/", "_", 'ANUA3_' . date("Ymdhms")) . '.pdf';

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        $filename = public_path() . '/backend/tcpdf/examples/' . $file_name;
        $pdfBig->output($filename, 'F');

        if ($previewPdf != 1) {

            // Upload on SFTP Server
            // CoreHelper::SFTPUploadAnu($sftpData);

            $aws_qr = \File::copy($filename, public_path() . '/' . $subdomain[0] . '/backend/tcpdf/examples/' . $file_name);
            @unlink($filename);
            $no_of_records = count($studentDataOrg);
            $user = $admin_id['username'];
            $template_name = "ANUA3-T";
            if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                // with sandbox
                $result = SbExceUploadHistory::create(['template_name' => $template_name, 'excel_sheet_name' => $excelfile, 'pdf_file' => $file_name, 'user' => $user, 'no_of_records' => $no_of_records, 'site_id' => $auth_site_id]);
            } else {
                // without sandbox
                $result = ExcelUploadHistory::create(['template_name' => $template_name, 'excel_sheet_name' => $excelfile, 'pdf_file' => $file_name, 'user' => $user, 'no_of_records' => $no_of_records, 'site_id' => $auth_site_id]);
            }

            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/';
            $pdf_url = $path . $subdomain[0] . "/backend/tcpdf/examples/" . $file_name;
            $msg = "<b>Click <a href='" . $path . $subdomain[0] . "/backend/tcpdf/examples/" . $file_name . "'class='downloadpdf download'  target='_blank'>Here</a> to download file<b>";
        } else {


            $aws_qr = \File::copy($filename, public_path() . '/' . $subdomain[0] . '/backend/tcpdf/examples/preview/' . $file_name);
            @unlink($filename);
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/';
            $pdf_url = $path . $subdomain[0] . "/backend/tcpdf/examples/preview/" . $file_name;
            $msg = "<b>Click <a href='" . $path . $subdomain[0] . "/backend/tcpdf/examples/preview/" . $file_name . "'class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
        }

        //API changes
        if (isset($pdf_data['generation_from']) && $pdf_data['generation_from'] == 'API') {
            $updated = date('Y-m-d H:i:s');
            ThirdPartyRequests::where('id', $pdf_data['request_id'])->update(['status' => 'Completed', 'printable_pdf_link' => $pdf_url, "updated_at" => $updated]);
            //Sending data to call back url
            $reaquestParameters = array(
                'request_id' => $pdf_data['request_id'],
                'printable_pdf_link' => $pdf_url,
            );
            $url = $pdf_data['call_back_url'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reaquestParameters));
            $result = curl_exec($ch);

            $updated = date('Y-m-d H:i:s');
            ThirdPartyRequests::where('id', $pdf_data['request_id'])->update(['call_back_response' => json_encode($result), "updated_at" => $updated]);

            curl_close($ch);
        }

        return $msg;
    }

    public function getPrintCount($serial_no)
    {
        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }
        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        $numCount = PrintingDetail::select('id')->where('sr_no', $serial_no)->count();

        return $numCount + 1;
    }

    public function createTemp($path)
    {
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

    public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no, $template_name, $admin_id, $card_serial_no)
    {

        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];

        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        if ($systemConfig['sandboxing'] == 1) {
            $result = PrintingDetail::create(['username' => $username, 'print_datetime' => $print_datetime, 'printer_name' => $printer_name, 'print_count' => $printer_count, 'print_serial_no' => $print_serial_no, 'sr_no' => 'T-' . $sr_no, 'template_name' => $template_name, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'publish' => 1]);
        } else {
            $result = PrintingDetail::create(['username' => $username, 'print_datetime' => $print_datetime, 'printer_name' => $printer_name, 'print_count' => $printer_count, 'print_serial_no' => $print_serial_no, 'sr_no' => $sr_no, 'template_name' => $template_name, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'publish' => 1]);
        }
    }

    public function addCertificate($serial_no, $certName, $dt, $template_id, $admin_id, $student_name, $mintData)
    {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $file1 = public_path() . '/backend/temp_pdf_file/' . $certName;
        $file2 = public_path() . '/backend/pdf_file/' . $certName;

        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        $pdfActualPath = public_path() . '/' . $subdomain[0] . '/backend/pdf_file/' . $certName;

        // copy($file1, $file2);        
        // $aws_qr = \File::copy($file2,$pdfActualPath);            
        // @unlink($file2);


        /* Server Storage check for already generated pdf and move to inactive folder */
        $storagePath = public_path();
        $file_existes = $storagePath . '/' . $subdomain[0] . '/backend/pdf_file/' . $certName;
        if (file_exists($file_existes)) {

            if (!is_dir($storagePath . '/' . $subdomain[0] . '/backend/pdf_file/Inactive_PDF')) {
                mkdir($storagePath . '/' . $subdomain[0] . '/backend/pdf_file/Inactive_PDF');
            }
            $student = StudentTable::where('status', 1)->where('serial_no', $serial_no)->value('id');
            $inactivePdf = $storagePath . '/' . $subdomain[0] . '/backend/pdf_file/Inactive_PDF/' . $student . '_' . $certName;

            copy($file_existes, $inactivePdf);
        }


        $source = \Config::get('constant.directoryPathBackward') . "\\backend\\temp_pdf_file\\" . $certName;
        $output = \Config::get('constant.directoryPathBackward') . $subdomain[0] . "\\backend\\pdf_file\\" . $certName;
        CoreHelper::compressPdfFile($source, $output);

        // Upload on SFTP Server
        // CoreHelper::SFTPUpload($output);

        @unlink($file1);

        // customMintPDF
        // blockchain code 
        // $pdf_path = public_path().'\\'.$subdomain[0].'\backend\pdf_file\\'.$certName;
        // $pdf_path = "https://verify.anu.edu.in/verify/seqrdoc/pdf_file/".$certName;
        $pdf_path = $output;
        $mintData['pdf_file'] = $pdf_path;
        $mintData['template_id'] = $template_id;
        $mintData['bc_contract_address'] = 'adasdas';

        // [0: Template Maker, 1 : PDF2PDF, 2:Custom, 3: Excel2PDF]
        $template_type = 2;
        $blockchain_type = 1;

        $url = 'http://localhost:9090/docs';
        if ($this->isUrlWorking($url)) {
            $response = CoreHelper::customMintPDF($mintData, $blockchain_type, $template_type);
        } else {
            $response = [];
        }

        // $response=CoreHelper::customMintPDF($mintData,$blockchain_type,$template_type);
        if ($response['status'] == 200) {
            $bc_txn_hash = $response['txnHash'];
            $bc_sc_id = $response['bc_sc_id'];
            if (isset($response['ipfsHash'])) {
                $bc_ipfs_hash = $response['ipfsHash'];
                $pinata_ipfs_hash = $response['pinataIpfsHash'];
            } else {
                $bc_ipfs_hash = null;
                $pinata_ipfs_hash = null;
                // $bc_sc_id=null;
            }
        } else {
            $bc_txn_hash = null;
            $bc_ipfs_hash = null;
            $pinata_ipfs_hash = null;
            $bc_sc_id = null;
        }


        //Sore file on azure server

        $sts = '1';
        $datetime  = date("Y-m-d H:i:s");
        $ses_id  = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();

        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path() . '/backend/qr';
        $key = strtoupper(md5($serial_no . $dt));
        $codeContents = $key;
        $fileName = $key . '.png';

        $urlRelativeFilePath = 'qr/' . $fileName;

        if ($systemConfig['sandboxing'] == 1) {
            $resultu = SbStudentTable::where('serial_no', 'T-' . $serial_no)->update(['status' => '0']);
            // Insert the new record
            $result = SbStudentTable::create(['serial_no' => 'T-' . $serial_no, 'student_name' => $student_name, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        } else {
            $resultu = StudentTable::where('serial_no', $serial_no)->update(['status' => '0']);
            // Insert the new record
            // $result = StudentTable::create(['serial_no'=>$serial_no,'student_name' =>$student_name ,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'template_type'=>2]);
            $bc_file_hash = '';
            $bc_file_hash = CoreHelper::generateFileHash($output);

            $result = StudentTable::create(['serial_no' => $serial_no, 'student_name' => $student_name, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'template_type' => 2, 'certificate_type' => 'Transcript', 'bc_txn_hash' => $bc_txn_hash, 'bc_ipfs_hash' => $bc_ipfs_hash, 'pinata_ipfs_hash' => $pinata_ipfs_hash, 'bc_sc_id' => $bc_sc_id, 'bc_file_hash' => $bc_file_hash]);


            // vendor identifier
            $studentData = StudentTable::where('serial_no', $serial_no)->where('status', 1)->first();

            $result = DB::table('blockchain_other_data')->updateOrInsert(
                ['student_table_id' => $studentData['id']],
                ['vendor_identifier' => 1]

            );
        }
    }

    public function isUrlWorking($url)
    {
        $headers = @get_headers($url);

        if ($headers && strpos($headers[0], '200') !== false) {
            return true; // URL is valid and working
        }
        return false; // Not working
    }

    public function getNextCardNo($template_name)
    {
        $auth_site_id = Auth::guard('admin')->user()->site_id;

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        if ($systemConfig['sandboxing'] == 1) {
            $result = \DB::select("SELECT * FROM sb_card_serial_numbers WHERE template_name = '$template_name'");
        } else {
            $result = \DB::select("SELECT * FROM card_serial_numbers WHERE template_name = '$template_name'");
        }

        return $result[0];
    }

    public function updateCardNo($template_name, $count, $next_serial_no)
    {
        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        if ($systemConfig['sandboxing'] == 1) {
            $result = \DB::select("UPDATE sb_card_serial_numbers SET card_count='$count',next_serial_no='$next_serial_no' WHERE template_name = '$template_name'");
        } else {
            $result = \DB::select("UPDATE card_serial_numbers SET card_count='$count',next_serial_no='$next_serial_no' WHERE template_name = '$template_name'");
        }

        return $result;
    }

    public function CreateMessage($tmpDir, $name = "", $font_size, $print_color)
    {
        if ($name == "")
            return;
        $name = strtoupper($name);
        // Create character image
        if ($font_size == 15 || $font_size == "15") {


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

            $filename = public_path() . "/backend/canvas/ghost_images/F15_H14_W504.png";

            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((14 * $currentX) / $size[1]);
        } else if ($font_size == 12) {

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

            $filename = public_path() . "/backend/canvas/ghost_images/F12_H8_W288.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((8 * $currentX) / $size[1]);
        } else if ($font_size == "10" || $font_size == 10) {
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

            $filename = public_path() . "/backend/canvas/ghost_images/F10_H5_W180.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((5 * $currentX) / $size[1]);
        } else if ($font_size == 11) {

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

            $filename = public_path() . "/backend/canvas/ghost_images/F11_H7_W250.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);


            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((7 * $currentX) / $size[1]);
        } else if ($font_size == "13" || $font_size == 13) {

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


            $filename = public_path() . "/backend/canvas/ghost_images/F13_H10_W360.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            // dd($rect);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX) / $size[1]);
        } else if ($font_size == "14" || $font_size == 14) {

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

            $filename = public_path() . "/backend/canvas/ghost_images/F14_H12_W432.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];

                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((12 * $currentX) / $size[1]);
        } else {
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

            $filename = public_path() . "/backend/canvas/ghost_images/ALPHA_GHOST.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);

            // Create Backgoround image
            $filename   = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);

            for ($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if (!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);

            imagepng($im, "$tmpDir/" . $name . "" . $font_size . ".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX) / $size[1]);
        }
    }
}
