<?php

namespace App\Repository;

use App\Entity\SchoolContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolContact[]    findAll()
 * @method SchoolContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolContactRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolContact::class);
    }

    // /**
    //  * @return SchoolContact[] Returns an array of SchoolContact objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SchoolContact
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
