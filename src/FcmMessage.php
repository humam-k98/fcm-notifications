<?php

namespace Humamkerdiah\FcmNotifications;

use Humamkerdiah\FcmNotifications\Contracts\FcmMessage as FcmMessageContract;

class FcmMessage implements FcmMessageContract
{
    protected string $title;
    protected string $body;
    protected array $data = [];
    protected array $tokens = [];
    protected ?string $topic = null;

    public function setTitle(string $title): FcmMessageContract
    {
        $this->title = $title;
        return $this;
    }

    public function setBody(string $body): FcmMessageContract
    {
        $this->body = $body;
        return $this;
    }

    public function setData(array $data): FcmMessageContract
    {
        $this->data = $data;
        return $this;
    }

    public function setTokens(array $tokens): FcmMessageContract
    {
        $this->tokens = $tokens;
        return $this;
    }

    public function setTopic(string $topic): FcmMessageContract
    {
        $this->topic = $topic;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function toArray(): array
    {
        $message = [
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
            ]
        ];

        if (!empty($this->data)) {
            $message['data'] = $this->data;
        }

        if (!empty($this->tokens)) {
            $message['registration_ids'] = $this->tokens;
        } elseif ($this->topic) {
            $message['to'] = '/topics/' . $this->topic;
        }

        return $message;
    }
}