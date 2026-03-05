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
use Illuminate\Support\Facades\Mail;


class KsgBatchController extends Controller
{
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
        //->where('tbl_batch.status', '!=', 'Send For Approval')
        ->where('tbl_batch.status', '!=', 'Approved')
        //->where('tbl_batch.branch_id',$branch_id)
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
        // $totalFiltered = $query->count();
        $length = $request->get('length', 10); 
        $start = $request->get('start', 0);    
        $data = $query->skip($start) // Skip the records
            ->take($length) // Limit the number of records returned
            ->get() // Fetch data here
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

                if(\App\Helpers\SitePermissionCheck::isPermitted('delete-batch')){
                    if(\App\Helpers\RolePermissionCheck::isPermitted('delete-batch')){
                      $deleteBtn = '<a href="javascript:void(0)" data-toggle="tooltip" data-id="'.$encryptedId.'" data-original-title="Delete" class="menu-link flex-stack px-3 DeleteBatch"><i class="fa fa-trash" style="color:red; margin-right:5px; font-size:22px;"></i></a>&nbsp;&nbsp;';
                    }
                }
       
               
                if(\App\Helpers\SitePermissionCheck::isPermitted('edit-batch')){
                    if(\App\Helpers\RolePermissionCheck::isPermitted('edit-batch')){
                      $editBtn = '<a href="javascript:void(0)" title="Edit" data-id="'.$row->id.'" class="menu-link flex-stack px-3 editBatch"><i class="fa fa-edit" style="color: blue; margin-right:5px; font-size:22px;" id="ths"></i></a>&nbsp;&nbsp;';
                   }
                }
                
                if(\App\Helpers\SitePermissionCheck::isPermitted('update-batch-status')){
                    if(\App\Helpers\RolePermissionCheck::isPermitted('update-batch-status')){
                      $approveRejectBtn = '<a href="javascript:void(0)" title="Approve/Reject" data-id="'.$row->id.'" class="menu-link flex-stack px-3 approveRejectBatch" style="font-weight:normal !important;">
                      <i class="fa fa-check-circle" style="color:green;margin-right:5px; font-size:22px;"></i>
                    </a>';
                   }
                }

          
            }

            // if($row->status==="Approved"){
            //   // if(\App\Helpers\SitePermissionCheck::isPermitted('PrintBatchRecord')){
            //   //   if(\App\Helpers\RolePermissionCheck::isPermitted('PrintBatchRecord')){

            //     $printBtn = '<a href="javascript:void(0)" data-toggle="modal" data-target="#pdfModal" data-id="'.$row->id.'" title="Send For Print" style="cursor: pointer;font-weight:normal !important;" class="menu-link flex-stack px-3 printBatch">
            //         <i class="fa fa-print" style="color:red;margin-right:5px; font-size:22px;"></i>
            //       </a>';
            //   //   }
            //   // }

            // }

            $viewUrl = route('view-records', ['id' => $encryptedId, 'flag' => $approve_flag,'breadcrums'=>'batchpagepage']);
            if(\App\Helpers\SitePermissionCheck::isPermitted('view-records')){
                if(\App\Helpers\RolePermissionCheck::isPermitted('view-records')){
                $viewBtn = '<a href="'.$viewUrl.'" title="View" class="menu-link flex-stack px-3" style="font-weight: normal !important;">
                    <i class="fa fa-eye" style="color: blue;margin-right:5px; font-size:22px;"></i>
                </a>';
                }
            }
            $row->files = $row->files ? explode(',', $row->files) : [];

            if(\App\Helpers\SitePermissionCheck::isPermitted('download-document')){
                if(\App\Helpers\RolePermissionCheck::isPermitted('download-document')){
                    $fileIcon ='<a href="javascript:void(0)" title="View Documents" data-id="'.$row->id.'" class="menu-link flex-stack px-3 ViewDocuments"><i class="fa fa-file" style="color: blue; margin-right:5px; font-size:22px;" id="ths"></i></a>&nbsp;&nbsp;';
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

       return view('admin.ksg.index');

    }



    /*public function uploadpage(){
       
      // $breadcrumb='<li class="breadcrumb-item active">Customes</li><li class="breadcrumb-item active">Batch</li>';
      // // dd($breadcrumb);
      $breadcrumb='Batch';
      return view('admin.ksg.index',compact(['breadcrumb']));
    }*/

    public function uploadpage(){
       
      $id=Auth::guard('admin')->user()->id;
      $branch_id=Auth::guard('admin')->user()->branch_id;
      // dd($branch_id);

      if (!is_null($branch_id) && $branch_id != '') {
        $breadcrumb='Batch';
        return view('admin.ksg.index',compact(['breadcrumb']));

      }
      else{
        return redirect()->route('ksg-home')->with('error', 'No branch is assigned to this user.');

      }
     
    }

    public function home(Request $request){
      return view('admin.ksg.home');

    }

    public function edit(Request $request,$id){
       
      $batch = KsgbatchModel::findOrFail($id);
   
      return response()->json($batch);
      return view('admin.ksg.index');
    }
   public function destroy(Request $request,$id){
       
      $batch = KsgbatchModel::find(decrypt($id));
      if (!$batch) {
        return response()->json(['error' => 'Batch not found'], 404);
      }
      // dd( $batch);
      $batch->publish = '0';
      $batch->updated_at = now();
      $batch->save();
  
      return response()->json(['success' => 'Batch deleted successfully.']);
    }

    
    public function getBatch(Request $request) {
      $batches = KsgbatchModel::select('name','id')
      ->where('publish', 1)
                  ->where('status', 'open')
                  ->get();
  
      return response()->json($batches);
  }
  


    public function update(Request $request){
      // dd( $request->oldFiles);
      $domain = \Request::getHost();
      $subdomain = explode('.', $domain);
      $request->validate([
        'name' => 'required',
        'files' => 'nullable|mimes:pdf,doc,docx|max:2048',
        ]);

        $id=$request->id;
        $batchDoc = KsgbatchModel::select("files")->where("id", $id)->first();
        $oldBatchDoc=$batchDoc->files;
        $batch = KsgbatchModel::find($id);
        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        $fileNames = [];
        $fileNamesString="";
        // Add old files to the fileNames array
          if ($request->has('oldFiles') && is_array($request->oldFiles)) {
            $fileNames = $request->oldFiles;
          }

        if ($request->hasFile('files')) {
          foreach ($request->file('files') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $target_path = public_path().'/'.$subdomain[0].'/backend/uploads/';
            if (!file_exists($target_path)) {
              mkdir($target_path, 0777, true);
            }
          $file->move($target_path, $fileName);
          $fileNames[] = $fileName;
          }
          $fileNamesString = implode(',', $fileNames);
        }
        

        $oldBatchDocArray = explode(',', $oldBatchDoc);
        $newFileNamesArray = explode(',', $fileNamesString);
        $filesNotInNewBatch = array_diff($oldBatchDocArray, $newFileNamesArray);
          //delete privious uoploaded file from path
          foreach ($filesNotInNewBatch as $file) {
            $filePath = $target_path. trim($file); 
            if (file_exists($filePath)) {
              unlink($filePath);
            }
          }
        // dd($fileNamesString);
        $batch->name = $request->name;
        $batch->updated_at = now();
        if($fileNamesString){
          $batch->files=$fileNamesString;

        }
        $batch->save();
    
        return response()->json(['success' => 'Batch updated successfully.']);
    }


    public function store(Request $request)
    {

      $id=Auth::guard('admin')->user()->id;
      $branch_id=Auth::guard('admin')->user()->branch_id;
      // dd($branch_id);
      $domain = \Request::getHost();
      $subdomain = explode('.', $domain);
      $request->validate([
          'name' => 'required',
          // 'files' => 'nullable|mimes:pdf,doc,docx|max:2048',
      ]);

      $fileNames = [];

        if ($request->hasFile('files')) {
          foreach ($request->file('files') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $target_path = public_path().'/'.$subdomain[0].'/backend/uploads/';
            if (!file_exists($target_path)) {
              mkdir($target_path, 0777, true);
            }
          $file->move($target_path, $fileName);
          $fileNames[] = $fileName;
          
          }
        }
        $fileNamesString = implode(',', $fileNames);
    // dd($fileNamesString);

        $existingRecord = KsgbatchModel::where('name', $request->name)->first();
   
        if ($existingRecord) {
            
            return response()->json(['error' => 'This batch name is already exists.']);
        }
    
        KsgbatchModel::create([
            'name' => $request->name,
            'files'=>$fileNamesString,
            'created_by' => $id,
            'branch_id'=>$branch_id,
            'created_at' => now(),
            'updated_at'=>now(),
        ]);
        // dd($fileNamesString);
        return response()->json(['success' => 'Batch added successfully.']);
    }

    
    public function updatestatus(Request $request){
 
      if ($request->newStatus == "Approved") {
        $request->validate([
            'updateRecordId' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != session('otp_id')) {
                        $fail('The OTP is invalid. Please send otp again.');
                    }
                },
            ],
            'newStatus' => 'required',
            'otp' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != session('otp')) {
                        $fail('The OTP is incorrect.');
                    }
                },
            ],
        ]);
      } else {
        $request->validate([
            'updateRecordId' => 'required',
            'newStatus' => 'required',
        ]);
    }
    
   
      $id=$request->updateRecordId;
      $userId=Auth::guard('admin')->user()->id;
      $record = KsgbatchModel::find($id);
      if (!$record) {
          return response()->json(['error' => 'Record not found']);
      }
      $record->comment = $request->comment;
      $record->status = $request->newStatus;
      $record->updated_at = now();
      $record->approvar_id=$userId;
      $record->save();


      DB::table('tbl_action_log')->insert([
        'br_id' => $id,
        'created_by' => $userId,
        'br_flag' => 0,
        'status' =>$request->newStatus,
        'comment' => $request->comment,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
      Session::forget('otp');
      Session::forget('otp_id');
      return response()->json(['success' => 'Record updated successfully.']);


  }



    // new
   
    public function downloadDocument(Request $request)
    {
      // dd( $request->all());
        $domain = $request->getHost();
        $subdomain = explode('.', $domain);
        $fileName = $request->input('fileName'); 
        $filePath = public_path($subdomain[0] . '/backend/uploads/' . $fileName);
    
        if (file_exists($filePath)) {
            return response()->file($filePath, [
                'Content-Type' => mime_content_type($filePath),
                'Content-Disposition' => 'attachment; filename="'.basename($filePath).'"',
            ]);
        } else {
            return response()->json(['error' => 'File not found.'], 404);
        }
    }
    

    public function viewpage(Request $request){
      $id=Auth::guard('admin')->user()->id;
      $branch_id=Auth::guard('admin')->user()->branch_id;
      
      if (!is_null($branch_id) && $branch_id != '') {
        return view('admin.ksg.approvalview');

      }
      else{
        return redirect()->route('ksg-home')->with('error', 'No branch is assigned to this user.');

      }

      // return view('admin.ksg.approvalview');
    }
    

    public function sendOtp(Request $request){
      // dd($request->query('name'));

      $batch=$request->query('name');
      $id=$request->query('id');
      $otp = rand(100000, 999999);
      $email =Auth::guard('admin')->user()->email;
      $name =Auth::guard('admin')->user()->username;
   

      // Store OTP in session (or database)
      Session::put('otp', $otp);
      Session::put('otp_id', $id);

      // Send mail
      // Mail::raw("Your OTP is: $otp", function ($message) use ($email) {
      //     $message->to($email)
      //             ->subject('Your OTP Code');
      // });

      // Mail::send('otpMail', $data, function ($message) use ($data) {
      //   $message->to($email)
      //       ->subject($data["title"]);
      //  });

        $body = "
        Dear $name,<br><br>
    
        You are attempting to approve a status of <strong>$batch</strong> batch in the system. Please use the following One-Time Password (OTP) to proceed:<br><br>
    
        <b>OTP: $otp</b><br><br>
    
      
    
        Thank you,<br>
        SeQR Doc
        ";
        
        Mail::send([], [], function ($message) use ($email, $body) {
            $message->to($email)
                    ->subject('OTP for Batch Approval')
                    ->setBody($body, 'text/html'); 
        });
        


      return response()->json(['success' => true, 'message' => 'OTP sent to email']);
  

    }



 
    public function getApprovalBatchData(Request $request){

      if ($request->ajax()) 
      {
    
          $approve_flag = $request->input('approve_flag');
          $branch_id=Auth::guard('admin')->user()->branch_id;
            $admin = Auth::guard('admin')->user();
            $roleName = $admin->getRoleName();
          // Build the initial query
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
          DB::raw("DATE_FORMAT(tbl_batch.created_at, '%d-%m-%Y') as formatted_date"),
          DB::raw("SUM(CASE WHEN tbl_batch_records.status = 'Pending' THEN 1 ELSE 0 END) as Pending"),
          DB::raw("SUM(CASE WHEN tbl_batch_records.status = 'Approved' THEN 1 ELSE 0 END) as Approved"),
          DB::raw("SUM(CASE WHEN tbl_batch_records.status = 'Correction' THEN 1 ELSE 0 END) as Correction"),
          DB::raw("SUM(CASE WHEN tbl_batch_records.status = 'Rejected' THEN 1 ELSE 0 END) as Rejected")
          )
        ->where('tbl_batch.publish', 1)
        //->where('tbl_batch.branch_id',$branch_id)
        ->where('tbl_batch.status', "Send For Approval")
        ->leftJoin('admin_table', 'tbl_batch.created_by', '=', 'admin_table.id')
        ->leftJoin('tbl_batch_records', 'tbl_batch.id', '=', 'tbl_batch_records.batch_id')
        ->groupBy('tbl_batch.id', 'admin_table.fullname', 'tbl_batch.created_at')
        ->orderBy('tbl_batch.id', 'desc');


        if ($roleName!="Admin") {
            $query->where('tbl_batch.branch_id', $branch_id);
        }

        $result_count = count($query->get()->toArray()); 
        $totalRecords = $result_count;

      // Apply search filter
      $searchValue = $request->get('search')['value'];
      if ($searchValue) {
          $query->where(function ($q) use ($searchValue) {
              $q->where('tbl_batch.name', 'like', "%{$searchValue}%")
                ->orWhere('admin_table.fullname', 'like', "%{$searchValue}%")
                ->orWhere('tbl_batch.status', 'like', "%{$searchValue}%");
          });
      }

      // $result_count = count($query->get()->toArray());
    $totalFiltered =  count($query->get()->toArray());

    // Handle pagination
    $length = $request->get('length', 10); // Default length to 10 if not specified
    $start = $request->get('start', 0);    // Default start to 0 if not specified

    // Fetch the paginated data
    $data = $query->skip($start) // Skip the records
        ->take($length) // Limit the number of records returned
        ->get() // Fetch the data here
        ->map(function ($item) {
            // Format status counts
            $item->status_counts = 
                "Approved: {$item->Approved}, " .
                "Pending: {$item->Pending}, " .
                "Correction: {$item->Correction}, " .
                "Rejected: {$item->Rejected}";
            return $item;
        });

    // Prepare the result with action buttons
    $result = [];
    foreach ($data as $key => $row) {
        // Add index column
        $row->DT_RowIndex = $start + $key + 1;
        
        // Prepare action buttons
        $printBtn = "";
        $deleteBtn = "";
        $approveRejectBtn = "";
        $editBtn = "";
        
        // Create encrypted ID for action buttons
        $encryptedId = encrypt($row->id);
        
        // Add buttons conditionally
        if ( $approve_flag==0) {
            if(\App\Helpers\SitePermissionCheck::isPermitted('delete-batch')){
                if(\App\Helpers\RolePermissionCheck::isPermitted('delete-batch')){
                    $deleteBtn = '<a href="javascript:void(0)" data-toggle="tooltip" data-id="'.$encryptedId.'" data-original-title="Delete" class="menu-link flex-stack px-3 DeleteBatch"><i class="fa fa-trash" style="color:red; margin-right:5px; font-size:22px;"></i></a>&nbsp;&nbsp;';  
                }
            }            
            if(\App\Helpers\SitePermissionCheck::isPermitted('edit-batch')){
                if(\App\Helpers\RolePermissionCheck::isPermitted('edit-batch')){
                    $editBtn = '<a href="javascript:void(0)" title="Edit" data-id="'.$row->id.'" class="menu-link flex-stack px-3 editBatch"><i class="fa fa-edit" style="color: blue; margin-right:5px; font-size:22px;" id="ths"></i></a>&nbsp;&nbsp;';
                }
            } 
        }
        if(\App\Helpers\SitePermissionCheck::isPermitted('update-batch-status')){
            if(\App\Helpers\RolePermissionCheck::isPermitted('update-batch-status')){
                $approveRejectBtn = '<a href="javascript:void(0)" title="Approve/Reject" data-id="'.$row->id.'" class="menu-link flex-stack px-3 approveRejectBatch" style="font-weight:normal !important;"><i class="fa fa-check-circle" style="color:green;margin-right:5px; font-size:22px;"></i></a>';
            }
        }  
  
        $viewUrl = route('view-records', ['id' => $encryptedId, 'flag' => $approve_flag,'breadcrums'=>'approvalpage']);
        //  dd($viewUrl);
        if(\App\Helpers\SitePermissionCheck::isPermitted('view-records')){
            if(\App\Helpers\RolePermissionCheck::isPermitted('view-records')){
                $viewBtn = '<a href="'.$viewUrl.'" title="View" class="menu-link flex-stack px-3" style="font-weight: normal !important;">
                <i class="fa fa-eye" style="color: blue;margin-right:5px; font-size:22px;"></i></a>';
            }
        }
        
        if(\App\Helpers\SitePermissionCheck::isPermitted('download-document')){
            if(\App\Helpers\RolePermissionCheck::isPermitted('download-document')){
                $fileIcon ='<a href="javascript:void(0)" title="View Documents" data-id="'.$row->id.'" class="menu-link flex-stack px-3 ViewDocuments"><i class="fa fa-file" style="color: blue; margin-right:5px; font-size:22px;" id="ths"></i></a>&nbsp;&nbsp;';
            }
        }                  
 

        // Combine action buttons
        $row->action = $editBtn . $deleteBtn . $approveRejectBtn. $viewBtn. $fileIcon;

        // Append the row to the result array
        $result[] = $row;
        }

        // Return the data in JSON format
        return response()->json([
            'draw' => intval($request->get('draw')), // Echo the draw parameter
            'recordsTotal' => $totalRecords,         // Total records before filtering
            'recordsFiltered' => $totalFiltered,     // Total records after filtering
            'data' => $result                        // Your data
        ]);    

  }

       

 }













  
}
