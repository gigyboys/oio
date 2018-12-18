<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }


    public function getPosts($field = 'id', $order = 'DESC') {

        $qb = $this->createQueryBuilder('post');

        $qb
            ->andWhere('post.tovalid = :tovalid')
            ->setParameter('tovalid', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
        ;
        $qb
            ->orderBy('post.'.$field, $order)
        ;

        $posts = $qb->getQuery()->getResult();

        return $posts;
    }

    public function getValidPosts($field = 'id', $order = 'DESC') {

        $qb = $this->createQueryBuilder('post');

        $qb
            ->andWhere('post.tovalid = :tovalid')
            ->setParameter('tovalid', true)
            ->andWhere('post.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
        ;
        $qb
            ->orderBy('post.'.$field, $order)
        ;

        $posts = $qb->getQuery()->getResult();

        return $posts;
    }

    public function getPostsLimit($limit, $order) {

        $qb = $this->createQueryBuilder('post');

        $qb
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->andWhere('post.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
        ;

        $qb
            ->setMaxResults($limit)
            ->orderBy('post.id', 'DESC')
        ;

        $postsTemp = $qb->getQuery()->getResult();

        $posts = array();
        if($order == 'DESC'){
            foreach($postsTemp as $post){
                array_unshift($posts, $post);
            }
        }else{
            $posts = $postsTemp;
        }

        return $posts;
    }

    public function getSincePost($post) {

        $qb = $this->createQueryBuilder('post');

        $qb
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->andWhere('post.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
        ;


        $qb->andWhere('post.id < :idPost');

        $qb
            ->setParameter('idPost', $post->getId())
            ->setMaxResults(1)
            ->orderBy('post.id', 'DESC')
        ;

        $post = $qb->getQuery()->getOneOrNullResult();

        return $post;
    }

    public function getPostsSince($post, $limit, $order) {

        $qb = $this->createQueryBuilder('post');

        $qb
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->andWhere('post.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
        ;

        $qb->andWhere('post.id <= :idPost');

        $qb
            ->setParameter('idPost', $post->getId())
            ->setMaxResults($limit)
            ->orderBy('post.id', 'DESC')
        ;

        $postsTemp = $qb->getQuery()->getResult();

        $posts = array();
        if($order == 'DESC'){
            foreach($postsTemp as $post){
                array_unshift($posts, $post);
            }
        }else{
            $posts = $postsTemp;
        }

        return $posts;
    }

    //search
    public function getPostSearch($critere)
    {
        $qb = $this->createQueryBuilder('post');

        $qb
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->andWhere('post.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
        ;

        $qb
            ->andWhere('post.title LIKE :critere')
            ->setParameter('critere', '%'.$critere.'%')
            ->orderBy('post.id', 'DESC')
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}
