<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Entity\UserToken;
use App\Service\SpotifyTokenService;
use App\Service\SpotifyUserTokenService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SpotifyUserTokenServiceTest extends KernelTestCase
{
    public function testGetTokenFailsOnEmptyUserToken(): void
    {
        $tokenService = $this->createMock(SpotifyTokenService::class);
        $service = new SpotifyUserTokenService($tokenService);
        $this->expectException(\Exception::class);
        $service->getToken(new User());
    }

    public function testGetTokenFailsOnEmptyAccessToken(): void
    {
        $user = new User();
        $user->setToken(new UserToken());

        $tokenService = $this->createMock(SpotifyTokenService::class);
        $service = new SpotifyUserTokenService($tokenService);

        $this->expectException(\Exception::class);
        $service->getToken($user);
    }

    public function testGetTokenReturnedIfNotExpired(): void
    {
        $user = new User();
        $userToken = new UserToken();
        $userToken->setAccessToken('validAccessToken');
        $userToken->setDateCreated(new \DateTime());
        $userToken->setExpiresIn(60);
        $user->setToken($userToken);

        $tokenService = $this->createMock(SpotifyTokenService::class);
        $service = new SpotifyUserTokenService($tokenService);

        $token = $service->getToken($user);
        $this->assertEquals('validAccessToken', $token->getAccessToken());
    }

    public function testGetTokenFailIfTokenExpiredAndNoRefreshToken(): void
    {
        $user = new User();
        $userToken = new UserToken();
        $userToken->setAccessToken('invalidAccessToken');
        $userToken->setDateCreated(new \DateTime('1970-01-01 00:00:00'));
        $userToken->setExpiresIn(60);
        $user->setToken($userToken);

        $tokenService = $this->createMock(SpotifyTokenService::class);
        $service = new SpotifyUserTokenService($tokenService);

        $this->expectException(EntityNotFoundException::class);
        $service->getToken($user);
    }

    public function testGetTokenFetchNewTokenWithRefreshTokenIfCurrentIsExpired(): void
    {
        $tokenService = $this->createMock(SpotifyTokenService::class);
        $newUserToken = new UserToken();
        $newUserToken->setAccessToken('validAccessToken');
        $tokenService->expects(self::once())
            ->method('getAccessTokenWithRefreshToken')
            ->willReturn($newUserToken);

        $user = new User();
        $userToken = new UserToken();
        $userToken->setAccessToken('invalidAccessToken');
        $userToken->setRefreshToken('validRefreshToken');
        $userToken->setDateCreated(new \DateTime('1970-01-01 00:00:00'));
        $userToken->setExpiresIn(60);
        $user->setToken($userToken);

        $service = new SpotifyUserTokenService($tokenService);
        $token = $service->getToken($user);

        $this->assertEquals('validAccessToken', $token->getAccessToken());
    }
}
