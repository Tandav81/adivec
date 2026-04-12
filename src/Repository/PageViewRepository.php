<?php

namespace App\Repository;

use App\Entity\PageView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PageViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageView::class);
    }

    /** Nombre de visites sur les N derniers jours */
    public function countSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.visitedAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Visites par jour sur les N derniers jours (pour le graphique) */
    public function countPerDay(int $days = 30): array
    {
        $since = new \DateTimeImmutable("-{$days} days");

        $rows = $this->createQueryBuilder('p')
            ->select("DATE(p.visitedAt) as day, COUNT(p.id) as total")
            ->where('p.visitedAt >= :since')
            ->setParameter('since', $since)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->getQuery()
            ->getResult();

        // Remplir les jours sans visite avec 0
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable("-{$i} days"))->format('Y-m-d');
            $result[$date] = 0;
        }
        foreach ($rows as $row) {
            $result[$row['day']] = (int) $row['total'];
        }

        return $result;
    }

    /** Pages les plus visitées sur les N derniers jours */
    public function topPages(int $days = 30, int $limit = 10): array
    {
        $since = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('p')
            ->select('p.url, COUNT(p.id) as total')
            ->where('p.visitedAt >= :since')
            ->setParameter('since', $since)
            ->groupBy('p.url')
            ->orderBy('total', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** Nombre de visiteurs uniques (par IP hashée) sur les N derniers jours */
    public function countUniqueVisitors(int $days = 30): int
    {
        $since = new \DateTimeImmutable("-{$days} days");

        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.ipHash)')
            ->where('p.visitedAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Supprime les entrées plus vieilles que N jours (nettoyage RGPD) */
    public function deleteOlderThan(int $days = 365): int
    {
        $limit = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('p')
            ->delete()
            ->where('p.visitedAt < :limit')
            ->setParameter('limit', $limit)
            ->getQuery()
            ->execute();
    }
}
