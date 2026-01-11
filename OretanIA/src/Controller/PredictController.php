<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class PredictController extends AbstractController{
    #[Route('/predictia', name: 'predictia')]
    public function chatbot(Request $request): Response
    {
        return $this->render('index.html.twig', [
            'logado' => !empty($request->getSession()->get('user-id'))
        ]);
    }

}
