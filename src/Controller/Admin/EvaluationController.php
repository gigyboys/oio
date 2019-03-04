<?php
namespace App\Controller\Admin;

use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Document;
use App\Entity\Logo;
use App\Entity\TypeSchool;
use App\Form\CoverType;
use App\Form\DocumentEditType;
use App\Form\DocumentType;
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
use App\Repository\EvaluationRepository;
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

class EvaluationController extends AbstractController {

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
        DocumentRepository $documentRepository,
        EvaluationRepository $evaluationRepository,
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
        $this->documentRepository = $documentRepository;
        $this->evaluationRepository = $evaluationRepository;
        $this->documentAuthorizationRepository = $documentAuthorizationRepository;

        $this->em = $em;
    }

    public function index($school_id)
    {
        $school = $this->schoolRepository->find($school_id);
        $evaluations = $this->evaluationRepository->findBy(array(
            'school' => $school,
            'current' => true,
        ));
        return $this->render('admin/school/evaluations.html.twig', array(
            'school' => $school,
            'evaluations' => $evaluations
        ));
    }


    /*
     * School Evaluation delete
     */
    public function deleteEvaluation($school_id, $id, Request $request)
    {
        $evaluation = $this->evaluationRepository->find($id);
        $school = $this->schoolRepository->find($school_id);

        $response = new Response();
        if ($evaluation) {
            $evaluation->setCurrent(false);
            $this->em->persist($evaluation);
            $this->em->flush();

            $evaluations = $this->evaluationRepository->findBy(array(
                'school' => $school,
                'current' => true,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'id' => $id,
                'evaluations' => $evaluations,
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