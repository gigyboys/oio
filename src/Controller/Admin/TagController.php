<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Logo;
use App\Entity\Post;
use App\Entity\SchoolPost;
use App\Entity\Tag;
use App\Entity\TagPost;
use App\Entity\TypeSchool;
use App\Form\CategoryEditType;
use App\Form\CategoryInitType;
use App\Form\CoverType;
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
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolPostRepository;
use App\Repository\TagPostRepository;
use App\Repository\CommentRepository;
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

class TagController extends AbstractController {

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
        CommentRepository $commentRepository,
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
        $this->commentRepository = $commentRepository;
        $this->em = $em;
    }


    public function tags()
    {
        $tags = $this->tagRepository->findAll();
        return $this->render('admin/platform/tags.html.twig', array(
            'tags' => $tags,
            'view' => 'platform',
        ));
    }

    public function addTag(Request $request)
    {
        $tag = new Tag();
        $form = $this->createForm(TagInitType::class, $tag);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->platformService->sluggify($tag->getName());

            $slugtmp = $slug;
            $notSlugs = array(
                "category",
                "categories",
            );
            $isSluggable = true;
            $i = 2;
            do {
                $tagTemp = $this->tagRepository->findOneBy(array(
                    'slug' => $slugtmp
                ));
                if($tagTemp || in_array($slugtmp, $notSlugs)){
                    $slugtmp = $slug."-".$i;
                    $i++;
                }
                else{
                    $isSluggable = false;
                }
            }
            while ($isSluggable);
            $slug = $slugtmp;
            $tag->setSlug($slug);

            $this->em->persist($tag);
            $this->em->flush();

            return $this->redirectToRoute("admin_platform_tag_edit", array('tag_id' => $tag->getId()));
        }

        return $this->render('admin/platform/tag_add.html.twig', array(
            'form' => $form->createView(),
            'view' => 'platform',
        ));
    }

    public function editTag($tag_id)
    {
        $tag = $this->tagRepository->find($tag_id);

        return $this->render('admin/platform/tag_edit.html.twig', array(
            'tag' => $tag,
            'view' => 'platform',
        ));
    }

    public function doEditTag($tag_id, Request $request)
    {
        $tag = $this->tagRepository->find($tag_id);

        $tagTemp = new Tag();
        $form = $this->createForm(TagEditType::class, $tagTemp);

        $form->handleRequest($request);
        $response = new Response();

        if ($form->isSubmitted() && $form->isValid()) {
            $tag->setName($tagTemp->getName());

            $slug = $this->platformService->sluggify($tagTemp->getSlug());

            $slugtmp = $slug;
            $notSlugs = array(
                "category",
                "categories",
            );
            $isSluggable = true;
            $i = 2;
            do {
                $tagTemp = $this->tagRepository->findOneBy(array(
                    'slug' => $slugtmp
                ));
                if(($tagTemp && $tagTemp->getId() != $tag->getId()) || in_array($slugtmp, $notSlugs)){
                    $slugtmp = $slug."-".$i;
                    $i++;
                }
                else{
                    $isSluggable = false;
                }
            }
            while ($isSluggable);
            $slug = $slugtmp;

            $tag->setSlug($slug);

            $this->em->persist($tag);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state'         => 1,
                'name'          => $tag->getName(),
                'slug'          => $tag->getSlug(),
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