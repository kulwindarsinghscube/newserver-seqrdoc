<?php
 
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Session,TCPDF,TCPDF_FONTS,Auth,DB;

use App\models\IdCardStatus;
use App\models\TemplateMaster;
use App\models\BackgroundTemplateMaster;
use App\models\FieldMaster;
use App\models\FontMaster;
use App\models\SystemConfig;
use App\models\Config;
use App\models\StudentTable;
use App\models\PrintingDetail;
use App\models\SbStudentTable;
use App\models\SbPrintingDetail;
use Mail;
use QrCode;
use File;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use Illuminate\Support\Facades\Cache;

class GenerateIDCardCertificateJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rowData;
    protected $additionalParams;

    public function __construct($rowData, $additionalParams)
    {
        $this->rowData = $rowData;
        $this->additionalParams = $additionalParams;
    }

    public function handle()
    {
        // echo "<pre>";
        // print_r($this->additionalParams['excel_row']);;
        // die();

        $rowData = $this->rowData;
        $excel_row = $this->additionalParams['excel_row'];
        $sheet = $this->additionalParams['sheet'];
        $printer_name = $this->additionalParams['printer_name'];
        $enrollImage = $this->additionalParams['enrollImage'];
        $FID = $this->additionalParams['FID'];
        $req_number = $this->additionalParams['req_number'];

        $pdf = new TCPDF('P', 'mm', array($bg_template_width_generate, $bg_template_height_generate), true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetCreator('SetCreator');

        $pdf->AddPage();

        if(isset($FID['bg_template_id']) && $FID['bg_template_id'] != ''){
            $pdf->Image($bg_template_img_generate, 0, 0, $bg_template_width_generate, $bg_template_height_generate, "JPG", '', 'R', true);
        }
        $serial_no = $rowData[0][2];
        if($get_file_aws_local_flag->file_aws_local == '1'){
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $file_pointer_jpg = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.jpg';

                $file_pointer_png =\Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.png';
            }
            else{
                $file_pointer_jpg = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.jpg';

                $file_pointer_png = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.png';
            }
        }
        else{
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $file_pointer_jpg = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.jpg';

                $file_pointer_png =\Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.png';
            }
            else{
                $file_pointer_jpg = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.jpg';

                $file_pointer_png = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.png';
            }
        }
        

        $count = count($FID['mapped_name']);

        for ($extra_fields=0; $extra_fields < $count; $extra_fields++) { 
            

            if(isset($FID['security_type'][0]) || isset($FID['security_type'][1])){

                array_push($FID['security_type'], $FID['security_type'][0]);
                array_push($FID['security_type'], $FID['security_type'][1]);
                unset($FID['security_type'][0]);
                unset($FID['security_type'][1]);
                
            }
            $security_type = $FID['security_type'][$extra_fields+2];
            
            if(isset($FID['x_pos'][0]) || isset($FID['x_pos'][1])){

                array_push($FID['x_pos'], $FID['x_pos'][0]);
                array_push($FID['x_pos'], $FID['x_pos'][1]);
                unset($FID['x_pos'][0]);
                unset($FID['x_pos'][1]);
                
            }
            $x = $FID['x_pos'][$extra_fields + 2];

            if(isset($FID['y_pos'][0]) || isset($FID['y_pos'][1])){

                array_push($FID['y_pos'], $FID['y_pos'][0]);
                array_push($FID['y_pos'], $FID['y_pos'][1]);
                unset($FID['y_pos'][0]);
                unset($FID['y_pos'][1]);
                
            }
            $y = $FID['y_pos'][$extra_fields + 2];


            $print_serial_no = $this->nextPrintSerial();

            if(isset($FID['field_position'][0]) || isset($FID['field_position'][1])){

                array_push($FID['field_position'], $FID['field_position'][0]);
                array_push($FID['field_position'], $FID['field_position'][1]);
                unset($FID['field_position'][0]);
                unset($FID['field_position'][1]);
                
            }
            //print_r($FID['field_position']);
            $field_position = $FID['field_position'][$extra_fields + 2];

            
            if(isset($FID['font_color'][0]) || isset($FID['font_color'][1])){

                array_push($FID['font_color'], $FID['font_color'][0]);
                array_push($FID['font_color'], $FID['font_color'][1]);
                unset($FID['font_color'][0]);
                unset($FID['font_color'][1]);
                
            }                
            $font_color_hex = $FID['font_color'][$extra_fields + 2];

            
            if($font_color_hex != ''){

                
                if($font_color_hex == "0"){

                    $r = 0;
                    $g = 0;
                    $b = 0;

                }else{

                    list($r,$g,$b)  = array($font_color_hex[0].$font_color_hex[1],
                                    $font_color_hex[2].$font_color_hex[3],
                                    $font_color_hex[4].$font_color_hex[5],
                            );
                    $r = hexdec($r);
                    $g = hexdec($g);
                    $b = hexdec($b);    
                }
            };
            

            if(isset($fonts_array[$extra_fields + 2])){

                $font = $fonts_array[$extra_fields + 2];
            }

            if(isset($FID['font_size'][0]) || isset($FID['font_size'][1])){

                array_push($FID['font_size'], $FID['font_size'][0]);
                array_push($FID['font_size'], $FID['font_size'][1]);
                unset($FID['font_size'][0]);
                unset($FID['font_size'][1]);
                
            }                
            $font_size = $FID['font_size'][$extra_fields + 2];


            if(isset($FID['font_style'][0]) || isset($FID['font_style'][1])){

                array_push($FID['font_style'], $FID['font_style'][0]);
                array_push($FID['font_style'], $FID['font_style'][1]);
                unset($FID['font_style'][0]);
                unset($FID['font_style'][1]);
                
            }                
            $font_style = $FID['font_style'][$extra_fields + 2];



            if(isset($FID['width'][0]) || isset($FID['width'][1])){

                array_push($FID['width'], $FID['width'][0]);
                array_push($FID['width'], $FID['width'][1]);
                unset($FID['width'][0]);
                unset($FID['width'][1]);
                
            }                
            $width = $FID['width'][$extra_fields + 2];


            if(isset($FID['height'][0]) || isset($FID['height'][1])){

                array_push($FID['height'], $FID['height'][0]);
                array_push($FID['height'], $FID['height'][1]);
                unset($FID['height'][0]);
                unset($FID['height'][1]);
                
            }                
            $height = $FID['height'][$extra_fields + 2];



            $str = '';
            if(isset($rowData[0][$extra_fields]))
                $str = $rowData[0][$extra_fields];
            

            if(isset($FID['text_justification'][0]) || isset($FID['text_justification'][1])){

                array_push($FID['text_justification'], $FID['text_justification'][0]);
                array_push($FID['text_justification'], $FID['text_justification'][1]);
                unset($FID['text_justification'][0]);
                unset($FID['text_justification'][1]);
                
            }                
            $text_align = $FID['text_justification'][$extra_fields + 2];



            if($field_position == 3){
                $str = $rowData[0][1];
            }else if($field_position == 4){
                
                $str = $rowData[0][2];
            }else if($field_position == 5){
                
                $str = $rowData[0][3];
            }else if($field_position == 6){
                
                $str = $rowData[0][4];
            }else if($field_position == 7){
                $str = $rowData[0][5];
            }else if($field_position == 8){
                
                $str = $rowData[0][6];
            }else if($field_position == 9){
                
                $str = $rowData[0][7];
            }else if($field_position == 10){
                $str = $rowData[0][8];

            }else if($field_position == 12){
                $str = $rowData[0][11];

            }

            //print_r($rowData[0]);
            /*echo '<br>';
            echo $str.'_____'.$field_position;
            echo '<br>';*/
            switch ($security_type) {
                case 'QR Code':
                    /* echo "----";
                    print_r($rowData[0]);
                    echo "----";
                    echo $field_position;
                    echo "----";
                    */
                    $dt = date("_ymdHis");
                    /*$excl_column_row = $sheet->getCellByColumnAndRow(0,$excel_row);
                    echo $str = $excl_column_row->getValue();*/
                    $str=$rowData[0][0]; //updated by mandar
                    /* echo "----";
                    print_r($excel_row);

                    echo "----";
                    echo $str;
                    echo "----";
                    echo '<br>';*/
                        $codeContents = strtoupper(md5($str.$dt));

                    if(!empty($str)){

                        if($template_id == 7){
                            //echo "a";
                            //$sheet->setCellValue('L'.$excel_row,$codeContents);
                            $sheet->setCellValue('M'.$excel_row,$codeContents);
                        }else{
                            //echo "b";
                            // $sheet->setCellValue('J'.$excel_row,$codeContents);
                                $sheet->setCellValue('K'.$excel_row,$codeContents);
                        }
                    }
                    
                    $pngAbsoluteFilePath = "$tmpDir/$codeContents.png";

                    QrCode::format('png')->size(200)->generate($codeContents,$pngAbsoluteFilePath);
                    $QR = imagecreatefrompng($pngAbsoluteFilePath);

                    $QR_width = imagesx($QR);
                    $QR_height = imagesy($QR);

                    $logo_qr_width = $QR_width/3;

                    imagepng($QR,$pngAbsoluteFilePath);
                    
                    $pdf->SetAlpha(1);
                    $pdf->Image($pngAbsoluteFilePath,$x,$y,19,19,"PNG",'','R',true);
                    break;

                case 'ID Barcode':
                    break;
                case 'Normal':

                    // echo $excel_row;
                    // die();
                    if($FID['template_id'] == 6){
                        
                        if($field_position == 8){
                            $cell = $sheet->getCellByColumnAndRow(9,$excel_row);
                            
                            $str = $cell->getValue();
                            if (is_numeric($str) && \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($str)){

                                $val = $cell->getValue();
                                $xls_date = $val;
        
                                $unix_date = ($xls_date - 25569) * 86400;
                                    
                                $xls_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($xls_date - 25569) * 86400;
                                $str =  date("d-m-Y", $unix_date);
                            }
                        }

                        if($field_position == 10){
                            $cell = $sheet->getCellByColumnAndRow(10,$excel_row);
                            
                            $str = $cell->getValue();
                        }
                    }else if($FID['template_id'] == 1||$FID['template_id'] == 2||$FID['template_id'] == 3||$FID['template_id'] == 4||$FID['template_id'] == 5){

                        if($field_position == 10){  

                            $cell = $sheet->getCellByColumnAndRow(9,$excel_row);

                            $str = $cell->getValue();
                            if (is_numeric($str) && \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($str)){

                                $val = $cell->getValue();
                                $xls_date = $val;
        
                                $unix_date = ($xls_date - 25569) * 86400;
                                    
                                $xls_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($xls_date - 25569) * 86400;
                                $str =  date("d-m-Y", $unix_date);
                            }
                        }

                            if($field_position == 12){
                            $cell = $sheet->getCellByColumnAndRow(10,$excel_row);
                            
                            $str = $cell->getValue();
                        }
                    }else{

                        if($field_position == 10){  

                            $cell = $sheet->getCellByColumnAndRow(9,$excel_row);

                            $str = $cell->getValue();
                            if (is_numeric($str) && \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($str)){

                                $val = $cell->getValue();
                                $xls_date = $val;
        
                                $unix_date = ($xls_date - 25569) * 86400;
                                    
                                $xls_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($xls_date - 25569) * 86400;
                                $str =  date("d-m-Y", $unix_date);
                            }
                        }


                    }
                    
                    
                    $pdf->SetAlpha(1);
                    $pdf->SetTextColor($r,$g,$b);
                    $pdf->SetFont($font,$font_style,$font_size,'',false);
                    $pdf->SetXY($x,$y);
                    $pdf->Cell($width,$height,$str,0,false,$text_align);
                    break;
                
                case 'Dynamic Image':

                    
                    $pdf->SetAlpha(1);
                    $excel_column_row = $sheet->getCellByColumnAndRow(2,$excel_row);
                    $enrollValue = $excel_column_row->getValue();

                    $serial_no = trim($serial_no);
                    
                    if($get_file_aws_local_flag->file_aws_local == '1'){
                        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                            $image_jpg = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.jpg';

                            $image_png =\Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.png';
                        }
                        else{
                            $image_jpg = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.jpg';

                            $image_png = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.png';
                        }
                    }
                    else{
                        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                            $image_jpg = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.jpg';

                            $image_png =\Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.png';
                        }
                        else{
                            $image_jpg = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.jpg';

                            $image_png = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.png';
                        }
                    }


                    

                    $exists = $this->check_file_exist($image_jpg);
                    
                    if($exists){
                        $pdf->image($image_jpg,$x,$y,$width / 3,$height / 3,"","",'L',true,3600);
                    } else {


                        $exists = $this->check_file_exist($image_png);
                        if($exists){
                            $pdf->image($image_png,$x,$y,$width / 3,$height / 3,"","",'L',true,3600);
                        } 
                    }

                    break;
                default:
                    # code...
                    break;
            }
        }

        // exit; 

        $serial_no = $rowData[0][0];//Overwrite enrolment serial no to unique no
        $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $excelfile);
        $admin_id = \Auth::guard('admin')->user()->toArray();
        $template_name  = $FID['template_name'];
        $certName = str_replace("/", "_", $serial_no) .".pdf";
        $myPath = public_path().'/backend/temp_pdf_file';
        $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');
        $student_table_id = $this->addCertificate($serial_no, $certName, $dt,$FID['template_id'],$admin_id);
        

        
        $username = $admin_id['username'];
        date_default_timezone_set('Asia/Kolkata');

        $content = "#".$log_serial_no." serial No :".$serial_no.PHP_EOL;
        $date = date('Y-m-d H:i:s').PHP_EOL;
        

        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
        }
        else{
            $file_path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
        }
        $fp = fopen($file_path.'/'.$withoutExt.".txt","a");
        fwrite($fp,$content);
        fwrite($fp,$date);

        
        $print_datetime = date("Y-m-d H:i:s");
        $print_count = $this->getPrintCount($serial_no);
        $printer_name = /*'HP 1020';*/$printer_name;
        
        $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no,$template_name,$admin_id,$student_table_id);
        $log_serial_no++;

        $excel_column_row = $sheet->getCellByColumnAndRow(3, $excel_row);
        $enroll_value = $excel_column_row->getValue();

        if($get_file_aws_local_flag->file_aws_local == '1'){
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $imageNameJpg = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$enroll_value.'.jpg';

                $imageNamePng =\Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$enroll_value.'.png';
            }
            else{
                $imageNameJpg = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$enroll_value.'.jpg';

                $imageNamePng = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$enroll_value.'.png';
            }
        }
        else{
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $imageNameJpg = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$enroll_value.'.jpg';

                $imageNamePng =\Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$enroll_value.'.png';
            }
            else{
                $imageNameJpg = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$enroll_value.'.jpg';

                $imageNamePng = \Config::get('constant.local_base_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$enroll_value.'.png';
            }
        }

        //echo $imageNamePng;
        $imageName = $enroll_value.'.jpg';
        
        /*if(file_exists($imageNamePng)){

            $imageName = $enroll_value.'.png';
        }*/


        if($this->check_file_exist($imageNamePng)){

            $imageName = $enroll_value.'.png';  
        }
        // array_push($enrollImage, $imageName);


        return $imageName;



        
    }



    /**
     * Helper function to get FID field value safely
     */
    public function getFIDValue(&$FID, $key, $index) {
        return $FID[$key][$index] ?? null;
    }

    /**
     * Helper public function to build image/file path
     */
    public function buildFilePath($basePath, $subdomain, $typePath, $template_id, $filename) {
        return $basePath . $subdomain . '/' . $typePath . '/' . $template_id . '/' . $filename;
    }

    public function checkImage(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        

        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $request_number = $request['req_number'];
        $IdCardStatus  = IdCardStatus::where('request_number',$request_number)
                        ->first();

        $excel = $IdCardStatus['excel_sheet'];
        $template_name = $IdCardStatus['template_name'];
        $template_id = TemplateMaster::where('template_name',
            $template_name)->value('id');
        $filename = $newflname =public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excel;
        $field_data = FieldMaster::where('template_id',$template_id)->get()->toArray();
        $FID = [];
        foreach ($field_data as $frow) {
            $FID['mapped_name'][] = $frow['mapped_name'];
        }
        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
        }
        else{
            $path = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
        }
        // dd($template_name);
        if($get_file_aws_local_flag->file_aws_local == '1'){
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $aws_path = '/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
            }
            else{
                $aws_path = '/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
            }
        }


        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        
        $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
        if(file_exists(public_path().'/'.$subdomain[0].'/test.txt')){
            unlink(public_path().'/'.$subdomain[0].'/test.txt');
        }
        $file_not_exists = [];
        for($excel_row = 2; $excel_row <= $highestRow; $excel_row++)
        {
            $rowData1 = $sheet->rangeToArray('A'. $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);
            
            $count =  count($FID['mapped_name']);
            $serial_no = $rowData1[0][2];
            
            if($get_file_aws_local_flag->file_aws_local == '1'){
                if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                    $file_location_jpg = '/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.jpg';

                    $file_location_png ='/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.png';
                }
                else{
                    $file_location_jpg = '/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.jpg';

                    $file_location_png = '/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.png';
                }
            }
            else{
                if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                    $file_location_jpg = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.jpg';

                    $file_location_png = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$serial_no.'.png';
                }
                else{
                    /*$file_location_jpg = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.jpg';

                    $file_location_png = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.png';*/

                    $file_location_jpg= 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/backend/templates/'.$template_id.'/'.$serial_no.'.jpg';
                    $file_location_png = 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/backend/templates/'.$template_id.'/'.$serial_no.'.png';
                }
            }

            

            $target_path = $path.'/'.$newflname;

            // dd($file_location_png);
            if($get_file_aws_local_flag->file_aws_local == '1'){
                if (!Storage::disk('s3')->exists($file_location_png) || !Storage::disk('s3')->exists($file_location_jpg)) {
                    if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                        if (Storage::disk('s3')->exists('/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$newflname)) {
                            Storage::disk('s3')->delete($subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$newflname);
                        }
                        return response()->json(['success'=>false,'message'=>'Please add images in folder of your template name','type'=>'toster']);
                    }
                    else{
                        if (Storage::disk('s3')->exists('/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$newflname)) {
                            Storage::disk('s3')->delete($subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$newflname);
                        }
                        return response()->json(['success'=>false,'message'=>'Please add images in folder of your template name','type'=>'toster']);
                    }
                
                }
            }
            else{


                
                if(!$this->check_file_exist($file_location_jpg)){

                    if(!$this->check_file_exist($file_location_png)){

                        $file = public_path().'/'.$subdomain[0].'/test.txt';
                        
                        file::append($file,$serial_no.PHP_EOL);
                        if($serial_no != ''){
                          
                                array_push($file_not_exists, $serial_no);
                            }

                    }
                }

               /* echo $file_location_jpg;
                exit;*/
                /*echo $serial_no;
                exit;*/
               /* if(!file_exists($file_location_jpg)){
                    if(!file_exists($file_location_png)){
                
                        array_push($file_not_exists, $serial_no);
                    }
                }*/
                
            }
        }

        if(count($file_not_exists) > 0){
            $path = 'https://'.$subdomain[0].'.seqrdoc.com/';
            $msg = "<b>Click <a href='".$path.$subdomain[0].'/test.txt'."'class='downloadpdf' download target='_blank'>Here</a> to download file<b>";

            return response()->json(['success'=>false,'message'=>'Please add images in folder of your template name','type'=>'toster','msg'=>$msg]);
        }else{
            return response()->json(['success'=>true,'message'=>'All files exists','type'=>'toster']);
        }

    }
     
    public function addCertificate($serial_no, $certName, $dt,$template_id,$admin_id)
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $file1 = public_path().'/backend/temp_pdf_file/'.$certName;
        
        $file2 =public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;

        if($subdomain[0]=='tpsdi'){
            $source=\Config::get('constant.directoryPathBackward')."\\backend\\temp_pdf_file\\".$certName;//$file1
            $output=\Config::get('constant.directoryPathBackward').$subdomain[0]."\\backend\\pdf_file\\".$certName;
            CoreHelper::compressPdfFile($source,$output);
        }else{
            copy($file1, $file2);
        }

        @unlink($file1);

        $sts = 1;
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
        $auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        // Mark all previous records of same serial no to inactive if any
        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            $resultu = SbStudentTable::where('serial_no',$serial_no)->update(['status'=>'0']);
            // Insert the new record

            $result = SbStudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id]);
        }
        else{
            $resultu = StudentTable::where('serial_no',$serial_no)->update(['status'=>'0']);
            // Insert the new record

            $result = StudentTable::create(['serial_no'=>$serial_no,'certificate_filename'=>$certName,'template_id'=>$template_id,'key'=>$key,'path'=>$urlRelativeFilePath,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id]);
        }

        return $result['id'];
    }
    public function getPrintCount($serial_no)
    {
        $numCount = PrintingDetail::select('id')->where('sr_no',$serial_no)->count();
        return $numCount + 1;
    }
    public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no,$template_name,$admin_id,$student_table_id)
    {
        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];
        $auth_site_id=\Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            // Insert the new record
            $result = SbPrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>$sr_no,'template_name'=>$template_name,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1]);
        }
        else{
            // Insert the new record
            $result = PrintingDetail::create(['username'=>$username,'print_datetime'=>$print_datetime,'printer_name'=>$printer_name,'print_count'=>$printer_count,'print_serial_no'=>$print_serial_no,'sr_no'=>$sr_no,'template_name'=>$template_name,'created_at'=>$datetime,'created_by'=>$ses_id,'updated_at'=>$datetime,'updated_by'=>$ses_id,'status'=>$sts,'site_id'=>$auth_site_id,'publish'=>1,'student_table_id'=>$student_table_id]);
        }
    }
    public function nextPrintSerial()
    {
        $current_year = 'PN/' . $this->getFinancialyear() . '/';
        // find max
        $maxNum = 0;

        $result = \DB::select("SELECT COALESCE(MAX(CONVERT(SUBSTR(print_serial_no, 10), UNSIGNED)), 0) AS next_num "
            . "FROM printing_details WHERE SUBSTR(print_serial_no, 1, 9) = '$current_year'");
        
        //get next num
        $maxNum = $result[0]->next_num + 1;

        return $current_year . $maxNum;
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

    public function check_file_exist($url){
        $handle = @fopen($url, 'r');
        if(!$handle){
            return false;
        }else{
            return true;
        }
    }


}
