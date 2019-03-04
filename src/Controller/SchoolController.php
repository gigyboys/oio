<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Subscription;
use App\Form\EvaluationType;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\DocumentRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolPostRepository;
use App\Repository\SchoolEventRepository;
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
use App\Repository\OptionRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SchoolController extends AbstractController{

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        SchoolContactRepository $schoolContactRepository,
        TypeRepository $typeRepository,
        EvaluationRepository $evaluationRepository,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        SchoolService $schoolService,
        PlatformService $platformService,
        PostRepository $postRepository,
        SchoolPostRepository $schoolPostRepository,
        SchoolEventRepository $schoolEventRepository,
        DocumentRepository $documentRepository,
        SubscriptionRepository $subscriptionRepository,
        OptionRepository $optionRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolContactRepository = $schoolContactRepository;
        $this->typeRepository = $typeRepository;
        $this->evaluationRepository = $evaluationRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->schoolService = $schoolService;
        $this->platformService = $platformService;
        $this->postRepository = $postRepository;
        $this->schoolPostRepository = $schoolPostRepository;
        $this->schoolEventRepository = $schoolEventRepository;
        $this->documentRepository = $documentRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->optionRepository = $optionRepository;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index($typeslug, $page, Request $request): Response
    {

        $parameter = $this->parameterRepository->findOneBy(array(
            'parameter' => 'schools_by_page',
        ));
        $limit = intval($parameter->getValue());
        $offset = ($page-1) * $limit;
        $published = true;

        $types = array("private", "public");
        if(!in_array($typeslug, $types)){
            $type = null;
        }else{
            $type = $this->typeRepository->findOneBy(array(
                'slug' => $typeslug
            ));
        }
        $schools = $this->schoolRepository->getSchoolOffsetLimit($offset, $limit, $published, $type);

        if(!in_array($typeslug, $types)){
            $allSchools = $this->schoolRepository->findBy(array(
                "published" => true,
            ));
        }else {
            $allSchools = $this->schoolRepository->findBy(array(
                "published" => true,
                "type" => $type
            ));
        }

        //$categories
        $allCategories = $this->schoolService->getCategoriesWithPublishedSchool(0);
        shuffle($allCategories);

        $limitCategories = 5;
        if($limitCategories > 0){
            $categoriesLimit = array();
            if(count($allCategories) < $limitCategories){
                $end = count($allCategories);
            }else{
                $end = $limitCategories;
            }

            for ($i=0; $i<$end; $i++) {
                array_push($categoriesLimit, $allCategories[$i]);
            }

            $categories = $categoriesLimit;
        }

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
            if(!$schools && $typeslug != 'all'){
                return $this->redirectToRoute('platform_home');
            }
            $response = $this->render('school/index.html.twig', [
                'allSchools' => $allSchools,
                'schools' => $schools,
                'categories' => $categories,
                'currentpage' => $page,
                'typeslug' => $typeslug,
                'limit' => $limit,
                'entityView' => 'school',
            ]);
        }

        return $response;
    }


    public function categories(Request $request)
    {
        $limit = 0;
        $categories = $this->schoolService->getCategoriesWithPublishedSchool($limit);

        return $this->render('school/categories.html.twig', array(
            'categories' => $categories,
        ));

    }

    public function schoolMap(Request $request)
    {
        $limit = 0;
        $contactTemps = $this->schoolContactRepository->getContactsMap();

        $contacts = array();
        foreach($contactTemps as $contact){
            if($contact->getSchool()->getPublished()){
                array_push($contacts, $contact);
            }
        }
        return $this->render('school/school_map.html.twig', array(
            'contacts' => $contacts,
        ));

    }

    public function schoolOfTheDay(Request $request)
    {
        $school = $this->schoolService->getSchoolOfTheDay();

        return $this->render('school/school-of-the-day.html.twig', array(
            'school' => $school,
        ));

    }

    public function viewCategoryById($id, Request $request)
    {
        $category = $this->categoryRepository->find($id);
        return $this->redirectToRoute('school_category_view', array('slug' => $category->getSlug()));
    }

    public function viewCategory($slug, $page, Request $request)
    {
        $category = $this->categoryRepository->findOneBy(array(
            'slug' => $slug,
        ));

        if($category){
            $limit = $this->parameterRepository->findOneBy(array(
                'parameter' => 'schools_by_page',
            ))->getValue();

            $offset = ($page-1) * $limit;
            $published = true;

            $schools = $this->schoolService->getSchoolByCategoryOffsetLimit($category, $offset, $limit, $published);

            $allSchools = $this->schoolService->getAllSchoolByCategory($category, $published);

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
                $pagination = $this->renderView('school/include/pagination_view_category.html.twig', array(
                    'allSchools' => $allSchools,
                    'schools' => $schools,
                    'category' => $category,
                    'currentpage' => $page,
                    'limit' => $limit,
                ));

                $currentUrl = $this->get('router')->generate('school_category_view', array(
                    'slug' => $category->getSlug(),
                    'page' => $page,
                ));

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'schools' => $listSchools,
                    'currentpage' => $page,
                    'pagination' => $pagination,
                    'currentUrl' => $currentUrl,
                    'category' => $category,
                    'page' => $page,
                )));
            }else{
                $response = $this->render('school/view_category.html.twig', array(
                    'category' => $category,
                    'allSchools' => $allSchools,
                    'schools' => $schools,
                    'currentpage' => $page,
                    'limit' => $limit,
                    'entityView' => 'school',
                ));
            }
        }else{
            $url = $this->get('router')->generate('platform_home');
            return new RedirectResponse($url);
        }

        return $response;
    }

    public function viewById($id, Request $request)
    {
        $school = $this->schoolRepository->find($id);
        return $this->redirectToRoute('school_view', array('slug' => $school->getSlug()));
    }

    public function view($slug, $type, Request $request): Response
    {
        $user = $this->getUser();

        $school = $this->schoolRepository->findOneBy([
            'slug' => $slug,
        ]);

        if($school && ( $school->getPublished() || $this->isGranted('ROLE_ADMIN'))){

            $options = $this->optionRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));

            $schoolContacts = $this->schoolContactRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));
            $coords = array();
            foreach($schoolContacts as $schoolContact){
                if(trim($schoolContact->getLatitude()) != "" && trim($schoolContact->getLongitude()) != ""){
                    array_push($coords, array(
                        "id" 			=> $schoolContact->getId(),
                        "latitude"		=> $schoolContact->getLatitude(),
                        "longitude"		=> $schoolContact->getLongitude(),
                        "label"		=> $schoolContact->getAddress(),
                    ));
                }
            }

            //evaluations
            $evaluations = null;
            $myEvaluation = null;
            if(!$user){
                $evaluations = $this->evaluationRepository->findBy(
                    array(
                        'school' => $school,
                        'current' => true,
                    ),
                    array('id' => 'DESC')
                );
            }else{
                $evaluations = $this->evaluationRepository->findBySchoolNotUser($school, $user);
                $myEvaluation = $this->evaluationRepository->findOneBy(
                    array(
                        'school' => $school,
                        'current' => true,
                        'user' => $user,
                    ),
                    array('id' => 'DESC')
                );
            }

            $allEvaluations = $this->evaluationRepository->findBy(array(
                'school' => $school,
                'current' => true,
            ));
            $countEvaluations = count($allEvaluations);
            $passMark = $this->schoolService->getPassMark($school);

            //categories
            $categories = array();
            $categorySchools = $this->categorySchoolRepository->findBy(array(
                'school' => $school,
            ));

            foreach($categorySchools as $categorySchool){
                $category = $categorySchool->getCategory();
                array_push($categories, $category);
            }

            //posts
            $schoolPosts = $this->schoolPostRepository->findBy(array(
                'school' => $school,
            ));

            $posts = array();
            foreach ($schoolPosts as $schoolPost) {
                $post = $schoolPost->getPost();
                if($post->getValid() && $post->getPublished() && !$post->getDeleted()){
                    array_push($posts, $post);
                }
            }
            usort($posts, function($a, $b) {
                return $b->getId() - $a->getId();
            });

            //events
            $schoolEvents = $this->schoolEventRepository->findBy(array(
                'school' => $school,
            ));

            $events = array();
            foreach ($schoolEvents as $schoolEvent) {
                $event = $schoolEvent->getEvent();
                if($event->getValid() && $event->getPublished() && !$event->getDeleted()){
                    array_push($events, $event);
                }
            }
            usort($events, function($a, $b) {
                return $b->getId() - $a->getId();
            });

            //visibles documents by visitors
            $documents = $this->schoolService->getDocumentsByUser($user, $school);
            //documents with status connected
            $documentsConnected = $this->schoolService->getDocumentsConnected($school);
            //documents with status subscribed
            $documentsSubscribed = $this->schoolService->getDocumentsSubscribed($school);

            //view
            $this->platformService->registerView($school);

            //nextSchool
            $nextSchool = $this->schoolService->getNextSchool($school);

            //previousSchool
            $previousSchool = $this->schoolService->getPreviousSchool($school);

            $types = array("about", "post", "event", "evaluation", "document");
            if (!in_array($type, $types)) {
                $type = "about";
            }
            return $this->render('school/view_school.html.twig', [
                'school'            => $school,
                'options'	        => $options,
                'schoolContacts'	=> $schoolContacts,
                'coords'	        => $coords,
                'passMark'	        => $passMark,
                'countEvaluations'	=> $countEvaluations,
                'evaluations'	    => $evaluations,
                'myEvaluation'	    => $myEvaluation,
                'categories' 		=> $categories,
                'categories' 		=> $categories,
                'posts' 			=> $posts,
                'events' 			=> $events,
                'documents' 		=> $documents,
                'documentsConnected' => $documentsConnected,
                'documentsSubscribed' => $documentsSubscribed,
                'nextSchool' 		=> $nextSchool,
                'previousSchool' 	=> $previousSchool,
                'type' => $type,
            ]);
        }else{
            return $this->redirectToRoute('platform_home');
        }
    }

    public function addEvaluation($id, Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $user = $this->getUser();

            $response = new Response();
            if($user){
                $school = $this->schoolRepository->find($id);

                $evaluation = new Evaluation();
                $formEvaluation = $this->createForm(EvaluationType::class, $evaluation);


                $formEvaluation->handleRequest($request);
                if ($formEvaluation->isValid()) {

                    $evaluations = $this->evaluationRepository->findBy(array(
                        'user' => $user,
                        'school' => $school,
                    ));

                    foreach ($evaluations as $userEvaluation) {
                        $userEvaluation->setCurrent(false);
                        $this->em->persist($userEvaluation);
                    }

                    $evaluation->setUser($user);
                    $evaluation->setSchool($school);
                    $evaluation->setDate(new \DateTime());
                    $evaluation->setCurrent(true);

                    $this->em->persist($evaluation);
                    $this->em->flush();

                    $allEvaluations = $this->evaluationRepository->findBy(array(
                        'school' => $school,
                        'current' => true,
                    ));

                    $countEvaluations = count($allEvaluations);
                    $passMark = $this->schoolService->getPassMark($school);

                    $myEvaluationItem = $this->renderView('school/include/my_evaluation.html.twig', array(
                        'myEvaluation' => $evaluation,
                        'school' => $school,
                    ));
                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'schoolId' => $school->getId(),
                        'evaluationId' => $evaluation->getId(),
                        'evaluationMark' => $evaluation->getMark(),
                        'evaluationComment' => $evaluation->getComment(),
                        'userId' => $user->getId(),
                        'myEvaluationItem' => $myEvaluationItem,
                        'passMark' => "$passMark",
                        'countEvaluations' => $countEvaluations,
                    )));
                }else{
                    $response->setContent(json_encode(array(
                        'state' => 0,
                        'message' => 'serveur message : une erreur est survenue',
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

        }else{
            throw new NotFoundHttpException('Page not found');
        }
    }

    /*
    *return map coordonnees
    */
    public function getMapCoordonnees($slug, Request $request)
    {
        $school = $this->schoolRepository->findOneBy(array(
            'slug' => $slug,
        ));
        $schoolContacts = $this->schoolContactRepository->findBy(array(
            'school' => $school,
            'published' => true,
        ));

        $response = new Response();

        $coords = array();
        foreach($schoolContacts as $schoolContact){
            if(trim($schoolContact->getLatitude()) != "" && trim($schoolContact->getLongitude()) != ""){
                $label = $schoolContact->getSchool()->getName()." - ".$schoolContact->getAddress();
                array_push($coords, array(
                    "id" 			=> $schoolContact->getId(),
                    "latitude"		=> $schoolContact->getLatitude(),
                    "longitude"		=> $schoolContact->getLongitude(),
                    "label"			=> $label,
                ));
            }
        }

        $response->setContent(json_encode(array(
            'state' => 1,
            'schoolId' => $school->getId(),
            'coords' => $coords,
        )));


        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /*
    *return map coordonnees contact
    */
    public function getMapCoordonneesContact($slug, $contact_id, Request $request)
    {
        $school = $this->schoolRepository->findOneBy(array(
            'slug' => $slug,
        ));
        $schoolContact = $this->schoolContactRepository->findOneBy(array(
            'id' => $contact_id,
            'school' => $school,
            'published' => true,
        ));

        $response = new Response();

        $coords = array();
        if($schoolContact){
            if(trim($schoolContact->getLatitude()) != "" && trim($schoolContact->getLongitude()) != ""){
                $label = $schoolContact->getSchool()->getName()." - ".$schoolContact->getAddress();
                array_push($coords, array(
                    "id" 			=> $schoolContact->getId(),
                    "latitude"		=> $schoolContact->getLatitude(),
                    "longitude"		=> $schoolContact->getLongitude(),
                    "label"			=> $label,
                ));
            }
        }

        $response->setContent(json_encode(array(
            'state' => 1,
            'schoolId' => $school->getId(),
            'coords' => $coords,
        )));


        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function downloadDocument($slug, $document_id, Request $request)
    {
        $user = $this->getUser();
        $document = $this->documentRepository->find($document_id);

        /*
        $school = $this->schoolRepository->findBy(array(
            'slug' => $slug,
        ));
        */

        // load the file from the filesystem
        $file = new File($document->getAbsolutePath());

        //download
        $this->platformService->registerDownload($document, $user, $request);

        $originalName = $document->getOriginalName();
        $position = strripos($originalName,".");
        $extension = substr($originalName,$position+1,strlen($originalName));

        $newName = $document->getName().".".$extension;

        return $this->file($file, $newName);
    }

    public function documentStatus($document_id, Request $request)
    {
        $document = $this->documentRepository->find($document_id);
        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
            'message' => 'serveur message : user not connected',
        )));

        if($document) {
            $downloads = $document->getDownloads();
            $response->setContent(json_encode(array(
                'state' => 1,
                'countDownloads' => count($downloads),
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    public function toggleSubscription($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);
        $user = $this->getUser();

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));
        if ($user) {
            if ($school) {
                $subscription = $this->subscriptionRepository->findOneBy(array(
                    'user' => $user,
                    'school' => $school,
                ));

                if($subscription){
                    if($subscription->getActive()){
                        $subscription->setActive(false);
                    }else{
                        $subscription->setActive(true);
                    }
                }else{
                    $subscription = new Subscription();
                    $subscription->setSchool($school);
                    $subscription->setUser($user);
                    $subscription->setActive(true);
                    $subscription->setDate(new \DateTime());
                }

                $this->em->persist($subscription);
                $this->em->flush();

                if($subscription->getActive()){
                    $active = 1;
                }else{
                    $active = 0;
                }

                //documents
                $documents = $this->schoolService->getDocumentsByUser($user, $school);
                //documents with status connected
                $documentsConnected = $this->schoolService->getDocumentsConnected($school);
                //documents with status subscribed
                $documentsSubscribed = $this->schoolService->getDocumentsSubscribed($school);
                $documentHtml = $this->renderView('school/include/document.html.twig', array(
                    'school'    => $school,
                    'documents' => $documents,
                    'documentsConnected' => $documentsConnected,
                    'documentsSubscribed' => $documentsSubscribed,
                ));

                $response->setContent(json_encode(array(
                    'state'         => 1,
                    'active'        => $active,
                    'documentHtml'  => $documentHtml,
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