<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        return $this->render('index.html.twig', [
            'logado' => !empty($request->getSession()->get('user-id'))
        ]);
    }

    #[Route('/audio', name: 'audio')]
    public function audio(): Response
    {
        return $this->render('audio.html.twig');
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        $error = ['', '', '', '', '', ''];
        return $this->render('login.html.twig', ['error' => $error]);
    }

    #[Route('/registro', name: 'registro')]
    public function registro(): Response
    {
        $error = ['', '', '', '', '', ''];
        return $this->render('registro.html.twig', ['error' => $error]);
    }
    #[Route('/chatbotia', name: 'chatbotia')]
    public function chatbot(Request $request): Response
    {
        return $this->render('index.html.twig', [
            'logado' => !empty($request->getSession()->get('user-id'))
        ]);
    }
}
