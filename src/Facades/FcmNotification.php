<?php

namespace Humamkerdiah\FcmNotifications\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Config;
use Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender;

class FcmNotification extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FcmNotificationSender::class;
    }
}