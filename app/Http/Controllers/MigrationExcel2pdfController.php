<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiTracker;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApiTrakerExport;
use Mail;
use Session;
//use Illuminate\Support\Facades\Mail;
use App\Exports\TemplateMasterExport;
use App\Jobs\SendMailJob;
use Storage;
use TCPDF;
use TCPDF_FONTS;
use App\Models\Site;
use App\Models\StudentTable;
use App\models\TemplateMaster;
use App\models\BackgroundTemplateMaster;
use App\models\FieldMaster;
use App\models\FontMaster;

use App\models\pdf2pdf\TemplateMaster as ExcelPDFTemplateMaster;

use QrCode;
use Auth;
use DB;

class MigrationExcel2pdfController extends Controller
{
    
    public function migrationStore(Request $request)
    {   

        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $instance = $subdomain[0];



        $siteData = Site::on('mysql')->select('new_server')->where(DB::raw('SUBSTRING_INDEX(site_url, ".", 1)'),$instance)->first();

        $dbSource = '';
        if($siteData->new_server == 0) {
            $dbSource = 'mysql';
        } else {
            $dbSource = 'mysql_new';
        }



        $site_id=Auth::guard('admin')->user()->site_id;
        $admin_id = \Auth::guard('admin')->user()->toArray();
        

        $blankPdfFile = public_path().'/blank.pdf';
    
        $file_name = basename($blankPdfFile);

        $pdfFile =  date("YmdHis") . "_" . $file_name;
        $target_path = public_path().'/'.$subdomain[0].'/uploads/pdfs/';

        //if directory not exist make directory
        if(!is_dir($target_path)){
            mkdir($target_path, 0777,true);
        }
        $path='uploads/pdfs/'.$pdfFile ;

        // Template Master ID for which is migrate
        $id = $request->template_id;
        // $id = 1000;

        $dataArray = [];
        
        //get backgroubd template master from db
        $BGTEMPLATE = BackgroundTemplateMaster::on($dbSource)->select('id','background_name')->where('site_id',$site_id)->get();
        //get template master data
        $TEMPLATE = TemplateMaster::on($dbSource)->where('id',$id)->first();

        if(!$TEMPLATE) {
            return response()->json(['success'=>"error",'message'=>'Template is not found.']);
        }
        
        $template_name =  str_replace(' ', '_',$TEMPLATE->template_name); 
        
       
        $FIELDS = FieldMaster::on($dbSource)->where('template_id',$id)->orderBy('field_position','ASC')->get();

        
        
        // echo "<pre>";
        $i = 1;

        // Array to store the mapped names
        $mappedNames = [];
        
        $staticImageName = [];

        // Assuming $FIELDS is an array of objects
        foreach($FIELDS as $key => $FID ) {


            
            if($FID->text_justification == 'L') {
                $placerDisplay = "0"; 
            } else if($FID->text_justification == 'C') {
                $placerDisplay = "1"; 
            } else if($FID->text_justification == 'R') {
                $placerDisplay = "2"; 
            } else if($FID->text_justification == 'J') {
                $placerDisplay = "3"; 
            } else {
                $placerDisplay = ""; 
            }



            if($FID->font_id != 0) {
                $font_data=FontMaster::on($dbSource)->find($FID->font_id)->toArray(); 

                if($FID->font_style == '' || empty($FID->font_style) ) {
                    $fontName = $font_data['font_filename_N'];
                } else if($FID->font_style == 'B') {
                    $fontName = $font_data['font_filename_B'];
                } else if($FID->font_style == 'BI') {
                    $fontName = $font_data['font_filename_BI'];
                } else if($FID->font_style == 'I') {
                    $fontName = $font_data['font_filename_I'];
                }
            }
            
            if($FID->font_color) {
                $color = "#".$FID->font_color;
            } else {
                $color = "#000000";
            }
            

            if($FID->security_type =='QR Code') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                $width = $this->mmToPointConvert($FID->width);
                $height = $this->mmToPointConvert($FID->height);
                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                // print_r($FID);
                $qrType = '';
                if($FID->combo_qr_text == '{{QR Code}}'){
                    $qrType = 'QR Default';
                    $qrSource = 'Current DateTime';
                } else {
                    $qrType = 'QR Dynamic';
                    $qrSource = 'Current DateTime';

                    if($FID->combo_qr_text) {
                        $convertedString = str_replace(['{{', '}}'], ['{^', '^}'], $FID->combo_qr_text);

                    }
                    
                }

                $x1=$x_pos;
                $y1=$y_pos;


                $x2=$x1+$width;
                $y2=$y1+$height;

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                $dataArray[$key]['placer_type'] = $qrType; 
                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height; 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                if($FID->visible == 1 ) {
                    $dataArray[$key]['qr_place'] = 'hide'; 
                } else {
                    $dataArray[$key]['qr_place'] = 'show'; 
                }

                $dataArray[$key]['barcode_content'] = ''; 
                $dataArray[$key]['barcode_content_position'] = ''; 
                $dataArray[$key]['source'] = $qrSource; 
                
                $dataArray[$key]['qr_details'] = nl2br($convertedString); 
                $dataArray[$key]['qr_position'] = ""; 

                $dataArray[$key]['placer_font_name'] = ''; 
                $dataArray[$key]['placer_font_bold'] = ''; 
                $dataArray[$key]['placer_font_italic'] = ''; 
                $dataArray[$key]['placer_font_underline'] = ''; 
                $dataArray[$key]['placer_font_size'] = 0; 
                $dataArray[$key]['ghost_words'] = ''; 
                $dataArray[$key]['placer_display'] = $placerDisplay; 
                $dataArray[$key]['degree_angle'] = ''; 
                $dataArray[$key]['font_color'] = $color; 
                $dataArray[$key]['opacity_val'] = ''; 
                $dataArray[$key]['image_path'] = ''; 
                $dataArray[$key]['line_height'] = ''; 
                $dataArray[$key]['qr_position'] = ''; 

            }


            if($FID->security_type =='Qr Code') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                $width = $this->mmToPointConvert($FID->width);
                $height = $this->mmToPointConvert($FID->height);
                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                // print_r($FID);
                $qrType = '';
                if($FID->combo_qr_text == '{{QR Code}}'){
                    $qrType = 'QR Default';
                    $qrSource = 'Current DateTime';
                } else {
                    $qrType = 'QR Dynamic';
                    $qrSource = 'Current DateTime';
                    if($FID->combo_qr_text) {
                        $convertedString = str_replace(['{{', '}}'], ['{^', '^}'], $FID->combo_qr_text);

                    }
                }
                $x1=$x_pos;
                $y1=$y_pos;


                $x2=$x1+$width;
                $y2=$y1+$height;

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                $dataArray[$key]['placer_type'] = $qrType; 
                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height; 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                if($FID->visible == 1 ) {
                    $dataArray[$key]['qr_place'] = 'hide'; 
                } else {
                    $dataArray[$key]['qr_place'] = 'show'; 
                }

                $dataArray[$key]['barcode_content'] = ''; 
                $dataArray[$key]['barcode_content_position'] = ''; 
                $dataArray[$key]['source'] = $qrSource; 
                
                $dataArray[$key]['qr_details'] = $convertedString; 
                $dataArray[$key]['qr_position'] = ""; 
                $dataArray[$key]['placer_font_name'] = ''; 
                $dataArray[$key]['placer_font_bold'] = ''; 
                $dataArray[$key]['placer_font_italic'] = ''; 
                $dataArray[$key]['placer_font_underline'] = ''; 
                $dataArray[$key]['placer_font_size'] = 0; 
                $dataArray[$key]['ghost_words'] = ''; 
                $dataArray[$key]['placer_display'] = $placerDisplay; 
                $dataArray[$key]['degree_angle'] = ''; 
                $dataArray[$key]['font_color'] = $color; 
                $dataArray[$key]['opacity_val'] = ''; 
                $dataArray[$key]['image_path'] = ''; 
                $dataArray[$key]['line_height'] = ''; 
                $dataArray[$key]['qr_position'] = ''; 

            }
            

            if($FID->name =='ID Barcode') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                $dataArray[$key]['placer_type'] = 'Barcode'; 

                $width = $this->mmToPointConvert($FID->width);
                $height = $this->mmToPointConvert($FID->height);
                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                $x1=$x_pos;
                $y1=$y_pos;
                $x2=$x1+$width;

                if($height == 0) {
                    // $y2_mm =  $FID->font_size +3;
                    $y2_mm =  $FID->font_size;
                    
                    $y2_po = $this->mmToPointConvert($y2_mm);
                    $y2=$y1+$y2_po;
                } else {
                    $y2=$y1+$height;
                }

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 
                
                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height; 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                

                $dataArray[$key]['placer_font_name'] = ''; 
                $dataArray[$key]['placer_font_bold'] = ''; 
                $dataArray[$key]['placer_font_italic'] = ''; 
                $dataArray[$key]['placer_font_underline'] = ''; 
                $dataArray[$key]['placer_font_size'] = 10; 
                
                $dataArray[$key]['ghost_words'] = ''; 
                $dataArray[$key]['placer_display'] = $placerDisplay; 
                $dataArray[$key]['qr_details'] = $FID->sample_text ? $FID->sample_text : "static text"; 
                $dataArray[$key]['qr_position'] = ''; 
                $dataArray[$key]['source'] = $FID->mapped_name ? $FID->mapped_name : ""; 
                
                $dataArray[$key]['degree_angle'] = 0; 
                $dataArray[$key]['font_color'] = $color; 
                $dataArray[$key]['opacity_val'] = ''; 
                $dataArray[$key]['image_path'] = ''; 
                $dataArray[$key]['line_height'] = ''; 
                $dataArray[$key]['opacity_val'] = ''; 
                $dataArray[$key]['qr_place'] = ''; 
                $dataArray[$key]['barcode_content'] = ''; 
                $dataArray[$key]['barcode_content_position'] = 'Text at Bottom'; 


               
               

            }

            if($FID->security_type =='Static Text') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                $width = $this->mmToPointConvert($FID->width);
                $height = $this->mmToPointConvert($FID->height);
                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                $x1=$x_pos;
                $y1=$y_pos;
                
                $x2=$x1+$width;
                
                if($height == 0) {
                    // $y2_mm =  $FID->font_size +3;
                    $y2_mm =  $FID->font_size;
                    
                    $y2_po = $this->mmToPointConvert($y2_mm);
                    $y2=$y1+$y2_po;
                } else {
                    $y2=$y1+$height;
                }

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                $dataArray[$key]['placer_type'] = "Static Text"; 
                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height ? $height : $this->mmToPointConvert(10); 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                $dataArray[$key]['placer_font_name'] = $fontName ? $fontName : ''; 
                $dataArray[$key]['placer_font_bold'] = ""; 
                $dataArray[$key]['placer_font_italic'] = ""; 
                $dataArray[$key]['placer_font_underline'] = ""; 
                $dataArray[$key]['placer_font_size'] = $FID->font_size ? $FID->font_size : 10; 
                $dataArray[$key]['ghost_words'] = ""; 
                $dataArray[$key]['placer_display'] = $placerDisplay; 
                $dataArray[$key]['qr_details'] = $FID->sample_text ? $FID->sample_text : "static text is here"; 
                $dataArray[$key]['qr_position'] = ""; 
                $dataArray[$key]['source'] =  ""; 
                $dataArray[$key]['degree_angle'] = ""; 
                $dataArray[$key]['font_color'] = $color;
                $dataArray[$key]['opacity_val'] = $FID->text_opicity; 
                $dataArray[$key]['image_path'] = ""; 
                $dataArray[$key]['line_height'] = ""; 
                $dataArray[$key]['qr_place'] = ""; 
                $dataArray[$key]['barcode_content'] = ""; 
                $dataArray[$key]['barcode_content_position'] = ""; 

                


            } 


            if($FID->security_type =='Normal') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                $width = $this->mmToPointConvert($FID->width);
                $height = $this->mmToPointConvert($FID->height);
                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                $x1=$x_pos;
                $y1=$y_pos;
                
                $x2=$x1+$width;
                
                if($height == 0) {
                    // $y2_mm =  $FID->font_size +3;
                    $y2_mm =  $FID->font_size;
                    
                    $y2_po = $this->mmToPointConvert($y2_mm);
                    $y2=$y1+$y2_po;
                } else {
                    $y2=$y1+$height;
                }

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                $dataArray[$key]['placer_type'] = "Plain Text"; 
                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height ? $height : $this->mmToPointConvert(10); 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 


                
                
                $dataArray[$key]['placer_font_name'] = $fontName ? $fontName : ''; 
                $dataArray[$key]['placer_font_bold'] = ""; 
                $dataArray[$key]['placer_font_italic'] = ""; 
                $dataArray[$key]['placer_font_underline'] = ""; 
                $dataArray[$key]['placer_font_size'] = $FID->font_size ? $FID->font_size : 10; 
                $dataArray[$key]['ghost_words'] = ""; 

                $dataArray[$key]['placer_display'] = $placerDisplay; 
                $dataArray[$key]['qr_details'] = ""; 
                $dataArray[$key]['qr_position'] = ""; 
                $dataArray[$key]['source'] =  $FID->mapped_name;
                $dataArray[$key]['degree_angle'] = ""; 
                $dataArray[$key]['font_color'] = $color;
                
                // $FID->font_color ? $FID->font_color : 

                $dataArray[$key]['opacity_val'] = $FID->text_opicity; 
                $dataArray[$key]['image_path'] = ""; 
                $dataArray[$key]['line_height'] = ""; 
                $dataArray[$key]['qr_place'] = ""; 
                $dataArray[$key]['barcode_content'] = ""; 
                $dataArray[$key]['barcode_content_position'] = ""; 
                
            }

            if($FID->security_type =='Micro line') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                $width = $this->mmToPointConvert($FID->width);
                $height = $this->mmToPointConvert($FID->height);
                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                $x1=$x_pos;
                $y1=$y_pos;
                
                $x2=$x1+$width;
                
                if($height == 0) {
                    // $y2_mm =  $FID->font_size +3;
                    $y2_mm =  $FID->font_size;
                    
                    $y2_po = $this->mmToPointConvert($y2_mm);
                    $y2=$y1+$y2_po;
                } else {
                    $y2=$y1+$height;
                }

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                $dataArray[$key]['placer_type'] = "Micro Line"; 
                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height ? $height : $this->mmToPointConvert(10); 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                $dataArray[$key]['placer_font_name'] = $fontName ? $fontName : '';  
                $dataArray[$key]['placer_font_bold'] = ""; 
                $dataArray[$key]['placer_font_italic'] = ""; 
                $dataArray[$key]['placer_font_underline'] = ""; 
                $dataArray[$key]['placer_font_size'] = $FID->font_size ? $FID->font_size : 10; 
                $dataArray[$key]['ghost_words'] = ""; 
                $dataArray[$key]['placer_display'] = $placerDisplay; 
                $dataArray[$key]['qr_details'] = ""; 
                $dataArray[$key]['qr_position'] = ""; 
                $dataArray[$key]['source'] =  $FID->mapped_name ? $FID->mapped_name : ""; 
                $dataArray[$key]['degree_angle'] = $FID->angle ? $FID->angle : ""; 
                $dataArray[$key]['font_color'] = $color;
                $dataArray[$key]['opacity_val'] = $FID->text_opicity; 
                $dataArray[$key]['image_path'] = ""; 
                $dataArray[$key]['line_height'] = ""; 
                $dataArray[$key]['qr_place'] = ""; 
                $dataArray[$key]['barcode_content'] = ""; 
                $dataArray[$key]['barcode_content_position'] = ""; 
                $dataArray[$key]['is_repeat'] = $FID->is_repeat ? $FID->is_repeat : 0; 
                

                
            }



            if($FID->security_type =='Ghost Image') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                $print_words = $FID->length ? $FID->length : 6;
                if($FID->font_size==10){
                    $img_width= round(floatval(15.6) * $print_words); 
                    $w=$img_width; 
                    $h=14; 
                }else if($FID->font_size==11){
                    $img_width= round(floatval(19.8) * $print_words);
                    $w=$img_width; 
                    $h=20; 
                }else if($FID->font_size==12){
                    $img_width= round(floatval(23.4) * $print_words);
                    $w=$img_width; 
                    $h=23; 
                }else if($FID->font_size==13){
                    $img_width= round(floatval(29.2) * $print_words); 
                    $w=$img_width; 
                    $h=29; 
                }else if($FID->font_size==14){
                    $img_width= round(floatval(36.2) * $print_words); 
                    $w=$img_width; 
                    $h=35; 
                }else if($FID->font_size==15){
                    $img_width= round(floatval(40.4) * $print_words); 
                    $w=$img_width; 
                    $h=40; 
                }

                // $width = $this->mmToPointConvert($w);
                // $height = $this->mmToPointConvert($h);
                $width = $w ;
                $height = $h;
                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                $x1=$x_pos;
                $y1=$y_pos;
                
                $x2=$x1+$width;
                
                if($height == 0) {
                    // $y2_mm =  $FID->font_size +3;
                    $y2_mm =  $FID->font_size;
                    
                    $y2_po = $this->mmToPointConvert($y2_mm);
                    $y2=$y1+$y2_po;
                } else {
                    $y2=$y1+$height;
                }

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                $dataArray[$key]['placer_type'] = "Ghost Image"; 
                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height ? $height : $this->mmToPointConvert(10); 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                $dataArray[$key]['placer_font_name'] = ""; 
                $dataArray[$key]['placer_font_bold'] = ""; 
                $dataArray[$key]['placer_font_italic'] = ""; 
                $dataArray[$key]['placer_font_underline'] = ""; 
                $dataArray[$key]['placer_font_size'] = $FID->font_size ? $FID->font_size : 10; 
                $dataArray[$key]['ghost_words'] = $FID->length ? $FID->length : 6; 
                $dataArray[$key]['placer_display'] = $placerDisplay;
                $dataArray[$key]['qr_details'] = ""; 
                $dataArray[$key]['qr_position'] = ""; 
                $dataArray[$key]['source'] =  $FID->mapped_name ? $FID->mapped_name : ""; 
                $dataArray[$key]['degree_angle'] = $FID->angle ? (int)$FID->angle : 0; 
                $dataArray[$key]['font_color'] = $color; 
                $dataArray[$key]['opacity_val'] = $FID->text_opicity; 
                $dataArray[$key]['image_path'] = ""; 
                $dataArray[$key]['line_height'] = ""; 
                $dataArray[$key]['qr_place'] = ""; 
                $dataArray[$key]['barcode_content'] = ""; 
                $dataArray[$key]['barcode_content_position'] = ""; 
                
            }


            if($FID->security_type =='Static Image') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                // $width = $this->mmToPointConvert($FID->width);
                // $height = $this->mmToPointConvert($FID->height);

                $width = $FID->width;
                $height = $FID->height;

                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                $x1=$x_pos;
                $y1=$y_pos;
                
                $x2=$x1+$width;
                
                if($height == 0) {
                    // $y2_mm =  $FID->font_size +3;
                    $y2_mm =  $FID->font_size;
                    
                    $y2_po = $this->mmToPointConvert($y2_mm);
                    $y2=$y1+$y2_po;
                } else {
                    $y2=$y1+$height;
                }
                

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                if($FID->is_uv_image == 1  || $FID->is_uv_image == '1') {
                    $dataArray[$key]['placer_type'] = "Invisible Image"; 
                    $dataArray[$key]['source'] =  ""; 
                } else {
                    $dataArray[$key]['placer_type'] = "Image"; 
                    $dataArray[$key]['source'] =  $FID->sample_image ? $FID->sample_image : ""; 
                }

                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height ? $height : $this->mmToPointConvert(10); 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                $dataArray[$key]['placer_font_name'] = ""; 
                $dataArray[$key]['placer_font_bold'] = ""; 
                $dataArray[$key]['placer_font_italic'] = ""; 
                $dataArray[$key]['placer_font_underline'] = ""; 
                $dataArray[$key]['placer_font_size'] = $FID->font_size ? $FID->font_size : 10; 
                $dataArray[$key]['ghost_words'] = $FID->length ? $FID->length : 6; 
                $dataArray[$key]['placer_display'] = $placerDisplay;
                $dataArray[$key]['qr_details'] = ""; 
                $dataArray[$key]['qr_position'] = ""; 
                
                $dataArray[$key]['degree_angle'] = $FID->angle ? (int)$FID->angle : 0; 
                $dataArray[$key]['font_color'] = $color; 
                $dataArray[$key]['opacity_val'] = $FID->text_opicity; 
                $dataArray[$key]['image_path'] = $FID->sample_image ? $FID->sample_image : ""; 
                
                $dataArray[$key]['line_height'] = ""; 
                $dataArray[$key]['qr_place'] = ""; 
                $dataArray[$key]['barcode_content'] = ""; 
                $dataArray[$key]['barcode_content_position'] = ""; 

                if($FID->sample_image) {
                    $imageFileName = $FID->sample_image;
                    $staticImageName[] = $imageFileName;
                    
                }
                
            }


            if($FID->security_type =='Dynamic Image') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                // $width = $this->mmToPointConvert($FID->width);
                // $height = $this->mmToPointConvert($FID->height);

                $width = $FID->width;
                $height = $FID->height;

                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                $x1=$x_pos;
                $y1=$y_pos;
                
                $x2=$x1+$width;
                
                if($height == 0) {
                    // $y2_mm =  $FID->font_size +3;
                    $y2_mm =  $FID->font_size;
                    
                    $y2_po = $this->mmToPointConvert($y2_mm);
                    $y2=$y1+$y2_po;
                } else {
                    $y2=$y1+$height;
                }
                

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                if($FID->is_uv_image == 1) {
                    $dataArray[$key]['placer_type'] = "Invisible Image"; 
                } else {
                    $dataArray[$key]['placer_type'] = "Dynamic Image"; 
                }

                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height ? $height : $this->mmToPointConvert(10); 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                $dataArray[$key]['placer_font_name'] = ""; 
                $dataArray[$key]['placer_font_bold'] = ""; 
                $dataArray[$key]['placer_font_italic'] = ""; 
                $dataArray[$key]['placer_font_underline'] = ""; 
                $dataArray[$key]['placer_font_size'] = $FID->font_size ? $FID->font_size : 10; 
                $dataArray[$key]['ghost_words'] = $FID->length ? $FID->length : 6; 
                $dataArray[$key]['placer_display'] = $placerDisplay;
                $dataArray[$key]['qr_details'] = ""; 
                $dataArray[$key]['qr_position'] = ""; 
                $dataArray[$key]['source'] =  $FID->mapped_name ? $FID->mapped_name : ""; 
                $dataArray[$key]['degree_angle'] = $FID->angle ? (int)$FID->angle : 0; 
                $dataArray[$key]['font_color'] = $color; 
                $dataArray[$key]['opacity_val'] = $FID->text_opicity; 
                $dataArray[$key]['image_path'] = $FID->sample_image ? $FID->sample_image : ""; 
                
                $dataArray[$key]['line_height'] = ""; 
                $dataArray[$key]['qr_place'] = ""; 
                $dataArray[$key]['barcode_content'] = ""; 
                $dataArray[$key]['barcode_content_position'] = ""; 

                // if($FID->sample_image) {
                //     $imageFileName = $FID->sample_image;
                //     $dynamicImageFile = public_path().'\\'.$subdomain[0].'\\backend\templates\\'.$TEMPLATE->id.'\\'.$FID->sample_image;
                // }
                
            }


            if($FID->security_type =='Invisible') {
                $dataArray[$key]['page_no'] = 0; 
                $dataArray[$key]['nature'] = 'placer'; 
                $dataArray[$key]['placer'] = "place_rect_".$key; 

                $width = $this->mmToPointConvert($FID->width);
                $height = $this->mmToPointConvert($FID->height);
                $x_pos = $this->mmToPointConvert($FID->x_pos);
                $y_pos = $this->mmToPointConvert($FID->y_pos);

                $x1=$x_pos;
                $y1=$y_pos;
                
                $x2=$x1+$width;
                
                if($height == 0) {
                    // $y2_mm =  $FID->font_size +3;
                    $y2_mm =  $FID->font_size;
                    
                    $y2_po = $this->mmToPointConvert($y2_mm);
                    $y2=$y1+$y2_po;
                } else {
                    $y2=$y1+$height;
                }

                $dataArray[$key]['placer_coords'] = $x1.','.$y1.','.$x2.','.$y2; 

                $dataArray[$key]['placer_type'] = "Invisible"; 
                $dataArray[$key]['width'] = $width; 
                $dataArray[$key]['height'] = $height ? $height : $this->mmToPointConvert(10); 
                $dataArray[$key]['left'] = $x_pos; 
                $dataArray[$key]['top'] = $y_pos; 
                $dataArray[$key]['placer_font_name'] = ""; 
                $dataArray[$key]['placer_font_bold'] = ""; 
                $dataArray[$key]['placer_font_italic'] = ""; 
                $dataArray[$key]['placer_font_underline'] = ""; 
                $dataArray[$key]['placer_font_size'] = $FID->font_size ? $FID->font_size : 10; 
                $dataArray[$key]['ghost_words'] = ""; 
                $dataArray[$key]['placer_display'] = $placerDisplay;
                $dataArray[$key]['qr_details'] = ""; 
                $dataArray[$key]['qr_position'] = ""; 
                $dataArray[$key]['source'] =  $FID->mapped_name;
                $dataArray[$key]['degree_angle'] = ""; 
                $dataArray[$key]['font_color'] = 'YELLOW';
                
                // $FID->font_color ? $FID->font_color : 

                $dataArray[$key]['opacity_val'] = $FID->text_opicity; 
                $dataArray[$key]['image_path'] = ""; 
                $dataArray[$key]['line_height'] = ""; 
                $dataArray[$key]['qr_place'] = ""; 
                $dataArray[$key]['barcode_content'] = ""; 
                $dataArray[$key]['barcode_content_position'] = ""; 
                
            }

            
            

            if($FID->mapped_name) {
                // Collecting the mapped_name
                $mappedNames[] = $FID->mapped_name;
            }


        }



        // Convert the array to a JSON formatted string
        $jsonData = json_encode($mappedNames);

        
        

        $transformed = [];

        foreach ($dataArray as $item) {
            $transformed[] = [
                'page_no' => $item['page_no'],
                'placer' => $item['placer'],
                'width' => $item['width'],
                'height' => $item['height'],
                'placer_coords' => $item['placer_coords'],
                'placer_font_name' => $item['placer_font_name'],
                'placer_font_bold' => $item['placer_font_bold'],
                'placer_font_italic' => $item['placer_font_italic'],
                'placer_font_underline' => $item['placer_font_underline'],
                'placer_font_size' => $item['placer_font_size'],
                'placer_type' => $item['placer_type'],
                'ghost_words' => $item['ghost_words'],
                'placer_display' => $item['placer_display'],
                'qr_details' => isset($item['qr_details']) ? $item['qr_details'] : '',
                'qr_position' => $item['qr_position'],
                'source' => isset($item['source']) ? $item['source'] : '',
                'degree_angle' => $item['degree_angle'],
                'font_color' => $item['font_color'],
                'opacity_val' => $item['opacity_val'],
                'image_path' => $item['image_path'],
                'line_height' => $item['line_height'],
                'left' => $item['left'],
                'top' => $item['top'],
                'qr_place' => $item['qr_place'],
                'barcode_content' => $item['barcode_content'],
                'barcode_content_position' => $item['barcode_content_position'],
                'is_repeat' => $item['is_repeat']
            ];
        }

        // Database stored ep_details
        $ep_boxes = json_encode($transformed, JSON_PRETTY_PRINT);
        

        $placer_boxes = $ep_boxes;


        
        $converted = ["objects" => []];

        foreach ($transformed as $item) {
            $converted['objects'][] = [
                "type" => "rect",
                "originX" => "left",
                "originY" => "top",
                "left" => (float) $item['left'],
                "top" => (float) $item['top'],
                "width" => (float) $item['width'],
                "height" => (float) $item['height'],
                "fill" => "rgba(0, 181, 204, 0.3)",
                "stroke" => "blue",
                "strokeWidth" => 0.1,
                "strokeDashArray" => null,
                "strokeLineCap" => "butt",
                "strokeLineJoin" => "miter",
                "strokeMiterLimit" => 10,
                "scaleX" => 1,
                "scaleY" => 1,
                "angle" => (float) $item['degree_angle'],
                "flipX" => false,
                "flipY" => false,
                "opacity" => (float) $item['opacity_val'] ?: 1,
                "shadow" => null,
                "visible" => true,
                "clipTo" => null,
                "backgroundColor" => "",
                "fillRule" => "nonzero",
                "globalCompositeOperation" => "source-over",
                "transformMatrix" => null,
                "skewX" => 0,
                "skewY" => 0,
                "rx" => 0,
                "ry" => 0,
                "id" => "1",  // You may need to generate unique IDs
                "page_no" => $item['page_no'],
                "name" => $item['placer'],
                "source" => $item['source'],
                "nature" => "placer",
                "fontName" => $item['placer_font_name'],
                "fontBold" => $item['placer_font_bold'],
                "fontItalic" => $item['placer_font_italic'],
                "fontUnderline" => $item['placer_font_underline'],
                "fontSize" => (float) $item['placer_font_size'],
                "placer_type" => $item['placer_type'],
                "ghost_words" => $item['ghost_words'],
                "placer_display" => $item['placer_display'],
                "qr_details" => $item['qr_details'],
                "qr_position" => $item['qr_position'],
                "degree_angle" => (float) $item['degree_angle'],
                "font_color" => $item['font_color'] ?: "BLACK",
                "opacity_val" => (float) $item['opacity_val'] ?: 1,
                "image_path" => $item['image_path'],
                "lineHeight" => $item['line_height'],
                "qrPlace" => $item['qr_place'],
                "barcodeContent" => $item['barcode_content'],
                "barcodeContentPosition" => $item['barcode_content_position']
            ];
        }
        // echo "<br>";
        // echo "<br>";
        // echo "<pre>";
        // // Output or use the converted array as needed
        // // print_r($converted);
        // echo "</pre>";

        $formatted_array = array(
            $converted
        );

        $json = json_encode($formatted_array, JSON_PRETTY_PRINT);
        $pdf_data = json_encode($formatted_array, JSON_PRETTY_PRINT);
        
        file_put_contents('data.json', $json);


        $templateData = ExcelPDFTemplateMaster::on($dbSource)->select('id')->where('template_name',$template_name)->first();
        
        if($templateData){
            $template_id = $templateData->id;
            
            
            
            $print_bg_status = $TEMPLATE->background_template_status == 0 ? 'No' : 'Yes';


            $bg_template_id = $TEMPLATE->bg_template_id;
            $verification_bg_status = $TEMPLATE->bg_template_id == 0 ? 'No' : 'Yes';


            
            ExcelPDFTemplateMaster::on($dbSource)->where('id',$template_id)->update([ "placer_details"=>$placer_boxes,"ep_details"=>$ep_boxes,"print_bg_file"=>$bg_template_id,"print_bg_status"=>$print_bg_status,"verification_bg_file"=>$bg_template_id,"verification_bg_status"=>$verification_bg_status]);   



            TemplateMaster::on($dbSource)->where('id',$TEMPLATE->id)->update([ "migrated"=>1]);   


            
            

            if($staticImageName) {
                foreach($staticImageName as $imageFile) {
                    $image_path_excel2pdf =public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf//".$template_id.'//images/'.$imageFile;
                    // $aws_qr1 = \File::copy($imageFile,$image_path_excel2pdf);
                    $image_path_excel2pdffolder = public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf//".$template_id.'//images';
                    if(!is_dir($image_path_excel2pdffolder)){
                        mkdir($image_path_excel2pdffolder, 0777);
                    }
                    $imageFile1 = public_path().'\\'.$subdomain[0].'\\backend\templates\\'.$TEMPLATE->id.'\\'.$imageFile;
                    if (!file_exists($image_path_excel2pdf)) {
                        \File::copy($imageFile1,$image_path_excel2pdf);
                    }
                }
            }

            // Dynamic Image Management
            $copy_Template_file= glob(public_path().'/'.$subdomain[0].'/backend/templates/'.$TEMPLATE->id."/*");
            $new_Template_file=public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf/".$template_id.'/';
            if(!is_dir($new_Template_file)){
                mkdir($new_Template_file, 0777);
            }

            foreach ($copy_Template_file as $key => $value) {
                $image_name_get=str_replace(public_path().'/'.$subdomain[0].'/backend/templates/'.$TEMPLATE->id.'/','', $value);
                $destFile=$new_Template_file.$image_name_get;
                if (!file_exists($destFile)) {
                    # code...    
                    \File::copy($value,$destFile);
                }
            }
            // Dynamic Image Management

            // if($imageFile) {
            //     $image_path_excel2pdf =public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf//".$template_id.'//images/'.$imageFileName;
            //     // $aws_qr1 = \File::copy($imageFile,$image_path_excel2pdf);

            //     $image_path_excel2pdffolder = public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf//".$template_id.'//images';
            //     if(!is_dir($image_path_excel2pdffolder)){
            //         mkdir($image_path_excel2pdffolder, 0777);
            //     }
            //     if (!file_exists($image_path_excel2pdf)) {
            //         \File::copy($imageFile,$image_path_excel2pdf);
            //     }

            // }
            // if($dynamicImageFile) {
            //     $image_path_excel2pdf =public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf//".$template_id.'//'.$imageFileName;
            //     \File::copy($dynamicImageFile,$image_path_excel2pdf);
            // }

            


            $folder=public_path().'/'.$subdomain[0].'/documents/';
            unlink($folder.$template_name.".json");
            $myfile = fopen($folder."/".$template_name.".json", "w") or die("Unable to open file!");
            fwrite($myfile, $pdf_data);              
            fclose($myfile);

            $folder=public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf';
            if (!file_exists($folder)) {
                mkdir($folder, 0777,true);
            }
            $template_folder=public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf/'.$template_id;
            if (!file_exists($template_folder)) {
                mkdir($template_folder, 0777,true);
            } 

            return response()->json(['success'=>"success",'message'=>'Migrated Template updated successfully.','type'=>'toaster','rstatus' => 'edit']);
        
        } else {

            
            $aws_qr = \File::copy($blankPdfFile,$target_path.''.$pdfFile );


            


            $print_bg_status = $TEMPLATE->background_template_status == 0 ? 'No' : 'Yes';


            $bg_template_id = $TEMPLATE->bg_template_id;
            $verification_bg_status = $TEMPLATE->bg_template_id == 0 ? 'No' : 'Yes';


            
            

            #Upload template in table
            $insertRequest = new ExcelPDFTemplateMaster();
            $insertRequest->setConnection($dbSource);
            $insertRequest->file_name=$path;
            $insertRequest->extractor_details='[]';
            $insertRequest->placer_details=$placer_boxes;
            $insertRequest->ep_details=$ep_boxes;
            $insertRequest->template_title=$template_name;
            $insertRequest->template_name=$template_name;
            $insertRequest->pdf_page='Single';
            $insertRequest->generated_by=$admin_id['id'];
            $insertRequest->map_type=1;

            $insertRequest->print_bg_file = $bg_template_id;
            $insertRequest->print_bg_status =$print_bg_status;

            $insertRequest->verification_bg_file = $bg_template_id;
            $insertRequest->verification_bg_status =$verification_bg_status;
        
            $insertRequest->save();

            $insertRequest=$insertRequest->toArray();
            $template_id=$insertRequest['id'];


            TemplateMaster::on($dbSource)->where('id',$TEMPLATE->id)->update([ "migrated"=>1]);
            
            $folder=public_path().'/'.$subdomain[0].'/documents';
            if (!file_exists($folder)) {
                mkdir($folder, 0777,true);
            }                           
            $myfile = fopen($folder."/".$template_name.".json", "w") or die("Unable to open file!");
            fwrite($myfile, $pdf_data);              
            fclose($myfile);                                 

            $folder=public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf';
            if (!file_exists($folder)) {
                mkdir($folder, 0777,true);
            }
            $template_folder=public_path().'/'.$subdomain[0].'/backend/templates/excel2pdf/'.$template_id;
            if (!file_exists($template_folder)) {
                mkdir($template_folder, 0777,true);
            }

            // Static Image
            if($staticImageName) {
                foreach($staticImageName as $imageFile) {
                    $image_path_excel2pdf =public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf//".$template_id.'//images/'.$imageFile;
                    // $aws_qr1 = \File::copy($imageFile,$image_path_excel2pdf);
                    
                    

                    $image_path_excel2pdffolder = public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf//".$template_id.'//images';
                    if(!is_dir($image_path_excel2pdffolder)){
                        mkdir($image_path_excel2pdffolder, 0777);
                    }
                    $imageFile1 = public_path().'\\'.$subdomain[0].'\\backend\templates\\'.$TEMPLATE->id.'\\'.$imageFile;
                    if (!file_exists($image_path_excel2pdf)) {
                        \File::copy($imageFile1,$image_path_excel2pdf);
                    }

                    


                }
            }


            // Dynamic Image Management
            $copy_Template_file= glob(public_path().'/'.$subdomain[0].'/backend/templates/'.$TEMPLATE->id."/*");
            $new_Template_file=public_path().'/'.$subdomain[0]."/backend/templates/excel2pdf/".$template_id.'/';
            if(!is_dir($new_Template_file)){
                mkdir($new_Template_file, 0777);
            }

            foreach ($copy_Template_file as $key => $value) {
                $image_name_get=str_replace(public_path().'/'.$subdomain[0].'/backend/templates/'.$TEMPLATE->id.'/','', $value);
                $destFile=$new_Template_file.$image_name_get;
                if (!file_exists($destFile)) {
                    # code...    
                    \File::copy($value,$destFile);
                }
            }
            // Dynamic Image Management


            // // Specify the file path
            $file = public_path().'/'.$subdomain[0].'/excel2pdf/processed_pdfs/excel/'.$template_name.'.txt';

            // Write the JSON string to the file
            file_put_contents($file, $jsonData);

            return response()->json(['success'=>"success",'message'=>'Migrated Template created successfully.','type'=>'toaster','rstatus' => 'insert','id'=>$template_id]);
            
        }   


    }
    


    function mmToPointConvert($number) {

        
        if ($number > 0) {
            $finalTotal = $number * 2.83465;

            $finalTotal = number_format($finalTotal,0);
            $finalTotal = str_replace(',', '', $finalTotal);
            // $finalTotal = $finalTotal - 3;
            return $finalTotal ;
            // return number_format($finalTotal,2);
        } else {
            return 0;
        }
    }

    
}
