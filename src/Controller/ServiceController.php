<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\Slide;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServiceController extends AbstractController
{
    #[Route('/service', name: 'app_service')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $news = $entityManager->getRepository(BlogPost::class)->findAll();
        var_dump($news);
        return $this->render('service/index.html.twig', [
            'controller_name' => 'ServiceController',
            'news' => $news,
        ]);
    }
}
