<?php

namespace Humamkerdiah\FcmNotifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender;
use Humamkerdiah\FcmNotifications\FcmMessage;
use RuntimeException;

/**
 * Class FcmChannel
 * @package Humamkerdiah\FcmNotifications\Channels
 * @method \Humamkerdiah\FcmNotifications\FcmMessage toFcm(mixed $notifiable)
 */
class FcmChannel
{
    protected FcmNotificationSender $fcmSender;

    public function __construct(FcmNotificationSender $fcmSender)
    {
        $this->fcmSender = $fcmSender;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return array|null
     */
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toFcm')) {
            throw new RuntimeException('Notification is missing toFcm method.');
        }

        /** @phpstan-ignore-next-line */
        $message = $notification->toFcm($notifiable);

        if (! $message instanceof FcmMessage) {
            throw new RuntimeException('toFcm must return an instance of FcmMessage.');
        }

        if (empty($message->getTokens()) && empty($message->getTopic())) {
            // Try to get tokens from the notifiable entity
            $tokens = $notifiable->routeNotificationFor('fcm', $notification);
            if (!empty($tokens)) {
                $message->setTokens(is_array($tokens) ? $tokens : [$tokens]);
            }
        }

        if (!empty($message->getTokens())) {
            return $this->fcmSender->sendToDevices($message);
        } elseif (!empty($message->getTopic())) {
            return $this->fcmSender->sendToTopic($message);
        }

        throw new RuntimeException('No FCM tokens or topic specified for the notification.');
    }
}