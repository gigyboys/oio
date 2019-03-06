<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\CategorySchool;
use App\Entity\Cover;
use App\Entity\Job;
use App\Entity\Logo;
use App\Entity\Tag;
use App\Entity\TypeSchool;
use App\Form\CategoryEditType;
use App\Form\CategoryInitType;
use App\Form\CoverType;
use App\Form\JobInitType;
use App\Form\LogoType;
use App\Form\SchoolDescriptionType;
use App\Form\SchoolInitType;
use App\Form\SchoolType;
use App\Form\TagEditType;
use App\Form\TagInitType;
use App\Model\SchoolInit;
use App\Repository\CategoryRepository;
use App\Repository\CategorySchoolRepository;
use App\Repository\CoverRepository;
use App\Repository\JobRepository;
use App\Repository\LogoRepository;
use App\Repository\ParameterRepository;
use App\Repository\TagRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Repository\GalleryRepository;
use App\Service\PlatformService;
use App\Service\JobService;
use App\Service\SchoolService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\School;
use App\Repository\SchoolRepository;

class JobController extends AbstractController {

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
        TagRepository $tagRepository,
        JobRepository $jobRepository,
        CommentRepository $commentRepository,
        GalleryRepository $galleryRepository,
        JobService $jobService,
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
        $this->tagRepository = $tagRepository;
        $this->jobRepository = $jobRepository;
        $this->galleryRepository = $galleryRepository;
        $this->jobService = $jobService;
        $this->em = $em;
    }

    public function job()
    {
        $jobs = $this->jobRepository->getJobs();
        $publishedJobs = $this->jobRepository->findBy(array(
            'published' => true,
            'tovalid'   => true,
            'deleted'   => false,
        ));
        $notPublishedJobs = $this->jobRepository->findBy(array(
            'published' => false,
            'tovalid'   => true,
            'deleted'   => false,
        ));

        return $this->render('admin/job/jobs.html.twig', array(
            'jobs' => $jobs,
            'publishedJobs' => $publishedJobs,
            'notPublishedJobs' => $notPublishedJobs,
            'view' => 'job',
        ));
    }

    public function jobCreation()
    {
        $jobs = $this->jobRepository->findBy(array(
            'tovalid' => false
        ));

        return $this->render('admin/job/jobs_creation.html.twig', array(
            'jobs' => $jobs,
            'view' => 'job',
        ));
    }

    public function addJob(Request $request)
    {
        $job = new Job();
        $form = $this->createForm(JobInitType::class, $job);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $this->platformService->getSlug($job->getTitle(), $job);
            $job->setSlug($slug);
            $job->setDate(new \DateTime());

            $job->setPublished(false);
            $job->setValid(false);
            $job->setDeleted(false);
            $user = $this->getUser();
            $job->setUser($user);
            $job->setShowAuthor(true);
            $job->setActiveComment(true);
            $job->setTovalid(true);

            $this->em->persist($job);

            $this->em->flush();

            return $this->redirectToRoute('admin_job');
        }

        return $this->render('admin/job/job_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /*
     * confirm delete job
     */
    public function deleteJob($job_id, Request $request)
    {
        $job = $this->jobRepository->find($job_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));
        if ($job) {
            $job->setDeleted(true);
            $this->em->persist($job);
            $this->em->flush();

            $jobs = $this->jobRepository->getJobs();
            $publishedJobs = $this->jobRepository->findBy(array(
                'published' => true,
                'tovalid'   => true,
                'deleted'   => false,
            ));
            $notPublishedJobs = $this->jobRepository->findBy(array(
                'published' => false,
                'tovalid'   => true,
                'deleted'   => false,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'id' => $job_id,
                'jobs' => $jobs,
                'publishedJobs' => $publishedJobs,
                'notPublishedJobs' => $notPublishedJobs,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}