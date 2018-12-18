<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Entity\NewsletterMail;
use App\Form\ContactType;
use App\Form\NewsletterMailType;
use App\Repository\NewsletterMailRepository;
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

class NewsletterController extends AbstractController {

    public function __construct(
        ParameterRepository $parameterRepository,
        NewsletterMailRepository $newsletterMailRepository,
        PlatformService $platformService,
        ObjectManager $em,
        \Swift_Mailer $mailer
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->newsletterMailRepository = $newsletterMailRepository;
        $this->platformService = $platformService;
        $this->mailer = $mailer;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index(Request $request): Response
    {
        $newsletterMail = new NewsletterMail();
        $msg = "";
        $form = $this->createForm(NewsletterMailType::class, $newsletterMail);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if (filter_var($newsletterMail->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $newsletterMailTemp = $this->newsletterMailRepository->findOneBy(array(
                    'email' => $newsletterMail->getEmail(),
                ));

                if (!$newsletterMailTemp){
                    $newsletterMail->setDate(new \DateTime());

                    $this->em->persist($newsletterMail);
                    $this->em->flush();
                }else{
                    $active = $newsletterMail->getActive();
                    if($active == null){
                        $active = true;
                    }
                    $newsletterMailTemp->setActive($active);

                    $this->em->persist($newsletterMailTemp);
                    $this->em->flush();
                }
                if($newsletterMail->getActive()){
                    $msg = "Vous êtes abonné à la newsletter";
                }else{
                    $msg = "Vous n'êtes pas abonné à la newsletter";
                }
            } else {
                $msg = "<span class='error'>Veuillez fournir votre adresse email valide</span>";
            }

        }

        return $this->render('platform/newsletter.html.twig', array(
            'form' => $form->createView(),
            'msg' => $msg,
        ));
    }
}