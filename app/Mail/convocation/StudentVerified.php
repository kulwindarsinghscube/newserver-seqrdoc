<?php

namespace App\Mail\convocation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
class StudentVerified extends Mailable
{
    use Queueable, SerializesModels;
    public $student; // Add this property to hold the student name

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($student)
    {
        $this->student = $student; // Store the student name
      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    //Anikiet 01-10-2025//
    // public function build()
    // {
    //     $email = $this->subject('MITWPU - Login Credentials for Convocation Registration | PRN NO :'.@$this->student['prn'])
    //                 ->view('convodataverification.emails.registration')
    //                 ->with([
    //                     'student' => $this->student, // Pass the student name to the view
    //                 ]);
    //     if (!empty(@$this->student['secondary_email_id'])) {
    //      $email->cc($this->student['secondary_email_id']);
    //     }
    //     // $email->bcc('support@scube.net.in');

    //     $email->attach(public_path().'/mitwpu/MITWPU STUDENT SUPPORT MANUAL - V1.0_10-09-2024.pdf', [
    //         'as' => 'STUDENT_SUPPORT_MANUAL.pdf', // Optional: Specify a custom name for the attachment
    //         'mime' => 'application/pdf', // Optional: Specify the MIME type
    //     ]);

    //     return $email;
    // }

    
}
