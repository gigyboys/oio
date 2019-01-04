<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Subscription;
use App\Entity\Participation;
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
use App\Repository\ParticipationRepository;
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
        ParticipationRepository $participationRepository,
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
        $this->participationRepository = $participationRepository;
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
            //listEvent
            $listEvents = array();
            foreach($events as $event){
                $event_view = $this->renderView('event/include/event_item.html.twig', array(
                    'event' => $event,
                ));
                array_push($listEvents, array(
                    "event_id" 	=> $event->getId(),
                    "event_view" 	=> $event_view,
                ));
            }

            //pagination
            $pagination = $this->renderView('event/include/pagination_list_event.html.twig', array(
                'allEvents' => $allEvents,
                'events' => $events,
                'limit' => $limit,
                'currentpage' => $page,
                'typeslug' => $typeslug,
            ));

            //type_links
            $typeLinks = $this->renderView('event/include/event_type_link.html.twig', array(
                'typeslug' => $typeslug,
            ));

            $currentUrl = $this->get('router')->generate('event', array(
                'typeslug' => $typeslug,
                'page' => $page
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'events' => $listEvents,
                'currentpage' => $page,
                'pagination' => $pagination,
                'typeLinks' => $typeLinks,
                'currentUrl' => $currentUrl,
                'page' => $page,
            )));
        }else{
            if(!$events){
                return $this->redirectToRoute('event', array(
                    'typeslug' => $typeslug
                ));
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



    public function viewById($id, Request $request)
    {
        $event = $this->eventRepository->find($id);
        if($event){
            return $this->redirectToRoute('event_view', array('slug' => $event->getSlug()));
        }else{
            return $this->redirectToRoute('event');
        }
    }

    public function view($slug, Request $request): Response
    {

        $user = $this->getUser();

        $event = $this->eventRepository->findOneBy(array(
            'slug' => $slug,
        ));

        $showEvent = false;
        if($event && !$event->getDeleted() && $event->getPublished() && $event->getValid()){
            $showEvent = true;
        }
        if($event && ( $showEvent || $this->isGranted('ROLE_ADMIN') || $event->getUser() == $user)){

            //view
            $this->platformService->registerView($event, $user, $request);

            /*
            $allComments = $this->blogService->getValidComments($post);

            $limit = 10;
            $type = "post";
            $order = "DESC";
            $comments = $this->commentRepository->getCommentsLimit($type, $post, $limit, $order);

            $previousComment = null;
            if(count($comments)>0){
                $firstComment = $comments[0];
                $previousComment = $this->commentRepository->getSinceComment($firstComment, $type, $post);
            }
            */
            return $this->render('event/view_event.html.twig', [
                'event' => $event,
                /*
                'comments' => $comments,
                'allComments' => $allComments,
                'previousComment' => $previousComment,
                */
                'entityView' => 'event',
            ]);
        }else{
            return $this->redirectToRoute('event');
        }
    }

    public function goingParticipation($event_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);
        $user = $this->getUser();

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));
        if ($user) {
            if ($event) {
                $participations = $this->participationRepository->findBy(array(
                    'user' => $user,
                    'event' => $event,
                ));

                foreach ($participations as $participationTemp) {
                    $this->em->remove($participationTemp);
                }

                $participation = new Participation();
                $participation->setEvent($event);
                $participation->setUser($user);
                $participation->setStatus(1);
                $participation->setDate(new \DateTime());

                $this->em->persist($participation);
                $this->em->flush();

                $participationHtml = $this->renderView('event/include/participation.html.twig', array(
                    'event'   => $event,
                    'user'    => $user,
                ));

                $response->setContent(json_encode(array(
                    'state'         => 1,
                    'participationHtml'  => $participationHtml,
                )));
            }
        }else{
            $response->setContent(json_encode(array(
                'state' => 3,
                'message' => 'Authentification requise',
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function maybeParticipation($event_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);
        $user = $this->getUser();

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));
        if ($user) {
            if ($event) {
                $participations = $this->participationRepository->findBy(array(
                    'user' => $user,
                    'event' => $event,
                ));

                foreach ($participations as $participationTemp) {
                    $this->em->remove($participationTemp);
                }

                $participation = new Participation();
                $participation->setEvent($event);
                $participation->setUser($user);
                $participation->setStatus(2);
                $participation->setDate(new \DateTime());

                $this->em->persist($participation);
                $this->em->flush();

                $participationHtml = $this->renderView('event/include/participation.html.twig', array(
                    'event'   => $event,
                    'user'    => $user,
                ));

                $response->setContent(json_encode(array(
                    'state'         => 1,
                    'participationHtml'  => $participationHtml,
                )));
            }
        }else{
            $response->setContent(json_encode(array(
                'state' => 3,
                'message' => 'Authentification requise',
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}