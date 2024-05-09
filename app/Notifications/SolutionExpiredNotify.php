<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SolutionExpiredNotify extends Notification
{
    use Queueable;

    private $plan;
    private $date;
    private $reciprocal;

    /**
     * Create a new notification instance.
     */
    public function __construct($plan, $date, $reciprocal)
    {
        //
        $this->plan = $plan;
        $this->date = $date;
        $this->reciprocal = $reciprocal;
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
                    ->subject('重要提醒：您的方案即將到期')
                    ->line('親愛的用戶，')
                    ->line('我們想提醒您，您的方案 ' . $this->plan . ' 即將在 ' . $this->reciprocal . ' 天後，也就是 ' . $this->date . ' 過期。')
                    ->line('我們感謝您一直以來對我們應用的支持。如果您想要續訂方案或有任何疑問，請隨時聯繫我們的客服團隊。')
                    ->line('謝謝您使用我們的應用服務！');
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
