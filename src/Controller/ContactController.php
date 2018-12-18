<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Model\MailMessage;
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

class ContactController extends AbstractController {

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

    public function index(Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $msg = "";
        $error = false;
        $user = $this->getUser();
        if($user){
            $contact->setUser($user);
            $contact->setName($user->getName());
            $contact->setEmail($user->getEmail());
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(trim($contact->getName()) == ""){
                $error = true;
                $msg .= "<div>Le champ <strong>nom</strong> est obligatoire.</div>";
            }

            if(trim($contact->getEmail()) == ""){
                $error = true;
                $msg .= "<div>Le champ <strong>email</strong> est obligatoire.</div>";
            }

            if(trim($contact->getPhone()) == ""){
                $error = true;
                $msg .= "<div>Le champ <strong>téléphone</strong> est obligatoire.</div>";
            }

            if(trim($contact->getMessage()) == ""){
                $error = true;
                $msg .= "<div>Le champ message est obligatoire.</div>";
            }

            if(!$error){
                $contact->setDate(new \DateTime());
                $contact->setStatus(false);
                if($user){
                    $contact->setUser($user);
                }
                $this->em->persist($contact);
                $this->em->flush();

                //envoi mail
                $content = $this->renderView("platform/notification/contact.html.twig", array(
                    'user' => $user,
                    'contact' => $contact,
                ));

                $message = new MailMessage();
                $message->setSubject("www.oio.com : Contact venant de ".$contact->getName());
                $message->setBody($content);
                $message->setFrom("noreplay@boot.com");
                $message->setTo("contact@boot.com");
                $message->setWrap("notification");

                $this->platformService->email($message);

                $msg = "<div class='success_msg'>Contact bien envoyé</div>";
                $contact = new Contact();
            }else{
                $msg = "<div class='error_msg'>".$msg."</div>";
            }
        }

        return $this->render('platform/contact.html.twig', [
            'formContact' => $form->createView(),
            'contact' => $contact,
            'msg' => $msg,
        ]);
    }
}