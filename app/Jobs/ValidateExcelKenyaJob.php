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

class ValidateExcelKenyaJob
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

        $pdf_data = $this->pdf_data;

        $rowData1=$pdf_data['rowData1'];
        $rowData2=$pdf_data['rowData2'];
        $auth_site_id=$pdf_data['auth_site_id'];
        $rowData1[0] = array_filter($rowData1[0]);
        
        $columnsCount1=count($rowData1[0]);
        
        $columnsCount2=count($rowData2[0]);
        
        if($columnsCount1==30){

            $columnArr=array('UNIQUEID','INDEX','SEX','NAME','PP1','G1','PP2','G2','PP3','G3','PP4','G4','PP5','G5','PP6','G6','PP7','G7','PP8','G8','PP9','G9','MGDescr','AGGREGATE','ENT','CODE','SCHOOL','RESYEAR','STATU','PHOTO');
            $mismatchColArr=array_diff($rowData1[0], $columnArr);
        
            if(count($mismatchColArr)>0){
                
                return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
            }


            // $columnArr=array('PRN','Sem_Number','Sub_Code','Sub_Name','INT','EXT','TOT','Sub_Cred','Earned_Cred','Grade_Point','Credit_Point','Grade');
            // $mismatchColArr=array_diff($rowData2[0], $columnArr);
            
            // if(count($mismatchColArr)>0){
                
            //     return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet2 : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
            // }

        }elseif($columnsCount1!=30){
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

        $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        $old_rows = 0;
        $new_rows = 0;
        foreach ($rowData1 as $key1 => $value1) {
            // $blobFile = pathinfo($value1[23]);
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
                if($studentTableCounts > 0){
                    $old_rows += 1;
                }else{
                    $new_rows += 1;
                }
            }   
        }
        
        $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));
                
        if(count($mismatchArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 /Json : BlobFileName contains following duplicate values : '.implode(',', $mismatchArr)]);
        }
            
        return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);
    }

  
}
