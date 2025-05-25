<?php

namespace Humamkerdiah\FcmNotifications;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Humamkerdiah\FcmNotifications\Auth\FcmOAuthProvider;
use Humamkerdiah\FcmNotifications\Contracts\FcmMessage;
use Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender as FcmNotificationSenderContract;
use Humamkerdiah\FcmNotifications\Exceptions\FcmNotificationException;

class FcmNotificationSender implements FcmNotificationSenderContract
{
    private Client $client;
    private array $config;
    private string $apiVersion;
    private ?FcmOAuthProvider $oauthProvider = null;

    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->config = $config;
        $this->apiVersion = $config['api_version'] ?? 'v1';
        
        if ($this->apiVersion === 'v1') {
            $this->oauthProvider = new FcmOAuthProvider($config);
            $this->client = $this->oauthProvider->createAuthenticatedClient();
        } else {
            // Legacy API setup
            $this->client = new Client([
                'headers' => [
                    'Authorization' => 'key=' . $config['server_key'],
                    'Content-Type' => 'application/json'
                ],
                'timeout' => $config['defaults']['timeout'] ?? 30.0,
            ]);
        }
    }

    public function sendToDevices(FcmMessage $message): array
    {
        if ($this->apiVersion === 'v1') {
            return $this->sendToDevicesV1($message);
        }

        return $this->sendToDevicesLegacy($message);
    }

    public function sendToTopic(FcmMessage $message): array
    {
        if ($this->apiVersion === 'v1') {
            return $this->sendToTopicV1($message);
        }

        return $this->sendToTopicLegacy($message);
    }

    public function subscribeToTopic(string $topic, array $tokens): array
    {
        // Topic subscription uses the same endpoint for both API versions
        try {
            $endpoint = $this->apiVersion === 'v1' 
                ? $this->config['endpoints']['v1']['topic_subscribe']
                : $this->config['endpoints']['legacy']['topic_subscribe'];

            $response = $this->client->post($endpoint, [
                'json' => [
                    'to' => '/topics/' . $topic,
                    'registration_tokens' => $tokens
                ],
                'headers' => $this->getTopicHeaders()
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (isset($result['error'])) {
                throw new FcmNotificationException(
                    'Failed to subscribe to topic',
                    $result
                );
            }

            return $result;
        } catch (GuzzleException $e) {
            throw new FcmNotificationException(
                'Failed to subscribe to topic: ' . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    public function unsubscribeFromTopic(string $topic, array $tokens): array
    {
        // Topic unsubscription uses the same endpoint for both API versions
        try {
            $endpoint = $this->apiVersion === 'v1' 
                ? $this->config['endpoints']['v1']['topic_unsubscribe']
                : $this->config['endpoints']['legacy']['topic_unsubscribe'];

            $response = $this->client->post($endpoint, [
                'json' => [
                    'to' => '/topics/' . $topic,
                    'registration_tokens' => $tokens
                ],
                'headers' => $this->getTopicHeaders()
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (isset($result['error'])) {
                throw new FcmNotificationException(
                    'Failed to unsubscribe from topic',
                    $result
                );
            }

            return $result;
        } catch (GuzzleException $e) {
            throw new FcmNotificationException(
                'Failed to unsubscribe from topic: ' . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Send to devices using FCM HTTP v1 API
     */
    private function sendToDevicesV1(FcmMessage $message): array
    {
        $tokens = $message->getTokens();
        if (empty($tokens)) {
            throw new FcmNotificationException('No tokens provided for device notification');
        }

        $endpoint = str_replace(
            '{project_id}', 
            $this->config['project_id'], 
            $this->config['endpoints']['v1']['send']
        );

        $results = [];
        $errors = [];

        // Send in batches to handle rate limits
        $batches = array_chunk($tokens, 500); // FCM v1 recommendation
        
        foreach ($batches as $batch) {
            $requests = [];
            
            foreach ($batch as $token) {
                $messageData = $message->toV1Array();
                $messageData['message']['token'] = $token;
                
                $requests[] = new Request('POST', $endpoint, [], json_encode($messageData));
            }

            $pool = new Pool($this->client, $requests, [
                'concurrency' => 5,
                'fulfilled' => function ($response, $index) use (&$results, $batch) {
                    $result = json_decode($response->getBody()->getContents(), true);
                    $results[] = [
                        'token' => $batch[$index],
                        'success' => true,
                        'message_id' => $result['name'] ?? null
                    ];
                },
                'rejected' => function ($reason, $index) use (&$errors, $batch) {
                    $errors[] = [
                        'token' => $batch[$index],
                        'success' => false,
                        'error' => $reason->getMessage()
                    ];
                }
            ]);

            $promise = $pool->promise();
            $promise->wait();
        }

        return [
            'success_count' => count($results),
            'failure_count' => count($errors),
            'results' => $results,
            'errors' => $errors
        ];
    }

    /**
     * Send to topic using FCM HTTP v1 API
     */
    private function sendToTopicV1(FcmMessage $message): array
    {
        if (!$message->getTopic()) {
            throw new FcmNotificationException('No topic provided for topic notification');
        }

        try {
            $endpoint = str_replace(
                '{project_id}', 
                $this->config['project_id'], 
                $this->config['endpoints']['v1']['send']
            );

            $messageData = $message->toV1Array();
            
            $response = $this->client->post($endpoint, [
                'json' => $messageData
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (!isset($result['name'])) {
                throw new FcmNotificationException(
                    'Failed to send to topic',
                    $result
                );
            }

            return [
                'success' => true,
                'message_id' => $result['name']
            ];
        } catch (GuzzleException $e) {
            throw new FcmNotificationException(
                'Failed to send notification to topic: ' . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Send to devices using legacy FCM API
     */
    private function sendToDevicesLegacy(FcmMessage $message): array
    {
        try {
            $response = $this->client->post($this->config['endpoints']['legacy']['send'], [
                'json' => $message->toArray()
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (isset($result['failure']) && $result['failure'] > 0) {
                throw new FcmNotificationException(
                    'Some messages failed to send',
                    $result
                );
            }

            return $result;
        } catch (GuzzleException $e) {
            throw new FcmNotificationException(
                'Failed to send notification: ' . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Send to topic using legacy FCM API
     */
    private function sendToTopicLegacy(FcmMessage $message): array
    {
        try {
            $response = $this->client->post($this->config['endpoints']['legacy']['send'], [
                'json' => $message->toArray()
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (isset($result['message_id']) === false) {
                throw new FcmNotificationException(
                    'Failed to send to topic',
                    $result
                );
            }

            return $result;
        } catch (GuzzleException $e) {
            throw new FcmNotificationException(
                'Failed to send notification to topic: ' . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get headers for topic operations
     */
    private function getTopicHeaders(): array
    {
        if ($this->apiVersion === 'v1') {
            return [
                'Authorization' => 'Bearer ' . $this->oauthProvider->getAccessToken(),
                'Content-Type' => 'application/json'
            ];
        }

        return [
            'Authorization' => 'key=' . $this->config['server_key'],
            'Content-Type' => 'application/json'
        ];
    }

    private function validateConfig(array $config): void
    {
        $apiVersion = $config['api_version'] ?? 'v1';

        if ($apiVersion === 'v1') {
            if (empty($config['project_id'])) {
                throw new FcmNotificationException('FCM project ID is required for v1 API');
            }

            if (empty($config['service_account_key_path']) && 
                !getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
                throw new FcmNotificationException(
                    'Service account key path or GOOGLE_APPLICATION_CREDENTIALS environment variable is required for v1 API'
                );
            }

            if (!isset($config['endpoints']['v1'])) {
                throw new FcmNotificationException('FCM v1 endpoints configuration is invalid');
            }
        } else {
            if (empty($config['server_key'])) {
                throw new FcmNotificationException('FCM server key is required for legacy API');
            }

            if (!isset($config['endpoints']['legacy'])) {
                throw new FcmNotificationException('FCM legacy endpoints configuration is invalid');
            }
        }
    }
}