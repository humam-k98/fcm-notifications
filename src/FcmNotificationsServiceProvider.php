<?php

namespace Humamkerdiah\FcmNotifications;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Notifications\ChannelManager;
use Humamkerdiah\FcmNotifications\Channels\FcmChannel;
use Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender as FcmNotificationSenderContract;

class FcmNotificationsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/fcm.php' => $this->app->configPath('fcm.php'),
            ], 'fcm-config');
        }

        $this->app->resolving(ChannelManager::class, function (ChannelManager $manager) {
            $manager->extend('fcm', function ($app) {
                return new FcmChannel($app->make(FcmNotificationSenderContract::class));
            });
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/fcm.php',
            'fcm'
        );

        $this->app->singleton(FcmNotificationSenderContract::class, function ($app) {
            return new FcmNotificationSender($app['config']['fcm']);
        });

        $this->app->alias(FcmNotificationSenderContract::class, 'fcm.sender');
    }

    public function provides()
    {
        return [
            FcmNotificationSenderContract::class,
            'fcm.sender'
        ];
    }
}