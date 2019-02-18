<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\JobRepository;
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
        JobService $jobService,
        PlatformService $platformService,
        ObjectManager $em
    )
    {
        $this->jobRepository = $jobRepository;
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
}