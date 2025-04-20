<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Server Key
    |--------------------------------------------------------------------------
    |
    | Server key for Firebase Cloud Messaging from firebase console
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
        'send' => 'https://fcm.googleapis.com/fcm/send',
        'topic_subscribe' => 'https://iid.googleapis.com/iid/v1:batchAdd',
        'topic_unsubscribe' => 'https://iid.googleapis.com/iid/v1:batchRemove',
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