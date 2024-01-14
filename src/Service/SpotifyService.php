<?php

namespace App\Service;

use App\Service\Spotify\Response\UserProfile;
use JsonMapper\JsonMapper;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyService
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
}
