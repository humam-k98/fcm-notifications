<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Humamkerdiah\FcmNotifications\FcmMessage;

class WelcomeNotification extends Notification
{
    use Queueable;

    private $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['fcm'];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage())
            ->setTitle('Welcome to Our App!')
            ->setBody("Hello {$this->user->name}, welcome to our application!")
            ->setData([
                'user_id' => $this->user->id,
                'action' => 'welcome',
                'timestamp' => now()->toISOString()
            ]);
    }
}
