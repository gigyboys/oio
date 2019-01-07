<?php

namespace App\Repository;

use App\Entity\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method School|null find($id, $lockMode = null, $lockVersion = null)
 * @method School|null findOneBy(array $criteria, array $orderBy = null)
 * @method School[]    findAll()
 * @method School[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchoolRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, School::class);
    }



    public function getSchoolOffsetLimit($offset, $limit, $published = null, $type = null, $field = 'position', $order = 'ASC') {

        $qb = $this->createQueryBuilder('school');

        if($published != null){
            $qb
                ->andWhere('school.published = :published')
                ->setParameter('published', $published);
        }

        if($type != null){
            $qb
                ->andWhere('school.type = :type')
                ->setParameter('type', $type);
        }

        $qb
            ->orderBy('school.'.$field, $order)
        ;

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        $schools = $qb->getQuery()->getResult();
        return $schools;
    }

    public function getLastAddedSchools($limit) {

        $qb = $this->createQueryBuilder('school');

        $qb
            ->where('school.published = :published')
            ->setParameter('published', true)
            ->orderBy('school.id', 'DESC');;

        $qb
            ->setMaxResults($limit)
        ;

        $schools = $qb->getQuery()->getResult();
        return $schools;
    }

    //search
    public function getSchoolSearch($critere, $published = null, $field = 'position', $order = 'ASC')
    {
        $qb = $this->createQueryBuilder('school');

        $qb
            ->where('school.name LIKE :critere OR school.shortName LIKE :critere')
            ->setParameter('critere', '%'.$critere.'%');

        if($published != null){
            $qb
                ->andWhere('school.published = :published')
                ->setParameter('published', $published);
        }

        $qb
            ->orderBy('school.'.$field, $order)
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    public function findFirstSchool($fied = 'position', $published = null) {

        $qb = $this->createQueryBuilder('school');

        if($published != null){
            $qb
                ->where('school.published = :published')
                ->setParameter('published', $published);
        }

        $qb
            ->orderBy('school.'.$fied, 'ASC')
            ->setMaxResults(1);


        $school = $qb->getQuery()->getOneOrNullResult();
        return $school;
    }

    public function findLastSchool($fied = 'position', $published = null) {

        $qb = $this->createQueryBuilder('school');

        if($published != null){
        $qb
            ->where('school.published = :published')
            ->setParameter('published', $published);
        }

        $qb
            ->orderBy('school.'.$fied, 'DESC')
            ->setMaxResults(1);


        $school = $qb->getQuery()->getOneOrNullResult();
        return $school;
    }

    public function findNextSchool(School $school, $field = 'position') {
        $id = $school->getPosition();
        switch ($field){
            case 'id':
                $id = $school->getId();
        }

        $qb = $this->createQueryBuilder('school');

        $qb
            ->where('school.published = :published')
            ->setParameter('published', true)
            ->andWhere('school.'.$field.' > :field')
            ->setParameter('field', $id)
            ->orderBy('school.'.$field, 'ASC')
            ->setMaxResults(1);


        $school = $qb->getQuery()->getOneOrNullResult();
        return $school;
    }

    public function findPreviousSchool(School $school, $field = 'position') {
        $id = $school->getPosition();
        switch ($field){
            case 'id':
                $id = $school->getId();
        }

        $qb = $this->createQueryBuilder('school');

        $qb
            ->where('school.published = :published')
            ->setParameter('published', true)
            ->andWhere('school.'.$field.' < :field')
            ->setParameter('field', $id)
            ->orderBy('school.'.$field, 'DESC')
            ->setMaxResults(1);


        $school = $qb->getQuery()->getOneOrNullResult();
        return $school;
    }

    //search
    public function findOrderBy($field = 'position', $order = 'ASC', $published = null)
    {
        $qb = $this->createQueryBuilder('school');

        if($published != null){
            $qb
                ->andWhere('school.published = :published')
                ->setParameter('published', $published);
        }

        $qb->orderBy('school.'.$field, $order);

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}
