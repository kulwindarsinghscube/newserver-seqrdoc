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

        return $query->orderBy('created_at', 'desc')->get([
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
            $convoStudent->issue_date,
            '',
            
        ];
    }
}
