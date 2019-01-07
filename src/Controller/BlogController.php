<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Evaluation;
use App\Form\CommentType;
use App\Form\EvaluationType;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CommentRepository;
use App\Repository\FieldRepository;
use App\Repository\PostRepository;
use App\Repository\TagPostRepository;
use App\Repository\TagRepository;
use App\Service\BlogService;
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

class BlogController extends AbstractController {

    public function __construct(
        PostRepository $postRepository,
        TagRepository $tagRepository,
        TagPostRepository $tagPostRepository,
        PlatformService $platformService,
        ParameterRepository $parameterRepository,
        CommentRepository $commentRepository,
        BlogService $blogService,
        ObjectManager $em
    )
    {
        $this->postRepository = $postRepository;
        $this->tagRepository = $tagRepository;
        $this->tagPostRepository = $tagPostRepository;
        $this->platformService = $platformService;
        $this->parameterRepository = $parameterRepository;
        $this->commentRepository = $commentRepository;
        $this->blogService = $blogService;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index()
    {
        $limit = $this->parameterRepository->findOneBy(array(
            'parameter' => 'posts_by_page',
        ))->getValue();

        $order = "ASC";
        $posts = $this->postRepository->getPostsLimit($limit, $order);

        $previousPost = null;
        if(count($posts)){
            $index = count($posts)-1;
            $firstPost = $posts[$index];
            $previousPost = $this->postRepository->getSincePost($firstPost);
        }

        return $this->render('blog/index.html.twig', array(
            'posts' => $posts,
            'previousPost' => $previousPost,
            'entityView' => 'blog',
        ));
    }

    public function viewById($id, Request $request)
    {
        $post = $this->postRepository->find($id);
        return $this->redirectToRoute('blog_post_view', array('slug' => $post->getSlug()));
    }

    public function view($slug, Request $request): Response
    {

        $user = $this->getUser();

        $post = $this->postRepository->findOneBy(array(
            'slug' => $slug,
        ));

        $showPost = false;
        if($post && !$post->getDeleted() && $post->getPublished() && $post->getValid()){
            $showPost = true;
        }
        if($post && ( $showPost || $this->isGranted('ROLE_ADMIN') || $post->getUser() == $user)){

            //tags
            $tags = array();

            $tagPosts = $this->tagPostRepository->findBy(array(
                'post' => $post,
            ));

            foreach($tagPosts as $tagPost){
                $tag = $tagPost->getTag();
                array_push($tags, $tag);
            }

            //view
            $this->platformService->registerView($post, $user, $request);

            $allComments = $this->blogService->getValidComments($post);

            $limit = 10;
            $type = "post";
            $order = "DESC";
            $comments = $this->commentRepository->getCommentsLimit($type, $post, $limit, $order);

            $previousComment = null;
            if(count($comments)>0){
                $firstComment = $comments[0];
                $previousComment = $this->commentRepository->getSinceComment($firstComment, $type, $post);
            }

            //nextPost
            $nextPost = $this->blogService->getNextPost($post);

            //previousPost
            $previousPost = $this->blogService->getPreviousPost($post);

            return $this->render('blog/view_post.html.twig', [
                'post'              => $post,
                'tags' 			    => $tags,
                'comments'          => $comments,
                'allComments'       => $allComments,
                'previousComment'   => $previousComment,
                'nextPost'          => $nextPost,
                'previousPost'      => $previousPost,
                'entityView'        => 'blog',
            ]);
        }else{
            return $this->redirectToRoute('blog');
        }
    }

    public function loadPosts($post_id, Request $request)
    {
        $lastPost = $this->postRepository->find($post_id);

        $response = new Response();
        if($lastPost){
            $limit = $this->parameterRepository->findOneBy(array(
                'parameter' => 'posts_by_page',
            ))->getValue();

            $order = "ASC";
            $posts = $this->postRepository->getPostsSince($lastPost, $limit, $order);

            $listPosts = array();
            foreach($posts as $post){
                $postItem = $this->renderView('blog/include/post_item.html.twig', array(
                    'post' => $post
                ));
                array_push($listPosts, array(
                    "id" 			=> $post->getId(),
                    "postItem" 		=> $postItem,
                ));
            }

            $previousPost = null;
            $previousPostId = 0;
            $urlLoadPost = null;

            if(count($posts)){
                $index = count($posts)-1;
                $firstPost = $posts[$index];
                $previousPost = $this->postRepository->getSincePost($firstPost);
            }
            if ($previousPost){
                $previousPostId = $previousPost->getId();
                $urlLoadPost = $this->generateUrl('blog_load_posts', array(
                    'post_id' => $previousPostId,
                ));
            }

            $response->setContent(json_encode(array(
                'state' => 1,
                'posts' => $listPosts,
                'previousPostId' => $previousPostId,
                'urlLoadPost' => $urlLoadPost,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function loadComments($post_id, $comment_id, Request $request)
    {
        $post = $this->postRepository->find($post_id);
        $lastComment = $this->commentRepository->find($comment_id);

        $response = new Response();
        if($post && $lastComment){
            $limit = 10;
            $type = "post";
            $order = "DESC";
            $comments = $this->commentRepository->getCommentsSince($lastComment, $type, $post, $limit, $order);

            $listComments = array();
            foreach($comments as $comment){
                $commentItem = $this->renderView('blog/include/comment_item.html.twig', array(
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
                $previousComment = $this->commentRepository->getSinceComment($firstComment, $type, $post, $order);
            }
            if ($previousComment){
                $previousCommentId = $previousComment->getId();
                $urlLoadComment = $this->generateUrl('blog_post_load_comment', array(
                    'post_id' => $post->getId(),
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

    public function newComment($post_id, Request $request)
    {
        $comment = new Comment();
        $post = $this->postRepository->find($post_id);
        $user = $this->getUser();
        $form = $this->createForm(CommentType::class, $comment);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($user){
            if($post && $user && $post->getActiveComment()){
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    //creation message
                    $comment->setPost($post);
                    $comment->setUser($user);
                    $comment->setDate(new \DateTime());

                    $this->em->persist($comment);

                    $this->em->flush();

                    $comments = $this->blogService->getValidComments($post);

                    $commentItem = $this->renderView('blog/include/comment_item.html.twig', array(
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

}