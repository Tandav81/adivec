<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findProductsByApplicationId($applicationId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.applications', 'app')
            ->where('app.id = :val')
            ->setParameter('val', $applicationId)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findProductsByTypeId(int $typeId): array
    {
        return $this->createQueryBuilder('p')
                ->andWhere('p.type = :type')
                ->setParameter('type', $typeId)
                ->orderBy('p.id', 'ASC')
                ->getQuery()
                ->getResult()
            ;
    }

    public function findById(int $id)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }
}
