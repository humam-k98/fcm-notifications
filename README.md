# Laravel FCM Notifications

A Laravel package for sending Firebase Cloud Messaging (FCM) notifications.

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

This will create a `config/fcm.php` file. Update your `.env` file with your FCM server key:

```env
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

The package throws exceptions for various error cases:
- Invalid server key
- Network errors
- Invalid message format
- Invalid device tokens

Make sure to wrap your calls in try-catch blocks:

```php
try {
    $user->notify(new NewMessage());
} catch (\Exception $e) {
    // Handle the error
    Log::error('FCM Notification failed: ' . $e->getMessage());
}
```

## Testing

When testing your application, you can mock the FCM notifications:

```php
use Mockery;
use Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender;

public function test_it_sends_fcm_notification()
{
    $mock = Mockery::mock(FcmNotificationSender::class);
    $mock->shouldReceive('sendToDevices')->once()->andReturn(['message_id' => '1:234']);
    $this->app->instance(FcmNotificationSender::class, $mock);

    // Your test code here
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.