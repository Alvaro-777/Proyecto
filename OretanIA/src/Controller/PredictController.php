<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\UsuarioRepository;

class PredictController extends AbstractController
{
    #[Route('/predictia', name: 'predictia')]
    public function datos(SessionInterface $session): Response
    {
        return $this->render('datos.html.twig', [
            'logado' => !empty($session->get('user-id'))
        ]);
    }

    #[Route('/predictia/procesar', name: 'predictia_procesar', methods: ['POST'])]
    public function procesar(
        Request $request,
        SessionInterface $session,
        UsuarioRepository $usuarioRepository
    ): Response {
        $userId = $session->get('user-id');
        if (empty($userId)) {
            return $this->redirectToRoute('login');
        }

        // Obtener datos del formulario
        $datos = $request->request->get('datos'); // Ej: "edad,ingresos,etc"

        // Validar datos
        if (empty($datos)) {
            $this->addFlash('error', 'Por favor, ingresa datos para predecir.');
            return $this->redirectToRoute('predictia');
        }

        // Ejecutar script de Python
        $script = __DIR__ . '/../../public/py/predict_ia.py';
        $comando = sprintf('python "%s" "%s"', $script, escapeshellarg($datos));
        $salida = shell_exec($comando);

        if (!$salida) {
            throw new \RuntimeException('Error al ejecutar el modelo de predicción.');
        }

        $resultado = trim($salida);

        // Guardar en base de datos (opcional)
        // ... aquí iría la lógica para guardar en Prediccion

        return $this->render('resultado.html.twig', [
            'resultado' => $resultado,
            'datos' => $datos
        ]);
    }
}