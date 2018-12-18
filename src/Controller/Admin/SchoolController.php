<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Logo;
use App\Entity\SchoolOfTheDay;
use App\Entity\TypeSchool;
use App\Form\CategoryEditType;
use App\Form\CategoryInitType;
use App\Form\CoverType;
use App\Form\LogoType;
use App\Form\SchoolDescriptionType;
use App\Form\SchoolInitType;
use App\Form\SchoolType;
use App\Form\SearchDateIntervalType;
use App\Form\SodInitType;
use App\Model\SchoolInit;
use App\Model\SearchDateInterval;
use App\Model\SodInit;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\LogoRepository;
use App\Repository\OptionRepository;
use App\Repository\ParameterRepository;
use App\Repository\SchoolOfTheDayRepository;
use App\Repository\TypeRepository;
use App\Repository\TypeSchoolRepository;
use App\Repository\UserRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;

class SchoolController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        OptionRepository $optionRepository,
        ParameterRepository $parameterRepository,
        SchoolService $schoolService,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        PlatformService $platformService,
        UserRepository $userRepository,
        TypeRepository $typeRepository,
        LogoRepository $logoRepository,
        CoverRepository $coverRepository,
        SchoolOfTheDayRepository $schoolOfTheDayRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->optionRepository = $optionRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolService = $schoolService;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->platformService = $platformService;
        $this->userRepository = $userRepository;
        $this->typeRepository = $typeRepository;
        $this->logoRepository = $logoRepository;
        $this->coverRepository = $coverRepository;
        $this->schoolOfTheDayRepository = $schoolOfTheDayRepository;
        $this->em = $em;
    }

    public function index(): Response
    {
        //$schools = $this->schoolRepository->findAll();
        $schools = $this->schoolRepository->findOrderBy("id", "ASC", null);
        $publishedSchools = $this->schoolRepository->findBy(array(
            'published' => true
        ));
        $notPublishedSchools = $this->schoolRepository->findBy(array(
            'published' => false
        ));

        return $this->render('admin/school/school.html.twig', array(
            'schools' => $schools,
            'publishedSchools' => $publishedSchools,
            'notPublishedSchools' => $notPublishedSchools,
        ));
    }

    public function position(): Response
    {
        $schools = $this->schoolRepository->findOrderBy("position", "ASC", null);
        $publishedSchools = $this->schoolRepository->findBy(array(
            'published' => true
        ));
        $notPublishedSchools = $this->schoolRepository->findBy(array(
            'published' => false
        ));

        return $this->render('admin/school/school-position.html.twig', array(
            'schools' => $schools,
            'publishedSchools' => $publishedSchools,
            'notPublishedSchools' => $notPublishedSchools,
        ));
    }

    public function savePosition(Request $request): Response
    {
        $order = $request->query->get('order');

        $ids = explode("-", $order);
        $position = 0;
        foreach ($ids as $id){
            $school = $this->schoolRepository->find($id);
            if($school){
                $position++;
                $school->setPosition($position);
            }
            $this->em->flush();
        }

        $response = new Response();

        $response->setContent(json_encode(array(
            'state' => 1,
            'order' => $order,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function editSchool($id)
    {
        $school = $this->schoolRepository->find($id);

        $types = $this->typeRepository->findAll();
        $options = $this->optionRepository->findAll();
        $schoolTemp = new SchoolInit();
        $formSchool = $this->createForm(SchoolDescriptionType::class, $schoolTemp);

        return $this->render('admin/school/edit_school.html.twig', array(
            'school' => $school,
            'types' => $types,
            'options' => $options,
            'formDesc' => $formSchool->createView()
        ));
    }

    public function addSchool(Request $request)
    {
        $schoolInit = new SchoolInit();
        $formInitSchool = $this->createForm(SchoolInitType::class, $schoolInit);
        $formInitSchool->handleRequest($request);

        if ($formInitSchool->isSubmitted() && $formInitSchool->isValid()) {
            $school = new School();
            $school->setName($schoolInit->getName());
            $school->setShortName($schoolInit->getShortName());

            $slug = $this->platformService->getSlug($school->getName(), $school);

            $school->setSlug($slug);
            $published = false;
            $school->setPublished($published);
            $school->setShortDescription($school->getName()." Courte Description" );
            $school->setDescription($school->getName()." Description" );

            //position
            $lastSchool = $this->schoolRepository->findLastSchool('position');
            $position = 0;
            if($lastSchool){
                $position = $lastSchool->getPosition() + 1;
            }
            $school->setPosition($position);

            //option
            $firstOption = $this->optionRepository->find(1);
            $school->setOption($firstOption);

            //type
            $type = $this->typeRepository->find($schoolInit->getTypeId());
            if($type){
                $school->setType($type);
            }

            $this->em->persist($school);
            $this->em->flush();

            return $this->redirectToRoute('admin_school_edit', array('id' => $school->getId()));
        }

        return $this->render('admin/school/add_school.html.twig', array(
            'formInitSchool' => $formInitSchool->createView(),
        ));
    }

    public function tooglePublication($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);
        $response = new Response();

        if ($school) {
            if($school->getPublished() == true){
                $school->setPublished(false) ;
            }else{
                $school->setPublished(true) ;
            }

            $this->em->persist($school);
            $this->em->flush();

            $schools = $this->schoolRepository->findAll();
            $publishedSchools = $this->schoolRepository->findBy(array(
                'published' => true
            ));
            $notPublishedSchools = $this->schoolRepository->findBy(array(
                'published' => false
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $school->getPublished(),
                'schools' => $schools,
                'publishedSchools' => $publishedSchools,
                'notPublishedSchools' => $notPublishedSchools,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditSchool($id, Request $request)
    {
        $school = $this->schoolRepository->find($id);

        $schoolTemp = new SchoolInit();
        $formSchool = $this->createForm(SchoolType::class, $schoolTemp);
        $formSchool->handleRequest($request);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($formSchool->isSubmitted() && $formSchool->isValid()) {
            $school->setName($schoolTemp->getName());
            $school->setShortName($schoolTemp->getShortName());
            $school->setSlogan($schoolTemp->getslogan());

            $slug = $this->platformService->getSlug($schoolTemp->getSlug(), $school);

            $school->setSlug($slug);

            //option
            $option = $this->optionRepository->find($schoolTemp->getOptionId());
            $school->setOption($option);

            //type
            $type = $this->typeRepository->find($schoolTemp->getTypeId());
            if($type){
                $school->setType($type);
            }

            $this->em->persist($school);

            $this->em->flush();

            $response->setContent(json_encode(array(
                'state'         => 1,
                'name'          => $school->getName(),
                'schoolId'      => $school->getId(),
                'shortName'     => $school->getShortName(),
                'slug'          => $school->getSlug(),
                'slogan'        => $school->getSlogan(),
                'typeId'        => $type->getId(),
                'typeName'      => $type->getName(),
                'optionId'      => $option->getId(),
                'optionName'    => ucfirst($option->getName()),
                'optionPluralName' => ucfirst($option->getPluralName()),
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditSchoolDescription($id, Request $request)
    {
        $school = $this->schoolRepository->find($id);

        $schoolTemp = new SchoolInit();
        $formSchool = $this->createForm(SchoolDescriptionType::class, $schoolTemp);
        $formSchool->handleRequest($request);

        $response = new Response();

        if ($formSchool->isSubmitted() && $formSchool->isValid()) {
            $school->setShortDescription($schoolTemp->getShortDescription());
            $school->setDescription($schoolTemp->getDescription());
            $this->em->persist($school);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'shortDescription' => $school->getShortDescription(),
                'description' => $school->getDescription(),
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function categories($school_id)
    {
        $school = $this->schoolRepository->find($school_id);
        $categories = $this->categoryRepository->findAll();

        return $this->render('admin/school/school_categories.html.twig', array(
            'school' => $school,
            'categories' => $categories
        ));
    }

    public function toogleCategory($school_id, $category_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);
        $category = $this->categoryRepository->find($category_id);

        $response = new Response();

        if ($school && $category) {
            $categorySchool = $this->categorySchoolRepository->findOneBy(array(
                'school' => $school,
                'category' => $category,
            ));

            if($categorySchool){
                $this->em->remove($categorySchool);
                $isCategory = false;
            }else{
                $categorySchool = new CategorySchool();
                $categorySchool->setSchool($school);
                $categorySchool->setCategory($category);
                $categorySchool->setCurrent(false);

                $this->em->persist($categorySchool);
                $isCategory = true;
            }
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $isCategory,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function modifyLogoPopup($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);
        $logos = $this->logoRepository->findBy(array(
            'school' => $school
        ));
        $currentLogo = $this->logoRepository->findOneBy(array(
            'school' => $school,
            'current' => true,
        ));

        $response = new Response();

        $content = $this->renderView('admin/school/modify_logo_popup.html.twig', array(
            'school' => $school,
            'logos' => $logos,
            'currentLogo' => $currentLogo,
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function modifyLogo($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);

        $logo = new Logo();
        $formLogo = $this->createForm(LogoType::class, $logo);
        $formLogo->handleRequest($request);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($school && $formLogo->isSubmitted() && $formLogo->isValid()) {
            $file = $logo->getFile();

            $t=time();
            $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$school->getId().'_'.$t.'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('logo_directory'),
                    $fileName
                );
                $logo->setPath($fileName);
                $logo->setOriginalname($file->getClientOriginalName());
                $logo->setName($file->getClientOriginalName());

                $logos = $this->logoRepository->findBy(array('school' => $school));

                foreach ($logos as $schoolLogo) {
                    $schoolLogo->setCurrent(false);
                }

                $logo->setCurrent(true);

                $logo->setSchool($school);

                $this->em->persist($logo);
                $this->em->flush();
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $logoPath = $logo->getWebPath();
            $logo116x116 = $this->platformService->imagineFilter($logoPath, '116x116');
            $logoItemContent = $this->renderView('admin/school/include/logo_item.html.twig', array(
                'school' => $school,
                'logo' => $logo,
                'classActive' => 'active'
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'logo116x116'	 	=> $logo116x116,
                'logoItemContent' => $logoItemContent,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function deleteLogo($school_id, $logo_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);
        $logo = $this->logoRepository->find($logo_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($school && $logo){
            if($school->getId() == $logo->getSchool()->getId()){
                $logoId = $logo->getId();
                $this->em->remove($logo);
                $this->em->flush();

                $currentLogo = $this->logoRepository->findOneBy(array(
                    'school' => $school,
                    'current' => true,
                ));

                if($currentLogo){
                    $isCurrentLogo = false;
                    $logoPath = $currentLogo->getWebPath();
                }else{
                    $isCurrentLogo = true;
                    $defaultLogoPath = 'default/images/school/logo/default.jpeg';
                    $logoPath = $defaultLogoPath;
                }

                $logo116x116 = $this->platformService->imagineFilter($logoPath, '116x116');

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'logoId' => $logoId,
                    'logo116x116' => $logo116x116,
                    'isCurrentLogo' => $isCurrentLogo,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function selectLogo($school_id, $logo_id)
    {
        $school = $this->schoolRepository->find($school_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($school){
            if($logo_id == 0){
                $logos = $this->logoRepository->findBy(array(
                    'school' => $school
                ));

                foreach ($logos as $logo) {
                    $logo->setCurrent(false);
                    $this->em->persist($logo);
                }
                $this->em->flush();

                $defaultLogoPath = 'default/images/school/logo/default.jpeg';
                $logoPath = $defaultLogoPath;
            }else{
                $logo = $this->logoRepository->find($logo_id);

                if($logo && $school->getId() == $logo->getSchool()->getId()){
                    $logos = $this->logoRepository->findBy(array(
                        'school' => $school
                    ));

                    foreach ($logos as $logoTmp) {
                        $logoTmp->setCurrent(false);
                        $this->em->persist($logoTmp);
                    }

                    $logo->setCurrent(true);

                    $this->em->persist($logo);
                    $this->em->flush();
                    $logoPath = $logo->getWebPath();
                }
            }

            $logo116x116 = $this->platformService->imagineFilter($logoPath, '116x116');

            $response->setContent(json_encode(array(
                'state' => 1,
                'logo116x116' => $logo116x116,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function modifyCoverPopup($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);
        $covers = $this->coverRepository->findBy(array(
            'school' => $school
        ));
        $current = $this->coverRepository->findOneBy(array(
            'school' => $school,
            'current' => true,
        ));

        $response = new Response();

        $content = $this->renderView('admin/school/modify_cover_popup.html.twig', array(
            'school' => $school,
            'covers' => $covers,
            'current' => $current,
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function modifyCover($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);

        $cover = new Cover();
        $formCover = $this->createForm(CoverType::class, $cover);
        $formCover->handleRequest($request);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($school && $formCover->isSubmitted() && $formCover->isValid()) {
            $file = $cover->getFile();

            $t=time();
            $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$school->getId().'_'.$t.'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('cover_directory'),
                    $fileName
                );
                $cover->setPath($fileName);
                $cover->setOriginalname($file->getClientOriginalName());
                $cover->setName($file->getClientOriginalName());

                $covers = $this->coverRepository->findBy(array('school' => $school));

                foreach ($covers as $schoolCover) {
                    $schoolCover->setCurrent(false);
                }

                $cover->setCurrent(true);

                $cover->setSchool($school);

                $this->em->persist($cover);
                $this->em->flush();
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $coverPath = $cover->getWebPath();
            $cover116x116 = $this->platformService->imagineFilter($coverPath, '116x116');
            $cover300x100 = $this->platformService->imagineFilter($coverPath, '300x100');

            $coverItemContent = $this->renderView('admin/school/include/cover_item.html.twig', array(
                'school' => $school,
                'cover' => $cover,
                'classActive' => 'active'
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'cover116x116'	 	=> $cover116x116,
                'cover300x100'	 	=> $cover300x100,
                'coverItemContent' => $coverItemContent,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function selectCover($school_id, $cover_id)
    {
        $school = $this->schoolRepository->find($school_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($school){
            if($cover_id == 0){
                $covers = $this->coverRepository->findBy(array(
                    'school' => $school
                ));

                foreach ($covers as $cover) {
                    $cover->setCurrent(false);
                    $this->em->persist($cover);
                }
                $this->em->flush();

                $defaultCoverPath = 'default/images/school/cover/default.jpeg';
                $coverPath = $defaultCoverPath;
            }else{
                $cover = $this->coverRepository->find($cover_id);

                if($cover && $school->getId() == $cover->getSchool()->getId()){
                    $covers = $this->coverRepository->findBy(array(
                        'school' => $school
                    ));

                    foreach ($covers as $coverTmp) {
                        $coverTmp->setCurrent(false);
                        $this->em->persist($coverTmp);
                    }

                    $cover->setCurrent(true);

                    $this->em->persist($cover);
                    $this->em->flush();
                    $coverPath = $cover->getWebPath();
                }
            }

            $cover116x116 = $this->platformService->imagineFilter($coverPath, '116x116');
            $cover300x100 = $this->platformService->imagineFilter($coverPath, '300x100');

            $response->setContent(json_encode(array(
                'state' => 1,
                'cover116x116' => $cover116x116,
                'cover300x100' => $cover300x100,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function deleteCover($school_id, $cover_id)
    {
        $school = $this->schoolRepository->find($school_id);
        $cover = $this->coverRepository->find($cover_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($school && $cover){
            if($school->getId() == $cover->getSchool()->getId()){
                $coverId = $cover->getId();
                $this->em->remove($cover);
                $this->em->flush();

                $current = $this->coverRepository->findOneBy(array(
                    'school' => $school,
                    'current' => true,
                ));

                if($current){
                    $isCurrent = false;
                    $coverPath = $current->getWebPath();
                }else{
                    $isCurrent = true;
                    $defaultCoverPath = 'default/images/school/cover/default.jpeg';
                    $coverPath = $defaultCoverPath;
                }

                $cover116x116 = $this->platformService->imagineFilter($coverPath, '116x116');
                $cover300x100 = $this->platformService->imagineFilter($coverPath, '300x100');

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'coverId' => $coverId,
                    'cover116x116' => $cover116x116,
                    'cover300x100' => $cover300x100,
                    'isCurrent' => $isCurrent,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /*
     * category school
     */
    public function category()
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render('admin/school/category.html.twig', array('categories' => $categories));
    }

    public function addCategory(Request $request)
    {
        $category = new Category();
        $formInitCategory = $this->createForm(CategoryInitType::class, $category);
        $formInitCategory->handleRequest($request);
        if ($formInitCategory->isSubmitted() && $formInitCategory->isValid()) {
            $slug = $this->platformService->sluggify($category->getName());

            $slugtmp = $slug;
            $notSlugs = array(
                "category",
                "categories",
            );
            $isSluggable = true;
            $i = 2;
            do {
                $categorytmp = $this->categoryRepository->findOneBy(array(
                    'slug' => $slugtmp
                ));
                if($categorytmp || in_array($slugtmp, $notSlugs)){
                    $slugtmp = $slug."-".$i;
                    $i++;
                }
                else{
                    $isSluggable = false;
                }
            }
            while ($isSluggable);
            $slug = $slugtmp;
            $category->setSlug($slug);

            $this->em->persist($category);
            $this->em->flush();

            return $this->redirectToRoute("admin_school_category_edit", array('category_id' => $category->getId()));
        }

        return $this->render('admin/school/category_add.html.twig', array(
            'formInitCategory' => $formInitCategory->createView(),
        ));
    }

    public function editCategory($category_id)
    {
        $category = $this->categoryRepository->find($category_id);

        return $this->render('admin/school/category_edit.html.twig', array(
            'category' => $category,
        ));
    }

    public function doEditCategory($category_id, Request $request)
    {
        $category = $this->categoryRepository->find($category_id);

        $categoryTemp = new Category();
        $formCategoryCommon = $this->createForm(CategoryEditType::class, $categoryTemp);

        $formCategoryCommon->handleRequest($request);
        $response = new Response();

        if ($formCategoryCommon->isSubmitted() && $formCategoryCommon->isValid()) {
            $category->setName($categoryTemp->getName());
            $category->setDescription($categoryTemp->getDescription());

            $slug = $this->platformService->sluggify($categoryTemp->getSlug());

            $slugtmp = $slug;
            $notSlugs = array(
                "category",
                "categories",
            );
            $isSluggable = true;
            $i = 2;
            do {
                $categorytmp = $this->categoryRepository->findOneBy(array(
                    'slug' => $slugtmp
                ));
                if(($categorytmp && $categorytmp->getId() != $category->getId()) || in_array($slugtmp, $notSlugs)){
                    $slugtmp = $slug."-".$i;
                    $i++;
                }
                else{
                    $isSluggable = false;
                }
            }
            while ($isSluggable);
            $slug = $slugtmp;

            $category->setSlug($slug);

            $this->em->persist($category);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state'         => 1,
                'name'          => $category->getName(),
                'slug'          => $category->getSlug(),
                'description'   => $category->getDescription(),
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /*
     * School Category delete
     */
    public function deleteCategory($category_id, Request $request)
    {
        $category = $this->categoryRepository->find($category_id);

        $response = new Response();
        if ($category) {
            $categorySchools = $this->categorySchoolRepository->findBy(array(
                'category' => $category,
            ));
            foreach ($categorySchools as $categorySchool) {
                $this->em->remove($categorySchool);
            }
            $this->em->remove($category);
            $this->em->flush();

            $categories = $this->categoryRepository->findAll();

            $response->setContent(json_encode(array(
                'state' => 1,
                'id' => $category_id,
                'categories' => $categories,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function editCategorySchools($category_id)
    {
        $category = $this->categoryRepository->find($category_id);
        $categorySchools = $this->categorySchoolRepository->findBy(array(
            'category' => $category
        ));
        $defaultCategorySchool = $this->categorySchoolRepository->findOneBy(array(
            'category' => $category,
            'current' => true,
        ));

        return $this->render('admin/school/category_edit_schools.html.twig', array(
            'category' => $category,
            'categorySchools' => $categorySchools,
            'defaultCategorySchool' => $defaultCategorySchool,
        ));
    }

    public function editCategoryRemoveSchool($category_id, $school_id, Request $request)
    {
        $response = new Response();
        $category = $this->categoryRepository->find($category_id);
        $school = $this->schoolRepository->find($school_id);

        if ($school && $category) {

            $categorySchool = $this->categorySchoolRepository->findOneBy(array(
                'category' => $category,
                'school' => $school,
            ));
            if ($categorySchool) {
                $isDefaultSchool = 0;
                if($categorySchool->getCurrent()){
                    $isDefaultSchool = 1;
                }

                $this->em->remove($categorySchool);
                $this->em->flush();

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'categoryId' => $category->getId(),
                    'schoolId' => $school->getId(),
                    'schoolName' => $school->getName(),
                    'isDefaultSchool' => $isDefaultSchool,
                )));
            }else{
                $response->setContent(json_encode(array(
                    'state' => 0,
                )));
            }
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function editCategorySetDefaultSchool($category_id, $school_id)
    {
        $response = new Response();
        $category = $this->categoryRepository->find($category_id);
        if ($category) {
            if ($school_id != 0) {
                $school = $this->schoolRepository->find($school_id);
                if ($school) {
                    $categorySchools = $this->categorySchoolRepository->findBy(array(
                        'category' => $category,
                    ));

                    foreach ($categorySchools as $categorySchoolTemp) {
                        $categorySchoolTemp->setCurrent(false);
                        $this->em->persist($categorySchoolTemp);
                    }

                    $categorySchool = $this->categorySchoolRepository->findOneBy(array(
                        'category' => $category,
                        'school' => $school,
                    ));
                    if ($categorySchool) {
                        $categorySchool->setCurrent(true);
                        $this->em->persist($categorySchool);
                    }else{
                        $categorySchool = new CategorySchool;
                        $categorySchool->setSchool($school);
                        $categorySchool->setCategory($category);
                        $categorySchool->setCurrent(true);
                        $this->em->persist($categorySchool);
                    }

                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'categoryId' => $category->getId(),
                        'schoolId' => $categorySchool->getSchool()->getId(),
                        'schoolName' => $categorySchool->getSchool()->getName(),
                    )));
                }else{
                    $response->setContent(json_encode(array(
                        'state' => 0,
                    )));
                }
            }else{
                $categorySchools = $this->categorySchoolRepository->findBy(array(
                    'category' => $category,
                ));

                foreach ($categorySchools as $categorySchoolTemp) {
                    $categorySchoolTemp->setCurrent(false);
                    $this->em->persist($categorySchoolTemp);
                }

                $this->em->flush();

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'categoryId' => $category->getId(),
                    'schoolId' => 0,
                )));
            }
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /*
     * school of the day
     */
    public function sod(Request $request)
    {
        $schoolToday = $this->schoolService->getSchoolOfTheDay();

        $searchDateInterval = new SearchDateInterval();
        $form = $this->createForm(SearchDateIntervalType::class, $searchDateInterval);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $date = $this->platformService->getDate($searchDateInterval->getDatebegin());
            if($date){
                $datebeginTemp = $date;
            }else{
                $datebeginTemp = new \DateTime('now');
            }
            $result = $datebeginTemp->format('Y-m-d');
            $datebeginTemp = new \DateTime($result);

            $date = $this->platformService->getDate($searchDateInterval->getDateend());
            if($date){
                $dateendTemp = $date;
            }else{
                $dateendTemp = new \DateTime('now');
            }
            $result = $dateendTemp->format('Y-m-d');
            $dateendTemp = new \DateTime($result);

            if($datebeginTemp <= $dateendTemp){
                $datebegin = $datebeginTemp;
                $dateend = $dateendTemp;
            }else{
                $datebegin = $dateendTemp;
                $dateend = $datebeginTemp;
            }

        }else{
            $session = $request->getSession();
            $dataInterval = $session->get('dataInterval');
            if($dataInterval){
                $datebegin = $dataInterval['datebegin'];
                $dateend = $dataInterval['dateend'];
            }else{
                $datebegin = new \DateTime('now');
                $result = $datebegin->format('Y-m-d');
                $datebegin = new \DateTime($result);
                $now = new \DateTime();
                $dateend = $now->modify('+30 day');
                //$dateend = new \DateTime('now');
                $result = $dateend->format('Y-m-d');
                $dateend = new \DateTime($result);
            }
        }

        $session = $request->getSession();
        $dataInterval = array(
            'datebegin' => $datebegin,
            'dateend' => $dateend,
        );
        $session->set('dataInterval', $dataInterval);

        $show = true;
        $i = 0;
        $days = array();
        while($show){
            $datebeginTemp = clone $datebegin;
            $day = $datebeginTemp->modify('+'.$i.' day');
            $schoolOfTheDay = $this->schoolOfTheDayRepository->findOneBy(array(
                'day' => $day,
                'current' => true,
            ));
            array_push($days,array(
                'day' => $day,
                'schoolOfTheDay' => $schoolOfTheDay,
            ));
            $i++;
            if($day >= $dateend ){
                $show = false;
            }
        }

        $schools = $this->schoolRepository->findBy(array(
            'published' => true,
        ));

        return $this->render('admin/school/sod.html.twig', array(
            'datebegin' => $datebegin,
            'dateend' => $dateend,
            'days' => $days,
            'schoolToday' => $schoolToday,
            'schools' => $schools,
        ));
    }

    public function sodAssignation(Request $request)
    {
        $schoolToday = $this->schoolService->getSchoolOfTheDay();
        $sodInit = new SodInit();
        $form = $this->createForm(SodInitType::class, $sodInit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $date = $this->platformService->getDate($sodInit->getDatebegin());
            if($date){
                $datebeginTemp = $date;
            }else{
                $datebeginTemp = new \DateTime('now');
            }
            $result = $datebeginTemp->format('Y-m-d');
            $datebeginTemp = new \DateTime($result);

            $date = $this->platformService->getDate($sodInit->getDateend());
            if($date){
                $dateendTemp = $date;
            }else{
                $dateendTemp = new \DateTime('now');
            }
            $result = $dateendTemp->format('Y-m-d');
            $dateendTemp = new \DateTime($result);

            if($datebeginTemp <= $dateendTemp){
                $datebegin = $datebeginTemp;
                $dateend = $dateendTemp;
            }else{
                $datebegin = $dateendTemp;
                $dateend = $datebeginTemp;
            }

            $assign = true;
            $i = 0;
            $days = array();
            while($assign){
                $datebeginTemp = clone $datebegin;
                $day = $datebeginTemp->modify('+'.$i.' day');

                $schoolOfTheDays = $this->schoolOfTheDayRepository->findBy(array(
                    'day' => $day,
                ));

                foreach ($schoolOfTheDays as $schoolOfTheDay) {
                    echo $schoolOfTheDay->getSchool()->getName()."<br />";
                    $schoolOfTheDay->setCurrent(false);
                }

                if($sodInit->getSchoolId()){
                    $school = $this->schoolRepository->find($sodInit->getSchoolId());
                    $sod = new SchoolOfTheDay();
                    $sod->setSchool($school);
                    $sod->setDate(new \DateTime());
                    $sod->setDay($day);
                    $sod->setCurrent(true);

                    $this->em->persist($sod);
                }
                $this->em->flush();

                $i++;
                if($day >= $dateend ){
                    $assign = false;
                }
            }
            return $this->redirectToRoute('admin_school_sod');
        }

        $schools = $this->schoolRepository->findBy(array(
            'published' => true,
        ));
        return $this->render('admin/school/sod_assignation.html.twig', array(
            'schools' => $schools,
            'form' => $form->createView(),
            'schoolToday' => $schoolToday,
        ));
    }
}