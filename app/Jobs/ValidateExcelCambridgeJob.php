<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\models\BackgroundTemplateMaster;
use Auth, DB;
use TCPDF;
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

class ValidateExcelCambridgeJob
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
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $pdf_data = $this->pdf_data;
         
        $rowData1 = $pdf_data['rowData1'];
        $auth_site_id = $pdf_data['auth_site_id'];
        $dropdown_template_id = $pdf_data['dropdown_template_id'];
        $rowData1[0] = array_filter($rowData1[0]);
        $columnsCount1 = count($rowData1[0]);
        // dd($columnsCount1, $rowData1[0]);

        // echo $columnsCount1;
        // exit;

        if ($dropdown_template_id == 1) {
            if ($columnsCount1 == 139) {
                $columnArr = array("Unique ID", "Name of the student", "Father name", "Mother Name", "Progamme/Branch", "USN",'Pdf file', "Semester", "Month & Year of Exam", "Sr no 1", "Course code 1", "Course title 1", "Credits 1", "Grade Awarded 1", "Grade points 1", "Sr no 2", "Course code 2", "Course title 2", "Credits 2", "Grade Awarded 2", "Grade points 2", "Sr no 3", "Course code 3", "Course title 3", "Credits 3", "Grade Awarded 3", "Grade points 3", "Sr no 4", "Course code 4", "Course title 4", "Credits 4", "Grade Awarded 4", "Grade points 4", "Sr no 5", "Course code 5", "Course title 5", "Credits 5", "Grade Awarded 5", "Grade points 5", "Sr no 6", "Course code 6", "Course title 6", "Credits 6", "Grade Awarded 6", "Grade points 6", "Sr no 7", "Course code 7", "Course title 7", "Credits 7", "Grade Awarded 7", "Grade points 7", "Sr no 8", "Course code 8", "Course title 8", "Credits 8", "Grade Awarded 8", "Grade points 8", "Sr no 9", "Course code 9", "Course title 9", "Credits 9", "Grade Awarded 9", "Grade points 9", "Sr no 10", "Course code 10", "Course title 10", "Credits 10", "Grade Awarded 10", "Grade points 10", "Sr no 11", "Course code 11", "Course title 11", "Credits 11", "Grade Awarded 11", "Grade points 11", "Sr no 12", "Course code 12", "Course title 12", "Credits 12", "Grade Awarded 12", "Grade points 12", "Sr no 13", "Course code 13", "Course title 13", "Credits 13", "Grade Awarded 13", "Grade points 13", "Sr no 14", "Course code 14", "Course title 14", "Credits 14", "Grade Awarded 14", "Grade points 14", "Sr no 15", "Course code 15", "Course title 15", "Credits 15", "Grade Awarded 15", "Grade points 15","Sr no 16", "Course code 16", "Course title 16", "Credits 16", "Grade Awarded 16", "Grade points 16","Sr no 17", "Course code 17", "Course title 17", "Credits 17", "Grade Awarded 17", "Grade points 17","Sr no 18", "Course code 18", "Course title 18", "Credits 18", "Grade Awarded 18", "Grade points 18","Sr no 19", "Course code 19", "Course title 19", "Credits 19", "Grade Awarded 19", "Grade points 19","Sr no 20", "Course code 20", "Course title 20", "Credits 20", "Grade Awarded 20", "Grade points 20", "Grade points 21","Credits registered", "Credits earned", "Cumulative credits earned", "Cumulative credits points earned", "Ʃ(Ci xGi)", "SGPA", "CGPA", "Photo");
                $mismatchColArr = array_diff($rowData1[0], $columnArr);
                if (count($mismatchColArr) > 0) {
                    return response()->json(['success' => false, 'type' => 'error', 'message' => 'Sheet1 : Column names not matching as per requirement. Please check columns : ' . implode(',', $mismatchColArr)]);
                }
            } else {
                return response()->json(['success' => false, 'type' => 'error', 'message' => 'Columns count of excel do not matched!']);
            }
        }


        $ab = array_count_values($rowData1[0]);

        $duplicate_columns = '';
        foreach ($ab as $key => $value) {
            if ($value > 1) {
                if ($duplicate_columns != '') {

                    $duplicate_columns .= ", " . $key;
                } else {
                    $duplicate_columns .= $key;
                }
            }
        }

        if ($duplicate_columns != '') {
            // Excel has more than 1 column having same name. i.e. <column name>
            $message = array('success' => false, 'type' => 'error', 'message' => 'Excel/Json has more than 1 column having same name. i.e. : ' . $duplicate_columns);
            //return json_encode($message);
        }

        unset($rowData1[0]);
        $rowData1 = array_values($rowData1);
        $blobArr = array();
        $profArr = array();
        $profErrArr = array();
        /*foreach ($rowData1 as $readblob) {
            array_push($blobArr, $readblob[23]);
        }*/

        $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
        $old_rows = 0;
        $new_rows = 0;
        foreach ($rowData1 as $key1 => $value1) {

            $serial_no = $value1[0];
            array_push($blobArr, $serial_no);
            // array_push($profArr, $value1[141]);

            /*$profile_path = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$value1[$photo_col];
            if (!file_exists($profile_path)) {   
                 //array_push($profErrArr, $value1[$photo_col]);
            }*/
            if ($sandboxCheck['sandboxing'] == 1) {
                $studentTableCounts = SbStudentTable::where('serial_no', $serial_no)->where('site_id', $auth_site_id)->count();

                $studentTablePrefixCounts = SbStudentTable::where('serial_no', 'T-' . $serial_no)->where('site_id', $auth_site_id)->count();
                if ($studentTableCounts > 0) {
                    $old_rows += 1;
                } else if ($studentTablePrefixCounts) {

                    $old_rows += 1;
                } else {
                    $new_rows += 1;
                }
            } else {
                $studentTableCounts = StudentTable::where('serial_no', $serial_no)->where('site_id', $auth_site_id)->count();
                if ($studentTableCounts > 0) {
                    $old_rows += 1;
                } else {
                    $new_rows += 1;
                }
            }
        }


        $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));

        if (count($mismatchArr) > 0) {
            return response()->json(['success' => false, 'type' => 'error', 'message' => 'Sheet1 : Unique Id contains following duplicate values : ' . implode(',', $mismatchArr)]);
        }
        /*  $mismatchProfArr = array_intersect($profArr, array_unique(array_diff_key($profArr, array_unique($profArr))));

        if(count($mismatchProfArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo contains following duplicate values : '.implode(',', $mismatchProfArr)]);
            }*/
        if (count($profErrArr) > 0) {
            //return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo does not exists following values : '.implode(',', $profErrArr)]);
        }

        return response()->json(['success' => true, 'type' => 'success', 'message' => 'success', 'old_rows' => $old_rows, 'new_rows' => $new_rows]);
    }
}
