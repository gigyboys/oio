<?php
namespace App\Twig;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Post;
use App\Entity\School;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\BlogService;
use App\Service\EventService;
use App\Service\PlatformService;
use App\Service\SchoolService;
use App\Service\UserService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        SchoolService $schoolService,
        UserService $userService,
        PlatformService $platformService,
        EventService $eventService,
        BlogService $blogService
    )
    {
        $this->schoolService = $schoolService;
        $this->userService = $userService;
        $this->platformService = $platformService;
        $this->eventService = $eventService;
        $this->blogService = $blogService;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('area', array($this, 'calculateArea')),
            new TwigFunction('substrSpace', array($this, 'substrSpace')),
            new TwigFunction('getCategoriesWithPublishedSchool', array($this, 'getCategoriesWithPublishedSchool')),
            new TwigFunction('getAllSchoolByCategory', array($this, 'getAllSchoolByCategory')),
            new TwigFunction('getCurrentSchoolByCategory', array($this, 'getCurrentSchoolByCategory')),
            new TwigFunction('getLastAddedSchools', array($this, 'getLastAddedSchools')),
            new TwigFunction('getSchoolOfTheDay', array($this, 'getSchoolOfTheDay')),
            new TwigFunction('lastEvaluatedSchool', array($this, 'lastEvaluatedSchool')),
            new TwigFunction('mostVisitedSchool', array($this, 'mostVisitedSchool')),
            new TwigFunction('getCategoriesBySchool', array($this, 'getCategoriesBySchool')),
            new TwigFunction('schoolLogo', array($this, 'schoolLogo')),
            new TwigFunction('schoolCover', array($this, 'schoolCover')),
            new TwigFunction('isCategorySchool', array($this, 'isCategorySchool')),
            new TwigFunction('getRelatedSchools', array($this, 'getRelatedSchools')),
            new TwigFunction('getType', array($this, 'getType')),
            new TwigFunction('userAvatar', array($this, 'userAvatar')),
            new TwigFunction('getLinkUserInfo', array($this, 'getLinkUserInfo')),
            new TwigFunction('isSubscribed', array($this, 'isSubscribed')),
            /*blog*/
            new TwigFunction('getValidPosts', array($this, 'getValidPosts')),
            new TwigFunction('postIllustration', array($this, 'postIllustration')),
            new TwigFunction('isTagPost', array($this, 'isTagPost')),
            new TwigFunction('isSchoolPost', array($this, 'isSchoolPost')),
            new TwigFunction('getValidComments', array($this, 'getValidComments')),
            new TwigFunction('getTagsWithPublishedPost', array($this, 'getTagsWithPublishedPost')),
            new TwigFunction('getPublishedPostsByTag', array($this, 'getPublishedPostsByTag')),
            /*platform*/
            new TwigFunction('fileIcon', array($this, 'fileIcon')),
            new TwigFunction('isAdmin', array($this, 'isAdmin')),
            new TwigFunction('getActiveUsers', array($this, 'getActiveUsers')),
            new TwigFunction('getLastVisit', array($this, 'getLastVisit')),
            new TwigFunction('isUserTeam', array($this, 'isUserTeam')),
            new TwigFunction('findUserTeamsOrderBy', array($this, 'findUserTeamsOrderBy')),
            new TwigFunction('getDateLabel', array($this, 'getDateLabel')),
            new TwigFunction('getAcronym', array($this, 'getAcronym')),
            /*event*/
            new TwigFunction('isTagEvent', array($this, 'isTagEvent')),
            new TwigFunction('eventIllustration', array($this, 'eventIllustration')),
            new TwigFunction('isSchoolEvent', array($this, 'isSchoolEvent')),
            new TwigFunction('getGoingParticipations', array($this, 'getGoingParticipations')),
            new TwigFunction('getMaybeParticipations', array($this, 'getMaybeParticipations')),
            new TwigFunction('isGoingParticipation', array($this, 'isGoingParticipation')),
            new TwigFunction('isMaybeParticipation', array($this, 'isMaybeParticipation')),
            new TwigFunction('getValidEvents', array($this, 'getValidEvents')),
            new TwigFunction('isCurrent', array($this, 'isCurrent')),
            new TwigFunction('isUpcomming', array($this, 'isUpcomming')),
            new TwigFunction('isPassed', array($this, 'isPassed')),
            new TwigFunction('getTagsWithPublishedEvent', array($this, 'getTagsWithPublishedEvent')),
            new TwigFunction('getPublishedEventsByTag', array($this, 'getPublishedEventsByTag')),
        );
    }

    public function calculateArea(int $width, int $length)
    {
        return $width * $length;
    }

    public function substrSpace($string, $length) {
        return $this->platformService->substrSpace($string, $length);
    }

    public function getCategoriesWithPublishedSchool($limit)
    {
        return $this->schoolService->getCategoriesWithPublishedSchool($limit);
    }

    public function getAllSchoolByCategory(Category $category, $publishState = null) {
        return $this->schoolService->getAllSchoolByCategory($category, $publishState);
    }

    public function getCurrentSchoolByCategory(Category $category){
        return $this->schoolService->getCurrentSchoolByCategory($category);
    }

    public function getLastAddedSchools($limit) {
        return $this->schoolService->getLastAddedSchools($limit);
    }

    public function getSchoolOfTheDay() {
        return $this->schoolService->getSchoolOfTheDay();
    }

    public function lastEvaluatedSchool() {
        return $this->schoolService->lastEvaluatedSchool();
    }

    public function mostVisitedSchool() {
        return $this->schoolService->mostVisitedSchool();
    }

    public function getCategoriesBySchool(School $school, $limit, $shuffle) {
        return $this->schoolService->getCategoriesBySchool($school, $limit, $shuffle);
    }

    public function schoolLogo(School $school) {
        return $this->schoolService->getLogoPath($school);
    }

    public function schoolCover(School $school) {
        return $this->schoolService->getCoverPath($school);
    }

    public function isCategorySchool(School $school, Category $category) {
        return $this->schoolService->isCategorySchool($school, $category);
    }

    public function getRelatedSchools(School $school, $limit) {
        return $this->schoolService->getRelatedSchools($school, $limit);
    }


    public function getType(School $school) {
        return $this->schoolService->getType($school);
    }

    public function userAvatar(User $user) {
        return $this->userService->getAvatarPath($user);
    }

    public function getLinkUserInfo(User $user, $label) {
        return $this->userService->getLinkUserInfo($user, $label);
    }

    public function isSubscribed(School $school, User $user) {
        return $this->schoolService->isSubscribed($school, $user);
    }

    /*
     * blog
     */
    public function postIllustration(Post $post) {
        return $this->blogService->getIllustrationPath($post);
    }

    public function isTagPost(Post $post, Tag $tag) {
        return $this->blogService->isTagPost($post, $tag);
    }

    public function getValidPosts() {
        return $this->blogService->getValidPosts();
    }

    public function isSchoolPost(Post $post, School $school) {
        return $this->blogService->isSchoolPost($post, $school);
    }

    public function getValidComments($entity) {
        return $this->platformService->getValidComments($entity);
    }

    public function getTagsWithPublishedPost() {
        return $this->blogService->getTagsWithPublishedPost();
    }

    public function getPublishedPostsByTag(Tag $tag) {
        return $this->blogService->getPublishedPostsByTag($tag);
    }

    /*
     * platform
     */

    public function fileIcon($filename) {
        return $this->platformService->fileIcon($filename);
    }

    public function getDateLabel($date, $short = false) {
        return $this->platformService->getDateLabel($date, $short);
    }

    public function getAcronym($string) {
        return $this->platformService->getAcronym($string);
    }

    public function isAdmin(User $user) {
        return $this->userService->isAdmin($user);
    }

    public function isUserTeam(User $user, $published = true) {
        return $this->userService->isUserTeam($user, $published);
    }

    public function findUserTeamsOrderBy($field, $order , $published) {
        return $this->userService->findUserTeamsOrderBy($field, $order , $published);
    }

    public function getActiveUsers() {
        return $this->userService->getActiveUsers();
    }

    public function getLastVisit(User $user) {
        return $this->userService->getLastVisit($user);
    }

    /*
     * event
     */
    public function isTagEvent(Event $event, Tag $tag) {
        return $this->eventService->isTagEvent($event, $tag);
    }

    public function eventIllustration(Event $event) {
        return $this->eventService->getIllustrationPath($event);
    }

    public function isSchoolEvent(Event $event, School $school) {
        return $this->eventService->isSchoolEvent($event, $school);
    }

    public function getGoingParticipations(Event $event) {
        return $this->eventService->getGoingParticipations($event);
    }

    public function getMaybeParticipations(Event $event) {
        return $this->eventService->getMaybeParticipations($event);
    }

    public function isGoingParticipation(Event $event, User $user) {
        return $this->eventService->isGoingParticipation($event, $user);
    }

    public function isMaybeParticipation(Event $event, User $user) {
        return $this->eventService->isMaybeParticipation($event, $user);
    }

    public function getValidEvents() {
        return $this->eventService->getValidEvents();
    }

    public function isCurrent(Event $event) {
        return $this->eventService->isCurrent($event);
    }

    public function isUpcomming(Event $event) {
        return $this->eventService->isUpcomming($event);
    }

    public function isPassed(Event $event) {
        return $this->eventService->isPassed($event);
    }

    public function getTagsWithPublishedEvent() {
        return $this->eventService->getTagsWithPublishedEvent();
    }

    public function getPublishedEventsByTag(Tag $tag) {
        return $this->eventService->getPublishedEventsByTag($tag);
    }
}