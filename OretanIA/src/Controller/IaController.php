<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractCtrl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\UsuarioRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IaController extends AbstractController
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/';
    private const PYTHON_SCRIPT = __DIR__ . '/../../public/py/predict_ia.py';

    private const ALLOWED_EXTENSIONS = ['csv', 'json', 'xls', 'xlsx', 'txt'];

    #[Route('/predictia', name: 'predictia')]
    public function index(SessionInterface $session): Response
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
        $esLogueado = !empty($userId);

        $datosTexto = trim($request->request->get('datos_texto', ''));
        $archivoAdjunto = $request->files->get('archivo_datos');

        $hayTexto = !empty($datosTexto);
        $hayArchivo = $archivoAdjunto && $archivoAdjunto->getClientOriginalName() !== '';

        // Validaciones
        if ($hayTexto && $hayArchivo) {
            throw new BadRequestHttpException('Por favor, introduce datos o sube un archivo, pero no ambos.');
        }
        if (!$hayTexto && !$hayArchivo) {
            throw new BadRequestHttpException('No hay datos para realizar la predicción.');
        }

        // Procesar archivo
        if ($hayArchivo) {
            $extension = strtolower($archivoAdjunto->getClientOriginalExtension());
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                $formatos = implode(', ', self::ALLOWED_EXTENSIONS);
                throw new BadRequestHttpException("Formato no permitido. Usa: {$formatos}");
            }

            // Crear carpeta del usuario
            $rutaUsuario = self::UPLOAD_DIR . $userId . '/';
            if (!is_dir($rutaUsuario)) {
                mkdir($rutaUsuario, 0777, true);
            }

            $nombreBase = pathinfo($archivoAdjunto->getClientOriginalName(), PATHINFO_FILENAME);
            $nombreUnico = $this->generarNombreUnico($rutaUsuario, $nombreBase, $extension);

            // Guardar archivo
            $pesoArchivo = $archivoAdjunto->getSize();
            $archivoAdjunto->move($rutaUsuario, $nombreUnico);
            $rutaArchivo = $rutaUsuario . $nombreUnico;

            // Obtener entidad Usuario
            $usuario = $usuarioRepository->find($userId);
            if (!$usuario) {
                throw new \RuntimeException('Usuario no encontrado.');
            }

            // Guardar en base de datos
            // ... aquí iría la lógica para guardar en Prediccion

            // Ejecutar Python con ruta de archivo
            $rutaProcesar = $rutaArchivo;
        }
        // Procesar texto
        else {
            $rutaProcesar = $datosTexto;
        }

        // Ejecutar script de Python
        $pythonBin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'py -3' : 'python3';
        $comando = sprintf(
            '%s %s %s 2>&1',
            $pythonBin,
            escapeshellarg(self::PYTHON_SCRIPT),
            escapeshellarg($rutaProcesar)
        );

        $salida = shell_exec($comando);
        if (!$salida) {
            throw new \RuntimeException('Error al ejecutar el modelo de predicción.');
        }

        $resultado = trim($salida);

        return $this->render('resultado.html.twig', [
            'resultado' => $resultado,
            'datos' => $hayArchivo ? 'Archivo: ' . $archivoAdjunto->getClientOriginalName() : $datosTexto
        ]);
    }

    private function generarNombreUnico(string $directorio, string $nombreBase, string $extension): string
    {
        $nombreCompleto = $nombreBase . '.' . $extension;
        $contador = 1;

        while (file_exists($directorio . $nombreCompleto)) {
            $nombreCompleto = $nombreBase . '(' . $contador . ')' . '.' . $extension;
            $contador++;
        }

        return $nombreCompleto;
    }
}