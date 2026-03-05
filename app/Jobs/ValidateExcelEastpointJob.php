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
class ValidateExcelEastpointJob
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

         if($dropdown_template_id==1){
           
                    if($columnsCount1==65){
                         $columnArr=array('Sl. No','COURSE_NAME','REGN_NO','Student Name','Father Name','YEAR','MONTH','Sem','CGPA','SGPA','SUB1NM','SUB1','SUB1_GRADE','SUB1_GRADE_POINTS','SUB1_CREDIT','SUB1_CREDIT_POINTS','SUB2NM','SUB2','SUB2_GRADE','SUB2_GRADE_POINTS','SUB2_CREDIT','SUB2_CREDIT_POINTS','SUB3NM','SUB3','SUB3_GRADE','SUB3_GRADE_POINTS','SUB3_CREDIT','SUB3_CREDIT_POINTS','SUB4NM','SUB4','SUB4_GRADE','SUB4_GRADE_POINTS',
                         'SUB4_CREDIT','SUB4_CREDIT_POINTS','SUB5NM','SUB5','SUB5_GRADE','SUB5_GRADE_POINTS','SUB5_CREDIT','SUB5_CREDIT_POINTS','SUB6NM','SUB6','SUB6_GRADE','SUB6_GRADE_POINTS','SUB6_CREDIT','SUB6_CREDIT_POINTS','SUB7NM','SUB7','SUB7_GRADE','SUB7_GRADE_POINTS','SUB7_CREDIT','SUB7_CREDIT_POINTS','SUB8NM','SUB8','SUB8_GRADE','SUB8_GRADE_POINTS','SUB8_CREDIT','SUB8_CREDIT_POINTS','DATE','Credits Registered','Credits Earned','Cumulative Credits Earned','E(C×G)','PHOTO','Unique_id');

                        $mismatchColArr=array_diff($rowData1[0], $columnArr);
                        if(count($mismatchColArr)>0){
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);


                        }

                        if(count($mismatchColArr)>0){
                            
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet2 : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                        }
                    }else{
                        return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
                    }
        }
        else if ($dropdown_template_id==2){

           $columnsCount2=count($rowData2[0]);

        //    echo "<pre>";
        //    print_r($rowData2[0]);
        //    die();
                //    echo $columnsCount1;
                //    echo "<br>";
                //    echo  $columnsCount2;
                //    echo "<br>";
                //    die();
              if($columnsCount1==16 && $columnsCount2==8){
                $columnArr=array("Reg. No.", "Student_name", "Father_name", "Mother_name", "Credits_Registered","Credits_Earned", "Cumulative_Credits_Earned", "E(CixGi)", "SGPA", "CGPA", "Medium_of_Instruction", "Date", "Photo", "Grade_card_Title","Month & Year of exam",'Unique ID');
                 $mismatchColArr=array_diff($rowData1[0], $columnArr);
                        if(count($mismatchColArr)>0){
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);


                        }


                        $columnArr2=array('Reg. No.','Semester', 'Course_code', 'Subject_Title','Credits_Assigned',	'Credits_Earned', 'Grade_Point', 'Letter_Grade');
                        $mismatchColArr=array_diff($rowData2[0], $columnArr2);
                        // dd($rowData2[0]);
                        if(count($mismatchColArr)>0){
                            
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet2 : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                        }

              }
              else{
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
            //kamini chnages 
            if($dropdown_template_id==1){
                 if($dropdown_template_id==1){
                       $Photo=$value1[23];

                    }
                    if($dropdown_template_id==2){
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
                  
                    if($dropdown_template_id==1||$dropdown_template_id==2){
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

