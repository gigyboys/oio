<?php

namespace App\Repository;

use App\Entity\TagEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TagEventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TagEvent::class);
    }

    public function getTagEventsByTagOffsetLimit($tag, $offset, $limit) {
        $qb = $this->createQueryBuilder('tagEvent');

        $qb
            ->innerJoin('tagEvent.event', 'event')
            ->andWhere('tagEvent.tag = :tag')
            ->setParameter('tag', $tag)
            ;
        $qb
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
            ;

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('event.datebegin', 'DESC')
        ;

        $tagEvents = $qb->getQuery()->getResult();

        return $tagEvents;
    }

    public function getTagEventsByTag($tag) {
        $qb = $this->createQueryBuilder('tagEvent');

        $qb
            ->innerJoin('tagEvent.event', 'event')
            ->andWhere('tagEvent.tag = :tag')
            ->setParameter('tag', $tag)
            ;
        $qb
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
            ;

        $qb
            ->orderBy('event.datebegin', 'DESC')
        ;

        $tagEvents = $qb->getQuery()->getResult();

        return $tagEvents;
    }

    public function getSinceTagEvent($event, $tag) {

        $qb = $this->createQueryBuilder('tagEvent');

        $qb
            ->innerJoin('tagEvent.event', 'event')
            ->andWhere('tagEvent.tag = :tag')
            ->setParameter('tag', $tag)
            ;

        $qb
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
        ;

        $qb
            ->andWhere('event.id < :idEvent')
            ->setParameter('idEvent', $event->getId());

        $qb
            ->setMaxResults(1)
            ->orderBy('event.id', 'DESC')
        ;

        $tagEvent = $qb->getQuery()->getOneOrNullResult();

        return $tagEvent;
    }

    public function getTagEventsSince($event, $limit, $order, $tag) {

        $qb = $this->createQueryBuilder('tagEvent');

        $qb
            ->innerJoin('tagEvent.event', 'event')
            ->andWhere('tagEvent.tag = :tag')
            ->setParameter('tag', $tag)
            ;

        $qb
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
        ;

        $qb
            ->andWhere('event.id <= :idEvent')
            ->setParameter('idEvent', $event->getId())
            ->setMaxResults($limit)
            ->orderBy('event.id', 'DESC')
        ;

        $tagEventsTemp = $qb->getQuery()->getResult();

        $tagEvents = array();
        if($order == 'DESC'){
            foreach($tagEventsTemp as $tagEvent){
                array_unshift($tagEvents, $tagEvent);
            }
        }else{
            $tagEvents = $tagEventsTemp;
        }

        return $tagEvents;
    }

    public function getTagEventsWithPublishedEvent($tag) {
        $qb = $this->createQueryBuilder('tagEvent');

        $qb
            ->innerJoin('tagEvent.event', 'event')
            ->andWhere('tagEvent.tag = :tag')
            ->setParameter('tag', $tag)
            ;
        $qb
            ->andWhere('event.published = :published')
            ->setParameter('published', true)
            ->andWhere('event.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('event.deleted = :deleted')
            ->setParameter('deleted', false)
            ;

        $tagEvents = $qb->getQuery()->getResult();

        return $tagEvents;
    }
}
