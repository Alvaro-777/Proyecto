<?php

namespace App\Controller;

use App\Entity\Archivo;
use App\Entity\HistorialUsoIA;
use App\Repository\IARepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractCtrl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\UsuarioRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PredictIaController extends AbstractController
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/';
    private const PYTHON_SCRIPT = __DIR__ . '/../../public/py/predict_ia.py';

    private const ALLOWED_EXTENSIONS = ['csv', 'json', 'xls', 'xlsx', 'txt'];

    #[Route('/predictia', name: 'predictia')]
    public function index(SessionInterface  $session,
                          UsuarioRepository $usuarioRepository,
    ): Response
    {
        $userId = $session->get('user-id');
        $usuario = $usuarioRepository->find($userId);
        if (empty($userId) || !$usuario) {
            return $this->redirectToRoute('inicio');
        }
        return $this->render('datos.html.twig', [
            'creditos' => $usuario->getCreditos(),
            'logado' => $usuario !== null
        ]);
    }

    #[Route('/predictia/procesar', name: 'predictia_procesar', methods: ['POST'])]
    public function procesar(
        Request                $request,
        SessionInterface       $session,
        EntityManagerInterface $entityManager,
        IARepository           $iaRepository,
        UsuarioRepository      $usuarioRepository
    ): Response
    {
        $userId = $session->get('user-id');
        $usuario = $usuarioRepository->find($userId);
        if ($usuario->getCreditos() > 0) {

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
                $tamanoArchivo = $archivoAdjunto->getSize();
                $archivoAdjunto->move($rutaUsuario, $nombreUnico);
                $rutaArchivo = $rutaUsuario . $nombreUnico;

                // Obtener entidad Usuario
                $usuario = $usuarioRepository->find($userId);
                if (!$usuario) {
                    throw new \RuntimeException('Usuario no encontrado.');
                }
                $textoInputHistorial = '"Información introducida por archivo ' . $nombreBase . ' "';
                // Guardar en base de datos
                $archivoEntity = new Archivo();
                $archivoEntity->setUsuario($usuario);
                $archivoEntity->setNombre($nombreUnico);
                $archivoEntity->setPeso($tamanoArchivo);
                $archivoEntity->setTipo($extension);
                $archivoEntity->setFechaSubida(new \DateTime());
                $entityManager->persist($archivoEntity);
                $entityManager->flush();

                $archivoId = $archivoEntity->getId();
                $archivoAdjunto->move($rutaUsuario, $nombreUnico);

                // Ejecutar Python con ruta de archivo
                $rutaProcesar = $rutaUsuario . $nombreUnico;
            } // Procesar texto
            else {
                $rutaProcesar = $datosTexto;
                $textoInputHistorial = $datosTexto;
                $archivoId = null;
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
            $ia = $iaRepository->find(2);
            $historial = new HistorialUsoIA();
            $historial->setUsuario($usuario);
            $historial->setIa($ia);
            $historial->setArchivo($archivoId);
            $historial->setTextoInput($textoInputHistorial);
            $historial->setFecha(new \DateTime());
            $historial->setIp($request->getClientIp());
            $entityManager->persist($historial);

            // Restar credito por utilización
            $usuario->setCreditos($usuario->getCreditos() - 1);
            $entityManager->persist($usuario);

            $entityManager->flush();

            $resultado = trim($salida);

            return $this->render('resultado.html.twig', [
                'creditos' => $usuario->getCreditos(),
                'resultado' => $resultado,
                'datos' => $hayArchivo ? 'Archivo: ' . $archivoAdjunto->getClientOriginalName() : $datosTexto
            ]);
        } else {
            $this->addFlash('error', 'No tienes créditos suficientes para usar este servicio.');
            return $this->render('planes.html.twig', [
                'logado' => !empty($request->getSession()->get('user-id'))
            ]);
        }
    }

    private
    function generarNombreUnico(string $directorio, string $nombreBase, string $extension): string
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