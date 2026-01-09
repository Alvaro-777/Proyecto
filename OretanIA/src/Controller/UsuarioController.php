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
        session_name("oretan-ia");
        session_start();

        $nombre = $_POST["signup-name"];
        $apellido = $_POST["signup-surname"];
        $email = $_POST["signup-email"];
        $password = $_POST["signup-pswd"];

        $repository = $entityManager->getRepository(Usuario::class);

        //Redirige devuelta al registro, aun no se como mandar el error especifico
        if($repository->existsByCorreo($email)){
            $_SESSION['error'] = [3, "El correo ya se encuentra registrado"];
            return $this->redirectToRoute('registro');
        }

        $usuario = new Usuario();
        $usuario->setCorreo($email);
        $usuario->setNombre($nombre);
        $usuario->setApellido($apellido);
        $usuario->setPswd($password);
        $usuario->setCreditos(50);
        $usuario->setFechaRegistro(new \DateTime());

        $entityManager->persist($usuario);

        $entityManager->flush();

        $user = $repository->findOneBy(['correo', $email]);

        //setcookie("current_user_id", strval($user->getId()), time() + (86400 * 30));
        $_SESSION["id"] = $user->getId();

        return $this->redirectToRoute('home');
    }

    #[Route('/verify-login', name: 'verify-login', methods: ['POST'])]
    public function verifyLogin(EntityManagerInterface $entityManager): RedirectResponse
    {
        session_name("oretan-ia");
        session_start();

        $repository = $entityManager->getRepository(Usuario::class);

        $email = $_POST["login-email"];
        $password = $_POST["login-pswd"];

        $user = $repository->findOneBy(array('correo' => $email));

        if($user->getPswd() !== $password){
            $_SESSION['error'] = [2, "Contraseña incorrecta"];
            return $this->redirectToRoute('login');
        }

        //setcookie("current_user_id", strval($user->getId()), time() + (86400 * 30));
        $_SESSION["id"] = $user->getId();

        return $this->redirectToRoute('home');
    }
}