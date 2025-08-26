<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Licence;

class PaymentDataMail extends Mailable
{
    use Queueable, SerializesModels;

    public $licence;
    public $user;
    public $domain;

    public function __construct(Licence $licence)
    {
        $this->licence = $licence;
        $this->user = $licence->user;
        $this->domain = $licence->domain;
        
        // Ensure all models are fresh to get proper casting
        $this->licence->refresh();
        $this->user->refresh();
        $this->domain->refresh();
    }

    public function build()
    {
        return $this->subject('New Payment Data - ExpressLabelMaker')
                    ->to('info@expresslabelmaker.com')
                    ->view('emails.payment-data')
                    ->with([
                        'licence' => $this->licence,
                        'user' => $this->user,
                        'domain' => $this->domain,
                    ]);
    }
}
