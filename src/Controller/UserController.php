<?php
namespace App\Controller;

use App\Entity\Avatar;
use App\Entity\User;
use App\Form\AvatarType;
use App\Form\UserBiographyType;
use App\Form\UserEditType;
use App\Form\UserPasswordType;
use App\Form\UserSetNewPasswordType;
use App\Form\UserType;
use App\Model\MailMessage;
use App\Model\UserPassword;
use App\Repository\AvatarRepository;
use App\Repository\PostRepository;
use App\Repository\EventRepository;
use App\Service\BlogService;
use App\Service\PlatformService;
use App\Service\SchoolService;
use App\Service\UserService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SchoolRepository;
use App\Repository\ParameterRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\ParameterBagUtils;

class UserController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        UserRepository $userRepository,
        AvatarRepository $avatarRepository,
        PostRepository $postRepository,
        EventRepository $eventRepository,
        PlatformService $platformService,
        UserService $userService,
        BlogService $blogService,
        SchoolService $schoolService,
        ObjectManager $em,
        \Swift_Mailer $mailer
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->userRepository = $userRepository;
        $this->avatarRepository = $avatarRepository;
        $this->postRepository = $postRepository;
        $this->eventRepository = $eventRepository;
        $this->platformService = $platformService;
        $this->userService = $userService;
        $this->blogService = $blogService;
        $this->schoolService = $schoolService;
        $this->em = $em;
        $this->mailer = $mailer;

        $this->platformService->registerVisit();
    }

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('platform_home');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $errorEmail = "";
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $existUser = $this->userRepository->findOneBy(array(
                'email' => $user->getEmail()
            ));
            if(!$existUser){
                $password = $passwordEncoder->encodePassword($user, $user->getPassword());
                $user->setPassword($password);
                $date = new \DateTime();
                $user->setDate($date);
                $user->setLastActivity($date);

                //username
                $slug = $this->platformService->sluggify($user->getName());

                $slugtmp = $slug;
                $notSlugs = array(
                    "school",
                    "blog",
                    "advert",
                    "forum",
                    "about",
                    "team",
                    "legal-notice",
                    "contact",
                    "newsletter",
                    "categories",
                    "category",
                    "user",
                    "admin",
                    "logout",
                    "login",
                    "register",
                );
                $isSluggable = true;
                $i = 2;
                do {
                    $usertmp = $this->userRepository->findOneBy(array(
                        'username' => $slugtmp
                    ));
                    if($usertmp || in_array($slugtmp, $notSlugs)){
                        $slugtmp = $slug."-".$i;
                        $i++;
                    }
                    else{
                        $isSluggable = false;
                    }
                } while ($isSluggable);
                $slug = $slugtmp;

                $user->setUsername($slug);
                $user->setToken("");
                $user->setEnabled(false);

                $this->em->persist($user);
                $this->em->flush();
                $token = md5(time()).$user->getId();
                $user->setToken($token);
                $this->em->flush();


                //sendig mail : for account activation

                $urlActivation = $this->generateUrl(
                    'user_register_activation',
                    array('token' => $user->getToken()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $content = $this->renderView("user/notification/user_activation.html.twig", array(
                    'user' => $user,
                    'urlActivation' => $urlActivation,
                ));

                $message = new MailMessage();
                $message->setSubject("www.oio.com : Lien d'activation du compte utilisateur");
                $message->setBody($content);
                $message->setFrom("noreplay@boot.com");
                $message->setTo($user->getEmail());
                $message->setWrap("notification");

                $this->platformService->email($message);

                $session = $request->getSession();
                $data = array(
                    'state' => 'activation',
                    'userid' => $user->getId(),
                );

                $session->set('data', $data);

                return $this->redirectToRoute("user_register");
            }else{
                $errorEmail = "<span style='color: #86251c;'>Cette adresse email est déjà utilisée.</span>";
            }
        }

        $session = $request->getSession();
        $dataSession = $session->get('data');
        if($dataSession){
            $user = $this->userRepository->find($dataSession['userid']);
            $session->remove('data');
            return $this->render('user/register.html.twig',array(
                'form' => $form->createView(),
                'user' => $user,
                'errorEmail' => $errorEmail,
                'mailActivation' => true,
            ));
        }else{
            return $this->render('user/register.html.twig',array(
                'form' => $form->createView(),
                'user' => $user,
                'errorEmail' => $errorEmail,
            ));
        }
    }

    public function registerActivation($token, Request $request): Response
    {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('platform_home');
        }

        $user = $this->userRepository->findOneBy(array(
            'token' => $token,
        ));

        if($user){
            $user->setEnabled(true);
            $date = new \DateTime();
            $user->setLastActivity($date);
            $this->em->flush();
            return $this->render('user/login.html.twig',array(
                'user' => $user,
                'last_username' => $user->getEmail(),
                'error' => '',
                'msgActivation' => "L'activation de votre compte est faite avec succès. Vous pouvez maintenant vous identifier."
            ));
        }else{
            $user = new User();
            $form = $this->createForm(UserType::class, $user);

            return $this->render('user/register.html.twig',array(
                'form' => $form->createView(),
                'user' => $user,
                'errorActivation' => '<span style="color:#8b392c">Vous tentez d\'activer un compte avec des mauvaises données.</span>'
            ));
        }
    }

    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('platform_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    public function loginAjaxCheck(Request $request)
    {
        return new JsonResponse(['state' => 1, 'error' => ""]);
    }

    public function loginAjax(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /*
             * @todo
             */
        }

        $response = new Response();

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $content = $this->renderView('user/login-ajax.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function profileById($user_id, $type, Request $request): Response
    {
        $user = $this->userRepository->find($user_id);
        if($user) {
            return $this->redirectToRoute('user_profile', array(
                'username' => $user->getUsername(),
                'type' => $type,
            ));
        }else{
            return $this->redirectToRoute('platform_home');
        }
    }

    public function profile($username, $type, Request $request): Response
    {

        $user = $this->userRepository->findOneBy(array(
            'username' => $username,
        ));

        if($user) {
            $connectedUser = $this->getUser();

            //posts
            if($connectedUser && $connectedUser->getId() == $user->getId()){
                $posts = $this->postRepository->findBy(array(
                    'user' => $user,
                    'deleted' => false,
                ));
            }else{
                $posts = $this->postRepository->findBy(array(
                    'user' => $user,
                    'published' => true,
                    'valid' => true,
                    'deleted' => false,
                    'showAuthor' => true,
                ));
            }

            //events
            if($connectedUser && $connectedUser->getId() == $user->getId()){
                $events = $this->eventRepository->findBy(array(
                    'user' => $user,
                    'deleted' => false,
                ));
            }else{
                $events = $this->eventRepository->findBy(array(
                    'user' => $user,
                    'published' => true,
                    'valid' => true,
                    'deleted' => false,
                    'showAuthor' => true,
                ));
            }

            //comments
            $comments = $this->platformService->getValidCommentsByUser($user);

            //evaluations
            $evaluations = $this->schoolService->getValidEvaluationsByUser($user);

            return $this->render('user/profile.html.twig', array(
                'user'      => $user,
                'type'      => $type,
                'posts'     => $posts,
                'events'    => $events,
                'comments'  => $comments,
                'evaluations'  => $evaluations,
            ));
        }else{
            return $this->redirectToRoute('platform_home');
        }
    }

    public function infoPopup($id, Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $user = $this->userRepository->find($id);

            $response = new Response();
            $content = $this->renderView('user/info-popup.html.twig', array(
                'user' => $user
            ));
            $response->setContent(json_encode(array(
                'state' => 1,
                'content' => $content,
            )));

            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }else{
            throw new NotFoundHttpException('Page not found');
        }
    }

    public function modifyAvatarPopup()
    {
        $user = $this->getUser();
        $avatars = $this->avatarRepository->findBy(array('user' => $user));
        $currentAvatar = $this->avatarRepository->findOneBy(array(
            'user' => $user,
            'current' => true,
        ));

        $response = new Response();

        $content = $this->renderView('user/modify_avatar_popup.html.twig', array(
            'avatars' => $avatars,
            'currentAvatar' => $currentAvatar,
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function modifyAvatar(Request $request)
    {
        $avatar = new Avatar();
        $user = $this->getUser();

        $formAvatar = $this->createForm(AvatarType::class, $avatar);
        $formAvatar->handleRequest($request);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($user && $formAvatar->isSubmitted() && $formAvatar->isValid()) {
            $file = $avatar->getFile();

            $t=time();
            $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$user->getId().'_'.$t.'.'.$file->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $file->move(
                    $this->getParameter('avatar_directory'),
                    $fileName
                );
                $avatar->setPath($fileName);
                $avatar->setOriginalname($file->getClientOriginalName());
                $avatar->setName($file->getClientOriginalName());

                $avatars = $this->avatarRepository->findBy(array('user' => $user));

                foreach ($avatars as $userAvatar) {
                    $userAvatar->setCurrent(false);
                }

                $avatar->setCurrent(true);

                $avatar->setUser($user);

                $this->em->persist($avatar);
                $this->em->flush();
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $avatarPath = $avatar->getWebPath();

            $avatar32x32 = $this->platformService->imagineFilter($avatarPath, '32x32');
            $avatar50x50 = $this->platformService->imagineFilter($avatarPath, '50x50');
            $avatar116x116 = $this->platformService->imagineFilter($avatarPath, '116x116');

            $avatarItemContent = $this->renderView('user/include/avatar_item.html.twig', array(
                'avatar' => $avatar,
                'classActive' => 'active'
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'avatar32x32' 		=> $avatar32x32,
                'avatar116x116'	 	=> $avatar116x116,
                'avatar50x50' 		=> $avatar50x50,
                'avatarItemContent' => $avatarItemContent,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function deleteAvatar($id)
    {
        $user = $this->getUser();
        $avatar = $this->avatarRepository->find($id);

        $response = new Response();

        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($user && $avatar){
            if($user->getId() == $avatar->getUser()->getId()){
                $avatarId = $avatar->getId();
                $this->em->remove($avatar);
                $this->em->flush();

                $currentAvatar = $this->avatarRepository->findOneBy(array(
                    'user' => $user,
                    'current' => true,
                ));

                if($currentAvatar){
                    $avatarPath = $avatar->getWebPath();
                    $isCurrentAvatar = false;
                }else{
                    $avatarPath = 'default/images/user/avatar/default.jpeg';
                    $isCurrentAvatar = true;
                }

                $avatar32x32 = $this->platformService->imagineFilter($avatarPath, '32x32');
                $avatar50x50 = $this->platformService->imagineFilter($avatarPath, '50x50');
                $avatar116x116 = $this->platformService->imagineFilter($avatarPath, '116x116');

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'avatarId' => $avatarId,
                    'avatar32x32' => $avatar32x32,
                    'avatar116x116' => $avatar116x116,
                    'avatar50x50' => $avatar50x50,
                    'isCurrentAvatar' => $isCurrentAvatar,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function selectAvatar($id)
    {
        $user = $this->getUser();

        $response = new Response();

        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($user){
            $avatarPath = 'default/images/user/avatar/default.jpeg';
            if($id == 0){
                $avatars = $this->avatarRepository->findBy(array('user' => $user));

                foreach ($avatars as $userAvatar) {
                    $userAvatar->setCurrent(false);
                    $this->em->persist($userAvatar);
                }
                $this->em->flush();
            }else{
                $avatar = $this->avatarRepository->find($id);

                if($avatar && $user->getId() == $avatar->getUser()->getId()){
                    $avatars = $this->avatarRepository->findBy(array('user' => $user));

                    foreach ($avatars as $userAvatar) {
                        $userAvatar->setCurrent(false);
                        $this->em->persist($userAvatar);
                    }

                    $avatar->setCurrent(true);

                    $this->em->persist($avatar);
                    $this->em->flush();

                    $avatarPath = $avatar->getWebPath();
                }
            }

            $avatar32x32 = $this->platformService->imagineFilter($avatarPath, '32x32');
            $avatar50x50 = $this->platformService->imagineFilter($avatarPath, '50x50');
            $avatar116x116 = $this->platformService->imagineFilter($avatarPath, '116x116');

            $response->setContent(json_encode(array(
                'state' => 1,
                'avatar32x32' => $avatar32x32,
                'avatar116x116' => $avatar116x116,
                'avatar50x50' => $avatar50x50,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function editUserBiography(Request $request)
    {
        if ($request->isXmlHttpRequest()){

            $response = new Response();
            $response->setContent(json_encode(array(
                'state' => 0,
            )));

            $user = $this->getUser();
            if($user){
                $userTemp = new User();
                $formUserBiography = $this->createForm(UserBiographyType::class, $userTemp);
                $formUserBiography->handleRequest($request);
                $response = new Response();

                if ($formUserBiography->isSubmitted() && $formUserBiography->isValid()) {
                    $user->setBiography($userTemp->getBiography());
                    $this->em->persist($user);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'biography' => $user->getBiography(),
                    )));
                }
            }

            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }else{
            throw new NotFoundHttpException('Page not found');
        }
    }

    public function editUser(Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $user = $this->getUser();
            $response = new Response();
            if($user){
                $userTemp = new User();
                $formUserCommon = $this->createForm(UserEditType::class, $userTemp);
                $formUserCommon->handleRequest($request);
                if ($formUserCommon->isSubmitted() && $formUserCommon->isValid()) {
                    $error = false;
                    $errorUsername = "";
                    $errorEmail = "";
                    $errorName = "";
                    $listErrors = array();

                    //username
                    $username = $this->platformService->sluggify($userTemp->getUsername());

                    if($username == ""){
                        $error = true;
                        $errorUsername = "Vous devez fournir un nom d'utilisateur";
                    }

                    if($errorUsername == ""){
                        $hasDoublon = $this->userService->checkHasDoublon($username, $user->getId());
                        if($hasDoublon){
                            $error = true;
                            $errorUsername = "Saisissez un autre nom d'utilisateur";
                        }
                    }

                    //email
                    $email = trim($userTemp->getEmail());
                    if($email == ""){
                        $error = true;
                        $errorEmail = "Vous devez fournir une adresse email";
                    }

                    if($errorEmail == ""){
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $error = true;
                            $errorEmail = "Vous devez fournir une adresse mail valide";
                        }
                    }

                    if($errorEmail == ""){
                        $hasEmailDoublon = $this->userService->checkHasEmailDoublon($email, $user->getId());
                        if($hasEmailDoublon){
                            $error = true;
                            $errorEmail = "Choisissez une autre adresse email";
                        }
                    }

                    //name
                    $name = trim($userTemp->getName());
                    if($name == ""){
                        $error = true;
                        $errorName = "Vous devez fournir un nom";
                    }

                    //location
                    $location = trim($userTemp->getLocation());

                    if($error){
                        $listErrors["errorUsername"]= $errorUsername;
                        $listErrors["errorEmail"]= $errorEmail;
                        $listErrors["errorName"]= $errorName;
                        $response->setContent(json_encode(array(
                            'state' => 2,
                            'error' => $listErrors,
                        )));
                        return $response;
                    }

                    $oldEmail = $user->getEmail();

                    $user->setName($name);
                    $user->setUsername($username);
                    $user->setLocation($location);
                    $user->setEmail($email);

                    $this->em->persist($user);
                    $this->em->flush();

                    if($oldEmail != $user->getEmail()){
                        //sending mail
                        $content = "<div>Bonjour ".$user->getName().",</div>";
                        $content .= "<div>Vous avez fait une modification sur votre profil. A partir de maintenant votre nouveau mail est <strong>".$user->getEmail()."</strong>.</div>";

                        $message = new MailMessage();
                        $message->setSubject("www.oio.com : Modification profil ");
                        $message->setBody($content);
                        $message->setFrom("noreplay@boot.com");
                        $message->setTo($user->getEmail());
                        $message->setWrap("notification");

                        $this->platformService->email($message);
                    }

                    /*
                    $oldToken = $this->container->get('security.context')->getToken();
                    $token = new UsernamePasswordToken(
                        $user,
                        null,
                        $oldToken->getProviderKey(),
                        $user->getRoles()
                    );
                    $this->container->get('security.context')->setToken($token);
                    */

                    $location = $user->getLocation();
                    if($location == null || $location == ""){
                        $location = "";
                    }
                    $title = 'Profil '.$user->getUsername();

                    $url = $this->get('router')->generate('user_profile', array('username' => $user->getUsername()));
                    $urlSetting = $this->get('router')->generate('user_profile', array(
                        'username' => $user->getUsername(),
                        'type' => 'setting',
                    ));

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'name' => $user->getName(),
                        'username' => $user->getUsername(),
                        'location' => $location,
                        'email' => $user->getEmail(),
                        'title' => $title,
                        'url' => $url,
                        'urlSetting' => $urlSetting,
                    )));
                }else{
                    $response->setContent(json_encode(array(
                        'state' => 0,
                        'message' => 'serveur message : une erreur est survenue',
                    )));
                }
            }else{
                $urlLogin = $this->get('router')->generate('com_user_login');
                $response->setContent(json_encode(array(
                    'state' => 3,
                    'urlLogin' => $urlLogin,
                )));
            }
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }else{
            throw new NotFoundHttpException('Page not found');
        }
    }

    public function editUserPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isXmlHttpRequest()){
            $user = $this->getUser();
            if($user){
                $userPassword = new UserPassword();
                $formUserPassword = $this->createForm(UserPasswordType::class, $userPassword);
                $formUserPassword->handleRequest($request);
                $response = new Response();
                if ($formUserPassword->isSubmitted() && $formUserPassword->isValid()) {
                    $match = $passwordEncoder->isPasswordValid($user, $userPassword->getCurrentPassword());
                    if($match && $userPassword->getNewPassword() != "" && $userPassword->getNewPassword() == $userPassword->getRepeatPassword()) {
                        $newPasswordEncoded = $passwordEncoder->encodePassword($user, $userPassword->getNewPassword());
                        $user->setPassword($newPasswordEncoded);
                        $this->em->persist($user);
                        $this->em->flush();

                        $response->setContent(json_encode(array(
                            'state' => 1,
                            /*
                            'currentPassword' => $userPassword->getCurrentPassword(),
                            'newPassword' => $userPassword->getNewPassword(),
                            'repeatPassword' => $userPassword->getRepeatPassword(),
                            'newPasswordEncoded' => $newPasswordEncoded,
                            */
                        )));
                    } else {
                        $response->setContent(json_encode(array(
                            'state' => 0,
                            'message' => 'Veuillez bien verifier votre saisie...',
                            'oldplainpassword' => $userPassword->getCurrentPassword(),
                            'newplainpassword' => $userPassword->getNewPassword(),
                            'repeatplainpassword' => $userPassword->getRepeatPassword(),
                        )));
                    }
                }else{
                    $response->setContent(json_encode(array(
                        'state' => 0,
                        'message' => 'serveur message : une erreur est survenue 2',
                    )));
                }
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }else{

            }
        }else{
            throw new NotFoundHttpException('Page not found');
        }
    }

    public function setNewPassword(Request $request)
    {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('platform_home');
        }

        $userTemp = new User();
        $form = $this->createForm(UserSetNewPasswordType::class, $userTemp);
        $error = "";
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(array(
                "email" => $userTemp->getEmail(),
            ));
            if($user){
                $token = md5(time()).$user->getId();
                $user->setToken($token);
                $this->em->persist($user);
                $this->em->flush();

                $urlToken = $this->generateUrl(
                    'user_set_new_password_token',
                    array('user_id' => $user->getId(), 'token' => $user->getToken()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                //sending mail
                $content = $this->renderView("user/notification/new_password_token.html.twig", array(
                    'user' => $user,
                    'urlToken' => $urlToken,
                ));

                $message = new MailMessage();
                $message->setSubject("www.oio.com : Récupération de mot de passe ");
                $message->setBody($content);
                $message->setFrom("noreplay@boot.com");
                $message->setTo($user->getEmail());
                $message->setWrap("notification");

                $this->platformService->email($message);


                return $this->render('user/set_new_password_step2.html.twig', array(
                    "user" => $user,
                    "urlToken" => $urlToken,
                ));
            }else{
                $error = "Cette adresse n'est pas lié à un utilisateur";
            }
        }

        return $this->render('user/set_new_password.html.twig', array(
            'formSetNewPassword' => $form->createView(),
            'error' => $error,
        ));
    }

    public function sendNewPassword($user_id, $token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('platform_home');
        }

        $user = $this->userRepository->findOneBy(array(
            "id" => $user_id,
            "token" => $token,
        ));

        $error = "";
        if ($user) {
            $passwordLenght = rand(8,12);
            //$plainpassword = "0000";
            $plainpassword = $this->platformService->generateRandomString($passwordLenght);
            $encodedPassword = $passwordEncoder->encodePassword($user, $plainpassword);
            $user->setPassword($encodedPassword);
            $user->setEnabled(true);
            $user->setLastActivity(new \DateTime());
            $this->em->persist($user);
            $this->em->flush();

            $urlLogin = $this->generateUrl(
                'user_login',
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $content = $this->renderView("user/notification/new_password.html.twig", array(
                'user' => $user,
                'plainpassword' => $plainpassword,
                'urlLogin' => $urlLogin,
            ));

            $message = new MailMessage();
            $message->setSubject("www.oio.com : Récupération de mot de passe");
            $message->setBody($content);
            $message->setFrom("noreplay@boot.com");
            $message->setTo($user->getEmail());
            $message->setWrap("notification");

            $this->platformService->email($message);

            return $this->render('user/set_new_password_step3.html.twig', array(
                "user" => $user,
            ));
        }else{
            $error = "<span style='color: #86251c;'>Une erreur est survenue. Veuillez renvoyer votre email.</span>";
        }

        return $this->render('user/set_new_password.html.twig', array(
            'error' => $error,
        ));
    }
}