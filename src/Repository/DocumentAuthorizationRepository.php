<?php

namespace App\Repository;

use App\Entity\DocumentAuthorization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DocumentAuthorization|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentAuthorization|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentAuthorization[]    findAll()
 * @method DocumentAuthorization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentAuthorizationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DocumentAuthorization::class);
    }

    // /**
    //  * @return DocumentAuthorization[] Returns an array of DocumentAuthorization objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DocumentAuthorization
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
