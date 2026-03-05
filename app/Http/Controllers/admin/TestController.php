<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\TemplateMaster;
use App\models\SuperAdmin;
use Session,TCPDF,TCPDF_FONTS,Auth,DB;
use App\Http\Requests\ExcelValidationRequest;
use App\Http\Requests\MappingDatabaseRequest;
use App\Http\Requests\TemplateMapRequest;
use App\Http\Requests\TemplateMasterRequest;
use App\Imports\TemplateMapImport;
use App\Imports\TemplateMasterImport;
use App\Jobs\PDFGenerateJob;
use App\models\BackgroundTemplateMaster;
use App\Events\BarcodeImageEvent;
use App\Events\TemplateEvent;
use App\models\FontMaster;
use App\models\FieldMaster;
use App\models\User;
use App\models\StudentTable;
use App\models\SbStudentTable;
use Maatwebsite\Excel\Facades\Excel;
use App\models\SystemConfig;
use App\Jobs\PreviewPDFGenerateJob;
use App\Exports\TemplateMasterExport;
use Storage;
use App\Library\Services\CheckUploadedFileOnAwsORLocalService;
use App\models\Config;
use App\models\PrintingDetail;
use App\models\ExcelUploadHistory;
use App\models\SbExceUploadHistory;
//use Illuminate\Support\Facades\Storage;
use App\Helpers\CoreHelper;
use Helper;
use App\models\Demo\Site as DemoSite;

use App\Models\Site;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function index(Request $request)
    {
           return true;
    }

    public function kewiTest(){
       /*$print_data=PrintingDetail::select('i')
                           ->get()
                           ->toArray();*/

         /*$student_data=StudentTable::select(['id','serial_no','certificate_filename','status'])
         ->get()
         ->toArray();*/
         //$dbName="seqr_d_kewi";
         /*$source=\Config::get('constant.directoryPathBackward')."kewi\\backend\\pdf_file\\Inactive_PDF";
         if(!is_dir($source)){
    
                        mkdir($source, 0777);
                    }

                   
         exit;*/
        // $dbName="seqr_d_mitwpu";
         // $student_data = StudentTable::where('status',1)->where('template_id',6)->whereDate('created_at', '>=','2024-10-16')->whereNotNull('bc_txn_hash')->get()
         // ->toArray();
        // $student_data=DB::connection($mysql2)->table($dbName.".student_table")->where('template_id','187')->orWhere('template_id','188')->get()
       //  ->toArray();
         //$student_data=DB::connection($mysql2)->table($dbName.".excelupload_history")->where('template_name','187')->orWhere('template_name','188')->get()
        // ->toArray();

         //$student_data = StudentTable::where('status',1)->where('serial_no', 'like', '%MEDAL_%')->whereDate('created_at', '=','2024-12-11')->where('template_id','5')->get()->toArray();
         $student_data = StudentTable::where('status',1)->where('template_type', '2')->where('template_id', '>=', '4')->whereDate('created_at', '>=','2025-08-01')->where('template_id', '<=','7')->get()->toArray();

         $i=1;
        //print_r(count($student_data));
       // exit;
         foreach ($student_data as $readData) {
      //   print_r($readData);


           // $source=\Config::get('constant.directoryPathBackward')."po\\backend\\tcpdf\\examples\\".$readData->pdf_file;
           /* $destination=\Config::get('constant.directoryPathBackward')."kewi\\backend\\tcpdf\\examples\\".$readData->pdf_file;
            \File::copy($source,$destination);*/
            // echo '<br>';
          

         /* echo $readData->id.'--------------------'.$readData->certificate_filename.'--------------------'.$readData->status;
          echo '<br>';*/

       //   if(!empty($readData['bc_txn_hash'])){


            $source=public_path()."\\mitwpu\\backend\\pdf_file\\".$readData['certificate_filename'];
            $destination=public_path()."\\mitwpu\\backend\\pdf_file\\7thConvocationBlockchain\\".$readData['certificate_filename'];

            if(file_exists($destination)){
                continue;
            }else if (file_exists($source)) {


             \File::copy($source,$destination);
            }else{
                 Log::info("No Data found on : ".$readData['certificate_filename']);
            }
            // echo "Found";
            // echo "<br>";
           // $source=\Config::get('constant.directoryPathBackward')."po\\backend\\pdf_file\\".$readData->certificate_filename;
           // $destination=\Config::get('constant.directoryPathBackward')."kewi\\backend\\pdf_file\\".$readData->certificate_filename;
            //\File::copy($source,$destination);
          
         // }else{
          
         //   $source=\Config::get('constant.directoryPathBackward')."po\\backend\\pdf_file\\Inactive_PDF\\".$readData->id."_".$readData->certificate_filename;
            //$destination=\Config::get('constant.directoryPathBackward')."kewi\\backend\\pdf_file\\Inactive_PDF\\".$i."_".$readData->certificate_filename;
            //\File::copy($source,$destination);
        //  }

          //@unlink($source);

/*
          kewi\backend\tcpdf\examples*/
          $i++;
          //exit;
         // 
         }
        // 
      exit;
      return true;
    }


     public function renewInstance(Request $request){


       // $siteData = DemoSite::select('bc_wallet_address')->where('site_url',$domain)->first();

        $siteData = DemoSite::select('site_url','end_date')->where('end_date','2025-06-19')->where('status','1')->get();
        

        foreach($siteData as $readData){
            print_r($readData->site_url);
            echo "<br>";
            $domain=$readData->site_url;
            $subdomain = explode('.', $domain);
            if($subdomain[0] == 'demo')
            {
                $dbName = 'seqr_'.$subdomain[0];
            }
            else{
                $dbName = 'seqr_d_'.$subdomain[0];
            }

           // exit;

            \DB::disconnect('mysql'); 
        \Config::set("database.connections.mysql", [
            'driver'   => 'mysql',
            'host'     => 'localhost',
            "port" => "3306",
            'database' => $dbName,
            'username' => 'developer',
            'password' => 'developer',
            "unix_socket" => "",
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "prefix_indexes" => true,
            "strict" => true,
            "engine" => null,
            "options" => []
        ]);
         \DB::reconnect();

           Site::where('site_url',$domain)->update(['end_date'=>$readData->end_date]);

            // exit;
        }
      

       
        // if (\DB::statement('create database ' . $dbName) == true) {
        // }
        // else{
        //  $dbName = 'seqr_demo';
        // }
        // dd($domain);
        

    }

}
