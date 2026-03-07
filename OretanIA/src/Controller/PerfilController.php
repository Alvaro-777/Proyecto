<?php

namespace App\Controller;

use App\Repository\HistorialUsoIARepository;
use App\Repository\PagoRepository;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/perfil/password/change',name: 'perfil_password_change')]
    public function newPassword(
        SessionInterface $session,
        UsuarioRepository $userRepo,
        Request $request): Response
    {
        // Comprobar si el usuario está logueado
        if (!$session->has('user-id')) {
            return $this->redirectToRoute('login');
        }

        $user = $userRepo->find($session->get('user-id'));

        if(password_verify($request->get('old-password'), $user->getPswd())){
            $user->setPswd(password_hash(
                $request->get('new-password'),
                PASSWORD_DEFAULT
            ));
            $userRepo->save();
        } else {
            $this->addFlash('error', 'La contraseña actual es incorrecta');
        }

        return $this->redirectToRoute('perfil');
    }
}
