<?php

namespace Humamkerdiah\FcmNotifications\Tests\Unit;

use Humamkerdiah\FcmNotifications\FcmMessage;
use Humamkerdiah\FcmNotifications\Tests\TestCase;

class FcmMessageTest extends TestCase
{
    public function test_can_create_fcm_message()
    {
        $message = new FcmMessage();
        $this->assertInstanceOf(FcmMessage::class, $message);
    }

    public function test_can_set_and_get_title()
    {
        $message = new FcmMessage();
        $title = 'Test Title';
        
        $result = $message->setTitle($title);
        
        $this->assertSame($message, $result);
        $this->assertEquals($title, $message->getTitle());
    }

    public function test_can_set_and_get_body()
    {
        $message = new FcmMessage();
        $body = 'Test Body';
        
        $result = $message->setBody($body);
        
        $this->assertSame($message, $result);
        $this->assertEquals($body, $message->getBody());
    }

    public function test_can_set_and_get_data()
    {
        $message = new FcmMessage();
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        
        $result = $message->setData($data);
        
        $this->assertSame($message, $result);
        $this->assertEquals($data, $message->getData());
    }

    public function test_can_set_and_get_tokens()
    {
        $message = new FcmMessage();
        $tokens = ['token1', 'token2', 'token3'];
        
        $result = $message->setTokens($tokens);
        
        $this->assertSame($message, $result);
        $this->assertEquals($tokens, $message->getTokens());
    }

    public function test_can_set_and_get_topic()
    {
        $message = new FcmMessage();
        $topic = 'test-topic';
        
        $result = $message->setTopic($topic);
        
        $this->assertSame($message, $result);
        $this->assertEquals($topic, $message->getTopic());
    }

    public function test_to_array_legacy_format_with_tokens()
    {
        $message = new FcmMessage();
        $message->setTitle('Test Title')
            ->setBody('Test Body')
            ->setData(['key' => 'value'])
            ->setTokens(['token1', 'token2']);

        $array = $message->toArray();

        $expected = [
            'notification' => [
                'title' => 'Test Title',
                'body' => 'Test Body',
            ],
            'data' => ['key' => 'value'],
            'registration_ids' => ['token1', 'token2']
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_array_legacy_format_with_topic()
    {
        $message = new FcmMessage();
        $message->setTitle('Test Title')
            ->setBody('Test Body')
            ->setData(['key' => 'value'])
            ->setTopic('test-topic');

        $array = $message->toArray();

        $expected = [
            'notification' => [
                'title' => 'Test Title',
                'body' => 'Test Body',
            ],
            'data' => ['key' => 'value'],
            'to' => '/topics/test-topic'
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_v1_array_format_with_token()
    {
        $message = new FcmMessage();
        $message->setTitle('Test Title')
            ->setBody('Test Body')
            ->setData(['key' => 'value'])
            ->setTokens(['token1']);

        $array = $message->toV1Array();

        $expected = [
            'message' => [
                'notification' => [
                    'title' => 'Test Title',
                    'body' => 'Test Body',
                ],
                'data' => ['key' => 'value'],
                'token' => 'token1'
            ]
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_v1_array_format_with_topic()
    {
        $message = new FcmMessage();
        $message->setTitle('Test Title')
            ->setBody('Test Body')
            ->setData(['key' => 'value'])
            ->setTopic('test-topic');

        $array = $message->toV1Array();

        $expected = [
            'message' => [
                'notification' => [
                    'title' => 'Test Title',
                    'body' => 'Test Body',
                ],
                'data' => ['key' => 'value'],
                'topic' => 'test-topic'
            ]
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_v1_batch_array_format()
    {
        $message = new FcmMessage();
        $message->setTitle('Test Title')
            ->setBody('Test Body')
            ->setData(['key' => 'value'])
            ->setTokens(['token1', 'token2']);

        $array = $message->toV1BatchArray();

        $expected = [
            [
                'notification' => [
                    'title' => 'Test Title',
                    'body' => 'Test Body',
                ],
                'data' => ['key' => 'value'],
                'token' => 'token1'
            ],
            [
                'notification' => [
                    'title' => 'Test Title',
                    'body' => 'Test Body',
                ],
                'data' => ['key' => 'value'],
                'token' => 'token2'
            ]
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_data_values_are_converted_to_strings_in_v1_format()
    {
        $message = new FcmMessage();
        $message->setTitle('Test')
            ->setBody('Test')
            ->setData(['int_value' => 123, 'bool_value' => true, 'string_value' => 'test'])
            ->setTokens(['token1']);

        $array = $message->toV1Array();

        $this->assertEquals([
            'int_value' => '123',
            'bool_value' => '1',
            'string_value' => 'test'
        ], $array['message']['data']);
    }

    public function test_message_without_data_omits_data_field()
    {
        $message = new FcmMessage();
        $message->setTitle('Test Title')
            ->setBody('Test Body')
            ->setTokens(['token1']);

        $legacyArray = $message->toArray();
        $v1Array = $message->toV1Array();

        $this->assertArrayNotHasKey('data', $legacyArray);
        $this->assertArrayNotHasKey('data', $v1Array['message']);
    }
}
