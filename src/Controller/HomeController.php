<?php
namespace App\Controller;

use App\Repository\ParameterRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;

class HomeController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        PlatformService $platformService,
        EventRepository $eventRepository,
        SchoolService $schoolService
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->platformService = $platformService;
        $this->schoolService = $schoolService;
        $this->eventRepository = $eventRepository;

        $this->platformService->registerVisit();
    }

    public function index(): Response
    {
        $schools = $this->schoolRepository->findAll();

        $allSchools = $this->schoolRepository->findBy(array(
            "published" 	=> true
        ));



        $parameter = $this->parameterRepository->findOneBy(array(
            'parameter' => 'categories_index',
        ));
        $limit = $parameter->getValue();

        //$evaluated Schools
        $evaluatedSchools = $this->schoolService->getEvaluatedSchools(0);

        //all published evaluations in published school
        $allEvaluations = $this->schoolService->getAllEvaluations();

        //$categories
        $allCategories = $this->schoolService->getCategoriesWithPublishedSchool(0);
        shuffle($allCategories);

        if($limit > 0){
            $categoriesLimit = array();
            if(count($allCategories) < $limit){
                $end = count($allCategories);
            }else{
                $end = $limit;
            }

            for ($i=0; $i<$end; $i++) {
                array_push($categoriesLimit, $allCategories[$i]);
            }

            $categories = $categoriesLimit;
        }

        //events
        $limit = 5;
        $offset = 0;
        $offset = 0;
        $published = true;
        $typeslug = "upcoming";
        $events = $this->eventRepository->getEventOffsetLimit($offset, $limit, $published, $typeslug);

//        $event['diff'] = $eventTemp->getDateend()->getTimestamp() - $eventTemp->getDatebegin()->getTimestamp();
        
        return $this->render('platform/home.html.twig', [
            'allSchools'        => $allSchools,
            'schools'           => $schools,
            'categories'        => $categories,
            'allCategories'     => $allCategories,
            'evaluatedSchools'  => $evaluatedSchools,
            'allEvaluations'    => $allEvaluations,
            'events'            => $events,
        ]);
    }
}