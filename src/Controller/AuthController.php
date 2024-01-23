<?php

namespace App\Controller;

use App\Entity\UserImage;
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
        Security $security,
        EntityManagerInterface $em,
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
        } else {
            foreach ($user->getImage() as $i) {
                $em->remove($i);
            }
        }

        foreach ($userProfile->images as $img) {
            $i = new UserImage();
            $i->setUrl($img->url);
            $i->setWidth($img->width);
            $i->setHeight($img->height);
            $user->addImage($i);
            $em->persist($i);
        }
        $em->persist($user);
        $em->flush();

        $spotifyPersistedUserTokenService->setToken($user, $userToken);

        $security->login($user);

        return $this->redirectToRoute('app_index');
    }
}
