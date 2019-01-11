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

class BlogController extends AbstractController {

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


    public function post()
    {
        $posts = $this->postRepository->getPosts();
        $publishedPosts = $this->postRepository->findBy(array(
            'published' => true,
            'tovalid'   => true,
            'deleted'   => false,
        ));
        $notPublishedPosts = $this->postRepository->findBy(array(
            'published' => false,
            'tovalid'   => true,
            'deleted'   => false,
        ));

        return $this->render('admin/blog/posts.html.twig', array(
            'posts' => $posts,
            'publishedPosts' => $publishedPosts,
            'notPublishedPosts' => $notPublishedPosts,
            'view' => 'blog',
        ));
    }

    public function postCreation()
    {
        $posts = $this->postRepository->findBy(array(
            'tovalid' => false
        ));

        return $this->render('admin/blog/posts_creation.html.twig', array(
            'posts' => $posts,
            'view' => 'blog',
        ));
    }

    public function addPost(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostInitType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setIntroduction('Introduction '.$post->getTitle());
            $post->setContent('Contenu '.$post->getTitle());

            $slug = $this->platformService->getSlug($post->getTitle(), $post);
            $post->setSlug($slug);
            $post->setDate(new \DateTime());
            $post->setPublished(false);
            $post->setValid(false);
            $post->setDeleted(false);
            $user = $this->getUser();
            $post->setUser($user);
            $post->setShowAuthor(true);
            $post->setActiveComment(true);
            $post->setTovalid(true);

            $this->em->persist($post);

            $this->em->flush();

            return $this->redirectToRoute('admin_blog_post_edit', array(
                'post_id' => $post->getId()
            ));
        }

        return $this->render('admin/blog/post_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editPost($post_id)
    {
        $post = $this->postRepository->find($post_id);

        return $this->render('admin/blog/post_edit.html.twig', array(
            'post' => $post,
            'view' => 'blog',
        ));
    }

    public function tags()
    {
        $tags = $this->tagRepository->findAll();
        return $this->render('admin/blog/tags.html.twig', array(
            'tags' => $tags,
            'view' => 'blog',
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

            return $this->redirectToRoute("admin_blog_tag_edit", array('tag_id' => $tag->getId()));
        }

        return $this->render('admin/blog/tag_add.html.twig', array(
            'form' => $form->createView(),
            'view' => 'blog',
        ));
    }

    public function editTag($tag_id)
    {
        $tag = $this->tagRepository->find($tag_id);

        return $this->render('admin/blog/tag_edit.html.twig', array(
            'tag' => $tag,
            'view' => 'blog',
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

    public function postTags($post_id)
    {
        $post = $this->postRepository->find($post_id);
        $tags = $this->tagRepository->findAll();

        return $this->render('admin/blog/post_tags.html.twig', array(
            'post' => $post,
            'tags' => $tags
        ));
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
        $post = $this->postRepository->find($post_id);
        $schools = $this->schoolRepository->findAll();

        return $this->render('admin/blog/post_schools.html.twig', array(
            'post' => $post,
            'schools' => $schools
        ));
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

    /*
     * confirm delete post
     */
    public function deletePost($post_id, Request $request)
    {
        $post = $this->postRepository->find($post_id);

        $response = new Response();
        if ($post) {
            $post->setDeleted(true);
            $this->em->persist($post);
            $this->em->flush();

            $posts = $this->postRepository->getPosts();
            $publishedPosts = $this->postRepository->findBy(array(
                'published' => true,
                'tovalid'   => true,
                'deleted'   => false,
            ));
            $notPublishedPosts = $this->postRepository->findBy(array(
                'published' => false,
                'tovalid'   => true,
                'deleted'   => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'id' => $post_id,
                'posts' => $posts,
                'publishedPosts' => $publishedPosts,
                'notPublishedPosts' => $notPublishedPosts,
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
    * Post comment
    */
    public function postComments($post_id)
    {
        $post = $this->postRepository->find($post_id);
        $comments = $this->commentRepository->findBy(array(
            'post' => $post,
            'deleted' => false,
        ));
        return $this->render('admin/blog/post_comments.html.twig', array(
            'post' => $post,
            'comments' => $comments
        ));
    }


    /*
     * Post Comment delete
     */
    public function deleteComment($post_id, $id, Request $request)
    {
        $comment = $this->commentRepository->find($id);
        $post = $this->postRepository->find($post_id);

        $response = new Response();
        if ($comment) {
            $comment->setDeleted(true);
            $this->em->persist($comment);
            $this->em->flush();

            $comments = $this->commentRepository->findBy(array(
                'post' => $post,
                'deleted' => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'id' => $id,
                'comments' => $comments,
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