<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\ORM\EntityNotFoundException;

class SpotifyUserTokenService
{
    public function __construct(
        protected SpotifyTokenService $spotifyTokenService
    ) {
    }

    public function getToken(User $user): UserToken
    {
        $token = $user->getToken();
        if (!$token) {
            throw new EntityNotFoundException('user token not found');
        }

        if (!$token->getAccessToken()) {
            throw new EntityNotFoundException('user token does not have access_token');
        }

        if (!$token->isExpired()) {
            return $token;
        }

        if (!$token->getRefreshToken()) {
            throw new EntityNotFoundException('user token does not have refresh_token');
        }

        return $this->spotifyTokenService->getAccessTokenWithRefreshToken($token->getRefreshToken());
    }
}
