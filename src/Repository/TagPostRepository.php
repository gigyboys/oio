<?php

namespace App\Repository;

use App\Entity\TagPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CategorySchool|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorySchool|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorySchool[]    findAll()
 * @method CategorySchool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagPostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TagPost::class);
    }

    public function getTagPostsLimit($limit, $order, $tag) {
        //$limit = 3;
        $qb = $this->createQueryBuilder('tagPost');

        $qb
            ->innerJoin('tagPost.post', 'post')
            ->andWhere('tagPost.tag = :tag')
            ->setParameter('tag', $tag)
            ;
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

        $tagPostsTemp = $qb->getQuery()->getResult();
        
        $tagPosts = array();
        if($order == 'DESC'){
            foreach($tagPostsTemp as $tagPost){
                array_unshift($tagPosts, $tagPost);
            }
        }else{
            $tagPosts = $tagPostsTemp;
        }

        return $tagPosts;
    }

    public function getSinceTagPost($post, $tag) {

        $qb = $this->createQueryBuilder('tagPost');

        $qb
            ->innerJoin('tagPost.post', 'post')
            ->andWhere('tagPost.tag = :tag')
            ->setParameter('tag', $tag)
            ;

        $qb
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->andWhere('post.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
        ;

        $qb
            ->andWhere('post.id < :idPost')
            ->setParameter('idPost', $post->getId());

        $qb
            ->setMaxResults(1)
            ->orderBy('post.id', 'DESC')
        ;

        $tagPost = $qb->getQuery()->getOneOrNullResult();

        return $tagPost;
    }

    public function getTagPostsSince($post, $limit, $order, $tag) {

        $qb = $this->createQueryBuilder('tagPost');

        $qb
            ->innerJoin('tagPost.post', 'post')
            ->andWhere('tagPost.tag = :tag')
            ->setParameter('tag', $tag)
            ;

        $qb
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->andWhere('post.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
        ;

        $qb
            ->andWhere('post.id <= :idPost')
            ->setParameter('idPost', $post->getId())
            ->setMaxResults($limit)
            ->orderBy('post.id', 'DESC')
        ;

        $tagPostsTemp = $qb->getQuery()->getResult();

        $tagPosts = array();
        if($order == 'DESC'){
            foreach($tagPostsTemp as $tagPost){
                array_unshift($tagPosts, $tagPost);
            }
        }else{
            $tagPosts = $tagPostsTemp;
        }

        return $tagPosts;
    }

    public function getTagPostsWithPublishedPost($tag) {
        $qb = $this->createQueryBuilder('tagPost');

        $qb
            ->innerJoin('tagPost.post', 'post')
            ->andWhere('tagPost.tag = :tag')
            ->setParameter('tag', $tag)
            ;
        $qb
            ->andWhere('post.published = :published')
            ->setParameter('published', true)
            ->andWhere('post.valid = :valid')
            ->setParameter('valid', true)
            ->andWhere('post.deleted = :deleted')
            ->setParameter('deleted', false)
            ;

        $tagPosts = $qb->getQuery()->getResult();

        return $tagPosts;
    }
}
