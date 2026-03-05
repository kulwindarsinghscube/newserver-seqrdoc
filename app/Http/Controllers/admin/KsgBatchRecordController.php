<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\TemplateMaster;
use App\models\KsgbatchModel;
use Session,TCPDF,TCPDF_FONTS,Auth,DB;
use Storage;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\CoreHelper;
use Helper;
use App\models\SystemConfig;
use App\models\KsgBtatchRecordModel;
use App\Jobs\ValidateExcelKsgUniversityJob;
use App\Jobs\PdfGenerateKsgJob;
use App\Exports\BatchRecordsExport;
use Maatwebsite\Excel\Facades\Excel;
class KsgBatchRecordController extends Controller
{

    public function view(Request $request,$id,$flag,$breadcrums)
    {
        $decryptedId = $id;
        $flag=$flag;
        $breadcrums=$breadcrums;
        $batch = KsgbatchModel::select('name', DB::raw("DATE(created_at) as created_date")) 
        ->where('id', decrypt($decryptedId))
        ->first();
        $id=decrypt($decryptedId);
        return view('admin.ksg.viewrecords',compact('decryptedId','batch','flag','breadcrums','id'));
    }

    public function index(Request $request)
    {
   
      if ($request->ajax()) 
      {
          $batch_id = decrypt($request->input('batch_id'));
          $approve_flag = $request->input('approve_flag');

          $query = KsgBtatchRecordModel::select(
              'tbl_batch_records.*', 
              'tbl_batch.status as batch_status',
              'admin_table.fullname as admin_name',
              DB::raw("DATE_FORMAT(tbl_batch_records.created_at, '%d-%m-%Y') as formatted_date")
          )
          ->where('tbl_batch_records.publish', 1)
          ->join('admin_table', 'tbl_batch_records.created_by', '=', 'admin_table.id')
          ->join('tbl_batch', 'tbl_batch_records.batch_id', '=', 'tbl_batch.id')
          ->where('tbl_batch_records.batch_id', $batch_id)
          ->orderBy('tbl_batch_records.id', 'desc');

          $totalRecords = $query->count();

          // Apply search filter
          $searchValue = $request->get('search')['value'];
          if ($searchValue) {
              $query->where(function ($q) use ($searchValue) {
                  $q->where('tbl_batch_records.name', 'like', "%{$searchValue}%")
                    ->orWhere('admin_table.fullname', 'like', "%{$searchValue}%")
                    ->orWhere('tbl_batch_records.status', 'like', "%{$searchValue}%")
                    ->orWhere('tbl_batch_records.unique_id_no', 'like', "%{$searchValue}%")
                    ->orWhere('tbl_batch_records.id_type', 'like', "%{$searchValue}%");
              });
          }

          // Get the total number of records after filtering
          $totalFiltered = $query->count();

          // Handle pagination
          $length = $request->get('length', 10);
          $start = $request->get('start', 0);    

          // Get paginated data
          $data = $query->skip($start) // Skip the records
              ->take($length) // Limit the number of records returned
              ->get(); // Fetch the data here

          
          $result = [];
         
          foreach ($data as $key => $row) {

            if ($approve_flag == 1 && $row->status == "Pending") {
            $row->checkbox = '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
            }else{
              $row->checkbox="";
            }
              $row->DT_RowIndex = $start + $key + 1;

              $printBtn = "";
              $deleteBtn = "";
              $approveRejectBtn = "";
              $editBtn = "";

              $encryptedId = encrypt($row->id);
                $editUrl = route('edit-batch', ['id' => $encryptedId]);

              //  dd($approve_flag.":".$row->batch_status.":".$row->status);
                $approveRejectBtn = '<a href="javascript:void(0)" title="Approve/Reject" data-id="'.$row->id.'" class="menu-link flex-stack px-3 approveRejectBatch" style="font-weight:normal !important;">
                <i class="fa fa-check-circle" style="color:green; margin-right:5px; font-size:22px;"></i>
                  </a>';

                $deleteBtn = '<a href="javascript:void(0)" data-toggle="tooltip" data-id="'.$encryptedId.'" title="Delete" class="menu-link flex-stack px-3 DeleteBatch" style="cursor: pointer; font-weight:normal !important;">
                          <i class="fa fa-trash" style="color:red; margin-right:5px; font-size:22px;"></i>
                        </a>';

                $editBtn = '<a href="javascript:void(0)" title="Edit" data-id="'.$row->id.'" class="menu-link flex-stack px-3 editBatch" style="font-weight:normal !important;">
                        <i class="fa fa-edit" style="margin-right:5px; font-size:22px;"></i>
                      </a>';

                $printBtn = '<a href="javascript:void(0)" data-toggle="modal" data-target="#pdfModal" data-id="'.$row->id.'" data-flag="record" title="Send For Print" style="cursor: pointer;font-weight:normal !important;" class="menu-link flex-stack px-3 printBatch">
                      <i class="fa fa-print" style="color:red;margin-right:5px; font-size:22px;"></i>
                    </a>';

                if ($approve_flag == 1 && $row->status == "Pending"||$row->status == "Correction") {
                  $actionBtn = $approveRejectBtn;
                }
                 elseif ($approve_flag == 0 && 
                    ($row->status == "Pending" || $row->status == "Correction" )) {
                
                     $actionBtn = $editBtn . $approveRejectBtn;
                } elseif ($approve_flag == 0 && $row->batch_status == "Send For Approval" && $row->status == "Correction") {

                $actionBtn =  $editBtn . $approveRejectBtn ;
                }elseif ($approve_flag == 0 && $row->batch_status == "Approved" && $row->status == "Approved") {

                  $actionBtn =  "";
                }
                elseif($approve_flag == 2 && $row->status  == "Approved" && $row->fees=="Paid"  ){
                  $actionBtn =  "$printBtn";

                }


                  else{
                    $actionBtn =  "";

                  }
              $row->action = $actionBtn  ;
              $result[] = $row;
              }

              
              return response()->json([
                  'draw' => intval($request->get('draw')), 
                  'recordsTotal' => $totalRecords,          
                  'recordsFiltered' => $totalFiltered,      
                  'data' => $result                         
              ]);

       

      }

       return view('admin.ksg.index');

    }


    public function uploadfile(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
      ini_set('memory_limit', '4096M');   
      $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
      $template_id="ksg";
      $batch_id=$request->input("batch_id");
    //   dd($batch_id);
      $domain = \Request::getHost();
      $subdomain = explode('.', $domain);
      if($request->hasFile('excel_file')){
          $file_name = $request['excel_file']->getClientOriginalName();
          $ext = pathinfo($file_name, PATHINFO_EXTENSION);
          $excelfile =  date("YmdHis") . "_" . $file_name;
          $target_path = public_path().'/backend/canvas/dummy_images/'.$template_id;
          $fullpath = $target_path.'/'.$excelfile;

          if(!is_dir($target_path)){
              
                mkdir($target_path, 0777);
             }

          if($request['excel_file']->move($target_path,$excelfile)){
              //get excel file data
              
              if($ext == 'xlsx' || $ext == 'Xlsx'){
                  $inputFileType = 'Xlsx';
              }
              else{
                  $inputFileType = 'Xls';
              }
              $auth_site_id=Auth::guard('admin')->user()->site_id;

              $systemConfig = SystemConfig::select('sandboxing')->where('site_id',$auth_site_id)->first();
              if($get_file_aws_local_flag->file_aws_local == '1'){
                  if($systemConfig['sandboxing'] == 1){
                      $sandbox_directory = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/';
                      //if directory not exist make directory
                      if(!is_dir($sandbox_directory)){
              
                          mkdir($sandbox_directory, 0777);
                      }

                      $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
                      $filename1 = \Storage::disk('s3')->url($excelfile);
                      
                  }else{
                      
                      $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
                      $filename1 = \Storage::disk('s3')->url($excelfile);
                  }
              }
              else{

                    $sandbox_directory = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/';
                  //if directory not exist make directory
                  if(!is_dir($sandbox_directory)){
          
                      mkdir($sandbox_directory, 0777);
                  }

                  if($systemConfig['sandboxing'] == 1){

                      $aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile);
                      
                  }else{
                      
                      $aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile);
                      
                  }

                  
              }

                  $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                  /**  Load $inputFileName to a Spreadsheet Object  **/
                  $objPHPExcel1 = $objReader->load($fullpath);
                  $sheet1 = $objPHPExcel1->getSheet(0);
                  $highestColumn1 = $sheet1->getHighestColumn();
                  $highestRow1 = $sheet1->getHighestDataRow();
                  $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
                  $recordToGenerate=$highestRow1-1;
                  $checkStatus = CoreHelper::checkMaxCertificateLimit($recordToGenerate);
                  if(!$checkStatus['status']){
                    return response()->json($checkStatus);
                  }
                   $excelData=array('rowData1'=>$rowData1,'auth_site_id'=>$auth_site_id);
                  $response = $this->dispatch(new ValidateExcelKsgUniversityJob($excelData));
                  // dd($response);
                  $responseData =$response->getData();
                    //   dd($responseData->duplicate_uniqueno);
              if($responseData->success){
                $id=Auth::guard('admin')->user()->id;
                //   $old_rows=$responseData->old_rows;
                //   $new_rows=$responseData->new_rows;
                  if (is_null($batch_id)) 
                  {
                    $highestId = KsgbatchModel::max('id');
                    $batchName = "Batch_" . ($highestId + 1);
                   
                    $batch = new KsgbatchModel();
                    $batch->name = $batchName;
                    $batch->created_by = $id;
                    $batch->created_at=now();
                    $batch->updated_at=now();
                    $batch->save();
                     $batch_id=$batch->id;
                  }
                //   foreach ($rowData1 as $key => $row)
                //    {
                //         if ($key === 0) continue;
            
                //         KsgBtatchRecordModel::create([
                //             'batch_id' => $batch_id, 
                //             'usn' => $row[0],
                //             'name' => $row[1],
                //             'course' => $row[2],
                //             'course_date' => $row[3],
                //             'course_month' => $row[4],
                //             'created_at' => now(),
                //             'updated_at' => now(),
                //             'created_by'=>$id,
                //         ]);
                //     }
                   
                    $duplicateUsnArray = explode(',', $responseData->duplicate_uniqueno);

                    foreach ($rowData1 as $key => $row) {
                        if ($key === 0) continue; 

                        $usn = $row[0];

                        if (in_array($usn, $duplicateUsnArray)) {
                            KsgBtatchRecordModel::where('usn', $usn)->update([
                                'batch_id' => $batch_id,
                                'name' => $row[1],
                                'course' => $row[2],
                                'course_2' => $row[3],
                                'course_date' => $row[4],
                                'course_month' => $row[5],
                                'fees'=>$row[6],
                                'unique_id_no'=>$row[7],
                                'id_type'=>$row[8],
                                'credit'=>$row[9],
                                'updated_at' => now(),
                                'created_by' => $id,
                            ]);
                        } else {
                            KsgBtatchRecordModel::create([
                                'batch_id' => $batch_id,
                                'usn' => $usn,
                                'name' => $row[1],
                                'course' => $row[2],
                                'course_2' => $row[3],
                                'course_date' => $row[4],
                                'course_month' => $row[5],
                                'fees'=>$row[6],
                                'unique_id_no'=>$row[7],
                                'id_type'=>$row[8],
                                'credit'=>$row[9],    
                                'created_at' => now(),
                                'updated_at' => now(),
                                'created_by' => $id,
                            ]);
                        }
                    }




              }else{
                 return $response;
              }
             
          }      
          if (file_exists($fullpath)) {
              unlink($fullpath);
          }
          



      return response()->json(['success'=>true,'type' => 'success', 'message' => 'File uploaded successfully','old_rows'=>$old_rows,'new_rows'=>$new_rows,'duplicate_uniqueno'=>$responseData->duplicate_uniqueno]);

      }
      else{
          return response()->json(['success'=>false,'message'=>'File not found!']);
      }

  }



  public function store(Request $request)
    {
    //  dd($request->unique_id_no);
      $id=Auth::guard('admin')->user()->id;
        $request->validate([
            'usn' => 'required',
            'studentName' => 'required',
            'course' => 'required',
            'date' => 'required',
            'month' => 'required',
            'batch_id' => 'required',   
            'fees_status' => 'required', 
            'unique_id_no'=>'required',
            'id_type'=>'required',
            'course_2' => 'required',

        ]);

        $existingRecord = KsgBtatchRecordModel::where('usn', $request->usn)->first();
    
        if ($existingRecord) {
            
            return response()->json(['error' => 'This Unique sr. no. already exists.']);
        }

        KsgBtatchRecordModel::create([
            'batch_id'=>decrypt($request->batch_id),
           'usn' => $request->usn,
            'name'=>$request->studentName,
            'course'=>$request->course,
            'course_date'=>$request->date,
            'course_month'=>$request->month,
            'fees'=>$request->fees_status,
            'unique_id_no'=>$request->unique_id_no,
            'id_type'=>$request->id_type,
            'credit'=>$request->credit,
            'created_by' => $id,
            'course_2'=>$request->course_2,
            'created_at' => now(),
            'updated_at'=>now(),
        ]);

        return response()->json(['success' => 'Record added successfully.']);
    }
    
    public function update(Request $request){
        // dd($request);
        $request->validate([
            'usn' => 'required',
            'studentName' => 'required',
            'course' => 'required',
            'date' => 'required',
            'month' => 'required',
            'batch_id' => 'required', 
            'fees_status' => 'required', 
            'unique_id_no'=>'required',
            'id_type'=>'required',
          ]);
  
          $id=$request->id;
     
          $record = KsgBtatchRecordModel::find($id);
          if (!$record) {
              return response()->json(['error' => 'Record not found'], 404);
          }
          $record->batch_id = decrypt($request->batch_id);
          $record->usn = $request->usn;
          $record->name = $request->studentName;
          $record->course = $request->course;
          $record->course_date = $request->date;
          $record->course_month = $request->month;
          $record->fees=$request->fees_status;
          $record->unique_id_no=$request->unique_id_no;
          $record->id_type=$request->id_type;
          $record->credit=$request->credit;
          $record->course_2 = $request->course_2;
          $record->updated_at = now();
          $record->save();
      
          return response()->json(['success' => 'Record updated successfully.']);
      }


      public function edit(Request $request,$id){
       
        $record = KsgBtatchRecordModel::findOrFail($id);
     
        return response()->json($record);
        // return view('admin.ksg.index');
      }


      public function destroy(Request $request,$id){
       
        $record = KsgBtatchRecordModel::find(decrypt($id));
        if (!$record) {
          return response()->json(['error' => 'Record not found'], 404);
        }
       
        $record->publish = '0';
        $record->updated_at = now();
        $record->save();
    
        return response()->json(['success' => 'Record deleted successfully.']);
      }

      public function updatestatus(Request $request){
        $request->validate([
          'updateRecordId' => 'required',
          'newStatus' => 'required', 
        ]);
        $id=$request->updateRecordId;
        $userId=Auth::guard('admin')->user()->id;
        $record = KsgBtatchRecordModel::find($id);
        if (!$record) {
            return response()->json(['error' => 'Record not found']);
        }
        $record->comment = $request->comment;
        $record->status = $request->newStatus;
        $record->updated_at = now();
        $record->approval_id =$userId;
        $record->save();

        
        DB::table('tbl_action_log')->insert([
          'br_id' => $id,
          'created_by' => $userId,
          'br_flag' => 1,
          'status' =>$request->newStatus,
          'comment' => $request->comment,
          'created_at' => now(),
          'updated_at' => now(),
      ]);

      // $hasPendingOrRejected = KsgBtatchRecordModel::where('batch_id', $record->batch_id)
      // ->whereNotIn('status', ['Pending', 'Correction'])
      //   ->exists();

      //   if ($hasPendingOrRejected) {
      //     $userId=Auth::guard('admin')->user()->id;
      //     $batch = KsgbatchModel::find($record->batch_id);
      //     if (!$batch) {
      //         return response()->json(['error' => 'Batch not found']);
      //     }
          
      //     $batch->status = "Approved";
      //     $batch->updated_at = now();
      //     $batch->approvar_id=$userId;
      //     $batch->save();
    
    
      //     DB::table('tbl_action_log')->insert([
      //       'br_id' => $record->batch_id,
      //       'created_by' => $userId,
      //       'br_flag' => 0,
      //       'status' =>"Approved",
      //       'created_at' => now(),
      //       'updated_at' => now(),
      //   ]);
         
      //   }
        return response()->json(['success' => 'Record updated successfully.']);


      }

      public function loderfile(Request $request,$flag){
        $batch_id=$request->batchIdtogenerate;
        $flag=$request->flag;
       
         if($flag=="batch"){
          $records = KsgBtatchRecordModel::select('usn','name','course','course_date','course_month')
          ->where('batch_id', $batch_id)
           ->where('status', 'approved')
           ->where('fees', 'Paid')
           ->get()
           ->toArray();
            $recordToGenerate=count($records);
            // dd( $records );
         }
         else if($flag=="record"){
          $records = KsgBtatchRecordModel::select('usn','name','course','course_date','course_month')
          ->where('id', $batch_id)
           ->where('status', 'approved')
           ->where('fees', 'Paid')
           ->get()
           ->toArray();
            $recordToGenerate=count($records);

         }
      

      // dd($recordToGenerate);
          $randstr=CoreHelper::genRandomStr(5);
          $jsonArr=array();
          $jsonArr['token'] = $randstr.'_'.time();
          $jsonArr['status'] ='200';
          $jsonArr['message'] ='Pdf generation started...';
          $jsonArr['recordsToGenerate'] =$recordToGenerate;
          $jsonArr['generatedCertificates'] =0;
          $jsonArr['pendingCertificates'] =$recordToGenerate;
          $jsonArr['timePerCertificate'] =0;
          $jsonArr['isGenerationCompleted'] =0;
          $jsonArr['totalSecondsForGeneration'] =0;
          $loaderData=CoreHelper::createLoaderJson($jsonArr,1);

          return response()->json(['success'=>true,'type' => 'success', 'loaderFile'=>$loaderData['fileName'],'loader_token'=>$loaderData['loader_token']]);
      }

      public function BatchRecordToPrintold(Request $request,CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal){
        // dd($request->all());
        $loader_token=$request['loader_token'];
        $batch_id=$request->batchIdtogenerate;
        $flag=$request->flag;
        $pdftype=$request->selecttype;
        ini_set('memory_limit', '4096M');
       
            $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
    
            $domain = \Request::getHost();
            $subdomain = explode('.', $domain);
            
            //For custom loader
            // $loader_token=$request['loader_token'];
            $auth_site_id=Auth::guard('admin')->user()->site_id;
            $admin_id = \Auth::guard('admin')->user()->toArray();
            if($flag=="batch"){
              // $records = KsgBtatchRecordModel::select('usn','name','course','course_date','course_month')
              // ->where('batch_id', $batch_id)
              //  ->where('status', 'Approved')
              //  ->where('fees', 'Paid')
              //  ->get()
              //  ->toArray();

              $records = KsgBtatchRecordModel::select(
                'tbl_batch_records.usn',
                'tbl_batch_records.name',
                'tbl_batch_records.course',
                'tbl_batch_records.course_date',
                'tbl_batch_records.course_month',
                'tbl_batch_records.batch_id',
                'admin_table.approval_sign' 
           
            )
            ->join('admin_table', 'tbl_batch_records.approval_id', '=', 'admin_table.id')
            ->where('tbl_batch_records.batch_id', $batch_id)
            ->where('tbl_batch_records.status', 'Approved')
            ->where('tbl_batch_records.fees', 'Paid')
            ->get()
            ->toArray();
              
            }
            else if($flag=="record"){
              $records = KsgBtatchRecordModel::select(
                'tbl_batch_records.usn',
                'tbl_batch_records.name',
                'tbl_batch_records.course',
                'tbl_batch_records.course_date',
                'tbl_batch_records.course_month',
                'tbl_batch_records.batch_id',
                'admin_table.approval_sign'
           
            )
            ->join('admin_table', 'tbl_batch_records.approval_id', '=', 'admin_table.id')
            ->where('tbl_batch_records.id', $batch_id)
            ->where('tbl_batch_records.status', 'Approved')
            ->where('tbl_batch_records.fees', 'Paid')
            ->get()
            ->toArray();
        

            }
          
         $batch_name = KsgbatchModel::where('id', $batch_id)->value('name');
         $temp_id=TemplateMaster::where('template_name',$batch_name)->value('id');
          
     

        $pdfData=array('studentDataOrg'=>$records,'auth_site_id'=>$auth_site_id,'template_id'=>$template_id,'previewPdf'=>$pdftype,'excelfile'=>$loaderData['fileName'],'loader_token'=>$loader_token,'image_path'=> $temp_id);

          
        $link = $this->dispatch(new PdfGenerateKsgJob($pdfData));
        
        return response()->json(['success'=>true,'message'=>'Certificates generated successfully.','link'=>$link]);

      }

      public function BatchRecordToPrint(Request $request, CheckUploadedFileOnAwsORLocalService $checkUploadedFileOnAwsOrLocal)
      {

          $loader_token = $request['loader_token'];
          $batch_id     = $request->batchIdtogenerate;
          $flag         = $request->flag;
          $pdftype      = $request->selecttype;
          ini_set('memory_limit', '4096M');

          $get_file_aws_local_flag = $checkUploadedFileOnAwsOrLocal->checkUploadedFileOnAwsORLocal();
          $domain      = \Request::getHost();
          $subdomain   = explode('.', $domain);
          $auth_site_id= Auth::guard('admin')->user()->site_id;
          $admin_id    = \Auth::guard('admin')->user()->toArray();

          // --- Batchwise setup ---
          $batchSize = 1; 
          $startRow  = (int) $request->get('startRow', 1);
          $endRow    = $startRow + $batchSize - 1;

          // --- Base query ---
          $query = KsgBtatchRecordModel::select(
                      'tbl_batch_records.usn',
                      'tbl_batch_records.name',
                      'tbl_batch_records.course',
                      'tbl_batch_records.course_date',
                      'tbl_batch_records.course_month',
                      'tbl_batch_records.batch_id',
                      'tbl_batch_records.credit',
                      'admin_table.approval_sign'
                  )
                  ->join('admin_table', 'tbl_batch_records.approval_id', '=', 'admin_table.id')
                  ->where('tbl_batch_records.status', 'Approved')
                  ->where('tbl_batch_records.fees', 'Paid');

          if ($flag == "batch") {
              $query->where('tbl_batch_records.batch_id', $batch_id);
          } elseif ($flag == "record") {
              $query->where('tbl_batch_records.id', $batch_id);
          }

          $highestRow = $query->count(); 
          // --- Fetch only current batch ---
          $records = $query->skip($startRow - 1)
                          ->take($batchSize)
                          ->get()
                          ->toArray();

          // if (empty($records)) {
          //     return response()->json([
          //         'success' => false,
          //         'message' => 'No records found for processing.'
          //     ]);
          // }
          $batch_name = KsgbatchModel::where('id', $batch_id)->value('name');
          $temp_id    = TemplateMaster::where('template_name', $batch_name)->value('id');

          $pdfData = [
              'studentDataOrg'   => $records,
              'auth_site_id'     => $auth_site_id,
              'template_id'      => 100, // fixed template id
              'previewPdf'       => $pdftype,
              // 'excelfile'=>$loaderData['fileName'], 
              'loader_token'     => $loader_token,
              'image_path'       => $temp_id,
              'highestrow'       => $highestRow
          ];
          $link = $this->dispatch(new PdfGenerateKsgJob($pdfData));

          return response()->json([
              'success'    => true,
              'message'    => 'Certificates generated successfully.',
              'link'       => $link,
              'startRow'   => $startRow,
              'endRow'     => $endRow,
              'highestRow' => $highestRow
          ]);
      }


      public function approveSelected(Request $request)
      {
    // dd($request);
          $selectedIds = $request->input('selected_ids');
          if(empty($selectedIds)) {
              return response()->json(['error' => 'No records selected'], 400);
          }
          $userId=Auth::guard('admin')->user()->id;
          $updatedRows = DB::table('tbl_batch_records') 
              ->whereIn('id', $selectedIds)  
              ->update(['status' => 'Approved','approval_id'=> $userId]);

             
              foreach ($selectedIds as $id) {
              DB::table('tbl_action_log')->insert([
                'br_id' => $id,
                'created_by' => $userId,
                'br_flag' => 1,
                'status' =>"Approved",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $batchId = DB::table('tbl_batch_records')
              ->where('id', $selectedIds[0])
              ->value('batch_id');
              if (!$batchId) {
                return response()->json(['error' => 'Batch not found for the selected records'], 400);
            }

            // $hasPendingOrCorrection = DB::table('tbl_batch_records')
            // ->where('batch_id', $batchId)
            // ->whereNotIn('status', ['Pending', 'Correction'])
            // ->exists();
            // $sql =  $hasPendingOrCorrection->toSql();
         

            // if($hasPendingOrCorrection){
            //   $userId=Auth::guard('admin')->user()->id;
            //   $batch = KsgbatchModel::find( $batchId);
            //   if (!$batch) {
            //       return response()->json(['error' => 'Batch not found']);
            //   }
              
            //   $batch->status = "Approved";
            //   $batch->updated_at = now();
            //   $batch->approvar_id=$userId;
            //   $batch->save();
        
        
            //   DB::table('tbl_action_log')->insert([
            //     'br_id' =>  $batchId,
            //     'created_by' => $userId,
            //     'br_flag' => 0,
            //     'status' =>"Approved",
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);

            // }

          }
      
          if ($updatedRows > 0) {
              return response()->json(['success' => 'Records approved successfully'], 200);
          } else {
              return response()->json(['error' => 'Failed to update records'], 500);
          }
      }
        
      public function exportRecords(Request $request,$id)
      {      
        if (!$id) {
            return redirect()->back()->with('error', 'ID is required.');
        }
        $batch =KsgbatchModel::where('id', $id)->value('name');
        $fileName = $batch . '_records.xlsx';
        return Excel::download(new BatchRecordsExport($id), $fileName);

      }

}