<?php

namespace App\Repository;

use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Evaluation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evaluation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evaluation[]    findAll()
 * @method Evaluation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    public function findBySchoolNotUser($school, $user)
    {
        $qb = $this->createQueryBuilder('evaluation');

        $qb
            ->where('evaluation.school = :school')
            ->setParameter('school', $school)
            ->andWhere('evaluation.user != :user')
            ->setParameter('user', $user)
            ->andWhere('evaluation.current = :current')
            ->setParameter('current', true);


        $qb
            ->orderBy('evaluation.date', 'DESC')
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    public function getValidEvaluationsByUser(User $user) {

        $qb = $this->createQueryBuilder('evaluation');


        $qb
            ->where('evaluation.user =:user')
            ->setParameter('user', $user);
        $qb
            ->andWhere('evaluation.current =:current')
            ->setParameter('current', true);
        $qb
            ->orderBy('evaluation.date', 'DESC')
        ;

        $evaluations = $qb->getQuery()->getResult();

        return $evaluations;
    }

    public function getLastEvaluation()
    {
        $qb = $this->createQueryBuilder('evaluation');

        $qb
            ->andWhere('evaluation.current = :current')
            ->setParameter('current', true);


        $qb
            ->orderBy('evaluation.date', 'DESC')
            ->setMaxResults(1);
        ;

        $evaluation = $qb->getQuery()->getOneOrNullResult();
        return $evaluation;
    }
}
