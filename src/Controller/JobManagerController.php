<?php
namespace App\Controller;

use App\Entity\Job;
use App\Entity\JobIllustration;
use App\Entity\Sector;
use App\Form\JobIllustrationType;
use App\Form\JobInitType;
use App\Form\JobDetailType;
use App\Form\JobContactType;
use App\Form\JobType;
use App\Service\PlatformService;
use App\Service\JobService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\SectorRepository;
use App\Repository\ContractRepository;
use App\Repository\ParameterRepository;
use App\Repository\JobRepository;
use App\Repository\JobIllustrationRepository;

class JobManagerController extends AbstractController {

    public function __construct(
        SectorRepository $sectorRepository,
        ContractRepository $contractRepository,
        ParameterRepository $parameterRepository,
        JobService $jobService,
        PlatformService $platformService,
        JobRepository $jobRepository,
        JobIllustrationRepository $jobIllustrationRepository,
        ObjectManager $em
    )
    {
        $this->sectorRepository = $sectorRepository;
        $this->contractRepository = $contractRepository;
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
            
            $sectors = $this->sectorRepository->findBy(
                array(), 
                array('name' => 'ASC')
            );

            $contracts = $this->contractRepository->findBy(
                array(), 
                array('name' => 'ASC')
            );
            
            return $this->render('job/job_edit.html.twig', array(
                'job'           => $job,
                'sectors'       => $sectors,
                'contracts'     => $contracts,
                'entityView'    => 'job',
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
            'state'     => 1,
            'content'   => $content,
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doToValidJob($job_id, Request $request)
    {
        $job = $this->jobRepository->find($job_id);
        $user = $this->getUser();

        if($job && $user && !$job->getTovalid() && $job->getUser()->getId() == $user->getId()){
            $job->setTovalid(true);
            $this->em->persist($job);
            $this->em->flush();
            return $this->redirectToRoute('job_manager_edit', array(
                'job_id' => $job->getId()
            ));
        }
        return $this->redirectToRoute('job');
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

    public function editIllustrationPopup($job_id, Request $request)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();

        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($job && $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user) {
            $illustrations = $this->jobIllustrationRepository->findBy(array(
                'job' => $job
            ));
            $current = $this->jobIllustrationRepository->findOneBy(array(
                'job' => $job,
                'current' => true,
            ));

            $content = $this->renderView('job/edit_illustration_popup.html.twig', array(
                'job' => $job,
                'illustrations' => $illustrations,
                'current' => $current,
            ));

            $response->setContent(json_encode(array(
                'state' => 1,
                'content' => $content,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function selectIllustration($job_id, $illustration_id)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($job && $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user){
            if($illustration_id == 0){
                $illustrations = $this->jobIllustrationRepository->findBy(array(
                    'job' => $job
                ));

                foreach ($illustrations as $illustration) {
                    $illustration->setCurrent(false);
                    $this->em->persist($illustration);
                }
                $this->em->flush();

                $defaultIllustrationPath = 'default/images/job/illustration/default.jpeg';
                $illustrationPath = $defaultIllustrationPath;
            }else{
                $illustration = $this->jobIllustrationRepository->find($illustration_id);

                if($illustration && $job->getId() == $illustration->getJob()->getId()){
                    $illustrations = $this->jobIllustrationRepository->findBy(array(
                        'job' => $job
                    ));

                    foreach ($illustrations as $illustrationTmp) {
                        $illustrationTmp->setCurrent(false);
                        $this->em->persist($illustrationTmp);
                    }

                    $illustration->setCurrent(true);

                    $this->em->persist($illustration);
                    $this->em->flush();
                    $illustrationPath = $illustration->getWebPath();
                }
            }

            $illustration116x116 = $this->platformService->imagineFilter($illustrationPath, '116x116');
            $illustration600x250 = $this->platformService->imagineFilter($illustrationPath, '600x250');

            $response->setContent(json_encode(array(
                'state' => 1,
                'illustration116x116' => $illustration116x116,
                'illustration600x250' => $illustration600x250,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function uploadIllustration($job_id, Request $request)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if ($job && $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user) {
            $illustration = new JobIllustration();
            $form = $this->createForm(JobIllustrationType::class, $illustration);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $illustration->getFile();

                $t=time();
                $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$job->getId().'_'.$t.'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('job_illustration_directory'),
                        $fileName
                    );
                    $illustration->setPath($fileName);
                    $illustration->setOriginalname($file->getClientOriginalName());
                    $illustration->setName($file->getClientOriginalName());

                    $illustrations = $this->jobIllustrationRepository->findBy(array('job' => $job));

                    foreach ($illustrations as $jobIllustration) {
                        $jobIllustration->setCurrent(false);
                    }

                    $illustration->setCurrent(true);
                    
                    $illustration->setJob($job);

                    $this->em->persist($illustration);
                    $this->em->flush();
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $path = $illustration->getWebPath();
                $illustration116x116 = $this->platformService->imagineFilter($path, '116x116');
                $illustration600x250 = $this->platformService->imagineFilter($path, '600x250');
                $illustrationItemContent = $this->renderView('job/include/illustration_item.html.twig', array(
                    'job' => $job,
                    'illustration' => $illustration,
                    'classActive' => 'active'
                ));

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'illustration116x116'	 	=> $illustration116x116,
                    'illustration600x250'	 	=> $illustration600x250,
                    'illustrationItemContent'  => $illustrationItemContent,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function deleteIllustration($job_id, $illustration_id, Request $request)
    {
        $job = $this->jobRepository->find($job_id);
        $illustration = $this->jobIllustrationRepository->find($illustration_id);
        $user = $this->getUser();
        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($job && $illustration && $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user){
            if($job->getId() == $illustration->getJob()->getId()){
                $illustrationId = $illustration->getId();
                $this->em->remove($illustration);
                $this->em->flush();

                $current = $this->jobIllustrationRepository->findOneBy(array(
                    'job' => $job,
                    'current' => true,
                ));

                if($current){
                    $isCurrent = false;
                    $path = $current->getWebPath();
                }else{
                    $isCurrent = true;
                    $defaultPath = 'default/images/job/illustration/default.jpeg';
                    $path = $defaultPath;
                }

                $illustration116x116 = $this->platformService->imagineFilter($path, '116x116');
                $illustration600x250 = $this->platformService->imagineFilter($path, '600x250');

                $response->setContent(json_encode(array(
                    'state' => 1,
                    'illustrationId' => $illustrationId,
                    'illustration116x116' => $illustration116x116,
                    'illustration600x250' => $illustration600x250,
                    'isCurrent' => $isCurrent,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditJob($job_id, Request $request)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();
        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($job){
            if ($this->isGranted('ROLE_ADMIN') || $job->getUser() == $user){
                $jobTemp = new Job();
                $form = $this->createForm(JobType::class, $jobTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $job->setTitle($jobTemp->getTitle());
                    if(trim($jobTemp->getSlug()) == ""){
                        $jobTemp->setSlug($jobTemp->getTitle());
                    }

                    $slug = $this->platformService->getSlug($jobTemp->getSlug(), $job);
                    $job->setSlug($slug);

                    //sector
                    $sector = $this->sectorRepository->find($jobTemp->getSectorId());
                    $job->setSector($sector);

                    $this->em->persist($job);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state' => 1,
                        'jobId' => $job->getId(),
                        'title' => $job->getTitle(),
                        'sectorId' => $job->getSector()->getId(),
                        'sectorName' => $job->getSector()->getName(),
                        'slug' => $job->getSlug(),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditDetailJob($job_id, Request $request)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();
        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($job){
            if ($this->isGranted('ROLE_ADMIN') || $job->getUser() == $user){
                $jobTemp = new Job();
                $form = $this->createForm(JobDetailType::class, $jobTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $job->setSociety($jobTemp->getSociety());
                    $job->setSalary($jobTemp->getSalary());
                    $job->setDescription($jobTemp->getDescription());

                    //contract
                    $contract = $this->contractRepository->find($jobTemp->getContractId());
                    if($contract){
                        $job->setContract($contract);
                        $contractId = $job->getContract()->getId();
                        $contractName = $job->getContract()->getName();
                    }else{
                        $contractId = 0;
                        $contractName = "";
                    }

                    //date
                    if($jobTemp->getDatelimitText() == ""){
                        $datelimit = null;
                    }else{
                        $datelimit = $this->platformService->getDate($jobTemp->getDatelimitText(), 'd/m/y h:i');
                        if ($datelimit instanceof \DateTime) {
                        }else{
                            $datelimit = null;
                        }
                    }
                    $job->setDatelimit($datelimit);
                    if($datelimit){
                        $datelimit = $datelimit->format('d/m/Y à H:i');
                    }else{
                        $datelimit = "";
                    }
                   

                    $this->em->persist($job);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state'         => 1,
                        'society'       => $job->getSociety(),
                        'salary'        => $job->getSalary(),
                        'contractId'    => $contractId,
                        'contractName'  => $contractName,
                        'datelimit'     => $datelimit,
                        'description'   => $job->getDescription(),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function doEditContactJob($job_id, Request $request)
    {
        $user = $this->getUser();
        $job = $this->jobRepository->find($job_id);

        $response = new Response();
        //set state 0 in error case
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        if($job){
            if ($this->isGranted('ROLE_ADMIN') || $job->getUser() == $user){
                $jobTemp = new Job();
                $form = $this->createForm(JobContactType::class, $jobTemp);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $job->setEmail($jobTemp->getEmail());
                    $job->setPhone($jobTemp->getPhone());
                    $job->setWebsite($jobTemp->getWebsite());
                    $job->setLocation($jobTemp->getLocation());
                    $job->setCity($jobTemp->getCity());
                    $job->setLatitude($jobTemp->getLatitude());
                    $job->setLongitude($jobTemp->getLongitude());
                   

                    $this->em->persist($job);
                    $this->em->flush();

                    $response->setContent(json_encode(array(
                        'state'=> 1,
                        'email'=> $job->getEmail(),
                        'phone'=> $job->getPhone(),
                        'website'=> $job->getWebsite(),
                        'location'=> $job->getLocation(),
                        'city'=> $job->getCity(),
                        'latitude'=> $job->getLatitude(),
                        'longitude'=> $job->getLongitude(),
                    )));
                }
            }
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}