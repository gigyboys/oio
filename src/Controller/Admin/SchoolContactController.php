<?php
namespace App\Controller\Admin;

use App\Entity\SchoolContact;
use App\Form\SchoolContactEditType;
use App\Form\SchoolContactInitType;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\SchoolContactRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\TypeRepository;
use App\Repository\TypeSchoolRepository;
use App\Repository\UserRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SchoolRepository;

class SchoolContactController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        ParameterRepository $parameterRepository,
        SchoolService $schoolService,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        PlatformService $platformService,
        UserRepository $userRepository,
        TypeRepository $typeRepository,
        LogoRepository $logoRepository,
        CoverRepository $coverRepository,
        SchoolContactRepository $schoolContactRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolService = $schoolService;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->platformService = $platformService;
        $this->userRepository = $userRepository;
        $this->typeRepository = $typeRepository;
        $this->logoRepository = $logoRepository;
        $this->coverRepository = $coverRepository;
        $this->schoolContactRepository = $schoolContactRepository;

        $this->em = $em;
    }

    public function index($school_id)
    {
        $school = $this->schoolRepository->find($school_id);
        $contacts = $this->schoolContactRepository->findBy(array(
            'school' => $school
        ));
        $publishedContacts = $this->schoolContactRepository->findBy(array(
            'school' => $school,
            'published' => true,
        ));
        $notPublishedContacts = $this->schoolContactRepository->findBy(array(
            'school' => $school,
            'published' => false,
        ));

        return $this->render('admin/school/contact.html.twig', array(
            'school' => $school,
            'contacts' => $contacts,
            'publishedContacts' => $publishedContacts,
            'notPublishedContacts' => $notPublishedContacts,
        ));
    }

    public function togglePublicationContact($school_id, $contact_id, Request $request)
    {
        $contact = $this->schoolContactRepository->find($contact_id);

        $response = new Response();

        if ($contact) {
            if($contact->getPublished() == true){
                $contact->setPublished(false) ;
            }else{
                $contact->setPublished(true) ;
            }

            $this->em->persist($contact);
            $this->em->flush();

            $school = $this->schoolRepository->find($school_id);
            $contacts = $this->schoolContactRepository->findBy(array(
                'school' => $school
            ));
            $publishedContacts = $this->schoolContactRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));
            $notPublishedContacts = $this->schoolContactRepository->findBy(array(
                'school' => $school,
                'published' => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $contact->getPublished(),
                'contacts' => $contacts,
                'publishedContacts' => $publishedContacts,
                'notPublishedContacts' => $notPublishedContacts,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    //add contact ajax
    public function addContactAjax($school_id)
    {
        $school = $this->schoolRepository->find($school_id);
        $response = new Response();

        $content = $this->renderView('admin/school/contact_add_ajax.html.twig', array(
            'school' => $school,
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function addContact($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);

        $contact = new SchoolContact();
        $formInitContact = $this->createForm(SchoolContactEditType::class, $contact);
        $formInitContact->handleRequest($request);
        if ($formInitContact->isSubmitted() && $formInitContact->isValid()) {
            $slug = $this->platformService->sluggify($contact->getName());
            $contact->setSlug($slug);
            $contact->setPublished(false);
            $contact->setSchool($school);
            $contact->setPublished(false);
            $contact->setShowmap(true);

            $contact->setDescription("description ".$contact->getName());

            $this->em->persist($contact);
            $this->em->flush();

            return $this->redirectToRoute('admin_school_contact_edit', array(
                'school_id' => $school->getId(),
                'contact_id' => $contact->getId(),
            ));
        }

        $school = $this->schoolRepository->find($school_id);
        $contacts = $this->schoolContactRepository->findBy(array(
            'school' => $school
        ));

        return $this->render('admin/school/contact_add.html.twig', array(
            'school' => $school,
            '$contact' => $contacts,
            '$formInitContact' => $formInitContact->createView(),
        ));
    }

    /*
     * School Edition contact
     */
    public function editContact($school_id, $contact_id)
    {
        $contact = $this->schoolContactRepository->find($contact_id);

        $school = $this->schoolRepository->find($school_id);
        $contacts = $this->schoolContactRepository->findBy(array(
            'school' => $school
        ));
        return $this->render('admin/school/contact_edit.html.twig', array(
            'contact' => $contact,
            'contacts' => $contacts,
            'school' => $contact->getSchool(),
        ));
    }

    /*
     * School Contact delete
     */
    public function deleteContact($school_id, $contact_id, Request $request)
    {
        $contact = $this->schoolContactRepository->find($contact_id);

        $response = new Response();
        if ($contact) {
            $this->em->remove($contact);
            $this->em->flush();

            $school = $this->schoolRepository->find($school_id);
            $contacts = $this->schoolContactRepository->findBy(array(
                'school' => $school
            ));
            $publishedContacts = $this->schoolContactRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));
            $notPublishedContacts = $this->schoolContactRepository->findBy(array(
                'school' => $school,
                'published' => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'id' => $contact_id,
                'contacts' => $contacts,
                'publishedContacts' => $publishedContacts,
                'notPublishedContacts' => $notPublishedContacts,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /*
     * School Contact Edition common
     */
    public function doEditContact($contact_id, Request $request)
    {
        $contact = $this->schoolContactRepository->find($contact_id);

        $contactTemp = new SchoolContact();
        $formContact = $this->createForm(SchoolContactEditType::class, $contactTemp);
        $formContact->handleRequest($request);
        $response = new Response();
        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $contact->setName($contactTemp->getName());
            $slug = $this->platformService->sluggify($contactTemp->getSlug());
            $contact->setSlug($slug);
            $contact->setAddress(($contactTemp->getAddress())?$contactTemp->getAddress():"");
            $contact->setEmail(($contactTemp->getEmail())?$contactTemp->getEmail():"");
            $contact->setPhone(($contactTemp->getPhone())?$contactTemp->getPhone():"");
            $contact->setWebsite(($contactTemp->getWebsite())?$contactTemp->getWebsite():"");
            $contact->setLatitude(($contactTemp->getLatitude())?$contactTemp->getLatitude():"");
            $contact->setLongitude(($contactTemp->getLongitude())?$contactTemp->getLongitude():"");
            $contact->setDescription(($contactTemp->getDescription())?$contactTemp->getDescription():"");

            $this->em->persist($contact);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'name' => $contact->getName(),
                'slug' => $contact->getSlug(),
                'address' => $contact->getAddress(),
                'email' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'website' => $contact->getWebsite(),
                'latitude' => $contact->getLatitude(),
                'longitude' => $contact->getLongitude(),
                'description' => $contact->getDescription(),
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