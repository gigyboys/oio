<?php
namespace App\Controller\Admin;

use App\Entity\TypeSchool;
use App\Form\AccessibilityType;
use App\Model\Accessibility;
use App\Repository\ParameterRepository;
use App\Repository\TypeSchoolRepository;
use App\Service\PlatformService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SchoolRepository;

class SettingController extends AbstractController {

    public function __construct(
        SchoolRepository $schoolRepository,
        PlatformService $platformService,
        ParameterRepository $parameterRepository,
        ObjectManager $em
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->platformService = $platformService;
        $this->parameterRepository = $parameterRepository;
        $this->em = $em;
    }

    public function accessibility(Request $request)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()){
            $accessibility = new Accessibility();
            $form = $this->createForm(AccessibilityType::class, $accessibility);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $default = 12;
                $categories_index = $this->parameterRepository->findOneBy(array(
                    'parameter' => 'categories_index'
                ));
                $categories_index->setValue($accessibility->getCategoriesIndex());
                if(!($categories_index->getValue()>=2 && $categories_index->getValue()<=24)){
                    $categories_index->setValue($default);
                }
                $this->em->persist($categories_index);

                $schools_by_page = $this->parameterRepository->findOneBy(array(
                    'parameter' => 'schools_by_page'
                ));
                $schools_by_page->setValue($accessibility->getSchoolsByPage());
                if(!($schools_by_page->getValue()>=2 && $schools_by_page->getValue()<=24)){
                    $schools_by_page->setValue($default);
                }
                $this->em->persist($schools_by_page);

                $posts_by_page = $this->parameterRepository->findOneBy(array(
                    'parameter' => 'posts_by_page'
                ));
                $posts_by_page->setValue($accessibility->getPostsByPage());
                if(!($posts_by_page->getValue()>=2 && $posts_by_page->getValue()<=24)){
                    $posts_by_page->setValue($default);
                }
                $this->em->persist($posts_by_page);

                $this->em->flush();

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'categoriesIndex' => $categories_index->getValue(),
                    'schoolsByPage' => $schools_by_page->getValue(),
                    'postsByPage' => $posts_by_page->getValue(),
                )));
            }else{
                $response->setContent(json_encode(array(
                    'state' => 0,
                )));
            }
        }else{
            $categories_index = $this->parameterRepository->findOneBy(array(
                'parameter' => 'categories_index'
            ));
            $schools_by_page = $this->parameterRepository->findOneBy(array(
                'parameter' => 'schools_by_page'
            ));
            $posts_by_page = $this->parameterRepository->findOneBy(array(
                'parameter' => 'posts_by_page'
            ));

            $response = $this->render('admin/setting/accessibility.html.twig', array(
                'categories_index' => $categories_index->getValue(),
                'schools_by_page' => $schools_by_page->getValue(),
                'posts_by_page' => $posts_by_page->getValue(),
            ));
        }
        return $response;
    }
}