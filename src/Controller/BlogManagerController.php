<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Post;
use App\Entity\PostIllustration;
use App\Entity\SchoolPost;
use App\Entity\TagPost;
use App\Form\EvaluationType;
use App\Form\PostContentType;
use App\Form\PostIllustrationType;
use App\Form\PostInitType;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
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

class BlogManagerController extends AbstractController {

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
        $this->em = $em;

        $this->platformService->registerVisit();
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
                $post->setPublished(true);
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


    public function toggleShowAuthor($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($post && $this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
            if($post->getShowAuthor() == true){
                $post->setShowAuthor(false) ;
            }else{
                $post->setShowAuthor(true) ;
            }

            $this->em->persist($post);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state'=> 1,
                'case' => $post->getShowAuthor(),
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function toggleActiveComment($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($post && $this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
            if($post->getActiveComment() == true){
                $post->setActiveComment(false) ;
            }else{
                $post->setActiveComment(true) ;
            }

            $this->em->persist($post);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case'  => $post->getActiveComment(),
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function togglePublication($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($post) {
            if ($this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
                if($post->getPublished() == true){
                    $post->setPublished(false) ;
                }else{
                    $post->setPublished(true) ;
                }

                $this->em->persist($post);
                $this->em->flush();

                if ($this->isGranted('ROLE_ADMIN')){
                    $posts = $this->postRepository->getPosts();
                    $publishedPosts = $this->postRepository->findBy(array(
                        'published' => true,
                        'tovalid'   => true,
                    ));
                    $notPublishedPosts = $this->postRepository->findBy(array(
                        'published' => false,
                        'tovalid'   => true,
                    ));

                    $response->setContent(json_encode(array(
                        'state'             => 1,
                        'case'              => $post->getPublished(),
                        'posts'             => $posts,
                        'publishedPosts'    => $publishedPosts,
                        'notPublishedPosts' => $notPublishedPosts,
                    )));
                }else{
                    $response->setContent(json_encode(array(
                        'state'             => 1,
                        'case'              => $post->getPublished(),
                    )));
                }
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function toggleValidation($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($post) {
            if ($this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
                if($post->getValid() == true){
                    $post->setValid(false) ;
                }else{
                    $post->setValid(true) ;
                }

                $this->em->persist($post);
                $this->em->flush();

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'case'  => $post->getValid(),
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function toggleDeletion($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($post) {
            if ($this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
                if($post->getDeleted() == true){
                    $post->setDeleted(false) ;
                }else{
                    $post->setDeleted(true) ;
                }

                $this->em->persist($post);
                $this->em->flush();

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'case' => $post->getDeleted(),
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditPost($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($post){
            if ($this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
                $postTemp = new Post();
                $form = $this->createForm(PostType::class, $postTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $post->setTitle($postTemp->getTitle());
                    if(trim($postTemp->getSlug()) == ""){
                        $postTemp->setSlug($postTemp->getTitle());
                    }

                    $slug = $this->platformService->getSlug($postTemp->getSlug(), $post);
                    $post->setSlug($slug);

                    $this->em->persist($post);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state'     => 1,
                        'postId'    => $post->getId(),
                        'title'     => $post->getTitle(),
                        'slug'      => $post->getSlug(),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditContentPost($post_id, Request $request)
    {
        $user = $this->getUser();
        $post = $this->postRepository->find($post_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($post){
            if ($this->isGranted('ROLE_ADMIN') || $post->getUser() == $user){
                $postTemp = new Post();
                $form = $this->createForm(PostContentType::class, $postTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $post->setIntroduction($postTemp->getIntroduction());
                    $post->setContent($postTemp->getContent());

                    $this->em->persist($post);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'introduction'  => $post->getIntroduction(),
                        'content'       => $post->getContent(),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

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
                'post'          => $post,
                'illustrations' => $illustrations,
                'current'       => $current,
            ));

            $response->setContent(json_encode(array(
                'state'     => 1,
                'content'   => $content,
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
                    'state'                     => 1,
                    'illustration116x116'	 	=> $illustration116x116,
                    'illustration600x250'	 	=> $illustration600x250,
                    'illustrationItemContent'   => $illustrationItemContent,
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
                'state'                 => 1,
                'illustration116x116'   => $illustration116x116,
                'illustration600x250'   => $illustration600x250,
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
                    'state'                 => 1,
                    'illustrationId'        => $illustrationId,
                    'illustration116x116'   => $illustration116x116,
                    'illustration600x250'   => $illustration600x250,
                    'isCurrent' => $isCurrent,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
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
                'post'          => $post,
                'tags'          => $tags,
                'entityView'    => 'blog',
            ));
        }
        return $this->redirectToRoute('blog');
    }


    public function toggleTag($post_id, $tag_id, Request $request)
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

        if($post && $user && $post->getUser()->getId() == $user->getId() ){
            $schools = $this->schoolService->findSchoolsSubscription($user);
            return $this->render('blog/post_schools.html.twig', array(
                'post' => $post,
                'schools' => $schools,
                'entityView' => 'blog',
            ));
        }
        return $this->redirectToRoute('blog');
    }

    public function toggleSchool($post_id, $school_id, Request $request)
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
                'case'  => $isSchool,
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