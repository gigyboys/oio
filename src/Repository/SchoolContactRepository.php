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


    //search
    public function getContactsMap()
    {
        $qb = $this->createQueryBuilder('contact');

        $qb/*
            ->andWhere('contact.school.published = :published')
            ->setParameter('published', true)*/
            ->andWhere('contact.published = :published')
            ->setParameter('published', true)
            ->andWhere('contact.longitude != :longitude')
            ->setParameter('longitude', '')
            ->andWhere('contact.latitude != :latitude')
            ->setParameter('latitude', '')
        ;

        //$qb->orderBy('contact.is', 'ASC');

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}
