<?php

namespace App\Repository;

use App\Entity\SchoolOfTheDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SchoolOfTheDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchoolOfTheDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchoolOfTheDay[]    findAll()
 * @method SchoolOfTheDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolOfTheDayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SchoolOfTheDay::class);
    }
}
