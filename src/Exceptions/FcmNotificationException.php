<?php

namespace Humamkerdiah\FcmNotifications\Exceptions;

use Exception;

class FcmNotificationException extends Exception
{
    protected array $response;

    public function __construct(string $message, array $response = [])
    {
        parent::__construct($message);
        $this->response = $response;
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}