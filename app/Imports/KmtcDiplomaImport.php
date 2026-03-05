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
use  App\Mail\convocation\StudentVerifiedKmtc;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class KmtcDiplomaImport implements ToCollection, WithHeadingRow, WithValidation
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
        $this->total_data = count($rows);
        logger('row index'. $rowIndex );
        foreach ($rows as $row) {
            // Skip empty rows
            $error_found = false;
            if (empty(@$row['unique_serial_no'])) {
                $rowIndex++;
                // continue;
            }
            
            //    SSSLPASS
            // Check for duplicate 'prn' within the dataToInsert array
            if (in_array(@$row['unique_serial_no'],  $uniquePrns)) {
                $error = array(
                    'Unique Serial No' => @$row['unique_serial_no']
                );
                // echo"<pre>";print_r($error);die;
                $this->addFailure($rowIndex, "Duplicate PRN detected", $error, 'prn', 1);
                // $rowIndex++;
                $error_found = true;
                // continue;
            }


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

            $full_name_hindi = $this->textTranslator->transliterateToHindi(@$row['name']);
            $course_name_hindi = $this->textTranslator->transliterateToHindi(@$row['title']);

            $full_name_krutidev = $this->textTranslator->unicodeToKrutiDev($full_name_hindi);
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
           
            
            $issue_date =NULL;
            // Add row to data array and uniquePrns array
            $uniquePrns[] = @$row['Unique Serial No'];
            if($error_found != true){
                // $specialization_hindi = $textTranslator->transliterateToHindi( @$row['specialization']);
                // $specialization_krutidev = $textTranslator->unicodeToKrutiDev($specialization_hindi);

                // $faculty_name_hindi = $textTranslator->transliterateToHindi(@$row['faculty_name']);
                // $faculty_name_krutidev = $textTranslator->unicodeToKrutiDev($faculty_name_hindi);
                
                $dataToInsert[] = [
                    'prn' => @$row['unique_serial_no'],
                    'date_of_birth' => $formattedDateOfBirth,
                    'wpu_email_id' => @$row['student_email_id'],
                    'full_name' => @$row['name'],
                    'course_name' => @$row['title'],
                    'full_name_hindi' => $full_name_hindi,
                    'course_name_hindi' => $course_name_hindi,
                    'course_name_krutidev' => $course_name_krutidev,
                    'full_name_krutidev' => $full_name_krutidev,
                    'cgpa' =>'1',
                    'password' => Hash::make('SSSLPASS'),
                    'created_at' => Carbon::now(),
                    'cgpa_hindi' => $cgpa_hindi,
                    'cgpa_krutidev' => $cgpa_krutidev,

                    // extra fields
                    'first_name' => @$row['name'],
                    'middle_name' => @$row['name'],
                    'last_name' => @$row['name'],
                     
                    'student_mobile_no' => @$row['student_mobile_no'],
                    'permanent_address' => @$row['permanent_address'],
                    'local_address' => @$row['local_address'],
                    'certificateid' => @$row['certificateid'],
                    'cohort_id' => @$row['cohort_id'],
                    'cohort_name' => @$row['cohort_name'],
                    'faculty_name' => @$row['faculty_name'],
                    'specialization' => @$row['specialization'],
                    'rank' => @$row['rank'],
                    'medal_type' => @$row['medal_type'],
                    'completion_date' => $completion_date,
                    'issue_date' => $issue_date,
                    'secondary_email_id' => @$row['secondary_email_id'],
                    'gender' => trim(@$row['gender']),
                    'day'=>trim(@$row['day']),
                    'month'=>trim(@$row['month']),
                    'year'=>trim(@$row['year']),
                    'diploma'=>trim(@$row['diploma']),
                    'security_line'=>trim(@$row['security_line']),
                    'campus'=>trim(@$row['campus']),
                    // 'specialization_krutidev'=>$specialization_krutidev,
                    // 'faculty_name_krutidev'=>$faculty_name_krutidev,
                ];
            }
            
            $rowIndex++;
        }

        // Perform batch insert
        try {
                // dd($dataToInsert);
            $dataToEmail = [];
            $counter = 0;
            // Insert data in chunks if it's too large
            
            // logger(" row $rowIndex ");
            // logger('Data to insert:', $dataToInsert);
            $chunkSize = 500; // Adjust basn your needs
            foreach (array_chunk($dataToInsert, $chunkSize) as $chunk) {
                DB::table('convo_students')->insert($chunk);
                foreach ($chunk as $student) {
                    if($counter <= 10){
                        $dataToEmail[] = $student;
                    }
                    $counter ++;
                }
               
            }
            // dd($dataToEmail);
            foreach ($dataToEmail as $student) {
                // dd($student['wpu_email_id']);
                try {
                    Mail::to($student['wpu_email_id'])->send(new StudentVerifiedKmtc($student));
                    $prn = $student['prn'];
                    if(!empty( $prn)){
                        ConvoStudent::where('prn', $prn)->update(['email_notification_sent' => '1']); 
                    }
                    Log::info("Email successfully sent to {$student['wpu_email_id']} for PRN {$student['prn']}.");
                } catch (\Exception $e) {
                       dd($e->getMessage());
                    // Log::error('Failed to queue email for user ' . $user->id . ': ' . $e->getMessage());
                }
            }




            $this->processed_data = count($dataToInsert);
                logger("count row:  $this->processed_data. and ".count($dataToInsert));
            $this->response = [
                'processed_data' =>  count($dataToInsert)

            ];
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    private function addFailure($rowIndex, $message, $value, $attribute, $in_excel = 0)
    {
        // dd(5);
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
            '*.unique_serial_no' => 'required|unique:convo_students,prn',
            '*.dob' => 'required',
            '*.student_email_id' => 'required|email',
            '*.name' => 'required|string',
            '*.title' => 'required|string',
            '*.diploma' => 'required|string',
            '*.campus' => 'required|string',
            '*.security_line' => 'required|string',
           // '*.cgpa' => 'required|numeric|min:0|max:10',
          // '*.gender' => 'string|in:F,M',
        ];
    }
    
    

    public function prepareForValidation($data, $index)
        {
            $data['gender'] =  trim($data['gender']);
            
            return $data;
        }

    public function customValidationMessages()
    {
        return [ 
            '*.dob.date_format' => 'The date of birth must be in the format dd/mm/YYYY.',
            '*.gender.in' => 'Only "F" or "M" are allowed for the gender column.',


        ];
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