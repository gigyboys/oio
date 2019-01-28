<?php
namespace App\Controller\Admin;

use App\Entity\NewsletterMail;
use App\Entity\TypeSchool;
use App\Repository\ContactRepository;
use App\Repository\NewsletterMailRepository;
use App\Repository\ParameterRepository;
use App\Repository\TypeSchoolRepository;
use App\Repository\UserRepository;
use App\Service\PlatformService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NewsletterController extends AbstractController {

    public function __construct(
        ParameterRepository $parameterRepository,
        UserRepository $userRepository,
        PlatformService $platformService,
        ContactRepository $contactRepository,
        NewsletterMailRepository $newsletterMailRepository,
        ObjectManager $em
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->platformService = $platformService;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->newsletterMailRepository = $newsletterMailRepository;
        $this->em = $em;
    }

    public function emails(): Response
    {
        $emails = $this->newsletterMailRepository->findAll();

        return $this->render('admin/platform/newsletter_emails.html.twig', array(
            'emails' => $emails,
            'view' => 'platform',
        ));
    }

}