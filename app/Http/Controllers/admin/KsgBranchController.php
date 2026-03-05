<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\TemplateMaster;
use App\models\KsgBranchModel;
use Session,TCPDF,TCPDF_FONTS,Auth,DB;
use Storage;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use App\Helpers\CoreHelper;
use Helper;
use App\Helpers\SitePermissionCheck;
use App\Helpers\RolePermissionCheck;
use App\models\Admin;
use App\models\Role;


class KsgBranchController extends Controller
{


    public function uploadpage(){
       

        $breadcrumb='Branch';
        return view('admin.ksg.branch_index',compact(['breadcrumb']));
    }


    public function index(Request $request)
    {
     
      if ($request->ajax()) 
      {
        $approve_flag = $request->input('approve_flag');
      $query = KsgBranchModel::select(
        'tbl_branch.id',
        DB::raw("MAX(tbl_branch.name) as name"),  // Use aggregate function for non-grouped fields
        DB::raw("MAX(tbl_branch.created_at) as created_at"),
        DB::raw("MAX(tbl_branch.updated_at) as updated_at"),
        DB::raw("MAX(tbl_branch.created_by) as created_by"),
        DB::raw("MAX(tbl_branch.publish) as publish"),
        DB::raw("MAX(admin_table.fullname) as admin_name"),
        DB::raw("DATE_FORMAT(MAX(tbl_branch.created_at), '%d-%m-%Y') as formatted_date")
        )
        ->where('tbl_branch.publish', 1)
        ->join('admin_table', 'tbl_branch.created_by', '=', 'admin_table.id')
        ->groupBy('tbl_branch.id') // Only group by the id and the aggregate fields
        ->orderBy('tbl_branch.id', 'desc');

        $result_count = count($query->get()->toArray()); 
        $totalRecords = $result_count;

        // Get the search value from the request
        $searchValue = $request->get('search')['value']; // This is the search input from DataTables

        // If there's a search value, apply it to the query
        if ($searchValue) {
            $query->where(function($q) use ($searchValue) {
                $q->where('tbl_branch.name', 'like', "%{$searchValue}%")
                  ->orWhere('admin_table.fullname', 'like', "%{$searchValue}%");
                 
            });
        }

        // Get the total number of records after filtering
        $totalFiltered =  count($query->get()->toArray());

        // Handle pagination
        $length = $request->get('length', 10); // Default length to 10 if not specified
        $start = $request->get('start', 0);    // Default start to 0 if not specified

        // Get paginated data
        $data = $query->skip($start) // Skip the records
            ->take($length) // Limit the number of records returned
            ->get(); // Fetch data here
         

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
           
            if(\App\Helpers\SitePermissionCheck::isPermitted('delete-branch')){
                if(\App\Helpers\RolePermissionCheck::isPermitted('delete-branch')){
                  $deleteBtn = '<a href="javascript:void(0)" data-toggle="tooltip" data-id="'.$encryptedId.'" data-original-title="Delete" class="menu-link flex-stack px-3 DeleteBranch"><i class="fa fa-trash" style="color:red; margin-right:5px; font-size:22px;"></i></a>&nbsp;&nbsp;';
                }
            }
            if(\App\Helpers\SitePermissionCheck::isPermitted('edit-branch')){
                if(\App\Helpers\RolePermissionCheck::isPermitted('edit-branch')){
                $editBtn = '<a href="javascript:void(0)" title="Edit" data-id="'.$row->id.'" class="menu-link flex-stack px-3 editBranch"><i class="fa fa-edit" style="color: blue; margin-right:5px; font-size:22px;" id="ths"></i></a>&nbsp;&nbsp;';
                }
            }

            // Combine action buttons
            $row->action = $editBtn . $deleteBtn ;

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

       return view('admin.ksg.branch_index');

    }

    public function store(Request $request)
    {
     
        $id=Auth::guard('admin')->user()->id;
        $request->validate([
            'name' => 'required',
        ]);

        $existingRecord = KsgBranchModel::where('name', $request->name)->first();
   
        if ($existingRecord) {
            
            return response()->json(['error' => 'This branch name is already exists.']);
        }
    
        // // Insert into database
        KsgBranchModel::create([
            'name' => $request->name,
            'created_by' => $id,
            'created_at' => now(),
            'updated_at'=>now(),
        ]);

        return response()->json(['success' => 'Branch added successfully.']);
    }

    public function edit(Request $request,$id){
       
        $batch = KsgBranchModel::findOrFail($id);
     
        return response()->json($batch);
        return view('admin.ksg.branch_index');
    }

    public function destroy(Request $request,$id){
       
        $batch = KsgBranchModel::find(decrypt($id));
        if (!$batch) {
          return response()->json(['error' => 'Branch not found'], 404);
        }
        // dd( $batch);
        $batch->publish = '0';
        $batch->updated_at = now();
        $batch->save();
    
        return response()->json(['success' => 'Branch deleted successfully.']);
      }

      public function update(Request $request){
        $request->validate([
          'name' => 'required',
          'updated_at'=>now(),
          ]);
  
          $id=$request->id;
     
          $batch = KsgBranchModel::find($id);
          if (!$batch) {
              return response()->json(['error' => 'Branch not found'], 404);
          }
          $batch->name = $request->name;
          $batch->updated_at = now();
          $batch->save();
      
          return response()->json(['success' => 'Branch updated successfully.']);
      }

    public function getBranch(Request $request,$id) {
        $role = Admin::select('role_id','branch_id')->where('id', $id)->first();    
           
        $roleName = Role::where('id', $role->role_id)->value('name');
        $branches = KsgBranchModel::select('name','id')
        ->where('publish', 1)
                    ->get();
        // return response()->json($branches);
        return response()->json([
            'role_name' => $roleName,
            'branches' => $branches,
            'assign-branch'=>$role->branch_id
        ]);
    }

    public function assignBranch(Request $request) {
        // dd($request);
        $validated = $request->validate([
            'userId' => 'required|integer',
            'assign_branch' => 'required|integer',
            'sign_file' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',  // Adjust file type and size as needed
        ]);
        $fileName="";
        $userId = $request->input('userId');
        $branchId = $request->input('assign_branch');

        if ($request->hasFile('sign_file')) {
            $file = $request->file('sign_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $domain = \Request::getHost();
            $subdomain = explode('.', $domain);
            $customPath = public_path() . '/' . $subdomain[0] . '/backend/canvas/images/';
            if (!file_exists($customPath)) {
                mkdir($customPath, 0777, true);
            }
            $file->move($customPath, $fileName);
            $filePath = $customPath . $fileName;
        }

        $Admin = Admin::find($userId);
        if (!$Admin) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $Admin->branch_id = $branchId;
        $Admin->updated_at = now();
        $Admin->approval_sign=$fileName;
        $Admin->save();


        return response()->json(['success' => true, 'message' => 'Branch assigned successfully.']);
    

    }
    
    

}