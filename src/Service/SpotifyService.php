<?php

namespace App\Service;

use App\Service\Spotify\Response\ArtistResponse;
use App\Service\Spotify\Response\UserPublicProfile;
use JsonMapper\JsonMapper;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyService
{
    public function __construct(
        protected HttpClientInterface $client,
        protected JsonMapper $jsonMapper,
        protected SpotifyTokenService $tokenService,
        protected LoggerInterface $logger,
    ) {
    }

    public function getArtist(string $spotifyId): ArtistResponse
    {
        $accessToken = $this->tokenService->getClientToken();
        $resp = $this->client->request('GET', "/v1/artists/$spotifyId", [
            'headers' => [
                'Authorization: Bearer '.$accessToken,
            ],
        ]);

        $content = '';
        try {
            $content = $resp->getContent();
        } catch (\Exception $e) {
            $this->logger->error('could not get artist', [
               'debug' => $resp->getInfo(),
            ]);
            throw new \Exception($e);
        }
        $resp = new ArtistResponse();
        try {
            $this->jsonMapper->mapObjectFromString($content, $resp);
        } catch (\Exception $e) {
            $this->logger->error('unexpected api response', [
               'content' => $content,
            ]);
            throw new \Exception('unexpected api response; err:'.$e->getMessage());
        }

        return $resp;
    }

    public function getUserProfile(string $spotifyId): UserPublicProfile
    {
        $accessToken = $this->tokenService->getClientToken();
        $resp = $this->client->request('GET', "/v1/users/$spotifyId", [
            'headers' => [
                'Authorization: Bearer '.$accessToken,
            ],
        ]);

        $content = '';
        try {
            $content = $resp->getContent();
        } catch (\Exception $e) {
            $this->logger->error('could not get user', [
                'debug' => $resp->getInfo()['debug'],
            ]);
            throw new \Exception($e);
        }
        $resp = new UserPublicProfile();
        try {
            $this->jsonMapper->mapObjectFromString($content, $resp);
        } catch (\Exception $e) {
            $this->logger->error('unexpected api response', [
                'content' => $content,
            ]);
            throw new \Exception('unexpected api response; err:'.$e->getMessage());
        }

        return $resp;
    }
}
