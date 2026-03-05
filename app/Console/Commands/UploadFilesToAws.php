<?php

namespace App\Console\Commands;
use App\Models\Demo\Site as DemoSite;
use App\models\StudentTable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Site;

class UploadFilesToAws extends Command
{
    protected $signature = 'aws:upload-files';
    protected $description = 'Upload Files to AWS Server';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('Started Upload Files To Aws Command');
        // $data = file_get_contents(public_path("demo/backend/pdf_file/Inactive_pdf/akrutidevpriya-normal-charmap_ocred.pdf"));
        
        $today = \Carbon\Carbon::today();

        // STEP 1 Fetch all sites with file storage enabled
        $sites = DemoSite::select('site_id', 'site_url', 'file_uploaded_date_time')->where('files_storage', 1)->where('new_server',1)
            // ->whereHas('students', function ($query) use ($today) {
            //     $query->where('files_storage', 0);
            //         // ->whereDate('created_at', $today->toDateString()); // Use toDateString for clarity
            // })
            ->get();
        //dd($sites);
        $s3 = \Storage::disk('s3');
        // dd($s3);
        if(!empty($sites)){
             //Log::info('Sites data found'.$sites);
            foreach ($sites as $site) {

                Log::info("Site name :".$site);
                $limit = 3000;
                $processed = 0;
                $domain = $site->site_url;
                $parts = explode('.', $domain);
                $instance = $parts[0];

                $folder_name = "public/$instance/backend/pdf_file";

                if($instance=="demo"){
                    $db_name =  "seqr_demo";
                }else{
                    $db_name =  "seqr_d_".$instance;
                }
                
                \DB::disconnect('mysql'); 
                \Config::set("database.connections.mysql", [
                    'driver'   => 'mysql',
                    'host'     => \Config::get('constant.DB_HOST'),
                    "port" => \Config::get('constant.DB_PORT'),
                    'database' => $db_name,
                    'username' => \Config::get('constant.DB_UN'),
                    'password' => \Config::get('constant.DB_PW'),
                    "unix_socket" => "",
                    "charset" => "utf8mb4",
                    "collation" => "utf8mb4_unicode_ci",
                    "prefix" => "",
                    "prefix_indexes" => true,
                    "strict" => true,
                    "engine" => null,
                    "options" => []
                ]);
                 $connection = \DB::reconnect();
                 
               //  dd($connection);
                 
                // dd($instance);
                 //print_r($site);
                if (!empty($instance)) {

                   Log::info("Instance found :".$instance);
                    // STEP 2 Logic for uploading active pdf 
                    $students = StudentTable::where('site_id', $site->site_id)->where('files_storage', 0) ->where(function ($query) {
                                                                                                                $query->where('status', '1')
                                                                                                                      ->orWhere('status', 1);
                                                                                                                })->limit($limit)->get();
                    //->where('status', 1)
                  //  dd($students);

                    if($students){
                        Log::info('Records found');
                        foreach ($students as $student) {
                            // STEP 2.1 Check if the file exists before attempting to upload
                            $localFilePath = "$instance/backend/pdf_file/$student->certificate_filename"; // Adjust the path accordingly

                            if (!file_exists($localFilePath)) {
                                Log::warning("File not found for student: {$student->id}");
                                $student->files_storage = 2;
                                $student->save();
                                continue; // Skip to the next student if file doesn't exist
                            }

                            try {
                                 // STEP 2.2  Create directory if it doesn't exist
                                if (!$s3->exists($folder_name)) {
                                    $s3->makeDirectory($folder_name, 0777);
                                }
                                // STEP 2.3 Upload the file
                                $uploaded = $s3->put("$folder_name/$student->certificate_filename", file_get_contents(public_path($localFilePath)));
                                // echo"<pre>";print_r( $uploaded);
                                if ($uploaded) {
                                    // STEP 2.3.1 if file uploaded then delete file from serve and update status in student_table
                                    Log::info("Successfully uploaded: " . $localFilePath);
                                    $student->files_storage = 1;
                                    $student->save();
                                    unlink(public_path($localFilePath));
                                }else{
                                    Log::error("Not uploaded: " . $localFilePath);
                                }
                            } catch (\Exception $e) {
                                Log::error('Error moving file to S3 for student ' . $student->id . ': ' . $e->getMessage());
                            }

                            $processed++;
                            // STEP 2.4 Check if limit reach per site 
                            if ($processed > $limit) {
                                break ; // Break out of both loops
                            }
                        }
                    }else{
                        Log::warning("Records not found for : {$instance}");
                    }

                    // // STEP 3 Logic for uploading active pdf 

                    $directory = public_path("$instance/backend/pdf_file/Inactive_pdf/");
                    // Get today's date
                    if (!empty($site->file_uploaded_date_time)) {
                        // If file_uploaded_date_time is set, use that date
                        $today = new \DateTime($site->file_uploaded_date_time);
                    } else {
                        // If it's not set, use the current date and time
                        $today = new \DateTime();
                    }

                    // STEP 3.1 Fetch all files in the directory
                    $files = glob($directory . '*');


                    // Initialize an array to hold files up to today's date
                    $limit = 50;
                    $processed = 0;
                    $filesUpToToday = [];
                    $last_file_date_time = '';
                    // STEP 3.1 Fetch all files in the directory those modified date time match 
                    foreach ($files as $file) {
                        // Check if it's a file (not a directory)
                        if (is_file($file)) {

                            // Get the modification time of the file
                            $fileModTime = new \DateTime('@' . filemtime($file));
                            $fileModTime->setTimezone(new \DateTimeZone('Asia/Kolkata')); // Set your timezone here


                            // Compare the modification time with today's date
                            if ($fileModTime <= $today) {
                                // Add the file to the result array
                                $last_file_date_time = $fileModTime;
                                $filesUpToToday[] = $file;
                                $processed++;
                            }

                            if ($processed > $limit) {
                                break;
                            }
                        }
                    }

                    // STEP 3.2 Upload All file to AWS server
                    foreach ($filesUpToToday as $file) {
                        $filePath = $file;
                        // Get the filename from the file path
                        $fileName = basename($filePath);

                        $awsInactivePDFFolder = 'public/' . $instance . '/backend/pdf_file/Inactive_PDF';
                        $inactive_pdf_path = "$instance/backend/pdf_file/Inactive_PDF/$fileName";
                        // check folder 
                        if (!$s3->exists($awsInactivePDFFolder)) {
                            $s3->makeDirectory($awsInactivePDFFolder, 0777);
                        }

                        if (!$s3->exists($inactive_pdf_path)) {
                            $uploaded = $s3->put("$awsInactivePDFFolder/$fileName", file_get_contents(public_path($inactive_pdf_path)));
                            if($uploaded){
                                Log::info("Successfully uploaded: " . $inactive_pdf_path);

                                unlink(public_path($inactive_pdf_path));
                            }
                        }

                    }

                    if (!empty($last_file_date_time)) {
                        $site->file_uploaded_date_time = $last_file_date_time;
                        $site->save();
                    }

                }else{
                     Log::info("Instance not found :".$instance);
                }


            }
        }else{
              Log::info('Sites data not found');
        }

        Log::info('Ended Upload Files To Aws Command');
    }

}