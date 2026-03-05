<?php

namespace App\Http\Controllers\convodataverification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\convodataverification\ConvoAdmin;
use App\Imports\StudentsImport;
use App\Imports\PhdStudentsImport; 
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Models\convodataverification\ConvoStudent;
use ReflectionClass;
use Illuminate\Support\Facades\DB;
use  App\Mail\convocation\AdminCorrection;
use App\Models\convodataverification\StudentAckLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

use App\Models\convodataverification\StudentTransaction;
use App\Exports\ConvoStudentExport;
use App\Exports\ConvoCustomStudentExport;
use App\Exports\ConvoCustomRankerStudentExport;
use App\Exports\ConvoCustomMedalistStudentExport;
use App\Http\Controllers\Admin\TextTranslator;
use App\Models\convodataverification\ConvoStudentLog;
use App\Exports\ConvoStudentPaymentsExport;
use App\Http\Controllers\convodataverification\ConvoStudentController;
use App\Models\convodataverification\StudentTransactionTemp;
use ZipArchive;
class ConvoAdminController extends Controller
{
    
    // function for view dashboard 
    public function index(Request $request)
    {   

        // ajax call for datatable
        if ($request->ajax()) {
            // Initialize query components
            $student_type = $request->input('student_type') == "1" ? 1 : 0;
            // Initialize query components
            $where_str = "student_type = ?";

            $where_params = [$student_type];

            // Check and apply the 'prn_filter'
            if (!empty($request->input('prn_filter'))) {
                $prn_filter = $request->input('prn_filter');
                $where_str .= " AND prn LIKE ?";
                $where_params[] = "%{$prn_filter}%";
            }
            if (!empty($request->input('name_filter'))) {
                $name_filter = $request->input('name_filter');
                $where_str .= " AND (full_name LIKE ?)";
                $where_params[] = "%{$name_filter}%";
                $where_params[] = "%{$name_filter}%";
            }

            // Check and apply the 'status_filter'
            if (!empty($request->input('status_filter'))) { 
                $status_filter = $request->input('status_filter');  
                if ($status_filter == 'registration completed') {

                    $registration_completed = [

                        'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved',

                        'student acknowledge all data as correct, Payment is completed and preview pdf is approved'

                    ]; 

                    // Add to the WHERE clause using a WHERE IN

                    $where_str .= " AND status IN (" . implode(',', array_fill(0, count($registration_completed), '?')) . ")";

                    $where_params[] = $registration_completed;

                }else{

                    $where_str .= " AND status LIKE ?";

                    $where_params[] = "%{$status_filter}%";

                } 
            }

            // Check and apply the 'course_filter'
            if (!empty($request->input('course_filter'))) {
                $course_filter = $request->input('course_filter');
                $where_str .= " AND (course_name LIKE ? )";
                $where_params[] = "%{$course_filter}%"; 
            }

            if (!empty($request->input('faculty_name'))) {
                $faculty_name = $request->input('faculty_name');
                $where_str .= " AND (faculty_name LIKE ? )";
                $where_params[] = "%{$faculty_name}%"; 
            }

            if (!empty($request->input('completion_year'))) {
                $completion_year = $request->input('completion_year');
                $where_str .= " AND (Date(completion_date) = ? )";
                $where_params[] = "{$completion_year}"; 
            }

            // Remove leading ' AND ' if present
            $where_str = ltrim($where_str, ' AND ');
            // For pagination
            $query = ConvoStudent::whereRaw($where_str, $where_params);

            
            // Get total record count after applying filters
            $totalRecords = $query->count();

            // Apply pagination
            if ($request->has('start') && $request->has('length')) {
                $query->skip($request->input('start'))
                    ->take($request->input('length'));
            }

            // Apply sorting
            if ($request->has('order')) {
                $sortingCols = $request->input('order');
                $columns = [
                    'id',
                    'prn',
                    'full_name',
                    'wpu_email_id',
                    'date_of_birth',
                    'course_name',
                    'collection_mode',
                    'cgpa',
                    'status',
                    'created_at'
                ];
                if($student_type == 1){
                    $columns[7] = 'topic';
                }
                for ($i = 0; $i < count($sortingCols); $i++) {
                    if ($sortingCols[$i]['column']) {
                        $columnIndex = $sortingCols[$i]['column'];
                        $column = $columns[$columnIndex] ?? null;
                        $direction = $sortingCols[$i]['dir'];
                        if ($column) {
                            $query->orderBy($column, $direction);
                        }
                    }
                }
            }

            // Fetch data
            $data = $query->get();

            // Add row numbers to each record
            $data->transform(function ($item, $key) use ($request) {
                $item->rownum = $key + 1 + $request->input('iDisplayStart'); // Row number calculation 
                return $item;
            });

            // Prepare response
            $response = [
                'iTotalDisplayRecords' => $totalRecords,
                'iTotalRecords' => $totalRecords,
                'sEcho' => intval($request->input('sEcho')),
                'aaData' => $data->toArray(),
            ];

            return response()->json($response);
        }

        
        $other_student_program  = ConvoStudent::distinct('course_name')->select('course_name')->where('student_type',0)->orderBy('course_name')->get();
        $phd_student_program  = ConvoStudent::distinct('course_name')->select('course_name')->where('student_type',1)->orderBy('course_name')->get();
       

        $other_student_specialization  = ConvoStudent::distinct('specialization')->select('specialization')->where('student_type',0)->orderBy('specialization')->get();
        $phd_student_specialization  = ConvoStudent::distinct('specialization')->select('specialization')->where('student_type',1)->orderBy('specialization')->get();
       

        $other_student_faculty  = ConvoStudent::distinct('faculty_name')->select('faculty_name')->where('student_type',0)->orderBy('faculty_name')->get();
        $phd_student_faculty = ConvoStudent::distinct('faculty_name')->select('faculty_name')->where('student_type',1)->orderBy('faculty_name')->get();
       
        $other_student_completion_year  = ConvoStudent::distinct('completion_date')->select(DB::raw("DATE_FORMAT(completion_date, '%M %Y') as completion_date_formated,completion_date"))->where('student_type',0)->orderBy('completion_date')->get();
        $phd_student_completion_year = ConvoStudent::distinct('completion_date')->select(DB::raw("DATE_FORMAT(completion_date, '%M %Y')  as completion_date_formated,completion_date"))->where('student_type',1)->orderBy('completion_date')->get();
       
            
        
        return view('convodataverification.admin.pages.index',compact('other_student_completion_year','phd_student_completion_year', 'other_student_program','phd_student_program','other_student_specialization','phd_student_specialization','other_student_faculty','phd_student_faculty')); // Or the view you want to show

 
    }

    // function for dashboard status count
    public function getStatusCounts()
    {
        $statusLabels = [
            'not_signed_up' => 'have not yet signed up',
            'completed_sign_up' => 'have completed 1st time sign up',
            'data_incorrect_admin_pending' => 'student marked few data as incorrect and admin’s action pending',
            'data_correct_pending_payment' => 'student acknowledge all data as correct but payment & preview pdf approval is pending',
            'data_correct_payment_pending_pdf' => 'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending',
            'data_correct_payment_pdf_approved' => 'student acknowledge all data as correct, Payment is completed and preview pdf is approved',
            'correction_pending_reacknowledgement' => 'admin performed correction but student’s re-acknowledgement pending',
            'reacknowledged_data_correct_payment_pending' => 'student re-acknowledged new data as correct but payment & preview pdf approval is pending',
            'reacknowledged_data_correct_payment_pending_pdf' => 'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending',
            'reacknowledged_data_correct_payment_pdf_approved' => 'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved',
            'total_student' => "Total Students",
            'registration_completed' => "Total Registration Completed",
   
        ];

        $statusShortLabels = [
            'not_signed_up' => 'have not yet signed up',
            'completed_sign_up' => 'have completed 1st time sign up',
            'data_incorrect_admin_pending' => 'student marked a few data as incorrect and admin’s action pending',
            'data_correct_pending_payment' => 'all data is correct but payment is pending(acknowledged)',
            'data_correct_payment_pending_pdf' => 'payment is completed but preview pdf approval is pending(acknowledged)',
            'data_correct_payment_pdf_approved' => 'preview pdf is approved(acknowledged)',
            'correction_pending_reacknowledgement' => 'admin performed correction but student’s re-acknowledgement pending',
            'reacknowledged_data_correct_payment_pending' => 'all data is correct but payment is pending (re-acknowledged)',
            'reacknowledged_data_correct_payment_pending_pdf' => 'payment is completed but  preview pdf approval is pending (re-acknowledged)',
            'reacknowledged_data_correct_payment_pdf_approved' => 'preview pdf is approved (re-acknowledged)',
            'total_student' => "Total Students", 
            'registration_completed' => "Total Registration Completed",
        ];
        $statusCounts = array_map(function ($statusName) {
            if($statusName == 'Total Students'){
                return ConvoStudent::count();
            }elseif($statusName == 'Total Registration Completed'){
                $registration_completed = [
                    'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved',
                    'student acknowledge all data as correct, Payment is completed and preview pdf is approved'
                ];
                return ConvoStudent::whereIn('status', $registration_completed)->count();
            }else{
                
                return ConvoStudent::where('status', $statusName)->count();

            }
        }, $statusLabels);

        $statusData = [];
        foreach ($statusLabels as $key => $label) {
            $statusData[$key] = [
                'label' => $label,
                'short_label' => $statusShortLabels[$key],
                'count' => $statusCounts[$key],
            ];
        }
        $approved = [
            "student re-acknowledged new data as correct, Payment is completed and preview pdf is approved",
            "student acknowledge all data as correct, Payment is completed and preview pdf is approved"
        ];

        $collection_mode['no_of_people_accompanied_registration_completed'] = ConvoStudent::where('collection_mode', 'Attending Convocation')->whereIn('status', $approved) // Adjust 'status' to your actual field name
        ->sum('no_of_people_accompanied'); 

        $collection_mode['no_of_people_accompanied_all'] = ConvoStudent::where('collection_mode', 'Attending Convocation')->whereNotIn('status', $approved) ->sum('no_of_people_accompanied'); // This
        
        $collection_mode['attending_convocation_registration_completed'] = ConvoStudent::where('collection_mode', 'Attending Convocation')
            ->whereIn('status', $approved) // Adjust 'status' to your actual field name
            ->count();

        $collection_mode['attending_convocation_all'] = ConvoStudent::where('collection_mode', 'Attending Convocation')->count();

        $collection_mode['by_post_india_registration_completed'] = ConvoStudent::where('collection_mode', 'By Post')
        ->whereIn('status', $approved)
        ->where('delivery_country',"India")
        ->count();

        $collection_mode['by_post_india_all'] = ConvoStudent::where('collection_mode', 'By Post')
        ->where('delivery_country',"India")
        ->count();

                                                 // Count for By Post, International
        $collection_mode['by_post_international_registration_completed'] = ConvoStudent::where('collection_mode', 'By Post')
        ->whereIn('status', $approved)
        ->where('delivery_country', '!=', "India") // Exclude India to get international students
        ->count();

        $collection_mode['by_post_international_all'] = ConvoStudent::where('collection_mode', 'By Post')
        ->where('delivery_country', '!=', "India") // Exclude India to get international students
        ->count();

        // Categorize status data
        $categories = [
            'other'=>[
                'not_signed_up',
                'completed_sign_up'
                
            ],
            'acknowledgment' => [
                'data_incorrect_admin_pending',
                'data_correct_pending_payment',
                'data_correct_payment_pending_pdf',
                'data_correct_payment_pdf_approved',
                
            ],
            're_acknowledgment' => [
                'correction_pending_reacknowledgement',
                'reacknowledged_data_correct_payment_pending',
                'reacknowledged_data_correct_payment_pending_pdf',
                'reacknowledged_data_correct_payment_pdf_approved',
            ],
            'total'=>[
                'total_student',
                'registration_completed'
            ]
            
        ];

        $categorizedData = [];
        foreach ($categories as $category => $keys) {
            $categorizedData[$category] = array_map(function ($key) use ($statusData) {
                return $statusData[$key];
            }, $keys);
        }

        $response = [
            'statusCounts' => $categorizedData,
            'collectionData' => $collection_mode
        ];

        return response()->json($response);
    }
 
    // function for upload Student form excel 
    public function uploadStudent(Request $request)
    {
        // Validate the request file
        $request->validate([
            'excel_data' => 'required|mimes:xls,xlsx',
        ]); 
        try {
            // Instantiate the import class
            // $student_import = new StudentsImport();

            if($request->input('student_type') == 1){

                $student_import = new PhdStudentsImport();

            }else{

                $student_import = new StudentsImport();

            }

            // Import the data from the file
            Excel::import($student_import, $request->file('excel_data'));
            
            // Check if there are any errors recorded in the import process
            if (!empty($student_import->errors)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Data imported with some errors!',
                    'processed_data' => @$student_import->processed_data,
                    'total_data' => @$student_import->total_data,
                    'errors' => $student_import->errors,
                    'failed' => ((int)@$student_import->total_data - (int)@$student_import->processed_data),

                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'processed_data' => @$student_import->processed_data,
                    'total_data' => @$student_import->total_data,
                    'failed' => ((int)@$student_import->total_data - (int)@$student_import->processed_data),
                    'message' => 'Data Imported and Processed Successfully!'
                ]);
            }
        } catch (\Throwable $e) {
            // Handle any exception that may occur during import
            $errors = [];
            $file = $request->file('excel_data');

            // Determine the file type and create the appropriate reader
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file->getPathname());
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            // Load the file into a Spreadsheet object
            $spreadsheet = $objReader->load($file->getPathname());

            // Get the first worksheet (or specify which worksheet you want)
            $worksheet = $spreadsheet->getActiveSheet();

            // Get the highest row number
            $total_data = $worksheet->getHighestRow();

            $total_data = $total_data - 1; 

            if (method_exists($e, 'failures') && $e->failures()) {
                $reflection = new ReflectionClass($e);
                $methods = $reflection->getProperties();


                $errors = $e->failures();
            } else {
                $errors = [
                    ['message' => $e->getMessage()]
                ];
            }

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
                'errors' => $errors,
                'processed_data' => 0,
                'total_data' => $total_data,
                'failed' => $total_data
            ]); // Use 500 for server errors
        }
    }


    // function for view edit page of student 
    public function edit($id)
    {
        // Fetch the student by ID
        $student = ConvoStudent::with('studentAckLogs')->findOrFail($id);

        // $student_ack_logs = StudentAckLog::where('student_id',$id)->get();
        $student_ack_logs = StudentAckLog::where('student_id', $id)->orderBy('created_at', 'desc')->get();


        $convo_student_logs = ConvoStudentLog::where('convo_student_id',$id)->orderBy('created_at','desc')->get();

        $payments = StudentTransaction::where('student_id', $id)
            // ->where('status', 'TXN_SUCCESS')
            ->orderBy('created_at', 'desc')
            ->get();

            $is_transaction_pending = false; 
            $is_transaction_failed = false;
            // Check if the student's status indicates that payment approval is pending
            $is_status_payment_pending = in_array($student->status, [
                'student acknowledge all data as correct but payment & preview pdf approval is pending', 
                'student re-acknowledged new data as correct but payment & preview pdf approval is pending'
            ]);
    
            // dd($student->status , $is_status_payment_pending);
            // If the student's status indicates pending payment approval
            if ($is_status_payment_pending) { 
                // Count main transactions that are pending
                $main_transaction_pending = $payments->where('status', 'PENDING')->count();
    
                // If there are any main pending transactions, set the flag to true
                if ($main_transaction_pending > 0) {
                    $is_transaction_pending = true; 
                } else {
                    // If no main pending transactions, check the temporary transaction table
                    $temporary_transaction_pending = StudentTransactionTemp::where('student_id', $id)
                        ->where('status', 'PENDING')
                        ->count();
    
                    // If there are any temporary pending transactions, set the flag to true
                    if ($temporary_transaction_pending > 0) {
                        $is_transaction_pending = true; 
                    }
                }
               
                if(!$is_transaction_pending){
                   
                // If there are any main failure without success transactions, set the flag to true
                $main_transaction_success = $payments->where('status', 'TXN_SUCCESS')->count();
                $main_transaction_failed = $payments->where('status', 'TXN_FAILURE')->count();
                if($main_transaction_success == 0 && $main_transaction_failed > 0){
                    $is_transaction_failed = true;
                }
                } 
            } 
           
           
        return view('convodataverification.admin.pages.edit', compact('is_transaction_failed','is_transaction_pending','student', 'payments', 'student_ack_logs','convo_student_logs'));
    }

    // function for update student details 
    public function update(Request $request, $id)
    {
        $student = ConvoStudent::findOrFail($id);
        $textTranslator = new TextTranslator();
        if ($request->has('cgpa')) {
            $cgpa = $request->input('cgpa');
            $cgpa_hindi = $textTranslator->transliterateToHindi($request->input('cgpa'));
            $cgpa_krutidev = $textTranslator->unicodeToKrutiDev($cgpa_hindi);
        }
    
        if ($request->has('topic')) {
            $topic = $request->input('topic');
            $topic_hindi = $textTranslator->transliterateToHindi($request->input('topic'));
            $topic_krutidev = $textTranslator->unicodeToKrutiDev($topic_hindi);
        }
        // $specialization_hindi = $textTranslator->transliterateToHindi($request->input('specialization'));
        // $specialization_krutidev = $textTranslator->unicodeToKrutiDev($specialization_hindi);

        // $faculty_name_hindi = $textTranslator->transliterateToHindi($request->input('faculty_name'));
        // $faculty_name_krutidev = $textTranslator->unicodeToKrutiDev($faculty_name_hindi);

        $previousStudent = ConvoStudent::findOrFail($id);
        $dataRequest = $request->all();
         
        // dd($fatherNameKrutidev);
        $updateData = [
            'prn' => $request->input('prn'),
            'date_of_birth' => $request->input('date_of_birth'),
            'wpu_email_id' => $request->input('wpu_email_id'),
            'full_name' => $request->input('full_name'),
            'full_name_hindi' => $request->input('full_name_hindi'),
            'full_name_krutidev' => $request->input('full_name_krutidev'),
            'course_name' => $request->input('course_name'),
            'course_name_hindi' => $request->input('course_name_hindi'),
            'course_name_krutidev' => $request->input('course_name_krutidev'),
            'mother_name' => $request->input('mother_name'),
            'mother_name_hindi' => $request->input('mother_name_hindi'),
            'mother_name_krutidev' => $request->input('mother_name_krutidev'),
            'father_name' => $request->input('father_name'),
            'father_name_hindi' => $request->input('father_name_hindi'),
            'father_name_krutidev' => $request->input('father_name_krutidev'),
            'gender' => $request->input('gender'),
            'secondary_email_id' => $request->input('secondary_email_id'),
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'last_name' => $request->input('last_name'),
            'student_mobile_no' => $request->input('student_mobile_no'),
            'permanent_address' => $request->input('permanent_address'),
            'local_address' => $request->input('local_address'),
            'certificateid' => $request->input('certificateid'),
            'cohort_id' => $request->input('cohort_id'),
            'cohort_name' => $request->input('cohort_name'),
            'faculty_name' => $request->input('faculty_name'),
            'specialization' => $request->input('specialization'),
            'rank' => $request->input('rank'),
            'medal_type' => $request->input('medal_type'),
            'completion_date' => $request->input('completion_date'),
            'issue_date' => $request->input('issue_date'),
            'no_of_people_accompanied' => $request->input('no_of_people_accompanied'),
        ];
        
        // Conditionally add cgpa fields if they exist
        if ($request->has('cgpa')) {
            $updateData['cgpa'] = $request->input('cgpa');
            $updateData['cgpa_hindi'] = @$cgpa_hindi;
            $updateData['cgpa_krutidev'] = @$cgpa_krutidev;
        }
        
        if ($request->has('topic')) {
            $updateData['topic'] = $request->input('topic');
            $updateData['topic_hindi'] = @$topic_hindi;
            $updateData['topic_krutidev'] = @$topic_krutidev;
        }
        
        $student->update($updateData);
        
        //if new correction msg added then only 
        $correction_message = $request->input('correction_message');
        if(!empty($correction_message)){
            
            $student->update([
                'correction_message' => $request->input('correction_message'),
            ]); 
        }
        
        // Handle the photograph upload if provided
        $domain = $request->getHost();
        $subdomain = explode('.', $domain);
        $studentPhotoPath = '';
        // if ($student->status != 'student acknowledge all data as correct, preview pdf is approved and payment completed'
        //  && $student->status != 'student re-acknowledged new data as correct, preview pdf is approved and payment completed') {

        // }

        if ($student->status == 'student marked few data as incorrect and admin’s action pending') {
            $student->update([
                'status' => 'admin performed correction but student’s re-acknowledgement pending',
            ]);
            Mail::to($student->wpu_email_id)->send(new AdminCorrection($student));
        }

        if ($student->status == 'student marked few data as incorrect and admin’s action pending') {

        }

        // Define the path to save the file locally
        $baseDirectoryPath = public_path($subdomain[0] . '/' . config('constant.backend') . "/students/");
        if ($request->hasFile('photograph')) {
            $file = $request->file('photograph');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $studentPhotoPath = 'images/' . $fileName;
            $student->update(['student_photo' => $fileName]);
        }


        $this->insertLog($previousStudent, $dataRequest);
        
        if (in_array($previousStudent->status, [
            'student acknowledge all data as correct but payment & preview pdf approval is pending',
            'student re-acknowledged new data as correct but payment & preview pdf approval is pending',
            'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending',
            'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending',
            'student acknowledge all data as correct, Payment is completed and preview pdf is approved',
            'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved'
        ])) {
            
             $convoStudentObj = new ConvoStudentController();
            
             if($previousStudent->student_type == 1){
                $convoStudentObj->generatePdfPhd($previousStudent->prn);
             }else{
                $convoStudentObj->generatePdf($previousStudent->prn);
             }
        }
        // $is_already = StudentAckLog::where('student_id', $id)
        // ->where('is_active', 1)
        // ->update(['is_active' => 0]); 
        // Return a response
        return response()->json(['status' => "success", 'message' => 'Student updated successfully!']);
    }

    // function for export students by filter 
    public function exportStudent(Request $request)
    {
        // Retrieve filter parameters from the request
        $filters = $request->only(['prn', 'name', 'status', 'course','student_type','faculty_name','specialization','completion_year']);

        $file_name = $request->student_type == 1 ? "phd_students":"degree_students";
        // Return the Excel download response
        return Excel::download(new ConvoStudentExport($filters), "$file_name.xlsx");
    }



    // function for export students by filter 
    public function exportCustomStudent(Request $request)
    {
        // Retrieve filter parameters from the request
        $filters = $request->only(['prn', 'name', 'status', 'course','student_type','faculty_name','specialization','completion_year']);

        $file_name = $request->student_type == 1 ? "phd_students":"degree_students";
        $type = 'MEDALIST';
        if( $type == 'RANKERS'){
            return Excel::download(new ConvoCustomRankerStudentExport($filters), "custom_$type-$file_name.xlsx");
        }elseif($type == 'MEDALIST'){
            return Excel::download(new ConvoCustomMedalistStudentExport($filters), "custom_$type-$file_name.xlsx"); 
        }else{
            return Excel::download(new ConvoCustomStudentExport($filters), "custom_$file_name.xlsx");

        }
        
        // Return the Excel download response
    }

    public function exportCustomStudent1(Request $request)
    {
        // Retrieve filter parameters from the request
        $filters = $request->only(['prn', 'name', 'status', 'course', 'student_type']);
        $file_name = $request->student_type == 1 ? "phd_students" : "degree_students";

        // Define the path for the temporary files
        $tempDir = storage_path('app/temp/');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true); // Create the temp directory if it doesn't exist
        }

        // Define the path for the zip file in the public directory
        $zipFileName = public_path('custom_students.zip');

        // Create an instance of ZipArchive to zip the files
        $zip = new ZipArchive();

        // Open the zip file for creation
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return response()->json(['message' => 'Could not create zip file'], 500);
        }

        // Get the faculty groups
        $facultyGroups = ConvoStudent::whereNotNull('faculty_name') 
        ->whereIn('status', [
            'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending',
            'student acknowledge all data as correct, Payment is completed and preview pdf is approved', 
            'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending',
            'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved'
        ])
        ->groupBy('faculty_name')
        ->select('faculty_name')
        ->get();
        // ->whereNull('cohort_id')
             
        foreach ($facultyGroups as $facultyGroup) {
            $filters['faculty_name'] = $facultyGroup['faculty_name'];
            $file_name_new =  str_replace(' ', "_", $facultyGroup['faculty_name']) . ".xlsx";
            $excelFilePath = $tempDir . $file_name_new; // Full path for the Excel file

            // Generate the Excel file and save it to the temp directory
            try {

                $type = 'Degree';
                if( $type == 'RANKERS'){ 
                    Excel::store(new ConvoCustomRankerStudentExport($filters), "temp/$file_name_new", 'local');
                }elseif($type == 'MEDALIST'){ 
                    Excel::store(new ConvoCustomMedalistStudentExport($filters), "temp/$file_name_new", 'local');
                }else{ 
                    Excel::store(new ConvoCustomStudentExport($filters), "temp/$file_name_new", 'local');
                }

                // Store in the local disk under temp
            } catch (\Exception $e) {
                return response()->json(['message' => "Failed to create file: $file_name_new. Error: " . $e->getMessage()], 500);
            }

            // Check if the Excel file was created
            if (file_exists($excelFilePath)) {
                // Add the file to the zip archive
                $zip->addFile($excelFilePath, $file_name_new);
            } else {
                // Handle the case where the Excel file could not be created
                return response()->json(['message' => "Failed to create file: $file_name_new"], 500);
            }
        }

        // Close the zip archive
        $zip->close();

        // Check if the zip file was created
        if (!file_exists($zipFileName)) {
            return response()->json(['message' => 'Zip file could not be created'], 500);
        }

        // Return the zip file as a response
        $response = response()->download($zipFileName)->deleteFileAfterSend(true);

        // Delete the Excel files from the temp directory after sending the response
        foreach ($facultyGroups as $facultyGroup) {
            $filters['faculty_name'] = $facultyGroup['faculty_name'];
            $file_name_new = $file_name . "-" . str_replace(' ', "_", $facultyGroup['faculty_name']) . ".xlsx";
            $excelFilePath = $tempDir . $file_name_new;

            // Delete the Excel file if it exists
            if (file_exists($excelFilePath)) {
                unlink($excelFilePath); // Delete the file
            }
        }

        // Optionally delete the zip file if you don't want to keep it
        // unlink($zipFileName);

        return $response;
    }




    // function for insert log on admin change student details 
    // public function insertLog($previousStudentData, $dataRequest)
    // {

    //     if (is_object($dataRequest)) {
    //         // Convert object to array
    //         $dataRequest = (array) $dataRequest;
    //     }

    //     $prn = '';
    //     if ($previousStudentData->prn != $dataRequest['prn']) {
    //         $prn = $dataRequest['prn'];
    //     }

    //     $wpu_email_id = '';
    //     if ($previousStudentData->wpu_email_id != $dataRequest['wpu_email_id']) {
    //         $wpu_email_id = $dataRequest['wpu_email_id'];
    //     }

    //     $secondary_email_id = '';
    //     if ($previousStudentData->secondary_email_id != $dataRequest['secondary_email_id']) {
    //         $secondary_email_id = $dataRequest['secondary_email_id'];
    //     }


    //     $gender = '';
    //     if ($previousStudentData->gender != $dataRequest['gender']) {
    //         $gender = $dataRequest['gender'];
    //     }
       
    //     $date_of_birth = '';
    //     if ($previousStudentData->date_of_birth != $dataRequest['date_of_birth']) {
    //         $date_of_birth = $dataRequest['date_of_birth'];
    //     }

    //     $cgpa = '';
    //     if ($previousStudentData->cgpa != $dataRequest['cgpa']) {
    //         $cgpa = $dataRequest['cgpa'];
    //     }

    //     $full_name = '';
    //     if ($previousStudentData->full_name != $dataRequest['full_name']) {
    //         $full_name = $dataRequest['full_name'];
    //     }

    //     $full_name_hindi = '';
    //     if ($previousStudentData->full_name_hindi != $dataRequest['full_name_hindi']) {
    //         $full_name_hindi = $dataRequest['full_name_hindi'];
    //     }

    //     $full_name_krutidev = '';
    //     if ($previousStudentData->full_name_krutidev != $dataRequest['full_name_krutidev']) {
    //         $full_name_krutidev = $dataRequest['full_name_krutidev'];
    //     }

    //     $mother_name = '';
    //     if ($previousStudentData->mother_name != $dataRequest['mother_name']) {
    //         $mother_name = $dataRequest['mother_name'];
    //     }

    //     $mother_name_hindi = '';
    //     if ($previousStudentData->mother_name_hindi != $dataRequest['mother_name_hindi']) {
    //         $mother_name_hindi = $dataRequest['mother_name_hindi'];
    //     }

    //     $mother_name_krutidev = '';
    //     if ($previousStudentData->mother_name_krutidev != $dataRequest['mother_name_krutidev']) {
    //         $mother_name_krutidev = $dataRequest['mother_name_krutidev'];
    //     }

    //     $father_name = '';
    //     if ($previousStudentData->father_name != $dataRequest['father_name']) {
    //         $father_name = $dataRequest['father_name'];
    //     }

    //     $father_name_hindi = '';
    //     if ($previousStudentData->father_name_hindi != $dataRequest['father_name_hindi']) {
    //         $father_name_hindi = $dataRequest['father_name_hindi'];
    //     }

    //     $father_name_krutidev = '';
    //     if ($previousStudentData->father_name_krutidev != $dataRequest['father_name_krutidev']) {
    //         $father_name_krutidev = $dataRequest['father_name_krutidev'];
    //     }


    //     $course_name = '';
    //     if ($previousStudentData->course_name != $dataRequest['course_name']) {
    //         $course_name = $dataRequest['course_name'];
    //     }

    //     $course_name_hindi = '';
    //     if ($previousStudentData->course_name_hindi != $dataRequest['course_name_hindi']) {
    //         $course_name_hindi = $dataRequest['course_name_hindi'];
    //     }

    //     $course_name_krutidev = '';
    //     if ($previousStudentData->course_name_krutidev != $dataRequest['course_name_krutidev']) {
    //         $course_name_krutidev = $dataRequest['course_name_krutidev'];
    //     }

    //     $updatedValues = [];
    //     $fieldsToCheck = [
    //         'first_name', 'middle_name', 'last_name', 'student_mobile_no', 
    //         'permanent_address', 'local_address', 'certificateid', 
    //         'cohort_id', 'cohort_name', 'faculty_name', 'specialization', 
    //         'rank', 'medal_type', 'completion_date', 'issue_date'
    //     ];
    //     $additionalFields = [ 
    //         'first_name', 'middle_name', 'last_name', 'student_mobile_no', 
    //         'permanent_address', 'local_address', 'certificateid', 
    //         'cohort_id', 'cohort_name', 'faculty_name', 'specialization', 
    //         'rank', 'medal_type', 'completion_date', 'issue_date'
    //     ];


    //     // // Iterate through additional fields and update values
    //     foreach ($additionalFields as $field) {
    //         if (isset($previousStudentData->$field) && isset($dataRequest[$field]) && $previousStudentData->$field != $dataRequest[$field]) {
    //             $updatedValues[$field] = $dataRequest[$field];

    //         }
    //     }

    //     // // Check if any of the fields to be logged are non-empty
    //     $hasNonEmptyFields = false; 
    //     foreach ($fieldsToCheck as $field) {
    //         if (!empty($dataRequest[$field])) {
    //             $hasNonEmptyFields = true;
    //             break;
    //         }
    //     }
    //     // dd($hasNonEmptyFields);
    //     // || !empty($updatedValues
    //     if ($prn != '' || $wpu_email_id != '' || $secondary_email_id != '' || $gender != '' || $date_of_birth != '' || $cgpa != '' || $full_name != '' || $full_name_hindi != '' || $full_name_krutidev != '' || $mother_name != '' || $mother_name_hindi != '' || $mother_name_krutidev != '' || $father_name != '' || $father_name_hindi != '' || $father_name_krutidev != '' || $course_name != '' || $course_name_hindi != '' || $course_name_krutidev || !empty($updatedValues)) {

    //         $ConvoStudentLog =  new ConvoStudentLog();
    //         $ConvoStudentLog->convo_student_id = $previousStudentData->id;
    //         $ConvoStudentLog->prn = $prn;
    //         $ConvoStudentLog->wpu_email_id = $wpu_email_id;
    //         $ConvoStudentLog->secondary_email_id = $secondary_email_id;
    //         $ConvoStudentLog->gender = $gender;
    //         $ConvoStudentLog->date_of_birth = $date_of_birth;
    //         $ConvoStudentLog->cgpa = $cgpa;
    //         $ConvoStudentLog->full_name = $full_name;
    //         $ConvoStudentLog->full_name_hindi = $full_name_hindi;
    //         $ConvoStudentLog->full_name_krutidev = $full_name_krutidev;
    //         $ConvoStudentLog->mother_name = $mother_name;
    //         $ConvoStudentLog->mother_name_hindi = $mother_name_hindi;
    //         $ConvoStudentLog->mother_name_krutidev = $mother_name_krutidev;
    //         $ConvoStudentLog->father_name = $father_name;
    //         $ConvoStudentLog->father_name_hindi = $father_name_hindi;
    //         $ConvoStudentLog->father_name_krutidev = $father_name_krutidev;
    //         $ConvoStudentLog->course_name = $course_name;
    //         $ConvoStudentLog->course_name_hindi = $course_name_hindi;
    //         $ConvoStudentLog->course_name_krutidev = $course_name_krutidev;
    //         $ConvoStudentLog->log_date = date('Y-m-d H:i:s');
    //         $ConvoStudentLog->method = 'POST';
    //         $ConvoStudentLog->user_type = 'admin';
    //         $ConvoStudentLog->user_id = \Auth::guard('admin')->user()->id;
    //         foreach ($updatedValues as $field => $value) {
    //             $ConvoStudentLog->$field = $value;
    //         }
    //         $ConvoStudentLog->save();
    //     }

    //     return true;
    // }
    public function insertLog($previousStudentData, $dataRequest)
    {
        if (is_object($dataRequest)) {
            // Convert object to array
            $dataRequest = (array) $dataRequest;
        }

        // Define the fields that need to be checked
        $fields = [
            'prn', 'wpu_email_id', 'secondary_email_id', 'gender', 'date_of_birth', 
            'full_name', 'full_name_hindi', 'full_name_krutidev', 'mother_name', 'mother_name_hindi', 
            'mother_name_krutidev', 'father_name', 'father_name_hindi', 'father_name_krutidev', 
            'course_name', 'course_name_hindi', 'course_name_krutidev'
        ];

        // Initialize an array to store updated values
        $updatedValues = [];

        // Iterate through each field to check for changes
        foreach ($fields as $field) {
            if (isset($previousStudentData->$field) && isset($dataRequest[$field]) && $previousStudentData->$field != $dataRequest[$field]) {
                $updatedValues[$field] = $dataRequest[$field];
            }
        }

        // Add additional fields for logging
        $additionalFields = [
            'first_name', 'middle_name', 'last_name', 'student_mobile_no', 'permanent_address', 
            'local_address', 'certificateid', 'cohort_id', 'cohort_name', 'faculty_name', 
            'specialization', 'rank', 'medal_type', 'completion_date', 'issue_date'
        ];

        foreach ($additionalFields as $field) {
            if (isset($previousStudentData->$field) && isset($dataRequest[$field]) && $previousStudentData->$field != $dataRequest[$field]) {
                $updatedValues[$field] = $dataRequest[$field];
            }
        }

        if(!empty($dataRequest['correction_message'])){
            $updatedValues['correction_message'] = $dataRequest['correction_message'];
        }

        // Check if any field is non-empty
        // $hasNonEmptyFields = false;
        // foreach ($fields as $field) {
        //     if (!empty($dataRequest[$field])) {
        //         $hasNonEmptyFields = true;
        //         break;
        //     }
        // }
        // dd($updatedValues);
        // Check if there are any updated values 
        if (!empty($updatedValues)) {
            $ConvoStudentLog = new ConvoStudentLog();
            $ConvoStudentLog->convo_student_id = $previousStudentData->id;
            // foreach ($fields as $field) {
            //     $ConvoStudentLog->$field = $dataRequest[$field] ?? '';
            // }
            $ConvoStudentLog->log_date = date('Y-m-d H:i:s');
            $ConvoStudentLog->method = 'POST';
            $ConvoStudentLog->user_type = 'admin';
            $ConvoStudentLog->user_id = \Auth::guard('admin')->user()->id;

            // Add additional updated fields
            foreach ($updatedValues as $field => $value) {
                $ConvoStudentLog->$field = $value;
            }
            
            $ConvoStudentLog->save();

           
            

        }

        return true;
    }

    // function for export students payments
    public function exportStudentTransaction(Request $request)
    { 
      $currentDateTime = date('dmyHis'); // Format: ddmmyyyyhhmm
      
      $filters = $request->only(['prn', 'name', 'status', 'course','student_type','faculty_name','specialization','completion_year']);
      
      $file_name = $request->student_type == 1 ? "phd_students":"degree_students";

      // Construct the file name
      $file_name = "Convocation_Payment_$file_name"."_". $currentDateTime;
      
      return Excel::download(new ConvoStudentPaymentsExport($filters), "$file_name.xlsx");
    }

     public function quillpad_Api(Request $request){
        $url = config('constant.quillpad').'processWordJSON';
        $params = [
            'inString' => $request->query('inString',''),
            'lang' => $request->query('lang', '')
        ];

        // Append parameters to the URL
        $url .= '?' . http_build_query($params);
        // dd();
        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);
        // dd(json_decode($response)->twords[0]->options[0]);
        // Check for errors
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            // Print the response
            $options = (!empty(json_decode($response)->twords)) ? json_decode($response)->twords[0]->options : null;
            //dd($options); // Debugging output

            $tds = '';
            if (!empty($options) && is_array($options)) {
                foreach ($options as $key => $value) {
                    $tds .= '<table><tr><td><input name="'.$request->query('inString','').'" value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'" type="radio" style="margin-right: 10px;" class="'.$request->query('inString','').'_'.$key.'">'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'</td></tr></table>';
                }
            }

            return $tds;

        }

        // Close cURL session
        curl_close($ch); 
    }

}