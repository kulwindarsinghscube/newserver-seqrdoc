<?php
/**
 *
 *  Author : Ketan valand 
 *   Date  : 27/11/2019
 *   Use   : listing of Profile & Changes Password
 *
**/
namespace App\Http\Controllers\InstituteStudentWallet;

use App\Http\Controllers\Controller;
use App\models\InstituteStudents;
use App\models\SessionManager;
use Illuminate\Http\Request;
use Auth;
use Hash;
use File;
class ProfileController extends Controller
{   
    /**
     * Display a listing of the Profile.
     *
     * @return view response
     */ 
    public function index(Request $request)
    {
    //     if ($request->isMethod('post')) {
    //     return redirect()->route('inswebapp.profile.updatephoto');
    // } else {
        // Show profile
    	return view('InstituteStudentWallet.profile.index');
    // }
      // dd(Auth::guard('gswallet')->user());
    }
    /**
     * Changes Password of Webuser.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
       if(!empty($request['password'])) {

          $user_id=Auth::guard('inswallet')->user()->id;
          $request['password']=Hash::make($request['password']);
          $change_status=InstituteStudents::where('id',$user_id)->update(['password'=>$request['password']]);


          $session_id =  SessionManager::select('session_id')->where('user_id',$user_id)->where('is_logged',1)->get()->toArray();
          // dd($session_id);

          foreach ($session_id as $key => $value) {
           
            $session_val = $value['session_id'];  
            $request->session()->forget('session_id');

            Auth::guard('inswallet')->logout();
          }
          SessionManager::where('user_id',$user_id)->update(['is_logged'=>0,'logout_time'=>date('Y-m-d H:i:s')]);

                 

          return $change_status ? response()->json(['success'=>true]) : "false"; 
       }

    }

        public function updateprofile(Request $request)
    {
        $domain = $request->getHost();
        if($domain=='certificate.kmtc.ac.ke'){ 
          $domain = 'kmtc.seqrdoc.com';
        }
        $subdomain = explode('.', $domain);

        $data = $request->only([
            'Student_Name',
            'Father_Name',
            'Mother_name',
            'mobile_no',
            'enrol_roll_number',
            'admission_year',
            'graduation_year',
            'adhar_no',
            'abc_id',
            'local_address',
            'permanent_address',
            'blood_group',
            'dob',
            'gender'
        ]);
        // dd($data);
        if ($request->hasFile('photo')) {
            // dd(public_path($subdomain[0].'/backend/templates/profile_photos/'));
            $file = $request->file('photo');
            $filename = Auth::guard('inswallet')->user()->id . '.' . $file->getClientOriginalExtension();
            if (!File::exists(public_path($subdomain[0].'/backend/templates/profile_photos/'))) {
                File::makeDirectory(public_path($subdomain[0].'/backend/templates/profile_photos/'), 0755, true); // recursive = true
            }
            // Optional: delete old photo if exists
            if (!empty(Auth::guard('inswallet')->user()->photo) && file_exists(public_path($subdomain[0].'/backend/templates/profile_photos/'.Auth::guard('inswallet')->user()->photo))) {
                @unlink(public_path($subdomain[0].'/backend/templates/profile_photos/'.Auth::guard('inswallet')->user()->photo));
            }
            $file->move(public_path($subdomain[0].'/backend/templates/profile_photos'), $filename);
            // $file->move(public_path('demo/backend/templates/profile_photos'), $filename);

            $data['photo'] = $filename;
        }
       if(!empty($data)) {
          $user_id=Auth::guard('inswallet')->user()->id;
          $change_profile=InstituteStudents::where('id',$user_id)->update($data);
          return $change_profile ? response()->json(['success'=>true]) : "false"; 
       }

    }

    public function sendToAnotherServer(Request $request)
    {
        $file = $request->file('photo');

        $response = Http::attach(
            'photo', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
        )->post('https://demo.seqrdoc.com/api/receive-photo');

        return $response->json(); // view response from server B
    }
}
