<?php

namespace App\Tests\Service;

use App\Service\SpotifyTokenService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SpotifyTokenServiceTest extends KernelTestCase
{
    public function testGetAccessTokenWithCode(): void
    {
        $mockResp = new MockResponse(json_encode([
            'access_token' => 'myToken',
            'token_type' => 'bearer',
            'scope' => 'myScope',
            'expires_in' => 60,
            'refresh_token' => 'myRefreshToken',
        ]));
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger);
        $res = $service->getAccessTokenWithCode('myCode');

        $this->assertEquals('myToken', $res->getAccessToken());
        $this->assertEquals('myRefreshToken', $res->getRefreshToken());
        $this->assertEquals(60, $res->getExpiresIn());
    }

    public function testGetAccessTokenWithCodeExceptionOnRequestFail(): void
    {
        $mockResp = new MockResponse('{"hello": "world"}', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger);
        $this->expectException(\Exception::class);
        $service->getAccessTokenWithCode('myCode');
    }

    public function testGetAccessTokenWithCodeExceptionOnInvalidResponse(): void
    {
        $mockResp = new MockResponse('<h1> Err </h1>');
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger);
        $this->expectException(\Exception::class);
        $service->getAccessTokenWithCode('myCode');
    }

    public function testGetAccessTokenWithRefreshToken(): void
    {
        $mockResp = new MockResponse(json_encode([
            'access_token' => 'myToken',
            'token_type' => 'bearer',
            'scope' => 'myScope',
            'expires_in' => 60,
            'refresh_token' => 'myRefreshToken2',
        ]));
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger);
        $res = $service->getAccessTokenWithRefreshToken('myRefreshToken');

        $this->assertEquals('myToken', $res->getAccessToken());
        $this->assertEquals('myRefreshToken2', $res->getRefreshToken());
        $this->assertEquals(60, $res->getExpiresIn());
    }

    public function testGetAccessTokenWithRefreshTokenWhenNoNewRefreshTokenIsGiven(): void
    {
        $mockResp = new MockResponse(json_encode([
            'access_token' => 'myToken',
            'token_type' => 'bearer',
            'scope' => 'myScope',
            'expires_in' => 60,
        ]));
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger);
        $res = $service->getAccessTokenWithRefreshToken('myRefreshToken');

        $this->assertEquals('myToken', $res->getAccessToken());
        $this->assertEquals('myRefreshToken', $res->getRefreshToken());
        $this->assertEquals(60, $res->getExpiresIn());
    }

    public function testGetAccessTokenWithRefreshTokenOnRequestFail(): void
    {
        $mockResp = new MockResponse('{"hello": "world"}', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger);
        $this->expectException(\Exception::class);
        $service->getAccessTokenWithRefreshToken('myRefreshToken');
    }

    public function testGetAccessTokenWithRefreshTokenOnInvalidResponse(): void
    {
        $mockResp = new MockResponse('<h1> Err </h1>');
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger);
        $this->expectException(\Exception::class);
        $service->getAccessTokenWithRefreshToken('myRefreshToken');
    }

    public function testGetClientTokenWithCache(): void
    {
        $httpClient = new MockHttpClient();
        $logger = $this->createMock(LoggerInterface::class);
        $cache = $this->createMock(FilesystemAdapter::class);
        $cache->expects(self::once())
            ->method('get')
            ->willReturn('test1');

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger, $cache);
        $accessToken = $service->getClientToken();

        $this->assertEquals('test1', $accessToken);
    }

    public function testGetClientTokenWithoutCache(): void
    {
        $mockResp = new MockResponse(json_encode([
            'access_token' => 'myToken1',
            'token_type' => 'bearer',
            'expires_in' => 60,
        ]));
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);

        $cache = new FilesystemAdapter(microtime(true));

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger, $cache);
        $accessToken = $service->getClientToken();

        $this->assertEquals('myToken1', $accessToken);
    }

    public function testGetClientTokenWithoutCacheOnRequestFail(): void
    {
        $mockResp = new MockResponse('{"hello": "world"}', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $cache = new FilesystemAdapter(microtime(true));

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger, $cache);
        $this->expectException(\Exception::class);
        $service->getClientToken();
    }

    public function testGetClientTokenWithoutCacheOnInvalidResponse(): void
    {
        $mockResp = new MockResponse('<h1> Err </h1>');
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $cache = new FilesystemAdapter(microtime(true));

        $service = new SpotifyTokenService($httpClient, 'http://example.com/auth', $logger, $cache);
        $this->expectException(\Exception::class);
        $service->getClientToken();
    }
}
