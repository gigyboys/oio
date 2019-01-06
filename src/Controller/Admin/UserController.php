<?php
namespace App\Controller\Admin;

use App\Entity\UserTeam;
use App\Form\UserTeamEditType;
use App\Repository\ParameterRepository;
use App\Repository\UserRepository;
use App\Repository\UserTeamRepository;
use App\Service\PlatformService;
use App\Service\UserService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController {

    public function __construct(
        ParameterRepository $parameterRepository,
        UserRepository $userRepository,
        UserTeamRepository $userTeamRepository,
        PlatformService $platformService,
        UserService $userService,
        ObjectManager $em
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->platformService = $platformService;
        $this->userRepository = $userRepository;
        $this->userTeamRepository = $userTeamRepository;
        $this->userService = $userService;
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

    public function toggleAdminState($user_id, Request $request)
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

    public function team(): Response
    {
        //$users = $this->userTeamRepository->findAll();
        $users = $this->userTeamRepository->findOrderBy();
        $publishedUsers = $this->userTeamRepository->findBy(array(
            'published' => true
        ));
        $notPublishedUsers = $this->userTeamRepository->findBy(array(
            'published' => false
        ));

        return $this->render('admin/user/team.html.twig', array(
            'users' => $users,
            'publishedUsers' => $publishedUsers,
            'notPublishedUsers' => $notPublishedUsers,
            'view' => 'user',
        ));
    }

    public function togglePublicationTeam($userTeam_id, Request $request)
    {
        $userTeam = $this->userTeamRepository->find($userTeam_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));
        if ($userTeam) {
            if($userTeam->getPublished() == true){
                $userTeam->setPublished(false) ;
            }else{
                $userTeam->setPublished(true) ;
            }

            $this->em->persist($userTeam);
            $this->em->flush();

            $users = $this->userTeamRepository->findAll();
            $publishedUsers = $this->userTeamRepository->findBy(array(
                'published' => true
            ));
            $notPublishedUsers = $this->userTeamRepository->findBy(array(
                'published' => false
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $userTeam->getPublished(),
                'users' => $users,
                'publishedUsers' => $publishedUsers,
                'notPublishedUsers' => $notPublishedUsers,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function toggleShowTeam($user_id, Request $request)
    {
        $user = $this->userRepository->find($user_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));
        if ($user) {
            if($this->userService->isUserTeam($user)){
                $userTeam = $this->userTeamRepository->findOneBy(array(
                    'user' => $user,
                ));
                $userTeam->setPublished(false);
                $this->em->persist($userTeam);
            }else{
                $userTeam = $this->userTeamRepository->findOneBy(array(
                    'user' => $user,
                ));
                if($userTeam){
                    $userTeam->setPublished(true);
                    $this->em->persist($userTeam);
                }else{
                    $userTeam = new UserTeam();
                    $userTeam->setUser($user);
                    $userTeam->setPublished(true);
                    
                    $lastUserTeam = $this->userTeamRepository->findLastUserTeam('position');
                    $position = 0;
                    if($lastUserTeam){
                        $position = $lastUserTeam->getPosition() + 1;
                    }
                    $userTeam->setPosition($position);
                    
                    $this->em->persist($userTeam);
                }
            }
            $this->em->persist($userTeam);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $this->userService->isUserTeam($user),
                'position' => $userTeam->getPosition()
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function teamPosition(): Response
    {
        $users = $this->userTeamRepository->findOrderBy();

        return $this->render('admin/user/team_position.html.twig', array(
            'users' => $users,
            'view' => 'user',
        ));
    }

    public function savePositionTeam(Request $request): Response
    {
        $order = $request->query->get('order');

        $ids = explode("-", $order);
        $position = 0;
        foreach ($ids as $id){
            $userTeam = $this->userTeamRepository->find($id);
            if($userTeam){
                $position++;
                $userTeam->setPosition($position);
            }
            $this->em->flush();
        }

        $response = new Response();

        $response->setContent(json_encode(array(
            'state' => 1,
            'order' => $order,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function editUserTeam($userTeam_id)
    {
        $userTeam = $this->userTeamRepository->find($userTeam_id);

        return $this->render('admin/user/team_user_edit.html.twig', array(
            'userTeam' => $userTeam,
            'view' => 'user',
        ));
    }

    /*
     * doEdit userTeam
     */
    public function doEditUserTeam($userTeam_id, Request $request)
    {
        $userTeam = $this->userTeamRepository->find($userTeam_id);

        $userTeamTemp = new UserTeam();
        $form = $this->createForm(UserTeamEditType::class, $userTeamTemp);
        $form->handleRequest($request);
        $response = new Response();
        if ($form->isSubmitted() && $form->isValid()) {
            $userTeam->setRole($userTeamTemp->getRole());
            $userTeam->setDescription($userTeamTemp->getDescription());

            $this->em->persist($userTeam);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'role' => $userTeam->getRole(),
                'description' => $userTeam->getDescription(),
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}