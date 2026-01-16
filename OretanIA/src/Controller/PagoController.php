<?php

namespace App\Controller;

use App\Entity\Pago;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PagoController extends AbstractController
{
    #[Route('/pago', name: 'pago_planes', methods: ['GET'])]
    public function mostrarPlanes(Request $request): Response
    {
        $planes = [
            1 => ['precio' => 5.00, 'creditos' => 500, 'nombre' => 'Básico'],
            2 => ['precio' => 10.00, 'creditos' => 1200, 'nombre' => 'Pro (+200 bonus)'],
            3 => ['precio' => 15.00, 'creditos' => 2000, 'nombre' => 'Premium (+500 bonus)'],
        ];

        return $this->render('planes.html.twig', [
            'logado' => !empty($request->getSession()->get('user-id'))
        ]);
    }

    #[Route('/pago/checkout', name: 'pago_checkout', methods: ['GET', 'POST'])]
    public function procesarPago(
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {

        if (empty($request->getSession()->get('user-id'))) {
            return $this->redirectToRoute('login');
        }

        $userId = $request->getSession()->get('user-id');
        $planId = (int)$request->query->get('plan');

        $planes = [
            1 => ['precio' => 5.00, 'creditos' => 500],
            2 => ['precio' => 10.00, 'creditos' => 1200],
            3 => ['precio' => 15.00, 'creditos' => 2000],
        ];

        if (!isset($planes[$planId])) {
            throw $this->createNotFoundException('Plan no válido.');
        }

        $plan = $planes[$planId];

        $usuarioRepo = $entityManager->getRepository(Usuario::class);
        $pagoRepo = $entityManager->getRepository(Pago::class);

        $usuario = $usuarioRepo->find($userId);

        $esPrimeraCompra = !$pagoRepo->findOneBy(['usuario' => $usuario]);
        $precio = $esPrimeraCompra ? $plan['precio'] * 0.9 : $plan['precio'];
        $precioFinal = number_format($precio, 2, '.', '');

        if ($request->isMethod('POST')) {
            $pago = new Pago();
            $pago->setUsuario($usuario);
            $pago->setCantidad($precioFinal);
            $pago->setCreditosObtenidos($plan['creditos']);
            $pago->setMetodo('Simulado');
            $pago->setValido(true);

            $entityManager->persist($pago);

            $nuevosCreditos = $usuario->getCreditos() + $plan['creditos'];
            $usuario->setCreditos($nuevosCreditos);

            $entityManager->flush();

            return $this->render('confirmacion.html.twig', [
                'creditos' => $plan['creditos'],
                'precio' => $precioFinal,
                'tieneDescuento' => $esPrimeraCompra,
                'logado' => true,
            ]);
        }

        return $this->render('checkout.html.twig', [
            'plan' => $plan,
            'planId' => $planId,
            'precioFinal' => $precioFinal,
            'aplicaDescuento' => $esPrimeraCompra,
            'logado' => true,
        ]);
    }
}