<?php

namespace App\Mail\convocation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
class StudentSummary extends Mailable
{
    use Queueable, SerializesModels;

    public $student; // Add this property to hold the correction details

    /**
     * Create a new message instance.
     *
     * @param string $corrections The details of the corrections
     * @return void
     */
    public function __construct( $all_count,$collection_mode)
    {
        $this->all_count = $all_count;
        $this->collection_mode = $collection_mode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    //Anikiet 01-10-2025//
    // public function build()
    // {
    //     $currentYear = date('Y');
    //     $formattedDate = date('d-m-Y');
 
    //     $email = $this->subject("MITWPU Convocation {$currentYear} -Summary of student verification and payments {$formattedDate}")
    //             ->view('convodataverification.emails.student_summary')
    //             ->with([
    //                 'all_count' => $this->all_count,
    //                 'collection_mode'=>$this->collection_mode
    //             ]);

    //     // Check if the secondary email ID is not empty and add it as CC
    //     $cc = ['dev13@scube.net.in','dev12@scube.net.in','software@scube.net.in','tester@scube.net.in','ankit@scube.net.in'];
    //     // $cc = 'dev12@scube.net.in';
    //     $email->cc($cc);
    //     // $email->bcc('support@scube.net.in');

    //     return $email;
                
    // }

     
}
