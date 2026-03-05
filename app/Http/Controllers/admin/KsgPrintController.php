<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\TemplateMaster;
use App\models\KsgbatchModel;
use Session,TCPDF,TCPDF_FONTS,Auth,DB;
use Storage;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use App\Helpers\CoreHelper;
use Helper;
use App\Helpers\SitePermissionCheck;
use App\Helpers\RolePermissionCheck;


class KsgPrintController extends Controller
{
    public function viewpage(Request $request){
        $id=Auth::guard('admin')->user()->id;
        $branch_id=Auth::guard('admin')->user()->branch_id;
        // return view('admin.ksg.print');
        if (!is_null($branch_id) && $branch_id != '') {
            return view('admin.ksg.print');
    
          }
          else{
            return redirect()->route('ksg-home')->with('error', 'No branch is assigned to this user.');
    
          }
    }

    public function index(Request $request)
    {
     
      if ($request->ajax()) 
      {
        $approve_flag = $request->input('approve_flag');
        $branch_id=Auth::guard('admin')->user()->branch_id;
        $admin = Auth::guard('admin')->user();
        $roleName = $admin->getRoleName();
        $query = KsgbatchModel::select(
        'tbl_batch.id',
        DB::raw("MAX(tbl_batch.name) as name"), 
        DB::raw("MAX(tbl_batch.status) as status"),
        DB::raw("MAX(tbl_batch.approvar_id) as approvar_id"),
        DB::raw("MAX(tbl_batch.created_at) as created_at"),
        DB::raw("MAX(tbl_batch.updated_at) as updated_at"),
        DB::raw("MAX(tbl_batch.comment) as comment"),
        DB::raw("MAX(tbl_batch.created_by) as created_by"),
        DB::raw("MAX(tbl_batch.publish) as publish"),
        DB::raw("MAX(admin_table.fullname) as admin_name"),
        DB::raw("DATE_FORMAT(MAX(tbl_batch.created_at), '%d-%m-%Y') as formatted_date"),
        DB::raw("(SELECT COUNT(*) FROM tbl_batch_records WHERE tbl_batch_records.status = 'Pending' AND tbl_batch_records.batch_id = tbl_batch.id) as Pending"),
        DB::raw("(SELECT COUNT(*) FROM tbl_batch_records WHERE tbl_batch_records.status = 'Approved' AND tbl_batch_records.batch_id = tbl_batch.id) as Approved"),
        DB::raw("(SELECT COUNT(*) FROM tbl_batch_records WHERE tbl_batch_records.status = 'Correction' AND tbl_batch_records.batch_id = tbl_batch.id) as Correction"),
        DB::raw("(SELECT COUNT(*) FROM tbl_batch_records WHERE tbl_batch_records.status = 'Rejected' AND tbl_batch_records.batch_id = tbl_batch.id) as Rejected")
        )
        ->where('tbl_batch.publish', 1)
        
        ->where('tbl_batch.status', '=', 'Approved')
        ->join('admin_table', 'tbl_batch.created_by', '=', 'admin_table.id')
        ->leftJoin('tbl_batch_records', 'tbl_batch.id', '=', 'tbl_batch_records.batch_id')
        ->groupBy('tbl_batch.id') 
        ->orderBy('tbl_batch.id', 'desc');

        if ($roleName!="Admin") {
            $query->where('tbl_batch.branch_id', $branch_id);
        }

        $result_count = count($query->get()->toArray());
        
        $totalRecords = $result_count;
        $searchValue = $request->get('search')['value']; 
        if ($searchValue) {
            $query->where(function($q) use ($searchValue) {
                $q->where('tbl_batch.name', 'like', "%{$searchValue}%")
                  ->orWhere('admin_table.fullname', 'like', "%{$searchValue}%")
                  ->orWhere('tbl_batch.status', 'like', "%{$searchValue}%");
            });
        }
        $totalFiltered = count($query->get()->toArray());
        $length = $request->get('length', 10); 
        $start = $request->get('start', 0);    
        $data = $query->skip($start) 
            ->take($length) 
            ->get() 
            ->map(function($item) {
                $item->status_counts = 
                    "Approved: {$item->Approved}, " .
                    "Pending: {$item->Pending}, " .
                    "Correction: {$item->Correction}, " .
                    "Rejected: {$item->Rejected}";
                return $item;
            });

        // Manually add index and action columns
        $result = [];
        foreach ($data as $key => $row) {
            // Add index column
            $row->DT_RowIndex = $start + $key + 1;
          
            $printBtn="";
            $deleteBtn="";
            $approveRejectBtn="";
            $editBtn="";
            // Create encrypted ID for action buttons
            $encryptedId = encrypt($row->id);
            if($row->status=="Open"||$row->status=="Correction" ){
          
            }

            if($row->status==="Approved"){
                if(\App\Helpers\SitePermissionCheck::isPermitted('PrintBatchRecord')){
                    if(\App\Helpers\RolePermissionCheck::isPermitted('PrintBatchRecord')){

                    $printBtn = '<a href="javascript:void(0)" data-toggle="modal" data-target="#pdfModal" data-id="'.$row->id.'" data-flag="batch" title="Send For Print" style="cursor: pointer;font-weight:normal !important;" class="menu-link flex-stack px-3 printBatch">
                    <i class="fa fa-print" style="color:red;margin-right:5px; font-size:22px;"></i>
                  </a>';
                }
            }

            }

            $viewUrl = route('view-records', ['id' => $encryptedId, 'flag' => $approve_flag,'breadcrums'=>'printpage']);
            if(\App\Helpers\SitePermissionCheck::isPermitted('view-records')){
                if(\App\Helpers\RolePermissionCheck::isPermitted('view-records')){
                    $viewBtn = '<a href="'.$viewUrl.'" title="View" class="menu-link flex-stack px-3" style="font-weight: normal !important;">
                        <i class="fa fa-eye" style="color: blue;margin-right:5px; font-size:22px;"></i>
                    </a>';
                }
            }
            // Combine action buttons
            $row->action = $editBtn . $deleteBtn . $viewBtn.$approveRejectBtn .$printBtn.$fileIcon ;

            // Append the row to the result array
            $result[] = $row;
        }

       
        return response()->json([
            'draw' => intval($request->get('draw')), // Echo the draw parameter
            'recordsTotal' => $totalRecords,          // Total records before filtering
            'recordsFiltered' => $totalFiltered,      // Total records after filtering
            'data' => $result                           // Your data
        ]);

      }

       return view('admin.ksg.print');

    }


}