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
            'controller_name' => 'HomeController',
            'families' => $families,
        ]);
    }

    #[Route('/types/{familyId}', name: 'app_types_family')]
    public function showTypesByFamily(EntityManagerInterface $entityManager,
                                       int $familyId): Response
    {
        $family = $entityManager->getRepository(Family::class)->findOneBy(['id' => $familyId]);
        $typesByFamily = $entityManager->getRepository(Type::class)->findBy(array('family' => $family));
        return $this->render('type/liste-type.html.twig', [
            'controller_name' => 'HomeController',
            'typesByFamily' => $typesByFamily
        ]);
    }

    #[Route('/products/{typeId}', name: 'app_products_type')]
    public function showProductsByType(EntityManagerInterface $entityManager,
                                       int $typeId) : Response
    {
        $products = $entityManager->getRepository(Product::class)->findProductsByTypeId($typeId);
        return $this->render('product/liste-produit.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products
        ]);
    }
}
