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
class ValidateExcelAtriauJob
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
       $rowData2=$pdf_data['subjectsMark'];
       
        // dd($rowData2[0]);
        if ($dropdown_template_id==1){

           if($columnsCount1==157){
                $columnArr=array( "ORG_NAME", "YEAR", "PROGRAM OF STUDY", "YEAR OF STUDY", "REGN_NO", "G.C. NO.", "TRANS NO", "CNAME", "EXAM YEAR", "ADMISSION_YEAR", "MEDIUM ", "TOTAL CREDIT", "TOTAL CREDIT EARNED INCLUDING CURRENT YEAR ", "GPA", "CGPA", "YEAR OF PASSING", "COURSE NAME SUB1", "COURSE CODE SUB 1", "SUB 1 MONTH, YEAR", "SUB 1 COMPLETION YEAR", "SUB 1 COMPLETON MONTH", "SUB 1 GRADE", "SUB 1 CREDIT", "COURSE NAME SUB2", "COURSE CODE SUB 2", "SUB 2 MONTH, YEAR", "SUB 2 COMPLETION YEAR", "SUB 2 COMPLETON MONTH", "SUB 2 GRADE", "SUB2_CREDIT", "COURSE NAME SUB3", "COURSE CODE SUB 3", "SUB 3 MONTH, YEAR", "SUB 3 COMPLETION YEAR", "SUB 3 COMPLETON MONTH", "SUB 3 GRADE", "SUB3_CREDIT", "COURSE NAME SUB4", "COURSE CODE SUB 4", "SUB 4 MONTH, YEAR", "SUB 4 COMPLETION YEAR", "SUB 4 COMPLETON MONTH", "SUB 4 GRADE", "SUB4_CREDIT", "COURSE NAME SUB5", "COURSE CODE SUB 5", "SUB 5 MONTH, YEAR", "SUB 5 COMPLETION YEAR", "SUB 5 COMPLETON MONTH", "SUB 5 GRADE", "SUB5_CREDIT", "COURSE NAME SUB6", "COURSE CODE SUB 6", "SUB 6 MONTH, YEAR", "SUB 6 COMPLETION YEAR", "SUB 6 COMPLETON MONTH", "SUB 6 GRADE", "SUB6_CREDIT", "COURSE NAME SUB7", "COURSE CODE SUB 7", "SUB 7 MONTH, YEAR", "SUB 7 COMPLETION YEAR", "SUB 7 COMPLETON MONTH", "SUB 7 GRADE", "SUB7_CREDIT", "COURSE NAME SUB8", "COURSE CODE SUB 8", "SUB 8 MONTH, YEAR", "SUB 8 COMPLETION YEAR", "SUB 8 COMPLETON MONTH", "SUB 8 GRADE", "SUB8_CREDIT", "COURSE NAME SUB9", "COURSE CODE SUB 9", "SUB 9 MONTH, YEAR", "SUB 9 COMPLETION YEAR", "SUB 9 COMPLETON MONTH", "SUB 9 GRADE", "SUB9_CREDIT", "COURSE NAME SUB10", "COURSE CODE SUB 10", "SUB 10 MONTH, YEAR", "SUB 10 COMPLETION YEAR", "SUB 10 COMPLETON MONTH", "SUB 10 GRADE", "SUB10_CREDIT", "COURSE NAME SUB11", "COURSE CODE SUB 11", "SUB 11 MONTH, YEAR", "SUB 11 COMPLETION YEAR", "SUB 11 COMPLETON MONTH", "SUB 11 GRADE", "SUB11_CREDIT", "COURSE NAME SUB12", "COURSE CODE SUB 12", "SUB 12 MONTH, YEAR", "SUB 12 COMPLETION YEAR", "SUB 12 COMPLETON MONTH", "SUB 12 GRADE", "SUB12_CREDIT", "COURSE NAME SUB13", "COURSE CODE SUB 13", "SUB 13 MONTH, YEAR", "SUB 13 COMPLETION YEAR", "SUB 13 COMPLETON MONTH", "SUB 13 GRADE", "SUB13_CREDIT", "COURSE NAME SUB14", "COURSE CODE SUB 14", "SUB 14 MONTH, YEAR", "SUB 14 COMPLETION YEAR", "SUB 14 COMPLETON MONTH", "SUB 14 GRADE", "SUB14_CREDIT", "COURSE NAME SUB15", "COURSE CODE SUB 15", "SUB 15 MONTH, YEAR", "SUB 15 COMPLETION YEAR", "SUB 15 COMPLETON MONTH", "SUB 15 GRADE", "SUB15_CREDIT", "COURSE NAME SUB16", "COURSE CODE SUB 16", "SUB 16 MONTH, YEAR", "SUB 16 COMPLETION YEAR", "SUB 16 COMPLETON MONTH", "SUB 16 GRADE", "SUB16_CREDIT", "COURSE NAME SUB17", "COURSE CODE SUB 17", "SUB 17 MONTH, YEAR", "SUB 17 COMPLETION YEAR", "SUB 17 COMPLETON MONTH", "SUB 17 GRADE", "SUB17_CREDIT", "COURSE NAME SUB18", "COURSE CODE SUB 18", "SUB 18 MONTH, YEAR", "SUB 18 COMPLETION YEAR", "SUB 18 COMPLETON MONTH", "SUB 18 GRADE", "SUB18_CREDIT", "COURSE NAME SUB19", "COURSE CODE SUB 19", "SUB 19 MONTH, YEAR", "SUB 19 COMPLETION YEAR", "SUB 19 COMPLETON MONTH", "SUB 19 GRADE", "SUB19_CREDIT", "COURSE NAME SUB20", "COURSE CODE SUB 20", "SUB 20 MONTH, YEAR", "SUB 20 COMPLETION YEAR", "SUB 20 COMPLETON MONTH", "SUB 20 GRADE", "SUB20_CREDIT",'Unique Id');
                 $mismatchColArr=array_diff($rowData1[0], $columnArr);
                        if(count($mismatchColArr)>0){
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);


                        }

            }
              else{
                        return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
                    }

        }

           elseif($dropdown_template_id==2){
            if($columnsCount1==156){
                // dd($columnsCount1);
                //$photo_col=16;
				$columnArr=array(
   // Student / Header fields
"ORG_NAME",
"YEAR",
"PROGRAM OF STUDY",
"YEAR OF STUDY",
"REGN_NO",
"G.C. NO.",
"TRANS NO",
"CNAME",
"EXAM YEAR",
"ADMISSION_YEAR",
"MEDIUM ",
"TOTAL CREDIT",
"TOTAL CREDIT EARNED INCLUDING CURRENT YEAR ",
"GPA",
"CGPA",
"YEAR OF PASSING",

// ================= SUBJECT 1 =================
"COURSE NAME SUB1",
"COURSE CODE SUB 1",
"SUB 1 MONTH, YEAR",
"SUB 1 COMPLETION YEAR",
"SUB 1 COMPLETON MONTH",
"SUB 1 GRADE",
"SUB 1 CREDIT",

// ================= SUBJECT 2 =================
"COURSE NAME SUB2",
"COURSE CODE SUB 2",
"SUB 2 MONTH, YEAR",
"SUB 2 COMPLETION YEAR",
"SUB 2 COMPLETON MONTH",
"SUB 2 GRADE",
"SUB2_CREDIT",

// ================= SUBJECT 3 =================
"COURSE NAME SUB3",
"COURSE CODE SUB 3",
"SUB 3 MONTH, YEAR",
"SUB 3 COMPLETION YEAR",
"SUB 3 COMPLETON MONTH",
"SUB 3 GRADE",
"SUB3_CREDIT",

// ================= SUBJECT 4 =================
"COURSE NAME SUB4",
"COURSE CODE SUB 4",
"SUB 4 MONTH, YEAR",
"SUB 4 COMPLETION YEAR",
"SUB 4 COMPLETON MONTH",
"SUB 4 GRADE",
"SUB4_CREDIT",

// ================= SUBJECT 5 =================
"COURSE NAME SUB5",
"COURSE CODE SUB 5",
"SUB 5 MONTH, YEAR",
"SUB 5 COMPLETION YEAR",
"SUB 5 COMPLETON MONTH",
"SUB 5 GRADE",
"SUB5_CREDIT",

// ================= SUBJECT 6 =================
"COURSE NAME SUB6",
"COURSE CODE SUB 6",
"SUB 6 MONTH, YEAR",
"SUB 6 COMPLETION YEAR",
"SUB 6 COMPLETON MONTH",
"SUB 6 GRADE",
"SUB6_CREDIT",

// ================= SUBJECT 7 =================
"COURSE NAME SUB7",
"COURSE CODE SUB 7",
"SUB 7 MONTH, YEAR",
"SUB 7 COMPLETION YEAR",
"SUB 7 COMPLETON MONTH",
"SUB 7 GRADE",
"SUB7_CREDIT",

// ================= SUBJECT 8 =================
"COURSE NAME SUB8",
"COURSE CODE SUB 8",
"SUB 8 MONTH, YEAR",
"SUB 8 COMPLETION YEAR",
"SUB 8 COMPLETON MONTH",
"SUB 8 GRADE",
"SUB8_CREDIT",

// ================= SUBJECT 9 =================
"COURSE NAME SUB9",
"COURSE CODE SUB 9",
"SUB 9 MONTH, YEAR",
"SUB 9 COMPLETION YEAR",
"SUB 9 COMPLETON MONTH",
"SUB 9 GRADE",
"SUB9_CREDIT",

// ================= SUBJECT 10 =================
"COURSE NAME SUB10",
"COURSE CODE SUB 10",
"SUB 10 MONTH, YEAR",
"SUB 10 COMPLETION YEAR",
"SUB 10 COMPLETON MONTH",
"SUB 10 GRADE",
"SUB10_CREDIT",

// ================= SUBJECT 11 =================
"COURSE NAME SUB11",
"COURSE CODE SUB 11",
"SUB 11 MONTH, YEAR",
"SUB 11 COMPLETION YEAR",
"SUB 11 COMPLETON MONTH",
"SUB 11 GRADE",
"SUB11_CREDIT",

// ================= SUBJECT 12 =================
"COURSE NAME SUB12",
"COURSE CODE SUB 12",
"SUB 12 MONTH, YEAR",
"SUB 12 COMPLETION YEAR",
"SUB 12 COMPLETON MONTH",
"SUB 12 GRADE",
"SUB12_CREDIT",

// ================= SUBJECT 13 =================
"COURSE NAME SUB13",
"COURSE CODE SUB 13",
"SUB 13 MONTH, YEAR",
"SUB 13 COMPLETION YEAR",
"SUB 13 COMPLETON MONTH",
"SUB 13 GRADE",
"SUB13_CREDIT",

// ================= SUBJECT 14 =================
"COURSE NAME SUB14",
"COURSE CODE SUB 14",
"SUB 14 MONTH, YEAR",
"SUB 14 COMPLETION YEAR",
"SUB 14 COMPLETON MONTH",
"SUB 14 GRADE",
"SUB14_CREDIT",

// ================= SUBJECT 15 =================
"COURSE NAME SUB15",
"COURSE CODE SUB 15",
"SUB 15 MONTH, YEAR",
"SUB 15 COMPLETION YEAR",
"SUB 15 COMPLETON MONTH",
"SUB 15 GRADE",
"SUB15_CREDIT",

// ================= SUBJECT 16 =================
"COURSE NAME SUB16",
"COURSE CODE SUB 16",
"SUB 16 MONTH, YEAR",
"SUB 16 COMPLETION YEAR",
"SUB 16 COMPLETON MONTH",
"SUB 16 GRADE",
"SUB16_CREDIT",

// ================= SUBJECT 17 =================
"COURSE NAME SUB17",
"COURSE CODE SUB 17",
"SUB 17 MONTH, YEAR",
"SUB 17 COMPLETION YEAR",
"SUB 17 COMPLETON MONTH",
"SUB 17 GRADE",
"SUB17_CREDIT",

// ================= SUBJECT 18 =================
"COURSE NAME SUB18",
"COURSE CODE SUB 18",
"SUB 18 MONTH, YEAR",
"SUB 18 COMPLETION YEAR",
"SUB 18 COMPLETON MONTH",
"SUB 18 GRADE",
"SUB18_CREDIT",

// ================= SUBJECT 19 =================
"COURSE NAME SUB19",
"COURSE CODE SUB 19",
"SUB 19 MONTH, YEAR",
"SUB 19 COMPLETION YEAR",
"SUB 19 COMPLETON MONTH",
"SUB 19 GRADE",
"SUB19_CREDIT",

// ================= SUBJECT 20 =================
"COURSE NAME SUB20",
"COURSE CODE SUB 20",
"SUB 20 MONTH, YEAR",
"SUB 20 COMPLETION YEAR",
"SUB 20 COMPLETON MONTH",
"SUB 20 GRADE",
"SUB20_CREDIT",);
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){   
                    // dd($mismatchColArr);             
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }

        elseif($dropdown_template_id==3){
            if($columnsCount1==9){
                //$photo_col=16;
				$columnArr=array("Unique ID","REG NO", "Sl No", "STUDENT NAME", "RESULT", "DEGREE", "COMPLETION DATE", "DATE", "PHOTO");
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
       

        $sandboxCheck = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
        $old_rows = 0;
        $new_rows = 0;
        foreach ($rowData1 as $key1 => $value1) {
            if($dropdown_template_id==1){
                 $serial_no=$value1[5];
            }
            else{
                $serial_no=$value1[0];
            }

       
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
            //kamini chnages 
            if($dropdown_template_id==1){
                 if($dropdown_template_id==1){
                       $Photo=$value1[12];

                    }

                     $template_id=100;
                    // $profile_path_png = public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.png';
                    // $profile_path_jpg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpg';
                    // $profile_path_jpeg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpeg';
                    $profile_path_org="";
                    
                    $profile_path_jpg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo;
                    if (file_exists($profile_path_jpg)) {
                        $profile_path_org = $profile_path_jpg; 
                    }else {
                        $profile_path_org = '';
                    }
                  
                    if($dropdown_template_id==1){
                        if (empty($profile_path_org)) {
                            array_push($profErrArr,$Photo);
                        }
                    }
            }



           
        }

      
        $mismatchArr = array_intersect($blobArr, array_unique(array_diff_key($blobArr, array_unique($blobArr))));
              
        if(count($mismatchArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Unique Id contains following duplicate values : '.implode(',', $mismatchArr)]);
        }
       
              
        return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);
    }

  
}

