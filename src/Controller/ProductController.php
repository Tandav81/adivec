<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/produit-alimentaire', name: 'app-product-alim')]
    public function showProduct(EntityManagerInterface $entityManager): Response
    {
        $productAlimentaire = $entityManager->getRepository(Product::class)->findAll();
        return $this->render('product/index.html.twig', [
            'productAlimentaire' => $productAlimentaire,
        ]);
    }

    #[Route('/create-product', name: 'create_product')]
    public function createProduct(EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $product->setNom('Blé');
        $product->setType('farine');
        $product->setPays('France');

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }
}
