<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class UsuarioController extends AbstractController
{

    #[\Symfony\Component\Routing\Attribute\Route('/login', name: 'login')]
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

    #[Route('/usuario/new', name: 'usuario-new', methods: ['POST'])]
    public function createUsuario(
        Request $request,
        EntityManagerInterface $entityManager,
        UsuarioRepository $usuarioRepository,
    ): Response {
        $nombre = $request->request->get('signup-name');
        $apellido = $request->request->get('signup-surname');
        $email = $request->request->get('signup-email');
        $password = $request->request->get('signup-pswd');
        $confirm = $request->request->get('signup-confirm');

        // Validar campos obligatorios
        if (empty($email) || empty($password)) {
            $errores = [null, null, null, 'Email obligatorio.', null, 'Contraseña obligatoria.'];
            return $this->render('registro.html.twig', [
                'error' => $errores,
                'form_data' => compact('nombre', 'apellido', 'email')
            ]);
        }

        // Verificar si el correo ya existe
        if ($usuarioRepository->existsByCorreo($email)) {
            $errores = [null, null, null, 'Este email ya está registrado.', null, null];
            return $this->render('registro.html.twig', [
                'error' => $errores,
                'form_data' => [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'email' => $email
                ]
            ]);
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
        $request->getSession()->set('user-id', $usuario->getId());

        return $this->redirectToRoute('home');
    }

    #[Route('/verify-login', name: 'verify-login', methods: ['POST'])]
    public function verifyLogin(
        Request $request,
        UsuarioRepository $usuarioRepository
    ): Response {
        $email = $request->request->get('login-email');
        $password = $request->request->get('login-pswd');

        $user = $usuarioRepository->findOneBy(['correo' => $email]);

        if (!$user || !password_verify($password, $user->getPswd())) {
            $this->addFlash('error', 'Correo o contraseña incorrectos.');
            return $this->redirectToRoute('login');
        }

        $request->getSession()->set('user-id', $user->getId());
        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->invalidate();
        return $this->redirectToRoute('home');
    }
}