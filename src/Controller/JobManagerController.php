<?php
namespace App\Controller;

use App\Entity\Job;
use App\Entity\JobIllustration;
use App\Form\JobInitType;
//use App\Form\EventType;
use App\Service\PlatformService;
use App\Service\JobService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SectorRepository;
use App\Repository\ParameterRepository;
use App\Repository\JobRepository;
use App\Repository\JobIllustrationRepository;

class JobManagerController extends AbstractController {

    public function __construct(
        SectorRepository $sectorRepository,
        ParameterRepository $parameterRepository,
        JobService $jobService,
        PlatformService $platformService,
        JobRepository $jobRepository,
        JobIllustrationRepository $jobIllustrationRepository,
        ObjectManager $em
    )
    {
        $this->sectorRepository = $sectorRepository;
        $this->parameterRepository = $parameterRepository;
        $this->jobService = $jobService;
        $this->platformService = $platformService;
        $this->jobRepository = $jobRepository;
        $this->jobIllustrationRepository = $jobIllustrationRepository;
        $this->em = $em;

        $this->platformService->registerVisit();
    }


    public function addJobAjax()
    {
        $user = $this->getUser();
        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($user){
            $sectors = $this->sectorRepository->findBy(
                array(), 
                array('name' => 'ASC')
            );
            $content = $this->renderView('job/job_add_ajax.html.twig', array(
                'sectors' => $sectors,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'content' => $content,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doAddJob(Request $request)
    {
        $job = new Job();
        $user = $this->getUser();
        $form = $this->createForm(JobInitType::class, $job);
        $form->handleRequest($request);
        $errormsg = "";
        if ($form->isSubmitted() && $form->isValid()) { 
            if(trim($job->getTitle())){
                $job->setDescription('Description '.$job->getTitle());

                //sector
                $sector = $this->sectorRepository->find($job->getSectorId());
                $job->setSector($sector);
                $slug = $this->platformService->getSlug($job->getTitle(), $job);
                $job->setSlug($slug);
                $job->setDate(new \DateTime());

                $job->setPublished(true);
                $job->setValid(false);
                $job->setDeleted(false);
                $job->setUser($user);
                $job->setShowAuthor(true);
                $job->setActiveComment(true);
                $job->setTovalid(false);

                $this->em->persist($job);

                $this->em->flush();

                return $this->redirectToRoute('job_manager_edit', array(
                    'job_id' => $job->getId(),
                ));
            }else{
                return $this->render('job/add_job.html.twig', array(
                    'form' => $form->createView(),
                    'errormsg' => $errormsg,
                    'job' => $job,
                ));
            }
        }

        return $this->render('job/add_job.html.twig', array(
            'form' => $form->createView(),
            'errormsg' => $errormsg,
            'job' => $job,
        ));
    }

    public function editJob($job_id)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);
        if($job && $user && $job->getUser()->getId() == $user->getId() ){
            return $this->render('job/job_edit.html.twig', array(
                'job' => $job,
                'entityView' => 'job',
            ));
        }else{
            return $this->redirectToRoute('job');
        }
    }

    public function toValidJobAjax($job_id)
    {
        $response = new Response();
        $job = $this->jobRepository->find($job_id);
        $content = $this->renderView('job/job_tovalid_ajax.html.twig', array(
            'job' => $job
        ));

        $response->setContent(json_encode(array(
            'state' => 1,
            'content' => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function togglePublication($job_id, Request $request)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($job) {
            if ($this->isGranted('ROLE_ADMIN') || $job->getUser() == $user){
                if($job->getPublished() == true){
                    $job->setPublished(false) ;
                }else{
                    $job->setPublished(true) ;
                }

                $this->em->persist($job);
                $this->em->flush();

                if ($this->isGranted('ROLE_ADMIN')){
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
                        'case' => $job->getPublished(),
                        'jobs' => $jobs,
                        'publishedJobs' => $publishedJobs,
                        'notPublishedJobs' => $notPublishedJobs,
                    )));
                }else{
                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'case' => $job->getPublished(),
                    )));
                }
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function toggleShowAuthor($job_id, Request $request)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($job && $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user){
            if($job->getShowAuthor() == true){
                $job->setShowAuthor(false) ;
            }else{
                $job->setShowAuthor(true) ;
            }

            $this->em->persist($job);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $job->getShowAuthor(),
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function toggleActiveComment($job_id, Request $request)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($job && $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user){
            if($job->getActiveComment() == true){
                $job->setActiveComment(false) ;
            }else{
                $job->setActiveComment(true) ;
            }

            $this->em->persist($job);
            $this->em->flush();

            $response->setContent(json_encode(array(
                'state' => 1,
                'case' => $job->getActiveComment(),
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}