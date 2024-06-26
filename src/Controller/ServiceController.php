<?php

namespace App\Controller;

use App\Entity\BlogPost;
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
        return $this->render('service/index.html.twig', [
            'controller_name' => 'ServiceController',
            'news' => $news,
        ]);
    }

    #[Route('/service/{id}', name: 'show_blog')]
    public function showBlog(int $id, EntityManagerInterface $entityManager): Response
    {
        $new = $entityManager->getRepository(BlogPost::class)->find($id);
        return $this->render('service/show.html.twig', [
            'controller_name' => 'ServiceController',
            'new' => $new,
        ]);
    }
}
