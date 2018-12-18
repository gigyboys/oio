<?php

namespace App\Repository;

use App\Entity\TagPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CategorySchool|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorySchool|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorySchool[]    findAll()
 * @method CategorySchool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagPostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TagPost::class);
    }

    // /**
    //  * @return CategorySchool[] Returns an array of CategorySchool objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CategorySchool
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
