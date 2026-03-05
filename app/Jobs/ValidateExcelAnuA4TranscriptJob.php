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

class ValidateExcelAnuA4TranscriptJob
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
        $studentData=$pdf_data['studentData'];
        $subjectData=$pdf_data['subjectData'];

        $auth_site_id=$pdf_data['auth_site_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
        $rowData1[0] = array_filter($rowData1[0]);
        $columnsCount1=count($rowData1[0]);
        
        //echo $columnsCount1;
        //exit;
       
       
        if($columnsCount1==247){
            $photo_col=127;
            $columnArr=array("ORG_NAME","ACADEMIC_COURSE_ID","COURSE_NAME","STREAM","SESSION","REGN_NO","RROLL","CNAME","GENDER","DOB","FNAME","MNAME","MRKS_REC_STATUS","RESULT","YEAR","MONTH","PERCENT","DOI","SEM","EXAM_TYPE","TOT_MRKS","TOT_CREDIT","TOT_CREDIT_POINTS","TOT_GRADE_POINTS","GRAND_TOT_MRKS","GRAND_TOT_CREDIT_POINTS","GRAND_TOT_CREDIT","CGPA","OGPA","SGPA","TOT_GRADE","GRAND_TOT_GRADE","SUB1NM","SUB1","SUB1MAX","SUB1_PR_MAX","SUB1_CE_MAX","SUB1_TH_MRKS","SUB1_PR_MRKS","SUB1_CE_MRKS","SUB1_GRADE","SUB1_GRADE_POINTS","SUB1_CREDIT","SUB1_CREDIT_POINTS","SUB1_REMARKS","AADHAAR_NAME","SUB2NM","SUB2","SUB2MAX","SUB2_PR_MAX","SUB2_CE_MAX","SUB2_TH_MRKS","SUB2_PR_MRKS","SUB2_CE_MRKS","SUB2_GRADE","SUB2_GRADE_POINTS","SUB2_CREDIT","SUB2_CREDIT_POINTS","SUB2_REMARKS","SUB3NM","SUB3","SUB3MAX","SUB3_PR_MAX","SUB3_CE_MAX","SUB3_TH_MRKS","SUB3_PR_MRKS","SUB3_CE_MRKS","SUB3_GRADE","SUB3_GRADE_POINTS","SUB3_CREDIT","SUB3_CREDIT_POINTS","SUB3_REMARKS","SUB4NM","SUB4","SUB4MAX","SUB4_PR_MAX","SUB4_CE_MAX","SUB4_TH_MRKS","SUB4_PR_MRKS","SUB4_CE_MRKS","SUB4_GRADE","SUB4_GRADE_POINTS","SUB4_CREDIT","SUB4_CREDIT_POINTS","SUB4_REMARKS","SUB5NM","SUB5","SUB5MAX","SUB5_PR_MAX","SUB5_CE_MAX","SUB5_TH_MRKS","SUB5_PR_MRKS","SUB5_CE_MRKS","SUB5_GRADE","SUB5_GRADE_POINTS","SUB5_CREDIT","SUB5_CREDIT_POINTS","SUB5_REMARKS","SUB6NM","SUB6","SUB6MAX","SUB6_PR_MAX","SUB6_CE_MAX","SUB6_TH_MRKS","SUB6_PR_MRKS","SUB6_CE_MRKS","SUB6_GRADE","SUB6_GRADE_POINTS","SUB6_CREDIT","SUB6_CREDIT_POINTS","SUB6_REMARKS","SUB7NM","SUB7","SUB7MAX","SUB7_PR_MAX","SUB7_CE_MAX","SUB7_TH_MRKS","SUB7_PR_MRKS","SUB7_CE_MRKS","SUB7_GRADE","SUB7_GRADE_POINTS","SUB7_CREDIT","SUB7_CREDIT_POINTS","SUB7_REMARKS","SUB8NM","SUB8","SUB8MAX","SUB8_PR_MAX","SUB8_CE_MAX","SUB8_TH_MRKS","SUB8_PR_MRKS","SUB8_CE_MRKS","SUB8_GRADE","SUB8_GRADE_POINTS","SUB8_CREDIT","SUB8_CREDIT_POINTS","SUB8_REMARKS","SUB9NM","SUB9","SUB9MAX","SUB9_PR_MAX","SUB9_CE_MAX","SUB9_TH_MRKS","SUB9_PR_MRKS","SUB9_CE_MRKS","SUB9_GRADE","SUB9_GRADE_POINTS","SUB9_CREDIT","SUB9_CREDIT_POINTS","SUB9_REMARKS","SUB10NM","SUB10","SUB10MAX","SUB10_PR_MAX","SUB10_CE_MAX","SUB10_TH_MRKS","SUB10_PR_MRKS","SUB10_CE_MRKS","SUB10_GRADE","SUB10_GRADE_POINTS","SUB10_CREDIT","SUB10_CREDIT_POINTS","SUB10_REMARKS","SUB11NM","SUB11","SUB11MAX","SUB11_PR_MAX","SUB11_CE_MAX","SUB11_TH_MRKS","SUB11_PR_MRKS","SUB11_CE_MRKS","SUB11_GRADE","SUB11_GRADE_POINTS","SUB11_CREDIT","SUB11_CREDIT_POINTS","SUB11_REMARKS","SUB12NM","SUB12","SUB12MAX","SUB12_PR_MAX","SUB12_CE_MAX","SUB12_TH_MRKS","SUB12_PR_MRKS","SUB12_CE_MRKS","SUB12_GRADE","SUB12_GRADE_POINTS","SUB12_CREDIT","SUB12_CREDIT_POINTS","SUB12_REMARKS","SUB13NM","SUB13","SUB13MAX","SUB13_PR_MAX","SUB13_CE_MAX","SUB13_TH_MRKS","SUB13_PR_MRKS","SUB13_CE_MRKS","SUB13_GRADE","SUB13_GRADE_POINTS","SUB13_CREDIT","SUB13_CREDIT_POINTS","SUB13_REMARKS","SUB14NM","SUB14","SUB14MAX","SUB14_PR_MAX","SUB14_CE_MAX","SUB14_TH_MRKS","SUB14_PR_MRKS","SUB14_CE_MRKS","SUB14_GRADE","SUB14_GRADE_POINTS","SUB14_CREDIT","SUB14_CREDIT_POINTS","SUB14_REMARKS","SUB15NM","SUB15","SUB15MAX","SUB15_PR_MAX","SUB15_CE_MAX","SUB15_TH_MRKS","SUB15_PR_MRKS","SUB15_CE_MRKS","SUB15_GRADE","SUB15_GRADE_POINTS","SUB15_CREDIT","SUB15_CREDIT_POINTS","SUB15_REMARKS","REMARKS","Session_Ref","Percent_Q","Semester_S","GRAND_TOT_CREDIT_POINTS_Z","GRAND_TOT_CREDIT_AA","REMARKS_HU","PHOTO","sign_name1","designation1","role1","sign_name2","designation2","role2","sign_name3","designation3","role3","minor","cgpa_description");
                            
            

            $mismatchColArr=array_diff($rowData1[0], $columnArr);
            if(count($mismatchColArr)>0){                    
                return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
            }
        }else{
            return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
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
        $studentArr=array();
        $blobArr=array();
        $profArr=array();
        $profErrArr=array();
        $maxSubArr=array();
        /*foreach ($rowData1 as $readblob) {
            array_push($blobArr, $readblob[23]);
        }*/

        $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        $old_rows = 0;
        $new_rows = 0;
        // echo "<pre>";
        // print_r($studentData);
        // echo "</pre>";
        // die();

        foreach ($studentData as $key1 => $value1) {

            $serial_no=$value1[6];
            

            array_push($blobArr, $serial_no);
            //array_push($studentArr, $serial_no);
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
        // if (in_array($blobArr[0], $subjectData)) {
        //     print_r($subjectData);
        // }
        foreach($blobArr as $roleId) {
            $subCount = 0;
            //echo "<pre>";
            ///print_r($subjectData);
            //echo "</pre>";
            //echo "<br>";
            foreach ($subjectData as $key2 => $value2) {
                

                if($value2[0] == $roleId){
                    if(!empty($value2[10])) {
                        $subCount ++;
                    }
                }
            }
            // echo $subCount;
            // echo "<br>";
            if($subCount >40) {
                array_push($maxSubArr, $roleId);
            }
            
            
        }

        if(count($maxSubArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : this role no subject count is more than 40 rows : '.implode(',', $maxSubArr)]);
        }
    
    

       
        $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));
              
        // if(count($mismatchArr)>0){
        //     return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Unique Id contains following duplicate values : '.implode(',', $mismatchArr)]);
        // }

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

