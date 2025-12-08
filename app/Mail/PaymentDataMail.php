<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use App\Models\Licence;

class PaymentDataMail extends Mailable
{
    use Queueable, SerializesModels;

    public $licence;
    public $user;
    public $domain;
    public $locale;

    public function __construct(Licence $licence, $locale = 'hr')
    {
        $this->licence = $licence;
        $this->user = $licence->user;
        $this->domain = $licence->domain;
        $this->locale = $locale;
        
        // Ensure all models are fresh to get proper casting
        $this->licence->refresh();
        $this->user->refresh();
        $this->domain->refresh();
    }

    public function build()
    {
        // Set locale for translations
        App::setLocale($this->locale);
        
        return $this->subject(__('payment.offer_request') . ' - ExpressLabelMaker')
                    ->to('info@expresslabelmaker.com')
                    ->replyTo($this->user->email)
                    ->view('emails.payment-data')
                    ->with([
                        'licence' => $this->licence,
                        'user' => $this->user,
                        'domain' => $this->domain,
                    ]);
    }
}
