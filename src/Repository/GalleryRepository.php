<?php

namespace App\Repository;

use App\Entity\Gallery;
use App\Entity\Event;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    public function findLastGallery($entity, $fied = 'position') {

        $qb = $this->createQueryBuilder('gallery');

        if ($entity instanceof Event){
            $qb
                ->andWhere('gallery.event = :event')
                ->setParameter('event', $entity);
        }
        
        elseif ($entity instanceof Post){
            $qb
                ->andWhere('gallery.post = :post')
                ->setParameter('post', $entity);
        }

        $qb
            ->orderBy('gallery.'.$fied, 'DESC')
            ->setMaxResults(1);


        $gallery = $qb->getQuery()->getOneOrNullResult();
        return $gallery;
    }
}
