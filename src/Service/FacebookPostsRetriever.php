<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookPostsRetriever
{
    public function __construct(
        private LoggerInterface $logger,
        private HttpClientInterface $httpClient,
        private string $pageId,
        private string $accessToken,
    ) {
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function retrieve(): array
    {
        try {
            $response = $this->httpClient->request(Request::METHOD_GET, "https://graph.facebook.com/v21.0/$this->pageId/feed", [
                'query' => [
                    'access_token' => $this->accessToken,
                    'fields' => 'full_picture,message,created_time,permalink_url',
                ],
            ]);

            $data = $response->toArray();

            return $data['data'] ?? [];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
