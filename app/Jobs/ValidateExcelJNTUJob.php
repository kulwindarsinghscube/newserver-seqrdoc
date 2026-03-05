<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\models\BackgroundTemplateMaster;
use Auth,DB;
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
class ValidateExcelJNTUJob
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
       
        $rowData1=$pdf_data['rowData1'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
        $rowData1[0] = array_filter($rowData1[0]);
       
        $columnsCount1=count($rowData1[0]);
        // dd($columnsCount1);
                if($dropdown_template_id==2){
                    if($columnsCount1==102){
                        $columnArr=array("UNIQUE ID", "HTNO", "MEMO NO", "APAAR ID", "SERIAL NO", "EXAMINATION", "MONTH & YEAR", "BRANCH ", "NAME OF THE STUDENT", "FATHERS_NAME", "MOTHERS_NAME", "SUBJECT_CODE1", "SUBJECT_TITLE1", "INTERNAL_MARKS(30M)1", "EXTERNAL_MARKS(70M)1", "TOTAL_MARKS(100)1", "RESULT1", "CREDITS1", "GRADE1", "SUBJECT_CODE2", "SUBJECT_TITLE2", "INTERNAL_MARKS(30M)2", "EXTERNAL_MARKS(70M)2", "TOTAL_MARKS(100)2", "RESULT2", "CREDITS2", "GRADE2", "SUBJECT_CODE3", "SUBJECT_TITLE3", "INTERNAL_MARKS(30M)3", "EXTERNAL_MARKS(70M)3", "TOTAL_MARKS(100)3", "RESULT3", "CREDITS3", "GRADE3", "SUBJECT_CODE4", "SUBJECT_TITLE4", "INTERNAL_MARKS(30M)4", "EXTERNAL_MARKS(70M)4", "TOTAL_MARKS(100)4", "RESULT4", "CREDITS4", "GRADE4", "SUBJECT_CODE5", "SUBJECT_TITLE5", "INTERNAL_MARKS(30M)5", "EXTERNAL_MARKS(70M)5", "TOTAL_MARKS(100)5", "RESULT5", "CREDITS5", "GRADE5", "SUBJECT_CODE6", "SUBJECT_TITLE6", "INTERNAL_MARKS(30M)6", "EXTERNAL_MARKS(70M)6", "TOTAL_MARKS(100)6", "RESULT6", "CREDITS6", "GRADE6", "SUBJECT_CODE7", "SUBJECT_TITLE7", "INTERNAL_MARKS(30M)7", "EXTERNAL_MARKS(70M)7", "TOTAL_MARKS(100)7", "RESULT7", "CREDITS7", "GRADE7", "SUBJECT_CODE8", "SUBJECT_TITLE8", "INTERNAL_MARKS(30M)8", "EXTERNAL_MARKS(70M)8", "TOTAL_MARKS(100)8", "RESULT8", "CREDITS8", "GRADE8", "SUBJECT_CODE9", "SUBJECT_TITLE9", "INTERNAL_MARKS(30M)9", "EXTERNAL_MARKS(70M)9", "TOTAL_MARKS(100)9", "RESULT9", "CREDITS9", "GRADE9", "SUBJECT_CODE10", "SUBJECT_TITLE10", "INTERNAL_MARKS(30M)10", "EXTERNAL_MARKS(70M)10", "TOTAL_MARKS(100)10", "RESULT10", "CREDITS10", "GRADE10", "TOTAL_INTERNAL_MARKS(30M)", "TOTAL_EXTERNAL_MARKS(70M)", "TOTAL_TOTAL_MARKS(100)", "TOTAL_CREDITS", "SUBJECTS_REGISTERED", "APPEARED", "PASSED", "AGGREGATE(IN WORDS)", "SGPA", "CGPA", "DATEOF ISSUE");
                        $mismatchColArr=array_diff($rowData1[0], $columnArr);
                        if(count($mismatchColArr)>0){
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                        }
                    }else{
                        return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
                    }
                    
                }
                elseif($dropdown_template_id==1){
                    if($columnsCount1==118){
                        $columnArr=array("UNIQUE ID", "HTNO", "MEMO NO", "APAAR ID", "SERIAL NO", "EXAMINATION", "MONTH & YEAR", "BRANCH ", "NAME OF THE STUDENT", "FATHERS_NAME", "MOTHERS_NAME", "SUBJECT_CODE1", "SUBJECT_TITLE1", "INTERNAL_MARKS(30M)1", "EXTERNAL_MARKS(70M)1", "TOTAL_MARKS(100)1", "RESULT1", "CREDITS1", "GRADE1", "SUBJECT_CODE2", "SUBJECT_TITLE2", "INTERNAL_MARKS(30M)2", "EXTERNAL_MARKS(70M)2", "TOTAL_MARKS(100)2", "RESULT2", "CREDITS2", "GRADE2", "SUBJECT_CODE3", "SUBJECT_TITLE3", "INTERNAL_MARKS(30M)3", "EXTERNAL_MARKS(70M)3", "TOTAL_MARKS(100)3", "RESULT3", "CREDITS3", "GRADE3", "SUBJECT_CODE4", "SUBJECT_TITLE4", "INTERNAL_MARKS(30M)4", "EXTERNAL_MARKS(70M)4", "TOTAL_MARKS(100)4", "RESULT4", "CREDITS4", "GRADE4", "SUBJECT_CODE5", "SUBJECT_TITLE5", "INTERNAL_MARKS(30M)5", "EXTERNAL_MARKS(70M)5", "TOTAL_MARKS(100)5", "RESULT5", "CREDITS5", "GRADE5", "SUBJECT_CODE6", "SUBJECT_TITLE6", "INTERNAL_MARKS(30M)6", "EXTERNAL_MARKS(70M)6", "TOTAL_MARKS(100)6", "RESULT6", "CREDITS6", "GRADE6", "SUBJECT_CODE7", "SUBJECT_TITLE7", "INTERNAL_MARKS(30M)7", "EXTERNAL_MARKS(70M)7", "TOTAL_MARKS(100)7", "RESULT7", "CREDITS7", "GRADE7", "SUBJECT_CODE8", "SUBJECT_TITLE8", "INTERNAL_MARKS(30M)8", "EXTERNAL_MARKS(70M)8", "TOTAL_MARKS(100)8", "RESULT8", "CREDITS8", "GRADE8", "SUBJECT_CODE9", "SUBJECT_TITLE9", "INTERNAL_MARKS(30M)9", "EXTERNAL_MARKS(70M)9", "TOTAL_MARKS(100)9", "RESULT9", "CREDITS9", "GRADE9", "SUBJECT_CODE10", "SUBJECT_TITLE10", "INTERNAL_MARKS(30M)10", "EXTERNAL_MARKS(70M)10", "TOTAL_MARKS(100)10", "RESULT10", "CREDITS10", "GRADE10", "SUBJECT_CODE11", "SUBJECT_TITLE11", "INTERNAL_MARKS(30M)11", "EXTERNAL_MARKS(70M)11", "TOTAL_MARKS(100)11", "RESULT11", "CREDITS11", "GRADE11", "SUBJECT_CODE12", "SUBJECT_TITLE12", "INTERNAL_MARKS(30M)12", "EXTERNAL_MARKS(70M)12", "TOTAL_MARKS(100)12", "RESULT12", "CREDITS12", "GRADE12", "TOTAL_INTERNAL_MARKS(30M)", "TOTAL_EXTERNAL_MARKS(70M)", "TOTAL_TOTAL_MARKS(100)", "TOTAL_CREDITS", "SUBJECTS_REGISTERED", "APPEARED", "PASSED", "AGGREGATE(IN WORDS)", "SGPA", "CGPA", "DATEOF ISSUE");
                        $mismatchColArr=array_diff($rowData1[0], $columnArr);
                        if(count($mismatchColArr)>0){
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet2/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                        }
                    }else{
                        return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
                    }
                    
                }
                
            
            
                $ab = array_count_values($rowData1[0]);

                $duplicate_columns = '';
                foreach ($ab as $key => $value) {
                    
                    if($value > 1){

                        if($duplicate_columns != ''){

                            $duplicate_columns .= ", ".$key;
                        }else{
                            
                            $duplicate_columns .= $key;
                        }
                    }
                }

                if($duplicate_columns != ''){
                    // Excel has more than 1 column having same name. i.e. <column name>
                    $message = Array('success'=>false,'type' => 'error', 'message' => 'Excel/Json has more than 1 column having same name. i.e. : '.$duplicate_columns);
                    return json_encode($message);
                }
            
                unset($rowData1[0]);
                $rowData1=array_values($rowData1);
                $blobArr=array();
                
                
                $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
                $old_rows = 0;
                $new_rows = 0;
                foreach ($rowData1 as $key1 => $value1) {

                    $serial_no=$value1[0];
                    array_push($blobArr, $serial_no);
                    
                    if($sandboxCheck['sandboxing'] == 1){
                       
                        $studentTableCounts = SbStudentTable::where('serial_no',$serial_no)->where('site_id',$auth_site_id)->count();
                       
                        $studentTablePrefixCounts = SbStudentTable::where('serial_no','T-'.$serial_no)->where('site_id',$auth_site_id)->count();
                        if($studentTableCounts > 0){
                            $old_rows += 1;
                        }else if($studentTablePrefixCounts){
                            
                            $old_rows += 1;
                        }else{
                            $new_rows += 1;
                        }

                    }else{
                        $studentTableCounts = StudentTable::where('serial_no',$serial_no)->where('site_id',$auth_site_id)->count();
                        // dd($studentTableCounts);
                        if($studentTableCounts > 0){
                            $old_rows += 1;
                        }else{
                            $new_rows += 1;
                        }
                    }   
                }
               

                $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));
                //   dd( $mismatchArr);    
                if(count($mismatchArr)>0){
                    return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Unique Id contains following duplicate values : '.implode(',', $mismatchArr)]);
                }else{
               
                    return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);
                }
    }

  
}
