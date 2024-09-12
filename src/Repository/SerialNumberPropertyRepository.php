<?php

namespace App\Repository;

use App\Entity\SerialNumberProperties;
use Develia\Symfony\Repository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SerialNumberProperties|null find($id, $lockMode = null, $lockVersion = null)
 * @method SerialNumberProperties|null findOneBy(array $criteria, array $orderBy = null)
 * @method SerialNumberProperties[] findAll()
 * @method SerialNumberProperties[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SerialNumberPropertyRepository extends Repository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, SerialNumberProperties::class);
    }

    public function findOneBySerialNumber($serialNumber): ?SerialNumberProperties {
        return $this->findOneBy(["serialNumber" => $serialNumber]);
    }
}