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
    // Redirection 301 : ancienne URL /application/{id} → nouvelle URL /application/{slug}
    #[Route('/application/{id}', name: 'app_application_legacy', requirements: ['id' => '\d+'])]
    public function indexLegacy(int $id, EntityManagerInterface $entityManager): Response
    {
        $application = $entityManager->getRepository(Application::class)->find($id);
        if (!$application || !$application->getSlug()) {
            throw $this->createNotFoundException();
        }
        return $this->redirectToRoute('app_application', ['slug' => $application->getSlug()], 301);
    }

    #[Route('/application/{slug}', name: 'app_application')]
    public function index(string $slug, EntityManagerInterface $entityManager): Response
    {
        $application = $entityManager->getRepository(Application::class)->findOneBy(['slug' => $slug]);
        if (!$application) {
            throw $this->createNotFoundException();
        }
        $products = $entityManager->getRepository(Product::class)
            ->findProductsByApplicationId($application->getId());

        return $this->render('product/liste-produit.html.twig', [
            'products' => $products,
            'application' => $application,
        ]);
    }

    #[Route('/application', name: 'app_application_page')]
    public function index2(EntityManagerInterface $entityManager): Response
    {
        $applications = $entityManager->getRepository(Application::class)->findAll();
        return $this->render('application/liste-application.html.twig', [
            'applications' => $applications,
        ]);
    }
}
