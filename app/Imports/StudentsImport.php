<?php

namespace App\Imports;

use App\Models\convodataverification\ConvoStudent;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Admin\TextTranslator;
use  App\Mail\convocation\StudentVerified;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public $errors = [];

    public $processed_data = 0;

    public $total_data = 0;

    public $created_data = 0;

    public $updated_data = 0;

    protected $response;

    public function __construct()
    {
        $this->textTranslator = new TextTranslator();

        $this->response = [];
    }
    public function collection(Collection $rows)
    {
        $dataToInsert = [];
        $uniquePrns = [];
        $rowIndex = 1; // Starting index for rows
        // $rows->shift(); // Remove the first row
        // $rows->shift(); // Remove the second row
        // $rows->shift(); // Remove the third row
        $this->total_data = count($rows);
        foreach ($rows as $row) {
            // Skip empty rows
            $error_found = false;
            if (empty(@$row['prn'])) {
                $rowIndex++;
                // continue;
            }
            
            // if (!ctype_alnum($row['prn']))
            // {
            //     $row['prn'] = (int)$row['prn'];
            // }
            //    MITWPUPASS
            // Check for duplicate 'prn' within the dataToInsert array
            if (in_array(@$row['prn'],  $uniquePrns)) {
                $error = array(
                    'prn' => @$row['prn']
                );
                // echo"<pre>";print_r($error);die;
                $this->addFailure($rowIndex, "Duplicate PRN detected", $error, 'prn', 1);
                // $rowIndex++;
                $error_found = true;
                // continue;
            }


            // $dateOfBirth = Carbon::parse(@$row['dob']);
            $formattedDateOfBirth = $this->parseDate($row['dob']);  
            // dd($formattedDateOfBirth);
            if (!$formattedDateOfBirth) {
                $error = array(
                    'dob' => @$row['dob']
                );
                // dd( $error);
                // echo"<pre>";print_r($error);die;
                $this->addFailure($rowIndex, "The date of birth must be in the format dd/mm/YYYY.", $error, 'dob');
                // $rowIndex++;
                $error_found = true;
                // continue;
            }

            $full_name_hindi = $this->textTranslator->transliterateToHindi(@$row['name_as_per_tc']);
            $course_name_hindi = $this->textTranslator->transliterateToHindi(@$row['competency_level']);

            $full_name_krutidev = $this->textTranslator->unicodeToAkruti($full_name_hindi);
            $course_name_krutidev = $this->textTranslator->unicodeToKrutiDev($course_name_hindi);

            $cgpa_hindi = $this->textTranslator->transliterateToHindi(@$row['cgpa']);
            $cgpa_krutidev = $this->textTranslator->unicodeToKrutiDev($cgpa_hindi);
            $completion_date = NULL;
          
            if(!empty(@$row['completion_date'])){
                // $date = Carbon::createFromFormat('F Y', @$row['completion_date']);
                // $date->day = 1;
                // $completion_date = $date->format('Y-m-d');

                $date =  strtotime(@$row['completion_date']) ; 
                $completion_date = date('Y-m-d',$date );
               
            } 
           
            // if(!empty(@$row['issue_date'])){ 

            //     $date =  strtotime(@$row['issue_date']) ; 
            //     $issue_date = date('Y-m-d',$date );
               
            // } 

            if(!empty(@$row['completion_date'])){
               
                $issue_date  = $this->parseDate($row['issue_date']);

            // $completion_date = date('Y-m-d',strtotime($issue_date));
            $issue_date=false;

                // dd($completion_date);

                if ($issue_date) {
                    $error = array(
                        'issue_date' => @$row['issue_date']
                    );
                    // echo"<pre>";print_r($error);die;
                    $this->addFailure($rowIndex, "The issue date must be in the format dd/mm/YYYY.", $error, 'dob', 1);
                    // $rowIndex++;
                    $error_found = true;
                    // continue;
                }else{
                    $issue_date =NULL;
                }
            } 

            // dd($full_name_hindi,$course_name_hindi);
            // Add row to data array and uniquePrns array
            $uniquePrns[] = @$row['prn'];
            if($error_found != true){
                // $specialization_hindi = $textTranslator->transliterateToHindi( @$row['specialization']);
                // $specialization_krutidev = $textTranslator->unicodeToKrutiDev($specialization_hindi);

                // $faculty_name_hindi = $textTranslator->transliterateToHindi(@$row['faculty_name']);
                // $faculty_name_krutidev = $textTranslator->unicodeToKrutiDev($faculty_name_hindi);
                
              $dataToInsert[] = [
    'prn' => @$row['prn'],
    'date_of_birth' => $formattedDateOfBirth,
    'wpu_email_id' => @$row['student_email_id'],
    'secondary_email_id' => @$row['secondary_mail_id'],
    'password' => Hash::make('MITWPUPASS'),
    'full_name' => @$row['name_as_per_tc'],
    'full_name_hindi' => $full_name_hindi,
    'full_name_krutidev' => $full_name_krutidev,
    'mother_name' => @$row['mother_name'],
    'mother_name_hindi' => @$row['mother_name_hindi'],
    'mother_name_krutidev' => @$row['mother_name_krutidev'],
    'father_name' => @$row['father_name'],
    'father_name_hindi' => @$row['father_name_hindi'],
    'father_name_krutidev' => @$row['father_name_krutidev'],
    'course_name' => @$row['competency_level'],
    'course_name_hindi' => $course_name_hindi,
    'course_name_krutidev' => $course_name_krutidev,
    'cgpa' => @$row['cgpa'],
    'cgpa_hindi' => $cgpa_hindi,
    'cgpa_krutidev' => $cgpa_krutidev,
    'topic' => @$row['application_type'],
    'topic_hindi' => @$row['application_type_hindi'],
    'topic_krutidev' => @$row['application_type_krutidev'],
    'student_type' => 0, // default or dynamic
    'gender' => trim(@$row['gender']),
    'first_name' => @$row['first_name'],
    'middle_name' => @$row['middle_name'],
    'last_name' => @$row['last_name'],
    'student_mobile_no' => @$row['student_mobile_no'],
    'permanent_address' => @$row['permanent_address'],
    'local_address' => @$row['local_address'],
    'delivery_address' => @$row['delivery_address'],
    'delivery_pincode' => @$row['pin_code'],
    'certificateid' => @$row['certificateid'],
    'cohort_id' => @$row['cohort_id'],
    'cohort_name' => @$row['cohort_name'],
    'faculty_name' => @$row['faculty_name'],
    'faculty_name_krutidev' => @$row['faculty_name_krutidev'],
    'specialization' => @$row['specialization'],
    'specialization_krutidev' => @$row['specialization_krutidev'],
    'rank' => @$row['rank'],
    'medal_type' => @$row['medal_type'],
    'completion_date' => $completion_date,
    'issue_date' => $issue_date,
    'attire_size' => @$row['attire_size'],
    'no_of_people_accompanied' => @$row['no_of_people_accompanying'],
    'created_at' => Carbon::now(),
];

            }
            // dd($dataToInsert);
          
            $rowIndex++;
        }

        // Perform batch insert
        try {

            $dataToEmail = [];
            $counter = 0;
            // Insert data in chunks if it's too large
            $chunkSize = 500; // Adjust based on your needs
            foreach (array_chunk($dataToInsert, $chunkSize) as $chunk) {
                DB::table('convo_students')->insert($chunk);
                foreach ($chunk as $student) {
                    if($counter <= 10){
                        $dataToEmail[] = $student;
                    }
                    $counter ++;
                }
               
            }
            
            foreach ($dataToEmail as $student) {
                // dd($student['wpu_email_id']);
                try {
                    Mail::to($student['wpu_email_id'])->send(new StudentVerified($student));
                    $prn = $student['prn'];
                    if(!empty( $prn)){
                        ConvoStudent::where('prn', $prn)->update(['email_notification_sent' => '1']); 
                    }
                } catch (\Exception $e) {
                    //    dd($e->getMessage());
                    // Log::error('Failed to queue email for user ' . $user->id . ': ' . $e->getMessage());
                }
            }




            $this->processed_data = count($dataToInsert);

            $this->response = [
                'processed_data' =>  $this->processed_data

            ];
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    private function addFailure($rowIndex, $message, $value, $attribute, $in_excel = 0)
    {
        $this->errors[] = [
            'row' => $rowIndex,
            'errors' => (array)$message,
            'values' => $value,
            'attribute' => $attribute,
            'within_excel' => $in_excel
        ];
    }

    public function rules(): array
    {
        return [
            // '*.prn' => 'required|unique:convo_students,prn',
            // '*.dob' => 'required',
            // '*.student_email_id' => 'required|email',
            // '*.name_as_per_tc' => 'required|string',
            // '*.competency_level' => 'required|string',
            // '*.cgpa' => 'required|numeric|min:0|max:10',
            // '*.gender' => 'string|in:F,M',
        ];
    }

    public function customValidationMessages()
    {
        return [ 
            // '*.dob.date_format' => 'The date of birth must be in the format dd/mm/YYYY.',
            // '*.gender.in' => 'Only "F" or "M" are allowed for the gender column.',
        ];
    }

    // Optional: Implement WithFailures to use the failures() method
    // public function failures()
    // {
    //     return collect($this->errors);
    // }

    public function prepareForValidation($data, $index)
    {
        $data['gender'] =  trim($data['gender']);
        
        return $data;
    }
    public function getResponse()
    {
        return $this->response;
    }

    public function failures()
    {
        return collect($this->errors);
    }

    function excelSerialToDate($serialDate) {
        
        // Define the base date for Excel (January 1, 1900)
        $baseDate = strtotime('1900-01-01');
        
        // Excel serial dates start from 1, so we adjust by subtracting 1
        // Note: Adding 1 to account for the Excel leap year bug (extra day in 1900)
        $daysToAdd = $serialDate - 2; // Subtract 2 to correct for Excel's leap year bug
        
        // Calculate the Unix timestamp for the final date
        $dateTimestamp = strtotime("+{$daysToAdd} days", $baseDate);
        
        // Return the formatted date
        return date('Y-m-d', $dateTimestamp);
    }

    function parseDate($date) {
            try {
                if (is_numeric($date)) {
                    // Handle Excel serial date
                    $formattedDate = $this->excelSerialToDate($date);
                    if (!$formattedDate || !Carbon::createFromFormat('Y-m-d', $formattedDate)) {
                        return false;
                    }
                    return Carbon::createFromFormat('Y-m-d', $formattedDate);
                } else {
                    // Handle standard date formats
                    // First attempt to parse using 'd/m/Y' format
                    $carbonDate = Carbon::createFromFormat('d/m/Y', $date);
                    
                    // Check if the parsed date matches the input format
                    if ($carbonDate->format('d/m/Y') === $date) {
                        return $carbonDate;
                    } else {
                        return false;
                    }
                }
            } catch (\Exception $e) { 
                return false;  
            }
         
    }
    
}
