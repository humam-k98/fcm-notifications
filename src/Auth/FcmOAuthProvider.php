<?php

namespace Humamkerdiah\FcmNotifications\Auth;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use Humamkerdiah\FcmNotifications\Exceptions\FcmNotificationException;

class FcmOAuthProvider
{
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    
    private array $config;
    private ?string $accessToken = null;
    private ?int $tokenExpiration = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get a valid access token for FCM HTTP v1 API
     */
    public function getAccessToken(): string
    {
        if ($this->accessToken && $this->tokenExpiration && time() < $this->tokenExpiration - 300) {
            return $this->accessToken;
        }

        $this->refreshAccessToken();

        return $this->accessToken;
    }

    /**
     * Create an authenticated HTTP client for FCM v1 API
     */
    public function createAuthenticatedClient(): Client
    {
        try {
            $credentials = $this->getCredentials();
            $middleware = new AuthTokenMiddleware($credentials);
            $stack = HandlerStack::create();
            $stack->push($middleware);

            return new Client([
                'handler' => $stack,
                'timeout' => $this->config['defaults']['timeout'] ?? 30.0,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (\Exception $e) {
            throw new FcmNotificationException(
                'Failed to create authenticated client: ' . $e->getMessage()
            );
        }
    }

    /**
     * Refresh the access token
     */
    private function refreshAccessToken(): void
    {
        try {
            $credentials = $this->getCredentials();
            $token = $credentials->fetchAuthToken();

            if (!isset($token['access_token'])) {
                throw new FcmNotificationException('Failed to obtain access token');
            }

            $this->accessToken = $token['access_token'];
            $this->tokenExpiration = time() + ($token['expires_in'] ?? 3600);
        } catch (\Exception $e) {
            throw new FcmNotificationException(
                'Failed to refresh access token: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get Google credentials based on configuration
     */
    private function getCredentials()
    {
        if (!empty($this->config['service_account_key_path'])) {
            // Use service account key file
            if (!file_exists($this->config['service_account_key_path'])) {
                throw new FcmNotificationException(
                    'Service account key file not found: ' . $this->config['service_account_key_path']
                );
            }

            return ApplicationDefaultCredentials::getCredentials(
                self::FCM_SCOPE,
                ['keyFile' => $this->config['service_account_key_path']]
            );
        }

        // Use application default credentials (from environment variable, etc.)
        return ApplicationDefaultCredentials::getCredentials(self::FCM_SCOPE);
    }
}
