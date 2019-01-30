<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Event;
use App\Entity\EventIllustration;
use App\Entity\SchoolEvent;
use App\Entity\TagEvent;
use App\Form\EvaluationType;
use App\Form\EventInitType;
use App\Form\EventContentType;
use App\Form\EventType;
use App\Form\EventDateType;
use App\Form\EventLocationType;
use App\Form\EventIllustrationType;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\EventRepository;
use App\Repository\FieldRepository;
use App\Repository\EventIllustrationRepository;
use App\Repository\SchoolEventRepository;
use App\Repository\TagEventRepository;
use App\Repository\TagRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;
use App\Repository\ParameterRepository;
use App\Repository\SchoolContactRepository;
use App\Repository\EvaluationRepository;

class EventManagerController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        SchoolService $schoolService,
        PlatformService $platformService,
        EventIllustrationRepository $eventIllustrationRepository,
        TagRepository $tagRepository,
        TagEventRepository $tagEventRepository,
        SchoolEventRepository $schoolEventRepository,
        EventRepository $eventRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolService = $schoolService;
        $this->platformService = $platformService;
        $this->eventIllustrationRepository = $eventIllustrationRepository;
        $this->tagRepository = $tagRepository;
        $this->tagEventRepository = $tagEventRepository;
        $this->schoolEventRepository = $schoolEventRepository;
        $this->eventRepository = $eventRepository;
        $this->eventRepository = $eventRepository;
        $this->em = $em;

        $this->platformService->registerVisit();
    }


    public function addEventAjax()
    {
        $user = $this->getUser();
        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($user){
            $content = $this->renderView('event/event_add_ajax.html.twig', array(

            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'content' => $content,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doAddEvent(Request $request)
    {
        $event = new Event();
        $form = $this->createForm(EventInitType::class, $event);
        $form->handleRequest($request);
        $errormsg = "";
        if ($form->isSubmitted() && $form->isValid()) { 
            if(trim($event->getTitle())){
                //datebegin
                $datebegin = $this->platformService->getDate($event->getDatebeginText(), 'd/m/y h:i');
                //dateend
                $dateend = $this->platformService->getDate($event->getDateendText(), 'd/m/y h:i');

                if ($datebegin instanceof \DateTime && $dateend instanceof \DateTime) {
                    $event->setIntroduction('Introduction '.$event->getTitle());
                    $event->setContent('Contenu '.$event->getTitle());

                    $slug = $this->platformService->getSlug($event->getTitle(), $event);
                    $event->setSlug($slug);
                    $event->setDate(new \DateTime());

                    $event->setDatebegin($datebegin);
                    $event->setDateend($dateend);

                    $event->setPublished(false);
                    $event->setValid(false);
                    $event->setDeleted(false);
                    $user = $this->getUser();
                    $event->setUser($user);
                    $event->setShowAuthor(true);
                    $event->setActiveComment(true);
                    $event->setTovalid(false);

                    $this->em->persist($event);

                    $this->em->flush();

                    return $this->redirectToRoute('event_manager_edit', array(
                        'event_id' => $event->getId(),
                    ));
                }else{
                    $errormsg = "<div class='error_msg'>Vérifiez votre saisie</div>";
                }
            }else{
                return $this->render('event/add_event.html.twig', array(
                    'form' => $form->createView(),
                    'errormsg' => $errormsg,
                    'event' => $event,
                ));
            }
        }

        return $this->render('event/add_event.html.twig', array(
            'form' => $form->createView(),
            'errormsg' => $errormsg,
            'event' => $event,
        ));
    }

    public function editEvent($event_id)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);
        if($event && $user && $event->getUser()->getId() == $user->getId() ){
            return $this->render('event/event_edit.html.twig', array(
                'event' => $event,
                'entityView' => 'event',
            ));
        }else{
            return $this->redirectToRoute('event');
        }
    }

    public function toggleShowAuthor($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($event && $this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
            if($event->getShowAuthor() == true){
                $event->setShowAuthor(false) ;
            }else{
                $event->setShowAuthor(true) ;
            }

            $this->em->persist($event);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $event->getShowAuthor(),
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function toggleActiveComment($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($event && $this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
            if($event->getActiveComment() == true){
                $event->setActiveComment(false) ;
            }else{
                $event->setActiveComment(true) ;
            }

            $this->em->persist($event);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $event->getActiveComment(),
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function togglePublication($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($event) {
            if ($this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
                if($event->getPublished() == true){
                    $event->setPublished(false) ;
                }else{
                    $event->setPublished(true) ;
                }

                $this->em->persist($event);
                $this->em->flush();

                $events = $this->eventRepository->getEvents();
                $publishedEvents = $this->eventRepository->findBy(array(
                    'published' => true,
                    'tovalid'   => true,
                    'deleted'   => false,
                ));
                $notPublishedEvents = $this->eventRepository->findBy(array(
                    'published' => false,
                    'tovalid'   => true,
                    'deleted'   => false,
                ));

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'case' => $event->getPublished(),
                    'events' => $events,
                    'publishedEvents' => $publishedEvents,
                    'notPublishedEvents' => $notPublishedEvents,
                )));
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function toggleValidation($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($event) {
            if ($this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
                if($event->getValid() == true){
                    $event->setValid(false) ;
                }else{
                    $event->setValid(true) ;
                }

                $this->em->persist($event);
                $this->em->flush();

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'case' => $event->getValid(),
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function toggleDeletion($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($event) {
            if ($this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
                if($event->getDeleted() == true){
                    $event->setDeleted(false) ;
                }else{
                    $event->setDeleted(true) ;
                }

                $this->em->persist($event);
                $this->em->flush();

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'case' => $event->getDeleted(),
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditEvent($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();
        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($event){
            if ($this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
                $eventTemp = new Event();
                $form = $this->createForm(EventType::class, $eventTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $event->setTitle($eventTemp->getTitle());
                    if(trim($eventTemp->getSlug()) == ""){
                        $eventTemp->setSlug($eventTemp->getTitle());
                    }

                    $slug = $this->platformService->getSlug($eventTemp->getSlug(), $event);
                    $event->setSlug($slug);

                    $this->em->persist($event);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'eventId' => $event->getId(),
                        'title' => $event->getTitle(),
                        'slug' => $event->getSlug(),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    /*
    * edit datebegin dateend
    */
    public function doEditDateEvent($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();
        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($event){
            if ($this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
                $eventTemp = new Event();
                $form = $this->createForm(EventDateType::class, $eventTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    //datebegin
                    $datebegin = $this->platformService->getDate($eventTemp->getDatebeginText(), 'd/m/y h:i');
                    //dateend
                    $dateend = $this->platformService->getDate($eventTemp->getDateendText(), 'd/m/y h:i');

                    $event->setDatebegin($datebegin);
                    $event->setDateend($dateend);

                    $this->em->persist($event);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'eventId' => $event->getId(),
                        'datebegin' => $event->getDatebegin()->format('d/m/Y H:i'),
                        'dateend' => $event->getDateend()->format('d/m/Y H:i'),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /*
    * edit location
    */
    public function doEditLocationEvent($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();
        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($event){
            if ($this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
                $eventTemp = new Event();
                $form = $this->createForm(EventLocationType::class, $eventTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $event->setLocation($eventTemp->getLocation());
                    $event->setCity($eventTemp->getCity());

                    $event->setLatitude($eventTemp->getLatitude());
                    $event->setLongitude($eventTemp->getLongitude());

                    $this->em->persist($event);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'eventId' => $event->getId(),
                        'location' => $event->getLocation(),
                        'city' => $event->getCity(),
                        'latitude' => $event->getLatitude(),
                        'longitude' => $event->getLongitude(),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditContentEvent($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();
        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($event){
            if ($this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
                $eventTemp = new Event();
                $form = $this->createForm(EventContentType::class, $eventTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $event->setIntroduction($eventTemp->getIntroduction());
                    $event->setContent($eventTemp->getContent());

                    $this->em->persist($event);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'introduction' => $event->getIntroduction(),
                        'content' => $event->getContent(),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function editIllustrationPopup($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($event && $this->isGranted('ROLE_ADMIN') || $event->getUser() == $user) {
            $illustrations = $this->eventIllustrationRepository->findBy(array(
                'event' => $event
            ));
            $current = $this->eventIllustrationRepository->findOneBy(array(
                'event' => $event,
                'current' => true,
            ));

            $content = $this->renderView('event/edit_illustration_popup.html.twig', array(
                'event' => $event,
                'illustrations' => $illustrations,
                'current' => $current,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'content' => $content,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function uploadIllustration($event_id, Request $request)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($event && $this->isGranted('ROLE_ADMIN') || $event->getUser() == $user) {
            $illustration = new EventIllustration();
            $form = $this->createForm(EventIllustrationType::class, $illustration);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $illustration->getFile();

                $t=time();
                $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$event->getId().'_'.$t.'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('event_illustration_directory'),
                        $fileName
                    );
                    $illustration->setPath($fileName);
                    $illustration->setOriginalname($file->getClientOriginalName());
                    $illustration->setName($file->getClientOriginalName());

                    $illustrations = $this->eventIllustrationRepository->findBy(array('event' => $event));

                    foreach ($illustrations as $eventIllustration) {
                        $eventIllustration->setCurrent(false);
                    }

                    $illustration->setCurrent(true);
                    
                    $illustration->setEvent($event);

                    $this->em->persist($illustration);
                    $this->em->flush();
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $path = $illustration->getWebPath();
                $illustration116x116 = $this->platformService->imagineFilter($path, '116x116');
                $illustration600x250 = $this->platformService->imagineFilter($path, '600x250');
                $illustrationItemContent = $this->renderView('event/include/illustration_item.html.twig', array(
                    'event' => $event,
                    'illustration' => $illustration,
                    'classActive' => 'active'
                ));

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'illustration116x116'	 	=> $illustration116x116,
                    'illustration600x250'	 	=> $illustration600x250,
                    'illustrationItemContent'  => $illustrationItemContent,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function selectIllustration($event_id, $illustration_id)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($event && $this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
            if($illustration_id == 0){
                $illustrations = $this->eventIllustrationRepository->findBy(array(
                    'event' => $event
                ));

                foreach ($illustrations as $illustration) {
                    $illustration->setCurrent(false);
                    $this->em->persist($illustration);
                }
                $this->em->flush();

                $defaultIllustrationPath = 'default/images/event/illustration/default.jpeg';
                $illustrationPath = $defaultIllustrationPath;
            }else{
                $illustration = $this->eventIllustrationRepository->find($illustration_id);

                if($illustration && $event->getId() == $illustration->getEvent()->getId()){
                    $illustrations = $this->eventIllustrationRepository->findBy(array(
                        'event' => $event
                    ));

                    foreach ($illustrations as $illustrationTmp) {
                        $illustrationTmp->setCurrent(false);
                        $this->em->persist($illustrationTmp);
                    }

                    $illustration->setCurrent(true);

                    $this->em->persist($illustration);
                    $this->em->flush();
                    $illustrationPath = $illustration->getWebPath();
                }
            }

            $illustration116x116 = $this->platformService->imagineFilter($illustrationPath, '116x116');
            $illustration600x250 = $this->platformService->imagineFilter($illustrationPath, '600x250');

            $response->setContent(json_encode(array(
                'state' => 1,
                'illustration116x116' => $illustration116x116,
                'illustration600x250' => $illustration600x250,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function deleteIllustration($event_id, $illustration_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);
        $illustration = $this->eventIllustrationRepository->find($illustration_id);
        $user = $this->getUser();
        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($event && $illustration && $this->isGranted('ROLE_ADMIN') || $event->getUser() == $user){
            if($event->getId() == $illustration->getEvent()->getId()){
                $illustrationId = $illustration->getId();
                $this->em->remove($illustration);
                $this->em->flush();

                $current = $this->eventIllustrationRepository->findOneBy(array(
                    'event' => $event,
                    'current' => true,
                ));

                if($current){
                    $isCurrent = false;
                    $path = $current->getWebPath();
                }else{
                    $isCurrent = true;
                    $defaultPath = 'default/images/event/illustration/default.jpeg';
                    $path = $defaultPath;
                }

                $illustration116x116 = $this->platformService->imagineFilter($path, '116x116');
                $illustration600x250 = $this->platformService->imagineFilter($path, '600x250');

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'illustrationId' => $illustrationId,
                    'illustration116x116' => $illustration116x116,
                    'illustration600x250' => $illustration600x250,
                    'isCurrent' => $isCurrent,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function ToValidEventAjax($event_id)
    {
        $response = new Response();
        $event = $this->eventRepository->find($event_id);
        $content = $this->renderView('event/event_tovalid_ajax.html.twig', array(
            'event' => $event
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doToValidEvent($event_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);
        $user = $this->getUser();

        if($event && $user && !$event->getTovalid() && $event->getUser()->getId() == $user->getId()){
            $event->setTovalid(true);
            $this->em->persist($event);
            $this->em->flush();
            return $this->redirectToRoute('event_manager_edit', array(
                'event_id' => $event->getId()
            ));
        }
        return $this->redirectToRoute('event');
    }

    public function eventSchools($event_id)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);

        if($event && $user && $event->getUser()->getId() == $user->getId() ){
            $schools = $this->schoolService->findSchoolsSubscription($user);
            return $this->render('event/event_schools.html.twig', array(
                'event' => $event,
                'schools' => $schools,
                'entityView' => 'event',
            ));
        }
        return $this->redirectToRoute('event');
    }

    public function toggleSchool($event_id, $school_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);
        $school = $this->schoolRepository->find($school_id);

        $response = new Response();

        if ($event && $school) {
            $schoolEvent = $this->schoolEventRepository->findOneBy(array(
                'event' => $event,
                'school' => $school,
            ));

            if($schoolEvent){
                $this->em->remove($schoolEvent);
                $isSchool = false;
            }else{
                $schoolEvent = new schoolEvent();
                $schoolEvent->setEvent($event);
                $schoolEvent->setSchool($school);

                $this->em->persist($schoolEvent);
                $isSchool = true;
            }
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $isSchool,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function eventTags($event_id)
    {
        $user = $this->getUser();
        $event = $this->eventRepository->find($event_id);
        $tags = $this->tagRepository->findAll();

        if($event && $user && $event->getUser()->getId() == $user->getId() ){
            return $this->render('event/event_tags.html.twig', array(
                'event'          => $event,
                'tags'          => $tags,
                'entityView'    => 'event',
            ));
        }
        return $this->redirectToRoute('event');
    }


    public function toggleTag($event_id, $tag_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);
        $tag = $this->tagRepository->find($tag_id);

        $response = new Response();

        if ($event && $tag) {
            $tagEvent = $this->tagEventRepository->findOneBy(array(
                'event' => $event,
                'tag' => $tag,
            ));

            if($tagEvent){
                $this->em->remove($tagEvent);
                $isTag = false;
            }else{
                $tagEvent = new TagEvent();
                $tagEvent->setEvent($event);
                $tagEvent->setTag($tag);
                $tagEvent->setCurrent(false);

                $this->em->persist($tagEvent);
                $isTag = true;
            }
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $isTag,
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