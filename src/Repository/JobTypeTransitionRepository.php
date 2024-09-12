<?php

namespace App\Repository;

use App\Entity\JobType;
use App\Entity\JobTypeTransition;
use Develia\Symfony\Repository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JobTypeTransition|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobTypeTransition|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobTypeTransition[] findAll()
 * @method JobTypeTransition[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobTypeTransitionRepository extends Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobTypeTransition::class);
    }


    public function getPointsPerTransition(JobType|int $fromJob, JobType|int $toJob): float
    {
        if (!$fromJob || !$toJob)
            return 0.0;

        return $this->findOneBy([
            "fromJobType" => $fromJob,
            "toJobType" => $toJob
        ])?->getPointsPerCompletion() ?? 0.0;
    }
}