<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
class SendSummaryBlockChain extends Mailable
{
    use Queueable, SerializesModels;
    public $documents; // Add this property to hold the student name
    public $is_failed;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($documents,$is_failed)
    {
        $this->documents = $documents;  
        $this->is_failed = $is_failed;
      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $cc = array(
            'dev13@scube.net.in',
            'dev12@scube.net.in',
            'ceo@devharshinfotech.com',
            'makrand.pendse@scube.net.in',
            'gangadhar@scube.net.in',
        );

        // $cc = array(
        //     'dev13@scube.net.in',
        //     'dev12@scube.net.in',
        //     'ankit@scube.net.in',
        //     'makrand.pendse@scube.net.in',
        //     'gangadhar@scube.net.in',
        // );

        if( $this->is_failed == 0){
            $subject ="No error occurred during retrieval";
        }else{
            $subject = $this->is_failed." error occurred during retrieval";
        }
      
        $email = $this->subject("Blockchain Documents - $subject")
                      ->view('mail.send_blockchain_summary_report')
                      ->with([
                          'documents' => $this->documents, // Pass the documents to the view
                      ])
                      ->cc($cc); // Add CC recipients
        

        return $email;
    }

    
}
