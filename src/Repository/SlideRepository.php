<?php

namespace App\Repository;

use App\Entity\Slide;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @extends ServiceEntityRepository<Slide>
 */
class SlideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Slide::class);
    }

    //    /**
    //     * @return Slide[] Returns an array of Slide objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

        public function findOneByDate()
        {
            return $this->createQueryBuilder('s')
                ->andWhere('CURRENT_DATE() BETWEEN s.dateDebut and s.dateFin')
                ->getQuery()
                ->getResult()
            ;
        }
}
