<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\SpotifyLoginService;
use App\Service\SpotifyPersistedUserTokenService;
use App\Service\SpotifyTokenService;
use App\Service\SpotifyUserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(SpotifyLoginService $spotifyLoginService): RedirectResponse
    {
        $loginUrl = $spotifyLoginService->getLoginUrl();

        return $this->redirect($loginUrl);
    }

    #[Route('/auth', name: 'app_auth')]
    public function index(
        #[MapQueryParameter] string $code,
        SpotifyTokenService $spotifyTokenService,
        SpotifyPersistedUserTokenService $spotifyPersistedUserTokenService,
        SpotifyUserService $spotifyUserService,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        Security $security,
    ): Response {
        $userToken = $spotifyTokenService->getAccessTokenWithCode($code);
        $userProfile = $spotifyUserService->getCurrentUser($userToken->getAccessToken());
        $user = $userRepository->findOneBy(['spotifyUserId' => $userProfile->id]);
        if (!$user) {
            $user = UserRepository::createUser(
                $userProfile->id,
                $userProfile->display_name,
                $userProfile->email,
                $userProfile->product,
            );
        }
        $user->setToken($user, $userToken);
        $em->persist($user);
        $em->flush();

        $security->login($user);

        return $this->redirectToRoute('app_index');
    }
}
