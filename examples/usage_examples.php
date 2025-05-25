<?php

// Example usage of the FCM Notifications package

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Humamkerdiah\FcmNotifications\FcmMessage;
use Humamkerdiah\FcmNotifications\Facades\FcmNotification;
use Illuminate\Support\Facades\Notification;

// Example 1: Send notification using Laravel's notification system
$user = User::find(1);
$user->notify(new WelcomeNotification($user));

// Example 2: Send to multiple users
$users = User::whereNotNull('fcm_token')->get();
Notification::send($users, new WelcomeNotification($users->first()));

// Example 3: Direct FCM usage - Send to specific devices
$message = (new FcmMessage())
    ->setTitle('Breaking News')
    ->setBody('Important update available!')
    ->setData([
        'category' => 'news',
        'priority' => 'high',
        'url' => 'https://example.com/news/123'
    ])
    ->setTokens([
        'device-token-1',
        'device-token-2',
        'device-token-3'
    ]);

try {
    $result = FcmNotification::sendToDevices($message);
    
    // v1 API response format
    if (config('fcm.api_version') === 'v1') {
        echo "Successfully sent to: " . $result['success_count'] . " devices\n";
        echo "Failed to send to: " . $result['failure_count'] . " devices\n";
        
        foreach ($result['errors'] as $error) {
            echo "Error for token {$error['token']}: {$error['error']}\n";
        }
    } else {
        // Legacy API response format
        echo "Success: " . $result['success'] . "\n";
        echo "Failure: " . $result['failure'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error sending notification: " . $e->getMessage();
}

// Example 4: Send to topic
$topicMessage = (new FcmMessage())
    ->setTitle('New Feature Available')
    ->setBody('Check out our latest update!')
    ->setData(['feature' => 'dark_mode'])
    ->setTopic('app_updates');

$result = FcmNotification::sendToTopic($topicMessage);

// Example 5: Topic management
try {
    // Subscribe users to a topic
    $deviceTokens = ['token1', 'token2', 'token3'];
    FcmNotification::subscribeToTopic('news_alerts', $deviceTokens);
    
    // Unsubscribe users from a topic
    FcmNotification::unsubscribeFromTopic('news_alerts', ['token1']);
    
} catch (\Exception $e) {
    echo "Topic management error: " . $e->getMessage();
}

// Example 6: Advanced message with custom data
$advancedMessage = (new FcmMessage())
    ->setTitle('Order Update')
    ->setBody('Your order #12345 has been shipped!')
    ->setData([
        'order_id' => '12345',
        'status' => 'shipped',
        'tracking_number' => 'TRK123456789',
        'estimated_delivery' => '2025-05-27',
        'action' => 'view_order',
        'deep_link' => 'myapp://orders/12345'
    ])
    ->setTokens(['user-device-token']);

FcmNotification::sendToDevices($advancedMessage);
