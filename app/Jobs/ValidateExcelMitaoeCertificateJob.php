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
class ValidateExcelMitaoeCertificateJob
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
        // dd( $dropdown_template_id);
        $columnsCount1=count($rowData1[0]);
        // dd($columnsCount1);
        if($dropdown_template_id==1){
            if($columnsCount1==54){
                $columnArr=array("ORG_NAME","ACADEMIC_COURSE_ID","COURSE_NAME","STREAM","SESSION","REGN_NO","EMAIL","RROLL","CNAME","GENDER","DOB","FNAME","MNAME","PHOTO","MRKS_REC_STATUS","RESULT","YEAR","CSV_MONTH","MONTH","PERCENT","DOI","CERT_NO","SEM","TOT","TOT_MIN","TOT_MRKS","TOT_TH_MAX","TOT_TH_MIN","TOT_TH_MRKS","TOT_PR_MAX","TOT_PR_MIN","TOT_PR_MRKS","TOT_CE_MAX","TOT_CE_MIN","TOT_CE_MRKS","TOT_VV_MAX","TOT_VV_MIN","TOT_VV_MRKS","TOT_CREDIT","TOT_CREDIT_POINTS","TOT_GRADE_POINTS","PREV_TOT_MRKS","GRAND_TOT_MAX","GRAND_TOT_MIN","GRAND_TOT_MRKS","GRAND_TOT_CREDIT","CGPA","REMARKS","SGPA","ABC_ACCOUNT_ID","TERM_TYPE","TOT_GRADE","AADHAR NUMBER","ADMISSION_YEAR");
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
            return json_encode($message);
        }
    
        unset($rowData1[0]);
        $rowData1=array_values($rowData1);
        $blobArr=array();
        $profErrArr=[];
        
        $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        $old_rows = 0;
        $new_rows = 0;
        foreach ($rowData1 as $key1 => $value1) {

            $serial_no=$value1[5];
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

            $Photo=$value1[10];
            $template_id=100;
            $profile_path_png = public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.png';
            $profile_path_jpg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpg';
            $profile_path_jpeg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpeg';
            $profile_path_org="";
            if (file_exists($profile_path_png)) {
                $profile_path_org = $profile_path_png; // Use PNG if it exists
            } elseif (file_exists($profile_path_jpg)) {
                $profile_path_org = $profile_path_jpg; // Use JPG if it exists
            } 
            elseif (file_exists($profile_path_jpeg)) {
                $profile_path_org = $profile_path_jpeg; // Use JPG if it exists
            }else {
                    $profile_path_org = ''; 
            }
            if($dropdown_template_id==1){
                if (empty($profile_path_org)) {
                    // array_push($profErrArr, $value1[10]);
                }
            }
        }
       

        $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));
        //   dd( $mismatchArr);    
        if(count($mismatchArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Unique Id contains following duplicate values : '.implode(',', $mismatchArr)]);
        }elseif(count($profErrArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo does not exists following values : '.implode(',', $profErrArr)]);
        }else{
       
            return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);
        }
    }

  
}
