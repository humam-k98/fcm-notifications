# FCM Notifications - Migration Guide to HTTP v1 API

This package now supports both the legacy FCM HTTP API and the new FCM HTTP v1 API. The v1 API is the recommended approach going forward.

## What's New in v1 API

- **OAuth 2.0 Authentication**: Uses service account keys instead of server keys
- **Better Security**: More secure authentication mechanism
- **Improved Error Handling**: Better error responses and debugging information
- **Future-Proof**: Google's recommended approach for FCM

## Migration Steps

### 1. Update Dependencies

Run composer update to get the new dependencies:

```bash
composer update
```

### 2. Get Service Account Key

1. Go to the [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Go to Project Settings > Service Accounts
4. Click "Generate new private key"
5. Download the JSON file and save it securely

### 3. Update Environment Variables

Add these new environment variables to your `.env` file:

```env
# FCM HTTP v1 API (Recommended)
FCM_API_VERSION=v1
FCM_PROJECT_ID=your-firebase-project-id
FCM_SERVICE_ACCOUNT_KEY_PATH=/path/to/your/service-account-key.json

# Legacy API (Still supported but deprecated)
# FCM_API_VERSION=legacy
# FCM_SERVER_KEY=your-server-key
```

### 4. Publish Updated Config (Optional)

If you want to republish the config file:

```bash
php artisan vendor:publish --tag=fcm-config --force
```

## Usage Examples

The API usage remains the same - the package handles the different API versions internally:

```php
use Humamkerdiah\FcmNotifications\FcmMessage;

// Create notification
$message = (new FcmMessage())
    ->setTitle('Hello World')
    ->setBody('This is a test notification')
    ->setData(['key' => 'value'])
    ->setTokens(['device-token-1', 'device-token-2']);

// Send using your notification class
class TestNotification extends Notification
{
    public function via($notifiable)
    {
        return ['fcm'];
    }
    
    public function toFcm($notifiable)
    {
        return (new FcmMessage())
            ->setTitle('Test')
            ->setBody('Hello from FCM v1!')
            ->setData(['user_id' => $notifiable->id]);
    }
}
```

## Configuration Options

### API Version Selection

```php
// config/fcm.php
'api_version' => env('FCM_API_VERSION', 'v1'), // 'v1' or 'legacy'
```

### v1 API Configuration

```php
// config/fcm.php
'project_id' => env('FCM_PROJECT_ID', ''),
'service_account_key_path' => env('FCM_SERVICE_ACCOUNT_KEY_PATH', ''),
```

### Legacy API Configuration

```php
// config/fcm.php
'server_key' => env('FCM_SERVER_KEY', ''),
```

## Error Handling

The v1 API provides better error handling and response format:

```php
try {
    $result = $fcmSender->sendToDevices($message);
    
    // v1 API response format
    echo "Success count: " . $result['success_count'];
    echo "Failure count: " . $result['failure_count'];
    
    foreach ($result['errors'] as $error) {
        echo "Failed token: " . $error['token'];
        echo "Error: " . $error['error'];
    }
    
} catch (FcmNotificationException $e) {
    // Handle exceptions
    echo "Error: " . $e->getMessage();
}
```

## Environment Variable Setup

You can also use the `GOOGLE_APPLICATION_CREDENTIALS` environment variable instead of specifying the path in the config:

```env
GOOGLE_APPLICATION_CREDENTIALS=/path/to/your/service-account-key.json
FCM_API_VERSION=v1
FCM_PROJECT_ID=your-firebase-project-id
```

## Backwards Compatibility

The package maintains full backwards compatibility. If you don't update your configuration, it will continue to use the legacy API. However, we recommend migrating to the v1 API for better security and future support.

## Security Notes

- Store your service account key file securely
- Never commit service account keys to version control
- Use environment variables for sensitive configuration
- Consider using Google Cloud's secret management for production environments
