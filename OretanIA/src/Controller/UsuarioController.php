<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UsuarioController extends AbstractController
{
    #[Route('/usuario/new', name: 'usuario-new', methods: ['POST'])]
    public function createUsuario(
        Request $request,
        EntityManagerInterface $entityManager,
        UsuarioRepository $usuarioRepository,
        UserPasswordHasherInterface $passwordHasher
    ): RedirectResponse {
        $nombre = $request->request->get('signup-name');
        $apellido = $request->request->get('signup-surname');
        $email = $request->request->get('signup-email');
        $password = $request->request->get('signup-pswd');

        // Validar campos obligatorios
        if (!$email || !$password) {
            $this->addFlash('error', 'Todos los campos son obligatorios.');
            return $this->redirectToRoute('registro');
        }

        // Verificar si el correo ya existe
        if ($usuarioRepository->existsByCorreo($email)) {
            $this->addFlash('error', 'El correo ya está registrado.');
            return $this->redirectToRoute('registro');
        }

        $usuario = new Usuario();
        $usuario->setCorreo($email);
        $usuario->setNombre($nombre);
        $usuario->setApellido($apellido);
        $usuario->setPswd(password_hash($password, PASSWORD_DEFAULT));
        $usuario->setCreditos(50);
        $usuario->setFechaRegistro(new \DateTime());

        $entityManager->persist($usuario);
        $entityManager->flush();

        // Guardar ID en sesión (mejor: usar Security)
        $this->getSession()->set('user-id', $usuario->getId());

        $this->addFlash('success', '¡Registro exitoso!');
        return $this->redirectToRoute('home');
    }

    #[Route('/verify-login', name: 'verify-login', methods: ['POST'])]
    public function verifyLogin(
        Request $request,
        UsuarioRepository $usuarioRepository
    ): RedirectResponse {
        $email = $request->request->get('login-email');
        $password = $request->request->get('login-pswd');

        $user = $usuarioRepository->findOneBy(['correo' => $email]);

        if (!$user || !password_verify($password, $user->getPswd())) {
            $this->addFlash('error', 'Correo o contraseña incorrectos.');
            return $this->redirectToRoute('login');
        }

        $this->getSession()->set('user-id', $user->getId());
        return $this->redirectToRoute('home');
    }

    private function getSession()
    {
        return $this->container->get('session');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->invalidate();
        return $this->redirectToRoute('home');
    }
}