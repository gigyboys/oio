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


    public function getEventOffsetLimit($offset, $limit, $published = null, $typeslug = 'all') {

        $currentDate = new \Datetime();
        $qb = $this->createQueryBuilder('event');

        if($published != null){
            $qb
                ->andWhere('event.published = :published')
                ->setParameter('published', $published);
        }
        $qb
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false);

        if($typeslug == 'upcoming'){
            $qb
                ->andWhere('event.dateend > :currentDate')
                ->setParameter('currentDate', $currentDate);
            $qb
                ->addOrderBy('event.datebegin', 'ASC')
                ->addOrderBy('event.id', 'ASC')
            ;
        }elseif($typeslug == 'passed'){
            $qb
                ->andWhere('event.dateend < :currentDate')
                ->setParameter('currentDate', $currentDate);
            $qb
                ->orderBy('event.dateend', 'DESC')
            ;
        }else{
            $qb
                ->orderBy('event.datebegin', 'DESC')
            ;
        }

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $events = $qb->getQuery()->getResult();
        return $events;
    }

    public function getEventsByType($typeslug = 'all') {

        $currentDate = new \Datetime();
        $qb = $this->createQueryBuilder('event');

        $qb
            ->andWhere('event.published = :published')
            ->setParameter('published', true);
        $qb
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false);
        
        if($typeslug == 'upcoming'){
            $qb
                ->andWhere('event.dateend > :currentDate')
                ->setParameter('currentDate', $currentDate);
            $qb
                ->orderBy('event.datebegin', 'ASC')
            ;
        }elseif($typeslug == 'passed'){
            $qb
                ->andWhere('event.dateend < :currentDate')
                ->setParameter('currentDate', $currentDate);
            $qb
                ->orderBy('event.dateend', 'DESC')
            ;
        }else{
            $qb
                ->orderBy('event.datebegin', 'DESC')
            ;
        }

        $events = $qb->getQuery()->getResult();
        return $events;
    }

    public function getValidEvents($field = 'id', $order = 'DESC') {

        $qb = $this->createQueryBuilder('event');

        $qb
            ->andWhere('event.tovalid = :tovalid')
            ->setParameter('tovalid', true)
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
        ;
        $qb
            ->orderBy('event.'.$field, $order)
        ;

        $events = $qb->getQuery()->getResult();

        return $events;
    }

    public function findFirstEvent() {
        $fied = 'datebegin';
        $qb = $this->createQueryBuilder('event');

        $qb
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false);

        $qb
            ->addOrderBy('event.'.$fied, 'ASC')
            ->addOrderBy('event.id', 'ASC')
            ->setMaxResults(1);

        $event = $qb->getQuery()->getOneOrNullResult();
        return $event;
    }

    public function findLastEvent() {
        $fied = 'datebegin';
        $qb = $this->createQueryBuilder('event');
        
        $qb
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false);

        $qb
            ->addOrderBy('event.'.$fied, 'DESC')
            ->addOrderBy('event.id', 'DESC')
            ->setMaxResults(1);


        $event = $qb->getQuery()->getOneOrNullResult();
        return $event;
    }

    public function findNextEvent(Event $event) {
        $qb = $this->createQueryBuilder('event');

        $qb
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
            ->andWhere('event.datebegin > :datebegin OR (event.datebegin = :datebegin AND event.id > :id)')
            ->setParameter('datebegin', $event->getDatebegin())
            ->andWhere('event.id != :id')
            ->setParameter('id', $event->getId())
            ->addOrderBy('event.datebegin', 'ASC')
            ->addOrderBy('event.id', 'ASC')
            ->setMaxResults(1);

        $event = $qb->getQuery()->getOneOrNullResult();
        return $event;
    }

    public function findPreviousEvent(Event $event) {
        $qb = $this->createQueryBuilder('event');

        $qb
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
            ->andWhere('event.datebegin < :datebegin OR (event.datebegin = :datebegin AND event.id < :id)')
            ->setParameter('datebegin', $event->getDatebegin())
            ->andWhere('event.id != :id')
            ->setParameter('id', $event->getId())
            ->addOrderBy('event.datebegin', 'DESC')
            ->addOrderBy('event.id', 'DESC')
            ->setMaxResults(1);

        $event = $qb->getQuery()->getOneOrNullResult();
        return $event;
    }

    //search
    public function getEventSearch($critere, $published = null, $field = 'datebegin', $order = 'DESC')
    {
        $qb = $this->createQueryBuilder('event');

        $qb
            ->where('event.title LIKE :critere')
            ->setParameter('critere', '%'.$critere.'%');

        if($published != null){
            $qb
                ->andWhere('event.published = :published')
                ->setParameter('published', $published)
                ->andWhere('event.valid = :valid')
                ->setParameter('valid', true)
                ->andWhere('event.deleted = :deleted')
                ->setParameter('deleted', false);
        }

        $qb
            ->orderBy('event.'.$field, $order)
        ;

        return $qb->getQuery()->getResult();
    }

}
