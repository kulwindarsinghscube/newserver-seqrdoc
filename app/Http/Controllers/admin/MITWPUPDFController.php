<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use TCPDF;
//use phpseclib2\Crypt\AES;
//use phpseclib\phpseclib\phpseclib\Crypt\AES;

// use phpseclib\phpseclib\phpseclib\Crypt\AES;
use App\Utility\GibberishAES;
use TCPDF_FONTS;
use App\models\StudentTable;
use App\Helpers\CoreHelper;
use DB;
use Storage;
use DPDF;

//phpseclib\phpseclib\phpseclib

class MITWPUPDFController extends Controller
{
    

    public function pdfView($prnNo){

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        $ghostImgArr = array();
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        // $pdf->SetCreator('TCPDF');
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

        $Times_New_Roman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $timesbi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbi.ttf', 'TrueTypeUnicode', '', 96);
        $timesi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesi.ttf', 'TrueTypeUnicode', '', 96);

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
        
        $pdf->AddPage();

        
        

        $result = DB::table('convo_students')
        ->select('*')
        ->where('prn','=',$prnNo)
        // ->limit(1)
        ->first();

        // print_r($result->prn);
        
        if(!$result) {
            $pdf->SetFont($arialb, '', 21, '', false);
            $pdf->SetXY(21, 70);
            $pdf->MultiCell(170, 0, 'Data Not Found',1, 'C', 0, 0);
            $pdf->Output('sample.pdf', 'I');   
            exit();

        }
        


        // Watermark
        // Get the page width/height

        $myPageWidth = $pdf->getPageWidth();
        $myPageHeight = $pdf->getPageHeight();

        // Find the middle of the page and adjust.
        $myX = ( $myPageWidth / 2 ) - 75;
        $myY = ( $myPageHeight / 2 ) + 25;

        // Set the transparency of the text to really light
        $pdf->SetAlpha(0.09);

        // Rotate 45 degrees and write the watermarking text
        $pdf->StartTransform();
        $pdf->Rotate(45, $myX, $myY);

        // echo $myX;

        $pdf->SetFont("courier", "", 70);
        // $pdf->Text($myX, $myY,"PREVIEW PDF");
        $pdf->SetXY(15, 180);
        $pdf->MultiCell(180, 0, 'PREVIEW PDF',0, 'C', 0, 0);
        $pdf->StopTransform();
       
        // Reset the transparency to default
        $pdf->SetAlpha(1);



        // $studentName = $result->full_name_krutidev;
        //$pdf->SetTextColor(0,0,0);
        // $pdf->SetFont($arial,'', 10, '', false);
        // $pdf->SetXY(45, 70);
        // $pdf->MultiCell(60, 0, 'For example, a program name such as',1, 'L', 0, 0);

        $profile_path_org = public_path().'\\'.$subdomain[0].'\backend\students\\'.$result->student_photo;   

        if(file_exists($profile_path_org)) {


            $profilex = 165;
            $profiley = 17;
            $profileWidth = 25;
            $profileHeight = 25;
            
            $pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
            $pdf->setPageMark();

        }

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 51);
        $pdf->MultiCell(180, 0, "laLFkkid v/;{k rFkk dk;Zdkjh v/;{k] iz'kkldh; vf/kdkjh ,oa ".$result->faculty_name_krutidev." dh flQkfj'k dks vuqeksfnr djrs gq,]", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
        $pdf->SetXY(15, 68);
        $pdf->MultiCell(180, 0, $result->full_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 78);
        $pdf->MultiCell(180, 0, "dks", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
        $pdf->SetXY(15, 86);
        $pdf->MultiCell(180, 0, $result->course_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);
        

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 97);

        $issue_date_krutidev= '19 vDVwcj „å„†';
        $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".$result->completion_date_krutidev." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd ".$result->specialization_krutidev." Lis'kykbts'ku esa mUgksausa ".$result->cgpa_krutidev." lhthih, izkIr fd;k gSA bls ekU;rk nsrs gq, vkt ".$issue_date_krutidev." ds volj ij ge vius uke rFkk fo'ofo|ky; dh eqgj ds vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);

        // $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ebZ „å„… esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd", 0, 'C', 0, 0, '', '', true, 0, true);


        // $pdf->SetTextColor(50,50,154);
        // $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        // $pdf->SetXY(15, 105);
        // $pdf->MultiCell(182, 0, "d‚EI;qVj lk;Ul ,¡M ,aftfuvfjax Lis'kykbts'ku esa mUgksausa ".$result->cgpa_krutidev." lhthih, izkIr fd;k gSA", 0, 'C', 0, 0, '', '', true, 0, true);


        // $pdf->SetTextColor(50,50,154);
        // $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        // $pdf->SetXY(15, 113);
        // $pdf->MultiCell(180, 0, "bls ekU;rk nsrs gq, vkt 04 uoacj 2023 ds volj ij ge vius uke rFkk fo'ofo|ky; dh eqgj ds vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 141);
        $pdf->MultiCell(190, 0, "The Founder President and the Executive President approbating the recommendations of the Governing Body and ".$result->faculty_name." hereby confer upon", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '', 19.98, '', false);
        $pdf->SetXY(10, 158);
        $pdf->MultiCell(190, 0, $result->full_name, 0, 'C', 0, 0, '', '', true, 0, true);

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 167.5);
        $pdf->MultiCell(190, 0, "the Degree of", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '', 19.98, '', false);
        $pdf->SetXY(10, 174);
        $pdf->MultiCell(190, 0, $result->course_name, 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 185);
        $completion_date_new = date("F Y", strtotime($result->completion_date));
        
        $specilaText = '';
        if($result->specialization) {
            $specilaText = "in ".$result->specialization." Specialisation";
        }
        $issue_formatted_date = '';

        $issue_date= '2024-10-19';

        if($issue_date) {
            $issue_formatted_date = date("jS", strtotime($issue_date))  . " day of " . date("F", strtotime($issue_date)) . " in the year " . date("Y", strtotime($issue_date));
        }
        
        // $issue_formatted_date = date("jS", strtotime($result->issue_date))  . " day of " . date("F", strtotime($result->issue_date)) . " in the year " . date("Y", strtotime($result->issue_date));

        $pdf->MultiCell(190, 0, $specilaText." With ".$result->cgpa." CGPA secured 
                        <br>in the examination held in ".$completion_date_new." 
                        <br>In recognition we have hereunder placed our names and
                        <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);
        
        // $pdf->MultiCell(190, 0, "in Computer Science and Engineering  Specialisation With ".$result->cgpa." CGPA secured 
        //             <br>in the examination held in May 2023 
        //             <br>In recognition we have hereunder placed our names and
        //             <br>the seal of the University on this 04th day of November in the year 2023.", 0, 'C', 0, 0, '', '', true, 0, true);



        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 230);
        $pdf->MultiCell(180, 0, 'vkbZps uko & '.$result->mother_name_krutidev, 0, 'L', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '',12, '', false);
        $pdf->SetXY(15, 238);
        $pdf->MultiCell(100, 0, 'Mother Name - '.$result->mother_name, 0, 'L', 0, 0, '', '', true, 0, true);

        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont($times, '', 11, '', false);
        $pdf->SetXY(10, 255);
        $pdf->MultiCell(190, 0, 'This is a review purpose preview pdf. Please confirm the correctness of the data in this PDF, on the convocation portal.', 0, 'L', 0, 0, '', '', true, 0, true);

        $pdf->SetFont($times, '', 11, '', false);
        $pdf->SetXY(10, 260);
        $pdf->MultiCell(190, 0, 'Do not print it.', 0, 'L', 0, 0, '', '', true, 0, true);
        
        $pdf->Output('sample.pdf', 'I');   
    }



    public function pdfViewPhd($prnNo){

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        $ghostImgArr = array();
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        // $pdf->SetCreator('TCPDF');
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

        $Times_New_Roman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $timesbi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbi.ttf', 'TrueTypeUnicode', '', 96);
        $timesi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesi.ttf', 'TrueTypeUnicode', '', 96);

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
        
        $pdf->AddPage();

        
        

        $result = DB::table('convo_students')
        ->select('*')
        ->where('prn','=',$prnNo)
        // ->limit(1)
        ->first();

        // print_r($result->prn);
        
        if(!$result) {
            $pdf->SetFont($arialb, '', 21, '', false);
            $pdf->SetXY(21, 70);
            $pdf->MultiCell(170, 0, 'Data Not Found',1, 'C', 0, 0);
            $pdf->Output('sample.pdf', 'I');   
            exit();

        }
        


        // Watermark
        // Get the page width/height

        $myPageWidth = $pdf->getPageWidth();
        $myPageHeight = $pdf->getPageHeight();

        // Find the middle of the page and adjust.
        $myX = ( $myPageWidth / 2 ) - 75;
        $myY = ( $myPageHeight / 2 ) + 25;

        // Set the transparency of the text to really light
        $pdf->SetAlpha(0.09);

        // Rotate 45 degrees and write the watermarking text
        $pdf->StartTransform();
        $pdf->Rotate(45, $myX, $myY);

        // echo $myX;

        $pdf->SetFont("courier", "", 70);
        // $pdf->Text($myX, $myY,"PREVIEW PDF");
        $pdf->SetXY(15, 180);
        $pdf->MultiCell(180, 0, 'PREVIEW PDF',0, 'C', 0, 0);
        $pdf->StopTransform();
       
        // Reset the transparency to default
        $pdf->SetAlpha(1);



        // $studentName = $result->full_name_krutidev;
        //$pdf->SetTextColor(0,0,0);
        // $pdf->SetFont($arial,'', 10, '', false);
        // $pdf->SetXY(45, 70);
        // $pdf->MultiCell(60, 0, 'For example, a program name such as',1, 'L', 0, 0);

        $profile_path_org = public_path().'\\'.$subdomain[0].'\backend\students\\'.$result->student_photo;   

        if(file_exists($profile_path_org)) {


            $profilex = 165;
            $profiley = 17;
            $profileWidth = 25;
            $profileHeight = 25;
            
            $pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
            $pdf->setPageMark();

        }

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 51);
        $pdf->MultiCell(180, 0, "laLFkkid v/;{k rFkk dk;Zdkjh v/;{k] iz'kkldh; vf/kdkjh ,oa ".$result->faculty_name_krutidev." dh flQkfj'k dks vuqeksfnr djrs gq,]", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
        $pdf->SetXY(15, 68);
        $pdf->MultiCell(180, 0, $result->full_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 78);
        $pdf->MultiCell(180, 0, "dks", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
        $pdf->SetXY(15, 86);
        $pdf->MultiCell(180, 0, $result->course_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 97);
        $issue_date_krutidev= '19 vDVwcj „å„†';
        $pdf->MultiCell(182, 0, "mUgksaus ".$result->completion_date_krutidev." esa fofu;eksa ds rgr bl mikf/k dks çkIr djus ds fy, fu/kkZfjr vko';drkvksa
            dks iwjk fd;k gS vkSj lQyrkiwoZd fFkfll 'kh\"kZd ß ".$result->topic_krutidev." Þ dk leFkZu fd;k gS
            bls ekU;rk nsrs gq, vkt ".$issue_date_krutidev." ds volj ij ge vius uke rFkk fo'ofo|ky; dh eqgj ds
            vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);



        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 141);
        $pdf->MultiCell(190, 0, "The Founder President and the Executive President approbating the recommendations of the Governing Body and ".$result->faculty_name." hereby confer upon", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '', 19.98, '', false);
        $pdf->SetXY(10, 158);
        $pdf->MultiCell(190, 0, $result->full_name, 0, 'C', 0, 0, '', '', true, 0, true);

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 167.5);
        $pdf->MultiCell(190, 0, "the Degree of", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '', 18, '', false);
        $pdf->SetXY(10, 174);
        $pdf->MultiCell(190, 0, $result->course_name, 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 185);

        $completion_date_new = date("F Y", strtotime($result->completion_date));

        $issue_date= '2024-10-19';
        $issue_formatted_date = '';
        if($issue_date) {
            $issue_formatted_date = date("jS", strtotime($issue_date))  . " day of " . date("F", strtotime($issue_date)) . " in the year " . date("Y", strtotime($issue_date));
        }
        $pdf->MultiCell(190, 0, 'She has fulfilled the requirements for the degree as prescribed under the regulations and successfully defended the thesis  titled " '.$result->topic  .'"  in '.$completion_date_new.'
                    <br>In recognition we have hereunder placed our names and
                    <br>the seal of the University on this '.$issue_formatted_date, 0, 'C', 0, 0, '', '', true, 0, true);



        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 230);
        $pdf->MultiCell(180, 0, 'vkbZps uko & '.$result->mother_name_krutidev, 0, 'L', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '',12, '', false);
        $pdf->SetXY(15, 238);
        $pdf->MultiCell(100, 0, 'Mother Name - '.$result->mother_name, 0, 'L', 0, 0, '', '', true, 0, true);

        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont($times, '', 11, '', false);
        $pdf->SetXY(10, 255);
        $pdf->MultiCell(190, 0, 'This is a review purpose preview pdf. Please confirm the correctness of the data in this PDF, on the convocation portal.', 0, 'L', 0, 0, '', '', true, 0, true);

        $pdf->SetFont($times, '', 11, '', false);
        $pdf->SetXY(10, 260);
        $pdf->MultiCell(190, 0, 'Do not print it.', 0, 'L', 0, 0, '', '', true, 0, true);
        
        $pdf->Output('sample.pdf', 'I');   
    }


}
