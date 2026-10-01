<?php

namespace App\Controller;

use App\Entity\Family;
use App\Entity\Product;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController extends AbstractController
{
    /**
     * Anciennes URL de fiches produits → nouvelle URL (301). Migration Version20261001120000 :
     * fusion des doublons alimentaire/technique, sauces soja déshydratées renommées,
     * slugs aux accents mal translittérés (« prot-eine » → « proteine »).
     */
    public const SLUG_REDIRECTS = [
        // Doublons fusionnés (la fiche principale est désormais listée dans les deux catégories)
        'amidon-natif-de-ble-1' => 'amidon-natif-de-ble',
        'gomme-de-guar-1' => 'gomme-de-guar',
        'gomme-de-xanthane-1' => 'gomme-de-xanthane',
        'fibre-de-pomme-de-terre-1' => 'fibre-de-pomme-de-terre',
        'cire-d-abeille-1' => 'cire-d-abeille',
        'cire-de-candelilla-1' => 'cire-de-candelilla',
        'cire-de-carnauba-1' => 'cire-de-carnauba',
        // Sauces soja déshydratées (produits distincts des versions liquides)
        'sauce-soja-standard-1' => 'sauce-soja-standard-deshydratee',
        'sauce-soja-sans-gluten-tamari-1' => 'sauce-soja-sans-gluten-tamari-deshydratee',
        'sauce-soja-umami-elev-e-1' => 'sauce-soja-umami-eleve-deshydratee',
        // Slugs corrigés
        'sauce-soja-umami-elev-e' => 'sauce-soja-umami-eleve',
        'carot-ene' => 'carotene',
        'concentrat-de-prot-eine-de-pois' => 'concentrat-de-proteine-de-pois',
        'exhausteur-de-go-ut-sauce-soja-sans-soja' => 'exhausteur-de-gout-sauce-soja-sans-soja',
        'isolat-de-prot-eine-de-pois' => 'isolat-de-proteine-de-pois',
        'l-ecithine-de-soja-bio' => 'lecithine-de-soja-bio',
        'l-ecithine-de-tournesol-bio' => 'lecithine-de-tournesol-bio',
        'l-ecithines-de-colza' => 'lecithines-de-colza',
        'l-ecithines-de-soja' => 'lecithines-de-soja',
        'l-ecithines-de-tournesol' => 'lecithines-de-tournesol',
        'lut-eine' => 'luteine',
        'prot-eine-de-bl-e' => 'proteine-de-ble',
        'r-eglisse' => 'reglisse',
        'sauce-soja-a-teneur-r-eduite-en-sel' => 'sauce-soja-a-teneur-reduite-en-sel',
        'sauce-soja-sucr-ee' => 'sauce-soja-sucree',
        'sauce-soja-taux-de-sel-r-eduit' => 'sauce-soja-taux-de-sel-reduit',
    ];

    /** Anciens identifiants numériques (/product/{id}) des fiches fusionnées. */
    private const MERGED_IDS = [78 => 'amidon-natif-de-ble', 92 => 'gomme-de-guar', 93 => 'gomme-de-xanthane',
        118 => 'fibre-de-pomme-de-terre', 96 => 'cire-d-abeille', 97 => 'cire-de-candelilla', 98 => 'cire-de-carnauba'];

    #[Route('/familles', name: 'app_familles')]
    public function showProduct(EntityManagerInterface $entityManager): Response
    {
        $families = $entityManager->getRepository(Family::class)->findAll();
        return $this->render('family/liste-famille.html.twig', [
            'families' => $families,
        ]);
    }

    // Redirection 301 : ancienne URL /types/{id} → nouvelle URL /types/{slug}
    #[Route('/types/{id}', name: 'app_types_family_legacy', requirements: ['id' => '\d+'])]
    public function showTypesByFamilyLegacy(int $id, EntityManagerInterface $entityManager): Response
    {
        $family = $entityManager->getRepository(Family::class)->find($id);
        if (!$family || !$family->getSlug()) {
            throw $this->createNotFoundException();
        }
        return $this->redirectToRoute('app_types_family', ['slug' => $family->getSlug()], 301);
    }

    #[Route('/types/{slug}', name: 'app_types_family')]
    public function showTypesByFamily(string $slug, EntityManagerInterface $entityManager): Response
    {
        $family = $entityManager->getRepository(Family::class)->findOneBy(['slug' => $slug]);
        if (!$family) {
            throw $this->createNotFoundException();
        }
        $typesByFamily = $entityManager->getRepository(Type::class)
            ->findBy(['family' => $family], ['name' => 'ASC']);

        return $this->render('type/liste-type.html.twig', [
            'typesByFamily' => $typesByFamily,
            'family' => $family,
        ]);
    }

    // Redirection 301 : ancienne URL /products/{typeId} → nouvelle URL /products/{slug}
    #[Route('/products/{typeId}', name: 'app_products_type_legacy', requirements: ['typeId' => '\d+'])]
    public function showProductsByTypeLegacy(int $typeId, EntityManagerInterface $entityManager): Response
    {
        $type = $entityManager->getRepository(Type::class)->find($typeId);
        if (!$type || !$type->getSlug()) {
            throw $this->createNotFoundException();
        }
        return $this->redirectToRoute('app_products_type', ['slug' => $type->getSlug()], 301);
    }

    #[Route('/products/{slug}', name: 'app_products_type')]
    public function showProductsByType(string $slug, EntityManagerInterface $entityManager): Response
    {
        $type = $entityManager->getRepository(Type::class)->findOneBy(['slug' => $slug]);
        if (!$type) {
            throw $this->createNotFoundException();
        }
        $products = $entityManager->getRepository(Product::class)
            ->findProductsByTypeId($type->getId());
        $family = $type->getFamily();

        return $this->render('product/liste-produit.html.twig', [
            'products' => $products,
            'family' => $family,
            'type' => $type,
        ]);
    }

    // Redirection 301 : ancienne URL /product/{id} → nouvelle URL /product/{slug}
    #[Route('/product/{id}', name: 'app_product_page_legacy', requirements: ['id' => '\d+'])]
    public function showProductByIdLegacy(int $id, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product || !$product->getSlug()) {
            if (isset(self::MERGED_IDS[$id])) {
                return $this->redirectToRoute('app_product_page', ['slug' => self::MERGED_IDS[$id]], 301);
            }
            throw $this->createNotFoundException();
        }
        return $this->redirectToRoute('app_product_page', ['slug' => $product->getSlug()], 301);
    }

    #[Route('/product/{slug}', name: 'app_product_page')]
    public function showProductBySlug(string $slug, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        if (!$product) {
            if (isset(self::SLUG_REDIRECTS[$slug])) {
                return $this->redirectToRoute('app_product_page', ['slug' => self::SLUG_REDIRECTS[$slug]], 301);
            }
            throw $this->createNotFoundException();
        }
        $canonical_url = $this->generateUrl(
            'app_product_page',
            ['slug' => $product->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return $this->render('product/page-produit.html.twig', [
            'product' => $product,
            'canonical_url' => $canonical_url,
        ]);
    }
}
