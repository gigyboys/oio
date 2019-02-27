<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Subscription;
use App\Entity\Participation;
use App\Entity\Comment;
use App\Form\EvaluationType;
use App\Form\CommentType;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\DocumentRepository;
use App\Repository\FieldRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolPostRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\TypeRepository;
use App\Repository\CommentRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use App\Service\EventService;
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
use App\Repository\TagEventRepository;
use App\Repository\TagRepository;
use App\Repository\GalleryRepository;
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
        EventService $eventService,
        PlatformService $platformService,
        FieldRepository $fieldRepository,
        PostRepository $postRepository,
        SchoolPostRepository $schoolPostRepository,
        DocumentRepository $documentRepository,
        SubscriptionRepository $subscriptionRepository,
        ParticipationRepository $participationRepository,
        CommentRepository $commentRepository,
        TagEventRepository $tagEventRepository,
        TagRepository $tagRepository,
        GalleryRepository $galleryRepository,
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
        $this->eventService = $eventService;
        $this->platformService = $platformService;
        $this->fieldRepository = $fieldRepository;
        $this->postRepository = $postRepository;
        $this->schoolPostRepository = $schoolPostRepository;
        $this->documentRepository = $documentRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->participationRepository = $participationRepository;
        $this->commentRepository = $commentRepository;
        $this->tagEventRepository = $tagEventRepository;
        $this->tagRepository = $tagRepository;
        $this->galleryRepository = $galleryRepository;
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
                    "event_id" 	 => $event->getId(),
                    "event_view" => $event_view,
                ));
            }

            //pagination
            $pagination = $this->renderView('event/include/pagination_list_event.html.twig', array(
                'allEvents'     => $allEvents,
                'events'        => $events,
                'limit'         => $limit,
                'currentpage'   => $page,
                'typeslug'      => $typeslug,
            ));

            //type_links
            $typeLinks = $this->renderView('event/include/event_type_link.html.twig', array(
                'typeslug' => $typeslug,
            ));

            $currentUrl = $this->get('router')->generate('event', array(
                'typeslug'  => $typeslug,
                'page'      => $page
            ));

            $response->setContent(json_encode(array(
                'state'         => 1,
                'events'        => $listEvents,
                'currentpage'   => $page,
                'pagination'    => $pagination,
                'typeLinks'     => $typeLinks,
                'currentUrl'    => $currentUrl,
                'page'          => $page,
            )));
        }else{
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

    public function viewTag($slug, $page, Request $request): Response
    {
        $tag = $this->tagRepository->findOneBy(array(
            'slug' => $slug,
        ));
        if($tag){
            $parameter = $this->parameterRepository->findOneBy(array(
                'parameter' => 'events_by_page',
            ));
            $limit = intval($parameter->getValue());
            $offset = ($page-1) * $limit;
            
            $events = $this->eventService->getEventsByTagOffsetLimit($tag, $offset, $limit);
            $allEvents = $this->eventService->getEventsByTag($tag);
        }else{
            $events = array();
            $allEvents = array();
        }
        $response = new Response();
        if ($request->isXmlHttpRequest()){
            //listEvent
            $listEvents = array();
            foreach($events as $event){
                $event_view = $this->renderView('event/include/event_item.html.twig', array(
                    'event' => $event,
                ));
                array_push($listEvents, array(
                    "event_id" 	 => $event->getId(),
                    "event_view" => $event_view,
                ));
            }

            //pagination
            $pagination = $this->renderView('event/include/pagination_list_event.html.twig', array(
                'tag'           => $tag,
                'allEvents'     => $allEvents,
                'events'        => $events,
                'limit'         => $limit,
                'currentpage'   => $page,
                'typeslug'      => "",
            ));

            //type_links
            $typeLinks = $this->renderView('event/include/event_type_link.html.twig', array(
                'tag'       => $tag,
                'typeslug'  => "",
            ));

            $currentUrl = $this->get('router')->generate('event_view_tag', array(
                'slug'   => $tag->getSlug(),
                'page'  => $page
            ));

            $response->setContent(json_encode(array(
                'state'         => 1,
                'events'        => $listEvents,
                'currentpage'   => $page,
                'pagination'    => $pagination,
                'typeLinks'     => $typeLinks,
                'currentUrl'    => $currentUrl,
                'page'          => $page,
            )));
        }else{
            $response = $this->render('event/index.html.twig', [
                'tag'           => $tag,
                'allEvents'     => $allEvents,
                'events'        => $events,
                'currentpage'   => $page,
                'typeslug'      => "",
                'limit'         => $limit,
                'entityView'    => 'event',
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
            //tags
            $tags = array();
            $tagEvents = $this->tagEventRepository->findBy(array(
                'event' => $event,
            ));
            foreach($tagEvents as $tagEvent){
                $tag = $tagEvent->getTag();
                array_push($tags, $tag);
            }

            //view
            $this->platformService->registerView($event, $user, $request);

            /* comments */
            $allComments = $this->eventService->getValidComments($event);

            $limit = 10;
            $type = "event";
            $order = "DESC";
            $comments = $this->commentRepository->getCommentsLimit($type, $event, $limit, $order);

            $previousComment = null;
            if(count($comments)>0){
                $firstComment = $comments[0];
                $previousComment = $this->commentRepository->getSinceComment($firstComment, $type, $event);
            }

            //nextEvent
            $nextEvent = $this->eventService->getNextEvent($event);

            //previousEvent
            $previousEvent = $this->eventService->getPreviousEvent($event);

            //galleries
            $galleries = $this->galleryRepository->findBy(
                array(
                    "event"     => $event,
                    "deleted"   => false,
                ), 
                array('position'=>'ASC')
            );

            return $this->render('event/view_event.html.twig', [
                'event'             => $event,
                'tags'              => $tags,
                'comments'          => $comments,
                'allComments'       => $allComments,
                'previousComment'   => $previousComment,
                'nextEvent'         => $nextEvent,
                'previousEvent'     => $previousEvent,
                'galleries'         => $galleries,
                'entityView'        => 'event',
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

    public function participationsPopup($event_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));
    
        if ($event) {
            $participations = $this->participationRepository->findBy(array(
                'status' => 1,
                'event' => $event,
            ));

            $participationsPopupHtml = $this->renderView('event/include/participations_popup.html.twig', array(
                'participations' => $participations
            ));

            $response->setContent(json_encode(array(
                'state'                  => 1,
                'participationsPopupHtml' => $participationsPopupHtml,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function newComment($event_id, Request $request)
    {
        $comment = new Comment();
        $event = $this->eventRepository->find($event_id);
        $user = $this->getUser();
        $form = $this->createForm(CommentType::class, $comment);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($user){
            if($event && $user && $event->getActiveComment()){
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    //creation message
                    $comment->setEvent($event);
                    $comment->setUser($user);
                    $comment->setDeleted(false);
                    $comment->setDate(new \DateTime());

                    $this->em->persist($comment);

                    $this->em->flush();

                    $comments = $this->eventService->getValidComments($event);

                    $commentItem = $this->renderView('event/include/comment_item.html.twig', array(
                        'comment' => $comment
                    ));

                    $infoComment = "";
                    if(count($comments) < 2){
                        $infoComment = count($comments)." Commentaire" ;
                    }else{
                        $infoComment = count($comments)." Commentaires";
                    }
                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'commentItem' => $commentItem,
                        'infoComment' => $infoComment,
                    )));
                }
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

    public function loadComments($event_id, $comment_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);
        $lastComment = $this->commentRepository->find($comment_id);

        $response = new Response();
        if($event && $lastComment){
            $limit = 10;
            $type = "event";
            $order = "DESC";
            $comments = $this->commentRepository->getCommentsSince($lastComment, $type, $event, $limit, $order);

            $listComments = array();
            foreach($comments as $comment){
                $commentItem = $this->renderView('event/include/comment_item.html.twig', array(
                    'comment' => $comment
                ));
                array_push($listComments, array(
                    "id" 			=> $comment->getId(),
                    "commentItem" 	=> $commentItem,
                ));
            }

            $previousComment = null;
            $previousCommentId = 0;
            $urlLoadComment = null;

            if($comments[0]){
                $firstComment = $comments[0];
                $previousComment = $this->commentRepository->getSinceComment($firstComment, $type, $event, $order);
            }
            if ($previousComment){
                $previousCommentId = $previousComment->getId();
                $urlLoadComment = $this->generateUrl('event_load_comment', array(
                    'event_id' => $event->getId(),
                    'comment_id' => $previousCommentId,
                ));
            }

            $response->setContent(json_encode(array(
                'state' => 1,
                'comments' => $listComments,
                'previousCommentId' => $previousCommentId,
                'urlLoadComment' => $urlLoadComment,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}