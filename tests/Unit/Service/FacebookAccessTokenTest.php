<?php

namespace App\Test\Unit\Service;

use App\Service\FacebookAccessToken;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FacebookAccessTokenTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private FacebookAccessToken $facebookAccessToken;
    private string $appId;
    private string $appSecret;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->appId = $_ENV['FACEBOOK_APP_ID'];
        $this->appSecret = $_ENV['FACEBOOK_APP_SECRET'];
        $this->facebookAccessToken = new FacebookAccessToken($this->logger, $this->httpClient, $this->appId, $this->appSecret);
    }

    public function testGenerateSuccess(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn(['access_token' => 'test_token']);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                Request::METHOD_GET,
                'https://graph.facebook.com/oauth/access_token',
                [
                    'query' => [
                        'client_id' => $this->appId,
                        'client_secret' => $this->appSecret,
                        'grant_type' => 'client_credentials',
                    ],
                ]
            )
            ->willReturn($responseMock);

        $result = $this->facebookAccessToken->generate();

        $this->assertEquals('test_token', $result);
    }

    public function testGenerateFailureBadCredentials(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn(['access_token' => '']);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                Request::METHOD_GET,
                'https://graph.facebook.com/oauth/access_token'
            )
            ->willReturn($responseMock);

        $result = $this->facebookAccessToken->generate();

        $this->assertEquals('', $result);
    }
}
