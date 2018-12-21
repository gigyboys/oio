<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Event;
use App\Entity\Post;
use App\Entity\PostIllustration;
use App\Entity\SchoolPost;
use App\Entity\TagPost;
use App\Form\EvaluationType;
use App\Form\EventContentType;
use App\Form\EventType;
use App\Form\PostContentType;
use App\Form\PostIllustrationType;
use App\Form\PostInitType;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\EventRepository;
use App\Repository\FieldRepository;
use App\Repository\PostIllustrationRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolPostRepository;
use App\Repository\TagPostRepository;
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
        PostRepository $postRepository,
        PostIllustrationRepository $postIllustrationRepository,
        TagRepository $tagRepository,
        TagPostRepository $tagPostRepository,
        SchoolPostRepository $schoolPostRepository,
        EventRepository $eventRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolService = $schoolService;
        $this->platformService = $platformService;
        $this->postRepository = $postRepository;
        $this->postIllustrationRepository = $postIllustrationRepository;
        $this->tagRepository = $tagRepository;
        $this->tagPostRepository = $tagPostRepository;
        $this->schoolPostRepository = $schoolPostRepository;
        $this->eventRepository = $eventRepository;
        $this->eventRepository = $eventRepository;
        $this->em = $em;

        $this->platformService->registerVisit();
    }


    public function toogleShowAuthor($event_id, Request $request)
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


    public function toogleActiveComment($event_id, Request $request)
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

    public function tooglePublication($event_id, Request $request)
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

    public function toogleValidation($event_id, Request $request)
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

    public function toogleDeletion($event_id, Request $request)
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

                    //datebegin
                    $datebegin = $this->platformService->getDate($eventTemp->getDatebeginText(), 'd/m/y h:i');
                    //dateend
                    $dateend = $this->platformService->getDate($eventTemp->getDateendText(), 'd/m/y h:i');

                    $event->setDatebegin($datebegin);
                    $event->setDateend($dateend);

                    $event->setLocation($eventTemp->getLocation());
                    $event->setCity($eventTemp->getCity());

                    $this->em->persist($event);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'eventId' => $event->getId(),
                        'title' => $event->getTitle(),
                        'slug' => $event->getSlug(),
                        'datebegin' => $event->getDatebegin()->format('m/d/Y H:i'),
                        'dateend' => $event->getDateend()->format('m/d/Y H:i'),
                        'location' => $event->getLocation(),
                        'city' => $event->getCity(),
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

    /*
    public function editIllustrationPopup($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($post && $this->isGranted('ROLE_ADMIN') || $post->getUser() == $user) {
            $illustrations = $this->postIllustrationRepository->findBy(array(
                'post' => $post
            ));
            $current = $this->postIllustrationRepository->findOneBy(array(
                'post' => $post,
                'current' => true,
            ));

            $content = $this->renderView('blog/edit_illustration_popup.html.twig', array(
                'post' => $post,
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

    public function uploadIllustration($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($post && $this->isGranted('ROLE_ADMIN') || $post->getUser() == $user) {
            $illustration = new PostIllustration();
            $form = $this->createForm(PostIllustrationType::class, $illustration);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $illustration->getFile();

                $t=time();
                $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$post->getId().'_'.$t.'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('illustration_directory'),
                        $fileName
                    );
                    $illustration->setPath($fileName);
                    $illustration->setOriginalname($file->getClientOriginalName());
                    $illustration->setName($file->getClientOriginalName());

                    $illustrations = $this->postIllustrationRepository->findBy(array('post' => $post));

                    foreach ($illustrations as $postIllustration) {
                        $postIllustration->setCurrent(false);
                    }

                    $illustration->setCurrent(true);

                    $illustration->setPost($post);

                    $this->em->persist($illustration);
                    $this->em->flush();
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $path = $illustration->getWebPath();
                $illustration116x116 = $this->platformService->imagineFilter($path, '116x116');
                $illustration600x250 = $this->platformService->imagineFilter($path, '600x250');
                $illustrationItemContent = $this->renderView('blog/include/illustration_item.html.twig', array(
                    'post' => $post,
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

    public function selectIllustration($post_id, $illustration_id)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($post && $this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
            if($illustration_id == 0){
                $illustrations = $this->postIllustrationRepository->findBy(array(
                    'post' => $post
                ));

                foreach ($illustrations as $illustration) {
                    $illustration->setCurrent(false);
                    $this->em->persist($illustration);
                }
                $this->em->flush();

                $defaultIllustrationPath = 'default/images/post/illustration/default.jpeg';
                $illustrationPath = $defaultIllustrationPath;
            }else{
                $illustration = $this->postIllustrationRepository->find($illustration_id);

                if($illustration && $post->getId() == $illustration->getPost()->getId()){
                    $illustrations = $this->postIllustrationRepository->findBy(array(
                        'post' => $post
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

    public function deleteIllustration($post_id, $illustration_id, Request $request)
    {
        $post = $this->postRepository->find($post_id);
        $illustration = $this->postIllustrationRepository->find($illustration_id);
        $user = $this->getUser();
        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($post && $illustration && $this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
            if($post->getId() == $illustration->getPost()->getId()){
                $illustrationId = $illustration->getId();
                $this->em->remove($illustration);
                $this->em->flush();

                $current = $this->postIllustrationRepository->findOneBy(array(
                    'post' => $post,
                    'current' => true,
                ));

                if($current){
                    $isCurrent = false;
                    $path = $current->getWebPath();
                }else{
                    $isCurrent = true;
                    $defaultPath = 'default/images/post/illustration/default.jpeg';
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

    public function addPostAjax()
    {
        $response = new Response();

        $content = $this->renderView('blog/post_add_ajax.html.twig', array(

        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doAddPost(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostInitType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if(trim($post->getTitle())){
                $slug = $this->platformService->getSlug($post->getTitle(), $post);

                $post->setSlug($slug);
                $post->setDate(new \DateTime());
                $post->setPublished(false);
                $post->setValid(false);
                $post->setDeleted(false);
                $post->setTovalid(false);

                $user = $this->getUser();
                $post->setUser($user);
                $post->setShowAuthor(true);
                $post->setActiveComment(true);

                $post->setIntroduction("Description ".$post->getTitle());
                $post->setContent("Contenu ".$post->getTitle());

                $this->em->persist($post);

                $this->em->flush();

                return $this->redirectToRoute('blog_manager_edit_post', array(
                    'post_id' => $post->getId()
                ));

            }else{
                return $this->render('blog/add_post.html.twig', array(
                    'form' => $form->createView(),
                ));
            }
        }

        return $this->render('blog/add_post.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editPost($post_id)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);
        if($post && $user && $post->getUser()->getId() == $user->getId() ){
            if ($post->getUser() == $user){
                return $this->render('blog/post_edit.html.twig', array(
                    'post' => $post,
                    'entityView' => 'blog',
                ));
            }else{
                return $this->redirectToRoute('blog');
            }
        }else{
            return $this->redirectToRoute('blog');
        }
    }

    public function ToValidPostAjax($post_id)
    {
        $response = new Response();
        $post = $this->postRepository->find($post_id);
        $content = $this->renderView('blog/post_tovalid_ajax.html.twig', array(
            'post' => $post
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doToValidPost($post_id, Request $request)
    {
        $post = $this->postRepository->find($post_id);
        $user = $this->getUser();

        if($post && $user && !$post->getTovalid() && $post->getUser()->getId() == $user->getId()){
            $post->setTovalid(true);
            $this->em->persist($post);
            $this->em->flush();
            return $this->redirectToRoute('blog_manager_edit_post', array(
                'post_id' => $post->getId()
            ));
        }
        return $this->redirectToRoute('blog');
    }

    public function postTags($post_id)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);
        $tags = $this->tagRepository->findAll();

        if($post && $user && $post->getUser()->getId() == $user->getId() ){
            return $this->render('blog/post_tags.html.twig', array(
                'post' => $post,
                'tags' => $tags,
                'entityView' => 'blog',
            ));
        }
        return $this->redirectToRoute('blog');
    }


    public function toogleTag($post_id, $tag_id, Request $request)
    {
        $post = $this->postRepository->find($post_id);
        $tag = $this->tagRepository->find($tag_id);

        $response = new Response();

        if ($post && $tag) {
            $tagPost = $this->tagPostRepository->findOneBy(array(
                'post' => $post,
                'tag' => $tag,
            ));

            if($tagPost){
                $this->em->remove($tagPost);
                $isTag = false;
            }else{
                $tagPost = new TagPost();
                $tagPost->setPost($post);
                $tagPost->setTag($tag);
                $tagPost->setCurrent(false);

                $this->em->persist($tagPost);
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

    public function postSchools($post_id)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);
        $schools = $this->schoolService->findSchoolsSubscription($user);

        if($post && $user && $post->getUser()->getId() == $user->getId() ){
            return $this->render('blog/post_schools.html.twig', array(
                'post' => $post,
                'schools' => $schools,
                'entityView' => 'blog',
            ));
        }
        return $this->redirectToRoute('blog');
    }

    public function toogleSchool($post_id, $school_id, Request $request)
    {
        $post = $this->postRepository->find($post_id);
        $school = $this->schoolRepository->find($school_id);

        $response = new Response();

        if ($post && $school) {
            $schoolPost = $this->schoolPostRepository->findOneBy(array(
                'post' => $post,
                'school' => $school,
            ));

            if($schoolPost){
                $this->em->remove($schoolPost);
                $isSchool = false;
            }else{
                $schoolPost = new SchoolPost();
                $schoolPost->setPost($post);
                $schoolPost->setSchool($school);

                $this->em->persist($schoolPost);
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
    */
}