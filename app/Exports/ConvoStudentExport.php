<?php

namespace App\Exports;

use App\Models\convodataverification\ConvoStudent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon; // Import Carbon for date handling

class ConvoStudentExport implements FromCollection, WithHeadings, WithMapping
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
        
        if (!empty($this->filters['status'])){
            if ($this->filters['status'] === 'registration completed') {
                $registration_completed = [
                    'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved',
                    'student acknowledge all data as correct, Payment is completed and preview pdf is approved'
                ];
                $query->whereIn('status', $registration_completed);
            } else {
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

        return $query->orderBy('created_at', 'desc')->get([
            'prn', 
            'wpu_email_id',
            'date_of_birth',
            'full_name',
            'full_name_hindi', 
            'mother_name',
            'mother_name_hindi', 
            'father_name',
            'father_name_hindi', 
            'course_name',
            'course_name_hindi', 
            'cgpa',
            'status',
            'created_at',
            'collection_mode',
            'is_pdf_approved', 
            'first_name',
            'middle_name',
            'last_name',
            'student_mobile_no',
            'permanent_address',
            'local_address',
            'certificateid',
            'cohort_id',
            'cohort_name',
            'faculty_name',
            'specialization',
            'rank',
            'medal_type',
            'completion_date',
            'issue_date',
            'delivery_address',
            'delivery_pincode',
            'delivery_country',
            'attire_size',
             'gender',
             'topic',
             'topic_hindi',
             'cgpa_hindi',
             'no_of_people_accompanied'

        ]);
    }

    public function headings(): array
    {
        return [
            'SR No.',
            'PRN',
            'Email Id',   
            'Full Name (English)',
            'Full Name (Hindi)', 
            'Mother Name (English)',
            'Mother Name (Hindi)', 
            'Father Name (English)',
            'Father Name (Hindi)', 
            'Course Name (English)',
            'Course Name (Hindi)', 
            $this->filters['student_type'] == 0 ?'CGPA':'Topic',
            $this->filters['student_type'] == 0 ?'CGPA (Hindi)':'Topic (Hindi)', 
            'Date of Birth',
            'Status',
            'Photograph',
            'Gender',
            'Collection Mode', 
            'First Name',
            'Middle Name',
            'Last Name',
            'Student Mobile No',
            'Permanent Address',
            'Local Address',
            'Certificate ID',
            'Cohort ID',
            'Cohort Name',
            'Faculty Name',
            'Specialization',
            'Rank',
            'Medal Type',
            'Completion Date',
            'Issue Date',
            'Delivery Address',
            'Delivery Pincode',
            'Delivery Country',
            'Attire Size', 
            'No. Of People Accompanied', 
            'Created Date',
        ];
    }

    public function map($convoStudent): array
    {
        return [
            $this->rowNumber++, // Increment and return the serial number 
            $convoStudent->prn,
            $convoStudent->wpu_email_id,
            $convoStudent->full_name,
            $convoStudent->full_name_hindi, 
            $convoStudent->mother_name,
            $convoStudent->mother_name_hindi, 
            $convoStudent->father_name,
            $convoStudent->father_name_hindi, 
            $convoStudent->course_name,
            $convoStudent->course_name_hindi, 
            $this->filters['student_type'] == 0 ? $convoStudent->cgpa: $convoStudent->topic,
            $this->filters['student_type'] == 0 ? $convoStudent->cgpa_hindi :$convoStudent->topic_hindi, 
            Carbon::parse($convoStudent->date_of_birth)->format('Y-m-d'), // Format DOB as YYYY-MM-DD 
            $convoStudent->status,
            '',
            $convoStudent->gender,
             
            $convoStudent->collection_mode, 
            $convoStudent->first_name,
            $convoStudent->middle_name,
            $convoStudent->last_name,
            $convoStudent->student_mobile_no,
            $convoStudent->permanent_address,
            $convoStudent->local_address,
            $convoStudent->certificateid,
            $convoStudent->cohort_id,
            $convoStudent->cohort_name,
            $convoStudent->faculty_name,
            $convoStudent->specialization,
            $convoStudent->rank,
            $convoStudent->medal_type,
            Carbon::parse($convoStudent->completion_date)->format('Y-m-d'), // Format completion_date as YYYY-MM-DD
            Carbon::parse($convoStudent->issue_date)->format('Y-m-d'), // Format issue_date as YYYY-MM-DD
            $convoStudent->delivery_address,
            $convoStudent->delivery_pincode,
            $convoStudent->delivery_country,
            $convoStudent->attire_size, 
            $convoStudent->no_of_people_accompanied > 0 ? $convoStudent->no_of_people_accompanied : '0',
            Carbon::parse($convoStudent->created_at)->format('Y-m-d h:i:s A'), // Format created_at as YYYY-MM-DD HH:MM:SS AM/PM
        
        ];
    }
}
