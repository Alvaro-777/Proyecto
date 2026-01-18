<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PerfilController extends AbstractController
{
    #[Route('/perfil', name: 'perfil')]
    public function index(SessionInterface $session): Response
    {
        // Comprobar si el usuario está logueado
        if (!$session->has('user-id')) {
            return $this->redirectToRoute('login');
        }

        return $this->render('perfil.html.twig', [
            'userId' => $session->get('user-id'),
        ]);
    }
}
