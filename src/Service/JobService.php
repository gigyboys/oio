<?php

namespace App\Service;


use App\Entity\Sector;
use App\Entity\Job;
use App\Entity\Contract;
use App\Entity\User;
use App\Repository\SectorRepository;
use App\Repository\ContractRepository;
use App\Repository\UserRepository;
use App\Repository\JobRepository;
use App\Repository\JobIllustrationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class JobService
{
    public function __construct(
        JobRepository $jobRepository,
        JobIllustrationRepository $jobIllustrationRepository,
        EntityManagerInterface $em
    )
    {
        $this->jobRepository = $jobRepository;
        $this->jobIllustrationRepository = $jobIllustrationRepository;
        $this->em = $em;
    }

    public function getIllustrationPath(Job $job) {
        $illustration = $this->jobIllustrationRepository->findOneBy(array(
            'job' => $job,
            'current' => true,
        ));

        if($illustration){
            return 'uploads/images/job/illustration/'.$illustration->getPath();
        }
        else{
            return 'default/images/job/illustration/default.jpeg';
        }
    }

}