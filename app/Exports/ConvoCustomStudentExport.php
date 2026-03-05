<?php

namespace App\Exports;

use App\Models\convodataverification\ConvoStudent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon; // Import Carbon for date handling

class ConvoCustomStudentExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;
    private $rowNumber = 1; // Counter for serial number
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = ConvoStudent::query();
        // $query->where('is_printed',0);
        // Apply filters if present
        if (!empty($this->filters['prn'])) {
            $query->where('prn', 'like', '%' . $this->filters['prn'] . '%');
        }
        if (!empty($this->filters['name'])) {
            $query->where('full_name', 'like', '%' . $this->filters['name'] . '%');
        } 
        if (!empty($this->filters['status'])) {
            if ($this->filters['status'] == 'registration completed') { 
                $registration_completed = [
                    'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved',
                    'student acknowledge all data as correct, Payment is completed and preview pdf is approved'
                ];
                
                $query->whereIn('status', $registration_completed);
            }else{
                 $query->where('status', $this->filters['status']);
            }   
        }
        if (!empty($this->filters['course'])) {
            $query->where('course_name', 'like', '%' . $this->filters['course'] . '%');
        }
        if (!empty($this->filters['student_type'])) {
            $query->where('student_type',$this->filters['student_type']);
       }else{
        $query->where('student_type',0);
       }
       if (!empty($this->filters['faculty_name'])) {
        $query->where('faculty_name', '=',  $this->filters['faculty_name']) ;
        }

        if (!empty($this->filters['specialization'])) {
            $query->where('specialization', 'like', '%' . $this->filters['specialization'] . '%');
        }

        if (!empty($this->filters['completion_year'])) {
            $query->where('completion_date', '=',  $this->filters['completion_year']);
        }
        $query->whereIn('status', [
            'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending',
            'student acknowledge all data as correct, Payment is completed and preview pdf is approved', 
            'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending',
            'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved'
        ]);
        // $query->whereIn('prn',[1062222565,1032191236,1032212293,1032200247,1032210258,1062220145,1062211057,1032210689,1032201729,1032220145,1212210052,1032200434,1032212065,1202210011,1062210947,1032202083,1222220022,1122200009,1062220292,1192220044,1132221073,1122200117,1032211120,1032212254,1032202145,1182190034,1032211599,1102220079,1202210005,1102200182,1062222674,1062220731,1132210754,1032212141,1132210817,1132220401,1032200887,1032202107,1032220908,1132221024,1032211255,1062221376,1032200847,1102220063,1032210478,1062210408,1062211736,1032201074,1032200933,1062220116,1062222120,1032201870,1032202186,1062210619,1132220676,1132220267,1062210378,1182190046,1032212252,1202210006,1062211753,1032211308,1062210320,1132220584,1032201430,1122200047,1202220357,1202210142,1062211432,1032202045,1132220927,1032200731,1202210129,1032211592,1032210115,1032210136,1032210688,1032210407,1032211781,1032211604,1032210557,1032211583,1032201347,1032212189]);

    //   if (!empty($this->filters['cohort_id']) && isset($this->filters['cohort_id'])) {
    //     $query->where('cohort_id', $this->filters['cohort_id']);
    //   } 
      
   
       //Enable when diploma student data export
      //$query->where('course_name', 'like', '%diploma%');
     
      //Enable when non diploma student data export
      //$query->where('course_name', 'not like', '%diploma%');

        //Enable when medal data export
        //    $query->where(function($query) {
        //         $query->whereNotNull('medal_type')
        //             ->where('medal_type', '!=', '');
        //     });

        //Enable when rank data export
        //    $query->where(function($query) {
        //         $query->whereNotNull('rank')
        //             ->where('rank', '!=', '');
        //     });

        // $ids_unique = ["6261","83","1902","127","4735","1882","4440","1828","2377","2401","1922","1931","5733","4478","1935","2726","925","982","4662","1141","1155","1785","2180","160","5557","1276","1258","6335","1291","298","310","334","538","1175","479","688","1538","2431","6559","2573","6404","6457","6342","6423","6478","6439","4427","2596","2608","3345","3347","3714","4128","6613","4126","4179","5628","3742","7100","7154","7078","7162","7305","3544","3780","5666","5588","3380","5633","7137","4778","3580","5590","3607","7338","5761","5671","2684","3479","5479","5625","4204","2626","2633","2649","5752","2987","2700","2944","4656","3000","3278","3048","2958","3240","2668","2886","3026","4167","4200","5756","5757","3268","3461","3467","4212","4648"];
        // $id_rank = ["309","315","577","80","79","81","170","169","168","167"] ;
        // $id_medal = ["80","79","81"];
        // $id_cgpa = ["284","865","965","1429"];
        // $query->whereIn('id',$ids_unique);
        
        // $prn = ['1062211063','1032201398','1032201115'];
        // $query->whereIn('prn',$prn);
        // $query->whereIn('status',[
        //     // 'student acknowledge all data as correct but payment & preview pdf approval is pending',
        //     'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending',
        //     'student acknowledge all data as correct, Payment is completed and preview pdf is approved',
        //     // 'student re-acknowledged new data as correct but payment & preview pdf approval is pending',
        //     'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending',
        //     'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved'
        // ]);
        // $query->whereNull('cohort_id');
        
        // Enable when NON-Exception data export
        // $query->where(function ($query) {
        //     $query->where('mother_name_hindi', 'NOT LIKE', '%ॐ%')
        //           ->where('full_name_hindi', 'NOT LIKE', '%ॐ%')
        //           ->where('mother_name_hindi', 'NOT LIKE', '%क्त%')
        //           ->where('full_name_hindi', 'NOT LIKE', '%क्त%')
        //           ->where('mother_name_hindi', 'NOT LIKE', '%ढ्ढ%')
        //           ->where('full_name_hindi', 'NOT LIKE', '%ढ्ढ%');
        // });


        //Enable when Exception data export
        // $query->where(function ($query) {
        //     $query->where('mother_name_hindi', 'LIKE', '%ॐ%')
        //           ->orWhere('full_name_hindi', 'LIKE', '%ॐ%')
        //           ->orWhere('mother_name_hindi', 'LIKE', '%क्त%')
        //           ->orWhere('full_name_hindi', 'LIKE', '%क्त%')
        //           ->orWhere('mother_name_hindi', 'LIKE', '%ढ्ढ%')
        //           ->orWhere('full_name_hindi', 'LIKE', '%ढ्ढ%');
        // });  
       

        
        
       
        return $query
        // ->orderBy('cohort_id', 'asc') 
        ->orderBy('faculty_name', 'asc')
        ->orderBy('course_name', 'asc')
        ->orderBy('specialization', 'asc')
        // ->orderBy('rank_serial_no', 'asc') //Enable when rank data export
        // ->orderBy('medal_type_serial_no', 'asc') //Enable when medal data export
        ->get([
            'prn', 
            'full_name',
            'full_name_hindi',
            'full_name_krutidev',

            'mother_name',
            'mother_name_hindi',
            'mother_name_krutidev',

            'father_name',
            'father_name_hindi',
            'father_name_krutidev',

            'course_name',
            'course_name_hindi',
            'course_name_krutidev',
            'rank',
            'rank_krutidev',
            'medal_type',
            'medal_type_krutidev',

            'student_photo',
            'faculty_name',
            'faculty_name_krutidev',
            'specialization',
            'specialization_krutidev',
            'certificateid',
            'completion_date',
            'completion_date_krutidev',
            'issue_date',
            'cgpa',
            'cgpa_hindi',
            'cgpa_krutidev',
            'topic',
            'topic_hindi',
            'topic_krutidev',
            'cgpa_temp',
            'cgpa_hindi_temp',
            'cgpa_krutidev_temp',
            'serial_no'
        ]);
    }

    public function headings(): array
    {

        
        return [
            'PRN',
            'Full Name (English)',
            'Full Name (Hindi)',
            'Full Name (Krutidev)',
            'Mother Name (English)',
            'Mother Name (Hindi)',
            'Mother Name (Krutidev)',
            'Father Name (English)',
            'Father Name (Hindi)',
            'Father Name (Krutidev)',
            'Course Name (English)',
            'Course Name (Hindi)',
            'Course Name (Krutidev)',
            // 'Rank',//Enable when rank data export
            // 'Rank (Krutidev)',//Enable when rank data export
            // 'Medal',//Enable when medal data export
            // 'Medal (Krutidev)',//Enable when medal data export
            $this->filters['student_type'] == 0 ?'CGPA':'Topic',
            $this->filters['student_type'] == 0 ?'CGPA (Hindi)':'Topic (Hindi)', 
            $this->filters['student_type'] == 0 ?'CGPA (Krutidev)':'Topic (Krutidev)', 
            'Photograph',
            'Faculty Name',
            'Faculty Name (Hindi)',
            'Faculty Name (Krutidev)',
            'Specialization',
            'Specialization (Krutidev)',

            'Certificate Id',
            
            'Completion Date',
            'Completion Date (Krutidev)',
            'Issue Date',
            'Issue Date (Krutidev)',
            'Serial No'
        ];
    }

    public function map($convoStudent): array
    {

        $completion_array = $this->getCompletionDate($convoStudent->completion_date,$convoStudent->course_name);
        return [
            // $this->rowNumber++, // Increment and return the serial number 
            $this->filters['student_type'] == 0 ?"DC_2024_".$convoStudent->prn:$convoStudent->prn, //Enable when degree and phd data export
            // "RANK_2024_".$convoStudent->prn, //Enable when rank data export
            // "MEDAL_2024_".$convoStudent->prn, //Enable when medal data export
            $convoStudent->full_name,
            $convoStudent->full_name_hindi,
            $convoStudent->full_name_krutidev,
            
            $convoStudent->mother_name,
            $convoStudent->mother_name_hindi,
            $convoStudent->mother_name_krutidev,

            $convoStudent->father_name,
            $convoStudent->father_name_hindi,
            $convoStudent->father_name_krutidev,

            $convoStudent->course_name,
            $convoStudent->course_name_hindi,
            $convoStudent->course_name_krutidev,

            // $convoStudent->rank, //Enable when rank data export
            // $convoStudent->rank_krutidev,//Enable when rank data export

            // $convoStudent->medal_type, //Enable when medal data export
            // $convoStudent->medal_type_krutidev,//Enable when medal data export

            $this->filters['student_type'] == 0 ? " ".$convoStudent->cgpa_temp: $convoStudent->topic,
            $this->filters['student_type'] == 0 ?$convoStudent->cgpa_hindi_temp:$convoStudent->topic_hindi, 
            $this->filters['student_type'] == 0 ?$convoStudent->cgpa_krutidev_temp:$convoStudent->topic_krutidev, 

            $convoStudent->student_photo,
            $convoStudent->faculty_name,
            '',
            $convoStudent->faculty_name_krutidev,
            $convoStudent->specialization,
            $convoStudent->specialization_krutidev,
            $convoStudent->certificateid,
           
            $this->filters['student_type'] == 0 ?$completion_array['completion_date'] : date("F Y", strtotime($convoStudent->completion_date)),
            $this->filters['student_type'] == 0 ?$completion_array['completion_krutidev'] :$convoStudent->completion_date_krutidev,
            $convoStudent->issue_date,
            '',
            $convoStudent->serial_no
            
        ];
    }

   

    public function  getCompletionDate($date,$course_name) {
        // Create a Carbon instance from the input date string
       $dateTime = Carbon::parse($date);
      
       // Define cutoff dates using Carbon
       $cutoffNovemberDecember = Carbon::create(2024, 3, 31);
       $cutoffMay = Carbon::create(2024, 4, 1);
       $cutoffJune = Carbon::create(2024, 6, 30);
       $cutoffJuly = Carbon::create(2024, 7, 1);
       $cutoffSeptember = Carbon::create(2024, 9, 30);
       $data = [];
       // Determine the completion date based on the input date

       if($course_name != 'Master of Technology'){
           if ($dateTime <= $cutoffNovemberDecember) {
               $data['completion_date'] = "November/December 2023";
               $data['completion_krutidev'] = "uoacj@fnlacj 2023";
           
           } elseif ($dateTime >= $cutoffMay && $dateTime <= $cutoffJune) {
               $data['completion_date'] = "May/June 2024";
               $data['completion_krutidev'] = "eÃ@twu 2024"; 
           } elseif ($dateTime >= $cutoffJuly && $dateTime <= $cutoffSeptember) {
               $data['completion_date'] = "August 2024";
               $data['completion_krutidev'] = "vxLr 2024"; 
           }else{
               $data['completion_date'] = "";
               $data['completion_krutidev'] = ""; 
           }
       }else{
           $cutoffNovemberDecember = Carbon::create(2023, 12, 31);
           $cutoffMay = Carbon::create(2024, 5, 1);
           if ($dateTime <= $cutoffNovemberDecember) {
               $data['completion_date'] = "November/December 2023";
               $data['completion_krutidev'] = "uoacj@fnlacj 2023";
           
           } elseif ($dateTime == $cutoffMay){
               $data['completion_date'] = "August 2024";
               $data['completion_krutidev'] = "vxLr 2024"; 
           }else{
               $data['completion_date'] = "";
               $data['completion_krutidev'] = ""; 
           }

       }

       if($dateTime == Carbon::create(2023, 4, 1)){
            $data['completion_date'] = "May/June 2023"; 
            $data['completion_krutidev'] = "eÃ@twu 2023"; 
       }

       return $data; // Optional: handle dates outside the specified ranges
   }


    public function  convertCgpa($cgpa) {
        $cgpa =number_format($cgpa, 2);

       return  " ".$cgpa; // Optional: handle dates outside the specified ranges
   }
    
}
