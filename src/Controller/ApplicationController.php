<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApplicationController extends AbstractController
{
    #[Route('/application/{id}', name: 'app_application')]
    public function index(int $id,EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findProductsByApplicationId($id);
        return $this->render('application/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
        ]);
    }
}
