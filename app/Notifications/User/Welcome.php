<?php

namespace App\Notifications\User;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Welcome extends Notification
{
    use Queueable;

    public string $url;

    public string $temporaryPassword;

    public User $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, string $temporaryPassword)
    {
        $this->user = $user;
        $this->url = Filament::getVerifyEmailUrl($user);
        $this->temporaryPassword = $temporaryPassword;
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
        return (new MailMessage)
            ->subject('Welcome to '.config('app.name').'!')
            ->line('Welcome to '.config('app.name').'!')
            ->line('You have been invited to join this Polypack instance.')
            ->line('Please verify your email address to continue.')
            ->action('Verify Email', $this->url)
            ->line('Your temporary password is: '.$this->temporaryPassword)
            ->line('You can change your password after logging in.')
            ->line('Your email will not be verified until you log in.');
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
