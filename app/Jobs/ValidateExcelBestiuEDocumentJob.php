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

class ValidateExcelBestiuEDocumentJob
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
            if($columnsCount1==70){
                //$photo_col=23;
				$columnArr=array("Unique ID","STUDENT NAME","FATHERS NAME","EXAMINATION","PROGRAMME","BATCH","ID NO.","DATE","SEMESTER","COURSE CODE_1","COURSE TITLE_1","CREDIT HOURS _1","GRADE POINT_1","CREDIT POINTS_1","COURSE CODE_2","COURSE TITLE_2","CREDIT HOURS _2","GRADE POINT_2","CREDIT POINTS_2","COURSE CODE_3","COURSE TITLE_3","CREDIT HOURS _3","GRADE POINT_3","CREDIT POINTS_3","COURSE CODE_4","COURSE TITLE_4","CREDIT HOURS _4","GRADE POINT_4","CREDIT POINTS_4","COURSE CODE_5","COURSE TITLE_5","CREDIT HOURS _5","GRADE POINT_5","CREDIT POINTS_5","COURSE CODE_6","COURSE TITLE_6","CREDIT HOURS _6","GRADE POINT_6","CREDIT POINTS_6","COURSE CODE_7","COURSE TITLE_7","CREDIT HOURS _7","GRADE POINT_7","CREDIT POINTS_7","COURSE CODE_8","COURSE TITLE_8","CREDIT HOURS _8","GRADE POINT_8","CREDIT POINTS_8","COURSE CODE_9","COURSE TITLE_9","CREDIT HOURS _9","GRADE POINT_9","CREDIT POINTS_9","COURSE CODE_10","COURSE TITLE_10","CREDIT HOURS _10","GRADE POINT_10","CREDIT POINTS_10","COURSE CODE_11","COURSE TITLE_11","CREDIT HOURS _11","GRADE POINT_11","CREDIT POINTS_11","CREDITS_EARNED","SGPA","CGPA","Total Credit Hours","Total Credit Points","School Name");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }
		elseif($dropdown_template_id==2||$dropdown_template_id==3||$dropdown_template_id==4){
            if($columnsCount1==185){
                
				$columnArr=array("Unique ID","STUDENT NAME","FATHERS NAME","EXAMINATION","PROGRAMME","BATCH","CLASS AWARDED","ID NO.","DATE","SEMESTER","COURSE CODE_1","COURSE TITLE_1","CREDIT HOURS_1","GRADE POINT_1","CREDIT POINTS_1","COURSE CODE_2","COURSE TITLE_2","CREDIT HOURS_2","GRADE POINT_2","CREDIT POINTS_2","COURSE CODE_3","COURSE TITLE_3","CREDIT HOURS_3","GRADE POINT_3","CREDIT POINTS_3","COURSE CODE_4","COURSE TITLE_4","CREDIT HOURS_4","GRADE POINT_4","CREDIT POINTS_4","COURSE CODE_5","COURSE TITLE_5","CREDIT HOURS_5","GRADE POINT_5","CREDIT POINTS_5","COURSE CODE_6","COURSE TITLE_6","CREDIT HOURS_6","GRADE POINT_6","CREDIT POINTS_6","COURSE CODE_7","COURSE TITLE_7","CREDIT HOURS_7","GRADE POINT_7","CREDIT POINTS_7","COURSE CODE_8","COURSE TITLE_8","CREDIT HOURS_8","GRADE POINT_8","CREDIT POINTS_8","COURSE CODE_9","COURSE TITLE_9","CREDIT HOURS_9","GRADE POINT_9","CREDIT POINTS_9","COURSE CODE_10","COURSE TITLE_10","CREDIT HOURS_10","GRADE POINT_10","CREDIT POINTS_10","COURSE CODE_11","COURSE TITLE_11","CREDIT HOURS_11","GRADE POINT_11","CREDIT POINTS_11","1 SGPA","2 SEMESTER","2 COURSE CODE_1","2 COURSE TITLE_1","2 CREDIT HOURS_1","2 GRADE POINT_1","2 CREDIT POINTS_1","2 COURSE CODE_2","2 COURSE TITLE_2","2 CREDIT HOURS_2","2 GRADE POINT_2","2 CREDIT POINTS_2","2 COURSE CODE_3","2 COURSE TITLE_3","2 CREDIT HOURS_3","2 GRADE POINT_3","2 CREDIT POINTS_3","2 COURSE CODE_4","2 COURSE TITLE_4","2 CREDIT HOURS_4","2 GRADE POINT_4","2 CREDIT POINTS_4","2 COURSE CODE_5","2 COURSE TITLE_5","2 CREDIT HOURS_5","2 GRADE POINT_5","2 CREDIT POINTS_5","2 COURSE CODE_6","2 COURSE TITLE_6","2 CREDIT HOURS_6","2 GRADE POINT_6","2 CREDIT POINTS_6","2 COURSE CODE_7","2 COURSE TITLE_7","2 CREDIT HOURS_7","2 GRADE POINT_7","2 CREDIT POINTS_7","2 COURSE CODE_8","2 COURSE TITLE_8","2 CREDIT HOURS_8","2 GRADE POINT_8","2 CREDIT POINTS_8","2 COURSE CODE_9","2 COURSE TITLE_9","2 CREDIT HOURS_9","2 GRADE POINT_9","2 CREDIT POINTS_9","2 COURSE CODE_10","2 COURSE TITLE_10","2 CREDIT HOURS_10","2 GRADE POINT_10","2 CREDIT POINTS_10","2 COURSE CODE_11","2 COURSE TITLE_11","2 CREDIT HOURS_11","2 GRADE POINT_11","2 CREDIT POINTS_11","2 SGPA","3 SEMESTER","3 COURSE CODE_1","3 COURSE TITLE_1","3 CREDIT HOURS_1","3 GRADE POINT_1","3 CREDIT POINTS_1","3 COURSE CODE_2","3 COURSE TITLE_2","3 CREDIT HOURS_2","3 GRADE POINT_2","3 CREDIT POINTS_2","3 COURSE CODE_3","3 COURSE TITLE_3","3 CREDIT HOURS_3","3 GRADE POINT_3","3 CREDIT POINTS_3","3 COURSE CODE_4","3 COURSE TITLE_4","3 CREDIT HOURS_4","3 GRADE POINT_4","3 CREDIT POINTS_4","3 COURSE CODE_5","3 COURSE TITLE_5","3 CREDIT HOURS_5","3 GRADE POINT_5","3 CREDIT POINTS_5","3 COURSE CODE_6","3 COURSE TITLE_6","3 CREDIT HOURS_6","3 GRADE POINT_6","3 CREDIT POINTS_6","3 COURSE CODE_7","3 COURSE TITLE_7","3 CREDIT HOURS_7","3 GRADE POINT_7","3 CREDIT POINTS_7","3 COURSE CODE_8","3 COURSE TITLE_8","3 CREDIT HOURS_8","3 GRADE POINT_8","3 CREDIT POINTS_8","3 COURSE CODE_9","3 COURSE TITLE_9","3 CREDIT HOURS_9","3 GRADE POINT_9","3 CREDIT POINTS_9","3 COURSE CODE_10","3 COURSE TITLE_10","3 CREDIT HOURS_10","3 GRADE POINT_10","3 CREDIT POINTS_10","3 COURSE CODE_11","3 COURSE TITLE_11","3 CREDIT HOURS_11","3 GRADE POINT_11","3 CREDIT POINTS_11","3 SGPA","TOTAL CREDITS EARNED","TOTAL CREDIT POINTS","GRADE POINT AVERAGE (CGPA)","PERCENTAGE OF MARKS","School Name");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }

         if($dropdown_template_id==7||$dropdown_template_id==5||$dropdown_template_id==6){
            if($columnsCount1==14){
                $columnArr=array("Unique ID","STUDENT NAME","FATHERS NAME","EXAMINATION","PROGRAMME","BATCH","CLASS AWARDED","ID NO.","DATE","TOTAL CREDITS EARNED","TOTAL CREDIT POINTS","GRADE POINT AVERAGE (CGPA)","PERCENTAGE OF MARKS","School Name");
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

