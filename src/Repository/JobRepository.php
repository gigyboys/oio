<?php

namespace App\Repository;

use App\Entity\Job;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Job::class);
    }

    public function getJobs($field = 'id', $order = 'DESC') {

        $qb = $this->createQueryBuilder('job');

        $qb
            ->andWhere('job.tovalid = :tovalid')
            ->setParameter('tovalid', true)
            ->andWhere('job.deleted = :deleted')
            ->setParameter('deleted', false)
        ;
        $qb
            ->orderBy('job.'.$field, $order)
        ;

        $jobs = $qb->getQuery()->getResult();

        return $jobs;
    }

}
