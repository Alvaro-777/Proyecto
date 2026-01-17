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
    public function index(
        SessionInterface $session,
        UsuarioRepository $usuarioRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $userId = $session->get('user-id');
        $usuario = $userId ? $usuarioRepository->find($userId) : null;

        if (empty($userId)|| !$usuario) {
            return $this->render('index.html.twig');
        }

        $iaId = 3;
        $historial = $entityManager->getRepository(\App\Entity\HistorialUsoIA::class)
            ->createQueryBuilder('h')
            ->where('h.usuario = :usuario')
            ->andWhere('h.ia = :ia')
            ->setParameter('usuario', $usuario)
            ->setParameter('ia', $iaId)
            ->orderBy('h.fecha', 'ASC')
            ->getQuery()
            ->getResult();

        $mensajes = [];
        if (empty($historial)) {
            // Primer uso: mensaje de bienvenida del sistema
            $mensajes[] = [
                'tipo' => 'assistant',
                'contenido' => '¡Hola! Soy tu asistente virtual. Estoy configurado para responder siempre en español. ¿En qué puedo ayudarte hoy?',
                'timestamp' => (new \DateTime())->format('H:i')
            ];
        } else {
            // Cargar historial existente
            foreach ($historial as $registro) {
                $mensajes[] = [
                    'tipo' => 'user',
                    'contenido' => $registro->getTextoInput(),
                    'timestamp' => $registro->getFecha()->format('H:i')
                ];
            }
        }
            return $this->render('mensaje.html.twig', [
                'logado' => true,
                'creditos' => $usuario->getCreditos(),
                'mensajes_iniciales' => $mensajes
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
        $usuario = $userId ? $usuarioRepository->find($userId) : null;

        if (empty($userId)|| !$usuario) {
            return $this->render('index.html.twig');
        }

        $mensajeUsuario = trim($request->request->get('mensaje', ''));
        if (empty($mensajeUsuario)) {
            return $this->json(['error' => 'El mensaje no puede estar vacío'], 400);
        }

        $usuario = $usuarioRepository->find($userId);

        if ($usuario->getCreditos() <= 0) {
            $this->addFlash('error', 'No tienes créditos suficientes para usar este servicio.');
            return $this->render('planes.html.twig', [
                'logado' => !empty($request->getSession()->get('user-id'))
            ]);
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