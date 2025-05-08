<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $messageContent;

    public function __construct($data)
    {
        $this->email = $data['email'];
        $this->messageContent = $data['contactMessage'];
    }

    public function build()
    {
        return $this->subject('Nova kontakt poruka - ExpressLabelMaker')
                    ->view('emails.contact-form')
                    ->with([
                        'email' => $this->email,
                        'contactMessage' => $this->messageContent
                    ]);
    }
} 