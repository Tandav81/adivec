<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Nous récupérons le nom d'hôte depuis l'URL
        $hostname = $request->getSchemeAndHttpHost();
        // On initialise un tableau pour lister les URLs
        $urls = [];
        $date = "2025-03-23";

        // On ajoute les URLs "statiques"
        $urls[] = ['loc' => $this->generateUrl('app_home'), 'lastmod' => $date];
        $urls[] = ['loc' => $this->generateUrl('app_about'), 'lastmod' => $date];
        $urls[] = ['loc' => $this->generateUrl('app_contact'), 'lastmod' => $date];
        $urls[] = ['loc' => $this->generateUrl('app_legal'), 'lastmod' => $date];
        $urls[] = ['loc' => $this->generateUrl('app_news'), 'lastmod' => $date];
        $urls[] = ['loc' => $this->generateUrl('app_familles'), 'lastmod' => $date];

        $articles = $entityManager->getRepository(BlogPost::class)->findByVisibles();
        $products = $entityManager->getRepository(Product::class)->findAll();
        // On ajoute les URLs dynamiques des articles dans le tableau
        foreach ($articles as $article) {
            $images = [
                'loc' => '/uploads/images/blog/' . $article->getImage(), // URL to image
                'title' => $article->getTitle()    // Optional, text describing the image
            ];

            $urls[] = [
                'loc' => $this->generateUrl('show_blog', [
                    'id' => $article->getId(),
                ]),
                'image' => $images,
                'lastmod' => $date
            ];
        }
        foreach ($products as $product) {
            $images = [
                'loc' => '/uploads/images/products/' . $product->getImage(), // URL to image
                'title' => $product->getNom()    // Optional, text describing the image
            ];

            $urls[] = [
                'loc' => $this->generateUrl('app_product_page', [
                    'id' => $product->getId(),
                ]),
                'image' => $images,
                'lastmod' => $date
            ];
        }
        // Fabrication de la réponse XML
        $response = new Response(
            $this->renderView('sitemap/index.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ]),
            200
        );

        // Ajout des entêtes
        $response->headers->set('Content-Type', 'text/xml');

        // On envoie la réponse
        return $response;
    }
}
