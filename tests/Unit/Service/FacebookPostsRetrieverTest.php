<?php

namespace App\Test\Unit\Service;

use App\Service\FacebookPostsRetriever;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FacebookPostsRetrieverTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private FacebookPostsRetriever $facebookPostsRetriever;
    private string $pageId;
    private string $accessToken;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pageId = $_ENV['FACEBOOK_PAGE_ID'];
        $this->accessToken = $_ENV['FACEBOOK_ACCESS_TOKEN'];
        $this->facebookPostsRetriever = new FacebookPostsRetriever($this->logger, $this->httpClient, $this->pageId, $this->accessToken);
    }

    public function testRetrieveSuccess(): void
    {
        $responseData = [
            'data' => [
                'full_picture' => 'test picture',
                'message' => 'test message',
                'created_time' => '2024-11-12T12:30:01+0000',
                'id' => '123456789',
                'permalink_url' => 'https://www.facebook.com/1114065927392539/posts/1113981737400958',
            ],
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn($responseData);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                Request::METHOD_GET,
                "https://graph.facebook.com/v21.0/$this->pageId/feed",
                [
                    'query' => [
                        'access_token' => $this->accessToken,
                        'fields' => 'full_picture,message,created_time,permalink_url',
                    ],
                ]
            )
            ->willReturn($responseMock);

        $result = $this->facebookPostsRetriever->retrieve();

        $this->assertEquals($responseData['data'], $result);
    }

    public function testRetrieveFailureBadCredentials(): void
    {
        $exception = new \Exception('Failed to retrieve data');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to retrieve data');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to retrieve data');

        $this->facebookPostsRetriever->retrieve();
    }
}
