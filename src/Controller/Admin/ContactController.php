<?php
namespace App\Controller\Admin;

use App\Entity\TypeSchool;
use App\Repository\ContactRepository;
use App\Repository\ParameterRepository;
use App\Repository\TypeSchoolRepository;
use App\Repository\UserRepository;
use App\Service\PlatformService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends AbstractController {

    public function __construct(
        ParameterRepository $parameterRepository,
        UserRepository $userRepository,
        PlatformService $platformService,
        ContactRepository $contactRepository,
        ObjectManager $em
    )
    {
        $this->parameterRepository = $parameterRepository;
        $this->platformService = $platformService;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->em = $em;
    }

    public function contacts(): Response
    {
        $contacts = $this->contactRepository->findAll();

        return $this->render('admin/platform/contacts.html.twig', array(
            'contacts' => $contacts,
            'view' => 'platform',
        ));
    }

    public function viewContact($contact_id): Response
    {
        $contact = $this->contactRepository->find($contact_id);

        if($contact){
            $contact->setStatus(true);
            $this->em->persist($contact);
            $this->em->flush();
            return $this->render('admin/platform/contact_view.html.twig', array(
                'contact' => $contact,
                'view' => 'platform',
            ));
        }else{
            return $this->redirectToRoute('admin');
        }
    }

}