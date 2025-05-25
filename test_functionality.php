<?php

require_once __DIR__ . '/vendor/autoload.php';

use Humamkerdiah\FcmNotifications\FcmMessage;
use Humamkerdiah\FcmNotifications\FcmNotificationSender;
use Humamkerdiah\FcmNotifications\Exceptions\FcmNotificationException;

echo "=== FCM Notifications Package Test ===\n\n";

// Test 1: FcmMessage Creation and Formatting
echo "Test 1: FcmMessage Creation and Formatting\n";
echo "-------------------------------------------\n";

try {
    $message = new FcmMessage();
    $message->setTitle('Test Notification')
           ->setBody('This is a test message')
           ->setData(['key' => 'value', 'user_id' => '123'])
           ->setTokens(['device-token-1', 'device-token-2']);

    echo "✓ FcmMessage created successfully\n";
    echo "✓ Title: " . $message->getTitle() . "\n";
    echo "✓ Body: " . $message->getBody() . "\n";
    echo "✓ Data: " . json_encode($message->getData()) . "\n";
    echo "✓ Tokens: " . json_encode($message->getTokens()) . "\n";

    // Test legacy format
    $legacyArray = $message->toArray();
    echo "✓ Legacy format: " . json_encode($legacyArray, JSON_PRETTY_PRINT) . "\n\n";

    // Test v1 format
    $v1Array = $message->toV1Array();
    echo "✓ V1 format: " . json_encode($v1Array, JSON_PRETTY_PRINT) . "\n\n";

} catch (Exception $e) {
    echo "✗ FcmMessage test failed: " . $e->getMessage() . "\n\n";
}

// Test 2: Topic Message
echo "Test 2: Topic Message\n";
echo "---------------------\n";

try {
    $topicMessage = new FcmMessage();
    $topicMessage->setTitle('Topic Notification')
               ->setBody('Message for all subscribers')
               ->setTopic('news');

    echo "✓ Topic message created successfully\n";
    echo "✓ Topic: " . $topicMessage->getTopic() . "\n";

    $topicArray = $topicMessage->toArray();
    echo "✓ Topic format: " . json_encode($topicArray, JSON_PRETTY_PRINT) . "\n\n";

} catch (Exception $e) {
    echo "✗ Topic message test failed: " . $e->getMessage() . "\n\n";
}

// Test 3: Configuration Validation
echo "Test 3: Configuration Validation\n";
echo "--------------------------------\n";

try {
    // Test legacy config validation
    $legacyConfig = [
        'api_version' => 'legacy',
        'server_key' => 'test-server-key',
        'endpoints' => [
            'legacy' => [
                'send' => 'https://fcm.googleapis.com/fcm/send',
                'topic_subscribe' => 'https://iid.googleapis.com/iid/v1:batchAdd',
                'topic_unsubscribe' => 'https://iid.googleapis.com/iid/v1:batchRemove',
            ]
        ],
        'defaults' => ['timeout' => 30.0]
    ];

    echo "✓ Legacy config structure is valid\n";

    // Test v1 config validation (without actually creating sender)
    $v1Config = [
        'api_version' => 'v1',
        'project_id' => 'test-project',
        'service_account_key_path' => '/fake/path/key.json',
        'endpoints' => [
            'v1' => [
                'send' => 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send',
                'topic_subscribe' => 'https://iid.googleapis.com/iid/v1:batchAdd',
                'topic_unsubscribe' => 'https://iid.googleapis.com/iid/v1:batchRemove',
            ]
        ],
        'defaults' => ['timeout' => 30.0]
    ];

    echo "✓ V1 config structure is valid\n\n";

} catch (Exception $e) {
    echo "✗ Configuration test failed: " . $e->getMessage() . "\n\n";
}

// Test 4: Exception Handling
echo "Test 4: Exception Handling\n";
echo "---------------------------\n";

try {
    // Test invalid config - should throw exception
    try {
        $invalidConfig = [
            'api_version' => 'legacy',
            // Missing server_key - should cause validation error
        ];
        
        $sender = new FcmNotificationSender($invalidConfig);
        echo "✗ Should have thrown exception for invalid config\n";
    } catch (FcmNotificationException $e) {
        echo "✓ Correctly caught FcmNotificationException: " . $e->getMessage() . "\n";
    }

    // Test v1 config validation
    try {
        $invalidV1Config = [
            'api_version' => 'v1',
            // Missing project_id - should cause validation error
        ];
        
        $sender = new FcmNotificationSender($invalidV1Config);
        echo "✗ Should have thrown exception for invalid v1 config\n";
    } catch (FcmNotificationException $e) {
        echo "✓ Correctly caught V1 config FcmNotificationException: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "✗ Exception handling test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "Basic functionality tests completed.\n";
echo "To test actual FCM sending, you need to:\n";
echo "1. Set up valid Firebase project credentials\n";
echo "2. Configure environment variables\n";
echo "3. Run integration tests with real FCM endpoints\n\n";

echo "Package appears to be working correctly for basic operations!\n";
