<?php

namespace App\Controller;

use App\Entity\User;
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
        return new Response('Hello, '.$user->getName());
    }
}
