<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;

class SpotifyPersistedUserTokenService
{
    public function __construct(
        protected SpotifyUserTokenService $spotifyUserTokenService,
        protected EntityManagerInterface $em,
    ) {
    }

    /**
     * fetches existing user token, 'refreshes' it if needed and persist it.
     */
    public function getToken(User $user): UserToken
    {
        $existingToken = $user->getToken();
        $token = $this->spotifyUserTokenService->getToken($user);
        if ($existingToken->getAccessToken() != $token->getAccessToken()) {
            $this->setToken($user, $token);
        }

        return $token;
    }

    public function setToken(User $user, UserToken $token): void
    {
        $currToken = $user->getToken();
        if ($currToken) {
            $currToken->setRefreshToken($token->getRefreshToken());
            $currToken->setAccessToken($token->getAccessToken());
            $currToken->setType($token->getType());
            $currToken->setScope($token->getScope());
            $currToken->setExpiresIn($token->getExpiresIn());
            $currToken->setUser($user);

            $this->em->persist($currToken);
        } else {
            $token->setUser($user);
            $user->setToken($token);
            $this->em->persist($user);
        }
        $this->em->flush();
    }
}
