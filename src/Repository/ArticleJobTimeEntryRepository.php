<?php

namespace App\Repository;

use App\Entity\ArticleJobTimeEntry;
use App\Entity\JobType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArticleJobTimeEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleJobTimeEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleJobTimeEntry[] findAll()
 * @method ArticleJobTimeEntry[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleJobTimeEntryRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, ArticleJobTimeEntry::class);
    }

    // Aquí puedes añadir métodos específicos para consultar artículos
    public function findByTechnician(int $userId, ?\DateTimeInterface $fromDate, ?\DateTimeInterface $toDate) {
        $queryBuilder = $this->createQueryBuilder('ajte')
                             ->select("ajte")
                             ->innerJoin("ajte.jobType", "job1")
                             ->where("ajte.technician = :technician and job1.deletionDate is null")
                             ->setParameter("technician", $userId);

        if ($fromDate) {
            $queryBuilder = $queryBuilder->andWhere("ajte.start >= :fromDate")->setParameter("fromDate", $fromDate);
        }

        if ($fromDate) {
            $queryBuilder = $queryBuilder->andWhere("ajte.end >= :endDate")->setParameter("endDate", $toDate);
        }


        $result = $queryBuilder->getQuery()->getResult();
        return $result;

    }


}