<?php

namespace App\Repository;

use App\Entity\SentMail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SentMail|null find($id, $lockMode = null, $lockVersion = null)
 * @method SentMail|null findOneBy(array $criteria, array $orderBy = null)
 * @method SentMail[]    findAll()
 * @method SentMail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SentMailRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SentMail::class);
    }

    // /**
    //  * @return SentMail[] Returns an array of SentMail objects
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
    public function findOneBySomeField($value): ?SentMail
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
