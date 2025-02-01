<?php

namespace App\Controller;

use App\Entity\Application;
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
        return $this->render('product/liste-produit.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/application', name: 'app_application_page')]
    public function index2(EntityManagerInterface $entityManager): Response
    {
        $applications = $entityManager->getRepository(Application::class)->findAll();
        return $this->render('application/liste-application.html.twig', [
            'controller_name' => 'HomeController',
            'applications' => $applications,
        ]);
    }
}
