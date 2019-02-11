<?php
namespace App\Controller\Admin;

use App\Repository\ParameterRepository;
use App\Repository\UserRepository;
use App\Service\SchoolService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;
use App\Repository\VisitRepository;

class DashboardController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        VisitRepository $visitRepository,
        SchoolService $schoolService,
        UserRepository $userRepository
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->visitRepository = $visitRepository;
        $this->schoolService = $schoolService;
        $this->userRepository = $userRepository;
    }

    public function index(): Response
    {
        $schools = $this->schoolRepository->findAll();
        $publishedSchools = $this->schoolRepository->findBy(array(
            'published' => true,
        ));
        $notPublishedSchools = $this->schoolRepository->findBy(array(
            'published' => false,
        ));
        $users = $this->userRepository->findAll();

        //visits
        $datebegin = new \DateTime('now');
        $result = $datebegin->format('Y-m-d');
        $datebegin = new \DateTime($result);
        $datebegin = $datebegin->modify('+1 day');
        $i = 0;
        $days = array();
        while($i <= 30){
            $day = clone $datebegin;
            $day = $day->modify('-1 day');
            $dateMax = clone $datebegin;
            $dateTemp = clone $datebegin;
            $dateMin = $dateTemp->modify('-1 day');
            
            $visits = $this->visitRepository->findVisits($dateMin, $dateMax);
            array_push($days,array(
                'day' => $day,
                'dateMax' => $dateMax,
                'dateMin' => $dateMin,
                'visits' => $visits,
            ));
            
            $datebegin = $dateMin;
            $i++;
        }

        $days = array_reverse($days);
        return $this->render('admin/index.html.twig', array(
            'schools' => $schools,
            'days' => $days,
            'publishedSchools' => $publishedSchools,
            'notPublishedSchools' => $notPublishedSchools,
            'users' => $users,
            'view' => 'dashboard',
        ));
    }
}