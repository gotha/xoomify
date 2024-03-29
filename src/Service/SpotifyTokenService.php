<?php

namespace App\Service;

use App\Entity\UserToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyTokenService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $redirect_uri,
        private LoggerInterface $logger,
        private FilesystemAdapter $cache = new FilesystemAdapter(),
    ) {
    }

    protected static function createUserTokenFromObject(object $data): UserToken
    {
        $token = new UserToken();
        $token->setAccessToken($data->access_token);
        $token->setType($data->token_type);
        $token->setScope($data->scope);
        $token->setExpiresIn($data->expires_in);
        $token->setRefreshToken($data->refresh_token);
        $token->setDateCreated(new \DateTime());

        return $token;
    }

    public function getAccessTokenWithCode(string $code): UserToken
    {
        $resp = $this->client->request('POST', '/api/token/', [
            'body' => http_build_query([
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirect_uri,
            ]),
        ]);

        $content = '';
        try {
            $content = $resp->getContent();
        } catch (\Exception $e) {
            $this->logger->error('could not get user access token with code', [
                'debug' => $resp->getInfo(),
            ]);
            throw new \Exception($e);
        }

        $data = @json_decode($content);
        if (!isset($data->access_token)) {
            $this->logger->error('could not find access token in repsonse', [
                'content' => $content,
            ]);
            throw new \Exception('could not find access token in response');
        }

        return self::createUserTokenFromObject($data);
    }

    public function getAccessTokenWithRefreshToken(string $refreshToken): UserToken
    {
        $resp = $this->client->request('POST', '/api/token/', [
            'body' => http_build_query([
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]),
            'headers' => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);
        $content = '';
        try {
            $content = $resp->getContent();
        } catch (\Exception $e) {
            $this->logger->error('could not get user access token with refresh token', [
                'debug' => $resp->getInfo(),
            ]);
            throw new \Exception($e);
        }

        $data = @json_decode($content);
        if (!isset($data->access_token)) {
            $this->logger->error('could not find access token in repsonse', [
                'content' => $content,
            ]);
            throw new \Exception('could not get user token');
        }

        // according to the spec, if no new refresh token is given - keep using the old one
        if (!isset($data->refresh_token)) {
            $data->refresh_token = $refreshToken;
        }

        return self::createUserTokenFromObject($data);
    }

    public function getClientToken(): string
    {
        $accessToken = $this->cache->get('spotify_client_access_token', function (ItemInterface $item) {
            $resp = $this->client->request('POST', '/api/token/', [
                'body' => http_build_query([
                    'grant_type' => 'client_credentials',
                ]),
            ]);

            $content = '';
            try {
                $content = $resp->getContent();
            } catch (\Exception $e) {
                $this->logger->error('could not get user access token with code', [
                    'debug' => $resp->getInfo(),
                ]);
                throw new \Exception($e);
            }

            $data = @json_decode($content);
            if (!isset($data->access_token)) {
                $this->logger->error('could not fetch client token', [
                    'content' => $content,
                ]);
                throw new \Exception('could not fetch client token:'.$content);
            }

            $expres_in = isset($data->expires_in) ? (int) $data->expires_in : 60;
            $item->expiresAfter($expres_in);

            return $data->access_token;
        });

        return $accessToken;
    }
}
