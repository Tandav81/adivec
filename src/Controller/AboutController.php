<?php

namespace App\Controller;

use App\Entity\LogoPartenaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'app_about')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $today = new \DateTime();
        $yearNow = $today->format('Y');
        $yearsExperience = $yearNow - 1989;
        $logosPartenaires = $entityManager->getRepository(LogoPartenaire::class)->findAll();

        return $this->render('about/index.html.twig', [
            'controller_name' => 'AboutController',
            'yearsExperience' => $yearsExperience,
            'logos' => $logosPartenaires,
        ]);
    }
}
