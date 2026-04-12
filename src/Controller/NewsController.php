<?php

namespace App\Controller;

use App\Entity\BlogPost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $news = $entityManager->getRepository(BlogPost::class)->findByVisibles();
        return $this->render('news/index.html.twig', [
            'news' => $news,
        ]);
    }

    // Redirection 301 : ancienne URL /news/{id} → nouvelle URL /news/{slug}
    #[Route('/news/{id}', name: 'show_blog_legacy', requirements: ['id' => '\d+'])]
    public function showBlogLegacy(int $id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(BlogPost::class)->find($id);
        if (!$article || !$article->getSlug()) {
            throw $this->createNotFoundException();
        }
        return $this->redirectToRoute('show_blog', ['slug' => $article->getSlug()], 301);
    }

    #[Route('/news/{slug}', name: 'show_blog')]
    public function showBlog(string $slug, EntityManagerInterface $entityManager): Response
    {
        $new = $entityManager->getRepository(BlogPost::class)->findOneBy(['slug' => $slug]);
        if (!$new) {
            throw $this->createNotFoundException();
        }
        $canonical_url = $this->generateUrl(
            'show_blog',
            ['slug' => $new->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return $this->render('news/show.html.twig', [
            'new' => $new,
            'canonical_url' => $canonical_url,
        ]);
    }
}
