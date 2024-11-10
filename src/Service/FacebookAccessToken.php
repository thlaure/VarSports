<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookAccessToken
{
    public function __construct(
        private LoggerInterface $logger,
        private HttpClientInterface $httpClient,
        private string $appId,
        private string $appSecret,
    ) {
    }

    public function generate(): string
    {
        try {
            $response = $this->httpClient->request(Request::METHOD_GET, 'https://graph.facebook.com/oauth/access_token', [
                'query' => [
                    'client_id' => $this->appId,
                    'client_secret' => $this->appSecret,
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $data = $response->toArray();

            return $data['access_token'] ?? '';
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
