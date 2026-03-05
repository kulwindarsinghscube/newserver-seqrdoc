<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApiTracker;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApiTrakerExport;
use Mail;
use Session;
use App\Exports\TemplateMasterExport;
use App\Jobs\SendMailJob;
use Storage;
use App\models\Transactions;
use App\Models\StudentTable;
use TCPDF;
use App\Utility\GibberishAES;
use App\Helpers\CoreHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use DB;
use QrCode;
use Validator;

class BMCCFileUploadController extends Controller
{


    public function index() {
        return view('admin.bmcc_upload.index');
    }

    

    public function azureFiles(Request $request) {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        


        // Define validation rules
        $rules = [
            'upload_file_type' => 'required',
        ];
    
        // If upload_file_type is not 'all', require limit_number as a numeric field
        if ($request->upload_file_type !== 'all') {
            $rules['limit_number'] = 'required|numeric|min:1';
        }
    
        // Validate request
        $validator = Validator::make($request->all(), $rules);
        

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422); // Return validation errors with HTTP status 422
        }


        $upload_file_type = $request->upload_file_type;
        $limit_number = $request->limit_number;
        
        if($upload_file_type == 'all') {
            $studentData = StudentTable::where('azure_flag',0)->where('status',1)->orderBy('id','desc')->get();
        } else {  
            $studentData = StudentTable::where('azure_flag',0)->where('status',1)->orderBy('id','desc')->limit($limit_number)->get();
        }

        // echo "<pre>";
        // echo count($studentData);
        // echo "<br>";
        // echo "<br>";
     
        // echo "<pre>";
        if($studentData) {

            
            $uploadCount = 0;
            $notUploadCount = 0;
            foreach($studentData as $student) {
                
                $student_id = $student['id'];
                $certName = $student['certificate_filename'];
                

                if (strpos($certName, 'GC') !== false) {
                    $blob = 'BMCC\GC\\';
                } else if (strpos($certName, 'PC') !== false) {
                    $blob = 'BMCC\PC\\';
                } else {
                    $blob = 'BMCC\GC\\';
                }
                $pdfActualPath=public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;
                
                if( file_exists($pdfActualPath) ) {
                    $result = CoreHelper::uploadBlob($pdfActualPath,$blob.$certName);
                    // Update Azure Flag
                    $resultu = StudentTable::where('id',$student_id)->update(['azure_flag'=>'1']);
                    $uploadCount++;
                } else {
                    $notUploadCount++;
                }


            }

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully.',
                'count' => $uploadCount,
                'notUploadCount' => $notUploadCount
            ]);


        } else {
            return response()->json([
                'success' => false,
                'message' => 'All Data Uploaded allready.'
            ]);
        }
        

    }



    public function azureFileList(Request $request) {

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
    
        $storageAccount = trim(env('AZURE_STORAGE_NAME'));
        $containerName = trim(env('AZURE_STORAGE_CONTAINER'));
        $accessKey = trim(env('AZURE_STORAGE_KEY'));

        $currentDate = gmdate("D, d M Y H:i:s T", time());
        $version = "2019-12-12"; // Azure API version
        
        // Canonicalized resource string for authentication
        $resource = "/$storageAccount/$containerName/$blobName";
        $stringToSign = "GET\n\n\n\n\n\n\n\n\n\n\n\nx-ms-date:$currentDate\nx-ms-version:$version\n/$storageAccount/$containerName\ncomp:list\nrestype:container";


        // Generate HMAC-SHA256 signature
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($accessKey), true));
        
        $authHeader = "SharedKey $storageAccount:$signature";

        // Set request headers
        $headers = [
            "Authorization: $authHeader",
            "x-ms-date: $currentDate",
            "x-ms-version: $version"
        ];

        // Azure Blob Storage URL
        $url = "https://$storageAccount.blob.core.windows.net/$containerName?restype=container&comp=list";

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Execute cURL request
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse XML response
        $xml = simplexml_load_string($response);

        if (!$xml) {
            die("Error fetching file list from Azure.");
        }
        // Display the first file only
        foreach ($xml->Blobs->Blob as $blob) {
            $fileName = (string) $blob->Name;
            $destinationURL = "https://$storageAccount.blob.core.windows.net/$containerName/$fileName";

            echo "<a href='$destinationURL' target='_blank'>$fileName</a><br>";
            echo "<br>";
            // break; // Stop after first file
        }


    }

}