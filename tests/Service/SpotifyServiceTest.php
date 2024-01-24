<?php

namespace App\Tests\Service;

use App\Service\SpotifyService;
use App\Service\SpotifyTokenService;
use JsonMapper\JsonMapperFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SpotifyServiceTest extends KernelTestCase
{
    public function testGetArtist(): void
    {
        $mockResp = new MockResponse('
			{
			  "external_urls": {
				"spotify": "https://open.spotify.com/artist/0TnOYISbd1XYRBk9myaseg"
			  },
			  "followers": {
				"href": null,
				"total": 10213193
			  },
			  "genres": [
				"dance pop",
				"miami hip hop",
				"pop"
			  ],
			  "href": "https://api.spotify.com/v1/artists/0TnOYISbd1XYRBk9myaseg?locale=en-US%2Cen%3Bq%3D0.5",
			  "id": "0TnOYISbd1XYRBk9myaseg",
			  "images": [
				{
				  "url": "https://i.scdn.co/image/ab6761610000e5ebee07b5820dd91d15d397e29c",
				  "height": 640,
				  "width": 640
				},
				{
				  "url": "https://i.scdn.co/image/ab67616100005174ee07b5820dd91d15d397e29c",
				  "height": 320,
				  "width": 320
				},
				{
				  "url": "https://i.scdn.co/image/ab6761610000f178ee07b5820dd91d15d397e29c",
				  "height": 160,
				  "width": 160
				}
			  ],
			  "name": "Pitbull",
			  "popularity": 82,
			  "type": "artist",
			  "uri": "spotify:artist:0TnOYISbd1XYRBk9myaseg"
			}
		');
        $httpClient = new MockHttpClient($mockResp);

        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $spotifyTokenServices = $this->createMock(SpotifyTokenService::class);
        $spotifyTokenServices->expects(self::once())
            ->method('getClientToken')
            ->willReturn('token');

        $logger = $this->createMock(LoggerInterface::class);

        $spotifyService = new SpotifyService($httpClient, $jsonMapper, $spotifyTokenServices, $logger);
        $artist = $spotifyService->getArtist('artistSpotifyId');

        $this->assertEquals('0TnOYISbd1XYRBk9myaseg', $artist->id);
        $this->assertEquals('Pitbull', $artist->name);
        $this->assertEquals(3, count($artist->images));
    }

    public function testGetArtistThrowsOnResponseException(): void
    {
        $mockResp = new MockResponse('{"hello": "world"}', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResp);

        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $spotifyTokenServices = $this->createMock(SpotifyTokenService::class);
        $spotifyTokenServices->expects(self::once())
            ->method('getClientToken')
            ->willReturn('token');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $spotifyService = new SpotifyService($httpClient, $jsonMapper, $spotifyTokenServices, $logger);
        $this->expectException(\Exception::class);
        $spotifyService->getArtist('artistSpotifyId');
    }

    public function testGetArtistThrowsOnUnexpectedResponsePayload(): void
    {
        $mockResp = new MockResponse('<h1>Error 500</h1>');
        $httpClient = new MockHttpClient($mockResp);

        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $spotifyTokenServices = $this->createMock(SpotifyTokenService::class);
        $spotifyTokenServices->expects(self::once())
            ->method('getClientToken')
            ->willReturn('token');

        $logger = $this->createMock(LoggerInterface::class);

        $spotifyService = new SpotifyService($httpClient, $jsonMapper, $spotifyTokenServices, $logger);
        $this->expectException(\Exception::class);
        $spotifyService->getArtist('artistSpotifyId');
    }

    public function testGetUserProfile(): void
    {
        $mockResp = new MockResponse('
			{
			  "display_name": "Hristo",
			  "external_urls": {
				"spotify": "https://open.spotify.com/user/x"
			  },
			  "href": "https://api.spotify.com/v1/users/x",
			  "id": "x",
			  "images": [
				{
				  "url": "https://example.com/image.png",
				  "height": 64,
				  "width": 64
				},
				{
				  "url": "https://example.com/image.png",
				  "height": 300,
				  "width": 300
				}
			  ],
			  "type": "user",
			  "uri": "spotify:user:x",
			  "followers": {
				"href": null,
				"total": 256
			  }
			}
		');
        $httpClient = new MockHttpClient($mockResp);

        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $spotifyTokenServices = $this->createMock(SpotifyTokenService::class);
        $spotifyTokenServices->expects(self::once())
            ->method('getClientToken')
            ->willReturn('token');

        $logger = $this->createMock(LoggerInterface::class);

        $spotifyService = new SpotifyService($httpClient, $jsonMapper, $spotifyTokenServices, $logger);
        $profile = $spotifyService->getUserProfile('userSpotifyId');

        $this->assertEquals('x', $profile->id);
        $this->assertEquals('Hristo', $profile->display_name);
        $this->assertEquals(2, count($profile->images));
    }

    public function testGetUesrProfileExceptionWhenRequestFails(): void
    {
        $mockResp = new MockResponse('{"hello": "world"}', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResp);

        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $spotifyTokenServices = $this->createMock(SpotifyTokenService::class);
        $spotifyTokenServices->expects(self::once())
            ->method('getClientToken')
            ->willReturn('token');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $spotifyService = new SpotifyService($httpClient, $jsonMapper, $spotifyTokenServices, $logger);
        $this->expectException(\Exception::class);
        $spotifyService->getUserProfile('userSpotifyId');
    }

    public function testGetUserProfileExceptionWhenMalformedData(): void
    {
        $mockResp = new MockResponse('<h1>Error 500</h1>');
        $httpClient = new MockHttpClient($mockResp);

        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $spotifyTokenServices = $this->createMock(SpotifyTokenService::class);
        $spotifyTokenServices->expects(self::once())
            ->method('getClientToken')
            ->willReturn('token');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $spotifyService = new SpotifyService($httpClient, $jsonMapper, $spotifyTokenServices, $logger);
        $this->expectException(\Exception::class);
        $spotifyService->getUserProfile('userSpotifyId');
    }
}
