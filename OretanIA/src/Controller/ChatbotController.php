<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\UsuarioRepository;
use App\Repository\IARepository;
use Doctrine\ORM\EntityManagerInterface;

class ChatbotController extends AbstractController
{
    private const PYTHON_SCRIPT = __DIR__ . '/../../public/py/chatbot_ia.py';

    #[Route('/chatbotia', name: 'chatbotia')]
    public function index(SessionInterface $session, UsuarioRepository $usuarioRepository): Response
    {
        $userId = $session->get('user-id');
        $usuario = $userId ? $usuarioRepository->find($userId) : null;

        return $this->render('mensaje.html.twig', [
            'logado' => $usuario !== null,
            'creditos' => $usuario ? $usuario->getCreditos() : 0
        ]);
    }

    #[Route('/chatbotia/enviar', name: 'chatbotia_enviar', methods: ['POST'])]
    public function enviarMensaje(
        Request $request,
        SessionInterface $session,
        UsuarioRepository $usuarioRepository,
        IARepository $iaRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $userId = $session->get('user-id');

        if (empty($userId)) {
            return $this->json(['error' => 'Debes iniciar sesión para usar el chatbot'], 401);
        }

        $mensajeUsuario = trim($request->request->get('mensaje', ''));
        if (empty($mensajeUsuario)) {
            return $this->json(['error' => 'El mensaje no puede estar vacío'], 400);
        }

        $usuario = $usuarioRepository->find($userId);
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        if ($usuario->getCreditos() <= 0) {
            return $this->json(['error' => 'No tienes créditos suficientes'], 403);
        }

        // Guardar historial en base de datos
        $ia = $iaRepository->find(3);
        if (!$ia) {
            return $this->json(['error' => 'Servicio de IA no disponible'], 500);
        }

        $historial = new \App\Entity\HistorialUsoIA();
        $historial->setUsuario($usuario);
        $historial->setIa($ia);
        $historial->setArchivo(null);
        $historial->setTextoInput($mensajeUsuario);
        $historial->setFecha(new \DateTime());
        $historial->setIp($request->getClientIp());
        $entityManager->persist($historial);

        // Ejecutar script de Python
        $pythonBin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'py -3' : 'python3';
        $comando = sprintf(
            '%s %s %s 2>&1',
            $pythonBin,
            escapeshellarg(self::PYTHON_SCRIPT),
            escapeshellarg($mensajeUsuario)
        );

        $salida = shell_exec($comando);
        if (!$salida) {
            return $this->json(['error' => 'Error al comunicarse con el chatbot'], 500);
        }

        $respuestaChatbot = trim($salida);

        // Descontar crédito y guardar
        $usuario->setCreditos($usuario->getCreditos() - 1);
        $entityManager->persist($usuario);
        $entityManager->flush();

        return $this->json([
            'respuesta' => $respuestaChatbot,
            'creditos_restantes' => $usuario->getCreditos(),
            'timestamp' => (new \DateTime())->format('H:i:s')
        ]);
    }

    #[Route('/chatbotia/reiniciar', name: 'chatbotia_reiniciar', methods: ['POST'])]
    public function reiniciarChat(SessionInterface $session): Response
    {
        $session->remove('chatbot_historial');
        return $this->json(['success' => true]);
    }
}