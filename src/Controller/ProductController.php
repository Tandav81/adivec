<?php

namespace App\Controller;

use App\Entity\Family;
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
        return $this->render('product/index.html.twig', [
            'controller_name' => 'HomeController',
            'families' => $families,
        ]);
    }

    #[Route('/types/{familyId}', name: 'app_types_family')]
    public function showProductsByType(EntityManagerInterface $entityManager,
                                       int $familyId): Response
    {
        $family = $entityManager->getRepository(Family::class)->findOneBy(['id' => $familyId]);
        $typesByFamily = $entityManager->getRepository(Type::class)->findBy(array('family' => $family));
        return $this->render('type/index.html.twig', [
            'controller_name' => 'HomeController',
            'typesByFamily' => $typesByFamily
        ]);
    }
}
