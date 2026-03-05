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
// use App\models\GlobalStudents;
use App\models\StudentTable;
use App\models\Site;
use App\models\SessionManager;
use Illuminate\Http\Request;
use Auth;
use Hash;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class DocumentsController extends Controller
{  
    public function importJsonData()
    {
        // Load JSON file from /storage/app/data/data.json
        $json = Storage::get('data/data.json');

          // Decode JSON into array
        $records = json_decode($json, true);
        //         $serials = array_column($records, 'Unique Serial No.');
        // echo "'" . implode("','", $serials) . "'";
        // die();
        //         $OTP = $this->generateOTP();//Hash::make($this->generateOTP())//Hash::check('123456', $hashedOTP)
        // $inputdata['password']=Hash::make($OTP);
        // Loop through each record
        // DB::enableQueryLog();
        foreach ($records as $record) {
            $institute_data = [
                'Student_Name'      => $record['NAME'],
                'institute_email'   => $record['Email'],
                'dob'               => $record['DOB'],
                'mobile_no'         => (string) $record['Mobile number'],
                'status'          => 1,
                'verify_by'          => 1,
                'is_verified'          => 1,
                'OTP'          => 16636,
                'password'          => Hash::make('Admin@123'),
                'enrol_roll_number' => $record['ID Number']
            ];

            $institute_s_id = DB::table('institute_students')->insertGetId($institute_data);

            // Update student_table_test with the institute_SId
            DB::table('student_table')
              ->where('serial_no', $record['Unique Serial No.'])
              ->where('status', 1)
              ->update(['kmtc_national_id' => $record['ID Number'],'institute_SId' => $institute_s_id,'global_SId' => $institute_s_id]);
        }
        // echo"<pre>";print_r(DB::getQueryLog());
        return response()->json(['status' => 'success']);
    }


    public function viewDocument($id)
    {
        // dd($id);
        $pdfUrl=base64_decode($id);
        if (!filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
            abort(403, 'Invalid document URL.');
        }
        // Show PDF in browser
        // return redirect()->away($pdfUrl);
        return view('InstituteStudentWallet.documents.show',array('pdfUrl'=>$pdfUrl));
    }

    /**
     * Display a listing of the Profile.
     *
     * @return view response
     */ 
    public function index()
    {
        // dd(Auth::guard('inswallet')->id());
        // dd(Auth::guard('inswallet')->user());
        $subQuery = DB::table('institute_students as gs')
        ->select('st.site_id')
        ->distinct()
        ->join('student_table as st', 'st.institute_SId', '=', 'gs.id')
        ->where('st.institute_SId', Auth::guard('inswallet')->id())
        ->where('st.status', 1);

        $institutes = Site::select('site_id','site_url')->whereIn('site_id', $subQuery)->where('status',1)->get();
        // dd($sites);
        // $institutes=Site::select('*')->where('status',1)->get();
    	  return view('InstituteStudentWallet.documents.index',array('institutes'=>$institutes));
    }

    public function getInstituteCertificates(Request $request)
    {
        $domain = $request->getHost();;
        if($domain=='certificate.kmtc.ac.ke'){ 
            $domain = 'kmtc.seqrdoc.com';
        }
        // dd($domain);
        $subdomain = explode('.', $domain);
        $sitedata=DB::table('sites')->where('site_url',$domain)->get()->first();
        // dd(Auth::guard('inswallet')->user());
        if(!empty($sitedata->site_id)) {
            $userId = Auth::guard('inswallet')->id();
            $kmtc_national_id = Auth::guard('inswallet')->user()->enrol_roll_number;
            // dd($kmtc_national_id);
            $query = DB::table('student_table as st')
            ->select('st.site_id','st.serial_no','st.created_at','st.certificate_filename')
            // Apply conditions based on subdomain
            // if ($subdomain[0] == 'kmtc') {
            //     $query->where([
            //         'st.status' => 1,
            //         'st.publish' => 1,
            //         'st.kmtc_national_id' => $kmtc_national_id,
            //     ]);
            // } else {
            //     $query->where([
            //         'st.status' => 1,
            //         'st.publish' => 1,
            //         'st.institute_SId' => $userId,
            //     ]);
            // }
            ->where(function ($q) use ($subdomain, $kmtc_national_id, $userId) {
                $q->where('st.status', 1)
                  ->where('st.publish', 1)
                  ->where(function ($subQ) use ($subdomain, $kmtc_national_id, $userId) {
                      if ($subdomain[0] == 'kmtc') {
                          $subQ->where('st.kmtc_national_id', $kmtc_national_id)->orWhere('st.institute_SId', '=', $userId);
                      } else {
                          $subQ->where('st.institute_SId', $userId);
                      }
                  });
            });
            $query->where('st.site_id', $sitedata->site_id);
         
            // dd($query->get());
            return DataTables::of($query)
            ->filterColumn('serial_no', function($query, $keyword) {
              $query->whereRaw("LOWER(st.serial_no) LIKE ?", ["%{$keyword}%"]);
            })
            // ->filterColumn('template_name', function($query, $keyword) {
            //     $query->whereRaw("LOWER(tm.template_name) LIKE ?", ["%{$keyword}%"]);
            // })
            // ->filterColumn('site_url', function($query, $keyword) {
            //     $query->whereRaw("LOWER(s.site_url) LIKE ?", ["%{$keyword}%"]);
            // })
            ->filterColumn('created_at', function($query, $keyword) {
                $query->whereRaw("LOWER(st.created_at) LIKE ?", ["%{$keyword}%"]);
            })
            ->addColumn('action', function ($row) use ($subdomain){
            
              // $url = urlencode('http://'.$request->fullDomain.'/'.$request->subdomain.'/backend/pdf_file/'.$row->certificate_filename); // or route to certificate
              // dd($subdomain[0]);
              $doc_url=url('/'.$subdomain[0].'/backend/pdf_file/'.$row->certificate_filename);
              $doc_url_seqr = route('insview.document', base64_encode($doc_url));
              $url = urlencode($doc_url); // or route to certificate
              // $url = urlencode('https://mvsr.seqrdoc.com/mvsr/backend/pdf_file/GUID00001.pdf'); // or route to certificate
              $text = urlencode("Check this certificate!");
              ;
              // return '
                  // <a href="https://www.facebook.com/sharer/sharer.php?u=' . $url . '" 
                  //    target="_blank" title="Share on Facebook">
                  //    <i class="fa fa-facebook"></i>
                  // </a>&nbsp;

                  // <a href="https://www.linkedin.com/shareArticle?mini=true&url=' . $url . '" 
                  //    target="_blank" title="Share on Linkdin">
                  //    <i class="fa fa-linkedin"></i>
                  // </a>&nbsp;

                  // <a href="https://api.whatsapp.com/send?text=' . $text . '%20' . $url . '" 
                  //    target="_blank" title="Share on WhatsApp">
                  //    <i class="fa fa-whatsapp"></i>
                  // </a>&nbsp;

                  // <a href="https://twitter.com/intent/tweet?text=' . $text . '&url=' . $url . '" 
                  //    target="_blank" title="Share on Twitter">
                  //    <i class="fa fa-twitter"></i>
                  // </a>
              // ';
              return '
                  <a href="' . $doc_url_seqr . '" 
                    target="_blank" title="View Document">
                    <i class="fa fa-eye"></i>
                  </a>
              ';
            })
            ->rawColumns(['action']) // VERY IMPORTANT for rendering HTML
            ->make(true);
        }
        if (!$sitedata->site_id) {
          return response()->json(['data' => []]);
        }
    }

    
}
