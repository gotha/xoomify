<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserPlayHistoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(
        #[CurrentUser] User $user,
    ): Response {
        return $this->render('index/index.html.twig', [
            'name' => $user->getName(),
        ]);
    }

    #[Route('/history', name: 'app_history')]
    public function history(
        #[CurrentUser] User $user,
        UserPlayHistoryRepository $userPlayHistoryRepository,
    ): Response {
        $history = $userPlayHistoryRepository->findBy([
            'user' => $user,
        ], [
            'playedAt' => 'desc',
        ], 50);

        return $this->render('index/history.html.twig', [
            'history' => $history,
            'user' => $user,
        ]);
    }
}
