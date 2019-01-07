<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\ParameterRepository;
use App\Repository\PostRepository;
use App\Repository\TypeRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;

class SearchController extends AbstractController {

    public function __construct(
        ParameterRepository $parameterRepository,
        SchoolRepository $schoolRepository,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        TypeRepository $typeRepository,
        PostRepository $postRepository,
        PlatformService $platformService,
        ObjectManager $em
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->schoolRepository = $schoolRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->typeRepository = $typeRepository;
        $this->postRepository = $postRepository;
        $this->platformService = $platformService;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index($page, Request $request)
    {
        $entity = $request->query->get('entity');
        $q = $request->query->get('q');

        switch ($entity){
            case "school":
                $published = true;
                $schools = $this->schoolRepository->getSchoolSearch($q, $published);

                $schoolsArrayTmp = array();

                $catSlug = $request->query->get('category');
                $category = null;
                if($catSlug != "all"){
                    $category =  $this->categoryRepository->findOneBy(array(
                        'slug' => $catSlug
                    ));
                    foreach ($schools as $school) {
                        $categorySchool =  $this->categorySchoolRepository->findOneBy(array(
                            'school' => $school,
                            'category' => $category
                        ));
                        if($categorySchool){
                            array_push($schoolsArrayTmp, $school);
                        }
                    }
                    $schoolsArray = $schoolsArrayTmp;
                }else{
                    $schoolsArray = $schools;
                }

                $schoolsArrayTmp = array();
                $typeSlug = $request->query->get('type');
                $typeEntity = null;
                if($typeSlug != "all"){
                    $typeEntity =  $this->typeRepository->findOneBy(array(
                        'slug' => $typeSlug
                    ));
                    foreach ($schoolsArray as $school) {
                        if($school->getType()->getId() == $typeEntity->getId()){
                            array_push($schoolsArrayTmp, $school);
                        }
                    }
                    $schoolsArray = $schoolsArrayTmp;
                }

                $parameter = $this->parameterRepository->findOneBy(array(
                    'parameter' => 'schools_by_page',
                ));
                $limit = $parameter->getValue();
                $offset = ($page-1) * $limit;

                if(count($schoolsArray) < $limit+$offset){
                    $end = count($schoolsArray);
                }else{
                    $end = $limit+$offset;
                }
                $resultList = array();
                for ($i=$offset; $i<$end; $i++) {
                    array_push($resultList, $schoolsArray[$i]);
                }

                $entityView = "school";
                $response = new Response();
                if ($request->isXmlHttpRequest()){
                    //listSchool
                    $listSchools = array();
                    foreach($resultList as $school){
                        $school_view = $this->renderView('school/school_item.html.twig', array(
                            'school' => $school,
                        ));
                        array_push($listSchools, array(
                            "school_id" 	=> $school->getId(),
                            "school_view" 	=> $school_view,
                        ));
                    }

                    //pagination
                    $pagination = $this->renderView('school/include/pagination_list_school_search.html.twig', array(
                        'entity' 		=> $entity,
                        'q' 			=> $q,
                        'catSlug'		=> $catSlug,
                        'typeSlug'		=> $typeSlug,
                        'allSchools'    => $schoolsArray,
                        'schools'       => $resultList,
                        'limit'         => $limit,
                        'currentpage'   => $page,
                    ));

                    $currentUrl = $this->get('router')->generate('platform_search', array(
                        'page' => $page,
                        'entity' => $entity,
                        'q' => $q,
                        'category' => $catSlug,
                        'type' => $typeSlug
                    ));

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'schools' => $listSchools,
                        'currentpage' => $page,
                        'pagination' => $pagination,
                        'currentUrl' => $currentUrl,
                        'page' => $page,
                    )));
                }else{
                    $response = $this->render('search/search.html.twig', array(
                        'entity' 		=> $entity,
                        'q' 			=> $q,
                        'catSlug'		=> $catSlug,
                        'category'		=> $category,
                        'typeSlug'		=> $typeSlug,
                        'typeEntity'    => $typeEntity,
                        'resultList' 	=> $resultList,
                        'allSchools' 	=> $schoolsArray,
                        'limit' 		=> $limit,
                        'currentpage'   => $page,
                        'entityView'	=> $entityView,
                    ));
                }
                return $response;
                break ;
            case "post":
                $allPosts = $this->postRepository->getPostSearch($q);
                
                $entityView = "blog";

                $parameter = $this->parameterRepository->findOneBy(array(
                    'parameter' => 'posts_by_page',
                ));
                $limit = $parameter->getValue();
                $offset = ($page-1) * $limit;

                if(count($allPosts) < $limit+$offset){
                    $end = count($allPosts);
                }else{
                    $end = $limit+$offset;
                }
                $resultList = array();
                for ($i=$offset; $i<$end; $i++) {
                    array_push($resultList, $allPosts[$i]);
                }

                $response = new Response();
                if ($request->isXmlHttpRequest()){
                    //listPost
                    $listPosts = array();
                    foreach($resultList as $post){
                        $post_view = $this->renderView('blog/include/post_item.html.twig', array(
                            'post' => $post,
                        ));
                        array_push($listPosts, array(
                            "post_id" 	=> $post->getId(),
                            "post_view" => $post_view,
                        ));
                    }

                    //pagination
                    $pagination = $this->renderView('blog/include/pagination_list_post_search.html.twig', array(
                        'entity' 		=> $entity,
                        'q' 			=> $q,
                        'allPosts'      => $allPosts,
                        'posts'         => $resultList,
                        'limit'         => $limit,
                        'currentpage'   => $page,
                    ));

                    $currentUrl = $this->get('router')->generate('platform_search', array(
                        'page'      => $page,
                        'entity'    => $entity,
                        'q'         => $q,
                    ));

                    $response->setContent(json_encode(array(
                        'state'         => 1,
                        'posts'         => $listPosts,
                        'currentpage'   => $page,
                        'pagination'    => $pagination,
                        'currentUrl'    => $currentUrl,
                        'page'          => $page,
                    )));
                }else{
                    $response = $this->render('search/search.html.twig', array(
                        'entity' 		=> $entity,
                        'q' 			=> $q,
                        'allPosts' 	    => $allPosts,
                        'resultList' 	=> $resultList,
                        'entityView'	=> $entityView,
                        'limit' 		=> $limit,
                        'currentpage'   => $page,
                    ));
                }
                return $response;
                break ;
            /*
        case "advert":
            $adverts = $advertRepository->getAdvertSearch($q);
            $resultList =  $adverts;
            $entityView = "advert";

            return $this->render('COMPlatformBundle:search:search.html.twig', array(
                'entity' 		=> $entity,
                'q' 			=> $q,
                'resultList' 	=> $resultList,
                'entityView'	=> $entityView,
                'locale' => $locale,
            ));
            break ;
            */
        }
    }

    public function getSingleSchool(Request $request){
        $q = $request->query->get('q');

        $publishState = 1; // published == true
        $schools = $this->schoolRepository->getSchoolSearch($q, $publishState);

        $schoolsArrayTmp = array();

        $catSlug = $request->query->get('category');
        if($catSlug != "all"){
            $category =  $this->categoryRepository->findOneBy(array(
                'slug' => $catSlug
            ));
            foreach ($schools as $school) {
                $categorySchool =  $this->categorySchoolRepository->findOneBy(array(
                    'school' => $school,
                    'category' => $category
                ));
                if($categorySchool){
                    array_push($schoolsArrayTmp, $school);
                }
            }
            $schoolsArray = $schoolsArrayTmp;
        }else{
            $schoolsArray = $schools;
        }


        $schoolsArrayTmp = array();
        $typeSlug = $request->query->get('type');
        if($typeSlug != "all"){
            $type =  $this->typeRepository->findOneBy(array(
                'slug' => $typeSlug
            ));
            foreach ($schoolsArray as $school) {
                if($school->getType()->getId() == $type->getId()){
                    array_push($schoolsArrayTmp, $school);
                }
            }
            $schoolsArray = $schoolsArrayTmp;
        }


        $response = new Response();
        if($schoolsArray){
            $randomIndex = rand(0, count($schoolsArray)-1);

            $school = $schoolsArray[$randomIndex];
            $school_view = $this->renderView('school/include/school_single_result.html.twig', array(
                'school' => $school,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'schoolid' => $school->getId(),
                'schoolname' => $school->getName(),
                'school_view' => $school_view,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }

        return $response;
    }
}