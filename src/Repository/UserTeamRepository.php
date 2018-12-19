<?php

namespace App\Repository;

use App\Entity\UserTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTeam[]    findAll()
 * @method UserTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTeamRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserTeam::class);
    }

    
    public function findOrderBy($field = 'position', $order = 'ASC', $published = null)
    {
        $qb = $this->createQueryBuilder('userTeam');

        if($published != null){
            $qb
                ->andWhere('userTeam.published = :published')
                ->setParameter('published', $published);
        }

        $qb->orderBy('userTeam.'.$field, $order);

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}
