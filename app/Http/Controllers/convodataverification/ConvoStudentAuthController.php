<?php

namespace App\Http\Controllers\convodataverification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\convodataverification\ConvoStudent;
use Validator;
use App\Models\convodataverification\PasswordResetRequest;
use Illuminate\Support\Facades\Mail;
use  App\Mail\convocation\SendResetPasswordRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ConvoStudentAuthController extends Controller
{
    public function index(Request $request)
    {   
         $domain = $request->getHost();
        $subdomain = explode('.', $domain);

        if($subdomain[0]="mitwpu"){
            return view('convodataverification.student.auth.closed');
        }

        if (Auth::guard('convo_student')->check()) {
            // If authenticated, show the appropriate view
            return redirect()->route('convo_student.dashboard'); // Or the view you want to show
        } else {

            // If not authenticated, redirect to the login page
            return view('convodataverification.student.auth.login');
        }
    }
   
    public function login(Request $request)
    {
        $credentials = $request->only('wpu_email_id', 'password','prn','date_of_birth');

       
 
        // Authenticate the user with custom logic
        $result = $this->authenticate($credentials);

        if ($result['success']) {
            Auth::guard('convo_student')->login($result['student']);
            return response()->json(['success' => true]);
        }
    
        return response()->json(['success' => false, 'message' => $result['message']]);
    }

    public function logout()
    {
        Auth::guard('convo_student')->logout();
        return redirect()->route('convo_student.login'); // Redirect to a named route

    }

    protected function authenticate(array $credentials)
    {
        // Find the student with the given PRN
        $credentials['prn'] = str_replace(' ', '', $credentials['prn']);
        $student = ConvoStudent::where('prn', $credentials['prn'])->first();

        if (!$student) {
            return ['success' => false, 'message' => 'PRN number is incorrect'];
        }
        if ($student->is_printed == 1) { 
            $error[] = 'Students are hereby notified that the registration process for old applicants have been officially close on October 7, 2024, at 6 PM.';
        }
        // Check if the email ID and date of birth match
        if(empty( $error)){
            // Check if the email ID and date of birth match
            if ($student->wpu_email_id !== $credentials['wpu_email_id']) { 
                $error[] = 'Email ID is incorrect';
            }

            if ($student->date_of_birth !== $credentials['date_of_birth']) { 
                $error[] = 'Date of birth is incorrect';
            }

            // Check if the password is correct
            if (!Hash::check($credentials['password'], $student->password)) {
            $error[] = 'Password is incorrect';
            }
        }

        
        // $error[] = "Students are hereby notified that the registration process has been officially closed.";

        if(!empty( $error)){
            return ['success' => false, 'message' =>  $error];
        }
        // Update status if needed
        if ($student->status === "have not yet signed up") {
            $student->status = "have completed 1st time sign up";
            $student->save();
        }

        return ['success' => true, 'student' => $student];
    }
    
    


    public function resetPassword($prnNo)
    {
        $student = ConvoStudent::where('prn', $prnNo)->first(); 

        return view('convodataverification.student.auth.reset_password', [
            'student' => $student
        ]);

    }

    public function resetPasswordUpdate(Request $request)
    {

        $validation_rules = [
            
            'prn' => 'required',
            'password' => ['required', 'min:8', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            'confirm_password' => 'required_with:password|same:password'
          ];
          
        $customMessages = [
            'password.regex' => 'Password format: minimum 8 characters with 1 special character.'
        ];

        $validation = Validator::make($request->all(), $validation_rules,$customMessages);
        if ($validation->fails()) {
            return json_encode([
                'errors' => $validation->errors()->getMessages(),
                'code' => 422,
            ]);
        }
        
        $student = ConvoStudent::where('id', $request['student_id'])
            ->first();

        if($student) {

            $student->password = Hash::make($request->password);

            $student->updated_at = date("Y-m-d H:i:s");
            $student->is_pwd_reset = 1;
            
            $student->save();

            return response()->json(['success' => true, 'message' => 'Reset Password SuccessFully']);
        } else {
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }


    }


    public function resetPasswordWithAllDetails()
    {
       

        return view('convodataverification.student.auth.reset_password_all_details');

    }

 
    public function sendPasswordResetRequestold(Request $request)
    {

         
        $request['prn'] = str_replace(' ', '', $request['prn']);

        $student = ConvoStudent::where('prn', $request['prn'])->first();

        if (!$student) {
            $error['prn'] = 'PRN number is incorrect';
            return ['success' => false, 'errors' => $error];
        }

        // Check if the email ID and date of birth match
        if ($student->wpu_email_id !== $request['wpu_email_id']) { 
            $error['wpu_email_id'] = 'Email ID is incorrect';
        }

        if ($student->date_of_birth !== $request['date_of_birth']) { 
            $error['date_of_birth'] = 'Date of birth is incorrect';
        }

        // Check if the password is correct
        // if (!Hash::check($request['password'], $student->password)) {
        //     $error['password'] = 'Password is incorrect';
        // }

        if(!empty( $error)){
            return ['success' => false, 'errors' =>  $error];
        }else{
            //Send reset link in email 
        }
        if($student) { 

           
            $token = Str::random(64); // Generate a random token
            $requestIp =request()->ip();
            PasswordResetRequest::create([
                'student_id' =>$student->id,
                'token' => $token,
                'request_ip' => $requestIp,
                'is_successful' => false
            ]);
            // dd($token);
            Mail::to($student->wpu_email_id)->send(new SendResetPasswordRequest($student,$token));
            return response()->json(['success' => true, 'message' => 'Reset Password SuccessFully']);
        } else {
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }


    }
    public function sendPasswordResetRequest(Request $request)
    {

        $domain = $request->getHost();
        $subdomain = explode('.', $domain);
        // dd($subdomain[0]);
        $request['prn'] = str_replace(' ', '', $request['prn']);

        $student = ConvoStudent::where('prn', $request['prn'])->first();

        if (!$student) {
            $error['prn'] = 'PRN number is incorrect';
            return ['success' => false, 'errors' => $error];
        }

        // Check if the email ID and date of birth match
        if ($student->wpu_email_id !== $request['wpu_email_id']) { 
            $error['wpu_email_id'] = 'Email ID is incorrect';
        }

        if ($student->date_of_birth !== $request['date_of_birth']) { 
            $error['date_of_birth'] = 'Date of birth is incorrect';
        }

        // Check if the password is correct
        // if (!Hash::check($request['password'], $student->password)) {
        //     $error['password'] = 'Password is incorrect';
        // }

        if(!empty( $error)){
            return ['success' => false, 'errors' =>  $error];
        }else{
            //Send reset link in email 
        }
        if($student) { 

           
            $token = Str::random(64); // Generate a random token
            $requestIp =request()->ip();
            PasswordResetRequest::create([
                'student_id' =>$student->id,
                'token' => $token,
                'request_ip' => $requestIp,
                'is_successful' => false
            ]);
            // dd($token);
            Mail::to($student->wpu_email_id)->send(new SendResetPasswordRequest($student,$token,$subdomain[0]));
            return response()->json(['success' => true, 'message' => 'Reset Password SuccessFully']);
        } else {
            return response()->json(['success' => false, 'message' => 'User Not Found']);
        }


    }

    public function resetPasswordViewWithAllDetails($token)
    {
       
        // dd($token);
        $reset_request = PasswordResetRequest::where('token',$token)->first();
   
        if(!empty($reset_request)){ 
            $reset_request->is_successful = true;
            $reset_request->save(); 
            $student = ConvoStudent::where('id', $reset_request->student_id)->first(); 
            if(empty($student)) {
                return view('convodataverification.student.auth.reset_expiry');
            }
            return view('convodataverification.student.auth.reset_password', [
                'student' => $student
            ]);
        }else{
            return view('convodataverification.student.auth.reset_expiry');
        }
        

    }
    
}
