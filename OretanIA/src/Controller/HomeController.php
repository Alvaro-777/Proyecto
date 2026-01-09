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
        session_name("oretan-ia");
        session_start();

        $error=['', '', ''];

        if(isset($_SESSION['error'])){
            $error[$_SESSION['error'][0]] = $_SESSION['error'][1];
            unset($_SESSION['error']);
        }

        return $this->render('login.html.twig');
    }

    #[Route('/registro', name: 'registro')]
    public function registro(): Response
    {
        session_name("oretan-ia");
        session_start();

        $error=['', '', '', '', '', ''];

        if(isset($_SESSION['error'])){
            $error[$_SESSION['error'][0]] = $_SESSION['error'][1];
            unset($_SESSION['error']);
        }

        return $this->render('registro.html.twig', ['error' => $error]);
    }
}
