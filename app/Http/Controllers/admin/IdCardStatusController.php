<?php
// Author : Rushik Joshi
// Date : 21/12/2019
// use for generate pdf of id cards and update status of id cards
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\models\IdCardStatusRequest;
use Illuminate\Http\Request;
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
use DB;
use Mail;
use TCPDF;
use QrCode;
use Auth;
use File;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use Illuminate\Support\Facades\Log;
use App\Helpers\CoreHelper;
class IdCardStatusController extends Controller
{
    // listing of id card status with their status
    public function index(Request $request, CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {



        if ($request->ajax()) {

            $where_str = "1 = ?";
            $where_params = array(1);

            if (!empty($request->input('sSearch'))) {
                $search = $request->input('sSearch');
                $where_str .= " and (id_card_status.template_name like \"%{$search}%\""
                    . " or excel_sheet like \"%{$search}%\""
                    . " or request_number like \"%{$search}%\""
                    . " or id_card_status.rows like \"%{$search}%\""
                    . " or id_card_status.status like \"%{$search}%\""
                    . " or updated_on like \"%{$search}%\""
                    . " or admin_table.username like \"%{$search}%\""
                    . ") ";
            }

            $status = $request->get('status');

            if ($status == 1 || $status == '1') {
                $status = 1;
                $where_str .= " and (id_card_status.status != 'Acknowledged')";
            } else if ($status == 0 || $status == '0') {
                $status = 0;
                $where_str .= " and (id_card_status.status= 'Acknowledged')";
            }

            $auth_site_id = Auth::guard('admin')->user()->site_id;
            //for serial number
            DB::statement(DB::raw('set @rownum=0'));
            $columns = [DB::raw('@rownum  := @rownum  + 1 AS rownum'), 'request_number', 'id_card_status.template_name', 'excel_sheet', 'rows', 'id_card_status.status', 'admin_table.username', 'updated_on', 'created_on', 'template_master.id'];


            $id_card_status_count = IdCardStatus::select($columns)
                ->leftjoin('template_master', 'template_master.template_name', 'id_card_status.template_name')
                ->leftjoin('admin_table', 'admin_table.id', 'id_card_status.uploaded_by')
                ->whereRaw($where_str, $where_params)
                ->count();

            $id_card_status_list = IdCardStatus::select($columns)
                ->leftjoin('template_master', 'template_master.template_name', 'id_card_status.template_name')
                ->leftjoin('admin_table', 'admin_table.id', 'id_card_status.uploaded_by')
                ->whereRaw($where_str, $where_params);

            if ($request->get('iDisplayStart') != '' && $request->get('iDisplayLength') != '') {
                $id_card_status_list = $id_card_status_list->take($request->input('iDisplayLength'))
                    ->skip($request->input('iDisplayStart'));
            }

            if ($request->input('iSortCol_0')) {
                $sql_order = '';
                for ($i = 0; $i < $request->input('iSortingCols'); $i++) {
                    $column = $columns[$request->input('iSortCol_' . $i)];
                    if (false !== ($index = strpos($column, ' as '))) {
                        $column = substr($column, 0, $index);
                    }
                    $id_card_status_list = $id_card_status_list->orderBy($column, $request->input('sSortDir_' . $i));
                }
            }
            $id_card_status_list = $id_card_status_list->get();

            $response['iTotalDisplayRecords'] = $id_card_status_count;
            $response['iTotalRecords'] = $id_card_status_count;
            $response['sEcho'] = intval($request->input('sEcho'));
            $response['aaData'] = $id_card_status_list->toArray();

            return $response;
        }
        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
        $file_aws_local = $get_file_aws_local_flag['file_aws_local'];

        $auth_site_id = \Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
        $config = $systemConfig['sandboxing'];
        return view('admin.idcardStatus.index', compact('file_aws_local', 'config'));
    }
    public function revokeRequest(Request $request)
    {

        $request_number = $request['req_number'];
        $updated_on = date('Y-m-d H:i:s');

        $IdCardStatus = IdCardStatus::select('rows', 'template_name')
            ->where('request_number', $request_number)
            ->first();

        $rows = $IdCardStatus['rows'];
        $template_name = $IdCardStatus['template_name'];
        $res = IdCardStatus::where('request_number', $request_number)->delete();

        $mail_data = [
            'req_no' => $request_number,
            'updated_on' => $updated_on,
            'template_name' => $template_name,
            'rows' => $rows
        ];
        $user_email = ['tester@scube.net.in','tester2@scube.net.in', 'ankit@scube.net.in', 'asdawson@tatapower.com'];
        $cc_email = ['dtp@scube.net.in','dev12@scube.net.in'];
        $mail_subject = "TPSDI " . $request_number . " Revoked request for " . $rows . " " . $template_name . ".";
        $mail_view = 'mail.revokeMail';

        Mail::send($mail_view, ['mail_data' => $mail_data], function ($message) use ($user_email, $mail_subject, $cc_email) {
            $message->to($user_email);
            $message->cc($cc_email);
            $message->subject($mail_subject);
            $message->from('info@seqrdoc.com');
        });


        if ($res) {

            $message = array('type' => 'success', 'message' => $request_number . ' revoked successfully');
            echo json_encode($message);
            exit;
        }
    }
    public function updateStatusToAcknowledge(Request $request)
    {

        $request_number = $request['req_number'];

        $changeStatus = IdCardStatus::where('request_number', $request_number)->update(['status' => 'Acknowledged']);

        if ($changeStatus) {

            $message = array('type' => 'success', 'message' => $request_number . ' Acknowledged successfully');
            echo json_encode($message);
            exit;
        }
    }
    public function updateStatusToComplete(Request $request)
    {


        $request_number = $request['req_number'];
        $updated_on = date('Y-m-d H:i:s');

        $IdCardStatus = IdCardStatus::select('rows', 'template_name', 'id')
            ->where('request_number', $request_number)
            ->first();

        $rows = $IdCardStatus['rows'];
        $template_name = $IdCardStatus['template_name'];
        $mail_data = [

            'req_no' => $request_number,
            'updated_on' => $updated_on,
            'template_name' => $template_name,
            'rows' => $rows
        ];
        $user_email = ['tester@scube.net.in','tester2@scube.net.in', 'ankit@scube.net.in', 'asdawson@tatapower.com'];
        $cc_email = ['dtp@scube.net.in','dev12@scube.net.in'];

        $mail_subject = "TPSDI " . $request_number . " Dispatch of " . $rows . " " . $template_name . " is initiated.";
        $mail_view = 'mail.updateId';

        Mail::send($mail_view, ['mail_data' => $mail_data], function ($message) use ($user_email, $mail_subject, $cc_email) {
            $message->to($user_email);
            $message->cc($cc_email);
            $message->subject($mail_subject);
            $message->from('info@seqrdoc.com');
        });

        $changeStatus = IdCardStatus::where('request_number', $request_number)->update(['status' => 'Complete']);

        $message = array('type' => 'success', 'message' => 'Status updated successfully');
        echo json_encode($message);
        exit;
    }
    public function processPdf(Request $request, CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {

        ini_set('max_execution_time', 7200);
        ini_set('max_input_time', 7200);

        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $systemConfig = SystemConfig::first();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $request_number = $request['req_number'];
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
        // echo $excelfile;

        $extension = pathinfo($excelfile, PATHINFO_EXTENSION);

        $inputType = 'Xls';
        if ($extension == 'xlsx' || $extension == 'XLSX') {

            $inputType = 'Xlsx';
        }

        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputType);
        $objPHPExcel = $objReader->load($target_path);
        $sheet = $objPHPExcel->getSheet(0);

        $fonts_array = array('' => '');


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
                    $exp = explode('.', $font['font_filename_B']);
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

                                $QR_width = imagesx($QR);
                                $QR_height = imagesy($QR);

                                $logo_qr_width = $QR_width / 3;

                                imagepng($QR, $pngAbsoluteFilePath);

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
                    $admin_id = \Auth::guard('admin')->user()->toArray();
                    $template_name = $FID['template_name'];
                    $certName = str_replace("/", "_", $serial_no) . ".pdf";
                    $myPath = public_path() . '/backend/temp_pdf_file';
                    $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');
                    $student_table_id = $this->addCertificate($serial_no, $certName, $dt, $FID['template_id'], $admin_id);



                    $username = $admin_id['username'];
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

            $this->generatePdfZip($request, $request_number, $template_id);
            $created_on = date('Y-m-d H:i:s');
            $highestRecord = $highestRow - 1;

            $changeStatus = IdCardStatus::where('request_number', $request_number)->update(['status' => 'Inprogress']);
            $mail_data = [

                'req_no' => $request_number,
                'updated_on' => $created_on,
                'template_name' => $template_name,
                'quantity' => $highestRecord
            ];

            $user_email = ['tester@scube.net.in','tester2@scube.net.in'];
            $cc_email = ['dev12@scube.net.in','dtp@scube.net.in'];
            $mail_subject = "TPSDI " . $request_number . " Request for " . $highestRecord . " " . $template_name . " is ready.";
            $mail_view = 'mail.processPdf';

            $response = Mail::send($mail_view, ['mail_data' => $mail_data], function ($message) use ($user_email, $mail_subject, $cc_email) {
                $message->to($user_email);
                $message->cc($cc_email);
                $message->subject($mail_subject);
                $message->from('info@seqrdoc.com');
            });
            // Log::info($response);
            if ($get_file_aws_local_flag->file_aws_local == '1') {
                unlink(public_path() . $save_path . '/' . $excel_filename . '_processed.' . $excel_extension);
                unlink(public_path() . $save_path . '/' . $zip_file_name);
                unset($fp);
                unlink(public_path() . $save_path . '/' . $excel_filename . ".txt");
                unlink($target_path);
                \File::deleteDirectory(public_path() . $save_path);
            }

            Log::info("Zip creation Ended for $request_number");
            $message = array('type' => 'success', 'message' => 'Status updated successfully');
        } else {
            $message = array('type' => 'pending');
        }
        echo json_encode($message);
        exit;


    }

    public function checkImage(Request $request, CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {

        $auth_site_id = Auth::guard('admin')->user()->site_id;


        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
        $systemConfig = SystemConfig::where('site_id', $auth_site_id)->first();
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $request_number = $request['req_number'];
        $IdCardStatus = IdCardStatus::where('request_number', $request_number)
            ->first();

        $excel = $IdCardStatus['excel_sheet'];
        $template_name = $IdCardStatus['template_name'];
        $template_id = TemplateMaster::where(
            'template_name',
            $template_name
        )->value('id');
        $filename = $newflname = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $excel;
        $field_data = FieldMaster::where('template_id', $template_id)->get()->toArray();
        $FID = [];
        foreach ($field_data as $frow) {
            $FID['mapped_name'][] = $frow['mapped_name'];
        }
        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
            $path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id;
        } else {
            $path = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id;
        }
        // dd($template_name);
        if ($get_file_aws_local_flag->file_aws_local == '1') {
            if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                $aws_path = '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id;
            } else {
                $aws_path = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id;
            }
        }


        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $rowData = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE);
        if (file_exists(public_path() . '/' . $subdomain[0] . '/test.txt')) {
            unlink(public_path() . '/' . $subdomain[0] . '/test.txt');
        }
        $file_not_exists = [];
        for ($excel_row = 2; $excel_row <= $highestRow; $excel_row++) {
            $rowData1 = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);

            $count = count($FID['mapped_name']);
            $serial_no = $rowData1[0][2];

            if ($get_file_aws_local_flag->file_aws_local == '1') {
                if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                    $file_location_jpg = '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.jpg';

                    $file_location_png = '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.png';
                } else {
                    $file_location_jpg = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.jpg';

                    $file_location_png = '/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $serial_no . '.png';
                }
            } else {
                if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                    $file_location_jpg = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.jpg';

                    $file_location_png = public_path() . '/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $serial_no . '.png';
                } else {
                    /*$file_location_jpg = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.jpg';

                    $file_location_png = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$serial_no.'.png';*/

                    $file_location_jpg = 'https://' . $subdomain[0] . '.seqrdoc.com/' . $subdomain[0] . '/backend/templates/' . $template_id . '/' . $serial_no . '.jpg';
                    $file_location_png = 'https://' . $subdomain[0] . '.seqrdoc.com/' . $subdomain[0] . '/backend/templates/' . $template_id . '/' . $serial_no . '.png';
                }
            }



            $target_path = $path . '/' . $newflname;

            // dd($file_location_png);
            if ($get_file_aws_local_flag->file_aws_local == '1') {
                if (!Storage::disk('s3')->exists($file_location_png) || !Storage::disk('s3')->exists($file_location_jpg)) {
                    if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
                        if (Storage::disk('s3')->exists('/' . $subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $newflname)) {
                            Storage::disk('s3')->delete($subdomain[0] . '/' . \Config::get('constant.template') . '/' . $template_id . '/' . $newflname);
                        }
                        return response()->json(['success' => false, 'message' => 'Please add images in folder of your template name', 'type' => 'toster']);
                    } else {
                        if (Storage::disk('s3')->exists('/' . $subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $newflname)) {
                            Storage::disk('s3')->delete($subdomain[0] . '/' . \Config::get('constant.sandbox') . '/' . $template_id . '/' . $newflname);
                        }
                        return response()->json(['success' => false, 'message' => 'Please add images in folder of your template name', 'type' => 'toster']);
                    }

                }
            } else {



                if (!$this->check_file_exist($file_location_jpg)) {

                    if (!$this->check_file_exist($file_location_png)) {

                        $file = public_path() . '/' . $subdomain[0] . '/test.txt';

                        file::append($file, $serial_no . PHP_EOL);
                        if ($serial_no != '') {

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

        if (count($file_not_exists) > 0) {
            $path = 'https://' . $subdomain[0] . '.seqrdoc.com/';
            $msg = "<b>Click <a href='" . $path . $subdomain[0] . '/test.txt' . "'class='downloadpdf' download target='_blank'>Here</a> to download file<b>";

            return response()->json(['success' => false, 'message' => 'Please add images in folder of your template name', 'type' => 'toster', 'msg' => $msg]);
        } else {
            return response()->json(['success' => true, 'message' => 'All files exists', 'type' => 'toster']);
        }

    }

    public function addCertificate($serial_no, $certName, $dt, $template_id, $admin_id)
    {
        $domain = \Request::getHost();
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

        $sts = 1;
        $datetime = date("Y-m-d H:i:s");
        $ses_id = $admin_id["id"];
        $certName = str_replace("/", "_", $certName);

        $get_config_data = Config::select('configuration')->first();

        $c = explode(", ", $get_config_data['configuration']);
        $key = "";


        $tempDir = public_path() . '/backend/qr';
        $key = strtoupper(md5($serial_no . $dt));
        $codeContents = $key;
        $fileName = $key . '.png';

        $urlRelativeFilePath = 'qr/' . $fileName;
        $auth_site_id = \Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
        // Mark all previous records of same serial no to inactive if any
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
        $ses_id = $admin_id["id"];
        $auth_site_id = \Auth::guard('admin')->user()->site_id;
        $systemConfig = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
        if (isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1) {
            // Insert the new record
            $result = SbPrintingDetail::create(['username' => $username, 'print_datetime' => $print_datetime, 'printer_name' => $printer_name, 'print_count' => $printer_count, 'print_serial_no' => $print_serial_no, 'sr_no' => $sr_no, 'template_name' => $template_name, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'publish' => 1]);
        } else {
            // Insert the new record
            $result = PrintingDetail::create(['username' => $username, 'print_datetime' => $print_datetime, 'printer_name' => $printer_name, 'print_count' => $printer_count, 'print_serial_no' => $print_serial_no, 'sr_no' => $sr_no, 'template_name' => $template_name, 'created_at' => $datetime, 'created_by' => $ses_id, 'updated_at' => $datetime, 'updated_by' => $ses_id, 'status' => $sts, 'site_id' => $auth_site_id, 'publish' => 1, 'student_table_id' => $student_table_id]);
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
        if ($mm > 3)
            $fy = $fy . "-" . ($yy + 1);
        else
            $fy = str_pad($yy - 1, 2, "0", STR_PAD_LEFT) . "-" . $fy;
        return $fy;
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


    public function TestR(Request $request, CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {
        // $this->findMissedSerialNo();
        // exit;
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $request_number = "R/25/1322";
        $template_id = 2;
        //$request_number = $request['req_number'];
        $IdCardStatus = IdCardStatus::where('request_number', $request_number)
            ->first();

        // print_r($IdCardStatus);
        // die();
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

            // print_r($rowData);
            // exit;
            $excel_column_row = $sheet->getCellByColumnAndRow(3, $excel_row);

            $enroll_value = $excel_column_row->getValue();



            $dt = date("_ymdHis");
            /*$excl_column_row = $sheet->getCellByColumnAndRow(0,$excel_row);
            echo $str = $excl_column_row->getValue();*/
            $str = $rowData[0][0]; //updated by mandar
            //   echo "----";
            // print_r($excel_row);

            //  echo "----";
            //  echo $str;
            //  echo "----";
            //  echo '<br>';
            // $codeContents = strtoupper(md5($str.$dt));


            //exit;
            if (!empty($str)) {

                if ($template_id == 7) {

                    $studentData = StudentTable::where('serial_no', $str)
                        ->orderBy('id', 'desc')->first();
                    // print_r($studentData);
                    // exit;
                    $codeContents = $studentData->key;
                    // exit;
                    //echo "a";
                    //$sheet->setCellValue('L'.$excel_row,$codeContents);
                    $sheet->setCellValue('M' . $excel_row, $codeContents);
                } else {
                    $studentData = StudentTable::where('serial_no', $str)
                        ->orderBy('id', 'desc')->first();
                    $codeContents = $studentData->key;
                    //echo "b";
                    // $sheet->setCellValue('J'.$excel_row,$codeContents);
                    $sheet->setCellValue('K' . $excel_row, $codeContents);
                }
                // echo "<pre>";print_r($excel_row);
                // echo "<pre>";print_r($codeContents);
                // exit;
            }

            // exit;     
            //  print_r($enroll_value);
            //exit;

            /*if($get_file_aws_local_flag->file_aws_local == '1'){
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
*/

            $imageName = $enroll_value . '.jpg';


            /*$file_location_jpg = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$enroll_value.'.jpg';

                $file_location_png = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$enroll_value.'.png';
               //exit;
               if(file_exists($file_location_jpg)){

                   array_push($enrollImage, $imageName);
                   
               }elseif(file_exists($file_location_png)){

                  $imageName = $enroll_value.'.png';
               array_push($enrollImage, $imageName);
               }else{
                    array_push($file_not_exists, $imageName);
               }*/
            // $imageNameJpg = public_path().'/'.$subdomain[0].'/backend/R_21_414/'.$enroll_value.'.jpg';
            // $imageNamePng = public_path().'/'.$subdomain[0].'/backend/R_21_414/'.$enroll_value.'.png';
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

            array_push($enrollImage, $imageName);
            //$imageNameJpg= 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/backend/R_21_414/'.$enroll_value.'.jpg';
            //$imageNamePng = 'https://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/backend/R_21_414/'.$enroll_value.'.png';
            /*if(file_exists($imageNameJpg)){
                array_push($enrollImage, $imageName);
                //echo "Jpg-".$imageName;
                //exit;
                 //echo $i."-".$imageName."-<img src='".$imageNameJpg."' height='100px' width='100px' /> <br>";
            }elseif(file_exists($imageNamePng)){

                $imageName = $enroll_value.'.png';
                array_push($enrollImage, $imageName);
                //echo "Png-".$imageName;
                //exit;
                 //echo $i."-".$imageName."-<img src='".$imageNamePng."' height='100px' width='100px' /> <br>";
            }else{

            */    //echo $imageName.'</br>';
            //exit;
            // echo $i."-".$imageName."-<img src='".$imageNameJpg."' height='100px' width='100px' /> <br>";
            // array_push($file_not_exists, $imageName);

            /*$file_location_jpg = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$enroll_value.'.jpg';

            $file_location_png = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$enroll_value.'.png';
           //exit;
           if(file_exists($file_location_jpg)){
               
           }elseif(file_exists($file_location_png)){

              
           }else{

               //echo "Template7 - ".$imageName.'</br>';
               //exit;
               // echo $i."-".$imageName."-<img src='".$imageNameJpg."' height='100px' width='100px' /> <br>";
               //array_push($file_not_exists, $imageName);


           }*/
            //}




            $i++;

        }


        /*$save_path = '/'.$subdomain[0].'/backend';
        $zip_file_name='test.zip';
        $zipPath= public_path().$save_path.'/'.$zip_file_name;
      // exit;
        $zip = new \ZipArchive;
        
        if ($zip->open($zipPath,\ZipArchive::CREATE) === TRUE) {
            
            $zip->addFile(public_path().$save_path.'/BronzeCertification-SHE_TPSDI_Job_B146_20211217122041_processed.xlsx',"BronzeCertification-SHE_TPSDI_Job_B146_20211217122041_processed.xlsx");
            
            foreach ($enrollImage as $key => $value) {

                $file_location = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$value;
                $zip->addfile($file_location,$value);
            }
            $zip->close();
        }*/


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

        //  $changeStatus = IdCardStatus::where('request_number',$request_number)->update(['status'=>'Inprogress']);
        // $mail_data = [

        //     'req_no'=>$request_number,
        //     'updated_on'=>$created_on,
        //     'template_name'=>$template_name,
        //     'quantity'=>$highestRecord
        // ];

        // $user_email = ['dtp@scube.net.in'];
        // $cc_email = ['dev12@scube.net.in'];
        // $mail_subject = "TPSDI ".$request_number." Request for ".$highestRecord." ".$template_name." is ready.";
        // $mail_view = 'mail.processPdf';

        // Mail::send($mail_view, ['mail_data'=>$mail_data], function ($message) use ($user_email,$mail_subject,$cc_email) {
        //     $message->to($user_email);
        //     $message->cc($cc_email);
        //     $message->subject($mail_subject);
        //     $message->from('info@seqrdoc.com');
        // });

    }


    public function processPdfCustom(Request $request, CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
    {

        $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();

        $systemConfig = SystemConfig::first();

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        //$request_number = $request['req_number'];
        $request_number = "R/25/1322";
        // dd($request_number);
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
        // echo $excelfile;

        $extension = pathinfo($excelfile, PATHINFO_EXTENSION);

        $inputType = 'Xls';
        if ($extension == 'xlsx' || $extension == 'XLSX') {

            $inputType = 'Xlsx';
        }

        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputType);
        $objPHPExcel = $objReader->load($target_path);
        $sheet = $objPHPExcel->getSheet(0);

        $fonts_array = array('' => '');


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
                    $exp = explode('.', $font['font_filename_B']);
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

        for ($excel_row = 94; $excel_row <= 101; $excel_row++) {

            $rowData = $sheet->rangeToArray('A' . $excel_row . ':' . $highestColumn . $excel_row, NULL, TRUE, FALSE);

            // print_r($rowData);
            // exit;
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

                        $QR_width = imagesx($QR);
                        $QR_height = imagesy($QR);

                        $logo_qr_width = $QR_width / 3;

                        imagepng($QR, $pngAbsoluteFilePath);

                        $pdf->SetAlpha(1);
                        $pdf->Image($pngAbsoluteFilePath, $x, $y, 19, 19, "PNG", '', 'R', true);
                        break;

                    case 'ID Barcode':
                        break;
                    case 'Normal':


                        if ($FID['template_id'] == 6) {

                            if ($field_position == 8) {
                                $cell = $sheet->getCellByColumnAndRow(8, $excel_row);

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
            $admin_id = \Auth::guard('admin')->user()->toArray();
            $template_name = $FID['template_name'];
            $certName = str_replace("/", "_", $serial_no) . ".pdf";
            $myPath = public_path() . '/backend/temp_pdf_file';
            $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');
            $student_table_id = $this->addCertificate($serial_no, $certName, $dt, $FID['template_id'], $admin_id);



            $username = $admin_id['username'];
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



        }

        // if($subdomain[0]=='test'){
        CoreHelper::rrmdir($tmpDir);
        //}

        //print_r($enrollImage);
        //exit;
        if ($subdomain[0] == "demo") {
            /*$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            $totalRecords = sizeof($allDataInSheet);
            $wrsheet = $objPHPExcel->getSheet(0);

            $format = 'dd/mm/yyyy';
            for ($i=1; $i <=$totalRecords ; $i++) { 
                 $wrsheet->getStyleByColumnAndRow(8, $i)->getNumberFormat()->setFormatCode($format);
            }*/

            $format = 'dd/mm/yyyy';
            // $objPHPExcel->getActiveSheet()->getColumnDimension('I')
            //    ->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getNumberFormat()->setFormatCode($format);
        }
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

        if ($get_file_aws_local_flag->file_aws_local == '1') {
            \Storage::disk('s3')->put($save_path . '/' . $excel_filename . '_processed.' . $excel_extension, file_get_contents(public_path() . $save_path . '/' . $excel_filename . '_processed.' . $excel_extension), 'public');
        }

        $excel_request_no = explode('/', $request_number);
        $zip_file_name = 'TEST' . $excel_request_no[0] . '_' . $excel_request_no[1] . '_' . $excel_request_no[2] . '.zip';



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

        // $changeStatus = IdCardStatus::where('request_number',$request_number)->update(['status'=>'Inprogress']);
        //  $mail_data = [

        //      'req_no'=>$request_number,
        //      'updated_on'=>$created_on,
        //      'template_name'=>$template_name,
        //      'quantity'=>$highestRecord
        //  ];

        //  $user_email = ['dtp@scube.net.in'];
        //  $cc_email = ['dev12@scube.net.in'];
        //  $mail_subject = "TPSDI ".$request_number." Request for ".$highestRecord." ".$template_name." is ready.";
        //  $mail_view = 'mail.processPdf';

        //  Mail::send($mail_view, ['mail_data'=>$mail_data], function ($message) use ($user_email,$mail_subject,$cc_email) {
        //      $message->to($user_email);
        //      $message->cc($cc_email);
        //      $message->subject($mail_subject);
        //      $message->from('info@seqrdoc.com');
        //  });

        // if($get_file_aws_local_flag->file_aws_local == '1'){
        //     unlink(public_path().$save_path.'/'.$excel_filename.'_processed.'.$excel_extension);
        //     unlink(public_path().$save_path.'/'.$zip_file_name);
        //     unset($fp);
        //     unlink(public_path().$save_path.'/'.$excel_filename.".txt");
        //     unlink($target_path);
        //     \File::deleteDirectory(public_path().$save_path);
        // }


        $message = array('type' => 'success', 'message' => 'Status updated successfully');
        echo json_encode($message);
        exit;


    }


    public function findMissedSerialNo()
    {
        // $ids = array("CardB038792", "CardB038793", "CardB038794", "CardB038795", "CardB038796", "CardB038797", "CardB038798", "CardB038799", "CardB038800", "CardB038801", "CardB038802", "CardB038803", "CardB038804", "CardB038805", "CardB038806", "CardB038807", "CardB038808", "CardB038809", "CardB038810", "CardB038811", "CardB038812", "CardB038813", "CardB038814", "CardB038815", "CardB038816", "CardB038817", "CardB038818", "CardB038819", "CardB038820", "CardB038821", "CardB038822", "CardB038823", "CardB038824", "CardB038825", "CardB038826", "CardB038827", "CardB038828", "CardB038829", "CardB038830", "CardB038831", "CardB038832", "CardB038833", "CardB038834", "CardB038835", "CardB038836", "CardB038837", "CardB038838", "CardB038839", "CardB038840", "CardB038841", "CardB038842", "CardB038843", "CardB038844", "CardB038845", "CardB038846", "CardB038847", "CardB038848", "CardB038849", "CardB038850", "CardB038851", "CardB038852", "CardB038853", "CardB038854", "CardB038855", "CardB038856", "CardB038857", "CardB038858", "CardB038859", "CardB038860", "CardB038861", "CardB038862", "CardB038863", "CardB038864", "CardB038865", "CardB038866", "CardB038867", "CardB038868", "CardB038869", "CardB038870", "CardB038871", "CardB038872", "CardB038873", "CardB038874", "CardB038875", "CardB038876", "CardB038877", "CardB038878", "CardB038879", "CardB038880", "CardB038881", "CardB038882", "CardB038883", "CardB038884", "CardB038885", "CardB038886", "CardB038887", "CardB038888", "CardB038889", "CardB038890", "CardB038891", "CardB038892", "CardB038893", "CardB038894", "CardB038895", "CardB038896", "CardB038897", "CardB038898", "CardB038899", "CardB038900", "CardB038901", "CardB038902", "CardB038903", "CardB038904", "CardB038905", "CardB038906", "CardB038907", "CardB038908", "CardB038909", "CardB038910", "CardB038911", "CardB038912", "CardB038913", "CardB038914", "CardB038915", "CardB038916", "CardB038917", "CardB038918", "CardB038919", "CardB038920", "CardB038921", "CardB038922", "CardB038923", "CardB038924", "CardB038925", "CardB038926", "CardB038927", "CardB038928", "CardB038929", "CardB038930", "CardB038931", "CardB038932", "CardB038933", "CardB038934", "CardB038935", "CardB038936", "CardB038937", "CardB038938", "CardB038939", "CardB038940", "CardB038941", "CardB038942", "CardB038943", "CardB038944", "CardB038945", "CardB038946", "CardB038947", "CardB038948", "CardB038949", "CardB038950", "CardB038951", "CardB038952", "CardB038953", "CardB038954", "CardB038955", "CardB038956", "CardB038957", "CardB038958", "CardB038959", "CardB038960", "CardB038961", "CardB038962", "CardB038963", "CardB038964", "CardB038965", "CardB038966", "CardB038967", "CardB038968", "CardB038969", "CardB038970", "CardB038971", "CardB038972", "CardB038973", "CardB038974", "CardB038975", "CardB038976", "CardB038977", "CardB038978", "CardB038979", "CardB038980", "CardB038981", "CardB038982", "CardB038983", "CardB038984", "CardB038985", "CardB038986", "CardB038987", "CardB038988", "CardB038989", "CardB038990", "CardB038991", "CardB038992", "CardB038993", "CardB038994", "CardB038995", "CardB038996", "CardB038997", "CardB038998", "CardB038999", "CardB039000", "CardB039001", "CardB039002", "CardB039003", "CardB039004", "CardB039005", "CardB039006", "CardB039007", "CardB039008", "CardB039009", "CardB039010", "CardB039011", "CardB039012", "CardB039013", "CardB039014", "CardB039015", "CardB039016", "CardB039017", "CardB039018", "CardB039019", "CardB039020", "CardB039021", "CardB039022", "CardB039023", "CardB039024", "CardB039025", "CardB039026", "CardB039027", "CardB039028", "CardB039029", "CardB039030", "CardB039031", "CardB039032", "CardB039033", "CardB039034", "CardB039035", "CardB039036", "CardB039037", "CardB039038", "CardB039039", "CardB039040", "CardB039041", "CardB039042", "CardB039043", "CardB039044", "CardB039045", "CardB039046", "CardB039047", "CardB039048", "CardB039049", "CardB039050", "CardB039051", "CardB039052", "CardB039053", "CardB039054", "CardB039055", "CardB039056", "CardB039057", "CardB039058", "CardB039059", "CardB039060", "CardB039061", "CardB039062", "CardB039063", "CardB039064", "CardB039065", "CardB039066", "CardB039067", "CardB039068", "CardB039069", "CardB039070", "CardB039071", "CardB039072", "CardB039073", "CardB039074", "CardB039075", "CardB039076", "CardB039077", "CardB039078", "CardB039079", "CardB039080", "CardB039081", "CardB039082", "CardB039083", "CardB039084", "CardB039085", "CardB039086", "CardB039087", "CardB039088", "CardB039089", "CardB039090", "CardB039091", "CardB039092", "CardB039093", "CardB039094", "CardB039095", "CardB039096", "CardB039097", "CardB039098", "CardB039099", "CardB039100", "CardB039101", "CardB039102", "CardB039103", "CardB039104", "CardB039105", "CardB039106", "CardB039107", "CardB039108", "CardB039109", "CardB039110", "CardB039111", "CardB039112", "CardB039113", "CardB039114", "CardB039115", "CardB039116", "CardB039117", "CardB039118", "CardB039119", "CardB039120", "CardB039121", "CardB039122", "CardB039123", "CardB039124", "CardB039125", "CardB039126", "CardB039127", "CardB039128", "CardB039129", "CardB039130", "CardB039131", "CardB039132", "CardB039133", "CardB039134", "CardB039135", "CardB039136", "CardB039137", "CardB039138", "CardB039139", "CardB039140", "CardB039141", "CardB039142", "CardB039143", "CardB039144", "CardB039145", "CardB039146", "CardB039147", "CardB039148", "CardB039149", "CardB039150", "CardB039151", "CardB039152", "CardB039153", "CardB039154", "CardB039155", "CardB039156", "CardB039157", "CardB039158", "CardB039159", "CardB039160", "CardB039161", "CardB039162", "CardB039163", "CardB039164", "CardB039165", "CardB039166", "CardB039167", "CardB039168", "CardB039169", "CardB039170", "CardB039171", "CardB039172", "CardB039173", "CardB039174", "CardB039175", "CardB039176", "CardB039177", "CardB039178", "CardB039179", "CardB039180", "CardB039181", "CardB039182", "CardB039183", "CardB039184", "CardB039185", "CardB039186", "CardB039187", "CardB039188", "CardB039189", "CardB039190", "CardB039191", "CardB039192", "CardB039193", "CardB039194", "CardB039195", "CardB039196", "CardB039197", "CardB039198", "CardB039199", "CardB039200", "CardB039201", "CardB039202", "CardB039203", "CardB039204", "CardB039205", "CardB039206", "CardB039207", "CardB039208", "CardB039209", "CardB039210", "CardB039211", "CardB039212", "CardB039213", "CardB039214", "CardB039215", "CardB039216", "CardB039217", "CardB039218", "CardB039219", "CardB039220", "CardB039221", "CardB039222", "CardB039223", "CardB039224", "CardB039225", "CardB039226", "CardB039227", "CardB039228", "CardB039229", "CardB039230", "CardB039231", "CardB039232", "CardB039233", "CardB039234", "CardB039235", "CardB039236", "CardB039237", "CardB039238", "CardB039239", "CardB039240", "CardB039241", "CardB039242", "CardB039243", "CardB039244", "CardB039245", "CardB039246", "CardB039247", "CardB039248", "CardB039249", "CardB039250", "CardB039251", "CardB039252", "CardB039253", "CardB039254", "CardB039255", "CardB039256", "CardB039257", "CardB039258", "CardB039259", "CardB039260", "CardB039261", "CardB039262", "CardB039263", "CardB039264", "CardB039265", "CardB039266", "CardB039267", "CardB039268", "CardB039269", "CardB039270", "CardB039271", "CardB039272", "CardB039273", "CardB039274", "CardB039275", "CardB039276", "CardB039277", "CardB039278", "CardB039279", "CardB039280", "CardB039281", "CardB039282" );
        $ids = array(
            "CardB040673",
            "CardB040674",
            "CardB040675",
            "CardB040676",
            "CardB040677",
            "CardB040678",
            "CardB040679",
            "CardB040680",
            "CardB040681",
            "CardB040682",
            "CardB040683",
            "CardB040684",
            "CardB040685",
            "CardB040686",
            "CardB040687",
            "CardB040688",
            "CardB040689",
            "CardB040690",
            "CardB040691",
            "CardB040692",
            "CardB040693",
            "CardB040694",
            "CardB040695",
            "CardB040696",
            "CardB040697",
            "CardB040698",
            "CardB040699",
            "CardB040700",
            "CardB040701",
            "CardB040702",
            "CardB040703",
            "CardB040704",
            "CardB040705",
            "CardB040706",
            "CardB040707",
            "CardB040708",
            "CardB040709",
            "CardB040710",
            "CardB040711",
            "CardB040712",
            "CardB040713",
            "CardB040714",
            "CardB040715",
            "CardB040716",
            "CardB040717",
            "CardB040718",
            "CardB040719",
            "CardB040720",
            "CardB040721",
            "CardB040722",
            "CardB040723",
            "CardB040724",
            "CardB040725",
            "CardB040726",
            "CardB040727",
            "CardB040728",
            "CardB040729",
            "CardB040730",
            "CardB040731",
            "CardB040732",
            "CardB040733",
            "CardB040734",
            "CardB040735",
            "CardB040736",
            "CardB040737",
            "CardB040738",
            "CardB040739",
            "CardB040740",
            "CardB040741",
            "CardB040742",
            "CardB040743",
            "CardB040744",
            "CardB040745",
            "CardB040746",
            "CardB040747",
            "CardB040748",
            "CardB040749",
            "CardB040750",
            "CardB040751",
            "CardB040752",
            "CardB040753",
            "CardB040754",
            "CardB040755",
            "CardB040756",
            "CardB040757",
            "CardB040758",
            "CardB040759",
            "CardB040760",
            "CardB040761",
            "CardB040762",
            "CardB040763",
            "CardB040764",
            "CardB040765",
            "CardB040766",
            "CardB040767",
            "CardB040768",
            "CardB040769",
            "CardB040770",
            "CardB040771",
            "CardB040772",
            "CardB040773",
            "CardB040774",
            "CardB040775",
            "CardB040776",
            "CardB040777",
            "CardB040778",
            "CardB040779",
            "CardB040780",
            "CardB040781",
            "CardB040782",
            "CardB040783",
            "CardB040784",
            "CardB040785",
            "CardB040786",
            "CardB040787",
            "CardB040788",
            "CardB040789",
            "CardB040790",
            "CardB040791",
            "CardB040792",
            "CardB040793",
            "CardB040794",
            "CardB040795",
            "CardB040796",
            "CardB040797",
            "CardB040798",
            "CardB040799",
            "CardB040800",
            "CardB040801",
            "CardB040802",
            "CardB040803",
            "CardB040804",
            "CardB040805",
            "CardB040806",
            "CardB040807",
            "CardB040808",
            "CardB040809",
            "CardB040810",
            "CardB040811",
            "CardB040812",
            "CardB040813",
            "CardB040814",
            "CardB040815",
            "CardB040816",
            "CardB040817",
            "CardB040818",
            "CardB040819",
            "CardB040820",
            "CardB040821",
            "CardB040822",
            "CardB040823",
            "CardB040824",
            "CardB040825",
            "CardB040826",
            "CardB040827",
            "CardB040828",
            "CardB040829",
            "CardB040830",
            "CardB040831",
            "CardB040832",
            "CardB040833",
            "CardB040834",
            "CardB040835",
            "CardB040836",
            "CardB040837",
            "CardB040838",
            "CardB040839",
            "CardB040840",
            "CardB040841",
            "CardB040842",
            "CardB040843",
            "CardB040844",
            "CardB040845",
            "CardB040846",
            "CardB040847",
            "CardB040848",
            "CardB040849",
            "CardB040850",
            "CardB040851",
            "CardB040852",
            "CardB040853",
            "CardB040854",
            "CardB040855",
            "CardB040856",
            "CardB040857",
            "CardB040858",
            "CardB040859",
            "CardB040860",
            "CardB040861",
            "CardB040862",
            "CardB040863",
            "CardB040864",
            "CardB040865",
            "CardB040866",
            "CardB040867",
            "CardB040868",
            "CardB040869",
            "CardB040870",
            "CardB040871",
            "CardB040872",
            "CardB040873",
            "CardB040874",
            "CardB040875",
            "CardB040876",
            "CardB040877",
            "CardB040878",
            "CardB040879",
            "CardB040880",
            "CardB040881",
            "CardB040882",
            "CardB040883",
            "CardB040884",
            "CardB040885",
            "CardB040886",
            "CardB040887",
            "CardB040888",
            "CardB040889",
            "CardB040890",
            "CardB040891",
            "CardB040892",
            "CardB040893",
            "CardB040894",
            "CardB040895",
            "CardB040896",
            "CardB040897",
            "CardB040898",
            "CardB040899",
            "CardB040900",
            "CardB040901",
            "CardB040902",
            "CardB040903",
            "CardB040904",
            "CardB040905",
            "CardB040906",
            "CardB040907",
            "CardB040908",
            "CardB040909",
            "CardB040910",
            "CardB040911",
            "CardB040912",
            "CardB040913",
            "CardB040914",
            "CardB040915",
            "CardB040916",
            "CardB040917",
            "CardB040918",
            "CardB040919",
            "CardB040920",
            "CardB040921",
            "CardB040922",
            "CardB040923",
            "CardB040924",
            "CardB040925",
            "CardB040926",
            "CardB040927",
            "CardB040928",
            "CardB040929",
            "CardB040930",
            "CardB040931",
            "CardB040932",
            "CardB040933",
            "CardB040934",
            "CardB040935",
            "CardB040936",
            "CardB040937",
            "CardB040938",
            "CardB040939",
            "CardB040940",
            "CardB040941",
            "CardB040942",
            "CardB040943",
            "CardB040944",
            "CardB040945",
            "CardB040946",
            "CardB040947",
            "CardB040948",
            "CardB040949",
            "CardB040950",
            "CardB040951",
            "CardB040952",
            "CardB040953",
            "CardB040954",
            "CardB040955",
            "CardB040956",
            "CardB040957",
            "CardB040958",
            "CardB040959",
            "CardB040960",
            "CardB040961",
            "CardB040962",
            "CardB040963",
            "CardB040964",
            "CardB040965",
            "CardB040966",
            "CardB040967",
            "CardB040968",
            "CardB040969",
            "CardB040970",
            "CardB040971",
            "CardB040972",
            "CardB040973",
            "CardB040974",
            "CardB040975",
            "CardB040976",
            "CardB040977",
            "CardB040978",
            "CardB040979",
            "CardB040980",
            "CardB040981",
            "CardB040982",
            "CardB040983",
            "CardB040984",
            "CardB040985",
            "CardB040986",
            "CardB040987",
            "CardB040988",
            "CardB040989",
            "CardB040990",
            "CardB040991",
            "CardB040992",
            "CardB040993",
            "CardB040994",
            "CardB040995",
            "CardB040996",
            "CardB040997",
            "CardB040998",
            "CardB040999",
            "CardB041000",
            "CardB041001",
            "CardB041002",
            "CardB041003",
            "CardB041004",
            "CardB041005",
            "CardB041006",
            "CardB041007",
            "CardB041008",
            "CardB041009",
            "CardB041010",
            "CardB041011",
            "CardB041012",
            "CardB041013",
            "CardB041014",
            "CardB041015",
            "CardB041016",
            "CardB041017",
            "CardB041018",
            "CardB041019",
            "CardB041020",
            "CardB041021",
            "CardB041022",
            "CardB041023",
            "CardB041024",
            "CardB041025",
            "CardB041026",
            "CardB041027",
            "CardB041028",
            "CardB041029",
            "CardB041030",
            "CardB041031",
            "CardB041032",
            "CardB041033",
            "CardB041034",
            "CardB041035",
            "CardB041036",
            "CardB041037",
            "CardB041038",
            "CardB041039",
            "CardB041040",
            "CardB041041",
            "CardB041042",
            "CardB041043",
            "CardB041044",
            "CardB041045",
            "CardB041046",
            "CardB041047",
            "CardB041048",
            "CardB041049",
            "CardB041050",
            "CardB041051",
            "CardB041052",
            "CardB041053",
            "CardB041054",
            "CardB041055",
            "CardB041056",
            "CardB041057",
            "CardB041058",
            "CardB041059",
            "CardB041060",
            "CardB041061",
            "CardB041062",
            "CardB041063",
            "CardB041064",
            "CardB041065",
            "CardB041066",
            "CardB041067",
            "CardB041068",
            "CardB041069",
            "CardB041070",
            "CardB041071",
            "CardB041072",
            "CardB041073",
            "CardB041074",
            "CardB041075",
            "CardB041076",
            "CardB041077",
            "CardB041078",
            "CardB041079",
            "CardB041080",
            "CardB041081",
            "CardB041082",
            "CardB041083",
            "CardB041084",
            "CardB041085",
            "CardB041086",
            "CardB041087",
            "CardB041088",
            "CardB041089",
            "CardB041090",
            "CardB041091",
            "CardB041092",
            "CardB041093",
            "CardB041094",
            "CardB041095",
            "CardB041096",
            "CardB041097",
            "CardB041098",
            "CardB041099",
            "CardB041100",
            "CardB041101",
            "CardB041102",
            "CardB041103",
            "CardB041104",
            "CardB041105",
            "CardB041106",
            "CardB041107",
            "CardB041108",
            "CardB041109",
            "CardB041110",
            "CardB041111",
            "CardB041112",
            "CardB041113",
            "CardB041114",
            "CardB041115",
            "CardB041116",
            "CardB041117",
            "CardB041118",
            "CardB041119",
            "CardB041120",
            "CardB041121",
            "CardB041122",
            "CardB041123",
            "CardB041124",
            "CardB041125",
            "CardB041126",
            "CardB041127",
            "CardB041128",
            "CardB041129",
            "CardB041130",
            "CardB041131"
        );
        foreach ($ids as $id) {
            $exist = StudentTable::where('status', 1)->where('template_id', 7)->where('serial_no', $id)->first();
            if (empty($exist)) {
                echo "<pre>";
                dd($id);
            }
        }
        dd("complete");
    }


    public function generatePdfZip(Request $request, $request_number, $template_id)
    {
        $checkUploadedFileOnAwsOrLocal = new CheckUploadedFileOnAwsORLocalService();
        // $this->findMissedSerialNo();
        // exit;
        $domain = \Request::getHost();
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

            // print_r($rowData);
            // exit;
            $excel_column_row = $sheet->getCellByColumnAndRow(3, $excel_row);

            $enroll_value = $excel_column_row->getValue();



            $dt = date("_ymdHis");

            $str = $rowData[0][0]; //updated by mandar


            //exit;
            if (!empty($str)) {

                if ($template_id == 7) {

                    $studentData = StudentTable::where('serial_no', $str)
                        ->orderBy('id', 'desc')->first();
                    // print_r($studentData);
                    // exit;
                    $codeContents = $studentData->key;
                    // exit;
                    //echo "a";
                    //$sheet->setCellValue('L'.$excel_row,$codeContents);
                    $sheet->setCellValue('M' . $excel_row, $codeContents);
                } else {
                    $studentData = StudentTable::where('serial_no', $str)
                        ->orderBy('id', 'desc')->first();
                    $codeContents = $studentData->key;
                    //echo "b";
                    // $sheet->setCellValue('J'.$excel_row,$codeContents);
                    $sheet->setCellValue('K' . $excel_row, $codeContents);
                }
                // echo "<pre>";print_r($excel_row);
                // echo "<pre>";print_r($codeContents);
                // exit;
            }


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

            array_push($enrollImage, $imageName);


            $i++;

        }




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

    }


}
