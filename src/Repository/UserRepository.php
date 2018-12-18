<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getAdmins()
    {
        $role = "ADMIN";
        $qb = $this->createQueryBuilder('user');
        $qb
            ->where('user.roles LIKE :role')
            ->setParameter('role', '%'.$role.'%');

        $users =  $qb->getQuery()->getResult();

        return $users;
    }

    public function getPosts($field = 'id', $order = 'DESC') {

        $qb = $this->createQueryBuilder('post');

        $qb
            ->orderBy('post.'.$field, $order)
        ;

        $posts = $qb->getQuery()->getResult();

        return $posts;
    }
}
