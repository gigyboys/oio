<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getEvents($field = 'id', $order = 'DESC') {

        $qb = $this->createQueryBuilder('event');

        $qb
            ->andWhere('event.tovalid = :tovalid')
            ->setParameter('tovalid', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
        ;
        $qb
            ->orderBy('event.'.$field, $order)
        ;

        $events = $qb->getQuery()->getResult();

        return $events;
    }


}
