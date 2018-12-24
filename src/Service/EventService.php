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
        EventIllustrationRepository $eventIllustrationRepository,
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

}