<?php

namespace App\Mail\convocation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
class AdminCorrection extends Mailable
{
    use Queueable, SerializesModels;

    public $student; // Add this property to hold the correction details

    /**
     * Create a new message instance.
     *
     * @param string $corrections The details of the corrections
     * @return void
     */
    public function __construct( $student)
    {
        $this->student = $student; // Store the correction details
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    //Anikiet 01-10-2025//
    // public function build()
    // {
         
    //     $email = $this->subject('MITWPU - Correction Made to Your Registration | PRN NO :' . @$this->student->prn)
    //             ->view('convodataverification.emails.admin_correction')
    //             ->with([
    //                 'student' => $this->student, // Pass the correction details to the view
    //             ]);

    //     // Check if the secondary email ID is not empty and add it as CC
    //     if (!empty(@$this->student->secondary_email_id)) {
    //     $email->cc($this->student->secondary_email_id);
    //     }
    //     // $email->bcc('support@scube.net.in');
    //     return $email;
                
    // }

     
}
