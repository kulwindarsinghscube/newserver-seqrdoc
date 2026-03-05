<?php

namespace App\Mail\convocation;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
class PaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $paymentDetails; // Property to hold payment details

    public $student;
    /**
     * Create a new message instance.
     *
     * @param array $paymentDetails Array containing payment details like amount and transaction ID
     * @return void
     */
    public function __construct(array $paymentDetails,$student)
    {
        $this->paymentDetails = $paymentDetails; // Store payment details
        $this->student = $student;
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    //Anikiet 01-10-2025//
    // public function build()
    // {
    //     $email = $this->subject('MITWPU - Payment Confirmation | PRN NO :'.@$this->student->prn)
    //                 ->view('convodataverification.emails.payment_confirmation')
    //                 ->with([
    //                     'paymentDetails' => $this->paymentDetails,
    //                     'student' => $this->student, // Pass payment details to the view
    //                 ]);
    //     if (!empty(@$this->student->secondary_email_id)) {
    //      $email->cc($this->student->secondary_email_id);
    //     }
    //     // $email->bcc('support@scube.net.in');
    //     return $email;
    // }

     
}
