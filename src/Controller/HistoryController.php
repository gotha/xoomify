<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserPlayHistoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class HistoryController extends AbstractController
{
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

        return $this->render('history/index.html.twig', [
            'history' => $history,
            'user' => $user,
            'title' => $user->getName()."'s play history",
        ]);
    }

    #[Route('/history/{user_id}', name: 'app_user_history')]
    public function userHistory(
        UserPlayHistoryRepository $userPlayHistoryRepository,
        UserRepository $userRepository,
        int $user_id = null,
    ): Response {
        $user = $userRepository->findOneBy(['id' => (int) $user_id]);
        if (!$user) {
            throw new NotFoundHttpException('could not find user');
        }
        $history = $userPlayHistoryRepository->findBy([
            'user' => $user,
        ], [
            'playedAt' => 'desc',
        ], 50);

        return $this->render('history/index.html.twig', [
            'history' => $history,
            'user' => $user,
            'title' => $user->getName()."'s play history",
        ]);
    }
}
