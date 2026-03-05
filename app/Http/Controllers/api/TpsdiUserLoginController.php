<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use carbon\Carbon;
use TCPDF;
use App\Utility\ApiSecurityLayer;

use App\models\FunctionalUsers;
use App\models\FunctionalUserLoginHistory;
class TpsdiUserLoginController extends Controller
{
    public function __construct()
    {
        //$this->middleware('tpsdiauth');

    }
	public function pass(Request $request)
   {
    // echo Hash::make('9689');
     // $user = Users::where('email', $request->input('email'))->first();
     // dd(Hash::check('Admin@123','$2y$10$p2jEk3Hkg8Vgbcy9hvyOa.JyPL3dMMWWNGx9eYx1MB5IXq67UsWh.'));
     // dd(Hash::check('123456a','$2y$10$.XB30GO4jn7bx7EauLrWkugIaCNGxiQCgrFTeFDeSSrGdQYd6Rneq'));
    return response()->json(['status' => 'success','sample_pass' =>  Hash::make('Admin@123')],200);
   }
   public function CheckPIN(Request $request)
   {
        if($request->get('mobile')){
        $this->validate($request, [
        'mobile' => ['required','digits:10','numeric']
        ],
        [
        'mobile.required' => 'Mobile number required!',
        'mobile.digits' => 'Please enter valid 10 digit number!',
        'mobile.numeric' => 'Please enter valid number!'
        ]);
        $pin_set=FunctionalUsers::select('PIN','Email_ID','Mobile_Number','Student_Name')->where('Mobile_Number',$request->get('mobile'))->first();
        }else if($request->get('email')){
        $this->validate($request, [
        'email' => ['required','email']
        ],
        [
        'email.required' => 'Email ID is required!',
        'email.email' => 'Please enter valid email ID!'
        ]);
        $pin_set=FunctionalUsers::select('PIN','Email_ID','Mobile_Number','Student_Name')->where('Email_ID',$request->get('email'))->first();
        }else{
            return response()->json(['status' => 401,'message' => 'Invalid request'],401);
        }
        
        if (is_null($pin_set)) {
             return response()->json(['status' => 401,'message' => 'No data found! Please check your input'],200);
        } else if(empty($pin_set->PIN)){
          return response()->json(['status' => 200,'data' => array('is_set' => false,'result'=>$pin_set)],200);  
        }else{
            return response()->json(['status' => 200,'data' => array('is_set' => true,'result'=>$pin_set)],200);
        }
        
    }
    public function sendotp(Request $request)
   {
       if($request->get('mobile')){
        $this->validate($request, [
        'mobile' => ['required','digits:10','numeric']
        ],
        [
        'mobile.required' => 'Mobile number required!',
        'mobile.digits' => 'Please enter valid 10 digit number!',
        'mobile.numeric' => 'Please enter valid number!'
        ]);
        $where=['Mobile_Number' => $request->input('mobile')];
        }else if($request->get('email')){
        $this->validate($request, [
        'email' => ['required','email']
        ],
        [
        'email.required' => 'Email ID is required!',
        'email.email' => 'Please enter valid email ID!'
        ]);
        $where=['Email_ID' => $request->input('email')];
        }else{
            return response()->json(['status' => 401,'message' => 'Invalid request'],401);
        }
        // if($request->input('mobile')!='9689279541'){
        $otp=mt_rand(1111,9999);
        $text="Your OTP for SeQR App Verification is ". $otp ."- Team SSSL";
        $user=FunctionalUsers::where($where)->update(['OTP'=>$otp]);
        // dd($user->get()->toArray());
        if ($user==0) {
             return response()->json(['status' => 401,'message' => 'No records found! Please check your input'],401);
        } else if ($user > 0){
            if(!empty($request->get('mobile'))){
                // dd($this->sendSMS( $request->input('mobile'),$text));
                $this->sendSMS( $request->input('mobile'),$text); 
             
             return response()->json(['status' => 200,'message' => 'OTP sent on your number','data'=>['mobile'=>$request->input('mobile')]],200);
             // return response()->json(['status' => 200,'message' => 'OTP sent on your number','data'=>['otp'=>$otp,'mobile'=>$request->input('mobile')]],200);
            }
            if(!empty($request->get('email'))){
              $user_email=[$request->get('email')]; 
              $cc_email=[]; 
              $mail_subject='TPSDI OTP Request';
              $mail_view='mail.tpsdi_otp';
              $data=['otp'=>$otp];
            $this->sendEmail($user_email,$cc_email,$mail_subject,$mail_view,$data);
            return response()->json(['status' => 200,'message' => 'OTP sent on your email ID','data'=>['otp'=>$otp,'mobile'=>$request->input('email')]],200);
            }
        }
    
   }
   public function verifyOtp(Request $request)
   {
    if($request->get('mobile')){
       $this->validate($request, [
       'mobile' => 'required',
       'otp' => 'required'
        ],
    [
        'mobile.required' => 'Mobile number required!',
        'otp.required' => 'Please enter OTP required!'
    ]);
       $where=['Mobile_Number' => $request->input('mobile')];
   }
   if($request->get('email')){
       $this->validate($request, [
       'email' => 'required',
       'otp' => 'required'
        ],
    [
        'email.required' => 'Email ID required!',
        'otp.required' => 'Please enter OTP required!'
    ]);
    $where=['Email_ID' => $request->input('email')];
   }
      $user = FunctionalUsers::where($where)->where('OTP',$request->input('otp'))->first();

     if(!empty($user)){
          return response()->json(['status' => 200,'message' => 'success','verifyOtp' => true,'data' => $this->get_user_details($user->id)],200);
      }else if(empty($user)){
          return response()->json(['status' => 401,'message' => 'No records found! Please check your input'],401);
      }else{
          return response()->json(['status' => 200,'message' => 'fail','verifyOtp' => false,'data' => null],200);
      }
   }
   public function setPIN(Request $request)
   {
        $this->validate($request, [
       'pin' => ['required','digits:4','numeric']
        ],
    [
        'pin.required' => 'Mobile number required!',
        'pin.digits' => 'Please enter valid 10 digit number!',
        'pin.numeric' => 'Please enter valid number!'
    ]);
        
        $user=FunctionalUsers::where('id', $request->input('id'))->update(['PIN'=>$request->input('pin')]);
        if ($user==0) {
             return response()->json(['status' => 200,'success' => false,'message' => 'Already used pin please try different pin'],200);
        } else if ($user > 0){
        // if($user){
          
           return response()->json(['status' => 200,'success' => true,'message' => 'Your PIN is Set successfully'],200);
        }
   }
   public function authenticate(Request $request)
   {
    if($request->get('pin')){
       $this->validate($request, [
       'pin' => ['required','digits:4','numeric']
        ],
    [
        'pin.required' => 'Mobile number required!',
        'pin.digits' => 'Please enter valid 4 digit number!',
        'pin.numeric' => 'Please enter valid number!'
    ]);
   }
      $contact = $request->input('user_name');
      $user = FunctionalUsers::where('PIN', $request->input('pin'))
      ->where(function($query) use ($contact) {
        $query->where('Mobile_Number', $contact)
              ->orWhere('Email_ID', $contact);
    })->first();
      // dd($user->id);
     if(!empty($user)){
          $apikey = base64_encode(Str::random(40));
          FunctionalUsers::where('id',$user->id)->update(['api_key' => $apikey]);
          $userId = $user->id;

            // Check if there's an existing active session (i.e., no logout_time)
            $existingSession = FunctionalUserLoginHistory::where('functional_user_id', $userId)
                ->whereNull('logout_time') // No logout time means active session
                ->latest()
                ->first();

            // If an active session exists, update its logout time
            if ($existingSession) {
                $existingSession->update([
                    'logout_time' => Carbon::now(), // Set the logout time
                ]);
            }

            // Now, create a new login history record for the user
            FunctionalUserLoginHistory::create([
                'functional_user_id' => $userId,
                'login_time' => Carbon::now(), // Current login timestamp
            ]);

        return response()->json(['status' => 200,'success'=>true,'message' => 'success','api_key' => $apikey,'data' => $this->get_user_details($user->id)],200);
    }else{
        return response()->json(['status' => 200,'success'=>false,'message' => 'Invalid credentials'],200);
    }
}

   public function logout($id)
   {
    // dd(session('key'));
      $user=FunctionalUsers::where('id', $id)->update(['api_key' =>null]);
      $loginHistory = FunctionalUserLoginHistory::where('functional_user_id', $id)
            ->whereNull('logout_time') // Ensure it updates the last session
            ->latest()
            ->first(); // Fetch the latest record with a NULL logout_time

        // Check if the record exists
        if ($loginHistory) {
            // If a session exists, update the logout time
            $loginHistory->update([
                'logout_time' => Carbon::now(),
            ]);
        }

      if($user){
          return response()->json(['status' => 200,'message' => 'User logout success'],200);
      }else{
          return response()->json(['status' => 401,'message' => 'fail'],401);
      }
   } 
   public function get_user_details($id)
   {
      $user = DB::table('functional_users')->where('id', $id)->get()->first();
      $user->user_type='functional';
      // dd($user);
      
// $user = $record ? $record->toArray() : [];
      if($user){
          return $user;//response()->json(['status' => 300,'message' => 'success','data'=>$user],200);
      }else{
          return [];//response()->json(['status' => 401,'message' => 'fail'],401);
      }
   }

   
   
   
   public function expireotp(Request $request)
   {
         if($request->get('mobile')){
       $this->validate($request, [
       'mobile' => 'required'
        ],
    [
        'mobile.required' => 'Mobile number required!'
    ]);
    $where=['Mobile_Number' => $request->input('mobile')];
   }
   if($request->get('email')){
       $this->validate($request, [
       'email' => 'required'
        ],
    [
        'email.required' => 'Email ID required!'
    ]);
    $where=['Email_ID' => $request->input('email')];
   }
        
        $user=Users::where($where)->update(['otp'=>null]);
     
        if($user){
          
           return response()->json(['status' => 200,'message' => 'Your OTP is expired'],200);
        }
        return response()->json(['status' => 401,'message' => 'failed'],401);
   }
   
   public function sendSMS($mobile_no, $text) {
        $apiKey = "A25dd681d149b289d3a4ce30b4ba67917";
        $message = urlencode($text);
        $sid="HXAP1649996084IN";
        $sender_id="SEQRDC";
        $type = "OTP";
        $template_id="1007161113135607610";
        $url = "https://api.kaleyra.io/v1/".$sid."/messages?type=".$type."&sender=".$sender_id."&to=+91".$mobile_no."&template_id=".$template_id."&body=".$message;
         // Set the headers
        $headers = array(
            "Content-Type: application/json",
            "api-key: ".$apiKey
        );
        if (strpos($text, 'OTP') !== false && (strlen($mobile_no) === 10)){ 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        $data = (json_decode($result, true));
        // dd($data);
        if ($data['status'] == "OK") {
            return 1;
        } else {
            return 0;
        }
        }
    }
   
    public function sendEmail($user_email,$cc_email,$mail_subject,$mail_view,$data) {
        $result=Mail::send($mail_view,$data, function ($message) use ($user_email, $mail_subject, $cc_email) {
                $message->to($user_email);
                $message->cc($cc_email);
                $message->subject($mail_subject);
            });
            if($result){
                return 1;
            } else {
                return 0;
            }
    }    
    public function listCertificates(Request $request)
   {

        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);
        $offset = $request->input('offset', 0); // default 0
        $baseQuery = DB::table('student_table')
                ->select('*')
                ->join('certificate_data_mapping', 'student_table.id', '=', 'certificate_data_mapping.student_tbl_id')
                ->where('student_table.status','=','1')
                ->where('student_table.functional_user_id',$request->input('fuser_id'));//->toSql();
               // Clone query for count
    $total = $baseQuery->count();
    $baseQuery->orderBy('student_table.created_at', 'desc');
    // Clone again for paginated data
    $user = (clone $baseQuery)->offset($offset)->limit(10)->get()->toArray();
        // 
        foreach ($user as $key => $value) {
        $value->certificate_filepath=$request->getScheme().'://' . $subdomain[0].'.'.$subdomain[1].'.com/' .$subdomain[0].'/backend/pdf_file/' . $value->certificate_filename;
        }
        // dd($user);
        if($user){
          return response()->json(['status' => 200,'message' => 'success','data'=>$user,'total_records' => $total],200);
        }else{
          return response()->json(['status' => 200,'message' => 'fail','data'=>[],'total_records' => 0],200);
        }
   }
    public function listCards(Request $request)
   {

        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);
        $offset = $request->input('offset', 0); // default 0
        $baseQuery = DB::table('student_table')
                ->select('*')
                ->join('card_data_mapping', 'student_table.id', '=', 'card_data_mapping.student_tbl_id')
                ->where('student_table.status','=','1')
                ->where('student_table.functional_user_id',$request->input('fuser_id'));//->toSql();
               // Clone query for count
    $total = $baseQuery->count();
    $baseQuery->orderBy('student_table.created_at', 'desc');
    // Clone again for paginated data
    $user = (clone $baseQuery)->offset($offset)->limit(10)->get()->toArray();
        // 
        foreach ($user as $key => $value) {
        $value->certificate_filepath=$request->getScheme().'://' . $subdomain[0].'.'.$subdomain[1].'.com/' .$subdomain[0].'/backend/pdf_file/' . $value->certificate_filename;
        }
        // dd($user);
        if($user){
          return response()->json(['status' => 200,'message' => 'success','data'=>$user,'total_records' => $total],200);
        }else{
          return response()->json(['success' => false,'status' => 200,'message' => 'No data found','data'=>[],'total_records' => 0],200);
        }
   }
   public function searchCards(Request $request)
    {
        if (ApiSecurityLayer::checkAccessTokenInstitute($request->user_id)) 
    {
        $offset = $request->input('offset', 0); // default 0
        $searchInput = $request->input('search_input');
        $filterType = $request->input('filter_type');

        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);

        $baseQuery = DB::table('student_table')
            ->select('*')
            ->join('card_data_mapping', 'student_table.id', '=', 'card_data_mapping.student_tbl_id')
            ->where('student_table.status', '=', '1');
        $baseQuery->orderBy('student_table.created_at', 'desc');
        // Add dynamic like condition
        if ($filterType == 1) {
            $baseQuery->where('Mobile_Number', 'like', '%' . $searchInput . '%');
        } elseif ($filterType == 2) {
            $baseQuery->where('Email_ID', 'like', '%' . $searchInput . '%');
        } elseif ($filterType == 3) {
            $baseQuery->where('batch_no', 'like', '%' . $searchInput . '%');
        }

        // Get total count
        $total = $baseQuery->count();

        // Get paginated results
        $user = (clone $baseQuery)->offset($offset)->limit(10)->get()->toArray();

        foreach ($user as $key => $value) {
            $value->certificate_filepath = $request->getScheme() . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/' . $subdomain[0] . '/backend/pdf_file/' . $value->certificate_filename;
        }

        if ($user) {
            return response()->json(['status' => 200, 'message' => 'success', 'data' => $user, 'total_records' => $total], 200);
        } else {
            return response()->json(['success' => false,'status' => 200, 'message' => 'No data found', 'data' => [], 'total_records' => 0], 200);
        }
        }
        else
        {
             return response()->json(array('success' => false,'status'=>403, 'message' => 'Access forbidden.'),403);
        }
    }

    public function searchCertificates(Request $request)
    {
        if (ApiSecurityLayer::checkAccessTokenInstitute($request->user_id)) 
    {
        $offset = $request->input('offset', 0); // default 0
        $searchInput = $request->input('search_input');
        $filterType = $request->input('filter_type');

        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);

        $baseQuery = DB::table('student_table')
            ->select('*')
            ->join('certificate_data_mapping', 'student_table.id', '=', 'certificate_data_mapping.student_tbl_id')
            ->where('student_table.status', '=', '1');
        $baseQuery->orderBy('student_table.created_at', 'desc');
        // Add dynamic like condition
        if ($filterType == 1) {
            $baseQuery->where('Mobile_Number', 'like', '%' . $searchInput . '%');
        } elseif ($filterType == 2) {
            $baseQuery->where('Email_ID', 'like', '%' . $searchInput . '%');
        }
        //  elseif ($filterType == 3) {
        //     $baseQuery->where('batch_no', 'like', '%' . $searchInput . '%');
        // }

        // Get total count
        $total = $baseQuery->count();

        // Get paginated results
        $user = (clone $baseQuery)->offset($offset)->limit(10)->get()->toArray();

        foreach ($user as $key => $value) {
            $value->certificate_filepath = $request->getScheme() . '://' . $subdomain[0] . '.' . $subdomain[1] . '.com/' . $subdomain[0] . '/backend/pdf_file/' . $value->certificate_filename;
        }

        if ($user) {
            return response()->json(['status' => 200, 'message' => 'success', 'data' => $user, 'total_records' => $total], 200);
        } else {
            return response()->json(['success' => false,'status' => 200, 'message' => 'No data found', 'data' => [], 'total_records' => 0], 200);
        }
        }
        else
        {
             return response()->json(array('success' => false,'status'=>403, 'message' => 'Access forbidden.'),403);
        }
    }

   public function expiringCards(Request $request)
   {
    if (ApiSecurityLayer::checkAccessTokenInstitute($request->user_id)) 
    {
        $hostUrl = $request->getHttpHost();
        $subdomain = explode('.', $hostUrl);
        if(!empty($request->start_date)&&!empty($request->end_date)){
        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
        $user = DB::table('student_table')
                ->select('*')
                ->join('card_data_mapping', 'student_table.id', '=', 'card_data_mapping.student_tbl_id')
                ->where('student_table.status','=','1')
                // ->Where('student_table.id','>','155472')
                ->whereBetween('valid_upto', [$startDate, $endDate])
                ->get()->toArray();
        // 
                // dd($user);
        foreach ($user as $key => $value) {
        $value->certificate_filepath=$request->getScheme().'://' . $subdomain[0].'.'.$subdomain[1].'.com/' .$subdomain[0].'/backend/pdf_file/' . $value->certificate_filename;
        }
    }
        $spreadsheet = new Spreadsheet();
            // Set document properties
            $spreadsheet->getProperties()->setCreator('PhpOffice')
            ->setLastModifiedBy('PhpOffice')
            ->setTitle('Office 2007 XLSX Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('PhpOffice')
            ->setKeywords('PhpOffice')
            ->setCategory('PhpOffice');

            // Add some data
            $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Serial No')
            ->setCellValue('B1', 'Name')
            ->setCellValue('C1', 'Enrolment No')
            ->setCellValue('D1', 'Course Name')
            ->setCellValue('E1', 'Validity')
            ->setCellValue('F1', 'Hub Name');
                // $i=0;
            foreach ($user as $key => $sheet_one) {
                $i=$key+2;
                $spreadsheet->getActiveSheet()->setCellValue("A$i",$sheet_one->unique_number);
                $spreadsheet->getActiveSheet()->setCellValue("B$i",$sheet_one->Candidate_name);
                $spreadsheet->getActiveSheet()->setCellValue("C$i",$sheet_one->enrollment_no);
                $spreadsheet->getActiveSheet()->setCellValue("D$i",$sheet_one->course);
                $spreadsheet->getActiveSheet()->setCellValue("E$i",$sheet_one->valid_upto);
                $spreadsheet->getActiveSheet()->setCellValue("F$i",$sheet_one->Hub_Name);
                // $i++;
            }
             ///////////////////////////////////////
                // $size=array('A'=>12.69,'B'=>41.202,'C'=>23.7,'D'=>12.2,'E'=>6.372,'F'=>24.62,'G'=>24.03,'H'=>29.16,'I'=>27,'J'=>25.76,'K'=>27.4,'L'=>32.9,'M'=>32.2,'N'=>33.875,'O'=>23.11,'P'=>22.623,'Q'=>27.73);
                foreach ($spreadsheet->getSheet(0)->getColumnIterator() as $column) {
                // if(in_array($column->getColumnIndex(),range('A','Q'))){

                // $spreadsheet->getSheet(0)->getColumnDimension($column->getColumnIndex())->setWidth($size[$column->getColumnIndex()]);
                // }else{

                $spreadsheet->getSheet(0)->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                // }

                }
                // Get the active sheet
                $sheet = $spreadsheet->getSheet(0);

                // Iterate through cells to adjust row heights based on content length
                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    foreach ($cellIterator as $cell) {
                        // Calculate and set row height based on content length
                        $content = $cell->getValue();
                        $wrappedContent = wordwrap($content, 20); // Adjust 50 to your desired line length
                        $lineCount = substr_count($wrappedContent, "\n") + 1;
                        $currentHeight = $sheet->getRowDimension($cell->getRow())->getRowHeight();
                        $neededHeight = $lineCount * 12; // Adjust 15 based on font size and style
                        
                        if ($neededHeight > $currentHeight) {
                            $sheet->getRowDimension($cell->getRow())->setRowHeight($neededHeight);
                        }
                    }
                }

            
            $range = 'A:F';
            $style = [
            'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
            'wrapText' => true,
            ],
            ];
            $spreadsheet->getSheet(0)->getStyle($range)->applyFromArray($style);
            $spreadsheet->getSheet(0)->getStyle('A1:F1')->getFont()->setBold(true)->setSize(12);
           //  ///////////////////////////////////////
           
            // Rename worksheet
            $spreadsheet->getActiveSheet()->setTitle('cards expiring report');

            $spreadsheet->createSheet();
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $spreadsheet->setActiveSheetIndex(0);
            // Redirect output to a client’s web browser (Xlsx)
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            // header('Content-Disposition: attachment;filename="01simple.xlsx"');
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            if(count($user)>0){
            $todaysdate = date_format(Carbon::today(), "d-m-Y");
            $att_name=public_path($subdomain[0]."/backend/reports/cards_expiring_report.xlsx");
            if(file_exists($att_name))
            unlink($att_name);
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($att_name);
            //  $writer->save('php://output');
            // exit;
            // new code excel end
            
             
            };
        // dd($user);
            $download_url=$request->getScheme().'://' . $subdomain[0].'.'.$subdomain[1].'.com/'.$subdomain[0].'/backend/reports/cards_expiring_report.xlsx';
        if($user){
          return response()->json(['status' => 200,'message' => 'success','data'=> $download_url],200);
        }else{
          return response()->json(['success' => false,'status' => 200,'message' => 'No data found'],200);
        }
        }
        else
        {
             return response()->json(array('success' => false,'status'=>403, 'message' => 'Access forbidden.'),403);
        }

   }
    public function Cards_Listing_Pdf(Request $request)
   {    
    // dd(ApiSecurityLayer::checkAccessTokenInstitute($request->user_id));
        if (ApiSecurityLayer::checkAccessTokenInstitute($request->user_id)) 
        {   
            
            $hostUrl = $request->getHttpHost();
            $subdomain = explode('.', $hostUrl);
            $query = DB::table('student_table')
                ->select('*')
                ->join('card_data_mapping', 'student_table.id', '=', 'card_data_mapping.student_tbl_id')
                ->where('student_table.status', '=', '1');

            if (!empty($request->select_type) && !empty($request->select_value)) {
                switch ($request->select_type) {
                    case '1':
                        $query->where('card_data_mapping.batch_no', '=', $request->select_value);
                        break;
                    case '2':
                        $query->where('card_data_mapping.Email_ID', '=', $request->select_value);
                        break;
                    default:
                        $query->where('card_data_mapping.Mobile_Number', '=', $request->select_value);
                        break;
                }
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $query->whereBetween('valid_upto', [$request->start_date, $request->end_date]);
            }

            $user = $query->get()->toArray();
            // dd($user);
            // Create new PDF document
            $pdf = new \TCPDF();

            // Set document information
            $pdf->SetCreator('Seqr Doc App');
            $pdf->SetAuthor('Seqr Doc App');
            $pdf->SetTitle('Card List Report');
            $pdf->SetSubject('Card List Report');

            // Set default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set default font
            $pdf->SetFont('helvetica', '', 12);

            // Add a page
            $pdf->AddPage();

            // Write content
            $html = '<h2 style="text-align: center;">Card List Report</h2><br><table border="1" cellpadding="5"><tr><th>S. No </th><th>Name</th><th>Enrollment No</th><th>Course Name</th><th>Validity</th><th>Hub</th><th>Batch No.</th></tr>';
            
            foreach ($user as $key =>$row) {
                // dd($key+1);
                $validity=(!empty($row->valid_upto))?$row->valid_upto:$row->on_date;
                $sr_no=($key+1);
                $html .= '<tr>';
                $html .= '<td>' . $sr_no.'</td>';
                $html .= '<td>' . $row->Candidate_name . '</td>';
                $html .= '<td>' . $row->enrollment_no . '</td>';
                $html .= '<td>' . $row->course . '</td>';
                $html .= '<td>' . $validity . '</td>';
                $html .= '<td>' . $row->Hub_Name . '</td>';
                $html .= '<td>' . $row->batch_no . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
            if (empty(trim($html))) {
                dd('HTML is empty!'); // or var_dump($html);
            }
            // echo "<pre>";
            // print_r($html);
            // exit;
            // Output HTML content
            $pdf->writeHTML($html, true, false, true, false, '');
            $att_name=public_path($subdomain[0]."/backend/reports/pdf/cards_listing_report.pdf");
            if(file_exists($att_name))
            unlink($att_name);

            $pdf->Output($att_name, 'F'); // 'F' = save to file

            // echo "PDF generated at: $savePath";
            $download_url=$request->getScheme().'://' . $subdomain[0].'.'.$subdomain[1].'.com/'.$subdomain[0].'/backend/reports/pdf/cards_listing_report.pdf';
            if($user){
              return response()->json(['status' => 200,'message' => 'success','data'=> $download_url],200);
            }else{
              return response()->json(['success' => false,'status' => 200,'message' => 'No data found'],200);
            }
        }
        else
        {
             return response()->json(array('success' => false,'status'=>403, 'message' => 'Access forbidden.'),403);
        }
    }
}
