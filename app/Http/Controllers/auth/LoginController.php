<?php
/**
 *
 *  Author : Ketan valand 
 *   Date  : 13/11/2019
 *   Use   : check specific login detail
 *
**/
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebUserRequest;
use App\models\SessionManager;
use App\models\User;
use App\models\GlobalStudents;
use App\models\InstituteStudents;
use App\models\demo\Site as Demosite;
use Auth;
use Helper;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Jobs\SendMailJob;
use URL;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    
    use AuthenticatesUsers;
    public function webInstStudentLogout(Request $request)
    {
       // when auto logout user then store logout time and forget session value

        $session_val = $request->session()->get('session_id');
        $this->sessionLogout($session_val);
        $request->session()->forget('session_id');

        Auth::guard('inswallet')->logout();

        return $this->loggedOut($request) ?: redirect()->route('inswebapp.index');
    }
    public function webStudentLogout(Request $request)
    {
       // when auto logout user then store logout time and forget session value

        $session_val = $request->session()->get('session_id');
        $this->sessionLogout($session_val);
        $request->session()->forget('session_id');

        Auth::guard('gswallet')->logout();

        return $this->loggedOut($request) ?: redirect()->route('gswebapp.index');
    }
    public function showWebInstStudentLogin()
    {
      $site_id=Helper::GetSiteId($_SERVER['SERVER_NAME']);
      $site_url = explode(".",$_SERVER['SERVER_NAME']);
      if($site_id == 237)
      {
          $sitename = ucfirst("IIT Jammu");
      }else{
          $sitename = ucfirst($site_url[0]);
      }
      
  
      $site_data = Demosite::select('apple_app_url','android_app_url')->where('site_id',$site_id)->first();
      
      return view('auth.inststudentlogin',compact('site_data','sitename'));
    }
    public function webInstStudentLogin(WebUserRequest $request)
    {
     
     // \Illuminate\Support\Facades\Config::set('database.connections.demo_connect', [
     //        'driver'   => 'mysql',
     //        'host'     => \Config::get('constant.DB_HOST'),
     //        "port" => \Config::get('constant.DB_PORT'),
     //        'database' => 'seqr_d_zeal',
     //        'username' => \Config::get('constant.DB_UN'),
     //        'password' => \Config::get('constant.DB_PW'),
     //        "unix_socket" => "",
     //        "charset" => "utf8mb4",
     //        "collation" => "utf8mb4_unicode_ci",
     //        "prefix" => "",
     //        "prefix_indexes" => true,
     //        "strict" => true,
     //        "engine" => null,
     //        "options" => []
     //    ]);  
     $site_id=Helper::GetSiteId($_SERVER['SERVER_NAME']);
     $site_url = explode(".",$_SERVER['SERVER_NAME']);
     if($site_id == 237)
     {
          $sitename = ucfirst("IIT Jammu");
     }else{
          $sitename = ucfirst($site_url[0]);
     }
     
      
      $credential1=[

        'institute_email'=>$request->username,
        'password'=>$request->password,
        
        'status'=>1,
        'verify_by'=>1,
        'is_verified'=>1
      ];
      

      
  
     if($site_id!=null)
     {
        $userData = InstituteStudents::select('*')->where('institute_email',$request->username)->first();
        // dd(Auth::guard('inswallet')->attempt($credential1));
        // dd($userData);
        // $OTP = $this->generateOTP();
        // dd(Hash::make($OTP)."and".$OTP);//96220
        if(!$userData){
          return response()->json(['success'=>false,'msg'=>'User not found with this username.']); 
        }else if(Hash::check($request->password, $userData['password'])&&$userData&&$userData['status']==0){
            
           if($userData['verify_by'] == 2 && $userData['OTP'] == 0){
                //sending mail
                $OTP = $this->generateOTP();
                InstituteStudents::where('id',$userData['id'])->update(['OTP'=>$OTP]);
                $mail_view = 'mail.student_wallet_otp';
                $user_email = $userData['institute_email'];
                
                $mail_subject = 'Student Wallet OTP Verification';
                $user_data = ['name'=>$userData['Student_Name'],'otp'=>$OTP];
                try {
                    
                $this->dispatch(new SendMailJob($mail_view,$user_email,$mail_subject,$user_data));
                } catch (Exception $e) {
                    dd($e);
                }
               return response()->json(['success'=>'verify','msg'=>'Your verification is pending. Please check your email for OTP.']); 
              }else if($userData['verify_by'] == 2 && $userData['OTP'] != 0){
                $result = InstituteStudents::where('id',$userData['id'])->where('OTP',$request->otp)->update(['verify_by'=>1]);
                $msg=($result==1)?'Your verification successfull.':'Your verification fail.';
                $vstatus=($result==1)?'verified':'vfail';
               return response()->json(['success'=>$vstatus,'msg'=>$msg]); 
              }else if($userData['verify_by'] == 1 && $userData['OTP'] != 0){
                $result = InstituteStudents::where('id',$userData['id'])->update(['is_verified'=>1,'status'=>1,'password'=>Hash::make($request->new_password)]);
                if($result){
                   $result = Auth::guard('inswallet')->user();
                    //store user's info in session manager when login user  
                    $session_manager = new SessionManager();
                    
                    $user_id = Auth::guard('inswallet')->user()->id;
                    $session_id = \Hash::make(rand(1,1000));
                    $session_manager->user_id = $user_id;
                    $session_manager->session_id = $session_id;
                    $session_manager->login_time = date('Y-m-d H:i:s');
                    $session_manager->is_logged = 1;
                    $session_manager->device_type = 'inswallet';
                    $session_manager->ip = \Request::ip();
                    $session_manager->site_id=$site_id;
                    $session_manager->save();

                    $auth_id=Auth::guard('inswallet')->user()->id; 
                    // $insert_id=GlobalStudents::where('id',$auth_id)->update(['site_id'=>$site_id]);
                    
                     // put value in session
                     $request->session()->put('session_id',$session_manager['id']);
                     $request->session()->put('site_name',$sitename);
                    return response()->json(['success'=>200,'msg'=>'Your password set successfull.']);  
                }else{
                   return response()->json(['success'=>405,'msg'=>'Fail to set password']); 
                }
                
              }

        }else if(Hash::check($request->password, $userData['password'])&&$userData&&$userData['status']!=1){
          return response()->json(['success'=>false,'msg'=>'Your account has been deactivated! Please contact to system administrator.']);
        }else if(Auth::guard('inswallet')->attempt($credential1))
        {   
            $result = Auth::guard('inswallet')->user();

       
                // dd($result);
                //store user's info in session manager when login user  
                $session_manager = new SessionManager();
                
                $user_id = Auth::guard('inswallet')->user()->id;
                $session_id = \Hash::make(rand(1,1000));
                $session_manager->user_id = $user_id;
                $session_manager->session_id = $session_id;
                $session_manager->login_time = date('Y-m-d H:i:s');
                $session_manager->is_logged = 1;
                $session_manager->device_type = 'inswallet';
                $session_manager->ip = \Request::ip();
                $session_manager->site_id=$site_id;
                $session_manager->save();

                $auth_id=Auth::guard('inswallet')->user()->id; 
                // $insert_id=GlobalStudents::where('id',$auth_id)->update(['site_id'=>$site_id]);
                
                 // put value in session
                 $request->session()->put('session_id',$session_manager['id']);
                 $request->session()->put('site_name',$sitename);
                 // dd(['success'=>true]);
                 return response()->json(['success'=>true]);  
          
       }
      else
       {
        return response()->json(['success'=>false,'msg'=>'These credentials do not match our records.']);  
       }
     }
     else
     {
      
       return response()->json(['success'=>'Not','msg'=>'Please Contact Service Porvider']);
     }
   }
    public function showWebStudentLogin()
    {
      $site_id=Helper::GetSiteId($_SERVER['SERVER_NAME']);
      $site_url = explode(".",$_SERVER['SERVER_NAME']);
      if($site_id == 237)
     {
          $sitename = ucfirst("IIT Jammu");
     }else{
          $sitename = ucfirst($site_url[0]);
     }
      
  
      $site_data = Demosite::select('apple_app_url','android_app_url')->where('site_id',$site_id)->first();
      
      return view('auth.studentlogin',compact('site_data','sitename'));
    }

    
    public function webStudentLogin(WebUserRequest $request)
    {
     
     // \Illuminate\Support\Facades\Config::set('database.connections.demo_connect', [
     //        'driver'   => 'mysql',
     //        'host'     => \Config::get('constant.DB_HOST'),
     //        "port" => \Config::get('constant.DB_PORT'),
     //        'database' => 'seqr_d_zeal',
     //        'username' => \Config::get('constant.DB_UN'),
     //        'password' => \Config::get('constant.DB_PW'),
     //        "unix_socket" => "",
     //        "charset" => "utf8mb4",
     //        "collation" => "utf8mb4_unicode_ci",
     //        "prefix" => "",
     //        "prefix_indexes" => true,
     //        "strict" => true,
     //        "engine" => null,
     //        "options" => []
     //    ]);  
     $site_id=Helper::GetSiteId($_SERVER['SERVER_NAME']);
     $site_url = explode(".",$_SERVER['SERVER_NAME']);
     if($site_id == 237)
     {
          $sitename = ucfirst("IIT Jammu");
     }else{
          $sitename = ucfirst($site_url[0]);
     }
     
      
      $credential1=[

        'personal_email'=>$request->username,
        'password'=>$request->password,
        
        'status'=>1,
        'verify_by'=>1,
        'is_verified'=>1
      ];
      

      
  
     if($site_id!=null)
     {
        $userData = GlobalStudents::select('*')->where('personal_email',$request->username)->first();
        // $OTP = $this->generateOTP();
        // dd(Hash::make($OTP)."and".$OTP);//96220
        if(!$userData){
          return response()->json(['success'=>false,'msg'=>'User not found with this username.']); 
        }else if(Hash::check($request->password, $userData['password'])&&$userData&&$userData['status']==0){
            
           if($userData['verify_by'] == 2 && $userData['OTP'] == 0){
                //sending mail
                $OTP = $this->generateOTP();
                GlobalStudents::where('id',$userData['id'])->update(['OTP'=>$OTP]);
                $mail_view = 'mail.student_wallet_otp';
                $user_email = $userData['personal_email'];
                
                $mail_subject = 'Student Wallet OTP Verification';
                $user_data = ['name'=>$userData['Student_Name'],'otp'=>$OTP];
                try {
                    
                $this->dispatch(new SendMailJob($mail_view,$user_email,$mail_subject,$user_data));
                } catch (Exception $e) {
                    dd($e);
                }
               return response()->json(['success'=>'verify','msg'=>'Your verification is pending. Please check your email for OTP.']); 
              }else if($userData['verify_by'] == 2 && $userData['OTP'] != 0){
                $result = GlobalStudents::where('id',$userData['id'])->where('OTP',$request->otp)->update(['verify_by'=>1]);
                $msg=($result==1)?'Your verification successfull.':'Your verification fail.';
                $vstatus=($result==1)?'verified':'vfail';
               return response()->json(['success'=>$vstatus,'msg'=>$msg]); 
              }else if($userData['verify_by'] == 1 && $userData['OTP'] != 0){
                $result = GlobalStudents::where('id',$userData['id'])->update(['is_verified'=>1,'status'=>1,'password'=>Hash::make($request->new_password)]);
                if($result){
                   $result = Auth::guard('gswallet')->user();
                    //store user's info in session manager when login user  
                    $session_manager = new SessionManager();
                    
                    $user_id = Auth::guard('gswallet')->user()->id;
                    $session_id = \Hash::make(rand(1,1000));
                    $session_manager->user_id = $user_id;
                    $session_manager->session_id = $session_id;
                    $session_manager->login_time = date('Y-m-d H:i:s');
                    $session_manager->is_logged = 1;
                    $session_manager->device_type = 'gswallet';
                    $session_manager->ip = \Request::ip();
                    $session_manager->site_id=$site_id;
                    $session_manager->save();

                    $auth_id=Auth::guard('gswallet')->user()->id; 
                    // $insert_id=GlobalStudents::where('id',$auth_id)->update(['site_id'=>$site_id]);
                    
                     // put value in session
                     $request->session()->put('session_id',$session_manager['id']);
                     $request->session()->put('site_name',$sitename);
                    return response()->json(['success'=>200,'msg'=>'Your password set successfull.']);  
                }else{
                   return response()->json(['success'=>405,'msg'=>'Fail to set password']); 
                }
                
              }

        }else if(Hash::check($request->password, $userData['password'])&&$userData&&$userData['status']!=1){
          return response()->json(['success'=>false,'msg'=>'Your account has been deactivated! Please contact to system administrator.']);
        }else if(Auth::guard('gswallet')->attempt($credential1))
        {   
            $result = Auth::guard('gswallet')->user();

       

                //store user's info in session manager when login user  
                $session_manager = new SessionManager();
                
                $user_id = Auth::guard('gswallet')->user()->id;
                $session_id = \Hash::make(rand(1,1000));
                $session_manager->user_id = $user_id;
                $session_manager->session_id = $session_id;
                $session_manager->login_time = date('Y-m-d H:i:s');
                $session_manager->is_logged = 1;
                $session_manager->device_type = 'gswallet';
                $session_manager->ip = \Request::ip();
                $session_manager->site_id=$site_id;
                $session_manager->save();

                $auth_id=Auth::guard('gswallet')->user()->id; 
                // $insert_id=GlobalStudents::where('id',$auth_id)->update(['site_id'=>$site_id]);
                
                 // put value in session
                 $request->session()->put('session_id',$session_manager['id']);
                 $request->session()->put('site_name',$sitename);
                 // dd(['success'=>true]);
                 return response()->json(['success'=>true]);  
          
       }
      else
       {
        return response()->json(['success'=>false,'msg'=>'These credentials do not match our records.']);  
       }
     }
     else
     {
      
       return response()->json(['success'=>'Not','msg'=>'Please Contact Service Porvider']);
     }
   }

/*    public function __construct()
    {
      $this->middleware('WebUser:webuser')->except('web.logout');
    }*/
    public function showWebUserLogin()
    {
      
      $site_id=Helper::GetSiteId($_SERVER['SERVER_NAME']);
      $site_url = explode(".",$_SERVER['SERVER_NAME']);
      if($site_id == 237)
     {
          $sitename = ucfirst("IIT Jammu");
     }else{
          $sitename = ucfirst($site_url[0]);
     }
      
     
      $site_data = Demosite::select('apple_app_url','android_app_url')->where('site_id',$site_id)->first();
      $allowedDomain = 'certificate.kmtc.ac.ke';
      $domain = \Request::getHost();

      
      if ($domain == $allowedDomain) {
       
        return view('auth.inststudentlogin',compact('site_data','sitename'));
        exit();
      }
      return view('auth.userlogin',compact('site_data','sitename'));
    }
    public function webuserLogin(WebUserRequest $request)
    {
       
     $site_id=Helper::GetSiteId($_SERVER['SERVER_NAME']);
     $site_url = explode(".",$_SERVER['SERVER_NAME']);
     if($site_id == 237)
     {
          $sitename = ucfirst("IIT Jammu");
     }else{
          $sitename = ucfirst($site_url[0]);
     }
     
      
      $credential1=[

        'username'=>$request->username,
        'password'=>$request->password,
        
        'publish'=>1,
        'site_id'=>$site_id,
      ];
      

      
  
     if($site_id!=null)
     {
        $userData = User::select('*')->where('username',$request->username)->first();
        
        if(!$userData){
          return response()->json(['success'=>false,'msg'=>'User not found with this username.']); 
        }else if($userData&&$userData['is_verified']!=1){

           if($userData['verify_by'] == 2){
                //sending mail
                $mail_view = 'mail.index';
                $user_email = $userData['email_id'];
                
                $mail_subject = 'Activate your account for SeQR Mobile App';
                $user_data = ['name'=>$userData['username'],'token'=>$userData['token']];

                $this->dispatch(new SendMailJob($mail_view,$user_email,$mail_subject,$user_data));
              }
               return response()->json(['success'=>200,'msg'=>'Your verification is pending. Please check your email for verification link.',
                'link' =>  URL::route('webapp.verify',$userData['email_id'])]); 

        }else if($userData&&$userData['status']!=1){
          return response()->json(['success'=>false,'msg'=>'Your account has been deactivated! Please contact to system administrator.']);
        }else if(Auth::guard('webuser')->attempt($credential1))
        {   
            $result = Auth::guard('webuser')->user();

       

                //store user's info in session manager when login user  
                $session_manager = new SessionManager();
                
                $user_id = Auth::guard('webuser')->user()->id;
                $session_id = \Hash::make(rand(1,1000));
                $session_manager->user_id = $user_id;
                $session_manager->session_id = $session_id;
                $session_manager->login_time = date('Y-m-d H:i:s');
                $session_manager->is_logged = 1;
                $session_manager->device_type = 'webuser';
                $session_manager->ip = \Request::ip();
                $session_manager->site_id=$site_id;
                $session_manager->save();

                $auth_id=Auth::guard('webuser')->user()->id; 
                $insert_id=User::where('id',$auth_id)->update(['site_id'=>$site_id]);
                
                 // put value in session
                 $request->session()->put('session_id',$session_manager['id']);
                 $request->session()->put('site_name',$sitename);

                 return response()->json(['success'=>true]);  
          
       }
      else
       {
        return response()->json(['success'=>false,'msg'=>'These credentials do not match our records.']);  
       }
     }
     else
     {
      
       return response()->json(['success'=>'Not','msg'=>'Please Contact Service Porvider']);
     }
   }
   public function webLogout(Request $request)
    {
       // when auto logout user then store logout time and forget session value

        $session_val = $request->session()->get('session_id');
        $this->sessionLogout($session_val);
        $request->session()->forget('session_id');

        Auth::guard('webuser')->logout();

        return $this->loggedOut($request) ?: redirect()->route('webapp.index');
    }
    public function autoLogout(Request $request){
      // when auto logout user then store logout time and forget session value

      $session_val = $request->session()->get('session_id');
      
      $this->sessionLogout($session_val);

      $request->session()->forget('session_id');

      Auth::guard('webuser')->logout();
      
    }
     public function sessionLogout($session_val){
      
      // call seperate function to logout
      $session_manager = SessionManager::find($session_val);
      
      $session_manager->logout_time = date('Y-m-d H:i:s');
      $session_manager->is_logged = 0;
      
      $session_manager->save();

    }
  
}
