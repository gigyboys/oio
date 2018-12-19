<?php
/**
 * Created by PhpStorm.
 * User: Gigy
 * Date: 09/11/2018
 * Time: 17:31
 */

namespace App\Service;


use App\Entity\Category;
use App\Entity\School;
use App\Entity\SchoolOfTheDay;
use App\Entity\User;
use App\Repository\AvatarRepository;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\SchoolContactRepository;
use App\Repository\SchoolOfTheDayRepository;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use App\Repository\UserTeamRepository;
use App\Repository\VisitRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Templating\EngineInterface;

class UserService
{
    /**
     * @var EngineInterface
     */
    private $templating;

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        AvatarRepository $avatarRepository,
        VisitRepository $visitRepository,
        UserRepository $userRepository,
        UserTeamRepository $userTeamRepository,
        EntityManagerInterface $em,
        EngineInterface $templating
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->avatarRepository = $avatarRepository;
        $this->visitRepository = $visitRepository;
        $this->userRepository = $userRepository;
        $this->userTeamRepository = $userTeamRepository;
        $this->em = $em;
        $this->templating = $templating;
    }



    public function getAvatarPath(User $user) {
        $avatar = $this->avatarRepository->findOneBy(array(
            'user' => $user,
            'current' => true,
        ));

        if($avatar){
            return 'uploads/images/user/avatar/'.$avatar->getPath();
        }
        else{
            return 'default/images/user/avatar/default.jpeg';
        }
    }

    public function getLinkUserInfo(User $user, $label) {
        $content = $this->templating->render('user/link-user-info.html.twig', array(
            'user' => $user,
            'label' => $label,
        ));
        return $content;
    }

    public function checkHasDoublon($username, $userid) {
        $user = $this->userRepository->findOneBy(array(
            'username' => $username,
        ));
        if($user && $user->getId() != $userid){
            return true;
        }else{
            return false;
        }
    }

    public function checkHasEmailDoublon($email, $userid) {
        $user = $this->userRepository->findOneBy(array(
            'email' => $email,
        ));
        if($user && $user->getId() != $userid){
            return true;
        }else{
            return false;
        }
    }

    public function isAdmin(User $user) {
        return $user->isAdmin();
    }

    public function getLastVisit(User $user) {
        $visit = $this->visitRepository->getLastVisit($user);
        return $visit;
    }

    public function getActiveUsers() {
        $users = $this->userRepository->findBy(array(
            'enabled' => true,
        ));
        return $users;
    }

    public function isUserTeam(User $user, $published = true) {
        $userTeam = $this->userTeamRepository->findOneBy(array(
            'user' => $user,
        ));
        if(!$userTeam){
            return false;
        }elseif(!$userTeam->getPublished() && $published){
            return false;
        }
        return true;
    }

}