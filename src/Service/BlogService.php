<?php
/**
 * Created by PhpStorm.
 * User: Gigy
 * Date: 09/11/2018
 * Time: 17:31
 */

namespace App\Service;


use App\Entity\Category;
use App\Entity\Post;
use App\Entity\School;
use App\Entity\SchoolOfTheDay;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CommentRepository;
use App\Repository\CoverRepository;
use App\Repository\EvaluationRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\PostIllustrationRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolContactRepository;
use App\Repository\SchoolOfTheDayRepository;
use App\Repository\SchoolPostRepository;
use App\Repository\SchoolRepository;
use App\Repository\TagPostRepository;
use App\Repository\TypeSchoolRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class BlogService
{
    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        SchoolOfTheDayRepository $schoolOfTheDayRepository,
        LogoRepository $logoRepository,
        CoverRepository $coverRepository,
        EvaluationRepository $evaluationRepository,
        PostIllustrationRepository $postIllustrationRepository,
        TagPostRepository $tagPostRepository,
        SchoolPostRepository $schoolPostRepository,
        CommentRepository $commentRepository,
        PostRepository $postRepository,
        TagRepository $tagRepository,
        EntityManagerInterface $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->schoolOfTheDayRepository = $schoolOfTheDayRepository;
        $this->logoRepository = $logoRepository;
        $this->coverRepository = $coverRepository;
        $this->evaluationRepository = $evaluationRepository;
        $this->postIllustrationRepository = $postIllustrationRepository;
        $this->tagPostRepository = $tagPostRepository;
        $this->schoolPostRepository = $schoolPostRepository;
        $this->commentRepository = $commentRepository;
        $this->postRepository = $postRepository;
        $this->tagRepository = $tagRepository;
        $this->em = $em;
    }

    public function getIllustrationPath(Post $post) {
        $illustration = $this->postIllustrationRepository->findOneBy(array(
            'post' => $post,
            'current' => true,
        ));

        if($illustration){
            return 'uploads/images/post/illustration/'.$illustration->getPath();
        }
        else{
            return 'default/images/post/illustration/default.jpeg';
        }
    }

    public function isTagPost(Post $post, Tag $tag) {
        $tagPost = $this->tagPostRepository->findOneBy(array(
            'post' => $post,
            'tag' => $tag,
        ));

        if($tagPost){
            $isTag = true;
        }else{
            $isTag = false;
        }

        return $isTag;
    }

    public function isSchoolPost(Post $post, School $school) {
        $schoolPost = $this->schoolPostRepository->findOneBy(array(
            'post' => $post,
            'school' => $school,
        ));

        if($schoolPost){
            $isSchool = true;
        }else{
            $isSchool = false;
        }

        return $isSchool;
    }

    public function getValidComments(Post $post) {
        $comments = array();

        $comments = $this->commentRepository->findBy(array(
            'post' => $post,
            'deleted' => false
        ));

        return $comments;
    }

    public function getValidPosts() {

        $posts = $this->postRepository->getValidPosts();

        return $posts;
    }

    public function getNextPost(Post $post) {
        $nextPost = $this->postRepository->findNextPost($post);
        if(!$nextPost){
            $nextPost = $this->postRepository->findFirstPost('id');
        }
        return $nextPost;
    }

    public function getPreviousPost(Post $post) {
        $previousPost = $this->postRepository->findPreviousPost($post);
        if(!$previousPost){
            $previousPost = $this->postRepository->findLastPost('id');
        }
        return $previousPost;
    }

    public function getPostsLimit($limit, $order, $tag = null) {
        if(!$tag){
            $posts = $this->postRepository->getPostsLimit($limit, $order);
            return $posts;
        }else{
            $tagPosts = $this->tagPostRepository->getTagPostsLimit($limit, $order, $tag);
            $posts = array();
            foreach($tagPosts as $tagPost){
                $post = $tagPost->getPost();
                array_push($posts, $post);
            }
            return $posts;
        }
    }

    public function getSincePost($post, $tag = null) {
        if(!$tag){
            $post = $this->postRepository->getSincePost($post);
            return $post;
        }else{
            $tagPost = $this->tagPostRepository->getSinceTagPost($post, $tag);
            if($tagPost){
                return $tagPost->getPost();
            }else{
                return null;
            }
        }
    }

    public function getPostsSince($post, $limit, $order, $tag = null) {
        if(!$tag){
            $posts = $this->postRepository->getPostsSince($post, $limit, $order);
            return $posts;
        }else{
            $tagPosts = $this->tagPostRepository->getTagPostsSince($post, $limit, $order, $tag);
            $posts = array();
            foreach($tagPosts as $tagPost){
                $post = $tagPost->getPost();
                array_push($posts, $post);
            }
            return $posts;
        }
    }

    public function getTagsWithPublishedPost() {
        $tags = array();
        
        $tagTemps = $this->tagRepository->findAllOrderByName('ASC');
        foreach($tagTemps as $tagTemp){
            if($this->isTagWithPublishedPost($tagTemp)){
                array_push($tags, $tagTemp);
            }
        }
        
        return $tags;
    }

    public function isTagWithPublishedPost($tag) {
        $tagPosts = $this->tagPostRepository->getTagPostsWithPublishedPost($tag);
        if($tagPosts){
            return true;
        }     
        return false;
    }

    public function getPublishedPostsByTag($tag) {
        $tagPosts = $this->tagPostRepository->getTagPostsWithPublishedPost($tag);
        $posts = array();
        foreach($tagPosts as $tagPost){
            $post = $tagPost->getPost();
            array_push($posts, $post);
        }
        return $posts;
    }
}