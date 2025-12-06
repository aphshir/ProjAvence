<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAllOrdered(): array
    {
        return $this->findAllOrderedQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('user')
            ->orderBy('user.email', 'ASC');
    }

    public function countOrders(User $user): int
    {
        return $this->createQueryBuilder('user')
            ->select('COUNT(customerOrder.id)')
            ->leftJoin('user.orders', 'customerOrder')
            ->where('user.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countAdmins(): int
    {
        return $this->createQueryBuilder('user')
            ->select('COUNT(user.id)')
            ->where('user.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function canBeDeleted(User $user): bool
    {
        if ($this->countOrders($user) > 0) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->countAdmins() > 1;
        }

        return true;
    }
}
