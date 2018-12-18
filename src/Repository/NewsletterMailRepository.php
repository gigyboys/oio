<?php

namespace App\Repository;

use App\Entity\NewsletterMail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method NewsletterMail|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsletterMail|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsletterMail[]    findAll()
 * @method NewsletterMail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsletterMailRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NewsletterMail::class);
    }

    // /**
    //  * @return NewsletterMail[] Returns an array of NewsletterMail objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NewsletterMail
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
