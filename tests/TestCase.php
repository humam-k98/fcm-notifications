<?php

namespace Humamkerdiah\FcmNotifications\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Humamkerdiah\FcmNotifications\FcmNotificationsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            FcmNotificationsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup configuration for testing
        $app['config']->set('fcm.api_version', 'legacy');
        $app['config']->set('fcm.server_key', 'test-server-key');
        $app['config']->set('fcm.project_id', 'test-project-id');
        $app['config']->set('fcm.service_account_key_path', '/path/to/test-key.json');
        $app['config']->set('fcm.endpoints.legacy.send', 'https://fcm.googleapis.com/fcm/send');
        $app['config']->set('fcm.endpoints.legacy.topic_subscribe', 'https://iid.googleapis.com/iid/v1:batchAdd');
        $app['config']->set('fcm.endpoints.legacy.topic_unsubscribe', 'https://iid.googleapis.com/iid/v1:batchRemove');
        $app['config']->set('fcm.endpoints.v1.send', 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send');
        $app['config']->set('fcm.endpoints.v1.topic_subscribe', 'https://iid.googleapis.com/iid/v1:batchAdd');
        $app['config']->set('fcm.endpoints.v1.topic_unsubscribe', 'https://iid.googleapis.com/iid/v1:batchRemove');
        $app['config']->set('fcm.defaults.timeout', 30.0);
    }

    protected function getLegacyConfig(): array
    {
        return [
            'api_version' => 'legacy',
            'server_key' => 'test-server-key',
            'endpoints' => [
                'legacy' => [
                    'send' => 'https://fcm.googleapis.com/fcm/send',
                    'topic_subscribe' => 'https://iid.googleapis.com/iid/v1:batchAdd',
                    'topic_unsubscribe' => 'https://iid.googleapis.com/iid/v1:batchRemove',
                ]
            ],
            'defaults' => [
                'timeout' => 30.0
            ]
        ];
    }

    protected function getV1Config(): array
    {
        return [
            'api_version' => 'v1',
            'project_id' => 'test-project-id',
            'service_account_key_path' => __DIR__ . '/fixtures/test-service-account.json',
            'endpoints' => [
                'v1' => [
                    'send' => 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send',
                    'topic_subscribe' => 'https://iid.googleapis.com/iid/v1:batchAdd',
                    'topic_unsubscribe' => 'https://iid.googleapis.com/iid/v1:batchRemove',
                ]
            ],
            'defaults' => [
                'timeout' => 30.0
            ]
        ];
    }
}
