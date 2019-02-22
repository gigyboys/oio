<?php
namespace App\Controller\Admin;

use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Document;
use App\Entity\Field;
use App\Entity\Logo;
use App\Entity\TypeSchool;
use App\Form\CoverType;
use App\Form\DocumentEditType;
use App\Form\DocumentType;
use App\Form\FieldEditType;
use App\Form\FieldInitType;
use App\Form\LogoType;
use App\Form\SchoolDescriptionType;
use App\Form\SchoolInitType;
use App\Form\SchoolType;
use App\Model\DocumentEdit;
use App\Model\SchoolInit;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\DocumentAuthorizationRepository;
use App\Repository\DocumentRepository;
use App\Repository\FieldRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\PlatformService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DocumentController extends AbstractController {

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
        FieldRepository $fieldRepository,
        DocumentRepository $documentRepository,
        DocumentAuthorizationRepository $documentAuthorizationRepository,
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
        $this->fieldRepository = $fieldRepository;
        $this->documentRepository = $documentRepository;
        $this->documentAuthorizationRepository = $documentAuthorizationRepository;

        $this->em = $em;
    }

    public function index($school_id)
    {
        $school = $this->schoolRepository->find($school_id);
        $documents = $this->documentRepository->findBy(array(
            'school' => $school
        ));

        $publishedDocuments = $this->documentRepository->findBy(array(
            'school' => $school,
            'published' => true,
        ));
        $notPublishedDocuments = $this->documentRepository->findBy(array(
            'school' => $school,
            'published' => false,
        ));

        return $this->render('admin/school/documents.html.twig', array(
            'school' => $school,
            'documents' => $documents,
            'publishedDocuments' => $publishedDocuments,
            'notPublishedDocuments' => $notPublishedDocuments,
        ));
    }


    public function togglePublicationDocument($school_id, $document_id, Request $request)
    {
        $document = $this->documentRepository->find($document_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($document) {
            if($document->getPublished() == true){
                $document->setPublished(false) ;
            }else{
                $document->setPublished(true) ;
            }

            $this->em->persist($document);
            $this->em->flush();

            $school = $this->schoolRepository->find($school_id);
            $documents = $this->documentRepository->findBy(array(
                'school' => $school
            ));

            $publishedDocuments = $this->documentRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));
            $notPublishedDocuments = $this->documentRepository->findBy(array(
                'school' => $school,
                'published' => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $document->getPublished(),
                'school' => $school,
                'documents' => $documents,
                'publishedDocuments' => $publishedDocuments,
                'notPublishedDocuments' => $notPublishedDocuments,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }



    public function addDocument($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);

        $documentTemp = new DocumentEdit();
        $form = $this->createForm(DocumentType::class, $documentTemp);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $documentTemp->getFile();

            $t=time();
            $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$school->getId().'_'.$t.'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('document_directory'),
                    $fileName
                );
                $document = new Document();
                $document->setName($documentTemp->getName());
                $document->setDescription($documentTemp->getDescription());
                //authorization
                $authorization = $this->documentAuthorizationRepository->find($documentTemp->getAuthorizationId());
                $document->setDocumentAuthorization($authorization);

                $document->setPath($fileName);
                $document->setOriginalname($file->getClientOriginalName());

                $document->setPublished(false);
                $document->setSchool($school);
                $document->setDate(new \DateTime());
                $this->em->persist($document);
                $this->em->flush();
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            
            return $this->redirectToRoute('admin_school_document', array(
                'school_id' => $school->getId(),
            ));
        }

        $school = $this->schoolRepository->find($school_id);
        $documents = $this->documentRepository->findBy(array(
            'school' => $school
        ));

        $authorizations = $this->documentAuthorizationRepository->findAll();
        return $this->render('admin/school/document_add.html.twig', array(
            'school' => $school,
            'documents' => $documents,
            'authorizations' => $authorizations,
            'form' => $form->createView(),
        ));
    }

    public function downloadDocument($school_id, $document_id, Request $request)
    {
        $user = $this->getUser();
        $document = $this->documentRepository->find($document_id);

        $school = $this->schoolRepository->find($school_id);

        $file = new File($document->getAbsolutePath());

        //download
        $this->platformService->registerDownload($document, $user, $request);

        return $this->file($file);
    }

    public function editDocument($school_id, $document_id)
    {
        $document = $this->documentRepository->find($document_id);

        $school = $this->schoolRepository->find($school_id);
        $documents = $this->documentRepository->findBy(array(
            'school' => $school
        ));
        $authorizations = $this->documentAuthorizationRepository->findAll();

        return $this->render('admin/school/document_edit.html.twig', array(
            'document' => $document,
            'documents' => $documents,
            'authorizations' => $authorizations,
            'school' => $school,
        ));
    }

    public function doEditDocument($document_id, Request $request)
    {
        $document = $this->documentRepository->find($document_id);

        $documentTemp = new DocumentEdit();
        $form = $this->createForm(DocumentEditType::class, $documentTemp);
        $form->handleRequest($request);
        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
            'error' => $form->getErrors(),
        )));

        if ($form->isSubmitted() && $form->isValid()) {
            $document->setName($documentTemp->getName());
            $document->setDescription($documentTemp->getDescription());
            //authorization
            $authorization = $this->documentAuthorizationRepository->find($documentTemp->getAuthorizationId());
            $document->setDocumentAuthorization($authorization);

            $this->em->persist($document);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state'             => 1,
                'name'              => $document->getName(),
                'description'       => $document->getDescription(),
                'authorizationId'   => $document->getDocumentAuthorization()->getId(),
                'authorizationName' => $document->getDocumentAuthorization()->getName(),
            )));
        }
        
        /*
            return $this->render('formtest.html.twig', array(
                'form' => $form->createView(),
            ));
        */

        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }
}