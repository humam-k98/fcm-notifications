<?php

namespace Humamkerdiah\FcmNotifications\Tests\Unit;

use Humamkerdiah\FcmNotifications\Channels\FcmChannel;
use Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender;
use Humamkerdiah\FcmNotifications\FcmMessage;
use Humamkerdiah\FcmNotifications\Tests\TestCase;
use Illuminate\Notifications\Notification;
use Mockery;
use RuntimeException;

class FcmChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_throws_exception_when_notification_missing_to_fcm_method()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Notification is missing toFcm method.');

        $sender = Mockery::mock(FcmNotificationSender::class);
        $channel = new FcmChannel($sender);
        
        $notifiable = new TestNotifiable();
        $notification = new TestNotificationWithoutToFcm();

        $channel->send($notifiable, $notification);
    }

    public function test_throws_exception_when_to_fcm_returns_wrong_type()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('toFcm must return an instance of FcmMessage.');

        $sender = Mockery::mock(FcmNotificationSender::class);
        $channel = new FcmChannel($sender);
        
        $notifiable = new TestNotifiable();
        $notification = new TestNotificationWithInvalidReturn();

        $channel->send($notifiable, $notification);
    }

    public function test_throws_exception_when_no_tokens_or_topic_specified()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No FCM tokens or topic specified for the notification.');

        $sender = Mockery::mock(FcmNotificationSender::class);
        $channel = new FcmChannel($sender);
        
        $notifiable = new TestNotifiable();
        $notification = new TestNotificationWithEmptyMessage();

        $channel->send($notifiable, $notification);
    }

    public function test_sends_to_devices_when_tokens_are_present()
    {
        $sender = Mockery::mock(FcmNotificationSender::class);
        $sender->shouldReceive('sendToDevices')
            ->once()
            ->with(Mockery::type(FcmMessage::class))
            ->andReturn(['success' => 1]);

        $channel = new FcmChannel($sender);
        
        $notifiable = new TestNotifiable();
        $notification = new TestNotificationWithTokens();

        $result = $channel->send($notifiable, $notification);

        $this->assertEquals(['success' => 1], $result);
    }

    public function test_sends_to_topic_when_topic_is_present()
    {
        $sender = Mockery::mock(FcmNotificationSender::class);
        $sender->shouldReceive('sendToTopic')
            ->once()
            ->with(Mockery::type(FcmMessage::class))
            ->andReturn(['message_id' => 'test_message_id']);

        $channel = new FcmChannel($sender);
        
        $notifiable = new TestNotifiable();
        $notification = new TestNotificationWithTopic();

        $result = $channel->send($notifiable, $notification);

        $this->assertEquals(['message_id' => 'test_message_id'], $result);
    }

    public function test_gets_tokens_from_notifiable_when_message_has_no_tokens()
    {
        $sender = Mockery::mock(FcmNotificationSender::class);
        $sender->shouldReceive('sendToDevices')
            ->once()
            ->with(Mockery::on(function ($message) {
                return $message instanceof FcmMessage && 
                       count($message->getTokens()) === 1 && 
                       $message->getTokens()[0] === 'notifiable_token';
            }))
            ->andReturn(['success' => 1]);

        $channel = new FcmChannel($sender);
        
        $notifiable = new TestNotifiableWithToken();
        $notification = new TestNotificationWithEmptyMessage();

        $result = $channel->send($notifiable, $notification);

        $this->assertEquals(['success' => 1], $result);
    }

    public function test_gets_multiple_tokens_from_notifiable_as_array()
    {
        $sender = Mockery::mock(FcmNotificationSender::class);
        $sender->shouldReceive('sendToDevices')
            ->once()
            ->with(Mockery::on(function ($message) {
                return $message instanceof FcmMessage && 
                       count($message->getTokens()) === 2 && 
                       $message->getTokens() === ['token1', 'token2'];
            }))
            ->andReturn(['success' => 2]);

        $channel = new FcmChannel($sender);
        
        $notifiable = new TestNotifiableWithMultipleTokens();
        $notification = new TestNotificationWithEmptyMessage();

        $result = $channel->send($notifiable, $notification);

        $this->assertEquals(['success' => 2], $result);
    }
}

// Test helper classes
class TestNotifiable
{
    public function routeNotificationFor($channel, $notification = null)
    {
        return null;
    }
}

class TestNotifiableWithToken
{
    public function routeNotificationFor($channel, $notification = null)
    {
        if ($channel === 'fcm') {
            return 'notifiable_token';
        }
        return null;
    }
}

class TestNotifiableWithMultipleTokens
{
    public function routeNotificationFor($channel, $notification = null)
    {
        if ($channel === 'fcm') {
            return ['token1', 'token2'];
        }
        return null;
    }
}

class TestNotificationWithoutToFcm extends Notification
{
    // No toFcm method
}

class TestNotificationWithInvalidReturn extends Notification
{
    public function toFcm($notifiable)
    {
        return 'not an FcmMessage instance';
    }
}

class TestNotificationWithEmptyMessage extends Notification
{
    public function toFcm($notifiable)
    {
        return new FcmMessage();
    }
}

class TestNotificationWithTokens extends Notification
{
    public function toFcm($notifiable)
    {
        return (new FcmMessage())
            ->setTitle('Test')
            ->setBody('Test Body')
            ->setTokens(['test_token']);
    }
}

class TestNotificationWithTopic extends Notification
{
    public function toFcm($notifiable)
    {
        return (new FcmMessage())
            ->setTitle('Test')
            ->setBody('Test Body')
            ->setTopic('test_topic');
    }
}
