<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\convocation\StudentVerified;
use App\Models\convodataverification\ConvoStudent;
use DB;
use Illuminate\Support\Facades\Config;

class SendStudentEmails extends Command
{
    protected $signature = 'emails:send-student';
    protected $description = 'Send email notifications to students who have not received them yet';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
       
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

	        $students =ConvoStudent::where('email_notification_sent', 0)->limit(100)->get(); 
	       //dd( $students);
	        foreach($students as $student){
	            try {
	                // Mail::to($student->wpu_email_id)->send(new StudentVerified($student));
					Log::info('send email to user ' . $student->wpu_email_id );
	                $student->email_notification_sent = 1;
	                $student->save();
	            } catch (\Exception $e) {
	                Log::error('Failed to send email for user ' . $student->wpu_email_id . ': ' . $e->getMessage());
	            }
	        }

        	$this->info('Emails sent successfully!');
    }
}
