<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Visit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method View|null find($id, $lockMode = null, $lockVersion = null)
 * @method View|null findOneBy(array $criteria, array $orderBy = null)
 * @method View[]    findAll()
 * @method View[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    public function getLastVisit(User $user) {

        $qb = $this->createQueryBuilder('visit');

        $qb
            ->andWhere('visit.user = :user')
            ->setParameter('user', $user)
        ;


        $qb
            ->orderBy('visit.date', 'DESC')
            ->setMaxResults(1)
        ;

        $visit = $qb->getQuery()->getOneOrNullResult();

        return $visit;
    }

    public function findVisits($dateMin, $dateMax) {

        $qb = $this->createQueryBuilder('visit');

        $qb
            ->andWhere('visit.date > :dateMin')
            ->setParameter('dateMin', $dateMin)
            ->andWhere('visit.date <= :dateMax')
            ->setParameter('dateMax', $dateMax)
        ;


        $qb
            ->orderBy('visit.date', 'ASC')
        ;

        $visits = $qb->getQuery()->getResult();

        return $visits;
    }
}
