<?php
namespace App\Controller\Admin;

use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Field;
use App\Entity\Logo;
use App\Entity\TypeSchool;
use App\Form\CoverType;
use App\Form\FieldEditType;
use App\Form\FieldInitType;
use App\Form\LogoType;
use App\Form\SchoolDescriptionType;
use App\Form\SchoolInitType;
use App\Form\SchoolType;
use App\Model\SchoolInit;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\FieldRepository;
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
use App\Entity\School;
use App\Repository\SchoolRepository;

class FieldController extends AbstractController {

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

        $this->em = $em;
    }

    public function index($school_id)
    {
        $school = $this->schoolRepository->find($school_id);
        $fields = $this->fieldRepository->findBy(array(
            'school' => $school
        ));
        $publishedFields = $this->fieldRepository->findBy(array(
            'school' => $school,
            'published' => true,
        ));
        $notPublishedFields = $this->fieldRepository->findBy(array(
            'school' => $school,
            'published' => false,
        ));

        return $this->render('admin/school/field.html.twig', array(
            'school' => $school,
            'fields' => $fields,
            'publishedFields' => $publishedFields,
            'notPublishedFields' => $notPublishedFields,
        ));
    }

    public function togglePublicationField($school_id, $field_id, Request $request)
    {
        $field = $this->fieldRepository->find($field_id);

        $response = new Response();

        if ($field) {
            if($field->getPublished() == true){
                $field->setPublished(false) ;
            }else{
                $field->setPublished(true) ;
            }

            $this->em->persist($field);
            $this->em->flush();

            $school = $this->schoolRepository->find($school_id);
            $fields = $this->fieldRepository->findBy(array(
                'school' => $school
            ));
            $publishedFields = $this->fieldRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));
            $notPublishedFields = $this->fieldRepository->findBy(array(
                'school' => $school,
                'published' => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $field->getPublished(),
                'fields' => $fields,
                'publishedFields' => $publishedFields,
                'notPublishedFields' => $notPublishedFields,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    //add field ajax
    public function addFieldAjax($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);
        $response = new Response();

        $content = $this->renderView('admin/school/field_add_ajax.html.twig', array(
            'school' => $school,
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function addField($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);

        $field = new Field();
        $formInitField = $this->createForm(FieldInitType::class, $field);
        $formInitField->handleRequest($request);
        if ($formInitField->isSubmitted() && $formInitField->isValid()) {
            $slug = $this->platformService->sluggify($field->getName());
            $field->setSlug($slug);
            $field->setPublished(false);
            $field->setSchool($school);

            $field->setDescription("description ".$field->getName());

            $this->em->persist($field);
            $this->em->flush();

            return $this->redirectToRoute('admin_school_field_edit', array(
                'school_id' => $school->getId(),
                'field_id' => $field->getId(),
            ));
        }

        $school = $this->schoolRepository->find($school_id);
        $fields = $this->fieldRepository->findBy(array(
            'school' => $school
        ));

        return $this->render('admin/school/field_add.html.twig', array(
            'school' => $school,
            'fields' => $fields,
            'formInitField' => $formInitField->createView(),
        ));
    }

    /*
     * School Edition field
     */
    public function editField($school_id, $field_id)
    {
        $field = $this->fieldRepository->find($field_id);

        $school = $this->schoolRepository->find($school_id);
        $fields = $this->fieldRepository->findBy(array(
            'school' => $school
        ));
        return $this->render('admin/school/field_edit.html.twig', array(
            'field' => $field,
            'fields' => $fields,
            'school' => $field->getSchool(),
        ));
    }

    /*
     * School Field delete
     */
    public function deleteField($school_id, $id, Request $request)
    {
        $field = $this->fieldRepository->find($id);

        $response = new Response();
        if ($field) {
            $this->em->remove($field);
            $this->em->flush();

            $school = $this->schoolRepository->find($school_id);
            $fields = $this->fieldRepository->findBy(array(
                'school' => $school
            ));
            $publishedFields = $this->fieldRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));
            $notPublishedFields = $this->fieldRepository->findBy(array(
                'school' => $school,
                'published' => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'id' => $id,
                'fields' => $fields,
                'publishedFields' => $publishedFields,
                'notPublishedFields' => $notPublishedFields,
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
     * School Field Edition common
     */
    public function doEditField($field_id, Request $request)
    {
        $field = $this->fieldRepository->find($field_id);

        $fieldTemp = new Field();
        $formField = $this->createForm(FieldEditType::class, $fieldTemp);
        $formField->handleRequest($request);
        $response = new Response();
        if ($formField->isSubmitted() && $formField->isValid()) {
            $field->setName($fieldTemp->getName());
            $field->setDescription($fieldTemp->getDescription());

            $slug = $this->platformService->sluggify($fieldTemp->getSlug());
            $field->setSlug($slug);

            $this->em->persist($field);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'name' => $field->getName(),
                'slug' => $field->getSlug(),
                'description' => $field->getDescription(),
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