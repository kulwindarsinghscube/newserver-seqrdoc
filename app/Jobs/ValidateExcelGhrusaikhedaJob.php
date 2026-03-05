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
class ValidateExcelGhrusaikhedaJob
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
        //dd($pdf_data);
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
        // dd($columnsCount1);

        if ($dropdown_template_id == 1) {
            if ($columnsCount1 == 102) {  // âœ… Fix here
                $columnArr = array("Unique ID","ROLL NO","ABC / APAAR ID","NAME FOR GRADE CARD", "MOTHER NAME","FATHER NAME","REGISTRATION NO","UNIVERSITY ENROLLMENT NO","TERM","ACADEMIC YEAR","SESSION","EXAMINATION","PROGRAMME", "GENDER","BRANCH","EXAM REGISTRATION CREDITS",
                    "EARN CREDITS",
                    "GRADE POINTS EARNED",
                    "SGPA",
                    "CUMULATIVE CREDITS",
                    "CUMULATIVE EGP",
                    "CGPA",
                    "RESULT",
                    "RESULT DATE",

                    "COURSE CODE 1",
                    "COURSE NAME 1",
                    "COMPONENT NAME 1",
                    "CREDITS 1",
                    "GRADES 1",

                    "COURSE CODE 2",
                    "COURSE NAME 2",
                    "COMPONENT NAME 2",
                    "CREDITS 2",
                    "GRADES 2",

                    "COURSE CODE 3",
                    "COURSE NAME 3",
                    "COMPONENT NAME 3",
                    "CREDITS 3",
                    "GRADES 3",

                    "COURSE CODE 4",
                    "COURSE NAME 4",
                    "COMPONENT NAME 4",
                    "CREDITS 4",
                    "GRADES 4",

                    "COURSE CODE 5",
                    "COURSE NAME 5",
                    "COMPONENT NAME 5",
                    "CREDITS 5",
                    "GRADES 5",

                    "COURSE CODE 6",
                    "COURSE NAME 6",
                    "COMPONENT NAME 6",
                    "CREDITS 6",
                    "GRADES 6",

                    "COURSE CODE 7",
                    "COURSE NAME 7",
                    "COMPONENT NAME 7",
                    "CREDITS 7",
                    "GRADES 7",

                    "COURSE CODE 8",
                    "COURSE NAME 8",
                    "COMPONENT NAME 8",
                    "CREDITS 8",
                    "GRADES 8",

                    "COURSE CODE 9",
                    "COURSE NAME 9",
                    "COMPONENT NAME 9",
                    "CREDITS 9",
                    "GRADES 9",

                    "COURSE CODE 10",
                    "COURSE NAME 10",
                    "COMPONENT NAME 10",
                    "CREDITS 10",
                    "GRADES 10",

                    "COURSE CODE 11",
                    "COURSE NAME 11",
                    "COMPONENT NAME 11",
                    "CREDITS 11",
                    "GRADES 11",


                      "COURSE CODE 12",
                    "COURSE NAME 12",
                    "COMPONENT NAME 12",
                    "CREDITS 12",
                    "GRADES 12",

                      "COURSE CODE 13",
                    "COURSE NAME 13",
                    "COMPONENT NAME 13",
                    "CREDITS 13",
                    "GRADES 13",

                      "COURSE CODE 14",
                    "COURSE NAME 14",
                    "COMPONENT NAME 14",
                    "CREDITS 14",
                    "GRADES 14",
                      "COURSE CODE 15",
                    "COURSE NAME 15",
                    "COMPONENT NAME 15",
                    "CREDITS 15",
                    "GRADES 15",
                    

                    "CREDIT EARNED",
                    "INCENTIVE GRADE POINT",
                    "NAME OF EXAM" 
                );



                $mismatchColArr = array_diff($rowData1[0], $columnArr);
                // dd($rowData1);
                if (count($mismatchColArr) > 0) {
                    return response()->json([
                        'success' => false,
                        'type' => 'error',
                        'message' => 'Sheet2/Json : Column names not matching as per requirement. Please check columns : ' . implode(',', $mismatchColArr)
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'type' => 'error',
                    'message' => 'Columns count of excel do not matched! Found: ' . $columnsCount1 . ', Expected: 99'
                ]);
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
            $message = array('success' => false, 'type' => 'error', 'message' => 'Excel/Json has more than 1 column having same name. i.e. : ' . $duplicate_columns);
            return json_encode($message);
        }

        unset($rowData1[0]);
        $rowData1 = array_values($rowData1);
        $blobArr = array();


        $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id', $auth_site_id)->first();
        $old_rows = 0;
        $new_rows = 0;
        foreach ($rowData1 as $key1 => $value1) {

            $serial_no = $value1[0];
            array_push($blobArr, $serial_no);

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
                // dd($studentTableCounts);
                if ($studentTableCounts > 0) {
                    $old_rows += 1;
                } else {
                    $new_rows += 1;
                }
            }
        }


        $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));
        //   dd( $mismatchArr);    
        if (count($mismatchArr) > 0) {
            return response()->json(['success' => false, 'type' => 'error', 'message' => 'Sheet1 : Unique Id contains following duplicate values : ' . implode(',', $mismatchArr)]);
        } else {

            return response()->json(['success' => true, 'type' => 'success', 'message' => 'success', 'old_rows' => $old_rows, 'new_rows' => $new_rows]);
        }
    }


}
