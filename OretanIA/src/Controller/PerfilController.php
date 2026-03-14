<?php

namespace App\Controller;

use App\Repository\HistorialUsoIARepository;
use App\Repository\PagoRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        PagoRepository $historialPagoRepo
    ): Response {

        if (!$session->has('user-id')) {
            return $this->redirectToRoute('login');
        }

        $user = $userRepo->find($session->get('user-id'));

        if (!$user) {
            $session->remove('user-id');
            return $this->redirectToRoute('login');
        }

        $historialUso = $historialUsoRepo->findBy([
            'usuario' => $user
        ]);

        $historialPago = $historialPagoRepo->findBy([
            'usuario' => $user
        ]);

        return $this->render('perfil.html.twig', [
            'user' => $user,
            'historialUso' => $historialUso,
            'historialPago' => $historialPago
        ]);
    }



    #[Route('/perfil/password/change', name: 'perfil_password_change')]
    public function newPassword(
        SessionInterface $session,
        UsuarioRepository $userRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        if (!$session->has('user-id')) {
            return $this->redirectToRoute('login');
        }

        $user = $userRepo->find($session->get('user-id'));

        if (!$user) {
            $session->remove('user-id');
            return $this->redirectToRoute('login');
        }

        if (password_verify($request->get('old-password'), $user->getPswd())) {

            $user->setPswd(
                password_hash($request->get('new-password'), PASSWORD_DEFAULT)
            );

            $em->flush();

        } else {
            $this->addFlash('error', 'La contraseña actual es incorrecta');
        }

        return $this->redirectToRoute('perfil');
    }



    #[Route('/perfil/configuracion', name: 'perfil_configuracion')]
    public function configuracion(
        SessionInterface $session,
        UsuarioRepository $userRepo
    ): Response {

        if (!$session->has('user-id')) {
            return $this->redirectToRoute('login');
        }

        $user = $userRepo->find($session->get('user-id'));

        if (!$user) {
            $session->remove('user-id');
            return $this->redirectToRoute('login');
        }

        return $this->render('perfil_configuracion.html.twig', [
            'user' => $user
        ]);
    }



    #[Route('/perfil/foto', name:'perfil_foto_upload', methods:['POST'])]
    public function subirFoto(
        SessionInterface $session,
        UsuarioRepository $userRepo,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        if (!$session->has('user-id')) {
            return $this->redirectToRoute('login');
        }

        $user = $userRepo->find($session->get('user-id'));

        if (!$user) {
            $session->remove('user-id');
            return $this->redirectToRoute('login');
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('foto');

        if (!$file) {
            return $this->redirectToRoute('perfil');
        }

        // extensiones permitidas
        $extensionesPermitidas = ['jpg','jpeg','png','webp'];

        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $extensionesPermitidas)) {
            $this->addFlash('error','Formato de imagen no permitido');
            return $this->redirectToRoute('perfil');
        }

        if ($file->getSize() > 2000000) {
            $this->addFlash('error','La imagen es demasiado grande');
            return $this->redirectToRoute('perfil');
        }

        $uploadsPath = $this->getParameter('kernel.project_dir').'/public/uploads/perfiles';

        // eliminar foto anterior
        if ($user->getFotoPerfil()) {

            $oldFile = $uploadsPath.'/'.$user->getFotoPerfil();

            if (file_exists($oldFile)) {
                unlink($oldFile);
            }

        }

        // generar nombre seguro
        $nombreArchivo = uniqid().'.'.$extension;

        $file->move($uploadsPath, $nombreArchivo);

        $user->setFotoPerfil($nombreArchivo);

        $em->flush();

        return $this->redirectToRoute('perfil');
    }

}