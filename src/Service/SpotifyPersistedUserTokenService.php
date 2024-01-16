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
            $user->setToken($token);
            $this->em->persist($user);
            $this->em->flush();
        }

        return $token;
    }
}
