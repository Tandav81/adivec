<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {

        $query = $request->query->get('q');

        if (!$query) {
            return $this->json([]);
        }

        // Recherche dans les produits et les applications
        $products = $entityManager->getRepository(Product::class)->createQueryBuilder('p')
            ->where('p.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $applications = $entityManager->getRepository(Application::class)->createQueryBuilder('a')
            ->where('a.libelle LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $results = [];

        foreach ($products as $product) {
            $results[] = [
                'id' => $product->getId(),
                'name' => $product->getNom(),
                'type' => 'product',
                'url' => $this->generateUrl('app_product_page', ['id' => $product->getId()]),
            ];
        }

        foreach ($applications as $application) {
            $results[] = [
                'id' => $application->getId(),
                'name' => $application->getLibelle(),
                'type' => 'application',
                'url' => $this->generateUrl('app_application', ['id' => $application->getId()]),
            ];
        }

        return $this->json($results);
    }
}
