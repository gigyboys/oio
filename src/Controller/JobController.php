<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CV;
use App\Entity\UserDocument;
use App\Entity\JobApplication;
use App\Entity\JobApplicationAttachment;
use App\Form\CommentType;
use App\Form\CVType;
use App\Form\UserDocumentType;
use App\Repository\JobRepository;
use App\Repository\CVRepository;
use App\Repository\JobApplicationRepository;
use App\Repository\JobApplicationAttachmentRepository;
use App\Service\PlatformService;
use App\Service\JobService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class JobController extends AbstractController{

    public function __construct(
        JobRepository $jobRepository,
        CVRepository $CVRepository,
        JobApplicationRepository $jobApplicationRepository,
        JobApplicationAttachmentRepository $jobApplicationAttachmentRepository,
        JobService $jobService,
        PlatformService $platformService,
        ObjectManager $em
    )
    {
        $this->jobRepository = $jobRepository;
        $this->CVRepository = $CVRepository;
        $this->jobApplicationRepository = $jobApplicationRepository;
        $this->jobApplicationAttachmentRepository = $jobApplicationAttachmentRepository;
        $this->jobService = $jobService;
        $this->platformService = $platformService;
        $this->em = $em;

        $this->platformService->registerVisit();
    }

    public function index($page, Request $request): Response
    {
        $response = $this->render('job/index.html.twig', [
            'entityView' => 'job',
        ]);

        return $response;
    }

    public function viewById($id, Request $request)
    {
        $job = $this->jobRepository->find($id);
        if($job){
            return $this->redirectToRoute('job_view', array('slug' => $job->getSlug()));
        }else{
            return $this->redirectToRoute('job');
        }
    }

    public function view($slug, Request $request): Response
    {

        $user = $this->getUser();

        $job = $this->jobRepository->findOneBy(array(
            'slug' => $slug,
        ));

        $showEntity = false;
        if($job && !$job->getDeleted() && $job->getPublished() && $job->getValid()){
            $showEntity = true;
        }
        if($job && ( $showEntity || $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user)){
            

            //view
            $this->platformService->registerView($job, $user, $request);

            /* comments */
            /*
            $allComments = $this->eventService->getValidComments($event);

            $limit = 10;
            $type = "event";
            $order = "DESC";
            $comments = $this->commentRepository->getCommentsLimit($type, $event, $limit, $order);

            $previousComment = null;
            if(count($comments)>0){
                $firstComment = $comments[0];
                $previousComment = $this->commentRepository->getSinceComment($firstComment, $type, $event);
            }

            //nextEvent
            $nextEvent = $this->eventService->getNextEvent($event);

            //previousEvent
            $previousEvent = $this->eventService->getPreviousEvent($event);
            */
            return $this->render('job/view_job.html.twig', [
                'job'             => $job,
                /*
                'comments'          => $comments,
                'allComments'       => $allComments,
                'previousComment'   => $previousComment,
                'nextEvent'         => $nextEvent,
                'previousEvent'     => $previousEvent,
                */
                'entityView'        => 'job',
            ]);
        }else{
            return $this->redirectToRoute('job');
        }
    }

    public function apply($slug, Request $request): Response
    {

        $user = $this->getUser();

        $job = $this->jobRepository->findOneBy(array(
            'slug' => $slug,
        ));

        $showEntity = false;
        if($job && !$job->getDeleted() && $job->getPublished() && $job->getValid()){
            $showEntity = true;
        }
        if($job && $user && ( $showEntity || $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user)){
            $jobApplication = $this->jobApplicationRepository->findOneBy(
                array(
                    "job"       => $job,
                    "user"      => $user,
                    "status"    => 0,
                )
            );

            if(!$jobApplication){
                $jobApplication = new JobApplication();
                $jobApplication->setUser($user);
                $jobApplication->setJob($job);
                $jobApplication->setStatus(0);
                $jobApplication->setDate(new \DateTime());

                $this->em->persist($jobApplication);
                $this->em->flush();
            }

            $cvAttachment = $this->jobApplicationAttachmentRepository->findCV($jobApplication);
            $cv = null;
            if($cvAttachment){
                if($cvAttachment->getCV() && $cvAttachment->getCV()->getUser()->getId() == $user->getId()){
                    $cv = $cvAttachment->getCV();
                }
            }else{
                $cv = $this->CVRepository->findOneBy(array(
                    "user"      => $user,
                    "current"   => true
                ));

                if($cv){
                    $cvAttachment = new JobApplicationAttachment();
                    $cvAttachment->setCV($cv);
                    $cvAttachment->setJobApplication($jobApplication);
                    $this->em->persist($cvAttachment);
                }
            }

            $attachments = $this->jobApplicationAttachmentRepository->findDocuments($jobApplication);

            $this->em->flush();

            return $this->render('job/apply_job.html.twig', [
                'job'               => $job,
                'jobApplication'    => $jobApplication,
                'cv'                => $cv,
                'attachments'       => $attachments,
                'entityView'        => 'job',
            ]);
        }else{
            return $this->redirectToRoute('job');
        }
    }

    public function uploadCV($slug, Request $request): Response
    {
        $CV = new CV();
        $user = $this->getUser();

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        $job = $this->jobRepository->findOneBy(array(
            'slug' => $slug,
        ));
        $showEntity = false;
        if($job && !$job->getDeleted() && $job->getPublished() && $job->getValid()){
            $showEntity = true;
        }
        if($job && $user && ( $showEntity || $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user)){
            $form = $this->createForm(CVType::class, $CV);
            $form->handleRequest($request);
            if ( $form->isSubmitted() && $form->isValid()) {
                $file = $CV->getFile();

                $t=time();
                $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$user->getId().'_'.$t.'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('cv_directory'),
                        $fileName
                    );
                    $CV->setPath($fileName);
                    $CV->setOriginalname($file->getClientOriginalName());
                    $CV->setName($file->getClientOriginalName());
                    $CV->setDate(new \DateTime());

                    $CVs = $this->CVRepository->findBy(array('user' => $user));

                    foreach ($CVs as $userCV) {
                        $userCV->setCurrent(false);
                    }

                    $CV->setCurrent(true);

                    $CV->setUser($user);

                    $this->em->persist($CV);

                    $jobApplication = $this->jobApplicationRepository->findOneBy(
                        array(
                            "job"       => $job,
                            "user"      => $user,
                            "status"    => 0,
                        )
                    );
        
                    if(!$jobApplication){
                        $jobApplication = new JobApplication();
                        $jobApplication->setUser($user);
                        $jobApplication->setJob($job);
                        $jobApplication->setStatus(0);
                        $jobApplication->setDate(new \DateTime());
        
                        $this->em->persist($jobApplication);
                    }

                    $cvAttachment = $this->jobApplicationAttachmentRepository->findCV($jobApplication);
                    $cv = null;
                    if($cvAttachment){
                        $cvAttachment->setCV($CV);
                    }else{
                        $cvAttachment = new JobApplicationAttachment();
                        $cvAttachment->setJobApplication($jobApplication);
                        $cvAttachment->setCV($CV);
                    }
                    $this->em->persist($cvAttachment);

                    $this->em->flush();
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $cvAttachment = $this->jobApplicationAttachmentRepository->findCV($jobApplication);
                $cv = null;
                if($cvAttachment){
                    if($cvAttachment->getCV() && $cvAttachment->getCV()->getUser()->getId() == $user->getId()){
                        $cv = $cvAttachment->getCV();
                    }
                }

                $attachments = $this->jobApplicationAttachmentRepository->findDocuments($jobApplication);

                $applyFilesHtml = $this->renderView('job/include/job_apply_files.html.twig', array(
                    'job'           => $job,
                    'cv'            => $cv,
                    'attachments'   => $attachments,
                ));

                $response->setContent(json_encode(array(
                    'state'             => 1,
                    'applyFilesHtml' => $applyFilesHtml,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function uploadDocument($slug, Request $request): Response
    {
        $userDocument = new UserDocument();
        $user = $this->getUser();

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        $job = $this->jobRepository->findOneBy(array(
            'slug' => $slug,
        ));
        $showEntity = false;
        if($job && !$job->getDeleted() && $job->getPublished() && $job->getValid()){
            $showEntity = true;
        }
        if($job && $user && ( $showEntity || $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user)){
            $form = $this->createForm(UserDocumentType::class, $userDocument);
            $form->handleRequest($request);
            if ( $form->isSubmitted() && $form->isValid()) {
                $file = $userDocument->getFile();

                $t=time();
                $fileName = substr(sha1(uniqid(mt_rand(), true)), 0, 10).'_'.$user->getId().'_'.$t.'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('userdocument_directory'),
                        $fileName
                    );
                    $userDocument->setPath($fileName);
                    $userDocument->setOriginalname($file->getClientOriginalName());
                    $userDocument->setName($file->getClientOriginalName());
                    $userDocument->setDate(new \DateTime());

                    $userDocument->setUser($user);

                    $this->em->persist($userDocument);

                    $jobApplication = $this->jobApplicationRepository->findOneBy(
                        array(
                            "job"       => $job,
                            "user"      => $user,
                            "status"    => 0,
                        )
                    );
        
                    if(!$jobApplication){
                        $jobApplication = new JobApplication();
                        $jobApplication->setUser($user);
                        $jobApplication->setJob($job);
                        $jobApplication->setStatus(0);
                        $jobApplication->setDate(new \DateTime());
        
                        $this->em->persist($jobApplication);
                    }

                    $attachment = new JobApplicationAttachment();
                    $attachment->setUserDocument($userDocument);
                    $attachment->setJobApplication($jobApplication);
                    $this->em->persist($attachment);

                    $this->em->flush();
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $cvAttachment = $this->jobApplicationAttachmentRepository->findCV($jobApplication);
                $cv = null;
                if($cvAttachment){
                    if($cvAttachment->getCV() && $cvAttachment->getCV()->getUser()->getId() == $user->getId()){
                        $cv = $cvAttachment->getCV();
                    }
                }

                $attachments = $this->jobApplicationAttachmentRepository->findDocuments($jobApplication);

                $applyFilesHtml = $this->renderView('job/include/job_apply_files.html.twig', array(
                    'job'           => $job,
                    'cv'            => $cv,
                    'attachments'   => $attachments,
                ));

                $response->setContent(json_encode(array(
                    'state'             => 1,
                    'applyFilesHtml' => $applyFilesHtml,
                )));
            }
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function deleteDocument($slug, $id, Request $request): Response
    {
        $user = $this->getUser();

        $response = new Response();
        $response->setContent(json_encode(array(
            'state' => 0,
        )));

        $job = $this->jobRepository->findOneBy(array(
            'slug' => $slug,
        ));

        $documentAttachment = $this->jobApplicationAttachmentRepository->find($id);
        $showEntity = false;
        if($job && !$job->getDeleted() && $job->getPublished() && $job->getValid()){
            $showEntity = true;
        }
        if($job && $documentAttachment && $user && ( $showEntity || $this->isGranted('ROLE_ADMIN') || $job->getUser() == $user)){
            $this->em->remove($documentAttachment);

            $jobApplication = $this->jobApplicationRepository->findOneBy(
                array(
                    "job"       => $job,
                    "user"      => $user,
                    "status"    => 0,
                )
            );

            if(!$jobApplication){
                $jobApplication = new JobApplication();
                $jobApplication->setUser($user);
                $jobApplication->setJob($job);
                $jobApplication->setStatus(0);
                $jobApplication->setDate(new \DateTime());

                $this->em->persist($jobApplication);
            }
            
            $this->em->flush();

            $cvAttachment = $this->jobApplicationAttachmentRepository->findCV($jobApplication);
            $cv = null;
            if($cvAttachment){
                if($cvAttachment->getCV() && $cvAttachment->getCV()->getUser()->getId() == $user->getId()){
                    $cv = $cvAttachment->getCV();
                }
            }

            $attachments = $this->jobApplicationAttachmentRepository->findDocuments($jobApplication);

            $applyFilesHtml = $this->renderView('job/include/job_apply_files.html.twig', array(
                'job'           => $job,
                'cv'            => $cv,
                'attachments'   => $attachments,
            ));

            $response->setContent(json_encode(array(
                'state'             => 1,
                'applyFilesHtml' => $applyFilesHtml,
            )));
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}