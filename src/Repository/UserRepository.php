<?php

namespace App\Repository;

use App\Entity\User;
use Develia\Symfony\Repository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[] findAll()
 * @method User[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @template-extends Repository<User>
 */
class UserRepository extends Repository {
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $request) {
        parent::__construct($registry, User::class);

        $this->security = $request;
    }

    public function getCurrentUser(): User {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->security->getUser();
    }
}