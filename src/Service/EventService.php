<?php
/**
 * Created by PhpStorm.
 * User: Gigy
 * Date: 09/11/2018
 * Time: 17:31
 */

namespace App\Service;


use App\Entity\Category;
use App\Entity\Event;
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
use App\Repository\EventIllustrationRepository;
use App\Repository\EventRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\PostIllustrationRepository;
use App\Repository\PostRepository;
use App\Repository\SchoolContactRepository;
use App\Repository\SchoolOfTheDayRepository;
use App\Repository\SchoolPostRepository;
use App\Repository\SchoolEventRepository;
use App\Repository\SchoolRepository;
use App\Repository\TagPostRepository;
use App\Repository\TypeSchoolRepository;
use App\Repository\ParticipationRepository;
use App\Repository\TagEventRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class EventService
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
        SchoolEventRepository $schoolEventRepository,
        CommentRepository $commentRepository,
        PostRepository $postRepository,
        EventRepository $eventRepository,
        ParticipationRepository $participationRepository,
        EventIllustrationRepository $eventIllustrationRepository,
        TagEventRepository $tagEventRepository,
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
        $this->schoolEventRepository = $schoolEventRepository;
        $this->commentRepository = $commentRepository;
        $this->postRepository = $postRepository;
        $this->eventRepository = $eventRepository;
        $this->eventIllustrationRepository = $eventIllustrationRepository;
        $this->participationRepository = $participationRepository;
        $this->tagEventRepository = $tagEventRepository;
        $this->tagRepository = $tagRepository;
        $this->em = $em;
    }

    public function getIllustrationPath(Event $event) {
        $illustration = $this->eventIllustrationRepository->findOneBy(array(
            'event' => $event,
            'current' => true,
        ));

        if($illustration){
            return 'uploads/images/event/illustration/'.$illustration->getPath();
        }
        else{
            return 'default/images/event/illustration/default.jpeg';
        }
    }

    public function isSchoolEvent(Event $event, School $school) {
        $schoolEvent = $this->schoolEventRepository->findOneBy(array(
            'event' => $event,
            'school' => $school,
        ));

        if($schoolEvent){
            $isSchool = true;
        }else{
            $isSchool = false;
        }

        return $isSchool;
    }

    public function getGoingParticipations(Event $event) {
        $participations = $this->participationRepository->findBy(array(
            'event' => $event,
            'status' => 1,
        ));

        return $participations;
    }

    public function getMaybeParticipations(Event $event) {
        $participations = $this->participationRepository->findBy(array(
            'event' => $event,
            'status' => 2,
        ));

        return $participations;
    }

    public function isGoingParticipation(Event $event, User $user) {
        $participation = $this->participationRepository->findOneBy(array(
            'event'  => $event,
            'user'   => $user,
            'status' => 1,
        ));

        if($participation){
            return true;
        }
        return false;
    }

    public function isMaybeParticipation(Event $event, User $user) {
        $participation = $this->participationRepository->findOneBy(array(
            'event'  => $event,
            'user'   => $user,
            'status' => 2,
        ));

        if($participation){
            return true;
        }
        return false;
    }

    public function getValidEvents() {

        $events = $this->eventRepository->getValidEvents();

        return $events;
    }

    public function getValidComments(Event $event) {
        $comments = array();

        $comments = $this->commentRepository->findBy(array(
            'event' => $event,
            'deleted' => false
        ));

        return $comments;
    }

    public function getValidCommentsByUser(User $user) {
        $comments = array();

        $commentTemps = $this->commentRepository->getValidCommentsByUser($user);

        $comments = array();
        foreach($commentTemps as $comment){
            if($comment->getPost()){
                $post = $comment->getPost();
                if($post->getPublished() && $post->getTovalid() && $post->getValid() && !$post->getDeleted()){
                    array_push($comments, $comment);
                }
            }elseif($comment->getEvent()){
                $event = $comment->getEvent();
                if($event->getPublished() && $event->getTovalid() && $event->getValid() && !$event->getDeleted()){
                    array_push($comments, $comment);
                }
            }
        }

        return $comments;
    }

    public function getNextEvent(Event $event) {//By 'datebegin'
        $nextEvent = $this->eventRepository->findNextEvent($event);
        if(!$nextEvent){
            $nextEvent = $this->eventRepository->findFirstEvent(); 
        }
        return $nextEvent;
    }

    public function getPreviousEvent(Event $event) {//By 'datebegin'
        $previousEvent = $this->eventRepository->findPreviousEvent($event);
        if(!$previousEvent){
            $previousEvent = $this->eventRepository->findLastEvent();
        }
        return $previousEvent;
    }

    public function isCurrent(Event $event) {
        $now = new \Datetime();
        
        if($now >= $event->getDatebegin() && $now <= $event->getDateend() ){
            return true;
        }
        return false;
    }

    public function isUpcomming(Event $event) {
        $now = new \Datetime();
        
        if($now < $event->getDatebegin() ){
            return true;
        }
        return false;
    }

    public function isPassed(Event $event) {
        $now = new \Datetime();
        
        if($now > $event->getDateend() ){
            return true;
        }
        return false;
    }

    public function isTagEvent(Event $event, Tag $tag) {
        $tagEvent = $this->tagEventRepository->findOneBy(array(
            'event' => $event,
            'tag' => $tag,
        ));

        if($tagEvent){
            $isTag = true;
        }else{
            $isTag = false;
        }

        return $isTag;
    }

    public function getTagsWithPublishedEvent() {
        $tags = array();
        
        $tagTemps = $this->tagRepository->findAllOrderByName('ASC');
        foreach($tagTemps as $tagTemp){
            if($this->isTagWithPublishedEvent($tagTemp)){
                array_push($tags, $tagTemp);
            }
        }
        
        return $tags;
    }

    public function isTagWithPublishedEvent($tag) {
        $tagEvents = $this->tagEventRepository->getTagEventsWithPublishedEvent($tag);
        if($tagEvents){
            return true;
        }     
        return false;
    }

    public function getEventsByTagOffsetLimit($tag, $offset, $limit) {
        $tagEvents = $this->tagEventRepository->getTagEventsByTagOffsetLimit($tag, $offset, $limit);
        
        $events = array();
        foreach($tagEvents as $tagEvent){
            $event = $tagEvent->getEvent();
            array_push($events, $event);
        }
        
        return $events;
    }

    public function getEventsByTag($tag) {
        $tagEvents = $this->tagEventRepository->getTagEventsByTag($tag);
        
        $events = array();
        foreach($tagEvents as $tagEvent){
            $event = $tagEvent->getEvent();
            array_push($events, $event);
        }
        return $events;
    }

    public function getPublishedEventsByTag($tag) {
        $tagEvents = $this->tagEventRepository->getTagEventsWithPublishedEvent($tag);
        $events = array();
        foreach($tagEvents as $tagEvent){
            $event = $tagEvent->getEvent();
            array_push($events, $event);
        }
        return $events;
    }

}