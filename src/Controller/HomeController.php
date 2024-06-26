<?php

namespace App\Controller;

use App\Entity\LogoPartenaire;
use App\Entity\Slide;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $slides = $entityManager->getRepository(Slide::class)->findAll();
        $logosPartenaires = $entityManager->getRepository(LogoPartenaire::class)->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'slides' => $slides,
            'logos' => $logosPartenaires
        ]);
    }
}
