<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/legal', name: 'app_legal')]
    public function mentionsShow(): Response
    {
        return $this->render('legal/mentions-legales.html.twig', [
            'controller_name' => 'LegalController',
        ]);
    }

    #[Route('/cgv', name: 'app_cgv')]
    public function cgvShow(): Response
    {
        return $this->render('legal/cgv.html.twig', [
            'controller_name' => 'LegalController',
        ]);
    }
}
