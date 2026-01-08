<?php
namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsuarioRepository;

class UsuarioController extends AbstractController
{
    #[Route('/usuario/new', name: 'usuario-new', methods: ['POST'])]
    public function createUsuario(EntityManagerInterface $entityManager): RedirectResponse
    {
        $nombre = $_POST["signup-fullname"];
        $email = $_POST["signup-email"];
        $password = $_POST["signup-pswd"];

        $repository = $entityManager->getRepository(Usuario::class);

        //Redirige devuelta al registro, aun no se como mandar el error especifico
        if($repository->existsByCorreo($email))
            return $this->redirectToRoute('registro', []);

        $usuario = new Usuario();
        $usuario->setCorreo($email);
        $usuario->setNombre(trim(substr($nombre, strpos($nombre, ",") + 1)));
        $usuario->setApellido(trim(substr($nombre, 0, strpos($nombre, ","))));
        $usuario->setContraseña($password);
        $usuario->setCreditos(50);
        $usuario->setFechaRegistro(new \DateTime());

        $entityManager->persist($usuario);

        $entityManager->flush();

        $user = $repository->findOneBy(['correo', $email]);

        setcookie("current_user_id", strval($user->getId()), time() + (86400 * 30));
        return $this->redirectToRoute('home');
    }

    #[Route('/verify-login', name: 'verify-login', methods: ['POST'])]
    public function verifyLogin(EntityManagerInterface $entityManager): RedirectResponse
    {
        $repository = $entityManager->getRepository(Usuario::class);

        $email = $_POST["login-email"];
        $password = $_POST["login-pswd"];

        $user = $repository->findOneBy(array('correo' => $email));

        if($user->getContraseña() !== $password)
            return $this->redirectToRoute('login');

        setcookie("current_user_id", strval($user->getId()), time() + (86400 * 30));
        return $this->redirectToRoute('home');
    }
}