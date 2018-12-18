<?php
namespace App\Controller\Admin;

use App\Repository\ParameterRepository;
use App\Repository\UserRepository;
use App\Service\SchoolService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;

class DashboardController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        SchoolService $schoolService,
        UserRepository $userRepository
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolService = $schoolService;
        $this->userRepository = $userRepository;
    }

    public function index(): Response
    {
        $schools = $this->schoolRepository->findAll();
        $publishedSchools = $this->schoolRepository->findBy(array(
            'published' => true,
        ));
        $notPublishedSchools = $this->schoolRepository->findBy(array(
            'published' => false,
        ));
        $users = $this->userRepository->findAll();

        return $this->render('admin/index.html.twig', array(
            'schools' => $schools,
            'publishedSchools' => $publishedSchools,
            'notPublishedSchools' => $notPublishedSchools,
            'users' => $users,
            'view' => 'dashboard',
        ));
    }
}