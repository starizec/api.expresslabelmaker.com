<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class WelcomeNewUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        $resetUrl = $this->resetUrl($notifiable);
        
        return (new MailMessage)
            ->subject(Lang::get('auth.welcome_subject'))
            ->greeting(Lang::get('auth.welcome_greeting'))
            ->line(Lang::get('auth.welcome_message'))
            ->line(Lang::get('auth.welcome_password_setup'))
            ->action(Lang::get('auth.welcome_password_button'), $resetUrl)
            ->line(Lang::get('auth.welcome_password_expiry', ['count' => config('auth.passwords.users.expire')]))
            ->line(Lang::get('auth.welcome_no_action'));
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
    
    /**
     * Get the reset URL for the given notifiable.
     */
    protected function resetUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(Config::get('auth.passwords.users.expire', 60)),
            [
                'token' => app('auth.password.broker')->createToken($notifiable),
                'email' => $notifiable->getEmailForPasswordReset(),
            ]
        );
    }
} 