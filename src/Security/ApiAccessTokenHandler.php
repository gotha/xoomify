<?php

namespace App\Security;

use App\Service\SpotifyUserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        protected SpotifyUserService $spotifyUserService,
        protected LoggerInterface $logger,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        try {
            $user = $this->spotifyUserService->getCurrentUser($accessToken);

            return new UserBadge($user->email);
        } catch (\Exception $e) {
            $this->logger->warning('api auth failure: '.$e->getMessage());
            throw new BadCredentialsException('Invalid credentials');
        }
    }
}
