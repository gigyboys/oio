<?php
namespace App\Controller\API;

use App\Entity\Category;
use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Logo;
use App\Entity\SchoolOfTheDay;
use App\Entity\TypeSchool;
use App\Form\CategoryEditType;
use App\Form\CategoryInitType;
use App\Form\CoverType;
use App\Form\LogoType;
use App\Form\SchoolDescriptionType;
use App\Form\SchoolInitType;
use App\Form\SchoolType;
use App\Form\SearchDateIntervalType;
use App\Form\SodInitType;
use App\Model\SchoolInit;
use App\Model\SearchDateInterval;
use App\Model\SodInit;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\LogoRepository;
use App\Repository\OptionRepository;
use App\Repository\ParameterRepository;
use App\Repository\SchoolOfTheDayRepository;
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

class SchoolController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        OptionRepository $optionRepository,
        ParameterRepository $parameterRepository,
        SchoolService $schoolService,
        CategoryRepository $categoryRepository,
        CategorySchoolRepository $categorySchoolRepository,
        PlatformService $platformService,
        UserRepository $userRepository,
        TypeRepository $typeRepository,
        LogoRepository $logoRepository,
        CoverRepository $coverRepository,
        SchoolOfTheDayRepository $schoolOfTheDayRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->optionRepository = $optionRepository;
        $this->parameterRepository = $parameterRepository;
        $this->schoolService = $schoolService;
        $this->categoryRepository = $categoryRepository;
        $this->categorySchoolRepository = $categorySchoolRepository;
        $this->platformService = $platformService;
        $this->userRepository = $userRepository;
        $this->typeRepository = $typeRepository;
        $this->logoRepository = $logoRepository;
        $this->coverRepository = $coverRepository;
        $this->schoolOfTheDayRepository = $schoolOfTheDayRepository;
        $this->em = $em;
    }

    public function schoolsState(): Response
    {
        $schoolTemps = $this->schoolRepository->findBy(array(
            'published' => true,
        ));

        $schools = array();
        foreach ($schoolTemps as $school) {
            array_push($schools,array(
                'id' => $school->getId(),
                'datemodif' => $school->getDatemodif()->format('Y-m-d H:i:s'),
                'position' => $school->getPosition(),
            ));
        }

        $response = new Response();

        $response->setContent(json_encode(array(
            'state' => 1,
            'schools' => $schools,
        )));
            
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function schoolInfo($school_id): Response
    {
        $schoolTemp = $this->schoolRepository->find($school_id);

        $response = new Response();

        $logoPath = $this->schoolService->getLogoPath($schoolTemp);
        $logo116x116 = $this->platformService->imagineFilter($logoPath, '116x116');

        $coverPath = $this->schoolService->getCoverPath($schoolTemp);
        $cover1200x500 = $this->platformService->imagineFilter($coverPath, '1200x500');

        $school = array(
            'id'                => $schoolTemp->getId(),
            'name'              => $schoolTemp->getName(),
            'shortName'         => $schoolTemp->getShortname(),
            'slug'              => $schoolTemp->getSlug(),
            'shortDescription'  => $schoolTemp->getShortDescription(),
            'description'       => $schoolTemp->getDescription(),
            'logoPath'          => $logoPath,
            'logo116x116'       => $logo116x116,
            'coverPath'         => $coverPath,
            'cover1200x500'     => $cover1200x500,
        );

        $response->setContent(json_encode(array(
            'state' => 1,
            'school' => $school,
        )));
            
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}