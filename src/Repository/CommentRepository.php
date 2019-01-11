<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getCommentsLimit($type, $entity, $limit, $order) {

        $qb = $this->createQueryBuilder('comment');

        $qb
            ->where('comment.'.$type.' =:'.$type)
            ->setParameter($type, $entity)
            ->andWhere('comment.deleted =:deleted')
            ->setParameter('deleted', false)
            ->setMaxResults($limit)
            ->orderBy('comment.id', 'DESC')
        ;

        $commentsTemp = $qb->getQuery()->getResult();

        $comments = array();
        if($order == 'DESC'){
            foreach($commentsTemp as $comment){
                array_unshift($comments, $comment);
            }
        }else{
            $comments = $commentsTemp;
        }

        return $comments;
    }

    public function getCommentsSince($comment, $type, $entity, $limit, $order) {

        $qb = $this->createQueryBuilder('comment');

        $qb
            ->where('comment.'.$type.' =:'.$type)
            ->setParameter($type, $entity)
            ->andWhere('comment.deleted =:deleted')
            ->setParameter('deleted', false);

        $qb->andWhere('comment.id <= :idComment');

        $qb
            ->setParameter('idComment', $comment->getId())
            ->setMaxResults($limit)
            ->orderBy('comment.id', 'DESC')
        ;

        $commentsTemp = $qb->getQuery()->getResult();

        $comments = array();
        if($order == 'DESC'){
            foreach($commentsTemp as $comment){
                array_unshift($comments, $comment);
            }
        }else{
            $comments = $commentsTemp;
        }

        return $comments;
    }

    public function getSinceComment($comment, $type, $entity) {

        $qb = $this->createQueryBuilder('comment');

        $qb
            ->where('comment.'.$type.' =:'.$type)
            ->setParameter($type, $entity)
            ->andWhere('comment.deleted =:deleted')
            ->setParameter('deleted', false);

        $qb->andWhere('comment.id < :idComment');

        $qb
            ->setParameter('idComment', $comment->getId())
            ->setMaxResults(1)
            ->orderBy('comment.id', 'DESC')
        ;

        $comment = $qb->getQuery()->getOneOrNullResult();

        return $comment;
    }

    public function getValidCommentsByUser(User $user) {

        $qb = $this->createQueryBuilder('comment');


        $qb
            ->where('comment.user =:user')
            ->setParameter('user', $user)
            ->andWhere('comment.deleted =:deleted')
            ->setParameter('deleted', false);
        $qb
            ->orderBy('comment.date', 'DESC')
        ;

        $comments = $qb->getQuery()->getResult();

        return $comments;
    }
}
