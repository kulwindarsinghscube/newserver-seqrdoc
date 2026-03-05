<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\convocation\StudentSummary;
use App\Models\convodataverification\ConvoStudent;
use App\Models\convodataverification\ConvoStudentLog;
use App\Models\convodataverification\StudentAckLog;
use App\Models\convodataverification\StudentTransaction;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;
class SendSummaryEmails extends Command
{
    protected $signature = 'emails:send-admin';
    protected $description = 'Send email notifications to admin daily summary ';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        Log::info('Started Send Summary Email Command ');
        \DB::disconnect('mysql'); 
        \Config::set("database.connections.mysql", [
            'driver'   => 'mysql',
            'host'     => \Config::get('constant.DB_HOST'),
            "port" => \Config::get('constant.DB_PORT'),
            'database' => 'seqr_d_mitwpu',
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
         \DB::reconnect();
        $admin_email = "dev12@scube.net.in";
        $startOfToday = date('Y-m-d');

        
        $endOfToday = date('Y-m-d'); 
         // Query to get records created today
        //   $verify_count = StudentAckLog::whereBetween('created_at', [$startOfToday, $endOfToday])
        //       ->where('fn_en_status', 1)
        //       ->where('fn_hi_status', 1)
        //       ->where('mn_en_status', 1)
        //       ->where('mn_hi_status', 1)
        //       ->where('ftn_en_status', 1)
        //       ->where('ftn_hi_status', 1)
        //       ->where('cs_en_status', 1)
        //       ->where('cs_hi_status', 1)
        //       ->where('se_status', 1)
        //       ->where('is_active', 1)
        //       ->count();
        //   $payment_count = StudentTransaction::whereBetween('created_at', [$startOfToday, $endOfToday])->where('status','TXN_SUCCESS')->count();
        $statuses = [
            "have not yet signed up",
            "have completed 1st time sign up",
            "student marked few data as incorrect and admin’s action pending",
            "student acknowledge all data as correct but payment & preview pdf approval is pending",
            "student acknowledge all data as correct, Payment is completed but preview pdf approval is pending",
            "student acknowledge all data as correct, Payment is completed and preview pdf is approved",
            "admin performed correction but student’s re-acknowledgement pending",
            "student re-acknowledged new data as correct but payment & preview pdf approval is pending",
            "student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending",
            "student re-acknowledged new data as correct, Payment is completed and preview pdf is approved"
        ];
        
        // Initialize an array to hold the counts
        $counts = [];
        
        // Loop through each status and get the count
        foreach ($statuses as $status) {
            // dd($status);
            $count = ConvoStudent::where('is_printed',0)->where('status', $status)
                                  ->count();      

            $counts[$status] = $count;
        }    

        $approved = [
            "student re-acknowledged new data as correct, Payment is completed and preview pdf is approved",
            "student acknowledge all data as correct, Payment is completed and preview pdf is approved"
        ];

        $collection_mode['no_of_people_accompanied_registration_completed'] = ConvoStudent::where('is_printed',0)->whereIn('status', $approved) // Adjust 'status' to your actual field name
        ->sum('no_of_people_accompanied'); 

        $collection_mode['no_of_people_accompanied_all'] = ConvoStudent::where('is_printed',0)->sum('no_of_people_accompanied'); // This
        
        // Count the students who have the status in the approved array and collection_mode as 'Attending Convocation'
        $collection_mode['attending_convocation_registration_completed'] = ConvoStudent::where('is_printed',0)->where('collection_mode', 'Attending Convocation')
            ->whereIn('status', $approved) // Adjust 'status' to your actual field name
            ->count();

        $collection_mode['attending_convocation_all'] = ConvoStudent::where('is_printed',0)->where('collection_mode', 'Attending Convocation')->count();

        $collection_mode['by_post_india_registration_completed'] = ConvoStudent::where('is_printed',0)->where('collection_mode', 'By Post')
        ->whereIn('status', $approved)
        ->where('delivery_country',"India")
        ->count();

        $collection_mode['by_post_india_all'] = ConvoStudent::where('is_printed',0)->where('collection_mode', 'By Post')
        ->where('delivery_country',"India")
        ->count();

                                                 // Count for By Post, International
        $collection_mode['by_post_international_registration_completed'] = ConvoStudent::where('is_printed',0)->where('collection_mode', 'By Post')
        ->whereIn('status', $approved)
        ->where('delivery_country', '!=', "India") // Exclude India to get international students
        ->count();

        $collection_mode['by_post_international_all'] = ConvoStudent::where('is_printed',0)->where('collection_mode', 'By Post')
        ->where('delivery_country', '!=', "India") // Exclude India to get international students
        ->count();

        try { 
            $admin_emails = ["coe@mitwpu.edu.in","pallavi.adya@mitwpu.edu.in","dcoe.exam@mitwpu.edu.in","acoe3@mitwpu.edu.in"];
            //  Log::error('SUmmary email run'); 
            // $admin_emails = ["dev13@scube.net.in","dev12@scube.net.in"];
          
            Mail::to($admin_emails)->send(new StudentSummary($counts,$collection_mode));
            Log::info('Email sent');
        } catch (\Exception $e) {
            Log::error('Failed to send summary email : ' . $e->getMessage());
        }
        
        Log::info('Ended Send Summary Email Command ');
        $this->info('Emails sent successfully!');
    }
}