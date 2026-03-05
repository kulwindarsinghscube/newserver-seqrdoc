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
use Dompdf\Dompdf;

class PDFNewController extends Controller
{



    public function testPdf(){
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        $inputFileType = 'Xlsx';
        $target_path = public_path().'/'.$subdomain[0].'/backend/sample_excel';
        $fullpath = $target_path.'/sample_excel_b_com_Computers.xlsx';

        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        /**  Load $inputFileName to a Spreadsheet Object  **/
 
        $objPHPExcel1 = $objReader->load($fullpath);
        $sheet1 = $objPHPExcel1->getSheet(0);
        $highestColumn1 = $sheet1->getHighestColumn();
        $highestRow1 = $sheet1->getHighestDataRow();
        
        $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
        unset($rowData1[0]);

        $sheet2 = $objPHPExcel1->getSheet(1);
        $rowData2=$sheet2->toArray();
        unset($rowData2[0]);
        $rowData2=array_values($rowData2);

        $subjectDataOrg=$rowData2;

        // $studentDataOrg = $rowData1;
        // $batchSize = 7; // Define how many records per batch
        // $chunks = array_chunk($rowData1, $batchSize); // Split the array into chunks of 6
        

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
        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
    
        $palatino = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\PALA.TTF', 'TrueTypeUnicode', '', 96);
        $palatinob = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\PALAB.TTF', 'TrueTypeUnicode', '', 96);
        $palatinobi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\PALABI.TTF', 'TrueTypeUnicode', '', 96);

        // Subject Hour
        $subjectsArr = array();
        if($subjectDataOrg) {
            foreach ($subjectDataOrg as $element) {
                $subjectsArr[$element[0]][] = $element;
            }
        }

        foreach($rowData1 as $studentData) {
            $pdfBig->AddPage();
            $high_res_bg="MALLA_REDDY_UNIVERSITY_A4_MALLA_REDDY_UNIVERSITY_A4_page-0001.jpg"; // MALLA_REDDY_UNIVERSITY_A4_MALLA_REDDY_UNIVERSITY_A4_page-0001.jpg
            $low_res_bg="MALLA_REDDY_UNIVERSITY_A4_MALLA_REDDY_UNIVERSITY_A4_page-0001.jpg";
            
            
            //set background image
            $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;           
            $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
            $pdfBig->setPageMark(); 	
            
            $unique_id = trim($studentData[0]);
            $cgcs_no = trim($studentData[1]);
            $programme = trim($studentData[2]);
            $hall_ticket_no = trim($studentData[3]);
            $candidate_name = trim($studentData[4]);
            $father_name = trim($studentData[5]);
            $year_of_admission = trim($studentData[6]);
            $month_and_year_of_pass = trim($studentData[7]);
            $class_awarded = trim($studentData[8]);
            $credit_registered = trim($studentData[9]);
            $cgpa = trim($studentData[10]);
            $credits_of_cgpa = trim($studentData[11]);
            $min_no_credit_award_degree = trim($studentData[12]);
            $date = trim($studentData[13]);

            
            // Start pdfBig
            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '0', $bottom = '0');
            $pdfBig->SetFont($palatino, '', 9, '', false); 
            $pdfBig->SetXY(11,44);        
            $pdfBig->MultiCell(18.5, 5, 'CGGS No.:', 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($palatinob, '', 9, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),44);        
            $pdfBig->MultiCell(100, 5, $cgcs_no, 0, "L", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetFont($palatino, 'BI', 16);
            $pdfBig->SetFont($palatinobi, '', 13);
            $pdfBig->SetXY(0,51);        
            $pdfBig->MultiCell(210, 5, 'Consolidated Grade / Credit Sheet', 0, "C", 0, 0, '', '', true, 0, true);

            $tableHeight= 5;
            $lty1 = 59;
            // left Side Box
            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY(16,$lty1);        
            $pdfBig->MultiCell(32, $tableHeight, 'Programme', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),$lty1);        
            $pdfBig->MultiCell(3, $tableHeight, ':', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),$lty1);        
            $pdfBig->MultiCell(56, $tableHeight, $programme, 0, "L", 0, 0, '', '', true, 0, true);

            
            $lty2 = $lty1 + $tableHeight;
            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY(16,$lty2);        
            $pdfBig->MultiCell(32, $tableHeight, 'Hall Ticket No.', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),$lty2);        
            $pdfBig->MultiCell(3, $tableHeight, ':', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),$lty2);        
            $pdfBig->MultiCell(56, $tableHeight, $hall_ticket_no, 0, "L", 0, 0, '', '', true, 0, true);



            $lty3 = $lty2 + $tableHeight;
            $pdfBig->startTransaction();
            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            // get the number of lines
            $lines = $pdfBig->MultiCell(56, $tableHeight, $candidate_name, 0, 'C', 0, 0, '', '', true, 0, false,true, 0);
            // restore previous object
            $pdfBig=$pdfBig->rollbackTransaction();	
            
            if($lines>1){
                $tableHeight = 8.5;
            }else{
                $tableHeight = $tableHeight;
            }


            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY(16,$lty3);        
            $pdfBig->MultiCell(32, $tableHeight, 'Name', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),$lty3);        
            $pdfBig->MultiCell(3, $tableHeight, ':', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),$lty3);        
            $pdfBig->MultiCell(56, $tableHeight, $candidate_name, 0, "L", 0, 0, '', '', true, 0, true);


            $lty4 = $lty3 + $tableHeight;
            $pdfBig->startTransaction();
            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            // get the number of lines
            $lines1 = $pdfBig->MultiCell(56, $tableHeight, $father_name, 0, 'C', 0, 0, '', '', true, 0, false,true, 0);
            // restore previous object
            $pdfBig=$pdfBig->rollbackTransaction();	
            
            if($lines1>1){
                $tableHeight = 9;
            }else{
                $tableHeight = $tableHeight;
            }

            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY(16,$lty4);        
            $pdfBig->MultiCell(32, $tableHeight, 'Father Name', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),$lty4);        
            $pdfBig->MultiCell(3, $tableHeight, ':', 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),$lty4);        
            $pdfBig->MultiCell(56, $tableHeight, $father_name, 0, "L", 0, 0, '', '', true, 0, true);


            $tableY = $lty4 + $tableHeight;


            // Right Side Box
            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY(135,59.5);        
            $pdfBig->MultiCell(32, 7, 'Year of Admission' , 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),59.5);        
            $pdfBig->MultiCell(32, 7, ' :&nbsp;&nbsp;'.$year_of_admission, 0, "L", 0, 0, '', '', true, 0, true);


            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY(135,66.5);        
            $pdfBig->MultiCell(32, 7, 'Month & Year of Pass' , 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),66.5);        
            $pdfBig->MultiCell(32, 7,' :&nbsp;&nbsp;'.$month_and_year_of_pass, 0, "L", 0, 0, '', '', true, 0, true);



            $pdfBig->SetFont($palatino, '', 8.5, '', false); 
            $pdfBig->SetXY(135,73.5);        
            $pdfBig->MultiCell(32, 7, 'Class Awarded' , 0, "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 8.5, '', false); 
            $pdfBig->SetXY($pdfBig->getX(),73.5);        
            $pdfBig->MultiCell(32, 7, ' :&nbsp;&nbsp;'.$class_awarded, 0, "L", 0, 0, '', '', true, 0, true);

            //  Left Table heading
            $pdfBig->SetFont($palatino, '', 5.5, '', false); 
            $pdfBig->SetXY(13,$tableY+15);        
            $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
            $pdfBig->StartTransform();
            $pdfBig->Rotate(90);
            $pdfBig->MultiCell(15, 4, 'Sr.No', 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->StopTransform();


            $pdfBig->SetXY(17,$tableY);        
            $pdfBig->setCellPaddings( $left = '1', $top = '5', $right = '', $bottom = '');
            $pdfBig->MultiCell(15, 15, 'COURSE CODE', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->SetXY(32,$tableY);        
            $pdfBig->setCellPaddings( $left = '', $top = '6.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(52, 15, 'COURSE TITLE', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(84,$tableY+15);        
            $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
            $pdfBig->StartTransform();
            $pdfBig->Rotate(90);
            $pdfBig->MultiCell(15, 4, 'CREDIT', 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->StopTransform();

            $pdfBig->SetXY(88,$tableY+15);        
            $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
            $pdfBig->StartTransform();
            $pdfBig->Rotate(90);
            $pdfBig->MultiCell(15, 4, 'GRADE', 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->StopTransform();

            $pdfBig->SetXY(92,$tableY+15);        
            $pdfBig->setCellPaddings( $left = '0.5', $top = '3.5', $right = '', $bottom = '');
            $pdfBig->StartTransform();
            $pdfBig->Rotate(90);
            $pdfBig->MultiCell(15, 11, 'Month & Year of Pass', 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->StopTransform();
            // LAst 103

            

            //  Right Table heading
            $pdfBig->SetFont($palatino, '', 5.5, '', false); 
            $pdfBig->SetXY(106,$tableY+15);        
            $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
            $pdfBig->StartTransform();
            $pdfBig->Rotate(90);
            $pdfBig->MultiCell(15, 4, 'Sr.No', 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->StopTransform();


            $pdfBig->SetXY(110,$tableY);        
            $pdfBig->setCellPaddings( $left = '1', $top = '5', $right = '', $bottom = '');
            $pdfBig->MultiCell(15, 15, 'COURSE CODE', 1, "C", 0, 0, '', '', true, 0, true);


            $pdfBig->SetXY(125,$tableY);        
            $pdfBig->setCellPaddings( $left = '', $top = '6.5', $right = '', $bottom = '');
            $pdfBig->MultiCell(52, 15, 'COURSE TITLE', 1, "C", 0, 0, '', '', true, 0, true);

            $pdfBig->SetXY(177,$tableY+15);        
            $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
            $pdfBig->StartTransform();
            $pdfBig->Rotate(90);
            $pdfBig->MultiCell(15, 4, 'CREDIT', 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->StopTransform();

            $pdfBig->SetXY(181,$tableY+15);        
            $pdfBig->setCellPaddings( $left = '', $top = '1', $right = '', $bottom = '');
            $pdfBig->StartTransform();
            $pdfBig->Rotate(90);
            $pdfBig->MultiCell(15, 4, 'GRADE', 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->StopTransform();

            $pdfBig->SetXY(185,$tableY+15);        
            $pdfBig->setCellPaddings( $left = '0.5', $top = '3.5', $right = '', $bottom = '');
            $pdfBig->StartTransform();
            $pdfBig->Rotate(90);
            $pdfBig->MultiCell(15, 11, 'Month & Year of Pass', 1, "C", 0, 0, '', '', true, 0, true);
            $pdfBig->StopTransform();
            $pdfBig->ln();


            $tableHeadingY = $pdfBig->getY()-11;


            $subjectsData=$subjectsArr[$hall_ticket_no];
            

            //Separate semesters 
            $subjects = array();
            foreach ($subjectsData as $element) {
                $subjects[$element[1]][] = $element;
            }
            ksort($subjects);
            $pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');

            foreach($subjects as $year => $year_array) {

                if ($year == "I" || $year == "II" || $year == "III" ) {
                    $pdfBig->setCellPaddings( $left = '0', $top = '1', $right = '', $bottom = '');
                    $pdfBig->SetFont($palatinob, '', 7.5, '', false); 
                    $pdfBig->SetXY(13,$tableHeadingY);        
                    $pdfBig->MultiCell(90, 5, $year.' Year I Semester', 0, "C", 0, 0, '', '', true, 0, true);


                    $pdfBig->setCellPaddings( $left = '0', $top = '1', $right = '', $bottom = '');
                    $pdfBig->SetFont($palatinob, '', 7.5, '', false); 
                    $pdfBig->SetXY(106,$tableHeadingY);        
                    $pdfBig->MultiCell(90, 5, $year.' Year II Semester', 0, "C", 0, 0, '', '', true, 0, true);



                    $tableMainY = $tableHeadingY+5;
                    $tableMainY2 = $tableHeadingY+5;

                    $tableMainHeadingY = $tableHeadingY+5;
                    $tableMainHeadingY2 = $tableHeadingY+5;

                    // // Border LEFT Code
                    // $pdfBig->SetXY(13,$tableMainY);        
                    // $pdfBig->MultiCell(4, 35, '', 1, "C", 0, 0, '', '', true, 0, true);

                    // $pdfBig->SetXY(17,$tableMainY);        
                    // $pdfBig->MultiCell(15, 35, '', 1, "C", 0, 0, '', '', true, 0, true);

                    // $pdfBig->SetXY(32,$tableMainY);        
                    // $pdfBig->MultiCell(52, 35, '', 1, "C", 0, 0, '', '', true, 0, true);

                    // $pdfBig->SetXY(84,$tableMainY);        
                    // $pdfBig->MultiCell(4, 35, '', 1, "C", 0, 0, '', '', true, 0, true);
                    
                    // $pdfBig->SetXY(88,$tableMainY);        
                    // $pdfBig->MultiCell(4, 35, '', 1, "C", 0, 0, '', '', true, 0, true);

                    // $pdfBig->SetXY(92,$tableMainY);        
                    // $pdfBig->MultiCell(11, 35, '', 1, "C", 0, 0, '', '', true, 0, true);
                    // // Border LEFT Code


                    // // Border RIGHT Code
                    // $pdfBig->SetXY(106,$tableMainY2);        
                    // $pdfBig->MultiCell(4, 35, '', 1, "C", 0, 0, '', '', true, 0, true);

                    // $pdfBig->SetXY(110,$tableMainY2);        
                    // $pdfBig->MultiCell(15, 35, '', 1, "C", 0, 0, '', '', true, 0, true);

                    // $pdfBig->SetXY(125,$tableMainY2);        
                    // $pdfBig->MultiCell(52, 35, '', 1, "C", 0, 0, '', '', true, 0, true);

                    // $pdfBig->SetXY(177,$tableMainY2);        
                    // $pdfBig->MultiCell(4, 35, '', 1, "C", 0, 0, '', '', true, 0, true);
                    
                    // $pdfBig->SetXY(181,$tableMainY2);        
                    // $pdfBig->MultiCell(4, 35, '', 1, "C", 0, 0, '', '', true, 0, true);

                    // $pdfBig->SetXY(185,$tableMainY2);        
                    // $pdfBig->MultiCell(11, 35, '', 1, "C", 0, 0, '', '', true, 0, true);
                    // // Border RIGHT Code

                   
                    $tabHeight = 4;
                    
                    $i = 1;
                    $i1 = 1;
                    $subjectDataLeftHeight = 0;
                    $subjectDataRightHeight = 0;
                    foreach($year_array as $key => $val){
                        
                        if($val[2] == 'I') {
                            
                            $tabHeight = 4;
                            $pdfBig->setCellPaddings( $left = '1', $top = '0', $right = '', $bottom = '');
                            $pdfBig->startTransaction();
                            $pdfBig->SetFont($palatino, '', 5.5, '', false); 
                            // get the number of lines
                            $lines3 = $pdfBig->MultiCell(52, $tabHeight, $val[4], 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                            // restore previous object
                            $pdfBig=$pdfBig->rollbackTransaction();	
                            
                            if($lines3>1){
                                $tabHeight = 6;
                            }else{
                                $tabHeight = $tabHeight;
                            }

                            $pdfBig->setCellPaddings( $left = '0', $top = '1', $right = '', $bottom = '');
                            $pdfBig->SetFont($palatino, '', 5.5, '', false); 
                            $pdfBig->SetXY(13,$tableMainY);        
                            $pdfBig->MultiCell(4, $tabHeight, $i, '0', "C", 0, 0, '', '', true, 0, true);
                        
                            $pdfBig->SetXY(17,$tableMainY);        
                            $pdfBig->MultiCell(15, $tabHeight, $val[3], 0, "C", 0, 0, '', '', true, 0, true);


                            $pdfBig->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');
                            $pdfBig->SetXY(32,$tableMainY);        
                            $pdfBig->MultiCell(52, $tabHeight, $val[4], 0, "L", 0, 0, '', '', true, 0, true);

                            $pdfBig->setCellPaddings( $left = '0', $top = '', $right = '', $bottom = '');
                            $pdfBig->SetXY(84,$tableMainY);        
                            $pdfBig->MultiCell(4, $tabHeight, $val[6], 0, "C", 0, 0, '', '', true, 0, true);
                        

                            $pdfBig->SetXY(88,$tableMainY);        
                            $pdfBig->MultiCell(4, $tabHeight, $val[5], 0, "C", 0, 0, '', '', true, 0, true);
                        

                            $pdfBig->SetXY(92,$tableMainY);    
                            
                        
                            $string = $val[7];
                            $timestamp = strtotime($string);
                            if ($timestamp !== false) {
                                $stringOutput = date('M Y', $timestamp);
                            } else {
                                $stringOutput = $val[7];
                            }


                            $pdfBig->MultiCell(11, $tabHeight, $stringOutput, 0, "C", 0, 0, '', '', true, 0, true);

                            
                            $tableMainY = $tableMainY+$tabHeight;
                            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '', $bottom = '');

                            $subjectDataLeftHeight += $tabHeight; 

                            $i = $i+1;

                        } else if($val[2] == 'II') {
                            $tabHeight = 4;
                            $pdfBig->setCellPaddings( $left = '1', $top = '0', $right = '', $bottom = '');
                            $pdfBig->startTransaction();
                            $pdfBig->SetFont($palatino, '', 5.5, '', false); 
                            // get the number of lines
                            $lines3 = $pdfBig->MultiCell(52, $tabHeight, $val[4], 0, 'L', 0, 0, '', '', true, 0, false,true, 0);
                            // restore previous object
                            $pdfBig=$pdfBig->rollbackTransaction();	
                            
                            if($lines3>1){
                                $tabHeight = 6;
                            }else{
                                $tabHeight = $tabHeight;
                            }
                            
                            $pdfBig->setCellPaddings( $left = '0', $top = '1', $right = '', $bottom = '');
                            $pdfBig->SetFont($palatino, '', 5.5, '', false); 
                            $pdfBig->SetXY(106,$tableMainY2);        
                            $pdfBig->MultiCell(4, $tabHeight, $i1, '0', "C", 0, 0, '', '', true, 0, true);
                        
                            $pdfBig->SetXY(110,$tableMainY2);        
                            $pdfBig->MultiCell(15, $tabHeight, $val[3], 0, "C", 0, 0, '', '', true, 0, true);


                            $pdfBig->setCellPaddings( $left = '1', $top = '', $right = '', $bottom = '');
                            $pdfBig->SetXY(125,$tableMainY2);        
                            $pdfBig->MultiCell(52, $tabHeight, $val[4], 0, "L", 0, 0, '', '', true, 0, true);

                            $pdfBig->setCellPaddings( $left = '0', $top = '', $right = '', $bottom = '');
                            $pdfBig->SetXY(177,$tableMainY2);        
                            $pdfBig->MultiCell(4, $tabHeight, $val[6], 0, "C", 0, 0, '', '', true, 0, true);
                        

                            $pdfBig->SetXY(181,$tableMainY2);        
                            $pdfBig->MultiCell(4, $tabHeight, $val[5], 0, "C", 0, 0, '', '', true, 0, true);
                            
                            $string = $val[7];
                            $timestamp = strtotime($string);
                            if ($timestamp !== false) {
                                $stringOutput = date('M Y', $timestamp);
                            } else {
                                $stringOutput = $val[7];
                            }
                            $pdfBig->SetXY(185,$tableMainY2);        
                            $pdfBig->MultiCell(11, $tabHeight, $stringOutput, 0, "C", 0, 0, '', '', true, 0, true);

                            
                            $tableMainY2 = $tableMainY2+$tabHeight;
                            $subjectDataRightHeight += $tabHeight;
                            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '', $bottom = '');
                            $i1 = $i1+1;
                        }
                        
                    }

                    // die();
                    // echo $subjectDataLeftHeight;
                    // echo '<bR>';
                    // echo "Right Side";
                    // echo $subjectDataRightHeight;
                    // echo '<bR>';
                    // echo '<bR>';
                    // $tableHeightNew = 0;
                    if($subjectDataLeftHeight > $subjectDataRightHeight) {
                        $tableHeadingY = $tableHeadingY +$subjectDataLeftHeight +5;
                        $tableHeightNew = $subjectDataLeftHeight;
                    } elseif($subjectDataLeftHeight < $subjectDataRightHeight) {
                        $tableHeadingY = $tableHeadingY +$subjectDataRightHeight +5;
                        $tableHeightNew = $subjectDataRightHeight;
                    } else if($subjectDataLeftHeight == $subjectDataRightHeight) {
                        $tableHeadingY = $tableHeadingY +$subjectDataLeftHeight +5;
                        $tableHeightNew = $subjectDataLeftHeight;
                    } else {
                        $tableHeadingY = $tableHeadingY +$subjectDataLeftHeight;
                        $tableHeightNew = $subjectDataLeftHeight;
                    }

                    // Border LEFT Code
                    $pdfBig->SetXY(13,$tableMainHeadingY);        
                    $pdfBig->MultiCell(4, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->SetXY(17,$tableMainHeadingY);        
                    $pdfBig->MultiCell(15, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->SetXY(32,$tableMainHeadingY);        
                    $pdfBig->MultiCell(52, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->SetXY(84,$tableMainHeadingY);        
                    $pdfBig->MultiCell(4, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);
                    
                    $pdfBig->SetXY(88,$tableMainHeadingY);        
                    $pdfBig->MultiCell(4, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->SetXY(92,$tableMainHeadingY);        
                    $pdfBig->MultiCell(11, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);
                    // Border LEFT Code


                    // Border RIGHT Code
                    $pdfBig->SetXY(106,$tableMainHeadingY2);        
                    $pdfBig->MultiCell(4, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->SetXY(110,$tableMainHeadingY2);        
                    $pdfBig->MultiCell(15, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->SetXY(125,$tableMainHeadingY2);        
                    $pdfBig->MultiCell(52, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->SetXY(177,$tableMainHeadingY2);        
                    $pdfBig->MultiCell(4, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);
                    
                    $pdfBig->SetXY(181,$tableMainHeadingY2);        
                    $pdfBig->MultiCell(4, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);

                    $pdfBig->SetXY(185,$tableMainHeadingY2);        
                    $pdfBig->MultiCell(11, $tableHeightNew, '', 1, "C", 0, 0, '', '', true, 0, true);
                    // Border RIGHT Code


                    
                }
            }


            $bottomY = $tableHeadingY+4;

            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '', $bottom = '');
            $pdfBig->SetFont($palatino, '', 8, '', false); 
            $pdfBig->SetXY(13,$bottomY);        
            $pdfBig->MultiCell(70, 5, 'No.of Credits Registered : '.$credit_registered, '0', "L", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '', $bottom = '');
            $pdfBig->SetFont($palatino, '', 8, '', false); 
            $pdfBig->SetXY(110,$bottomY);        
            $pdfBig->MultiCell(70, 5, 'Cumulative Grade Point Average (CGPA) : '.$cgpa, '0', "L", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '', $bottom = '');
            $pdfBig->SetFont($palatino, '', 8, '', false); 
            $pdfBig->SetXY(13,$bottomY + 5);        
            $pdfBig->MultiCell(70, 5, 'No.of Credits for CGPA Computation : '.$credits_of_cgpa, '0', "L", 0, 0, '', '', true, 0, true);


            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '', $bottom = '');
            $pdfBig->SetFont($palatino, '', 8, '', false); 
            $pdfBig->SetXY(110,$bottomY + 5);        
            $pdfBig->MultiCell(80, 5, 'Minimum No. of Credits for the Award of Degree : '.$min_no_credit_award_degree, '0', "L", 0, 0, '', '', true, 0, true);


            

            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '', $bottom = '');
            $pdfBig->SetFont($palatinob, '', 9, '', false); 
            $pdfBig->SetXY(17,258);        
            $pdfBig->MultiCell(50, 5, 'Controller of Examinations', '0', "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 9, '', false); 
            $pdfBig->SetXY(98,258);        
            $pdfBig->MultiCell(50, 5, 'Registrar', '0', "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatinob, '', 9, '', false); 
            $pdfBig->SetXY(168,258);        
            $pdfBig->MultiCell(50, 5, 'Vice Chancellor', '0', "L", 0, 0, '', '', true, 0, true);

            $pdfBig->SetFont($palatino, '', 7.5, '', false); 
            $pdfBig->SetXY(68,281);        
            $pdfBig->MultiCell(50, 5, 'Date : <b>'.$date.'</b>' , '0', "L", 0, 0, '', '', true, 0, true);
            

            $pdfBig->setCellPaddings( $left = '0', $top = '0', $right = '0', $bottom = '0');

            // End pdfBig
            // $serial_no=$GUID=$unique_id;
            // //qr code    
            // $dt = date("_ymdHis");
            // $str=$GUID.$dt;
            // $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
            // $encryptedString = strtoupper(md5($str));
            // $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
            // $qrCodex = 21; //
            // $qrCodey = 72; //
            // $qrCodeWidth =20;
            // $qrCodeHeight = 20;
            // $ecc = 'L';
            // $pixel_Size = 1;
            // $frame_Size = 1;  
            // // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
            // \QrCode::size(75.6)
            //     ->backgroundColor(255, 255, 0)
            //     ->format('png')
            //     ->generate($codeContents, $qr_code_path);

            // $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
            // $pdfBig->setPageMark();


            // //Microline
            // $str = $candidate_name;
            // $str = strtoupper(preg_replace('/\s+/', '', $str)); 
            // $textArray = imagettfbbox(1.3, 0, public_path().'/backend/fonts/Arial.ttf', $str);
            // $strWidth = ($textArray[2] - $textArray[0]);
            // $strHeight = $textArray[6] - $textArray[1] / 5;
            // $width=70;
            // $latestWidth =$width;
            // // $latestWidth = round($width*4.13);
            // //Updated by Mandar
            // $microlinestr=$str;
            // $microlinestrLength=strlen($microlinestr);
            // //width per character
            // $microLinecharacterWd =$strWidth/$microlinestrLength;
            // //Required no of characters required in string to match width
            // $microlinestrCharReq=$latestWidth/$microLinecharacterWd;
            // $microlinestrCharReq=round($microlinestrCharReq);
            // //No of time string should repeated
            // $repeateMicrolineStrCount=$latestWidth/$strWidth;
            // $repeateMicrolineStrCount=round($repeateMicrolineStrCount)+1;
            // //Repeatation of string 
            // $microlinestrRep = str_repeat($microlinestr, $repeateMicrolineStrCount);                            
            // //Cut string in required characters (final string)
            // $arrayEnrollment1 = substr($microlinestrRep,0,$microlinestrCharReq);

            // // $str = $nameOrg;
            // // $str = strtoupper(preg_replace('/\s+/', '', $str)); 			
            // $microlinestr=$arrayEnrollment1;
            // $pdfBig->SetFont($arial, '', 1.3, '', false);
            // $pdfBig->SetTextColor(0, 0, 0);
            // $pdfBig->SetXY(21, 93);        
            // $pdfBig->Cell(20, 0, $microlinestr, 0, false, 'C');
 

        

        }
        // die();
        
        $pdfBig->Output('sample.pdf', 'I');   
        
            
        // $unique_id = trim($studentData[0]);
        // $student_id = trim($studentData[1]);
        // $hall_ticket = trim($studentData[1]);
        // $memo_no = trim($studentData[2]);
        // $appar_id = trim($studentData[3]);
        // $serial_no = trim($studentData[4]);
        // $examination = trim($studentData[5]);
        // $month_year = trim($studentData[6]);
        // $branch = trim($studentData[7]); 
        // $candidate_name = trim($studentData[8]);
        // $father_name =  trim($studentData[9]);
        // $mother_name =  trim($studentData[10]);

        // $total_internal_marks = trim($studentData[107]);
        // $total_external_marks = trim($studentData[108]);
        // $total_marks_100 = trim($studentData[109]);
        // $total_credits = trim($studentData[110]);

        // $total_subject = trim($studentData[111]);
        // $total_appered = trim($studentData[112]);
        // $total_passed = trim($studentData[113]);
        
        // $agreegate_in_word =  trim($studentData[114]);
        // $sgpa =  trim($studentData[115]);
        // $cgpa =  trim($studentData[116]);
        // $date_of_issue =  trim($studentData[117]);

        

        // $studentData[2]='ANKITA PRIYADARSANI MAHANANDA JENA';
        // $studentData[3] = 'PRIYADARSANI MAHANANDA JENA';
        // $studentData[4] = 'Basic Computer Skills';
        // $studentData[5] = 'ITERS';
        // $studentData[6] = 'A';
        // $studentData[7] = 'Bandra LDC';
        // $studentData[8] = '02/10/2024';
        // $studentData[9] = '10/12/2024';
        // $x=18;
        // $y=80;
        // $pdfBig->SetTextColor(0,0,0);
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 23, '', false);
        // $pdfBig->SetXY($x, $y);
        // $pdfBig->Cell(0, 10, 'Awarded to', 0, false, 'C');
        
        // // Measure the text width at the initial font size
        // $font_size1 = 25;
        // $width1 = 180;
        // $pdfBig->SetFont($Times_New_Roman, 'B', 25, '', false);
        // $textWidth1 = $pdfBig->GetStringWidth($studentData[2]);

        // // Automatically adjust font size to fit the cell width
        // if ($textWidth1 > $width1) {
        //     $scalingFactor1 = $width1 / $textWidth1; // Calculate scaling factor
        //     $adjustedFontSize1 = floor($font_size1 * $scalingFactor1); // Scale down font size
        // } else {
        //     $adjustedFontSize1 = $font_size1; // No adjustment needed
        // }


        // $pdfBig->SetTextColor(255, 0, 0);
        // $pdfBig->SetFont($Times_New_Roman, 'B', $adjustedFontSize1, '', false);
        // $pdfBig->SetXY(15, $y+10);
        // $pdfBig->MultiCell(180, 10, $studentData[2], 0, "C", 0, 0, '', '', true, 0, true);
        // // $pdfBig->Cell(0, 10, $studentData[2], 0, false, 'C');   
        // $pdfBig->SetTextColor(0,0,0);
        
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // // Measure the text width at the initial font size
        // $font_size = 20;
        // $width = 180;
        // $textWidth = $pdfBig->GetStringWidth('S/o,D/o,W/o, Guardian '.$studentData[3]);

        // // Automatically adjust font size to fit the cell width
        // if ($textWidth > $width) {
        //     $scalingFactor = $width / $textWidth; // Calculate scaling factor
        //     $adjustedFontSize = floor($font_size * $scalingFactor); // Scale down font size
        // } else {
        //     $adjustedFontSize = $font_size; // No adjustment needed
        // }

        // $pdfBig->SetFont($Times_New_Roman, 'BI', $adjustedFontSize, '', false);
        // $pdfBig->SetXY(15,$y + 23);        
        // $pdfBig->MultiCell(180, 11, '<span style="font-family:'.$MTCORSVA.'"> S/o,D/o,W/o, Guardian </span>'.$studentData[3], 0, "C", 0, 0, '', '', true, 0, true);


        // Set the adjusted font size
        //$pdfBig->SetFont($fonts_array[$extra_fields],$bold, $adjustedFontSize);

        
        
        // $text = 'S/o,D/o,W/o, Guardian ' . $studentData[3] ;
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY(15, $y + 23); 
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'S/o,D/o,W/o, Guardian '); 
        // $pdfBig->SetFont($Times_New_Roman, 'BI', $adjustedFontSize, '', false);
        // $pdfBig->Write(0, $studentData[3]);
        

        // $totalWidth1 = $pdfBig->GetStringWidth('S/o,D/o,W/o, Guardian'); 
        // $totalWidth2 = $pdfBig->GetStringWidth($studentData[3]); 

        // echo $totalWidth1;
        // echo "<br>";
        // echo $totalWidth2;
        // echo "<br>";
        // echo $totalWidth1+$totalWidth2;

        // if()

        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->SetXY(15,$y + 23);        
        // $pdfBig->MultiCell(72, 11, 'S/o,D/o,W/o, Guardian', 1, "L", 0, 0, '', '', true, 0, true);

        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->SetXY($pdfBig->getX(),$y + 23);        
        // $pdfBig->MultiCell(108, 11, $studentData[3], 1, "L", 0, 0, '', '', true, 0, true);



        // $pdfBig->SetXY($x, $y+33);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->SetXY($x, $y+33);
        // $pdfBig->Cell(0, 10, 'for the course of', 0, false, 'C');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->SetXY($x, $y+43);
        // $pdfBig->Cell(0, 10, $studentData[4], 0, false, 'C');
        
        
        // $text = 'in ' . $studentData[5] . ' Secter'; 
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY($centerX, $y + 53); 
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'in ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[5] . ' ');
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'Secter');


        // $text = 'with Grade ' . $studentData[6] ;
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false); 
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY($centerX, $y + 63);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'with Grade  ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[6] . ' ');

        // $pdfBig->SetXY($x, $y+74);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Cell(0, 10, 'Conducted at', 0, false, 'C');

        // $text = 'Center - Location ' . $studentData[7] ;
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false); 
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY($centerX, $y + 84);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'Center - Location ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[7] . ' ');

        // $text = 'from ' . $studentData[8] .' to '.$studentData[9];
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false); 
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY($centerX, $y + 94);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'from ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[8] . ' ');
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, ' to ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[9] . ' ');

        // $pdfBig->SetFont($MTCORSVA, '', 15, '', false);
        // $pdfBig->SetXY(20, $y+165);
        // $pdfBig->Cell(0, 0, 'Cerificate No: '.$studentData[0], 0, false, 'L');
        // $pdfBig->SetXY(140, $y+165);
        // $pdfBig->Cell(0, 0, 'Date of issue: '.$studentData[1], 0, false, 'L');

        // $pdfBig->SetFont($Times_New_Normal, '', 8, '', false);
        // $pdfBig->SetXY(10, $y+164);
        // $pdfBig->Cell(0, 0, '75 - 100 = "A+" grade, 60 - 74 = "A" grade,', 0, false, 'C');
        // $pdfBig->SetXY(10, $y+169);
        // $pdfBig->Cell(0, 0, '50 - 59 = "B" grade, 35 - 49 = "C" Grade,', 0, false, 'C');



        // $pdfBig->SetFont('helvetica', '', 16);
        // $pdfBig->SetXY(50, 50);
        
        // // Start transformation
        // $pdfBig->StartTransform();
        // $pdfBig->ScaleY(150, 50, 50); // 150% vertical scaling at X=50, Y=50
        
        // $pdfBig->Cell(50, 10, "Stretched Text", 0, 1, 'C');
        
        // // End transformation
        // $pdfBig->StopTransform();
        

        
        
        // $str = 'Dummy Text';
        // $angle = 45;
        // $line_gap = 20;

        // $font_color_extra = '000000';
        // $security_line = '';
        // for($d = 0; $d < 15; $d++)
        //     $security_line .= $str . ' ';

        
        
        // $pdfBig->SetOverprint(true, true, 0);

        // $uv_percentage= 15;

        
        // $rgb_opacity=$uv_percentage/100;
        // $pdfBig->SetAlpha($rgb_opacity);
        // $pdfBig->SetTextColor(0, 0, 0);                                        
            
        
        
        // $pdfBig->SetFont($arialn, 'B', 10);

        // if (210 < 297){
        //     $pdfWidth = 210;
        //     $pdfHeight = 297;
        // }else{
        //     $pdfWidth = 210;
        //     $pdfHeight = 297;
        // }
        // for ($i=0; $i < $pdfHeight; $i+=$line_gap) {                                    
        //     $pdfBig->SetXY(0,$i);
        //     $pdfBig->StartTransform();  
        //     $pdfBig->Rotate(45);
        //     $pdfBig->Cell(0, 0, $security_line, 0, false, 'L');
        //     $pdfBig->StopTransform();
        // }
        // for ($j=0; $j < $pdfWidth; $j+=$line_gap) {                                    
        //     $pdfBig->SetXY($j+5,$pdfHeight);
        //     $pdfBig->StartTransform();  
        //     $pdfBig->Rotate(45);
        //     $pdfBig->Cell(0, 0, $security_line, 0, false, 'L');
        //     $pdfBig->StopTransform();
        // }
        
        // $pdfBig->SetOverprint(false, false, 0);
        // $pdfBig->SetAlpha(1);

        // Running Below Done
        // $security_line = '';
        // for($d = 0; $d < 15; $d++)
        //     $security_line .= $str . ' ';

        
        // $pdfWidth = 210;
        // $pdfHeight = 297;
        // $j_increased=5;
        // $line_gap =10;
        // $text_align = 'L';
        
        // $pdfBig->SetOverprint(true, true, 0);
        // $uv_percentage= 15;
        // $pdfBig->SetTextColor(0, 0, 0, $uv_percentage, false, '');

        // $rgb_opacity=$uv_percentage/100;
        // // $pdfBig->SetAlpha($rgb_opacity);
        // // $pdfBig->SetTextColor(0, 0, 0); 
        // $pdfBig->SetFont($times, 'B', 12);

       
        // for ($i=0; $i <= $pdfHeight; $i+=$line_gap) {
        //     $pdfBig->SetXY(0,$i);
        //     $pdfBig->StartTransform();  
        //     $pdfBig->Rotate($angle);
        //     $pdfBig->Cell(0, 0, $security_line, 0, false, $text_align);
        //     $pdfBig->StopTransform();
        // }

        
        // for ($j=0; $j < $pdfWidth; $j+=$line_gap) {
        //     //$pdfBig->SetXY($j+5,$pdfHeight);
        //     $pdfBig->SetXY($j+$j_increased,$pdfHeight);
        //     $pdfBig->StartTransform();  
        //     $pdfBig->Rotate($angle);
        //     $pdfBig->Cell(0, 0, $security_line, 0, false, $text_align);
        //     $pdfBig->StopTransform();
        // }
        // $pdfBig->SetOverprint(false, false, 0);

        // Running Upeprr Done

        // $pdfBig->SetAlpha(1);


        // Set transparency
        // $pdfBig->SetAlpha(0.1);
        // $pdfBig->SetAlpha(1);

        // Define watermark text

        // $watermarkText = 'Raj Kamal Kumawat Raj Kamal Kumawat Raj Kamal Kumawat Raj Kamal Kumawat';
        // $chrPerLine = 100; // Equivalent to your logic
        // $repeat_txt = str_repeat($watermarkText, $chrPerLine);


        // // Get page dimensions
        // $pageWidth = $pdfBig->getPageWidth();
        // $pageHeight = $pdfBig->getPageHeight();


        // // Calculate text width
        // $textWidth = $pdfBig->GetStringWidth($repeat_txt);

        // // Define spacing
        // $xSpacing = $textWidth * 1.5; // Adjust horizontal spacing
        // $ySpacing = 10; // Adjust vertical spacing

        // // Loop to repeat watermark
        // for ($y = 0; $y < $pageHeight; $y += $ySpacing) {
        //     for ($x = -$textWidth; $x < $pageWidth + $textWidth; $x += $xSpacing) {
        //         echo $y;
        //         echo "<bR>";
        //         // echo $x;
        //         // echo "<bR>";
        //         // echo $y;
        //         // echo "<bR>";
        //         // echo "<bR>";

        //         $pdfBig->StartTransform();  
        //         $pdfBig->Rotate(45); // Rotate around center
        //         $pdfBig->SetFont($times, 'B', 12);
        //         $pdfBig->Text($x, $y, $repeat_txt);
        //         $pdfBig->StopTransform();
        //     }
        // }

        // $x = 0;
        // $y = 0;
        // for ($y = 0; $y < 290; $y += 10) {
            
        //     $pdfBig->SetFont($BookmanOldStyle_N, '', 10, '', false); 
        //     $pdfBig->SetXY($x,$y);        
        //     $pdfBig->MultiCell(210, 10, $repeat_txt, 0, "L", 0, 0, '', '', true, 0, true);

        //     // $y = $y+10;
        // }


        // Reset transparency
        // $pdfBig->SetAlpha(1);




        // $pdfBig->Output('sample.pdf', 'I');   
    }

    public function convertExcelDate($dateFromExcel)
    {
        if (is_numeric($dateFromExcel)) {
            // Handle Excel date format
            try {
                $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateFromExcel);
                return Carbon::instance($excelDate)->format('d-m-Y'); // Returns DD-MM-YYYY
            } catch (\Throwable $th) {
                \Log::error("Excel Date Conversion Error: " . $th->getMessage() . " for value: " . $dateFromExcel);
                return null;
            }
        } else {
            // Handle normal string date format
            try {
                return Carbon::parse($dateFromExcel)->format('d-m-Y'); // Returns DD-MM-YYYY
            } catch (\Throwable $th) {
                \Log::error("String Date Parsing Error: " . $th->getMessage() . " for value: " . $dateFromExcel);
                return null;
            }
        }
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
            // dd($rect);
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


   

}
