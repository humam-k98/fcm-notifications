<?php

namespace Humamkerdiah\FcmNotifications\Contracts;

interface FcmNotificationSender
{
    /**
     * Send a notification to specific devices
     */
    public function sendToDevices(FcmMessage $message): array;

    /**
     * Send a notification to a topic
     */
    public function sendToTopic(FcmMessage $message): array;

    /**
     * Subscribe devices to a topic
     */
    public function subscribeToTopic(string $topic, array $tokens): array;

    /**
     * Unsubscribe devices from a topic
     */
    public function unsubscribeFromTopic(string $topic, array $tokens): array;
}