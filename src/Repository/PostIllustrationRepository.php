<?php

namespace App\Repository;

use App\Entity\PostIllustration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PostIllustration|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostIllustration|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostIllustration[]    findAll()
 * @method PostIllustration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostIllustrationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PostIllustration::class);
    }

}
