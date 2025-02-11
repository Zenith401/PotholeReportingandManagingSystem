<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail;

class CustomEmailUpdateVerification extends VerifyEmail
{
    /**
     * Override the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->greeting('Hi ' . $notifiable->fname . ',')
            ->line('You have recently updated your email address in the Pothole Reporting System.')
            ->line('Please verify your new email address by clicking the button below:')
            ->action('Verify New Email Address', $verificationUrl)
            ->line('If you did not request this change, please contact support immediately.')
            ->line('Thank you for keeping your account secure!');
    }
}
