<?php

namespace Humamkerdiah\FcmNotifications\Contracts;

interface FcmMessage
{
    /**
     * Set the title of the notification
     */
    public function setTitle(string $title): self;

    /**
     * Set the body of the notification
     */
    public function setBody(string $body): self;

    /**
     * Set the data payload of the notification
     */
    public function setData(array $data): self;

    /**
     * Set the target tokens to receive the notification
     */
    public function setTokens(array $tokens): self;

    /**
     * Set the topic to send the notification to
     */
    public function setTopic(string $topic): self;

    /**
     * Get the notification title
     */
    public function getTitle(): string;

    /**
     * Get the notification body
     */
    public function getBody(): string;

    /**
     * Get the notification data payload
     */
    public function getData(): array;

    /**
     * Get the target tokens
     */
    public function getTokens(): array;

    /**
     * Get the target topic
     */
    public function getTopic(): ?string;    /**
     * Get the notification payload as array
     */
    public function toArray(): array;

    /**
     * Get the notification payload as FCM HTTP v1 API array
     */
    public function toV1Array(): array;

    /**
     * Get the notification payload as FCM HTTP v1 API batch array for multiple tokens
     */
    public function toV1BatchArray(): array;
}