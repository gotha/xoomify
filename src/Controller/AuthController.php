<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SpotifyLoginService;
use App\Service\SpotifyService;
use App\Service\SpotifyTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        SpotifyService $spotifyService,
        UserRepository $userRepository,
        EntityManagerInterface $em,
    ): Response {
        $userToken = $spotifyTokenService->getAccessTokenWithCode($code);
        $userProfile = $spotifyService->getCurrentUser($userToken->getAccessToken());
        $user = $userRepository->findOneBy(['spotifyUserId' => $userProfile->id]);
        if (!$user) {
            // if user does not exist, lets create it
            $user = new User();
            $user->setSpotifyUserId($userProfile->id);
            $user->setName($userProfile->display_name);
            $user->setEmail($userProfile->email);
            $user->setUserType($userProfile->product);
        }
        // update access token
        $user->setToken($userToken);
        $em->persist($user);
        $em->flush();

        return new Response('Hello '.$user->getName());
    }
}
