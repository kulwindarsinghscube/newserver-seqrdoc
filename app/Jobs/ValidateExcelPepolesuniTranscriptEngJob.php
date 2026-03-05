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

class ValidateExcelPepolesuniTranscriptEngJob
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
        $rowData2=$pdf_data['rowData2'];
        $rowData3=$pdf_data['rowData3'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $dropdown_template_id=$pdf_data['dropdown_template_id'];
        
        $rowData1[0] = array_filter($rowData1[0]);
            
        $columnsCount1=count($rowData1[0]);

        $columnsCount2=count($rowData2[0]);
        
        
        if($columnsCount1==44&&$columnsCount2==10){

            $columnArr=array("Unique_ID","Name of Student","FathersName","Enrollment Number","Enrollment Year","Name of Course","Branch","Name of  Institute","Duration of Course","I_Year Exam","I_Year_Status","I_Year_Result","I_Year_SGPA","II_Year Exam","II_Year_Status","II_Year_Result","II_Year_SGPA","III_Year Exam","III_Year_Status","III_Year_Result","III_Year_SGPA","IV_Year Exam","IV_Year_Status","IV_Year_Result","IV_Year_SGPA","V_Year Exam","V_Year_Status","V_Year_Result","V_Year_SGPA","VI_Year Exam","VI_Year_Status","VI_Year_Result","VI_Year_SGPA","VII_Year Exam","VII_Year_Status","VII_Year_Result","VII_Year_SGPA","VIII_Year Exam","VIII_Year_Status","VIII_Year_Result","VIII_Year_SGPA","CGPA","Division","note");
            $mismatchColArr1=array_diff($rowData1[0], $columnArr);
            if(count($mismatchColArr1)>0){
                return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr1)]);
            }

            $columnArr=array("Unique_ID","Year","Paper Code","Paper Name","Credit Offered Theory","Credit Offered Practical","Credit Earned Theory","Credit Earned Practical","Grade Theory","Grade Practical");
            $mismatchColArr2=array_diff($rowData2[0], $columnArr);
            if(count($mismatchColArr2)>0){
                return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet2 : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr2)]);
            }



        }elseif($columnsCount1!=44){
            return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of sheet1 do not matched!']);
        }else{
            return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of sheet2 do not matched!']);
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
        
        $rowData2[0] = array_filter($rowData2[0]);
        
        $ab = array_count_values($rowData2[0]);

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
        $profArr=array();
        $profErrArr=array();
        
        $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        $old_rows = 0;
        $new_rows = 0;
        foreach ($rowData1 as $key1 => $value1) {
            $serial_no=$value1[0];
            array_push($blobArr, $serial_no);
            
            $profile_path_jpg = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.trim($value1[3]).'.jpg';
            $profile_path_jpeg = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.trim($value1[3]).'.jpeg';
            $profile_path_png = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.trim($value1[3]).'.png';


            if (!file_exists($profile_path_jpg)) {
                if(!file_exists($profile_path_png)){
                    if(!file_exists($profile_path_jpeg)){
                        array_push($profErrArr, $value1[3]);
                    }
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
                
        if(count($mismatchArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Unique Id contains following duplicate values : '.implode(',', $mismatchArr)]);
        }
        
        // if(count($profErrArr)>0){
        //     return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo does not exists following values : '.implode(',', $profErrArr)]);
        // }
                
        return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);
    }



  
}
