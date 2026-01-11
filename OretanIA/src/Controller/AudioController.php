<?php

namespace App\Controller;

use App\Entity\Archivo;
use App\Entity\HistorialUsoIA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AudioController extends AbstractController
{
    private const ALLOWED_EXTENSIONS = ['txt', 'pdf', 'docx'];
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/';
    private const AUDIO_DIR = __DIR__ . '/../../public/audios/';
    private const PYTHON_SCRIPT = __DIR__ . '/../../public/py/procesar_audio.py';

    #[Route('/audio', name: 'audio_index', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        return $this->render('audio.html.twig', [
            'mostrar_adjunto' => !empty($session->get('user-id')),
        ]);
    }

    #[Route('/audio/procesar', name: 'audio_procesar', methods: ['POST'])]
    public function procesar(
        Request                $request,
        SessionInterface       $session,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Validaciones iniciales
        $textoUsuario = trim($request->request->get('texto_usuario', ''));
        $archivoAdjunto = $request->files->get('archivo_adjunto');

        $hayTexto = !empty($textoUsuario);
        $hayArchivo = $archivoAdjunto && $archivoAdjunto->getClientOriginalName() !== '';

        if ($hayTexto && $hayArchivo) {
            throw new BadRequestHttpException('Por favor, escribe un texto o adjunta un archivo, pero no ambos.');
        }
        if (!$hayTexto && !$hayArchivo) {
            throw new BadRequestHttpException('No hay datos para crear el audio.');
        }

        $userId = $session->get('user-id');
        $esUsuarioLogueado = !empty($userId);

        // --- Caso: archivo adjunto ---
        if ($hayArchivo) {
            if (!$esUsuarioLogueado) {
                throw new BadRequestHttpException('Solo los usuarios registrados pueden subir archivos.');
            }

            $extension = strtolower($archivoAdjunto->getClientOriginalExtension());
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                throw new BadRequestHttpException('Tipo de archivo no permitido. Solo se aceptan .txt, .pdf, .docx');
            }

            $rutaUsuario = self::UPLOAD_DIR . $userId . '/';
            if (!is_dir($rutaUsuario)) {
                mkdir($rutaUsuario, 0777, true);
            }

            $nombreBase = pathinfo($archivoAdjunto->getClientOriginalName(), PATHINFO_FILENAME);
            $nombreUnico = $this->generarNombreUnico($rutaUsuario, $nombreBase, $extension);
            $archivoAdjunto->move($rutaUsuario, $nombreUnico);
            $rutaProcesar = $rutaUsuario . $nombreUnico;
            $textoInputHistorial = "Información introducida por archivo ".$nombreBase;

            // Gestiones si el usuario está logueado
            $archivoEntity = new Archivo();
            $archivoEntity->setUsuario($userId);
            $archivoEntity->setNombre($nombreUnico);
            $archivoEntity->setPeso($archivoAdjunto->getSize());
            $archivoEntity->setTipo($extension);
            $archivoEntity->setFechaSubida(new \DateTime());
            $entityManager->persist($archivoEntity);
            $entityManager->flush();
            $archivoId = $archivoEntity->getId();
        } else {
            $rutaProcesar = $textoUsuario;
            $textoInputHistorial=$textoUsuario;
            $archivoId = null;
        }

        // Ejecutar script de Python
        if (!is_dir(self::AUDIO_DIR)) {
            mkdir(self::AUDIO_DIR, 0777, true);
        }

        $pythonBin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'py -3' : 'python3';
        $comando = sprintf(
            '%s %s %s %s 2>&1',
            $pythonBin,
            escapeshellarg(self::PYTHON_SCRIPT),
            escapeshellarg($rutaProcesar),
            escapeshellarg(self::AUDIO_DIR)
        );

        $salida = shell_exec($comando);
        if (!$salida) {
            throw new \RuntimeException('El script de Python no devolvió ninguna salida.');
        }

        $audioGenerado = trim($salida);
        $audioRutaAbs = realpath(self::AUDIO_DIR . basename($audioGenerado));
        if (!$audioRutaAbs || !file_exists($audioRutaAbs)) {
            throw new \RuntimeException("El archivo de audio no se encontró. Salida: " . $salida);
        }
        if ($esUsuarioLogueado){
            $historial = new HistorialUsoIA();
            $historial->setUsuario($userId);
            $historial->setIa(1);
            $historial->setArchivo($archivoId);
            $historial->setTextoInput($textoInputHistorial);
            $historial->setFecha(new \DateTime());
            $entityManager->persist($historial);
            $entityManager->flush();
        }

        return new BinaryFileResponse($audioRutaAbs, 200, [
            'Content-Type' => 'audio/mpeg',
            'Content-Disposition' => 'attachment; filename="' . basename($audioGenerado) . '"'
        ]);
    }

    private function generarNombreUnico(string $rutaUsuario, string $nombreBase, string $extension): string
    {
        $nombreCompleto = $nombreBase . '.' . $extension;
        $contador = 1;

        while (file_exists($rutaUsuario . $nombreCompleto)) {
            $nombreCompleto = $nombreBase . '(' . $contador . ')' . '.' . $extension;
            $contador++;
        }

        return $nombreCompleto;
    }
}