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
class ValidateExcelSJcitJob
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
       
        
        if ($dropdown_template_id==1){

           $columnsCount2=count($rowData2[0]);
                //    dd($columnsCount1,$columnsCount2);
                // dd($columnsCount2);
              if($columnsCount1==16 && $columnsCount2==8){
                // $columnArr=array("USN", "Student_name", "Father_name", "Mother_name", "Credits_Registered","Credits_Earned", "Cumulative_Credits_Earned", "E(CixGi)", "SGPA", "CGPA", "Medium_of_Instruction", "Date", "Photo", "Grade_card_Title",'Month & Year of exam','Unique_id');

                   $columnArr=array('Unique_id', "USN", "Grade_card_Title","Student Name", "Father Name/Mother Name", "Month and year", "Appar_Id", "Credits_Registered", "Credit_Earned", "Cumulative_Credit_Earned", "Cumulative GP", "SGPA", "CGPA", "Medium of instruction", "Date", "Photo");
                 $mismatchColArr=array_diff($rowData1[0], $columnArr);
                        if(count($mismatchColArr)>0){
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);


                        }


                          $columnArr2=array('USN','Semester', 'Course_code', 'Subject_Title','Credits_Assigned',	'Credits_Earned', 'Grade_Point', 'Letter_Grade');
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

