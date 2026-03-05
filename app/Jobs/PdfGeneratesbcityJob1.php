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

class PdfGeneratesbcityJob1
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


        $credit_register_key = $pdf_data['credit_register_key'];
        $total_credit_hours_key = $pdf_data['total_credit_hours_key'];
        $total_credit_points_key = $pdf_data['total_credit_points_key'];
        $note_key = $pdf_data['note_key'];
        $cgpa_key = $pdf_data['cgpa_key'];
        $sgpa_key = $pdf_data['sgpa_key'];
        $subj_col = $pdf_data['subj_col'];

        $subj_start = $pdf_data['subj_start'];
        $subj_end = $pdf_data['subj_end'];

        $template_id = $pdf_data['template_id'];
        $dropdown_template_id = $pdf_data['dropdown_template_id'];
        $previewPdf = $pdf_data['previewPdf'];
        $excelfile = $pdf_data['excelfile'];
        $auth_site_id = $pdf_data['auth_site_id'];
        $previewWithoutBg = $previewPdf[1];
        $previewPdf = $previewPdf[0];

        // $first_sheet=$pdf_data['studentDataOrg']; // get first worksheet rows

        // echo "<pre>";
        // print_r($pdf_data);
        // echo "</pre>";
        // Log an error
        // \Log::info('pdf data error', ['pdf_data' => $pdf_data]);



        // $total_unique_records=count($first_sheet);
        // $last_row=$total_unique_records+1;

        if (isset($pdf_data['generation_from']) && $pdf_data['generation_from'] == 'API') {
            $admin_id = $pdf_data['admin_id'];
        } else {
            $admin_id = \Auth::guard('admin')->user()->toArray();
        }
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $systemConfig = SystemConfig::select('sandboxing', 'printer_name')->where('site_id', $auth_site_id)->first();
        $printer_name = $systemConfig['printer_name'];


        $ghostImgArr = array();

        // Start Update code for batchwise genration
        $loader_data = CoreHelper::getLoaderJson($pdf_data['loader_token']);


        // Log an error
        //\Log::info('loader error', ['loader_data' => $loader_data]);

        if (!empty($loader_data) && isset($loader_data['generatedCertificates'])) {

            $generated_documents = $loader_data['generatedCertificates'];
        } else {
            $generated_documents = 0;
        }


        //\Log::info('generated_documents error', ['generated_documents' => $generated_documents]);

        if ($generated_documents == 0) {
            Session::forget('pdf_data_obj');
            // $pdfBig = new TCPDF('L', 'mm', array('297', '210'), true, 'UTF-8', false);
            $pdfBig = new TCPDF('P', 'mm', "A4", true, 'UTF-8', false);

            $pdfBig->SetCreator(PDF_CREATOR);
            $pdfBig->SetAuthor('TCPDF');
            $pdfBig->SetTitle('Certificate');
            $pdfBig->SetSubject('');
            // Session::put('pdf_obj', $pdfBig);


        } else {


            // $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
            // $pdfBig->SetCreator(PDF_CREATOR);
            // $pdfBig->SetAuthor('TCPDF');
            // $pdfBig->SetTitle('Certificate');
            // $pdfBig->SetSubject('');
            // $pdfContent = Session::get('pdf_data_obj');  
            // $pdfBig->setSourceFile(StreamReader($pdfContent));

            //  $sessionData = Session::all();

            // // Print the session data in a readable format
            // print_r($sessionData);

            if (Session::get('pdf_data_obj') != null) {
                $pdfBig = Session::get('pdf_data_obj');
            }
        }


        // if(Session::get('pdf_obj') != null){
        // $pdfBig = Session::get('pdf_obj');   
        // }





        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
        $pdfBig->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\arialb.ttf', 'TrueTypeUnicode', '', 96);
        $arial = TCPDF_FONTS::addTTFfont(public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\arial.ttf', 'TrueTypeUnicode', '', 96);
                $cambria = TCPDF_FONTS::addTTFfont(
            public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Cambria-Font-For-Windows.ttf',
            'TrueTypeUnicode',
            '',
            96
        );

        // Bold
        $cambriaB = TCPDF_FONTS::addTTFfont(
            public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Cambria Bold 700.ttf',
            'TrueTypeUnicode',
            '',
            96
        );

        // Italic
        $cambriaI = TCPDF_FONTS::addTTFfont(
            public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Cambria Italic 400.ttf',
            'TrueTypeUnicode',
            '',
            96
        );

        // Bold Italic
        $cambriaBI = TCPDF_FONTS::addTTFfont(
            public_path() . '\\' . $subdomain[0] . '\backend\canvas\fonts\Cambria Bold Italic 700.ttf',
            'TrueTypeUnicode',
            '',
            96
        );

        $preview_serial_no = 1;
        //$card_serial_no="";
        $log_serial_no = 1;
        $cardDetails = array();
        $card_serial_no = 0;
        if (empty($studentDataOrg)) {
        }
        // $generated_documents=0;  //for custom loader
        if ($studentDataOrg && !empty($studentDataOrg)) {
            foreach ($studentDataOrg as $studentData) {

                if ($card_serial_no > 999999 && $previewPdf != 1) {
                    echo "<h5>Your card series ended...!</h5>";
                    exit;
                }
                //For Custom Loader
                $startTimeLoader =  date('Y-m-d H:i:s');
                $high_res_bg = "sbcitycollege_BG.jpg"; // bestiu_bg, bestiu_bg_data
                $low_res_bg = "sbcitycollege_BG.jpg";
                $pdfBig->AddPage();
                $pdfBig->SetFont($arialNarrowB, '', 8, '', false);
                //set background image
                $template_img_generate = public_path() . '\\' . $subdomain[0] . '\backend\canvas\bg_images\\' . $high_res_bg;

                if ($previewPdf == 1) {
                    if ($previewWithoutBg != 1) {
                        $pdfBig->Image($template_img_generate, 0, 0,  '210', '297', "JPG", '', 'R', true);
                    }
                    $date_font_size = '11';
                    $date_nox = 13;
                    $date_noy = 40;
                    // $date_nostr = 'DRAFT ' . date('d-m-Y H:i:s');
                    $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
                    $pdfBig->SetTextColor(192, 192, 192);
                    $pdfBig->SetXY($date_nox, $date_noy);
                    $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');
                    $pdfBig->SetTextColor(0, 0, 0, 100, false, '');
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


                // add spot colors
                //$pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
                //$pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

                $pdf->AddPage();
                $print_serial_no = $this->nextPrintSerial();
                //set background image
                $template_img_generate = public_path() . '\\' . $subdomain[0] . '\backend\canvas\bg_images\\' . $low_res_bg;

                if ($previewPdf != 1) {
                    $pdf->Image($template_img_generate, 0, 0,  '210', '297', "JPG", '', 'R', true);
                }
                //$pdf->setPageMark();
                $pdf->setPageMark();
                //$pdfBig->setPageMark();
                //if($previewPdf!=1){            
                $x = 173;
                $y = 39.1;
                $font_size = 12;
                if ($previewPdf != 1) {
                    $str = str_pad($card_serial_no, 7, '0', STR_PAD_LEFT);
                } else {
                    $str = str_pad($preview_serial_no, 7, '0', STR_PAD_LEFT);
                }
                $strArr = str_split($str);
                $x_org = $x;
                $y_org = $y;
                $font_size_org = $font_size;
                $i = 0;
                $j = 0;
                $y = $y + 4.5;
                $z = 0;
                foreach ($strArr as $character) {
                    $pdf->SetFont($arialNarrow, 0, $font_size, '', false);
                    $pdf->SetXY($x, $y + $z);

                    $pdfBig->SetFont($arialNarrow, 0, $font_size, '', false);
                    $pdfBig->SetXY($x, $y + $z);

                    if ($i == 3) {
                        $j = $j + 0.2;
                    } else if ($i > 1) {
                        $j = $j + 0.1;
                    }

                    if ($i > 1) {
                        $z = $z + 0.1;
                    }
                    if ($i > 3) {
                        $x = $x + 0.4;
                    } else if ($i > 2) {
                        $x = $x + 0.2;
                    }
                    //$pdf->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
                    //$pdfBig->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
                    $i++;
                    $x = $x + 2.2 + $j;
                    if ($i > 2) {
                        $font_size = $font_size + 1.7;
                    }
                }




                $pdfBig->SetXY(16.7, 79.5);
                $pdfBig->SetFont($arial, '', 11, '', false);
                $pdfBig->SetFillColor(255, 255, 255);
                $pdfBig->SetTextColor(0, 0, 0);

                // Extract variables (with safety)
                $unique_id     = trim($studentData[0] ?? '');
                $candidate_name = trim($studentData[1] ?? '');
                $mother_name   = trim($studentData[2] ?? '');
                $seat_no       = trim($studentData[3] ?? '');
                $exam_category = trim($studentData[4] ?? '');
                $major         = trim($studentData[5] ?? '');
                $center_no     = trim($studentData[7] ?? '');
                $enrol_no      = trim($studentData[8] ?? '');
                $pl_no         = trim($studentData[9] ?? '');

                // Excel date → real date
                $excel_date    = trim($studentData[10] ?? '');
                $date_str      = '';
                if (is_numeric($excel_date) && $excel_date > 0) {
                    $date_str = date('d-m-Y', ($excel_date - 25569) * 86400);
                }
                $medium        = trim($studentData[11] ?? '');

                // Summary fields
                $incentive     = trim($studentData[156] ?? '');
                $sgpv          = trim($studentData[157] ?? '');
                $total_credits = trim($studentData[158] ?? '');
                $sgpa          = number_format((float)($studentData[159] ?? 0), 2, '.', '');
                $out_of        = trim($studentData[160] ?? '');
                $total_marks   = trim($studentData[161] ?? '');
                $out_of_marks  = trim($studentData[162] ?? '');
                $result        = trim($studentData[163] ?? '');
                $percent       = trim($studentData[164] ?? '');
                $performance   = trim($studentData[166] ?? '');
                $abc_apaar     = trim($studentData[168] ?? '');

                        // ───────────────────────────────────────────────
                // Header Section
                // ───────────────────────────────────────────────
                $pdfBig->SetFont($cambriaB, '', 11);
                $pdfBig->SetXY(10, 60);
                $pdfBig->Cell(190, 5, '(FACULTY OF ARTS & HUMANITIES)', 0, 1, 'C');

                $pdfBig->SetFont($cambriaB, '', 10);
                $pdfBig->SetXY(10, 65);
                $pdfBig->Cell(190, 5, 'FIRST SEMESTER OF BACHELOR OF ARTS (B.A.) (NEP)', 0, 1, 'C');

                $pdfBig->SetFont($cambriaB, '', 10);
                $pdfBig->SetXY(10, 71);
                $pdfBig->Cell(190, 5, 'WINTER-2025', 0, 1, 'C');

                $pdfBig->SetFont($cambriaB, 'B', 8.5);

                $y          = 78;
                $h          = 5.2;
                $leftX      = 20;
                $rightX     = 130;

                $labelW     = 25;
                $colonW     = 7;
                $valueW     = 30;   // increased slightly if names are long — adjust as needed

                $colonOffset = 20;   // left side (you had 20)
                $valueOffset = $colonOffset + 5;

                $colonOffsetRight = 15;   // right side was slightly smaller
                $valueOffsetRight = $colonOffsetRight + 5;

                // ────────────────────────────────────────────────
                //          Define all fields in logical order
                // ────────────────────────────────────────────────
                $fields = [
                    // Left column                  // Right column
                    ['Student Name',   strtoupper($candidate_name),   'Center No',     $center_no],
                    ['Mother Name',    strtoupper($mother_name),      'Enrol. No.',    strtoupper($enrol_no)],
                    ['Roll No.',       $seat_no,                      'P/L No.',       $pl_no],
                    ['Exam Category',  $exam_category,                'Date',          $date_str],
                    ['Major',          strtoupper($major),            'Medium',        strtoupper($medium)],
                    // Add more rows here in the future if needed, e.g.:
                    // ['Father Name', strtoupper($father_name),      'Something',     $value],
                ];

                foreach ($fields as $row) {
                    [$leftLabel, $leftValue, $rightLabel, $rightValue] = $row;

                    // ─── LEFT ───────────────────────────────────────
                    $pdfBig->SetXY($leftX, $y);
                    $pdfBig->MultiCell($labelW, $h, $leftLabel, 0, 'L');

                    $pdfBig->SetXY($leftX + $colonOffset, $y);
                    $pdfBig->MultiCell($colonW, $h, ':', 0, 'C');

                    $pdfBig->SetXY($leftX + $valueOffset, $y);
                    $pdfBig->MultiCell($valueW, $h, $leftValue, 0, 'L');

                    // ─── RIGHT ──────────────────────────────────────
                    $pdfBig->SetXY($rightX, $y);
                    $pdfBig->MultiCell($labelW, $h, $rightLabel, 0, 'L');

                    $pdfBig->SetXY($rightX + $colonOffsetRight, $y);
                    $pdfBig->MultiCell($colonW, $h, ':', 0, 'C');

                    $pdfBig->SetXY($rightX + $valueOffsetRight, $y);
                    $pdfBig->MultiCell($valueW, $h, $rightValue, 0, 'L');

                    $y += $h;
                }



                // Optional: switch back to normal font after this section
                $pdfBig->SetFont($cambria, '', 8.5);

                $pdfBig->Ln(3);

                $w_sr      = 8;
                $w_abbr    = 12;
                $w_sub     = 40;

                $w_small   = 8;
                $w_total   = 10;
                $w_grade   = 6;
                $w_gp      = 10;
                $w_credit  = 6;
                $w_status  = 8;

                // ───────────────────────────────────────────────
                $left = 15;
                $pdfBig->SetX($left);
                $startY = $pdfBig->GetY();

                // ───────────────────────────────────────────────
                // HEADER STYLE
                // ───────────────────────────────────────────────
                $pdfBig->SetFont($arialb, '', 6);
                $pdfBig->SetFillColor(235, 235, 235);
                $pdfBig->SetTextColor(0, 0, 0);

                $pdfBig->setCellPadding(0);
                $pdfBig->setCellHeightRatio(0.9);

                $h1 = 7;
                $h2 = 7;
                $h3 = 7;

                $totalHeaderHeight = $h1 + $h2 + $h3;

                // ───────────────────────────────────────────────
                // FIXED COLUMNS (FULL HEIGHT MERGED)
                // ───────────────────────────────────────────────
                $pdfBig->SetXY($left, $startY);
                $pdfBig->MultiCell($w_sr, $totalHeaderHeight, "SR.\nNO", 1, 'C', true, 0, '', '', true, 0, false, true, $totalHeaderHeight, 'M');

                $pdfBig->SetXY($left + $w_sr, $startY);
                $pdfBig->MultiCell($w_abbr, $totalHeaderHeight, "ABBR", 1, 'C', true, 0, '', '', true, 0, false, true, $totalHeaderHeight, 'M');

                $pdfBig->SetXY($left + $w_sr + $w_abbr, $startY);
                $pdfBig->MultiCell($w_sub, $totalHeaderHeight, "SUBJECT", 1, 'C', true, 0, '', '', true, 0, false, true, $totalHeaderHeight, 'M');

                // ───────────────────────────────────────────────
                // VARIABLE SECTION START POSITION
                // ───────────────────────────────────────────────
                $x = $left + $w_sr + $w_abbr + $w_sub;
                $y = $startY;

                // ───────────────────────────────────────────────
                // LEVEL 1 - MAIN GROUP HEADERS
                // ───────────────────────────────────────────────
                // total scheme width = 3 + 1 + 3 = 7 small columns
                $schemeWidth = $w_small * 7;

                $pdfBig->SetXY($x, $y);
                $pdfBig->MultiCell(
                    $schemeWidth,
                    $h1,
                    "MARKS & CREDIT SCHEME",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h1,
                    'M'
                );

                $pdfBig->SetXY($x + $schemeWidth, $y);
                $pdfBig->MultiCell(
                    ($w_small * 3) + $w_total + $w_grade + $w_gp + $w_credit + $w_status,
                    $h1,
                    "MARKS & GRADES AWARDED",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h1,
                    'M'
                );


                // ───────────────────────────────────────────────
                // LEVEL 2 - SUB GROUP HEADERS
                // ───────────────────────────────────────────────
                $y += $h1;
                $pdfBig->SetXY($x, $y);

                // MAX MARKS
                $pdfBig->MultiCell(
                    $w_small * 3,
                    $h2,
                    "MAX MARKS",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h2,
                    'M'
                );

                // TOTAL (rowspan 2)
                $pdfBig->MultiCell(
                    $w_small * 1,
                    $h2 + $h3,
                    "TOTAL",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h2 + $h3,
                    'M'
                );

                // MIN MARKS
                $pdfBig->MultiCell(
                    $w_small * 3,
                    $h2,
                    "MIN MARKS",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h2,
                    'M'
                );

                // $oldLineWidth = $pdfBig->GetLineWidth();
                // $pdfBig->SetLineWidth(0.8);

                // Only Left + Right border thick
                $pdfBig->MultiCell($w_small, $h2 + $h3, "SEE", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                $pdfBig->MultiCell($w_small, $h2 + $h3, "CIE", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                $pdfBig->MultiCell($w_small, $h2 + $h3, "PI", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');

                // $pdfBig->SetLineWidth($oldLineWidth);





                $pdfBig->MultiCell($w_total,  $h2 + $h3, "TOTAL\nMARKS", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                // $pdfBig->MultiCell($w_grade,  $h2 + $h3, "GRADE",        1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                // -------- GRADE --------
                $x_grade = $pdfBig->GetX();
                $y_grade = $pdfBig->GetY();

                $pdfBig->MultiCell($w_grade, $h2 + $h3, '', 1, 'M', true, 0);

                $pdfBig->StartTransform();
                $pdfBig->Rotate(90, $x_grade + ($w_grade / 2), $y_grade + (($h2 + $h3) / 2));
                $pdfBig->Text(
                    $x_grade + ($w_grade / 2) - 3,
                    $y_grade + (($h2 + $h3) / 2) - 2,
                    'GRADE'
                );
                $pdfBig->StopTransform();

                $pdfBig->SetXY($x_grade + $w_grade, $y_grade);



                $pdfBig->MultiCell($w_gp,     $h2 + $h3, "GRADE\nPOINT", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                // $pdfBig->MultiCell($w_credit, $h2 + $h3, "CREDIT",       1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');

                // -------- CREDIT --------
                $x_credit = $pdfBig->GetX();
                $y_credit = $pdfBig->GetY();

                $pdfBig->MultiCell($w_credit, $h2 + $h3, '', 1, 'M', true, 0);

                // Text
                $creditText = 'CREDIT';
                $textWidth  = $pdfBig->GetStringWidth($creditText);

                $pdfBig->StartTransform();
                $pdfBig->Rotate(90, $x_credit + ($w_credit / 2), $y_credit + (($h2 + $h3) / 2));

                $pdfBig->Text(
                    $x_credit + ($w_credit / 2) - ($textWidth / 2),
                    $y_credit + (($h2 + $h3) / 2),
                    $creditText
                );

                $pdfBig->StopTransform();

                $pdfBig->SetXY($x_credit + $w_credit, $y_credit);


                $x_status = $pdfBig->GetX();
                $y_status = $pdfBig->GetY();

                $pdfBig->MultiCell(
                    $w_status,
                    $h2 + $h3,
                    '',
                    1,
                    'M',
                    true,
                    0
                );

                // Text
                $statusText = 'STATUS';
                $textWidth  = $pdfBig->GetStringWidth($statusText);

                $pdfBig->StartTransform();
                $pdfBig->Rotate(90, $x_status + ($w_status / 2), $y_status + (($h2 + $h3) / 2));

                $pdfBig->Text(
                    $x_status + ($w_status / 2) - ($textWidth / 2),
                    $y_status + (($h2 + $h3) / 2),
                    $statusText
                );

                $pdfBig->StopTransform();

                $pdfBig->SetXY($x_status + $w_status, $y_status);




                // ───────────────────────────────────────────────
                // LEVEL 3 - COLUMN TITLES
                // ───────────────────────────────────────────────
                $y += $h2;
                $pdfBig->SetXY($x, $y);

                // MAX MARKS sub columns
                $pdfBig->MultiCell($w_small, $h3, "SEE", 1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');
                $pdfBig->MultiCell($w_small, $h3, "CIE", 1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');
                $pdfBig->MultiCell($w_small, $h3, "PI",  1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');

                // Skip TOTAL (already rowspan)
                $pdfBig->SetX($x + ($w_small * 4));

                // MIN MARKS sub columns
                $pdfBig->MultiCell($w_small, $h3, "SEE", 1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');
                $pdfBig->MultiCell($w_small, $h3, "CIE", 1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');
                $pdfBig->MultiCell($w_small, $h3, "PI",  1, 'C', true, 1, '', '', true, 0, false, true, $h3, 'M');



                // ───────────────────────────────────────────────
                // MOVE CURSOR BELOW HEADER
                // ───────────────────────────────────────────────
                $pdfBig->SetY($startY + $totalHeaderHeight);

                // =====================================================
                // DATA ROWS
                // =====================================================
                // =====================================================
                // DATA ROWS (PURE MULTICELL STRUCTURE)
                // =====================================================
                $pdfBig->SetFont($cambria, '', 6);  // default normal font
                $y = $pdfBig->GetY();

                $abbrs = [
                    'MAJOR-1',
                    'MAJOR-2',
                    'MINOR',
                    'VSC',
                    'SEC',
                    'AEC',
                    'VEC',
                    'IKS',
                    'CC'
                    // adjust order as per your actual subjects
                ];

                for ($i = 0; $i < 10; $i++) {

                    $base = 12 + $i * 16;
                    $subject = trim($studentData[$base] ?? '');
                    if ($subject === '') continue;

                    $sr   = ($i + 1) . '.';
                    $abbr = $abbrs[$i] ?? '-';

                    $rowData = [
                        $sr,
                        $abbr,
                        $subject,

                        trim($studentData[$base + 1] ?? ''),
                        trim($studentData[$base + 2] ?? ''),
                        trim($studentData[$base + 3] ?? ''),
                        trim($studentData[$base + 4] ?? ''),

                        trim($studentData[$base + 5] ?? ''),
                        trim($studentData[$base + 6] ?? ''),
                        trim($studentData[$base + 7] ?? ''),

                        trim($studentData[$base + 8] ?? ''),
                        trim($studentData[$base + 9] ?? ''),
                        trim($studentData[$base + 10] ?? ''),

                        trim($studentData[$base + 11] ?? ''),
                        trim($studentData[$base + 12] ?? ''),
                        trim($studentData[$base + 13] ?? ''),
                        trim($studentData[$base + 14] ?? ''),
                        trim($studentData[$base + 15] ?? '')
                    ];

                    $widths = [
                        $w_sr,
                        $w_abbr,
                        $w_sub,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_total,
                        $w_grade,
                        $w_gp,
                        $w_credit,
                        $w_status
                    ];

                    $startX = $left;
                    $startY = $y;

                    // Calculate row height
                    $maxHeight = 0;
                    foreach ($rowData as $key => $text) {
                        $height = $pdfBig->getStringHeight($widths[$key], $text);
                        $maxHeight = max($maxHeight, $height);
                    }
                    $rowHeight = max(8, $maxHeight + 2);

                    // Page break
                    if ($startY + $rowHeight > 270) {
                        $pdfBig->AddPage();
                        $startY = $pdfBig->GetY();
                    }

                    $x = $startX;
                    foreach ($rowData as $key => $text) {

                        $pdfBig->SetXY($x, $startY);

                        // ===== FONT BOLD CONDITION =====
                        if ($key === 1 || $key === 17 || $key === 11 || $key === 10 || $key === 12) {
                            $pdfBig->SetFont($cambriaB, '', 6.5);
                        } else {
                            $pdfBig->SetFont($cambria, '', 6.5);
                        }

                        // ===== BORDER CONDITION =====
                        if ($key === 10 || $key === 11 || $key === 12 ||$key===13) {
                            $pdfBig->SetLineWidth(0.5);   // Bold border
                            $border = 'LRB';             // Top Bottom Left Right
                        } else {
                            $pdfBig->SetLineWidth(0.3);   // Normal border
                            $border = 1;
                        }

                        // Alignment
                        $align = ($key == 2) ? 'L' : 'C';

                        // Padding
                        if ($key == 2) {
                            $pdfBig->setCellPaddings(2, 1, 1, 1);
                        } else {
                            $pdfBig->setCellPaddings(1, 1, 1, 1);
                        }

                        $pdfBig->MultiCell(
                            $widths[$key],
                            $rowHeight,
                            $text,
                            $border,   // 👈 LTRB applied here
                            $align,
                            0,
                            0,
                            '',
                            '',
                            true,
                            0,
                            false,
                            true,
                            $rowHeight,
                            'M'
                        );

                        $x += $widths[$key];
                    }



                    $y = $startY + $rowHeight;
                }





                // Optional: line or space after last row
                $pdfBig->Ln(15);
                // $pdfBig->Ln(10);




                // ───────────────────────────────────────────────
                // Summary Section
                // ───────────────────────────────────────────────

                $pdfBig->SetFillColor(235, 235, 235);

                $sumH = 7;
                $startX = 15;
                $startY = $pdfBig->GetY();

                $pdfBig->SetXY($startX, $startY);

                /* =====================================================
   COLUMN WIDTHS (KEEP FIXED FOR PERFECT ALIGNMENT)
===================================================== */

                $w1  = 19;  // INCENTIVE
                $w2  = 12;  // ΣGPV
                $w3  = 21;  // TOTAL CREDITS
                $w4  = 12;  // SGPA
                $w5  = 13;  // OUT OF
                $w6  = 28;  // TOTAL MARKS OBTAINED
                $w7  = 23;  // OUT OF MARKS
                $w8  = 17;  // RESULT
                $w9  = 17;  // PERCENT
                $w10 = 17;  // REMARKS

                $widths = [$w1, $w2, $w3, $w4, $w5, $w6, $w7, $w8, $w9, $w10];


                /* =====================================================
   HEADER ROW  (Bold + Center)
===================================================== */

                $pdfBig->SetFont($cambriaB, '', 7.5);

                $headers = [
                    'INCENTIVE',
                    'ΣGPV',
                    "TOTAL\nCREDITS",
                    'SGPA',
                    'OUT OF',
                    "TOTAL MARKS\nOBTAINED",
                    "OUT OF\nMARKS",
                    'RESULT',
                    'PERCENT',
                    'REMARKS'
                ];

                $x = $startX;

                foreach ($headers as $i => $head) {
                    $pdfBig->SetXY($x, $startY);
                    $pdfBig->MultiCell($widths[$i], $sumH, $head, 1, 'C', 1);
                    $x += $widths[$i];
                }

                $startY += $sumH;


                /* =====================================================
   DATA ROW (Normal + Center)
===================================================== */

                $pdfBig->SetFont($cambriaB, '', 7.5);

                $data = [
                    $incentive,
                    $sgpv,
                    $total_credits,
                    $sgpa,
                    $out_of,
                    $total_marks,
                    $out_of_marks,
                    $result,
                    $percent,
                    ''
                ];

                $x = $startX;
                $rowY = $startY;

                foreach ($data as $i => $value) {

                    $pdfBig->SetXY($x, $rowY);

                    $pdfBig->MultiCell(
                        $widths[$i],
                        $sumH,
                        trim($value),
                        1,
                        'C',      // horizontal center
                        0,
                        0,        // stay on same row
                        '',
                        '',
                        true,
                        0,
                        false,
                        true,
                        $sumH,    // fixed height
                        'M'       // vertical middle
                    );

                    $x += $widths[$i];
                }

                $startY += $sumH;

                // $startY += $sumH;


                /* =====================================================
   PERFORMANCE ROW (Normal + Left Aligned)
===================================================== */

                $pdfBig->SetFont($cambriaB, '', 7.5);
                $pdfBig->SetFillColor(235, 235, 235);

                // Merge first 5 columns
                $leftEmpty = $w1 + $w2 + $w3 + $w4 + $w5;

                $pdfBig->SetXY($startX, $startY);
                $pdfBig->MultiCell(
                    $leftEmpty,
                    $sumH,
                    '',
                    1,
                    'C',
                    1,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $sumH,
                    'M'
                );

                // PERFORMANCE label (merge w6 + w7)
                $performanceLabelWidth = $w6 + $w7;

                $pdfBig->SetXY($startX + $leftEmpty, $startY);
                $pdfBig->MultiCell(
                    $performanceLabelWidth,
                    $sumH,
                    'PERFORMANCE',
                    1,
                    'C',   // center
                    1,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $sumH,
                    'M'
                );

                // Dynamic performance value (merge w8+w9+w10)
                $performanceValueWidth = $w8 + $w9 + $w10;

                $pdfBig->SetXY($startX + $leftEmpty + $performanceLabelWidth, $startY);
                $pdfBig->MultiCell(
                    $performanceValueWidth,
                    $sumH,
                    strtoupper($performance ?? ''),
                    1,
                    'C',   // center
                    1,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $sumH,
                    'M'
                );

                $startY += $sumH;


                $pdfBig->Ln(10);
                $pdfBig->SetFont($cambria, '', 8);
                $pdfBig->Cell(180, 5, 'ABC/APAAR ID : ' . $abc_apaar, 0, 1, 'R');

                // ───────────────────────────────────────────────
                // Footer (Exact Compact Layout Like Image)
                // ───────────────────────────────────────────────

                $pdfBig->SetFont($cambria, '', 6);

                // Move slightly down from previous content
                $currentY = $pdfBig->GetY();
                $pdfBig->SetXY(15, $currentY - 2);

                // Line 1
                $pdfBig->MultiCell(
                    180,
                    3.5,   // smaller height = tight spacing
                    '(THIS STATEMENT IS SUBJECT TO CORRECTIONS IF ANY)',
                    0,
                    'L',
                    0,
                    1
                );

                // Line 2
                $pdfBig->SetX(15);
                $pdfBig->MultiCell(
                    180,
                    3.5,
                    'REFER CREDIT GRADE SYSTEM TABLE: Criteria for Award of Grades:',
                    0,
                    'L',
                    0,
                    1
                );

                // Grade System Line (Wrapped Compactly)
                // $pdfBig->SetX(15);
                // $pdfBig->MultiCell(
                //     180,
                //     3.5,
                //     'Grade: O Point: 10.00 – 9.00 Range: 100-90 (OUTSTANDING) | ' .
                //         '| Grade: A+ Point: 8.99-8.00 Range: 89-80 (EXELENT) | ' .
                //         '|Grade: A Point: 7.99 – 7.00 Range: 79-70 (VERY GOOD) | ' .
                //         '| Grade: B+' .

                //         '| Point: 6.99-6.00 Range: 69-60 (GOOD)| ' .
                //         ') Grade: B Point: 5.99 – 5.50 Range: 59-55 (ABOVE AVERAGE)  | ' .
                //         '| Grade: C Point: 5.49-5.00 Range: 54-50 (AVERAGE) ',
                //     0,
                //     'L',
                //     0,
                //     1
                // );
                $pdfBig->SetX(15);

                $text1 =
                    'Grade: O Point: 10.00 – 9.00 Range: 100-90 (OUTSTANDING) | ' .
                    'Grade: A+ Point: 8.99-8.00 Range: 89-80 (EXCELLENT) | ' .
                    'Grade: A Point: 7.99 – 7.00 Range: 79-70 (VERY GOOD) |Grade: B+ ';

                $pdfBig->MultiCell(180, 3.5, $text1, 0, 'L', 0, 1);

                // 🔹 Small vertical gap before second line
                $pdfBig->Ln(0);   // adjust 0.5 / 1 / 1.5 as needed

                $pdfBig->SetX(15);

                $text2 =
                    // 'Grade: B+ ' .
                    'Point: 6.99-6.00 Range: 69-60 (GOOD) | ' .
                    'Grade: B Point: 5.99 – 5.50 Range: 59-55 (ABOVE AVERAGE) | ' .
                    'Grade: C Point: 5.49-5.00 Range: 54-50 (AVERAGE)';

                $pdfBig->MultiCell(180, 3.5, $text2, 0, 'L');


                // Note Line (Very Tight)
                $pdfBig->SetX(15);
                $pdfBig->MultiCell(
                    180,
                    3,
                    'Note: (*) Pass by grace marks vide AUTONOMY Direction No. 8 of 2024',
                    0,
                    'L'
                );





















                //Start pdf

                 $pdf->SetFont($cambriaB, '', 11);
                $pdf->SetXY(10, 60);
                $pdf->Cell(190, 5, '(FACULTY OF ARTS & HUMANITIES)', 0, 1, 'C');

                $pdf->SetFont($cambriaB, '', 10);
                $pdf->SetXY(10, 65);
                $pdf->Cell(190, 5, 'FIRST SEMESTER OF BACHELOR OF ARTS (B.A.) (NEP)', 0, 1, 'C');

                $pdf->SetFont($cambriaB, '', 10);
                $pdf->SetXY(10, 71);
                $pdf->Cell(190, 5, 'WINTER-2025', 0, 1, 'C');

                $pdf->SetFont($cambriaB, 'B', 8.5);

                $y          = 78;
                $h          = 5.2;
                $leftX      = 20;
                $rightX     = 130;

                $labelW     = 25;
                $colonW     = 7;
                $valueW     = 30;   // increased slightly if names are long — adjust as needed

                $colonOffset = 20;   // left side (you had 20)
                $valueOffset = $colonOffset + 5;

                $colonOffsetRight = 15;   // right side was slightly smaller
                $valueOffsetRight = $colonOffsetRight + 5;

                // ────────────────────────────────────────────────
                //          Define all fields in logical order
                // ────────────────────────────────────────────────
                $fields = [
                    // Left column                  // Right column
                    ['Student Name',   strtoupper($candidate_name),   'Center No',     $center_no],
                    ['Mother Name',    strtoupper($mother_name),      'Enrol. No.',    strtoupper($enrol_no)],
                    ['Roll No.',       $seat_no,                      'P/L No.',       $pl_no],
                    ['Exam Category',  $exam_category,                'Date',          $date_str],
                    ['Major',          strtoupper($major),            'Medium',        strtoupper($medium)],
                    // Add more rows here in the future if needed, e.g.:
                    // ['Father Name', strtoupper($father_name),      'Something',     $value],
                ];

                foreach ($fields as $row) {
                    [$leftLabel, $leftValue, $rightLabel, $rightValue] = $row;

                    // ─── LEFT ───────────────────────────────────────
                    $pdf->SetXY($leftX, $y);
                    $pdf->MultiCell($labelW, $h, $leftLabel, 0, 'L');

                    $pdf->SetXY($leftX + $colonOffset, $y);
                    $pdf->MultiCell($colonW, $h, ':', 0, 'C');

                    $pdf->SetXY($leftX + $valueOffset, $y);
                    $pdf->MultiCell($valueW, $h, $leftValue, 0, 'L');

                    // ─── RIGHT ──────────────────────────────────────
                    $pdf->SetXY($rightX, $y);
                    $pdf->MultiCell($labelW, $h, $rightLabel, 0, 'L');

                    $pdf->SetXY($rightX + $colonOffsetRight, $y);
                    $pdf->MultiCell($colonW, $h, ':', 0, 'C');

                    $pdf->SetXY($rightX + $valueOffsetRight, $y);
                    $pdf->MultiCell($valueW, $h, $rightValue, 0, 'L');

                    $y += $h;
                }



                // Optional: switch back to normal font after this section
                $pdf->SetFont($cambria, '', 8.5);

                $pdf->Ln(3);

                $w_sr      = 8;
                $w_abbr    = 12;
                $w_sub     = 40;

                $w_small   = 8;
                $w_total   = 10;
                $w_grade   = 6;
                $w_gp      = 10;
                $w_credit  = 6;
                $w_status  = 8;

                // ───────────────────────────────────────────────
                $left = 15;
                $pdf->SetX($left);
                $startY = $pdf->GetY();

                // ───────────────────────────────────────────────
                // HEADER STYLE
                // ───────────────────────────────────────────────
                $pdf->SetFont($arialb, '', 6);
                $pdf->SetFillColor(235, 235, 235);
                $pdf->SetTextColor(0, 0, 0);

                $pdf->setCellPadding(0);
                $pdf->setCellHeightRatio(0.9);

                $h1 = 7;
                $h2 = 7;
                $h3 = 7;

                $totalHeaderHeight = $h1 + $h2 + $h3;

                // ───────────────────────────────────────────────
                // FIXED COLUMNS (FULL HEIGHT MERGED)
                // ───────────────────────────────────────────────
                $pdf->SetXY($left, $startY);
                $pdf->MultiCell($w_sr, $totalHeaderHeight, "SR.\nNO", 1, 'C', true, 0, '', '', true, 0, false, true, $totalHeaderHeight, 'M');

                $pdf->SetXY($left + $w_sr, $startY);
                $pdf->MultiCell($w_abbr, $totalHeaderHeight, "ABBR", 1, 'C', true, 0, '', '', true, 0, false, true, $totalHeaderHeight, 'M');

                $pdf->SetXY($left + $w_sr + $w_abbr, $startY);
                $pdf->MultiCell($w_sub, $totalHeaderHeight, "SUBJECT", 1, 'C', true, 0, '', '', true, 0, false, true, $totalHeaderHeight, 'M');

                // ───────────────────────────────────────────────
                // VARIABLE SECTION START POSITION
                // ───────────────────────────────────────────────
                $x = $left + $w_sr + $w_abbr + $w_sub;
                $y = $startY;

                // ───────────────────────────────────────────────
                // LEVEL 1 - MAIN GROUP HEADERS
                // ───────────────────────────────────────────────
                // total scheme width = 3 + 1 + 3 = 7 small columns
                $schemeWidth = $w_small * 7;

                $pdf->SetXY($x, $y);
                $pdf->MultiCell(
                    $schemeWidth,
                    $h1,
                    "MARKS & CREDIT SCHEME",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h1,
                    'M'
                );

                $pdf->SetXY($x + $schemeWidth, $y);
                $pdf->MultiCell(
                    ($w_small * 3) + $w_total + $w_grade + $w_gp + $w_credit + $w_status,
                    $h1,
                    "MARKS & GRADES AWARDED",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h1,
                    'M'
                );


                // ───────────────────────────────────────────────
                // LEVEL 2 - SUB GROUP HEADERS
                // ───────────────────────────────────────────────
                $y += $h1;
                $pdf->SetXY($x, $y);

                // MAX MARKS
                $pdf->MultiCell(
                    $w_small * 3,
                    $h2,
                    "MAX MARKS",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h2,
                    'M'
                );

                // TOTAL (rowspan 2)
                $pdf->MultiCell(
                    $w_small * 1,
                    $h2 + $h3,
                    "TOTAL",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h2 + $h3,
                    'M'
                );

                // MIN MARKS
                $pdf->MultiCell(
                    $w_small * 3,
                    $h2,
                    "MIN MARKS",
                    1,
                    'C',
                    true,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $h2,
                    'M'
                );

                // $oldLineWidth = $pdf->GetLineWidth();
                // $pdf->SetLineWidth(0.8);

                // Only Left + Right border thick
                $pdf->MultiCell($w_small, $h2 + $h3, "SEE", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                $pdf->MultiCell($w_small, $h2 + $h3, "CIE", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                $pdf->MultiCell($w_small, $h2 + $h3, "PI", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');

                // $pdf->SetLineWidth($oldLineWidth);





                $pdf->MultiCell($w_total,  $h2 + $h3, "TOTAL\nMARKS", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                // $pdf->MultiCell($w_grade,  $h2 + $h3, "GRADE",        1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                // -------- GRADE --------
                $x_grade = $pdf->GetX();
                $y_grade = $pdf->GetY();

                $pdf->MultiCell($w_grade, $h2 + $h3, '', 1, 'M', true, 0);

                $pdf->StartTransform();
                $pdf->Rotate(90, $x_grade + ($w_grade / 2), $y_grade + (($h2 + $h3) / 2));
                $pdf->Text(
                    $x_grade + ($w_grade / 2) - 3,
                    $y_grade + (($h2 + $h3) / 2) - 2,
                    'GRADE'
                );
                $pdf->StopTransform();

                $pdf->SetXY($x_grade + $w_grade, $y_grade);



                $pdf->MultiCell($w_gp,     $h2 + $h3, "GRADE\nPOINT", 1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');
                // $pdf->MultiCell($w_credit, $h2 + $h3, "CREDIT",       1, 'C', true, 0, '', '', true, 0, false, true, $h2 + $h3, 'M');

                // -------- CREDIT --------
                $x_credit = $pdf->GetX();
                $y_credit = $pdf->GetY();

                $pdf->MultiCell($w_credit, $h2 + $h3, '', 1, 'M', true, 0);

                // Text
                $creditText = 'CREDIT';
                $textWidth  = $pdf->GetStringWidth($creditText);

                $pdf->StartTransform();
                $pdf->Rotate(90, $x_credit + ($w_credit / 2), $y_credit + (($h2 + $h3) / 2));

                $pdf->Text(
                    $x_credit + ($w_credit / 2) - ($textWidth / 2),
                    $y_credit + (($h2 + $h3) / 2),
                    $creditText
                );

                $pdf->StopTransform();

                $pdf->SetXY($x_credit + $w_credit, $y_credit);


                $x_status = $pdf->GetX();
                $y_status = $pdf->GetY();

                $pdf->MultiCell(
                    $w_status,
                    $h2 + $h3,
                    '',
                    1,
                    'M',
                    true,
                    0
                );

                // Text
                $statusText = 'STATUS';
                $textWidth  = $pdf->GetStringWidth($statusText);

                $pdf->StartTransform();
                $pdf->Rotate(90, $x_status + ($w_status / 2), $y_status + (($h2 + $h3) / 2));

                $pdf->Text(
                    $x_status + ($w_status / 2) - ($textWidth / 2),
                    $y_status + (($h2 + $h3) / 2),
                    $statusText
                );

                $pdf->StopTransform();

                $pdf->SetXY($x_status + $w_status, $y_status);




                // ───────────────────────────────────────────────
                // LEVEL 3 - COLUMN TITLES
                // ───────────────────────────────────────────────
                $y += $h2;
                $pdf->SetXY($x, $y);

                // MAX MARKS sub columns
                $pdf->MultiCell($w_small, $h3, "SEE", 1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');
                $pdf->MultiCell($w_small, $h3, "CIE", 1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');
                $pdf->MultiCell($w_small, $h3, "PI",  1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');

                // Skip TOTAL (already rowspan)
                $pdf->SetX($x + ($w_small * 4));

                // MIN MARKS sub columns
                $pdf->MultiCell($w_small, $h3, "SEE", 1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');
                $pdf->MultiCell($w_small, $h3, "CIE", 1, 'C', true, 0, '', '', true, 0, false, true, $h3, 'M');
                $pdf->MultiCell($w_small, $h3, "PI",  1, 'C', true, 1, '', '', true, 0, false, true, $h3, 'M');



                // ───────────────────────────────────────────────
                // MOVE CURSOR BELOW HEADER
                // ───────────────────────────────────────────────
                $pdf->SetY($startY + $totalHeaderHeight);

                // =====================================================
                // DATA ROWS
                // =====================================================
                // =====================================================
                // DATA ROWS (PURE MULTICELL STRUCTURE)
                // =====================================================
                $pdf->SetFont($cambria, '', 6);  // default normal font
                $y = $pdf->GetY();

                $abbrs = [
                    'MAJOR-1',
                    'MAJOR-2',
                    'MINOR',
                    'VSC',
                    'SEC',
                    'AEC',
                    'VEC',
                    'IKS',
                    'CC'
                    // adjust order as per your actual subjects
                ];

                for ($i = 0; $i < 10; $i++) {

                    $base = 12 + $i * 16;
                    $subject = trim($studentData[$base] ?? '');
                    if ($subject === '') continue;

                    $sr   = ($i + 1) . '.';
                    $abbr = $abbrs[$i] ?? '-';

                    $rowData = [
                        $sr,
                        $abbr,
                        $subject,

                        trim($studentData[$base + 1] ?? ''),
                        trim($studentData[$base + 2] ?? ''),
                        trim($studentData[$base + 3] ?? ''),
                        trim($studentData[$base + 4] ?? ''),

                        trim($studentData[$base + 5] ?? ''),
                        trim($studentData[$base + 6] ?? ''),
                        trim($studentData[$base + 7] ?? ''),

                        trim($studentData[$base + 8] ?? ''),
                        trim($studentData[$base + 9] ?? ''),
                        trim($studentData[$base + 10] ?? ''),

                        trim($studentData[$base + 11] ?? ''),
                        trim($studentData[$base + 12] ?? ''),
                        trim($studentData[$base + 13] ?? ''),
                        trim($studentData[$base + 14] ?? ''),
                        trim($studentData[$base + 15] ?? '')
                    ];

                    $widths = [
                        $w_sr,
                        $w_abbr,
                        $w_sub,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_small,
                        $w_total,
                        $w_grade,
                        $w_gp,
                        $w_credit,
                        $w_status
                    ];

                    $startX = $left;
                    $startY = $y;

                    // Calculate row height
                    $maxHeight = 0;
                    foreach ($rowData as $key => $text) {
                        $height = $pdf->getStringHeight($widths[$key], $text);
                        $maxHeight = max($maxHeight, $height);
                    }
                    $rowHeight = max(8, $maxHeight + 2);

                    // Page break
                    if ($startY + $rowHeight > 270) {
                        $pdf->AddPage();
                        $startY = $pdf->GetY();
                    }

                    $x = $startX;
                    foreach ($rowData as $key => $text) {

                        $pdf->SetXY($x, $startY);

                        // ===== FONT BOLD CONDITION =====
                        if ($key === 1 || $key === 17 || $key === 11 || $key === 10 || $key === 12) {
                            $pdf->SetFont($cambriaB, '', 6.5);
                        } else {
                            $pdf->SetFont($cambria, '', 6.5);
                        }

                        // ===== BORDER CONDITION =====
                        if ($key === 10 || $key === 11 || $key === 12||$key===13) {
                            $pdf->SetLineWidth(0.5);   // Bold border
                            $border = 'LRB';             // Top Bottom Left Right
                        } else {
                            $pdf->SetLineWidth(0.3);   // Normal border
                            $border = 1;
                        }

                        // Alignment
                        $align = ($key == 2) ? 'L' : 'C';

                        // Padding
                        if ($key == 2) {
                            $pdf->setCellPaddings(2, 1, 1, 1);
                        } else {
                            $pdf->setCellPaddings(1, 1, 1, 1);
                        }

                        $pdf->MultiCell(
                            $widths[$key],
                            $rowHeight,
                            $text,
                            $border,   // 👈 LTRB applied here
                            $align,
                            0,
                            0,
                            '',
                            '',
                            true,
                            0,
                            false,
                            true,
                            $rowHeight,
                            'M'
                        );

                        $x += $widths[$key];
                    }



                    $y = $startY + $rowHeight;
                }





                // Optional: line or space after last row
                $pdf->Ln(15);
                // $pdf->Ln(10);




                // ───────────────────────────────────────────────
                // Summary Section
                // ───────────────────────────────────────────────

                $pdf->SetFillColor(235, 235, 235);

                $sumH = 7;
                $startX = 15;
                $startY = $pdf->GetY();

                $pdf->SetXY($startX, $startY);

                /* =====================================================
   COLUMN WIDTHS (KEEP FIXED FOR PERFECT ALIGNMENT)
===================================================== */

                $w1  = 19;  // INCENTIVE
                $w2  = 12;  // ΣGPV
                $w3  = 21;  // TOTAL CREDITS
                $w4  = 12;  // SGPA
                $w5  = 13;  // OUT OF
                $w6  = 28;  // TOTAL MARKS OBTAINED
                $w7  = 23;  // OUT OF MARKS
                $w8  = 17;  // RESULT
                $w9  = 17;  // PERCENT
                $w10 = 17;  // REMARKS

                $widths = [$w1, $w2, $w3, $w4, $w5, $w6, $w7, $w8, $w9, $w10];


                /* =====================================================
   HEADER ROW  (Bold + Center)
===================================================== */

                $pdf->SetFont($cambriaB, '', 7.5);

                $headers = [
                    'INCENTIVE',
                    'ΣGPV',
                    "TOTAL\nCREDITS",
                    'SGPA',
                    'OUT OF',
                    "TOTAL MARKS\nOBTAINED",
                    "OUT OF\nMARKS",
                    'RESULT',
                    'PERCENT',
                    'REMARKS'
                ];

                $x = $startX;

                foreach ($headers as $i => $head) {
                    $pdf->SetXY($x, $startY);
                    $pdf->MultiCell($widths[$i], $sumH, $head, 1, 'C', 1);
                    $x += $widths[$i];
                }

                $startY += $sumH;


                /* =====================================================
   DATA ROW (Normal + Center)
===================================================== */

                $pdf->SetFont($cambriaB, '', 7.5);

                $data = [
                    $incentive,
                    $sgpv,
                    $total_credits,
                    $sgpa,
                    $out_of,
                    $total_marks,
                    $out_of_marks,
                    $result,
                    $percent,
                    ''
                ];

                $x = $startX;
                $rowY = $startY;

                foreach ($data as $i => $value) {

                    $pdf->SetXY($x, $rowY);

                    $pdf->MultiCell(
                        $widths[$i],
                        $sumH,
                        trim($value),
                        1,
                        'C',      // horizontal center
                        0,
                        0,        // stay on same row
                        '',
                        '',
                        true,
                        0,
                        false,
                        true,
                        $sumH,    // fixed height
                        'M'       // vertical middle
                    );

                    $x += $widths[$i];
                }

                $startY += $sumH;

                // $startY += $sumH;


                /* =====================================================
   PERFORMANCE ROW (Normal + Left Aligned)
===================================================== */

                $pdf->SetFont($cambriaB, '', 7.5);
                $pdf->SetFillColor(235, 235, 235);

                // Merge first 5 columns
                $leftEmpty = $w1 + $w2 + $w3 + $w4 + $w5;

                $pdf->SetXY($startX, $startY);
                $pdf->MultiCell(
                    $leftEmpty,
                    $sumH,
                    '',
                    1,
                    'C',
                    1,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $sumH,
                    'M'
                );

                // PERFORMANCE label (merge w6 + w7)
                $performanceLabelWidth = $w6 + $w7;

                $pdf->SetXY($startX + $leftEmpty, $startY);
                $pdf->MultiCell(
                    $performanceLabelWidth,
                    $sumH,
                    'PERFORMANCE',
                    1,
                    'C',   // center
                    1,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $sumH,
                    'M'
                );

                // Dynamic performance value (merge w8+w9+w10)
                $performanceValueWidth = $w8 + $w9 + $w10;

                $pdf->SetXY($startX + $leftEmpty + $performanceLabelWidth, $startY);
                $pdf->MultiCell(
                    $performanceValueWidth,
                    $sumH,
                    strtoupper($performance ?? ''),
                    1,
                    'C',   // center
                    1,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $sumH,
                    'M'
                );

                $startY += $sumH;


                $pdf->Ln(10);
                $pdf->SetFont($cambria, '', 8);
                $pdf->Cell(180, 5, 'ABC/APAAR ID : ' . $abc_apaar, 0, 1, 'R');

                // ───────────────────────────────────────────────
                // Footer (Exact Compact Layout Like Image)
                // ───────────────────────────────────────────────

                $pdf->SetFont($cambria, '', 6);

                // Move slightly down from previous content
                $currentY = $pdf->GetY();
                $pdf->SetXY(15, $currentY - 2);

                // Line 1
                $pdf->MultiCell(
                    180,
                    3.5,   // smaller height = tight spacing
                    '(THIS STATEMENT IS SUBJECT TO CORRECTIONS IF ANY)',
                    0,
                    'L',
                    0,
                    1
                );

                // Line 2
                $pdf->SetX(15);
                $pdf->MultiCell(
                    180,
                    3.5,
                    'REFER CREDIT GRADE SYSTEM TABLE: Criteria for Award of Grades:',
                    0,
                    'L',
                    0,
                    1
                );

                // Grade System Line (Wrapped Compactly)
                // $pdf->SetX(15);
                // $pdf->MultiCell(
                //     180,
                //     3.5,
                //     'Grade: O Point: 10.00 – 9.00 Range: 100-90 (OUTSTANDING) | ' .
                //         '| Grade: A+ Point: 8.99-8.00 Range: 89-80 (EXELENT) | ' .
                //         '|Grade: A Point: 7.99 – 7.00 Range: 79-70 (VERY GOOD) | ' .
                //         '| Grade: B+' .

                //         '| Point: 6.99-6.00 Range: 69-60 (GOOD)| ' .
                //         ') Grade: B Point: 5.99 – 5.50 Range: 59-55 (ABOVE AVERAGE)  | ' .
                //         '| Grade: C Point: 5.49-5.00 Range: 54-50 (AVERAGE) ',
                //     0,
                //     'L',
                //     0,
                //     1
                // );
                $pdf->SetX(15);

                $text1 =
                    'Grade: O Point: 10.00 – 9.00 Range: 100-90 (OUTSTANDING) | ' .
                    'Grade: A+ Point: 8.99-8.00 Range: 89-80 (EXCELLENT) | ' .
                    'Grade: A Point: 7.99 – 7.00 Range: 79-70 (VERY GOOD) |Grade: B+ ';

                $pdf->MultiCell(180, 3.5, $text1, 0, 'L', 0, 1);

                // 🔹 Small vertical gap before second line
                $pdf->Ln(0);   // adjust 0.5 / 1 / 1.5 as needed

                $pdf->SetX(15);

                $text2 =
                    // 'Grade: B+ ' .
                    'Point: 6.99-6.00 Range: 69-60 (GOOD) | ' .
                    'Grade: B Point: 5.99 – 5.50 Range: 59-55 (ABOVE AVERAGE) | ' .
                    'Grade: C Point: 5.49-5.00 Range: 54-50 (AVERAGE)';

                $pdf->MultiCell(180, 3.5, $text2, 0, 'L');


                // Note Line (Very Tight)
                $pdf->SetX(15);
                $pdf->MultiCell(
                    180,
                    3,
                    'Note: (*) Pass by grace marks vide AUTONOMY Direction No. 8 of 2024',
                    0,
                    'L'
                );

                //End pdf			

                // Ghost image
                // $nameOrg = $candidate_name;

                /*$ghost_font_size = '13';
    			$ghostImagex = 132;
    			$ghostImagey = 267;
    			$ghostImageWidth = 55;
    			$ghostImageHeight = 9.8;*/

                $nameOrg = $candidate_name;
                $ghost_font_size = '13';
                $ghostImagex = 13;
                $ghostImagey = 276;
                $ghostImageWidth = 55;
                $ghostImageHeight = 9.8;
                $name = substr(str_replace(' ', '', strtoupper($nameOrg)), 0, 6);
                $tmpDir = $this->createTemp(public_path() . '\backend\images\ghosttemp\temp');
                $w = $this->CreateMessage($tmpDir, $name, $ghost_font_size, '');
                $pdf->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
                $pdfBig->Image("$tmpDir/" . $name . "" . $ghost_font_size . ".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
                $pdf->setPageMark();
                $pdfBig->setPageMark();
                $serial_no = $GUID = $studentData[0];
                // dd($studentData);
                //qr code    
                $dt = date("_ymdHis");
                $str = $GUID . $dt;

                $codeContents = "";
                $codeContents .= $candidate_name;
                $codeContents .= "\n";
                $codeContents .= $student_id;
                $codeContents .= "\n\n" . strtoupper(md5($str));

                $encryptedString = strtoupper(md5($str));


                // $codeContents =$encryptedString = strtoupper(md5($str));
                $qr_code_path = public_path() . '\\' . $subdomain[0] . '\backend\canvas\images\qr\/' . $encryptedString . '.png';
                $qrCodex = 75;
                $qrCodey = 265;
                $qrCodeWidth = 21;
                $qrCodeHeight = 21;
                $ecc = 'L';
                $pixel_Size = 1;
                $frame_Size = 1;
                \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
                // \QrCode::size(75.6)
                //     ->backgroundColor(255, 255, 0)
                //     ->format('png')
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
                    'fgcolor' => array(0, 0, 0),
                    'bgcolor' => false, //array(255,255,255),
                    'text' => true,
                    'font' => 'helvetica',
                    'fontsize' => 9,
                    'stretchtext' => 7
                );

                $barcodex = 14;
                $barcodey = 262;
                $barcodeWidth = 56;
                $barodeHeight = 13;
                $pdf->SetAlpha(1);
                $pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
                $pdfBig->SetAlpha(1);
                $pdfBig->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');

                $str = $nameOrg;
                $str = strtoupper(preg_replace('/\s+/', '', $str));

                $microlinestr = $str;
                $pdf->SetFont($arialb, '', 2, '', false);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetXY(75, 263);
                $pdf->Cell(21, 0, $microlinestr, 0, false, 'C');

                $pdfBig->SetFont($arialb, '', 2, '', false);
                $pdfBig->SetTextColor(0, 0, 0);
                $pdfBig->SetXY(75, 263);
                $pdfBig->Cell(21, 0, $microlinestr, 0, false, 'C');
    
                if ($previewPdf != 1) {

                    $certName = str_replace("/", "_", $GUID) . ".pdf";

                    $myPath = public_path() . '/backend/temp_pdf_file';

                    $fileVerificationPath = $myPath . DIRECTORY_SEPARATOR . $certName;

                    $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');

                    $this->addCertificate($serial_no, $certName, $dt, $template_id, $admin_id);

                    $username = $admin_id['username'];
                    date_default_timezone_set('Asia/Kolkata');

                    $content = "#" . $log_serial_no . " serial No :" . $serial_no . PHP_EOL;
                    $date = date('Y-m-d H:i:s') . PHP_EOL;
                    $print_datetime = date("Y-m-d H:i:s");


                    $print_count = $this->getPrintCount($serial_no);
                    $printer_name = /*'HP 1020';*/ $printer_name;

                    $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no, 'Memorandum', $admin_id, $card_serial_no);

                    $card_serial_no = $card_serial_no + 1;
                }

                $generated_documents++;

                if (isset($pdf_data['generation_from']) && $pdf_data['generation_from'] == 'API') {
                    $updated = date('Y-m-d H:i:s');
                    ThirdPartyRequests::where('id', $pdf_data['request_id'])->update(['generated_documents' => $generated_documents, "updated_at" => $updated]);
                } else {
                    //For Custom loader calculation
                    //echo $generated_documents;
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
                //delete temp dir 26-04-2022 
                CoreHelper::rrmdir($tmpDir);

                $pdf_data_obj = $pdfBig; // Get the PDF data as a string

                // Store the PDF data in the session
                Session::put('pdf_data_obj', $pdf_data_obj);

                // Update code for batchwise genration
                return "Will be generated soon!";
            }
            if ($previewPdf != 1) {
                $this->updateCardNo('Sbcity', $card_serial_no - $cardDetails->starting_serial_no, $card_serial_no);
            }
        }

        $msg = '';

        $file_name =  str_replace("/", "_", 'Sbcity' . date("Ymdhms")) . '.pdf';

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();

        $filename = public_path() . '/backend/tcpdf/examples/' . $file_name;

        $pdfBig->output($filename, 'F');

        if ($previewPdf != 1) {
            $aws_qr = \File::copy($filename, public_path() . '/' . $subdomain[0] . '/backend/tcpdf/examples/' . $file_name);
            @unlink($filename);
            //$no_of_records = count($studentDataOrg);
            //Update code for batchwise genration
            $no_of_records = $pdf_data['highestrow'];
            $user = $admin_id['username'];
            $template_name = "Sbcity";
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
            $msg = "<b>Click <a href='" . $path . $subdomain[0] . "/backend/tcpdf/examples/" . $file_name . "'class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
        } else {
            $aws_qr = \File::copy($filename, public_path() . '/' . $subdomain[0] . '/backend/tcpdf/examples/preview/' . $file_name);
            @unlink($filename);
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $path = $protocol . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/';
            $pdf_url = $path . $subdomain[0] . "/backend/tcpdf/examples/preview/" . $file_name;
            $msg = "<b>Click <a href='" . $path . $subdomain[0] . "/backend/tcpdf/examples/preview/" . $file_name . "' class='downloadpdf download' target='_blank'>Here</a> to download file<b>";
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

    public function uploadPdfsToServer()
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $certName = "abc.pdf";

        $files = $this->getDirContents(public_path() . '/' . $subdomain[0] . '/backend/pdf_file/');

        foreach ($files as $filename) {
            echo $filename . "<br>";
        }
    }

    public function getDirContents($dir, &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            }
        }

        return $results;
    }

    public function downloadPdfsFromServer()
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $accesskey = "Tz7IOmG/9+tyxZpRTAam+Ll3eqA9jezqHdqSdgi+BjHsje0+VM+pKC6USBuR/K0nkw5E7Psw/4IJY3KMgBMLrA==";
        $storageAccount = 'seqrdocpdf';
        $containerName = 'pdffile';

        $files = $this->getDirContents(public_path() . '/' . $subdomain[0] . '/backend/pdf_file/');

        foreach ($files as $filename) {
            $myFile = pathinfo($filename);
            $blobName = 'BMCC\PC\\' . $myFile['basename'];
            echo $destinationURL = "https://$storageAccount.blob.core.windows.net/$containerName/$blobName";

            $local_server_file_path = public_path() . '/' . $subdomain[0] . '/backend/pdf_file_downloaded/' . $blobName;
            if (file_exists($destinationURL)) {
                file_put_contents($local_server_file_path, file_get_contents($destinationURL));
            }
        }
    }

    public function addCertificate($serial_no, $certName, $dt, $template_id, $admin_id)
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
        /*copy($file1, $file2);        
        $aws_qr = \File::copy($file2,$pdfActualPath);
        @unlink($file2);*/
        $source = \Config::get('constant.directoryPathBackward') . "\\backend\\temp_pdf_file\\" . $certName;
        $output = \Config::get('constant.directoryPathBackward') . $subdomain[0] . "\\backend\\pdf_file\\" . $certName;
        CoreHelper::compressPdfFile($source, $output);
        @unlink($file1);

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

            $result = SbStudentTable::create(['serial_no' => 'T-' . $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        } else {
            $resultu = StudentTable::where('serial_no', '' . $serial_no)->update(['status' => '0']);
            // Insert the new record

            $result = StudentTable::create(['serial_no' => $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'template_type' => 2]);
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
        $connId = ftp_connect($ftpHost, $ftpPort) or die("Couldn't connect to $ftpHost");
        // login to FTP server
        $ftpLogin = ftp_login($connId, $ftpUsername, $ftpPassword);
        // local & server file path
        $localFilePath  = $pdfActualPath;
        $remoteFilePath = $certName;
        // try to upload file
        if (ftp_put($connId, $remoteFilePath, $localFilePath, FTP_BINARY)) {
            //echo "File transfer successful - $localFilePath";
        } else {
            //echo "There was an error while uploading $localFilePath";
        }
        // close the connection
        ftp_close($connId);
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

    public function nextPrintSerial()
    {
        $current_year = 'PN/' . $this->getFinancialyear() . '/';
        // find max
        $maxNum = 0;

        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }

        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        $result = \DB::select("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num "
            . "FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '$current_year'");
        //get next num
        $maxNum = $result[0]->next_num + 1;

        return $current_year . $maxNum;
    }

    public function getNextCardNo($template_name)
    {
        //Updated by Mandar for api based pdf generation
        if (Auth::guard('admin')->user()) {
            $auth_site_id = Auth::guard('admin')->user()->site_id;
        } else {
            $auth_site_id = $this->pdf_data['auth_site_id'];
        }
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


    public function getFinancialyear()
    {
        $yy = date('y');
        $mm = date('m');
        $fy = str_pad($yy, 2, "0", STR_PAD_LEFT);
        if ($mm > 3)
            $fy = $fy . "-" . ($yy + 1);
        else
            $fy = str_pad($yy - 1, 2, "0", STR_PAD_LEFT) . "-" . $fy;
        return $fy;
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
            $filename = public_path() . "/backend/canvas/ghost_images/alpha_GHOST.png";
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

    function GetStringPositions($strings, $pdf)
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
        $ret[0] = (205 - $sum) / 2;
        for ($i = 1; $i < $len; $i++) {
            $ret[$i] = $ret[$i - 1] + $w[$i - 1];
        }

        return $ret;
    }

    function sanitizeQrString($content)
    {
        $find = array('â€œ', 'â€™', 'â€¦', 'â€”', 'â€“', 'â€˜', 'Ã©', 'Â', 'â€¢', 'Ëœ', 'â€'); // en dash
        $replace = array('“', '’', '…', '—', '–', '‘', 'é', '', '•', '˜', '”');
        return $content = str_replace($find, $replace, $content);
    }
}
