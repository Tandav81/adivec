<?php

namespace App\Controller;

use App\Entity\Family;
use App\Entity\Product;
use App\Entity\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/types/{id}', name: 'app_types_family')]
    public function showTypesByFamily(EntityManagerInterface $entityManager,
                                       int $id): Response
    {
        $family = $entityManager->getRepository(Family::class)->findOneBy(['id' => $id]);
        $typesByFamily = $entityManager->getRepository(Type::class)
            ->findBy(array('family' => $family), array('name' => 'ASC'));

        return $this->render('type/liste-type.html.twig', [
            'typesByFamily' => $typesByFamily,
            'typeId' => $id,
            'family'=>$family
            ]);
    }

    #[Route('/products/{typeId}', name: 'app_products_type')]
    public function showProductsByType(EntityManagerInterface $entityManager,
                                       int $typeId) : Response
    {
        $products = $entityManager->getRepository(Product::class)->findProductsByTypeId($typeId);
        $type = $entityManager->getRepository(Type::class)->findOneById($typeId);
        $family = $entityManager->getRepository(Family::class)->findOneById($type->getFamily()->getId());
        return $this->render('product/liste-produit.html.twig', [
            'products' => $products,
            'typeId' => $typeId,
            'family' => $family,
            'type'=>$type
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_page')]
    public function showProductById(EntityManagerInterface $entityManager,
                                       int $id) : Response
    {
        $product = $entityManager->getRepository(Product::class)->findById($id);
        $canonical_url = $this->generateUrl('app_product_page', [
            'id' => $product->getId(),
        ]);
        return $this->render('product/page-produit.html.twig', [
            'product' => $product,
            'canonical_url' => $canonical_url
        ]);
    }
}
