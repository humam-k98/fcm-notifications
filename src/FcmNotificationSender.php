<?php

namespace Humamkerdiah\FcmNotifications;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Humamkerdiah\FcmNotifications\Contracts\FcmMessage;
use Humamkerdiah\FcmNotifications\Contracts\FcmNotificationSender as FcmNotificationSenderContract;
use Humamkerdiah\FcmNotifications\Exceptions\FcmNotificationException;

class FcmNotificationSender implements FcmNotificationSenderContract
{
    private Client $client;
    private array $config;

    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->config = $config;
        $this->client = new Client([
            'headers' => [
                'Authorization' => 'key=' . $config['server_key'],
                'Content-Type' => 'application/json'
            ],
            'timeout' => $config['defaults']['timeout'] ?? 30.0,
        ]);
    }

    public function sendToDevices(FcmMessage $message): array
    {
        try {
            $response = $this->client->post($this->config['endpoints']['send'], [
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

    public function sendToTopic(FcmMessage $message): array
    {
        try {
            $response = $this->client->post($this->config['endpoints']['send'], [
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

    public function subscribeToTopic(string $topic, array $tokens): array
    {
        try {
            $response = $this->client->post($this->config['endpoints']['topic_subscribe'], [
                'json' => [
                    'to' => '/topics/' . $topic,
                    'registration_tokens' => $tokens
                ]
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
        try {
            $response = $this->client->post($this->config['endpoints']['topic_unsubscribe'], [
                'json' => [
                    'to' => '/topics/' . $topic,
                    'registration_tokens' => $tokens
                ]
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

    private function validateConfig(array $config): void
    {
        if (empty($config['server_key'])) {
            throw new FcmNotificationException('FCM server key is required');
        }

        if (empty($config['endpoints']) || 
            !isset($config['endpoints']['send']) ||
            !isset($config['endpoints']['topic_subscribe']) ||
            !isset($config['endpoints']['topic_unsubscribe'])) {
            throw new FcmNotificationException('FCM endpoints configuration is invalid');
        }
    }
}