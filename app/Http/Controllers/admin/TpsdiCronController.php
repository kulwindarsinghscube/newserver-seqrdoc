<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\IdCardStatusRequest;
use App\models\IdCardStatus;
use App\models\TemplateMaster;
use App\models\BackgroundTemplateMaster;
use App\models\FieldMaster;
use App\models\FontMaster;
use App\models\SystemConfig;
use App\models\Config;
use App\models\StudentTable;
use App\models\CardDataMapping;
use App\models\FunctionalUsers;
use App\models\PrintingDetail;
use App\models\SbStudentTable;
use App\models\SbPrintingDetail;
use Carbon\Carbon;
use DB;
use Mail;
use TCPDF;
use QrCode;
use Auth;
use File;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use Illuminate\Support\Facades\Log;
use App\Helpers\CoreHelper;

class TpsdiCronController extends Controller
{
    public function send_ToPrint() {
        $dbName = 'seqr_d_tpsdi';
        \DB::disconnect('mysql'); 
        \Config::set("database.connections.mysql", [
            'driver'   => 'mysql',
            'host'     => \Config::get('constant.DB_HOST'),
            "port" => \Config::get('constant.DB_PORT'),
            'database' => $dbName,
            'username' => \Config::get('constant.DB_UN'),
            'password' => \Config::get('constant.DB_PW'),
            "unix_socket" => "",
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => true,
            "engine" => null,
            "options" => []
        ]);
         \DB::reconnect();
        Log::info('Printing cron started.');
        $domain = 'tpsdi.seqrdoc.com';
        $subdomain = explode('.', $domain);
        $printed_logs=DB::table('printed_zip_logs')->whereNull('deleted_at')->get();
        if(count($printed_logs)>0){
            foreach ($printed_logs as $key => $log) {
                // dd(json_decode($log->id_card_status_ids));
                $id_card_status_ids=json_decode($log->id_card_status_ids);
                // dd(count($id_card_status_ids));
              $printed_records = DB::table('id_card_status')->where('status','Acknowledged')->whereIn('id', $id_card_status_ids)->count(); 
              $zip_file_name = $log->printed_zip_name;
                $save_path = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/print-card';
                $zip_full_path = public_path() . $save_path . '/' . $zip_file_name;
              if(count($id_card_status_ids)==$printed_records && file_exists($zip_full_path)){
                    unlink($zip_full_path);
                    DB::table('printed_zip_logs')
                    ->where('id', $log->id)
                    ->update(['deleted_at' => now()]);
                    Log::info('Printing zip deleted.'.$zip_full_path);
              }
               
            }
        }
        $records = DB::table('id_card_status')
            ->join('template_master', 'id_card_status.template_name', '=', 'template_master.template_name')
            ->where('id_card_status.status', 'Inprogress')
            ->where('id_card_status.email_status', 0)
            ->whereRaw('(
                SELECT SUM(`rows`)
                FROM id_card_status
                WHERE status = ? AND email_status = ?
            ) > 500', ['Inprogress', 0])
            ->select('id_card_status.id AS id_card_status_id','id_card_status.rows','id_card_status.request_number', 'template_master.id')
            ->get()->toArray();
            if(count($records)>0){
            $id_card_status_ids=array_column($records,'id_card_status_id');
            $total_quantity=array_sum(array_column($records,'rows'));
        try {
            $zip_file_name = 'print_data_' . date('Y-m-d_H-i-s') . '.zip';
            $save_path = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/print-card';
            $zip_full_path = public_path() . $save_path . '/' . $zip_file_name;
            // dd('https://'.$domain.$save_path);
            // Ensure the directory exists
            if (!file_exists(public_path() . $save_path)) {
                mkdir(public_path() . $save_path, 0777, true);
            }

            $zip = new \ZipArchive;
            
            if ($zip->open($zip_full_path, \ZipArchive::CREATE) === TRUE) {
                Log::error('Printing zip creation started for'.$zip_full_path);
                foreach ($records as $value) {
                    $exist_save_path = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $value->id;
                    $exist_file_name = str_replace('/', '_', $value->request_number);
                    $fullFilePath = public_path() . $exist_save_path . '/' . $exist_file_name.'.zip';

                    if (file_exists($fullFilePath)) {
                        $zip->addFile($fullFilePath, $exist_file_name.'.zip');
                    } else {
                        \Log::warning("Missing file: " . $fullFilePath);
                    }
                }
                $zip->close();
                $created_on = date('Y-m-d H:i:s');
                $mail_data = [
                                'records' => $records,
                                'total_quantity' => $total_quantity,
                                'file_path'=>'https://'.$domain.$save_path.'/'. $zip_file_name
                            ];

                $user_email = ['dev1@scube.net.in'];
                // $user_email = ['dtp@scube.net.in'];
                $cc_email = ['dev12@scube.net.in'];
                // $cc_email = ['dev12@scube.net.in'];
                $mail_subject = "TATA ID CARDS_".$total_quantity;
                $mail_view = 'mail.printPdf';

                $response = Mail::send($mail_view, ['mail_data' => $mail_data], function ($message) use ($user_email, $mail_subject, $cc_email) {
                    $message->to($user_email);
                    $message->cc($cc_email);
                    $message->subject($mail_subject);
                    $message->from('info@seqrdoc.com');
                });
                if (count(Mail::failures()) > 0) {
                    
                    Log::error('Email sending failed to: ' . implode(', ', Mail::failures()));
                } else {
                    Log::error('Printing zip emailed and zip logs stored successfully');
                    DB::table('printed_zip_logs')->insert(array('printed_zip_name'=>$zip_file_name,'id_card_status_ids'=>json_encode($id_card_status_ids)));
                    DB::table('id_card_status')
                    ->whereIn('id', $id_card_status_ids)
                    ->update(['email_status' => 1]);
                }   
            Log::error('Printing zip creation end for'.$zip_full_path);
            } else {
                \Log::error("Unable to open ZIP file for writing: $zip_full_path");
            }
          Log::info('Printing cron ended.');  
        } catch (\Exception $e) {
            \Log::error("ZIP creation error: " . $e->getMessage());
            dd($e);
        }
        }
    }

    public function index() {
        set_time_limit(0);
        ini_set('max_execution_time', 0); // No limit for script execution
        ini_set('max_input_time', 0);     // No limit for input parsing
        $dbName = 'seqr_d_tpsdi';
        \DB::disconnect('mysql'); 
        \Config::set("database.connections.mysql", [
            'driver'   => 'mysql',
            'host'     => \Config::get('constant.DB_HOST'),
            "port" => \Config::get('constant.DB_PORT'),
            'database' => $dbName,
            'username' => \Config::get('constant.DB_UN'),
            'password' => \Config::get('constant.DB_PW'),
            "unix_socket" => "",
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => true,
            "engine" => null,
            "options" => []
        ]);
         \DB::reconnect();
        $domain = 'tpsdi.seqrdoc.com';
        $baseQuery = DB::table('id_card_status')
            ->where('created_on', '<', Carbon::now()->subHours(24))
            ->where('status', 'Received')
            ->where('email_status', 0);

        if (empty($baseQuery->get()->toArray())) {
            Log::info('No records found for id_card_status older than '.Carbon::now()->subHours(24).'(24 hours) with status=Received and email_status=0');
        } else {
            $baseQuery->orderBy('id')->chunk(20, function ($results) use ($domain) {
                foreach ($results as $input) {
                    $this->processPdf($input->request_number, $domain);
                }
            });
        }

    }
    public function processPdf($input,$domain)
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0); // No limit for script execution
        ini_set('max_input_time', 0);     // No limit for input parsing
        $checkUploadedFileOnAwsOrLocal = new CheckUploadedFileOnAwsORLocalService();

        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $systemConfig = SystemConfig::first();

        
        $subdomain = explode('.', $domain);

        $request_number = $input;
        $IdCardStatus = IdCardStatus::where('request_number', $request_number)
            ->first();
        //$IdCardStatus['template_name']="Training participation 1 sided";
        $template_name = $IdCardStatus['template_name'];
        $excelfile = $IdCardStatus['excel_sheet'];
        $highestRow = $IdCardStatus['rows'] + 1;
        $status = $IdCardStatus['status'];



        $templateMaster = TemplateMaster::select('id', 'unique_serial_no', 'template_name', 'bg_template_id', 'width', 'background_template_status')->where(
            'template_name',
            $template_name
        )->first();


        $template_id = $templateMaster['id'];
        $unique_serial_no = $templateMaster['unique_serial_no'];
        $template_name = $templateMaster['template_name'];
        $background_template_id = $templateMaster['bg_template_id'];
        $template_width = $templateMaster['width'];
        $template_height = $templateMaster['height'];
        $backgound_template_status = $templateMaster['background_template_status'];

        if ($background_template_id != 0) {

            $backgroundMaster = BackgroundTemplateMaster::select('width', 'height')
                ->where('id', $background_template_id)
                ->first();

            $template_width = $backgroundMaster['width'];
            $template_height = $backgroundMaster['height'];
        }


        $FID = [];
        $FID['template_id'] = $template_id;
        $FID['bg_template_id'] = $background_template_id;
        $FID['template_width'] = $template_width;
        $FID['template_height'] = $template_height;
        $FID['template_name'] = $template_name;
        $FID['background_template_status'] = $backgound_template_status;
        $FID['printing_type'] = 'pdfGenerate';



        $fields_master = FieldMaster::where('template_id', $template_id)
            ->orderBy('field_position', 'asc')
            ->get();

        $fields = collect($fields_master);
        // print_r($fields);
        $check_mapped = array();
        foreach ($fields as $key => $value) {

            $FID['mapped_name'][] = $value['mapped_name'];
            $FID['mapped_excel_col'][] = '';
            $FID['data'][] = '';
            $FID['security_type'][] = $value['security_type'];
            $FID['field_position'][] = $value['field_position'];
            $FID['text_justification'][] = $value['text_justification'];
            $FID['x_pos'][] = $value['x_pos'];
            $FID['y_pos'][] = $value['y_pos'];
            $FID['width'][] = $value['width'];
            $FID['height'][] = $value['height'];
            $FID['font_style'][] = $value['font_style'];
            $FID['font_id'][] = $value['font_id'];
            $FID['font_size'][] = $value['font_size'];
            $FID['font_color'][] = $value['font_color'];
            $FID['font_color_extra'][] = $value['font_color_extra'];

            // created by Rushik 
            // start get data from db and store in array 
            $FID['sample_image'][] = $value['sample_image'];
            $FID['angle'][] = $value['angle'];
            $FID['sample_text'][] = $value['sample_text'];
            $FID['line_gap'][] = $value['line_gap'];
            $FID['length'][] = $value['length'];
            $FID['uv_percentage'][] = $value['uv_percentage'];
            $FID['print_type'] = 'pdf';
            $FID['is_repeat'][] = $value['is_repeat'];
            $FID['field_sample_text_width'][] = $value['field_sample_text_width'];
            $FID['field_sample_text_vertical_width'][] = $value['field_sample_text_vertical_width'];
            $FID['field_sample_text_horizontal_width'][] = $value['field_sample_text_horizontal_width'];
            // end get data from db and store in array
            $FID['is_mapped'][] = $value['is_mapped'];
            $FID['infinite_height'][] = $value['infinite_height'];
            $FID['include_image'][] = $value['include_image'];
            $FID['grey_scale'][] = $value['grey_scale'];
        }


        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {

            if ($get_file_aws_local_flag->file_aws_local == '1') {
                $target_path = '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $excelfile;
                $copy_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $excelfile;
                $s3_file = \Storage::disk('s3')->get($target_path);
                $s3 = \Storage::disk('public_new');
                $s3->put($target_path, $s3_file);
                $target_path = $copy_path;
            } else {
                $target_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $excelfile;
            }
        } else {
            if ($get_file_aws_local_flag->file_aws_local == '1') {
                $target_path = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile;
                $copy_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile;
                $s3_file = \Storage::disk('s3')->get($target_path);
                $s3 = \Storage::disk('public_new');
                $s3->put($target_path, $s3_file);
                $target_path = $copy_path;
            } else {
                $target_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile;
            }
        }
        // echo  $target_path;die();

        $extension = pathinfo($excelfile, PATHINFO_EXTENSION);

        $inputType = 'Xls';
        if ($extension == 'xlsx' || $extension == 'XLSX') {

            $inputType = 'Xlsx';
        }

        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputType);
        $objPHPExcel = $objReader->load($target_path);
        $sheet = $objPHPExcel->getSheet(0);

        $fonts_array = array('' => '');

        // dd($FID);
        foreach ($FID['font_id'] as $key => $font) {


            if (($font != '' && $font != 'null' && !empty($font)) || ($font == '0')) {

                if ($font != '0') {
                    $fontMaster = FontMaster::select('font_name', 'font_filename', 'font_filename_N', 'font_filename_B', 'font_filename_I', 'font_filename_BI')
                        ->where('id', $font)
                        ->first();
                } else {
                    $fontMaster = FontMaster::select('font_name', 'font_filename', 'font_filename_N', 'font_filename_B', 'font_filename_I', 'font_filename_BI')
                        ->first();
                }
                // dd($fontMaster);
                if ($FID['font_style'][$key] == '') {
                    if ($get_file_aws_local_flag->file_aws_local == '1') {
                        $font_filename = \Config::get('constant.amazone_path') . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_N'];

                        $filename = $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_N'];

                        $font_name[$key] = $fontMaster['font_filename'];
                        if (!\Storage::disk('s3')->has($filename)) {
                            if (!file_exists($font_filename)) {


                                $message = array('type' => 'error', 'message' => $font['font_filename_N'] . ' font not found');
                                echo json_encode($message);
                                exit;
                            }
                        }
                    } else {
                        $font_filename = public_path() . '/' . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_N'];
                        $font_name[$key] = $fontMaster['font_filename'];

                        if (!file_exists($font_filename)) {


                            $message = array('type' => 'error', 'message' => $font['font_filename_N'] . ' font not found');
                            echo json_encode($message);
                            exit;
                        }
                    }
                } else if ($FID['font_style'][$key] == 'B') {
                    if ($get_file_aws_local_flag->file_aws_local == '1') {
                        $font_filename = \Config::get('constant.amazone_path') . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_B'];
                    } else {
                        $font_filename = public_path() . '/' . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_B'];
                    }
                    // echo"<pre>";print_r($font);
                    $exp = explode('.', $fontMaster['font_filename_B']);
                    $font_name[$key] = $exp[0];
                } else if ($FID['font_style'][$key] == 'I') {
                    if ($get_file_aws_local_flag->file_aws_local == '1') {
                        $font_filename = \Config::get('constant.amazone_path') . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_I'];
                    } else {
                        $font_filename = public_path() . '/' . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_I'];
                    }
                } else if ($FID['font_style'][$key] == 'BI') {
                    if ($get_file_aws_local_flag->file_aws_local == '1') {
                        $font_filename = \Config::get('constant.amazone_path') . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_BI'];
                    } else {
                        $font_filename = public_path() . '/' . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_BI'];
                    }
                }
                if ($get_file_aws_local_flag->file_aws_local == '1') {
                    if (!\Storage::disk('s3')->has($filename)) {
                        // if other styles are not present then load normal file
                        $font_filename = \Config::get('constant.amazone_path') . $subdomain[0] . '/backend/canvas/fonts/' . $get_font_data['font_filename_N'];
                        $filename = $subdomain[0] . '/backend/canvas/fonts/' . $get_font_data['font_filename_N'];


                        if (!\Storage::disk('s3')->has($filename)) {
                            $message = array('type' => 'error', 'message' => $font['font_filename_N'] . ' font not found');
                            echo json_encode($message);
                            exit;
                        }
                    }
                } else {

                    if (!file_exists($font_filename)) {

                        $font_filename = public_path() . '/' . $subdomain[0] . '/backend/canvas/fonts/' . $fontMaster['font_filename_N'];

                        if (!file_exists($font_filename)) {


                            $message = array('type' => 'error', 'message' => $font['font_filename_N'] . ' font not found');
                            echo json_encode($message);
                            exit;
                        }
                    }
                }
                $fonts_array[$key] = \TCPDF_FONTS::addTTFfont($font_filename, 'TrueTypeUnicode', '', false);
            }
        }


        $printer_name = $systemConfig['printer_name'];
        $timezone = $systemConfig['timezone'];

        $style2D = array(
            'border' => false,
            'vpadding' => 0,
            'hpadding' => 0,
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );
        $style1Da = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 3
        );
        $style1D = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );

        $ghostImgArr = array();

        $highestColumn = $sheet->getHighestColumn();


        $formula = [];
        foreach ($sheet->getCellCollection() as $cellId) {
            foreach ($cellId as $key1 => $value1) {
                $checkFormula = $sheet->getCell($cellId)->isFormula();
                if ($checkFormula == 1) {
                    $formula[] = $cellId;
                }
            }
        }
        ;

        if (!empty($formula)) {

            $message = array('type' => 'error', 'message' => 'Please remove formula from column', 'cell' => $formula);
            echo json_encode($message);
            exit;
        }

        if (isset($FID['bg_template_id']) && $FID['bg_template_id'] != '') {

            if ($FID['bg_template_id'] == 0) {
                $bg_template_img_generate = '';
                $bg_template_width_generate = $FID['template_width'];
                $bg_template_height_generate = $FID['template_height'];
            } else {
                $get_bg_template_data = BackgroundTemplateMaster::select('image_path', 'width', 'height')->where('id', $FID['bg_template_id'])->first();
                if ($get_file_aws_local_flag->file_aws_local == '1') {
                    $bg_template_img_generate = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.bg_images') . '/' . $get_bg_template_data['image_path'];
                } else {
                    $bg_template_img_generate = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.bg_images') . '/' . $get_bg_template_data['image_path'];
                }

                $bg_template_width_generate = $get_bg_template_data['width'];
                $bg_template_height_generate = $get_bg_template_data['height'];
            }
        } else {
            $bg_template_img_generate = '';
            $bg_template_width_generate = 210;
            $bg_template_height_generate = 297;
        }
        $tmp_path = public_path() . '/backend/images/ghosttemp';

        $tmpDir = app('App\Http\Controllers\admin\TemplateMasterController')->createTemp($tmp_path);

        $log_serial_no = 1;

        if ($template_id == 7) {

            //$sheet->setCellValue('L1','SeQR code');
            $sheet->setCellValue('M1', 'SeQR code');
        } else {
            //$sheet->setCellValue('J1','SeQR code');
            $sheet->setCellValue('K1', 'SeQR code');
        }
        $enrollImage = [];
        // Retrieve the most recent IdCardStatusRequest for the given card_id, ordered by end_index in descending order
        $IdCardStatusRequests = IdCardStatusRequest::where('card_id', $IdCardStatus['id'])
            ->orderBy('end_index', 'desc')
            ->first();

        $limit = 20; // Define the batch limit for processing
        $is_process_complete = 0; // Initialize default process status as incomplete
        $last_iteration = 0;
        // Check if a request already exists
        if (!empty($IdCardStatusRequests)) {
            // Log the existing request details
            Log::info("Existing request found: ", [
                'start_index' => $IdCardStatusRequests->start_index,
                'end_index' => $IdCardStatusRequests->end_index
            ]);

            // Check if processing is complete based on the highest row
            if ($IdCardStatusRequests->start_index >= $highestRow) {
                $start_from_request = $IdCardStatusRequests->start_index;
                $end_from_request = $IdCardStatusRequests->end_index;
                if ($start_from_request > $highestRow) {
                    $end_from_request = $highestRow;
                    $last_iteration = 1; 
                    Log::info("Adjusted end index to cap at the highest row: $end_from_request");
                }else{
                    $is_process_complete = 1; // Mark process as complete
                    Log::info("Process complete: Start index matches the highest row.");
                }
                
                
            } else {
                // Calculate the start and end indices for the next processing batch
                $start_from_request = $IdCardStatusRequests->start_index;
                $end_from_request = $IdCardStatusRequests->end_index;
            }
        } else {
            // Handle case where no previous requests exist
            $start_from_request = 2; // Default start index
            $end_from_request = $start_from_request + $limit; // Calculate the end index based on the limit
            $is_process_complete = 0;

            // Create a new IdCardStatusRequest entry
            $IdCardStatusRequests = new IdCardStatusRequest();
            $IdCardStatusRequests->start_index = $start_from_request;
            $IdCardStatusRequests->card_id = $IdCardStatus['id']; // Assign the correct card ID
            $IdCardStatusRequests->end_index = $end_from_request;
            $IdCardStatusRequests->total_index = $highestRow;
            $IdCardStatusRequests->save();

            // Log the creation of a new request
            Log::info("New IdCardStatusRequest created: ", [
                'start_index' => $start_from_request,
                'end_index' => $end_from_request,
                'total_index' => $highestRow
            ]);
        }

        // Log the final process status
        Log::info("Process status: ", ['is_process_complete' => $is_process_complete]);

        if ($is_process_complete == 0) {
            Log::info("STARTED FOR LOOP  FUNCTION RUNING  $request_number :  $start_from_request to $end_from_request $highestRow");
            // Create the next request only if the process is incomplete
            for ($excel_row = $start_from_request; $excel_row <= $end_from_request; $excel_row++) {

                $rowData = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);
                if (!empty($rowData[0][0])) {
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

                    if (isset($FID['bg_template_id']) && $FID['bg_template_id'] != '') {
                        $pdf->Image($bg_template_img_generate, 0, 0, $bg_template_width_generate, $bg_template_height_generate, "JPG", '', 'R', true);
                    }
                    $serial_no = $rowData[0][2];
                    if ($get_file_aws_local_flag->file_aws_local == '1') {
                        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                            $file_pointer_jpg = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.jpg';

                            $file_pointer_png = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.png';
                        } else {
                            $file_pointer_jpg = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.jpg';

                            $file_pointer_png = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.png';
                        }
                    } else {
                        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                            $file_pointer_jpg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.jpg';

                            $file_pointer_png = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.png';
                        } else {
                            $file_pointer_jpg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.jpg';

                            $file_pointer_png = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.png';
                        }
                    }


                    $count = count($FID['mapped_name']);

                    for ($extra_fields = 0; $extra_fields < $count; $extra_fields++) {


                        if (isset($FID['security_type'][0]) || isset($FID['security_type'][1])) {

                            array_push($FID['security_type'], $FID['security_type'][0]);
                            array_push($FID['security_type'], $FID['security_type'][1]);
                            unset($FID['security_type'][0]);
                            unset($FID['security_type'][1]);

                        }
                        $security_type = $FID['security_type'][$extra_fields + 2];

                        if (isset($FID['x_pos'][0]) || isset($FID['x_pos'][1])) {

                            array_push($FID['x_pos'], $FID['x_pos'][0]);
                            array_push($FID['x_pos'], $FID['x_pos'][1]);
                            unset($FID['x_pos'][0]);
                            unset($FID['x_pos'][1]);

                        }
                        $x = $FID['x_pos'][$extra_fields + 2];

                        if (isset($FID['y_pos'][0]) || isset($FID['y_pos'][1])) {

                            array_push($FID['y_pos'], $FID['y_pos'][0]);
                            array_push($FID['y_pos'], $FID['y_pos'][1]);
                            unset($FID['y_pos'][0]);
                            unset($FID['y_pos'][1]);

                        }
                        $y = $FID['y_pos'][$extra_fields + 2];


                        $print_serial_no = $this->nextPrintSerial();

                        if (isset($FID['field_position'][0]) || isset($FID['field_position'][1])) {

                            array_push($FID['field_position'], $FID['field_position'][0]);
                            array_push($FID['field_position'], $FID['field_position'][1]);
                            unset($FID['field_position'][0]);
                            unset($FID['field_position'][1]);

                        }
                        //print_r($FID['field_position']);
                        $field_position = $FID['field_position'][$extra_fields + 2];


                        if (isset($FID['font_color'][0]) || isset($FID['font_color'][1])) {

                            array_push($FID['font_color'], $FID['font_color'][0]);
                            array_push($FID['font_color'], $FID['font_color'][1]);
                            unset($FID['font_color'][0]);
                            unset($FID['font_color'][1]);

                        }
                        $font_color_hex = $FID['font_color'][$extra_fields + 2];


                        if ($font_color_hex != '') {


                            if ($font_color_hex == "0") {

                                $r = 0;
                                $g = 0;
                                $b = 0;

                            } else {

                                list($r, $g, $b) = array(
                                    $font_color_hex[0] . $font_color_hex[1],
                                    $font_color_hex[2] . $font_color_hex[3],
                                    $font_color_hex[4] . $font_color_hex[5],
                                );
                                $r = hexdec($r);
                                $g = hexdec($g);
                                $b = hexdec($b);
                            }
                        }
                        ;


                        if (isset($fonts_array[$extra_fields + 2])) {

                            $font = $fonts_array[$extra_fields + 2];
                        }

                        if (isset($FID['font_size'][0]) || isset($FID['font_size'][1])) {

                            array_push($FID['font_size'], $FID['font_size'][0]);
                            array_push($FID['font_size'], $FID['font_size'][1]);
                            unset($FID['font_size'][0]);
                            unset($FID['font_size'][1]);

                        }
                        $font_size = $FID['font_size'][$extra_fields + 2];


                        if (isset($FID['font_style'][0]) || isset($FID['font_style'][1])) {

                            array_push($FID['font_style'], $FID['font_style'][0]);
                            array_push($FID['font_style'], $FID['font_style'][1]);
                            unset($FID['font_style'][0]);
                            unset($FID['font_style'][1]);

                        }
                        $font_style = $FID['font_style'][$extra_fields + 2];



                        if (isset($FID['width'][0]) || isset($FID['width'][1])) {

                            array_push($FID['width'], $FID['width'][0]);
                            array_push($FID['width'], $FID['width'][1]);
                            unset($FID['width'][0]);
                            unset($FID['width'][1]);

                        }
                        $width = $FID['width'][$extra_fields + 2];


                        if (isset($FID['height'][0]) || isset($FID['height'][1])) {

                            array_push($FID['height'], $FID['height'][0]);
                            array_push($FID['height'], $FID['height'][1]);
                            unset($FID['height'][0]);
                            unset($FID['height'][1]);

                        }
                        $height = $FID['height'][$extra_fields + 2];



                        $str = '';
                        if (isset($rowData[0][$extra_fields]))
                            $str = $rowData[0][$extra_fields];


                        if (isset($FID['text_justification'][0]) || isset($FID['text_justification'][1])) {

                            array_push($FID['text_justification'], $FID['text_justification'][0]);
                            array_push($FID['text_justification'], $FID['text_justification'][1]);
                            unset($FID['text_justification'][0]);
                            unset($FID['text_justification'][1]);

                        }
                        $text_align = $FID['text_justification'][$extra_fields + 2];



                        if ($field_position == 3) {
                            $str = $rowData[0][1];
                        } else if ($field_position == 4) {

                            $str = $rowData[0][2];
                        } else if ($field_position == 5) {

                            $str = $rowData[0][3];
                        } else if ($field_position == 6) {

                            $str = $rowData[0][4];
                        } else if ($field_position == 7) {
                            $str = $rowData[0][5];
                        } else if ($field_position == 8) {

                            $str = $rowData[0][6];
                        } else if ($field_position == 9) {

                            $str = $rowData[0][7];
                        } else if ($field_position == 10) {
                            $str = $rowData[0][8];

                        } else if ($field_position == 12) {
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
                                $str = $rowData[0][0]; //updated by mandar
                                /* echo "----";
                               print_r($excel_row);
        
                                echo "----";
                                echo $str;
                                echo "----";
                                echo '<br>';*/
                                $codeContents = strtoupper(md5($str . $dt));

                                if (!empty($str)) {

                                    if ($template_id == 7) {
                                        //echo "a";
                                        //$sheet->setCellValue('L'.$excel_row,$codeContents);
                                        $sheet->setCellValue('M' . $excel_row, $codeContents);
                                    } else {
                                        //echo "b";
                                        // $sheet->setCellValue('J'.$excel_row,$codeContents);
                                        $sheet->setCellValue('K' . $excel_row, $codeContents);
                                    }
                                }

                                $pngAbsoluteFilePath = "$tmpDir/$codeContents.png";

                                QrCode::format('png')->size(200)->generate($codeContents, $pngAbsoluteFilePath);
                                $QR = imagecreatefrompng($pngAbsoluteFilePath);

                                // $QR_width = imagesx($QR);
                                // $QR_height = imagesy($QR);

                                // $logo_qr_width = $QR_width / 3;

                                // imagepng($QR, $pngAbsoluteFilePath);
                                // Crop white space perfectly
                                $this->cropQrImage($pngAbsoluteFilePath);

                                $pdf->SetAlpha(1);
                                $pdf->Image($pngAbsoluteFilePath, $x, $y, 19, 19, "PNG", '', 'R', true);
                                break;

                            case 'ID Barcode':
                                break;
                            case 'Normal':


                                if ($FID['template_id'] == 6) {

                                    if ($field_position == 8) {
                                        $cell = $sheet->getCellByColumnAndRow(9, $excel_row);

                                        $str = $cell->getValue();
                                        if (is_numeric($str) && \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($str)) {

                                            $val = $cell->getValue();
                                            $xls_date = $val;

                                            $unix_date = ($xls_date - 25569) * 86400;

                                            $xls_date = 25569 + ($unix_date / 86400);
                                            $unix_date = ($xls_date - 25569) * 86400;
                                            $str = date("d-m-Y", $unix_date);
                                        }
                                    }

                                    if ($field_position == 10) {
                                        $cell = $sheet->getCellByColumnAndRow(10, $excel_row);

                                        $str = $cell->getValue();
                                    }
                                } else if ($FID['template_id'] == 1 || $FID['template_id'] == 2 || $FID['template_id'] == 3 || $FID['template_id'] == 4 || $FID['template_id'] == 5||$FID['template_id'] == 8||$FID['template_id'] == 9||$FID['template_id'] == 10) {

                                    if ($field_position == 10) {

                                        $cell = $sheet->getCellByColumnAndRow(9, $excel_row);

                                        $str = $cell->getValue();
                                        if (is_numeric($str) && \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($str)) {

                                            $val = $cell->getValue();
                                            $xls_date = $val;

                                            $unix_date = ($xls_date - 25569) * 86400;

                                            $xls_date = 25569 + ($unix_date / 86400);
                                            $unix_date = ($xls_date - 25569) * 86400;
                                            $str = date("d-m-Y", $unix_date);
                                        }
                                    }

                                    if ($field_position == 12) {
                                        $cell = $sheet->getCellByColumnAndRow(10, $excel_row);

                                        $str = $cell->getValue();
                                    }
                                } else {

                                    if ($field_position == 10) {

                                        $cell = $sheet->getCellByColumnAndRow(9, $excel_row);

                                        $str = $cell->getValue();
                                        if (is_numeric($str) && \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($str)) {

                                            $val = $cell->getValue();
                                            $xls_date = $val;

                                            $unix_date = ($xls_date - 25569) * 86400;

                                            $xls_date = 25569 + ($unix_date / 86400);
                                            $unix_date = ($xls_date - 25569) * 86400;
                                            $str = date("d-m-Y", $unix_date);
                                        }
                                    }


                                }


                                $pdf->SetAlpha(1);
                                $pdf->SetTextColor($r, $g, $b);
                                $pdf->SetFont($font, $font_style, $font_size, '', false);
                                $pdf->SetXY($x, $y);
                                $pdf->Cell($width, $height, $str, 0, false, $text_align);
                                break;

                            case 'Dynamic Image':


                                $pdf->SetAlpha(1);
                                $excel_column_row = $sheet->getCellByColumnAndRow(2, $excel_row);
                                $enrollValue = $excel_column_row->getValue();

                                $serial_no = trim($serial_no);

                                if ($get_file_aws_local_flag->file_aws_local == '1') {
                                    if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                                        $image_jpg = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.jpg';

                                        $image_png = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.png';
                                    } else {
                                        $image_jpg = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.jpg';

                                        $image_png = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.png';
                                    }
                                } else {
                                    if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                                        $image_jpg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.jpg';

                                        $image_png = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.png';
                                    } else {
                                        $image_jpg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.jpg';

                                        $image_png = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.png';
                                    }
                                }




                                $exists = $this->check_file_exist($image_jpg);

                                if ($exists) {
                                    $pdf->image($image_jpg, $x, $y, $width / 3, $height / 3, "", "", 'L', true, 3600);
                                } else {


                                    $exists = $this->check_file_exist($image_png);
                                    if ($exists) {
                                        $pdf->image($image_png, $x, $y, $width / 3, $height / 3, "", "", 'L', true, 3600);
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
                    $admin_id = DB::table('admin_table')->where('id',1)->get()->toArray();
                    $template_name = $FID['template_name'];
                    $certName = str_replace("/", "_", $serial_no) . ".pdf";
                    $myPath = public_path() . '/backend/temp_pdf_file';
                    $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');
                    $student_table_id = $this->addCertificate($serial_no, $certName, $dt, $FID['template_id'], $admin_id,$domain);



                    $username = $admin_id[0]->username;
                    date_default_timezone_set('Asia/Kolkata');

                    $content = "#" . $log_serial_no . " serial No :" . $serial_no . PHP_EOL;
                    $date = date('Y-m-d H:i:s') . PHP_EOL;


                    if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                        $file_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id;
                    } else {
                        $file_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id;
                    }
                    $fp = fopen($file_path . '/' . $withoutExt . ".txt", "a");
                    fwrite($fp, $content);
                    fwrite($fp, $date);

                    $print_datetime = date("Y-m-d H:i:s");
                    $print_count = $this->getPrintCount($serial_no);
                    $printer_name = /*'HP 1020';*/ $printer_name;

                    $this->addPrintDetails($username, $print_datetime, $printer_name, $print_count, $print_serial_no, $serial_no, $template_name, $admin_id, $student_table_id);
                    $log_serial_no++;

                    $excel_column_row = $sheet->getCellByColumnAndRow(3, $excel_row);
                    $enroll_value = $excel_column_row->getValue();

                    if ($get_file_aws_local_flag->file_aws_local == '1') {
                        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                            $imageNameJpg = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $enroll_value . '.jpg';

                            $imageNamePng = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $enroll_value . '.png';
                        } else {
                            $imageNameJpg = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.jpg';

                            $imageNamePng = \Config::get('constant.amazone_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.png';
                        }
                    } else {
                        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                            $imageNameJpg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $enroll_value . '.jpg';

                            $imageNamePng = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $enroll_value . '.png';
                        } else {
                            $imageNameJpg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.jpg';

                            $imageNamePng = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.png';
                        }
                    }

                    //echo $imageNamePng;
                    $imageName = $enroll_value . '.jpg';

                    /*if(file_exists($imageNamePng)){
        
                        $imageName = $enroll_value.'.png';
                    }*/


                    if ($this->check_file_exist($imageNamePng)) {

                        $imageName = $enroll_value . '.png';
                    }
                    array_push($enrollImage, $imageName);



                }else{
                    Log::info("No Data found on $excel_row");

                }
               
            }
            $IdCardStatusRequests->start_index = $end_from_request;
            if( $last_iteration == 0 ){

                $IdCardStatusRequests->end_index = ($end_from_request + $limit);

            }else{
                $IdCardStatusRequests->end_index = $end_from_request;
            }
            $IdCardStatusRequests->save();
            Log::info("ENDED FOR LOOP FOR INDEX $excel_row FUNCTION RUNING  $request_number :  $start_from_request to $end_from_request");

        }
        if ($is_process_complete == 1) {
            Log::info("Zip creation started for $request_number");
            // if($subdomain[0]=='test'){
            CoreHelper::rrmdir($tmpDir);
            //}

            //print_r($enrollImage);
            //exit;

            $this->generatePdfZip($request_number, $template_id,$domain);

            $created_on = date('Y-m-d H:i:s');
            $highestRecord = $highestRow - 1;

            // $changeStatus = IdCardStatus::where('request_number', $request_number)->update(['status' => 'Inprogress']);

            $mail_data = [

                'req_no' => $request_number,
                'updated_on' => $created_on,
                'template_name' => $template_name,
                'quantity' => $highestRecord
            ];

            // $user_email = ['dev1@scube.net.in'];
            $user_email = ['dtp@scube.net.in'];
            // $cc_email = [];
            $cc_email = ['dev12@scube.net.in'];
            $mail_subject = "TPSDI " . $request_number . " Request for " . $highestRecord . " " . $template_name . " is ready.";
            $mail_view = 'mail.processPdf';

            $response = Mail::send($mail_view, ['mail_data' => $mail_data], function ($message) use ($user_email, $mail_subject, $cc_email) {
                $message->to($user_email);
                $message->cc($cc_email);
                $message->subject($mail_subject);
                $message->from('info@seqrdoc.com');
            });
            if (count(Mail::failures()) > 0) {
                
                Log::error('Email sending failed to: ' . implode(', ', Mail::failures()));
            } else {
                
                $changeStatus = IdCardStatus::where('request_number', $request_number)->update(['status' => 'Inprogress']);
            }
            
            if ($get_file_aws_local_flag->file_aws_local == '1') {
                unlink(public_path() . $save_path . '/' . $excel_filename . '_processed.' . $excel_extension);
                unlink(public_path() . $save_path . '/' . $zip_file_name);
                unset($fp);
                unlink(public_path() . $save_path . '/' . $excel_filename . ".txt");
                unlink($target_path);
                \File::deleteDirectory(public_path() . $save_path);
            }

            Log::info("Zip creation Ended for $request_number");
            Log::info("Status updated successfully for $request_number");
            $message = array('type' => 'success', 'message' => 'Status updated successfully');
            $this->index();
        } else {
             $this->processPdf($input,$domain);
            $message = array('type' => 'pending');
             Log::info("Request is inprocess for $request_number");
        }
        echo json_encode($message);
        exit;


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

    function cropQrImage($imagePath, $outputPath = null, $tolerance = 10)
    {
        $img = imagecreatefrompng($imagePath);
        $width = imagesx($img);
        $height = imagesy($img);

        $minX = $width;
        $minY = $height;
        $maxX = 0;
        $maxY = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                $colors = imagecolorsforindex($img, $rgba);

                // Check if pixel is NOT white or transparent
                if (!($colors['red'] >= (255 - $tolerance) && $colors['green'] >= (255 - $tolerance) && $colors['blue'] >= (255 - $tolerance)) && $colors['alpha'] < 127) {
                    if ($x < $minX) $minX = $x;
                    if ($x > $maxX) $maxX = $x;
                    if ($y < $minY) $minY = $y;
                    if ($y > $maxY) $maxY = $y;
                }
            }
        }

        if ($maxX < $minX || $maxY < $minY) {
            // Image is empty or all white
            return false;
        }

        $newWidth = $maxX - $minX + 1;
        $newHeight = $maxY - $minY + 1;

        $cropped = imagecreatetruecolor($newWidth, $newHeight);
        imagesavealpha($cropped, true);
        $transparency = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
        imagefill($cropped, 0, 0, $transparency);

        imagecopy($cropped, $img, 0, 0, $minX, $minY, $newWidth, $newHeight);

        if ($outputPath === null) {
            $outputPath = $imagePath;
        }

        imagepng($cropped, $outputPath);
        imagedestroy($img);
        imagedestroy($cropped);

        return true;
    }
    public function check_file_exist($url)
    {
        $handle = @fopen($url, 'r');
        if (!$handle) {
            return false;
        } else {
            return true;
        }
    }

    public function addCertificate($serial_no, $certName, $dt, $template_id, $admin_id,$domain)
    {
       
        $subdomain = explode('.', $domain);
        $file1 = public_path() . '/backend/temp_pdf_file/' . $certName;

        $file2 = public_path() . '/' . $subdomain[0] . '/backend/pdf_file/' . $certName;

        if ($subdomain[0] == 'tpsdi') {
            $source = \Config::get('constant.directoryPathBackward') . "\\backend\\temp_pdf_file\\" . $certName;//$file1
            $output = \Config::get('constant.directoryPathBackward') . $subdomain[0] . "\\backend\\pdf_file\\" . $certName;
            CoreHelper::compressPdfFile($source, $output);
        } else {
            copy($file1, $file2);
        }

        @unlink($file1);
        // dd($admin_id);
        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id[0]->id;
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();

        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path() . '/backend/qr';
        $key = strtoupper(md5($serial_no . $dt));
        $codeContents = $key;
        $fileName = $key . '.png';

        $urlRelativeFilePath = 'qr/' . $fileName;
        $auth_site_id = 248;//change to 248
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
        // Mark all previous records of same serial no to inactive if any
        // dd($ses_id,$admin_id);
        // dd(['serial_no' => $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
            $resultu = SbStudentTable::where('serial_no', $serial_no)->update(['status' => '0']);
            // Insert the new record

            $result = SbStudentTable::create(['serial_no' => $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        } else {
            $resultu = StudentTable::where('serial_no', $serial_no)->update(['status' => '0']);
            // Insert the new record

            $result = StudentTable::create(['serial_no' => $serial_no, 'certificate_filename' => $certName, 'template_id' => $template_id, 'key' => $key, 'path' => $urlRelativeFilePath, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id]);
        }

        return $result['id'];
    }
    public function getPrintCount($serial_no)
    {
        $numCount = PrintingDetail::select('id')->where('sr_no', $serial_no)->count();
        return $numCount + 1;
    }

    public function addPrintDetails($username, $print_datetime, $printer_name, $printer_count, $print_serial_no, $sr_no, $template_name, $admin_id, $student_table_id)
    {
        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id[0]->id;
        $auth_site_id = 248;//change to 248
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
            // Insert the new record
            $result = SbPrintingDetail::create(['username' => $username, 'print_datetime' => $print_datetime, 'printer_name' => $printer_name, 'print_count' => $printer_count, 'print_serial_no' => $print_serial_no, 'sr_no' => $sr_no, 'template_name' => $template_name, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'publish' => 1]);
        } else {
            // Insert the new record
            $result = PrintingDetail::create(['username' => $username, 'print_datetime' => $print_datetime, 'printer_name' => $printer_name, 'print_count' => $printer_count, 'print_serial_no' => $print_serial_no, 'sr_no' => $sr_no, 'template_name' => $template_name, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'publish' => 1, 'student_table_id' => $student_table_id]);
        }
    }

    public function generatePdfZip($request_number, $template_id,$domain)
    {
        try {
        $checkUploadedFileOnAwsOrLocal = new CheckUploadedFileOnAwsORLocalService();
        // $this->findMissedSerialNo();
        // exit;
        $subdomain = explode('.', $domain);
        // $request_number = "R/24/1278";

        //$request_number = $request['req_number'];
        $IdCardStatus = IdCardStatus::where('request_number', $request_number)
            ->first();

        //print_r($IdCardStatus);

        //$IdCardStatus['template_name']="Training participation 1 sided";
        $template_name = $IdCardStatus['template_name'];
        $excelfile = $IdCardStatus['excel_sheet'];
        //$excelfile = "TPSDI_Job_D245_20240217145502__.xlsx";
        $highestRow = $IdCardStatus['rows'] + 1;
        $status = $IdCardStatus['status'];



        //$excelfile="-SHE_TPSDI_Job_B146_20211217122041_processed.xlsx";
        // $target_path=public_path().'/'.$subdomain[0].'/backend/'.$excelfile;
        $target_path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excelfile;

        // echo $excelfile;

        $extension = pathinfo($excelfile, PATHINFO_EXTENSION);

        $inputType = 'Xls';
        if ($extension == 'xlsx' || $extension == 'XLSX') {

            $inputType = 'Xlsx';
        }
        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputType);
        $objPHPExcel = $objReader->load($target_path);
        $sheet = $objPHPExcel->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();



        if ($template_id == 7) {

            //$sheet->setCellValue('L1','SeQR code');
            $sheet->setCellValue('M1', 'SeQR code');
        } else {
            //$sheet->setCellValue('J1','SeQR code');
            $sheet->setCellValue('K1', 'SeQR code');
        }

        $file_not_exists = [];
        $enrollImage = [];
        $i = 1;
        // $highestRow = 500;
            

        for ($excel_row = 2; $excel_row <= $highestRow; $excel_row++) {

            $rowData = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);

            $rowData = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);

            // print_r($rowData[0]);
            // exit;

            
            $excel_column_row = $sheet->getCellByColumnAndRow(3, $excel_row);

            $enroll_value = $excel_column_row->getValue();



            $dt = date("_ymdHis");

            $str = $rowData[0][0]; //updated by mandar
            
            // echo $str;die();
            
            //exit;
            if (!empty($str)) {
                
               
                if ($template_id == 7) {

                    $studentData = StudentTable::where('serial_no', $str)
                        ->orderBy('id', 'desc')->first();
                    // print_r($studentData);
                    // exit;
                    $codeContents = $output->data."\n\n".$studentData->key;
                    // exit;
                    //echo "a";
                    //$sheet->setCellValue('L'.$excel_row,$codeContents);
                    $sheet->setCellValue('M' . $excel_row, $codeContents);
                } else {
            // dd($studentData);
                    $studentData = StudentTable::where('serial_no', $str)
                        ->orderBy('id', 'desc')->first();
                    $codeContents = $output->data."\n\n".$studentData->key;
                    //echo "b";
                    // $sheet->setCellValue('J'.$excel_row,$codeContents);
                    $sheet->setCellValue('K' . $excel_row, $codeContents);
                }
                // echo "<pre>";print_r($excel_row);
                // echo "<pre>";print_r($codeContents);
                // exit;
            }
            if(!empty($enroll_value)){
                // dd($studentData);

            $imageName = $enroll_value . '.jpg';


            $imageNamePng = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.png';
            $imageNameJpg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.jpg';
            $imageNameJpeg = \Config::get('constant.local_base_path') . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $enroll_value . '.jpeg';
            
            //echo'<br>';   
            if (file_exists($imageNamePng)) {

                $imageName = $enroll_value . '.png';

            } elseif (file_exists($imageNameJpg)) {
                $imageName = $enroll_value . '.jpg';
            } elseif (file_exists($imageNameJpeg)) {
                $imageName = $enroll_value . '.jpeg';
            }
           // echo $imageName."<br>";
            array_push($enrollImage, $imageName);

            $i++;
            }else{
                break;
            }

        }
        
// dd($enrollImage);


        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, "Xlsx");
        $pathinfo = pathinfo($excelfile);

        $excel_filename = $pathinfo['filename'];
        $excel_extension = $pathinfo['extension'];

        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
            $save_path = '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id;
        } else {
            $save_path = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id;
        }

        $objWriter->save(public_path() . $save_path . '/' . $template_name . '_' . $excel_filename . '_processed.' . $excel_extension);

        //print_r($file_not_exists);
        $pathinfo = pathinfo($excelfile);
        $excel_filename = $pathinfo['filename'];
        $excel_extension = $pathinfo['extension'];

        $excel_request_no = explode('/', $request_number);
        $zip_file_name = $excel_request_no[0] . '_' . $excel_request_no[1] . '_' . $excel_request_no[2] . '.zip';


        \Log::info('Enroll Image count: ' . count($enrollImage));

        $zip = new \ZipArchive;

        if ($zip->open(public_path() . $save_path . '/' . $zip_file_name, \ZipArchive::CREATE) === TRUE) {

            $zip->addFile(public_path() . $save_path . '/' . $template_name . '_' . $excel_filename . '_processed.' . $excel_extension, $template_name . '_' . $excel_filename . '_processed.' . $excel_extension);

            foreach ($enrollImage as $key => $value) {
                $zip->addfile(public_path() . $save_path . '/' . $value, $value);
            }
            $zip->close();
        }
        if ($get_file_aws_local_flag->file_aws_local == '1') {
            \Storage::disk('s3')->put($save_path . '/' . $zip_file_name, file_get_contents(public_path() . $save_path . '/' . $zip_file_name), 'public');
            \Storage::disk('s3')->put($save_path . '/' . $withoutExt . ".txt", file_get_contents($file_path . '/' . $withoutExt . ".txt"), 'public');
        }
        $created_on = date('Y-m-d H:i:s');
        $highestRecord = $highestRow - 1;

        return true;
        } catch (Exception $e) {
                dd($e);
            }
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
}
