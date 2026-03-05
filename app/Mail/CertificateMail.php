<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateMail extends Mailable
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
        $certificateTemplate = view('emails.certificate', [
            'vc' => $this->vc,
            'token' => $this->token
        ])->render();

        return $this->from(env('MAIL_FROM_ADDRESS'))
                    ->subject("{$this->vc['degreeName']} Certificate")
                    ->html($certificateTemplate);
    }
}
