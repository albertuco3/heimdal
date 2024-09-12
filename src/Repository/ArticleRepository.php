<?php

namespace App\Repository;

use App\Entity\Article;
use Develia\Symfony\Repository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[] findAll()
 * @method Article[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends Repository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Article::class);
    }

    // Aquí puedes añadir métodos específicos para consultar artículos

    /**
     * @param string $serialNumber
     * @return Article|null
     * @throws NonUniqueResultException
     */
    public function findOneBySerialNumber(string $serialNumber): ?Article {
        return $this->createQueryBuilder('a')
                    ->where("a.serialNumber = :serialNumber")
                    ->setParameter('serialNumber', $serialNumber)
                    ->orderBy('a.id', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

    }

    /**
     * @param $deliveryNoteId
     * @return Article[]
     */
    public function findByDeliveryOrder($deliveryNoteId): array {
        return $this->createQueryBuilder('a')
                    ->where("a.deliveryNoteId = :deliveryNoteId")
                    ->setParameter('deliveryNoteId', $deliveryNoteId)
                    ->getQuery()
                    ->getResult();
    }

    public function updatePriorityById(int $articleId, int $priorityValue): void
    {
        $qb = $this->createQueryBuilder('a');
        $qb->update()
            ->set('a.priority', ':priority')
            ->where('a.id = :id')
            ->setParameter('priority', $priorityValue)
            ->setParameter('id', $articleId)
            ->getQuery()
            ->execute();
    }


}