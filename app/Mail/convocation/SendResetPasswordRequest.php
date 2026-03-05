<?php

namespace App\Mail\convocation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class SendResetPasswordRequest extends Mailable
{
    use Queueable, SerializesModels;
    public $student; // Add this property to hold the student name
    public $token;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($student,$token,$subdomain)
    {
        $this->student = $student; // Store the student name
        $this->token = $token;
        $this->subdomain = $subdomain;
      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        if ($this->subdomain=="kmtc"){
            $resetLink = 'https://kmtc.seqrdoc.com/convo_student/reset_password_token/'.$this->token;
        }
        else if ($this->subdomain=="mitwpu"){
            $resetLink = 'https://convocation.mitwpu.edu.in/convo_student/reset_password_token/'.$this->token;


        }else{
            $resetLink = 'https://demo.seqrdoclocal.com/convo_student/reset_password_token/'.$this->token;
        }
        // $encryptedPrn = Crypt::encrypt(@$this->student['prn']);
        // $resetLink = 'https://convocation.mitwpu.edu.in/convo_student/reset_password_token/'.$this->token;
        // dd($resetLink );
        $email = $this->subject(strtoupper($this->subdomain) .' - Reset your password | PRN NO :'.@$this->student['prn'])
                    ->view('convodataverification.emails.send_reset_password_request')
                    ->with([
                        'student' => $this->student,
                        'resetLink'=>$resetLink // Pass the student name to the view
                    ]);
        if (!empty(@$this->student['secondary_email_id'])) {
         $email->cc($this->student['secondary_email_id']);
        }
        // $email->bcc('support@scube.net.in');
        return $email;
    }

    
}
