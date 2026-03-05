<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Admin;
use App\models\InstituteMaster;
use App\models\TemplateMaster;
use App\models\User;
use App\models\ScannedHistory;
use App\models\Transactions;
use App\models\StudentTable;
use Auth;
use QrCode;
use App\Helpers\CoreHelper;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use DB;

class DashboardCountController extends Controller
{
    // certificate count
    public function certificateCount() {
        $auth_site_id=Auth::guard('admin')->user()->site_id;
        
        //Count Active  Certificates

        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        $date = $_GET['date'];
        
        // print_r($_GET);
        // die();
        $query = StudentTable::where('status','1');
        $query->where('site_id',$auth_site_id);
        
        if($date) {
            $query->where(DB::raw("DATE(created_at) = '".$date."'"));
        }
        if($start_date) {
            $query->where(DB::raw("DATE(created_at) >= '".$start_date."'"));
        }
        if($end_date) {
            $query->where(DB::raw("DATE(created_at_) <= '".$end_date."'"));
        }
        
        $active_certificates = $query->count();


        //Count Inactive Certificates
        $query1 = StudentTable::where('status','0');
        $query1->where('site_id',$auth_site_id);
        
        if($date) {
            $query1->where(DB::raw("DATE(created_at) = '".$date."'"));
        }
        if($start_date) {
            $query1->where(DB::raw("DATE(created_at) >= '".$start_date."'"));
        }
        if($end_date) {
            $query1->where(DB::raw("DATE(created_at) <= '".$end_date."'"));
        }
        $inactive_certificates = $query1->count();




        //Count Certificates
        $query2 = StudentTable::where('site_id',$auth_site_id);
        
        if($date) {
            $query2->where(DB::raw("DATE(created_at) = '".$date."'"));
        }
        if($start_date) {
            $query2->where(DB::raw("DATE(created_at) >= '".$start_date."'"));
        }
        if($end_date) {
            $query2->where(DB::raw("DATE(created_at) <= '".$end_date."'"));
        }
        $total_certificates = $query2->count();

        
        

        return response()->json([
            'status' => 200,
            'active_certificates'=> $active_certificates,
            'inactive_certificates'=> $inactive_certificates,
            'total_certificates'=> $total_certificates
        ]);
    }

}
