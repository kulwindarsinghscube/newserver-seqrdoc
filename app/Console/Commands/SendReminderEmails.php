<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\convocation\StudentReminder;
use App\Models\convodataverification\ConvoStudent;
use App\Models\convodataverification\ConvoStudentLog;
use App\Models\convodataverification\StudentAckLog;
use App\Models\convodataverification\StudentTransaction;
use Carbon\Carbon;
class SendReminderEmails extends Command
{
    protected $signature = 'emails:send-reminder';
    protected $description = 'Send email notifications reminder to student';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    { 
        // dd("1",env('MAIL_PASSWORD_2'));
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
         $statuses = [
            "have not yet signed up", 
            "student acknowledge all data as correct, Payment is completed and preview pdf is approved", 
            "student re-acknowledged new data as correct, Payment is completed and preview pdf is approved"
        ];

        $students = ConvoStudent::where('is_printed',0)
        // ->whereNotIn('status', $statuses)
        ->where('reminder_notification_sent', 0)
        // ->where('id',7431)
        ->limit(100)->get(); 
        // dd($students);
       
        foreach ($students as $student) {  
            try { 
                Mail::to($student['wpu_email_id'])->send(new StudentReminder($student));
                $student->reminder_notification_sent = 1 ;
                $student->save();
                Log::info('Reminder email sent to ' . $student['wpu_email_id']);

            } catch (\Exception $e) {
                Log::error('Failed to send email for user ' . $student['wpu_email_id'] . ': ' . $e->getMessage());
            }
        }

        $this->info('Emails sent successfully!');
    }
}