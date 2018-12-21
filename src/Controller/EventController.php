<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Subscription;
use App\Form\EvaluationType;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\DocumentRepository;
use App\Repository\FieldRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolPostRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\TypeRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SchoolRepository;
use App\Repository\ParameterRepository;
use App\Repository\SchoolContactRepository;
use App\Repository\EvaluationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventController extends AbstractController{

    public function __construct(
        SchoolRepository $schoolRepository,
        EventRepository $eventRepository,
        ParameterRepository $parameterRepository,
        SchoolContactRepository $schoolContactRepository,
        TypeRepository $typeRepository,
        EvaluationRepository $evaluationRepository,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        SchoolService $schoolService,
        PlatformService $platformService,
        FieldRepository $fieldRepository,
        PostRepository $postRepository,
        SchoolPostRepository $schoolPostRepository,
        DocumentRepository $documentRepository,
        SubscriptionRepository $subscriptionRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->eventRepository = $eventRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolContactRepository = $schoolContactRepository;
        $this->typeRepository = $typeRepository;
        $this->evaluationRepository = $evaluationRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->schoolService = $schoolService;
        $this->platformService = $platformService;
        $this->fieldRepository = $fieldRepository;
        $this->postRepository = $postRepository;
        $this->schoolPostRepository = $schoolPostRepository;
        $this->documentRepository = $documentRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index($typeslug, $page, Request $request): Response
    {

        $parameter = $this->parameterRepository->findOneBy(array(
            'parameter' => 'events_by_page',
        ));
        $limit = intval($parameter->getValue());
        $offset = ($page-1) * $limit;
        $published = true;

        //$types = array("upcoming", "passed", "all");
        
        $events = $this->eventRepository->getEventOffsetLimit($offset, $limit, $published, $typeslug);

        $allEvents = $this->eventRepository->getEventsByType($typeslug);
            
        $response = new Response();
        if ($request->isXmlHttpRequest()){
            //listSchool
            $listSchools = array();
            foreach($schools as $school){
                $school_view = $this->renderView('school/school_item.html.twig', array(
                    'school' => $school,
                ));
                array_push($listSchools, array(
                    "school_id" 	=> $school->getId(),
                    "school_view" 	=> $school_view,
                ));
            }

            //pagination
            $pagination = $this->renderView('school/include/pagination_list_school.html.twig', array(
                'allSchools' => $allSchools,
                'schools' => $schools,
                'limit' => $limit,
                'currentpage' => $page,
                'typeslug' => $typeslug,
            ));

            //type_links
            $typeLinks = $this->renderView('school/include/school_type_link.html.twig', array(
                'typeslug' => $typeslug,
            ));

            $currentUrl = $this->get('router')->generate('school_home', array(
                'typeslug' => $typeslug,
                'page' => $page
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'schools' => $listSchools,
                'currentpage' => $page,
                'pagination' => $pagination,
                'typeLinks' => $typeLinks,
                'currentUrl' => $currentUrl,
                'page' => $page,
            )));
        }else{
            if(!$events){/*
                return $this->redirectToRoute('school_home', array(
                    'typeslug' => $typeslug
                ));*/
                return $this->redirectToRoute('platform_home');
            }
            $response = $this->render('event/index.html.twig', [
                'allEvents' => $allEvents,
                'events' => $events,
                'currentpage' => $page,
                'typeslug' => $typeslug,
                'limit' => $limit,
                'entityView' => 'event',
            ]);
        }

        return $response;
    }


}