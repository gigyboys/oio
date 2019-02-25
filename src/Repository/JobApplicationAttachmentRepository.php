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

    public function findCV($jobApplication)
    {
        $qb = $this->createQueryBuilder('attachment');

        $qb
            ->andWhere('attachment.cv IS NOT NULL')
            ->andWhere('attachment.jobApplication = :jobApplication')
            ->setParameter('jobApplication', $jobApplication);

        $qb
            ->setMaxResults(1);

        $attachment = $qb->getQuery()->getOneOrNullResult();
        return $attachment;
    }

    public function findDocuments($jobApplication)
    {
        $qb = $this->createQueryBuilder('attachment');

        $qb
            ->andWhere('attachment.userDocument IS NOT NULL')
            ->andWhere('attachment.jobApplication = :jobApplication')
            ->setParameter('jobApplication', $jobApplication);

        $attachments = $qb->getQuery()->getResult();
        return $attachments;
    }
}
