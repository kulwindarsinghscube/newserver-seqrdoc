<?php

namespace App\Mail\convocation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
class StudentReminder extends Mailable
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
    //     // $email = $this->subject('MITWPU - FINAL REMINDER FOR REGISTRATION | PRN NO :'.@$this->student['prn'])
    //     $email = $this->subject('MITWPU - Reopening of 6th Convocation (2024) application link for students. Till 30 November 2024 | PRN NO :'.@$this->student['prn'])        
    //                 ->view('convodataverification.emails.reminder')
    //                 ->with([
    //                     'student' => $this->student, // Pass the student name to the view
    //                 ]);
    //     if (!empty(@$this->student['secondary_email_id'])) {
    //      $email->cc($this->student['secondary_email_id']);
    //     }

    //     // $email->bcc('support@scube.net.in');

    //     return $email;
    // }

    
}
