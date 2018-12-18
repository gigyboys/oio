<?php
namespace App\Controller\Admin;

use App\Entity\TypeSchool;
use App\Repository\ParameterRepository;
use App\Repository\TypeSchoolRepository;
use App\Repository\UserRepository;
use App\Service\PlatformService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController {

    public function __construct(
        ParameterRepository $parameterRepository,
        UserRepository $userRepository,
        PlatformService $platformService,
        ObjectManager $em
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->platformService = $platformService;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    public function index(): Response
    {
        $users = $this->userRepository->findAll();
        $activeUsers = $this->userRepository->findBy(array(
            'enabled' => true
        ));
        $inactiveUsers = $this->userRepository->findBy(array(
            'enabled' => false
        ));

        return $this->render('admin/user/user.html.twig', array(
            'users' => $users,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
            'view' => 'user',
        ));
    }

    public function admin(): Response
    {
        $users = $this->userRepository->getAdmins();

        return $this->render('admin/user/user_admin.html.twig', array(
            'users' => $users,
            'view' => 'user',
        ));
    }

    public function editUser($user_id)
    {
        $user = $this->userRepository->find($user_id);

        return $this->render('admin/user/user_edit.html.twig', array(
            'user' => $user,
            'view' => 'user',
        ));
    }

    public function toogleAdminState($user_id, Request $request)
    {
        $user = $this->userRepository->find($user_id);
        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($user && !$user->isSuperAdmin() && $this->isGranted('ROLE_SUPER_ADMIN')) {
            if($user->isAdmin()){
                $roles = array("ROLE_USER");
            }else{
                $roles = array("ROLE_ADMIN");
            }

            $user->setRoles($roles);
            $this->em->persist($user);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $user->isAdmin(),
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}