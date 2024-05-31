<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClickTimesRemind extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private $code;

    /**
     * @var int
     */
    private $times;

    /**
     * Create a new notification instance.
     */
    public function __construct($code,$times)
    {
        $this->code = $code;
        $this->times = $times;
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
                    ->subject('次數提醒通知') 
                    ->line('3D名片的次數剩下'.$this->times.'次，請至相關店面加值');
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
