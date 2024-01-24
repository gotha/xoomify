<?php

namespace App\Tests\Service;

use App\Service\SpotifyUserService;
use JsonMapper\JsonMapperFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SpotifyUserServiceTest extends KernelTestCase
{
    public function testGetCurrentUser(): void
    {
        $mockResp = new MockResponse('
			{
			  "country": "BG",
			  "display_name": "Hristo",
			  "email": "example@example.com",
			  "explicit_content": {
				"filter_enabled": false,
				"filter_locked": false
			  },
			  "external_urls": {
				"spotify": "string"
			  },
			  "followers": {
				"href": "string",
				"total": 0
			  },
			  "href": "string",
			  "id": "id1",
			  "images": [
				{
				  "url": "https://i.scdn.co/image/ab67616d00001e02ff9ca10b55ce82ae553c8228",
				  "height": 300,
				  "width": 300
				}
			  ],
			  "product": "string",
			  "type": "string",
			  "uri": "string"
			}
		');
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $service = new SpotifyUserService($httpClient, $logger, $jsonMapper);
        $profile = $service->getCurrentUser('acessToken1');

        $this->assertEquals('Hristo', $profile->display_name);
        $this->assertEquals('example@example.com', $profile->email);
        $this->assertEquals('id1', $profile->id);
    }

    public function testGetCurrentUserOnRequestFail(): void
    {
        $mockResp = new MockResponse('{"hi": true}', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $service = new SpotifyUserService($httpClient, $logger, $jsonMapper);
        $this->expectException(\Exception::class);
        $service->getCurrentUser('acessToken1');
    }

    public function testGetCurrentUserOnInvalidReponsePayload(): void
    {
        $mockResp = new MockResponse('<h1>Err</h1>');
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $service = new SpotifyUserService($httpClient, $logger, $jsonMapper);
        $this->expectException(\Exception::class);
        $service->getCurrentUser('acessToken1');
    }

    public function testGetRecentlyPlayedSongs(): void
    {
        $mockResp = new MockResponse('
			{
			  "items": [
				{
				  "track": {
					"album": {
					  "album_type": "album",
					  "artists": [
						{
						  "external_urls": {
							"spotify": "https://open.spotify.com/artist/2pAajGWerK3ghwToNWFENS"
						  },
						  "href": "https://api.spotify.com/v1/artists/2pAajGWerK3ghwToNWFENS",
						  "id": "2pAajGWerK3ghwToNWFENS",
						  "name": "Puscifer",
						  "type": "artist",
						  "uri": "spotify:artist:2pAajGWerK3ghwToNWFENS"
						}
					  ],
					  "available_markets": [ "AR", "AU", "AT", "BE", "BO", "BR", "BG", "CA", "CL", "CO", "CR", "CY", "CZ", "DK", "DO", "DE", "EC", "EE", "SV", "FI", "FR", "GR", "GT", "HN", "HK", "HU", "IS", "IE", "IT", "LV", "LT", "LU", "MY", "MT", "MX", "NL", "NZ", "NI", "NO", "PA", "PY", "PE", "PH", "PL", "PT", "SG", "SK", "ES", "SE", "CH", "TW", "TR", "UY", "US", "GB", "AD", "LI", "MC", "ID", "JP", "TH", "VN", "RO", "IL", "ZA", "SA", "AE", "BH", "QA", "OM", "KW", "EG", "MA", "DZ", "TN", "LB", "JO", "PS", "IN", "BY", "KZ", "MD", "UA", "AL", "BA", "HR", "ME", "MK", "RS", "SI", "KR", "BD", "PK", "LK", "GH", "KE", "NG", "TZ", "UG", "AG", "AM", "BS", "BB", "BZ", "BT", "BW", "BF", "CV", "CW", "DM", "FJ", "GM", "GE", "GD", "GW", "GY", "HT", "JM", "KI", "LS", "LR", "MW", "MV", "ML", "MH", "FM", "NA", "NR", "NE", "PW", "PG", "WS", "SM", "ST", "SN", "SC", "SL", "SB", "KN", "LC", "VC", "SR", "TL", "TO", "TT", "TV", "VU", "AZ", "BN", "BI", "KH", "CM", "TD", "KM", "GQ", "SZ", "GA", "GN", "KG", "LA", "MO", "MR", "MN", "NP", "RW", "TG", "UZ", "ZW", "BJ", "MG", "MU", "MZ", "AO", "CI", "DJ", "ZM", "CD", "CG", "IQ", "LY", "TJ", "VE", "ET", "XK" ],
					  "external_urls": {
						"spotify": "https://open.spotify.com/album/7wJ7Yxbq1B2rSILxUyxSTJ"
					  },
					  "href": "https://api.spotify.com/v1/albums/7wJ7Yxbq1B2rSILxUyxSTJ",
					  "id": "7wJ7Yxbq1B2rSILxUyxSTJ",
					  "images": [
						{
						  "height": 640,
						  "url": "https://i.scdn.co/image/ab67616d0000b273df442b34e8d5b293d2664c9c",
						  "width": 640
						},
						{
						  "height": 300,
						  "url": "https://i.scdn.co/image/ab67616d00001e02df442b34e8d5b293d2664c9c",
						  "width": 300
						},
						{
						  "height": 64,
						  "url": "https://i.scdn.co/image/ab67616d00004851df442b34e8d5b293d2664c9c",
						  "width": 64
						}
					  ],
					  "name": "Global Probing",
					  "release_date": "2023-11-17",
					  "release_date_precision": "day",
					  "total_tracks": 16,
					  "type": "album",
					  "uri": "spotify:album:7wJ7Yxbq1B2rSILxUyxSTJ"
					},
					"artists": [
					  {
						"external_urls": {
						  "spotify": "https://open.spotify.com/artist/2pAajGWerK3ghwToNWFENS"
						},
						"href": "https://api.spotify.com/v1/artists/2pAajGWerK3ghwToNWFENS",
						"id": "2pAajGWerK3ghwToNWFENS",
						"name": "Puscifer",
						"type": "artist",
						"uri": "spotify:artist:2pAajGWerK3ghwToNWFENS"
					  }
					],
					"available_markets": [ "AR", "AU", "AT", "BE", "BO", "BR", "BG", "CA", "CL", "CO", "CR", "CY", "CZ", "DK", "DO", "DE", "EC", "EE", "SV", "FI", "FR", "GR", "GT", "HN", "HK", "HU", "IS", "IE", "IT", "LV", "LT", "LU", "MY", "MT", "MX", "NL", "NZ", "NI", "NO", "PA", "PY", "PE", "PH", "PL", "PT", "SG", "SK", "ES", "SE", "CH", "TW", "TR", "UY", "US", "GB", "AD", "LI", "MC", "ID", "JP", "TH", "VN", "RO", "IL", "ZA", "SA", "AE", "BH", "QA", "OM", "KW", "EG", "MA", "DZ", "TN", "LB", "JO", "PS", "IN", "BY", "KZ", "MD", "UA", "AL", "BA", "HR", "ME", "MK", "RS", "SI", "KR", "BD", "PK", "LK", "GH", "KE", "NG", "TZ", "UG", "AG", "AM", "BS", "BB", "BZ", "BT", "BW", "BF", "CV", "CW", "DM", "FJ", "GM", "GE", "GD", "GW", "GY", "HT", "JM", "KI", "LS", "LR", "MW", "MV", "ML", "MH", "FM", "NA", "NR", "NE", "PW", "PG", "WS", "SM", "ST", "SN", "SC", "SL", "SB", "KN", "LC", "VC", "SR", "TL", "TO", "TT", "TV", "VU", "AZ", "BN", "BI", "KH", "CM", "TD", "KM", "GQ", "SZ", "GA", "GN", "KG", "LA", "MO", "MR", "MN", "NP", "RW", "TG", "UZ", "ZW", "BJ", "MG", "MU", "MZ", "AO", "CI", "DJ", "ZM", "CD", "CG", "IQ", "LY", "TJ", "VE", "ET", "XK"
					],
					"disc_number": 1,
					"duration_ms": 309808,
					"explicit": true,
					"external_ids": {
					  "isrc": "QMEU32311747"
					},
					"external_urls": {
					  "spotify": "https://open.spotify.com/track/2GLa6pL4qWfZo9UN6yboQJ"
					},
					"href": "https://api.spotify.com/v1/tracks/2GLa6pL4qWfZo9UN6yboQJ",
					"id": "2GLa6pL4qWfZo9UN6yboQJ",
					"is_local": false,
					"name": "Bullet Train to Iowa",
					"popularity": 36,
					"preview_url": "https://p.scdn.co/mp3-preview/9ae4ede575d5d7fa0ff87a9986f6db8a28673ccd?cid=c71a11758e6a43a3bbc01dbf843695d9",
					"track_number": 12,
					"type": "track",
					"uri": "spotify:track:2GLa6pL4qWfZo9UN6yboQJ"
				  },
				  "played_at": "2024-01-24T09:02:32.463Z",
				  "context": {
					"type": "album",
					"href": "https://api.spotify.com/v1/albums/7wJ7Yxbq1B2rSILxUyxSTJ",
					"external_urls": {
					  "spotify": "https://open.spotify.com/album/7wJ7Yxbq1B2rSILxUyxSTJ"
					},
					"uri": "spotify:album:7wJ7Yxbq1B2rSILxUyxSTJ"
				  }
				},
				{
				  "track": {
					"album": {
					  "album_type": "album",
					  "artists": [
						{
						  "external_urls": {
							"spotify": "https://open.spotify.com/artist/2pAajGWerK3ghwToNWFENS"
						  },
						  "href": "https://api.spotify.com/v1/artists/2pAajGWerK3ghwToNWFENS",
						  "id": "2pAajGWerK3ghwToNWFENS",
						  "name": "Puscifer",
						  "type": "artist",
						  "uri": "spotify:artist:2pAajGWerK3ghwToNWFENS"
						}
					  ],
					  "available_markets": [ "AR", "AU", "AT", "BE", "BO", "BR", "BG", "CA", "CL", "CO", "CR", "CY", "CZ", "DK", "DO", "DE", "EC", "EE", "SV", "FI", "FR", "GR", "GT", "HN", "HK", "HU", "IS", "IE", "IT", "LV", "LT", "LU", "MY", "MT", "MX", "NL", "NZ", "NI", "NO", "PA", "PY", "PE", "PH", "PL", "PT", "SG", "SK", "ES", "SE", "CH", "TW", "TR", "UY", "US", "GB", "AD", "LI", "MC", "ID", "JP", "TH", "VN", "RO", "IL", "ZA", "SA", "AE", "BH", "QA", "OM", "KW", "EG", "MA", "DZ", "TN", "LB", "JO", "PS", "IN", "BY", "KZ", "MD", "UA", "AL", "BA", "HR", "ME", "MK", "RS", "SI", "KR", "BD", "PK", "LK", "GH", "KE", "NG", "TZ", "UG", "AG", "AM", "BS", "BB", "BZ", "BT", "BW", "BF", "CV", "CW", "DM", "FJ", "GM", "GE", "GD", "GW", "GY", "HT", "JM", "KI", "LS", "LR", "MW", "MV", "ML", "MH", "FM", "NA", "NR", "NE", "PW", "PG", "WS", "SM", "ST", "SN", "SC", "SL", "SB", "KN", "LC", "VC", "SR", "TL", "TO", "TT", "TV", "VU", "AZ", "BN", "BI", "KH", "CM", "TD", "KM", "GQ", "SZ", "GA", "GN", "KG", "LA", "MO", "MR", "MN", "NP", "RW", "TG", "UZ", "ZW", "BJ", "MG", "MU", "MZ", "AO", "CI", "DJ", "ZM", "CD", "CG", "IQ", "LY", "TJ", "VE", "ET", "XK" ],
					  "external_urls": {
						"spotify": "https://open.spotify.com/album/7wJ7Yxbq1B2rSILxUyxSTJ"
					  },
					  "href": "https://api.spotify.com/v1/albums/7wJ7Yxbq1B2rSILxUyxSTJ",
					  "id": "7wJ7Yxbq1B2rSILxUyxSTJ",
					  "images": [
						{
						  "height": 640,
						  "url": "https://i.scdn.co/image/ab67616d0000b273df442b34e8d5b293d2664c9c",
						  "width": 640
						},
						{
						  "height": 300,
						  "url": "https://i.scdn.co/image/ab67616d00001e02df442b34e8d5b293d2664c9c",
						  "width": 300
						},
						{
						  "height": 64,
						  "url": "https://i.scdn.co/image/ab67616d00004851df442b34e8d5b293d2664c9c",
						  "width": 64
						}
					  ],
					  "name": "Global Probing",
					  "release_date": "2023-11-17",
					  "release_date_precision": "day",
					  "total_tracks": 16,
					  "type": "album",
					  "uri": "spotify:album:7wJ7Yxbq1B2rSILxUyxSTJ"
					},
					"artists": [
					  {
						"external_urls": {
						  "spotify": "https://open.spotify.com/artist/2pAajGWerK3ghwToNWFENS"
						},
						"href": "https://api.spotify.com/v1/artists/2pAajGWerK3ghwToNWFENS",
						"id": "2pAajGWerK3ghwToNWFENS",
						"name": "Puscifer",
						"type": "artist",
						"uri": "spotify:artist:2pAajGWerK3ghwToNWFENS"
					  }
					],
					"available_markets": [ "AR", "AU", "AT", "BE", "BO", "BR", "BG", "CA", "CL", "CO", "CR", "CY", "CZ", "DK", "DO", "DE", "EC", "EE", "SV", "FI", "FR", "GR", "GT", "HN", "HK", "HU", "IS", "IE", "IT", "LV", "LT", "LU", "MY", "MT", "MX", "NL", "NZ", "NI", "NO", "PA", "PY", "PE", "PH", "PL", "PT", "SG", "SK", "ES", "SE", "CH", "TW", "TR", "UY", "US", "GB", "AD", "LI", "MC", "ID", "JP", "TH", "VN", "RO", "IL", "ZA", "SA", "AE", "BH", "QA", "OM", "KW", "EG", "MA", "DZ", "TN", "LB", "JO", "PS", "IN", "BY", "KZ", "MD", "UA", "AL", "BA", "HR", "ME", "MK", "RS", "SI", "KR", "BD", "PK", "LK", "GH", "KE", "NG", "TZ", "UG", "AG", "AM", "BS", "BB", "BZ", "BT", "BW", "BF", "CV", "CW", "DM", "FJ", "GM", "GE", "GD", "GW", "GY", "HT", "JM", "KI", "LS", "LR", "MW", "MV", "ML", "MH", "FM", "NA", "NR", "NE", "PW", "PG", "WS", "SM", "ST", "SN", "SC", "SL", "SB", "KN", "LC", "VC", "SR", "TL", "TO", "TT", "TV", "VU", "AZ", "BN", "BI", "KH", "CM", "TD", "KM", "GQ", "SZ", "GA", "GN", "KG", "LA", "MO", "MR", "MN", "NP", "RW", "TG", "UZ", "ZW", "BJ", "MG", "MU", "MZ", "AO", "CI", "DJ", "ZM", "CD", "CG", "IQ", "LY", "TJ", "VE", "ET", "XK" ],
					"disc_number": 1,
					"duration_ms": 299965,
					"explicit": false,
					"external_ids": {
					  "isrc": "QMEU32311746"
					},
					"external_urls": {
					  "spotify": "https://open.spotify.com/track/2arNe1aYFEEJXzurrJn0dc"
					},
					"href": "https://api.spotify.com/v1/tracks/2arNe1aYFEEJXzurrJn0dc",
					"id": "2arNe1aYFEEJXzurrJn0dc",
					"is_local": false,
					"name": "The Humbling River (Live)",
					"popularity": 30,
					"preview_url": "https://p.scdn.co/mp3-preview/6b1dcf03456eb100ed571fec34784f0d2150f7d5?cid=c71a11758e6a43a3bbc01dbf843695d9",
					"track_number": 11,
					"type": "track",
					"uri": "spotify:track:2arNe1aYFEEJXzurrJn0dc"
				  },
				  "played_at": "2024-01-24T08:57:22.696Z",
				  "context": {
					"type": "album",
					"href": "https://api.spotify.com/v1/albums/7wJ7Yxbq1B2rSILxUyxSTJ",
					"external_urls": {
					  "spotify": "https://open.spotify.com/album/7wJ7Yxbq1B2rSILxUyxSTJ"
					},
					"uri": "spotify:album:7wJ7Yxbq1B2rSILxUyxSTJ"
				  }
				}
			  ],
			  "next": "https://api.spotify.com/v1/me/player/recently-played?after=1706082026038",
			  "cursors": {
				"after": "1706086952463",
				"before": "1706082026038"
			  },
			  "limit": 20,
			  "href": "https://api.spotify.com/v1/me/player/recently-played"
			}
		');
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $service = new SpotifyUserService($httpClient, $logger, $jsonMapper);
        $res = $service->getRecentlyPlayedSongs('accessToken1');

        $this->assertEquals(2, count($res->items));
        $this->assertEquals('2GLa6pL4qWfZo9UN6yboQJ', $res->items[0]->track->id);
        $this->assertEquals('2pAajGWerK3ghwToNWFENS', $res->items[0]->track->artists[0]->id);
        $artists = $res->getArtists();

        $this->assertEquals(1, count($artists));
        $this->assertEquals('Puscifer', $artists[0]->name);

        $this->assertEquals('1706082026038', $res->getNextAfter());
    }

    public function testGetRecentlyPlayedSongsOnRequestFail(): void
    {
        $mockResp = new MockResponse('{"hi": true}', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $service = new SpotifyUserService($httpClient, $logger, $jsonMapper);
        $this->expectException(\Exception::class);
        $service->getRecentlyPlayedSongs('acessToken1', null, time());
    }

    public function testRecentlyPlayedSongsOnInvalidReponsePayload(): void
    {
        $mockResp = new MockResponse('<h1>Err</h1>');
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $service = new SpotifyUserService($httpClient, $logger, $jsonMapper);
        $this->expectException(\Exception::class);
        $service->getRecentlyPlayedSongs('accessToken1', time());
    }

    public function testRecentlyPlayedSongsBeforeAndAfterUnsupportedSimultaniously(): void
    {
        $mockResp = new MockResponse('{"hello": true}');
        $httpClient = new MockHttpClient($mockResp);
        $logger = $this->createMock(LoggerInterface::class);
        $jsonMapper = (new JsonMapperFactory())->bestFit();

        $service = new SpotifyUserService($httpClient, $logger, $jsonMapper);
        $this->expectException(\Exception::class);
        $service->getRecentlyPlayedSongs('accessToken1', time(), time());
    }
}
