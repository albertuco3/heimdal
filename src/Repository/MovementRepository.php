<?php

namespace App\Repository;

use App\Entity\Movement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Movement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movement[] findAll()
 * @method Movement[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovementRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Movement::class);
    }

    // Métodos para consultas específicas de movimientos
    public function findBySerialNumber($serialNumber, ?\DateTimeInterface $fromDate = null, ?\DateTimeInterface $toDate = null) {
        $queryBuilder = $this->createQueryBuilder('m')
                             ->leftJoin('m.article', 'a')
                             ->andWhere('a.serialNumber = :serialNumber')
                             ->setParameter('serialNumber', $serialNumber);

        if ($fromDate) {
            $queryBuilder = $queryBuilder->andWhere('m.date >= :fromDate')
                                         ->setParameter('fromDate', $fromDate);
        }

        if ($fromDate) {
            $queryBuilder = $queryBuilder->andWhere('m.date <= :toDate')
                                         ->setParameter('toDate', $toDate);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function findByTechnician($technicianId, ?\DateTimeInterface $fromDate = null, ?\DateTimeInterface $toDate = null) {
        $queryBuilder = $this->createQueryBuilder('m')
                             ->innerJoin('m.newJobType', 'j1')
                             ->innerJoin('m.oldJobType', 'j2')
                             ->where('m.oldOwner = :technicianId or m.newOwner = :technicianId or m.oldReceiver = :technicianId or m.newReceiver  = :technicianId ')
                             ->andWhere('j1.deletionDate is null')
                             ->andWhere('j2.deletionDate is null')
                             ->setParameter('technicianId', $technicianId);

        if ($fromDate) {
            $queryBuilder = $queryBuilder->andWhere('m.date >= :fromDate')
                                         ->setParameter('fromDate', $fromDate);
        }

        if ($toDate) {
            $queryBuilder = $queryBuilder->andWhere('m.date <= :toDate')
                                         ->setParameter('toDate', $toDate);
        }

        $result = $queryBuilder->getQuery()
                               ->getResult();
        return $result;
    }


}