<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/audio', name: 'audio')]
    public function audio(): Response
    {
        return $this->render('audio.html.twig');
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('login.html.twig');
    }

    #[Route('/registro', name: 'registro')]
    public function registro(): Response
    {
        $error=['', '', '', '', ''];
        return $this->render('registro.html.twig', ['error' => $error]);
    }
}
