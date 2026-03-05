<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventPassMail extends Mailable
{
    use SerializesModels;

    public $vc;
    public $token;

    public function __construct($vc, $token)
    {
        $this->vc = $vc;
        $this->token = $token;
    }

    public function build()
    {
        $eventPassTemplate = view('emails.eventPass', [
            'vc' => $this->vc,
            'token' => $this->token
        ])->render();

        return $this->from(env('MAIL_FROM_ADDRESS'))
                    ->subject("{$this->vc['eventName']} Invitation")
                    ->html($eventPassTemplate);
    }
}
