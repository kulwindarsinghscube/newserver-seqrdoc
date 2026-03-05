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

class ValidateExcelCSCACSJob
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
        
        
        //exit;
       
        if($dropdown_template_id==1){
        //    if($columnsCount1==115){
        //         // $photo_col=354;
		// 		$columnArr=array("Unique ID","PROGRAMME","SEMESTER","ACADEMIC YEAR","STUDENT ID","PRN No.","Examination Seat No.","Name of the Learner","Month & Year of Examination","COURSE CODE_1","COURSE TITLE_1","Course Credit_1","CIA Max_1",
        //         "CIA Min_1","CIA Obt_1","SEM Max_1","SEM Min_1","SEM Obt_1","Total Max_1","Total Min_1","Total Obt_1","Grade_1","Grade Points_1","Credits Earned_1","CG_1","COURSE CODE_2","COURSE TITLE_2","Course Credit_2","CIA Max_2",
        //         "CIA Min_2","CIA Obt_2","SEM Max_2","SEM Min_2","SEM Obt_2","Total Max_2","Total Min_2","Total Obt_2","Grade_2","Grade Points_2","Credits Earned_2","CG_2","COURSE CODE_3","COURSE TITLE_3","Course Credit_3","CIA Max_3",
        //         "CIA Min_3","CIA Obt_3","SEM Max_3","SEM Min_3","SEM Obt_3","Total Max_3","Total Min_3","Total Obt_3","Grade_3","Grade Points_3","Credits Earned_3","CG_3","COURSE CODE_4","COURSE TITLE_4","Course Credit_4","CIA Max_4",
        //         "CIA Min_4","CIA Obt_4","SEM Max_4","SEM Min_4","SEM Obt_4","Total Max_4","Total Min_4","Total Obt_4","Grade_4","Grade Points_4","Credits Earned_4","CG_4","COURSE CODE_5","COURSE TITLE_5","Course Credit_5","CIA Max_5",
        //         "CIA Min_5","CIA Obt_5","SEM Max_5","SEM Min_5","SEM Obt_5","Total Max_5","Total Min_5","Total Obt_5","Grade_5","Grade Points_5","Credits Earned_5","CG_5","COURSE CODE_6","COURSE TITLE_6","Course Credit_6","CIA Max_6",
        //         "CIA Min_6","CIA Obt_6","SEM Max_6","SEM Min_6","SEM Obt_6","Total Max_6","Total Min_6","Total Obt_6","Grade_6","Grade Points_6","Credits Earned_6","CG_6","SGPI","Result","Credits Earned","Total","ΣC","ΣCG","Place","Print Date","Result Date","PHOTO");
        //         $mismatchColArr=array_diff($rowData1[0], $columnArr);
             if($columnsCount1==344){
                // $photo_col=354;
				$columnArr=array("Unique ID","PROGRAMME","SEMESTER","ACADEMIC YEAR","STUDENT ID","PRN No.","Examination Seat No.","Name of the Learner","Month & Year of Examination","COURSE CODE_1","COURSE TITLE_1","Course Credit_1","CIA Max_1",
                "CIA Min_1","CIA Obt_1","SEM Max_1","SEM Min_1","SEM Obt_1","Total Max_1","Total Min_1","Total Obt_1","Grade_1","Grade Points_1","Credits Earned_1","CG_1","COURSE CODE_2","COURSE TITLE_2","Course Credit_2","CIA Max_2",
                "CIA Min_2","CIA Obt_2","SEM Max_2","SEM Min_2","SEM Obt_2","Total Max_2","Total Min_2","Total Obt_2","Grade_2","Grade Points_2","Credits Earned_2","CG_2","COURSE CODE_3","COURSE TITLE_3","Course Credit_3","CIA Max_3",
                "CIA Min_3","CIA Obt_3","SEM Max_3","SEM Min_3","SEM Obt_3","Total Max_3","Total Min_3","Total Obt_3","Grade_3","Grade Points_3","Credits Earned_3","CG_3","COURSE CODE_4","COURSE TITLE_4","Course Credit_4","CIA Max_4",
                "CIA Min_4","CIA Obt_4","SEM Max_4","SEM Min_4","SEM Obt_4","Total Max_4","Total Min_4","Total Obt_4","Grade_4","Grade Points_4","Credits Earned_4","CG_4","COURSE CODE_5","COURSE TITLE_5","Course Credit_5","CIA Max_5",
                "CIA Min_5","CIA Obt_5","SEM Max_5","SEM Min_5","SEM Obt_5","Total Max_5","Total Min_5","Total Obt_5","Grade_5","Grade Points_5","Credits Earned_5","CG_5","COURSE CODE_6","COURSE TITLE_6","Course Credit_6","CIA Max_6",
                "CIA Min_6","CIA Obt_6","SEM Max_6","SEM Min_6","SEM Obt_6","Total Max_6","Total Min_6","Total Obt_6","Grade_6","Grade Points_6","Credits Earned_6","CG_6","COURSE CODE_7","COURSE TITLE_7","Course Credit_7","CIA Max_7",
                "CIA Min_7","CIA Obt_7","SEM Max_7","SEM Min_7","SEM Obt_7","Total Max_7","Total Min_7","Total Obt_7","Grade_7","Grade Points_7","Credits Earned_7","CG_7","COURSE CODE_8","COURSE TITLE_8","Course Credit_8","CIA Max_8",
                "CIA Min_8","CIA Obt_8","SEM Max_8","SEM Min_8","SEM Obt_8","Total Max_8","Total Min_8","Total Obt_8","Grade_8","Grade Points_8","Credits Earned_8","CG_8","COURSE CODE_9","COURSE TITLE_9","Course Credit_9","CIA Max_9",
                "CIA Min_9","CIA Obt_9","SEM Max_9","SEM Min_9","SEM Obt_9","Total Max_9","Total Min_9","Total Obt_9","Grade_9","Grade Points_9","Credits Earned_9","CG_9","COURSE CODE_10","COURSE TITLE_10","Course Credit_10","CIA Max_10",
                "CIA Min_10","CIA Obt_10","SEM Max_10","SEM Min_10","SEM Obt_10","Total Max_10","Total Min_10","Total Obt_10","Grade_10","Grade Points_10","Credits Earned_10","CG_10","COURSE CODE_11","COURSE TITLE_11","Course Credit_11","CIA Max_11",
                "CIA Min_11","CIA Obt_11","SEM Max_11","SEM Min_11","SEM Obt_11","Total Max_11","Total Min_11","Total Obt_11","Grade_11","Grade Points_11","Credits Earned_11","CG_11","SGPI","Result","Credits Earned","Total","ΣC","ΣCG","Place","Print Date","Result Date","PHOTO","Total_MO");

                $columnArr=array("ORG_CODE","ORG_NAME","ORG_NAME_L","ACADEMIC_COURSE_ID","COURSE_NAME","COURSE_NAME_L","STREAM","STREAM_L","SESSION","STUDENTID","REGN_NO","RROLL","Exam_Seat_Number","CNAME","AADHAAR_NAME","GENDER","DOB","FNAME","MNAME","GNAME","BLOOD_GROUP","RELIGION","CASTENAME","NATIONALITY","PH","MOBILE","EMAIL","PHOTO","STUDENT_ADDRESS","MRKS_REC_STATUS","RESULT","YEAR","MONTH","DIVISION","GRADE","PERCENT","SEM","EXAM_TYPE","TOT","TOT_MIN","TOT_MRKS","TOT_CREDIT","TOT_CREDIT_POINTS","TOT_GRADE_POINTS","GRAND_TOT_MAX","GRAND_TOT_MIN","GRAND_TOT_MRKS","GRAND_TOT_CREDIT_POINTS","CGPA","REMARKS","SGPA","ABC_ACCOUNT_ID","TERM_TYPE","TOT_GRADE","SUB1NM","SUB1","SUB1MAX","SUB1MIN","SUB1_TH_MAX","SUB1_TH_MIN","SUB1_CE_MAX","SUB1_CE_MIN","SUB1_TH_MRKS","SUB1_PR_MRKS","SUB1_CE_MRKS","SUB1_TOT","SUB1_STATUS","SUB1_PR_MAX","SUB1_PR_MIN","SUB1_GRADE","SUB1_GRADE_POINTS","SUB1_CREDIT","SUB1_CREDIT_POINTS","SUB1_GRACE","SUB1_REMARKS","SUB1_CREDIT_ELIGIBILITY","SUB2NM","SUB2","SUB2MAX","SUB2MIN","SUB2_TH_MAX","SUB2_TH_MIN","SUB2_CE_MAX","SUB2_CE_MIN","SUB2_TH_MRKS","SUB2_PR_MRKS","SUB2_CE_MRKS","SUB2_TOT","SUB2_STATUS","SUB2_PR_MAX","SUB2_PR_MIN","SUB2_GRADE","SUB2_GRADE_POINTS","SUB2_CREDIT","SUB2_CREDIT_POINTS","SUB2_GRACE","SUB2_REMARKS","SUB2_CREDIT_ELIGIBILITY","SUB3NM","SUB3","SUB3MAX","SUB3MIN","SUB3_TH_MAX","SUB3_TH_MIN","SUB3_CE_MAX","SUB3_CE_MIN","SUB3_TH_MRKS", "SUB3_PR_MRKS","SUB3_CE_MRKS","SUB3_TOT","SUB3_STATUS","SUB3_PR_MAX","SUB3_PR_MIN","SUB3_GRADE","SUB3_GRADE_POINTS","SUB3_CREDIT","SUB3_CREDIT_POINTS","SUB3_GRACE","SUB3_REMARKS","SUB3_CREDIT_ELIGIBILITY","SUB4NM","SUB4","SUB4MAX","SUB4MIN","SUB4_TH_MAX","SUB4_TH_MIN","SUB4_CE_MAX","SUB4_CE_MIN","SUB4_TH_MRKS","SUB4_PR_MRKS","SUB4_CE_MRKS","SUB4_TOT","SUB4_STATUS","SUB4_PR_MAX","SUB4_PR_MIN","SUB4_GRADE","SUB4_GRADE_POINTS","SUB4_CREDIT","SUB4_CREDIT_POINTS","SUB4_GRACE","SUB4_REMARKS","SUB4_CREDIT_ELIGIBILITY","SUB5NM","SUB5","SUB5MAX","SUB5MIN","SUB5_TH_MAX","SUB5_TH_MIN","SUB5_CE_MAX","SUB5_CE_MIN","SUB5_TH_MRKS","SUB5_PR_MRKS","SUB5_CE_MRKS","SUB5_TOT","SUB5_STATUS","SUB5_PR_MAX","SUB5_PR_MIN","SUB5_GRADE","SUB5_GRADE_POINTS","SUB5_CREDIT","SUB5_CREDIT_POINTS","SUB5_GRACE","SUB5_REMARKS","SUB5_CREDIT_ELIGIBILITY","SUB6NM","SUB6","SUB6MAX","SUB6MIN","SUB6_TH_MAX","SUB6_TH_MIN","SUB6_CE_MAX","SUB6_CE_MIN","SUB6_TH_MRKS","SUB6_PR_MRKS","SUB6_CE_MRKS","SUB6_TOT","SUB6_STATUS","SUB6_PR_MAX","SUB6_PR_MIN","SUB6_GRADE","SUB6_GRADE_POINTS","SUB6_CREDIT","SUB6_CREDIT_POINTS","SUB6_GRACE","SUB6_REMARKS","SUB6_CREDIT_ELIGIBILITY","SUB7NM","SUB7","SUB7MAX","SUB7MIN","SUB7_TH_MAX","SUB7_TH_MIN","SUB7_CE_MAX","SUB7_CE_MIN","SUB7_TH_MRKS","SUB7_PR_MRKS","SUB7_CE_MRKS","SUB7_TOT","SUB7_STATUS","SUB7_PR_MAX","SUB7_PR_MIN","SUB7_GRADE","SUB7_GRADE_POINTS","SUB7_CREDIT","SUB7_CREDIT_POINTS","SUB7_GRACE","SUB7_REMARKS","SUB7_CREDIT_ELIGIBILITY","SUB8NM","SUB8","SUB8MAX","SUB8MIN","SUB8_TH_MAX","SUB8_TH_MIN","SUB8_CE_MAX","SUB8_CE_MIN","SUB8_TH_MRKS","SUB8_PR_MRKS","SUB8_CE_MRKS","SUB8_TOT","SUB8_STATUS","SUB8_PR_MAX","SUB8_PR_MIN","SUB8_GRADE","SUB8_GRADE_POINTS","SUB8_CREDIT","SUB8_CREDIT_POINTS","SUB8_GRACE","SUB8_REMARKS","SUB8_CREDIT_ELIGIBILITY","SUB9NM","SUB9","SUB9MAX","SUB9MIN","SUB9_TH_MAX","SUB9_TH_MIN","SUB9_CE_MAX","SUB9_CE_MIN","SUB9_TH_MRKS","SUB9_PR_MRKS","SUB9_CE_MRKS","SUB9_TOT","SUB9_STATUS","SUB9_PR_MAX","SUB9_PR_MIN","SUB9_GRADE","SUB9_GRADE_POINTS","SUB9_CREDIT","SUB9_CREDIT_POINTS","SUB9_GRACE","SUB9_REMARKS","SUB9_CREDIT_ELIGIBILITY","SUB10NM","SUB10","SUB10MAX","SUB10MIN","SUB10_TH_MAX","SUB10_TH_MIN","SUB10_CE_MAX","SUB10_CE_MIN","SUB10_TH_MRKS","SUB10_PR_MRKS","SUB10_CE_MRKS","SUB10_TOT","SUB10_STATUS","SUB10_PR_MAX","SUB10_PR_MIN","SUB10_GRADE","SUB10_GRADE_POINTS","SUB10_CREDIT","SUB10_CREDIT_POINTS","SUB10_GRACE","SUB10_REMARKS","SUB10_CREDIT_ELIGIBILITY","SUB11NM","SUB11","SUB11MAX","SUB11MIN","SUB11_TH_MAX","SUB11_TH_MIN","SUB11_CE_MAX","SUB11_CE_MIN","SUB11_TH_MRKS","SUB11_PR_MRKS","SUB11_CE_MRKS","SUB11_TOT","SUB11_STATUS","SUB11_PR_MAX","SUB11_PR_MIN","SUB11_GRADE","SUB11_GRADE_POINTS","SUB11_CREDIT","SUB11_CREDIT_POINTS","SUB11_GRACE","SUB11_REMARKS","SUB11_CREDIT_ELIGIBILITY","SUB12NM","SUB12","SUB12MAX","SUB12MIN","SUB12_TH_MAX","SUB12_TH_MIN","SUB12_CE_MAX","SUB12_CE_MIN","SUB12_TH_MRKS","SUB12_PR_MRKS","SUB12_CE_MRKS","SUB12_TOT","SUB12_STATUS","SUB12_PR_MAX","SUB12_PR_MIN","SUB12_GRADE","SUB12_GRADE_POINTS","SUB12_CREDIT","SUB12_CREDIT_POINTS","SUB12_GRACE","SUB12_REMARKS","SUB12_CREDIT_ELIGIBILITY","TOT_TH_MAX","TOT_TH_MIN","TOT_TH_MRKS","TOT_PR_MAX","TOT_PR_MIN","TOT_PR_MRKS","TOT_CE_MAX","TOT_CE_MIN","TOT_CE_MRKS","CERT_NO","DOI","DEPARTMENT","Admission_Year","Course_Term","Course_Pattern","AdmissionFormNo","Branch","Category","Stud_Taluka","Stud_District","Stud_State","Medium","Marital Status","Name 10th Marksheet","Date Of Result","Date Of Issue");

                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }

         elseif($dropdown_template_id == 2) {
                    if ($columnsCount1 == 9) {
                    // dd($columnsCount1);
                $columnArr = array("REF NO","SEAT NO","STUDENT NAME","COURSE NAME","COMPLETION DATE","GRADE","DATE","CGPA","PHOTO");


                        $mismatchColArr = array_diff($rowData1[0], $columnArr);

                        if (!empty($mismatchColArr)) {

                            return response()->json(['success' => false,'type'    => 'error','message' => 'Sheet1 : Column names not matching. Please check columns : ' . implode(', ', $mismatchColArr)]);}

                    } else {
                        return response()->json(['success' => false,'type'    => 'error','message' => 'Columns count of excel do not match for Template 2!']);
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

            $serial_no=$value1[0];
            array_push($blobArr, $serial_no);
            // array_push($profArr, $value1[141]);
			if($dropdown_template_id==3){
				$profile_path = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$value1[$photo_col];
				if (!file_exists($profile_path)) {   
					 array_push($profErrArr, $value1[$photo_col]);
				}
			}
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
            //   dd(count($mismatchArr));
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

