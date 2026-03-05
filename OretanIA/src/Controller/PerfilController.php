<?php

namespace App\Controller;

use App\Repository\HistorialUsoIARepository;
use App\Repository\PagoRepository;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PerfilController extends AbstractController
{
    #[Route('/perfil', name: 'perfil')]
    public function index(
        SessionInterface $session,
        UsuarioRepository $userRepo,
        HistorialUsoIARepository $historialUsoRepo,
        PagoRepository $historialPagoRepo): Response
    {
        // Comprobar si el usuario está logueado
        if (!$session->has('user-id')) {
            return $this->redirectToRoute('login');
        }

        $user = $userRepo->find($session->get('user-id'));
        $historialUso = $historialUsoRepo->findBy(['usuario' => $session->get('user-id')]);
        $historialPago = $historialPagoRepo->findBy(['usuario' => $session->get('user-id')]);

        return $this->render('perfil.html.twig', [
            'user' => $user,
            'historialUso' => $historialUso,
            'historialPago' => $historialPago
        ]);
    }
}
