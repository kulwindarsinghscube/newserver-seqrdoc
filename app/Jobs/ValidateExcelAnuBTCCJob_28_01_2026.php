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
class ValidateExcelAnuBTCCJob
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
            // dd(    $rowData1[0] );
        //echo $columnsCount1;
        //exit;
    //    dd($rowData1[0]);
        if($dropdown_template_id==7){
            // if($columnsCount1==19){
                $photo_col=132;
				$columnArr=array('Unique_ID', 'REGN_NO', 'CNAME', 'COURSE_NAME', 'STREAM', 'D_O_B', 'PHOTO','Name1','Designation1','Name2','Designation2','QR_Code_No','SEM','TOT_CREDIT_POINTS','GRAND_TOT_CREDIT_MAX','TOT_CREDIT_MAX','TOT_CREDIT','GRAND_TOT_CREDIT_POINTS','GRAND_TOT_CREDIT','CGPA','REMARKS','SGPA','ABC_ACCOUNT_ID','TERM_TYPE','SGPA _Desc','ADMISSION_YEAR','QR_Output','Issue_date',
                'SUB1NM','SUB1','SUB1_GRADE','SUB1_GRADE_POINTS','SUB1_CREDIT_MAX','SUB1_CREDIT','SUB1_CREDIT_POINTS','SUB2NM','SUB2','SUB2_GRADE','SUB2_GRADE_POINTS','SUB2_CREDIT_MAX','SUB2_CREDIT','SUB2_CREDIT_POINTS','SUB3NM','SUB3','SUB3_GRADE','SUB3_GRADE_POINTS','SUB3_CREDIT_MAX','SUB3_CREDIT','SUB3_CREDIT_POINTS','SUB4NM','SUB4','SUB4_GRADE','SUB4_GRADE_POINTS','SUB4_CREDIT_MAX','SUB4_CREDIT','SUB4_CREDIT_POINTS','SUB5NM','SUB5','SUB5_GRADE','SUB5_GRADE_POINTS','SUB5_CREDIT_MAX','SUB5_CREDIT','SUB5_CREDIT_POINTS','SUB6NM','SUB6','SUB6_GRADE','SUB6_GRADE_POINTS','SUB6_CREDIT_MAX','SUB6_CREDIT','SUB6_CREDIT_POINTS','SUB7NM','SUB7','SUB7_GRADE','SUB7_GRADE_POINTS','SUB7_CREDIT_MAX','SUB7_CREDIT','SUB7_CREDIT_POINTS','SUB8NM','SUB8','SUB8_GRADE','SUB8_GRADE_POINTS','SUB8_CREDIT_MAX','SUB8_CREDIT','SUB8_CREDIT_POINTS','SUB9NM','SUB9','SUB9_GRADE','SUB9_GRADE_POINTS','SUB9_CREDIT_MAX','SUB9_CREDIT','SUB9_CREDIT_POINTS','SUB10NM','SUB10','SUB10_GRADE','SUB10_GRADE_POINTS','SUB10_CREDIT_MAX','SUB10_CREDIT','SUB10_CREDIT_POINTS','SUB11NM','SUB11','SUB11_GRADE','SUB11_GRADE_POINTS','SUB11_CREDIT_MAX','SUB11_CREDIT','SUB11_CREDIT_POINTS','SUB12NM','SUB12','SUB12_GRADE','SUB12_GRADE_POINTS','SUB12_CREDIT_MAX','SUB12_CREDIT','SUB12_CREDIT_POINTS','SUB13NM','SUB13','SUB13_GRADE','SUB13_GRADE_POINTS','SUB13_CREDIT_MAX','SUB13_CREDIT','SUB12_CREDIT_POINTS','SUB14NM','SUB14','SUB14_GRADE','SUB14_GRADE_POINTS','SUB14_CREDIT_MAX','SUB14_CREDIT','SUB14_CREDIT_POINTS','SUB15NM','SUB15','SUB15_GRADE','SUB15_GRADE_POINTS','SUB15_CREDIT_MAX','SUB15_CREDIT','SUB15_CREDIT_POINTS');
                $mismatchColArr=array_diff( $columnArr,$rowData1[0]);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            // }else{
            //     return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            // }
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
         $uniqueIdIndex = array_search('Unique_ID', $rowData1[0]);
                //  dd(     $uniqueIdIndex);
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
        // $uniqueIdIndex = array_search('Unique_ID', $rowData1[0]);

        foreach ($rowData1 as $key1 => $value1) {

            $serial_no=$value1[$uniqueIdIndex];
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

