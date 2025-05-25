<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FCM API Version
    |--------------------------------------------------------------------------
    |
    | Choose between 'legacy' and 'v1' API versions
    | v1 is the recommended version using OAuth 2.0 authentication
    | legacy uses the deprecated server key authentication
    |
    */
    'api_version' => env('FCM_API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID (Required for v1 API)
    |--------------------------------------------------------------------------
    |
    | Your Firebase project ID from the Firebase console
    | https://console.firebase.google.com/
    |
    */
    'project_id' => env('FCM_PROJECT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Service Account Key File Path (Required for v1 API)
    |--------------------------------------------------------------------------
    |
    | Path to your Firebase service account key JSON file
    | Download from Firebase Console > Project Settings > Service Accounts
    |
    */
    'service_account_key_path' => env('FCM_SERVICE_ACCOUNT_KEY_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Server Key (Legacy API)
    |--------------------------------------------------------------------------
    |
    | Server key for Firebase Cloud Messaging from firebase console
    | Only used when api_version is set to 'legacy'
    | https://console.firebase.google.com/
    |
    */
    'server_key' => env('FCM_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging API Endpoints
    |--------------------------------------------------------------------------
    |
    | FCM API endpoints used for different operations
    |
    */
    'endpoints' => [
        'v1' => [
            'send' => 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send',
            'topic_subscribe' => 'https://iid.googleapis.com/iid/v1:batchAdd',
            'topic_unsubscribe' => 'https://iid.googleapis.com/iid/v1:batchRemove',
        ],
        'legacy' => [
            'send' => 'https://fcm.googleapis.com/fcm/send',
            'topic_subscribe' => 'https://iid.googleapis.com/iid/v1:batchAdd',
            'topic_unsubscribe' => 'https://iid.googleapis.com/iid/v1:batchRemove',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Notification Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for notifications that can be overridden
    |
    */
    'defaults' => [
        'timeout' => 30.0, // Request timeout in seconds
        'retry_attempts' => 3, // Number of retry attempts for failed requests
        'retry_interval' => 1000, // Retry interval in milliseconds
    ],
];