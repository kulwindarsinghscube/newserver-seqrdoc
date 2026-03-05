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
class ValidateExcelMvsrJob
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

        //echo $columnsCount1;
        //exit;
       
        if($dropdown_template_id==1 || $dropdown_template_id==3 || $dropdown_template_id==7){
            if($columnsCount1==25){
                $columnArr=array("Unique_Id","Name","Parents_Name","Month_&_Year_of_Exam","RefNo","Degree","Branch","Gender","Hall_Ticket_No","College_Code","Class_Awarded","Serial_No","CGPA Secured","Credits Registered","Credits Secured","Place","Date","Image","Marksheet","Note","Course Year","Mother Name","CGPA In Word","Percentage Marks","Division");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }
        //kamini chnages
        elseif($dropdown_template_id==2){
            if($columnsCount1==13){
                //$columnArr=array('Unique Serial No','Ref No','Roll No','Student Name','Father Name','Mother Name','Degree','Programe Name','Exam held in Month','Result','CGPA','photo','Date of Declaration of Result');
                $columnArr=array('Unique_Id','RefNo','Roll No','Student Name','Father Name','Mother Name','Degree','Programe Name','Exam held in Month','Result','CGPA','Photo','Date of Declaration of Result');
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }
        // else if($dropdown_template_id==3){
        //     if($columnsCount1==234){
        //         //$photo_col=23;
        //         $columnArr=array("Unique Serial No","Roll No","Ref No","Name","Father Name","Mother Name","Course","Branch","Examination","Course Name","Course Year","I SEMESTER","COURSE TITLE_1","CREDIT_1","LETTER GRADE_1","COURSE TITLE_2","CREDIT_2","LETTER GRADE_2","COURSE TITLE_3","CREDIT_3","LETTER GRADE_3","COURSE TITLE_4","CREDIT_4","LETTER GRADE_4","COURSE TITLE_5","CREDIT_5","LETTER GRADE_5","COURSE TITLE_6","CREDIT_6","LETTER GRADE_6","COURSE TITLE_7","CREDIT_7","LETTER GRADE_7","COURSE TITLE_8","CREDIT_8","LETTER GRADE_8","SGPA","II SEMESTER","COURSE TITLE_1","CREDIT_1","LETTER GRADE_1","COURSE TITLE_2","CREDIT_2","LETTER GRADE_2","COURSE TITLE_3","CREDIT_3","LETTER GRADE_3","COURSE TITLE_4","CREDIT_4","LETTER GRADE_4","COURSE TITLE_5","CREDIT_5","LETTER GRADE_5","COURSE TITLE_6","CREDIT_6","LETTER GRADE_6","COURSE TITLE_7","CREDIT_7","LETTER GRADE_7","COURSE TITLE_8","CREDIT_8","LETTER GRADE_8","SGPA","III SEMESTER","COURSE TITLE_1","CREDIT_1","LETTER GRADE_1","COURSE TITLE_2","CREDIT_2","LETTER GRADE_2","COURSE TITLE_3","CREDIT_3","LETTER GRADE_3","COURSE TITLE_4","CREDIT_4","LETTER GRADE_4","COURSE TITLE_5","CREDIT_5","LETTER GRADE_5","COURSE TITLE_6","CREDIT_6","LETTER GRADE_6","COURSE TITLE_7","CREDIT_7","LETTER GRADE_7","COURSE TITLE_8","CREDIT_8","LETTER GRADE_8","COURSE TITLE_9","CREDIT_9","LETTER GRADE_9","COURSE TITLE_10","CREDIT_10","LETTER GRADE_10","SGPA","IV SEMESTER","COURSE TITLE_1","CREDIT_1","LETTER GRADE_1","COURSE TITLE_2","CREDIT_2","LETTER GRADE_2","COURSE TITLE_3","CREDIT_3","LETTER GRADE_3","COURSE TITLE_4","CREDIT_4","LETTER GRADE_4","COURSE TITLE_5","CREDIT_5","LETTER GRADE_5","COURSE TITLE_6","CREDIT_6","LETTER GRADE_6","COURSE TITLE_7","CREDIT_7","LETTER GRADE_7","COURSE TITLE_8","CREDIT_8","LETTER GRADE_8","COURSE TITLE_9","CREDIT_9","LETTER GRADE_9","SGPA","V SEMESTER","COURSE TITLE_1","CREDIT_1","LETTER GRADE_1","COURSE TITLE_2","CREDIT_2","LETTER GRADE_2","COURSE TITLE_3","CREDIT_3","LETTER GRADE_3","COURSE TITLE_4","CREDIT_4","LETTER GRADE_4","COURSE TITLE_5","CREDIT_5","LETTER GRADE_5","COURSE TITLE_6","CREDIT_6","LETTER GRADE_6","COURSE TITLE_7","CREDIT_7","LETTER GRADE_7","COURSE TITLE_8","CREDIT_8","LETTER GRADE_8","COURSE TITLE_9","CREDIT_9","LETTER GRADE_9","COURSE TITLE_10","CREDIT_10","LETTER GRADE_10","SGPA","VI SEMESTER","COURSE TITLE_1","CREDIT_1","LETTER GRADE_1","COURSE TITLE_2","CREDIT_2","LETTER GRADE_2","COURSE TITLE_3","CREDIT_3","LETTER GRADE_3","COURSE TITLE_4","CREDIT_4","LETTER GRADE_4","COURSE TITLE_5","CREDIT_5","LETTER GRADE_5","COURSE TITLE_6","CREDIT_6","LETTER GRADE_6","COURSE TITLE_7","CREDIT_7","LETTER GRADE_7","COURSE TITLE_8","CREDIT_8","LETTER GRADE_8","COURSE TITLE_9","CREDIT_9","LETTER GRADE_9","SGPA","VII SEMESTER","COURSE TITLE_1","CREDIT_1","LETTER GRADE_1","COURSE TITLE_2","CREDIT_2","LETTER GRADE_2","COURSE TITLE_3","CREDIT_3","LETTER GRADE_3","COURSE TITLE_4","CREDIT_4","LETTER GRADE_4","COURSE TITLE_5","CREDIT_5","LETTER GRADE_5","COURSE TITLE_6","CREDIT_6","LETTER GRADE_6","COURSE TITLE_7","CREDIT_7","LETTER GRADE_7","COURSE TITLE_8","CREDIT_8","LETTER GRADE_8","COURSE TITLE_9","CREDIT_9","LETTER GRADE_9","SGPA","VIII SEMESTER","COURSE TITLE_1","CREDIT_1","LETTER GRADE_1","COURSE TITLE_2","CREDIT_2","LETTER GRADE_2","COURSE TITLE_3","CREDIT_3","LETTER GRADE_3","COURSE TITLE_4","CREDIT_4","LETTER GRADE_4","SGPA","CGPA","CGPA In Word","PERCENTAGE OF MARKS","Date Declaration of Results","Division","PHOTO");
        //         $mismatchColArr=array_diff($rowData1[0], $columnArr);
        //         if(count($mismatchColArr)>0){                    
        //             return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
        //         }
        //     }else{
        //         return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
        //     }
        // }
        elseif($dropdown_template_id==4||$dropdown_template_id==5){
            $columnsCount2=count($rowData2[0]);
           
            if($columnsCount1==27 && $columnsCount2==8){
                    $columnArr=array('Unique_Id','Name','Father_Name','Month_&_Year_of_Exam','University','Memo_No','Examination','Branch','Gender','Hall_Ticket_No','College_Code','Serial_No','Subjects_Registered','Appeared','Passed','Total_Grade_Secured','Total_GI','Total_Result','Total_CI','SGPA','CGPA','Place','Date','Image','Marksheet','Mother name','SGPA in words');

                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);


                }


                $columnArr2=array('Hall_Ticket_No','Sr_no','Subject_Code','Subject_Title','Grade_Secured','Grade_Point_Gi','Result','Credits_Obtained_Ci');
                $mismatchColArr=array_diff($rowData2[0], $columnArr2);
                // dd($rowData2[0]);
                if(count($mismatchColArr)>0){
                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet2 : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }

         if($dropdown_template_id==8 ){


             if($columnsCount1==27){
                $columnArr=array("Unique_Id","Name","Parents_Name","Month_&_Year_of_Exam","RefNo","Degree","Branch","Gender","Hall_Ticket_No","College_Code","Class_Awarded","Serial_No","CGPA Secured","Credits Registered","Credits Secured","Place","Date","Image","Marksheet","Note","Course Year","Mother Name","CGPA In Word","Percentage Marks","Division",'Heading1','Heading2');
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }

         }

          elseif($dropdown_template_id==9){
            // dd($rowData1[0]);
            $columnsCount2=count($rowData2[0]);
           
                    if($columnsCount1==32 && $columnsCount2==9){
                         $columnArr=array('Unique_Id','Name','Father_Name','Month_&_Year_of_Exam','University','Memo_No','Examination','Branch','Gender','Hall_Ticket_No','College_Code','Serial_No','Subjects_Registered','Appeared','Passed','Total_Grade_Secured','Total_GI','Total_Result','Total_CI','SGPA','CGPA','Place','Date','Image','Marksheet','Mother name','SGPA in words',"Percentage Marks","Division",'Course','Heading1','Heading2');

                        $mismatchColArr=array_diff($rowData1[0], $columnArr);
                        if(count($mismatchColArr)>0){
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);


                        }


                        $columnArr2=array('Hall_Ticket_No','Sem','Sr_no','Subject_Code','Subject_Title','Grade_Secured','Grade_Point_Gi','Result','Credits_Obtained_Ci');
                        $mismatchColArr=array_diff($rowData2[0], $columnArr2);
                        // dd($rowData2[0]);
                        if(count($mismatchColArr)>0){
                            
                            return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet2 : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
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
			// if($dropdown_template_id==3){
			// 	$profile_path = public_path().'\\'.$subdomain[0].'\backend\templates\100\\'.$value1[$photo_col];
			// 	if (!file_exists($profile_path)) {   
			// 		 array_push($profErrArr, $value1[$photo_col]);
			// 	}
			// }
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
            if($dropdown_template_id==2 ||$dropdown_template_id==4||$dropdown_template_id==5){
                  if($dropdown_template_id==2){
                      $Photo=$value1[11];
                    }elseif($dropdown_template_id==4||$dropdown_template_id==5){
                       $Photo=$value1[23];

                    }

                     $template_id=100;
                    // $profile_path_png = public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.png';
                    // $profile_path_jpg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpg';
                    // $profile_path_jpeg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpeg';
                    $profile_path_org="";
                    // if (file_exists($profile_path_png)) {
                    //     $profile_path_org = $profile_path_png; // Use PNG if it exists
                    // } elseif (file_exists($profile_path_jpg)) {
                    //     $profile_path_org = $profile_path_jpg; // Use JPG if it exists
                    // } elseif (file_exists($profile_path_jpeg)) {
                    //     $profile_path_org = $profile_path_jpeg; // Use JPG if it exists
                    // }else {
                    //     $profile_path_org = '';
                    // }
                    $profile_path_jpg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo;
                    if (file_exists($profile_path_jpg)) {
                        $profile_path_org = $profile_path_jpg; 
                    }else {
                        $profile_path_org = '';
                    }
                  
                    if($dropdown_template_id==2||$dropdown_template_id==4||$dropdown_template_id==5){
                        if (empty($profile_path_org)) {
                            array_push($profErrArr,$Photo);
                        }
                    }
            }else if($dropdown_template_id==1||$dropdown_template_id==8){
                    $Photo=$value1[17];
                    $template_id=100;
                    $profile_path_png = public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.png';
                    $profile_path_jpg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpg';
                    $profile_path_jpeg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpeg';
                    $profile_path_org="";
                    if (file_exists($profile_path_png)) {
                        $profile_path_org = $profile_path_png; // Use PNG if it exists
                    } elseif (file_exists($profile_path_jpg)) {
                        $profile_path_org = $profile_path_jpg; // Use JPG if it exists
                    } elseif (file_exists($profile_path_jpeg)) {
                        $profile_path_org = $profile_path_jpeg; // Use JPG if it exists
                    }else {
                        $profile_path_org = '';
                    }
                    if($dropdown_template_id==2){
                        if (empty($profile_path_org)) {
                            array_push($profErrArr, $value1[233]);
                        }
                    }
            }
            else if($dropdown_template_id==3){
                    $Photo=$value1[11];
                    $template_id=100;
                    $profile_path_png = public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.png';
                    $profile_path_jpg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpg';
                    $profile_path_jpeg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo.'.jpeg';
                    $profile_path_org="";
                    if (file_exists($profile_path_png)) {
                        $profile_path_org = $profile_path_png; // Use PNG if it exists
                    } elseif (file_exists($profile_path_jpg)) {
                        $profile_path_org = $profile_path_jpg; // Use JPG if it exists
                    } elseif (file_exists($profile_path_jpeg)) {
                        $profile_path_org = $profile_path_jpeg; // Use JPG if it exists
                    }else {
                        $profile_path_org = '';
                    }
                    if($dropdown_template_id==2){
                        if (empty($profile_path_org)) {
                            array_push($profErrArr, $value1[233]);
                        }
                    }
            }
             else if($dropdown_template_id==7){
                 
                    $Photo=$value1[17];
                    $template_id=100;
                    $profile_path_org="";
                    $profile_path_jpg=public_path().'\\'.$subdomain[0].'\backend\templates\\'.$template_id.'\\'.$Photo;
                    if (file_exists($profile_path_jpg)) {
                        $profile_path_org = $profile_path_jpg; 
                    }else {
                        $profile_path_org = '';
                    }
                  
                    if($dropdown_template_id==7){
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
        /*  $mismatchProfArr = array_intersect($profArr, array_unique(array_diff_key($profArr, array_unique($profArr))));

        if(count($mismatchProfArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo contains following duplicate values : '.implode(',', $mismatchProfArr)]);
        }*/
                
        //kamini chnages
        // if($dropdown_template_id==2 ||$dropdown_template_id==4){
        //     if(count($profErrArr)>0){
        //         return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo does not exists following values : '.implode(',', $profErrArr)]);
        //     }
        // }
        if(count($profErrArr)>0){
            return response()->json(['success'=>false,'type' => 'error','message' => 'Sheet1 : Photo does not exists following values : '.implode(',', $profErrArr)]);
        }
              
        return response()->json(['success'=>true,'type' => 'success', 'message' => 'success','old_rows'=>$old_rows,'new_rows'=>$new_rows]);
    }

  
}

