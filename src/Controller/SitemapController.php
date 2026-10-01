<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\BlogPost;
use App\Entity\Family;
use App\Entity\Product;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Sitemap XML.
 *
 * <lastmod> = date réelle de dernière modification de la page :
 *  - pages statiques : date de modification de leur template Twig (= date de déploiement) ;
 *  - fiches (produits, news) : updatedAt ?? createdAt de l'entité, ou la date du template
 *    si celui-ci est plus récent (une évolution du gabarit modifie aussi la page) ;
 *  - listes (types, familles, applications) : la plus récente des fiches listées.
 * Jamais la date du jour : Google ignore un lastmod qui change à chaque passage.
 */
final class SitemapController extends AbstractController
{
    /** @var array<string, \DateTimeInterface> */
    private array $templateDates = [];

    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $hostname = $request->getSchemeAndHttpHost();
        $urls = [];

        // Pages statiques
        $static = [
            ['app_home', 'home/index.html.twig', 'weekly', '1.0'],
            ['app_familles', 'family/liste-famille.html.twig', 'weekly', '0.9'],
            ['app_application_page', 'application/liste-application.html.twig', 'weekly', '0.9'],
            ['app_news', 'news/index.html.twig', 'weekly', '0.8'],
            ['app_about', 'about/index.html.twig', 'monthly', '0.7'],
            ['app_contact', 'contact/index.html.twig', 'monthly', '0.6'],
            ['app_legal', 'legal/mentions-legales.html.twig', 'yearly', '0.3'],
            ['app_cgv', 'legal/cgv.html.twig', 'yearly', '0.3'],
        ];
        foreach ($static as [$route, $template, $changefreq, $priority]) {
            $urls[] = $this->entry($route, [], $this->templateDate($template), $changefreq, $priority);
        }

        // Actualités visibles
        $newsTemplate = $this->templateDate('news/show.html.twig');
        foreach ($entityManager->getRepository(BlogPost::class)->findByVisibles() as $article) {
            if (!$article->getSlug()) {
                continue;
            }
            $urls[] = $this->entry(
                'show_blog',
                ['slug' => $article->getSlug()],
                $this->latest($newsTemplate, $article->getUpdatedAt() ?? $article->getCreatedAt()),
                'monthly',
                '0.7',
                $article->getImage() ? $hostname . '/uploads/images/blog/' . $article->getImage() : null,
                $article->getTitle()
            );
        }

        // Fiches produits (+ mémorisation de la date de chaque produit pour les listes)
        $productTemplate = $this->templateDate('product/page-produit.html.twig');
        $productDates = [];
        foreach ($entityManager->getRepository(Product::class)->findAll() as $product) {
            if (!$product->getSlug()) {
                continue;
            }
            $date = $this->latest($productTemplate, $product->getUpdatedAt() ?? $product->getCreatedAt());
            $productDates[$product->getId()] = $date;

            $familyImage = $product->getType()?->getFamily()?->getImage();
            $imageFile = $product->getImage()
                ? '/uploads/images/products/' . $product->getImage()
                : ($familyImage ? '/uploads/images/family/' . $familyImage : null);

            $urls[] = $this->entry(
                'app_product_page',
                ['slug' => $product->getSlug()],
                $date,
                'monthly',
                '0.8',
                $imageFile ? $hostname . $imageFile : null,
                $product->getNom()
            );
        }

        // Listes : date la plus récente parmi les produits affichés
        $listTemplate = $this->templateDate('product/liste-produit.html.twig');
        $typeTemplate = $this->templateDate('type/liste-type.html.twig');
        $productsLatest = function (iterable $products, \DateTimeInterface $base) use ($productDates): \DateTimeInterface {
            foreach ($products as $product) {
                $base = $this->latest($base, $productDates[$product->getId()] ?? null);
            }
            return $base;
        };

        foreach ($entityManager->getRepository(Family::class)->findAll() as $family) {
            if (!$family->getSlug()) {
                continue;
            }
            $date = $typeTemplate;
            foreach ($family->getTypes() as $type) {
                $date = $productsLatest($type->getProducts(), $date);
            }
            $urls[] = $this->entry('app_types_family', ['slug' => $family->getSlug()], $date, 'monthly', '0.8');
        }

        foreach ($entityManager->getRepository(Type::class)->findAll() as $type) {
            if (!$type->getSlug()) {
                continue;
            }
            $urls[] = $this->entry(
                'app_products_type',
                ['slug' => $type->getSlug()],
                $productsLatest($type->getProducts(), $listTemplate),
                'monthly',
                '0.8'
            );
        }

        foreach ($entityManager->getRepository(Application::class)->findAll() as $application) {
            if (!$application->getSlug()) {
                continue;
            }
            $urls[] = $this->entry(
                'app_application',
                ['slug' => $application->getSlug()],
                $productsLatest($application->getProducts(), $listTemplate),
                'monthly',
                '0.7'
            );
        }

        $response = new Response($this->renderView('sitemap/index.html.twig', ['urls' => $urls]));
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');

        return $response;
    }

    private function entry(
        string $route,
        array $params,
        \DateTimeInterface $lastmod,
        string $changefreq,
        string $priority,
        ?string $imageLoc = null,
        ?string $imageTitle = null,
    ): array {
        return [
            'loc' => $this->generateUrl($route, $params, UrlGeneratorInterface::ABSOLUTE_URL),
            'lastmod' => $lastmod->format('Y-m-d'),
            'changefreq' => $changefreq,
            'priority' => $priority,
            'image' => $imageLoc ? ['loc' => $imageLoc, 'title' => $imageTitle] : null,
        ];
    }

    private function latest(\DateTimeInterface $a, ?\DateTimeInterface $b): \DateTimeInterface
    {
        return ($b !== null && $b > $a) ? $b : $a;
    }

    /** Date de dernière modification d'un template (= date de mise en ligne de la version courante). */
    private function templateDate(string $template): \DateTimeInterface
    {
        if (!isset($this->templateDates[$template])) {
            $path = $this->getParameter('kernel.project_dir') . '/templates/' . $template;
            $mtime = @filemtime($path) ?: time();
            $this->templateDates[$template] = (new \DateTimeImmutable())->setTimestamp($mtime);
        }

        return $this->templateDates[$template];
    }
}
