<?php

namespace App\Controller;

use App\Entity\BlogPost;
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
        $slides = $entityManager->getRepository(Slide::class)->findOneByDate();
        $logosPartenaires = $entityManager->getRepository(LogoPartenaire::class)->findAll();
        $news = $entityManager->getRepository(BlogPost::class)->findAll();
        $today = new \DateTime();
        $yearNow = $today->format('Y');
        $yearsExperience = $yearNow - 1989;

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'slides' => $slides,
            'logos' => $logosPartenaires,
            'news' => $news,
            'yearsExperience' => $yearsExperience
        ]);
    }
}
