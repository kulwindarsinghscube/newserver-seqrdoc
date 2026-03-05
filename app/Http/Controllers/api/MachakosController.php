<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\models\License;
use TCPDF;
use DB;
use App\models\Admin;
use App\Jobs\PdfGenerateMachakosApiJob;
use App\Utility\MachakosApiSecurityLayer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MachakosController extends Controller
{


    public function uploadJson(Request $request)
{
    if (MachakosApiSecurityLayer::checkAuthorization()) {
        $rules = [
            'pdf_file' => 'required|file',
        ];
        $messages = [
            'pdf_file.required' => 'File is required.',
            'pdf_file.mimes' => 'File must be a JSON file.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success'=>false,
                'status' => 422,
                'message' => MachakosApiSecurityLayer::getMessage($validator->errors()),
            ], 422);
        }

        if ($request->file('pdf_file')) {
            try {
                $filePath = $request->file('pdf_file')->store('uploads');
                $jsonContent = file_get_contents(storage_path('app/' . $filePath));
                $jsonData = json_decode($jsonContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON format.');
                }

                $this->dispatch(new PdfGenerateMachakosApiJob($jsonData));
                $domain = \Request::getHost();
                $file_name = str_replace("/", "_", 'license' . date("Ymdhms")) . '.pdf';
                $Imagelink = 'https://'.$domain . '/backend/tcpdf/examples/' . $file_name;

                return response()->json([
                    'success'=>true,
                    'status' => 200,
                    'message' => "File uploaded successfully",
                    'link' => $Imagelink,
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => "An error occurred: " . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'status' => 400,
            'message' => "File upload failed",
        ], 400);
    } else {
        return response()->json([
            'success' => false,
            'status' => 403,
            'message' => 'Access forbidden.',
        ], 403);
    }
}

    
public function fetchData() {
    $data = License::select('business_name', 'pdf_file')
                   ->where('publish', 1)
                   ->get();

    if($data->count()>0){
        return response()->json([
            'status' => 200,
            'count'=>$data->count(),
            'data' => $data
            
        ], 200);
    }
   else {
        return response()->json([
            'status' => 404,
            'message' => 'No Record Found'
        ], 404);
    }

   
}
    
}
