<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PagoController extends AbstractController
{
    #[Route('/pago', name: 'pago_planes')]
    public function mostrarPlanes(Request $request): Response
    {
        // Verificar si el usuario está logueado
        if (empty($request->getSession()->get('user-id'))) {
            return $this->redirectToRoute('login');
        }

        $planes = [
            1 => ['precio' => 5.00, 'creditos' => 500, 'nombre' => 'Básico'],
            2 => ['precio' => 10.00, 'creditos' => 1200, 'nombre' => 'Pro (+200 bonus)'],
            3 => ['precio' => 15.00, 'creditos' => 2000, 'nombre' => 'Premium (+500 bonus)'],
        ];

        return $this->render('pago/planes.html.twig', [
            'planes' => $planes,
            'logado' => true,
        ]);
    }


}