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

class ValidateExcelNnpJob
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


        //exit;

        if ($dropdown_template_id == 1) {
            if ($columnsCount1 == 115) {
                // $photo_col=354;
                $columnArr = array(
                    "Unique ID",
                    "PROGRAMME",
                    "SEMESTER",
                    "ACADEMIC YEAR",
                    "STUDENT ID",
                    "PRN No.",
                    "Examination Seat No.",
                    "Name of the Learner",
                    "Month & Year of Examination",
                    "COURSE CODE_1",
                    "COURSE TITLE_1",
                    "Course Credit_1",
                    "CIA Max_1",
                    "CIA Min_1",
                    "CIA Obt_1",
                    "SEM Max_1",
                    "SEM Min_1",
                    "SEM Obt_1",
                    "Total Max_1",
                    "Total Min_1",
                    "Total Obt_1",
                    "Grade_1",
                    "Grade Points_1",
                    "Credits Earned_1",
                    "CG_1",
                    "COURSE CODE_2",
                    "COURSE TITLE_2",
                    "Course Credit_2",
                    "CIA Max_2",
                    "CIA Min_2",
                    "CIA Obt_2",
                    "SEM Max_2",
                    "SEM Min_2",
                    "SEM Obt_2",
                    "Total Max_2",
                    "Total Min_2",
                    "Total Obt_2",
                    "Grade_2",
                    "Grade Points_2",
                    "Credits Earned_2",
                    "CG_2",
                    "COURSE CODE_3",
                    "COURSE TITLE_3",
                    "Course Credit_3",
                    "CIA Max_3",
                    "CIA Min_3",
                    "CIA Obt_3",
                    "SEM Max_3",
                    "SEM Min_3",
                    "SEM Obt_3",
                    "Total Max_3",
                    "Total Min_3",
                    "Total Obt_3",
                    "Grade_3",
                    "Grade Points_3",
                    "Credits Earned_3",
                    "CG_3",
                    "COURSE CODE_4",
                    "COURSE TITLE_4",
                    "Course Credit_4",
                    "CIA Max_4",
                    "CIA Min_4",
                    "CIA Obt_4",
                    "SEM Max_4",
                    "SEM Min_4",
                    "SEM Obt_4",
                    "Total Max_4",
                    "Total Min_4",
                    "Total Obt_4",
                    "Grade_4",
                    "Grade Points_4",
                    "Credits Earned_4",
                    "CG_4",
                    "COURSE CODE_5",
                    "COURSE TITLE_5",
                    "Course Credit_5",
                    "CIA Max_5",
                    "CIA Min_5",
                    "CIA Obt_5",
                    "SEM Max_5",
                    "SEM Min_5",
                    "SEM Obt_5",
                    "Total Max_5",
                    "Total Min_5",
                    "Total Obt_5",
                    "Grade_5",
                    "Grade Points_5",
                    "Credits Earned_5",
                    "CG_5",
                    "COURSE CODE_6",
                    "COURSE TITLE_6",
                    "Course Credit_6",
                    "CIA Max_6",
                    "CIA Min_6",
                    "CIA Obt_6",
                    "SEM Max_6",
                    "SEM Min_6",
                    "SEM Obt_6",
                    "Total Max_6",
                    "Total Min_6",
                    "Total Obt_6",
                    "Grade_6",
                    "Grade Points_6",
                    "Credits Earned_6",
                    "CG_6",
                    "SGPI",
                    "Result",
                    "Credits Earned",
                    "Total",
                    "ΣC",
                    "ΣCG",
                    "Place",
                    "Print Date",
                    "Result Date",
                    "PHOTO"
                );
                $mismatchColArr = array_diff($rowData1[0], $columnArr);
                if (count($mismatchColArr) > 0) {
                    return response()->json(['success' => false, 'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : ' . implode(',', $mismatchColArr)]);
                }
            } else {
                return response()->json(['success' => false, 'type' => 'error', 'message' => 'Columns count of excel do not matched!']);
            }
        } elseif ($dropdown_template_id == 2) {
            if ($columnsCount1 == 15) {
                // dd($columnsCount1);
                $columnArr = array("Unique Serial No.", "Certificate No.", "Name", "Subject", "Subject_2", "competence", "competence1", "competence2", "competence3", "competence4", "competence5", "competence6", "Security Line", "Serial Number", "Date");


                $mismatchColArr = array_diff($rowData1[0], $columnArr);
                // dd($rowData1);

                if (!empty($mismatchColArr)) {
                    print_r($mismatchColArr);
                    exit; // stop execution
                    return response()->json(['success' => false, 'type'    => 'error', 'message' => 'Sheet1 : Column names not matching. Please check columns : ' . implode(', ', $mismatchColArr)]);
                }
            } else {
                return response()->json(['success' => false, 'type'    => 'error', 'message' => 'Columns count of excel do not match for Template 2!']);
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
            if ($dropdown_template_id == 3) {
                $profile_path = public_path() . '\\' . $subdomain[0] . '\backend\templates\100\\' . $value1[$photo_col];
                if (!file_exists($profile_path)) {
                    array_push($profErrArr, $value1[$photo_col]);
                }
            }
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
        //   dd(count($mismatchArr));
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
