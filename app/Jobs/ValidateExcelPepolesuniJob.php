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

class ValidateExcelPepolesuniJob
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

        $rowData1=$pdf_data['rowData1'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
        $rowData1[0] = array_filter($rowData1[0]);
        $columnsCount1=count($rowData1[0]);
        
        //echo $columnsCount1;
        //exit;
   
        if($dropdown_template_id==1){
            if($columnsCount1 == 140){
                $columnArr=array("EnrollmentNo", "statement", "MarksheetNo", "Marksheet_Issue_Date", "NameofStudent", "FathersName", "MothersName", "ProgramName", "BranchSpecialization", "Semeter", "ExaminationMonthYear", "Status", "Institute", "Alloted_Credit_Point_Total", "Earned_Credit_Point_Total", "Result", "SGPA", "Paper_Code_1", "Paper_Name_1", "Credit_Alloted_Th_1", "Credit_Alloted_Pr_1", "Credit_Earned_Th_1", "Credit_Earned_Pr_1", "Grade_Th_1", "Grade_Pr_1", "Paper_Code_2", "Paper_Name_2", "Credit_Alloted_Th_2", "Credit_Alloted_Pr_2", "Credit_Earned_Th_2", "Credit_Earned_Pr_2", "Grade_Th_2", "Grade_Pr_2", "Paper_Code_3", "Paper_Name_3", "Credit_Alloted_Th_3", "Credit_Alloted_Pr_3", "Credit_Earned_Th_3", "Credit_Earned_Pr_3", "Grade_Th_3", "Grade_Pr_3", "Paper_Code_4", "Paper_Name_4", "Credit_Alloted_Th_4", "Credit_Alloted_Pr_4", "Credit_Earned_Th_4", "Credit_Earned_Pr_4", "Grade_Th_4", "Grade_Pr_4", "Paper_Code_5", "Paper_Name_5", "Credit_Alloted_Th_5", "Credit_Alloted_Pr_5", "Credit_Earned_Th_5", "Credit_Earned_Pr_5", "Grade_Th_5", "Grade_Pr_5", "Paper_Code_6", "Paper_Name_6", "Credit_Alloted_Th_6", "Credit_Alloted_Pr_6", "Credit_Earned_Th_6", "Credit_Earned_Pr_6", "Grade_Th_6", "Grade_Pr_6", "Paper_Code_7", "Paper_Name_7", "Credit_Alloted_Th_7", "Credit_Alloted_Pr_7", "Credit_Earned_Th_7", "Credit_Earned_Pr_7", "Grade_Th_7", "Grade_Pr_7", "Paper_Code_8", "Paper_Name_8", "Credit_Alloted_Th_8", "Credit_Alloted_Pr_8", "Credit_Earned_Th_8", "Credit_Earned_Pr_8", "Grade_Th_8", "Grade_Pr_8", "Paper_Code_9", "Paper_Name_9", "Credit_Alloted_Th_9", "Credit_Alloted_Pr_9", "Credit_Earned_Th_9", "Credit_Earned_Pr_9", "Grade_Th_9", "Grade_Pr_9", "Paper_Code_10", "Paper_Name_10", "Credit_Alloted_Th_10", "Credit_Alloted_Pr_10", "Credit_Earned_Th_10", "Credit_Earned_Pr_10", "Grade_Th_10", "Grade_Pr_10", "Paper_Code_11", "Paper_Name_11", "Credit_Alloted_Th_11", "Credit_Alloted_Pr_11", "Credit_Earned_Th_11", "Credit_Earned_Pr_11", "Grade_Th_11", "Grade_Pr_11", "Paper_Code_12", "Paper_Name_12", "Credit_Alloted_Th_12", "Credit_Alloted_Pr_12", "Credit_Earned_Th_12", "Credit_Earned_Pr_12", "Grade_Th_12", "Grade_Pr_12", "Exam_Sem-I", "SGPA_Sem-I", "Result_I", "Exam_Sem-II", "SGPA_Sem-II", "Result_II", "Exam_Sem-III", "SGPA_Sem-III", "Result_III", "Exam_Sem-IV", "SGPA_Sem-IV", "Result_IV", "Exam_Sem-V", "SGPA_Sem-V", "Result_V", "Exam_Sem-VI", "SGPA_Sem-VI", "Result_VI", "Exam_Sem-VII", "SGPA_Sem-VII", "Result_VII", "Exam_Sem-VIII", "SGPA_Sem-VIII", "Result_VIII", "CGPA_Final", "Overall_Result", "Division_Final");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                // dd($mismatchColArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }elseif($dropdown_template_id==2){
            if($columnsCount1==14){
                $columnArr=array("Sr. No.","Enrollment No","Name of Student","Name of Student_Hindi","Course Name","Coursename_Hindi","Division","Division_Hindi","Passing Year","Exam_Result_MonthYear","Institute Name","Remarks ","Result Notification","DEGREE S.NO.");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }
        elseif($dropdown_template_id==3){
           
            if($columnsCount1==17){
                $columnArr=array("S.No","Enrollment No.","Name of Candidate","Name of Student_Hindi","Subject","Subject Name_Hindi","Faculty","Faculty_Hindi","Supervisor","Co-Supervisor","Topic of Research","Date of Registration","Registration No.","Date of Thesis Submission","Date of Oral defence","UGC Approval","DEGREE S.NO.");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        } 
        elseif($dropdown_template_id==4){
           
            if($columnsCount1==10){
                $columnArr=array("S.No","Date","Provisional No","Batch","Enrollment No","Name","Fathers Name","Course","Institute","Passing Year");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }  

        elseif($dropdown_template_id==5){
           
            if($columnsCount1==9){
                $columnArr=array("S. No","Date","Migration No","Batch","Enrollment No","Name","Fathers Name","Course","Institute");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
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
            //return json_encode($message);
        }
        
        unset($rowData1[0]);
        $rowData1=array_values($rowData1);
        $blobArr=array();
        $profArr=array();
        $profErrArr=array();
        /*foreach ($rowData1 as $readblob) {
            array_push($blobArr, $readblob[23]);
        }*/

        $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        $old_rows = 0;
        $new_rows = 0;
        foreach ($rowData1 as $key1 => $value1) {

            if($dropdown_template_id==1){
                $serial_no=$value1[2];
            }elseif($dropdown_template_id==2 || $dropdown_template_id==3){
                $serial_no = $value1[1];
            }elseif($dropdown_template_id==4 || $dropdown_template_id==5 ){
                $serial_no = $value1[4];
            }
            array_push($blobArr, $serial_no);
            // array_push($profArr, $value1[141]);

            /*$profile_path = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$value1[$photo_col];
            if (!file_exists($profile_path)) {   
                 //array_push($profErrArr, $value1[$photo_col]);
            }*/
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
                if($studentTableCounts > 0){
                    $old_rows += 1;
                }else{
                    $new_rows += 1;
                }
            }   
        }

       
        $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));
        if(count($mismatchArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Unique Id contains following duplicate values : '.implode(',', $mismatchArr)]);
        }
        /*  $mismatchProfArr = array_intersect($profArr, array_unique(array_diff_key($profArr, array_unique($profArr))));

        if(count($mismatchProfArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo contains following duplicate values : '.implode(',', $mismatchProfArr)]);
        }*/
        if(count($profErrArr)>0){
            //return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo does not exists following values : '.implode(',', $profErrArr)]);
        }
              
        return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);
    }

  
}

