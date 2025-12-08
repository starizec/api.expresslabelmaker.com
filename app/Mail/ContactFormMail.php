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
    public $isConfirmation;

    public function __construct($data, $isConfirmation = false)
    {
        $this->email = $data['email'];
        $this->messageContent = $data['contactMessage'];
        $this->isConfirmation = $isConfirmation;
    }

    public function build()
    {
        if ($this->isConfirmation) {
            return $this->subject('Potvrda primitka vaše poruke - ExpressLabelMaker')
                        ->view('emails.contact-form-confirmation')
                        ->with([
                            'email' => $this->email,
                            'contactMessage' => $this->messageContent
                        ]);
        }

        return $this->subject('Nova kontakt poruka - ExpressLabelMaker')
                    ->replyTo($this->email)
                    ->view('emails.contact-form')
                    ->with([
                        'email' => $this->email,
                        'contactMessage' => $this->messageContent
                    ]);
    }
} 