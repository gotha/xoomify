<?php

namespace App\Service;

use App\Service\Spotify\Response\PlayHistoryResponse;
use App\Service\Spotify\Response\UserProfile;
use JsonMapper\JsonMapper;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyUserService
{
    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        private JsonMapper $jsonMapper,
    ) {
    }

    public function getCurrentUser(string $accessToken): UserProfile
    {
        $resp = $this->client->request('GET', '/v1/me', [
            'headers' => [
                'Authorization: Bearer '.$accessToken,
            ],
        ]);

        $content = '';
        try {
            $content = $resp->getContent();
        } catch (\Exception $e) {
            $this->logger->error('could not get user data', [
                'debug' => $resp->getInfo()['debug'],
            ]);
            throw new \Exception($e);
        }

        $user = new UserProfile();
        try {
            $this->jsonMapper->mapObjectFromString($content, $user);
        } catch (\Exception $e) {
            $this->logger->error('unexpected user data; could not marshal json to object', [
                'content' => $content,
            ]);
            throw new \Exception('unexpected user data');
        }

        return $user;
    }

    public function getRecentlyPlayedSongs(string $accessToken, string $after = null, string $before = null): PlayHistoryResponse
    {
        if ($before && $after) {
            throw new \Exception("you have to set either 'before' or 'after', not both");
        }
        $url = '/v1/me/player/recently-played?limit=20';
        if ($after) {
            $url .= "&after=$after";
        }
        if ($before) {
            $url .= "&before=$before";
        }

        $resp = $this->client->request('GET', $url, [
            'headers' => [
                'Authorization: Bearer '.$accessToken,
            ],
        ]);

        $content = '';
        try {
            $content = $resp->getContent();
        } catch (\Exception $e) {
            $this->logger->error('could not get user play history', [
                'debug' => $resp->getInfo()['debug'],
            ]);
            throw new \Exception($e);
        }

        $resp = new PlayHistoryResponse();
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
