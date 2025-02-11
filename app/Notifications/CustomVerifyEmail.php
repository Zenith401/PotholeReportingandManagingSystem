<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /**
     * Override the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        // Generate the signed URL
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->greeting('Welcome to the Pothole Reporting System, ' . $notifiable->fname . '!')
            ->line('We’re excited you’re here. Before getting started, we just need to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required. Without email verification your account will be deactivated within 30 days');
            //->line('Without email verification your account will be deactivated within 30 days');
    }
}
