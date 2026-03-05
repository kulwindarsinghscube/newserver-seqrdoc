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
class ValidateExcelSaiuJob
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
        // dd($columnsCount1);
        // echo $columnsCount1;
        // exit;
        if($dropdown_template_id==1){
            if($columnsCount1==100){
				$columnArr=array("Unique id","Name","PRN No","Month & Year of Passing","Date of Issue","Programme","Code1","Course Title1","Grade1","Credits1","Code2","Course Title2","Grade2","Credits2","Code3","Course Title3","Grade3","Credits3","Code4","Course Title4","Grade4","Credits4","Code5","Course Title5","Grade5","Credits5","Code21","Course Title21","Grade21","Credits21","Code22","Course Title22","Grade22","Credits22","Code23","Course Title23","Grade23","Credits23","Code24","Course Title24","Grade24","Credits24","Code25","Course Title25","Grade25","Credits25","Code31","Course Title31","Grade31","Credits31","Code32","Course Title32","Grade32","Credits32","Code33","Course Title33","Grade33","Credits33","Code34","Course Title34","Grade34","Credits34","Code35","Course Title35","Grade35","Credits35","COMM Code 1","COMM Course Title 1","COMM Grade 1","COMM Code 2","COMM Course Title 2","COMM Grade 2","COMM Code 3","COMM Course Title 3","COMM Grade 3","Grade 3 STATUS","WWBL CODE1","WWBL  Course Title 1","WWBL  Grade 1","WWBL CODE2","WWBL  Course Title 2","WWBL  Grade 2","WWBL CODE3","WWBL  Course Title 3","WWBL  Grade 3","WWBL Grade STATUS","TGPA 1","Credits Earned 1","TGPA 2","Credits Earned 2","TGPA 3","Credits Earned 3","Total Credits Earned ","CGPA","Result","Dean Signature","COE Signature","Lab1 Title","Lab2 Title","Serial NO.");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }
		elseif($dropdown_template_id==2){
            // dd($columnsCount1);
            if($columnsCount1==8){
                //$photo_col=23;
				//$columnArr=array("Unique id","Serial No.","Name","Year","Specialization","Special Recognition");
                $columnArr=array("Unique id","Serial No.","Name","Course Name","Year","Specialization","PRN","Convocation Date");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }elseif($dropdown_template_id==3){
            if($columnsCount1==72){
				$columnArr=array("Unique id","Name","PRN No","Month & Year of Passing","Date of Issue","Programme","Code 11","Course Title 11","Grade 11","Credits 11","Code 12","Course Title 12","Grade 12","Credits 12","Code 13","Course Title 13","Grade 13","Credits 13","Code 14","Course Title 14","Grade 14","Credits 14","Code 15","Course Title 15","Grade 15","Credits 15","Code21","Course Title21","Grade21","Credits21","Code22","Course Title22","Grade22","Credits22","Code23","Course Title23","Grade23","Credits23","Code24","Course Title24","Grade24","Credits24","Code25","Course Title25","Grade25","Credits25","Code31","Course Title31","Grade31","Credits31","Code32","Course Title32","Grade32","Credits32","Code33","Course Title33","Grade33","Credits33","Code34","Course Title34","Grade34","Credits34","TGPA 1","Credits Earned 1","TGPA 2","Credits Earned 2","TGPA 3","Credits Earned 3","Total Credits Earned","CGPA","Dean Signature","COE Signature");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }elseif($dropdown_template_id==4){
            if($columnsCount1==100){
				$columnArr=array("Unique id","Name","PRN No","Month & Year of Passing","Date of Issue","Programme","Code1","Course Title1","Grade1","Credits1","Code2","Course Title2","Grade2","Credits2","Code3","Course Title3","Grade3","Credits3","Code4","Course Title4","Grade4","Credits4","Code5","Course Title5","Grade5","Credits5","Code21","Course Title21","Grade21","Credits21","Code22","Course Title22","Grade22","Credits22","Code23","Course Title23","Grade23","Credits23","Code24","Course Title24","Grade24","Credits24","Code25","Course Title25","Grade25","Credits25","Code31","Course Title31","Grade31","Credits31","Code32","Course Title32","Grade32","Credits32","Code33","Course Title33","Grade33","Credits33","Code34","Course Title34","Grade34","Credits34","Code35","Course Title35","Grade35","Credits35","COMM Code 1","COMM Course Title 1","COMM Grade 1","COMM Code 2","COMM Course Title 2","COMM Grade 2","COMM Code 3","COMM Course Title 3","COMM Grade 3","Grade 3 STATUS","WWBL CODE1","WWBL  Course Title 1","WWBL  Grade 1","WWBL CODE2","WWBL  Course Title 2","WWBL  Grade 2","WWBL CODE3","WWBL  Course Title 3","WWBL  Grade 3","WWBL Grade STATUS","TGPA 1","Credits Earned 1","TGPA 2","Credits Earned 2","TGPA 3","Credits Earned 3","Total Credits Earned ","CGPA","Result","Dean Signature","COE Signature","Lab1 Title","Lab2 Title","Serial NO.");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }elseif($dropdown_template_id==5){
            if($columnsCount1==100){
				$columnArr=array("Unique id","Name","PRN No","Month & Year of Passing","Date of Issue","Programme","Code1","Course Title1","Grade1","Credits1","Code2","Course Title2","Grade2","Credits2","Code3","Course Title3","Grade3","Credits3","Code4","Course Title4","Grade4","Credits4","Code5","Course Title5","Grade5","Credits5","Code21","Course Title21","Grade21","Credits21","Code22","Course Title22","Grade22","Credits22","Code23","Course Title23","Grade23","Credits23","Code24","Course Title24","Grade24","Credits24","Code25","Course Title25","Grade25","Credits25","Code31","Course Title31","Grade31","Credits31","Code32","Course Title32","Grade32","Credits32","Code33","Course Title33","Grade33","Credits33","Code34","Course Title34","Grade34","Credits34","Code35","Course Title35","Grade35","Credits35","COMM Code 1","COMM Course Title 1","COMM Grade 1","COMM Code 2","COMM Course Title 2","COMM Grade 2","COMM Code 3","COMM Course Title 3","COMM Grade 3","Grade 3 STATUS","WWBL CODE1","WWBL  Course Title 1","WWBL  Grade 1","WWBL CODE2","WWBL  Course Title 2","WWBL  Grade 2","WWBL CODE3","WWBL  Course Title 3","WWBL  Grade 3","WWBL Grade STATUS","TGPA 1","Credits Earned 1","TGPA 2","Credits Earned 2","TGPA 3","Credits Earned 3","Total Credits Earned ","CGPA","Result","Dean Signature","COE Signature","Lab1 Title","Lab2 Title","Serial NO.");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }elseif($dropdown_template_id==6){
            if($columnsCount1==58){
				$columnArr=array("Unique id","Name","PRN No","Father's Nome","Mother’s Nome","Semester Program","Month & Year of Examination","Dote of Issue","SI.No1","Course Code1","Course Nome1","Grades1","Credits1","SI.No2","Course Code2","Course Nome2","Grades2","Credits2","SI.No3","Course Code3","Course Nome3","Grades3","Credits3","SI.No4","Course Code4","Course Nome4","Grades4","Credits4","SI.No5","Course Code5","Course Nome5","Grades5","Credits5","SI.No6","Course Code6","Course Nome6","Grades6","Credits6","SI.No7","Course Code7","Course Nome7","Grades7","Credits7","SI.No8","Course Code8","Course Nome8","Grades8","Credits8","SI.No9","Course Code9","Course Nome9","Grades9","Credits9","Semester","SGPA","Credits Earned","DEAN Signature","CONTROLLER  OF EXAMINATION Signature");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }elseif($dropdown_template_id==7){
            if($columnsCount1==238){
				$columnArr=array("unique id","Transcript Issue Date","Name","PRN","School of Arts and Sciences","Cumulative GPA","Earned Credit","I SEMESTER","COURSE CODE_11","COURSE _11","CREDIT_11","GRADE_11","COURSE CODE_12","COURSE_12","CREDIT_12","GRADE_12","COURSE CODE_13","COURSE_13","CREDIT_13","GRADE_13","COURSE CODE_14","COURSE_14","CREDIT_14","GRADE_14","COURSE CODE_15","COURSE_15","CREDIT_15","GRADE_15","COURSE CODE_16","COURSE_16","CREDIT_16","GRADE_16","COURSE CODE_17","COURSE_17","CREDIT_17","GRADE_17","COURSE CODE_18","COURSE_18","CREDIT_18","GRADE_18","COURSE CODE_19","COURSE_19","CREDIT_19","GRADE_19","SGPA_1","II SEMESTER","COURSE CODE_21","COURSE_21","CREDIT_21","GRADE_21","COURSE CODE_22","COURSE_22","CREDIT_22","GRADE_22","COURSE CODE_23","COURSE_23","CREDIT_23","GRADE_23","COURSE CODE_24","COURSE_24","CREDIT_24","GRADE_24","COURSE CODE_25","COURSE_25","CREDIT_25","GRADE_25","COURSE CODE_26","COURSE_26","CREDIT_26","GRADE_26","COURSE CODE_27","COURSE_27","CREDIT_27","GRADE_27","COURSE CODE_28","COURSE_28","CREDIT_28","GRADE_28","COURSE CODE_29","COURSE_29","CREDIT_29","GRADE_29","SGPA_2","III SEMESTER","COURSE CODE_31","COURSE_31","CREDIT_31","GRADE_31","COURSE CODE_32","COURSE_32","CREDIT_32","GRADE_32","COURSE CODE_33","COURSE_33","CREDIT_33","GRADE_33","COURSE CODE_34","COURSE_34","CREDIT_34","GRADE_34","COURSE CODE_35","COURSE_35","CREDIT_35","GRADE_35","COURSE CODE_36","COURSE_36","CREDIT_36","GRADE_36","COURSE CODE_37","COURSE_37","CREDIT_37","GRADE_37","COURSE CODE_38","COURSE_38","CREDIT_38","GRADE_38","COURSE CODE_39","COURSE_39","CREDIT_39","GRADE_39","SGPA_3","IV SEMESTER","COURSE CODE_41","COURSE_41","CREDIT_41","GRADE_41","COURSE CODE_42","COURSE_42","CREDIT_42","GRADE_42","COURSE CODE_43","COURSE_43","CREDIT_43","GRADE_43","COURSE CODE_44","COURSE_44","CREDIT_44","GRADE_44","COURSE CODE_45","COURSE_45","CREDIT_45","GRADE_45","COURSE CODE_46","COURSE_46","CREDIT_46","GRADE_46","COURSE CODE_47","COURSE_47","CREDIT_47","GRADE_47","COURSE CODE_48","COURSE_48","CREDIT_48","GRADE_48","COURSE CODE_49","COURSE_49","CREDIT_49","GRADE_49","SGPA_4","V SEMESTER","COURSE CODE_51","COURSE_51","CREDIT_51","GRADE_51","COURSE CODE_52","COURSE_52","CREDIT_52","GRADE_52","COURSE CODE_53","COURSE_53","CREDIT_53","GRADE_53","COURSE CODE_54","COURSE_54","CREDIT_54","GRADE_54","COURSE CODE_55","COURSE_55","CREDIT_55","GRADE_55","COURSE CODE_56","COURSE_56","CREDIT_56","GRADE_56","COURSE CODE_57","COURSE_57","CREDIT_57","GRADE_57","COURSE CODE_58","COURSE_58","CREDIT_58","GRADE_58","COURSE CODE_59","COURSE_59","CREDIT_59","GRADE_59","SGPA_5","VI SEMESTER","COURSE CODE_61","COURSE_61","CREDIT_61","GRADE_61","COURSE CODE_62","COURSE_62","CREDIT_62","GRADE_62","COURSE CODE_63","COURSE_63","CREDIT_63","GRADE_63","COURSE CODE_64","COURSE_64","CREDIT_64","GRADE_64","COURSE CODE_65","COURSE_65","CREDIT_65","GRADE_65","COURSE CODE_66","COURSE_66","CREDIT_66","GRADE_66","COURSE CODE_67","COURSE_67","CREDIT_67","GRADE_67","COURSE CODE_68","COURSE_68","CREDIT_68","GRADE_68","COURSE CODE_69","COURSE_69","CREDIT_69","GRADE_69","SGPA_6","Academic year 1","Academic year 2","Academic year 3");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                // dd($columnArr);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }

        }elseif($dropdown_template_id==8){
            if($columnsCount1==314){
				$columnArr = array(
    "unique id", "Transcript Issue Date", "Name", "PRN", "School of Arts and Sciences", "Cumulative GPA","Earned Credit",

    // I SEMESTER
    "I SEMESTER",
    "COURSE CODE_11", "COURSE _11", "CREDIT_11", "GRADE_11",
    "COURSE CODE_12", "COURSE_12", "CREDIT_12", "GRADE_12",
    "COURSE CODE_13", "COURSE_13", "CREDIT_13", "GRADE_13",
    "COURSE CODE_14", "COURSE_14", "CREDIT_14", "GRADE_14",
    "COURSE CODE_15", "COURSE_15", "CREDIT_15", "GRADE_15",
    "COURSE CODE_16", "COURSE_16", "CREDIT_16", "GRADE_16",
    "COURSE CODE_17", "COURSE_17", "CREDIT_17", "GRADE_17",
    "COURSE CODE_18", "COURSE_18", "CREDIT_18", "GRADE_18",
    "COURSE CODE_19", "COURSE_19", "CREDIT_19", "GRADE_19",
    "SGPA_1",

    // II SEMESTER
    "II SEMESTER",
    "COURSE CODE_21", "COURSE_21", "CREDIT_21", "GRADE_21",
    "COURSE CODE_22", "COURSE_22", "CREDIT_22", "GRADE_22",
    "COURSE CODE_23", "COURSE_23", "CREDIT_23", "GRADE_23",
    "COURSE CODE_24", "COURSE_24", "CREDIT_24", "GRADE_24",
    "COURSE CODE_25", "COURSE_25", "CREDIT_25", "GRADE_25",
    "COURSE CODE_26", "COURSE_26", "CREDIT_26", "GRADE_26",
    "COURSE CODE_27", "COURSE_27", "CREDIT_27", "GRADE_27",
    "COURSE CODE_28", "COURSE_28", "CREDIT_28", "GRADE_28",
    "COURSE CODE_29", "COURSE_29", "CREDIT_29", "GRADE_29",
    "SGPA_2",

    // III SEMESTER
    "III SEMESTER",
    "COURSE CODE_31", "COURSE_31", "CREDIT_31", "GRADE_31",
    "COURSE CODE_32", "COURSE_32", "CREDIT_32", "GRADE_32",
    "COURSE CODE_33", "COURSE_33", "CREDIT_33", "GRADE_33",
    "COURSE CODE_34", "COURSE_34", "CREDIT_34", "GRADE_34",
    "COURSE CODE_35", "COURSE_35", "CREDIT_35", "GRADE_35",
    "COURSE CODE_36", "COURSE_36", "CREDIT_36", "GRADE_36",
    "COURSE CODE_37", "COURSE_37", "CREDIT_37", "GRADE_37",
    "COURSE CODE_38", "COURSE_38", "CREDIT_38", "GRADE_38",
    "COURSE CODE_39", "COURSE_39", "CREDIT_39", "GRADE_39",
    "SGPA_3",

    // IV SEMESTER
    "IV SEMESTER",
    "COURSE CODE_41", "COURSE_41", "CREDIT_41", "GRADE_41",
    "COURSE CODE_42", "COURSE_42", "CREDIT_42", "GRADE_42",
    "COURSE CODE_43", "COURSE_43", "CREDIT_43", "GRADE_43",
    "COURSE CODE_44", "COURSE_44", "CREDIT_44", "GRADE_44",
    "COURSE CODE_45", "COURSE_45", "CREDIT_45", "GRADE_45",
    "COURSE CODE_46", "COURSE_46", "CREDIT_46", "GRADE_46",
    "COURSE CODE_47", "COURSE_47", "CREDIT_47", "GRADE_47",
    "COURSE CODE_48", "COURSE_48", "CREDIT_48", "GRADE_48",
    "COURSE CODE_49", "COURSE_49", "CREDIT_49", "GRADE_49",
    "SGPA_4",

    // V SEMESTER
    "V SEMESTER",
    "COURSE CODE_51", "COURSE_51", "CREDIT_51", "GRADE_51",
    "COURSE CODE_52", "COURSE_52", "CREDIT_52", "GRADE_52",
    "COURSE CODE_53", "COURSE_53", "CREDIT_53", "GRADE_53",
    "COURSE CODE_54", "COURSE_54", "CREDIT_54", "GRADE_54",
    "COURSE CODE_55", "COURSE_55", "CREDIT_55", "GRADE_55",
    "COURSE CODE_56", "COURSE_56", "CREDIT_56", "GRADE_56",
    "COURSE CODE_57", "COURSE_57", "CREDIT_57", "GRADE_57",
    "COURSE CODE_58", "COURSE_58", "CREDIT_58", "GRADE_58",
    "COURSE CODE_59", "COURSE_59", "CREDIT_59", "GRADE_59",
    "SGPA_5",

    // VI SEMESTER
    "VI SEMESTER",
    "COURSE CODE_61", "COURSE_61", "CREDIT_61", "GRADE_61",
    "COURSE CODE_62", "COURSE_62", "CREDIT_62", "GRADE_62",
    "COURSE CODE_63", "COURSE_63", "CREDIT_63", "GRADE_63",
    "COURSE CODE_64", "COURSE_64", "CREDIT_64", "GRADE_64",
    "COURSE CODE_65", "COURSE_65", "CREDIT_65", "GRADE_65",
    "COURSE CODE_66", "COURSE_66", "CREDIT_66", "GRADE_66",
    "COURSE CODE_67", "COURSE_67", "CREDIT_67", "GRADE_67",
    "COURSE CODE_68", "COURSE_68", "CREDIT_68", "GRADE_68",
    "COURSE CODE_69", "COURSE_69", "CREDIT_69", "GRADE_69",
    "SGPA_6",

    // VII SEMESTER
    "VII SEMESTER",
    "COURSE CODE_71", "COURSE_71", "CREDIT_71", "GRADE_71",
    "COURSE CODE_72", "COURSE_72", "CREDIT_72", "GRADE_72",
    "COURSE CODE_73", "COURSE_73", "CREDIT_73", "GRADE_73",
    "COURSE CODE_74", "COURSE_74", "CREDIT_74", "GRADE_74",
    "COURSE CODE_75", "COURSE_75", "CREDIT_75", "GRADE_75",
    "COURSE CODE_76", "COURSE_76", "CREDIT_76", "GRADE_76",
    "COURSE CODE_77", "COURSE_77", "CREDIT_77", "GRADE_77",
    "COURSE CODE_78", "COURSE_78", "CREDIT_78", "GRADE_78",
    "COURSE CODE_79", "COURSE_79", "CREDIT_79", "GRADE_79",
    "SGPA_7",

    // VIII SEMESTER
    "VIII SEMESTER",
    "COURSE CODE_81", "COURSE_81", "CREDIT_81", "GRADE_81",
    "COURSE CODE_82", "COURSE_82", "CREDIT_82", "GRADE_82",
    "COURSE CODE_83", "COURSE_83", "CREDIT_83", "GRADE_83",
    "COURSE CODE_84", "COURSE_84", "CREDIT_84", "GRADE_84",
    "COURSE CODE_85", "COURSE_85", "CREDIT_85", "GRADE_85",
    "COURSE CODE_86", "COURSE_86", "CREDIT_86", "GRADE_86",
    "COURSE CODE_87", "COURSE_87", "CREDIT_87", "GRADE_87",
    "COURSE CODE_88", "COURSE_88", "CREDIT_88", "GRADE_88",
    "COURSE CODE_89", "COURSE_89", "CREDIT_89", "GRADE_89",
    "SGPA_8",

    // Academic Years
    "Academic year 1", "Academic year 2", "Academic year 3"
);

                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                // dd($rowData1);
                if(count($mismatchColArr)>0){                    
                    return response()->json(['success'=>false,'type' => 'error', 'message' => 'Sheet1/Json : Column names not matching as per requirement. Please check columns : '.implode(',', $mismatchColArr)]);
                }
            }else{
                return response()->json(['success'=>false,'type' => 'error', 'message'=>'Columns count of excel do not matched!']);
            }
        }
        elseif($dropdown_template_id==9){
            if($columnsCount1==86){
				$columnArr=array("unique id","Transcript Issue Date","Name","PRN","School of Arts and Sciences","Cumulative GPA","Earned Credit","I SEMESTER","COURSE CODE_11","COURSE _11","CREDIT_11","GRADE_11","COURSE CODE_12","COURSE_12","CREDIT_12","GRADE_12","COURSE CODE_13","COURSE_13","CREDIT_13","GRADE_13","COURSE CODE_14","COURSE_14","CREDIT_14","GRADE_14","COURSE CODE_15","COURSE_15","CREDIT_15","GRADE_15","COURSE CODE_16","COURSE_16","CREDIT_16","GRADE_16","COURSE CODE_17","COURSE_17","CREDIT_17","GRADE_17","COURSE CODE_18","COURSE_18","CREDIT_18","GRADE_18","COURSE CODE_19","COURSE_19","CREDIT_19","GRADE_19","SGPA_1","II SEMESTER","COURSE CODE_21","COURSE_21","CREDIT_21","GRADE_21","COURSE CODE_22","COURSE_22","CREDIT_22","GRADE_22","COURSE CODE_23","COURSE_23","CREDIT_23","GRADE_23","COURSE CODE_24","COURSE_24","CREDIT_24","GRADE_24","COURSE CODE_25","COURSE_25","CREDIT_25","GRADE_25","COURSE CODE_26","COURSE_26","CREDIT_26","GRADE_26","COURSE CODE_27","COURSE_27","CREDIT_27","GRADE_27","COURSE CODE_28","COURSE_28","CREDIT_28","GRADE_28","COURSE CODE_29","COURSE_29","CREDIT_29","GRADE_29","SGPA_2","Academic year 1","Academic year 2","Academic year 3");
                $mismatchColArr=array_diff($rowData1[0], $columnArr);
                // dd($columnArr);
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

