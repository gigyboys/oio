<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Event;
use App\Entity\Logo;
use App\Entity\Post;
use App\Entity\SchoolPost;
use App\Entity\SchoolEvent;
use App\Entity\Tag;
use App\Entity\TagPost;
use App\Entity\TypeSchool;
use App\Form\CategoryEditType;
use App\Form\CategoryInitType;
use App\Form\CoverType;
use App\Form\EventInitType;
use App\Form\LogoType;
use App\Form\PostInitType;
use App\Form\SchoolDescriptionType;
use App\Form\SchoolInitType;
use App\Form\SchoolType;
use App\Form\TagEditType;
use App\Form\TagInitType;
use App\Model\SchoolInit;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\EventRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolPostRepository;
use App\Repository\SchoolEventRepository;
use App\Repository\TagPostRepository;
use App\Repository\TagRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;

class EventController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        SchoolService $schoolService,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        PlatformService $platformService,
        UserRepository $userRepository,
        TypeRepository $typeRepository,
        LogoRepository $logoRepository,
        CoverRepository $coverRepository,
        PostRepository $postRepository,
        TagRepository $tagRepository,
        TagPostRepository $tagPostRepository,
        SchoolPostRepository $schoolPostRepository,
        SchoolEventRepository $schoolEventRepository,
        EventRepository $eventRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolService = $schoolService;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->platformService = $platformService;
        $this->userRepository = $userRepository;
        $this->typeRepository = $typeRepository;
        $this->logoRepository = $logoRepository;
        $this->coverRepository = $coverRepository;
        $this->postRepository = $postRepository;
        $this->tagRepository = $tagRepository;
        $this->tagPostRepository = $tagPostRepository;
        $this->schoolPostRepository = $schoolPostRepository;
        $this->schoolEventRepository = $schoolEventRepository;
        $this->eventRepository = $eventRepository;
        $this->em = $em;
    }


    public function event()
    {
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

        return $this->render('admin/event/events.html.twig', array(
            'events' => $events,
            'publishedEvents' => $publishedEvents,
            'notPublishedEvents' => $notPublishedEvents,
            'view' => 'event',
        ));
    }

    public function addEvent(Request $request)
    {
        $event = new Event();
        $form = $this->createForm(EventInitType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setIntroduction('Introduction '.$event->getTitle());
            $event->setContent('Contenu '.$event->getTitle());

            $slug = $this->platformService->getSlug($event->getTitle(), $event);
            $event->setSlug($slug);
            $event->setDate(new \DateTime());

            //datebegin
            $datebegin = $this->platformService->getDate($event->getDatebeginText(), 'd/m/y h:i');
            //dateend
            $dateend = $this->platformService->getDate($event->getDateendText(), 'd/m/y h:i');

            $event->setDatebegin($datebegin);
            $event->setDateend($dateend);

            $event->setPublished(false);
            $event->setValid(false);
            $event->setDeleted(false);
            $user = $this->getUser();
            $event->setUser($user);
            $event->setShowAuthor(true);
            $event->setActiveComment(true);
            $event->setTovalid(true);

            $this->em->persist($event);

            $this->em->flush();

            return $this->redirectToRoute('admin_event');
            /*
            return $this->redirectToRoute('admin_blog_post_edit', array(
                'post_id' => $event->getId()
            ));
            */
        }

        return $this->render('admin/event/event_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editEvent($event_id)
    {
        $event = $this->eventRepository->find($event_id);

        return $this->render('admin/event/event_edit.html.twig', array(
            'event' => $event,
            'view' => 'event',
        ));
    }

    /*
     * confirm delete event
     */
    public function deleteEvent($event_id, Request $request)
    {
        $event = $this->eventRepository->find($event_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));
        if ($event) {
            $event->setDeleted(true);
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
                'id' => $event_id,
                'events' => $events,
                'publishedEvents' => $publishedEvents,
                'notPublishedEvents' => $notPublishedEvents,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function eventSchools($event_id)
    {
        $event = $this->eventRepository->find($event_id);
        $schools = $this->schoolRepository->findAll();

        return $this->render('admin/event/event_schools.html.twig', array(
            'event' => $event,
            'schools' => $schools,
            'view' => 'event',
        ));
    }

    public function toogleSchool($event_id, $school_id, Request $request)
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
                $schoolEvent = new SchoolEvent();
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
}