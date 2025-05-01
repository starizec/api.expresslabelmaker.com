<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;

class LicenceRenewalNotification extends Notification
{
    use Queueable;

    public $licence;

    /**
     * Create a new notification instance.
     */
    public function __construct($licence)
    {
        $this->licence = $licence;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $formattedDate = Carbon::parse($this->licence->valid_until)->format('d.m.Y');
        
        return (new MailMessage)
            ->subject(Lang::get('messages.licence_renewal_notification', ['domain' => $this->licence->domain->name]))
            ->greeting(Lang::get('messages.licence_renewal_added', ['domain' => $this->licence->domain->name, 'valid_until' => $formattedDate]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
