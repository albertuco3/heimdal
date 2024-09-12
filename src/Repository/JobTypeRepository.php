<?php

namespace App\Repository;

use App\Entity\JobType;
use Develia\Symfony\Repository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JobType|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobType|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobType[] findAll()
 * @method JobType[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobTypeRepository extends Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobType::class);
    }

    /**
     * @param JobType $jobType
     * @param bool $flush
     * @return void
     */
    function remove($jobType, bool $flush = true): void
    {
        $jobType->setDeletionDate(new \DateTime());
        $this->_em->persist($jobType);
        if ($flush)
            $this->_em->flush();
    }
    // Métodos específicos para consultar tipos de trabajos
}