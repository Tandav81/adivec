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
            throw $this->createNotFoundException();
        }
        return $this->redirectToRoute('app_product_page', ['slug' => $product->getSlug()], 301);
    }

    #[Route('/product/{slug}', name: 'app_product_page')]
    public function showProductBySlug(string $slug, EntityManagerInterface $entityManager): Response
    {
        $product = $entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        if (!$product) {
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
