<?php

namespace App\Exports;

use App\Models\convodataverification\ConvoStudent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon; // Import Carbon for date handling

class ConvoCustomMedalistStudentExport implements FromCollection, WithHeadings, WithMapping
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
        $query->where('faculty_name', 'like', '%' . $this->filters['faculty_name'] . '%');
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
        //Enable when medal data export
           $query->where(function($query) {
                $query->whereNotNull('medal_type')
                    ->where('medal_type', '!=', '');
            });
 
        
       
        return $query
        // ->orderBy('cohort_id', 'asc') 
        ->orderBy('faculty_name', 'asc')
        ->orderBy('course_name', 'asc')
        ->orderBy('specialization', 'asc')
        ->orderBy('medal_type_serial_no', 'asc') //Enable when medal data export
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
           
            'Photograph',
            'Faculty Name',
            'Faculty Name (Hindi)',
            'Faculty Name (Krutidev)',
            'Certificate Id', 
            'Completion Date',
            'Completion Date (Krutidev)',
            'Issue Date',
            'Issue Date (Krutidev)',
            'Medal Type',//Enable when medal data export
            'Medal Type (Krutidev)',//Enable when medal data export
            'Specialization',
            'Specialization (Hindi)', 
            'Specialization (Krutidev)', 
            'Serial No'
        ];
    }

    public function map($convoStudent): array
    {

        $completion_array = $this->getCompletionDate($convoStudent->completion_date,$convoStudent->course_name);
        return [
            // $this->rowNumber++, // Increment and return the serial number  
            "MEDAL_2024_".$convoStudent->prn, //Enable when medal data export
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

          

            $convoStudent->student_photo,
            $convoStudent->faculty_name,
            '',
            $convoStudent->faculty_name_krutidev,
            $convoStudent->certificateid,
            $this->filters['student_type'] == 0 ?$completion_array['completion_date'] : date("F Y", strtotime($convoStudent->completion_date)),
            $this->filters['student_type'] == 0 ?$completion_array['completion_krutidev'] :$convoStudent->completion_date_krutidev,
            $convoStudent->issue_date,
            $convoStudent->issue_date_krutidev,
            $convoStudent->medal_type, //Enable when medal data export
            $convoStudent->medal_type_krutidev,//Enable when medal data export
 
            $convoStudent->specialization,
            '',
            $convoStudent->specialization_krutidev,  
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
