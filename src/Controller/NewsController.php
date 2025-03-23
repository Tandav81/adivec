<?php

namespace App\Controller;

use App\Entity\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $news = $entityManager->getRepository(BlogPost::class)->findByVisibles();
        return $this->render('news/index.html.twig', [
            'controller_name' => 'ServiceController',
            'news' => $news,
        ]);
    }

    #[Route('/news/{id}', name: 'show_blog')]
    public function showBlog(int $id, EntityManagerInterface $entityManager): Response
    {
        $new = $entityManager->getRepository(BlogPost::class)->find($id);
        $canonical_url = $this->generateUrl('show_blog', [
            'id' => $new->getId(),
        ]);
        return $this->render('news/show.html.twig', [
            'controller_name' => 'ServiceController',
            'new' => $new,
            'canonical_url' => $canonical_url
        ]);
    }
}
