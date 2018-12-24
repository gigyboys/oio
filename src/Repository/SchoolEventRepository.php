<?php

namespace App\Repository;

use App\Entity\SchoolEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CategorySchool|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorySchool|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorySchool[]    findAll()
 * @method CategorySchool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolEventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolEvent::class);
    }

}
