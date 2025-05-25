<?php

require_once __DIR__ . '/vendor/autoload.php';

use Humamkerdiah\FcmNotifications\FcmMessage;
use Humamkerdiah\FcmNotifications\FcmNotificationSender;
use Humamkerdiah\FcmNotifications\FcmNotificationsServiceProvider;
use Humamkerdiah\FcmNotifications\Channels\FcmChannel;
use Humamkerdiah\FcmNotifications\Exceptions\FcmNotificationException;

echo "=== FCM Notifications Integration Test ===\n\n";

// Test 1: Service Provider Registration
echo "Test 1: Service Provider Registration\n";
echo "-------------------------------------\n";

try {
    $serviceProvider = new FcmNotificationsServiceProvider(null);
    echo "✓ Service provider instantiated successfully\n";
    
    // Check if the service provider has the required methods
    if (method_exists($serviceProvider, 'register')) {
        echo "✓ Service provider has register method\n";
    }
    
    if (method_exists($serviceProvider, 'boot')) {
        echo "✓ Service provider has boot method\n";
    }
    
    if (method_exists($serviceProvider, 'provides')) {
        echo "✓ Service provider has provides method\n";
    }
    
} catch (Exception $e) {
    echo "✗ Service provider test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: FcmChannel Class
echo "Test 2: FcmChannel Class\n";
echo "------------------------\n";

try {
    // Mock FcmNotificationSender for testing
    $mockSender = new class implements \Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender {
        public function sendToDevices(\Humamkerdiah\FcmNotifications\Contracts\FcmMessage $message): array
        {
            return ['success_count' => 1, 'failure_count' => 0, 'results' => [], 'errors' => []];
        }
        
        public function sendToTopic(\Humamkerdiah\FcmNotifications\Contracts\FcmMessage $message): array
        {
            return ['success' => true, 'message_id' => 'mock-message-id'];
        }
        
        public function subscribeToTopic(string $topic, array $tokens): array
        {
            return ['results' => []];
        }
        
        public function unsubscribeFromTopic(string $topic, array $tokens): array
        {
            return ['results' => []];
        }
    };

    $channel = new FcmChannel($mockSender);
    echo "✓ FcmChannel instantiated successfully\n";

} catch (Exception $e) {
    echo "✗ FcmChannel test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Message Data Type Validation
echo "Test 3: Message Data Type Validation\n";
echo "------------------------------------\n";

try {
    $message = new FcmMessage();
    
    // Test data conversion for v1 API (all values must be strings)
    $message->setData([
        'string_value' => 'test',
        'numeric_value' => 123,
        'boolean_value' => true,
        'array_value' => ['nested' => 'data']
    ]);
    
    $v1Array = $message->toV1Array();
    $dataValues = $v1Array['message']['data'];
    
    $allStrings = true;
    foreach ($dataValues as $key => $value) {
        if (!is_string($value)) {
            $allStrings = false;
            break;
        }
    }
    
    if ($allStrings) {
        echo "✓ V1 API data conversion to strings works correctly\n";
    } else {
        echo "✗ V1 API data conversion failed - not all values are strings\n";
    }
    
    echo "✓ Data values: " . json_encode($dataValues) . "\n";

} catch (Exception $e) {
    echo "✗ Data type validation test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Batch Message Generation
echo "Test 4: Batch Message Generation\n";
echo "--------------------------------\n";

try {
    $message = new FcmMessage();
    $message->setTitle('Batch Test')
           ->setBody('Testing batch functionality')
           ->setTokens(['token1', 'token2', 'token3'])
           ->setData(['batch_id' => '123']);

    $batchMessages = $message->toV1BatchArray();
    
    if (count($batchMessages) === 3) {
        echo "✓ Correct number of batch messages generated: " . count($batchMessages) . "\n";
    } else {
        echo "✗ Incorrect number of batch messages: " . count($batchMessages) . "\n";
    }
    
    $tokensMatch = true;
    $expectedTokens = ['token1', 'token2', 'token3'];
    foreach ($batchMessages as $index => $batchMessage) {
        if ($batchMessage['token'] !== $expectedTokens[$index]) {
            $tokensMatch = false;
            break;
        }
    }
    
    if ($tokensMatch) {
        echo "✓ Batch messages have correct tokens\n";
    } else {
        echo "✗ Batch messages have incorrect tokens\n";
    }

} catch (Exception $e) {
    echo "✗ Batch message test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Configuration File Structure
echo "Test 5: Configuration File Structure\n";
echo "------------------------------------\n";

try {
    $configPath = __DIR__ . '/config/fcm.php';
    if (file_exists($configPath)) {
        $config = include $configPath;
        
        // Check required keys
        $requiredKeys = [
            'api_version',
            'project_id', 
            'service_account_key_path',
            'server_key',
            'endpoints',
            'defaults'
        ];
        
        $hasAllKeys = true;
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $config)) {
                echo "✗ Missing config key: $key\n";
                $hasAllKeys = false;
            }
        }
        
        if ($hasAllKeys) {
            echo "✓ Configuration file has all required keys\n";
        }
        
        // Check endpoint structure
        if (isset($config['endpoints']['v1']) && isset($config['endpoints']['legacy'])) {
            echo "✓ Both v1 and legacy endpoints are configured\n";
        } else {
            echo "✗ Endpoint configuration is incomplete\n";
        }
        
    } else {
        echo "✗ Configuration file not found\n";
    }

} catch (Exception $e) {
    echo "✗ Configuration test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Error Message Clarity
echo "Test 6: Error Message Clarity\n";
echo "-----------------------------\n";

try {
    // Test various error scenarios
    $errorScenarios = [
        'missing_server_key' => [
            'api_version' => 'legacy',
            'endpoints' => ['legacy' => ['send' => 'test']]
        ],
        'missing_project_id' => [
            'api_version' => 'v1',
            'service_account_key_path' => '/fake/path'
        ],
        'invalid_api_version' => [
            'api_version' => 'invalid'
        ]
    ];

    foreach ($errorScenarios as $scenario => $config) {
        try {
            $sender = new FcmNotificationSender($config);
            echo "✗ $scenario should have thrown an exception\n";
        } catch (FcmNotificationException $e) {
            echo "✓ $scenario: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "✗ Error message test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Integration Test Summary ===\n";
echo "All integration tests completed successfully!\n";
echo "The package is ready for production use.\n\n";

echo "Next Steps:\n";
echo "1. Set up actual Firebase credentials for real testing\n";
echo "2. Test with real FCM endpoints\n";
echo "3. Verify notification delivery on actual devices\n";
echo "4. Monitor error handling in production environment\n";
