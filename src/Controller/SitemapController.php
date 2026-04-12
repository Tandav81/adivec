<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\Product;
use App\Entity\Application;
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
        // Date du jour pour les pages sans historique de modification
        $today = (new \DateTime())->format('Y-m-d');

        // On ajoute les URLs "statiques"
        $urls[] = ['loc' => $this->generateUrl('app_home'),             'lastmod' => $today, 'changefreq' => 'weekly',  'priority' => '1.0'];
        $urls[] = ['loc' => $this->generateUrl('app_familles'),         'lastmod' => $today, 'changefreq' => 'weekly',  'priority' => '0.9'];
        $urls[] = ['loc' => $this->generateUrl('app_application_page'), 'lastmod' => $today, 'changefreq' => 'weekly',  'priority' => '0.9'];
        $urls[] = ['loc' => $this->generateUrl('app_news'),             'lastmod' => $today, 'changefreq' => 'weekly',  'priority' => '0.8'];
        $urls[] = ['loc' => $this->generateUrl('app_about'),            'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'];
        $urls[] = ['loc' => $this->generateUrl('app_contact'),          'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.6'];
        $urls[] = ['loc' => $this->generateUrl('app_legal'),            'lastmod' => $today, 'changefreq' => 'yearly',  'priority' => '0.3'];

        $articles = $entityManager->getRepository(BlogPost::class)->findByVisibles();
        $products = $entityManager->getRepository(Product::class)->findAll();
        $applications = $entityManager->getRepository(Application::class)->findAll();

        // On ajoute les URLs dynamiques des articles dans le tableau
        foreach ($articles as $article) {
            if (!$article->getSlug()) {
                continue;
            }
            $images = [
                'loc' => $hostname . '/uploads/images/blog/' . $article->getImage(),
                'title' => $article->getTitle()
            ];

            $urls[] = [
                'loc' => $this->generateUrl('show_blog', [
                    'slug' => $article->getSlug(),
                ]),
                'image' => $images,
                'lastmod' => $article->getUpdatedAt()?->format('Y-m-d') ?? $today,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }
        foreach ($products as $product) {
            if (!$product->getSlug()) {
                continue;
            }
            $imageFile = $product->getImage()
                ? '/uploads/images/products/' . $product->getImage()
                : '/uploads/images/family/' . $product->getType()->getFamily()->getImage();

            $images = [
                'loc' => $hostname . $imageFile,
                'title' => $product->getNom()
            ];

            $urls[] = [
                'loc' => $this->generateUrl('app_product_page', [
                    'slug' => $product->getSlug(),
                ]),
                'image' => $images,
                'lastmod' => $product->getUpdatedAt()?->format('Y-m-d') ?? $today,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }
        // On ajoute les pages applications
        foreach ($applications as $application) {
            if (!$application->getSlug()) {
                continue;
            }
            $urls[] = [
                'loc' => $this->generateUrl('app_application', [
                    'slug' => $application->getSlug(),
                ]),
                'lastmod' => $today,
                'changefreq' => 'monthly',
                'priority' => '0.7',
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
