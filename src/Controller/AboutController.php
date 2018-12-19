<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ParameterRepository;
use App\Repository\UserTeamRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;
use Twig\Environment;

class AboutController extends AbstractController {

    public function __construct(
        ParameterRepository $parameterRepository,
        PlatformService $platformService,
        UserTeamRepository $userTeamRepository,
        ObjectManager $em,
        \Swift_Mailer $mailer
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->platformService = $platformService;
        $this->userTeamRepository = $userTeamRepository;
        $this->mailer = $mailer;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index()
    {
        return $this->render('platform/about.html.twig');
    }

    public function team()
    {
        $users = $this->userTeamRepository->findBy(array(
            'published' => true,
        ));
        return $this->render('platform/team.html.twig', array(
            'users' => $users
        ));
    }

    public function notice()
    {
        return $this->render('platform/notice.html.twig');
    }
}