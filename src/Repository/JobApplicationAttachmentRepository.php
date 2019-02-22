<?php

namespace App\Repository;

use App\Entity\JobApplicationAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobApplicationAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobApplicationAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobApplicationAttachment[]    findAll()
 * @method JobApplicationAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobApplicationAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobApplicationAttachment::class);
    }

    // /**
    //  * @return JobApplicationAttachment[] Returns an array of JobApplicationAttachment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JobApplicationAttachment
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
