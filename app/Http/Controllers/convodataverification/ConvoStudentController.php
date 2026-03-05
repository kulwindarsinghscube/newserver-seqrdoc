<?php

namespace App\Http\Controllers\convodataverification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\convodataverification\ConvoAdmin;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use App\Models\convodataverification\ConvoStudent;
use ReflectionClass;
use Yajra\DataTables\DataTables;
use App\Models\convodataverification\StudentAckLog; 
use App\Models\convodataverification\StudentTransaction;
use App\Models\convodataverification\StudentTransactionTemp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use  App\Mail\convocation\StudentVerified;
use Illuminate\Support\Facades\DB;
use QrCode;
use  App\Mail\convocation\PaymentConfirmation;
use  App\Mail\convocation\StudentApprovedPdf;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Admin\TextTranslator;
use App\Models\convodataverification\ConvoStudentLog; 
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\File;
use App\models\StudentTable;

use TCPDF; 
use App\Utility\GibberishAES;
use TCPDF_FONTS; 
use App\Helpers\CoreHelper; 
use Storage;
use DPDF;
use DateTime;
class ConvoStudentController extends Controller
{

    //function for view student dashboard page
    public function index(Request $request)
    {  

        $studentID = @auth()->guard('convo_student')->user()->id;
 
        $student = ConvoStudent::with(['studentAckLogs'])->where('id', $studentID)->first(); 
    
        // Mail::to('dev13@scube.net.in')->send(new StudentApprovedPdf($student));
        if($student->is_pwd_reset == 0) {            
            return redirect()->route('convo_student.reset_password',[$student->prn]);
        }
        
        $payments = StudentTransaction::where('student_id', $studentID)
        // ->where('status','TXN_SUCCESS')
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
                $temporary_transaction_pending = StudentTransactionTemp::where('student_id', $studentID)
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
         
         
        $countries = DB::select('SELECT name FROM countries'); 
        $student_ack_logs = StudentAckLog::where('student_id', $studentID)->orderBy('created_at', 'desc')->get();

        $convo_student_logs = ConvoStudentLog::where('convo_student_id',$studentID)->orderBy('created_at','desc')->get();
        $totalAmount = 0;
     
        if ($student->collection_mode == "By Post") {
           
           
            // Calculate the total amount of successful transactions
            $totalAmount = $payments->where('status', 'TXN_SUCCESS')->sum('txn_amount');
        
          
            if ($totalAmount >= 3000) {
                
            }
        
            // Adjust the total amount based on the fee
            $totalAmount = 3000 - (float)$totalAmount  ;  
        }
        $amount = 0;
        if($student->collection_mode == "By Post"){
            $amount = 1500;  // for other countries Outside India
            if($student->delivery_country == "India"){
                $amount = 750; 
            }
        }
        
        return view('convodataverification.student.pages.index', [
            'student' => $student,
            'payments' => $payments,
            'countries' =>$countries,
            'convo_student_logs' =>$convo_student_logs,
            'student_ack_logs' =>$student_ack_logs,
            'is_transaction_pending'=>$is_transaction_pending,
            'is_transaction_failed' => $is_transaction_failed,
            'total_amount' => $totalAmount,
            'paid_amount'=>$amount
        ]);       
    }

    //function for view student dashboard page
    public function verifyDetails(Request $request){ 
      
        // Initialize paths
        $payment_url = '';
        $fnEnImagePath = $fnHiImagePath = $csEnImagePath = $csHiImagePath = $cgpaImagePath = $studentPhotoPath = null;

         // Define the base directory path within the public directory
        $domain = $request->getHost();
        $subdomain = explode('.', $domain);
      
        if( $subdomain[0] == 'convocation') {
            $subdomain[0] = 'mitwpu';
        }
        $file_data = []; 
        // Define the path to save the file locally
        $baseDirectoryPath = public_path($subdomain[0] . '/' . config('constant.backend') . "/students/");  
        //echo $baseDirectoryPath;
        // Ensure the directory exists
      
        if (!file_exists($baseDirectoryPath)) {
            mkdir($baseDirectoryPath, 0777, true);
        } 
        // Handle file uploads
        if ($request->hasFile('full_name_correction_file') && $request->input('full_name_correct') == 0 ) {
            $file = $request->file('full_name_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $fnEnImagePath = 'images/' . $fileName;
            $file_data['fn_en_image' ] = @$fnEnImagePath ? basename(@$fnEnImagePath) : null;

        }

        if ($request->hasFile('full_name_hindi_correction_file') && $request->input('full_name_hindi_correct') == 0 ) {
            $file = $request->file('full_name_hindi_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $fnHiImagePath = 'images/' . $fileName;

            $file_data['fn_hi_image' ] = @$fnHiImagePath ? basename(@$fnHiImagePath) : null;

        }

        if ($request->hasFile('program_english_correction_file') && $request->input('program_english_correct') == 0 ) {
            $file = $request->file('program_english_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $csEnImagePath = 'images/' . $fileName;
            $file_data['cs_en_image' ] = @$csEnImagePath ? basename(@$csEnImagePath) : null;
        }

        if ($request->hasFile('program_hindi_correction_file') && $request->input('program_hindi_correct') == 0 ) {
            $file = $request->file('program_hindi_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $csHiImagePath = 'images/' . $fileName;
            $file_data['cs_hi_image' ] = @$csHiImagePath ? basename(@$csHiImagePath) : null;
        }

        // if ($request->hasFile('cgpa_correction_file') && $request->input('cgpa_correct') == 0 ) {
        //     $file = $request->file('cgpa_correction_file');
        //     $fileName = time() . '-' . $file->getClientOriginalName();
        //     $file->move($baseDirectoryPath, $fileName);
        //     $cgpaImagePath = 'images/' . $fileName;
        //     $file_data['cgpa_image' ] = @$cgpaImagePath ? basename(@$cgpaImagePath) : null;

        // }

        // Handle file upload for mother_name
        if ($request->hasFile('mother_name_correction_file') && $request->input('mother_name_correct') == 0 ) {
            $file = $request->file('mother_name_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $mnEnImagePath = 'images/' . $fileName;
            $file_data['mn_en_image'] = @$mnEnImagePath ? basename(@$mnEnImagePath) : null;
        }

        // Handle file upload for mother_name_hindi
        if ($request->hasFile('mother_name_hindi_correction_file') && $request->input('mother_name_hindi_correct') == 0 ) {
            $file = $request->file('mother_name_hindi_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $mnHiImagePath = 'images/' . $fileName;
            $file_data['mn_hi_image'] = @$mnHiImagePath ? basename(@$mnHiImagePath) : null;
        }

        // Handle file upload for father_name
        if ($request->hasFile('father_name_correction_file') && $request->input('father_name_correct') == 0 ) {
            $file = $request->file('father_name_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $ftnEnImagePath = 'images/' . $fileName;
            $file_data['ftn_en_image'] = @$ftnEnImagePath ? basename(@$ftnEnImagePath) : null;
        }

        // Handle file upload for father_name_hindi
        if ($request->hasFile('father_name_hindi_correction_file') && $request->input('father_name_hindi_correct') == 0 ) {
            $file = $request->file('father_name_hindi_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $ftnHiImagePath = 'images/' . $fileName;
            $file_data['ftn_hi_image'] = @$ftnHiImagePath ? basename(@$ftnHiImagePath) : null;
        }
        if ($request->hasFile('secondary_email_correction_file') && $request->input('secondary_email_correction_file') == 0 ) {
            $file = $request->file('secondary_email_correction_file');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $esImagePath = 'images/' . $fileName;
            $file_data['se_image'] = @$esImagePath ? basename(@$esImagePath) : null;
        }
        

        if ($request->hasFile('photograph') ) {
            $file = $request->file('photograph');
            $fileName = time() . '-' . $file->getClientOriginalName();
            $file->move($baseDirectoryPath, $fileName);
            $studentPhotoPath = 'images/' . $fileName;
        }



        $studentId = auth()->guard('convo_student')->user()->id; 
        if(!empty($studentId)){
            $is_already = StudentAckLog::where('student_id', $studentId)
            ->where('is_active', 1)
            ->update(['is_active' => 0]); 
        } 
        // Extract the file names from the paths
        $data = [
            'student_id' => $studentId,
            'fn_en_status' => @$request->input('full_name_correct'),
            'fn_en_remark' => @$request->input('full_name_correct') == 0 ? @$request->input('full_name_correction_remarks') : '',
            'fn_hi_status' => @$request->input('full_name_hindi_correct'),
            'fn_hi_remark' =>  @$request->input('full_name_hindi_correct') == 0 ? @$request->input('full_name_hindi_correction_remarks') : '',
            'cs_en_status' => @$request->input('program_english_correct'),
            'cs_en_remark' =>  @$request->input('program_english_correct') == 0 ? @$request->input('program_english_correction_remarks') : '',
            'cs_hi_status' => @$request->input('program_hindi_correct'),
            'cs_hi_remark' => @$request->input('program_hindi_correct') == 0 ? @$request->input('program_hindi_correction_remarks') : '',
            'cgpa_status' => 1,
            // 'cgpa_remark' => @$request->input('cgpa_correct') == 0 ? @$request->input('cgpa_correction_remarks') :'',
            'is_active'=>1,
             // Adding data for mother_name
            'mn_en_status' => @$request->input('mother_name_correct'),
            'mn_en_remark' => @$request->input('mother_name_correct') == 0 ? @$request->input('mother_name_correction_remarks') : '',
            'mn_hi_status' => @$request->input('mother_name_hindi_correct'),
            'mn_hi_remark' => @$request->input('mother_name_hindi_correct') == 0 ? @$request->input('mother_name_hindi_correction_remarks') : '',
            
            // Adding data for father_name
            'ftn_en_status' => @$request->input('father_name_correct'),
            'ftn_en_remark' => @$request->input('father_name_correct') == 0 ? @$request->input('father_name_correction_remarks') : '',
            'ftn_hi_status' => @$request->input('father_name_hindi_correct'),
            'ftn_hi_remark' => @$request->input('father_name_hindi_correct') == 0 ? @$request->input('father_name_hindi_correction_remarks') : '',
            
            'se_remark' => @$request->input('secondary_email_correction_remarks') == 0 ? @$request->input('secondary_email_correction_remarks') : '',
            'se_status' => @$request->input('email_correct'),
            
        ];

        $data = array_merge($file_data, $data); 

        // Initialize an array to hold the update data
        $update_data = [];

        // Fetch the student's current record
        $student = ConvoStudent::where('id', $studentId)->first();

        if ($student) {

            $update_data['mother_name'] = $request->input('mother_name');
        
            $update_data['mother_name_hindi'] = $request->input('mother_name_hindi');
            $update_data['father_name'] = $request->input('father_name');
            $update_data['father_name_hindi'] = $request->input('father_name_hindi');
            $textTranslator = new TextTranslator(); 
            $update_data['father_name_krutidev'] = $textTranslator->unicodeToKrutiDev( $request->input('father_name_hindi'));
            $update_data['mother_name_krutidev'] = $textTranslator->unicodeToAkruti( $request->input('mother_name_hindi'));
            
            $update_data['secondary_email_id'] = $request->input('secondary_email_id');

            $update_data['collection_mode'] = $request->input('collection_mode');
            $update_data['attire_size'] = $request->input('attire_size');
            $update_data['delivery_address'] = $request->input('delivery_address');
            $update_data['delivery_pincode'] = $request->input('delivery_pincode');
            $update_data['delivery_country'] = $request->input('delivery_country');

            $update_data['student_declaration'] = @$request->input('student_declaration');
            $update_data['no_of_people_accompanied']=@$request->input('no_of_people_accompanied');
            if (!empty($studentPhotoPath)) {
                // Safely get the basename of the photo path
                $update_data['student_photo'] = basename($studentPhotoPath);
            }
            // Check if the status needs to be updated
            if ($student->status === 'have completed 1st time sign up') {
                $update_data['status'] = 'student marked few data as incorrect and admin’s action pending';
            }
            //             dd($student->status );
            // dd( $update_data['status']);
            // Perform the update only if there's any data to update
            if (!empty($update_data)) {
                ConvoStudent::where('id', $studentId)->update($update_data);
            }
            
            // dd($student->status);   
            if($data['se_status'] == 1 &&  $data['mn_en_status'] == 1 &&  $data['mn_hi_status'] == 1 &&  $data['ftn_en_status'] == 1 && $data['ftn_hi_status'] == 1 && $data['cs_hi_status'] == 1 && $data['cs_en_status'] == 1 && $data['fn_hi_status'] == 1 && $data['fn_en_status'] == 1 ){
                if ($student->status  === 'have completed 1st time sign up' || $student->status  === '') {
                    $update_data['status'] = 'student acknowledge all data as correct but payment & preview pdf approval is pending';
                 }
                if( $student->status  === 'admin performed correction but student’s re-acknowledgement pending'){
                    $update_data['status'] = 'student re-acknowledged new data as correct but payment & preview pdf approval is pending';
                 }

                
                 //kamini changes 
              if($subdomain[0]=='kmtc'){
             
                $this->generatekmtcPdf($student->prn);
              }
              else{
                if($student->student_type == 1){
                    $this->generatePdfPhd($student->prn);
                 }else{
                    $this->generatePdf($student->prn);
                 }
              }


                

                 $payment_url = route('convo_student.payment');
            }

            // Perform the update only if there's any data to update
            if (!empty($update_data)) {
                ConvoStudent::where('id', $studentId)->update($update_data);
            }
        }

        // Insert data into the database
        StudentAckLog::create($data);  // Replace ModelName with the actual model name
       
        $student = ConvoStudent::select('id','status','prn')->where('id', $studentId)->first();
       
        // Return a response or redirect
        return response()->json(['message' => 'Details verified and saved successfully.','status'=>$student,'payment_url'=>$payment_url]);
        
        
    }

    //function for view payment page
    public function payment(Request $request) 
    {
        $student = @auth()->guard('convo_student')->user(); 
        // if($student->collection_mode == "By Post"){
        //     $amount = 1500;  // for other countries Outside India
        //     if($student->delivery_country == "India"){
        //         $amount = 750; 
        //     }
        // }else{
        //     //Mode of collection of Degree Certificate
        //     $amount = 3000;  
        // }
        $amount = 3000; 
        $student_id = @auth()->guard('convo_student')->user()->id; 
        $orderId = 'MITWPU_OI_'.rand(10000,99999999).'_'.$student_id;  
        $txnDate =Carbon::now();  
        $data = [  
            'txn_amount' => $amount,
            'order_id' => $orderId,
            'status' => "PENDING", 
            "student_id"=> $student_id,
            "txn_date" =>$txnDate,
        ]; 
        // Log::info('PAYMET INTAITED orderId :'.$orderId. " Status :PENDING Student_id :$student_id");
        $create = StudentTransactionTemp::create($data); 
        $prn = @auth()->guard('convo_student')->user()->prn;  
        return view('convodataverification.student.pages.payment',compact('orderId','prn','amount','student')); 
    }


    public function changeCollectionModePayment(Request $request,$size) 
    {
        $student = @auth()->guard('convo_student')->user(); 
        $student_id = @auth()->guard('convo_student')->user()->id; 
        $is_payment_valid = true;
        if($student->collection_mode == "By Post"){
            $amount = 1500;  // for other countries Outside India
            if($student->delivery_country == "India"){
                $amount = 750; 
            }
        
        }else{
            $is_payment_valid = false;
        }
        $payments = StudentTransaction::where('student_id', $student_id)
            ->where('status','TXN_SUCCESS')
            ->orderBy('created_at', 'desc')
            ->get();

        if (!empty($payments)) { 
            $amount = 3000 - (float)$amount; 
        }else{
            $is_payment_valid = false;
        }


       
        if($is_payment_valid){

            $update_data['attire_size'] = $size;
            ConvoStudent::where('id', $student_id)->update($update_data);

            $orderId = 'MITWPU_OI_'.rand(10000,99999999).'_'.$student_id;  
            $txnDate =Carbon::now();  
            $data = [  
                'txn_amount' => $amount,
                'order_id' => $orderId,
                'status' => "PENDING", 
                "student_id"=> $student_id,
                "txn_date" =>$txnDate,
                "changed_collection_mode" => 1
            ]; 
            // dd($data );
            // Log::info('PAYMET INTAITED orderId :'.$orderId. " Status :PENDING Student_id :$student_id");
            $create = StudentTransactionTemp::create($data); 
            $prn = @auth()->guard('convo_student')->user()->prn;  
            return view('convodataverification.student.pages.payment',compact('orderId','prn','amount','student'));  
        }else{
            return redirect()->route('convo_student.dashboard');
        }
         
       
    }

    //function for process response of paytm payment gateway or callback function 
    public function payment_response(Request $request)
    {
        // Retrieve and sanitize input data
        $inputInfo = $request->all();
        $transaction_exist = StudentTransaction::where('txn_id', $request->input('TXNID'))->first();
        if (empty($transaction_exist)) {
            $data = [
                'currency' => $request->input('CURRENCY'),
                'gateway_name' => $request->input('GATEWAYNAME'),
                'response_message' => $request->input('RESPMSG'),
                'bank_name' => $request->input('BANKNAME'),
                'payment_mode' => $request->input('PAYMENTMODE'),
                'mid' => $request->input('MID'),
                'response_code' => $request->input('RESPCODE'),
                'txn_id' => $request->input('TXNID'),
                'txn_amount' => $request->input('TXNAMOUNT'),
                'order_id' => $request->input('ORDERID'),
                'status' => $request->input('STATUS'),
                'bank_txn_id' => $request->input('BANKTXNID'),
                'checksum_hash' => $request->input('CHECKSUMHASH'),
            ];
            $txnDate = Carbon::parse($request->input('TXNDATE'));
            $data['txn_date'] = $txnDate;

            // Obtain student ID, handle cases where user is not authenticated
            $student_id = @auth()->guard('convo_student')->user()->id;
            $data['student_id'] = $student_id;
            $student = ConvoStudent::where('id', $student_id)->first();


            // Check if the order ID already exists
            $orderId = $request->input('ORDERID');
            $status = $request->input('STATUS');
            // Log::info('PAYMET CAlLBACK RECIVE orderId :'.$orderId. " Status :$status Student_id :$student_id");
            // Create a new record


            // update status of temporary student transaction if exist
            $student_transaction_temp = StudentTransactionTemp::where('order_id', $orderId)->first();
            if (!empty($student_transaction_temp)) {
                $student_transaction_temp->status = $status;
                $student_transaction_temp->save();
                $data['changed_collection_mode'] = $student_transaction_temp->changed_collection_mode;
            }

            if ($request->input('STATUS') == 'TXN_SUCCESS' && (!empty($student_transaction_temp) && $student_transaction_temp->changed_collection_mode == 0)) {

                if ($student->status == 'student acknowledge all data as correct but payment & preview pdf approval is pending') {
                    $update_data['status'] = 'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending';
                }
                if ($student->status == 'student re-acknowledged new data as correct but payment & preview pdf approval is pending') {
                    $update_data['status'] = 'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending';
                }
                if (!empty($update_data)) {
                    ConvoStudent::where('id', $student_id)->update($update_data);
                }
            }

            if ($request->input('STATUS') == 'TXN_SUCCESS' && (!empty($student_transaction_temp) && $student_transaction_temp->changed_collection_mode == 1)) {
                $update_data_collection['collection_mode'] = "Attending Convocation";
                ConvoStudent::where('id', $student_transaction_temp->student_id)->update($update_data_collection);

            }


            $create = StudentTransaction::create($data);


            if ($request->input('STATUS') == 'TXN_SUCCESS') {
                try {
                    Mail::to($student->wpu_email_id)->send(new PaymentConfirmation($data, $student));
                    // Optionally, you can log a success message
                    Log::info('Payment confirmation email sent to: ' . $student->wpu_email_id);
                } catch (Exception $e) {
                    Log::error('Failed to send payment confirmation email to: ' . $student->wpu_email_id . '. Error: ' . $e->getMessage());

                }

            }
        }
        return view('convodataverification.student.pages.payment_status', compact('inputInfo'));

    }

    //function for approve pdf 
    public function approvePdfPreview(Request $request)
    {
        
        $student_id = $request->input('student_id'); 
        $update_data['is_pdf_approved'] = 1;
        $student = ConvoStudent::where('id', $student_id)->first();

        if ($student->status == 'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending') {
            $update_data['status'] = 'student acknowledge all data as correct, Payment is completed and preview pdf is approved';
            }
            if( $student->status == 'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending'){
                $update_data['status'] = 'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved';
                }
        if (!empty($update_data)) {
            ConvoStudent::where('id', $student_id)->update($update_data);
            try {
                Mail::to($student->wpu_email_id)->send(new StudentApprovedPdf($student));
                // Optionally, you can log a success message
                Log::info('Registration Completed email sent to: ' . $student->wpu_email_id);
            } catch (Exception $e) { 
                Log::error('Failed to send payment confirmation email to: ' . $student->wpu_email_id . '. Error: ' . $e->getMessage());
               
            }

        }
        return response()->json(['success' => true, 'message' => 'PDF approved successfully.']);
    }

        //kamini changes
        public function createTemp($path){
            //create ghost image folder
            $tmp = date("ymdHis");
           
            $tmpname = tempnam($path, $tmp);
            if (file_exists($tmpname)) {
             unlink($tmpname);
            }
            mkdir($tmpname, 0777);
            return $tmpname;
        }
        public function CreateMessage($tmpDir, $name = "",$font_size,$print_color)
        {
            if($name == "")
                return;
            $name = strtoupper($name);
            // Create character image
            if($font_size == 15 || $font_size == "15"){
    
    
                $AlphaPosArray = array(
                    "A" => array(0, 825),
                    "B" => array(825, 840),
                    "C" => array(1665, 824),
                    "D" => array(2489, 856),
                    "E" => array(3345, 872),
                    "F" => array(4217, 760),
                    "G" => array(4977, 848),
                    "H" => array(5825, 896),
                    "I" => array(6721, 728),
                    "J" => array(7449, 864),
                    "K" => array(8313, 840),
                    "L" => array(9153, 817),
                    "M" => array(9970, 920),
                    "N" => array(10890, 728),
                    "O" => array(11618, 944),
                    "P" => array(12562, 736),
                    "Q" => array(13298, 920),
                    "R" => array(14218, 840),
                    "S" => array(15058, 824),
                    "T" => array(15882, 816),
                    "U" => array(16698, 800),
                    "V" => array(17498, 841),
                    "W" => array(18339, 864),
                    "X" => array(19203, 800),
                    "Y" => array(20003, 824),
                    "Z" => array(20827, 876)
                );
    
                $filename = public_path()."/backend/canvas/ghost_images/F15_H14_W504.png";
    
                $charsImage = imagecreatefrompng($filename);
                $size = getimagesize($filename);
                // Create Backgoround image
                $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
                $bgImage = imagecreatefrompng($filename);
                $currentX = 0;
                $len = strlen($name);
                
                for($i = 0; $i < $len; $i++) {
                    $value = $name[$i];
                    if(!array_key_exists($value, $AlphaPosArray))
                        continue;
                    $X = $AlphaPosArray[$value][0];
                    $W = $AlphaPosArray[$value][1];
                    
                    imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                    $currentX += $W;
                }
    
                $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
                $im = imagecrop($bgImage, $rect);
                
                imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
                imagedestroy($bgImage);
                imagedestroy($charsImage);
                return round((14 * $currentX)/ $size[1]);
    
            }else if($font_size == 12){
    
                $AlphaPosArray = array(
                    "A" => array(0, 849),
                    "B" => array(849, 864),
                    "C" => array(1713, 840),
                    "D" => array(2553, 792),
                    "E" => array(3345, 872),
                    "F" => array(4217, 776),
                    "G" => array(4993, 832),
                    "H" => array(5825, 880),
                    "I" => array(6705, 744),
                    "J" => array(7449, 804),
                    "K" => array(8273, 928),
                    "L" => array(9201, 776),
                    "M" => array(9977, 920),
                    "N" => array(10897, 744),
                    "O" => array(11641, 864),
                    "P" => array(12505, 808),
                    "Q" => array(13313, 804),
                    "R" => array(14117, 904),
                    "S" => array(15021, 832),
                    "T" => array(15853, 816),
                    "U" => array(16669, 824),
                    "V" => array(17493, 800),
                    "W" => array(18293, 909),
                    "X" => array(19202, 800),
                    "Y" => array(20002, 840),
                    "Z" => array(20842, 792)
                
                );
                    
                    $filename = public_path()."/backend/canvas/ghost_images/F12_H8_W288.png";
                $charsImage = imagecreatefrompng($filename);
                $size = getimagesize($filename);
                // Create Backgoround image
                $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
                $bgImage = imagecreatefrompng($filename);
                $currentX = 0;
                $len = strlen($name);
                
                for($i = 0; $i < $len; $i++) {
                    $value = $name[$i];
                    if(!array_key_exists($value, $AlphaPosArray))
                        continue;
                    $X = $AlphaPosArray[$value][0];
                    $W = $AlphaPosArray[$value][1];
                    
                    imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                    $currentX += $W;
                }
    
                $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
                $im = imagecrop($bgImage, $rect);
                
                imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
                imagedestroy($bgImage);
                imagedestroy($charsImage);
                return round((8 * $currentX)/ $size[1]);
    
            }else if($font_size == "10" || $font_size == 10){
                $AlphaPosArray = array(
                    "A" => array(0, 700),
                    "B" => array(700, 757),
                    "C" => array(1457, 704),
                    "D" => array(2161, 712),
                    "E" => array(2873, 672),
                    "F" => array(3545, 664),
                    "G" => array(4209, 752),
                    "H" => array(4961, 744),
                    "I" => array(5705, 616),
                    "J" => array(6321, 736),
                    "K" => array(7057, 784),
                    "L" => array(7841, 673),
                    "M" => array(8514, 752),
                    "N" => array(9266, 640),
                    "O" => array(9906, 760),
                    "P" => array(10666, 664),
                    "Q" => array(11330, 736),
                    "R" => array(12066, 712),
                    "S" => array(12778, 664),
                    "T" => array(13442, 723),
                    "U" => array(14165, 696),
                    "V" => array(14861, 696),
                    "W" => array(15557, 745),
                    "X" => array(16302, 680),
                    "Y" => array(16982, 728),
                    "Z" => array(17710, 680)
                    
                );
                
                $filename = public_path()."/backend/canvas/ghost_images/F10_H5_W180.png";
                $charsImage = imagecreatefrompng($filename);
                $size = getimagesize($filename);
                // Create Backgoround image
                $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
                $bgImage = imagecreatefrompng($filename);
                $currentX = 0;
                $len = strlen($name);
                
                for($i = 0; $i < $len; $i++) {
                    $value = $name[$i];
                    if(!array_key_exists($value, $AlphaPosArray))
                        continue;
                    $X = $AlphaPosArray[$value][0];
                    $W = $AlphaPosArray[$value][1];
                    imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                    $currentX += $W;
                }
                
                $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
                $im = imagecrop($bgImage, $rect);
               
                imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
                imagedestroy($bgImage);
                imagedestroy($charsImage);
                return round((5 * $currentX)/ $size[1]);
    
            }else if($font_size == 11){
    
                $AlphaPosArray = array(
                    "A" => array(0, 833),
                    "B" => array(833, 872),
                    "C" => array(1705, 800),
                    "D" => array(2505, 888),
                    "E" => array(3393, 856),
                    "F" => array(4249, 760),
                    "G" => array(5009, 856),
                    "H" => array(5865, 896),
                    "I" => array(6761, 744),
                    "J" => array(7505, 832),
                    "K" => array(8337, 887),
                    "L" => array(9224, 760),
                    "M" => array(9984, 920),
                    "N" => array(10904, 789),
                    "O" => array(11693, 896),
                    "P" => array(12589, 776),
                    "Q" => array(13365, 904),
                    "R" => array(14269, 784),
                    "S" => array(15053, 872),
                    "T" => array(15925, 776),
                    "U" => array(16701, 832),
                    "V" => array(17533, 824),
                    "W" => array(18357, 872),
                    "X" => array(19229, 806),
                    "Y" => array(20035, 832),
                    "Z" => array(20867, 848)
                
                );
                    
                    $filename = public_path()."/backend/canvas/ghost_images/F11_H7_W250.png";
                $charsImage = imagecreatefrompng($filename);
                $size = getimagesize($filename);
                // Create Backgoround image
                $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
                $bgImage = imagecreatefrompng($filename);
                $currentX = 0;
                $len = strlen($name);
                
                for($i = 0; $i < $len; $i++) {
                    $value = $name[$i];
                    if(!array_key_exists($value, $AlphaPosArray))
                        continue;
                    $X = $AlphaPosArray[$value][0];
                    $W = $AlphaPosArray[$value][1];
                    
                    imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                    $currentX += $W;
                }
    
                $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
                $im = imagecrop($bgImage, $rect);
                
    
                imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
                imagedestroy($bgImage);
                imagedestroy($charsImage);
                return round((7 * $currentX)/ $size[1]);
    
            }else if($font_size == "13" || $font_size == 13){
    
                $AlphaPosArray = array(
                    "A" => array(0, 865),
                    "B" => array(865, 792),
                    "C" => array(1657, 856),
                    "D" => array(2513, 888),
                    "E" => array(3401, 768),
                    "F" => array(4169, 864),
                    "G" => array(5033, 824),
                    "H" => array(5857, 896),
                    "I" => array(6753, 784),
                    "J" => array(7537, 808),
                    "K" => array(8345, 877),
                    "L" => array(9222, 664),
                    "M" => array(9886, 976),
                    "N" => array(10862, 832),
                    "O" => array(11694, 856),
                    "P" => array(12550, 776),
                    "Q" => array(13326, 896),
                    "R" => array(14222, 816),
                    "S" => array(15038, 784),
                    "T" => array(15822, 816),
                    "U" => array(16638, 840),
                    "V" => array(17478, 794),
                    "W" => array(18272, 920),
                    "X" => array(19192, 808),
                    "Y" => array(20000, 880),
                    "Z" => array(20880, 800)
                
                );
    
                $filename ='http://'.$_SERVER['HTTP_HOST']."/backend/canvas/ghost_images/F13_H10_W360.png";
                 $charsImage = imagecreatefrompng($filename);
                $size = getimagesize($filename);
                // Create Backgoround image
                $filename ='http://'.$_SERVER['HTTP_HOST']."/backend/canvas/ghost_images/alpha_GHOST.png";
                $bgImage = imagecreatefrompng($filename);
                $currentX = 0;
                $len = strlen($name);
                
                for($i = 0; $i < $len; $i++) {
                    $value = $name[$i];
                    if(!array_key_exists($value, $AlphaPosArray))
                        continue;
                    $X = $AlphaPosArray[$value][0];
                    $W = $AlphaPosArray[$value][1];
                    
                    imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                    $currentX += $W;
                }
    
                $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
      
                $im = imagecrop($bgImage, $rect);
                
                imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
                imagedestroy($bgImage);
                imagedestroy($charsImage);
                return round((10 * $currentX)/ $size[1]);
    
            }else if($font_size == "14" || $font_size == 14){
    
                $AlphaPosArray = array(
                    "A" => array(0, 833),
                    "B" => array(833, 872),
                    "C" => array(1705, 856),
                    "D" => array(2561, 832),
                    "E" => array(3393, 832),
                    "F" => array(4225, 736),
                    "G" => array(4961, 892),
                    "H" => array(5853, 940),
                    "I" => array(6793, 736),
                    "J" => array(7529, 792),
                    "K" => array(8321, 848),
                    "L" => array(9169, 746),
                    "M" => array(9915, 1024),
                    "N" => array(10939, 744),
                    "O" => array(11683, 864),
                    "P" => array(12547, 792),
                    "Q" => array(13339, 848),
                    "R" => array(14187, 872),
                    "S" => array(15059, 808),
                    "T" => array(15867, 824),
                    "U" => array(16691, 872),
                    "V" => array(17563, 736),
                    "W" => array(18299, 897),
                    "X" => array(19196, 808),
                    "Y" => array(20004, 880),
                    "Z" => array(80884, 808)
                
                );
                    
                    $filename = public_path()."/backend/canvas/ghost_images/F14_H12_W432.png";
                $charsImage = imagecreatefrompng($filename);
                $size = getimagesize($filename);
                // Create Backgoround image
                $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
                $bgImage = imagecreatefrompng($filename);
                $currentX = 0;
                $len = strlen($name);
                
                for($i = 0; $i < $len; $i++) {
                    $value = $name[$i];
                    if(!array_key_exists($value, $AlphaPosArray))
                        continue;
                    $X = $AlphaPosArray[$value][0];
                    $W = $AlphaPosArray[$value][1];
                    
                    imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                    $currentX += $W;
                }
    
                $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
                $im = imagecrop($bgImage, $rect);
                
                imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
                imagedestroy($bgImage);
                imagedestroy($charsImage);
                return round((12 * $currentX)/ $size[1]);
    
            }else{
                $AlphaPosArray = array(
                    "A" => array(0, 944),
                    "B" => array(943, 944),
                    "C" => array(1980, 944),
                    "D" => array(2923, 944),
                    "E" => array(3897, 944),
                    "F" => array(4840, 753),
                    "G" => array(5657, 943),
                    "H" => array(6694, 881),
                    "I" => array(7668, 504),
                    "J" => array(8265, 692),
                    "K" => array(9020, 881),
                    "L" => array(9899, 944),
                    "M" => array(10842, 944),
                    "N" => array(11974, 724),
                    "O" => array(12916, 850),
                    "P" => array(13859, 850),
                    "Q" => array(14802, 880),
                    "R" => array(15776, 944),
                    "S" => array(16719, 880),
                    "T" => array(17599, 880),
                    "U" => array(18479, 880),
                    "V" => array(19485, 880),
                    "W" => array(20396, 1038),
                    "X" => array(21465, 944),
                    "Y" => array(22407, 880),
                    "Z" => array(23287, 880)
                );  
    
                $filename = public_path()."/backend/canvas/ghost_images/ALPHA_GHOST.png";
                $charsImage = imagecreatefrompng($filename);
                $size = getimagesize($filename);
    
                // Create Backgoround image
                $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
                $bgImage = imagecreatefrompng($filename);
                $currentX = 0;
                $len = strlen($name);
                
                for($i = 0; $i < $len; $i++) {
                    $value = $name[$i];
                    if(!array_key_exists($value, $AlphaPosArray))
                        continue;
                    $X = $AlphaPosArray[$value][0];
                    $W = $AlphaPosArray[$value][1];
                    imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                    $currentX += $W;
                }
    
                $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
                $im = imagecrop($bgImage, $rect);
                
                imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
                imagedestroy($bgImage);
                imagedestroy($charsImage);
                return round((10 * $currentX)/ $size[1]);
            }
        }

        public function generatekmtcPdf($prnNo) {
            $domain = \Request::getHost();
            $subdomain = explode('.', $domain);
  
            if($subdomain[0] =='convocation') {
                $subdomain[0] = 'mitwpu';
            }
     
            $ghostImgArr = array();
            $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('TCPDF');
            $pdf->SetTitle('Certificate');
            $pdf->SetSubject('');
        
            // $domain = \Request::getHost();
            // $subdomain = explode('.', $domain);
            
            $ghostImgArr = array();
            $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            // $pdf->SetCreator('TCPDF');
            $pdf->SetAuthor('TCPDF');
            $pdf->SetTitle('Certificate');
            $pdf->SetSubject('');
    
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);
    
    
            // add spot colors
            $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
            $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
    
            $Times_New_Roman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.ttf', 'TrueTypeUnicode', '', 96);
            $timesbi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbi.ttf', 'TrueTypeUnicode', '', 96);
            $timesi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesi.ttf', 'TrueTypeUnicode', '', 96);
         
            // $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
            // $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
            // $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
            // $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);

           
            $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
            $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
            $KRDEV010 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KRDEV010.ttf', 'TrueTypeUnicode', '', 96);
            $KRDEV100 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KrutiDev100Regular.ttf', 'TrueTypeUnicode', '', 96);
        
            $KRDEV100Thin = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100_thin.TTF', 'TrueTypeUnicode', '', 96);
            $KRDEV100R = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100.TTF', 'TrueTypeUnicode', '', 96);
            
            $KRDEV100B = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100_bold.ttf', 'TrueTypeUnicode', '', 96);
            
            $MTCORSVA = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MTCORSVA_5.ttf', 'TrueTypeUnicode', '', 96);
            $LABRIT_N=TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\LABRIT_N.ttf', 'TrueTypeUnicode', '', 96);
            // $AkrutiDevPriya_B = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\AkrutiDevPriya_B.ttf', 'TrueTypeUnicode', '', 96);
            // dd($subdomain);
            $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\KMTC_Diploma_2024_KMTC_APPROVED_DIPLOMA_2024.jpg'; 
            $pdf->AddPage();
    
            $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
            
    
            $result =  ConvoStudent::select('*')
            ->where('prn','=',$prnNo)
            // ->limit(1)
            ->first();
    
            // dd( $result->full_name );
            if(!$result) {
                $pdf->SetFont($arialb, '', 21, '', false);
                $pdf->SetXY(21, 70);
                $pdf->MultiCell(170, 0, 'Data Not Found',1, 'C', 0, 0);
                $pdf->Output('sample.pdf', 'I');   
                exit();
    
            }
            
    
    
            // Watermark
            // Get the page width/height
    
            $myPageWidth = $pdf->getPageWidth();
            $myPageHeight = $pdf->getPageHeight();
    
            // Find the middle of the page and adjust.
            $myX = ( $myPageWidth / 2 ) - 75;
            $myY = ( $myPageHeight / 2 ) + 25;
    
            // Set the transparency of the text to really light
            $pdf->SetAlpha(0.09);
    
            // Rotate 45 degrees and write the watermarking text
            $pdf->StartTransform();
            $pdf->Rotate(45, $myX, $myY);
    
            // echo $myX;
    
            $pdf->SetFont("courier", "", 70);
            // $pdf->Text($myX, $myY,"PREVIEW PDF");
            $pdf->SetXY(15, 180);
            $pdf->MultiCell(180, 0, 'PREVIEW PDF',0, 'C', 0, 0);
            $pdf->StopTransform();
           
            // Reset the transparency to default
            $pdf->SetAlpha(1);

            $pdf->SetFont($arial, '', 6, '', false);
            $pdf->SetTextColor(200, 200, 200);
            $pdf->SetFontStretching(130);
            
            $startX = 15;                 // Starting X position
            $targetWidth = 180;          // Width to fill
            $baseText = trim($result->security_line);
            
            // Build repeated string that fits the target width
            $repeatedText = $baseText;
            while ($pdf->GetStringWidth($repeatedText) < $targetWidth) {
                $repeatedText .= ' ' . $baseText;
            }
            
            // Trim if too long
            while ($pdf->GetStringWidth($repeatedText) > $targetWidth) {
                $repeatedText = mb_substr($repeatedText, 0, -1);
            }
            
            // Y positions for repeating the same line 6 times
            $yPositions = [88, 91, 94, 97, 100, 103];
            
            $pdf->StartTransform();
            foreach ($yPositions as $y) {
                $pdf->SetXY($startX, $y);
                $pdf->Cell(0, 0, $repeatedText, 0, false, 'L'); // Left-aligned, single repeated line
            }
            $pdf->StopTransform();
            
            $pdf->SetFontStretching(100);
            

            
            
            
            // dd($result->full_name);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont($arial, '', 11, '', false);
            $pdf->SetXY(90, 20);
            $pdf->MultiCell(180, 0, htmlspecialchars($result->prn), 0, 'C', 0, 0, '', '', true, 0, true);


            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont($LABRIT_N, '', 32, '', false);
            $pdf->SetXY(0, 90);
            $pdf->MultiCell(0, 0, htmlspecialchars($result->full_name), 0, 'C', 0, 0, '', '', true, 0, true);
            $pdf->SetXY(0, 163);
            $pdf->MultiCell(180, 0, 'in', 0, 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetXY(0, 175);
            $pdf->MultiCell(180, 0, htmlspecialchars($result->course_name), 0, 'C', 0, 0, '', '', true, 0, true);

         



              //micro text
              $microline_str = $result->full_name;
              $microline_str = preg_replace('/\s+/', '', $microline_str);
              $desiredLength = 200; // Set your desired character length here
              $repeated_microline = substr(str_repeat($microline_str, ceil($desiredLength / strlen($microline_str))), 0, $desiredLength);
              
              $pdf->SetFont($ArialB, '', 1.2, '', false);
              $pdf->SetTextColor(0, 0, 0);
              $pdf->StartTransform();
              $pdf->SetXY(40, 268);        
              $pdf->Cell(25, 0, $repeated_microline, 0, false, 'C');

               //micro text
               $microline_str = $result->diploma;
               $microline_str = preg_replace('/\s+/', '', $microline_str);
               $desiredLength = 200; // Set your desired character length here
               $repeated_microline = substr(str_repeat($microline_str, ceil($desiredLength / strlen($microline_str))), 0, $desiredLength);
               
               $pdf->SetFont($ArialB, '', 1.2, '', false);
               $pdf->SetTextColor(0, 0, 0);
               $pdf->StartTransform();
               $pdf->SetXY(142, 268);        
               $pdf->Cell(25, 0, $repeated_microline, 0, false, 'C');
               

            $serial_no=$GUID=$result->prn;
            $dt = date("_ymdHis");
            $str=$GUID.$dt;
            $encryptedString = strtoupper(md5($str));
            $codeContents = $result->prn.", "."\n\n".$encryptedString;
            $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
          
            $qrCodex = 15;
            $qrCodey = 20;
            $qrCodeWidth =25;
            $qrCodeHeight =25;
            $ecc = 'L';
            $pixel_Size = 1;
            $frame_Size = 1;  

            \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
            // \QrCode::size(75.6)
            //     ->backgroundColor(255, 255, 0)
            //     ->format('png')
            //     ->generate($codeContents, $qr_code_path);
            $pdf->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
            $pdf->setPageMark();

            //ghost omage
            $name=$result->full_name;
            $nameOrg= $name;
            $ghost_font_size = '12';
            $ghostImagex = 17;
            $ghostImagey = 280;
            $ghostImageWidth = 39.405983333;
            $ghostImageHeight = 8;
            $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
            $tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
            // dd(  $name);
            $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');         
            $pdf->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
            $pdf->setPageMark();


            // Save the PDF to a file
            $fileName = 'certificate_' . $prnNo . '.pdf'; // Create a unique file name based on the PRN number
    
            // Use DIRECTORY_SEPARATOR for cross-platform compatibility
            $filePath = public_path() . DIRECTORY_SEPARATOR . $subdomain[0] . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'convocation' . DIRECTORY_SEPARATOR . 'certificate' . DIRECTORY_SEPARATOR . $fileName;
            
            // Ensure the directory exists
            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }
            
            $pdf->Output($filePath, 'F'); // Save the PDF file
            
            $result->certificate_pdf = $fileName;
            $result->save();
          
            return $fileName; // Return the file name
        }
    
        //kamini changes end




    //function for creating pdf and store in table
    public function generatePdf($prnNo) {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        if($subdomain[0] =='convocation') {
            $subdomain[0] = 'mitwpu';
        }
    
        $ghostImgArr = array();
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');
    
        // $domain = \Request::getHost();
        // $subdomain = explode('.', $domain);
        
        $ghostImgArr = array();
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        // $pdf->SetCreator('TCPDF');
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

        $Times_New_Roman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $timesbi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbi.ttf', 'TrueTypeUnicode', '', 96);
        $timesi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesi.ttf', 'TrueTypeUnicode', '', 96);

        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $KRDEV010 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KRDEV010.ttf', 'TrueTypeUnicode', '', 96);
        $KRDEV100 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KrutiDev100Regular.ttf', 'TrueTypeUnicode', '', 96);
        $KRDEV100Thin = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100_thin.TTF', 'TrueTypeUnicode', '', 96);
        $KRDEV100R = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100.TTF', 'TrueTypeUnicode', '', 96);
        
        $KRDEV100B = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100_bold.ttf', 'TrueTypeUnicode', '', 96);
        
        $MTCORSVA = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MTCORSVA_5.ttf', 'TrueTypeUnicode', '', 96);
        $AkrutiDevPriya_B = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\AkrutiDevPriya_B.ttf', 'TrueTypeUnicode', '', 96);

        $pdf->AddPage();

        
        

        $result =  ConvoStudent::select('*')
        ->where('prn','=',$prnNo)
        // ->limit(1)
        ->first();

        // print_r($result->prn);
        
        if(!$result) {
            $pdf->SetFont($arialb, '', 21, '', false);
            $pdf->SetXY(21, 70);
            $pdf->MultiCell(170, 0, 'Data Not Found',1, 'C', 0, 0);
            $pdf->Output('sample.pdf', 'I');   
            exit();

        }
        

        // echo "<pre>";
        // print_r($result);
        // die();


        // Watermark
        // Get the page width/height

        $myPageWidth = $pdf->getPageWidth();
        $myPageHeight = $pdf->getPageHeight();

        // Find the middle of the page and adjust.
        $myX = ( $myPageWidth / 2 ) - 75;
        $myY = ( $myPageHeight / 2 ) + 25;

        // Set the transparency of the text to really light
        $pdf->SetAlpha(0.09);

        // Rotate 45 degrees and write the watermarking text
        $pdf->StartTransform();
        $pdf->Rotate(45, $myX, $myY);

        // echo $myX;

        $pdf->SetFont("courier", "", 70);
        // $pdf->Text($myX, $myY,"PREVIEW PDF");
        $pdf->SetXY(15, 180);
        $pdf->MultiCell(180, 0, 'PREVIEW PDF',0, 'C', 0, 0);
        $pdf->StopTransform();
       
        // Reset the transparency to default
        $pdf->SetAlpha(1);



        // $studentName = $result->full_name_krutidev;
        //$pdf->SetTextColor(0,0,0);
        // $pdf->SetFont($arial,'', 10, '', false);
        // $pdf->SetXY(45, 70);
        // $pdf->MultiCell(60, 0, 'For example, a program name such as',1, 'L', 0, 0);
        if($subdomain[0] =='convocation') {
            $subdomain[0] = 'mitwpu';
        }
        $profile_path_org = public_path().'\\'.$subdomain[0].'\backend\students\\'.$result->student_photo;   

        if(file_exists($profile_path_org)) {


            $profilex = 165;
            $profiley = 17;
            $profileWidth = 25;
            $profileHeight = 25;
            
            $pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
            $pdf->setPageMark();

        }

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 51);
        // $pdf->MultiCell(180, 0, "laLFkkid v/;{k rFkk dk;Zdkjh v/;{k] iz'kkldh; vf/kdkjh ,oa ".$result->faculty_name_krutidev." dh flQkfj'k dks vuqeksfnr djrs gq,]", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->MultiCell(180, 0, "laLFkkid v/;{k rFkk dk;Zdkjh v/;{k] iz'kkldh; vf/kdkjh ,oa ".htmlspecialchars($result->faculty_name_krutidev)." dh flQkfj'k dks vuqeksfnr djrs gq,];g çekf.kr djrs gSa fd", 0, 'C', 0, 0, '', '', true, 0, true);

        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($AkrutiDevPriya_B, '', 19.98, '', false);
        $pdf->SetXY(15, 68);
        // $pdf->MultiCell(180, 0, $result->full_name_krutidev.' '.$result->mother_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);

        $pdf->MultiCell(180, 0, htmlspecialchars($result->full_name_krutidev) .'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span> <span style="color:#32329a;font-family:'.$KRDEV100.';font-size:16.02px;">ek¡ dk uke</span> '.htmlspecialchars($result->mother_name_krutidev), 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 78);
        $pdf->MultiCell(180, 0, "dks", 0, 'C', 0, 0, '', '', true, 0, true);



        if(stripos(htmlspecialchars($result->course_name_krutidev), 'fMIyksek') !== FALSE){


            if(stripos($result->specialization_krutidev, 'ix fMIyksek bu') !== FALSE){
                $result->specialization_krutidev = str_replace('ix fMIyksek bu','',$result->specialization_krutidev);
            }

            $pdf->SetTextColor(255,0,0);
            $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
            
            $pdf->SetXY(15, 86);
            if($result->specialization_krutidev) {
                $pdf->MultiCell(180, 0, htmlspecialchars($result->course_name_krutidev).' bu '.htmlspecialchars($result->specialization_krutidev), 0, 'C', 0, 0, '', '', true, 0, true);
            } else {
                $pdf->MultiCell(180, 0, htmlspecialchars($result->course_name_krutidev), 0, 'C', 0, 0, '', '', true, 0, true);
            }

            $pdf->Ln();

            

        } else {
            $pdf->SetTextColor(255,0,0);
            $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
            $pdf->SetXY(15, 86);
            $pdf->MultiCell(180, 0, htmlspecialchars($result->course_name_krutidev), 0, 'C', 0, 0, '', '', true, 0, true);

            $pdf->Ln();
            
        }
        

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);

        $pdf->SetXY(15, $pdf->GetY()+2);

        
        $issue_date_krutidev= '19 vDVwcj „å„†';
        $result->completion_date_krutidev="eÃ @ twu „å„†";
        $date_data = $this->getCompletionDate($result->completion_date,$result->course_name);
         $completion_date = $date_data['completion_date'];
         $completion_date_krutidev = $date_data['completion_krutidev'];
        if(stripos($result->course_name_krutidev, 'fMIyksek') !== FALSE){
            $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".htmlspecialchars($completion_date_krutidev)." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd mUgksausa ".htmlspecialchars($result->cgpa_krutidev)." lhthih, izkIr fd;k gSA bls ekU;rk nsrs gq, vkt ".htmlspecialchars($issue_date_krutidev)." ds volj ij ge vius uke rFkk fo'ofo|ky; dh eksgj ds vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);

        } else {
            if($result->specialization_krutidev) {
                $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".htmlspecialchars($completion_date_krutidev)." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd ".htmlspecialchars($result->specialization_krutidev)." Lis'kykbts'ku esa mUgksausa ".htmlspecialchars($result->cgpa_krutidev)." lhthih, izkIr fd;k gSA bls ekU;rk nsrs gq, vkt ".htmlspecialchars($issue_date_krutidev)." ds volj ij ge vius uke rFkk fo'ofo|ky; dh eksgj ds vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);
            } else {
                $pdf->MultiCell(182, 0, "dh mikf/k iznku dh tkrh gSA ".htmlspecialchars($completion_date_krutidev)." esa gqbZ ijh{kk ds vuqlkj ;g izekf.kr fd;k tkrk gS fd mUgksausa ".htmlspecialchars($result->cgpa_krutidev)." lhthih, izkIr fd;k gSA bls ekU;rk nsrs gq, vkt ".htmlspecialchars($issue_date_krutidev)." ds volj ij ge vius uke rFkk fo'ofo|ky; dh eksgj ds vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);
            }
            
        }




        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 141);
        $pdf->MultiCell(190, 0, "The Founder President and the Executive President approbating the recommendations of the Governing Body and ".$result->faculty_name." hereby confer upon", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '', 19.98, '', false);
        $pdf->SetXY(10, 158);
        $full_name = ucwords(strtolower($result->full_name));
        $mother_name = ucwords(strtolower($result->mother_name));
        // $pdf->MultiCell(190, 0, $full_name.' '.$mother_name, 0, 'C', 0, 0, '', '', true, 0, true);

        $pdf->MultiCell(190, 0, trim($full_name).'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px; ">, Mother\'s Name</span> '.trim($mother_name), 0, 'C', 0, 0, '', '', true, 0, true);

        


        
        if(stripos( strtolower($result->course_name), 'diploma') !== FALSE){


            $pdf->SetTextColor(50,50,154);
            $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
            $pdf->SetXY(10, 167.5);
            $pdf->MultiCell(190, 0, "the", 0, 'C', 0, 0, '', '', true, 0, true);


            if(stripos($result->specialization, 'PG Diploma in') !== FALSE){
                $result->specialization = str_replace('PG Diploma in','',$result->specialization);
            }
            

            $pdf->SetTextColor(255,0,0);
            $pdf->SetFont($timesb, '', 19.98, '', false);
            $pdf->SetXY(10, 174);
            if($result->specialization) {
                $pdf->MultiCell(190, 0, $result->course_name .' in '.$result->specialization , 0, 'C', 0, 0, '', '', true, 0, true);
            } else {
                $pdf->MultiCell(190, 0, $result->course_name , 0, 'C', 0, 0, '', '', true, 0, true);
            }

            $pdf->Ln();
        } else {
            
            $pdf->SetTextColor(50,50,154);
            $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
            $pdf->SetXY(10, 167.5);
            $pdf->MultiCell(190, 0, "the Degree of", 0, 'C', 0, 0, '', '', true, 0, true);

            $pdf->SetTextColor(255,0,0);
            $pdf->SetFont($timesb, '', 19.98, '', false);
            $pdf->SetXY(10, 174);
            $pdf->MultiCell(190, 0, $result->course_name, 0, 'C', 0, 0, '', '', true, 0, true);
            
            $pdf->Ln();
        }



        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, $pdf->GetY()+2);

        // $completion_date_new = 
        //$completion_date_new = date("F Y", strtotime($result->completion_date));

         $completion_date_new = $completion_date;

        

        $specilaText = '';
        if($result->specialization) {
            $specilaText = "in ".$result->specialization." Specialisation";
        }
            

        $issue_formatted_date = '';
        $issue_date= '2024-10-19';
        if($issue_date) {
            $issue_formatted_date = date("jS", strtotime($issue_date))  . " day of " . date("F", strtotime($issue_date)) . " in the year " . date("Y", strtotime($issue_date));
        }

       

        if(stripos($result->course_name, 'Diploma') !== FALSE){

            $pdf->MultiCell(190, 0, " With ".$result->cgpa." CGPA secured 
                    in the examination held in ".$completion_date_new.".
                    <br>In recognition we have hereunder placed our names and
                    <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);

        } else {

            if($result->specialization) {

                $pdf->MultiCell(190, 0, $specilaText." With ".$result->cgpa." CGPA secured 
                    <br>in the examination held in ".$completion_date_new.".
                    <br>In recognition we have hereunder placed our names and
                    <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);

            } else {

                $pdf->MultiCell(190, 0, " With ".$result->cgpa." CGPA secured 
                    in the examination held in ".$completion_date_new.".
                    <br>In recognition we have hereunder placed our names and
                    <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);
            }
            
            // $pdf->MultiCell(190, 0, $specilaText." With ".$cgpa." CGPA secured 
            //         <br>in the examination held in ".$completion_date_new." 
            //         <br>In recognition we have hereunder placed our names and
            //         <br>the seal of the University on this ".$issue_formatted_date.".", 0, 'C', 0, 0, '', '', true, 0, true);

        }



        // $pdf->SetTextColor(255,0,0);
        // $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        // $pdf->SetXY(15, 230);
        // $pdf->MultiCell(180, 0, 'vkbZps uko & '.$result->mother_name_krutidev, 0, 'L', 0, 0, '', '', true, 0, true);


        // $pdf->SetTextColor(255,0,0);
        // $pdf->SetFont($timesb, '',12, '', false);
        // $pdf->SetXY(15, 238);
        // $pdf->MultiCell(100, 0, 'Mother Name - '.$result->mother_name, 0, 'L', 0, 0, '', '', true, 0, true);

        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont($times, '', 11, '', false);
        $pdf->SetXY(10, 255);
        $pdf->MultiCell(190, 0, 'This is a review purpose preview pdf. Please confirm the correctness of the data in this PDF, on the convocation portal.', 0, 'L', 0, 0, '', '', true, 0, true);

        $pdf->SetFont($times, '', 11, '', false);
        $pdf->SetXY(10, 260);
        $pdf->MultiCell(190, 0, 'Do not print it.', 0, 'L', 0, 0, '', '', true, 0, true);
        
        // Save the PDF to a file
        $fileName = 'certificate_' . $prnNo . '.pdf'; // Create a unique file name based on the PRN number

        // Use DIRECTORY_SEPARATOR for cross-platform compatibility
        $filePath = public_path() . DIRECTORY_SEPARATOR . $subdomain[0] . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'convocation' . DIRECTORY_SEPARATOR . 'certificate' . DIRECTORY_SEPARATOR . $fileName;
        
        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        $pdf->Output($filePath, 'F'); // Save the PDF file
        
        $result->certificate_pdf = $fileName;
        $result->save();
        
        return $fileName; // Return the file name
    }


    public function generatePdfPhd($prnNo) {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        if($subdomain[0] =='convocation') {
            $subdomain[0] = 'mitwpu';
        }
    
        $ghostImgArr = array();
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');
    
        // $domain = \Request::getHost();
        // $subdomain = explode('.', $domain);
        
        $ghostImgArr = array();
        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        // $pdf->SetCreator('TCPDF');
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);


        // add spot colors
        $pdf->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdf->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

        $Times_New_Roman = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $timesbi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesbi.ttf', 'TrueTypeUnicode', '', 96);
        $timesi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\timesi.ttf', 'TrueTypeUnicode', '', 96);

        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $KRDEV010 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KRDEV010.ttf', 'TrueTypeUnicode', '', 96);
        $KRDEV100 = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\KrutiDev100Regular.ttf', 'TrueTypeUnicode', '', 96);
        $KRDEV100Thin = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100_thin.TTF', 'TrueTypeUnicode', '', 96);
        $KRDEV100R = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100.TTF', 'TrueTypeUnicode', '', 96);
        
        $KRDEV100B = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Krutidev_100_bold.ttf', 'TrueTypeUnicode', '', 96);
        
        $MTCORSVA = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\MTCORSVA_5.ttf', 'TrueTypeUnicode', '', 96);
        $AkrutiDevPriya_B = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\AkrutiDevPriya_B.ttf', 'TrueTypeUnicode', '', 96);

        $pdf->AddPage();

        
        

        $result =  ConvoStudent::select('*')
        ->where('prn','=',$prnNo)
        // ->limit(1)
        ->first();

        // print_r($result->prn);
        
        if(!$result) {
            $pdf->SetFont($arialb, '', 21, '', false);
            $pdf->SetXY(21, 70);
            $pdf->MultiCell(170, 0, 'Data Not Found',1, 'C', 0, 0);
            $pdf->Output('sample.pdf', 'I');   
            exit();

        }
        


        // Watermark
        // Get the page width/height

        $myPageWidth = $pdf->getPageWidth();
        $myPageHeight = $pdf->getPageHeight();

        // Find the middle of the page and adjust.
        $myX = ( $myPageWidth / 2 ) - 75;
        $myY = ( $myPageHeight / 2 ) + 25;

        // Set the transparency of the text to really light
        $pdf->SetAlpha(0.09);

        // Rotate 45 degrees and write the watermarking text
        $pdf->StartTransform();
        $pdf->Rotate(45, $myX, $myY);

        // echo $myX;

        $pdf->SetFont("courier", "", 70);
        // $pdf->Text($myX, $myY,"PREVIEW PDF");
        $pdf->SetXY(15, 180);
        $pdf->MultiCell(180, 0, 'PREVIEW PDF',0, 'C', 0, 0);
        $pdf->StopTransform();
       
        // Reset the transparency to default
        $pdf->SetAlpha(1);



        // $studentName = $result->full_name_krutidev;
        //$pdf->SetTextColor(0,0,0);
        // $pdf->SetFont($arial,'', 10, '', false);
        // $pdf->SetXY(45, 70);
        // $pdf->MultiCell(60, 0, 'For example, a program name such as',1, 'L', 0, 0);
        if($subdomain[0] =='convocation') {
            $subdomain[0] = 'mitwpu';
        }
        $profile_path_org = public_path().'\\'.$subdomain[0].'\backend\students\\'.$result->student_photo;   

        if(file_exists($profile_path_org)) {


            $profilex = 165;
            $profiley = 17;
            $profileWidth = 25;
            $profileHeight = 25;
            
            $pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
            $pdf->setPageMark();

        }

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 51);
        // $pdf->MultiCell(180, 0, "laLFkkid v/;{k rFkk dk;Zdkjh v/;{k] iz'kkldh; vf/kdkjh ,oa ".$result->faculty_name_krutidev." dh flQkfj'k dks vuqeksfnr djrs gq,]", 0, 'C', 0, 0, '', '', true, 0, true);
        $pdf->MultiCell(180, 0, "laLFkkid v/;{k rFkk dk;Zdkjh v/;{k] iz'kkldh; vf/kdkjh ,oa ".$result->faculty_name_krutidev." dh flQkfj'k dks vuqeksfnr djrs gq,];g çekf.kr djrs gSa fd", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($AkrutiDevPriya_B, '', 19.98, '', false);
        $pdf->SetXY(15, 68);
        // $pdf->MultiCell(180, 0, $result->full_name_krutidev.' '.$result->mother_name_krutidev, 0, 'C', 0, 0, '', '', true, 0, true);

        $pdf->MultiCell(180, 0, htmlspecialchars($result->full_name_krutidev) .'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px;">,</span> <span style="color:#32329a;font-family:'.$KRDEV100.';font-size:16.02px;">ek¡ dk uke</span> '.htmlspecialchars($result->mother_name_krutidev), 0, 'C', 0, 0, '', '', true, 0, true);
        

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 78);
        $pdf->MultiCell(180, 0, "dks", 0, 'C', 0, 0, '', '', true, 0, true);

        
        $pdf->SetTextColor(255,0,0);
        if ($result->course_name== "Doctor of Philosophy (Electronics and Communication Engineering)") {
            $pdf->SetFont($KRDEV100B, '', 19.5, '', false);
        } else {
            $pdf->SetFont($KRDEV100B, '', 19.98, '', false);
        }
       
        $pdf->SetXY(15, 86);
        $pdf->MultiCell(180, 0, htmlspecialchars($result->course_name_krutidev), 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        $pdf->SetXY(15, 97);
        $issue_date_krutidev= '19 vDVwcj „å„†';
        $pdf->MultiCell(182, 0, "mUgksaus ".htmlspecialchars($result->completion_date_krutidev)." esa fofu;eksa ds rgr bl mikf/k dks çkIr djus ds fy, fu/kkZfjr vko';drkvksa
            dks iwjk fd;k gS vkSj lQyrkiwoZd fFkfll 'kh\"kZd ß".htmlspecialchars($result->topic_krutidev)."Þ dk leFkZu fd;k gS
            bls ekU;rk nsrs gq, vkt ".htmlspecialchars($issue_date_krutidev)." ds volj ij ge vius uke rFkk fo'ofo|ky; dh eksgj ds
            vf/kdkjkuqlkj bls laiUu djrs gSaA", 0, 'C', 0, 0, '', '', true, 0, true);



        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 141);
        $pdf->MultiCell(190, 0, "The Founder President and the Executive President approbating the recommendations of the Governing Body and ".$result->faculty_name." hereby confer upon", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '', 19.98, '', false);
        $pdf->SetXY(10, 158);
        $full_name = ucwords(strtolower($result->full_name));
        $mother_name =ucwords(strtolower($result->mother_name));
        // $pdf->MultiCell(190, 0, $full_name.' '.$mother_name, 0, 'C', 0, 0, '', '', true, 0, true);

        $pdf->MultiCell(190, 0, trim($full_name).'<span style="color:#32329a;font-family:'.$MTCORSVA.';font-size:16.02px; ">, Mother\'s Name</span> '.trim($mother_name), 0, 'C', 0, 0, '', '', true, 0, true);

        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 167.5);
        $pdf->MultiCell(190, 0, "the Degree of", 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(255,0,0);
        $pdf->SetFont($timesb, '', 18, '', false);
        $pdf->SetXY(10, 174);
        $pdf->MultiCell(190, 0, $result->course_name, 0, 'C', 0, 0, '', '', true, 0, true);


        $pdf->SetTextColor(50,50,154);
        $pdf->SetFont($MTCORSVA, '', 16.02, '', false);
        $pdf->SetXY(10, 185);

        $completion_date_new = date("F Y", strtotime($result->completion_date));

        $issue_date= '2024-10-19';
        $issue_formatted_date = '';
        if($issue_date) {
            $issue_formatted_date = date("jS", strtotime($issue_date))  . " day of " . date("F", strtotime($issue_date)) . " in the year " . date("Y", strtotime($issue_date));
        }
        $pdf->MultiCell(190, 0, 'has fulfilled the requirements for the degree as prescribed under the regulations and successfully defended the thesis  titled "'.$result->topic.'" in '.$completion_date_new.'.
                    <br>In recognition we have hereunder placed our names and
                    <br>the seal of the University on this '.$issue_formatted_date.'.', 0, 'C', 0, 0, '', '', true, 0, true);


        // $pdf->SetTextColor(255,0,0);
        // $pdf->SetFont($KRDEV100, '', 16.02, '', false);
        // $pdf->SetXY(15, 230);
        // $pdf->MultiCell(180, 0, 'vkbZps uko & '.$result->mother_name_krutidev, 0, 'L', 0, 0, '', '', true, 0, true);


        // $pdf->SetTextColor(255,0,0);
        // $pdf->SetFont($timesb, '',12, '', false);
        // $pdf->SetXY(15, 238);
        // $pdf->MultiCell(100, 0, 'Mother Name - '.$result->mother_name, 0, 'L', 0, 0, '', '', true, 0, true);

        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont($times, '', 11, '', false);
        $pdf->SetXY(10, 255);
        $pdf->MultiCell(190, 0, 'This is a review purpose preview pdf. Please confirm the correctness of the data in this PDF, on the convocation portal.', 0, 'L', 0, 0, '', '', true, 0, true);

        $pdf->SetFont($times, '', 11, '', false);
        $pdf->SetXY(10, 260);
        $pdf->MultiCell(190, 0, 'Do not print it.', 0, 'L', 0, 0, '', '', true, 0, true);
        
        // Save the PDF to a file
        $fileName = 'certificate_' . $prnNo . '.pdf'; // Create a unique file name based on the PRN number

        // Use DIRECTORY_SEPARATOR for cross-platform compatibility
        $filePath = public_path() . DIRECTORY_SEPARATOR . $subdomain[0] . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'convocation' . DIRECTORY_SEPARATOR . 'certificate' . DIRECTORY_SEPARATOR . $fileName;
        
        // Ensure the directory exists
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        $pdf->Output($filePath, 'F'); // Save the PDF file
        
        $result->certificate_pdf = $fileName;
        $result->save();
        
        return $fileName; // Return the file name
    }

    public function convertcgpa($offset,$limit){
      
        $datas = ConvoStudent::
                    //  whereIn('status',[
                    //     // 'student acknowledge all data as correct but payment & preview pdf approval is pending',
                    //     'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending',
                    //     'student acknowledge all data as correct, Payment is completed and preview pdf is approved',
                    //     // 'student re-acknowledged new data as correct but payment & preview pdf approval is pending',
                    //     'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending',
                    //     'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved'
                    // ])
                    where('is_printed',0)
                   
                    // ->where('cgpa_temp','!=','')
                    ->orderBy('id')
                    // ->skip($offset)  
                    // ->take($limit)  
                 
                    ->get();   
                        dd($datas );
        // foreach($datas as $data){ 
        //     $textTranslator = new TextTranslator(); 
        //     $cgpa =number_format($data['cgpa'], 2);
        //     $cgpa_hindi = $textTranslator->transliterateToHindi($cgpa);
        //     $cgpa_krutidev = $textTranslator->unicodeToKrutiDev($cgpa_hindi);
        //     $data->cgpa_temp =  $cgpa;
        //     $data->cgpa_hindi_temp =  $cgpa_hindi;
        //     $data->cgpa_krutidev_temp = $cgpa_krutidev;
        //     $data->save();
        // }
       
    }

    public function updategeneratedPdf(Request $request){ 
        
        // echo "running";
        // die();
        // $offset = $request->offset;
        // $limit = 100;
        // $this->convertcgpa($offset,$limit);
        

        // $response = $this->fetchandcopypastefile($request);
        // dd($response);
        // $students = ConvoStudent::select('id','prn','student_type')->where('is_printed',0)
        //             ->whereIn('status', [
        //                 'student acknowledge all data as correct but payment & preview pdf approval is pending',
        //                 'student re-acknowledged new data as correct but payment & preview pdf approval is pending',
        //              ])->get();
        // dd($students);

        // $this->insertCohortsNo($request);
        // $this->insertSerialNo($request);
        // $this->convertcgpa($offset,$limit);
        // $this->updateTextFormat();
        // $this->process_payment_transactions($request);
        // $offset = $request->offset; // Start at the beginning
        // // dd($offset);
        // $limit = 100; // Number of students to process at a time
        // Log::info('PDF genrated FROM : ' . $offset);
         
        $students = ConvoStudent::select('id','prn','student_type')
                    // ->whereIn('status', [
                    //     'student acknowledge all data as correct but payment & preview pdf approval is pending',
                    //     'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending',
                    //     'student acknowledge all data as correct, Payment is completed and preview pdf is approved',
                    //     'student re-acknowledged new data as correct but payment & preview pdf approval is pending',
                    //     'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending',
                    //     'student re-acknowledged new data as correct, Payment is completed and preview pdf is approved',
                    //  ]) 
                     ->where('prn', '1202220432')
                    //  ->whereIn('wpu_email_id', $emailIds)
                    // ->whereDate('created_at','2024-10-07')
                    // ->where('course_name', 'LIKE', '%APPLICATIONS%')
                    // ->where('student_type',1)
                    // ->where(function($query) {
                    //     $query->where('course_name', 'LIKE', '%BBA LL.B. (Hons.)%')
                    //           ->orWhere('course_name', '=', 'LL.B.')
                    //           ->orWhere('course_name', 'LIKE', '%BBA (Leadership)%');
                    // })
                    ->orderBy('id')
                    // ->skip($offset)  
                    // ->take($limit)  
                    ->get(); 
        dd($students);
        foreach($students as $student){
            if($student->student_type == 1){
                $this->generatePdfPhd($student->prn);  
                Log::info('PDF genrated successfully generatePdfPhd(): ' . $student->prn);
            }else{
                $this->generatePdf($student->prn);  
                Log::info('PDF genrated successfully generatePdf(): ' . $student->prn);
            } 
        }

        // $students = ConvoStudent::where('updated_at', '>=', '2024-09-23 16:00:00')
        // ->where('updated_at', '<=','2024-09-24 12:00:00')
        // ->skip($offset) // Skip the processed students
        // ->take($limit)
        // ->get();
        
        // $textTranslator = new TextTranslator();
        // foreach($students as $student){
        //     Log::info("Started prn : $student->prn id: $student->id");
        //     if(!empty($student->full_name_hindi)){
        //         $full_name_hindi = $student->full_name_hindi;
        //         $full_name_krutidev =  $textTranslator->unicodeToKrutiDev($full_name_hindi);
        //         $student->full_name_krutidev = $full_name_krutidev;
        //         Log::info("Convert full_name_krutidev $student->full_name_krutidev");
        //     }
        //     if(!empty($student->mother_name_hindi)){
        //         $mother_name_hindi = $student->mother_name_hindi;
        //         $mother_name_krutidev =  $textTranslator->unicodeToKrutiDev($mother_name_hindi);
        //         $student->mother_name_krutidev = $mother_name_krutidev;
        //         Log::info("Convert mother_name_krutidev $student->mother_name_krutidev");
        //     }

        //     if(!empty($student->father_name_hindi)){
        //         $father_name_hindi = $student->father_name_hindi;
        //         $father_name_krutidev =  $textTranslator->unicodeToKrutiDev($father_name_hindi);
        //         $student->father_name_krutidev = $father_name_krutidev;
        //         Log::info("Convert father_name_krutidev $student->father_name_krutidev");
        //     }
        //     Log::info("Ended prn : $student->prn id: $student->id");
        //     $student->save();
          
            
           
        // }
       
    }


    public function process_payment_transactions(Request $request) {
        $domain = $request->getHost();
        $subdomain = explode('.', $domain);
        if ($subdomain[0] == 'convocation') {
            $subdomain[0] = 'mitwpu';
        }
    
        // Define the path to save the file locally
        $filePath = public_path($subdomain[0] . '/process_transaction/process_payment_transaction.xlsx');
    
        // Load the spreadsheet
        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $spreadsheet = $objReader->load($filePath);
        } catch (\Exception $e) {
            Log::error("Failed to load the spreadsheet: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load the spreadsheet.'], 500);
        }
    
        // Get the active sheet
        $sheet = $spreadsheet->getActiveSheet();
        $rowCounter = 0;
        $bulkInsertData = [];
    
        // Iterate through each row in the sheet
        foreach ($sheet->getRowIterator() as $row) {
            $rowCounter++;
    
            // Skip the first row (header)
            if ($rowCounter === 1) {
                continue;
            }
    
            // Get each cell in the row
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
    
            // Assuming 'cust_id' is in the first column (index 0)
            $data = [
                'txn_amount' => $rowData[0], // Change the index based on the actual column
                'order_id' => trim($rowData[1], "'"),
                'txn_date' => trim($rowData[2], "'"),
                'student_id' => null // Initialize in case no student is found
            ];
            // dd($data);
            $prn = trim($rowData[3], "'");
   
            if (!empty($prn)) {
                $student = ConvoStudent::where('prn', $prn)->first();
                if (!empty($student)) {
                    $data['student_id'] = $student->id;
                    $transaction = StudentTransaction::where('order_id', $data['order_id'])->first();
    
                    if (empty($transaction)) {
                        $bulkInsertData[] = $data; // Prepare for bulk insert
                    } else {
                        Log::info("Transaction already exists for order_id: " . $data['order_id']." Status :-$transaction->status" );
                    }
                } else {
                    Log::info("No student found for PRN: " . $prn);
                }
            }
        }
    
        // Perform bulk insert if data is available
        if (!empty($bulkInsertData)) {
            try {
                StudentTransactionTemp::insert($bulkInsertData); // Bulk insert
                Log::info("Successfully imported " . count($bulkInsertData) . " transactions.");
            } catch (\Exception $e) {
                Log::error("Bulk insert failed: " . $e->getMessage());
                return response()->json(['error' => 'Failed to insert transactions.'], 500);
            }
        } else {
            Log::info("No new transactions to import.");
        }
    
        return response()->json(['success' => 'Transactions processed successfully.']);
    }
    public function insertSerialNo(Request $request) {
        $domain = $request->getHost();
        $subdomain = explode('.', $domain);
        if ($subdomain[0] == 'convocation') {
            $subdomain[0] = 'mitwpu';
        }
    
        // Define the path to save the file locally
        $filePath = public_path($subdomain[0] . '/serial_no/serial_no.xlsx');
    
        // Load the spreadsheet
        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $spreadsheet = $objReader->load($filePath);
        } catch (\Exception $e) {
            Log::error("Failed to load the spreadsheet: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load the spreadsheet.'], 500);
        }
    
        // Get the active sheet
        $sheet = $spreadsheet->getActiveSheet();
        $rowCounter = 0;
        $bulkInsertData = [];
    
        // Iterate through each row in the sheet
        foreach ($sheet->getRowIterator() as $row) {
            $rowCounter++;
    
            // Skip the first row (header)
            if ($rowCounter === 1) {
                continue;
            }
    
            // Get each cell in the row
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
    
            $serial_no = $rowData[0];
            // dd($data);
            $prn = trim($rowData[1], "'");
   
            if (!empty($prn) && !empty($serial_no)) {
                $student = ConvoStudent::where('prn', $prn)->first();
                if (!empty($student)) {
                    $student->serial_no = $serial_no;
                    $student->save();
                    Log::info("Serial no of student prn: $prn serial_no:$serial_no");
                } else {
                    Log::info("No student found for PRN: $prn serial_no:$serial_no");
                }
            }
        }
    
        
    
        return response()->json(['success' => 'Insert Serial Number .']);
    }

    public function insertCohortsNo(Request $request) {
        $domain = $request->getHost();
        $subdomain = explode('.', $domain);
        if ($subdomain[0] == 'convocation') {
            $subdomain[0] = 'mitwpu';
        }
    
        // Define the path to save the file locally
        $filePath = public_path($subdomain[0] . '/cohorts_no/cohorts_no.xlsx');
    
        // Load the spreadsheet
        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $spreadsheet = $objReader->load($filePath);
        } catch (\Exception $e) {
            Log::error("Failed to load the spreadsheet: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load the spreadsheet.'], 500);
        }
    
        // Get the active sheet
        $sheet = $spreadsheet->getActiveSheet();
        $rowCounter = 0;
        $bulkInsertData = [];
    
        // Iterate through each row in the sheet
        foreach ($sheet->getRowIterator() as $row) {
            $rowCounter++;
    
            // Skip the first row (header)
            if ($rowCounter === 1) {
                continue;
            }
    
            // Get each cell in the row
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
    
            $cohort_id = $rowData[1];
            // dd($data);
            $prn = trim($rowData[0], "'");
   
            if (!empty($prn) && !empty($cohort_id)) {
                $student = ConvoStudent::where('prn', $prn)->first();
                if (!empty($student)) {
                    $student->cohort_id = $cohort_id;
                    $student->save();
                    Log::info("Serial no of student prn: $prn cohort_id:$cohort_id");
                } else {
                    Log::info("No student found for PRN: $prn cohort_id:$cohort_id");
                }
            }
        }
    
        
    
        return response()->json(['success' => 'Insert Serial Number .']);
    }
    


    public function krutidevToAkruti(){
        dd(1);
        $textTranslator = new TextTranslator(); 
        $prn = [1062222565,1032191236,1032212293,1032200247,1032210258,1062220145,1062211057,1032210689,1032201729,1032220145,1212210052,1032200434,1032212065,1202210011,1062210947,1032202083,1222220022,1122200009,1062220292,1192220044,1132221073,1122200117,1032211120,1032212254,1032202145,1182190034,1032211599,1102220079,1202210005,1102200182,1062222674,1062220731,1132210754,1032212141,1132210817,1132220401,1032200887,1032202107,1032220908,1132221024,1032211255,1062221376,1032200847,1102220063,1032210478,1062210408,1062211736,1032201074,1032200933,1062220116,1062222120,1032201870,1032202186,1062210619,1132220676,1132220267,1062210378,1182190046,1032212252,1202210006,1062211753,1032211308,1062210320,1132220584,1032201430,1122200047,1202220357,1202210142,1062211432,1032202045,1132220927,1032200731,1202210129,1032211592,1032210115,1032210136,1032210688,1032210407,1032211781,1032211604,1032210557,1032211583,1032201347,1032212189];
         
        $convoStudents = ConvoStudent::whereIn('prn',$prn)->whereNotNull('cohort_id')->get();
        // dd($convoStudents);
        $i=1;
        foreach($convoStudents as $convoStudent){
            $convoStudent->full_name_krutidev = $textTranslator->unicodeToAkruti($convoStudent->full_name_hindi);
            $convoStudent->mother_name_krutidev = $textTranslator->unicodeToAkruti($convoStudent->mother_name_hindi);
            $convoStudent->save();
            Log::info("$i");
            $i++;
        }
        dd("update COMPLETED SUCCESSFULL $i");
    }
    public function fetchandcopypastefile(Request $request){
        dd(1);
        $files = [1062222565,1032191236,1032212293,1032200247,1032210258,1062220145,1062211057,1032210689,1032201729,1032220145,1212210052,1032200434,1032212065,1202210011,1062210947,1032202083,1222220022,1122200009,1062220292,1192220044,1132221073,1122200117,1032211120,1032212254,1032202145,1182190034,1032211599,1102220079,1202210005,1102200182,1062222674,1062220731,1132210754,1032212141,1132210817,1132220401,1032200887,1032202107,1032220908,1132221024,1032211255,1062221376,1032200847,1102220063,1032210478,1062210408,1062211736,1032201074,1032200933,1062220116,1062222120,1032201870,1032202186,1062210619,1132220676,1132220267,1062210378,1182190046,1032212252,1202210006,1062211753,1032211308,1062210320,1132220584,1032201430,1122200047,1202220357,1202210142,1062211432,1032202045,1132220927,1032200731,1202210129,1032211592,1032210115,1032210136,1032210688,1032210407,1032211781,1032211604,1032210557,1032211583,1032201347,1032212189];
        // Get subdomain dynamically
        $data = StudentTable::whereBetween('id', [230298, 230387])->get()->pluck('certificate_filename');


        // dd($data);
        $domain = $request->getHost();
        $subdomain = explode('.', $domain)[0]; 

        // Define the source and destination directories
        $sourceDirectory = public_path("$subdomain/" . config('constant.backend') . "/pdf_file/");
        $destinationDirectory = public_path("$subdomain/" . config('constant.backend') . "/copied_pdf_files/");

        // Ensure the destination directory exists
        if (!File::exists($destinationDirectory)) {
            File::makeDirectory($destinationDirectory, 0755, true, true);
        }

        $copiedFiles = [];
        $notfound = [];
        foreach ($data as $file) {
            // dd($file);
            $sourceFile = $sourceDirectory . "/" . $file;
            $destinationFile = $destinationDirectory . "/" . $file;

            if (File::exists($sourceFile)) {
                // Copy the file to the new location
                File::copy($sourceFile, $destinationFile);
                $copiedFiles[] = $file;
                // dd($copiedFiles);
            }else{
                $notfound[] = $file;
            }
        }

        return response()->json([
            'message' => count($copiedFiles) . ' files copied successfully!',
            'copied_files' => count($copiedFiles),
            'notfound_files' => count($notfound)
        ]);
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
    // public function updateTextFormat() {
    //     // Fetch distinct course names from the database
    //     $words = ConvoStudent::distinct('course_name')->get();
        
    //     foreach ($words as $word) {
    //         // Capitalize words in the course name
    //         $old_word = $word->course_name; // Store the old word for logging
    //         $new_word = $this->capitalizeWordsWithBrackets($old_word);
            
    //         // Check if the new word is different from the old word
    //         if ($old_word !== $new_word) {
    //             // Update the course name in the database
    //             // ConvoStudent::where('course_name', $old_word)
    //             //             ->update(['course_name' => $new_word]);
                
    //             // Log the old and new words
    //             \Log::info('Course name updated', [
    //                 'old' => $old_word,
    //                 'new' => $new_word,
    //             ]);
    //         }
    //     }


    //     $words = ConvoStudent::distinct('specialization')->get();
        
    //     foreach ($words as $word) {
    //         // Capitalize words in the course name
    //         $old_word = $word->specialization; // Store the old word for logging
    //         $new_word = $this->capitalizeWordsWithBrackets($old_word);
            
    //         // Check if the new word is different from the old word
    //         if ($old_word !== $new_word) {
    //             // Update the course name in the database
    //             // ConvoStudent::where('course_name', $old_word)
    //             //             ->update(['course_name' => $new_word]);
                
    //             // Log the old and new words
    //             \Log::info('Specialization name updated', [
    //                 'old' => $old_word,
    //                 'new' => $new_word,
    //             ]);
    //         }
    //     }
    // }
    
    // public function capitalizeWordsWithBrackets($string) {
    //     // Convert the string to lowercase first, then capitalize each word
    //     $string = ucwords(strtolower($string));

    //     // Define the words you want to skip
    //     $skipWords = ['of', 'and'];

    //     // Use a regular expression to split words, keeping brackets as part of the words
    //     preg_match_all('/\(([^)]+)\)|\S+/', $string, $matches);
    //     $words = $matches[0];

    //     // Loop through the words and check if any should be skipped
    //     foreach ($words as $key => $word) {
    //         // Check if the word is in brackets and capitalize inside the brackets
    //         if (preg_match('/^\(([^)]+)\)$/', $word, $bracketMatch)) {
    //             $innerWord = ucwords(strtolower($bracketMatch[1])); // Capitalize words inside brackets
    //             $words[$key] = '(' . $innerWord . ')';
    //         } elseif (in_array(strtolower($word), $skipWords)) {
    //             // Lowercase the skipped words
    //             $words[$key] = strtolower($word);
    //         } else {
    //             // Capitalize the first letter of the word
    //             $words[$key] = ucwords(strtolower($word));
    //         }
    //     }

    //     // Recombine the words into a single string
    //     return implode(' ', $words);
    // }

} 