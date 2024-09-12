<?php

namespace App\Repository;

use App\Entity\UserPoint;
use Develia\Date;
use Develia\Symfony\Repository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserPoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPoint[] findAll()
 * @method UserPoint[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template-extends Repository<UserPoint>
 */
class UserPointRepository extends Repository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, UserPoint::class);

    }

    public function getPoints($userId, ?\DateTimeInterface $fromDate = null, ?\DateTimeInterface $toDate = null) {

        $queryBuilder = $this->createQueryBuilder("up")
                             ->where("up.owner = :user")
                             ->setParameter('user', $userId)
                             ->orderBy("up.date", "ASC");
        if ($fromDate)
            $queryBuilder = $queryBuilder->andWhere("up.date >= :fromDate")->setParameter('fromDate', Date::startOfDay($fromDate));
        if ($toDate)
            $queryBuilder = $queryBuilder->andWhere("up.date <= :toDate")->setParameter('toDate', Date::endOfDay($toDate));


        return $queryBuilder->getQuery()->getResult();

    }


}