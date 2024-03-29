<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class ResetPasswordLink extends Notification
{
    use Queueable;

    private $resetPasswordToken;
    private $email;

    /**
     * Create a new notification instance.
     */
    public function __construct($resetPasswordToken, $email)
    {
        $this->resetPasswordToken = $resetPasswordToken;
        $this->email = $email;
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
                    ->subject('重設密碼通知') 
                    ->line('您收到這封信是因為我們收到了您帳戶的密碼重設請求。')
                    ->action('重設密碼', route('resetPassword', [
                            'resetPasswordToken' => $this->resetPasswordToken,
                            'email' => $this->email
                        ]))
                    ->line('如果您沒有請求重設密碼，則無需進一步採取任何操作。')
                    ->line('感謝您使用我們的應用！');
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
