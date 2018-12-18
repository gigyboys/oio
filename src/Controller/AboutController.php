<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ParameterRepository;
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
        ObjectManager $em,
        \Swift_Mailer $mailer
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->platformService = $platformService;
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
        return $this->render('platform/team.html.twig');
    }

    public function notice()
    {
        return $this->render('platform/notice.html.twig');
    }
}