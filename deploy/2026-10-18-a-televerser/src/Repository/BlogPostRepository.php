<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

        /**
         * @return BlogPost[] Returns an array of BlogPost objects
         */
        public function findByVisibles(?int $limit = null): array
        {
            $qb = $this->createQueryBuilder('b')
                ->andWhere('b.visible = :val')
                ->setParameter('val', true)
                ->orderBy('b.createdAt', 'DESC')
                ->addOrderBy('b.id', 'DESC');

            if ($limit !== null) {
                $qb->setMaxResults($limit);
            }

            return $qb->getQuery()->getResult();
        }

        /**
         * Dernières actualités visibles, sauf celle passée en paramètre (bloc « À lire aussi »).
         *
         * @return BlogPost[]
         */
        public function findLatestVisibleExcept(BlogPost $exclude, int $limit = 3): array
        {
            return $this->createQueryBuilder('b')
                ->andWhere('b.visible = :val')
                ->andWhere('b != :exclude')
                ->setParameter('val', true)
                ->setParameter('exclude', $exclude)
                ->orderBy('b.createdAt', 'DESC')
                ->addOrderBy('b.id', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        }

    //    public function findOneBySomeField($value): ?BlogPost
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
