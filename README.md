# Laravel FCM Notifications

A Laravel package for sending Firebase Cloud Messaging (FCM) notifications with support for both legacy API and the new FCM HTTP v1 API.

## About
Laravel FCM Notifications is a powerful package for integrating Firebase Cloud Messaging (FCM) into your Laravel application. It supports both the legacy FCM API and the new FCM HTTP v1 API (recommended). Send push notifications to individual users or groups, manage topics, and more with simple configuration and seamless integration.

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [FCM HTTP v1 API (Recommended)](#fcm-http-v1-api-recommended)
- [Legacy API Support](#legacy-api-support)
- [Usage](#usage)
- [Migration Guide](#migration-guide)
- [License](#license)

## Features
- ✅ **FCM HTTP v1 API Support** - OAuth 2.0 authentication with service account keys
- ✅ **Legacy API Support** - Backwards compatibility with server key authentication
- ✅ Easy integration with Laravel notifications
- ✅ Send notifications to single or multiple users
- ✅ Topic-based messaging for targeted notifications
- ✅ Batch processing for multiple device tokens
- ✅ Comprehensive error handling and retry mechanisms
- ✅ Support for both mobile and web push notifications
- ✅ Laravel 8, 9, 10, and 11 compatibility

## Installation

You can install the package via composer:

```bash
composer require humamkerdiah/fcm-notifications
```

## Configuration

After installation, publish the config file:

```bash
php artisan vendor:publish --tag=fcm-config
```

This will create a `config/fcm.php` file where you can configure your FCM settings.

## FCM HTTP v1 API (Recommended)

The v1 API uses OAuth 2.0 authentication and is Google's recommended approach for FCM.

### Setup for v1 API

1. **Get Service Account Key:**
   - Go to [Firebase Console](https://console.firebase.google.com/)
   - Select your project → Project Settings → Service Accounts
   - Click "Generate new private key" and download the JSON file

2. **Update your `.env` file:**

```env
FCM_API_VERSION=v1
FCM_PROJECT_ID=your-firebase-project-id
FCM_SERVICE_ACCOUNT_KEY_PATH=/path/to/your/service-account-key.json
```

Alternatively, you can use the `GOOGLE_APPLICATION_CREDENTIALS` environment variable:

```env
GOOGLE_APPLICATION_CREDENTIALS=/path/to/your/service-account-key.json
FCM_API_VERSION=v1
FCM_PROJECT_ID=your-firebase-project-id
```

## Legacy API Support

The package still supports the legacy server key authentication for backwards compatibility.

### Setup for Legacy API

Update your `.env` file:

```env
FCM_API_VERSION=legacy
FCM_SERVER_KEY=your-server-key-here
```

## Usage

### Using Laravel Notifications

There are two ways to use FCM notifications in your Laravel application:

#### 1. Using the FCM Channel Class

```php
use Humamkerdiah\FcmNotifications\Channels\FcmChannel;

class NewMessage extends Notification
{
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return (new FcmMessage())
            ->setTitle('New Message')
            ->setBody('You have a new message!')
            ->setData([
                'message_id' => '123',
                'url' => '/messages/123'
            ]);
    }
}
```

#### 2. Using the Channel Name

```php
class NewMessage extends Notification
{
    public function via($notifiable)
    {
        return ['fcm'];  // Use the registered channel name
    }

    public function toFcm($notifiable)
    {
        return (new FcmMessage())
            // ... same as above
    }
}
```

### Setting Up Your Model

Make your model use FCM notifications by implementing the `routeNotificationForFcm` method:

```php
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    public function routeNotificationForFcm($notification)
    {
        return $this->device_token; // Return a single token
        // Or return multiple tokens:
        // return $this->device_tokens->pluck('token')->toArray();
    }
}
```

### Sending Notifications

```php
// To a single user
$user->notify(new NewMessage());

// To multiple users
Notification::send($users, new NewMessage());

// Using the facade for direct FCM operations
use Humamkerdiah\FcmNotifications\Facades\FcmNotification;

// Send to specific devices
$message = new FcmMessage();
$message->setTitle('Hello')
       ->setBody('This is a test notification')
       ->setData(['key' => 'value'])
       ->setTokens(['device-token-1', 'device-token-2']);

FcmNotification::sendToDevices($message);

// Or send to a topic
$message->setTopic('news');
FcmNotification::sendToTopic($message);
```

### Topic Management

```php
// Subscribe tokens to a topic
FcmNotification::subscribeToTopic('news', ['device-token-1', 'device-token-2']);

// Unsubscribe tokens from a topic
FcmNotification::unsubscribeFromTopic('news', ['device-token-1', 'device-token-2']);
```

## Error Handling

The package provides comprehensive error handling for both API versions:

### v1 API Response Format

```php
try {
    $result = FcmNotification::sendToDevices($message);
    
    echo "Success count: " . $result['success_count'];
    echo "Failure count: " . $result['failure_count'];
    
    // Handle successful sends
    foreach ($result['results'] as $result) {
        echo "Token: " . $result['token'] . " - Message ID: " . $result['message_id'];
    }
    
    // Handle failures
    foreach ($result['errors'] as $error) {
        echo "Failed token: " . $error['token'] . " - Error: " . $error['error'];
    }
    
} catch (\Humamkerdiah\FcmNotifications\Exceptions\FcmNotificationException $e) {
    Log::error('FCM Notification failed: ' . $e->getMessage());
}
```

### Legacy API Response Format

```php
try {
    $result = FcmNotification::sendToDevices($message);
    
    echo "Success: " . $result['success'];
    echo "Failure: " . $result['failure'];
    echo "Canonical IDs: " . $result['canonical_ids'];
    
} catch (\Exception $e) {
    Log::error('FCM Notification failed: ' . $e->getMessage());
}
```

## Migration Guide

If you're upgrading from the legacy API to v1 API, see our [Migration Guide](MIGRATION_GUIDE.md) for detailed instructions.

## Testing

When testing your application, you can mock the FCM notifications:

```php
use Mockery;
use Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender;

public function test_it_sends_fcm_notification()
{
    $mock = Mockery::mock(FcmNotificationSender::class);
    
    // For v1 API
    $mock->shouldReceive('sendToDevices')->once()->andReturn([
        'success_count' => 1,
        'failure_count' => 0,
        'results' => [['token' => 'test-token', 'success' => true, 'message_id' => 'test-message-id']],
        'errors' => []
    ]);
    
    $this->app->instance(FcmNotificationSender::class, $mock);

    // Your test code here
}
```

## Requirements

- PHP 7.4 or higher
- Laravel 8.0 or higher
- GuzzleHTTP 7.0 or higher
- Google Auth Library (for v1 API)

## Security Considerations

- Store service account keys securely and never commit them to version control
- Use environment variables for all sensitive configuration
- Consider using Google Cloud Secret Manager in production
- Regularly rotate your service account keys

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.