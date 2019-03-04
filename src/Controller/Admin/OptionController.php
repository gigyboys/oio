<?php
namespace App\Controller\Admin;

use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Option;
use App\Entity\Logo;
use App\Entity\TypeSchool;
use App\Form\CoverType;
use App\Form\OptionEditType;
use App\Form\OptionInitType;
use App\Form\LogoType;
use App\Form\SchoolInitType;
use App\Form\SchoolType;
use App\Model\SchoolInit;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\OptionRepository;
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

class OptionController extends AbstractController {

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
        OptionRepository $optionRepository,
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
        $this->optionRepository = $optionRepository;

        $this->em = $em;
    }

    public function index($school_id)
    {
        $school = $this->schoolRepository->find($school_id);
        $options = $this->optionRepository->findBy(array(
            'school' => $school
        ));
        $publishedOptions = $this->optionRepository->findBy(array(
            'school' => $school,
            'published' => true,
        ));
        $notPublishedOptions = $this->optionRepository->findBy(array(
            'school' => $school,
            'published' => false,
        ));

        return $this->render('admin/school/option.html.twig', array(
            'school' => $school,
            'options' => $options,
            'publishedOptions' => $publishedOptions,
            'notPublishedOptions' => $notPublishedOptions,
        ));
    }

    public function togglePublicationOption($school_id, $option_id, Request $request)
    {
        $option = $this->optionRepository->find($option_id);

        $response = new Response();

        if ($option) {
            if($option->getPublished() == true){
                $option->setPublished(false) ;
            }else{
                $option->setPublished(true) ;
            }

            $this->em->persist($option);
            $this->em->flush();

            $school = $this->schoolRepository->find($school_id);
            $options = $this->optionRepository->findBy(array(
                'school' => $school
            ));
            $publishedOptions = $this->optionRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));
            $notPublishedOptions = $this->optionRepository->findBy(array(
                'school' => $school,
                'published' => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $option->getPublished(),
                'options' => $options,
                'publishedOptions' => $publishedOptions,
                'notPublishedOptions' => $notPublishedOptions,
            )));
        }else{
            $response->setContent(json_encode(array(
                'state' => 0,
            )));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    //add option ajax
    public function addOptionAjax($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);
        $response = new Response();

        $content = $this->renderView('admin/school/option_add_ajax.html.twig', array(
            'school' => $school,
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function addOption($school_id, Request $request)
    {
        $school = $this->schoolRepository->find($school_id);

        $option = new Option();
        $formInitOption = $this->createForm(OptionInitType::class, $option);
        $formInitOption->handleRequest($request);
        if ($formInitOption->isSubmitted() && $formInitOption->isValid()) {
            $option->setPublished(false);
            $option->setSchool($school);

            $this->em->persist($option);
            $this->em->flush();

            return $this->redirectToRoute('admin_school_option_edit', array(
                'school_id' => $school->getId(),
                'option_id' => $option->getId(),
            ));
        }

        $school = $this->schoolRepository->find($school_id);
        $options = $this->optionRepository->findBy(array(
            'school' => $school
        ));

        return $this->render('admin/school/option_add.html.twig', array(
            'school' => $school,
            'options' => $options,
            'formInitOption' => $formInitOption->createView(),
        ));
    }

    /*
     * School Edition option
     */
    public function editOption($school_id, $option_id)
    {
        $option = $this->optionRepository->find($option_id);

        $school = $this->schoolRepository->find($school_id);
        $options = $this->optionRepository->findBy(array(
            'school' => $school
        ));
        return $this->render('admin/school/option_edit.html.twig', array(
            'option' => $option,
            'options' => $options,
            'school' => $option->getSchool(),
        ));
    }

    /*
     * School Option delete
     */
    public function deleteOption($school_id, $id, Request $request)
    {
        $option = $this->optionRepository->find($id);

        $response = new Response();
        if ($option) {
            $this->em->remove($option);
            $this->em->flush();

            $school = $this->schoolRepository->find($school_id);
            $options = $this->optionRepository->findBy(array(
                'school' => $school
            ));
            $publishedOptions = $this->optionRepository->findBy(array(
                'school' => $school,
                'published' => true,
            ));
            $notPublishedOptions = $this->optionRepository->findBy(array(
                'school' => $school,
                'published' => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'id' => $id,
                'options' => $options,
                'publishedOptions' => $publishedOptions,
                'notPublishedOptions' => $notPublishedOptions,
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
     * School Option Edition common
     */
    public function doEditOption($option_id, Request $request)
    {
        $option = $this->optionRepository->find($option_id);

        $optionTemp = new Option();
        $formOption = $this->createForm(OptionEditType::class, $optionTemp);
        $formOption->handleRequest($request);
        $response = new Response();
        if ($formOption->isSubmitted() && $formOption->isValid()) {
            $option->setName($optionTemp->getName());
            $option->setContent($optionTemp->getContent());

            $this->em->persist($option);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'name' => $option->getName(),
                'content' => $option->getContent(),
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