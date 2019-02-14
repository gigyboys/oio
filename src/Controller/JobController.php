<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\JobRepository;
use App\Service\PlatformService;
use App\Service\JobService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class JobController extends AbstractController{

    public function __construct(
        JobRepository $jobRepository,
        JobService $jobService,
        PlatformService $platformService,
        ObjectManager $em
    )
    {
        $this->jobRepository = $jobRepository;
        $this->jobService = $jobService;
        $this->platformService = $platformService;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index($page, Request $request): Response
    {
        $response = $this->render('job/index.html.twig', [
            'entityView' => 'job',
        ]);

        return $response;
    }
}