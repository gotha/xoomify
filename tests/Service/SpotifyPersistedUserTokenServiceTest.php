<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Entity\UserToken;
use App\Service\SpotifyPersistedUserTokenService;
use App\Service\SpotifyUserTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SpotifyPersistedUserTokenServiceTest extends KernelTestCase
{
    public function testSetTokenPersistsUserWithNewToken(): void
    {
        $user = new User();
        $userToken = new UserToken();
        $userToken->setAccessToken('accessToken');

        $userTokenService = $this->createMock(SpotifyUserTokenService::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');
        $em->method('persist')
            ->with($user);
        $service = new SpotifyPersistedUserTokenService($userTokenService, $em);

        $service->setToken($user, $userToken);
    }

    public function testSetTokenUpdateTokenIfExists(): void
    {
        $user = new User();
        $existingUserToken = new UserToken();
        $existingUserToken->setAccessToken('oldAccessToken');
        $user->setToken($existingUserToken);

        $newUserToken = new UserToken();
        $newUserToken->setAccessToken('newAccessToken');
        $newUserToken->setUser($user);

        $userTokenService = $this->createMock(SpotifyUserTokenService::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');
        $em->method('persist')
            ->with($newUserToken);
        $service = new SpotifyPersistedUserTokenService($userTokenService, $em);

        $service->setToken($user, $newUserToken);
    }

    public function testGetTokenWillGetNewTokenFromService(): void
    {
        $user = new User();
        $existingUserToken = new UserToken();
        $existingUserToken->setAccessToken('accessToken1');
        $user->setToken($existingUserToken);

        $userTokenService = $this->createMock(SpotifyUserTokenService::class);
        $userTokenService->expects(self::once())
            ->method('getToken')
            ->willReturn($existingUserToken);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');

        $service = new SpotifyPersistedUserTokenService($userTokenService, $em);
        $res = $service->getToken($user);

        $this->assertEquals('accessToken1', $res->getAccessToken());
    }

    public function testGetTokenWillGetNewTokenFromServiceAndPersistItIfNeeded(): void
    {
        $user = new User();
        $existingUserToken = new UserToken();
        $existingUserToken->setAccessToken('accessToken1');
        $user->setToken($existingUserToken);

        $newUserToken = new UserToken();
        $newUserToken->setAccessToken('accessToken2');

        $userTokenService = $this->createMock(SpotifyUserTokenService::class);
        $userTokenService->expects(self::once())
            ->method('getToken')
            ->willReturn($newUserToken);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist');
        $em->expects(self::once())->method('flush');

        $service = new SpotifyPersistedUserTokenService($userTokenService, $em);
        $res = $service->getToken($user);

        $this->assertEquals('accessToken2', $res->getAccessToken());
    }
}
