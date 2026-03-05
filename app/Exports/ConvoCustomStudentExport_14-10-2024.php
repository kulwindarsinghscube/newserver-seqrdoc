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

        // Apply filters if present
        if (!empty($this->filters['prn'])) {
            $query->where('prn', 'like', '%' . $this->filters['prn'] . '%');
        }
        if (!empty($this->filters['name'])) {
            $query->where('full_name', 'like', '%' . $this->filters['name'] . '%');
        } 
        if (!empty($this->filters['status'])) {
            // if ($this->filters['status'] == 'registration completed') { 
            //     $registration_completed = [
            //         'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved',
            //         'student acknowledge all data as correct, Payment is completed and preview pdf is approved'
            //     ];
                
            //     $query->whereIn('status', $registration_completed);
            // }else{
            //      $query->where('status', $this->filters['status']);
            // }   
        }
        if (!empty($this->filters['course'])) {
            $query->where('course_name', 'like', '%' . $this->filters['course'] . '%');
        }
        if (!empty($this->filters['student_type'])) {
            $query->where('student_type',$this->filters['student_type']);
       }else{
        $query->where('student_type',0);
       }
       if (!empty($this->filters['faculty_name']) && isset($this->filters['faculty_name'])) {
        $query->where('faculty_name', $this->filters['faculty_name']);
      }
    //    $query->where(function($query) {
    //         $query->whereNotNull('medal_type')
    //             ->where('medal_type', '!=', '');
    //     });

        // $student_data = [
        //     "925","975","2681","970","1152","1784","1290","3035","2175","3326","279","305","329","538","568","479","679","1538","1573","6456","6404","6405","6337","6406","6478","4425","6436","2887","2892","2904","3434","3445","3259","3459","3464","3465","3697","3700","3711","6259","1875","148","129","4197","4175","4209","1899","5747","3714","2525","2602","4428","4432","4441","4431","79","113","4732","6558","6607","4127","6613","4126","1825","2375","2384","1922","1925","5733","4458","1935","2726","5627","3725","7076","7074","7078","7084","7303","7126","7301","7308","3532","3779","5664","7128","7127","5586","3376","7133","7325","5632","7131","7312","4773","3576","5590","3594","7138","7337","5762","5761","7139","5670","3476","4166","163","2625","2629","2643","2676","2688","4656","4658","1275","1225","5469","2976","2697","2931","2997","2926","2724","2658","2877","2672","3336","3335","3368","2876","4198","6335","4202","4210","4646","2595"    
        
        // ];
        // $query->whereIn('id',$student_data);
        $query->whereIn('status',[
            // 'student acknowledge all data as correct but payment & preview pdf approval is pending',
            'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending',
            'student acknowledge all data as correct, Payment is completed and preview pdf is approved',
            // 'student re-acknowledged new data as correct but payment & preview pdf approval is pending',
            'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending',
            'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved'
        ]);
        
        // $query->where(function ($query) {
        //     $query->where('mother_name_hindi', 'NOT LIKE', '%ॐ%')
        //           ->where('full_name_hindi', 'NOT LIKE', '%ॐ%')
        //           ->where('mother_name_hindi', 'NOT LIKE', '%क्त%')
        //           ->where('full_name_hindi', 'NOT LIKE', '%क्त%')
        //           ->where('mother_name_hindi', 'NOT LIKE', '%ढ्ढ%')
        //           ->where('full_name_hindi', 'NOT LIKE', '%ढ्ढ%');
        // });
        // $query->where(function ($query) {
        //     $query->where('mother_name_hindi', 'LIKE', '%ॐ%')
        //           ->orWhere('full_name_hindi', 'LIKE', '%ॐ%')
        //           ->orWhere('mother_name_hindi', 'LIKE', '%क्त%')
        //           ->orWhere('full_name_hindi', 'LIKE', '%क्त%')
        //           ->orWhere('mother_name_hindi', 'LIKE', '%ढ्ढ%')
        //           ->orWhere('full_name_hindi', 'LIKE', '%ढ्ढ%');
        // });
        // dd($query->toSql(), $query->getBindings());

        
        
       
        return $query->orderBy('faculty_name', 'asc')->orderBy('course_name', 'asc')->orderBy('specialization', 'asc')->get([
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
            'topic_krutidev'
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
            // 'Rank',
            // 'Rank (Krutidev)',
            // 'Medal',
            // 'Medal (Krutidev)',
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
            'Issue Date (Krutidev)'
        ];
    }

    public function map($convoStudent): array
    {
        return [
            // $this->rowNumber++, // Increment and return the serial number 
            $convoStudent->prn,
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

            // $convoStudent->rank,
            // $convoStudent->rank_krutidev,

            // $convoStudent->medal_type,
            // $convoStudent->medal_type_krutidev,

            $this->filters['student_type'] == 0 ? $convoStudent->cgpa: $convoStudent->topic,
            $this->filters['student_type'] == 0 ?$convoStudent->cgpa_hindi:$convoStudent->topic_hindi, 
            $this->filters['student_type'] == 0 ?$convoStudent->cgpa_krutidev:$convoStudent->topic_krutidev, 

            $convoStudent->student_photo,
            $convoStudent->faculty_name,
            '',
            $convoStudent->faculty_name_krutidev,
            $convoStudent->specialization,
            $convoStudent->specialization_krutidev,
            $convoStudent->certificateid,
            $convoStudent->completion_date,
            $convoStudent->completion_date_krutidev,
            // $this->filters['student_type'] == 0 ?"May/June 2024" :$convoStudent->completion_date,
            // $this->filters['student_type'] == 0 ?"eÃ @ twu „å„†" :$convoStudent->completion_date_krutidev,
            $convoStudent->issue_date,
            '',
            
        ];
    }
}
