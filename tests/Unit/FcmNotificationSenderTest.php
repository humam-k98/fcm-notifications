<?php

namespace Humamkerdiah\FcmNotifications\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Humamkerdiah\FcmNotifications\FcmMessage;
use Humamkerdiah\FcmNotifications\FcmNotificationSender;
use Humamkerdiah\FcmNotifications\Exceptions\FcmNotificationException;
use Humamkerdiah\FcmNotifications\Tests\TestCase;
use Mockery;

class FcmNotificationSenderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_throws_exception_for_missing_server_key_in_legacy_api()
    {
        $this->expectException(FcmNotificationException::class);
        $this->expectExceptionMessage('FCM server key is required for legacy API');

        $config = $this->getLegacyConfig();
        unset($config['server_key']);

        new FcmNotificationSender($config);
    }

    public function test_throws_exception_for_missing_project_id_in_v1_api()
    {
        $this->expectException(FcmNotificationException::class);
        $this->expectExceptionMessage('FCM project ID is required for v1 API');

        $config = $this->getV1Config();
        unset($config['project_id']);

        new FcmNotificationSender($config);
    }

    public function test_throws_exception_for_missing_service_account_in_v1_api()
    {
        $this->expectException(FcmNotificationException::class);
        $this->expectExceptionMessage('Service account key path or GOOGLE_APPLICATION_CREDENTIALS environment variable is required for v1 API');

        $config = $this->getV1Config();
        unset($config['service_account_key_path']);

        new FcmNotificationSender($config);
    }

    public function test_legacy_send_to_devices_success()
    {
        // Mock HTTP response
        $mockResponse = new Response(200, [], json_encode([
            'multicast_id' => 123456789,
            'success' => 2,
            'failure' => 0,
            'results' => [
                ['message_id' => 'msg1'],
                ['message_id' => 'msg2']
            ]
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Create sender with mocked client
        $config = $this->getLegacyConfig();
        $sender = new FcmNotificationSender($config);
        
        // Use reflection to replace the client
        $reflection = new \ReflectionClass($sender);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($sender, $client);

        $message = (new FcmMessage())
            ->setTitle('Test')
            ->setBody('Test Body')
            ->setTokens(['token1', 'token2']);

        $result = $sender->sendToDevices($message);

        $this->assertEquals(2, $result['success']);
        $this->assertEquals(0, $result['failure']);
    }

    public function test_legacy_send_to_devices_with_failures()
    {
        $this->expectException(FcmNotificationException::class);
        $this->expectExceptionMessage('Some messages failed to send');

        // Mock HTTP response with failures
        $mockResponse = new Response(200, [], json_encode([
            'multicast_id' => 123456789,
            'success' => 1,
            'failure' => 1,
            'results' => [
                ['message_id' => 'msg1'],
                ['error' => 'InvalidRegistration']
            ]
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $config = $this->getLegacyConfig();
        $sender = new FcmNotificationSender($config);
        
        $reflection = new \ReflectionClass($sender);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($sender, $client);

        $message = (new FcmMessage())
            ->setTitle('Test')
            ->setBody('Test Body')
            ->setTokens(['token1', 'token2']);

        $sender->sendToDevices($message);
    }

    public function test_legacy_send_to_topic_success()
    {
        // Mock HTTP response
        $mockResponse = new Response(200, [], json_encode([
            'message_id' => 'topic_msg_123'
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $config = $this->getLegacyConfig();
        $sender = new FcmNotificationSender($config);
        
        $reflection = new \ReflectionClass($sender);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($sender, $client);

        $message = (new FcmMessage())
            ->setTitle('Test')
            ->setBody('Test Body')
            ->setTopic('test-topic');

        $result = $sender->sendToTopic($message);

        $this->assertEquals('topic_msg_123', $result['message_id']);
    }

    public function test_legacy_send_to_topic_failure()
    {
        $this->expectException(FcmNotificationException::class);
        $this->expectExceptionMessage('Failed to send to topic');

        // Mock HTTP response without message_id
        $mockResponse = new Response(200, [], json_encode([
            'error' => 'InvalidTopic'
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $config = $this->getLegacyConfig();
        $sender = new FcmNotificationSender($config);
        
        $reflection = new \ReflectionClass($sender);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($sender, $client);

        $message = (new FcmMessage())
            ->setTitle('Test')
            ->setBody('Test Body')
            ->setTopic('test-topic');

        $sender->sendToTopic($message);
    }

    public function test_subscribe_to_topic_success()
    {
        // Mock HTTP response
        $mockResponse = new Response(200, [], json_encode([
            'results' => [
                ['error' => null],
                ['error' => null]
            ]
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $config = $this->getLegacyConfig();
        $sender = new FcmNotificationSender($config);
        
        $reflection = new \ReflectionClass($sender);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($sender, $client);

        $result = $sender->subscribeToTopic('test-topic', ['token1', 'token2']);

        $this->assertArrayHasKey('results', $result);
    }

    public function test_unsubscribe_from_topic_success()
    {
        // Mock HTTP response
        $mockResponse = new Response(200, [], json_encode([
            'results' => [
                ['error' => null],
                ['error' => null]
            ]
        ]));

        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $config = $this->getLegacyConfig();
        $sender = new FcmNotificationSender($config);
        
        $reflection = new \ReflectionClass($sender);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($sender, $client);

        $result = $sender->unsubscribeFromTopic('test-topic', ['token1', 'token2']);

        $this->assertArrayHasKey('results', $result);
    }

    public function test_can_instantiate_with_valid_legacy_config()
    {
        $config = $this->getLegacyConfig();
        $sender = new FcmNotificationSender($config);
        
        $this->assertInstanceOf(FcmNotificationSender::class, $sender);
    }
}
